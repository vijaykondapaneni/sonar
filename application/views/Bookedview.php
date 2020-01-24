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
          <h2>Percentage Booked Count -- <?php echo $dayRangeTypedisplay;?> </h2>
          <table class="table table-bordered">
          <tr>
               <th>Sl no</th>
               <th>Salon Id</th>
               <th>Salon Account no</th>
               <th>Salon Name</th>
               <th>% Booked</th>
               <th>Leaderboard %Booked</th>
               <th>Leaderboard Employee Name</th>
               <th>SDK Status</th>
          </tr>
          <?php
             $i=1;
             foreach ($salon_info as  $count){
          ?>   
          <tr style="text-align:left;">
              <td> <?php echo $i; ?></td>
              <td><?php echo $count['salon_id']; ?></td>
              <td><?php echo $count['salon_name']; ?></td>
              <td><?php echo $count['salon_account_id']; ?></td>
              <td <?php if($count['booked'] == 0 ){ ?> style="background-color:red;" <?php } ?> >
                  <?php echo $count['booked']; ?>
                  <?php if($count['booked'] == 0 ){ ?> <br/> 
                    <a href="<?php echo MAIN_SERVER_URL;?>PercentageBookedForDashboard/fixpercentage_booked/<?php echo $count['salon_id']?>/<?php echo $count['dayRangeType']?>" target="_blank"> Fix </a>  
                   <?php } ?>

              </td>
              <td <?php if($count['highest_booked'] == 0 ){ ?> style="background-color:red;" <?php } ?>><?php echo $count['highest_booked']; ?></td>
              <td <?php if($count['employeename'] == 'NULL' ){ ?> style="background-color:red;" <?php } ?>><?php echo $count['employeename']; ?></td>
              <td <?php if($count['status'] == 'Not Working' ){ ?> style="background-color:red;" <?php } ?>><?php echo $count['status']; ?></td>
               <?php $i++; }?>
          </tr>
          </table>      
      </div>
</body>
</html>