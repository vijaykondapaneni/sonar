<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class CheckSdkVersion extends CI_Controller {
		
		function __construct() {
			parent::__construct();
			$this->load->library('session');
			//$this->load->library('form_validation');
			//$this->load->library('pagination');
			$this->load->helper(array('form', 'url'));
			$this->load->database();
		}
		//salon_update_date

		function getServiceSdkVersion()
		{
			
			error_reporting(E_ALL);
			ini_set('display_errors', 0);
			ini_set('memory_limit',-1);
	   		ini_set('max_execution_time',0);
	   		ini_set("default_socket_timeout",5);
	   		$soapClientVersion = 'SOAP_1_1';
	   		$getConfigData = $this->db->query("SELECT salon_name,salon_id,salon_account_id,mill_username,mill_password,mill_guid,mill_url FROM mill_all_sdk_config_details");
			$configDataArr = $getConfigData->result_array();
			/*echo "<pre>";
			print_r($configDataArr);
			die();*/
			
			echo "<table border=1>
					<th>Salon Id</td>
					<th>Salon Name</th>
					<th>Salon Acc.no</th>
					<th>SDK Version</th>
					<th>Error</th>
			";
			
			$msg = '';
			$mystring = '';
			foreach ($configDataArr as $key => $configData) {
				$salon_name	=	$configData['salon_name'];
				$salon_id	=	$configData['salon_id'];
				$salon_account_id	=	$configData['salon_account_id'];
				$MillenniumGuid	=	$configData['mill_guid'];
				$musername	=	$configData['mill_username'];
				$mpassword	=	$configData['mill_password'];
				$url = $configData['mill_url'];
				
	    		try{
					//$client = new SoapClient($url.'?WSDL', array('trace' => 1, 'soap_version' => $soapClientVersion, 'exceptions'=> 1));
					$client = new SoapClient($url.'?WSDL', array('trace' => 1, 'soap_version' => $soapClientVersion));
				} catch (Exception $e) {
					$msg = $e->getMessage()."<br>";
					echo "<tr>";
					echo "<td>".$salon_id."</td>";
					echo "<td>".$salon_name."</td>";
					echo "<td>".$salon_account_id."</td>";
					echo "<td></td>";
					echo "<td>".$msg."</td>";
				    echo "</tr>";
					continue;
				}
				try{	
				   	//$client = new SoapClient('http://76.124.213.113:9000/webapp/MillenniumSDK.asmx?WSDL');
					$ns = 'http://www.harms-software.com/Millennium.SDK'; //Namespace of the WS.
					$auth = new stdClass();
					$auth->MillenniumGuid = $MillenniumGuid;
			
					//$auth->SessionId = $MillenniumGuid;
			
					$header = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
					 $client->__setSoapHeaders($header);
					//var_dump($client);exit;
					//$outputheader = new SoapHeader($ns, 'SessionInfo', '', false);
					 $outputheader = '';
					$param = array('User' => $musername,'Password' => $mpassword);
				
					$result = $client->__soapCall('Logon', array($param), NULL,NULL,$outputheader);
					
					$logonStatus = $result->LogonResult;
				    $sessId = $outputheader['SessionInfo']->SessionId;
				} catch (Exception $e) {
					//echo 'Caught exception1: ',  $e->getMessage(), "\n";
					$msg = $e->getMessage()."<br>";
					echo "<tr>";
					echo "<td>".$salon_id."</td>";
					echo "<td>".$salon_name."</td>";
					echo "<td>".$salon_account_id."</td>";
					echo "<td></td>";
					echo "<td>".$msg."</td>";
				    echo "</tr>";
					continue;
				}
				
				if(!empty($sessId)){
					$sess = new stdClass();
					$sess->SessionId = $sessId;
				}
		
				//exit;
				
				if(!empty($logonStatus) && !empty($sessId))
				{
					$headers = array();
					$headers[] = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
					$headers[] = new SoapHeader($ns, 'SessionInfo', $sess, false);
					$client->__setSoapHeaders($headers);
					$outputheader2 = '';
					//$param2 = array('AppointmentId' => 373460);
					//echo $row['ApptId'];exit;
					try{
			              
		              $result2 = $client->__soapCall('GetSDKVersion', array(), NULL,$headers,$outputheader2);
		              
		            }
		            catch (Exception $e) {
		              $msg = 'Caught exception2: '.$e->getMessage();
		              echo "<tr>";
    					echo "<td>".$salon_id."</td>";
    					echo "<td>".$salon_name."</td>";
    					echo "<td>".$salon_account_id."</td>";
    					echo "<td></td>";
    					echo "<td>".$msg."</td>";
    				    echo "</tr>";
    					continue;

		            }
		            
		            //print_r($result2);exit;
		            //echo $result2->GetSDKVersionResult;exit;
		            
		            if(!empty($result2->GetSDKVersionResult)){
		                $mystring = $result2->GetSDKVersionResult;
		            } else {
		                $mystring='---';
		            }
				}
				echo "<tr>";
					echo "<td>".$salon_id."</td>";
					echo "<td>".$salon_name."</td>";
					echo "<td>".$salon_account_id."</td>";
					echo "<td>".$mystring."</td>";
					echo "<td></td>";
				echo "</tr>";
			}
			echo "</table>";
		}
    }
		
