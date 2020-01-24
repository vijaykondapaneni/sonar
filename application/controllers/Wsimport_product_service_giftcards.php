<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class Wsimport_product_service_giftcards extends CI_Controller
{
    
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
    public function index(){print "Test";}
    
    /**
     * 
     * @param type $dayRangeType
     * @param type $s
     * @param type $e
     */

    /**
    For Calculations
    */
    function setImport_New($salon_code_encode='')
        {
            // not for epic salon
          if($salon_code_encode!='VmtkNFUyRnJNVVpPVlZaV1ZrVktVRnBYTVdwbFFUMDk='){
            if($salon_code_encode!=''){
                $salon_code = salonWebappCloudDe($salon_code_encode);
                $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code)->result_array();

                $salon_code = $getConfigDetails[0]['salon_account_id'];
            }
          
            //$mainurl = 'http://67.43.5.76/~newserver/reports/index.php/';
            $mainurl = MAIN_SERVER_URL;
            //$mainurl = 'localhost/salon-reports/';
            $loop = array();
            $loop[] = 'wsimport_all_salon_product_data/getProductSales';
            $loop[] = 'wsimport_all_salon_services_data/GetServiceSales';
            $loop[] = 'wsimport_all_salon_gift_card_data/GetGiftCertificatesSales';
            $loop[] = 'wsimport_millsdk_past_appts/getPastAppointments';
            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_code!=''){
                $current_date = date('Y-m-d');
                $dates = date('Y-m-d');
                $datesranges = $dates.'/'.$dates;
                $onedaybefore = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $dates) ) ));
                $current_date_onedaybefore = date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $dates) ) ));
                $olddatesranges = $onedaybefore.'/'.$onedaybefore;
                if($current_date==$dates){
                $url = $mainurl.$value.'/'.$datesranges.'/'.$salon_code;
                }

                if($current_date_onedaybefore==$onedaybefore){
                 $url_oneday = $mainurl.$value.'/'.$olddatesranges.'/'.$salon_code;
                }

            }else{
                 $url = $mainurl.$value;   
                 $url_oneday = $mainurl.$value;   
                }
               // pa($i);
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                pa($url_oneday,$i);
                $old_op = $this->Common_model->insertDataByUsingCUrl($url_oneday);
                $i++;
            }
            // set calculations
            $newloop = array();
            $newloop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastweek';
            $newloop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/today';
            $newloop[] = 'StaffReportsForDashboard/setStaffDashboard/today';
            $newloop[] = 'StaffReportsForDashboard/setStaffDashboard/lastweek';
            $newloop[] = 'StaffReportsForDashboard/setStaffDashboard/lastmonth';
            $newloop[] = 'StaffReportsForDashboard/setStaffDashboard/Monthly';
            $i=1;

            if($salon_code_encode!=''){
                $salon_code = salonWebappCloudDe($salon_code_encode);
                $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code)->result_array();
                //pa($getConfigDetails);
                $salon_code = $getConfigDetails[0]['salon_account_id'];
                $salon_id = $getConfigDetails[0]['salon_id'];
            }
            foreach ($newloop as $key => $value) {
                
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
     }    
 }       