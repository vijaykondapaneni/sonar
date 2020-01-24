<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;


class StaffGraphsWs extends REST_Controller
{
    public  $salonId;
    public  $staffId;
    public  $startDate;
    public  $endDate;
    public  $currentDate;
    private $dayRangeType;
    private $lastOneMonthstartDate;
    private $lastTwoMonthsstartDate;   
    private $lastThreeMonthsstartDate;

    CONST Staff_WebService_MONTHLY = 'StaffGraphsWs/getInernalGraph/Monthly';
    CONST Staff_WebService_Yearly = 'StaffGraphsWs/getInernalGraph/Yearly';
    CONST Staff_WebService_Last90days = 'StaffGraphsWs/getInernalGraph/Last90Days';

    
    
    function __construct(){ 
        parent::__construct();
        $this->load->library('webserviceaccess');
        $this->load->model('Common_model'); 
    }
     /**
    * Auth Function
    */
    private function __getAccessWesbService($service,$salon_id){
               $this->WebAccessResponse = $this->webserviceaccess->validateWebAppWs($service,$salon_id); 
             
                return $this->WebAccessResponse;
    }
    
    // Get Date Range Types
    public function __getStartEndDate($dayRangeType, $year = 1 ,$s = '', $e = '')
    {
        $this->dayRangeType =  $dayRangeType;
        $this->currentDate = getDateFn();
        switch ($this->dayRangeType) {
            case TODAY:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
                $this->Service_url = self::Staff_WebService_Yearly;
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
                    $this->Service_url = self::Staff_WebService_Yearly;   
                break;
            case LASTMONTH:
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->Service_url = self::Staff_WebService_Yearly;
            break;
            case MONTHLY:
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $this->currentDate;
                $this->Service_url = self::Staff_WebService_MONTHLY;
            break;
            case "THREEMONTHS":
            case LAST90DAYS:                      
            case "Last90days":
            case "Last90Days":    
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->lastOneMonthstartDate = getDateFn(strtotime("first day of last month"));
                $this->lastTwoMonthsstartDate = getDateFn(strtotime($this->lastOneMonthstartDate. " -1 month"));
                $this->lastThreeMonthsstartDate = getDateFn(strtotime($this->lastOneMonthstartDate. " -2 months")); 
                $this->Service_url = self::Staff_WebService_Last90days;
            break;
            case YEARLY:                                   
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                $this->endDate = $this->currentDate;
                $this->Service_url = self::Staff_WebService_Yearly;
            break;
            case CUSTOMDATE:
                $this->startDate = $s;
                $this->endDate = $e;
                $this->Service_url = self::Staff_WebService_Yearly;
            break;
            default:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
                $this->Service_url = self::Staff_WebService_Yearly;
           break;
        }
    }
    
    public function getInernalGraph($dayRangeType="Today"){
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


        $internalGraphType = urldecode($_POST['graph_type']);
        
        $salon_id = $_POST['salon_id'];
        if(!isset($_POST['salon_id']) || ($_POST['salon_id']=='')){
            $response_array = array('status' => false, 'error' => 'Salon Id Required', 'error_code' => 402);
            $response_code = 200; 
            goto response;
        }
        if(!isset($_POST['graph_type']) || ($_POST['graph_type']=='')){
            $response_array = array('status' => false, 'error' => 'Type Required', 'error_code' => 402);
            $response_code = 200; 
            goto response;
        }
        if(!isset($_POST['staff_id'])|| ($_POST['staff_id']=='')){
            $response_array = array('status' => false, 'error' => 'Staff Id Required', 'error_code' => 402);
            $response_code = 200; 
            goto response;
        }
    

        $salon_id = $_POST['salon_id'];
        $staff_id = $_POST['staff_id'];

        $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
        $salonData = $salonDetails['salon_info'];
        $dataArray['location_id'] = $salonData['salon_id'];
        $dataArray['location_name'] = $salonData['salon_name'];
        $this->__getType($dayRangeType,$internalGraphType,$salon_id,$staff_id);
       
        /*print $this->current_value;
        print $this->last_year_value;
        
        print_r($this->res);
        exit;
*/
        
        if(!empty($this->res))
            {
               $tempArr = array();
                foreach($this->res as $retailResult)
                {
                    $valuesArr["current_value"] = $retailResult[$this->current_value];
                    $valuesArr["last_year_value"] = $retailResult[$this->last_year_value];
                    $valuesArr["key"] = $retailResult["key"];
                    $tempArr[] = $valuesArr;
                }
                $dataArray["graph_data"] = $tempArr;
                
            }
            else
            {
                $dataArray["graph_data"] = array();
                $dataArray["status"] = false;
            }
            $response_array['values'][] = $dataArray;
    
       $response_array['start_date'] = $this->startDate; 
       $response_array['end_date'] = $this->endDate;
       $response_array["status"] = true;
       $response_array["graph_type"] = $internalGraphType;
       $response_code = 200;

       response:
       $this->response($response_array, $response_code);
    }


    public function __getType($dayRangeType,$internalGraphType,$salon_id,$staff_id){
        switch ($internalGraphType) {
            case 'Prebook%':
                $this->res = $this->__getPrebookGraphWs($dayRangeType,$salon_id,$staff_id);
                $this->current_value = 'prebook_value';
                $this->last_year_value = 'last_year_prebook_value';
                break;
            case 'RPCT':
                $this->res = $this->__getRpctGraphWs($dayRangeType,$salon_id,$staff_id);
                $this->current_value = 'RPCT';
                $this->last_year_value = 'last_year_RPCT';    
                break;
            case 'Color':
                $this->res = $this->__getColorPercentageGraphWs($dayRangeType,$salon_id,$staff_id);
                $this->current_value = 'color_percentage';
                $this->last_year_value = 'last_year_color_percentage'; 
                break;
            case 'Rebook%':
                $this->res = $this->__getRebookGraphWs($dayRangeType,$salon_id,$staff_id);
                $this->current_value = 'rebook_value';
                $this->last_year_value = 'last_year_rebook_value';
                break;
            case 'booked':
                // need to work
                $this->res = $this->__getPercentageBookedGraphWs($dayRangeType,$salon_id,$staff_id);
                $this->current_value = 'current_value';
                $this->last_year_value = 'last_year_value';
                break;
            case '% Buying Retail':
                $this->res = $this->__getRetailGraphWs($dayRangeType,$salon_id,$staff_id);
                $this->current_value = 'current_value';
                $this->last_year_value = 'last_year_value';
                break;

            case 'RUCT':
                $this->res = $this->__getRuctGraphWs($dayRangeType,$salon_id,$staff_id);
                $this->current_value = 'RUCT';
                $this->last_year_value = 'last_year_RUCT';
                break;

            case 'Avg Servic Tkt':
                $this->res = $this->__getAvgServiceTicketGraphWs($dayRangeType,$salon_id,$staff_id);
                $this->current_value = 'avg_ser_ticket_value';
                $this->last_year_value = 'last_year_avg_ser_ticket_value';
                break;
            case 'AVG Retail':
                $this->res = $this->__getAvgRetailTicketGraphWs($dayRangeType,$salon_id,$staff_id);
                $this->current_value = 'avg_rpct';
                $this->last_year_value = 'last_year_avg_rpct';
                break;
            default:
                $this->res = $this->__getPrebookGraphWs($dayRangeType,$salon_id,$staff_id);
                $this->current_value = 'current_value';
                $this->last_year_value = 'last_year_value';
                break;
        }
    }





    /**
    *Prebook Graphs
    */    
    public function __getPrebookGraphWs($dayRangeType="Today",$salon_id,$staff_id){
        $this->currentDate = getDateFn();
            if($dayRangeType=='Yearly'){
                    $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $this->db->group_by('MONTH(`start_date`)');
            }elseif($dayRangeType=='Last90Days'){
                    $lastOneMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
                    $lastTwoMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate."-1 month"));
                    $lastThreeMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate. "-2 months"));
                    $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => 'Yearly');
                    $this->db->where("(start_date = '".$lastOneMonthstartDate."' or start_date = '".$lastTwoMonthsstartDate."' or start_date = '".$lastThreeMonthsstartDate."')");
                    $this->db->group_by('YEAR(`start_date`),MONTH(`start_date`)');
            }else{
                     $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType);
            }
            $this->db->order_by("start_date","asc");
            $res = $this->db->select('*')
                        ->get_where(MILL_PREBOOK_CALCULATIONS_CRON,$whereReports)
                        ->result_array();
            return $res;
                    
    }
    public function __getRebookGraphWs($dayRangeType="Today",$salon_id,$staff_id){

        if($dayRangeType=='Yearly'){
           $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
           $this->db->group_by('MONTH(`start_date`)'); 
            }elseif($dayRangeType=='Last90Days'){
                $lastOneMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
                $lastTwoMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate."-1 month"));
                $lastThreeMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate. "-2 months"));
                $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => 'Yearly');
                $this->db->where("(start_date = '".$lastOneMonthstartDate."' or start_date = '".$lastTwoMonthsstartDate."' or start_date = '".$lastThreeMonthsstartDate."')");
                $this->db->group_by('YEAR(`start_date`),MONTH(`start_date`)');

            }else{
                $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType);
            }
            $res = $this->db->select('*')
                 ->get_where(MILL_PREBOOK_CALCULATIONS_CRON,$whereReports)
                 ->result_array();


            return $res;
                    
    }
    public function __getRpctGraphWs($dayRangeType="Today",$salon_id,$staff_id){
            
            if($dayRangeType=='Yearly'){
                $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                $this->db->group_by('MONTH(`start_date`)');
            }elseif ($dayRangeType=='Last90Days') {
               $lastOneMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
               $lastTwoMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate."-1 month"));
               $lastThreeMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate. "-2 months"));
               $whereReports = array('salon_id' => $salon_id ,'staff_id' =>$staff_id,'dayRangeType' => 'Yearly');
               $this->db->where("(start_date = '".$lastOneMonthstartDate."' or start_date = '".$lastTwoMonthsstartDate."' or start_date = '".$lastThreeMonthsstartDate."')");
               $this->db->group_by('YEAR(`start_date`),MONTH(`start_date`)');
            }else{
                $whereReports = array('salon_id' => $salon_id,'staff_id' =>$staff_id,'dayRangeType' =>$dayRangeType);
            }

            $res = $this->db->select('*')
                                     ->get_where(MILL_RPCT_CALCULATIONS_CRON,$whereReports)
                                     ->result_array();
            return $res;
    }
    public function __getColorPercentageGraphWs($dayRangeType="Today",$salon_id,$staff_id){
        if($dayRangeType=='Yearly'){
             $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
            $this->db->group_by('MONTH(`start_date`)');
            }elseif ($dayRangeType=='Last90Days') {
               $lastOneMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
                $lastTwoMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate."-1 month"));
                $lastThreeMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate. "-2 months"));
                $whereReports = array('salon_id' => $salon_id,'staff_id' => $staff_id,'dayRangeType' => 'Yearly');
                $this->db->where("(start_date = '".$lastOneMonthstartDate."' or start_date = '".$lastTwoMonthsstartDate."' or start_date = '".$lastThreeMonthsstartDate."')");
                $this->db->group_by('YEAR(`start_date`),MONTH(`start_date`)');
            }else{
                 $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType);
            }
            $res = $this->db->select('*')
                                     ->get_where(MILL_RPCT_CALCULATIONS_CRON,$whereReports)
                                     ->result_array();
            return $res;                      
                    
    }
    public function __getAvgRetailTicketGraphWs($dayRangeType="Today",$salon_id,$staff_id){

        if($dayRangeType=='Yearly'){
             $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
            $this->db->group_by('MONTH(`start_date`)');
        }elseif($dayRangeType=='Last90Days'){
            $lastOneMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
            $lastTwoMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate."-1 month"));
            $lastThreeMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate. "-2 months"));
            $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => 'Yearly');
            $this->db->where("(start_date = '".$lastOneMonthstartDate."' or start_date = '".$lastTwoMonthsstartDate."' or start_date = '".$lastThreeMonthsstartDate."')");
            $this->db->group_by('YEAR(`start_date`),MONTH(`start_date`)');
        }else{
            $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType);
        }
        $res = $this->db->select('*')
                                     ->get_where(MILL_RPCT_CALCULATIONS_CRON,$whereReports)
                                     ->result_array();
        return $res;           
    }
    public function __getAvgServiceTicketGraphWs($dayRangeType="Today",$salon_id,$staff_id){
            
            if($dayRangeType=='Yearly'){
                 $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                $this->db->group_by('MONTH(`start_date`)');
                }elseif($dayRangeType=='Last90Days'){
                    $lastOneMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
                    $lastTwoMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate."-1 month"));
                    $lastThreeMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate. "-2 months"));
                    $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => 'Yearly');
                    $this->db->where("(start_date = '".$lastOneMonthstartDate."' or start_date = '".$lastTwoMonthsstartDate."' or start_date = '".$lastThreeMonthsstartDate."')");
                    $this->db->group_by('YEAR(`start_date`),MONTH(`start_date`)');
                }else{
                    $whereReports = array('salon_id' => $salon_id,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType);
                }
                $res = $this->db->select('*')
                                     ->get_where(MILL_RPCT_CALCULATIONS_CRON,$whereReports)
                                     ->result_array();
            return $res;        
    }
    public function __getRuctGraphWs($dayRangeType="Today",$salon_id,$staff_id){
        
        if($dayRangeType=='Yearly'){
            $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
            $this->db->group_by('MONTH(`start_date`)');
        }elseif($dayRangeType=='Last90Days'){
            $lastOneMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
            $lastTwoMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate."-1 month"));
            $lastThreeMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate. "-2 months"));
            $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => 'Yearly');
            $this->db->where("(start_date = '".$lastOneMonthstartDate."' or start_date = '".$lastTwoMonthsstartDate."' or start_date = '".$lastThreeMonthsstartDate."')");
            $this->db->group_by('YEAR(`start_date`),MONTH(`start_date`)');
        }else{
            $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType);
        }           

        $res = $this->db->select('*')
                                     ->get_where(MILL_RUCT_CALCULATION_CRON,$whereReports)
                                     ->result_array();
        return $res;                             
                    
    }
    public function getClientServedGraphWs($dayRangeType="Today"){
        $this->currentDate = getDateFn();
        // GET START DATE AND END DATE AS PER PARAMETERS
        $this->__getStartEndDate($dayRangeType);
        if(isset($_POST['salon_id'])){
            $salon_id = $_POST['salon_id'];
        }else{
            $salon_id = '';
        }
        switch ($dayRangeType) {
            case 'Monthly':
                $service_url = self::StaffClietnServedWs_MONTHLY;
                break;
            case 'Yearly':
                $service_url = self::StaffClietnServedWs_Yearly;
                break;
            case 'threemonths':
                $service_url = self::StaffClietnServedWs_threemonths;
                break;        
            default:
                $service_url = self::StaffClietnServedWs_MONTHLY;
                break;
        }

        $this->__getAccessWesbService($service_url,$salon_id);
        /*if($this->WebAccessResponse['HTTPCODE'] == 200 && $this->WebAccessResponse['STATUS'] == 'SESSIONACTIVE')
        {    $response_array = $this->WebAccessResponse;
             $response_code = 200; 
             goto response;
        } else if($this->WebAccessResponse['HTTPCODE'] == 200 && $this->WebAccessResponse['STATUS'] == 'SUCCEED') {
        }*/
        if($this->WebAccessResponse['HTTPCODE'] != 200){
               $response_array = array('status' => false, 'message' => $this->WebAccessResponse['MESSAGE'], 'status_code' => 401);
                $response_code = $this->WebAccessResponse['HTTPCODE'];
                goto response;    
            }
        
        if(isset($_POST['salon_id']) && !empty($_POST['salon_id'])){
                if(!isset($_POST['staff_id']) && empty($_POST['staff_id'])) {
                    $response_array = array('status' => false, 'error' => 'Invalid Staff Id', 'error_code' => 401);
                    $response_code = 401;
                    goto response; 
                }
                
                    $this->salonId = $_POST['salon_id'];
                    $this->staffId = $_POST['staff_id'];

                    switch ($this->dayRangeType) {
                        case YEARLY:
                                $whereReports = array('salon_id' => $this->salonId ,'staff_id' => $this->staffId,'dayRangeType' => $this->dayRangeType,'YEAR(`start_date`)' => date('Y'));
                                $this->db->group_by('MONTH(`start_date`)');
                            break;
                        case THREEMONTHS:
                        case LAST90DAYS:     
                                $lastOneMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
                                $lastTwoMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate."-1 month"));
                                $lastThreeMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate. "-2 months"));
                                $whereReports = array('salon_id' => $this->salonId ,'staff_id' => $this->staffId,'dayRangeType' => 'Yearly');
                                $this->db->where("(start_date = '".$lastOneMonthstartDate."' or start_date = '".$lastTwoMonthsstartDate."' or start_date = '".$lastThreeMonthsstartDate."')");
                                $this->db->group_by('YEAR(`start_date`),MONTH(`start_date`)');

                            break;

                        default:
                                $whereReports = array('salon_id' => $this->salonId ,'staff_id' => $this->staffId,'dayRangeType' => $this->dayRangeType);
                            break;
                    }

                    $response_array = array();

                     $res = $this->db->select('*')
                                     ->get_where(MILL_RUCT_CALCULATION_CRON,$whereReports)
                                     ->result_array();
                    if(!empty($res))
                    {
                        $valuesArr =  array();
                        foreach($res as $k => $retailResult)
                        {
                            $valuesArr[$k]["current_value"] = $retailResult["clients_served_value"];
                            $valuesArr[$k]["key"] = $retailResult["key"];
                            $valuesArr[$k]["last_year_value"] = $retailResult["last_year_clients_served_value"];
                            
                        }
                        $response_array["graph_data"] = $valuesArr;
                        $response_array["status"] = true;
                    }
                    else
                    {
                        $response_array["graph_data"] = array();
                        $response_array["status"] = false;
                    }
                  
                $response_array['start_date'] = $this->startDate; 
                $response_array['end_date'] = $this->endDate;
                $response_array['title'] = 'Client Served';
                $response_code = 200;

                }else{
                    $response_array = array('success' => false, 'error' => 'Invalid Salon Id', 'error_code' => 401);
                    $response_code = 401;
                    goto response; 
                }
            response:
            $this->response($response_array, $response_code);
    }
    public function __getRetailGraphWs($dayRangeType="Today",$salon_id,$staff_id){
        
        if($dayRangeType=='Yearly'){
            $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
            $this->db->group_by('MONTH(`start_date`)'); 
        }elseif($dayRangeType=='Last90Days'){
            $lastOneMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
            $lastTwoMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate."-1 month"));
            $lastThreeMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate. "-2 months"));
            $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => 'Yearly');
            $this->db->where("(start_date = '".$lastOneMonthstartDate."' or start_date = '".$lastTwoMonthsstartDate."' or start_date = '".$lastThreeMonthsstartDate."')");
            $this->db->group_by('YEAR(`start_date`),MONTH(`start_date`)');
        }else{
            $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType);
        }
                    
        $res = $this->db->select('*')
                                     ->get_where(MILL_CLIENT_BUYING_RETAIL_STAFF_REPORTS,$whereReports)
                                     ->result_array();
        return $res;                             
                   
    }
    public function __getPercentageBookedGraphWs($dayRangeType="Today",$salon_id,$staff_id){
        
        if($dayRangeType=='Yearly'){
            $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
            $this->db->group_by('MONTH(`start_date`)');
        }elseif('Last90Days'){
            $lastOneMonthstartDate = date("Y-m-d", strtotime("first day of last month"));
            $lastTwoMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate."-1 month"));
            $lastThreeMonthsstartDate = date("Y-m-d", strtotime($lastOneMonthstartDate. "-2 months"));
            $whereReports = array('salon_id' => $salon_id,'staff_id' => $staff_id,'dayRangeType' => 'Yearly');
            $this->db->where("(start_date = '".$lastOneMonthstartDate."' or start_date = '".$lastTwoMonthsstartDate."' or start_date = '".$lastThreeMonthsstartDate."')");
            $this->db->group_by('YEAR(`start_date`),MONTH(`start_date`)');
        }else{
            $whereReports = array('salon_id' => $salon_id ,'staff_id' => $staff_id,'dayRangeType' => $dayRangeType);

        }
        $res = $this->db->select('*')
                                     ->get_where(MILL_PERCENT_BOOKED_STAFF_REPORTS,$whereReports)
                                     ->result_array();

        return $res;
    }
  
    
}