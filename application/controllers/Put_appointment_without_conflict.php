<?php
//error_reporting('0');
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Put_appointment_without_conflict extends CI_Controller {
		
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

		public function putAppointment()
		{
			exit;
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit',-1);
	   		ini_set('max_execution_time',0);
	   		ini_set("default_socket_timeout",5);
	   		
	   		$soapClientVersion = 'SOAP_1_1';

	   		$appointmentsData = array();
	   		$updatedAppointments = array();
	   		$appointmentJsonData = file_get_contents("https://saloncloudsplus.com/millws/getAllMillAppointments");
	   		$appointmentsData = json_decode($appointmentJsonData,true); //Appointment Data in Array
	   		//print_r($appointmentsData);exit;

	   		$this->db->where('put_to_sdk','Yes');
	   		$getAllConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
		   	$getAllConfigDetailsArr = $getAllConfigDetails->result_array();

		   	if(!empty($getAllConfigDetailsArr)){
		   		foreach($getAllConfigDetailsArr as $configDetails){
		   			$serverSalonIds[] = $configDetails["salon_id"];
		   		}
		   	} else {
		   		$serverSalonIds = array();
		   	}

		   	$serverSalonIds = array(536,537,538,539,540);

		   	echo "Salons in this server "."<br>"."<pre>";print_r($serverSalonIds);

	   		if(!empty($appointmentsData)){
	   			foreach ($appointmentsData as $row)
				{
					if(!in_array($row['salon_id'], $serverSalonIds)) {
						continue;
					} else {
						echo $row['salon_id']."<br>";//exit;
					
						//echo $row['ApptId'];exit;
						//$this->db->where('putAppointmentStatus',1);
						$this->db->where('salon_id',$row['salon_id']);  
		   				$getConfigData = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
		   				$configDataArr = $getConfigData->row_array();
						// echo $this->db->last_query();exit;
						$MillenniumGuid	=	$configDataArr['mill_guid'];
						$musername	=	$configDataArr['mill_username'];
						$mpassword	=	$configDataArr['mill_password'];
						$url = $configDataArr['mill_url'];
												
						//echo $row['ApptId'];exit;
						try{
							$client = new SoapClient($url.'?WSDL', array('trace' => 1, 'soap_version' => $soapClientVersion, 'exceptions'=> 1,"stream_context" =>stream_context_create(array('ssl' => array('verify_peer'=> false,'verify_peer_name'  => false)))));
							//$client = new SoapClient($wsd, array('exceptions'=> 1,'trace' => true, 'soap_version' => $soapClientVersion,"stream_context" =>stream_context_create(array('ssl' => array('verify_peer'=> false,'verify_peer_name'  => false)))));
						} catch (Exception $e) {
							echo $exceptionError = $e->getMessage()."<br>";
							continue;
						}

						try{	
						   	//$client = new SoapClient('http://76.124.213.113:9000/webapp/MillenniumSDK.asmx?WSDL');
							$ns = 'http://www.harms-software.com/Millennium.SDK'; //Namespace of the WS.
							$auth = new stdClass();
							$auth->MillenniumGuid = $MillenniumGuid;
					
							//$auth->SessionId = $MillenniumGuid;
					
							$header = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
							 $client->__setSoapHeaders($header);
							//var_dump($client);exit;
							//$outputheader = new SoapHeader($ns, 'SessionInfo', '', false);
							 $outputheader = '';
							$param = array('User' => $musername,'Password' => $mpassword);
						
							$result = $client->__soapCall('Logon', array($param), NULL,NULL,$outputheader);
						} catch (Exception $e) {
							//echo 'Caught exception1: ',  $e->getMessage(), "\n";
							echo $exceptionError = $e->getMessage()."<br>";
							continue;
						}

						$logonStatus = $result->LogonResult;
						$sessId = $outputheader['SessionInfo']->SessionId;
						if(!empty($sessId)){
							$sess = new stdClass();
							$sess->SessionId = $sessId;
						}
				
						//exit;
						
						if(!empty($logonStatus) && !empty($sessId))
						{
							$headers = array();
							$headers[] = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
							$headers[] = new SoapHeader($ns, 'SessionInfo', $sess, false);
							$client->__setSoapHeaders($headers);
							$outputheader2 = '';
							//$param2 = array('AppointmentId' => 373460);
							//echo $row['ApptId'];exit;
							$param2 = array('AppointmentId' => $row['ApptId']);
							try {
								$result2 = $client->__soapCall('GetAppointment', array($param2), NULL,$headers,$outputheader2);
								//print_r($result2);exit;
								if(!empty($result2))
								{
									//echo '<pre>';print_r((array)$result2->GetAppointmentResult);echo '</pre>';exit;
									$array = json_decode(json_encode($result2->GetAppointmentResult),true);

									$json_get_array = json_encode($array);//FOR LOG

									if($array['ConfirmationTypes'] != '**C*****') {
										$array['ConfirmationTypes'] = '**C*****';
										$param2 = array('appointment' => $array);

										$json_put_array = json_encode($param2);//FOR LOG

										//exit;
										try {
											 	$result3 = $client->__soapCall('PutAppointmentWithoutConflictCheck', array($param2), NULL,$headers,$outputheader2);

											 	try {
											 		$insert_array = array('salon_id' => $row['salon_id'],
											 							'appt_id' => $result3->PutAppointmentResult,
											 							'appointment_date' => $row['ddate'],
											 							'is_appt_write_back' => 1,
											 							'cron_type' => 'PUT APPT CONF',
											 							'json_get_array' => $json_get_array,
											 							'json_put_array' => $json_put_array,
											 							'inserted_date' => date('Y-m-d H:i:s')
											 						);
											 		$this->db->insert('plus_put_appointment_logs', $insert_array);
											 	} catch(Exception $e) {
											 		echo 'Caught exception: ',  $e->getMessage(), "\n";echo "<br>";
											 	} 

											 	echo '<pre>';print_r($result3);echo '</pre>';echo "<br>";

											 	//echo $result3->PutAppointmentResult;exit;
											 	$temp = array();
											 	$temp['ApptId'] = $result3->PutAppointmentWithoutConflictCheckResult;
											 	$temp['salon_id'] = $row['salon_id'];
											 	$updatedAppointments[] = $temp;
											 	//$updatedAppointments[] = $result3->PutAppointmentResult;


										} catch(Exception $e) {
										 //var_dump($e->getTrace());
											echo 'Caught exception: ',  $e->getMessage(), "\n";echo "<br>";
											try {
											 		$insert_array = array('salon_id' => $row['salon_id'],
											 							'appt_id' => $row['ApptId'],
											 							'appointment_date' => $row['ddate'],
											 							'is_appt_write_back' => 0,
											 							'cron_type' => 'PUT APPT CONF FAIL',
											 							'json_get_array' => $json_get_array,
											 							'json_put_array' => $json_put_array,
											 							'inserted_date' => date('Y-m-d H:i:s')
											 						);
											 		$this->db->insert('plus_put_appointment_logs', $insert_array);
											 	} catch(Exception $e) {
											 		echo 'Caught exception: ',  $e->getMessage(), "\n";echo "<br>";
											 	} 
									
										}
									}
									
									//echo $str;exit;
								}
							} catch(Exception $e) {
								echo 'Caught exception: ',  $e->getMessage(), "\n";echo "<br>";
							}
						}
					
					}
					
				}
				if(!empty($updatedAppointments))
		   		{
		   			$post_body = json_encode($updatedAppointments);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/millws/updatePutAppointmentStatus");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body); 
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
					$result=curl_exec($ch);
					curl_close($ch);
					$res = json_decode($result,true);
					if(!empty($res) && $res['status']==1)
					{
						echo "Put Appointment status Updated Successfully.";
						echo "<br>";
					}
					else
					{
						echo "Put Appointment status not Updated Successfully.";
						echo "<br>";
					}
					//print_r($res);exit;

		   		}
		   		else
		   		{
		   			echo "No Updated Appointements Found.";
		   			echo "<br>";
		   		}
	   		}
	   		else
	   		{
	   			echo "No Appointments Found in the Database.";
	   			echo "<br>";
	   		}
		}
	}
?>