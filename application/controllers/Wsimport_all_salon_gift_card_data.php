<?php
 defined('BASEPATH') OR exit('No direct script access allowed');
 if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
 }
 class Wsimport_all_salon_gift_card_data extends CI_Controller {
   /**
       AUTHOR: Subbu
       DESCRIPTION: THIS CLASS IS FOR IMPORT SALON GIFT CARD
	**/
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

   function __construct() {
			parent::__construct();
			$this->load->model('Common_model');
			$this->load->model('Salongiftcardimport_model');
		}
    /**
     * Default Fn
     */    
	public function index(){print "Test";}
   
    /**
     * This function for get gift certiifcates sales
     * @param type $startDate
     * @param type $endDate
     * @param type $account_no
     */
    public function GetGiftCertificatesSales($startDate="",$endDate="",$account_no=""){
		    
			$getConfigDetails  = $this->Common_model->getMillSdkConfigDetails($account_no);
			if($getConfigDetails->num_rows()>0)
			{
				foreach($getConfigDetails->result_array() as $configDetails)
				{
					pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");

					$this->salonAccountId =  $configDetails['salon_account_id'];
					$this->pemFilePath	=	base_url()."salonevolve.pem";
					$this->salonMillIp	=	$configDetails['mill_ip_address'];
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
		            $log['whichCron'] = 'GetGiftCertificatesSales';
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
                   $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetGiftCertificateSales',$millMethodParams);                   
					if(isset($this->millResponseXml['GCSales']))
						 {
						 	$gcCount = count($this->millResponseXml['GCSales']);
						 	if(!isset($this->millResponseXml['GCSales'][0]))  
	                        {
	                            $tempArr = $this->millResponseXml['GCSales'];
	                            unset($this->millResponseXml['GCSales']);
	                            $this->millResponseXml['GCSales'][0] = $tempArr;
	                        }
							pa('',"The GC Sales Count--".$gcCount); //DEBUGMSG
							//pa($this->millResponseXml['GCSales']);
						    foreach($this->millResponseXml['GCSales'] as $giftCard) //CONVERTED APPOINTMENTS XML TO ARRAY
							{
							   unset($giftCard['mnotes']);
							   //pa('',"Gift Card--".$giftCard["cinvoiceno"]); //DEBUGMSG
                               array_walk($giftCard, function (&$value,&$key) { 
                                    $value = trim($value);
                                    if($key == 'nprice' || $key == 'norigamt' || $key == 'namtleft')
                                        $value = number_format($value, 2, '.', '');
                                  });

						        $giftCard['dexpiration'] = (!empty($giftCard['dexpiration'])) ? str_replace("T"," ",$giftCard['dexpiration']) : '';
						        $giftCard['dvalidfrom'] = (!empty($giftCard['dvalidfrom'])) ? str_replace("T"," ",$giftCard['dvalidfrom']) : '';						        
						        $giftCard['tlastmodified'] = (!empty($giftCard['tlastmodified'])) ? str_replace("T"," ",$giftCard['tlastmodified']) : '';
						      
						        $giftCard['tdatetime'] = (!empty($giftCard['tdatetime'])) ? explode("T",$giftCard['tdatetime'])[0] : '';

						        

						        //pa('',"GetGiftCertificatesSales");

						        $whereCondition = array('iid' => $giftCard['iid'],'cgiftnumber' => $giftCard['cgiftnumber'],'iheaderid' => $giftCard['iheaderid'],'cinvoiceno' => $giftCard['cinvoiceno'],'account_no' => $this->salonAccountId);

								$arrGiftCard = $this->Salongiftcardimport_model
                                           ->compareGiftCardSalesWithBalance($whereCondition)
                                           ->row_array();

							   	//pa('',"DBdata");															
								
								if(!empty($arrGiftCard))
									{
										$arrGiftCard['nprice'] = number_format($arrGiftCard['nprice'], 2, '.', '');
										$giftCard['ntip'] = number_format($giftCard['ntip'], 2, '.', '');
										$giftCard['ntipleft'] = number_format($giftCard['ntipleft'], 2, '.', '');
										$diff_array = array_diff_assoc($giftCard, $arrGiftCard);
										//pa($diff_array);
										if(empty($diff_array))
									    	{
												pa("No Updates");
												continue; //SAME DATA FOUND, SO CONTINUe with the loop
											}	
											else
												{
													
													/*pa($giftCard);
													pa($arrGiftCard);
													pa($diff_array);
													exit;*/
													
													$diff_array['updatedDate'] = date("Y-m-d H:i:s");
                                                    $diff_array['insert_status'] = Wsimport_all_salon_gift_card_data::UPDATED;
                                                    $whereconditions = array();

													$whereconditions['iid'] = $giftCard['iid'];
													$whereconditions['cgiftnumber'] = $giftCard['cgiftnumber'];
													$whereconditions['iheaderid'] = $giftCard['iheaderid'];
													$whereconditions['cinvoiceno'] = $giftCard['cinvoiceno'];
													$whereconditions['account_no'] = $this->salonAccountId;
													
													$res = $this->Salongiftcardimport_model->updateGiftCardSalesWithBalance($whereconditions,$diff_array);

										           pa("Data Updated");
												}
											}
											else // INSERT APPOINTMENT DATA IN DB 
											{
												   
												    $giftCard['account_no'] = $this->salonAccountId;
						                            $giftCard['insertedDate'] = date("Y-m-d H:i:s");
						                            $giftCard['updatedDate'] = date("Y-m-d H:i:s");
						                            $giftCard['insert_status'] = Wsimport_all_salon_gift_card_data::INSERTED;
													//$res = $this->db->insert(MILL_SERVICE_SALES, $employee_data);

													$res = $this->Salongiftcardimport_model->insertGiftCardSalesWithBalance($giftCard);
													$giftcard_id = $this->db->insert_id();
									                pa("Data Inserted");
												
											}
										  
										} //foreach ends of Package
                                   }
									else
									{
										echo "No Gift Card Sales With Balance Data found in Millennium.";
									}
							// Database Log
							$log['id'] = $log_id;
                            $log_id = $this->Common_model->saveMillCronLogs($log); 	
                            $logOff = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('LogOff',$millloginDetails);	
					         }else{
								  	  echo "SESSION ID NOT SET";
								  }			
				}
			}
	}


}
