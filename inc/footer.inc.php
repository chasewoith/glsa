<?php

/*
 +------------------------------------------------------------------------+
 | GREENLAKECHILDCARE.ORG                                                 |
 +------------------------------------------------------------------------+
 | footer.inc.php                                                         |
 | global footer include                                                  |
 |                                                                        |
 | andy hieb, dtekwest@dtek.net                                           |
 | 2005.11.09                                                             |
 |                                                                        |
 +------------------------------------------------------------------------+
 */

print "</div><!-- /content -->\n\n";

print "<div class=\"break\">&nbsp;</div>\n\n";

print "</div><!-- /middleblock -->\n\n";

print "<div id=\"bottomblock\"><div id=\"footer\">\n\n";

print "<a href=\"http://www.dtek.net/\" target=\"_blank\">";
print "<img id=\"footer-dtek\" \n";
print "src=\"" . $page['path_prefix'] . "img/dtek_footer.gif\" ";
print "width=\"30\" height=\"22\" alt=\"web site by Dtek Digital \n";
print "Media\" /></a>\n";
print "<img id=\"footer-glsa\" ";
print "src=\"" . $page['path_prefix'] . "img/glsa_footer.gif\" ";
print "width=\"71\" height=\"22\" alt=\"GLSA\" />\n";
print "<address>6415 1st Avenue NE, Seattle, WA 98115</address>\n";
print "<address>206.525.5909</address>\n";
print "<address><a href=\"http://www.greenlakchildare.org/\">";
print "greenlakechildcare.org</a></address>\n";
print "<div class=\"break\">&nbsp;</div>\n\n";

print "</div><!-- /footer --></div><!-- /bottomblock -->\n\n";

print "</div><!-- /container -->\n\n";

print "</body>\n";
print "</html>\n";

?>
