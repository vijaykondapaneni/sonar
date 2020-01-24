<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OwnerTotalSalesDataForGraphs extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Owner Internal Graphs Calculation
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
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                $this->endDate = $this->currentDate;
            break;
            case "Previousmonths":                                   
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                $this->endDate = getDateFn(strtotime("last day of last month"));
            break;         
            default:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
           break;
                    }
    }

   
    function __getSalonReportForOwnerGraphs($data){
       pa($data,'Input Data'); 
       if(!empty($data["salon_id"]) && !empty($data["start_date"]) && !empty($data["end_date"])&& !empty($data["dayRangeType"]))
            {
                $salon_id = $data['salon_id'];
                $startDate = $data['start_date'];
                $endDate = $data['end_date'];
                $dayRangeType = $data['dayRangeType'];
                $startDayOfTheWeek = $data['startDayOfTheWeek'];
                $relatedSalons = $this->Saloncloudsplus_model->relatedSalons($salon_id);
               // pa($relatedSalons,'relatedSalons');
                if(!empty($relatedSalons)){
                        $response['values'] = array();
                        foreach ($relatedSalons as $eachSalon) {
                            $salon_id = $eachSalon['salon_id'];
                            $salon_name = $eachSalon['salon_name'];
                            $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)
                                                   ->row_array();
                            if(!empty($salonDetails))
                            {
                                $salonAccountNo = $salonDetails['salon_account_id'];
                            }
                            else
                            {
                                $salonAccountNo = '';
                            }
                            
                            $dataArray['location_id'] = $salon_id;
                            $dataArray['location_name'] = $salon_name;
                            $salesArray = array();
                            $year = date("Y",strtotime("-1 year"));
                            $last_year_start_date = strtotime($startDate.' -1 year');
                            $lastYearStartDate = date('Y-m-d', $last_year_start_date);
                            $last_year_end_date = strtotime($endDate.' -1 year');
                            $lastYearEndDate = date('Y-m-d', $last_year_end_date);
                            
                            if($dayRangeType=='Monthly'){
                                if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                                {   
                                    $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                                }
                                else
                                {
                                   $fourWeeksArr = getLast4WeekRanges(date('Y'));
                                }

                                $startDate = $fourWeeksArr[0]['start_date'];
                                $endDate = $fourWeeksArr[3]['end_date'];

                            // last year

                                if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                                {   
                                    $fourWeeksArrLastYear = getLast4BusinessWeeksRanges($startDayOfTheWeek,date("Y",strtotime("-1 year")));
                                }
                                else
                                {
                                   $fourWeeksArrLastYear = getLast4WeekRanges(date("Y",strtotime("-1 year")));
                                }

                                $lastYearStartDate = $fourWeeksArrLastYear[0]['start_date'];
                                $lastYearEndDate = $fourWeeksArrLastYear[3]['end_date'];

                            }

                            if($dayRangeType=='Yearly'){
                                $lastYearEndDate = date("Y-m-t", strtotime($lastYearEndDate));
                                
                            }
                            
                            $whereConditions = array('account_no' =>$salonAccountNo);
                            $where = "tdatetime>='".$startDate."' AND tdatetime<='".$endDate."'";
                            $whereLastYear = "tdatetime>='".$lastYearStartDate."' AND tdatetime<='".$lastYearEndDate."'";
                            //TOTAL RETAIL
                            $retailSalesDetailsArr = $this->GraphsOwner_model
                                                     ->getRetailDataforTotalSales($whereConditions,$where)
                                                     ->result_array();
                           // pa($this->db->last_query(),'retailSalesDetailsArr');                         
                            $retailTotalSales = isset($retailSalesDetailsArr[0]['nprice']) && !empty($retailSalesDetailsArr[0]['nprice']) ? $retailSalesDetailsArr[0]['nprice'] : ''; 

                            //LAST YEAR DATA
                            $retailSalesDetailsArrLastYear = $this->GraphsOwner_model
                                                     ->getRetailDataforTotalSales($whereConditions,$whereLastYear)
                                                     ->result_array();
                            //pa($this->db->last_query(),'retailSalesDetailsArrLastYear');                         
                            $retailTotalSalesLastYear = isset($retailSalesDetailsArrLastYear[0]['nprice']) && !empty($retailSalesDetailsArrLastYear[0]['nprice']) ? $retailSalesDetailsArrLastYear[0]['nprice'] : '';
                            
                            //TOTAL SERVICE
                            $serviceSalesDetailsArr =  $this->GraphsOwner_model
                                                     ->getSalesDataforTotalSales($whereConditions,$where)
                                                     ->result_array();
                            $serviceTotalSales = isset($serviceSalesDetailsArr[0]['nprice']) && !empty($serviceSalesDetailsArr[0]['nprice']) ? $serviceSalesDetailsArr[0]['nprice'] : '';
                            // Last Year Total Service                            
                            $serviceSalesDetailsArrLastyear = $this->GraphsOwner_model
                                                     ->getSalesDataforTotalSales($whereConditions,$whereLastYear)
                                                     ->result_array();
                            $serviceTotalSalesLastYear = isset($serviceSalesDetailsArrLastyear[0]['nprice']) && !empty($serviceSalesDetailsArrLastyear[0]['nprice']) ? $serviceSalesDetailsArrLastyear[0]['nprice'] : '';
                            // GIFT CARDS TOTAL
                            $gcSalesDetailsArr = $this->GraphsOwner_model
                                                     ->getGiftDataforTotalSales($whereConditions,$where)
                                                     ->result_array();
                            $gcTotalSales = isset($gcSalesDetailsArr[0]['nprice']) && !empty($gcSalesDetailsArr[0]['nprice']) ? $gcSalesDetailsArr[0]['nprice']:'';
                            // Last Year Gift Card
                            $gcSalesDetailsArrLastyear = $this->GraphsOwner_model
                                                     ->getGiftDataforTotalSales($whereConditions,$whereLastYear)
                                                     ->result_array();
                            $gcTotalSalesLastYear = isset($gcSalesDetailsArrLastyear[0]['nprice']) && !empty($gcSalesDetailsArrLastyear[0]['nprice']) ? $gcSalesDetailsArrLastyear[0]['nprice'] : '';
                            
                            pa($retailTotalSales,'retailTotalSales'); 
                            pa($serviceTotalSales,'serviceTotalSales'); 
                            pa($gcTotalSales,'gcTotalSales',false); 

                            pa($retailTotalSalesLastYear,'retailTotalSalesLastYear'); 
                            pa($serviceTotalSalesLastYear,'serviceTotalSalesLastYear'); 
                            pa($gcTotalSalesLastYear,'gcTotalSalesLastYear',false); 

                            $totalValues["graphs_data"]["current_value"] = $retailTotalSales+$serviceTotalSales+$gcTotalSales;
                            $totalValues["graphs_data"]["last_year_value"] = $retailTotalSalesLastYear+$serviceTotalSalesLastYear+$gcTotalSalesLastYear;
                            $currentyear = date('Y');
                            $start_date_monthname =  date("M", strtotime($startDate));
                            $end_date_monthname =  date("M", strtotime($endDate));
                            
                            if($dayRangeType=='Monthly'){
                                $dayRangeTypekey = '4 weeks';
                            }elseif ($dayRangeType=='Yearly') {
                                $dayRangeTypekey = 'Year';
                            }elseif($dayRangeType=='threemonths'){
                                $dayRangeTypekey = $start_date_monthname.'-'.$end_date_monthname;
                            }else{
                                $dayRangeTypekey = '';
                            }
                            
                            $totalValues["graphs_data"]["key"] = "";
                            if(!empty($totalValues)){
                                $tempArr = array();
                                foreach ($totalValues as $totals) {
                                   $totalSales['current_value'] =  $this->Common_model->appCloudNumberFormat($totals['current_value'], 2);
                                   $totalSales['key'] = $dayRangeTypekey;
                                   $totalSales['last_year_value'] = $this->Common_model->appCloudNumberFormat($totals['last_year_value'], 2);
                                   $tempArr[] = $totalSales;
                                 }
                                $dataArray["graph_data"] = $tempArr;
                              }
                               $response['values'][] = $dataArray;
                             }
                        $response['start_date'] = $startDate;
                        $response['end_date'] = $endDate;
                        $response["status"] = true;
                }else{
                    $response["status"] = false;
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
    function setOwnerTotalSalesDataForGraphs($dayRangeType="today",$salon_id="")
        { 
            $this->currentDate = getDateFn();
            $getAllSalons = $this->Common_model->getAllSalons($salon_id);
            //pa($getAllSalons,'getAllSalons',true);
            $processedSalons = array();
            if(isset($getAllSalons["mill_salons"]) && !empty($getAllSalons["mill_salons"]))
            {
                foreach($getAllSalons["mill_salons"] as $salonsData)
                {
                    pa('',"Reports Cron Running For--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                    $this->salon_id = $salonsData['salon_id'];
                    $salonDetails = $this->Common_model->getSalonInfoBy($this->salon_id);
                    if(!in_array($this->salon_id, $processedSalons))
                    {
                      if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"]) && $salonDetails['salon_info']["millennium_enabled"]=="Yes"){
                        // Database Log
                        $log['AccountNo'] = $salonDetails['salon_info']['salon_code'];
                        $log['salon_id'] = $salonDetails['salon_info']['salon_id'];
                        $log['StartingTime'] = date('Y-m-d H:i:s');
                        $log['whichCron'] = 'setOwnerTotalSalesDataForGraphs';
                        //$log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        $log['CronUrl'] = MAIN_SERVER_URL.'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/'.$dayRangeType.'/'.$salon_id;
                       
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
                        //pa($getAllRetailData,'getAllRetailData');
                        if(isset($getAllRetailData) && !empty($getAllRetailData['values'])){
                            foreach($getAllRetailData['values'] as $resValue){
                                $salonId = $resValue["location_id"];
                                $processedSalons[] = $salonId;
                                if(isset($resValue["graph_data"]) && !empty($resValue["graph_data"]))
                                {
                                    //pa($resValue["graph_data"],'Graph Data',false);
                                    foreach($resValue['graph_data'] as $resValuess){
                                        array_walk($resValuess, function (&$value,&$key) { 
                                            if(($key == 'current_value') || ($key == 'last_year_value'))
                                                $value = number_format($value, 2, '.', '');
                                        });
                                        if($dayRangeType=='Previousmonths'){
                                            $this->startDate = $resValuess['start_date'];
                                            $this->endDate = $resValuess['end_date'];
                                            $insertdayRangeType = 'Yearly';
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
                                       //    pa($dataArray);
                                        // compare database
                                        $reportsWhere = array('salon_id' => $salonId,'dayRangeType' => $insertdayRangeType);
                                        //pa($reportsWhere,'reports',true);

                                        $reportsDataForSalon = $this->GraphsOwner_model
                                                               ->compareTotalSalesOwnerReportsData($reportsWhere)
                                                               ->row_array();

                                        if(!empty($reportsDataForSalon)){
                                             $diff_array = array_diff_assoc($dataArray, $reportsDataForSalon);
                                             if(empty($diff_array)){
                                               pa('No Updates'); 
                                             }else{
                                                // update
                                                $diff_array['insert_status'] = OwnerTotalSalesDataForGraphs::UPDATED;
                                                $diff_array["updatedDate"] = date("Y-m-d H:i:s");
                                                $diff_array["start_date"] = $this->startDate;
                                                $diff_array["end_date"] = $this->endDate;
                                                try {
                                                    $reportsWhere = array('salon_id' => $salonId,'dayRangeType' => $insertdayRangeType);
                                                    $update = $this->GraphsOwner_model->updateTotalSalesOwnerReportsData($reportsWhere,$diff_array);
                                                    pa($diff_array,'Reports Data updated Successfully');
                                                } catch (Exception $e) {
                                                    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
                                                }
                                             }

                                        }else{
                                            
                                              // Insert Data
                                                $dataArray["insert_status"] = OwnerTotalSalesDataForGraphs::INSERTED;
                                                $dataArray["insertedDate"] = date("Y-m-d H:i:s");
                                                $dataArray["updatedDate"] = date("Y-m-d H:i:s");
                                                try {
                                                    $insert = $this->GraphsOwner_model->insertTotalSalesOwnerReportsData($dataArray);
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
                } // for each
            }else{
                echo "No SalonId's In Server";
            }
            
       } 
   
 }       