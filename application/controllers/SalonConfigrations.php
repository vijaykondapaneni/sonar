<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class SalonConfigrations extends CI_Controller
{   
    // Define constant as per value;
    CONST INSERTED = 0;
    /**
    AUTHOR: Subbu
    DESCRIPTION: THIS CLASS IS FOR Creating For Salon Configrations
    **/
    
    public $data = array();
     
    function __construct()
    {
        parent::__construct();
        
        $this->load->model('Common_model');
        $this->load->model('User');
        $this->load->library('template');

        if(isset($_GET['signature']) && isset($_GET['timestamp'])){
            $signature = base64_decode($_GET['signature']);
            $timestamp = $_GET['timestamp'];
            //$server_url = 'http://67.43.5.76/~newserver/reports/index.php/';
            $server_url = MAIN_SERVER_URL;
            $service_url = 'Users/Login';
            $checkauthuser = $this->Common_model->checkAuthUser($signature,$timestamp,$server_url,$service_url);
            if(!$checkauthuser){
                if(!$this->session->userdata('isUserLoggedIn')){
                    redirect(site_url('users/login'));
                }
            }else{

            }
        }else{
            if(!$this->session->userdata('isUserLoggedIn')){
                redirect(site_url('users/login'));
            }
        }
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

    public function Config($salon_id=''){
        $this->data['allsalons'] = $this->Common_model->getAllSalons($salon_id);
        $this->data['allservices'] = $this->Common_model->getAllServiceTypes();
        $this->data['allleaderboardtypes'] = $this->Common_model->getAllLeaderBoardTypes();
        $this->data['allrpcts'] = $this->Common_model->getAllRpcts();
        // staff side
        $this->data['allstaffservices'] = $this->Common_model->getAllStaffServiceTypes();
        $this->data['allstaffleaderboardtypes'] = $this->Common_model->getAllStaffLeaderBoardTypes();
        $this->data['title'] = 'All Salons';
        $this->data['user'] = $this->User->getRows(array('id'=>$this->session->userdata('userId')));
        //load the view

        $this->data['body'] = $this->load->view('salonconfig', $this->data , TRUE);
        $this->template->load('default',$this->data);
    }
    
    public function updateconfig(){
        if(!empty($_GET['servicetype'])){
           $servicetype = implode($_GET['servicetype'],",");
        }else{
            $servicetype = "";
        }

        if(!empty($_GET['leaderboardtype'])){
           $leaderboardtype = implode($_GET['leaderboardtype'],",");
        }else{
            $leaderboardtype = "";
        }
        $updatearray['service_types'] = $servicetype;
        $updatearray['leaderboard_type'] = $leaderboardtype;
        $updatearray['rpct_type'] = $_GET['rpcttype'];

        $this->db->where('salon_id',$_GET['salon_id']);
        $this->db->update('mill_all_sdk_config_details',$updatearray);
        pa('Successfully Updates','Successfully Updates');
        $signature = '';
        $timestamp = '';
        if(isset($_GET['signature']) && isset($_GET['timestamp'])){
          $signature = $_GET['signature'];
          $timestamp = $_GET['timestamp'];
        } 

    ?>


    <meta http-equiv="refresh" content="2;url=<?php echo base_url()?>/index.php/SalonConfigrations/Config?signature=<?php echo $_GET['signature']?>&timestamp=<?php echo $_GET['timestamp']?>" />
    <?php
   }

   public function updatestaffconfig(){
       
        if(!empty($_GET['staffservicetype'])){
           $staffservicetype = implode($_GET['staffservicetype'],",");
        }else{
            $staffservicetype = "";
        }
        
        if(!empty($_GET['staffleaderboardtype'])){
           $staffleaderboardtype = implode($_GET['staffleaderboardtype'],",");
        }else{
            $staffleaderboardtype = "";
        }
        $updatearray['staff_service_types'] = $staffservicetype;
        $updatearray['staff_leaderboard_type'] = $staffleaderboardtype;

        $this->db->where('salon_id',$_GET['salon_id']);
        $this->db->update('mill_all_sdk_config_details',$updatearray);
        pa('Successfully Updates','Successfully Updates');
        $signature = '';
        $timestamp = '';
        if(isset($_GET['signature']) && isset($_GET['timestamp'])){
          $signature = $_GET['signature'];
          $timestamp = $_GET['timestamp'];
        }
    ?>
    <meta http-equiv="refresh" content="2;url=<?php echo base_url()?>/index.php/SalonConfigrations/Config?signature=<?php echo $_GET['signature']?>&timestamp=<?php echo $_GET['timestamp']?>" />
    <?php
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
      
 
 }       