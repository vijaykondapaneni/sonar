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
 <h1>Employee iid not set salons list</h1>
    
<table class="table table-bordered">
<tr>
    <th>Sl no</th>
    <th>Salon Id</th>
    <th>Salon Name</th>
    <th>Salon Acc No</th>
    <th>Total Staff</th>
    <th>Not Set Staff</th>
</tr>
<?php
$i= 1; 
$total_sum=0; ?>
  <?php  foreach ($staff_count as $counts){ 
          $salon_id = $counts['salon_id'];
          $this->db->select('count(*) as notsetstaff');
          $this->db->where('salon_id',$salon_id);
          $this->db->where('emp_iid',0);
          $notset = $this->db->get('plus_staff2')->row_array();
          $this->db->select('count(*) as setstaff');
          $this->db->where('salon_id',$salon_id);
          $setstaff = $this->db->get('plus_staff2')->row_array();
  ?>

<tr>
    <td> <?= $i++ ?></td>
    <td><?= $counts['salon_id']; ?></td>
    <td><?= $counts['salon_name']; ?></td>
    <td><?= $counts['salon_account_id']; ?></td>
    <td><?= $setstaff['setstaff']; ?></td>
    <td><?= $notset['notsetstaff']; ?></td>
</tr>
  <?php }?>
</table>

 
            
</div>
</body>
</html>

