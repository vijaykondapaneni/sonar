<?php 
$this->load->view('users/headermenu');
error_reporting(E_ERROR | E_PARSE);?>
<script type="text/javascript" src="<?php print base_url();?>/assets/js/pace.min.js"></script>
<style type="text/css">
 .pace {
  -webkit-pointer-events: none;
  pointer-events: none;

  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;

  z-index: 2000;
  position: fixed;
  margin: auto;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  height: 5px;
  width: 200px;
  background: #fff;
  border: 1px solid #29d;

  overflow: hidden;
}

.pace .pace-progress {
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  -ms-box-sizing: border-box;
  -o-box-sizing: border-box;
  box-sizing: border-box;

  -webkit-transform: translate3d(0, 0, 0);
  -moz-transform: translate3d(0, 0, 0);
  -ms-transform: translate3d(0, 0, 0);
  -o-transform: translate3d(0, 0, 0);
  transform: translate3d(0, 0, 0);

  max-width: 200px;
  position: fixed;
  z-index: 2000;
  display: block;
  position: absolute;
  top: 0;
  right: 100%;
  height: 100%;
  width: 100%;
  background: #29d;
}

.pace.pace-inactive {
  display: none;
}
</style>
<div class="container">
 <center><h2>The Woodhouse Day Spa - Zionsville</h2></center>
 <h3><?php print ("Daily Performance Indicators");?> - MR055</h3>
 <h4><?php echo $displaytime; ?>
 <div class="pull-right"><button onclick="goBack()" class="btn btn-success">Back</button></div></h4>


<div class="container table-responsive">

<table class="table  table-bordered" width="100%" border="0" cellpadding="0" cellspacing="0" >
  <tr style="background:#438eb9;color:#fff;">
    <td width="112" rowspan="2" align="center" valign="top">Emp Name</td>
    <td height="27" colspan="3" align="center">Client </td>
    <td width="122" rowspan="2" align="center">Retail Units/<br />
      Retail Client
    </td>
    <td width="146" rowspan="2" align="center">Retail Units/<br />
Service Client</td>
    <td width="107" rowspan="2" align="center">Average Service Ticket</td>
    <td width="177" rowspan="2" align="center">Average Service Ticket</td>
    <td colspan="2" valign="top" align="center">Reebok</td>
  </tr>
  <tr style="background:#438eb9;color:#fff;">
    <td width="37" height="30" align="center">New</td>
    <td width="75" align="center">Repeat</td>
    <td width="48" align="center">Total</td>
    <td width="143" align="center">Count</td>
    <td width="148" align="center"> %</td>
  </tr>
  <tbody>
  <?php 
			$total = count($all);
			$total_newclients = 0;
			$total_repeat_clients = 0;
			$total_total_clients = 0;
			$total_retialunits_retailclients = 0;
			$total_retialunits_serviceclients = 0;
			$total_avg_service_ticket = 0;
			$total_avg_retail_ticket = 0;
			if(!empty($employee_details)){ 
				foreach($employee_details as $details){
						$total_newclients = $total_newclients + $details['new_clients'];
						$total_repeat_clients = $total_repeat_clients + $details['repeat_clients'];
						$total_total_clients = $total_total_clients + $details['total_clients'];
						$total_retialunits_retailclients = $total_newclients + $details['retialunits_retailclients'];
						$total_retialunits_serviceclients = $total_retialunits_serviceclients + $details['retialunits_serviceclients'];
						$total_avg_service_ticket = $total_newclients + $details['avg_service_ticket'];
						$total_avg_retail_ticket = $total_newclients + $details['avg_retail_ticket'];					
  ?>
			<tr>
				<td height="33" align="center"><?php echo $details['cfirstname']." ".$details['clastname']; ?></td>
				<td align="center"><?php echo $details['new_clients']; ?></td>
				<td align="center"><?php echo $details['repeat_clients']; ?></td>
				<td align="center"><?php echo $details['total_clients']; ?></td>
				<td align="center"><?php echo $details['retialunits_retailclients']; ?></td>
				<td align="center"><?php echo $details['retialunits_serviceclients']; ?></td>
				<td align="center">$<?php echo $details['avg_service_ticket']; ?></td>
				<td align="center">$<?php echo $details['avg_retail_ticket']; ?></td>
				<td align="center">0.00</td>
				<td align="center">0.00</td>
			 </tr>
  <?php } } ?>
			<tr>
				<td height="33" align="center">Total Averages:</td>
				<td align="center"><?php echo number_format($total_newclients/$total,2); ?></td>
				<td align="center"><?php echo number_format($total_repeat_clients/$total,2); ?></td>
				<td align="center"><?php echo number_format($total_total_clients/$total,2); ?></td>
				<td align="center"><?php echo number_format($total_retialunits_retailclients/$total,2); ?></td>
				<td align="center"><?php echo number_format($total_retialunits_serviceclients/$total,2); ?></td>
				<td align="center">$<?php echo number_format($total_avg_service_ticket/$total,2); ?></td>
				<td align="center">$<?php echo number_format($total_avg_retail_ticket/$total,2); ?></td>
				<td align="center">0.00</td>
				<td align="center">0.00</td>
			 </tr>	
  </tbody>
</table>
</div>

</div>
<style type="text/css">
    .action_calbtns ul li a.btn_del {
        text-align: left;
        border-radius: 0px;
        border: 0px none;
    }
</style>
<script>
function goBack() {
    window.history.back();
}
</script>
