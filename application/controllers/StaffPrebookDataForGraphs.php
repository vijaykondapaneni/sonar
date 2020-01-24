<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class StaffPrebookDataForGraphs extends CI_Controller
{
    
    // Define constant as per value;
    CONST INSERTED = 0;
    CONST UPDATED = 1;  
    
    public $salonAccountId;
    public $startDate;
    public $endDate;
    
    public $staffCalcData;
    
    public $currentDate;
    
    private $salonsInfo;
    private $dayRangeType; 
    
    private $__insideConfigArr;
    private $insideFormulaCalcuation ;
    private $_staffCalcData;
    

    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
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
        
        $whereServiceSalesCondition = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"],'lrefund' => 'false');
        
        $serviceSalesDetailsArrNew =  $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,count(DISTINCT iclientid) as unique_client_count, SUM(nprice * nquantity) as nprice , SUM(nquantity) as nquantity')
                                                ->where($sqlCommonWeekCond)
                                                ->get_where(MILL_SERVICE_SALES,$whereServiceSalesCondition )
                                                ->row_array();
        
        //pa($this->db->last_query(),'NEWserviceSalesDetailsArrNew');
        
        $this->__insideConfigArr['serviceSalesDetailsArrNew'.$temp['strYearType']] = !empty($serviceSalesDetailsArrNew) ? $serviceSalesDetailsArrNew : array();
        
        $whereServiceSalesConditionRepeat = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"],'lrefund' => 'false','lprebook' =>'true');
        $serviceSalesDetailsArrRepeat =  $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count,count(DISTINCT iclientid) as unique_client_count, SUM(nquantity) as nquantity,SUM(nprice * nquantity) as nprice')
                                                    ->where($sqlCommonWeekCond)
                                                    ->get_where(MILL_SERVICE_SALES, $whereServiceSalesConditionRepeat)
                                                    ->row_array();
        //pa($this->db->last_query(),'serviceSalesDetailsArrRepeat');
        $this->__insideConfigArr['serviceSalesDetailsArrRepeat'.$temp['strYearType']] = !empty($serviceSalesDetailsArrRepeat) ? $serviceSalesDetailsArrRepeat : array();

        
        //GET UNIQUE CLIENT COUNT BY DAY
        $whereServiceSalesUniqueClientIDsCondition = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"],'lrefund' => 'false');
        //$whereServiceSalesUniqueClientIDsCondition = array_merge($whereServiceSalesUniqueClientIDsCondition,$sqlCommonWeekCond);
		$getServiceSalesUniqueClientIdsCount = $this->DB_ReadOnly->select('DISTINCT (iclientid) as unique_client_count,tdatetime')
                                    //->group_by('tdatetime') 
                                    ->where($sqlCommonWeekCond)
                                    ->get_where(MILL_SERVICE_SALES, $whereServiceSalesUniqueClientIDsCondition)
                                    ->result_array();
                        
       // $this->__insideConfigArr['getServiceSalesUniqueClientIdsCount'.$temp['strYearType']] = !empty($getServiceSalesUniqueClientIdsCount) ? array_column($getServiceSalesUniqueClientIdsCount, "unique_client_count") : array();
        //pa($this->db->last_query(),'$getServiceSalesUniqueClientIdsCount');
        $this->__insideConfigArr['getUniqueClientIdsCountGroupByDatesArr'.$temp['strYearType']]['tempArr'] = ($getServiceSalesUniqueClientIdsCount)? $getServiceSalesUniqueClientIdsCount : array(); 
                
        return $this->__insideConfigArr;
    }
    
   
    /**
    * Get Future And Unique Client Count
    */
    private function __getFutureAndUniqueClientCount($startDayOfMonth,$endDayOfMonth,$strYearType)
    {
                $tempUniqueClientArr = array();
                foreach($this->__insideConfigArr['getUniqueClientIdsCountGroupByDatesArr'.$strYearType]['tempArr'] as $arr)
                {
                    $tempUniqueClientArr[$arr['tdatetime']][] = $arr['unique_client_count'];
                }
                
                unset($this->__insideConfigArr['getUniqueClientIdsCountGroupByDatesArr'.$strYearType]['tempArr']);
                
                $this->__insideConfigArr['getUniqueClientIdsCountGroupByDatesArr'.$strYearType] = $tempUniqueClientArr;
                    
                $begin = new DateTime($startDayOfMonth);
				$end = new DateTime($endDayOfMonth);
				$end = $end->modify( '+1 day' );
				$interval = new DateInterval('P1D');
				$daterange = new DatePeriod($begin, $interval ,$end);
                
                unset($this->__insideConfigArr['getServiceSalesUniqueClientIdsCount'.$strYearType]);
                 
				foreach($daterange as $datess){
				    $getUniqueClientIdsCountGroupByDatesArr = $this->__insideConfigArr['getUniqueClientIdsCountGroupByDatesArr'.$strYearType];
				    
				    $uniqueClientIdsJoined =  isset($getUniqueClientIdsCountGroupByDatesArr[$datess->format("Y-m-d")]) ? join(',',$getUniqueClientIdsCountGroupByDatesArr[$datess->format("Y-m-d")]):'';
                   
                    $this->__insideConfigArr['getServiceSalesUniqueClientIdsCount'.$strYearType][$datess->format("Y-m-d")] =  isset($getUniqueClientIdsCountGroupByDatesArr[$datess->format("Y-m-d")]) ? count($getUniqueClientIdsCountGroupByDatesArr[$datess->format("Y-m-d")]): 0;
				    $plusFourMonthsDate = date('Y-m-d',strtotime($datess->format("Y-m-d") . "+120 days"));
					
				    if(!empty($uniqueClientIdsJoined)){
				    	$sql_get_clients_serviced_count = $this->DB_ReadOnly->query("SELECT count(DISTINCT ClientId) as client_count FROM 
						".MILL_APPTS_TABLE."  
						WHERE 
						AccountNo = '".$this->salonAccountId."' and 
						SlcStatus != 'Deleted' and 
						str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$datess->format("Y-m-d")."' and 
						str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$plusFourMonthsDate."' and 
						DATE(  `MillCreatedDate` ) <=  '".$datess->format("Y-m-d")."' and 
						LPrebook =  'true' and 
						ClientId IN ($uniqueClientIdsJoined)")->row_array();
                      	
						$allClientCount[$datess->format("Y-m-d")] = $sql_get_clients_serviced_count["client_count"];
				    }
                    else $allClientCount[$datess->format("Y-m-d")] = 0;
					
				}
               
				$this->__insideConfigArr['totalFutureClientsCountSum'.$strYearType] = array_sum($allClientCount);
    }
        
    public function getRetailPerServiceReports($temp)
		{
            $this->staffCalcData = array();
			$this->insideFormulaCalcuation = array();
            
			if(!empty($temp["salon_id"]) && !empty($temp["staff_iid"]) && !empty($temp["date_range_type"]) && !empty($temp["start_date"]) && !empty($temp["end_date"]))
			{
                $salonDetailsArr = $this->Common_model
                                   ->getMillSdkConfigDetailsBy($temp["salon_id"])
                                   ->row_array();
                
                
                
                $salonAccountNo = isset($salonDetailsArr['salon_account_id']) ? $salonDetailsArr['salon_account_id']: '';
                $this->salonAccountId = $salonAccountNo;
                
                //TO GET EMPLOYEE DATA 
				$whereEmployeeCondition = array('account_no' => $salonAccountNo,'iid' => $temp["staff_iid"]);
                $getEmployeeDetailsArr = $this->Common_model->getEmployeeListing($whereEmployeeCondition)->row_array();
               
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
                       
                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Services']['New'] =  $this->__insideConfigArr['serviceSalesDetailsArrNew'.$strYearType];
                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Services']['Repeat'] =  $this->__insideConfigArr['serviceSalesDetailsArrRepeat'.$strYearType];    
                       
                                        //REBOOK CALCULATION 
                                        $this->__getFutureAndUniqueClientCount($startDayOfMonth,$endDayOfMonth,$strYearType);

                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Services']['rebook_deno'] =  array_sum($this->__insideConfigArr['getServiceSalesUniqueClientIdsCount'.$strYearType]); 

                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Services']['rebook_nume'] = $this->__insideConfigArr['totalFutureClientsCountSum'.$strYearType];
                                        
                                            $this->insideFormulaCalcuation[$monthName][$strYearType.'Services']['start_date'] =  $startDayOfMonth;    
                                            $this->insideFormulaCalcuation[$monthName][$strYearType.'Services']['end_date'] =  $endDayOfMonth;    
                                    }
                                    //Calculation the prebook and rebook for this and last year of current month  
                                     $tempArr = $this->__calculatePrebookAndRebook($strYearType,'Yearly');
                                     
                            }
                            
                        $this->staffCalcData["prebook_percentage"] = $tempArr;
                  		$this->staffCalcData["status"] = true;
                        //pa($this->insideFormulaCalcuation,'insideFormulaCalcuation');
                        //pa($this->staffCalcData,$this->dayRangeType);
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
                       
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Services']['New'] =  $this->__insideConfigArr['serviceSalesDetailsArrNew'.$strYearType];
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Services']['Repeat'] =  $this->__insideConfigArr['serviceSalesDetailsArrRepeat'.$strYearType];    
                       
                                        //REBOOK CALCULATION 
                                        $this->__getFutureAndUniqueClientCount($startDayOfWeek,$endDayOfWeek,$strYearType);

                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Services']['rebook_deno'] =  array_sum($this->__insideConfigArr['getServiceSalesUniqueClientIdsCount'.$strYearType]); 

                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Services']['rebook_nume'] = $this->__insideConfigArr['totalFutureClientsCountSum'.$strYearType];
                                        
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Services']['start_date'] =  $startDayOfWeek;    
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Services']['end_date'] =  $endDayOfWeek;    
                                        $i++;
						            }
                                   
                                    //Calculation the prebook and rebook for this and last year of current month  
                                    $tempArr = $this->__calculatePrebookAndRebook($strYearType,$this->dayRangeType);
                            }
                            
                        $this->staffCalcData["prebook_percentage"] = $tempArr;
                  		$this->staffCalcData["start_date"] = $temp["start_date"];
              			$this->staffCalcData["end_date"] = $temp["end_date"];
                        //pa($this->insideFormulaCalcuation,'insideFormulaCalcuation');         
                        //pa($this->staffCalcData,'staffCalcData'); 
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
    
    private function __calculatePrebookAndRebook($strYearType,$Key = '')
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

                        $servicesArray = $dayRanges[$strYearType.'Services'];

                        $this->_staffCalcData[$month_week_name][$strYear."prebook_value"] = (isset($servicesArray['Repeat']['unique_client_count']) && isset($servicesArray['New']['unique_client_count']) && ($servicesArray['Repeat']['unique_client_count']!= 0 || $servicesArray['New']['unique_client_count']!= 0)) ? $this->Common_model->appCloudNumberFormat(($servicesArray['Repeat']['unique_client_count']/($servicesArray['New']['unique_client_count']))*100,2) : '0.00' ;
                            
                        $this->_staffCalcData[$month_week_name][$strYear."rebook_value"] = (isset($servicesArray['rebook_deno']) && isset($servicesArray['rebook_nume']) && ($servicesArray['rebook_deno']!= 0 || $servicesArray['rebook_nume']!= 0)) ?$this->Common_model->appCloudNumberFormat(($servicesArray['rebook_nume']/($servicesArray['rebook_deno']))*100,2) : '0.00' ;
                        
                       if( $strYearType == 'thisYear')
                        {
                            $this->_staffCalcData[$month_week_name]["start_date"] = (isset($servicesArray['start_date'])) ? $servicesArray['start_date'] : '' ;

                            $this->_staffCalcData[$month_week_name]["end_date"] = (isset($servicesArray['end_date'])) ? $servicesArray['end_date'] : '' ; 
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
    function setStaffPrebookInternalGraph($dayRangeType="Today",$account_no="", $year= 1)
		{
            if($account_no!=''){
             $account_no = salonWebappCloudDe($account_no);
             $salon_config_details = $this->Common_model->getMillSdkConfigDetails($account_no)->row_array();
             $salon_id = $salon_config_details['salon_id'];
            }else{
                $salon_id='';
            }
           
            $this->currentDate = getDateFn();
			
            // GET START DATE AND END DATE AS PER PARAMETERS
            $this->__getStartEndDate($dayRangeType, $year ,'2017-03-01','2017-03-03');
            $this->salonsInfo = $this->Common_model->getAllSalons($salon_id);
            
            
			if(isset($this->salonsInfo["mill_salons"]) && !empty($this->salonsInfo["mill_salons"]))
			{
				foreach($this->salonsInfo["mill_salons"] as $salonsData)
				{
                    $salonDetails = $this->Common_model->getSalonInfoBy($salonsData['salon_id']);
                    
                    // Database Log
                    $log['AccountNo'] = $salonDetails['salon_info']['salon_code'];
                    $log['salon_id'] = $salonDetails['salon_info']['salon_id'];
                    $log['StartingTime'] = date('Y-m-d H:i:s');
                    $log['whichCron'] = 'setStaffPrebookInternalGraph';
                    //$log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $log['CronUrl'] = MAIN_SERVER_URL.'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/'.$dayRangeType.'/'.$salon_id;
                    $log['CronType'] = 1;
                    $log['id'] = 0;
                    $log_id = $this->Common_model->saveMillCronLogs($log);  
                    
                    pa('',"Salon Reports Cron Dashboard--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                    //Get the all staff members by salon id
                    $getAllStaff = $this->Common_model->getAllStaffMembersBySalon($salonsData["salon_id"]);
                    
                    //pa($getAllStaff,'Staff');
                     
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
                            $this->getRetailPerServiceReports($temps);
                       
                            if(isset($this->staffCalcData["prebook_percentage"]) && !empty($this->staffCalcData["prebook_percentage"]))
							{
								//JSON DECODE FOR EACH LOOP STARTS
								foreach($this->staffCalcData["prebook_percentage"] as $resValue)
								{
                                    $insertDataArray["salon_id"] = $salonsData["salon_id"];
									$insertDataArray["staff_id"] = $staffMembers["staff_id"];
									$insertDataArray["dayRangeType"] = $resValue["dayRangeType"];
									$insertDataArray["start_date"] = $resValue['start_date'];
									$insertDataArray["end_date"] = $resValue['end_date'];
									$insertDataArray["prebook_value"] = $resValue["prebook_value"];
									$insertDataArray["rebook_value"] = $resValue["rebook_value"];
									$insertDataArray["key"] = $resValue["key"];
									$insertDataArray["last_year_prebook_value"] = $resValue["last_year_prebook_value"];
									$insertDataArray["last_year_rebook_value"] = $resValue["last_year_rebook_value"];
									
                                    $yearRes = (new DateTime($resValue['start_date']))->format("Y");
                                    $monthRes = (new DateTime($resValue['start_date']))->format("m");
                                    
									
									$reportsWhere = array('salon_id' => $salonsData["salon_id"], 'staff_id' => $staffMembers["staff_id"],'dayRangeType' =>  $resValue["dayRangeType"],'key' => $resValue["key"],"MONTH(`start_date`)" => $monthRes, 'YEAR(`start_date`)' => $yearRes );
                                    if(MONTHLY == $this->dayRangeType)
                                    {
                                            $reportsWhere = array('salon_id' => $salonsData["salon_id"], 'staff_id' => $staffMembers["staff_id"], 'dayRangeType' =>  $resValue["dayRangeType"],'key' => $resValue["key"]);
                                    }
                                    
									$reportsDataForSalon = $this->DB_ReadOnly->select('*')
                                                                    ->get_where(MILL_PREBOOK_CALCULATIONS_CRON,$reportsWhere)
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
								    		$diff_array["insert_status"] = StaffPrebookDataForGraphs::UPDATED;
											$diff_array["updatedDate"] = date("Y-m-d H:i:s");
								    		try {
											    $this->db->where('id', $reportsDataForSalon['id']);
									    		$this->db->update(MILL_PREBOOK_CALCULATIONS_CRON, $diff_array); 
												pa("Reports Data updated Successfully.");
											} catch (Exception $e) {
											    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
											}
								    	}
								    }
								    else
								    {
                                        	//INSERT REPORT
									    	$insertDataArray["insert_status"] = StaffPrebookDataForGraphs::INSERTED;
											$insertDataArray["insertedDate"] = date("Y-m-d H:i:s");
											$insertDataArray["updatedDate"] = date("Y-m-d H:i:s");
											try {
												$this->db->insert(MILL_PREBOOK_CALCULATIONS_CRON, $insertDataArray);
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

