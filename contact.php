<?php

/*
 +------------------------------------------------------------------------+
 | GREENLAKECHILDCARE.ORG                                                 |
 +------------------------------------------------------------------------+
 | contact.php                                                            |
 | Contact page                                                           |
 |                                                                        |
 | andy hieb, andy@dtek.net                                               |
 | 2009.05.01                                                             |
 |                                                                        |
 +------------------------------------------------------------------------+
 */

include 'inc/header.inc.php';

?>

<p><strong>Green Lake School-Age Care Program</strong><br />
6415 1<sup>st</sup> Avenue NE, Seattle, WA 98115<br />
206.525.5909<br />
<a href="http://www.greenlakechildcare.org">greenlakechildcare.org</a></p>
 

<h3>Send Us An Email</h3>

<?php
 
//
// initialize variables
//

$error_msgs = array();
$error_fields = array();
$error_intro = '';

$now_timestamp = date('U');

$p_timestamp = array_key_exists('timestamp', $_POST) ? $_POST['timestamp'] : $now_timestamp;
$p_token = array_key_exists('token', $_POST) ? $_POST['token'] : tokenize($CONFIG['form_key1'],$now_timestamp);
$p_submitted = array_key_exists('submitted', $_POST) ? $_POST['submitted'] : '';
$p_name = array_key_exists('name', $_POST) ? $_POST['name'] : '';
$p_email = array_key_exists('email', $_POST) ? $_POST['email'] : '';
$p_fielde = array_key_exists('fielde', $_POST) ? $_POST['fielde'] : '';
$p_phone = array_key_exists('phone', $_POST) ? $_POST['phone'] : '';
$p_message = array_key_exists('message', $_POST) ? $_POST['message'] : '';


//
// if form has been submitted, check for errors
//

if ($p_submitted == 'true')
{
	$error_msgs = array();
	$error_intro = "<strong>Sorry, your email was not sent!</strong><br />";
	$error_intro .= "Please correct the following problems, then click the ";
	$error_intro .= "\"Send\" button again:";
	
	if (empty($p_name))
	{
		$error_msgs[] = "Please enter your name";
		$error_fields[] = 'name';
	}
	if (empty($p_fielde))
	{
		$error_msgs[] = "Please enter your email address";
		$error_fields[] = 'fielde';
	}
	if (empty($p_message))
	{
		$error_msgs[] = "Please enter your message";
		$error_fields[] = 'message';
	}
}


//
// print form if it hasn't yet been submitted, or there are errors
//

if ($p_submitted != 'true' || !empty($error_msgs))
{
	$form_timestamp = sanitize($p_timestamp);
	$form_token = sanitize($p_token);
	$form_name = sanitize($p_name);
	$form_email = ($p_submitted != 'true') ? $CONFIG['antispam_string'] : sanitize($p_email);
	$form_fielde = sanitize($p_fielde);
	$form_phone = sanitize($p_phone);
	$form_message = sanitize($p_message);

	if (!empty($error_msgs))
	{
		print "<div class=\"error\">\n";
		print "<p>" . $error_intro . "</p>\n";
		print "<ul>\n";
		foreach ($error_msgs as $msg)
		{
			print "<li>" . $msg . "</li>\n";
		}
		print "</ul>\n";
		print "</div>\n\n";
	}

?>

<form class="contact" id="contact" action="<?php print $_SERVER['PHP_SELF']; ?>" method="post">

<p><label for="name">Name</label><br />
<input class="text<?php if (in_array('name',$error_fields)) print ' error'; ?>" type="text" id="name" name="name" value="<?php echo $form_name ?>" /></p>

<p><label for="email">Email</label><br />
<input class="text<?php if (in_array('fielde',$error_fields)) print ' error'; ?>" type="text" id="fielde" name="fielde" value="<?php echo $form_fielde ?>" />
<input class="text" type="text" id="email" name="email" value="<?php echo $form_email ?>" /></p>

<p><label for="phone">Phone (optional)</label><br />
<input class="text" type="text" id="phone" name="phone" value="<?php echo $form_phone ?>" /></p>

<p><label for="message">Message</label><br />
<textarea<?php if (in_array('message',$error_fields)) print ' class="error"'; ?> id="message" name="message" rows="5" cols="20"><?php echo $form_message ?></textarea></p>

<p>
<input type="hidden" name="timestamp" value="<?php echo $form_timestamp ?>" />
<input type="hidden" name="token" value="<?php echo $form_token ?>" />
<input type="hidden" name="submitted" value="true" />
<input type="submit" class="button" value="Send" id="submit" />
</p>

</form>

<?php
}

//
// form is submitted: display confirmation message
//

else
{

  // to submit the form:
  // 1) token must validate
  // 2) bogus, anti-spam fields must contain the initial string
  if (validate_token($p_token,$p_timestamp) && $p_email == $CONFIG['antispam_string'])
  {
    // send email

    $mail_date_sent = date("Y.m.d");
    $mail_to = $CONFIG['contact'];
     	
    // check all data used in mail headers for control characters
    // http://www.pair.com/support/notices/email-form-vuln.html
    if (preg_match("/[\\000-\\037]/",$p_name.$p_fielde))
    {
      $mail_from = "GLSA Contact Form <info@greenlakechildcare.org>";
    }
    else
    {
      $mail_from = $p_name . " <" . $p_fielde . ">";
    }

    $mail_subject = "GLSA web site contact";

    $mail_body = "The GLSA web site contact form was submitted \n";
    $mail_body .= "on $mail_date_sent by " . $p_name . ". \n\n";

    $mail_body .= "Contact form: " . $CONFIG['url'] . $CONFIG['path_rel'];
    $mail_body .= $page['self'] . "\n\n";

    $mail_body .= "Name: " . $p_name . "\n";
    $mail_body .= "Email: " . $p_fielde . "\n";
    if (!empty($p_phone))  $mail_body .= "Phone: " . $p_phone . "\n";

    $mail_body .= "\nMessage:\n" . $p_message . "\n";

    mail ($mail_to, $mail_subject, $mail_body, "From: $mail_from");

/*
    print "<!-- \n";
    print "mail_to: $mail_to \n";
    print "mail_subject: $mail_subject \n";
    print "mail_body: $mail_body \n";
    print "mail_headers: From: $mail_from \n";
    print "-->\n";
*/
  }
?>

<p class="strong">Thank you for contacting us.</p>

<p class="strong">We will reply as soon as possible.</p>

<?php

}

// print "form timestamp: $p_timestamp <br />\n";
// print "current timestamp: ".$now_timestamp." <br />\n";
// print "token: $p_token <br />\n";
// if (validate_token($p_token,$p_timestamp))  print "valid form <br />\n";
// else  print "invalid form <br />\n";

include 'inc/footer.inc.php';
 
?>
