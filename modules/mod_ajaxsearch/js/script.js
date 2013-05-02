var JQ = jQuery.noConflict();
JQ(document).ready(function()
{
	JQ("input").blur(function(){
	 	JQ('#suggestions').fadeOut();
	 });
	
	JQ('#loading-not').hide();
	
	JQ("#loading").bind("ajaxSend", function(){
		JQ(this).show();
		JQ('#loading-not').hide();
	 }).bind("ajaxComplete", function(){
	    JQ(this).hide();
		JQ('#loading-not').show();	
	 });
}) 

function lookup(inputString,id, order) {
	if(inputString.length < 3) {
		JQ('#suggestions').fadeOut(); 
		JQ('#loading-not').hide();
	} else {
		JQ.post("index.php?task=ajax&option=com_search&tmpl=component&type=raw&id="+id+"&ordering="+order , {queryString: ""+inputString+""}, function(data) { 																									
			JQ('#suggestions').fadeIn(); // Show the suggestions box
			JQ('#suggestions').html(data); // Fill the suggestions box
		});
	}
}

function hide() {
	JQ('#loading-not').hide();
}

