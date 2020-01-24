<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}

class Wsimport_millsdk_past_appts extends CI_Controller {
	/**
       AUTHOR: Subbu
       DESCRIPTION: THIS CLASS IS FOR IMPORT PAST APPOINTMENTS
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
    public $millResponseSessionId;    

   function __construct() {
			parent::__construct();
			$this->load->model('Common_model');
			$this->load->model('Pastappointmentsimport_model');
		}
        
    /**
     * Default Index Fn
     */    
	public function index(){ print "Test";}
    
    
    /**
     * This function for get past appointments   
     * @param type $startDate
     * @param type $endDate
     * @param type $account_no
     */
	public function getPastAppointments($startDate="",$endDate="",$account_no=""){
            $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no);
			if($getConfigDetails->num_rows()>0){
				foreach($getConfigDetails->result_array() as $configDetails){
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
                    $log['whichCron'] = 'getPastAppointments';
                    $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_millsdk_past_appts/getPastAppointments/'.$startDate.'/'.$endDate.'/'.$this->salonAccountId;
                    $log['CronType'] = 0;
                    $log['id'] = 0;
                    $log_id = $this->Common_model->saveMillCronLogs($log);
									
                     //MILLENIUM SDK REQUEST FOR SOAP CALL
		            $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
	                $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                    
	                $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                    
	                //pa($this->millResponseSessionId,'Session');
                    
                    if($this->millResponseSessionId){
	                     $millMethodParams = array('StartDate' => $this->startDate,'EndDate' => $this->endDate);
	                     $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetAllAppointmentsByDate',$millMethodParams);
	                     $allapptIds = array();
						 if(isset($this->millResponseXml['Apptointments']))
								{
										//COUNT OF APPTS FROM MILL SDK
										$countOfAppts = count($this->millResponseXml['Apptointments']);
										//echo $countOfAppts;exit;
										foreach($this->millResponseXml['Apptointments'] as $pAppts) //CONVERTED APPOINTMENTS XML TO ARRAY
												{
													//pa($pAppts['cempname'],'EmployeeName'); 
													//pa('',"Past Apptointments--".$pAppts["iid"]); //DEBUGMSG
													$pAppts['AppointmentIID']= $pAppts["iid"];
													$allapptIds[] = $pAppts['iid'];
													
													$pAppts['CheckedIn'] = $pAppts['lcheckedin'];

													if(is_array($pAppts['ccheckintime'])){
													 $pAppts['CheckInTime'] = $pAppts['ccheckintime'][0];
													}else{
													  $pAppts['CheckInTime'] = $pAppts['ccheckintime'];	
													}

													if(is_array($pAppts['ccheckouttime'])){
													 $pAppts['CheckoutTime'] = $pAppts['ccheckouttime'][0];
													}else{
													  $pAppts['CheckoutTime'] = $pAppts['ccheckouttime'];	
													}

													//$pAppts['CheckInTime'] = $pAppts['ccheckintime'];	
													//$pAppts['CheckoutTime'] = $pAppts['ccheckouttime'];
													$pAppts['MillLastModifiedDate'] = (!empty($pAppts['tlastmodified'])) ? str_replace("T"," ",$pAppts['tlastmodified']) : '';

													$pAppts['MillCreatedDate'] = (!empty($pAppts['torigdatetime'])) ? str_replace("T"," ",$pAppts['torigdatetime']) : '';

													$pAppts['MillLastModifiedDate'] = (!empty($pAppts['tlastmodified'])) ? str_replace("T"," ",$pAppts['tlastmodified']) : '';

													$pAppts['MillLastChangeDate'] = (!empty($pAppts['tlastchg'])) ? str_replace("T"," ",$pAppts['tlastchg']) : '';

													
													$appointmentDateOnly = (!empty($pAppts['ddate'])) ? explode("T",$pAppts['ddate'])[0] : '';

													$pAppts['AppointmentDate'] = date('Y-m-d H:i:s', strtotime("$appointmentDateOnly $pAppts[ctimeofday]"));

													$pAppts['EmployeeName'] = $pAppts['cempname'];
													$pAppts['Service'] = $pAppts['cservice'];
													$pAppts['ApptId'] = $pAppts['iapptid'];
													$pAppts['ClientId'] = $pAppts['iclientid'];
													$pAppts['Nstartlen'] = $pAppts['nstartlen'];
													$pAppts['Ngaplen'] = $pAppts['ngaplen'];
													$pAppts['Nfinishlen'] = $pAppts['nfinishlen'];
													$pAppts['Lprebook'] = $pAppts['lprebook'];
													
													$removeKeys = array('iid','ilocationid','iapptid','ddate','ctimeofday','istandid','iclientid','iservid','iappttype','lcheckedin','ccheckintime','ccheckouttime','iresourceid','igenderid','nlineno','lnoshow','iblockid','istatusid','igid','llate','nsessionlen','ndelay','tlastmodified','ipayclid','mappnotes','torigdatetime','tlastchg','ioriglogin','ilastlogin','lsola','iseconds','lclass','lpost','tarrived','iconfirmedby','cconfirmation','cclient','cempname','cservice','cresourcedescr','cblockdescr','cgenderdescr','cappttypedescr','tconfirmed','nstartlen','nfinishlen','ngaplen','lprebook');
                                                    $pAppts = array_diff_key($pAppts, array_flip($removeKeys));
													
													/*array_walk($pAppts, function (&$value) { $value = trim($value);});*/		
													array_walk($pAppts, function (&$value,&$key) { 
					                                    $value = trim($value);
					                                    if($key == 'nstartlen' || $key == 'ngaplen' || $key == 'nfinishlen')
					                                        $value = number_format($value, 4, '.', '');
		                                             });
													
													$pAppts['Status'] = 1;
													// COMPARE DB DATA WITH SERVICE DATA 
													//pa('',"getPastAppointments");
													$whereCondition = array('AppointmentIID' => $pAppts['AppointmentIID'],'	AccountNo' => $this->salonAccountId);
												    
													$arrPastAppts =  $this->Pastappointmentsimport_model
													                ->compareMillPastAppointments($whereCondition)
													                ->row_array();
													//pa('',"DBdata");

													if(!empty($arrPastAppts))
													{
														// COMPARE DB DATA WITH SERVICE DATA 
														
														$diff_array = array_diff_assoc($pAppts, $arrPastAppts);
														if(empty($diff_array))
														{
															pa("No Updates");
															continue; //SAME DATA FOUND, SO CONTINUe with the loop
														}	
														else
														{
															//UPDATE DATA IN DB 
															$diff_array['ModifiedDate'] = date("Y-m-d H:i:s");
															$diff_array['SlcStatus'] = Wsimport_millsdk_past_appts::UPDATED;
															
															$whereconditions = array();
															$whereconditions['AppointmentIID'] = $pAppts['AppointmentIID'];
															$whereconditions['AccountNo'] = $this->salonAccountId;
															$res = $this->Pastappointmentsimport_model->updateMillPastAppointments($whereconditions,$diff_array);
															pa("Data Updated");

															//pa($diff_array,"Updated data ---ID=".$arrPastAppts['id']);
														}
													}
													else // INSERT APPOINTMENT DATA IN DB 
													{
													    $pAppts['AccountNo'] = $this->salonAccountId;
													    $pAppts['CrateatedDate'] = date("Y-m-d H:i:s");
								                        $pAppts['ModifiedDate'] = date("Y-m-d H:i:s");
								                        $pAppts['SlcStatus'] = Wsimport_millsdk_past_appts::INSERTED;
														$res = $this->Pastappointmentsimport_model->insertMillPastAppointments($pAppts);
														$appts_id = $this->db->insert_id();
														pa("Data inserted");
														//pa('',"inserted data ---ID=".$appts_id);
													}
												}
						  }
						 else{	//APPTS not found in MILL
		  				          echo "No Appts found in MILL."."<br>";
						 }
						 if(!empty($allapptIds))
							{
								//pa($allapptIds,'allapptIds',false);
								$this->db->select('AppointmentIID');
								$this->db->where('AccountNo',$this->salonAccountId);
								$this->db->where('date(AppointmentDate)>=',$this->startDate);
								$this->db->where('date(AppointmentDate)<=',$this->endDate);
								$this->db->where('SlcStatus!=',2);
							    $allApptsqueryArray = $this->db->get('mill_past_appointments')->result_array(); 
								foreach($allApptsqueryArray as $allApptId)
								{
									$allDBapptIds[] =  $allApptId['AppointmentIID'];
								}
								//pa($allDBapptIds,'db appointments',false);
								$diff_array_appointments = array();
								$diff_array_appointments = array_diff($allDBapptIds,$allapptIds);
								//pa($diff_array_appointments,'',false);
								if(!empty($diff_array_appointments))
									{
										foreach($diff_array_appointments as $aptId)
										{
											$update_data = array(
												'SlcStatus' => '2',
												'ModifiedDate' => date("Y-m-d H:i:s"),
											);

											$this->db->where('AppointmentIID',$aptId);
											$this->db->where('AccountNo',$this->salonAccountId);
											$this->db->where('date(AppointmentDate)>=',$this->startDate);
											$this->db->where('date(AppointmentDate)<=',$this->endDate);
											$this->db->update('mill_past_appointments',$update_data);
											//pa($this->db->last_query());
											pa($aptId,"Update Cancel Apptointment Id -$aptId");
										}
									}
							}	
						 // Database Log
						 $log['id'] = $log_id;
                         $log_id = $this->Common_model->saveMillCronLogs($log);
                         $logOff = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('LogOff',$millloginDetails);
				   }else{
				   	     echo "SESSION NOT SET";
				   }	
				}
 			}else{
 				pa('Salons are inactive or invalid salon');
 			}
     	}
  
  
 }
