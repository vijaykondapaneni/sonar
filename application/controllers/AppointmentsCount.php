<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class AppointmentsCount extends REST_Controller {
  function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Newsalon_model');
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
       // $this->load->library("security");
    }
	public function getAppointmentsCount($account_no){
       $this->DB_ReadOnly->where('AccountNo',$account_no);
       $res = $this->DB_ReadOnly->get('mill_appointments');
       echo $res->num_rows();
       //echo $this->DB_ReadOnly->last_query();
	}


  public function getMillAppointmentsCount(){
      
       $all = $this->DB_ReadOnly->get(MILL_ALL_SDK_CONFIG_DETAILS)->result_array();
    //pa($this->DB_ReadOnly->last_query());
      $i=1;
    foreach ($all as $key => $value) {
      $account_no =  $value['salon_account_id'];
      $salon_id =  $value['salon_id'];


     $sql_get_clients_serviced_count = $this->DB_ReadOnly->query("SELECT count(DISTINCT ClientId) as client_count FROM 
            ".MILL_APPTS_TABLE."  
            WHERE 
            AccountNo = '".$account_no."' and 
            SlcStatus != 'Deleted' and 
            ClientId != '-999' and 
            str_to_date(AppointmentDate, '%m/%d/%Y') >= '".date('Y-m-d')."'");



      $this->DB_ReadOnly->where('AccountNo',$account_no);
      $this->DB_ReadOnly->where('AccountNo',$account_no);
      $res = $this->DB_ReadOnly->get('mill_appointments');
      $intservercount =  $res->num_rows();
      //print $this->DB_ReadOnly->last_query();
      $plusservercount = $this->salonInfo = $this->Common_model->getCurlData('https://saloncloudsplus.com/millApptCount/getPostCount',array('salon_id' => $salon_id));

      pa($i++.'--'.$value['salon_id']."------------".$value['salon_account_id']."---------".$value['salon_name']."------Intermediate--".$intservercount." ".'-----Plus--'.$plusservercount);

     
     }
  }

  public function getAptCount() {
        $account_no = $_POST['account_no'];
        if($account_no!='' and isset($account_no)){
            $get_count = $this->db->query("SELECT COUNT(*) FROM `mill_appointments` WHERE `AccountNo` = ".$account_no."")->result_array();
          //print_r($get_count);
          echo $get_count[0]['COUNT(*)'];
        }else{
            echo "Error!";
        }
    }

   public function getnextfourdaysAptCount() {
        $account_no = $_POST['account_no'];
        if($account_no!='' and isset($account_no)){
          $start_date = date('Y-m-d');
          $end_date =  date("Y-m-d", strtotime(" +4 days"));
          $this->db->get('mill_appointments');
          $get_count = $this->db->query("SELECT COUNT(*) FROM `mill_appointments` WHERE `AccountNo` = ".$account_no." and ClientId!='-999' and SlcStatus!='Deleted' and STR_TO_DATE(AppointmentDate, '%m/%d/%Y') >= '".$start_date."' and  STR_TO_DATE(AppointmentDate, '%m/%d/%Y') <= '".$end_date."'")->result_array();
          //print_r($get_count);
          echo $get_count[0]['COUNT(*)'];
        }else{
            echo "Error!";
        }
    }  
	 
  public function getMillAptsCount() {
        $account_no = $_POST['account_no'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        if($account_no!='' and isset($account_no)){
          //$start_date = date('Y-m-d');
          //$end_date =  date("Y-m-d", strtotime(" +4 days"));
          $this->db->get('mill_appointments');
          $get_count = $this->db->query("SELECT COUNT(*) FROM `mill_appointments` WHERE `AccountNo` = ".$account_no." and ClientId!='-999' and SlcStatus!='Deleted' and STR_TO_DATE(AppointmentDate, '%m/%d/%Y') >= '".$start_date."' and  STR_TO_DATE(AppointmentDate, '%m/%d/%Y') <= '".$end_date."'")->result_array();
          //print_r($get_count);
        //  print_r($this->db->last_query());
          echo $get_count[0]['COUNT(*)'];
        }else{
            echo "Error!";
        }
    }  
        	
}