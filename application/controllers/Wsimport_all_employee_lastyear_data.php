<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class Wsimport_all_employee_lastyear_data extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR IMPORT Employees Last Year
    **/
     // Define constant as per value;
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
    public $transactionIds;
    public $currentDate;
    public $nusoap_library;    
    private $salonId;
    private $salonInfo;
    private $dayRangeType;
    public $millResponseSessionId; 
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Employeeimportlastyear_model');
    }
    
    /**
     * Default index Fn
     */
    public function index(){print "Test";}

    /**
     * 
     * @param type $dayRangeType
     * @param type $year
     */
    function __getStartEndDate($dayRangeType, $numOfYears)
    {
        $this->dayRangeType =  $dayRangeType;
        switch ($this->dayRangeType) {
            case "today":
                $this->startDate = getDateFn(strtotime("-$numOfYears year"));
                $this->endDate   =  getDateFn(strtotime("-$numOfYears year"));
            break;
            case "lastweek":
                if(isset( $this->salonInfo['salon_info']["salon_start_day_of_week"]) && !empty($this->salonInfo['salon_info']["salon_start_day_of_week"]))
                    {
                        $lastDayOfTheWeek =  $this->salonInfo['salon_info']["salon_start_day_of_week"];
                        $end_day_of_this_week = strtotime($startDate.' +6 days');
                        $weekStartDate = date("Y-m-d",strtotime('last '.$lastDayOfTheWeek));
                        $weekEndDate = date('Y-m-d', $end_day_of_this_week);
                        $this->startDate = getDateFn(strtotime($weekStartDate."-$numOfYears year"));
                        $this->endDate = getDateFn(strtotime($weekEndDate."-$numOfYears year"));
                    }
                    else
                    {
                        $weekStartDate = date('Y-m-d', strtotime('-7 days'));
                        $weekEndDate = date('Y-m-d', strtotime('-1 days'));
                        $this->startDate = getDateFn(strtotime($weekStartDate."-$numOfYears year"));
                        $this->endDate = getDateFn(strtotime($weekEndDate."-$numOfYears year"));
                    }   
            break;
            case "lastmonth":
                $LastMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
                $LastMonthendDate = date("Y-m-d", strtotime("last day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthstartDate."-$numOfYears year"));
                $this->endDate = getDateFn(strtotime($LastMonthendDate."-$numOfYears year"));
                break;
            case "monthly":
                $MonthStartDate = date("Y-m-")."01";
                $MonthEndDate = $this->currentDate;
                $this->startDate = getDateFn(strtotime($MonthStartDate."-$numOfYears year"));
                $this->endDate = getDateFn(strtotime($MonthEndDate."-$numOfYears year"));
                break;
            case "last90days":
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $Last90startDate = date("Y-m-d", strtotime($LastMonthFirst. " -2 months"));
                $Last90endDate = date("Y-m-d", strtotime("last day of last month"));
                $this->startDate = getDateFn(strtotime($Last90startDate."-$numOfYears year"));
                $this->endDate = getDateFn(strtotime($Last90endDate."-$numOfYears year"));
                break;
            case "yearly":
            //case yearly:
                $YearlystartDate = date("Y-")."01-01";
                $YearlyendDate = date("Y-m-d");
                $this->startDate = getDateFn(strtotime($YearlystartDate."-$numOfYears year"));
                $this->endDate = getDateFn(strtotime($YearlyendDate."-$numOfYears year"));
                break;
            default:
                $this->startDate = getDateFn(strtotime("-$numOfYears year"));
                $this->endDate   =  getDateFn(strtotime("-$numOfYears year"));
            break;
        }
    }

    /**
    * @author MAK<anas-php@webappclouds.com>
    * @description : This function for get employee schedule hours last year data 
    * @param type $dayRangeType
    * @param type $account_no
    */ 
    function GetEmployeeScheduleHoursForLastYear($dayRangeType= '',$account_no="",$numOfYears=1)
    {
        if($account_no!=''){
            $account_no = salonWebappCloudDe($account_no);
        }
       // GET SALON CONFIG DETAILS
        $this->currentDate = getDateFn();
        $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no);
        if($getConfigDetails->num_rows() > 0) {
            foreach ($getConfigDetails->result_array() as $configDetails) {
                pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
                
                //LOG IN DETAILS FOR MILLENIUM SDK  
                $this->salonAccountId = $configDetails['salon_account_id'];
                $this->pemFilePath   =  base_url()."salonevolve.pem";
                $this->salonMillIp   =  $configDetails['mill_ip_address'];
                $this->salonMillGuid =  $configDetails['mill_guid'];
                $this->salonMillUsername =  $configDetails['mill_username'];
                $this->salonMillPassword =  $configDetails['mill_password'];
                $this->salonMillSdkUrl = $configDetails['mill_url'];
                $this->salonId = $configDetails['salon_id'];
                // Database Log
                $log['AccountNo'] = $configDetails['salon_account_id'];
                $log['salon_id'] = $configDetails['salon_id'];
                $log['StartingTime'] = date('Y-m-d H:i:s');
                $log['whichCron'] = 'GetEmployeeScheduleHoursForLastYear';
                $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/'.$dayRangeType.'/'.$account_no;
                $log['CronType'] = 0;
                $log['id'] = 0;
                $log_id = $this->Common_model->saveMillCronLogs($log);                
                
                $this->salonInfo = $this->Common_model->getCurlData(GET_SALON_INFO_FR_SALONID_BY_SALONCLOUDSPLUS,array('salon_id' => $this->salonId));

                // GET START DATE AND END DATE AS PER PARAMETERS
                $this->__getStartEndDate($dayRangeType,$numOfYears);

               //MILLENIUM SDK REQUEST FOR SOAP CALL    
                $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                pa($this->millResponseSessionId,'Session');
                if ($this->millResponseSessionId){
                            $whereconditions = array('account_no' => $this->salonAccountId);
                            $EmployeeListArr = $this->Common_model->getEmployeeListing($whereconditions)->result_array();
                            
                            pa($EmployeeListArr,'EmployeeListing','');
                            
                            if(!empty($EmployeeListArr)) 
                            {
                                $XmlIds = '';  
                                foreach ($EmployeeListArr as $service){
                                 $XmlIds .='<Ids><Id>'.$service['iid'].'</Id></Ids>';
                                }
                                $millMethodParams['XmlIds'] = '<NewDataSet>'.$XmlIds.'</NewDataSet>';
                                $millMethodParams['StartDate'] = $this->startDate;
                                $millMethodParams['EndDate'] = $this->endDate;
                                pa($millMethodParams,'millMethodParams');
                                $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetEmployeeScheduleHours',$millMethodParams);
                                pa($this->millResponseXml,'GetEmployeeScheduleHours',false);
                                if ($this->millResponseXml){
                                       
                                        if(isset($this->millResponseXml['EmployeeScheduleHours']))
                                        {
                                            $arrEmployeeScheduleHours = $this->millResponseXml['EmployeeScheduleHours'];
                                            pa($arrEmployeeScheduleHours,'arrEmployeeScheduleHours',false);

                                            foreach($arrEmployeeScheduleHours as $emlist)
                                            {
                                                
                                                array_walk($emlist, function (&$value,&$key) { 
                                                    $value = trim($value);
                                                    if($key == 'nhours')
                                                        $value = number_format($value, 4, '.', '');
                                                    });
                                
                                                //pa($emlist,'WORKING HOURS '.$emlist['nhours'].' '.$emlist['cworktype'].' FOR IEMPID '.$emlist['iempid'],'');

                                                // GETS DB COMPARING 
                                                $whereCondition =  array('iempid' => $emlist['iempid'],'iworktypeid' => $emlist['iworktypeid'],'account_no' => $this->salonAccountId,'dayRangeType' => $this->dayRangeType,'start_date' => $this->startDate,'end_date' => $this->endDate);
                                                
                                                $arrEmployeelist = $this->Employeeimportlastyear_model
                                                                ->compareMillEmployeesScheduleHoursLastYear($whereCondition)
                                                                ->row_array();
                                               
                        
                                                if(!empty($arrEmployeelist))
                                                {
                                                   
                                                    $diff_array = array_diff_assoc($emlist, $arrEmployeelist );
                                                    pa($diff_array,"Diff Array");
                                                    
                                                    if(empty($diff_array))
                                                    {
                                                        continue; //SAME DATA FOUND, SO CONTINUe with the loop
                                                    }    
                                                    else
                                                    {
                                                         //UPDATE DATA IN DB 
                                                        $diff_array['updatedDate'] =  date("Y-m-d H:i:s");
                                                        $diff_array['insert_status'] = self::UPDATED;
                                                        //pa($diff_array,"update");
                                                         
                                                        $whereconditions = array();
                                                        $whereconditions['iempid'] = $emlist['iempid'];
                                                        $whereconditions['iworktypeid'] = $emlist['iworktypeid'];
                                                        $whereconditions['dayRangeType'] = $this->dayRangeType;
                                                        $whereconditions['start_date'] = $this->startDate;
                                                        $whereconditions['end_date'] = $this->endDate;
                                                        $whereconditions['account_no'] = $this->salonAccountId;
                                                        $res = $this->Employeeimportlastyear_model->updateEmployeesScheduleHoursLastYear($whereconditions,$diff_array);
                                                    }
                                                }
                                                else // INSERT APPOINTMENT DATA IN DB 
                                                {
                                                   //pa($emlist,'INsert');
                                                   $emlist['account_no'] =  $this->salonAccountId;
                                                   $emlist['dayRangeType'] =  $this->dayRangeType;
                                                   $emlist['start_date'] =  $this->startDate;
                                                   $emlist['end_date'] =  $this->endDate;
                                                   $emlist['insertedDate'] =  date("Y-m-d H:i:s");
                                                   $emlist['updatedDate'] =  date("Y-m-d H:i:s");
                                                   $emlist['insert_status'] = self::INSERTED;
                                                   
                                                    $res = $this->Employeeimportlastyear_model->insertMillEmployeesScheduleHoursLastYear($emlist);
                                                    $emlist_id = $this->db->insert_id();
                                                }
                                            } //foreach ends of Package
                                        }
                                        else
                                        {
                                            echo "No Employee Schedule Hours found in Millennium.";
                                        }
                                }else{
                                    pa('Mill Response Not exist');
                                }
                            }
                            else
                            {
                                //PACKAGE SERRIES LIST IS EMPTY
                                echo "No Employee Schedule Hours found in Millennium.";
                            }
                    // Database Log    
                    $log['id'] = $log_id;
                    $log_id = $this->Common_model->saveMillCronLogs($log);      
                    $logOff = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('LogOff',$millloginDetails);  
                    }
                }
            }
        }   
  
  
} 