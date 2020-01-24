<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class WsSyncStaffWithPlusClouds extends CI_Controller
{
    /**
     * Default Fn call on execute controller 
     */
    function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
    }   
    
    /**
     * Default index Fn
     */
    public function index(){print "Test";}
    
    
    /**
     * Description: Update Staff by fetching from Salon plus server.
     */
    function updateStaff($account_no="")
    {	
    	if($account_no!=''){
             $account_no = salonWebappCloudDe($account_no);
        }
        if(!empty($account_no))
		{
			$names = array($account_no);
			$this->db->where_in('salon_account_id', $names);
		}     
       
		$getAllSalons = $this->db->select('salon_id')
                                    ->get(MILL_ALL_SDK_CONFIG_DETAILS)
                                    ->result_array();
                                
        $getAllSalonsIds = (isset($getAllSalons) && !empty($getAllSalons))? array_column($getAllSalons, 'salon_id'): array();
        $salonIdsInServer = (!empty($getAllSalonsIds)) ? implode(",",$getAllSalonsIds) : '';
        pa(GET_STAFF_FROM_PLUS_SERVER_URL);
        $getAllStaff = $this->Common_model->getCurlData(GET_STAFF_FROM_PLUS_SERVER_URL,
                    array('all_salon_ids' => $salonIdsInServer) );

            pa($getAllStaff,'$getAllStaff',false);
              
			
			if(isset($getAllStaff["staff"]) && !empty($getAllStaff["staff"]))
			{
				$allStaffIds = array();
				foreach($getAllStaff["staff"] as $staffMembers)
				{
					$allStaffIds[] = $staffMembers['staff_id'];

					//TO UPDATE ACCOUNT NUMBER IN STAFF2 TABLE
					$accDetailsArray = $this->db->select('salon_account_id')
                                                    ->get_where(MILL_ALL_SDK_CONFIG_DETAILS, array('salon_id' => $staffMembers['salon_id']))
                                                    ->row_array();
                    $accNo = (isset($accDetailsArray["salon_account_id"]) && !empty($accDetailsArray["salon_account_id"])) ?
                                $accNo = $accDetailsArray["salon_account_id"] : "";
                    
                    $staffArray = $this->db->select('*')
                                        ->get_where(STAFF2_TABLE, array('salon_id' => $staffMembers['salon_id'],'staff_id' => $staffMembers['staff_id']))
                                        ->row_array(); 
                    
					if(!empty($staffArray))
					{
                        $diff_array = array_diff_assoc($staffMembers,$staffArray);
                        
						if(empty($diff_array))
						{
                            pa('','no Staff diff with DB -- '.$staffMembers['salon_id']. ' -- ' .$staffMembers['staff_id'] );
							continue; //SAME DATA FOUND, SO CONTINUe with the loop
						}	
						else
						{
                            //UPDATE DATA IN DB 
                            $staff_data = array(
								'staff_id' => $staffMembers['staff_id'],
								'salon_id' => $staffMembers['salon_id'],
								'account_no' => $accNo,
								'name' => $staffMembers['name'],
								'designation' => $staffMembers['designation'],
								'owner_employee' => $staffMembers['owner_employee'],
								'allowed_locations' => $staffMembers['allowed_locations'],
								'skill_set' => $staffMembers['skill_set'],
								'dept_skill_set' => $staffMembers['dept_skill_set'],
								'prebook' => $staffMembers['prebook'],
								'color' => $staffMembers['color'],
								'productivity' => $staffMembers['productivity'],
								'avg_service_ticket' => $staffMembers['avg_service_ticket'],
								'avg_rpct' => $staffMembers['avg_rpct'],
								'RPST_goal' => $staffMembers['RPST_goal'],
								'ruct' => $staffMembers['ruct'],
								'rebook_goal' => $staffMembers['rebook_goal'],
								'percentage_buying_retail_goal' => $staffMembers['percentage_buying_retail_goal'],
								'percentage_booked_goal' => $staffMembers['percentage_booked_goal'],
								'clients_serviced' => $staffMembers['clients_serviced'],
								'image' => $staffMembers['image'],
								'email' => $staffMembers['email'],
								'password' => $staffMembers['password'],
								'emp_iid' => $staffMembers['emp_iid'],
								'device_type' => $staffMembers['device_type'],
								'push_token' => $staffMembers['push_token'],
								'status' => $staffMembers['status'],
								'show_reports' => $staffMembers['show_reports'],
								'updated' => date("Y-m-d H:i:s"),
								'leader_board_status'=>$staffMembers['leader_board_status']
							);
							/*$staff_data = array(
								'staff_id' => $staffMembers['staff_id'],
								'salon_id' => $staffMembers['salon_id'],
								'account_no' => $accNo,
								'name' => $staffMembers['name'],
								'designation' => $staffMembers['designation'],
								'owner_employee' => $staffMembers['owner_employee'],
								'image' => $staffMembers['image'],
								'emp_iid' => $staffMembers['emp_iid'],
								'updated' => date("Y-m-d H:i:s"),
								'leader_board_status'=>$staffMembers['leader_board_status'],
							);*/
							$this->db->where('staff_id',$staffMembers['staff_id']);
							$this->db->where('salon_id',$staffMembers['salon_id']);
							$res = $this->db->update(STAFF2_TABLE, $staff_data);
                            pa($this->db->last_query(),"Staff Updated".$staffMembers['staff_id']);
						}
					}
					else // INSERT APPOINTMENT DATA IN DB 
					{
                        //pa($diff_array,'INSERT');
                         $staff_data = array(
								'staff_id' => $staffMembers['staff_id'],
								'salon_id' => $staffMembers['salon_id'],
								'account_no' => $accNo,
								'name' => $staffMembers['name'],
								'designation' => $staffMembers['designation'],
								'owner_employee' => $staffMembers['owner_employee'],
								'allowed_locations' => $staffMembers['allowed_locations'],
								'skill_set' => $staffMembers['skill_set'],
								'dept_skill_set' => $staffMembers['dept_skill_set'],
								'prebook' => $staffMembers['prebook'],
								'color' => $staffMembers['color'],
								'productivity' => $staffMembers['productivity'],
								'avg_service_ticket' => $staffMembers['avg_service_ticket'],
								'avg_rpct' => $staffMembers['avg_rpct'],
								'RPST_goal' => $staffMembers['RPST_goal'],
								'ruct' => $staffMembers['ruct'],
								'rebook_goal' => $staffMembers['rebook_goal'],
								'percentage_buying_retail_goal' => $staffMembers['percentage_buying_retail_goal'],
								'percentage_booked_goal' => $staffMembers['percentage_booked_goal'],
								'clients_serviced' => $staffMembers['clients_serviced'],
								'image' => $staffMembers['image'],
								'email' => $staffMembers['email'],
								'password' => $staffMembers['password'],
								'emp_iid' => $staffMembers['emp_iid'],
								'device_type' => $staffMembers['device_type'],
								'push_token' => $staffMembers['push_token'],
								'status' => $staffMembers['status'],
								'show_reports' => $staffMembers['show_reports'],
								'updated' => date("Y-m-d H:i:s"),
								'leader_board_status'=>$staffMembers['leader_board_status']
							);
						/*$staff_data = array(
								'staff_id' => $staffMembers['staff_id'],
								'salon_id' => $staffMembers['salon_id'],
								'account_no' => $accNo,
								'name' => $staffMembers['name'],
								'designation' => $staffMembers['designation'],
								'owner_employee' => $staffMembers['owner_employee'],
								'image' => $staffMembers['image'],
								'emp_iid' => $staffMembers['emp_iid'],
								'updated' => date("Y-m-d H:i:s"),
								'created' => date("Y-m-d H:i:s"),
								'leader_board_status'=>$staffMembers['leader_board_status'],
						);*/
						$res = $this->db->insert(STAFF2_TABLE, $staff_data);
						//$staff_id = $this->db->insert_id();
                        pa($this->db->last_query(),"Staff Inserted".$staffMembers['staff_id']);
					}
				}

              
				if(!empty($allStaffIds))
				{
					//print_r($allapptIds);exit;
					//TO SEARCH EXISTING APPOINTMENTS WITH NEW APPOINTMENTS
					$allDBStaffIds = array();
					//$allapptIds[] = $appts['iid'][0];
					// GETS APPOINTMENTS DATA FROM DB COMPARING APPOINTMENT IID
			        $allStaffQueryArray =  $this->db->select('staff_id')
                                                    ->where_in('salon_id', $getAllSalonsIds)
                                                    ->get(STAFF2_TABLE)
                                                    ->result_array();
                    
                    $allDBStaffIds = (isset($allStaffQueryArray) && !empty($allStaffQueryArray))? array_column($allStaffQueryArray, 'staff_id'): array();              
                     
                    
                    $count = 1;
                    if(!empty($allDBStaffIds)){
                            foreach($allDBStaffIds as $k => $staffsid)
                            {
                               
                                if(!in_array($staffsid,$allStaffIds)){
                                    $this->db->where('staff_id',$staffsid);
        							$this->db->delete(STAFF2_TABLE);
                                    pa($staffsid,'Deleted -- '. $count);
                                }
                                $count++;
                            }                                                                                                      
                    }
                }
			}
			else
			{
				echo "No Staff Found"."<br>";
			}
		}
}
