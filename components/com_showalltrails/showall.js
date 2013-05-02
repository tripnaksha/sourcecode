//get the location of this script
var scripts = document.getElementsByTagName("script") ;
for(var i = 0 ; i < scripts.length ; i++)
{
	var scriptSource = scripts[i].src ;
	var temp = scriptSource.indexOf("showall.js") ;
	if(temp >= 0)
	{
	   jsLocation = scriptSource.slice(0, temp);
	   break;
	   //below will work only if 'images' and 'js' files are in separate folders at the same level.
	   //rtLocation is the root component folder and rtImageLocation is the images folder
//	   rtLocation = jsLocation.substring(0, jsLocation.lastIndexOf('/'));
//	   rtImageLocation = jsLocation.substring(0, jsLocation.lastIndexOf('/')-3) + "/images/" ;
	}
}
var trailId, userId, ratingVal;

//if (!isMSIE) window.onload=pageload();

/*
function ajaxFunction(url, queryString, retFunction) {
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
			   retFunction(ajaxRequest);
			}
		}
	   };
	} catch (error3) {
		alert('Page not found');
		return false;
	}
}
*/
function likeit(rating, uid, trailid) {
	ratingVal = rating;
	userId = uid;
	trailId = trailid;
	var ratingmsg = document.getElementById('ratingmsg'+trailid);
	if (uid > 0)
	{
	   ratingmsg.innerHTML = "<img src=\"" + jsLocation + "/loading.gif\" border=\"0\" align=\"absmiddle\" /><small> Saving</small>";
	   var queryString = "uid=" + uid + "&trailid=" + trailid + "&rating=" + rating + "&mode=rating";
	   ajaxFunction("index.php?option=com_saverating&format=raw", queryString, savedone);
//	ajaxFunction(url, queryString, returnVar, retFunction);
	}
	else
	   ratingmsg.innerHTML = "<small><font color=\"red\">Please login to rate!</font></small>";
	setTimeout("document.getElementById('ratingmsg"+trailId+"').innerHTML='&nbsp;'", 2000);
}

function savedone(text){
//alert(text.responseText);
	if (text.responseText.indexOf("Save") == -1)
	{
	  document.getElementById('ratingmsg'+trailId).innerHTML = '<small><font color=\"red\">Could not save!</font></small>';
	  setTimeout("document.getElementById('ratingmsg"+trailId+"').innerHTML='&nbsp;'", 2000);
	  return;
	}
	else if (text.responseText.indexOf("Saverate") != -1)
	{
	  document.getElementById('ratingmsg'+trailId).innerHTML = '<small><font color=\"green\">Saved!</font></small>';
	  setTimeout("document.getElementById('ratingmsg"+trailId+"').innerHTML='&nbsp;'", 2000);

	  var like = document.getElementById('like'+trailId);
	  var likeimg = document.getElementById('likeimg'+trailId);
	  var unlike = document.getElementById('unlike'+trailId);
	  var unlikeimg = document.getElementById('unlikeimg'+trailId);

	  like.removeChild(likeimg);
	  unlike.removeChild(unlikeimg);
	  var likeimg2 = document.createElement('img');
	  var unlikeimg2 = document.createElement('img');
	  likeimg2.id = 'likeimg'+trailId;
	  unlikeimg2.id = 'unlikeimg'+trailId;
	  if (ratingVal == 1)
	  {
	    likeimg2.src = jsLocation + "/thumbs_upc.png";
	    unlikeimg2.src = jsLocation + "/thumbs_down.png";
	  }
	  else
	  {
	    likeimg2.src = jsLocation + "/thumbs_up.png";
	    unlikeimg2.src = jsLocation + "/thumbs_downc.png";
	  }
	  likeimg2.style.width = "25px";
	  likeimg2.style.height = "25px";
	  likeimg2.title = "Like it!";
	  like.appendChild(likeimg2);
	  unlikeimg2.style.width = "25px";
	  unlikeimg2.style.height = "25px";
	  unlikeimg2.title = "Don't like it.";

	  like.appendChild(likeimg2);
	  unlike.appendChild(unlikeimg2);
	}
	else if (text.responseText.indexOf("Savefav") != -1)
	{
	  document.getElementById('ratingmsg'+trailId).innerHTML = '<small><font color=\"green\">Saved!</font></small>';
	  setTimeout("document.getElementById('ratingmsg"+trailId+"').innerHTML='&nbsp;'", 2000);
	  
	  var fav = document.getElementById('fav'+trailId);
	  var favimg = document.getElementById('favimg'+trailId);
	  
	  fav.removeChild(favimg);
	  var favimg2 = document.createElement('img');
	  favimg2.id = 'favimg'+trailId;
	  
	  if (text.responseText.indexOf("1") != -1)
	    favimg2.src = jsLocation + "/fav.png";
	  else
	    favimg2.src = jsLocation + "/favs.png";
	  favimg2.style.width = "25px";
	  favimg2.style.height = "25px";
	  favimg2.title = "Favorite it!";
	  fav.appendChild(favimg2);
	}
};

function favit(uid, trailid) {
//	ratingVal = rating;
	userId = uid;
	trailId = trailid;
	var ratingmsg = document.getElementById('ratingmsg'+trailid);

	if (uid > 0)
	{
	   ratingmsg.innerHTML = "<img src=\"" + jsLocation + "/loading.gif\" border=\"0\" align=\"absmiddle\" /><small> Saving</small>";
	   var queryString = "uid=" + uid + "&trailid=" + trailid + "&mode=fav";
//   alert(queryString);
	   ajaxFunction("index.php?option=com_saverating&format=raw", queryString, savedone);
//	ajaxFunction(url, queryString, returnVar, retFunction);
	}
	else
	   ratingmsg.innerHTML = "<small><font color=\"red\">Please login to fav!</font></small>";
	setTimeout("document.getElementById('ratingmsg"+trailId+"').innerHTML='&nbsp;'", 2000);
}


