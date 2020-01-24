<?php
error_reporting(1);
defined('BASEPATH') OR exit('No direct script access allowed');

if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library_checksdk_new.php');
    }

class MillCurrentStatus extends CI_Controller {
   function __construct() {
     parent::__construct();
     $this->load->model('Common_model');
    }
  /**
   #function for get mill sdk details
  */
  public function getCheckMillSdk($account_no=0){
            $getAllSalons = $this->Common_model->getMillSdkConfigDetails($account_no)->result_array();
            /*pa($getAllSalons,'',true);
            exit();*/
                foreach($getAllSalons as $value)
                {
                    // insert record in database
                    $current_date = date('Y-m-d H:i:s');
                    $salon_id = $value['salon_id'];
                    $salon_name = $value['salon_name'];
                    $data = array('salon_id'=>$salon_id,'salon_name'=>$salon_name);
                    $this->db->insert('mill_all_salons_sdk_reports_server', $data);
                    $insert_id = $this->db->insert_id();  
                    //pa($insert_id);
                    $mill_url = $value['mill_url'];
                    $mill_guid = $value['mill_guid'];
                    $mill_password = $value['mill_password'];
                    $mill_username = $value['mill_username'];
                    $mill_ip_address = $value['mill_ip_address'];
                    $salon_account_id = $value['salon_account_id'];
                    $millloginDetails = array('User' => $mill_username,'Password' => $mill_password);
                    
                    $nusoap_library = new Nusoap_library_checksdk_new($mill_url.'?WSDL','wsdl','','','','');
                    $millResponseSessionId = $nusoap_library->soap_library($mill_url,$mill_guid)->getMillMethodCall($current_date,'SESSION',$insert_id,$salon_id,'Logon',$millloginDetails);
                    // Insert One Record
                    pa($millResponseSessionId,'',false); 
                    if($millResponseSessionId){
                      if(isset($millResponseSessionId['faultcode']) && $millResponseSessionId['faultcode']!=''){
                        /*echo "11";
                        exit();*/
                        $updatedata = array('end_date'=>$current_date,'session_status'=>1,'session_error'=>$millResponseSessionId['faultstring']);
                        $this->db->where('id',$insert_id);
                        $this->db->where('salon_id',$salon_id);
                        $this->db->update('mill_all_salons_sdk_reports_server', $updatedata);
                        //echo $this->db->last_query();
                      }
                        $startDate = date("Y-m-d", strtotime(" +1 year"));
                        $endDate = date("Y-m-d", strtotime(" +1 year"));
                        $millMethodParams = array('StartDate' => $startDate,'EndDate' => $endDate);
                        $millResponseXml = $nusoap_library->getMillMethodCall($current_date,'APPOINTMENT',$insert_id,$salon_id,'GetAllAppointmentsByDate',$millMethodParams);
                        if($millResponseXml){
                          if(isset($millResponseXml['faultcode']) && $millResponseXml['faultcode']!=''){
                            $updatedata = array('end_date'=>$current_date,'appointment_status'=>1,'appointment_error'=>$millResponseXml['faultstring']);
                            $this->db->where('id',$insert_id);
                            $this->db->where('salon_id',$salon_id);
                            $this->db->update('mill_all_salons_sdk_reports_server', $updatedata);
                            //echo $this->db->last_query();
                          }
                          $updatedata = array('end_date'=>$current_date);
                          // $update = $this->Common_model->updateCheckSDKData($insert_id,$salon_id,$updatedata);
                          $this->db->where('id',$insert_id);
                          $this->db->where('salon_id',$salon_id);
                          $this->db->update('mill_all_salons_sdk_reports_server', $updatedata);
                          //echo $this->db->last_query();
                        }
                    }
                    else{
                        pa('SESSION-Error');
                       /* $updatedata = array('end_date'=>$current_date,'session_status'=>1,'session_error'=>'SESSION-Error','appointment_status'=>1);
                        $this->db->where('id',$insert_id);
                        $this->db->where('salon_id',$salon_id);
                        $this->db->update('mill_all_salons_sdk_reports_server', $updatedata);*/
                    } 
            }  // for each close  
   }
} 

