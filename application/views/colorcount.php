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
 <h2>Color & Chemical Count</h2>
    
<table class="table table-bordered">
<tr>
     <th>Sl no</th>
     <th>Salon Id</th>
      <th>Salon Account no</th>
      <th>Salon Name</th>
     <th>Color</th>
     <th>Sub Color</th>
     <th>Chemical</th>
     <th>Sub Chemical</th>
    
</tr>
  <?php
   $i=1;
 // echo "<pre>";
 //             print_r($sdata);
  foreach ($sdata as  $counts){
     
    ?>
     
<tr style="text-align:left;">
  <td> <?php echo $i; ?></td>
   
     <td><?php echo $counts['salon_id']; ?></td>
     <td><?php echo $counts['salon_account_id']; ?></td>
      <td><?php echo $counts['salon_name']; ?></td>
    <td><?php echo $counts['color'];?></td>
   <td><?php echo $counts['subcolor']?></td>
   <td><?php echo $counts['chemical'];?></td>
   <td><?php echo $counts['subchemical']?></td>
     <?php $i++; }?>
    </tr>
</table>      
</div>
</body>
</html>