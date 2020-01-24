<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class MetricReportsIndvidualSalon extends REST_Controller
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
    private $lastYearStartDate;
    private $lastYearEndDate;   
    private $ouathResponse;
    CONST SERVICE_URL_TODAY = 'MetricReportsIndvidualSalon/getMetricReportsIndvidualSalon/Today';
    CONST SERVICE_URL_LASTWEEK = 'MetricReportsIndvidualSalon/getMetricReportsIndvidualSalon/Lastweek';
    CONST SERVICE_URL_LASTMONTH = 'MetricReportsIndvidualSalon/getMetricReportsIndvidualSalon/Lastmonth';
    CONST SERVICE_URL_LAST90DAYS = 'MetricReportsIndvidualSalon/getMetricReportsIndvidualSalon/Last90days';
        
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('OwnerWebServices_model');
        $this->load->library('webserviceaccess');
    }
    // Get Date Range Types
    function __getStartEndDate($dayRangeType,$year=1)
    {
        $this->dayRangeType =  $dayRangeType;
        $currentDate = date('Y-m-d');

         switch ($this->dayRangeType) {
            case "Today":
                $this->startDate = $currentDate;
                $this->endDate = $currentDate;
                $this->lastYearStartDate =  getDateFn(strtotime("-$year year"));
                $this->lastYearEndDate = getDateFn(strtotime("-$year year"));
                $this->Range = 'today';
                $this->Service_url = self::SERVICE_URL_TODAY;
            break;                          
            case "Weekly":
                    if(isset( $this->salonDetails['salon_info']["salon_start_day_of_week"]) && !empty($this->salonDetails['salon_info']["salon_start_day_of_week"]))
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
                    $this->Service_url = self::SERVICE_URL_LASTWEEK;

            break;
            case "Lastmonth":
                    $this->startDate = getDateFn(strtotime("first day of last month"));
                    $this->endDate = getDateFn(strtotime("last day of last month"));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                    $this->Range = 'Last Month';
                    $this->Service_url = self::SERVICE_URL_LASTMONTH;
            break;
            case "Monthly":
                    $this->startDate = getDateFn(strtotime("first day of this month"));
                    $this->endDate = $currentDate;
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                    $this->Range = 'Monthly';
                    $this->Service_url = self::SERVICE_URL_LASTMONTH;
            break;
            case "Last90days":                                   
                    $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                    $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                    $this->endDate = getDateFn(strtotime("last day of last month"));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                    $this->Range = 'Last Three Months';
                    $this->Service_url = self::SERVICE_URL_LAST90DAYS;
            break;
            case "Yearly":                                   
                    $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                    $this->endDate = $currentDate;
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                    $this->Range = 'yearly';
                    $this->Service_url = self::SERVICE_URL_LAST90DAYS;
            break;          
            default:
                    $this->startDate =  $currentDate;
                    $this->endDate   =  $currentDate;
                    $this->lastYearStartDate =  getDateFn(strtotime("-$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime("-$year year"));
                    $this->Range = 'today';
                    $this->Service_url = self::SERVICE_URL_TODAY;
           break;
        }
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
     * This function for get employee schedule hours last year data
     * @param type $account_no
     */
    function getMetricReportsIndvidualSalon()
        { 
           if(isset($_POST['salon_id'])){
                $salon_id = $_POST['salon_id'];
            }else{
                $salon_id = '';
            }
            $type = $dayRangeType = $_POST['type'];
            $this->__getStartEndDate($dayRangeType);
            /*$this->__getAccessWesbService($this->Service_url,$salon_id);
            if($this->WebAccessResponse['HTTPCODE'] != 200){
               $response_array = array('status' => false, 'message' => $this->WebAccessResponse['MESSAGE'], 'status_code' => 401);
                $response_code = $this->WebAccessResponse['HTTPCODE'];
                goto response;    
            }*/
           
            if(isset($_POST['salon_id']) && $_POST['salon_id']!=''){
                $salon_id = $_POST['salon_id'];
                $staff_id = isset($_POST['salon_id']) ? $_POST['salon_id'] : '';
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $this->salonDetails = $salonDetails;
                $salon = isset($salonDetails['salon_info']) && !empty($salonDetails['salon_info']) ? $salonDetails['salon_info']:'';
                $sdkdata = $this->Common_model->getMillSdkConfigDetailsBy($_POST['salon_id'])->row_array();
                //pa($sdkdata,'sdkdata',true);
                $service_types = $sdkdata['service_types'];
                $leaderboard_type = $sdkdata['leaderboard_type'];
                
                if(!empty($salon) && isset($salon["millennium_enabled"]) && isset($salon["service_retail_reports_enabled"]) && $salon["millennium_enabled"]=="Yes" && $salon["service_retail_reports_enabled"]=="Yes"){
                     $this->__getStartEndDate($dayRangeType);
                     $dbrange = $this->Range;
                    // Current Year Data
                    $whereCondition = array('salon_id' => $salon_id,'report_type' => 'currentYearReport','dayRangeType' => $dbrange,'start_date' => $this->startDate,'end_date' => $this->endDate);
                    $res1 = $this->OwnerWebServices_model
                            ->getOwnerReports($whereCondition)
                            ->row_array();
                    // Last Year Data
                    $whereCondition = array('salon_id' => $salon_id,'report_type' => 'lastYearReport','dayRangeType' => $dbrange,'start_date' => $this->lastYearStartDate,'end_date' => $this->lastYearEndDate);
                    $res2 = $this->OwnerWebServices_model
                            ->getOwnerReports($whereCondition)
                            ->row_array();
                   
                    $data = array();
                    $data['type'] = $this->Range;
                    $locations = array();
                    $ownerdata['name'] = !empty($salon["salon_name"]) ? $salon["salon_name"] : '';
                   // $ownerdata['dateRange'] = $this->startDate . " to " . $this->endDate;
                    $start_date  = date("m/d/Y", strtotime($this->startDate)); 
                    $end_date  = date("m/d/Y", strtotime($this->endDate));
                    $ownerdata['dateRange'] = $start_date . " to " . $end_date;
                    $tiles = array();
                    $res = array();
                    $res[0]['title'] = 'Total Sales';
                    $res[0]['key']  = 'total_sales';
                    $res[0]['type'] = '$';
                    $res[1]['title'] = 'Service Sales'; 
                    $res[1]['key'] = 'service_revenue';
                    $res[1]['type'] = '$';
                    $res[2]['title'] = 'Retail Sales'; 
                    $res[2]['key'] = 'total_retail_price';
                    $res[2]['type'] = '$';
                    $res[3]['title'] = 'Gift Cards'; 
                    $res[3]['key'] = 'gift_cards';
                    $res[3]['type'] = '$';
                    $res[4]['title'] = 'New Guest'; 
                    $res[4]['key'] = 'guest_qty_new';
                    $res[4]['type'] = '';
                    $res[5]['title'] = 'Repeat Guest'; 
                    $res[5]['key'] = 'guest_qty_repeated';
                    $res[5]['type'] = '';
                    $res[6]['title'] = 'RPCT'; 
                    $res[6]['key'] = 'RPCT';
                    $res[6]['type'] = '$';
                    $res[7]['title'] = '% Booked'; 
                    $res[7]['key'] = 'percent_booked';
                    $res[7]['type'] = '%';
                    $res[8]['title'] = '% Prebook'; 
                    $res[8]['key'] = 'prebook_percentage';
                    $res[8]['type'] = '%';
                    $res[9]['title'] = '% Color'; 
                    $res[9]['key'] = 'color_percentage';
                    $res[9]['type'] = '%';
                    $res[10]['title'] = 'Avg Service Ticket'; 
                    $res[10]['key'] = 'avg_service_ticket';
                    $res[10]['type'] = '$';
                    $res[11]['title'] = 'Estimated Sales'; 
                    $res[11]['key'] = 'estimated_sales';
                    $res[11]['type'] = '$';
                    foreach ($res as $key => $value) {
                            $metrics_data = array();
                            $metrics_data['title'] = $value['title'];
                            $metrics_data['value'] = !empty($res1) ? 
                              $res1[$value['key']] : '0.0';
                            $metrics_data['value_ly'] = !empty($res2) ? 
                              $res2[$value['key']] : '0.0';
                            $metrics_data['type'] = $value['type'];
                            $tiles[] = $metrics_data;
                    }
                    
                    $ownerdata['data'] = $tiles;
                    $response_array['data'] = $ownerdata;
                    $response_array['status'] = true; 
                    $response_code = 200;

                }else{
                    $response_array = array('status' => false, 'message' => 'Salon Details Not Found', 'status_code' => 402);
                    $response_code = 402; 
                    goto response;
                }
               
            }else{
                $response_array = array('status' => false, 'message' => 'Invalid Salon Id', 'status_code' => 401);
                $response_code = 401;
                goto response; 
            }

           response:
           $this->response($response_array, $response_code);
            
       } 
   
 }       