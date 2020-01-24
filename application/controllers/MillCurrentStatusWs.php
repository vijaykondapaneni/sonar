<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class MillCurrentStatusWs extends REST_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Owner Dashboard WebServices
    **/
    public  $salon_id;
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
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
    function getMillCurrentStatusWs()
        { 
           
            if(isset($_POST['salon_id']) && $_POST['salon_id']!=''){
                $salon_id = $_POST['salon_id'];

                     $this->db->where('salon_id',$salon_id);
                     $this->db->order_by("created_date","desc");
                     $res = $this->db->get('mill_all_salons_sdk_reports_server')->row_array();
                     $data['session_status'] = $res['session_status'];
                     $data['appointment_status'] = $res['appointment_status'];
                     $data['session_error'] = $res['session_error'];
                     $data['appointment_error'] = $res['appointment_error'];
                     $data['created_date'] = $res['created_date'];
                     $response_array['data'] = $data;
                     $response_code = 200;
                
            }else{
                $response_array = array('status' => false, 'message' => 'Invalid Salon Id', 'status_code' => 401);
                $response_code = 401;
                goto response; 
            }

           response:
           $this->response($response_array, $response_code);
            
       } 
   
 }       