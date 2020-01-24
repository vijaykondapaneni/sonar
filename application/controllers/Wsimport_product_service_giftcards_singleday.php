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
    function setImport($salon_code='')
        {
            if($salon_code!=''){
                $salon_code = salonWebappCloudDe($salon_code);
                $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code)->result_array();
                $salon_code = $getConfigDetails[0]['salon_account_id'];
            }

            $mainurl = 'http://67.43.5.76/~newserver/reports/index.php/';
            //$mainurl = 'localhost/salon-reports/';
            $loop = array();
            $loop[] = 'wsimport_all_salon_product_data/getProductSales';
            $loop[] = 'wsimport_all_salon_services_data/GetServiceSales';
            $loop[] = 'wsimport_all_salon_gift_card_data/GetGiftCertificatesSales';
            $loop[] = 'wsimport_millsdk_past_appts/getPastAppointments';
            $i=1;
            foreach ($loop as $key => $value) {
                if($salon_code!=''){
                $dates = date('Y-m-d');
                $datesranges = $dates.'/'.$dates;   
                $url = $mainurl.$value.'/'.$datesranges.'/'.$salon_code;
                }else{
                 $url = $mainurl.$value;   
                }
               // pa($i);
                pa($url,$i);
                $op = $this->Common_model->insertDataByUsingCUrl($url);
                $i++;
            } 
        }
 }       