<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(file_exists(APPPATH.'libraries/nusoap/nusoap.php'))
        require_once(APPPATH.'libraries/nusoap/nusoap.php');

if(file_exists(APPPATH.'helpers/global_helper.php'))
        require_once(APPPATH.'helpers/global_helper.php');
    

/**
 * @Author : Mohd Anas Khan (MAK)
 * Soap Libaray for specially Millennium SDK services.
 */
class Nusoap_library extends nusoap_client {
    /**
     * @var type String
     */
    public $sdkUrl = '';
    
    /**
     * @var type String
     */
    public $millGuid;
    
    
    /**
     * @var type String
     */
    public $millUsername;
    
    /**
     * @var type String
     */
    public $millPassword;
    
    /**
     * @var type String
     */
    public $millSession;
    
    /**
     * @var type String
     */
    public $millRequestSoapXmlFn;
    
    /**
     * @var type String
     */
    public $millRequestSoapXml;
    
    /**
     * @var type ARRAY
     */
    public $millRequestParameters;
    
    /**
     * @var type String
     */
    public $soapError;
    
    /**
     * @var type ARRAY
     */
    public $soapResponse;
    
    /**
     * @var type OBJECT
     */
    public $soapClient;
    
   
    /**
     * 
     * @param type $function
     * @param type $sdkUrl
     * @param type $guid
     * @return $this OBJECT CLASS
     */
    public function soap_library($sdkUrl,$guid)
        {
           $this->millGuid = $guid;
           $this->sdkUrl = $sdkUrl;
          
           return $this;
        }
    
    /**
     * 
     * @return string XML
     */
    private function __headerSoapRequest()
    {
        $soapheaderXml ='';
        $soapheaderXml .= "<MillenniumInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
					  <MillenniumGuid>".$this->millGuid."</MillenniumGuid>
					</MillenniumInfo>";
            
        if($this->millSession)
            $soapheaderXml .= "<SessionInfo xmlns=\"http://www.harms-software.com/Millennium.SDK\">
							  <SessionId>".$this->millSession."</SessionId>
							</SessionInfo>";
        
         return $soapheaderXml;
        
    } 
    
    /**
     * 
     * @param type $parameters
     * @param type $debug
     * @return $this OBJECT CLASS
     */
    public function getMillMethodCall($function = '',$parameters,$debug = false)
    {
        $this->millRequestParameters = $parameters;
       
        if($function)
            $this->millRequestSoapXmlFn = $function;
        
        $this->setHeaders($this->__headerSoapRequest());
        
        $this->soapResponse = $this->call($this->millRequestSoapXmlFn, $this->millRequestParameters , '', '', false, true);
          
        if ($this->fault || $this->error_str) {
                    $this->soapError = $this->error_str;
                     if($debug)
                        return $this; 
                     else{
                        print $this->soapError;
                        // $this->__sendmail($this->soapError,$this->sdkUrl,$this->millGuid);
                     }
                     
        }
        
        if(isset($this->responseHeader['SessionInfo']['SessionId']))
            {
                return $this->millSession = $this->responseHeader['SessionInfo']['SessionId'];
            }
        
        if($debug)
             return $this;      
        else
             return $this->__convertXmlToPhpArray($this->soapResponse);
            /*if(!is_array($this->soapResponse)){
                return $this->__convertXmlToPhpArray($this->soapResponse);
            } else {
                //$arrayResponse = $this->soapResponse["GetAppointmentsCheckedInResult"];
                return $this->soapResponse;
            }*/
    }

    /**
     * 
     * @param type $arrayResponse
     * @return type Array
     */

    public function getMillMethodCallArray($function = '',$parameters,$debug = false)
    {
        $this->millRequestParameters = $parameters;
       
        if($function)
            $this->millRequestSoapXmlFn = $function;
        
        $this->setHeaders($this->__headerSoapRequest());
        
        $this->soapResponse = $this->call($this->millRequestSoapXmlFn, $this->millRequestParameters , '', '', false, true);
          
        if ($this->fault || $this->error_str) {
                    $this->soapError = $this->error_str;
                     if($debug)
                        return $this; 
                     else{
                        print $this->soapError;
                        // $this->__sendmail($this->soapError,$this->sdkUrl,$this->millGuid);
                     }
                     
        }
        
        if(isset($this->responseHeader['SessionInfo']['SessionId']))
            {
                return $this->millSession = $this->responseHeader['SessionInfo']['SessionId'];
            }
        
        if($debug)
             return $this;      
        else

            return $this->soapResponse;
            //return $this->__convertXmlToPhpArray($this->soapResponse);
            /*if(!is_array($this->soapResponse)){
                return $this->__convertXmlToPhpArray($this->soapResponse);
            } else {
                //$arrayResponse = $this->soapResponse["GetAppointmentsCheckedInResult"];
                return $this->soapResponse;
            }*/
    }
    
    /**
     * 
     * @param type $xmlResponse
     * @return type Array
     */
    private function __convertXmlToPhpArray($xmlResponse)
    {
        
        try{ 
            if(isset($xmlResponse[$this->millRequestSoapXmlFn.'Result']) && is_string($xmlResponse[$this->millRequestSoapXmlFn.'Result'])){
                 if($xmlResponse[$this->millRequestSoapXmlFn.'Result']!='true'){
                    $xmlRepObject =  new SimpleXMLElement(utf8_encode($xmlResponse[$this->millRequestSoapXmlFn.'Result'])); 
                    return $xml = json_decode(json_encode($xmlRepObject),TRUE);
                }
            }
                else {return $xmlResponse;}   
            } 
        catch (Exception $e) { 
               //$this->__sendmail($this->soapError,$this->sdkUrl,$this->millGuid); 
               return $xmlResponse;
            }
    }
    
    private function __sendmail($response_check,$sendpoint, $soapguid){
            $CI =& get_instance();
            $CI->load->model('Common_model');
            $details = $CI->Common_model->getMillSdkConfigDetailsByGuid($soapguid)->row_array();
            $salon_name = $details['salon_name'];
            $requestUrl = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $sender_email   = 'report@webappclouds.com';
            $sender_name    = 'webappclouds';
            $receiver_name = 'Subramanyam';
            $receiver_email = 'subramanyam-php@webappclouds.com';
            $email_subject  = '['.date('Y-m-d H:i:s').'] REPORT CRON ERROR';
            $email_message = "<p>Hi ".ucfirst($receiver_name).",</p>";
            $email_message .= "<p>Soap Request URL: ".$requestUrl."</p>";
            $email_message .= "<p>Salon Name: ".$salon_name."</p>";
            $email_message .= "<p>Soap Endpoint: ".$sendpoint."</p>";
            $email_message .= "<p>Soap GUID: ".$soapguid."</p>";
            $email_message .= "<p>".json_encode($response_check)."</p>";
            $email_message .= "<p>Date:".date('Y-m-d H:i:s')."</p>";
            $email_message .= "<p>Time:".time()."</p>";
            $email_message .= "Thanks and Regards,<br />";
            $email_message .= 'Webappclouds Team';	
            
            // To send HTML mail, the Content-type header must be set
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=iso-8859-1';

            // Additional headers
            $headers[] = 'From:'.$sender_name.' <'.$sender_email.'>';
         
           echo  mail($receiver_email, $email_subject, $email_message,implode("\r\n", $headers));
           
           
    }
}