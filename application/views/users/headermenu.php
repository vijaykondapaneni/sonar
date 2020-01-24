<div class="row row-bordered background">
        <div class="col-md-12">
            <div class="col-md-8 pull-left" style="margin-left: 40px; margin-top:20px;margin-bottom: 20px;">
                <img src="http://webappcloudsplus.com/assets/images/logo-small.png" alt="">
            </div>
            <div class = "btn-group pull-right"  style="padding:5px 15px 0 10px;">
               <button type = "button" class = "btn btn-info">Welcome <strong><?php echo $user['name']; ?>!</strong></button>

               <button type = "button" class = "btn btn-info dropdown-toggle" data-toggle = "dropdown">
                  <span class = "caret"></span>
                  <span class = "sr-only">Toggle Dropdown</span>
               </button>
               <?php
                $signature = '';
                $timestamp = '';
                if(isset($_GET['signature']) && isset($_GET['timestamp'])){
                  $signature = $_GET['signature'];
                  $timestamp = $_GET['timestamp'];
                }  
               ?>

               <ul class = "dropdown-menu" role = "menu">
                  <?php if ($this->session->userdata('isUserLoggedIn')){
                          if($this->session->userdata['userId']==1){?>
                  <li><a href = "<?php echo base_url();?>index.php/SalonConfigrations/Config?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">Salon's Settings</a></li>
                  <li><a href = "<?php echo base_url();?>index.php/MillSdkReports/getMillSdkReports?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">SDK Reports</a></li>
                  <li><a href = "<?php echo base_url();?>index.php/WoodHouseReports/index?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">WoodHouse Reports</a></li>
                  <li><a href = "<?php echo base_url();?>index.php/Salon554Reports/index?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">Salon554 Reports</a></li>
                  <li><a href = "<?php echo base_url();?>index.php/MillSdkReports/getSalonWeeksDisplay?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">Salon Last Four Weeks</a></li>
                  <?php }
                  }else{ ?>
                  
                  <li><a href = "<?php echo base_url();?>index.php/SalonConfigrations/Config?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">Salon's Settings</a></li>
                  <li><a href = "<?php echo base_url();?>index.php/MillSdkReports/getMillSdkReports?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">SDK Reports</a></li>
                  <li><a href = "<?php echo base_url();?>index.php/WoodHouseReports/index?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">WoodHouse Reports</a></li>
                  <li><a href = "<?php echo base_url();?>index.php/Salon554Reports/index?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">Salon554 Reports</a></li>
                  <li><a href = "<?php echo base_url();?>index.php/MillSdkReports/getSalonWeeksDisplay?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">Salon Last Four Weeks</a></li>
                  <?php }
                  ?>

                  <?php if ($this->session->userdata('isUserLoggedIn')){
                          if($this->session->userdata['userId']==2){?>
                  <li><a href = "<?php echo base_url();?>index.php/Salon554Reports/index?signature=<?php echo $signature?>&timestamp=<?php echo $timestamp?>">Salon554 Reports</a></li>
                  <?php }
                   }
                  ?>

                  <?php 
                  if ($this->session->userdata('isUserLoggedIn')){?>
                  <li><a href = "<?php echo site_url('/users/account')?>">Login Settings </a></li>
                  <li class = "divider"></li>
                  <li><a href = "<?php echo site_url('users/logout')?>">Logout</a></li>
                  <?php } ?>
               </ul>
            </div>
        </div>
    </div>
<style type="text/css">
  .background{
    background-color: #438eb9;
  }
</style>

