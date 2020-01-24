<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */
    class Reports_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		  }
		 /**
		   GETS APPOINTMENTS DATA FROM DB COMPARING APPT ID
		 */
		 public function compareGiftCardSalesWithBalance($data){
		 	return $this->DB_ReadOnly->get_where(MILL_GIFT_CARD_SALES_WITH_BALANCE,$data);
		 }
		 /**
		    Insert GIFT CARD SALES DATA
		 */ 
		 public function insertGiftCardSalesWithBalance($data){
            return $this->db->insert(MILL_GIFT_CARD_SALES_WITH_BALANCE, $data);
		 }
		 /**
		    Update GIFT CARD SALES DATA
		 */ 
		 public function updateGiftCardSalesWithBalance($whereconditions,$data){
            $this->db->where('iid',$whereconditions['iid']);
			$this->db->where('cgiftnumber',$whereconditions['cgiftnumber']);
			$this->db->where('iheaderid',$whereconditions['cgiftnumber']);
			$this->db->where('cinvoiceno',$whereconditions['cinvoiceno']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_GIFT_CARD_SALES_WITH_BALANCE, $data);
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
			$this->db->where('account_no',$whereconditions['AccountNo']);
			return $this->db->update(MILL_PAST_APPTS_TABLE, $data);
		 }  
		 /**
		   GETS PAST TWO YEARS CLIENTS DATA FROM DB COMPARING 
		 */
		 public function compareMillTwoYearsClients($data){
		 	return $this->DB_ReadOnly->get_where(MILL_CLIENTS_TABLE,$data);
		 }
		 /**
		    Insert PAST TWO YEARS CLIENTS DATA
		 */ 
		 public function insertMillTwoYearsClients($data){
            return $this->db->insert(MILL_CLIENTS_TABLE, $data);
		 }
		 /**
		    Update PAST TWO YEARS CLIENTS DATA
		 */ 
		 public function updateMillTwoYearsClients($whereconditions,$data){
            $this->db->where('ClientId',$whereconditions['ClientId']);
			$this->db->where('account_no',$whereconditions['AccountNo']);
			return $this->db->update(MILL_CLIENTS_TABLE, $data);
		 } 
		/**
		   GETS SALON SERVICES DATA FROM DB COMPARING 
		 */
		 public function compareMillServicesSales($data){
		 	return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$data);
		 }
		 /**
		    Insert SALON SERVICES DATA
		 */ 
		 public function insertMillServicesSales($data){
            return $this->db->insert(MILL_SERVICE_SALES, $data);
		 }
		 /**
		    Update SALON SERVICES DATA
		 */ 
		 public function updateMillServicesSales($whereconditions,$data){
            $this->db->where('iempid',$whereconditions['iempid']);
			$this->db->where('itransdetailid',$whereconditions['itransdetailid']);
			$this->db->where('cinvoiceno',$whereconditions['cinvoiceno']);
			$this->db->where('iclientid',$whereconditions['iclientid']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_SERVICE_SALES, $data);
		 }
        /**
		   GETS SALON PRODUCTS DATA FROM DB COMPARING 
		 */
		 public function compareMillProductsSales($data){
		 	return $this->DB_ReadOnly->get_where(MILL_PRODUCT_SALES,$data);
		 }
		 /**
		    Insert SALON PRODUCTS DATA
		 */ 
		 public function insertMillProductsSales($data){
            return $this->db->insert(MILL_PRODUCT_SALES, $data);
		 }
		 /**
		    Update SALON PRODUCTS DATA
		 */ 
		 public function updateMillProductsSales($whereconditions,$data){
            $this->db->where('iempid',$whereconditions['iempid']);
			$this->db->where('itransdetailid',$whereconditions['itransdetailid']);
			$this->db->where('cinvoiceno',$whereconditions['cinvoiceno']);
			$this->db->where('iclientid',$whereconditions['iclientid']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_PRODUCT_SALES, $data);
		 }

		 /**
		   GET EMPLOYEES SCHEDULE HOURS DATA FROM DB COMPARING 
		 */
		 public function compareMillEmployeesScheduleHoursLastYear($data){
		 	return $this->DB_ReadOnly->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS,$data);
		 }
		 /**
		    Insert EMPLOYEES SCHEDULE HOURS DATA
		 */ 
		 public function insertMillEmployeesScheduleHoursLastYear($data){
            return $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }
		 /**
		    Update EMPLOYEES SCHEDULE HOURS DATA
		 */ 
		 public function updateEmployeesScheduleHoursLastYear($whereconditions,$data){
            $this->db->where('iempid',$whereconditions['iempid']);
			$this->db->where('itransdetailid',$whereconditions['itransdetailid']);
			$this->db->where('cinvoiceno',$whereconditions['cinvoiceno']);
			$this->db->where('iclientid',$whereconditions['iclientid']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }


		  /**
		   GET EMPLOYEES SCHEDULE HOURS CURRENT YEAR DATA FROM DB COMPARING 
		 */
		 public function compareMillEmployeesScheduleHoursCurrentYear($data){
		 	return $this->DB_ReadOnly->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS,$data);
		 }
		 /**
		    Insert EMPLOYEES SCHEDULE HOURS CURRENT YEAR DATA
		 */ 
		 public function insertMillEmployeesScheduleHoursCurrentYear($data){
            return $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }
		 /**
		    Update EMPLOYEES SCHEDULE HOURS CURRENT YEAR DATA
		 */ 
		 public function updateEmployeesScheduleHoursCurrentYear($whereconditions,$data){
            $this->db->where('iempid',$whereconditions['iempid']);
			$this->db->where('itransdetailid',$whereconditions['itransdetailid']);
			$this->db->where('cinvoiceno',$whereconditions['cinvoiceno']);
			$this->db->where('iclientid',$whereconditions['iclientid']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }

		 /**
		   GET EMPLOYEES SCHEDULE HOURS Month Wise DATA FROM DB COMPARING 
		 */
		 public function compareMillEmployeesScheduleHoursMonthWise($data){
		 	return $this->DB_ReadOnly->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS,$data);
		 }
		 /**
		    Insert EMPLOYEES SCHEDULE HOURS Month Wise DATA
		 */ 
		 public function insertMillEmployeesScheduleHoursMonthWise($data){
            return $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }
		 /**
		    Update EMPLOYEES SCHEDULE HOURS Month Wise DATA
		 */ 
		 public function updateEmployeesScheduleHoursMonthWise($whereconditions,$data){
            $this->db->where('iempid',$whereconditions['iempid']);
			$this->db->where('itransdetailid',$whereconditions['itransdetailid']);
			$this->db->where('cinvoiceno',$whereconditions['cinvoiceno']);
			$this->db->where('iclientid',$whereconditions['iclientid']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }

		 /**
		   GET EMPLOYEES SCHEDULE HOURS Month Wise Last Year DATA FROM DB COMPARING 
		 */
		 public function compareMillEmployeesScheduleHoursMonthWiseLastYear($data){
		 	return $this->DB_ReadOnly->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS,$data);
		 }
		 /**
		    Insert EMPLOYEES SCHEDULE HOURS Month Wise Last Year DATA
		 */ 
		 public function insertMillEmployeesScheduleHoursMonthWiseLastYear($data){
            return $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }
		 /**
		    Update EMPLOYEES SCHEDULE HOURS  Month Wise Last Year DATA
		 */ 
		 public function updateEmployeesScheduleHoursMonthWiseLastYear($whereconditions,$data){
            $this->db->where('iempid',$whereconditions['iempid']);
			$this->db->where('itransdetailid',$whereconditions['itransdetailid']);
			$this->db->where('cinvoiceno',$whereconditions['cinvoiceno']);
			$this->db->where('iclientid',$whereconditions['iclientid']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }

		 /**
		   GET EMPLOYEES SCHEDULE HOURS WEEK WISE DATA FROM DB COMPARING 
		 */
		 public function compareMillEmployeesScheduleHoursWeekWise($data){
		 	return $this->DB_ReadOnly->get_where(MILL_EMPLOYEE_SCHEDULE_HOURS,$data);
		 }
		 /**
		    Insert EMPLOYEES SCHEDULE HOURS WEEK WISE DATA
		 */ 
		 public function insertMillEmployeesScheduleHoursWeekWise($data){
            return $this->db->insert(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }
		 /**
		    Update EMPLOYEES SCHEDULE HOURS  WEEK WISE DATA
		 */ 
		 public function updateEmployeesScheduleHoursWeekWise($whereconditions,$data){
            $this->db->where('iempid',$whereconditions['iempid']);
			$this->db->where('itransdetailid',$whereconditions['itransdetailid']);
			$this->db->where('cinvoiceno',$whereconditions['cinvoiceno']);
			$this->db->where('iclientid',$whereconditions['iclientid']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
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
			$this->db->where('itransdetailid',$whereconditions['itransdetailid']);
			$this->db->where('cinvoiceno',$whereconditions['cinvoiceno']);
			$this->db->where('iclientid',$whereconditions['iclientid']);
			$this->db->where('account_no',$whereconditions['account_no']);
			return $this->db->update(MILL_EMPLOYEE_SCHEDULE_HOURS, $data);
		 }

		 

   }       