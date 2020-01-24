<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if(file_exists(APPPATH.'Nusoap_library.php')){
        require_once(APPPATH.'Nusoap_library.php');
}
class WoodHouseReports extends CI_Controller {

    CONST salonAccountId = 2021309399;
    CONST salonId = 371;
    CONST salonMillSdkUrl = 'http://24.106.96.186:6112/webappsdk/MillenniumSDK.asmx';
    CONST salonMillGuid = '3AD20D93-K31C-88F4-52DD-35381DD66DE2';
    CONST salonMillUsername = 'sdk';
    CONST salonMillPassword = 'webapp1234';

   function __construct() {
     parent::__construct();
     $this->load->model('Common_model');
     $this->load->model('User');
     $this->load->library('template');
     
    }

  public function index(){
    if(isset($_GET['signature']) && isset($_GET['timestamp'])){
            $signature = base64_decode($_GET['signature']);
            $timestamp = $_GET['timestamp'];
            //$server_url = 'http://67.43.5.76/~newserver/reports/index.php/';
            $server_url = MAIN_SERVER_URL;
            $service_url = 'Users/Login';

            $checkauthuser = $this->Common_model->checkAuthUser($signature,$timestamp,$server_url,$service_url);
            if(!$checkauthuser){
                if(!$this->session->userdata('isUserLoggedIn')){
                    redirect(site_url('users/login'));
                }
            }else{

            }
        }else{
            if(!$this->session->userdata('isUserLoggedIn')){
                redirect(site_url('users/login'));
            }
    }
    $this->data['title'] = 'WoodHouse Reports';
    $this->data['user'] = $this->User->getRows(array('id'=>$this->session->userdata('userId')));
    $this->data['body'] = $this->load->view('woodhouse/index', $this->data , TRUE);
    $this->template->load('default',$this->data);

   // $this->load->view('woodhouse/header');
   // $this->load->view('woodhouse/index');
  }

  public function reporttype(){
    $reporttype = $_GET['reporttype'];

    $start = $_GET['datepicker_start'];
    $end = $_GET['datepicker_end'];
     
    $data['start_date'] =  date("Y-m-d", strtotime($start));   
    $data['end_date'] =  date("Y-m-d", strtotime($end));

    $displaytime = 'From'. '&nbsp;' . date('l',strtotime($start)) . '&nbsp;' . $start .  '&nbsp;' . 'To ' . date('l',strtotime($end)) . '&nbsp;' . $end ;
    
    $millloginDetails = array('User' => self::salonMillUsername,'Password' => self::salonMillPassword);
    $this->nusoap_library = new Nusoap_library(self::salonMillSdkUrl.'?WSDL','wsdl','','','','');
                     
    $this->millResponseSessionId = $this->nusoap_library->soap_library(self::salonMillSdkUrl,self::salonMillGuid)->getMillMethodCall('Logon',$millloginDetails);          
    //pa($this->millResponseSessionId,'Session');
    if($this->millResponseSessionId){
        if($reporttype == 'MR041'){
            /*$millMethodParams = array('IncludeDeleted' =>0,'IncludeInactive'=>0);
            $results = $this->nusoap_library->getMillMethodCall('GetEmployeeListing',$millMethodParams);
            $data['all'] = $results['EmpInfo'];
            pa($data['all']);*/

            /*$results[] = array('iid'=>169,'cfirstname'=>'Amber','clastname'=>'Kile');
            $results[] = array('iid'=>104,'cfirstname'=>'Amy','clastname'=>'Delph');
            $results[] = array('iid'=>176,'cfirstname'=>'April','clastname'=>'Tragresser');*/

            $results[] = array('iid'=>168,'cfirstname'=>'ERINS','clastname'=>'Stenftenagel');
            $results[] = array('iid'=>22,'cfirstname'=>'House','clastname'=>'House');
            $results[] = array('iid'=>162,'cfirstname'=>'Karen','clastname'=>'Hewett');
            $results[] = array('iid'=>111,'cfirstname'=>'Poteet','clastname'=>'Kelly');
            $results[] = array('iid'=>104,'cfirstname'=>'Amy','clastname'=>'Delph');
            $results[] = array('iid'=>172,'cfirstname'=>'Jewel','clastname'=>'Emery');
            $results[] = array('iid'=>171,'cfirstname'=>'Kourtney','clastname'=>'Hoopingarner');
            $data['all'] = $results;
            $data['displaytime'] = $displaytime;
            $data['title'] = 'WoodHouse MR041 Reports';
            $data['user'] = $this->User->getRows(array('id'=>$this->session->userdata('userId')));
            //pa($data,'data',true);
            $data['body'] = $this->load->view('woodhouse/serviceandretail',$data,TRUE);
            $this->template->load('default',$data);
            //$this->load->view('woodhouse/header');
            //$this->load->view('woodhouse/serviceandretail',$data);
        }
        elseif($reporttype == 'MR045'){
            /*$millMethodParams = array('IncludeDeleted' =>0,'IncludeInactive'=>0);
            $results = $this->nusoap_library->getMillMethodCall('GetEmployeeListing',$millMethodParams);
            $data['all'] = $results['EmpInfo'];
            pa($data['all']);*/

            /*$results[] = array('iid'=>169,'cfirstname'=>'Amber','clastname'=>'Kile');
            $results[] = array('iid'=>104,'cfirstname'=>'Amy','clastname'=>'Delph');
            $results[] = array('iid'=>176,'cfirstname'=>'April','clastname'=>'Tragresser');*/
            /*$results[] = array('iid'=>168,'cfirstname'=>'ERINS','clastname'=>'Stenftenagel');
            $results[] = array('iid'=>22,'cfirstname'=>'House','clastname'=>'House');
            $results[] = array('iid'=>162,'cfirstname'=>'Karen','clastname'=>'Hewett');
            $results[] = array('iid'=>111,'cfirstname'=>'Poteet','clastname'=>'Kelly');
            $results[] = array('iid'=>104,'cfirstname'=>'Amy','clastname'=>'Delph');*/
            //$results[] = array('iid'=>172,'cfirstname'=>'Jewel','clastname'=>'Emery');
            //$results[] = array('iid'=>171,'cfirstname'=>'Kourtney','clastname'=>'Hoopingarner');


            $results[] = array('iid'=>162,'cfirstname'=>'Karen','clastname'=>'Hewett');
            $results[] = array('iid'=>111,'cfirstname'=>'Poteet','clastname'=>'Kelly');
            $results[] = array('iid'=>171,'cfirstname'=>'Kourtney','clastname'=>'Hoopingarner');
          // $results[] = array('iid'=>30,'cfirstname'=>'Kelly','clastname'=>'Poteet');


            $data['all'] = $results;
            $data['displaytime'] = $displaytime;
            // get service sales

            $millMethodParams['StartDate'] = $data['start_date'];
            $millMethodParams['EndDate'] = $data['end_date'];
            $millMethodParams['IncludeVoided'] = 0;
            $totalservicesales = $this->nusoap_library->getMillMethodCall('GetServiceTotalSales',$millMethodParams);
           // pa($totalservicesales,'',false);
            // lrefund true
            $totalserivcesalesrefundtrue = isset($totalservicesales['ServiceTotalSales']['0']['ntotal'])?$totalservicesales['ServiceTotalSales']['0']['ntotal']:0;

            $totalserivcesalesrefundfalse = isset($totalservicesales['ServiceTotalSales']['1']['ntotal'])?$totalservicesales['ServiceTotalSales']['1']['ntotal']:0;

            $totalservicesamonut = $totalserivcesalesrefundfalse - $totalserivcesalesrefundtrue;

            $totalservicesamonut = !empty($totalservicesamonut) ? $totalservicesamonut :0;
            $data['totalservicesamonut'] = $totalservicesamonut;
            // get product sales

            $totalproductsales = $this->nusoap_library->getMillMethodCall('GetProductTotalSales',$millMethodParams);
            //pa($totalproductsales,'',false);
            // lrefund true
            $totalproductsalesrefundtrue = isset($totalproductsales['ProductTotalSales']['0']['ntotal'])?$totalproductsales['ProductTotalSales']['0']['ntotal']:0;

            $totalproductsalesrefundfalse = isset($totalproductsales['ProductTotalSales']['1']['ntotal'])?$totalproductsales['ProductTotalSales']['1']['ntotal']:0;

            $totalproductamonut = $totalproductsalesrefundfalse - $totalproductsalesrefundtrue;

            $totalproductsamonut = !empty($totalproductamonut) ? $totalproductamonut :0;
            $data['totalproductsamonut'] = $totalproductsamonut;
            // Gift Certificates
            $totalgiftcards = $this->nusoap_library->getMillMethodCall('GetGiftCertificateSales',$millMethodParams);

            $giftcards_nprice = !empty($totalgiftcards['GCSales']) ? 
                                array_column($totalgiftcards['GCSales'], "nprice") : array();
            $data['giftcards_nprice_total'] = array_sum($giftcards_nprice);
            $data['totalgiftcards']   = $totalgiftcards['GCSales'];

            // tanning
            $totaltanning = $this->nusoap_library->getMillMethodCall('GetTanningSalesByDate',$millMethodParams);
            $tannig_nprice = !empty($totaltanning['TransTan']) ? 
                                array_column($totaltanning['TransTan'], "nprice") : array();
            $data['tanning_nprice_total'] = array_sum($tannig_nprice);
            if(!empty($totaltanning)){
              $data['totaltanning']   =  $totaltanning['TransTan'];
            }else{
                $data['totaltanning']   = array();
            }

            //packages
            $totalpackages = $this->nusoap_library->getMillMethodCall('GetPackageSeriesSalesListingByDate',$millMethodParams);
            $package_nprice = !empty($totalpackages['Package']) ? 
                                array_column($totalpackages['Package'], "namount") : array();
            $data['package_nprice_total'] = array_sum($package_nprice);
            if(!empty($totalpackages)){
              $data['totalpackage']   =  $totalpackages['Package'];
            }else{
                $data['totalpackage']   = array();
            }

            //membership

            $totalmemberships = $this->nusoap_library->getMillMethodCall('GetMembershipSalesByDate',$millMethodParams);
            $membership_nprice = !empty($totalmemberships['MembershipSale']) ? 
                                array_column($totalmemberships['MembershipSale'], "nprice") : array();
            $data['membership_nprice_total'] = array_sum($membership_nprice);
            if(!empty($totalmemberships)){
              $data['totalmemberships']   =  $totalmemberships['MembershipSale'];
            }else{
                $data['totalmemberships']   = array();
            }

            // servicesalesinvoices
             $getservicesale = $this->nusoap_library->getMillMethodCall('GetServiceSales',$millMethodParams);

            $serviceInvoices = !empty($getservicesale['ServiceSales']) ? 
                                array_column($getservicesale['ServiceSales'], "cinvoiceno") : array();
            $serviceInvoicescount= sizeof(array_unique($serviceInvoices));

            // productsalesinvoices
             $getproductsales = $this->nusoap_library->getMillMethodCall('GetProductSales',$millMethodParams);

            $productInvoices = !empty($getproductsales['ProductSales']) ? 
                                array_column($getproductsales['ProductSales'], "cinvoiceno") : array();
            $productInvoicescount= sizeof(array_unique($productInvoices));

            $totatticket_array = array_intersect($serviceInvoices,$productInvoices);

            $data['totalticketscount'] = sizeof($totatticket_array);
            $data['serviceInvoicescount'] = $serviceInvoicescount;
            $data['productInvoicescount'] = $productInvoicescount;

            // clients

            $plus_staff = array('iappttype'=>-2);
            $newclients = array_filter(
                                $getservicesale['ServiceSales'],
                                function ($key) use ($plus_staff) {
                                  return in_array($key["iappttype"], $plus_staff);
                                });

            $newclients_count_service = sizeof($newclients); 

            $totalclients_service_array =  !empty($getservicesale['ServiceSales']) ? 
                                array_column($getservicesale['ServiceSales'], "iappttype") : array();

            $totalclients_service_count = sizeof($totalclients_service_array);                    
            $repeated_clients_count_service =  $totalclients_service_count-$newclients_count_service;

            $plus_staff = array('iappttype'=>-2);
            $newclients_products = array_filter(
                                $getproductsales['ProductSales'],
                                function ($key) use ($plus_staff) {
                                  return in_array($key["iappttype"], $plus_staff);
                                });
            $newclients_count_products = sizeof($newclients_products); 

            $totalclients_product_array =  !empty($getproductsales['ProductSales']) ? 
                                array_column($getproductsales['ProductSales'], "iappttype") : array();

            $totalclients_product_count = sizeof($totalclients_product_array);                    
            $repeated_clients_count_product =  $totalclients_product_count-$totalclients_product_count;

            $data['totalnewclients'] =   $newclients_count_service + $newclients_count_products;
            $data['totalrepeatedclients'] = $repeated_clients_count_service+$repeated_clients_count_product;
            $data['totalclients'] = $totalclients_service_count+$totalclients_product_count;
             
           // pa($data['totalgiftcards'],'yes',false);
           // $this->load->view('woodhouse/header');
           // $this->load->view('woodhouse/mr045',$data);
            $data['title'] = 'WoodHouse MR045 Reports';
            $data['user'] = $this->User->getRows(array('id'=>$this->session->userdata('userId')));
            //pa($data,'data',true);
            $data['body'] = $this->load->view('woodhouse/mr045',$data,TRUE);
            $this->template->load('default',$data);
        }elseif($reporttype == 'MA055'){
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
				/*$this->load->view('woodhouse/header');
				$this->load->view('woodhouse/ma055',$data);*/
                $data['title'] = 'WoodHouse MA055 Reports';
                $data['user'] = $this->User->getRows(array('id'=>$this->session->userdata('userId')));
                //pa($data,'data',true);
                $data['body'] = $this->load->view('woodhouse/ma055',$data,TRUE);
                $this->template->load('default',$data);
			}
         elseif($reporttype == 'MR085'){
                $millMethodParams['StartDate'] = $data['start_date'];
                $millMethodParams['EndDate'] = $data['end_date'];
                $millMethodParams['IncludeVoided'] = 0;
                // giftcards with balance
              /*  $giftcardsbalance = $this->nusoap_library->getMillMethodCall('GetGiftCertificatesWithBalance',$millMethodParams);

                pa($giftcardsbalance,'giftcardsbalance',false);

                $gcnpricetotalarr = !empty($giftcardsbalance['GCWithBalance']) ? 
                                    array_column($giftcardsbalance['GCWithBalance'], "nprice") : array();

                $gcnpricetotal = array_sum($gcnpricetotalarr);
                pa($gcnpricetotal,'gcnpricetotal',false);

                $gcnorigamttotalarr = !empty($giftcardsbalance['GCWithBalance']) ? 
                                    array_column($giftcardsbalance['GCWithBalance'], "norigamt") : array();
                                    
                $gcnorigamttotal = array_sum($gcnorigamttotalarr);

                pa($gcnorigamttotal,'gcnorigamttotalarr',false);

                 $gcnamtlefttotalarr = !empty($giftcardsbalance['GCWithBalance']) ? 
                                    array_column($giftcardsbalance['GCWithBalance'], "namtleft") : array();
                                    
                $gcnamtlefttotal = array_sum($gcnamtlefttotalarr);

                pa($gcnamtlefttotal,'gcnamtlefttotal',false);
                $gcbalancetotal = $gcnpricetotal+$gcnorigamttotal+$gcnamtlefttotal;

                pa($gcbalancetotal,'gcbalancetotal',true);*/
               
            // pacakages
                $packages = $this->nusoap_library->getMillMethodCall('GetPackageSeriesSalesListingByDate',$millMethodParams);
                $data['packages'] = !empty($packages)?$packages['Package']:array();
                //refundpackages  
                $refundpackages = $this->nusoap_library->getMillMethodCall('GetPackageSeriesRefunds',$millMethodParams);
               if(!empty($refundpackages)){
                if(!isset($refundpackages['refundpkg'][0]))  
                 {
                     $refundpackages_arr[] = $refundpackages['refundpkg'];
                     $tempArr['refundpkg'] = $refundpackages_arr;
                     unset($refundpackages['refundpkg']);
                     $refundpackages = $tempArr;
                 }
                } 
            
                $data['refundpackages'] = !empty($refundpackages)?$refundpackages['refundpkg']:array();
                // pa($data['refundpackages'],'refundpackages',true); 
               
                // giftcards refunds
                $returngiftcardsarr = $this->nusoap_library->getMillMethodCall('GetGiftCertificateRefunds',$millMethodParams);
               // pa($returngiftcardsarr,'refunds',true);
                $data['returngiftcards'] = !empty($returngiftcardsarr)?$returngiftcardsarr['refundgc']:array();
                $giftcardsales = $this->nusoap_library->getMillMethodCall('GetGiftCertificateSales',$millMethodParams);
                $data['giftcards'] = !empty($giftcardsales)?$giftcardsales['GCSales']:array();
               // pa($giftcardsales,'giftcardsales',true);

                $servicesales = $this->nusoap_library->getMillMethodCall('GetServiceSales',$millMethodParams);
                $servicesalesarray = !empty($servicesales) ? $servicesales['ServiceSales']:array();
                $data['servicesales'] = $servicesalesarray;
                //pa($servicesales,'servicesales',false);
                /*$servicesalescodes = !empty($servicesales['ServiceSales']) ? 
                                    array_column($servicesales['ServiceSales'], "cservicecode") : array();*/
                //$servicesalesUniquecodes = array_unique($servicesalescodes);
                //pa($servicesalesUniquecodes,'servicecodes',true);
                $productsales = $this->nusoap_library->getMillMethodCall('GetProductSales',$millMethodParams);
               // pa($productsales,'productsales',true);
                $productsalesarray = !empty($productsales) ? $productsales['ProductSales']:array();
                $data['productsales'] = $productsalesarray;
                $data['displaytime'] = $displaytime;
                //$this->load->view('woodhouse/header');
                //$this->load->view('woodhouse/mr085',$data);
                $data['title'] = 'WoodHouse MR085 Reports';
                $data['user'] = $this->User->getRows(array('id'=>$this->session->userdata('userId')));
                //pa($data,'data',true);
                $data['body'] = $this->load->view('woodhouse/mr085',$data,TRUE);
                $this->template->load('default',$data);
            }   
		} // if close
    } // function close   

    function is_assoc($array){
		$array = array_keys($array); return ($array !== array_keys($array));
	}



  public function serviceandretail(){
    // get employee listing


    $this->load->view('woodhouse/serviceandretail');
  }  
  
 
} 

