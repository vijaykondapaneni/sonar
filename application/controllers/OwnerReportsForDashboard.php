<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OwnerReportsForDashboard extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Owner Dashboard Calculation
    **/
    CONST INSERTED = 0;
    CONST UPDATED = 1;
    public $salon_id;
    public $startDate;
    public $endDate;
    public $lastYearStartDate;
    public $lastYearEndDate;
    public  $currentDate;
    private $salonId;
    private $salonDetails;
    private $dayRangeType;    
  
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('ColorPercentage_model');
        $this->load->model('DashboardOwner_model');
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
    }
    // Get Date Range Types
    function __getStartEndDate($dayRangeType,$s = '', $e = '')
    {
        $this->dayRangeType =  $dayRangeType;
        $year = 1;
        $currentDate = date('Y-m-d');
         switch ($this->dayRangeType) {
            case "today":
                $this->startDate = $this->currentDate;
                $this->endDate = $this->currentDate;
                $this->currentdatenumber = 1;
                $this->currentnumberofdaysmonth = 1;
                
            break;
            case "lastweek":
                 if(isset($this->salonDetails['salon_info']["salon_start_day_of_week"]) && !empty($this->salonDetails['salon_info']["salon_start_day_of_week"]))
                    {
                        $lastDayOfTheWeek =  $this->salonDetails['salon_info']["salon_start_day_of_week"];
                        $this->startDate = getDateFn(strtotime('last '.$lastDayOfTheWeek));
                        $this->endDate = getDateFn(strtotime($this->startDate.' +6 days'));
                       
                    }
                    else
                    {
                        $this->startDate = getDateFn(strtotime('-7 days'));
                        $this->endDate = getDateFn(strtotime('-1 days'));
                        
                    }

                    $this->currentdatenumber = date('w');
                    $this->currentnumberofdaysmonth = 7;


            break;
            case LASTMONTH:
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->currentdatenumber = 1;
                $this->currentnumberofdaysmonth = 1;
                    
            break;
            case "Monthly":
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $this->currentDate;
                $this->currentdatenumber = date("d");
                $this->currentnumberofdaysmonth = date("t");
            break;
            case LAST90DAYS:
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->currentdatenumber = 1;
                $this->currentnumberofdaysmonth = 1;
                   
            break;
            case "Yearly":
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                $this->endDate = $currentDate;
                $this->currentdatenumber = date('z') + 1;
                $this->currentnumberofdaysmonth = 365;
            break;         
            default:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
                $this->currentdatenumber = 1;
                $this->currentnumberofdaysmonth = 1;
           break;
        }
    }

    function __getStartEndDateLastYear($dayRangeType,$year=1)
    {
        $this->dayRangeType =  $dayRangeType;
         switch ($this->dayRangeType) {
            case "today":
                //$MonthStartDate = date("Y-m-")."01";
                $this->startDate = $this->currentDate;
                $this->endDate = $this->currentDate;
                $this->lastYearStartDate =  getDateFn(strtotime("-$year year"));
                $this->lastYearEndDate = getDateFn(strtotime("-$year year"));
                $this->currentdatenumber = 1;
                $this->currentnumberofdaysmonth = 1;
            break;
            case LASTWEEK:
                if(isset( $this->salonDetails['salon_info']["salon_start_day_of_week"]) && !empty($this->salonDetails['salon_info']["salon_start_day_of_week"]))
                {
                    $lastDayOfTheWeek =  $this->salonDetails['salon_info']["salon_start_day_of_week"];
                    $this->startDate = getDateFn(strtotime('last '.$lastDayOfTheWeek));
                    $this->endDate = getDateFn(strtotime($this->startDate.' +6 days'));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                }
                else
                {
                    $this->startDate = getDateFn(strtotime('-7 days'));
                    $this->endDate = getDateFn(strtotime('-1 days'));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                }
                $this->currentdatenumber = date('w');
                $this->currentnumberofdaysmonth = 7;   
            break;
            case LASTMONTH:
                    $this->startDate = getDateFn(strtotime("first day of last month"));
                    $this->endDate = getDateFn(strtotime("last day of last month"));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                    $this->currentdatenumber = 1;
                    $this->currentnumberofdaysmonth = 1;
            break;
            case "Monthly":
                    $this->startDate = getDateFn(strtotime("first day of this month"));
                    $this->endDate = $this->currentDate;
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                    $this->currentdatenumber = date("d");
                    $this->currentnumberofdaysmonth = date("t");
            break;
            case LAST90DAYS:
                   
                    $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                    $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                    $this->endDate = getDateFn(strtotime("last day of last month"));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                    $this->currentdatenumber = 1;
                    $this->currentnumberofdaysmonth = 1;
            break;
            case "Yearly":
                   
                    $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                    $this->endDate = $this->currentDate;
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
                    $this->currentdatenumber = date('z') + 1;
                    $this->currentnumberofdaysmonth = 365;
            break;         
            default:
                    $this->startDate =  $this->currentDate;
                    $this->endDate   =  $this->currentDate;
                    $this->lastYearStartDate =  getDateFn(strtotime("-$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime("-$year year"));
                    $this->currentdatenumber = 1;
                    $this->currentnumberofdaysmonth = 1;
           break;
        }
    }

    function __make_like_conditions ($fields, array $query)
    {
        $likes = array();
        foreach ($query as $match) {
            $likes[] = "$fields LIKE '%$match%'";
        }
        return '('.implode(' || ', $likes).')';
    }

    function __getSalonReportForOwner($data){
        $arrdbData = array();
       //pa($data,'Dates');
       if(!empty($data["salon_id"]) && !empty($data["start_date"]) && !empty($data["end_date"]))
            {
               $color_field_array = array('color','highlight','Retouch','Hi-Lites','Lo-Lites','Minking','Foils','Virgin','Single Process','Crown Highlight','Partial Highlight','Double Process','Glaze','Base Softening','Highlights','Frosting','Balayage','Special Effects','Colors','Coloring','Chemical','Hilite','Hilites','Hilight','High','Perm','Relaxer','Color Retouch','Full Highlight','Custom Color','Permanent Wave');

                $startDate = $data['start_date'];
                $endDate = $data['end_date'];
                $salon_id = $data['salon_id'];
                $dayRangeType = $data['dayRangeType'];

                $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)
                                                   ->row_array();
                $salonAccountNo = $salonDetails['salon_account_id'];
                $rpct_type = $salonDetails['rpct_type'];
                
                $whereConditions = array('account_no' =>$salonAccountNo , 'tdatetime >=' =>$startDate, 'tdatetime <=' =>$endDate);
                $whereConditionsLRefundFalse = array('account_no' =>$salonAccountNo ,'tdatetime >=' =>$startDate, 'tdatetime <=' =>$endDate,'lrefund' => 'false');
                $whereConditionsLPrebookTrue = array('account_no' =>$salonAccountNo , 'tdatetime >=' =>$startDate, 'tdatetime <=' =>$endDate,'lrefund' => 'false','lprebook' => 'true');
                $whereConditionsScheduleHours = array('account_no' =>$salonAccountNo , 'start_date >=' =>$startDate, 'end_date <=' =>$endDate,'cworktype' => 'Work Time','dayRangeType' => $dayRangeType);
                $wherearray = array('salonAccountNo'=>$salonAccountNo,'startDate'=>$startDate,'endDate'=>$endDate);
                $wherearrayDayRangeType = array('salonAccountNo'=>$salonAccountNo,'startDate'=>$startDate,'endDate'=>$endDate,'dayRangeType'=>$dayRangeType);                              
 

                $getTotalServiceSales = $this->DashboardOwner_model
                                        ->getTotalServiceSales($whereConditions)
                                        ->row_array();
                
               // pa($this->db->last_query());
                $total_service_sales = $dataArray['total_service_sales'] = (!empty($getTotalServiceSales['nprice']) && $getTotalServiceSales['nprice'] > 0 ) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice'],2)  : '0.00';
                
                $getServiceInvoicesClientIdsCount = $this->DashboardOwner_model
                                        ->getServiceInvoicesClientIdsCount($whereConditionsLRefundFalse)
                                        ->row_array();

                $getServiceInvoiceCountWithPrebookTrue =$this->DashboardOwner_model
                                        ->getServiceInvoiceCountWithPrebookTrue($whereConditionsLPrebookTrue)
                                        ->row_array();                         

                //PREBOOK                

                $dataArray['prebook_percentage'] = (!empty($getServiceInvoicesClientIdsCount['unique_client_count']) && !empty($getServiceInvoiceCountWithPrebookTrue['unique_client_count'] )) ? $this->Common_model->appCludRoundCalc($getServiceInvoiceCountWithPrebookTrue['unique_client_count'],$getServiceInvoicesClientIdsCount['unique_client_count'],100,2)  : '0.00';

                //TO GET TOTAL RETAIL SALES DETAILS
                $getTotalProductSales = $this->DashboardOwner_model
                                        ->getTotalProductSales($whereConditions)
                                        ->row_array();
                //TOTAL RETAIL SALES
                 $dataArray['total_retail_sales'] = (!empty($getTotalProductSales['nprice']) && $getTotalProductSales['nprice'] > 0 ) ? $this->Common_model->appCloudNumberFormat($getTotalProductSales['nprice'],2)  : '0.00';

                 $getProductInvoicesClientIdsCount = $this->DashboardOwner_model
                                        ->getProductInvoicesClientIdsCount(array('account_no' =>$salonAccountNo , 'tdatetime >=' =>$startDate, 'tdatetime <=' =>$endDate,'lrefund' => 'false'))
                                        ->row_array();
                
                
                // Total Retail Units
                $dataArray['retail_units'] =  '0.00';
                /*$dataArray['retail_units'] = (isset($getTotalProductSales['nquantity']) && !empty($getTotalProductSales['nquantity']) ) ? $getTotalProductSales["nquantity"]  : '0.00';*/

                // RPCT Calculations
                //pa($getServiceInvoicesClientIdsCount);
                //pa($getProductInvoicesClientIdsCount);
                
                // RPCT CALCULATIONS CHANGE FOR LEVEL SALON
                // Get Total Unique Clients
                $serviceSalesClientIds = $this->DashboardOwner_model
                                         ->getServiceSalesClientIdsForClientIds($whereConditionsLRefundFalse)
                                         ->result_array();
                 $serviceClientIds = !empty($serviceSalesClientIds)?array_column($serviceSalesClientIds, "service_client_ids"):array();
                 $serviceClientCount = !empty($serviceClientIds) ? count($serviceClientIds) : 0;
                 $productSalesClientIds = $this->DashboardOwner_model
                                         ->getProductSalesClientIdsForClientIds($whereConditionsLRefundFalse)
                                         ->result_array();
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

                if($rpct_type==2){

                    if(!empty($getTotalProductSales["nprice"]) && !empty($totalUniqueClients))
                    {
                        $dataArray["RPCT"] = $this->Common_model->appCludRoundCalcWithOutMultiplication($getTotalProductSales["nprice"],$totalUniqueClients, 2);
                    }
                    else
                    {
                        $dataArray["RPCT"] = "0.00";
                    }

                }else{
                    $dataArray['RPCT'] = !empty($getTotalProductSales['nprice'] > 0 && !empty($getProductInvoicesClientIdsCount['invoice_count']) ) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($getTotalProductSales["nprice"],$getProductInvoicesClientIdsCount["invoice_count"],2)  : '0.00';
                } 


               // RPST Calculations
                $dataArray['RPST'] =  '0.00';
                /*$dataArray['RPST'] = ($getTotalProductSales['nprice'] > 0 && !empty($getServiceInvoicesClientIdsCount['invoice_count']) ) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($getTotalProductSales['nprice'],$getServiceInvoicesClientIdsCount['invoice_count'],2)  : '0.00';*/
               // Avg Service Ticket
                /*$dataArray['avgServiceTicket'] =  '0.00';
                // Avg Service Ticket
                $dataArray['avgRetailTicket'] =  '0.00';*/

                pa($total_service_sales);
                pa($this->currentdatenumber);
                pa($this->currentnumberofdaysmonth);

                $estimated_sales = !empty($total_service_sales) ?   ($total_service_sales / $this->currentdatenumber)*$this->currentnumberofdaysmonth  : '0.0';
                pa($estimated_sales);
                $dataArray['estimated_sales'] = !empty($estimated_sales) ? $estimated_sales : '0.00';  




                




                $dataArray['avgServiceTicket'] = (!empty($getTotalServiceSales['nprice']) && !empty($getServiceInvoicesClientIdsCount['invoice_count']) ) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice']/$getServiceInvoicesClientIdsCount["invoice_count"],2)  : '0.00';
                
                // Avg Service Ticket
                $dataArray['avgRetailTicket'] = (!empty($getTotalProductSales['nprice']) && !empty($getProductInvoicesClientIdsCount['invoice_count']) ) ? $this->Common_model->appCloudNumberFormat($getTotalProductSales['nprice']/$getProductInvoicesClientIdsCount["invoice_count"],2)  : '0.00';
                
                // Color Percentage
                $queryss = "cservicedescription";
                $like_conditions = $this->__make_like_conditions($queryss, $color_field_array);
                $getTotalColorServiceSales = $this->DashboardOwner_model
                                        ->getTotalColorServiceSales($whereConditions,$like_conditions)
                                        ->row_array();

                // %color calculation
               
               /* $dataArray['total_color_service_sales'] = (!empty($getTotalServiceSales['nprice']) && $getTotalColorServiceSales['nprice'] >0 ) ? $this->Common_model->appCloudNumberFormat(($getTotalColorServiceSales['nprice']),2)  : '0.00';*/
               $dataArray['total_color_service_sales'] =  '0.00';


                
              
                // $dataArray['color_percentage'] = (!empty($getTotalServiceSales['nprice']) && !empty($getTotalColorServiceSales) ) ? $this->Common_model->appCloudNumberFormat(($getTotalColorServiceSales['nprice']/$getTotalServiceSales['nprice']*100),2)  : '0.00';
                $whereConditionsServiceSalesCount = array('account_no' =>$salonAccountNo , 'tdatetime >=' =>$startDate, 'tdatetime <=' =>$endDate); 
                    $dataArray['color_percentage']  = $this->ColorPercentage_model->getColorPercentage($whereConditionsServiceSalesCount);
                    //pa($dataArray['color_percentage'],'color_percentage',false);
                 //Total Gift Card SALES
                 $getTotalGiftCardSales = $this->DashboardOwner_model
                                        ->getTotalGiftCardSales($whereConditions)
                                        ->row_array();
                 $dataArray['total_gift_card_sales'] = (!empty($getTotalGiftCardSales['nprice']) && $getTotalGiftCardSales['nprice'] > 0)  ? $this->Common_model->appCloudNumberFormat($getTotalGiftCardSales['nprice'],2)  : '0.00';                         
                
                //TOTAL SALES (RETAIL + SERVICE)

                 if(($salon_id=='536') || ($salon_id=='537') || ($salon_id=='538') || ($salon_id=='539') || ($salon_id=='540')){
                    $dataArray['total_sales'] = !empty($getTotalServiceSales['nprice']) || !empty($getTotalProductSales['nprice']) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice']+$getTotalProductSales['nprice'],2)  : '0.00';

                 }else{
                    $dataArray['total_sales'] = !empty($getTotalServiceSales['nprice']) || !empty($getTotalProductSales['nprice']) || !empty($getTotalGiftCardSales['nprice'])  ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice']+$getTotalProductSales['nprice']+$getTotalGiftCardSales['nprice'],2)  : '0.00';
                 }


                 
                // Percentage BOOKED
                 $getTotalScheduledHours = $this->DashboardOwner_model
                                           ->getTotalScheduledHours($whereConditionsScheduleHours)
                                           ->row_array();
                 //pa($this->db->last_query(),'getTotalScheduledHours');
                $totalScheduledHours = !empty($getTotalScheduledHours) && isset($getTotalScheduledHours['nhours']) && !empty($getTotalScheduledHours['nhours']) ?  $getTotalScheduledHours['nhours']  : '0';
              
                
                $getTotalHoursBooked = $this->DashboardOwner_model
                                           ->getTotalHoursBooked($wherearray)
                                           ->row_array();
               // pa($this->db->last_query(),'getTotalHoursBooked');                           
                $totalHoursBookedForSalon = !empty($getTotalHoursBooked) && isset($getTotalHoursBooked['totalhours']) && !empty($getTotalHoursBooked['totalhours']) ?  $getTotalHoursBooked['totalhours']  : '0';

                //pa($totalScheduledHours,'totalScheduledHours');
                //pa($getTotalHoursBooked,'getTotalHoursBooked');

                $dataArray['percentage_booked'] = !empty($totalScheduledHours) && !empty($totalHoursBookedForSalon)  ? $this->Common_model->appCloudNumberFormat(($totalHoursBookedForSalon/$totalScheduledHours)*100,2)  : '0';

                //pa($dataArray['percentage_booked'],'percentage_booked',true);

                //Get Client TICKETS
                $serviceSalesClientIds = $this->DashboardOwner_model
                                        ->getserviceSalesClientIds($whereConditionsLRefundFalse)
                                        ->result_array();
                $arrServiceClientIds = !empty($serviceSalesClientIds)  ? array_column($serviceSalesClientIds, "service_client_ids")  : array();
                $serviceClientCount = !empty($serviceSalesClientIds)  ? count($arrServiceClientIds)  : 0;

                $productSalesClientIds = $this->DashboardOwner_model
                                        ->getPoductSalesClientIds($whereConditionsLRefundFalse)
                                        ->result_array();
                $arrProductClientIds = !empty($productSalesClientIds)  ? array_column($productSalesClientIds, "retail_client_ids")  : array();
                $retailClientCount = !empty($arrProductClientIds)  ? count($arrProductClientIds)  : 0;
                $commonClietIds = !empty($arrServiceClientIds) && !empty($arrProductClientIds)  ? array_intersect($arrServiceClientIds, $arrProductClientIds)  : array();
                $commonClientsCount = !empty($commonClietIds) ? count($commonClietIds):0;
                $totalUniqueClients = ($serviceClientCount - $commonClientsCount) + ($retailClientCount - $commonClientsCount) + $commonClientsCount;

                //CLIENTS SERVICED or SERVED
                $getStaffUniqueWorkedDates = $this->DashboardOwner_model
                                             ->getStaffUniqueWorkedDates($whereConditionsLRefundFalse)
                                             ->result_array();
                $arrStaffWorkedDates = !empty($getStaffUniqueWorkedDates) ? array_column($getStaffUniqueWorkedDates, "tdatetime"):array();
                $totalWorkedDaysCount = !empty($commonClietIds) ? count($arrStaffWorkedDates):0;

                $dataArray['client_served'] = !empty($totalUniqueClients) && !empty($totalWorkedDaysCount) ? $this->Common_model->appCloudNumberFormat($totalUniqueClients/$totalWorkedDaysCount, 2):array();

               //RUCT Calculation
                $productSalesDetailsArrr = $this->DashboardOwner_model
                                           ->getProductSalesDetailsArrr($whereConditions)
                                           ->row_array();
                $dataArray['RUCT'] = isset($productSalesDetailsArrr['nquantity']) && !empty($productSalesDetailsArrr['nquantity']) && !empty($totalUniqueClients) ? $this->Common_model->appCloudNumberFormat($productSalesDetailsArrr['nquantity']/$totalUniqueClients, 2):'0.00';
                //rebook percentage calculation STARTS
                
                //GET UNIQUE CLIENT COUNT BY DAY

                $getServiceSalesUniqueClientIdsCount = $this->DashboardOwner_model
                                                       ->getServiceSalesUniqueClientIdsCount($whereConditionsLRefundFalse)
                                                       ->result_array();
                $arrClientsCountPerDay = !empty($getServiceSalesUniqueClientIdsCount)?array_column($getServiceSalesUniqueClientIdsCount, "unique_client_count"):array();

                $totalClientsCount = isset($arrClientsCountPerDay)&& !empty($arrClientsCountPerDay)? array_sum($arrClientsCountPerDay):0;
                $begin = new DateTime($startDate);
                $end = new DateTime($endDate);
                $end = $end->modify( '+1 day' );
                $interval = new DateInterval('P1D');
                $daterange = new DatePeriod($begin, $interval ,$end);
               foreach($daterange as $datess){
                    //echo $datess->format("Y-m-d") . "<br>";
                    
                    $this->DB_ReadOnly->select('iclientid');
                    $this->DB_ReadOnly->group_by('iclientid');
                     $getServiceSalesUniqueClientIds = $this->DashboardOwner_model
                                                      ->getServiceSalesUniqueClientIds(array('account_no' =>$salonAccountNo ,'tdatetime' => $datess->format("Y-m-d"),'lrefund' => 'false'))
                                                      ->result_array();

                   
                    $uniqueClientIdsServiced = !empty($getServiceSalesUniqueClientIds)? array_column($getServiceSalesUniqueClientIds, "iclientid"):array();
                    $uniqueClientIdsJoined = !empty($uniqueClientIdsServiced)?implode(",", $uniqueClientIdsServiced):''; 
                    //print_r($uniqueClientIdsServiced);exit;
                    $plusFourMonthsDate = date('Y-m-d',strtotime($datess->format("Y-m-d") . "+120 days"));
                    //echo $uniqueClientIdsJoined."<br>";

                    if(!empty($uniqueClientIdsJoined)){
                        
                        $wheredata['plusFourMonthsDate'] =$plusFourMonthsDate;
                        $wheredata['salonAccountNo'] = $salonAccountNo;
                        $wheredata['datessformat'] = $datess->format("Y-m-d");
                        $wheredata['uniqueClientIdsJoined'] = $uniqueClientIdsJoined;
                        $getAllClientCount = $this->DashboardOwner_model
                                                  ->getClientServicedCount($wheredata)
                                                  ->row_array();
                        $arrAllClientCount[] = $getAllClientCount["client_count"];
                    }
                    
                }
                $totalFutureClientsCount = !empty($arrAllClientCount)?array_sum($arrAllClientCount):'0'; 
                //Rebook Percentage
                $dataArray['rebook_percentage'] = $totalFutureClientsCount >0 && $totalClientsCount>0 ?
                $this->Common_model->appCloudNumberFormat(($totalFutureClientsCount/$totalClientsCount)*100, 2):'0';
                //Percentage of Clients Buying Retail Starts

                $serviceSalesClientIds = $this->DashboardOwner_model
                                         ->getServiceSalesClientIdsForClientIds($whereConditionsLRefundFalse)
                                         ->result_array();
               
                 $serviceClientIds = !empty($serviceSalesClientIds)?array_column($serviceSalesClientIds, "service_client_ids"):array();
                 $serviceClientCount = !empty($serviceClientIds) ? count($serviceClientIds) : 0;

              


                 $productSalesClientIds = $this->DashboardOwner_model
                                         ->getProductSalesClientIdsForClientIds($whereConditionsLRefundFalse)
                                         ->result_array();
                                   
                // pa($this->db->last_query(),'productSalesClientIds');

                 $productClientIds = !empty($productSalesClientIds)?array_column($productSalesClientIds, "retail_client_ids"):array();
                 $retailClientCount = !empty($productClientIds) ? count($productClientIds) : 0;
                 //pa($retailClientCount,'retailClientCount');
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
             //   pa($retailClientCount,'retailClientCount');

                 $commonClietIds = !empty($serviceClientIds) && !empty($productClientIds) ? array_intersect($serviceClientIds, $productClientIds) : array();
                 $commonClientsCount = !empty($commonClietIds) ? count($commonClietIds) : 0; 
                 
                 $totalUniqueClients = ($serviceClientCount - $commonClientsCount) + ($retailClientCount - $commonClientsCount) + $commonClientsCount;
                 $dataArray["percentage_of_clients_buying_retail"] =  !empty($totalUniqueClients) && !empty($retailClientCount)? $this->Common_model->appCloudNumberFormat(($retailClientCount/$totalUniqueClients)*100, 2):'0.00';
                
                  //GET TOTAL GUESTS COUNT
                $sql_get_total_clients_from_appts = $this->DashboardOwner_model
                                                  ->getTotalServicedClientIdsFromAppointments($wherearray)
                                                  ->row_array();
                $dataArray["total_guest_qty"] =  !empty($sql_get_total_clients_from_appts['total_client_count']) ? $sql_get_total_clients_from_appts['total_client_count']:'0';   
                 
                pa($dataArray["total_guest_qty"],'total_guest_qty');
                
                // new guest for oneday
                
                if($dayRangeType=='today'){
                   // new guest
                   $sql_get_new_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as new_client_count FROM mill_clients client join mill_appointments appts on appts.ClientId=client.ClientId
                        WHERE
                        appts.AccountNo = '".$salonAccountNo."' and
                        appts.SlcStatus != 'Deleted' and
                        client.AccountNo = '".$salonAccountNo."' and
                        str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$this->startDate."' and
                        str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$this->endDate."' and
                        ((date(client.clientFirstVistedDate) = '0001-01-01' and date(client.clientLastVistedDate) = '0001-01-01') or
                        (str_to_date(AppointmentDate, '%m/%d/%Y') = date(client.clientFirstVistedDate) and  str_to_date(AppointmentDate, '%m/%d/%Y') = date(client.clientLastVistedDate))
                        )")->row_array();

                   $new_guest_qty = $sql_get_new_clients_from_appts['new_client_count'];

                   pa($new_guest_qty ,'new_guest_qty');

                   $repeated_guest_qty = ($dataArray["total_guest_qty"]>0) ? $dataArray["total_guest_qty"] - $new_guest_qty : 0;
                    pa($repeated_guest_qty ,'repeated_guest_qty');
                  /* $sql_get_new_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as new_client_count FROM 
                    ".MILL_CLIENTS_TABLE." client 
                    join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                    WHERE 
                    appts.AccountNo = '".$salonAccountNo."' and 
                    appts.SlcStatus != '2' and 
                    client.AccountNo = '".$salonAccountNo."' and
                    date(appts.AppointmentDate) >= '".$startDate."' and 
                    date(appts.AppointmentDate) <= '".$endDate."' and 

                    ((date(client.clientFirstVistedDate) >= '".$startDate."' 
                    and 
                    date(client.clientFirstVistedDate) <= '".$endDate."') or (client.clientFirstVistedDate='0001-01-01 00:00:00')) 
                    and appts.ClientId !='-999'")->row_array();

                   $new_guest_qty = $sql_get_new_clients_from_appts['new_client_count'];

                   //pa($this->db->last_query(),'new_guest_qty');
                   // repeat guest
                   $sql_get_repeat_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as repeat_client_count FROM 
                    ".MILL_CLIENTS_TABLE." client 
                    join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                    WHERE 
                    appts.AccountNo = '".$salonAccountNo."' and 
                    appts.SlcStatus != '2' and 
                    client.AccountNo = '".$salonAccountNo."' and
                    date(appts.AppointmentDate) >= '".$startDate."' and 
                    date(appts.AppointmentDate) <= '".$endDate."' and 

                    ((date(client.clientFirstVistedDate)!=date(client.clientLastVistedDate)) 
                    or (client.clientFirstVistedDate!='0001-01-01 00:00:00')) 
                    and appts.ClientId !='-999'")->row_array();

                   $repeated_guest_qty = $sql_get_repeat_clients_from_appts['repeat_client_count'];*/
                }else{

                   $clientscount_data['start_date'] = $startDate;
                   $clientscount_data['end_date'] = $endDate;
                   $clientscount_data['salonAccountNo'] = $salonAccountNo;
                  // pa($clientscount_data);

                   $new_guest_qty = $this->DashboardOwner_model->getNewGuestCount($clientscount_data);
                   //pa($this->db->last_query());
                   $total_guest_qty = $this->DashboardOwner_model->getTotalClientsCount($clientscount_data);
                   //pa($this->db->last_query());
                  // pa($total_guest_qty);

                   $repeated_guest_qty = $total_guest_qty-$new_guest_qty;

                   if($repeated_guest_qty<0){
                    $repeated_guest_qty = 0;
                    $errors['code'] = 'Repeated Guest Qty Error';
                    $error_message= "<p>".$salonAccountNo."</p>";
                    $error_message.= "<p>Day Range Type".$dayRangeType."</p>";
                    $error_message.= "<p>Total Guest Qty".$total_guest_qty."</p>";
                    $error_message.= "<p>Is Getting Negative Value</p>";
                    $errors['message'] = $error_message;
                    $errors['tablename'] = 'mill_owner_report_calculations_cron';
                    send_mail_database_error($errors);
                   }
                }

                
                $dataArray["new_guest_qty"] = $new_guest_qty;
                $dataArray["repeated_guest_qty"] = $repeated_guest_qty;
                
                //GET TOTAL GUESTS COUNT
              /*  $sql_get_total_clients_from_appts = $this->DashboardOwner_model
                                                  ->getTotalServicedClientIdsFromAppointments($wherearray)
                                                  ->row_array();
                 $dataArray["total_guest_qty"] =  !empty($sql_get_total_clients_from_appts['total_client_count']) ? $sql_get_total_clients_from_appts['total_client_count']:'0';   */                               
                //Leader Board Data FOR %BOOKED
                 $percentBookedCount = array();
                 
                 $leadserboarddata = $this->DashboardOwner_model->getLeaderBoardData($wherearray,$wherearrayDayRangeType); 
                 $dataArray['highest_percent_booked_value'] = $leadserboarddata['highest_percent_booked_value'];
                 $dataArray['highest_percent_booked_employee'] = $leadserboarddata['highest_percent_booked_employee'];
                 $dataArray['highest_percent_booked_employee_iid'] = $leadserboarddata['highest_percent_booked_employee_iid'];
                 //pa($this->db->last_query(),'highest_percent_booked_employee',false);
                 //LEADER BOARD DATA FOR NEW CLIENTS
                 
                 $getLeaderBoardDataNewClients = $this->DashboardOwner_model->getLeaderBoardDataNewClients($wherearray,$dayRangeType);
                 //pa($this->db->last_query(),'getLeaderBoardDataNewClients',false);
                 $dataArray['highest_new_client_value'] = $getLeaderBoardDataNewClients['highest_new_client_value'];
                 $dataArray['highest_new_client_employee'] = $getLeaderBoardDataNewClients['highest_new_client_employee'];
                 $dataArray['highest_new_client_employee_iid'] = $getLeaderBoardDataNewClients['highest_new_client_employee_iid'];
                
                 //LEADER BOARD DATA for RETAIL SALES OF STYLISTS
                 $leadserboarddataforRetailSales = $this->DashboardOwner_model->leadserboarddataforRetailSales($wherearray);
                 //pa($this->db->last_query(),'leadserboarddataforRetailSales',false);
                 $dataArray['highest_retail_total_value'] = $leadserboarddataforRetailSales['highest_retail_total_value'];
                 $dataArray['highest_retail_total_employee'] = $leadserboarddataforRetailSales['highest_retail_total_employee'];
                 $dataArray['highest_avg_retail_total_employee_iid'] = $leadserboarddataforRetailSales['highest_avg_retail_total_employee_iid'];
                
                 //LEADER BOARD DATA for SERVICE SALES OF STYLISTS
                 $leadserboarddataforServiceSales = $this->DashboardOwner_model->leadserboarddataforServiceSales($wherearray);
                // pa($this->db->last_query(),'leadserboarddataforServiceSales',false);
                 $dataArray['highest_service_total_value'] = $leadserboarddataforServiceSales['highest_service_total_value'];
                 $dataArray['highest_service_total_employee'] = $leadserboarddataforServiceSales['highest_service_total_employee'];
                 $dataArray['highest_service_total_employee_iid'] = $leadserboarddataforServiceSales['highest_service_total_employee_iid'];

                 //Leader board Color percentage
                $colorPercentage = array();
                $wherearrayColorLikeConditions = $wherearray;           
                $wherearrayColorLikeConditions['like_conditions'] = $like_conditions;           
                // $leadserboarddataColorPercentageSales = $this->DashboardOwner_model->leadserboarddataforColorSales($wherearrayColorLikeConditions);
                $leadserboarddataColorPercentageSales = $this->ColorPercentage_model->leadserboarddataforColorSalesNewMethod($wherearrayColorLikeConditions);
                //pa($leadserboarddataColorPercentageSales,'leadserboarddataColorPercentageSales',false); 
               //  pa($this->db->last_query(),'leadserboarddataforColorSales',false);
                $dataArray['highest_color_percent_value'] = $leadserboarddataColorPercentageSales['highest_color_percent_value'];
                $dataArray['highest_color_percent_employee'] = $leadserboarddataColorPercentageSales['highest_color_percent_employee'];
                $dataArray['highest_color_percent_employee_iid'] = $leadserboarddataColorPercentageSales['highest_color_percent_employee_iid'];
                //LEADER BOARD DATA for AVG RETAIL TICKET
               //$leadserboarddataforRetailAvgTicket = $this->DashboardOwner_model->leadserboarddataforRetailAvgTicket($wherearray);
              // pa($this->db->last_query(),'leadserboarddataforRetailAvgTicket',false);
              
              /* $dataArray['highest_avg_rpct_value'] = $leadserboarddataforRetailAvgTicket['highest_avg_rpct_value'];
               $dataArray['highest_avg_rpct_employee'] = $leadserboarddataforRetailAvgTicket['highest_avg_rpct_employee'];
               $dataArray['highest_avg_rpct_employee_iid'] = $leadserboarddataforRetailAvgTicket['highest_avg_rpct_employee_iid'];*/

               //LEADER BOARD DATA for AVG SERVICE TICKET
               //$leadserboarddataforServiceAvgTicket = $this->DashboardOwner_model->leadserboarddataforServiceAvgTicket($wherearray);
             //   pa($this->db->last_query(),'leadserboarddataforServiceAvgTicket',false);

            /*   $dataArray['highest_avg_serviceTicket_value'] = $leadserboarddataforServiceAvgTicket['highest_avg_serviceTicket_value'];
               $dataArray['highest_avg_serviceTicket_employee'] = $leadserboarddataforServiceAvgTicket['highest_avg_serviceTicket_employee'];
               $dataArray['highest_avg_serviceTicket_employee_iid'] = $leadserboarddataforServiceAvgTicket['highest_avg_serviceTicket_employee_iid'];*/
                //LEADER BOARD DATA for %Prebooked
              
              // $leadserboarddataforPrebooked = $this->DashboardOwner_model->leadserboarddataforPrebooked($wherearray);

             //  pa($this->db->last_query(),'leadserboarddataforPrebooked',false);

               /*$dataArray['highest_prebook_value'] = $leadserboarddataforPrebooked['highest_prebook_value'];
               $dataArray['highest_prebook_sold_employee'] = $leadserboarddataforPrebooked['highest_prebook_sold_employee'];
               $dataArray['highest_prebook_sold_employee_iid'] = $leadserboarddataforPrebooked['highest_prebook_sold_employee_iid'];*/
                
               // NEWLY ADDED 
               /*$leadserboarddataforPercentageRetailToServiceSales = $this->DashboardOwner_model->leadserboarddataforPercentageRetailToServiceSales($wherearray);
            //   pa($this->db->last_query(),'leadserboarddataforPercentageRetailToServiceSales',false);
               $dataArray['highest_percentage_retail_to_service_sales_value'] = $leadserboarddataforPercentageRetailToServiceSales['highest_percentage_retail_to_service_sales_value'];
               $dataArray['highest_percentage_retail_to_service_sales_employee'] = $leadserboarddataforPercentageRetailToServiceSales['highest_percentage_retail_to_service_sales_employee'];
               $dataArray['highest_percentage_retail_to_service_sales_employee_iid'] = $leadserboarddataforPercentageRetailToServiceSales['highest_percentage_retail_to_service_sales_employee_iid'];*/
               // CLOSE NEWLY ADDED 
              // pa($dataArray,'Return Data'); 
             
              // pa($dataArray);
              return $dataArray;  
                                         
            }else{
                 return array(); 
            }
    }
    
    function __topFiveServicesAndProductOwnerReport($data){
        
        $salon_id = $data['salon_id'];
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $dayRangeType = $data['dayRangeType'];

        if(!empty($salon_id) && !empty($startDate) && !empty($endDate))
            {
                $salonDetailsArr = $this->Common_model
                                   ->getMillSdkConfigDetailsBy($salon_id)
                                   ->row_array();
                $salonAccountNo = (!empty($salonDetailsArr)) ? $salonDetailsArr['salon_account_id'] : '';
                $fetchData = array();
                $wherearray = array('salonAccountNo'=>$salonAccountNo,'startDate'=>$startDate,'endDate'=>$endDate);
                    if($salonAccountNo){
                         $topFiveServiceSales = $this->DashboardOwner_model
                                                ->topFiveServiceSales($wherearray)
                                                ->result_array();
                        $topFiveProductSales = $this->DashboardOwner_model
                                                ->topFiveProductSales($wherearray)
                                                ->result_array();
                        if(is_array($topFiveServiceSales)){
                            $fetchData["ServiceSales"] =  $topFiveServiceSales ;
                        }
                        if(is_array($topFiveProductSales)){
                            $fetchData["ProductSales"] =  $topFiveProductSales ;
                        }
                    } 
                    else 
                    { 
                        exit("Please check the parameters,it should be valide."); 
                    }
               
                $kcount = 0; // Initalisation of counter to apply as key to create array.
                    if(!empty($fetchData)){
                            foreach($fetchData as $sales_type => $lists){
                                foreach($lists as $list){
                                        $whereCondition  = array('salon_id' => $salon_id, 'service_code' => $list['service_code'], 'sales_type' => $sales_type, 'day_range_type' => $dayRangeType, 'start_date' => $startDate, 'end_date' => $endDate);

                                        //Checking the record whether it present or not.
                                        $select_columns = array('id','salon_id','service_code','service_description','total_price','sales_type','day_range_type','start_date','end_date');

                                        $client_data = $this->DashboardOwner_model->getTopFiveOwnerReport($whereCondition,$select_columns);

                                        $insertData[$kcount]['salon_id'] = $salon_id;
                                        $insertData[$kcount]['service_code'] = $list['service_code'];
                                        $insertData[$kcount]['service_description'] = $list['service_description'];
                                        $insertData[$kcount]['total_price'] =  $list['TOTAL_PRICE'];
                                        $insertData[$kcount]['sales_type'] =   $sales_type;
                                        $insertData[$kcount]['start_date'] =  $startDate;
                                        $insertData[$kcount]['end_date'] =  $endDate;
                                        $insertData[$kcount]['day_range_type'] = $dayRangeType;
                                        $insertData[$kcount]['insertedDate'] = date("Y-m-d H:i:s");
                                       
                                        if ($client_data->num_rows() > 0) {
                                                $dbRes = $client_data->row_array();
                                                $fiter_array_to_compare = array_diff_key($insertData[$kcount], array_flip(["insertedDate"]));
                                                $diff_array = array_diff($fiter_array_to_compare, $dbRes );

                                                if(empty($diff_array)) {
                                                    //pa("$sales_type--No Updates");
                                                    continue;
                                                } else {  //Updating Existing Record here.

                                                    $customArr = array('updatedDate' => date("Y-m-d H:i:s"), 'insert_status' => 1);
                                                    $diff_array = array_merge($diff_array,$customArr);

                                                    //update row only with modified coloums 
                                                    $where_cond = array('id' => $dbRes['id']);
                                                    $this->DashboardOwner_model->updateTopFiveOwnerReport($where_cond,$diff_array);
                                                    //pa($diff_array,"$sales_type - Update");
                                                }
                                        }else{ //Inserting New Record here.
                                                    $insertData[$kcount]['insert_status'] = 0;
                                                    $this->DashboardOwner_model->insertTopFiveOwnerReport($insertData[$kcount]);
                                                    //pa($this->db->insert_id(),"$sales_type - Insert");
                                        }

                                        $kcount ++;  // Increment by 1 .
                                }
                            }
                    }
            }
            else {
                exit("Please check the parameters,it should be valid.");
            }
        
    }

    
    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    /**
     * This function for get employee schedule hours last year data
     * @param type $account_no
     */
    function setOwnerReportsDashboard($dayRangeType="today",$salon_id="",$year="")
        { 
            $this->currentDate = getDateFn();
            $getAllSalons = $this->Common_model->getAllSalons($salon_id);
            if(isset($getAllSalons["mill_salons"]) && !empty($getAllSalons["mill_salons"]))
            {
                foreach($getAllSalons["mill_salons"] as $salonsData)
                {
                    pa('',"Reports Owner Dashboard--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                    $this->salon_id = $salonsData['salon_id'];
                    $this->salonDetails = $salonDetails = $this->Common_model->getSalonInfoBy($this->salon_id);
                    
                    if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"]) && isset($salonDetails['salon_info']["service_retail_reports_enabled"]) && $salonDetails['salon_info']["millennium_enabled"]=="Yes" && $salonDetails['salon_info']["service_retail_reports_enabled"]=="Yes"){
                        // Database Log
                        $log['AccountNo'] = $salonDetails['salon_info']['salon_code'];
                        $log['salon_id'] = $salonDetails['salon_info']['salon_id'];
                        $log['StartingTime'] = date('Y-m-d H:i:s');

                        if($year==1){
                            $log['whichCron'] = 'setOwnerReportsDashboardLastYear';
                        } else {
                            $log['whichCron'] = 'setOwnerReportsDashboard';
                        }
                        
                        $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        $log['CronType'] = 1;
                        $log['id'] = 0;
                        $log_id = $this->Common_model->saveMillCronLogs($log);
                        // GET START DATE AND END DATE AS PER PARAMETERS

                        if($year==1){
                            $this->__getStartEndDateLastYear($dayRangeType);
                            $arrSalonDetails['start_date'] = $this->lastYearStartDate;
                            $arrSalonDetails['end_date'] = $this->lastYearEndDate;
                            $arrData['start_date'] = $this->lastYearStartDate;
                            $arrData['end_date'] = $this->lastYearEndDate;
                            $arrData['status'] = true;
                        } else {
                            $this->__getStartEndDate($dayRangeType);
                            $arrSalonDetails['start_date'] = $this->startDate;
                            $arrSalonDetails['end_date'] = $this->endDate;
                            $arrData['start_date'] = $this->startDate;
                            $arrData['end_date'] = $this->endDate;
                            $arrData['status'] = true;
                        }
                                                
                        $arrSalonDetails['salon_id'] = $this->salon_id;
                        
                        $arrSalonDetails['dayRangeType'] = $dayRangeType;

                        pa($arrSalonDetails['start_date']);
                        pa($arrSalonDetails['end_date']);

                        $res = $this->__getSalonReportForOwner($arrSalonDetails);
                        //pa($res,'Results',false);//exit;
                        $dataArray["currentYearReport"] = array();
                        $dataArray["lastYearReport"] = array();
                        $temp1 = array();
                        $temp1['salon_id'] = $this->salon_id;
                        $temp1['start_date'] = $this->startDate;
                        $temp1['end_date'] = $this->endDate;

                        if($year==1){
                            $report_type = $temp1['report_type'] = "lastYearReport";
                        } else {
                            $report_type =  $temp1['report_type'] = "currentYearReport";
                        }
                        
                        $temp1['dayRangeType'] = !empty($dayRangeType)?$dayRangeType:'';

                        $temp1['total_sales'] = isset($res["total_sales"]) && !empty($res["total_sales"]) && $res["total_sales"]!="0.00" ? $res['total_sales']:'0.00';


                        $temp1['service_revenue'] = isset($res["total_service_sales"]) && !empty($res["total_service_sales"]) && $res["total_service_sales"]!="0.00" ? $res['total_service_sales']:'0.00';
                        $temp1['total_retail_price'] = isset($res["total_retail_sales"]) && !empty($res["total_retail_sales"]) && $res["total_retail_sales"]!="0.00" ? $res['total_retail_sales']:'0.00';
                        $temp1['gift_cards'] = isset($res["total_gift_card_sales"]) && !empty($res["total_gift_card_sales"]) && $res["total_gift_card_sales"]!="0.00" ? $res['total_gift_card_sales']:'0.00';
                        $temp1['color_revenue'] = isset($res["total_color_service_sales"]) && !empty($res["total_color_service_sales"]) && $res["total_color_service_sales"]!="0.00" ? $res['total_color_service_sales']:'0.00';
                        $temp1['color_percentage'] = isset($res["color_percentage"]) && !empty($res["color_percentage"]) && $res["color_percentage"]!="0.00" ? $res['color_percentage']:'0.00';
                        $temp1['guest_qty_new'] = isset($res["new_guest_qty"]) && !empty($res["color_percentage"]) && $res["new_guest_qty"]!="0" ? $res['new_guest_qty']:'0';
                        $temp1['guest_qty_repeated'] = isset($res["repeated_guest_qty"]) && !empty($res["repeated_guest_qty"]) && $res["repeated_guest_qty"]!="0" ? $res['repeated_guest_qty']:'0';
                        $temp1['RPCT'] = isset($res["RPCT"]) && !empty($res["RPCT"]) && $res["RPCT"]!="0" ? $res['RPCT']:'0.00';
                        $temp1['avg_service_ticket'] = isset($res["avgServiceTicket"]) && !empty($res["avgServiceTicket"]) && $res["avgServiceTicket"]!="0" ? $res['avgServiceTicket']:'0.00';

                        $temp1['estimated_sales'] = isset($res["estimated_sales"]) && !empty($res["estimated_sales"]) && $res["estimated_sales"]!="0" ? $res['estimated_sales']:'0.00';

                        $temp1['avg_retail_ticket'] = isset($res["avgRetailTicket"]) && !empty($res["avgRetailTicket"]) && $res["avgRetailTicket"]!="0" ? $res['avgRetailTicket']:'0.00';
                        $temp1['prebook_percentage'] = isset($res["prebook_percentage"]) && !empty($res["prebook_percentage"]) && $res["prebook_percentage"]!="0" ? $res['prebook_percentage']:'0.00';
                        $temp1['percent_booked'] = isset($res["percentage_booked"]) && !empty($res["percentage_booked"]) ? $res['percentage_booked']:'0.00';
                        $temp1['retail_units'] = isset($res["retail_units"]) && !empty($res["retail_units"]) ? $res['retail_units']:'0';
                        $temp1['RPST'] = isset($res["RPST"]) && !empty($res["RPST"]) ? $res['RPST']:'0.00';
                        $temp1['client_served'] = isset($res["client_served"]) && !empty($res["client_served"]) ? $res['client_served']:'0.00';
                        $temp1['RUCT'] = isset($res["RUCT"]) && !empty($res["RUCT"]) ? $res['RUCT']:'0.00';
                        $temp1['rebook_percentage'] = isset($res["rebook_percentage"]) && !empty($res["rebook_percentage"]) ? $res['rebook_percentage']:'0.00';
                        $temp1['total_guest_qty'] = isset($res["total_guest_qty"]) && !empty($res["total_guest_qty"]) ? $res['total_guest_qty']:'0';
                        $temp1['percentage_of_clients_buying_retail'] = isset($res["percentage_of_clients_buying_retail"]) && !empty($res["percentage_of_clients_buying_retail"]) ? $res['percentage_of_clients_buying_retail']:'0';
                        

                        
                        /*$temp1['highest_avg_rpct_value'] = isset($res["highest_avg_rpct_value"]) && !empty($res["highest_avg_rpct_value"]) && $res["highest_avg_rpct_value"]!="0.00" ? $res['highest_avg_rpct_value']:'0.00';
                        $temp1['highest_avg_rpct_employee'] = isset($res["highest_avg_rpct_employee"]) && !empty($res["highest_avg_rpct_employee"]) && $res["highest_avg_rpct_employee"]!="" ? $res['highest_avg_rpct_employee']:'';
                        if(isset($res["highest_avg_rpct_employee_iid"]) && !empty($res["highest_avg_rpct_employee_iid"]))
                        {
                            $staff2WhereCondition = array('emp_iid' => $res["highest_avg_rpct_employee_iid"], 'salon_id' => $this->salon_id, 'emp_iid != ' => 0);
                            $getStaffImageForRpct = $this->Common_model
                                                    ->getStaffImage($staff2WhereCondition)
                                                    ->row_array();
                            $temp1['highest_avg_rpct_employee_image'] = !empty($getStaffImageForRpct)? $getStaffImageForRpct['image']:'';
                        }
                        else
                        {
                            $temp1["highest_avg_rpct_employee_image"] = "";
                        }*/


                       /*$temp1['highest_avg_serviceTicket_value'] = isset($res["highest_avg_serviceTicket_value"]) && !empty($res["highest_avg_serviceTicket_value"]) && $res["highest_avg_serviceTicket_value"]!="0.00" ? $res['highest_avg_serviceTicket_value']:'0.00';
                       $temp1['highest_avg_serviceTicket_employee'] = isset($res["highest_avg_serviceTicket_value"]) && !empty($res["highest_avg_serviceTicket_employee"]) && $res["highest_avg_serviceTicket_value"]!="" ? $res['highest_avg_serviceTicket_employee']:'';

                       if(isset($res["highest_avg_serviceTicket_employee_iid"]) && !empty($res["highest_avg_serviceTicket_employee_iid"]))
                        {
                            $staff2WhereCondition = array('emp_iid' => $res["highest_avg_serviceTicket_employee_iid"], 'salon_id' => $salon_id, 'emp_iid != ' => 0);
                            $getStaffImage = $this->Common_model
                                                    ->getStaffImage($staff2WhereCondition)
                                                    ->row_array();
                            //print_r($getStaffImageForRpct);exit;
                            $temp1['highest_avg_serviceTicket_employee_image'] = !empty($getStaffImage)? $getStaffImage['image']:'';
                            
                        }
                        else
                        {
                            $temp1["highest_avg_serviceTicket_employee_image"] = "";
                        }*/

                        /*$temp1['highest_prebook_value'] = isset($res["highest_prebook_value"]) && !empty($res["highest_prebook_value"]) && $res["highest_prebook_value"]!="0.00" ? $res['highest_prebook_value']:'0.00';
                        $temp1['highest_prebook_sold_employee'] = isset($res["highest_prebook_sold_employee"]) && !empty($res["highest_prebook_sold_employee"]) && $res["highest_prebook_sold_employee"]!="" ? $res['highest_prebook_sold_employee']:'';

                        if(isset($res["highest_prebook_sold_employee_iid"]) && !empty($res["highest_prebook_sold_employee_iid"]))
                        {
                           $staff2WhereCondition = array('emp_iid' => $res["highest_prebook_sold_employee_iid"], 'salon_id' => $salon_id, 'emp_iid != ' => 0);
                           $getStaffImage = $this->Common_model
                                                    ->getStaffImage($staff2WhereCondition)
                                                    ->row_array();
                           $temp1['highest_prebook_sold_employee_image'] = !empty($getStaffImage)? $getStaffImage['image']:'';
                            
                        }
                        else
                        {
                            $temp1["highest_prebook_sold_employee_image"] = "";
                        }*/



                        $temp1['highest_product_revenue_value'] = isset($res["highest_retail_total_value"]) && !empty($res["highest_retail_total_value"]) && $res["highest_retail_total_value"]!="0.00" ? $res['highest_retail_total_value']:'0.00';
                        $temp1['highest_product_revenue_employee'] = isset($res["highest_retail_total_employee"]) && !empty($res["highest_retail_total_employee"]) && $res["highest_retail_total_employee"]!="" ? $res['highest_retail_total_employee']:'';
                        if(isset($res["highest_avg_retail_total_employee_iid"]) && !empty($res["highest_avg_retail_total_employee_iid"]))
                        {
                            $staff2WhereCondition = array('emp_iid' => $res["highest_avg_retail_total_employee_iid"], 'salon_id' => $salon_id, 'emp_iid != ' => 0);
                            $getStaffImage = $this->Common_model
                                                    ->getStaffImage($staff2WhereCondition)
                                                    ->row_array();
                            $temp1['highest_product_revenue_employee_image'] = !empty($getStaffImage)? $getStaffImage['image']:'';
                            
                        }
                        else
                        {
                            $temp1["highest_product_revenue_employee_image"] = "";
                        }

                        $temp1['highest_service_revenue_value'] = isset($res["highest_service_total_value"]) && !empty($res["highest_service_total_value"]) && $res["highest_service_total_value"]!="0.00" ? $res['highest_service_total_value']:'0.00';
                        $temp1['highest_service_revenue_employee'] = isset($res["highest_service_total_employee"]) && !empty($res["highest_service_total_employee"]) && $res["highest_service_total_employee"]!="" ? $res['highest_service_total_employee']:'';
                        if(isset($res["highest_service_total_employee_iid"]) && !empty($res["highest_service_total_employee_iid"]))
                            {
                                $staff2WhereCondition = array('emp_iid' => $res["highest_service_total_employee_iid"], 'salon_id' => $salon_id, 'emp_iid != ' => 0);
                                $getStaffImage = $this->Common_model
                                                    ->getStaffImage($staff2WhereCondition)
                                                    ->row_array();
                                $temp1['highest_service_revenue_employee_image'] = !empty($getStaffImage)? $getStaffImage['image']:'';
                                
                            }
                            else
                            {
                                $temp1["highest_service_revenue_employee_image"] = "";
                            }
                       
                        /*$temp1['highest_rebook_value'] = isset($res["highest_prebook_value"]) && !empty($res["highest_prebook_value"]) && $res["highest_prebook_value"]!="0.00" ? $res['highest_prebook_value']:'0.00';
                        $temp1['highest_rebook_employee'] = isset($res["highest_prebook_sold_employee"]) && !empty($res["highest_prebook_sold_employee"]) && $res["highest_prebook_sold_employee"]!="" ? $res['highest_prebook_sold_employee']:'';
                        if(isset($res["highest_prebook_sold_employee_iid"]) && !empty($res["highest_prebook_sold_employee_iid"]))
                        {
                            $staff2WhereCondition = array('emp_iid' => $res["highest_prebook_sold_employee_iid"], 'salon_id' => $salon_id, 'emp_iid != ' => 0);
                            $getStaffImage = $this->Common_model
                                                    ->getStaffImage($staff2WhereCondition)
                                                    ->row_array();
                            $temp1['highest_rebook_employee_image'] = !empty($getStaffImage)? $getStaffImage['image']:'';
                            
                        }
                        else
                        {
                            $temp1["highest_rebook_employee_image"] = "";
                        }
*/                        

                        $temp1['highest_new_guests_value'] = isset($res["highest_new_client_value"]) && !empty($res["highest_new_client_value"]) && $res["highest_new_client_value"]!="" ? $res['highest_new_client_value']:'0';

                        $temp1['highest_new_guests_employee'] = isset($res["highest_new_client_employee"]) && !empty($res["highest_new_client_employee"]) && $res["highest_new_client_employee"]!="" ? $res['highest_new_client_employee']:'';
                       
                        if(isset($res["highest_new_client_employee_iid"]) && !empty($res["highest_new_client_employee_iid"]))
                        {
                            $staff2WhereCondition = array('emp_iid' => $res["highest_new_client_employee_iid"], 'salon_id' => $salon_id, 'emp_iid != ' => 0);
                            $getStaffImage = $this->Common_model
                                                    ->getStaffImage($staff2WhereCondition)
                                                    ->row_array();
                            $temp1['highest_new_guests_employee_image'] = !empty($getStaffImage)? $getStaffImage['image']:'';
                            
                        }
                        else
                        {
                            $temp1["highest_new_guests_employee_image"] = "";
                        }

                       $temp1['highest_color_value'] = isset($res["highest_color_percent_value"]) && !empty($res["highest_color_percent_value"]) && $res["highest_color_percent_value"]!="" ? $res['highest_color_percent_value']:'0'; 

                       $temp1['highest_color_employee'] = isset($res["highest_color_percent_employee"]) && !empty($res["highest_color_percent_employee"]) && $res["highest_color_percent_employee"]!="" ? $res['highest_color_percent_employee']:'';

                     if(isset($res["highest_color_percent_employee_iid"]) && !empty($res["highest_color_percent_employee_iid"]))
                        {
                            $staff2WhereCondition = array('emp_iid' => $res["highest_color_percent_employee_iid"], 'salon_id' => $salon_id, 'emp_iid != ' => 0);
                            $getStaffImage = $this->Common_model
                                                    ->getStaffImage($staff2WhereCondition)
                                                    ->row_array();
                            $temp1['highest_color_employee_image'] = !empty($getStaffImage)? $getStaffImage['image']:'';                            
                        }
                        else
                        {
                            $temp1["highest_color_employee_image"] = "";
                        }

                       $temp1['highest_percent_booked_value'] = isset($res["highest_percent_booked_value"]) && !empty($res["highest_percent_booked_value"]) && $res["highest_percent_booked_value"]!="" ? $res['highest_percent_booked_value']:'0';  
                       $temp1['highest_percent_booked_employee'] = isset($res["highest_percent_booked_employee"]) && !empty($res["highest_percent_booked_employee"]) && $res["highest_percent_booked_employee"]!="" ? $res['highest_percent_booked_employee']:'';

                       if(isset($res["highest_percent_booked_employee_iid"]) && !empty($res["highest_percent_booked_employee_iid"]))
                        {
                            $staff2WhereCondition = array('emp_iid' => $res["highest_percent_booked_employee_iid"], 'salon_id' => $salon_id, 'emp_iid != ' => 0);
                            $getStaffImage = $this->Common_model
                                                    ->getStaffImage($staff2WhereCondition)
                                                    ->row_array();
                            $temp1['highest_percent_booked_employee_image'] = !empty($getStaffImage)? $getStaffImage['image']:''; 
                            
                        }
                        else
                        {
                            $temp1["highest_percent_booked_employee_image"] = "";
                        }


                        // NEWLY ADDED
                       /* $temp1['highest_percentage_retail_to_service_sales_value'] = isset($res["highest_percentage_retail_to_service_sales_value"]) && !empty($res["highest_percentage_retail_to_service_sales_value"]) && $res["highest_percentage_retail_to_service_sales_value"]!="" ? $res['highest_percentage_retail_to_service_sales_value']:'0';  
                       $temp1['highest_percentage_retail_to_service_sales_employee'] = isset($res["highest_percentage_retail_to_service_sales_employee"]) && !empty($res["highest_percentage_retail_to_service_sales_employee"]) && $res["highest_percentage_retail_to_service_sales_employee"]!="" ? $res['highest_percentage_retail_to_service_sales_employee']:'';

                       if(isset($res["highest_percentage_retail_to_service_sales_employee_iid"]) && !empty($res["highest_percentage_retail_to_service_sales_employee_iid"]))
                        {
                            $staff2WhereCondition = array('emp_iid' => $res["highest_percentage_retail_to_service_sales_employee_iid"], 'salon_id' => $salon_id, 'emp_iid != ' => 0);
                            $getStaffImage = $this->Common_model
                                                    ->getStaffImage($staff2WhereCondition)
                                                    ->row_array();
                            $temp1['highest_percentage_retail_to_service_sales_employee_image'] = !empty($getStaffImage)? $getStaffImage['image']:''; 
                            
                        }
                        else
                        {
                            $temp1["highest_percentage_retail_to_service_sales_employee_image"] = "";
                        } */
                        // CLOSED NEWLY ADDED


                       // Update Top Five Services/Products Report
                      // $this->__topFiveServicesAndProductOwnerReport($arrSalonDetails);
                      // close
                       $temp1['prebook_percentage'] = number_format($temp1['prebook_percentage'], 2, '.', '');
                       $temp1['RPCT'] = number_format($temp1['RPCT'], 2, '.', '');
                       $temp1['RPST'] = number_format($temp1['RPST'], 2, '.', '');
                       $temp1['estimated_sales'] = number_format($temp1['estimated_sales'], 2, '.', '');

                        if($year==1){
                            $reportsWhere = array('salon_id' => $this->salon_id,'report_type' => 'lastYearReport','dayRangeType' => $dayRangeType,'start_date' => $this->lastYearStartDate,'end_date' => $this->lastYearEndDate);
                            $this->startDate = $this->lastYearStartDate;
                            $this->endDate = $this->lastYearEndDate;
                            $temp1['start_date'] = $this->lastYearStartDate;
                            $temp1['end_date'] = $this->lastYearEndDate;
                        } else {
                            $reportsWhere = array('salon_id' => $this->salon_id,'report_type' => 'currentYearReport','dayRangeType' => $dayRangeType,'start_date' => $this->startDate,'end_date' => $this->endDate);
                        }
                       //pa($temp1,'temp1',false);


                        $reportsDataForSalon = $this->DashboardOwner_model
                                              ->getOwnerReportCalcCron($reportsWhere)
                                              ->row_array();
                       if(!empty($reportsDataForSalon))
                        {
                            $reportsDataForSalon['retail_units'] = number_format($reportsDataForSalon['retail_units'], 2, '.', '');
                            $diff_array = array_diff_assoc($temp1, $reportsDataForSalon);
                            //pa($temp1,"temp1");
                            if(empty($diff_array))
                            {
                                pa('No Updates');
                                continue;
                            }
                            else
                            {
                                //Update REPORT
                                /*pa($temp1,"temp1");
                                pa($reportsDataForSalon,"reportsDataForSalon");
                                pa($diff_array,"diff_array");*/
                                $diff_array["insert_status"] = OwnerReportsForDashboard::UPDATED;
                                $diff_array["updatedDate"] = date("Y-m-d H:i:s");
                                try {
                                    $reportsWhere = array('salon_id' => $salon_id,'report_type' => $report_type,'dayRangeType' => $dayRangeType,'start_date' => $this->startDate,'end_date' => $this->endDate);
                                    $update = $this->DashboardOwner_model->updateOwnerReportCron($reportsWhere,$diff_array);
                                    pa('Reports Data updated Successfully');
                                    
                                } catch (Exception $e) {
                                    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
                                }
                            }
                        }
                        else
                        {
                            //INSERT REPORT
                            $temp1["insert_status"] =OwnerReportsForDashboard::INSERTED;
                            $temp1["insertedDate"] = date("Y-m-d H:i:s");
                            $temp1["updatedDate"] = date("Y-m-d H:i:s");
                            try {
                                    $insert = $this->DashboardOwner_model->insertOwnerReportCron($temp1);
                                    pa('Reports Data Inserted Successfully');
                            } catch (Exception $e) {
                                echo 'Reports Insert failed: ' . $e->getMessage()."<br>";
                                
                            }
                        }                        
                     //  pa($reportsDataForSalon);                       
                    // Database Log
                    $log['id'] = $log_id;
                    $log_id = $this->Common_model->saveMillCronLogs($log);  
                    }else{
                        echo "Salon Details are not sufficient";
                    }
                }
            }else{
                echo "No SalonId's In Server";
            }
            
       } 
   
 }       