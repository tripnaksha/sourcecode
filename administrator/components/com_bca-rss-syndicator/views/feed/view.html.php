<?php

defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class BcaRssSyndicatorViewFeed extends JView
{
	function display($tpl = null)
	{
		$feed =& $this->get('SData');
		$sections =& $this->get('Sections');
		$exCategories =& $this->get('ExCategories'); 
		$isNew = ($feed->id<1);
		$text = $isNew ? 'New feed':'Change feed: '. $feed->feed_name;
		
		JToolBarHelper::title(   JText::_( 'Breast Cancer Awareness RSS Syndicator').': <small><small>[ ' . $text.' ]</small></small>', 'addedit.png' );
		JToolBarHelper::apply();
		JToolBarHelper::save();
		
		$lists = array();
		
		if ($isNew)  {
			JToolBarHelper::cancel();
			$default =& $this->get('DefaultData');			
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'close' );
		}
		
		
		//rss type list
		$rssType[] = JHTML::_('select.option', '0.91','RSS 0.91');
		$rssType[] = JHTML::_('select.option', '1.0','RSS 1.0');
		$rssType[] = JHTML::_('select.option', '2.0','RSS 2.0');
		$rssType[] = JHTML::_('select.option', "MBOX","MBOX");
		$rssType[] = JHTML::_('select.option', "OPML","OPML");
		$rssType[] = JHTML::_('select.option', "ATOM","ATOM");
		$rssType[] = JHTML::_('select.option', "ATOM0.3","ATOM 0.3");
		$rssType[] = JHTML::_('select.option', "HTML","HTML");
		$rssType[] = JHTML::_('select.option', "JS","JS");
		$lists['rssTypeList'] = JHTML::_('select.genericlist', $rssType, 'feed_type', 'class="inputbox"', 'value', 'text', $isNew ? $default->defaultType : $feed->feed_type, 'feed_type');

		$fulltext[] = JHTML::_('select.option', "0","Do nothing");
		$fulltext[] = JHTML::_('select.option', "1","Read more link");
		$fulltext[] = JHTML::_('select.option', "2","Add to intro text");
		$lists['fulltextlist'] = JHTML::_('select.genericlist', $fulltext, 'msg_fulltext', 'class="inputbox"', 'value', 'text',  $isNew ? '1': $feed->msg_fulltext );
		
		$orderings[] = JHTML::_('select.option', 'date','Date Ascending');
		$orderings[] = JHTML::_('select.option', 'rdate','Date Descending');
		$orderings[] = JHTML::_('select.option', 'catsect','Joomla Section, Category ordering');
		$orderings[] = JHTML::_('select.option', "artord","Joomla Article ordering");
		$lists['orderingList'] = JHTML::_('select.genericlist', $orderings, 'msg_orderby', 'class="inputbox"', 'value', 'text', $isNew ? $default->orderby : $feed->msg_orderby, 'msg_orderby');
		
		$numWords[] = JHTML::_('select.option','0','All');
		for ($i=25;$i<=250;$i+=25) {
			$numWords[] = JHTML::_('select.option',$i,$i);
		}
		$lists['numWordsList'] = JHTML::_('select.genericList', $numWords, 'msg_numWords', 'class="inputbox"','value', 'text', $isNew ? $default->numWords : $feed->msg_numWords, 'msg_numWords' );
		
		$authorformats[] = JHTML::_( 'select.option', 'NAME','Name Only');
		$authorformats[] = JHTML::_( 'select.option', 'EMAIL','Email Only');
		$authorformats[] = JHTML::_( 'select.option', 'NAME&EMAIL','Name and Email');
		$lists['renderAuthorList'] = JHTML::_('select.genericList', $authorformats, 'feed_renderAuthorFormat', 'class="inputbox"','value', 'text', $isNew ? $default->renderAuthorFormat : $feed->feed_renderAuthorFormat, 'feed_renderAuthorFormat' );
		
		$renderHTML[] = JHTML::_( 'select.option', '1','Yes');
		$renderHTML[] = JHTML::_( 'select.option', '0','No');
		$lists['renderHTMLList'] =JHTML::_( 'select.genericList',$renderHTML, 'feed_renderHTML', 'class="inputbox"','value', 'text', $isNew ? $default->renderHTML : $feed->feed_renderHTML , 'feed_renderHTML');
		
		$FPItemsOnly[] = JHTML::_( 'select.option', '1','Yes');
		$FPItemsOnly[] = JHTML::_( 'select.option', '0','No');
		$lists['FPItemsOnlyList'] =JHTML::_( 'select.genericList',$FPItemsOnly, 'msg_FPItemsOnly', 'class="inputbox"','value', 'text',$isNew ? $default->FPItemsOnly : $feed->msg_FPItemsOnly, 'msg_FPItemsOnly' );
		
		$renderImages[]   = JHTML::_( 'select.option', "1","Yes");
		$renderImages[]   = JHTML::_( 'select.option', "0","No");
		$lists['renderImagesList'] = JHTML::_( 'select.genericList', $renderImages, 'feed_renderImages', 'class="inputbox"','value', 'text',$isNew ? '1' : $feed->feed_renderImages );
	
		$renderPubl[] = JHTML::_( 'select.option', "1","Yes");
		$renderPubl[] = JHTML::_( 'select.option', "0","No");
		$lists['renderPublishedList'] = JHTML::_( 'select.genericList', $renderPubl, 'published', 'class="inputbox"','value', 'text',$isNew ? NULL : $feed->published);
		
		//Section list
		$sectOptions[] = JHTML::_('select.option', "","All sections");
		$sectOptions[] = JHTML::_('select.option', "0","Uncategorised");		
		foreach($sections as $section)
		{
			$sectOptions[] = JHTML::_('select.option', $section->id,$section->title);			
		}
		
		if($isNew)
			$sectSelected = '';
		else
			$sectSelected = explode(',',$feed->msg_sectlist);
		
		$lists['sectionlist'] = JHTML::_( 'select.genericList',$sectOptions, 'msg_sectlist' . '[]', ' class="inputbox"  multiple="true"', 'value', 'text', $sectSelected );
		//Excluded categories
		if($isNew)
			$exCatSelected = '';
		else
			$exCatSelected = explode(',',$feed->msg_excatlist);			
			
		$exCatOptions[] = JHTML::_('select.option', '','No exclusion');
		$exCatOptions[] = JHTML::_('select.option', "0","Uncategorised");
		foreach($exCategories as $exCategory)
		{
			$exCatOptions[] = JHTML::_('select.option', $exCategory->id,$exCategory->title);
		}
		
		$lists['excludedcatlist'] = JHTML::_( 'select.genericList', $exCatOptions, 'msg_excatlist' . '[]', 'class="inputbox"  multiple="true"', 'value', 'text', $exCatSelected );
		
		//Feedbutton images uit de directory laden
		$button_path = JPATH_ROOT .DS. "components".DS."com_bca-rss-syndicator".DS."assets".DS."images".DS."buttons";
		$dir = @opendir($button_path);
		$button_images = array();
		$button_col_count = 0;

		while( $file = @readdir($dir) )
		{
			if( $file != '.' && $file != '..' && is_file($button_path . '/' . $file) && !is_link($button_path . '/' . $file) )
			{
				if( preg_match('/(\.gif$|\.png$|\.jpg|\.jpeg)$/is', $file) )
				{
				   $button_images[$button_col_count] = $file;
				   $button_name[$button_col_count] = ucfirst(str_replace("_", " ", preg_replace('/^(.*)\..*$/', '\1', $file)));
				   $buttons[] = JHTML::_( 'select.option', $button_images[$button_col_count], $button_name[$button_col_count]);
				   $button_col_count++;				
				}
			}
		}
		@closedir($dir);
		$lists['feedButtons'] = JHTML::_( 'select.genericList', $buttons, 'feed_button', 'onchange="loadButton(this)" class="inputbox" ','value', 'text',$isNew ? 'rss20.gif' : $feed->feed_button);
		
		//Editor
		$editor  =& JFactory::getEditor();	
		
		$this->assignRef('id', $feed->id);
		$this->assignRef('name', $feed->feed_name);
		$this->assignRef('count', $feed->msg_count = $isNew? $default->count:$feed->msg_count);
		$this->assignRef('cache', $feed->feed_cache = $isNew? $default->cache:$feed->feed_cache);
		$this->assignRef('imgUrl', $feed->feed_imgUrl);
		$isNew? $feed->feed_button='rss20.gif':$feed->feed_button;
		$this->assignRef('BtnImgUrl', $feed->feed_button);
		$this->assignRef('exitems', $feed->msg_exitems);
		$this->assignRef('description', $feed->feed_description = $isNew ? $default->description : $feed->feed_description);
		$this->assignRef('editor', $editor);
		$this->assignRef('lists', $lists);
		parent::display($tpl);
	}
}
?>