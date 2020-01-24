<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Wsexport_mill_data extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			header('Content-Type: application/json');
			$this->load->library('session');
			$this->load->library('form_validation');
			$this->load->library('pagination');
			$this->load->helper(array('form', 'url'));
			$this->load->database();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		}
		
		function index() {
			// wont do anything
		}
		
		function exporting_mill_appointments() {
			ini_set('memory_limit',-1);
			//ini_set('max_execution_time',600);
                        //set_time_limit(0); 
                        ini_set('MAX_EXECUTION_TIME', -1);
			$this->DB_ReadOnly->select("AccountNo");
			$this->DB_ReadOnly->group_by("AccountNo");
			//$this->db->where("AccountNo!=",1069669406);
			//$this->db->where("AccountNo",1606999570);
			$get_account_nos = $this->DB_ReadOnly->get(MILL_APPTS_TABLE);
			foreach($get_account_nos->result_array() as $account_nos) {

				$data = array();
				$AppointmentIIDs = array();
				$account_no = $account_nos['AccountNo'];
				$this->DB_ReadOnly->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
				$this->DB_ReadOnly->limit(500);
				$get_appointments = $this->DB_ReadOnly->get(MILL_APPTS_TABLE);
				if($get_appointments->num_rows() > 0) {
					foreach($get_appointments->result_array() as $appointment) {
						//echo "dfsdfsdf sdfsdf";exit;
						$temp = array();
						$AppointmentIIDs[] = $appointment['AppointmentIID'];
						//if(!in_array($appointment['AppointmentIID'],$data)) {
							$temp['AppointmentId'] = $appointment['AppointmentIID'];
							$temp['ApptId'] = $appointment['ApptId'];
							$temp['SlcStatus'] = $appointment['SlcStatus'];
							$temp['ClientId'] = $appointment['ClientId'];
							$temp['AppointmentDate'] = $appointment['AppointmentDate'];
							$temp['AppointmentTime'] = $appointment['AppointmentTime'];
							$temp['CreationDate'] = $appointment['CreationDate'];
							$temp['EmployeeName'] = $appointment['EmployeeName'];
							$temp['Status'] = $appointment['Status'];
							$temp['Price'] = $appointment['Price'];
							$temp['RowOrder'] = $appointment['RowOrder'];
							$temp['Service'] = $appointment['Service'];
							$temp['ServiceCategory'] = $appointment['ServiceCategory'];
							$temp['Tax'] = $appointment['Tax'];
							$temp['SubTotal'] = $appointment['SubTotal'];
							$temp['Total'] = $appointment['Total'];
							$temp['Tender'] = $appointment['Tender'];
							$temp['SdkStatus'] = $appointment['sdk_status'];
							$temp['MillCreatedDate'] = $appointment['MillCreatedDate'];
							$temp['MillLastModifiedDate'] = $appointment['MillLastModifiedDate'];
							$temp['MillLastChangeDate'] = $appointment['MillLastChangeDate'];
							$temp['Lprebook'] = $appointment['Lprebook'];
							$temp['Nstartlen'] = $appointment['Nstartlen'];
							$temp['Ngaplen'] = $appointment['Ngaplen'];
							$temp['Nfinishlen'] = $appointment['Nfinishlen'];
							$temp['CheckedIn'] = $appointment['CheckedIn'];
							$temp['CheckInTime'] = $appointment['CheckInTime'];
							$temp['CheckoutTime'] = $appointment['CheckoutTime'];
							$temp['Noshow'] = $appointment['Noshow'];
							$temp['iempid'] = $appointment['iempid'];
							$temp['iservid'] = $appointment['iservid'];
							$temp['BlockId'] = $appointment['BlockId'];
							$temp['BlockDescription'] = $appointment['BlockDescription'];
							$temp['MapNotes'] = $appointment['MapNotes'];
							$temp['is_checked_in_push_sent'] = $appointment['is_checked_in_push_sent'];
							$temp['checked_in_push_send_date'] = $appointment['checked_in_push_send_date'];
							$temp['appointment_log'] = $appointment['appointment_log'];
							$data[] = $temp;
						//}
					}
					$post_body = json_encode($data);
					//echo $post_body;exit;
					
					//$input_array = json_decode($post_body,true);
					//print_r($input_array);exit;
					$ch = curl_init();
					//curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data_new/importing_mill_appointments_sdk/".$account_no);
					curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data_new_export/importing_mill_appointments_sdk/".$account_no);
					//curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data/importing_mill_appointments/1744665785");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                                        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body); 
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
					$result=curl_exec($ch);
					
					$response = json_decode($result,true);
					//print_r($response);
					if($response['IsTotalSuccess'] == true) {
						//mark all records as processed
						$this->db->where_in('AppointmentIID', $AppointmentIIDs);
						$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
						$update_array = array(
							 "IsProcessed" => 1
						);
						$processed = $this->db->update(MILL_APPTS_TABLE,$update_array);
						//echo $this->db->last_query();exit;
					} else if($response['SystemError'] == "") {
						if(!empty($response['FailedIdList'])){
							foreach ($response['FailedIdList'] as $FailedIdList) {
								$failure_array[] = $FailedIdList['id'];
							}
							
							//mark only successful records as processed
							$this->db->where_in('AppointmentIID', $AppointmentIIDs);
							$this->db->where_not_in('AppointmentIID', $failure_array);
							$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
							$update_array = array(
								"IsProcessed" => 1
							);
							$processed = $this->db->update(MILL_APPTS_TABLE,$update_array);
					        echo "Successfully Updated for AccountNo: ".$account_no."\n";
						}	
						//echo $this->db->last_query();exit;
					}
				} else {
					echo "No Data for AccountNo: ".$account_no."\n";
				}
			}
		}
		
		//It exports only updated clients like appointments
		function exporting_mill_clients() {
			ini_set('memory_limit',-1);
			ini_set('max_execution_time',600);
			$this->DB_ReadOnly->select("AccountNo");
			$this->DB_ReadOnly->group_by("AccountNo");
			//$this->db->where("AccountNo=",164194097);
			//$this->db->where("AccountNo",1974787526);
			//$this->db->where("AccountNo",1606999570);
			$get_account_nos = $this->DB_ReadOnly->get(MILL_CLIENTS_TABLE);
			foreach($get_account_nos->result_array() as $account_nos) {
				$data = array();
				$ClientIds = array();
				$account_no = $account_nos['AccountNo'];
				$this->DB_ReadOnly->limit(500);
				$this->DB_ReadOnly->where(array("AccountNo" => $account_no, "IsProcessed" => 0, "ClientId!=" => ''));
				$get_appointments = $this->DB_ReadOnly->get(MILL_CLIENTS_TABLE);
				if($get_appointments->num_rows() > 0) {
					foreach($get_appointments->result_array() as $appointment) {
						$temp = array();
						$ClientIds[] = $appointment['ClientId']; 
						$temp['ClientId'] = $appointment['ClientId'];
						$temp['Email'] = $appointment['Email'];
						$temp['Zip'] = $appointment['Zip'];
						$temp['Phone'] = $appointment['Phone'];
						$temp['Name'] = $appointment['Name'];
						$temp['Dob'] = $appointment['Dob'];
						$temp['GiftCardBalance'] = $appointment['GiftCardBalance'];
						$temp['LoyaltyPoints'] = $appointment['LoyaltyPoints'];
						$temp['LastReceiptAmount'] = $appointment['LastReceiptAmount'];
						$temp['Sex'] = $appointment['Sex'];
						$temp['Mobile'] = $appointment['Mobile'];
						$temp['MobileAreaCode'] = $appointment['MobileAreaCode'];
						$temp['BusinessPhoneNumber'] = $appointment['BusinessPhoneNumber'];
						$temp['BusinessAreaCode'] = $appointment['BusinessAreaCode'];
						$temp['clientFirstVistedDate'] = $appointment['clientFirstVistedDate'];
						$temp['clientLastVistedDate'] = $appointment['clientLastVistedDate'];
						$temp['optin_email'] = $appointment['opted_in_email'];
						$temp['optin_sms'] = $appointment['opted_in_sms'];
						$temp['HasMemberships'] = $appointment['HasMemberships'];
						$temp['client_log'] = $appointment['client_log'];
						$data[] = $temp;
					}
					$post_body = json_encode($data);
					
					//$input_array = json_decode($post_body,true);
					//print_r($input_array);exit;
					$ch = curl_init();
					
					//curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data_new/importing_mill_clients_sdk/".$account_no);
					curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data_new_export/importing_mill_clients_sdk/".$account_no);
					//curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data/importing_mill_clients/1744665785");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body); 
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
					$result=curl_exec($ch);
				    //pa($result,'result',true);
					$response = json_decode($result,true);
					print_r($response);
					if($response['IsTotalSuccess'] == true) {
						//mark all records as processed
						$this->db->where_in('ClientId', $ClientIds);
						$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
						$update_array = array(
							 "IsProcessed" => 1
						);
						$processed = $this->db->update(MILL_CLIENTS_TABLE,$update_array);
					} else if($response['SystemError'] == "") {
						if(!empty($response['FailedIdList'])){
							foreach ($response['FailedIdList'] as $FailedIdList) {
								$failure_array[] = $FailedIdList['id'];
							}
							//mark only successful records as processed
							$this->db->where_in('ClientId', $ClientIds);
							$this->db->where_not_in('ClientId', $failure_array);
							$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
							$update_array = array(
								"IsProcessed" => 1
							);
							$processed = $this->db->update(MILL_CLIENTS_TABLE,$update_array);
						    echo $this->db->last_query()."\n";
						    echo "Successfully Updated for AccountNo: ".$account_no."\n";
						}	
					}
					
				} else {
					echo "No Data for AccountNo: ".$account_no."\n";
				}
			}
		}

		function exporting_mill_appointments_clone() {
			ini_set('memory_limit',-1);
			ini_set('max_execution_time',600);
			$this->db->select("AccountNo");
			$this->db->group_by("AccountNo");
			//$this->db->where("AccountNo",1062278045);
			$get_account_nos = $this->db->get(MILL_APPTS_TABLE_BK);
			foreach($get_account_nos->result_array() as $account_nos) {

				$data = array();
				$account_no = $account_nos['AccountNo'];
				$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
				$get_appointments = $this->db->get(MILL_APPTS_TABLE_BK);
				if($get_appointments->num_rows() > 0) {
					foreach($get_appointments->result_array() as $appointment) {
						//echo "dfsdfsdf sdfsdf";exit;
						$temp = array();

						//if(!in_array($appointment['AppointmentIID'],$data)) {
							$temp['AppointmentId'] = $appointment['AppointmentIID'];
							$temp['ApptId'] = $appointment['ApptId'];
							$temp['SlcStatus'] = $appointment['SlcStatus'];
							$temp['ClientId'] = $appointment['ClientId'];
							$temp['AppointmentDate'] = $appointment['AppointmentDate'];
							$temp['AppointmentTime'] = $appointment['AppointmentTime'];
							$temp['CreationDate'] = $appointment['CreationDate'];
							$temp['EmployeeName'] = $appointment['EmployeeName'];
							$temp['Status'] = $appointment['Status'];
							$temp['Price'] = $appointment['Price'];
							$temp['RowOrder'] = $appointment['RowOrder'];
							$temp['Service'] = $appointment['Service'];
							$temp['ServiceCategory'] = $appointment['ServiceCategory'];
							$temp['Tax'] = $appointment['Tax'];
							$temp['SubTotal'] = $appointment['SubTotal'];
							$temp['Total'] = $appointment['Total'];
							$temp['Tender'] = $appointment['Tender'];
							$temp['SdkStatus'] = $appointment['sdk_status'];
							$data[] = $temp;
						//}
					}
					$post_body = json_encode($data);
					//echo $post_body;exit;
					
					//$input_array = json_decode($post_body,true);
					//print_r($input_array);exit;
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data_clone/importing_mill_appointments/".$account_no);
					//curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data/importing_mill_appointments/1744665785");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body); 
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
					$result=curl_exec($ch);
					
					$response = json_decode($result,true);
					print_r($response);
					if($response['IsTotalSuccess'] == true) {
						//mark all records as processed
						$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
						$update_array = array(
							 "IsProcessed" => 1
						);
						$processed = $this->db->update(MILL_APPTS_TABLE_BK,$update_array);
						//echo $this->db->last_query();exit;
					} else if($response['SystemError'] == "") {
						foreach ($response['FailedIdList'] as $FailedIdList) {
							$failure_array[] = $FailedIdList['id'];
						}
						
						//mark only successful records as processed
						$this->db->where_not_in('AppointmentIID', $failure_array);
						$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
						$update_array = array(
							"IsProcessed" => 1
						);
						$processed = $this->db->update(MILL_APPTS_TABLE_BK,$update_array);
						//echo $this->db->last_query();exit;
					}
					echo "Successfully Updated for AccountNo: ".$account_no."\n";
				} else {
					echo "No Data for AccountNo: ".$account_no."\n";
				}
			}
		}

		function exporting_mill_appointments_history() {
			ini_set('memory_limit',-1);
			ini_set('max_execution_time',0);
			$this->db->select("AccountNo");
			$this->db->group_by("AccountNo");
			//$this->db->where("AccountNo",849400097);
			$get_account_nos = $this->DB_ReadOnly->get("mill_past_appointments");
			foreach($get_account_nos->result_array() as $account_nos) {

				$data = array();
				$account_no = $account_nos['AccountNo'];
				$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
				//$this->db->limit(5000);
				$get_appointments = $this->db->get("mill_past_appointments");
				if($get_appointments->num_rows() > 0) {
					foreach($get_appointments->result_array() as $appointment) {
						//echo "dfsdfsdf sdfsdf";exit;
						$temp = array();

						
						$temp['AppointmentId'] = $appointment['AppointmentIID'];
						$temp['ApptId'] = $appointment['ApptId'];
						$temp['SlcStatus'] = $appointment['SlcStatus'];
						$temp['ClientId'] = $appointment['ClientId'];
						$temp['AppointmentDate'] = $appointment['AppointmentDate'];
						$temp['AppointmentTime'] = $appointment['AppointmentTime'];
						$temp['CreationDate'] = $appointment['CreationDate'];
						$temp['EmployeeName'] = $appointment['EmployeeName'];
						$temp['Status'] = $appointment['Status'];
						$temp['Price'] = $appointment['Price'];
						$temp['RowOrder'] = $appointment['RowOrder'];
						$temp['Service'] = $appointment['Service'];
						$temp['ServiceCategory'] = $appointment['ServiceCategory'];
						$temp['Tax'] = $appointment['Tax'];
						$temp['SubTotal'] = $appointment['SubTotal'];
						$temp['Total'] = $appointment['Total'];
						$temp['Tender'] = $appointment['Tender'];
						$temp['SdkStatus'] = $appointment['sdk_status'];
						$data[] = $temp;
					}
					$post_body = json_encode($data);
					//echo $post_body;exit;
					echo sizeof($data)."\n";
					//exit;
					//$input_array = json_decode($post_body,true);
					//print_r($input_array);exit;
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data_new/importing_mill_appointments_sdk/".$account_no);
					//curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsimport_data/importing_mill_appointments/1744665785");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body); 
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
					$result=curl_exec($ch);
					
					$response = json_decode($result,true);
					print_r($response);
					if($response['IsTotalSuccess'] == true) {
						//mark all records as processed
						$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
						$update_array = array(
							 "IsProcessed" => 1
						);
						$processed = $this->db->update("mill_past_appointments",$update_array);
						//echo $this->db->last_query();exit;
					} else if($response['SystemError'] == "") {
						foreach ($response['FailedIdList'] as $FailedIdList) {
							$failure_array[] = $FailedIdList['id'];
						}
						
						//mark only successful records as processed
						$this->db->where_not_in('AppointmentIID', $failure_array);
						$this->db->where(array("AccountNo" => $account_no, "IsProcessed" => 0));
						$update_array = array(
							"IsProcessed" => 1
						);
						$processed = $this->db->update("mill_past_appointments",$update_array);
						//echo $this->db->last_query();exit;
					}
					echo "Successfully Updated for AccountNo: ".$account_no."\n";
				} else {
					echo "No Data for AccountNo: ".$account_no."\n";
				}
			}
		}
	}
?>