<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */
    class Servicesalesimport_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
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
		  DELETE SALON SERVICES DATA
		*/ 
		 public function deleteMillServiceSales($whereconditions)
         {
            $this->db->where('id',$whereconditions['id']);
            $this->db->where('account_no',$whereconditions['account_no']);
            $this->db->delete(MILL_SERVICE_SALES); 
         }
		 
   }       