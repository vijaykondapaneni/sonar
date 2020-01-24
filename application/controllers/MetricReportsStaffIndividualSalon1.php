<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class MetricReportsStaffIndividualSalon1 extends REST_Controller
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

    CONST SERVICE_URL_TODAY = 'MetricReportsStaffIndividualSalon/getMetricReportsStaffIndividualSalon/Today';
    CONST SERVICE_URL_LASTWEEK = 'MetricReportsStaffIndividualSalon/getMetricReportsStaffIndividualSalon/Lastweek';
    CONST SERVICE_URL_LASTMONTH = 'MetricReportsStaffIndividualSalon/getMetricReportsStaffIndividualSalon/Lastmonth';
    CONST SERVICE_URL_LAST90DAYS = 'MetricReportsStaffIndividualSalon/getMetricReportsStaffIndividualSalon/Last90days';
    

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('OwnerWebServices_model');
        $this->load->model('saloncloudsplus_model');
        $this->load->library('webserviceaccess');
    }
    // Get Date Range Types

    function __getStartEndDate($type,$salon_id)
    {
        $dayRangeType = $this->dayRangeType =  $type;
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

    public function __getType($metric){
        //print $internalGraphType; 
        switch ($metric) {
            case 'total_sales':
                $this->column = 'total_sales';
                break;
            case 'service_revenue':
                $this->column = 'total_service_sales';
                break; 
            case 'total_retail_price':
                $this->column = 'total_retail_price';
                break;        
            case 'gift_cards':
                $this->column = 'gift_cards';
                break;
            case 'guest_qty_new':
               $this->column = 'new_guest_qty';
                break;
            case 'guest_qty_repeated':
                $this->column = 'repeated_guest_qty';
                break;
            case 'RPCT':
                $this->column = 'RPCT';
                break;
            case 'percent_booked':
                $this->column = 'percent_booked';
                break;
            case 'prebook_percentage':
               $this->column = 'prebook_percentage';
                break;
            case 'color_percentage':
               $this->column = 'color_percentage';
                break;
            default:
                $this->column = 'total_service_sales';
                break; 
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
   
    public function getMetricReportsStaffIndividualSalon(){
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

        
        $type = $_POST['type'];
        $metric = $_POST['metric'];
        $salon_id = $_POST['salon_id'];
        $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
        $this->salonDetails = $salonDetails;
        $salon = isset($salonDetails['salon_info']) && !empty($salonDetails['salon_info']) ? $salonDetails['salon_info']:'';
        $sdkdata = $this->Common_model->getMillSdkConfigDetailsBy($_POST['salon_id'])->row_array();
        if(!empty($salon) && isset($salon["millennium_enabled"]) && isset($salon["service_retail_reports_enabled"]) && $salon["millennium_enabled"]=="Yes" && $salon["service_retail_reports_enabled"]=="Yes"){

              $this->__getType($metric);
              $this->__getStartEndDate($type,$salon_id);
              //pa($this->startDate);
              //pa($this->endDate);
              //pa($this->column,'',false);

              

              $res1 = $this->db->query('SELECT reports.salon_id,start_date,end_date,'.$this->column.',emp.name,emp.  emp_iid,emp.image,emp.staff_id  FROM 
                    '.MILL_REPORT_CALCULATIONS_CRON.' reports 
                    join '.STAFF2_TABLE.' emp on reports.salon_id=emp.salon_id
                    WHERE 
                    reports.salon_id = "'.$salon_id.'" and 
                    reports.start_date = "'.$this->startDate.'" and
                    reports.end_date = "'.$this->endDate.'" and
                    reports.dayRangeType = "'.$this->Range.'" group by reports.staff_id ')->result_array();
              //pa($this->db->last_query());
              //pa($res1,'res',true);

              if(!empty($res1)){
                  foreach ($res1 as $key => $value) {
                    $dataArray['staff_id'] = $value['staff_id'];
                    $dataArray['name'] = $value['name'];
                    $dataArray['image'] = $value['image'];
                    $dataArray['value'] =  !empty($value[$this->column]) ? $value[$this->column] : '0';  
                    $this->db->select($this->column);
                    $this->db->where('dayRangeType',$this->Range);
                    $this->db->where('start_date',$this->lastYearStartDate);
                    $this->db->where('end_date',$this->lastYearEndDate);
                    $res2 = $this->db->get(MILL_REPORT_CALCULATIONS_CRON)->row_array();
                    $dataArray['value_ly'] =  !empty($res2[$this->column]) ? $res2[$this->column] : '0';
                    $response_array['data'][] = $dataArray;
                 }
              }else{
                $response_array['data'][] = array();
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
                pa($this->db->last_query(),'',false);
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