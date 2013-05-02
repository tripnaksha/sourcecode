<?php
if (!defined('_JEXEC')) die('Direct Access to this location is not allowed.');
?>
<style type="text/css">
div.docs,div.docs p,div.docs ul li,div.docs ol li {
	text-align: left;
	font-weight: lighter;
	font-family: Tahoma, Arial, Verdana;
}

div.docs h1 {
	text-align: center;
}

div.docs h4,span.h4 {
	color: #CC0000;
}

div.docs p {
	padding-left: 3em;
	font-weight: lighter;
}

div.docs .small {
	color: #666666;
	font-size: 90%;
}

div.docs h5,span.h5 {
	font-weight: bold;
}
</style>
<div class="docs">
<p style="text-align: right;"><span class="small">Updated: January 2, 2009</span></p>

<h1><img src="components/com_sh404sef/images/sh404SEF-logo-big.png"
	align="middle" alt="sh404SEF" title="sh404SEF logo" border="0"
	width="291" height="186" />sh404SEF Documentation</h1>
<p style="text-align: center;">for Joomla 1.5.x</p>
<h2>Support site</h2>
<br />
You will find up to date information and support at <a
	href="http://extensions.siliana.com/">extensions.Siliana.com/</a>.
Please also check our forum at <a
	href="http://extensions.siliana.com/forums/">extensions.Siliana.com/forums/</a>.
Please read also our Frequently Asked Question, probably the best place
to start with if something does not work as you like<br />
<br />
<h2>Summary</h2>
<p><span class=h5>Allows Search Engine Friendly URLs for Apache (and
possibly but unsupported, IIS)</span>. You can also setup your own, custom URLs
if you don't like those automatically built. Builds page title and meta
tags, and insert them into page. Title and tags can be manually set as
well. Provides security functions, by checking the content of URL and
visitor IP against various security check lists, plus an anti-flooding
system.</p>
<p><span class=h5>IMPORTANT : dual-level interface:</span> sh404sef has a <span class=h5>dual-level</span> interface. 
This means that by default only a few commands and options are shown. The full set of options can be 
displayed after clicking on the link located on the main control panel and 
called <span class=h5>"Click here to display advanced interface"</span>!</p>
<p>sh404SEF works with <span class=h5>Joomfish</span>, and
there are several plugins to build up URL for various components,
including <span class=h5>Virtuemart, Fireboard, Community Builder,
Myblog, iJoomla Magazine and NewsPortal, Mosets Tree and Hot
Property,...</span>.</p>
<p>It has a caching system, to reduce database queries and enhance page
loading time when using URL rewriting.</p>
<p>sh404SEF can operate <span class=h5>with or without mod_rewrite</span>
(that is with or without .htaccess file). Url are the same, except there
is an added /index.php/ bit in them when not using .htaccess. This is
now the default setting, as it is much easier to use. You may want to
adjust your ErrorDocument as 404 errors will no longer be processed by
Joomla when operating without a .htaccess file.</p>
<p>The integrated tool to manage your META tags will rewrite Title,
Description, Keywords, Robots and Language meta tags to your liking, on
any page of your site. It has a plugin system to accomodate any
component, and plugins for Virtuemart and com_smf and for regular
content are provided to automatically generate these tags. Plus, you'll
be able to manually set any tags you like on a page by page basis (a
page is identified by its URL). Plus you'll be able to set content title
within h1 tags, and remove Generator = Joomla tags, plus a whole lot of
other SEO useful changes.</p>
<p>There is no hack of Joomla, just a standard plugin, installed
automatically with sh404SEF.</p>
<p>Many thanks to all previous contributors to 404SEF and 404SEFx</p>
<h2>Documentation</h2>

<p>


<h4>IMPORTANT : if you plan to use mod_rewrite (.htaccess) rewriting :</h4>
<span class=h5>BEFORE</span> making any attempt to activate this
component and use its URL rewriting functions, <span class=h5>your
Joomla installation should already be compatible with URL rewriting</span>.
This is not required if you select no .htaccess rewrite mode in sh404SEF
advanced parameters (but this mode may not always work as well,
depending on your server settings). <br />
<br />
Remember : if you are having difficulties with this, it is unlikely to
be a joomla problem, but most likely something related to your server
setup. For instance, many times, you will be faced with 404 errors or
Internal server errors 500 display. This indicates that there is
something in your .htacces file that is not compatible with your apache
server setup.<br />
<br />
If you face this kind of errors, I will suggest you contact your hosting
company for assistance. <br />
If your .htaccess is not compatible with your apache server, or hosting
company, there is no point in trying to use sh404SEF - or any other
similar component like ARTIO Joomsef, OpenSEF or Advanced SEF - as they
will simply not work. You will have first to fix your installation,
paying particular attention to the existence and the content of your
.htaccess file. However, one of the first thing to control : verify that
mod_rewrite is loaded by PHP. To do this, in Joomla backend, go to
System menu, then System information. On the PHP tab, just run a search
for the word 'rewrite'. If you don't find anything, then mod_rewrite is
not loaded and nothing will work. You need to change your Apache web
server httpd.conf file, or contact your system administrator or shared
host company to do this for you.
<br /><br />

<p>More advice on .htaccess, a very tricky issue on many occasions, can
be found on line at <a href="http://extensions.siliana.com/en/Table/sh404SEF-and-url-rewriting"></a>. In a few words :</p>
<ul>
	<li>Joomla standard .htaccess is very <span class=h5>FINE</span>. It
	will work with most hosting companies. You should use it unmodified, at
	least to start with. Just remember it comes named as htaccess.txt, so
	you need to <span class=h5>rename</span> it to .htaccess before
	anything.</li>
	<li>Joomla standard .htaccess comes configured for Joomla standard SEF
	system (which makes sense!). To use it with sh404SEF (or OpenSEF, Artio
	JoomSEF, SEF Advance), you must open it up in an editor, and make the
	few changes explained in it:<br />
	If you scroll down towards the end of the file, you'll see two
	sections, one marked <span class=h5>Begin - Joomla! core SEF Section</span>,
	and just next to it another marked : <span class=h5>########## Begin -
	3rd Party SEF Section</span><br />
	Now the tricky part : you should type # at the beginning of each line
	of the first section, and remove those # in front of those in the
	second section, so that the whole things looks like :<br />
	<br />
	<i>########## Begin - Joomla! core SEF Section<br />
	############# Use this section if using ONLY Joomla! core SEF<br />
	## ALL (RewriteCond) lines in this section are only required if you
	actually<br />
	## have directories named 'content' or 'component' on your server<br />
	## If you do not have directories with these names, comment them out.<br />
	#<br />
	#RewriteCond %{REQUEST_FILENAME} !-f<br />
	#RewriteCond %{REQUEST_FILENAME} !-d<br />
	#RewriteCond %{REQUEST_URI} ^(/component/option,com) [NC,OR] ##optional
	- see notes##<br />
	#RewriteCond %{REQUEST_URI} (/|\.htm|\.php|\.html|/[^.]*)$ [NC]<br />
	#RewriteRule ^(content/|component/) index.php<br />
	#<br />
	########## End - Joomla! core SEF Section<br />
	<br />
	<br />

	########## Begin - 3rd Party SEF Section<br />
	############# Use this section if you are using a 3rd party (Non
	Joomla! core) SEF extension - e.g. OpenSEF, 404_SEF, 404SEFx, SEF
	Advance, etc<br />
	#<br />
	RewriteCond %{REQUEST_URI} ^(/component/option,com) [NC,OR] ##optional
	- see notes##<br />
	RewriteCond %{REQUEST_URI} (/|\.htm|\.php|\.html|/[^.]*)$ [NC]<br />
	RewriteCond %{REQUEST_FILENAME} !-f<br />
	RewriteCond %{REQUEST_FILENAME} !-d<br />
	RewriteRule (.*) index.php<br />
	#<br />
	########## End - 3rd Party SEF Section</i><br />
	<br />
	Note : this is from the .htaccess that comes with Joomla 1.0.12. If you
	are using a more recent version of Joomla, use the latest version of
	the file instead of this one.<br />
	<br />
	</li>
	<li>If you get 404 errors or Internal error 500, or similar, when
	clicking on a rewritten URL, then you should try adding another # at
	the beginning of this line (near the top of the file): <br />
	<br />
	Options FollowSymLinks <br />
	<br />
	so that it looks like: <br />
	<br />
	#Options FollowSymLinks <br />
	<br />
	</li>
	<li>If that does not work, and if your Joomla site is in a
	sub-directory, you should look for the line that looks like: <br />
	#RewriteBase /<br />
	and replace it with : RewriteBase /sub_directory_of_your_joomla_install<br />
	</li>
	<li>On some servers, even if your site is not in a sub-directory, you
	may want to try replacing<br />
	#RewriteBase /<br />
	by <br />
	RewriteBase /<br />
	</li>
	<li>One little thing more : try changing only one thing at a time, and
	check the result before moving to next step</li>
</ul>
<ol style="list-style-type: upper-roman;">
	<li>
	<h3>Introduction</h3>
	<p>Here are the main information bits required to use sh404SEF. You can
	view this documentation again by selecting the 'sh404SEF Documentation'
	button from the sh404SEF Control Panel.</p>
	<p>Please note that I cannot support IIS installation in any way.
	sh404SEF may well work on such machines, but it is not tested at all.</p>
	</li>
	<li>
	<h3>Installation Instructions</h3>
	<p>You can view installation instructions below by clicking the
	appropriate arrow.</p>
	<ol style="list-style-type: decimal;" id="collapsibleList">
		<li><script type="text/javascript">
					document.writeln('<img id="imgInstall" src="components/com_sh404sef/images/up.png" width="15" height="8" alt="Open list" onClick="toggle(\'imgInstall\',\'install\');">');
				</script> <span class="h4">Installation</span><br />
		<ul id="install" style="list-style: none;">
			<li>
			<ol>
				<li>Upload the zip file to Joomla using the component installer in
				the usual way.</li>
				<li>For apache, the .htaccess file that comes with Joomla is FINE!!<br />
				There is just one line I would suggest you add to it: <br />
				# Rule for duplicate content removal : www.domain.com vs domain.com<br />
				RewriteCond %{HTTP_HOST} ^mydomain\.com [NC]<br />
				RewriteRule (.*) http://www.mydomain.com/$1 [R=301,L,NC]<br />
				What this will do is rewrite all page requests starting with
				mydomain.com to www.mydomain.com. This way, you will avoid some
				duplicate contents issues (even though that is not really a problem
				nowadays, at least with Google), and also improve the way many
				editors, including JCE, will behave.<br />
				You should <span class=h5>replace mydomain\.com on the first line
				and www.mydomain.com by your own domain information of course</span>.
				<br />
				Also, you should place these 3 lines just after the RewriteBase line
				in Joomla standard .htaccess file.<br />
				Lastly, you can also do it the other way around, that is rewrite
				www.mydomain.com to mydomain.com. To do this, simply use :<br />
				# Rule for duplicate content removal : domain.com vs wwwdomain.com<br />
				RewriteCond %{HTTP_HOST} ^www.mydomain\.com [NC]<br />
				RewriteRule (.*) http://mydomain.com/$1 [R=301,L,NC]<br />
				<br />
				</li>
				<li>For IIS, see Configuring IIS..</li>
				<li>Ensure that SEF is enabled under Global Configuration in the
				Joomla backend.</li>
				<li>Edit the 404 SEF configuration, Change Enable to yes and save.<br />
				This is neccessary to ensure the default 404 document gets saved to
				the Joomla database.</li>
			</ol>
			</li>
		</ul>
		</li>
		<li><script type="text/javascript">document.writeln('<img id="imgIIS" src="components/com_sh404sef/images/up.png" width="15" height="8" alt="Open list" onClick="toggle(\'imgIIS\',\'iis\');">');
				</script> <span class="h4">Configuring IIS</span><br />
		<br />

		<ul id="iis" style="list-style: none">
			<li><span class=h5>IMPORTANT</span> : The instructions below are
			provided as is, with no warranty. They are simply copied over from
			past version of 404SEFx, and I have absolutely no idea if they are
			correct or not. Any feed back on that matter is appreciated, and may
			passed over to other people using the support forum at <a
				href="http://extensions.siliana.com/forums/">extensions.Siliana.com/forums/</a>.
			Please not that good results have been reported using the "no
			.htaccess (/index.php/)" operating mode of sh404SEF instead of trying
			to setup a rewriting engine in IIS. If you use this operating mode,
			you don"t need anything than just sh404SEF. However, again, IIS may
			have been configured in a way where even this mode cannot work, so
			please report to sh404SEF support forum, or to Joomla Forum for
			assistance in setting this up.<br />
			<br />
			<ol>
				<li>
				<h4>Install ActiveScript</h4>
				After installing PHP, you should download the ActiveScript DLL
				(php4activescript.dll) and place it in the main PHP folder (e.g.
				C:\php).<br />
				<br />
				After having all the files needed, you must register the DLL on your
				system. To achieve this, open a Command Prompt window (located in
				the Start Menu). Then go to your PHP directory by typing something
				like cd C:\php. To register the DLL just type <pre>regsvr32 php4activescript.dll</pre>
				</li>
				<li>
				<h4>Install .NET framework 1.1</h4>
				To the best of my limited knowledge of IIS, this is required for
				web.config to work, so install it<br />
				<br />
				</li>
				<li>
				<h4>Create/Modify web.config</h4>
				<span style="color: red; font-weight: bold;">NOTE: in the example
				below, Joomla/Mambo is installed in the virtual directory 'joomla'.</span><br />
				<br />
				Create C:\Inetpub\wwwroot\web.config and add the content below:<br />
				<pre>&lt;?xml version=&quot;1.0&quot; encoding=&quot;utf-8&quot;?&gt;
        &lt;configuration&gt;
        	&lt;system.web&gt;
        		&lt;compilation defaultLanguage=&quot;PHP4Script&quot; debug=&quot;true&quot; /&gt;
        		&lt;customerrors mode=&quot;On&quot; defaultRedirect=&quot;/joomla/index.php&quot; /&gt;
        	&lt;/system.web&gt;
        &lt;/configuration&gt;</pre></li>
				<li>
				<h4>Configure the Custom Errors</h4>
				<span style="color: red; font-weight: bold;">NOTE: in the example
				below, Joomla/Mambo is installed in the virtual directory Joomla</span>
				<pre>Using the Internet Services Manager, right-click the directory in which Joomla is installed.
							Select properties &gt;&gt; Custom Error
							set the 404 to URL:/mambo/index.php
							set the 405 to URL:/mambo/index.php</pre></li>
			</ol>
			</li>
		</ul>
		</li>
		<li><script type="text/javascript">document.writeln('<img id="imgUninstall" src="components/com_sh404sef/images/up.png" width="15" height="8" alt="Open list" onClick="toggle(\'imgUninstall\',\'uninstall\');">');
				</script> <span class="h4">Uninstall</span><br />
		<ul id="uninstall" style="list-style: none;">
			<li>
			<ol>
				<li>Uninstall the component using the component unistaller in the
				usual way.</li>
				<li>For IIS, remove C:\Inetpub\wwwroot\web.config and<br />
				the Custom Errors you created with the Internet Services Manager<br />
				</li>
			</ol>
			</li>
		</ul>
		</li>
	</ol>
	</li>
	<li>
	<h3>Useful Tips For Using sh404SEF</h3>
	<ul style="list-style: none;">
		<li>
		<h4>Configuration</h4>

		<p>Using sh404SEF configuration is fairly straightforward for most of
		it. For more information on each item hover your mouse over the blue
		(i) images when you are in the configuration screen.</p>
		<p>When you save the configuration you will be prompted to remove all
		your URL's from the database. This is required only if you have
		changed parameters affecting the way URL are built, such as changing
		suffix from .htm to .html for instance. If you have a high traffic
		site it may be wise to put it offline before or purging the database.
		<span class=h5>After doing that, you should use an <strong>online</strong>
		tool to build automatically a sitemap</span>. The sitemap generator
		will go through all of your site, and therefore all of your links, so
		they will be all automatically placed in the cache, thus speeding
		access for your next visitors.<br>
		The caching system of sh404SEF is transparent,and will be rebuild
		automatically whenever required. Using the cache will vastly speed up
		the page load time, by dramatically reducing the number of database
		queries. Beware that URL caching uses up a lot of memory though. Its
		size can be limited using the appropriate parameter, and it can also
		be turned off completely.
		
		
		<p></p>
		<p>If you have a multi-lingual site, you can turn on or off URL
		translation. Normally, URL elements will be translated into the user
		language. However, it sometimes better not to translate, such as when
		using non-latin characters based languages. On such occasions, default
		site language will always be used</p>
		<p></p>

		<p>You may want to purge the 404 log before creating fresh urls.</p>
		</li>
		<li>
		<h4>Modifying URL's</h4>
		<p>You can modify URL's to your liking. Go into sh404SEF Control Panel
		and click ' View/Edit SEF Urls'. Select the URL you wish to modify. If
		you click the check box labeled 'Save as Custom Redirect' it will
		place this URL into the 'Custom Redirect' area which you can navigate
		to from the sh404SEF Control Panel. When you click 'View/Edit Custom
		Redirects' you will see your URL in here now instead. These urls will
		not be removed when you save the config. You can modify these and save
		them as you wish.
		
		
		<p>This is particularly useful if you are updating from an old site
		because any URL's that are no longer availble will be logged. You can
		view these URL's by clicking 'View/Edit 404 Logs' in the sh404SEF
		Control Panel. You can redirect visitors to the new page by selecting
		the URL you wish to modify and entering the new url.</p>
		</li>
		<li>
		<h4>Backing up your data</h4>
		<p>You can import and export your URL to a text file, using the
		corresponding button on sh404SEF control panel.</p>
		<p>You can also import and export URL's from the Custom Redirect area.
		In this case, only the manually defined URL will stored in the export
		file.</p>
		<p>The import file is a simple text file, where URL are listed in
		rows. This is well suited to be imported into a spreadsheet software
		if some heavy text processing is to be done on redirections for
		instance</p>
		<p>If you want to import back your urls, go to the same screen, browse
		to the file and click the 'Import Custom URLS' button.</p>
		<p>Manually defined meta tags can be exported/imported in very much
		the same way. The export/import link is located at the top of the Meta
		Tags screen, accessed from the main control panel</p>
	
	</ul>
</ol>

<br />

<script type="text/javascript">
		document.getElementById('collapsibleList').style.listStyle="none"; // remove list markers
		document.getElementById('install').style.display="none"; // collapse list
		document.getElementById('iis').style.display="none"; // collapse list
		document.getElementById('uninstall').style.display="none"; // collapse list
		// this function toggles the status of a list
		function toggle(image,list){
			var listElementStyle=document.getElementById(list).style;
			if (listElementStyle.display=="none"){
				listElementStyle.display="block"; document.getElementById(image).src="components/com_sh404sef/images/down.png";
				document.getElementById(image).alt="Close list";
			}
			else{
				listElementStyle.display="none";
				document.getElementById(image).src="components/com_sh404sef/images/up.png";
				document.getElementById(image).alt="Open list";
			}
		}
	</script>
<div class="small" style="text-align: center;">Copyright &copy;
2004-2009 Yannick Gaultier<br />
Distributed under the terms of the GNU General Public License.</div>
</div>
