<?php
/**
* OptimizeTables - System plugin for daily database table optimizing
* @version 2.0
* @copyright (C) 2005-2009 by Joomlaportal.ru - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL.
* OptimizeTables is free software.
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemOptimizeTables extends JPlugin
{
	function plgSystemOptimizeTables(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onAfterInitialise()
	{
		global $mainframe;

		$currentTime = time();
		$tomorrowDate = date('Y-m-d', time());

	        $time = $this->params->get('time', '00:00:00');
	        $nextOptimization = $this->params->get('nextOptimization', $tomorrowDate . ' ' . $time);

	        $nextOptimizationTime = strtotime($nextOptimization);

		if ($nextOptimizationTime < $currentTime) {
			$dbo = JFactory::getDBO();
			$dbo->setQuery("SHOW TABLES FROM `" . $mainframe->getCfg('db') . "`" );
			$tables = $dbo->loadResultArray();

			$dbo->setQuery("OPTIMIZE TABLE `" . implode("` , `",$tables) . "`;" );
			$dbo->query();

			$nextOptimization = date('Y-m-d H:i:s', $nextOptimizationTime + 86400);

			$query = "UPDATE #__plugins"
				. " SET params = 'time=" . $time . "\nnextOptimization=" . $nextOptimization . "\n'"
				. " WHERE folder = 'system'"
				. "   AND element = 'optimizetables'"
				;
			$dbo->setQuery($query);
			$dbo->query();

		}
	}
}
?>