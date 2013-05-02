<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2006-2010 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @version $Id: log.php 196 2010-07-25 13:39:31Z nikosdion $
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

/**
 * The Control Panel model
 *
 */
class AkeebaModelLog extends JModel
{
	function getLogFiles()
	{
		$configuration = AEFactory::getConfiguration();
		$outdir = $configuration->get('akeeba.basic.output_directory');

		$files = AEUtilScanner::getFiles($outdir);
		$ret = array();
		if(!empty($files) && is_array($files))
		{
			foreach($files as $filename)
			{
				$basename = basename($filename);
				if( (substr($basename,0,7) == 'akeeba.') && (substr($basename,-4) == '.log') && ($basename != 'akeeba.log') )
				{
					$tag = str_replace('akeeba.', '', str_replace('.log', '', $basename));
					if(!empty($tag)) $ret[] = $tag;
				}
			}
		}
		return $ret;
	}

	function getLogList()
	{
		$options = array();

		$list = $this->getLogFiles();
		if(!empty($list))
		{
			$options[] = JHTML::_('select.option',null,JText::_('LOG_CHOOSE_FILE_VALUE'));
			foreach($list as $item)
			{
				$text = JText::_('STATS_LABEL_ORIGIN_'.strtoupper($item));
				$options[] = JHTML::_('select.option',$item,$text);
			}
		}
		return $options;
	}
}