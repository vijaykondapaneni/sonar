<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class SetCrons extends CI_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Set the crons in all shell scripted files
    **/
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
    }
    CONST FILE_PATH = '/home/ec2-user/cronscripts/';
    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    /**
     *Getting past appointments 
     * @param type $salon_id
     */
    public function setCronsInFiles($salon_code=""){
        if($salon_code==''){
            print "Please provide salon account number";
            exit();
        }
         $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code);
            if($getConfigDetails->num_rows()>0){
                foreach($getConfigDetails->result_array() as $configDetails){
                    pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
                    $account_no = $configDetails['salon_account_id'];
                    $salon_id = $configDetails['salon_id'];
                    $encoded_code = salonWebappCloudEn($account_no);
                    // for employee listing
                    $file = fopen(self::FILE_PATH.'getEmployeeListing.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_listing GetEmployeeListing '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    //wsimport_all_employee_currentyear_data today
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_currentyear_data_today.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_currentyear_data GetEmployeeScheduleHoursForCurrentYear today '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    //wsimport_all_employee_currentyear_data lastweek
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_currentyear_data_lastweek.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_currentyear_data GetEmployeeScheduleHoursForCurrentYear lastweek '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    //GetEmployeeScheduleHoursForCurrentYear lastmonth
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_currentyear_data_lastmonth.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_currentyear_data GetEmployeeScheduleHoursForCurrentYear lastmonth '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    //GetEmployeeScheduleHoursForCurrentYear monthly
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_currentyear_data_monthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_currentyear_data GetEmployeeScheduleHoursForCurrentYear monthly '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    //GetEmployeeScheduleHoursForCurrentYear last90days
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_currentyear_data_last90days.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_currentyear_data GetEmployeeScheduleHoursForCurrentYear last90days '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    //GetEmployeeScheduleHoursForCurrentYear yearly
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_currentyear_data_yearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_currentyear_data GetEmployeeScheduleHoursForCurrentYear yearly '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    //StaffAvgRpctDataForGraphs Lastyear
                    $file = fopen(self::FILE_PATH.'StaffAvgRpctDataForGraphsLastyear.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffAvgRpctDataForGraphs setStaffRpctInternalGraph Lastyear '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // StaffAvgRpctDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'StaffAvgRpctDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffAvgRpctDataForGraphs setStaffRpctInternalGraph Monthly '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // StaffAvgRpctDataForGraphs Previousmonths 
                    $file = fopen(self::FILE_PATH.'StaffAvgRpctDataForGraphsPreviousmonths.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffAvgRpctDataForGraphs setStaffRpctInternalGraph Previousmonths '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // StaffAvgRpctDataForGraphs Currentmonth 
                    $file = fopen(self::FILE_PATH.'StaffAvgRpctDataForGraphsCurrentmonth.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffAvgRpctDataForGraphs setStaffRpctInternalGraph Currentmonth '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // StaffRetailBuyByClientForGraphs Lastyear 
                    $file = fopen(self::FILE_PATH.'StaffRetailBuyByClientForGraphsLastyear.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffRetailBuyByClientForGraphs setStaffRetailBuyByClientInternalGraph Lastyear '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // StaffRetailBuyByClientForGraphs Monthly 
                    $file = fopen(self::FILE_PATH.'StaffRetailBuyByClientForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffRetailBuyByClientForGraphs setStaffRetailBuyByClientInternalGraph Monthly '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    // StaffRetailBuyByClientForGraphs Previousmonths 
                    $file = fopen(self::FILE_PATH.'StaffRetailBuyByClientForGraphsPreviousmonths.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffRetailBuyByClientForGraphs setStaffRetailBuyByClientInternalGraph Previousmonths '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // StaffRetailBuyByClientForGraphs Currentmonth 
                    $file = fopen(self::FILE_PATH.'StaffRetailBuyByClientForGraphsCurrentmonth.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffRetailBuyByClientForGraphs setStaffRetailBuyByClientInternalGraph Currentmonth '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // StaffRuctServedByClientForGraphs Lastyear 
                    $file = fopen(self::FILE_PATH.'StaffRuctServedByClientForGraphsLastyear.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffRuctServedByClientForGraphs setStaffRuctServedInternalGraph Lastyear '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // StaffRuctServedByClientForGraphs Monthly 
                    $file = fopen(self::FILE_PATH.'StaffRuctServedByClientForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffRuctServedByClientForGraphs setStaffRuctServedInternalGraph Monthly '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // StaffRuctServedByClientForGraphs Previousmonths 
                    $file = fopen(self::FILE_PATH.'StaffRuctServedByClientForGraphsPreviousmonths.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffRuctServedByClientForGraphs setStaffRuctServedInternalGraph Previousmonths '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // StaffRuctServedByClientForGraphs Currentmonth 
                    $file = fopen(self::FILE_PATH.'StaffRuctServedByClientForGraphsCurrentmonth.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffRuctServedByClientForGraphs setStaffRuctServedInternalGraph Currentmonth '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // wsimport_all_employee_monthwise_data IndividualMonth 
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_monthwise_dataIndividualMonth.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_monthwise_data GetEmployeeScheduleHoursForMonthWise IndividualMonth '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    // wsimport_all_employee_monthwiselastyear_data IndividualMonth 
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_monthwise_dataIndividualMonth.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_monthwiselastyear_data GetEmployeeScheduleHoursForMonthWiseLastYear IndividualMonth '. $encoded_code.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);

                    // wsimport_all_employee_lastyear_data today 
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_lastyear_datatoday.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_lastyear_data GetEmployeeScheduleHoursForLastYear today '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    // wsimport_all_employee_lastyear_data lastweek 
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_lastyear_datalastweek.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_lastyear_data GetEmployeeScheduleHoursForLastYear lastweek '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    // wsimport_all_employee_lastyear_data lastmonth 
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_lastyear_datalastmonth.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_lastyear_data GetEmployeeScheduleHoursForLastYear lastmonth '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    // wsimport_all_employee_lastyear_data last90days 
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_lastyear_datalast90days.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_lastyear_data GetEmployeeScheduleHoursForLastYear last90days '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    // wsimport_all_employee_lastyear_data yearly 
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_lastyear_datayearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_lastyear_data GetEmployeeScheduleHoursForLastYear yearly '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    // StaffPrebookDataForGraphs  Monthly 
                    $file = fopen(self::FILE_PATH.'StaffPrebookDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffPrebookDataForGraphs setStaffPrebookInternalGraph Monthly '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    // StaffPrebookDataForGraphs  Previousmonths 
                    $file = fopen(self::FILE_PATH.'StaffPrebookDataForGraphsPreviousmonths.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffPrebookDataForGraphs setStaffPrebookInternalGraph Previousmonths '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    // StaffPrebookDataForGraphs  Lastyear 
                    $file = fopen(self::FILE_PATH.'StaffPrebookDataForGraphsLastyear.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffPrebookDataForGraphs setStaffPrebookInternalGraph Lastyear '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    // wsimport_all_employee_weekwise_data  IndividualWeek 
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_weekwise_dataIndividualWeek.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_weekwise_data GetEmployeeScheduleHoursForWeekWise IndividualWeek '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    // wsimport_all_employee_weekwiselastyear_data  IndividualWeek 
                    $file = fopen(self::FILE_PATH.'wsimport_all_employee_weekwiselastyear_dataIndividualWeek.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php wsimport_all_employee_weekwiselastyear_data GetEmployeeScheduleHoursForWeekWiseLastYear IndividualWeek '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    // StaffPercentBookedDataForGraphs  Yearly 
                    $file = fopen(self::FILE_PATH.'StaffPercentBookedDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffPercentBookedDataForGraphs setStaffPercentBookedDataForGraphs Yearly '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    // StaffPercentBookedDataForGraphs  Monthly 
                    $file = fopen(self::FILE_PATH.'StaffPercentBookedDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffPercentBookedDataForGraphs setStaffPercentBookedDataForGraphs Monthly '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    // StaffPercentBookedDataForGraphs  Previousmonths 
                    $file = fopen(self::FILE_PATH.'StaffPercentBookedDataForGraphsPreviousmonths.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffPercentBookedDataForGraphs setStaffPercentBookedDataForGraphs Previousmonths '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    // WsSyncStaffWithPlusClouds  
                    $file = fopen(self::FILE_PATH.'WsSyncStaffWithPlusClouds.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php WsSyncStaffWithPlusClouds updateStaff '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //Wsimport_all_salon_services_data_lastday
                    $file = fopen(self::FILE_PATH.'Wsimport_all_salon_services_data_lastday.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_all_salon_services_data_lastday GetServiceSales '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //Wsimport_all_salon_services_data_today
                    $file = fopen(self::FILE_PATH.'Wsimport_all_salon_services_data_today.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_all_salon_services_data_today GetServiceSales '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //Wsimport_all_salon_product_data_today
                    $file = fopen(self::FILE_PATH.'Wsimport_all_salon_product_data_today.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_all_salon_product_data_today getProductSales '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //Wsimport_all_salon_product_data_lastday
                    $file = fopen(self::FILE_PATH.'Wsimport_all_salon_product_data_lastday.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_all_salon_product_data_lastday getProductSales '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //Wsimport_all_salon_gift_card_data_today
                    $file = fopen(self::FILE_PATH.'Wsimport_all_salon_gift_card_data_today.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_all_salon_gift_card_data_today GetGiftCertificatesSales '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //Wsimport_all_salon_gift_card_data_lastday
                    $file = fopen(self::FILE_PATH.'Wsimport_all_salon_gift_card_data_lastday.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_all_salon_gift_card_data_lastday GetGiftCertificatesSales '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    //Wsimport_millsdk_past_appts_today
                    $file = fopen(self::FILE_PATH.'Wsimport_millsdk_past_appts_today.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_millsdk_past_appts_today getPastAppointments '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //Wsimport_millsdk_appts Oneweek
                    $file = fopen(self::FILE_PATH.'Wsimport_millsdk_apptsOneweek.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_millsdk_appts getAppointments Oneweek '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //Wsimport_millsdk_appts Twomonths
                    $file = fopen(self::FILE_PATH.'Wsimport_millsdk_apptsTwomonths.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_millsdk_appts getAppointments Twomonths '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //Wsimport_millsdk_appts Fourmonths
                    $file = fopen(self::FILE_PATH.'Wsimport_millsdk_apptsFourmonths.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_millsdk_appts getAppointments Fourmonths '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //Wsimport_millsdk_appts Sixmonths
                    $file = fopen(self::FILE_PATH.'Wsimport_millsdk_apptsSixmonths.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php Wsimport_millsdk_appts getAppointments Sixmonths '. $encoded_code.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerReportsForDashboard today
                    $file = fopen(self::FILE_PATH.'OwnerReportsForDashboardtoday.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerReportsForDashboard setOwnerReportsDashboard today '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //StaffReportsForDashboard today
                    $file = fopen(self::FILE_PATH.'StaffReportsForDashboardtoday.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffReportsForDashboard setStaffDashboard today '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerServiceSalesDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerServiceSalesDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerServiceSalesDataForGraphs setOwnerServiceSalesDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerServiceSalesDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerServiceSalesDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerServiceSalesDataForGraphs setOwnerServiceSalesDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerRetailSalesDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerRetailSalesDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerRetailSalesDataForGraphs setOwnerRetailSalesDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerRetailSalesDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerRetailSalesDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerRetailSalesDataForGraphs setOwnerRetailSalesDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerGiftCardsSalesDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerGiftCardsSalesDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerGiftCardsSalesDataForGraphs setOwnerGiftCardsSalesDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerGiftCardsSalesDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerGiftCardsSalesDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerGiftCardsSalesDataForGraphs setOwnerGiftCardsSalesDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerNewGuestDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerNewGuestDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerNewGuestDataForGraphs setOwnerNewGuestDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerNewGuestDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerNewGuestDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerNewGuestDataForGraphs setOwnerNewGuestDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerRepeatedGuestDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerRepeatedGuestDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerRepeatedGuestDataForGraphs setOwnerRepeatedGuestDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerRepeatedGuestDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerRepeatedGuestDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerRepeatedGuestDataForGraphs setOwnerRepeatedGuestDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    //OwnerRpctDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerRpctDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerRpctDataForGraphs setOwnerRpctDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerRpctDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerRpctDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerRpctDataForGraphs setOwnerRpctDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    //OwnerPrebookDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerPrebookDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerPrebookDataForGraphs setOwnerPrebookDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerPrebookDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerPrebookDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerPrebookDataForGraphs setOwnerPrebookDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    //OwnerColorPercentageDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerColorPercentageDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerColorPercentageDataForGraphs setOwnerColorPercentageDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerColorPercentageDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerColorPercentageDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerColorPercentageDataForGraphs setOwnerColorPercentageDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    //OwnerPercentBookedDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerPercentBookedDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerPercentBookedDataForGraphs setOwnerPercentBookedDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerPercentBookedDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerPercentBookedDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerPercentBookedDataForGraphs setOwnerPercentBookedDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    //OwnerPercentageRetailToServiceSalesDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerPercentageRetailToServiceSalesDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerPercentageRetailToServiceSalesDataForGraphs setOwnerPercentageRetailToServiceSalesDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerPercentageRetailToServiceSalesDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerPercentageRetailToServiceSalesDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerPercentageRetailToServiceSalesDataForGraphs setOwnerPercentageRetailToServiceSalesDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                    //OwnerClientServicedDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerClientServicedDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerClientServicedDataForGraphs setOwnerClientServicedDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerClientServicedDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerClientServicedDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerClientServicedDataForGraphs setOwnerClientServicedDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);

                   
                    //OwnerRUCTDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerRUCTDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerRUCTDataForGraphs setOwnerRUCTDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerRUCTDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerRUCTDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerRUCTDataForGraphs setOwnerRUCTDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);

                    //OwnerRebookPercentageDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerRebookPercentageDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerRebookPercentageDataForGraphs setOwnerRebookPercentageDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerRebookPercentageDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerRebookPercentageDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerRebookPercentageDataForGraphs setOwnerRebookPercentageDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);

                    
                    //OwnerTotalSalesDataForGraphs Yearly
                    $file = fopen(self::FILE_PATH.'OwnerTotalSalesDataForGraphsYearly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerTotalSalesDataForGraphs setOwnerTotalSalesDataForGraphs Yearly '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerTotalSalesDataForGraphs Monthly
                    $file = fopen(self::FILE_PATH.'OwnerTotalSalesDataForGraphsMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerTotalSalesDataForGraphs setOwnerTotalSalesDataForGraphs Monthly '. $salon_id.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    //OwnerTotalSalesDataForGraphs threemonths
                    $file = fopen(self::FILE_PATH.'OwnerTotalSalesDataForGraphsthreemonths.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerTotalSalesDataForGraphs setOwnerTotalSalesDataForGraphs threemonths '. $salon_id.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);

                    //StaffReportsForDashboardBasedOnSkillSet Monthly
                    $file = fopen(self::FILE_PATH.'StaffReportsForDashboardBasedOnSkillSetMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffReportsForDashboardBasedOnSkillSet setStaffDashboard Monthly '. $salon_id.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);

                    //StaffReportsForDashboard Monthly
                    $file = fopen(self::FILE_PATH.'StaffReportsForDashboardMonthly.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffReportsForDashboard setStaffDashboard Monthly '. $salon_id.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);

                    //OwnerReportsForDashboard lastweek
                    $file = fopen(self::FILE_PATH.'OwnerReportsForDashboardlastweek.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerReportsForDashboard setOwnerReportsDashboard lastweek '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //StaffReportsForDashboard lastweek
                    $file = fopen(self::FILE_PATH.'StaffReportsForDashboardlastweek.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffReportsForDashboard setStaffDashboard lastweek '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerReportsForDashboard lastmonth
                    $file = fopen(self::FILE_PATH.'OwnerReportsForDashboardlastmonth.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerReportsForDashboard setOwnerReportsDashboard lastmonth '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //OwnerReportsForDashboard last90days
                    $file = fopen(self::FILE_PATH.'OwnerReportsForDashboardlast90days.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerReportsForDashboard setOwnerReportsDashboard last90days '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                    //StaffReportsForDashboard lastmonth
                    $file = fopen(self::FILE_PATH.'StaffReportsForDashboardlastmonth.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffReportsForDashboard setStaffDashboard lastmonth '. $salon_id.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    //StaffReportsForDashboard last90days
                    $file = fopen(self::FILE_PATH.'StaffReportsForDashboardlast90days.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php StaffReportsForDashboard setStaffDashboard last90days '. $salon_id.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);

                    //OwnerReportsForDashboardLastYear last90days
                    $file = fopen(self::FILE_PATH.'OwnerReportsForDashboardLastYearlast90days.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/index.php OwnerReportsForDashboardLastYear setOwnerReportsForDashboardLastYear last90days 1 '. $salon_id.' > /dev/null 2>&1'."\n"; 
                    echo fwrite($file,$string);
                } 
            }else{
                pa('Invalid Salon...Please Recheck');
            }       



        }    
  
}      