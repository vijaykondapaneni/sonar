<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class NewSalon extends CI_Controller
{
    
    // Define constant as per value;
    CONST INSERTED = 0;
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Creating New Salon
    **/
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Newsalon_model');
       // $this->load->library("security");
    }   
    
    /**
     * Default index Fn
     */
    public function index(){print phpinfo();}
    
    /**
     * 
     * @param type $dayRangeType
     * @param type $s
     * @param type $e
     */

    public function insertsalon(){
       $this->load->view('newsalon');
    }
    public function addsalon(){

    $data = $this->security->xss_clean($this->input->post());
    unset($data['saveForm']);
    // insert to db
    $insert = $this->Newsalon_model->insertNewSalon($data);
    //pa($this->db->insert_id());
    $salon_id = $data['salon_id'];
    $salon_code = $data['salon_account_id'];
    pa($salon_id,'Salon Id');
    pa($salon_code,'Salon Code');

    pa('Successfully Inserted','Successfully Inserted');
    if($this->db->insert_id()){
    exit;?>
    <meta http-equiv="refresh" content="3;url=http://ec2-34-232-4-81.compute-1.amazonaws.com/index.php/NewSalon/insertsalon" />
    <?php }
   }

   public function editSalon($id){
          $data1 = ['service_types'=> "1,2,3,4,5,6,7,8,9,10",
          'leaderboard_type'=>"1,2,3,4,5",
        'staff_service_types'=> "prebook,rpct,color",
        'staff_leaderboard_type'=> "1,3,4"];
          $this->db->where('salon_id',$id);
          $res = $this->db->update('mill_all_sdk_config_details',$data1);
          echo "Success";
   }

   public function deleteSalonData($id){
            $this->db->where('account_no',$whereconditions['account_no']);
            $this->db->delete(MILL_PRODUCT_SALES);
   }

    function dateRange($first, $last, $step = '+1 day', $format = 'Y-m-d' ) {
                $dates = array();
                $current = strtotime($first);
                $last = strtotime($last);
                while( $current <= $last ) {    
                    //$dates[] = date($format, $current);
                    $twodates['start_date'] = date($format, $current);
                    $current = strtotime($step, $current);
                    $twodates['end_date'] = date($format, $current);
                    $dates[]  = $twodates;
                }
                return $dates;
    }
    /**
    For Calculations
    */
    function setCalculationsNewSalon($salon_id='')
        {
            //$mainurl = 'http://67.43.5.76/~newserver/reports/index.php/';
            $mainurl = MAIN_SERVER_URL;
            //$mainurl = 'localhost/salon-reports/';
            $loop = array();
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/last90days';
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastmonth';
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastweek';
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/today';
            //$loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/last90days/1';
            //$loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastmonth/1';
            //$loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastweek/1';
            //$loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/today/1';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerServiceSalesDataForGraphsLastYear/setOwnerServiceSalesDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerRetailSalesDataForGraphsLastYear/setOwnerRetailSalesDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerGiftCardsSalesDataForGraphsLastYear/setOwnerGiftCardsSalesDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Previousmonths';
           // $loop[] = 'OwnerGiftCardsSalesDataForGraphsLastYear/setOwnerGiftCardsSalesDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Previousmonths';
            
            //$loop[] = 'OwnerNewGuestDataForGraphsLastYear/setOwnerNewGuestDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerRepeatedGuestDataForGraphsLastYear/setOwnerRepeatedGuestDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerRpctDataForGraphsLastYear/setOwnerRpctDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerPrebookDataForGraphsLastYear/setOwnerPrebookDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerColorPercentageDataForGraphsLastYear/setOwnerColorPercentageDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerPercentBookedDataForGraphsLastYear/setOwnerPercentBookedDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerClientServicedDataForGraphsLastYear/setOwnerClientServicedDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Previousmonths';
           // $loop[] = 'OwnerPrebookDataForGraphsLastYear/setOwnerPrebookDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerRUCTDataForGraphsLastYear/setOwnerRUCTDataForGraphsLastYear/Yearly/1';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Previousmonths';
            //$loop[] = 'OwnerRebookPercentageDataForGraphsLastYear/setOwnerRebookPercentageDataForGraphsLastYearYearly/Yearly/1';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/threemonths';
            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_id!=''){
                 $url = $mainurl.$value.'/'.$salon_id;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            } 
        }

    /**
    Function for daily crons for all calculations (dashboard and internal graphs)
    */
    public function setCalculationsNewSalonAllMethods($salon_code=''){

           if($salon_code!=''){
                $salon_code = salonWebappCloudDe($salon_code);
                $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code)->result_array();
                $salon_id = $getConfigDetails[0]['salon_id'];
            }else{
                $salon_id='';
            }
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            //$loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastweek';
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/today';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/today';
            //$loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastweek';            
            //$loop[] = 'StaffReportsForDashboard/setStaffDashboard/Monthly';
            /*$loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastweek';
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/today';
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastweek/1';
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/today/1';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Monthly';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentageRetailToServiceSalesDataForGraphs/setOwnerPercentageRetailToServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Monthly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/seOwnerTotalSalesDataForGraphs/Monthly';
            // staff
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/today';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastweek';            
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/Monthly';
            $loop[] = 'StaffReportsForDashboardBasedOnSkillSet/setStaffDashboard/Monthly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Monthly';*/
            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_id!=''){
                 $url = $mainurl.$value.'/'.$salon_id;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
    }

    /**
    Function for data import employee listing
    */
    public function setEmployeesNewSalon($salon_code=''){
            //$mainurl = 'localhost/salon-reports/';
            //$mainurl = 'http://67.43.5.76/~newserver/reports/index.php/';
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            $loop[] = 'wsimport_all_employee_listing/GetEmployeeListing';
            $loop[] = 'WsSyncStaffWithPlusClouds/updateStaff';
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/today';
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/lastweek';
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/lastmonth';
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/last90days';
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/yearly';
            $loop[] = 'wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/today';
            $loop[] = 'wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/lastweek';
            $loop[] = 'wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/lastmonth';
            $loop[] = 'wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/last90days';
            $loop[] = 'wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/yearly';
            $loop[] = 'wsimport_all_employee_monthwise_data/GetEmployeeScheduleHoursForMonthWiseFirstTime/IndividualMonth';            
            $loop[] = 'wsimport_all_employee_monthwiselastyear_data/GetEmployeeScheduleHoursForMonthWiseLastYear/IndividualMonth';
            $loop[] = 'wsimport_all_employee_weekwise_data/GetEmployeeScheduleHoursForWeekWiseFirstTime/IndividualWeek';
            $loop[] = 'wsimport_all_employee_weekwiselastyear_data/GetEmployeeScheduleHoursForWeekWiseLastYear/IndividualWeek';
            $i=1;
            if($salon_code!=''){
               $account_no = salonWebappCloudEn($salon_code);
            }
            //pa($account_no,'',true);
            foreach ($loop as $key => $value) {
                if($salon_code!=''){
                 $url = $mainurl.$value.'/'.$account_no;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
    } 

    /**
    Function get  Staff Related Current Year
    */        
    public function getStaffDataCurrentYear($salon_id=""){
           // $mainurl = 'http://67.43.5.76/~newserver/reports/index.php/';
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/Monthly';
            $loop[] = 'StaffReportsForDashboardBasedOnSkillSet/setStaffDashboard/Monthly';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/today';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastweek';            
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastmonth';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/last90days';
            $loop[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Currentmonth/1';
            $loop[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Currentmonth';
            $loop[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Currentmonth/1';

            $loop[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Currentmonth/1';
           
            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_id!=''){
                 $url = $mainurl.$value.'/'.$salon_id;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
    }

     /**
    Function get  Staff Related Last Year
    */        
    public function getStaffDataLastYear($salon_id=""){
            //$mainurl = 'http://67.43.5.76/~newserver/reports/index.php/';
            if($salon_id!=''){
                $salon_details = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                $salon_code = $salon_details['salon_account_id'];
                if($salon_code!=''){
                    $account_no = salonWebappCloudEn($salon_code);
                }
            }
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            $loop[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Lastyear';
            $loop[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Lastyear';            
            $loop[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Lastyear';
            $loop[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Lastyear';
           
            $i=1;
            foreach ($loop as $key => $value) {
                if($account_no!=''){
                 $url = $mainurl.$value.'/'.$account_no;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
    }
     /**
    Function set  Staff Related Calculations
    */  
    public function setStaffCalculation($salon_id=""){
            if($salon_id!=''){
                $salon_details = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                $salon_code = $salon_details['salon_account_id'];
                if($salon_code!=''){
                    $account_no = salonWebappCloudEn($salon_code);
                }
            }
            //$mainurl = 'http://67.43.5.76/~newserver/reports/index.php/';
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            
            $loop[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Monthly';

            $loop[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Monthly';

            $loop[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Monthly';

            $loop[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Monthly';
            
            
            $loop[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Previousmonths';
            
            $loop[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Previousmonths';

            $loop[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Previousmonths';
            
            $loop[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Previousmonths';
            $i=1;
            foreach ($loop as $key => $value) {
                if($account_no!=''){
                 $url = $mainurl.$value.'/'.$account_no;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
    }


     /**
    Function for daily crons for all calculations (dashboard and internal graphs)
    */
    public function setCalculationsNewSalonAllMethodsForFirstTime($salon_code=''){

           if($salon_code!=''){
                $salon_code = salonWebappCloudDe($salon_code);
                $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code)->result_array();
                $salon_id = $getConfigDetails[0]['salon_id'];
            }else{
                $salon_id='';
            }
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
           
            //$loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastweek';
            //$loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/today';
            //$loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/last90days/1';
            //$loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastmonth/1';
           // $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastweek/1';
           //$loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/today/1';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Previousmonths';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Previousmonths';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Previousmonths';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Yearly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Monthly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Previousmonths';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Yearly';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Monthly';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Previousmonths';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Yearly';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Yearly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Yearly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Yearly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentageRetailToServiceSalesDataForGraphs/setOwnerPercentageRetailToServiceSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerPercentageRetailToServiceSalesDataForGraphs/setOwnerPercentageRetailToServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Yearly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Monthly';
            //$loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Yearly';
            //$loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Yearly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Monthly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Yearly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Monthly';
            //$loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/Yearly';
            //$loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/Monthly';
            //$loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/threemonths';
            // staff
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/today';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastweek';            
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/Monthly';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastmonth';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/last90days';
            $loop[] = 'StaffReportsForDashboardBasedOnSkillSet/setStaffDashboard/Monthly';
            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_id!=''){
                 $url = $mainurl.$value.'/'.$salon_id;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
    }

     /**
    Function for daily crons for all calculations (dashboard and internal graphs)
    */
    public function setCalculationsNewSalonAllMethodsDailyOnce($salon_code=''){

           if($salon_code!=''){
                $salon_code = salonWebappCloudDe($salon_code);
                $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code)->result_array();
                $salon_id = $getConfigDetails[0]['salon_id'];
            }else{
                $salon_id='';
            }
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
           // $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/last90days';
        //    $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastmonth';
          //  $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/last90days/1';
        //    $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastmonth/1';
         //   $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastweek/1';
          //  $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/today/1';

            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Yearly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Monthly';
            //$loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Previousmonths';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Yearly';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Monthly';
            //$loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Previousmonths';
            

            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Yearly';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Yearly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Yearly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Yearly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentageRetailToServiceSalesDataForGraphs/setOwnerPercentageRetailToServiceSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerPercentageRetailToServiceSalesDataForGraphs/setOwnerPercentageRetailToServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Yearly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Yearly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Yearly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Monthly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Yearly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/seOwnerTotalSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/threemonths';
            // staff
            //$loop[] = 'StaffReportsForDashboard/setStaffDashboard/Monthly';
            //$loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastmonth';
            //$loop[] = 'StaffReportsForDashboard/setStaffDashboard/last90days';
            //$loop[] = 'StaffReportsForDashboardBasedOnSkillSet/setStaffDashboard/Monthly';
            // calculations
            //$loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastweek/1';
            //$loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/today/1';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Monthly';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentageRetailToServiceSalesDataForGraphs/setOwnerPercentageRetailToServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Monthly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/seOwnerTotalSalesDataForGraphs/Monthly';
            // staff
            $loop[] = 'StaffReportsForDashboardBasedOnSkillSet/setStaffDashboard/Monthly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Monthly';
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastweek/1'; 
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/today/1';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/Monthly';
            $loop[] = 'StaffReportsForDashboardBasedOnSkillSet/setStaffDashboard/Monthly';
            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_id!=''){
                 $url = $mainurl.$value.'/'.$salon_id;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
    }
    
      /**
    Function for daily crons for all calculations (dashboard and internal graphs)
    */
    public function setCalculationsNewSalonAllMethodsDailyOnceBack($salon_code=''){

           if($salon_code!=''){
                $salon_code = salonWebappCloudDe($salon_code);
                $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code)->result_array();
                $salon_id = $getConfigDetails[0]['salon_id'];
            }else{
                $salon_id='';
            }
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/last90days';
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastmonth';
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/last90days/1';
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastmonth/1';
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastweek/1';
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/today/1';

            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Yearly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Monthly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Previousmonths';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Yearly';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Monthly';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Previousmonths';
            

            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Yearly';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Yearly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Yearly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Yearly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentageRetailToServiceSalesDataForGraphs/setOwnerPercentageRetailToServiceSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerPercentageRetailToServiceSalesDataForGraphs/setOwnerPercentageRetailToServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Yearly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Yearly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Yearly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Monthly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Yearly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/Yearly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/seOwnerTotalSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/threemonths';
            // staff
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/Monthly';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastmonth';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/last90days';
            $loop[] = 'StaffReportsForDashboardBasedOnSkillSet/setStaffDashboard/Monthly';
            // calculations
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastweek/1';
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/today/1';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRepeatedGuestDataForGraphs/setOwnerRepeatedGuestDataForGraphs/Monthly';
            $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerColorPercentageDataForGraphs/setOwnerColorPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentBookedDataForGraphs/setOwnerPercentBookedDataForGraphs/Monthly';
            $loop[] = 'OwnerPercentageRetailToServiceSalesDataForGraphs/setOwnerPercentageRetailToServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerClientServicedDataForGraphs/setOwnerClientServicedDataForGraphs/Monthly';
            $loop[] = 'OwnerPrebookDataForGraphs/setOwnerPrebookDataForGraphs/Monthly';
            $loop[] = 'OwnerRUCTDataForGraphs/setOwnerRUCTDataForGraphs/Monthly';
            $loop[] = 'OwnerRebookPercentageDataForGraphs/setOwnerRebookPercentageDataForGraphs/Monthly';
            $loop[] = 'OwnerTotalSalesDataForGraphs/seOwnerTotalSalesDataForGraphs/Monthly';
            // staff
            $loop[] = 'StaffReportsForDashboardBasedOnSkillSet/setStaffDashboard/Monthly';
            $loop[] = 'OwnerNewGuestDataForGraphs/setOwnerNewGuestDataForGraphs/Monthly';
            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_id!=''){
                 $url = $mainurl.$value.'/'.$salon_id;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
    }
    
    /**
    Function for daily crons for all calculations (dashboard and internal graphs)
    */
    public function setCalculationsNewSalonAllMethodsMonthlyOnce($salon_code=''){

           if($salon_code!=''){
                $salon_code = salonWebappCloudDe($salon_code);
                $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code)->result_array();
                $salon_id = $getConfigDetails[0]['salon_id'];
            }else{
                $salon_id='';
            }
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/last90days';
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastmonth';
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/last90days/1';
            $loop[] = 'OwnerReportsForDashboardLastYear/setOwnerReportsForDashboardLastYear/lastmonth/1';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/threemonths';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastmonth';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/last90days';
            $loop[] = 'OwnerTotalSalesDataForGraphs/setOwnerTotalSalesDataForGraphs/threemonths';
            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_id!=''){
                 $url = $mainurl.$value.'/'.$salon_id;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
    }

    /**
    Function for daily crons for all calculations (dashboard and internal graphs)
    */
    public function setCalculationsNewSalonAllMethodsEveryFourHours($salon_code=''){

           if($salon_code!=''){
                $salon_code = salonWebappCloudDe($salon_code);
                $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code)->result_array();
                $salon_id = $getConfigDetails[0]['salon_id'];
            }else{
                $salon_id='';
            }
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastweek';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastweek';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/Monthly';
            $loop[] = 'OwnerServiceSalesDataForGraphs/setOwnerServiceSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerRetailSalesDataForGraphs/setOwnerRetailSalesDataForGraphs/Monthly';
            $loop[] = 'OwnerGiftCardsSalesDataForGraphs/setOwnerGiftCardsSalesDataForGraphs/Monthly'; 
            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_id!=''){
                 $url = $mainurl.$value.'/'.$salon_id;
                }else{
                 $url = $mainurl.$value;   
                }
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            }
    }
    
    public function getPhpInfo(){
       echo phpinfo();
    }
 
 }       