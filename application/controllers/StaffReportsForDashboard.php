<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class StaffReportsForDashboard extends CI_Controller
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
        $this->load->model('ColorPercentage_model');
        $this->load->model('Leaderboard_model');
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
            case "yearly":
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
            case "yearly":
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

    function __getSalonReportForStaff($data){
        $arrdbData = array();
        //pa($data,'Dates',false);
        if(!empty($data["salon_id"]) && !empty($data["start_date"]) && !empty($data["end_date"]) && !empty($data["staff_id"]) )
            {

                $startDate = $data['start_date'];
                $endDate = $data['end_date'];
                $salon_id = $data['salon_id'];
                $staff_id = $data['staff_id'];
                $iempid =  $data['staff_iid'];
                $dayRangeType = $data['dayRangeType'];
                $salonDetails = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)
                                                   ->row_array();
                $salonAccountNo = $salonDetails['salon_account_id'];
                $rpct_type = $salonDetails['rpct_type'];                
                $whereConditions = array('account_no' =>$salonAccountNo , 'tdatetime >=' =>$startDate, 'tdatetime <=' =>$endDate,'iempid'=>$iempid);
                $whereConditionsLRefundFalse = array('account_no' =>$salonAccountNo ,'tdatetime >=' =>$startDate, 'tdatetime <=' =>$endDate,'lrefund' => 'false','iempid'=>$iempid);
                $whereConditionsLPrebookTrue = array('account_no' =>$salonAccountNo , 'tdatetime >=' =>$startDate, 'tdatetime <=' =>$endDate,'lrefund' => 'false','lprebook' => 'true','iempid'=>$iempid);
                $whereConditionsScheduleHours = array('account_no' =>$salonAccountNo , 'start_date >=' =>$startDate, 'end_date <=' =>$endDate,'cworktype' => 'Work Time','dayRangeType' => $dayRangeType,'iempid'=>$iempid);
                $wherearray = array('salonAccountNo'=>$salonAccountNo,'startDate'=>$startDate,'endDate'=>$endDate,'iempid'=>$iempid);
                $wherearrayDayRangeType = array('salonAccountNo'=>$salonAccountNo,'startDate'=>$startDate,'endDate'=>$endDate,'dayRangeType'=>$dayRangeType,'iempid'=>$iempid);                              
 

                $getTotalServiceSales = $this->DashboardOwner_model
                                        ->getTotalServiceSales($whereConditions)
                                        ->row_array();
                $total_service_sales =  $dataArray['total_service_sales'] = (!empty($getTotalServiceSales['nprice']) && $getTotalServiceSales['nprice'] > 0 ) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice'],2)  : '0.00';


                $getTotalProductSales = $this->DashboardOwner_model
                                        ->getTotalProductSales($whereConditions)
                                        ->row_array();
                $dataArray['total_retail_price'] = (!empty($getTotalProductSales['nprice']) && $getTotalProductSales['nprice'] > 0 ) ? $this->Common_model->appCloudNumberFormat($getTotalProductSales['nprice'],2)  : '0.00';



                //Total Gift Card SALES
                 $getTotalGiftCardSales = $this->DashboardOwner_model
                                        ->getTotalGiftCardSales($whereConditions)
                                        ->row_array();
                 $dataArray['gift_cards'] = (!empty($getTotalGiftCardSales['nprice']) && $getTotalGiftCardSales['nprice'] > 0)  ? $this->Common_model->appCloudNumberFormat($getTotalGiftCardSales['nprice'],2)  : '0.00';                         
                
                //TOTAL SALES (RETAIL + SERVICE)
                if(($salon_id=='536') || ($salon_id=='537') || ($salon_id=='538') || ($salon_id=='539') || ($salon_id=='540')){
                    $dataArray['total_sales'] = !empty($getTotalServiceSales['nprice']) || !empty($getTotalProductSales['nprice']) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice']+$getTotalProductSales['nprice'],2)  : '0.00';
                }else{
                    $dataArray['total_sales'] = !empty($getTotalServiceSales['nprice']) || !empty($getTotalProductSales['nprice']) || !empty($getTotalGiftCardSales['nprice'])  ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice']+$getTotalProductSales['nprice']+$getTotalGiftCardSales['nprice'],2)  : '0.00';

                }
                
                if(($dayRangeType=='today') || ($dayRangeType=='lastweek') || ($dayRangeType=='Monthly') || ($dayRangeType=='Yearly')){
                    if(($salon_id=='536') || ($salon_id=='537') || ($salon_id=='538') || ($salon_id=='539') || ($salon_id=='540')){
                        $whereConditionsScheduleHoursJoseph = array('account_no' =>$salonAccountNo , 'start_date =' =>$startDate, 'end_date =' =>$endDate,'cworktype' => 'Work Time','dayRangeType' => $dayRangeType,'iempid'=>$iempid);

                        $getTotalScheduledHours = $this->DashboardOwner_model
                                           ->getTotalScheduledHoursJoesph($whereConditionsScheduleHoursJoseph)
                                           ->row_array();


                    }else{

                        $getTotalScheduledHours = $this->DashboardOwner_model
                                               ->getTotalScheduledHours($whereConditionsScheduleHours)
                                               ->row_array();


                    }
                }else{

                    $getTotalScheduledHours = $this->DashboardOwner_model
                                               ->getTotalScheduledHours($whereConditionsScheduleHours)
                                               ->row_array();

                }
                

                // Percentage BOOKED
                 

                //pa($this->db->last_query(),'getTotalScheduledHours');
                $totalScheduledHours = !empty($getTotalScheduledHours) && isset($getTotalScheduledHours['nhours']) && !empty($getTotalScheduledHours['nhours']) ?  $getTotalScheduledHours['nhours']  : '0';
              
                
                $getTotalHoursBooked = $this->DashboardOwner_model
                                           ->getTotalHoursBookedStaff($wherearray)
                                           ->row_array();
                //pa($this->db->last_query(),'getTotalHoursBooked');                           
                $totalHoursBookedForSalon = !empty($getTotalHoursBooked) && isset($getTotalHoursBooked['totalhours']) && !empty($getTotalHoursBooked['totalhours']) ?  $getTotalHoursBooked['totalhours']  : '0';

                //pa($totalScheduledHours,'totalScheduledHours');
                //pa($totalHoursBookedForSalon,'totalHoursBookedForSalon');

                $dataArray['percent_booked'] = !empty($totalScheduledHours) && !empty($totalHoursBookedForSalon)  ? $this->Common_model->appCloudNumberFormat(($totalHoursBookedForSalon/$totalScheduledHours)*100,2)  : '0.00';






               /* $clientscount_data['start_date'] = $startDate;
                $clientscount_data['end_date'] = $endDate;
                $clientscount_data['iempid'] = $iempid;
                $clientscount_data['salonAccountNo'] = $salonAccountNo;
                $new_guest = $this->DashboardOwner_model
                                  ->getNewGuestCountStaff($clientscount_data);
                $dataArray['new_guest_qty'] = (!empty($new_guest) && $new_guest > 0 ) ? $new_guest  : 0;
                $total_guest_qty = $this->DashboardOwner_model->getTotalClientsCountStaff($clientscount_data);
                $repeated_guest_qty = $total_guest_qty-$new_guest;
                $dataArray['repeated_guest_qty'] = (!empty($repeated_guest_qty) && $repeated_guest_qty > 0 ) ? $repeated_guest_qty  : 0;*/

                if($this->dayRangeType=='today'){
                         $sql_get_new_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as new_client_count FROM mill_clients client join mill_appointments appts on appts.ClientId=client.ClientId
                                WHERE
                        appts.AccountNo = '".$salonAccountNo."' and
                        appts.SlcStatus != 'Deleted' and
                        client.AccountNo = '".$salonAccountNo."' and
                        appts.iempid = '".$iempid."' and
                        str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$this->startDate."' and
                        str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$this->endDate."' and
                        ((date(client.clientFirstVistedDate) = '0001-01-01' and date(client.clientLastVistedDate) = '0001-01-01') or
                        (str_to_date(AppointmentDate, '%m/%d/%Y') = date(client.clientFirstVistedDate) and  str_to_date(AppointmentDate, '%m/%d/%Y') = date(client.clientLastVistedDate))
                        )")->row_array();
                    //pa($this->DB_ReadOnly->last_query(),"new guest");

                    $new_guest = $sql_get_new_clients_from_appts['new_client_count'];

                    $sql_get_total_clients_from_appts = $this->DB_ReadOnly->query("SELECT count(DISTINCT client.ClientId) as total_client_count FROM 
                            ".MILL_CLIENTS_TABLE." client 
                            join ".MILL_APPTS_TABLE." appts on appts.ClientId=client.ClientId 
                            WHERE 
                            appts.AccountNo = '".$salonAccountNo."' and 
                            appts.SlcStatus != 'Deleted' and 
                            appts.ClientId != '-999' and 
                            appts.iempid = '".$iempid."' and 
                            client.AccountNo = '".$salonAccountNo."' and 
                            str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$this->startDate."' and 
                            str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$this->endDate."'")->row_array();

                    $total_guest_qty =  !empty($sql_get_total_clients_from_appts['total_client_count']) ? $sql_get_total_clients_from_appts['total_client_count']:'0';


                    }else{

                        $clientscount_data['start_date'] = $this->startDate;
                        $clientscount_data['end_date'] = $this->endDate;
                        $clientscount_data['iempid'] = $iempid;
                        $clientscount_data['salonAccountNo'] = $salonAccountNo;
                        $new_guest = $this->DashboardOwner_model
                                          ->getNewGuestCountStaff($clientscount_data);

                        $total_guest_qty = $this->DashboardOwner_model->getTotalClientsCountStaff($clientscount_data);

                    }
                $repeated_guest_qty = $total_guest_qty-$new_guest;

                $dataArray['new_guest_qty'] = (!empty($new_guest) && $new_guest > 0 ) ? $new_guest  : 0;
                $dataArray['repeated_guest_qty'] = (!empty($repeated_guest_qty) && $repeated_guest_qty > 0 ) ? $repeated_guest_qty  : 0;


                if($repeated_guest_qty<0){
                    $repeated_guest_qty = 0;
                    $errors['code'] = 'Repeated Guest Qty Error -- Staff Dashboard';
                    $error_message= "<p>".$salonAccountNo."</p>";
                    $error_message.= "<p>Day Range Type".$dayRangeType."</p>";
                    $error_message.= "<p>Total Guest Qty".$total_guest_qty."</p>";
                    $error_message.= "<p>Is Getting Negative Value</p>";
                    $errors['message'] = $error_message;
                    $errors['tablename'] = 'mill_owner_report_calculations_cron';
                    send_mail_database_error($errors);
                }

                $serviceSalesInvoicesAndClientIds = $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count')
                    ->get_where(MILL_SERVICE_SALES,$whereConditionsLRefundFalse)
                    ->row_array();
                
                $invoice_count = !empty($serviceSalesInvoicesAndClientIds) ? $serviceSalesInvoicesAndClientIds['invoice_count'] : 0;

                $dataArray['avgServiceTicket'] = (!empty($getTotalServiceSales['nprice']) && !empty($invoice_count)) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales["nprice"]/$invoice_count,2)  : '0.00';
                

                $dataArray['avg_service_ticket'] = (!empty($getTotalServiceSales['nprice']) && !empty($invoice_count)) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales["nprice"]/$invoice_count,2)  : '0.00';





                $getProductSalesInvoicesAndClientIds = $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count')
                    ->get_where(MILL_PRODUCT_SALES,$whereConditionsLRefundFalse)
                    ->row_array();
                $product_invoice_count = !empty($getProductSalesInvoicesAndClientIds) ? $getProductSalesInvoicesAndClientIds['invoice_count'] : 0;                        

                $dataArray['avgRetailTicket'] = (!empty($getTotalProductSales['nprice']) && !empty($product_invoice_count)) ? $this->Common_model->appCloudNumberFormat($getTotalProductSales["nprice"]/$product_invoice_count,2)  : '0.00';

                $serviceSalesInvoicesAndClientIdsWithPrebookTrue = $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count')
                    ->get_where(MILL_SERVICE_SALES,$whereConditionsLPrebookTrue)
                    ->row_array();
                
                $invoice_count_lprebooktrue = !empty($serviceSalesInvoicesAndClientIdsWithPrebookTrue) ? $serviceSalesInvoicesAndClientIdsWithPrebookTrue['invoice_count'] : 0;

                $dataArray['prebook_percentage'] = (!empty($invoice_count_lprebooktrue) && !empty($invoice_count)) ? $this->Common_model->appCloudNumberFormat(($invoice_count_lprebooktrue/$invoice_count)*100,2)  : '0.00';

                $totalUniqueClientsQry = $this->DB_ReadOnly->query("SELECT COUNT( * ) AS total_clients
                                                                FROM (
                                                                SELECT DISTINCT CONCAT( cinvoiceno, iclientid ) AS total
                                                                FROM mill_service_sales
                                                                WHERE `account_no` = '".$salonAccountNo."'
                                                                AND tdatetime >= '".$startDate."'
                                                                AND tdatetime <= '".$endDate."'
                                                                AND iempid = '".$iempid."'
                                                                AND `lrefund` = 'false'
                                                                UNION SELECT DISTINCT CONCAT( cinvoiceno, iclientid ) AS total
                                                                FROM mill_product_sales
                                                                WHERE `account_no` = '".$salonAccountNo."'
                                                                AND tdatetime >= '".$startDate."'
                                                                AND tdatetime <= '".$endDate."'
                                                                AND iempid = '".$iempid."'
                                                                AND `lrefund` = 'false'
                                                                ) AS total")->row_array();
                $totalUniqueClients = !empty($totalUniqueClientsQry) ? $totalUniqueClientsQry['total_clients'] : 0;

                if($rpct_type ==2)
                {
                    $dataArray['RPCT'] = (!empty($getTotalProductSales['nprice']) && !empty($totalUniqueClients)) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($getTotalProductSales['nprice'],$totalUniqueClients,2)  : '0.00';
                         
                }
                else {
                    $dataArray['RPCT'] = (!empty($getTotalProductSales['nprice']) && !empty($product_invoice_count)) ? $this->Common_model->appCludRoundCalcWithOutMultiplication($getTotalProductSales["nprice"],$product_invoice_count,2)  : '0.00';
                    
                }
                $dataArray['color_percentage'] = $this->ColorPercentage_model->getColorPercentage($whereConditions);

                $estimated_sales = !empty($total_service_sales) ?   ($total_service_sales / $this->currentdatenumber)*$this->currentnumberofdaysmonth  : '0.0';
                //pa($estimated_sales);
                $dataArray['estimated_sales'] = !empty($estimated_sales) ? $estimated_sales : '0.00';

                //pa($dataArray,'dataArray');  


                /*$dataArray['avgServiceTicket'] = (!empty($getTotalServiceSales['nprice']) && !empty($getServiceInvoicesClientIdsCount['invoice_count']) ) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice']/$getServiceInvoicesClientIdsCount["invoice_count"],2)  : '0.00';*/
                


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
    function setStaffDashboard($dayRangeType="today",$salon_id="",$year="")
        { 
            $this->dayRangeType = $dayRangeType;
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
                            $log['whichCron'] = 'setStaffDashboardLastYear';
                        } else {
                            $log['whichCron'] = 'setStaffDashboard';
                        }
                        
                        $log['CronUrl'] = MAIN_SERVER_URL.'StaffReportsForDashboard/setStaffDashboard/'.$dayRangeType.'/'.$salon_id;
                        $log['CronType'] = 1;
                        $log['id'] = 0;
                        $log_id = $this->Common_model->saveMillCronLogs($log);
                        // GET START DATE AND END DATE AS PER PARAMETERS
                        

                        if($year==1){
                            $this->__getStartEndDateLastYear($dayRangeType);
                        } else {
                            $this->__getStartEndDate($dayRangeType);
                        }

                        $arrSalonDetails['salon_id'] = $this->salon_id;
                        
                        if($year==1){
                            $arrSalonDetails['start_date'] = $this->lastYearStartDate;
                            $arrSalonDetails['end_date'] = $this->lastYearEndDate;
                        } else {
                            $arrSalonDetails['start_date'] = $this->startDate;
                            $arrSalonDetails['end_date'] = $this->endDate;
                        }

                        $arrSalonDetails['dayRangeType'] = $dayRangeType;
                        $arrSalonDetails['salonAccountNo'] = $salonDetails['salon_info']['salon_code'];
                        // Get Leaderboard

                        $leaderboard = $this->Leaderboard_model->getStaffLeaderboard($arrSalonDetails);
                        //pa($leaderboard,'leaderboard');

                        $getAllStaff = $this->Common_model->getAllStaffMembersBySalon($this->salon_id);
                        //pa($getAllStaff,'Staff');
                        if(isset($getAllStaff["getAllStaff"]) && !empty($getAllStaff["getAllStaff"]))
                        {
                            foreach($getAllStaff["getAllStaff"] as $staffMembers)
                            {
                                
                                $arrSalonDetails['staff_id'] = isset($staffMembers["staff_id"])? $staffMembers["staff_id"] : '';
                                $arrSalonDetails['staff_iid'] = isset($staffMembers["emp_iid"])? $staffMembers["emp_iid"] : 0;
                                $res = $this->__getSalonReportForStaff($arrSalonDetails);
                                $res['highest_rebook_value']  = $leaderboard['highest_rebook_value'];
                                $res['highest_rebook_sold_employee']  = $leaderboard['highest_rebook_sold_employee'];
                                $res['highest_rebook_sold_employee_image']  = $leaderboard['highest_rebook_sold_employee_image'];
                                $res['highest_service_revenue_value']  = $leaderboard['highest_service_revenue_value'];
                                $res['highest_service_revenue_employee']  = $leaderboard['highest_service_revenue_employee'];
                                $res['highest_service_revenue_employee_image']  = $leaderboard['highest_service_revenue_employee_image'];
                                $res['highest_product_revenue_value']  = $leaderboard['highest_product_revenue_value'];
                                $res['highest_product_revenue_employee']  = $leaderboard['highest_product_revenue_employee'];
                                $res['highest_product_revenue_employee_image']  = $leaderboard['highest_product_revenue_employee_image'];
                                $res['salon_id'] = $this->salon_id;

                                if($year==1){
                                    $res['start_date'] = $this->lastYearStartDate;
                                    $res['end_date'] = $this->lastYearEndDate;
                                    $res['report_type'] = 'lastYearReport';
                                } else {
                                    $res['start_date'] = $this->startDate;
                                    $res['end_date'] = $this->endDate;
                                    $res['report_type'] = 'currentYearReport';
                                }

                                
                                $res['dayRangeType'] = $dayRangeType;
                                
                                $res['staff_id'] = $staffMembers["staff_id"];     
                                $res['RPCT'] = number_format($res['RPCT'], 2, '.', '');
                                $res['estimated_sales'] = number_format($res['estimated_sales'], 2, '.', '');
                                //pa($res,'res',false);
                               
                                if(!empty($res)){
                                    

                                    if($year==1){
                                        $reportsWhere = array('salon_id' => $salonsData["salon_id"], 'staff_id' => $staffMembers["staff_id"],'dayRangeType'=>$dayRangeType,'report_type' => 'lastYearReport','start_date' => $this->lastYearStartDate,'end_date' => $this->lastYearEndDate);
                                    } else {
                                        $reportsWhere = array('salon_id' => $salonsData["salon_id"], 'staff_id' => $staffMembers["staff_id"],'dayRangeType'=>$dayRangeType,'report_type' => 'currentYearReport','start_date' => $this->startDate,'end_date' => $this->endDate);
                                    }


                                    $reportsDataForSalon =  $this->DB_ReadOnly->select('*')
                                                                    ->get_where(MILL_REPORT_CALCULATIONS_CRON,$reportsWhere)
                                                                    ->row_array();

                                    //echo $this->DB_ReadOnly->last_query();exit;                          
                                    if($reportsDataForSalon===FALSE){
                                      $errors = $this->DB_ReadOnly->error();
                                      $errors['tablename'] = MILL_REPORT_CALCULATIONS_CRON;
                                      send_mail_database_error($errors);
                                    }
                                    //pa($reportsDataForSalon,'reportsDataForSalon');
                                    if(!empty($reportsDataForSalon))
                                    {  
                                        // update
                                        $diff_array = array_diff_assoc($res,$reportsDataForSalon);
                                        //pa($diff_array,"Diff Array");
                                                                
                                        if(empty($diff_array))
                                        {
                                              pa("No Updates");
                                              continue; //SAME DATA FOUND, SO CONTINUe with the loop
                                        }else{

                                            /*pa($res,"res");
                                            pa($reportsDataForSalon,"reportsDataForSalon");
                                            pa($diff_array,"diff_array");*/
                                            $diff_array["insert_status"] = self::UPDATED;
                                            $diff_array["updatedDate"] = date("Y-m-d H:i:s");
                                            $this->db->where('id', $reportsDataForSalon["id"]);
                                          

                                            if($year==1){
                                                $this->db->where('start_date', $this->lastYearStartDate);
                                                $this->db->where('end_date', $this->lastYearEndDate);
                                                $this->db->where('report_type', 'lastYearReport');
                                            } else {
                                                $this->db->where('start_date', $this->startDate);
                                                $this->db->where('end_date', $this->endDate);
                                                $this->db->where('report_type', 'currentYearReport');
                                            }

                                            $this->db->update(MILL_REPORT_CALCULATIONS_CRON, $diff_array);
                                            pa('Updated');
                                        }

                                    }else{
                                        // insert
                                        $res["insert_status"] = self::INSERTED;;
                                        $res["insertedDate"] = date("Y-m-d H:i:s");
                                        $res["updatedDate"] = date("Y-m-d H:i:s");
                                        $this->db->insert(MILL_REPORT_CALCULATIONS_CRON, $res);
                                        pa('Inserted');

                                    } 

                                } // $res close
                                  
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