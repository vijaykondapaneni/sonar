<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class Ws_services_data extends CI_Controller {
	

   function __construct() {
			parent::__construct();
			$this->load->model('Common_model');
			$this->load->model('Servicesalesimport_model');
			$this->load->database();
		}
        
    /**
     * Default index Fn
     */    
	public function index(){ print "Test";}

	function getLastThreeMonthsServicesForClients(){
		$dataArray = array();
		$fetchedArray = array();
		if(isset($_POST["account_no"]) && !empty($_POST["account_no"]) && isset($_POST["client_id"]) && !empty($_POST["client_id"])){

			$account_no = $_POST["account_no"];
			$client_id = $_POST["client_id"];

			$currentDate = date("Y-m-d");
			$lastThreemonthsDate = date("Y-m-d", strtotime($currentDate . "-3 months"));

			//GET LAST 3 months SERVICE SALES
			$previousServicesArray = $this->db->query("SELECT service.cservicecode,service.cservicedescription,service.tdatetime,service.nprice,emp.name FROM ".MILL_SERVICE_SALES." service 
					join ".STAFF2_TABLE." emp on emp.emp_iid=service.iempid 
					WHERE 
					service.account_no = '".$account_no."' and 
					emp.account_no = '".$account_no."' and 
					service.tdatetime >= '".$lastThreemonthsDate."' and 
					service.tdatetime <= '".$currentDate."' and 
					service.iclientid = '".$client_id."' and 
					service.lrefund = 'false'")->result_array();

			if(!empty($previousServicesArray)){

				foreach($previousServicesArray as $services){
					$service_datas["cservicecode"] = $services["cservicecode"];
					$service_datas["cservicedescription"] = $services["cservicedescription"];
					$service_datas["tdatetime"] = date("m-d-Y", strtotime($services["tdatetime"]));
					$service_datas["nprice"] = $services["nprice"];
					$service_datas["name"] = $services["name"];
					$servicesFetchedArray[] = $service_datas;
				}
				$dataArray["service_data"] = $servicesFetchedArray;
			} else {
				$dataArray["service_data"] = array();
			}
			//GET LAST 3 months RETAIL OR PRODUCT SALES
			$previousRetailsArray = $this->db->query("SELECT retail.cproductcode,retail.cproductdescription,retail.tdatetime,retail.nprice,emp.name FROM ".MILL_PRODUCT_SALES." retail 
					join ".STAFF2_TABLE." emp on emp.emp_iid=retail.iempid 
					WHERE 
					retail.account_no = '".$account_no."' and 
					emp.account_no = '".$account_no."' and 
					retail.tdatetime >= '".$lastThreemonthsDate."' and 
					retail.tdatetime <= '".$currentDate."' and 
					retail.iclientid = '".$client_id."' and 
					retail.lrefund = 'false'")->result_array();

			if(!empty($previousRetailsArray)){

				foreach($previousRetailsArray as $retails){
					$retail_datas["cproductcode"] = $retails["cproductcode"];
					$retail_datas["cproductdescription"] = $retails["cproductdescription"];
					$retail_datas["tdatetime"] = date("m-d-Y", strtotime($retails["tdatetime"]));
					$retail_datas["nprice"] = $retails["nprice"];
					$retail_datas["name"] = $retails["name"];
					$retailsFetchedArray[] = $retail_datas;
				}
				$dataArray["retail_data"] = $retailsFetchedArray;
			} else {
				$dataArray["retail_data"] = array();
			}

		} else {
			//POST DATA EMPTY
			$dataArray["service_data"] = array();
			$dataArray["retail_data"] = array();
		}

		echo json_encode($dataArray);
	}
   
}