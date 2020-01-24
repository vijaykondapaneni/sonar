<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;


class ServiceAndRetailreportsWs extends REST_Controller
{
    public  $salonId;
    public  $staffId;
    public  $startDate;
    public  $endDate;
    public  $currentDate;
    private $dayRangeType;
    CONST SERVICE_URL = 'ServiceAndRetailreportsWs/getServiceAndRetailreports';

    function __construct(){ 
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->library('webserviceaccess');
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE); 
    }
     /**
    * Auth Function
    */
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
    
    // Get Date Range Types
    public function __getStartEndDate($dayRangeType, $lastweeks = 0)
    {
        $this->dayRangeType =  $dayRangeType;
        $currentDate = getDateFn();
        switch ($this->dayRangeType) {
            case "day":
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
            break;
            case "lastweeks":
                 if(isset($this->salonInfo["salon_start_day_of_week"]) && !empty($this->salonInfo["salon_start_day_of_week"]))
                    {
                       
                       $fourWeeksArr = getLast4BusinessWeeksRanges($this->salonInfo["salon_start_day_of_week"],date('Y'));

                        $noOfWeeks = $lastweeks;
                        $lastDayOfTheWeek = $this->salonInfo["salon_start_day_of_week"];
                        $currentYearFirstDay = date('Y-m-d',strtotime('last '.$lastDayOfTheWeek.' -'.$noOfWeeks.' week'));
                        $currentWeekFirstDay = date("Y-m-d",strtotime('last '.$lastDayOfTheWeek));
                        $end_day_of_this_week = strtotime($currentWeekFirstDay.' +6 days');
                        $currentDate = date('Y-m-d', $end_day_of_this_week);

                    }
                    else
                    {
                        $fourWeeksArr = getLast4WeekRanges(date('Y'));

                        $noOfWeeks = $lastweeks;
                        $currentDate = date('Y-m-d',strtotime('last sunday'));
                        $currentYearFirstDay = date('Y-m-d',strtotime('last monday -'.$noOfWeeks.' week'));

                        if($currentYearFirstDay > $currentDate)
                        {
                            $noOfWeeks = $lastweeks - 1;
                            $currentDate = date('Y-m-d',strtotime('last sunday'));
                            $currentYearFirstDay = date('Y-m-d',strtotime('last monday -'.$noOfWeeks.' week'));
                        }
                        else
                        {
                            $noOfWeeks = $lastweeks;
                            $currentDate = date('Y-m-d',strtotime('last sunday'));
                            $currentYearFirstDay = date('Y-m-d',strtotime('last monday -'.$noOfWeeks.' week'));
                        }
                    }

                    $first_date = $fourWeeksArr[0]['start_date'];
                    $last_date = $fourWeeksArr[3]['end_date'];

                    function dateRange( $first, $last, $step = '+1 day', $format = 'Y-m-d' ) 
                    {
                        $dates = array();
                        $current = strtotime( $first );
                        $last = strtotime( $last );

                        while( $current <= $last ) {

                            $dates[] = date( $format, $current );
                            $current = strtotime( $step, $current );
                        }

                        return $dates;
                   }

                   $dateranges =  dateRange( $first_date,$last_date);
                    if(!empty($currentDate) && !empty($currentYearFirstDay)){
                            $aryRange=array();
                            $iDateFrom=mktime(1,0,0,substr($currentYearFirstDay,5,2),     substr($currentYearFirstDay,8,2),substr($currentYearFirstDay,0,4));
                            $iDateTo=mktime(1,0,0,substr($currentDate,5,2),substr($currentDate,8,2),substr($currentDate,0,4));
                            if ($iDateTo>=$iDateFrom)
                            {
                                array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
                                while ($iDateFrom<$iDateTo)
                                {
                                    $iDateFrom+=86400; // add 24 hours
                                    array_push($aryRange,date('Y-m-d',$iDateFrom));
                                }
                            }
                        } 

                        if(!empty($aryRange)){
                            $this->weekDays = $dateranges;
                            $this->startDate = $first_date;
                            $this->endDate = $last_date;
                            /*$this->weekDays = $aryRange;
                            $this->startDate = current($aryRange);
                            $this->endDate = end($aryRange);
                            pa($this->weekDays);
                            pa($this->startDate);
                            pa($this->endDate);
                            */
                        }
                        else
                        {
                            $this->weekDays = array();
                            $this->startDate = "";
                            $this->endDate = "";
                        }   
                break;
            case "last_month":
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
            break;
            case "current_month":
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = getDateFn(strtotime("last day of of this month"));
            break;
            default:
                $this->startDate =  $currentDate;
                $this->endDate   =  $currentDate;
           break;
        }
    }
    /**
    *Prebook Graphs
    */    
    public function getServiceAndRetailreports(){
            $service_url = self::SERVICE_URL;
            if(isset($_POST['salon_id'])){
                $salon_id = $_POST['salon_id'];
            }else{
                $salon_id = '';
            }

            $this->__getAccessWesbService($service_url,$salon_id);
            if($this->WebAccessResponse['HTTPCODE'] != 200){
               $response_array = array('status' => false, 'message' => $this->WebAccessResponse['MESSAGE'], 'status_code' => 401);
                $response_code = $this->WebAccessResponse['HTTPCODE'];
                goto response;    
            }


           
        
        if(!isset($_POST['salon_id'])){
            $response_array = array('status' => false, 'error' => 'Staff Id Missed', 'error_code' => 402);
            $response_code = 200; 
            goto response;
        }
        if(isset($_POST['salon_id'])){
          $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
          $salonData = $salonDetails['salon_info'];
          $this->salonInfo = $salonData;
          if(empty($salonData)){
            $response_array = array('status' => false, 'error' => 'Staff Id Missed', 'error_code' => 402);
            $response_code = 200; 
            goto response;
          }  
        }  
        // GET START DATE AND END DATE AS PER PARAMETERS
        $dayRangeType = $_POST['dateRange'];
        $salon_id = $_POST['salon_id'];
        if(isset($_POST['lastweeksrange'])){
            $lastweeksrange = $_POST['lastweeksrange'];
        }else{
            $lastweeksrange = 0;
        }
        $this->currentDate = getDateFn();
       // pa($this->salonInfo,'salonData',false);
        $this->__getStartEndDate($dayRangeType,$lastweeksrange);

        if(!empty($this->startDate) && !empty($this->endDate)){
             $aryRange=array();
            if($dayRangeType=='last_month'){
                $begin = new DateTime($this->startDate);
                $end = new DateTime($this->endDate);
                $end = $end->modify( '+1 day' );
                $interval = new DateInterval('P1D');
                $daterange = new DatePeriod($begin, $interval ,$end);
                //pa($daterange,'',true);
                $mainurl = MAIN_SERVER_URL;
                $salesArray = array();
                foreach ($daterange as $key => $date) {
                    $month = $date->format("m");
                    $month = ltrim($month, '0');
                    $monthName = $date->format("F");
                    $year = date("Y");
                    $firstDayOfMonth = $date->format("Y-m-d");
                    array_push($aryRange,$firstDayOfMonth);
                } 
            }else{

               
                $iDateFrom=mktime(1,0,0,substr($this->startDate,5,2),substr($this->startDate,8,2),substr($this->startDate,0,4));
                $iDateTo=mktime(1,0,0,substr($this->endDate,5,2),     substr($this->endDate,8,2),substr($this->endDate,0,4));
                if ($iDateTo>=$iDateFrom)
                {
                    array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
                    while ($iDateFrom<$iDateTo)
                    {
                        $iDateFrom+=86400; // add 24 hours
                        array_push($aryRange,date('Y-m-d',$iDateFrom));
                    }
                }

            }

            
        
        }
        if(!empty($aryRange)){
            $dataArray['weekDays'] = $aryRange;
            $dataArray['firstDay'] = current($aryRange);
            $dataArray['lastDay'] = end($aryRange);
        }
        else
        {
            $dataArray['weekDays'] = array();
            $dataArray['firstDay'] = "";
            $dataArray['lastDay'] = "";
        }


        $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
        $salonData = $salonDetails['salon_info'];
        $salonAccountNo = $salonData['salon_code'];
         
        if(isset($_POST['staff_id']) && !empty($_POST['staff_id']))
        {
            $this->DB_ReadOnly->where("staff_id", $_POST['staff_id']);
            $this->DB_ReadOnly->where("salon_id",$_POST['salon_id']);
            $getStaffData = $this->DB_ReadOnly->get(STAFF2_TABLE);
            $staffData = $getStaffData->row_array();
            if(!empty($staffData))
            {
                if(!empty($staffData['emp_iid']) || $staffData['emp_iid']!=0)
                {
                    $employeeId = $staffData['emp_iid'];
                }
                else
                {
                    $employeeId = 0;
                }
            }
            else
            {
                $employeeId = 0;
            }
            if($employeeId==0){
               $response_array = array('status' => false, 'error' => 'Employee IId Is Not Set In Plus Server', 'error_code' => 402);
               $response_code = 402; 
               goto response;  
            }
            $whereConditions = array('startDate'=>$this->startDate,'endDate'=>$this->endDate,'account_no'=>$salonAccountNo,'iempid'=>$employeeId);
           // pa($whereConditions);
           
            //product sales
            $productsales = $this->Common_model->getProductSalesForGraphs($whereConditions)->result_array();
           /* print_r($this->db->last_query());
            exit;*/
            $productsalesArr = array();
            foreach ($productsales as $key => $value) {
                $retail['clientName'] = $value['Name'];
                $retail['invoice'] = $value['cinvoiceno'];
                $retail['description'] = $value['cproductdescription'];
                $retail['quantity'] = (int)$value['nquantity'];
                $retail['price'] = number_format($value['nprice'],2);
                $retail['purchaseDate'] = $value['tdatetime'];
                array_push($productsalesArr,$retail);
            }
            $dataArray['retailSales'] = $productsalesArr;
            // service sales
            $servicesales = $this->Common_model->getServiceSalesForGraphs($whereConditions)->result_array();
           // print_r($this->db->last_query());
            $servicesalesArr = array();
            foreach ($servicesales as $key => $value) {
                $service['clientName'] = $value['Name'];
                $service['invoice'] = $value['cinvoiceno'];
                $service['description'] = $value['cservicedescription'];
                $service['quantity'] = (int)$value['nquantity'];
                $service['price'] = number_format($value['nprice'],2);
                $service['serviceDate'] = $value['tdatetime'];
                array_push($servicesalesArr,$service);
            }
            $dataArray['serviceSales'] = $servicesalesArr;
            $this->DB_ReadOnly->where('iid',$employeeId);
            $this->DB_ReadOnly->where('account_no',$salonAccountNo);
            $getEmployeeName = $this->DB_ReadOnly->get("mill_employee_listing");
            $resultData = $getEmployeeName->row_array();
            if(!empty($resultData)){
                $dataArray['employeeData'] = $resultData;   
            }
            else
            {
                $dataArray['employeeData'] = array();   
            }
            $dataArray['status'] = true;
            $dataArray['message'] = "Data Found.";
            $response_array = $dataArray;
            $response_code = 200;

        }else{
            $response_array = array('status' => false, 'error' => 'Staff Id Missed', 'error_code' => 402);
            $response_code = 402; 
            goto response;
        }
        response:
           $this->response($response_array, $response_code);
    }

    
    
}