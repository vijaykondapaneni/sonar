<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
use Restserver\Libraries\REST_Controller;

class Getcode extends REST_Controller {
  function __construct()
    {
        parent::__construct();
        $this->load->model('Common_model');
        $this->load->model('Newsalon_model');
        $this->DB_ReadOnly = $this->load->database('read_only', TRUE);
       // $this->load->library("security");
    }
  public function getencode($str){
    print salonWebappCloudEn($str);
  }
  public function getcount(){
    $this->DB_ReadOnly->where('status','0');
    $count = $this->DB_ReadOnly->get(MILL_ALL_SDK_CONFIG_DETAILS)->num_rows();
    $i=1;
    print "<br>";
    print "<br>";
    print $count;
    print "<br>";
    print "-------------------------------------------------------------------";
    print "<br>";
  }
  public function setcodeindb(){
   
    $all = $this->DB_ReadOnly->get(MILL_ALL_SDK_CONFIG_DETAILS)->result_array();
    $i=1;
    foreach ($all as $key => $value) {
      $accountnumber =  $value['salon_account_id'];
      if($value['status']==0){
        $display = 'Active';
      }else{
        $display = 'InActive';
      }
      $str = salonWebappCloudEn($accountnumber);
      pa($i++.'--'.$value['salon_id']."------------".$value['salon_account_id']."---------".$value['salon_name']."---------".$str." ".'-------'.$display);
    }
  }
  public function getSignature(){
    if(!isset($_POST['salon_id'])){
            $response_array = array('status' => false, 'error' => 'Invalid Salon Id', 'error_code' => 401);
          $response_code = 401;
          goto response;
        }
        if(!isset($_POST['timestamp'])){
            $response_array = array('status' => false, 'error' => 'Time Stamp Required', 'error_code' => 401);
          $response_code = 401;
          goto response;
        }
        
        if(!isset($_POST['service_url'])){
            $response_array = array('status' => false, 'error' => 'Service Url Required', 'error_code' => 401);
          $response_code = 401;
          goto response;
        }
        $response_array['Signature'] = $this->__getSignature($_POST['salon_id'],$_POST['timestamp'],$_POST['service_url']);
        $response_code = 200;

        response:
        $this->response($response_array, $response_code);
    }
    
    private function __getSignature($salon_id,$timestamp,$service_url){
       //$host = 'http://67.43.5.76/~newserver/reports/index.php/';
       $host = MAIN_SERVER_URL;
       //$host = '://67.43.5.76/~newserver/reports/index.php/';
       //print "<br/>";
       $private_key = 'SGuD4awN1VTMSxRXrUZDpEEol1oBXywj';
       $uri = $service_url;
       //$params['TimeStamp'] = urldecode($timestamp);
       $params['TimeStamp'] = $timestamp;
       $params['salon_id'] = $salon_id;
       $method = 'POST';
       // create the canonicalized query
       $canonicalized_query = array();
       foreach ($params as $param=>$value){
       $param = str_replace('%7E', '~', rawurlencode($param));
       $value = str_replace('%7E', '~', rawurlencode($value));
       $canonicalized_query[] = $param.'='.$value;
       }
       $canonicalized_query = implode('&', $canonicalized_query);
       $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
       //exit;
       $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $private_key, TRUE));
       $signature = str_replace('%7E', '~', rawurlencode($signature));
      // $signature = $host.$uri.'?'.$canonicalized_query.'&Signature='.$signature;
       return $signature;
    }    
          
}