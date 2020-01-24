<?php 
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	class Service_retail_model extends CI_Model {

		public function __construct() {
			$this->load->database();
	    	//$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		}

		function getServiceTotal($array_data){
			if(!empty($array_data) && !empty($array_data["client_id"]) && !empty($array_data["start_date"]) && !empty($array_data["end_date"]) && !empty($array_data["account_no"])){

				$account_no = $array_data["account_no"];
				$client_id = $array_data["client_id"];
				$start_date = $array_data["start_date"];
				$end_date = $array_data["end_date"];

				$this->db->select('SUM(nprice) as service_total');
				$this->db->where(array('account_no' =>$account_no,'iclientid' => $client_id,'tdatetime >=' => $start_date, 'tdatetime <=' => $end_date));
				$getServicesales = $this->db->get(MILL_SERVICE_SALES);
				//echo $this->db->last_query();exit;
				$getServiceSalesTotal = $getServicesales->row_array();
				return $getServiceSalesTotal["service_total"];

			} else {
				return 0;
			}
		}

		function getRetailTotal($array_data){
			if(!empty($array_data) && !empty($array_data["client_id"]) && !empty($array_data["start_date"]) && !empty($array_data["end_date"]) && !empty($array_data["account_no"])){

				$account_no = $array_data["account_no"];
				$client_id = $array_data["client_id"];
				$start_date = $array_data["start_date"];
				$end_date = $array_data["end_date"];

				$this->db->select('SUM(nprice) as retail_total');
				$this->db->where(array('account_no' =>$account_no,'iclientid' => $client_id,'tdatetime >=' => $start_date, 'tdatetime <=' => $end_date));
				$getRetailsales = $this->db->get(MILL_PRODUCT_SALES);
				$getRetailSalesTotal = $getRetailsales->row_array();
				return $getRetailSalesTotal["retail_total"];

			} else {
				return 0;
			}
		}

		function getTotalServices($array_data){
			if(!empty($array_data) && !empty($array_data["start_date"]) && !empty($array_data["end_date"]) && !empty($array_data["account_no"])){

				$account_no = $array_data["account_no"];
				$start_date = $array_data["start_date"];
				$end_date = $array_data["end_date"];

				$this->db->select('SUM(nprice) as service_total');
				$this->db->where(array('account_no' =>$account_no,'tdatetime >=' => $start_date, 'tdatetime <=' => $end_date));
				$getServicesales = $this->db->get(MILL_SERVICE_SALES);
				//echo $this->db->last_query();exit;
				$getServiceSalesTotal = $getServicesales->row_array();
				return $getServiceSalesTotal["service_total"];

			} else {
				return 0;
			}
		}

		function getTotalRetail($array_data){
			if(!empty($array_data) && !empty($array_data["start_date"]) && !empty($array_data["end_date"]) && !empty($array_data["account_no"])){

				$account_no = $array_data["account_no"];
				$start_date = $array_data["start_date"];
				$end_date = $array_data["end_date"];

				$this->db->select('SUM(nprice) as retail_total');
				$this->db->where(array('account_no' =>$account_no,'tdatetime >=' => $start_date, 'tdatetime <=' => $end_date));
				$getRetailsales = $this->db->get(MILL_PRODUCT_SALES);
				$getRetailSalesTotal = $getRetailsales->row_array();
				return $getRetailSalesTotal["retail_total"];

			} else {
				return 0;
			}
		}

	}

?>