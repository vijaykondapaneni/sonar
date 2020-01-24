<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}

class Wsimport_get_clients_by_date_created extends CI_Controller {

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
			$this->load->model('Twoyearclientsimport_model');
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

    function GetClientsByDateCreatedForCurrentDay($account_no="")
	{

		$currentDate = date("Y-m-d");
		// GET SALON CONFIG DETAILS
        $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no);
		if($getConfigDetails->num_rows()>0)
		{
			foreach($getConfigDetails->result_array() as $configDetails)
			{
				pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
                
				//LOG IN DETAILS FOR MILLENIUM SDK	
                $this->salonAccountId = $configDetails['salon_account_id'];
                $this->pemFilePath	 =	base_url()."salonevolve.pem";
                $this->salonMillIp	 =	$configDetails['mill_ip_address'];
                $this->salonMillGuid =	$configDetails['mill_guid'];
                $this->salonMillUsername =	$configDetails['mill_username'];
                $this->salonMillPassword =	$configDetails['mill_password'];
                $this->salonMillSdkUrl = $configDetails['mill_url'];
                $this->startDate = (!empty($startDate)) ? $startDate : date("Y-m-d");
                $this->endDate   = (!empty($endDate)) ? $endDate : date("Y-m-d");
                // Database Log
                $log['AccountNo'] = $configDetails['salon_account_id'];
                $log['salon_id'] = $configDetails['salon_id'];
                $log['StartingTime'] = date('Y-m-d H:i:s');
                $log['whichCron'] = 'GetClientsByDateCreatedForCurrentDay';
                $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $log['CronType'] = 0;
                $log['id'] = 0;
                $log_id = $this->Common_model->saveMillCronLogs($log);
				$clientsIds = array();
                //MILLENIUM SDK REQUEST FOR SOAP CALL	
                $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                $millMethodParams = array('StartDate' => $currentDate,'EndDate' => $currentDate,'IncludeDeleted'=>0);
                
                $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                
                $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                
                pa($this->millResponseSessionId,'session','');
                
                $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetClientsByDateCreated',$millMethodParams);
                
                $clientsIds = array();
				if(isset($this->millResponseXml['Clients']))
				{
					
					$clientsCount = count($this->millResponseXml['Clients']);
					pa('',"The total Clients in SDK".$clientsCount,false); //DEBUGMSG
					foreach($this->millResponseXml['Clients'] as $mClients) //CONVERTED Clients XML TO ARRAY
					{
						
						pa('',"Clients Details--".$mClients["iid"]); //DEBUGMSG
						$client_id = $mClients["iid"];
						if(!in_array($client_id,$clientsIds) && $client_id != "-999")
						//CHECKING DUPLICATION OF CLIENT ID AND 
						 {

						 	$millMethodParams = array('ClientId' => $client_id);
                            $this->millClientResponseXml = $this->nusoap_library->getMillMethodCall('GetClient',$millMethodParams);
                            // pa($this->millClientResponseXml,'');
                            $whereConditions =  array('ClientId' => $this->millClientResponseXml['GetClientResult']['Id'],'AccountNo' => $this->salonAccountId);
													//$query = $this->db->get_where(MILL_CLIENTS_TABLE,$comparearray);
                            // pa($this->millClientResponseXml,"Mill Data");
							$arrClientsdata = $this->Twoyearclientsimport_model
							          ->compareMillTwoYearsClients($whereConditions)
							          ->row_array();
                           // pa($this->db->last_query(),"Last Query");

							pa($arrClientsdata,"DBdata");

							
							$firstVisitDate = (!empty($this->millClientResponseXml['GetClientResult']['FirstVisitDate'])) ? str_replace("T"," ",$this->millClientResponseXml['GetClientResult']['FirstVisitDate']) : '';

							$lastVisitDate = (!empty($this->millClientResponseXml['GetClientResult']['LastVisitDate'])) ? str_replace("T"," ",$this->millClientResponseXml['GetClientResult']['LastVisitDate']) : '';

							$optedInSms = (!empty($this->millClientResponseXml['GetClientResult']['ConfirmViaSMS'])&& $this->millClientResponseXml['GetClientResult']['ConfirmViaSMS']=='true') ? 1:2;

							$optedInEMail = (!empty($this->millClientResponseXml['GetClientResult']['ConfirmViaEmail'])&& $this->millClientResponseXml['GetClientResult']['ConfirmViaEmail']=='true') ? 1:2;

							$mobilePhoneNo = (!empty($this->millClientResponseXml['GetClientResult']['CellPhoneNumber'])) ? $this->millClientResponseXml['GetClientResult']['CellPhoneNumber']:0;

							$HasMemberships = (!empty($this->millClientResponseXml['GetClientResult']['HasMemberships'])&& $this->millClientResponseXml['GetClientResult']['HasMemberships']=='true') ? 1:0;


							   $comparearray = array();
							   $comparearray['Email'] = $this->millClientResponseXml['GetClientResult']['EmailAddress'];
							   $comparearray['Zip'] = $this->millClientResponseXml['GetClientResult']['ZipCode'];
							   $comparearray['Phone'] = $this->millClientResponseXml['GetClientResult']['HomeAreaCode'].$this->millClientResponseXml['GetClientResult']['HomePhoneNumber'];
							   $comparearray['Name'] = $this->millClientResponseXml['GetClientResult']['FirstName'].' '.$this->millClientResponseXml['GetClientResult']['LastName'];
							   $comparearray['Dob'] = $this->millClientResponseXml['GetClientResult']['Birthday'];
							   $comparearray['Sex'] = $this->millClientResponseXml['GetClientResult']['Sex'];
							   $comparearray['Mobile'] = $this->millClientResponseXml['GetClientResult']['CellPhoneNumber'];
							   //$comparearray['Mobile'] = '';
							   $comparearray['MobileAreaCode'] = $this->millClientResponseXml['GetClientResult']['CellAreaCode'];
							   $comparearray['BusinessAreaCode'] = $this->millClientResponseXml['GetClientResult']['BusinessAreaCode'];
							   $comparearray['BusinessPhoneNumber'] = $this->millClientResponseXml['GetClientResult']['BusinessPhoneNumber'];
							   $comparearray['clientFirstVistedDate'] = $firstVisitDate;
							   $comparearray['clientLastVistedDate'] = $lastVisitDate;
							   $comparearray['opted_in_email'] = $optedInEMail;
							   $comparearray['opted_in_sms'] = $optedInSms;
							   

							   $comparearray['HasMemberships'] = $HasMemberships;
								$comparearray['ReferralTypeId'] = $this->millClientResponseXml['GetClientResult']['ReferralTypeId'];
								$comparearray['ReferredByClientId'] = $this->millClientResponseXml['GetClientResult']['ReferredByClientId'];
								
								//$comparearray['numberOfVisits'] = $this->millClientResponseXml['GetClientResult']['NumberOfVisits'];
													
							 if(!empty($arrClientsdata))
		                        {
                                   $diff_array = array_diff_assoc($comparearray, $arrClientsdata);
                                   pa($diff_array,"Diff Array");
                                   if(empty($diff_array))
		                            {
		                                continue; //SAME DATA FOUND, SO CONTINUe with the loop
		                            }else{
		                            	$diff_array['ModifiedDate'] = date("Y-m-d H:i:s");
                                        $diff_array['SlcStatus'] = Wsimport_get_clients_by_date_created::UPDATED;

                                        $diff_array['IsProcessed']=0;
							   			$diff_array['IsProcessedDM']=0;


                                        $whereconditions = array();
										$whereconditions['ClientId'] = $this->millClientResponseXml['GetClientResult']['Id'];
										$whereconditions['AccountNo'] = $this->salonAccountId;
															//$this->db->update(MILL_CLIENTS_TABLE, $clients_data);

										if(!empty($comparearray['Email'])){
									 		$hasEmail = " with Email ".$comparearray['Email'];
									 	} else {
									 		$hasEmail = " without Email";
									 	}

									 	if(!empty($mobilePhoneNo)){
									 		$hasPhone = " with Phone ".$mobilePhoneNo;
									 	} else {
									 		$hasPhone = " without Phone";
									 	}

									 	$statusUpdate = "Updated Client Id ".$client_id.$hasEmail." and".$hasPhone." and Opted in email ".$optedInEMail." and ".$optedInSms." on ".date("Y-m-d H:i:s")."\n";
										$this->db->set('client_log', 'CONCAT(client_log,"'.$statusUpdate.'")', FALSE);

										$this->db->where('ClientId',$whereconditions['ClientId']);
										$this->db->where('AccountNo',$whereconditions['AccountNo']);
										$update_query =  $this->db->update(MILL_CLIENTS_TABLE, $diff_array);

										//$this->Twoyearclientsimport_model->updateMillTwoYearsClients($whereconditions,$diff_array);
		                            }	
		                        }else
								{
									$comparearray['ClientId'] =  $this->millClientResponseXml['GetClientResult']['Id'];
									$comparearray['AccountNo'] = $this->salonAccountId;
									$comparearray['CreatedDate'] = date("Y-m-d H:i:s");
									$comparearray['ModifiedDate'] = date("Y-m-d H:i:s");

									$comparearray['IsProcessed']=0;
							   		$comparearray['IsProcessedDM']=0;
									
									$comparearray['SlcStatus'] = Wsimport_get_clients_by_date_created::INSERTED;
                                   
									//$res = $this->Twoyearclientsimport_model->insertMillTwoYearsClients($comparearray);

									if(!empty($comparearray['Email'])){
								 		$hasEmail = " with Email ".$comparearray['Email'];
								 	} else {
								 		$hasEmail = " without Email ";
								 	}

								 	if(!empty($mobilePhoneNo)){
								 		$hasPhone = " with Phone ".$mobilePhoneNo;
								 	} else {
								 		$hasPhone = " without Phone ";
								 	}

								 	$statusUpdate = "Inserted Client Id ".$client_id.$hasEmail." and ".$hasPhone." and Opted in email ".$optedInEMail." and ".$optedInSms." on ".date("Y-m-d H:i:s")."\n";
									$this->db->set('client_log', 'CONCAT(client_log,"'.$statusUpdate.'")', FALSE);
						             
						            $insert_query = $this->db->insert(MILL_CLIENTS_TABLE, $comparearray);			
									$clients_id = $this->db->insert_id();
									pa('',"inserted data ---ID=".$clients_id);
								}

						 }	
					    }	
				}else{
					echo "No data found in MILL SDK";
				}
				$log['id'] = $log_id;
                $log_id = $this->Common_model->saveMillCronLogs($log);
			}
		}
	}

    function GetClientsByDateCreated($startDate="",$endDate="",$account_no="")
	{
		// GET SALON CONFIG DETAILS
        $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no);
		if($getConfigDetails->num_rows()>0)
		{
			foreach($getConfigDetails->result_array() as $configDetails)
			{
				//pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
                
				//LOG IN DETAILS FOR MILLENIUM SDK	
                $this->salonAccountId = $configDetails['salon_account_id'];
                $this->pemFilePath	 =	base_url()."salonevolve.pem";
                $this->salonMillIp	 =	$configDetails['mill_ip_address'];
                $this->salonMillGuid =	$configDetails['mill_guid'];
                $this->salonMillUsername =	$configDetails['mill_username'];
                $this->salonMillPassword =	$configDetails['mill_password'];
                $this->salonMillSdkUrl = $configDetails['mill_url'];
                $this->startDate = (!empty($startDate)) ? $startDate : date("Y-m-d");
                $this->endDate   = (!empty($endDate)) ? $endDate : date("Y-m-d");
                // Database Log
                $log['AccountNo'] = $configDetails['salon_account_id'];
                $log['salon_id'] = $configDetails['salon_id'];
                $log['StartingTime'] = date('Y-m-d H:i:s');
                $log['whichCron'] = 'GetClientsByDateCreated';
                $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $log['CronType'] = 0;
                $log['id'] = 0;
                $log_id = $this->Common_model->saveMillCronLogs($log);
				$clientsIds = array();
                //MILLENIUM SDK REQUEST FOR SOAP CALL	
                $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                $millMethodParams = array('StartDate' => $this->startDate,'EndDate' => $this->endDate,'IncludeDeleted'=>0);
                
                $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                
                $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                
                //pa($this->millResponseSessionId,'session','');
                if(empty($this->millResponseSessionId)){
                    echo "SDK is not working";exit;
                }
                
                $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetClientsByDateCreated',$millMethodParams);
                
                //print_r($this->millResponseXml['Clients']);exit;
                
                $clientsIds = array();
				if(isset($this->millResponseXml['Clients']) && !empty($this->millResponseXml['Clients']))
				{
					
					//$clientsCount = count($this->millResponseXml['Clients']);
					
					//pa('',"The total Clients in SDK".$clientsCount,false); //DEBUGMSG
					foreach($this->millResponseXml['Clients'] as $mClients) //CONVERTED Clients XML TO ARRAY
					{
						
						//pa('',"Clients Details--".$mClients["iid"]); //DEBUGMSG
						$client_id = $mClients["iid"];
						if(!in_array($client_id,$clientsIds) && $client_id != "-999")
						//CHECKING DUPLICATION OF CLIENT ID AND 
						 {

						 	$millMethodParams = array('ClientId' => $client_id);
                            $this->millClientResponseXml = $this->nusoap_library->getMillMethodCall('GetClient',$millMethodParams);
                            // pa($this->millClientResponseXml,'');
                            $whereConditions =  array('ClientId' => $this->millClientResponseXml['GetClientResult']['Id'],'AccountNo' => $this->salonAccountId);
													//$query = $this->db->get_where(MILL_CLIENTS_TABLE,$comparearray);
                            // pa($this->millClientResponseXml,"Mill Data");
							$arrClientsdata = $this->Twoyearclientsimport_model
							          ->compareMillTwoYearsClients($whereConditions)
							          ->row_array();
                           // pa($this->db->last_query(),"Last Query");

							//pa($arrClientsdata,"DBdata");

							
							$firstVisitDate = (!empty($this->millClientResponseXml['GetClientResult']['FirstVisitDate'])) ? str_replace("T"," ",$this->millClientResponseXml['GetClientResult']['FirstVisitDate']) : '';

							$lastVisitDate = (!empty($this->millClientResponseXml['GetClientResult']['LastVisitDate'])) ? str_replace("T"," ",$this->millClientResponseXml['GetClientResult']['LastVisitDate']) : '';

							$optedInSms = (!empty($this->millClientResponseXml['GetClientResult']['ConfirmViaSMS'])&& $this->millClientResponseXml['GetClientResult']['ConfirmViaSMS']=='true') ? 1:2;

							$optedInEMail = (!empty($this->millClientResponseXml['GetClientResult']['ConfirmViaEmail'])&& $this->millClientResponseXml['GetClientResult']['ConfirmViaEmail']=='true') ? 1:2;

							$mobilePhoneNo = (!empty($this->millClientResponseXml['GetClientResult']['CellPhoneNumber'])) ? $this->millClientResponseXml['GetClientResult']['CellPhoneNumber']:0;

							$HasMemberships = (!empty($this->millClientResponseXml['GetClientResult']['HasMemberships'])&& $this->millClientResponseXml['GetClientResult']['HasMemberships']=='true') ? 1:0;


							   $comparearray = array();
							   $comparearray['Email'] = $this->millClientResponseXml['GetClientResult']['EmailAddress'];
							   $comparearray['Zip'] = $this->millClientResponseXml['GetClientResult']['ZipCode'];
							   $comparearray['Phone'] = $this->millClientResponseXml['GetClientResult']['HomeAreaCode'].$this->millClientResponseXml['GetClientResult']['HomePhoneNumber'];
							   $comparearray['Name'] = $this->millClientResponseXml['GetClientResult']['FirstName'].' '.$this->millClientResponseXml['GetClientResult']['LastName'];
							   $comparearray['Dob'] = $this->millClientResponseXml['GetClientResult']['Birthday'];
							   $comparearray['Sex'] = $this->millClientResponseXml['GetClientResult']['Sex'];
							   $comparearray['Mobile'] = $this->millClientResponseXml['GetClientResult']['CellPhoneNumber'];
							   //$comparearray['Mobile'] = '';
							   $comparearray['MobileAreaCode'] = $this->millClientResponseXml['GetClientResult']['CellAreaCode'];
							   $comparearray['BusinessAreaCode'] = $this->millClientResponseXml['GetClientResult']['BusinessAreaCode'];
							   $comparearray['BusinessPhoneNumber'] = $this->millClientResponseXml['GetClientResult']['BusinessPhoneNumber'];
							   $comparearray['clientFirstVistedDate'] = $firstVisitDate;
							   $comparearray['clientLastVistedDate'] = $lastVisitDate;
							   $comparearray['opted_in_email'] = $optedInEMail;
							   $comparearray['opted_in_sms'] = $optedInSms;
							   

							   $comparearray['HasMemberships'] = $HasMemberships;
								$comparearray['ReferralTypeId'] = $this->millClientResponseXml['GetClientResult']['ReferralTypeId'];
								$comparearray['ReferredByClientId'] = $this->millClientResponseXml['GetClientResult']['ReferredByClientId'];
								
								//$comparearray['numberOfVisits'] = $this->millClientResponseXml['GetClientResult']['NumberOfVisits'];
													
							 if(!empty($arrClientsdata))
		                        {
                                   $diff_array = array_diff_assoc($comparearray, $arrClientsdata);
                                   //pa($diff_array,"Diff Array");
                                   if(empty($diff_array))
		                            {
		                                continue; //SAME DATA FOUND, SO CONTINUe with the loop
		                            }else{
		                            	$diff_array['ModifiedDate'] = date("Y-m-d H:i:s");
                                        $diff_array['SlcStatus'] = Wsimport_get_clients_by_date_created::UPDATED;

                                        $diff_array['IsProcessed']=0;
							   			$diff_array['IsProcessedDM']=0;


                                        $whereconditions = array();
										$whereconditions['ClientId'] = $this->millClientResponseXml['GetClientResult']['Id'];
										$whereconditions['AccountNo'] = $this->salonAccountId;
															//$this->db->update(MILL_CLIENTS_TABLE, $clients_data);

										if(!empty($comparearray['Email'])){
									 		$hasEmail = " with Email ".$comparearray['Email'];
									 	} else {
									 		$hasEmail = " without Email";
									 	}

									 	if(!empty($mobilePhoneNo)){
									 		$hasPhone = " with Phone ".$mobilePhoneNo;
									 	} else {
									 		$hasPhone = " without Phone";
									 	}

									 	$statusUpdate = "Updated Client Id ".$client_id.$hasEmail." and".$hasPhone." and Opted in email ".$optedInEMail." and ".$optedInSms." on ".date("Y-m-d H:i:s")."\n";
										$this->db->set('client_log', 'CONCAT(client_log,"'.$statusUpdate.'")', FALSE);

										$this->db->where('ClientId',$whereconditions['ClientId']);
										$this->db->where('AccountNo',$whereconditions['AccountNo']);
										$update_query =  $this->db->update(MILL_CLIENTS_TABLE, $diff_array);

										//$this->Twoyearclientsimport_model->updateMillTwoYearsClients($whereconditions,$diff_array);
		                            }	
		                        }else
								{
									$comparearray['ClientId'] =  $this->millClientResponseXml['GetClientResult']['Id'];
									$comparearray['AccountNo'] = $this->salonAccountId;
									$comparearray['CreatedDate'] = date("Y-m-d H:i:s");
									$comparearray['ModifiedDate'] = date("Y-m-d H:i:s");

									$comparearray['IsProcessed']=0;
							   		$comparearray['IsProcessedDM']=0;
									
									$comparearray['SlcStatus'] = Wsimport_get_clients_by_date_created::INSERTED;
                                   
									//$res = $this->Twoyearclientsimport_model->insertMillTwoYearsClients($comparearray);

									if(!empty($comparearray['Email'])){
								 		$hasEmail = " with Email ".$comparearray['Email'];
								 	} else {
								 		$hasEmail = " without Email ";
								 	}

								 	if(!empty($mobilePhoneNo)){
								 		$hasPhone = " with Phone ".$mobilePhoneNo;
								 	} else {
								 		$hasPhone = " without Phone ";
								 	}

								 	$statusUpdate = "Inserted Client Id ".$client_id.$hasEmail." and ".$hasPhone." and Opted in email ".$optedInEMail." and ".$optedInSms." on ".date("Y-m-d H:i:s")."\n";
									$this->db->set('client_log', 'CONCAT(client_log,"'.$statusUpdate.'")', FALSE);
						             
						            $insert_query = $this->db->insert(MILL_CLIENTS_TABLE, $comparearray);			
									$clients_id = $this->db->insert_id();
									//pa('',"inserted data ---ID=".$clients_id);
								}

						 }	
					}
					$logOff = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('LogOff',$millloginDetails);
					
					$this->exporting_mill_clients($account_no);
					echo "Success!";
				}else{
					echo "No new clients found in millennium."; 
					//echo "No data";
				}
				$log['id'] = $log_id;
                //$log_id = $this->Common_model->saveMillCronLogs($log);
			}
		} else {
		    echo "SDK not integrated.";//NOT INTEGRATED OR NO CONFIG DETAILS FOUND
		}
	}
	
	function GetClientsByDateCreatedByAjaxCall()
	{
	    //echo 1;exit;
	    if(!empty($_POST['start_date']) && !empty($_POST['end_date']) && !empty($_POST['salon_code'])){
	        $account_no = $_POST['salon_code'];
	        $startDate = $_POST['start_date'];
	        $endDate = $_POST['end_date'];
	        
	        // GET SALON CONFIG DETAILS
            $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no);
    		if($getConfigDetails->num_rows()>0)
    		{
    			foreach($getConfigDetails->result_array() as $configDetails)
    			{
    				//pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
                    
    				//LOG IN DETAILS FOR MILLENIUM SDK	
                    $this->salonAccountId = $configDetails['salon_account_id'];
                    $this->pemFilePath	 =	base_url()."salonevolve.pem";
                    $this->salonMillIp	 =	$configDetails['mill_ip_address'];
                    $this->salonMillGuid =	$configDetails['mill_guid'];
                    $this->salonMillUsername =	$configDetails['mill_username'];
                    $this->salonMillPassword =	$configDetails['mill_password'];
                    $this->salonMillSdkUrl = $configDetails['mill_url'];
                    $this->startDate = (!empty($startDate)) ? $startDate : date("Y-m-d");
                    $this->endDate   = (!empty($endDate)) ? $endDate : date("Y-m-d");
                    // Database Log
                    $log['AccountNo'] = $configDetails['salon_account_id'];
                    $log['salon_id'] = $configDetails['salon_id'];
                    $log['StartingTime'] = date('Y-m-d H:i:s');
                    $log['whichCron'] = 'GetClientsByDateCreated';
                    $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $log['CronType'] = 0;
                    $log['id'] = 0;
                    $log_id = $this->Common_model->saveMillCronLogs($log);
    				$clientsIds = array();
                    //MILLENIUM SDK REQUEST FOR SOAP CALL	
                    $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                    $millMethodParams = array('StartDate' => $this->startDate,'EndDate' => $this->endDate,'IncludeDeleted'=>0);
                    
                    $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                    
                    $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                    
                    //pa($this->millResponseSessionId,'session','');
                    if(empty($this->millResponseSessionId)){
                        echo 3;exit;
                    }
                    
                    $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetClientsByDateCreated',$millMethodParams);
                    
                    $clientsIds = array();
    				if(isset($this->millResponseXml['Clients']))
    				{
    					
    					$clientsCount = count($this->millResponseXml['Clients']);
    					//pa('',"The total Clients in SDK".$clientsCount,false); //DEBUGMSG
    					foreach($this->millResponseXml['Clients'] as $mClients) //CONVERTED Clients XML TO ARRAY
    					{
    						
    						//pa('',"Clients Details--".$mClients["iid"]); //DEBUGMSG
    						$client_id = $mClients["iid"];
    						if(!in_array($client_id,$clientsIds) && $client_id != "-999")
    						//CHECKING DUPLICATION OF CLIENT ID AND 
    						 {
    
    						 	$millMethodParams = array('ClientId' => $client_id);
                                $this->millClientResponseXml = $this->nusoap_library->getMillMethodCall('GetClient',$millMethodParams);
                                // pa($this->millClientResponseXml,'');
                                $whereConditions =  array('ClientId' => $this->millClientResponseXml['GetClientResult']['Id'],'AccountNo' => $this->salonAccountId);
    													//$query = $this->db->get_where(MILL_CLIENTS_TABLE,$comparearray);
                                // pa($this->millClientResponseXml,"Mill Data");
    							$arrClientsdata = $this->Twoyearclientsimport_model
    							          ->compareMillTwoYearsClients($whereConditions)
    							          ->row_array();
                               // pa($this->db->last_query(),"Last Query");
    
    							//pa($arrClientsdata,"DBdata");
    
    							
    							$firstVisitDate = (!empty($this->millClientResponseXml['GetClientResult']['FirstVisitDate'])) ? str_replace("T"," ",$this->millClientResponseXml['GetClientResult']['FirstVisitDate']) : '';
    
    							$lastVisitDate = (!empty($this->millClientResponseXml['GetClientResult']['LastVisitDate'])) ? str_replace("T"," ",$this->millClientResponseXml['GetClientResult']['LastVisitDate']) : '';
    
    							$optedInSms = (!empty($this->millClientResponseXml['GetClientResult']['ConfirmViaSMS'])&& $this->millClientResponseXml['GetClientResult']['ConfirmViaSMS']=='true') ? 1:2;
    
    							$optedInEMail = (!empty($this->millClientResponseXml['GetClientResult']['ConfirmViaEmail'])&& $this->millClientResponseXml['GetClientResult']['ConfirmViaEmail']=='true') ? 1:2;
    
    							$mobilePhoneNo = (!empty($this->millClientResponseXml['GetClientResult']['CellPhoneNumber'])) ? $this->millClientResponseXml['GetClientResult']['CellPhoneNumber']:0;
    
    							$HasMemberships = (!empty($this->millClientResponseXml['GetClientResult']['HasMemberships'])&& $this->millClientResponseXml['GetClientResult']['HasMemberships']=='true') ? 1:0;
    
    
    							   $comparearray = array();
    							   $comparearray['Email'] = $this->millClientResponseXml['GetClientResult']['EmailAddress'];
    							   $comparearray['Zip'] = $this->millClientResponseXml['GetClientResult']['ZipCode'];
    							   $comparearray['Phone'] = $this->millClientResponseXml['GetClientResult']['HomeAreaCode'].$this->millClientResponseXml['GetClientResult']['HomePhoneNumber'];
    							   $comparearray['Name'] = $this->millClientResponseXml['GetClientResult']['FirstName'].' '.$this->millClientResponseXml['GetClientResult']['LastName'];
    							   $comparearray['Dob'] = $this->millClientResponseXml['GetClientResult']['Birthday'];
    							   $comparearray['Sex'] = $this->millClientResponseXml['GetClientResult']['Sex'];
    							   $comparearray['Mobile'] = $this->millClientResponseXml['GetClientResult']['CellPhoneNumber'];
    							   //$comparearray['Mobile'] = '';
    							   $comparearray['MobileAreaCode'] = $this->millClientResponseXml['GetClientResult']['CellAreaCode'];
    							   $comparearray['BusinessAreaCode'] = $this->millClientResponseXml['GetClientResult']['BusinessAreaCode'];
    							   $comparearray['BusinessPhoneNumber'] = $this->millClientResponseXml['GetClientResult']['BusinessPhoneNumber'];
    							   $comparearray['clientFirstVistedDate'] = $firstVisitDate;
    							   $comparearray['clientLastVistedDate'] = $lastVisitDate;
    							   $comparearray['opted_in_email'] = $optedInEMail;
    							   $comparearray['opted_in_sms'] = $optedInSms;
    							   
    
    							   $comparearray['HasMemberships'] = $HasMemberships;
    								$comparearray['ReferralTypeId'] = $this->millClientResponseXml['GetClientResult']['ReferralTypeId'];
    								$comparearray['ReferredByClientId'] = $this->millClientResponseXml['GetClientResult']['ReferredByClientId'];
    								
    								//$comparearray['numberOfVisits'] = $this->millClientResponseXml['GetClientResult']['NumberOfVisits'];
    													
    							 if(!empty($arrClientsdata))
    		                        {
                                       $diff_array = array_diff_assoc($comparearray, $arrClientsdata);
                                       //pa($diff_array,"Diff Array");
                                       if(empty($diff_array))
    		                            {
    		                                continue; //SAME DATA FOUND, SO CONTINUe with the loop
    		                            }else{
    		                            	$diff_array['ModifiedDate'] = date("Y-m-d H:i:s");
                                            $diff_array['SlcStatus'] = Wsimport_get_clients_by_date_created::UPDATED;
    
                                            $diff_array['IsProcessed']=0;
    							   			$diff_array['IsProcessedDM']=0;
    
    
                                            $whereconditions = array();
    										$whereconditions['ClientId'] = $this->millClientResponseXml['GetClientResult']['Id'];
    										$whereconditions['AccountNo'] = $this->salonAccountId;
    															//$this->db->update(MILL_CLIENTS_TABLE, $clients_data);
    
    										if(!empty($comparearray['Email'])){
    									 		$hasEmail = " with Email ".$comparearray['Email'];
    									 	} else {
    									 		$hasEmail = " without Email";
    									 	}
    
    									 	if(!empty($mobilePhoneNo)){
    									 		$hasPhone = " with Phone ".$mobilePhoneNo;
    									 	} else {
    									 		$hasPhone = " without Phone";
    									 	}
    
    									 	$statusUpdate = "Updated Client Id ".$client_id.$hasEmail." and".$hasPhone." and Opted in email ".$optedInEMail." and ".$optedInSms." on ".date("Y-m-d H:i:s")."\n";
    										$this->db->set('client_log', 'CONCAT(client_log,"'.$statusUpdate.'")', FALSE);
    
    										$this->db->where('ClientId',$whereconditions['ClientId']);
    										$this->db->where('AccountNo',$whereconditions['AccountNo']);
    										$update_query =  $this->db->update(MILL_CLIENTS_TABLE, $diff_array);
    
    										//$this->Twoyearclientsimport_model->updateMillTwoYearsClients($whereconditions,$diff_array);
    		                            }	
    		                        }else
    								{
    									$comparearray['ClientId'] =  $this->millClientResponseXml['GetClientResult']['Id'];
    									$comparearray['AccountNo'] = $this->salonAccountId;
    									$comparearray['CreatedDate'] = date("Y-m-d H:i:s");
    									$comparearray['ModifiedDate'] = date("Y-m-d H:i:s");
    
    									$comparearray['IsProcessed']=0;
    							   		$comparearray['IsProcessedDM']=0;
    									
    									$comparearray['SlcStatus'] = Wsimport_get_clients_by_date_created::INSERTED;
                                       
    									//$res = $this->Twoyearclientsimport_model->insertMillTwoYearsClients($comparearray);
    
    									if(!empty($comparearray['Email'])){
    								 		$hasEmail = " with Email ".$comparearray['Email'];
    								 	} else {
    								 		$hasEmail = " without Email ";
    								 	}
    
    								 	if(!empty($mobilePhoneNo)){
    								 		$hasPhone = " with Phone ".$mobilePhoneNo;
    								 	} else {
    								 		$hasPhone = " without Phone ";
    								 	}
    
    								 	$statusUpdate = "Inserted Client Id ".$client_id.$hasEmail." and ".$hasPhone." and Opted in email ".$optedInEMail." and ".$optedInSms." on ".date("Y-m-d H:i:s")."\n";
    									$this->db->set('client_log', 'CONCAT(client_log,"'.$statusUpdate.'")', FALSE);
    						             
    						            $insert_query = $this->db->insert(MILL_CLIENTS_TABLE, $comparearray);			
    									$clients_id = $this->db->insert_id();
    									//pa('',"inserted data ---ID=".$clients_id);
    								}
    
    						 }	
    					}
    					echo 1;
    				}else{
    					//echo "No data found in MILL SDK";
    					echo 0;
    				}
    				$log['id'] = $log_id;
                    $log_id = $this->Common_model->saveMillCronLogs($log);
    			}
    		} else {
    		    echo 2;//NOT INTEGRATED OR NO CONFIG DETAILS FOUND
    		}
	    }
		
	}
	
	function exporting_mill_clients($account_no) {
		ini_set('memory_limit',-1);
		ini_set('max_execution_time',600);
		
		$data = array();
		$ClientIds = array();
		//$account_no = $account_nos['AccountNo'];
		$this->db->limit(500);
		$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0, "ClientId!=" => ''));
		$get_appointments = $this->db->get(MILL_CLIENTS_TABLE);
		if($get_appointments->num_rows() > 0) {
			foreach($get_appointments->result_array() as $appointment) {
				$temp = array();
				$ClientIds[] = $appointment['ClientId']; 
				$temp['ClientId'] = $appointment['ClientId'];
				$temp['Email'] = $appointment['Email'];
				$temp['Zip'] = $appointment['Zip'];
				$temp['Phone'] = $appointment['Phone'];
				$temp['Name'] = $appointment['Name'];
				$temp['Dob'] = $appointment['Dob'];
				$temp['GiftCardBalance'] = $appointment['GiftCardBalance'];
				$temp['LoyaltyPoints'] = $appointment['LoyaltyPoints'];
				$temp['LastReceiptAmount'] = $appointment['LastReceiptAmount'];
				$temp['Sex'] = $appointment['Sex'];
				$temp['Mobile'] = $appointment['Mobile'];
				$temp['MobileAreaCode'] = $appointment['MobileAreaCode'];
				$temp['BusinessPhoneNumber'] = $appointment['BusinessPhoneNumber'];
				$temp['BusinessAreaCode'] = $appointment['BusinessAreaCode'];
				$temp['clientFirstVistedDate'] = $appointment['clientFirstVistedDate'];
				$temp['clientLastVistedDate'] = $appointment['clientLastVistedDate'];
				$temp['optin_email'] = $appointment['opted_in_email'];
				$temp['optin_sms'] = $appointment['opted_in_sms'];
				$temp['HasMemberships'] = $appointment['HasMemberships'];
				$temp['reward_points'] = $appointment['reward_points'];
				$temp['client_log'] = $appointment['client_log'];
				$data[] = $temp;
			}
			$post_body = json_encode($data);
			
			//$input_array = json_decode($post_body,true);
			//print_r($input_array);exit;
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data_new_export/importing_mill_clients_sdk/".$account_no);
			//curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data/importing_mill_clients/1744665785");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
			$result=curl_exec($ch);
		    //pa($result,'result',true);
			$response = json_decode($result,true);
			//print_r($response);
			if($response['IsTotalSuccess'] == true) {
				//mark all records as processed
				$this->db->where_in('ClientId', $ClientIds);
				$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
				$update_array = array(
					 "IsProcessed" => 1
				);
				$processed = $this->db->update(MILL_CLIENTS_TABLE,$update_array);
			} else if($response['SystemError'] == "") {
				if(!empty($response['FailedIdList'])){
					foreach ($response['FailedIdList'] as $FailedIdList) {
						$failure_array[] = $FailedIdList['id'];
					}
					//mark only successful records as processed
					$this->db->where_in('ClientId', $ClientIds);
					$this->db->where_not_in('ClientId', $failure_array);
					$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
					$update_array = array(
						"IsProcessed" => 1
					);
					$processed = $this->db->update(MILL_CLIENTS_TABLE,$update_array);
				    //echo $this->db->last_query()."\n";
				    //echo "Successfully Updated for AccountNo: ".$account_no."\n";
				}	
			}
			
		} else {
			//echo "No Data for AccountNo: ".$account_no."\n";
		}
		
	}
 

}