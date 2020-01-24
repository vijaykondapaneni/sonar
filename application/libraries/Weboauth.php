<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
    class Weboauth 
    {
         
        private  $header;
        private  $authorizeToken;
        private  $accessToken;
        private  $activeSession;
        private  $requestedWith;
        
        
        private  $__salon;
        private  $__session;
        
        private  $response;
       
        var $ci;
        
        CONST TIMEOUT = 30;
            
        function __construct() 
        {
               $this->ci =& get_instance(); 
               $this->getallheaders(); 
               $this->authorizeToken = $this->isSetHeaderAuthorize();
               $this->requestedWith  = $this->isSetHeaderRequestWith();
               $this->accessToken    = $this->isSetHeaderAccessToken();
               $this->activeSession  = $this->isSetHeaderActiveSession();
        }
        
        public function Oauth() 
        {
            if(isset($this->authorizeToken)) {
                        $this->__authorizeSalonCall();
            } else {
                 if(!(isset($this->accessToken ,$this->activeSession,$this->requestedWith))) {
                        $this->__denyDirectAccessInBrowserOnAjaxCall();
                    }
            }
            
            if(isset($this->accessToken) || isset($this->activeSession)) {
                        $this->__isValidAccessToken();     
            } 
            
            
            if(isset($this->response['HTTPCODE']) && ($this->response['HTTPCODE'] !== 200)){ 
                    $this->http_response($this->response['HTTPCODE'] , $this->response);
                    
            } else {        
                    $this->http_response($this->response['HTTPCODE']);
            }
            
           return ($this->response); 
        }
        
        
        private function __isValidAccessToken()
        {
           
            if($this->accessToken && $this->activeSession){
                /** Valid active session with DB if exist OR NOT*/
                $where_array = array('auth_session' => $this->activeSession , 'auth_access_token' => $this->accessToken);
                $db_secure_table_row = $this->ci->db->where($where_array)
                                                     ->get('salon_secure_authentication');
                
                $this->__session = $db_secure_table_row->row_array();
                  
                /** IF active session is not valid with db will pass error and also destroy that session.*/
                if($db_secure_table_row->num_rows() > 0){
                     $post_salon_id = $this->ci->input->post('salon_id', TRUE);
                     $get_salon_id = $this->ci->input->get('salon_id', TRUE);
                     $this->__salon = isset($post_salon_id) ? $post_salon_id : $get_salon_id;
                     $this->__validSessionWithSalon();
                }else{
                     $this->__returnResponse('FAILED','GETACCESSTOKEN',401,'NOT AUTHORIZED ACCESS.');          
                 }
            } else {
                     $this->__returnResponse('FAILED','GETACCESSTOKEN',401,'NOT AUTHORIZED ACCESS.');          
            }
        }

        private function __validSessionWithSalon()
        {
                        if( ($this->__session['auth_access_token'] === $this->accessToken) && (strtotime(date('Y-m-d H:i:s')) <= strtotime($this->__session['session_reset_at']))){
                               if($this->__session['salon_id'] === $this->__salon){
                                  $this->__returnResponse('SUCCEED','GETACCESSTOKEN',200,'AUTHORIZATION COMPLETED');
                                }
                                else{
                                  $this->__returnResponse('FAILED','GETACCESSTOKEN',203,'SESSION TOKEN VALID BUT NOT ALLOW TO THIS SALON. PLEASE CHECK SALON ID');
                                }  
                        } else {
                                  $this->__returnResponse('SESSIONEXPIRE','GETACCESSTOKEN',403,'ACCESS TOKEN NOT MATCHED');
                        } 
        }
            

        private function __authorizeSalonCall()
        {
            if(!empty($this->authorizeToken)){
                    $where_array = array('auth_secure_token' => $this->authorizeToken);
                    try {
                            $db_secure_table_row = $this->ci->db->where($where_array)
                                                                ->get('salon_secure_authentication');
                            if($db_secure_table_row->num_rows() > 0) {
                               //insert clients in salonbiz clients table
                                $salon_authorization_data = $db_secure_table_row->row_array();
                                $this->__genAccessToken($salon_authorization_data); 
                                
                            } else {
                                $this->__returnResponse('FAILED','GETAUTHORIZATION',401,'YOUR ARE NOT AUTHORIZED');
                            }
                        } catch (Exception $exc) {
                            echo $exc->getTraceAsString();
                        } 
             } else {
                                $this->__returnResponse('FAILED','GETAUTHORIZATION',401,'NOT AUTHORIZED ACCESS');
             }       
        }
        
        
        private function isSetHeaderAuthorize()
        {
            if(isset($this->header['Authorization-Bearer'])){
               return $this->__customtrim($this->header['Authorization-Bearer']);  
            }
        }
        
        private function isSetHeaderAccessToken()
        {  
            if(isset($this->header['Access-Token'])){
               return $this->__customtrim($this->header['Access-Token']);  
            }
            
        }
        
        private function isSetHeaderActiveSession()
        {
            if(isset($this->header['Active-Session'])){
               return $this->__customtrim($this->header['Active-Session']);  
            }
        }
        
        
        private function isSetHeaderRequestWith()
        {
            if(isset($this->header['X-Requested-With'])){
               return $this->__customtrim($this->header['X-Requested-With']);  
            }
        }
        
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
                //$this->ci->load->view("errors/html/error_404",array('heading'=> $text, 'message'=> ''));
                if($code != 200){
                   //show_error($text, $code, $heading = 'An Error Was Encountered: '.$code);
                    return $this->response; exit();
                }
            } 
        }
            
        function getallheaders() 
        { 
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
        
        private function __genAccessToken($salon_authorization_data)
        {
            if(is_array($salon_authorization_data))
                {
//                        if( (strtotime(date('Y-m-d H:i:s')) >= strtotime($salon_authorization_data['session_reset_at'])) || (empty($salon_authorization_data['auth_session']) || empty($salon_authorization_data['auth_access_token']) ) ){
                
                if( (strtotime(date('Y-m-d H:i:s')) >= strtotime($salon_authorization_data['session_reset_at']))){
                   // echo "INSIDE CONDITION";
                            $session_custom_id = $this->__genSession();
                                 
                            $salon_authorization_data['auth_session'] = $session_custom_id;
                            $salon_authorization_data['auth_access_token'] = $this->__genSecureToken(32);
                            $salon_authorization_data['auth_updated_at'] = date('Y-m-d H:i:s');
                            $salon_authorization_data['session_reset_at'] = date("Y/m/d H:i:s", strtotime("+".Weboauth::TIMEOUT." minutes"));
                            $whereConditionAuth = array('auth_id'=>$salon_authorization_data['auth_id']);
                            $this->ci->db->where($whereConditionAuth);
                            $this->ci->db->update('salon_secure_authentication', $salon_authorization_data);
                            
                            $this->__returnResponse('SESSIONACTIVE','GETACCESSTOKEN',200,'',$salon_authorization_data['auth_session'],$salon_authorization_data['auth_access_token'],$salon_authorization_data['session_reset_at']);
                        } else {
                            if((!empty($salon_authorization_data['auth_session']) || !empty($salon_authorization_data['auth_access_token']) )){
                                    $this->__returnResponse('SESSIONACTIVE','GETACCESSTOKEN',200,'',$salon_authorization_data['auth_session'],$salon_authorization_data['auth_access_token'],$salon_authorization_data['session_reset_at']);
                            }else {
                                
                            }
                        }
                 
            }
        }


        private function __denyDirectAccessInBrowserOnAjaxCall()
        {
            if($this->requestedWith === 'XMLHttpRequest'){
                    $this->__returnResponse('SUCCESS','GETAJAXCALL',200,'AJAX CALL ALLOWED');
            } else {
                    $this->__returnResponse('FAILED','GETAJAXCALL',405,'DIRECT ACCESS NOT ALLOWED');
            }
        }
                    
        private function genrateSecureAuthDataForSalon($salon_id = '')
        {
            if(!$salon_id)
                die ('Please pass valid salon id.');
            
            $session_array = array(
                'salon_id' => $salon_id,
                'auth_session' => $this->__genSession(),
                'auth_secure_token' => $this->__genSecureToken(32),
                'auth_access_token' => '',
                'auth_created_at' => date('Y-m-d H:i:s'),
                'auth_updated_at' => date('Y-m-d H:i:s'),
            );
            
            $where_array = array('salon_id' => $salon_id);
            
            try {
                    $this->ci->db->where($where_array);
					$db_secure_table_row = $this->ci->db->get('salon_secure_authentication');
              
					if($db_secure_table_row->num_rows() <= 0) {
                       //insert clients in salonbiz clients table
						$this->ci->db->insert('salon_secure_authentication', $session_array);
                    }  
                    
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                }

            $salon_secure_db_data = $this->ci->db->get('salon_secure_authentication')->row_array('salon_secure_authentication',$where_array);
            
            return isset($salon_secure_db_data)? $salon_secure_db_data : $session_array;
        }
        
        public function __generateSecureAuthTokeFirstTime($salon_id)
        {
            $this->genrateSecureAuthDataForSalon($salon_id);
        }
        
        private function __genSession($key = '')
        {
            return md5(microtime(true).$key);
        }

        private function __genSecureToken($bytes)
        {
            return $token  =  bin2hex(openssl_random_pseudo_bytes($bytes));
        }
        
        private function __customtrim($inputstr = '')
        {
            return rtrim(ltrim($inputstr));
        }
        
        private function __returnResponse($status = '', $method = '',$httpCode = '',$message = '', $activesession = '', $accesstoken = '', $sessionResetAt = '')
        {
                if(!empty($status))
                    $this->response['STATUS'] =   $status;
                
                if(!empty($method))
                    $this->response['METHOD'] =   $method;
                
                if(!empty($httpCode))
                    $this->response['HTTPCODE'] =   $httpCode;
                
                if(!empty($message))
                    $this->response['MESSAGE'] =   $message;
                
                if(!empty($activesession))
                    $this->response['ACTIVESESSION'] =  $activesession;
                
                if(!empty($accesstoken))
                    $this->response['ACCESSTOKEN'] =   $accesstoken;
                
                if(!empty($sessionResetAt))
                    $this->response['SESSIONRESETAT'] =   $sessionResetAt;
        }
        
    }