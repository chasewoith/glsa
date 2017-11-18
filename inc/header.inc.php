<?php

/*
 +------------------------------------------------------------------------+
 | GREENLAKECHILDCARE.ORG                                                 |
 +------------------------------------------------------------------------+
 | header.inc.php                                                         |
 | global header include                                                  |
 |                                                                        |
 | andy hieb, dtekwest@dtek.net                                           |
 | 2007.04.13                                                             |
 |                                                                        |
 +------------------------------------------------------------------------+
 */

// paths
//see also: config.inc.php, 404.php

$path_inc = '/usr/www/users/glsa/greenlakechildcare.org/inc';  // Pair abej live site
// $path_inc = '/usr/www/users/glsa/develop/inc';  // Pair abej staging site
// $path_inc = '/WWW/greenlakechildcare.org/inc';  // old CBC Computer server settings
// $path_inc = '/home/dtekwest/public_html/glsa/inc';  // egyptian staging server


// certain versions of Mac/IE needed this header b/c hosting4 sends PHP 
// files with the UTF-8 charset, different than the charset specified by 
// meta tag below, which confuses that browser.  (and setting the meta tag 
// to UTF-8 did not seem to fix the problem either...)
// more info: http://www.l-c-n.com/IE5tests/misc/
header('Content-Type: text/html; charset=iso-8859-1');
 
//
// variables set in the include'ing file:
//
//	$this_dir_offset (optional): used to determine the path_prefix. 
//	acceptable values are "abs" for absolute, or an integer number of 
//	directories away from the site root.
//
$this_dir_offset = isset($this_dir_offset) ? $this_dir_offset : '';

//
//	$this_title (optional): for pages not listed in $CONFIG['menu'], this 
//	variable is a string to use for the content title, and to append to
//	$CONFIG['site_title'] to create the page title
//
$this_title = isset($this_title) ? $this_title : '';

//
//	$this_photo (optional): for pages not listed in $CONFIG['menu'], this 
//	variable is a string file name of a photo to use for this page (in the 
//	$CONFIG['path_rel_media'] directory)
//
$this_photo = isset($this_photo) ? $this_photo : '';

//
// +----------------------------------------------------------------------+
//

//
// get global includes
//

include($path_inc . '/config.inc.php');
include($path_inc . '/functions.inc.php');


//
// fill page variables
//

$page = array();

// get page self
$page['self'] = get_self();

// get the page path prefix 
$page['path_prefix'] = get_path($this_dir_offset);

// get the page menu handle
$page['menu_handle'] = get_menu($page['self']);

// get the page title
$page['page_title'] = get_pagetitle($page, $this_title);

// get the content title
$page['content_title'] = get_contenttitle($page, $this_title);

// get the page photo 
$page['photo'] = get_photo($page, $this_photo);


//
// print HTML
//

print "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n";
print "     \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n\n";

print "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n";
print "<head>\n";
print "<title>" . $page['page_title'] . "</title>\n";
print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />\n";
print "<meta name=\"keywords\" content=\"" . $CONFIG['meta_keywords'] . "\" />\n";
print "<meta name=\"description\" content=\"" . $CONFIG['meta_description'] . "\" />\n";
print "<style type=\"text/css\">@import \"" . $page['path_prefix'] . "inc/main.css\";</style>\n";
print "<style type=\"text/css\" media=\"print\">@import \"" . $page['path_prefix'] . "inc/print.css\";</style>\n";
print "<script language=\"javascript\" type=\"text/javascript\" src=\"" . $page['path_prefix'] . "inc/postweaks.js\"></script>\n";
print "<link rel=\"shortcut icon\" href=\"" . $page['path_prefix'] . "favicon.ico\" type=\"image/x-icon\" />\n";
print "</head>\n\n";

print "<body>\n\n";

// print "<!--\n";
// print_r($page);
// print "-->\n";

print "<div id=\"container\">\n\n";

print "<div class=\"skiplink\"><a href=\"#contentheader\">Skip to page content</a></div>\n\n";

print "<div id=\"topblock\"><div id=\"header\">\n";
print "<h1>Green Lake School-Age Care Program</h1>\n\n";

print "<div id=\"tagline\"></div>\n\n";

// site menu
$output_menu = print_menu($page);
print $output_menu;

print "</div><!-- /header --></div><!-- /topblock -->\n\n";

print "<div id=\"middleblock\">\n";

if ($page['menu_handle'] == 'home')
{
	// home logo and quote
	$output_logoblock = print_logoblock($page);
	print $output_logoblock;
	
	print "<div id=\"contenthome\">\n\n";

	print "<h2 id=\"contentheader\">" . $page['content_title'] . "</h2>\n\n";

	// home photo
	$output_photoblock = print_photoblock($page);
	print $output_photoblock;
}
else
{
	print "<div id=\"content\">\n\n";

	// secondary page logo
	$output_logoblock = print_logoblock($page);
	print $output_logoblock;

	// secondary page photo
	$output_photoblock = print_photoblock($page);
	print $output_photoblock;

	print "<h2 id=\"contentheader\">" . $page['content_title'] . "</h2>\n";
}

?>
