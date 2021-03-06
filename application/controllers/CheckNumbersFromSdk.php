<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class CheckNumbersFromSdk extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR CheckNumbersFromSdk
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
        $this->load->model('GraphsOwner_model');
        $this->load->model('OwnerWebServices_model');
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
                $this->Range = 'Today';
                $start_date  = date("m/d/Y", strtotime($this->startDate)); 
                $end_date  = date("m/d/Y", strtotime($this->endDate));
                $this->dateRange = $start_date . " to " . $end_date;
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
                    $this->Range = 'Last Week';
                    $start_date  = date("m/d/Y", strtotime($this->startDate)); 
                    $end_date  = date("m/d/Y", strtotime($this->endDate));
                    $this->dateRange = $start_date . " to " . $end_date;   
            break;
            case LASTMONTH:
                $this->startDate = getDateFn(strtotime("first day of last month"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->Range = 'Last Month';
                $start_date  = date("m/d/Y", strtotime($this->startDate)); 
                $end_date  = date("m/d/Y", strtotime($this->endDate));
                $this->dateRange = $start_date . " to " . $end_date;
                    
            break;
            case "Monthly":
                $this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $this->currentDate;
            break;
            case LAST90DAYS:
                $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                $this->endDate = getDateFn(strtotime("last day of last month"));
                $this->Range = 'Last 90 Days';
                $start_date  = date("m/d/Y", strtotime($this->startDate)); 
                $end_date  = date("m/d/Y", strtotime($this->endDate));
                $this->dateRange = $start_date . " to " . $end_date;
                   
            break;
            case "Yearly":
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                //$this->startDate = getDateFn(strtotime("first day of this month"));
                $this->endDate = $currentDate;
            break;         
            default:
                $this->startDate =  $this->currentDate;
                $this->endDate   =  $this->currentDate;
           break;
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
    function getCheckNumbersFromSdk($dayRangeType="today",$salon_id="")
        { 
            if(!$this->session->userdata('isUserLoggedIn')){
             redirect(MAIN_SERVER_URL.'users/login/4');
             exit();
            }
            //pa('test','',true);
            $this->currentDate = getDateFn();
            $getAllSalons = $this->Common_model->getAllSalons($salon_id);
            //pa($getAllSalons,'',true);
            if(isset($getAllSalons["mill_salons"]) && !empty($getAllSalons["mill_salons"]))
            {
                $all_results = array();
                foreach($getAllSalons["mill_salons"] as $salonsData)
                {
                    //pa('',"Reports Owner Dashboard--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                    $account_no = $salonsData['salon_account_id'];
                    $salon_id =   $this->salon_id = $salonsData['salon_id'];
                    $this->salonDetails = $salonDetails = $this->Common_model->getSalonInfoBy($this->salon_id);
                    $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no)->row_array();
                    //pa($getConfigDetails);
                    $this->salonMillIp  =  $getConfigDetails['mill_ip_address'];
                    $this->salonMillGuid = $getConfigDetails['mill_guid'];
                    $this->salonMillUsername = $getConfigDetails['mill_username'];
                    $this->salonMillPassword = $getConfigDetails['mill_password'];
                    $this->salonMillSdkUrl = $getConfigDetails['mill_url'];
                    //pa($salonDetails);

                    $all_results1 = array();  
                    if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"]) && isset($salonDetails['salon_info']["service_retail_reports_enabled"]) && $salonDetails['salon_info']["millennium_enabled"]=="Yes" && $salonDetails['salon_info']["service_retail_reports_enabled"]=="Yes"){
                        // sdk status check
                        $this->db->where('salon_id',$salon_id);
                        $this->db->order_by('id','desc');
                        $get_session_status = $this->db->get('mill_all_salons_sdk_reports_server')->row_array();
                        //pa($this->db->last_query());
                        //pa($get_session_status,'',false);
                        $this->__getStartEndDate($dayRangeType);
                        $sdk_status = "error";
                        $all_results1['status'] = $get_session_status['session_status'];
                        $all_results1['salon_name'] = $salonDetails['salon_info']['salon_name'];
                        $all_results1['salon_id'] = $salonDetails['salon_info']['salon_id'];
                        $all_results1['account_no'] = $account_no;
                        $whereCondition = array('salon_id' => $salon_id,'report_type' => 'currentYearReport','dayRangeType' => $dayRangeType,'start_date' => $this->startDate,'end_date' => $this->endDate);
                        $res1 = $this->OwnerWebServices_model
                        ->getOwnerReports($whereCondition)
                        ->row_array();
                       //pa($this->db->last_query()); 
                       //pa($res1,'res1',true); 
                       $all_results1['service_total_db'] = $res1['service_revenue']; 
                       $all_results1['retail_total_db'] = $res1['total_retail_price']; 
                       $all_results1['giftcard_total_db'] = $res1['gift_cards']; 

                       $all_results1['service_total'] = ''; 
                       $all_results1['retail_total'] = ''; 
                       $all_results1['giftcard_total'] = ''; 

                       
                        if($get_session_status['session_status']==0){
                           $sdk_status = "Working";
                           
                           // service total
                           

                           // sdk check
                           $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                            $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                             
                            $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                            //pa($this->millResponseSessionId,'Session');
                            if($this->millResponseSessionId){
                                $millMethodParams = array('StartDate' => $this->startDate,'EndDate' => $this->endDate,'IncludeVoided' => 0);
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetServiceTotalSales',$millMethodParams);
                                if(isset($millResponseXml['ServiceTotalSales'])){
                                 $ServiceTotalSales = $millResponseXml['ServiceTotalSales'];
                                    if(is_array($ServiceTotalSales)){
                                        if(isset($ServiceTotalSales[1]['ntotal'])){
                                            $service_total_sales = round($ServiceTotalSales[1]['ntotal'],2);
                                            if(isset($ServiceTotalSales[0]['ntotal'])){
                                                if($ServiceTotalSales[0]['ntotal'] > 0){
                                                    $service_total_sales = round($ServiceTotalSales[1]['ntotal'] - $ServiceTotalSales[0]['ntotal'],2);
                                                }else{
                                                     $service_total_sales = round($ServiceTotalSales[1]['ntotal'] + $ServiceTotalSales[0]['ntotal'],2);
                                                }
                                            }
                                        }else{
                                            $service_total_sales = 0;
                                        }
                                        
                                    }else{
                                            $service_total_sales = round($ServiceTotalSales,2);
                                    }
                                }else{
                                        $service_total_sales = 0;
                                }
                                $all_results1['service_total'] = $service_total_sales; 
                                // product sales
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetProductTotalSales',$millMethodParams);

                                if(isset($millResponseXml['ProductTotalSales'])){
                                $ProductTotalSales = $millResponseXml['ProductTotalSales'];

                                    if(is_array($ProductTotalSales)){
                                        if(isset($ProductTotalSales[1]['ntotal'])){
                                             $product_total_sales = round($ProductTotalSales[1]['ntotal'],2);
                                            if(isset($ProductTotalSales[0]['ntotal'])){
                                                if($ProductTotalSales[0]['ntotal'] > 0){
                                                    $product_total_sales = round($ProductTotalSales[1]['ntotal'] - $ProductTotalSales[0]['ntotal'],2);
                                                }else{
                                                    $product_total_sales = round($ProductTotalSales[1]['ntotal'] + $ProductTotalSales[0]['ntotal'],2);
                                                }
                                                
                                            }
                                        }else{
                                            $product_total_sales = 0;
                                        }
                                       
                                    }else{
                                            $product_total_sales = round($ProductTotalSales,2);
                                    }
                                }else{
                                    $product_total_sales = 0;
                                }
                                 $all_results1['retail_total'] = $product_total_sales;


                                // gift cards 
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetGiftCertificateSales',$millMethodParams);
                                $GC_total_Sales = 0; 
                                if(isset($millResponseXml['GCSales'])){
                                    if(!isset($millResponseXml['GCSales'][0]))  
                                    {
                                         $tempArr = $millResponseXml['GCSales'];
                                         unset($millResponseXml['GCSales']);
                                         $millResponseXml['GCSales'][0] = $tempArr;
                                    }
                                    $GCSales = $millResponseXml['GCSales'];
            
                                    foreach ($GCSales as $gckey=>$gcresults) {
                                           $GC_total_Sales+=$gcresults['nprice'];
                                    }
                                 
                                }else{
                                    $GC_total_Sales = 0;
                                }

                                 $all_results1['giftcard_total'] = $GC_total_Sales;
                                //pa($all_results1,'',true);
                              }    

                        }else{
                            

                        }
                     $all_results[] = $all_results1;    
                    }else{
                        echo "Salon Details are not sufficient";
                    }
                }
                $data['all_results'] = $all_results;
                $data['column_name'] = array('Salon Id','Account No','Name','SDK Service  Sales','Database Service Sales','SDK Retail Sales','Database Retail Sales','SDK Gift Card Sales','Database Gift Card Sales','SDK Status');
                $data['table_heading'] = $this->Range;
                $data['date_range'] = $this->dateRange;
                $data['startDate'] = $this->startDate;
                $data['endDate'] = $this->endDate;
                $data['dayRangeType'] = $this->dayRangeType;

                //pa($data,'',true);

                $this->load->view('viewtable',$data);
            }else{
                echo "No SalonId's In Server";
            }
            
       }

    function getInternalGraphs($salon_id="")
        { 
            if(!$this->session->userdata('isUserLoggedIn')){
             redirect(MAIN_SERVER_URL.'users/login/4');
             exit();
            }
            $this->dayRangeType = $dayRangeType = 'Yearly';
            //pa('test','',true);
            $this->currentDate = getDateFn();
            $getAllSalons = $this->Common_model->getAllSalons($salon_id);
            //pa($getAllSalons,'',true);
            if(isset($getAllSalons["mill_salons"]) && !empty($getAllSalons["mill_salons"]))
            {
                $all_results = array();
                foreach($getAllSalons["mill_salons"] as $salonsData)
                {
                    //pa('',"Reports Owner Dashboard--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                    $account_no = $salonsData['salon_account_id'];
                    $salon_id =   $this->salon_id = $salonsData['salon_id'];
                    $this->salonDetails = $salonDetails = $this->Common_model->getSalonInfoBy($this->salon_id);
                    $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no)->row_array();
                    //pa($getConfigDetails);
                    $this->salonMillIp  =  $getConfigDetails['mill_ip_address'];
                    $this->salonMillGuid = $getConfigDetails['mill_guid'];
                    $this->salonMillUsername = $getConfigDetails['mill_username'];
                    $this->salonMillPassword = $getConfigDetails['mill_password'];
                    $this->salonMillSdkUrl = $getConfigDetails['mill_url'];
                    //pa($salonDetails);

                    $all_results1 = array();  
                    if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"]) && isset($salonDetails['salon_info']["service_retail_reports_enabled"]) && $salonDetails['salon_info']["millennium_enabled"]=="Yes" && $salonDetails['salon_info']["service_retail_reports_enabled"]=="Yes"){
                        // sdk status check
                        $this->db->where('salon_id',$salon_id);
                        $this->db->order_by('id','desc');
                        $get_session_status = $this->db->get('mill_all_salons_sdk_reports_server')->row_array();
                        //pa($this->db->last_query());
                        //pa($get_session_status,'',false);
                        $this->__getStartEndDate($dayRangeType);
                        $sdk_status = "error";
                      
                        $begin = new DateTime($this->startDate);
                        $end = new DateTime($this->endDate);
                        $interval = new DateInterval('P1M');
                        $daterange = new DatePeriod($begin, $interval ,$end);
                        $salesArray = array();
                        $databasevalues = array();
                    foreach ($daterange as $key => $date) {
                        $month = $date->format("m");
                        $month = ltrim($month, '0');
                        $monthName = $date->format("F");
                        $year = date("Y");
                        $firstDayOfMonth = $date->format("Y-m-d");
                        $lastdayOfMonth = date('Y-m-t',strtotime($date->format("Y-m-d")));
                        $last_year_start_date = strtotime($firstDayOfMonth);
                        $firstDayOfMonth = date('Y-m-d', $last_year_start_date);
                        $last_year_end_date = strtotime($lastdayOfMonth);
                        $lastdayOfMonth = date('Y-m-d', $last_year_end_date);
                        $last_year_firstDayOfMonth = getDateFn(strtotime($firstDayOfMonth." -1 year"));

                        $last_year_lastdayOfMonth = getDateFn(strtotime($lastdayOfMonth." -1 year"));

                        //pa($this->endDate);
                        if(strtotime($this->endDate) < strtotime($lastdayOfMonth))
                        {
                            $lastdayOfMonth = $this->endDate;
                        }

                        
                        
                        //pa($firstDayOfMonth.'-'.$lastdayOfMonth,'currentyear');
                        //pa($last_year_firstDayOfMonth.'-'.$last_year_lastdayOfMonth,'lastyear');


                        // services
                        $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'start_date' => $firstDayOfMonth,'end_date' => $lastdayOfMonth);
                        $res1 = $this->GraphsOwner_model
                        ->compareServiceOwnerReportsData($whereCondition)
                        ->row_array();

                        if(!empty($res1)){
                            $results['service_current_value'] = $res1['current_value'];
                            $results['service_last_year_value'] = $res1['last_year_value'];
                        }else{
                            $results['service_current_value'] = 0;
                            $results['service_last_year_value'] = 0;
                        }

                        // products
                        $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'start_date' => $firstDayOfMonth,'end_date' => $lastdayOfMonth);
                        $res1 = $this->GraphsOwner_model
                        ->compareRetailOwnerReportsData($whereCondition)
                        ->row_array();

                        if(!empty($res1)){
                            $results['retail_current_value'] = $res1['current_value'];
                            $results['retail_last_year_value'] = $res1['last_year_value'];
                        }else{
                            $results['retail_current_value'] = 0;
                            $results['retail_last_year_value'] = 0;
                        }

                        // giftcards
                        $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'start_date' => $firstDayOfMonth,'end_date' => $lastdayOfMonth);
                        $res1 = $this->GraphsOwner_model
                        ->compareGiftCardOwnerReportsData($whereCondition)
                        ->row_array();


                        if(!empty($res1)){
                            $results['gc_current_value'] = $res1['current_value'];
                            $results['gc_last_year_value'] = $res1['last_year_value'];
                        }else{
                            $results['gc_current_value'] = 0;
                            $results['gc_last_year_value'] = 0;
                        }

                        //pa($results,'',true);


                        $results['sdk_service_current_value'] = 0;
                        $results['sdk_service_last_year_value'] = 0;
                        $results['sdk_retail_current_value'] = 0;
                        $results['sdk_retail_last_year_value'] = 0;
                        $results['sdk_gc_current_value'] = 0;
                        $results['sdk_gc_last_year_value'] = 0;
                        
                        // check sdk
                          $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                            $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                             
                            $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                            //pa($this->millResponseSessionId,'Session');
                            if($this->millResponseSessionId){
                                $millMethodParams = array('StartDate' => $firstDayOfMonth,'EndDate' => $lastdayOfMonth,'IncludeVoided' => 0);
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetServiceTotalSales',$millMethodParams);
                                if(isset($millResponseXml['ServiceTotalSales'])){
                                 $ServiceTotalSales = $millResponseXml['ServiceTotalSales'];
                                    if(is_array($ServiceTotalSales)){
                                        if(isset($ServiceTotalSales[1]['ntotal'])){
                                            $service_total_sales = round($ServiceTotalSales[1]['ntotal'],2);
                                            if(isset($ServiceTotalSales[0]['ntotal'])){
                                                if($ServiceTotalSales[0]['ntotal'] > 0){
                                                    $service_total_sales = round($ServiceTotalSales[1]['ntotal'] - $ServiceTotalSales[0]['ntotal'],2);
                                                }else{
                                                     $service_total_sales = round($ServiceTotalSales[1]['ntotal'] + $ServiceTotalSales[0]['ntotal'],2);
                                                }
                                            }
                                        }else{
                                            $service_total_sales = 0;
                                        }
                                        
                                    }else{
                                            $service_total_sales = round($ServiceTotalSales,2);
                                    }
                                }else{
                                        $service_total_sales = 0;
                                }

                                $results['sdk_service_current_value'] = $service_total_sales;
                                // service last year sdk
                                $millMethodParams_lastyear = array('StartDate' => $last_year_firstDayOfMonth,'EndDate' => $last_year_lastdayOfMonth,'IncludeVoided' => 0);
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetServiceTotalSales',$millMethodParams_lastyear);
                                if(isset($millResponseXml['ServiceTotalSales'])){
                                 $ServiceTotalSales = $millResponseXml['ServiceTotalSales'];
                                    if(is_array($ServiceTotalSales)){
                                        if(isset($ServiceTotalSales[1]['ntotal'])){
                                            $service_total_sales = round($ServiceTotalSales[1]['ntotal'],2);
                                            if(isset($ServiceTotalSales[0]['ntotal'])){
                                                if($ServiceTotalSales[0]['ntotal'] > 0){
                                                    $service_total_sales = round($ServiceTotalSales[1]['ntotal'] - $ServiceTotalSales[0]['ntotal'],2);
                                                }else{
                                                     $service_total_sales = round($ServiceTotalSales[1]['ntotal'] + $ServiceTotalSales[0]['ntotal'],2);
                                                }
                                            }
                                        }else{
                                            $service_total_sales = 0;
                                        }
                                        
                                    }else{
                                            $service_total_sales = round($ServiceTotalSales,2);
                                    }
                                }else{
                                        $service_total_sales = 0;
                                }
                                $results['sdk_service_last_year_value'] = $service_total_sales;

                                // products current year

                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetProductTotalSales',$millMethodParams);

                                if(isset($millResponseXml['ProductTotalSales'])){
                                $ProductTotalSales = $millResponseXml['ProductTotalSales'];

                                    if(is_array($ProductTotalSales)){
                                        if(isset($ProductTotalSales[1]['ntotal'])){
                                             $product_total_sales = round($ProductTotalSales[1]['ntotal'],2);
                                            if(isset($ProductTotalSales[0]['ntotal'])){
                                                if($ProductTotalSales[0]['ntotal'] > 0){
                                                    $product_total_sales = round($ProductTotalSales[1]['ntotal'] - $ProductTotalSales[0]['ntotal'],2);
                                                }else{
                                                    $product_total_sales = round($ProductTotalSales[1]['ntotal'] + $ProductTotalSales[0]['ntotal'],2);
                                                }
                                                
                                            }
                                        }else{
                                            $product_total_sales = 0;
                                        }
                                       
                                    }else{
                                            $product_total_sales = round($ProductTotalSales,2);
                                    }
                                }else{
                                    $product_total_sales = 0;
                                }
                                 $results['sdk_retail_current_value'] = $product_total_sales;

                                 // product last year
                                 $millResponseXml = $this->nusoap_library->getMillMethodCall('GetProductTotalSales',$millMethodParams_lastyear);

                                if(isset($millResponseXml['ProductTotalSales'])){
                                $ProductTotalSales = $millResponseXml['ProductTotalSales'];

                                    if(is_array($ProductTotalSales)){
                                        if(isset($ProductTotalSales[1]['ntotal'])){
                                             $product_total_sales = round($ProductTotalSales[1]['ntotal'],2);
                                            if(isset($ProductTotalSales[0]['ntotal'])){
                                                if($ProductTotalSales[0]['ntotal'] > 0){
                                                    $product_total_sales = round($ProductTotalSales[1]['ntotal'] - $ProductTotalSales[0]['ntotal'],2);
                                                }else{
                                                    $product_total_sales = round($ProductTotalSales[1]['ntotal'] + $ProductTotalSales[0]['ntotal'],2);
                                                }
                                                
                                            }
                                        }else{
                                            $product_total_sales = 0;
                                        }
                                       
                                    }else{
                                            $product_total_sales = round($ProductTotalSales,2);
                                    }
                                }else{
                                    $product_total_sales = 0;
                                }
                                 $results['sdk_retail_last_year_value'] = $product_total_sales;

                                // gift cards current year
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetGiftCertificateSales',$millMethodParams);
                                $GC_total_Sales = 0; 
                                if(isset($millResponseXml['GCSales'])){
                                    if(!isset($millResponseXml['GCSales'][0]))  
                                    {
                                         $tempArr = $millResponseXml['GCSales'];
                                         unset($millResponseXml['GCSales']);
                                         $millResponseXml['GCSales'][0] = $tempArr;
                                    }
                                    $GCSales = $millResponseXml['GCSales'];
            
                                    foreach ($GCSales as $gckey=>$gcresults) {
                                           $GC_total_Sales+=$gcresults['nprice'];
                                    }
                                 
                                }else{
                                    $GC_total_Sales = 0;
                                }

                                 $results['sdk_gc_current_value'] = $GC_total_Sales;

                                 // gift cards last year
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetGiftCertificateSales',$millMethodParams_lastyear);
                                $GC_total_Sales = 0; 
                                if(isset($millResponseXml['GCSales'])){
                                    if(!isset($millResponseXml['GCSales'][0]))  
                                    {
                                         $tempArr = $millResponseXml['GCSales'];
                                         unset($millResponseXml['GCSales']);
                                         $millResponseXml['GCSales'][0] = $tempArr;
                                    }
                                    $GCSales = $millResponseXml['GCSales'];
            
                                    foreach ($GCSales as $gckey=>$gcresults) {
                                           $GC_total_Sales+=$gcresults['nprice'];
                                    }
                                 
                                }else{
                                    $GC_total_Sales = 0;
                                }

                                 $results['sdk_gc_last_year_value'] = $GC_total_Sales;

                            }

                        $results['monthname'] = $monthName;
                        $results['start_date'] = $firstDayOfMonth;
                        $results['end_date'] = $lastdayOfMonth;
                        $results['lastyear_start_date'] = $last_year_firstDayOfMonth;
                        $results['lastyear_end_date'] = $last_year_lastdayOfMonth;
                        $results['status'] = $get_session_status['session_status'];
                        $results['salon_id'] = $salon_id;
                        $allvalues[] = $results;
                    }
                  }
                     $all_results = $allvalues;    
                  
                }
                $data['all_results'] = $all_results;
                $data['startDate'] = $this->startDate;
                $data['endDate'] = $this->endDate;
                $data['salon_id'] = $salon_id;
                $data['salon_name'] = $salonDetails['salon_info']['salon_name'];
                $data['dayRangeType'] = $this->dayRangeType;

                //pa($data,'',true);

                $this->load->view('yearlyviewtable',$data);
            }else{
                echo "No SalonId's In Server";
            }
            
       } 
    function getInternalGraphsWeekly($salon_id="")
        { 
            if(!$this->session->userdata('isUserLoggedIn')){
             redirect(MAIN_SERVER_URL.'users/login/4');
             exit();
            }
            //pa("test",'',true);
            $this->dayRangeType = $dayRangeType = 'lastweek';
            //pa('test','',true);
            $this->currentDate = getDateFn();
            $getAllSalons = $this->Common_model->getAllSalons($salon_id);
            //pa($getAllSalons,'',true);
            if(isset($getAllSalons["mill_salons"]) && !empty($getAllSalons["mill_salons"]))
            {
                $all_results = array();
                foreach($getAllSalons["mill_salons"] as $salonsData)
                {
                    //pa('',"Reports Owner Dashboard--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                    $account_no = $salonsData['salon_account_id'];
                    $salon_id =   $this->salon_id = $salonsData['salon_id'];
                    $this->salonDetails = $salonDetails = $this->Common_model->getSalonInfoBy($this->salon_id);
                    $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no)->row_array();
                    //pa($getConfigDetails);
                    $this->salonMillIp  =  $getConfigDetails['mill_ip_address'];
                    $this->salonMillGuid = $getConfigDetails['mill_guid'];
                    $this->salonMillUsername = $getConfigDetails['mill_username'];
                    $this->salonMillPassword = $getConfigDetails['mill_password'];
                    $this->salonMillSdkUrl = $getConfigDetails['mill_url'];
                    //pa($salonDetails);

                    $all_results1 = array();  
                    if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"]) && isset($salonDetails['salon_info']["service_retail_reports_enabled"]) && $salonDetails['salon_info']["millennium_enabled"]=="Yes" && $salonDetails['salon_info']["service_retail_reports_enabled"]=="Yes"){
                        // sdk status check
                        $this->db->where('salon_id',$salon_id);
                        $this->db->order_by('id','desc');
                        $get_session_status = $this->db->get('mill_all_salons_sdk_reports_server')->row_array();
                        //pa($this->db->last_query());
                        //pa($get_session_status,'',false);
                        $this->__getStartEndDate($dayRangeType);
                        $sdk_status = "error";
                        $startDayOfTheWeek = $salonDetails['salon_info']["salon_start_day_of_week"];

                        //pa($startDayOfTheWeek,'startDayOfTheWeek');
                        if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek)){
                            $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                        }
                        else{
                            $fourWeeksArr = getLast4WeekRanges(date('Y'));
                        }

                        //pa($fourWeeksArr,'',false);
                        $week_number=0;
                        $week_number_db=1;    
                      
                    foreach ($fourWeeksArr as $dates) {
                        $dayRangeType = "Monthly";
                        $firstDayOfMonth = $dates['start_date'];
                        $lastdayOfMonth = $dates['end_date'];
                        $current_week = $dates['current_week'];
                        //pa($firstDayOfMonth.' To '.$lastdayOfMonth,'Start and End DayOfWeek',false);

                        if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek))
                        {   
                            $fourWeeksArrLastYear = getLast4BusinessWeeksRanges($startDayOfTheWeek,date("Y",strtotime("-1 year")));
                        }
                        else
                        {
                           $fourWeeksArrLastYear = getLast4WeekRanges(date("Y",strtotime("-1 year")));
                        }

                        $last_year_firstDayOfMonth = $fourWeeksArrLastYear[$week_number]['start_date'];
                        $last_year_lastdayOfMonth = $fourWeeksArrLastYear[$week_number]['end_date'];

                        //pa($last_year_firstDayOfMonth.' To '.$last_year_lastdayOfMonth,'Start and End DayOfWeek',true);

                        //pa($firstDayOfMonth.'-'.$lastdayOfMonth,'currentyear');
                        //pa($last_year_firstDayOfMonth.'-'.$last_year_lastdayOfMonth,'lastyear');

                        // services
                        $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'start_date' => $firstDayOfMonth,'end_date' => $lastdayOfMonth);
                        $res1 = $this->GraphsOwner_model
                        ->compareServiceOwnerReportsData($whereCondition)
                        ->row_array();

                        if(!empty($res1)){
                            $results['service_current_value'] = $res1['current_value'];
                            $results['service_last_year_value'] = $res1['last_year_value'];
                        }else{
                            $results['service_current_value'] = 0;
                            $results['service_last_year_value'] = 0;
                        }

                        // products
                        $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'start_date' => $firstDayOfMonth,'end_date' => $lastdayOfMonth);
                        $res1 = $this->GraphsOwner_model
                        ->compareRetailOwnerReportsData($whereCondition)
                        ->row_array();

                        if(!empty($res1)){
                            $results['retail_current_value'] = $res1['current_value'];
                            $results['retail_last_year_value'] = $res1['last_year_value'];
                        }else{
                            $results['retail_current_value'] = 0;
                            $results['retail_last_year_value'] = 0;
                        }

                        // giftcards
                        $whereCondition = array('salon_id' => $salon_id,'dayRangeType' => $dayRangeType,'start_date' => $firstDayOfMonth,'end_date' => $lastdayOfMonth);
                        $res1 = $this->GraphsOwner_model
                        ->compareGiftCardOwnerReportsData($whereCondition)
                        ->row_array();

                        if(!empty($res1)){
                            $results['gc_current_value'] = $res1['current_value'];
                            $results['gc_last_year_value'] = $res1['last_year_value'];
                        }else{
                            $results['gc_current_value'] = 0;
                            $results['gc_last_year_value'] = 0;
                        }


                        $results['sdk_service_current_value'] = 0;
                        $results['sdk_service_last_year_value'] = 0;
                        $results['sdk_retail_current_value'] = 0;
                        $results['sdk_retail_last_year_value'] = 0;
                        $results['sdk_gc_current_value'] = 0;
                        $results['sdk_gc_last_year_value'] = 0;
                        
                        // check sdk
                          $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                            $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                             
                            $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                            //pa($this->millResponseSessionId,'Session');
                            if($this->millResponseSessionId){
                                $millMethodParams = array('StartDate' => $firstDayOfMonth,'EndDate' => $lastdayOfMonth,'IncludeVoided' => 0);
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetServiceTotalSales',$millMethodParams);
                                if(isset($millResponseXml['ServiceTotalSales'])){
                                 $ServiceTotalSales = $millResponseXml['ServiceTotalSales'];
                                    if(is_array($ServiceTotalSales)){
                                        if(isset($ServiceTotalSales[1]['ntotal'])){
                                            $service_total_sales = round($ServiceTotalSales[1]['ntotal'],2);
                                            if(isset($ServiceTotalSales[0]['ntotal'])){
                                                if($ServiceTotalSales[0]['ntotal'] > 0){
                                                    $service_total_sales = round($ServiceTotalSales[1]['ntotal'] - $ServiceTotalSales[0]['ntotal'],2);
                                                }else{
                                                     $service_total_sales = round($ServiceTotalSales[1]['ntotal'] + $ServiceTotalSales[0]['ntotal'],2);
                                                }
                                            }
                                        }else{
                                            $service_total_sales = 0;
                                        }
                                        
                                    }else{
                                            $service_total_sales = round($ServiceTotalSales,2);
                                    }
                                }else{
                                        $service_total_sales = 0;
                                }

                                $results['sdk_service_current_value'] = $service_total_sales;
                                // service last year sdk
                                $millMethodParams_lastyear = array('StartDate' => $last_year_firstDayOfMonth,'EndDate' => $last_year_lastdayOfMonth,'IncludeVoided' => 0);
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetServiceTotalSales',$millMethodParams_lastyear);
                                if(isset($millResponseXml['ServiceTotalSales'])){
                                 $ServiceTotalSales = $millResponseXml['ServiceTotalSales'];
                                    if(is_array($ServiceTotalSales)){
                                        if(isset($ServiceTotalSales[1]['ntotal'])){
                                            $service_total_sales = round($ServiceTotalSales[1]['ntotal'],2);
                                            if(isset($ServiceTotalSales[0]['ntotal'])){
                                                if($ServiceTotalSales[0]['ntotal'] > 0){
                                                    $service_total_sales = round($ServiceTotalSales[1]['ntotal'] - $ServiceTotalSales[0]['ntotal'],2);
                                                }else{
                                                     $service_total_sales = round($ServiceTotalSales[1]['ntotal'] + $ServiceTotalSales[0]['ntotal'],2);
                                                }
                                            }
                                        }else{
                                            $service_total_sales = 0;
                                        }
                                        
                                    }else{
                                            $service_total_sales = round($ServiceTotalSales,2);
                                    }
                                }else{
                                        $service_total_sales = 0;
                                }
                                $results['sdk_service_last_year_value'] = $service_total_sales;

                                // products current year

                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetProductTotalSales',$millMethodParams);

                                if(isset($millResponseXml['ProductTotalSales'])){
                                $ProductTotalSales = $millResponseXml['ProductTotalSales'];

                                    if(is_array($ProductTotalSales)){
                                        if(isset($ProductTotalSales[1]['ntotal'])){
                                             $product_total_sales = round($ProductTotalSales[1]['ntotal'],2);
                                            if(isset($ProductTotalSales[0]['ntotal'])){
                                                if($ProductTotalSales[0]['ntotal'] > 0){
                                                    $product_total_sales = round($ProductTotalSales[1]['ntotal'] - $ProductTotalSales[0]['ntotal'],2);
                                                }else{
                                                    $product_total_sales = round($ProductTotalSales[1]['ntotal'] + $ProductTotalSales[0]['ntotal'],2);
                                                }
                                                
                                            }
                                        }else{
                                            $product_total_sales = 0;
                                        }
                                       
                                    }else{
                                            $product_total_sales = round($ProductTotalSales,2);
                                    }
                                }else{
                                    $product_total_sales = 0;
                                }
                                 $results['sdk_retail_current_value'] = $product_total_sales;

                                 // product last year
                                 $millResponseXml = $this->nusoap_library->getMillMethodCall('GetProductTotalSales',$millMethodParams_lastyear);

                                if(isset($millResponseXml['ProductTotalSales'])){
                                $ProductTotalSales = $millResponseXml['ProductTotalSales'];

                                    if(is_array($ProductTotalSales)){
                                        if(isset($ProductTotalSales[1]['ntotal'])){
                                             $product_total_sales = round($ProductTotalSales[1]['ntotal'],2);
                                            if(isset($ProductTotalSales[0]['ntotal'])){
                                                if($ProductTotalSales[0]['ntotal'] > 0){
                                                    $product_total_sales = round($ProductTotalSales[1]['ntotal'] - $ProductTotalSales[0]['ntotal'],2);
                                                }else{
                                                    $product_total_sales = round($ProductTotalSales[1]['ntotal'] + $ProductTotalSales[0]['ntotal'],2);
                                                }
                                                
                                            }
                                        }else{
                                            $product_total_sales = 0;
                                        }
                                       
                                    }else{
                                            $product_total_sales = round($ProductTotalSales,2);
                                    }
                                }else{
                                    $product_total_sales = 0;
                                }
                                 $results['sdk_retail_last_year_value'] = $product_total_sales;

                                // gift cards current year
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetGiftCertificateSales',$millMethodParams);
                                $GC_total_Sales = 0; 
                                if(isset($millResponseXml['GCSales'])){
                                    if(!isset($millResponseXml['GCSales'][0]))  
                                    {
                                         $tempArr = $millResponseXml['GCSales'];
                                         unset($millResponseXml['GCSales']);
                                         $millResponseXml['GCSales'][0] = $tempArr;
                                    }
                                    $GCSales = $millResponseXml['GCSales'];
            
                                    foreach ($GCSales as $gckey=>$gcresults) {
                                           $GC_total_Sales+=$gcresults['nprice'];
                                    }
                                 
                                }else{
                                    $GC_total_Sales = 0;
                                }

                                 $results['sdk_gc_current_value'] = $GC_total_Sales;

                                 // gift cards last year
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetGiftCertificateSales',$millMethodParams_lastyear);
                                $GC_total_Sales = 0; 
                                if(isset($millResponseXml['GCSales'])){
                                    if(!isset($millResponseXml['GCSales'][0]))  
                                    {
                                         $tempArr = $millResponseXml['GCSales'];
                                         unset($millResponseXml['GCSales']);
                                         $millResponseXml['GCSales'][0] = $tempArr;
                                    }
                                    $GCSales = $millResponseXml['GCSales'];
            
                                    foreach ($GCSales as $gckey=>$gcresults) {
                                           $GC_total_Sales+=$gcresults['nprice'];
                                    }
                                 
                                }else{
                                    $GC_total_Sales = 0;
                                }

                                 $results['sdk_gc_last_year_value'] = $GC_total_Sales;

                            }

                        $results['monthname'] = "Week".$week_number;
                        $results['start_date'] = $firstDayOfMonth;
                        $results['end_date'] = $lastdayOfMonth;
                        $results['lastyear_start_date'] = $last_year_firstDayOfMonth;
                        $results['lastyear_end_date'] = $last_year_lastdayOfMonth;
                        $results['status'] = $get_session_status['session_status'];
                        $results['salon_id'] = $salon_id;
                        $allvalues[] = $results;
                     $week_number++;
                     $week_number_db++;
                    }
                    
                  }
                     $all_results = $allvalues;    
                  
                }
                //pa($all_results,'',true);
                $data['all_results'] = $all_results;
                $data['startDate'] = $this->startDate;
                $data['endDate'] = $this->endDate;
                $data['salon_id'] = $salon_id;
                $data['salon_name'] = $salonDetails['salon_info']['salon_name'];
                $data['dayRangeType'] = $this->dayRangeType;

                //pa($data,'',true);

                $this->load->view('weeklyviewtable',$data);
            }else{
                echo "No SalonId's In Server";
            }
            
       }        
   
 }       