<?php
defined('_JEXEC') or die();
jimport('joomla.application.component.view');
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'components/com_login_box/assets/css/style1.css');

class LoginBoxViewLoginBox extends JView {
   function display($tmpl = null) {
	global $mainframe;
	$nums = $this->_buildRandNums();
	$this->assignRef('nums',	$nums);
        parent::display($tmpl);
   }
	function _buildRandNums()
	{
	    $nums['num1'] = rand(0,10);
	    $nums['num2'] = rand(0,10);
	    return $nums;
	}
}
?>
