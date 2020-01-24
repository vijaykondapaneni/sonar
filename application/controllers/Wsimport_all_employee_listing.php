<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class Wsimport_all_employee_listing extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR IMPORT PAST APPOINTMENTS
    **/
    CONST INSERTED = 0;
    CONST UPDATED = 1;    
    public $salonAccountId;
    public $pemFilePath;
    public $salonMillIp;
    public $salonMillGuid;
    public $salonMillUsername;
    public $salonMillPassword;
    public $salonMillSdkUrl;
    public $startDate;
    public $endDate;
    public $millResponseXml;
    public $millResponseSessionId;    
  
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Employeeimportlisting_model');
    }
    
    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    /**
     * This function for get employee schedule hours last year data
     * @param type $account_no
     */
    function GetEmployeeListing($account_no="")
        {
        if($account_no!=''){
            $account_no = salonWebappCloudDe($account_no);
        }
         $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no);
          if($getConfigDetails->num_rows()>0)
            {
               foreach($getConfigDetails->result_array() as $configDetails)
                {
                     pa($configDetails['salon_name'],'salon name');
                     $this->salonAccountId = $configDetails['salon_account_id'];
                     $this->pemFilePath  =  base_url()."salonevolve.pem";
                     $this->salonMillIp  =  $configDetails['mill_ip_address'];
                     $this->salonMillGuid = $configDetails['mill_guid'];
                     $this->salonMillUsername = $configDetails['mill_username'];
                     $this->salonMillPassword = $configDetails['mill_password'];
                     $this->salonMillSdkUrl = $configDetails['mill_url'];
                     $this->startDate = (!empty($startDate)) ? $startDate : date("Y-m-d");
                     $this->endDate   = (!empty($endDate)) ? $endDate : date("Y-m-d");
                     //Database Log
                     $log['AccountNo'] = $configDetails['salon_account_id'];
                     $log['salon_id'] = $configDetails['salon_id'];
                     $log['StartingTime'] = date('Y-m-d H:i:s');
                     $log['whichCron'] = 'GetEmployeeListing';
                     //$log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                     $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_listing/GetEmployeeListing/'.$this->salonAccountId;
                     $log['CronType'] = 0;
                     $log['id'] = 0;
                     $log_id = $this->Common_model->saveMillCronLogs($log);

                     $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                      $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                      
                      $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                     //pa($this->millResponseSessionId,'Session');
                    
                     if($this->millResponseSessionId){
                        $millMethodParams = array('IncludeDeleted' => 0,'IncludeInactive' =>0);
                        $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetEmployeeListing',$millMethodParams);
                        if(isset($this->millResponseXml['EmpInfo']))
                        {
                            foreach($this->millResponseXml['EmpInfo'] as $mEmpInfo) 
                            {
                                //pa($mEmpInfo,'empinfo',false);
                                //pa('',"Employee Listing--".$mEmpInfo["iid"]); //DEBUGMSG                                                
                                array_walk($mEmpInfo, function (&$value) { $value = trim($value);});

                               // pa('',"GetEmployeeListing");

                                $whereConditions = array('iid' => $mEmpInfo['iid'],'account_no' => $this->salonAccountId);

                                $arrEmployees = $this->Employeeimportlisting_model
                                      ->compareMillEmployeesListing($whereConditions)
                                      ->row_array();
                                //pa('',"DBdata");      
                               
                                if(!empty($arrEmployees))
                                {
                                   $diff_array = array_diff_assoc($mEmpInfo, $arrEmployees);

                                    if(empty($diff_array))
                                    {
                                        continue; //SAME DATA FOUND, SO CONTINUe with the loop
                                    }   
                                    else
                                    {
                                        //UPDATE DATA IN DB 
                                        $diff_array['updatedDate'] = date("Y-m-d H:i:s");
                                        $diff_array['insert_status'] = Wsimport_all_employee_listing::UPDATED;   
                                        
                                        $whereconditions = array();
                                        $whereconditions['iid'] = $mEmpInfo['iid'];
                                        $whereconditions['account_no'] = $this->salonAccountId;
                                   
                                        $res = $this->Employeeimportlisting_model->updateEmployeesListing($whereconditions,$diff_array);
                                       // pa($diff_array,"Updated data ---ID=".$arrEmployees['id']);
                                    }
                                }
                                else // INSERT APPOINTMENT DATA IN DB 
                                {
                                    $mEmpInfo['account_no'] = $this->salonAccountId;
                                    $mEmpInfo['insertedDate'] = date("Y-m-d H:i:s");
                                    $mEmpInfo['updatedDate'] = date("Y-m-d H:i:s");
                                    $mEmpInfo['insert_status'] = Wsimport_all_employee_listing::INSERTED;
                                    $res = $this->Employeeimportlisting_model->insertMillEmployeesListing($mEmpInfo);
                                    $emp_id = $this->db->insert_id();
                                    //pa('',"inserted data ---ID=".$emp_id);
                                }
                            } //foreach ends of Package
                        }
                        else
                        {
                            echo "No Employee Data found in Millennium.";
                        }
                    // Database Log
                    $log['id'] = $log_id;
                    $log_id = $this->Common_model->saveMillCronLogs($log);    
                    $logOff = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('LogOff',$millloginDetails);
                     }else{
                      echo "SESSION ID NOT SET";
                     }
                    
                        
                 }
            }else{
                pa('Salons are inactive or invalid salon');
            }
       } 
   
 }       