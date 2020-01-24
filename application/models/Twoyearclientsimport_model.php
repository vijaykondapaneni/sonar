<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */
    class Twoyearclientsimport_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
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
			$this->db->where('AccountNo',$whereconditions['AccountNo']);
			return $this->db->update(MILL_CLIENTS_TABLE, $data);
		 } 
		
   }       