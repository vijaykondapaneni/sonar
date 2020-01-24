<?php
//error_reporting('0');
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Get_service_retail_total_for_client extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			//header('Content-Type: application/json');
			$this->load->library('session');
			$this->load->library('form_validation');
			$this->load->library('pagination');
			$this->load->helper(array('form', 'url'));
			$this->load->model('Common_model');
			$this->load->model('Service_retail_model');
			$this->load->database();
		}
		
		function index() {
			// wont do anything
		}

		function getServiceAndRetailTotalForClient(){
			//echo "1";exit;
			error_reporting(0);

			$data = array();

			$inputArr = file_get_contents("php://input");
            //print_r($inputArr);
      		$selectedAddonsArray = json_decode($inputArr,true);
      		//print_r($selectedAddonsArray);exit;

      		if(!empty($selectedAddonsArray) && isset($selectedAddonsArray["account_no"]) && !empty($selectedAddonsArray["account_no"]) && isset($selectedAddonsArray["start_date"]) && !empty($selectedAddonsArray["start_date"]) && isset($selectedAddonsArray["end_date"]) && !empty($selectedAddonsArray["end_date"])){

      			$account_no = $selectedAddonsArray["account_no"];

      			$start_date = $selectedAddonsArray["start_date"];
      			$end_date = $selectedAddonsArray["end_date"];

                $start_date_lastyear = date( "Y-m-d", strtotime( $start_date ."-1 year") ); 
                $end_date_lastyear = date( "Y-m-d", strtotime( $end_date ."-1 year") ); 

      			if(isset($selectedAddonsArray["type"]) && !empty($selectedAddonsArray["type"])){
      				$type = $selectedAddonsArray["type"];
      			} else {
      				$type = "";
      			}

      			if(!empty($selectedAddonsArray["client_ids"])){

      				foreach($selectedAddonsArray["client_ids"] as $clientId){

      					if(!empty($type) && $type=='service'){

      						$arr = array('client_id' => $clientId,'start_date' => $start_date,'end_date' => $end_date,'account_no' => $account_no);
      						$serviceSales = $this->Service_retail_model->getServiceTotal($arr);
      						$retailSales = 0;

      					} else if(!empty($type) && $type=='retail') {
      						$arr = array('client_id' => $clientId,'start_date' => $start_date,'end_date' => $end_date,'account_no' => $account_no);
      						$serviceSales = 0;
      						$retailSales = $this->Service_retail_model->getRetailTotal($arr);
      					} else {
      						//echo 1;exit;
      						$arr = array('client_id' => $clientId,'start_date' => $start_date,'end_date' => $end_date,'account_no' => $account_no);
      						$serviceSales = $this->Service_retail_model->getServiceTotal($arr);
      						$retailSales = $this->Service_retail_model->getRetailTotal($arr);
      					}

      					$totalSales[$clientId]['service_sale'] =  number_format($serviceSales, 2, '.', '');
      					$totalSales[$clientId]['retail_sale'] = number_format($retailSales, 2, '.', '');
      				}
      				//print_r($totalSales);exit;
      				$data["sales"] = $totalSales;
      				$data["status"] = true;
      			} else {
      				//Clientids empty
      				if(!empty($type) && $type=='service'){

  						$arr = array('start_date' => $start_date,'end_date' => $end_date,'account_no' => $account_no);
  						$serviceSales = $this->Service_retail_model->getTotalServices($arr);

                        $arr_lastyear = array('start_date' => $start_date_lastyear,'end_date' => $end_date_lastyear,'account_no' => $account_no);
                        $serviceSales_lastyear = $this->Service_retail_model->getTotalServices($arr_lastyear);


  						$retailSales = 0;
                        $retailSales_lastyear = 0;

  					} else if(!empty($type) && $type=='retail') {
  						$serviceSales = 0;
                        $serviceSales_lastyear = 0;

                        $arr = array('start_date' => $start_date,'end_date' => $end_date,'account_no' => $account_no);
  						$retailSales = $this->Service_retail_model->getTotalRetail($arr);

                        $arr_lastyear = array('start_date' => $start_date_lastyear,'end_date' => $end_date_lastyear,'account_no' => $account_no);
                        $retailSales_lastyear = $this->Service_retail_model->getTotalRetail($arr_lastyear);
  					} else {
  						//echo 1;exit;
  						$arr = array('start_date' => $start_date,'end_date' => $end_date,'account_no' => $account_no);
  						$serviceSales = $this->Service_retail_model->getTotalServices($arr);
  						$retailSales = $this->Service_retail_model->getTotalRetail($arr);

                        $arr_lastyear = array('start_date' => $start_date_lastyear,'end_date' => $end_date_lastyear,'account_no' => $account_no);
                        $serviceSales_lastyear = $this->Service_retail_model->getTotalServices($arr_lastyear);
                        $retailSales_lastyear = $this->Service_retail_model->getTotalRetail($arr_lastyear);

  					}

  					$totalSales['service_sale'] =  number_format($serviceSales, 2, '.', '');
      				$totalSales['retail_sale'] = number_format($retailSales, 2, '.', '');

                    $totalSales['service_sale_lastyear'] =  number_format($serviceSales_lastyear, 2, '.', '');
                    $totalSales['retail_sale_lastyear'] = number_format($retailSales_lastyear, 2, '.', '');

      				$data["sales"] = $totalSales;
      				$data["status"] = true;
      			}
      		} else {
      			//JSON decode empty
      			$data["sales"] = array();
      			$data["status"] = false;
      		}
      		echo json_encode($data);
		}


    function getServiceAndRetailTotalForClientWeekWise()
    {
      //echo "1";exit;
      error_reporting(1);

      $data = array();
      $finalArray = array();

      $inputArr = file_get_contents("php://input");
            // print_r($inputArr);
          $selectedAddonsArray = json_decode($inputArr,true);
          // print_r($selectedAddonsArray);exit;

            if(!empty($selectedAddonsArray) && isset($selectedAddonsArray["account_no"]) && !empty($selectedAddonsArray["account_no"])){
                $account_no = $selectedAddonsArray["account_no"];
              foreach ($selectedAddonsArray["details"] as $key => $value) {
                    /*echo $value['clientIds']."<br/>";*/
                    $toCheckClientids = $value['clientIds'];
                    $start_date = $value['start_date'];
                    $end_date = $value['end_date'];
                  
                  $arr = array('client_id' => $toCheckClientids,'start_date' => $start_date,'end_date' => $end_date,'account_no' => $account_no);
                  $query = $this->db->query("SELECT SUM( nprice * nquantity ) as serviceTotal  FROM `mill_service_sales` WHERE account_no = '$account_no' AND tdatetime >= '$start_date' AND tdatetime <= '$end_date' AND iclientid IN($toCheckClientids)");
                  $query1 = $this->db->query("SELECT SUM( nprice * nquantity ) as retailTotal  FROM `mill_product_sales` WHERE account_no = '$account_no' AND tdatetime >= '$start_date' AND tdatetime <= '$end_date' AND iclientid IN($toCheckClientids)");
                  $result = $query->row_array();
                  $result1 = $query1->row_array();
                 
                  /*echo "<pre>";print_r($result);
                  echo "<pre>";print_r($result1);*/
                  $serviceSales = !is_null($result['serviceTotal']) ? round($result['serviceTotal'],2) : "0.00";
                  $retailSales = !is_null($result1['retailTotal']) ? round($result['serviceTotal'],2) : "0.00";
                  $finalArray[$key]['service_sales'] = $serviceSales;
                  $finalArray[$key]['retail_sales'] = $retailSales;
                  $finalArray[$key]['count'] = $value['count'];
              }
              $data['status'] = true;
              $data['data'] = $finalArray;
          } else {
            //JSON decode empty
            $data['data'] = $finalArray;
            $data["status"] = false;
          }
          echo json_encode($data);
    }
	}
?>