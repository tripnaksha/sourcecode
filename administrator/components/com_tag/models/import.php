<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'tag.php';
require_once JPATH_COMPONENT_SITE.DS.'helper'.DS.'helper.php';

/**
 * Tag Component Import Model
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class TagModelImport extends TagModelTag
{
	function termCheck($term){
		$ignoreNumericTags=JoomlaTagsHelper::param('IgnoeNumericTags',0);
		if($ignoreNumericTags){
			if(is_numeric($term)){
				echo('ignore:'.$term);
			 return false;
			}
		}
		$minTagLength=JoomlaTagsHelper::param('MinTagLength',1);
		$len=JString::strlen($term);
		if($len<$minTagLength){
			return false;
		}
		return true;
	}

	function importTagsFromMetaKeys(){
		$query='select id,metakey from #__content';
		$this->_db->setQuery($query);
		$metaKeys= $this->_db->loadObjectList();
		if(!empty($metaKeys)){
			foreach($metaKeys as $meta){
				if(isset($meta->metakey)&&empty($meta->metakey)==false){
					$cid=$meta->id;
					if(!$this->isContentHasTags($cid)){

						$keys=explode(',',$meta->metakey);
						$keysProcessed=array();
						foreach($keys as $key){
							$key=JoomlaTagsHelper::preHandle($key);
							if(empty($key)==false){
								if(!in_array($key,$keysProcessed)){
									$keysProcessed[]=$key;
								}
							}
						}
						unset($keys);
						$deleteTags='delete from #__tag_term_content where cid='.$cid;
						$this->_db->setQuery($deleteTags);
						$this->_db->query();
						foreach($keysProcessed as $key){
							$pass=$this->termCheck($key);
							if($pass){
								$tid=$this->storeTerm($key);
								$this->insertContentterm($tid,$cid);
							}
						}
					}
				}
			}
		}
		//print_r($metaKeys);

	}
	function importTagsFromJTags(){
		$jtagsQuery="select tag_id,item_id from #__jtags_items where component='com_content'";
		$this->_db->setQuery($jtagsQuery);
		$jtagTags= $this->_db->loadObjectList();
		$jtags=array();
		if(!empty($jtagTags)){
			foreach($jtagTags as $jtag){
				if(array_key_exists($jtag->tag_id,$jtags)){
					$jtags[$jtag->tag_id][]=$jtag->item_id;
				}else{
					$jtags[$jtag->tag_id]=array($jtag->item_id);
				}
			}
		}
		$jtermsQuery='select tag_id,name from #__jtags_tags';
		$this->_db->setQuery($jtermsQuery);
		$jtagterms= $this->_db->loadObjectList();
		if(!empty($jtagterms)){
			foreach($jtagterms as $jterm){
				$pass=$this->termCheck($jterm->name);
				if($pass){
					$tid=$this->storeTerm($jterm->name);
					if(array_key_exists($jterm->tag_id,$jtags)){
						$cids=$jtags[$jterm->tag_id];
						foreach ($cids as $cid){
							$this->storeContentTerm($tid,$cid);
						}
					}
				}
			}
		}

	}



}
