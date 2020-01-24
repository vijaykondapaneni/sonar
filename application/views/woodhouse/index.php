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
<form name="index" action="<?php print base_url();?>index.php/<?php echo $this->uri->segment(1); ?>/reporttype" method="get">
<p>
<div class="container">
<select id="reporttype" name="reporttype" class="form-control">                      
<option value="MR041">MR041</option>
<option value="MR045">MR045</option>
<option value="MA055">MA055</option>
<option value="MR085">MR085</option>
</select>
<div>Note: Please select atmost 10 days range for appropriate results</div>
&nbsp; <b>From:</b> <input type="text" name="datepicker_start" id="datepicker" class="form-control"> &nbsp;  
       <b>To:</b> <input type="text" name="datepicker_end" id="datepicker2" class="form-control"></p> <br/>

<input type="submit" name="submit" class="btn btn-primary" value="Get Reports">
<input type="hidden" name="signature" value="<?php echo $signature;?>">
<input type="hidden" name="timestamp" value="<?php echo $timestamp;?>">
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
