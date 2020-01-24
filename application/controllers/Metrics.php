<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Metrics extends REST_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Owner Dashboard WebServices
    **/
    public  $salon_id;
    public  $startDate;
    public  $endDate;
    public  $currentDate;
    private $salonId;
    private $salonDetails;
    private $dayRangeType;
    private $lastYearStartDate;
    private $lastYearEndDate;   
    private $ouathResponse;
        
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('OwnerWebServices_model');
        $this->load->library('webserviceaccess');
    }
    // Get Date Range Types
   
    
    private function __getAccessWesbService($service,$salon_id){
               $this->WebAccessResponse = $this->webserviceaccess->validateWebAppWs($service,$salon_id);
                return $this->WebAccessResponse;
    }
    /**
     * Default index Fn
     */
    public function index(){
                    $this->__getAccessWesbService();
                    // To generate Secure Auth token for specific salon.
                    print "Test";
        }
    
    /**
     * This function for get employee schedule hours last year data
     * @param type $account_no
     */
    function getAllMetrics($dayRangeType="Today")
        { 
            /*if(isset($_POST['salon_id'])){
                $salon_id = $_POST['salon_id'];
            }else{
                $salon_id = '';
            }
            
            $this->__getAccessWesbService($this->Service_url,$salon_id);
            if($this->WebAccessResponse['HTTPCODE'] != 200){
               $response_array = array('status' => false, 'message' => $this->WebAccessResponse['MESSAGE'], 'status_code' => 401);
                $response_code = $this->WebAccessResponse['HTTPCODE'];
                goto response;    
            }*/

            $res = array();
            $res[0]['title'] = 'Total Sales';
            $res[0]['key']  = 'total_sales';
            $res[0]['type'] = '$';
            $res[1]['title'] = 'Service Sales'; 
            $res[1]['key'] = 'service_revenue';
            $res[1]['type'] = '$';
            $res[2]['title'] = 'Retail Sales'; 
            $res[2]['key'] = 'total_retail_price';
            $res[2]['type'] = '$';
            $res[3]['title'] = 'Gift Cards'; 
            $res[3]['key'] = 'gift_cards';
            $res[3]['type'] = '$';
            $res[4]['title'] = 'New Guest'; 
            $res[4]['key'] = 'guest_qty_new';
            $res[4]['type'] = '';
            $res[5]['title'] = 'Repeat Guest'; 
            $res[5]['key'] = 'guest_qty_repeated';
            $res[5]['type'] = '';
            $res[6]['title'] = 'RPCT'; 
            $res[6]['key'] = 'RPCT';
            $res[6]['type'] = '$';
            $res[7]['title'] = '% Booked'; 
            $res[7]['key'] = 'percent_booked';
            $res[7]['type'] = '%';
            $res[8]['title'] = '% Prebook'; 
            $res[8]['key'] = 'prebook_percentage';
            $res[8]['type'] = '%';
            $res[9]['title'] = '% Color'; 
            $res[9]['key'] = 'color_percentage';
            $res[9]['type'] = '%';
            $res[10]['title'] = 'Avg Service Ticket'; 
            $res[10]['key'] = 'avg_service_ticket';
            $res[10]['type'] = '$';
            $res[11]['title'] = 'Estimated Sales'; 
            $res[11]['key'] = 'estimated_sales';
            $res[11]['type'] = '$';
            $response_array['result'] = $res;
            $response_code = 200;

               
           response:
           $this->response($response_array, $response_code);
            
       } 
   
 }       