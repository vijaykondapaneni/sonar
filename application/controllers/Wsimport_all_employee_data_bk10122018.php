<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Wsimport_all_employee_data extends CI_Controller {
		
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
		function GetEmployeeListing($account_no="")
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit', '-1');
			require_once('lib/nusoap.php'); //SOAP LIBRARY FILE
			require_once('xml2arr.php'); 
			//require_once('salonevolve.pem');
			//TO GET CONFIG DETAILS FROM DB
			if(!empty($account_no))
			{
				$this->db->where(array('salon_account_id' => $account_no));
			}
			//$this->db->where(array('salon_account_id' =>'1501738222'));
			$getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
			//print_r($getConfigDetails->result_array());exit;

			if($getConfigDetails->num_rows>0)
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
					//echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
					//echo "<br>";
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
							
							$param2 = array('IncludeDeleted' => 0,'IncludeInactive' => 0); //parameters
							
							try{
								$result2 = $client2->call('GetEmployeeListing', array('parameters' => $param2), '', '', false, true);//METHOD WITH PARAMETERS
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
									//echo $result2['GetEmployeeListingResult']; exit;
									//RESULT OF APPOINTMENTS IN XML FORMAT
									try{
										$xml = new simpleXml2Array(utf8_encode($result2['GetEmployeeListingResult']),null);
										//$xml = new simpleXml2Array($result2['GetAllAppointmentsByDateResult']);
									}
									catch (Exception $e) {
		    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
									}
									
									$clientsIds = array();
									$dataArr = array();
									$allapptIds = array();

									$allempIIDs = array();
									//print_r($xml->arr);exit;
									//echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
									if(isset($xml->arr['EmpInfo']))
									{
										foreach($xml->arr['EmpInfo'] as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
										{
											//echo 'Employee Listing '.$appts['iid'][0].'<br>';

											$allempIIDs[] = $appts['iid'][0];
																							
											// GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
											$query = $this->db->get_where(MILL_EMPLOYEE_LISTING, array('iid' => $appts['iid'][0],'account_no' => $account_no));
											$apptsArray = $query->row_array();
											if(!empty($apptsArray))
											{
												if($apptsArray['iid']==$appts['iid'][0] && $apptsArray['account_no']==$account_no 
													&& $apptsArray['ccode']==$appts['ccode'][0] && $apptsArray['clastname']==$appts['clastname'][0] 
													&& $apptsArray['cfirstname']==$appts['cfirstname'][0] && $apptsArray['carea']==$appts['carea'][0]
													&& $apptsArray['cphone']==$appts['cphone'][0] 
												)
												{
													continue; //SAME DATA FOUND, SO CONTINUe with the loop
												}	
												else
												{
													//UPDATE DATA IN DB 
													$employee_data = array(
														'iid' => $appts['iid'][0],
														'ccode' => $appts['ccode'][0],
														'clastname' => $appts['clastname'][0],
														'cfirstname' => $appts['cfirstname'][0], //appointment Date
														'carea' => $appts['carea'][0],
														'cphone' => $appts['cphone'][0],
														'updatedDate' => date("Y-m-d H:i:s"),
														'insert_status' => 'Updated',
													);
													$this->db->where('iid',$appts['iid'][0]);
													$this->db->where('account_no',$account_no);
													$res = $this->db->update(MILL_EMPLOYEE_LISTING, $employee_data);
												}
											}
											else // INSERT APPOINTMENT DATA IN DB 
											{
												$employee_data = array(
													'account_no' => $account_no,
													'iid' => $appts['iid'][0],
													'ccode' => $appts['ccode'][0],
													'clastname' => $appts['clastname'][0],
													'cfirstname' => $appts['cfirstname'][0], //appointment Date
													'carea' => $appts['carea'][0],
													'cphone' => $appts['cphone'][0],
													'insertedDate' => date("Y-m-d H:i:s"),
													'updatedDate' => date("Y-m-d H:i:s"),
													'insert_status' => 'Inserted',
												);
												$res = $this->db->insert(MILL_EMPLOYEE_LISTING, $employee_data);
												$appts_id = $this->db->insert_id();
											}
										} //foreach ends of Package

										/*if(!empty($allempIIDs))
										{
											//print_r($allapptIds);exit;
											//TO SEARCH EXISTING APPOINTMENTS WITH NEW APPOINTMENTS
											$allDBempIIds = array();
											//$allapptIds[] = $appts['iid'][0];

											// GETS APPOINTMENTS DATA FROM DB COMPARING APPOINTMENT IID
											$this->db->where('account_no',$account_no);
											$allemployeeQuery = $this->db->get(MILL_EMPLOYEE_LISTING);
											$allemployeequeryArray = $allemployeeQuery->result_array();

											foreach($allemployeequeryArray as $allEmpId)
											{
												$allDBempIIds[] =  $allEmpId['iid'];
											}

											$result = array_diff($allDBempIIds,$allempIIDs);

											//echo "<pre>";print_r($result);echo "</pre>";

											if(!empty($result))
											{
												foreach($result as $resultss)
												{
													$this->db->where('iid',$resultss);
													$this->db->where('account_no',$account_no);
													$this->db->delete(MILL_EMPLOYEE_LISTING);
												}
											}
										}*/
									}
									else
									{
										echo "No Employee Data found in Millennium.";
									}
								}
							}
							$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
						}
					}
				}
			}
		}

		//Added By KRANTHI ON 28-11-2015
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
		function GetEmployeeScheduleHoursForCurrentYear($day="today",$account_no="")
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
			
           /* echo $getConfigDetails->num_rows();
            exit;*/
			if($getConfigDetails->num_rows()>0)
			{
             
				foreach($getConfigDetails->result_array() as $configDetails)
				{
					echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
					echo "<br>";

					$salon_id = $configDetails['salon_id'];

					$tempSalonArr = array();
					$tempSalonArr["salon_id"] = $salon_id;
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/getSalonInfoFromSalonId");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $tempSalonArr); 
					//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
					$salonResult=curl_exec($ch);
					//echo $salonResult;exit;
					$salonArray = json_decode($salonResult,true);
					//print_r($salon);exit;
					if(isset($salonArray["salon_info"]) && !empty($salonArray["salon_info"]))
					{
						$salon = $salonArray["salon_info"];
					}
					else
					{
						$salon = "";
					}

					if($day == "today") {
						$startDate = $currentDate;
						$endDate = $currentDate;
						$lastYearStartDate = date("Y-m-d",strtotime("-1 year"));
						$lastYearEndDate = date("Y-m-d",strtotime("-1 year"));
					} else if($day == "lastweek"){
						if(isset($salon["salon_start_day_of_week"]) && !empty($salon["salon_start_day_of_week"]))
						{
							$lastDayOfTheWeek = $salon["salon_start_day_of_week"];
							$startDate = date("Y-m-d",strtotime('last '.$lastDayOfTheWeek));
							
							$end_day_of_this_week = strtotime($startDate.' +6 days');
							$endDate = date('Y-m-d', $end_day_of_this_week);

							$last_year_start_date = strtotime($startDate.' -1 year');
						    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

						    $last_year_end_date = strtotime($endDate.' -1 year');
						    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
						}
						else
						{
							$startDate = date('Y-m-d', strtotime('-7 days'));
							//echo "<br>";
							$endDate = date('Y-m-d', strtotime('-1 days'));
							//exit;
							$last_year_start_date = strtotime($startDate.' -1 year');
						    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

						    $last_year_end_date = strtotime($endDate.' -1 year');
						    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
						}
					} else if($day == "lastmonth"){// last month 1st to last month last date
						$startDate = date("Y-m-d", strtotime("first day of last month"));
						$endDate = date("Y-m-d", strtotime("last day of last month"));

						$last_year_start_date = strtotime($startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

					    $last_year_end_date = strtotime($endDate.' -1 year');
					    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
					} else if($day == "Monthly") { // From current month 1st to current day
						//$startDate = "01-".date("M-y");
						$startDate = date("Y-m-")."01";
						$endDate = $currentDate;
						/*$startDate = "2016-06-01";
						$endDate = "2016-06-01";*/
						$last_year_start_date = strtotime($startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

					    $last_year_end_date = strtotime($endDate.' -1 year');
					    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
					} else if($day == "last90days") { // From 3month's 1st to last month last date
						$LastMonthFirst = date("Y-m-d", strtotime("first day of last month"));
						$startDate = date("Y-m-d", strtotime($LastMonthFirst. " -2 months"));
						$endDate = date("Y-m-d", strtotime("last day of last month"));

						$last_year_start_date = strtotime($startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

					    $last_year_end_date = strtotime($endDate.' -1 year');
					    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
					} else if($day == "Yearly") { // From 1st Month to Current month Current date
						$startDate = date("Y-")."01-01";
						$endDate = $currentDate;	

						$last_year_start_date = strtotime($startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

					    $last_year_end_date = strtotime($endDate.' -1 year');
					    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
					} else { // From 3month's 1st to this last month last date
						$startDate = $currentDate;
						$endDate = $currentDate;
						$lastYearStartDate = date("Y-m-d",strtotime("-1 year"));
						$lastYearEndDate = date("Y-m-d",strtotime("-1 year"));
					}
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

							$this->db->where(array('account_no' =>$account_no));
							$getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
							$packageListArr = $getPackageSeriesList->result_array();
							//print_r($packageListArr);exit;
							if(!empty($packageListArr)) 
							{
								foreach($packageListArr as $packageList)
								{
									pa($startDate.'---'.$endDate);
									$packageId = $packageList["iid"];
									//CURRENT YEAR DATA
									$params2['XmlIds'] = '<NewDataSet><Ids><Id>'.$packageId.'</Id></Ids></NewDataSet>';
									$params2['StartDate'] = $startDate;
									$params2['EndDate'] = $endDate;

									try{
										$result2 = $client2->call('GetEmployeeScheduleHours', array('parameters' => $params2), '', '', false, true);//METHOD WITH PARAMETERS
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
											//echo $result2['GetEmployeeScheduleHoursResult']; exit;
											//RESULT OF APPOINTMENTS IN XML FORMAT
											try{
												$xml = new simpleXml2Array(utf8_encode($result2['GetEmployeeScheduleHoursResult']),null);
												//$xml = new simpleXml2Array($result2['GetEmployeeScheduleHoursResult']);
											}
											catch (Exception $e) {
				    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
											}
											
											$clientsIds = array();
											$dataArr = array();
											$allapptIds = array();
											//print_r($xml->arr['EmployeeScheduleHours']);exit;
											//echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
											if(isset($xml->arr['EmployeeScheduleHours']))
											{
												//print_r($xml->arr['EmployeeScheduleHours']);exit;
												foreach($xml->arr['EmployeeScheduleHours'] as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
												{
													pa('WORKING HOURS '.$appts['nhours'][0].' '.$appts['cworktype'][0].' FOR IEMPID '.$appts['iempid'][0]);

													// GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
													$query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $appts['iempid'][0],'iworktypeid' => $appts['iworktypeid'][0],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $startDate,'end_date' => $endDate));
													$apptsArray = $query->row_array();
													// echo $this->db->last_query();
													if(!empty($apptsArray))
													{
														if($apptsArray['iempid']==$appts['iempid'][0] && $apptsArray['account_no']==$account_no 
															&& $apptsArray['cempcode']==$appts['cempcode'][0] && $apptsArray['cemplastname']==$appts['cemplastname'][0] 
															&& $apptsArray['cempfirstname']==$appts['cempfirstname'][0] && $apptsArray['iworktypeid']==$appts['iworktypeid'][0]
															&& $apptsArray['cworktype']==$appts['cworktype'][0] && $apptsArray['nhours']==$appts['nhours'][0]
														)
														{
															continue; //SAME DATA FOUND, SO CONTINUe with the loop
														}	
														else
														{
															//UPDATE DATA IN DB 
															$employee_data = array(
																'iempid' => $appts['iempid'][0],
																'cempcode' => $appts['cempcode'][0],
																'cemplastname' => $appts['cemplastname'][0],
																'cempfirstname' => $appts['cempfirstname'][0], //appointment Date
																'iworktypeid' => $appts['iworktypeid'][0],
																'cworktype' => $appts['cworktype'][0],
																'nhours' => $appts['nhours'][0],
																'dayRangeType' => $day,
																'start_date' => $startDate,
																'end_date' => $endDate,
																'updatedDate' => date("Y-m-d H:i:s"),
																'insert_status' => 'Updated',
															);
															$this->db->where('iempid',$appts['iempid'][0]);
															$this->db->where('iworktypeid',$appts['iworktypeid'][0]);
															$this->db->where('dayRangeType', $day);
						    								$this->db->where('start_date', $startDate);
						    								$this->db->where('end_date', $endDate);
															$this->db->where('account_no',$account_no);
															$res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
														}
													}
													else // INSERT APPOINTMENT DATA IN DB 
													{
														$employee_data = array(
															'account_no' => $account_no,
															'iempid' => $appts['iempid'][0],
															'cempcode' => $appts['cempcode'][0],
															'cemplastname' => $appts['cemplastname'][0],
															'cempfirstname' => $appts['cempfirstname'][0],
															'iworktypeid' => $appts['iworktypeid'][0],
															'cworktype' => $appts['cworktype'][0],
															'nhours' => $appts['nhours'][0],
															'dayRangeType' => $day,
															'start_date' => $startDate,
															'end_date' => $endDate,
															'insertedDate' => date("Y-m-d H:i:s"),
															'updatedDate' => date("Y-m-d H:i:s"),
															'insert_status' => 'Inserted',
														);
														$res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
														$appts_id = $this->db->insert_id();
													}
												} //foreach ends of Package
											}
											else
											{
												pa("No Employee Schedule Hours found in Millennium.");
											}
										}
									}
									$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);

									//CURRENT YEAR DATA ENDS
								} //Foreach of package series from db
							}
							else
							{
								//PACKAGE SERRIES LIST IS EMPTY
								pa("No Employee Schedule Hours found in Millennium.");
							}
						}
					}
				}
			}
		}

		function GetEmployeeScheduleHoursForLastYear($day="today",$account_no="")
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
			

			if($getConfigDetails->num_rows()>0)
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
					echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
					echo "<br>";
					$salon_id = $configDetails['salon_id'];

					$tempSalonArr = array();
					$tempSalonArr["salon_id"] = $salon_id;
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/getSalonInfoFromSalonId");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $tempSalonArr); 
					//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
					$salonResult=curl_exec($ch);
					//echo $salonResult;exit;
					$salonArray = json_decode($salonResult,true);
					//print_r($salon);exit;
					if(isset($salonArray["salon_info"]) && !empty($salonArray["salon_info"]))
					{
						$salon = $salonArray["salon_info"];
					}
					else
					{
						$salon = "";
					}

					if($day == "today") {
						$startDate = $currentDate;
						$endDate = $currentDate;
						$lastYearStartDate = date("Y-m-d",strtotime("-1 year"));
						$lastYearEndDate = date("Y-m-d",strtotime("-1 year"));
					} else if($day == "lastweek"){
						if(isset($salon["salon_start_day_of_week"]) && !empty($salon["salon_start_day_of_week"]))
						{
							$lastDayOfTheWeek = $salon["salon_start_day_of_week"];
							$startDate = date("Y-m-d",strtotime('last '.$lastDayOfTheWeek));
							
							$end_day_of_this_week = strtotime($startDate.' +6 days');
							$endDate = date('Y-m-d', $end_day_of_this_week);

							$last_year_start_date = strtotime($startDate.' -1 year');
						    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

						    $last_year_end_date = strtotime($endDate.' -1 year');
						    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
						}
						else
						{
							$startDate = date('Y-m-d', strtotime('-7 days'));
							//echo "<br>";
							$endDate = date('Y-m-d', strtotime('-1 days'));
							//exit;
							$last_year_start_date = strtotime($startDate.' -1 year');
						    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

						    $last_year_end_date = strtotime($endDate.' -1 year');
						    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
						}
					} else if($day == "lastmonth"){// last month 1st to last month last date
						$startDate = date("Y-m-d", strtotime("first day of last month"));
						$endDate = date("Y-m-d", strtotime("last day of last month"));

						$last_year_start_date = strtotime($startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

					    $last_year_end_date = strtotime($endDate.' -1 year');
					    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
					} else if($day == "Monthly") { // From current month 1st to current day
						//$startDate = "01-".date("M-y");
						$startDate = date("Y-m-")."01";
						$endDate = $currentDate;
						/*$startDate = "2016-06-01";
						$endDate = "2016-06-01";*/
						$last_year_start_date = strtotime($startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

					    $last_year_end_date = strtotime($endDate.' -1 year');
					    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
					} else if($day == "last90days") { // From 3month's 1st to last month last date
						$LastMonthFirst = date("Y-m-d", strtotime("first day of last month"));
						$startDate = date("Y-m-d", strtotime($LastMonthFirst. " -2 months"));
						$endDate = date("Y-m-d", strtotime("last day of last month"));

						$last_year_start_date = strtotime($startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

					    $last_year_end_date = strtotime($endDate.' -1 year');
					    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
					} else if($day == "Yearly") { // From 1st Month to Current month Current date
						$startDate = date("Y-")."01-01";
						$endDate = $currentDate;	

						$last_year_start_date = strtotime($startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

					    $last_year_end_date = strtotime($endDate.' -1 year');
					    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
					} else { // From 3month's 1st to this last month last date
						$startDate = $currentDate;
						$endDate = $currentDate;
						$lastYearStartDate = date("Y-m-d",strtotime("-1 year"));
						$lastYearEndDate = date("Y-m-d",strtotime("-1 year"));
					}
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

							$this->db->where(array('account_no' =>$account_no));
							$getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
							$packageListArr = $getPackageSeriesList->result_array();
							//print_r($packageListArr);exit;
							if(!empty($packageListArr)) 
							{
								foreach($packageListArr as $packageList)
								{
									$packageId = $packageList["iid"];
									pa($lastYearStartDate . '---' . $lastYearEndDate);
									//LAST YEAR DATA STARTS
									$params2['XmlIds'] = '<NewDataSet><Ids><Id>'.$packageId.'</Id></Ids></NewDataSet>';
									$params2['StartDate'] = $lastYearStartDate;
									$params2['EndDate'] = $lastYearEndDate;

									try{
										$result2 = $client2->call('GetEmployeeScheduleHours', array('parameters' => $params2), '', '', false, true);//METHOD WITH PARAMETERS
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
											//echo $result2['GetEmployeeScheduleHoursResult']; exit;
											//RESULT OF APPOINTMENTS IN XML FORMAT
											try{
												$xml = new simpleXml2Array(utf8_encode($result2['GetEmployeeScheduleHoursResult']),null);
												//$xml = new simpleXml2Array($result2['GetEmployeeScheduleHoursResult']);
											}
											catch (Exception $e) {
				    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
											}
											
											$clientsIds = array();
											$dataArr = array();
											$allapptIds = array();
											//print_r($xml->arr['EmployeeScheduleHours']);exit;
											//echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
											if(isset($xml->arr['EmployeeScheduleHours']))
											{
												//print_r($xml->arr['EmployeeScheduleHours']);exit;
												foreach($xml->arr['EmployeeScheduleHours'] as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
												{
													pa('WORKING HOURS '.$appts['nhours'][0].' '.$appts['cworktype'][0].' FOR IEMPID '.$appts['iempid'][0]);

													// GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
													$query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $appts['iempid'][0],'iworktypeid' => $appts['iworktypeid'][0],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $lastYearStartDate,'end_date' => $lastYearEndDate));
													$apptsArray = $query->row_array();
													if(!empty($apptsArray))
													{
														if($apptsArray['iempid']==$appts['iempid'][0] && $apptsArray['account_no']==$account_no 
															&& $apptsArray['cempcode']==$appts['cempcode'][0] && $apptsArray['cemplastname']==$appts['cemplastname'][0] 
															&& $apptsArray['cempfirstname']==$appts['cempfirstname'][0] && $apptsArray['iworktypeid']==$appts['iworktypeid'][0]
															&& $apptsArray['cworktype']==$appts['cworktype'][0] && $apptsArray['nhours']==$appts['nhours'][0]
														)
														{
															continue; //SAME DATA FOUND, SO CONTINUe with the loop
														}	
														else
														{
															//UPDATE DATA IN DB 
															$employee_data = array(
																'iempid' => $appts['iempid'][0],
																'cempcode' => $appts['cempcode'][0],
																'cemplastname' => $appts['cemplastname'][0],
																'cempfirstname' => $appts['cempfirstname'][0], //appointment Date
																'iworktypeid' => $appts['iworktypeid'][0],
																'cworktype' => $appts['cworktype'][0],
																'nhours' => $appts['nhours'][0],
																'dayRangeType' => $day,
																'start_date' => $lastYearStartDate,
																'end_date' => $lastYearEndDate,
																'updatedDate' => date("Y-m-d H:i:s"),
																'insert_status' => 'Updated',
															);
															$this->db->where('iempid',$appts['iempid'][0]);
															$this->db->where('iworktypeid',$appts['iworktypeid'][0]);
															$this->db->where('dayRangeType', $day);
						    								$this->db->where('start_date', $lastYearStartDate);
						    								$this->db->where('end_date', $lastYearEndDate);
															$this->db->where('account_no',$account_no);
															$res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
														}
													}
													else // INSERT APPOINTMENT DATA IN DB 
													{
														$employee_data = array(
															'account_no' => $account_no,
															'iempid' => $appts['iempid'][0],
															'cempcode' => $appts['cempcode'][0],
															'cemplastname' => $appts['cemplastname'][0],
															'cempfirstname' => $appts['cempfirstname'][0],
															'iworktypeid' => $appts['iworktypeid'][0],
															'cworktype' => $appts['cworktype'][0],
															'nhours' => $appts['nhours'][0],
															'dayRangeType' => $day,
															'start_date' => $lastYearStartDate,
															'end_date' => $lastYearEndDate,
															'insertedDate' => date("Y-m-d H:i:s"),
															'updatedDate' => date("Y-m-d H:i:s"),
															'insert_status' => 'Inserted',
														);
														$res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
														$appts_id = $this->db->insert_id();
													}
												} //foreach ends of Package
											}
											else
											{
												pa("No Employee Schedule Hours found in Millennium.");
											}
										}
									}
									$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);
									//LAST YEAR DATA ENDS
								} //Foreach of package series from db
							}
							else
							{
								//PACKAGE SERRIES LIST IS EMPTY
								pa("No Employee Schedule Hours found in Millennium.");
							}
						}
					}
				}
			}
		}

		//Added By KRANTHI ON 28-11-2015
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
		function GetEmployeeScheduleHoursForMonthWise($day="today",$account_no="")
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
			if($day == "IndividualMonth") { // From 1st Month to Current month Current date
				//$startDate = date("Y")."-01-01";
				$startDate = date("Y-m")."-01";
				$endDate = $currentDate;

				/*$startDate = "2017-01-01";
				$endDate = "2017-01-31";*/

				$last_year_start_date = strtotime($startDate.' -1 year');
			    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

			    $last_year_end_date = strtotime($endDate.' -1 year');
			    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
			}

			if($getConfigDetails->num_rows>0)
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
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

							$begin = new DateTime($startDate);
							$end = new DateTime($endDate);
							//$end = $end->modify( '+1 day' );
							$interval = new DateInterval('P1M');
							$daterange = new DatePeriod($begin, $interval ,$end);

							foreach ($daterange as $key => $date) {
								$month = $date->format("m");
							    $month = ltrim($month, '0');
							    $monthName = $date->format("F");
							    $year = date("Y");
							    $firstDay = $date->format("Y-m-d");
								$lastDay = date('Y-m-t',strtotime($date->format("Y-m-d")));

								echo $firstDay.' '.$lastDay."<br>";

								$this->db->where(array('account_no' =>$account_no));
								$getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
								$packageListArr = $getPackageSeriesList->result_array();
								//print_r($packageListArr);exit;
								if(!empty($packageListArr)) 
								{
									foreach($packageListArr as $packageList)
									{
										$packageId = $packageList["iid"];
										//CURRENT YEAR DATA
										$params2['XmlIds'] = '<NewDataSet><Ids><Id>'.$packageId.'</Id></Ids></NewDataSet>';
										$params2['StartDate'] = $firstDay;
										$params2['EndDate'] = $lastDay;

										try{
											$result2 = $client2->call('GetEmployeeScheduleHours', array('parameters' => $params2), '', '', false, true);//METHOD WITH PARAMETERS
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
												//echo $result2['GetEmployeeScheduleHoursResult']; exit;
												//RESULT OF APPOINTMENTS IN XML FORMAT
												try{
													$xml = new simpleXml2Array(utf8_encode($result2['GetEmployeeScheduleHoursResult']),null);
													//$xml = new simpleXml2Array($result2['GetEmployeeScheduleHoursResult']);
												}
												catch (Exception $e) {
					    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
												}
												
												$clientsIds = array();
												$dataArr = array();
												$allapptIds = array();
												//print_r($xml->arr['EmployeeScheduleHours']);exit;
												//echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
												if(isset($xml->arr['EmployeeScheduleHours']))
												{
													//print_r($xml->arr['EmployeeScheduleHours']);exit;
													foreach($xml->arr['EmployeeScheduleHours'] as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
													{
														echo 'WORKING HOURS '.$appts['nhours'][0].' '.$appts['cworktype'][0].' FOR IEMPID '.$appts['iempid'][0].'<br>';

														// GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
														$query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $appts['iempid'][0],'iworktypeid' => $appts['iworktypeid'][0],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $firstDay,'end_date' => $lastDay));
														$apptsArray = $query->row_array();
														if(!empty($apptsArray))
														{
															if($apptsArray['iempid']==$appts['iempid'][0] && $apptsArray['account_no']==$account_no 
																&& $apptsArray['cempcode']==$appts['cempcode'][0] && $apptsArray['cemplastname']==$appts['cemplastname'][0] 
																&& $apptsArray['cempfirstname']==$appts['cempfirstname'][0] && $apptsArray['iworktypeid']==$appts['iworktypeid'][0]
																&& $apptsArray['cworktype']==$appts['cworktype'][0] && $apptsArray['nhours']==$appts['nhours'][0]
															)
															{
																continue; //SAME DATA FOUND, SO CONTINUe with the loop
															}	
															else
															{
																//UPDATE DATA IN DB 
																$employee_data = array(
																	'iempid' => $appts['iempid'][0],
																	'cempcode' => $appts['cempcode'][0],
																	'cemplastname' => $appts['cemplastname'][0],
																	'cempfirstname' => $appts['cempfirstname'][0], //appointment Date
																	'iworktypeid' => $appts['iworktypeid'][0],
																	'cworktype' => $appts['cworktype'][0],
																	'nhours' => $appts['nhours'][0],
																	'dayRangeType' => $day,
																	'start_date' =>  $firstDay,
																	'end_date' =>  $lastDay,
																	'updatedDate' => date("Y-m-d H:i:s"),
																	'insert_status' => 'Updated',
																);
																$this->db->where('iempid',$appts['iempid'][0]);
																$this->db->where('iworktypeid',$appts['iworktypeid'][0]);
																$this->db->where('dayRangeType', $day);
							    								$this->db->where('start_date', $firstDay);
							    								$this->db->where('end_date', $lastDay);
																$this->db->where('account_no',$account_no);
																$res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
															}
														}
														else // INSERT APPOINTMENT DATA IN DB 
														{
															$employee_data = array(
																'account_no' => $account_no,
																'iempid' => $appts['iempid'][0],
																'cempcode' => $appts['cempcode'][0],
																'cemplastname' => $appts['cemplastname'][0],
																'cempfirstname' => $appts['cempfirstname'][0],
																'iworktypeid' => $appts['iworktypeid'][0],
																'cworktype' => $appts['cworktype'][0],
																'nhours' => $appts['nhours'][0],
																'dayRangeType' => $day,
																'start_date' => $firstDay,
																'end_date' => $lastDay,
																'insertedDate' => date("Y-m-d H:i:s"),
																'updatedDate' => date("Y-m-d H:i:s"),
																'insert_status' => 'Inserted',
															);
															$res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
															$appts_id = $this->db->insert_id();
														}
													} //foreach ends of Package
												}
												else
												{
													echo "No Employee Schedule Hours found in Millennium."."<br>";
												}
											}
										}
										$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);

										//CURRENT YEAR DATA ENDS
									} //Foreach of package series from db
								}
								else
								{
									//PACKAGE SERRIES LIST IS EMPTY
									echo "No Employee Schedule Hours found in Millennium."."<br>";
								}
							}
						}//foreach for date ranges ends
					}
				}
			}
		}

		//Added By KRANTHI ON 28-11-2015
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
		function GetEmployeeScheduleHoursForMonthWiseLastYear($day="today",$account_no="")
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
			if($day == "IndividualMonth") { // From 1st Month to Current month Current date
				$startDate = date("Y")."-01-01";
				$endDate = date("Y")."-12-31";
				/*$startDate = date("Y-m")."-01";
				$endDate = $currentDate;	*/

				$last_year_start_date = strtotime($startDate.' -1 year');
			    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

			    $last_year_end_date = strtotime($endDate.' -1 year');
			    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
			}

			//echo $lastYearStartDate;exit;
			if($getConfigDetails->num_rows>0)
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
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
							//echo $lastYearStartDate;exit;
							$begin = new DateTime($lastYearStartDate);
							$end = new DateTime($lastYearEndDate);
							//$end = $end->modify( '+1 day' );
							$interval = new DateInterval('P1M');
							$daterange = new DatePeriod($begin, $interval ,$end);

							foreach ($daterange as $key => $date) {
								$month = $date->format("m");
							    $month = ltrim($month, '0');
							    $monthName = $date->format("F");
							    $year = date("Y");

							    $firstDay = $date->format("Y-m-d");
								$lastDay = date('Y-m-t',strtotime($date->format("Y-m-d")));

							    /*$last_year_start_date = strtotime($firstDayCurrentYear.' -1 year');
							    $firstDay = date('Y-m-d', $last_year_start_date);

							    $last_year_end_date = strtotime($lastDayCurrentYear.' -1 year');
							    $lastDay = date('Y-m-d', $last_year_end_date);*/

							    //$firstDay = $date->format("Y-m-d");
								//$lastDay = date('Y-m-t',strtotime($date->format("Y-m-d")));

							    echo $firstDay.' '.$lastDay."<br>";

								$this->db->where(array('account_no' =>$account_no));
								$getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
								$packageListArr = $getPackageSeriesList->result_array();
								//print_r($packageListArr);exit;
								if(!empty($packageListArr)) 
								{
									foreach($packageListArr as $packageList)
									{
										$packageId = $packageList["iid"];
										//CURRENT YEAR DATA
										$params2['XmlIds'] = '<NewDataSet><Ids><Id>'.$packageId.'</Id></Ids></NewDataSet>';
										$params2['StartDate'] = $firstDay;
										$params2['EndDate'] = $lastDay;

										try{
											$result2 = $client2->call('GetEmployeeScheduleHours', array('parameters' => $params2), '', '', false, true);//METHOD WITH PARAMETERS
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
												//echo $result2['GetEmployeeScheduleHoursResult']; exit;
												//RESULT OF APPOINTMENTS IN XML FORMAT
												try{
													$xml = new simpleXml2Array(utf8_encode($result2['GetEmployeeScheduleHoursResult']),null);
													//$xml = new simpleXml2Array($result2['GetEmployeeScheduleHoursResult']);
												}
												catch (Exception $e) {
					    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
												}
												
												$clientsIds = array();
												$dataArr = array();
												$allapptIds = array();
												//print_r($xml->arr['EmployeeScheduleHours']);exit;
												//echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
												if(isset($xml->arr['EmployeeScheduleHours']))
												{
													//print_r($xml->arr['EmployeeScheduleHours']);exit;
													foreach($xml->arr['EmployeeScheduleHours'] as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
													{
														echo 'WORKING HOURS '.$appts['nhours'][0].' '.$appts['cworktype'][0].' FOR IEMPID '.$appts['iempid'][0].'<br>';

														// GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
														$query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $appts['iempid'][0],'iworktypeid' => $appts['iworktypeid'][0],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $firstDay,'end_date' => $lastDay));
														$apptsArray = $query->row_array();
														if(!empty($apptsArray))
														{
															if($apptsArray['iempid']==$appts['iempid'][0] && $apptsArray['account_no']==$account_no 
																&& $apptsArray['cempcode']==$appts['cempcode'][0] && $apptsArray['cemplastname']==$appts['cemplastname'][0] 
																&& $apptsArray['cempfirstname']==$appts['cempfirstname'][0] && $apptsArray['iworktypeid']==$appts['iworktypeid'][0]
																&& $apptsArray['cworktype']==$appts['cworktype'][0] && $apptsArray['nhours']==$appts['nhours'][0]
															)
															{
																continue; //SAME DATA FOUND, SO CONTINUe with the loop
															}	
															else
															{
																//UPDATE DATA IN DB 
																$employee_data = array(
																	'iempid' => $appts['iempid'][0],
																	'cempcode' => $appts['cempcode'][0],
																	'cemplastname' => $appts['cemplastname'][0],
																	'cempfirstname' => $appts['cempfirstname'][0], //appointment Date
																	'iworktypeid' => $appts['iworktypeid'][0],
																	'cworktype' => $appts['cworktype'][0],
																	'nhours' => $appts['nhours'][0],
																	'dayRangeType' => $day,
																	'start_date' =>  $firstDay,
																	'end_date' =>  $lastDay,
																	'updatedDate' => date("Y-m-d H:i:s"),
																	'insert_status' => 'Updated',
																);
																$this->db->where('iempid',$appts['iempid'][0]);
																$this->db->where('iworktypeid',$appts['iworktypeid'][0]);
																$this->db->where('dayRangeType', $day);
							    								$this->db->where('start_date', $firstDay);
							    								$this->db->where('end_date', $lastDay);
																$this->db->where('account_no',$account_no);
																$res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
															}
														}
														else // INSERT APPOINTMENT DATA IN DB 
														{
															$employee_data = array(
																'account_no' => $account_no,
																'iempid' => $appts['iempid'][0],
																'cempcode' => $appts['cempcode'][0],
																'cemplastname' => $appts['cemplastname'][0],
																'cempfirstname' => $appts['cempfirstname'][0],
																'iworktypeid' => $appts['iworktypeid'][0],
																'cworktype' => $appts['cworktype'][0],
																'nhours' => $appts['nhours'][0],
																'dayRangeType' => $day,
																'start_date' => $firstDay,
																'end_date' => $lastDay,
																'insertedDate' => date("Y-m-d H:i:s"),
																'updatedDate' => date("Y-m-d H:i:s"),
																'insert_status' => 'Inserted',
															);
															$res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
															$appts_id = $this->db->insert_id();
														}
													} //foreach ends of Package
												}
												else
												{
													echo "No Employee Schedule Hours found in Millennium."."<br>";
												}
											}
										}
										$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);

										//CURRENT YEAR DATA ENDS
									} //Foreach of package series from db
								}
								else
								{
									//PACKAGE SERRIES LIST IS EMPTY
									echo "No Employee Schedule Hours found in Millennium."."<br>";
								}
							}
						}//foreach for date ranges ends
					}
				}
			}
		}

		//Added By KRANTHI ON 28-11-2015
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
		function GetEmployeeScheduleHoursForWeekWise($day="today",$account_no="")
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
			

			if($getConfigDetails->num_rows>0)
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
					echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
					echo "<br>";

					$salon_id = $configDetails['salon_id'];

					$tempSalonArr = array();
					$tempSalonArr["salon_id"] = $salon_id;

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/getSalonInfoFromSalonId");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $tempSalonArr); 
					//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
					$salonResult=curl_exec($ch);
					//echo $salonResult;exit;
					$salonArray = json_decode($salonResult,true);
					//print_r($salon);exit;
					if(isset($salonArray["salon_info"]) && !empty($salonArray["salon_info"]))
					{
						$salon = $salonArray["salon_info"];
					}
					else
					{
						$salon = "";
					}
					//echo $salon["salon_start_day_of_week"];exit;
					if($day == "IndividualWeek") { // From 1st Month to Current month Current date
						
					    $startDate = date("Y-m-")."01";
						$endDate = $currentDate;
						/*$startDate = "2016-06-01";
						$endDate = "2016-06-01";*/
						$last_year_start_date = strtotime($startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

					    $last_year_end_date = strtotime($endDate.' -1 year');
					    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
					}

					//echo $startDate.' '.$endDate;exit;
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

							$begin = new DateTime($startDate);
							$end = new DateTime($endDate);

							//$end = $end->modify( '+1 day' );
							$interval = new DateInterval('P1W');
							$daterange = new DatePeriod($begin, $interval ,$end);
							//$i=1;
							foreach ($daterange as $key => $date) {
								$week = $date->format("W");
								//$week = $week+1;
								//$week_number = "Week ".$i;
								
								$month = $date->format("m");
								$month = ltrim($month, '0');
								
								$monthName = $date->format("F");
								$year = date("Y");
								

								if(isset($salon["salon_start_day_of_week"]) && !empty($salon["salon_start_day_of_week"]))
								{
									/*$start_date = strtotime($date->format("Y-m-d"));
									$startLastDay = strtotime('last '.$salon["salon_start_day_of_week"], $start_date);
									$firstDay = date('Y-m-d', $startLastDay);*/

									$lastDayOfTheWeek = $salon["salon_start_day_of_week"];
									$firstDay = date("Y-m-d",strtotime('last '.$lastDayOfTheWeek));

									//echo $startDate;exit;
									$end_day_of_this_week = strtotime($firstDay.' +6 days');
									$lastDay = date('Y-m-d', $end_day_of_this_week);
								}
								else
								{
									$firstDay = $date->format("Y-m-d");
									$lastDay = date('Y-m-d',strtotime($date->format("Y-m-d") . "+6 days"));
								}

								//echo $firstDay.' '.$lastDay;exit;
								$this->db->where(array('account_no' =>$account_no));
								$getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
								$packageListArr = $getPackageSeriesList->result_array();
								//print_r($packageListArr);exit;
								if(!empty($packageListArr)) 
								{
									foreach($packageListArr as $packageList)
									{
										$packageId = $packageList["iid"];
										//CURRENT YEAR DATA
										$params2['XmlIds'] = '<NewDataSet><Ids><Id>'.$packageId.'</Id></Ids></NewDataSet>';
										$params2['StartDate'] = $firstDay;
										$params2['EndDate'] = $lastDay;

										try{
											$result2 = $client2->call('GetEmployeeScheduleHours', array('parameters' => $params2), '', '', false, true);//METHOD WITH PARAMETERS
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
												//echo $result2['GetEmployeeScheduleHoursResult']; exit;
												//RESULT OF APPOINTMENTS IN XML FORMAT
												try{
													$xml = new simpleXml2Array(utf8_encode($result2['GetEmployeeScheduleHoursResult']),null);
													//$xml = new simpleXml2Array($result2['GetEmployeeScheduleHoursResult']);
												}
												catch (Exception $e) {
					    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
												}
												
												$clientsIds = array();
												$dataArr = array();
												$allapptIds = array();
												//print_r($xml->arr['EmployeeScheduleHours']);exit;
												//echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
												if(isset($xml->arr['EmployeeScheduleHours']))
												{
													//print_r($xml->arr['EmployeeScheduleHours']);exit;
													foreach($xml->arr['EmployeeScheduleHours'] as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
													{
														echo 'WORKING HOURS '.$appts['nhours'][0].' '.$appts['cworktype'][0].' FOR IEMPID '.$appts['iempid'][0].'<br>';

														// GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
														$query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $appts['iempid'][0],'iworktypeid' => $appts['iworktypeid'][0],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $firstDay,'end_date' => $lastDay));
														$apptsArray = $query->row_array();
														if(!empty($apptsArray))
														{
															if($apptsArray['iempid']==$appts['iempid'][0] && $apptsArray['account_no']==$account_no 
																&& $apptsArray['cempcode']==$appts['cempcode'][0] && $apptsArray['cemplastname']==$appts['cemplastname'][0] 
																&& $apptsArray['cempfirstname']==$appts['cempfirstname'][0] && $apptsArray['iworktypeid']==$appts['iworktypeid'][0]
																&& $apptsArray['cworktype']==$appts['cworktype'][0] && $apptsArray['nhours']==$appts['nhours'][0]
															)
															{
																continue; //SAME DATA FOUND, SO CONTINUe with the loop
															}	
															else
															{
																//UPDATE DATA IN DB 
																$employee_data = array(
																	'iempid' => $appts['iempid'][0],
																	'cempcode' => $appts['cempcode'][0],
																	'cemplastname' => $appts['cemplastname'][0],
																	'cempfirstname' => $appts['cempfirstname'][0], //appointment Date
																	'iworktypeid' => $appts['iworktypeid'][0],
																	'cworktype' => $appts['cworktype'][0],
																	'nhours' => $appts['nhours'][0],
																	'dayRangeType' => $day,
																	'start_date' =>  $firstDay,
																	'end_date' =>  $lastDay,
																	'updatedDate' => date("Y-m-d H:i:s"),
																	'insert_status' => 'Updated',
																);
																$this->db->where('iempid',$appts['iempid'][0]);
																$this->db->where('iworktypeid',$appts['iworktypeid'][0]);
																$this->db->where('dayRangeType', $day);
							    								$this->db->where('start_date', $firstDay);
							    								$this->db->where('end_date', $lastDay);
																$this->db->where('account_no',$account_no);
																$res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
															}
														}
														else // INSERT APPOINTMENT DATA IN DB 
														{
															$employee_data = array(
																'account_no' => $account_no,
																'iempid' => $appts['iempid'][0],
																'cempcode' => $appts['cempcode'][0],
																'cemplastname' => $appts['cemplastname'][0],
																'cempfirstname' => $appts['cempfirstname'][0],
																'iworktypeid' => $appts['iworktypeid'][0],
																'cworktype' => $appts['cworktype'][0],
																'nhours' => $appts['nhours'][0],
																'dayRangeType' => $day,
																'start_date' => $firstDay,
																'end_date' => $lastDay,
																'insertedDate' => date("Y-m-d H:i:s"),
																'updatedDate' => date("Y-m-d H:i:s"),
																'insert_status' => 'Inserted',
															);
															$res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
															$appts_id = $this->db->insert_id();
														}
													} //foreach ends of Package
												}
												else
												{
													echo "No Employee Schedule Hours found in Millennium.";
												}
											}
										}
										$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);

										//CURRENT YEAR DATA ENDS
									} //Foreach of package series from db
								}
								else
								{
									//PACKAGE SERRIES LIST IS EMPTY
									echo "No Employee Schedule Hours found in Millennium.";
								}
							}
						}//foreach for date ranges ends
					}
				}
			}
		}

		//Added By KRANTHI ON 28-11-2015
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
		function GetEmployeeScheduleHoursForWeekWiseLastYear($day="today",$account_no="")
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
			

			if($getConfigDetails->num_rows>0)
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
					echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
					echo "<br>";

					$salon_id = $configDetails['salon_id'];

					$tempSalonArr = array();
					$tempSalonArr["salon_id"] = $salon_id;
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/getSalonInfoFromSalonId");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $tempSalonArr); 
					//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
					$salonResult=curl_exec($ch);
					//echo $salonResult;exit;
					$salonArray = json_decode($salonResult,true);
					//print_r($salon);exit;
					if(isset($salonArray["salon_info"]) && !empty($salonArray["salon_info"]))
					{
						$salon = $salonArray["salon_info"];
					}
					else
					{
						$salon = "";
					}

					if($day == "IndividualWeek") { // From 1st Month to Current month Current date
						$startDate = date("Y-m-")."01";
						$endDate = $currentDate;
						/*$startDate = "2016-06-01";
						$endDate = "2016-06-01";*/
						$last_year_start_date = strtotime($startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $last_year_start_date);

					    $last_year_end_date = strtotime($endDate.' -1 year');
					    $lastYearEndDate = date('Y-m-d', $last_year_end_date);
					    //$lastYearEndDate = date('Y-m-d', $last_year_end_date);
					}

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

							$begin = new DateTime($lastYearStartDate);
							$end = new DateTime($lastYearEndDate);

							//$end = $end->modify( '+1 day' );
							$interval = new DateInterval('P1W');
							$daterange = new DatePeriod($begin, $interval ,$end);
							//$i=1;
							foreach ($daterange as $key => $date) {
								$week = $date->format("W");
								//$week = $week+1;
								//$week_number = "Week ".$i;
								
								$month = $date->format("m");
								$month = ltrim($month, '0');
								
								$monthName = $date->format("F");
								$year = date("Y");
								/*$firstDay = $date->format("Y-m-d");
								$lastDay = date('Y-m-d',strtotime($date->format("Y-m-d") . "+6 days"));*/

								if(isset($salon["salon_start_day_of_week"]) && !empty($salon["salon_start_day_of_week"]))
								{
									$start_date = strtotime($date->format("Y-m-d"));
									$startLastDay = strtotime('last '.$salon["salon_start_day_of_week"], $start_date);
									$firstDay = date('Y-m-d', $startLastDay);
									//echo $startDate;exit;
									$end_day_of_this_week = strtotime($firstDay.' +6 days');
									$lastDay = date('Y-m-d', $end_day_of_this_week);
								}
								else
								{
									$firstDay = $date->format("Y-m-d");
									$lastDay = date('Y-m-d',strtotime($date->format("Y-m-d") . "+6 days"));
								}

								//echo $firstDay.' '.$lastDay;exit;

								$this->db->where(array('account_no' =>$account_no));
								$getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
								$packageListArr = $getPackageSeriesList->result_array();
								//print_r($packageListArr);exit;
								if(!empty($packageListArr)) 
								{
									foreach($packageListArr as $packageList)
									{
										$packageId = $packageList["iid"];
										//CURRENT YEAR DATA
										$params2['XmlIds'] = '<NewDataSet><Ids><Id>'.$packageId.'</Id></Ids></NewDataSet>';
										$params2['StartDate'] = $firstDay;
										$params2['EndDate'] = $lastDay;

										try{
											$result2 = $client2->call('GetEmployeeScheduleHours', array('parameters' => $params2), '', '', false, true);//METHOD WITH PARAMETERS
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
												//echo $result2['GetEmployeeScheduleHoursResult']; exit;
												//RESULT OF APPOINTMENTS IN XML FORMAT
												try{
													$xml = new simpleXml2Array(utf8_encode($result2['GetEmployeeScheduleHoursResult']),null);
													//$xml = new simpleXml2Array($result2['GetEmployeeScheduleHoursResult']);
												}
												catch (Exception $e) {
					    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
												}
												
												$clientsIds = array();
												$dataArr = array();
												$allapptIds = array();
												//print_r($xml->arr['EmployeeScheduleHours']);exit;
												//echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
												if(isset($xml->arr['EmployeeScheduleHours']))
												{
													//print_r($xml->arr['EmployeeScheduleHours']);exit;
													foreach($xml->arr['EmployeeScheduleHours'] as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
													{
														echo 'WORKING HOURS '.$appts['nhours'][0].' '.$appts['cworktype'][0].' FOR IEMPID '.$appts['iempid'][0].'<br>';

														// GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
														$query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $appts['iempid'][0],'iworktypeid' => $appts['iworktypeid'][0],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $firstDay,'end_date' => $lastDay));
														$apptsArray = $query->row_array();
														if(!empty($apptsArray))
														{
															if($apptsArray['iempid']==$appts['iempid'][0] && $apptsArray['account_no']==$account_no 
																&& $apptsArray['cempcode']==$appts['cempcode'][0] && $apptsArray['cemplastname']==$appts['cemplastname'][0] 
																&& $apptsArray['cempfirstname']==$appts['cempfirstname'][0] && $apptsArray['iworktypeid']==$appts['iworktypeid'][0]
																&& $apptsArray['cworktype']==$appts['cworktype'][0] && $apptsArray['nhours']==$appts['nhours'][0]
															)
															{
																continue; //SAME DATA FOUND, SO CONTINUe with the loop
															}	
															else
															{
																//UPDATE DATA IN DB 
																$employee_data = array(
																	'iempid' => $appts['iempid'][0],
																	'cempcode' => $appts['cempcode'][0],
																	'cemplastname' => $appts['cemplastname'][0],
																	'cempfirstname' => $appts['cempfirstname'][0], //appointment Date
																	'iworktypeid' => $appts['iworktypeid'][0],
																	'cworktype' => $appts['cworktype'][0],
																	'nhours' => $appts['nhours'][0],
																	'dayRangeType' => $day,
																	'start_date' =>  $firstDay,
																	'end_date' =>  $lastDay,
																	'updatedDate' => date("Y-m-d H:i:s"),
																	'insert_status' => 'Updated',
																);
																$this->db->where('iempid',$appts['iempid'][0]);
																$this->db->where('iworktypeid',$appts['iworktypeid'][0]);
																$this->db->where('dayRangeType', $day);
							    								$this->db->where('start_date', $firstDay);
							    								$this->db->where('end_date', $lastDay);
																$this->db->where('account_no',$account_no);
																$res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
															}
														}
														else // INSERT APPOINTMENT DATA IN DB 
														{
															$employee_data = array(
																'account_no' => $account_no,
																'iempid' => $appts['iempid'][0],
																'cempcode' => $appts['cempcode'][0],
																'cemplastname' => $appts['cemplastname'][0],
																'cempfirstname' => $appts['cempfirstname'][0],
																'iworktypeid' => $appts['iworktypeid'][0],
																'cworktype' => $appts['cworktype'][0],
																'nhours' => $appts['nhours'][0],
																'dayRangeType' => $day,
																'start_date' => $firstDay,
																'end_date' => $lastDay,
																'insertedDate' => date("Y-m-d H:i:s"),
																'updatedDate' => date("Y-m-d H:i:s"),
																'insert_status' => 'Inserted',
															);
															$res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $employee_data);
															$appts_id = $this->db->insert_id();
														}
													} //foreach ends of Package
												}
												else
												{
													echo "No Employee Schedule Hours found in Millennium.";
												}
											}
										}
										$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);

										//CURRENT YEAR DATA ENDS
									} //Foreach of package series from db
								}
								else
								{
									//PACKAGE SERRIES LIST IS EMPTY
									echo "No Employee Schedule Hours found in Millennium.";
								}
							}
						}//foreach for date ranges ends
					}
				}
			}
		}

		//Added By KRANTHI ON 28-11-2015
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
		function GetEmployeeSchedules($account_no="")
		{
			//echo "sfsdfsd";exit;
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit', '-1');
			require_once('lib/nusoap.php'); //SOAP LIBRARY FILE
			require_once('xml2arr.php'); 
			//require_once('salonevolve.pem');
			//TO GET CONFIG DETAILS FROM DB
			//$this->db->where(array('salon_account_id' =>'1501738222'));
			
			if(!empty($account_no)){
				$names = array($account_no);
				$this->db->where_in('salon_account_id', $names);
			} else {
				$names = array(1149088973,2064572705);
				$this->db->where_in('salon_account_id', $names);
			}

			
			$getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
			//print_r($getConfigDetails->result_array());exit;

			$currentDate = date("Y-m-d");
			

			//if($getConfigDetails->num_rows>0)
			if(!empty($getConfigDetails->result_array()))	
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
					echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
					echo "<br>";

					$salon_id = $configDetails['salon_id'];

					
					$startDate = $currentDate;
					$end_day_of_this_week = strtotime($startDate.' +1 year');
					$endDate = date('Y-m-d', $end_day_of_this_week);

					
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

							$this->db->where(array('account_no' =>$account_no));
							$getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
							$packageListArr = $getPackageSeriesList->result_array();
							//print_r($packageListArr);exit;
							if(!empty($packageListArr)) 
							{
								foreach($packageListArr as $packageList)
								{
									$packageId = $packageList["iid"];
									//CURRENT YEAR DATA
									$params2['XmlIds'] = '<NewDataSet><Ids><Id>'.$packageId.'</Id></Ids></NewDataSet>';
									$params2['StartDate'] = $startDate;
									$params2['EndDate'] = $endDate;

									try{
										$result2 = $client2->call('GetEmployeeSchedules', array('parameters' => $params2), '', '', false, true);//METHOD WITH PARAMETERS
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
											//echo $result2['GetEmployeeSchedulesResult']; exit;
											//RESULT OF APPOINTMENTS IN XML FORMAT
											try{
												$xml = new simpleXml2Array(utf8_encode($result2['GetEmployeeSchedulesResult']),null);
												//$xml = new simpleXml2Array($result2['GetEmployeeSchedulesResult']);
											}
											catch (Exception $e) {
				    							echo 'Caught exception2: ',  $e->getMessage(), "\n";
											}
											
											$clientsIds = array();
											$dataArr = array();
											$allapptIds = array();
											//print_r($xml->arr['EmpSchedule']);exit;
											//echo "<br/><br/><br/><br/>----------------------------------------------------------<br/><br/><br/>";
											if(isset($xml->arr['EmpSchedule']))
											{
												//print_r($xml->arr['EmployeeScheduleHours']);exit;
												foreach($xml->arr['EmpSchedule'] as $appts) //CONVERTED APPOINTMENTS XML TO ARRAY
												{
													//echo 'WORKING HOURS '.$appts['nhours'][0].' '.$appts['cworktype'][0].' FOR IEMPID '.$appts['iempid'][0].'<br>';

													if(isset($appts['ddate'][0]) && !empty($appts['ddate'][0])){
														$expDate = explode("T",$appts['ddate'][0]);
														$ddate = $expDate[0];
													} else {
														$ddate = "";
													}

													// GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
													$query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULES, array('iid' => $appts['iid'][0],'iempid' => $appts['iempid'][0],'iworktypeid' => $appts['iworktypeid'][0],'account_no' => $account_no,'ddate' => $ddate));
													$apptsArray = $query->row_array();
													
													if(!empty($apptsArray))
													{
														
														if($apptsArray['ddate']==$ddate && 
															$apptsArray['iempid']==$appts['iempid'][0] && 
															$apptsArray['cworktype']==trim($appts['cworktype'][0]) && 
															$apptsArray['iid']==$appts['iid'][0] && 
															$apptsArray['ilocationid']==$appts['ilocationid'][0] && 
															$apptsArray['icardid']==$appts['icardid'][0] && 
															$apptsArray['iworktypeid']==$appts['iworktypeid'][0] && 
															$apptsArray['ctimein']==$appts['ctimein'][0] && 
															$apptsArray['ctimeout']==$appts['ctimeout'][0] && 
															$apptsArray['igid']==$appts['igid'][0] && 
															$apptsArray['iresourceid']==$appts['iresourceid'][0] && 
															$apptsArray['tlastmodified']==$appts['tlastmodified'][0] 
														)
														{
															continue; //SAME DATA FOUND, SO CONTINUe with the loop
														}	
														else
														{
															//UPDATE DATA IN DB 
															$employee_data = array(
																'ddate' => $ddate,
																'iempid' => $appts['iempid'][0],
																'cworktype' => trim($appts['cworktype'][0]),
																'iid' => $appts['iid'][0],
																'ilocationid' => $appts['ilocationid'][0],
																'icardid' => $appts['icardid'][0],
																'iworktypeid' => $appts['iworktypeid'][0],
																'ctimein' => $appts['ctimein'][0],
																'ctimeout' => $appts['ctimeout'][0],
																'igid' => $appts['igid'][0],
																'iresourceid' => $appts['iresourceid'][0],
																'tlastmodified' => $appts['tlastmodified'][0],
																'updatedDate' => date("Y-m-d H:i:s"),
																'insert_status' => 'Updated',
															);
															$this->db->where('iid',$appts['iid'][0]);
															$this->db->where('iempid',$appts['iempid'][0]);
															$this->db->where('iworktypeid',$appts['iworktypeid'][0]);
															$this->db->where('ddate', $ddate);
						    								$this->db->where('account_no',$account_no);
															$res = $this->db->update(MILL_EMPLOYEE_SCHEDULES, $employee_data);
														}
													}
													else // INSERT APPOINTMENT DATA IN DB 
													{
														$employee_data = array(
															'account_no' => $account_no,
															'ddate' => $ddate,
															'iempid' => $appts['iempid'][0],
															'cworktype' => trim($appts['cworktype'][0]),
															'iid' => $appts['iid'][0],
															'ilocationid' => $appts['ilocationid'][0],
															'icardid' => $appts['icardid'][0],
															'iworktypeid' => $appts['iworktypeid'][0],
															'ctimein' => $appts['ctimein'][0],
															'ctimeout' => $appts['ctimeout'][0],
															'igid' => $appts['igid'][0],
															'iresourceid' => $appts['iresourceid'][0],
															'tlastmodified' => $appts['tlastmodified'][0],
															'insertedDate' => date("Y-m-d H:i:s"),
															'updatedDate' => date("Y-m-d H:i:s"),
															'insert_status' => 'Inserted',
														);
														$res = $this->db->insert(MILL_EMPLOYEE_SCHEDULES, $employee_data);
														$appts_id = $this->db->insert_id();
													}
												} //foreach ends of Package
											}
											else
											{
												echo "No Employee Schedules found in Millennium.";
											}
										}
									}
									$errorType = 'client code executed successfully'; //logToFile('errorFile.log', $errorType);

									//CURRENT YEAR DATA ENDS
								} //Foreach of package series from db
							}
							else
							{
								//PACKAGE SERRIES LIST IS EMPTY
								echo "No Employee Schedule Hours found in Millennium.";
							}
						}
					}
				}
			}
		}

		function wsEmployeeSchedules(){
			$data = array();
			$dataArray = array();
			if(isset($_POST['salon_id']) && !empty($_POST['salon_id']) && isset($_POST['staff_id']) && !empty($_POST['staff_id'])) {
				
				$this->db->where('salon_id', $_POST['salon_id']);
				$getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
				$configDetailsArray = $getConfigDetails->row_array();
				//print_r($getConfigDetails->result_array());exit;

				if(!empty($configDetailsArray) && !empty($configDetailsArray["salon_account_id"])){
					$account_no = $configDetailsArray["salon_account_id"];

					$query = $this->db->get_where(STAFF2_TABLE, array('account_no' => $account_no,'staff_id' => $_POST['staff_id']));
					$staffArray = $query->row_array();

					$today_date = date('Y-m-d');
					if(!empty($staffArray) && !empty($staffArray["emp_iid"])){
						$schedules = $this->db->get_where(MILL_EMPLOYEE_SCHEDULES, array('iempid' => $staffArray["emp_iid"],'account_no' => $account_no,'cworktype' => 'Work Time','ddate >=' => $today_date));
						$schedulesArray = $schedules->result_array();
						//print_r($schedulesArray);exit;

						if(!empty($schedulesArray)){
							foreach($schedulesArray as $schedule){
								//echo strlen($schedule['ctimein']);exit;
								$schedule['ctimein']= trim($schedule['ctimein']);
								$time_in_1 = substr($schedule['ctimein'], 0, 2); 
								$time_in_2 = substr($schedule['ctimein'], -2);
								$ampm = ($time_in_1>=12) ? 'PM' : 'AM' ;
								$time_in_1 = ($time_in_1>12) ? $time_in_1-12 : $time_in_1 ;
								$time_in_1 = (strlen($time_in_1) >= 2) ? $time_in_1 : '0'.$time_in_1;
								$time_in_1 = ($time_in_1 == '00') ? 12 : $time_in_1;
								$appt_in_time=$time_in_1 . ":" . $time_in_2 . " " . $ampm;
								$appointmentStart_mdy = date("m-d-Y",strtotime($schedule['ddate'])).' '.$appt_in_time;
								$appointmentStart_ymd = date("Y-m-d",strtotime($schedule['ddate'])).' '.$appt_in_time;
								$dayData["start_time"] = $appointmentStart_mdy;
								//echo $appointmentStart_mdy;exit;
								$schedule['ctimeout']= trim($schedule['ctimeout']);
								$time_out_1 = substr($schedule['ctimeout'], 0, 2); 
								$time_out_2 = substr($schedule['ctimeout'], -2);
								$ampm = ($time_out_1>=12) ? 'PM' : 'AM' ;
								$time_out_1 = ($time_out_1>12) ? $time_out_1-12 : $time_out_1 ;
								$time_out_1 = (strlen($time_out_1) >= 2) ? $time_out_1 : '0'.$time_out_1;
								$time_out_1 = ($time_out_1 == '00') ? 12 : $time_out_1;
								$appt_out_time=$time_out_1 . ":" . $time_out_2 . " " . $ampm;
								$appointmentEnd_mdy = date("m-d-Y",strtotime($schedule['ddate'])).' '.$appt_out_time;
								$appointmentEnd_ymd = date("Y-m-d",strtotime($schedule['ddate'])).' '.$appt_out_time;
								$dayData["end_time"] = $appointmentEnd_mdy;

								$dayData["ddate"] = $schedule['ddate'];

								$data[] = $dayData;
							}
							$dataArray["employee_schedules"] = $data;
							$dataArray["status"] = true;
							$dataArray["msg"] = "Employee schedules data found.";
							//print_r($data);exit;
						} else {
							$dataArray["employee_schedules"] = array();
							$dataArray["status"] = false;
							$dataArray["msg"] = "Employee schedules data not found.";
						}
					}else {
						$dataArray["employee_schedules"] = array();
						$dataArray["status"] = false;
						$dataArray["msg"] = "No staff found or please update emp iid in admin.";
					}
				} else {
					$dataArray["employee_schedules"] = array();
					$dataArray["status"] = false;
					$dataArray["msg"] = "No SDK details found.";
				}
			} else {
				$dataArray["employee_schedules"] = array();
				$dataArray["status"] = false;
				$dataArray["msg"] = "Please provide salon id or staff id.";
			}

			echo json_encode($dataArray);
		}
	}
?>