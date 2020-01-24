<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class WsSaveOnlineBookingAppts extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			$this->load->database();
		}
		
		function index() {
			// wont do anything
		}
		
		public function saveAppointments() {
			$data=array();
			$input = file_get_contents('php://input');			
			if(!empty($input)) {
				$jsonDecodedInput = json_decode($input,true);
				$accountNo = $jsonDecodedInput['account_no'];
				$ApptId = $jsonDecodedInput['ApptId'];
				$ddate = $jsonDecodedInput['ddate'];
				$sdk_status = $jsonDecodedInput['sdk_status'];
				$lprebook = $jsonDecodedInput['lprebook'];
				foreach ($jsonDecodedInput['appointments'] as $each) {
					$saveData = array(
						'AppointmentIID' => $each['iid'],
						'SalonId' => 0,
						'AccountNo' => $accountNo,
						'ApptId' => $ApptId,
						'SlcStatus' => 'Inserted',
						'AppointmentDate' => $ddate, //appointment Date
						'AppointmentTime' => $each['ctimeofday'],
						'EmployeeName' => $each['iempname'],
						'WinServiceTypeId' => 0,
						'ClientId' => $each['iclientid'],
						'Service' => $each['cservice'],
						'Status' => 1,
						'IsProcessed' => 0,
						'CrateatedDate' => date("Y-m-d H:i:s"),
						'ModifiedDate' => date("Y-m-d H:i:s"),
						'sdk_status' => $sdk_status,
						'Lprebook' => $lprebook,
						'Nstartlen' => $each['nstartlen'],
						'Ngaplen' => $each['ngaplen'],
						'Nfinishlen' => $each['nfinishlen'],
						'CheckedIn' => $each['lcheckedin'],
						'CheckInTime' => $each['ccheckintime'],
						'CheckoutTime' => $each['ccheckouttime'],
						'Noshow' => $each['lnoshow'],
						'iempid' => $each['iempid'],
						'iservid' => $each['service_id']
					);
					$res = $this->db->insert(MILL_APPTS_TABLE, $saveData);	
					//$id = 	$this->db->insert_id();			
				}
				$status = true;
				$message = "Data saved successfully.";
			} else {
				$status = false;
				$message = "Input must be provided.";
			}
			$data['status'] = $status;
			$data['message'] = $message;
			echo json_encode($data);
		}
		
	}
?>