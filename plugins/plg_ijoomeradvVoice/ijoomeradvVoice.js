if(typeof(jQuery) != 'undefined'){
	var jQuery = jQuery.noConflict();
}else{
	var jQuery = joms.jQuery.noConflict();
}

jQuery(document).ready(function(){
	setTimeout(function(){commentaudio();captionaudio()},2000);
	setInterval(function(){commentaudio();captionaudio()},1000);
});

var commentaudio = function(){
	var regex = /{voice}(.*?){\/voice}/ig;
	var url = document.URL.split("index.php");
	var voicepath = url[0]+'components/com_ijoomeradv/assets/voice/';
	var flashpath = url[0]+'plugins/system/ijoomeradvVoice/';
	
	jQuery("div *").not("iframe").contents().each(function() {
		if(this.nodeType == 3){
			if(this.nodeValue){
				var text = this.nodeValue;
				match = regex.exec(text);
			    if(match){
			    	var filename = match[1].split(".");
			    	
			    	//check if file have extension or not
			    	if(filename[1]){
				    	var duration = filename[1].split("&");
				    	filename = filename[0]; 
				    	replacetxt = '<object width="102" height="20">';
				    	replacetxt += '<PARAM NAME=movie VALUE="'+flashpath+'audioplay.swf?file='+voicepath+filename+'.mp3&auto=no&sendstop=yes&repeatTimes=1&buttondir='+flashpath+'buttons/negative&bgcolor=0xffffff&mode=playpause">';
				    	replacetxt += '<PARAM NAME=quality VALUE=high>';
				    	replacetxt += '<PARAM NAME=wmode VALUE=transparent>';
				    	replacetxt += '	<embed src="'+flashpath+'audioplay.swf?file='+voicepath+filename+'.mp3&auto=no&sendstop=yes&repeatTimes=1&buttondir='+flashpath+'buttons/negative&bgcolor=0xffffff&mode=playpause" quality=high wmode=transparent width="102" height="20" align="" TYPE="application/x-shockwave-flash" >';
				    	replacetxt += '	</embed>';
				    	replacetxt += '</object>';
				    	
				    	//check if file duration is available or not
				    	if(duration[1]){
				    		replacetxt += '<p>'+duration[1]+' sec</p>';
				    	}
				    	
				    	replacetxt = jQuery.trim(replacetxt);
				    	
				    	this.nodeValue = this.nodeValue.replace(regex, '');
				    	jQuery(replacetxt).insertAfter(jQuery(this).parent());
			    	}
			    }
			}
		}
	});
}


var captionaudio = function(){
	var regex = /{voice}(.*?){\/voice}/ig;
	var url = document.URL.split("index.php");
	var voicepath = url[0]+'components/com_ijoomeradv/assets/voice/';
	var flashpath = url[0]+'plugins/system/ijoomeradvVoice/';
	
	//for photo caption
	jQuery('#cGallery .photoCaptionText').not('.shadow').each(function() {
		var text=jQuery('#cGallery .photoCaptionText').not('.shadow').val();
		match = regex.exec(text);
		if(match){
	    	var filename = match[1].split(".");
	    	
	    	//check if file have extension or not
	    	if(filename[1]){
		    	var duration = filename[1].split("&");
		    	filename = filename[0]; 
		    	replacetxt = '<object width="102" height="20">';
		    	replacetxt += '<PARAM NAME=movie VALUE="'+flashpath+'audioplay.swf?file='+voicepath+filename+'.mp3&auto=no&sendstop=yes&repeatTimes=1&buttondir='+flashpath+'buttons/negative&bgcolor=0xffffff&mode=playpause">';
		    	replacetxt += '<PARAM NAME=quality VALUE=high>';
		    	replacetxt += '<PARAM NAME=wmode VALUE=transparent>';
		    	replacetxt += '	<embed src="'+flashpath+'audioplay.swf?file='+voicepath+filename+'.mp3&auto=no&sendstop=yes&repeatTimes=1&buttondir='+flashpath+'buttons/negative&bgcolor=0xffffff&mode=playpause" quality=high wmode=transparent width="102" height="20" align="" TYPE="application/x-shockwave-flash" >';
		    	replacetxt += '	</embed>';
		    	replacetxt += '</object>';
		    	
		    	//check if file duration is available or not
		    	if(duration[1]){
		    		replacetxt += '<p>'+duration[1]+' sec</p>';
		    	}
		    	
		    	replacetxt = jQuery.trim(replacetxt);
		    	
		    	jQuery('#cGallery .photoCaptionText').not('.shadow').val(text.replace(regex, ''));
		    	jQuery(replacetxt).insertAfter(jQuery(this));
	    	}
	    }
	});
}