<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class MillFormulas extends CI_Controller {

  function __construct() {

    parent::__construct();
    $this->load->library('session');
    $this->load->library('form_validation');
    $this->load->library('pagination');
    $this->load->helper(array('form', 'url'));
    $this->load->database();

  }

  public function index()

  {
    
  }

  function GetFormulaTypes($salon_account_id=""){
//echo $salon_account_id;exit;
    if(isset($salon_account_id) && $salon_account_id!=""){
      $this->db->where_in('salon_account_id', $salon_account_id);
    }

    $getConfigDetails = $this->db->get('mill_all_sdk_config_details');
//print_r($getConfigDetails->result_array());exit;
    if(!empty($getConfigDetails->result_array()))
    {

      foreach($getConfigDetails->result_array() as $configDetails)
      {

        echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
        echo "<br>";

        $account_no = $configDetails['salon_account_id'];
        $salon_id = $configDetails['salon_id'];
        $path_to_pem = base_url()."salonevolve.pem";
        $siteIp = $configDetails['mill_ip_address'];
        $wsd = $configDetails['mill_url']."?WSDL";
        $ns = 'http://www.harms-software.com/Millennium.SDK';
        $user = $configDetails['mill_username'];
        $password = $configDetails['mill_password'];
        $MillenniumGuid = $configDetails['mill_guid'];

        $client = new SoapClient($wsd);
        $auth = new stdClass();
        $auth->MillenniumGuid = $MillenniumGuid;
        $header = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
        $client->__setSoapHeaders($header);
        $Logon = '';

        $FormulaTypes = array();

        $param = array('User' => $user, 'Password' => $password);

        try 
        {
          $result = $client->__soapCall('Logon', array($param), NULL,NULL,$Logon);
        } 
        catch (Exception $e) 
        {
          $FormulaTypes['status']=0;
          $FormulaTypes['message']="An error occurred. Please try again ...";                      
        }

        $sessId = $Logon['SessionInfo']->SessionId;
            
        if(!empty($sessId)){
          
          $sess = new stdClass();
          $sess->SessionId = $sessId;
    
          $headers = array();
          $headers[] = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
          $headers[] = new SoapHeader($ns, 'SessionInfo', $sess, false);
          $client->__setSoapHeaders($headers);

          $result = '';
          $param = array('IncludeDeleted' => false);

          try
          {  
            $result = $client->__soapCall('GetFormulaTypes', array($param), NULL, $headers,$result);
            $result = simplexml_load_string(utf8_encode($result->GetFormulaTypesResult),null);

            //echo "<pre>"; print_r($result);echo "</pre>";exit;
            $i=0;

            if(count($result)>0){

              foreach ($result as $key => $row) {
              
                $iid =   (int)trim(($row->iid));
                $ilocationid = (int)trim(($row->ilocationid));
                $cdescr = (string)trim(($row->cdescr));
                $ddatedel = (string)trim((str_replace("T", " ", $row->ddatedel)));
                $igid = (int)trim(($row->igid));
                $tlastmodified = (string)(str_replace("T", " ", trim($row->tlastmodified)));

                $checkquery = $this->db->get_where('plus_mill_formula_types', array('iid' => (int)($row->iid),'salon_id' => $salon_id,'account_no' => $account_no));  
                
                if($checkquery->num_rows()>0)
                {
                  
                  $row = $checkquery->row_array();  

                  if(
                    $row['iid'] == $iid &&                     
                    $row['salon_id'] == $salon_id && 
                    $row['account_no'] == $account_no  && 
                    $row['ilocationid'] == $ilocationid && 
                    $row['cdescr'] == $cdescr && 
                    $row['ddatedel'] == $ddatedel && 
                    $row['igid'] == $igid && 
                    $row['tlastmodified'] == $tlastmodified
                    )
                  {
                    continue; 
                  } 

                  else
                  {

                    $data = array(
                      'iid' => $iid,
                      'ilocationid' => $ilocationid,
                      'cdescr' =>  $cdescr,
                      'ddatedel' => $ddatedel,
                      'igid' =>  $igid,
                      'tlastmodified' =>  $tlastmodified,
                      'updated_date' => date("Y-m-d H:i:s")
                    );

                    $this->db->where('iid',$iid);
                    $this->db->where('account_no',$account_no);
                    $this->db->where('salon_id',$salon_id);
                    $res = $this->db->update('plus_mill_formula_types', $data);
                  }

                }            

                else                 
                {

                  $data = array(
                    'account_no' => trim($account_no),
                    'salon_id' => trim($salon_id),
                    'iid' => $iid,
                    'ilocationid' => $ilocationid,
                    'cdescr' =>  $cdescr,
                    'ddatedel' => $ddatedel,
                    'igid' =>  $igid,
                    'tlastmodified' =>  $tlastmodified,
                    'inserted_date' => date("Y-m-d H:i:s"));

                  $res = $this->db->insert('plus_mill_formula_types', $data);
                  $id = $this->db->insert_id();
                  //echo $this->db->last_query();

                }  
              }

              $FormulaTypes['status']=1;
              $FormulaTypes['message']="success"; 
            }
          }   
                   
          catch(Exception $e) {

            $FormulaTypes['status']=0;
            $FormulaTypes['message']="An error occurred. Please try again ...";  

          } 

          echo $FormulaTypes= json_encode($FormulaTypes); 

        }

      }  

    }   

  }  

  function GetClientFormulas($salon_account_id="",$limit=0,$limitStartFromCount=0){
    ini_set('max_execution_time', 300);
    ini_set("memory_limit","512M");
    //ini_set('memory_limit', '1024M');
    if(isset($salon_account_id) && $salon_account_id!=""){
      $this->db->where_in('salon_account_id', $salon_account_id);
    }

    $getConfigDetails = $this->db->get('mill_all_sdk_config_details')->result_array();

    //if($getConfigDetails->num_rows()>0 && $salon_account_id!="")
    if(!empty($getConfigDetails))
    {

      foreach($getConfigDetails as $configDetails)
      {

        echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";
       

        $account_no = $configDetails['salon_account_id'];
        $salon_id = $configDetails['salon_id'];
        $path_to_pem = base_url()."salonevolve.pem";
        $siteIp = $configDetails['mill_ip_address'];
        $wsd = $configDetails['mill_url']."?WSDL";
        $ns = 'http://www.harms-software.com/Millennium.SDK';
        $user = $configDetails['mill_username'];
        $password = $configDetails['mill_password'];
        $MillenniumGuid = $configDetails['mill_guid'];
        
        $this->db->select('ClientId'); 
        $this->db->where('AccountNo', $salon_account_id);
        //$this->db->limit(1);
        $this->db->limit($limit,$limitStartFromCount);
        $getClientDetails = $this->db->get(MILL_CLIENTS_TABLE)->result_array();
        
        /*if(!empty($getClientDetails))
        { 
            $xmlIDS = '';
            foreach($getClientDetails as $ClientDetail)          
            {
                $clientid = $ClientDetail['ClientId'];

            }
        }*/

        if(!empty($getClientDetails))
          {
            $xmlIDS = '';
            foreach($getClientDetails as $todayClients)
            {
              //$allClientCount[] = $todayClients["ClientId"];
                if(isset($todayClients["ClientId"]) && !empty($todayClients["ClientId"])){
                    $clientid = $todayClients["ClientId"];
                } else {
                    $clientid = "";
                }
                $xmlIDS .= '<Ids><Id>'.(int)$clientid.'</Id></Ids>';
            }
          }
          else
          {
            $xmlIDS = '';
            echo "No Clients FOund in database."."<br>";exit;
          }
         
        
        if($xmlIDS!=""){

          $client = new SoapClient($wsd, array("trace" => 1, "exceptions" => 1));
          $auth = new stdClass();
          $auth->MillenniumGuid = $MillenniumGuid;
          $header = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
          $client->__setSoapHeaders($header);
          $Logon = '';

          $FormulaTypes = array();

          $param = array('User' => $user, 'Password' => $password);

          try 
          {
            $result = $client->__soapCall('Logon', array($param), NULL,NULL,$Logon);
          } 
          catch (Exception $e) 
          {
            $FormulaTypes['status']=0;
            $FormulaTypes['message']="An error occurred. Please try again ...";                      
          }

          $sessId = $Logon['SessionInfo']->SessionId;
            
          if(!empty($sessId)){
          
          $sess = new stdClass();
          $sess->SessionId = $sessId;
    
          $headers = array();
          $headers[] = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
          $headers[] = new SoapHeader($ns, 'SessionInfo', $sess, false);
          $client->__setSoapHeaders($headers);

          $result = '';
          $param['XmlIds'] = '<NewDataSet>'.$xmlIDS.'</NewDataSet>';

          try
          { 
            $result = $client->__soapCall('GetClientFormulas', array($param), NULL, $headers,$result);
            $result = simplexml_load_string(utf8_encode($result->GetClientFormulasResult),null);

            //echo "<pre>"; print_r($result);echo "</pre>";  exit;                          
            $i=0;

            if(!empty($result)){

              foreach ($result as $key => $row) {
              
                $iclientid =   (int)trim(($row->iclientid));
                $dformdate = (string)trim((str_replace("T", " ", $row->dformdate)));
                $iformtype = (int)trim(($row->iformtype));
                $cemployee = (string)trim(($row->cemployee));
                $iformtypegid = (int)trim(($row->iformtypegid));
                $cformulatype = (string)trim(($row->cformulatype));
                $iformulaid = (int)trim(($row->iformulaid));
                $mformula = (string)trim(($row->mformula));               
                
               // $checkquery = $this->db->get_where('plus_mill_clients_formulas', array('client_id' =>$iclientid,'salon_id' => $salon_id,'account_no' => $account_no,'iformtype' => $iformtype,'iformulaid' => $iformulaid));
                
                $checkquery = $this->db->select('client_id, dformdate, iformtype,cemployee, iformtypegid, cformulatype,iformulaid,mformula')->get_where('plus_mill_clients_formulas', array('client_id' => $iclientid,'iformulaid' => $row->iformulaid,'salon_id' => $salon_id,'account_no' => $account_no)); 

                //'cemployee' => $cemployee,'iformtypegid' => $iformtypegid,'cformulatype' => $cformulatype,'mformula' => $mformula

                if(!empty($checkquery->row_array()))
                {
                  
                  $row = $checkquery->row_array();  

                  if(
                    $row['iformtype'] == $iformtype && 
                    $row['cemployee'] == $cemployee && 
                    $row['iformtypegid'] == $iformtypegid && 
                    $row['cformulatype'] == $cformulatype && 
                    $row['dformdate'] == $dformdate &&
                    $row['mformula'] == $mformula 
                    )
                  {
                    continue; 
                  } 

                  else
                  {
                    echo "<br/>UPDATING: salon_id:".$salon_id." - client_id:".$iclientid." - iformulaid:".$iformulaid."  - account_no:".$account_no." <br>";  
                    $data = array(
                      'iformtype' => $iformtype,
                      'iformulaid' => $iformulaid,
                      'cemployee' =>  $cemployee,
                      'iformtypegid' => $iformtypegid,
                      'cformulatype' =>  $cformulatype,
                      'mformula' =>  $mformula,
                      'dformdate' =>  $dformdate,
                      'updated_date' => date("Y-m-d H:i:s")
                    );

                    $this->db->where('client_id',$iclientid);
                    $this->db->where('iformulaid',$iformulaid);
                    $this->db->where('iformtype',$iformtype);
                    $this->db->where('account_no',$account_no);
                    $this->db->where('salon_id',$salon_id);
                    $res = $this->db->update('plus_mill_clients_formulas', $data);
                  }

                }            

                else                 
                {
                  echo "<br/>INSERTING: salon_id:".$salon_id." - client_id:".$iclientid." <br>"; 
                  $data = array(
                    'client_id' => trim($iclientid),
                    'account_no' => trim($account_no),
                    'salon_id' => trim($salon_id),
                    'iformtype' => $iformtype,
                    'iformulaid' => $iformulaid,
                    'cemployee' =>  $cemployee,
                    'iformtypegid' => $iformtypegid,
                    'cformulatype' =>  $cformulatype,
                    'mformula' =>  $mformula,
                    'dformdate' =>  $dformdate,
                    'inserted_date' => date("Y-m-d H:i:s"));

                  $res = $this->db->insert('plus_mill_clients_formulas', $data);
                  $id = $this->db->insert_id();
                  //echo $this->db->last_query();

                }  
              }

              $FormulaTypes['status']=1;
              $FormulaTypes['message']="success"; 
            }
          }   
                   
          catch(Exception $e) {

            $FormulaTypes['status']=0;
            $FormulaTypes['message']="An error occurred. Please try again ...";  

          } 

          echo $FormulaTypes= json_encode($FormulaTypes); 

          }
        }
        /* if client id check end */ 
        //}
        /* for loop end */
        //}
        /* if clients check end */  
      }  

    }  
    else {

      $data['status']=0;
      $data['message']="An error occurred. Please try again ...";  
      echo $data= json_encode($data);  

    } 
  } 
  
    /**
     *METHOD: POST 
     *@param type $salon_id , $client_id
     * 
     * **/

    function WsGetClientFormulas() {
        
                    $response_check = array();
                     
                    $salon_id  = $this->input->post('salon_id');
                    $client_id = $this->input->post('client_id');
            
                    //$response_check['data'] = $salon_id . ' ' . $client_id; //to check import json

                    if(empty($salon_id) || empty($client_id)){ 
                        $response_check['message']  = "Not valid either salon_id OR client_id.";
                        $response_check['status']  =  false;
                        $response_check['data'] = array();
                        echo json_encode($response_check); return false;
                    }
                    
                    $whereCondition  = array('salon_id' => $salon_id , 'client_id' => $client_id);
                    //Checking the record whether it present or not.
                    $this->db->where($whereCondition);
                    $formulas_data = $this->db->get('plus_mill_clients_formulas');
                     
                        if($formulas_data->num_rows() > 0) {
                            foreach($formulas_data->result_array() as $formulas) {
                                $response_check['data'][] = $formulas;
                            }
                            $response_check['status'] = true;
                            $response_check['message'] = "Formula list found.";
                        } else {
                            $response_check['data'] = array();
                            $response_check['status'] = false;
                            $response_check['message'] = "No Formula list found.";
                        }
                echo json_encode($response_check);
		}


    function GetClientFormulasFromDailyAppts($salon_account_id="")
    {
      //echo "dsfsdfs";exit;
      ini_set('max_execution_time', 300);
      ini_set("memory_limit","512M");
      //ini_set('memory_limit', '1024M');
      
      $today_date = date("Y-m-d");
      //$today_date = "2017-04-05";

      if(isset($salon_account_id) && $salon_account_id!=""){
        $this->db->where_in('salon_account_id', $salon_account_id);
      }

      $this->db->where('putFormulaNotes',1);
      $getConfigDetails = $this->db->get('mill_all_sdk_config_details');

      if(!empty($getConfigDetails->result_array()))
      {

        foreach($getConfigDetails->result_array() as $configDetails)
        {

          echo "===> Cron Running For Salon Account Number: <b>".$configDetails['salon_account_id']."</b>";echo "<br>";
         

          $account_no = $configDetails['salon_account_id'];
          $salon_id = $configDetails['salon_id'];
          $path_to_pem = base_url()."salonevolve.pem";
          $siteIp = $configDetails['mill_ip_address'];
          $wsd = $configDetails['mill_url']."?WSDL";
          $ns = 'http://www.harms-software.com/Millennium.SDK';
          $user = $configDetails['mill_username'];
          $password = $configDetails['mill_password'];
          $MillenniumGuid = $configDetails['mill_guid'];
           
           $sql_get_clients_serviced_count = $this->db->query("SELECT DISTINCT ClientId FROM 
              ".MILL_APPTS_TABLE."  
              WHERE 
              AccountNo = '".$account_no."' and 
              SlcStatus != 'Deleted' and 
              ClientId != '-999' and 
              str_to_date(AppointmentDate, '%m/%d/%Y') = '".$today_date."'")->result_array();
              //echo $this->db->last_query();echo "<br>";exit;
          if(!empty($sql_get_clients_serviced_count))
          {
            $xmlIDS = '';
            foreach($sql_get_clients_serviced_count as $todayClients)
            {
              //$allClientCount[] = $todayClients["ClientId"];
                if(isset($todayClients["ClientId"]) && !empty($todayClients["ClientId"])){
                    $clientid = $todayClients["ClientId"];
                } else {
                    $clientid = "";
                }
                $xmlIDS .= '<Ids><Id>'.(int)$clientid.'</Id></Ids>';
            }
          }
          else
          {
            $xmlIDS = '';
            echo "No Clients FOund in database."."<br>";exit;
          }

          //print_r($allClientCount);exit;
          
          
          /*if(!empty($xmlIDS))
          {*/

          /*foreach($allClientCount as $ClientDetailId)          
          {*/
           
            //$clientid = $ClientDetailId;
            if($xmlIDS!="")
            {

              $client = new SoapClient($wsd, array("trace" => 1, "exceptions" => 1));
              $auth = new stdClass();
              $auth->MillenniumGuid = $MillenniumGuid;
              $header = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
              $client->__setSoapHeaders($header);
              $Logon = '';

              $FormulaTypes = array();

              $param = array('User' => $user, 'Password' => $password);

              try 
              {
                $result = $client->__soapCall('Logon', array($param), NULL,NULL,$Logon);
              } 
              catch (Exception $e) 
              {
                $FormulaTypes['status']=0;
                $FormulaTypes['message']="An error occurred. Please try again ...";                      
              }

              $sessId = $Logon['SessionInfo']->SessionId;
                
              if(!empty($sessId)){
              
              $sess = new stdClass();
              $sess->SessionId = $sessId;
        
              $headers = array();
              $headers[] = new SoapHeader($ns, 'MillenniumInfo', $auth, false);
              $headers[] = new SoapHeader($ns, 'SessionInfo', $sess, false);
              $client->__setSoapHeaders($headers);

              $result = '';
              //$param['XmlIds'] = '<NewDataSet><Ids><Id>'.(int)$clientid.'</Id></Ids></NewDataSet>';

              $param['XmlIds'] = '<NewDataSet>'.$xmlIDS.'</NewDataSet>';

              try
              { 
                $result = $client->__soapCall('GetClientFormulas', array($param), NULL, $headers,$result);
                $result = simplexml_load_string(utf8_encode($result->GetClientFormulasResult),null);

                //echo "<pre>"; print_r($result);echo "</pre>";  exit;                          
                $i=0;

                if(!empty($result) && count($result)>0){

                  foreach ($result as $key => $row) {
                  
                    $iclientid =   (int)trim(($row->iclientid));
                    $dformdate = (string)trim((str_replace("T", " ", $row->dformdate)));
                    $iformtype = (int)trim(($row->iformtype));
                    $cemployee = (string)trim(($row->cemployee));
                    $iformtypegid = (int)trim(($row->iformtypegid));
                    $cformulatype = (string)trim(($row->cformulatype));
                    $iformulaid = (int)trim(($row->iformulaid));
                    $mformula = (string)trim(($row->mformula));               
                    
                   // $checkquery = $this->db->get_where('plus_mill_clients_formulas', array('client_id' =>$iclientid,'salon_id' => $salon_id,'account_no' => $account_no,'iformtype' => $iformtype,'iformulaid' => $iformulaid));
                    
                    $checkquery = $this->db->select('client_id, dformdate, iformtype,cemployee, iformtypegid, cformulatype,iformulaid,mformula')->get_where('plus_mill_clients_formulas', array('client_id' => $iclientid,'iformulaid' => $row->iformulaid,'salon_id' => $salon_id,'account_no' => $account_no)); 

                    //'cemployee' => $cemployee,'iformtypegid' => $iformtypegid,'cformulatype' => $cformulatype,'mformula' => $mformula

                    if($checkquery->num_rows()>0)
                    {
                      
                      $row = $checkquery->row_array();  

                      if(
                        $row['iformtype'] == $iformtype && 
                        $row['cemployee'] == $cemployee && 
                        $row['iformtypegid'] == $iformtypegid && 
                        $row['cformulatype'] == $cformulatype && 
                        $row['dformdate'] == $dformdate &&
                        $row['mformula'] == $mformula 
                        )
                      {
                        continue; 
                      } 

                      else
                      {
                        echo "<br/>UPDATING: salon_id:".$salon_id." - client_id:".$iclientid." - iformulaid:".$iformulaid."  - account_no:".$account_no." <br>";  
                        $data = array(
                          'iformtype' => $iformtype,
                          'iformulaid' => $iformulaid,
                          'cemployee' =>  $cemployee,
                          'iformtypegid' => $iformtypegid,
                          'cformulatype' =>  $cformulatype,
                          'mformula' =>  $mformula,
                          'dformdate' =>  $dformdate,
                          'updated_date' => date("Y-m-d H:i:s")
                        );

                        $this->db->where('client_id',$iclientid);
                        $this->db->where('iformulaid',$iformulaid);
                        $this->db->where('iformtype',$iformtype);
                        $this->db->where('account_no',$account_no);
                        $this->db->where('salon_id',$salon_id);
                        $res = $this->db->update('plus_mill_clients_formulas', $data);
                      }

                    }            

                    else                 
                    {
                      echo "<br/>INSERTING: salon_id:".$salon_id." - client_id:".$iclientid." <br>"; 
                      $data = array(
                        'client_id' => trim($iclientid),
                        'account_no' => trim($account_no),
                        'salon_id' => trim($salon_id),
                        'iformtype' => $iformtype,
                        'iformulaid' => $iformulaid,
                        'cemployee' =>  $cemployee,
                        'iformtypegid' => $iformtypegid,
                        'cformulatype' =>  $cformulatype,
                        'mformula' =>  $mformula,
                        'dformdate' =>  $dformdate,
                        'inserted_date' => date("Y-m-d H:i:s"));

                      $res = $this->db->insert('plus_mill_clients_formulas', $data);
                      $id = $this->db->insert_id();
                      //echo $this->db->last_query();

                    }  
                  }

                  $FormulaTypes['status']=1;
                  $FormulaTypes['message']="success"; 
                }
              }   
                       
              catch(Exception $e) {

                $FormulaTypes['status']=0;
                $FormulaTypes['message']="An error occurred. Please try again ...";  

              } 

              echo $FormulaTypes= json_encode($FormulaTypes); 

            }
          //}
          /* if client id check end */ 
          //}
          /* for loop end */
          }
          /* if clients check end */  
        }  

      }  
      else {

        $data['status']=0;
        $data['message']="An error occurred. Please try again ...";  
        echo $data= json_encode($data);  

      } 
    } 
        

}            