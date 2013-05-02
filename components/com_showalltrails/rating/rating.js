
var jQuery = jQuery.noConflict();

//get the location of this script
var scripts = document.getElementsByTagName("script") ;
for(var i = 0 ; i < scripts.length ; i++)
{
	var scriptSource = scripts[i].src ;
	var temp = scriptSource.indexOf("rating.js") ;
	if(temp >= 0)
	   jsLocation = scriptSource.slice(0, temp);
}
jQuery(document).ready(function() {
	jQuery('.status').prepend("<div class='score_this'>(<a href='#'>Rate this trail</a>)</div>");
	jQuery('.score_this').click(function(){
		jQuery(this).slideUp();
		return false;
	});
	
	jQuery('.score a').click(function() {
		jQuery(this).parent().parent().parent().addClass('scored');
		jQuery.get(jsLocation + "rating.php" + jQuery(this).attr("href") +"&update=true", {}, function(data){
			jQuery('.scored').fadeOut("normal",function() {
				jQuery(this).html(data);
				jQuery(this).fadeIn();
				jQuery(this).removeClass('scored');
			});
		});
		return false; 
	});
});
