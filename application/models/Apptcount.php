<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */

    class Apptcount extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		  }


function staff()
{
 $this->db->select('*');
$this->db->from('mill_all_sdk_config_details');
$query=$this->db->get();
return $query->result_array();
}

	function countview($salon_account_id=0)
	{
	 $this->db->select('count(*) as count');
	$this->db->from('plus_staff2');
	$this->db->where('account_no',$salon_account_id);

	$query=$this->db->get();
	// echo  $this->db->last_query();
	// exit;
	return $query->result_array();
	}	

	function clientcount($salon_account_id=0)
	{
		$this->db->select('COUNT(DISTINCT(Id)) AS client');
		$this->db->from('mill_clients');
		$this->db->where('AccountNo',$salon_account_id);

		$query1=$this->db->get();
		// echo  $this->db->last_query();
		// exit;
		return $query1->result_array();
	}
		
		function appcount($salon_account_id=0)
	{
		$this->db->select('COUNT(DISTINCT(Id)) AS appts');
		$this->db->from('mill_appointments');
		$this->db->where('AccountNo',$salon_account_id);

		$query1=$this->db->get();
		// echo  $this->db->last_query();
		// exit;
		return $query1->result_array();
	}

function featappcount($salon_account_id=0)
{

	 $query1 =$this->db->query("SELECT COUNT(DISTINCT(Id))  AS fetappts FROM mill_appointments WHERE AccountNo = '$salon_account_id' AND str_to_date(AppointmentDate, '%m/%d/%Y' ) > '2017-11-05'");
return $query1->result_array();
}
		}
