// js/map-route.js

const GRAPH_URL = "./maps/college_graph.json";
const ROOMS_URL = "./maps/rooms.json";

const MAP_FILES = {
  M1: "./assets/svg/M1.svg",
  M2: "./assets/svg/M2.svg",
  M3: "./assets/svg/M3.svg",
  A1: "./assets/svg/A1.svg",
  A2: "./assets/svg/A2.svg",
  A3: "./assets/svg/A3.svg"
};

const DEFAULT_MAP = "M1";

/*
  Peso fijo para edges que cambian de mapa visual:
  M1 <-> M2, M2 <-> M3, A1 <-> A2, etc.
  M0 y M1 NO cuentan como cambio visual.
*/
const INTERMAP_EDGE_WEIGHT = 800;

const fromInput = document.querySelector("#fromRoom");
const toInput = document.querySelector("#toRoom");
const roomsList = document.querySelector("#roomsList");
const btnRoute = document.querySelector("#btnRoute");
const btnClear = document.querySelector("#btnClear");

const btnPrev = document.querySelector("#map-prev-btn");
const btnNext = document.querySelector("#map-next-btn");
const mapContainer = document.querySelector("#map-container");

let graphEdges = [];
let rooms = {};
let roomsNorm = new Map();

let currentMapKey = DEFAULT_MAP;
let currentNodes = {};
let allNodes = {};

let routeSegments = [];
let currentSegmentIndex = 0;

const mapCache = new Map();

init().catch(console.error);

async function init() {
  validateRequiredElements();

  const [graph, roomsData] = await Promise.all([
    fetchJson(GRAPH_URL),
    fetchJson(ROOMS_URL)
  ]);

  graphEdges = graph.edges ?? [];
  rooms = roomsData.rooms ?? {};
  roomsNorm = new Map(
    Object.entries(rooms).map(([name, nodeId]) => [norm(name), nodeId])
  );

  populateRoomsDatalist();
  await preloadAllMaps();
  await loadMap(DEFAULT_MAP);

  updateMapSideButtons();

  btnRoute.addEventListener("click", onDrawRoute);
  btnClear.addEventListener("click", onClearRoute);

  btnPrev.addEventListener("click", () => {
    goToSegment(currentSegmentIndex - 1).catch(console.error);
  });

  btnNext.addEventListener("click", () => {
    goToSegment(currentSegmentIndex + 1).catch(console.error);
  });

  [fromInput, toInput].forEach((inp) => {
    inp.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        btnRoute.click();
      }
    });
  });

  console.log("Map system ready");
  console.log("Global nodes:", Object.keys(allNodes).length);
  console.log("Edges:", graphEdges.length);
  console.log("Rooms:", Object.keys(rooms).length);
}

function validateRequiredElements() {
  const missing = [];

  if (!fromInput) missing.push("#fromRoom");
  if (!toInput) missing.push("#toRoom");
  if (!btnRoute) missing.push("#btnRoute");
  if (!btnClear) missing.push("#btnClear");
  if (!btnPrev) missing.push("#map-prev-btn");
  if (!btnNext) missing.push("#map-next-btn");
  if (!mapContainer) missing.push("#map-container");

  if (missing.length) {
    throw new Error(`Faltan elementos en map.php: ${missing.join(", ")}`);
  }
}

async function fetchJson(url) {
  const response = await fetch(url, { cache: "no-store" });
  if (!response.ok) {
    throw new Error(`No se pudo cargar ${url} (${response.status})`);
  }
  return response.json();
}

function populateRoomsDatalist() {
  if (!roomsList) return;

  roomsList.innerHTML = "";
  Object.keys(rooms)
    .sort()
    .forEach((name) => {
      const opt = document.createElement("option");
      opt.value = name;
      roomsList.appendChild(opt);
    });
}

async function preloadAllMaps() {
  const scratch = ensureScratchHost();

  for (const [mapKey, filePath] of Object.entries(MAP_FILES)) {
    try {
      const response = await fetch(filePath, { cache: "no-store" });
      if (!response.ok) {
        console.warn(`No se pudo cargar ${filePath}. Se omitirá ${mapKey}.`);
        continue;
      }

      const svgText = await response.text();
      const nodes = extractNodesFromSvgText(svgText, scratch);

      mapCache.set(mapKey, {
        text: svgText,
        nodes
      });

      Object.assign(allNodes, nodes);
    } catch (err) {
      console.warn(`Error precargando ${mapKey}:`, err);
    }
  }
}

async function loadMap(mapKey) {
  const cached = mapCache.get(mapKey);
  if (!cached) {
    throw new Error(`No hay SVG cargado para el mapa ${mapKey}`);
  }

  mapContainer.innerHTML = cached.text;

  const svg = getCurrentSvg();
  if (!svg) {
    throw new Error(`No encontré un <svg> dentro del mapa ${mapKey}`);
  }

  svg.id = "campus-map";
  currentMapKey = mapKey;
  currentNodes = extractNodes(svg);
}

function getCurrentSvg() {
  return mapContainer.querySelector("svg");
}

function ensureScratchHost() {
  let host = document.querySelector("#svg-scratch-host");
  if (host) return host;

  host = document.createElement("div");
  host.id = "svg-scratch-host";
  host.style.position = "absolute";
  host.style.left = "-100000px";
  host.style.top = "-100000px";
  host.style.width = "1px";
  host.style.height = "1px";
  host.style.overflow = "hidden";
  host.style.pointerEvents = "none";
  host.style.opacity = "0";
  document.body.appendChild(host);

  return host;
}

function extractNodesFromSvgText(svgText, scratchHost) {
  scratchHost.innerHTML = svgText;
  const svg = scratchHost.querySelector("svg");
  if (!svg) return {};

  return extractNodes(svg);
}

function extractNodes(svg) {
  const nodes = {};

  svg.querySelectorAll('[id^="n_"]').forEach((el) => {
    const point = getNodeCenterInSvgSpace(svg, el);
    if (!point) return;
    nodes[el.id] = point;
  });

  return nodes;
}

function getNodeCenterInSvgSpace(svg, el) {
  const tag = el.tagName.toLowerCase();

  let x = null;
  let y = null;

  if (el.hasAttribute("cx") && el.hasAttribute("cy")) {
    x = parseFloat(el.getAttribute("cx"));
    y = parseFloat(el.getAttribute("cy"));
  } else if (tag === "rect" && el.hasAttribute("x") && el.hasAttribute("y")) {
    x = parseFloat(el.getAttribute("x")) + parseFloat(el.getAttribute("width") || "0") / 2;
    y = parseFloat(el.getAttribute("y")) + parseFloat(el.getAttribute("height") || "0") / 2;
  } else {
    try {
      const bb = el.getBBox();
      x = bb.x + bb.width / 2;
      y = bb.y + bb.height / 2;
    } catch {
      return null;
    }
  }

  try {
    const pt = svg.createSVGPoint();
    pt.x = x;
    pt.y = y;

    const elCTM = el.getCTM();
    const svgCTM = svg.getCTM();

    if (elCTM && svgCTM) {
      const toSvgSpace = svgCTM.inverse().multiply(elCTM);
      const transformed = pt.matrixTransform(toSvgSpace);
      return { x: transformed.x, y: transformed.y };
    }

    return { x, y };
  } catch {
    return { x, y };
  }
}

function norm(s) {
  return (s || "").trim().toLowerCase();
}

function normalizeCode(raw) {
  return (raw || "")
    .trim()
    .toUpperCase()
    .replace(/\s+/g, "");
}

function resolveRoomToNodeId(value, roomMap, roomNormMap, nodeDict) {
  const raw = (value || "").trim();
  if (!raw) return null;

  if (raw.startsWith("n_")) {
    return nodeDict[raw] ? raw : raw;
  }

  if (roomMap[raw]) return roomMap[raw];

  const caseInsensitiveMatch = roomNormMap.get(norm(raw));
  if (caseInsensitiveMatch) return caseInsensitiveMatch;

  const code = normalizeCode(raw);
  const direct = `n_${code}`;
  if (nodeDict[direct]) return direct;

  const prefix = `n_${code}`;
  const matches = Object.keys(nodeDict).filter((id) => id.startsWith(prefix));
  if (matches.length === 1) return matches[0];

  const nonCorridorMatches = matches.filter((id) => !id.startsWith(`${prefix}C`));
  if (nonCorridorMatches.length === 1) return nonCorridorMatches[0];

  const corridorDirect = `n_${code}C`;
  if (nodeDict[corridorDirect]) return corridorDirect;

  const corridorMatches = Object.keys(nodeDict).filter((id) => id.startsWith(`n_${code}C`));
  if (corridorMatches.length === 1) return corridorMatches[0];

  if (matches.length > 1 || corridorMatches.length > 1) {
    console.warn(
      "Ambiguo:",
      raw,
      "→ posibles:",
      [...new Set([...nonCorridorMatches, ...corridorMatches])].slice(0, 12)
    );
  }

  return null;
}

/*
  Piso lógico real:
  n_M0..., n_M1..., n_M2..., n_A1..., etc.
*/
function getFloorKeyFromNode(nodeId) {
  const clean = (nodeId || "").replace(/^n_/, "").toUpperCase();
  const match = clean.match(/^[A-Z]\d/);
  return match ? match[0] : null;
}

/*
  Grupo visual:
  M0 y M1 viven en el mismo SVG => ambos cuentan como M1
*/
function getVisualGroupFromNode(nodeId) {
  const floorKey = getFloorKeyFromNode(nodeId);

  if (floorKey === "M0" || floorKey === "M1") return "M1";

  return floorKey;
}

function buildAdj(nodes, edges) {
  const adj = new Map();

  const add = (u, v, w) => {
    if (!adj.has(u)) adj.set(u, []);
    adj.get(u).push({ to: v, w });
  };

  for (const e of edges) {
    const A = nodes[e.a];
    const B = nodes[e.b];
    if (!A || !B) continue;

    const groupA = getVisualGroupFromNode(e.a);
    const groupB = getVisualGroupFromNode(e.b);

    let weight;

    /*
      Si cambia de grupo visual, usa peso fijo.
      Ej: M1 <-> M2, M2 <-> M3, A1 <-> A2.
      M0 <-> M1 NO entra aquí porque ambos son "M1".
    */
    if (groupA && groupB && groupA !== groupB) {
      weight = (e.mult ?? 1) * INTERMAP_EDGE_WEIGHT;
    } else {
      const base = Math.hypot(A.x - B.x, A.y - B.y);
      weight = base * (e.mult ?? 1);
    }

    add(e.a, e.b, weight);
    add(e.b, e.a, weight);
  }

  return adj;
}

function dijkstra(adj, start, goal) {
  const dist = new Map([[start, 0]]);
  const prev = new Map();
  const visited = new Set();
  const pq = [{ id: start, d: 0 }];

  while (pq.length) {
    pq.sort((a, b) => a.d - b.d);
    const { id } = pq.shift();

    if (visited.has(id)) continue;
    visited.add(id);

    if (id === goal) break;

    for (const n of adj.get(id) ?? []) {
      const nd = (dist.get(id) ?? Infinity) + n.w;

      if (nd < (dist.get(n.to) ?? Infinity)) {
        dist.set(n.to, nd);
        prev.set(n.to, id);
        pq.push({ id: n.to, d: nd });
      }
    }
  }

  if (start !== goal && !prev.has(goal)) return null;

  const path = [goal];
  let cur = goal;

  while (cur !== start) {
    cur = prev.get(cur);
    if (!cur) return null;
    path.push(cur);
  }

  path.reverse();
  return path;
}

function splitPathByMap(path) {
  const segments = [];
  let currentSegment = null;

  for (const nodeId of path) {
    const mapKey = getVisualGroupFromNode(nodeId);
    if (!mapKey) continue;

    if (!currentSegment || currentSegment.map !== mapKey) {
      currentSegment = { map: mapKey, nodes: [nodeId] };
      segments.push(currentSegment);
    } else {
      currentSegment.nodes.push(nodeId);
    }
  }

  return segments;
}

async function onDrawRoute() {
  const startId = resolveRoomToNodeId(fromInput.value, rooms, roomsNorm, allNodes);
  const endId = resolveRoomToNodeId(toInput.value, rooms, roomsNorm, allNodes);

  if (!startId || !endId) {
    console.warn("No pude resolver From/To.");
    return;
  }

  if (!allNodes[startId] || !allNodes[endId]) {
    console.warn("Start/End no existe en nodos globales:", { startId, endId });
    return;
  }

  const globalAdj = buildAdj(allNodes, graphEdges);
  const fullPath = dijkstra(globalAdj, startId, endId);

  if (!fullPath) {
    console.warn("No hay ruta.");
    return;
  }

  routeSegments = splitPathByMap(fullPath);
  currentSegmentIndex = 0;

  if (!routeSegments.length) {
    console.warn("No se pudieron generar segmentos.");
    return;
  }

  await goToSegment(0);
}

async function goToSegment(index) {
  if (index < 0 || index >= routeSegments.length) return;

  currentSegmentIndex = index;
  const segment = routeSegments[currentSegmentIndex];

  await loadMap(segment.map);

  const svg = getCurrentSvg();
  drawRoute(svg, currentNodes, segment.nodes);
  updateMapSideButtons();
}

function updateMapSideButtons() {
  const hasPrev = currentSegmentIndex > 0;
  const hasNext = currentSegmentIndex < routeSegments.length - 1;

  btnPrev.disabled = !hasPrev;
  btnNext.disabled = !hasNext;

  btnPrev.classList.toggle("disabled", !hasPrev);
  btnNext.classList.toggle("disabled", !hasNext);
}

function onClearRoute() {
  const svg = getCurrentSvg();
  svg?.querySelector("#route")?.remove();

  routeSegments = [];
  currentSegmentIndex = 0;
  updateMapSideButtons();
}

function drawRoute(svg, nodes, pathIds) {
  if (!svg) return;

  let route = svg.querySelector("#route");
  if (!route) {
    route = document.createElementNS("http://www.w3.org/2000/svg", "path");
    route.id = "route";
    route.setAttribute("fill", "none");
    route.setAttribute("stroke", "green");
    route.setAttribute("stroke-width", "3");
    route.setAttribute("vector-effect", "non-scaling-stroke");
    route.setAttribute("stroke-linecap", "round");
    route.setAttribute("stroke-linejoin", "round");
    svg.appendChild(route);
  }

  const pts = pathIds.map((id) => nodes[id]).filter(Boolean);
  const d = pts
    .map((p, i) => (i === 0 ? `M ${p.x} ${p.y}` : `L ${p.x} ${p.y}`))
    .join(" ");

  route.setAttribute("d", d);
}
