<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}

class Wsimport_all_employee_weekwiselastyear_data extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR WEEKWISE LAST YEAR DATA
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
        $this->load->model('Employeeimportweekwiselastyear_model');
        //$this->load->library('nusoap_library');
    }
    
    /**
     * Default Index Fn
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
            case "IndividualWeek":
                $yearstartDate = date("Y-m-")."01"; 
                $this->startDate = date('Y-m-d',strtotime($yearstartDate." -$numOfYears year"));
                $this->endDate = date('Y-m-d', strtotime($this->currentDate." -$numOfYears year"));
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
     * @param type $year
     * @param type $account_no
     */
    function GetEmployeeScheduleHoursForWeekWiseLastYear($dayRangeType="",$account_no="",$numOfYears=1)
        {
            if($account_no!=''){
            $account_no = salonWebappCloudDe($account_no);
            }
            $this->currentDate = getDateFn();
            $getConfigDetails  = $this->Common_model->getMillSdkConfigDetails($account_no);
           // pa($getConfigDetails->num_rows(),'getConfigDetails',false);
           
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
                    $log['whichCron'] = 'GetEmployeeScheduleHoursForMonthWiseLastYear';
                    //$log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_weekwiselastyear_data/GetEmployeeScheduleHoursForMonthWiseLastYear/'.$dayRangeType.'/'.$account_no;
                    $log['CronType'] = 0;
                    $log['id'] = 0;
                    $log_id = $this->Common_model->saveMillCronLogs($log);
                    
                    $this->salonInfo = $this->Common_model->getCurlData(GET_SALON_INFO_FR_SALONID_BY_SALONCLOUDSPLUS,
                        array('salon_id' => $this->salonId) );

                      // GET START DATE AND END DATE AS PER PARAMETERS
                    $this->__getStartEndDate($dayRangeType,$numOfYears);
                     //MILLENIUM SDK REQUEST FOR SOAP CALL     
                     
                    $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                    $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                    $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                    pa($this->millResponseSessionId,'session');

                    $salon = $this->salonInfo["salon_info"];
                    $startDayOfTheWeek = $salon["salon_start_day_of_week"];
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {   
                        $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,date("Y",strtotime("-1 year")));
                    }
                    else
                    {
                       $fourWeeksArr = getLast4WeekRanges(date("Y",strtotime("-1 year")));
                    }

                    pa($fourWeeksArr,'fourWeeksArr',false);
                    $firstDay = '';
                    $lastDay = '';
                    foreach ($fourWeeksArr as $key => $date) {
                                $firstDay = $date['start_date'];
                                $lastDay = $date['end_date'];
                                
                                if ($this->millResponseSessionId){
                                $whereconditions = array('account_no' => $this->salonAccountId);
                                $EmployeeListArr = $this->Common_model->getEmployeeListing($whereconditions)->result_array();
                                pa('','EmployeeListing','');
                                 if(!empty($EmployeeListArr)) 
                            {
                                $XmlIds = '';  
                                foreach ($EmployeeListArr as $service){
                                 $XmlIds .='<Ids><Id>'.$service['iid'].'</Id></Ids>';
                                }
                                $millMethodParams['XmlIds'] = '<NewDataSet>'.$XmlIds.'</NewDataSet>';
                                $millMethodParams['StartDate'] = $firstDay;
                                $millMethodParams['EndDate'] = $lastDay;
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
                                                 $whereCondition =  array('iempid' => $emlist['iempid'],'iworktypeid' => $emlist['iworktypeid'],'account_no' => $this->salonAccountId,'dayRangeType' => $this->dayRangeType,'start_date' => $firstDay,'end_date' => $lastDay);
                                                
                                                $arrEmployeelist = $this->Employeeimportweekwiselastyear_model
                                                                ->compareMillEmployeesScheduleHoursWeekWiseLastYear($whereCondition)
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
                                                        $whereconditions['start_date'] = $firstDay;
                                                        $whereconditions['end_date'] = $lastDay;
                                                        $whereconditions['account_no'] = $this->salonAccountId;
                                                        $res = $this->Employeeimportweekwiselastyear_model->updateEmployeesScheduleHoursWeekWiseLastYear($whereconditions,$diff_array);
                                                    }
                                                }
                                                else // INSERT APPOINTMENT DATA IN DB 
                                                {
                                                   //pa($emlist,'INsert');
                                                   $emlist['account_no'] =  $this->salonAccountId;
                                                   $emlist['dayRangeType'] =  $this->dayRangeType;
                                                   $emlist['start_date'] =  $firstDay;
                                                   $emlist['end_date'] =  $lastDay;
                                                   $emlist['insertedDate'] =  date("Y-m-d H:i:s");
                                                   $emlist['updatedDate'] =  date("Y-m-d H:i:s");
                                                   $emlist['insert_status'] = self::INSERTED;
                                                   
                                                    $res = $this->Employeeimportweekwiselastyear_model->insertMillEmployeesScheduleHoursWeekWiseLastYear($emlist);
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
                                
                              }
                             // Database Log
                        $log['id'] = $log_id;
                        $log_id = $this->Common_model->saveMillCronLogs($log); 
                        $logOff = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('LogOff',$millloginDetails);   
                    }    
                   }
                 
            }else{
                pa('Config Details Error');
            }
        } 
  
 }       