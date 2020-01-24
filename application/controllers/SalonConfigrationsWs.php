<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class SalonConfigrationsWs extends REST_Controller
{
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR salon metrics settings WebServices for plus to intermediate
    **/
        
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('OwnerWebServices_model');
        $this->load->library('webserviceaccess');
    }
    
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
    function getConfigSalonData($dayRangeType="Today")
        { 
           
            if(isset($_POST['salon_id']) && $_POST['salon_id']!=''){
                $salon_id = $_POST['salon_id'];
                $staff_id = isset($_POST['salon_id']) ? $_POST['salon_id'] : '';
                $this->data['allsalons'] = $this->Common_model->getAllSalons($salon_id);
                $this->data['allservices'] = $this->Common_model->getAllServiceTypes();
                $this->data['allleaderboardtypes'] = $this->Common_model->getAllLeaderBoardTypes();
                $this->data['allrpcts'] = $this->Common_model->getAllRpcts();
                // staff side
                $this->data['allstaffservices'] = $this->Common_model->getAllStaffServiceTypes();
                $this->data['allstaffleaderboardtypes'] = $this->Common_model->getAllStaffLeaderBoardTypes();
                $response_array['data'] = $this->data;
                $response_code = 200;  
               
            }else{
                $response_array = array('status' => false, 'message' => 'Invalid Salon Id', 'status_code' => 401);
                $response_code = 401;
                goto response; 
            }

           response:
           $this->response($response_array, $response_code);
       } 

    public function getSalonInfoSettingsWs(){

        if(isset($_POST['salon_id']) && $_POST['salon_id']!=''){
                $salon_id = $_POST['salon_id'];
                $staff_id = isset($_POST['salon_id']) ? $_POST['salon_id'] : '';
                $salon_info = $sdkdata = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                $response_array['salon_info'] = $salon_info;
                $response_code = 200;  
               
            }else{
                $response_array = array('status' => false, 'message' => 'Invalid Salon Id', 'status_code' => 401);
                $response_code = 401;
                goto response; 
            }

           response:
           $this->response($response_array, $response_code);
    }   

    public function updateOwnerSettingsWs(){

        if(isset($_POST['salon_id']) && $_POST['salon_id']!=''){
                $salon_id = $_POST['salon_id'];
                $sdkdata = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                $updatearray['service_types'] = $_POST['servicetype'];
                $updatearray['leaderboard_type'] = $_POST['leaderboardtype'];
                $updatearray['rpct_type'] = $_POST['rpcttype'];
                $this->db->where('salon_id',$_POST['salon_id']);
                $this->db->update('mill_all_sdk_config_details',$updatearray);
                //pa($this->db->last_query());
                // update lastmonth and last90days and internal grpahs for staff and owner
                $existingrpct = $sdkdata['rpct_type'];
                if($existingrpct!=$_POST['rpcttype']){
                        $mainurl = MAIN_SERVER_URL;
                        $loop = array();
                        $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/last90days';
                        $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastmonth';
                        $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/lastweek';
                        $loop[] = 'OwnerReportsForDashboard/setOwnerReportsDashboard/today';
                        $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Previousmonths';
                        $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Monthly';
                        $loop[] = 'OwnerRpctDataForGraphs/setOwnerRpctDataForGraphs/Yearly';
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
                            //pa($url,$i);
                            $op = $this->Common_model->insertDataByUsingCUrl($url);
                            $i++;
                        }
                        // account_no encoded
                            $salon_details = $this->Common_model->getMillSdkConfigDetailsBy($salon_id)->row_array();
                            $salon_code = $salon_details['salon_account_id'];
                            if($salon_code!=''){
                                $account_no = salonWebappCloudEn($salon_code);
                            }
                        
                            $loop1 = array();
                            $loop1[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Yearly';
                            $loop1[] = 'StaffAvgRpctDataForGraphs/setStaffRpctInternalGraph/Monthly';
                            $i=1;
                            foreach ($loop1 as $key => $value) {
                                if($account_no!=''){
                                 $url = $mainurl.$value.'/'.$account_no;
                                }else{
                                 $url = $mainurl.$value;   
                                }
                                //pa($url,$i);
                                $op = $this->Common_model->insertDataByUsingCUrl($url);
                                $i++;
                            }
                    }
                $response_array = array('status' => 'success', 'message' => 'Success', 'status_code' => 200);
                $response_code = 200;  
               
            }else{
                $response_array = array('status' => false, 'message' => 'Invalid Salon Id', 'status_code' => 401);
                $response_code = 401;
                goto response; 
            }
           response:
           $this->response($response_array, $response_code);
    }

    public function updateStaffSettingsWs(){
        if(isset($_POST['salon_id']) && $_POST['salon_id']!=''){
                $salon_id = $_POST['salon_id'];
                if(!empty($_POST['staffservicetype'])){
                   $staffservicetype = $_POST['staffservicetype'];
                }else{
                    $staffservicetype = "";
                }
        
                if(!empty($_POST['staffleaderboardtype'])){
                   $staffleaderboardtype = $_POST['staffleaderboardtype'];
                }else{
                    $staffleaderboardtype = "";
                }
                $updatearray['staff_service_types'] = $staffservicetype;
                $updatearray['staff_leaderboard_type'] = $staffleaderboardtype;

                $this->db->where('salon_id',$_POST['salon_id']);
                $this->db->update('mill_all_sdk_config_details',$updatearray);
                 $response_array = array('status' => 'success', 'message' => 'Success', 'status_code' => 200);
                 $response_code = 200;  
                       
                }else{
                        $response_array = array('status' => false, 'message' => 'Invalid Salon Id', 'status_code' => 401);
                        $response_code = 401;
                        goto response; 
                    }
                   response:
                $this->response($response_array, $response_code);
    }

    public function getRpctDetailsWs(){
           if(isset($_POST['rpct_type']) && $_POST['rpct_type']!=''){
                $rpct_type = $_POST['rpct_type'];
                $response_array['rpctdetails'] = $this->Common_model->getRpctTypeBy($rpct_type);
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