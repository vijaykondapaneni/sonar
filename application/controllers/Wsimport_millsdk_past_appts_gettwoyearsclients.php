<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}

class Wsimport_millsdk_past_appts_gettwoyearsclients extends CI_Controller {

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
    function getTwoYearClients($startDate="",$endDate="",$account_no="")
		{
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
                    $log['whichCron'] = 'getTwoYearClients';
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
                    
                    pa($this->millResponseSessionId,'session','');
                    
                    $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetClientsByLastVisit',$millMethodParams);
                    
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
                                            $diff_array['SlcStatus'] = Wsimport_millsdk_past_appts_gettwoyearsclients::UPDATED;

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
										
										$comparearray['SlcStatus'] = Wsimport_millsdk_past_appts_gettwoyearsclients::INSERTED;
                                       
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
                    $logOff = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('LogOff',$millloginDetails);
				}
			}
		}
 

 }