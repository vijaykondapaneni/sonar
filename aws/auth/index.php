<?php
	define('BASEPATH', '/');
	require('../application/config/database.php');
	require('AuthnetAIM.class.php');

	$mode = 'Production'; // Development or Production
	
	mysql_connect($db['default']['hostname'], $db['default']['username'], $db['default']['password']) or die("Unable to connect to the MySql server."); 
	mysql_select_db($db['default']['database'])or die("Unable to select the Database.");

	function logM($msg) {
		$encryptionMethod = "AES-256-CBC";
		$secretHash = '27js82NJDNOF8enfew84nhf9frERff98';
		$now = date("F j, Y, g:i:s a");
		$msg = $now." -- ".$msg;
		//$msg = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $secretHash, $msg, MCRYPT_MODE_ECB);
		//echo mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $secretHash, $msg, MCRYPT_MODE_ECB);
		error_log($msg."\r\n", 3, 'log.php');
	}

	function decode5t($str) {
		for($i=0;$i<5;$i++){
			$str = base64_decode(strrev($str)); //reverse the string first and then apply base64
		}
		return $str;
	}
	//echo decode5t($_POST['creditcard']); die();
	
	$jsonOutput = array();
	$jsonOutput['status'] = FALSE;
	$jsonOutput['msg'] = '';
	$jsonOutput['errors'] = 0;
	$jsonOutput['approval_code'] = '';
	$jsonOutput['transaction_id'] = '';
	$jsonOutput['avs_result'] = '';
	$jsonOutput['cvv_result'] = '';

	$req = array(
'user_id','email','product','business_firstname','business_lastname','business_address','business_city',
'business_state','business_zipcode','business_telephone','shipping_firstname','shipping_lastname',
'shipping_address','shipping_city','shipping_state','shipping_zipcode','creditcard','expiration',
'total','cvv','invoice','tax'
				);
	$reqNotEmpty = array('salon_id','creditcard','expiration','total','cvv');

	foreach($req as $v) {
		${$v} = '';
		if(isset($_POST[$v])){
			${$v} = $_POST[$v];
		}
	}
						
	foreach($reqNotEmpty as $c) {
		${$c} = '';
		if(isset($_POST[$c]) && !empty($_POST[$c])){
			${$c} = $_POST[$c];
		} else {
			$jsonOutput['status'] = FALSE;
			$jsonOutput['errors'] = $jsonOutput['errors'] + 1;
			$jsonOutput['msg'] = 'One or more of the required fields not filled.';
		}
	}

	if($jsonOutput['errors'] == 0) {
		$query = mysql_query("SELECT * FROM `plus_giftcards_settings` WHERE `salon_id`=".mysql_real_escape_string($salon_id)." LIMIT 1;");
		$array = mysql_fetch_array($query);
		if (!empty($array)) {
			$loginId = $array['settings_auth_api_key'];
			$transactionKey = $array['settings_auth_password'];
			if (empty($loginId) || empty($transactionKey)) {
				$jsonOutput['status'] = FALSE;
				$jsonOutput['errors'] = 1;
				$jsonOutput['msg'] = 'Credentials not set.';
			}
		} else {
			$jsonOutput['status'] = FALSE;
			$jsonOutput['errors'] = 1;
			$jsonOutput['msg'] = 'Salon not found.';
		}
	}

	if($jsonOutput['errors'] == 0) {
		try {
			$creditcard = decode5t($creditcard);
			$product = (isset($product) && !empty($product)) ? $product : 'Giftcards';
			if ($mode == 'Production') {
				$payment = new AuthnetAIM($loginId, $transactionKey);
			} else {
				$payment = new AuthnetAIM($loginId, $transactionKey, true);
			}
			$payment->setTransaction($creditcard, $expiration, $total, $cvv, $invoice, $tax);
			$payment->setParameter("x_duplicate_window", 180);
			$payment->setParameter("x_cust_id", $user_id);
			$payment->setParameter("x_customer_ip", $_SERVER['REMOTE_ADDR']);
			$payment->setParameter("x_email", $email);
			$payment->setParameter("x_email_customer", FALSE);
			$payment->setParameter("x_first_name", $business_firstname);
			$payment->setParameter("x_last_name", $business_lastname);
			$payment->setParameter("x_address", $business_address);
			$payment->setParameter("x_city", $business_city);
			$payment->setParameter("x_state", $business_state);
			$payment->setParameter("x_zip", $business_zipcode);
			$payment->setParameter("x_phone", $business_telephone);
			$payment->setParameter("x_ship_to_first_name", $shipping_firstname);
			$payment->setParameter("x_ship_to_last_name", $shipping_lastname);
			$payment->setParameter("x_ship_to_address", $shipping_address);
			$payment->setParameter("x_ship_to_city", $shipping_city);
			$payment->setParameter("x_ship_to_state", $shipping_state);
			$payment->setParameter("x_ship_to_zip", $shipping_zipcode);
			$payment->setParameter("x_description", $product);
			$payment->process();
		 
			if ($payment->isApproved()) {
				// Get info from Authnet to store in the database
				$approval_code  = $payment->getAuthCode();
				$avs_result     = $payment->getAVSResponse();
				$cvv_result     = $payment->getCVVResponse();
				$transaction_id = $payment->getTransactionID();
					
				$jsonOutput['status'] = TRUE;
				$jsonOutput['errors'] = 0;
				$jsonOutput['msg'] = 'Transaction Passed.';
				$jsonOutput['approval_code'] = $approval_code;
				$jsonOutput['transaction_id'] = $transaction_id;
				$jsonOutput['avs_result'] = $avs_result;
				$jsonOutput['cvv_result'] = $cvv_result;
				
				// Do stuff with this. Most likely store it in a database.
				// Direct the user to a receipt or something similiar.
			} else if ($payment->isDeclined()) {
				// Get reason for the decline from the bank. This always says,
				// "This credit card has been declined". Not very useful.
				$reason = $payment->getResponseText();
				
				$jsonOutput['status'] = FALSE;
				$jsonOutput['msg'] = $reason;

		 
				// Politely tell the customer their card was declined
				// and to try a different form of payment.
			} else if ($payment->isError()) {
				// Get the error number so we can reference the Authnet
				// documentation and get an error description.
				$error_number  = $payment->getResponseSubcode();
				$error_message = $payment->getResponseText();
		 
				// OR
		 
				// Capture a detailed error message. No need to refer to the manual
				// with this one as it tells you everything the manual does.
				$full_error_message =  $payment->getResponseMessage();
		 
		 		$jsonOutput['status'] = FALSE;
				$jsonOutput['msg'] = $error_message;
				$jsonOutput['errors'] = $error_number;
				$jsonOutput['moreinfo'] = $error_message;
		 
				// We can tell what kind of error it is and handle it appropriately.
				if ($payment->isConfigError()) {
					// We misconfigured something on our end.
				} else if ($payment->isTempError()) {
					// Some kind of temporary error on Authorize.Net's end. 
					// It should work properly "soon".
				} else {
					// All other errors.
				}
		 
				// Report the error to someone who can investigate it
				// and hopefully fix it
		 
				// Notify the user of the error and request they contact
				// us for further assistance
			}
		} catch (AuthnetAIMException $e) {
			$jsonOutput['status'] = FALSE;
			$jsonOutput['msg'] = 'There was an error processing the transaction. Here is the error message: '.$e->__toString();
			$jsonOutput['errors'] = 1;
		}
	}
	logM($jsonOutput['msg']);
	echo json_encode($jsonOutput);
?>