<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */
    class Employeeimportlisting_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		  }
		 /**
		   GET EMPLOYEES LISTING DATA FROM DB COMPARING 
		 */
		 public function compareMillEmployeesListing($data){
		 	return $this->DB_ReadOnly->get_where(MILL_EMPLOYEE_LISTING,$data);
		 }
		 /**
		    Insert EMPLOYEES SCHEDULE HOURS CURRENT YEAR DATA
		 */ 
		 public function insertMillEmployeesListing($data){
            return $this->db->insert(MILL_EMPLOYEE_LISTING, $data);
		 }
		 /**
		    Update EMPLOYEES SCHEDULE HOURS CURRENT YEAR DATA
		 */ 
		 public function updateEmployeesListing($whereconditions,$data){
            $this->db->where('iid',$whereconditions['iid']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_EMPLOYEE_LISTING, $data);
		 }
   }       