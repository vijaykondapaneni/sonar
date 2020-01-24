<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Product/Retail Sales Model
	 * Contains all the queries which are related Product/Retail Sales Module.
	 */
    class Productsalesimport_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
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
			$insert_query = $this->db->insert_string(MILL_PRODUCT_SALES, $data);
			$insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO',$insert_query);
			$this->db->query($insert_query);
			$insert_id =  $this->db->insert_id();
			return $insert_id;
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
			$this->db->update(MILL_PRODUCT_SALES, $data);
			return true;
		 }
         
         public function deleteMillProductSales($whereconditions)
         {
            $this->db->where('id',$whereconditions['id']);
            $this->db->where('account_no',$whereconditions['account_no']);
            $this->db->delete(MILL_PRODUCT_SALES);
         }

   }       