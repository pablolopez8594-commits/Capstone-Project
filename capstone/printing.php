<?php include "partials/header.php"; ?>


<style>
  body { text-align: center; font-family: Arial, sans-serif; }
  .print-button {
      display: inline-block;
      background-color: #28a745;
      color: white;
      width: 200px;
      height: 200px;
      border-radius: 20px;
      text-decoration: none;
      padding: 20px;
      box-sizing: border-box;
      transition: 0.3s;
  }
  .print-button img { width: 80px; margin-top: 10px; }
  .print-button span {
      display: block;
      margin-top: 15px;
      font-size: 20px;
      font-weight: bold;
  }
  .print-button:hover { background-color: #218838; transform: scale(1.05); }
  .description {
      max-width: 700px;
      margin: 20px auto;
      font-size: 18px;
  }
</style>

<main class="container">
  <h1>Printing Services</h1>

  <a href="https://print.slc.me/user" class="print-button">
    <img src="https://img.icons8.com/ios-filled/100/FFFFFF/print.png" alt="Printer Icon">
    <span>Printing Services</span>
  </a>

  <p class="description">
    St. Lawrence College students can use the PaperCut system to manage printing,
    check balances for printing, upload documents, and release print jobs on campus printers.
  </p>
</main>
<?php include "partials/nav.php"; ?>
<?php include "partials/footer.php"; ?>