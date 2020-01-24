<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class WoodHouseEmployeePerformanceReports extends CI_Controller {

    CONST salonAccountId = 2021309399;
    CONST salonId = 371;
    CONST salonMillSdkUrl = 'http://24.106.96.186:6112/webappsdk/MillenniumSDK.asmx';
    CONST salonMillGuid = '3AD20D93-K31C-88F4-52DD-35381DD66DE2';
    CONST salonMillUsername = 'sdk';
    CONST salonMillPassword = 'webapp1234';

   function __construct() {
     parent::__construct();
     $this->load->model('Common_model');
}

  public function index(){
    $this->load->view('woodhouse/header');
    $this->load->view('woodhouse/index');
  }

  public function reporttype(){
    $reporttype = $_POST['reporttype'];

    $start = $_POST['datepicker_start'];
    $end = $_POST['datepicker_end'];
    
    $data['start_date'] =  date("Y-m-d", strtotime($start));   
    $data['end_date'] =  date("Y-m-d", strtotime($end));

    $displaytime = 'From'. '&nbsp;' . date('l',strtotime($start)) . '&nbsp;' . $start .  '&nbsp;' . 'To ' . date('l',strtotime($end)) . '&nbsp;' . $end ;
    
    $millloginDetails = array('User' => self::salonMillUsername,'Password' => self::salonMillPassword);
    $this->nusoap_library = new Nusoap_library(self::salonMillSdkUrl.'?WSDL','wsdl','','','','');
                     
    $this->millResponseSessionId = $this->nusoap_library->soap_library(self::salonMillSdkUrl,self::salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);          
    //pa($this->millResponseSessionId,'Session');
			/*$millMethodParams = array('IncludeDeleted' =>0,'IncludeInactive'=>0);
            $results = $this->nusoap_library->getMillMethodCall('GetEmployeeListing',$millMethodParams);
            $data['all'] = $results['EmpInfo'];
            pa($data['all']);
			exit;*/
            /*$results[] = array('iid'=>169,'cfirstname'=>'Amber','clastname'=>'Kile');
            $results[] = array('iid'=>104,'cfirstname'=>'Amy','clastname'=>'Delph');
            $results[] = array('iid'=>176,'cfirstname'=>'April','clastname'=>'Tragresser');*/
            $results[] = array('iid'=>188,'cfirstname'=>'Allison','clastname'=>'Savannah');
            $results[] = array('iid'=>181,'cfirstname'=>'Amber','clastname'=>'Kile');
            $results[] = array('iid'=>104,'cfirstname'=>'Amy','clastname'=>'Delph');
            $results[] = array('iid'=>151,'cfirstname'=>'April','clastname'=>'Tragresser');
            $results[] = array('iid'=>104,'cfirstname'=>'Amy','clastname'=>'Delph');
            $results[] = array('iid'=>183,'cfirstname'=>'Becky','clastname'=>'Dick');
            $results[] = array('iid'=>184,'cfirstname'=>'Caitlin','clastname'=>'Clancy');
			// pa($results);
    if($this->millResponseSessionId){
        if($reporttype == 'MR041'){
            
            $data['all'] = $results;
            $data['displaytime'] = $displaytime;
            $this->load->view('woodhouse/header');
            $this->load->view('woodhouse/serviceandretail',$data);
        }
        elseif($reporttype == 'MA055'){
          
            $data['all'] = $results;
            $data['displaytime'] = $displaytime;
			$new = 0;
			$repeat = 0;
			$total_price = 0;
			$ser_invoice_array = array();
			$ser_invoice = 0;
            // get service sales
			$return_data = array();
			//echo "<pre>";
			foreach($results as $key => $res){
					$employee_data = $res;
					$millMethodParams['XmlIds'] = '<NewDataSet><Ids><Id>'.$res['iid'].'</Id></Ids></NewDataSet>';
					$millMethodParams['StartDate'] = $data['start_date'];
					$millMethodParams['EndDate'] = $data['end_date'];
					$millMethodParams['IncludeVoided'] = 0;
					//print_r($millMethodParams);
					$employee_servicesales = $this->nusoap_library->getMillMethodCall('GetServiceSalesByEmployee',$millMethodParams);
					//pa($employee_servicesales,'',false);
					if(!empty($employee_servicesales['ServiceSalesByEmployee'])){
							if($this->is_assoc($employee_servicesales['ServiceSalesByEmployee'])){
									$servicesales = $employee_servicesales['ServiceSalesByEmployee'];
									if($servicesales['iappttype'] == -2){
											$new++;
									}else{
											$repeat++;
									}
									if(isset($servicesales['nprice']) && isset($servicesales['nquantity'])){
											$price_quantity = $servicesales['nprice']*$servicesales['nquantity'];
											$total_price = $total_price + $price_quantity;
									}
									if(isset($servicesales['cinvoiceno']) && in_array($servicesales['cinvoiceno'],$ser_invoice_array) === false){
											array_push($ser_invoice_array, $servicesales['cinvoiceno']);
											$ser_invoice++;
									}	
							}else{
									foreach($employee_servicesales['ServiceSalesByEmployee'] as $servicesales){
										//print_r($servicesales);
											if($servicesales['iappttype'] == -2){
													$new++;
											}else{
													$repeat++;
											}
											if(isset($servicesales['nprice']) && isset($servicesales['nquantity'])){
													$price_quantity = $servicesales['nprice']*$servicesales['nquantity'];
													$total_price = $total_price + $price_quantity;
											}
											if(isset($servicesales['cinvoiceno']) && in_array($servicesales['cinvoiceno'],$ser_invoice_array) === false){
													array_push($ser_invoice_array, $servicesales['cinvoiceno']);
													$ser_invoice++;
											}		
									}
							}
					}
					$total_clients = $new+$repeat;
					$employee_data['new_clients'] = number_format($new,2);
					$employee_data['repeat_clients'] = number_format($repeat,2);
					$employee_data['total_clients'] = number_format($total_clients,2);
					
					$avg_service_ticket = 0;
					if($ser_invoice != 0){
							$avg_service_ticket = $total_price/$ser_invoice;
					}
					
					//Product Sales 
					$employee_productsales = $this->nusoap_library->getMillMethodCall('GetProductSalesByEmployee',$millMethodParams);
					//pa($employee_productsales,'',false);
					$quantity = 0;
					$clients = 0;
					$total_prod_price = 0;
					$prod_invoice_array = array();
					$prod_invoice = 0;
					if(!empty($employee_productsales['ProductSalesByEmployee'])){
							if($this->is_assoc($employee_productsales['ProductSalesByEmployee'])){
									$productsales = $employee_productsales['ProductSalesByEmployee'];
									if(isset($productsales['nquantity'])){
											$quantity = $quantity + $productsales['nquantity'];
									}
									if(isset($productsales['iclientid'])){
											$clients++;
									}
									if(isset($productsales['nprice']) && isset($productsales['nquantity'])){
											$prod_price_quantity = $productsales['nprice']*$productsales['nquantity'];
											$total_prod_price = $total_prod_price + $prod_price_quantity;
									}	
									if(isset($productsales['cinvoiceno']) && in_array($productsales['cinvoiceno'],$prod_invoice_array) === false){
											array_push($prod_invoice_array, $productsales['cinvoiceno']);
											$prod_invoice++;
									}		
							}else{
									foreach($employee_productsales['ProductSalesByEmployee'] as $productsales){
											if(isset($productsales['nquantity'])){
													$quantity = $quantity + $productsales['nquantity'];
											}	
											if(isset($productsales['iclientid'])){
													$clients++;
											}	
											if(isset($productsales['nprice']) && isset($productsales['nquantity'])){
													$prod_price_quantity = $productsales['nprice']*$productsales['nquantity'];
													$total_prod_price = $total_prod_price + $prod_price_quantity;
											}
											if(isset($productsales['cinvoiceno']) && in_array($productsales['cinvoiceno'],$prod_invoice_array) === false){
													array_push($prod_invoice_array, $productsales['cinvoiceno']);
													$prod_invoice++;
											}		
									}
							}
					}
					if($clients != 0){
						$retialunits_retailclients = $quantity/$clients;
					}else{
						$retialunits_retailclients = 0;
					}
					
					$retialunits_serviceclients = $quantity/$total_clients;
					$employee_data['retialunits_retailclients'] = number_format($retialunits_retailclients,2);
					$employee_data['retialunits_serviceclients'] = number_format($retialunits_serviceclients,2);
					
					$avg_retail_ticket = 0;
					if($prod_invoice != 0){
						$avg_retail_ticket = $total_prod_price/$prod_invoice;
					}
					
					
					$employee_data['avg_service_ticket'] = number_format($avg_service_ticket,2);
					$employee_data['avg_retail_ticket'] = number_format($avg_retail_ticket,2);
					
					
					
					$return_data[$key] = $employee_data;					
			}
			$data['employee_details'] = $return_data;
            //pa($return_data,'yes',false);
            $this->load->view('woodhouse/header');
            $this->load->view('woodhouse/ma055',$data);
        }
     }   

    
  }

	function is_assoc($array){
		$array = array_keys($array); return ($array !== array_keys($array));
	}

  public function serviceandretail(){
    // get employee listing


    $this->load->view('woodhouse/serviceandretail');
  }  
  
 
  
 
} 

