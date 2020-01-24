<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */
    class Pastappointmentsimport_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		  }
		
		  /**
		   GETS PAST APPOINTMENTS DATA FROM DB COMPARING 
		 */
		 public function compareMillPastAppointments($data){
		 	return $this->DB_ReadOnly->get_where(MILL_PAST_APPTS_TABLE,$data);
		 }
		 /**
		    Insert PAST APPOINTMENTS DATA
		 */ 
		 public function insertMillPastAppointments($data){
		 	return $this->db->insert(MILL_PAST_APPTS_TABLE, $data);
		 }
		 /**
		    Update PAST APPOINTMENTS DATA
		 */ 
		 public function updateMillPastAppointments($whereconditions,$data){
            $this->db->where('AppointmentIID',$whereconditions['AppointmentIID']);
			$this->db->where('AccountNo',$whereconditions['AccountNo']);
			return $this->db->update(MILL_PAST_APPTS_TABLE, $data);
		 }  
		  

   }       