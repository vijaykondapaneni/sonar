<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	
	class Wsimport_referral_types_in_sdk extends CI_Controller {
		
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

		public function GetReferralsTypes($account_no = "") {

	        if(!empty($account_no))
			{
				//$accounts = array($account_no);
				$this->db->where('salon_account_id', $account_no);

				$getConfigDetails = $this->db->get(MILL_ALL_SDK_CONFIG_DETAILS);

                if ($getConfigDetails->num_rows() > 0) {

                    $configDetails = $getConfigDetails->row_array();


                    $account_no = $configDetails['salon_account_id'];
                    $salon_id = $configDetails['salon_id'];
                    //$path_to_pem = base_url() . "salonevolve.pem";
                    $siteIp = $configDetails['mill_ip_address'];
                    $wsd = $configDetails['mill_url'] . "?wsdl";
                    $ns = 'http://www.harms-software.com/Millennium.SDK';
                    $user = $configDetails['mill_username'];
                    $password = $configDetails['mill_password'];
                    $MillenniumGuid = $configDetails['mill_guid'];
                    $soapClientVersion = 'SOAP_1_1';
                    $service = array();
                    $client = new SoapClient($wsd);

                    //$client =  new SoapClient($wsd);
                    $auth = new stdClass();
                    $auth->MillenniumGuid = $MillenniumGuid;
                    $header = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
                    $client->__setSoapHeaders($header);
                    $Logon = '';
                    $service = array();

                    $param = array('User' => $user, 'Password' => $password);

                    try {

                        $result = $client->__soapCall('Logon', array($param), NULL, NULL, $Logon);
                    } catch (Exception $e) {

                        $referrals['status'] = 0;
                        $referrals['message'] = "An error occurred. Please try again ...";
                    }

                    $sessId = $Logon['SessionInfo']->SessionId;



                    if (!empty($sessId)) {

                        $sess = new stdClass();
                        $sess->SessionId = $sessId;

                        $headers = array();
                        $headers[] = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
                        $headers[] = new SoapHeader($ns, 'SessionInfo', $sess, false);
                        $client->__setSoapHeaders($headers);

                        $result = '';

                        //$mill_categories = array();
                        ///	$db_categories = array();

                        $param = array('IncludeDeleted' => false);

                        try {

                            $result = $client->__soapCall('GetReferralTypes', array($param), NULL, $headers, $result);


                            $result = simplexml_load_string(utf8_encode($result->GetReferralTypesResult), null);
                            $i = 0;

                            foreach ($result as $row) {

                                $iid = (string) (trim($row->iid));
                                $mill_refer[] = $iid;

                                $ilocationid = (string) (trim($row->ilocationid));
                                $ccode = (string) (trim($row->ccode));
                                $cdescr = (string) (trim($row->cdescr));
                                $dcreated = str_replace('T', ' ', (trim($row->dcreated)));
                                $ddatedel = str_replace('T', ' ', (trim($row->ddatedel)));
                                $igid = (string) (trim($row->igid));
                                $linactive = (string) (trim($row->linactive));
                                $tlastmodified = str_replace('T', ' ', (trim($row->tlastmodified)));
                                $referquery = $this->db->get_where('referral_types', array('iid' => $iid, 'salon_id' => $salon_id, 'account_no' => $account_no));

                                if ($referquery->num_rows() > 0) {

                                    $referrow = $referquery->row_array();

                                    if (
                                            $referrow['iid'] == $iid && $referrow['account_no'] == $account_no && $referrow['salon_id'] == $salon_id && $referrow['ilocationid'] == $ilocationid && $referrow['ccode'] == $ccode && $referrow['cdescr'] == $cdescr && $referrow['dcreated'] == $dcreated && $referrow['ddatedel'] == $ddatedel && $referrow['igid'] == $igid && $referrow['linactive'] == $linactive && $referrow['tlastmodified'] == $tlastmodified
                                    ) {
                                        continue; //SAME DATA FOUND, SO CONTINUe with the loop
                                    } else {
                                        //UPDATE DATA IN DB 
                                        $refer_data = array(
                                            'iid' => trim((string) ($row->iid)),
                                            'ilocationid' => trim((string) ($row->ilocationid)),
                                            'ccode' => trim((string) ($row->ccode)),
                                            'cdescr' => trim((string) ($row->cdescr)),
                                            'dcreated' => str_replace('T', ' ', (trim($row->dcreated))),
                                            'ddatedel' => str_replace('T', ' ', (trim($row->ddatedel))),
                                            'igid' => trim((string) ($row->igid)),
                                            'linactive' => trim((string) ($row->linactive)),
                                            'tlastmodified' => str_replace('T', ' ', (trim($row->tlastmodified))),
                                            'updatedDate' => date("Y-m-d H:i:s")
                                        );

                                        //	echo "<pre>";print_r($refer_data);exit;
                                        $this->db->where('iid', $iid);
                                        $this->db->where('account_no', $account_no);
                                        $this->db->where('salon_id', $salon_id);
                                        $res = $this->db->update('referral_types', $refer_data);
                                    }
                                } else { // INSERT DATA IN DB 
                                    $refer_data = array(
                                        'account_no' => trim($account_no),
                                        'salon_id' => trim($salon_id),
                                        'iid' => trim($iid),
                                        'ilocationid' => trim((string) ($row->ilocationid)),
                                        'ccode' => trim((string) ($row->ccode)),
                                        'cdescr' => trim((string) ($row->cdescr)),
                                        'dcreated' => str_replace('T', ' ', (trim($row->dcreated))),
                                        'ddatedel' => str_replace('T', ' ', (trim($row->ddatedel))),
                                        'igid' => trim((string) ($row->igid)),
                                        'linactive' => trim((string) ($row->linactive)),
                                        'tlastmodified' => str_replace('T', ' ', (trim($row->tlastmodified))),
                                        'insertedDate' => date("Y-m-d H:i:s")
                                    );
                                    //echo "<pre>";print_r($refer_data);exit;
                                    $res = $this->db->insert('referral_types', $refer_data);
                                }


                                $refer['status'] = 1;
                                $refer['message'] = "success";
                                $i++;
                            }

                            $this->db->where(array('salon_id' => $salon_id, 'account_no' => $account_no));
                            $this->db->where_not_in('iid', $mill_refer);
                            $db_referquery = $this->db->delete('referral_types');
                        } catch (Exception $e) {

                            $refer['status'] = 0;
                            $refer['message'] = "An error occurred. Please try again ..." . $e;
                            echo "An error occurred. please contact Millennium Support. \n\n" . $e->getMessage();
                        }

                        //echo $service= json_encode($service); 							    
                    } else {

                        $refer['status'] = 0;
                        $refer['message'] = "Error. please try again.";
                    }
                } else {

                    $refer['status'] = 0;
                    $refer['message'] = "Missing salon configuration details.";
                }
			}
	        

	        //echo json_encode($service);
	        echo "Data updated successfully.";
	    }



	}
?>