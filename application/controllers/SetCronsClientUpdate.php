<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class SetCronsClientUpdate extends CI_Controller
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
    CONST FILE_PATH = '/home/ec2-user/reports1cronscripts/';
    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    /**
     *Getting past appointments 
     * @param type $salon_id
     */
    public function setCronsInFilesClientUpdate($salon_code=""){
         $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($salon_code);
            if($getConfigDetails->num_rows()>0){
                foreach($getConfigDetails->result_array() as $configDetails){
                    pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
                    $account_no = $configDetails['salon_account_id'];
                    $salon_id = $configDetails['salon_id'];
                    //$encoded_code = salonWebappCloudEn($account_no);

                    // for employee listing
                    $file = fopen(self::FILE_PATH.'WsUpdateClientsWeek.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/reports1/index.php Wsimport_millsdk_clients_update getClientsLastModified '. $account_no.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                    //WsUpdateClientsOneday
                    $file = fopen(self::FILE_PATH.'WsUpdateClientsOneday.sh',"a");
                    $string = '/usr/bin/php -q  /var/www/html/reports1/index.php Wsimport_millsdk_clients_update getClientsLastVisitForCurrentDay '. $account_no.' > /dev/null 2>&1'."\n";
                    echo fwrite($file,$string);
                } 
            }else{
                pa('Invalid Salon...Please Recheck');
            }
        }   
  
}      