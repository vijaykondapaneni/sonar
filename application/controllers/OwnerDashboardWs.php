<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class OwnerDashboardWs extends REST_Controller
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
    CONST SERVICE_URL_TODAY = 'OwnerDashboardWs/getOwnerDashboardWs/Today';
    CONST SERVICE_URL_LASTWEEK = 'OwnerDashboardWs/getOwnerDashboardWs/Lastweek';
    CONST SERVICE_URL_LASTMONTH = 'OwnerDashboardWs/getOwnerDashboardWs/Lastmonth';
    CONST SERVICE_URL_LAST90DAYS = 'OwnerDashboardWs/getOwnerDashboardWs/Last90days';
        
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
                $this->Range = 'Today';
                $this->Service_url = self::SERVICE_URL_TODAY;
            break;                          
            case "Lastweek":
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
                    $this->Range = 'Last Week';
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
                    $this->Range = 'Yearly';
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
    
    private function __getAccessWesbService($service,$salon_id){
               $this->WebAccessResponse = $this->webserviceaccess->validateWebAppWs($service,$salon_id);
               /*if(isset($this->WebAccessResponse['HTTPCODE']) && $this->WebAccessResponse['HTTPCODE'] == 405 ){
                    show_error($this->WebAccessResponse['MESSAGE'], $this->WebAccessResponse['HTTPCODE'], $this->WebAccessResponse['HTTPCODE']);
                }else if((isset($this->WebAccessResponse['HTTPCODE']) && $this->WebAccessResponse['HTTPCODE'] != 200)) {
                    show_error($this->WebAccessResponse['MESSAGE'], $this->WebAccessResponse['HTTPCODE'], $this->WebAccessResponse['HTTPCODE']);
                    $this->WebAccessResponse = $this->WebAccessResponse;
                }else if((isset($this->WebAccessResponse['HTTPCODE']) && $this->WebAccessResponse['HTTPCODE'] == 200))
                {
                     $this->WebAccessResponse = $this->WebAccessResponse;
                }*/
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
    function getOwnerDashboardWs($dayRangeType="Today")
        { 
           if(isset($_POST['salon_id'])){
                $salon_id = $_POST['salon_id'];
            }else{
                $salon_id = '';
            }
            $this->__getStartEndDate($dayRangeType);
            $this->__getAccessWesbService($this->Service_url,$salon_id);
            if($this->WebAccessResponse['HTTPCODE'] != 200){
               $response_array = array('status' => false, 'message' => $this->WebAccessResponse['MESSAGE'], 'status_code' => 401);
                $response_code = $this->WebAccessResponse['HTTPCODE'];
                goto response;    
            }
           
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
                    // Current Year Data
                    $whereCondition = array('salon_id' => $salon_id,'report_type' => 'currentYearReport','dayRangeType' => $dayRangeType,'start_date' => $this->startDate,'end_date' => $this->endDate);
                    $res1 = $this->OwnerWebServices_model
                            ->getOwnerReports($whereCondition)
                            ->row_array();
                    // Last Year Data
                    $whereCondition = array('salon_id' => $salon_id,'report_type' => 'lastYearReport','dayRangeType' => $dayRangeType,'start_date' => $this->lastYearStartDate,'end_date' => $this->lastYearEndDate);
                    $res2 = $this->OwnerWebServices_model
                            ->getOwnerReports($whereCondition)
                            ->row_array();
                    $response_array['status'] = true; 
                    $response_array['message'] = "";
                    $data = array();
                    $data['type'] = $this->Range;
                    $locations = array();
                    $ownerdata['name'] = !empty($salon["salon_name"]) ? $salon["salon_name"] : '';
                   // $ownerdata['dateRange'] = $this->startDate . " to " . $this->endDate;
                    $start_date  = date("m/d/Y", strtotime($this->startDate)); 
                    $end_date  = date("m/d/Y", strtotime($this->endDate));
                    $ownerdata['dateRange'] = $start_date . " to " . $end_date;
                    $last_updated = !empty($res1['updatedDate']) ? date("m/d/Y", strtotime($res1['updatedDate'])) : " "; 
                    $ownerdata['last_updated'] = $last_updated; 
                                        // Total Revenue Current Year
                    $service_revenue1 = !empty($res1['service_revenue']) ? $res1['service_revenue'] : 0;
                    $total_retail_price1 = !empty($res1['total_retail_price']) ? $res1['total_retail_price'] : 0;
                    $gift_cards1 = !empty($res1['gift_cards']) ? $res1['gift_cards'] : 0;  
                    $current_total_revenue = $service_revenue1+$total_retail_price1+$gift_cards1;
                    if($current_total_revenue=='0'){
                        $current_total_revenue = '0.0';
                    }
                    // Total Revenue Last  Year
                    $service_revenue2 = !empty($res2['service_revenue']) ? $res2['service_revenue'] : 0;
                    $total_retail_price2 = !empty($res2['total_retail_price']) ? $res2['total_retail_price'] : 0;
                    $gift_cards2 = !empty($res2['gift_cards']) ? $res2['gift_cards'] : 0;  
                    $previous_total_revenue = $service_revenue2+$total_retail_price2+$gift_cards2;
                    if($previous_total_revenue=='0'){
                        $previous_total_revenue = '0.0';
                    }
                    $service_types = explode(",",$service_types);
                    $tiles = array();

                    foreach ($service_types as $key => $value) {
                        $servicedetails = $this->Common_model->getServiceTypeBy($value);
                        if($servicedetails['titleText']=='Total Revenue'){
                            $metrics_data['type'] = $servicedetails['tile_type'];
                            $metrics_data['titleText'] = $servicedetails['titleText'];
                            
                            $metrics_data_values = array();
                            $metrics_data_values['prefix'] = $servicedetails['prefix'];
                            $metrics_data_values['postfix'] = $servicedetails['postfix'];
                            $metrics_data_values['previous'] = $previous_total_revenue;
                            $metrics_data_values['current'] = $current_total_revenue;
                            if($current_total_revenue>0){
                             $metrics_data['icon'] = $servicedetails['icon'];    
                            }else{
                             $metrics_data['icon'] = $servicedetails['icon_empty'];
                            }
                            $metrics_data['values'] = $metrics_data_values;
                            $tiles[] = $metrics_data;
                        }else{
                            $metrics_data = array();
                            $metrics_data['type'] = $servicedetails['tile_type'];
                            $metrics_data['titleText'] = $servicedetails['titleText'];
                            $metrics_data_values = array();
                            $metrics_data_values['prefix'] = $servicedetails['prefix'];
                            $metrics_data_values['postfix'] = $servicedetails['postfix'];
                            $metrics_data_values['previous'] = !empty($res2) ? 
                              $res2[$servicedetails['column_name']] : '0.0';
                            $metrics_data_values['current'] = !empty($res1) ? 
                              $res1[$servicedetails['column_name']] : '0.0';
                            if($res1[$servicedetails['column_name']]>0){
                             $metrics_data['icon'] = $servicedetails['icon'];    
                            }else{
                             $metrics_data['icon'] = $servicedetails['icon_empty'];
                            }  
                            $metrics_data['values'] = $metrics_data_values;
                            $tiles[] = $metrics_data;

                        }
                    }
                    
                    $ownerdata['tiles'] = $tiles; 
                    // Close Tiles
                    // Leader Board Data
                    $leaderboard = array();
                    $leaderboard_type = explode(",",$leaderboard_type);
                    foreach ($leaderboard_type as $key => $value) {
                        $leaderboarddetails = $this->Common_model->getLeaderBoardTypeBy($value);
                        $leaderboarddata['name'] = !empty($res1) ? $res1[$leaderboarddetails['column_name']] : '';
                        $leaderboarddata['image'] = !empty($res1) ? $res1[$leaderboarddetails['image']] : '';
                        $leaderboarddata['title'] = $leaderboarddetails['title'];
                        $leaderboarddata['value'] = !empty($res1) ?  $leaderboarddetails['prefix'].$res1[$leaderboarddetails['column_value_name']].$leaderboarddetails['postfix']: $leaderboarddetails['prefix'].'0.0'.$leaderboarddetails['postfix'];
                        $leaderboard[] = $leaderboarddata;
                    }
                    if($dayRangeType=='Last90days'){
                        $leaderboard = array();
                    }
                    $ownerdata['leaders'] = $leaderboard;
                    $locations[] = $ownerdata;
                    $data['locations'] = $locations;
                    $response_array['data'] = $data;
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