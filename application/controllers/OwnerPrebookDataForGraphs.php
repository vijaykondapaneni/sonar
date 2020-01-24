<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OwnerPrebookDataForGraphs extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Internal Graphs Calculation For Owner
    **/
    CONST INSERTED = 0;
    CONST UPDATED = 1;
    public $salon_id;
    public $startDate;
    public $endDate;
    public  $currentDate;
    private $salonId;
    private $salonDetails;
    private $dayRangeType;    
  
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Saloncloudsplus_model');
        $this->load->model('GraphsOwner_model');

    }
    // Get Date Range Types
    function __getStartEndDate($dayRangeType,$year=1)
    {
        $this->dayRangeType =  $dayRangeType;
        $currentDate = date('Y-m-d');

         switch ($this->dayRangeType) {
            case "today":
                $this->startDate = $this->currentDate;
                $this->endDate = $this->currentDate;
            break;                          
            case "lastMonth":
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
            break;
            case "Monthly":
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $this->currentDate;
            break;
            case "threemonths":
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
            break;
            case "Yearly":                                   
                    /*$this->startDate = getDateFn(strtotime('first day of January '.date('Y')));*/
                $this->startDate = date("Y-m-")."01";
                $this->endDate = $this->currentDate;
            break;
            case "Previousmonths": 
                 $currentmonthdate = date('m');
                    if($currentmonthdate <= 3){
                         $year_given = date("Y",strtotime("-1 year"));
                         $this->startDate = getDateFn(strtotime('first day of January '.$year_given));
                          $this->endDate = getDateFn(strtotime("last day of last month"));
                    }else{
                       $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                       $this->endDate = getDateFn(strtotime("last day of last month"));
                    }                                    
                // $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                // $this->endDate = getDateFn(strtotime("last day of last month"));
            break;            
            default:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
           break;

       }
    }

   
    function __getSalonReportForOwnerGraphs($data){
       if(!empty($data["salon_id"]) && !empty($data["start_date"]) && !empty($data["end_date"])&& !empty($data["dayRangeType"]))
            {
                $salon_id = $data['salon_id'];
                $startDate = $data['start_date'];
                $endDate = $data['end_date'];
                $dayRangeType = $data['dayRangeType'];
                $startDayOfTheWeek = $data['startDayOfTheWeek'];
                if(($dayRangeType=="Yearly")||($dayRangeType=="Previousmonths") ){
                    $response['values'] = array();
                    $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                    $salon_name = $salonDetails['salon_name'];
                    $salonAccountNo = $salonDetails['salon_account_id'];
                    $dataArray['location_id'] = $salon_id;
                    $dataArray['location_name'] = $salon_name;
                    $begin = new DateTime($startDate);
                    $end = new DateTime($endDate);
                    $interval = new DateInterval('P1M');
                    $daterange = new DatePeriod($begin, $interval ,$end);
                    $salesArray = array();
                    foreach ($daterange as $key => $date) {
                        $month = $date->format("m");
                        $month = ltrim($month, '0');
                        $monthName = $date->format("F");
                        //$year = date("Y");
                        $firstDayOfMonth = $date->format("Y-m-d");
                        $lastdayOfMonth = date('Y-m-t',strtotime($date->format("Y-m-d")));
                        $year =  date("Y",strtotime($firstDayOfMonth));
                        $last_year_start_date = strtotime($firstDayOfMonth);
                        $firstDayOfMonth = date('Y-m-d', $last_year_start_date);
                        $last_year_end_date = strtotime($lastdayOfMonth);
                        $lastdayOfMonth = date('Y-m-d', $last_year_end_date);
                        $prebookArray[$monthName] = array();
                        $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund' => 'false');
                        $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                        $resultAssocArr = $this->GraphsOwner_model
                                                  ->getPrebookDetailsArr($whereConditions,$where)
                                                  ->row_array();
                        $prebookArray[$monthName]['Services']['New'] = !empty($resultAssocArr)? $resultAssocArr : array();

                        $whereConditions =  array('account_no' =>$salonAccountNo,'lprebook' =>'true','lrefund' => 'false');
                        $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                        $serviceSalesDetailsArrRepeat = $this->GraphsOwner_model
                                                  ->getPrebookDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$monthName]['Services']['Repeat'] = !empty($serviceSalesDetailsArrRepeat)? $serviceSalesDetailsArrRepeat : array();                           

                          
                        
                        //LAST YEAR DATA
                        $month = $date->format("m");
                        $month = ltrim($month, '0');
                        $monthName = $date->format("F");
                        // $year = date("Y",strtotime("-1 year"));
                        $lastyear =  date("Y",strtotime($firstDayOfMonth));
                        $year = $lastyear -1;
                        $last_year_start_date = strtotime($startDate.' -1 year');
                        $lastYearStartDate = date('Y-m-d', $last_year_start_date);
                        $last_year_end_date = strtotime($endDate.' -1 year');
                        $lastYearEndDate = date('Y-m-d', $last_year_end_date);
                        $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund' => 'false');
                        $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                        $resultAssocArr = $this->GraphsOwner_model
                                                  ->getPrebookDetailsArr($whereConditions,$where)
                                                  ->row_array();
                        $prebookArray[$monthName]['last_year_Services']['New'] = !empty($resultAssocArr)? $resultAssocArr : array();
                        
                        $whereConditions =  array('account_no' =>$salonAccountNo,'lprebook' =>'true','lrefund' => 'false');
                        $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                        $serviceSalesDetailsArr = $this->GraphsOwner_model
                                                  ->getPrebookDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$monthName]['last_year_Services']['Repeat'] = !empty($serviceSalesDetailsArr)? $serviceSalesDetailsArr : array();
                        $prebookArray[$monthName]['dates']['Repeat']['start_date'] = $firstDayOfMonth;
                        $prebookArray[$monthName]['dates']['Repeat']['end_date'] = $lastdayOfMonth; 
                    }
                    if(!empty($prebookArray)){
                            $tempArr = array();
                            foreach($prebookArray as $month_week_name => $dayRanges) {
                               $servicesArray = $dayRanges['Services'];
                               $lastYearServicesArray = $dayRanges['last_year_Services'];
                               $datesArray = $dayRanges['dates'];
                               $prebookPercentage["current_value"] = isset($servicesArray['Repeat']['unique_client_count']) && isset($servicesArray['New']['unique_client_count']) && ($servicesArray['Repeat']['unique_client_count']!=0 || $servicesArray['New']['unique_client_count']!=0) ? $this->Common_model->appCloudNumberFormat(($servicesArray['Repeat']['unique_client_count']/($servicesArray['New']['unique_client_count']))*100, 2):'0';
                               $prebookPercentage["key"] =  substr($month_week_name, 0, 3);
                               //LAST YEAR DATA
                               $prebookPercentage["last_year_value"] = isset($lastYearServicesArray['Repeat']['unique_client_count']) && isset($lastYearServicesArray['New']['unique_client_count']) && ($lastYearServicesArray['Repeat']['unique_client_count']!=0 || $lastYearServicesArray['New']['unique_client_count']!=0) ? $this->Common_model->appCloudNumberFormat(($lastYearServicesArray['Repeat']['unique_client_count']/($lastYearServicesArray['New']['unique_client_count']))*100, 2):'0';
                               $prebookPercentage["key"] =  substr($month_week_name, 0, 3);
                               $prebookPercentage["start_date"] = $datesArray['Repeat']['start_date'];
                               $prebookPercentage["end_date"] = $datesArray['Repeat']['end_date'];


                               $tempArr[] = $prebookPercentage;
                             }
                            $dataArray["graph_data"] = $tempArr;
                          }
                    $response['values'][] = $dataArray;
                    $response['start_date'] = $startDate;
                    $response['end_date'] = $endDate;
                    $response["status"] = true;
                }elseif($dayRangeType=="Monthly"){
                    $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                    $salon_name = $salonDetails['salon_name'];
                    $salonAccountNo = $salonDetails['salon_account_id'];
                    $dataArray['location_id'] = $salon_id;
                    $dataArray['location_name'] = $salon_name;
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek)){
                        $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else{
                        $fourWeeksArr = getLast4WeekRanges(date('Y'));
                    }
                    $prebookArray = array();
                    $week_number=0;
                    $week_number_db=1;
                    foreach ($fourWeeksArr as $dates) {
                        $startDayOfWeek = $dates['start_date'];
                        $endDayOfWeek = $dates['end_date'];
                        $current_week = $dates['current_week'];
                        pa($startDayOfWeek.' To '.$endDayOfWeek,'Start and End DayOfWeek',false);
                        $prebookArray[$week_number_db] = array();
                        $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund'=>'false');
                        /*  $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year AND WEEK(tdatetime) =$week";*/
                        $where = "tdatetime >='".$startDayOfWeek."' AND tdatetime<='".$endDayOfWeek."'";
                        pa($where,'currentYear');
                        $resultAssocArr = $this->GraphsOwner_model
                                                  ->getPrebookDetailsArr($whereConditions,$where)
                                                  ->row_array();
                        $prebookArray[$week_number_db]['Services']['New'] = !empty($resultAssocArr)? $resultAssocArr : array();
                        $whereConditions =  array('account_no' =>$salonAccountNo,'lprebook' =>'true','lrefund' => 'false');
                        /*$where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year AND WEEK(tdatetime) =$week";*/
                        $where = "tdatetime >='".$startDayOfWeek."' AND tdatetime<='".$endDayOfWeek."'";
                       
                        $serviceSalesDetailsArr = $this->GraphsOwner_model
                                                  ->getPrebookDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$week_number_db]['Services']['Repeat'] = !empty($serviceSalesDetailsArr)? $serviceSalesDetailsArr : array();
                        $prebookArray[$week_number_db]['Dates']['start_date'] = $startDayOfWeek;
                        $prebookArray[$week_number_db]['Dates']['end_date'] = $endDayOfWeek;
                        $prebookArray[$week_number_db]['Dates']['current_week'] = $current_week;


                       //LAST Year Same MONTH DATA
                        if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                        {   
                            $fourWeeksArrLastYear = getLast4BusinessWeeksRanges($startDayOfTheWeek,date("Y",strtotime("-1 year")));
                        }
                        else
                        {
                           $fourWeeksArrLastYear = getLast4WeekRanges(date("Y",strtotime("-1 year")));
                        }

                        // pa($fourWeeksArrLastYear,'fourWeeksArrLastYear',false);
                        $startDayOfWeekLastYear = $fourWeeksArrLastYear[$week_number]['start_date'];
                        $endDayOfWeekLastYear = $fourWeeksArrLastYear[$week_number]['end_date'];
                        pa($startDayOfWeekLastYear." To ". $endDayOfWeekLastYear,'Start and End DayOfWeekLastYear',false);

                        $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund'=>'false');
                        /*$where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year AND WEEK(tdatetime) =$week";*/
                        $where = "tdatetime >='".$startDayOfWeekLastYear."' AND tdatetime<='".$endDayOfWeekLastYear."'";
                        pa($where,'whereLastYear');
                       
                        $resultAssocArrLastyear = $this->GraphsOwner_model
                                                  ->getPrebookDetailsArr($whereConditions,$where)
                                                  ->row_array();
                        $prebookArray[$week_number_db]['last_year_Services']['New'] = !empty($resultAssocArrLastyear)? $resultAssocArrLastyear : array();
                        $whereConditions =  array('account_no' =>$salonAccountNo,'lprebook' =>'true','lrefund' => 'false');
                        /*$where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year AND WEEK(tdatetime) =$week";*/
                        $where = "tdatetime >='".$startDayOfWeekLastYear."' AND tdatetime<='".$endDayOfWeekLastYear."'";
                        
                        $serviceSalesDetailsArrRepeatLastYear = $this->GraphsOwner_model
                                                  ->getPrebookDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$week_number_db]['last_year_Services']['Repeat'] = !empty($serviceSalesDetailsArrRepeatLastYear)? $serviceSalesDetailsArrRepeatLastYear : array();
                        $week_number++;
                        $week_number_db++;
                    }
                    if(!empty($prebookArray))
                    {
                        $tempArr = array();
                        foreach($prebookArray as $month_week_name => $dayRanges)
                        {
                            $servicesArray = $dayRanges['Services'];
                            $datesArray = $dayRanges['Dates'];
                            $lastYearServicesArray = $dayRanges['last_year_Services'];
                            $prebookPercentage["current_value"] = isset($servicesArray['Repeat']['unique_client_count']) && isset($servicesArray['New']['unique_client_count']) && ($servicesArray['Repeat']['unique_client_count']!=0 || $servicesArray['New']['unique_client_count']!=0) ? $this->Common_model->appCloudNumberFormat(($servicesArray['Repeat']['unique_client_count']/($servicesArray['New']['unique_client_count']))*100, 2):'0';
                            $prebookPercentage["key"] = $month_week_name;
                            //LAST YEAR DATA
                            $prebookPercentage["last_year_value"] = isset($lastYearServicesArray['Repeat']['unique_client_count']) && isset($lastYearServicesArray['New']['unique_client_count']) && ($lastYearServicesArray['Repeat']['unique_client_count']!=0 || $lastYearServicesArray['New']['unique_client_count']!=0) ? $this->Common_model->appCloudNumberFormat(($lastYearServicesArray['Repeat']['unique_client_count']/($lastYearServicesArray['New']['unique_client_count']))*100, 2):'0';
                            $prebookPercentage["key"] = "Week ".$month_week_name;
                            $prebookPercentage['start_date'] = $datesArray['start_date'];
                            $prebookPercentage['end_date'] = $datesArray['end_date'];
                            $prebookPercentage['current_week'] = $datesArray['current_week'];
                            $tempArr[] = $prebookPercentage;
                           
                        }
                        $dataArray["graph_data"] = $tempArr; 
                    }  
                    $response['values'][] = $dataArray;
                    $response["start_date"] = $startDate;
                    $response["end_date"] = $endDate;
                    $response["status"] = true;  
                }
            }else{
                $response["status"] = false;
            }
            return $response;
    }
    
    
    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    /**
     * This function for get employee schedule hours last year data
     * @param type $account_no
     */
    function setOwnerPrebookDataForGraphs($dayRangeType="today",$salon_id="")
        { 
            $this->currentDate = getDateFn();
            $getAllSalons = $this->Common_model->getAllSalons($salon_id);
            $processedSalons = array();
            if(isset($getAllSalons["mill_salons"]) && !empty($getAllSalons["mill_salons"]))
            {
                foreach($getAllSalons["mill_salons"] as $salonsData)
                {
                    $salon_id = $salonsData["salon_id"];
                    if(!in_array($salon_id, $processedSalons))
                    {
                        pa('',"Reports Cron Running For--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                        $this->salon_id = $salonsData['salon_id'];
                        $salonDetails = $this->Common_model->getSalonInfoBy($this->salon_id);
                    
                        if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"]) && $salonDetails['salon_info']["millennium_enabled"]=="Yes"){
                        // Database Log
                        $log['AccountNo'] = $salonDetails['salon_info']['salon_code'];
                        $log['salon_id'] = $salonDetails['salon_info']['salon_id'];
                        $log['StartingTime'] = date('Y-m-d H:i:s');
                        $log['whichCron'] = 'setOwnerPrebookDataForGraphs';
                        $log['CronUrl'] = MAIN_SERVER_URL.'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/'.$dayRangeType.'/'.$salon_id;
                        $log['CronType'] = 1;
                        $log['id'] = 0;
                        $log_id = $this->Common_model->saveMillCronLogs($log);    
                        // GET START DATE AND END DATE AS PER PARAMETERS
                         $this->__getStartEndDate($dayRangeType);
                        $arrData['start_date'] = $this->startDate;
                        $arrData['end_date'] = $this->endDate;
                        $arrData['status'] = true;
                        $arrSalonDetails['salon_id'] = $this->salon_id;
                        $arrSalonDetails['start_date'] = $this->startDate;
                        $arrSalonDetails['end_date'] = $this->endDate;
                        $arrSalonDetails['dayRangeType'] = $dayRangeType;
                        $arrSalonDetails['startDayOfTheWeek'] =  isset($salonsData["salon_start_day_of_week"]) && !empty($salonsData["salon_start_day_of_week"]) ? $salonsData["salon_start_day_of_week"] : '';                        
                        $getAllRetailData = $this->__getSalonReportForOwnerGraphs($arrSalonDetails);
                        //pa($getAllRetailData,'getAllRetailData',true);
                        if(isset($getAllRetailData) && !empty($getAllRetailData['values'])){
                            foreach($getAllRetailData['values'] as $resValue){
                                $salonId = $resValue["location_id"];
                                $processedSalons[] = $salonId;
                                if(isset($resValue["graph_data"]) && !empty($resValue["graph_data"]))
                                {
                                    foreach($resValue['graph_data'] as $resValuess){
                                        array_walk($resValuess, function (&$value,&$key) { 
                                            if(($key == 'current_value') || ($key == 'last_year_value'))
                                                $value = number_format($value, 2, '.', '');
                                        });
                                        if($dayRangeType=='Previousmonths'){
                                            $this->startDate = $resValuess['start_date'];
                                            $this->endDate = $resValuess['end_date'];
                                            $insertdayRangeType = 'Yearly';
                                        }elseif($dayRangeType=='Monthly'){
                                            $this->startDate = $resValuess['start_date'];
                                            $this->endDate = $resValuess['end_date'];
                                            $insertdayRangeType = $dayRangeType;
                                        }else{
                                            $insertdayRangeType = $dayRangeType;
                                        }
                                        $dataArray['salon_id'] = $salonId;
                                        $dataArray['dayRangeType'] = $insertdayRangeType;
                                        $dataArray['start_date'] = $this->startDate;
                                        $dataArray['end_date'] = $this->endDate;
                                        $dataArray['current_value'] = $resValuess['current_value'];
                                        $dataArray['key'] = $resValuess['key'];
                                        $dataArray['last_year_value'] = $resValuess['last_year_value'];
                                        $yearRes = (new DateTime($this->startDate))->format("Y");
                                        $monthRes = (new DateTime($this->endDate))->format("m");
                                        // compare database
                                        $reportsWhere = array('salon_id' => $salonId,'dayRangeType' => $insertdayRangeType,'key' => $resValuess['key'],"MONTH(`start_date`)" => $monthRes, 'YEAR(`start_date`)' => $yearRes);
                                        if($dayRangeType=='Monthly'){
                                            $reportsWhere = array('salon_id' => $salonId,'dayRangeType' => $insertdayRangeType,'key' => $resValuess['key']);
                                        }

                                        $reportsDataForSalon = $this->GraphsOwner_model
                                                               ->comparePrebookOwnerReportsData($reportsWhere)
                                                               ->row_array();
                                        if(!empty($reportsDataForSalon)){
                                             $diff_array = array_diff_assoc($dataArray, $reportsDataForSalon);
                                             if(empty($diff_array)){
                                               pa('No Updates');
                                             }else{
                                                // update
                                                $diff_array['insert_status'] = OwnerPrebookDataForGraphs::UPDATED;
                                                $diff_array["updatedDate"] = date("Y-m-d H:i:s");
                                                try {
                                                    $reportsWhere = array('salon_id' => $salonId,'key' => $resValuess['key'],'dayRangeType' => $insertdayRangeType,'id'=>$reportsDataForSalon['id']);
                                                    $update = $this->GraphsOwner_model->updatePrebookOwnerReportsData($reportsWhere,$diff_array);
                                                    pa($diff_array,'Reports Data updated Successfully');
                                                    pa($reportsDataForSalon['id'],'DB ID');
                                                } catch (Exception $e) {
                                                    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
                                                }
                                             }

                                        }else{
                                            
                                              // Insert Data
                                                $dataArray["insert_status"] = OwnerPrebookDataForGraphs::INSERTED;
                                                $dataArray["insertedDate"] = date("Y-m-d H:i:s");
                                                $dataArray["updatedDate"] = date("Y-m-d H:i:s");
                                                try {
                                                    $insert = $this->GraphsOwner_model->insertPrebookOwnerReportsData($dataArray);
                                                    pa($this->db->insert_id(),'Reports Data Inserted
                                                        ');
                                                } catch (Exception $e) {
                                                    echo 'Reports Insert failed: ' . $e->getMessage()."<br>";
                                                }
                                        }                        
                                                            
                                    }
 
                                }else{
                                    echo "No Graph Data";
                                }

                            }
 
                        }else{
                            echo "No Data";
                        }
                     // Database Log
                    $log['id'] = $log_id;
                    $log_id = $this->Common_model->saveMillCronLogs($log);     
                    
                    }else{
                        echo "Salon Details are not sufficient";
                    }
                  } // if loop close  
                } // for each loop
            }else{
                echo "No SalonId's In Server";
            }
            
       } 
   
 }       