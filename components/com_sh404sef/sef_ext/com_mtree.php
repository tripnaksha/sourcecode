<?php
/**
 * sh404SEF support for MosetsTree component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_mtree.php 866 2009-01-17 14:05:21Z silianacom-svn $
 * {shSourceVersionTag: Version x - 2007-09-20}
 * Based on mosets.com extension for SEF Advance by Lee Cher Yeong <mtree@mosets.com>  
 */
 
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = & shRouter::shGetConfig();  
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------

// ------------------  load language file - adjust as needed ----------------------------------------
$shLangIso = shLoadPluginLanguage( 'com_mtree', $shLangIso, '_MT_SEF_DETAILS');
// ------------------  load language file - adjust as needed ----------------------------------------


	/********************************************************
	* Utility Functions
	********************************************************/

	/***
	* Append Categories' Pathway
	*/
	if (!function_exists('shAppendCat')) {
	function shAppendCat( $cat_id, $option, $shLangName ) {
	  $sefConfig = & shRouter::shGetConfig();
		$sef = array();
		$pathWay = new mtPathWay( $cat_id );
		$pathway_ids = $pathWay->getPathWay( $cat_id );
		switch ($sefConfig->shMTreeInsertCategories) {
		  case '1':  // only last cat 
		    if ( $cat_id > 0 ) {  //this is not root we must use this cat
		  	  $sef[] = ($sefConfig->shMTreeInsertCategoryId ? $cat_id.$sefConfig->replacement:'').$pathWay->getCatName( $cat_id ) ;
		    } //else // this is root, don't add cat name  
        shRemoveFromGETVarsList('cat_id');          
		  break;
		  case '0': // if no cat, we still put them all. This param only applies to listing links
		  case '2':  // we want all cats
  		  foreach( $pathway_ids AS $id ) {
		  	  $sef[] = ($sefConfig->shMTreeInsertCategoryId ? $id.$sefConfig->replacement:'')
                    .$pathWay->getCatName( $id );
		    }
  		  // If curreny category is not root, append to sefstring
	  	  if ( $cat_id > 0 )
		  	  $sef[] = ($sefConfig->shMTreeInsertCategoryId ? $cat_id.$sefConfig->replacement:'')
                    .$pathWay->getCatName( $cat_id ) ;
        shRemoveFromGETVarsList('cat_id');            
      break;
    }
		return $sef;
	}
}
	/***
	* Append Listing "filename"
	*/
	if (!function_exists('shAppendListing')) {
	function shAppendListing( $link_name, $link_id, $add_details=false, $shLangIso, $option, $shLangName) {
	  global $sh_LANG;
	  $sefConfig = & shRouter::shGetConfig();
		$sef = array();
		
		if( $sefConfig->shMTreeInsertListingId ) {
			if( !$sefConfig->shMTreePrependListingId) {
				$sef[] = ($sefConfig->shMTreeInsertListingName? $link_name . $sefConfig->replacement : ''). $link_id;
			} else {
				$sef[] = $link_id . ($sefConfig->shMTreeInsertListingName? $sefConfig->replacement.$link_name : '');
			}
		} else {
		  if ($sefConfig->shMTreeInsertListingName)	$sef[] = $link_name;
		}

		if( $add_details ) {
			$sef[]= $sh_LANG[$shLangIso]['_MT_SEF_DETAILS'];
		}

    if ($sefConfig->shMTreeInsertListingName || $sefConfig->shMTreeInsertListingId)
      shRemoveFromGETVarsList('link_id');
		return $sef;
	}
  }
	/***
	* Return value from shAppendCat + shAppendListing
	*/
	if (!function_exists('shAppendCatListing')) {
	function shAppendCatListing( $mtLink, $add_extension=true, $shLangIso, $option, $shLangName) {
			return array_merge( shAppendCat( $mtLink->cat_id, $option, $shLangName),
                          shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName));
	}
  }

	/***
	* Routine function to restrive $limit & $limitvalue from query string
	*
	* @param int A referenced $limit - number of results shown per page
	* @param int A referenced $limitstart - the record number to start display
	* @param string Query string
	*/
	if (!function_exists('shGetLimits')) {
	function shGetLimits( &$limit, &$limitstart, $string ) {
		// limit
		$temp = split('&limit=', $string);
		if (count($temp) >= 2) {
			$temp = split("&", $temp[1]);
			$limit = $temp[0];
		} else {
			$limit = '';
		}

		// limitstart
		$temp = split('&limitstart=', $string);
		if (count($temp) >= 2) {
			$temp = split("&", $temp[1]);
			$limitstart = $temp[0];
		} else {
			$limitstart = '';
		}

		if ( $limit <> '' && $limitstart <> '' ) {
			return true;
		} else {
			return false;
		}

	}
  }


# Include the config file
require_once( sh404SEF_ABS_PATH.'components/com_mtree/mtree.class.php' );

# Inlcude back-end class
require_once( sh404SEF_ABS_PATH.'administrator/components/com_mtree/admin.mtree.class.php' );

    $task = isset($task) ? $task : null;
    $Itemid = isset($Itemid) ? $Itemid : null;  // V 1.2.4.t
    $link_id = isset($link_id) ? $link_id : null;
    $cat_id = isset($cat_id) ? $cat_id : null;
    $user_id = isset($user_id) ? $user_id : null;


    // shumisha : insert component name from menu
    $shMTreeName = shGetComponentPrefix($option);
    $shMTreeName = empty($shMTreeName) ?  getMenuTitle($option, null, $Itemid, null, $shLangName )
                                                     : $shMTreeName;
    $shMTreeName = (empty($shMTreeName) || $shMTreeName == '/') ? 'Directory':$shMTreeName; // V 1.2.4.t
    if ($sefConfig->shInsertMTreeName && !empty($shMTreeName)) $title[] = $shMTreeName;  

switch ($task) {

    # List Categories (listcats)
    case 'listcats' :
      if ($cat_id == 0) {  // V 1.2.4.t 23/08/2007 18:20:20
        if (empty( $title))
          $title[] = $shMTreeName;
        shRemoveFromGETVarsList('cat_id');  
      } else {    
        $tmp = shAppendCat( $cat_id, $option, $shLangName);
  			if (empty($title)) 
          $title = $tmp;
        else
          $title = array_merge ($title, $tmp);  
      }    
			$title[] = '/';
    break;

		# My Listing
		case 'mylisting' : 
			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_MYLISTING'];
		break;

		# Featured Listing
		case 'listfeatured':
			if (empty($title))
        $title = shAppendCat( $cat_id, $option, $shLangName);
      else
        $title = array_merge ($title, shAppendCat( $cat_id, $option, $shLangName)); 
			$title[] =  $sh_LANG[$shLangIso]['_MT_SEF_FEATUREDLISTING'] . '/';
		break;

		# New/Latest Listing
		case 'listnew': 
			if (empty($title))
        $title = shAppendCat( $cat_id, $option, $shLangName);
      else
        $title = array_merge ($title, shAppendCat( $cat_id, $option, $shLangName)); 
			$title[] =  $sh_LANG[$shLangIso]['_MT_SEF_NEWLISTING'] . '/';
		break;

		# Popular Listing
		case 'listpopular':
			if (empty($title))
        $title = shAppendCat( $cat_id, $option, $shLangName);
      else
        $title = array_merge ($title, shAppendCat( $cat_id, $option, $shLangName)); 
			$title[] =  $sh_LANG[$shLangIso]['_MT_SEF_POPULARLISTING'];
		break;

		# Most Rated Listing
		case 'listmostrated':
			if (empty($title))
        $title = shAppendCat( $cat_id, $option, $shLangName);
      else
        $title = array_merge ($title, shAppendCat( $cat_id, $option, $shLangName)); 
			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_MOSTRATEDLISTING'];
		break;

		# Top Rated Listing
		case 'listtoprated':
			if (empty($title))
        $title = shAppendCat( $cat_id, $option, $shLangName);
      else
        $title = array_merge ($title, shAppendCat( $cat_id, $option, $shLangName)); 
			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_TOPRATEDLISTING'];
		break;

		# Most Reviewed Listing
		case 'listmostreview': 
			if (empty($title))
        $title = shAppendCat( $cat_id, $option, $shLangName);
      else
        $title = array_merge ($title, shAppendCat( $cat_id, $option, $shLangName)); 
			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_MOSTREVIEWEDLISTING'];
		break;

		case 'listalpha' :
			if (empty($title))
				$title = shAppendCat( $cat_id, $option, $shLangName);
			else
				$title = array_merge ($title, shAppendCat( $cat_id, $option, $shLangName)); 

			//$title[] = $sh_LANG[$shLangIso]['_MT_SEF_LISTALPHA'];
			$shStartPage = isset($alpha) ? $alpha : (isset($start) ? $start : null); // Mtree changed first page number
															// as Jooma 1.5.x alredy uses $start
			// Get start alphabet
			if ($shStartPage == '0')
				$title[] = '0-9';
			else 
				$title[] = $shStartPage;
			$title[] = '/';
		break;		

		# Advanced Search Results
		case 'advsearch2' :
			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_ADVSEARCH2'];

			// Get search id
			//$search_id = shGetID('search',$string);
    break;
    
		# Advanced Search
		case 'advsearch' :
			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_ADVSEARCH'];
		break;
		

		# View All listing from Owner
		case 'viewowner' :
			if ($sefConfig->shMTreeInsertUserName) {
			  $database->setQuery( "SELECT name FROM #__users WHERE id='".$user_id."' AND block='0'" );
			  $username = $database->loadResult();
			} else $username = '';  

			if ( !empty($username) ) {
				$title[] = ($sefConfig->shMTreeInsertUserId ? $user_id.$sefConfig->replacement : '').$username;
        $title[] = $sh_LANG[$shLangIso]['_MT_SEF_OWNER'];
        if (isset($user_id)) shRemoveFromGETVarsList('user_id'); 
			}

			// TODO - Does not append further virtual path if username does not exists. mtree.php
			//				should check if user is not block / exists.
		break;
			
		# View Listing
		case 'viewlink' :
		  if (!empty($link_id)) {
  			$mtLink = new mtLinks( $database );
  			$mtLink->load( $link_id );
  			if ($sefConfig->shMTreeInsertCategories > 0) {
  			  if (empty($title))
            $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName)); 
        }
  			if( shGetLimits( $limit, $limitstart, $string ) ) {
  				//	http://example.com/c/mtree/Computer/Games/Donkey_Kong/reviews23/
  				
  				if (empty($title))
            $title = shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName));
  				$title[] = $sh_LANG[$shLangIso]['_MT_SEF_REVIEWS_PAGE'];
  			
  			} else {
  				//	http://example.com/c/mtree/Computer/Games/Donkey_Kong/details/
  				if (empty($title))
            $title = shAppendListing( $mtLink->link_name, $mtLink->link_id, true, $shLangIso, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendListing( $mtLink->link_name, $mtLink->link_id, true, $shLangIso, $option, $shLangName));
  			}
    	} else $dosef = false;	
		break;

		# Write Review
		case 'writereview' :
		  if (!empty($link_id)) {
  			$mtLink = new mtLinks( $database );
  			$mtLink->load( $link_id );
  			if ($sefConfig->shMTreeInsertCategories > 0) {
  			  if (empty($title))
            $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName)); 
        }
  			if (empty($title))
          $title = shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName)); 
  			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_REVIEW'];
			} else $dosef = false;
		break;

		# Rating
    case 'rate' :
		  if (!empty($link_id)) {
        $mtLink = new mtLinks( $database );
        $mtLink->load( $link_id );
  			if ($sefConfig->shMTreeInsertCategories > 0) {
  			  if (empty($title))
            $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName)); 
        }
  			if (empty($title))
          $title = shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName));
  			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_RATE'];
    	} else $dosef = false;
		break;

		# RECOMMEND
		case 'recommend' :
		  if (!empty($link_id)) {
        $mtLink = new mtLinks( $database );
        $mtLink->load( $link_id );
  			if ($sefConfig->shMTreeInsertCategories > 0) {
  			  if (empty($title))
            $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName)); 
        }
  			if (empty($title))
          $title = shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName));
  			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_RECOMMEND'];
    	} else $dosef = false;
		break;

		# CONTACT OWNER
		case 'contact' :
		  if (!empty($link_id)) {
        $mtLink = new mtLinks( $database );
        $mtLink->load( $link_id );
  			if ($sefConfig->shMTreeInsertCategories > 0) {
  			  if (empty($title))
            $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName)); 
        }
  			if (empty($title))
          $title = shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName));
  			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_CONTACT'];
    	} else $dosef = false;
		break;

		# REPORT LISTING
		case 'report' :
		  if (!empty($link_id)) {
        $mtLink = new mtLinks( $database );
        $mtLink->load( $link_id );
  			if ($sefConfig->shMTreeInsertCategories > 0) {
  			  if (empty($title))
            $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName)); 
        }
  			if (empty($title))
          $title = shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName));
  			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_REPORT'];
    	} else $dosef = false;
		break;

		# CLAIM LISTING
		case 'claim' :
		  if (!empty($link_id)) {
   			$mtLink = new mtLinks( $database );
   			$mtLink->load( $link_id );
  			if ($sefConfig->shMTreeInsertCategories > 0) {
  			  if (empty($title))
            $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName)); 
        }
  			if (empty($title))
          $title = shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName));
  			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_CLAIM'];
    	} else $dosef = false;
		break;

		# VISIT LISTING
		case 'visit' :
		  if (!empty($link_id)) {
  			$mtLink = new mtLinks( $database );
  			$mtLink->load( $link_id );
  			if (empty($title))
          $title = shAppendCatListing( $mtLink, false, $shLangIso, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendCatListing( $mtLink, false, $shLangIso, $option, $shLangName));
  			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_VISIT']; 
    	} else $dosef = false;
		break;
		
		# Add Listing
		case 'addlisting':
			if (!empty($link_id)) {
				$mtLink = new mtLinks( $database );
				$mtLink->load( $link_id );
				if (empty($title))
          $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName));
			} elseif (isset($cat_id)) {  // cat id can be zero (for root)
        if (empty($title))
          $title = shAppendCat( $cat_id, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendCat( $cat_id, $option, $shLangName));
			}
			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_ADDLISTING'];
		break;

		# Add Category
		case 'addcategory' :
			if (!empty($link_id))  {
				$mtLink = new mtLinks( $database );
        $mtLink->load( $link_id );
				if (empty($title))
          $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName));
			} elseif (isset($cat_id)) {
				if (empty($title))
          $title = shAppendCat( $cat_id, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendCat( $cat_id, $option, $shLangName));
			}
			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_ADDCATEGORY'];
		break;

		# Search Results
		case 'search':
  		if (empty($title))
          $title = shAppendCat( $cat_id, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendCat( $cat_id, $option, $shLangName));
  
  		$title[] = $sh_LANG[$shLangIso]['_MT_SEF_SEARCH'];
		break;
		
		# Edit listing
		case 'editlisting' :
		  if (!empty($link_id)) {
  			$mtLink = new mtLinks( $database );
  			$mtLink->load( $link_id );
  			if ($sefConfig->shMTreeInsertCategories > 0) {
  			  if (empty($title))
            $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName)); 
        }
  			if (empty($title))
          $title = shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName)); 
  			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_EDIT_LISTING'];
			} else $dosef = false;
		break;
		
		# Delete listing
		case 'deletelisting' :
		  if (!empty($link_id)) {
  			$mtLink = new mtLinks( $database );
  			$mtLink->load( $link_id );
  			if ($sefConfig->shMTreeInsertCategories > 0) {
  			  if (empty($title))
            $title = shAppendCat( $mtLink->cat_id, $option, $shLangName);
          else
            $title = array_merge ($title, shAppendCat( $mtLink->cat_id, $option, $shLangName)); 
        }
  			if (empty($title))
          $title = shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName);
        else
          $title = array_merge ($title, shAppendListing( $mtLink->link_name, $mtLink->link_id, false, $shLangIso, $option, $shLangName)); 
  			$title[] = $sh_LANG[$shLangIso]['_MT_SEF_DELETE_LISTING'];
			} else $dosef = false;
		break;
		
		case '':
		  if (empty( $title))
        $title[] = $shMTreeName; // at least put defautl name, even if told not to do so
      $title[] = '/';
		break;
		
		default:
		  $dosef = false;
		break;
		
}      

/* sh404SEF extension plugin : remove vars we have used, adjust as needed --*/  
shRemoveFromGETVarsList('option');
shRemoveFromGETVarsList('lang');  
shRemoveFromGETVarsList('Itemid');
if (isset($task))
  shRemoveFromGETVarsList('task');
if (isset($limit))
  shRemoveFromGETVarsList('limit');
if (isset($limitstart))  
  shRemoveFromGETVarsList('limitstart'); // limitstart can be zero
if (isset($start))
  shRemoveFromGETVarsList('start');
/* sh404SEF extension plugin : end of remove vars we have used -------------*/  

  
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------

?>
