<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OwnerRpctDataForGraphs extends CI_Controller
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
                    $this->startDate =  $this->currentDate;;
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
                        // $year = date("Y");
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
                                                  ->getRPCTDetailsArr($whereConditions,$where)
                                                  ->row_array();
                        $prebookArray[$monthName]['service'] = !empty($resultAssocArr)? $resultAssocArr : array();

                        $whereConditions =  array('account_no' =>$salonAccountNo);
                        $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                        $productSalesDetailsArr = $this->GraphsOwner_model
                                                  ->getRetailSalesDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$monthName]['retail'] = !empty($productSalesDetailsArr)? $productSalesDetailsArr : array();



                        $serviceSalesDetailsArr = $this->GraphsOwner_model
                                                  ->getServiceSalesDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$monthName]['service_sales'] = !empty($serviceSalesDetailsArr)? $serviceSalesDetailsArr : array();



                        // get service sales  unique clients count

                        $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year";
                        $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund' => 'false');

                        $serviceSalesDetailsArrNew = $this->GraphsOwner_model->getRPCTServiceDetailsArr($whereConditions,$where)->row_array();

                        if(!empty($serviceSalesDetailsArrNew))
                        {
                            $prebookArray[$monthName]['service_client_count'] = $serviceSalesDetailsArrNew;
                        }
                        else
                        {
                            $prebookArray[$monthName]['service_client_count'] = array();
                        }

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
                        // $year = date("Y",strtotime("-1 year"));
                        $lastyear =  date("Y",strtotime($firstDayOfMonth));
                        $year = $lastyear -1;
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

                        $serviceSalesDetailsArr = $this->GraphsOwner_model
                                                  ->getServiceSalesDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$monthName]['last_year_service_sales'] = !empty($serviceSalesDetailsArr)? $serviceSalesDetailsArr : array();



                        //service client id for rpct

                        $whereConditions = array('account_no' =>$salonAccountNo,'lrefund' => 'false');
                        $serviceSalesDetailsArrNewLastyear = $this->GraphsOwner_model->getRPCTServiceDetailsArr($whereConditions,$where)->row_array();


                        if(!empty($serviceSalesDetailsArrNewLastyear))
                        {
                            $prebookArray[$monthName]['last_year_service_client_count'] = $serviceSalesDetailsArrNewLastyear;
                        }
                        else
                        {
                            $prebookArray[$monthName]['last_year_service_client_count'] = array();
                        }

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

                        $prebookArray[$monthName]['dates']['retail']['start_date'] = $firstDayOfMonth;
                        $prebookArray[$monthName]['dates']['retail']['end_date'] = $lastdayOfMonth; 
                    }
                    if(!empty($prebookArray)){
                            $tempArr = array();
                            foreach($prebookArray as $month_week_name => $dayRanges) {
                               $datesArray = $dayRanges['dates'];
                               //RPCT CALCULATION CHANGE FOR LEVEL SALON
                               if($rpct_type==2){
                                
                               $allArray["current_value"] = isset($dayRanges['retail']["nprice"]) && !empty($dayRanges['retail']["nprice"]) && isset($dayRanges['service']["invoice_count"]) && !empty($dayRanges['service']["invoice_count"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['retail']["nprice"]/$dayRanges['totalUniqueClients'],2):'0';

                               $allArray["key"] =  substr($month_week_name, 0, 3);
                               //LAST YEAR DATA
                               $allArray["last_year_value"] = isset($dayRanges['last_year_retail']["nprice"]) && !empty($dayRanges['last_year_retail']["nprice"]) && isset($dayRanges['last_year_service']["invoice_count"]) && !empty($dayRanges['last_year_service']["invoice_count"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['last_year_retail']["nprice"]/$dayRanges['totalUniqueClients_lastyear'],2):'0';
                               }elseif($rpct_type==3){
                                
                               $allArray["current_value"] = isset($dayRanges['retail']["nprice"]) && !empty($dayRanges['retail']["nprice"]) && isset($dayRanges['service_sales']["nprice"]) && !empty($dayRanges['service_sales']["nprice"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['retail']["nprice"]/$dayRanges['service_sales']["nprice"],2):'0';

                               $allArray["key"] =  substr($month_week_name, 0, 3);
                               //LAST YEAR DATA
                               $allArray["last_year_value"] = isset($dayRanges['last_year_retail']["nprice"]) && !empty($dayRanges['last_year_retail']["nprice"]) && isset($dayRanges['last_year_service_sales']["nprice"]) && !empty($dayRanges['last_year_service_sales']["nprice"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['last_year_retail']["nprice"]/$dayRanges['last_year_service_sales']["nprice"],2):'0';
                               }else{

                                $allArray["current_value"] = isset($dayRanges['retail']["nprice"]) && !empty($dayRanges['retail']["nprice"]) && isset($dayRanges['service']["invoice_count"]) && !empty($dayRanges['service']["invoice_count"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['retail']["nprice"]/$dayRanges['service']["invoice_count"],2):'0';
                               $allArray["key"] =  substr($month_week_name, 0, 3);
                               //LAST YEAR DATA
                               $allArray["last_year_value"] = isset($dayRanges['last_year_retail']["nprice"]) && !empty($dayRanges['last_year_retail']["nprice"]) && isset($dayRanges['last_year_service']["invoice_count"]) && !empty($dayRanges['last_year_service']["invoice_count"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['last_year_retail']["nprice"]/$dayRanges['last_year_service']["invoice_count"],2):'0';
                               }
                                  
                              $allArray["start_date"] = $datesArray['retail']['start_date'];
                              $allArray["end_date"] = $datesArray['retail']['end_date']; 
                              $tempArr[] = $allArray;
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
                    $rpct_type = $salonDetails['rpct_type'];
                    $dataArray['location_id'] = $salon_id;
                    $dataArray['location_name'] = $salon_name;
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek)){
                        $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else{
                        $fourWeeksArr = getLast4WeekRanges(date('Y'));
                    }
                    $salesArray = array();
                    $week_number=0;
                    $week_number_db=1;
                    $i=1;
                    foreach ($fourWeeksArr as $dates) {
                        $startDayOfWeek = $dates['start_date'];
                        $endDayOfWeek = $dates['end_date'];
                        $current_week = $dates['current_week'];
                        pa($startDayOfWeek.' To '.$endDayOfWeek,'Start and End DayOfWeek',false);
                        $prebookArray[$week_number_db] = array();
                        $whereConditions =  array('account_no' =>$salonAccountNo,'lrefund'=>'false');
                       /* $where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year AND WEEK(tdatetime) =$week";*/
                        $where = "tdatetime >='".$startDayOfWeek."' AND tdatetime<='".$endDayOfWeek."'";
                        pa($where,'currentYear');
                       
                        $resultAssocArr = $this->GraphsOwner_model
                                                  ->getRPCTDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$week_number_db]['service'] = !empty($resultAssocArr)? $resultAssocArr : array();
                        $whereConditions =  array('account_no' =>$salonAccountNo);
                        /*$where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year AND WEEK(tdatetime) =$week";*/
                        $where = "tdatetime >='".$startDayOfWeek."' AND tdatetime<='".$endDayOfWeek."'";
                        $productSalesDetailsArr = $this->GraphsOwner_model
                                                  ->getRetailSalesDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$week_number_db]['retail'] = !empty($productSalesDetailsArr)? $productSalesDetailsArr : array(); 

                        $serviceSalesDetailsArr = $this->GraphsOwner_model
                                                  ->getServiceSalesDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$week_number_db]['service_sales'] = !empty($serviceSalesDetailsArr)? $serviceSalesDetailsArr : array(); 



                        // Get unique client ids for level salon
                        $whereConditions = array('account_no' =>$salonAccountNo,'lrefund' => 'false');
                        $where = "tdatetime >='".$startDayOfWeek."' AND tdatetime<='".$endDayOfWeek."'";

                        $serviceSalesInvoicesArr = $this->GraphsOwner_model->getRPCTServiceDetailsArr($whereConditions,$where)->row_array();

                        if(!empty($serviceSalesInvoicesArr))
                        {
                            $prebookArray[$week_number_db]['service_client_count'] = $serviceSalesInvoicesArr;
                        }
                        else
                        {
                            $prebookArray[$week_number_db]['service_client_count'] = array();
                        }

                        // get unique client ids  for level salon

                        $where = "tdatetime >='".$startDayOfWeek."' AND tdatetime<='".$endDayOfWeek."'";
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
                        //pa($this->db->last_query());         
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

                      
                       $prebookArray[$week_number_db]['totalUniqueClients'] = $totalUniqueClients;

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
                                                  ->getRPCTDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$week_number_db]['last_year_service'] = !empty($resultAssocArrLastyear)? $resultAssocArrLastyear : array();


                        $whereConditions =  array('account_no' =>$salonAccountNo);
                        /*$where = "MONTH(tdatetime) = $month AND YEAR(tdatetime) =$year AND WEEK(tdatetime) =$week";*/
                        $where = "tdatetime >='".$startDayOfWeekLastYear."' AND tdatetime<='".$endDayOfWeekLastYear."'";
                        $productSalesDetailsArr = $this->GraphsOwner_model
                                                  ->getRetailSalesDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$week_number_db]['last_year_retail'] = !empty($productSalesDetailsArr)? $productSalesDetailsArr : array();

                        $serviceSalesDetailsArr = $this->GraphsOwner_model
                                                  ->getServiceSalesDetailsArr($whereConditions,$where)
                                                  ->row_array();

                        $prebookArray[$week_number_db]['last_year_service_sales'] = !empty($serviceSalesDetailsArr)? $serviceSalesDetailsArr : array();


                        $whereConditions = array('account_no' =>$salonAccountNo,'lrefund' => 'false');
                        $where = "tdatetime >='".$startDayOfWeekLastYear."' AND tdatetime<='".$endDayOfWeekLastYear."'";

                        $serviceSalesInvoicesArr = $this->GraphsOwner_model->getRPCTServiceDetailsArr($whereConditions,$where)->row_array();

                        if(!empty($serviceSalesInvoicesArr))
                        {
                            $prebookArray[$week_number_db]['last_year_service_client_count'] = $serviceSalesInvoicesArr;
                        }
                        else
                        {
                            $prebookArray[$week_number_db]['last_year_service_client_count'] = array();
                        }

                        // get unique client ids  for level salon

                        $where = "tdatetime >='".$startDayOfWeekLastYear."' AND tdatetime<='".$endDayOfWeekLastYear."'";
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
                       
                       $prebookArray[$week_number_db]['totalUniqueClients_lastyear'] = $totalUniqueClients;
                        
                        $week_number++;
                        $week_number_db++;
                    }
                    if(!empty($prebookArray))
                    {
                        $tempArr = array();
                        foreach($prebookArray as $month_week_name => $dayRanges)
                        {

                            $datesArray = $dayRanges['Dates'];

                            if($rpct_type==2){
                                $allArray["current_value"] = isset($dayRanges['retail']["nprice"]) && !empty($dayRanges['retail']["nprice"]) && isset($dayRanges['totalUniqueClients']) && !empty($dayRanges['totalUniqueClients']) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($dayRanges['retail']["nprice"],$dayRanges["totalUniqueClients"], 2):'0';
                                   $allArray["key"] = "Week ".$month_week_name;
                                //LAST YEAR DATA
                                $allArray["last_year_value"] = isset($dayRanges['last_year_retail']["nprice"]) && !empty($dayRanges['last_year_retail']["nprice"]) && isset($dayRanges['totalUniqueClients_lastyear']) && !empty($dayRanges['totalUniqueClients_lastyear']) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($dayRanges['last_year_retail']["nprice"],$dayRanges["totalUniqueClients_lastyear"], 2):'0';

                            }
                            elseif($rpct_type==3){
                                $allArray["current_value"] = isset($dayRanges['retail']["nprice"]) && !empty($dayRanges['retail']["nprice"]) && isset($dayRanges['service_sales']["nprice"]) && !empty($dayRanges['service_sales']["nprice"]) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($dayRanges['retail']["nprice"],$dayRanges['service_sales']["nprice"], 2):'0';
                                   $allArray["key"] = "Week ".$month_week_name;
                                //LAST YEAR DATA
                                $allArray["last_year_value"] = isset($dayRanges['last_year_retail']["nprice"]) && !empty($dayRanges['last_year_retail']["nprice"]) && isset($dayRanges['last_year_service_sales']["nprice"]) && !empty($dayRanges['last_year_service_sales']["nprice"]) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($dayRanges['last_year_retail']["nprice"],$dayRanges['last_year_service_sales']["nprice"], 2):'0';

                                /*$allArray["current_value"] = isset($dayRanges['service']["unique_client_count"]) && !empty($dayRanges['service']["unique_client_count"]) && isset($dayRanges['service_client_count']["unique_client_count"]) && !empty($dayRanges['service_client_count']["unique_client_count"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['service']["unique_client_count"]/$dayRanges['service_client_count']["unique_client_count"], 2):'0';
                                $allArray["key"] = "Week ".$month_week_name;
                            //LAST YEAR DATA
                                $allArray["last_year_value"] = isset($dayRanges['last_year_retail']["nprice"]) && !empty($dayRanges['last_year_retail']["nprice"]) && isset($dayRanges['last_year_service']["invoice_count"]) && !empty($dayRanges['last_year_service']["invoice_count"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['last_year_retail']["nprice"]/$dayRanges['last_year_service']["invoice_count"], 2):'0';*/

                            }else{

                            $allArray["current_value"] = isset($dayRanges['retail']["nprice"]) && !empty($dayRanges['retail']["nprice"]) && isset($dayRanges['service']["invoice_count"]) && !empty($dayRanges['service']["invoice_count"]) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($dayRanges['retail']["nprice"],$dayRanges['service']["invoice_count"], 2):'0';
                            $allArray["key"] = "Week ".$month_week_name;
                            //LAST YEAR DATA
                             $allArray["last_year_value"] = isset($dayRanges['last_year_retail']["nprice"]) && !empty($dayRanges['last_year_retail']["nprice"]) && isset($dayRanges['last_year_service']["invoice_count"]) && !empty($dayRanges['last_year_service']["invoice_count"]) ? $this->Common_model->appCloudNumberFormat($dayRanges['last_year_retail']["nprice"]/$dayRanges['last_year_service']["invoice_count"], 2):'0';
                            /*$allArray["last_year_value"] = isset($dayRanges['last_year_service']["unique_client_count"]) && !empty($dayRanges['last_year_service']["unique_client_count"]) && isset($dayRanges['last_year_service_client_count']["unique_client_count"]) && !empty($dayRanges['last_year_service_client_count']["unique_client_count"]) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($dayRanges['last_year_service']["unique_client_count"],$dayRanges['last_year_service_client_count']["unique_client_count"], 2):'0';*/

                            }
                            $allArray['start_date'] = $datesArray['start_date'];
                            $allArray['end_date'] = $datesArray['end_date'];
                            $allArray['current_week'] = $datesArray['current_week'];
                            $tempArr[] = $allArray;
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
    function setOwnerRpctDataForGraphs($dayRangeType="today",$salon_id="")
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
                        $log['whichCron'] = 'setOwnerRpctDataForGraphs';
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

                                        $reportsDataForSalon = $this->GraphsOwner_model
                                                               ->compareRPCTOwnerReportsData($reportsWhere)
                                                               ->row_array();
                                        if(!empty($reportsDataForSalon)){
                                             $diff_array = array_diff_assoc($dataArray, $reportsDataForSalon);
                                             if(empty($diff_array)){
                                                pa('No Updates'); 
                                             }else{
                                                // update
                                                $diff_array['insert_status'] = OwnerRpctDataForGraphs::UPDATED;
                                                $diff_array["updatedDate"] = date("Y-m-d H:i:s");
                                                try {
                                                    $reportsWhere = array('salon_id' => $salonId,'key' => $resValuess['key'],'dayRangeType' => $insertdayRangeType,'id'=>$reportsDataForSalon['id']);
                                                    $update = $this->GraphsOwner_model->updateRPCTOwnerReportsData($reportsWhere,$diff_array);
                                                    pa($diff_array,'Reports Data updated Successfully');
                                                    pa($reportsDataForSalon['id'],'DB ID');
                                                } catch (Exception $e) {
                                                    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
                                                }
                                             }

                                        }else{
                                            
                                              // Insert Data
                                                $dataArray["insert_status"] = OwnerRpctDataForGraphs::INSERTED;
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

