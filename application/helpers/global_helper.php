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
		$settings = mysqli_fetch_array(mysqli_query("SELECT * FROM ".SETTINGS_TABLE." ORDER BY id ASC LIMIT 1;"));
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
    
    /**
     function returns the encode 
    */
    function salonWebappCloudEn($str){
     	for ($i=0; $i <5; $i++) { 
     	  $str =  base64_encode($str);
     	}
     	return $str;
    }
    /**
     function returns the decode 
    */
    function salonWebappCloudDe($str){
     	for ($i=0; $i <5; $i++) { 
     	  $str =  base64_decode($str);
     	}
     	return $str;
    }
    /**
    function returns the date format
    */ 
    function setDateFormat($date){
    	return date('d-m-Y', strtotime($date));
    }
    //It calculates and returns last 4 business weeks ranges as per provided year and day
    function getLast4BusinessWeeksRanges($day,$year){
        $currentDayinYear = date($year.'-m-d');
	    $lastWeekDay = date('Y-m-d',strtotime($currentDayinYear.' last '.$day));
	    $lastWeekEndDay = date('Y-m-d', strtotime($lastWeekDay.'+6 days'));
	    $Week['start_date'] = $lastWeekDay;$Week['end_date'] = $lastWeekEndDay;$Week['current_week'] = true;
        $last4BusinessStartDays[] = $Week;
	    for($i=0;$i<3;$i++) {
	        $Week = array();
	        $lastWeekDay = date('Y-m-d',strtotime($lastWeekDay."-7 days"));
	        $lastWeekEndDay = date('Y-m-d',strtotime($lastWeekDay.'+6 days'));
	        $Week['start_date'] = $lastWeekDay;$Week['end_date'] = $lastWeekEndDay;$Week['current_week'] = false;
	        $last4BusinessStartDays[] = $Week;
	    }
    return array_reverse($last4BusinessStartDays);
    }


    //It calculates and returns last 4 weeks ranges as per provided year and day
    function getLast4WeekRanges($year) {
        $currentDayinYear = date($year.'-m-d');
	    $lastWeekDay = date('Y-m-d',strtotime($currentDayinYear.' last Monday'));
	    $lastWeekEndDay = date('Y-m-d', strtotime($lastWeekDay.'+6 days'));
	    $Week['start_date'] = $lastWeekDay;$Week['end_date'] = $lastWeekEndDay;$Week['current_week'] = true;
        $last4WeekStartDays[] = $Week;
	    for($i=0;$i<3;$i++) {

	        $Week = array();
	        $lastWeekDay = date('Y-m-d',strtotime($lastWeekDay."-7 days"));
	        $lastWeekEndDay = date('Y-m-d',strtotime($lastWeekDay.'+6 days'));
	        $Week['start_date'] = $lastWeekDay;$Week['end_date'] = $lastWeekEndDay;$Week['current_week'] = false;
	        $last4WeekStartDays[] = $Week;
	    }
     return array_reverse($last4WeekStartDays);
    }
    
    function send_mail_database_error($errordata='') {
	    $server_url = MAIN_SERVER_URL;
	    $erro_code = $errordata['code'];
	    $erro_message = $errordata['message'];
	    $tablename = $errordata['tablename'];
	    $sender_email   = 'subramanyam-php@webappclouds.com';
        $sender_name    = 'Subramanyam Vemuri';
        $receiver_name = 'Subramanyam';
        $receiver_email = 'subramanyam-php@webappclouds.com,christopher@webappclouds.com,ramakrishna-aws@webappclouds.com';
        $email_subject  = '['.date('Y-m-d H:i:s').']'.$erro_message;
        $email_message = "<p>Hi ".ucfirst($receiver_name).",</p>";
        $email_message .= "<p>SERVER URL: ".$server_url."</p>";
        $email_message .= "<p>Table Name: ".$tablename."</p>";
        $email_message .= "<p>Message: ".$erro_message."</p>";
        $email_message .= "<p>Error Code: ".$erro_code."</p>";
        $email_message .= "<p>Date:".date('Y-m-d H:i:s')."</p>";
        $email_message .= "<p>Time:".time()."</p>";
        $email_message .= "Thanks and Regards,<br />";
        $email_message .= 'Webappclouds Team';	
        // To send HTML mail, the Content-type header must be set
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        // Additional headers
        $headers[] = 'From:'.$sender_name.' <'.$sender_email.'>';
        print $email_message;
        echo  mail($receiver_email, $email_subject, $email_message,implode("\r\n", $headers));
}


    

?>