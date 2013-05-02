<?php
/**
* Joomla/Mambo Community Builder : Plugin Handler
* @version $Id: library/cb/cb.params.php 610 2006-12-13 17:33:44Z beat $
* @package Community Builder
* @subpackage cb.params.php
* @author various, JoomlaJoe and Beat
* @copyright (C) Beat and JoomlaJoe, www.joomlapolis.com and various
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
* Parameters handler
* @package Joomla/Mambo Community Builder
*/
class cbParamsEditorController extends cbParamsBase {
//	/** @var object */
//	var $_params = null;
//	/** @var string The raw params string */
//	var $_raw = null;
	/** The main enclosing tag name
	 *  @var string */
	var $_maintagname = null;
	/** The attribute name of setup file
	 *  @var string */
	var $_attrname = null;
	/** The attribute value of setup file
	 *  @var string */
	var $_attrvalue = null;
	/** plugin object
	 *  @var moscomprofilerPlugin */
	var $_pluginObject = null;
	/** @var int */
	var $_tabid = null;
	/** The xml plugin root element
	 *  @var CBSimpleXMLElement */
	var $_xml = null;
	/** The xml params element
	 *  @var CBSimpleXMLElement */
	var $_xmlElem = null;
	/** The xml actions element
	 *  @var CBSimpleXMLElement */
	var $_actions = null;
	/** The xml types element
	 *  @var CBSimpleXMLElement */
	var $_types = null;
	/** The xml views element
	 *  @var CBSimpleXMLElement */
	var $_views = null;
	/** Options from url REQUEST
	 *  @var unknown_type */
	var $_options = null;
	/** Extending view parser
	 *  @var cbEditRowView */
	var $_extendViewParser = null;
	/** CB plugin parameters
	 *  @var unknown_type */
	var $_pluginParams = null;

/**
* Constructor
* @param string The raw parms text
* @param CBSimpleXMLElement    The root element
* @param string The type of setup file
*/
	function cbParamsEditorController( $paramsValues, $xmlElement, $xml, &$pluginObject, $tabId=null, $maintagname='cbinstall', $attrname='type', $attrvalue='plugin'  ) {
	    // $this->_params = $this->parse( $text );
	    // $this->_raw = $text;
	    $this->cbParamsBase( $paramsValues );
	    $this->_xml				=&	$xmlElement;
	    if ( $xml ) {
	    	$this->_actions		=&	$xml->getElementByPath( 'actions' );
		    $this->_types		=&	$xml->getElementByPath( 'types' );
		    $this->_views		=&	$xml->getElementByPath( 'views' );
	    }
	    $this->_pluginObject	=&	$pluginObject;
	    $this->_tabId			=	$tabId;
	    $this->_maintagname		=	$maintagname;
	    $this->_attrname		=	$attrname;
	    $this->_attrvalue		=	$attrvalue;
	    $this->_pluginParams	=&	$this;
	}
	function setAllParams( &$object ) {
		$this->_params			=&	$object;
	}
	function setPluginParams( &$pluginParams ) {
	    $this->_pluginParams	=&	$pluginParams;
	}
	function setOptions( $options ) {
		$this->_options			=	$options;
	}
	function setExtendedViewParser( &$extendedViewParser ) {
		$this->_extendViewParser =&	$extendedViewParser;
	}
/**
* Converts the parameters received as POST array into the raw parms text
* @param mixed POST array or string
* @return string The raw parms text
* @var string The type of setup file
*/
	function getRawParams ( $params ) {
		if(is_array($params)) {
			foreach ($params as $k=>$v) {
				if (is_array($v)) {
					$v = implode("|*|", $v);
				}
				if (get_magic_quotes_gpc()) {
					$v = stripslashes( $v );
				}
				$txt[] = "$k=$v";
			}
			$ret = cbEditRowView::textareaHandling( $txt );
			if (get_magic_quotes_gpc()) {
				$ret = addslashes( $ret );
			}
		} else {
			$ret = $params;
		}
		return $ret;
	}

/**
* Converts the parameters received as POST array into the |*| and CBparams formats
* @param  array  $params  MODIFIED BY THIS CALL: POST array
*/
	function fixMultiSelects ( &$params ) {
		if ( is_array( $params ) ) {
			foreach ( $params as $k => $v ) {
				if ( is_array( $v ) ) {
					if ( isset( $v[0] ) ) {
						$params[$k]		=	implode( "|*|", $v );
					} else {
						$params[$k]		=	cbParamsEditorController::getRawParams( $v );
					}
				}
			}
		}
	}
	/**
	 * Draws the control, or the default text area if a setup file is not found
	 *
	 * @param  string   $tag_path
	 * @param  string   $grand_parent_path
	 * @param  string   $parent_tag
	 * @param  string   $parent_attr
	 * @param  string   $parent_attrvalue
	 * @param  string   $control_name
	 * @param  boolean  $paramstextarea
	 * @param  atring   $viewType          ( 'view', 'param', 'depends': means: <param> tag => param, <field> tag => view )
	 * @param  string   $htmlFormatting    ( 'table', 'td', 'none', 'fieldsListArray' )
	 * @return string                       HTML
	 */
	function draw( $tag_path='params', $grand_parent_path=null, $parent_tag=null, $parent_attr=null, $parent_attrvalue=null, $control_name='params', $paramstextarea=true, $viewType = 'depends', $htmlFormatting = 'table'  ) {

		if ( $this->_xml ) {
			$element =& $this->_xml;
			if ( $element && $element->name() == $this->_maintagname && $element->attributes( $this->_attrname ) == $this->_attrvalue) {
				if ( $grand_parent_path != null ) {
					$element =& $element->getElementByPath( $grand_parent_path );
					if ( ! $element ) {
						return null;
					}
				}
				if ( $parent_tag != null && $parent_attr != null && $parent_attrvalue != null ) {
					$element =& $element->getChildByNameAttr( $parent_tag, $parent_attr, $parent_attrvalue );
					if ( $element ) {
						if ( $tag_path ) {
							$element =& $element->getElementByPath( $tag_path );
						}
						if ( $element ) {
							$this->_xmlElem =& $element;
						}
					}
				} else {
					$element =& $element->getElementByPath( $tag_path );
					if ( $element ) {
						$this->_xmlElem =& $element;
					}
				}
			} elseif ( ! $tag_path ) {
				$this->_xmlElem =& $element;
			}
		}

	    if ( $this->_xmlElem ) {
	    	
	    	$controllerView = new cbDrawController( $this->_xmlElem, $this->_actions, $this->_options );
	    	$controllerView->setControl_name( $control_name );
	    	
	    	$editRowView =& new cbEditRowView( $this->_pluginParams, $this->_types, $this->_actions, $this->_views, $this->_pluginObject, $this->_tabid );
	    	$editRowView->setModelOfDataRows( $this->_params );
	    	if ( $this->_extendViewParser ) {
	    		$editRowView->setExtendedViewParser( $this->_extendViewParser );
	    	}
	    	return $editRowView->renderEditRowView( $this->_xmlElem, $this->_params, $controllerView, $this->_options, $viewType, $htmlFormatting );
		} else {
			if ($paramstextarea) {
				return "<textarea name=\"$control_name\" cols=\"40\" rows=\"10\" class=\"text_area\">".htmlspecialchars($this->_raw)."</textarea>";
			} else {
				return null;
			}
		}
	}
} // class cbParamsEditorController

class cbEditRowView {
	var $_i						 =	0;
	/** A stack (array) of the data which is a class
	 *  @var array of stdClass */
	var $_modelOfData			=	array();
	/** The data rows (for ordering arrows)
	 *  @var array */
	var $_modelOfDataRows		=	null;
	/** The current row number (for ordering arrows)
	 *  @var int */
	var $_modelOfDataRowsNumber	=	null;
	/** Extending view functions
	 *  @var cbEditRowView */
	var $_extendViewParser		=	null;
	/** Drawing controller
	 *  @var cbDrawController */
	var $_controllerView		=	null;
	/** The options from url REQUEST
	 * @var array of string */
	var $_options				=	null;
	/** The plugin parameters
	 *  @var cbParamsBase */
	var $_pluginParams			=	null;
	/** The parameters objects for individual columns (cache)
	 *  @var array of cbParamsBase */
	var $_paramsOfColumns		=	null;
	/** The xml <types> element
	 *  @var CBSimpleXMLElement */
	var $_types					=	null;
	/** The xml <actions> element
	 *  @var CBSimpleXMLElement */
	var $_actions				=	null;
	/** The xml <views> element
	 *  @var CBSimpleXMLElement */
	var $_views					=	null;
	/** The xml parent element
	 *  @var CBSimpleXMLElement */
	var $_parentModelOfView		=	null;
	/** The plugin object
	 *  @var moscomprofilerPlugin */
	var $_pluginObject			=	null;
	/** Id of tab
	 *  @var int */
	var $_tabid					=	null;
	/** internal temporary var: if render as view (true) or as param (false)
	 *  @var boolean */
	var $_view					=	null;
	/** methods of this class
	 * @var array */
	var $_methods				=	null;
	/** list of possible values
	 * @var array of stdClass: 'name' => object (->value, (optional ->index), ->text) */
	var $_selectValues			=	array();

	function cbEditRowView( &$pluginParams, &$types, &$actions, &$views, &$pluginObject, $tabId = null ) {
		$this->_pluginParams		=&	$pluginParams;
		$this->_types				=&	$types;
		$this->_actions				=&	$actions;
		$this->_views				=&	$views;
		$this->_pluginObject		=&	$pluginObject;
		$this->_tabid				=	$tabId;
	}
	
	function setParentView( &$modelView ) {
		$this->_parentModelOfView	=&	$modelView;
		if ( isset( $this->_extendViewParser ) && ( $this->_extendViewParser->_parentModelOfView === null ) ) {
			$this->_extendViewParser->setParentView( $modelView );
		}
	}
	function pushModelOfData( &$modelOfData ) {
		array_unshift( $this->_modelOfData, $modelOfData );
	}
	function popModelOfData( ) {
		array_shift( $this->_modelOfData );
	}
	function setModelOfDataRows( &$modelOfDataRows ) {
		$this->_modelOfDataRows		=&	$modelOfDataRows;
	}
	function setModelOfDataRowsNumber( $i ) {
		$this->_modelOfDataRowsNumber = $i;
		if ( $this->_extendViewParser ) {
			$this->_extendViewParser->setModelOfDataRowsNumber( $i );
		}
	}
	/**
	 * Sets an extended view parser
	 * This method is experimental and not part of CB API.
	 *
	 * @param CBSimpleXmlElement $extendedViewParserElement  an xml element like <extendparser class="className" /> where className extends cbEditRowView
	 */
	function setExtendedViewParser( &$extendedViewParserElement ) {
		if ( $extendedViewParserElement ) {
			$class			=	$extendedViewParserElement->attributes( 'class' );
			if ( $class ) {
				$extendedViewParser			=&	new $class( $this->_pluginParams, $this->_types, $this->_actions, $this->_views, $this->_pluginObject, $this->_tabid, $this );
				$this->_extendViewParser	=&	$extendedViewParser;
			}
		}
	}
	function setSelectValues( &$node, &$selectValues ) {
		$this->_selectValues[$node->attributes( 'name' )]	=&	$selectValues;
	}
	function & _getSelectValues( &$node ) {
		$nodeName			=	$node->attributes( 'name' );
		if ( isset( $this->_selectValues[$nodeName] ) ) {
			return $this->_selectValues[$nodeName];
		} else {
			$arr	=	array();
			return $arr;
		}
	}
	/**
	 * Renders as ECHO HTML code of a table
	 *
	 * @param CBSimpleXmlElement  $modelView
	 * @param stdClass            $modelOfData       ( $row object )
	 * @param cbDrawController    $controllerView
	 * @param array               $options
	 * @param atring              $viewType   ( 'view', 'param', 'depends': means: <param> tag => param, <field> tag => view )
	 * @param atring              $htmlFormatting   ( 'table', 'td', 'none' )
	 * 
	 */
	function renderEditRowView( &$modelOfView, &$modelOfData, &$controllerView, $options, $viewType = 'depends', $htmlFormatting = 'table' ) {
		global $_CB_framework;

		if ( $this->_parentModelOfView === null ) {
			$this->setParentView( $modelOfView );
		}
		$this->pushModelOfData( $modelOfData );
		$this->_controllerView	=&	$controllerView;
		$this->_options			=	$options;

		if ( $this->_extendViewParser ) {
			$html	=	$this->_extendViewParser->renderEditRowView( $modelOfView, $modelOfData, $controllerView, $options, $viewType, $htmlFormatting );
			if ( $html ) {
				return $html;
			}
		}

		$html	= array();
		if ( $htmlFormatting == 'table' ) {
			$html[]	= '<table class="adminform">';

			$label = $modelOfView->attributes( 'label' );
			if ( $label ) {
			    // add the params description to the display
			    $html[] = '<tr><th colspan="3">' . CBTxt::Th( getLangDefinition( $label ) ) . '</th></tr>';
			}
			$description = $modelOfView->attributes( 'description' );
			if ( $description ) {
			    // add the params description to the display
			    $html[] = '<tr><td colspan="3">' . CBTxt::Th( getLangDefinition( $description ) ) . '</td></tr>';
			}
		}
		$this->_methods = get_class_methods( get_class( $this ) );

		$this->_jsif =	array();
		$tabs		= new cbTabs( 0, $_CB_framework->getUi() );
		$html[]		= $this->renderAllParams( $modelOfView, $controllerView->control_name(), $tabs, $viewType, $htmlFormatting );
		if ( $htmlFormatting == 'table' ) {
			$html[]		= '</table>';
		}
		
		$jsCode		=	$this->_compileJsCode();
		if ( $jsCode && ( $htmlFormatting != 'fieldsListArray' ) ) {
			$_CB_framework->document->addHeadScriptDeclaration( $jsCode );
		}
		
		return ( $htmlFormatting == 'fieldsListArray' ? $html : implode( "\n", $html ) );
	}

	/**
	* @param string The name of the field
	* @param mixed The default value if not found
	* @return string
	*/
	function get( $key, $default=null ) {
	    if ( isset( $this->_modelOfData[0]->$key ) ) {
	    	if (is_array( $default ) ) {
	    		return explode( '|*|', $this->_modelOfData[0]->$key );
	    	} else {
		        return $this->_modelOfData[0]->$key;
	    	}
		} else {
		    return $default;
		}
	}

	function _compileJsCode( ) {
		if ( count( $this->_jsif ) == 0 ) {
			return null;
		}
		$js	=	'';
		$i	=	0;
		foreach ( $this->_jsif as $ifVal ) {

			$ifName					=	$ifVal['ifname'];
			$element				=	$ifVal['element'];
			$name					=	$this->control_id( $ifVal['control_name'], $element->attributes( 'name' ) );
			
			$operator				=	$element->attributes( 'operator' );
			$value					=	$element->attributes( 'value' );
			// $valuetype			=	$element->attributes( 'valuetype' );

			if ( $operator ) {
				$operatorNegation	=	array( '=' => '!=', '==' => '!=', '!=' => '==', '<>' => '==', '<' => '>=', '>' => '<=', '<=' => '>', '>=' => '<', 'regexp' => 'regexp' );
				$revertedOp			=	$operatorNegation[$operator];
			} elseif ( isset( $ifVal['onchange'] ) && ( $ifVal['onchange'] == 'evaluate' ) ) {
				$revertedOp			=	'evaluate';
			} else {
				$revertedOp			=	'no-operator-specified-in-xml';
			}
			//if ( in_array( $valuetype, array( 'string', 'const:string', 'text', 'const:text' ) ) ) {
			//	$value				=	"\\'" . $value . "\\'";
			//}
			if ( isset( $ifVal['show'] ) && ( count( $ifVal['show'] ) > 0 ) ) {
				$show				=	"['" . implode( "','", $ifVal['show'] ) . "']";
			} else {
				$show				=	"[]";
			}
			if ( isset( $ifVal['set'] ) && ( count( $ifVal['set'] ) > 0 ) ) {
				$set				=	"['" . implode( "','", $ifVal['set'] ) . "']";
			} else {
				$set				=	"[]";
			}
			$js	.=	"cbHideFields[" . $i . "] = new Array();\n";
			$js	.=	"cbHideFields[" . $i . "][0] = '" . $ifName		. "';\n";
			$js	.=	"cbHideFields[" . $i . "][1] = '" . $name		. "';\n";
			$js	.=	"cbHideFields[" . $i . "][2] = '" . $revertedOp	. "';\n";
			$js	.=	"cbHideFields[" . $i . "][3] = '" . str_replace( '\\', '\\\\', $value ) . "';\n";
			$js	.=	"cbHideFields[" . $i . "][4] = "  . $show		. ";\n";
			$js	.=	"cbHideFields[" . $i . "][5] = "  . $set		. ";\n";
			$i++;
		}
		return $js;
	}

	function _evalIf( &$element ) {
		$name				=	$element->attributes( 'name' );
		$operator			=	$element->attributes( 'operator' );
		$value				=	$element->attributes( 'value' );
		// $valuetype		=	$element->attributes( 'valuetype' );
		
		$paramValue			=	$this->get( $name );					//TBD: missing default value, but not easy to find, as it's in the view param for now: $param->attributes( 'default' ) );
		
		if ( $element->attributes( 'translate' ) == '_UE' ) {
			$value			=	getLangDefinition( $value );
		} elseif ( $element->attributes( 'translate' ) == 'yes' ) {
			$value			=	CBTxt::T( $value );
		}

		switch ( $operator ) {
			case '=':
			case '==':
				$result		=	( $paramValue == $value );
				break;
			case '!=':
			case '<>':
				$result		=	( $paramValue != $value );
				break;
			case '<':
				$result		=	( $paramValue < $value );
				break;
			case '>':
				$result		=	( $paramValue > $value );
				break;
			case '<=':
				$result		=	( $paramValue <= $value );
				break;
			case '>=':
				$result		=	( $paramValue >= $value );
				break;
			case 'regexp':
				$result		=	( preg_match( '/' . $value . '/', $paramValue ) == 1 );
				break;
		
			default:
				break;
		}
		return $result;
	}

	function _htmlId( $control_name, $element ) {
		$name				=	$element->attributes( 'name' );
		if ( $name ) {
			return str_replace( array( '[', ']' ), '__', 'cbfr_' . ( $control_name ? $control_name . '_' : '' ) . $name );
		} else {
			return null;
		}
	}

	function _outputIdEqualHtmlId( $control_name, $element ) {
		$htmlid				=	$this->_htmlId( $control_name, $element );
		if ( $htmlid ) {
			$htmlid			=	' id="' . htmlspecialchars( $htmlid ) . '"';
		}
		return $htmlid;
	}

	function _renderLine( $param, $result, $control_name='params', $htmlFormatting = 'table', $htmlid = true ) {
		$html			=	array();
		if ( $htmlid ) {
			$htid		=	$this->_outputIdEqualHtmlId( $control_name, $param );
		} else {
			$htid		=	null;
		}
		if ( $htmlFormatting == 'table' ) {
			$html[]		= '<tr' . $htid
						. ( ( $param->attributes( 'class' ) ) ? ' class="' . htmlspecialchars( $param->attributes( 'class' ) ) . '"' : '' )
						. '>';
			if ( $param->attributes( 'label' ) === '' ) {
				$html[] = '<td colspan="2" width="95%"'
						. ( ( $param->attributes( 'valuedescription' ) ) ? ' onmouseover="return overlib(\''. str_replace( array( "\n", "\r" ), array( "&lt;br /&gt;", "\\r" ), htmlspecialchars( addslashes( $param->attributes( 'valuedescription' ) ) ) ) .'\', CAPTION, \''
											  . htmlspecialchars( addslashes( $param->attributes( 'valuedescriptiontitle' ) ) ) . '\', BELOW, RIGHT);" onmouseout="return nd();"' : '' )
						. '>' . $result[1] . '</td>';
			} else {
				$html[] = '<td width="35%" align="right" valign="top">' . $result[0] . '</td>';
				$html[] = '<td width="60%"'
						. ( ( $param->attributes( 'valuedescription' ) ) ? ' onmouseover="return overlib(\''. str_replace( array( "\n", "\r" ), array( "&lt;br /&gt;", "\\r" ), htmlspecialchars( addslashes( $param->attributes( 'valuedescription' ) ) ) ) .'\', CAPTION, \''
							  . htmlspecialchars( addslashes( $param->attributes( 'valuedescriptiontitle' ) ) ) . '\', BELOW, RIGHT);" onmouseout="return nd();"' : '' )
						. '>' . $result[1] . '</td>';
			}
			$html[]		= '<td width="5%" align="left" valign="top">' . $result[2] . "</td>";
			$html[]		= '</tr>';
		} elseif ( $htmlFormatting == 'td' ) {
			$type		=	$param->attributes( 'type' );
			$rowspan	=	$param->attributes( 'rowspan' );
			if ( ( $type != 'hidden' ) && ( ( ! $rowspan ) || ( ( $rowspan == 'all' ) && ( $this->_modelOfDataRowsNumber == 0 ) ) ) ) {
				$html[] = "\t\t\t<td" . $htid
						. ( ( $param->attributes( 'class' ) ) ? ' class="' . htmlspecialchars( $param->attributes( 'class' ) ) . '"' : '' )
						. ( ( $param->attributes( 'align' ) ) ? ' style="text-align:' . htmlspecialchars( $param->attributes( 'align' ) ) . ';"' :
								( in_array( $param->attributes( 'type' ), array( 'checkmark', 'published' ) ) ? ' style="text-align:center;"' : '' ) )
						. ( ( $param->attributes( 'nowrap' ) ) || in_array( $param->attributes( 'type' ), array( 'checkmark', 'ordering' ) ) ? ' nowrap="nowrap"' : '' )
						. ( ( $param->attributes( 'valuedescription' ) ) ? ' onmouseover="return overlib(\''. htmlspecialchars( addslashes( str_replace( array( "\n", "\r" ), array( "<br />", "\\r" ), $param->attributes( 'valuedescription' ) ) ) ) .'\', CAPTION, \''
																	  . htmlspecialchars( addslashes( $param->attributes( 'valuedescriptiontitle' ) ) ) . '\', BELOW, RIGHT);" onmouseout="return nd();"' : '' )
						. ( ( $rowspan == 'all' ) ? ' rowspan="' . (int) count( $this->_modelOfDataRows ) . '"' : '' )
						. '>'
						. $result[1]
						. "</td>";
			} else {
				$html[]	=	'';
			}
		} elseif ( $htmlFormatting == 'span' ) {
			if (substr( $result[0], -1 ) == ":" ) {
				$result[0]	=	substr( $result[0], 0, -1 );
			}
			if (substr( $result[0], -2 ) == "%s" ) {
				$result[0]	=	substr( $result[0], 0, -2 );
				$html[] = '<div' . $htid
						. ( ( $param->attributes( 'class' ) ) ? ' class="' . htmlspecialchars( $param->attributes( 'class' ) ) . '"' : '' )
						. '>'
						. '<span class="cbLabelSpan">' . $result[0] . '</span>'
						. '<span class="cbFieldSpan"'
						. ( ( $param->attributes( 'valuedescription' ) ) ? ' onmouseover="return overlib(\''. str_replace( array( "\n", "\r" ), array( "&lt;br /&gt;", "\\r" ), htmlspecialchars( addslashes( $param->attributes( 'valuedescription' ) ) ) ) .'\', CAPTION, \''
						  											. htmlspecialchars( addslashes( $param->attributes( 'valuedescriptiontitle' ) ) ) . '\', BELOW, RIGHT);" onmouseout="return nd();"' : '' )
						. '>' . $result[1] . '</span> '
						. '</div>';
			} else {
				$html[] = '<div' . $htid
						. ( ( $param->attributes( 'class' ) ) ? ' class="' . htmlspecialchars( $param->attributes( 'class' ) ) . '"' : '' )
						. '>'
						. '<span class="cbFieldSpan"'
						. ( ( $param->attributes( 'valuedescription' ) ) ? ' onmouseover="return overlib(\''. str_replace( array( "\n", "\r" ), array( "&lt;br /&gt;", "\\r" ), htmlspecialchars( addslashes( $param->attributes( 'valuedescription' ) ) ) ) .'\', CAPTION, \''
																	  . htmlspecialchars( addslashes( $param->attributes( 'valuedescriptiontitle' ) ) ) . '\', BELOW, RIGHT);" onmouseout="return nd();"' : '' )
						. '>' . $result[1] . '</span> '
						. '<span class="cbLabelSpan">' . $result[0] . '</span>'
						. '</div>';
			}
		} elseif ( in_array( $htmlFormatting, array( 'none', 'fieldsListArray' ) ) ) {
			$html[]		=	$result[1];
		} else {
			$html[]	= "*" . $result[1] . "*";
		}
		return ( $htmlFormatting == 'fieldsListArray' ? $html : implode( "\n", $html ) );
	}
	function & _parseParamsColumn( $paramsName ) {
		if ( ! isset( $this->_paramsOfColumns[$paramsName] ) ) {
			$this->_paramsOfColumns[$paramsName]	=	new cbParamsBase( isset( $this->_modelOfData[0]->$paramsName ) ? $this->_modelOfData[0]->$paramsName : '' );
		}
		return $this->_paramsOfColumns[$paramsName]->_params;
	}
	/**
	 * Renders all parameters (including inheritance magic)
	 *
	 * @param CBSimpleXMLElement $element
	 * @param string             $control_name
	 * @param cbTabs             $tabs
	 * @param atring             $viewType         ( 'view', 'param', 'depends': means: <param> tag => param, <field> tag => view )
	 * @param atring             $htmlFormatting   ( 'table', 'td', 'span', 'none' )
	 * @return string HTML
	 */
	function renderAllParams( &$xmlParentElement, $control_name='params', $tabs=null, $viewType = 'depends', $htmlFormatting = 'table' ) {
		$html											=	array();
		$extenders										=	array();

		if ( ( $this->_inverted ) && ( count( $this->_extenders ) == 1 ) ) {
			$element									=	array_shift( $this->_extenders );
			array_unshift( $this->_extenders, array( &$xmlParentElement ) );
			$this->_inverted							=	false;
		} else {
			$element									=&	$xmlParentElement;
		}

		if ( is_array( $element ) ) {
			foreach ( $element as $el ) {
				$html[]									=	$this->renderAllParams( $el, $control_name, $tabs, $viewType, $htmlFormatting );
			}
		} else {
			$identicalMatches							=	array();
			$nonMatches									=	array();
			if ( count( $this->_extenders ) > 0 ) {
				$extenders								=	array_shift( $this->_extenders );
				foreach ( $extenders as $ext ) {
					if ( ( $ext->name() == 'inherit' ) || ( ( ( $ext->name() == $element->name() ) ) && $ext->attributes( 'name' ) == $element->attributes( 'name' ) ) ) {
						if ( count( $element->children() ) > 0 ) {
							foreach ( $ext->children() as $chld ) {
								$this->_addTagMatch( $identicalMatches, $chld );
							}
						} else {
							foreach ( $ext->children() as $chld ) {
								$saveExtTwo				=	$this->_extenders;
								$this->_extenders		=	array ();
								$html[]					=	$this->renderOneParamAndChildren( $chld, $control_name, $tabs, $viewType, $htmlFormatting );
								$this->_extenders		=	$saveExtTwo;
							}
						}
					} else {
						foreach ( $ext->children() as $chld ) {
							$nonMatches[]				=	$chld;
						}
					}
				}
			}
	
			foreach ( $element->children() as $param ) {
				$idkeyMatched							=	$this->_getKeyOfTagMatch( $identicalMatches, $param );
				if ( $idkeyMatched !== null ) {
					foreach ( $identicalMatches as $idkey => $idmatch ) {
						if ( $idkey == $idkeyMatched ) {
							break;
						} else {
							foreach ( $idmatch as $extparam ) {
								$saveExtTwo				=	$this->_extenders;
								$this->_extenders		=	array ( array( &$param ) );
								$html[]					=	$this->renderOneParamAndChildren( $extparam, $control_name, $tabs, $viewType, $htmlFormatting );
								$this->_extenders		=	$saveExtTwo;
							}
							unset( $identicalMatches[$idkey] );
						}
					}
					foreach ( $identicalMatches[$idkeyMatched] as $k => $extparam ) {
						$saveExtTwo						=	$this->_extenders;
						$this->_extenders				=	array ( array( &$param ) );
						$this->_inverted				=	true;
						$html[]							=	$this->renderOneParamAndChildren( $extparam, $control_name, $tabs, $viewType, $htmlFormatting );
						$this->_inverted				=	false;
						$this->_extenders				=	$saveExtTwo;
						unset( $identicalMatches[$idkeyMatched][$k] );
					}
				} else {
					$html[]								=	$this->renderOneParamAndChildren( $param, $control_name, $tabs, $viewType, $htmlFormatting );
				}
			}
			foreach ( $identicalMatches as $idmatch ) {
				foreach ( $idmatch as $extparam ) {
					$saveExtTwo							=	$this->_extenders;
					$this->_extenders					=	array ();
					$html[]								=	$this->renderOneParamAndChildren( $extparam, $control_name, $tabs, $viewType, $htmlFormatting );
					$this->_extenders					=	$saveExtTwo;
				}
			}
			foreach ( $nonMatches as $chld ) {
		//		if ( ( count( $chld->children() ) == 0 ) || in_array( $chld->name(), array( 'param', 'field' ) ) ) {
	//				$html[]								=	$this->renderOneParamAndChildren( $chld, $control_name, $tabs, $viewType, $htmlFormatting );
		//			unset( $this->_extenders[$k] );
		//		}
			}
	
		//	$this->_extenders							=	$saveExt;
	
			if ( ( count( $element->children() ) < 1 ) && ( count( $extenders ) == 0 ) ) {
				if ( $htmlFormatting == 'table' ) {
					$html[] = "<tr><td colspan=\"2\"><i>" . _UE_NO_PARAMS . /* ": " . $element->name() . '(' . implode( ',', $element->attributes() ) . ')' . */ "</i></td></tr>";
				} elseif ( $htmlFormatting == 'td' ) {
					$html[] = "<td><i>" . _UE_NO_PARAMS . "</i></td>";
				} elseif ( $htmlFormatting == 'fieldsListArray' ) {
					// nothing
				} else {
					$html[] = "<i>" . _UE_NO_PARAMS . "</i>";
				}
			}
		}
		return ( $htmlFormatting == 'fieldsListArray' ? $html : implode( "\n", $html ) );
	}
	/**
	 * Returns a unique text id of a xml element depending on name and attribute values
	 * @access private
	 *
	 * @param  CBSimpleXMLElement  $el
	 * @return string
	 */
	function _uniqueTag( &$el ) {
		$add		=	'';
		foreach ( $el->attributes() as $k => $v ) {
			$add	.=	'|**|' . $k . '|==|' . $v;
		}
		return ( $el->name()) . $add;
	}
/*
	function _explodeTag( $uniqueTag ) {
		$tags		=	explode( '|**|', $uniqueTag );
		$name		=	$tags[0];
		$attr		=	array();
		for ( $i = 1, $n = count( $tags ); $i < $n; $i++ ) {
			$parts	=	explode( '|==|', $tags[$i] );
			$attr[$parts[0]]	=	$parts[1];
		}
	}
*/
	function _addTagMatch( &$identicalMatches, $chld ) {
		$identicalMatches[$this->_uniqueTag( $chld )][]	=	$chld;
	}
	function _getKeyOfTagMatch( &$identicalMatches, &$param ) {
		$paramTag	=	$this->_uniqueTag( $param );
		foreach ( array_keys( $identicalMatches ) as $k ) {
			if ( strpos( $k, $paramTag ) === 0 ) {
				return $k;
			}
		}
		return null;
	}
	function _getKeyOfTagMatchOLD( &$identicalMatches, $param ) {
		if ( isset( $identicalMatches[$this->_uniqueTag( $param )] ) ) {
			return $this->_uniqueTag( $param );
		} else {
			return null;
		}
	}
	/**
	 * renders one parameter and its children
	 *
	 * @param CBSimpleXMLElement $param
	 * @param string             $control_name
	 * @param cbTabs             $tabs
	 * @param atring             $viewType   ( 'view', 'param', 'depends': means: <param> tag => param, <field> tag => view )
	 * @param atring             $htmlFormatting   ( 'table', 'td', 'span', 'none' )
	 * @return string HTML
	 */
	var $_inverted		=	false;
	var $_extenders		=	array();
	function renderOneParamAndChildren( &$param, $control_name='params', $tabs=null, $viewType = 'depends', $htmlFormatting = 'table' ) {
		static $tabNavJS				=	array();		// javascript for all nested tabs.
		static $tabpaneCounter			=	0;				// level of tabs (for nested tabs)
		// static $tabpaneNames			=	array();		// names of the tabpanes of level [tabpaneCounter] for the tabpanetabs

		$html							=	array();

		$viewMode						=	$param->attributes( 'mode' );
		switch ( $viewMode ) {
			// case 'view':
			case 'show':
				$viewType				=	'view';
				break;
			// case 'param':
			case 'edit':
				$viewType				=	'param';
				break;
			default:
				break;
		}

		switch ( $param->name() ) {
			case 'inherit':
				$from				=	$param->attributes( 'from' );
				if ( $from ) {
					$fromXml		=	$param->xpath( $from );
					if ( $fromXml && ( count( $fromXml ) > 0 ) ) {
						array_unshift( $this->_extenders, array( &$param ) );
						foreach ( $fromXml as $fmx ) {
							$html[]	=	$this->renderAllParams( $fmx, $control_name, $tabs, $viewType, $htmlFormatting );
						}
					}
				}
				break;
			case 'param':
				$result				=	$this->renderParam( $param, $control_name, ( $viewType == 'view' ), $htmlFormatting );
				$html[]				=	$this->_renderLine( $param, $result, $control_name, $htmlFormatting );
				if ( ( ! ( $viewType == 'view' ) ) && ( $param->attributes( 'onchange' ) == 'evaluate' ) ) {
					$ifName			=	$this->_htmlId( $control_name, $param );
					$this->_jsif[$ifName]['element']					=	$param;
					$this->_jsif[$ifName]['control_name']				=	$control_name;
					$this->_jsif[$ifName]['ifname']						=	$ifName;
					$this->_jsif[$ifName]['onchange']					=	$param->attributes( 'onchange' );
				}
				break;

			case 'params':
				$paramsName			=	$param->attributes( 'name' );
				$paramsType			=	$param->attributes( 'type' );
				if ( ( $paramsType == 'params' ) && $paramsName ) {
					$valueObj		=&	$this->_parseParamsColumn( $paramsName );
					$this->pushModelOfData( $valueObj );
					if ( $control_name ) {
						$child_cnam	=	$control_name . '[' . $paramsName . ']';
					} else {
						$child_cnam	=	$paramsName;
					}

					$html[]			=	$this->renderAllParams( $param, $child_cnam, $tabs, $viewType, $htmlFormatting );
					$this->popModelOfData();
				}
				break;
			case 'field':
				$result				=	$this->renderParam( $param, $control_name, ( $viewType != 'param' ) );
				
				$link				=	$param->attributes( 'link' );
				$title				=	htmlspecialchars( $param->attributes( 'title' ) );
				if ( $title ) {
					$title			= ' title="' . $title . '"';
				} else {
					$title			= '';
				}

				if ( $htmlFormatting != 'fieldsListArray' ) {
					if ( $link ) {
						if ( $param->attributes( 'target' ) == '_blank' ) {
							$linkhref = $this->_controllerView->drawUrl( $link, $param, $this->_modelOfData[0], isset( $this->_modelOfData[0]->id ) ? $this->_modelOfData[0]->id : null, true, false );		//TBD NOT URGENT: hardcoded id column name 'id'
							$onclickJS	=	'window.open(\'' . htmlspecialchars( unHtmlspecialchars( $linkhref ) )
								 		.	'\', \'cbtablebrowserpopup\', \'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no\'); return false;';
 							$rowOnclickHtml	=	' onclick="' . $onclickJS . '"';
						} else {
							$linkhref = $this->_controllerView->drawUrl( $link, $param, $this->_modelOfData[0], isset( $this->_modelOfData[0]->id ) ? $this->_modelOfData[0]->id : null, true );		//TBD NOT URGENT: hardcoded id column name 'id'
							$rowOnclickHtml	=	'';
						}

						$result[1]		= '<a href="' . $linkhref .'"' . $title . $rowOnclickHtml . '>' . ( trim( $result[1] ) ? $result[1] : '---' ) . '</a>';
					} elseif ( $title ) {
						$result[1]		= '<span' . $title . '>' . $result[1] . '</span>';
					}
				}
				$html[]	= $this->_renderLine( $param, $result, $control_name, $htmlFormatting, false );
				break;
		
			case 'fieldset':
				$htid				=	$this->_outputIdEqualHtmlId( $control_name, $param );

				$legend				=	$param->attributes( 'label' );
				$description		=	$param->attributes( 'description' );
				$name				=	$param->attributes( 'name' );
				$class				=	$param->attributes( 'class' );
				
				$fieldsethtml		=	'<fieldset' . ( $class ? ' class="' . $class . '"' : ( $name ? ( ' class="cbfieldset_' . $name . '"' ) : '' ) ) . '>';
				if ( $htmlFormatting == 'table' ) {
					$html[] 		=	'<tr' . $htid . '><td colspan="3" width="100%">' . $fieldsethtml;
				} elseif ( $htmlFormatting == 'td' ) {
					$html[]			=	"\t\t\t<td" . $htid . ">" . $fieldsethtml;
				} elseif ( $htmlFormatting == 'span' ) {
					$html[]			=	'<div' . $htid . '>' . $fieldsethtml;
				} elseif ( $htmlFormatting == 'fieldsListArray' ) {
					// nothing
				} else {
					$html[] 		=	'<fieldset' . $htid . ( $name ? ( ' class="cbfieldset_' . $name . '"' ) : '' ) . '>';
				}
				if ( $legend && ( $htmlFormatting != 'fieldsListArray' ) ) {
				    $html[]			=	'<legend' . ( $class ? ' class="' . $class . '"' : '' ) . '>' . CBTxt::Th( getLangDefinition($legend) ) . '</legend>';
				}
				if ( $htmlFormatting == 'table' ) {
					$html[]			=	'<table class="paramlist" cellspacing="0" cellpadding="0" width="100%">';
					if ( $description ) {
					    $html[]		=	'<tr><td colspan="3" width="100%"><strong>' . CBTxt::Th( getLangDefinition($description) ) . '</strong></td></tr>';
					}
				} elseif ( $htmlFormatting == 'td' ) {
					if ( $description ) {
						$html[] 	=	'<td colspan="3" width="100%"><strong>' . CBTxt::Th( getLangDefinition($description) ) . '</strong></td>';
					}
				} elseif ( $htmlFormatting == 'span' ) {
					if ( $description ) {
						$html[]		=	'<span class="cbLabelSpan">' . CBTxt::Th( getLangDefinition($description) ) . '</span> ';
					}
					$html[]			=	'<span class="cbFieldSpan">';
				} elseif ( $htmlFormatting == 'fieldsListArray' ) {
					// nothing
				} else {
					if ( $description ) {
						$html[] 	=	'<strong>' . CBTxt::Th( getLangDefinition($description) ) . '</strong>';
					}
				}
				$html[]				=	$this->renderAllParams( $param, $control_name, $tabs, $viewType, $htmlFormatting );
				
				if ( $htmlFormatting == 'table' ) {
					$html[]			=	"\n\t</table>";
					$html[]			=	'</fieldset></td></tr>';
				} elseif ( $htmlFormatting == 'td' ) {
					$html[]			=	'</fieldset></td>';
				} elseif ( $htmlFormatting == 'span' ) {
					$html[]			=	'</span></fieldset></div>';
				} elseif ( $htmlFormatting == 'fieldsListArray' ) {
					// nothing
				} else {
					$html[]			=	'</fieldset>';
				}
				break;
				
			case 'fields':
			case 'status':
				$html[]				=	$this->renderAllParams( $param, $control_name, $tabs, $viewType, $htmlFormatting );
				break;
				
			case 'if':
				$showInside							=	true;
				$ifType								=	$param->attributes( 'type' );
				if ( ( $ifType == 'showhide' ) && ( ! ( $viewType == 'view' ) ) ) {
					$ifName							=	$this->_htmlId( $control_name, $param ) . $param->attributes( 'operator' ) . $param->attributes( 'value' ). $param->attributes( 'valuetype' );
					// $this->_jsif[$ifName]		=	array();
					// $this->_jsif[$ifName]['show']=	array();
					// $this->_jsif[$ifName]['set']	=	array();
					if ( count( $param->children() ) > 0 ) {
						foreach ( $param->children() as $subParam ) {
							if ( $subParam->name() == 'showview' ) {
								$viewName			=	$subParam->attributes( 'view' );
								$viewModel			=&	$this->_views->getChildByNameAttributes( 'view', array( 'ui' => 'admin', 'name' => $viewName ) );
								if ( !$viewModel ) {
									echo 'Extended renderAllParams:showview: View ' . $viewName . ' not defined in XML';
									return false;
								}
								foreach ( $viewModel->children() as $vChild ) {
									$this->_jsif[$ifName]['show'][]		=	$this->_htmlId( $control_name, $vChild );
								}
							} elseif ( in_array( $subParam->name(), array( 'params', 'fields', 'status', 'if' ) ) ) {
								if ( count( $subParam->children() ) > 0 ) {
									if ( $subParam->name() == 'params' ) {
										$paramsName							=	$subParam->attributes( 'name' );
										if ( $control_name ) {
											$child_cnam						=	$control_name . '[' . $paramsName . ']';
										} else {
											$child_cnam						=	$paramsName;
										}
									} else {
										$child_cnam							=	$control_name;
									}
									foreach ( $subParam->children() as $vChild ) {
										if ( ! in_array( $vChild->name(), array( 'showview', 'if', 'else' ) ) ) {													//TBD	//FIXME: this avoids JS error but still shows sub-view ! recursive function needed here
											$this->_jsif[$ifName]['show'][]		=	$this->_htmlId( $child_cnam, $vChild );
										} elseif ( $vChild->name() == 'if' ) {
											foreach ( $vChild->children() as $vvChild ) {
												if ( ! in_array( $vvChild->name(), array( 'showview', 'if', 'else', 'params', 'fields', 'status' ) ) ) {													//TBD	//FIXME: this avoids JS error but still shows sub-view ! recursive function needed here
													$this->_jsif[$ifName]['show'][]		=	$this->_htmlId( $child_cnam, $vvChild );
												} elseif ( $vvChild->name() == 'if' ) {
													foreach ( $vvChild->children() as $vvvChild ) {
														if ( ! in_array( $vvvChild->name(), array( 'showview', 'if', 'else', 'params', 'fields', 'status' ) ) ) {													//TBD	//FIXME: this avoids JS error but still shows sub-view ! recursive function needed here
															$this->_jsif[$ifName]['show'][]		=	$this->_htmlId( $child_cnam, $vvvChild );
														}
													}
												}
											}
										}
									}
								}
							} elseif ( $subParam->name() == 'else' ) {
								if ( $subParam->attributes( 'action' ) == 'set' ) {
									$correspondingParam						=	$param->getAnyChildByNameAttr( 'param', 'name', $subParam->attributes( 'name' ) );
									if ( $correspondingParam ) {
										$this->_jsif[$ifName]['set'][]		=	$this->_htmlId( $control_name, $correspondingParam )
																			.	'=' . $this->control_id( $control_name, $subParam->attributes( 'name' ) )
																			.	'=' . $subParam->attributes( 'value' );
									} else {
										echo 'No corresponding param to the else statement for name ' . $subParam->attributes( 'name' ) . ' !';
									}
								}
							} else {
								$this->_jsif[$ifName]['show'][]				=	$this->_htmlId( $control_name, $subParam );
							}
						}
						$this->_jsif[$ifName]['element']					=	$param;
						$this->_jsif[$ifName]['control_name']				=	$control_name;
						$this->_jsif[$ifName]['ifname']						=	$this->_htmlId( $control_name, $param );
					}
				} elseif ( ( $ifType == 'condition' ) || ( $viewType == 'view' ) ) {
					$showInside						=	$this->_evalIf( $param );
				}
				if ( $showInside ) {
					$html[] = $this->renderAllParams( $param, $control_name, $tabs, $viewType, $htmlFormatting );
				}
				break;
			case 'else':
				break;		// implemented in if above it

			case 'toolbarmenu':
				break;		// implemented in higher level

			case 'tabpane':
				// first render all tabpanetabs (including nested tabpanes):
				$tabpaneCounter++;
				$this->tabpaneNames[$tabpaneCounter]	=	$param->attributes( 'name' );
				$subhtml					=	$this->renderAllParams( $param, $control_name, $tabs, $viewType, $htmlFormatting );
				unset( $this->tabpaneNames[$tabpaneCounter] );
				$tabpaneCounter--;
				if ( $htmlFormatting != 'fieldsListArray' ) {
					// then puts them together:
					$htid					=	$this->_outputIdEqualHtmlId( $control_name, $param );
					if ( $htmlFormatting == 'table' ) {
						$html[]				=	'<tr' . $htid . '><td colspan="3" width="100%">';
					} elseif ( $htmlFormatting == 'td' ) {
						$html[]				=	'<td' . $htid . '>';
					}
					if ( $tabpaneCounter == 0 ) {
						$html[]				=	$tabs->_getTabNavJS( $param->attributes( 'name' ), $tabNavJS );
						$tabNavJS			=	array();
					}
					$html[]					=	$tabs->startPane( $param->attributes( 'name' ) );
				}
				$html[]						=	$subhtml;
				if ( $htmlFormatting != 'fieldsListArray' ) {
					$html[]					=	$tabs->endPane();
					if ( $htmlFormatting == 'table' ) {
						$html[]				=	'</td></tr>';
					} elseif ( $htmlFormatting == 'td' ) {
						$html[]				=	'</td>';
					}
				}
				break;

			case 'tabpanetab':
				if ( $htmlFormatting != 'fieldsListArray' ) {
					$i						=	$this->_i++;
					$idtab					=	$this->tabpaneNames[$tabpaneCounter] . $this->_i;
					$html[]					=	$tabs->startTab( $this->tabpaneNames[$tabpaneCounter], CBTxt::T( getLangDefinition( $param->attributes( 'label' ) ) ), $idtab );
					$html[]					=	'<table class="paramlist" cellspacing="0" cellpadding="0" width="100%">';
	
					$tabName				=	$param->attributes( 'name' );
					$tabTitle				=	$param->attributes( 'title' );
					$description			=	$param->attributes( 'description' );
					if ( $tabTitle ) {
					    $html[]				=	'<tr><td colspan="3" width="100%"><h3' . ( $tabName ? ' class="cbTH' . $this->tabpaneNames[$tabpaneCounter] . $tabName . '"' : '' ) . '>' . CBTxt::Th( getLangDefinition( $tabTitle ) ) . '</h3></td></tr>';
					}
					if ( $description || ! $tabTitle ) {
					    $html[]				=	'<tr><td colspan="3" width="100%"><strong>' . CBTxt::Th( getLangDefinition( $description ) ) . '</strong></td></tr>';		// either description or a spacer.
					}
				}
				$html[]						=	$this->renderAllParams( $param, $control_name, $tabs, $viewType, $htmlFormatting );
				if ( $htmlFormatting != 'fieldsListArray' ) {
					$html[]					=	"\n\t</table>";
					$html[]					=	$tabs->endTab();
					$tabNavJS[$i]->nested	=	( $tabpaneCounter > 1 );
					$tabNavJS[$i]->name		=	CBTxt::T( getLangDefinition( $param->attributes( 'label' ) ) );
					$tabNavJS[$i]->id		=	$idtab;
					$tabNavJS[$i]->pluginclass	=	$idtab;
				}
				break;

			case 'extendparser':
				$this->setExtendedViewParser( $param );
				break;

			default:
				if ( $this->_extendViewParser ) {
					$html[]						=	$this->_extendViewParser->renderAllParams( $param, $control_name, $tabs, $viewType, $htmlFormatting );
				} else {
					echo 'Method to render XML view element ' . $param->name() . ' is not implemented !';
				}
				break;
		}
		return ( $htmlFormatting == 'fieldsListArray' ? $html : implode( "\n", $html ) );
	}


	/**
	* @param  CBSimpleXMLElement $param           object A param tag node
	* @param  string             $control_name    The control name
	* @param  boolean            $view            true if view only, false if editable
	* @param  string             $htmlFormatting  'table', 'fieldsListArray', etc.
	* @return array Any array of the label, the form element and the tooltip
	*/
	function renderParam( &$param, $control_name = 'params', $view = true, $htmlFormatting = 'table' ) {
		if ( $htmlFormatting == 'fieldsListArray' ) {
			return array( null, $this->control_name( $control_name, $param->attributes( 'name' ) ), null );
		}
	    $result = array();

		$name			=	$param->attributes( 'name' );
		$label			=	CBTxt::T( getLangDefinition($param->attributes( 'label' )));
		$description	=	CBTxt::T( getLangDefinition(htmlspecialchars($param->attributes( 'description' ))));

		$value = $this->get( $name, $param->attributes( 'default' ) );
		
		if ( $param->attributes( 'translate' ) == '_UE' ) {
			$value		=	getLangDefinition( $value );
		} elseif ( $param->attributes( 'translate' ) == 'yes' ) {
			$value		=	CBTxt::T( $value );
		}

		$result[0] = $label ? $label : $name;
		if ( $result[0] == '@spacer' ) {
			$result[0] = '<hr/>';
		} else if ( $result[0] ) {
			if ($name == '@spacer')	$result[0] = '<strong>'.$result[0].'</strong>';
			else $result[0] .= ':';
		}

		$result[1]	=	null;
		$type = $param->attributes( 'type' );
/* up to proof of contrary, not needed, as type="private" does it...				//TBD remove this once sure
		if ( $type == 'privateparam' ) {
			$className		= $param->attributes( 'class' );
			$methodName		= $param->attributes( 'method' );
			if ( ! $className ) {
				$className	=	$this->_parentModelOfView->attributes( 'class' );
			}
			if ( $className && $methodName && class_exists( $className ) ) {
				global $_CB_database;
				$obj = new $className( $_CB_database );
				if ( method_exists( $obj, $methodName ) ) {
					$control_name_name	=	$this->control_name( $control_name, $name );
					$result[1]	=	$obj->$methodName( $param, $control_name, $view, $name, $control_name_name, $value, $this->_pluginParams, $type );	//TBD FIXME: pluginParams should be available by the method params() of $obj, not as function parameter
				} else {
					$result[1]	=	"Missing method " . $methodName. " in class " . $className;
				}
			} else {
				$result[1]	=	"Missing class " . $className . ", and/or method " . $methodName . " in xml";
			}
		}
*/
		if ( substr( $type, 0, 4 ) == 'xml:' ) {
			$xmlType	=	substr( $type, 4 );
			if ( $this->_types ) {
				$typeModel	=	$this->_types->getChildByNameAttr( 'type', 'name' , $xmlType );
				// find root type:
				if ( $typeModel ) {
					$root		=	$typeModel;
					for ( $i = 0; $i < 100; $i++ ) {
						if ( substr( $root->attributes( 'base' ), 0, 4 ) == 'xml:' ) {
							$subbasetype	=	$root->attributes( 'base' );
							$root	=	$this->_types->getChildByNameAttr( 'type', 'name' , substr( $subbasetype, 4 ) );
							if ( ! $root ) {
								$result[1] =	"Missing type definition of " . $subbasetype;
								break;
							}
						} else {
							// we found the root and type:
							$type	=	$root->attributes( 'base' );
							break;
						}
					}
					if ( $i >= 99 ) {
						echo 'Error: recursion loop in XML type definition of ' . $o->name() . ' ' . $o->attributes( 'name' ) . ' type: ' . $o->attributes( 'type' );
						exit;
					}
					$levelModel		=	$typeModel;
					$insertAfter	=	array();
					for ( $i = 0; $i < 100; $i++ ) {
						switch ( $type ) {
							case 'list':
							case 'multilist':
							case 'radio':
							case 'checkbox':
								if ( $view ) {
									$valueNode	=	$levelModel->getAnyChildByNameAttr( 'option', 'value', $value );	// recurse in children over optgroups if needed.
									if ( $valueNode ) {
										$result[1]	=	$valueNode->data();
									}
								} else {
									if ( $levelModel->attributes( 'insertbase' ) != 'before' ) {
										foreach ( $levelModel->children() as $option ) {
											if ( $option->name() == 'option' ) {
												$param->addChildWithAttr( 'option', $option->data(), null, $option->attributes() );
											} elseif ( $option->name() == 'optgroup' ) {
												$paramOptgroup		=	$param->addChildWithAttr( 'optgroup', $option->data(), null, $option->attributes() );
												// in HTML 4, optgroups can not be nested (w3c)
												foreach ( $option->children() as $optChild ) {
													if ( $optChild->name() == 'option' ) {
														$paramOptgroup->addChildWithAttr( 'option', $optChild->data(), null, $optChild->attributes() );
													}
												}
											}
										}
									} else {
										$insertAfter[]	=	$levelModel;	
									}
								}
								break;
							default:
								if ( $view ) {
									$result[1]	=	"Unknown base type " . $type . " in XML";
								} else {
									$param->addChildWithAttr( 'option', "Unknown base type " . $type . " in XML", null, array( 'value' => '0') );
								}
								break;
						}
						if ( ( $levelModel->attributes( 'name' ) == $typeModel->attributes( 'name' ) ) && ( substr( $levelModel->attributes( 'type' ), 0, 4 ) == 'xml:' ) ) {
							$levelModel	=	$this->_types->getChildByNameAttr( 'type', 'name' , substr( $levelModel->attributes( 'type' ), 4 ) );
						} elseif ( substr( $levelModel->attributes( 'base' ), 0, 4 ) == 'xml:' ) {
							$levelModel	=	$this->_types->getChildByNameAttr( 'type', 'name' , substr( $levelModel->attributes( 'base' ), 4 ) );
						} else {
							break;
						}

					}
					foreach ( $insertAfter as $levelModel ) {
						foreach ($levelModel->children() as $option ) {
							if ( $option->name() == 'option' ) {
								$param->addChildWithAttr( 'option', $option->data(), null, $option->attributes() );
							} elseif ( $option->name() == 'optgroup' ) {
								$paramOptgroup		=	$param->addChildWithAttr( 'optgroup', $option->data(), null, $option->attributes() );
								foreach ( $option->children() as $optChild ) {
									if ( $optChild->name() == 'option' ) {
										$paramOptgroup->addChildWithAttr( 'option', $optChild->data(), null, $optChild->attributes() );
									}
								}
							}
						}
					}

				} else {
					$result[1] = "Missing type def. for param-type " .  $param->attributes( 'type' );
				}
			} else {
				$result[1] =	"No types defined in XML";
			}
		}

		if ( ! isset( $this->_methods ) ) {
			$this->_methods = get_class_methods( get_class( $this ) );
		}
		if ($result[1] ) {
			// nothing to do
		} elseif ( $this->_extendViewParser && in_array( '_form_' . $type, $this->_extendViewParser->_methods ) ) {
			$this->_view					=	$view;
			$this->_extendViewParser->_view	=	$view;
			$result[1] = call_user_func( array( &$this->_extendViewParser, '_form_' . $type ), $name, $value, $param, $control_name );
		} elseif (in_array( '_form_' . $type, $this->_methods )) {
			$this->_view					=	$view;
			$result[1] = call_user_func( array( &$this, '_form_' . $type ), $name, $value, $param, $control_name );
		} else {
		    $result[1] = _HANDLER . ' = ' . $type;
		}

		if ( $description ) {
			$result[2]	=	cbFieldTip( null, $description, $name );
		} else {
			$result[2] = '';
		}

		if ( ( ! $view ) && ( ! $result[1] ) ) {
			$result		=	array( null, null, null );
		}
		return $result;
	}
	
	function control_name( $control_name, $name ) {
		if ( $control_name ) {
			return $control_name .'['. $name .']';
		} else {
			return $name;
		}
	}
	function control_id( $control_name, $name ) {
		return moscomprofilerHTML::htmlId( $this->control_name( $control_name, $name ) );
/*
		if ( $control_name ) {
			return str_replace( array( '[', ']' ), array( '__', '' ), $control_name ) .'__'. $name;
		} else {
			return $name;
		}
*/
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_text( $name, $value, &$node, $control_name ) {
		$size		=	$node->attributes( 'size' );
		$maxlength	=	$node->attributes( 'maxlength' );
		return '<input type="text" name="'. $this->control_name( $control_name, $name ) . '" id="'. $this->control_id( $control_name, $name ) . '" value="'. htmlspecialchars($value) .'" class="inputbox" '
				. ( $size ? 'size="'. $size .'" ' : '' )
				. ( $maxlength ? 'maxlength="'. $maxlength .'" ' : '' )
				. '/>';
	}
	/**
	* Calls method or function of plugin/tab
	* 
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_custom( $name, $value, &$node, $control_name ) {
		global $_CB_database, $_PLUGINS;
		
		$pluginId	=	$this->_pluginObject->id;
		$tabId		=	$this->_tabid;
		
		$class	=	$node->attributes( 'class' );
		$method	=	$node->attributes( 'method' );
		if(!is_null($class) && strlen(trim($class)) > 0) {
			if ($pluginId !== null) {
				$params	=	null;
				if ($tabId !== null) {
					$_CB_database->setQuery( "SELECT * FROM #__comprofiler_tabs t"
					. "\n WHERE t.enabled=1 AND t.tabid = " . (int) $tabId);
					$oTabs = $_CB_database->loadObjectList();
					if (count($oTabs)>0) $params = $oTabs[0]->params;
				}
				$args = array($name,$value,$control_name);
				$_PLUGINS->plugVarValue($pluginId, "published", "1");		// need to be able to call also unpublished plugin for parametring
				return $_PLUGINS->call($pluginId,$method,$class,$args,$params);
			} else {
				$udc = new $class();
				if(method_exists($udc,$method)) {
					return call_user_func_array(array($udc,$method),array($name,$value,$control_name));
				}
			}		
		} elseif (function_exists( $method )) {
			return call_user_func_array( $method, array($name,$value,$control_name) );
		}
		return "";
			
	}


	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_list( $name, $value, &$node, $control_name ) {
		$options = array();
		
		if ( ( $node->attributes( 'blanktext' ) ) && ( ( $node->attributes( 'hideblanktext' ) != 'true' ) || ( $value == $node->attributes( 'default' ) ) ) ) {
			$options[] = moscomprofilerHTML::makeOption( $node->attributes( 'default' ), $node->attributes( 'blanktext' ) );
		}
		foreach ( $node->children() as $option ) {
			if ( $option->name() == 'option' ) {
				if ( $option->attributes( 'index' ) ) {
					$val = $option->attributes( 'index' );
				} else {
					$val = $option->attributes( 'value' );
				}
				$text = CBTxt::T( getLangDefinition($option->data()) );
				$options[] = moscomprofilerHTML::makeOption( $val, $text );
			} elseif ( $option->name() == 'optgroup' ) {
				$text = CBTxt::T( getLangDefinition( $option->attributes( 'label' ) ) );
				$options[] = moscomprofilerHTML::makeOptGroup( $text );
				foreach ( $option->children() as $optGroupOption ) {
					if ( $optGroupOption->name() == 'option' ) {
						if ( $optGroupOption->attributes( 'index' ) ) {
							$val = $optGroupOption->attributes( 'index' );
						} else {
							$val = $optGroupOption->attributes( 'value' );
						}
						$text = CBTxt::T( getLangDefinition($optGroupOption->data()) );
						$options[] = moscomprofilerHTML::makeOption( $val, $text );
					}
				}
				$options[] = moscomprofilerHTML::makeOptGroup( null );
			}
		}

		return moscomprofilerHTML::selectList( $options, ''. $this->control_name( $control_name, $name ) . '', 'class="inputbox" id="' . $this->control_id( $control_name, $name ) . '"', 'value', 'text', $value, 2 );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_radio( $name, $value, &$node, $control_name ) {
		$options = array();
		foreach ( $node->children() as $option ) {
			if ( $option->attributes( 'index' ) ) {
				$val = $option->attributes( 'index' );
			} else {
				$val = $option->attributes( 'value' );
			}
			$text = CBTxt::T( getLangDefinition($option->data()) );
			$options[] = moscomprofilerHTML::makeOption( $val, $text );
		}
		return moscomprofilerHTML::radioList( $options, ''. $this->control_name( $control_name, $name ) . '', '', 'value', 'text', $value );			//TBD missing id :  id="' . $this->control_id( $control_name, $name ) . '"
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_mos_section( $name, $value, &$node, $control_name ) {
		global $_CB_database;

		$query = "SELECT id AS value, title AS text"
		. "\n FROM #__sections"
		. "\n WHERE published = 1 AND scope = 'content'"
		. "\n ORDER BY title"
		;
		$_CB_database->setQuery( $query );
		$options = $_CB_database->loadObjectList();
		array_unshift( $options, moscomprofilerHTML::makeOption( '0', '- Select Content Section -' ) );

		return moscomprofilerHTML::selectList( $options, ''. $this->control_name( $control_name, $name ) . '', 'class="inputbox" id="' . $this->control_id( $control_name, $name ) . '"', 'value', 'text', $value, 2 );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_mos_category( $name, $value, &$node, $control_name ) {
		global $_CB_database;

		$query 	= "SELECT c.id AS value, CONCAT_WS( '/',s.title, c.title ) AS text"
		. "\n FROM #__categories AS c"
		. "\n LEFT JOIN #__sections AS s ON s.id=c.section"
		. "\n WHERE c.published = 1 AND s.scope='content'"
		. "\n ORDER BY c.title"
		;
		$_CB_database->setQuery( $query );
		$options = $_CB_database->loadObjectList();
		array_unshift( $options, moscomprofilerHTML::makeOption( '0', '- Select Content Category -' ) );

		return moscomprofilerHTML::selectList( $options, ''. $this->control_name( $control_name, $name ) . '', 'class="inputbox" id="' . $this->control_id( $control_name, $name ) . '"', 'value', 'text', $value, 2 );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_field( $name, $value, &$node, $control_name ) {
		global $_CB_database;

		$query 	=	"SELECT f.fieldid AS value, f.title AS text"
		. "\n FROM #__comprofiler_fields AS f"
		. "\n LEFT JOIN #__comprofiler_tabs AS t ON t.tabid = f.tabid"
		. "\n WHERE f.published = 1 AND f.name != 'NA'"
		. "\n ORDER BY t.ordering, f.ordering"
		;
		$_CB_database->setQuery( $query );
		$options				=	$_CB_database->loadObjectList();
		for ($i=0, $n=count($options); $i<$n; $i++) {
			$options[$i]->text	=	CBTxt::T( getLangDefinition( $options[$i]->text ) );
		}
		array_unshift( $options, moscomprofilerHTML::makeOption( '0', '- Select Field -' ) );

		return moscomprofilerHTML::selectList( $options, ''. $this->control_name( $control_name, $name ) . '', 'class="inputbox" id="' . $this->control_id( $control_name, $name ) . '"', 'value', 'text', $value, 2 );
	}
	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_mos_menu( $name, $value, &$node, $control_name ) {
		$menuTypes		=	$this->_form_mos_menu__menutypes();
	
		foreach( $menuTypes as $menutype ) {
			$options[]	=	moscomprofilerHTML::makeOption( $menutype, $menutype );
		}
		array_unshift( $options, moscomprofilerHTML::makeOption( '', '- Select Menu -' ) );

		return moscomprofilerHTML::selectList( $options, ''. $this->control_name( $control_name, $name ) . '', 'class="inputbox" id="' . $this->control_id( $control_name, $name ) . '"', 'value', 'text', $value, 2 );
	}
	function _form_mos_menu__menutypes() {
		global $_CB_database;

		$query		=	"SELECT params"
					.	"\n FROM #__modules"
					.	"\n WHERE module = 'mod_mainmenu'"
					//.	"\n ORDER BY title"
					;
		$_CB_database->setQuery( $query	);
		$modMenus	=	$_CB_database->loadObjectList();

		$query		=	"SELECT menutype"
					.	"\n FROM #__menu"
					.	"\n GROUP BY menutype"
					//.	"\n ORDER BY menutype"
					;
		$_CB_database->setQuery( $query	);
		$menuMenus	=	$_CB_database->loadResultArray();

		$menuTypes	=	array();

		foreach ( $modMenus as $modMenu ) {
			$modParams 		=	new cbParamsBase( $modMenu->params );
			$menuType 		=	$modParams->get( 'menutype' );
			if ( ! $menuType ) {
				$menuType	=	'mainmenu';
			}
			if ( ! in_array( $menuType, $menuTypes ) ) {
				$menuTypes[] =	$menuType;
			}
		}

		foreach ( $menuMenus as $menuType ) {
			if ( ! in_array( $menuType, $menuTypes ) ) {
				$menuTypes[] =	$menuType;
			}
		}

		asort( $menuTypes );
		return $menuTypes;
	}

	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_imagelist( $name, $value, &$node, $control_name ) {
		global $_CB_framework;

		// path to images directory
		$path = $_CB_framework->getCfg('absolute_path') . $node->attributes( 'directory' );
		$files = cbReadDirectory( $path, '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$' );

		$options = array();
		foreach ($files as $file) {
			$options[] = moscomprofilerHTML::makeOption( $file, $file );
		}
		if ( !$node->attributes( 'hide_none' ) ) {
			array_unshift( $options, moscomprofilerHTML::makeOption( '-1', '- Do not use an image -' ) );
		}
		if ( !$node->attributes( 'hide_default' ) ) {
			array_unshift( $options, moscomprofilerHTML::makeOption( '', '- Use Default image -' ) );
		}

		return moscomprofilerHTML::selectList( $options, ''. $this->control_name( $control_name, $name ) . '', 'class="inputbox" id="' . $this->control_id( $control_name, $name ) . '"', 'value', 'text', $value, 2 );
	}

	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_textarea( $name, $value, &$node, $control_name ) {
 		$rows 	= $node->attributes( 'rows' );
 		$cols 	= $node->attributes( 'cols' );
 		// convert <br /> tags so they are not visible when editing
 		$value 	= str_replace( array( "\\\\", '\n', '\r' ), array( "\\", "\n", "\r" ), $value );

 		return '<textarea name="'. $this->control_name( $control_name, $name ) . '" cols="'. $cols .'" rows="'. $rows .'" class="text_area" id="' . $this->control_id( $control_name, $name ) . '">'. htmlspecialchars($value) .'</textarea>';
	}

	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_spacer( $name, $value, &$node, $control_name ) {
		if ( $value ) {
			return '<strong id="' . $this->control_id( $control_name, $name ) . '">'.$value.'</strong>';
		} else {
			return '<hr id="' . $this->control_id( $control_name, $name ) . '" />';
		}
	}

	/**
	* @param string The name of the form element
	* @param string The value of the element
	* @param CBSimpleXMLElement  $node The xml element for the parameter
	* @param string The control name
	* @return string The html for the element
	*/
	function _form_usergroup( $name, $value, &$node, $control_name ) {
		$gtree = cbGetAllUsergroupsBelowMe();
	/*
		if ( ! $value ) {
			$value = $_CB_framework->acl->get_group_id('Registered','ARO');
			// array_unshift( $gtree, moscomprofilerHTML::makeOption( '0', '- Select User Group -' ) );
		}
	*/
		if ( ( $node->attributes( 'blanktext' ) ) && ( ( $node->attributes( 'hideblanktext' ) != 'true' ) || ( $value == 0 ) ) ) {
			array_unshift( $gtree, moscomprofilerHTML::makeOption( '0', $node->attributes( 'blanktext' ) ) );
		}
		$content	=	moscomprofilerHTML::selectList( $gtree, $this->control_name( $control_name, $name ), 'class="inputbox" id="' . $this->control_id( $control_name, $name ) . '" size="1"', 'value', 'text', (int) $value, 2, false );	//  size="10"
		return $content;
	}
	/**
	* special handling for textarea param
	*/
	function textareaHandling( &$txt ) {
		$total = count( $txt );
		for( $i=0; $i < $total; $i++ ) {
			if ( strstr( $txt[$i], "\n" ) ) {
				$txt[$i] = str_replace( array( "\\", "\n", "\r" ), array( "\\\\", '\n', '\r'  ) , $txt[$i] );
			}
		}
		$ret = implode( "\n", $txt );
		return $ret;
	}
}


/**
 * This class is EXPERIMENTAL WIP (reasearch Work In Progress)
 * It is not yet ready for use in CB API and will be developped in a probably incompatible way.
 * That's why it's licence is not yet GPL. It will be released GPL once completed, but that's not this version yet.
 * @license For CB 1.1 internal use only. Copying outside CB not permitted, as it's not yet final. Thanks.
 */
class cbDrawController {
	/** CB page navigator (and ordering)
	 *  @var cbPageNav */
	var $pageNav;
	/** @var CBSimpleXMLElement */
	var $_tableBrowserModel;
	/**  <actions> element
	 * 	@var CBSimpleXMLElement*/
	var $_actions;
	var $_options;
	var $_tableName;
	var $_search;
	var $_searchFields;
	var $_filters;
	var $_statistics;
	var $_control_name;
	
	function cbDrawController( $tableBrowserModel, $actions, $options ) {
		$this->_tableBrowserModel	=& $tableBrowserModel;
		$this->_actions				=& $actions;
		$this->_options				=  $options;
		
		$this->_tableName			= $tableBrowserModel->attributes( 'name' );			// TBD: does this really belong here ???!
	}
	function fieldName( $fieldName ) {
		// search, toggle, idcid[], order[], subtask
		$arrayBrackets = '';
		if ( substr( $fieldName, -2 ) == '[]' ) {
			$fieldName = substr( $fieldName, 0 , -2 );
			$arrayBrackets = '[]';
		}
		return $this->_tableName . '[' . $fieldName . ']' . $arrayBrackets;
	}
	function fieldId( $fieldId, $number=null, $htmlspecs=true ) {		//TBD: htmlspecialchars....
		// id 
		return 'cb' . $this->_tableName . $fieldId . $number;
	}
	function taskName( $subTask, $htmlspecs=true ) {
		// for saveorder,  publish, unpublish, orderup, orderdown
		return $this->_options['task'];
	}
	function fieldValue( $fieldName ) {
		if ( $fieldName == 'search' ) {
			return $this->_search;
		}
		return '';
	}
	function subtaskName( $htmlspecs=true ) {
		// saveorder,  publish, unpublish, orderup, orderdown
		return $this->fieldName( 'subtask' );
	}
	function subtaskValue( $subTask, $htmlspecs=true  ) {
		return $subTask;
	}
	function setSearch( &$search, $searchFields ) {
		$this->_search			=&	$search;
		$this->_searchFields	=	$searchFields;
	}
	function hasSearchFields( ) {
		return ( $this->_searchFields == true );
	}
	function quicksearchfields() {
		$result	=	'';
		if ( $this->hasSearchFields() ) {
			if ( $this->pageNav !== null ) {
				$onchangeJs = $this->pageNav->js_limitstart(0);
			} else {
				$onchangeJs = 'cbParentForm(this).submit();';
			}
			$result =	'<input type="text" name="' . $this->fieldName( 'search' ) . '" value="' . $this->fieldValue( 'search' ) . '" class="inputbox" '
						. 'onchange="' . $onchangeJs . '"'
						. ' />';
		}
		return $result;
	}
	/**
	 * returns HTML code for the filters
	 *
	 * @param cbEditRowView $editRowView
	 * @param string        $htmlFormatting  ( 'table', 'td', 'none' )
	 * @return unknown
	 */
	function filters( &$editRowView, $htmlFormatting = 'none' ) {
		global $_CB_framework;
		$lists 						=	array();
		if ( count( $this->_filters ) > 0 ) {
			/*
			if ( $this->pageNav !== null ) {
				$onchangeJs			=	$this->pageNav->js_limitstart(0);
			} else {
				$onchangeJs			=	'cbParentForm(this).submit();';
			}
			*/
			$valueObj				=	new stdClass();
			$saveName				=	array();
			foreach ( $this->_filters as $k => $v ) {
				$valname			=	'filter_' . $v['name'];
				$valueObj->$valname	=	$v['value'];

				// $filterXml			=	$v['xml'];
				$saveName[$k]		=	$v['xml']->attributes( 'name' );
				$this->_filters[$k]['xml']->addAttribute( 'name', 'filter_' . $saveName[$k] );

				$editRowView->setSelectValues( $v['xml'], $v['selectValues'] );
			}
			
			$renderedViews			=	array();
			
			foreach ( $this->_filters as $k => $v ) {
				// <filter> tag: $v['xml']

				$viewName				=	$v['xml']->attributes( 'view' );
				
				if ( $viewName ) {
					$view			=	$this->_filters[$k]['xmlparent']->getChildByNameAttr( 'view', 'name', $viewName );
					if ( ! $view ) {
						echo 'filter view ' . $viewName . ' not defined in filters';
					}
				} else {
					$view			=	$this->_filters[$k]['xml']->getElementByPath( 'view' );
				}
				
				if ( $view ) {
					if ( ( ! $viewName ) || ! in_array( $viewName, $renderedViews ) ) {
						$lists[$k]		=	$editRowView->renderEditRowView( $view, $valueObj, $this, $this->_options, 'param', $htmlFormatting );
					}
					if ( $viewName ) {
						$renderedViews[] =	$viewName;
					}
				} else {
					$editRowView->pushModelOfData( $valueObj );
					$result			=	$editRowView->renderParam( $this->_filters[$k]['xml'], $this->control_name(), false );
					$editRowView->popModelOfData();
					$lists[$k]		=	'<div class="cbFilter">'
									. '<span class="cbLabelSpan">' . $result[0] . '</span> '
									. '<span class="cbDescrSpan">' . $result[2] . '</span>'
									. '<span class="cbFieldSpan">' . $result[1] . '</span>'
									. '</div>';
				}
			}
			foreach ( $this->_filters as $k => $v ) {
				$this->_filters[$k]['xml']->addAttribute( 'name', $saveName[$k] );
			}
			if ( count( $lists ) > 1 ) {
				$adminimagesdir		=	$_CB_framework->getCfg( 'live_site' ) . '/components/com_comprofiler/images/';
				$lists[]			=	'<div class="cbFilter"><input type="image" src="' . $adminimagesdir . '/search.gif" alt="' . _UE_SEARCH . '" align="top" style="border: 0px;" /></div>';
			}
		}
		return $lists;
	}
	function setFilters( &$filters ){
		$this->_filters = $filters;
	}
	function setStatistics( &$statsArray ) {
		$this->_statistics =& $statsArray;
	}
	function & getStatistics( ) {
		return $this->_statistics;
	}
	function control_name( ) {
		return $this->_control_name;
	}
	function setControl_name( $control_name ) {
		$this->_control_name = $control_name;
	}
	function drawUrl( $cbUri, &$sourceElem, &$data, $id, $htmlspecialchars = true, $inPage = true ) {
		global $_CB_framework;

		$ui						=	$_CB_framework->getUi();
		if ( substr( $cbUri, 0, 4 ) == 'cbo:' ) {
			$subTaskValue	=	substr( $cbUri, 4 );
			switch ( $subTaskValue ) {
				case 'newrow':
					$id	=	0;
					// fallthrough: no break on purpose.
				case 'rowedit':				//TBD this is duplicate of below
					$baseUrl	=	( ( $ui != 2 ) ? ( $inPage ? 'index.php' : 'index2.php' ) : ( $inPage ? 'index2.php' : 'index3.php' ) );
					$baseUrl	.=		'?option=' . $this->_options['option'] . '&task=' . $this->_options['task'] . '&cid=' . $this->_options['pluginid'];
					$url	= $baseUrl . '&table=' . $this->_tableBrowserModel->attributes( 'name' ) . '&action=editrow';		// below: . '&tid=' . $id;
					break;
				case 'saveorder':
				case 'editrows':
				case 'deleterows':
				case 'copyrows':
				case 'updaterows':
				case 'publish':
				case 'unpublish':
				case 'enable':
				case 'disable':
				default:
					$url	= 'javascript:cbDoListTask(this, '				// cb					//TBD: this is duplicate of pager.
					. "'" . $this->taskName( false ). "','" 				// task
					. $this->subtaskName( false ). "','" 					// subtaskName
					. $this->subtaskValue( $subTaskValue, false ) . "','" 	// subtaskValue
					. $this->fieldId( 'id', null, false ) . "'"				// fldName
					. ");";
					break;
			}

		} elseif ( substr( $cbUri, 0, 10 ) == 'cb_action:' ) {

			$actionName				=	substr( $cbUri, 10 );
			$action					=&	$this->_actions->getChildByNameAttr( 'action', 'name', $actionName );
			if ( $action ) {
				$requestNames		=	explode( ' ', $action->attributes( 'request' ) );
				$requestValues		=	explode( ' ', $action->attributes( 'action' ) );
				$parametersValues	=	explode( ' ', $action->attributes( 'parameters' ) );
				
				$baseUrl			=	( ( $ui != 2 ) ? ( $inPage ? 'index.php' : 'index2.php' ) : ( $inPage ? 'index2.php' : 'index3.php' ) );
				$baseUrl			.=	'?';
				$baseRequests		=	array( 'option' => 'option', 'task' => 'task', 'cid' => 'pluginid' );
				$urlParams			=	array();
				foreach ( $baseRequests as $breq => $breqOptionsValue ) {
					if ( ( ! ( in_array( $breq, $requestNames ) || in_array( $breq, $parametersValues ) ) ) && isset( $this->_options[$breqOptionsValue] ) ) {
						$urlParams[$breq]	=	$breq . '=' . $this->_options[$breqOptionsValue];
					}
				}

				$url		= $baseUrl;
				for ( $i = 0, $n = count( $requestNames ); $i < $n; $i++ ) {
					$urlParams[$requestNames[$i]]	=	$requestNames[$i] . '=' . $requestValues[$i];				// other parameters = paramvalues added below
				}
				$url		=	$baseUrl . implode( '&', $urlParams );
			} else {
				$url = "#action_not_defined:" . $actionName;
			}

		} else {

			$url = $cbUri;

		}

		if ( ! cbStartOfStringMatch( $url, 'javascript:' ) ) {
			// get the parameters of action/link from XML :
			$parametersNames				=	explode( ' ', $sourceElem->attributes( 'parameters' ) );
			$parametersValues				=	explode( ' ', $sourceElem->attributes( 'paramvalues' ) );
			$parametersValuesTypes			=	explode( ' ', $sourceElem->attributes( 'paramvaluestypes' ) );
	
			// add currently activated filters to the parameters:
			if ( count( $this->_filters ) > 0 ) {
				foreach ( $this->_filters as $k => $v ) {
					$filterName				=	$this->fieldName( $k );
					if ( ( $v['value'] != $v['default'] ) && ( ! in_array( $filterName, $parametersNames ) ) ) {
						$parametersNames[]	=	$filterName;
						$parametersValues[]	=	"'" . $v['value'] . "'";		//TBD: check this.
					}
				}
			}
	
			// add current search string, if any:
			$searchName						=	$this->fieldName( 'search' );
			$searchValue					=	$this->fieldValue( 'search' );
			if ( $searchValue && ( ! in_array( $searchName, $parametersNames ) ) ) {
				$parametersNames[]			=	$searchName;
				$parametersValues[]			=	"'" . $searchValue . "'";
			}
	
			// generate current action (and parameters ?) as cbprevstate
			$cbprevstate					=	array();
			foreach ( $this->_options as $req => $act ) {
				if ( $req && $act && ! in_array( $req, array( 'cbprevstate' ) ) ) {
					$cbprevstate[]			=	$req . '=' . $act;
				}
			}
			$parametersNames[]				=	'cbprevstate';
			$parametersValues[]				=	"'" . base64_encode( implode( '&', $cbprevstate ) ) . "'";
			
			// finally generate URL:
			for ( $i = 0, $n = count( $parametersNames ); $i < $n; $i++ ) {
				$nameOfVariable				=	$parametersValues[$i];
				if ( $nameOfVariable ) {

					if ( isset( $parametersValuesTypes[$i] ) && $parametersValuesTypes[$i] ) {
						if ( $parametersValuesTypes[$i] == 'sql:field' ) {
							$nameOfVariable	=	$data->$nameOfVariable;
						} else {
							// $nameOfVariable untouched
						}
					} elseif ( ( substr( $nameOfVariable, 0, 1 ) == "'" ) && ( substr( $nameOfVariable, -1 ) == "'" ) ) {
						$nameOfVariable		=	substr( $nameOfVariable, 1, -1 );
					} else {
						$nameOfVariable		=	$data->$nameOfVariable;
					}
					$url					.=	'&' . $parametersNames[$i] . '=' . urlencode( $nameOfVariable );
				}
			}
		}

		if ( $htmlspecialchars ) {
			$url							=	htmlspecialchars( $url );
		}
		return $url;
	}

	function drawPageNvigator( $positionType /* , $viewModelElement ??? */ ) {
		
	}
	function createPageNvigator( $total, $limitstart, $limit ) {
		cbimport( 'cb.pagination' );
		$this->pageNav = new cbPageNav( $total, $limitstart, $limit, array( &$this, 'fieldName' ), $this );
		$this->pageNav->setControllerView( $this );
	}
/*		$this->pageNav =& $pageNav;
		$this->filters =& $this->filters;
		$this->search  =& $this->search;
*/
}	// class cbDrawController


?>
