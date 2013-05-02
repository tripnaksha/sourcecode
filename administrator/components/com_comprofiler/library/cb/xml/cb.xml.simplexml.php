<?php
/**
* Abstraction class for PHP SimpleXMLElement for PHP 4 and 5, including < 5.1.3
* @version $Id:$
* @author Beat
* @copyright (C) 2007 Beat and Lightning MultiCom SA, 1009 Pully, Switzerland
* @license Lightning Proprietary. See licence. Allowed for free use within CB and for CB plugins.
*/

// Check to ensure this file is within the rest of the framework
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

//define('CBXML_TEST_CBXML','');
//define('JXML_TEST_DOMIT', '');

define('CB_PHP_XML', class_exists( 'SimpleXMLElement' ) && ( version_compare( phpversion(), '5.1.3', '>=' ) ) && ( ! @ini_get( 'zend.ze1_compatibility_mode' ) ) && ! defined('CBXML_TEST_CBXML') );

if ( CB_PHP_XML ) {
	cbimport( 'cb.xml.xml' );
} else {
	cbimport( 'cb.xml.domit' );
}

/**
 * SimpleXML Element extended for CB.
 *
 */
class CBSimpleXMLElement extends FixedSimpleXML {
	/**
	 * Get the first child element in matching all the attiributes $attributes
	 *
	 * @param   string	$name         The name tag of the element searched
	 * @param   array   $attributes   array of attribute => value which must match also
	 * @return  CBSimpleXMLElement  or false if no child matches
	 */
	function &getChildByNameAttributes( $name, $attributes = array() ) {
		foreach ( $this->children() as $child ) {
			if ( $child->name() == $name ) {
				$found = true;
				foreach ( $attributes as $atr => $val ) {
					if ( $child->attributes( $atr ) != $val ) {
						$found = false;
						break;
					}
				}
				if ( $found ) {
					return $child;
				}
			}
		}
		$false	= false;
		return $false;
	}
	/**
	 * Get the first child element in matching the attiribute
	 *
	 * @param   string	$name         The name tag of the element searched
	 * @param   string  $attribute    Attribute name to check
	 * @param   string  $value        Attribute value which must also match
	 * @return	CBSimpleXMLElement  or false if no child matches
	 */
	function &getChildByNameAttr( $name, $attribute, $value = null ) {
		foreach ( $this->children() as $child ) {
			if ( $child->name() == $name ) {
					if ( $child->attributes( $attribute ) == $value ) {
						return $child;
					}
			}
		}
		$false	= false;
		return $false;
	}
	/**
	 * Get the first child or childs' child (recursing) element in matching the attiribute
	 *
	 * @param   string	$name         The name tag of the element searched
	 * @param   string  $attribute    Attribute name to check
	 * @param   string  $value        Attribute value which must also match
	 * @return	CBSimpleXMLElement  or false if no child matches
	 */
	function &getAnyChildByNameAttr( $name, $attribute, $value = null ) {
		$children				=	$this->children();			// this is needed due to a bug in PHP 4.4.2 where you can have only 1 iterator per array reference, so doing second iteration on same array within first iteration kills this.
		foreach ( $children as $child ) {
			if ( $child->name() == $name ) {
					if ( $child->attributes( $attribute ) == $value ) {
						return $child;
					}
			}
			if ( count( $child->children() ) > 0 ) {
				$grandchild		=	$child->getAnyChildByNameAttr( $name, $attribute, $value );	// recurse
				if ( $grandchild ) {
					return $grandchild;
				}
			}
		}
		$false					=	false;
		return $false;
	}
	/* THIS MOVED ONE LEVEL DOWN TO PHP-specific implementations !!!
	 *
	 * Get an element in the document by / separated path
	 * or FALSE
	 *
	 * @param	string	$path	The / separated path to the element
	 * @return	CBSimpleXMLElement or FALSE
	 */
	// function & getElementByPath( $path ) {

}



?>
