<?php
include '../connection.php';
$reportName = 'Product Removal from Website Report';

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
          <th>Product ID</th>
          <th>Name</th>
          <th>ebay ID</th>
          <th>QTY</th>
        </tr>
      </thead>
      <tbody>
        
<?php
//Get Deleted items...
$dq = "SELECT * FROM oc_kb_ebay_profile_products WHERE `status` = 'Deleted'";
$dg = mysqli_query($s_conn, $dq) or die($s_conn->error);
while($dr = mysqli_fetch_array($dg)){
  $pid = $dr['id_product'];
  $eid = $dr['ebay_listiing_id'];
  //Get product info...
  $pq = "SELECT * FROM oc_product p
         LEFT JOIN oc_product_description pd
         ON p.product_id = pd.product_id
         WHERE p.product_id = '" . $pid . "'";
  $pg = mysqli_query($s_conn, $pq) or die($s_conn->error);
  if(mysqli_num_rows($pg) > 0){
    $pr = mysqli_fetch_array($pg);
    $name = $pr['name'];
    $qty = $pr['quantity'];
    
    //Update inventory list to zero qty for these ended ebay items...
    $uq = "UPDATE oc_product SET `quantity` = 0 WHERE product_id = '" . $pid . "'";
    mysqli_query($s_conn, $uq) or die($s_conn->error);
    
    echo '<tr>
            <td>' . $dr['id_product'] . '</td>
            <td>' . $name . '</td>
            <td>' . $eid . '</td>
            <td>' . $qty . '</td>
          </tr>';
    
  }
  //Remove item from ebay profile...
  $rq = "DELETE FROM oc_kb_ebay_profile_products WHERE id_ebay_profile_products = '" . $dr['id_ebay_profile_products'] . "'";
  mysqli_query($s_conn, $rq) or die($s_conn->error);
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