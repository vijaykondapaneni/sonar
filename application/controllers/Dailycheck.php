<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
    }
class Dailycheck extends CI_Controller {
   function __construct() {
     parent::__construct();
     $this->load->model('Common_model');
     $this->load->model('MillSdkReports_model');
     $this->load->model('User');
     $this->load->library('template');
     
  }  

  public function index(){
    /*print "Test";
    exit();*/
    if(!$this->session->userdata('isUserLoggedIn')){
            redirect(MAIN_SERVER_URL.'index.php/users/login');
    }
  }
     function getsdkdetails(){
       $salon_id = $_POST['salon_id'];
       $this->db->select('mill_username,mill_password,mill_guid,mill_url');
       $this->db->from('mill_all_sdk_config_details');
       $this->db->where('salon_id',$salon_id);
         
        $query = $this->db->get();
        
        $res =  $query->row_array();
      
         if(!empty($res)){
        
           echo json_encode($res);
         }
         else{
             
             echo "Invalid .";
         }
    }

  }  