<?php


defined( '_JEXEC' ) or die( 'Restricted access' );

class BcaRssSyndicatorViewAbout
{

	function __construct()
	{
		$text = 'About Breast Cancer Awareness RSS Syndicator';
		JToolBarHelper::title(   JText::_( 'Breast Cancer Awareness RSS Syndicator').': <small><small>[ ' . $text.' ]</small></small>', 'systeminfo.png' );
		$this->about();
	}
	
	function about()
	{
		?>
			<div class="m">
							<p class="sectionname" align="center"><img src="components/com_bca-rss-syndicator/assets/images/bca-rss-syndicator.jpg"></p>
			<p align="left">
			The Breast Cancer Awareness RSS Syndicator component makes your content available in different RSS, <a href="http://www.opml.org/" target="_blank">OPML</a> and <a href="http://www.atomenabled.org/" target="_blank">ATOM 0.3</a> formats.
			</p>
			<p align="left">If you need help with configuring this component and module you can find documentation at the <a href="http://www.bodyhealthdebate.co.uk/breast-cancer-awareness-rss-syndicator-joomla" target="_blank">Body Health Debate</a> website.</p>
			<p align="left">The Breast Cancer Awareness RSS Syndicator component and module were developed by Body Health Debate to raise awareness of breast cancer in the Joomla Community.</p>
			<p align="left"><strong>License</strong></p>
			<p align="left">The Breast Cancer Awareness RSS Syndicator is free software; you can redistribute it and/or modify it under the terms of the <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">General Public License</a> as published by the Free Software Foundation. </p>
			<p align="left"><strong>Resources</strong></p>

			<p align="left">The Breast Cancer Awareness RSS Syndicator is based upon the Joomla 1.0 DS-Syndicate component, and was converted to Joomla 1.5 by XiPat</p>
			<p align="left">The Breast Cancer Awareness RSS Syndicator makes use of the free <a href="http://www.bitfolge.de/rsscreator-en.html" target="_blank">FeedCreator class</a> by Kai Blankenhorn and the excellent XMLRPC Library from <a href="http://scripts.incutio.com/xmlrpc/" target="_blank">Incutio</a>. The Button Maker code was a creation of <a href="http://minimalverbosity.com/2003/May/19/buttons.htm" target="_blank">Bill Zeller</a> with an user interface from <a href="http://www.kalsey.com/tools/buttonmaker/" target="_blank">here</a>. The implementation of the MetaWeblog and blogger APIs were implemented by <a href="http://www.vd-tools.com" target="_blank">Gayle Davies</a> with help from code from NucleusCMS. My changes were to refactor the code.</p>

			<p align="left">Copyright 2008, <a href="http://www.bodyhealthdebate.co.uk" target="_blank">Body Health Debate</a>.</p>
				<div class="clr"></div>
			</div>
		<?php
	}

}
?>
