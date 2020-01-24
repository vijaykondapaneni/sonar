<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}

class Wsimport_all_employee_weekwise_data extends CI_Controller
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
    
    public $transactionIds;
    public $currentDate;
    public $nusoap_library;
    
    private $salonId;
    private $salonInfo;
    private $dayRangeType; 


    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Employeeimportweekwise_model');
        //$this->load->library('nusoap_library');
    }
    
    /**
     * Default Index Fn
     */
    public function index(){print "Test";}
    
    /**
     * 
     * @param type $dayRangeType
     * @param type $s
     * @param type $e
     */
    function __getStartEndDate($dayRangeType,$s = '', $e = '')
    {
        $this->dayRangeType =  $dayRangeType;
        
        switch ($this->dayRangeType) {
              case "IndividualWeek":
                     /* $yearStartDate = date("Y")."-01-01";
                      $yearEndDate = date("Y")."-12-31";*/
                      $MonthStartDate = date("Y-m-")."01";
                      $MonthEndDate = $this->currentDate;
                      $this->startDate = getDateFn(strtotime($MonthStartDate));
                      $this->endDate   =  getDateFn(strtotime($MonthEndDate));
                 break; 
                 case "CUSTOMDATE":
                          $this->startDate = $s;
                          $this->endDate = $e;
                 break;                         
                 default:
                          $this->startDate =  $this->currentDate;
                          $this->endDate   =  $this->currentDate;
                 break;
        }
    }
 
    
    /**
     * This function used for get employee schedule hour for week wise
     * @param type $dayRangeType
     * @param type $account_no
     */
    function GetEmployeeScheduleHoursForWeekWise($dayRangeType="",$account_no="")
        {
            if($account_no!=''){
            $account_no = salonWebappCloudDe($account_no);
            }
            $this->currentDate = getDateFn();
            $getConfigDetails  = $this->Common_model->getMillSdkConfigDetails($account_no);
            if($getConfigDetails->num_rows()>0)
            {
                foreach($getConfigDetails->result_array() as $configDetails)
                {
                    pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
                    $this->salonAccountId = $configDetails['salon_account_id'];
                    $this->pemFilePath   =  base_url()."salonevolve.pem";
                    $this->salonMillIp   =  $configDetails['mill_ip_address'];
                    $this->salonMillGuid =  $configDetails['mill_guid'];
                    $this->salonMillUsername =  $configDetails['mill_username'];
                    $this->salonMillPassword =  $configDetails['mill_password'];
                    $this->salonMillSdkUrl = $configDetails['mill_url'];
                    $this->salonId = $configDetails['salon_id'];
                    //Database Log
                    $log['AccountNo'] = $configDetails['salon_account_id'];
                    $log['salon_id'] = $configDetails['salon_id'];
                    $log['StartingTime'] = date('Y-m-d H:i:s');
                    $log['whichCron'] = 'GetEmployeeScheduleHoursForWeekWise';
                    $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_weekwise_data/GetEmployeeScheduleHoursForWeekWise/'.$dayRangeType.'/'.$account_no;
                    $log['CronType'] = 0;
                    $log['id'] = 0;
                    $log_id = $this->Common_model->saveMillCronLogs($log);
                    
                    $this->salonInfo =  $this->Common_model->getCurlData(GET_SALON_INFO_FR_SALONID_BY_SALONCLOUDSPLUS,
                        array('salon_id' => $this->salonId) );
                    $salon = $this->salonInfo["salon_info"];

                      // GET START DATE AND END DATE AS PER PARAMETERS
                    $this->__getStartEndDate($dayRangeType);
                     
                    $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                                     
                    $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                     
                    $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                    pa($this->millResponseSessionId,'SESSION');
                   
                            //$i=1;
                    $startDayOfTheWeek = $salon["salon_start_day_of_week"];
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek)){
                        $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else{
                        $fourWeeksArr = getLast4WeekRanges(date('Y'));
                    }

                   // pa($fourWeeksArr,'fourWeeksArr',false);
                       
                        foreach ($fourWeeksArr as $key => $date) {
                            $firstDay = $date['start_date'];
                            $lastDay = $date['end_date'];
                            if ($this->millResponseSessionId){
                            $whereconditions = array('account_no' => $this->salonAccountId);
                            $EmployeeListArr = $this->Common_model->getEmployeeListing($whereconditions)->result_array();
                           // pa('','EmployeeListing','');
                            if(!empty($EmployeeListArr)) 
                            { 
                                foreach($EmployeeListArr as $employeeList)
                                {
                                     $employeeId = $employeeList["iid"];
                                     $millMethodParams['XmlIds'] = '<NewDataSet><Ids><Id>'.$employeeId.'</Id></Ids></NewDataSet>';
                                     $millMethodParams['StartDate'] = $firstDay;
                                     $millMethodParams['EndDate'] = $lastDay;
                                    // pa($millMethodParams,'millMethodParams');
                                      
                                     $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetEmployeeScheduleHours',$millMethodParams);
                                
                                   //  pa($this->millResponseXml,'GetEmployeeScheduleHours','');
                                     if ($this->millResponseXml){
                                         if(isset($this->millResponseXml['EmployeeScheduleHours']))
                                                {
                                                 // pa($this->millResponseXml['EmployeeScheduleHours'],'employee hours',true);
                                                  if(count($this->millResponseXml['EmployeeScheduleHours']) == count($this->millResponseXml['EmployeeScheduleHours'], COUNT_RECURSIVE)){
                                                    $arrEmployeeScheduleHours[] = $this->millResponseXml['EmployeeScheduleHours'];
                                                    }else {
                                                        $arrEmployeeScheduleHours = $this->millResponseXml['EmployeeScheduleHours'];
                                                    }
                                                  foreach($arrEmployeeScheduleHours as $emlist)
                                                    {
                                                        array_walk($emlist, function (&$value,&$key) { 
                                                            $value = trim($value);
                                                            if($key == 'nhours')
                                                                $value = number_format($value, 4, '.', '');
                                                            });
                                                      //  pa($emlist,'WORKING HOURS '.$emlist['nhours'].' '.$emlist['cworktype'].' FOR IEMPID '.$emlist['iempid'],'');

                                                        $whereCondition =  array('iempid' => $emlist['iempid'],'iworktypeid' => $emlist['iworktypeid'],'account_no' => $this->salonAccountId,'dayRangeType' => $this->dayRangeType,'start_date' => $firstDay,'end_date' => $lastDay);

                                                        $arrEmployeelist = $this->Employeeimportweekwise_model
                                                                        ->compareMillEmployeesScheduleHoursWeekWise($whereCondition)
                                                                        ->row_array();
                                                        if(!empty($arrEmployeelist))
                                                        {
                                                            $diff_array = array_diff_assoc($emlist, $arrEmployeelist );
                                                            if(empty($diff_array))
                                                            {
                                                                continue; //SAME DATA FOUND, SO CONTINUe with the loop
                                                            } else{
                                                                $diff_array['updatedDate'] =  date("Y-m-d H:i:s");
                                                                $diff_array['insert_status'] = Wsimport_all_employee_weekwise_data::UPDATED;
                                                             //   pa($diff_array,"update");
                                                                 
                                                                $whereconditions = array();
                                                                $whereconditions['iempid'] = $emlist['iempid'];
                                                                $whereconditions['iworktypeid'] = $emlist['iworktypeid'];
                                                                $whereconditions['dayRangeType'] = $this->dayRangeType;
                                                                $whereconditions['start_date'] = $firstDay;
                                                                $whereconditions['end_date'] = $lastDay;
                                                                $whereconditions['account_no'] = $this->salonAccountId;
                                                                $res = $this->Employeeimportweekwise_model->updateEmployeesScheduleHoursWeekWise($whereconditions,$diff_array);
                                                            }

                                                        }else{
                                                            // insert
                                                        //   pa($emlist,'INsert');
                                                           $emlist['account_no'] =  $this->salonAccountId;
                                                           $emlist['dayRangeType'] =  $this->dayRangeType;
                                                           $emlist['start_date'] =  $firstDay;
                                                           $emlist['end_date'] =  $lastDay;
                                                           $emlist['insertedDate'] =  date("Y-m-d H:i:s");
                                                           $emlist['updatedDate'] =  date("Y-m-d H:i:s");
                                                           $emlist['insert_status'] = Wsimport_all_employee_weekwise_data::INSERTED;
                                                           
                                                            $res = $this->Employeeimportweekwise_model->insertMillEmployeesScheduleHoursWeekWise($emlist);
                                                            $emlist_id = $this->db->insert_id();
                                                        }
                                                    }
                                                }
                                     }  
                                }
                            }
                            else{
                                echo "No Employee Schedule Hours found in Millennium.";
                            }
                            
                          }  
                        }
                    // Database Log
                    $log['id'] = $log_id;
                    $log_id = $this->Common_model->saveMillCronLogs($log);    
                 }
            }else{
              pa('Salons are inactive or invalid salon');
            }
        } 
  
 }       