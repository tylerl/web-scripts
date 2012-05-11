<?php
########################################################
# Templated PHP Mailer
# Copyright 2010-2012 TL Tech Services LLC.
#
# Permission is hereby granted to deal with this software in accordance with
# the provisions of the MIT License. A copy of this license is available at
# the following Location:
#   http://www.opensource.org/licenses/mit-license.html
#
########################################################
unset_magic_quotes();
$form = $_REQUEST;

########################################################
# Edit this part to customize the script
########################################################

# Send from this address
$from = 'donotreply@example.com';
	
# A message will be sent to each of these addresses (you obviously have to have at least 1)
$to_list = array(
	"example@example.com",
);

# Subject of the email to be sent
$subject = "Website Email from {$_SERVER['HTTP_HOST']} at ".date("r");


##########
# SETTINGS
##########
# Where to redirect to if successful (must go SOMEWHERE)
$success_page = "/";
# Where to redirect to if not successful. Remove this setting to see the error message.
$failed_page = ""; 
# Where to redirect to if spam check failed
$spam_page = "/mail_failed.html"; #ignored if spam check is disabled

$spam_field = ""; # empty to disable spam check
$spam_value = ""; # if form variable $spam_field contains $spam_value, then check passes

#####################################################
# EMAIL BODY START
ob_start(); ?>

<p><b>Your Name:</b> {{name}}
<p><b>Your Phone Number:</b> {{phone}}
<p><b>Your Email:</b> {{email}}

<p><b>Comments:</b><br/>
{{comments}}

--
Sent from: {{FORM_URL}}

<?php
#####################################################
# EMAIL BODY END
$body = ob_get_clean();

######################################################
# SET TEMPLATE VARIABLES
$form["FORM_URL"] = $_SERVER['HTTP_REFERER'];
//$form["notes"] = @join(", ", $_REQUEST["notes"]);
######################################################

##############################################################
## No need to modify beyond this point
##############################################################

foreach($form as $key=>$val) {
	$body = str_replace("{{".$key."}}",htmlspecialchars($val),$body);	
}

# Only do spam check if we have a field to check and a page to redir to
if (!empty($spam_field) && !empty($spam_page)) {
	if ($_REQUEST[$spam_field] != $spam_value) {
		header("location:$spam_page");
		exit;
	}
}


# Set mail headers
$boundary = md5(time());
$body = "--$boundary\nContent-type: text/plain\r\n\r\n".reverse_htmlentities(strip_tags($body))."\r\n--$boundary\r\nContent-type:text/html\r\n\r\n".$body;
$headers = "From: $from\nContent-type: multipart/alternative; boundary=\"$boundary\"\n";

# send mails
$OK = false;
if (empty($failed_page)) {
	// Show error on failure. This path is meant for debugging, not for production.
	@error_reporting(E_ALL);
	@ini_set("display_errors", 1);

	foreach ($to_list as $to) {
		if (!mail($to,$subject,$body,$headers)) {
			echo "Could not send email.";
			exit;
		}
	}
	$OK = true;
} else {
	// redirect to failed_page on error
	foreach ($to_list as $to) {
		if (@mail($to,$subject,$body,$headers)) {
			$OK = true;
		}
	}
}

if ($OK) {
	header("location:$success_page");
} else {
	header("location:$failed_page");	
}

return;

########################################################################
# undo damage done by magic quotes if it was set
function unset_magic_quotes() {
	if (get_magic_quotes_gpc()) {
		$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
		while (list($key, $val) = each($process)) {
			foreach ($val as $k => $v) {
				unset($process[$key][$k]);
				if (is_array($v)) {
					$process[$key][stripslashes($k)] = $v;
					$process[] = &$process[$key][stripslashes($k)];
				} else {
					$process[$key][stripslashes($k)] = stripslashes($v);
				}
			}
		}
		unset($process);
	}
}
########################################################################
# unescape HTML entities
function reverse_htmlentities($mixed)
{
    $htmltable = get_html_translation_table(HTML_ENTITIES);
    foreach($htmltable as $key => $value)
    {
        $mixed = ereg_replace(addslashes($value),$key,$mixed);
    }
    return $mixed;
}
?>

