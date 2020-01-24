<link href="<?php echo base_url();?>assets/css/jquery-ui.css" rel="stylesheet">
<?php $this->load->view('users/headermenu');?>
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
<?php
$signature = '';
$timestamp = '';
if(isset($_GET['signature']) && isset($_GET['timestamp'])){
  $signature = $_GET['signature'];
  $timestamp = $_GET['timestamp'];
}  
?>
<h1>Salon554 Reports </h1>
<form name="index" action="<?php print base_url();?>index.php/<?php echo $this->uri->segment(1); ?>/reporttype" method="get">
<p>
<div class="container">
<select id="staff_id" name="staff_id" class="form-control">
  <option value="0_">Select Staff</option>                      
  <?php foreach($allstaff as $staff_key=>$staff_value){?>
  <option value="<?php echo $staff_value['emp_iid']?>_<?php echo $staff_value['name'];?>"
><?php echo $staff_value['name'];?></option>
  <?php }?>

</select>
<select id="year_reports" name="year_reports" class="form-control" style="margin-top: 20px;margin-bottom: 20px;">
         <!--option value="2016">2016</option-->
         <option value="2017">2017</option>
       </select>
<input type="submit" name="submit" class="btn btn-primary" value="Get Reports">
<input type="hidden" name="signature" value="<?php echo $signature;?>">
<input type="hidden" name="timestamp" value="<?php echo $timestamp;?>">
<input type="hidden" name="salon_code" value="<?php echo $salonAccountId;?>">
</div>
</p>
</form>
</body>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <script>
  $( function() {
    $( "#datepicker" ).datepicker();
  } );
  $( function() {
    $( "#datepicker2" ).datepicker();
  } );
  </script>
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> -->
</html>
