<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
    <div class="row">
        <div class="col-md-12">
            <h1>Login</h1>
            
        </div>
    </div>

	<div id="body">
        <div id="infoMessage">
            <?php
            if(!empty($success_msg)){
                echo '<p class="statusMsg">'.$success_msg.'</p>';
            }elseif(!empty($error_msg)){
                echo '<p class="statusMsg">'.$error_msg.'</p>';
            }
            ?>
        </div>
  
        <div class="container">
            <div class="col-md-5">
                    <form action="" method="post">
                   <div class="form-group has-feedback">
                       <input type="email" class="form-control" name="email" placeholder="Email" required="" value="">
                       <?php echo form_error('email','<span class="help-block">','</span>'); ?>
                   </div>
                   <div class="form-group">
                     <input type="password" class="form-control" name="password" placeholder="Password" required="">
                     <?php echo form_error('password','<span class="help-block">','</span>'); ?>
                   </div>
                   <div class="form-group">
                       <input class="btn btn-primary" name="loginSubmit" type="submit" value="Submit"/>
                   </div>
               </form> 
            </div>    
        </div>
    <?php /*    
    <p class="footInfo">Don't have an account? <a href="<?php echo base_url(); ?>users/registration">Register here</a></p>
     */ ?> 
       
	</div>
    <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
