<?php
/**
 * @package    Joomla.Tutorials
 * @subpackage Components
 * @link http://docs.joomla.org/Developing_a_Model-View-Controller_Component_-_Part_1
 * @license    GNU/GPL
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
$document = JFactory::getDocument();
$url = JFactory::getURI()->toString();
if (strpos($url, ".com") !== false) {
   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQkUZHNJdizg5ywABG1vcOZLnlKKRQiK1QyIYbC7QJYSAvZi_ftqMywEg';
}
else if (strpos($url, ".in") !== false) {
   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBTGk7r6UUG2tv7pCXD49pEILMut2BSK1KyluFXiSlHDmPfgxKEcQu31zA';
}
else if (strpos($url, ".net") !== false) {
   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQVUtw86IlLILJmmHf_nHtc38TT4xQEwo2T9X3LZpg2rnfZGcOvR7jrgA';
}
else if (strpos($url, ".org") !== false) {
   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBRfh_bXew_S5fvS_f8On4C7bLoFWxSxwel7vDUce97Zyj7kn9hhDqcEhQ';
}
else if (strpos($url, "192.168.2.3") !== false) {
   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBQrYQ538GaVx7Y5oWUNieOY1BuluhTxxahJrgPkopr_wUZiigcgAainqA';
}
else if (strpos($url, "192.168.2.2") !== false) {
   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBR2bDFzeCT1WBspxZrlXfH7Q6YWYhSjQl2ZBZH0H0HKmSschtXmr2DxcA';
}
else if (strpos($url, "192.168.1.10") !== false) {
   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBTmuanj1bmj0Kwra7_b0ad5OHlDlBRDXWIgvx0w3LRNw0hBx3Uyx9NgQg';
}
else if (strpos($url, "192.168") !== false) {
   $key = 'ABQIAAAAQOdSXOyy0HH_z2H06qwXrBSQvaeunbmSpov2FeFgtN9Ft_-duxQeLyrqgG0pLspna6L5_tzgXRcCrA';
}
$document->addScript('http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $key);
$document->addScript('https://www.google.com/jsapi?key=' . $key);
$document->addScript(JURI::base() . 'components/com_routes/assets/js/map.js');
$document->addScript(JURI::base() . 'components/com_routes/assets/js/markerlib_pack.js');
$document->addScript(JURI::base() . 'components/com_routes/assets/js/epoly.js');
$document->addStyleSheet(JURI::base() . 'components/com_routes/assets/css/map.css');

$njs = 'window.addEvent(\'onunload\', \'GUnload()\');';
$document->addScriptDeclaration($njs);

/**
 * HTML View class for the HelloWorld Component
 *
 * @package    HelloWorld
 */
class RoutesViewTraildisplay extends JView
{
    function display($tpl = null)
    {
        $model = &$this->getModel();
        $details = $model->getTrailDetail();
	$js = "load(";
	if (JFactory::getUser()->get('username'))
	  $js .= '\'' . JFactory::getUser()->get('username') . '\'';
	else
	  $js .= '\'' . 'Guest' . '\'';
	$js .= ', ';
	if (JFactory::getUser()->get('id'))
	  $js .= '\'' . JFactory::getUser()->get('id') . '\'';
	else
	  $js .= "'0'";
	if ($details->id)
	  $js .= ", " . $details->id;
	else
	  $js .= ", " . '\'0\'';
	$js .= ",'".$geoplugin->city."');";
	$picjs = <<<TEST
      google.load('search', '1');

      var imageSearch;

      function addPaginationLinks() {
        // To paginate search results, use the cursor function.
        var cursor = imageSearch.cursor;
        var curPage = cursor.currentPageIndex; // check what page the app is on
        var pagesDiv = document.createElement('p');
        for (var i = 0; i < cursor.pages.length; i++) {
          var page = cursor.pages[i];
          if (curPage == i) { 

          // If we are on the current page, then don't make a link.
            var label = document.createTextNode(' ' + page.label + ' ');
            pagesDiv.appendChild(label);
          } else {

            // Create links to other pages using gotoPage() on the searcher.
            var link = document.createElement('a');
            link.href = 'javascript:imageSearch.gotoPage('+i+');';
            link.innerHTML = page.label;
            link.style.marginRight = '2px';
            pagesDiv.appendChild(link);
          }
        }

        var contentDiv = document.getElementById('pictures');
        contentDiv.appendChild(pagesDiv);
      }

      function searchCompletePic(query) {

        // Check that we got results
        if (imageSearch.results && imageSearch.results.length > 0) {
          // Grab our content div, clear it.
          var contentDiv = document.getElementById('pictures');
          contentDiv.innerHTML = '';

          // Loop through our results, printing them to the page.
          var results = imageSearch.results;
          for (var i = 0; i < results.length; i++) {
            // For each result write it's title and image to the screen
            var result = results[i];
            var imgContainer = document.createElement('div');
            imgContainer.className = "box";
            var newLink = document.createElement('a');
            
            // We use titleNoFormatting so that no HTML tags are left in the 
            // title
            newLink.title = query;
            newLink.setAttribute ('rel', "{handler: 'iframe', size: {x: " + result.width + ", y: " + result.height + "}}");
//            newLink.rel = "{handler: 'iframe'}";
            newLink.href = "#";
            newLink.setAttribute ('onclick', "SqueezeBox.fromElement('" + result.url + "'); return false;");

            var newImg = document.createElement('img');
            // There is also a result.url property which has the escaped version
            newImg.src = result.tbUrl;
            newImg.alt = query + ' img' + (i+1);
            newImg.title = query + ' img' + (i+1);

            newLink.appendChild(newImg);
            imgContainer.appendChild(newLink);

            // Put our title + image in the content
            contentDiv.appendChild(imgContainer);
          }
          var clrContainer = document.createElement('div');
          clrContainer.setAttribute("style","clear:both");
          clrContainer.appendChild( document.createTextNode( '\u00A0' ) );
          contentDiv.appendChild(clrContainer);

          // Now add links to additional pages of search results.
          addPaginationLinks(imageSearch);
        }
      }

      function OnLoad() {
      
        // Create an Image Search instance.
        imageSearch = new google.search.ImageSearch();

        // Set searchComplete as the callback function when a search is 
        // complete.  The imageSearch object will have results in it.
        imageSearch.setSearchCompleteCallback(this, searchCompletePic, ['$details->name']);
        imageSearch.setResultSetSize(8);

        // Find me a beautiful car.
        imageSearch.execute("$details->name");
        
        // Include the required Google branding
        google.search.Search.getBranding('branding');
      }
      google.setOnLoadCallback(OnLoad);
TEST;
	$js .= $picjs;
	print_r ($key);
         $this->assignRef( 'js', $js );

	if ($details->id)
	{
		JPluginHelper::importPlugin( 'trails' );
		$dispatcher =& JDispatcher::getInstance();
		$results = $dispatcher->trigger( 'onDisplayTrails2', array (& $details->id));
	        $this->assignRef( 'html', $results[0] );
	}

//	$query = " SELECT a.id, a.name, a.nname, a.nemail, \n" .
//		" a.userId, a.intro, a.length, DATE_FORMAT(a.createtime,'%d %b %y') as ttime, d.name as uname, upload, encodeurl\n" .
        $this->assignRef( 'trailid', $details->id );
        $this->assignRef( 'trailname', $details->name );
        $this->assignRef( 'description', $details->descr );
        $this->assignRef( 'createtime', $details->ttime );
        $this->assignRef( 'username', $details->uname );
        $this->assignRef( 'nname', $details->nname );
        $this->assignRef( 'traillength', $details->length );
        $this->assignRef( 'upload', $details->upload );
        $this->assignRef( 'encodeurl', $details->encodeurl );
 
        parent::display($tpl);
    }
}
