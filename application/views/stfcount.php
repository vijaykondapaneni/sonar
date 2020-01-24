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
 <h1>Staff</h1>
    
<table class="table table-bordered">
<tr>
     <th>Sl no</th>
    <th>Salon Id</th>
    <th>Salon Name</th>
    <th>Salon Acc No</th>
      <th>StaffCount</th>
  
</tr>
<?php
$i= 1; 
$total_sum=0; ?>
  <?php  foreach ($countno as $counts){ ?>

<tr>
  <td> <?= $i++ ?></td>
    <td><?= $counts->salon_id ?></td>
    <td><?= $counts->salon_name ?></td>
   <td><?= $counts->salon_account_id ?></td>
     <td><?= $counts->mycount ?></td>
</tr>
<?php $total_sum+=$counts->mycount; ?>
  <?php }?>
</table>

  Total Sum = <?php echo $total_sum;?>
  

            
</div>
</body>
</html>

