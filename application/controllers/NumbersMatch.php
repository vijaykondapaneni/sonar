<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class NumbersMatch extends CI_Controller
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
            case Yearly:
                $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
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
    // services 
    public function Services($salon_id,$start_date,$end_date,$dayRangeType,$type="")
       { 
            /*if(!$this->session->userdata('isUserLoggedIn')){
             redirect(MAIN_SERVER_URL.'users/login/4');
             exit();
            }*/
           /* pa($salon_id,'');
            pa($start_date,'');
            pa($end_date,'');
            pa($dayRangeType,'',true);*/
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
                        
                        // loop run

                        $startDate = $start_date;
                        $endDate = $end_date;

                        $begin = new DateTime($startDate);
                        $end = new DateTime($endDate);
                        $end = $end->modify( '+1 day' );
                        $interval = new DateInterval('P1D');
                        $daterange = new DatePeriod($begin, $interval ,$end);
                        //pa($daterange,'',true);
                        $mainurl = MAIN_SERVER_URL;
                        $salesArray = array();
                        foreach ($daterange as $key => $date) {
                            $month = $date->format("m");
                            $month = ltrim($month, '0');
                            $monthName = $date->format("F");
                            $year = date("Y");
                            $firstDayOfMonth = $date->format("Y-m-d");
                            pa($firstDayOfMonth,'dates');
                            $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                            $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                             
                            $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                            //pa($this->millResponseSessionId,'Session');
                            if($this->millResponseSessionId){
                                $millMethodParams = array('StartDate' => $firstDayOfMonth,'EndDate' => $firstDayOfMonth,'IncludeVoided' => 0);
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

                                $whereConditions = array('account_no' =>$account_no , 'tdatetime >=' =>$firstDayOfMonth, 'tdatetime <=' =>$firstDayOfMonth);

                                $getTotalServiceSales = $this->DashboardOwner_model
                                        ->getTotalServiceSales($whereConditions)
                                        ->row_array();
                                pa($getTotalServiceSales['nprice'],'Db Total');
                                pa($service_total_sales,'SDK Total');
                                
                                if($service_total_sales!=$getTotalServiceSales['nprice']){
                                    pa("Data Not Matched");

                                // data delete
                                    $this->db->where('account_no',$account_no);
                                    $this->db->where('tdatetime',$firstDayOfMonth);
                                    $this->db->delete(MILL_SERVICE_SALES); 
                                   // pa($this->db->last_query());

                                // data insert
                                   
                                    //$mainurl = 'localhost/salon-reports/';
                                    $loop = array();
                                    $loop[] = 'wsimport_all_salon_services_data/GetServiceSales';
                                    $i=1;
                                    foreach ($loop as $key => $value) {
                                       if($account_no!=''){
                                         $olddatesranges = $firstDayOfMonth.'/'.$firstDayOfMonth;
                                         $url_oneday = $mainurl.$value.'/'.$olddatesranges.'/'.$account_no;
                                       // pa($url_oneday,'URL');
                                        $op = $this->Common_model->insertDataByUsingCUrl($url_oneday);
                                       }  
                                    }



                                } // if close
                                else{
                                    pa('Data Matched');
                                }             

                                // pa($service_total_sales,$firstDayOfMonth);   
                                }    
                            }
                         $newloop = array();
                         
                        if($type=='Yearly') {
                            $newloop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Previousmonths';
                            $newloop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Yearly';
                        }elseif($type=='Monthly') {
                            $newloop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Monthly';
                        }else{
                            $newloop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/'.$dayRangeType;
                            $newloop[] = 'StaffReportsForDashboard/setStaffDashboard/'.$dayRangeType;
                        }

                        



                         $j=1;
                         foreach ($newloop as $key => $value) {
                            $url = $mainurl.$value.'/'.$salon_id;
                            //pa($url,$j);
                            $op = $this->Common_model->insertDataByUsingCUrl($url);
                            $j++;
                         }   

                           
                        }
                        
                        pa($salon_id,'',true);    
                       
                        
                    }else{
                        echo "Salon Details are not sufficient";
                    }
                }
               
            }else{
                echo "No SalonId's In Server";
            }
       } 
    // products   
    public function Products($salon_id,$start_date,$end_date,$dayRangeType,$type="")
       { 
            /*if(!$this->session->userdata('isUserLoggedIn')){
             redirect(MAIN_SERVER_URL.'users/login/4');
             exit();
            }*/
           /* pa($salon_id,'');
            pa($start_date,'');
            pa($end_date,'');
            pa($dayRangeType,'',true);*/
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
                        
                        // loop run

                        $startDate = $start_date;
                        $endDate = $end_date;

                        $begin = new DateTime($startDate);
                        $end = new DateTime($endDate);
                        $end = $end->modify( '+1 day' );
                        $interval = new DateInterval('P1D');
                        $daterange = new DatePeriod($begin, $interval ,$end);
                        //pa($daterange,'',true);
                        $mainurl = MAIN_SERVER_URL;
                        $salesArray = array();
                        foreach ($daterange as $key => $date) {
                            $month = $date->format("m");
                            $month = ltrim($month, '0');
                            $monthName = $date->format("F");
                            $year = date("Y");
                            $firstDayOfMonth = $date->format("Y-m-d");
                            pa($firstDayOfMonth,'dates');
                            $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                            $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                             
                            $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                            //pa($this->millResponseSessionId,'Session');
                            if($this->millResponseSessionId){
                                $millMethodParams = array('StartDate' => $firstDayOfMonth,'EndDate' => $firstDayOfMonth,'IncludeVoided' => 0);
                                $millResponseXml = $this->nusoap_library->getMillMethodCall('GetProductTotalSales',$millMethodParams);
                                //pa($millResponseXml['ProductTotalSales'],'',true);
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

                                $whereConditions = array('account_no' =>$account_no , 'tdatetime >=' =>$firstDayOfMonth, 'tdatetime <=' =>$firstDayOfMonth);

                                $getTotalProductSales = $this->DashboardOwner_model
                                        ->getTotalProductSales($whereConditions)
                                        ->row_array();
                                pa($getTotalProductSales['nprice'],'Db Total');
                                pa($product_total_sales,'SDK Total');
                                
                                if((int)$product_total_sales!=(int)$getTotalProductSales['nprice']){
                                    pa("Data Not Matched");

                                // data delete
                                    $this->db->where('account_no',$account_no);
                                    $this->db->where('tdatetime',$firstDayOfMonth);
                                    $this->db->delete(MILL_PRODUCT_SALES); 
                                   // pa($this->db->last_query());

                                // data insert
                                   
                                    //$mainurl = 'localhost/salon-reports/';
                                    $loop = array();
                                    $loop[] = 'wsimport_all_salon_product_data/getProductSales';
                                    $i=1;
                                    foreach ($loop as $key => $value) {
                                       if($account_no!=''){
                                         $olddatesranges = $firstDayOfMonth.'/'.$firstDayOfMonth;
                                         $url_oneday = $mainurl.$value.'/'.$olddatesranges.'/'.$account_no;
                                       // pa($url_oneday,'URL');
                                        $op = $this->Common_model->insertDataByUsingCUrl($url_oneday);
                                       }  
                                    }



                                } // if close
                                else{
                                    pa('Data Matched');
                                }             

                                // pa($service_total_sales,$firstDayOfMonth);   
                                }    
                            }
                        $newloop = array();
                         
                        if($type=='Yearly') {
                            $newloop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Previousmonths';
                            $newloop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Yearly';
                        }elseif($type=='Monthly') {
                            $newloop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Monthly';
                        }else{
                            $newloop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/'.$dayRangeType;
                            $newloop[] = 'StaffReportsForDashboard/setStaffDashboard/'.$dayRangeType;
                        }


                         $j=1;
                         foreach ($newloop as $key => $value) {
                            $url = $mainurl.$value.'/'.$salon_id;
                            //pa($url,$j);
                            $op = $this->Common_model->insertDataByUsingCUrl($url);
                            $j++;
                         }   

                           
                        }
                        
                        pa($salon_id,'',true);    
                       
                        
                    }else{
                        echo "Salon Details are not sufficient";
                    }
                }
               
            }else{
                echo "No SalonId's In Server";
            }
       }   
    
    // Gift cards
    public function GiftCards($salon_id,$start_date,$end_date,$dayRangeType,$type="")
       { 
            /*if(!$this->session->userdata('isUserLoggedIn')){
             redirect(MAIN_SERVER_URL.'users/login/4');
             exit();
            }*/
            //pa($type,'',true);
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
                    if(!empty($salonDetails)&& isset($salonDetails['salon_info']["millennium_enabled"]) && isset($salonDetails['salon_info']["service_retail_reports_enabled"]) && $salonDetails['salon_info']["millennium_enabled"]=="Yes" && $salonDetails['salon_info']["service_retail_reports_enabled"]=="Yes")
                    {
                        $startDate = $start_date;
                        $endDate = $end_date;
                        $begin = new DateTime($startDate);
                        $end = new DateTime($endDate);
                        $end = $end->modify( '+1 day' );
                        $interval = new DateInterval('P1D');
                        $daterange = new DatePeriod($begin, $interval ,$end);
                        //pa($daterange,'',true);
                        $mainurl = MAIN_SERVER_URL;
                        $salesArray = array();
                        foreach ($daterange as $key => $date) {
                            $month = $date->format("m");
                            $month = ltrim($month, '0');
                            $monthName = $date->format("F");
                            $year = date("Y");
                            $firstDayOfMonth = $date->format("Y-m-d");
                            pa($firstDayOfMonth,'dates');
                            $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                            $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                             
                            $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                            if($this->millResponseSessionId){
                                $millMethodParams = array('StartDate' => $firstDayOfMonth,'EndDate' => $firstDayOfMonth,'IncludeVoided' => 0);
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

                                $whereConditions = array('account_no' =>$account_no , 'tdatetime >=' =>$firstDayOfMonth, 'tdatetime <=' =>$firstDayOfMonth);

                                $getTotalGiftCardSales = $this->DashboardOwner_model
                                        ->getTotalGiftCardSales($whereConditions)
                                        ->row_array();
                                pa($getTotalGiftCardSales['nprice'],'Db Total');
                                pa($GC_total_Sales,'SDK Total');
                                if((int)$GC_total_Sales!=(int)$getTotalGiftCardSales['nprice']){
                                    pa("Data Not Matched");
                                    // data delete
                                    $this->db->where('account_no',$account_no);
                                    $this->db->where('tdatetime',$firstDayOfMonth);
                                    $this->db->delete(MILL_GIFT_CARD_SALES_WITH_BALANCE);
                                    $loop = array();
                                    $loop[] = 'wsimport_all_salon_gift_card_data/GetGiftCertificatesSales';
                                    $i=1;
                                    foreach ($loop as $key => $value) {
                                       if($account_no!=''){
                                         $olddatesranges = $firstDayOfMonth.'/'.$firstDayOfMonth;
                                         $url_oneday = $mainurl.$value.'/'.$olddatesranges.'/'.$account_no;
                                       // pa($url_oneday,'URL');
                                        $op = $this->Common_model->insertDataByUsingCUrl($url_oneday);
                                       }  
                                    }
                                 }else{
                                    pa("Data Matched");
                                 }

                            }else{
                                pa('Session Not Set');
                            }
                        }

                        $newloop = array();
                        
                        if($type=='Yearly') {
                            $newloop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Previousmonths';
                            $newloop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Yearly';
                        }elseif($type=='Monthly') {
                            $newloop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Monthly';
                        }else{
                            $newloop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/'.$dayRangeType;
                            $newloop[] = 'StaffReportsForDashboard/setStaffDashboard/'.$dayRangeType;
                        }
                        
                        $j=1;
                        foreach ($newloop as $key => $value) {
                            $url = $mainurl.$value.'/'.$salon_id;
                            pa($url,$j);
                            $op = $this->Common_model->insertDataByUsingCUrl($url);
                            $j++;
                        }     

                        
                    }else{
                        echo "Salon Details are not sufficient";
                    }
                }
               
            }else{
                echo "No SalonId's In Server";
            }
       }   



 }       