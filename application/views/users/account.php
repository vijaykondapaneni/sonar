<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
    <?php $this->load->view('users/headermenu');?>
    <!--EOC: Top header section-->

	<div id="body">
        <table width="700" cellpadding="0" cellspacing="2" align="center" class="table table-bordered table-striped table-condensed">
        <tbody>
        <tr>
            <td width="130" align="right"> Name: </td>
            <td><?php echo $user['name']; ?></td>
        </tr>
		<tr>
			<td width="130" align="right"> Email: </td>
			<td><?php echo $user['email']; ?></td>
		</tr>
		<tr>
			<td width="130" align="right"> Phone: </td>
			<td><?php echo $user['phone']; ?></td>
		</tr>
		<tr>
			<td align="right">Gender:</td>
			<td><?php echo $user['gender']; ?></td>
		</tr>
		
		<tr>
			<td>&nbsp;</td>
			<td>
			</td>
		</tr>
        </tbody>
    </table>
       
        
	</div>
    <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
    
