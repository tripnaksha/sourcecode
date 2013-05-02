<?php
/**
 * sh404SEF support for Mosets HotProperty component.
 * Copyright Yannick Gaultier (shumisha) - 2007
 * shumisha@gmail.com
 * @version     $Id: com_hotproperty.php 866 2009-01-17 14:05:21Z silianacom-svn $
 * 
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
$shLangIso = shLoadPluginLanguage( 'com_hotproperty', $shLangIso, '_HP_SEF_PROPERTY');
// ------------------  load language file - adjust as needed ----------------------------------------


	/********************************************************
	* Utility Functions
	********************************************************/
	

# Include the config file

require( sh404SEF_ABS_PATH.'administrator/components/com_hotproperty/config.hotproperty.php' );

    // V 1.2.4.s make sure user param prevails on guessed Itemid
    if (empty($Itemid) && $sefConfig->shInsertGlobalItemidIfNone 
         && !empty($shCurrentItemid)) {
      $string .= '&Itemid='.$shCurrentItemid; ;  // append current Itemid 
      $Itemid = $shCurrentItemid; 
      shAddToGETVarsList('Itemid', $Itemid); // V 1.2.4.m
    }
    $task = isset($task) ? $task : null;
    $id = isset($id) ? $id : null;
    $agent_id = isset($agent_id) ? $agent_id : null;
    $limit = isset($limit) ? $limit : null;
    $limitstart = isset($limitstart) ? $limitstart : null;
    
    // $search    // not processed, passed as GET var
    // $type      // not processed, passed as GET var
    // $search_id // not processed, passed as GET var
    
    // shumisha : insert component name from menu
    // Configuration variable 
    $shInsertHotPropName = true;  // if yes, name is inserted
    $shHotPropName = shGetComponentPrefix($option);
    $shHotPropName = empty($shHotPropName) ?  getMenuTitle($option, null, $Itemid, null, $shLangName )
                                                     : $shHotPropName;
    //$shHotPropName = (empty($shHotPropName) || $shHotPropName == '/') ? 'Directory':$shHotPropName; // V 1.2.4.t
    if ($shInsertHotPropName && !empty($shHotPropName)) $title[] = $shHotPropName;  

switch ($task) {

    # View Property
    case 'view':
      if ( is_numeric($id) ) {
				$database->setQuery( "SELECT t.id,t.name AS type_name, p.id, p.name AS property_name FROM #__hp_properties AS p" 
					.	"\nLEFT JOIN #__hp_prop_types AS t ON p.type = t.id"
					.	"\nWHERE p.id = '".$id."'"
					.	"\nLIMIT 1"
				);
				if (shTranslateUrl($option, $shLangName))
          $row = $database->loadObject(  );
        else $row = $database->loadObject( false ); 
				$title[] = $sh_LANG[$shLangIso]['_HP_SEF_PROPERTY'];
        $title[] = $row->type_name;
        $title[] = $row->property_name;
        shRemoveFromGETVarsList('task');
        shRemoveFromGETVarsList('id');
			}
    break;

		# View Type
		case  'viewtype':
			$type_id = $id;
			if ( is_numeric($type_id) ) {
				$database->setQuery( "SELECT t.id, t.name AS type_name FROM #__hp_prop_types AS t" 
					.	"\nWHERE t.id = '".$type_id."'"
					.	"\nLIMIT 1"
				);
        if (shTranslateUrl($option, $shLangName))
          $row = $database->loadObject(  );
        else $row = $database->loadObject( false );
				$title[] = $sh_LANG[$shLangIso]['_HP_SEF_PROPERTY'];
        $title[] = $row->type_name;

				// --- Sort & Order
				if (!isset($sort)) {
					global $hp_default_order;
					$sort = $hp_default_order;
				}

				if (!isset($order)) {
					global $hp_default_order2;
					$order = $hp_default_order2;
				}

				global $hp_default_order,$hp_default_order2,$hp_default_limit;

				if ( $sort <> '' && $order <> '' && !( $sort == $hp_default_order && $order == $hp_default_order2 && $limitstart == 0 && $limit == $hp_default_limit) ) {
					$title[] = $sort;
          $title[] = $order;
          shRemoveFromGETVarsList('sort');
          shRemoveFromGETVarsList('order');
				}
        shRemoveFromGETVarsList('task');
        shRemoveFromGETVarsList('id');
			} else $dosef = false; 
    break;
    
		# View Featured
		case 'viewfeatured':
			$title[] = $sh_LANG[$shLangIso]['_HP_SEF_FEATURED'];
			$title[] = '/';
			shRemoveFromGETVarsList('task');
		break;

		# View Agent
		case 'viewagent' :

			$agent_id = $id;
			// This condition allows listagent module to work so that $agent_id can be a javascript expression.
			if ( is_numeric($agent_id) ) {
				$database->setQuery( "SELECT a.id, a.name AS agent_name, c.id, c.name AS company_name FROM #__hp_agents AS a" 
					.	"\nLEFT JOIN #__hp_companies AS c ON a.company = c.id"
					.	"\nWHERE a.id = '".$agent_id."'"
					.	"\nLIMIT 1"
				);
				if (shTranslateUrl($option, $shLangName))
          $row = $database->loadObject(  );
        else $row = $database->loadObject( false );
				if (!empty($row)) {
				  $title[] = $row->company_name;
				  $title[] = $sh_LANG[$shLangIso]['_HP_SEF_VIEWAGENT'];
				  $title[] = $row->agent_name;
				  $title[] = '/';
				  shRemoveFromGETVarsList('id');
        } else {  // for some reason, no name and company : just put a text. $id will be passed as GET var 
          $title[] = $sh_LANG[$shLangIso]['_HP_SEF_VIEWAGENT'];
        }
        shRemoveFromGETVarsList('task');
			}

			// Default virtual directory would be: $sh_LANG[$shLangIso]['_HP_SEF_COMPANY/company_name/agent_name/.
			// There is an alternative way to represent this as to allow List Agents module to work: $sh_LANG[$shLangIso]['_HP_SEF_VIEWAGENT/agent_id
			//if ( !is_numeric($agent_id) || count($row) <= 0 ) {
			//	$sefstring .= ."/".$agent_id;
			//} else {
			//	$sefstring .= $sh_LANG[$shLangIso]['_HP_SEF_COMPANY."/".sefencode($row->company_name)."/".sefencode($row->agent_name)."/";
			//}  // THIS should bot be a problem with sh404SEF 
	  break;

		# View Company / View Company Email / Send Company Email
		case 'viewco':
    case 'viewcoemail':
    case 'sendenquiry':

			$company_id = $id;
			shRemoveFromGETVarsList('task');
			// This condition allows listagent module to work so that $agent_id can be a javascript expression.
			if ( is_numeric($company_id) ) {
				$database->setQuery( "SELECT c.id, c.name AS company_name FROM #__hp_companies AS c" 
					.	"\nWHERE c.id = '".$company_id."'"
					.	"\nLIMIT 1"
				);
				if (shTranslateUrl($option, $shLangName))
          $row = $database->loadObject( );
        else $row = $database->loadObject( false );
			}
			
			# View Company
			switch ($task) {
			  case 'viewco': 
				// Default virtual directory would be: $sh_LANG[$shLangIso]['_HP_SEF_COMPANY/company_name/.
				// There is an alternative way to represent this as to allow List Companies module to work: $sh_LANG[$shLangIso]['_HP_SEF_COMPANY/company_id
				//if ( !is_numeric($company_id) || count($row) <= 0 ) {
				//	$sefstring .= $sh_LANG[$shLangIso]['_HP_SEF_COMPANY."/".$company_id;
				//} else {
				//	$sefstring .= $sh_LANG[$shLangIso]['_HP_SEF_COMPANY."/".sefencode($row->company_name)."/";
				if (!empty($row)) {
				  $title[] = $sh_LANG[$shLangIso]['_HP_SEF_COMPANY'];
				  $title[] = $row->company_name;
				  $title[] = '/';
				  shRemoveFromGETVarsList('id');
        } else {
          $title[] = $sh_LANG[$shLangIso]['_HP_SEF_COMPANY'];
        }
				
      break;
      
			# View Company Email
			case 'viewcoemail' : 
				$title[] = $sh_LANG[$shLangIso]['_HP_SEF_EMAIL'];
        $title[] = $row->company_name;
        shRemoveFromGETVarsList('id');
      break;
      
			# Send Enquiry Email
			case 'sendenquiry' :
			  $title[] = $sh_LANG[$shLangIso]['_HP_SEF_SENDENQUIRY'];
			  $title[] = $row->company_name;
        shRemoveFromGETVarsList('id');
				// Sendenquiry does not use this ID to determine whether this is directed to company or agent, the form has a hidden field called 'sbj' to distinguish this.
			break;
			}
		break;

		# View Agent Email
		case 'viewagentemail':

			$agent_id = id;
      $title[] = $sh_LANG[$shLangIso]['_HP_SEF_EMAIL'];
      shRemoveFromGETVarsList('task');
      
			if ( is_numeric($agent_id) ) {
				$database->setQuery( "SELECT a.id, a.name AS agent_name, c.id, c.name AS company_name FROM #__hp_agents AS a" 
					.	"\nLEFT JOIN #__hp_companies AS c ON a.company = c.id"
					.	"\nWHERE a.id = '".$agent_id."'"
					.	"\nLIMIT 1"
				);
				if (shTranslateUrl($option, $shLangName))
          $row = $database->loadObject( );
        else $row = $database->loadObject( false );
        $title[] = $row->company_name;
        $title[] = $row->agent_name;
        $title[] = '/';
        shRemoveFromGETVarsList('id');
			} else $dosef = false;
		break;

		# Standard Search
		case 'search' : 
			global $sufix;
			require( sh404SEF_ABS_PATH.'administrator/components/com_hotproperty/config.hotproperty.php' );

			$title[] = $sh_LANG[$shLangIso]['_HP_SEF_SEARCH'];
			//$title[] = '/';
			shRemoveFromGETVarsList('task');
      // type and search are left as GET VARS, we don't want them in DB (we could have type though)
		break;

		# Advanced Search
		case 'advsearch':
			$title[] = $sh_LANG[$shLangIso]['_HP_SEF_ADVSEARCH'];
			$title[] = '/';
			shRemoveFromGETVarsList('task');
		break;

		# Advanced Search Result
		case 'asearch' : 
			$title[] = $sh_LANG[$shLangIso]['_HP_SEF_SEARCHRESULT'];
            $title[] = '/';
			shRemoveFromGETVarsList('task');
      // search_id is left as GET var, don't want in DB
		break;

		# Manage Property
		case 'manageprop' : 
			$title[] = $sh_LANG[$shLangIso]['_HP_SEF_MANAGEPROP'];
            $title[] = '/';
			shRemoveFromGETVarsList('task');
		break;

		# Edit Agent
		case 'editagent' : 
			$title[] = $sh_LANG[$shLangIso]['_HP_SEF_EDITAGENT'];
			$title[] = '/';
			shRemoveFromGETVarsList('task');
		break;

		# Add Property
		case 'addprop' :
            $title[] = $sh_LANG[$shLangIso]['_HP_SEF_ADDPROPERTY'];
			$title[] = '/';
			shRemoveFromGETVarsList('task');
		break;

		# Edit Property
		case 'editprop' : 
			//$id = property id / left as GET var 
			$title[] = $sh_LANG[$shLangIso]['_HP_SEF_EDITPROPERTY'];
			$title[] = '/';
			shRemoveFromGETVarsList('task');
		break;
		
		case '':
		  if (empty( $title)) $title[] = $shHotPropName; // at least put defautl name, even if told not to do so
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
if (isset($limit))
  shRemoveFromGETVarsList('limit');
if (isset($limitstart))  
  shRemoveFromGETVarsList('limitstart'); // limitstart can be zero
/* sh404SEF extension plugin : end of remove vars we have used -------------*/  

  
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------

?>
