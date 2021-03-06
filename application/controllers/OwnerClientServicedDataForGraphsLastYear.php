<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OwnerClientServicedDataForGraphsLastYear extends CI_Controller
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
            case today:
                $this->startDate = $currentDate;
                $this->endDate = $currentDate;
            break;                          
            case lastMonth:
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
            break;
            case Monthly:
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $currentDate;
            break;
            case threemonths:
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
            break;
            case Yearly:                                   
                $this->startDate = date("Y-")."01-01";
                $this->endDate = date("Y-")."12-31";
            break;         
            default:
                $this->startDate =  $currentDate;
                $this->endDate   =  $currentDate;
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
                if(!empty($relatedSalons)){
                    if($dayRangeType=="Yearly"){
                        $response['values'] = array();
                        foreach ($relatedSalons as $eachSalon) {
                            $salon_id = $eachSalon['salon_id'];
                            $salon_name = $eachSalon['salon_name'];
                            $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)
                                                   ->row_array();
                            $salonAccountNo = $salonDetails['salon_account_id'];
                            if(!empty($salonDetails))
                            {
                                $salonAccountNo = $salonDetails['salon_account_id'];
                            }
                            else
                            {
                                continue;
                            }
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

                                $getUniqueClientTickets = $this->GraphsOwner_model
                                                          ->getUnqiueClients($salonAccountNo,$month,$year)
                                                          ->result_array();

                                $prebookArray[$monthName]['Both']['New'] = !empty($getUniqueClientTickets) ? $getUniqueClientTickets : array(); 
                                                         
                                
                                $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund' => 'false');
                                $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                                $serviceSalesDetailsArrNew = $this->GraphsOwner_model
                                                          ->getServiceSalesData($whereConditions,$where)
                                                          ->row_array();

                                $prebookArray[$monthName]['Services']['New'] = !empty($serviceSalesDetailsArrNew)? $serviceSalesDetailsArrNew : array();                           
                                
                                //LAST YEAR DATA
                                $month = $date->format("m");
                                $month = ltrim($month, '0');
                                $monthName = $date->format("F");
                                $year = date("Y",strtotime("-$lastYearnumberOfYears year"));
                                $last_year_start_date = strtotime($startDate." -$lastYearnumberOfYears year");
                                $lastYearStartDate = date('Y-m-d', $last_year_start_date);
                                $last_year_end_date = strtotime($endDate."-$lastYearnumberOfYears year");
                                $lastYearEndDate = date('Y-m-d', $last_year_end_date);

                                $getUniqueClientTicketsLastyear = $this->GraphsOwner_model
                                                                  ->getUnqiueClients($salonAccountNo,$month,$year)
                                                                  ->result_array(); 

                                $prebookArray[$monthName]['last_year_Both']['New'] = !empty($getUniqueClientTicketsLastyear)? $getUniqueClientTicketsLastyear : array();
                                
                                $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund' => 'false');
                                $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                                $serviceSalesDetailsArrNewLastyear = $this->GraphsOwner_model
                                                          ->getServiceSalesData($whereConditions,$where)
                                                          ->row_array();

                                $prebookArray[$monthName]['last_year_Services']['New'] = !empty($serviceSalesDetailsArrNewLastyear)? $serviceSalesDetailsArrNewLastyear : array();
                                $prebookArray[$monthName]['dates']['service']['start_date'] = $firstDayOfMonth;
                                $prebookArray[$monthName]['dates']['service']['end_date'] = $lastdayOfMonth;


                            }
                           if(!empty($prebookArray))
                            {
                                $tempArr = array();
                                foreach($prebookArray as $month_week_name => $dayRanges)
                                {
                                    $servicesArray = $dayRanges['Services'];
                                    $lastYearServicesArray = $dayRanges['last_year_Services'];
                                    $datesArray = $dayRanges['dates'];
                                    $uniqueInvoiceArray = $dayRanges['Both'];
                                    $lastYearuniqueInvoiceArray = $dayRanges['last_year_Both'];
                                    
                                    $uniqueInvoicesArr[] = isset($uniqueInvoiceArray['New']) ? 
                                    array_column($uniqueInvoiceArray['New'], "cinvoiceno") : array();

                                    $totalUniqueInvoices = !empty($uniqueInvoicesArr) ? count($uniqueInvoicesArr):0;


                                    
                                    $prebookPercentage["current_value"] = isset($totalUniqueInvoices) && isset($servicesArray['New']['tdatetime']) && !empty($servicesArray['New']['tdatetime'])  ? $this->Common_model->appCloudNumberFormat($totalUniqueInvoices/$servicesArray['New']['tdatetime'], 2):'0';

                                    $prebookPercentage["key"] = substr($month_week_name, 0, 3);


                                    $uniqueInvoiceslastArr[] = isset($lastYearuniqueInvoiceArray['New']) ? array_column($lastYearuniqueInvoiceArray['New'], "cinvoiceno") : 0;

                                    $totalUniqueInvoiceslast = !empty($uniqueInvoiceslastArr) ? count($uniqueInvoiceslastArr) : 0;

                                    $prebookPercentage["last_year_value"] = isset($totalUniqueInvoiceslast) && isset($lastYearServicesArray['New']['tdatetime']) && !empty($lastYearServicesArray['New']['tdatetime'])  ? $this->Common_model->appCloudNumberFormat($totalUniqueInvoiceslast/$lastYearServicesArray['New']['tdatetime'], 2):'0';

                                    $prebookPercentage["start_date"] = $datesArray['service']['start_date'];
                                    $prebookPercentage["end_date"] = $datesArray['service']['end_date'];
                                    $tempArr[] = $prebookPercentage;
                                    
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
    function setOwnerClientServicedDataForGraphsLastYear($dayRangeType="today",$year=1,$salon_id="")
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
                        $log['whichCron'] = 'setOwnerClientServicedDataForGraphsLastYear';
                        $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
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
                                                               ->compareClientServiceOwnerReportsData($reportsWhere)
                                                               ->row_array();
                                        if(!empty($reportsDataForSalon)){
                                             $diff_array = array_diff_assoc($dataArray, $reportsDataForSalon);
                                             if(empty($diff_array)){
                                              pa('No Updates');
                                             }else{
                                                // update
                                                $diff_array['insert_status'] = OwnerClientServicedDataForGraphsLastYear::UPDATED;
                                                $diff_array["updatedDate"] = date("Y-m-d H:i:s");
                                                try {
                                                    $reportsWhere = array('salon_id' => $salonId,'key' => $resValuess['key'],'dayRangeType' => $dayRangeType,'id' => $reportsDataForSalon['id']);
                                                    $update = $this->GraphsOwner_model->updateClientServiceOwnerReportsData($reportsWhere,$diff_array);
                                                    pa($diff_array,'Reports Data updated Successfully'); 
                                                    pa($reportsDataForSalon['id'],'DB ID');
                                                } catch (Exception $e) {
                                                    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
                                                }
                                             }

                                        }else{
                                            
                                              // Insert Data
                                                $dataArray["insert_status"] = OwnerClientServicedDataForGraphsLastYear::INSERTED;
                                                $dataArray["insertedDate"] = date("Y-m-d H:i:s");
                                                $dataArray["updatedDate"] = date("Y-m-d H:i:s");
                                                try {
                                                    $insert = $this->GraphsOwner_model->insertClientServiceOwnerReportsData($dataArray);
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