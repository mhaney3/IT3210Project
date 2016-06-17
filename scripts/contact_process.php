<?php

/*

	FormContact 1.1 Plus
	By WarkenSoft Productions
	Copyright 2008

	Optional Variables used by the script
		$destination_email
		$followup_url
		$required
		$subject

	Variables Required for Autoresponder
		$autoresponse_file
		$email

*/

################################## BEGIN EDITING HERE ##################################
# You will want to configure the following variables to suit your needs

	# This variable will be used if the destination_email variable is not set on the form.
	$default_destination_email = "your@emailaddress.here";

	# The name of your website
	$site_name = "Your Website's Name";

	# The default followup URL in case one isn't supplied on the form.
	# your form I've supplied does have a link to the "thanks.html" document
	$default_followup_url = "http://www.your site name.com/thanks.html";

	# The value of this variable will be displayed if the followup_url is not specified.
	$default_response = "Thank you for contacting us.<br>We will respond to your email as soon as possible";

################ YOU SHOULDN'T NEED TO EDIT ANYTHING BEYOND THIS POINT ################


function error($code = "No Error Specified", $display = false, $terminate = false)
{
	global $default_destination_email;
	mail("$default_destination_email","FORM_CONTACT Script Error","Warning!
The FORM_CONTACT script on your site generated the following error:
$code","From: $default_destination_email");

	if($display) echo $code;

	if($terminate) exit;
}



if($_POST)
{
	if(!$_POST['destination_email'])
	{
		$_POST['destination_email'] = $default_destination_email;
	}
	if($_POST['destination_email'] == "your@emailaddress.here")
	{
		error("Script Error:  This form processing script has not yet been correctly set up.<br>Please contact the webmaster.", true, true);
	}

	$arrexclude = array("Submit", "destination_email", "required", "followup_url");
	if($_POST['required']){
		$a_required = explode(" ",$_POST['required']);
		for ($n = 0; $n < count($a_required); $n++)
		{
			if(!$_POST[$a_required[$n]])
			{
				echo "You must enter a value for: $a_required[$n]";
				exit;
			}
		}
	}

	$msg = "";
	while (list($key, $value) = each($_POST)) {
		$exclude = false;
		for ($n = 0; $n <= count($arrexclude); $n++) {
			if ($key == $arrexclude[$n]) {
				$exclude = true;
			}
		}

		if ($exclude == false) {
			$key = strtoupper($key);
			$msg .= "\n\n".$key.": \n";
			if (is_array($value)) {
				$msg .= implode("\n", $value);
			} else {
				$msg .= $value;
			}
		}
	}

	$msg = trim(stripslashes($msg));

	$badStrings = array("Content-Type:",
	                     "MIME-Version:",
	                     "Content-Transfer-Encoding:",
	                     "bcc:",
	                     "cc:");

	foreach($badStrings as $v2){
	    if(strpos(strtolower($_POST['destination_email']), strtolower($v2)) !== false){
	        error("Website Form Hack Attempt on Destination Email: '$destination_email'");
	        header("HTTP/1.0 403 Forbidden");
	            exit;
	    }
	    if(strpos(strtolower($_POST['subject']), strtolower($v2)) !== false){
	        error("Website Form Hack Attempt on Subject: '$subject'");
	        header("HTTP/1.0 403 Forbidden");
	            exit;
	    }
	    if(strpos(strtolower($_POST['email']), strtolower($v2)) !== false){
	        error("Website Form Hack Attempt on Email: '$email'");
	        header("HTTP/1.0 403 Forbidden");
	            exit;
	    }
	    if(strpos(strtolower($_POST['response_email']), strtolower($v2)) !== false){
	        error("Website Form Hack Attempt on Response_email: '$response_email'");
	        header("HTTP/1.0 403 Forbidden");
	            exit;
	    }
	    if(strpos(strtolower($_POST['response_name']), strtolower($v2)) !== false){
	        error("Website Form Hack Attempt on Response_name: '$response_name'");
	        header("HTTP/1.0 403 Forbidden");
	            exit;
	    }
	}

	$destination_email = eregi_replace("[\r|\n]*", "", $_POST['destination_email']);
	$email = eregi_replace("[\r|\n]*", "", $_POST['email']);
	$subject = eregi_replace("[\r|\n]*", "", $_POST['subject']);
	$response_email = eregi_replace("[\r|\n]*", "", $_POST['response_email']);
	$response_name = eregi_replace("[\r|\n]*", "", $_POST['response_name']);


	mail("$destination_email","Website Feedback Form: $subject","$msg\n","FROM: $destination_email");

	$autoresponse_file = eregi_replace("[\r|\n]*", "", $_POST['autoresponse_file']);
	if($autoresponse_file && $email)
	{
		if(strpos(" $autoresponse_file", "..")) error("AutoResponse File Hack Detected.  Attempted to download: '$autoresponse_file'", false, true);


		if(@$file = implode(file("autoresponse/$autoresponse_file"), ""))
		{
			$fp = 0;
			while(strpos(" $file", "{~", $fp))
			{
				$fp = strpos($file, "{~", $fp);
				$ep = strpos($file, "~}", $fp)+2;
				$temp = substr($file, $fp, $ep-$fp);
				$temp2 = substr($temp, 2, strlen($temp)-4);

				if(strpos("-$temp", " "))
				{
					error("You may not have spaces in variable names in the autoresponse files.
	The file containing the error is: $autoresponse_file");
					break;
				}

				if(strpos("-$temp", "\n"))
				{
					error("You may not have newline characters in the autoresponse files.
	The file containing the error is: $autoresponse_file");
					break;
				}

				$file = str_replace($temp, $_POST[$temp2], $file);
			}
			mail("$email","$site_name: Automated Response","$file","From: $destination_email");
		}
		else
		{
			error("The following form asked for a non-existent autoresponse to be sent:\n$HTTP_REFERER\n\nThe autoresponse file that it asked for was:\n$autoresponse_file");
		}
	}

	if(!$_POST['followup_url']) $_POST['followup_url'] = $default_followup_url;
	if($_POST['followup_url'] != "http://www.your site name.com/thanks.html")
		header("Location: " . $_POST['followup_url']);
	else
		echo "$default_response";

}
?>
