<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SeoReports extends CI_Controller {
    
    function __construct(){
        parent::__construct();
    }


    public function CheckLogin(){
    	if(!empty($_POST['email']) && !empty($_POST['password'])){
    		$email = $_POST['email'];
    		$password =  md5($_POST['password']);
    		$redirect_url = $_POST['redirect_url'];

    		$this->db->where('email',$email);
    		$this->db->where('password',$password);
    		$res = $this->db->get('users')->row_array();
    		if(!empty($res)){
    			$_SESSION['seosession'] = $res['id'];
    			redirect($redirect_url);
    		}else{
    			echo "Try again";
    			exit();
    		}
    	}else{
    		echo "Try again";
    	    exit();
    	}
    } 

   

    public function getNewReports($account_no,$start_date,$end_date){

    	if(empty($_SESSION['seosession'])){
            $this->load->view('checkseologin');
    	}else{

	    	/*pa($account_no);
	    	pa($start_date);
	    	pa($end_date);*/

	    	//$start_date_qry = '2019-01-01';
	    	$start_date_qry = date('Y-m-d', strtotime('first day of january this year'));
	    	


	    	$newclients = $this->db->query("SELECT ClientId FROM `mill_clients` where AccountNo = ".$account_no." and date(clientFirstVistedDate) >= '".$start_date_qry."' and date(clientFirstVistedDate) and date(clientFirstVistedDate) <= '".$end_date."'  order by clientFirstVistedDate  asc")->result_array();

	    	$final_display = array();

	    	if(!empty($newclients)){

	    		foreach($newclients as $newclientskey=>$newclientsvalue){
		            $new_client_list[] = $newclientsvalue['ClientId'];
		        }
		        $new_client_list1 = implode(",",$new_client_list); 
		        $get = $this->db->query("SELECT * FROM 
	                        ".MILL_APPTS_TABLE."  
	                        WHERE 
	                        AccountNo = '".$account_no."' and 
	                        SlcStatus != 'Deleted' and
	                        ClientId IN  (".$new_client_list1.") and 
	                        str_to_date(AppointmentDate, '%m/%d/%Y') >= '".$start_date."' and 
	                        str_to_date(AppointmentDate, '%m/%d/%Y') <= '".$end_date."' group by ApptId order by MillLastModifiedDate asc
	                        ")->result_array();

		        if(!empty($get)){

		        	foreach ($get as $key => $value) {
		        		 $display_data['account_no'] = $value['AccountNo'];
		        		 $display_data['client_id'] = $value['ClientId'];
		        		 $this->db->where('ClientId',$value['ClientId']);
		        		 $this->db->where('AccountNo',$value['AccountNo']);
		        		 $this->db->select('Name,Email,clientFirstVistedDate,ReferralTypeId,MobileAreaCode,Mobile');
		        		 $clientdetails = $this->db->get('mill_clients')->row_array();
		        		 $name = explode(" ",$clientdetails['Name']);	    		  	 
		    		  	 $display_data['email'] = $clientdetails['Email'];
		    		  	 $display_data['first_name'] = isset($name[0]) ? $name[0] : "";
		    		  	 $display_data['last_name'] = isset($name[1]) ? $name[1] : "";
	                     $ReferralTypeId = $clientdetails['ReferralTypeId'];
	                     $display_data['phone_number'] = $clientdetails['MobileAreaCode'].$clientdetails['Mobile'];

	                     $source = '';
	                     // ref name
	                     $ref_accountno = $value['AccountNo'];
	                     /*if($ref_accountno=='150537690'){
	                        $ref_accountno = 1086230978;
	                     }*/
	                     $this->db->where('account_no',$ref_accountno);
	                     $this->db->where('iid',$ReferralTypeId);
	                     $ref_details = $this->db->get('referral_types')->row_array();
	                     if(!empty($ref_details)){
	                        $source = $ref_details['cdescr'];
	                     }

	                     $display_data['source'] = $source;


		    		  	 $AppointmentDate = date("Y-m-d",strtotime($value['AppointmentDate']));
		    		  	 $display_data['first_visit_date'] = date("m/d/Y",strtotime($clientdetails['clientFirstVistedDate']));
	                     $display_data['appt_date'] = date("m/d/Y",strtotime($value['AppointmentDate']));
		    		  	 $display_data['first_visit_month'] = date("F",strtotime($clientdetails['clientFirstVistedDate']));

		    		  	 $service_total = 0;
		    		  	 $services= '';
		    		  	 $products_total = 0;
		    		  	 $products= '';
		    		  	 $services_res = array();
		    		  	 $employee_name = '';

		    		  	 $serviceids = $this->db->query("SELECT iservid,EmployeeName FROM 
	                        ".MILL_APPTS_TABLE."  
	                        WHERE 
	                        AccountNo = '".$value['AccountNo']."' and 
	                        SlcStatus != 'Deleted' and
	                        ClientId =  '".$value['ClientId']."' and
	                        ApptId = '".$value['ApptId']."' and
	                        str_to_date(AppointmentDate, '%m/%d/%Y') = '".$AppointmentDate."' ")->result_array();

		    		  	 if(!empty($serviceids)){
		                      foreach($serviceids as $servicelist=>$servicevaluelistvalue){
		                        $new_list[] = $servicevaluelistvalue['iservid'];
		                        $employee_name.= $servicevaluelistvalue['EmployeeName'].",";
		                       
		                      }
		                      $services_list = implode(",",$new_list);  
		                      $serviceidslistarray = explode(",", $services_list);


		                      $this->db->where('account_no',$value['AccountNo']);
		    		  		  $this->db->where('iclientid',$value['ClientId']);
		    		  		  $this->db->where('tdatetime',$AppointmentDate);
		    		  		  $this->db->where('lrefund','false');
		    		  		  $this->db->where_in('iitemid',$serviceidslistarray);
		    		  		  $services_res = $this->db->get('mill_service_sales')->result_array();

			    		  	  
			    		  	  if(!empty($services_res)){
			    		  			 foreach ($services_res as $servicekey => $servicevalue) {
			    		  			 	$services.= $servicevalue['cservicedescription'].",";
			    		  			 	$service_total+= $servicevalue['nquantity'] * $servicevalue['nprice'];
			    		  			 	$iheaderid = $servicevalue['iheaderid'];
			    		  			 }
			    		        }
		    		  	       
		    		  	        if(isset($iheaderid)){
		    		  	        	// retail
				    		  		$this->db->where('account_no',$value['AccountNo']);
				    		  		$this->db->where('iclientid',$value['ClientId']);
				    		  		$this->db->where('tdatetime',$AppointmentDate);
				    		  		$this->db->where('lrefund','false');
				    		  		$this->db->where('iheaderid',$iheaderid);
				    		  		$products_qry = $this->db->get('mill_product_sales')->result_array();
				    		  	    
				    		  		if(!empty($products_qry)){
				    		  			 foreach ($products_qry as $productkey => $productvalue) {
				    		  			 	$products.= $productvalue['cproductdescription'].",";
				    		  			 	$products_total+= $productvalue['nquantity'] * $productvalue['nprice'];
				    		  			 }
				    		  		}

		    		  	        }
			                          
			             }

			             $display_data['product'] = trim($products,",");
			             $display_data['service'] = trim($services,",");
			             $display_data['revenue'] = $service_total + $products_total;
			             $display_data['appt_count'] = count($serviceids);
			             $display_data['employee_name'] =  trim($employee_name,",");

			             $final_display[] = $display_data;


		        	}

		        }
	    	}
	    	$data['final_display'] = $final_display;
	    	//pa($data);

	    	$this->load->view('seoreportsnew',$data);

	    	}

    	
    	
    }



	
	
}
