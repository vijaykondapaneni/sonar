
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
 <h2>Log Count</h2>
    
<table class="table table-bordered">
<tr>
     <th>Sl no</th>
     <th>Salon Id</th>
      <th>Salon Account no</th>
      <th>Salon Name</th>
     <th>Count</th>
     <th>Status</th>

    
</tr>
  <?php
   $i=1;

  foreach ($salon_data as  $count){
     
    ?>
     
<tr style="text-align:left;">
  <td> <?php echo $i; ?></td>
   
     <td><?php echo $count['salon_id']; ?></td>
     <td><?php echo $count['salon_account_id']; ?></td>
      <td><?php echo $count['salon_name']; ?></td>
    <td><?php echo $count['count'];?></td>
   <?php if($count['status']== "SDK Error"){
        ?> <td style="background-color:red;">
      <?php } else{ ?>
      <td><?php } echo $count['status'];?></td>
     <?php $i++; }?>
    </tr>
</table>      
</div>
</body>
</html>