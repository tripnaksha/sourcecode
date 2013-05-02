<?php
/**
* Joomla/Mambo Community Builder
* @version $Id: admin.comprofiler.html.php 610 2006-12-13 17:33:44Z beat $
* @package Community Builder
* @subpackage admin.comprofiler.html.php
* @author JoomlaJoe and Beat
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class HTML_comprofiler {
	function secureAboveForm( $functionName ) {
		global $_CB_framework;
		ob_start();
?>
if(self!=top) {
	parent.document.body.innerHTML='Iframes not allowed, could be hack attempt..., sorry!';
	self.top.location=self.location;
}
<?php
		$js		=	 ob_get_contents();
		ob_end_clean();
		$_CB_framework->document->addHeadScriptDeclaration( $js );
		return null;
	}
	function installPluginForm() {
	}

	function _saveOrderJs( $task ) {
		global $_CB_framework;

		ob_start();
?>
function cbsaveorder( n ) {
	cbcheckAll_button( n );
	submitform('<?php echo addslashes( $task ); ?>');
}

//needed by cbsaveorder function
function cbcheckAll_button( n ) {
	for ( var j = 0; j <= n; j++ ) {
		box = eval( "document.adminForm.cb" + j );
		if ( box.checked == false ) {
			box.checked = true;
		}
	}
}
<?php
		$js		=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->document->addHeadScriptDeclaration( $js );
	}
	function showLists( &$rows, $pageNav, $search, $option ) {
		global $_CB_framework;
		HTML_comprofiler::secureAboveForm('showLists');

		outputCbTemplate( 2 );
		outputCbJs( 2 );

		global $_CB_Backend_Title;
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-lists', "CB List Manager" ) );

		HTML_comprofiler::_saveOrderJs( 'savelistorder' );
?>
<form action="index2.php" method="post" name="adminForm">
  <table cellpadding="4" cellspacing="0" border="0" width="100%">
    <tr>
       <td>Search: <input type="text" name="search" value="<?php echo $search;?>" class="inputbox" onChange="document.adminForm.submit();" />
      </td>
    </tr>
  </table>
  <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
   <thead>
    <tr>
      <th width="2%" class="title">#</th>
      <th width="3%" class="title"> <input type="checkbox" name="toggle" value="" <?php echo 'onClick="checkAll(' . count($rows) . ');"'; ?> />
      </th>
      <th width="25%" class="title">Title</th>
      <th width="25%" class="title">Description</th>
      <th width="5%" class="title">Published?</th>
      <th width="5%" class="title">Default?</th>
      <th width="15%" class="title">Access</th>
      <th width="5%" class="title" colspan="2">Re-Order</th>
      <th width="1%"><a href="javascript: cbsaveorder( <?php echo count( $rows )-1; ?> )"><img src="images/filesave.png" border="0" width="16" height="16" alt="Save Order" /></a></th>
      <th width="2%" class="title">listid</th>
    </tr>
   </thead>
   <tbody>
<?php
		$k = 0;
		$imgpath='../components/com_comprofiler/images/';
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row =& $rows[$i];

		    $img3 = $row->published ?  'tick.png' : 'publish_x.png';
		    $task3 = $row->published ?  'listPublishedNo' : 'listPublishedYes';
		    $img4 = $row->default ?  'tick.png' : 'publish_x.png';
			$task4 = $row->default ?  'listDefaultNo' : 'listDefaultYes';
?>
    <tr class="<?php echo "row$k"; ?>">
      <td><?php echo $i+1+$pageNav->limitstart;?></td>
      <td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->listid; ?>" onClick="isChecked(this.checked);" /></td>
      <td> <a href="#editList" onClick="return listItemTask('cb<?php echo $i;?>','editList')"><?php echo htmlspecialchars( getLangDefinition($row->title) ); ?></a></td>
      <td><?php echo htmlspecialchars( getLangDefinition($row->description) ); ?></td>
      <td width="10%"><a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task3;?>')"><img src="<?php echo $imgpath.$img3;?>" width="16" height="16" border="0" alt="" /></a></td>
      <td width="10%"><a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task4;?>')"><img src="<?php echo $imgpath.$img4;?>" width="16" height="16" border="0" alt="" /></a></td>
	  <td><?php 
	  		if ( $row->useraccessgroupid >= 0 ) {
		  		echo '<span style="color:red;">' . $_CB_framework->acl->get_group_name( (int) $row->useraccessgroupid ) . '</span>';
	  		} elseif ( $row->useraccessgroupid == -2 ) {
	  			echo '<span style="color:green;">Everybody</span>';
	  		} elseif ( $row->useraccessgroupid == -1 ) {
	  			echo '<span style="color:orange;">All Registered Users</span>';
	  		}
	  ?></td>
      <td>
	<?php    if ($i > 0 || ($i+$pageNav->limitstart > 0)) { ?>
         <a href="#reorder" onClick="return listItemTask('cb<?php echo $i;?>','orderupList')">
            <img src="images/uparrow.png" width="12" height="12" border="0" alt="Move Up" />
         </a>
	<?php    } ?>
      </td>
      <td>
	<?php    if ($i < $n-1 || $i+$pageNav->limitstart < $pageNav->total-1) { ?>
         <a href="#reorder" onClick="return listItemTask('cb<?php echo $i;?>','orderdownList')">
            <img src="images/downarrow.png" width="12" height="12" border="0" alt="Move Down" />
         </a>
	<?php    } ?>
      </td>
	  <td align="center">
	  <input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
	  </td>
      <td style="text-align:right"><?php echo $row->listid;?></td>
    </tr>
    <?php $k = 1 - $k; } ?>
   </tbody>
   <tfoot>
    <tr>
      <th align="center" colspan="12"> <?php echo $pageNav->getListFooter(); ?></th>
    </tr>
   </tfoot>
  </table>
  <input type="hidden" name="option" value="<?php echo $option;?>" />
  <input type="hidden" name="task" value="showLists" />
  <input type="hidden" name="boxchecked" value="0" />
  <?php
	echo cbGetSpoofInputTag( 'list' );
  ?>
</form>
<?php
	}

	
	function editList( &$row, $lists, $fields, $option, $tabid, $paramsEditorHtml ) {
		global $_CB_database, $_CB_framework;

		HTML_comprofiler::secureAboveForm('editList');
		outputCbTemplate( 2 );
		outputCbJs( 2 );
		initToolTip(2);

		global $_CB_Backend_Title;
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-lists', "Community Builder List: <small>" . ( $row->listid ? "Edit" . ' [ '. htmlspecialchars( getLangDefinition( $row->title ) ) .' ]' : "New" ) . '</small>' ) );

		if ( $row->listid && ( ! $row->published ) ) {
			echo '<div class="cbWarning">List is not published</div>' . "\n";
		}

		$notFoundFielIds		=	array();
		$fieldids				=	array();
		$col1options="";
		$col2options="";
		$col3options="";
		$col4options="";
		if($tabid >0) {
			$col1fields=explode('|*|',$row->col1fields);
			for ($i=0, $n=count( $col1fields ); $i < $n; $i++) {
				$col1field = $col1fields[$i];
				if(trim($col1field)!='' && trim($col1field)!=null) {
					$text			=	array_search($col1field,$fields);
					if ( is_string( $text ) ) {
						$col1options .= "<option value=\"".$col1field."\">". htmlspecialchars( getLangDefinition($text) ) ."\n";
						$fieldids[] = $col1field;
					} else {
						$notFoundFielIds[]	=	$col1field;
					}
				}
			}
			$col2fields=explode('|*|',$row->col2fields);
			for ($i=0, $n=count( $col2fields ); $i < $n; $i++) {
				$col2field = $col2fields[$i];
				if(trim($col2field)!='' && trim($col2field)!=null) { 
					$text			=	array_search($col2field,$fields);
					if ( is_string( $text ) ) {
						$col2options .= "<option value=\"".$col2field."\">". htmlspecialchars( getLangDefinition($text) ) ."\n";
						$fieldids[]			=	$col2field;
					} else {
						$notFoundFielIds[]	=	$col2field;
					}
				}
			}
			$col3fields=explode('|*|',$row->col3fields);
			for ($i=0, $n=count( $col3fields ); $i < $n; $i++) {
				$col3field = $col3fields[$i];
				if(trim($col3field)!='' && trim($col3field)!=null) { 
					$text			=	array_search($col3field,$fields);
					if ( is_string( $text ) ) {
						$col3options .= "<option value=\"".$col3field."\">". htmlspecialchars( getLangDefinition($text) ) ."\n";
						$fieldids[]			=	$col3field;
					} else {
						$notFoundFielIds[]	=	$col3field;
					}
				}
			}
			$col4fields=explode('|*|',$row->col4fields);
			for ($i=0, $n=count( $col4fields ); $i < $n; $i++) {
				$col4field = $col4fields[$i];
				if(trim($col4field)!='' && trim($col4field)!=null) { 
					$text			=	array_search($col4field,$fields);
					if ( is_string( $text ) ) {
						$col4options .= "<option value=\"".$col4field."\">". htmlspecialchars( getLangDefinition($text) ) ."\n";
						$fieldids[]			=	$col4field;
					} else {
						$notFoundFielIds[]	=	$col4field;
					}
				}
			}
		}
		
		// this query is for listing displayable fields which are not yet in a column:
		$fieldsRemaining		=	array_diff( $fields, $fieldids );
		if ( count( $fieldsRemaining ) > 0 ) {
			$_CB_database->setQuery( "SELECT f.fieldid, f.title, f.name"
				. "\n FROM #__comprofiler_fields f"
				. "\n INNER JOIN #__comprofiler_plugin AS p ON (f.pluginid = p.id)"
				. "\n WHERE f.fieldid IN (" . implode( ',', $fieldsRemaining ) . ')'
				. "\n  AND p.published = 1"
			);
			$fields				=	$_CB_database->loadObjectList();
		} else {
			$fields				=	array();
		}

		$stripME				=	array(" ASC", " DESC","`");
		$sortsArray				=	explode( ', ', $row->sortfields );
		$SQLfunctions			=	array();
		$k						=	-1;
		foreach ( $sortsArray as $k => $v ) {
			$WhereIn			=	trim( str_replace($stripME, "", $v ) );
			if ( substr( $WhereIn, -1, 1 ) == ')') {
				$SQLfunctions[]	=	$WhereIn;
				unset( $sortsArray[$k] );
			} else {
				$sortsArray[$k]	=	$_CB_database->Quote( $WhereIn );
			}
		}
		$sortsArray[$k+1]		=	$_CB_database->Quote( 'onlinestatus' );		//TBD: refactor to field, temporarly in here...
		// this query is for listing sortable fields
		// sortsArray never empty, so this is ok:
		$_CB_database->setQuery( "SELECT f.title, f.name"
			. "\n FROM #__comprofiler_fields f"
			. "\n INNER JOIN #__comprofiler_plugin AS p ON (f.pluginid = p.id)"
			. "\n WHERE ( f.published = 1 OR f.name IN ('name','username') )"
			. "\n  AND f.name <> 'NA'"
			. "\n  AND f.tablecolumns <> ''"
			. "\n  AND p.published = 1"
			. "\n AND f.name NOT IN(" . implode( ',', $sortsArray ) . ")"
		);
/*
		$stripME = array(" ASC", " DESC","`");
		$WhereIn = str_replace($stripME, "", $row->sortfields);
		$WhereIn = "'".str_replace(", ","','",$WhereIn)."'";
		$_CB_database->setQuery( "SELECT f.title, f.name"
			. "\nFROM #__comprofiler_fields f"
			. "\nWHERE f.published = 1 AND f.name!='NA'"
			. "\nAND f.name NOT IN(".$WhereIn.")"
		);
*/
		$sortfields = $_CB_database->loadObjectList();
		if ( is_array( $sortfields ) && ( count( $sortfields ) > 0 ) && ! in_array( 'RAND()', $SQLfunctions ) ) {
			$randomSort		=	new stdClass();
			$randomSort->title	=	CBTxt::T("Sort Randomly");
			$randomSort->name	=	"RAND()";
			$sortfields[]	=	$randomSort;
		}
		// this query is for listing filterable fields
		$_CB_database->setQuery( "SELECT f.title, f.name"
			. "\n FROM #__comprofiler_fields f"
			. "\n INNER JOIN #__comprofiler_plugin AS p ON (f.pluginid = p.id)"
			. "\n WHERE f.published = 1"
			. "\n  AND f.name <> 'NA'"
			. "\n  AND f.tablecolumns <> ''"
			. "\n  AND p.published = 1"
			. "\n   OR f.name IN ('name','username')"
		);
		$filterfields = $_CB_database->loadObjectList();
		
		
		
		$sortlists=explode(", ",str_replace("`","",$row->sortfields));
		$sortparts=array();
		$i=0;
		foreach($sortlists as $sortlist) {
			$sortlistpart=array();
			$sortlistpart=explode(" ",$sortlist);
			if(!ISSET($sortlistpart[1])) $sortlistpart[1]="";
			$sortparts[$i]['field']=$sortlistpart[0];	
			$sortparts[$i]['dir']=$sortlistpart[1];
			if ( substr( $sortlistpart[0], -1, 1 ) != ')' ) {
				$_CB_database->setQuery("SELECT title FROM #__comprofiler_fields WHERE name='".$sortlistpart[0]."' LIMIT 1");
				$sortparts[$i]['title']=$_CB_database->loadResult();
			} else {
				switch ( $sortlistpart[0] ) {
					case 'RAND()':
						$sortparts[$i]['title']	=	CBTxt::T("Sort Randomly");						
						break;
				
					default:
						$sortparts[$i]['title']	=	CBTxt::T("Non-existing field") . ": " . $sortlistpart[0];						
						break;
				}
			}
			$i++;
		}

if ( count( $notFoundFielIds ) > 0 ) {
	cbArrayToInts( $notFoundFielIds );
	$_CB_database->setQuery( "SELECT f.name, f.title, f.published, f.profile, p.published AS pluginpublished, p.name AS pluginname"
		. "\n FROM #__comprofiler_fields AS f"
		. "\n INNER JOIN #__comprofiler_plugin AS p ON (f.pluginid = p.id)"
		. "\n WHERE f.fieldid IN (" . implode( ',', $notFoundFielIds ) . ")"
	//	. "\n WHERE f.published = 1"
	//	. "\n  AND f.profile > 0"
	//	. "\n  AND p.published = 1"
		. "\n ORDER BY f.ordering"
	);
	$problemFields				=	$_CB_database->loadObjectList();
	if ( is_array( $problemFields ) && ( count( $problemFields ) > 0 ) ) {
		echo '<div class="cbWarning">Following fields are in list but not visible in here for following reason(s):<ul>';
		foreach ( $problemFields as $f ) {
			if ( $f->published != 1 ) {
				echo '<li>Field "' . getLangDefinition( $f->title ) . ' (' . $f->name . ')" ' . " is not published !";
			}
			if ( $f->profile <= 0 ) {
				echo '<li>Field "' . getLangDefinition( $f->title ) . ' (' . $f->name . ')" ' . " is not displayed on profile !";
			}
			if ( $f->pluginpublished != 1 ) {
				echo '<li>Field "' . getLangDefinition( $f->title ) . ' (' . $f->name . ')" ' . " is from plugin \"" . $f->pluginname . "\" but this plugin is not published !";
			}
		}
		echo '</ul>If you save this users list now, the fields listed above will be removed from this users list. If you want to keep these fields in this list, cancel now and go to Components / Community Builder / Field Manager.</div>' . "\n";
	}
}
		ob_start();
?>
  function getObject(obj) {
    var strObj;
    if (document.all) {
      strObj = document.all.item(obj);
    } else if (document.getElementById) {
      strObj = document.getElementById(obj);
    }
	return strObj;
}
function shDiv(objID,sh) {
	var strObj;
	strObj = getObject(objID);
	if(sh==0) {
	strObj.style.display="none";
	} else {
	strObj.style.display="block";
	}
}
		function submitbutton(pressbutton) {
			if (pressbutton == 'showLists') {
		        <?php echo $_CB_framework->saveCmsEditorJS( 'description' ); ?>
				submitform( pressbutton );
				return;
			}
			var coll = document.adminForm;
			var errorMSG = '';
			var iserror=0;
			if (coll.col1enabled.checked == true) coll.col1title.setAttribute('mosReq',1);
			if (coll.col2enabled.checked == true) coll.col2title.setAttribute('mosReq',1);
			if (coll.col3enabled.checked == true) coll.col3title.setAttribute('mosReq',1);
			if (coll.col4enabled.checked == true) coll.col4title.setAttribute('mosReq',1);
			getSortList(document.adminForm.sort);
			getFilterList(document.adminForm.filter);
		     if (coll != null) {
		       var elements = coll.elements;
		       // loop through all input elements in form
		       for (var i=0; i < elements.length; i++) {
		         // check if element is mandatory; here mosReq=1
		         if ((typeof(elements.item(i).getAttribute('mosReq')) != "undefined") && (elements.item(i).getAttribute('mosReq') == 1)) {
		           if (elements.item(i).value == '') {
		             //alert(elements.item(i).getAttribute('mosLabel') + ':' + elements.item(i).getAttribute('mosReq'));
		             // add up all error messages
		             errorMSG += elements.item(i).getAttribute('mosLabel') + ' : <?php echo _UE_REQUIRED_ERROR; ?>\n';
		             // notify user by changing background color, in this case to red
		             elements.item(i).style.backgroundColor = "red";
		             iserror=1;
		           }
		         }
		       }
		     }
			if(iserror==1) { alert(errorMSG); }
			else {
				selectAll(document.adminForm.col1);
				selectAll(document.adminForm.col2);
				selectAll(document.adminForm.col3);
				selectAll(document.adminForm.col4);
		        <?php echo $_CB_framework->saveCmsEditorJS( 'description' ); ?>
				submitform( pressbutton );
			}

		}

    function addOption(selectObj, value)
    {
      optionSelected = (value == null);
      if(value == null) value = prompt('', '');
      if(value != null)
      {
        if(value.indexOf(',') != -1)
          alert('Commas are not allowed in size values');
        else
        {
          var i = selectObj.options.length;
          value = value.replace(/1\/2/g, '�');
          selectObj.options.length = i + 1;
          selectObj.options[i].value = (value != '' && value != ' ') ? value : ' ';
          selectObj.options[i].text = (value != '' && value != ' ') ? value : '[empty]';
          selectObj.options[i].selected = optionSelected;
// uncomment the line below if you want the select list to change it's size to match the number of options it contains.
//          selectObj.size = selectObj.options.length;
        }
      }
    }
  
    function editOptions(selectObj)
    {
      for(var i = 0; i < selectObj.options.length; i++)
      {
        if(selectObj.options[i].selected)
        {
          var value = prompt('', selectObj.options[i].value);
          if(value != null)
          {
            if(value.indexOf(',') != -1)
              alert('Commas are not allowed in size values');
            else
            {
              selectObj.options[i].value = value;
              selectObj.options[i].text = (value != '') ? value : '[empty]';
              selectObj.options[i].selected = true;
            }
          }
        }
      }
    }
    
    function deleteOptions(selectObj)
    {
      for(var i = 0; i < selectObj.options.length; i++)
      {
        if(selectObj.options[i].selected)
        {
          for(var j = i; j < selectObj.options.length - 1; j++)
          {
            selectObj.options[j].value = selectObj.options[j + 1].value;
            selectObj.options[j].text = selectObj.options[j + 1].text;
            selectObj.options[j].selected = selectObj.options[j + 1].selected;
          }
          selectObj.options.length = selectObj.options.length - 1;
          i--;
        }
      }
    }
    
    function moveOptions(selectObj, direction)
    {
      if(selectObj.selectedIndex != -1)
      {
        if(direction < 0)
        {
          for(i = 0; i < selectObj.options.length; i++)
          {
            swapValue = (i == 0 || selectObj.options[i + direction].selected) ? null : selectObj.options[i + direction].value;
            swapText = (i == 0 || selectObj.options[i + direction].selected) ? null : selectObj.options[i + direction].text;
            if(selectObj.options[i].selected && swapValue != null && swapText != null)
            {
              thisValue = selectObj.options[i].value;
              thisText = selectObj.options[i].text;
              selectObj.options[i].value = swapValue;
              selectObj.options[i].text = swapText;
              selectObj.options[i + direction].value = thisValue;
              selectObj.options[i + direction].text = thisText;
              selectObj.options[i].selected = false;
              selectObj.options[i + direction].selected = true;
            }
          }
        }
        else
        {
          for(i = selectObj.options.length - 1; i >= 0; i--)
          {
            swapValue = (i == selectObj.options.length - 1 || selectObj.options[i + direction].selected) ? null : selectObj.options[i + direction].value;
            swapText = (i == selectObj.options.length - 1 || selectObj.options[i + direction].selected) ? null : selectObj.options[i + direction].text;
            if(selectObj.options[i].selected && swapValue != null && swapText != null)
            {
              thisValue = selectObj.options[i].value;
              thisText = selectObj.options[i].text;
              selectObj.options[i].value = swapValue;
              selectObj.options[i].text = swapText;
              selectObj.options[i + direction].value = thisValue;
              selectObj.options[i + direction].text = thisText;
              selectObj.options[i].selected = false;
              selectObj.options[i + direction].selected = true;
            }
          }
        }
      }
    }
    var NS4 = (document.layers);

    function moveOption(fromObj, toObj)
    {
      for(var i = fromObj.options.length - 1; i >= 0; i--)
      {
        if(fromObj.options[i].selected)
        {
          fromObj.options[i].selected = false;
          var optionText = fromObj.options[i].text.replace(' [ASC]','');
	      optionText = optionText.replace(' [DESC]','');
          var optionValue = fromObj.options[i].value.replace(' ASC','');
	      optionValue = optionValue.replace(' DESC','');
          for(var j = i; j < fromObj.options.length - 1; j++)
          {
            fromObj.options[j].text = fromObj.options[j + 1].text;
            fromObj.options[j].value = fromObj.options[j + 1].value;
          }
          fromObj.options.length = fromObj.options.length - 1;
          toObjIndex = toObj.options.length;
          toObj.options.length = toObj.options.length + 1;
          toObj.options[toObjIndex].text = optionText;
          toObj.options[toObjIndex].value = optionValue;
          if(NS4)
            history.go(0);
        }
      }
    }

    function moveOption2(fromObj, toObj, appendValue)
    {
        if(fromObj.options[fromObj.selectedIndex].selected)
        {
	  fromObjIndex=fromObj.selectedIndex;
          fromObj.options[fromObjIndex].selected = false;
          optionText = fromObj.options[fromObjIndex].text+ ' ['+appendValue+']';
          optionValue = fromObj.options[fromObjIndex].value+' '+appendValue;
          for(var j = fromObjIndex; j < fromObj.options.length - 1; j++)
          {
            fromObj.options[j].text = fromObj.options[j + 1].text;
            fromObj.options[j].value = fromObj.options[j + 1].value;
          }
          fromObj.options.length = fromObj.options.length - 1;
          toObjIndex = toObj.options.length;
          toObj.options.length = toObj.options.length + 1;
          toObj.options[toObjIndex].text = optionText;
          toObj.options[toObjIndex].value = optionValue;
	  toObj.options[toObjIndex].selected=false;
          if(NS4)
            history.go(0);
        }

    }

    function moveOption3(fromObj, toObj, comparison, condition)
    {
        if(fromObj.options[fromObj.selectedIndex].selected)
        {
	  if((condition=='' || condition==null) && document.adminForm.condition.getAttribute('Req')==1) {
		alert("You must define a condition text!");
		return;
	  }
	  fromObjIndex=fromObj.selectedIndex;
          fromObj.options[fromObjIndex].selected = false;
          optionText = fromObj.options[fromObjIndex].text+ ' '+comparison+' '+condition;
	  condition=condition.replace("'", "\\'");
	  if(condition!='' && condition!=null) condition="'"+escape(condition)+"'";
          optionValue = fromObj.options[fromObjIndex].value+' '+comparison+condition;
          toObjIndex = toObj.options.length;
          toObj.options.length = toObj.options.length + 1;
          toObj.options[toObjIndex].text = optionText;
          toObj.options[toObjIndex].value = optionValue;
	  toObj.options[toObjIndex].selected=false;
          if(NS4)
            history.go(0);
        }

    }
    function moveOption4(fromObj, toObj)
    {
      for(var i = fromObj.options.length - 1; i >= 0; i--)
      {
        if(fromObj.options[i].selected)
        {
          fromObj.options[i].selected = false;
          for(var j = i; j < fromObj.options.length - 1; j++)
          {
            fromObj.options[j].text = fromObj.options[j + 1].text;
            fromObj.options[j].value = fromObj.options[j + 1].value;
          }
          fromObj.options.length = fromObj.options.length - 1;
          if(NS4)
            history.go(0);
        }
      }
    }

   
    function getSortList(selectObj) {
    	var sortfields='';
    	var j=0;
    	selectAll(selectObj);
    	if(selectObj.selectedIndex != -1)
    	{
    		for(i = 0; i < selectObj.options.length; i++)
    		{
    			if(j>0) sortfields +=  ', ';
    			sortfields +=  selectObj.options[i].value;
    			j++;
    		}
    		//alert(sortfields);
    		document.adminForm.sortfields.value=sortfields;
    	}
    }

	function getFilterList(selectObj) {
		var filterfields='';
		var j=0;
		var advType=getObject('ft2');
		var simType=getObject('ft1');
		//alert(simType.checked);
		if(simType.checked) {
			selectAll(selectObj);
			if(selectObj.selectedIndex != -1) {
				for(i = 0; i < selectObj.options.length; i++) {
					if(j>0) filterfields +=  ' AND ';
					filterfields +=  selectObj.options[i].value;
					j++;
				}
			}
			if(filterfields!="") {
				document.adminForm.filterfields.value="s("+filterfields+")";
			} else {
				document.adminForm.filterfields.value="";
			}
		} else {
			if(document.adminForm.advFilterText.value!="") {
				document.adminForm.filterfields.value="a("+escape(document.adminForm.advFilterText.value)+")";
			} else {
				document.adminForm.filterfields.value="";
			}
		}
	}
  
    function selectAll(selectObj)
    {
      if(selectObj.options.length)
        for(i = 0; i < selectObj.options.length; i++)
          selectObj.options[i].selected = true;
      return false;
    }

    function loadUGIDs(selectObj)
    {
	var UGIDs='';
	var j=0;
      if(selectObj.selectedIndex != -1)
      {
          for(i = 0; i < selectObj.options.length; i++)
          {
		if(selectObj.options[i].selected) {
			if(j>0) UGIDs +=  ', ';
			UGIDs +=  selectObj.options[i].value;
			j++;
		}
          }
		document.adminForm.usergroupids.value=UGIDs;
        }
    }
    function enableListColumn(colnum) {
	var oForm;
	var colName;
	oForm=document.adminForm;
	colName="col"+colnum+"enabled";
	if(oForm.elements[colName].checked) {
		//alert("Enabled");
		oForm.col1title.readOnly=false;
		oForm.col1captions.disabled=false;
		//document.col1.disabled=false;
		oForm.col1up.disabled=false;
		oForm.col1down.disabled=false;
		oForm.col1remove.disabled=false;
		oForm.addcol1.disabled=false;
	} else {
		//alert("Disabled");
		oForm.col1title.readOnly=true;
		oForm.col1captions.disabled=true;
		//document.col1.disabled=true;
		oForm.col1up.disabled=true;
		oForm.col1down.disabled=true;
		oForm.col1remove.disabled=true;
		oForm.addcol1.disabled=true;
	}		

    }
	function filterCondition(needCond) {
		if(needCond==0) {
			document.adminForm.condition.value="";
			document.adminForm.condition.readOnly=true;
			document.adminForm.condition.setAttribute("Req",0);
		} else {
			document.adminForm.condition.value="";
			document.adminForm.condition.readOnly=false;
			document.adminForm.condition.setAttribute("Req",1);
		}

	}

<?php
		$jsListsJs		=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->document->addHeadScriptDeclaration( $jsListsJs );
?>
	<form action="index2.php?option=com_comprofiler&task=saveList" method="POST" name="adminForm">
	<table cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform">
		<tr>
			<td width="20%">URL for menu link to this list:</td>
			<td align=left  width="40%"><?php
		if ( $row->listid ) {
			$url	=	'index.php?option=com_comprofiler&amp;task=usersList&amp;listid=' . (int) $row->listid;
			echo '<a href="' . $_CB_framework->getCfg('live_site') . '/' . $url . '" target="_blank">' . $url . '</a>';
		} else {
			echo "You need to save this new list first to see the direct menu link url.";
		}
			?></td>
			<td width="40%">&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">URL for search link to this list:</td>
			<td align=left  width="40%"><?php
		if ( $row->listid ) {
			$url	=	'index.php?option=com_comprofiler&amp;task=usersList&amp;listid=' . (int) $row->listid . '&amp;searchmode=1';
			echo '<a href="' . $_CB_framework->getCfg('live_site') . '/' . $url . '" target="_blank">' . $url . '</a>';
		} else {
			echo "You need to save this new list first to see the direct menu link url.";
		}
			?></td>
			<td width="40%">Only fields appearing in list columns and on profiles and which are have the searchable attribute ON will appear in search criterias of the list.</td>
		</tr>
		<tr>
			<td>Title:</td>
			<td align=left><input type="text" name="title" mosReq="1" mosLabel="Title" class="inputbox" value="<?php echo htmlspecialchars($row->title); ?>" /></td>
			<td>Title appears in frontend on top of the list.</td>
		</tr>
		<tr>
			<td>Description:</td>
			<td align=left><?php echo $_CB_framework->displayCmsEditor( 'description', $row->description, 600, 200, 50, 7 );
				// <textarea name="description" cols="50" rows="7">< ?php echo htmlspecialchars($row->description); ? ></textarea>
			?></td>
			<td>Description appears in frontend under the title of the list.</td>
		</tr>
		<tr>
			<td>User Group to allow access to:</td>
			<td><?php echo $lists['useraccessgroup']; ?></td>
			<td>All groups above that level will also have access to the list.</td>
		</tr>
		<tr>
			<td>User Groups to Include in List:</td>
			<td><?php echo $lists['usergroups']; ?></td>
			<td><strong><font color="red">Multiple choices:</font> CTRL/CMD-click to add/remove single choices.</strong></td>
		</tr>
		<tr>
			<td>Published:</td>
			<td><?php echo $lists['published']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>Default:</td>
			<td><?php echo $lists['default']; ?></td>
			<td><strong><font color="red">WARNING:</font></strong> The default list should be the one with the lowest user groups access rights !</td>
		</tr>
		<tr>
			<td>Sort By:</td>
			<td>
				<select name="sortfieldlist">
					<?php
						for ($i=0, $n=count( $sortfields ); $i < $n; $i++) {
							$sortfield =& $sortfields[$i];
							if ( substr( $sortfield->name, -1, 1 ) != ')' ) {
								$sortfieldName	=	'`' . $sortfield->name . '`';
							} else {
								$sortfieldName	=	$sortfield->name;
							}
							echo "<option value=\"" . $sortfieldName . "\">". htmlspecialchars( getLangDefinition($sortfield->title) ) ."</option>\n";
						}
					?>
				</select><select name=direction><option value="ASC">ASC</option><option value="DESC">DESC</option></select><input type=button onclick="moveOption2(this.form.sortfieldlist, sort, this.form.direction.value);" value=" Add "><br />
				<select id=sort name=sort size="5" multiple  mosReq="1" mosLabel="Sort By">
					<?php
						for ($i=0, $n=count( $sortparts ); $i < $n; $i++) {
							$sortpart = $sortparts[$i];
							if( $sortpart['field'] != '' ) {
								if ( substr( $sortpart['field'], -1, 1 ) != ')' ) {
									$sortfiNam		=	'`' . $sortpart['field'] . '`';
								} else {
									$sortfiNam		=	$sortpart['field'];
								}
								echo '<option value="' . $sortfiNam . ' ' . $sortpart['dir'] . '">' . htmlspecialchars( getLangDefinition($sortpart['title']) ) . ' [' . $sortpart['dir'] . "]</option>\n";
							}
						}
		
					?>
				</select><br />
				<input type=button onclick="moveOptions(sort, -1);" value=" + " />
				<input type=button onclick="moveOptions(sort, 1);" value=" - " />
				<br />
				<input type=button onclick="moveOption(this.form.sort,this.form.sortfieldlist);" value=" Remove ">
			</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>Filter:</td>
			<td colspan="2">
<?php

		$simChecked="";
		$advChecked="";
		$simStyle="display:none;";
		$advStyle="display:none;";
		//echo $row->filterfields;
		$filttype=substr($row->filterfields,0,1);
		$row->filterfields=substr($row->filterfields,2,-1);
		//substr($row->filterfields,1,-1)
		// echo "row->filterfields=".$row->filterfields;
		if($filttype=="a") {
			$advChecked="CHECKED";
			$advStyle="display:block;";
		} else {
			$simChecked="checked=\"checked\"";
			$simStyle="display:block;";
		}
		$filterlists=explode(" AND ",$row->filterfields);
		$filterparts=array();
		$i=0;
		foreach($filterlists as $filterlist) {
		
			$filterlistpart=array();
			$filterlistpart=explode(" ",$filterlist);
			$filterparts[$i]['field']=str_replace("`","",$filterlistpart[0]);
			$_CB_database->setQuery("SELECT title FROM #__comprofiler_fields WHERE name='".$filterparts[$i]['field']."' LIMIT 1");
			$filtertitle=$_CB_database->loadResult();
			$filterparts[$i]['value']=$filterlist;
			$filterparts[$i]['title']=str_replace(array("'","`"),"",str_replace($filterparts[$i]['field'],getLangDefinition($filtertitle),$filterlist));
		
			$i++;
		}
?>
				<label for=ft1 ><input type="radio" <?php echo $simChecked; ?> id="ft1" onclick="javascript:shDiv('simFilter',1);shDiv('advFilter',0);" name=filtertype value="0" checked="checked" />Simple </label><label for=ft2 ><input type="radio" <?php echo $advChecked; ?> onclick="javascript:shDiv('simFilter',0);shDiv('advFilter',1);" id="ft2" name="filtertype" value="1" />Advanced </label>
				<br />
				<div id="simFilter" name="simFilter" style="<?php echo $simStyle; ?>" >
				<select name="filterfieldlist">
					<?php
						foreach ($filterfields AS $filterfield) {
							echo "<option value=\"`".$filterfield->name."`\">". htmlspecialchars( getLangDefinition($filterfield->title) ) ."\n";
						}
		
					?>
				</select>
				<select name=comparison onchange="javascript:filterCondition(this.options[this.selectedIndex].getAttribute('needCond'));">
					<option value=">" needCond="1">Greater Than</option>
					<option value=">=" needCond="1">Greater Than or Equal To</option>
					<option value="&lt;" needCond="1">Less Than</option>
					<option value="&lt;=" needCond="1">Less Than or Equal To</option>
					<option value="=" needCond="1">Equal To</option>
					<option value="!=" needCond="1">Not Equal To</option>
					<option value="IS NULL" needCond="0">Is NULL</option>
					<option value="IS NOT NULL"  needCond="0">Is Not NULL</option>
					<option value="LIKE"  needCond="1">Like</option>
				</select>
				<input type=text name=condition value="" Req=1 />
				<input type=button onclick="moveOption3(this.form.filterfieldlist, filter, this.form.comparison.value, this.form.condition.value);" value=" Add ">
				<br />
				<select id=filter name=filter size="5" multiple  mosReq=0 mosLabel="Filter By">
					<?php
						foreach ($filterparts AS $filterpart) {
							if($filterpart['value']!='') {
								echo "<option value=\"".$filterpart['value']."\">".stripslashes(utf8RawUrlDecode($filterpart['title']))."\n";	//BB todo sortout htmlspecialchars...not compatible with utf8rawdecode
							}
						}
		
					?>
				</select><br />
				<input type=button onclick="moveOptions(filter, -1);" value=" + " />
				<input type=button onclick="moveOptions(filter, 1);" value=" - " />
				<br />
				<input type=button onclick="moveOption4(this.form.filter,this.form.filterfieldlist);" value=" Remove ">
				</div>
				<div id="advFilter" name="advFilter" style="<?php echo $advStyle; ?>">
					<textarea name="advFilterText" cols="50" rows="7"><?php echo stripslashes(utf8RawUrlDecode($row->filterfields)); 	//BB todo sortout htmlspecialchars...not compatible with utf8rawdecode
					?></textarea>
				</div>
			</td>
		</tr>
	</table>
	<table cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform">
		<tr>
			<td width="100%" colspan="3" style="text-align:center;">
				<strong>Note:</strong> fields must be on profile to appear in this list and be visible on the users-list.
			</td>
		</tr>
		<tr>
			<td width="33%">
				Enable Column 1: <input type=checkbox <?php /* onclick="javascript:enableListColumn(1);" */ ?> name="col1enabled" <?php if($row->col1enabled == 1) echo ' checked="checked" ';  ?> value=1 ><br />
				Column 1 Title:<br />
				<input type="text" name="col1title" mosReq=0 mosLabel="Column 1 Title" class="inputbox" value="<?php echo htmlspecialchars($row->col1title); ?>" /><br />
				Column 1 Captions:<input type=checkbox name=col1captions <?php if($row->col1captions == 1) echo " CHECKED ";  ?> value=1 ><br />
				<select id=col1 size="5" multiple name=col1[] >
					<?php
					echo $col1options;
					?>
				</select><br />
				<input name=col1up type=button onclick="moveOptions(col1, -1);" value=" + " />
				<input name=col1down type=button onclick="moveOptions(col1, 1);" value=" - " />
				<br />
				<input name=col1remove type=button onclick="moveOption(col1,this.form.fieldlist);" value=" Remove ">
			</td>
			<td width="33%" rowspan=3 valign=center align=center>Field List:<br />
				<input name=addcol1 type=button onclick="moveOption(this.form.fieldlist, col1);" value=" <- Add ">
				<input type=button onclick="moveOption(this.form.fieldlist, col2);" value=" Add -> "><br />
				<select name="fieldlist" size="10" multiple>
					<?php
						foreach ( $fields as $field ) {
							echo "<option value=\"".$field->fieldid."\">".htmlspecialchars( getLangDefinition($field->title) )."\n";
						}
		
					?>
				</select><br />
				<input type=button onclick="moveOption(this.form.fieldlist, col3);" value=" <- Add ">
				<input type=button onclick="moveOption(this.form.fieldlist, col4);" value=" Add -> ">
			</td>
			<td width="33%">
				Enable Column 2: <input type=checkbox name=col2enabled <?php if($row->col2enabled == 1) echo " CHECKED ";  ?> value=1 ><br />
				Column 2 Title:<br />
				<input type="text" name="col2title" mosReq=0 mosLabel="Column 2 Title" class="inputbox" value="<?php echo htmlspecialchars($row->col2title); ?>" /><br />
				Column 2 Captions:<input type=checkbox name=col2captions <?php if($row->col2captions == 1) echo " CHECKED ";  ?> value=1 ><br />
				<select id=col2 size="5" multiple name=col2[] >
					<?php
					echo $col2options;
					?>
				</select><br />
				<input type=button onclick="moveOptions(col2, -1);" value=" + " />
				<input type=button onclick="moveOptions(col2, 1);" value=" - " />
				<br />
				<input type=button onclick="moveOption(col2,this.form.fieldlist);" value=" Remove ">
			</td>
		</tr>
		<tr>
		</tr>
		<tr>
			<td width="33%">
				Enable Column 3: <input type=checkbox name=col3enabled <?php if($row->col3enabled == 1) echo " CHECKED ";  ?> value=1 /><br />
				Column 3 Title:<br />
				<input type="text" name="col3title" mosReq=0 mosLabel="Column 3 Title" class="inputbox" value="<?php echo htmlspecialchars($row->col3title); ?>" /><br />
				Column 3 Captions:<input type=checkbox name=col3captions <?php if($row->col3captions == 1) echo " CHECKED ";  ?> value=1 ><br />
				<select id=col3 size="5" multiple name=col3[]>
					<?php
					echo $col3options;
					?>
				</select><br />
				<input type=button onclick="moveOptions(col3, -1);" value=" + " />
				<input type=button onclick="moveOptions(col3, 1);" value=" - " />
				<br />
				<input type=button onclick="moveOption(col3,this.form.fieldlist);" value=" Remove ">
			</td>
			<td width="33%">
				Enable Column 4: <input type=checkbox name=col4enabled <?php if($row->col4enabled == 1) echo " CHECKED ";  ?> value=1 ><br />
				Column 4 Title:<br />
				<input type="text" name="col4title" mosReq=0 mosLabel="Column 4 Title" class="inputbox" value="<?php echo htmlspecialchars($row->col4title); ?>" /><br />
				Column 4 Captions:<input type=checkbox name=col4captions <?php if($row->col4captions == 1) echo " CHECKED ";  ?> value=1 ><br />
				<select id=col4 size="5" multiple name=col4[]>
					<?php
					echo $col4options;
					?>
				</select><br />
				<input type=button onclick="moveOptions(col4, -1);" value=" + " />
				<input type=button onclick="moveOptions(col4, 1);" value=" - " />
				<br />
				<input type=button onclick="moveOption(col4,this.form.fieldlist);" value=" Remove ">
			</td>
		</tr>
	</table>
<?php
	// params:
	if ( $paramsEditorHtml ) {
		foreach ( $paramsEditorHtml as $paramsEditorHtmlBlock ) {
?>
		<table class="adminform" cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<th colspan="2">
					<?php echo $paramsEditorHtmlBlock['title']; ?>
				</th>
			</tr>
			<tr>
				<td>
					<?php echo $paramsEditorHtmlBlock['content']; ?>
				</td>
			</tr>
		</table>
<?php
		}
	}
?>

  <table cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform">
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>

  </table>
  <input type="hidden" name="sortfields" value="<?php echo $row->sortfields; ?>" />
  <input type="hidden" name="filterfields" value="<?php echo $row->filterfields; ?>" />
  <input type="hidden" name="usergroupids" value="<?php echo $row->usergroupids; ?>" />
  <input type="hidden" name="listid" value="<?php echo $row->listid; ?>" />
  <input type="hidden" name="ordering" value="<?php echo $row->ordering; ?>" />
  <input type="hidden" name="option" value="com_comprofiler" />
  <input type="hidden" name="task" value="" />
  <?php
	echo cbGetSpoofInputTag( 'list' );
  ?>
</form>
  
<?php 	
	}


	function showFields( &$rows, $pageNav, $search, $option ) {
		global $ueConfig;

		HTML_comprofiler::secureAboveForm('showFields');

		outputCbTemplate( 2 );
		outputCbJs( 2 );

		global $_CB_Backend_Title;
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-fields', "CB Field Manager" ) );

		HTML_comprofiler::_saveOrderJs( 'savefieldorder' );
?>
<form action="index2.php" method="post" name="adminForm">
  <table cellpadding="4" cellspacing="0" border="0" width="100%">
    <tr>
      <td>Search: <input type="text" name="search" value="<?php echo $search;?>" class="inputbox" onChange="document.adminForm.submit();" />
      </td>
    </tr>
  </table>
  <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
   <thead>
    <tr>
      <th width="2%" class="title">#</th>
      <th width="3%" class="title"> <input type="checkbox" name="toggle" value="" <?php echo 'onClick="checkAll(' . count($rows) . ');"'; ?> />
      </th>
      <th width="10%" class="title">Name</th>
      <th width="10%" class="title">Title</th>
      <th width="10%" class="title">Type</th>
      <th width="10%" class="title">Tab</th>
      <th width="5%" class="title">Required?</th>
      <th width="5%" class="title">Profile?</th>
      <th width="5%" class="title">Registration?</th>
      <th width="5%" class="title">Searchable?</th>
      <th width="5%" class="title">Published?</th>
      <th width="5%" class="title" colspan="2">Re-Order</th>
	  <th width="1%"><a href="javascript: cbsaveorder( <?php echo count( $rows )-1; ?> )"><img src="images/filesave.png" border="0" width="16" height="16" alt="Save Order" /></a></th>
    </tr>
   </thead>
   <tbody>
<?php
		$k = 0;
		$imgpath='../components/com_comprofiler/images/';
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row =& $rows[$i];
			$img = $row->required ? 'tick.png' : 'publish_x.png' ;
			$task = $row->required ? 'fieldRequiredNo' : 'fieldRequiredYes' ;
			switch ($row->profile) {
				case 0:
					$img2	= 'publish_x.png';
					$task2	= 'fieldProfileYes1';
					$text2	= '';
					break;
				case 1:
					$img2	= 'tick.png';
					$task2	= 'fieldProfileYes2';
					$text2	= '<span style="color:green;">(1 Line)</span>';
					break;
				case 2:
				default:
					$img2	= 'tick.png';
					$task2	= 'fieldProfileNo';
					$text2	= '<span style="color:green;">(2 Lines)</span>';
					break;
			}
			$img3  = $row->published ?  'tick.png' : 'publish_x.png';
			$task3 = $row->published ?  'fieldPublishedNo' : 'fieldPublishedYes';
			$img4  = $row->registration ?  'tick.png' : 'publish_x.png';
			$task4 = $row->registration ?  'fieldRegistrationNo' : 'fieldRegistrationYes';
			$img5  = $row->searchable ?  'tick.png' : 'publish_x.png';
			$task5 = $row->searchable ?  'fieldSearchableNo' : 'fieldSearchableYes';
?>
    <tr class="<?php echo "row$k"; ?>">
      <td><?php echo $i+1+$pageNav->limitstart;?></td>
      <td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->fieldid; ?>" onClick="isChecked(this.checked);" /></td>
      <td> <a href="#editField" onClick="return listItemTask('cb<?php echo $i;?>','editField')">
        <?php echo htmlspecialchars( $row->name ); ?> </a> </td>
      <td><?php echo htmlspecialchars( getLangDefinition( $row->title ) ); ?></td>
      <td><?php
      		if ( $row->pluginid && ( $row->fieldpluginpublished == 0 ) ) {
      			echo '<span style="color:red;" title="field will not be visible as field plugin \'' . htmlspecialchars( $row->fieldpluginname ) . '\' is not published.">';
      		}
      		if ( $row->type == 'connections' && $ueConfig['allowConnections'] == 0 ) {
      			echo '<span style="color:red;" title="field will not be visible as connections are not enabled in CB configuration.">';
      		}
			echo htmlspecialchars( $row->type );
     		if ( ( $row->pluginid && ( $row->fieldpluginpublished == 0 ) ) || ( $row->type == 'connections' && $ueConfig['allowConnections'] == 0 ) ) {
     			echo '</span>';
     		}
	?></td>
      <td><?php
      		if ( $row->tabenabled == 0 ) {
      			echo '<span style="color:red;" title="field will not be visible as tab is not enabled.">';
      		} elseif ( $row->tabpluginid && ( $row->pluginpublished == 0 ) ) {
      			echo '<span style="color:red;" title="field will not be visible as tab\'s plugin \'' . htmlspecialchars( $row->pluginname ) . '\' is not published.">';
      		}
			echo htmlspecialchars( getLangDefinition( $row->tab ) );
     		if ( $row->tabenabled == 0 || ( $row->tabpluginid && ( $row->pluginpublished == 0 ) ) ) {
     			echo '</span>';
     		}
	  ?></td>
      <td width="10%"><a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')"><img src="<?php echo $imgpath.$img;?>" width="16" height="16" border="0" alt="" /></a></td>
      <td width="10%"><a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task2;?>')"><img src="<?php echo $imgpath.$img2;?>" width="16" height="16" border="0" alt="" /><?php echo $text2;?></a></td>
      <td width="10%"><a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task4;?>')"><img src="<?php echo $imgpath.$img4;?>" width="16" height="16" border="0" alt="" /></a></td>
      <td width="10%"><?php
      if ( $row->tablecolumns != '' && ! in_array( $row->type, array( 'password', 'userparams' ) ) ) {
		?><a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task5;?>')"><img src="<?php echo $imgpath.$img5;?>" width="16" height="16" border="0" alt="" /></a><?php
      } else {
      	 echo '<img src="' . $imgpath . $img5 . '" width="16" height="16" border="0" alt="" />';
      }
		?></td>
<?php		if ( $row->sys == 1 ) {
?>
      <td width="10%"><img src="<?php echo $imgpath.$img3;?>" width="16" height="16" border="0" alt="" title="System-fields cannot be published/unpublished here.<?php if ( in_array( $row->name, array( 'name', 'firstname', 'middlename', 'lastname' ) ) ) echo ' Name-fields publishing depends on your setting in global CB config.'; ?>" /></td>
<?php
			} else {
?>
      <td width="10%"><a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task3;?>')"><img src="<?php echo $imgpath.$img3;?>" width="16" height="16" border="0" alt="" /></a></td>
<?php
			}
?>
      <td>
	<?php    if (($i > 0 || ($i+$pageNav->limitstart > 0)) && $row->tab == @$rows[$i-1]->tab) { ?>
         <a href="#reorder" onClick="return listItemTask('cb<?php echo $i;?>','orderupField')">
            <img src="images/uparrow.png" width="12" height="12" border="0" alt="Move Up" />
         </a>
	<?php    } ?>
      </td>
      <td>
	<?php    if (($i < $n-1 || $i+$pageNav->limitstart < $pageNav->total-1) && $row->tab == @$rows[$i+1]->tab) { ?>
         <a href="#reorder" onClick="return listItemTask('cb<?php echo $i;?>','orderdownField')">
            <img src="images/downarrow.png" width="12" height="12" border="0" alt="Move Down" />
         </a>
	<?php    } ?>
      </td>
	  <td align="center">
	  <input type="text" name="order[]" size="5" value="<?php echo htmlspecialchars( $row->ordering ); ?>" class="text_area" style="text-align: center" />
	  </td>
    </tr>
    <?php $k = 1 - $k; } ?>
   </tbody>
   <tfoot>
    <tr>
      <th align="center" colspan="14"> <?php echo $pageNav->getListFooter(); ?></th>
    </tr>
   </tfoot>
  </table>
  <input type="hidden" name="option" value="<?php echo $option;?>" />
  <input type="hidden" name="task" value="showField" />
  <input type="hidden" name="boxchecked" value="0" />
  <?php
	echo cbGetSpoofInputTag( 'field' );
  ?>
</form>
<?php
	}



	function editfield( &$row, $lists, $fieldvalues, $option, $paramsEditorHtml ) {
		global $_CB_framework, $_CB_database;

		HTML_comprofiler::secureAboveForm('editfield');
		outputCbTemplate( 2 );
		outputCbJs( 2 );
		initToolTip( 2 );

		global $_CB_Backend_Title;
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-fields', "Community Builder Field" . ': <small>' . ( $row->fieldid ? "Edit" . ' [ ' . htmlspecialchars( getLangDefinition( $row->title ) ) . ' ] ' : "New" ) . '</small>' ) );

		if ( $row->fieldid && ( ! $row->published ) ) {
			echo '<div class="cbWarning">Field is not published</div>' . "\n";
		}
		if ( $row->pluginid ) {
			$plugin		=	new moscomprofilerPlugin( $_CB_database );
			if ( ! $plugin->load( (int) $row->pluginid ) ) {
				echo '<div class="cbWarning">Plugin is not installed</div>' . "\n";				
			} else {
				if ( ! $plugin->published ) {
					echo '<div class="cbWarning">Plugin is not published</div>' . "\n";
				}
			}
		}

//		$_CB_framework->outputCbJQuery( "var cbTypeState = $('#type').val();	$('#type').change(function() { if ( cbTypeState != $('#type').val() ) submitbutton('reloadField') } ).change();" );
//		outputCbJs( 2 );
	if($row->fieldid > 0) {
		$_CB_framework->outputCbJQuery( 'document.adminForm.name.readOnly=true; document.adminForm.name.disabled=true; document.adminForm.type.disabled=true;');
	}
//		disableAll();
//		selType('".$row->type."');	

		ob_start();
?>  
   function submitbutton(pressbutton) {
     if ( (pressbutton == 'showField') || (pressbutton == 'reloadField') ) {
       document.adminForm.type.disabled=false;
       <?php echo $_CB_framework->saveCmsEditorJS( 'description' );
			if ( $row->type == 'editorta' ) {
				echo $_CB_framework->saveCmsEditorJS( 'default' );
			}
       ?>
       submitform(pressbutton);
       return;
     }
     var coll = document.adminForm;
     var errorMSG = '';
     var iserror=0;
     if (coll != null) {
       var elements = coll.elements;
       // loop through all input elements in form
       for (var i=0; i < elements.length; i++) {
         // check if element is mandatory; here mosReq=1
         if ( (typeof(elements.item(i).getAttribute('mosReq')) != "undefined") && (elements.item(i).getAttribute('mosReq') == 1) ) {
           if (elements.item(i).value == '') {
             //alert(elements.item(i).getAttribute('mosLabel') + ':' + elements.item(i).getAttribute('mosReq'));
             // add up all error messages
             errorMSG += elements.item(i).getAttribute('mosLabel') + ' : <?php echo _UE_REQUIRED_ERROR; ?>\n';
             // notify user by changing background color, in this case to red
             elements.item(i).style.backgroundColor = "red";
             iserror=1;
           }
         }
       }
     }
     if(iserror==1) {
       alert(errorMSG);
     } else {
       document.adminForm.type.disabled=false;
       <?php echo $_CB_framework->saveCmsEditorJS( 'description' );
			if ( $row->type == 'editorta' ) {
				echo $_CB_framework->saveCmsEditorJS( 'cb_default' );
			}
       ?>
       submitform(pressbutton);
     }
   }
<?php
		$jsTop		=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->document->addHeadScriptDeclaration( $jsTop );
		ob_start();
?>
	function insertRow() {
		// Create and insert rows and cells into the first body.
//		var i = $('#adminForm input[name=valueCount]').val( Number( $('#adminForm input[name=valueCount]').val() ) + 1 ).val();
//		$('#fieldValuesBody').append('<tr><td><input id=\"vNames'+i+'\" name=\"vNames[' + i + ']\" /></td></tr>');
		var i = $('#adminForm input[name=valueCount]').val( Number( $('#adminForm input[name=valueCount]').val() ) + 1 ).val();
		$('#fieldValuesList').append('<li><input id=\"vNames'+i+'\" name=\"vNames[]\" /></li>');
		$('#vNames'+i).hide().slideDown('medium').focus();
	}
	
	function disableAll() {
		$('#divValues,#divColsRows,#divWeb,#divText').hide('fast', function() { $(this).css('visibility','visible') } );
		$('#vNames0').attr('mosReq','0');
	}
	
	function selType(sType) {
		var elem;
		//alert(sType);
		disableAll();
		switch (sType) {
			case 'editorta':
			case 'textarea':
				$('#divText,#divColsRows').show('fast');
				break;
	
			case 'emailaddress':
			case 'password':
			case 'text':
			case 'integer':
			case 'predefined':
				$('#divText').show('fast');
				break;
	
			case 'select':
			case 'multiselect':
				$('#divValues').show('fast');
				$('#vNames0').attr('mosReq','1');
				break;
	
			case 'radio':
			case 'multicheckbox':
				$('#divValues,#divColsRows').show('fast');
				$('#vNames0').attr('mosReq','1');
				break;
	
			case 'webaddress':
				$('#divText,#divWeb').show('fast');
				break;
	
			case 'delimiter':
			default:
		}
	}

  function prep4SQL(o){
	if(o.value!='') {
		var cbsqloldvalue, cbsqlnewvalue;
		o.value=o.value.replace('cb_','');
		cbsqloldvalue = o.value;
		o.value=o.value.replace(/[^a-zA-Z0-9]+/g,'');
		cbsqlnewvalue = o.value;
		o.value='cb_' + o.value;
		if (cbsqloldvalue != cbsqlnewvalue) {
			alert("Warning: SQL name of field has been changed to fit SQL constraints")
		}
	}
  }
  var cbTypeState = $('#type').val();	$('#type').change(function() { selType(this.options[this.selectedIndex].value); if ( cbTypeState != $('#type').val() ) submitbutton('reloadField') } ).change();
  $('#name').change(function() { if ( ! $('#name').attr('disabled') ) { prep4SQL(this); } } ).change();
  $('#insertrow').click(function() { insertRow(); } );
  $('#fieldValuesList').sortable( { items: 'li', containment: 'parent', animated: true, placeholder: 'fieldValuesList-selected' } );
//  $('#mainparams').sortable( { items: 'tr', containment: 'parent', animated: true } );
  /* $('#adminForm').submit(function() { return submitbutton(''); } );	*/
  disableAll();
  selType('<?php echo $row->type; ?>'); 
<?php
$jsContent	=	ob_get_contents();
ob_end_clean();

		$_CB_framework->outputCbJQuery( $jsContent, 'ui-all' );
?>
<form action="index2.php?option=com_comprofiler&task=saveField" method="POST" id="adminForm" name="adminForm">
<?php
		if ( $paramsEditorHtml ) {
?>
  <table cellspacing="0" cellpadding="0" width="100%">
   <tr valign="top">
    <td width="60%" valign="top">
<?php
		}
?>

	<table cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform" id="mainparams">
		<tr>
			<td width="20%">Type:</td>
			<td width="20%"><?php echo $lists['type']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Tab:</td>
			<td width="20%"><?php echo $lists['tabs']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Name:</td>
			<td align=left  width="20%"><input type="text" id="name" name="name" maxlength='64' mosReq="1" mosLabel="Name" class="inputbox" value="<?php echo htmlspecialchars( $row->name ); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Title:</td>
			<td width="20%" align=left><input type="text" name="title" mosReq="1" mosLabel="Title" class="inputbox" value="<?php echo htmlspecialchars( $row->title ); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">Description/"i" field-tip: text or HTML:</td>
		</tr>
		<tr>
			<td colspan="3" align=left><?php echo $_CB_framework->displayCmsEditor( 'description', $row->description, 600 /* ( $row->type == 'delimiter' ? 600 : 286 ) */ , 200, 50, 7 );
			// <textarea name="description" cols="40" rows="6" maxlength='255' mosReq="0" mosLabel="Description" class="inputbox">< ?php echo htmlspecialchars( $row->description ); ? ></textarea>
			?></td>
		</tr>
<?php
		if ( $row->type != 'delimiter' ) { ?>

		<tr>
<?php		if ( $row->type == 'editorta' ) {	?>
			<td colspan="3"><?php echo CBTxt::T( "Pre-filled default value at registration only" ); ?>:</td>
		</tr>
		<tr>
			<td colspan="3"><?php
				echo $_CB_framework->displayCmsEditor( 'cb_default', $row->default, 600, 200, 50, 7 );
			?></td>
<?php
			} else {
				?>
			<td width="20%"><?php echo CBTxt::T( "Pre-filled default value at registration only" ); ?>:</td>
			<td width="20%">
				<input type="text" name="cb_default" mosLabel="<?php echo CBTxt::T( "Default value" ); ?>" class="inputbox" value="<?php echo htmlspecialchars( $row->default ); ?>" />
			</td>
			<td>&nbsp;</td><?php
			}
			?>
		</tr>
<?php
		}
?>

		<tr>
			<td width="20%">Required?:</td>
			<td width="20%"><?php echo $lists['required']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Show on Profile?:</td>
			<td width="20%"><?php echo $lists['profile']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Display field title in Profile?:</td>
			<td width="20%"><?php echo $lists['displaytitle']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Searchable in users-lists?:</td>
			<td width="20%"><?php echo $lists['searchable']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">User Read Only?:</td>
			<td width="20%"><?php echo $lists['readonly']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Show at Registration?:</td>
			<td width="20%"><?php echo $lists['registration']; ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Published:</td>
			<td width="20%"><?php echo ( $row->sys == 1 ? ( $row->published ? _UE_YES : _UE_NO ) . ' (System-fields cannot be published/unpublished here.' . ( in_array( $row->name, array( 'name', 'firstname', 'middlename', 'lastname' ) ) ? ' Name-fields publishing depends on your setting in global CB config.)' : ')' ) : $lists['published'] ); ?></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Size:</td>
			<td width="20%"><input type="text" name="size" mosLabel="Size" class="inputbox" value="<?php echo htmlspecialchars( $row->size ); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
	</table>
	<div id="page1"  class="pagetext">
		
	</div>
	<div id="divText"  class="pagetext">
		<table cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform">
		<tr>
			<td width="20%">Max Length:</td>
			<td width="20%"><input type="text" name="maxlength" mosLabel="Max Length" class="inputbox" value="<?php echo htmlspecialchars( $row->maxlength ); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
		</table>
	</div>
	<div id="divColsRows"  class="pagetext">
		<table cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform">
		<tr>
			<td width="20%">Cols:</td>
			<td width="20%"><input type="text" name="cols" mosLabel="Cols" class="inputbox" value="<?php echo htmlspecialchars( $row->cols ); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%">Rows:</td>
			<td width="20%"><input type="text" name="rows"  mosLabel="Rows" class="inputbox" value="<?php echo htmlspecialchars( $row->rows ); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
		</table>
	</div>
	<div id="divWeb"  class="pagetext">
		<table cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform">
		<tr>
			<td width="20%">Type:</td>
			<td width="20%"><?php echo $lists['webaddresstypes']; ?></td>
			<td>&nbsp;</td>
		</tr>
		</table>
	</div>
	<div id="divValues" style="text-align:left;">
		Use the table below to add new values.<br />
		<input type=button id="insertrow" value="Add a Value" />
		<table align="left" id="divFieldValues" cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform" >
		<thead>
		<tr>
			<th width="20%">Name</th>
		</tr>
		</thead>
		<tbody id="fieldValuesBody">
		<tr>
			<td>
				<ul id="fieldValuesList">
	<?php
		//echo "count:".count( $fieldvalues );
		//print_r (array_values($fieldvalues));
		for ($i=0, $n=count( $fieldvalues ); $i < $n; $i++) {
			//print "count:".$i;
			$fieldvalue = $fieldvalues[$i];
			if ($i==0) $req =1;
			else $req = 0;
			echo "\n<li><input type='text' mosReq='$req'  mosLabel='Value' value=\"" . htmlspecialchars( $fieldvalue->fieldtitle ) . "\" name=\"vNames[]\" id=\"vNames".$i."\" /></li>\n";
		}
		if(count( $fieldvalues )< 1) {
			echo "\n<li><input type='text' mosReq='0'  mosLabel='Value' value='' name='vNames[]' /></li>\n";
			$i=0;
		}
	?>
				</ul>
			</td>
		</tr>
		</tbody>
	  </table>
	</div>
<?php
/*
		//echo "count:".count( $fieldvalues );
		//print_r (array_values($fieldvalues));
		for ($i=0, $n=count( $fieldvalues ); $i < $n; $i++) {
			//print "count:".$i;
			$fieldvalue = $fieldvalues[$i];
			if ($i==0) $req =1;
			else $req = 0;
			echo "<tr>\n<td width=\"20%\"><input type='text' mosReq='$req'  mosLabel='Value' value=\"" . htmlspecialchars( $fieldvalue->fieldtitle ) . "\" name=\"vNames[".$i."]\" id=\"vNames".$i."\" /></td></tr>\n";
		}
		if(count( $fieldvalues )< 1) {
			echo "<tr>\n<td width=\"20%\"><input type='text' mosReq='0'  mosLabel='Value' value='' name=vNames[0] /></td></tr>\n";
			$i=0;
		}
	?>
		</tbody>
		</table>
	</div>
<?php
*/
		if ( $paramsEditorHtml ) {
?>
    </td>
    <td width="40%" valign="top">
<?php
			foreach ( $paramsEditorHtml as $paramsEditorHtmlBlock ) {
?>
		<table class="adminform" cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<th colspan="2">
					<?php echo $paramsEditorHtmlBlock['title']; ?>
				</th>
			</tr>
			<tr>
				<td>
					<?php echo $paramsEditorHtmlBlock['content']; ?>
				</td>
			</tr>
		</table>
<?php
			}
?>
    </td>
   <tr>
  </table>
<?php
		}
?>
  <table cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform">
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>

  </table>
  <input type="hidden" name="valueCount" value=<?php echo $i; ?> />
  <input type="hidden" name="oldtabid" value="<?php echo htmlspecialchars( $row->tabid ); ?>" />
  <input type="hidden" name="fieldid" value="<?php echo (int) $row->fieldid; ?>" />
  <input type="hidden" name="ordering" value="<?php echo htmlspecialchars( $row->ordering ); ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="task" value="" />
  <?php
	echo cbGetSpoofInputTag( 'field' );
  ?>
</form>
<?php 
}


	function showTabs( &$rows, $pageNav, $search, $option ) {
		global $_CB_framework;
		HTML_comprofiler::secureAboveForm('showTabs');

		outputCbTemplate( 2 );
		outputCbJs( 2 );

		global $_CB_Backend_Title;
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-tabs', "CB Tab Manager" ) );

		HTML_comprofiler::_saveOrderJs( 'savetaborder' );
?>
<form action="index2.php" method="post" name="adminForm">
  <table cellpadding="4" cellspacing="0" border="0" width="100%">
    <tr>
      <td>Search: <input type="text" name="search" value="<?php echo $search;?>" class="inputbox" onChange="document.adminForm.submit();" />
      </td>
    </tr>
  </table>
  <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
   <thead>
    <tr>
      <th width="1%" class="title">#</th>
      <th width="1%" class="title"> <input type="checkbox" name="toggle" value="" <?php echo 'onClick="checkAll(' . count($rows) . ');"'; ?> /></th>
      <th width="17%" class="title">Title</th>
      <th width="30%" class="title">Description</th>
      <th width="10%" class="title">Display</th>
      <th width="12%" class="title">Plugin</th>
      <th width="5%" class="title">Published</th>
      <th width="10%" class="title">Access</th>
      <th width="5%" class="title">Position</th>
      <th width="5%" class="title" colspan="2">Re-Order</th>
      <th width="3%" colspan="2"><a href="javascript: cbsaveorder( <?php echo count( $rows )-1; ?> )"><img src="images/filesave.png" border="0" width="16" height="16" alt="Save Order" /></a></th>
      <th width="1%" class="title">Tabid</th>
    </tr>
   </thead>
   <tbody>
<?php
		$k = 0;
		$imgpath='../components/com_comprofiler/images/';
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row =& $rows[$i];
			if($row->sys==2) {
				$img3='tick.png';
				$task3=null;
			} else {
			        $img3 = $row->enabled ?  'tick.png' : 'publish_x.png';
			        $task3 = $row->enabled ?  'tabPublishedNo' : 'tabPublishedYes';
			}
?>
    <tr class="<?php echo "row$k"; ?>">
      <td><?php echo $i+1+$pageNav->limitstart;?></td>
      <td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->tabid; ?>" onclick="isChecked(this.checked);" /></td>
      <td> <a href="#editTab" onclick="return listItemTask('cb<?php echo $i;?>','editTab')">
        <?php echo htmlspecialchars( getLangDefinition($row->title) ); ?> </a> </td>
	<td><?php echo htmlspecialchars( getLangDefinition($row->description) ); ?></td>
	<td><?php echo htmlspecialchars( $row->displaytype ); ?></td>
	<td><?php
      		if ( $row->pluginid && ( $row->pluginpublished == 0 ) ) {
      			echo '<span style="color:red;" title="tab will not be visible as plugin is not published.">';
      		}
			echo ( ( $row->pluginname) ? htmlspecialchars( $row->pluginname ) : "-" );
     		if ( $row->pluginid && ( $row->pluginpublished == 0 ) ) {
     			echo '</span>';
     		}
	  ?></td>
	<?php $task3 = ($task3==null) ? " " : "onClick=\"return listItemTask('cb".$i."','".$task3."')\"" ; ?>
      <td><a href="javascript: void(0);" <?php echo $task3; ?> ><img src="<?php echo $imgpath.$img3;?>" width="16" height="16" border="0" alt="" /></a></td>
	  <td><?php 
	  		if ( $row->useraccessgroupid >= 0 ) {
		  		echo '<span style="color:red;">' . $_CB_framework->acl->get_group_name( (int) $row->useraccessgroupid ) . '</span>';
	  		} elseif ( $row->useraccessgroupid == -2 ) {
	  			echo '<span style="color:green;">Everybody</span>';
	  		} elseif ( $row->useraccessgroupid == -1 ) {
	  			echo '<span style="color:orange;">All Registered Users</span>';
	  		}
	  ?></td>
	<td><?php echo htmlspecialchars( substr( $row->position, 0, 3 ) == 'cb_' ? substr( $row->position, 3 ) : $row->position ); ?></td>
      <td>
	<?php    if (($i > 0 || ($i+$pageNav->limitstart > 0)) && $row->position == @$rows[$i-1]->position) { ?>
         <a href="#reorder" onClick="return listItemTask('cb<?php echo $i;?>','orderupTab')">
            <img src="images/uparrow.png" width="12" height="12" border="0" alt="Move Up" />
         </a>
	<?php    } ?>
      </td>
      <td>
	<?php    if (($i < $n-1 || $i+$pageNav->limitstart < $pageNav->total-1) && $row->position == @$rows[$i+1]->position) { ?>
         <a href="#reorder" onClick="return listItemTask('cb<?php echo $i;?>','orderdownTab')">
            <img src="images/downarrow.png" width="12" height="12" border="0" alt="Move Down" />
         </a>
	<?php    } ?>
      </td>
	  <td align="center" colspan="2">
	  <input type="text" name="order[]" size="5" value="<?php echo htmlspecialchars( $row->ordering ); ?>" class="text_area" style="text-align: center" />
	  </td>      
	  <td style="text-align:right;"><?php echo htmlspecialchars( $row->tabid ); ?></td>
    </tr>
    <?php $k = 1 - $k; } ?>
   </tbody>
   <tfoot>
    <tr>
      <th align="center" colspan="15"> <?php echo $pageNav->getListFooter(); ?></th>
    </tr>
   </tfoot>
  </table>
  <input type="hidden" name="option" value="<?php echo $option;?>" />
  <input type="hidden" name="task" value="showTab" />
  <input type="hidden" name="boxchecked" value="0" />
  <?php
	echo cbGetSpoofInputTag( 'tab' );
  ?>
</form>
<?php }

	function edittab( &$row, $option, &$lists, $tabid, &$paramsEditorHtml ) {
		global $_CB_framework, $task,$_CB_database, $_PLUGINS;

		HTML_comprofiler::secureAboveForm('edittab');
		outputCbTemplate( 2 );
		outputCbJs( 2 );
		initToolTip( 2 );
		$_CB_framework->outputCbJQuery( '' );

		global $_CB_Backend_Title;
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-tabs', "Community Builder Tab: <small>" . ( $row->tabid ? "Edit" . ' [ '. htmlspecialchars( getLangDefinition( $row->title ) ) .' ]' : "New" ) . '</small>' ) );

		if ( $row->tabid && ( ! $row->enabled ) ) {
			echo '<div class="cbWarning">Tab is not published</div>' . "\n";
		}

		ob_start();
?>
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'showTab') {
		        <?php echo $_CB_framework->saveCmsEditorJS( 'description' ); ?>
				submitform( pressbutton );
				return;
			}
			var r = new RegExp("[^0-9A-Za-z]", "i");

			// do field validation
			if (trim(form.title.value) == "") {
				alert( "You must provide a title." );
			} else {
		        <?php echo $_CB_framework->saveCmsEditorJS( 'description' ); ?>
				submitform( pressbutton );
			}
		}
<?php
		$js			=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->document->addHeadScriptDeclaration( $js );
?>
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:10000;"></div>

	<form action="index2.php?option=com_comprofiler&task=saveTab" method="POST" name="adminForm">
	<table cellspacing="0" cellpadding="0" width="100%">
	<tr valign="top">
		<td width="60%" valign="top">
			<table class="adminform">
			<tr>
				<th colspan="3">
				Tab Details
				</th>
			</tr>
			<tr>
				<td width="20%">Title:</td>
				<td width="35%"><input type="text" name="title" class="inputbox" size="40" value="<?php echo htmlspecialchars( $row->title ); ?>" /></td>
				<td width="45%">Title as will appear on tab.</td>
			</tr>
			<tr>
				<td colspan="3">Description: This description appears only on user edit, not on profile (For profile text, use delimiter fields):</td>
			</tr>
			<tr>
				<td colspan="3" align="left"><?php echo $_CB_framework->displayCmsEditor( 'description', $row->description, 600, 200, 50, 10 );
				// <textarea name="description" class="inputbox" cols="40" rows="10">< ?php echo htmlspecialchars( $row->description ); ? ></textarea>
				?></td>
			</tr>
			<tr>
				<td>Publish:</td>
				<td><?php echo $lists['enabled']; ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>Profile ordering:</td>
				<td><?php echo $lists['ordering']; ?></td>
				<td>Tabs and fields on profile are ordered as follows:<ol>
				    <li>position of tab on user profile (top-down, left-right)</li>
				    <li>This ordering of tab on position of user profile</li>
				    <li>ordering of field within tab position of user profile.</li></ol>
				</td>
			</tr>
			<tr>
				<td>Registration ordering<br />(default value: 10):</td>
				<td><input type="text" name="ordering_register" class="inputbox" size="40" value="<?php echo $row->ordering_register; ?>" /></td>
				<td>Tabs and fields on registration are ordered as follows:<ol>
					<li>This registration ordering of tab</li>
				    <li>position of tab on user profile (top-down, left-right)</li>
				    <li>ordering of tab on position of user profile</li>
				    <li>ordering of field within tab position of user profile.</li></ol>
				</td>
			</tr>
			<tr>
				<td>Position:</td>
				<td><?php echo $lists['position']; ?></td>
				<td>Position on profile and ordering on registration.</td>
			</tr>
			<tr>
				<td>Display type:</td>
				<td><?php echo $lists['displaytype']; ?></td>
				<td>In which way the content of this tab will be displayed on the profile.</td>
			</tr>
			<tr>
				<td>User Group to allow access to:</td>
				<td><?php echo $lists['useraccessgroup']; ?></td>
				<td>All groups above that level will also have access to the list.</td>
			</tr>
			</table>
		</td>
		<td width="40%">
			<table class="adminform">
			<tr>
				<th colspan="2">
				Parameters
				</th>
			</tr>
			<tr>
				<td>
				<?php
				if ( $row->tabid && $row->pluginid > 0 ) {
					$plugin= new moscomprofilerPlugin($_CB_database);
					$plugin->load( (int) $row->pluginid);

					// fail if checked out not by 'me'
					if ($plugin->checked_out && $plugin->checked_out <> $_CB_framework->myId() ) {
						echo "<script type=\"text/javascript\">alert('The plugin $plugin->name is currently being edited by another administrator'); document.location.href='index2.php?option=$option'</script>\n";
						exit(0);
					}
				
					// get params values
					if ( $plugin->type !== "language" && $plugin->id ) {
						$_PLUGINS->loadPluginGroup( $plugin->type, array( (int) $plugin->id ), 0 );
					}

					$element	=	$_PLUGINS->loadPluginXML( 'editTab', $row->pluginclass, $plugin->id );
/*
					$xmlfile = $_CB_framework->getCfg('absolute_path') . '/components/com_comprofiler/plugin/' .$plugin->type . '/'.$plugin->folder . '/' . $plugin->element .'.xml';
					// $params =& new cbParameters( $row->params, $xmlfile );
					cbimport('cb.xml.simplexml');
					$xmlDoc =& new CBSimpleXML();
					if ( $xmlDoc->loadFile( $xmlfile ) ) {
						$element =& $xmlDoc->document;
					} else {
						$element = null;
					}
*/
					$pluginParams = new cbParamsBase( $plugin->params );
					
					$params =& new cbParamsEditorController( $row->params, $element, $element, $plugin, $row->tabid );
					$params->setPluginParams( $pluginParams );
					$options = array( 'option' => $option, 'task' => $task, 'pluginid' => $row->pluginid, 'tabid' => $row->tabid );
					$params->setOptions( $options );

					echo $params->draw( 'params', 'tabs', 'tab', 'class', $row->pluginclass );
				} else {
					echo '<i>No Parameters</i>';
				}

		if ( $paramsEditorHtml ) {
			foreach ( $paramsEditorHtml as $paramsEditorHtmlBlock ) {
?>
					<table class="adminform" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<th colspan="2">
								<?php echo $paramsEditorHtmlBlock['title']; ?>
							</th>
						</tr>
						<tr>
							<td>
								<?php echo $paramsEditorHtmlBlock['content']; ?>
							</td>
						</tr>
					</table>
<?php
			}
		}
?>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
  <input type="hidden" name="tabid" value="<?php echo $row->tabid; ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="task" value="" />
  <?php
	echo cbGetSpoofInputTag( 'tab' );
  ?>
</form>
<?php }

	function showUsers( &$rows, &$pageNav, $search, $option, &$lists, &$pluginColumns ) {
		HTML_comprofiler::secureAboveForm('showUsers');

		outputCbTemplate( 2 );
		outputCbJs( 2 );

		global $_CB_Backend_Title;
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-user', "CB User Manager" ) );

		$colspans			=	13 + count( $pluginColumns );
?>
<form action="index2.php" method="post" name="adminForm">
  <table cellpadding="4" cellspacing="0" border="0" width="100%">
    <tr>
      <td style="width:80%;">Search: <input type="text" name="search" value="<?php echo $search;?>" class="inputbox" onChange="document.adminForm.submit();" />
      </td>
<?php
		foreach ( $lists as $li ) {
?>
	  <td width="right">
		<?php echo $li;?>
	  </td>

<?php
		}
?>

    </tr>
  </table>
  <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
   <thead>
    <tr>
      <th align="center" colspan="<?php echo $colspans; ?>"> <?php echo $pageNav->writePagesLinks(); ?></th>
    </tr>
    <tr>
      <th width="1%" class="title">#</th>
      <th width="3%" class="title"> <input type="checkbox" name="toggle" value="" <?php echo 'onClick="checkAll(' . count($rows) . ');"'; ?> />
      </th>
      <th width="15%" class="title">Name</th>
      <th width="10%" class="title">UserName</th>
      <th width="5%" class="title" nowrap="nowrap">Logged In</th>
<?php
		foreach ( $pluginColumns as $name => $content ) {
?>
	  <th width="15%" class="title"><?php echo $name; ?></th>

<?php
		}
?>
      <th width="15%" class="title">Group</th>
      <th width="15%" class="title">E-Mail</th>
      <th width="10%" class="title">Registered</th>
      <th width="10%" class="title" nowrap="nowrap">Last Visit</th>
      <th width="5%" class="title">Enabled</th>
      <th width="5%" class="title">Confirmed</th>
      <th width="5%" class="title">Approved</th>
      <th width="1%" class="title">ID</th>
    </tr>
   </thead>
   <tbody>
<?php
		$k = 0;
		$imgpath='../components/com_comprofiler/images/';
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row =& $rows[$i];
			$img = $row->block ? 'publish_x.png' : 'tick.png';
			$task = $row->block ? 'unblock' : 'block';
			$hover1 = $row->block ? 'Blocked' : 'Enabled';
			
			switch ($row->approved) {
				case 0:
	        		$img2 = 'pending.png';
	        		$task2 = 'approve';
					$hover = 'Pending Approval';
				break;
				case 1:
	        		$img2 = 'tick.png';
	        		$task2 = 'reject';
					$hover = 'Approved';
				break;				
				case 2:
	        		$img2 = 'publish_x.png';
	        		$task2 = 'approve';
					$hover = 'Rejected';
				break;				

			}

		        $img3 = $row->confirmed ?  'tick.png' : 'publish_x.png';
		        // $task3 = $row->confirmed ?   'reject' : 'approve';
		        $hover3 = $row->confirmed ?   'confirmed' : 'unconfirmed';

?>
    <tr class="<?php echo "row$k"; ?>">
      <td><?php echo $i+1+$pageNav->limitstart;?></td>
      <td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onClick="isChecked(this.checked);" /></td>
      <td> <a href="#edit" onClick="return listItemTask('cb<?php echo $i;?>','edit')">
        <?php echo $row->name; ?> </a> </td>
      <td><?php echo $row->username; ?></td>
      <td align="center"><?php echo $row->loggedin ? '<img src="' . $imgpath . 'tick.png" width="16" height="16" border="0" alt="" />': ''; ?></td>
<?php
		foreach ( $pluginColumns as $name => $content ) {
?>
	  <td><?php echo $content[$row->id]; ?></td>

<?php
		}
?>
      <td><?php echo $row->groupname; ?></td>
      <td><a href="mailto:<?php echo htmlspecialchars( $row->email ); ?>"><?php echo htmlspecialchars( $row->email ); ?></a></td>
      <td><?php echo cbFormatDate( $row->registerDate ); ?></td>
      <td><?php echo cbFormatDate( $row->lastvisitDate ); ?></td>
      <td width="10%"><a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')"><img src="<?php echo $imgpath.$img;?>" width="16" height="16" border="0" title="<?php echo $hover1; ?>" alt="<?php echo $hover1; ?>" /></a></td>
      <td width="10%"><img src="<?php echo $imgpath.$img3;?>" width="16" height="16" border="0" title="<?php echo $hover3; ?>" alt="<?php echo $hover3; ?>" /></td>
      <td width="10%"><a href="javascript: void(0);" onClick="return listItemTask('cb<?php echo $i;?>','<?php echo $task2;?>')"><img src="<?php echo $imgpath.$img2;?>" width="16" height="16" border="0" title="<?php echo $hover; ?>" alt="<?php echo $hover; ?>" /></a></td>
      <td><?php echo $row->id; ?></td>

    </tr>
    <?php $k = 1 - $k;
		}
		?>
   </tbody>
   <tfoot>
    <tr>
      <th align="center" colspan="<?php echo $colspans; ?>"> <?php echo $pageNav->getListFooter(); ?></th>
    </tr>
   </tfoot>
  </table>
  <input type="hidden" name="option" value="<?php echo $option;?>" />
  <input type="hidden" name="task" value="showusers" />
  <input type="hidden" name="boxchecked" value="0" />
  <?php
	echo cbGetSpoofInputTag( 'user' );
  ?>
</form>
<?php }

	function edituser( $user, $option, $newCBuser, &$postdata ) {
		global $_CB_framework, $_PLUGINS;

		$results = $_PLUGINS->trigger( 'onBeforeUserProfileEditDisplay', array( &$user, 2 ) );
		if ($_PLUGINS->is_errors()) {
			echo "<script type=\"text/javascript\">alert(\"" . str_replace( array("\n",'<br />'), array('\\n','\\n'), addslashes( $_PLUGINS->getErrorMSG() ) ) ."\"); window.history.go(-1); </script>\n";
			exit();
		}

		HTML_comprofiler::secureAboveForm('edituser');
		outputCbTemplate(2);
		initToolTip(2);
		$tabs			=	new cbTabs( ( ( ( $_CB_framework->getUi() == 2 ) && ( ! isset($_REQUEST['tab']) ) ) ? 1 : 0 ), 2 );		// use cookies in backend to remember selected tab.
		$tabcontent		=	$tabs->getEditTabs( $user, $postdata );

		outputCbJs( 2 );

		global $_CB_Backend_Title;
//OLD:	$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-users', "Community Builder User: <small>" . ( $user->id ? "Edit" . ' [ '. $user->username .' ]' : "New" ) . '</small>' ) );
//NEW:
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-users', CBTxt::T( "Community Builder User" ) . ": <small>" . ( $user->id ? CBTxt::T( "Edit" ) . ' [ '. $user->username .' ]' : CBTxt::T( "New" ) ) . '</small>' ) );

		ob_start();
?>
var cbDefaultFieldbackgroundColor;
function cbFrmSubmitButton() {
	var me = this.elements;
<?php
$version = checkJversion();
if ($version == 1) {
	// var r = new RegExp("^[a-zA-Z](([\.\-a-zA-Z0-9@])?[a-zA-Z0-9]*)*$", "i");
?>
	var r = new RegExp("^[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]*$", "i");
<?php
} elseif ( $version == -1 ) {
?>
	var r = new RegExp("[^A-Za-z0-9]", "i");
<?php
} else {
?>
	var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");
<?php
}
?>
	var errorMSG = '';
	var iserror=0;
	if (cbDefaultFieldbackgroundColor === undefined) cbDefaultFieldbackgroundColor = ((me['username'].style.getPropertyValue) ? me['username'].style.getPropertyValue("backgroundColor") : me['username'].style.backgroundColor);
<?php echo $tabs->fieldJS; ?>
	if (me['username'].value == "") {
		errorMSG += "<?php echo str_replace( array( "\n", "\r" ), ' ', unhtmlentities( _REGWARN_UNAME ) ); ?>\n";
		me['username'].style.backgroundColor = "red";
		iserror=1;
	} else if (r.exec(me['username'].value) || (me['username'].value.length < 3)) {
		errorMSG += "<?php echo str_replace( array( "\n", "\r" ), ' ', sprintf( unhtmlentities(_VALID_AZ09), unhtmlentities( _PROMPT_UNAME ), 2 ) );?>\n";
		me['username'].style.backgroundColor = "red";
		iserror=1;
	} else if (me['username'].style.backgroundColor.slice(0,3)=="red") {
		me['username'].style.backgroundColor = cbDefaultFieldbackgroundColor;
	}
	if ((me['password'].value) && (me['password'].value.length < 6)) {
		errorMSG += "<?php echo str_replace( array( "\n", "\r" ), ' ', sprintf( unhtmlentities(_VALID_AZ09), unhtmlentities(_REGISTER_PASS), 6 ) );?>\n";
		me['password'].style.backgroundColor = "red";
		iserror=1;
	} else if ((me['password'].value != "") && (me['password'].value != me['password__verify'].value)){
		errorMSG += "<?php echo unhtmlentities(_REGWARN_VPASS2);?>\n";
		me['password'].style.backgroundColor = "red"; me['password__verify'].style.backgroundColor = "red";
		iserror=1;
	} else {
		if (me['password'].style.backgroundColor.slice(0,3)=="red") me['password'].style.backgroundColor = cbDefaultFieldbackgroundColor;
		if (me['password__verify'].style.backgroundColor.slice(0,3)=="red") me['password__verify'].style.backgroundColor = cbDefaultFieldbackgroundColor;
	}
	if (me['gid'].value == "") {
		errorMSG += "You must assign user to a group.\n";
		iserror=1;
	}

	// loop through all input elements in form
	var fieldErrorMessages = new Array;
	for (var i=0; i < me.length; i++) {
		// check if element is mandatory; here mosReq=1
		if ( (typeof(me[i].getAttribute('mosReq')) != "undefined") && ( me[i].getAttribute('mosReq') == 1) ) {
			if (me[i].type == 'radio' || me[i].type == 'checkbox') {
				var rOptions = me[me[i].getAttribute('name')];
				var rChecked = 0;
				if(rOptions.length > 1) {
					for (var r=0; r < rOptions.length; r++) {
						if ( (typeof(rOptions[r].getAttribute('mosReq')) != "undefined") && ( rOptions[r].getAttribute('mosReq') == 1) ) {
							if (rOptions[r].checked) {
								rChecked=1;
							}
						}
					}
				} else {
					if (me[i].checked) {
						rChecked=1;
					}
				}
				if(rChecked==0) {
					for (var k=0; k < me.length; k++) {
						if (me[i].getAttribute('name') == me[k].getAttribute('name')) {
							if (me[k].checked) {
								rChecked=1;
								break;
							}
						}
					}
				}
				if(rChecked==0) {
					var alreadyFlagged = false;
					for (var j = 0, n = fieldErrorMessages.length; j < n; j++) {
						if (fieldErrorMessages[j] == me[i].getAttribute('name')) {
							alreadyFlagged = true;
							break
						}
					}
					if ( ! alreadyFlagged ) {
						fieldErrorMessages.push(me[i].getAttribute('name'));
						// add up all error messages
						errorMSG += me[i].getAttribute('mosLabel') + ' : <?php echo unhtmlentities(_UE_REQUIRED_ERROR); ?>\n';
						// notify user by changing background color, in this case to red
						me[i].style.backgroundColor = "red";
						iserror=1;
					}
				} else if (me[i].style.backgroundColor.slice(0,3)=="red") me[i].style.backgroundColor = cbDefaultFieldbackgroundColor;
			}
			if (me[i].value == '') {
				// add up all error messages
				errorMSG += me[i].getAttribute('mosLabel') + ' : <?php echo unhtmlentities(_UE_REQUIRED_ERROR); ?>\n';
				// notify user by changing background color, in this case to red
				me[i].style.backgroundColor = "red";
				iserror=1;
			} else if (me[i].style.backgroundColor.slice(0,3)=="red") me[i].style.backgroundColor = cbDefaultFieldbackgroundColor;
		}
	}
	if(iserror==1) {
		alert(errorMSG);
		return false;
	} else {
		return true;
	}
}
$('#cbcheckedadminForm').submit( cbFrmSubmitButton );
$('div.cbtoolbarbar a.cbtoolbar').click( function() {
		var taskVal = $(this).attr('href').substring(1);
		$('#cbcheckedadminForm input[name=task]').val( taskVal );
		if (taskVal == 'showusers') {
			$('#cbcheckedadminForm')[0].submit();
		} else {
			$('#cbcheckedadminForm').submit();
		}
		return false;
	} );
<?php
		$cbjavascript	=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->outputCbJQuery( $cbjavascript );

		if ( is_array( $results ) ) {
			echo implode( '', $results );
		}

		HTML_comprofiler::_overideWebFxLayout();
?>

<form action="index2.php" method="post" name="adminForm" id="cbcheckedadminForm" enctype="multipart/form-data" autocomplete="off">
<?php
echo "<table cellspacing='0' cellpadding='4' border='0' width='100%' id='userEditTable'><tr><td width='100%'>\n";
echo $tabcontent;
echo "</td></tr></table>";
?>
  <input type="hidden" name="id" value="<?php echo $user->id; ?>" />
  <input type="hidden" name="newCBuser" value="<?php echo $newCBuser; ?>" />
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="task" value="save" />
  <?php
	echo cbGetSpoofInputTag( 'user' );
  ?>
</form>
<div style="align:center;">
<?php
echo getFieldIcons(2,true,true,"","",true);
if( isset( $_REQUEST['tab'] ) ) {
	$_CB_framework->outputCbJQuery( "showCBTab( '" . addslashes( urldecode( stripslashes( cbGetParam( $_REQUEST, 'tab' ) ) ) ) . "' );" );
}
?>
</div>
<?php }
	/**
	 * over ride styles from webfxlayout
	 *
	 */
	function _overideWebFxLayout() {
		global $_CB_framework;

		ob_start();
?>
.dynamic-tab-pane-control h2 {
	text-align:	center;
	width:		auto;
}

.dynamic-tab-pane-control h2 a {
	display:	inline;
	width:		auto;
}

.dynamic-tab-pane-control a:hover {
	background: transparent;
}
<?php
		$css	=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->document->addHeadStyleInline( $css );
	}
   function showConfig( &$ueConfig, &$lists, $option ) {
	global $_CB_framework;

	HTML_comprofiler::secureAboveForm('showConfig');
	outputCbTemplate(2);
	outputCbJs(2);

	global $_CB_Backend_Title;
	$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-settings', "CB " . _UE_REG_CONFIGURATION_MANAGER ) );
	HTML_comprofiler::_overideWebFxLayout();
	
?>
<div style="width:95%;text-align:center;margin-bottom:15px;">
	<div style="width:88%;margin:auto;text-align:left;">
<?php update_checker(); ?>
	</div>
</div>
   <form action="index2.php" method="post" name="adminForm">
   <table cellspacing='0' cellpadding='4' border='0' width='100%'><tr><td width='100%'>
<?php
$tabs = new cbTabs( 0,2 );
?>
<?php

echo $tabs->startPane( "CB" );
echo $tabs->startTab("CB",_UE_GENERAL,"tab1");
?>

   <table cellpadding="4" cellspacing="0" border="0" width="95%" class="adminform">
      <tr align="center" valign="middle">
         <th width="20%">&nbsp;</th>
         <th width="20%"><?php echo _UE_CURRENT_SETTINGS ?></th>
         <th width="60%"><?php echo _UE_EXPLANATION ?></th>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_NAME_STYLE ?></td>
         <td align="left" valign="top"><?php echo $lists['name_style']; ?></td>
         <td align="left" valign="top"><?php echo _UE_NAME_STYLE_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_NAME_FORMAT ?></td>
         <td align="left" valign="top"><?php echo $lists['name_format']; ?></td>
         <td align="left" valign="top"><?php echo _UE_NAME_FORMAT_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_DATE_FORMAT ?></td>
         <td align="left" valign="top"><?php echo $lists['date_format']; ?></td>
         <td align="left" valign="top"><?php echo _UE_DATE_FORMAT_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_CALENDAR_TYPE ?></td>
         <td align="left" valign="top"><?php echo $lists['calendar_type']; ?></td>
         <td align="left" valign="top"><?php echo _UE_CALENDAR_TYPE_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOW_EMAIL_DISPLAY ?></td>
         <td align="left" valign="top"><?php echo $lists['allow_email_display']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOW_EMAIL_DISPLAY_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOW_EMAIL_REPLYTO ?></td>
         <td align="left" valign="top"><?php echo $lists['allow_email_replyto']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOW_EMAIL_REPLYTO_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOW_EMAIL ?></td>
         <td align="left" valign="top"><?php echo $lists['allow_email']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOW_EMAIL_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOW_WEBSITE ?></td>
         <td align="left" valign="top"><?php echo $lists['allow_website']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOW_WEBSITE_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOW_ONLINESTATUS ?></td>
         <td align="left" valign="top"><?php echo $lists['allow_onlinestatus']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOW_ONLINESTATUS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ICONS_DISPLAY ?></td>
         <td align="left" valign="top"><?php echo $lists['icons_display']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ICONS_DISPLAY_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_LOGIN_TYPE ?></td>
         <td align="left" valign="top"><?php echo $lists['login_type']; ?></td>
         <td align="left" valign="top"><?php echo _UE_LOGIN_TYPE_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <th colspan="3">&nbsp;</th>
      </tr>
   </table>
<?php
echo $tabs->endTab();
echo $tabs->startTab("CB",_UE_REGISTRATION,"tab2");
?>
   <table cellpadding="4" cellspacing="0" border="0" width="95%" class="adminform">
      <tr align="center" valign="middle">
         <th width="20%">&nbsp;</th>
         <th width="20%"><?php echo _UE_CURRENT_SETTINGS ?></th>
         <th width="60%"><?php echo _UE_EXPLANATION ?></th>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_CB_ALLOW ?></td>
         <td align="left" valign="top"><?php echo $lists['admin_allowcbregistration']; ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_CB_ALLOW_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_CB_EMAILPASS ?></td>
         <td align="left" valign="top"><?php echo $lists['emailpass']; ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_CB_EMAILPASS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_ADMIN_APPROVAL ?></td>
         <td align="left" valign="top"><?php echo $lists['admin_approval']; ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_ADMIN_APPROVAL_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_CONFIRMATION ?></td>
         <td align="left" valign="top"><?php echo $lists['confirmation']; ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_CONFIRMATION_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_USERNAMECHECKER ?></td>
         <td align="left" valign="top"><?php echo $lists['reg_username_checker']; ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_USERNAMECHECKER_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_EMAILCHECKER ?></td>
         <td align="left" valign="top"><?php echo $lists['reg_email_checker']; ?></td>
         <td align="left" valign="top"><?php if ( ! function_exists( 'getmxrr' ) ) { echo _UE_REG_EMAILCHECKER_WARNING . ' --- '; } echo _UE_REG_EMAILCHECKER_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_UNIQUEEMAIL ?></td>
         <td align="left" valign="top"><?php echo $_CB_framework->getCfg( 'uniquemail') ? _UE_YES : _UE_NO; ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_UNIQUEEMAIL_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_SHOW_LOGIN_ON_PAGE ?></td>
         <td align="left" valign="top"><?php echo $lists['reg_show_login_on_page']; ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_SHOW_LOGIN_ON_PAGE_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_EMAIL_NAME ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_reg_email_name" value="<?php echo htmlspecialchars(stripslashes($ueConfig['reg_email_name'])); ?>" /></td>
         <td align="left" valign="top"><?php echo _UE_REG_EMAIL_NAME_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_EMAIL_FROM ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_reg_email_from" value="<?php echo htmlspecialchars($ueConfig['reg_email_from']); ?>" /></td>
         <td align="left" valign="top"><?php echo _UE_REG_EMAIL_FROM_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_EMAIL_REPLYTO ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_reg_email_replyto" value="<?php echo htmlspecialchars($ueConfig['reg_email_replyto']); ?>" /></td>
         <td align="left" valign="top"><?php echo _UE_REG_EMAIL_REPLYTO_DESC ?></td>
      </tr>
      <tr  align="left" valign="middle">
	 <td align="left" valign="top"></td>
	 <td align="left" valign="top" colspan="2"><?php echo _UE_REG_EMAIL_TAGS; ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_PEND_APPR_SUB ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_reg_pend_appr_sub" size="50" value="<?php echo htmlspecialchars(stripslashes($ueConfig['reg_pend_appr_sub'])); ?>" /></td>
         <td align="left" valign="top"><?php echo _UE_REG_PEND_APPR_SUB_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_PEND_APPR_MSG ?></td>
         <td align="left" valign="top" colspan=2><textarea name="cfg_reg_pend_appr_msg" cols=50 rows=6><?php echo htmlspecialchars(stripslashes($ueConfig['reg_pend_appr_msg'])); ?></textarea></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_WELCOME_SUB ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_reg_welcome_sub" size="50" value="<?php echo htmlspecialchars(stripslashes($ueConfig['reg_welcome_sub'])); ?>" /></td>
         <td align="left" valign="top"><?php echo _UE_REG_WELCOME_SUB_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_WELCOME_MSG ?></td>
         <td align="left" valign="top" colspan=2><textarea name="cfg_reg_welcome_msg" cols=50 rows=6><?php echo htmlspecialchars(stripslashes($ueConfig['reg_welcome_msg'])); ?></textarea></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_SHOW_ICONS_EXPLAIN ?></td>
         <td align="left" valign="top"><?php echo $lists['reg_show_icons_explain']; ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_SHOW_ICONS_EXPLAIN_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_INTRO_MSG ?></td>
         <td align="left" valign="top"><textarea name="cfg_reg_intro_msg" cols=50 rows=6><?php echo htmlspecialchars(stripslashes($ueConfig['reg_intro_msg'])); ?></textarea></td>
         <td align="left" valign="top"><?php echo _UE_REG_INTRO_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_CONCLUSION_MSG ?></td>
         <td align="left" valign="top"><textarea name="cfg_reg_conclusion_msg" cols=50 rows=6><?php echo htmlspecialchars(stripslashes($ueConfig['reg_conclusion_msg'])); ?></textarea></td>
         <td align="left" valign="top"><?php echo _UE_REG_CONCLUSION_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_TOC_MSG ?></td>
         <td align="left" valign="top"><?php echo $lists['reg_enable_toc']; ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_TOC_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_TOC_URL_MSG ?></td>
         <td align="left" valign="top"><input type="text" size="50" name="cfg_reg_toc_url" value="<?php echo htmlspecialchars($ueConfig['reg_toc_url']); ?>" /></td>
         <td align="left" valign="top"><?php echo _UE_REG_TOC_URL_DESC ?></td>
      </tr>
     <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_FIRST_VISIT_URL_MSG ?></td>
         <td align="left" valign="top"><input type="text" size="50" name="cfg_reg_first_visit_url" value="<?php echo htmlspecialchars($ueConfig['reg_first_visit_url']); ?>" /></td>
         <td align="left" valign="top"><?php echo _UE_REG_FIRST_VISIT_URL_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <th colspan="3">&nbsp;</th>
      </tr>
   </table>
<?php
echo $tabs->endTab();
echo $tabs->startTab("CB",_UE_USERLIST,"tab3");
?>
   <table cellpadding="4" cellspacing="0" border="0" width="95%" class="adminform">
      <tr align="center" valign="middle">
         <th width="20%">&nbsp;</th>
         <th width="20%"><?php echo _UE_CURRENT_SETTINGS ?></th>
         <th width="60%"><?php echo _UE_EXPLANATION ?></th>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_NUM_PER_PAGE ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_num_per_page" value="<?php echo htmlspecialchars($ueConfig['num_per_page']); ?>" /></td>
         <td align="left" valign="top"><?php echo _UE_NUM_PER_PAGE_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOW_PROFILELINK ?></td>
         <td align="left" valign="top"><?php echo $lists['allow_profilelink']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOW_PROFILELINK_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <th colspan="3">&nbsp;</th>
      </tr>
   </table>
<?php
echo $tabs->endTab();
echo $tabs->startTab("CB",_UE_USERPROFILE,"tab4");
?>
   <table cellpadding="4" cellspacing="0" border="0" width="95%" class="adminform">
      <tr align="center" valign="middle">
         <th width="20%">&nbsp;</th>
         <th width="20%"><?php echo _UE_CURRENT_SETTINGS ?></th>
         <th width="60%"><?php echo _UE_EXPLANATION ?></th>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_USERNAME ?></td>
         <td align="left" valign="top"><?php echo $lists['usernameedit']; ?></td>
         <td align="left" valign="top"><?php echo _UE_USERNAME_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ADMINREQUIREDFIELDS ?></td>
         <td align="left" valign="top"><?php echo $lists['adminrequiredfields']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ADMINREQUIREDFIELDS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOW_PROFILEVIEWBY ?></td>
         <td align="left" valign="top"><?php echo $lists['allow_profileviewbyGID']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOW_PROFILEVIEWBY_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_MINHITSINTV ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_minHitsInterval" value="<?php echo htmlspecialchars($ueConfig['minHitsInterval']);?>" /></td>
         <td align="left" valign="top"><?php echo _UE_MINHITSINTV_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_TEMPLATEDIR ?></td>
         <td align="left" valign="top"><?php echo $lists['templatedir']; ?></td>
         <td align="left" valign="top"><?php echo _UE_TEMPLATEDIR_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_PROFILE_2COLS ?></td>
         <td align="left" valign="top"><?php echo _UE_LEFT ?>: <input type="text" size="2" name="cfg_left2colsWidth" value="<?php echo htmlspecialchars($ueConfig['left2colsWidth']);?>" /> %&nbsp;&nbsp;&nbsp;&nbsp;<?php echo _UE_REG_PROFILE_2COLS_RIGHT_REST ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_PROFILE_2COLS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_PROFILE_3COLS ?></td>
         <td align="left" valign="top"><?php echo _UE_LEFT ?>: <input type="text" size="2" name="cfg_left3colsWidth" value="<?php echo htmlspecialchars($ueConfig['left3colsWidth']);?>" /> %&nbsp;&nbsp;&nbsp;&nbsp;
         							  <?php echo _UE_RIGHT ?>: <input type="text" size="2" name="cfg_right3colsWidth" value="<?php echo htmlspecialchars($ueConfig['right3colsWidth']);?>" /> %</td>
         <td align="left" valign="top"><?php echo _UE_REG_PROFILE_3COLS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_SHOWEMPTYTABS ?></td>
         <td align="left" valign="top"><?php echo $lists['showEmptyTabs']; ?></td>
         <td align="left" valign="top"><?php echo _UE_SHOWEMPTYTABS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_SHOWEMPTYFIELDS ?></td>
         <td align="left" valign="top"><?php echo $lists['showEmptyFields']; ?></td>
         <td align="left" valign="top"><?php echo _UE_SHOWEMPTYFIELDS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_EMPTYFIELDSTEXT ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_emptyFieldsText" value="<?php echo htmlspecialchars( isset( $ueConfig['emptyFieldsText'] ) ? $ueConfig['emptyFieldsText'] : '-' );?>" /></td>
         <td align="left" valign="top"><?php echo  _UE_EMPTYFIELDSTEXT_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_NESTTABS ?></td>
         <td align="left" valign="top"><?php echo $lists['nesttabs']; ?></td>
         <td align="left" valign="top"><?php echo _UE_NESTTABS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_XHTMLCOMPLY ?></td>
         <td align="left" valign="top"><?php echo $lists['xhtmlComply']; ?></td>
         <td align="left" valign="top"><?php echo _UE_XHTMLCOMPLY_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_FRONTENDUSERPARAMS ?></td>
         <td align="left" valign="top"><?php echo $lists['frontend_userparams']; ?></td>
         <td align="left" valign="top"><?php echo _UE_FRONTENDUSERPARAMS_DESC ?></td>
      </tr>
<?php
if ( isset( $ueConfig['frontend_userparams'] ) ) {
	if ( ( $ueConfig['frontend_userparams'] == 1 ) !== in_array( $_CB_framework->getCfg( "frontend_userparams" ), array( '1', null) ) ) {
//TBD in CB 1.3: move those in language strings:
?>
      <tr align="center" valign="middle">
         <td align="left" valign="top"> </td>
         <td align="left" valign="top"><div class="cbSmallWarning">WARNING: different from the CMS setting !</div></td>
         <td align="left" valign="top">This may be ok, but this warning is just to make you aware of the difference.</td>
      </tr>
<?php
	}
}
?>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_FILTER_ALLOWED_TAGS ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_html_filter_allowed_tags" value="<?php echo htmlspecialchars($ueConfig['html_filter_allowed_tags']);?>" /></td>
         <td align="left" valign="top"><?php echo  _UE_REG_FILTER_ALLOWED_TAGS_DESC . '<br />' . $lists['_filteredbydefault']; ?></td>
      </tr>
      <tr align="center" valign="middle">
         <th colspan="3">&nbsp;</th>
      </tr>
   </table>
<?php
echo $tabs->endTab();


$imgToolBox							=	new imgToolBox();
if ( $ueConfig['im_path'] ) {
	$imgToolBox->_IM_path			=	$ueConfig['im_path'];
}
if ( $ueConfig['netpbm_path'] ) {
	$imgToolBox->_NETPBM_path		=	$ueConfig['netpbm_path'];
}
$imageLibs							=	$imgToolBox->getImageLibs();

echo $tabs->startTab("CB",_UE_AVATARS,"tab5");
?>
   <table cellpadding="4" cellspacing="0" border="0" width="95%" class="adminform">
      <tr align="center" valign="middle">
         <th width="20%">&nbsp;</th>
         <th width="20%"><?php echo _UE_CURRENT_SETTINGS ?></th>
         <th width="60%"><?php echo _UE_EXPLANATION ?></th>
      </tr>
	 <tr align="center" valign="middle">
		<td align="left" valign="top"><?php echo _UE_IMPATH;?></td>
		<td align="left" valign="top">
			<input type="text" name="cfg_im_path" value="<?php echo ($ueConfig['im_path'] == '') ? 'auto' : htmlspecialchars($ueConfig['im_path']);?>" size="40" >
		</td>
		<td align="left" valign="top">
			<?php echo _UE_IMPATH_DESC;?>
		</td>
	</tr>
	 <tr align="center" valign="middle">
		<td align="left" valign="top"><?php echo _UE_NETPBMPATH;?></td>
		<td align="left" valign="top">
			<input type="text" name="cfg_netpbm_path" value="<?php echo ($ueConfig['netpbm_path'] == '') ? 'auto' : htmlspecialchars($ueConfig['netpbm_path']);?>" size="40" >
		</td>
		<td align="left" valign="top">
			<?php echo _UE_NETPBMPATH_DESC;?>
		</td>
	</tr>
      <tr align="center" valign="middle">
	<td align="left" valign="top">
		<?php echo _UE_CONVERSIONTYPE;?>
	</td>	
	<td align="left" valign="top">
	<?php echo $lists['conversiontype']; ?>
	</td>
	<td align="left" valign="top">
		<a href="http://www.imagemagick.org" target=_blank>ImageMagick</a>&nbsp;&nbsp;
			<?php if(array_key_exists('imagemagick',$imageLibs)) echo '<strong><font color="green">'._UE_AUTODET.' '.$imageLibs['imagemagick'].'</font></strong>'; else echo '<strong><font color="red">' . _UE_ERROR_NOTINSTALLED . '</font></strong>'; ?>
			<br />
		<a href="http://sourceforge.net/projects/netpbm" target=_blank>NetPBM</a>&nbsp;&nbsp;
			<?php if(array_key_exists('netpbm',$imageLibs)) echo '<strong><font color="green">'._UE_AUTODET.' '.$imageLibs['netpbm'].'</font></strong>'; else echo '<strong><font color="red">' . _UE_ERROR_NOTINSTALLED . '</font></strong>'; ?>
			<br />
		GD1 library 
			<?php if(array_key_exists('gd1',$imageLibs['gd'])) echo '&nbsp;&nbsp;<strong><font color="green">'._UE_AUTODET.', '.$imageLibs['gd']['gd1'].'</font></strong>'; else echo '<strong><font color="red">' . _UE_ERROR_NOTINSTALLED . '</font></strong>'; ?>
			<br />
		GD2 library 
			<?php if(array_key_exists('gd2',$imageLibs['gd'])) echo '&nbsp;&nbsp;<strong><font color="green">'._UE_AUTODET.', '.$imageLibs['gd']['gd2'].'</font></strong>'; else echo '<strong><font color="red">' . _UE_ERROR_NOTINSTALLED . '</font></strong>'; ?>

	</td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_AVATAR ?></td>
         <td align="left" valign="top"><?php echo $lists['allowAvatar']; ?></td>
         <td align="left" valign="top"><?php echo _UE_AVATAR_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_AVATARUPLOAD ?></td>
         <td align="left" valign="top"><?php echo $lists['allowAvatarUpload']; ?></td>
         <td align="left" valign="top"><?php echo _UE_AVATARUPLOAD_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALWAYSRESAMPLEUPLOADS ?></td>
         <td align="left" valign="top"><?php echo $lists['avatarResizeAlways']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALWAYSRESAMPLEUPLOADS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_AVATARGALLERY ?></td>
         <td align="left" valign="top"><?php echo $lists['allowAvatarGallery']; ?></td>
         <td align="left" valign="top"><?php echo _UE_AVATARGALLERY_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_AVHEIGHT ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_avatarHeight" value="<?php echo htmlspecialchars($ueConfig['avatarHeight']);?>" /></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_AVWIDTH ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_avatarWidth" value="<?php echo htmlspecialchars($ueConfig['avatarWidth']);?>" /></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_AVSIZE ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_avatarSize" value="<?php echo htmlspecialchars($ueConfig['avatarSize']);?>" /></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_TNHEIGHT ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_thumbHeight" value="<?php echo htmlspecialchars($ueConfig['thumbHeight']);?>" /></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_TNWIDTH ?></td>
         <td align="left" valign="top"><input type="text" name="cfg_thumbWidth" value="<?php echo htmlspecialchars($ueConfig['thumbWidth']);?>" /></td>
      </tr>
      <tr align="center" valign="middle">
         <th colspan="3">&nbsp;</th>
      </tr>
   </table>
<?php
echo $tabs->endTab();
echo $tabs->startTab("CB",_UE_MODERATE,"tab6");
?>
   <table cellpadding="4" cellspacing="0" border="0" width="95%" class="adminform">
      <tr align="center" valign="middle">
         <th width="20%">&nbsp;</th>
         <th width="20%"><?php echo _UE_CURRENT_SETTINGS ?></th>
         <th width="60%"><?php echo _UE_EXPLANATION ?></th>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_AVATARUPLOADAPPROVALGROUP ?></td>
         <td align="left" valign="top"><?php echo $lists['imageApproverGid']; ?></td>
         <td align="left" valign="top"><?php echo _UE_AVATARUPLOADAPPROVALGROUP_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_MODERATORUSERAPPOVAL ?></td>
         <td align="left" valign="top"><?php echo $lists['allowModUserApproval']; ?></td>
         <td align="left" valign="top"><?php echo _UE_MODERATORUSERAPPOVAL_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_MODERATOREMAIL ?></td>
         <td align="left" valign="top"><?php echo $lists['moderatorEmail']; ?></td>
         <td align="left" valign="top"><?php echo _UE_MODERATOREMAIL_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOWUSERREPORTS ?></td>
         <td align="left" valign="top"><?php echo $lists['allowUserReports']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOWUSERREPORTS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_AVATARUPLOADAPPROVAL ?></td>
         <td align="left" valign="top"><?php echo $lists['avatarUploadApproval']; ?></td>
         <td align="left" valign="top"><?php echo _UE_AVATARUPLOADAPPROVAL_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOWMODERATORSUSEREDIT ?></td>
         <td align="left" valign="top"><?php echo $lists['allowModeratorsUserEdit']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOWMODERATORSUSEREDIT_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOWUSERPROFILEBANNING ?></td>
         <td align="left" valign="top"><?php echo $lists['allowUserBanning']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOWUSERPROFILEBANNING_DESC ?></td>
      </tr>

      <tr align="center" valign="middle">
         <th colspan="3">&nbsp;</th>
      </tr>
   </table>
<?php
echo $tabs->endTab();
echo $tabs->startTab("CB",_UE_CONNECTION,"tab7");
?>
   <table cellpadding="4" cellspacing="0" border="0" width="95%" class="adminform">
      <tr align="center" valign="middle">
         <th width="20%">&nbsp;</th>
         <th width="20%"><?php echo _UE_CURRENT_SETTINGS ?></th>
         <th width="60%"><?php echo _UE_EXPLANATION ?></th>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_ALLOWCONNECTIONS ?></td>
         <td align="left" valign="top"><?php echo $lists['allowConnections']; ?></td>
         <td align="left" valign="top"><?php echo _UE_ALLOWCONNECTIONS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_CONNECTIONDISPLAY ?></td>
         <td align="left" valign="top"><?php echo $lists['connectionDisplay']; ?></td>
         <td align="left" valign="top"><?php echo _UE_CONNECTIONDISPLAY_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_CONNECTIONPATH ?></td>
         <td align="left" valign="top"><?php echo $lists['connectionPath']; ?></td>
         <td align="left" valign="top"><?php echo _UE_CONNECTIONPATH_DESC ?></td>
      </tr>      
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_USEMUTUALCONNECTIONACCEPTANCE ?></td>
         <td align="left" valign="top"><?php echo $lists['useMutualConnections']; ?></td>
         <td align="left" valign="top"><?php echo _UE_USEMUTUALCONNECTIONACCEPTANCE_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_CONNECTOINNOTIFYTYPE ?></td>
         <td align="left" valign="top"><?php echo $lists['conNotifyTypes']; ?></td>
         <td align="left" valign="top"><?php echo _UE_CONNECTOINNOTIFYTYPE_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_AUTOADDCONNECTIONS ?></td>
         <td align="left" valign="top"><?php echo $lists['autoAddConnections']; ?></td>
         <td align="left" valign="top"><?php echo _UE_AUTOADDCONNECTIONS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_CONNECTIONCATEGORIES ?></td>
         <td align="left" valign="top" ><textarea name="cfg_connection_categories" cols=25 rows=6><?php echo htmlspecialchars($ueConfig['connection_categories']); ?></textarea></td>
         <td align="left" valign="top"><?php echo _UE_CONNECTIONCATEGORIES_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <th colspan="3">&nbsp;</th>
      </tr>
   </table>
<?php
echo $tabs->endTab();
echo $tabs->startTab("CB",_UE_INTEGRATION,"tab8");
?>
   <table cellpadding="4" cellspacing="0" border="0" width="95%" class="adminform">
      <tr align="center" valign="middle">
         <th width="20%">&nbsp;</th>
         <th width="20%"><?php echo _UE_CURRENT_SETTINGS ?></th>
         <th width="60%"><?php echo _UE_EXPLANATION ?></th>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_NOVERSIONCHECK ?></td>
         <td align="left" valign="top"><?php echo $lists['noVersionCheck']; ?></td>
         <td align="left" valign="top"><?php echo _UE_NOVERSIONCHECK_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <td align="left" valign="top"><?php echo _UE_REG_FURTHER_SETTINGS ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_FURTHER_SETTINGS_MORE ?></td>
         <td align="left" valign="top"><?php echo _UE_REG_FURTHER_SETTINGS_DESC ?></td>
      </tr>
      <tr align="center" valign="middle">
         <th colspan="3">&nbsp;</th>
      </tr>
   </table>

<?php
echo $tabs->endTab();
echo $tabs->endPane();

?>
</td></tr></table>
   <input type="hidden" name="task" value="" />
   <input type="hidden" name="option" value="<?php echo $option; ?>" />
   <input type="hidden" name="cfg_version" value="<?php echo $ueConfig['version']; ?>" />
   <?php
	echo cbGetSpoofInputTag( 'config' );
	?>
</form>
<?php
		// flush();
		// ob_flush();
?>
<div style="align:center;">
   <p><?php echo _UE_BY ?>
      <a href="http://www.joomlapolis.com" target="_blank">Community Builder Team of Joomlapolis</a>
      <br />
      <font class="small"><?php echo _UE_VERSION ?>: <?php echo $ueConfig['version']; ?></font>
      <?php
		// update_checker();
?>
   </p>
</div>
<?php
	}


	function showTools() {
		HTML_comprofiler::secureAboveForm('showTools');

		outputCbTemplate( 2 );
		outputCbJs( 2 );

		global $_CB_Backend_Title;
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-tools', "CB Tools Manager" ) );

		$cbSpoofField			=	cbSpoofField();
		$cbSpoofString			=	cbSpoofString( null, 'cbtools' );

?>
<div style="text-align:left;margin-top:30px;margin-bottom:20px;">
	<table width="90%" border="0" cellpadding="10" cellspacing="2" class="adminForm">
		<tr>
			<td width="25%">
				<a href="index2.php?option=com_comprofiler&amp;task=loadSampleData&amp;<?php echo $cbSpoofField . '=' . $cbSpoofString; ?>">Load Sample Data</a>
			</td>
			<td width="75%">
				This will load sample data into the Joomla/Mambo Community Builder component. Precisely, an additional information tab (that you can change, unpublish or delete in CB Tabs manager) will be created containing fields for: location, occupation, interests, company, address, city, state, zipcode, country, phone and fax (you can then change, unpublish or delete those fields which you don't need' in CB Fields Manager). Also a users-list will be created, that you can edit from the CB Lists manager. This will help you get started quicker with CB.
			</td>
		</tr>
		<tr>
			<td>
				<a href="index2.php?option=com_comprofiler&amp;task=syncUsers&amp;<?php echo $cbSpoofField . '=' . $cbSpoofString; ?>">Synchronize Users</a>
			</td>
			<td>
				This will synchronize the Joomla/Mambo User table with the Joomla/Mambo Community Builder User Table.<br />
				Please make sure before synchronizing that the user name type (first/lastname mode choice) is set correctly in
				Components -&gt; Community Builder -&gt; Configuration -&gt; General, so that the user-synchronization imports
				the names in the appropriate format.
			</td>
		</tr>
		<tr>
			<td>
				<a href="index2.php?option=com_comprofiler&amp;task=checkcbdb&amp;databaseid=0&amp;<?php echo $cbSpoofField . '=' . $cbSpoofString; ?>">Check Community Builder Database</a>
			</td>
			<td>
				This will perform a series of tests on the Community Builder database and report back potential inconsistencies without changing or correcting the database.
			</td>
		</tr>
		<tr>
			<td>
				<a href="index2.php?option=com_comprofiler&amp;task=checkcbdb&amp;databaseid=3&amp;<?php echo $cbSpoofField . '=' . $cbSpoofString; ?>">Check Community Builder User Fields Database</a>
			</td>
			<td>
				This will perform a series of tests on the Community Builder User fields database and report back potential inconsistencies without changing or correcting the database.
			</td>
		</tr>
		<tr>
			<td>
				<a href="index2.php?option=com_comprofiler&amp;task=checkcbdb&amp;databaseid=1&amp;<?php echo $cbSpoofField . '=' . $cbSpoofString; ?>">Check CB plugins database</a>
			</td>
			<td>
				This will check the database of installed CB plugins and report back potential inconsistencies without changing or correcting the database.
			</td>
		</tr>
		<tr>
			<td>
				<a href="index2.php?option=com_comprofiler&amp;task=checkcbdb&amp;databaseid=2&amp;<?php echo $cbSpoofField . '=' . $cbSpoofString; ?>">Check Users Database</a>
			</td>
			<td>
				This will perform a series of tests on the Users database of the CMS, the Community Builder users database and ACL and report back potential inconsistencies without changing or correcting the database.
			</td>
		</tr>
	</table>
</div>
 <?php
} //end function showTools

	/**
	 * Shows result of database check or fix (with or without dryrun)
	 *
	 * @param  CBdbChecker  $dbChecker
	 * @param  boolean      $upgrade
	 * @param  boolean      $dryRun
	 * @param  boolean      $result
	 * @param  array        $messagesBefore
	 * @param  array        $messagesAfter
	 * @param  string       $dbName
	 * @param  int          $dbId
	 * @param  boolean      $showConclusion
	 */
	function fixcbdbShowResults( &$dbChecker, $upgrade, $dryRun, $result, $messagesBefore, $messagesAfter, $dbName, $dbId, $showConclusion = true ) {
		global $_CB_framework;

		static $jsId = 0;
		++$jsId;

		$cbSpoofField			=	cbSpoofField();
		$cbSpoofString			=	cbSpoofString( null, 'cbtools' );

		foreach ( $messagesBefore as $msg ) {
			if ( $msg ) {
				echo '<p>' . $msg . '</p>';
			}
		}

		if ( $dbChecker !== null ) {
			if ( $result == true ) {
				echo '<div><font color="green">'
					. ( $upgrade ? ( $dryRun ? $dbName . ' Database adjustments dryrun is successful, see results below' : $dbName . ' Database adjustments have been performed successfully.' ) : 'All ' . $dbName . ' Database is up to date.' )
					. '</font></div>';
			} elseif ( is_string( $result ) ) {
				echo '<div><font color="red">' . $result . '</font></div>';
			} else {
				echo '<div style="color:red;">';
				echo '<h3><font color="red">'
					.	$dbName . ' ' . ( $upgrade ? 'Database adjustments errors:' : 'Database structure differences:' )
					.	'</font></h3>';
				$errors		=	$dbChecker->getErrors( false );
				foreach ( $errors as $err ) {
					echo '<div style="font-size:115%">' . $err[0];
					if ( $err[1] ) {
						echo '<div style="font-size:90%">' . $err[1] . '</div>';
					}
					echo '</div>';
				}
				echo "</div>";
				if ( ! $upgrade ) {
					echo '<p><font color="red">The ' . $dbName . ' database structure differences can be fixed (adjusted) by clicking here: <a href="'
						. $_CB_framework->getCfg( 'live_site' ) . '/administrator/index2.php?option=com_comprofiler&amp;task=fixcbdb&amp;dryrun=0&amp;databaseid=' . $dbId . '&amp;' . $cbSpoofField . '=' . $cbSpoofString
						. '"><span style="font-size:125%; padding: 4px; border: 1px red solid; background-color: #ffd">Click here to Fix (adjust) all ' . $dbName . ' database differences listed above</span></a> (you can also <a href="'
						. $_CB_framework->getCfg( 'live_site' ) . '/administrator/index2.php?option=com_comprofiler&amp;task=fixcbdb&amp;dryrun=1&amp;databaseid=' . $dbId . '&amp;' . $cbSpoofField . '=' . $cbSpoofString
						. '"><span style="padding: 4px; border: 1px green solid; background-color: #ffd">Click here to preview fixing (adjusting) queries in a dry-run</span></a>), but <strong><u><span style="font-size:125%;color:red;">in all cases you need to backup database first</span></u></strong> as this adjustment is changing database and is a new feature.</font>'
						. '</p>';
				}
			}
			$logs			=	$dbChecker->getLogs( false );
			if ( count( $logs ) > 0 ) {
				echo "<div style='margin-bottom:15px;'><a href='#' id='cbdetailsLinkShow_" . $jsId . "'>Click here to Show details</a></div>";
				echo "<div id='cbdetailsdbcheck_" . $jsId . "' style='color:green;margin-bottom:15px;'>";
				foreach ( $logs as $err ) {
					echo '<div style="font-size:100%">' . $err[0];
					if ( $err[1] ) {
						echo '<div style="font-size:90%">' . $err[1] . '</div>';
					}
					echo '</div>';
				}
				echo '</div>';
				$_CB_framework->outputCbJQuery( "$('#cbdetailsdbcheck_" . $jsId . "').hide();      $('#cbdetailsLinkShow_" . $jsId . "').click( function() { $('#cbdetailsdbcheck_" . $jsId . "').toggle('slow'); $('#cbdetailsLinkShow_" . $jsId . "').html( $('#cbdetailsLinkShow_" . $jsId . "').html() == 'Click here to Show details' ? 'Click here to Hide details' : 'Click here to Show details' ); return false; } );");
			}
		}
		if ( $showConclusion ) {
			if ( $upgrade ) {
				if ( $dryRun ) {
					echo "<p>Dry-run of " . $dbName . " database adjustments done. None of the queries listed in details have been performed.</p>";
					echo '<p>The database adjustments listed above can be applied by clicking here: <a href="'
						. $_CB_framework->getCfg( 'live_site' ) . '/administrator/index2.php?option=com_comprofiler&amp;task=fixcbdb&amp;dryrun=0&amp;databaseid=' . $dbId . '&amp;' . $cbSpoofField . '=' . $cbSpoofString
						. '"><span style="font-size:125%; padding: 4px; border: 1px red solid; background-color: #ffd">Click here to Fix (adjust) all database differences listed above</span></a>, but <strong><u><span style="font-size:125%;color:red;">you need to backup database first</span></u></strong> as this fixing/adjusting is changing database and is a new feature.</font></p>';
				} else {
					echo '<p>The ' . $dbName . ' database adjustments have been done. If all lines above are in green, database adjustments completed successfully. Otherwise, if some lines are red, please report exact errors and queries to authors forum, and try checking database again.</p>';
					echo '<p>The database structure can be checked again by clicking here: <a href="'
						. $_CB_framework->getCfg( 'live_site' ) . '/administrator/index2.php?option=com_comprofiler&amp;task=checkcbdb&amp;databaseid=' . $dbId . '&amp;' . $cbSpoofField . '=' . $cbSpoofString
						. '"><span style="font-size:125%; padding: 4px; border: 1px orange solid; background-color: #ffd">Click here to Check ' . $dbName . ' database</span></a>.</font></p>';
				}
			} else {
				echo '<p>' . $dbName . ' database checks done. If all lines above are in green, test completed successfully. Otherwise, please take corrective measures proposed in red.</p>';
			}
		}	
		foreach ( $messagesAfter as $msg ) {
			if ( $msg ) {
				echo '<p>' . $msg . '</p>';
			}
		}
	}

	/**
	* Writes a list of the defined modules
	* @param array An array of category objects
	*/
	function showPlugins( &$rows, &$pageNav, $option, &$lists, $search ) {
		global $_CB_framework, $_PLUGINS;

		HTML_comprofiler::secureAboveForm('showPlugins');

		outputCbTemplate( 2 );
		outputCbJs( 2 );
	    initToolTip( 2 );

		global $_CB_Backend_Title;
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-plugins', 'CB Plugin Manager <small><small> &nbsp;&nbsp;&nbsp;&nbsp; <a href="#install">Install Plugin</a></small></small>' ) );

		$p_startdir=$_CB_framework->getCfg('absolute_path')."/components/com_comprofiler/plugin/";

		HTML_comprofiler::_saveOrderJs( 'savepluginorder' );
		ob_start();
	?>
		function submitbutton3(pressbutton) {
			var form = document.adminForm_dir;
	
			// do field validation
			if (form.userfile.value == ""){
				alert( "Please select a directory" );
			} else {
				form.submit();
			}
		}
<?php 
		$js			=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->document->addHeadScriptDeclaration( $js );
?>		
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminheading" style="width:100%">
		<tr>
			<td style="width:80%">
			Filter: <input type="text" name="search" value="<?php echo $search;?>" class="text_area" onChange="document.adminForm.submit();" />
			</td>
			<td align="right">
			<?php echo $lists['type'];?>
			</td>
		</tr>
		</table>

		<table class="adminlist">
		<thead>
		  <tr>
			<th width="20">#</th>
			<th width="20">
			<input type="checkbox" name="toggle" value="" <?php echo 'onclick="checkAll(' . count( $rows ) . ');"';?> />
			</th>
			<th class="title">
			Plugin Name
			</th>
			<th nowrap="nowrap" width="5%">
	  		Installed
			</th>
			<th nowrap="nowrap" width="5%">
	  		Published
			</th>
			<th colspan="2" nowrap="nowrap" width="5%">
			Reorder
			</th>
			<th width="2%">
			Order
			</th>
			<th width="4%">
			<a href="javascript: cbsaveorder( <?php echo count( $rows )-1; ?> )"><img src="images/filesave.png" border="0" width="16" height="16" alt="Save Order" /></a>
			</th>
			<th nowrap="nowrap" align="left" width="10%">
			Access
			</th>
			<th nowrap="nowrap" align="left" width="10%">
			Type
			</th>
			<th nowrap="nowrap" align="left" width="10%">
			Directory
			</th>
		  </tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row 	= &$rows[$i];
			
			$xmlfile			=	$_PLUGINS->getPluginXmlPath( $row );
			$filesInstalled		=	file_exists($xmlfile);

			$link = 'index2.php?option=com_comprofiler&amp;task=editPlugin&amp;cid='. $row->id;

			//Access
			if ( !$row->access ) {
				$color_access = 'style="color: green;"';
				$task_access = 'accessregistered';
			} else if ( $row->access == 1 ) {
				$color_access = 'style="color: red;"';
				$task_access = 'accessspecial';
			} else {
				$color_access = 'style="color: black;"';
				$task_access = 'accesspublic';
			}
	
			$access = '	<a href="javascript: void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task_access .'\')" '. $color_access .'>
			'. $row->groupname .'
			</a>';
			
			//Checked Out
			if ( $filesInstalled && $row->checked_out ) {
				$hover = '';
				$date 				= cbFormatDate( $row->checked_out_time );
				$checked_out_text 	= '<table>';
				$checked_out_text 	.= '<tr><td>'. addslashes($row->editor) .'</td></tr>';
				$checked_out_text 	.= '<tr><td>'. $date .'</td></tr>';
				$checked_out_text 	.= '</table>';
				$hover = 'onMouseOver="return overlib(\''. htmlspecialchars( $checked_out_text ) .'\', CAPTION, \'Checked Out\', BELOW, RIGHT);" onMouseOut="return nd();"';
				$checked	 		= '<img src="images/checked_out.png" '. $hover .'/>';
			} else {
				$checked = '<input type="checkbox" id="cb'.$i.'" name="cid[]" value="'.$row->id.'" onclick="isChecked(this.checked);" />';
			}

			$imgpath='../components/com_comprofiler/images/';
			//Installedg
			$instImg 	= $filesInstalled ? 'tick.png' : 'publish_x.png';
			$instAlt 	= $filesInstalled ? 'Installed' : 'Plugin Files missing';
			$installed  = '<img src="' . $imgpath . $instImg .'" border="0" alt="'. $instAlt .'"  title="'. $instAlt .'" />';

			//Published
			$img 	= $row->published ? 'publish_g.png' : 'publish_x.png';
			$task 	= $row->published ? 'unpublishPlugin' : 'publishPlugin';
			$alt 	= $row->published ? 'Published' : 'Unpublished';
			$action	= $row->published ? 'Unpublish Item' : 'Publish item';
			if ( ( $row->type == "language" ) && $row->published ) {
				$published = '<img src="' . $imgpath . 'publish_g.png" border="0" alt="Published" title="language plugins cannot be unpublished, only uninstalled" />';
			} elseif ( ( $row->id == 1 ) && $row->published ) {
				$published = '<img src="' . $imgpath . 'publish_g.png" border="0" alt="Published" title="CB core plugin cannot be unpublished" />';
			} else {
				$published = '<a href="javascript: void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
			<img src="'. $imgpath . $img .'" border="0" alt="'. $alt .'" />
			</a>';
			}
			
			//Backend plugin menu:
			$backendPluginMenus = array();
			if ( isset( $row->backend_menu ) && $row->backend_menu ) {
				$backend = explode( ",", $row->backend_menu );
				foreach ( $backend as $backendAction ) {
					$backendActionParts = explode( ":", $backendAction );
					$backendActionLink = 'index2.php?option=com_comprofiler&amp;task=pluginmenu&amp;pluginid=' . $row->id
										. '&amp;menu=' . $backendActionParts[1];
					$backendPluginMenus[] = '&nbsp; [<a href="' . $backendActionLink . '">' . $backendActionParts[0] . '</a>] ';
				}
			}

			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="right"><?php echo $i + 1 + $pageNav->limitstart ?></td>
				<td>
				<?php echo $checked; ?>
				</td>
				<td>
				<?php
				if ( ($row->checked_out && ( $row->checked_out != $_CB_framework->myId() )) || !$filesInstalled ) {
					if ( ! $filesInstalled ) {
						echo '<span title="' . $instAlt , '">';
					}
					echo $row->name;
					if ( ! $filesInstalled ) {
						echo "</span>";
					}
				} else {
					?>
					<a href="<?php echo $link; ?>">
					<?php echo htmlspecialchars( $row->name ); ?>
					</a>
					<?php
					echo implode( '', $backendPluginMenus );
				}
				?>
				</td>
				<td align="center">
				<?php echo $installed;?>
				</td>
				<td align="center">
				<?php echo $published;?>
				</td>
				<td>
				<?php    if (($i > 0 || ($i+$pageNav->limitstart > 0)) && $row->type == @$rows[$i-1]->type) { ?>
			         <a href="#reorder" onClick="return listItemTask('cb<?php echo $i;?>','orderupPlugin')">
			            <img src="images/uparrow.png" width="12" height="12" border="0" alt="Move Up" />
			         </a>
				<?php    } ?>
			      </td>
			      <td>
				<?php    if (($i < $n-1 || $i+$pageNav->limitstart < $pageNav->total-1) && $row->type == @$rows[$i+1]->type) { ?>
			         <a href="#reorder" onClick="return listItemTask('cb<?php echo $i;?>','orderdownPlugin')">
			            <img src="images/downarrow.png" width="12" height="12" border="0" alt="Move Down" />
			         </a>
				<?php    } ?>
				</td>
				<td align="center" colspan="2">
				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
				</td>
				<td align="left">
				<?php echo $access;?>
				</td>
				<td align="left" nowrap="nowrap">
				<?php echo $row->type;?>
				</td>
				<td align="left" nowrap="nowrap">
				<?php
			if ( ! $filesInstalled ) {
				echo '<span style="text-decoration:line-through" title="' . $instAlt , '">';
			}
			echo $row->element;
			if ( ! $filesInstalled ) {
				echo "</span>";
			}
				?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
	</tbody>
	<tfoot>
     <tr>
      <th align="center" colspan="12"> <?php echo $pageNav->getListFooter(); ?></th>
     </tr>
    </tfoot>
  </table>
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="showPlugins" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<?php
	echo cbGetSpoofInputTag( 'plugin' );
		?>
</form>
		
	<div style="clear:both;">	
		<form enctype="multipart/form-data" action="index2.php" method="post" name="filename">
		<table class="adminheading">
		<tr>
			<th class="install">
			<a name="install">Install New Plugin</a>
			</th>
		</tr>
		</table>
		
		<table class="adminform">
		<tr>
			<th>
			Upload Package File
			</th>
		</tr>
		<tr>
			<td align="left">
			Package File:
			<input class="text_area" name="userfile" type="file" size="70"/>
			<input class="button" type="submit" value="Upload File &amp; Install" />
			</td>
		</tr>
		</table>
		
		<input type="hidden" name="task" value="installPluginUpload"/>
		<input type="hidden" name="option" value="com_comprofiler"/>
		<input type="hidden" name="client" value=""/>
		<?php
	echo cbGetSpoofInputTag( 'plugin' );
		?>
		</form>
		<br />
		
		<form enctype="multipart/form-data" action="index2.php" method="post" name="adminForm_dir">
		<table class="adminform">
		<tr>
			<th>
			Install from directory
			</th>
		</tr>
		<tr>
			<td align="left">
			Install directory:&nbsp;
			<input type="text" name="userfile" class="text_area" size="65" value="<?php echo $p_startdir; ?>"/>&nbsp;
			<input type="button" class="button" value="Install" onclick="submitbutton3()" />
			</td>
		</tr>
		</table>
		
		<input type="hidden" name="task" value="installPluginDir" />
		<input type="hidden" name="option" value="com_comprofiler"/>
		<input type="hidden" name="client" value=""/>
		<?php
	echo cbGetSpoofInputTag( 'plugin' );
		?>
		</form>
		<br />

		<form enctype="multipart/form-data" action="index2.php" method="post" name="adminForm_URL">
		<table class="adminform">
		<tr>
			<th>
			Install package from web (http/https)
			</th>
		</tr>
		<tr>
			<td align="left">
			Installation package URL:&nbsp;
			<input type="text" name="userfile" class="text_area" size="65" value="http://www.joomlapolis.com/plugins/"/>&nbsp;
			<input class="button" type="submit" value="Download Package &amp; Install" />
			</td>
		</tr>
		</table>
		
		<input type="hidden" name="task" value="installPluginURL" />
		<input type="hidden" name="option" value="com_comprofiler"/>
		<input type="hidden" name="client" value=""/>
		<?php
	echo cbGetSpoofInputTag( 'plugin' );
		?>
		</form>
		<br />
		<table class="content">
		<?php
	if (!is_callable(array("JFile","write")) || ($_CB_framework->getCfg('ftp_enable') != 1)) {
			writableCell( 'components/com_comprofiler/plugin/user' );
			// writableCell( 'components/com_comprofiler/plugin/fieldtypes' );
			writableCell( 'components/com_comprofiler/plugin/templates' );
			writableCell( 'components/com_comprofiler/plugin/language' );
	}
		writableCell( 'media' );
				
		?>
		</table>
	</div>
		<?php
	}

	/**
	* Writes the edit form for new and existing module
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param moscomprofilerPlugin $row
	* @param array of string $lists  An array of select lists
	* @param cbParamsEditor $params
	* @param string $option of component.
	* 
	*/
	function editPlugin( &$row, &$lists, &$params, $options ) {
		global $_PLUGINS;

		HTML_comprofiler::secureAboveForm('editPlugin');
		outputCbTemplate( 2 );
		outputCbJs( 2 );
	    initToolTip( 2 );

	    $nameA = '';
		$filesInstalled = true;
		if ( $row->id ) {
			$nameA = '[ '. htmlspecialchars( getLangDefinition( $row->name ) ) .' ]';
			
			$xmlfile	=	$_PLUGINS->getPluginXmlPath( $row );
			$filesInstalled = file_exists($xmlfile);
		}

		global $_CB_Backend_Title;
		$_CB_Backend_Title	=	array( 0 => array( 'cbicon-48-plugins', "Community Builder Plugin: <small>" . ( $row->id ? "Edit" . ' ' . $nameA : "New" ) . '</small>' ) );

		if ( $row->id && ( ! $row->published ) ) {
			echo '<div class="cbWarning">Plugin is not published</div>' . "\n";
		}
		?>
		<form action="index2.php" method="post" name="adminForm">
		<table cellspacing="0" cellpadding="0" width="100%">
		<tr valign="top">
			<td width="60%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="2">
					Plugin Common Settings
					</th>
				</tr>
				<tr>
					<td width="100" align="left">
					Name:
					</td>
					<td>
					<input class="text_area" type="text" name="name" size="35" value="<?php echo htmlspecialchars( $row->name ); ?>" />
					</td>
				</tr>
				<tr>
					<td valign="top" align="left">
					Plugin Order:
					</td>
					<td>
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="left">
					Access Level:
					</td>
					<td>
					<?php echo $lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top">
					Published:
					</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" colspan="2">&nbsp;

					</td>
				</tr>
				<tr>
					<td valign="top">
					Description:
					</td>
					<td>
					<?php echo $row->description; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="left">
					Folder / File:
					</td>
					<td>
					<?php echo $lists['type'] . "/" . htmlspecialchars( $row->element ) . ".php"; ?>
					</td>
				</tr>
				</table>
<?php				if ( $filesInstalled && $row->id ) {
						$settingsHtml = $params->draw( 'params', 'views', 'view', 'type', 'settings' );
						if ( $settingsHtml ) {	?>
				<table class="adminform">
				<tr>
					<th>
					<?php echo htmlspecialchars( $row->name ); ?> Specific Plugin Settings
					</th>
				</tr>
				<tr>
					<td width="100%" align="left"><?php echo $settingsHtml; ?></td>
				</tr>
				</table>
<?php					}
					}   ?>
			</td>
			<td width="40%">
				<table class="adminform" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<th colspan="2">
					Parameters
					</th>
				</tr>
				<tr>
					<td>
					<?php
					if ( $filesInstalled && $row->id ) {
						echo $params->draw();
					} elseif ( !$filesInstalled ) {
						echo '<b><font style="color:red;">Plugin not installed</font></b><br />';
						echo $params->draw();
					} else {
						echo '<i>No Parameters</i>';
					}
					?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>

		<input type="hidden" name="option" value="<?php echo $options['option']; ?>" />
		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="task" value="editPlugin" />
		<?php
	echo cbGetSpoofInputTag( 'plugin' );
		?>
		</form>
		<?php
	}
	
	
	function showInstallMessage( $message, $title, $url ) {
		global $PHP_SELF;
		?>
		<table class="adminheading">
		<tr>
			<th class="install">
			<?php echo $title; ?>
			</th>
		</tr>
		</table>
		
		<table class="adminform">
		<tr>
			<td align="left">
			<strong><?php echo $message; ?></strong>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
			[&nbsp;<a href="<?php echo $url;?>" style="font-size: 16px; font-weight: bold">Continue ...</a>&nbsp;]
			</td>
		</tr>
		</table>
		<?php
	}

} // class HTML_moscomprofiler 


function writableCell( $folder, $useAdminFs = true ) {
	global $_CB_framework;

	$path					=	$_CB_framework->getCfg('absolute_path') . '/' . $folder;

	if ( $useAdminFs ) {
		cbimport( 'cb.adminfilesystem' );
		$adminFS			=&	cbAdminFileSystem::getInstance();
		if ( ! $adminFS->isUsingStandardPHP() ) {
			return;
		}
		// not yet implemented in ftp layer $writable			=	( $adminFS->file_exists( $path ) && $adminFS->is_writable( $path ) );
	}
	$writable				=	is_writable( $path );
	echo '<tr>';
	echo '<td class="item">' . $folder . '/</td>';
	echo '<td align="left">';
	echo $writable ? '<b><font color="green">Writeable</font></b>' : '<b><font color="red">Unwriteable</font></b>' . '</td>';
	echo '</tr>';
}


function update_checker(){
	  global $_CB_framework, $ueConfig;
  
/*	  ?>
	  
	  <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminheading">
      <tr>
        <th width="100%" class="info">Update check</th>
      </tr>
      </table>
      <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminform">
      <tr>
        <th class="title" colspan="2">Checking for updates...</th>
      </tr>
*/	  ?>
      <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminheading">
      <tr>
        <td width="15%"><?php echo _UE_VERSION; ?> : </td>
        <td><?php echo $ueConfig['version']; ?></td>
      </tr>
      <tr>
        <td><?php echo _UE_LATEST_VERSION; ?> : </td>
        <td><?php
      if (isset($ueConfig["noVersionCheck"]) && $ueConfig["noVersionCheck"] == "1") {
      	?><div id="cbLatestVersion"><a href="check_now" onclick="return cbCheckVersion();" style="cursor: pointer; text-decoration:underline;">check now</a></div><?php
      } else {
        ?><div id="cbLatestVersion" style="color:#CCC">...</div><?php
      }        
        ?></td>
      </tr>
    </table>
<?php 
		ob_start();
?>
    function cbCheckVersion() {
    	document.getElementById('cbLatestVersion').innerHTML = 'Checking latest version now...';
    	CBmakeHttpRequest('<?php echo "index3.php?option=com_comprofiler&task=latestVersion&no_html=1&format=raw"; ?>', 'cbLatestVersion', 'There was a problem with the request.', null);
    	return false;
    }
    function cbInitAjax() {
    	CBmakeHttpRequest('<?php echo "index3.php?option=com_comprofiler&task=latestVersion&no_html=1&format=raw"; ?>', 'cbLatestVersion', 'There was a problem with the request.', null);
    }
<?php
		if (!(isset($ueConfig["noVersionCheck"]) && $ueConfig["noVersionCheck"] == "1")) {
			echo "cbAddEvent(window, 'load', cbInitAjax);";
		}
		$js		=	ob_get_contents();
		ob_end_clean();
		$_CB_framework->document->addHeadScriptDeclaration( $js );
}
?>
