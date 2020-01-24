<head>
  <title>Reports</title>

  <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
  <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>
  
   <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"/>
   <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css"/>
  
</head>
<script type="text/javascript">
    $(document).ready(function() {
    $('#example').DataTable( {
        dom: 'Bfrtip',
        buttons: [
             'csv'
        ]
    } );
} );
</script>

<table id="example" class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th>AccountNo</th>
                <th>ClientId</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Source</th>
                <th>Stylist</th>                
                <th>Client Appointment</th>
                <th>Retail</th>
                <th>First Visit</th>
                <th>First Visit Month</th>
                <th>Appointment Date</th>
                <th>Revenue</th>
                <th>Appt Count</th>
            </tr>
        </thead>
         <tbody>

            <?php if(!empty($final_display)){
                foreach ($final_display as $key => $value) { ?>
                    <tr>
                        <td><?php echo $value['account_no'];?></td>
                        <td><?php echo $value['client_id'];?></td>
                        <td><?php echo $value['first_name'];?></td>
                        <td><?php echo $value['last_name'];?></td>
                        <td><?php echo $value['email'];?></td>
                        <td><?php echo $value['phone_number'];?></td>
                        <td><?php echo $value['source'];?></td>
                        <td><?php echo $value['employee_name'];?></td>
                        <td><?php echo $value['service'];?></td>
                        <td><?php echo $value['product'];?></td>
                        <td><?php echo $value['first_visit_date'];?></td>
                        <td><?php echo $value['first_visit_month'];?></td>
                        <td><?php echo $value['appt_date'];?></td>
                        <td><?php echo "$"." ".number_format($value['revenue'],2);?></td>
                        <td><?php echo $value['appt_count'];?></td>
                       
                    </tr>
                <?php }
            ?>
               

            <?php } ?>

            
           
        </tbody>
        
    </table>