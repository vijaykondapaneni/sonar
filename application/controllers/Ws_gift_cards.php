<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ws_gift_cards extends CI_Controller {
   function __construct() {
     parent::__construct();
    
     $this->load->library('form_validation');
      $this->load->library('pagination');
      $this->load->helper(array('form', 'url'));
      $this->load->database();
     
  }  

  public function index(){
    /*print "Test";
    exit();
    if(!$this->session->userdata('isUserLoggedIn')){
            redirect(MAIN_SERVER_URL.'index.php/users/login');
    }*/
  }
  
  function getGiftCardBalance(){

    if(isset($_POST['AccountNo']) && !empty($_POST['AccountNo']) && isset($_POST['ClientId']) && !empty($_POST['ClientId'])){
       // echo "sdf";exit;
      $this->db->select('cgiftnumber,tdatetime,nprice,namtleft');
      $this->db->from('mill_gift_card_sales_with_balance');
      $this->db->where('account_no',$_POST['AccountNo']);
      $this->db->where('ipurchfor',$_POST['ClientId']);
         
      $query = $this->db->get();
     // echo $this->db->last_query();exit;  
      $res =  $query->result_array();
      
      if(!empty($res)){
        echo json_encode($res);
      } else {
        $res = array(); 
        echo json_encode($res);
      }
    } else {
      $res = array(); 
      echo json_encode($res);
    }
  }

}  