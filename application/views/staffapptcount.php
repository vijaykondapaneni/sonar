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
 <h1>Counts</h1>

    
<table class="table table-bordered">
<tr style="text-align:left;">
     <th>Sl no</th>
    <th>Salon Id</th>
    <th>Salon Name</th>
    <th>Salon Account No</th>
   <th>Staff Count</th>
   <th>Client Count</th>
 <th>Total Appointments</th>
  <th>Future Appointments</th>
</tr>
<?php
$i= 1; 
$Staff_sum=0; 
$Client_sum=0; 
$App_sum=0; 
$FeatApp_sum=0; ?>

  <?php

 
  foreach ($sdata as  $counts){
   
    ?>
     
<tr style="text-align:left;">
  <td> <?php echo $i; ?></td>
   
     <td><?php echo $counts['salon_id']; ?></td>
     <td><?php echo $counts['salon_name']; ?></td>
     <td><?php echo $counts['salon_account_id']; ?></td>
    <td><?php echo $counts['account']['0']['count']; ?></td>
    <td><?php echo $counts['Account_id']['0']['client']; ?></td>
     <td><?php echo $counts['accountid']['0']['appts']; ?></td>
     <td><?php echo $counts['account_id']['0']['fetappts']; ?></td>
</tr>

  <?php  $i++;
  $Staff_sum+=$counts['account']['0']['count']; 
  $Client_sum+=$counts['Account_id']['0']['client']; 
  $App_sum+=$counts['accountid']['0']['appts'];
 $FeatApp_sum+=$counts['account_id']['0']['fetappts'];
}

  ?> 

</table>

Total Staff Sum = <?php echo  $Staff_sum;?><br><br>
Total Client Sum = <?php echo  $Client_sum;?><br><br>
Total Appointments Sum = <?php echo  $App_sum;?><br><br> 
Total Future Appointments Sum = <?php echo  $FeatApp_sum?>  <br> <br> 
</div>
</body>
</html>
