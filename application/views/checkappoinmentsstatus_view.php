<htlm>
    <head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css">
      
    </head>


    <body>

    
         <div class="container">
 <h3>Check Appoinment Status (Last Updated Date)</h3>
 <br>
    
<table id="example"   class="issues_grid table table-striped table-bordered table-hover"  >
        <!--<table id="example" class="display" cellspacing="0" width="100%">-->
        <thead>
                  <tr>
                    <th>Sl no</th>
                    <th>Salon Id</th>
                    <th>Salon Name</th>
                    <th>Salon Acc No</th>
                    <th>Checkdin</th>
                    <th>Oneday </th>
                    <th>OneWeek</th>
                    <th>Twomonths </th>
                    <th>Fourmonths </th>
                    <th>Sixmonths </th>
                    <th>Status</th> 
                   </tr>

      </thead>
                
      <tbody>
                  <?php  
                    $today = date("Y-m-d") ;
                  $i= 1; 
                  foreach ($result as $row){
                                if($row['sdkerror']['0']['session_status'] == '0'){

                                          $status= "<span class='label label-success'>SDK Working</span>";
                                        
                                        
                                      }
                                      else{
                                        $status= "<span class='label label-sm label-danger'>SDK Error</span>";
                                        
                                      }



                   ?>
                   
                      <tr>
                        <td><?php echo $i;?></td>
                        <td><?php echo $row['salon_id'];?></td>
                        <td><?php echo $row['salon_name'];?></td>
                        <td><?php echo $row['salon_account_id'];?></td>
                        <td><?php echo $row['starttime']['0']['StartingTime'];?></td> 
                        <td>
                        <?php 
                       $oneday = date('Y-m-d', strtotime($row['oneday']['0']['CrateatedDate']));
                       // $oneday = date('Y-m-d');
                        ?>
                       
                        <?php if($today != $oneday){ ?>
                            <span style="color:black;background-color: #ff1919;">

                            <?php echo $row['oneday']['0']['CrateatedDate'];?></span>
                            <?php } else {  ?>
                            <span >

                            <?php echo $row['oneday']['0']['CrateatedDate'];?></span>
                            <?php } ?>
                          
                        </td> 

                        <td>
                        <?php 
                        $oneweek = date('Y-m-d', strtotime($row['oneweek']['0']['CrateatedDate']));
                        ?>
                       
                        <?php if($today != $oneweek){ ?>
                            <span style="color:black;background-color: #ff1919;">

                            <?php echo $row['oneweek']['0']['CrateatedDate'];?></span>
                            <?php } else {  ?>
                            <span >

                            <?php echo $row['oneweek']['0']['CrateatedDate'];?></span>
                            <?php } ?>
                          
                        </td> 
                        <td>
                        <?php 
                       $twomonths = date('Y-m-d', strtotime($row['twomonths']['0']['CrateatedDate']));
                        ?>
                       
                        <?php if($today != $twomonths){ ?>
                            <span style="color:black;background-color: #ff1919;">

                            <?php echo $row['twomonths']['0']['CrateatedDate'];?></span>
                            <?php } else {  ?>
                            <span >

                            <?php echo $row['twomonths']['0']['CrateatedDate'];?></span>
                            <?php } ?>
                          
                        </td> 
                        <td>
                        <?php 
                       $fourmonths = date('Y-m-d', strtotime($row['fourmonths']['0']['CrateatedDate']));
                        ?>
                       
                        <?php if($today != $fourmonths){ ?>
                            <span style="color:black;background-color: #ff1919;">

                            <?php echo $row['fourmonths']['0']['CrateatedDate'];?></span>
                            <?php } else {  ?>
                            <span >

                            <?php echo $row['fourmonths']['0']['CrateatedDate'];?></span>
                            <?php } ?>
                          
                        </td> 
                        <td>
                        <?php 
                       $sixmonths = date('Y-m-d', strtotime($row['sixmonths']['0']['CrateatedDate']));
                        ?>
                       
                        <?php if($today != $sixmonths){ ?>
                            <span style="color:black;background-color: #ff1919;">

                            <?php echo $row['sixmonths']['0']['CrateatedDate'];?></span>
                            <?php } else {  ?>
                            <span >

                            <?php echo $row['sixmonths']['0']['CrateatedDate'];?></span>
                            <?php } ?>
                          
                        </td> 

                        <td><?php echo $status;?></td> 
                      </tr>
                     
 
              <?php $i++; }?>
          </tbody>


        </table>

  
            
</div>
</body>
<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>
<!-- <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#example').DataTable();
} );

</script> -->


</html>

