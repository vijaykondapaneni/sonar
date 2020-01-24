<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class Wsimport_all_employee_currentyear_data extends CI_Controller
{
    
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
    public $millResponseSessionId;
    
    public $transactionIds;
    public $currentDate;
    public $nusoap_library;
    
    private $salonId;
    private $salonInfo;
    private $dayRangeType; 
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR IMPORT Employee schedule hours
    **/
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Employeeimportcurrentyear_model');
    }   
    
    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    /**
     * 
     * @param type $dayRangeType
     * @param type $s
     * @param type $e
     */
    function __getStartEndDate($dayRangeType, $s = '', $e = '')
    {
        $this->dayRangeType =  $dayRangeType;
        
        switch ($this->dayRangeType) {
            case TODAY:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
            break;
            case LASTWEEK:
                     if(isset( $this->salonInfo['salon_info']["salon_start_day_of_week"]) && !empty($this->salonInfo['salon_info']["salon_start_day_of_week"]))
                        {
                            $lastDayOfTheWeek =  $this->salonInfo['salon_info']["salon_start_day_of_week"];
                            $this->startDate = getDateFn(strtotime('last '.$lastDayOfTheWeek));
                            $this->endDate = getDateFn(strtotime($this->startDate.' +6 days'));
                        }
                        else
                        {
                            $this->startDate = getDateFn(strtotime('-7 days'));
                            $this->endDate = getDateFn(strtotime('-1 days'));
                        }   
            break;
            case LASTMONTH:
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                break;
            case MONTHLY:
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $this->currentDate;
                break;
            case LAST90DAYS:
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                break;
            case "yearly":
            //case YEARLY:
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                $this->endDate = $this->currentDate;
                break;
            case CUSTOMDATE:
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
    * @author MAK<anas-php@webappclouds.com>
    * @description : This function for get employee schedule hours current year data 
    * @param type $dayRangeType
    * @param type $account_no
    */ 
    function GetEmployeeScheduleHoursForCurrentYear($dayRangeType= '' ,$account_no="")
        {
            if($account_no!=''){
             $account_no = salonWebappCloudDe($account_no);
            }
            // GET SALON CONFIG DETAILS
            $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no);
            $this->currentDate = getDateFn();
           // pa($getConfigDetails->result_array(),'',true);
            
            if($getConfigDetails->num_rows()>0)
            {
                foreach($getConfigDetails->result_array() as $configDetails)
                {
                    pa($configDetails['salon_name'],"Salon Name");
                    
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
                    $log['whichCron'] = 'GetEmployeeScheduleHoursForCurrentYear';
                    //$log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/'.$dayRangeType.'/'.$this->salonAccountId;
                    $log['CronType'] = 0;
                    $log['id'] = 0;
                    $log_id = $this->Common_model->saveMillCronLogs($log);
                    $this->salonInfo = $this->Common_model->getCurlData(GET_SALON_INFO_FR_SALONID_BY_SALONCLOUDSPLUS,
                        array('salon_id' => $this->salonId) );
          
                    // GET START DATE AND END DATE AS PER PARAMETERS
                    $this->__getStartEndDate($dayRangeType);
                    
                    //MILLENIUM SDK REQUEST FOR SOAP CALL   
                    $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                    $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                    $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);

                    //pa($this->millResponseSessionId,'session');

                    if ($this->millResponseSessionId){
                            $whereconditions = array('account_no' => $this->salonAccountId);
                            $EmployeeListArr = $this->Common_model
                                               ->getEmployeeListing($whereconditions)
                                               ->result_array();
                            pa($EmployeeListArr,'EmployeeListArr',false);
                           
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
                                                
                                                $arrEmployeelist = $this->Employeeimportcurrentyear_model
                                                                ->compareMillEmployeesScheduleHoursCurrentYear($whereCondition)
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
                                                        $res = $this->Employeeimportcurrentyear_model->updateEmployeesScheduleHoursCurrentYear($whereconditions,$diff_array);
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
                                                   
                                                    $res = $this->Employeeimportcurrentyear_model->insertMillEmployeesScheduleHoursCurrentYear($emlist);
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
            }else{
                pa('Salons are inactive or invalid salon');
            }
        }    
 
 }       