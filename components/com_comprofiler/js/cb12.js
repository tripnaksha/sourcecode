function cbsaveorder( cb, n, fldName, task, subtaskName, subtaskValue ) {
    cbCheckAllRowsAndSubTask( cb, n, fldName, subtaskName, subtaskValue );
    submitform( task );
}
//needed by cbsaveorder function
function cbCheckAllRowsAndSubTask( cb, n, fldName, subtaskName, subtaskValue ) {
    if (!fldName) {
        fldName = 'cb';
    }
    f = cbParentForm( cb );
    for ( var i = 0; i < n; i++ ) {
             box = f.elements[fldName+i];
             if ( box.checked == false ) {
                     box.checked = true;
             }
    }
	if (subtaskName && subtaskValue) {
		f.elements[subtaskName].value = subtaskValue;
	}
}
/**
* Toggles the check state of a group of boxes
*
* Checkboxes must have an id attribute in the form cb0, cb1...
* @param the id of the toggle button
* @param The number of box to 'check'
* @param An alternative field name id prefix
*/
function cbToggleAll( tgl, n, fldName ) {
    if (!fldName) {
        fldName = 'cb';
    }
    var frm = tgl.form;
    for (i=0; i < n; i++) {
        cb = eval( 'frm.' + fldName + i );
        if (cb) {
            cb.checked = tgl.checked;
        }
    }
    return true;
}

function cbParentForm(cb) {
	var f;
	if ( cb == window  ) {
		f = window.event.srcElement;	// IE
	} else {
		f = cb;
	}
	while (f) {
		f = f.parentNode;
		if (f.nodeName == 'FORM') {
			break;
		}
	}
	return f;
}
/**
* Performs task/subtask on table row id
*/
function cbListItemTask( cb, task, subtaskName, subtaskValue, fldName, id ) {
    var f = cbParentForm(cb);
    if (cb) {
        for (i = 0; true; i++) {
            cbx = f.elements[fldName+i];
            if (!cbx) {
            	break;
            }
            if ( i == id ) {
	            cbx.checked = true;
            } else {
	            cbx.checked = false;
        	}
        }
		f.elements[subtaskName].value = subtaskValue;
        submitbutton(task);
    }
    return false;
}
/**
* Performs task/subtask on selected table rows
*/
function cbDoListTask( cb, task, subtaskName, subtaskValue, fldName ) {
    var f = document.forms['adminForm'];
    if (cb) {
    	var oneChecked = false;
        for (i = 0; true; i++) {
            cbx = f.elements[fldName+i];
            if ( ! cbx ) {
            	break;
            }
            if ( cbx.checked ) {
	            oneChecked = true;
	            break;
        	}
        }
        if ( oneChecked ) {
        	if ( subtaskValue == 'deleterows' ) {
        		if ( ! confirm('Are you sure you want to delete selected items ?') ) { 
        			return false;
        		}
        	}
			f.elements[subtaskName].value = subtaskValue;
    	    submitbutton(task);
        } else {
        	alert( "no items selected" );
        }
    }
    return false;
}

function submitbutton(pressbutton) {
	if (pressbutton == "showPlugins" || pressbutton == "cancelPlugin" || pressbutton == "cancelPluginAction") {
		cbsubmitform(pressbutton);
		return false;
	}
	// validation
	var form = document.forms['adminForm'];
	if ( ( typeof(form.elements['name']) != "undefined") && ( form.elements['name'].value == "" ) ) {
		alert( "Plugin must have a name" );
	} else {
		cbsubmitform(pressbutton);
	}
	return false;
}

/**
* Submit the admin form
*/
function cbsubmitform(pressbutton){
	document.forms['adminForm'].elements['task'].value = pressbutton;
	if ( typeof(document.forms['adminForm']) != 'undefined' ) {
		try {
			document.forms['adminForm'].onsubmit();
			}
		catch(e){}
	}
	document.forms['adminForm'].submit();
}

/**
* general cb DOM events handler
*/

var cbW3CDOM = (document.createElement && document.getElementsByTagName);

function cbGetElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null ) {
		node = document;
	}
	if ( tag == null ) {
		tag = '*';
	}
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp('(^|\\s)'+searchClass+'(\\s|$)');
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

function cbAddEvent(obj, evType, fn){
	if (obj.addEventListener){
		obj.addEventListener(evType, fn, true);
		return true;
	} else if (obj.attachEvent){
		var r = obj.attachEvent("on"+evType, fn);
		return r;
	} else {
		return false;
	}
}

function cbAddEventObjArray(objArr, evType, fn){
	for (var j=0;j<objArr.length;j++) {
		if (objArr[j].type != 'hidden') {
			eval('objArr[j].on' + evType + '=fn');
			/* cbAddEvent( objArr[j], evType, fn ); */
		}
	}
}

/**
* CB filters events handler
*/
function cbInitFiltersBlur()
{
	if (!cbW3CDOM) {
		return;
	}
	var nav = cbGetElementsByClass('cbFilters');
	if ((nav.length == 1) && (nav[0].getElementsByTagName('input').length == 1)) {		//TBD TEST!
		for (var i=0;i<nav.length;i++) {
			cbAddEventObjArray( nav[i].getElementsByTagName('input'),  'change', cbFilterInputBlur );
			cbAddEventObjArray( nav[i].getElementsByTagName('select'), 'change', cbFilterInputBlur );
		}
	}
}

function cbFilterInputBlur(thisevent) {
//	var mine;
//	if (thisevent) {
//		mine = (thisevent.target.parentNode == this);
//	} else if (window.event.target) {
//		mine = (window.event.target==window.event.currentTarget);
//	} else if (window.event.srcElement) {
//		mine = (window.event.srcElement.parentNode == this);
//	}
//	if (mine) {
		cbParentForm(this).submit();
		return false;
//	}
//	return !mine;
}

cbAddEvent(window, 'load', cbInitFiltersBlur);

/**
* CB hide and set fields depending on other fields:
*/

var cbHideFields = new Array();
var cbParamsSaveBefHide = new Array();
var cbSels = new Array();
var cbPreviousOnChangeValues = new Array();

function cbGetDisplayStyle( dt ) {
	var ds;
	if (dt.style.getPropertyValue) {
		ds = dt.style.getPropertyValue("display");
	} else {
		ds = dt.style.display;
	}
	return ds;
}
/**
* CB change params hidding/showing actions
*/
function cbParamChange() {
	var fieldsToShow = new Array();
	var fieldsToHide = new Array();
	var value, nameValue, inputToSet, field;
	var changedDoPost	=	false;
	var i,j,k,alreadyHidden;
	for (i=0;i<cbHideFields.length;i++) {
		for (j=1;j<cbSels[i].length;j++) {
			if (cbSels[i][j].type != 'hidden') {
				/*
				var name = cbSels[i][j].name;
				if ( name.substr(-2, 2)  == '[]' ) {
					name = name.substr(0, name.length-2);
				}
				*/
				if ((cbSels[i][j].type == 'radio') || (cbSels[i][j].type == 'checkbox') ) {
					if ( cbSels[i][j].checked ) {
						value = cbSels[i][j].value;
					}
				} else {
					value = cbSels[i][j].value;
				}
			}
		}
		// already the case: if (cbHideFields[1] == cbSels[i][0].id)
		var cMatch = false;
		switch (cbHideFields[i][2]) {
			case '==': if ( value == cbHideFields[i][3] ) { cMatch = true; } break;
			case '!=': if ( value != cbHideFields[i][3] ) { cMatch = true; } break;
			case '>=': if ( value >= cbHideFields[i][3] ) { cMatch = true; } break;
			case '<=': if ( value <= cbHideFields[i][3] ) { cMatch = true; } break;
			case '>' : if ( value >  cbHideFields[i][3] ) { cMatch = true; } break;
			case '<' : if ( value <  cbHideFields[i][3] ) { cMatch = true; } break;
			case 'regexp' :
				var cbRegexp = new RegExp(cbHideFields[i][3]);
				cMatch = ( ! cbRegexp.test(value) );
				break;
			case 'evaluate' :
				break;
			default:
				alert('js error operator "'+cbHideFields[i][2]+'" unknown.');
				break;
		}
		if (cbHideFields[i][2] == 'evaluate' ) {
			if (typeof(cbPreviousOnChangeValues[cbHideFields[i][1]]) != 'undefined') {
				if (cbPreviousOnChangeValues[cbHideFields[i][1]] != value) {
					changedDoPost	=	true;
				}
			}
			cbPreviousOnChangeValues[cbHideFields[i][1]]	=	value;
		} else {
			if ( cMatch ) {
				// Match: Hide fields, removing them from the shown fields:

				for (j=0;j<fieldsToShow.length;j++) {
					for (k=0;k<cbHideFields[i][4].length;k++) {
						if (cbHideFields[i][4][k] == fieldsToShow[j]) {
							fieldsToShow.splice(j, 1);
						}
					}
				}

				fieldsToHide = fieldsToHide.concat( cbHideFields[i][4] );
				if ( cbHideFields[i][5].length > 0 ) {

					// Fields to set: set them now so they can evaluate properly above:
					if ( cbGetDisplayStyle( document.getElementById( cbHideFields[i][0] ) ) != 'none' ) {
						for (j=0;j<cbHideFields[i][5].length;j++) {
							nameValue  = cbHideFields[i][5][j].split('=',3);
							if ( cbGetDisplayStyle( document.getElementById( cbHideFields[i][4][0] /* nameValue[0] */ ) ) != 'none' ) {
								inputToSet = document.getElementById( nameValue[1] );
								if (typeof(cbParamsSaveBefHide[i])=='undefined') {
									cbParamsSaveBefHide[i] = new Array();
								}
								cbParamsSaveBefHide[i][j] = inputToSet.value;
								inputToSet.value = nameValue[2];
							}
						}
					}

				}
			} else {
				// No match: Show fields if not already hidden:
				alreadyHidden = false;
				for (j=0;j<fieldsToHide.length;j++) {
					for (k=0;k<cbHideFields[i][4].length;k++) {
						if (cbHideFields[i][4][k] == fieldsToHide[j]) {
							alreadyHidden = true;
						}
					}
				}
				if ( ! alreadyHidden ) {
					fieldsToShow = fieldsToShow.concat( cbHideFields[i][4] );
					if ( cbHideFields[i][5].length > 0 ) {
	
						// Fields to restore: restore them now, so they can evaluate properly above:
						// TBD:Opera doesn't restore correctly with radio choice
	//					if ( cbGetDisplayStyle( document.getElementById( cbHideFields[i][0] ) ) == 'none' ) {
							for (j=0;j<cbHideFields[i][5].length;j++) {
								nameValue  = cbHideFields[i][5][j].split('=',3);
								if ( cbGetDisplayStyle( document.getElementById( cbHideFields[i][4][0] /* nameValue[0] */ ) ) == 'none' ) {
									inputToSet = document.getElementById( nameValue[1] );
									inputToSet.value = cbParamsSaveBefHide[i][j];
								}
							}
	//					}
	
					}
				}
			}
		}
	}

	if (typeof(jQuery)=='undefined') {
		for (i=0;i<fieldsToShow.length;i++) {
			field = document.getElementById(fieldsToShow[i]);
			if (field) {
				field.style.display = '';
			} else {
				alert('field not found number: '+i+' id:'+fieldsToShow[i]);
			}
		}
		for (i=0;i<fieldsToHide.length;i++) {
			field = document.getElementById(fieldsToHide[i]);
			if (field) {
				field.style.display = 'none';
			} else {
				alert('field not found number: '+i+' id:'+fieldsToHide[i]);
			}
		}
	} else {
		for (i=0;i<fieldsToShow.length;i++) {
			// jQuery( document.getElementById(fieldsToShow[i]) ).slideDown("slow");	//  http://dev.jquery.com/ticket/2041
			jQuery( document.getElementById(fieldsToShow[i]) ).fadeIn("slow");
		}
		for (i=0;i<fieldsToHide.length;i++) {
			// jQuery( document.getElementById(fieldsToHide[i]) ).slideUp("slow");
			jQuery( document.getElementById(fieldsToHide[i]) ).fadeOut("slow", function() {
					jQuery( this ).hide();
					});
		}
	}

	if ( changedDoPost ) {
		cbParentForm(this).submit();
		return false;
	}
}

function cbInitFields()
{
	if (!cbW3CDOM) {
		return;
	}

	if (typeof(overlib_pagedefaults)!='undefined') {
		overlib_pagedefaults(WIDTH,250,VAUTO,RIGHT,AUTOSTATUSCAP, CSSCLASS,TEXTFONTCLASS,'cb-tips-font',FGCLASS,'cb-tips-fg',BGCLASS,'cb-tips-bg',CAPTIONFONTCLASS,'cb-tips-capfont', CLOSEFONTCLASS, 'cb-tips-closefont');
	}
	if (typeof(cbHideFields)=='undefined') {
		return;
	}

	for (var i=0;i<cbHideFields.length;i++) {
		var inputDom = document.getElementById(cbHideFields[i][0]);
		if ( inputDom === null ) {
			alert('xml name ' + cbHideFields[i][0] + ' is undefined. It is cbHideFields[' + i + '][0].');
		} else {
			var sels = inputDom.getElementsByTagName('input');
			if ( sels.length == 0 ) {
				sels = inputDom.getElementsByTagName('select');
			}
			var k = 1;
			cbSels[i] = new Array();
			cbSels[i][0] = inputDom;
			for (var j=0;j<sels.length;j++) {
				if (sels[j].type != 'hidden') {
					if (sels[j].type == 'text') {
						cbAddEvent( sels[j], 'change', cbParamChange );
					} else {
						cbAddEvent( sels[j], 'click', cbParamChange );
					}
					cbSels[i][k++] = sels[j];
				}
			}
		}
	}
	cbParamChange();
}

cbAddEvent(window, 'load', cbInitFields);


/**
* CB basic ajax library (experimental): OBSOLETED IN CB 1.2: USE JQUERY !
*/


function CBgetHttpRequestInstance() {
	var http_request = false;

	if (window.XMLHttpRequest) { // Mozilla, Safari,...
		http_request = new XMLHttpRequest();
		if (http_request.overrideMimeType) {
			http_request.overrideMimeType('text/xml');
		}
	} else if (window.ActiveXObject) { // IE
		try {
			http_request = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				http_request = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {}
		}
	}
	return http_request;
}

function CBmakeHttpRequest(url, id, errorText, postsVars, http_request) {
	if ((arguments.length < 5) || (http_request==null) ) {
		http_request = CBgetHttpRequestInstance();
	}
	if (!http_request) {
		// alert('Giving up: Cannot create an XMLHTTP instance');
		return false;
	}
	http_request.onreadystatechange = function() { CBalertContents(http_request); };
	if (postsVars == null) {
		http_request.open('GET', url, true);
		http_request.send(null);
	} else {
		http_request.open('POST', url, true);
		http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http_request.setRequestHeader("Content-length", postsVars.length);
		http_request.send(postsVars);
	}

	function CBalertContents(http_request) {
		if (http_request.readyState == 4) {
			if ((http_request.status == 200) && (http_request.responseText.length > 0) && (http_request.responseText.length < 1025)) {
				document.getElementById(id).innerHTML = http_request.responseText;
			} else if (errorText.length > 0) {
				document.getElementById(id).innerHTML = errorText;
			} else {
				document.getElementById(id).innerHTML = '';
			}
			http_request = null;
		}
	}

	return true;
}
