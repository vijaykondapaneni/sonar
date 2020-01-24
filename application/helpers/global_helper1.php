<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	/**
     * 
     * @param type $to
     * @param type $toname
     * @param type $subject
     * @param type $text
     * @param type $html
     * @param type $from
     * @param type $fromname
     * @param type $api_user
     * @param type $api_key
     * @param type $sendgrid_api_key_id
     * @param type $repyTo
     * @return boolean
     */
	function api_mail($to,$toname="",$subject,$text="",$html="",$from,$fromname="",$api_user,$api_key,$sendgrid_api_key_id,$repyTo='') {
		ini_set('display_startup_errors',0);
		ini_set('display_errors',0);
		
		$url = 'https://api.sendgrid.com/';
		$params = array(
			'to'        => $to,
			'toname'    => $toname,
			'from'      => $from,
			'fromname'  => $fromname,
			'subject'   => $subject,
			'text'      => $text,
			'html'      => $html
		);
		 
		if(!filter_var($repyTo, FILTER_VALIDATE_EMAIL) === false && !empty($repyTo)) {
			$params['replyto']	= $repyTo;
		}

		$request =  $url.'api/mail.send.json';

		$session = curl_init($request);
		// Tell PHP not to use SSLv3 (instead opting for TLS)
		curl_setopt($session, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $sendgrid_api_key_id));
		// Tell curl to use HTTP POST
		curl_setopt ($session, CURLOPT_POST, true);
		// Tell curl that this is the body of the POST
		curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
		// Tell curl not to return headers, but do return the response
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		// obtain response
		$response = curl_exec($session);
		
		curl_close($session);
		
		$result = json_decode($response);

		if ($result->message == "success") {
			return true;
		} else {
			return true;
		}
	}
	
    /**
     * 
     * @param type $to
     * @param type $toname
     * @param type $subject
     * @param type $message
     * @param type $from
     * @param type $fromname
     * @param type $repyTo
     */
	function send_mail($to,$toname="",$subject,$message,$from,$fromname="",$repyTo='') {
		$settings = mysql_fetch_array(mysql_query("SELECT * FROM ".SETTINGS_TABLE." ORDER BY id ASC LIMIT 1;"));
		if ($settings['enable_sendgrid'] == 1) {
			api_mail($to,$toname,$subject,'',$message.'<br/ ><br/ >',$from,$fromname,$settings['sendgrid_api_user'],$settings['sendgrid_api_key'],$settings['sendgrid_api_key_id'],$repyTo);
		} else {
			$headers = 'From: '.$from.''."\r\n".'Reply-To: '.$from.'';
			mail($to,$subject,$message,$headers);
		}
	}
    
    /**
     * 
     * @param type $phone
     * @return string
     */
	function format_phone_us($phone) {
		// note: making sure we have something
		if(!isset($phone{3})) { return ''; }
		$phoneno = str_replace('+1','',$phone);
		$phone_details = preg_replace("/[^0-9]/", "", $phoneno);
		$length = strlen($phone_details);
		switch($length) {
			case 7:
				return preg_replace("/([0-9]{3})([0-9]{4})/", "$1$2", $phone_details);
			break;
			case 10:
				return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1$2$3", $phone_details);
			break;
				case 11:
				return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "$1$2$3$4", $phone_details);
			break;
			default:
				return $phone;
			break;
		}
	}
	
    /**
     * 
     * @param type $a
     * @param type $b
     * @return type
     */
	function cmp_by_date_added_desc($a, $b) {
		$a = (int) $a["date_redeemed"];
		$b = (int) $b["date_redeemed"];
		return $b - $a;
	}
	
	 
    /**
     * @author Anas <anas-php@webappclouds.com>
     * @param type $value
     * @param type $comment
     * @param type $exit
     */ 
    function pa($value, $comment='' ,$exit = false){
            echo "<pre>-----------------------------BOL: ". strtoupper(str_replace(' ', '', $comment))."----------------------------<br/>";
            print_r($value);
            echo "<br/>-----------------------------EOL: ". strtoupper(str_replace(' ', '', $comment))."---------------------------</pre>";
            if($exit){
              exit();
            }
    }
    
    /**
     * @description: return Date
     * @param type $params
     * @param type $setdefaultday
     * @return type
     */
    function getDateFn($params = '',$setdefaultday = '') { return ($params != '') ? date("Y-m-d",$params) : date("Y-m-d");}
    
    

?>