<?php
/**
 * @version		$Id: view.html.php 10498 2008-07-04 00:05:36Z ian $
 * @package		Joomla
 * @subpackage	Weblinks
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class SearchViewAjax extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'search.php' );

		$error	= '';
		$rows	= null;

		// Get some data from the model
		$areas      = &$this->get('areas');
		$state 		= &$this->get('state');
		$searchword = $state->get('keyword');
		
		//params
		$params_module = new JParameter(SearchModelAjax::getParams());

		//sanatise searchword
		if(SearchHelper::santiseSearchWord($searchword, $state->get('match'))) {
			$error = JText::_( 'IGNOREKEYWORD' );
		}


		// put the filtered results back into the model
		// for next release, the checks should be done in the model perhaps...
		$state->set('keyword', $searchword);

		if(!$error)
		{
			$results	= &$this->get('data' );

			require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

			for ($i=0; $i < count($results); $i++)
			{
				$row = &$results[$i]->text;

				
				$searchwords = preg_split("/\s+/", $searchword);
				$needle = $searchwords[0];
		
				$row = SearchHelper::prepareSearchContent( $row, 200, $needle );
				$searchwords = array_unique( $searchwords );
				$searchRegex = '#(';
				$x = 0;
				foreach ($searchwords as $k => $hlword)
				{
					$searchRegex .= ($x == 0 ? '' : '|');
					$searchRegex .= preg_quote($hlword, '#');
					$x++;
				}
				$searchRegex .= ')#iu';

				$result =& $results[$i];

			    $result->count		= $i + 1;
			}
		}
		
		$this->assignRef('results',		$results);
		$this->assignRef('array_params',	$params_module);
		$this->assign('searchword',		$searchword);
		
		parent::display($tpl);
	}
}

?>