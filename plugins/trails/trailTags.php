<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');

$mainframe->registerEvent( 'onDisplayTrails', 'plgTrailTags' );


class plgTrailTags extends JPlugin
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
	function plgTrailTags(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 *
	 * @return tag html
	 */
	function onDisplayTrails($name)
	{
		// Avoid displaying the button for anything except content articles
		$option=JRequest::getVar('option');
		if (!( $option == 'com_traildisplay' || $option == 'com_routes')) {
			return true;
		}

		// Get the article ID
		$cid = JRequest::getVar( 'tview', array(0), '', 'array');
		$id = 0;
		if ( count($cid) > 0 ) {
			$id = intval($cid[0]);
		}
		if ( $id == 0) {
			$nid = JRequest::getVar( 'tview', null);
			if ( !is_null($nid) ) {
				$id = intval($nid);
			}
		}

		require_once JPATH_BASE.DS.'components'.DS.'com_tag'.DS.'helper'.DS.'helper.php';

		$lang = & JFactory::getLanguage();
		$lang->load('com_tag', JPATH_SITE);

		$query='select t.id,t.name,t.hits from #__tag_term as t left join #__tag_term_content as c  on c.tid=t.id where c.cid='.$id.' and c.component = "com_traildisplay" order by t.weight desc,t.name';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$terms= $db->loadObjectList();
		$SuppresseSingleTerms=JoomlaTagsHelper::param('SuppresseSingleTerms');
		$HitsNumber=JoomlaTagsHelper::param('HitsNumber');
		$document =& JFactory::getDocument();
		$document->addStyleSheet(JURI::base() . 'components/com_tag/css/tagcloud.css');
		$havingTags=false;
		$links='';
		$link='';
		$maxTagsNumber=JoomlaTagsHelper::param('MaxTagsNumber',10);
		$currentNumber=0;
		if(isset($terms)&&!empty($terms)){
			$haveValidTags=false;
			foreach($terms as $term){
				if($SuppresseSingleTerms){
					$countQuery='select count(cid) as ct from jos_tag_term_content where tid='.$term->id;
					$db->setQuery($countQuery);
					$ct=$db->loadResult();
					if(@intval($ct)<=1){
						continue;
					}
					//do some specail filters.
				}
				if($currentNumber>=$maxTagsNumber){
					break;
				}
				$currentNumber++;
				$link='index.php?option=com_tag&task=tag&tag='.urlencode($term->name);
				$link=JRoute::_($link);
			
				$term->name=JoomlaTagsHelper::ucwords($term->name);
				if($HitsNumber){
					$links.='<li><a href="'.$link.'" rel="tag" title="'.$term->name.';Hits:'.$term->hits.'" >'.$term->name.'</a></li>';
				}else{
					$links.='<li><a href="'.$link.'" rel="tag" title="'.$term->name.'" >'.$term->name.'</a></li>';
				}
				$havingTags=true;
			}
			//$article->text.='<div class="tag">Tags:<ul>'.$links.'</ul></div>';
			if($havingTags){
				$tagResult='<div class="clearfix"></div><div class="tag">'.JText::_('TAGS:').'<ul>'.$links.'</ul></div>';
			}
		return $tagResult;
		}
	}
}
?>
