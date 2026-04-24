<?php
class StaffRepository {
    private PDO $conn;
    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

public function search(string $q = "") {
    $sql = "
        SELECT 
            s.staff_IdNum,
            s.staff_name,
            s.staff_email,
            s.office_num,
            s.office_open,
            s.office_close,
            GROUP_CONCAT(DISTINCT c.course_name ORDER BY c.course_name SEPARATOR ', ') AS courses
        FROM staff s
        LEFT JOIN assignedCourses ac ON ac.staff_IdNum = s.staff_IdNum
        LEFT JOIN courses c ON c.course_id = ac.course_id
    ";

    $params = [];
    if ($q !== "") {
        $like = "%" . $q . "%";
        $sql .= " WHERE s.staff_name LIKE :q1 OR s.staff_email LIKE :q2 OR s.office_num LIKE :q3 ";
        $params[":q1"] = $like;
        $params[":q2"] = $like;
        $params[":q3"] = $like;
    }

    $sql .= " GROUP BY s.staff_IdNum ORDER BY s.staff_name ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$r) {
        $r["courses"] = $r["courses"] ? array_map("trim", explode(",", $r["courses"])) : [];
    }
    unset($r);

    return $rows;
}
}