
//
// +---------------------------------------------------------------------+
// |GREENLAKECHILDCARE.ORG                                               |
// +---------------------------------------------------------------------+
// | postweaks.js                                                        |
// | JS positioning tweaks                                               |
// |                                                                     |
// | Dtek Digital Media, dtek.net                                        |
// | 2005.11.10                                                          |
// |                                                                     |
// +---------------------------------------------------------------------+
//

// try to get some details about this userAgent

// detection script taken from "The Ultimate JavaScript Client Sniffer, 
// Version 3.03".  JS UA detection is deprecated, but this script is really
// just for design vanity. failure of entire script would be livable.

var agt = navigator.userAgent.toLowerCase();

var is_major = parseInt(navigator.appVersion);
var is_minor = parseFloat(navigator.appVersion);

var is_ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
var is_ie3    = (is_ie && (is_major < 4));
var is_ie4    = (is_ie && (is_major == 4) && (agt.indexOf("msie 4") != -1) );
var is_ie5up  = (is_ie && !is_ie3 && !is_ie4);

var is_mac    = (agt.indexOf("mac") != -1);


// get the Y position of an element

function findPosY(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}


// get the viewport width

function getViewPortWidth()
{
	if (self.innerWidth)
	{ 
		// all except Explorer
		return self.innerWidth;
	} 
	else if (document.documentElement && document.documentElement.clientWidth)
	{
		// Explorer 6 Strict Mode
		return document.documentElement.clientWidth;
	}
	else if (document.body)
	{
		// other Explorers
		return document.body.clientWidth;
	}
}


// get the viewport height

function getViewPortHeight()
{
	if (self.innerHeight)
	{ 
		// all except Explorer
		return self.innerHeight;
	} 
	else if (document.documentElement && document.documentElement.clientHeight)
	{
		// Explorer 6 Strict Mode
		return document.documentElement.clientHeight;
	}
	else if (document.body)
	{
		// other Explorers
		return document.body.clientHeight;
	}
}

function do_postweaks()
{
	// target positioning values
	
	var min_width_container = 760;
	var min_height_middleblock = 300;
	var min_height_quoteblock = 174;

	// get current positioning objects and values
	
	if (document.getElementById)
	{
		var viewPortWidth = getViewPortWidth();
		var viewPortHeight = getViewPortHeight();

		var containerElement = document.getElementById('container');
		var middleblockElement = document.getElementById('middleblock');
		var middleblockElementHeight = middleblockElement.clientHeight;
		var quoteblockElement = document.getElementById('quoteblock');
		if (quoteblockElement)  var quoteblockElementHeight = quoteblockElement.clientHeight;
		var bottomblockElement = document.getElementById('bottomblock');
		var bottomblockY = findPosY(bottomblockElement);
		var footerElement = document.getElementById('footer');
		var footerHeight = footerElement.clientHeight;
		var visibleHeight = viewPortHeight - bottomblockY;

		//alert("initial middleblockElementHeight: " + middleblockElementHeight + ", min_height_middleblock: " + min_height_middleblock);
		//alert("initial quoteblockElementHeight: " + quoteblockElementHeight + ", min_height_quoteblock: " + min_height_quoteblock);
		//alert("viewPortHeight: " + viewPortHeight + "\nbottomblockY: " + bottomblockY + "\nfooterHeight: " + footerHeight + "\nvisibleHeight: " + visibleHeight);

		// do the repositioning

		// emulate min-width for IE

		if (is_ie5up)
		{
			//alert("setting min-width");
			if (viewPortWidth < min_width_container)
			{
				containerElement.style.width = min_width_container + 'px';
			}
			else
			{
				containerElement.style.width = 'auto';
			}
		}

		// emulate min-height for Win/IE
		if (is_ie5up && !is_mac)
		{
			//alert("setting min-height");
			if (middleblockElementHeight < min_height_middleblock)
			{
				middleblockElement.style.height = min_height_middleblock + 'px';
			}

			if (quoteblockElement)
			{
				if (quoteblockElementHeight < min_height_quoteblock)
				{
					quoteblockElement.style.height = min_height_quoteblock + 'px';
				}
			}
		}

		// set bottomblock element height for all except Mac/IE
		//
		// essentially, this code sets 1 of 2 height values for the 
		// bottomblock element, depending on where the end of the footer falls
		// relative to the viewport height.  (if this function is ignored or
		// dropped in the future, the bottomblock height would just be kept at
		// the value specified via CSS, which would add a little unnecessary 
		// scrolling, but otherwise be fine...)
		//
		// "visibleHeight" refers to the space between the top of bottomBlock
		// and the bottom of the viewport.
		// if visibleHeight is shorter than footerHeight, set bottomblockHeight 
		// to footerHeight.
		// otherwise, set bottomblockHeight to visibleHeight.

		if (!(is_mac && is_ie))
		{
			//alert("setting bottomblock height");
			if (visibleHeight < footerHeight)
			{
				bottomblockElement.style.height = footerHeight + 'px';
			}
			else
			{
				bottomblockElement.style.height = visibleHeight + 'px';
			}
		}

		//alert("final middleblockElementHeight: " + middleblockElementHeight + ", min_height_middleblock: " + min_height_middleblock);
		//alert("final quoteblockElementHeight: " + quoteblockElementHeight + ", min_height_quoteblock: " + min_height_quoteblock);
	}
}

window.onload = function()
{ 
	do_postweaks();
}
window.onresize = function()
{
	do_postweaks();
}
