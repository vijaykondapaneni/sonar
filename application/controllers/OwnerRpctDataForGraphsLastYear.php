<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OwnerRpctDataForGraphsLastYear extends CI_Controller
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
                    $this->startDate = date("Y-")."01-01";
                    $this->endDate = date("Y-")."12-31";
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
                $numberOfYears = $data['numberOfYears'];
                $lastYearnumberOfYears = $numberOfYears+1;
                $relatedSalons = $this->Saloncloudsplus_model->relatedSalons($salon_id);
                $relatedSalons = $this->Saloncloudsplus_model->relatedSalons($salon_id);
                if(!empty($relatedSalons)){
                    if($dayRangeType=="Yearly"){
                        $response['values'] = array();
                        foreach ($relatedSalons as $eachSalon) {
                            $salon_id = $eachSalon['salon_id'];
                            $salon_name = $eachSalon['salon_name'];
                            $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)
                                                   ->row_array();
                            $salonAccountNo = $salonDetails['salon_account_id'];
                            $rpct_type = $salonDetails['rpct_type'];
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
                                $year = date("Y",strtotime("-$numberOfYears year"));
                                $firstDayOfMonth = $date->format("Y-m-d");
                                $lastdayOfMonth = date('Y-m-t',strtotime($date->format("Y-m-d")));
                                $last_year_start_date = strtotime($firstDayOfMonth."-$numberOfYears year");
                                $firstDayOfMonth = date('Y-m-d', $last_year_start_date);
                                $last_year_end_date = strtotime($lastdayOfMonth." -$numberOfYears year");
                                $lastdayOfMonth = date('Y-m-d', $last_year_end_date);
                                $prebookArray[$monthName] = array();
                                $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund' => 'false');
                                $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                                $resultAssocArr = $this->GraphsOwner_model
                                                          ->getRPCTDetailsArr($whereConditions,$where)
                                                          ->row_array();
                                $prebookArray[$monthName]['service'] = !empty($resultAssocArr)? $resultAssocArr : array();
                                $whereConditions =  array('account_no' =>$salonAccountNo);
                                $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                                $productSalesDetailsArr = $this->GraphsOwner_model
                                                          ->getRetailSalesDetailsArr($whereConditions,$where)
                                                          ->row_array();

                                $prebookArray[$monthName]['retail'] = !empty($productSalesDetailsArr)? $productSalesDetailsArr : array();
                                 // get unique clients count
                                $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                                $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund' => 'false');

                                $serviceSalesClientIds = $this->GraphsOwner_model
                                         ->getRPCTServiceSalesClientIdsForClientIds($whereConditions,$where)
                                         ->result_array();
                                //pa($this->db->last_query());         
                                $serviceClientIds = !empty($serviceSalesClientIds)?array_column($serviceSalesClientIds, "service_client_ids"):array();
                                $serviceClientCount = !empty($serviceClientIds) ? count($serviceClientIds) : 0;
                                $productSalesClientIds = $this->GraphsOwner_model
                                         ->getRPCTProductSalesClientIdsForClientIds($whereConditions,$where)
                                         ->result_array();
                               // pa($this->db->last_query());         
                                $productClientIds = !empty($productSalesClientIds)?array_column($productSalesClientIds, "retail_client_ids"):array();
                                $retailClientCount = !empty($productClientIds) ? count($productClientIds) : 0;
                                if(!empty($productSalesClientIds))
                                    {
                                        $productClientIds = array_column($productSalesClientIds, "retail_client_ids");
                                        $retailClientCount = count($productClientIds);
                                    }
                                else
                                    {
                                        $productClientIds = array();
                                        $retailClientCount = 0;
                                    }
                                $commonClietIds = !empty($serviceClientIds) && !empty($productClientIds) ? array_intersect($serviceClientIds, $productClientIds) : array();
                                $commonClientsCount = !empty($commonClietIds) ? count($commonClietIds) : 0; 
                                $totalUniqueClients = ($serviceClientCount - $commonClientsCount) + ($retailClientCount - $commonClientsCount) + $commonClientsCount;
                                
                                //pa($totalUniqueClients,'totalUniqueClients',false);
                                $prebookArray[$monthName]['totalUniqueClients'] = $totalUniqueClients;    
                                                 
                                  
                                
                                //LAST YEAR DATA
                                $month = $date->format("m");
                                $month = ltrim($month, '0');
                                $monthName = $date->format("F");
                                $year = date("Y",strtotime("-$lastYearnumberOfYears year"));
                                $last_year_start_date = strtotime($startDate." -$lastYearnumberOfYears year");
                                $lastYearStartDate = date('Y-m-d', $last_year_start_date);
                                $last_year_end_date = strtotime($endDate."-$lastYearnumberOfYears year");
                                $lastYearEndDate = date('Y-m-d', $last_year_end_date);
                                $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund' => 'false');
                                $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                                $resultAssocArr = $this->GraphsOwner_model
                                                          ->getRPCTDetailsArr($whereConditions,$where)
                                                          ->row_array();

                                $prebookArray[$monthName]['last_year_service'] = !empty($resultAssocArr)? $resultAssocArr : array();
                                $whereConditions =  array('account_no' =>$salonAccountNo);
                                $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                                $productSalesDetailsArr = $this->GraphsOwner_model
                                                          ->getRetailSalesDetailsArr($whereConditions,$where)
                                                          ->row_array();

                                $prebookArray[$monthName]['last_year_retail'] = !empty($productSalesDetailsArr)? $productSalesDetailsArr : array();
                                 // get unique clients count
                                $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                                $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund' => 'false');

                                $serviceSalesClientIds = $this->GraphsOwner_model
                                         ->getRPCTServiceSalesClientIdsForClientIds($whereConditions,$where)
                                         ->result_array();
                                //pa($this->db->last_query());         
                                $serviceClientIds = !empty($serviceSalesClientIds)?array_column($serviceSalesClientIds, "service_client_ids"):array();
                                $serviceClientCount = !empty($serviceClientIds) ? count($serviceClientIds) : 0;
                                $productSalesClientIds = $this->GraphsOwner_model
                                         ->getRPCTProductSalesClientIdsForClientIds($whereConditions,$where)
                                         ->result_array();
                               // pa($this->db->last_query());         
                                $productClientIds = !empty($productSalesClientIds)?array_column($productSalesClientIds, "retail_client_ids"):array();
                                $retailClientCount = !empty($productClientIds) ? count($productClientIds) : 0;
                                if(!empty($productSalesClientIds))
                                    {
                                        $productClientIds = array_column($productSalesClientIds, "retail_client_ids");
                                        $retailClientCount = count($productClientIds);
                                    }
                                else
                                    {
                                        $productClientIds = array();
                                        $retailClientCount = 0;
                                    }
                                $commonClietIds = !empty($serviceClientIds) && !empty($productClientIds) ? array_intersect($serviceClientIds, $productClientIds) : array();
                                $commonClientsCount = !empty($commonClietIds) ? count($commonClietIds) : 0; 
                                $totalUniqueClients_lastyear = ($serviceClientCount - $commonClientsCount) + ($retailClientCount - $commonClientsCount) + $commonClientsCount;
                                
                                //pa($totalUniqueClients,'totalUniqueClients',false);
                                $prebookArray[$monthName]['totalUniqueClients_lastyear'] = $totalUniqueClients_lastyear;  

                                $prebookArray[$monthName]['service']['start_date'] = $firstDayOfMonth;
                                $prebookArray[$monthName]['service']['end_date'] = $lastdayOfMonth; 
                            }
                            if(!empty($prebookArray)){
                                $tempArr = array();
                                foreach($prebookArray as $month_week_name => $dayRanges) {

                                    if($rpct_type==2){
                                        $allArray["current_value"] = isset($dayRanges['retail']["nprice"]) && !empty($dayRanges['retail']["nprice"]) && isset($dayRanges['totalUniqueClients']) && !empty($dayRanges['totalUniqueClients']) ? $this->Common_model->appCloudNumberFormat($dayRanges['retail']["nprice"]/$dayRanges['totalUniqueClients'],2):'0';
                                        $allArray["key"] =  substr($month_week_name, 0, 3);
                                           //LAST YEAR DATA
                                        $allArray["last_year_value"] = isset($dayRanges['last_year_retail']["nprice"]) && !empty($dayRanges['last_year_retail']["nprice"]) && isset($dayRanges['totalUniqueClients_lastyear']) && !empty($dayRanges['totalUniqueClients_lastyear']) ? $this->Common_model->appCloudNumberFormat($dayRanges['last_year_retail']["nprice"]/$dayRanges['totalUniqueClients_lastyear'],2):'0';

                                    }else{
                                        $allArray["current_value"] = isset($dayRanges['retail']["nprice"]) && !empty($dayRanges['retail']["nprice"]) && isset($dayRanges['service']["invoice_count"]) && !empty($dayRanges['service']["invoice_count"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['retail']["nprice"]/$dayRanges['service']["invoice_count"],2):'0';
                                        $allArray["key"] =  substr($month_week_name, 0, 3);
                                           //LAST YEAR DATA
                                        $allArray["last_year_value"] = isset($dayRanges['last_year_retail']["nprice"]) && !empty($dayRanges['last_year_retail']["nprice"]) && isset($dayRanges['last_year_service']["invoice_count"]) && !empty($dayRanges['last_year_service']["invoice_count"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['last_year_retail']["nprice"]/$dayRanges['last_year_service']["invoice_count"],2):'0';
                                    }

                                   

                                   $allArray["key"] =  substr($month_week_name, 0, 3);
                                   $allArray["start_date"] = $dayRanges['service']['start_date'];
                                   $allArray["end_date"] = $dayRanges['service']['end_date'];

                                   $tempArr[] = $allArray;
                                 }
                                $dataArray["graph_data"] = $tempArr;
                              }
                         $response['values'][] = $dataArray;
                            
                        }
                        $response['start_date'] = $startDate;
                        $response['end_date'] = $endDate;
                        $response["status"] = true;
                    }

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
    function setOwnerRpctDataForGraphsLastYear($dayRangeType="today",$year=1,$salon_id="")
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
                        $log['whichCron'] = 'setOwnerRpctDataForGraphsLastYear';
                        $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        $log['CronType'] = 1;
                        $log['id'] = 0;
                        $log_id = $this->Common_model->saveMillCronLogs($log); 
                        // GET START DATE AND END DATE AS PER PARAMETERS
                      
                        $this->__getStartEndDate($dayRangeType,$year);

                        $arrData['start_date'] = $this->startDate;
                        $arrData['end_date'] = $this->endDate;
                        $arrData['status'] = true;
                        $arrSalonDetails['salon_id'] = $this->salon_id;
                        $arrSalonDetails['start_date'] = $this->startDate;
                        $arrSalonDetails['end_date'] = $this->endDate;
                        $arrSalonDetails['dayRangeType'] = $dayRangeType;
                        $arrSalonDetails['numberOfYears'] = $year;                        
                        $getAllRetailData = $this->__getSalonReportForOwnerGraphs($arrSalonDetails);
                        
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
                                        $this->startDate = $resValuess['start_date'];
                                        $this->endDate = $resValuess['end_date'];
                                        $dataArray['salon_id'] = $salonId;
                                        $dataArray['dayRangeType'] = $dayRangeType;
                                        $dataArray['start_date'] = $this->startDate;
                                        $dataArray['end_date'] = $this->endDate;
                                        $dataArray['current_value'] = $resValuess['current_value'];
                                        $dataArray['key'] = $resValuess['key'];
                                        $dataArray['last_year_value'] = $resValuess['last_year_value'];
                                        // compare database
                                        $reportsWhere = array('salon_id' => $salonId,'dayRangeType' => $dayRangeType,'key' => $resValuess['key'],'start_date' => $this->startDate,'end_date' =>$this->endDate);

                                        $reportsDataForSalon = $this->GraphsOwner_model
                                                               ->compareRPCTOwnerReportsData($reportsWhere)
                                                               ->row_array();
                                        if(!empty($reportsDataForSalon)){
                                             $diff_array = array_diff_assoc($dataArray, $reportsDataForSalon);
                                             if(empty($diff_array)){
                                                pa('No Updates'); 
                                             }else{
                                                // update
                                                $diff_array['insert_status'] = OwnerRpctDataForGraphsLastYear::UPDATED;
                                                $diff_array["updatedDate"] = date("Y-m-d H:i:s");
                                                try {
                                                    $reportsWhere = array('salon_id' => $salonId,'key' => $resValuess['key'],'dayRangeType' => $dayRangeType,'id' => $reportsDataForSalon['id']);
                                                    $update = $this->GraphsOwner_model->updateRPCTOwnerReportsData($reportsWhere,$diff_array);
                                                    pa($diff_array,'Reports Data updated Successfully');
                                                    pa($reportsDataForSalon['id'],'DB ID');
                                                } catch (Exception $e) {
                                                    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
                                                }
                                             }

                                        }else{
                                           
                                              // Insert Data
                                                $dataArray["insert_status"] = OwnerRpctDataForGraphsLastYear::INSERTED;
                                                $dataArray["insertedDate"] = date("Y-m-d H:i:s");
                                                $dataArray["updatedDate"] = date("Y-m-d H:i:s");
                                                try {
                                                    $insert = $this->GraphsOwner_model->insertRPCTOwnerReportsData($dataArray);
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
                } // for each close
            }else{
                echo "No SalonId's In Server";
            }
            
       } 
   
 }       