<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}


class Refreshstaff extends CI_Controller {
    
        CONST INSERTED = 0;
       
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Newsalon_model');
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
       // $this->load->library("security");
    }  

	public function staff()
	{

        if(isset($_POST['id'])){
        
            $salon_id = $_POST['id'];
            $sql = $this->DB_ReadOnly->query("Select salon_account_id,salon_id from mill_all_sdk_config_details where salon_id = $salon_id")->row();
            $salon = $sql->salon_id;
            $account = $sql->salon_account_id;
            if($account!=''){
             $account = salonWebappCloudEn($account);
            }
            
            $mainurl = MAIN_SERVER_URL;
            $loop = array();
            $loop[] = 'wsimport_all_employee_listing/GetEmployeeListing/'.$account.' ';
            $loop[] = 'WsSyncStaffWithPlusClouds/updateStaff/'.$account.' ';
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/today/'.$account.' ';
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/lastweek/'.$account.'';
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/lastmonth/'.$account.'';
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/last90days/'.$account.'';
            $loop[] = 'wsimport_all_employee_currentyear_data/GetEmployeeScheduleHoursForCurrentYear/yearly/'.$account.'';
            $loop[] = 'wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/today/'.$account.'';
            $loop[] = 'wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/lastweek/'.$account.'';
            $loop[] = 'wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/lastmonth/'.$account.'';
            $loop[] = 'wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/last90days/'.$account.'';
            $loop[] = 'wsimport_all_employee_lastyear_data/GetEmployeeScheduleHoursForLastYear/yearly/'.$account.'';
            $loop[] = 'wsimport_all_employee_monthwise_data/GetEmployeeScheduleHoursForMonthWise/IndividualMonth/'.$account.'';            
            $loop[] = 'wsimport_all_employee_monthwiselastyear_data/GetEmployeeScheduleHoursForMonthWiseLastYear/IndividualMonth/'.$account.'';
            $loop[] = 'wsimport_all_employee_weekwise_data/GetEmployeeScheduleHoursForWeekWise/IndividualWeek/'.$account.'';
            $loop[] = 'wsimport_all_employee_weekwiselastyear_data/GetEmployeeScheduleHoursForWeekWiseLastYear/IndividualWeek/'.$account.'';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/Monthly/'.$salon.' ';
            $loop[] = 'StaffReportsForDashboardBasedOnSkillSet/setStaffDashboard/Monthly/'.$salon.'';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/today/'.$salon.'';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastweek/'.$salon.'';            
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/lastmonth/'.$salon.'';
            $loop[] = 'StaffReportsForDashboard/setStaffDashboard/last90days/'.$salon.'';
            $loop[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Currentmonth/1/'.$salon.'';
            $loop[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Currentmonth/'.$salon.'';
            $loop[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Currentmonth/1/'.$salon.'';
            $loop[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Currentmonth/1/'.$salon.'';
            $loop[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Lastyear/1/'.$salon.'';
            $loop[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Lastyear/1/'.$salon.'';
            $loop[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Lastyear/1/'.$salon.'';
            $loop[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Lastyear/1/'.$salon.'';
            $loop[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Monthly/1/'.$salon.'';
            $loop[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Monthly/1/'.$salon.'';
            $loop[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Monthly/1/'.$salon.'';
            $loop[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Monthly/1/'.$salon.'';
            $loop[] = 'StaffPrebookDataForGraphs/setStaffPrebookInternalGraph/Previousmonths/1/'.$salon.'';
            $loop[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Previousmonths/1/'.$salon.'';
            $loop[] = 'StaffRetailBuyByClientForGraphs/setStaffRetailBuyByClientInternalGraph/Previousmonths/1/'.$salon.'';
            $loop[] = 'StaffRuctServedByClientForGraphs/setStaffRuctServedInternalGraph/Previousmonths/1/'.$salon.'';
            $loop[]= 'OwnerReportsForDashboard/setOwnerReportsDashboard/last90days/'.$salon.'';
			$loop[]= 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastmonth/'.$salon.'';
			$loop[]= 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastweek/'.$salon.'';
			$loop[]= 'OwnerReportsForDashboard/setOwnerReportsDashboard/today/'.$salon.'';
             $i=1;
                 foreach ($loop as $key => $value) {
                 $url = $mainurl.$value;
                 pa($url,$i);
                 $op = $this->Common_model->insertDataByUsingCUrl($url);
                 $i++;
                 }    
       } 
       else{
            echo 'id is not inserted properlly';
        }
	}
}


