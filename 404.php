<?php

/*
 +------------------------------------------------------------------------+
 | GREENLAKECHILDCARE.ORG                                                 |
 +------------------------------------------------------------------------+
 | 404.php                                                                |
 | 404 error page                                                         |
 |                                                                        |
 | andy hieb, dtekwest@dtek.net                                           |
 | 2005.11.10                                                             |
 |                                                                        |
 +------------------------------------------------------------------------+
 */

// see also: config.inc.php, header.inc.php

// Pair abej production server
$path_inc = '/usr/www/users/glsa/greenlakechildcare.org/inc';

// CBC Computer hosting4 production server settings
// $path_inc = '/WWW/greenlakechildcare.org/inc';

// Dtek egyptian staging server settings
// $path_inc = '/home/arh1/public_html/glsa/inc';

$this_dir_offset = 'abs';
$this_title = 'Error: File Not Found';

include($path_inc . '/header.inc.php');
 
?>

<p>Sorry, the page or file you requested was not found.</p>

<p>If you've reached this page from a broken link, please <a 
href="<?php echo $CONFIG['url'].$CONFIG['path_rel']; ?>contact.php">contact 
us</a> to let us know.</p>

<?

include($path_inc . '/footer.inc.php');
 
?>
