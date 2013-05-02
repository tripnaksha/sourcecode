<?php
/**
 * @version $Id: plgContentTags.php 1.5
 * @copyright JoomlaTags.org
 * @license GNU/GPLv2,
 * @author http://www.joomlatags.org
 */
defined( '_JEXEC' ) or  die('Restricted access');
jimport( 'joomla.event.plugin' );

require_once JPATH_SITE.DS.'components'.DS.'com_tag'.DS.'helper'.DS.'helper.php';
require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
class plgContentTags extends JPlugin
{


	function plgContentTags( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}


	function onPrepareContent( &$article, &$params, $limitstart )
	{

		//$regex = "#{tag\s*(.*?)}(.*?){/tag}#s";
		//$article->text=preg_replace($regex,' ',$article->text);
		$app =& JFactory::getApplication();
		if($app->getName() != 'site') {
			return true;
		}
		If (isset($article)&&(!isset($article->id)||is_null($article->id))){
			return true;
		}


		$FrontPageTag=JoomlaTagsHelper::param('FrontPageTag');
		$BlogTag=JoomlaTagsHelper::param('BlogTag');
		$view=JRequest :: getVar('view');
		$layout=JRequest :: getVar('layout');
		if(( $view== 'frontpage')&&!$FrontPageTag){
			return true;
		}
		if($layout == 'blog'&&!$BlogTag){
			return true;
		}
		if(($layout != 'blog')&&($view == 'category'||$view=='section')){
			return true;
		}
		

		$lang = & JFactory::getLanguage();
		$lang->load('com_tag', JPATH_SITE);

		//select t.id as tid,t.name, count(tc.cid) as ct from jos_tag_term_content as c left join jos_tag_term as t on c.tid=t.id left join jos_tag_term_content tc on c.tid=tc.cid where c.cid=1 group by t.id,tc.cid ;

		$query='select t.id,t.name,t.hits from #__tag_term as t left join #__tag_term_content as c  on c.tid=t.id where c.cid='.$article->id.' and c.component = "com_content" order by t.weight desc,t.name';
		//echo($query);
		$db			=& JFactory::getDBO();
		$db	->setQuery($query);
		$terms= $db	->loadObjectList();
		$SuppresseSingleTerms=JoomlaTagsHelper::param('SuppresseSingleTerms');
		$HitsNumber=JoomlaTagsHelper::param('HitsNumber');
		$document =& JFactory::getDocument();
		$document->addStyleSheet(JURI::base() . 'components/com_tag/css/tagcloud.css');
		$havingTags=false;
		$links='';
		$link='';
		$maxTagsNumber=JoomlaTagsHelper::param('MaxTagsNumber',10);
		$showRelatedArticles=JoomlaTagsHelper::param('RelatedArticlesByTags',0);
		$currentNumber=0;
		$termIds=array();
		if(isset($terms)&&!empty($terms)){
			$haveValidTags=false;
			foreach($terms as $term){
				if($showRelatedArticles||$SuppresseSingleTerms){
					$countQuery='select count(cid) as ct from jos_tag_term_content where tid='.$term->id;
					$db->setQuery($countQuery);
					$ct=$db->loadResult();
						
					if(@intval($ct)<=1){
						if($SuppresseSingleTerms){
							continue;
						}
					}else{
						$termIds[]=$term->id;
					}
				}
				//do some specail filters.

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
				$position=JoomlaTagsHelper::param('TagPosition');
				if($position==1){
					$article->text=$tagResult.$article->text;
				}else if($position==2){
					$article->text=$tagResult.$article->text.$tagResult;
				}else{
					$article->text.=$tagResult;
				}
			}

		}
		$showAddTagButton=JoomlaTagsHelper::param('ShowAddTagButton');
		if($showAddTagButton){
			$user	=& JFactory::getUser();

			$canEdit=$this->canUserAddTags($user,$article->id);
			if($canEdit){
				$Itemid = JRequest::getVar( 'Itemid', false);
				if ( is_numeric($Itemid) ){
					$Itemid = intval($Itemid);
				}
				else{
					$Itemid = 1;
				}
				$article->text.=$this->addTagsButtonsHTML($article->id,$Itemid,$havingTags);
			}
		}

		if($showRelatedArticles&&!empty($termIds)&&($view=='article')){		
			$article->text.=$this->showReleatedArticlesByTags($article->id,$termIds);
		}
		return true;
	}

	function showReleatedArticlesByTags($articleId,$termIds){
		$count=JoomlaTagsHelper::param('RelatedArticlesCountByTags',10);		
		$relatedArticlesTitle=JoomlaTagsHelper::param('RelatedArticlesTitleByTags',"Related Articles");
		$max=max(intval($relatedArticlesCount),array_count_values($termIds));
		$termIds=array_slice($termIds,0,$max);
		$termIdsCondition=@implode(',',$termIds);
		//find the unique article ids
		$query=' select distinct cid from #__tag_term_content where tid in ('.$termIdsCondition.') and cid<>'.$articleId;
		$db			=& JFactory::getDBO();
		$db	->setQuery($query);

		$cids=$db->loadResultArray(0);
	
			
		$nullDate	= $db->getNullDate();
		$date =& JFactory::getDate();
		$now = $date->toMySQL();

		$where		= ' a.id in('.@implode(',',$cids).') AND a.state = 1'
		. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
		. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
		;

		// Content Items only
		$query = 'SELECT a.id,a.title, a.alias,a.access,a.sectionid, ' .
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
			' FROM #__content AS a' .
			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
			' INNER JOIN #__sections AS s ON s.id = a.sectionid' .
			' WHERE '. $where .' AND s.id > 0' .			
			' AND s.published = 1' .
			' AND cc.published = 1';
		$db->setQuery($query, 0, $count);
		$rows = $db->loadObjectList();

		if(empty($rows)){
			return '';
		}
		$user		=& JFactory::getUser();
		$aid		= $user->get('aid', 0);
		
		$html='<div class="relateditemsbytags"><h3>'.$relatedArticlesTitle.'</h3><ul class="relateditems">';
		$link;
		foreach ( $rows as $row )
		{

			if($row->access <= $aid)
			{
				$link = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catslug, $row->sectionid));
			} else {
				$link = JRoute::_('index.php?option=com_user&view=login');
			}
			$html.='<li> <a href="'.$link.'">'.htmlspecialchars( $row->title ).'</a></li>';
		}
		$html.='</ul></div>';
		return $html;

	}


	function canUserAddTags($user, $article_id)
	{
		// A user must be logged in to add attachments
		if ( $user->get('username') == '' ) {
			return false;
		}

		// If the user generally has permissions to add content, they qualify.
		// (editor, publisher, admin, etc)
		// NOTE: Exclude authors since they need to be handled separately.
		$user_type = $user->get('usertype', false);
		if ( ($user_type != 'Author') &&
		$user->authorize('com_content', 'add', 'content', 'all') ) {
			return true;
		}

		// Make sure the article is valid and load its info
		if ( $article_id == null || $article_id == '' || !is_numeric($article_id) ) {
			return false;
		}
		$db =& JFactory::getDBO();
		$query = "SELECT created_by from #__content WHERE id='" . $article_id . "'";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ( count($rows) == 0 ) {
			return false;
		}
		$created_by = $rows[0]->created_by;

		//the created author can add tags.
		if($user->get('id') == $created_by){
			return true;
		}


		// No one else is allowed to add articles
		return false;
	}
	function addTagsButtonsHTML($article_id, $Itemid, $havingTags)
	{
		$document = & JFactory::getDocument();
		$document->addScript( JURI::root(true).'/media/system/js/modal.js' );
		JHTML::_('behavior.modal', 'a.modal');

		// Generate the HTML for a  button for the user to click to get to a form to add an attachment
		$url = "index.php?option=com_tag&task=add&refresh=1&article_id=".$article_id;

		$url = JRoute::_($url);
		$icon_url = JURI::Base() . 'components/com_tag/images/logo.png';

		$add_tag_txt;
		if($havingTags){
			$add_tag_txt = JText::_('EDIT TAGS');
		}else{
			$add_tag_txt = JText::_('ADD TAGS');
		}
		$ahead = '<a class="modal" type="button" href="' . $url . '" ';
		$ahead .= "rel=\"{handler: 'iframe', size: {x: 500, y: 260}}\">";
		$links = "$ahead<img src=\"$icon_url\" /></a>";
		$links .= $ahead.$add_tag_txt."</a>";
		return "\n<div class=\"addtags\">$links</div>\n";

	}
	/**
	 * Auto extract meta keywords as tags
	 *
	 * @param $article
	 * @param $isNew
	 * @return unknown_type
	 */
	function onAfterContentSave( &$article, $isNew )
	{
		$autoMetaKeywordsExtractor=$FrontPageTag=JoomlaTagsHelper::param('autoMetaKeywordsExtractor');
		if($autoMetaKeywordsExtractor){

			if($isNew){
				$tags=$article->metakey;
				$id = $article->id;
				$combined = array();
				$combined[$id]=$tags;

				require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tag'.DS.'models'.DS.'tag.php');
				$tmodel = new TagModelTag();
				$tmodel->batchUpdate($combined);
			}
		}

		return true;
	}



}
//end class
?>
