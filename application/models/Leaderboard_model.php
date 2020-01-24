<?php  
defined('BASEPATH') OR exit('No direct script access allowed');

   /**
   * Class Leaderboard_model
   * Contains all the leaderboard models
   */
class Leaderboard_model extends CI_Model {
	CONST LEADER_BOARD_STATUS = 1;
    CONST MIN_EMP_SERVICE_COUNT = 3;
    public function __construct() {
       parent::__construct();
       $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
    }
	private function __getStaffImage($employeeId ='' , $salon_id = '')
	    {
	        if(!empty($employeeId) && !empty($salon_id)){
	            $staffWhereCondition = array('emp_iid' => $employeeId, 'account_no' => $salon_id, 'emp_iid != ' => 0);
	            $getStaffImage =  $this->DB_ReadOnly->select('image')
	                                            ->get_where(STAFF2_TABLE,$staffWhereCondition)
	                                            ->row_array();
	            
	          
	            return isset($getStaffImage['image']) ? $getStaffImage['image'] : '';
	        }
	        
	    }

    public function getStaffLeaderboard($temp){
    	$this->salonAccountId = $temp['salonAccountNo'];
    	$this->insideConfigArr = array();
    	$this->insideConfigArr['rebookpercentage'] = array();
    	$tempArrRebookCount = array();
    	$this->staffCalcData = array();
        $sql_get_unique_clients_count_obj = $this->DB_ReadOnly->query("
                                            SELECT rebook.iempid,emp.name ,SUM(rebook.unique_client_count) as all_stafs_unique_client_count FROM
                                                (
                                                    SELECT tdatetime, iempid, count( DISTINCT iclientid ) AS unique_client_count 
                                                    FROM ".MILL_SERVICE_SALES." 
                                                    WHERE account_no = '".$this->salonAccountId."' 
                                                    AND   tdatetime >= '".$temp["start_date"]."'
                                                    AND tdatetime <= '".$temp["end_date"]."' 
                                                    AND lrefund = 'false' 
                                                    GROUP BY tdatetime,iempid 
                                                ) AS rebook 
                                            join ".STAFF2_TABLE." emp 
                                            on emp.emp_iid = rebook.iempid 
                                            where emp.account_no = '".$this->salonAccountId."' and
                                            emp.leader_board_status = ".self::LEADER_BOARD_STATUS."
                                            GROUP BY rebook.iempid     
                                            order by rebook.iempid");
                
                //echo $this->db->last_query();exit;
                $this->insideConfigArr['rebookpercentage']['total_unique_client_Arr'] = $sql_get_unique_clients_count_obj->result_array();
               
                
            if($this->insideConfigArr['rebookpercentage']['total_unique_client_Arr']){
                    
                $total_unique_clients_count = 0;
                foreach ( $this->insideConfigArr['rebookpercentage']['total_unique_client_Arr'] as  $totalClientCountperStaff){
                    $total_unique_clients_count += $totalClientCountperStaff['all_stafs_unique_client_count'];
                    $this->insideConfigArr['rebookpercentage']["staff"][$totalClientCountperStaff['iempid']] = $totalClientCountperStaff['name'];
                    $this->insideConfigArr['rebookpercentage']["total_unique_clients_count"][$totalClientCountperStaff['iempid']] = $totalClientCountperStaff['all_stafs_unique_client_count'];
                }
                

                $begin = new DateTime($temp["start_date"]);
                $end = new DateTime($temp["end_date"]);
                $end = $end->modify( '+1 day' );
                $interval = new DateInterval('P1D');
                $daterange = new DatePeriod($begin, $interval ,$end);
                
                foreach($daterange as $datess){
                    //echo $datess->format("Y-m-d") . "<br>";
                    
                    $this->DB_ReadOnly->select('iempid,GROUP_CONCAT( DISTINCT iclientid ) as DayWiseUniqueClient');
                    $this->DB_ReadOnly->group_by('iempid');
                    $this->insideConfigArr['rebookpercentage']['getServiceSalesUniqueClientIds'] = $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES, array('account_no' =>$this->salonAccountId ,'tdatetime' => $datess->format("Y-m-d"),'lrefund' => 'false'))->result_array();
                     
                    $plusFourMonthsDate = date('Y-m-d',strtotime($datess->format("Y-m-d") . "+120 days"));
                    
         
                    if(!empty($this->insideConfigArr['rebookpercentage']['getServiceSalesUniqueClientIds'])){
                        foreach($this->insideConfigArr['rebookpercentage']['getServiceSalesUniqueClientIds'] as $concatClientIds){
                            $this->insideConfigArr['rebookpercentage']['sql_get_clients_serviced_count'] = $this->DB_ReadOnly->query("SELECT future_clients . * ,emp.name,emp.emp_iid
                            FROM (
                                SELECT iempid,count(DISTINCT ClientId) as client_count FROM 
                                ".MILL_APPTS_TABLE."  
                                WHERE 
                                AccountNo = '".$this->salonAccountId."' and 
                                SlcStatus != 'Deleted' and 
                                str_to_date(AppointmentDate, '%m/%d/%Y') > '".$datess->format("Y-m-d")."' and 
                                str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$plusFourMonthsDate."' and 
                                DATE(  MillCreatedDate ) <=  '".$datess->format("Y-m-d")."' and 
                                LPrebook =  'true' and 
                                iempid = ".$concatClientIds['iempid']." and     
                                ClientId IN (".$concatClientIds['DayWiseUniqueClient'].")
                            )
                            AS future_clients join ".STAFF2_TABLE." emp on emp.emp_iid = future_clients.iempid where emp.account_no = '".$this->salonAccountId."' ")->row_array();
                            
                            
                            $this->insideConfigArr['rebookpercentage']['rebookClientArr'][$concatClientIds['iempid']][$datess->format("Y-m-d")] = isset($this->insideConfigArr['rebookpercentage']['sql_get_clients_serviced_count']['client_count'])? $this->insideConfigArr['rebookpercentage']['sql_get_clients_serviced_count']['client_count'] : 0;
                          
                           if(!isset($this->insideConfigArr['rebookpercentage']['total_unique_clients_count'][$concatClientIds['iempid']]))
                                  $tempArrRebookCount[$concatClientIds['iempid']] = 0;
                        }
                        
                    }
                }

             
                // merge those employee iid has no unique clients counts
                $this->insideConfigArr['rebookpercentage']['total_unique_clients_count'] = isset($this->insideConfigArr['rebookpercentage']['total_unique_clients_count']) && !empty($this->insideConfigArr['rebookpercentage']['total_unique_clients_count']) ? ($this->insideConfigArr['rebookpercentage']['total_unique_clients_count'] + $tempArrRebookCount) : 0 ;
                

                if(!empty($this->insideConfigArr['rebookpercentage']['rebookClientArr'])) {
                    foreach ($this->insideConfigArr['rebookpercentage']['rebookClientArr'] as $iempid => $rebookClient){
                                $rebookClientCountArr[$iempid] = array_sum($rebookClient);
                                $this->insideConfigArr['rebookpercentage']["staff_rebook_percentage_daywise"][$iempid] = $rebookClientCountArr[$iempid];
                                if($rebookClientCountArr[$iempid] > 0 && $this->insideConfigArr['rebookpercentage']["total_unique_clients_count"][$iempid] > 0)
                                    {
                                        $this->insideConfigArr['rebookpercentage']["rebook_percentage_order"][$iempid] = number_format(($rebookClientCountArr[$iempid]/$this->insideConfigArr['rebookpercentage']["total_unique_clients_count"][$iempid])*100, 2, '.', '');
                                    }
                    }
                }
                else
                {
                    $this->insideConfigArr['rebookpercentage']["rebook_percentage_order"] = array();
                }
                
                
                
                if(isset($this->insideConfigArr['rebookpercentage']['rebook_percentage_order']) && !empty($this->insideConfigArr['rebookpercentage']['rebook_percentage_order']))  { 
                    $staff_max_rebook_value = max($this->insideConfigArr['rebookpercentage']['rebook_percentage_order']);

                    $staff_maxs_rebook_ID = array_keys($this->insideConfigArr['rebookpercentage']['rebook_percentage_order'], $staff_max_rebook_value);

                  if(isset($staff_maxs_rebook_ID[0]))
                      $staff_max_rebook_name = $this->insideConfigArr['rebookpercentage']['staff'][$staff_maxs_rebook_ID[0]];
                }
            }
                if(!empty($staff_max_rebook_value) && !empty($staff_maxs_rebook_ID) && !empty($staff_max_rebook_name))
                {
                    $this->staffCalcData["highest_rebook_value"] = $staff_max_rebook_value;
                    $this->staffCalcData["highest_rebook_sold_employee"] = $staff_max_rebook_name;
                    if(isset($staff_maxs_rebook_ID[0]))
                        $this->staffCalcData["highest_rebook_sold_employee_image"] = $this->__getStaffImage($staff_maxs_rebook_ID[0], $this->salonAccountId);
                      
                }
                else
                {
                    $this->staffCalcData["highest_rebook_value"] = '0.00';
                    $this->staffCalcData["highest_rebook_sold_employee"] = "";
                    $this->staffCalcData["highest_rebook_sold_employee_image"] = "";
                }


            // service leaderboard
            
            $this->insideConfigArr['totalservicesales'] = array(); 
            $this->insideConfigArr['totalservicesales']['get_highest_service'] = $this->DB_ReadOnly->query("SELECT count(DISTINCT service.cinvoiceno) as invoice_count, sum(service.nprice*service.nquantity) as total_service,emp.name,emp.emp_iid FROM ".MILL_SERVICE_SALES." service 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid 
                    WHERE 
                    service.account_no = '".$this->salonAccountId."' and 
                    emp.account_no = '".$this->salonAccountId."' and
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS." and 
                    service.tdatetime >= '".$temp["start_date"]."' and 
                    service.tdatetime <= '".$temp["end_date"]."' GROUP BY service.iempid")->result_array();
                
                if(!empty($this->insideConfigArr['totalservicesales']['get_highest_service'])){

                    foreach($this->insideConfigArr['totalservicesales']['get_highest_service'] as $result){
                        $this->insideConfigArr['totalservicesales']['service_total'][] = $this->Common_model->appCloudNumberFormat($result['total_service'],2);
                        $this->insideConfigArr['totalservicesales']['iempname'][] = trim($result['name']);
                        $this->insideConfigArr['totalservicesales']['iid'][] = $result['emp_iid'];

                    }
                }
                
                if(isset($this->insideConfigArr['totalservicesales']['service_total']) && !empty($this->insideConfigArr['totalservicesales']['service_total']))
                {
                    $high_key_value = array_search(max($this->insideConfigArr['totalservicesales']['service_total']), $this->insideConfigArr['totalservicesales']['service_total']);
                    $this->staffCalcData["highest_service_revenue_value"] = $this->Common_model->appCloudNumberFormat($this->insideConfigArr['totalservicesales']['service_total'][$high_key_value],2);
                    $this->staffCalcData["highest_service_revenue_employee"] = $this->insideConfigArr['totalservicesales']['iempname'][$high_key_value];
                    $this->staffCalcData["highest_service_revenue_employee_image"] = $this->__getStaffImage($this->insideConfigArr['totalservicesales']['iid'][$high_key_value], $this->salonAccountId);
                }
                else
                {
                    $this->staffCalcData["highest_service_revenue_value"] = '0.00';
                    $this->staffCalcData["highest_service_revenue_employee"] = "";
                    $this->staffCalcData["highest_service_revenue_employee_image"] = "";
                }

                // retail leaderboard

                $this->insideConfigArr['totalretailsales'] = array();        
                $this->insideConfigArr['totalretailsales']['get_highest_retail'] = $this->DB_ReadOnly->query("SELECT count(DISTINCT retail.cinvoiceno) as invoice_count, sum(retail.nprice*retail.nquantity) as total_retail,emp.name,emp.emp_iid FROM ".MILL_PRODUCT_SALES." retail 
                    join ".STAFF2_TABLE." emp on emp.emp_iid=retail.iempid 
                    WHERE 
                    retail.account_no = '".$this->salonAccountId."' and
                    emp.leader_board_status = ".self::LEADER_BOARD_STATUS." and 
                    emp.account_no = '".$this->salonAccountId."' and 
                    retail.tdatetime >= '".$temp["start_date"]."' and 
                    retail.tdatetime <= '".$temp["end_date"]."' GROUP BY retail.iempid")->result_array();

            
                if(!empty($this->insideConfigArr['totalretailsales']['get_highest_retail'])){
                    foreach($this->insideConfigArr['totalretailsales']['get_highest_retail'] as $result){

                        $this->insideConfigArr['totalretailsales']['retail_total'][] =  $this->Common_model->appCloudNumberFormat($result['total_retail'],2);
                        $this->insideConfigArr['totalretailsales']['iempname'][] = trim($result['name']);
                        $this->insideConfigArr['totalretailsales']['iid'][] = $result['emp_iid'];

                    }
                    //print_r($this->insideConfigArr['totalretailsales']);exit;
                }
                
                if(isset($this->insideConfigArr['totalretailsales']['retail_total']) && !empty($this->insideConfigArr['totalretailsales']['retail_total']))
                {
                    $high_key_value = array_search(max($this->insideConfigArr['totalretailsales']['retail_total']), $this->insideConfigArr['totalretailsales']['retail_total']);

                    $this->staffCalcData["highest_product_revenue_value"] = $this->Common_model->appCloudNumberFormat($this->insideConfigArr['totalretailsales']['retail_total'][$high_key_value],2);
                    $this->staffCalcData["highest_product_revenue_employee"] = $this->insideConfigArr['totalretailsales']['iempname'][$high_key_value];
                    $this->staffCalcData["highest_product_revenue_employee_image"] = $this->__getStaffImage($this->insideConfigArr['totalretailsales']['iid'][$high_key_value], $this->salonAccountId);
                }
                else
                {
                    $this->staffCalcData["highest_product_revenue_value"] = '0.00';
                    $this->staffCalcData["highest_product_revenue_employee"] = "";
                    $this->staffCalcData["highest_product_revenue_employee_image"] = "";
                }
                
                return $this->staffCalcData;



    }
}  