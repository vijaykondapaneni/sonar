<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	
	class CheckMissedClientsInfo extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			$this->load->library('session');
			$this->load->library('form_validation');
			$this->load->library('pagination');
			$this->load->helper(array('form', 'url'));
			$this->load->database();

			
		}

		function getMissedClients($account_no='')
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit', '-1');
			require_once('lib/nusoap.php'); //SOAP LIBRARY FILE
			require_once('xml2arr.php'); 
			if(!empty($account_no)){
				$accounts = array($account_no);
				$this->db->where_in('salon_account_id', $accounts);
			}
			$curDate = date('Y-m-d');
            $pastDate = date('Y-m-d',strtotime('-3 days'));
			
			$getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
			if(!empty($getConfigDetails->result_array()))
			{
				foreach($getConfigDetails->result_array() as $configDetails)
				{
					pa($configDetails['salon_name'].'--'.$configDetails['salon_account_id']);
					$account_no = $configDetails['salon_account_id'];
					$clients_check = $this->db->query('SELECT DISTINCT iclientid FROM mill_service_sales WHERE account_no='.$account_no.' and tdatetime>="'.$pastDate.'" and tdatetime<="'.$pastDate.'" AND iclientid NOT IN (SELECT Clientid FROM mill_clients where AccountNo='.$account_no.')')->result_array();
					/*pa($this->db->last_query());
					exit();*/
                    if(!empty($clients_check)){

                    	
						$account_no = $configDetails['salon_account_id'];
						$path_to_pem	=	base_url()."salonevolve.pem";
						$siteIp			=	$configDetails['mill_ip_address'];
						$MillenniumGuid	=	$configDetails['mill_guid'];
						$musername		=	$configDetails['mill_username'];
						$mpassword		=	$configDetails['mill_password'];
						$url = $configDetails['mill_url'];
						
						$client = new nusoap_client($url.'?WSDL','wsdl','','','','');
						$headers = "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
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
								$headers2 = "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
								  <MillenniumGuid>".$MillenniumGuid."</MillenniumGuid>
								</MillenniumInfo>
								<SessionInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
								  <SessionId>".$SessionId."</SessionId>
								</SessionInfo>";
								$client2->setHeaders($headers2);
								$err2 = $client2->getError();
								if ($err2) {
									$errorType = '<h2>Constructor error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
								}
								//GETTING ALL THE APPOINTMENTS FROM SDK------------> STEP 2
								if(!empty($startDate) && !empty($endDate))
								{
									$param2 = array('StartDate' => $startDate,'EndDate' => $endDate); //parameters
								}
								else
								{
									$currentDate = date("Y-m-d");
									$param2 = array('StartDate' => $currentDate,'EndDate' => $currentDate); //parameters
			    					
								}

								//print_r($param2);exit;
								
								try{
									$result2 = $client2->call('GetClientsByLastVisit', array('parameters' => $param2), '', '', false, true);//METHOD WITH PARAMETERS
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
										//RESULT OF APPOINTMENTS IN XML FORMAT
										
										$clientsIds = array();
										$dataArr = array();
										$allapptIds = array();
										if(isset($clients_check))
										{
											$clientsCount = count($clients_check);
											//pa($clients_check,'clients_check');
											pa($clientsCount,'clientsCount');
											
											foreach($clients_check as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
											{
												//echo 'CLIENTID '.$appts['iid'][0].'<br>';
												$client_id = $appts['iclientid'];
												//pa($client_id,'client_id');
												if(!in_array($client_id,$clientsIds) && $client_id != "-999")
												{
													$err3 = $client2->getError();
													if ($err3) {
														$errorType = '<h2>Constructor error in Third query</h2><pre>' . $err3 . '</pre>'; //logToFile('errorFile.log', $errorType);
													}
													$clientsIds[] = $client_id; 
												}
											}
										}


										if(!empty($clientsIds)){
											$uniqClientIds = array_unique($clientsIds);
		                                    $xmlIDS = '';
		                                    foreach (array_chunk($uniqClientIds,5) as $newUniqClientIds) {
		                                    	$xmlIDS = '';
		                                    	foreach ($newUniqClientIds as $key => $uClientId) {
		                                          $xmlIDS .='<Ids><Id>'.$uClientId.'</Id></Ids>,';     
		                                        }
		                                        /*pa($xmlIDS);
		                                        exit();*/
			                                    //$xmlIDS .='<Ids><Id>12357</Id></Ids>,';
			                                    $error_array = '';
			                                    $trimedClientIds = rtrim($xmlIDS,",");
			                                   // pa($trimedClientIds,'trimedClientIds');
												
												$param3 = array('XmlIds' => '<NewDataSet>'.$trimedClientIds.'</NewDataSet>');
												try{
													$result3 = $client2->call('GetClientInfo', array('parameters' => $param3), '', '', false, true);
												}
												catch (Exception $e) {
			    									echo 'Caught exception3: ',  $e->getMessage(), "\n";
												}

												//pa($param3);exit;
												//pa($result3);

												/*$finalResult = str_replace('&#x2;', ' ', $result3['GetClientInfoResult']);
												$finalResult = str_replace('&#x7;', ' ', $finalResult);*/
												$patterns = "/&#x['a-zA-Z0-9']{0,};/";
                                                $finalResult = preg_replace($patterns, ' ', $result3['GetClientInfoResult']);

												try{
													$xml = new simpleXml2Array(utf8_encode($finalResult),null);
													//$xml = new simpleXml2Array($result2['GetAllAppointmentsByDateResult']);
												} catch (Exception $e) {
					    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
												}

											    //pa($xml->arr['Clients'],'',true);

												if(!empty($xml->arr['Clients'])){

													foreach($xml->arr['Clients'] as $clientInfo){
														pa($clientInfo['iid'][0],'clientID');

														$query = $this->db->get_where(MILL_CLIENTS_TABLE, array('ClientId' => $clientInfo['iid'][0],'AccountNo' => $account_no));
														$array = $query->row_array();

														//DateCreated 
														if(!empty($clientInfo['dfirstvisit'][0]))
														{
															$explodeFirstVisitDate = explode("T",$clientInfo['dfirstvisit'][0]);
															$firstVisitDate = $explodeFirstVisitDate[0]." ".$explodeFirstVisitDate[1];
														}
														else
														{
															$firstVisitDate = "";
														}

														if(!empty($clientInfo['dlastvisit'][0]))
														{
															$explodeLastVisitDate = explode("T",$clientInfo['dlastvisit'][0]);
															$lastVisitDate = $explodeLastVisitDate[0]." ".$explodeLastVisitDate[1];
														}
														else
														{
															$lastVisitDate = "";
														}

														if(!empty($clientInfo['lsmsblast'][0]) && $clientInfo['lsmsblast'][0]=='true')
														{
															$optedInSms = 1;
														}
														else
														{
															$optedInSms = 2;
														}

														if(!empty($clientInfo['lnoemail'][0]) && $clientInfo['lnoemail'][0]=='true')
														{
															$optedInEMail = 1;
														}
														else
														{
															$optedInEMail = 2;
														}

														if(!empty($clientInfo['nsex'][0]) && $clientInfo['nsex'][0]==1)
														{
															$sex = 'Male';
														}
														else if(!empty($clientInfo['nsex'][0]) && $clientInfo['nsex'][0]==2)
														{
															$sex = 'Female';
														}
														else {
															$sex = 'Unknown';
														}



														if(!empty($array))
														{
															if($array['Email']==trim($clientInfo['cemail'][0]) 
																&& $array['AccountNo']== $account_no 
																&& $array['Zip']==trim($clientInfo['czip'][0]) 
																&& $array['Phone']==trim($clientInfo['carea'][0]).trim($clientInfo['chomephone'][0]) 
																&& $array['Name']==trim($clientInfo['cfirstname'][0]).' '.trim($clientInfo['clastname'][0]) 
																&& $array['Dob']==$clientInfo['dbday'][0] 
																&& $array['Sex']==$sex 
																&& $array['Mobile']==trim($clientInfo['ccellphone'][0])
																&& $array['MobileAreaCode']==trim($clientInfo['ccellarea'][0]) 
																&& $array['BusinessPhoneNumber']==trim($clientInfo['cbusphone'][0])
																&& $array['BusinessAreaCode']==trim($clientInfo['cbusarea'][0]) 
																&& $array['clientFirstVistedDate']==$firstVisitDate 
																&& $array['clientLastVistedDate']==$lastVisitDate 
																&& $array['opted_in_email']==$optedInEMail 
																&& $array['opted_in_sms']==$optedInSms
															)
															{
																pa("NO Updates");
																continue; //SAME DATA FOUND, SO CONTINUe with the loop
															} 
															else
															{
																$clients_data = array(
																		
																	'Email' => trim($clientInfo['cemail'][0]),
																	'Name' => trim($clientInfo['cfirstname'][0]).' '.trim($clientInfo['clastname'][0]),
																	'Phone' => trim($clientInfo['carea'][0]).trim($clientInfo['chomephone'][0]),
																	'Dob' => trim($clientInfo['dbday'][0]),
																	'Zip' => trim($clientInfo['czip'][0]),
																	'ModifiedDate' => date("Y-m-d H:i:s"),
																	'IsProcessed' => 0,
																	'Sex' => $sex,
																	'Mobile' => trim($clientInfo['ccellphone'][0]),
																	'MobileAreaCode' => trim($clientInfo['ccellarea'][0]),
																	'BusinessPhoneNumber' => trim($clientInfo['cbusphone'][0]),
																	'BusinessAreaCode' => trim($clientInfo['cbusarea'][0]),
																	'clientFirstVistedDate' => $firstVisitDate,
																	'clientLastVistedDate' => $lastVisitDate,
																	'opted_in_email' => $optedInEMail,
																	'opted_in_sms' => $optedInSms
																);
																$this->db->where('ClientId',$clientInfo['iid'][0]);
																$this->db->where('AccountNo',$account_no);
																$this->db->update(MILL_CLIENTS_TABLE, $clients_data);
																pa("Data Updated");
																
															}
														}
														else
														{
															$clients_data = array(
																'ClientId' => $clientInfo['iid'][0],
																'AccountNo' => $account_no,
																'SalonId' => 0,
																'Email' => trim($clientInfo['cemail'][0]),
																'Name' => trim($clientInfo['cfirstname'][0]).' '.trim($clientInfo['clastname'][0]),
																'Phone' => trim($clientInfo['carea'][0]).trim($clientInfo['chomephone'][0]),
																'Zip' => trim($clientInfo['czip'][0]),
																'Dob' => $clientInfo['dbday'][0],
																'CreatedDate' => date("Y-m-d H:i:s"),
																'ModifiedDate' => date("Y-m-d H:i:s"),
																'IsProcessed' => 0,
																'Sex' => $sex,
																'Mobile' => trim($clientInfo['ccellphone'][0]),
																'MobileAreaCode' => trim($clientInfo['ccellarea'][0]),
																'BusinessPhoneNumber' => trim($clientInfo['cbusphone'][0]),
																'BusinessAreaCode' => trim($clientInfo['cbusarea'][0]),
																'clientFirstVistedDate' => $firstVisitDate,
																'clientLastVistedDate' => $lastVisitDate,
																'opted_in_email' => $optedInEMail,
																'opted_in_sms' => $optedInSms
															);
															$res = $this->db->insert(MILL_CLIENTS_TABLE, $clients_data);
															$clients_id = $this->db->insert_id();
															pa("Data Inserted");
															
														}
														
													}
													
												} else {
													//NO CLIENTS FOUND IN THE SDK
												}
		                                    	
		                                    }

		                                    

										} 

										
																			
									}
								}
								$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
							}
					}
				  }
				}

			}
		}

		function getMissedAllClients($account_no='')
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit', '-1');
			require_once('lib/nusoap.php'); //SOAP LIBRARY FILE
			require_once('xml2arr.php'); 
			if(!empty($account_no)){
				$accounts = array($account_no);
				$this->db->where_in('salon_account_id', $accounts);
			}
			$curDate = date('Y-m-d');
            $pastDate = date('Y-m-d',strtotime('-3 days'));
			
			$getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
			if(!empty($getConfigDetails->result_array()))
			{
				foreach($getConfigDetails->result_array() as $configDetails)
				{
					pa($configDetails['salon_name'].'--'.$configDetails['salon_account_id']);
					$account_no = $configDetails['salon_account_id'];
					$clients_check = $this->db->query('SELECT DISTINCT iclientid FROM mill_service_sales WHERE account_no='.$account_no.' AND iclientid NOT IN (SELECT Clientid FROM mill_clients where AccountNo='.$account_no.')')->result_array();
					/*pa($this->db->last_query());
					exit();*/
                    if(!empty($clients_check)){

                    	
						$account_no = $configDetails['salon_account_id'];
						$path_to_pem	=	base_url()."salonevolve.pem";
						$siteIp			=	$configDetails['mill_ip_address'];
						$MillenniumGuid	=	$configDetails['mill_guid'];
						$musername		=	$configDetails['mill_username'];
						$mpassword		=	$configDetails['mill_password'];
						$url = $configDetails['mill_url'];
						
						$client = new nusoap_client($url.'?WSDL','wsdl','','','','');
						$headers = "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
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
								$headers2 = "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
								  <MillenniumGuid>".$MillenniumGuid."</MillenniumGuid>
								</MillenniumInfo>
								<SessionInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
								  <SessionId>".$SessionId."</SessionId>
								</SessionInfo>";
								$client2->setHeaders($headers2);
								$err2 = $client2->getError();
								if ($err2) {
									$errorType = '<h2>Constructor error in second query</h2><pre>' . $err2 . '</pre>'; //logToFile('errorFile.log', $errorType);
								}
								//GETTING ALL THE APPOINTMENTS FROM SDK------------> STEP 2
								if(!empty($startDate) && !empty($endDate))
								{
									$param2 = array('StartDate' => $startDate,'EndDate' => $endDate); //parameters
								}
								else
								{
									$currentDate = date("Y-m-d");
									$param2 = array('StartDate' => $currentDate,'EndDate' => $currentDate); //parameters
			    					
								}

								//print_r($param2);exit;
								
								try{
									$result2 = $client2->call('GetClientsByLastVisit', array('parameters' => $param2), '', '', false, true);//METHOD WITH PARAMETERS
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
										//RESULT OF APPOINTMENTS IN XML FORMAT
										
										$clientsIds = array();
										$dataArr = array();
										$allapptIds = array();
										if(isset($clients_check))
										{
											$clientsCount = count($clients_check);
											//pa($clients_check,'clients_check');
											pa($clientsCount,'clientsCount');
											
											foreach($clients_check as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
											{
												//echo 'CLIENTID '.$appts['iid'][0].'<br>';
												$client_id = $appts['iclientid'];
												//pa($client_id,'client_id');
												if(!in_array($client_id,$clientsIds) && $client_id != "-999")
												{
													$err3 = $client2->getError();
													if ($err3) {
														$errorType = '<h2>Constructor error in Third query</h2><pre>' . $err3 . '</pre>'; //logToFile('errorFile.log', $errorType);
													}
													$clientsIds[] = $client_id; 
												}
											}
										}


										if(!empty($clientsIds)){
											$uniqClientIds = array_unique($clientsIds);
		                                    $xmlIDS = '';
		                                    foreach (array_chunk($uniqClientIds,5) as $newUniqClientIds) {
		                                    	$xmlIDS = '';
		                                    	foreach ($newUniqClientIds as $key => $uClientId) {
		                                          $xmlIDS .='<Ids><Id>'.$uClientId.'</Id></Ids>,';     
		                                        }
		                                        /*pa($xmlIDS);
		                                        exit();*/
			                                    //$xmlIDS .='<Ids><Id>12357</Id></Ids>,';
			                                    $error_array = '';
			                                    $trimedClientIds = rtrim($xmlIDS,",");
			                                   // pa($trimedClientIds,'trimedClientIds');
												
												$param3 = array('XmlIds' => '<NewDataSet>'.$trimedClientIds.'</NewDataSet>');
												try{
													$result3 = $client2->call('GetClientInfo', array('parameters' => $param3), '', '', false, true);
												}
												catch (Exception $e) {
			    									echo 'Caught exception3: ',  $e->getMessage(), "\n";
												}

												//pa($param3);exit;
												//pa($result3);

												/*$finalResult = str_replace('&#x2;', ' ', $result3['GetClientInfoResult']);
												$finalResult = str_replace('&#x7;', ' ', $finalResult);*/
												$patterns = "/&#x['a-zA-Z0-9']{0,};/";
                                                $finalResult = preg_replace($patterns, ' ', $result3['GetClientInfoResult']);

												try{
													$xml = new simpleXml2Array(utf8_encode($finalResult),null);
													//$xml = new simpleXml2Array($result2['GetAllAppointmentsByDateResult']);
												} catch (Exception $e) {
					    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
												}

											    //pa($xml->arr['Clients'],'',true);

												if(!empty($xml->arr['Clients'])){

													foreach($xml->arr['Clients'] as $clientInfo){
														pa($clientInfo['iid'][0],'clientID');

														$query = $this->db->get_where(MILL_CLIENTS_TABLE, array('ClientId' => $clientInfo['iid'][0],'AccountNo' => $account_no));
														$array = $query->row_array();

														//DateCreated 
														if(!empty($clientInfo['dfirstvisit'][0]))
														{
															$explodeFirstVisitDate = explode("T",$clientInfo['dfirstvisit'][0]);
															$firstVisitDate = $explodeFirstVisitDate[0]." ".$explodeFirstVisitDate[1];
														}
														else
														{
															$firstVisitDate = "";
														}

														if(!empty($clientInfo['dlastvisit'][0]))
														{
															$explodeLastVisitDate = explode("T",$clientInfo['dlastvisit'][0]);
															$lastVisitDate = $explodeLastVisitDate[0]." ".$explodeLastVisitDate[1];
														}
														else
														{
															$lastVisitDate = "";
														}

														if(!empty($clientInfo['lsmsblast'][0]) && $clientInfo['lsmsblast'][0]=='true')
														{
															$optedInSms = 1;
														}
														else
														{
															$optedInSms = 2;
														}

														if(!empty($clientInfo['lnoemail'][0]) && $clientInfo['lnoemail'][0]=='true')
														{
															$optedInEMail = 1;
														}
														else
														{
															$optedInEMail = 2;
														}

														if(!empty($clientInfo['nsex'][0]) && $clientInfo['nsex'][0]==1)
														{
															$sex = 'Male';
														}
														else if(!empty($clientInfo['nsex'][0]) && $clientInfo['nsex'][0]==2)
														{
															$sex = 'Female';
														}
														else {
															$sex = 'Unknown';
														}



														if(!empty($array))
														{
															if($array['Email']==trim($clientInfo['cemail'][0]) 
																&& $array['AccountNo']== $account_no 
																&& $array['Zip']==trim($clientInfo['czip'][0]) 
																&& $array['Phone']==trim($clientInfo['carea'][0]).trim($clientInfo['chomephone'][0]) 
																&& $array['Name']==trim($clientInfo['cfirstname'][0]).' '.trim($clientInfo['clastname'][0]) 
																&& $array['Dob']==$clientInfo['dbday'][0] 
																&& $array['Sex']==$sex 
																&& $array['Mobile']==trim($clientInfo['ccellphone'][0])
																&& $array['MobileAreaCode']==trim($clientInfo['ccellarea'][0]) 
																&& $array['BusinessPhoneNumber']==trim($clientInfo['cbusphone'][0])
																&& $array['BusinessAreaCode']==trim($clientInfo['cbusarea'][0]) 
																&& $array['clientFirstVistedDate']==$firstVisitDate 
																&& $array['clientLastVistedDate']==$lastVisitDate 
																&& $array['opted_in_email']==$optedInEMail 
																&& $array['opted_in_sms']==$optedInSms
															)
															{
																pa("NO Updates");
																continue; //SAME DATA FOUND, SO CONTINUe with the loop
															} 
															else
															{
																$clients_data = array(
																		
																	'Email' => trim($clientInfo['cemail'][0]),
																	'Name' => trim($clientInfo['cfirstname'][0]).' '.trim($clientInfo['clastname'][0]),
																	'Phone' => trim($clientInfo['carea'][0]).trim($clientInfo['chomephone'][0]),
																	'Dob' => trim($clientInfo['dbday'][0]),
																	'Zip' => trim($clientInfo['czip'][0]),
																	'ModifiedDate' => date("Y-m-d H:i:s"),
																	'IsProcessed' => 0,
																	'Sex' => $sex,
																	'Mobile' => trim($clientInfo['ccellphone'][0]),
																	'MobileAreaCode' => trim($clientInfo['ccellarea'][0]),
																	'BusinessPhoneNumber' => trim($clientInfo['cbusphone'][0]),
																	'BusinessAreaCode' => trim($clientInfo['cbusarea'][0]),
																	'clientFirstVistedDate' => $firstVisitDate,
																	'clientLastVistedDate' => $lastVisitDate,
																	'opted_in_email' => $optedInEMail,
																	'opted_in_sms' => $optedInSms
																);
																$this->db->where('ClientId',$clientInfo['iid'][0]);
																$this->db->where('AccountNo',$account_no);
																$this->db->update(MILL_CLIENTS_TABLE, $clients_data);
																pa("Data Updated");
																
															}
														}
														else
														{
															$clients_data = array(
																'ClientId' => $clientInfo['iid'][0],
																'AccountNo' => $account_no,
																'SalonId' => 0,
																'Email' => trim($clientInfo['cemail'][0]),
																'Name' => trim($clientInfo['cfirstname'][0]).' '.trim($clientInfo['clastname'][0]),
																'Phone' => trim($clientInfo['carea'][0]).trim($clientInfo['chomephone'][0]),
																'Zip' => trim($clientInfo['czip'][0]),
																'Dob' => $clientInfo['dbday'][0],
																'CreatedDate' => date("Y-m-d H:i:s"),
																'ModifiedDate' => date("Y-m-d H:i:s"),
																'IsProcessed' => 0,
																'Sex' => $sex,
																'Mobile' => trim($clientInfo['ccellphone'][0]),
																'MobileAreaCode' => trim($clientInfo['ccellarea'][0]),
																'BusinessPhoneNumber' => trim($clientInfo['cbusphone'][0]),
																'BusinessAreaCode' => trim($clientInfo['cbusarea'][0]),
																'clientFirstVistedDate' => $firstVisitDate,
																'clientLastVistedDate' => $lastVisitDate,
																'opted_in_email' => $optedInEMail,
																'opted_in_sms' => $optedInSms
															);
															$res = $this->db->insert(MILL_CLIENTS_TABLE, $clients_data);
															$clients_id = $this->db->insert_id();
															pa("Data Inserted");
															
														}
														
													}
													
												} else {
													//NO CLIENTS FOUND IN THE SDK
												}
		                                    	
		                                    }

		                                    

										} 

										
																			
									}
								}
								$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
							}
					}
				  }
				}

			}
		}

		
}