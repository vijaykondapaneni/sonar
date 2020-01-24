<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class PercentageBookedForDashboard extends CI_Controller
{
    public  $salon_id;
    public  $startDate;
    public  $endDate;
    public  $currentDate;
    private $salonId;
    private $salonDetails;
    private $dayRangeType;
    
    CONST SERVICE_URL_TODAY = 'PercentageBookedForDashboard/getPercentageBookedForDashboard/today';
    CONST SERVICE_URL_LASTWEEK = 'PercentageBookedForDashboard/getPercentageBookedForDashboard/lastweek';
    CONST SERVICE_URL_LASTMONTH = 'PercentageBookedForDashboard/getPercentageBookedForDashboard/lastmonth';
    CONST SERVICE_URL_LAST90DAYS = 'PercentageBookedForDashboard/getPercentageBookedForDashboard/last90days';
        
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('DashboardOwner_model');
        $this->load->model('ColorPercentage_model');
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
    }
    function __getStartEndDate($dayRangeType,$year=1)
    {
        $this->dayRangeType =  $dayRangeType;
        $currentDate = date('Y-m-d');

         switch ($this->dayRangeType) {
            case "today":
                $this->startDate = $currentDate;
                $this->endDate = $currentDate;
                $this->lastYearStartDate =  getDateFn(strtotime("-$year year"));
                $this->lastYearEndDate = getDateFn(strtotime("-$year year"));
                $this->Range = 'Today';
                $this->Service_url = self::SERVICE_URL_TODAY;
            break;                          
            case "lastweek":

               // pa($this->salonDetails['salon_info']["salon_start_day_of_week"],'subbu');

                if(isset($this->salonDetails['salon_info']["salon_start_day_of_week"]) && !empty($this->salonDetails['salon_info']["salon_start_day_of_week"]))
                {
                    $lastDayOfTheWeek =  $this->salonDetails['salon_info']["salon_start_day_of_week"];
                    $this->startDate = getDateFn(strtotime('last '.$lastDayOfTheWeek));
                    $this->endDate = getDateFn(strtotime($this->startDate.' +6 days'));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                }
                else
                {
                    $this->startDate = getDateFn(strtotime('-7 days'));
                    $this->endDate = getDateFn(strtotime('-1 days'));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                }
                $this->Range = 'Last Week';
                $this->Service_url = self::SERVICE_URL_LASTWEEK;

            break;
            case "lastmonth":
                    $this->startDate = getDateFn(strtotime("first day of last month"));
                    $this->endDate = getDateFn(strtotime("last day of last month"));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                    $this->Range = 'Last Month';
                    $this->Service_url = self::SERVICE_URL_LASTMONTH;
            break;
            case "last90days":                                   
                    $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                    $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                    $this->endDate = getDateFn(strtotime("last day of last month"));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                    $this->Range = 'Last Three Months';
                    $this->Service_url = self::SERVICE_URL_LAST90DAYS;
            break;          
            default:
                    $this->startDate =  $currentDate;
                    $this->endDate   =  $currentDate;
                    $this->lastYearStartDate =  getDateFn(strtotime("-$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime("-$year year"));
                    $this->Range = 'Today';
                    $this->Service_url = self::SERVICE_URL_TODAY;
           break;
        }
    }
    function getPercentageBookedForDashboard($dayRangeType="today",$salon_id="")
        { 

        	$result_arr = array();
            $this->currentDate = getDateFn();
            $this->salonsInfo = $this->Common_model->getAllSalons($salon_id);
            if(isset($this->salonsInfo["mill_salons"]) && !empty($this->salonsInfo["mill_salons"]))
            {
            	foreach($this->salonsInfo["mill_salons"] as $key=>$salonsData)
                {
                    $this->salon_id = $salonsData['salon_id'];
                    $this->salonDetails = $this->Common_model->getSalonInfoBy($salonsData['salon_id']); 
                    //pa($this->salonDetails,'salonInfo',false);
                	$data[$key]['salon_id']=$salonsData['salon_id'];
                	$data[$key]['salon_name']=$salonsData['salon_name'];
                	$data[$key]['salon_account_id']=$salonsData['salon_account_id'];
                    $this->__getStartEndDate($dayRangeType);
                    $currentdate = date('Y-m-d');
	                $st_date = $currentdate.' 00:00:00';    
	                $end_date = $currentdate.' 23:59:59';
	                $current_hour = date('H');
	                $st_hour = $current_hour - 2;    
	                $end_hour = $current_hour + 1;  
                    $result = $this->DB_ReadOnly->query("SELECT * FROM mill_all_salons_sdk_reports_server
                       WHERE
                       salon_id='$this->salon_id' and
                       created_date BETWEEN '".$st_date."' AND '".$end_date."'
                        AND HOUR(created_date) BETWEEN ".$st_hour." AND ".$end_hour." and (session_status=0 or appointment_status =0) GROUP by salon_id ORDER by date(created_date) desc ")->row_array();
                   // pa($this->DB_ReadOnly->last_query(),'last_query'); 
                    if($result == ""){
                        $data[$key]['status']= "Not Working";
                    }else{
                         $data[$key]['status']="Sdk Working";
                    }
                    $data[$key]['dayRangeType'] = $dayRangeType;
                    $res=$this->DB_ReadOnly->query("SELECT percent_booked,  highest_percent_booked_value,highest_percent_booked_employee  FROM mill_owner_report_calculations_cron where salon_id='$this->salon_id'AND start_date='$this->startDate' AND end_date='$this->endDate' AND dayRangeType='$dayRangeType'")->row_array();
                    //pa($this->DB_ReadOnly->last_query(),'last_query');   
                     
                    if( $res!="" ){
                            $data[$key]['booked']=$res['percent_booked'];
                            $data[$key]['highest_booked']=$res['highest_percent_booked_value'];
                            $data[$key]['employeename']=$res['highest_percent_booked_employee'];
                    }else{
                        	$data[$key]['booked']="0";
                        	$data[$key]['highest_booked']="0";
                        	$data[$key]['employeename']="NULL";
                    }

                }
                // echo "<pre>";
                //print_r($data);
               // pa($data,'data',true);
                $result_arr['salon_info'] = $data;
                $result_arr['dayRangeTypedisplay'] = $dayRangeType;
                $this->load->view('Bookedview',$result_arr);
            }

        }

    function fixpercentage_booked($salon_id,$dayRangeType){
            if($salon_id!=''){
                $salon_details = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                $salon_code = $salon_details['salon_account_id'];
                if($salon_code!=''){
                    $account_no = salonWebappCloudEn($salon_code);
                }
            }
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/'.$dayRangeType;
            $i=1;
            //pa($account_no,'',true);
            foreach ($loop as $key => $value) {
                if($salon_code!=''){
                 $url = $mainurl.$value.'/'.$account_no;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
            $loop1 = array();
            $loop1[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/'.$dayRangeType;
            $i=1;
            //pa($account_no,'',true);
            foreach ($loop1 as $key => $value) {
                if($salon_code!=''){
                 $url = $mainurl.$value.'/'.$salon_id;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }



    }    
}
  