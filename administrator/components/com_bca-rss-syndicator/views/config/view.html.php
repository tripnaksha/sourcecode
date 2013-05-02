<?php

defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class BcaRssSyndicatorViewConfig extends JView
{
	function display($tpl = null)
	{
		JToolBarHelper::save();
		$configs  =& $this->get('Data');
		$text = 'Default settings';
		JToolBarHelper::title(   JText::_( 'Breast Cancer Awareness RSS Syndicator').': <small><small>[ ' . $text.' ]</small></small>', 'config.png' );
		
		$lists = array();
		
		$rssType[] = JHTML::_('select.option', '0.91','RSS 0.91');
		$rssType[] = JHTML::_('select.option', '1.0','RSS 1.0');
		$rssType[] = JHTML::_('select.option', '2.0','RSS 2.0');
		$lists['rssTypeList'] = JHTML::_('select.genericlist', $rssType, 'defaultType', 'class="inputbox"', 'value', 'text', $configs->defaultType, 'defaultType');
		
		$orderings[] = JHTML::_('select.option', 'date','Date Ascending');
		$orderings[] = JHTML::_('select.option', 'rdate','Date Descending');
		$orderings[] = JHTML::_('select.option', 'catsect','Joomla Section, Category ordering');
		$lists['orderingList'] = JHTML::_('select.genericlist', $orderings, 'orderby', 'class="inputbox"', 'value', 'text', $configs->orderby, 'orderby');
		
		$numWords[] = JHTML::_('select.option','0','All');
		for ($i=25;$i<=250;$i+=25) {
			$numWords[] = JHTML::_('select.option',$i,$i);
		}
		$lists['numWordsList'] = JHTML::_('select.genericList', $numWords, 'numWords', 'class="inputbox"','value', 'text', $configs->numWords,  'numWords');
		
		$authorformats[] = JHTML::_( 'select.option', 'NAME','Name Only');
		$authorformats[] = JHTML::_( 'select.option', 'EMAIL','Email Only');
		$authorformats[] = JHTML::_( 'select.option', 'NAME&EMAIL','Name and Email');
		$lists['renderAuthorList'] = JHTML::_('select.genericList', $authorformats, 'renderAuthorFormat', 'class="inputbox"','value', 'text',$configs->renderAuthorFormat );
		
		$renderHTML[] = JHTML::_( 'select.option', '1','Yes');
		$renderHTML[] = JHTML::_( 'select.option', '0','No');
		$lists['renderHTMLList'] =JHTML::_( 'select.genericList',$renderHTML, 'renderHTML', 'class="inputbox"','value', 'text',$configs->renderHTML );
		
		$FPItemsOnly[] = JHTML::_( 'select.option', '1','Yes');
		$FPItemsOnly[] = JHTML::_( 'select.option', '0','No');
		$lists['FPItemsOnlyList'] =JHTML::_( 'select.genericList',$FPItemsOnly, 'FPItemsOnly', 'class="inputbox"','value', 'text',$configs->FPItemsOnly );
		$this->assignRef('id', $configs->id);
		$this->assignRef('msg', $configs->msg);
		$this->assignRef('defaultType', $lists['rssTypeList']);
		$this->assignRef('count', $configs->count);
		$this->assignRef('orderby', $lists['orderingList']);
		$this->assignRef('numWords', $lists['numWordsList']);
		$this->assignRef('renderAuthorFormat', $lists['renderAuthorList']);
		$this->assignRef('renderHTML', $lists['renderHTMLList']);
		$this->assignRef('FPItemsOnly', $lists['FPItemsOnlyList']);
		$this->assignRef('cache', $configs->cache);
		$this->assignRef('imgUrl', $configs->imgUrl);
		$this->assignRef('description', $configs->description);
		parent::display($tpl);
	}
}
?>