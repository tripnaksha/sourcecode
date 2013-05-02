<?php
/**
* OSOLCaptcha Plugin for Joomla 1.5
* @version $Id: osolcaptcha.php $
* @package: OSOLCaptcha 
* ===================================================
* @author
* Name: Sreekanth Dayanand, www.outsource-online.net
* Email: joomla@outsource-online.net
* Url: http://www.outsource-online.net
* ===================================================
* @copyright (C) 2010 Sreekanth Dayanand, Outsource Online (www.outsource-online.net). All rights reserved.
* @license see http://www.gnu.org/licenses/gpl-2.0.html  GNU/GPL.
* You can use, redistribute this file and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation.
*/

defined('_JEXEC') or die();


class plgSystemOSOLCaptcha extends JPlugin
{

		var $bgColor = "#000000";
		var $textColor = "#ff0000";
		
		//var $params;
		var $botScoutProtection  = '';
		var $botscoutAPIKey  = '';
		var $redirectURLforSuspectedIPs  = '';
		var $reportBotscoutNegativeMail='';
        function display()
		{
			$plugin 	=& JPluginHelper::getPlugin('system', 'osolcaptcha');
			$this->params   	= new JParameter($plugin->params);
			$imageFunction = 'create_image'.$this->params->get('imageFunction');//JRequest::getVar('imageFunction','');
			$imageFunction = ((!method_exists($this,$imageFunction)))?'create_imageAdv':$imageFunction;
		    //echo $this->params->get('imageFunction');exit;
			 
		   $this->$imageFunction();
		   exit;
		

		return true;
	}
	function setColors()
	{
			$plugin 	=& JPluginHelper::getPlugin('system', 'osolcaptcha');
			$this->params   	= new JParameter($plugin->params);
			$this->bgColor  = $this->params->get('bgColor',$this->bgColor);
			$this->textColor  = $this->params->get('textColor',$this->textColor);
	}
	
	
	// generates distorted letters ,this is a revised version of a method used in kcaptcha
	#http://www.phpclasses.org/browse/package/3193.html
		
	# Copyright by Kruglov Sergei, 2006, 2007, 2008
	# www.captcha.ru, www.kruglov.ru
	
	# System requirements: PHP 4.0.6+ w/ GD
	
	# KCAPTCHA is a free software. You can freely use it for building own site or software.
	# If you use this software as a part of own sofware, you must leave copyright notices intact or add KCAPTCHA copyright notices to own.
	function create_imageAdv(){

		$alphabet = "0123456789abcdefghijklmnopqrstuvwxyz";
		$allowed_symbols = "23456789abcdeghkmnpqsuvxyz";
		
		$length = 5;
		$width = 120;
		$height = 60;
		$fluctuation_amplitude = 5;
		$no_spaces = true;
		$this->setColors();
		$foreground_color = $this->HexToRGB($this->textColor) ;//array(255,255,255);//array(180, 180, 180);//array(255,255,255);//
		$background_color = $this->HexToRGB($this->bgColor) ;//array(44,127,7);//array(53,3,0);//array(246, 246, 246);//array(0,0,0);//
		$jpeg_quality = 90;
		
	
		$alphabet_length=strlen($alphabet);
		
		do{
			// generating random keystring
			while(true){
				$this->keystring='';
				for($i=0;$i<$length;$i++){
					$this->keystring.=$allowed_symbols{mt_rand(0,strlen($allowed_symbols)-1)};
				}
				if(!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $this->keystring)) break;
			}
		
			$font_file=$font_file=dirname(__FILE__).DS.'osolCaptcha'.DS.'adlibBT.png';
			$font=imagecreatefrompng($font_file);
			imagealphablending($font, true);
			$fontfile_width=imagesx($font);
			$fontfile_height=imagesy($font)-1;
			$font_metrics=array();
			$symbol=0;
			$reading_symbol=false;

			// loading font
			for($i=0;$i<$fontfile_width && $symbol<$alphabet_length;$i++){
				$transparent = (imagecolorat($font, $i, 0) >> 24) == 127;

				if(!$reading_symbol && !$transparent){
					$font_metrics[$alphabet{$symbol}]=array('start'=>$i);
					$reading_symbol=true;
					continue;
				}

				if($reading_symbol && $transparent){
					$font_metrics[$alphabet{$symbol}]['end']=$i;
					$reading_symbol=false;
					$symbol++;
					continue;
				}
			}

			$img=imagecreatetruecolor($width, $height);
			imagealphablending($img, true);
			$white=imagecolorallocate($img, 255, 255, 255);
			$black=imagecolorallocate($img, 0, 0, 0);

			imagefilledrectangle($img, 0, 0, $width-1, $height-1, $white);

			// draw text
			$x=1;
			for($i=0;$i<$length;$i++){
				$m=$font_metrics[$this->keystring{$i}];

				$y=mt_rand(-$fluctuation_amplitude, $fluctuation_amplitude)+($height-$fontfile_height)/2+2;

				if($no_spaces){
					$shift=0;
					if($i>0){
						$shift=10000;
						for($sy=7;$sy<$fontfile_height-20;$sy+=1){
							for($sx=$m['start']-1;$sx<$m['end'];$sx+=1){
				        		$rgb=imagecolorat($font, $sx, $sy);
				        		$opacity=$rgb>>24;
								if($opacity<127){
									$left=$sx-$m['start']+$x;
									$py=$sy+$y;
									if($py>$height) break;
									for($px=min($left,$width-1);$px>$left-12 && $px>=0;$px-=1){
						        		$color=imagecolorat($img, $px, $py) & 0xff;
										if($color+$opacity<190){
											if($shift>$left-$px){
												$shift=$left-$px;
											}
											break;
										}
									}
									break;
								}
							}
						}
						if($shift==10000){
							$shift=mt_rand(4,6);
						}

					}
				}else{
					$shift=1;
				}
				imagecopy($img, $font, $x-$shift, $y, $m['start'], 1, $m['end']-$m['start'], $fontfile_height);
				$x+=$m['end']-$m['start']-$shift;
			}
		}while($x>=$width-10); // while not fit in canvas

		$center=$x/2;

		
		$img2=imagecreatetruecolor($width, $height);
		$foreground=imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2]);
		$background=imagecolorallocate($img2, $background_color[0], $background_color[1], $background_color[2]);
		imagefilledrectangle($img2, 0, 0, $width-1, $height-1, $background);		
		imagefilledrectangle($img2, 0, $height, $width-1, $height+12, $foreground);
		

		// periods
		$rand1=mt_rand(750000,1200000)/10000000;
		$rand2=mt_rand(750000,1200000)/10000000;
		$rand3=mt_rand(750000,1200000)/10000000;
		$rand4=mt_rand(750000,1200000)/10000000;
		// phases
		$rand5=mt_rand(0,31415926)/10000000;
		$rand6=mt_rand(0,31415926)/10000000;
		$rand7=mt_rand(0,31415926)/10000000;
		$rand8=mt_rand(0,31415926)/10000000;
		// amplitudes
		$rand9=mt_rand(330,420)/110;
		$rand10=mt_rand(330,450)/110;

		//wave distortion

		for($x=0;$x<$width;$x++){
			for($y=0;$y<$height;$y++){
				$sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$width/2+$center+1;
				$sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

				if($sx<0 || $sy<0 || $sx>=$width-1 || $sy>=$height-1){
					continue;
				}else{
					$color=imagecolorat($img, $sx, $sy) & 0xFF;
					$color_x=imagecolorat($img, $sx+1, $sy) & 0xFF;
					$color_y=imagecolorat($img, $sx, $sy+1) & 0xFF;
					$color_xy=imagecolorat($img, $sx+1, $sy+1) & 0xFF;
				}

				if($color==255 && $color_x==255 && $color_y==255 && $color_xy==255){
					continue;
				}else if($color==0 && $color_x==0 && $color_y==0 && $color_xy==0){
					$newred=$foreground_color[0];
					$newgreen=$foreground_color[1];
					$newblue=$foreground_color[2];
				}else{
					$frsx=$sx-floor($sx);
					$frsy=$sy-floor($sy);
					$frsx1=1-$frsx;
					$frsy1=1-$frsy;

					$newcolor=(
						$color*$frsx1*$frsy1+
						$color_x*$frsx*$frsy1+
						$color_y*$frsx1*$frsy+
						$color_xy*$frsx*$frsy);

					if($newcolor>255) $newcolor=255;
					$newcolor=$newcolor/255;
					$newcolor0=1-$newcolor;

					$newred=$newcolor0*$foreground_color[0]+$newcolor*$background_color[0];
					$newgreen=$newcolor0*$foreground_color[1]+$newcolor*$background_color[1];
					$newblue=$newcolor0*$foreground_color[2]+$newcolor*$background_color[2];
				}

				imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
			}
		}
		
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
		header('Cache-Control: no-store, no-cache, must-revalidate'); 
		header('Cache-Control: post-check=0, pre-check=0', FALSE); 
		header('Pragma: no-cache');
		
		if(function_exists("imagejpeg")){
			header("Content-Type: image/jpeg");
			imagejpeg($img2, null, $jpeg_quality);
		}else if(function_exists("imagegif")){
			header("Content-Type: image/gif");
			imagegif($img2);



		}else if(function_exists("imagepng")){
			header("Content-Type: image/x-png");
			imagepng($img2);
		}
		$security_code = $this->keystring;
		//Set the session to store the security code
		//$_SESSION["security_code"] 
		$currentSession =  & JFactory::getSession() ;//&JSession::getInstance('none',array()); 
		$currentSession->set('securiy_code'.(JRequest::getVar('instanceNo')+0), $security_code);
		$width = 120;//100;
		$height = 40;//20;
		
		
	}
	// generates plain letters
	function create_imagePlane()
	{
		//Let's generate a totally random string using md5
		$md5_hash = md5(rand(0,999)); 
		//We don't need a 32 character long string so we trim it down to 5 
		$security_code = str_replace(array("0","O","o"), array("p"),substr($md5_hash, 15, 5)); 
		
		//Set the session to store the security code
		//$_SESSION["security_code"] 
		$currentSession =  & JFactory::getSession() ;//&JSession::getInstance('none',array()); 
		$currentSession->set('securiy_code'.(JRequest::getVar('instanceNo')+0), $security_code);
		$width = 120;//100;
		$height = 40;//20;
		$image = imagecreate($width, $height);  
		$this->setColors();
		$foreground_color = $this->HexToRGB($this->textColor) ;//array(255,255,255);//array(180, 180, 180);//array(255,255,255);//
		$background_color = $this->HexToRGB($this->bgColor) ;
		//We are making three colors, white, black and gray
		$white = imagecolorallocate ($image, $foreground_color[0],$foreground_color[1],$foreground_color[2]);//255, 255, 255);
		$black = imagecolorallocate ($image,$background_color[0],$background_color[1],$background_color[2]);//44,127,7);// imagecolorallocate ($image, 0, 0, 0);
		$grey = imagecolorallocate ($image, 204, 204, 204);
		
		//Make the background black 
		imagefill($image, 0, 0, $black); 
		//imagestring($image, 3, 30, 3, $security_code, $white);
		$size = 10;
		$this->ly = (int)(2.4 * $size);
		$x = 20;
		for($i=0;$i<strlen($security_code);$i++)
		{
			
			$angle = rand(-45,45);
			$y        = intval(rand((int)($size * 1.5), (int)($this->ly - ($size / 7))));
			
			@imagettftext($image, $size, $angle, $x + (int)($size / 15), $y, $white, dirname(__FILE__).DS.'osolCaptcha'.DS.'adlibBT.TTF', $security_code[$i]);
			$x += ($size *2);
		}
		//imageline($image, 0, $height/2, $width, $height/2, $grey); 
		//imageline($image, $width/2, 0, $width/2, $height, $grey); 
		header('Content-type: image/png');
		imagepng($image);
		//imagedestroy($image);
	}	

	function confirm( $word,$instanceNo='' )
	{
		
		$currentSession = & JFactory::getSession() ;

		$securiy_code = $currentSession->get('securiy_code'.$instanceNo);
		
		if ( $word == $securiy_code  &&  ($word != '')) 
		   //JError::raiseWarning("666",$word.'-t-'.$security_code);
		   return true;
		else
		   //JError::raiseWarning("666",$word.'-f-'.$security_code);
		   return false;  

	}

	// Function wrappers for TriggerEvent usage
	function onCaptcha_Display() {	
		return $this->display();	
	}

	function onCaptcha_confirm($word, &$return) {		
		$return = $this->confirm($word);
		return $return;
	}
	//declare the system events
	/**
         * Do something onAfterInitialise 
         */
       // function onAfterInitialise()
	   function onAfterRoute()
        {
			global $mainframe;
			//$this->botscoutCheck();
			JPlugin::loadLanguage( 'plg_system_osolcaptcha', JPATH_ADMINISTRATOR );
			//showCaptcha=True;
			$showCaptcha = JRequest::getVar('showCaptcha');
			if($showCaptcha == 'True')
			{
				return $this->display();
			}
		
			$return = false;
			$osolCatchaTxt = JRequest::getVar('osolCatchaTxt','');
			$osolCatchaTxtInst = JRequest::getVar('osolCatchaTxtInst','');
			
	
			/*$mainframe->triggerEvent('onCaptcha_confirm', array($osolCatchaTxt, &$return));
			if(!$return)*/
			//if(isset($_REQUEST[JUtility::getToken()])&& !$this->confirm($osolCatchaTxt))
			$option = JRequest::getVar('option');
			$task = JRequest::getVar('task');
			//$this->mailBotScoutResult();
			
			/**********************************************SECOND LEVEL SECURITY CHECK*******************************************************************/
				$secondLevelPass = true;
				$plugin 	=& JPluginHelper::getPlugin('system', 'osolcaptcha');
				$this->params   	= new JParameter($plugin->params);
				//echo  $this->params->get("enableSecondLevelSecurity");exit;
				/*echo (
					($option == 'com_contact' && $task=='submit')||
					($option == 'com_user' && isset($_REQUEST['task']) && $task != 'logout' )
				   )."<br />";
					
					echo $this->params->get("enableSecondLevelSecurity") == 'Yes' ."<br />";
					echo ($osolCatchaTxtInst == '' || $osolCatchaTxt == '');
					echo (
				   (
					($option == 'com_contact' && $task=='submit')||
					($option == 'com_user' && isset($_REQUEST['task']) && $task != 'logout' )
				   ) && 
				   $this->params->get("enableSecondLevelSecurity") == 'Yes' && 
				   ($osolCatchaTxtInst == '' || $osolCatchaTxt == '')
				  )."<br/>";
					echo "<pre>";print_r($_REQUEST);echo "</pre>";*/
				if(
				   (
					($option == 'com_contact' && $task=='submit')||
					($option == 'com_user' && isset($_REQUEST['task']) && $task != 'logout' )
				   ) && 
				   $this->params->get("enableSecondLevelSecurity") == 'Yes' && 
				   ($osolCatchaTxtInst == '' || $osolCatchaTxt == '')
				  )
				{
					
					//echo "HHH";;exit;
					$isEnabledForForm = $this->getIsEnabledForForms();
					
					/*"enableForContactUs"  
									"enableForComLogin" 
									"enableForRegistration"
									"enableForReset" 
									"enableForRemind" */
									
					switch($option)
					{
						case 'com_contact':
							if($isEnabledForForm["enableForContactUs"])
							{
								JRequest::setVar('task','');
								$secondLevelPass = false;
							}
							
							break;
						case 'com_user':
							
							if(JRequest::getVar('task') == 'login'  && $isEnabledForForm["enableForComLogin"])
							{
								JRequest::setVar('view','login');
								JRequest::setVar('task','');
								$secondLevelPass = false;
							}
							if(JRequest::getVar('task') == 'register_save'   && $isEnabledForForm["enableForRegistration"])
							{
								JRequest::setVar('view','register');
								JRequest::setVar('task','');
								$secondLevelPass = false;
							}
							if(JRequest::getVar('task') == 'remindusername'   && $isEnabledForForm["enableForRemind"])
							{
								JRequest::setVar('view','remind');
								JRequest::setVar('task','');
								$secondLevelPass = false;
							}
							if(JRequest::getVar('task') == 'requestreset'   && $isEnabledForForm["enableForReset"])
							{
								JRequest::setVar('view','reset');
								JRequest::setVar('task','');
								$secondLevelPass = false;
							}
							
							
							
							break;
					}
					//echo $secondLevelPass;exit;
					if(!$secondLevelPass)
					{
						$this->reportBotscoutNegativeMail  = $this->params->get('reportBotscoutNegativeMail',$this->reportBotscoutNegativeMail);
						if($this->reportBotscoutNegativeMail   !='')
						{
							$this->mailBotScoutResult();
						}
						JError::raiseWarning("666",JTEXT::_('OSOLCAPTCHA_ERROR_MESSAGE'));
					}
					
				}
			/*****************************************************************************************************************/
			//echo $option;		exit;
			//echo  $task;exit;

			if(in_array($option,array('com_contact','com_user')) && (isset($_REQUEST['task'])&& $task != 'logout' ) && (isset($_REQUEST['osolCatchaTxt']) && !$this->confirm($osolCatchaTxt,$osolCatchaTxtInst)))//."$osolCatchaTxt,$osolCatchaTxtInst".JFactory::getSession()->get('securiy_code'.$osolCatchaTxtInst)
			{
				
				//."$osolCatchaTxt,$osolCatchaTxtInst:".JFactory::getSession()->get('securiy_code'.$osolCatchaTxtInst)
				//"You have entered the wrong CAPTCHA sequence. Please try again.  <a href=\"javascript:history.go(-1);\">Go to Previous page</a>"
				JError::raiseWarning("666",JTEXT::_('OSOLCAPTCHA_ERROR_MESSAGE'));
				switch($option)
				{
					case 'com_contact':
						JRequest::setVar('task','');
						
						break;
					case 'com_user':
						
						if(JRequest::getVar('task') == 'login')
						{
							JRequest::setVar('view','login');
						}
						if(JRequest::getVar('task') == 'register_save')
						{
							JRequest::setVar('view','register');
						}
						if(JRequest::getVar('task') == 'remindusername')
						{
							JRequest::setVar('view','remind');
						}
						if(JRequest::getVar('task') == 'requestreset')
						{
							JRequest::setVar('view','reset');
						}
						
						
						JRequest::setVar('task','');
						break;
				}
				
			}
			elseif(isset($_REQUEST['osolCatchaTxt']) && !$this->confirm($osolCatchaTxt,$osolCatchaTxtInst))
			{
				//JError::raiseWarning("666","You have entered the wrong CAPTCHA sequence. Please try again.  <a href=\"javascript:history.go(-1);\">Go to Previous page</a>");
				JError::raiseWarning("666", JTEXT::_('OSOLCAPTCHA_ERROR_MESSAGE').JTEXT::_('OSOL_CAPTCHA_GO_BACK'));
				//JFactory::getApplication()
//				$mainframe->redirect(JURI::base());
				$mainframe->redirect(JFactory::getURI()->toString());
//				$mainframe->redirect('http://localhost'.JFactory::getURI()->toString());
			}
			else
			{
				$this->botscoutCheck();
			}
			//admin passphrase check
			//$pathArray = explode("/",JURI::base());
			$pathArray = preg_split("~/~",JURI::base());//fix provided by Gruz from ukraine on 5th september 2010

			
			$isAdmin = ($pathArray[(count($pathArray) - 2)] == "administrator");
			
			if($isAdmin  )
			{
				$currentSession =  & JFactory::getSession() ;//&JSession::getInstance('none',array()); 
				$sessOsolAdminPassPhrase = $currentSession->get('osolAdminPassPhrase','');
				$paramOsolPassPhrase =$this->params->get("adminPassPhrase");
				$osolPP = JRequest::getVar('osolPP','');
				/*echo  $sessOsolAdminPassPhrase . " | ". $osolPP . " | ".$paramOsolPassPhrase. " | <br />";
				echo "( $sessOsolAdminPassPhrase != $paramOsolPassPhrase)  = ".( $sessOsolAdminPassPhrase != $paramOsolPassPhrase) ."<br />";
				echo ($paramOsolPassPhrase !='')." && ".( $sessOsolAdminPassPhrase != $paramOsolPassPhrase) ." && ". ($osolPP!= $paramOsolPassPhrase )."<br />";
				echo "($paramOsolPassPhrase !='') && ( $sessOsolAdminPassPhrase != $paramOsolPassPhrase)  &&  ($osolPP!= $paramOsolPassPhrase ) :".($paramOsolPassPhrase !='') && ( $sessOsolAdminPassPhrase != $paramOsolPassPhrase)  &&  ($osolPP!= $paramOsolPassPhrase ) ."|";
				exit;*/
				if(($paramOsolPassPhrase !='') && ( $sessOsolAdminPassPhrase != $paramOsolPassPhrase)  &&  ($osolPP!= $paramOsolPassPhrase ) )
				{
					$liveSiteUserSide  = str_replace("/administrator/","/",JURI::base());
					$mainframe->redirect($liveSiteUserSide);
					
				}
				elseif($osolPP == $paramOsolPassPhrase )
				{
					
					$currentSession->set('osolAdminPassPhrase',$osolPP);
				}
			}
        }

        /**
         * Do something onAfterRoute 
         */
        /*function onAfterRoute()
        {
			//echo "onAfterRoute()<br />";
			
        }*/
		function GetCapthcaHTML($vertical = false)
		{
			JPlugin::loadLanguage( 'plg_system_osolcaptcha', JPATH_ADMINISTRATOR );
			if(!isset($GLOBALS['totalCaptchas']))
			{
				$GLOBALS['totalCaptchas'] = -1;
			}
			#JHTML::_('behavior.tooltip');

			$GLOBALS['totalCaptchas']++;
			$doc =& JFactory::getDocument();
			$cssFile = dirname(__FILE__).DS.'osolCaptcha'.DS.'captchaStyle.css';
			if(is_file($cssFile ) && !is_dir($cssFile))
			{
				$style = file_get_contents(dirname(__FILE__).DS.'osolCaptcha'.DS.'captchaStyle.css'); 
				$doc->addStyleDeclaration( $style );
			}

			return ("
					<!--div class=\"fields\">
					<label for=\"osolCatchaTxt{$GLOBALS['totalCaptchas']}\">".JText::_('ENTER CAPTCHA VALUE')."</label> 
					</div-->
					<div class=\"fields\">
	<img id=\"captchaCode".$GLOBALS['totalCaptchas']."\" src=\"".JURI::base()."index.php?showCaptcha=True&amp;instanceNo=".$GLOBALS['totalCaptchas']."\" alt=\"Captcha\" /> 
					</div>
					<div class=\"fields\">
			<label>
	<script language=\"javascript\" type=\"text/javascript\">
		//<![CDATA[
	function reloadCapthcha{$GLOBALS['totalCaptchas']}(instanceNo)
			{
				var captchaSrc = \"".JURI::base()."index.php?showCaptcha=True&instanceNo=\"+instanceNo+\"&time=\"+ new Date().getTime();
				document.getElementById('captchaCode'+instanceNo).src = captchaSrc ;
			}
			//]]>
			</script>
       <a href=\"#\" onclick=\"reloadCapthcha{$GLOBALS['totalCaptchas']}(".$GLOBALS['totalCaptchas'].");return false;\" style=\"font-size:12px;\">".JText::_('REFRESH CAPTCHA')."</a>
    </label>
					</div>
					<div class=\"fields\">

					<input type=\"text\" name=\"osolCatchaTxt\" id=\"osolCatchaTxt{$GLOBALS['totalCaptchas']}\" onblur=\"if(this.value=\"\") this.value='Enter text from above...'\" onclick=\"this.value=''\" value=\"Enter text from above...\" class=\"inputbox required validate-captcha\" style=\"color:gray\"/>&nbsp;
						<input type=\"hidden\" name=\"osolCatchaTxtInst\" id=\"osolCatchaTxtInst\"  value=\"".$GLOBALS['totalCaptchas']."\"   /><br/>
					</div>

</div>
");
		}
		function getEnabledForms()
		{
			return array(
									"enableForContactUs" => '<button class="button validate" type="submit">', 
									"enableForComLogin"  => '<input type="submit" name="Submit" class="button" value="'.JTEXT::_('LOGIN').'" />', 
									"enableForRegistration" => '<button class="button validate" type="submit">'.JTEXT::_('REGISTER').'</button>',
									"enableForReset" => '<button type="submit" class="validate">'.JText::_('Submit').'</button>',
									"enableForRemind" => '<button type="submit" class="validate">'.JText::_('Submit').'</button>',
									);
		}
		function getIsEnabledForForms()
		{
			$plugin 	=& JPluginHelper::getPlugin('system', 'osolcaptcha');
			$this->params   	= new JParameter($plugin->params);
			$enabledForms = $this->getEnabledForms();
			$isEnabledForForm = array();
			foreach($enabledForms as $paramName => $pattern)
			{
				//echo $paramName." = ".$this->params->get($paramName)."<br />";
				$isEnabledForForm[$paramName] = ($this->params->get($paramName) == 'Yes');
			}
			return $isEnabledForForm;
		}
        /**
         * Do something onAfterDispatch 
         */
        function onAfterDispatch()
        {
			
			$document = &JFactory::getDocument();//
			$content = $document->getBuffer('component');
			$option = JRequest::getVar('option');
			$view = JRequest::getVar('view');
			$captchaHTML = $this->GetCapthcaHTML();



			

			$plugin 	=& JPluginHelper::getPlugin('system', 'osolcaptcha');
 			/*$this->params   	= new JParameter($plugin->params);
			$enabledForms = array(
									"enableForContactUs" => '<button class="button validate" type="submit">', 
									"enableForComLogin"  => '<input type="submit" name="Submit" class="button" value="'.JTEXT::_('LOGIN').'" />', 
									"enableForRegistration" => '<button class="button validate" type="submit">'.JTEXT::_('REGISTER').'</button>',
									"enableForReset" => '<button type="submit" class="validate">'.JText::_('Submit').'</button>',
									"enableForRemind" => '<button type="submit" class="validate">'.JText::_('Submit').'</button>',
									);
			$isEnabledForForm = array();
			foreach($enabledForms as $paramName => $pattern)
			{
				//echo $paramName." = ".$this->params->get($paramName)."<br />";
				$isEnabledForForm[$paramName] = ($this->params->get($paramName) == 'Yes');
			}*/
			$enabledForms = $this->getEnabledForms();
			$isEnabledForForm = $this->getIsEnabledForForms();
			//echo "<pre>";print_r($isEnabledForForm);echo "</pre>";exit;
			$newContent = "";
			if(in_array($option,array('com_contact','com_user')))
			{
				switch($option)
				{
					case 'com_contact':
						//$checkContent = '<button class="button validate" type="submit">';
						if($isEnabledForForm["enableForContactUs"])
						{
							$checkContent = $enabledForms["enableForContactUs"];
							
						}
						
						break;
					case 'com_user':
						switch($view)
						{
							case 'register':
								if($isEnabledForForm["enableForRegistration"])
								{
									$checkContent = $enabledForms["enableForRegistration"];
									
								}
								//$checkContent = array('<button class="button validate" type="submit">'.JTEXT::_('REGISTER').'</button>','<input type="submit" name="Submit" class="button" value="'.JTEXT::_('LOGIN').'" />');
								break;
							case 'login':
								if($isEnabledForForm["enableForComLogin"])
								{
									$checkContent = $enabledForms["enableForComLogin"];
									
								}
								break;
							case 'remind':
								//echo $isEnabledForForm["enableForComRemind"]
								if($isEnabledForForm["enableForRemind"])
								{
									$checkContent = $enabledForms["enableForRemind"];
									
									
								}
								break;
							case 'reset':
								if($isEnabledForForm["enableForReset"] && JRequest::getVar('layout','') == '')
								{
									$checkContent = $enabledForms["enableForReset"];
									
								}
								break;
							
						}
						
						break;
				}
				
				/*if(is_array($checkContent ))
				{
					//echo "<pre>";print_r($checkContent);echo "</pre>";exit;
					foreach($checkContent as $checkControll)
					{
						if(strstr($content,$checkControll))
						{
							unset($checkContent);
							$checkContent = $checkControll;
							break;
						}
					}
				}*/
				$newContent = str_replace($checkContent,$captchaHTML.$checkContent,$content);
			}
			if($newContent!="")
			{
				$document->setBuffer($newContent,'component');
			}
			
			//now check for login module,add the possition here if the login module is not in any of the following
			$plugin 	=& JPluginHelper::getPlugin('system', 'osolcaptcha');
		    $this->params   	= new JParameter($plugin->params);
			if($this->params->get('enableForModules') == 'Yes')// && $option!= 'com_user')
			{
				/*$positions = preg_match_all('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', file_get_contents($data), $matches)
				$type  = $matches[1][$i];
				echo $type.$name."<br />";*/
				foreach(array('left','right','top','user2','user3') as $pos)
				{
					
					//$document->setBuffer('false' ,'modules',$pos);
					$modContent = $document->getBuffer('modules',$pos);
					$loginModuleSearch ='<input type="submit" name="Submit" class="button" value="'.JTEXT::_('LOGIN').'" />';
					
					if(strstr($modContent,$loginModuleSearch)!=false)
					{
						//echo $pos.'true<br />';
					
						
						$modContent = str_replace($loginModuleSearch ,$this->GetCapthcaHTML(true).$loginModuleSearch,$modContent);
						
						
						$document->setBuffer($modContent ,'modules',$pos);
						
						//$document->getBuffer('modules',$pos);//for some reason this method needs  to be called to show the captcha
					}
					
					//echo "onAfterDespatch()<br />";
					//echo "<pre>";print_r($modloginContent);echo "</pre>";
				}
			}
        }
		function changeMod($modContent ,$pos)
		{
						$document2 = &JFactory::getDocument();//
						$document2->setBuffer($modContent ,'modules',$pos);
		}
        /**
         * Do something onAfterRender 
         */
        function onAfterRender()
        {
			global $mainframe;
			
			//echo "<pre>";print_r($document->setBuffer('fff','component'));echo "</pre>";
			//echo "onAfterRender()<br />";
        }
		/*
			Usage
			<?php 
				//set the argument below to true if you need to show vertically( 3 cells one below the other)
				JFactory::getApplication()->triggerEvent('onShowOSOLCaptcha', array(false)); 
			?>
			*/
		function onShowOSOLCaptcha($isVertical)
		{
			
			echo $this->GetCapthcaHTML($isVertical);
		}
		
		function HexToRGB($hex) {
			$hex = ereg_replace("#", "", $hex);
			$color = array();
			
			if(strlen($hex) == 3) {
				$color['r'] = hexdec(substr($hex, 0, 1) . $r);
				$color['g'] = hexdec(substr($hex, 1, 1) . $g);
				$color['b'] = hexdec(substr($hex, 2, 1) . $b);
			}
			else if(strlen($hex) == 6) {
				$color['r'] = hexdec(substr($hex, 0, 2));
				$color['g'] = hexdec(substr($hex, 2, 2));
				$color['b'] = hexdec(substr($hex, 4, 2));
			}
			
			return array_values($color);
		}
	
		function RGBToHex($r, $g, $b) {
			$hex = "#";
			$hex.= dechex($r);
			$hex.= dechex($g);
			$hex.= dechex($b);
			
			return $hex;
		}
	function botscoutCheck()
	{
		/////////////////////////////////////////////////////
		// sample API code for use with the BotScout.com API
		// code by MrMike / version 2.0 / LDM 2-2009 
		/////////////////////////////////////////////////////
		
		/////////////////// START CONFIGURATION ////////////////////////
		// use diagnostic output? ('1' to use, '0' to suppress)
		// (normally set to '0')
		global $mainframe;
		$currentSession =  & JFactory::getSession() ;//&JSession::getInstance('none',array()); 
		$botscoutCheckdone = $currentSession->get('botscoutCheckdone');
		$plugin 	=& JPluginHelper::getPlugin('system', 'osolcaptcha');
		$this->params   	= new JParameter($plugin->params);
		$this->botScoutProtection  = $this->params->get('botScoutProtection',$this->botScoutProtection);
		$this->botscoutAPIKey  = $this->params->get('botscoutAPIKey',$this->botscoutAPIKey);
		$this->redirectURLforSuspectedIPs  = $this->params->get('redirectURLforSuspectedIPs',$this->redirectURLforSuspectedIPs);
		$this->reportBotscoutNegativeMail  = $this->params->get('reportBotscoutNegativeMail',$this->reportBotscoutNegativeMail);
		
		if((!isset($_REQUEST['email'])) || $this->botScoutProtection == 'Disable' || $this->botscoutAPIKey == '')
		{
			return;
		}
		
		$diag = '0';
		
		/////////////////// END CONFIGURATION ////////////////////////
		
		
		////////////////////////
		// test values 
		// an email value...a bot, perhaps?
		// these would normally come from your 
		// web form or registration form code 
			
		$XMAIL = $_REQUEST['email'];
		
		// an IP address
		$XIP = $_SERVER['REMOTE_ADDR'];
		
		// a name, maybe a bot?
		$XNAME = '';
		
		////////////////////////
		// your optional API key (don't have one? get one here: http://botscout.com/
		$APIKEY=$this->botscoutAPIKey;
		
		$USEXML = 0;
		
		////////////////////////
		
		// sample query strings - you'd dynamically construct this 
		// string and use it as in the example below - these examples use the optional API 'key' field 
		// for more information on using the API key, please visit http://botscout.com
		
		// in most cases the BEST test is to use the "MULTI" query and test for the IP and email
		//$multi_test = "http://botscout.com/test/?multi&mail=$XMAIL&ip=$XIP&key=$APIKEY";
		
		/* you can use these but they're much less efficient and (possibly) not as reliable
		$test_string = "http://botscout.com/test/?mail=$XMAIL&key=$APIKEY";	// test email - reliable
		$test_string = "http://botscout.com/test/?ip=$XIP&key=$APIKEY";		// test IP - reliable
		$test_string = "http://botscout.com/test/?name=$XNAME&key=$APIKEY";	// test name (unreliable!)
		$test_string = "http://botscout.com/test/?all=$XNAME&key=$APIKEY";	// test all (see docs)
		*/
		
		// make the url compliant with urlencode()
		$XMAIL = urlencode($XMAIL);
		
		// for this example we'll use the MULTI test 
		$test_string = "http://botscout.com/test/?multi&mail=$XMAIL&ip=$XIP";
		
		// are using an API key? If so, append it.
		if($APIKEY != ''){
			$test_string = "$test_string&key=$APIKEY";
		}
		
		// are using XML responses? If so, append the XML format key.
		if($USEXML == '1'){
			$test_string = "$test_string&format=xml";
		}
		
		////////////////////////
		if($diag=='1'){print "Test String: $test_string";}
		////////////////////////
		
		
		////////////////////////
		// use file_get_contents() or cURL? 
		// we'll user file_get_contents() unless it's not available 
		
		if(function_exists('file_get_contents')&& (ini_get('allow_url_fopen')=='On')){
			// Use file_get_contents
			$data = file_get_contents($test_string);
		}else{
			$ch = curl_init($test_string);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$returned_data = curl_exec($ch);
			curl_close($ch);
		}
		
		// diagnostic output 
		if($diag=='1'){
			print "RETURNED DATA: $returned_data";
			// sanity check 
			if($returned_data==''){ print 'Error: No return data from API query.'; exit; } 
		} 
		
		
		// take the returned value and parse it (standard API, not XML)
		$botdata = explode('|', $returned_data); 
		
		// sample 'MULTI' return string 
		// Y|MULTI|IP|4|MAIL|26|NAME|30
		
		// $botdata[0] - 'Y' if found in database, 'N' if not found, '!' if an error occurred 
		// $botdata[1] - type of test (will be 'MAIL', 'IP', 'NAME', or 'MULTI') 
		// $botdata[2] - descriptor field for item (IP)
		// $botdata[3] - how many times the IP was found in the database 
		// $botdata[4] - descriptor field for item (MAIL)
		// $botdata[5] - how many times the EMAIL was found in the database 
		// $botdata[6] - descriptor field for item (NAME)
		// $botdata[7] - how many times the NAME was found in the database 
		//$mainframe->redirect($this->redirectURLforSuspectedIPs);
		if($botdata[0] == 'Y'){
			
			//$this->botScoutProtection  = $this->params->get('botScoutProtection',$this->botScoutProtection);//Disable,Redirect,Stop
			//$this->redirectURLforSuspectedIPs  = $this->params->get('redirectURLforSuspectedIPs',$this->redirectURLforSuspectedIPs);
			if($this->reportBotscoutNegativeMail  !='')
			{
				$this->mailBotScoutResult();
			}
			if($this->botScoutProtection == 'Redirect')
			{
				$mainframe->redirect($this->redirectURLforSuspectedIPs);
				
			}
			else //Stop
			{
			}
			
			exit;
			
		}
		$currentSession->set('botscoutCheckdone', 1);
		if(($diag=='1') && substr($returned_data, 0,1) == '!'){
			// if the first character is an exclamation mark, an error has occurred  
			print "Error: $returned_data";
			exit;
		}
		
		
		// this example tests the email address and IP to see if either of them appear 
		// in the database at all. Either one is a fairly good indicator of bot identity. 
		if($botdata[3] > 0 || $botdata[5] > 0){ 
			if($diag=='1')print $data; 
		
			if($diag=='1'){ 
				print "Bot signature found."; 
				print "Type of test was: $botdata[1]"; 
				print "The {$botdata[2]} was found {$botdata[3]} times, the {$botdata[4]} was found {$botdata[5]} times"; 
			} 
		
			// your 'rejection' code would go here.... 
			// for example, print a fake error message and exit the process. 
			$errnum = round(rand(1100, 25000));
			if($diag=='1')print "Confabulation Error #$errnum, Halting.";
			exit;
		
		}
		////////////////////////
	}
	function mailBotScoutResult($isSecondLevel =  false)
	{
				global $mainframe;
				$mailFrom = $mainframe->getCfg('mailfrom');
				$fromName = $mainframe->getCfg('fromname');
				
				$plugin 	=& JPluginHelper::getPlugin('system', 'osolcaptcha');
				
				$this->params   	= new JParameter($plugin->params);
				
				$this->reportBotscoutNegativeMail  = $this->params->get('reportBotscoutNegativeMail',$this->reportBotscoutNegativeMail);
				//JUtility::sendMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=null, $bcc=null, $attachment=null, $replyto=null, $replytoname=null)
				$subject = $mainframe->getCfg('sitename')." : Suspected spam attack from ".$_SERVER['REMOTE_ADDR'];
				$verificationType = $isSecondLevel?"botscout":"second level security";
				$message = "Following request from IP:{$_SERVER['REMOTE_ADDR']} returned a -ve result on $verificationType verification in ".JURI::current()."\r\n Get vars =".var_export($_GET,true)."\r\n POST vars =".var_export($_POST,true)."\r\n REQUEST vars =".var_export($_REQUEST,true);
				//echo $subject."\r\n".$message ;exit;
				JUtility::sendMail($mailFrom,$fromName,$this->reportBotscoutNegativeMail ,$subject  ,$message,$mailFrom);
	}

}
?>
