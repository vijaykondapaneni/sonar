<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/sdkreports/jquery.dataTables.css">
<!-- jQuery -->
<script type="text/javascript" charset="utf8" src="<?php echo base_url();?>assets/sdkreports/jquery-1.9.1.min.js"></script>
 
<!-- DataTables -->
<script type="text/javascript" charset="utf8" src="<?php echo base_url();?>assets/sdkreports/jquery.dataTables.min.js"></script>

<link rel="stylesheet" href="<?php echo base_url();?>assets/sdkreports/bootstrap.min.css">

<script src="<?php echo base_url();?>assets/sdkreports/bootstrap.min.js"></script>

   

<div class="container">
    <h1 style="margin-bottom: 10px;">Mill SDK Reports</h1>
    <table id="example" class="display" width="100%" cellspacing="0">
	<thead>
	    <tr>
	        <th>Id</th>
	        <th>Salon Id</th>
	        <th>Salon Name</th>
	        <th>Session Status</th>
	        <th>Appointment Status</th>
	        <th>Date</th>
	       
	    </tr>
	</thead>
	<tfoot>
	    <tr>
	       <th>Id</th>
	       <th>Salon Id</th>
	       <th>Salon Name</th>
	       <th>Session Status</th>
	       <th>Appointment Status</th>
	       <th>Date</th>
	    </tr>
	</tfoot>
	</table>
</div>

<script type="text/javascript">
	$( document ).ready(function() {

	 $('#example').dataTable({
         //"order": [[ 5, "desc" ]],
         "aaSorting": [
                   [5, "desc"]
               ],
         "bProcessing": true,
         "sAjaxSource": '<?php echo base_url();?>MillSdkReports/getAjaxData',
         "aoColumns": [
                { mData: 'id' } ,
                { mData: 'salon_id' },
                { mData: 'salon_name' },
                { mData: 'session_status' },
                { mData: 'appointment_status' },
                { mData: 'created_date'},
         ],

	  });

	

});
</script>
