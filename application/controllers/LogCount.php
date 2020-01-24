<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LogCount extends CI_Controller {
    
    function __construct()
    {
        parent::__construct();
        
        
    } 
    public function index()
          {
        }
    public function getLogCount(){
    
       $res=$this->db->query('SELECT salon_id,salon_account_id, salon_name  FROM mill_all_sdk_config_details')->result_array();
      
        foreach($res as $key=>$value)
        {
            $temp = array();
            $salon_id=$value['salon_id'];
            $temp['salon_id']=$value['salon_id'];
            $temp['salon_account_id']=$value['salon_account_id'];
            $temp['salon_name']=$value['salon_name'];
            $currentdate = date('Y-m-d');
	                $st_date = $currentdate.' 00:00:00';    
	                $end_date = $currentdate.' 23:59:59';
	                $current_hour = date('H');
	                $st_hour = $current_hour - 2;    
	                $end_hour = $current_hour + 1;  
                    $result = $this->db->query("SELECT * FROM mill_all_salons_sdk_reports_server
                       WHERE
                       salon_id=$salon_id and
                       created_date BETWEEN '".$st_date."' AND '".$end_date."'
                        AND HOUR(created_date) BETWEEN ".$st_hour." AND ".$end_hour." and (session_status=0 or appointment_status =0) GROUP by salon_id ORDER by date(created_date) desc ")->row_array();
                   //echo $this->db->last_query(); 
                    if($result == ""){
                        $temp['status']= "SDK Error";
                    }else{
                         $temp['status']="SDK Working";
                    }
          
            $date = date('Y-m-d');
           $this->db->where('date(StartingTime)',$date);
          $this->db->where('salon_id',$salon_id);
          $qry = $this->db->get('mill_cron_running_logs');
          $temp['count']=$qry->num_rows();
          $salon_data[] = $temp;
        }
        $data['salon_data']=$salon_data;
       $this->load->view("logview",$data);
    
        
       
   }
        
}