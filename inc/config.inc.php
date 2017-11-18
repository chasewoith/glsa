<?php

/*
 +------------------------------------------------------------------------+
 | GREENLAKECHILDCARE.ORG                                                 |
 +------------------------------------------------------------------------+
 | config.inc.php                                                         |
 | site configuration                                                     |
 |                                                                        |
 | andy hieb, andy@dtek.net                                               |
 | 2009.05.01                                                             |
 |                                                                        |
 +------------------------------------------------------------------------+
 */

//
// url and path settings
//

// see also: header.inc.php, 404.php

// Pair abej live site

$CONFIG['url']				= 'http://www.greenlakechildcare.org';
$CONFIG['path']				= '/usr/www/users/glsa/greenlakechildcare.org';
$CONFIG['path_rel']			= '/';
$CONFIG['path_inc']			= '/usr/www/users/glsa/greenlakechildcare.org/inc';


// Pair abej staging site
/*
$CONFIG['url']				= 'http://www.greenlakechildcare.org/dev';
$CONFIG['path']				= '/usr/www/users/glsa/greenlakechildcare.org';
$CONFIG['path_rel']			= '/dev/';
$CONFIG['path_inc']			= '/usr/www/users/glsa/greenlakechildcare.org/dev/inc';
*/

// Dtek wizzo staging server settings
/*$CONFIG['url']				= 'http://dev.greenlakechildcare.org';
$CONFIG['path']				= 'C:/wamp/www/glsa/greenlakechildcare.org';
$CONFIG['path_rel']			= '/glsa/greenlakechildcare.org/';
$CONFIG['path_inc']			= 'C:/wamp/www/glsa/greenlakechildcare.org/inc';
*/
$CONFIG['path_rel_media']	= 'media';


//
// form settings
//

//$CONFIG['contact'] = 'andy@dtek.net';
$CONFIG['contact'] = 'info@greenlakechildcare.org';

// user warning string to show in hidden anti-spam fields
$CONFIG['antispam_string'] = 'Please do NOT edit this text, or your message will not be sent. (This field is for spam protection.)';

// random private keys to help protect against CSRF
// if you want to change these keys, to avoid loss of legitimate form submissions just change one key within a 24-hour period and be sure that the older key is always listed as form_key1
$CONFIG['form_key1'] = 'b7 82 f4 c3 f4 8d d4 c0 dc de';
$CONFIG['form_key2'] = 'ba 69 82 f3 a6 8c 45 3c f8 93';

// time window for accepting form submissions, in seconds (helps protect against CSRF)
$CONFIG['form_expiration'] = 86400; // 24hrs


//
// meta tags
//

$CONFIG['meta_keywords'] = 'child care, childcare, greenlake, green lake, school-age, school age, before and after school care, before and after school childcare, enrichment program, after school activity, school age child care, affordable child care, North Seattle child care, North Seattle childcare, summer camp, summer day camp, summer activities, summer child care, camp, Seattle summer camp';
$CONFIG['meta_description'] = 'GLSA is a before and after school child care program in the Green Lake area of North Seattle providing care for school aged children from ages five to twelve.';


//
// site title
//

// individual page titles are appended to this string

$CONFIG['site_title'] = 'Green Lake School-Age Care Program';


//
// menu settings
//

// menu items will appear in the order they're listed here

// NOTE that the site Home page must have a handle of 'home'

//	handle => 
//	0 => menu link text
//	1 => menu link title
//	2 => url
//	3 => page title text
//	4 => page photo (if any), relative to the media directory

$CONFIG['menu'] = array
(
	'home' => array('home', 'GLSA Home Page', 'index.php', 'Welcome!', 'home.jpg'),
	'program' => array('our program', 'About Our Program', 'program.php', 'Our Program', '013.jpg'),
	'staff' => array('our staff', 'About Our Staff', 'staff.php', 'Our Staff', '013-2.jpg'),
	'faq' => array('FAQ', 'Frequently Asked Questions', 'faq.php', 'Frequently Asked Questions', '060.jpg'),
	'rates' => array('rates', 'Rates', 'rates.php', '2013-2014 School Year Rates', '008.jpg'),
	'calendar' => array('calendar', 'GLSA Calendar', 'calendar.php', 'Calendar for 2012-2013', '059.jpg'),
	'registration' => array('registration', 'Registration Information', 'registration.php', 'Registration Information', '012.jpg'),
	'summer' => array('summer day camp', 'Summer Day Camp', 'summer.php', 'Summer Day Camp', '053.jpg'),
	'contact' => array('contact', 'Contact GLSA', 'contact.php', 'Contact', '018.jpg'),
);



//
// quote settings
//

//	handle => 
//	0 => speaker
//	1 => quote text (XHTML) (use doublequotes so line breaks can be inlcuded)

$CONFIG['quotes'] = array
(
	'home' => array('Julian, age 7', "<p>When I think of GLSA I think I like it.  I just like to play and sometimes we go on fieldtrips.</p>"),
	'program' => array('Afsara, age 10', "<p>When I think of GLSA I think fun.</p>"),
	'staff' => array('Dain, age 5 5/6ths', "<p>GLSA is awesome!</p>"),
);

?>
