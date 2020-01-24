<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class WsServiceRetailRevenue extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			$this->load->library('session');
			$this->load->library('form_validation');
			$this->load->library('pagination');
			$this->load->helper(array('form', 'url'));
			$this->load->database();
		}
		
		function index() {
			// wont do anything
		}

		function testTime()
		{
			echo date("Y-m-d H:i:s");
		}

		function getClientsRevenue(){
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			$dataArray = array();
			$inputArr = file_get_contents("php://input");
			if(!empty($_POST["account_no"]) && !empty($_POST["price"]) && !empty($_POST["startDate"]) && !empty($_POST["endDate"])){

				$account_no = $_POST["account_no"];
				$startDate = $_POST["startDate"];
				$endDate = $_POST["endDate"];
				$price = $_POST["price"];

				$sql_get_clients_ser_retail_revenue = $this->db->query("SELECT account_no,iclientid,sum(total) as fullTotal FROM 
					(SELECT account_no,iclientid,SUM(nprice*nquantity) AS total FROM `mill_service_sales` 
						WHERE 
						account_no = '".$account_no."' AND 
						tdatetime BETWEEN '".$startDate."' AND 
						'".$endDate."' 
						GROUP BY iclientid 
						UNION ALL 
						SELECT account_no,iclientid,SUM(nprice*nquantity) AS total FROM `mill_product_sales` 
						WHERE 
						account_no = '".$account_no."' AND 
						tdatetime BETWEEN '".$startDate."' AND 
						'".$endDate."' 
						GROUP BY iclientid) t group by t.iclientid having fullTotal > '".$price."'")->result_array();
				//echo $this->db->last_query();exit;
				
				if(!empty($sql_get_clients_ser_retail_revenue)){
					$dataArray["data"] = $sql_get_clients_ser_retail_revenue;
					$dataArray["status"] = true;
				} else {
					$dataArray["data"] = array();
					$dataArray["status"] = false;
				}
			} else {
				$dataArray["data"] = array();
				$dataArray["status"] = false;
			}
			echo json_encode($dataArray);
		}


		function getClientsRevenueClientsCount(){
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			$dataArray = array();
			$inputArr = file_get_contents("php://input");
			if(!empty($_POST["account_no"]) && !empty($_POST["price"]) && !empty($_POST["startDate"]) && !empty($_POST["endDate"])){

				$account_no = $_POST["account_no"];
				$startDate = $_POST["startDate"];
				$endDate = $_POST["endDate"];
				$price = $_POST["price"];

				$sql_get_clients_ser_retail_revenue = $this->db->query("select count(*) AS client_count from (SELECT iclientid,sum(total) as fullTotal FROM (SELECT iclientid,SUM(nprice*nquantity) AS total FROM `mill_service_sales` WHERE account_no = '".$account_no."' AND tdatetime BETWEEN '".$startDate."' AND '".$endDate."' GROUP BY iclientid UNION ALL SELECT iclientid,SUM(nprice*nquantity) AS total FROM `mill_product_sales` WHERE account_no = '".$account_no."' AND tdatetime BETWEEN '".$startDate."' AND '".$endDate."' GROUP BY iclientid) t group by t.iclientid having fullTotal > '".$price."') as finals")->row_array();
				//echo $this->db->last_query();exit;
				
				if(isset($sql_get_clients_ser_retail_revenue["client_count"]) && !empty($sql_get_clients_ser_retail_revenue["client_count"])){
					$dataArray["client_count"] = $sql_get_clients_ser_retail_revenue["client_count"];
					$dataArray["status"] = true;
				} else {
					$dataArray["client_count"] = 0;
					$dataArray["status"] = false;
				}
			} else {
				$dataArray["client_count"] = 0;
				$dataArray["status"] = false;
			}
			echo json_encode($dataArray);
		}

		function getApptServicePrices($account_no){

			/*error_reporting(E_ALL);
			ini_set('display_errors', 1);*/
			$dataArray = array();
			$inputArr = file_get_contents("php://input");
			//echo $inputArr;exit;
			$obApptsArray = json_decode($inputArr,true);
			//print_r($obApptsArray);exit;
			if(!empty($obApptsArray['data'])){
				//$account_no = $obApptsArray['data']['account_no'];
				foreach($obApptsArray['data'] as $appt){
					

					$datess = strtotime($appt['ddate']);
 					$appt_date = date('Y-m-d',$datess);//exit;


					$sql_get_clients_ser_retail_revenue = $this->db->query("SELECT s.account_no,s.iclientid,SUM(s.nprice*s.nquantity) AS total,s.cservicedescription,c.Email,c.Name,s.tdatetime FROM `mill_service_sales` s  JOIN mill_clients c ON c.ClientId = s.iclientid AND c.AccountNo = s.account_no
						WHERE 
						s.account_no = ".$account_no." AND 
						s.tdatetime = '".$appt_date."' AND 
						s.iclientid = ".$appt['iclientid']." AND 
						s.iempid = ".$appt['iempid']." AND 
						s.iitemid  = ".$appt['service_id'])->row_array();
					//echo $this->db->last_query();exit;

					if(!empty($sql_get_clients_ser_retail_revenue)){
						$temp_data[] = $sql_get_clients_ser_retail_revenue;
					} else {
						$temp_data[] = array();
					}
				}
				if(!empty($temp_data)){
					$dataArray['prices'] = $temp_data;
				} else {
					$dataArray['prices'] = array();
				}
			} else {
				//NO $obApptsArray is empty
				$dataArray['prices'] = array();
			}

			echo json_encode($dataArray);
		}
	}
?>