<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
	
	class Wsimport_all_employee_data extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			$this->load->model('Common_model');
			$this->load->library('session');
			$this->load->library('form_validation');
			$this->load->library('pagination');
			$this->load->helper(array('form', 'url'));
			$this->load->database();
		}
		
		function index() {
			// wont do anything
		}
		function __getStartEndDate($day)
	    {
	    	$currentDate = date("Y-m-d");
	    	 $this->dayRangeType =  $day;
	    	 
	    	   switch ($this->dayRangeType) {
	    	   	 case "today":
						$this->startDate = $currentDate;
						$this->endDate = $currentDate;
						$this->lastYearStartDate = date("Y-m-d",strtotime("-1 year"));
						$this->lastYearEndDate = date("Y-m-d",strtotime("-1 year"));
				 break;
					case "lastweek":
					//echo $this->configDetails["salon_start_day_of_week"];
						if(isset($this->configDetails["salon_start_day_of_week"]) && !empty($this->configDetails["salon_start_day_of_week"]))
						{
							$lastDayOfTheWeek = $this->configDetails["salon_start_day_of_week"];
							
							$this->startDate = date("Y-m-d",strtotime('last '.$lastDayOfTheWeek));
							$end_day_of_this_week = strtotime($this->startDate.' +6 days');
							$this->endDate = date('Y-m-d', $end_day_of_this_week);

							$this->last_year_start_date = strtotime($this->startDate.' -1 year');
						    $this->lastYearStartDate = date('Y-m-d', $this->last_year_start_date);

						    $this->last_year_end_date = strtotime($this->endDate.' -1 year');
						    $this->lastYearEndDate = date('Y-m-d', $this->last_year_end_date);
						}
						else
						{
							$this->startDate = date('Y-m-d', strtotime('-7 days'));
							$this->endDate = date('Y-m-d', strtotime('-1 days'));
							
							$this->last_year_start_date = strtotime($this->startDate.' -1 year');
						    $this->lastYearStartDate = date('Y-m-d', $this->last_year_start_date);

						    $this->last_year_end_date = strtotime($this->endDate.' -1 year');
						    $this->lastYearEndDate = date('Y-m-d', $this->last_year_end_date);
						}
					 break;
					 case "lastmonth":// last month 1st to last month last date
						$this->startDate = date("Y-m-d", strtotime("first day of last month"));
						$this->endDate = date("Y-m-d", strtotime("last day of last month"));

						$this->last_year_start_date = strtotime($this->startDate.' -1 year');
					    $this->lastYearStartDate = date('Y-m-d', $this->last_year_start_date);

					    $this->last_year_end_date = strtotime($this->endDate.' -1 year');
					    $this->lastYearEndDate = date('Y-m-d', $this->last_year_end_date);
					 break;

					 case  "Monthly":  // From current month 1st to current day
					    $this->startDate = date("Y-m-")."01";
						$this->endDate = $currentDate;
						$this->last_year_start_date = strtotime($this->startDate.' -1 year');
					    $this->lastYearStartDate = date('Y-m-d',$this->last_year_start_date);
					    $this->last_year_end_date = strtotime($this->endDate.' -1 year');
					    $this->lastYearEndDate = date('Y-m-d', $this->last_year_end_date);
					 break;

					case "last90days":  // From 3month's 1st to last month last date
						$LastMonthFirst = date("Y-m-d", strtotime("first day of last month"));
						$this->startDate = date("Y-m-d", strtotime($LastMonthFirst. " -2 months"));
						$this->endDate = date("Y-m-d", strtotime("last day of last month"));

						$this->last_year_start_date = strtotime($this->startDate.' -1 year');
					    $this->lastYearStartDate = date('Y-m-d', $this->last_year_start_date);

					    $this->last_year_end_date = strtotime($this->endDate.' -1 year');
					    $this->lastYearEndDate = date('Y-m-d', $this->last_year_end_date);
					 break;
					case "Yearly": // From 1st Month to Current month Current date
						$this->startDate = date("Y-")."01-01";
						$this->endDate = $currentDate;	

						$this->last_year_start_date = strtotime($this->startDate.' -1 year');
					    $this->lastYearStartDate = date('Y-m-d', $this->last_year_start_date);

					    $this->last_year_end_date = strtotime($this->endDate.' -1 year');
					    $this->lastYearEndDate = date('Y-m-d', $this->last_year_end_date);
					 break;
					
					default: // From 3month's 1st to this last month last date
						$this->startDate = $currentDate;
						$this->endDate = $currentDate;
						$this->lastYearStartDate = date("Y-m-d",strtotime("-1 year"));
						$this->lastYearEndDate = date("Y-m-d",strtotime("-1 year"));
					
                     break;


	    	   }

	    }

		//Added By Swathi
		
		function GetEmployeeListing($account_no="")
		{ 
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit', '-1');
			
			if(!empty($account_no))
			{
				$this->db->where(array('salon_account_id' => $account_no));
			}
		
			$getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
		    //print_r($getConfigDetails->result_array());exit;

			if($getConfigDetails->num_rows()>0)
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
					$this->configDetails=$configDetails;
	                pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
	                $account_no = $this->salonAccountId = $configDetails['salon_account_id'];
	                $this->pemFilePath     =    base_url()."salonevolve.pem";
	                $this->salonMillIp     =    $configDetails['mill_ip_address'];
	                $this->salonMillGuid =    $configDetails['mill_guid'];
	                $this->salonMillUsername =    $configDetails['mill_username'];
	                $this->salonMillPassword =    $configDetails['mill_password'];
	                $this->salonMillSdkUrl = $configDetails['mill_url'];
					$millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
	                $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
	                $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
	                pa($this->millResponseSessionId,'Session',false);
	                $allempIIDs = array();
                    if($this->millResponseSessionId)
                    {
                    	$param2 = array('IncludeDeleted' => 0,'IncludeInactive' => 0);
                    	$this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetEmployeeListing',$param2);
                    	//pa($this->millResponseXml['EmpInfo']);
                    	 if(isset($this->millResponseXml['EmpInfo']))
                    	 {
                    	 	$empinf= array();
                    	    foreach($this->millResponseXml['EmpInfo'] as $empinfo)
	                          {
	                          	
	                       
	                          	$allempIIDs[] = $empinfo['iid'];

                    	 	   $query = $this->db->get_where(MILL_EMPLOYEE_LISTING, array('iid' => $empinfo['iid'],'account_no' => $account_no));
                    	 	//pa($this->db->last_query());
							   $empdbArray = $query->row_array();
							
								$empinf['iid']=$empinfo['iid'];
								$empinf['ccode']=trim($empinfo['ccode']);
								$empinf['clastname']=trim($empinfo['clastname']);
								$empinf['cfirstname']=is_array($empinfo['cfirstname']) ? trim($empinfo['cfirstname'][0]) : trim($empinfo['cfirstname']) ;
								
								$empinf['carea']=is_array($empinfo['carea']) ? trim($empinfo['carea'][0]) : trim($empinfo['carea']) ;
								
								$empinf['cphone']=is_array($empinfo['cphone']) ? trim($empinfo['cphone'][0]) : trim($empinfo['cphone']) ;
                                  
						
							 if(!empty($empdbArray))
	                           {
	                           
	                               $diff_array = array_diff_assoc($empinf, $empdbArray);
	                               if(empty($diff_array)) {
	                                        pa("No Updates");
	                                         continue; //SAME DATA FOUND, SO CONTINUe with the loop
	                                     }    
	                                      else
	                                       { 
	                                       	 
	                                        
	                                        /*pa($empinf,"sdk");
	                                        pa($empdbArray,"db");
	                                        pa($diff_array,"Diff Array");*/
	                                          
	                                          $diff_array['updatedDate'] = date("Y-m-d H:i:s");
	                                          $diff_array['insert_status'] = "Updated";
	                                          $this->db->where('account_no', $account_no);
	                                          $this->db->where('iid', $empinf['iid']);
	                                           $res = $this->db->update(MILL_EMPLOYEE_LISTING, $diff_array);
	                                           // pa($this->db->last_query());
	                                             pa("Updated data");
	                                                 
	                                                    
	                                                }
	                                            }
	                                            else // INSERT Employee DATA IN DB 
	                                            {
	                                                
	                                                $empinf['account_no']=$account_no;
	                                                $empinf['insertedDate']= date("Y-m-d H:i:s");
	                                                $empinf['updatedDate']=date("Y-m-d H:i:s");
	                                                $empinf['insert_status']="Inserted";
	                                            
	                                                $res = $this->db->insert(MILL_EMPLOYEE_LISTING, $empinf);
	                                              // pa($this->db->last_query());
	                                               
	                                                pa("inserted");
	                                            }
						


						   }//for each end
						 

                         }

                    }else{
                    	pa("session not set");
                    }
                }
            }
        }

		//Added By Swathi
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
	     function GetEmployeeScheduleHoursForCurrentYear($day="today",$account_no="")
	        {
                    error_reporting(E_ALL);
	                ini_set('display_errors', 1);
	                ini_set('memory_limit', '-1');
	                if(!empty($account_no))
					{
						$this->db->where(array('salon_account_id' => $account_no));
					}
	                $getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
	                if($getConfigDetails->num_rows()>0)
	                  {
	                        foreach($getConfigDetails->result_array() as $configDetails)
	                        {
	                        
	                            $this->configDetails=$configDetails;
	                            pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
	                            $account_no = $this->salonAccountId = $configDetails['salon_account_id'];
	                            $this->pemFilePath     =    base_url()."salonevolve.pem";
	                            $this->salonMillIp     =    $configDetails['mill_ip_address'];
	                            $this->salonMillGuid =    $configDetails['mill_guid'];
	                            $this->salonMillUsername =    $configDetails['mill_username'];
	                            $this->salonMillPassword =    $configDetails['mill_password'];
	                            $this->salonMillSdkUrl = $configDetails['mill_url'];
	                            // Database Log
	                            $log['AccountNo'] = $configDetails['salon_account_id'];
	                            $log['salon_id'] = $configDetails['salon_id'];
	                            $log['StartingTime'] = date('Y-m-d H:i:s');
	                            $log['whichCron'] = 'GetEmployeeScheduleHoursForCurrentYear';
	                            $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_data_new/GetEmployeeScheduleHoursForCurrentYear/'.$day.'/'.$account_no;
	                           // $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	                            $log['CronType'] = 0;
	                            $log['id'] = 0;
	                            $log_id = $this->Common_model->saveMillCronLogs($log);
	                            $this->__getStartEndDate($day);
	                            //MILLENIUM SDK REQUEST FOR SOAP CALL
	                            $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
	                            $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
	                            $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
	                            pa($this->millResponseSessionId,'Session',false);

	                             if($this->millResponseSessionId)
	                               {
			                            $this->db->where(array('account_no' =>$account_no));
			                            $getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
			                            $packageListArr = $getPackageSeriesList->result_array();
			                            $packageId=array();
			                            $xmlIds="";
	                                   // pa($packageListArr);
	                            	if(!empty($packageListArr)) 
	                                    {
	                                        foreach($packageListArr as $packageList)
	                                        {

	                                            $packageId = $packageList["iid"];
	                                            $xmlIds.= '<Ids><Id>'.$packageId.'</Id></Ids>';  
	                                        }
	                                        $Params = array('XmlIds' => '<NewDataSet>'.$xmlIds.'</NewDataSet>','StartDate' => $this->startDate,'EndDate' =>  $this->endDate);
	                                        $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetEmployeeScheduleHours',$Params);
	                                         //pa($this->millResponseXml);
	                                         if(isset($this->millResponseXml['EmployeeScheduleHours']))

	                                         { 
	                                         	$empsch= array();

	                                             foreach($this->millResponseXml['EmployeeScheduleHours'] as $Empschedule)
	                                             {
	                                             	if(is_array($Empschedule)){
	                                             		$empsch=$Empschedule;
	                                             	}else{
	                                             		$empsch=$this->millResponseXml['EmployeeScheduleHours'];
	                                             	}
	                                                 $empsch['nhours'] = number_format($empsch['nhours'], 2, '.', '');
	                                                 
	                                                 $query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $empsch['iempid'],'iworktypeid' => $empsch['iworktypeid'],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $this->startDate,'end_date' => $this->endDate));
	                                                 $empArray = $query->row_array();
	                                                 $empsch_array=array_map('trim',$empsch);
	                                              if(!empty($empArray))
	                                              {
	                                                $empArray['nhours'] = number_format($empArray['nhours'], 2, '.', '');
	                                                  
	                                                $diff_array = array_diff_assoc($empsch_array, $empArray);
	                                                

	                                                if(empty($diff_array))
	                                                {
	                                                    pa("No Updates");
	                                                    continue; //SAME DATA FOUND, SO CONTINUe with the loop
	                                                }    
	                                                else
	                                                { 
	                                                    /*pa($diff_array,"Diff Array");
	                                                    pa($empsch_array,"sdk");
	                                                    pa($empArray,"db");*/
	                                                    $diff_array['updatedDate'] = date("Y-m-d H:i:s");
	                                                    $diff_array['insert_status'] = "Updated";
	                                                    $this->db->where('account_no', $account_no);
	                                                    $this->db->where('iempid', $empArray['iempid']);
	                                                    $this->db->where('iworktypeid', $empArray['iworktypeid']);
	                                                    $this->db->where('dayRangeType',$day);
	                                                    $this->db->where('start_date',$this->startDate);
	                                                    $this->db->where('end_date', $this->endDate);
	                                                   
                                                        
	                                                    $res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $diff_array);
	                                                   // pa($this->db->last_query());
	                                                    pa("Updated data");
	                                                    
	                                                    
	                                                }
	                                            }
	                                            else // INSERT Employee DATA IN DB 
	                                            {
	                                                
	                                                $empsch_array['account_no']=$account_no;
	                                                $empsch_array['dayRangeType']= $day;
	                                                $empsch_array['start_date']= $this->startDate;
	                                                $empsch_array['end_date']= $this->endDate;
	                                                $empsch_array['insertedDate']= date("Y-m-d H:i:s");
	                                                $empsch_array['updatedDate']=date("Y-m-d H:i:s");
	                                                $empsch_array['insert_status']="Inserted";
	                                            
	                                                $res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $empsch_array);
	                                                // pa($this->db->last_query());
	                                                pa("inserted");
	                                            }
	                                    
	                                       }//for each end
	                                                            
	                                     } else{
	                                     	pa("No Employee Schedule Hours found in Millennium.");
	                                     }                               
	                                    
	                                }
	                                else
	                                {
	                                    //PACKAGE SERRIES LIST IS EMPTY
	                                    pa("No Employee Schedule Hours found in Millennium.");
	                                }
	                            }else{
	                                pa("Session Not set");
	                            }
	                    }
	                     $log['id'] = $log_id;
                         $log_id = $this->Common_model->saveMillCronLogs($log);
	                }
	         }


	   function GetEmployeeScheduleHoursForLastYear($day="today",$account_no="")
		{
			error_reporting(E_ALL);
	        ini_set('display_errors', 1);
	        ini_set('memory_limit', '-1');
	           if(!empty($account_no))
					{
						$this->db->where(array('salon_account_id' => $account_no));
					}
	              $getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);     
	         
	              if($getConfigDetails->num_rows()>0)
	                  {
	                    foreach($getConfigDetails->result_array() as $configDetails)
	                        {
	                            $this->configDetails=$configDetails;
	                            pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
	                            $account_no = $this->salonAccountId = $configDetails['salon_account_id'];
	                            $this->pemFilePath     =    base_url()."salonevolve.pem";
	                            $this->salonMillIp     =    $configDetails['mill_ip_address'];
	                            $this->salonMillGuid =    $configDetails['mill_guid'];
	                            $this->salonMillUsername =    $configDetails['mill_username'];
	                            $this->salonMillPassword =    $configDetails['mill_password'];
	                            $this->salonMillSdkUrl = $configDetails['mill_url'];
	                            // Database Log
	                            $log['AccountNo'] = $configDetails['salon_account_id'];
	                            $log['salon_id'] = $configDetails['salon_id'];
	                            $log['StartingTime'] = date('Y-m-d H:i:s');
	                            $log['whichCron'] = 'GetEmployeeScheduleHoursForLastYear';
	                            $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_data_new/GetEmployeeScheduleHoursForLastYear/'.$day.'/'.$account_no;
	                           // $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	                            $log['CronType'] = 0;
	                            $log['id'] = 0;
	                            $log_id = $this->Common_model->saveMillCronLogs($log);
	                            $this->__getStartEndDate($day);
	                            //echo $this->lastYearStartDate;
	                            //MILLENIUM SDK REQUEST FOR SOAP CALL
	                            $millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
	                            $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
	                            $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
	                            pa($this->millResponseSessionId,'Session',false);

	                            if($this->millResponseSessionId){
	                            $this->db->where(array('account_no' =>$account_no));
	                            $getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
	                            $packageListArr = $getPackageSeriesList->result_array();
	                            $packageId=array();
	                            $xmlIds="";
	                            //pa($packageListArr);
	                            if(!empty($packageListArr)) 
	                                    {
	                                        foreach($packageListArr as $packageList)
	                                        {

	                                            $packageId = $packageList["iid"];
	                                            $xmlIds.= '<Ids><Id>'.$packageId.'</Id></Ids>';  
	                                        }
	                                        //pa($xmlIds);
	                                        $Params = array('XmlIds' => '<NewDataSet>'.$xmlIds.'</NewDataSet>','StartDate' => $this->lastYearStartDate,'EndDate' =>  $this->lastYearEndDate);
	                                        $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetEmployeeScheduleHours',$Params);
	                                         //pa($this->millResponseXml);
	                                         if(isset($this->millResponseXml['EmployeeScheduleHours']))

	                                         { 
	                                         	$empsch= array();

	                                             foreach($this->millResponseXml['EmployeeScheduleHours'] as $Empschedule1)

	                                             {

	                                             	if(is_array($Empschedule1)){
	                                             		$empsch=$Empschedule1;
	                                             	}else{
	                                             		$empsch=$this->millResponseXml['EmployeeScheduleHours'];
	                                             	}

	                                                 $empsch['nhours'] = number_format($empsch['nhours'], 2, '.', '');
	                                                 $empsch_array=array_map('trim',$empsch);
													  //print_r($trimmed_array);
													 // exit;
	                                                 $query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $empsch_array['iempid'],'iworktypeid' => $empsch_array['iworktypeid'],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $this->lastYearStartDate,'end_date' => $this->lastYearEndDate));
	                                             $empArray = $query->row_array();
	                                             //pa($this->db->last_query());

	                                           // pa($Empschedule,"sdk");
	                                            //exit;
	                                          //   pa($empArray,"db");
	                                            if(!empty($empArray))
	                                              {
	                                                  $empArray['nhours'] = number_format($empArray['nhours'], 2, '.', '');
	                                                  
	                                                $diff_array = array_diff_assoc($empsch_array, $empArray);
	                                               

	                                                if(empty($diff_array))
	                                                {
	                                                    pa("No Updates");
	                                                    continue; //SAME DATA FOUND, SO CONTINUe with the loop
	                                                }    
	                                                else
	                                                { 
	                                                    /*pa($diff_array,"Diff Array");
	                                                    pa($empsch_array,"sdk");
	                                                    pa($empArray,"db");*/
	                                                    $diff_array['updatedDate'] = date("Y-m-d H:i:s");
	                                                    $diff_array['insert_status'] = "Updated";
	                                                    $this->db->where('account_no', $account_no);
	                                                    $this->db->where('iempid', $empArray['iempid']);
	                                                    $this->db->where('iworktypeid', $empArray['iworktypeid']);
	                                                    $this->db->where('dayRangeType',$day);
	                                                    $this->db->where('start_date',$this->lastYearStartDate);
	                                                    $this->db->where('end_date', $this->lastYearEndDate);
	                                                   
                                                         //exit;
	                                                    $res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $diff_array);
	                                                   pa($this->db->last_query());
	                                                    pa("Updated data");
	                                                    //exit;
	                                                    
	                                                }
	                                            }
	                                            else // INSERT Employee DATA IN DB 
	                                            {

	                                                
	                                                $empsch_array['account_no']=$account_no;
	                                                $empsch_array['dayRangeType']= $day;
	                                                $empsch_array['start_date']= $this->lastYearStartDate;
	                                                $empsch_array['end_date']= $this->lastYearEndDate;
	                                                $empsch_array['insertedDate']= date("Y-m-d H:i:s");
	                                                $empsch_array['updatedDate']=date("Y-m-d H:i:s");
	                                                $empsch_array['insert_status']="Inserted";
	                                            
	                                                $res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $empsch_array);
	                                                // pa($this->db->last_query());
	                                                pa("inserted data");
	                                            }
	                                    
	                                       }//for each end
	                                                            
	                                     } else{
	                                     	pa("No Employee Schedule Hours found in Millennium.");
	                                     }                               
	                                    
	                                }
	                                else
	                                {
	                                    //PACKAGE SERRIES LIST IS EMPTY
	                                    pa("No Employee Schedule Hours found in Millennium.");
	                                }
	                            }else{
	                                pa("Session Not set");
	                            }
	                    }
	                    $log['id'] = $log_id;
                        $log_id = $this->Common_model->saveMillCronLogs($log);
           
            }
		}


		//Added By Swathi 
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
		function GetEmployeeScheduleHoursForMonthWise($day="IndividualMonth",$account_no="")
		{
			error_reporting(E_ALL);
	        ini_set('display_errors', 1);
	        ini_set('memory_limit', '-1');
	       
	       if(!empty($account_no))
			  {
				$this->db->where(array('salon_account_id' => $account_no));
			   }
	        $getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
	       
			$currentDate = date("Y-m-d");

			if($day == "IndividualMonth") { // From 1st Month to Current month Current date
				//$startDate = date("Y")."-01-01";
				$this->startDate = date("Y")."-01-01";
				$this->endDate = $currentDate;

				$this->last_year_start_date = strtotime($this->startDate.' -1 year');
			    $this->lastYearStartDate = date('Y-m-d', $this->last_year_start_date);

			    $this->last_year_end_date = strtotime($this->endDate.' -1 year');
			    $this->lastYearEndDate = date('Y-m-d',$this->last_year_end_date);
			}

			if($getConfigDetails->num_rows()>0)
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
					$this->configDetails=$configDetails;
	                pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
	                $account_no = $this->salonAccountId = $configDetails['salon_account_id'];
	                $this->pemFilePath     =    base_url()."salonevolve.pem";
	                $this->salonMillIp     =    $configDetails['mill_ip_address'];
	                $this->salonMillGuid =    $configDetails['mill_guid'];
	                $this->salonMillUsername =    $configDetails['mill_username'];
	                $this->salonMillPassword =    $configDetails['mill_password'];
	                $this->salonMillSdkUrl = $configDetails['mill_url'];
	                            // Database Log
	                $log['AccountNo'] = $configDetails['salon_account_id'];
	                $log['salon_id'] = $configDetails['salon_id'];
	                $log['StartingTime'] = date('Y-m-d H:i:s');
	                $log['whichCron'] = 'GetEmployeeScheduleHoursForMonthWise';
	                $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_data_new/GetEmployeeScheduleHoursForMonthWise/'.$day.'/'.$account_no;
	                           // $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	                $log['CronType'] = 0;
	                $log['id'] = 0;
	                $log_id = $this->Common_model->saveMillCronLogs($log);
					$millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
	                $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
	                $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
	                 pa($this->millResponseSessionId,'Session',false);
					

							$begin = new DateTime($this->startDate);
							$end = new DateTime($this->endDate);
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

								//echo $firstDay.' '.$lastDay."<br>";
                                if($this->millResponseSessionId){
								$this->db->where(array('account_no' =>$account_no));
								$getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
								$packageListArr = $getPackageSeriesList->result_array();
								//print_r($packageListArr);exit;
								$packageId=array();
	                            $xmlIds="";
								if(!empty($packageListArr)) 
								{
									foreach($packageListArr as $packageList)
									{
										$packageId = $packageList["iid"];
										$xmlIds.= '<Ids><Id>'.$packageId.'</Id></Ids>';  
									}
									     $Params = array('XmlIds' => '<NewDataSet>'.$xmlIds.'</NewDataSet>','StartDate' =>$firstDay,'EndDate' =>  $lastDay);
	                                        $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetEmployeeScheduleHours',$Params);
	                                          if(isset($this->millResponseXml['EmployeeScheduleHours']))
	                                         { 
	                                         	$empsch= array();

	                                             foreach($this->millResponseXml['EmployeeScheduleHours'] as $Empschedule)
	                                             {
	                                             	if(is_array($Empschedule)){
	                                             		$empsch=$Empschedule;
	                                             	}else{
	                                             		$empsch=$this->millResponseXml['EmployeeScheduleHours'];
	                                             	}
	                                                 $empsch['nhours'] = number_format($empsch['nhours'], 2, '.', '');
	                                                 $empsch_array=array_map('trim',$empsch);
	                                                 $query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $empsch['iempid'],'iworktypeid' => $empsch['iworktypeid'],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $firstDay,'end_date' => $lastDay));
	                                             $empArray = $query->row_array();

	                                            if(!empty($empArray))
	                                              {
	                                                  $empArray['nhours'] = number_format($empArray['nhours'], 2, '.', '');
	                                                  
	                                                $diff_array = array_diff_assoc($empsch_array, $empArray);
	                                                

	                                                if(empty($diff_array))
	                                                {
	                                                    pa("No Updates");
	                                                    continue; //SAME DATA FOUND, SO CONTINUe with the loop
	                                                }    
	                                                else
	                                                { 
	                                                    /*pa($diff_array,"Diff Array");
	                                                    pa($empsch_array,"sdk");
	                                                    pa($empArray,"db");*/
	                                                    $diff_array['updatedDate'] = date("Y-m-d H:i:s");
	                                                    $diff_array['insert_status'] = "Updated";
	                                                    $this->db->where('account_no', $account_no);
	                                                    $this->db->where('iempid', $empArray['iempid']);
	                                                    $this->db->where('iworktypeid', $empArray['iworktypeid']);
	                                                    $this->db->where('dayRangeType',$day);
	                                                    $this->db->where('start_date',$firstDay);
	                                                    $this->db->where('end_date', $lastDay);
	                                                   
                                                         //exit;
	                                                    $res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $diff_array);
	                                                    //pa($this->db->last_query());
	                                                    pa("Updated data");
	                                                    //exit;
	                                                    
	                                                }
	                                            }
	                                            else // INSERT Employee DATA IN DB 
	                                            {
	                                                
	                                                $empsch_array['account_no']=$account_no;
	                                                $empsch_array['dayRangeType']= $day;
	                                                $empsch_array['start_date']= $firstDay;
	                                                $empsch_array['end_date']= $lastDay;
	                                                $empsch_array['insertedDate']= date("Y-m-d H:i:s");
	                                                $empsch_array['updatedDate']=date("Y-m-d H:i:s");
	                                                $empsch_array['insert_status']="Inserted";
	                                            
	                                                $res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $empsch_array);
	                                                // pa($this->db->last_query());
	                                                pa("inserted");
	                                            }
	                                    
	                                       }//for each end
	                                                            
	                                     } else{
	                                     	pa("No Employee Schedule Hours found in Millennium.");
	                                     }                               
	                                    
	                                }
	                                else
	                                {
	                                    //PACKAGE SERRIES LIST IS EMPTY
	                                    pa("No Employee Schedule Hours found in Millennium.");
	                                }
	                            
	                        }
	                   
	                }

				}         
				 $log['id'] = $log_id;
                 $log_id = $this->Common_model->saveMillCronLogs($log);      
			}
		}

		//Added By Swathi
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
		function GetEmployeeScheduleHoursForMonthWiseLastYear($day="IndividualMonth",$account_no="")
		{
			error_reporting(E_ALL);
	        ini_set('display_errors', 1);
	        ini_set('memory_limit', '-1');
	         if(!empty($account_no))
					{
						$this->db->where(array('salon_account_id' => $account_no));
					}
	         $getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
			$currentDate = date("Y-m-d");

			if($day == "IndividualMonth") { // From 1st Month to Current month Current date
				//$startDate = date("Y")."-01-01";
				$this->startDate = date("Y")."-01-01";
				$this->endDate  = date("Y")."-12-31";

				$this->last_year_start_date = strtotime($this->startDate.' -1 year');
			    $this->lastYearStartDate = date('Y-m-d', $this->last_year_start_date);

			    $this->last_year_end_date = strtotime($this->endDate.' -1 year');
			    $this->lastYearEndDate = date('Y-m-d',$this->last_year_end_date);
			}

			if($getConfigDetails->num_rows()>0)
			{

				foreach($getConfigDetails->result_array() as $configDetails)
				{
					$this->configDetails=$configDetails;
	                pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
	                $account_no = $this->salonAccountId = $configDetails['salon_account_id'];
	                $this->pemFilePath     =    base_url()."salonevolve.pem";
	                $this->salonMillIp     =    $configDetails['mill_ip_address'];
	                $this->salonMillGuid =    $configDetails['mill_guid'];
	                $this->salonMillUsername =    $configDetails['mill_username'];
	                $this->salonMillPassword =    $configDetails['mill_password'];
	                $this->salonMillSdkUrl = $configDetails['mill_url'];
	                            // Database Log
	                $log['AccountNo'] = $configDetails['salon_account_id'];
	                $log['salon_id'] = $configDetails['salon_id'];
	                $log['StartingTime'] = date('Y-m-d H:i:s');
	                $log['whichCron'] = 'GetEmployeeScheduleHoursForMonthWiseLastYear';
	                $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_data_new/GetEmployeeScheduleHoursForMonthWiseLastYear/'.$day.'/'.$account_no;
	                           // $log['CronUrl'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	                $log['CronType'] = 0;
	                $log['id'] = 0;
	                $log_id = $this->Common_model->saveMillCronLogs($log);
					$millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
	                $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
	                $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
	                 pa($this->millResponseSessionId,'Session',false);
					

							$begin = new DateTime($this->lastYearStartDate);
							$end = new DateTime($this->lastYearEndDate);
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

								//echo $firstDay.' '.$lastDay."<br>";
                                if($this->millResponseSessionId){
								$this->db->where(array('account_no' =>$account_no));
								$getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
								$packageListArr = $getPackageSeriesList->result_array();
								//print_r($packageListArr);exit;
								$packageId=array();
	                            $xmlIds="";
								if(!empty($packageListArr)) 
								{
									foreach($packageListArr as $packageList)
									{
										$packageId = $packageList["iid"];
										$xmlIds.= '<Ids><Id>'.$packageId.'</Id></Ids>';  
									}
									     $Params = array('XmlIds' => '<NewDataSet>'.$xmlIds.'</NewDataSet>','StartDate' =>$firstDay,'EndDate' =>  $lastDay);
	                                        $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetEmployeeScheduleHours',$Params);
	                                          if(isset($this->millResponseXml['EmployeeScheduleHours']))
	                                         { 
	                                         	$empsch= array();

	                                             foreach($this->millResponseXml['EmployeeScheduleHours'] as $Empschedule)
	                                             {
	                                             	if(is_array($Empschedule)){
	                                             		$empsch=$Empschedule;
	                                             	}else{
	                                             		$empsch=$this->millResponseXml['EmployeeScheduleHours'];
	                                             	}
	                                                 $empsch['nhours'] = number_format($empsch['nhours'], 2, '.', '');
	                                                 
	                                                 $query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $empsch['iempid'],'iworktypeid' => $empsch['iworktypeid'],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $firstDay,'end_date' => $lastDay));
	                                             $empArray = $query->row_array();
	                                             $empsch_array=array_map('trim',$empsch);

	                                            if(!empty($empArray))
	                                              {
	                                                  $empArray['nhours'] = number_format($empArray['nhours'], 2, '.', '');
	                                                  
	                                                $diff_array = array_diff_assoc($empsch_array, $empArray);
	                                                

	                                                if(empty($diff_array))
	                                                {
	                                                    pa("No Updates");
	                                                    continue; //SAME DATA FOUND, SO CONTINUe with the loop
	                                                }    
	                                                else
	                                                { 
	                                                    /*pa($diff_array,"Diff Array");
	                                                    pa($empsch_array,"sdk");
	                                                    pa($empArray,"db");*/
	                                                    $diff_array['updatedDate'] = date("Y-m-d H:i:s");
	                                                    $diff_array['insert_status'] = "Updated";
	                                                    $this->db->where('account_no', $account_no);
	                                                    $this->db->where('iempid', $empArray['iempid']);
	                                                    $this->db->where('iworktypeid', $empArray['iworktypeid']);
	                                                    $this->db->where('dayRangeType',$day);
	                                                    $this->db->where('start_date',$firstDay);
	                                                    $this->db->where('end_date', $lastDay);
	                                                   
                                                         //exit;
	                                                    $res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $diff_array);
	                                                    //pa($this->db->last_query());
	                                                    pa("Updated data");
	                                                    //exit;
	                                                    
	                                                }
	                                            }
	                                            else // INSERT Employee DATA IN DB 
	                                            {
	                                                
	                                                $empsch_array['account_no']=$account_no;
	                                                $empsch_array['dayRangeType']= $day;
	                                                $empsch_array['start_date']= $firstDay;
	                                                $empsch_array['end_date']= $lastDay;
	                                                $empsch_array['insertedDate']= date("Y-m-d H:i:s");
	                                                $empsch_array['updatedDate']=date("Y-m-d H:i:s");
	                                                $empsch_array['insert_status']="Inserted";
	                                            
	                                                $res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $empsch_array);
	                                                // pa($this->db->last_query());
	                                                pa("inserted");
	                                            }
	                                    
	                                       }//for each end
	                                                            
	                                     } else{
	                                     	pa("No Employee Schedule Hours found in Millennium.");
	                                     }                               
	                                    
	                                }
	                                else
	                                {
	                                    //PACKAGE SERRIES LIST IS EMPTY
	                                    pa("No Employee Schedule Hours found in Millennium.");
	                                }
	                            
	                        }
	                   
	                }

				}
				$log['id'] = $log_id;
                $log_id = $this->Common_model->saveMillCronLogs($log);     
			}
		}

		//Added By Swathi
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
		function GetEmployeeScheduleHoursForWeekWise($day="IndividualWeek",$account_no="")
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit', '-1');
		     if(!empty($account_no))
				{
					$this->db->where(array('salon_account_id' => $account_no));
				}
	         $getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
			 $currentDate = date("Y-m-d");
			  if($getConfigDetails->num_rows()>0)
             {
                foreach($getConfigDetails->result_array() as $configDetails)
                {
                    pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
                    $account_no = $this->salonAccountId = $configDetails['salon_account_id'];
                    $this->pemFilePath   =  base_url()."salonevolve.pem";
                    $this->salonMillIp   =  $configDetails['mill_ip_address'];
                    $this->salonMillGuid =  $configDetails['mill_guid'];
                    $this->salonMillUsername =  $configDetails['mill_username'];
                    $this->salonMillPassword =  $configDetails['mill_password'];
                    $this->salonMillSdkUrl = $configDetails['mill_url'];
                    $this->salonId = $configDetails['salon_id'];
                    //Database Log
                    $log['AccountNo'] = $configDetails['salon_account_id'];
                    $log['salon_id'] = $configDetails['salon_id'];
                    $log['StartingTime'] = date('Y-m-d H:i:s');
                    $log['whichCron'] = 'GetEmployeeScheduleHoursForWeekWise';
                    $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_data/GetEmployeeScheduleHoursForWeekWise/'.$day.'/'.$account_no;
                    $log['CronType'] = 0;
                    $log['id'] = 0;
                    $log_id = $this->Common_model->saveMillCronLogs($log);
                    if($day == "IndividualWeek") { // From 1st Month to Current month Current date
						
					    $this->startDate = date("Y-m-")."01";
						$this->endDate = $currentDate;
						
						$this->last_year_start_date = strtotime($this->startDate.' -1 year');
					    $lastYearStartDate = date('Y-m-d', $this->last_year_start_date);

					    $this->last_year_end_date = strtotime($this->endDate.' -1 year');
					    $this->lastYearEndDate = date('Y-m-d', $this->last_year_end_date);
					}
					$millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                                     
                    $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                     
                    $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                    pa($this->millResponseSessionId,'SESSION');

                    $startDayOfTheWeek = $configDetails["salon_start_day_of_week"];
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek)){
                        $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,date('Y'));
                    }
                    else{
                        $fourWeeksArr = getLast4WeekRanges(date('Y'));
                    }
                    //pa($fourWeeksArr);
                     foreach ($fourWeeksArr as $key => $date) {
                         $firstDay = $date['start_date'];
                          $lastDay = $date['end_date'];
                            if ($this->millResponseSessionId){
                              $this->db->where(array('account_no' =>$account_no));
							  $getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
							  $packageListArr = $getPackageSeriesList->result_array();
                               $xmlIds="";
                               if(!empty($packageListArr)) 
	                                    {
	                                      foreach($packageListArr as $packageList)
	                                        {
	                                            $packageId = $packageList["iid"];
	                                            $xmlIds.= '<Ids><Id>'.$packageId.'</Id></Ids>';  
	                                        }
	                                        $Params = array('XmlIds' => '<NewDataSet>'.$xmlIds.'</NewDataSet>','StartDate' =>$firstDay,'EndDate' => $lastDay);
	                                        $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetEmployeeScheduleHours',$Params);

	                                        //pa($this->millResponseXml);
	                                        if(isset($this->millResponseXml['EmployeeScheduleHours']))

	                                         { 
	                                         	$empsch= array();

	                                             foreach($this->millResponseXml['EmployeeScheduleHours'] as $Empschedule)
	                                             {
	                                             	if(is_array($Empschedule)){
	                                             		$empsch=$Empschedule;
	                                             	}else{
	                                             		$empsch=$this->millResponseXml['EmployeeScheduleHours'];
	                                             	}

	                                                 $empsch['nhours'] = number_format($empsch['nhours'], 2, '.', '');
	                                                 
	                                                 $query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $empsch['iempid'],'iworktypeid' => $empsch['iworktypeid'],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $firstDay,'end_date' => $lastDay));
	                                                 $empArray = $query->row_array();
	                                                 //pa($this->db->last_query());
	                                                  
	                                                    //exit;
	                                                 $empsch_array=array_map('trim',$empsch);
	                                                 
	                                              if(!empty($empArray))
	                                              {
	                                                $empArray['nhours'] = number_format($empArray['nhours'], 2, '.', '');
	                                                  
	                                                $diff_array = array_diff_assoc($empsch_array, $empArray);
	                                                 

	                                                if(empty($diff_array))
	                                                {
	                                                    pa("No Updates");
	                                                    continue; //SAME DATA FOUND, SO CONTINUe with the loop
	                                                }    
	                                                else
	                                                { 

	                                                   /* pa($diff_array,"Diff Array");
	                                                    pa($empsch_array,"sdk");
	                                                    pa($empArray,"db");*/
	                                                    $diff_array['updatedDate'] = date("Y-m-d H:i:s");
	                                                    $diff_array['insert_status'] = "Updated";
	                                                    $this->db->where('account_no', $account_no);
	                                                    $this->db->where('iempid', $empArray['iempid']);
	                                                    $this->db->where('iworktypeid', $empArray['iworktypeid']);
	                                                    $this->db->where('dayRangeType',$day);
	                                                    $this->db->where('start_date',$firstDay);
	                                                    $this->db->where('end_date', $lastDay);
	                                                   
                                                        
	                                                    $res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $diff_array);
	                                                    //pa($this->db->last_query());
	                                                    pa("Updated data");
	                                                    
	                                                    
	                                                }
	                                            }
	                                            else // INSERT Employee DATA IN DB 
	                                            {
	                                                
	                                                $empsch_array['account_no']=$account_no;
	                                                $empsch_array['dayRangeType']= $day;
	                                                $empsch_array['start_date']= $firstDay;
	                                                $empsch_array['end_date']=$lastDay;
	                                                $empsch_array['insertedDate']= date("Y-m-d H:i:s");
	                                                $empsch_array['updatedDate']=date("Y-m-d H:i:s");
	                                                $empsch_array['insert_status']="Inserted";
	                                            
	                                                $res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $empsch_array);
	                                               // pa($this->db->last_query());
	                                                pa("inserted");
	                                            }
	                                    
	                                       }//for each end
	                                                            
	                                     } else{
	                                     	pa("No Employee Schedule Hours found in Millennium.");
	                                     }
	                                    }

                          }else{
                          	pa("Seesion not set");
                          }
                      }
                 
                }//end of config for loop
                // Database Log
                  $log['id'] = $log_id;
                  $log_id = $this->Common_model->saveMillCronLogs($log); 

            }else{
            	 pa('Salons are inactive or invalid salon');
            }
		}

		//Added By Swathi
		//THE BELOW METHOD IS USED FOR GETTING SCHEDULE HOURS OF A EMPLOYEEE FROM MILLENIUM SDK FOR ALL SALONS
		function GetEmployeeScheduleHoursForWeekWiseLastYear($day="IndividualWeek",$account_no="")
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('memory_limit', '-1');
		     if(!empty($account_no))
				{
					$this->db->where(array('salon_account_id' => $account_no));
				}
	         $getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);
			 $currentDate = date("Y-m-d");
			  if($getConfigDetails->num_rows()>0)
             {
                foreach($getConfigDetails->result_array() as $configDetails)
                {

                    pa('',"".$configDetails['salon_name'].' ---['.$configDetails['salon_account_id']."]");
                    $account_no = $this->salonAccountId = $configDetails['salon_account_id'];
                    $this->pemFilePath   =  base_url()."salonevolve.pem";
                    $this->salonMillIp   =  $configDetails['mill_ip_address'];
                    $this->salonMillGuid =  $configDetails['mill_guid'];
                    $this->salonMillUsername =  $configDetails['mill_username'];
                    $this->salonMillPassword =  $configDetails['mill_password'];
                    $this->salonMillSdkUrl = $configDetails['mill_url'];
                    $this->salonId = $configDetails['salon_id'];
                    //Database Log
                    $log['AccountNo'] = $configDetails['salon_account_id'];
                    $log['salon_id'] = $configDetails['salon_id'];
                    $log['StartingTime'] = date('Y-m-d H:i:s');
                    $log['whichCron'] = 'GetEmployeeScheduleHoursForWeekWiseLastYear';
                    $log['CronUrl'] = MAIN_SERVER_URL.'Wsimport_all_employee_data/GetEmployeeScheduleHoursForWeekWiseLastYear/'.$day.'/'.$account_no;
                    $log['CronType'] = 0;
                    $log['id'] = 0;
                    $log_id = $this->Common_model->saveMillCronLogs($log);
                    if($day == "IndividualWeek") { // From 1st Month to Current month Current date
						
					    $this->startDate = date("Y-m-")."01";
						$this->endDate = $currentDate;
						
						$this->last_year_start_date = strtotime($this->startDate.' -1 year');
					    $this->lastYearStartDate = date('Y-m-d', $this->last_year_start_date);

					    $this->last_year_end_date = strtotime($this->endDate.' -1 year');
					    $this->lastYearEndDate = date('Y-m-d', $this->last_year_end_date);
					}
					$millloginDetails = array('User' => $this->salonMillUsername,'Password' => $this->salonMillPassword);
                                     
                    $this->nusoap_library = new Nusoap_library($this->salonMillSdkUrl.'?WSDL','wsdl','','','','');
                     
                    $this->millResponseSessionId = $this->nusoap_library->soap_library($this->salonMillSdkUrl,$this->salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);
                    pa($this->millResponseSessionId,'SESSION');

                    $startDayOfTheWeek = $configDetails["salon_start_day_of_week"];
                    if(isset($startDayOfTheWeek) && !empty($startDayOfTheWeek)){
                        $fourWeeksArr = getLast4BusinessWeeksRanges($startDayOfTheWeek,date("Y",strtotime("-1 year")));
                    }
                    else{
                        $fourWeeksArr = getLast4WeekRanges(date("Y",strtotime("-1 year")));
                    }
                    //pa($fourWeeksArr);
                    //exit;
                     foreach ($fourWeeksArr as $key => $date) {
                         $firstDay = $date['start_date'];
                          $lastDay = $date['end_date'];
                            if($this->millResponseSessionId){
                              $this->db->where(array('account_no' =>$account_no));
							  $getPackageSeriesList = $this->db->get(MILL_EMPLOYEE_LISTING);
							  $packageListArr = $getPackageSeriesList->result_array();
							  //pa($this->db->last_query());
                               $xmlIds="";
                               if(!empty($packageListArr)) 
	                                    {
	                                      foreach($packageListArr as $packageList)
	                                        {
	                                            $packageId = $packageList["iid"];
	                                            $xmlIds.= '<Ids><Id>'.$packageId.'</Id></Ids>';  
	                                        }
	                                        $Params = array('XmlIds' => '<NewDataSet>'.$xmlIds.'</NewDataSet>','StartDate' =>$firstDay,'EndDate' => $lastDay);
	                                        $this->millResponseXml = $this->nusoap_library->getMillMethodCall('GetEmployeeScheduleHours',$Params);

	                                       // pa($this->millResponseXml);
	                                        if(isset($this->millResponseXml['EmployeeScheduleHours']))

	                                         { 
	                                         	$empsch= array();

	                                             foreach($this->millResponseXml['EmployeeScheduleHours'] as $Empschedule)
	                                             {
	                                             	if(is_array($Empschedule)){
	                                             		$empsch=$Empschedule;
	                                             	}else{
	                                             		$empsch=$this->millResponseXml['EmployeeScheduleHours'];
	                                             	}

	                                                 $empsch['nhours'] = number_format($empsch['nhours'], 2, '.', '');
	                                                 
	                                                 $query = $this->db->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS, array('iempid' => $empsch['iempid'],'iworktypeid' => $empsch['iworktypeid'],'account_no' => $account_no,'dayRangeType' => $day,'start_date' => $firstDay,'end_date' => $lastDay));
	                                                 $empArray = $query->row_array();
	                                                 //pa($this->db->last_query());
	                                                  
	                                                    //exit;
	                                                 $empsch_array=array_map('trim',$empsch);
	                                                 
	                                              if(!empty($empArray))
	                                              {
	                                                $empArray['nhours'] = number_format($empArray['nhours'], 2, '.', '');
	                                                  
	                                                $diff_array = array_diff_assoc($empsch_array, $empArray);
	                                                 

	                                                if(empty($diff_array))
	                                                {
	                                                    pa("No Updates");
	                                                    continue; //SAME DATA FOUND, SO CONTINUe with the loop
	                                                }    
	                                                else
	                                                { 

	                                                   /* pa($diff_array,"Diff Array");
	                                                    pa($empsch_array,"sdk");
	                                                    pa($empArray,"db");*/
	                                                    $diff_array['updatedDate'] = date("Y-m-d H:i:s");
	                                                    $diff_array['insert_status'] = "Updated";
	                                                    $this->db->where('account_no', $account_no);
	                                                    $this->db->where('iempid', $empArray['iempid']);
	                                                    $this->db->where('iworktypeid', $empArray['iworktypeid']);
	                                                    $this->db->where('dayRangeType',$day);
	                                                    $this->db->where('start_date',$firstDay);
	                                                    $this->db->where('end_date', $lastDay);
	                                                   
                                                        
	                                                    $res = $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $diff_array);
	                                                    //pa($this->db->last_query());
	                                                    pa("Updated data");
	                                                    
	                                                    
	                                                }
	                                            }
	                                            else // INSERT Employee DATA IN DB 
	                                            {
	                                                
	                                                $empsch_array['account_no']=$account_no;
	                                                $empsch_array['dayRangeType']= $day;
	                                                $empsch_array['start_date']= $firstDay;
	                                                $empsch_array['end_date']=$lastDay;
	                                                $empsch_array['insertedDate']= date("Y-m-d H:i:s");
	                                                $empsch_array['updatedDate']=date("Y-m-d H:i:s");
	                                                $empsch_array['insert_status']="Inserted";
	                                            
	                                                $res = $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $empsch_array);
	                                               // pa($this->db->last_query());
	                                                pa("inserted");
	                                            }
	                                    
	                                       }//for each end
	                                                            
	                                     } else{
	                                     	pa("No Employee Schedule Hours found in Millennium.");
	                                     }
	                                    }

                          }else{
                          	pa("Seesion not set");
                          }
                      }
                 
                }//end of config for loop
                // Database Log
                  $log['id'] = $log_id;
                  $log_id = $this->Common_model->saveMillCronLogs($log); 

            }else{
            	 pa('Salons are inactive or invalid salon');
            }
		}


		//Added By Swathi
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