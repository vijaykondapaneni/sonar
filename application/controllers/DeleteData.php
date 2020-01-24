<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class DeleteData extends CI_Controller
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

    public function deletefrom_salonid() 
    {  

       $salonids=array('751');
       $salon_ids=implode(",",$salonids);
       
       $tables=array('mill_clients_served_owner_reports','mill_client_buying_retail_staff_reports','mill_new_guest_owner_reports','mill_percentage_retail_to_service_sales_owner_reports','mill_percent_booked_owner_reports','mill_rebook_percentage_owner_reports','mill_repeat_guest_owner_reports','mill_report_based_on_skillset_calculations_cron','mill_retail_owner_reports','mill_rpct_calculations_cron','mill_rpct_owner_reports','mill_ruct_calculation_cron','mill_ruct_owner_reports','mill_service_owner_reports','mill_topfive_services_owner_report','mill_total_revenue_reports','mill_all_sdk_config_details','plus_staff2');
       foreach ($tables as $tab){       
        $qry = $this->db->query("DELETE FROM $tab  WHERE salon_id  IN  ($salon_ids)");
        echo  $this->db->last_query();echo "<br>";echo "<br>";
       }
       exit;
      
    }
    
  
  public function deletefrom_accountnumber() 
   {
    $salonactnumbers=array('484654003');
    $salon_accountnumbers=implode(",",$salonactnumbers);
       
    $tables=array('mill_service_sales','mill_employee_listing','mill_employee_schedule_hours','mill_gift_card_sales_with_balance','mill_product_sales');
       
     foreach ($tables as $tab){ 
        $qry = $this->db->query("DELETE FROM $tab WHERE account_no  IN  ($salon_accountnumbers)");
        echo  $this->db->last_query();echo "<br>";echo "<br>";
       }
       exit;
      
  }


  public function deletefrom_accountnumber_capital() 
   {
    $salonactnumbers=array('484654003');
    $salon_accountnumbers=implode(",",$salonactnumbers);
       
    $tables=array('mill_appointments','mill_clients','mill_cron_running_logs','mill_past_appointments');
       
     foreach ($tables as $tab){ 
        $qry = $this->db->query("DELETE FROM $tab WHERE AccountNo  IN  ($salon_accountnumbers)");
        echo  $this->db->last_query();echo "<br>";echo "<br>";
       }
       exit;
      
  }




  public function notExistSalons_deletefrom_salonid() 
    {  

      $allsalons = $this->db->get('mill_all_sdk_config_details')->result_array();
      if(!empty($allsalons)){
        $salon_ids = '';
        foreach ($allsalons as $key => $value) {
            $salon_ids.= $value['salon_id'].","; 
        }
        $final_salon_ids = trim($salon_ids,",");
        
      
       
       $tables=array('mill_clients_served_owner_reports','mill_client_buying_retail_staff_reports','mill_new_guest_owner_reports','mill_percentage_retail_to_service_sales_owner_reports','mill_percent_booked_owner_reports','mill_rebook_percentage_owner_reports','mill_repeat_guest_owner_reports','mill_report_based_on_skillset_calculations_cron','mill_retail_owner_reports','mill_rpct_calculations_cron','mill_rpct_owner_reports','mill_ruct_calculation_cron','mill_ruct_owner_reports','mill_service_owner_reports','mill_topfive_services_owner_report','mill_total_revenue_reports','mill_all_sdk_config_details','plus_staff2');
       foreach ($tables as $tab){       
        //echo "DELETE FROM $tab  WHERE salon_id  NOT IN  ($final_salon_ids)";
        $qry = $this->db->query("DELETE FROM $tab  WHERE salon_id NOT IN  ($final_salon_ids)");
        echo  $this->db->last_query();echo "<br>";echo "<br>";
       }
       exit;
     }  
      
    }
    
  
  public function notExistSalons_deletefrom_accountnumber() 
   {
    

    $allsalons = $this->db->get('mill_all_sdk_config_details')->result_array();
    if(!empty($allsalons)){
        $accountnos = '';
        
        foreach ($allsalons as $key => $value) {
            $accountnos.= $value['salon_account_id'].","; 
        }

        $final_accountnos = trim($accountnos,",");

       $tables=array('mill_service_sales','mill_employee_listing','mill_employee_schedule_hours','mill_gift_card_sales_with_balance','mill_product_sales');
         
       foreach ($tables as $tab){ 
          //echo "DELETE FROM $tab WHERE account_no  NOT IN  ($final_accountnos)";
          $qry = $this->db->query("DELETE FROM $tab WHERE account_no  NOT IN  ($final_accountnos)");
          echo  $this->db->last_query();echo "<br>";echo "<br>";
         }
         exit;
    }
       
      
  }


  public function notExistSalons_deletefrom_accountnumber_capital() 
   {
    
    $allsalons = $this->db->get('mill_all_sdk_config_details')->result_array();

     if(!empty($allsalons)){
        $accountnos = '';

        foreach ($allsalons as $key => $value) {
            $accountnos.= $value['salon_account_id'].","; 
        }
        $final_accountnos = trim($accountnos,",");

        


       $tables=array('mill_appointments','mill_clients','mill_cron_running_logs','mill_past_appointments');
       foreach ($tables as $tab){
        //echo "DELETE FROM $tab WHERE AccountNo NOT IN  ($final_accountnos)";
        $qry = $this->db->query("DELETE FROM $tab WHERE AccountNo NOT IN  ($final_accountnos)");
        echo  $this->db->last_query();echo "<br>";echo "<br>";
       }
       exit;

     }
       
    
      
  }

           
 
 }       