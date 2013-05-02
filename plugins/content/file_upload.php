<?php

/**

* @version	$Id: file_upload.php 2009-01-11  Kim $

* @package	Plg_file_upload 1.6.2

* @copyright	Copyright (C) 2008 Joomla Projects. All rights reserved.

* @license	GNU/GPL, see LICENSE.txt

* @contact	administracion@joomlanetprojects.com

* @website	www.joomlanetprojects.com

**/



//Acceso restringido fuera de Joomla!

defined( '_JEXEC' ) or die( 'Acceso restringido' );



JHTML::_('behavior.mootools');
require_once (JPATH_SITE.DS.'components'.DS.'com_savetrail'.DS.'phpencoder.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_savetrail'.DS.'PolylineReducer.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_savetrail'.DS.'GeoPoint.php');
$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base() . 'media/system/css/modal.css');
$document->addScript(JURI::base() . 'media/system/js/modal.js');
$document->addStyleSheet(JURI::base() . 'media/system/css/modal.css');
$document->addScriptDeclaration("window.addEvent('domready', function() {SqueezeBox.initialize({});});");



jimport( 'joomla.plugin.plugin' );



class plgContentfile_upload extends JPlugin

{



    //Constructor

    function plgContentfile_upload( &$subject )

    {

      //Creamos el constructor de la clase

      parent::__construct( $subject );



      // Cargamos los parmetros del plugin

      $this->_plugin = JPluginHelper::getPlugin( 'content', 'file_upload' );

      $this->_params = new JParameter( $this->_plugin->params );

    }





	function onPrepareContent( &$article, &$params, $limitstart )

	{

            global $mainframe;



            jimport('joomla.filesystem.file');



            //obtenemos el lenguaje desde la administracion

            JPlugin::loadLanguage( 'plg_content_file_upload', JPATH_ADMINISTRATOR );



	    //obtenemos el plugin pasandole tipo y nombre

	    $plugin =& JPluginHelper::getPlugin('content', 'file_upload');



	    //Instanciamos JParameter con los parametros como argumento

            $pluginParams = new JParameter( $plugin->params );



		//enviamos las cabeceras css

		if($pluginParams->get('dropbox') == 0)

		{

			JHTML::stylesheet('file_upload.css', 'plugins/content/file_upload/', array('media'=>'all'));

		}

		else

		{

			JHTML::stylesheet('dropbox.css', 'plugins/content/file_upload/', array('media'=>'all'));

		}

                

		//Almacenamos los valores de los parametros y otras variables

		$enabled		= $pluginParams->get('enabled', 1);

                $word                   = $pluginParams->get('word', 'upload');

                $rights                 = $pluginParams->get('permisos', 0777);

		$destino 		= $pluginParams->get('destino', 'files');

		$size 			= $pluginParams->get('size', 50);

		$button_name            = $pluginParams->get('button_name', 'Upload');

		$class 			= $pluginParams->get('class', 'uploadbutton');

		$inputbox 		= $pluginParams->get('inputbox', 'inputbox');

		$max_size 		= $pluginParams->get('max_size', 30);

		$NOTICE			= JText::_('NOTICE');

                $ERROR                  = JText::_('ERROR');

                $SUCCESS                = JText::_('SUCCESS');

                $MIME_FAIL              = JText::_('MIME_FAIL');

                $FILENAME               = JText::_('FILENAME');

                $FILESIZE               = JText::_('FILESIZE');

                $DOWNLOAD               = JText::_('DOWNLOAD');

                $RELOAD                 = JText::_('RELOAD');

		$type 			= $pluginParams->get('type', 'zip');

		$admin_mail             = $pluginParams->get('admin_mail', 1);

                $recipient              = $pluginParams->get('recipient');

                $ishtml                 = $pluginParams->get('ishtml', 1);

		$dropbox                = $pluginParams->get('dropbox', 0);

		$dropuser               = $pluginParams->get('dropuser', 'myusername');

		$droppass               = $pluginParams->get('droppass', 'mypassword');

		$dropfolder             = $pluginParams->get('dropdir', 'myfolder');

		$site			= $mainframe->getCfg('sitename');

                $manager                = $pluginParams->get('manager', 1);

                $alerts                 = $pluginParams->get('alerts', 0);



                //almacenamos en $regex la expresion regular

		$regex = '/{'.$word.'}/i';



		//Si el plugin no esta activado imprimiremos una cadena vacia

		if (!$enabled)

		{

			$article->text = preg_replace($regex, ' ', $article->text);

        }



		//buscamos la coincidencia de la expresion regular en los articulos

		preg_match($regex, $article->text, $matches);



                // Number of plugins

                $count = count( $matches[0] );



		if($count)

		{



		//obtenemos cada uno de los mime type introducidos en una matriz

		$tipos = explode(",", $type);



                //Si no existe creamos un directorio en el raiz para nuestros archivos

		if (!JFolder::exists('$destino'))

		{

			JFolder::create($destino, $rights);

		}

		if(!JFolder::exists('$username') && !$guest)

		{

			JFolder::create($destino.DS.$username, $rights);

		}



		//Obtendremos los datos del usuario a fin de crear una subcarpeta con su username

		$user     = & JFactory::getUser();

		$username = $user->get('username');

		$guest    = $user->guest;



		$alert = "<div class='alert'>".$NOTICE."</div>";



		if($guest)

		{

			$article->text = preg_replace($regex, $alert, $article->text);

		}

		else

		{

			if($dropbox == 0)

			{

				// mostramos el formulario de envio

				$js = "<script type='text/javascript'>

					function ajaxFunction(url, queryString, returnVar, retFunction) {

					var ajaxRequest;

					try{

						// Opera 8.0+, Firefox, Safari

						ajaxRequest = new XMLHttpRequest();

					} catch (e){

						// Internet Explorer Browsers

						try{

							ajaxRequest = new ActiveXObject(\"Msxml2.XMLHTTP\");

						} catch (e) {

							try{

								ajaxRequest = new ActiveXObject(\"Microsoft.XMLHTTP\");

							} catch (e){

								// Something went wrong

								alert(\"Your browser broke!\");

								return false;

							}

						}

					}

					try{

					   ajaxRequest.open(\"POST\", url, true);

					   ajaxRequest.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded\");

					   ajaxRequest.send(queryString);

					   //upon a change of status of the request for the lookup page, call the javascript handler

					   ajaxRequest.onreadystatechange = function() {

						//readystate of 4 means the request is complete

						if (ajaxRequest.readyState == 4) {

							//status code of 200 means OK (regular status codes)

							if (ajaxRequest.status != 200) {

								alert('Page not found');

				//				alert(ajaxRequest.responseText);

								return false;

							}

							else {

				//			alert(ajaxRequest.responseText);

							   retFunction(ajaxRequest,returnVar);

							}

						}

					   };

					} catch (error3) {

						alert('Page not found');

						return false;

					}

				}

				function checkAval () {

					var trailName = document.getElementById('trailName');

					var nameMsg = document.getElementById('nameMsg');

					if (trailName.value.length >= 6) {

					  var searchTrailname = \"searchMode=all&searchName=\" + trailName.value;

					  ajaxFunction(\"index.php?option=com_searchtrails&format=raw\", searchTrailname, '', getAval);

					  //ajaxFunction(url, queryString, returnVar, retFunction) -- function definition

					}

					else

					{

					  avFlag = 0;

					}

				};

				//Check if said trail name is already saved or if it is still available.

				function getAval (jsonText) {

					var nameMsg = document.getElementById('nameMsg');

					var btn = document.getElementById('submitbtn');

					if (eval( jsonText.responseText ) == null)

					{

					  nameMsg.innerHTML = \"<small>Trail name available!</small>\";

					  nameMsg.style.color = '#73d670';

					  btn.disabled = false;

					  avFlag = 1;

					}

					else

					{

					  nameMsg.innerHTML = \"Please choose a different name for the trail.\";

					  nameMsg.style.color = 'red';

					  btn.disabled = true;

					  avFlag = 0;

					}

				};

				window.onload = function ( e ) {

					cssCode ='html {background-color: #f8f8f8;}body {margin: 5px;  background-color: #f8f8f8;  overflow: hidden;} a {text-decoration: underline !important;  color: #fff !important;}';

					cssCode = cssCode + 'a:hover {text-decoration: none !important;  color: red;} a img {border: 0px;}';

					cssCode = cssCode + 'h3 {font-family: Arial, Verdana, Helvetica;  font-size: 16px;  color: #81AA4F;}';

					cssCode = cssCode + 'label {padding-top:5px; font-weight: bold;  font-size: 14px;  color: #445903;} p {font-weight: bold;  color: #4f6313;}';

					cssCode = cssCode + '.fields {margin-bottom: 7px;} .submit {border: 1px solid #607c24;padding: 2px;text-align: center;background-color: #5d7021;color: #CCCCCC;}';

					cssCode = cssCode + '#system-message {position: absolute;  left: 20px;} #system-message dt.error {   display: none;}';

					cssCode = cssCode + '#system-message dd.error ul {   color: #c00;   background-color: #E6C0C0;   border: 3px solid #DE7A7B !important; list-style: none;  padding-right: 5px;  height: auto !important;  width: 200px;  vertical-align: middle;}';

					var styleElement = document.createElement('style');

					styleElement.type = 'text/css';

					if (styleElement.styleSheet) {

					   styleElement.styleSheet.cssText = cssCode;

					}

					else {

					   styleElement.appendChild(document.createTextNode(cssCode));

					}

					document.getElementsByTagName('head')[0].appendChild(styleElement);

				};

				</script>";

				$form = $js .  "<div id='formUpload'><form name='file_upload_form' method='post' action='' enctype='multipart/form-data' target='upload_target'>

				<div class='fields'>

				   <input type='file' class='$inputbox' name='file' size='$size' maxlength='$max_size' />

				</div>

				<div class='fields'>

				   <label >Name this trail* - </label><br />

				   <input type='text' class='$inputbox' id='trailName' name='trailName' size='20' maxlength='60' onkeyup=\"checkAval()\"/>

				<div id='nameMsg' ></div>

				</div>

				<div class='fields'>

				   <label >Brief introduction - </label><br />

				   <textarea id='trailDesc' name='trailDesc' cols=20></textarea>

				</div>

				<!--div class='fields'>

				   <input type='checkbox' name='roadtype' value='100'><label > Check this for on-road trails</label><br />

				</div-->

				<div class='fields'>

				   <input type='checkbox' name='private' value='100'><label > Check this for private trails</label><br />

				</div>

				<input id='submitbtn' type='submit' name='enviar' value='$button_name' class='$class'>

				<iframe id='upload_target' name='upload_target' src='' style='width:0;height:0;border:0px;'></iframe>

				<div id='afterUpload'>

				</div>

				</form></div>";



                                if($manager == 1 && $dropbox == 0)

                                {

                                    $form .= "<div class='Uploadrefresh'><a href='".$_SERVER['PHP_SELF']."'>".$RELOAD."</a></div><table class='Uploadmanager'><tbody>";

                                    $form .= "<tr><th>".$FILENAME."</th><th>".$FILESIZE."</th><th style='text-align: right;'>".$DOWNLOAD."</th></tr>";



                                    // mostrarmos los archivos de cada usuario

                                    $dir = $destino.DS.$username;



                                    $directorio = @opendir($dir);



                                    while ($archivo = @readdir($directorio))

                                    {

                                        if($archivo != "." && $archivo != "..")

                                        {

                                            $form .= "<tr><td class='Uploadmanagerfile'>".$archivo."</td><td class='Uploadmanagersize'>".filesize($dir.DS.$archivo)." bytes</td><td class='Uploadmanagericon'><a href='".DS.$dir."/".$archivo."'><img src='/plugins/content/file_upload/images/download.png' alt='".$DOWNLOAD."' /></a></td></tr>";

                                        }

                                    }



                                    @closedir($directorio);



                                    $form .= "</tbody></table></div>\n";

                                }



				$form .= "<!-- ends file upload plugin -->";

			}

			else

			{

				// mostramos el formulario de envio de dropbox

				$form = "<!-- start file upload plugin -->\n<div class='formUploadContainer'><div id='formUpload'><form name='file_upload_form' method='post' action='' enctype='multipart/form-data'>

				<input type='file' class='$inputbox' name='file' size='$size' maxlenght='$max_size' /><input type='submit' name='enviar' value='$button_name' class='$class'>

				</div>

				</form>

				</div>\n

				<!-- ends file upload plugin -->";

			}



			//reemplazamos la aparicion de $regex por $form

			$article->text = preg_replace($regex, $form, $article->text);

		}



		$user	= & JFactory::getUser();

		$db	=& JFactory::getDBO();

		$username = $user->get('username');

		$guest	= $user->guest;

		$name	= $db->getEscaped($_POST['trailName']);

		$rtype	= $_POST['private'];

		$zoom	= '8';

		$uid	= $user->get('id');

		$intro	= $db->getEscaped($_POST['trailDesc']);

		$intro	= strlen($_POST['trailDesc'])>0?$intro:"--No Description--";

		



		if (isset($_POST['enviar']) && strlen($name)==0)

		{

			echo "<script type='text/javascript'>alert('Please enter a name for this trail');</script>";

			return JError::raiseWarning('', 'Please enter a name for this trail');

		}

		else if (isset($_POST['enviar']) && strlen($name)<6)

		{

			echo "<script type='text/javascript'>alert('Name should be longer than 6 chars');</script>";

			return JError::raiseWarning('', 'Name should be longer than 6 chars');

		}

		//Si no existe creamos un directorio en el raiz para nuestros archivos

		if (!is_dir('$destino')) 

		{

			@mkdir ($destino, 0775);

			//create html file to hide content of folder

			$myFile = $destino.'/'."index.html";

			$fh = fopen($myFile, 'w') or die("can't open file");

			$stringData = "<html><body bgcolor='#FFFFFF'></body></html>";

			fwrite($fh, $stringData);

			fclose($fh);

		}

		if(!is_dir('$username') && !$guest)

		{

			@mkdir ("$destino/$username", 0775);

			//create html file to hide content of folder

			$myFile = $destino.'/'.$username.'/'."index.html";

			$fh = fopen($myFile, 'w') or die("can't open file");

			$stringData = "<html><body bgcolor='#FFFFFF'></body></html>";

			fwrite($fh, $stringData);

			fclose($fh);

		}

		$arr = get_defined_vars();


		//Si es un invitado le invitaremos a loguearse

		if (!$guest)

		{

			//copiamos el archivo a la ruta definida en los parametros del plugin

			if (isset($_POST['enviar']) && $_FILES['file'] != "")

			{

				//comprobaremos que el tipo de archivo es permitido por la administracion

				$filename = JFile::makeSafe($_FILES['file']['name']);

				$tmpFile  = $_FILES['file']['tmp_name'] ;

				$extension= JFile::getExt($filename);



				if(in_array(JFile::getExt($filename), $tipos))

				{

					$send = @copy($_FILES['file']['tmp_name'], "$destino/$username/" . $_FILES['file']['name']);

					$url=$destino.'/'.$username.'/'.$_FILES['file']['name'];

					$flag = 0;

					if($dropbox == 0)

					{

						if ($extension === "kmz" || $extension === "KMZ")

						{

						     //http://bjw.co.nz/developer/php/62-php-unzip-an-uploaded-file-using-php

						     $zip = new ZipArchive;

						     $res = $zip->open($url);
//						     echo "<script type='text/javascript'>alert('asd'.$res);</script>";

						     if ($res === TRUE) {

							 $extdir = str_replace('.'.$extension, "", $url);

							 $extdir = str_replace(' ','-',$extdir);

							 $zip->extractTo($extdir);

							 //create html file to hide content of folder

							 $myFile = $extdir.'/'."index.html";

							 $fh = fopen($myFile, 'w') or die("can't open file");

							 $stringData = "<html><body bgcolor='#FFFFFF'></body></html>";

							 fwrite($fh, $stringData);

							 fclose($fh);



							 $desired_extension = 'kml';

							 $dir = opendir($extdir);

							 while(false != ($file = readdir($dir))) 

							 { 

							     if(($file != ".") and ($file != ".."))

							     {

							       $fileChunks = explode(".", $file);

							       if($fileChunks[1] == $desired_extension) //interested in second chunk only 

							       {

								  $url = $extdir.'/'.$file;

								  $flag = 1;

							       }

							     } 

							 }

							 if ($flag == 0)

							    echo "<script type='text/javascript'>alert('Could not find any KML data inside this archive.');</script>";

							 closedir($dir); 

							 

							 $zip->close();

						     } else {

							 echo "<script type='text/javascript'>alert('Could not extract data from the archive file.');</script>";

							 exit;

						     }

						}

//						else

						{

//						   $send = JFile::upload($tmpFile, $destino.DS.$username.DS.$filename);
							$xml = new DOMDocument();

							if (file_exists($url)) {

							    $dom->preserveWhiteSpace = FALSE;

							    $xml->load($url);

							    

							    $documents = $xml->getElementsByTagName( "Document" );

							    $count = 0;

							    $query = "";

							    $respoint = 0;

							    $resline = 0;

							    $pflag = 0;

							    $lflag = 0;

							    $docinfo = (array) null;

							    $coords = (array) null;

							    $vals = (array) null;

							    $pieces = (array) null;

							    $points = (array) null;

							    $mode = (array) null;

							    $parts = (array) null;



							    if ($documents->length > 0)

							       foreach( $documents as $document ) 

							       { 

								 $dataset = $document->getElementsByTagName( "Placemark" );

								 $checkLines = $document->getElementsByTagName( "LineString" );

								 $docinfo[$count][0] = $document->getElementsByTagName( "name" )->item(0)->nodeValue;

								 $docinfo[$count][1] = $document->getElementsByTagName( "LineString" )->length;

								 $docinfo[$count][2] = $document->getElementsByTagName( "Point" )->length;

								 $count++;

							       }

							    else

							    {

							       $dataset = $xml->getElementsByTagName( "Placemark" );

							       $checkLines = $xml->getElementsByTagName( "LineString" );

							    }

							    

							    //Store points of every linestring in array to help compare with waypoints

							    foreach ( $checkLines as $line )

							    {

							       $coords = preg_split("/[\s]+/", str_replace("\n", " ", $line->getElementsByTagName('coordinates')->item(0)->nodeValue));

							       $vals = array_merge($vals, $coords);

							    }



							    foreach( $dataset as $row ) 

							    {

								$xmlLines = $row->getElementsByTagName( "LineString" );

								$xmlPoint = $row->getElementsByTagName( "Point" );

								$name2 = $row->getElementsByTagName( "name" )->item(0)->nodeValue;

								$description = $row->getElementsByTagName( "description" )->item(0)->nodeValue;

								//http://stackoverflow.com/questions/138313/how-to-extract-img-src-title-and-alt-from-html-using-php

								$img = array();

								$links = "";

								preg_match_all('/<img[^>]+>/i',$description, $result);

								foreach( $result as $img_tag)

								{

								    for ($j=0; $j<count($img_tag);$j++)

								    {

								       //http://stackoverflow.com/questions/138313/how-to-extract-img-src-title-and-alt-from-html-using-php

								       preg_match_all('/(src)=("[^"]*")/i',$img_tag[$j], $img);

								       $links = $links . "<a href=".$img[2][0]." target=\"_blank\" alt=\"".$name2."\">Image link ".($j+1)."</a><br />";

								    }

								}

								$stripped_desc = preg_replace('/<img[^>]+\>/i',"", $description);

								if (strlen ($links)>0)

								   $stripped_desc = $stripped_desc . "<br /><h3>Images</h3>" . $links;

								

//							        echo "<script type='text/javascript'>alert('".$links."');</script>";

								if ($xmlLines->length > 0)

								{

								    $lflag = 1;

								    $temp = (array) null;

								    $coordinates = trim($xmlLines->item(0)->getElementsByTagName('coordinates')->item(0)->nodeValue);

								    $coordinates = str_replace("\n", " ", $coordinates);

								    $temp = preg_split("/[\s]+/", mysql_escape_string($coordinates));

								    if (strlen ( trim($temp[0])) == 0)

								       array_splice( $temp, 0, 1 );

								    if (strlen ( trim($temp[count($temp)-1])) == 0)

								       array_splice( $temp, count($temp)-1, 1 );

								    $pieces = array_merge($pieces, $temp);

								}

								if ($xmlPoint->length > 0)

								{

								    $pflag = 1;

								    $part = "";

								    $coordinates = str_replace("\n", " ", mysql_escape_string($xmlPoint->item(0)->getElementsByTagName('coordinates')->item(0)->nodeValue));

								    $temp = explode(",", mysql_escape_string($coordinates));

								    if (strlen ( trim($temp[0])) == 0)

								       array_splice( $temp, 0, 1 );

								    if (strlen ( trim($temp[count($temp)-1])) == 0)

								       array_splice( $temp, count($temp)-1, 1 );



								    //If point is part of any trail segment, mark it

								    if (in_array($coordinates, $vals))

								       $datatype = '1';

								    else

								       $datatype = '0';



								    $lat = $temp[1];

								    $lng = $temp[0];

								    $part = "(\"(" . $lat .','. $lng.")\", ".$lat.", ".$lng.", ".$uid.", \"".$name2."\", \"".$db->getEscaped($stripped_desc)."\", ".$datatype.")";

								    array_push($parts, $part);

								}

							    }

							    $temp2 = (array) null;

							    if ($lflag == 1)

							    {

								$i = 0;

								$distance = 0;

								foreach( $pieces as $modepoint )

								{

									$pnt = explode (",", trim($modepoint));

									if ($i > 0)

									{

									    $lat1 = $templat;

									    $lat2 = $pnt[1];

									    $lon1 = $templng;

									    $lon2 = $pnt[0];

									    //http://www.web-max.ca/PHP/misc_2.php

									    $milecon = 1.609344;

									    $distance = $distance + ($milecon*3958*3.1415926*sqrt(($lat2-$lat1)*($lat2-$lat1) + cos($lat2/57.29578)*cos($lat1/57.29578)*($lon2-$lon1)*($lon2-$lon1))/180);

									}

									$templat = $pnt[1];

									$templng = $pnt[0];

									$var = "(".$pnt[1].",".$pnt[0].",";

									$var2 = strlen(str_replace("\\n","",$pnt[2]))>0?str_replace("\\n","",$pnt[2]).")":"0.0".")";

									$temp2[$i] = $var . $var2;

									$i = $i + 1;

									if (in_array(trim($modepoint), $points))

									    array_push($mode, '2');

									else

									    array_push($mode, '0');

								}

//	echo "<script type='text/javascript'>alert('".mysql_escape_string(join("</marker><marker>",$temp2))."');</script>";

//	echo "count - ".mysql_escape_string(count($temp2));



	   						       $start = $temp2[0];

	    						       $XML = "<route><marker>".join("</marker><marker>",$temp2)."</marker></route>";
							       $center = $start;

							       $detailXML = "<mode>".join("</mode><mode>",$mode)."</mode>";



							       $polymod = array ();

							       $polyline = array();

							       $polycheck = array ();

							       $longpolyline = array();

							       $string = str_replace("<route><marker>(", "", $XML);

							       $string = str_replace(")</marker></route>", "", $string);

							       $points = explode(")</marker><marker>(", $string);

							       if ($rtype == 100)

							         $private = 1;

							       else

							         $private = 0;



							       for ( $i = 0; $i < count($points); $i++)

							       {

								 $temp = explode (",",$points[$i]);

								 $polymod[$i][0] = trim($temp[0]);

								 $polymod[$i][1] = trim($temp[1]);

								 $polycheck[$i] = new GeoPoint($temp[1],$temp[0]);

							       }

							       $reducer = new PolylineReducer($polycheck);

							       $simple_line = $reducer->SimplerLine(0.007);

							       $polyline = dpEncode($polymod);



							       if (strlen ($polyline[0]) > 2048)

							       {

								 $longpolyline = $polyline;

								 for ( $i = 0; $i < count($simple_line); $i++)

								 {

								    $polyshort[$i][0] = $simple_line[$i]->longitude;

								    $polyshort[$i][1] = $simple_line[$i]->latitude;

								 }

								 $polyline = dpEncode($polyshort);

							       }

							       else

							       {

								 $longpolyline = "";

							       }



							       $query = "INSERT INTO jos_trailList (" .

										"name," .

										"routeXML," .

										"routeStart," .

										"mapCenter," .

										"zoomLevel," .

										"userId," .

										"intro," .

										"length," .

										"private," .

										"createTime," .

										"detailXML," .

										"encodeurl," .

										"longurl," .

										"upload)" .

									 " VALUES (" .

									 $db->quote($name) . ", " .

									 $db->quote( $db->getEscaped($XML)) . ", ".

									 $db->quote($start) . ", " .

									 $db->quote($center) . ", " .

									 $db->quote($zoom) . ", " .

									 $db->quote($uid) . ", " .

									 $db->quote($db->getEscaped($intro)) . ", " .

									 $db->quote($distance) . ", " .

									 $db->quote($private) . ", " .

									 "NOW() , " .

									 $db->quote( $db->getEscaped($detailXML)) . ", " .

									 $db->quote( $db->getEscaped($polyline[0])) . ", " .

									 $db->quote( $db->getEscaped($longpolyline[0])) . ", " .

									 '2' . ");";



							       $result = mysql_query($query);
							       if (!$result) {

								 die('Invalid query : ' . $query);

							       }



							       $lastid = mysql_insert_id();

							       $xml = '<?xml version="1.0"?>' . $db->getEscaped($XML);

							       $dom = new DomDocument;

							       $dom -> loadXML($xml);

							       $nodes = $dom->documentElement->getElementsByTagName('marker');

							       $query = "INSERT INTO jos_trailDetail (Trail_ID , Lat , Lng) VALUES ";

							       foreach ($nodes as $node) {

							    	$elements = explode(",", trim(trim($node->nodeValue,"("),")"));

							    	$query = $query . "\n(" . $lastid . ", " . $elements[0] . ", " . $elements[1] . "), ";

							       }

							       $result = mysql_query(substr ($query , 0, strlen ($query) - 2));

							       if (!$result)

							       {

								   $query2 = "DELETE FROM jos_trailList WHERE id = " . $lastid . ";";

								   mysql_query($query2);

								   $resline = 0;

							       }

							       else

								   $resline = 1;

							    }

							    if ($pflag == 1)

							    {

							       $query = "INSERT INTO jos_pointInfo (point, lat, lng, userid, label, descr, type ) VALUES \n" . join(",\n", $parts) . ';';

							       $result = mysql_query($query);

							       if (!$result) {

								 $respoint = 0;

							         die('Invalid query : ' . $query);

							       }

							       else

								 $respoint = 1;

							    }



							    if ($lflag == 0 && $pflag == 0 )

								echo "<script type='text/javascript'>alert('Sorry, the KML uploader could not handle the data you uploaded.\\nSend this file to the admin (admin@tripnaksha.com) so that it can be uploaded!');</script>";



							    if ($respoint == 0 && $resline == 0) {

							      echo "<script type='text/javascript'>alert('There was an error storing this route. Please try again or notify the admin (admin@tripnaksha.com).".  $lastid."' );</script>";

							      die('Invalid query : ' . $query);

							    }

							    else if ($respoint == 1 && $resline == 1)

							    {

							      $tname = preg_replace('{[ \t]+}', '-', $name);

							      echo "<script type='text/javascript'>SqueezeBox.fromElement('index.php?option=com_addltrailinfo&tmpl=component&trailid=".$lastid."&trailname=".$tname."', {handler: 'iframe', size: {x: 600, y: 500}});</script>";

							      echo "<script type='text/javascript'>alert('Route information saved!');</script>";

//							      echo "<script type='text/javascript'>window.addEvent('domready', function() {setTimeout(function(){window.parent.parent.location='index.php?option=com_routes&view=traildisplay&tview=$lastid:$name';}, 50);});</script>";

							    }

							    else if ($respoint == 1 || $resline == 0)

							    {

							      echo "<script type='text/javascript'>alert('Point information saved!');</script>";

							      echo "<script type='text/javascript'>window.addEvent('domready', function() {setTimeout(function(){window.parent.parent.location='index.php?option=com_routes&view=map';}, 50);});</script>";

							    }

							    else if ($respoint == 0 || $resline == 1)

							    {

							      $tname = preg_replace('{[ \t]+}', '-', $name);

	//						      echo "<script type='text/javascript'>SqueezeBox.fromElement('index.php?option=com_addltrailinfo&tmpl=component&trailid=".$lastid."&trailname=".$tname."', {handler: 'iframe', size: {x: 600, y: 500}});</script>";

							      echo "<script type='text/javascript'>alert('Route saved!');</script>";

//							      echo "<script type='text/javascript'>window.addEvent('domready', function() {setTimeout(function(){window.parent.parent.location='index.php?option=com_routes&view=traildisplay&tview=$lastid:$name';}, 50);});</script>";
//							      header("Location: index.php?option=com_routes&view=traildisplay&tview=$lastid:$name");

	//						      echo "<script type='text/javascript'>window.addEvent('domready', function() {setTimeout(function(){window.parent.parent.location='index.php?option=com_traildisplay&Itemid=1&tview=$lastid&trailname=$name';}, 50);});</script>";

							    }



							} else {

							    exit('Failed to open the input file.');

							}

						}

					}

					else

					{

						require_once(JPATH_PLUGINS.DS."content".DS."file_upload".DS."DropboxUploader.php");



						try

						{

        					// Rename uploaded file to reflect original name

        					if ($_FILES['file']['error'] !== UPLOAD_ERR_OK)

                                                    throw new Exception($error);



        					$tmpDir = uniqid('tmp/DropboxUploader-');

        					if (!mkdir($tmpDir))

                                                    throw new Exception(JText::_('NODIR'));



       						if ($_FILES['file']['name'] === "")

                                                    throw new Exception(JText::_('NOBROWSE'));



        					$tmpFile = $tmpDir.'/'.str_replace("/\0", '_', $_FILES['file']['name']);

        					if (!move_uploaded_file($_FILES['file']['tmp_name'], $tmpFile))

                                                    throw new Exception(JText::_('NORENAME'));



						// Upload through dropbox api

        					$uploader = new DropboxUploader($dropuser, $droppass);

						$uploader->setCaCertificateFile(JPATH_PLUGINS.DS."content".DS."file_upload".DS."Equifax_Secure_Global_eBusiness_CA-1.cer");

        					$send     = $uploader->upload($tmpFile, $dropfolder."/".$username);

    						} catch(Exception $e) {

                                                    echo '<span style="color: red">Error: ' . htmlspecialchars($e->getMessage()) . '</span>';



							// Clean up

							if (isset($tmpFile) && file_exists($tmpFile))

							unlink($tmpFile);



							if (isset($tmpDir) && file_exists($tmpDir))

							rmdir($tmpDir);

    					}

					}

					//Si se envio mostramos un aviso y enviamos el mail

					if($send)

					{

                                            if($alerts == 1)

                                            {

						$app = JFactory::getApplication();

                                                $app->enqueueMessage( $SUCCESS );

                                            }



						if($admin_mail == 1)

						{

							//enviamos un mensaje al administrador/es del sitio cada vez que llega un mensaje

							$mail =& JFactory::getMailer();



							$config =& JFactory::getConfig();

							$mail->addRecipient( $config->getValue( 'config.mailfrom' ) );

                                                        $mail->addRecipient($recipient);

							$mail->setSubject( JText::_('SUBJECT') . $site );

							$mail->setBody( $username  . "&nbsp;" . JText::_('MAILBODY') );

                                                        $mailer->IsHTML($ishtml);



							$mail->Send();

							//si todo es correcto creamos un aviso

						}

					}



					if(!$send)

					{

                                            if($alerts == 1)

                                            {

						JError::raiseWarning( 100, $ERROR );

                                            }

                                            else

                                            {

                                                echo "<script type='text/javascript'>alert('$ERROR')</script>";

                                            }

					}

				}

				else

				{

                                    if($alerts == 1)

                                    {

					//si el tipo de archivo no es correcto creamos un aviso

					JError::raiseWarning( 100, $MIME_FAIL );

                                    }

                                    else

                                    {

                                        echo "<script type='text/javascript'>alert('$MIME_FAIL')</script>";

                                    }

				}

			}

		}



	}

        }



}

?>
