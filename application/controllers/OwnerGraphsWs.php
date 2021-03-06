<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class OwnerGraphsWs extends REST_Controller
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

    CONST Staff_WebService_MONTHLY = 'OwnerGraphsWs/getInernalGraph/Monthly';
    CONST Staff_WebService_Yearly = 'OwnerGraphsWs/getInernalGraph/Yearly';
    CONST Staff_WebService_Last90days = 'OwnerGraphsWs/getInernalGraph/Last90days';
    

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('OwnerWebServices_model');
        $this->load->model('saloncloudsplus_model');
        $this->load->library('webserviceaccess');
    }
    // Get Date Range Types
    function __getStartEndDate($dayRangeType,$year=1)
    {
        $this->dayRangeType =  $dayRangeType;
        $this->currentDate = getDateFn();
         switch ($this->dayRangeType) {
            case "Today":
                $this->startDate = $this->currentDate;
                $this->endDate = $this->currentDate;
                $this->Service_url = self::Staff_WebService_MONTHLY;
            break;
            case "lastmonth":
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->Service_url = self::Staff_WebService_MONTHLY;
            break;
            case "Monthly":
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $this->currentDate;
                $this->Service_url = self::Staff_WebService_MONTHLY;
            break;
            case "Last90days":                      
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->lastOneMonthstartDate = getDateFn(strtotime("first day of last month"));
                $this->lastTwoMonthsstartDate = getDateFn(strtotime($this->lastOneMonthstartDate. " -1 month"));
                $this->lastThreeMonthsstartDate = getDateFn(strtotime($this->lastOneMonthstartDate. " -2 months")); 
                 $this->Service_url = self::Staff_WebService_Last90days;
            break;
            case "Yearly":                                   
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                $this->endDate = $this->currentDate;
                $this->Service_url = self::Staff_WebService_Yearly;
            break;          
            default:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
                $this->Service_url = self::Staff_WebService_MONTHLY;
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

    public function __getType($dayRangeType,$internalGraphType,$salon_id){
        //print $internalGraphType; 
        switch ($internalGraphType) {
            case 'Retail':
                $this->res = $this->__getOwnerRetailSalesGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case 'Service':
                $this->res = $this->__getOwnerServiceSalesGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;    
            case 'Gift Cards':
                $this->res = $this->__getOwnerGiftCardGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case 'New Guest':
                $this->res = $this->__getOwnerNewGuestGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case 'Repeat Guest':
                $this->res = $this->__getOwnerRepeatedGuestGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case 'RPCT':
                $this->res = $this->__getOwnerRPCTGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case '% BOOKED':
                $this->res = $this->__getOwnerPercentageBookedGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case '% PREBOOKED':
                $this->res = $this->__getOwnerPrebookGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case '% COLOR':
                $this->res = $this->__getOwnerColorPercentageGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case 'RUCT':
                $this->res = $this->__getOwnerRUCTGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case '% REBOOK':
                $this->res = $this->__getOwnerRebookGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case 'Client Served':
                $this->res = $this->__getOwnerClientServicedGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            case 'Total Revenue':
                $this->res = $this->__getOwnerTotalSalesGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
                break;
            default:
                $this->res = $this->__getOwnerRetailSalesGraphWs($dayRangeType,$salon_id);
                $this->table_show = 0;
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
        if(!isset($_POST['salon_id']) && !empty($_POST['salon_id'])){
            $response_array = array('status' => false, 'error' => 'Salon Id Required', 'error_code' => 402);
            $response_code = 200; 
            goto response;
        }
        if(!isset($_POST['graph_type']) && !empty($_POST['graph_type'])){
            $response_array = array('status' => false, 'error' => 'Type Required', 'error_code' => 402);
            $response_code = 200; 
            goto response;
        }

        
        $relatedSalons = $this->saloncloudsplus_model->relatedSalons($salon_id);


        if(!empty($relatedSalons)){
             foreach ($relatedSalons as $eachSalon) {
                $salon_id = $eachSalon['salon_id'];
                $salon_name = $eachSalon['salon_name'];
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                $this->__getType($dayRangeType,$internalGraphType,$salon_id);
                if(!empty($this->res))
                    {
                       $tempArr = array();
                        foreach($this->res as $retailResult)
                        {
                            $valuesArr["current_value"] = $retailResult["current_value"];
                            $valuesArr["last_year_value"] = $retailResult["last_year_value"];
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
                 }   
             }
        
           $response_array['start_date'] = $this->startDate; 
           $response_array['end_date'] = $this->endDate;
           $response_array["status"] = true;
           $response_array["table_show"] = $this->table_show;
           $response_code = 200;
           response:
           $this->response($response_array, $response_code);
    }
    

    
    /**
     * This function for get owner retail sales graphs
     * @param type $dayrangetype
     */
    function __getOwnerRetailSalesGraphWs($dayRangeType,$salon_id)
        {  
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
               
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                 
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                    
                }

                return  $this->OwnerWebServices_model
                               ->getRetailSalesWebService($whereCondition,$where,$groupby)
                               ->result_array();
          }      
        } 
    /**
     * This function for get owner service sales graphs
     * @param type $dayrangetype
    */
    function __getOwnerServiceSalesGraphWs($dayRangeType,$salon_id)
        { 
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
                return  $this->OwnerWebServices_model
                       ->getServiceSalesWebService($whereCondition,$where,$groupby)
                       ->result_array();
            }
        }
    /**
     * This function for get owner gift card graphs
     * @param type $dayrangetype
    */
    function __getOwnerGiftCardGraphWs($dayRangeType="Today",$salon_id)
        { 
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                        $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
                return $this->OwnerWebServices_model
                               ->getGiftCardWebService($whereCondition,$where,$groupby)
                               ->result_array();
             }
        }
    /**
     * This function for get owner new guest graphs
     * @param type $dayrangetype
    */
    function __getOwnerNewGuestGraphWs($dayRangeType="Today",$salon_id)
        { 
          if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
                return $this->OwnerWebServices_model
                       ->getNewGuestWebService($whereCondition,$where,$groupby)
                       ->result_array();
               }
        } 
    /**
     * This function for get owner repeated Guest graphs
     * @param type $dayrangetype
    */
    function __getOwnerRepeatedGuestGraphWs($dayRangeType="Today",$salon_id)
        { 
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                  
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
                return $this->OwnerWebServices_model
                       ->getRepeatedGuestWebService($whereCondition,$where,$groupby)
                       ->result_array();
            }            
        }
    /**
     * This function for get owner RPCT graphs
     * @param type $dayrangetype
    */
    function __getOwnerRPCTGraphWs($dayRangeType="Today",$salon_id)
        {   
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
            }
            return $this->OwnerWebServices_model
                        ->getRPCTWebService($whereCondition,$where,$groupby)
                        ->result_array();
        }
    /**
     * This function for get owner Prebook graphs
     * @param type $dayrangetype
    */
    function __getOwnerPrebookGraphWs($dayRangeType="Today",$salon_id)
        {  
            if(isset($salon_id) && $salon_id!=''){
                
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                    
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
            return $this->OwnerWebServices_model
                            ->getPrebookWebService($whereCondition,$where,$groupby)
                            ->result_array();
            }        
        }
    /**
     * This function for get owner Color Percentage graphs
     * @param type $dayrangetype
    */
    function __getOwnerColorPercentageGraphWs($dayRangeType="Today",$salon_id)
        { 
            
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                        
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                   
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
                return $this->OwnerWebServices_model
                       ->getColorPercentageWebService($whereCondition,$where,$groupby)
                       ->result_array();
                       // pa($this->db->last_query());
            }           
        } 
    /**
     * This function for get owner Percentage booked graphs
     * @param type $dayrangetype
    */
    function __getOwnerPercentageBookedGraphWs($dayRangeType="Today",$salon_id)
        {  
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                   
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
                return $this->OwnerWebServices_model
                       ->getPercentageBookedWebService($whereCondition,$where,$groupby)
                       ->result_array();
            }          
        }
    /**
     * This function for get owner Percentage booked graphs
     * @param type $dayrangetype
    */
    function __getOwnerRUCTGraphWs($dayRangeType="Today",$salon_id)
        { 
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                   if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
                return $this->OwnerWebServices_model
                       ->getRUCTWebService($whereCondition,$where,$groupby)
                       ->result_array();
           }          
        } 
    /**
     * This function for get owner Percentage booked graphs
     * @param type $dayrangetype
    */
    function __getOwnerRebookGraphWs($dayRangeType="Today",$salon_id)
        { 
            
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
               return $this->OwnerWebServices_model
                       ->getRebookWebService($whereCondition,$where,$groupby)
                       ->result_array();
            }           
        }
    /**
     * This function for get owner Client Services Internal graphs
     * @param type $dayrangetype
    */
    function __getOwnerClientServicedGraphWs($dayRangeType="Today",$salon_id)
        { 
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];

                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;
                if($dayRangeType=='Yearly'){
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'YEAR(`start_date`)' => date('Y'));
                    $where ='';
                    $groupby = 'MONTH(`start_date`)';
                }elseif ($dayRangeType=='Last90days') {
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => 'Yearly');
                    $where = "(start_date = '".$this->lastOneMonthstartDate."' or start_date = '".$this->lastTwoMonthsstartDate."' or start_date = '".$this->lastThreeMonthsstartDate."')";
                    $groupby = 'YEAR(`start_date`),MONTH(`start_date`)';
                }else{
                   
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                    {
                        $startDayOfTheWeekArr= getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else
                    {
                         $startDayOfTheWeekArr= getLast4WeekRanges(date('Y'));
                    }
                    $where = "";
                    $start_dateArr = array_column($startDayOfTheWeekArr, "start_date");
                    $end_dateArr = array_column($startDayOfTheWeekArr, "end_date");
                    $where = 'start_date in ("' . implode('", "', $start_dateArr) . '") and end_date in ("' . implode('", "', $end_dateArr) . '")';
                    $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType); 
                    $groupby = '';
                }
                return $this->OwnerWebServices_model
                       ->getClientServicedWebService($whereCondition,$where,$groupby)
                       ->result_array();
            }     
        }
    /**
     * This function for get owner Client Services Internal graphs
     * @param type $dayrangetype
    */
    function __getOwnerTotalSalesGraphWs($dayRangeType="Today",$salon_id)
        { 
           
            if(isset($salon_id) && $salon_id!=''){
                $salonDetails = $this->Common_model->getSalonInfoBy($salon_id);
                $salonData = $salonDetails['salon_info'];
                $salon_name = $salonData['salon_name'];
                       
                $startDayOfTheWeek =  isset($salonData["salon_start_day_of_week"]) && !empty($salonData["salon_start_day_of_week"]) ? $salonData["salon_start_day_of_week"] : '';
                $dataArray['location_id'] = $salon_id;
                $dataArray['location_name'] = $salon_name;


                        /*$currentDate = date("Y-m-d");
                        if($dayRangeType=='Yearly'){
                           $this->startDate = date("Y-")."01-01";
                           $this->endDate = $currentDate;
                        }elseif ($dayRangeType=='Last90days') {
                            $LastMonthFirst = date("Y-m-d", strtotime("first day of last month"));
                            $this->startDate = date("Y-m-d", strtotime($LastMonthFirst. " -2 months"));
                            $this->endDate = date("Y-m-d", strtotime("last day of last month"));
                        }else{
                           $this->startDate = date("Y-m-")."01";
                           $this->endDate = $currentDate;
                        }
                        if($dayRangeType=='Last90days'){
                            $dayRangeType = 'threemonths';
                        }*/
                        /*$reportsWhere = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'start_date' => $this->startDate,'end_date' => $this->endDate);*/
                    if($dayRangeType=='Last90days'){
                        $dayRangeType='threemonths';
                    }
                    $reportsWhere = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType);
                   return $this->OwnerWebServices_model
                           ->getTotalSalesWebService($reportsWhere)
                           ->result_array();
             }         
        }    
 }       