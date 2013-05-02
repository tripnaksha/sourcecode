<?php
// WARNING: No blank line or spaces before the "< ? p h p" above this.

// IMPORTANT: This file should be made in UTF-8 (without BOM) only.
// CB will automatically convert to site's local character set.

/**
* Joomla/Mambo Community Builder
* @version $Id: default_language.php 609 2006-12-13 17:30:15Z beat $
* @package Community Builder
* @subpackage Default CB-Team Plugins Language file (English)
* @author Beat, Nant and JoomlaJoe
* @copyright (C) www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// ensure this file is being included by a parent file:
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

// 1.2 Stable:
// ProfileBook plugin: (new method: UTF8 encoding here):
CBTxt::addStrings( array(
'Profile Book' => 'Profile Book',
'Name' => 'Name',
'Entry' => 'Entry',
'Profile Book Description' => 'Profile Book Description',
'Created On: %s' => 'Created On: %s',
'Edited By %s On: %s' => 'Edited By %s On: %s',
'<br /><strong>[Notice: </strong><em>Last Edit by Site Moderator</em><strong>]</strong>' => '<br /><strong>[Notice: </strong><em>Last Edit by Site Moderator</em><strong>]</strong>',
'Users Feedback:' => 'Users Feedback:',
'Edited by Site Moderator' => 'Edited by Site Moderator',
'Comments' => 'Comments',
'Name' => 'Name',
'Email' => 'Email',
'Location' => 'Location',
'This user currently doesn\'t have any posts.' => 'This user currently doesn\'t have any posts.',
'User Rating' => 'User Rating',
'Web Address' => 'Web Address',
'Submit Entry' => 'Submit Entry',
'Update Entry' => 'Update Entry',
'Enable Profile Entries' => 'Enable Profile Entries',
'Auto Publish' => 'Auto Publish',
'Notify Me' => 'Notify Me',
'Enable visitors to your profile to make comments about you and your profile.' => 'Enable visitors to your profile to make comments about you and your profile.',
'Enable Auto Publish if you want entries submitted to be automatically approved and displayed on your profile.' => 'Enable Auto Publish if you want entries submitted to be automatically approved and displayed on your profile.',
'Enable Notify Me if you would like to receive an email notification each time someone submits an entry.  This is recommended if you are not using the Auto Publish feature.' => 'Enable Notify Me if you would like to receive an email notification each time someone submits an entry.  This is recommended if you are not using the Auto Publish feature.',
'Bold' => 'Bold',
'Italic' => 'Italic',
'Underline' => 'Underline',
'Quote' => 'Quote',
'Code' => 'Code',
'List' => 'List',
'List' => 'List',
'Image' => 'Image',
'Link' => 'Link',
'Close' => 'Close',
'Color' => 'Color',
'Size' => 'Size',
'Item' => 'Item',
'Bold text: [b]text[/b]' => 'Bold text: [b]text[/b]',
'Italic text: [i]text[/i]' => 'Italic text: [i]text[/i]',
'Underline text: [u]text[/u]' => 'Underline text: [u]text[/u]',
'Quoted text: [quote]text[/quote]' => 'Quoted text: [quote]text[/quote]',
'Code display: [code]code[/code]' => 'Code display: [code]code[/code]',
'Unordered List: [ul] [li]text[/li] [/ul] - Hint: a list must contain List Items' => 'Unordered List: [ul] [li]text[/li] [/ul] - Hint: a list must contain List Items',
'Ordered List: [ol] [li]text[/li] [/ol] - Hint: a list must contain List Items' => 'Ordered List: [ol] [li]text[/li] [/ol] - Hint: a list must contain List Items',
'Image: [img size=(01-499)]http://www.google.com/images/web_logo_left.gif[/img]' => 'Image: [img size=(01-499)]http://www.google.com/images/web_logo_left.gif[/img]',
'Link: [url=http://www.zzz.com/]This is a link[/url]' => 'Link: [url=http://www.zzz.com/]This is a link[/url]',
'Close all open bbCode tags' => 'Close all open bbCode tags',
'Color: [color=#FF6600]text[/color]' => 'Color: [color=#FF6600]text[/color]',
'Size: [size=1]text size[/size] - Hint: sizes range from 1 to 5' => 'Size: [size=1]text size[/size] - Hint: sizes range from 1 to 5',
'List Item: [li] list item [/li] - Hint: a list item must be within a [ol] or [ul] List' => 'List Item: [li] list item [/li] - Hint: a list item must be within a [ol] or [ul] List',
'Dimensions' => 'Dimensions',
'File Types' => 'File Types',
'Submit' => 'Submit',
'Preview' => 'Preview',
'Cancel' => 'Cancel',
'User Comments' => 'User Comments',
'Your Feedback' => 'Your Feedback',
'Edit' => 'Edit',
'Update' => 'Update',
'Delete' => 'Delete',
'Publish' => 'Publish',
'Sign Profile Book' => 'Sign Profile Book',
'Give Feedback' => 'Give Feedback',
'Edit Feedback' => 'Edit Feedback',
'Un-Publish' => 'Un-Publish',
'Not Published' => 'Not Published',
'Color' => 'Color',
'Size' => 'Size',
'Very Small' => 'Very Small',
'Small' => 'Small',
'Normal' => 'Normal',
'Big' => 'Big',
'Very Big' => 'Very Big',
'Close All Tags' => 'Close All Tags',
'Standard' => 'Standard',
'Red' => 'Red',
'Purple' => 'Purple',
'Blue' => 'Blue',
'Green' => 'Green',
'Yellow' => 'Yellow',
'Orange' => 'Orange',
'Darkblue' => 'Darkblue',
'Gold' => 'Gold',
'Brown' => 'Brown',
'Silver' => 'Silver',
'You have received a new entry in your %s' => 'You have received a new entry in your %s',
'%s has just submitted a new entry in your %s.' => '%s has just submitted a new entry in your %s.',
'An entry in your %s has just been updated' => 'An entry in your %s has just been updated',
'%s has just submitted an edited entry for %s in your %s.' => '%s has just submitted an edited entry for %s in your %s.',
"\n\nYour current setting is that you need to review entries in your %1\$s. Please login, review the new entry and publish if you agree. Direct access to your %1\$s:\n%2\$s\n" => "\n\nYour current setting is that you need to review entries in your %1\$s. Please login, review the new entry and publish if you agree. Direct access to your %1\$s:\n%2\$s\n",
"\n\nYour current setting is that new entries in your %1\$s are automatically publihed. To see the new entry, please login. You can then see the new entry and take appropriate action if needed. Direct access to your %1\$s:\n%2\$s\n" => "\n\nYour current setting is that new entries in your %1\$s are automatically publihed. To see the new entry, please login. You can then see the new entry and take appropriate action if needed. Direct access to your %1\$s:\n%2\$s\n",
'Name is Required!' => 'Name is Required!',
'Email Address is Required!' => 'Email Address is Required!',
'Comment is Required!' => 'Comment is Required!',
'User Rating is Required!' => 'User Rating is Required!',
'You have not selected a User Rating. Do you really want to provide an Entry without User Rating ?' => 'You have not selected a User Rating. Do you really want to provide an Entry without User Rating ?',
'Return Gesture' => 'Return Gesture',
'Profile Rating' => 'Profile Rating',
'You have not selected your User Rating.' => 'You have not selected your User Rating.',
'Would you like to give a User Rating ?' => 'Would you like to give a User Rating ?',
'Do you really want to delete permanently this Comment and associated User Rating ?' => 'Do you really want to delete permanently this Comment and associated User Rating ?',
'You are about to edit somebody else\'s text as a site Moderator. This will be clearly noted. Proceed ?' => 'You are about to edit somebody else\'s text as a site Moderator. This will be clearly noted. Proceed ?',
'Hidden' => 'Hidden',
'Feedback from %s: ' => 'Feedback from %s: ',
'Poor' => 'Poor',
'Best' => 'Best',
// 1.2:
'Vote %s star' => 'Vote %s star',
'Vote %s stars' => 'Vote %s stars',
'Cancel Rating' => 'Cancel Rating',
'Average Profile Rating by other users' => 'Average Profile Rating by other users'
) );

// Profile Gallery plugin: (new method: UTF8 encoding here):
CBTxt::addStrings( array(
'CB Profile Gallery' => 'CB Profile Gallery',
'This tab contains a basic no-frills image Gallery for CB profiles' => 'This tab contains a basic no-frills image Gallery for CB profiles',
'Current Items' => 'Current Items',
'Keeps track of number of stored items' => 'Keeps track of number of stored items',
'Date of last update to Gallery items in this profile' => 'Date of last update to Gallery items in this profile',
'Last Update' => 'Last Update',
'Enable Gallery' => 'Enable Gallery',
'Select Yes or No to turn-on or off the Gallery Tab' => 'Select Yes or No to turn-on or off the Gallery Tab',
'Short Greeting' => 'Short Greeting',
'Enter a short greeting for your gallery viewers' => 'Enter a short greeting for your gallery viewers',
'Item Quota' => 'Item Quota',
'The admin may use this to over-ride the default value of allowable items for each profile owner' => 'The admin may use this to over-ride the default value of allowable items for each profile owner',
'No Items published in this profile gallery' => 'No Items published in this profile gallery',
'Title:' => 'Title:',
'Description:' => 'Description:',
'Image File:' => 'Image File:',
'Submit New Gallery Entry' => 'Submit New Gallery Entry',
'Submit Gallery Entry' => 'Submit Gallery Entry',
'A file must be selected via the Browse button' => 'A file must be selected via the Browse button',
'A gallery item title must be entered' => 'A gallery item title must be entered',
'Autopublish items' => 'Autopublish items',
'Select Yes or No to autopublish or not newly uploaded gallery items' => 'Select Yes or No to autopublish or not newly uploaded gallery items',
'Current Storage' => 'Current Storage',
'This field keeps track of the total size of all uploaded gallery items - like a quota usage field. Value is in bytes.' => 'This field keeps track of the total size of all uploaded gallery items - like a quota usage field. Value is in bytes.',
'Greetings - connections only viewing enabled' => 'Greetings - connections only viewing enabled',
'Sorry - connections only viewing enabled for this gallery that currently has %1$d items in it.' => 'Sorry - connections only viewing enabled for this gallery that currently has %1$d items in it.',
'Automatically approve' => 'Automatically approve',
'This value can be set by the admin to over-ride the gallery plugin backend default approval parameter' => 'This value can be set by the admin to over-ride the gallery plugin backend default approval parameter',
'Storage Quota (KB)' => 'Storage Quota (KB)',
'This value can be set by the admin to over-ride the gallery plugin backend default user quota' => 'This value can be set by the admin to over-ride the gallery plugin backend default user quota',
'Maximum allowable single upload size exceeded - gallery item rejected' => 'Maximum allowable single upload size exceeded - gallery item rejected',
'File extension not authorized' => 'File extension not authorized',
/**
 * Parameters available for use in _pg_QuotaMessage language string
 * %1$d ~ Total count of items uploaded
 * %2$d ~ Maximum uploaded items allowed
 * %3$d ~ Total KB of uploaded items
 * %4$d ~ Maximum KB of uploaded items allowed
 * %5$d ~ Consumed storage percentage of uploaded items
 * %6$d ~ Free storage percentage of uploaded items
 */
' [Your current quota marks: %1$d/%2$d items %3$d/%4$d Kbytes (%5$d%% consumed - %6$d%% free)]' => ' [Your current quota marks: %1$d/%2$d items %3$d/%4$d Kbytes (%5$d%% consumed - %6$d%% free)]',
'This file would cause you to exceed you quota - gallery item rejected' => 'This file would cause you to exceed you quota - gallery item rejected',
'Access Mode' => 'Access Mode',
'Select desirable access mode: Public access, Registered users only, Connected users only, REG-S for Registered-stealth, CON-S for Connections-stealth' => 'Select desirable access mode: Public access, Registered users only, Connected users only, REG-S for Registered-stealth, CON-S for Connections-stealth',
'Allow Public Access' => 'Allow Public Access',
'Allow Registered Access' => 'Allow Registered Access',
'Allow Connections Access' => 'Allow Connections Access',
'Registered Stealth Access' => 'Registered Stealth Access',
'Connections Stealth Access' => 'Connections Stealth Access',
'Display Format' => 'Display Format',
'Select Display Format to apply for gallery viewing.' => 'Select Display Format to apply for gallery viewing.',
'Pictures gallery list format' => 'Pictures gallery list format',
'File list format' => 'File list format',
'Picture gallery list lightbox format' => 'Picture gallery list lightbox format',
'Gallery repository successfully created!' => 'Gallery repository successfully created!',
'Gallery repository could not be created! Please notify system admin!' => 'Gallery repository could not be created! Please notify system admin!',
'Image ToolBox failure! - Please notify system admin - ' => 'Image ToolBox failure! - Please notify system admin - ',
'The file upload has failed! - Please notify your system admin!' => 'The file upload has failed! - Please notify your system admin!',
/**
 * Parameters available for use in _pg_FileUploadSucceeded and _pg_FileUploadAndTnSucceeded language strings
 * %1$s ~ Name of uploaded file in user repository
 */
'The file %1$s has been successfully uploaded!' => 'The file %1$s has been successfully uploaded!',
'The file %1$s has been successfully uploaded and tn%1$s thumbnail created!' => 'The file %1$s has been successfully uploaded and tn%1$s thumbnail created!',
'Only Registered Members Allowed to view the %1$d items in this Gallery!' => 'Only Registered Members Allowed to view the %1$d items in this Gallery!',
'Delete' => 'Delete',
'Publish' => 'Publish',
'Unpublish' => 'Unpublish',
'Approve' => 'Approve',
'Revoke' => 'Revoke',
'Default setting' => 'Default setting',
'Are you sure you want to delete selected item ? The selected item will be deleted and cannot be undone!' => 'Are you sure you want to delete selected item ? The selected item will be deleted and cannot be undone!',
'Max single upload (KB)' => 'Max single upload (KB)',
'This value can be set by the admin to over-ride the gallery plugin backend default maximum single upload size' => 'This value can be set by the admin to over-ride the gallery plugin backend default maximum single upload size',
'Updated' => 'Updated',
'Title' => 'Title',
'Description' => 'Description',
'Download' => 'Download',
'Actions' => 'Actions',
'Never' => 'Never',
'Gallery Moderation' => 'Gallery Moderation',
'This tab contains all pending autorization gallery items' => 'This tab contains all pending autorization gallery items',
'New Gallery Item just uploaded' => 'New Gallery Item just uploaded',
/**
 * Parameters available for use in _pg_MSGBODY_NEW language string
 * %1\$s ~ item type
 * %2\$s ~ item title
 * %3\$s ~ item description
 * %4\$s ~ username
 * %5\$s ~ profile link
 */
"A new Gallery item has just been uploaded and may require approval.\n"
."This email contains the item details\n\n"
."Gallery Item Type - %1\$s\n"
."Gallery Item Title - %2\$s\n"
."Gallery Item Description - %3\$s\n\n"
."Username - %4\$s\n"
."Profile Link - %5\$s \n\n\n"
."Please do not respond to this message as it is automatically generated and is for information purposes only\n"
=>
"A new Gallery item has just been uploaded and may require approval.\n"
."This email contains the item details\n\n"
."Gallery Item Type - %1\$s\n"
."Gallery Item Title - %2\$s\n"
."Gallery Item Description - %3\$s\n\n"
."Username - %4\$s\n"
."Profile Link - %5\$s \n\n\n"
."Please do not respond to this message as it is automatically generated and is for information purposes only\n",

'Your Gallery Item has been approved!' => 'Your Gallery Item has been approved!',

"A Gallery item in your Gallery Tab has just been approved by a moderator.\n\n\n"
."Please do not respond to this message as it is automatically generated and is for information purposes only\n"
=>
"A Gallery item in your Gallery Tab has just been approved by a moderator.\n\n\n"
."Please do not respond to this message as it is automatically generated and is for information purposes only\n",

'Your Gallery Item has been revoked!' => 'Your Gallery Item has been revoked!',

"A Gallery item in your Gallery Tab has just been revoked by a moderator.\n\n\n"
."If you feel that this action is unjustified please contact one of our moderators.\n"
."Please do not respond to this message as it is automatically generated and is for information purposes only\n"
=>
"A Gallery item in your Gallery Tab has just been revoked by a moderator.\n\n\n"
."If you feel that this action is unjustified please contact one of our moderators.\n"
."Please do not respond to this message as it is automatically generated and is for information purposes only\n",

'Your Gallery Item has been deleted!' => 'Your Gallery Item has been deleted!',

"A Gallery item in your Gallery Tab has just been deleted by a moderator.\n\n\n"
."If you feel that this action is unjustified please contact one of our moderators.\n"
."Please do not respond to this message as it is automatically generated and is for information purposes only\n"
=>
"A Gallery item in your Gallery Tab has just been deleted by a moderator.\n\n\n"
."If you feel that this action is unjustified please contact one of our moderators.\n"
."Please do not respond to this message as it is automatically generated and is for information purposes only\n",

'Your Gallery item is pending approval by a site moderator.' => 'Your Gallery item is pending approval by a site moderator.',
'Your Gallery item quota has been reached. You must delete an item in order to upload a new one or you may contact the admin to increase your quota.' => 'Your Gallery item quota has been reached. You must delete an item in order to upload a new one or you may contact the admin to increase your quota.',
'Failed to be add index.html to the plugin gallery - please contact administrator!' => 'Failed to be add index.html to the plugin gallery - please contact administrator!',
'No item uploaded!' => 'No item uploaded!',
/**
 * Parameters available for use in _pgModeratorViewMessage
 * %1$d ~ Total count of items uploaded
 * %2$d ~ Maximum uploaded items allowed
 * %3$d ~ Total KB of uploaded items
 * %4$d ~ Maximum KB of uploaded items allowed
 * %5$s ~ access mode setting
 * %6$s ~ display format setting
 */
'<font color=red>Moderator data:<br />'
.'Items - %1$d<br />'
.'Item Quota - %2$d<br />'
.'Storage - %3$d<br />'
.'Storage Quota - %4$d<br />'
.'Access Mode - %5$s<br />'
.'Display Mode - %6$s<br /></font>'
=>
'<font color=red>Moderator data:<br />'
.'Items - %1$d<br />'
.'Item Quota - %2$d<br />'
.'Storage - %3$d<br />'
.'Storage Quota - %4$d<br />'
.'Access Mode - %5$s<br />'
.'Display Mode - %6$s<br /></font>',

'Image ' => 'Image ',
' of ' => ' of ',
'Image {x} of {y}' => 'Image {x} of {y}',
/**
 * Following section defines language strings used in CB Gallery Module
 */
'No Viewable Items' => 'No Viewable Items',
'No items rendered' => 'No items rendered',

'Edit Gallery Item' => 'Edit Gallery Item',
'Edit' => 'Edit',
'Update' => 'Update',

'Bad File - Item rejected' => 'Bad File - Item rejected'
) );

// Privacy plugin: (new method: UTF8 encoding here):
CBTxt::addStrings( array(
'Visible on profile'					=>	'Visible on profile',
'Only to logged-in users'				=>	'Only to logged-in users',
'Only for direct connections'			=>	'Only for direct connections',
'Only for %s'							=>	'Only for %s',
'Also for connections\' connections'	=>	'Also for connections\' connections',
'Invisible on profile'					=>	'Invisible on profile',
'Access only to logged-in users. Please login.'					=>	'Access only to logged-in users. Please login.',
'Access only to logged-in users. Please login or %s.'				=>	'Access only to logged-in users. Please login or %s.',
'register'															=>	'register',
'Access only with login'											=>	'Access only with login',
'Access only to directly connected users'							=>	'Access only to directly connected users',
'Access only to directly connected users and friends of friends'	=>	'Access only to directly connected users and friends of friends',
));

// Activity plugin: (new method: UTF8 encoding here):
CBTxt::addStrings( array(
'updated his profile'					=>	'updated his profile',
'is now'								=>	'is now',
'%s is now %s'							=>	'%s is now %s',
'%s and %s are now %s'					=>	'%s and %s are now %s',
'connected'								=>	'connected',
'%s and %s'								=>	'%s and %s',
'%s, %s and %s'							=>	'%s, %s and %s'	,
'%s, %s, %s and %s more'				=>	'%s, %s, %s and %s more',
'%s added a new %s'						=>	'%s added a new %s'	,
'%s added new %s'						=>	'%s added new %s',
'picture'								=>	'picture',
'pictures'								=>	'pictures',
'profile book'							=>	'profile book',
'profile gallery'						=>	'profile gallery',
'wall'									=>	'wall',
'%s added a new %s in %s'				=>	'%s added a new %s in %s',
'%s added a new %s in %s\'s %s'		=>	'%s added a new %s in %s\'s %s',
'%s added a new %s in the %s'			=>	'%s added a new %s in the %s',
'%s updated a %s in %s'				=>	'%s updated a %s in %s',
'%s updated a %s in %s\'s %s'			=>	'%s updated a %s in %s\'s %s',
'%s updated a %s in the %s'			=>	'%s updated a %s in the %s',
'comment'								=>	'comment',
'note'									=>	'note',
'tag'									=>	'tag',
'profile'								=>	'profile',
'rating'								=>	'rating',
'%s commented on %s\'s %s'				=>	'%s commented on %s\'s %s',
'%s commented on own %s'				=>	'%s commented on own %s',
'%s has joined'							=>	'%s has joined',
'%s has posted on %s'					=>	'%s has posted on %s',
'%s joined the %s %s'					=>	'%s joined the %s %s',
'group'									=>	'group',
));
// Ratings fields plugin: (new method: UTF8 encoding here):
CBTxt::addStrings( array(
'Thank you for rating!'				=>	'Thank you for rating!',
'Click on a star to rate!'				=>	'Click on a star to rate!',
// Rate 1 Star:
'Rate %s %s'							=>	'Rate %s %s',
'Cancel Rating'							=>	'Cancel Rating',
// following rating strings can be used/changed in field's param
'Self'									=>	'Self',
'Visitor'								=>	'Visitor',
'Rating'								=>	'Rating',
'Star'									=>	'Star',
'Stars'									=>	'Stars',
'Poorest'								=>	'Poorest',
'Poor'									=>	'Poor',
'Average'								=>	'Average',
'Good'									=>	'Good',
'Better'								=>	'Better',
'Best'									=>	'Best',
));

// IMPORTANT WARNING: The closing tag, "?" and ">" has been intentionally omitted - CB works fine without it.
// This was done to avoid errors caused by custom strings being added after the closing tag. ]
// With such tags, always watchout to NOT add any line or space or anything after the "?" and the ">".
