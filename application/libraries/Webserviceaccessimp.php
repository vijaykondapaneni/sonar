<?php
/**
 * class to check and validate API access calls
 */
class Webserviceaccess {    
    // request headers
    protected $headers;
    // request timestamp
    protected $Timestamp;
    // error messages
    CONST INVALID_SIGNATURE = "Invalid Signature";
    CONST MISSING_TIME_STAMP = "Missing Time Stamp";
    CONST MISSING_SALON_ID = "Missing Salon Id";
    CONST EXPIRED_TIME_STAMP = "Time Stamp Expired";
    CONST INVALID_REQUEST = "Authorization Failed Invalid Request";
    CONST REQUEST_LIFE_TIME = 900000;//in miliseconds (15 mins)
    CONST REPORTS_SERVER_URL = 'http://67.43.5.76/~newserver/reports/index.php/';
    //CONST REPORTS_PRIVATEKEY = "37ED2479429E1105E0530100007F248D";
    CONST REPORTS_PRIVATEKEY = "SGuD4awN1VTMSxRXrUZDpEEol1oBXywj";
    CONST VERSION = 1.0;
    CONST METHOD_NOT_ALLOWED='Method Not Allowed';
    CONST SUCCESS = 'SUCCESS';
    CONST FAILED = 'FAILED';
    CONST GETAJAXCALL = 'GETAJAXCALL';
    CONST VERSION_CHECKING = 'Version Checking';
    CONST AJAX_CALL_ALLOWED = 'AJAX CALL ALLOWED';
    CONST DIRCT_ACCESS_NOT_ALLOWED = 'DIRECT ACCESS NOT ALLOWED';
    CONST PARAMETERS_MISSED = 'Parameters Missed';
    var $ci; 
    public function __construct() {
        $this->ci =& get_instance(); 
        // load request headers
        $this->loadHeaders();
        $this->Signature = $this->isSetHeaderSignature();
        $this->Timestamp  = $this->isSetHeaderTimeStamp();
        $this->Version    = $this->isSetHeaderVersion();
        $this->requestedWith  = $this->isSetHeaderRequestWith();
     }
    /**
    * Function returns the trim the string
    */
    private function __customtrim($inputstr = '')
        {
            return rtrim(ltrim($inputstr));
        }
         
    /**
    * Function returns the header signature
    */
    private function isSetHeaderSignature()
        {
            if(isset($this->header['Signature'])){
               return $this->__customtrim($this->header['Signature']);  
            }
        }
    /**
    * Function returns the header signature
    */
    private function isSetHeaderTimeStamp()
        {
            if(isset($this->header['Timestamp'])){
               return $this->__customtrim($this->header['Timestamp']);  
            }
        }
    /**
    * Function returns the header signature
    */
    private function isSetHeaderVersion()
        {
            if(isset($this->header['Version'])){
               return $this->__customtrim($this->header['Version']);  
            }
        }
    /**
    * Function returns the header request with
    */    
    private function isSetHeaderRequestWith()
        {
            if(isset($this->header['X-Requested-With'])){
               return $this->__customtrim($this->header['X-Requested-With']);  
            }
        }             
    /**
     * function to prepare HTTP Response Codes
    */
    private function  http_response($code = NULL) {
            if ($code !== NULL) {
                switch ($code) {
                    case 100: $text = 'Continue'; break;
                    case 101: $text = 'Switching Protocols'; break;
                    case 200: $text = 'OK'; break;
                    case 201: $text = 'Created'; break;
                    case 202: $text = 'Accepted'; break;
                    case 203: $text = 'Non-Authoritative Information'; break;
                    case 204: $text = 'No Content'; break;
                    case 205: $text = 'Reset Content'; break;
                    case 206: $text = 'Partial Content'; break;
                    case 300: $text = 'Multiple Choices'; break;
                    case 301: $text = 'Moved Permanently'; break;
                    case 302: $text = 'Moved Temporarily'; break;
                    case 303: $text = 'See Other'; break;
                    case 304: $text = 'Not Modified'; break;
                    case 305: $text = 'Use Proxy'; break;
                    case 400: $text = 'Bad Request'; break;
                    case 401: $text = 'Unauthorized'; break;
                    case 402: $text = 'Payment Required'; break;
                    case 403: $text = 'Forbidden'; break;
                    case 404: $text = 'Not Found'; break;
                    case 405: $text = 'Method Not Allowed'; break;
                    case 406: $text = 'Not Acceptable'; break;
                    case 407: $text = 'Proxy Authentication Required'; break;
                    case 408: $text = 'Request Time-out'; break;
                    case 409: $text = 'Conflict'; break;
                    case 410: $text = 'Gone'; break;
                    case 411: $text = 'Length Required'; break;
                    case 412: $text = 'Precondition Failed'; break;
                    case 413: $text = 'Request Entity Too Large'; break;
                    case 414: $text = 'Request-URI Too Large'; break;
                    case 415: $text = 'Unsupported Media Type'; break;
                    case 500: $text = 'Internal Server Error'; break;
                    case 501: $text = 'Not Implemented'; break;
                    case 502: $text = 'Bad Gateway'; break;
                    case 503: $text = 'Service Unavailable'; break;
                    case 504: $text = 'Gateway Time-out'; break;
                    case 505: $text = 'HTTP Version not supported'; break;
                    default:
                        exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
                }
              
                $this->ci->output->set_status_header($code);
                if($code != 200){
                   //show_error($text, $code, $heading = 'An Error Was Encountered: '.$code);
                    return $this->response; exit();
                }
            } 
        }
        
    
    /**
     * function to prepare HTTP request header array
    */
    protected function loadHeaders(){
        $this->header = ''; 
           foreach ($_SERVER as $name => $value) 
           { 
               if (substr($name, 0, 5) == 'HTTP_') 
               { 
                   $this->header[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
               } 
           } 
          return $this->header; 
    }

    /**
     * function to prepare HTTP request header array
    */
    protected function getSignature($service_url,$salon_id,$timestamp){
       $host = self::REPORTS_SERVER_URL;
       //print "<br/>";
       $private_key = self::REPORTS_PRIVATEKEY;
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
       $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $private_key, TRUE));
       $signature = str_replace('%7E', '~', rawurlencode($signature));
      // $signature = $host.$uri.'?'.$canonicalized_query.'&Signature='.$signature;
       return $signature;
    }

    /**
     * function to get time stamp
     * @return ERROR if time stamp is missing
     */
    protected function getTimeStamp(){

        if(isset($this->Timestamp)){
            //$time_stamp = urldecode($this->Timestamp);
            $time_stamp =$this->Timestamp;
            $microtime = microtime();
            $comps = explode(' ', $microtime); 
            $militime = sprintf('%d%03d', $comps[1], $comps[0] * 1000);
            // calculate the time defference
            //print $militime - $time_stamp;
            if(self::REQUEST_LIFE_TIME >= ($militime - $time_stamp)){
                return $this->Timestamp;
            }else{
                $this->errorString = self::EXPIRED_TIME_STAMP;
                return "Error";
            }
        }else{
            $this->errorString = self::MISSING_TIME_STAMP;
            return "Error";
        }
    }
    
    /**
     * Function to validate API request
     * @return boolean True/False
     */
    public function validateWebAppWs($service_url,$salon_id){

        // Check Timestamp Exist Or Not
        if(isset($this->Timestamp)) 
        {
            $time_stamp = $this->getTimeStamp();
            if($time_stamp=='Error'){
               $this->__returnResponse(self::FAILED,self::GETAJAXCALL,401,self::EXPIRED_TIME_STAMP); 
               goto response; 
            }else{
              $this->__returnResponse(self::SUCCESS,self::GETAJAXCALL,200,self::SUCCESS);
            }
        }
        else 
        {
            if(!(isset($this->Signature ,$this->TimeStamp,$this->Version,$this->requestedWith))) {
                $this->__denyDirectAccessInBrowserOnAjaxCall('Timestamp Failed');
                goto response; 
            }
        }

        // check if signature valid or not
        if(isset($this->Signature)) 
        {
            $getSignature  = $this->getSignature($service_url,$salon_id,$this->Timestamp);
            //print "<br/>";
            if($getSignature==$this->Signature){
                $this->__returnResponse(self::SUCCESS,self::METHOD_NOT_ALLOWED,200,self::SUCCESS);
            }else{
                $this->__returnResponse(self::FAILED,self::METHOD_NOT_ALLOWED,401,self::INVALID_SIGNATURE);
                goto response;   
            }
        }
        else 
        {
            if(!(isset($this->Signature ,$this->TimeStamp,$this->Version,$this->requestedWith))) {
                $this->__denyDirectAccessInBrowserOnAjaxCall('Signature Failed');
                goto response; 
            }
        }

        // Check Version 
        if(isset($this->Version)) 
        {
            if($this->Version==self::VERSION){
               $this->__returnResponse(self::SUCCESS,self::VERSION_CHECKING,200,self::SUCCESS);      
            }else{
               $this->__returnResponse(self::SUCCESS,self::VERSION_CHECKING,200,self::SUCCESS);
            }
        }
        else 
        {
            if(!(isset($this->Signature ,$this->TimeStamp,$this->Version,$this->requestedWith))) {
                $this->__denyDirectAccessInBrowserOnAjaxCall('Vesrion Failed');
                goto response; 
            }
        }     
        response:
        if(isset($this->response['HTTPCODE']) && ($this->response['HTTPCODE'] !== 200)){ 
            $this->http_response($this->response['HTTPCODE'] , $this->response);
                    
        } else {        
            $this->http_response($this->response['HTTPCODE']);
        }

        return ($this->response); 
    }

    /**
    * Function returns the status methods
    */
    private function __returnResponse($status = '', $method = '',$httpCode = '',$message = '')
        {
            if(!empty($status))
                $this->response['STATUS'] =   $status;
            
            if(!empty($method))
                $this->response['METHOD'] =   $method;
            
            if(!empty($httpCode))
                $this->response['HTTPCODE'] =   $httpCode;
            
            if(!empty($message))
                $this->response['MESSAGE'] =   $message;
        }
    /**
    * Function returns deny the access
    */
    private function __denyDirectAccessInBrowserOnAjaxCall($message)
        {
            $this->__returnResponse(self::FAILED,self::PARAMETERS_MISSED,405,$message);
           /* if($this->requestedWith === 'XMLHttpRequest'){
                    $this->__returnResponse(self::SUCCESS,self::GETAJAXCALL,200,self::AJAX_CALL_ALLOWED);
            } else {
                    $this->__returnResponse(self::FAILED,self::GETAJAXCALL,405,self::INVALID_REQUEST);
            }*/
        }
}