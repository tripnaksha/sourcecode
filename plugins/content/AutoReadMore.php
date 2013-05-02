<?php

// Do the usual dance
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgContentAutoReadMore extends JPlugin {

 function plgContentAutoReadMore(&$subject, $params) { parent::__construct($subject, $params); }

 function onPrepareContent( &$article, &$params ) {
  // Be sure that we're using the com_content module, and that we're on the frontpage, or category blog, or section blog.
  if(JRequest :: getCmd('option') != 'com_content') return;
  $view=JRequest :: getCmd('view'); $layout=JRequest :: getCmd('layout');
  if(! (($view=='frontpage') || (($view=='category') && ($layout=='blog')) || (($view=='section') && ($layout=='blog'))) ) return;
  // Be sure that this section/category/article is not one that the user wanted to exclude.
  if ($this->param('Enabled_Front_Page') == 0 and $view=='frontpage') return;
  if (in_array($article->sectionid, explode(',', $this->param('Exclude_Section_Ids')))) return;
  if (in_array($article->catid, explode(',', $this->param('Exclude_Category_Ids')))) return;
  if (in_array($article->id, explode(',', $this->param('Exclude_Article_Ids')))) return;
  // How many characters are we allowed?
  $max_chars = $this->param('Max_Chars');
  if (!is_numeric($max_chars)) $max_chars = 500;
  if (strlen(strip_tags($article->text)) > $max_chars) {
   // First, remove all new lines
   $article->text = preg_replace("/\r\n|\r|\n/", "", $article->text);
   // Next, replace <br /> tags with \n
   $article->text = preg_replace("/<BR[^>]*>/i", "\n", $article->text);
   // Replace <p> tags with \n\n
   $article->text = preg_replace("/<P[^>]*>/i", "\n\n", $article->text);
   // Strip all tags
   $article->text = strip_tags($article->text);
   // Truncate
   $article->text = substr($article->text, 0, $max_chars);
   // Pop off the last word in case it got cut in the middle
   $article->text = preg_replace("/[.,!?:;]? [^ ]*$/", "", $article->text);
   // Add a few new lines to the end of the article, and ...
   $article->text = trim($article->text) . "...\n\n";
   // Replace \n with <br />
   $article->text = str_replace("\n", "<br />", $article->text);
   // Add a "read more" link
   $article->readmore = true;
   }
  }

 function param($name) {
  static $plugin, $pluginParams;
  if (!isset($plugin)) {
   $plugin =& JPluginHelper::getPlugin('content', 'AutoReadMore');
   $pluginParams = new JParameter( $plugin->params );
   }
  return $pluginParams->get($name);
  }

 }
