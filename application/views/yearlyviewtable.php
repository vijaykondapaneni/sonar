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
         <h1><?php echo $salon_id;?> -- <?php echo $salon_name;?> -- Yearly</h1>
    
         <table class="table table-bordered">
          <tr>
              <th>Sl no</th>
              <th>Month Name</th>
              <th>SDK Service</th>
              <th>DB Service</th>
              <th>SDK Service Last Year</th>
              <th>DB Service Last Year</th>
              <th>SDK Retail</th>
              <th>DB Retail</th>
              <th>SDK Retail Last Year</th>
              <th>DB Retail Last Year</th>
              <th>SDK GC</th>
              <th>DB GC</th>
              <th>SDK GC Last Year</th>
              <th>DB GC Last Year</th>
              <th>SDK Status</th>
              
          </tr>
       <?php
        $i= 1; 
        $total_sum=0; 

        //pa($all_results,'',true);

        
        ?>
          <?php  foreach ($all_results as $results=>$val){ 
             //pa($val);
             $sdk_status  = ($val['status']==0) ? 'SDK Working' : 'Error';
             $start_date = $val['start_date'];
             $end_date = $val['end_date'];
             $lastyear_start_date = $val['lastyear_start_date'];
             $lastyear_end_date = $val['lastyear_end_date'];
            ?>
            <tr>
              <td> <?= $i++; ?></td>
              <td> <?= $val['monthname']; ?></td>              
              <td> <?= $val['sdk_service_current_value']; ?></td>
              <td>
                <?php if($val['sdk_service_current_value']!=$val['service_current_value']) {
                ?> 
                <span style="color:black;background-color: #ff1919;">  
                <a href="<?php echo MAIN_SERVER_URL;?>NumbersMatch/Services/<?php echo $val['salon_id']?>/<?php echo $start_date;?>/<?php echo $end_date;?>/<?php echo $dayRangeType;?>/Yearly" target="_blank">Click Here </a>  <br/><?php } ?>
                     <?= $val['service_current_value']; ?>
                <?php if((int)$val['sdk_service_current_value']!=(int)$val['service_current_value']) {
                   ?> 
                  </span>  
                <?php } ?>
              </td>
              <td> <?= $val['sdk_service_last_year_value']; ?></td>
              <td>
                <?php if($val['sdk_service_last_year_value']!=$val['service_last_year_value']) {
                ?> 
                <span style="color:black;background-color: #ff1919;">  
                <a href="<?php echo MAIN_SERVER_URL;?>NumbersMatch/Services/<?php echo $val['salon_id']?>/<?php echo $lastyear_start_date;?>/<?php echo $lastyear_end_date;?>/<?php echo $dayRangeType;?>/Yearly" target="_blank">Click Here </a>  <br/><?php } ?>
                     <?= $val['service_last_year_value']; ?>
                <?php if((int)$val['sdk_service_last_year_value']!=(int)$val['service_last_year_value']) {
                   ?> 
                  </span>  
                <?php } ?>
              </td>
              

              <td> <?= $val['sdk_retail_current_value']; ?></td>
              <td>
                <?php if((int)$val['sdk_retail_current_value']!=(int)$val['retail_current_value']) {
                ?> 
                <span style="color:black;background-color: #ff1919;">  
                <a href="<?php echo MAIN_SERVER_URL;?>NumbersMatch/Products/<?php echo $val['salon_id']?>/<?php echo $start_date;?>/<?php echo $end_date;?>/<?php echo $dayRangeType;?>/Yearly" target="_blank">Click Here </a> <br/>
                <?php }?>
                     <?= $val['retail_current_value']; ?>
                <?php if($val['sdk_retail_current_value']!=$val['retail_current_value']) {
                   ?> 
                  </span>  
                <?php } ?>
              </td>


              <td> <?= $val['sdk_retail_last_year_value']; ?></td>


              <td>
                <?php if((int)$val['sdk_retail_last_year_value']!=(int)$val['retail_last_year_value']) {
                ?> 
                <span style="color:black;background-color: #ff1919;">  
                <a href="<?php echo MAIN_SERVER_URL;?>NumbersMatch/Products/<?php echo $val['salon_id']?>/<?php echo $lastyear_start_date;?>/<?php echo $lastyear_end_date;?>/<?php echo $dayRangeType;?>/Yearly" target="_blank">Click Here </a> <br/>
                <?php }?>
                     <?= $val['retail_last_year_value']; ?>
                <?php if($val['sdk_retail_last_year_value']!=$val['retail_last_year_value']) {
                   ?> 
                  </span>  
                <?php } ?>
              </td>


              

              <td> <?= $val['sdk_gc_current_value']; ?></td>
              
              <td>
                <?php if((int)$val['sdk_gc_current_value']!=(int)$val['gc_current_value']) {
                ?> 
                <span style="color:black;background-color: #ff1919;">
                 <a href="<?php echo MAIN_SERVER_URL;?>NumbersMatch/GiftCards/<?php echo $val['salon_id']?>/<?php echo $start_date;?>/<?php echo $end_date;?>/<?php echo $dayRangeType;?>/Yearly" target="_blank">Click Here </a> <br/>
                  <?php }?>
                     <?= $val['gc_current_value']; ?>
                <?php if($val['sdk_gc_current_value']!=$val['gc_current_value']) {
                   ?> 
                  </span>  
                <?php } ?>
              </td>
              


              <td> <?= $val['sdk_gc_last_year_value']; ?></td>

              <td>
                <?php if((int)$val['sdk_gc_last_year_value']!=(int)$val['gc_last_year_value']) {
                ?> 
                <span style="color:black;background-color: #ff1919;">
                 <a href="<?php echo MAIN_SERVER_URL;?>NumbersMatch/GiftCards/<?php echo $val['salon_id']?>/<?php echo $lastyear_start_date;?>/<?php echo $lastyear_end_date;?>/<?php echo $dayRangeType;?>/Yearly" target="_blank">Click Here </a> <br/>
                  <?php }?>
                     <?= $val['gc_last_year_value']; ?>
                <?php if($val['sdk_gc_last_year_value']!=$val['gc_last_year_value']) {
                   ?> 
                  </span>  
                <?php } ?>
              </td>
              
              <td> <?= $sdk_status; ?></td>

            <tr>  
          <?php }?>
        </table>

  
</div>
</body>
</html>

