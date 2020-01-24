<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class staffReportsForDashboardBasedOnSkillSet extends CI_Controller
{
    
    // Define constant as per value;
    CONST INSERTED = 0;
    CONST UPDATED = 1;  
    
    public $salonAccountId;
    public $startDate;
    public $endDate;
    
    public $staffCalcData;
    
    public $currentDate;
    
    private $salonId;
    private $salonsInfo;
    private $dayRangeType; 
    
    private $__insideConfigArr;
    
    public  $colorFieldsArr = array('color','highlight','Retouch','Hi-Lites','Lo-Lites','Minking','Foils','Virgin','Single Process','Crown Highlight','Partial Highlight','Double Process','Glaze','Base Softening','Highlights','Frosting','Balayage','Special Effects','Colors','Coloring','Chemical','Hilite','Hilites','Hilight','High','Perm','Relaxer','Color Retouch','Full Highlight','Custom Color','Permanent Wave');
        
    public  $colorLikeStr =  '';
    
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('DashboardOwner_model');
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
    }
    
    /**
     * Default Index Fn
     */    
	public function index(){ print "Test";}
    
    /**
     * 
     * @param type $dayRangeType
     * @param type $s
     * @param type $e
     */
    public function __getStartEndDate($dayRangeType, $s = '', $e = '')
    {
        $this->dayRangeType =  $dayRangeType;
        
        switch ($this->dayRangeType) {
                        case TODAY:
                                    $this->startDate =  $this->currentDate;
                                    $this->endDate   =  $this->currentDate;
                            break;
                        case LASTWEEK:
                                   
                                 if(isset( $this->salonInfo['salon_info']["salon_start_day_of_week"]) && !empty($this->salonInfo['salon_info']["salon_start_day_of_week"]))
                                    {
                                        $lastDayOfTheWeek =  $this->salonInfo['salon_info']["salon_start_day_of_week"];
                                        $this->startDate = getDateFn(strtotime('last '.$lastDayOfTheWeek));
                                        $this->endDate = getDateFn(strtotime($this->startDate.' +6 days'));
                                    }
                                    else
                                    {
                                        $this->startDate = getDateFn(strtotime('-7 days'));
                                        $this->endDate = getDateFn(strtotime('-1 days'));
                                    }   
                            break;
                        case LASTMONTH:
                                    $this->startDate = getDateFn(strtotime("first day of last month"));
                                    $this->endDate = getDateFn(strtotime("last day of last month"));
                            break;
                        case MONTHLY:
                                   
                                    $this->startDate = getDateFn(strtotime("first day of this month"));
                                    $this->endDate = $this->currentDate;
                            break;
                        case LAST90DAYS:
                                   
                                    $LastMonthFirst = getDateFn(strtotime("first day of last month"));
                                    $this->startDate = getDateFn(strtotime($LastMonthFirst. " -2 months"));
                                    $this->endDate = getDateFn(strtotime("last day of last month"));
                            break;
                        case YEARLY:
                                   
                                    $this->startDate = getDateFn(strtotime('first day of January '.date('Y')));
                                    $this->endDate = $this->currentDate;
                            break;
                        case CUSTOMDATE:
                                    $this->startDate = $s;
                                    $this->endDate = $e;
                            break;
                            default:
                                    
                                    $this->startDate =  $this->currentDate;
                                    $this->endDate   =  $this->currentDate;
                            break;
                    }
    }
    
    
    private function __getInsideSqlQueryFn($salonAccountNo, $temp)
    {
        //GET NEW SERVICED CLIENT IDS FROM APPOINTMENTS
        //appts.EmployeeName = "'.$temp["staff_name"].'" and 
       /* $sql_get_new_clients_from_appts = $this->db->query('SELECT count(DISTINCT client.ClientId) as new_client_count FROM 
            '.MILL_CLIENTS_TABLE.' client 
            join '.MILL_PAST_APPTS_TABLE.' appts on appts.ClientId=client.ClientId 
            WHERE 
            appts.iempid = "'.$temp["staff_iid"].'" and 
            appts.AccountNo = "'.$salonAccountNo.'" and 
            appts.SlcStatus != "Deleted" and 
            client.AccountNo = "'.$salonAccountNo.'" and 
            str_to_date(appts.AppointmentDate, "%m/%d/%Y") >= "'.$this->currentDate.'" and 
            str_to_date(appts.AppointmentDate, "%m/%d/%Y") <= "'.$this->currentDate.'" and 
            client.clientFirstVistedDate =  client.clientLastVistedDate'

            )->row_array();*/
        $sql_get_new_clients_from_appts = $this->DB_ReadOnly->query('SELECT count(DISTINCT client.ClientId) as new_client_count FROM 
            '.MILL_CLIENTS_TABLE.' client 
            join '.MILL_PAST_APPTS_TABLE.' appts on appts.ClientId=client.ClientId 
            WHERE 
            appts.iempid = "'.$temp["staff_iid"].'" and 
            appts.AccountNo = "'.$salonAccountNo.'" and 
            appts.SlcStatus != "2" and 
            client.AccountNo = "'.$salonAccountNo.'" and 
            str_to_date(appts.AppointmentDate, "%m/%d/%Y") >= "'.$this->currentDate.'" and 
            str_to_date(appts.AppointmentDate, "%m/%d/%Y") <= "'.$this->currentDate.'" and 
            date(client.clientFirstVistedDate) >= "'.$this->startDate.'" and 
            date(client.clientFirstVistedDate) <= "'.$this->endDate.'" and appts.ClientId !="-999"')->row_array();
            
            pa($this->db->last_query(),'sql_get_new_clients_from_appts');

                
            $this->__insideConfigArr['new_guest_qty'] = ($sql_get_new_clients_from_appts)? array_column($sql_get_new_clients_from_appts, "new_client_count") : array();            
			
        //GET REPEATED SERVICED CLIENTS IDS FROM APPOINTMENTS
        $sql_get_repeated_clients_from_appts = $this->DB_ReadOnly->query('SELECT count(DISTINCT client.ClientId) as repeated_client_count FROM 
					'.MILL_CLIENTS_TABLE.' client 
					join '.MILL_PAST_APPTS_TABLE.' appts on appts.ClientId=client.ClientId 
					WHERE 
					appts.iempid = "'.$temp["staff_iid"].'" and 
					appts.AccountNo = "'.$salonAccountNo.'" and 
					appts.SlcStatus != "2" and 
					client.AccountNo = "'.$salonAccountNo.'" and 
					str_to_date(appts.AppointmentDate, "%m/%d/%Y") >= "'.$this->currentDate.'" and 
					str_to_date(appts.AppointmentDate, "%m/%d/%Y") <= "'.$this->currentDate.'" and 
					client.clientFirstVistedDate != client.clientLastVistedDate')->row_array();
            pa($this->DB_ReadOnly->last_query(),'sql_get_repeated_clients_from_appts');
        
                $this->__insideConfigArr['repeated_guest_qty'] = ($sql_get_repeated_clients_from_appts)? array_column($sql_get_repeated_clients_from_appts, "repeated_client_count") : array();                   
                
        return $this->__insideConfigArr;
    }
    
   
    
        
    public function getRetailPerServiceReports($temp)
		{
            $this->staffCalcData = array();
			$dataArray = array();
            
           // pa($this,'OBJ', true);
            
			//if(!empty($temp["salon_id"]) && !empty($temp["staff_iid"]) && !empty($temp["staff_iids"]) && !empty($temp["start_date"]) && !empty($temp["end_date"]))
            if(!empty($temp["salon_id"]) && !empty($temp["staff_iid"]) && !empty($temp["start_date"]) && !empty($temp["end_date"]))
			{
			    $salonDetailsArr = $this->Common_model
                                   ->getMillSdkConfigDetailsBy($temp["salon_id"])
                                   ->row_array();
                
                $salonAccountNo = isset($salonDetailsArr['salon_account_id']) ? $salonDetailsArr['salon_account_id']: '';
                $this->salonAccountId = $salonAccountNo;
                
                //TO GET EMPLOYEE DATA 
				$whereEmployeeCondition = array('account_no' => $salonAccountNo,'iid' => $temp["staff_iid"]);
                $getEmployeeDetailsArr = $this->Common_model->getEmployeeListing($whereEmployeeCondition)->row_array();
                
                //TO GET ALL SERVICE SALES DETAILS
                $whereTotalServiceConditions = array('account_no' => $salonAccountNo , 'iempid' =>$temp["staff_iid"] ,'tdatetime >=' =>$this->startDate, 'tdatetime <=' =>$this->endDate);
                $getTotalServiceSales = $this->DashboardOwner_model
                                        ->getTotalServiceSales($whereTotalServiceConditions)
                                        ->row_array();
               // pa($this->db->last_query(),'TOTAL service');
                
                
                $this->staffCalcData['total_service_sales'] = (!empty($getTotalServiceSales['nprice']) && $getTotalServiceSales['nprice'] > 0 ) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales['nprice'],2)  : '0.00';
                
                //TO GET SERVICES INVOICES AND CLIENT IDS
                $whereServiceInvoiceAndClientIdConditions = array('account_no' => $salonAccountNo , 'iempid' =>$temp["staff_iid"] ,'tdatetime >=' =>$this->startDate, 'tdatetime <=' =>$this->endDate,'lrefund' => 'false');
                $serviceSalesInvoicesAndClientIds = $this->Common_model
                                        ->getServiceInvoiceAndClientIds($whereServiceInvoiceAndClientIdConditions)
                                        ->row_array();
                
				//TO GET SERVICE SALES INVOICES AND CLIENTIDS WHOSE PREBOOK IS TRUE
                $whereServiceInvoiceAndClientIdConditions = array('account_no' => $salonAccountNo , 'iempid' =>$temp["staff_iid"] ,'tdatetime >=' =>$this->startDate, 'tdatetime <=' =>$this->endDate,'lrefund' => 'false','lprebook' =>'true');
                $serviceSalesInvoicesAndClientIdsPrebookTrue = $this->Common_model
                                        ->getServiceInvoiceAndClientIds($whereServiceInvoiceAndClientIdConditions)
                                        ->row_array();
            
                //pa($this->db->last_query(),'service invioice and clientids with prebook');
                
                //TO GET TOTAL RETAIL SALES DETAILS
                $whereProductSalesConditions = array('account_no' => $salonAccountNo , 'iempid' =>$temp["staff_iid"] ,'tdatetime >=' =>$this->startDate, 'tdatetime <=' =>$this->endDate);
                $getTotalProductSales = $this->DashboardOwner_model
                                        ->getTotalProductSales($whereProductSalesConditions)
                                        ->row_array();
                
                //pa($this->db->last_query(),'total retail service');
                
                $this->staffCalcData['total_retail_price'] = (!empty($getTotalProductSales['nprice']) && $getTotalProductSales['nprice'] > 0 ) ? $this->Common_model->appCloudNumberFormat($getTotalProductSales['nprice'],2)  : '0.00';
                
                
				//TO GET RETAIL SALES Invoices and clientids
		        $whereProductInviousCliendIdsConditions = array('account_no' => $salonAccountNo , 'iempid' =>$temp["staff_iid"] ,'tdatetime >=' =>$this->startDate, 'tdatetime <=' =>$this->endDate,'lrefund' => 'false');
                $getProductSalesInvoicesAndClientIds = $this->DashboardOwner_model
                                        ->getProductInvoicesClientIdsCount($whereProductInviousCliendIdsConditions)
                                        ->row_array();
                
               // pa($this->db->last_query(),'retail service invoice and client');
         
                //avgServiceTicket
                $this->staffCalcData['avgServiceTicket'] = (!empty($getTotalServiceSales['nprice']) && !empty($serviceSalesInvoicesAndClientIds['invoice_count'])) ? $this->Common_model->appCloudNumberFormat($getTotalServiceSales["nprice"]/$serviceSalesInvoicesAndClientIds["invoice_count"],2)  : '0.00';
                
                //avgRetailTicket
				$this->staffCalcData['avgRetailTicket'] = (!empty($getTotalProductSales['nprice']) && !empty($getProductSalesInvoicesAndClientIds['invoice_count'])) ? $this->Common_model->appCloudNumberFormat($getTotalProductSales["nprice"]/$getProductSalesInvoicesAndClientIds["invoice_count"],2)  : '0.00';
                
				//RPST
                $this->staffCalcData['RPST'] = (!empty($getTotalProductSales['nprice']) && !empty($serviceSalesInvoicesAndClientIds['invoice_count'])) ? $this->Common_model->appCloudNumberFormat($getTotalProductSales["nprice"]/$serviceSalesInvoicesAndClientIds["invoice_count"],2)  : '0.00';
                
                //RPCT STARTS
                 $this->staffCalcData['RPCT'] = (!empty($getTotalProductSales['nprice']) && !empty($getProductSalesInvoicesAndClientIds['invoice_count'])) ? $this->Common_model->appCloudNumberFormat($getTotalProductSales["nprice"]/$getProductSalesInvoicesAndClientIds["invoice_count"],2)  : '0.00';
                
              
                 //RPRT STARTS
                $this->staffCalcData['RPRT'] = (!empty($getTotalProductSales['nprice']) && !empty($getProductSalesInvoicesAndClientIds['invoice_count'])) ? $this->Common_model->appCloudNumberFormat($getTotalProductSales["nprice"]/$getProductSalesInvoicesAndClientIds["invoice_count"],2)  : '0.00';
                
    			 //%PREBOOK STARTS
                $this->staffCalcData['prebook_percentage'] = (!empty($serviceSalesInvoicesAndClientIdsPrebookTrue['invoice_count']) && !empty($serviceSalesInvoicesAndClientIds['invoice_count'])) ? $this->Common_model->appCloudNumberFormat(($serviceSalesInvoicesAndClientIdsPrebookTrue["invoice_count"]/$serviceSalesInvoicesAndClientIds["invoice_count"])*100,2)  : '0.00';
				
                
				$this->__getInsideSqlQueryFn($salonAccountNo, $temp);
				 
                //GET TOTAL GUEST CLIENTS FROM APPOINTMENTS
				$this->staffCalcData['new_guest_qty'] = count($this->__insideConfigArr['new_guest_qty']);
                
                //GET REPEATED SERVICED CLIENTS IDS FROM APPOINTMENTS
				$this->staffCalcData['repeated_guest_qty'] = count($this->__insideConfigArr['repeated_guest_qty']);
                               
                //RETAIL PER SERVICE PER CURRENT DAY OF NEWLY ADDED SERVICE AND RETAIL
                //$this->__leaderBoardRetailPerService($temp);
                //     pa( $this ,'staffCalcData');
   		}
			else
			{
				//IF POST VALUES ARE EMPTY
				
			}
        }
    
    /**
     * 
     * @param type $day
     * @param type $para_salon_id
     */
    function setStaffDashboard($dayRangeType="Today", $salon_id="")
		{
        
            $this->currentDate = getDateFn();
			
            // GET START DATE AND END DATE AS PER PARAMETERS
            $this->__getStartEndDate($dayRangeType, '2017-03-01','2017-03-03');
            $this->salonsInfo = $this->Common_model->getAllSalons($salon_id);
            
			if(isset($this->salonsInfo["mill_salons"]) && !empty($this->salonsInfo["mill_salons"]))
			{
				foreach($this->salonsInfo["mill_salons"] as $salonsData)
				{
                    pa('',"Salon Reports Cron Dashboard--".$dayRangeType. "--" .$salonsData['salon_id'].' ---['.$salonsData['salon_name']."]");
                    //Get the all staff members by salon id
                    $getAllStaff = $this->Common_model->getAllStaffMembersBySalon($salonsData["salon_id"]);
                    
                    //pa($getAllStaff,'Staff');
                     
					if(isset($getAllStaff["getAllStaff"]) && !empty($getAllStaff["getAllStaff"]))
					{
						foreach($getAllStaff["getAllStaff"] as $staffMembers)
						{
							/*$salonStaffIds = array();
							$salonStaffIds["salon_id"] = $salonsData["salon_id"];
							$salonStaffIds["staff_id"] = $staffMembers["staff_id"];

							$salonStaffResultArray  = $this->Common_model->getMillAppointmentAndSalonInfo($salonStaffIds);

                            pa('','LoopStaff-----'.$salonStaffIds["staff_id"]);*/
                            
                            
                            $temps = array();
						 	$temps['staff_id'] = isset($staffMembers["staff_id"])? $staffMembers["staff_id"] : '';
						 	$temps['staff_iid'] = isset($staffMembers["emp_iid"])? $staffMembers["emp_iid"] : 0;
						 	//$temps['staff_iids'] = isset($salonStaffResultArray["emp_iids"])? $salonStaffResultArray["emp_iids"] : '';
						 	//$temps['staff_name'] = isset($salonStaffResultArray["staff_name"])? $salonStaffResultArray["staff_name"] : '';
						 	$temps['salon_id'] = $salonsData["salon_id"];
						 	
                            $temps['start_date'] = $this->startDate;
						 	$temps['end_date'] = $this->endDate;
                            
						 	//$temps["checked_out_client_id"] = isset($salonStaffResultArray["checked_out_client_id"])? $salonStaffResultArray["checked_out_client_id"] : 0;
                            
                           // GET CALCULATE VALEUES AS PER FORMULAS 
                            $this->getRetailPerServiceReports($temps);


    						$this->staffCalcData["salon_id"] = $salonsData["salon_id"];
							$this->staffCalcData["staff_id"] = $staffMembers["staff_id"];
                            
							//DATA TO BE INSERTED OR UPDATED
							$reportsWhere = array('salon_id' => $salonsData["salon_id"], 'staff_id' => $staffMembers["staff_id"]);
                            $reportsDataForSalon =  $this->DB_ReadOnly->select('*')
                                                            ->get_where(MILL_REPORT_BASED_ON_SKILLSET_CALCULATIONS_CRON,$reportsWhere)
                                                            ->row_array();
							
                            
                            if(!empty($reportsDataForSalon))
						    {
                                $diff_array = array_diff_assoc($this->staffCalcData,$reportsDataForSalon);
                                
                                pa($diff_array,"Diff Array");
                                                        
						    	if(empty($diff_array))
						    	{
						    		  continue; //SAME DATA FOUND, SO CONTINUe with the loop
						    	}
						    	else
						    	{
						    		//Update REPORT
						    		$this->staffCalcData["insert_status"] = staffReportsForDashboardBasedOnSkillSet::UPDATED;
									$this->staffCalcData["updatedDate"] = date("Y-m-d H:i:s");
						    		try {
									    
                                        $this->db->where('id', $reportsDataForSalon["id"]);
//							    		$this->db->where('salon_id', $salonStaffIds["salon_id"]);
//							    		$this->db->where('staff_id', $salonStaffIds["staff_id"]);
							    		$this->db->query('LOCK TABLE '.MILL_REPORT_BASED_ON_SKILLSET_CALCULATIONS_CRON.' WRITE');
										$this->db->trans_begin();
										$this->db->update(MILL_REPORT_BASED_ON_SKILLSET_CALCULATIONS_CRON, $this->staffCalcData);

                                        pa($this->db->last_query(),'Update SQL');
										if ($this->db->trans_status() === FALSE)
										{
										    $this->db->trans_rollback();
										}
										else
										{
										    $this->db->trans_commit();
										}
										$this->db->query('UNLOCK TABLES');
										echo "Reports Data updated Successfully."."<br>";
									} catch (Exception $e) {
									    echo 'Reports Update failed: ' . $e->getMessage()."<br>";
									}
						    	}
						    }
						    else
						    {
						    	//INSERT REPORT
						    	$this->staffCalcData["insert_status"] = staffReportsForDashboardBasedOnSkillSet::INSERTED;;
								$this->staffCalcData["insertedDate"] = date("Y-m-d H:i:s");
								$this->staffCalcData["updatedDate"] = date("Y-m-d H:i:s");
                                
								try {
									
									$this->db->query('LOCK TABLE '.MILL_REPORT_BASED_ON_SKILLSET_CALCULATIONS_CRON.' WRITE');

									$this->db->trans_begin();

									$this->db->insert(MILL_REPORT_BASED_ON_SKILLSET_CALCULATIONS_CRON, $this->staffCalcData);
                                    
									if ($this->db->trans_status() === FALSE)
									{
									    $this->db->trans_rollback();
									}
									else
									{
									    $this->db->trans_commit();
									}

									$this->db->query('UNLOCK TABLES');
                                    pa($this->db->last_query(),'INSERTSQL');
						    		echo "Reports Data inserted Successfully."."<br>";
						    	} catch (Exception $e) {
									echo 'Reports Insert failed: ' . $e->getMessage()."<br>";
								}
						    }
						} //STAFF for loop ends.
					}
					else
					{
						echo "No Staff Available.";
					}
				}
			}
			else
			{
				echo "No Salons Found.";
			}
		}
    
        

        private function __leaderBoardRetailPerService($temp)
        {
            //RETAIL PER SERVICE PER CURRENT DAY OF NEWLY ADDED SERVICE AND RETAIL
                //$checkedOutClientId = !empty($temp['checked_out_client_id']) ? $temp['checked_out_client_id'] : '';
            
				// sql need to update not correct
				$sql_get_retail_Of_Client = "SELECT count(DISTINCT retail.cinvoiceno) as invoice_count, sum(retail.nprice*retail.nquantity) as total_retail,client.Name FROM ".MILL_PRODUCT_SALES." retail 
					join ".MILL_CLIENTS_TABLE." client on client.ClientId=retail.iclientid 
					WHERE 
					retail.iempid = '".$temp['staff_iid']."' and 
					retail.account_no = '".$this->salonAccountId."' and 
					client.AccountNo = '".$this->salonAccountId."' and 
					retail.iclientid = '".$checkedOutClientId."' and 
					client.ClientId = '".$checkedOutClientId."' and 
					retail.tdatetime >= '".$this->currentDate."' and 
					retail.tdatetime <= '".$this->currentDate."' and 
					retail.lrefund = 'false'
					";
			
				$this->__insideConfigArr['retailperservice']['stylistRetail_result'] = $this->DB_ReadOnly->query($sql_get_retail_Of_Client)->result_array();

				$sql_get_service_Of_Client = "SELECT count(DISTINCT service.cinvoiceno) as invoice_count, sum(service.nprice*service.nquantity) as total_service,client.Name FROM ".MILL_SERVICE_SALES." service 
					join ".MILL_CLIENTS_TABLE." client on client.ClientId=service.iclientid 
					WHERE 
					service.iempid = '".$temp['staff_iid']."' and 
					service.account_no = '".$this->salonAccountId."' and 
					client.AccountNo = '".$this->salonAccountId."' and 
					service.iclientid = '".$checkedOutClientId."' and 
					client.ClientId = '".$checkedOutClientId."' and 
					service.tdatetime >= '".$this->currentDate."' and 
					service.tdatetime <= '".$this->currentDate."' and 
					service.lrefund = 'false'
					";
			
				$this->__insideConfigArr['retailperservice']['stylistService_result'] = $this->DB_ReadOnly->query($sql_get_service_Of_Client)->result_array();

				
				if(!empty($this->__insideConfigArr['retailperservice']['stylistRetail_result']["total_retail"]) && $this->__insideConfigArr['retailperservice']['stylistRetail_result']["total_retail"]!=NULL  && !empty($this->__insideConfigArr['retailperservice']['stylistService_result']["total_service"]) && $this->__insideConfigArr['retailperservice']['stylistService_result']["total_service"]!=NULL)
				{
					$this->staffCalcData["retailPerServiceValue"] = number_format(($this->__insideConfigArr['retailperservice']['stylistRetail_result']['total_retail']/$this->__insideConfigArr['retailperservice']['stylistService_result']["total_service"])*100, 2, '.', '');
				}
				else
				{
					$this->staffCalcData["retailPerServiceValue"] = '0.00';
				}

				if(!empty($this->__insideConfigArr['retailperservice']['stylistRetail_result']['Name']))
				{
					$this->staffCalcData["client_name"] = $this->__insideConfigArr['retailperservice']['stylistRetail_result']['Name'];
				}
				else if(!empty($this->__insideConfigArr['retailperservice']['stylistService_result']['Name']))
				{
					$this->staffCalcData["client_name"] = $this->__insideConfigArr['retailperservice']['stylistService_result']['Name'];
				}
				else
				{
					$this->staffCalcData["client_name"] = "";
				}
        }
        
}

