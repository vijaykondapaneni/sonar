<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class Wsimport_all_salon_services_data extends CI_Controller {

	CONST INSERTED = 0;
    CONST UPDATED = 1;    
    public $salonAccountId;
    public $pemFilePath;
    public $salonMillIp;
    public $salonMillGuid;
    public $salonMillUsername;
    public $salonMillPassword;
    public $salonMillSdkUrl;
    public $startDate;
    public $endDate;
    public $millResponseXml;    
    public $transactionIds;
    public $millResponseSessionId;
    /**
       AUTHOR: Subbu
       DESCRIPTION: THIS CLASS IS FOR IMPORT SALON SERVICES DATA
	**/

   function __construct() {
			parent::__construct();
			$this->load->model('Common_model');
			$this->load->model('Servicesalesimport_model');
		}
        
    /**
     * Default index Fn
     */    
	public function index(){ print "Test";}
   
    /**
     * This function for get service sales   
     * @param type $startDate
     * @param type $endDate
     * @param type $account_no
     */
	function GetServiceSales($startDate="",$endDate="",$account_no="")
		{
			// GET SALON CONFIG DETAILS
			$getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no);
			if($getConfigDetails->num_rows()>0)
			{
				foreach($getConfigDetails->result_array() as $configDetails)
				{
					pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
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
                     $log['whichCron'] = 'GetServiceSales';
                     $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                     $log['CronType'] = 0;
                     $log['id'] = 0;
                     $log_id = $this->Common_model->saveMillCronLogs($log);
                     //MILLENIUM SDK REQUEST FOR SOAP CALL
		            $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                    $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                     
                    $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                    
                    pa($this->millResponseSessionId,'Session');
                    
					if($this->millResponseSessionId){
						$millMethodParams = array('StartDate' => $this->startDate,'EndDate' => $this->endDate,'IncludeVoided' => 0);
                        $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetServiceSales',$millMethodParams);
                        
					 if(isset($this->millResponseXml['ServiceSales']))
					  {
							/*$serviceSalesCount = count($this->$millResponseXml['ServiceSales']);*/
							if(!isset($this->millResponseXml['ServiceSales'][0]))  
	                        {
	                            $tempArr = $this->millResponseXml['ServiceSales'];
	                            unset($this->millResponseXml['ServiceSales']);
	                            $this->millResponseXml['ServiceSales'][0] = $tempArr;
	                        }
							foreach($this->millResponseXml['ServiceSales'] as $sSales) 
							  {
								pa('',"Service Sales--".$sSales["cinvoiceno"]); //DEBUGMSG

								array_walk($sSales, function (&$value,&$key) { 
                                    $value = trim($value);
                                    if($key == 'nprice' || $key == 'nquantity')
                                        $value = number_format($value, 4, '.', '');
                                  });

								$sSales['tdatetime'] = (!empty($sSales['tdatetime'])) ? explode("T",$sSales['tdatetime'])[0] : '';

								$this->transactionIds[] = $sSales["itransdetailid"];//COLLECT TRANS ID TO PERFORM DELETE OPERATION BELOW  

								pa('',"GetServiceSales");												
								// GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
								$wherecondition =  array('iempid' => $sSales['iempid'],'itransdetailid' => $sSales['itransdetailid'],'cinvoiceno' => $sSales['cinvoiceno'],'iclientid' => $sSales['iclientid'],'account_no' => $this->salonAccountId);
								$arrServiceSales = $this->Servicesalesimport_model
								   ->compareMillServicesSales($wherecondition)
								   ->row_array();

							    pa('',"DBdata");	   
								
								if(!empty($arrServiceSales))
								{
									$diff_array = array_diff_assoc($sSales, $arrServiceSales);
									//pa($diff_array,"Diff Array");

                                    if(empty($diff_array))
									{
										continue; //SAME DATA FOUND, SO CONTINUe with the loop
									}	
									else
									{
										
                                        $diff_array['updatedDate'] = date("Y-m-d H:i:s");
                                        $diff_array['insert_status'] = Wsimport_all_salon_services_data::UPDATED;										
										$whereconditions = array();
										$whereconditions['iempid'] = $sSales['iempid'];
										$whereconditions['itransdetailid'] = $sSales['itransdetailid'];
										$whereconditions['cinvoiceno'] = $sSales['cinvoiceno'];
										$whereconditions['iclientid'] = $sSales['iclientid'];
										$whereconditions['account_no'] = $this->salonAccountId;
										//$res = $this->db->update(MILL_SERVICE_SALES, $employee_data);
										$res = $this->Servicesalesimport_model->updateMillServicesSales($whereconditions,$diff_array);
										pa($diff_array,"Updated data ---ID=".$arrServiceSales['id']);
									}
								}
								else // INSERT APPOINTMENT DATA IN DB 
								{
									$sSales['account_no'] = $this->salonAccountId;
		                            $sSales['insertedDate'] = date("Y-m-d H:i:s");
		                            $sSales['updatedDate'] = date("Y-m-d H:i:s");
		                            $sSales['insert_status'] = Wsimport_all_salon_services_data::INSERTED;
									//$res = $this->db->insert(MILL_SERVICE_SALES, $employee_data);
									$res = $this->Servicesalesimport_model->insertMillServicesSales($sSales);
									$appts_id = $this->db->insert_id();
									pa('',"inserted data ---ID=".$appts_id);
								}
							} //foreach ends of Package

							if(!empty($this->transactionIds))
							{
								//TO SEARCH EXISTING APPOINTMENTS WITH NEW APPOINTMENTS
								$allDBtransactionIds = array();
								//  DB COMPARING 								
							    $whereCondition = array('account_no' => $this->salonAccountId , 'tdatetime >=' => $this->startDate, 'tdatetime <=' => $this->endDate);
								$allApptsqueryArray = $this->Servicesalesimport_model
                                           ->compareMillServicesSales($whereCondition)
                                           ->result_array();
							foreach($allApptsqueryArray as $allApptId)
							{
								$allDBtransactionIds[$allApptId['id']] =  $allApptId['itransdetailid'];
							}
							if($allDBtransactionIds){
								$result = array_diff($allDBtransactionIds,$this->transactionIds);
								
								if(!empty($result))
								{
									foreach($result as $recordID =>$resultss)
									{
										$whereconditions = array(
                                        'id' => $recordID,
                                        'account_no'=> $this->salonAccountId,
                                        );
										$this->Servicesalesimport_model->deleteMillServiceSales($whereconditions); 
									}
									pa($result,"Deleted data ---=".$result);
								}
							  }	
							}
					  }
					  else
					   {
							echo "No Service Sales Data found in Millennium.";
					   }
					// Database Log
					$log['id'] = $log_id;
                    $log_id = $this->Common_model->saveMillCronLogs($log);   
                    $logOff = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('LogOff',$millloginDetails);
					
				    }else{
				   	       echo "SESSION ID NOT SET";
				    }
				  } 	
				 
				 $errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
        }
        else{
            exit("PLASE CHECK SALON ID");
        }
    }
}