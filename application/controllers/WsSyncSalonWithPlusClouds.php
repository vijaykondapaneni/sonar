<?php
//error_reporting('0');
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class WsSyncSalonWithPlusClouds extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			//header('Content-Type: application/json');
			$this->load->library('session');
			$this->load->library('form_validation');
			$this->load->library('pagination');
			$this->load->helper(array('form', 'url'));
			$this->load->model('Common_model');
			$this->load->database();
		}
		
		function index() {
			// wont do anything

		}
		function setWsSyncSalonWithPlusClouds($salon_id="") {

			$this->load->model('Common_model');
			$getAllSalons = $this->Common_model->getAllSalons($salon_id);
			//pa($getAllSalons,'',false);
		      if(isset($getAllSalons["mill_salons"]) && !empty($getAllSalons["mill_salons"]))
               {
                foreach($getAllSalons["mill_salons"] as $salonsData)
                {	
					//pa($salonsData);
					$log['AccountNo'] = $salonsData['salon_account_id'];
                    $log['salon_id'] = $salonsData['salon_id'];
                    $log['StartingTime'] = date('Y-m-d H:i:s');
                    $log['whichCron'] = 'WsSyncSalonWithPlusClouds';
                    $log['CronUrl'] = MAIN_SERVER_URL.'WsSyncSalonWithPlusClouds/setWsSyncSalonWithPlusClouds/'.$salon_id;
                   // $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $log['CronType'] = 0;
                    $log['id'] = 0;
                    $log_id = $this->Common_model->saveMillCronLogs($log);
					$salon_id = $salonsData["salon_id"];
					$salonname = $salonsData["salon_name"];
					$salonstartdayofweek = $salonsData["salon_start_day_of_week"];
					$millenniumenabled = $salonsData["millennium_enabled"];
					$serviceretailreportsenabled = $salonsData["service_retail_reports_enabled"];
					$teamcommission = $salonsData["team_commission"];
					$tempSalonArr = array();
					$tempSalonArr["salon_id"] = $salon_id;
		            $ch = curl_init();
		            curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/getSalonInfoFromSalonId");
		            // for local server
		            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		            // close
		            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		            curl_setopt($ch, CURLOPT_POST, 1);
		            curl_setopt($ch, CURLOPT_POSTFIELDS, $tempSalonArr);
		            $salonResult=curl_exec($ch);
		            $result = json_decode($salonResult,true);
		            //pa($result,'',true);   
		            $data=  json_decode($salonResult,true);
		            $salon_name = $data['salon_info']['salon_name'].PHP_EOL;
		            $salon_id = $data['salon_info']['salon_id'].PHP_EOL;
		            $salon_start_day_of_week = $data['salon_info']['salon_start_day_of_week'].PHP_EOL;
		            $millennium_enabled = $data['salon_info']['millennium_enabled'].PHP_EOL;
		            $team_commission = $data['salon_info']['team_commission'].PHP_EOL;
		            $email_push = $data['salon_info']['email_push'].PHP_EOL;
		            $texting_enabled = $data['salon_info']['texting_enabled'].PHP_EOL;
		            $put_to_sdk = $data['salon_info']['put_to_sdk'].PHP_EOL;
		            //print_r($millennium_enabled);
		            //exit;
		            $service_retail_reports_enabled = $data['salon_info']['service_retail_reports_enabled'].PHP_EOL;
             
		           if(!empty($data)){
		          		if(trim($salon_name) === trim($salonname) 
		          			&& trim($salon_start_day_of_week) === trim($salonstartdayofweek)
		          			&& trim($millennium_enabled) === trim($salonsData["millennium_enabled"])
		          			&& trim($service_retail_reports_enabled) === trim($salonsData["service_retail_reports_enabled"])
		          			&& trim($team_commission) === trim($salonsData["team_commission"])
		          			&& trim($email_push) === trim($salonsData["email_push"])
		          			&& trim($texting_enabled) === trim($salonsData["texting_enabled"])
		          			&& trim($put_to_sdk) === trim($salonsData["put_to_sdk"]))
								{
									//continue; //SAME DATA FOUND, SO CONTINUe with the loop
									pa('No Updates');
								}	
								else
								{		
								  $millconfig_data = array(
								  	  'salon_name' => $data['salon_info']['salon_name'],
								  	  'salon_start_day_of_week ' => $data['salon_info']['salon_start_day_of_week'],
								  	  'millennium_enabled' => $data['salon_info']['service_retail_reports_enabled'],
								  	  'service_retail_reports_enabled' => $data['salon_info']['service_retail_reports_enabled'],
								  	  'team_commission' => $data['salon_info']['team_commission'],
								  	  'email_push' => $data['salon_info']['email_push'],
								  	  'texting_enabled' => $data['salon_info']['texting_enabled'],
								  	  'put_to_sdk' => $data['salon_info']['put_to_sdk'],
								  	 );
								  $this->db->where('salon_id ',$salonsData["salon_id"]);
              					  $res = $this->db->update(MILL_ALL_SDK_CONFIG_DETAILS, $millconfig_data);
              					  pa($this->db->last_query());
              					  pa('Successfully Updated'.'--'.$salonsData['salon_id']);
								}
		        	}
          		$log['id'] = $log_id;
	            $log_id = $this->Common_model->saveMillCronLogs($log);
			   }
			
		}

	}		


}	