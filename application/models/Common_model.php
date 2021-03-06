<?php  
	defined('BASEPATH') OR exit('No direct script access allowed');

	/**
	 * Class Forms_model
	 * Contains all the queries which are related Forms Module.
	 */

    class Common_model extends CI_Model {
	     public function __construct() {
			parent::__construct();
			$this->DB_ReadOnly = $this->load->database('read_only', TRUE);
		  }
         /**
         This function for get mill sdk config details 
         */
		 public function getMillSdkConfigDetails($account_no){
		 	if(!empty($account_no))
			{
				$names = array($account_no);
				$this->DB_ReadOnly->where_in('salon_account_id', $names);
			}
			$this->DB_ReadOnly->where('status',0);
			return $this->DB_ReadOnly->get(MILL_ALL_SDK_CONFIG_DETAILS);
		 }
		 /**
         This function for get mill sdk config details 
         */
		 public function getMillSdkConfigDetailsByGuid($mill_guid){
		 	$this->DB_ReadOnly->where('mill_guid', $mill_guid);
			return $this->DB_ReadOnly->get(MILL_ALL_SDK_CONFIG_DETAILS);
		 }
		/**
         This function for all curl methods 
        */ 
         public function getCurlData($url,$data){
         	if($url=='https://saloncloudsplus.com/wsInfotoIntServer/getSalonInfoFromSalonId'){
         		$salon_id = $data['salon_id'];
         		return $this->getSalonInfoBy($salon_id);
         	}else{
         		$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,$url);
				// for local server
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	            // close
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
				$salonResult=curl_exec($ch);
				curl_close($ch);
				return json_decode($salonResult,true);
         	}
         	
        }
        /**
         This function for insert data by using curl
        */ 
         public function insertDataByUsingCUrl($url,$data=''){
         	$ch = curl_init(); 
            curl_setopt($ch,CURLOPT_URL,$url);
            // for local server
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            // close
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            //  curl_setopt($ch,CURLOPT_HEADER, false); 
            $output=curl_exec($ch); 
            curl_close($ch);
            return $output;
        }
         /**
		 Get Employee listing 
		 */ 
		 public function getEmployeeListing($whereConditions){
		 	$this->DB_ReadOnly->where($whereConditions);
            return $this->DB_ReadOnly->get(MILL_EMPLOYEE_LISTING);
		 }
         /**
           This function for get mill sdk config details by salon id
         */
		 public function getAllSalons($salon_id){
		 	if($salon_id!='')
			{
				$this->DB_ReadOnly->where('salon_id',$salon_id);
			}
			$this->DB_ReadOnly->select('salon_id,salon_name,salon_start_day_of_week,millennium_enabled,service_retail_reports_enabled,team_commission,salon_account_id');
			$configDetails = $this->DB_ReadOnly->get(MILL_ALL_SDK_CONFIG_DETAILS)->result_array();
			$getdbdata = array();
			foreach ($configDetails as $key => $value) {
				$dbresult['salon_id'] = $value['salon_id'];
			    $dbresult['salon_name'] = $value['salon_name'];
			    $dbresult['salon_start_day_of_week'] = $value['salon_start_day_of_week'];
			    $dbresult['millennium_enabled'] = $value['millennium_enabled'];
			    $dbresult['service_retail_reports_enabled'] = $value['service_retail_reports_enabled'];
			    $dbresult['team_commission'] = $value['team_commission'];
		    	$dbresult['salon_account_id'] = $value['salon_account_id'];
			    array_push($getdbdata,$dbresult);
			}
			$results['mill_salons'] = $getdbdata;
			return $results;
			/*pa($results,'',false);
            if(!empty($configDetails))
			{
				$salonIdsArr = array();
				foreach($configDetails as $salonIds)
				{
					$salonIdsArr[] = $salonIds["salon_id"];
				}

				$salonIdsInServer = implode(",",$salonIdsArr);

				$postSalonIds = array();
				$postSalonIds["all_salon_ids"] = $salonIdsInServer;
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/getMillSalonsInfo");
				// for local server
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	            // close
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postSalonIds); 
				$salons_result=curl_exec($ch);
				curl_close($ch);
				$getAllSalons = json_decode($salons_result,true);

			}
			else
			{
				$getAllSalons = array();
			}*/
			
        } 
        /**
           This function for get mill sdk config details by salon id
         */
		 public function getSalonInfoBy($salon_id){
		 	
            $this->DB_ReadOnly->where('salon_id',$salon_id);
			$configDetails = $this->DB_ReadOnly->get(MILL_ALL_SDK_CONFIG_DETAILS)->row_array();
			$data['salon_id'] = $configDetails['salon_id'];
			$data['salon_name'] = $configDetails['salon_name'];
			$data['millennium_enabled'] = $configDetails['millennium_enabled'];
			$data['salon_start_day_of_week'] = $configDetails['salon_start_day_of_week'];
			$data['service_retail_reports_enabled'] = $configDetails['service_retail_reports_enabled'];
			$data['salon_code'] = $configDetails['salon_account_id'];
			$data['team_commission'] = $configDetails['team_commission'];
			$data['salon_account_id'] = $configDetails['salon_account_id'];
			$results['salon_info'] = $data;
			return $results;
			/*pa($results);
		 	$tempSalonArr = array();
			$tempSalonArr["salon_id"] = $salon_id;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,"https://saloncloudsplus.com/wsInfotoIntServer/getSalonInfoFromSalonId");
			// for local server
		    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	        // close
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $tempSalonArr); 
			$salonResult=curl_exec($ch);
			$result = json_decode($salonResult,true);
			pa($result,'',true);	
			return json_decode($salonResult,true);		*/	
        } 

         /**
         This function for get mill sdk config details 
         */
		 public function getMillSdkConfigDetailsBy($salon_id){
		 	$this->DB_ReadOnly->where('salon_id', $salon_id);
			return $this->DB_ReadOnly->get(MILL_ALL_SDK_CONFIG_DETAILS);
		 }
         /**
        Returns number format 
        */
        public function appCloudNumberFormat($value,$decimal,$pointertype="."){
           return number_format($value, $decimal, $pointertype, '');
          // ex:number_format($getTotalServiceSales['nprice'], 2, '.', ''); 
        }
        /**
        Return round function
        */
        public function appCludRoundCalc($val1,$val2,$total=100,$diff=2){
          
          return round(($val1/$val2)*$total,$diff);
           //ex:round(($getServiceInvoiceCountWithPrebookTrue["invoice_count"]/$getServiceInvoicesClientIdsCount["invoice_count"])*100, 2);
        }

        /**
        Return round function
        */
        public function appCludRoundCalcWithOutMultiplication($val1,$val2,$diff){
           return round(($val1/$val2),$diff);
          // ex: round($getTotalProductSales["nprice"]/$getProductInvoicesClientIdsCount["invoice_count"], 2);
        }
        /**
        Get Staff Image
       */
		public function getStaffImage($whereConditions){
		 $this->DB_ReadOnly->select('image');
		 return $this->DB_ReadOnly->get_where(STAFF2_TABLE,$whereConditions);
		}
        /**
        Insert Data Import Logs 
       */
        public function saveMillCronLogs($data)
        {
    	   $id = $data['id']; 
       	   if($id == 0) {
		    //Insert
		    unset($data['id']);
		    $this->db->insert(MILL_RUNNING_CRON_LOG_REPORT, $data);
		    return $this->db->insert_id();
		   } else {
		    //Update
		    $updateArray = array('EndingTime' => date('Y-m-d H:i:s'));
		    $this->db->where("id",$id);
		    $this->db->update(MILL_RUNNING_CRON_LOG_REPORT, $updateArray);
		    return $id;
		   }
		  }

			 /**
	    * 
	    * @param int $salon_id
	    * @return Mulit Dimensional Array
	    */
	    public function getAllStaffMembersBySalon($salon_id)
	    {
	       /*$tempSalonArr = array();
	       $tempSalonArr["salon_id"] = $salon_id;
	       return $this->getCurlData(GETALLSTAFFMEMBERS_URL,$tempSalonArr);*/
	       if(!empty($salon_id))
			{
				//$salon_id = $_POST["salon_id"];
				$this->db->select('staff_id,emp_iid');
				$staffWhere = array('salon_id' => $salon_id, 'emp_iid != ' => 0, 'status' => 'Active');
				//$staffWhere = array('salon_id' => $salon_id, 'emp_iid != ' => 0,'staff_id' => 3293);
				$getAllStaff = $this->db->get_where(STAFF2_TABLE,$staffWhere)->result_array();
				//print_r($getAllStaff);exit;
				if(!empty($getAllStaff))
				{
					$dataArray["getAllStaff"] = $getAllStaff;
					$dataArray["status"] = true;
				}
				else
				{
					$dataArray["getAllStaff"] = array();
					$dataArray["status"] = false;
				}
			}
			else
			{
				$dataArray["getAllStaff"] = array();
				$dataArray["status"] = false;
			}
			return $dataArray;
	    }

	    /**
	    * @Description $tempSalonArr should have salon id and staff id
	    * @param Array $tempSalonArr
	    * @return Mulit Dimensional Array
	    */
	    public function getMillAppointmentAndSalonInfo($tempSalonArr)
	    {
	       //return $this->getCurlData(MILLAPPOINTMENTANDSALONINFOFORCRON_URL,$tempSalonArr);
	    	if(isset($tempSalonArr["salon_id"]) && !empty($tempSalonArr["salon_id"]) && isset($tempSalonArr["staff_id"]) && !empty($tempSalonArr["staff_id"])){

	    		$salon_id = $tempSalonArr["salon_id"];
		    	$staff_id = $tempSalonArr["staff_id"];

		    	//TO GET STAFF DETAILS AND THERE GOALS DATA
				$this->db->select('name,emp_iid,skill_set,dept_skill_set,prebook,color,productivity,avg_service_ticket,avg_rpct,RPST_goal,push_token,device_type');
				$staff2Where = array('staff_id' => $staff_id, 'salon_id' => $salon_id, 'emp_iid != ' => 0, 'status' => 'Active');
				$getStaff = $this->db->get_where(STAFF2_TABLE,$staff2Where)->row_array();
				//print_r($getStaff);exit;

				if(!empty($getStaff) && !empty($getStaff["emp_iid"]))
				{
					$staff_iid = $getStaff["emp_iid"];
				}
				else
				{
					$staff_iid = 0;
				}

				$dataArray["staff_iid"] = $staff_iid;

				//GOAL OR SKILL SET INFO
				if(!empty($getStaff["skill_set"]))
				{
					$dataArray["skill_set"] = $getStaff["skill_set"];
				}
				else
				{
					$dataArray["skill_set"] = "";
				}

				if(!empty($getStaff["dept_skill_set"]))
				{
					$dataArray["dept_skill_set"] = $getStaff["dept_skill_set"];
				}
				else
				{
					$dataArray["dept_skill_set"] = "";
				}

				if(!empty($getStaff["prebook"]))
				{
					$dataArray["skill_set_prebook"] = $getStaff["prebook"];
				}
				else
				{
					$dataArray["skill_set_prebook"] = "";
				}

				if(!empty($getStaff["color"]))
				{
					$dataArray["skill_set_color"] = $getStaff["color"];
				}
				else
				{
					$dataArray["skill_set_color"] = "";
				}

				if(!empty($getStaff["productivity"]))
				{
					$dataArray["skill_set_productivity"] = $getStaff["productivity"];
				}
				else
				{
					$dataArray["skill_set_productivity"] = "";
				}

				if(!empty($getStaff["avg_service_ticket"]))
				{
					$dataArray["skill_set_avg_service_ticket"] = $getStaff["avg_service_ticket"];
				}
				else
				{
					$dataArray["skill_set_avg_service_ticket"] = "";
				}
				
				if(!empty($getStaff["avg_rpct"]))
				{
					$dataArray["skill_set_avg_rpct"] = $getStaff["avg_rpct"];
				}
				else
				{
					$dataArray["skill_set_avg_rpct"] = "";
				}

				if(!empty($getStaff["RPST_goal"]))
				{
					$dataArray["RPST_goal"] = $getStaff["avg_rpct"];
				}
				else
				{
					$dataArray["RPST_goal"] = "";
				}

				if(!empty($getStaff["ruct"]))
				{
					$dataArray["skill_set_ruct"] = $getStaff["ruct"];
				}
				else
				{
					$dataArray["skill_set_ruct"] = "";
				}

				if(!empty($getStaff["clients_serviced"]))
				{
					$dataArray["clients_serviced"] = $getStaff["clients_serviced"];
				}
				else
				{
					$dataArray["clients_serviced"] = "";
				}

				if(!empty($getStaff["percentage_buying_retail_goal"]))
				{
					$dataArray["buying_retail_percentage_goal"] = $getStaff["percentage_buying_retail_goal"];
				}
				else
				{
					$dataArray["buying_retail_percentage_goal"] = "";
				}

				if(!empty($getStaff["rebook_goal"]))
				{
					$dataArray["skill_set_rebook"] = $getStaff["rebook_goal"];
				}
				else
				{
					$dataArray["skill_set_rebook"] = "";
				}

				if(!empty($getStaff["percentage_booked_goal"]))
				{
					$dataArray["percentage_booked_goal"] = $getStaff["percentage_booked_goal"];
				}
				else
				{
					$dataArray["percentage_booked_goal"] = "";
				}
				return $dataArray;

		    } else {
		    	return array();
		    }
	    }
	    
	    /**
	     * 
	     * @param type $whereConditions
	     * @return type
	     */
	    public function getServiceInvoiceAndClientIds($whereConditions){
	        $this->DB_ReadOnly->select('count(DISTINCT cinvoiceno) as invoice_count');
	        $this->DB_ReadOnly->select('count(DISTINCT iclientid) as unique_client_count');
	        return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
	    }
    
	    /**
	     * 
	     * @param type $whereConditions
	     * @param type $likeConditions
	     * @return type
	     */
	    public function getStaffTotalColorServiceSales($whereConditions,$likeConditions=""){
	        if($likeConditions!=''){
	         $this->DB_ReadOnly->where($likeConditions);
	        }
	        $this->DB_ReadOnly->select('SUM(nprice * nquantity) as nprice');
	        return $this->DB_ReadOnly->get_where(MILL_SERVICE_SALES,$whereConditions);
	    }
	    /**
	     * Get authentication table data
	     */
	    public function getAuthData(){
	        $this->DB_ReadOnly->select('auth_id,session_reset_at');
	        return $this->DB_ReadOnly->get('salon_secure_authentication');
	    }
	    /**
	    *Update authentication table data
	    */
	    public function updateAuthData($updateArray,$whereConditions){
            $this->db->where('auth_id',$whereConditions['auth_id']);
		    $this->db->update('salon_secure_authentication', $updateArray);
	    }
	    /**
	    Get Missed Crons data
	    */
	    public function getMissedCrons($account_no){
          if($account_no!=''){
          	$this->DB_ReadOnly->where('AccountNo',$account_no);
          }
         $todaydate = date('Y-m-d');
         $emptydate = '0000-00-00 00:00:00';
         return $this->DB_ReadOnly->query("SELECT CronUrl FROM 
                    ".MILL_RUNNING_CRON_LOG_REPORT." WHERE whichCron in ('getProductSales','GetServiceSales','GetGiftCertificatesSales') and  
                    date(StartingTime) = '".$todaydate."'  and
                    EndingTime = '".$emptydate."'")->result_array();
	    }

	    public function emptyCronLog(){
	    	$this->db->query("DELETE FROM ".MILL_RUNNING_CRON_LOG_REPORT." WHERE RunningDate < NOW() - INTERVAL 1 DAY");
	    }
	    public function getServiceSalesForGraphs($data
	    	){
	    	return $this->DB_ReadOnly->query("SELECT cinvoiceno,cservicedescription,nquantity,nprice,tdatetime,Name FROM ".MILL_SERVICE_SALES." service
                    join  mill_clients client on service.iclientid=client.ClientId 
                    WHERE 
                    service.account_no = ".$data['account_no']." and 
                    service.iempid = ".$data['iempid']." and 
                    client.AccountNo = ".$data['account_no']." and 
                    service.tdatetime >= '".$data['startDate']."' and 
                    service.tdatetime <= '".$data['endDate']."' order by tdatetime");
	        }

	    public function getProductSalesForGraphs($data
	    	){
	    	//$this->db->order_by("tdatetime","asc");
	    	return $this->DB_ReadOnly->query("SELECT cinvoiceno,cproductdescription,nquantity,nprice,tdatetime,Name FROM ".MILL_PRODUCT_SALES." retail
                    join  mill_clients client on retail.iclientid=client.ClientId 
                    WHERE 
                    retail.account_no = ".$data['account_no']." and 
                    retail.iempid = ".$data['iempid']." and 
                    client.AccountNo = ".$data['account_no']." and 
                    retail.tdatetime >= '".$data['startDate']."' and 
                    retail.tdatetime <= '".$data['endDate']."' order by tdatetime ");
	        }

	        /**
	        *Function for get service types
	        **/
	        public function getAllServiceTypes(){
	          $this->DB_ReadOnly->where('status',0);
	          return $this->DB_ReadOnly->get('service_types')->result_array();	
	        }

	        public function getServiceTypeBy($id){
	        	$this->DB_ReadOnly->where_in('id',$id);
	        	return $this->DB_ReadOnly->get("service_types")->row_array();
	        }

	        public function getAllRpcts(){
	            $this->DB_ReadOnly->where('status',0);
	            return $this->DB_ReadOnly->get('rpct_types')->result_array();	
	        }
	        public function getRpctTypeBy($id){
	        	$this->DB_ReadOnly->where('id',$id);
	            return $this->DB_ReadOnly->get('rpct_types')->row_array();	
	        }

	        public function getAllLeaderBoardTypes(){
	        	$this->DB_ReadOnly->where('status',0);
	            return $this->DB_ReadOnly->get('leaderboard_types')->result_array();
	        }

	        public function getLeaderBoardTypeBy($id){
	        	$this->DB_ReadOnly->where_in('id',$id);
	        	return $this->DB_ReadOnly->get("leaderboard_types")->row_array();
	        }

	        // staff side
	        public function getAllStaffServiceTypes(){
	          $this->DB_ReadOnly->where('status',0);
	          return $this->DB_ReadOnly->get('staff_service_types')->result_array();	
	        }

	        public function getStaffServiceTypeBy($id){
	        	$this->DB_ReadOnly->where_in('id',$id);
	        	return $this->DB_ReadOnly->get("staff_service_types")->row_array();
	        }

	        public function getAllStaffLeaderBoardTypes(){
	        	$this->DB_ReadOnly->where('status',0);
	            return $this->DB_ReadOnly->get('service_leaderboard_types')->result_array();
	        }

	        public function getStaffLeaderBoardTypeBy($id){
	        	$this->DB_ReadOnly->where_in('id',$id);
	        	return $this->DB_ReadOnly->get("service_leaderboard_types")->row_array();
	        }
	         /**
	        This function for get mill sdk config details all salons
	        */
			public function getMillSdkConfigDetailsByGuidAllSalons($mill_guid){
			 	$url = 'http://webappcloudsplus.com/getAllMillSalons/getSalonDetailsByGuid';
			    $data['guid'] = $mill_guid;
			    $ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL,$url);
			      // for local server
			    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			            // close
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			    curl_setopt($ch, CURLOPT_POST, 1);
			    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
			    $salonResult=curl_exec($ch);
			    $allresults =  json_decode($salonResult,true);
			    return $allresults;
			}

			public function insertCheckSDKData($data){
				return $this->db->insert('mill_all_salons_sdk_reports', $data);
			}

			public function updateCheckSDKData($id,$salon_id,$data){
				$this->db->where('id',$id);
				$this->db->where('salon_id',$salon_id);
				return $this->db->update('mill_all_salons_sdk_reports', $data);
			}

			public function getMillSdkReportsData(){
                $this->db->order_by('created_date','desc');
				return $this->DB_ReadOnly->get('mill_all_salons_sdk_reports')->result_array();
			}
			
			/**
			*Function for get url web app signature for without login show sdk reports and config details
			*/

			public function getUrlAuthWebAppSignature($server_url,$service_url,$timestamp){
				$host = $server_url;
		        $private_key = 'SGuD4awN1VTMSxRXrUZDpEEol1oBXywj';
		        $uri = $service_url;
			    $params['TimeStamp'] = $timestamp;
		        $method = 'POST';
		        $canonicalized_query = array();
		        foreach ($params as $param=>$value){
			        $param = str_replace('%7E', '~', rawurlencode($param));
			        $value = str_replace('%7E', '~', rawurlencode($value));
			        $canonicalized_query[] = $param.'='.$value;
		        }
		        $canonicalized_query = implode('&', $canonicalized_query);
		        $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
		        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $private_key, TRUE));
		        $signature = str_replace('%7E', '~', rawurlencode($signature));
		        return $signature;
			}

			/**
			* Check admin is logged in from salon cloud plus server and time stamp
			*/ 
		    public function checkAuthUser($signature,$timestamp,$server_url,$service_url){
	            // timestamp verify            
	            $microtime = microtime();
	            $comps = explode(' ', $microtime); 
	            $militime = sprintf('%d%03d', $comps[1], $comps[0] * 1000);
	            $REQUEST_LIFE_TIME = 900000;//in miliseconds (15 mins)
	            $militime - $timestamp;
	            if($REQUEST_LIFE_TIME >= ($militime - $timestamp)){
	                 // check signature
	            	//print $signature;
	            	//print "<br/>";
	            	$getsignature = $this->getUrlAuthWebAppSignature($server_url,$service_url,$timestamp);
	            	if($signature==$getsignature){
	                	return 1;
	                } else{
	                	return 0;
	                }
	            }else{
	            	return 0;
	            }
		    }

       

 }
