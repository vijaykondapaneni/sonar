<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class CronsStop extends CI_Controller
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

    public function setCronsStop(){
    $update = $this->db->update(MILL_ALL_SDK_CONFIG_DETAILS,array('status'=>1));
    if($update){
        pa('Successfully Updated');
    }
    // truncate tables
    $this->db->truncate(MILL_ALL_SDK_CONFIG_DETAILS);
    $this->db->truncate(MILL_SERVICE_SALES);
    $this->db->truncate(MILL_PRODUCT_SALES);
    $this->db->truncate(MILL_GIFT_CARD_SALES_WITH_BALANCE);
    $this->db->truncate(MILL_CLIENTS_TABLE);
    $this->db->truncate(MILL_APPTS_TABLE);
    $this->db->truncate(MILL_PAST_APPTS_TABLE);
    $this->db->truncate(MILL_GIFT_CARDS_OWNER_REPORTS);
    $this->db->truncate(MILL_PERCENT_BOOKED_OWNER_REPORTS);
    $this->db->truncate(MILL_PERCENT_COLOR_OWNER_REPORTS);
    $this->db->truncate(MILL_PERCENT_PREBOOKED_OWNER_REPORTS);
    $this->db->truncate(MILL_REPEAT_GUEST_OWNER_REPORTS);
    $this->db->truncate(MILL_RETAIL_OWNER_REPORTS);
    $this->db->truncate(MILL_RPCT_OWNER_REPORTS);
    $this->db->truncate(MILL_SERVICE_OWNER_REPORTS);
    $this->db->truncate(MILL_TOTAL_REVENUE_REPORTS);
    $this->db->truncate(MILL_EMPLOYEE_SCHEDULE_HOURS);
    $this->db->truncate(MILL_NEW_GUEST_OWNER_REPORTS);
    $this->db->truncate(MILL_OWNER_REPORT_CALCULATIONS_CRON);
    $this->db->truncate(STAFF2_TABLE);
    $this->db->truncate(MILL_CLIENTS_SERVED_OWNER_REPORTS);
    $this->db->truncate(MILL_RUCT_CALCULATION_CRON);
    $this->db->truncate(MILL_CLIENT_BUYING_RETAIL_STAFF_REPORTS);
    $this->db->truncate(MILL_RUNNING_CRON_LOG_REPORT);
    $this->db->truncate(MILL_EMPLOYEE_LISTING);
  }   
 
}       