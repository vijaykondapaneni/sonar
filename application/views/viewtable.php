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
         <h1><?php echo $table_heading;?> -- <?php echo $date_range;?></h1>
    
         <table class="table table-bordered">
          <tr>
              <th>Sl no</th>
              <?php foreach ($column_name as $key) { ?>
                <th><?php echo $key;?></th>
              <?php 
              } 
             ?>
             <th></th>
          </tr>
       <?php
        $i= 1; 
        $total_sum=0; ?>
          <?php  foreach ($all_results as $results=>$val){ 
             //pa($val);
             $sdk_status  = ($val['status']==0) ? 'SDK Working' : 'Error';
             $giftcarddb = $val['giftcard_total_db'];
             $giftcard = $val['giftcard_total'];

             // echo "<pre>";
             // print_r($val['giftcard_total_db']);
             // print_r($val['giftcard_total']);
            ?>
            <tr>
              <td> <?= $i++; ?></td>
              <td> <?= $val['salon_id']; ?></td>
              <td> <?= $val['account_no']; ?></td>
              <td> <?= $val['salon_name']; ?></td>
              <td> <?= $val['service_total']; ?></td>
              <td>
                <?php if($val['service_total']!=$val['service_total_db']) {
                ?> 
                <span style="color:black;background-color: #ff1919;">  
                <a href="<?php echo MAIN_SERVER_URL;?>NumbersMatch/Services/<?php echo $val['salon_id']?>/<?php echo $startDate;?>/<?php echo $endDate;?>/<?php echo $dayRangeType;?>" target="_blank">Click Here </a>  <br/><?php } ?>
                     <?= $val['service_total_db']; ?>
                <?php if((int)$val['service_total']!=(int)$val['service_total_db']) {
                   ?> 
                  </span>  
                <?php } ?>
              </td>
              <td> <?= $val['retail_total']; ?></td>
              <td>
                <?php if((int)$val['retail_total']!=(int)$val['retail_total_db']) {
                ?> 
                <span style="color:black;background-color: #ff1919;">  
                <a href="<?php echo MAIN_SERVER_URL;?>NumbersMatch/Products/<?php echo $val['salon_id']?>/<?php echo $startDate;?>/<?php echo $endDate;?>/<?php echo $dayRangeType;?>" target="_blank">Click Here </a> <br/>
                <?php }?>
                     <?= $val['retail_total_db']; ?>
                <?php if($val['retail_total']!=$val['retail_total_db']) {
                   ?> 
                  </span>  
                <?php } ?>
              </td>
              
              <td> <?= $val['giftcard_total']; ?></td>

              <td>
                <?php if((int)$val['giftcard_total']!=(int)$val['giftcard_total_db']) {
                ?> 
                <span style="color:black;background-color: #ff1919;">
                 <a href="<?php echo MAIN_SERVER_URL;?>NumbersMatch/GiftCards/<?php echo $val['salon_id']?>/<?php echo $startDate;?>/<?php echo $endDate;?>/<?php echo $dayRangeType;?>" target="_blank">Click Here </a> <br/>
                  <?php }?>
                     <?= $val['giftcard_total_db']; ?>
                <?php if($val['giftcard_total']!=$val['giftcard_total_db']) {
                   ?> 
                  </span>  
                <?php } ?>
              </td>
              <td> <?= $sdk_status; ?></td>
              <td>
                  <a href="<?php echo MAIN_SERVER_URL;?>CheckNumbersFromSdk/getInternalGraphsWeekly/<?php echo $val['salon_id'];?>" target="_blank">Internal Graph Monthly </a> <br/><br/>
                  <a href="<?php echo MAIN_SERVER_URL;?>CheckNumbersFromSdk/getInternalGraphs/<?php echo $val['salon_id'];?>" target="_blank">Internal Graph Yearly </a>
              </td>

            <tr>  
          <?php }?>
        </table>

  
</div>
</body>
</html>

