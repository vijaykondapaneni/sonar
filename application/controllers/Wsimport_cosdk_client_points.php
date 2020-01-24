<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Wsimport_cosdk_client_points extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			$this->load->library('session');
			$this->load->library('form_validation');
			$this->load->library('pagination');
			$this->load->helper(array('form', 'url'));
			$this->load->database();
		}
		
		function index() {
			// wont do anything
		}


			
		//Added By KRANTHI ON 28-11-2015
		//THE BELOW METHOD IS USED FOR GETTING APPOINTMENTS FROM MILLENIUM SDK FOR ALL SALONS
		function GetClientPointsHistory($startDate="",$endDate="",$account_no="")
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit', '-1');
			require_once('lib/nusoap.php'); //SOAP LIBRARY FILE
			require_once('xml2arr.php'); 
			//require_once('salonevolve.pem');
			//TO GET CONFIG DETAILS FROM DB

			if(!empty($startDate) && !empty($endDate)){
				$StartDate = $startDate;
				//$EndDate = $endDate;
				$EndDate = date('Y-m-d',strtotime($endDate . "+1 days"));
			} else {
				$StartDate = date("Y-m-d");
				$EndDate = date('Y-m-d',strtotime($StartDate . "+1 days"));
			}

			$this->db->where('salon_account_id',$account_no);
			$getConfigDetails = $this->db->get(MILL_ALL_COSDK_CONFIG_DETAILS);
			$configDetails = $getConfigDetails->row_array();

			if(!empty($configDetails))
			{

				/*foreach($getConfigDetails->result_array() as $configDetails)
				{*/
					echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
					echo "<br>";
					//SALON ACCOUNT NO
					//$account_no = 1501738222;
					$account_no = $configDetails['salon_account_id'];
					//echo $account_no.'<br>';
					//LOG IN DETAILS FOR MILLENIUM SDK	
					$path_to_pem	=	base_url()."salonevolve.pem";

					$siteIp			=	$configDetails['mill_ip_address'];
					$MillenniumGuid	=	$configDetails['mill_guid'];
					$musername		=	$configDetails['mill_username'];
					$mpassword		=	$configDetails['mill_password'];
					$url = $configDetails['mill_url'];

					$locationID = $configDetails["ilocationid"];

					//MILLENIUM SDK URL AND HEADERS AND GUID	
					//$client = new nusoap_client('http://'.$siteIp.'/sdkadvance/MillenniumSDK.asmx?WSDL','wsdl','','','','');
					$client = new nusoap_client($url.'?WSDL','wsdl','','','','');
					$headers = "<MillenniumInfo xmlns=\"http://www.harms-software.com/MillenniumCO.SDK\">
					  <MillenniumGuid>".$MillenniumGuid."</MillenniumGuid>
					</MillenniumInfo>";

					$client->setHeaders($headers);
					$err = $client->getError();
					if ($err) {
						echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
					}
					// LOGIN TO MILLENIUM SDK TO GET SESSION ID----->FIRST STEP
					$param = array('User' => $musername,'Password' => $mpassword);
					try{
						$result = $client->call('Logon', array('parameters' => $param), '', '', false, true);
					}
					catch (Exception $e) {
		    			echo 'Caught exception1: ',  $e->getMessage(), "\n";
					}
					

					if ($client->fault) {
						echo '<h2>Fault</h2><pre>';
						print_r($result);
						echo '</pre>';
					} else {
						
						$err = $client->getError();
						if ($err) {
							$errorType = '<h2>Error</h2><pre>' . $err . '</pre>';  //logToFile('errorFile.log', $errorType);
						} else {
							$data = $client->response;
							preg_match('/<SessionId>(.*?)<\/SessionId>/s', $data, $matches);
							$SessionId = $matches[0]; //SESSION ID AFTER LOGIN
							
							//WE AGAIN CALLING ANOTHER SERVICE, FOR CALLING EVERY SERVICE OTHER THAN LOGON WE NEED THE SESSION ID 
							$client2 = new nusoap_client($url.'?WSDL', 'wsdl','','','','');
							$headers2 = "<MillenniumInfo xmlns=\"http://www.harms-software.com/MillenniumCO.SDK\">
							  <MillenniumGuid>".$MillenniumGuid."</MillenniumGuid>
							</MillenniumInfo>
							<SessionInfo xmlns=\"http://www.harms-software.com/MillenniumCO.SDK\">
							  <SessionId>".$SessionId."</SessionId>
							</SessionInfo>";
							$client2->setHeaders($headers2);
							$err2 = $client2->getError();
							if ($err2) {
								$errorType = '<h2>Constructor error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
							}
													
                            echo "Fetching Points History for Location ".$locationID." and Account No: ".$account_no."<br>";

                            $this->db->select('GlobalId');
                            //$this->db->limit(500,100);//(count to get,from which id we should get)
                            $client_query = $this->db->get_where(MILL_COSDK_CLIENTS, array('account_no' => $account_no,'LocationId' =>$locationID));
							$clientsArray = $client_query->result_array();
							//print_r($clientsArray);exit;
							if(!empty($clientsArray)){
								foreach($clientsArray as $clientGlobalIDS){

									echo "Fetching Points History for Client Global Id ".$clientGlobalIDS["GlobalId"]."<br>";
									
									if(isset($clientGlobalIDS["GlobalId"]) && !empty($clientGlobalIDS["GlobalId"])){
										$clientGlobalId = $clientGlobalIDS["GlobalId"];
									} else {
										$clientGlobalId = "";
									}

									$params2['XmlGIds'] = 
	                                '<NewDataSet>
	                                	<XmlGIds>
	                                		<LocationId>'.$locationID.'</LocationId>
	                                		<GId>'.$clientGlobalId.'</GId>
	                                	</XmlGIds>
	                                </NewDataSet>';
	                                //$params2['XmlIds'] = '<Ids><LocationId>'.$locationID.'</LocationId></Ids>';

	                                $params2['startDate'] = $StartDate;
	                                $params2['endDate'] = $EndDate;

	                                try{
										$result2 = $client2->call('GetClientPointsHistory', array('parameters' => $params2), '', '', false, true);//METHOD WITH PARAMETERS
									}
									catch (Exception $e) {
				    					echo 'Caught exception2: ',  $e->getMessage(), "\n";
									}
									
									if ($client2->fault) {
										echo '<h2>Fault in second query</h2><pre>';
										print_r($result2);
										echo '</pre>';
									} else {
										$err = $client2->getError();
										if ($err2) {
											$errorType = '<h2>Error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
										} else {
											//echo $result2['GetClientPointsHistoryResult']; exit;
											//RESULT OF APPOINTMENTS IN XML FORMAT
											try{
												$xml = new simpleXml2Array(utf8_encode($result2['GetClientPointsHistoryResult']),null);
												//$xml = new simpleXml2Array($result2['GetClientPointsHistoryResult']);
											}
											catch (Exception $e) {
				    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
											}
											
											$clientsIds = array();
											$dataArr = array();
											$allapptIds = array();
											$transactionIds = array();
											//print_r($xml->arr);exit;
											//echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
											if(isset($xml->arr['PointsHistory']))
											{
												foreach($xml->arr['PointsHistory'] as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
												{
													//echo 'Service Total Sales '.!empty($appts["iid"][0]) ? $appts["ntotal"][0] : "0.00".'<br>';
													echo 'Client Points By Date Time '.$appts['ipointsremaining'][0].'<br>';

													if(!empty($appts['tdatetime'][0]))
													{
														$expDate = explode("T",$appts['tdatetime'][0]);
														$tdatetime = $expDate[0];
													}
													else
													{
														$tdatetime = "";
													}

													if(!empty($appts['dpointsexpire'][0]))
													{
														$expExpireDate = explode("T",$appts['dpointsexpire'][0]);
														$dpointsexpire = $expExpireDate[0];
													}
													else
													{
														$dpointsexpire = "";
													}							
													// GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
													/*$query = $this->db->get_where(MILL_SERVICE_TOTAL_SALES_BY_CLASS, array('iclassid' => $appts['iclassid'][0],'account_no' => $account_no,'lrefund' => 'false'));*/
													$this->db->where('ilocationid',$appts['ilocationid'][0]);
													$this->db->where('account_no',$account_no);
													$this->db->where('iclientid',$appts['iclientid'][0]);
													$this->db->where('iclientgid',$appts['iclientgid'][0]);
													$query = $this->db->get(MILL_COSDK_CLIENTS_POINTS_HISTORY);
													$apptsArray = $query->row_array();
													if(!empty($apptsArray))
													{
														if($apptsArray['ilocationid']==$appts['ilocationid'][0] &&
															$apptsArray['account_no']==$account_no && 
															$apptsArray['tdatetime']==$tdatetime &&
															$apptsArray['cpromo']==$appts['cpromo'][0] && 
															$apptsArray['cref']==$appts['cref'][0] && 
															$apptsArray['citem']==$appts['citem'][0] &&
															$apptsArray['ipoints']==$appts['ipoints'][0] &&
															$apptsArray['ipointsremaining']==$appts['ipointsremaining'][0] &&
															$apptsArray['ypointstocurrency']==$appts['ypointstocurrency'][0] &&
															$apptsArray['yvalueremaining']==$appts['yvalueremaining'][0] &&
															$apptsArray['dpointsexpire']==$dpointsexpire &&
															$apptsArray['iheaderid']==$appts['iheaderid'][0] &&
															$apptsArray['iheadergid']==$appts['iheadergid'][0] &&
															$apptsArray['iclientid']==$appts['iclientid'][0] &&
															$apptsArray['iclientgid']==$appts['iclientgid'][0]
														)
														{
															continue; //SAME DATA FOUND, SO CONTINUe with the loop
														}	
														else
														{
															//UPDATE DATA IN DB 
															$employee_data = array(
																'ilocationid' => $appts['ilocationid'][0],
																'tdatetime' => $tdatetime,
																'cpromo' => $appts['cpromo'][0],
																'cref' => $appts['cref'][0], 
																'citem' => $appts['citem'][0],
																'ipoints' => $appts['ipoints'][0],
																'ipointsremaining' => $appts['ipointsremaining'][0],
																'ypointstocurrency' => $appts['ypointstocurrency'][0],
																'yvalueremaining' => $appts['yvalueremaining'][0],
																'dpointsexpire' => $dpointsexpire,
																'iheaderid' => $appts['iheaderid'][0],
																'iclientid' => $appts['iclientid'][0],
																'iclientgid' => $appts['iclientgid'][0],
																'updatedDate' => date("Y-m-d H:i:s"),
																'insert_status' => 'Updated',
															);
															$this->db->where('ilocationid',$appts['ilocationid'][0]);
															$this->db->where('account_no',$account_no);
															$this->db->where('iclientid',$appts['iclientid'][0]);
															$this->db->where('iclientgid',$appts['iclientgid'][0]);
															$res = $this->db->update(MILL_COSDK_CLIENTS_POINTS_HISTORY, $employee_data);
														}
													}
													else // INSERT APPOINTMENT DATA IN DB 
													{
														$employee_data = array(
															'account_no' => $account_no,
															'ilocationid' => $appts['ilocationid'][0],
															'tdatetime' => $tdatetime,
															'cpromo' => $appts['cpromo'][0],
															'cref' => $appts['cref'][0], 
															'citem' => $appts['citem'][0],
															'ipoints' => $appts['ipoints'][0],
															'ipointsremaining' => $appts['ipointsremaining'][0],
															'ypointstocurrency' => $appts['ypointstocurrency'][0],
															'yvalueremaining' => $appts['yvalueremaining'][0],
															'dpointsexpire' => $dpointsexpire,
															'iheaderid' => $appts['iheaderid'][0],
															'iclientid' => $appts['iclientid'][0],
															'iclientgid' => $appts['iclientgid'][0],
															'insertedDate' => date("Y-m-d H:i:s"),
															'updatedDate' => date("Y-m-d H:i:s"),
															'insert_status' => 'Inserted',
														);
														$res = $this->db->insert(MILL_COSDK_CLIENTS_POINTS_HISTORY, $employee_data);
														$appts_id = $this->db->insert_id();
													}
												} //foreach ends of Package
											}
											else
											{
												echo "No Client Points History by datetime Data found in Millennium."."<br>";
											}
										}
									}
								} //CLIENTS FOREACH ENDS
							} else {
								//NO CLIENTS FOUND WITH GLOBAL ID
								echo "No Client FOund in database."."<br>";
							}
                      
											
							$errorType = 'client code executed successfully'.'<br>'; //logToFile('errorFile.log', $errorType);
						}
					}
				//}
			}
		}

		function putClientPoints(){
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit',-1);
	   		ini_set('max_execution_time',0);
	   		ini_set("default_socket_timeout",5);
	   		
	   		$soapClientVersion = 'SOAP_1_1';

	   		$appointmentsData = array();
	   		$updatedAppointments = array();
	   		$appointmentJsonData = file_get_contents("https://saloncloudsplus.com/millws/getAllMillClientPointsToWriteBack");
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

		   	echo "Salons in this server "."<br>"."<pre>";print_r($serverSalonIds);

	   		if(!empty($appointmentsData)){
	   			foreach ($appointmentsData as $row)
				{
					if(!in_array($row['salon_id'], $serverSalonIds)) {
						continue;
					} else {
						echo $row['salon_id']."<br>";//exit;

						$clientid = $row['clientid'];
						$points = $row['reward_points'];
						$pointsValue = 5;
						$pointsExpiryDate = '2018-02-28T00:00:00';
					
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
							$client = new SoapClient($url.'?WSDL', array('trace' => 1, 'soap_version' => $soapClientVersion, 'exceptions'=> 1));
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
							
							
							$param2 = array('clientId' => $clientid,'points' => $points,'pointsValue' => $pointsValue,'pointsExpireOn' => $pointsExpiryDate,'reason' => 'Points Adjustment From Salon Clouds');
							
							try {
								 	$result3 = $client->__soapCall('PutClientPointsAdjustment', array($param2), NULL,$headers,$outputheader2);
			 	
								 	echo '<pre>';print_r($result3->PutClientPointsAdjustmentResult);echo '</pre>';echo "<br>";

								 	//if()

								 	//echo $result3->PutAppointmentResult;exit;
								 	if($result3->PutClientPointsAdjustmentResult==true){
								 		$temp = array();
									 	$temp['clientid'] = $row['clientid'];
									 	$temp['salon_id'] = $row['salon_id'];
									 	$updatedAppointments[] = $temp;
								 	} else {
								 		$updatedAppointments = array();
								 	}
							} catch(Exception $e) {
							 //var_dump($e->getTrace());
								echo 'Caught exception: ',  $e->getMessage(), "\n";echo "<br>";
							}
						}
					
					}
					
				}
				if(!empty($updatedAppointments))
		   		{
		   			$post_body = json_encode($updatedAppointments);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/millws/updatePutClientPointStatus");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body); 
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
					$result=curl_exec($ch);
					curl_close($ch);
					$res = json_decode($result,true);
					if(!empty($res) && $res['status']==1)
					{
						echo "Put Client Points Updated Successfully.";
						echo "<br>";
					}
					else
					{
						echo "Put Client Points not Updated Successfully.";
						echo "<br>";
					}
					//print_r($res);exit;

		   		}
		   		else
		   		{
		   			echo "No Updated Client Points Found.";
		   			echo "<br>";
		   		}
	   		}
	   		else
	   		{
	   			echo "No Client Points Found in the Database.";
	   			echo "<br>";
	   		}
		}
	}
?>