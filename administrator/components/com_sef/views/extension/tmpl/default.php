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
?>

<script type="text/javascript">
<!--
function submitbutton(pressbutton)
{
    if( pressbutton == 'cancel' ) {
        submitform(pressbutton);
        return;
    }
    
    // Create the filters array
    var txt = '';
    for( var i = 0, n = filters.length; i < n; i++ ) {
        if( i > 0 ) {
            txt += '\n';
        }
        txt += filters[i][0] + '=';
        for( var j = 1, m = filters[i].length; j < m; j++ ) {
            if( j > 1 ) {
                txt += ',';
            }
            txt += filters[i][j];
        }
    }
    
    // Set the value and send the form
    document.adminForm.filters.value = txt;
    submitform(pressbutton);
}

var filters = new Array();
var acceptVars = new Array();

<?php
// Create the arrays of variable filter rules
if( count($this->acceptVars) > 0 ) {
    $i = 0;
    foreach($this->acceptVars as $acceptVar) {
        echo "acceptVars[{$i}] = '{$acceptVar}';\n";
        $i++;
    }
}

$i = 0;
if( count($this->filters['pos']) > 0 ) {
    foreach($this->filters['pos'] as $filter) {
        echo "filters[{$i}] = new Array('+{$filter->rule}'";
        foreach($filter->vars as $var) {
            echo ", '{$var}'";
        }
        echo ");\n";
        $i++;
    }
}
if( count($this->filters['neg']) > 0 ) {
    foreach($this->filters['neg'] as $filter) {
        echo "filters[{$i}] = new Array('-{$filter->rule}'";
        foreach($filter->vars as $var) {
            echo ", '{$var}'";
        }
        echo ");\n";
        $i++;
    }
}
?>

function removeRule()
{
    var el = $('selRules');
    
    var i = el.selectedIndex;
    if( i < 0 ) {
        return;
    }

    // Remove the option from list
    el.remove(i);
    
    // Remove the filter from array
    filters.splice(i, 1);

    // Select the correct remaining rule
    if( el.length > 0 ) {
        if( i >= el.length ) {
            i = el.length - 1;
        }
        el.selectedIndex = i;
    }
    
    ruleClicked();
}

function removeAllRules()
{
    // Confirm
    var q = confirm('<?php echo JText::_('WARNING_REMOVE_FILTER_ALL_RULES'); ?>');
    if( !q ) {
        return;
    }
    
    var el = $('selRules');
    
    // Remove options from list
    el.options.length = 0;
    
    // Remove all the filters
    filters.length = 0;
    
    ruleClicked();
}

function addRule()
{
    var re = $('ruleRegExp').value;
    var neg = $('ruleNegate').checked;
    
    // Check regular expression
    if( re == '' ) {
        alert('<?php echo JText::_('WARNING_ADD_FILTER_RULE_EMPTY'); ?>');
        return;
    }
    
    // Check if the rule already exists
    var txt = (neg ? '-' : '+') + re;
    for( var i = 0, n = filters.length; i < n; i++ ) {
        if( filters[i][0] == txt ) {
            alert('<?php echo JText::_('WARNING_FILTER_RULE_EXISTS'); ?>');
            return;
        }
    }
    
    // Create new filter in array
    filters.push(new Array(txt));
    
    // Add the option to list
    var el = $('selRules');
    txt = re;
    if( neg ) {
        txt = 'NOT ' + txt;
    }
    try {
        el.add(new Option(txt, el.length)); // IE, Opera
    }
    catch(e) {
        el.add(new Option(txt, el.length), null); // FF
    }
    
    // Select new filter
    el.selectedIndex = el.length - 1;
    ruleClicked();
}

function ruleClicked()
{
    var el = $('selRules');
    var assigned = $('assignedVars');
    
    // Clear the assigned vars list
    assigned.options.length = 0;
    
    // Add all the assigned variables
    if( el.selectedIndex >= 0 ) {
        for( var i = 1, n = filters[el.selectedIndex].length; i < n; i++ ) {
            try {
                assigned.add(new Option(filters[el.selectedIndex][i], i)); // IE, Opera
            }
            catch(e) {
                assigned.add(new Option(filters[el.selectedIndex][i], i), null); // FF
            }
        }
    }
    
    showAvailableVars();
}

function showAvailableVars()
{
    var el = $('selRules');
    var available = $('availableVars');
    
    // Clear the available vars list
    available.options.length = 0;
    
    // Add the available vars
    var filter = null;
    
    var ind = el.selectedIndex;
    if( ind >= 0 ) {
        filter = filters[ind];
    }
    
    for( var i = 0, n = acceptVars.length; i < n; i++ ) {
        if( (filter != null) && (filter.indexOf(acceptVars[i], 1) > 0) ) {
            continue;
        }
        
        try {
            available.add(new Option(acceptVars[i], i)); // IE, Opera
        }
        catch(e) {
            available.add(new Option(acceptVars[i], i), null); // FF
        }
    }
}

function addAll()
{
    var el = $('selRules');
    if( el.selectedIndex < 0 ) {
        return;
    }
    
    var available = $('availableVars');
    var vars = new Array();
    
    // Get all the available variables
    for( var i = 0, n = available.length; i < n; i++ ) {
        vars.push(available.options[i].text);
    }
    
    // Add variables
    addVars(vars);
}

function addSelected()
{
    var el = $('selRules');
    if( el.selectedIndex < 0 ) {
        return;
    }
    
    var available = $('availableVars');
    var vars = new Array();
    
    // Get selected available variables
    for( var i = 0, n = available.length; i < n; i++ ) {
        if( available.options[i].selected ) {
            vars.push(available.options[i].text);
        }
    }
    
    // Add variables
    addVars(vars);
}

function removeSelected()
{
    var el = $('selRules');
    if( el.selectedIndex < 0 ) {
        return;
    }
    
    var assigned = $('assignedVars');
    var vars = new Array();
    
    // Get selected assigned variables
    for( var i = 0, n = assigned.length; i < n; i++ ) {
        if( assigned.options[i].selected ) {
            vars.push(assigned.options[i].text);
        }
    }
    
    // Add variables
    removeVars(vars);
}

function removeAll()
{
    var el = $('selRules');
    if( el.selectedIndex < 0 ) {
        return;
    }
    
    var assigned = $('assignedVars');
    var vars = new Array();
    
    // Get all the assigned variables
    for( var i = 0, n = assigned.length; i < n; i++ ) {
        vars.push(assigned.options[i].text);
    }
    
    // Add variables
    removeVars(vars);
}

function addVars(vars)
{
    var el = $('selRules');
    var ind = el.selectedIndex;
    if( ind < 0 ) {
        return;
    }
    
    // Get the assigned variables, remove them from filter, and add them to new vars
    for( var i = 0, n = filters[ind].length - 1; i < n; i++ ) {
        vars.push(filters[ind].pop());
    }
    
    // Sort the variables
    vars.sort();
    
    // Add them back to filter
    for( var i = 0, n = vars.length; i < n; i++ ) {
        filters[ind].push(vars.shift());
    }
    
    // Update lists
    ruleClicked();
}

function removeVars(vars)
{
    var el = $('selRules');
    var ind = el.selectedIndex;
    if( ind < 0 ) {
        return;
    }
    
    // Loop through the vars and remove them from assigned variables
    for( var i = 0, n = vars.length; i < n; i++ ) {
        var pos = filters[ind].indexOf(vars[i], 1);
        if( pos > 0 ) {
            filters[ind].splice(pos, 1);
        }
    }
    
    // Update lists
    ruleClicked();
}

-->
</script>

<form action="index.php" method="post" name="adminForm">

<div class="col width-60">
    <?php
    if( !empty($this->extension->name) ) {
        ?>
        <fieldset class="adminform">
            <legend><?php echo JText::_( 'Extension Details' ); ?></legend>
            
            <table class="admintable">
                <tr>
                    <td class="key">
                        <?php echo JText::_('Name'); ?>:
                    </td>
                    <td>
                        <?php echo $this->extension->name; ?>
                    </td>
                </tr>
                <tr>
                    <td class="key">
                        <?php echo JText::_('Version'); ?>:
                    </td>
                    <td>
                        <?php echo $this->extension->version; ?>
                    </td>
                </tr>
                <tr>
                    <td class="key">
                        <?php echo JText::_('Description'); ?>:
                    </td>
                    <td>
                        <?php echo $this->extension->description; ?>
                    </td>
                </tr>
            </table>
        </fieldset>
        <?php
    }
    ?>
    
    <?php
    if( !is_null($this->extension->component) ) {
        ?>
        <fieldset class="adminform">
            <legend><?php echo JText::_( 'Component Details' ); ?></legend>
            
            <table class="admintable">
                <tr>
                    <td class="key">
                        <?php echo JText::_('Name'); ?>:
                    </td>
                    <td>
                        <?php echo $this->extension->component->name; ?>
                    </td>
                </tr>
                <tr>
                    <td class="key">
                        <?php echo JText::_('Option'); ?>:
                    </td>
                    <td>
                        <?php echo $this->extension->component->option; ?>
                    </td>
                </tr>
            </table>
        </fieldset>
        <?php
    }
    ?>
    
    <fieldset class="adminform">
        <legend><?php echo JText::_('Variables filtering'); ?></legend>

        <a href="#" onclick="javascript:$('filterdiv').style.display='block';this.style.display='none';return false;">Show</a>
        <div id="filterdiv" style="display:none">
        <table width="100%">
            <tr>
                <th align="left"><?php echo JText::_('Usage'); ?></th>
            </tr>
            <tr>
                <td><?php echo JText::_('DESC_VARIABLE_FILTER_USAGE'); ?></td>
            </tr>
        </table>
        
        <table width="100%">
            <tr>
                <th align="left" colspan="2"><?php echo JText::_('Add rule'); ?></th>
            </tr>
            <tr>
                <td>
                	<?php echo JText::_('Regular expression'); ?>:
                    <input type="text" name="ruleRegExp" id="ruleRegExp" value="" size="25" />
                    <?php echo JText::_('negate this rule'); ?>
                    <input type="checkbox" name="ruleNegate" id="ruleNegate" />
                    <input type="button" value="<?php echo JText::_('Add rule'); ?>" onclick="addRule();" />
                </td>
            </tr>
        </table>        
        
        <table width="100%">
            <tr>
                <th align="left" width="40%"><?php echo JText::_('Rules'); ?></th>
                <th align="left" width="25%"><?php echo JText::_('Assigned variables'); ?></th>
                <th align="left" width="10%">&nbsp;</th>
                <th align="left" width="25%"><?php echo JText::_('Available variables'); ?></th>
            </tr>
            <tr>
                <td>
                    <select name="selRules" id="selRules" size="10" onchange="ruleClicked();" style="width: 90%;">
                        <?php
                        // Create options for rules
                        $i = 0;
                        if( count($this->filters['pos']) > 0 ) {
                            foreach($this->filters['pos'] as $filter) {
                                ?>
                                <option value="<?php echo $i; ?>"><?php echo $filter->rule; ?></option>
                                <?php
                                $i++;
                            }
                        }
                        if( count($this->filters['neg']) > 0 ) {
                            foreach($this->filters['neg'] as $filter) {
                                ?>
                                <option value="<?php echo $i; ?>">NOT <?php echo $filter->rule; ?></option>
                                <?php
                                $i++;
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>
                    <select name="assignedVars" id="assignedVars" size="10" multiple="multiple" ondblclick="removeSelected();" style="width: 100%;">
                    </select>
                </td>
                <td align="center">
                    <input class="hasTip" title="<?php echo JText::_('TT_ADD_ALL_VARIABLES'); ?>" type="button" value="&lt;&lt;" onclick="addAll();" style="margin: 5px;" /><br />
                    <input class="hasTip" title="<?php echo JText::_('TT_ADD_SELECTED_VARIABLES'); ?>" type="button" value="&lt;" onclick="addSelected();" style="margin: 5px;" /><br />
                    <input class="hasTip" title="<?php echo JText::_('TT_REMOVE_SELECTED_VARIABLES'); ?>" type="button" value="&gt;" onclick="removeSelected();" style="margin: 5px;" /><br />
                    <input class="hasTip" title="<?php echo JText::_('TT_REMOVE_ALL_VARIABLES'); ?>" type="button" value="&gt;&gt;" onclick="removeAll();" style="margin: 5px;" />
                </td>
                <td>
                    <select name="availableVars" id="availableVars" size="10" multiple="multiple" ondblclick="addSelected();" style="width: 100%;">
                        <?php
                        // Create options for accept vars
                        $i = 0;
                        if( count($this->acceptVars) > 0 ) {
                            foreach($this->acceptVars as $var) {
                                ?>
                                <option value="<?php echo $i; ?>"><?php echo $var; ?></option>
                                <?php
                                $i++;
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <input type="button" value="<?php echo JText::_('Remove selected rule'); ?>" onclick="removeRule();" />
                    <input type="button" value="<?php echo JText::_('Remove all rules'); ?>" onclick="removeAllRules();" />
                </td>
            </tr>
        </table>
        </div>
        
    </fieldset>
</div>

<div class="col width-40">
    <fieldset class="adminform">
        <legend><?php echo JText::_( 'Parameters' ); ?></legend>
        
        <?php
        echo $this->pane->startPane('ext-pane');
        
        // Render each parameters group
        $groups = $this->extension->params->getGroups();
        if (is_array($groups) && count($groups) > 0) {
            $i = 0;
            foreach ($groups as $group => $count) {
                if ($count > 0) {
                    if ($group == '_default') $label = JText::_('Extension');
                    else $label = JText::_($group);
                    $i++;
                    echo $this->pane->startPanel($label, 'page-'.$i);
                    echo $this->extension->params->render('params', $group);
                    echo $this->pane->endPanel();
                }
            }
        }
        
        echo $this->pane->endPane();
        ?>
    </fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="controller" value="extension" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="file" value="<?php echo $this->extension->file; ?>" />
<input type="hidden" name="redirto" value="<?php echo $this->redirto; ?>" />
<input type="hidden" name="filters" value="" />

<?php echo JHTML::_( 'form.token' ); ?>
</form>
