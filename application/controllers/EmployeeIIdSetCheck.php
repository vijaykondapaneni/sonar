<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EmployeeIIdSetCheck extends CI_Controller {
    
    function __construct()
    {
        parent::__construct();
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
       
    } 

	public function index()
	{
	    if($_SERVER['REMOTE_ADDR']!='10.10.3.108'){
	    	$server_remote = substr($_SERVER['REMOTE_ADDR'], 0, 6);
	    }else{
	    	$server_remote = $_SERVER['REMOTE_ADDR'];
	    }
	    
	    if(($server_remote == '10.10.3.108') || ($server_remote == '183.83')){
            
	    }else{
	    	if(!$this->session->userdata('isUserLoggedIn')){
             redirect(MAIN_SERVER_URL.'users/login/5');
             exit();
            }
	    }
	  
	    //for appointment 

	    $data['staff_count'] = $this->DB_ReadOnly->query("SELECT salon_id,salon_name,salon_account_id FROM mill_all_sdk_config_details")->result_array();
	    $this->load->view('staff_count',$data);
		
	}
	
}
