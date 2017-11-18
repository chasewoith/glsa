<?php

/*
 +------------------------------------------------------------------------+
 | GREENLAKECHILDCARE.ORG                                                 |
 +------------------------------------------------------------------------+
 | functions.inc.php                                                      |
 | common PHP functions                                                   |
 |                                                                        |
 | andy hieb, andy@dtek.net                                               |
 | 2009.05.01                                                             |
 |                                                                        |
 +------------------------------------------------------------------------+
 */

//functions:
//	get_self
//	get_path
//	get_menu
//	get_pagetitle
//	get_contenttitle
//	get_photo
//	print_menu
//	print_logoblock
//	print_photoblock
//	sanitize
//	tokenize
//	validate_token

//
// determine the current page file name
// return a path relative to the site URL
//
 
function get_self()  
{
	global $CONFIG;
	
	// trim the relative path
	$self = substr($_SERVER['PHP_SELF'],strlen($CONFIG['path_rel']));

	return $self;
}


//
// determine the path prefix for the current page
// return an absolute path or path relative to the site URL
//

function get_path($dir_offset)  
{
	global $CONFIG;

	$path = "";

	if (empty($dir_offset))
	{
		$path = "";
	}
	// a relative path should be given as an integer
	else if (is_int($dir_offset))
	{
		for ($i=0; $i<$dir_offset; $i++)
		{
			$path .= "../";
		}
	}
	// absolute paths should be specified as "abs", but we default here...
	else
	{
		$path = $CONFIG['url'] . $CONFIG['path_rel'];
	}
	
	return $path;
}


//
// get the menu handle for this page, if any
// return a string
//
 
function get_menu($self)
{
	global $CONFIG;

	$handle = "";

	foreach($CONFIG['menu'] as $this_handle => $this_page)
	{
		if (in_array($self, $this_page))  
		{
			$handle = $this_handle;
		}
	}

	return $handle;
}


//
// get the page title for the current page
// return a string
//
 
function get_pagetitle($page, $this_title)
{
	global $CONFIG;

	$title = $CONFIG['site_title'];

	if (is_array($CONFIG['menu'][$page['menu_handle']]))
	{
		if (!empty($CONFIG['menu'][$page['menu_handle']][3]))
		{
			$title .= " : " . $CONFIG['menu'][$page['menu_handle']][3];
		}
	}
	else if (!empty($this_title))
	{
		$title .= " : " . $this_title;
	}

	return $title;
}


//
// get the content title for the current page
// return a string
//
 
function get_contenttitle($page, $this_title)
{
	global $CONFIG;

	$title = $CONFIG['site_title'];

	if (is_array($CONFIG['menu'][$page['menu_handle']]))
	{
		if (!empty($CONFIG['menu'][$page['menu_handle']][3]))
		{
			$title = $CONFIG['menu'][$page['menu_handle']][3];
		}
	}
	else if (!empty($this_title))
	{
		$title = $this_title;
	}

	return $title;
}


// 
// get the photo for this page, if any
// return a string file name
//

function get_photo($page, $this_photo)
{
	global $CONFIG;

	$img = "";
	$img_name = "";
	$img_path = $CONFIG['path'] . "/" . $CONFIG['path_rel_media'] . "/";

	if (is_array($CONFIG['menu'][$page['menu_handle']]))
	{
		$img_name = $CONFIG['menu'][$page['menu_handle']][4];
	}
	else if (!empty($this_photo))
	{
		$img_name = $this_photo;
	}
	
	if (!empty($img_name) && file_exists($img_path.$img_name))
	{
		$img = $page['path_prefix'] . $CONFIG['path_rel_media'] . '/';
		$img .= $img_name;
	}

	return $img;
}


//
// print the menu
//

function print_menu($page)
{
	global $CONFIG;

	$output = "";

	$output .= "<div id=\"menu\">\n";
	$output .= "<ul>\n";

	$n = 0; // we need a special class for our first link
	foreach ($CONFIG['menu'] as $this_handle => $this_page)
	{
		$this_text	= $this_page[0];
		$this_title	= $this_page[1];
		$this_uri	= $page['path_prefix'] . $this_page[2];
		if ($n == 0)
		{
			$this_style = ($this_page[2] == $page['self']) ? "class=\"firstsel\" " : "class=\"first\" ";
		}
		else
		{
			$this_style = ($this_page[2] == $page['self']) ? "class=\"sel\" " : "";
		}
		
		$output .= "<li><a " . $this_style . "href=\"" . $this_uri . "\" ";
		$output .= "title=\"" . $this_title . "\">" . $this_text;
		$output .= "</a></li>\n";

		$n = 1;
	}
	$output .= "</ul>\n";
	$output .= "</div>\n\n";

	return $output;
}


//
// print the logoblock
//

function print_logoblock($page)
{
	global $CONFIG;

	$output = "";

	if ($page['menu_handle'] == 'home')
	{
		$output .= "<div id=\"logohomeblock\">\n";
		$output .= "<img id=\"logohome\" src=\"" . $page['path_prefix'];
		$output .= "img/logo_home.jpg\" alt=\"\" />\n";
		$output .= "<div id=\"quoteblock\">\n";
		$output .= "<blockquote>\n";
		$output .= $CONFIG['quotes']['home'][1] . "\n";
		$output .= "<cite>- " . $CONFIG['quotes']['home'][0] . "</cite>\n";
		$output .= "</blockquote>\n";
		$output .= "</div>\n";
		$output .= "</div>\n\n";
	}
	else
	{
		$output .= "<div id=\"logospacer\">&nbsp;</div>\n\n";
	}

	return $output;
}


//
// print the photoblock
//

function print_photoblock($page)
{
	global $CONFIG;

	$menu_handle = $page['menu_handle'];
	$quote = $CONFIG['quotes'][$menu_handle];

	$output = "";

	if (empty($page['photo']))  return $output;

	$div_class = ($menu_handle == 'home') ? "photoblockhome" : "photoblock";
	$img_class = $quote ? 'class="quoted" ' : "";

	$output .= "<div class=\"" . $div_class . "\">\n";
	$output .= "<img " . $img_class . "src=\"" . $page['photo'] . "\" alt=\"\" />\n";
	if ($quote && $menu_handle != 'home')
	{
		$output .= "<div id=\"quoteblock\">\n";
		$output .= "<blockquote>\n";
		$output .= $quote[1] . "\n";
		$output .= "<cite>- " . $quote[0] . "</cite>\n";
		$output .= "</blockquote>\n";
		$output .= "</div>\n\n";
	}
	$output .= "</div>\n\n";

	return $output;
}


//
// sanitize form data
//
// @param string $str
// @return string
//

function sanitize($str)
{
	return stripslashes(htmlspecialchars($str));
}


//
// create a form token
//
// @param int $timestamp
// @param string $form_key
// @return string
//

function tokenize($form_key,$timestamp)
{
	return (md5($form_key.$_SERVER['REMOTE_ADDR'].$timestamp));
}


//
// validate a form timestamp & token
//
// @param int $timestamp
// @param string $token
// @return boolean
//

function validate_token($token,$timestamp)
{
	global $CONFIG;
	if (date("U") - $timestamp > $CONFIG['form_expiration'])  return false;
	return ($token == tokenize($CONFIG['form_key1'],$timestamp) || $token == tokenize($CONFIG['form_key2'],$timestamp));
}

?>
