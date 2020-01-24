<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
    <div class="row">
        <div class="col-md-12">
            <h1>User Registration</h1>
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
                    <div class="form-group">
                        <input type="text" class="form-control" name="name" placeholder="Name" required="" value="<?php echo !empty($user['name'])?$user['name']:''; ?>">
                      <?php echo form_error('name','<span class="help-block">','</span>'); ?>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" name="email" placeholder="Email" required="" value="<?php echo !empty($user['email'])?$user['email']:''; ?>">
                      <?php echo form_error('email','<span class="help-block">','</span>'); ?>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="phone" placeholder="Phone" value="<?php echo !empty($user['phone'])?$user['phone']:''; ?>">
                    </div>
                    <div class="form-group">
                      <input type="password" class="form-control" name="password" placeholder="Password" required="">
                      <?php echo form_error('password','<span class="help-block">','</span>'); ?>
                    </div>
                    <div class="form-group">
                      <input type="password" class="form-control" name="conf_password" placeholder="Confirm password" required="">
                      <?php echo form_error('conf_password','<span class="help-block">','</span>'); ?>
                    </div>
                    <div class="form-group">
                        <?php
                        if(!empty($user['gender']) && $user['gender'] == 'Female'){
                            $fcheck = 'checked="checked"';
                            $mcheck = '';
                        }else{
                            $mcheck = 'checked="checked"';
                            $fcheck = '';
                        }
                        ?>
                        <div class="radio">
                            <label>
                            <input type="radio" name="gender" value="Male" <?php echo $mcheck; ?>>
                            Male
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                              <input type="radio" name="gender" value="Female" <?php echo $fcheck; ?>>
                              Female
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <input class="btn btn-primary" name="regisSubmit" type="submit" value="Submit"/>
                    </div>
                </form>
        </div>    
    </div>
        
    <p class="footInfo">Already have an account? <a href="<?php echo base_url(); ?>users/login">Login here</a></p>              
        
        
       
	</div>
    <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>






