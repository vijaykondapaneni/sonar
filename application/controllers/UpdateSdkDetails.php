<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library_checksdk_new.php');
    }

class UpdateSdkDetails extends REST_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Update SDK Details
    **/
    public  $salon_id;
    public  $startDate;
    public  $endDate;
    public  $currentDate;
    private $salonId;
    private $salonDetails;
    private $dayRangeType;
    private $lastYearStartDate;
    private $lastYearEndDate;   
    private $ouathResponse;
        
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('OwnerWebServices_model');
        $this->load->library('webserviceaccess');
    }
    
    
    private function __getAccessWesbService($service,$salon_id){
               $this->WebAccessResponse = $this->webserviceaccess->validateWebAppWs($service,$salon_id);
               return $this->WebAccessResponse;
    }
    /**
     * Default index Fn
     */
    public function index(){
                    $this->__getAccessWesbService();
                    // To generate Secure Auth token for specific salon.
                    print "Test";
        }
    
    /**
     * This function for update sdk details
     * @param type $account_no
     */
    function updateMillSdk()
        { 
           
            if(($_POST['salon_id']!='') && ($_POST['salon_account_id']!='') && ($_POST['mill_url']!='') && ($_POST['mill_guid']!='') && ($_POST['mill_password']!='') && ($_POST['mill_username']!=''))
            {
              $salon_account_id = $_POST['salon_account_id'];  
              $salon_id = $_POST['salon_id'];
              $this->db->where('salon_id',$salon_id);
              $this->db->where('salon_account_id',$salon_account_id);
              $num_rows = $this->db->get('mill_all_sdk_config_details')->num_rows();
              if($num_rows<=0){
               $data['update_status'] = "false"; 
               $response_array[] = $data;
               $response_code = 200;
                goto response;
              }
              $this->db->where('salon_id',$salon_id);
              $res =  $this->db->get('mill_all_sdk_config_details')->row_array();
              $salon_id = $res['salon_id'];
              $salon_name = $res['salon_name'];
              $mill_url = $_POST['mill_url'];
              $mill_guid = $_POST['mill_guid'];
              $mill_username = $_POST['mill_username'];
              $mill_password = $_POST['mill_password']; 
              $updated_by = $_POST['login_id'];
              $updated_at = date('Y-m-d H:i:s'); 
              $update_array['mill_url'] = $mill_url;
              $update_array['mill_guid'] = $mill_guid;
              $update_array['mill_username'] = $mill_username;
              $update_array['mill_password'] = $mill_password;
              $update_array['updated_by'] = $updated_by;
              $update_array['updated_at'] = $updated_at;
              $this->db->where('salon_account_id',$salon_account_id);
              $updateqry = $this->db->update('mill_all_sdk_config_details',$update_array);
              if($updateqry){
                $update_status = 'true';
              }else{
                $update_status = 'false';
              }
              $data['update_status'] = $update_status;
              $response_array[] = $data;
              $response_code = 200;
            }else{
               $data['update_status'] = "false"; 
               $response_array[] = $data;
               $response_code = 200;
               goto response; 
            }

           response:
           $this->response($response_array, $response_code);
            
       }

    /**
     * This function for update sdk details
     * @param type $account_no
     */
    function verifySdk()
        { 
           
            if(($_POST['salon_id']!='') && ($_POST['salon_account_id']!='') && ($_POST['mill_url']!='') && ($_POST['mill_guid']!='') && ($_POST['mill_password']!='') && ($_POST['mill_username']!=''))
            {
              $salon_account_id = $_POST['salon_account_id'];  
              $salon_id = $_POST['salon_id'];
              $this->db->where('salon_id',$salon_id);
              $this->db->where('salon_account_id',$salon_account_id);
              $num_rows = $this->db->get('mill_all_sdk_config_details')->num_rows();
              if($num_rows<=0){
               $data['update_status'] = "false"; 
               $data['sdk_status'] = "false"; 
               $response_array[] = $data;
               $response_code = 200;
                goto response;
              }
              $this->db->where('salon_id',$salon_id);
              $res =  $this->db->get('mill_all_sdk_config_details')->row_array();
              $salon_id = $res['salon_id'];
              $salon_name = $res['salon_name'];
                      
              $mill_url = $_POST['mill_url'];
              $mill_guid = $_POST['mill_guid'];
              $mill_username = $_POST['mill_username'];
              $mill_password = $_POST['mill_password']; 
              //$updated_by = $_POST['login_id']; 
              //$updated_at = date('Y-m-d H:i:s');
              $update_array['mill_url'] = $mill_url;
              $update_array['mill_guid'] = $mill_guid;
              $update_array['mill_username'] = $mill_username;
              $update_array['mill_password'] = $mill_password;
              
              $this->db->where('salon_account_id',$salon_account_id);
              // check sdk work or not
              $data_test = array('salon_id'=>$salon_id,'salon_name'=>$salon_name);
              $this->db->insert('mill_all_salons_sdk_reports_server', $data_test);
              $insert_id = $this->db->insert_id();
              $current_date = date('Y-m-d H:i:s');
              $millloginDetails = array('User' => $mill_username,'Password' => $mill_password);
              $nusoap_library = new Nusoap_library_checksdk_new($mill_url.'?WSDL','wsdl','','','','');
              $millResponseSessionId = $nusoap_library->soap_library($mill_url,$mill_guid)->getMillMethodCall($current_date,'SESSION',$insert_id,$salon_id,'Logon',$millloginDetails);
              if($millResponseSessionId==''){
                 $data['session_status'] = "false"; 
                 $data['session_message'] = "false"; 
                 $data['appt_status'] = "false"; 
                 $data['appt_message'] = "false"; 
                 $response_array[] = $data;
                 $response_code = 200;
                 goto response; 
              }
              if(isset($millResponseSessionId['faultcode']) && $millResponseSessionId['faultcode']!='')
              {
                 $session_status = "false";
                 $session_message = $millResponseSessionId['faultstring'];
                 $appt_status = "false";
                 $appt_message = "sdk error";
              }else{
              	 $session_status = "true";
              	 $session_message = "true";
              	 $startDate = date("Y-m-d");
	             $endDate = date("Y-m-d");
	             $millMethodParamsSession = array('StartDate' => $startDate,'EndDate' => $endDate);
                 $millResponseXml = $nusoap_library->getMillMethodCall($current_date,'APPOINTMENT',$insert_id,$salon_id,'GetAllAppointmentsByDate',$millMethodParamsSession);
                 //pa($millResponseXml,'millResponseXml');	      
                 if($millResponseXml)
	             {
	                 if(isset($millResponseXml['faultcode']) && $millResponseXml['faultcode']!='')
	                 {
	                    $appt_status = "false";
	                    $appt_message = $millResponseXml['faultstring'];	
	                 }else{
                        $appt_status = "true";
	                    $appt_message = "true";
	                 }
	             }else{
	             	$appt_status = "true";
	                $appt_message = "true";
	             }      	

              }

              $data['session_status'] = $session_status;
              $data['session_message'] = $session_message; 
              $data['appt_status'] = $appt_status;
              $data['appt_message'] = $appt_message; 
              $response_array[] = $data;
              $response_code = 200;
               
            }else{
               $data['session_status'] = "false"; 
               $data['session_message'] = "false"; 
               $data['appt_status'] = "false"; 
               $data['appt_message'] = "false"; 
               $response_array[] = $data;
               $response_code = 200;
               goto response; 
            }

           response:
           $this->response($response_array, $response_code);
            
       }    
   
 }       