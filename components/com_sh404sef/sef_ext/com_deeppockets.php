<?php
/**
 * sh404SEF support for com_content component.
 * by Bob Janes (GreyHead) - 2007
 * few changes by shumisha - march 2008
 * info@greyhead.net
 * 
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

$debug_plugin = false;

// ------------------  standard plugin initialize function - don't change
global $sh_LANG;
$sefConfig = & shRouter::shGetConfig();
// get DB
$database =& JFactory::getDBO();

$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
// ------------------  standard plugin initialize function - don't change

if (!defined(SH404SEF_DP_INSERT_ALL_CATEGORIES)) {
	define ('SH404SEF_DP_INSERT_ALL_CATEGORIES', 2);   // set to 0 to have no cat inserted  /234-ContentTitle/
								// set to 1 to have only last cat added /123-CatTitle/234-ContentTitle/
								// set to 2 to have all nested cats inserted /456-Cat1Title/123-Cat2Title/234-ContentTitle/

	define ('SH404SEF_DP_INSERT_CAT_ID', 0);		// if non-zero, id of each cat will be inserted in the url /123-CatTitle/
	define ('SH404SEF_DP_INSERT_CONTENT_ID', 0);      // if non-zero, id of each content element will be inserted in url /234-ContentTitle/
	define ('SH404SEF_DP_USE_JOOMLA_URL', 0);    // if non-zero, DP links to content element will be identical to those
	                                             // generated for Joomla regular content - usefull if this content can also be
	                                             // accessed outside of DP, to avoid duplicate content penalties 
}

if( !function_exists( 'dp_sef_get_category_array' ) ){
  function dp_sef_get_category_array( $category_id, $option, $shLangName ){
  
    global $shMosConfig_locale;
    $sefConfig = & shRouter::shGetConfig();
    
    // get DB
    $db =& JFactory::getDBO();
  
	  static $tree = null;  // V 1.2.4.m  $tree must an array based on current language
	  $title=array();
	  
	  if (SH404SEF_DP_INSERT_ALL_CATEGORIES == 0) return $title;
	  
	  if(empty($tree[$shMosConfig_lang])){  // load up all cat details
  		$q  = 'SELECT title, id, parent_id FROM #__dp_categories' ;
	  	$q .= "\n WHERE published <> '0';"; // V x
	  	$db->setQuery( $q );
	  	if (!shTranslateUrl($option, $shLangName))  // V 1.2.4.m
	  	  $tree[$shMosConfig_lang] = $db->loadObjectList( 'id', false);  // V 1.2.4.m if Joomfish, and don't translate
	  	                                                    // use special call of loadObjectList, asking JF not to translate
	  	else  
		    $tree[$shMosConfig_lang] = $db->loadObjectList( 'id' );
  	}
	  if (SH404SEF_DP_INSERT_ALL_CATEGORIES == 1)    // only one category
	    $title[] = (SH404SEF_DP_INSERT_CAT_ID != 0 ? 
                    $tree[$shMosConfig_lang][ $category_id ]->id.$sefConfig->replacement : '')   
                 .$tree[$shMosConfig_lang][ $category_id ]->title; 
    else 
      do {               // all categories and subcategories.  
		    $title[] = (SH404SEF_DP_INSERT_CAT_ID ? 
          $tree[$shMosConfig_lang][ $category_id ]->id.$sefConfig->replacement : '') // to category
          .$tree[$shMosConfig_lang][ $category_id ]->title;                           // will always be unique
		    $category_id = $tree[$shMosConfig_lang][ $category_id ]->parent_id;
	    } while( $category_id != 0 );
	  return array_reverse( $title );
  }
}


$task = isset($task) ? @$task : null;

// shumisha : insert component name from menu
$shDPName = shGetComponentPrefix($option);
if (!empty($shDPName))
	$title[] = $shDPName;
else {	
	$shDPName = empty($shDPName) ?  getMenuTitle($option, null, @$Itemid, null, $shLangName ) : $shDPName;
	$shDPName = $shDPName == '/' ? 'DP':$shDPName;
}

switch ($task){
    case 'catShow':
      if ( !empty($id) ) {
	       $categoryTitle =  dp_sef_get_category_array( $id, $option, $shLangName ); 
	       if ( !empty($categoryTitle) ) {
		        if (empty($title))  // there may already be component name in $title
				$title = $categoryTitle;
			else 
			         $title = array_merge($title, $categoryTitle);
			$title[] = "/";	 
	       }
	    }  
      shRemoveFromGETVarsList('id');
      shRemoveFromGETVarsList('task');
    break;
    
	case 'catContShow':  // showing regular content element
/*		if (!empty($id)) {
			if (!empty($title))
				$title = array_merge($title, sef_404::getContentTitles('view',$id, (isset($Itemid) ? @$Itemid : null), $shLangName)); // V 1.2.4.q added forced language
			else $title = sef_404::getContentTitles('view',$id,(isset($Itemid) ? @$Itemid : null), $shLangName);
			shRemoveFromGETVarsList('id');
			shRemoveFromGETVarsList('task');
			if (!empty($cat))
				shRemoveFromGETVarsList('cat');
		}
	break; */
	
    case 'contShow':
    default:
	if (!empty($cat)) {
		$categoryTitle =  dp_sef_get_category_array( &$database, $cat, $option, $shLangName ); 
	       if ( !empty($categoryTitle) ) {
		        if (empty($title))  // there may already be component name in $title
				$title = $categoryTitle;
			else 
			         $title = array_merge($title, $categoryTitle);
			$title[] = "/";	 
	       }
	}
      if ( !empty($id) ) {
        $sql = '
            SELECT id, cat, title, name 
                FROM #__deeppockets 
                WHERE id = '.$id;
        $database->setQuery($sql);
        $contentTitle = $database->loadObject(  );
        if ( $debug_plugin ) {
            echo "contentTitle: ";print_r($contentTitle);echo "<br />";
        }
      }
      if ( !empty($contentTitle) ) {
        if (empty($cat) && !empty($contentTitle->cat)) {
          $categoryTitle =  dp_sef_get_category_array( $contentTitle->cat, $option, $shLangName ); 
          if ( $categoryTitle ) {
            if (empty($title))  // there may already be component name in $title
              $title = $categoryTitle;
	           else 
		          $title = array_merge($title, $categoryTitle);
          }
        }
        if ( !empty($contentTitle->title)) {
          $title[] = (SH404SEF_DP_INSERT_CONTENT_ID ? $id.$sefConfig->replacement:'').$contentTitle->title;
        } elseif ( $contentTitle->name != '' ) {
          $title[] = (SH404SEF_DP_INSERT_CONTENT_ID ? $id.$sefConfig->replacement:'').$contentTitle->name;
        }
      }
      shRemoveFromGETVarsList('id');
      shRemoveFromGETVarsList('task');
      if (!empty($cat))
	shRemoveFromGETVarsList('cat');
      break;
}

if (empty($title)) {
	$title[] = $shDPName;
	$title[] = "/";
}	

if ( $debug_plugin ) {
    echo "title: ";print_r($title);echo "<br />";
}

shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');
if (!empty($Itemid))
  shRemoveFromGETVarsList('Itemid');
// optional removal of limit and limitstart
if (!empty($limit))      // use empty to test $limit as $limit is not allowed to be zero
  shRemoveFromGETVarsList('limit');
if (isset($limitstart))  // use isset to test $limitstart, as it can be zero
  shRemoveFromGETVarsList('limitstart');

// ------------------  standard plugin finalize function - don't change
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString,
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
      (isset($shLangName) ? @$shLangName : null));
}
// ------------------  standard plugin finalize function - don't change
?>
