<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class StaffAvgRpctDataForGraphs extends CI_Controller
{
    
    // Define constant as per value;
    CONST INSERTED = 0;
    CONST UPDATED = 1; 
    CONST RPCTBASEDONSALON = 234;
    
    public $salonAccountId;
    public $startDate;
    public $endDate;
    
    public $staffCalcData;
    public $salonID;
    
    public $currentDate;
    
    private $salonsInfo;
    private $dayRangeType; 
    
    private $__insideConfigArr;
    
    public  $colorFieldsArr = array('color','highlight','Retouch','Hi-Lites','Lo-Lites','Minking','Foils','Virgin','Single Process','Crown Highlight','Partial Highlight','Double Process','Glaze','Base Softening','Highlights','Frosting','Balayage','Special Effects','Colors','Coloring','Chemical','Hilite','Hilites','Hilight','High','Perm','Relaxer','Color Retouch','Full Highlight','Custom Color','Permanent Wave');
      
    private $insideFormulaCalcuation ;
    private $_staffCalcData;
    

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('ColorPercentage_model');      
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
    }
    
    /**
     * Default Index Fn
     */    
	public function index(){ print "Test";}
    
    /**
     * 
     * @param type $dayRangeType
     * @param type $s
     * @param type $e
     */
    public function __getStartEndDate($dayRangeType, $year = 1 ,$s = '', $e = '')
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
                        case YEARLY:
                                    $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                                    $this->endDate = $this->currentDate;
                            break;
                        case CURRENTMONTH:
                                    $this->startDate = date("Y-m-")."01";
                                    $this->endDate = $this->currentDate;
                            break;
                        case LASTYEAR:
                                    $this->startDate = date("Y-01-01", strtotime("-".$year." year"));// get start date from here
                                    $this->endDate = date("Y-12-t", strtotime($this->startDate));
                            break;
                        case PREVIOUSMONTHS:                                   
                                    $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                                    $this->endDate = getDateFn(strtotime("last day of last month"));
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
    
    public function __make_like_conditions ($fields, array $query)
    {
        $likes = array();
        foreach ($query as $match) {
            $likes[] = "$fields LIKE '%$match%'";
        }
        return '('.implode(' || ', $likes).')';
    }
        
        
    private function __getInsideSqlQueryFn($salonAccountNo, $temp)
    {
        /***
        * Monthly-Weeakly Condition for common query structure
        */
        
        if($this->dayRangeType ==  MONTHLY)
        {
            $sqlCommonWeekCond = "tdatetime >='".$temp['startDayOfWeek']."' AND tdatetime<='".$temp['endDayOfWeek']."'";
        }
        else{
            $sqlCommonWeekCond = "MONTH(`tdatetime`) ='".$temp['month']."' AND YEAR(`tdatetime`) ='".$temp['year']."'";
        }
        
        // pa($sqlCommonWeekCond,'$sqlCommonWeekCond');
        // Total service sales with refunds
        $whereServiceSalesCondition = array( 'account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"]);
        $serviceSalesDetailsArrNew =  $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,count(DISTINCT iclientid) as unique_client_count, SUM(nprice * nquantity) as nprice , SUM(nquantity) as nquantity')
                                                ->where($sqlCommonWeekCond)
                                                ->get_where(MILL_SERVICE_SALES,$whereServiceSalesCondition )
                                                ->row_array();
        
        $this->__insideConfigArr['serviceSalesDetailsArrNew'.$temp['strYearType']] = !empty($serviceSalesDetailsArrNew) ? $serviceSalesDetailsArrNew : array();
       // pa($this->db->last_query(),'NEWserviceSalesDetailsArrNew');
        
        
        // Total service sales without refunds
        $whereServiceSalesConditionWithoutRefund = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"],'lrefund' => 'false');
        $serviceSalesWithoutRefund =  $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,count(DISTINCT iclientid) as unique_client_count, SUM(nprice * nquantity) as nprice , SUM(nquantity) as nquantity')
                                                ->where($sqlCommonWeekCond)
                                                ->get_where(MILL_SERVICE_SALES,$whereServiceSalesConditionWithoutRefund )
                                                ->row_array();
        
        $this->__insideConfigArr['serviceSalesWithoutRefund'.$temp['strYearType']] = !empty($serviceSalesWithoutRefund) ? $serviceSalesWithoutRefund : array();
       // pa($this->db->last_query(),'$serviceSalesWithoutRefund');
        
        
        // Total Product sales
        $whereProducteSalesCondition = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"]);
        
        $productSalesDetailsArr =  $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count, SUM(nprice * nquantity) as nprice , SUM(nquantity) as nquantity')
                                                ->where($sqlCommonWeekCond)    
                                                ->get_where(MILL_PRODUCT_SALES,$whereProducteSalesCondition )
                                                ->row_array();
       
        $this->__insideConfigArr['productSalesDetailsArr'.$temp['strYearType']] = !empty($productSalesDetailsArr) ? $productSalesDetailsArr : array();
         //pa($this->db->last_query(),'NEWserviceSalesDetailsArrNew');
        
        
        //RPCT CALCULATION
        $whereProductSalesWithoutRefundCondition = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"],'lrefund' => 'false');
        $productSalesWithoutRefundArr =  $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,count(DISTINCT iclientid) as unique_client_count, SUM(nquantity) as nquantity,SUM(nprice * nquantity) as nprice')
                                                    ->where($sqlCommonWeekCond)
                                                    ->get_where(MILL_PRODUCT_SALES, $whereProductSalesWithoutRefundCondition)
                                                    ->row_array();
       
        $this->__insideConfigArr['productSalesWithoutRefundArr'.$temp['strYearType']] = !empty($productSalesWithoutRefundArr) ? $productSalesWithoutRefundArr : array();
       // pa($this->db->last_query(),'serviceSalesDetailsArrRepeat');                    
                
       
        //ONLY COLOR SERVICES TOTAL PRICE, INVOICE COUNT 
        
        // $this->colorLikeStr = $this->__make_like_conditions('cservicedescription', $this->colorFieldsArr);
        
        // $whereColorServiceSalesCondition = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"]);
        // $colorserviceSalesDetailsArr =  $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice')
        //                                             ->where($sqlCommonWeekCond)
        //                                             ->where($this->colorLikeStr)    
        //                                             ->get_where(MILL_SERVICE_SALES, $whereColorServiceSalesCondition)
        //                                             ->row_array();
         $this->colorLikeStr = $this->ColorPercentage_model->getColorLIkeConditionsArrayString($salonAccountNo);
           //pa($this->colorLikeStr,'colorLikeStr');
           $whereColorServiceSalesCondition = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"]);
           $colorserviceSalesDetailsArr =  $this->DB_ReadOnly->select('SUM(nquantity) as nprice')
                                                    ->where($sqlCommonWeekCond)
                                                    ->where($this->colorLikeStr)    
                                                    ->get_where(MILL_SERVICE_SALES, $whereColorServiceSalesCondition)
                                                    ->row_array();
       
        $this->__insideConfigArr['colorserviceSalesDetailsArr'.$temp['strYearType']] = !empty($colorserviceSalesDetailsArr) ? $colorserviceSalesDetailsArr : array();
        
        //pa($this->db->last_query(),'colorserviceSalesDetailsArr');
        
        
        /**
         Get RPCT Sales Unique clients
         */   
            $whereServiceClientIdsCondition = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"],'lrefund' => 'false');
            $serviceSalesClientIds=  $this->DB_ReadOnly->select('DISTINCT(iclientid) as service_client_ids')
                                            ->where($sqlCommonWeekCond)
                                            ->get_where(MILL_SERVICE_SALES, $whereServiceClientIdsCondition)
                                            ->result_array();
            
            $this->__insideConfigArr['uniqueServiceSalesClientIdArr'.$temp['strYearType']] = ($serviceSalesClientIds)? array_column($serviceSalesClientIds, "service_client_ids") : array();
            
         /**
         Get RPCT Retails Unique clients
         */ 
            $whereRetailClientIdsCondition = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"],'lrefund' => 'false');
            $retailSalesClientIds=  $this->DB_ReadOnly->select('DISTINCT(iclientid) as retail_client_ids')
                                            ->where($sqlCommonWeekCond)
                                            ->get_where(MILL_PRODUCT_SALES, $whereRetailClientIdsCondition)
                                            ->result_array();
            
            $this->__insideConfigArr['uniqueRetailSalesClientIdArr'.$temp['strYearType']] = ($retailSalesClientIds)? array_column($retailSalesClientIds, "retail_client_ids") : array();  
         
         
        return $this->__insideConfigArr;
    }
    
   
        
    public function getAvgRpctReports($temp)
		{
            $this->staffCalcData = array();
			$this->insideFormulaCalcuation = array();
            
			//if(!empty($temp["salon_id"]) && !empty($temp["staff_iid"]) && !empty($temp["staff_iids"]) && !empty($temp["date_range_type"]) && !empty($temp["start_date"]) && !empty($temp["end_date"]))
            if(!empty($temp["salon_id"]) && !empty($temp["staff_iid"]) && !empty($temp["date_range_type"]) && !empty($temp["start_date"]) && !empty($temp["end_date"]))
			{
                $salonDetailsArr = $this->Common_model
                                   ->getMillSdkConfigDetailsBy($temp["salon_id"])
                                   ->row_array();
                
                //$staffIIdsArr = (!empty($temp["staff_iids"]) && strpos($temp["staff_iids"], ',') !== false) ? explode(",", $temp["staff_iids"]) : array($temp["staff_iids"]) ;
				
				$this->salonID = isset($temp["salon_id"]) ? $temp["salon_id"]: '';
                
                
                $salonAccountNo = isset($salonDetailsArr['salon_account_id']) ? $salonDetailsArr['salon_account_id']: '';
                $this->salonAccountId = $salonAccountNo;
                
                //TO GET EMPLOYEE DATA 
        		/*$getEmployeeDetailsArr = $this->DB_ReadOnly->where(array('account_no' =>$salonAccountNo))
                                                ->where_in('iid', $staffIIdsArr)
                                                ->get(MILL_EMPLOYEE_LISTING)
                                                ->row_array();*/
                
                
                /*************GETING CURRETNT YEAR AND LAST YEAR FOR LOPP************/
                $yearArr = array();
                $Yinterval = new DateInterval('P1Y');

                $dateTime = new DateTime($this->startDate);
                $yearArr['thisYear'] = $dateTime->format('Y');

                $dateTime = new DateTime($this->startDate);
                $yearArr['lastYear'] = $dateTime->sub($Yinterval)->format('Y');

                /***************************/
           
                switch ($this->dayRangeType)
                {
                    case PREVIOUSMONTHS:
                    case LASTYEAR:
                    case CURRENTMONTH:    
                    case YEARLY: 
                            $beginDateObj = new DateTime($temp["start_date"]);
                            $endDateObj = new DateTime($temp["end_date"]);
                            $intervalDateObj = new DateInterval('P1M');
                            $daterangeDateObj = new DatePeriod($beginDateObj, $intervalDateObj ,$endDateObj);
                            foreach ($yearArr as $strYearType => $intYear)
                            {
                                foreach ($daterangeDateObj as $key => $date)
                                    {
                                        $month = ltrim($date->format("m"), '0');
                                        $monthName = $date->format("F");
                                        $year = $intYear;
                                        
                                        //pa( '','Loop'.'-'.$monthName.'-'.$intYear);
                                        
                                        $startDayOfMonth = $date->format($intYear."-m-d");
                                        $endDayOfMonth = $date->format($intYear."-m-t");
                                        
                                        if(strtotime($endDayOfMonth) > strtotime($this->currentDate)) {
                                            $endDayOfMonth = $this->currentDate; 
                                        }
                                        else { 
                                            $endDayOfMonth = $date->format($intYear."-m-t"); 
                                        }
                                        
                                        $temp['month'] =  $month;
                                        $temp['year'] =  $year;
                                        $temp['strYearType'] =  $strYearType;
                                        
                                        $this->__getInsideSqlQueryFn($salonAccountNo, $temp);
                                        
                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Services'] =  $this->__insideConfigArr['serviceSalesDetailsArrNew'.$strYearType];
                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Service_without_refund'] =  $this->__insideConfigArr['serviceSalesWithoutRefund'.$strYearType];    
                       
                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Retail'] =  $this->__insideConfigArr['productSalesDetailsArr'.$strYearType];
                                        
                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Rpct_data'] =  $this->__insideConfigArr['productSalesWithoutRefundArr'.$strYearType];
                                        
                                       
                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Color_service'] =  $this->__insideConfigArr['colorserviceSalesDetailsArr'.$strYearType];

                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'uniqueRetailSalesClientIdArr'] =  $this->__insideConfigArr['uniqueRetailSalesClientIdArr'.$strYearType];
                                        
                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'uniqueServiceSalesClientIdArr'] =  $this->__insideConfigArr['uniqueServiceSalesClientIdArr'.$strYearType];
                                        
                                        $this->insideFormulaCalcuation[$monthName][$strYearType]['start_date'] =  $startDayOfMonth;    
                                        $this->insideFormulaCalcuation[$monthName][$strYearType]['end_date'] =  $endDayOfMonth;    
                                        
						            }
                                    
                                    //Calculation as per formula for this and last year of current month  
                                     $tempArr = $this->__calculateAsPerFormulaForSalon($strYearType,'Yearly');
                                     
                            }
                        $this->staffCalcData["avgTicket"] = $tempArr;
                        // pa($this->staffCalcData["avgTicket"],'insideFormulaCalcuation'); 
                  		break;
                    case MONTHLY:
                            
                            foreach ($yearArr as $strYearType => $intYear)
                            {
                                $i = 1;
                                
                                $startDayOfTheWeek = $temp['startDayOfTheWeek'];
                                if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek)){
                                    $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,$intYear);
                                }
                                else{
                                    $fourWeeksArr = getLast4WeekRanges($intYear);
                                }
                                
                                foreach ($fourWeeksArr as $key => $date)
                                    {
                                        $startDayOfWeek = $date['start_date'];
                                        $endDayOfWeek = $date['end_date'];
                                        $current_week = $date['current_week'];
                                        
                                        $weekNumber = "Week ".$i;
                                         
                                        //pa($startDayOfWeek.' To '.$endDayOfWeek,'Loop');
                                   
                                       
                                        $temp['strYearType'] =  $strYearType;
                                        $temp['startDayOfWeek'] =  $startDayOfWeek;
                                        $temp['endDayOfWeek'] = $endDayOfWeek;
                                        
                                        $this->__getInsideSqlQueryFn($salonAccountNo, $temp);
                       
                                       
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Services'] =  $this->__insideConfigArr['serviceSalesDetailsArrNew'.$strYearType];
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Service_without_refund'] =  $this->__insideConfigArr['serviceSalesWithoutRefund'.$strYearType];    
                       
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Retail'] =  $this->__insideConfigArr['productSalesDetailsArr'.$strYearType];
                                        
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Rpct_data'] =  $this->__insideConfigArr['productSalesWithoutRefundArr'.$strYearType];
                                       
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Color_service'] =  $this->__insideConfigArr['colorserviceSalesDetailsArr'.$strYearType];
                                        
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'uniqueRetailSalesClientIdArr'] =  $this->__insideConfigArr['uniqueRetailSalesClientIdArr'.$strYearType];
                                        
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'uniqueServiceSalesClientIdArr'] =  $this->__insideConfigArr['uniqueServiceSalesClientIdArr'.$strYearType];
                                        

                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType]['start_date'] =  $startDayOfWeek;    
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType]['end_date'] =  $endDayOfWeek;    
                                        $i++;
                                    }
                                   
                                    //Calculation the prebook and rebook for this and last year of current month  
                                    $tempArr = $this->__calculateAsPerFormulaForSalon($strYearType,$this->dayRangeType);
                            }
                        
                        $this->staffCalcData["avgTicket"] = $tempArr;
                  		$this->staffCalcData["start_date"] = $temp["start_date"];
              			$this->staffCalcData["end_date"] = $temp["end_date"];    
                        //pa($this->insideFormulaCalcuation,'insideFormulaCalcuation');         
                        //pa($this->staffCalcData,'staffCalcData');         
                        break;    
                    case "MONTHLYWITHPHPWEEKS":
                   
                            $beginDateObj = new DateTime($temp["start_date"]);
                            $endDateObj = new DateTime($temp["end_date"]);
                            $intervalDateObj = new DateInterval('P1W');
                            $daterangeDateObj = new DatePeriod($beginDateObj, $intervalDateObj ,$endDateObj);   
                        
                            foreach ($yearArr as $strYearType => $intYear)
                            {
                                $i = 1;
                                foreach ($daterangeDateObj as $key => $date)
                                    {
                      
                                        $week = $date->format("W");
                                        $weekNumber = "Week ".$i;
                                    
                                        $month = ltrim($date->format("m"), '0');
                                        $monthName = $date->format("F");
                                        $year = $intYear;
                                    
                                        if($month==1 && ($week==52 || $week==53))
                                            {
                                                $week = 1;
                                            }
                                            else
                                            {
                                                $week = $week;
                                            }
                                            
                                        //pa( '','Loop'.'-'.$monthName.'-'.$intYear);
                                        
                                        $startDayOfWeek = $date->format($intYear."-m-d");
                                        $end_day_of_this_week = strtotime($date->format("Y-m-d").' +6 days');
                                        $endDayOfWeek = date($intYear."-m-d", $end_day_of_this_week);
                                        
                                        $temp['month'] =  $month;
                                        $temp['year'] =  $year;
                                        $temp['WeekNo'] = $week;
                                        $temp['strYearType'] =  $strYearType;
                                        
                                        $this->__getInsideSqlQueryFn($salonAccountNo, $temp);
                       
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Services'] =  $this->__insideConfigArr['serviceSalesDetailsArrNew'.$strYearType];
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Service_without_refund'] =  $this->__insideConfigArr['serviceSalesWithoutRefund'.$strYearType];    
                       
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Retail'] =  $this->__insideConfigArr['productSalesDetailsArr'.$strYearType];
                                        
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Rpct_data'] =  $this->__insideConfigArr['productSalesWithoutRefundArr'.$strYearType];
                                       
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Color_service'] =  $this->__insideConfigArr['colorserviceSalesDetailsArr'.$strYearType];

                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType]['start_date'] =  $temp["start_date"];    
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType]['end_date'] =  $temp["end_date"];    
                                        $i++;
						            }
                                   
                                    //Calculation the prebook and rebook for this and last year of current month  
                                    $tempArr = $this->__calculateAsPerFormulaForSalon($strYearType,$this->dayRangeType);
                            }
                        
                        $this->staffCalcData["avgTicket"] = $tempArr;
                  		$this->staffCalcData["start_date"] = $temp["start_date"];
              			$this->staffCalcData["end_date"] = $temp["end_date"];    
                        //pa($this->staffCalcData,'Monthly');         
                        break;
                    default :
                        break;
                        
                }
            }
			else
			{
				//IF POST VALUES ARE EMPTY
                exit('Please check the parameters.');
			}
        }
    
    private function __calculateAsPerFormulaForSalon($strYearType,$Key = '')
    {
       // pa($this->insideFormulaCalcuation,'__calculatePrebookAndRebook');
        //Calculation the prebook and rebook for this and last year of current month  
            if(!empty($this->insideFormulaCalcuation))
                {
                    $tempArr = array();
                    foreach($this->insideFormulaCalcuation as $month_week_name => $dayRanges)
                    {
                        if($strYearType == 'thisYear'){ $strYear =  '';}
                        if($strYearType == 'lastYear'){ $strYear =  'last_year_';}

                        $servicesArr = $dayRanges[$strYearType.'Services'];
                        $retailArr = $dayRanges[$strYearType.'Retail'];
                        $serviceWithoutRefundArr = $dayRanges[$strYearType.'Service_without_refund'];
                        $rpctArr = $dayRanges[$strYearType.'Rpct_data'];
                        $colorServiceArr = $dayRanges[$strYearType.'Color_service'];
                        
                        $uniqueServiceSalesClientIdArr = $dayRanges[$strYearType.'uniqueServiceSalesClientIdArr'];
                        $uniqueRetailSalesClientIdArr = $dayRanges[$strYearType.'uniqueRetailSalesClientIdArr'];
                        
                        
                        //AVG RETAIL TICKET    
                        $this->_staffCalcData[$month_week_name][$strYear."avg_rpct"] = (isset($retailArr["nprice"]) && isset($rpctArr["invoice_count"]) && !empty($retailArr["nprice"]) && !empty($rpctArr["invoice_count"])) ? $this->Common_model->appCloudNumberFormat(($retailArr["nprice"]/($rpctArr["invoice_count"])),2) : '0.00' ;
                        
                        //AVG SERVICE TICKET
                        $this->_staffCalcData[$month_week_name][$strYear."avg_ser_ticket_value"] = (isset($servicesArr["nprice"]) && isset($serviceWithoutRefundArr["invoice_count"]) && !empty($servicesArr["nprice"]) && !empty($serviceWithoutRefundArr["invoice_count"])) ? $this->Common_model->appCloudNumberFormat(($servicesArr["nprice"]/($serviceWithoutRefundArr["invoice_count"])),2) : '0.00' ;
                         
                        //RPST
				        $this->_staffCalcData[$month_week_name][$strYear."RPST"] = (isset($retailArr["nprice"]) && isset($serviceWithoutRefundArr["invoice_count"]) && !empty($retailArr["nprice"]) && !empty($serviceWithoutRefundArr["invoice_count"])) ? $this->Common_model->appCloudNumberFormat(($retailArr["nprice"]/($serviceWithoutRefundArr["invoice_count"])),2) : '0.00' ;   
                            
                        
                        //RPCT Total unique client count
                        //Get total unique clients
                        $serviceClientInvoiceCount = count($uniqueServiceSalesClientIdArr);

                        //GET TOTAL CLIENTS TICKET BY THE STYLIST
                        //Get CLient TICKETS
                        $retailClientInvoiceCount = count($uniqueRetailSalesClientIdArr);


                        $commonClientsInvoiceCount = count(array_intersect($uniqueServiceSalesClientIdArr, $uniqueRetailSalesClientIdArr));


                        $totalUniqueClients = ($serviceClientInvoiceCount - $commonClientsInvoiceCount) + ($retailClientInvoiceCount - $commonClientsInvoiceCount) + $commonClientsInvoiceCount;
                        

                        $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($this->salonID)->row_array();
                        $rpct_type = $salonDetails['rpct_type']; 
                            
						//RPCT
                        /*if(!empty($temp["salon_id"]) && $temp["salon_id"] == StaffAvgRpctDataForGraphs::RPCTBASEDONSALON)*/
                        if($rpct_type ==2)
                        {       
                                $this->_staffCalcData[$month_week_name][$strYear."RPCT"] = (isset($retailArr["nprice"]) && isset($totalUniqueClients) && !empty($retailArr["nprice"]) && !empty($totalUniqueClients)) ? 
                                     round($retailArr["nprice"]/$totalUniqueClients, 2): '0.00';  
                        }
                        else {
                                $this->_staffCalcData[$month_week_name][$strYear."RPCT"] = (isset($retailArr["nprice"]) && isset($rpctArr["invoice_count"]) && !empty($retailArr["nprice"]) && !empty($rpctArr["invoice_count"])) ? 
                                    round($retailArr["nprice"]/$rpctArr["invoice_count"], 2): '0.00'; 
                        }
                    
                        //COLOR PERCENTAGE
	                    $this->_staffCalcData[$month_week_name][$strYear."color_percentage"] = (isset($colorServiceArr["nprice"]) && isset($servicesArr["nquantity"]) && !empty($colorServiceArr["nprice"]) && !empty($servicesArr["nquantity"])) ? $this->Common_model->appCloudNumberFormat(($colorServiceArr["nprice"]/($servicesArr["nquantity"]))*100,2) : '0.00' ;
                        
                    if( $strYearType == 'thisYear')
                     {
                         $this->_staffCalcData[$month_week_name]["start_date"] = (isset($dayRanges[$strYearType]['start_date'])) ? $dayRanges[$strYearType]['start_date'] : '' ;

                         $this->_staffCalcData[$month_week_name]["end_date"] = (isset($dayRanges[$strYearType]['end_date'])) ? $dayRanges[$strYearType]['end_date'] : '' ; 
                     }

                    $this->_staffCalcData[$month_week_name]["dayRangeType"] = (!empty($Key)) ? $Key : '' ; 
                    
                    if( MONTHLY == $this->dayRangeType) {
                           $this->_staffCalcData[$month_week_name]["key"] =  $month_week_name;     
                        }
                        else {
                           $this->_staffCalcData[$month_week_name]["key"] = substr($month_week_name, 0, 3);
                        }
                     
                    }
                }
       return $this->_staffCalcData;
    }
        
    /**
     * 
     * @param type $day
     * @param type $para_salon_id
     */
    function setStaffRpctInternalGraph($dayRangeType="Today",$account_no="",$year= 1)
		{

            if($account_no!=''){
             $account_no = salonWebappCloudDe($account_no);
             $salon_config_details = $this->Common_model->getMillSdkConfigDetails($account_no)->row_array();
             $salon_id = $salon_config_details['salon_id'];
            }else{
                $salon_id='';
            }
            //echo $salon_id;exit;
            $this->currentDate = getDateFn();
		    // GET START DATE AND END DATE AS PER PARAMETERS
            $this->__getStartEndDate($dayRangeType, $year);
            $this->salonsInfo = $this->Common_model->getAllSalons($salon_id);
            //print_r($this->salonsInfo );exit;
			if(isset($this->salonsInfo["mill_salons"]) && !empty($this->salonsInfo["mill_salons"]))
			{
               foreach($this->salonsInfo["mill_salons"] as $salonsData)
				{
                    
                    $salonDetails = $this->Common_model->getSalonInfoBy($salonsData['salon_id']);
                    
                    // Database Log
                    $log['AccountNo'] = $salonDetails['salon_info']['salon_code'];
                    $log['salon_id'] = $salonDetails['salon_info']['salon_id'];
                    $log['StartingTime'] = date('Y-m-d H:i:s');
                    $log['whichCron'] = 'setStaffRpctInternalGraph';
                    //$log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $log['CronUrl'] = MAIN_SERVER_URL.'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/'.$dayRangeType.'/'.$salon_id;
                    
                    $log['CronType'] = 1;
                    $log['id'] = 0;
                    $log_id = $this->Common_model->saveMillCronLogs($log);      
                    
                        
                    pa('',"Salon Reports Cron Dashboard--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                    //Get the all staff members by salon id
                    $getAllStaff = $this->Common_model->getAllStaffMembersBySalon($salonsData["salon_id"]);
                    //print_r($getAllStaff);exit; 
					if(isset($getAllStaff["getAllStaff"]) && !empty($getAllStaff["getAllStaff"]))
					{
						foreach($getAllStaff["getAllStaff"] as $staffMembers)
						{
							/*$salonStaffIds = array();
							$salonStaffIds["salon_id"] = $salonsData["salon_id"];
							$salonStaffIds["staff_id"] = $staffMembers["staff_id"];

							$salonStaffResultArray  = $this->Common_model->getMillAppointmentAndSalonInfo($salonStaffIds);
  
                            pa('','LoopStaff-----'.$salonStaffIds["staff_id"]);*/
                            
                            $temps = array();
						 	$temps['staff_id'] = isset($staffMembers["staff_id"])? $staffMembers["staff_id"] : '';
						 	$temps['staff_iid'] = isset($staffMembers["emp_iid"])? $staffMembers["emp_iid"] : 0;
						 	
                            //$temps['staff_iids'] = isset($salonStaffResultArray["emp_iids"])? $salonStaffResultArray["emp_iids"] : '';
						 	
                            //$temps['staff_name'] = isset($salonStaffResultArray["staff_name"])? $salonStaffResultArray["staff_name"] : '';
						 	
                            $temps['salon_id'] = $salonsData["salon_id"];
						 	$temps['start_date'] = $this->startDate;
						 	$temps['end_date'] = $this->endDate;
                            $temps["date_range_type"] = $this->dayRangeType ;
                            
                            $temps['startDayOfTheWeek'] =  isset($salonsData["salon_start_day_of_week"]) && !empty($salonsData["salon_start_day_of_week"]) ? $salonsData["salon_start_day_of_week"] : '';
                            
                           // GET CALCULATE VALEUES AS PER FORMULAS 
                            $this->getAvgRpctReports($temps);
                       
                            if(isset($this->staffCalcData["avgTicket"]) && !empty($this->staffCalcData["avgTicket"]))
							{
								//JSON DECODE FOR EACH LOOP STARTS
								foreach($this->staffCalcData["avgTicket"] as $resValue)
								{
                                    $insertDataArray["salon_id"] = $salonsData["salon_id"];
									$insertDataArray["staff_id"] = $staffMembers["staff_id"];
									$insertDataArray["dayRangeType"] = $resValue["dayRangeType"];
									$insertDataArray["start_date"] = $resValue['start_date'];
									$insertDataArray["end_date"] = $resValue['end_date'];
                                    
									$insertDataArray["avg_rpct"] = $resValue["avg_rpct"];
									$insertDataArray["avg_ser_ticket_value"] = $resValue["avg_ser_ticket_value"];
									$insertDataArray["key"] = $resValue["key"];
									$insertDataArray["RPCT"] = $resValue["RPCT"];
									$insertDataArray["color_percentage"] = $resValue["color_percentage"];
                                    $insertDataArray["last_year_avg_rpct"] = $resValue["last_year_avg_rpct"];
									$insertDataArray["last_year_avg_ser_ticket_value"] = $resValue["last_year_avg_ser_ticket_value"];
									$insertDataArray["last_year_RPCT"] =$resValue["last_year_RPCT"];
									$insertDataArray["last_year_color_percentage"] =$resValue["last_year_color_percentage"];
                                    
									
                                    $yearRes = (new DateTime($resValue['start_date']))->format("Y");
                                    $monthRes = (new DateTime($resValue['start_date']))->format("m");
                                    
                                    $insertDataArray['RPCT'] = number_format($insertDataArray['RPCT'], 2, '.', '');
									$insertDataArray['last_year_RPCT'] = number_format($insertDataArray['last_year_RPCT'], 2, '.', '');
									$reportsWhere = array('salon_id' => $salonsData["salon_id"], 'staff_id' => $staffMembers["staff_id"],'dayRangeType' =>  $resValue["dayRangeType"],'key' => $resValue["key"],"MONTH(`start_date`)" => $monthRes, 'YEAR(`start_date`)' => $yearRes );
                                    if(MONTHLY == $this->dayRangeType){
                                            $reportsWhere = array('salon_id' => $salonsData["salon_id"], 'staff_id' => $staffMembers["staff_id"], 'dayRangeType' =>  $resValue["dayRangeType"],'key' => $resValue["key"]);
                                        }
                                       
									$reportsDataForSalon = $this->DB_ReadOnly->select('*')
                                                                    ->get_where(MILL_RPCT_CALCULATIONS_CRON,$reportsWhere)
                                                                    ->row_array();
                                  
                                    if(!empty($reportsDataForSalon))
								    {
                                        $diff_array = array_diff_assoc($insertDataArray,$reportsDataForSalon);
                                
                                        //pa($diff_array,"Diff Array");
                                        
								    	if(empty($diff_array))
								    	{
								    		pa("No Updates");
                                            continue;
								    	}
								    	else
								    	{
								    		//Update REPORT
                                            /*pa($insertDataArray,"insertDataArray");
                                            pa($reportsDataForSalon,"reportsDataForSalon");
                                            pa($diff_array,"diff_array");*/
								    		$diff_array["insert_status"] = StaffAvgRpctDataForGraphs::UPDATED;
											$diff_array["updatedDate"] = date("Y-m-d H:i:s");
								    		try {
											    $this->db->where('id', $reportsDataForSalon['id']);
									    		$this->db->update(MILL_RPCT_CALCULATIONS_CRON, $diff_array); 
												pa("Reports Data updated Successfully.");
											} catch (Exception $e) {
											    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
											}
								    	}
								    }
								    else
								    {
                                        	//INSERT REPORT
									    	$insertDataArray["insert_status"] = StaffAvgRpctDataForGraphs::INSERTED;
											$insertDataArray["insertedDate"] = date("Y-m-d H:i:s");
											$insertDataArray["updatedDate"] = date("Y-m-d H:i:s");
											try {
												$this->db->insert(MILL_RPCT_CALCULATIONS_CRON, $insertDataArray);
                                                pa("Reports Data inserted Successfully.");
									    	} catch (Exception $e) {
												echo 'Reports Insert failed: ' . $e->getMessage()."<br>";
											}
                                    }
								} 
							}
                       
                       } //STAFF for loop ends.
					}
					else
					{
						echo "No Staff Available.";
					}
                    
                    // Database Log
                    $log['id'] = $log_id;
                    $log_id = $this->Common_model->saveMillCronLogs($log);   
				}
			}
			else
			{
				echo "No Salons Found.";
			}
		}
    
        

        private function __leaderBoardRetailPerService($temp)
        {
            //RETAIL PER SERVICE PER CURRENT DAY OF NEWLY ADDED SERVICE AND RETAIL
                $checkedOutClientId = !empty($temp['checked_out_client_id']) ? $temp['checked_out_client_id'] : '';
            
				// sql need to update not correct
				$sql_get_retail_Of_Client = "SELECT count(DISTINCT retail.cinvoiceno) as invoice_count, sum(retail.nprice*retail.nquantity) as total_retail,client.Name FROM ".MILL_PRODUCT_SALES." retail 
					join ".MILL_CLIENTS_TABLE." client on client.ClientId=retail.iclientid 
					WHERE 
					retail.iempid = '".$temp['staff_iid']."' and 
					retail.account_no = '".$this->salonAccountId."' and 
					client.AccountNo = '".$this->salonAccountId."' and 
					retail.iclientid = '".$checkedOutClientId."' and 
					client.ClientId = '".$checkedOutClientId."' and 
					retail.tdatetime >= '".$this->currentDate."' and 
					retail.tdatetime <= '".$this->currentDate."' and 
					retail.lrefund = 'false'
					";
			
				$this->__insideConfigArr['retailperservice']['stylistRetail_result'] = $this->DB_ReadOnly->query($sql_get_retail_Of_Client)->result_array();

				$sql_get_service_Of_Client = "SELECT count(DISTINCT service.cinvoiceno) as invoice_count, sum(service.nprice*service.nquantity) as total_service,client.Name FROM ".MILL_SERVICE_SALES." service 
					join ".MILL_CLIENTS_TABLE." client on client.ClientId=service.iclientid 
					WHERE 
					service.iempid = '".$temp['staff_iid']."' and 
					service.account_no = '".$this->salonAccountId."' and 
					client.AccountNo = '".$this->salonAccountId."' and 
					service.iclientid = '".$checkedOutClientId."' and 
					client.ClientId = '".$checkedOutClientId."' and 
					service.tdatetime >= '".$this->currentDate."' and 
					service.tdatetime <= '".$this->currentDate."' and 
					service.lrefund = 'false'
					";
			
				$this->__insideConfigArr['retailperservice']['stylistService_result'] = $this->DB_ReadOnly->query($sql_get_service_Of_Client)->result_array();

				
				if(!empty($this->__insideConfigArr['retailperservice']['stylistRetail_result']["total_retail"]) && $this->__insideConfigArr['retailperservice']['stylistRetail_result']["total_retail"]!=NULL  && !empty($this->__insideConfigArr['retailperservice']['stylistService_result']["total_service"]) && $this->__insideConfigArr['retailperservice']['stylistService_result']["total_service"]!=NULL)
				{
					$this->staffCalcData["retailPerServiceValue"] = number_format(($this->__insideConfigArr['retailperservice']['stylistRetail_result']['total_retail']/$this->__insideConfigArr['retailperservice']['stylistService_result']["total_service"])*100, 2, '.', '');
				}
				else
				{
					$this->staffCalcData["retailPerServiceValue"] = '0.00';
				}

				if(!empty($this->__insideConfigArr['retailperservice']['stylistRetail_result']['Name']))
				{
					$this->staffCalcData["client_name"] = $this->__insideConfigArr['retailperservice']['stylistRetail_result']['Name'];
				}
				else if(!empty($this->__insideConfigArr['retailperservice']['stylistService_result']['Name']))
				{
					$this->staffCalcData["client_name"] = $this->__insideConfigArr['retailperservice']['stylistService_result']['Name'];
				}
				else
				{
					$this->staffCalcData["client_name"] = "";
				}
        }
        
}

