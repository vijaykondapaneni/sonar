<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class StaffRetailBuyByClientForGraphs extends CI_Controller
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
    
    /**
     * 
     * @param type $salonAccountNo
     * @param type $temp
     * @return type $this->__insideConfigArr
     */
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
        
        $getUniqueClientTickets = $this->DB_ReadOnly->query("SELECT iclientid
																	FROM  ".MILL_SERVICE_SALES." 
																	WHERE  `account_no` =".$salonAccountNo."
																	AND ".$sqlCommonWeekCond."
																	AND  `iempid` =".$temp["staff_iid"]."
																	AND  `lrefund` =  'false'
																	UNION 
																	SELECT iclientid
																	FROM  ".MILL_PRODUCT_SALES." 
																	WHERE  `account_no` =".$salonAccountNo."
																	AND ".$sqlCommonWeekCond."
																	AND  `iempid` =".$temp["staff_iid"]."
																	AND  `lrefund` =  'false'")->result_array();
        //pa($this->db->last_query(),'$getUniqueClientTickets');
        
        $this->__insideConfigArr['getUniqueClientTickets'.$temp['strYearType']] = !empty($getUniqueClientTickets) ? $getUniqueClientTickets : array();
        
       
        $whereProductSalesCondition = array('account_no' =>$salonAccountNo , 'iempid' =>$temp["staff_iid"],'lrefund' => 'false');
        
        $productSalesDetailsArrNew =  $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count')
                                                ->where($sqlCommonWeekCond)
                                                ->get_where(MILL_PRODUCT_SALES,$whereProductSalesCondition )
                                                ->row_array();                
        
        
        //pa($this->db->last_query(),'$productSalesDetailsArrNew');
        
        $this->__insideConfigArr['productSalesDetailsArrNew'.$temp['strYearType']] = !empty($productSalesDetailsArrNew) ? $productSalesDetailsArrNew : array();  
        
        return $this->__insideConfigArr;
    }
    
    /**
     * 
     * @param type $temp
     */    
    private function getRetailBuyByClientReports($temp)
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
                                        
                                        pa( '','Loop'.'-'.$monthName.'-'.$intYear);
                                        
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
                       
                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Both']['New'] =  $this->__insideConfigArr['getUniqueClientTickets'.$strYearType];
                                        
                                        $this->insideFormulaCalcuation[$monthName][$strYearType.'Products']['New'] =  $this->__insideConfigArr['productSalesDetailsArrNew'.$strYearType]; 
                                         
                                      
                                        $this->insideFormulaCalcuation[$monthName][$strYearType]['start_date'] =  $startDayOfMonth;    
                                        $this->insideFormulaCalcuation[$monthName][$strYearType]['end_date'] =  $endDayOfMonth;   
                                    }
                                    //Calculation the Ruct server by client for this and last year of current month 
                                    
                                     $tempArr = $this->__calculateRetailBuyingByClient($strYearType,'Yearly');
                                     
                            }
                            
                        $this->staffCalcData["retail_percentage"] = $tempArr;
                  		//pa($this->insideFormulaCalcuation,'insideFormulaCalcuation');
                        //pa($this->staffCalcData,$this->dayRangeType,TRUE);
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
                                         
                                        pa($startDayOfWeek.' To '.$endDayOfWeek,'Loop');
                                   
                                       
                                        $temp['strYearType'] =  $strYearType;
                                        $temp['startDayOfWeek'] = $startDayOfWeek;
                                        $temp['endDayOfWeek'] = $endDayOfWeek;
                                        
                                        $this->__getInsideSqlQueryFn($salonAccountNo, $temp);
                       
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Both']['New'] =  $this->__insideConfigArr['getUniqueClientTickets'.$strYearType];
                                        
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType.'Products']['New'] =  $this->__insideConfigArr['productSalesDetailsArrNew'.$strYearType]; 
                                         
                                      
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType]['start_date'] =  $startDayOfWeek;    
                                        $this->insideFormulaCalcuation[$weekNumber][$strYearType]['end_date'] =  $endDayOfWeek; 
                                      
                                        $i++;
						            }
                                    //Calculation the prebook and rebook for this and last year of current month  
                                    $tempArr = $this->__calculateRetailBuyingByClient($strYearType,$this->dayRangeType);
                            }
                            
                        $this->staffCalcData["retail_percentage"] = $tempArr;
                  		$this->staffCalcData["start_date"] = $temp["start_date"];
              			$this->staffCalcData["end_date"] = $temp["end_date"];
                        //pa($this->insideFormulaCalcuation,'insideFormulaCalcuation');         
                        // pa($this->staffCalcData,'staffCalcData',TRUE); 
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
    
    /**
     * 
     * @param type $strYearType
     * @param type $Key
     * @return type Associate array $this->_staffCalcData
     */
    private function __calculateRetailBuyingByClient($strYearType,$Key = '')
    {
         //pa($this->insideFormulaCalcuation,'__calculatePrebookAndRebook');
         
        //Calculation the prebook and rebook for this and last year of current month  
            if(!empty($this->insideFormulaCalcuation))
                {
                    $tempArr = array();
                    foreach($this->insideFormulaCalcuation as $month_week_name => $dayRanges)
                    {
                        if($strYearType == 'thisYear'){ $strYear =  'current';}
                        if($strYearType == 'lastYear'){ $strYear =  'last_year';}
    
                        $productsArray = $dayRanges[$strYearType.'Products'];
                        $uniqueInvoiceArray = $dayRanges[$strYearType.'Both'];

                        //COUNT OF INVOICE TICKETS

                        $this->__insideConfigArr['uniqueInvoicesArr'.$strYearType] = (isset($uniqueInvoiceArray['New'])) ? array_column($uniqueInvoiceArray['New'], "iclientid") : array();

                        $this->__insideConfigArr['totalUniqueInvoices'.$strYearType] = (isset($this->__insideConfigArr['uniqueInvoicesArr'.$strYearType])) ? count($this->__insideConfigArr['uniqueInvoicesArr'.$strYearType]) : 0;
                        
                        //CLIENTS BUYING RETAIL
              			$this->_staffCalcData[$month_week_name][$strYear.'_value'] = (($this->__insideConfigArr['totalUniqueInvoices'.$strYearType] != 0) && isset($productsArray['New']['unique_client_count']) && !empty($productsArray['New']['unique_client_count']) ) ? $this->Common_model->appCloudNumberFormat(($productsArray['New']['unique_client_count']/$this->__insideConfigArr['totalUniqueInvoices'.$strYearType]) * 100,2) : '0.00';

                       //pa($strYearType.' '.$month_week_name. ' >>>>  '.$productsArray['New']['unique_client_count'] .' --- '.$this->__insideConfigArr['totalUniqueInvoices'.$strYearType])  ;
                         
                       
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
    
    function setStaffRetailBuyByClientInternalGraph($dayRangeType="Today",$account_no="", $year= 1)
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
                    $log['whichCron'] = 'setStaffRetailBuyByClientInternalGraph';
                    //$log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $log['CronUrl'] = MAIN_SERVER_URL.'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/'.$dayRangeType.'/'.$salon_id;
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
                            $this->getRetailBuyByClientReports($temps);
                           
                            if(isset($this->staffCalcData["retail_percentage"]) && !empty($this->staffCalcData["retail_percentage"]))
							{
								//JSON DECODE FOR EACH LOOP STARTS
								foreach($this->staffCalcData["retail_percentage"] as $resValue)
								{
                                    
                                    $insertDataArray["salon_id"] = $salonsData["salon_id"];
									$insertDataArray["staff_id"] = $staffMembers["staff_id"];
									$insertDataArray["dayRangeType"] = $resValue["dayRangeType"];
									$insertDataArray["start_date"] = $resValue['start_date'];
									$insertDataArray["end_date"] = $resValue['end_date'];
                                    
									$insertDataArray["current_value"] = $resValue["current_value"];
									$insertDataArray["key"] = $resValue["key"];
									$insertDataArray["last_year_value"] = $resValue["last_year_value"];
																		
                                    $yearRes = (new DateTime($resValue['start_date']))->format("Y");
                                    $monthRes = (new DateTime($resValue['start_date']))->format("m");
                                    
									
									$reportsWhere = array('salon_id' => $salonsData["salon_id"], 'staff_id' => $staffMembers["staff_id"],'dayRangeType' =>  $resValue["dayRangeType"],'key' => $resValue["key"],'start_date' => $resValue['start_date'], 'end_date' => $resValue['end_date'] );
                                    if(MONTHLY == $this->dayRangeType)
                                    {
                                            $reportsWhere = array('salon_id' => $salonsData["salon_id"], 'staff_id' => $staffMembers["staff_id"], 'dayRangeType' =>  $resValue["dayRangeType"],'key' => $resValue["key"]);
                                    }
                                    
									$reportsDataForSalon = $this->DB_ReadOnly->select('*')
                                                                    ->get_where(MILL_CLIENT_BUYING_RETAIL_STAFF_REPORTS,$reportsWhere)
                                                                    ->row_array();
                                  
                                    if(!empty($reportsDataForSalon))
								    {
                                        $diff_array = array_diff_assoc($insertDataArray,$reportsDataForSalon);
                                
                                        //pa($diff_array,"Diff Array");
                                        
								    	if(empty($diff_array))
								    	{
								    		continue;
								    	}
								    	else
								    	{
                                            //Update REPORT
								    		$insertDataArray["insert_status"] = StaffRetailBuyByClientForGraphs::UPDATED;
											$insertDataArray["updatedDate"] = date("Y-m-d H:i:s");
								    		try {
											    $this->db->where('id', $reportsDataForSalon['id']);
									    		$this->db->update(MILL_CLIENT_BUYING_RETAIL_STAFF_REPORTS, $insertDataArray); 
												pa ($this->db->last_query(),"Reports Data updated Successfully.");
											} catch (Exception $e) {
											    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
											}
								    	}
								    }
								    else
								    {
                                        	//INSERT REPORT
									    	$insertDataArray["insert_status"] = StaffRetailBuyByClientForGraphs::INSERTED;
											$insertDataArray["insertedDate"] = date("Y-m-d H:i:s");
											$insertDataArray["updatedDate"] = date("Y-m-d H:i:s");
											try {
												$this->db->insert(MILL_CLIENT_BUYING_RETAIL_STAFF_REPORTS, $insertDataArray);
                                                pa ($this->db->last_query(),"Reports Data inserted Successfully.");
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
}

