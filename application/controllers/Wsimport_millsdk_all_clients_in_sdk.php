<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	
	class Wsimport_millsdk_all_clients_in_sdk extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			$this->load->library('session');
			$this->load->library('form_validation');
			$this->load->library('pagination');
			$this->load->helper(array('form', 'url'));
			$this->load->database();

			$this->load->model('Common_model');
			$this->load->model('Appointmentsimport_model');
			$this->load->model('Twoyearclientsimport_model');
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		}

		function checkTime()
		{
			echo date("Y-m-d H:i:s");
		}
		
				
		//Added By KRANTHI ON 27-11-2015
		//THE BELOW METHOD IS USED FOR GETTING MILL CLIENTS AS PER GIVEN DATE.
		function getAllClientsInSalon($account_no="",$start="",$end="")
		{
			//echo "dfsdf";exit;
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit', '-1');
			require_once('lib/nusoap.php'); //SOAP LIBRARY FILE
			require_once('xml2arr.php'); 
			//require_once('salonevolve.pem');
			//TO GET CONFIG DETAILS FROM DB
			if(!empty($account_no))
			{
				$accounts = array($account_no);
				$this->db->where_in('salon_account_id', $accounts);
			}
			
			//$this->db->where(array('salon_account_id' =>'936066019'));
			//$names = array(738661740);
			//$this->db->where_in('salon_account_id', $names);
			$getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
			//print_r($getConfigDetails->result_array());exit;

			//if($getConfigDetails->num_rows>0)
			if(!empty($getConfigDetails->result_array()))
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
					echo "===> TWO YEAR CLIENTS: For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
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

					/*$siteIp			=	"50.192.249.193";
					$MillenniumGuid	=	"01017B56-IB42-7058-4459-5066B7FA1663";
					$musername		=	"SDKTEST";
					$mpassword		=	"sdk1234";*/

					//MILLENIUM SDK URL AND HEADERS AND GUID	
					//$client = new nusoap_client('http://'.$siteIp.'/sdkadvance/MillenniumSDK.asmx?WSDL','wsdl','','','','');
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
							/*if(!empty($startDate) && !empty($endDate))
							{
								$param2 = array('StartDate' => $startDate,'EndDate' => $endDate); //parameters
							}
							else
							{
								$currentDate = date("Y-m-d");
								$param2 = array('StartDate' => $currentDate,'EndDate' => $currentDate); //parameters
		    					
							}*/
							
							$param2 = array('SearchString' => '','IncludeDeleted' => 0,'IncludeInactive' => 0); //parameters

							try{
								$result2 = $client2->call('GetClientListing', array('parameters' => $param2), '', '', false, true);//METHOD WITH PARAMETERS
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
									//echo $result2['GetClientListingResult']; exit;
									//RESULT OF APPOINTMENTS IN XML FORMAT
									try{
										$xml = new simpleXml2Array(utf8_encode($result2['GetClientListingResult']),null);
										//$xml = new simpleXml2Array($result2['GetClientsByLastVisitResult']);
									}
									catch (Exception $e) {
		    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
									}
									
									$clientsIds = array();
									$dataArr = array();
									$allapptIds = array();
									//print_r($xml->arr);exit;
									//echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
									if(isset($xml->arr['Clients']) && !empty($xml->arr['Clients']))
									{
										$clientsCount = count($xml->arr['Clients']);

										echo "The total Clients in SDK ".$clientsCount.'<br>';
										$clients_chunked_array = array_chunk($xml->arr['Clients'], 500);
										//print_r($xml->arr['Clients']);exit;
										//$output = array_slice($xml->arr['Clients'], $start, $end);
										foreach($clients_chunked_array as $mainKey => $mainChunk){
											echo "The main Array Key ".$mainKey."<br>";
											//print_r($mainChunk);exit;
						                    foreach($mainChunk as $subChunk){
						                        //echo "The Client Id :".$subChunk['iid'][0]."<br>";exit;
						                        //echo 'CLIENTID '.$appts['iid'][0].'<br>';
																				
												$client_id = $subChunk['iid'][0];
												//$client_id = 21292;
												//echo $client_id.'<br>';
												if(!in_array($client_id,$clientsIds) && $client_id != "-999")//CHECKING DUPLICATION OF CLIENT ID AND 
												{
													echo 'CLIENT '.$client_id.'<br>';

													$err3 = $client2->getError();
													if ($err3) {
														$errorType = '<h2>Constructor error in Third query</h2><pre>' . $err3 . '</pre>'; //logToFile('errorFile.log', $errorType);
													}
													//$clientsIds[] = $client_id;
													//CALLING ANOTHER SERVICE TO GET CLIENT INFO FROM APPOINTMENTS------->STEP 3

													//Adding client ids to array to check the client inserted count
													$clientsIds[] = $client_id; 

													$param3 = array('ClientId' => $client_id);
													try{
														$result3 = $client2->call('GetClient', array('parameters' => $param3), '', '', false, true);
														//print_r($result3);exit;
													}
													catch (Exception $e) {
				    									echo 'Caught exception3: ',  $e->getMessage(), "\n";
													}
													
													if ($client2->fault) {
														echo '<h2>Fault in Third query</h2><pre>';
														print_r($result3);
														echo '</pre>';
													}
													else
													{
														$err3 = $client2->getError();
														if ($err3) {
															$errorType = '<h2>Error in Third query</h2><pre>' . $err3 . '</pre>'; //logToFile('errorFile.log', $errorType);
														}
														else
														{
															$query = $this->db->get_where(MILL_CLIENTS_TABLE, array('ClientId' => $result3['GetClientResult']['Id'],'AccountNo' => $account_no));
															$array = $query->row_array();

															//DateCreated 
															if(!empty($result3['GetClientResult']['FirstVisitDate']))
															{
																$explodeFirstVisitDate = explode("T",$result3['GetClientResult']['FirstVisitDate']);
																$firstVisitDate = $explodeFirstVisitDate[0]." ".$explodeFirstVisitDate[1];
															}
															else
															{
																$firstVisitDate = "";
															}

															if(!empty($result3['GetClientResult']['LastVisitDate']))
															{
																$explodeLastVisitDate = explode("T",$result3['GetClientResult']['LastVisitDate']);
																$lastVisitDate = $explodeLastVisitDate[0]." ".$explodeLastVisitDate[1];
															}
															else
															{
																$lastVisitDate = "";
															}

															if(!empty($result3['GetClientResult']['ConfirmViaSMS'])&& $result3['GetClientResult']['ConfirmViaSMS']=='true')
															{
																$optedInSms = 1;
															}
															else
															{
																$optedInSms = 2;
															}

															if(!empty($result3['GetClientResult']['ConfirmViaEmail']) && $result3['GetClientResult']['ConfirmViaEmail']=='true')
															{
																$optedInEMail = 1;
															}
															else
															{
																$optedInEMail = 2;
															}

															if(!empty($array))
															{
																if($array['Email']==$result3['GetClientResult']['EmailAddress'] && $array['AccountNo']== $account_no 
																	&& $array['Zip']==$result3['GetClientResult']['ZipCode'] && $array['Phone']==$result3['GetClientResult']['HomeAreaCode'].$result3['GetClientResult']['HomePhoneNumber']
																	&& $array['Name']==$result3['GetClientResult']['FirstName'].' '.$result3['GetClientResult']['LastName'] && $array['Dob']==$result3['GetClientResult']['Birthday']
																	&& $array['Sex']==$result3['GetClientResult']['Sex'] && $array['Mobile']==$result3['GetClientResult']['CellPhoneNumber']
																	&& $array['MobileAreaCode']==$result3['GetClientResult']['CellAreaCode'] && $array['BusinessPhoneNumber']==$result3['GetClientResult']['BusinessPhoneNumber']
																	&& $array['BusinessAreaCode']==$result3['GetClientResult']['BusinessAreaCode'] 
																	&& $array['clientFirstVistedDate']==$firstVisitDate 
																	&& $array['clientLastVistedDate']==$lastVisitDate 
																	&& $array['opted_in_email']==$optedInEMail
																	&& $array['opted_in_sms']==$optedInSms
																)
																{
																	continue; //SAME DATA FOUND, SO CONTINUe with the loop
																} 
																else
																{
																	$clients_data = array(
																			
																		'Email' => $result3['GetClientResult']['EmailAddress'],
																		'Name' => $result3['GetClientResult']['FirstName'].' '.$result3['GetClientResult']['LastName'],
																		'Phone' => $result3['GetClientResult']['HomeAreaCode'].$result3['GetClientResult']['HomePhoneNumber'],
																		'Dob' => $result3['GetClientResult']['Birthday'],
																		'Zip' => $result3['GetClientResult']['ZipCode'],
																		'ModifiedDate' => date("Y-m-d H:i:s"),
																		'IsProcessed' => 0,
																		'Sex' => $result3['GetClientResult']['Sex'],
																		'Mobile' => $result3['GetClientResult']['CellPhoneNumber'],
																		'MobileAreaCode' => $result3['GetClientResult']['CellAreaCode'],
																		'BusinessPhoneNumber' => $result3['GetClientResult']['BusinessPhoneNumber'],
																		'BusinessAreaCode' => $result3['GetClientResult']['BusinessAreaCode'],
																		'clientFirstVistedDate' => $firstVisitDate,
																		'clientLastVistedDate' => $lastVisitDate,
																		'opted_in_email' => $optedInEMail,
																		'opted_in_sms' => $optedInSms
																	);
																	$this->db->where('ClientId',$result3['GetClientResult']['Id']);
																	$this->db->where('AccountNo',$account_no);
																	$this->db->update(MILL_CLIENTS_TABLE, $clients_data);
																	
																}
															}
															else
															{
																$clients_data = array(
																	'ClientId' => $result3['GetClientResult']['Id'],
																	'AccountNo' => $account_no,
																	'SalonId' => 0,
																	'Email' => $result3['GetClientResult']['EmailAddress'],
																	'Name' => $result3['GetClientResult']['FirstName'].' '.$result3['GetClientResult']['LastName'],
																	'Phone' => $result3['GetClientResult']['HomeAreaCode'].$result3['GetClientResult']['HomePhoneNumber'],
																	'Zip' => $result3['GetClientResult']['ZipCode'],
																	'Dob' => $result3['GetClientResult']['Birthday'],
																	'CreatedDate' => date("Y-m-d H:i:s"),
																	'ModifiedDate' => date("Y-m-d H:i:s"),
																	'IsProcessed' => 0,
																	'Sex' => $result3['GetClientResult']['Sex'],
																	'Mobile' => $result3['GetClientResult']['CellPhoneNumber'],
																	'MobileAreaCode' => $result3['GetClientResult']['CellAreaCode'],
																	'BusinessPhoneNumber' => $result3['GetClientResult']['BusinessPhoneNumber'],
																	'BusinessAreaCode' => $result3['GetClientResult']['BusinessAreaCode'],
																	'clientFirstVistedDate' => $firstVisitDate,
																	'clientLastVistedDate' => $lastVisitDate,
																	'opted_in_email' => $optedInEMail,
																	'opted_in_sms' => $optedInSms
																);
																$res = $this->db->insert(MILL_CLIENTS_TABLE, $clients_data);
																$clients_id = $this->db->insert_id();
															}

															//$retval = mysql_query( $query, $conn );
														}
													}
												}
						                    }
										}
										
										
										$insertedClientCount = count($clientsIds);
										echo "The total clients inserted in the DB ".$insertedClientCount."<br>";
										
										//EMAIL SENDING ENDS
									} //if condition, if clients are not found in MILL SDK
									else
									{
										echo "No data found in MILL SDK";
									}
								}
							}
							$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
						}
					}
				}
			}
		}

		function GetClientPointsRemaining($account_no="",$limit=0,$limitStartFromCount=0)
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit', '-1');
			require_once('lib/nusoap.php'); //SOAP LIBRARY FILE
			require_once('xml2arr.php'); 
			//require_once('salonevolve.pem');
			//TO GET CONFIG DETAILS FROM DB
			//$this->db->where(array('salon_account_id' =>'1501738222'));
			if(!empty($account_no))
			{
				$names = array($account_no);
				$this->db->where_in('salon_account_id', $names);
			}
			$getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
			//print_r($getConfigDetails->result_array());exit;

			$currentDate = date("Y-m-d");
			

			if(!empty($getConfigDetails->result_array()))
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
					echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
					echo "<br>";

					$salon_id = $configDetails['salon_id'];
				
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

					/*$siteIp			=	"50.192.249.193";
					$MillenniumGuid	=	"01017B56-IB42-7058-4459-5066B7FA1663";
					$musername		=	"SDKTEST";
					$mpassword		=	"sdk1234";*/

					//MILLENIUM SDK URL AND HEADERS AND GUID	
					//$client = new nusoap_client('http://'.$siteIp.'/sdkadvance/MillenniumSDK.asmx?WSDL','wsdl','','','','');

					try{
						$client = new nusoap_client($url.'?WSDL','wsdl','','','','');
					} catch (Exception $e){
						echo 'Caught exception1: ',  $e->getMessage(), "\n";
					}
					
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
							echo $errorType = '<h2>Error</h2><pre>' . $err . '</pre>';  //logToFile('errorFile.log', $errorType);
						} else {
							$data = $client->response;
							preg_match('/<SessionId>(.*?)<\/SessionId>/s', $data, $matches);
							$SessionId = $matches[0]; //SESSION ID AFTER LOGIN
							//echo $SessionId;exit;
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
							$this->db->limit($limit,$limitStartFromCount);//(limitcount,From startingID)
							$this->db->where(array('AccountNo' =>$account_no));
							$getPackageSeriesList = $this->db->get(MILL_CLIENTS_TABLE);
							$clientListArr = $getPackageSeriesList->result_array();
							//print_r($packageListArr);exit;
							if(!empty($clientListArr)) 
							{
								foreach($clientListArr as $packageList)
								{
									$client_Id = $packageList["ClientId"];
									//CURRENT YEAR DATA
									//$params2['XmlIds'] = '<NewDataSet><Ids><Id>'.$packageId.'</Id></Ids></NewDataSet>';
									$params2['clientId'] = $client_Id;
									//$params2['EndDate'] = $endDate;

									try{
										$result2 = $client2->call('GetClientPointsRemaining', array('parameters' => $params2), '', '', false, true);//METHOD WITH PARAMETERS
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
											//echo $result2['GetClientPointsRemainingResult']; exit;
																	
											if(isset($result2['GetClientPointsRemainingResult']) && !empty($result2['GetClientPointsRemainingResult']))
											{
												$query = $this->db->get_where(MILL_CLIENTS_TABLE, array('ClientId' => $client_Id,'AccountNo' => $account_no));
												$clientArray = $query->row_array();

												if($result2['GetClientPointsRemainingResult'] == $clientArray['reward_points']){
													continue;
												} else {
													$update_array = array('reward_points' => $result2['GetClientPointsRemainingResult'], 'IsProcessed' => 0);

				    								$this->db->where('ClientId', $client_Id);
													$this->db->where('AccountNo',$account_no);
													$res = $this->db->update(MILL_CLIENTS_TABLE, $update_array);
												}
											}
											else
											{
												echo "No Remaining Points Found.";echo "<br>";
											}
										}
									}
									$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);

									//CURRENT YEAR DATA ENDS
								} //Foreach of package series from db
							}
							else
							{
								echo "No Remaining Points Found.";echo "<br>";
							}
						}
					}
				}
			}
		}


		function updateClientRewardPoints($account_no=""){

			if(file_exists(APPPATH.'Nusoap_library.php')){
		       require_once(APPPATH.'Nusoap_library.php');
			}
			if($account_no!=''){
            	$account_no = salonWebappCloudDe($account_no);
            }

            $current_date = date('Y-m-d');
            //$current_date = '2018-01-10';

            $getConfigDetails = $this->Common_model->getMillSdkConfigDetails($account_no);
			if($getConfigDetails->num_rows()>0){
				foreach($getConfigDetails->result_array() as $configDetails){
					pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
                    $account_no = $this->salonAccountId = $configDetails['salon_account_id'];
                    $this->pemFilePath	 =	base_url()."salonevolve.pem";
                    $this->salonMillIp	 =	$configDetails['mill_ip_address'];
                    $this->salonMillGuid =	$configDetails['mill_guid'];
                    $this->salonMillUsername =	$configDetails['mill_username'];
                    $this->salonMillPassword =	$configDetails['mill_password'];
                    $this->salonMillSdkUrl = $configDetails['mill_url'];
                    
                    //$this->__getStartEndDate($dayRangeType,$startDate,$endDate);
                     //MILLENIUM SDK REQUEST FOR SOAP CALL
		            $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
	                $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
	                $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
	                pa($this->millResponseSessionId,'Session',false);
                    if($this->millResponseSessionId){
	                     $millMethodParams = array('StartDate' => $current_date,'EndDate' => $current_date);
	                     pa($millMethodParams,'millMethodParams');
	                     $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetAllAppointmentsByDate',$millMethodParams);
	                     $clientsIds = array();
	                     $allapptIds = array();
	                     if($account_no==''){
                            pa('AccountNo Error');
	                     	exit();
	                     }
	                     //pa($this->millResponseXml['Apptointments'],'sdk',false);
	                     if(isset($this->millResponseXml['Apptointments']))
								{
										//COUNT OF APPTS FROM MILL SDK
									   if(!isset($this->millResponseXml['Apptointments'][0]))  
			                            {
			                                 $tempArr = $this->millResponseXml['Apptointments'];
			                                 unset($this->millResponseXml['Apptointments']);
			                                 $this->millResponseXml['Apptointments'][0] = $tempArr;
			                            }
			                            $countOfAppts = count($this->millResponseXml['Apptointments']);
									   pa($countOfAppts,'SDKCOUNT',false);
					

			                            //pa($this->millResponseXml['Apptointments'],'data',false);

										foreach($this->millResponseXml['Apptointments'] as $appts)
										//CONVERTED APPOINTMENTS XML TO ARRAY
										{
													
                                            //Fetching Clients
											$client_id = $appts['iclientid'];
											pa($client_id,'client Id from sdk',false);

											if(!in_array($client_id,$clientsIds) && $client_id != "-999")//CHECKING DUPLICATION OF CLIENT ID AND 
									        {
									         	pa($client_id,'Clients Table Modifications',false);
									         	/*$params2['clientId'] = $client_id;
												//$params2['EndDate'] = $endDate;

												try{
													$result2 = $client2->call('GetClientPointsRemaining', array('parameters' => $params2), '', '', false, true);//METHOD WITH PARAMETERS
												}
												catch (Exception $e) {
							    					echo 'Caught exception2: ',  $e->getMessage(), "\n";
												}*/

												$millMethodParams = array('clientId' => $client_id);
									         	pa($millMethodParams,'params');

                                                $this->millClientResponseXml = $this->nusoap_library->getMillMethodCall('GetClientPointsRemaining',$millMethodParams);

                                                pa($this->millClientResponseXml['GetClientPointsRemainingResult'],'ClientPoints');

                                                if(isset($this->millClientResponseXml['GetClientPointsRemainingResult']) && !empty($this->millClientResponseXml['GetClientPointsRemainingResult']))
												{

													$query = $this->db->get_where(MILL_CLIENTS_TABLE, array('ClientId' => $client_id,'AccountNo' => $account_no));
													$clientArray = $query->row_array();

													if($this->millClientResponseXml['GetClientPointsRemainingResult'] == $clientArray['reward_points']){
														continue;
													} else {
														$update_array = array('reward_points' => $this->millClientResponseXml['GetClientPointsRemainingResult'], 'IsProcessed' => 0);

					    								$this->db->where('ClientId', $client_id);
														$this->db->where('AccountNo',$account_no);
														$res = $this->db->update(MILL_CLIENTS_TABLE, $update_array);
													}
												}
												else
												{
													echo "No Remaining Points Found.";echo "<br>";
												}
											
												$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
											}
										}
												
						        }else{	//APPTS not found in MILL
				  				          echo "No Appts found in MILL."."<br>";
								}
						
				   }else{
				   	     echo "SESSION NOT SET";
				   }	
				}
 			}else{
 				pa('Salons are inactive or invalid salon');
 			}
		}
	}
?>