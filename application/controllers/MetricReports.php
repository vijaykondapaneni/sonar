<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class MetricReports extends REST_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Owner Dashboard WebServices
    **/
    public  $salon_id;
    public  $startDate;
    public  $endDate;
    public  $currentDate;
    private $salonId;
    private $salonDetails;
    private $dayRangeType;
    private $lastOneMonthstartDate;
    private $lastTwoMonthsstartDate;   
    private $lastThreeMonthsstartDate; 

    CONST SERVICE_URL_TODAY = 'MetricReports/getMetricReports/Today';
    CONST SERVICE_URL_LASTWEEK = 'MetricReports/getMetricReports/Lastweek';
    CONST SERVICE_URL_LASTMONTH = 'MetricReports/getMetricReports/Lastmonth';
    CONST SERVICE_URL_LAST90DAYS = 'MetricReports/getMetricReports/Last90days';
    

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('OwnerWebServices_model');
        $this->load->model('saloncloudsplus_model');
        $this->load->library('webserviceaccess');
    }
    // Get Date Range Types
    function __getStartEndDate($dayRangeType,$salon_id)
    {
        $this->dayRangeType =  $dayRangeType;
        $year = 1;
        $this->currentDate = $currentDate = date('Y-m-d');
         switch ($this->dayRangeType) {
            case "Today":
                $this->startDate = $this->currentDate;
                $this->endDate = $this->currentDate;
                $this->lastYearStartDate =  getDateFn(strtotime("-$year year"));
                $this->lastYearEndDate = getDateFn(strtotime("-$year year"));
                $this->Range = 'today';
            break;
            case "Weekly":
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
                    $this->Range = 'lastweek';
            break;
            case LASTMONTH:
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                $this->Range = 'lastmonth';
                    
            break;
            case "Monthly":
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $this->currentDate;
                $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                $this->Range = 'Monthly';
            break;
            case LAST90DAYS:
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                $this->Range = 'last90days';
                   
            break;
            case "Yearly":
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                $this->endDate = $currentDate;
                $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                $this->Range = 'yearly';
            break;         
            default:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
                $this->lastYearStartDate =  getDateFn(strtotime("-$year year"));
                $this->lastYearEndDate = getDateFn(strtotime("-$year year"));
           break;
        }
    }
    /**
    * Auth Function
    */
    private function __getAccessWesbService($service,$salon_id){
               $this->WebAccessResponse = $this->webserviceaccess->validateWebAppWs($service,$salon_id); 
               return $this->WebAccessResponse;
    }

    /**
     * Default index Fn
     */
    public function index(){
        $this->__getAccessWesbService();
        print "Test";
    }
   
    public function getMetricReports($dayRangeType="Today"){
        if(isset($_POST['salon_id'])){
            $salon_id = $_POST['salon_id'];
        }else{
               $salon_id = '';
        }
        //$this->__getStartEndDate($dayRangeType);

        /*$this->__getAccessWesbService($this->Service_url,$salon_id);
        if($this->WebAccessResponse['HTTPCODE'] != 200){
               $response_array = array('status' => false, 'message' => $this->WebAccessResponse['MESSAGE'], 'status_code' => 401);
                $response_code = $this->WebAccessResponse['HTTPCODE'];
                goto response;    
        }*/

        $type = urldecode($_POST['type']);
        $salon_id = $_POST['salon_id'];
        if(!isset($_POST['salon_id']) && !empty($_POST['salon_id'])){
            $response_array = array('status' => false, 'error' => 'Salon Id Required', 'error_code' => 402);
            $response_code = 200; 
            goto response;
        }
        if(!isset($_POST['type']) && !empty($_POST['type'])){
            $response_array = array('status' => false, 'error' => 'Type Required', 'error_code' => 402);
            $response_code = 200; 
            goto response;
        }
        if(!isset($_POST['metric']) && !empty($_POST['metric'])){
            $response_array = array('status' => false, 'error' => 'Metric Required', 'error_code' => 402);
            $response_code = 200; 
            goto response;
        }

        
        $relatedSalons = $this->saloncloudsplus_model->relatedSalons($salon_id);
        $type = $_POST['type'];
        $metric = $_POST['metric'];

        if(!empty($relatedSalons)){
             foreach ($relatedSalons as $eachSalon) {
                $salon_id = $eachSalon['salon_id'];
                $salon_name = $eachSalon['salon_name'];
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $dataArray['title'] = $salon_name;
                $dataArray['salon_id'] = $salon_id;
                $this->salonDetails = $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $this->__getData($type,$metric,$salon_id);
                //pa($this->res,'res',true);
                if(!empty($this->res))
                    {
                        $tempArr = array();
                        $dataArray["value"] = $this->res["value"];
                        $dataArray["last_year_value"] = $this->res["value_ly"];
                    }
                    else
                    {
                        $dataArray["graph_data"] = array();
                        $dataArray["status"] = false;
                    }
                    
                    $response_array['data'][] = $dataArray;
                 }   
             }
        
           $response_array['start_date'] = $this->startDate; 
           $response_array['end_date'] = $this->endDate;
           $response_array["status"] = true;
           $response_code = 200;
           response:
           $this->response($response_array, $response_code);
    }


    function __getData($type,$metric,$salon_id)
        {  
            //pa($metric);
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                $this->__getStartEndDate($type,$salon_id);
                $dbtype = $this->Range;
                $whereCondition = array('salon_id' => $salon_id,'report_type' => 'currentYearReport','dayRangeType' => $dbtype,'start_date' => $this->startDate,'end_date' => $this->endDate);
                $this->db->select($metric);
                $res1 = $this->db->get_where(MILL_OWNER_REPORT_CALCULATIONS_CRON,$whereCondition)->row_array();
                /*pa($this->db->last_query());
                exit;*/
                $value = !empty($res1[$metric]) ? $res1[$metric] : '0.00';
                // Last Year Data
                $whereCondition = array('salon_id' => $salon_id,'report_type' => 'lastYearReport','dayRangeType' => $dbtype,'start_date' => $this->lastYearStartDate,'end_date' => $this->lastYearEndDate);
                $this->db->select($metric);
                $res2 = $this->db->get_where(MILL_OWNER_REPORT_CALCULATIONS_CRON,$whereCondition)->row_array();
                $value_ly = !empty($res2[$metric]) ? $res2[$metric] : '0.00';
                $res['value'] = $value;
                $res['value_ly'] = $value_ly;
                $this->res = $res;
          }      
        } 
    

    
    
 }       