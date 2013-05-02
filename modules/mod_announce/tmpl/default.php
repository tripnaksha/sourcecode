<?php // no direct access
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.mootools');
$doc = &JFactory::getDocument();
?>
<?php if (count($anns) > 0)
	{
	$x = "<script type=text/javascript>";
	$x = $x . "var author=\"www.jdstiles.com\";
		var DS_Speed="; 
	$x = $x . $delay  . "*1000;";
	$x = $x . "var DS_txtalign=\"left\";
		var DS_msgs=new Array();
		var DS_urls=new Array();
		var DS_targets=new Array();
		var DS_fontface=new Array();
		var DS_fontcolor=new Array();
		var DS_fontsize=new Array();";

		foreach($anns as $product)
		{
	$x = $x . "DS_msgs[DS_msgs.length] = \"" . $product->text . "\";"; 
	$x = $x . "DS_urls[DS_urls.length] = \"" . $product->link . "\";";
	$x = $x . "DS_targets[DS_targets.length] = \"\";
		DS_fontface[DS_fontface.length] = \"arial\";
		DS_fontcolor[DS_fontcolor.length] = \"#ffffff\";
		DS_fontsize[DS_fontsize.length] = \"2\";";
		}
//		endforeach;
	$x = $x . "var DS_I=0;
		var DS_MsgsLength=0;
		var DS_MsgsLength=DS_msgs.length;
		for(DS_I=0;DS_I<DS_MsgsLength;DS_I++){
		DS_msgs[DS_I]=\"<font face=\"+DS_fontface[DS_I]+\" size='\" +DS_fontsize[DS_I]+ \"' color='\"+DS_fontcolor[DS_I]+\"'>\" +DS_msgs[DS_I]+\"</font>\";
		if(DS_urls[DS_I]!=\"\")
			if(DS_targets[DS_I]!=\"\")
				DS_msgs[DS_I]=\"<a href='http://\"+DS_urls[DS_I]+\"' target='\"+DS_targets[DS_I]+\"'>\"+DS_msgs[DS_I]+\"</a>\";
			else
				DS_msgs[DS_I]=\"<a href='http://\"+DS_urls[DS_I]+\"'>\"+DS_msgs[DS_I]+\"</a>\";
		}
		var DS_Count=0;
		if (document.layers)
		  document.write(\"<layer name='nannouncement' top='0'></layer>\");
		else if (document.all||document.getElementById)
		{
		  document.write(\"<div id='announcement' style='text-align:\"+DS_txtalign+\"'></div>\");
		}
		function DS_SlideText(){
		  if (document.layers){
		    document.layers.nmsg.document.open();
		    document.layers.nmsg.document.write(\"<p align=\"+DS_txtalign+\">\"+DS_msgs[DS_Count]);
		    document.layers.nmsg.document.close();
		  }
		  else if (document.all||document.getElementById){
		    var ien6=(document.all) ? announcement:document.getElementById('announcement');
		    ien6.innerHTML=DS_msgs[DS_Count];
		  }
		  DS_Count=(DS_Count+1)%DS_MsgsLength;
		  setTimeout('DS_SlideText()',DS_Speed);
		}
		window.onload = DS_SlideText;
	</script>";
	}
	echo $x;
 ?>
