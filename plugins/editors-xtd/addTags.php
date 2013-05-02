<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');


class plgButtonAddTags extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param       object $subject The object to observe
	 * @param       array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgAddTags(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Add Attachment button
	 *
	 * @return a button
	 */
	function onDisplay($name)
	{
		// Avoid displaying the button for anything except content articles
		$option=JRequest::getVar('option');
//		if ( $option != 'com_content' ) {
		if (!( $option == 'com_contentsubmit' || $option == 'com_content' || $option == 'com_traildisplay' || $option == 'com_eventlist')) {

			return new JObject();
		}

		// Get the article ID
		$cid = JRequest::getVar( 'cid', array(0), '', 'array');
		$id = 0;
		if ( count($cid) > 0 ) {
			$id = intval($cid[0]);
		}
		if ( $id == 0) {
			$nid = JRequest::getVar( 'id', null);
			if ( !is_null($nid) ) {
				$id = intval($nid);
			}
		}

		// Create the button object
		$button = new JObject();


		// Figure out where we are and construct the right link and set
		// up the style sheet (to get the visual for the button working)
		global $mainframe;
		$doc =& JFactory::getDocument();
		$document = & JFactory::getDocument();
		if ( $mainframe->isAdmin() ) {
			$document->addStyleSheet(JURI::base() . 'components/com_tag/css/tag.css');

			if ( $id == 0 ) {
				$button->set('options', "{handler: 'iframe', size: {x: 400, y: 300}}");
				$link = "index.php?option=com_tag&controller=tag&task=warning&tmpl=component&tagsWarning=FIRST_SAVE_WARNING";
			}
			else {
				$button->set('options', "{handler: 'iframe', size: {x: 500, y: 300}}");
				$link = "index.php?option=com_tag&controller=tag&task=add&article_id=".$id."&tmpl=component";
			}
		}
		else {
			$document->addStyleSheet(JURI::base() . 'components/com_tag/css/tagcloud.css');
				
			//return $button;
			if ( $id == 0 ) {
				$button->set('options', "{handler: 'iframe', size: {x: 400, y: 300}}");
				$msg = JText::_('SAVE ARTICLE BEFORE ADD TAGS');
				$link = "index.php?option=com_tag&task=warning&tmpl=component&tagsWarning=FIRST_SAVE_WARNING";
			}
			else {
				$button->set('options', "{handler: 'iframe', size: {x: 500, y: 300}}");
				$link = "index.php?option=com_tag&task=add&article_id=".$id;
			}
		}

		//JRequest::setVar('tagsWarning','FIRST_SAVE_WARNING');

		$button->set('modal', true);
		$button->set('class', 'modal');
		$button->set('text', JText::_('Add Tags'));
		$button->set('name', 'add_Tags');
		$button->set('link', $link);
		//$button->set('image', '');

		return $button;
	}
}
?>
