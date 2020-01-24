<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */
    class Employeeimportweekwiselastyear_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		  }
		
		 /**
		   GET EMPLOYEES SCHEDULE HOURS WEEK WISE Last Year DATA FROM DB COMPARING 
		 */
		 public function compareMillEmployeesScheduleHoursWeekWiseLastYear($data){
		 	return $this->DB_ReadOnly->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS,$data);
		 }
		 /**
		    Insert EMPLOYEES SCHEDULE HOURS WEEK WISE Last Year DATA
		 */ 
		 public function insertMillEmployeesScheduleHoursWeekWiseLastYear($data){
            return $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }
		 /**
		    Update EMPLOYEES SCHEDULE HOURS  WEEK WISE Last Year DATA
		 */ 
		 public function updateEmployeesScheduleHoursWeekWiseLastYear($whereconditions,$data){
            $this->db->where('iempid',$whereconditions['iempid']);
			$this->db->where('iworktypeid',$whereconditions['iworktypeid']);
			$this->db->where('dayRangeType',$whereconditions['dayRangeType']);
			$this->db->where('start_date',$whereconditions['start_date']);
			$this->db->where('end_date',$whereconditions['end_date']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }

		 

   }       