<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 


$doc =& JFactory::getDocument(); 

?>
<div style="text-align:<?php echo $align; ?>" class="bcarss<?php echo $cssClass;?>">
	<div style="text-align:<?php echo $align; ?>" class="bcarss_message">
		<?php echo $message; ?>
    </div>
<?php
    $mod_content = "";
	foreach ($feed_props as $feed_prop) {
		$mod_content .= "<div style=\"text-align:$align\" class=\"bcarss_feed\">";
		$feed_link = JRoute::_("index.php?option=com_bca-rss-syndicator&amp;feed_id=".$feed_prop->id);
		$mod_content .= "<a href=\"".$feed_link."\">";
		$imgUrl = $feed_prop->feed_button;
		//Check if imgUrl is filled, is it empty then fill it with default.gif
		if ($imgUrl == "") { $imgUrl = "default.gif"; }
		//Check if there is a picture present
		if (file_exists(JPATH_BASE.DS."components".DS."com_bca-rss-syndicator".DS."assets".DS."images".DS."buttons".DS.$imgUrl)) {			
			//the picture exist so display it
			$feed_img =  JURI::root() . "components/com_bca-rss-syndicator/assets/images/buttons/".$imgUrl;			
			$mod_content .= "<img src=\"" . $feed_img . "\" alt=\"".$feed_prop->feed_name."\" title=\"".$feed_prop->feed_name."\" /></a>";
		} else {
		  //if the picture doesn't exist show the name of the feed
		  $mod_content .= $feed_prop->feed_name . "</a>";
		}
		$mod_content .= '</div>';
		
		//add head link
		$feedFormat = "application/xml";
		switch($feed_prop->feed_type)
		{
			case "0.91":
			case "1.0": 
			case "2.0":
				$feedFormat = "application/rss+xml";
				break;		
			case "ATOM":
				$feedFormat = "application/atom+xml";
				break;
			case "MBOX":
				$feedFormat = "text/plain";
				break;		
		}

        if($link_to_feed_icon)
        {
            $feedLinkExist = false;
                foreach($doc->_links as $link)
                {
                    preg_match("#href=\"".preg_quote($feed_link)."\"#s", $link, $matches);
                    if($matches){
                        $feedLinkExist = true;
                        break;
                    }
                }
                if(!$feedLinkExist)
                $doc->addHeadLink($feed_link, 'alternate', 'rel', array('type'=>$feedFormat, 'title'=>$feed_prop->feed_name));
        }
  }
  echo $mod_content;
?>
</div>
<?php
 //<link href="/xipat/j15/index.php?format=feed&amp;type=rss" rel="alternate" type="application/rss+xml" title="RSS 2.0" />
  //<link href="/xipat/j15/index.php?format=feed&amp;type=atom" rel="alternate" type="application/atom+xml" title="Atom 1.0" />

?>