<?php

class JCEGroupsHelper {
	function getUserGroupFromId( $id ){
		$db	=& JFactory::getDBO();
		
		$query = 'SELECT *'
		. ' FROM #__jce_groups'
		. ' WHERE '.$id.' IN (users)'
		;			
		$db->setQuery( $query );
		$groups = $db->loadObjectList();
		return $groups[0];
	}
	function getUserGroupFromType( $type ){
		$db	=& JFactory::getDBO();
		
		if(!is_int($type)){
			$query = 'SELECT id'
			. ' FROM #__core_acl_aro_groups'
			. ' WHERE name = "'.$type.'"'
			;				
			$db->setQuery( $query );
			$id = $db->loadResult();
		}
		
		$query = 'SELECT *'
		. ' FROM #__jce_groups'
		. ' WHERE '.$type.' IN (types)'
		;			
		$db->setQuery( $query );
		$groups = $db->loadObjectList();
		return $groups[0];
	}
	function getRowArray($rows){
		$out = array();
		$rows = explode(';', $rows);
		$i = 1;
		foreach($rows as $row){
			$out[$i] = $row;
			$i++;
		}
		return $out;
	}
}