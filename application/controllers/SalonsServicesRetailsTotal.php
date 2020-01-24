<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class SalonsServicesRetailsTotal extends CI_Controller
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
            case "yearly":
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
    function getSalonsServicesRetailsTotal($dayRangeType="yearly",$high_number="",$salon_id="")
        { 
            
            if($high_number==''){
                $high_number = 2000000;
            }

            $this->currentDate = getDateFn();
            if($salon_id!='')
              {
                $this->DB_ReadOnly->where('salon_id',$salon_id);
              }
              $configDetails = $this->DB_ReadOnly->get('mill_all_sdk_config_details')->result_array();
             //pa($getAllSalons,'',true);
            if(isset($configDetails) && !empty($configDetails))
            {
                $all_results = array();
                foreach($configDetails as $salonsData)
                {
                    $account_no = $salonsData['salon_account_id'];
                    $salon_id =   $this->salon_id = $salonsData['salon_id'];
                    //pa($salonDetails);
                    if(!empty($salonsData)){
                       // pa("Test",'',true);
                        // sdk status check
                        $this->db->where('salon_id',$salon_id);
                        $this->db->order_by('id','desc');
                        $get_session_status = $this->db->get('mill_all_salons_sdk_reports_server')->row_array();
                        //pa($this->db->last_query());
                        //pa($get_session_status,'',false);
                        $this->__getStartEndDate($dayRangeType);
                        $startDate = $this->startDate;
                        $endDate = $this->endDate;

                        $whereConditions = array('account_no' =>$account_no , 'tdatetime >=' =>$startDate, 'tdatetime <=' =>$endDate);

                         $getTotalServiceSales = $this->DashboardOwner_model
                                        ->getTotalServiceSales($whereConditions)
                                        ->row_array();
                         $service_sales = !empty($getTotalServiceSales['nprice']) ? $getTotalServiceSales['nprice'] : 0;               

                        //TO GET TOTAL RETAIL SALES DETAILS
                         $getTotalProductSales = $this->DashboardOwner_model
                                        ->getTotalProductSales($whereConditions)
                                        ->row_array();                
                         $retail_sales = !empty($getTotalProductSales['nprice']) ? $getTotalProductSales['nprice'] : 0; 

                         $total_sales = $service_sales + $retail_sales;
                         
                         if($total_sales>$high_number){
                          $new_data['salon_id'] = $salon_id;
                          $new_data['account_no'] = $account_no;
                          $new_data['salon_name'] = $salonsData['salon_name'];
                          $new_data['service_sales'] = $service_sales;
                          $new_data['retail_sales'] = $retail_sales;
                          $new_data['total_sales'] = $total_sales;
                          $all_results[] = $new_data;    
                         }
                     
                    }else{
                        echo "Salon Details are not sufficient";
                    }
                }
                //pa($all_results,'',true);
                 $data['all_results'] =  $all_results; 
                $this->load->view('salonservicesretailstotal.php',$data);
            }else{
                echo "No SalonId's In Server";
            }
            
       }
  
   
 }       