 <?php
// no direct access
defined('_JEXEC') or die('Restricted access');

// Get request ID from query string variable
$trailname = JRequest::getVar( 'searchName', null );
$trailid = JRequest::getInt( 'rid' );
$userid = JRequest::getInt( 'uid' );

// Get instance of database object
$db =& JFactory::getDBO();

//require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'search.php' );
require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_search'.DS.'helpers'.DS.'search.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

if(strlen($trailname)>0 && !$trailid)
{
	$query = 'SELECT a.Content_ID as id, b.title, '
					. 'b.sectionid, b.catid, '
					. 'b.created_by, b.created, '
					. 'CONCAT( b.introtext, b.`fulltext` ) AS text, CONCAT_WS( "/", u.title, c.title ) AS section,'
					. 'CASE WHEN CHAR_LENGTH( c.alias ) THEN CONCAT_WS( ":", c.id, c.alias ) ELSE c.id END AS catslug,'
					. 'CASE WHEN CHAR_LENGTH( b.alias ) THEN CONCAT_WS( ":", b.id, b.alias ) ELSE b.id END AS slug '
					. 'FROM jos_trailReview a, jos_trailList d, jos_content b '
					. 'INNER JOIN jos_categories AS c ON c.id = b.catid '
					. 'INNER JOIN jos_sections AS u ON u.id = b.sectionid '
					. 'WHERE d.name LIKE ' . $db->Quote( '%'. $db->getEscaped( $trailname, true ) .'%', false ) . ' '
					. 'AND a.Trail_ID = d.id '
					. 'AND a.Content_ID = b.id '
					. 'AND b.state =1 '
					. 'AND c.published =1 '
					. 'AND u.published =1 ';
}
elseif ($trailid)
{
	$query = 'SELECT a.Content_ID as id, b.title, '
					. 'b.sectionid, b.catid, '
					. 'b.created_by, b.created, '
					. 'CONCAT( b.introtext, b.`fulltext` ) AS text, CONCAT_WS( "/", u.title, c.title ) AS section,'
					. 'CASE WHEN CHAR_LENGTH( c.alias ) THEN CONCAT_WS( ":", c.id, c.alias ) ELSE c.id END AS catslug,'
					. 'CASE WHEN CHAR_LENGTH( b.alias ) THEN CONCAT_WS( ":", b.id, b.alias ) ELSE b.id END AS slug '
					. 'FROM jos_trailReview a, jos_trailList d, jos_content b '
					. 'INNER JOIN jos_categories AS c ON c.id = b.catid '
					. 'INNER JOIN jos_sections AS u ON u.id = b.sectionid '
					. 'WHERE a.Trail_ID = ' . $trailid . ' '
					. 'AND a.Content_ID = b.id '
					. 'AND d.id = a.Trail_ID '
					. 'AND b.state =1 '
					. 'AND c.published =1 '
					. 'AND u.published =1 ';
}

$db->setQuery( $query, 0 );
$list = $db->loadObjectList();

if(isset($list))
{
	foreach($list as $key => $item)
	{
		$list[$key]->href = ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid);
	}
}

$rows = $list;

$results = $list;

?>
<form id="searchForm" action="<?php echo JRoute::_( 'index.php' );?>" method="get" name="searchForm">
	<table class="contentpaneopen<?php echo JRequest::getVar( 'pageclass_sfx', null );?>">
		<tr>
			<td width="auto" nowrap="nowrap">
				<label for="search_searchword">
					<?php echo JText::_( 'Search Reviews for Trail' ); ?>:
				</label>
			</td>
			<td nowrap="nowrap">
				<input type="text" name="searchName" id="search_searchword" size="30" maxlength="20" value="<?php echo $trailname; ?>" class="inputbox<?php echo JRequest::getVar( 'pageclass_sfx', null );?>" />
			</td>
			<td width="100%" nowrap="nowrap">
				<button name="Search" onClick="this.form.submit()" class="button<?php echo JRequest::getVar( 'pageclass_sfx', null );?>"><?php echo JText::_( 'Search' );?></button>
			</td>
		</tr>
		<tr>
			<td>
				<br />
				<?php echo "Total : "; echo count ($results); if (count ($results) == 1 ) echo JText::_( " review " ); else echo JText::_( " reviews " ); echo JText::_( "found for " );?> <b><?php echo $trailname;?></b>
			</td>
		</tr>
		<tr>
			<td nowrap="nowrap">
			<?php
			if (count ($results) > 0) {
				foreach( $results as $key => $result ) { ?>
					<fieldset>
						<div>
							<span class="small<?php echo JRequest::getVar( 'pageclass_sfx', null );?>">
								<?php echo $key + 1; echo " . "; ?>
							</span>
							<?php if ( $result->href ) : ?>
								<a href="<?php echo JRoute::_($result->href); ?>">
							<?php endif;

							echo $result->title;

							if ( $result->href ) : ?>
								</a>
							<?php endif;
							if ( $result->section ) : ?>
								<br />
								<span class="small<?php echo JRequest::getVar( 'pageclass_sfx', null );?>">
									(<?php echo $db->getEscaped($result->section); ?>)
								</span>
							<?php endif; ?>
							<div>
								<?php //echo $result->text; ?>
							</div>
							<div class="small<?php echo JRequest::getVar( 'pageclass_sfx', null );?>">
								<?php echo JHTML::Date ( $result->created );?>
							</div>
						</div>
					</fieldset>
				<?php }
			}?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="option" value="com_searchtrailreviews" />
</form>
