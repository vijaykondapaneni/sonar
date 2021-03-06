<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	
	class Ws_get_retail_sales_with_client_info extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			$this->load->library('session');
			$this->load->library('form_validation');
			$this->load->library('pagination');
			$this->load->helper(array('form', 'url'));
			$this->load->database();

			$this->load->model('Common_model');
			$this->load->model('Appointmentsimport_model');
			$this->load->model('Twoyearclientsimport_model');
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		}

		function checkTime()
		{
			echo date("Y-m-d H:i:s");
		}

		function getRetailSalesInfo(){

            $data = array();
            
            if(!empty($_POST['salonCode']) && !empty($_POST['startDate']) && !empty($_POST['endDate'])){
            	$start_date = $_POST['startDate'];
            	$end_date = $_POST['endDate'];
            	$account_no = $_POST['salonCode'];

            	$this->db->select('mill_clients.Name,mill_clients.Email,SUM(mill_product_sales.nprice*mill_product_sales.nquantity) as Price,mill_product_sales.cproductdescription,mill_product_sales.tdatetime');
            	$this->db->join('mill_clients', 'mill_product_sales.account_no = mill_clients.AccountNo AND mill_product_sales.iclientid = mill_clients.ClientId', 'inner');
            	$this->db->where('mill_product_sales.tdatetime >=',$start_date);
            	$this->db->where('mill_product_sales.tdatetime <=',$end_date);
            	$this->db->where('mill_product_sales.account_no',$account_no);
            	$this->db->where('mill_product_sales.lrefund','false');
            	$this->db->group_by('mill_clients.ClientId');
            	$getRetailDetails = $this->db->get('mill_product_sales')->result_array();
                  //print_r($getRetailDetails);exit;
            	//echo $this->db->last_query();exit;
            	if(!empty($getRetailDetails)){
            		$data['data'] = $getRetailDetails;
            		$data['status'] = true;
            	} else {
            		$data['data'] = array();
            		$data['status'] = false;
            	}

            } else {
            	$data['data'] = array();
            	$data['status'] = false;
            }

            echo json_encode($data);
        }



	}
?>