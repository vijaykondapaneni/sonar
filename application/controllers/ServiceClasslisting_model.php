<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */

    class ServiceClasslisting_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		  }


	function details()
		{
		 $this->db->select('*');
		$this->db->from('mill_all_sdk_config_details');
		$query=$this->db->get();
		return $query->result_array();
		}
	function colorview($salon_id)
	{
				$keyword="color";
				$this->db->select('salon_id,cclassname,iid');
				$this->db->from('ob_mill_service_class_listing');
				$this->db->where('salon_id',$salon_id);
		        $this->db->where("cclassname LIKE '%$keyword%'");

				$query=$this->db->get();
		// echo $this->db->last_query();
		// exit;
		/*return $query->result_array();*/
					if($query->num_rows()){
						return $query->result_array();
					}else{
						return false;
					}
		

	}
	function chemicalview($salon_id)
		{
			$keyword="Chemical";
		 $this->db->select('iid,cclassname,iid');
		$this->db->from('ob_mill_service_class_listing');
		$this->db->where('salon_id',$salon_id);
        $this->db->where("cclassname LIKE '%$keyword%'");
		$query=$this->db->get();
		// return $query->result_array();
			if($query->num_rows()){
			return $query->result_array();
		}else{
			return false;
		}
		}


	
}
?>
