<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class OwnerReportsForDashboardLastYear extends CI_Controller
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
        $this->load->model('DashboardOwner_model');
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);

    }
    // Get Date Range Types
    function __getStartEndDate($dayRangeType,$year=1)
    {
        $this->dayRangeType =  $dayRangeType;
         switch ($this->dayRangeType) {
            case "today":
                //$MonthStartDate = date("Y-m-")."01";
                $this->startDate = $this->currentDate;
                $this->endDate = $this->currentDate;
                $this->lastYearStartDate =  getDateFn(strtotime("-$year year"));
                $this->lastYearEndDate = getDateFn(strtotime("-$year year"));
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
            break;
            case LASTMONTH:
                    $this->startDate = getDateFn(strtotime("first day of last month"));
                    $this->endDate = getDateFn(strtotime("last day of last month"));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
            break;
            case "Monthly":
                    $this->startDate = getDateFn(strtotime("first day of this month"));
                    $this->endDate = $this->currentDate;
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
            break;
            case LAST90DAYS:
                   
                    $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                    $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                    $this->endDate = getDateFn(strtotime("last day of last month"));
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
            break;
            case Yearly:
                   
                    $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                    $this->endDate = $this->currentDate;
                    $this->lastYearStartDate =  getDateFn(strtotime($this->startDate." -$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime($this->endDate." -$year year"));
            break;         
            default:
                    $this->startDate =  $this->currentDate;
                    $this->endDate   =  $this->currentDate;
                    $this->lastYearStartDate =  getDateFn(strtotime("-$year year"));
                    $this->lastYearEndDate = getDateFn(strtotime("-$year year"));
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
       pa($data,'Dates');
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
 

                $serviceSalesDetailsArrNewLastYear = $this->DashboardOwner_model
                                        ->getServiceInvoicesClientIdsCount($whereConditionsLRefundFalse)
                                        ->row_array();

                $serviceSalesDetailsArrPrebookTrueLastYear = $this->DashboardOwner_model
                                        ->getServiceInvoiceCountWithPrebookTrue($whereConditionsLPrebookTrue)
                                        ->row_array();

                // TOTAL SERVICE SALES
                $dataArray['last_year_total_service_sales'] = (!empty($serviceSalesDetailsArrNewLastYear) && $serviceSalesDetailsArrNewLastYear['nprice'] ) ? $this->Common_model->appCloudNumberFormat($serviceSalesDetailsArrNewLastYear['nprice'],2)  : '0.00';   
                           
                $dataArray['last_year_prebook_percentage'] = (!empty($serviceSalesDetailsArrNewLastYear["unique_client_count"]) && $serviceSalesDetailsArrPrebookTrueLastYear["unique_client_count"] ) ? $this->Common_model->appCludRoundCalc(
                    $serviceSalesDetailsArrPrebookTrueLastYear["unique_client_count"],$serviceSalesDetailsArrNewLastYear["unique_client_count"],100, 2)  : '0.00';
                $productSalesDetailsArrLastYear = $this->DashboardOwner_model
                                        ->getTotalProductSales($whereConditions)
                                        ->row_array();
                $productInvoicesArrLastYear = $this->DashboardOwner_model
                                        ->getProductInvoicesClientIdsCount($whereConditionsLRefundFalse)
                                        ->row_array();
                $dataArray['last_year_total_retail_sales'] = (!empty($productSalesDetailsArrLastYear) && $productSalesDetailsArrLastYear['nprice'] ) ? $this->Common_model->appCloudNumberFormat($productSalesDetailsArrLastYear['nprice'],2)  : '0.00';
                $dataArray['last_year_total_sales'] = (!empty($serviceSalesDetailsArrNewLastYear['nprice']) && $productSalesDetailsArrLastYear['nprice'] ) ? $this->Common_model->appCloudNumberFormat($serviceSalesDetailsArrNewLastYear['nprice']+$productSalesDetailsArrLastYear['nprice'],2)  : '0.00';

                /*$dataArray['last_year_RPCT'] = (!empty($productSalesDetailsArrLastYear['nprice']) && !empty($productInvoicesArrLastYear['invoice_count'])) ? $this->Common_model->appCloudNumberFormat($productSalesDetailsArrLastYear["nprice"]/$productInvoicesArrLastYear["invoice_count"],2)  : '0.00';*/

                 // RPCT CALCULATIONS CHANGE FOR LEVEL SALON
                 // get unique clients counts for level salon
                $serviceSalesClientIds = $this->DashboardOwner_model
                                         ->getServiceSalesClientIdsForClientIds($whereConditionsLRefundFalse)
                                         ->result_array();
                //pa($this->db->last_query());                         
                $serviceClientIds = !empty($serviceSalesClientIds)?array_column($serviceSalesClientIds, "service_client_ids"):array();
                $serviceClientCount = !empty($serviceClientIds) ? count($serviceClientIds) : 0;
                $productSalesClientIds = $this->DashboardOwner_model
                                         ->getProductSalesClientIdsForClientIds($whereConditionsLRefundFalse)
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
                
                if($rpct_type==2){

                    /*$dataArray['last_year_RPCT'] = ($serviceSalesDetailsArrNewLastYear['unique_client_count'] > 0 && $productInvoicesArrLastYear['unique_client_count'] > 0 ) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($productInvoicesArrLastYear["unique_client_count"],$serviceSalesDetailsArrNewLastYear["unique_client_count"],2)  : '0.00';*/
                    if(!empty($productSalesDetailsArrLastYear["nprice"]) && !empty($totalUniqueClients))
                    {
                        $dataArray["last_year_RPCT"] = $this->Common_model->appCludRoundCalcWithOutMultiplication($productSalesDetailsArrLastYear["nprice"],$totalUniqueClients, 2);
                    }
                    else
                    {
                        $dataArray["last_year_RPCT"] = "0.00";
                    }

                }else{
                    $dataArray['last_year_RPCT'] = (!empty($productSalesDetailsArrLastYear['nprice']) && !empty($productInvoicesArrLastYear['invoice_count'])) ? $this->Common_model->appCloudNumberFormat($productSalesDetailsArrLastYear["nprice"]/$productInvoicesArrLastYear["invoice_count"],2)  : '0.00';
                }
               // pa($productInvoicesArrLastYear); 
               // pa($serviceSalesDetailsArrNewLastYear); 
               // pa($dataArray,'dataArray',false);
                
                $dataArray['last_year_avgServiceTicket'] = (!empty($serviceSalesDetailsArrNewLastYear['nprice']) && !empty($serviceSalesDetailsArrNewLastYear['invoice_count'])) ? $this->Common_model->appCloudNumberFormat($serviceSalesDetailsArrNewLastYear["nprice"]/$serviceSalesDetailsArrNewLastYear["invoice_count"],2)  : '0.00';
                $dataArray['last_year_avgRetailTicket'] = (!empty($productSalesDetailsArrLastYear['nprice']) && !empty($productInvoicesArrLastYear['invoice_count'])) ? $this->Common_model->appCloudNumberFormat($productSalesDetailsArrLastYear["nprice"]/$productInvoicesArrLastYear["invoice_count"],2)  : '0.00';
                
               //Gift Card SALES
                $gcSalesDetailsArr = $this->DashboardOwner_model
                                        ->getTotalGiftCardSales($whereConditions)
                                        ->row_array();
                $dataArray['last_year_total_gift_card_sales'] = (!empty($gcSalesDetailsArr) && !empty($gcSalesDetailsArr['nprice'])) ? $this->Common_model->appCloudNumberFormat($gcSalesDetailsArr['nprice'],2)  : '0.00';                         
               
                $queryss = "cservicedescription";
                $like_conditions = $this->__make_like_conditions($queryss, $color_field_array);
                $totalColorServiceSales = $this->DashboardOwner_model
                                        ->getTotalColorServiceSales($whereConditionsLRefundFalse,$like_conditions)
                                        ->row_array();

                // %color calculation
                $dataArray['last_year_total_color_service_sales'] = (!empty($totalColorServiceSales) && $totalColorServiceSales['nprice']) ? $this->Common_model->appCloudNumberFormat(($totalColorServiceSales['nprice']),2)  : '0.00';

                $dataArray['last_year_color_percentage'] = (!empty($serviceSalesDetailsArrNewLastYear['nprice']) && $totalColorServiceSales['nprice']) ? $this->Common_model->appCloudNumberFormat(($totalColorServiceSales['nprice']/$serviceSalesDetailsArrNewLastYear['nprice'])*100,2)  : '0.00';

                //GET NEW SERVICED CLIENT IDS FROM APPOINTMENTS


                /*$sql_get_new_clients_from_appts = $this->DashboardOwner_model
                                                  ->getServicedClientIdsFromAppointments($wherearray)
                                                  ->row_array();
                $dataArray["last_year_new_guest_qty"] =  !empty($sql_get_new_clients_from_appts['new_client_count']) ? $sql_get_new_clients_from_appts['new_client_count']:'0';
                //GET REPEATED SERVICED CLIENTS IDS FROM APPOINTMENTS
                $sql_get_repeated_clients_from_appts = $this->DashboardOwner_model
                                                  ->getRepeatedServicedClientIdsFromAppointments($wherearray)
                                                  ->row_array();
                $dataArray["last_year_repeated_guest_qty"] =  !empty($sql_get_repeated_clients_from_appts['repeated_client_count']) ? $sql_get_repeated_clients_from_appts['repeated_client_count']:'0';*/
                // print $dayRangeType;  
                if($dayRangeType=='today'){
                   // new guest
                   $sql_get_new_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as new_client_count FROM 
                    ".MILL_CLIENTS_TABLE." client 
                    join ".MILL_PAST_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                    WHERE 
                    appts.AccountNo = '".$salonAccountNo."' and 
                    appts.SlcStatus != '2' and 
                    client.AccountNo = '".$salonAccountNo."' and
                    date(appts.AppointmentDate) >= '".$this->startDate."' and 
                    date(appts.AppointmentDate) <= '".$this->endDate."' and 

                    ((date(client.clientFirstVistedDate) >= '".$this->startDate."' 
                    and 
                    date(client.clientFirstVistedDate) <= '".$this->endDate."') or (client.clientFirstVistedDate='0001-01-01 00:00:00')) 
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
                    date(appts.AppointmentDate) >= '".$this->startDate."' and 
                    date(appts.AppointmentDate) <= '".$this->endDate."' and 

                    ((date(client.clientFirstVistedDate)!=date(client.clientLastVistedDate)) 
                    or (client.clientFirstVistedDate!='0001-01-01 00:00:00')) 
                    and appts.ClientId !='-999'")->row_array();

                   $repeated_guest_qty = $sql_get_repeat_clients_from_appts['repeat_client_count'];
                }else{

                   $clientscount_data['start_date'] = $data['start_date'];
                   $clientscount_data['end_date'] = $data['end_date'];
                   $clientscount_data['salonAccountNo'] = $salonAccountNo;
                   pa($clientscount_data);

                   $new_guest_qty = $this->DashboardOwner_model->getNewGuestCount($clientscount_data);

                   $total_guest_qty = $this->DashboardOwner_model->getTotalClientsCount($clientscount_data);
                   $repeated_guest_qty = $total_guest_qty-$new_guest_qty;
                   if($repeated_guest_qty<0){
                    $repeated_guest_qty = 0;
                    $errors['code'] = 'Repeated Guest Qty Error -- Lastyear';
                    $error_message= "<p>".$salonAccountNo."</p>";
                    $error_message.= "<p>Day Range Type".$dayRangeType."</p>";
                    $error_message.= "<p>Total Guest Qty".$total_guest_qty."</p>";
                    $error_message.= "<p>Is Getting Negative Value</p>";
                    $errors['message'] = $error_message;
                    $errors['tablename'] = 'mill_owner_report_calculations_cron';
                    send_mail_database_error($errors);
                   }
                }

                $dataArray['last_year_new_guest_qty'] = $new_guest_qty;
                $dataArray['last_year_repeated_guest_qty'] = $repeated_guest_qty;


                pa($dataArray,'dataArray',false);


                //%BOOKED
                $total_scheduled_hours = $this->DashboardOwner_model
                                           ->getTotalScheduledHours($whereConditionsScheduleHours)
                                           ->row_array();
                $totalScheduledHours =  !empty($total_scheduled_hours) && isset($total_scheduled_hours['nhours']) && !empty($total_scheduled_hours['nhours']) ? $total_scheduled_hours['nhours']:'0';

                
                $booked_hours_count = $this->DashboardOwner_model
                                           ->getTotalHoursBooked($wherearray)
                                           ->row_array();
                $totalHoursBookedForSalon =  !empty($booked_hours_count) && isset($booked_hours_count['totalhours']) && !empty($booked_hours_count['totalhours']) ? $booked_hours_count['totalhours']:'0';
                $dataArray['percentage_booked'] =  !empty($totalScheduledHours) && isset($totalHoursBookedForSalon) ? $this->Common_model->appCloudNumberFormat(($totalHoursBookedForSalon/$totalScheduledHours), 2):'0';
               // pa($dataArray,'dataArray');
                return $dataArray;  
                                         
            }else{
                 return array(); 
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
    function setOwnerReportsForDashboardLastYear($dayRangeType="today",$year=1,$salon_id="")
        { 
            $this->currentDate = getDateFn();
            $getAllSalons = $this->Common_model->getAllSalons($salon_id);
            if(isset($getAllSalons["mill_salons"]) && !empty($getAllSalons["mill_salons"]))
            {
                foreach($getAllSalons["mill_salons"] as $salonsData)
                {
                    pa('',"Reports Cron Running For--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                    $this->salon_id = $salonsData['salon_id'];
                    $this->salonDetails = $salonDetails = $this->Common_model->getSalonInfoBy($this->salon_id);
                    
                    if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"]) && isset($salonDetails['salon_info']["service_retail_reports_enabled"]) && $salonDetails['salon_info']["millennium_enabled"]=="Yes" && $salonDetails['salon_info']["service_retail_reports_enabled"]=="Yes"){
                        // Database Log
                        $log['AccountNo'] = $salonDetails['salon_info']['salon_code'];
                        $log['salon_id'] = $salonDetails['salon_info']['salon_id'];
                        $log['StartingTime'] = date('Y-m-d H:i:s');
                        $log['whichCron'] = 'setOwnerReportsForDashboardLastYear';
                        $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        $log['CronType'] = 1;
                        $log['id'] = 0;
                        $log_id = $this->Common_model->saveMillCronLogs($log);
                        // GET START DATE AND END DATE AS PER PARAMETERS
                        $this->__getStartEndDate($dayRangeType,$year);
                        $arrData['start_date'] = $this->lastYearStartDate;
                        $arrData['end_date'] = $this->lastYearEndDate;
                        $arrData['status'] = true;
                        $arrSalonDetails['salon_id'] = $this->salon_id;
                        $arrSalonDetails['start_date'] = $this->lastYearStartDate;
                        $arrSalonDetails['end_date'] = $this->lastYearEndDate;
                        $arrSalonDetails['dayRangeType'] = $dayRangeType;                        
                        $res = $this->__getSalonReportForOwner($arrSalonDetails);
                        $dataArray["currentYearReport"] = array();
                        $dataArray["lastYearReport"] = array();
                        $temp1 = array();
                        $temp1['salon_id'] = $this->salon_id;
                        $temp1['start_date'] = $this->lastYearStartDate;
                        $temp1['end_date'] = $this->lastYearEndDate;
                        $temp1['report_type'] = "lastYearReport";
                        $temp1['dayRangeType'] = !empty($dayRangeType)?$dayRangeType:'';
                        
                        $temp1['service_revenue'] = isset($res["last_year_total_service_sales"]) && !empty($res["last_year_total_service_sales"]) && $res["last_year_total_service_sales"]!="0.00" ? $res['last_year_total_service_sales']:'0';
                        $temp1['total_retail_price'] = isset($res["last_year_total_retail_sales"]) && !empty($res["last_year_total_retail_sales"]) && $res["last_year_total_retail_sales"]!="0.00" ? $res['last_year_total_retail_sales']:'0';
                        $temp1['gift_cards'] = isset($res["last_year_total_gift_card_sales"]) && !empty($res["last_year_total_gift_card_sales"]) && $res["last_year_total_gift_card_sales"]!="0.00" ? $res['last_year_total_gift_card_sales']:'0';
                        $temp1['color_revenue'] = isset($res["last_year_total_color_service_sales"]) && !empty($res["last_year_total_color_service_sales"]) && $res["last_year_total_color_service_sales"]!="0.00" ? $res['last_year_total_color_service_sales']:'0';
                        $temp1['color_percentage'] = isset($res["last_year_color_percentage"]) && !empty($res["last_year_color_percentage"]) && $res["last_year_color_percentage"]!="0.00" ? $res['last_year_color_percentage']:'0';
                        $temp1['guest_qty_new'] = isset($res["last_year_new_guest_qty"]) && !empty($res["last_year_new_guest_qty"]) && $res["last_year_new_guest_qty"]!="0" ? $res['last_year_new_guest_qty']:'0';
                        $temp1['guest_qty_repeated'] = isset($res["last_year_repeated_guest_qty"]) && !empty($res["last_year_repeated_guest_qty"]) && $res["last_year_repeated_guest_qty"]!="0" ? $res['last_year_repeated_guest_qty']:'0';
                        $temp1['RPCT'] = isset($res["last_year_RPCT"]) && !empty($res["last_year_RPCT"]) && $res["last_year_RPCT"]!="0" ? $res['last_year_RPCT']:'0';
                        $temp1['avg_service_ticket'] = isset($res["last_year_avgServiceTicket"]) && !empty($res["last_year_avgServiceTicket"]) && $res["last_year_avgServiceTicket"]!="0" ? $res['last_year_avgServiceTicket']:'0';
                        $temp1['avg_retail_ticket'] = isset($res["last_year_avgRetailTicket"]) && !empty($res["last_year_avgRetailTicket"]) && $res["last_year_avgRetailTicket"]!="0" ? $res['last_year_avgRetailTicket']:'0';
                        $temp1['prebook_percentage'] = isset($res["last_year_prebook_percentage"]) && !empty($res["last_year_prebook_percentage"]) && $res["last_year_prebook_percentage"]!="0" ? $res['last_year_prebook_percentage']:'0';
                        $temp1['percent_booked'] = isset($res["percentage_booked"]) && !empty($res["percentage_booked"]) ? $res['percentage_booked']:'0';
                       
                        $reportsWhere = array('salon_id' => $this->salon_id,'report_type' => 'lastYearReport','dayRangeType' => $dayRangeType,'start_date' => $this->lastYearStartDate,'end_date' => $this->lastYearEndDate);

                        $reportsDataForSalon = $this->DashboardOwner_model
                                              ->getOwnerReportCalcCron($reportsWhere)
                                              ->row_array();
                      //  pa($reportsDataForSalon,'reportsWhere',true);                      
                       if(!empty($reportsDataForSalon))
                        {
                            $diff_array = array_diff_assoc($temp1, $reportsDataForSalon );
                            
                            if(empty($diff_array))
                            {
                               pa('No Updates');
                            }
                            else
                            {
                                //Update REPORT
                                $temp1["insert_status"] = OwnerReportsForDashboardLastYear::UPDATED;
                                $temp1["updatedDate"] = date("Y-m-d H:i:s");
                                try {
                                    $reportsWhere = array('salon_id' => $salon_id,'report_type' => 'lastYearReport','dayRangeType' => $dayRangeType,'start_date' => $this->lastYearStartDate,'end_date' => $this->lastYearEndDate);
                                    $update = $this->DashboardOwner_model->updateOwnerReportCron($reportsWhere,$diff_array);
                                    pa($diff_array,'Reports Data updated Successfully');
                                    
                                } catch (Exception $e) {
                                    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
                                }
                            }
                        }
                        else
                        {
                            //INSERT REPORT
                            
                            $temp1["insert_status"] =OwnerReportsForDashboardLastYear::INSERTED;
                            $temp1["insertedDate"] = date("Y-m-d H:i:s");
                            $temp1["updatedDate"] = date("Y-m-d H:i:s");
                            try {
                                    $insert = $this->DashboardOwner_model->insertOwnerReportCron($temp1);
                                    pa($this->db->insert_id(),'Reports Data Inserted Successfully');
                            } catch (Exception $e) {
                                echo 'Reports Insert failed: ' . $e->getMessage()."<br>";
                                
                            }
                        }                        
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