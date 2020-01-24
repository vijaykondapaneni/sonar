<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */
    class Salongiftcardimport_model extends CI_Model {
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
		 	$insert_query = $this->db->insert_string(MILL_GIFT_CARD_SALES_WITH_BALANCE, $data);
			$insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
			$this->db->query($insert_query);
			$insert_id =  $this->db->insert_id();
			return $insert_id;
		 }
		 /**
		    Update GIFT CARD SALES DATA
		 */ 
		 public function updateGiftCardSalesWithBalance($whereconditions,$data){
            $this->db->where('iid',$whereconditions['iid']);
			$this->db->where('cgiftnumber',$whereconditions['cgiftnumber']);
			$this->db->where('iheaderid',$whereconditions['iheaderid']);
			$this->db->where('cinvoiceno',$whereconditions['cinvoiceno']);
			$this->db->where('account_no',$whereconditions['account_no']);
			$update = $this->db->update(MILL_GIFT_CARD_SALES_WITH_BALANCE, $data);
			return $update;
			
		 }
		  
   }       