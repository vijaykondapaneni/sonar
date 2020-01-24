<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}

class Wsimport_missed_crondata extends CI_Controller {

	/**
       AUTHOR: Subbu
       DESCRIPTION: THIS CLASS IS FOR IMPORT PAST APPOINTMENTS
	**/
	CONST INSERTED = 0;
    CONST UPDATED = 1;
    public $pemFilePath;
    public $salonMillIp;
    public $salonMillGuid;
    public $salonMillUsername;
    public $salonMillPassword;
    public $salonMillSdkUrl;
    public $startDate;
    public $endDate;
    public $millResponseXml;
    public $salonAccountId;
    public $millClientResponseXml;
    public $millResponseSessionId;

   /**
    *
    */
   function __construct() {
			parent::__construct();
			$this->load->model('Common_model');
        }
        
    /**
     * Default Index Fn
     */    
	public function index(){ print "Test";}

    /**
     * 
     * @param type $startDate
     * @param type $endDate
     * @param type $account_no
     */
    function getCrons($account_no="")
		{
			$missedcrons = $this->Common_model->getMissedCrons($account_no);
			foreach ($missedcrons as $key => $value) {
				print $url =  $value['CronUrl'];
				$this->Common_model->insertDataByUsingCUrl($url);
			}
		}
 

 }