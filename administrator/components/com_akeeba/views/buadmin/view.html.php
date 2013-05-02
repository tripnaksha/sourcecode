<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: view.html.php 204 2010-08-04 15:47:10Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * Akeeba Backup Administrator view class
 *
 */
class AkeebaViewBuadmin extends JView
{
	public function display()
	{
		$task = JRequest::getCmd('task','default');

		switch($task)
		{
			case 'showcomment':
				JToolBarHelper::title(JText::_('AKEEBA').': <small>'.JText::_('BUADMIN').'</small>','akeeba');
				JToolBarHelper::back('Back', 'index.php?option='.JRequest::getCmd('option').'&view=buadmin');
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				$document =& JFactory::getDocument();
				$document->addStyleSheet(JURI::base().'../media/com_akeeba/theme/akeebaui.css');

				$id = JRequest::getInt('id',0);
				$record = AEPlatform::get_statistics($id);
				$this->assign('record', $record);
				$this->assign('record_id', $id);

				JRequest::setVar('tpl','comment');
				break;

			default:
				$registry =& AEFactory::getConfiguration();

				JToolBarHelper::title(JText::_('AKEEBA').': <small>'.JText::_('BUADMIN').'</small>','akeeba');

				JToolBarHelper::back('Back', 'index.php?option='.JRequest::getCmd('option'));
				JToolBarHelper::spacer();
				JToolBarHelper::deleteList();
				JToolBarHelper::custom( 'deletefiles', 'delete.png', 'delete_f2.png', JText::_('STATS_LABEL_DELETEFILES'), true );

				if(AKEEBA_PRO)
				{
					JToolBarHelper::publish('restore', JText::_('STATS_LABEL_RESTORE'));
				}

				// "Show warning first" download button. Joomlantastic!
				$confirmationText = AkeebaHelperEscape::escapeJS( JText::_('STATS_LOG_DOWNLOAD_CONFIRM'), "'\n" );
				$baseURI = JURI::base();
				$js = <<<ENDSCRIPT
function confirmDownloadButton()
{
	var answer = confirm('$confirmationText');
	if(answer) submitbutton('download');
}

function confirmDownload(id, part)
{
	var answer = confirm('$confirmationText');
	var newURL = '$baseURI';
	if(answer) {
		newURL += 'index.php?option=com_akeeba&view=buadmin&task=download&id='+id;
		if( part != '' ) newURL += '&part=' + part
		window.location = newURL;
	}
}

ENDSCRIPT;

				$document =& JFactory::getDocument();
				$document->addScriptDeclaration($js);
				$bar = & JToolBar::getInstance('toolbar');
				$bar->appendButton( 'link', 'save', JText::_('STATS_LOG_DOWNLOAD'), "javascript:confirmDownloadButton();" );

				JToolBarHelper::editList('showcomment', JText::_('STATS_LOG_EDITCOMMENT'));
				JToolBarHelper::spacer();

				$document =& JFactory::getDocument();
				$document->addStyleSheet(JURI::base().'../media/com_akeeba/theme/akeebaui.css');

				require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'statistics.php';
				$model = new AkeebaModelStatistics();
				$list =& $model->getStatisticsListWithMeta();

				$this->assignRef('list', $list);
				$this->assignRef('pagination', $model->getPagination());
				break;
		}

		// Add live help
		AkeebaHelperIncludes::addHelp();

		parent::display(JRequest::getVar('tpl'));
	}
}