<htlm>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    </head>

<style>
table {
    border-collapse: collapse;
}

table, th, td {
    border: 1px solid black;
}
</style>
    <body>
         <div class="container">
        
         <table class="table table-bordered">
          <tr>
              <th>Sl no</th>
              <th>Salon Id</th>
              <th>Salon Name</th>
              <th>Account No</th>
              <th>Service</th>
              <th>Retail</th>
              <th>Total</th>
          </tr>
       <?php
        $i= 1; 
        $total_sum=0; 
       ?>
          <?php  foreach ($all_results as $results=>$val){ 
            ?>
            <tr>
              <td> <?= $i++; ?></td>
              <td> <?= $val['salon_id']; ?></td>
              <td> <?= $val['salon_name']; ?></td>
              <td> <?= $val['account_no']; ?></td>
              <td> <?= $val['service_sales']; ?></td>
              <td> <?= $val['retail_sales']; ?></td>
              <td> <?= $val['total_sales']; ?></td>

            <tr>  
          <?php }?>
        </table>

  
</div>
</body>
</html>

