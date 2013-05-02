<?php defined('_JEXEC') or die('Restricted access'); // no direct access ?>

<script type="text/javascript">
// Create the appropriate ajaxrequest depending on the browser and then pass the responsetext
// to the function name passed as parameter.
function ajaxFunction(url, queryString, returnVar, retFunction) {
	var ajaxRequest;
	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				alert("Your browser broke!");
				return false;
			}
		}
	}
	try{
	   ajaxRequest.open("POST", url, true);
	   ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

	   //upon a change of status of the request for the lookup page, call the javascript handler
	   ajaxRequest.onreadystatechange = function() {
		//readystate of 4 means the request is complete
		if (ajaxRequest.readyState == 4) {
			//status code of 200 means OK (regular status codes)
			if (ajaxRequest.status != 200) {
				document.getElementById('result').innerHTML = "Oops! We seem to have run into some problem!";
				return false;
			} else
			{
				retFunction(ajaxRequest,returnVar);
			}
		}
	}
	   ajaxRequest.send(queryString);
	} catch (error3) {
		document.getElementById('result').innerHTML = "Oops! We just had a problem!";
		return false;
	}
};

// Evaluates the responsetext and displays appropriate result - not found trail/list of trail names
function getTrailList (jsonText) {
    if (eval( jsonText ))
    {
        if (eval("(" + jsonText.responseText + ")"))
        {
           searchResult = eval("(" + jsonText.responseText + ")");
        }
        else
        {
           document.getElementById('result').innerHTML = "Hmmm. We don't seem to have a trail with this name!";
           return;
        }

        if (searchResult.length == 0)
        {
           document.getElementById('result').innerHTML = "Hmmm. We don't seem to have a trail with this name!";
           return;
        }
        else
        {
           var cnt = 0;
	   var ol = "<ol>";
	   var li;

           while (cnt < searchResult.length)
           {
              ol += "<li>";
	      ol += searchResult[cnt].name;
              ol += "</li>";
	      cnt++;
           }
           ol += "</ol>";
	   document.getElementById('result').innerHTML = ol;
        }
    }
    else
	document.getElementById('result').innerHTML = "Whoa! Some problem!";
};

function handleEnter (e) {
	var key = e.keyCode || e.which;
	if (key == 13)
	{
	    searchResults();
	}
};

function getbrailList (jsonText) {
	alert(jsonText.responseText);
};

// Handle blank text in ajax search textbox and send search text to ajaxfunction.
// Actual search is done by the search component, output expected is in raw format
// which is formatted in this module.
function searchResults()
{
	var searchWord = document.getElementById('mod_search_searchword');

	if (searchWord.value == '')
	{
	   document.getElementById('result').innerHTML = "Please enter search text.";
	}
	else
	{
		//Get user id from Joomla, use it in the params sent to the search component.
		<?php	   $user =& JFactory::getUser();
			   $userID = $user->get('id');
		?>
		searchText = "searchMode=name&name=" + searchWord.value + "&uid=" + <?php echo $userID?>;
		ajaxFunction("/joom/index.php?option=com_searchtrails&format=raw", searchText, '', getTrailList);
	}
};
</script>

    <?php $output = '<input name="searchword" id="mod_search_searchword" maxlength="'.$maxlength.'" alt="'.$button_text.'" class="inputbox'.$moduleclass_sfx.'" onkeypress="handleEnter(event);" type="text" size="'.$width.'" value="'. $searchtext . '"  onblur="if(this.value==\'\') this.value=\''.$searchtext.'\';" onfocus="if(this.value==\''.$searchtext.'\') this.value=\'\';" />';
    $button = '<input type="button" value="go" class="button'.$moduleclass_sfx.'" onclick="searchResults();"/>';
    echo $output.'&nbsp&nbsp'.$button;
    ?>
    <! When search is done from the js searchbox, the searchbox of the ajax module is populated with the searchtext.
     This same text is used below to fire the ajax search module and display the results.-->
    <?php
       if ($searchtext != "")
       {
	  echo "<SCRIPT LANGUAGE='javascript'>searchResults();</SCRIPT>";
       }
    ?>

    <?php echo "<br /><br />\n"; ?>
<div id="result" name="result">
</div>
    <input type="hidden" name="task" value="" />