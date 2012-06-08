<?php
// Copyright 2010-2012 by Tyler Larson <devel@tlarson.com>. All rights reserved.  

// Drop-in replacement for php mail() which uses the PEAR Mail::send function
// instead, allowing for greater flexibility.

// Note that sending via SMTP requires you specify a "From:" header, or you'll
// get an error.

function pear_mail($to,$subject,$message,$headers="") {
	require_once("Mail.php");
	function split_headers( $headers )
	{
		$header_array = array();
		$lines = explode("\n",$headers);
		foreach ($lines as $line) {
			$kv = explode(":",$line,2);
			if (!empty($kv[1])) {
				$header_array[trim($kv[0])] = trim($kv[1]);
			}
		}
		return $header_array;
	}

	$mailer = Mail::factory('smtp',array('host'=>'127.0.0.1','port'=>'25'));
	$header_list = split_headers($headers);
	$header_list['Subject'] = $subject;
	return $mailer->send($to,$header_list,$message);
}

$headers = "From:sender@example.com\r\nContent-type:text/plain";

# Both functions take the exact same parameters making it very easy to 
# substitute one for the other

mail     ("recipient@example.com","Example Mail","Test Send",$headers);
pear_mail("recipient@example.com","Example Mail","Test Send",$headers);

