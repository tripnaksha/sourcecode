<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.html.pane');
$pane =& JPane::getInstance('Tabs');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminheading">
		<tr><th>		
        <?php
		$config =& JFactory::getConfig();
		$sefConfig =& SEFConfig::getConfig();
		$lists = $this->lists;
		$sef_config_file = JPATH_COMPONENT . DS . 'configuration.php';
		echo 'ARTIO JoomSEF ' . JText::_('Configuration file') . (file_exists($sef_config_file) ? (is_writable($sef_config_file) ? (' <b><font color="green">'.JText::_('Writeable').'</font></b>') : (' <b><font color="red">'.JText::_('Unwriteable').'</font></b>')) : (' <b><font color="red">'.JText::_('Using Default Values').'</font></b>'));
		?>		
		</th></tr>
		</table>
		<?php if (!$config->getValue('sef')) {
			JError::raiseNotice('100', JText::sprintf('INFO_SEF_DISABLED', '<a href="index.php?option=com_config">', '</a>'));
		}
		$x = 0;
	    ?>
	    <script language="Javascript">
	    function submitbutton(pressbutton) {
	        <?php
	        jimport( 'joomla.html.editor' );
	        $editor =& JFactory::getEditor();
	        echo $editor->save('introtext');
	        ?>
	        submitform(pressbutton);
	    }
		</script>
		
		<?php
		echo $pane->startPane('config-pane');
		echo $pane->startPanel(JText::_('Basic'), 'basic');
		?>
		
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Basic Configuration'); ?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_JOOMSEF_ENABLED'),JText::_('Enabled'));?></td>
    	            <td width="200"><?php echo JText::_('JoomSEF Enabled');?>?</td>
    	            <td><?php echo $lists['enabled'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_DISABLE_NEW_SEF'),JText::_('Disable creation of new SEF URLs?'));?></td>
    	            <td><?php echo JText::_('Disable creation of new SEF URLs?');?></td>
    	            <td><?php echo $lists['disableNewSEF']; ?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_NUMBER_DUPLICATES'),JText::_('Number duplicate URLs?'));?></td>
    	            <td><?php echo JText::_('Number duplicate URLs?');?></td>
    	            <td><?php echo $lists['numberDuplicates']; ?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_REPLACE_CHAR'),JText::_('Replacement character'));?></td>
    	            <td><?php echo JText::_('Replacement character');?></td>
    	            <td><input type="text" name="replacement" value="<?php echo $sefConfig->replacement;?>" size="1" maxlength="1"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_PAGE_SEP_CHAR'),JText::_('Page spacer character'));?></td>
    	            <td><?php echo JText::_('Page spacer character');?></td>
    	            <td><input type="text" name="pagerep" value="<?php echo $sefConfig->pagerep;?>" size="1" maxlength="1"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_STRIP_CHAR'),JText::_('Strip characters'));?></td>
    	            <td><?php echo JText::_('Strip characters');?></td>
    	            <td><input type="text" name="stripthese" value="<?php echo $sefConfig->stripthese;?>" size="60" maxlength="255"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_FRIEND_TRIM_CHAR'),JText::_('Trim friendly characters'));?></td>
    	            <td><?php echo JText::_('Trim friendly characters');?></td>
    	            <td><input type="text" name="friendlytrim" value="<?php echo $sefConfig->friendlytrim;?>" size="60" maxlength="255"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_USE_ALIAS'),JText::_('Use Title Alias'));?></td>
    	            <td><?php echo JText::_('Use Title or Alias');?></td>
    	            <td><?php echo $lists['useAlias'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_SUFFIX'),JText::_('File suffix'));?></td>
    	            <td><?php echo JText::_('File suffix');?></td>
    	            <td><input type="text" name="suffix" value="<?php echo $sefConfig->suffix; ?>" size="10" maxlength="6"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_ADD_FILE'),JText::_('Default index file'));?></td>
    	            <td><?php echo JText::_('Default index file');?></td>
    	            <td><input type="text" name="addFile" value="<?php echo $sefConfig->addFile; ?>" size="60" maxlength="60"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_PAGE_TEXT'),JText::_('Page text'));?></td>
    	            <td><?php echo JText::_('Page text');?></td>
    	            <td><input type="text" name="pagetext" value="<?php echo $sefConfig->pagetext; ?>" size="30" maxlength="30"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_LOWERCASE'),JText::_('All lowercase'));?></td>
    	            <td><?php echo JText::_('All lowercase');?>?</td>
    	            <td><?php echo $lists['lowerCase'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_USE_SEC-CAT_INDEX'),JText::_('Use index file for sections and categories'));?></td>
    	            <td><?php echo JText::_('Use index for sections and categories');?></td>
    	            <td><?php echo $lists['contentUseIndex']; ?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  
		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('Advanced'), 'advanced');
		  ?>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Advanced Configuration');?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20" valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_ALLOW_UTF'), JText::_('Allow UTF-8 characters in URL'));?></td>
    	            <td width="200" valign="top"><?php echo JText::_('Allow UTF-8 characters in URL');?></td>
    	            <td><?php echo $lists['allowUTF'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_REPLACEMENTS'), JText::_('Non-ascii char replacements'));?></td>
    	            <td valign="top"><?php echo JText::_('Non-ascii char replacements');?></td>
    	            <td><textarea name="replacements" cols="40" rows="5"><?php echo $sefConfig->replacements;?></textarea></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_EXCLUDE_SOURCE'), JText::_('Exclude source info (Itemid)'));?></td>
    	            <td><?php echo JText::_('Exclude source info (Itemid)');?></td>
    	            <td><?php echo $lists['excludeSource'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_REAPPEND_SOURCE'), JText::_('Reappend source (Itemid)'));?></td>
    	            <td><?php echo JText::_('Reappend source (Itemid)');?></td>
    	            <td><?php echo $lists['reappendSource'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_IGNORE_SOURCE'), JText::_('Ignore multiple sources (Itemids)'));?></td>
    	            <td><?php echo JText::_('Ignore multiple sources (Itemids)');?></td>
    	            <td><?php echo $lists['ignoreSource'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_APPEND_NONSEF'), JText::_('Append non-SEF variables to URL'));?></td>
    	            <td><?php echo JText::_('Append non-SEF variables to URL');?></td>
    	            <td><?php echo $lists['appendNonSef'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_TRANSIT_SLASH'), JText::_('Be tolerant to trailing slash?'));?></td>
    	            <td><?php echo JText::_('Be tolerant to trailing slash?');?></td>
    	            <td><?php echo $lists['transitSlash'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_NONSEF_REDIRECT'), JText::_('Redirect nonSEF URLs to SEF?'));?></td>
    	            <td><?php echo JText::_('Redirect nonSEF URLs to SEF?');?></td>
    	            <td><?php echo $lists['nonSefRedirect'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_USE_MOVED'), JText::_('Use Moved Permanently redirection table?'));?></td>
    	            <td><?php echo JText::_('Use Moved Permanently redirection table?');?></td>
    	            <td><?php echo $lists['useMoved'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_USE_MOVED_ASK'), JText::_('Ask before saving URL to Moved Permanently table?'));?></td>
    	            <td><?php echo JText::_('Ask before saving URL to Moved Permanently table?');?></td>
    	            <td><?php echo $lists['useMovedAsk'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_DONT_REMOVE_SID'), JText::_('Do not remove SID from SEF URL?'));?></td>
    	            <td><?php echo JText::_('Do not remove SID from SEF URL?');?></td>
    	            <td><?php echo $lists['dontRemoveSid'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_SET_QUERY_STRING'), JText::_('Set server QUERY_STRING?'));?></td>
    	            <td><?php echo JText::_('Set server QUERY_STRING?');?></td>
    	            <td><?php echo $lists['setQueryString'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_PARSE_JOOMLA_SEO'), JText::_('Parse Joomla SEO links?'));?></td>
    	            <td><?php echo JText::_('Parse Joomla SEO links?');?></td>
    	            <td><?php echo $lists['parseJoomlaSEO'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CHECK_JUNK_URLS'), JText::_('Filter variable values?'));?></td>
    	            <td><?php echo JText::_('Filter variable values?');?></td>
    	            <td><?php echo $lists['checkJunkUrls'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JUNK_WORDS'), JText::_('Filter these words'));?></td>
    	            <td><?php echo JText::_('Filter these words');?>:</td>
    	            <td><?php echo $lists['junkWords'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JUNK_EXCLUDE'), JText::_('Variables to exclude from filtering'));?></td>
    	            <td><?php echo JText::_('Variables to exclude from filtering');?>:</td>
    	            <td><?php echo $lists['junkExclude'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_PREVENT_NONSEF_OVERWRITE'), JText::_('Prevent non-SEF variables from overwriting the parsed ones'));?></td>
    	            <td><?php echo JText::_('Prevent non-SEF variables from overwriting the parsed ones');?>:</td>
    	            <td><?php echo $lists['preventNonSefOverwrite'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CUSTOM_NONSEF'), JText::_('Custom non-SEF variables'));?></td>
    	            <td><?php echo JText::_('Custom non-SEF variables');?>:</td>
    	            <td><input type="text" name="customNonSef" value="<?php echo $sefConfig->customNonSef; ?>" size="60"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_AUTO_CANONICAL'), JText::_('Automatic canonical link generation'));?></td>
    	            <td><?php echo JText::_('Automatic canonical link generation');?>:</td>
    	            <td><?php echo $lists['autoCanonical']; ?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_SEF_COMPONENT_URLS'), JText::_('SEF URLs using component template'));?></td>
    	            <td><?php echo JText::_('SEF URLs using component template');?>:</td>
    	            <td><?php echo $lists['sefComponentUrls']; ?></td>
    	        </tr>
    	        </table>
		    </fieldset>
    	        
			<fieldset class="adminform">
		      <legend><?php echo JText::_('URL Source Tracing');?></legend>
		      <table class="adminform">    	        
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20" valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_TRACE'), JText::_('Trace URL Source'));?></td>
    	            <td width="200" valign="top"><?php echo JText::_('Enable URL source tracing?');?></td>
    	            <td><?php echo $lists['trace'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_TRACE_DEPTH'), JText::_('Tracing depth'));?></td>
    	            <td><?php echo JText::_('Tracing depth');?>:</td>
    	            <td><?php echo $lists['traceLevel'];?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  
		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('Cache'), 'cache');
		  ?>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Cache Configuration');?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_USE_CACHE'), JText::_('Use cache?'));?></td>
    	            <td width="200"><?php echo JText::_('Use cache?');?></td>
    	            <td><?php echo $lists['useCache'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CACHE_SIZE'), JText::_('Maximum cache size'));?></td>
    	            <td><?php echo JText::_('Maximum cache size');?>:</td>
    	            <td><?php echo $lists['cacheSize'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CACHE_HITS'), JText::_('Minimum cache hits count'));?></td>
    	            <td><?php echo JText::_('Minimum cache hits count');?>:</td>
    	            <td><?php echo $lists['cacheMinHits'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CACHE_RECORDHITS'), JText::_('Record hits for cached URLs'));?></td>
    	            <td><?php echo JText::_('Record hits for cached URLs');?>:</td>
    	            <td><?php echo $lists['cacheRecordHits'];?></td>
    	        </tr>
		      </table>
		  </fieldset>

          <?php if (is_dir(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_joomfish')) { ?>
		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('JoomFish'), 'joomfish');
		  ?>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('JoomFish Related Configuration');?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_JF_LANG_PLACEMENT'), JText::_('Language integration'));?></td>
    	            <td width="200"><?php echo JText::_('Language integration');?></td>
    	            <td><?php echo $lists['langPlacement'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JF_ALWAYS_USE_LANG'), JText::_('Always use language?'));?></td>
    	            <td><?php echo JText::_('Always use language?');?></td>
    	            <td><?php echo $lists['alwaysUseLang'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JF_TRANSLATE'), JText::_('Translate URLs?'));?></td>
    	            <td><?php echo JText::_('Translate URLs?');?></td>
    	            <td><?php echo $lists['translateNames'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JF_BROWSER_LANG'), JText::_('Get language from browser setting?'));?></td>
    	            <td><?php echo JText::_('Get language from browser setting?');?></td>
    	            <td><?php echo $lists['jfBrowserLang'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JF_LANG_COOKIE'), JText::_('Save language to cookie?'));?></td>
    	            <td><?php echo JText::_('Save language to cookie?');?></td>
    	            <td><?php echo $lists['jfLangCookie'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JF_MAIN_LANG'), JText::_('Main language'));?></td>
    	            <td><?php echo JText::_('Main language');?>:</td>
    	            <td><?php echo $lists['mainLanguage'];?></td>
    	        </tr>
		      </table>
		      
		      <?php
		      if( isset($lists['jfSubDomains']) ) {
		          ?>
		          <table class="adminform">
		          <tr>
		              <th width="20"><?php echo JHTML::_('tooltip', JText::_('TT_JF_DOMAIN'), JText::_('Domain configuration'));?></th>
		              <th width="200"><?php echo JText::_('Domain configuration'); ?></th>
		              <th colspan="2"></th>
		          </tr>
		          <?php
		          foreach( $lists['jfSubDomains'] as $l ) {
		              ?>
		              <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	              <td colspan="2"><?php echo $l->name;?></td>
        	              <td><input type="text" name="jfSubDomains[<?php echo $l->code; ?>]" class="inputbox" size="45" value="<?php echo $l->value; ?>" /></td>
        	              <td></td>
    	              </tr>
		              <?php
		          }
		          ?>
		          </table>
		          <?php
		      }
		      ?>
		  </fieldset>
          <?php } ?>

		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('404 Page'), '404');
		  ?>
		  
		  <div class="col width-50">
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('404 Page'); ?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_404_PAGE'), JText::_('404 Page'));?></td>
    	            <td width="200"><?php echo JText::_('404 Page');?></td>
    	            <td><?php echo $lists['page404'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_404_MESSAGE'), JText::_('Show 404 Message'));?></td>
    	            <td><?php echo JText::_('Show 404 Message');?></td>
    	            <td><?php echo $lists['msg404'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_404_RECORD_HITS'), JText::_('Record 404 page hits?'));?></td>
    	            <td><?php echo JText::_('Record 404 page hits?');?></td>
    	            <td><?php echo $lists['record404'];?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Default 404 Page').' - '.JText::_('ItemID');?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20" valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_USE_404_ITEMID'), JText::_('Use Itemid for Default 404 Page'));?></td>
    	            <td width="200" valign="top"><?php echo JText::_('Use Itemid for Default 404 Page');?></td>
    	            <td><?php echo $lists['use404itemid'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_SELECT_ITEMID'), JText::_('Select Itemid'));?></td>
    	            <td valign="top"><?php echo JText::_('Select Itemid');?></td>
    	            <td><?php echo $lists['itemid404'];?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  </div>
		  
		  <div class="col width-50">
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Custom 404 Page');?></legend>
    		  <?php
    		  // parameters : hidden field, content, width, height, cols, rows
    		  jimport( 'joomla.html.editor' );
    		  $editor =& JFactory::getEditor();
    		  echo $editor->display('introtext', $lists['txt404'], '450', '250', '50', '11');
    		  ?>
		  </fieldset>
		  </div>
		  <div class="clr"></div>
		  
		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('Registration'), 'registration');
		  $x = 0;
		  ?>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('ARTIO JoomSEF Registration');?></legend>
		      <p><?php echo JText::_('INFO_REGISTRATION'); ?></p>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_ARTIO_DOWNLOAD_ID'), JText::_('JoomSEF Download ID'));?></td>
    	            <td width="200"><?php echo JText::_('JoomSEF Download ID');?>:</td>
    	            <td><?php echo $lists['artioDownloadId'];?></td>
    	        </tr>
		      </table>
		  </fieldset>

		  <?php $x = 0; ?>
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('ARTIO User Account'); ?></legend>
		      <p><?php echo JText::_('INFO_ACCOUNT'); ?></p>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_ARTIO_USERNAME'), JText::_('ARTIO Site Username'));?></td>
    	            <td width="200"><?php echo JText::_('ARTIO Site Username');?>:</td>
    	            <td><?php echo $lists['artioUserName'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_ARTIO_PASSWORD'), JText::_('ARTIO Site Password'));?></td>
    	            <td><?php echo JText::_('ARTIO Site Password');?>:</td>
    	            <td><?php echo $lists['artioPassword'];?></td>
    	        </tr>
		      </table>
		  </fieldset>		  

		  <?php
		  echo $pane->endPanel();
		  echo $pane->endPane();
		  ?>
		
		
		
<input type="hidden" name="id" value="" />
<input type="hidden" name="section" value="config" />
<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="config" />
</form>
