<?php defined('_JEXEC') or die('Restricted access'); ?>
<ul id="searchresults">
<?php 
$section = "";
$counter = count( $this->results );
if ($counter > $this->array_params->get( 'amount' )) {$counter=$this->array_params->get( 'amount' );}
for ($i=0, $n=$counter; $i < $n; $i++)	{
	$result = &$this->results[$i];
	
	if($this->array_params->get( 'category' )==1) {
		if($result->section != $section) { // check if the category changed
			echo '<li class="category">'.$this->escape($result->section).'</li>';
			$section = $result->section;
		}
	}
	
	echo  "<li>";
		if ($result->browsernav == 1 ) : 
			echo '<a href="'.JRoute::_($result->href).'" target="_blank" >';
		else : 
			echo '<a href="'.JRoute::_($result->href).'">';
		endif; 	
	
			$name =  $this->escape($result->title);
			if(strlen($name) > 35) { 
				$name = substr($name, 0, 35) . "...";
			}	 
			$description = $result->text;
			if(strlen($description) > 80) { 
				$description = substr($description, 0, 80) . "...";
			}
			
			echo "<div class='txt'>";
				echo "<span>$name</span><br />";
				echo $description;
			echo "</div>";
	
    	echo  "</a>";

	echo "</li>";	
} 

if($counter==0) {
	echo '<li class="no_results">'.$this->array_params->get( 'no_results' ).'</li>';
}
echo '<li class="advanced_search"><a href="index.php?option=com_search&searchword='.$this->escape($this->searchword).'&ordering=newest&searchphrase=all" title="Search">'.$this->array_params->get( 'text_bottom' ).'</a></li>';
?>

</ul>

<?php exit; ?>