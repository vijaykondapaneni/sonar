<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OwnerNewGuestDataForGraphs extends CI_Controller
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
        $this->load->model('DashboardOwner_model');

    }
    // Get Date Range Types
    function __getStartEndDate($dayRangeType,$year=1)
    {
        $this->dayRangeType =  $dayRangeType;
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
                    $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)
                                           ->row_array();
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
                        // $year = date("Y");
                        $firstDayOfMonth = $date->format("Y-m-d");
                        $lastdayOfMonth = date('Y-m-t',strtotime($date->format("Y-m-d")));
                        $year =  date("Y",strtotime($firstDayOfMonth));
                        $last_year_start_date = strtotime($firstDayOfMonth);
                        $firstDayOfMonth = date('Y-m-d', $last_year_start_date);
                        $last_year_end_date = strtotime($lastdayOfMonth);
                        $lastdayOfMonth = date('Y-m-d', $last_year_end_date);
                        /*$whereConditions =  array('salonAccountNo' =>$salonAccountNo,'month'=>$month,'year'=>$year);
                        $resultAssocArr = $this->GraphsOwner_model
                                                  ->getNewGuestDetailsArr($whereConditions)
                                                  ->row_array();
                        pa($this->db->last_query(),'New Guest Qty');
                        pa($resultAssocArr,'resultAssocArr',false);*/
                        $clientscount_data['start_date'] = $firstDayOfMonth;
                        $clientscount_data['end_date'] = $lastdayOfMonth;
                        $clientscount_data['salonAccountNo'] = $salonAccountNo;
                        pa($clientscount_data);          
                        $new_guest = $this->DashboardOwner_model
                                               ->getNewGuestCount($clientscount_data);
                        $resultAssocArr['checked_in_clients_count'] = $new_guest;
                        //pa($resultAssocArr,'resultAssocArr',false);
                        $salesArray[$monthName]['NewGuest']['checked_in'] = !empty($resultAssocArr)? $resultAssocArr : array();

                        $salesArray[$monthName]['dates']['checked_in']['start_date'] = $firstDayOfMonth;
                        $salesArray[$monthName]['dates']['checked_in']['end_date'] = $lastdayOfMonth; 
                        
                        //LAST YEAR DATA
                        $month = $date->format("m");
                        $month = ltrim($month, '0');
                        $monthName = $date->format("F");
                        // $year = date("Y",strtotime("-1 year"));

                        $firstDayOfMonth = $date->format("Y-m-d");
                        $lastdayOfMonth = date('Y-m-t',strtotime($date->format("Y-m-d")));
                        $lastyear =  date("Y",strtotime($firstDayOfMonth));
                        $year = $lastyear -1;
                        $last_year_firstDayOfMonth = getDateFn(strtotime($firstDayOfMonth." -1 year"));

                        $last_year_lastdayOfMonth = getDateFn(strtotime($lastdayOfMonth." -1 year"));

                       
                        $last_year_start_date = strtotime($startDate.' -1 year');
                        $lastYearStartDate = date('Y-m-d', $last_year_start_date);
                        $last_year_end_date = strtotime($endDate.' -1 year');
                        $lastYearEndDate = date('Y-m-d', $last_year_end_date);
                       /* $whereConditions =  array('salonAccountNo' =>$salonAccountNo,'month'=>$month,'year'=>$year);
                      
                        $resultAssocArr_Lastyear = $this->GraphsOwner_model
                                                  ->getNewGuestDetailsArr($whereConditions)
                                                  ->row_array();*/

                        //pa($resultAssocArr_Lastyear,'resultAssocArr_Lastyear',false);
                        $clientscount_data['start_date'] = $last_year_firstDayOfMonth;
                        $clientscount_data['end_date'] = $last_year_lastdayOfMonth;
                        $clientscount_data['salonAccountNo'] = $salonAccountNo;
                        pa($clientscount_data);          
                        $new_guest = $this->DashboardOwner_model
                                          ->getNewGuestCount($clientscount_data);
                        $resultAssocArr_Lastyear['checked_in_clients_count'] = $new_guest;
                        //pa($resultAssocArr_Lastyear,'resultAssocArr',false);

                        $salesArray[$monthName]['last_year_NewGuest']['checked_in'] = !empty($resultAssocArr_Lastyear)? $resultAssocArr_Lastyear : array();
                    }
                    if(!empty($salesArray)){
                            $tempArr = array();
                            foreach ($salesArray as $month_week_name => $dayRanges) {
                               $newGuestArray = $dayRanges['NewGuest'];
                               $lastYearNewGuestArray = $dayRanges['last_year_NewGuest'];
                               $datesArray = $dayRanges['dates'];
                               $totalSales['current_value'] = isset($newGuestArray['checked_in']['checked_in_clients_count']) ? $newGuestArray['checked_in']['checked_in_clients_count']:'0';
                               $totalSales['key'] = substr($month_week_name, 0, 3);
                               $totalSales['last_year_value'] = isset($lastYearNewGuestArray['checked_in']['checked_in_clients_count']) ? $lastYearNewGuestArray['checked_in']['checked_in_clients_count']:'0';
                               $totalSales["start_date"] = $datesArray['checked_in']['start_date'];
                               $totalSales["end_date"] = $datesArray['checked_in']['end_date'];

                               $tempArr[] = $totalSales;
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
                    pa($startDayOfTheWeek,'startDayOfTheWeek');
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek)){
                        $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else{
                        $fourWeeksArr = getLast4WeekRanges(date('Y'));
                    }
                    $salesArray = array();
                    $week_number=0;
                    $week_number_db=1;
                    foreach ($fourWeeksArr as $dates) {
                        $startDayOfWeek = $dates['start_date'];
                        $endDayOfWeek = $dates['end_date'];
                        $current_week = $dates['current_week'];
                        pa($startDayOfWeek.' To '.$endDayOfWeek,'Start and End DayOfWeek',false);
                        /*$whereConditions =  array('salonAccountNo' =>$salonAccountNo,'startDayOfWeek'=>$startDayOfWeek,'endDayOfWeek'=>$endDayOfWeek);
                        pa($whereConditions,'Current Year');
                        $resultAssocArr = $this->GraphsOwner_model
                                                  ->getNewGuestDetailsWeekWiseStartDayOfTheWeekArr($whereConditions)
                                                  ->row_array();

                        pa($resultAssocArr);   */                       
                        
                        $clientscount_data['start_date'] = $startDayOfWeek;
                        $clientscount_data['end_date'] = $endDayOfWeek;
                        $clientscount_data['salonAccountNo'] = $salonAccountNo;
                        pa($clientscount_data);          
                        $new_guest = $this->DashboardOwner_model
                                               ->getNewGuestCount($clientscount_data);
                        $resultAssocArr['checked_in_clients_count'] = $new_guest;

                        $salesArray[$week_number_db]['NewGuest']['client_id'] = !empty($resultAssocArr)? $resultAssocArr : array();
                        $salesArray[$week_number_db]['Dates']['start_date'] = $startDayOfWeek;
                        $salesArray[$week_number_db]['Dates']['end_date'] = $endDayOfWeek;
                        $salesArray[$week_number_db]['Dates']['current_week'] = $current_week;
                           //LAST Year Same MONTH DATA
                        if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                        {   
                            $fourWeeksArrLastYear = getLast4BusinessWeeksRanges($startDayOfTheWeek,date("Y",strtotime("-1 year")));
                        }
                        else
                        {
                           $fourWeeksArrLastYear = getLast4WeekRanges(date("Y",strtotime("-1 year")));
                        }
                        $startDayOfWeekLastYear = $fourWeeksArrLastYear[$week_number]['start_date'];
                        $endDayOfWeekLastYear = $fourWeeksArrLastYear[$week_number]['end_date'];
                        /*pa($startDayOfWeekLastYear." To ". $endDayOfWeekLastYear,'Start and End DayOfWeekLastYear',false);
                        $whereConditions =  array('salonAccountNo' =>$salonAccountNo,'startDayOfWeek'=>$startDayOfWeekLastYear,'endDayOfWeek'=>$endDayOfWeekLastYear);
                        pa($whereConditions,'Last Year');
                        $resultAssocArrLastyear = $this->GraphsOwner_model
                                                      ->getNewGuestDetailsWeekWiseStartDayOfTheWeekArr($whereConditions)
                                                      ->row_array();
                        pa($resultAssocArrLastyear);*/                              
                        $clientscount_data['start_date'] = $startDayOfWeekLastYear;
                        $clientscount_data['end_date'] = $endDayOfWeekLastYear;
                        $clientscount_data['salonAccountNo'] = $salonAccountNo;
                        pa($clientscount_data);          
                        $new_guest = $this->DashboardOwner_model
                                               ->getNewGuestCount($clientscount_data);
                        $resultAssocArrLastyear['checked_in_clients_count'] = $new_guest;

                        $salesArray[$week_number_db]['last_year_NewGuest']['client_id'] = !empty($resultAssocArrLastyear)? $resultAssocArrLastyear : array();                          
                            
                            $week_number++;
                            $week_number_db++;
                        }
                        if(!empty($salesArray))
                        {
                            $tempArr = array();
                            foreach($salesArray as $month_week_name => $dayRanges)
                            {
                                $newGuestArray = $dayRanges['NewGuest'];
                                $datesArray = $dayRanges['Dates'];
                                $lastYearNewGuestArray = $dayRanges['last_year_NewGuest'];
                                $totalSales['current_value'] = isset($newGuestArray['client_id']['checked_in_clients_count']) ? $newGuestArray['client_id']['checked_in_clients_count']:'0';
                                $totalSales['key'] = "Week ".$month_week_name;
                                $totalSales['last_year_value'] = isset($lastYearNewGuestArray['client_id']['checked_in_clients_count']) ? $lastYearNewGuestArray['client_id']['checked_in_clients_count']:'0';
                                $totalSales['start_date'] = $datesArray['start_date'];
                                $totalSales['end_date'] = $datesArray['end_date'];
                                $totalSales['current_week'] = $datesArray['current_week'];
                                
                                $tempArr[] = $totalSales;
                                //}
                            }
                            $dataArray["graph_data"] = $tempArr;
                            
                        }
                        //print_r($dataArray);exit;
                        $response['values'][] = $dataArray;
                 
                $response["start_date"] = $startDate;
                $response["end_date"] = $endDate;
                $response["status"] = true;  
                }
                $response["status"] = false;
                                         
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
    function setOwnerNewGuestDataForGraphs($dayRangeType="today",$salon_id="")
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
                        pa('','salon Info','');
                    
                       if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"]) && $salonDetails['salon_info']["millennium_enabled"]=="Yes"){
                        // Database Log
                        $log['AccountNo'] = $salonDetails['salon_info']['salon_code'];
                        $log['salon_id'] = $salonDetails['salon_info']['salon_id'];
                        $log['StartingTime'] = date('Y-m-d H:i:s');
                        $log['whichCron'] = 'setOwnerNewGuestDataForGraphs';
                        $log['CronUrl'] = MAIN_SERVER_URL.'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/'.$dayRangeType.'/'.$salon_id;
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
                        pa($getAllRetailData,'getAllRetailData',false);
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

                                        //pa($reportsWhere,'reportsWhere',true);

                                        $reportsDataForSalon = $this->GraphsOwner_model
                                                               ->compareNewGuestOwnerReportsData($reportsWhere)
                                                               ->row_array();
                                        if(!empty($reportsDataForSalon)){
                                             $diff_array = array_diff_assoc($dataArray, $reportsDataForSalon);
                                             if(empty($diff_array)){
                                                pa('No Updates'); 
                                             }else{
                                                // update
                                                $diff_array['insert_status'] = OwnerNewGuestDataForGraphs::UPDATED;
                                                $diff_array["updatedDate"] = date("Y-m-d H:i:s");
                                                try {
                                                    $reportsWhere = array('salon_id' => $salonId,'key' => $resValuess['key'],'dayRangeType' => $insertdayRangeType,'id'=>$reportsDataForSalon['id']);
                                                    $update = $this->GraphsOwner_model->updateNewGuestOwnerReportsData($reportsWhere,$diff_array);
                                                    pa($diff_array,'Reports Data updated Successfully');
                                                    pa($reportsDataForSalon['id'],'DB Updated ID');
                                                } catch (Exception $e) {
                                                    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
                                                }
                                             }

                                        }else{
                                            
                                              // Insert Data
                                                $dataArray["insert_status"] = OwnerNewGuestDataForGraphs::INSERTED;
                                                $dataArray["insertedDate"] = date("Y-m-d H:i:s");
                                                $dataArray["updatedDate"] = date("Y-m-d H:i:s");
                                                try {
                                                    $insert = $this->GraphsOwner_model->insertNewGuestOwnerReportsData($dataArray);
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
                   } // if close  
                } // for each close
            }else{
                echo "No SalonId's In Server";
            }
            
       } 
   
 }       