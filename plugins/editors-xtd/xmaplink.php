<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );


class plgButtonXmapLink extends JPlugin
{

	function plgButtonXmapLink(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onDisplay($name)
	{

		global $mainframe;
		$doc 		=& JFactory::getDocument();

		$link = 'index.php?option=com_xmap&amp;task=navigator&amp;sitemap='.$this->params->get('sitemap').'&amp;tmpl=component&amp;e_name='.$name;

		JHTML::_('behavior.modal');

		$button = new JObject();
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', JText::_('Link'));
		$button->set('name', 'blank');
		$button->set('options', "{handler: 'iframe', size: {x: 570, y: 400}}");

		return $button;
	}
}