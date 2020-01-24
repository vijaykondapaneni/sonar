<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */
    class Checkappoinmentsstatus_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		  }
		
		  /**
		   GETS PAST APPOINTMENTS DATA FROM DB COMPARING 
		 */
		   public function getconfig_details(){
		   	$this->db->select('salon_name,salon_id,salon_account_id,status');
		   	$this->db->from('mill_all_sdk_config_details');
		   	$this->db->where('status','0');
		   	$query = $this->db->get();
		   	return $query->result_array();

		   }

		   public function getcheckdin($salon_account_id=0){


		   	$this->db->select('*');

		   	$this->db->from('mill_cron_running_logs');
		   
		   	$this->db->where('whichCron','appointmentsCheckedIn');
		   	$this->db->where('AccountNo',$salon_account_id);
		   	$this->db->order_by('id', 'desc');

		   	$this->db->LIMIT('1');

			
		   	
	// $query = $this->db->query("SELECT * FROM (select * from `mill_cron_running_logs` WHERE `whichCron` = 'appointmentsCheckedIn' ORDER BY `id` DESC) final where final.`AccountNo` = 1974787526 order by final.id desc LIMIT 1");
		   	$query = $this->db->get();
		   	 // echo $this->db->last_query();
		   	 // exit;
		   	return $query->result_array();
		   }

		   public function getoneday($salon_account_id=0){
		   	$this->db->select('*');
		   	$this->db->from('mill_appointments');
		   	$this->db->where('AccountNo',$salon_account_id);
		   	$this->db->where('dayRange','Oneday');
		   	// $this->db->group_by('AccountNo');
		   	$this->db->order_by('id', 'desc');
		   	// echo $this->db->last_query();
		   	// exit;
		   	$this->db->LIMIT('1');

		   	$query = $this->db->get();
		   	return $query->result_array();
		   }
		   public function getoneweek($salon_account_id){
		   	$this->db->select('*');
		   	$this->db->from('mill_appointments');
		   	$this->db->where('AccountNo',$salon_account_id);
		   	$this->db->where('dayRange','Oneweek');
		   	// $this->db->group_by('AccountNo');
		   $this->db->order_by('id', 'desc');
		   	$this->db->LIMIT('1');
		   	 // echo $this->db->last_query();
		   	 // exit;

		   	$query = $this->db->get();
		   	return $query->result_array();
		   }
		   public function gettwomonths($salon_account_id){
		   	$this->db->select('*');
		   	$this->db->from('mill_appointments');
		   	$this->db->where('AccountNo',$salon_account_id);
		   	$this->db->where('dayRange','Twomonths');
		   		$this->db->order_by('id', 'desc');
		   	// echo $this->db->last_query();
		   	// exit;
		   	$this->db->LIMIT('1');
		   	// echo $this->db->last_query();
		   	// exit;

		   	$query = $this->db->get();
		   	return $query->result_array();
		   }
		   public function getfourmonths($salon_account_id){
		   	$this->db->select('*');
		   	$this->db->from('mill_appointments');
		   	$this->db->where('AccountNo',$salon_account_id);
		   	$this->db->where('dayRange','Fourmonths');
		   	$this->db->order_by('id', 'desc');
		   	// echo $this->db->last_query();
		   	// exit;
		   	$this->db->LIMIT('1');
		   	// echo $this->db->last_query();
		   	// exit;

		   	$query = $this->db->get();
		   	return $query->result_array();
		   }
		    public function getsixmonths($salon_account_id){
		   	$this->db->select('*');
		   	$this->db->from('mill_appointments');
		   	$this->db->where('AccountNo',$salon_account_id);
		   	$this->db->where('dayRange','Sixmonths');
		   	$this->db->order_by('id', 'desc');
		   	// echo $this->db->last_query();
		   	// exit;
		   	$this->db->LIMIT('1');
		   	// echo $this->db->last_query();
		   	// exit;

		   	$query = $this->db->get();
		   	return $query->result_array();
		   }

		   public function getsdkerror($salon_id){
		   	$this->db->select('*');
		   	$this->db->from('mill_all_salons_sdk_reports_server');
		   	$this->db->where('salon_id',$salon_id);
		   	$this->db->order_by('id', 'desc');
		   	$this->db->LIMIT('1');
		   	$query = $this->db->get();
		   	return $query->result_array();

		   }

		   
		
		 		  

   }       