<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class SEFViewImportExport extends JView
{
	function display($tpl = null)
	{
		JToolBarHelper::title( JText::_('JoomSEF URL Manager'), 'url-edit.png' );
		
		JToolBarHelper::back('Back', 'index.php?option=com_sef&controller=sefurls');
		
		$this->assign('aceSefPresent', $this->get('AceSefTablePresent'));
		$this->assign('shSefPresent', $this->get('ShTablePresent'));
		
		parent::display($tpl);
	}
	
}
