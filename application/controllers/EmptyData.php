<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class EmptyData extends CI_Controller
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

    public function emptyCronLog(){
        $emptycronlog = $this->Common_model->emptyCronLog();
    }

    
 
 }       