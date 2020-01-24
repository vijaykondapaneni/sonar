<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @Author : Mohd Anas Khan (MAK)
 * Soap Libaray for specially Millennium SDK services.
 */
class Nusoap_library {
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
    public $millLogFn = "Logon";
    
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
    * Cunstruct Fn
    */
    public function __construct() {}

    /**
     * 
     * @param type $function
     * @param type $sdkUrl
     * @param type $guid
     * @return $this OBJECT CLASS
     */
    public function nusoap_library($function,$sdkUrl,$guid)
        {
           if(file_exists(APPPATH.'libraries/nusoap/nusoap.php'))
                        require_once(APPPATH.'libraries/nusoap/nusoap.php');
           
           $this->millRequestSoapXmlFn = $function;
           $this->millGuid = $guid;
           $this->sdkUrl = $sdkUrl;
           return $this;
        }
    
    /**
     * 
     * @param type $logDetails
     * @return $this OBJECT CLASS
     */    
    public function getLogonSession($logDetails)
    {
        $this->millRequestParameters = $logDetails;
        $this->millUsername = isset($logDetails['User'])?$logDetails['User']:''; 
        $this->millPassword = isset($logDetails['Password'])?$logDetails['Password']:''; 
        
        $this->soapClient = new nusoap_client(
           $this->sdkUrl.'?WSDL',
           'wsdl',
           '',
           '',
           '',
           ''
           );
        $this->soapClient->setHeaders($this->__headerSoapRequest());
        $this->soapResponse = $this->soapClient->call($this->millLogFn, $this->millRequestParameters , '', '', false, true);
        

        if(isset($this->soapClient->responseHeader['SessionInfo']['SessionId']))
               $this->millSession = $this->soapClient->responseHeader['SessionInfo']['SessionId'];
             
            
        if ($this->soapClient->fault || $this->soapClient->error_str)
        {
                    $this->soapError = $this->soapClient->error_str;
        }
                    
       return $this;
        
    }   
    
    /**
     * 
     * @param type $parameters
     * @param type $debug
     * @return $this OBJECT CLASS
     */
    public function getMillMethodCall($parameters,$debug = false)
    {
       $this->millRequestParameters = $parameters;
       
       $this->soapClient = new nusoap_client(
           $this->sdkUrl.'?WSDL',
           'wsdl',
           '',
           '',
           '',
           ''
           );
       
        $this->soapClient->setHeaders($this->__headerSoapRequest());
        
        $this->soapResponse = $this->soapClient->call($this->millRequestSoapXmlFn, $this->millRequestParameters , '', '', false, true);
            
        if ($this->soapClient->fault || $this->soapClient->error_str) {
                    $this->soapError = $this->soapClient->error_str;
                     if($debug)
                        return $this; 
                     else
                        exit($this->soapError);
        }
        
       
        if($debug)
             return $this;      
        else
             return $this->__convertXmlToPhpArray($this->soapResponse); 
            
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
     * @param type $xmlResponse
     * @return type Array
     */
    private function __convertXmlToPhpArray($xmlResponse)
    {
        try { 

            $xmlRepObject =  new SimpleXMLElement($xmlResponse[$this->millRequestSoapXmlFn.'Result']); 
            return $xml = json_decode(json_encode($xmlRepObject),TRUE);
        } 
        catch (Exception $e) { 
            return $xmlResponse;
           // echo $e;
             }
           
           
    }
}
