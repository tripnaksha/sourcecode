<?php
/**
* Precise emulation of PHP SimpleXMLElement in PHP < 5.1.3
* @version $Id:$
* @author Beat
* @copyright (C) 2007 Beat and Lightning MultiCom SA, 1009 Pully, Switzerland
* @license Lightning Proprietary. See licence. Allowed for free use within CB and for CB plugins.
*/

// Check to ensure this file is within the rest of the framework
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

if( defined('JXML_TEST_DOMIT') || ! function_exists( 'xml_parser_create' ) ) {
	global $_CB_framework;
	$domitPath = $_CB_framework->getCfg('absolute_path') . '/includes/domit/xml_domit_lite_include.php';
	if ( file_exists( $domitPath ) ) {
		require_once( $domitPath );
	} else {
		die("<font color='red'>". $_CB_framework->getCfg( 'absolute_path' ) . "/includes/domit/ does not exist! This is normal with mambo 4.5.0 and 4.6.1 and php 4 without xml parser library binded. Community Builder needs this library for handling plugins.<br />  You Must Manually do the following:<br /> 1.) create " . $_CB_framework->getCfg( 'absolute_path' ) . "/includes/domit/ directory <br /> 2.) chmod it to 777 <br /> 3.) copy corresponding content of a mambo 4.5.2 directory.</font><br /><br />\n");
	}
}

/**
 * Class to emulate precisely PHP SimpleXMLElement in PHP < 5.1.3
 *
 * @author Beat
 * @copyright Beat 2007
 * @licence allowed for free use within CB and for CB plugins
 */
class FixedSimpleXML {
	/** Attributes of this element
	* @var array of string */
	var $_attributes	=	array();

	/** The name of the element
	* @var string */
	var $_name			=	'';

	/** The data the element contains
	* @var string */
	var $_data			=	'';

	/** The parent of the element
	* @var FixedSimpleXML */
	var $_parent		=	null;

	/** Array of references to the objects of all direct children of this XML object
	* @var array of FixedSimpleXML */
	var $_children		=	array();
	
	/**
	 * Constructor, creates tree and parses XML
	 * All parameters are equivalent to PHP 5 SimpleXML, except last 2
	 *
	 * @param  string          $data
	 * @param  int             $options
	 * @param  boolean         $data_is_url
	 * @param  string          $ns
	 * @param  boolean         $is_prefix
	 * @param  string          $name         used internally to creat tree:  name
	 * @param  array           $attrs        used internally to create tree: attributes
	 * @return FixedSimpleXML
	 */
	function FixedSimpleXML( $data, $options = null, $data_is_url = false, $ns = null, $is_prefix = false, $name = null, $attrs = array() )
	{
		if ( $data ) {
			$this->_xmlHelper		=&	new SimpleXML_Helper( $this,  $data, $options, $data_is_url, $ns, $is_prefix );
		} else {
			//Make the keys of the attr array lower case, and store the value
			$this->_attributes		=	$attrs;				// array_change_key_case($attrs, CASE_LOWER);
			$this->_name			=	$name;
		}
	}
	/**
	 * Get an element in the document by / separated path
	 * or FALSE
	 *
	 * @param	string	$path	The / separated path to the element
	 * @return	CBSimpleXMLElement or FALSE
	 */
	function & getElementByPath( $path ) {
		$parts				=	explode( '/', trim($path, '/') );

		$tmp				=&	$this;
		foreach ($parts as $node) {
			$found			=	false;
			foreach ( $tmp->_children as $k => $child ) {
				if ($child->_name == $node) {
					$tmp	=&	$tmp->_children[$k];
					$found	=	true;
					break;
				}
			}
			if ( ! $found ) break;
		}
		if ( $found ) {
			return $tmp;
		} else {
			$false			=	false;
			return $false;
		}
	}

	/**
	 * Get the name of the element
	 *
	 * @return string
	 */
	function name() {
		return $this->_name;
	}

	/**
	 * Get the an attribute of the element
	 *
	 * @param  string  $attribute  The name of the attribute
	 * @return mixed   string      If an attribute is given will return the attribute if it exist.
	 *                 boolean     Null if attribute is given but doesn't exist
	 * 				   array       If no attribute is given will return the complete attributes array
	 */
	function attributes( $attribute = null ) {
		if( ! isset( $attribute ) ) {
			return $this->_attributes;
		}
		return ( isset( $this->_attributes[$attribute] ) ? $this->_attributes[$attribute] : null );
	}

	/**
	 * Get the data of the element
	 *
	 * @return string
	 */
	function data( ) {
		return $this->_data;
	}

	/**
	 * Set the data of the element
	 * WARNING: Not PHP SimpleXML compatible
	 *
	 * @param	string $data
	 * @return string
	 */
	function setData( $data ) {
		$this->_data	=	$data;
	}
	/**
	 * Get the children of the element
	 *
	 * @return array FixedSimpleXML
	 * 
	 */
	function & children( ) {
		return $this->_children;
	}

	 /**
	 * Adds an attribute to the element, override if it already exists
	 *
	 * @param string $name
	 * @param array  $attrs
	 */
	function addAttribute( $name, $value ) {
		$this->_attributes[$name]	=	$value;
	}
	/**
	 * Searches this SimpleXML node for children matching the XPath path.
	 * Implements a usefull subset of the syntax of http://www.w3.org/TR/xpath:
	 *
	 * Abreviated syntax: works:
	   * para  selects the para element children of the context node
	   * *     selects all element children of the context node
	   * para[1]		selects the first para child of the context node
	   * * / para		selects all para grandchildren of the context node ( space added around / to not break comment
	   * /doc/chapter[5]/section[2]		selects the second section of the fifth chapter of the doc
	   * chapter//para					selects the para element descendants of the chapter element children of the context node
	   * //olist/item	selects all the item elements in the same document as the context node that have an olist parent
	   * .				selects the context node
	   * .//para		selects the para element descendants of the context node
	   * ..				selects the parent of the context node
	   * para[@type="warning"]			selects all para children of the context node that have a type attribute with value warning
	   * para[@type="warning"][5]		selects the fifth para child of the context node that has a type attribute with value warning
	   * para[5][@type="warning"]		selects the fifth para child of the context node if that child has a type attribute with value warning
	   * chapter[title="Introduction"]	selects the chapter children of the context node that have one or more title children with string-value equal to Introduction
	 * DOES NOT WORK yet:
	   * //para			selects all the para descendants of the document root and thus selects all para elements in the same document as the context node
	   * //para									selects all descendant para elements that are the first para children of their parents
	   * div//para								select all para descendants of div children
	   * para[last()]							selects the last para child of the context node
	   * chapter[title]							selects the chapter children of the context node that have one or more title children
	   * employee[@secretary and @assistant]	selects all the employee children of the context node that have both a secretary attribute and an assistant attribute
	 *
	 * @param  string  $basePath
	 * @return array|boolean     array of SimpleXMLElement objects or FALSE in case of an error.
	 */
	function xpath ( $basePath ) {
		$path				=	$basePath;
	    if ( empty( $path ) ) {
	    	return array();
	    } elseif ( substr( $path , -1) === '/' ) {
			return false;			 // If ends with '/' then wrong
		}
		$nodesList			=	array( &$this );

		$recurse			=	false;
		$absoluteMode		=	( $path[0] == '/' );
		if ( $absoluteMode ) {
			$node			=	$this;
			while ( $node->_parent ) {
				$node		=	$node->_parent;
			}
			$path			=	substr( $path, 1 );
			if ( ( $path != '' ) && ( $path[0] == '/' ) ) {
				$recurse	=	true;						//TBD
				$path		=	substr( $path, 1 );
			}
			$nodesList		=	array( &$node );
		}
		$xpa				=	explode( '/', $path );
		if ( $absoluteMode && ! $recurse ) {
			$xpb			=	array_shift( $xpa );
			if ( ! in_array( $xpb, array( '*', $node->name() ) ) ) {
				return array();
			}
		}
		foreach ( $xpa as $xpb ) {
			$xpc					=	explode( '::', $xpb, 2 );
			if ( count( $xpc ) == 1 ) {
				if ( $xpb == '.' ) {
					// ./
					$nodesList		=	array( &$this );
				} elseif ( $xpb == '..' ) {
					// ../
					$new			=	array();
					foreach ( $nodesList as $n ) {
						if ( $n->_parent && ! in_array( $n->_parent, $new ) ) {
							$new[]	=	$n->_parent;
						}
					}
					$nodesList		=	$new;
				} elseif ( ( $xpb == '*' ) || ( $xpb == '' ) ) {			//TBD to check: maybe this case is included below already...
					// */ or /
					$new			=	array();
					foreach ( $nodesList as $n ) {
						foreach ( $n->children() as $nn ) {
							$new[]	=	$nn;
						}
					}
					$nodesList		=	$new;
				} else {
					// tag or tag[a] or tag[a][b]
					$matches		=	null;
					$result			=	preg_match_all( "/^([^\\[\\]]*)*?(?:\\[([^\\[\\]]*)\\])?(?:\\[([^\\[\\]]*)\\])?\$/", $xpb, $matches );
					if ( $result ) {
						// tag or tag[a] or tag[a][b]
						// $matches = array( 0 => "tag[b][c]", 1 => "tag", 2 => "b", 3 => "c" );
						if ( $matches[2][0] ) {
							$matchesB	=	null;	
							$resultB	=	preg_match_all( "/^@([^=]+)=([\"'])(.+)\\2\$/", $matches[2][0], $matchesB );
							// $matchesB = array( 0 => '@attr="val"', 1 => 'attr', 2 => '"', 3 => 'val' );
						}
						if ( $matches[3][0] ) {
							$matchesC	=	null;	
							$resultC	=	preg_match_all( "/^@([^=]+)=([\"'])(.+)\\2\$/", $matches[3][0], $matchesC );
							// $matchesC = array( 0 => '@attr="val"', 1 => 'attr', 2 => '"', 3 => 'val' );
						}
						$new			=	array();
						foreach ( $nodesList as $n ) {
							// tag , tag[1], tag[1][@attr="val"], tag[@attr="val"], tag[@attr="val"][1]  ( where tag can be '*' or '' or tag )
							$counterA		=	0;
							$counterB		=	0;
							foreach ( $n->children() as $nn ) {
								if ( in_array( $matches[1][0], array( '*', '', $nn->name() ) ) ) {
									if ( is_numeric( $matches[2][0] ) ) {
										if ( ++$counterA < $matches[2][0] ) {
											continue;
										} elseif ( $counterA > $matches[2][0] ) {
											break;
										}
									} elseif ( $matches[2][0] ) {
										if ( ! ( ( ! $resultB ) || ( $resultB && ( $nn->attributes( $matchesB[1][0] ) == $matchesB[3][0] ) ) ) ) {
											continue;
										}
									}
									// second [ ]:
									if ( is_numeric( $matches[3][0] ) ) {
										if ( ++$counterB < $matches[3][0] ) {
											continue;
										} elseif ( $counterB > $matches[3][0] ) {
											break;
										}
									} elseif ( $matches[3][0] ) {
										if ( ! ( ( ! $resultC ) || ( $resultC && ( $nn->attributes( $matchesC[1][0] ) == $matchesC[3][0] ) ) ) ) {
											continue;
										}
									}
									$new[]	=	$nn;
								}
							}
						}
						$nodesList		=	$new;
					} else {
						trigger_error( sprintf( 'Error in xpath( %s ): illegal subexpression: %s ', $basePath , $xpb), E_USER_WARNING );
					}
				}
			} else {		// ( count( $xpc ) == 2 ) {
				//TBD: for now just trigger an error:
				trigger_error( sprintf( 'Unsuported in CB xpath( %s ): unabreviated syntax subexpression: %s ', $basePath , $xpb), E_USER_WARNING );
			}
		}
		return $nodesList;
	}

	/**
	 * Adds a direct child to the element
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $nameSpace
	 * @param array  $attrs
	 * @return FixedSimpleXML the child
	 */
	function &addChildWithAttr( $name, $value, $nameSpace = null, $attrs = array() ) {
		// If there is no array already set for the tag name being added, create an empty array for it:
		if( ! isset( $this->$name ) ) {
			$this->$name	=	array();
		}

		// Create the child object itself:
		$classname			=	get_class( $this );
		$child				=&	new $classname( null, null, false, null, false, $name, $attrs );
		$child->_data		=	$value;
		// Add the parent:
		$child->_parent		=&	$this;

		// Add the reference of it to the end of an array member named for the elements name:
		$this->{$name}[]	=&	$child;

		// Add the reference to the children array member:
		$this->_children[]	=&	$child;


		return $child;
	}
	/**
	 * Remove a specific child from the tree
	 *
	 * @param FixedSimpleXML $child  the child
	 */
	function removeChild( &$child ) {
		$name = $child->name();
		for ( $i = 0, $n = count( $this->_children ); $i < $n; $i++ ) {
			if ( $this->_children[$i] == $child ) {
				unset( $this->_children[$i] );
			}
		}
		for ( $i = 0, $n = count( $this->{$name} ); $i < $n; $i++ ) {
			if ( $this->{$name}[$i] == $child ) {
				unset( $this->{$name}[$i] );
			}
		}
		$this->_children	=	array_values( $this->_children );
		$this->{$name}		=	array_values($this->{$name});
		unset( $child );
	}
	/**
	 * Adds a direct child to the element prepended as first child
	 * WARNING: Not PHP SimpleXML compatible
	 *
	 * @param string $name
	 * @param array  $attrs
	 * @return FixedSimpleXML  the child
	 */
	function & prependChild( $name, $attrs ) {
		// If there is no array already set for the tag name being added, create an empty array for it:
		if(!isset($this->$name))
			$this->$name = array();

		// Create the child object itself
		$classname = get_class( $this );
		$child = new $classname( null, null, false, null, false, $name, $attrs );

		// Add the reference of it to the end of an array member named for the elements name:
		array_unshift( $this->$name, $child );

		// Add the reference to the children array member:
		array_unshift( $this->_children, $child );

		return $child;
	}
	/**
	 * Return a well-formed XML string based on SimpleXML element
	 *
	 * @param  string  $filename  filename to write to if not returning xml
	 * @param  int     $_level    no public access: level for indentation
	 * @return string             if no $filename, otherwise null
	 */
	function asXML( $filename = null, $_level = 0 ) {
		$out = "\n".str_repeat("\t", $_level).'<'.$this->_name;

		//For each attribute, add attr="value"
		foreach($this->_attributes as $attr => $value)
			$out .= ' '.$attr.'="'.htmlspecialchars($value).'"';

		//If there are no children and it contains no data, end it off with a />
		if(empty($this->_children) && empty($this->_data))
			$out .= " />";

		else
		{
			//If there are children
			if(!empty($this->_children))
			{
				//Close off the start tag
				$out .= '>';

				//For each child, call the asXML function (this will ensure that all children are added recursively)
				foreach($this->_children as $child)
					$out .= $child->asXML( null, $_level + 1 );

				//Add the newline and indentation to go along with the close tag
				$out .= "\n".str_repeat("\t", $_level);
			}

			//If there is data, close off the start tag and add the data
			elseif(!empty($this->_data))
				$out .= '>'. htmlspecialchars($this->_data);

			//Add the end tag
			$out .= '</'.$this->_name.'>';
		}

		if ( ( $_level != 0 ) || ( $filename === null ) ) {
			return $out;
		} else {
			file_put_contents( $filename, $out );
			return null;
		}
	}
}


/**
 * Helper Class to load SimpleXMLElement in PHP < 5.1.3
 *
 * @author Beat
 * @copyright Beat 2007
 * @licence allowed for free use within CB and for CB plugins
 */
class SimpleXML_Helper
{
	/** Document element
	* @var FixedSimpleXML $document */
	var $document = null;
	/** The XML parser
	 * @var resource */
	var $_parser = null;
	/** parsing helper
	* @var array of array */
	var $_stack = array();

	/**
	 * Constructor.
	 */
	function SimpleXML_Helper( &$firstElement, $data, $options = null, $data_is_url = false, $ns = null, $is_prefix = false ) {

		if ( strlen( $data ) > 64000 ) {
			// DOMIT XML parser can be very very very memory-hungry on PHP < 5.1.3 on large files:
			if ( ( ! is_callable( 'ini_get_all' ) ) || in_array( 'memory_limit', array_keys( ini_get_all() ) ) ) {
				$memMax			=	trim( @ini_get( 'memory_limit' ) );
				if ( $memMax ) {
					$last			=	strtolower( $memMax{strlen( $memMax ) - 1} );
					switch( $last ) {
						case 'g':
							$memMax	*=	1024;
						case 'm':
							$memMax	*=	1024;
						case 'k':
							$memMax	*=	1024;
					}
					if ( $memMax < 64000000 ) {
						@ini_set( 'memory_limit', '64M' );
					}
					if ( $memMax < 96000000 ) {
						@ini_set( 'memory_limit', '96M' );
					}
					if ( $memMax < 128000000 ) {
						@ini_set( 'memory_limit', '128M' );
					}
					if ( $memMax < 196000000 ) {
						@ini_set( 'memory_limit', '196M' );
					}
				}
			}
		}

		if( defined('JXML_TEST_DOMIT') || ! function_exists( 'xml_parser_create' ) ) {

			global $_CB_framework;

			$domitPath = $_CB_framework->getCfg('absolute_path') . '/includes/domit/xml_domit_lite_include.php';
			if ( file_exists( $domitPath ) ) {
				require_once( $domitPath );
			} else {
				die("<font color='red'>". $_CB_framework->getCfg( 'absolute_path' ) . "/includes/domit/ does not exist! This is normal with mambo 4.5.0 and 4.6.1. Community Builder needs this library for handling plugins.<br />  You Must Manually do the following:<br /> 1.) create " . $_CB_framework->getCfg( 'absolute_path' ) . "/includes/domit/ directory <br /> 2.) chmod it to 777 <br /> 3.) copy corresponding content of a mambo 4.5.2 directory.</font><br /><br />\n");
			}
			
			$this->_parser = null;

		} else {
			
			//Create the parser resource and make sure both versions of PHP autodetect the format
			$this->_parser = xml_parser_create('');
	
			// check parser resource
			xml_set_object($this->_parser, $this);
			xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, 0);
	
			//Set the handlers
			xml_set_element_handler($this->_parser, '_startElement', '_endElement');
			xml_set_character_data_handler($this->_parser, '_characterData');
		}

		// set the first element
		$this->document[0]	=&	$firstElement;
/*
$mem0 = memory_get_usage();
echo "Memory: " . $mem0 ."\n";

$time = microtime(true);
*/
		// load the XML data and generate tree
		if ( $data_is_url ) {
			if ( ! $this->loadFile( $data ) ) {
				echo "XML file " . $data . " load error.";
				exit();
			}
		} else {
			if ( ! $this->loadString( $data ) ) {
				echo "XML string load error.";
				exit();
			}
		}
/*
$time2 = microtime(true) - $time;
echo "Time function calls: " . $time2 ."\n";

$mem1 = memory_get_usage();
echo "Memory used additional: " . ($mem1 - $mem0) ."\n";
$mem0 = $mem1;
*/
	}

	 /**
	 * Interprets a string of XML into an object
	 *
	 * This function will take the well-formed xml string data and return an object of class
	 * FixedSimpleXML with properties containing the data held within the xml document.
	 * If any errors occur, it returns FALSE.
	 *
	 * @param string  Well-formed xml string data
	 * @return boolean
	 */
	function loadString( $string ) {
		$this->_parse( $string );
		return true;
	}

	 /**
	 * Interprets an XML file into an object
	 *
	 * @param string  Path to xml file containing a well-formed XML document
	 * @return boolean True if successful, false if file empty
	 */
	function loadFile( $path ) {
		if ( file_exists( $path ) ) {
			//Get the XML document loaded into a variable
			$xml = trim( file_get_contents($path) );
			if ( $xml == '' ) {
				return false;
			} else {
				$this->_parse( $xml );
				unset( $xml );
				return true;
			}
		} else {
			return false;
		}
	}
	/**
	 * Returns all attributes of the DOMIT element in an array
	 *
	 * @param DOMIT_Lite_Element $element
	 * @return array of string
	 */
	function _domitGetAttributes( &$element ) {
		$attributesArray = array();
		
		//get a reference to the attributes list / named node map (don't forget the ampersand!)
		$attrList =& $element->attributes;

		if ( $attrList !== null && is_array( $attrList ) && ( count( $attrList ) > 0 ) ) {
			//iterate through the list
			foreach ($attrList as $k => $currAttr ) {
				$attributesArray[$k] = $currAttr;
			}
		}
		return $attributesArray;
	}
	/**
	 * Recursively parses XML using DOMIT
	 *
	 * @param unknown_type $element
	 */
	function _domitParse( &$element ) {
		if ( $element->nodeName != '#text' ) {
			$this->_startElement( null, $element->nodeName, $this->_domitGetAttributes( $element ) );
			if ( $element->hasChildNodes() ) {
				$myChildNodes = $element->childNodes;
				//get the total number of childNodes for the document element
				$numChildren = $element->childCount;
				//iterate through the collection
				for ($i = 0; $i < $numChildren; $i++) {
					//get a reference to the i childNode
					$currentNode = $myChildNodes[$i];
					// recurse
					$this->_domitParse( $currentNode );
				}
			}
			$this->_endElement( null, $element->nodeName );
		} else {
			$this->_characterData( null, $element->nodeValue );
		}
	}

	/**
	 * Start parsing an XML document
	 *
	 * Parses an XML document. The handlers for the configured events are called as many times as necessary.
	 *
	 * @param  string $data to parse
	 */
	function _parse($data = '') {
		if ( $this->_parser === null ) {

			$xml					=&	new DOMIT_Lite_Document();
			$success				=	$xml->parseXML( $data );

			if ($success) {
				//gets a reference to the root element of the cd collection
				$myDocumentElement	=&	$xml->documentElement;

				$this->_domitParse( $myDocumentElement );
				$this->document		=	$this->document[0];
			}

		} else {

			if ( xml_parse( $this->_parser, $data ) ) {
				$this->document		=	$this->document[0];
			} else {
				//Error handling
				$this->_handleError( xml_get_error_code( $this->_parser ),
									 xml_get_current_line_number( $this->_parser ),
									 xml_get_current_column_number( $this->_parser ) );
			}
			xml_parser_free($this->_parser);
		}
	}

	/**
	 * Handles an XML parsing error
	 *
	 * @param int $code XML Error Code
	 * @param int $line Line on which the error happened
	 * @param int $col Column on which the error happened
	 */
	function _handleError($code, $line, $col) {
		echo 'XML Parsing Error at '.$line.':'.$col.'. Error '.$code.': '.xml_error_string($code);
	}
	/**
	 * Gets the current direct parent
	 *
	 * @return FixedSimpleXML  object
	 */
	function & _getStackElement() {
		$return =& $this;
		foreach($this->_stack as $stack) {
			$return =& $return->{$stack[0]}[$stack[1]];
			// equivalent to:
			//list( $n, $k ) = $stack;
			//$return	=	$return->{$n}[$k];
		}
		return $return;
	}

	/**
	 * Handler function for the start of a tag
	 *
	 * @param resource $parser
	 * @param string $name
	 * @param array $attrs
	 */
	function _startElement( $parser, $name, $attrs = array() ) {
		//Check to see if tag is root-level
		if (count($this->_stack) == 0) {
			// start out the stack with the document tag
			$this->_stack = array( array ( 'document', 0 ) );
			$this->document[0]->_name		=	$name;
			$this->document[0]->_attributes	=	$attrs;
		} else {
			//If it isn't root level, use the stack to find the parent
			 //Get the name which points to the current direct parent, relative to $this
			$parent			=&	$this->_getStackElement();

			//Add the child
			$parent->addChildWithAttr( $name, null, null, $attrs );

			//Update the stack
			$this->_stack[]	=	array( $name, ( count( $parent->$name ) - 1 ) );
		}
	}

	/**
	 * Handler function for the end of a tag
	 *
	 * @param resource $parser
	 * @param string $name
	 */
	function _endElement( $parser, $name ) {
		//Update stack by removing the end value from it as the parent
		array_pop($this->_stack);
	}

	/**
	 * Handler function for the character data within a tag
	 *
	 * @param resource $parser
	 * @param string $data
	 */
	function _characterData( $parser, $data ) {
		//Get the reference to the current parent object
		$tag =& $this->_getStackElement();

		//Assign data to it
		$tag->_data .= $data;
	}
}

?>
