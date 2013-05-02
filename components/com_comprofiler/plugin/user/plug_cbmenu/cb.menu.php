<?php
/**
* Menu and Status functions handling the CB tab api
* @version $Id: cb.menu.php 609 2006-12-13 17:30:15Z beat $
* @package Community Builder
* @subpackage cb.menu.php
* @author Beat
* @copyright (C) 2005-2006 Beat, www.joomlapolis.com and Lightning MultiCom SA, 1009 Pully, Switzerland
* @license cbMenu, cbMenuHandler, cbMenuBest, cbBestHandler, cbMenuTab, cbTabHandler: Lightning Proprietary. See licence. Rest: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

$_PLUGINS->registerFunction( 'onPrepareMenus', 'prepareMenu','getMenuTab' );
$_PLUGINS->registerFunction( 'onPrepareMenus', 'prepareStatus','getStatusTab' );


/***** Generic Menu API: ******/

/**
* Module database table class for menu storage
* @package Community Builder
* @subpackage menu CB core module : Generic Menu API
* @author Beat
* @copyright (C) Lightning MultiCom SA Switzerland
* @license Lightning Proprietary. See licence.
*/
class cbMenu {			// later: extends mosMenu
	/** @var int Primary key */
	var $id			= null;
	/** @var string Name/title of link */
	var $name		= null;
	/** @var string URI link */
	var $link		= null;
	// /** @var int */
	// var $type	= "url";
	/* cb extensions: */
	/** @var string */
	var $target		= null;
	/** @var string */
	var $imgHTML	= null;
	/** @var string */
	var $alt		= null;
	/** @var string */
	var $tooltip	= null;
	/** @var string */
	var $keystroke	= null;
	/** @var string */
	var $class		= null;
	/** @var string */
	var $menuid		= null;
	
	/**
	* Constructor
	* @param database A database connector object
	*/
	function cbMenu( &$db ) {
		// 	$this->mosMenu( $db );			// 	maybe later:	$this->comprofilerDBTable( '#__comprofiler_menu', 'id', $db );
	}
	function setMenu( $id, $caption, $url, $target="", $imgHTML=null, $alt=null, $tooltip=null, $keystroke=null, $class=null, $topCaption=null, $menuid = null ) {
		$this->id		= $id;
		$this->name		= $caption;
		$this->link		= $url;
		$this->target	= $target;
		$this->imgHTML	= $imgHTML;
		$this->alt		= $alt;
		$this->tooltip	= $tooltip;
		$this->keystroke= $keystroke;
		$this->class	= $class;
		$this->topName	= $topCaption;
		$this->menuid	= $menuid;
	}
	// to be overriden:
	function displayMenuItem($level, $idCounter) {
	}
}	// end class cbMenu

/**
* Menu-Tree Library for handling the CB Menu tree api
* @package Community Builder
* @author Beat
*/
function multimerge ($array1, $array2) {
	if (is_array($array2) && count($array2)) {
		foreach ($array2 as $k => $v) {
			if (is_array($v) && count($v) && isset($array1[$k])) {
				$array1[$k] = multimerge($array1[$k], $v);
			} else {
				$array1[$k] = $v;
			}
		}
	} else {
		$array1 = $array2;
	}
	return $array1;
}
/**
* Menu-Tree Class for handling the CB Menu tree api
*/
class cbMenuHandler {		//BB extends cbPluginHandler  {
	/** @var array of cbMenu objects sorted properly (multidimentional, recursive) */
	var $items = array();
	/** @var int unique id counter for cb Menus */
	var $idCounter = 9001;
	/** @var string object variable name of menu name/hypertext if leafs are objects */
	var $oVarName = "name";
	/** @var string object variable name of menu link/url if leafs are objects */
	var $oVarLink = "link";
	/** @var string class name for generating the HTML of a menu item of menu item if leafs are objects */
	var $oVarDisplayClassName = "cbMenu";
	/** @var string object method for generating the HTML of a menu item of menu item if leafs are objects */
	var $oVarDisplayMethodName = "displayMenuItem";
	/** @var string optional CSS class for objects link/url */
	var $class = null;
	/** @var string text to output at begin of menu */
	var $htmlBegin = "<div class='%s'>";
	/** @var string text to output at end of menu */
	var $htmlEnd = "</div>";	// available argument: total number of menus.
	/** @var string text to output at each level of menu */
	var $htmlDown = array(	"<div class='cbMenuLevel1' id='cbMenuId%2\$s><div class='MenuLevel1txt'>%1\$s</div>",
							"<div class='cbMenuLevel2' id='cbMenuId%2\$s><div class='MenuLevel2txt'>%1\$s</div>",
							"<div class='cbMenuLevel3' id='cbMenuId%2\$s><div class='MenuLevel3txt'>%1\$s</div>" );
	/** @var string text to output at end of menu */
	var $htmlUp = array(	"</div>","</div>","</div>" );
	/** @var string text to output at leaves with URL of menu */
	var $htmlLeaf = array(	"<div class='cbMenuSingleText'>%s</div>",	// when only a text to diplay
							"<div class='cbMenuLeaf1'><a href='%s'>%s</a></div>",
							"<div class='cbMenuLeaf2'><a href='%s'>%s</a></div>",
							"<div class='cbMenuLeaf3'><a href='%s'>%s</a></div>",
							"<div class='cbMenuLeaf4'><a href='%s'>%s</a></div>" );
	/** @var string text to output at leaves without URL of menu */
	var $htmlText = array(	"", 										// returned value for empty string "" entry
							"<div class='cbMenuLeaf1'>%s</div>",
							"<div class='cbMenuLeaf2'>%s</div>",
							"<div class='cbMenuLeaf3'>%s</div>",
							"<div class='cbMenuLeaf4'>%s</div>" );
	/** @var string text to output as separator text at leaves of menu */
	var $htmlSeparator = array(	null, 									// returned value for null entry
								"%s<span class='cbMenuSeparator1'><hr noshade='noshade' size=0></span>",
								"%s<span class='cbMenuSeparator2'><hr noshade='noshade' size=0></span>",
								"%s<span class='cbMenuSeparator3'><hr noshade='noshade' size=0></span>",
								"%s<span class='cbMenuSeparator4'><hr noshade='noshade' size=0></span>" );

	/**
	* Constructor
	*/
	function cbMenuHandler() {
		//BB $this->cbPluginHandler();
	}
	
	function addArrayItem( $array ) {
		// $itm = array( 'caption' => $caption, 'url'=> $url, 'target'=> $target );
		$this->items = multimerge($this->items, $array);
	}

	function addObjectItem( $arrayPos, $caption, $url="", $target="", $img=null, $alt=null, $tooltip=null, $keystroke=null, $menuid=null ) {
		global $_CB_database;
		
		if( $target== "_self" ) $target = "";
				
		$a = &$arrayPos;
		$k = null;
		while (is_array($a)) {
			$topK = $k;
			$k = key($a);
			$a = &$a[key($a)];
		}
		if ($topK === null) $topK = $k;
		$itm = new $this->oVarDisplayClassName($_CB_database);
		$itm->setMenu( $this->idCounter++, $caption, $url, $target, $img, $alt, $tooltip, $keystroke, $this->class, $topK, $k );
		$a = $itm;
		$this->items = multimerge($this->items, $arrayPos);
	}

	function addSeparator($arrayPos)
	{
		$a = &$arrayPos;
		while (is_array($a)) $a = &$a[key($a)];
		$a = null;
		$this->items = multimerge($this->items, $arrayPos);
	}

	function displayMenu(&$idCounterStart, $menuClass=null, $callBackFunc=null) {
		if ($menuClass===null) $menuClass = $this->oVarDisplayClassName;
		if ($callBackFunc === null) {
			$callBackFunc = array(&$this,"callBack");
			$params = array("level" => 0, "idCounter" => $idCounterStart, "nbMainMenus" => count($this->items));
		}
		$ret = "";
		if (is_array($this->items)) $ret .= call_user_func_array( $callBackFunc, array(&$params, 'begin', $menuClass, $this->items) );
		$ret .= $this->_displayMenu( $callBackFunc, $params, null, $this->items );
		if (is_array($this->items)) $ret .= call_user_func_array( $callBackFunc, array(&$params, 'end', null, $this->items) );
		$idCounterStart = $params['idCounter'];
		return $ret;
	}

	function _displayMenu($callBackFunc,&$params,$key, $value) {
		$ret = "";
		if (is_array($value)) {
			foreach ($value as $k => $v) {
			if (is_array(($v))) $ret .= call_user_func_array($callBackFunc, array(&$params, 'down', $k, $v) );
				$ret .= $this->_displayMenu($callBackFunc,$params,$k,$v);
			if (is_array(($v))) $ret .= call_user_func_array($callBackFunc, array(&$params, 'up', $k, $v) );
			}
			reset($value);
		} else {
			$ret .= call_user_func_array($callBackFunc, array(&$params, 'leaf', $key, $value) );
		}
		return $ret;
	}
	function languageTranslate($string) {
		return $string;							// translation function made to overload
	}
	function callBack(&$params, $action, $key, $val) {
		$ret = "";
		$levelNow=$params['level'];
		switch ($action) {
			case "begin":
				$htmlRes = &$this->htmlBegin;
				$ret .= sprintf( $htmlRes, $key );		// key is $menuClass in this particular case 
				$params['level'] +=1;
				break;
			case "end":
				$params['level'] -=1;
				$htmlRes = &$this->htmlEnd;
				$ret .= sprintf($htmlRes, $params['nbMainMenus']);
				break;
			case "down":
				$htmlRes = &$this->htmlDown[$params['level']-1];
				$ret .= sprintf( $htmlRes, $this->languageTranslate($key), $params['idCounter']++);
				$params['level'] +=1;
				break;
			case "up":
				$params['level'] -=1;
				$htmlRes = &$this->htmlUp[$params['level']-1];
				$ret .= $htmlRes;
				break;
			case "leaf":
			// $ret.=$key; if key===null, problem here: no menu...
				if ($val === null) {
					$htmlRes = &$this->htmlSeparator[$params['level']];
					$ret .= sprintf( $htmlRes, $this->languageTranslate($key), $params['idCounter'] );
				} else if ($val === "") {
					$htmlRes = &$this->htmlText[$params['level']];
					$ret .= sprintf( $htmlRes, $this->languageTranslate($key), $params['idCounter'] );
				} else {
					if (is_object($val)) {
						if (method_exists($val,$this->oVarDisplayMethodName)) {
							$displayMethodName = $this->oVarDisplayMethodName;
							$ret .= $val->$displayMethodName($params['level'], $params['idCounter']);
						} else {
							$l = $this->oVarLink;
							$n = $this->oVarName;
							$htmlRes = &$this->htmlLeaf[$params['level']];
							$ret .= sprintf( $htmlRes, $val->$l, $val->$n, $params['idCounter'] );
						}
					} else {
						$htmlRes = &$this->htmlLeaf[$params['level']];
						$ret .= sprintf( $htmlRes, $val, $this->languageTranslate($key), $params['idCounter'] );
					}
				}
				if ($params['level']==1) $params['idCounter']++;
				break;
			default:
				echo "error\n";
		}
		if ($ret) {
			$tabs = "";
			for ($i=0; $i<min($params['level'],$levelNow); $i++) $tabs .= "\t";
			$ret = $tabs.$ret."\n";
		}
		// echo $action." ".$key." => ".(($action=="leaf") ? $val : key($val))."\n";
		return $ret;
	}
	function set( $property, $value ) {
		$this->$property = $value;
	}
}	// end class cbMenuHandler


/****** CSS/JS Best Menu classes: *******/

/**
* Module database table class for menu storage
* @package Community Builder
* @author Beat
*/
class cbMenuBest extends cbMenu {			// later: extends mosMenu
	/**
	* Constructor
	* @param database A database connector object
	*/
	function cbMenuBest ( &$db ) {
		$this->cbMenu($db);
	}
	function displayMenuItem($level, $idCounter) {
		$ret = '';
		switch ($level) {
			case 0:
				$ret .= "<div class=\"cbMenuSingleText\">".$this->name."</div>";		// when only a text to diplay as whole menu!
				break;
			case 1:
				$ret .= "<li id=\"menu".$idCounter."\" class=\"cbMenu\" onmouseover=\"MontrerMenu('ssmenu%3\$d');\" onmouseout=\"CacherDelai();\">";
				if (substr(ltrim($this->link),0,1) == '<') {
					$ret .= $this->link;
				} else {
					$ret .= "<a href=\"".$this->link."\"";
					if (isset($this->class) && $this->class) $ret .= " class=\"".$this->class."\"";
					if (isset($this->target) && $this->target) $ret .= " target=\"".$this->target."\"";
					if (isset($this->tooltip) && $this->tooltip) $ret .= " title=\"".$this->tooltip."\"";
					$ret .= ">";
					if (isset($this->imgHTML) && $this->imgHTML) $ret .= $this->imgHTML;				//BB: missing alt text...
					$ret .= $this->name."<span>&nbsp;:</span></a>";	// empty menu
				}
				$ret .= "</li>";
				break;
			case 2:
				$ret .= "<li>";
				if (substr(ltrim($this->link),0,1) == '<') {
					$ret .= $this->link;
				} else {
					$ret .= "<a href=\"".$this->link."\"";
					if (isset($this->class) && $this->class) $ret .= " class=\"".$this->class."\"";
					if (isset($this->target) && $this->target) $ret .= " target=\"".$this->target."\"";
					if (isset($this->tooltip) && $this->tooltip) $ret .= " title=\"".$this->tooltip."\"";
					$ret .= ">";
					if (isset($this->imgHTML) && $this->imgHTML) $ret .= $this->imgHTML;				//BB: missing alt text...
					$ret .= $this->name."<span>&nbsp;;</span></a>";
				}
				$ret .= "</li>";
				break;
			default:
		}		
		return $ret;
	}
}	// end class cbMenuBest

class cbBestMenuHandler  extends cbMenuHandler {
	/** @var string text to output at begin of menu */
	var $htmlBegin = "<div id=\"conteneurmenu\">\n<script type=\"text/javascript\">preChargement();</script>
<ul class=\"cbpMenu\" id=\"cbMenuNav\">";
	/** @var string text to output at end of menu */
	var $htmlEnd = "  </ul>\n</div>
<script type=\"text/javascript\"><!--//--><![CDATA[//><!--
nbmenu=%d;
var cbOldwindowOnLoad;
if (window.attachEvent) window.attachEvent(\"onload\", Chargement)
else { if (document.all&&document.getElementById&&window.onload) { cbOldwindowOnLoad = window.onload;
window.onload = function() { if (cbOldwindowOnLoad) cbOldwindowOnLoad(); Chargement(); } }
		else Chargement();}
//--><!]]></script>";
	/** @var string text to output at each level of menu
	* 		/ enleve:  class=\"mainlevel\"
	*/
	var $htmlDown = array(	"<li id=\"menu%2\$d\" class=\"cbMenu\" onmouseover=\"MontrerMenu('ssmenu%2\$d');\" onmouseout=\"CacherDelai();\">
	<a href=\"javascript:void(0)\">%1\$s<span>&nbsp;:</span></a>\n\t  <ul id=\"ssmenu%2\$d\" class=\"cbSSmenu\">",
							"\t<li><a href=\"javascript:void(0)\">%1\$s<span>&nbsp;:</span></a>\n\t\t  <ul>",
							"\t\t<li><a href=\"javascript:void(0)\">%1\$s<span>&nbsp;:</span></a>\n\t\t\t  <ul>" );
	/** @var string text to output at end of menu */
	var $htmlUp = array(	"  </ul>\n\t</li>","\t  </ul>\n\t\t</li>","\t\t  </ul>\n\t\t\t</li>" );
	/** @var string text to output at leaves with URL of menu */
	var $htmlLeaf = array(	"<div class=\"cbMenuSingleText\">%s</div>",	// when only a text to diplay as whole menu!
							"<p id=\"menu%3\$d\" class=\"cbMenu\" onmouseover=\"MontrerMenu('ssmenu%3\$d');\" onmouseout=\"CacherDelai();\"><a href=\"%1\$s\">%2\$s<span>&nbsp;;</span></a></p>",	// empty menu
							"<li><a href=\"%s\">%s<span>&nbsp;;</span></a></li>",
							"<div class=\"cbMenuLeaf3\"><a href=\"%s\">%s</a></div>",
							"<div class=\"cbMenuLeaf4\"><a href=\"%s\">%s</a></div>" );
	/** @var string text to output at leaves without URL of menu */
	var $htmlText = array(	"", 										// returned value for empty string "" entry
							"<div class=\"cbMenuLeaf1\">%s</div>",
							"<li><a href=\"javascript:void()\">%s<span>&nbsp;;</span></a></li>",
							"<div class=\"cbMenuLeaf3\">%s</div>",
							"<div class=\"cbMenuLeaf4\">%s</div>" );
	/** @var string text to output as separator text at leaves of menu */
	var $htmlSeparator = array(	null, 									// returned value for null entry
								"%s<span class=\"cbMenuSeparator1\"><hr noshade=\"noshade\" size=0></span>",
								"<li><hr noshade=\"noshade\" size=\"0\" /><span>&nbsp;;</span></li>",
								"%s<span class=\"cbMenuSeparator3\"><hr noshade=\"noshade\" size=0></span>",
								"%s<span class=\"cbMenuSeparator4\"><hr noshade=\"noshade\" size=0></span>" );
	/**
	* Constructor
	* @param int userinterface. Frontend:1, Backend:2.
	*/
	function cbBestMenuHandler() {
		$this->cbMenuHandler();
		$this->set("oVarDisplayClassName","cbMenuBest");
		//	$head = '<link rel="stylesheet" type="text/css" href="menubest.css" />';
		//	echo $head."\n";		//BB needs to go into <head> section for W3C compliance -> gone into CB CSS file...
		// echo "<script type=\"text/javascript\" src=\"menubest.js\"></script>\n";
	}
}	// end class cbBestMenuHandler


/****** CSS/JS Menu classes: *******/

/**
* Module database table class for menu storage
* @package Community Builder
* @author Beat
*/
class cbMenuCSS extends cbMenu {			// later: extends mosMenu
	/**
	* Constructor
	* @param database A database connector object
	*/
	function cbMenuCSS ( &$db ) {
		$this->cbMenu($db);
	}
	function displayMenuItem($level, $idCounter) {
		$ret = '';
		switch ($level) {
			case 0:
				$ret .= "<div class='cbMenuSingleText'>".$this->name."</div>";		// when only a text to diplay as whole menu!
				break;
			case 1:
				$ret .= "<p id=\"menu".$idCounter."\" class=\"cbMenu\" onmouseover=\"MontrerMenu('ssmenu%3\$d');\" onmouseout=\"CacherDelai();\">";
				if (substr(ltrim($this->link),0,1) == '<') {
					$ret .= $this->link;
				} else {
					$ret .= "<a href=\"".$this->link."\"";
					if (isset($this->class) && $this->class) $ret .= " class='".$this->class."'";
					if (isset($this->target) && $this->target) $ret .= " target='".$this->target."'";
					if (isset($this->tooltip) && $this->tooltip) $ret .= " title='".$this->tooltip."'";
					$ret .= ">";
					if (isset($this->imgHTML) && $this->imgHTML) $ret .= $this->imgHTML;				//BB: missing alt text...
					$ret .= $this->name."<span>&nbsp;:</span></a>";	// empty menu
				}
				$ret .= "</p>";
				break;
			case 2:
				$ret .= "<li>";
				if (substr(ltrim($this->link),0,1) == '<') {
					$ret .= $this->link;
				} else {
					$ret .= "<a href=\"".$this->link."\"";
					if (isset($this->class) && $this->class) $ret .= " class='".$this->class."'";
					if (isset($this->target) && $this->target) $ret .= " target='".$this->target."'";
					if (isset($this->tooltip) && $this->tooltip) $ret .= " title='".$this->tooltip."'";
					$ret .= ">";
					if (isset($this->imgHTML) && $this->imgHTML) $ret .= $this->imgHTML;				//BB: missing alt text...
					$ret .= $this->name."<span>&nbsp;;</span></a>";
				}
				$ret .= "</li>";
				break;
			default:
		}		
		return $ret;
	}
}	// end class cbMenuCSS

class cbCSSMenuHandler  extends cbMenuHandler {
	/** @var string text to output at begin of menu */
	var $htmlBegin = "<div id=\"conteneurmenu\">\n<script type=\"text/javascript\">preChargement();</script>";
	/** @var string text to output at end of menu */
	var $htmlEnd = "</div><script type=\"text/javascript\"><!--//--><![CDATA[//><!--
nbmenu=%d;
var cbOldwindowOnLoad;
if (window.attachEvent) window.attachEvent(\"onload\", Chargement)
else { if (document.all&&document.getElementById&&window.onload) { cbOldwindowOnLoad = window.onload;
window.onload = function() { if (cbOldwindowOnLoad) cbOldwindowOnLoad(); Chargement(); } }
		else Chargement();}
//--><!]]></script>";
	/** @var string text to output at each level of menu
	* 		/ enleve:  class=\"mainlevel\"
	*/
	var $htmlDown = array(	"<p id=\"menu%2\$d\" class=\"cbMenu\" onmouseover=\"MontrerMenu('ssmenu%2\$d');\" onmouseout=\"CacherDelai();\"><a href=\"javascript:void(0)\" onmouseover=\"MontrerMenu('ssmenu%2\$d');\">%1\$s<span>&nbsp;:</span></a></p>
<ul id=\"ssmenu%2\$d\" class=\"cbSSmenu\" onmouseover=\"AnnulerCacher();\" onmouseout=\"CacherDelai();\">",
							"<*>",
							"<*>" );
	/** @var string text to output at end of menu */
	var $htmlUp = array(	"</ul>","</*>","</*>" );
	/** @var string text to output at leaves with URL of menu */
	var $htmlLeaf = array(	"<div class='cbMenuSingleText'>%s</div>",	// when only a text to diplay as whole menu!
							"<p id=\"menu%3\$d\" class=\"cbMenu\" onmouseover=\"MontrerMenu('ssmenu%3\$d');\" onmouseout=\"CacherDelai();\"><a href='%1\$s'>%2\$s<span>&nbsp;;</span></a></p>",	// empty menu
							"<li><a href='%s'>%s<span>&nbsp;;</span></a></li>",
							"<div class='cbMenuLeaf3'><a href='%s'>%s</a></div>",
							"<div class='cbMenuLeaf4'><a href='%s'>%s</a></div>" );
	/** @var string text to output at leaves without URL of menu */
	var $htmlText = array(	"", 										// returned value for empty string "" entry
							"<div class='cbMenuLeaf1'>%s</div>",
							"<li><a href='javascript:void()'>%s<span>&nbsp;;</span></a></li>",
							"<div class='cbMenuLeaf3'>%s</div>",
							"<div class='cbMenuLeaf4'>%s</div>" );
	/** @var string text to output as separator text at leaves of menu */
	var $htmlSeparator = array(	null, 									// returned value for null entry
								"%s<span class='cbMenuSeparator1'><hr noshade='noshade' size=0></span>",
								"<li><hr noshade='noshade' size=\"0\" /><span>&nbsp;;</span></li>",
								"%s<span class='cbMenuSeparator3'><hr noshade='noshade' size=0></span>",
								"%s<span class='cbMenuSeparator4'><hr noshade='noshade' size=0></span>" );
	/**
	* Constructor
	* @param int userinterface. Frontend:1, Backend:2.
	*/
	function cbCSSMenuHandler() {
		$this->cbMenuHandler();
		$this->set("oVarDisplayClassName","cbMenuCSS");
		//	$head = '<link rel="stylesheet" type="text/css" href="menu.css" />';
		//	echo $head."\n";		//BB needs to go into <head> section for W3C compliance -> gone into CB CSS file...
		// echo "<script type=\"text/javascript\" src=\"menu.js\"></script>\n";
	}
}	// end class cbCSSMenuHandler


/******* Suckfish menu: *******/

/**
* Module database table class for menu storage
* @package Community Builder
* @author Beat
*/
class cbMenuSucker extends cbMenu {			// later: extends mosMenu
	function displayMenuItem($level, $idCounter) {
		$ret = '';
		switch ($level) {
			case 0:
				$ret .= "<li>".$this->name."</li>\n";		// when only a text to diplay as whole menu!
				break;
			default:
				$ret .= "<li>";
				if (substr(ltrim($this->link),0,1) == '<') {
					$ret .= $this->link;
				} else {
					$ret .= "<a href=\"".$this->link."\"";
					if (isset($this->class) && $this->class) $ret .= " class='".$this->class."'";
					if (isset($this->target) && $this->target) $ret .= " target='".$this->target."'";
					if (isset($this->tooltip) && $this->tooltip) $ret .= " title='".$this->tooltip."'";
					$ret .= ">";
					if (isset($this->imgHTML) && $this->imgHTML) $ret .= $this->imgHTML;
					$ret .= $this->name."<span>&nbsp;;</span></a>";
				}
				$ret .= "</li>";
				break;
		}		
		return $ret;
	}
}	// end class cbMenuSucker

class cbSuckerMenuHandler  extends cbMenuHandler {
	/** @var string text to output at begin of menu */
	var $htmlBegin = "<script type=\"text/javascript\"><!--//--><![CDATA[//><!--
function cbSfHover() {
	var sfEls = document.getElementById(\"cbsMenuNav\").getElementsByTagName(\"LI\");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=\" sfhover\";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(\" sfhover\\\\b\"), \"\");
		}
	}
}
var cbOldwindowOnLoad;
if (window.attachEvent) window.attachEvent(\"onload\", cbSfHover)
else if (document.all&&document.getElementById&&window.onload) { cbOldwindowOnLoad = window.onload;
window.onload = function() { if (cbOldwindowOnLoad) cbOldwindowOnLoad(); cbSfHover(); } }
//--><!]]></script>
<div id=\"conteneurmenu\">
  <ul class=\"cbsMenu\" id=\"cbsMenuNav\">";
	/** @var string text to output at end of menu */
	var $htmlEnd = "  </ul>\n</div>";
	/** @var string text to output at each level of menu
	* 		/ enleve:  class=\"mainlevel\"
	*/
	var $htmlDown = array(	"<li><a href=\"javascript:void(0)\">%1\$s<span>&nbsp;:</span></a>\n\t  <ul>",
							"\t<li><a href=\"javascript:void(0)\">%1\$s<span>&nbsp;:</span></a>\n\t\t  <ul>",
							"\t\t<li><a href=\"javascript:void(0)\">%1\$s<span>&nbsp;:</span></a>\n\t\t\t  <ul>" );
	/** @var string text to output at end of menu */
	var $htmlUp = array(	"  </ul>\n\t</li>","\t  </ul>\n\t\t</li>","\t\t  </ul>\n\t\t\t</li>" );
	/** @var string text to output at leaves with URL of menu */
	var $htmlLeaf = array(	"","","","");	// when only a text to diplay as whole menu!
	/** @var string text to output at leaves without URL of menu */
	var $htmlText = array(	"","","","");	// returned value for empty string "" entry
	/** @var string text to output as separator text at leaves of menu */
	var $htmlSeparator = array(	null,		// returned value for -1st: null entry, -next-ones: separator
								"","","","");
	/**
	* Constructor
	* @param int userinterface. Frontend:1, Backend:2.
	*/
	function cbSuckerMenuHandler() {
		$this->cbMenuHandler();
		$this->set("oVarDisplayClassName","cbMenuSucker");
	}
}	// end class cbSuckerMenuHandler


/****** CB Best Menu classes: table: *******/

/**
* Module database table class for menu storage
* @package Community Builder
* @author Beat
*/
class cbMenuTabList extends cbMenu {			// later: extends mosMenu
	/**
	* Constructor
	* @param database A database connector object
	*/
	function cbMenuTabList ( &$db ) {
		$this->cbMenu($db);
	}
	function displayMenuItem($level, $idCounter) {
		global $cbMenuTabListLastTopName;
		$ret = '';
		switch ($level) {
			case 0:			// when only a text to diplay as whole menu!
			case 1:			// Do not display menu header
				break;
			default:
				$ret .= "\t\t\t<tr class=\"sectiontableentry".($idCounter&1 ? 1 : 2)."\" id=\"cbStatList" . $idCounter . "\">\n\t\t\t\t\t\t";
				$ret .= '<td class="titleCell">';
				if (!isset($cbMenuTabListLastTopName) or ($this->topName != $cbMenuTabListLastTopName)) {
					$cbMenuTabListLastTopName = $this->topName;
					$ret .= getLangDefinition($this->topName);
				} else $ret .= "&nbsp;";
				$ret .= "</td>\n\t\t\t\t\t\t";
				$ret .= '<td class="fieldCell">';
				if (substr(ltrim($this->link),0,1) == '<') {
					$ret .= $this->link;
				} else {
					if (isset($this->link) && $this->link) {
						$ret .= "<a href=\"".$this->link."\"";
						if (isset($this->target) && $this->target) $ret .= " target=\"".$this->target."\"";
					} else {
						$ret .= "<span";
					}
					if (isset($this->class) && $this->class) $ret .= " class=\"".$this->class."\"";
					if (isset($this->tooltip) && $this->tooltip) $ret .= " title=\"".$this->tooltip."\"";
					$ret .= ">";
					// if (isset($this->imgHTML) && $this->imgHTML) $ret .= $this->imgHTML;				//BB: missing alt text...
					$ret .= $this->name;
					if (isset($this->link) && $this->link) {
						$ret .= "</a>";
					} else {
						$ret .= "</span>";
					}
				}
				$ret .= "</td>\n\t\t\t\t\t</tr>";
				break;
		}		
		return $ret;
	}
}	// end class cbMenuTabList

class cbListMenuHandler  extends cbMenuHandler {
	/** @var string text to output at begin of menu */
	var $htmlBegin = "";
	/** @var string text to output at end of menu */
	var $htmlEnd = "";
	/** @var string text to output at each level of menu
	* 		/ enleve:  class=\"mainlevel\"
	*/
	var $htmlDown = array(	"","","" );
	/** @var string text to output at end of menu */
	var $htmlUp = array(	"","","" );
	/** @var string text to output at leaves with URL of menu */
	var $htmlLeaf = array(	"","","","");	// when only a text to diplay as whole menu!
	/** @var string text to output at leaves without URL of menu */
	var $htmlText = array(	"","","","");	// returned value for empty string "" entry
	/** @var string text to output as separator text at leaves of menu */
	var $htmlSeparator = array(	null,		// returned value for -1st: null entry, -next-ones: separator
								"\t\t\t<tr class=\"sectiontableentry1\"><td colspan=\"2\"><hr /></td></tr>","\t\t\t<tr><td colspan=\"2\"><hr /></td></tr>","","");
	/**
	* Constructor
	* @param int userinterface. Frontend:1, Backend:2.
	*/
	function cbListMenuHandler() {
		$this->cbMenuHandler();
		$this->set("oVarDisplayClassName","cbMenuTabList");
	}
}	// end class cbListMenuHandler

/****** CB Best Menu classes: table: *******/

/**
* Module database table class for menu storage
* @package Community Builder
* @author Beat
*/
class cbMenuDivsList extends cbMenu {			// later: extends mosMenu
	/**
	* Constructor
	* @param database A database connector object
	*/
	function cbMenuDivsList ( &$db ) {
		$this->cbMenu($db);
	}
	function displayMenuItem($level, $idCounter) {
		$ret = '';
		switch ($level) {
			case 0:			// when only a text to diplay as whole menu!
			case 1:			// Do not display menu header
				break;
			default:
				$menuidClass	=	' class="cbMenuItem'
								.	( $this->menuid ? ' cbMenu' . $this->menuid : '' )
								.	( ( isset( $this->class ) && $this->class ) ? ' ' . $this->class : '' )
								.	'"';
				$ret 			.=	"\t\t\t<tr class=\"sectiontableentry".($idCounter&1 ? 1 : 2)."\" id=\"cbStatList" . $idCounter . "\">\n\t\t\t\t\t\t";
/*
				$ret .= '<td class="titleCell">';
				if (!isset($cbMenuDivsListLastTopName) or ($this->topName != $cbMenuDivsListLastTopName)) {
					$cbMenuDivsListLastTopName = $this->topName;
					$ret .= getLangDefinition($this->topName);
				} else $ret .= "&nbsp;";
				$ret .= "</td>\n\t\t\t\t\t\t";
*/
				$ret			.=	"\n\t\t\t\t\t\t" . '<td class="fieldCell" colspan="2">';
				if (substr(ltrim($this->link),0,1) == '<') {
					$ret		.=	$this->link;
				} else {
					if (isset($this->link) && $this->link) {
						$ret	.=	"<a href=\"".$this->link."\"";
						if (isset($this->target) && $this->target) {
							$ret .= " target=\"".$this->target."\"";
						}
					} else {
						$ret	.=	"<span";
					}
					$ret		.=	$menuidClass;
					if (isset($this->tooltip) && $this->tooltip) {
						$ret	.=	" title=\"".$this->tooltip."\"";
					}
					$ret		.=	">";
					// if (isset($this->imgHTML) && $this->imgHTML) $ret .= $this->imgHTML;				//BB: missing alt text...
					$ret		.=	$this->name;
					if (isset($this->link) && $this->link) {
						$ret	.=	"</a>";
					} else {
						$ret	.=	"</span>";
					}
				}
				$ret			.=	"</td>\n\t\t\t\t\t</tr>";
				break;
		}		
		return $ret;
	}
}	// end class cbMenuDivsList

class cbDivsMenuHandler  extends cbListMenuHandler {
	/**
	* Constructor
	* @param int userinterface. Frontend:1, Backend:2.
	*/
	function cbDivsMenuHandler() {
		$this->cbMenuHandler();
		$this->set("oVarDisplayClassName","cbMenuDivsList");
	}
}	// end class cbDivsMenuHandler

class cbMenuULlist extends cbMenu {
	function displayMenuItem($level, $idCounter) {
		$ret 			= '';
		$menuidClass	=	' class="cbMenuItem cbMenuEogr' . ( $idCounter & 1 ? 2 : 1 )
						.	( $this->menuid ? ' cbMenu' . $this->menuid : '' )
						.	( ( isset( $this->class ) && $this->class ) ? ' ' . $this->class : '' )
						.	'"'
						.	( ( isset( $this->tooltip ) && $this->tooltip ) ? ' title="' . $this->tooltip . '"' : '' )
						;
		switch ($level) {
			case 0:
				$ret 			.= '<li' . $menuidClass . '>' . $this->name . '</li>';		// when only a text to diplay as whole menu!
				break;
			default:
				$ret 			.= '<li' . $menuidClass . '>';
				if ( substr( ltrim( $this->link ), 0, 1 ) == '<' ) {
					$ret		.=	$this->link;
				} else {
					$ret		.=	"<a href=\"".$this->link."\"";
					if (isset($this->target) && $this->target) {
						$ret	.=	' target="' . $this->target . '"';
					}
					$ret 		.= '>';
					// no images here: if (isset($this->imgHTML) && $this->imgHTML) $ret .= $this->imgHTML;
					$ret 		.= $this->name . '</a>';
				}
				$ret 			.= '</li>';
				break;
		}		
		return $ret;
	}
}
class cbMenuHandlerUL  extends cbSuckerMenuHandler {
	/** @var string text to output at begin of menu */
	var $htmlBegin = "<ul class='%s'>";
	/** @var string text to output at end of menu */
	var $htmlEnd = "</ul>";	// available argument: total number of menus.
	/** @var string text to output at each level of menu */
	var $htmlDown = array(	'',	// "<li class='cbMenuLevel1' id='cbMenuId%2\$s'><ul class='MenuLevel1txt'>",
							"<li class='cbMenuLevel2' id='cbMenuId%2\$s'><ul class='MenuLevel2txt'>",
							"<li class='cbMenuLevel3' id='cbMenuId%2\$s'><ul class='MenuLevel3txt'>" );
	/** @var string text to output at end of menu */
	var $htmlUp = array(	'', /*"</ul></li>",*/"</ul></li>","</ul></li>" );
	/** @var string text to output at leaves with URL of menu */
	var $htmlLeaf = array(	"<li class='cbMenuSingleText'>%s</li>",	// when only a text to diplay
							"<li class='cbMenuLeaf1'><a href='%s'>%s</a></li>",
							"<li class='cbMenuLeaf2'><a href='%s'>%s</a></li>",
							"<li class='cbMenuLeaf3'><a href='%s'>%s</a></li>",
							"<li class='cbMenuLeaf4'><a href='%s'>%s</a></li>" );
	/** @var string text to output at leaves without URL of menu */
	var $htmlText = array(	"", 										// returned value for empty string "" entry
							"<li class='cbMenuLeaf1'>%s</li>",
							"<li class='cbMenuLeaf2'>%s</li>",
							"<li class='cbMenuLeaf3'>%s</li>",
							"<li class='cbMenuLeaf4'>%s</li>" );
	/** @var string text to output as separator text at leaves of menu */
	var $htmlSeparator = array(	null, 									// returned value for null entry
								"%s<li class='cbMenuSeparator1'><hr noshade='noshade' size=0></li>",
								"%s<li class='cbMenuSeparator2'><hr noshade='noshade' size=0></li>",
								"%s<li class='cbMenuSeparator3'><hr noshade='noshade' size=0></li>",
								"%s<li class='cbMenuSeparator4'><hr noshade='noshade' size=0></li>" );
	/**
	* Constructor
	* @param int userinterface. Frontend:1, Backend:2.
	*/
	function cbMenuHandlerUL() {
		$this->cbSuckerMenuHandler();
		$this->set("oVarDisplayClassName","cbMenuULlist");
	}
	
	
}	// end class cbMenuHandlerUL

/******* comprofiler MenuBar classes: ******/

class cbMenuBar extends cbBestMenuHandler {
// class cbMenuBar extends cbCSSMenuHandler {
// class cbMenuBar extends cbSuckerMenuHandler {
	function outputScripts( ) {
		global $_CB_framework;
		$_CB_framework->document->addHeadScriptUrl( '/components/com_comprofiler/js/menubest.js', true );
		return null;
	}
	function languageTranslate($string) {
		return getLangDefinition($string);
	}
}	// end class cbMenuBar


class cbMenuList extends cbListMenuHandler {
	function outputScripts( /*$ui=1 */ ) {
	}
	function languageTranslate($string) {
		return getLangDefinition($string);
	}
}	// end class cbMenuList

class cbMenuDivs extends cbDivsMenuHandler {
	function outputScripts( /*$ui=1 */ ) {
	}
	function languageTranslate($string) {
		return getLangDefinition($string);
	}
}	// end class cbMenuDivs

class cbMenuUL extends cbMenuHandlerUL {
	function outputScripts( /*$ui=1 */ ) {
	}
	function languageTranslate($string) {
		return getLangDefinition($string);
	}
}	// end class cbMenuUL



// ****************************************************************************************************

/**
* Core Status Tab Class for handling the CB User Status Display and the CB tab api
* @package	Community Builder
* @author	Beat
*/
class getMenuTab  extends cbTabHandler {
	/**	@var cbMenuHandler $menuBar */
	var $menuBar;
	var $ui;
	var $cbMyIsModerator;
	var $cbUserIsModerator;
	/**
	* Constructor
	*/
	function getMenuTab() {
		$this->cbTabHandler();
	}
	/**
	* Method is called before the user profile is viewed (onPrepareMenus event)
	*
	* @param  moscomprofilerUser  holds the core mambo user data
	*/
	function prepareMenu( &$user ) {
		global $_CB_framework;

		$this->ui					=	$_CB_framework->getUi();
		$this->cbUserIsModerator	=	isModerator( $user->id );
		$this->cbMyIsModerator		=	isModerator( $_CB_framework->myId() );
		$params						=	$this->params;
		switch ($params->get('menuFormat', 'menuBar')) {
			case "menuList":
			case "no":
				$this->menuBar = new cbMenuList(1);
				break;
			case "menuUL":
				$this->menuBar = new cbMenuUL(1);
				break;
			case "menuDivs":
				$this->menuBar = new cbMenuDivs(1);
				break;
			case "menuBar":
			default:
				$this->menuBar = new cbMenuBar(1);
				break;
		}
		$this->menuBar->outputScripts(1);
	}
	/**
	 * Adds a menu item to the menu tree array to be displayed later anywhere
	 *
	 * @param string $arrayPos		array of array (single item each time)
	 * @param string $caption
	 * @param string $url
	 * @param string $target
	 * @param string $img
	 * @param string $alt
	 * @param string $tooltip
	 * @param char	 $keystroke
	 */
	function _addMenuItem( $arrayPos, $caption, $url="", $target="", $img=null, $alt=null, $tooltip=null, $keystroke=null ) {
		$this->addMenu( array(	"position"	=> "menuBar" ,		// "menuBar", "menuList"
							"arrayPos"	=> $arrayPos ,
							"caption"	=> $caption ,
							"url"		=> $url ,
							"target"	=> $target,
							"img"		=> $img ,
							"alt"		=> $alt ,
							"tooltip"	=> $tooltip,
							"keystroke"	=> $keystroke ) );
	}
	/**
	 * add a '&uid='.$user->id if $user is not viewing user
	 *
	 * @param  moscomprofilerUser  $user
	 * @return string
	 */
	function _addUid( &$user ) {
		global $_CB_framework;

		if ( $_CB_framework->myId() != $user->id ) {
			return "&amp;uid=".$user->id;
		}
		return null;
	}
	/**
	* Generates the menu and user status to display on the user profile by calling back $this->addMenu
	* @param  moscomprofilerTab   $tab       the tab database entry
	* @param  moscomprofilerUser  $user      the user being displayed
	* @param  int                 $ui        1 for front-end, 2 for back-end
	* @return boolean                        either true, or false if ErrorMSG generated
	*/
	function getMenuAndStatus( $tab, $user, $ui ) {
		global $_CB_framework, $_CB_database, $ueConfig,$_REQUEST,$_POST;

		$params				=	$this->params;

		$Itemid				=	getCBprofileItemid( 0 );

		// Build basic menu:
		$ue_base_url		 = "index.php?option=com_comprofiler";
		if ( $Itemid ) {
			$ue_base_url	.= "&amp;Itemid=" . $Itemid;	// Base URL string
		}
		$ue_credits_url		 = $ue_base_url."&amp;task=teamCredits";
		$ue_userdetails_url	 = $ue_base_url."&amp;task=userDetails" . $this->_addUid( $user );
		$ue_useravatar_url	 = $ue_base_url."&amp;task=userAvatar" . $this->_addUid( $user );
		$ue_deleteavatar_url = $ue_base_url."&amp;task=userAvatar&amp;do=deleteavatar" . $this->_addUid( $user );
		$ue_unbanrequest_url = $ue_base_url."&amp;task=banProfile&amp;act=2&amp;reportform=1&amp;uid=".$user->id;
		$ue_banhistory_url   = $ue_base_url."&amp;task=moderateBans&amp;act=2&amp;uid=".$user->id;
		$ue_ban_url 		 = $ue_base_url."&amp;task=banProfile&amp;act=1&amp;uid=".$user->id;
		$ue_unban_url 		 = $ue_base_url."&amp;task=banProfile&amp;act=0&amp;reportform=0&amp;uid=".$user->id;
		$ue_reportuser_url	 = $ue_base_url."&amp;task=reportUser&amp;uid=".$user->id;
		$ue_viewuserreports_url = $ue_base_url."&amp;task=viewReports&amp;uid=".$user->id;
		$ue_viewOlduserreports_url = $ue_base_url."&amp;task=viewReports&amp;act=1&amp;uid=".$user->id;
		$ue_approve_image_url= $ue_base_url."&amp;task=approveImage&amp;flag=1&amp;avatars=".$user->id;
		$ue_reject_image_url = $ue_base_url."&amp;task=approveImage&amp;flag=0&amp;avatars=".$user->id;
		$ue_userprofile_url	 = $ue_base_url."";
		$adminimagesdir		=	$_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/images/';

		// $this->menuBar->set("class", "mainlevel");		//BB: hardcoded to check >RC2.

		$firstMenuName		= $params->get('firstMenuName', '_UE_MENU_CB');
		$firstSubMenuName	= $params->get('firstSubMenuName', '_UE_MENU_ABOUT_CB');
		$firstSubMenuHref	= $params->get('firstSubMenuHref', $ue_credits_url);
		$secondSubMenuName	= $params->get('secondSubMenuName', '');
		$secondSubMenuHref	= $params->get('secondSubMenuHref', '');
		if ($firstMenuName != "") {
			$mi = array(); $mi[$firstMenuName]='';
		//	$this->_addMenuItem( $mi,$firstMenuName,"javascript:void(0)" );		// Community
			if ($firstSubMenuName != "") {
				unset($mi);
				if ($firstSubMenuHref == "") $firstSubMenuHref = "javascript:void(0)";
				$mi = array(); $mi[$firstMenuName]["_UE_TEAMCREDITS_CB"]='';
				$this->_addMenuItem( $mi,getLangDefinition($firstSubMenuName),cbSef($firstSubMenuHref) );		// About...
				if ($secondSubMenuName != "") {
					if ($secondSubMenuHref == "") $secondSubMenuHref = "javascript:void(0)";
					$mi = array(); $mi[$firstMenuName]["_UE_SECOND"]='';
					$this->_addMenuItem( $mi,getLangDefinition($secondSubMenuName),cbSef($secondSubMenuHref) );		// Free...
				}
			}
		}
		// ----- VIEW MENU - BEFORE EDIT MENU IF NOT VIEWING A PROFILE -----
		if ( $_CB_framework->myId() > 0 ) {
			// View My Profile:
			if ( $_CB_framework->displayedUser() === null ) {
				$mi = array(); $mi["_UE_MENU_VIEW"]["_UE_MENU_VIEWMYPROFILE"]=null;
				$this->_addMenuItem( $mi, _UE_MENU_VIEWMYPROFILE,cbSef($ue_userprofile_url), "",
				"","", _UE_MENU_VIEWMYPROFILE_DESC,"" );
			}
		}
		// ----- EDIT MENU -----
		if ( ! cbCheckIfUserCanPerformUserTask( $user->id, 'allowModeratorsUserEdit') ) {
			if ( $user->id == $_CB_framework->myId() ) {
				$menuTexts	=	array(	'_UE_UPDATEPROFILE'				=>	_UE_UPDATEPROFILE,
										'_UE_MENU_UPDATEPROFILE_DESC'	=>	_UE_MENU_UPDATEPROFILE_DESC,
										'_UE_UPDATEAVATAR'				=>	_UE_UPDATEAVATAR,
										'_UE_MENU_UPDATEAVATAR_DESC'	=>	_UE_MENU_UPDATEAVATAR_DESC,
										'_UE_DELETE_AVATAR'				=>	_UE_DELETE_AVATAR,
										'_UE_MENU_DELETE_AVATAR_DESC'	=>	_UE_MENU_DELETE_AVATAR_DESC
									);
			} else {
				$menuTexts	=	array(	'_UE_UPDATEPROFILE'				=>	_UE_MOD_MENU_UPDATEPROFILE,
										'_UE_MENU_UPDATEPROFILE_DESC'	=>	_UE_MOD_MENU_UPDATEPROFILE_DESC,
										'_UE_UPDATEAVATAR'				=>	_UE_MOD_MENU_UPDATEAVATAR,
										'_UE_MENU_UPDATEAVATAR_DESC'	=>	_UE_MOD_MENU_UPDATEAVATAR_DESC,
										'_UE_DELETE_AVATAR'				=>	_UE_MOD_MENU_DELETE_AVATAR,
										'_UE_MENU_DELETE_AVATAR_DESC'	=>	_UE_MOD_MENU_DELETE_AVATAR_DESC
									);
			}
			// Update Profile:
			$mi = array(); $mi["_UE_MENU_EDIT"]["_UE_UPDATEPROFILE"]=null;
			$this->_addMenuItem( $mi, $menuTexts['_UE_UPDATEPROFILE'],cbSef($ue_userdetails_url), "",
			"<img src=\"".$adminimagesdir."updateprofile.gif\" alt='' />","", $menuTexts['_UE_MENU_UPDATEPROFILE_DESC'],"" );
			// Update Avatar:
			if($ueConfig['allowAvatar']==1 && ($ueConfig['allowAvatarUpload']==1 || $ueConfig['allowAvatarGallery']==1)) {
				$mi = array(); $mi["_UE_MENU_EDIT"]["_UE_UPDATEAVATAR"]=null;
				$this->_addMenuItem( $mi, $menuTexts['_UE_UPDATEAVATAR'],cbSef($ue_useravatar_url), "",
				"<img src=\"".$adminimagesdir."newavatar.gif\" alt='' />","", $menuTexts['_UE_MENU_UPDATEAVATAR_DESC'],"" );
				// Delete Avatar:
				if($user->avatar!='' && $user->avatar!=null) {
					$mi = array(); $mi["_UE_MENU_EDIT"]["_UE_DELETE_AVATAR"]=null;
					$this->_addMenuItem( $mi, $menuTexts['_UE_DELETE_AVATAR'],cbSef($ue_deleteavatar_url), "",
					"<img src=\"".$adminimagesdir."delavatar.gif\" alt='' />","", $menuTexts['_UE_MENU_DELETE_AVATAR_DESC'],"" );
				}
			}
		}
		// ----- VIEW MENU - AFTER EDIT IF VIEWING A PROFILE -----
		if ( $_CB_framework->myId() > 0 ) {
			// View My Profile:
			if ( ( $_CB_framework->myId() != $user->id ) && ( $_CB_framework->displayedUser() !== null ) ) {
				$mi = array(); $mi["_UE_MENU_VIEW"]["_UE_MENU_VIEWMYPROFILE"]=null;
				$this->_addMenuItem( $mi, _UE_MENU_VIEWMYPROFILE,cbSef($ue_userprofile_url), "",
				"","", _UE_MENU_VIEWMYPROFILE_DESC,"" );
			}
		}
		// ----- MESSAGES MENU -----
		// Send PMS
		if ( $_CB_framework->myId() != $user->id && $_CB_framework->myId() > 0 ) {
			global $_CB_PMS;
			$resultArray = $_CB_PMS->getPMSlinks($user->id, $_CB_framework->myId(), "", "", 1);
			if (count($resultArray) > 0) {
				foreach ($resultArray as $res) {
				 	if (is_array($res)) {
						$mi = array(); $mi["_UE_MENU_MESSAGES"][$res["caption"]]=null;
						$this->_addMenuItem( $mi, getLangDefinition($res["caption"]),cbSef($res["url"]), "",
						"","", getLangDefinition($res["tooltip"]),"" );
				 	}
				}
			}
		}

		// Send Email
		$emailHtml=getFieldValue('primaryemailaddress',$user->email,$user);
		if ($ueConfig['allow_email_display']!=4 && $_CB_framework->myId() != $user->id && $_CB_framework->myId() > 0) {
			switch ($ueConfig['allow_email_display']) {
				case 1:	// Display Email only
					$caption = $emailHtml;
					$url = "javascript:void(0);";
					$desc = _UE_MENU_USEREMAIL_DESC;
					break;
				case 2:	// Display Email with link:
					$caption = null;
					$url = $emailHtml;
					$desc = _UE_MENU_SENDUSEREMAIL_DESC;
					break;
				case 3:	// Display Email-to text with link to web-form:
					$caption = _UE_MENU_SENDUSEREMAIL;
					$url = $emailHtml;
					$desc = _UE_MENU_SENDUSEREMAIL_DESC;
					break;
			}
			$mi = array(); $mi["_UE_MENU_MESSAGES"]["_UE_MENU_SENDUSEREMAIL"]=null;
			$this->_addMenuItem( $mi, $caption, $url, "", "", "", $desc, "" );
		}
		// ----- CONNECTIONS MENU -----
		IF ($ueConfig['allowConnections'] && $_CB_framework->myId() > 0) {
			$ue_addConnection_url = $ue_base_url."&amp;act=connections&amp;task=addConnection&amp;connectionid=".$user->id;
			$ue_removeConnection_url = $ue_base_url."&amp;act=connections&amp;task=removeConnection&amp;connectionid=".$user->id;
			$ue_manageConnection_url = $ue_base_url."&amp;task=manageConnections";
			
			// Manage My Connections
			$mi = array(); $mi["_UE_MENU_CONNECTIONS"]["_UE_MENU_MANAGEMYCONNECTIONS"]=null;
			$this->_addMenuItem( $mi, _UE_MENU_MANAGEMYCONNECTIONS,cbSef($ue_manageConnection_url), "",
			"","", _UE_MENU_MANAGEMYCONNECTIONS_DESC,"" );
			
			if ( $_CB_framework->myId() != $user->id ) {
				$_CB_database->setQuery("SELECT COUNT(*) FROM #__comprofiler_members WHERE referenceid=" . (int) $_CB_framework->myId() . " AND memberid=" . (int) $user->id);
				$isConnection = $_CB_database->loadResult();
				if ($isConnection) {
					$_CB_database->setQuery("SELECT COUNT(*) FROM #__comprofiler_members WHERE referenceid=" . (int) $_CB_framework->myId() . " AND memberid=" . (int) $user->id." AND pending=0");
					$isApproved = $_CB_database->loadResult();
					$_CB_database->setQuery("SELECT COUNT(*) FROM #__comprofiler_members WHERE referenceid=" . (int) $_CB_framework->myId() . " AND memberid=" . (int) $user->id." AND accepted=1");
					$isAccepted = $_CB_database->loadResult();
				}
				if($isConnection==0) {
					$connectionurl=cbSef($ue_addConnection_url);
					if ( $ueConfig['useMutualConnections'] == 1 ) {
						$fmsg	  = "_UE_ADDCONNECTIONREQUEST";
						$fmsgdesc = _UE_ADDCONNECTIONREQUEST_DESC;
					} else {
						$fmsg	  = "_UE_ADDCONNECTION";
						$fmsgdesc = _UE_ADDCONNECTION_DESC;
					}
					if($ueConfig['conNotifyType']!=0) {
						$connectionurl="javascript:void(0)\" onclick=\"return overlib('"
						. str_replace(array("<",">"), array("&lt;","&gt;"),
						_UE_CONNECTIONINVITATIONMSG."<br /><form action=&quot;".$connectionurl
						."&quot; method=&quot;post&quot; id=&quot;connOverForm&quot; name=&quot;connOverForm&quot;>"._UE_MESSAGE
						."<br /><textarea cols=&quot;40&quot; rows=&quot;8&quot; name=&quot;message&quot;></textarea><br />"
						. "<input type=&quot;button&quot; class=&quot;inputbox&quot; onclick=&quot;cbConnSubmReq();&quot; value=&quot;"
						._UE_SENDCONNECTIONREQUEST."&quot; />&nbsp;&nbsp;"
						."<input type=&quot;button&quot; class=&quot;inputbox&quot; onclick=&quot;cClick();&quot;  value=&quot;"
						._UE_CANCELCONNECTIONREQUEST."&quot; /></form>")
						."', STICKY, CAPTION,'"
						.sprintf(_UE_CONNECTTO,htmlspecialchars(str_replace("'","&#039;",getNameFormat($user->name,$user->username,$ueConfig['name_format'])),ENT_QUOTES))
						."', CENTER,CLOSECLICK,CLOSETEXT,'"._UE_CLOSE_OVERLIB."',WIDTH,350, ANCHOR,'cbAddConn',ANCHORALIGN,'LR','UR');";
						// $flink="<a href=\"".$connectionurl."\" id=\"cbAddConn\" name=\"cbAddConn\" title=\"".$fmsgdesc."\">".getLangDefinition($fmsg)."</a>";
						$flink = $connectionurl."\" id=\"cbAddConn\" name=\"cbAddConn";	//BBTRYREMOVED: "\" title=\"".$fmsgdesc."\">".getLangDefinition($fmsg)."</a>";
					} else {
						$flink=$connectionurl;
					}
				} else {
					if ($isAccepted) {
						$connectionurl=cbSef($ue_removeConnection_url);
						if ($isApproved) {
							$fmsg = "_UE_REMOVECONNECTION";
							$fmsgdesc=_UE_REMOVECONNECTION_DESC;
						} else {
							$fmsg = "_UE_REVOKECONNECTIONREQUEST";
							$fmsgdesc=_UE_REVOKECONNECTIONREQUEST_DESC;
						}
						// $flink="<a href=\"".$connectionurl."\" onclick=\"return confirmSubmit();\" title=\"".$fmsgdesc."\">".getLangDefinition($fmsg)."</a>";
						$flink = $connectionurl."\" onclick=\"return confirmSubmit();"; //BBTRYREMOVED: \" title=\"".$fmsgdesc."\">".getLangDefinition($fmsg)."</a>";
					} else {
						/*
						$connectionurl=cbSef($ue_manageConnection_url);
						$fmsg = "_UE_MANAGECONNECTIONS";				//BB this is wrong here, unless non-accepted connections are also displayed there
						$fmsgdesc=_UE_MENU_MANAGEMYCONNECTIONS_DESC;
						$flink=$connectionurl;
						*/
						$fmsg = null;		// manage connections is already above, no need to repeat here !
					}
				}
				// Request/Add/Remove/Revoke Connection
				if ( $fmsg ) {
					$mi = array(); $mi["_UE_MENU_CONNECTIONS"][$fmsg]=null;
					$this->_addMenuItem( $mi, getLangDefinition($fmsg), $flink /*$connectionurl*/, "",
					"","", $fmsgdesc,"" );
				}
			}

		}
		// ----- MODERATE MENU -----
		if ( $_CB_framework->myId() == $user->id ) {
			// Request to unban:
			if($user->banned==1 && $this->cbUserIsModerator==0 && $ueConfig['allowUserBanning']==1) {
				$mi = array(); $mi["_UE_MENU_MODERATE"]["_UE_REQUESTUNBANPROFILE"]=null;
				$this->_addMenuItem( $mi, _UE_REQUESTUNBANPROFILE,cbSef($ue_unbanrequest_url), "",
				"","", _UE_MENU_REQUESTUNBANPROFILE_DESC,"" );
			}
		} else {
			// Report User:
			if($ueConfig['allowUserReports']==1 && $this->cbUserIsModerator==0 && $_CB_framework->myId() > 0) {
				$mi = array(); $mi["_UE_MENU_MODERATE"]["_UE_REPORTUSER"]=null;
				$this->_addMenuItem( $mi, _UE_REPORTUSER,cbSef($ue_reportuser_url), "",
				"","", _UE_MENU_REPORTUSER_DESC,"" );
			}
			// Approve/Reject Avatar & Ban/Unban profile & View User Reports:
			if($this->cbMyIsModerator==1 && $this->cbUserIsModerator==0) {

				$query = "SELECT COUNT(*) FROM #__comprofiler_userreports  WHERE reportedstatus=0 AND reporteduser=" . (int) $user->id;
				if(!$_CB_database->setQuery($query)) print $_CB_database->getErrorMsg();
				$userreports = $_CB_database->loadResult();

				$query = "SELECT COUNT(*) FROM #__comprofiler_userreports  WHERE reporteduser=" . (int) $user->id;
				if(!$_CB_database->setQuery($query)) print $_CB_database->getErrorMsg();
				$userreportsAllTimes = $_CB_database->loadResult();

				if(!($user->avatar=='' || $user->avatar==null)) {
					if($user->avatarapproved==0) {
						// Approve Image
						$mi = array(); $mi["_UE_MENU_MODERATE"]["_UE_APPROVE_IMAGE"]=null;
						$this->_addMenuItem( $mi, _UE_APPROVE_IMAGE,cbSef($ue_approve_image_url), "",
						"","", _UE_MENU_APPROVE_IMAGE_DESC,"" );
					}
					// Reject Image
					$mi = array(); $mi["_UE_MENU_MODERATE"]["_UE_REJECT_IMAGE"]=null;
					$this->_addMenuItem( $mi, _UE_REJECT_IMAGE,cbSef($ue_reject_image_url), "",
					"","", _UE_MENU_REJECT_IMAGE_DESC,"" );
				}
				if($ueConfig['allowUserBanning']==1) {
					if($user->banned!=0 ) {
						// unban profile
						$mi = array(); $mi["_UE_MENU_MODERATE"]["_UE_UNBANPROFILE"]=null;
						$this->_addMenuItem( $mi, _UE_UNBANPROFILE,cbSef($ue_unban_url), "",
						"","", _UE_MENU_UNBANPROFILE_DESC,"" );
					} else {
						// ban profile
						$mi = array(); $mi["_UE_MENU_MODERATE"]["_UE_BANPROFILE"]=null;
						$this->_addMenuItem( $mi, _UE_BANPROFILE,cbSef($ue_ban_url), "",
						"","", _UE_MENU_BANPROFILE_DESC,"" );
					}
					if( $user->bannedby ) {
						// ban history
						$mi = array(); $mi["_UE_MENU_MODERATE"]["_UE_MENU_BANPROFILE_HISTORY"]=null;
						$this->_addMenuItem( $mi, _UE_MENU_BANPROFILE_HISTORY,cbSef($ue_banhistory_url), "",
						"","", _UE_MENU_BANPROFILE_HISTORY_DESC,"" );
					}
				}
				if($ueConfig['allowUserReports']==1 && $userreports>0) {
					// view user reports
					$mi = array(); $mi["_UE_MENU_MODERATE"]["_UE_VIEWUSERREPORTS"]=null;
					$this->_addMenuItem( $mi, _UE_VIEWUSERREPORTS,cbSef($ue_viewuserreports_url), "",
					"","", _UE_MENU_VIEWUSERREPORTS_DESC,"" );
				} elseif($ueConfig['allowUserReports']==1 && $userreportsAllTimes>0) {
					// view user reports
					$mi = array(); $mi["_UE_MENU_MODERATE"]["_UE_VIEWUSERREPORTS"]=null;
					$this->_addMenuItem( $mi, _UE_MOD_MENU_VIEWOLDUSERREPORTS,cbSef($ue_viewOlduserreports_url), "",
					"","", _UE_MOD_MENU_VIEWOLDUSERREPORTS_DESC,"" );
				}
			}
		}
		// Test example:
		/*
		$mi = array(); $mi["_UE_MENU_CONNECTIONS"]["duplique"]=null;
		$this->addMenu( array(	"position"	=> "menuBar" ,		// "menuBar", "menuList"
									"arrayPos"	=> $mi ,
									"caption"	=> _UE_MENU_MANAGEMYCONNECTIONS ,
									"url"		=> cbSef($ue_manageConnection_url) ,		// can also be "<a ....>" or "javascript:void(0)" or ""
									"target"	=> "" ,	// e.g. "_blank"
									"img"		=> null ,	// e.g. "<img src='plugins/user/myplugin/images/icon.gif' width='16' height='16' alt='' />"
									"alt"		=> null ,	// e.g. "text"
									"tooltip"	=> _UE_MENU_MANAGEMYCONNECTIONS_DESC ,
									"keystroke"	=> null ) );	// e.g. "P"
		*/
	}
	/**
	* Generates the HTML to display the user profile tab
	* @param  moscomprofilerTab   $tab       the tab database entry
	* @param  moscomprofilerUser  $user      the user being displayed
	* @param  int                 $ui        1 for front-end, 2 for back-end
	* @return mixed                          either string HTML for tab content, or false if ErrorMSG generated
	*/
	function getDisplayTab($tab,$user,$ui) {
		global $_CB_framework, $_PLUGINS, $_CB_OneTwoRowsStyleToggle;

		$params=$this->params;

		if ( ! $this->menuBar ) {		// in case menu is called before onBeforeUserProfileDisplay
			$this->prepareMenu( $user, $ui, isModerator( $user->id ), isModerator( $_CB_framework->myId() ) );
		}

		// add plugins' menus:
		$pm = $_PLUGINS->getMenus();
		for ($i=0, $pmc=count($pm); $i<$pmc; $i++) {
			if($pm[$i]['position'] == "menuBar") {
				$this->menuBar->addObjectItem( $pm[$i]['arrayPos'], $pm[$i]['caption'],
				isset($pm[$i]['url'])	?$pm[$i]['url']		:"",
				isset($pm[$i]['target'])?$pm[$i]['target']	:"",
				isset($pm[$i]['img'])	?$pm[$i]['img']		:null,
				isset($pm[$i]['alt'])	?$pm[$i]['alt']		:null,
				isset($pm[$i]['tooltip'])?$pm[$i]['tooltip']:null,
				isset($pm[$i]['keystroke'])?$pm[$i]['keystroke']:null );

			}
		}
		// display Menu:
		switch ($params->get('menuFormat', 'menuBar')) {
			case "no":
				$return = "";
				
				$return .= $this->_writeTabDescription( $tab, $user, 'cbUserMenuDescription' );

				break;
			case "menuUL":
				$return = "";
				
				$return .= $this->_writeTabDescription( $tab, $user, 'cbUserMenuDescription' );

				// $mi = array(); $mi["SEPAR"]["SEPAR"]=null;
				// $this->menuBar->addSeparator($mi);
				$idCounter						=	$_CB_OneTwoRowsStyleToggle;
				$tableContent					=	$this->menuBar->displayMenu($idCounter);
				if ( $tableContent != '' ) {
					$_CB_OneTwoRowsStyleToggle	=	($idCounter&1 ? 2 : 1);
					$return						.=	'<div class="cbMenuList">' . $tableContent . '</div>';
				}
				break;
			case "menuList":
			case "menuDivs":
				$return = "";
				
				$return .= $this->_writeTabDescription( $tab, $user, 'cbUserMenuDescription' );

				// $mi = array(); $mi["SEPAR"]["SEPAR"]=null;
				// $this->menuBar->addSeparator($mi);
				$idCounter						=	$_CB_OneTwoRowsStyleToggle;
				$tableContent					=	$this->menuBar->displayMenu($idCounter);
				if ( $tableContent != '' ) {
					$_CB_OneTwoRowsStyleToggle	=	($idCounter&1 ? 2 : 1);
					$return						.=	'<table class="cbStatusList">' . $tableContent . '</table>';			//TBD in CB 1.3 : rename to cbMenuList for consistency
				}
				break;
			case "menuBar":
			default:
				$idCounter = 1;
				$return = $this->menuBar->displayMenu($idCounter);
				
				$return .= $this->_writeTabDescription( $tab, $user , 'cbUserMenuDescription' );
				break;
		}
		return $return;
	}
}	// end class getMenuTab

/**
* Core Status Tab Class for handling the CB User Status Display and the CB tab api
* @package	Community Builder
* @author	Beat
*/
class getStatusTab extends cbTabHandler {
	/**	@var	generic Menu List object	*/
	var $menuList;
	var $ui;
	var $cbMyIsModerator;
	var $cbUserIsModerator;
	/*
	* Constructor
	*/
	function getStatusTab() {
		$this->cbTabHandler();
	}
	/**
	* Method is called before the user profile is viewed (onPrepareMenus event)
	*
	* @param  moscomprofilerUser  holds the core mambo user data
	*/
	function prepareStatus( &$user ) {
		global $_CB_framework;

		$this->ui					=	$_CB_framework->getUi();
		$this->cbUserIsModerator	=	isModerator( $user->id );
		$this->cbMyIsModerator		=	isModerator( $_CB_framework->myId() );
		$params						=	$this->params;
		switch ($params->get('statusFormat', 'menuList')) {
			case "menuBar":
				$this->menuList = new cbMenuBar(1);
				break;
			case "menuUL":
				$this->menuList = new cbMenuUL(1);
				break;
			case "menuDivs":
				$this->menuList = new cbMenuDivs(1);
				break;
			case "menuList":
			default:
				$this->menuList = new cbMenuList(1);
				break;
		}
		return $this->menuList->outputScripts(1);
	}
	/**
	 * Adds a menu item to the menu tree array to be displayed later anywhere
	 *
	 * @param string $arrayPos		array of array (single item each time)
	 * @param string $caption
	 * @param string $url
	 * @param string $target
	 * @param string $img
	 * @param string $alt
	 * @param string $tooltip
	 * @param char	 $keystroke
	 */
	function _addMenuItem( $arrayPos, $caption, $url="", $target="", $img=null, $alt=null, $tooltip=null, $keystroke=null ) {
		$this->addMenu( array(	"position"	=> "menuList" ,		// "menuBar", "menuList"
							"arrayPos"	=> $arrayPos ,
							"caption"	=> $caption ,
							"url"		=> $url ,
							"target"	=> $target,
							"img"		=> $img ,
							"alt"		=> $alt ,
							"tooltip"	=> $tooltip,
							"keystroke"	=> $keystroke ) );
	}
	/**
	* Generates the menu and user status to display on the user profile by calling back $this->addMenu
	* @param  moscomprofilerTab   $tab       the tab database entry
	* @param  moscomprofilerUser  $user      the user being displayed
	* @param  int                 $ui        1 for front-end, 2 for back-end
	* @return boolean                        either true, or false if ErrorMSG generated
	*/
	function getMenuAndStatus($tab,$user,$ui) {
/*
		global $_CB_database, $ueConfig;

		$params   = $this->params;

		$showtime = ( $params->get( 'showtime', '1' ) == '1' );
		//------------------- User Status items for User Status Window:
		// Hits
		if ($params->get('hits', '1')==1) {
			$mi = array(); $mi["_UE_MENU_STATUS"]["_UE_HITS"]["_UE_HITS"]=null;
			$this->_addMenuItem( $mi, $user->hits,"", "",
			"","", _UE_HITS_DESC,"" );
		}
		// Online Status
		if ($ueConfig['allow_onlinestatus']==1 && $params->get('online', '1')==1) {
			$_CB_database->setQuery( "SELECT COUNT(*) FROM #__session WHERE userid =". (int) $user->id . " AND (guest = 0) AND (NOT ( usertype is NULL OR usertype = '' ))" );
			$isonline = $_CB_database->loadResult();
			$mi = array(); $mi["_UE_MENU_STATUS"]["_UE_ONLINESTATUS"]["_UE_ONLINESTATUS"]=null;
			$this->_addMenuItem( $mi, ($isonline > 0) ? _UE_ISONLINE : _UE_ISOFFLINE,"", "",
			"","", _UE_ONLINESTATUS_DESC,"" );
		}
		// Member Since
		if ($params->get('membersince', '1')==1) {
			$mi = array(); $mi["_UE_MENU_STATUS"]["_UE_MEMBERSINCE"]["_UE_MEMBERSINCE"]=null;
			$dat = cbFormatDate( $user->registerDate, 1, $showtime );
			if (!$dat) $dat="?";
			$this->_addMenuItem( $mi, $dat,"", "",
			"","", _UE_MEMBERSINCE_DESC,"" );
		}
		// Last Online
		if ($params->get('lastonline', '1')==1) {
			$mi = array(); $mi["_UE_MENU_STATUS"]["_UE_LASTONLINE"]["_UE_LASTONLINE"]=null;
			$dat = cbFormatDate( $user->lastvisitDate, 1, $showtime );
			if (!$dat) $dat=_UE_NEVER;
			$this->_addMenuItem( $mi, $dat,"", "",
			"","", _UE_LASTONLINE_DESC,"" );
		}
		// Last Updated
		if ($params->get('lastupdated', '1')==1) {
			$mi = array(); $mi["_UE_MENU_STATUS"]["_UE_LASTUPDATEDON"]["_UE_LASTUPDATEDON"]=null;
			$dat = cbFormatDate( $user->lastupdatedate, 1, $showtime );
			if (!$dat) $dat=_UE_NEVER;
			$this->_addMenuItem( $mi, $dat,"", "",
			"","", _UE_LASTUPDATEDON_DESC,"" );
		}
*/
	/*	// Test example: Member Since:
		$mi = array(); $mi["_UE_MENU_STATUS"]["_UE_MEMBERSINCE"]["dupl"]=null;
		$dat = cbFormatDate( $user->registerDate, 1, $showtime );
		if (!$dat) $dat="?";
		$this->addMenu( array(	"position"	=> "menuList" ,		// "menuBar", "menuList"
									"arrayPos"	=> $mi ,
									"caption"	=> $dat ,
									"url"		=> "" ,		// can also be "<a ....>" or "javascript:void(0)" or ""
									"target"	=> "" ,	// e.g. "_blank"
									"img"		=> null ,	// e.g. "<img src='plugins/user/myplugin/images/icon.gif' width='16' height='16' alt='' />"
									"alt"		=> null ,	// e.g. "text"
									"tooltip"	=> _UE_MEMBERSINCE_DESC ,
									"keystroke"	=> null ) );	// e.g. "P"
	*/
		return true;
	}
	/**
	* Generates the HTML to display the user profile tab
	* @param  moscomprofilerTab   $tab       the tab database entry
	* @param  moscomprofilerUser  $user      the user being displayed
	* @param  int                 $ui        1 for front-end, 2 for back-end
	* @return mixed                          either string HTML for tab content, or false if ErrorMSG generated
	*/
	function getDisplayTab($tab,$user,$ui) {
		global $_CB_framework, $_PLUGINS, $_CB_OneTwoRowsStyleToggle;

		$params=$this->params;

		if ( ! $this->menuList ) {		// in case status is called before onBeforeUserProfileDisplay
			$this->prepareStatus( $user, $ui, isModerator( $user->id ), isModerator( $_CB_framework->myId() ) );
		}

		// add plugins' status:
		$pm = $_PLUGINS->getMenus();
		for ($i=0, $pmc=count($pm); $i<$pmc; $i++) {
			if($pm[$i]['position'] == "menuList") {
				$this->menuList->addObjectItem( $pm[$i]['arrayPos'], $pm[$i]['caption'],
				isset($pm[$i]['url'])	?$pm[$i]['url']		:"",
				isset($pm[$i]['target'])?$pm[$i]['target']	:"",
				isset($pm[$i]['img'])	?$pm[$i]['img']		:null,
				isset($pm[$i]['alt'])	?$pm[$i]['alt']		:null,
				isset($pm[$i]['tooltip'])?$pm[$i]['tooltip']:null,
				isset($pm[$i]['keystroke'])?$pm[$i]['keystroke']:null );
			}
		}

		// display User Status window:
		// display Menu:
		switch ($params->get('statusFormat', 'menuList')) {
			case "no":
				$return = "";
				$return .= $this->_writeTabDescription( $tab, $user, 'cbUserStatusDescription' );
				break;
			case "menuBar":
				$idCounter = 1;
				$return = $this->menuList->displayMenu($idCounter);
				$return .= $this->_writeTabDescription( $tab, $user, 'cbUserStatusDescription' );
				break;
			case "menuUL":
				$return = "";
				$return .= $this->_writeTabDescription( $tab, $user, 'cbUserStatusDescription' );

				$idCounter						=	$_CB_OneTwoRowsStyleToggle;
				$tableContent					=	$this->menuList->displayMenu($idCounter);
				if ( $tableContent != '' ) {
					$_CB_OneTwoRowsStyleToggle	=	($idCounter&1 ? 2 : 1);
					$return						.=	'<div class="cbStatusList">' . $tableContent . '</div>';
				}
				break;
			case "menuList":
			case "menuDivs":
				default:
				$return = "";
				$return .= $this->_writeTabDescription( $tab, $user, 'cbUserStatusDescription' );

				$idCounter						=	$_CB_OneTwoRowsStyleToggle;
				$tableContent					=	$this->menuList->displayMenu($idCounter);
				if ( $tableContent != '' ) {
					$_CB_OneTwoRowsStyleToggle	=	($idCounter&1 ? 2 : 1);
					$return						.=	'<table class="cbStatusList">' . $tableContent . '</table>';
				}
				break;
		}
		return $return;
	}
}	// end class getStatusTab

?>