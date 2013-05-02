<?php
/**
* @version		$Id: extravote.php 2008 vargas $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or  die('Restricted access');
jimport( 'joomla.event.plugin' );



class plgContentExtraVote extends JPlugin
{

	function plgContentExtraVote( &$subject )
	{
        parent::__construct($subject);
		
        $this->plugin = &JPluginHelper::getPlugin('content', 'extravote');
        $this->params = new JParameter($this->plugin->params);
	}
		
	function onBeforeDisplayContent(&$article, &$params)
	{
        if ( $this->params->get('display') == 0 && !$params->get( 'intro_only' )  )
		{
		    $article->xid = 'x';
		    return $this->ContentExtraVote($article, $params);
 	    }
 	}
	
	function ContentExtraVote(&$article, &$params)
	{ 
           
		$id  = $article->id;
		$xid = $article->xid;
		
		$rating_count=$rating_sum=0;
		$html='';

		if ($params->get( 'show_vote' ) && !$params->get( 'popup' ))
		{
			$db	=& JFactory::getDBO();
			$query='SELECT * FROM #__content_rating WHERE content_id='. $id;
			$db->setQuery($query);
			$vote=$db->loadObject();
		
			if($vote) {
				$rating_sum = intval($vote->rating_sum);
				$rating_count = intval($vote->rating_count);
			}
		
				$html .= $this->plgContentExtraVoteStars( $id, $rating_sum, $rating_count, $xid );
		}
		return $html;
 	}
	
  
 	function plgContentExtraVoteStars( $id, $rating_sum, $rating_count, $xid )
	{
	 	if ( $this->params->get('css', 1) ) :
			JHTML::stylesheet('extravote.css','plugins/content/extravote/',false);
		endif;
     	JHTML::script('extravote.js','plugins/content/extravote/',false);
        JPlugin::loadLanguage('plg_content_extravote', JPATH_ADMINISTRATOR);
	
        $live_path = JURI::base();

     	global $plgContentExtraVoteAddScript;
		
		$counter = $this->params->get('counter',1);
		$unrated = $this->params->get('unrated',1);
		$percent = 0;
		$stars = '';
		
	 	if(!$plgContentExtraVoteAddScript){ 
         	echo "
<script type=\"text/javascript\" language=\"javascript\">
<!--
var sfolder = '".JURI::base(true)."';
var extravote_text=Array('".JTEXT::_('Your browser does not support AJAX')."','".JTEXT::_('Loading')."','".JTEXT::_('Thank you for voting')."','".JTEXT::_('You need to login')."','".JTEXT::_('You have already rated this item')."','".JTEXT::_('Votes')."','".JTEXT::_('Vote')."');
-->
</script>";
     	$plgContentExtraVoteAddScript = 1;
	 	}
		
		if($rating_count!=0) {
			$percent = number_format((intval($rating_sum) / intval( $rating_count ))*20,2);
		} elseif ($unrated == 0) {
			$counter = -1;
		}
		
		if ( (int)$xid ) { 
			$stars = '-small';
			if ( $counter == 2 ) $counter = 0;
		} else {
			if ( $counter == 3 ) $counter = 0;
		}
								
	 	$html="
<div class=\"extravote-container".$stars."\"".( $xid != 'x' ? "" : " style=\"margin-top:5px;\"" ).">
  <ul class=\"extravote-stars".$stars."\">
    <li id=\"rating_".$id."_".$xid."\" class=\"current-rating\" style=\"width:".(int)$percent."%;\"></li>
    <li><a href=\"javascript:void(null)\" onclick=\"javascript:JVXVote(".$id.",1,".$rating_sum.",".$rating_count.",'".$xid."',".$counter.");\" title=\"".JTEXT::_('Very Poor')."\" class=\"one-star\">1</a></li>
    <li><a href=\"javascript:void(null)\" onclick=\"javascript:JVXVote(".$id.",2,".$rating_sum.",".$rating_count.",'".$xid."',".$counter.");\" title=\"".JTEXT::_('Poor')."\" class=\"two-stars\">2</a></li>
    <li><a href=\"javascript:void(null)\" onclick=\"javascript:JVXVote(".$id.",3,".$rating_sum.",".$rating_count.",'".$xid."',".$counter.");\" title=\"".JTEXT::_('Regular')."\" class=\"three-stars\">3</a></li>
    <li><a href=\"javascript:void(null)\" onclick=\"javascript:JVXVote(".$id.",4,".$rating_sum.",".$rating_count.",'".$xid."',".$counter.");\" title=\"".JTEXT::_('Good')."\" class=\"four-stars\">4</a></li>
    <li><a href=\"javascript:void(null)\" onclick=\"javascript:JVXVote(".$id.",5,".$rating_sum.",".$rating_count.",'".$xid."',".$counter.");\" title=\"".JTEXT::_('Very Good')."\" class=\"five-stars\">5</a></li>
  </ul>
</div>
  <span id=\"extravote_".$id."_".$xid."\" class=\"extravote-count\"><small>";
  
  		if ( $counter != -1 ) {
  			if ( $counter != 0 ) {
				$html .= "( ";
			 		if($rating_count!=1) {
				 		$html .= $rating_count." ".JTEXT::_('Votes');
			 		} else { 
			 			$html .= $rating_count." ".JTEXT::_('Vote');
     				}
 	 			$html .=" )";
			}
		}
 	 	$html .="</small></span>";
		
	 	return $html;
 	}
	
 	function onPrepareContent( &$article, &$params ) 
	{
	    if (isset($article->id)) {
		
	        $extra = $this->params->get('extra', 1);
			$main  = $this->params->get('main', 1);
			
 	 	    if ( $extra != 0 ) {
			
   	 		    $regex = "#{extravote\s*([0-9]+)}#s";
				
			    if ( $extra == 2 && JRequest::getCmd('view') != 'article')
			    {
   	 			    $article->text = preg_replace( $regex, '', $article->text );
			    } else {
				    $this->article_id = $article->id;
   	 			    $article->text = preg_replace_callback( $regex, array($this,'plgContentExtraVoteReplacer'), $article->text );
			    }
		    }
			
 	 	    if ( $main != 0 ) {
			
			    if ( $main == 2 && JRequest::getCmd('view') != 'article')
			    {
   	 			    $article->text = preg_replace( '#{mainvote}#', '', $article->text );
			    } else {
				    $this->article_id = $article->id;
   	 			    $article->text = preg_replace_callback( '#{mainvote}#', array($this,'plgContentExtraVoteReplacer'), $article->text );
			    }
		    }
		
		    if ( $this->params->get('display') == 1 )  {
			
		        $article->xid = 'x';
		        $article->text .= '<br />'.$this->ContentExtraVote($article, $params);
		    }
 	    }
 	}
 
	function plgContentExtraVoteReplacer(&$matches ) 
	{
  		$db	=& JFactory::getDBO();
  		$cid=$this->article_id;
  		$rating_sum = 0;
  		$rating_count = 0;
		if ($matches[0] == '{mainvote}') {
			global $mainvote;
			$mainvote .= 'x';
  			$xid .= 'x'.$mainvote;
  			$db->setQuery('SELECT * FROM #__content_rating WHERE content_id='. (int)$cid);
		} else {
  			$xid = (int)$matches[1];
  			$db->setQuery('SELECT * FROM #__content_extravote WHERE content_id='.(int)$cid.' AND extra_id='.(int)$xid);
		}
  		$vote = $db->loadObject();
  		if($vote) {
	 		if($vote->rating_count!=0)
				$rating_sum = intval($vote->rating_sum);
				$rating_count = intval($vote->rating_count);
	 	}
  		return $this->plgContentExtraVoteStars( $cid, $rating_sum, $rating_count, $xid );
	}
	
}
?>
