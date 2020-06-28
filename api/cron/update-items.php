<?php
include '../connection.php';
$reportName = 'Updated Items Report';

//Load Variables...

?>

  <html>

  <head>
    <title>
      <?php echo $reportName; ?>
    </title>
    <link href="../../jquery/jquery-ui.css" rel="stylesheet" />
    <style>
      /* page */

      html {
        font: 16px/1 "Open Sans", sans-serif;
        overflow: auto;
        padding: 0.5in;
      }

      html {
        background: #999;
        cursor: default;
      }

      body {
        box-sizing: border-box;
        min-height: 11in;
        margin: 0 auto;
        overflow: hidden;
        padding: 0.5in;
        width: 8.5in;
      }

      body {
        background: #FFF;
        border-radius: 1px;
        box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
      }

      th,
      td {
        border: 1px solid black;
        padding: 5px;
      }

      .btn {
        padding-left: 8px;
        padding-right: 8px;
        background: blue;
        border-radius: 25px;
        color: white;
      }

      @media print {
        * {
          -webkit-print-color-adjust: exact;
        }
        html {
          background: none;
          padding: 0;
        }
        body {
          box-shadow: none;
          margin: 0;
        }
        span:empty {
          display: none;
        }
        .add,
        .cut {
          display: none;
        }
      }

      @page {
        margin: 0;
      }
    </style>
  </head>

  <body>
    <h1 style="text-align:center;">
      <?php echo $reportName; ?>
    </h1>

    <table id="rec_table" style="/*margin:auto;*/">
      <thead>
        <tr style="background:lightgray;">
          <th>PID</th>
          <th>Name</th>
          <th>ebay ID</th>
          <th>QTY</th>
        </tr>
      </thead>
      <tbody>
        
<?php
$q = "SELECT * FROM oc_kb_ebay_profile_products WHERE `status` = 'Updated'";
$g = mysqli_query($s_conn, $q) or die($s_conn->error);
while($r = mysqli_fetch_array($g)){
  //Get product info...
  $iq = "SELECT * FROM oc_product p
         LEFT JOIN oc_product_description pd
         ON p.product_id = pd.product_id
         WHERE p.product_id = '" . $r['id_product'] . "'";
  $ig = mysqli_query($s_conn, $iq) or die($s_conn->error);
  $ir = mysqli_fetch_array($ig);
  
  echo '<tr>
          <td>' . $ir['product_id'] . '</td>
          <td>' . $ir['name'] . '</td>
          <td>' . $r['ebay_listiing_id'] . '</td>
          <td>' . $ir['quantity'] . '</td>
        </tr>';
}
?>



      </tbody>
    </table>



  </body>
  <!--JQuery Files-->
  <script src="../../jquery/external/jquery/jquery.js"></script>
  <script src="../../jquery/jquery-ui.js"></script>
  <script>
    $(document).ready(function() {
      $(".date").datepicker();
    });
  </script>
  <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
  <script>
    $("#rec_table").dataTable({
      "paging": false
    });
  </script>

  </html>