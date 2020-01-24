<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */
    class Appointmentsimport_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		  }
		
		  /**
		   GETS PAST APPOINTMENTS DATA FROM DB COMPARING 
		 */
		 public function compareAppointments($data){
		 	//$this->db->trans_start();
		 	$compare = $this->DB_ReadOnly->get_where(MILL_APPTS_TABLE,$data);
		 	if($compare === FALSE)
			{
			    $errors = $this->DB_ReadOnly->error();
			    $errors['tablename'] = 'mill_appointments';
			    send_mail_database_error($errors);
			    $this->DB_ReadOnly->trans_complete();
			    exit();
			}
			//$this->db->trans_complete();
		 	return $compare;
		 	//return $this->db->get_where(MILL_APPTS_TABLE,$data);
		 }
		 /**
		    Insert PAST APPOINTMENTS DATA
		 */ 
		 public function insertMillAppointments($data){
		 	//$this->db->trans_start();
			$insert_query = $this->db->insert_string(MILL_APPTS_TABLE, $data);
			$insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
			$ins = $this->db->query($insert_query);
			$insert_id =  $this->db->insert_id();
			if($ins === FALSE)
			{
			    $errors = $this->db->error();
			    $errors['tablename'] = 'mill_appointments---insertMillAppointments'.'--'.$data['AccountNo'];
			    send_mail_database_error($errors);
			    //$this->db->trans_complete();
			    exit();
			} 
			//$this->db->trans_complete();
			return $insert_id;

		 	//return $this->db->insert(MILL_APPTS_TABLE, $data);
		 	/*$this->db->trans_start();
			$insert_query = $this->db->insert_string(MILL_APPTS_TABLE, $data);
			$insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
			$this->db->query($insert_query);
			$insert_id =  $this->db->insert_id();
			$this->db->trans_complete();
			return $insert_id;*/
		 }
		 /**
		    Update PAST APPOINTMENTS DATA
		 */ 
		 public function updateMillAppointments($whereconditions,$data){
		 	//$this->db->trans_start();
            $this->db->where('AppointmentIID',$whereconditions['AppointmentIID']);
			$this->db->where('AccountNo',$whereconditions['AccountNo']);
			$upd = $this->db->update(MILL_APPTS_TABLE, $data);
			if($upd === FALSE)
			{
			    $errors = $this->db->error();
			    $errors['tablename'] = 'mill_appointments--updateMillAppointments'.'---'.$whereconditions['AppointmentIID'];
			    send_mail_database_error($errors);
			    //$this->db->trans_complete();
			    exit();
			} 
			//$this->db->trans_complete();
			return true;
		 } 
		 /**
		   Check Appointments 
		 */ 
		 public function checkAppointmentsToModify($whereconditions){
	 		//$this->DB_ReadOnly->trans_start();
	 		$this->DB_ReadOnly->where('AccountNo',$whereconditions['AccountNo']);
			$this->DB_ReadOnly->where("STR_TO_DATE(AppointmentDate, '%m/%d/%Y') >=", $whereconditions['startDate']);
			$this->DB_ReadOnly->where("STR_TO_DATE(AppointmentDate, '%m/%d/%Y') <=", $whereconditions['endDate']);
			$get = $this->DB_ReadOnly->get(MILL_APPTS_TABLE);
			if($this->DB_ReadOnly->trans_status() === FALSE)
			{
			    $errors = $this->DB_ReadOnly->error();
			    $errors['tablename'] = 'mill_appointments--checkAppointmentsToModify'.'---'.$whereconditions['AccountNo'];
			    send_mail_database_error($errors);
			    //$this->DB_ReadOnly->trans_complete();
			    exit();
			} 
			//$this->DB_ReadOnly->trans_complete();
			return $get;
		 }
		 /**
		   Update Cancelled Appointments
		 */ 
		 public function updateCancelledAppointments($whereconditions,$data){
		 	
		 	//$this->db->trans_start();
		 	$this->db->where('AccountNo',$whereconditions['AccountNo']);
	 		$this->db->where('AppointmentIID',$whereconditions['AppointmentIID']);
	 		$this->db->where('SlcStatus !=','Deleted');
			$this->db->where("STR_TO_DATE(AppointmentDate, '%m/%d/%Y') >=", $whereconditions['startDate']);
			$this->db->where("STR_TO_DATE(AppointmentDate, '%m/%d/%Y') <=", $whereconditions['endDate']);
			$statusUpdate = "Deleted at ".date("Y-m-d H:i:s")." for Appointment ".$whereconditions['AppointmentIID']."\n";
			$this->db->set('appointment_log', 'CONCAT(appointment_log,"'.$statusUpdate.'")', FALSE);
			$update =  $this->db->update(MILL_APPTS_TABLE,$data);
			if($update === FALSE)
			{
			    $errors = $this->db->error();
			    $errors['tablename'] = 'mill_appointments--updateCancelledAppointments'.'---'.$whereconditions['AccountNo'];
			    send_mail_database_error($errors);
			    //$this->db->trans_complete();
			    exit();
			} 
			//$this->db->trans_complete();
			return $update;


	 		/*$this->db->where('AccountNo',$whereconditions['AccountNo']);
	 		$this->db->where('AppointmentIID',$whereconditions['AppointmentIID']);
	 		$this->db->where('SlcStatus !=','Deleted');
			$this->db->where("STR_TO_DATE(AppointmentDate, '%m/%d/%Y') >=", $whereconditions['startDate']);
			$this->db->where("STR_TO_DATE(AppointmentDate, '%m/%d/%Y') <=", $whereconditions['endDate']);
			return $this->db->update(MILL_APPTS_TABLE,$data);*/
		 }
		 /**
		   Update CheckidIn Appointments
		 */ 
		 public function updateCheckedInAppointments($whereconditions,$data){
	 		//$this->db->trans_start();
	 		$this->db->where('AccountNo',$whereconditions['AccountNo']);
	 		$this->db->where('AppointmentIID',$whereconditions['AppointmentIID']);
	 		$update =  $this->db->update(MILL_APPTS_TABLE,$data);
	 		if($update === FALSE)
			{
			    $errors = $this->db->error();
			    $errors['tablename'] = 'mill_appointments--updateCheckedInAppointments'.'---'.$whereconditions['AccountNo'];
			    send_mail_database_error($errors);
			    //$this->db->trans_complete();
			    exit();
			} 
			//$this->db->trans_complete();
			return $update;
	 		/*$this->db->where('AccountNo',$whereconditions['AccountNo']);
	 		$this->db->where('AppointmentIID',$whereconditions['AppointmentIID']);
	 		return $this->db->update(MILL_APPTS_TABLE,$data);*/
		 } 
		 		  

   }       