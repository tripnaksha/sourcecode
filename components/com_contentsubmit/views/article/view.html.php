<?php
/**
 * @version		$Id: view.html.php 10094 2008-03-02 04:35:10Z instance $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );?>

<script language="javascript" type="text/javascript">
	function createCookie( name, value, days) {
		var expires = "";
		if (days)
		{
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			expires = "; expires="+date.toGMTString();
		}
		document.cookie = name+"="+value+expires+"; path=/";
	};

	window.addEvent('domready',function(){
		createCookie("trailrev",0,-1);
		createCookie("time",0,-1);
		createCookie("equipment",0,-1);
		createCookie("difficulty",0,-1);

		if ($('catid').getValue() != 4) 
		{
		  $('ltrail').style.visibility = 'hidden';
		  $('trailid').style.visibility = 'hidden';
		  $('ldifficulty').style.visibility = 'hidden';
		  $('difficulty').style.visibility = 'hidden';
		  $('ltime').style.visibility = 'hidden';
		  $('time').style.visibility = 'hidden';
		  $('lequipment').style.visibility = 'hidden';
		  $('equipment').style.visibility = 'hidden';
		}
		// When the select list with trailid is changed, set cookies which are used by AfterContentSave plugin
		$('trailid').addEvent('change',function(){
			if ($('catid').getValue() == 4)
			{
			   createCookie('trailid',this.getValue(),0);
			   createCookie('trailrev',this.getValue(),0);
			}
		});
		$('difficulty').addEvent('change',function(){
			if ($('catid').getValue() == 4)
			   createCookie('difficulty',this.getValue(),0);
		});
		$('time').addEvent('change',function(){
			if ($('catid').getValue() == 4)
			   createCookie('time',this.getValue(),0);
		});
		$('equipment').addEvent('change',function(){
			if ($('catid').getValue() == 4)
			   createCookie('equipment',this.getValue(),0);
		});
		// When the category list is changed to trail review, set cookies which are used by AfterContentSave plugin
		$('catid').addEvent('change',function(){
			if ($('catid').getValue() == 4)
			{
			  $('ltrail').style.visibility = 'visible';
			  $('trailid').style.visibility = 'visible';
			  $('ldifficulty').style.visibility = 'visible';
			  $('difficulty').style.visibility = 'visible';
			  $('ltime').style.visibility = 'visible';
			  $('time').style.visibility = 'visible';
			  $('lequipment').style.visibility = 'visible';
			  $('equipment').style.visibility = 'visible';
			  createCookie('trailid',$('trailid').getValue(),0);
			  createCookie('trailrev',$('trailid').getValue(),0);
			}
			else
			{
			  $('ltrail').style.visibility = 'hidden';
			  $('trailid').style.visibility = 'hidden';
			  $('ldifficulty').style.visibility = 'hidden';
			  $('difficulty').style.visibility = 'hidden';
			  $('ltime').style.visibility = 'hidden';
			  $('time').style.visibility = 'hidden';
			  $('lequipment').style.visibility = 'hidden';
			  $('equipment').style.visibility = 'hidden';
			  createCookie("trailrev",0,-1);
			}
		});
	});
</script>

<?php
jimport( 'joomla.application.component.view' );

/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentsubmitViewArticle extends JView
{

	function display($tpl=null)
	{
		global $mainframe;

		// Initialize variables
		$document	=& JFactory::getDocument();
		$user		=& JFactory::getUser();
		$uri     	 =& JFactory::getURI();

		// Make sure you are logged in and have the necessary access rights
		if ($user->get('gid') < 19) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Initialize variables
		$article	=& $this->get('Article');
		$params		=& $article->parameters;
		$isNew		= ($article->id < 1);

		// At some point in the future this will come from a request object
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		// Add the Calendar includes to the document <head> section
		//JHTML::_('behavior.calendar');

		if ($isNew)
		{
			// TODO: Do we allow non-sectioned articles from the frontend??
			$article->sectionid = JRequest::getVar('sectionid', 0, '', 'int');
			$db = JFactory::getDBO();
			$db->setQuery('SELECT title FROM #__sections WHERE id = '.(int) $article->sectionid);
			$article->section = $db->loadResult();
		}

		// Get the lists
		$lists = $this->_buildEditLists();

		// Load the JEditor object
		$editor =& JFactory::getEditor();

		// Build the page title string
		$title = $article->id ? JText::_('Edit') : JText::_('New');

		// Set page title
		$document->setTitle($title);

		// get pathway
		$pathway =& $mainframe->getPathWay();
		$pathway->addItem($title, '');

		// Unify the introtext and fulltext fields and separated the fields by the {readmore} tag
		// if (JString::strlen($article->fulltext) > 1) {
			// $article->text = $article->introtext."<hr id=\"system-readmore\" />".$article->fulltext;
		// } else {
			// $article->text = $article->introtext;
		// }

		// Ensure the row data is safe html
		// JFilterOutput::objectHTMLSafe( $article);

		$this->assign('action', 	$uri->toString());

		$this->assignRef('article',	$article);
		$this->assignRef('params',	$params);
		$this->assignRef('lists',	$lists);
		$this->assignRef('editor',	$editor);
		$this->assignRef('user',	$user);


		parent::display($tpl);
	}

	function _buildEditLists()
	{
		// Get the article and database connector from the model
		$article = & $this->get('Article');
		$db 	 = & JFactory::getDBO();
		$uri     	 =& JFactory::getURI();

		// $javascript = "onchange=\"changeDynaList( 'catid', sectioncategories, document.adminForm.sectionid.options[document.adminForm.sectionid.selectedIndex].value, 0, 0);\"";
		$javascript = "";

		// layout
		$layout	=& $this->get('Layout');
		$id	=& $this->get('Id');

		switch ($layout) {
			case "bysection":
				// ********************************
				// send over a fixed sectionid
				// ********************************
				$query = "SELECT s.id, s.title " .
						 " FROM #__sections AS s " .
						 " WHERE s.id = '".$id."'";
				$db->setQuery($query);
				$section = $db->loadObject();

				$sections[] = JHTML::_('select.option', $id, $section->title, 'id', 'title');

				$lists['sectionid'] = JHTML::_('select.genericlist',  $sections, 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', intval($id));

				// Added - Ajay - 04/02/09 - for create review from map and to assign a review to a trail.
				// *********************************************
				// send over a fixed trailid or list of trails
				// *********************************************
				$testid=1;
				$trailId = JRequest::getInt( 'trailId' );
				$query = "SELECT t.id, t.name " .
					 " FROM #__trailList AS t ";
				if ($trailId > 0)
				{
					$query .= " WHERE t.id = '".$trailId."'";
					$query .= " AND t.private = 0";
				}
				else
					$query .= " WHERE t.private = 0";
				$query .= " ORDER BY `name` ASC ";

				$db->setQuery($query);
				$cat_list = $db->loadObjectList();
				$trail_list = $db->loadObjectList();

				$trails = array();
				foreach ($trail_list as $trail)
				{
//					$trails[] = JHTML::_('select.option', $trail->id, $trail->name, 'id', 'name');
					$trails[] = JHTML::_('select.option', $trail->id, substr($trail->name, 0, 40), 'id', 'name');
				}
				if (JRequest::getInt( 'Itemid' ) == 16)
				{
				  $lists['listid'] = JHTML::_('select.genericlist',  $trails, 'trailid', 'class="inputbox" size="1"', 'id', 'name', 0);
				  if(isset($_COOKIE['trailrev']))
				     unset($_COOKIE['trailrev']);
				}

				// ********************************
				// send over the section's categories
				// ********************************
				$section_list[] = (int) $section->id;
				$contentSection = $section->title;
				$ctgId = JRequest::getInt( 'ctgid' );

				$query = " SELECT c.id, c.title, c.section " .
						 " FROM #__categories c" .
						 " WHERE `section` = '".$section->id."' ";
				// Added - Ajay - 04/02/09 - for create review from map.
				if ($ctgId > 0)
					$query .= " AND c.id = " . $ctgId;
//				$query .= " ORDER BY `title` ASC ";
				$db->setQuery($query);
				$cat_list = $db->loadObjectList();

				// Commented - Ajay - 20/01/09 - remove the option of "Uncategorized" from appearing when submitting an article
				/*// Uncategorized category mapped to uncategorized section
				$uncat = new stdClass();
				$uncat->id = 0;
				$uncat->title = JText::_('Uncategorized');
				$uncat->section = 0;
				$cat_list[] = $uncat;
				*/
				$categories = array();
				//$categories[] = JHTML::_('select.option', '-1', JText::_( 'Select Category' ), 'id', 'title');

				foreach ($cat_list as $cat)
				{
					$categories[] = JHTML::_('select.option', $cat->id, $cat->title, 'id', 'title');
				}
				$lists['catid'] = JHTML::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1"'/*.  $kavascript */ , 'id', 'title', 0);

			  break;
			case "bycategory":
				$query = "SELECT s.id AS sectionid, s.title AS sectiontitle, c.id, c.title " .
						 " FROM #__categories AS c " .
						 " LEFT JOIN #__sections as s on c.section = s.id " .
						 " WHERE c.id = '".$id."'";
				$db->setQuery($query);
				$data = $db->loadObject();

				// ********************************
				// send over a fixed sectionid
				// ********************************
				$sections[] = JHTML::_('select.option', $data->sectionid, $data->sectiontitle, 'id', 'title');
				$lists['sectionid'] = JHTML::_('select.genericlist',  $sections, 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', intval($data->sectionid));

				// ********************************
				// send over a fixed categoryid
				// ********************************
				$categories[] = JHTML::_('select.option', $data->id, $data->title, 'id', 'title');
				$lists['catid'] = JHTML::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', intval($data->id));

			  break;
		}

		// Select List: Category Ordering
		$query = 'SELECT ordering AS value, title AS text FROM #__content WHERE catid = '.(int) $article->catid.' ORDER BY ordering';
		$lists['ordering'] = JHTML::_('list.specificordering', $article, $article->id, $query, 1);

		// Radio Buttons: Should the article be published
		$lists['state'] = JHTML::_('select.booleanlist', 'state', '', $article->state);

		// Radio Buttons: Should the article be added to the frontpage
		/*if($article->id) {
			$query = 'SELECT content_id FROM #__content_frontpage WHERE content_id = '. (int) $article->id;
			$db->setQuery($query);
			$article->frontpage = $db->loadResult();
		} else {
			$article->frontpage = 0;
		}
		*/

		$lists['frontpage'] = JHTML::_('select.booleanlist', 'frontpage', '', (boolean) $article->frontpage);

		// Select List: Group Access
		// Modified - Ajay - 20/01/09 - Default access level is public.
		if($article->access == NULL) {
		   $article->access = 0;
		}
		$lists['access'] = JHTML::_('list.accesslevel', $article);

		return $lists;
	}

	function _displayPagebreak($tpl)
	{
		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('PGB ARTICLE PAGEBRK'));

		parent::display($tpl);
	}
}
?>
