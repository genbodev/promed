(function(a){a.fn.bindImageLoad=function(c){function b(d){if(!d.complete){return false}if(typeof d.naturalWidth!=="undefined"&&d.naturalWidth===0){return false}return true}return this.each(function(){var d=a(this);if(d.is("img")&&a.isFunction(c)){d.one("load",c);if(b(this)){d.trigger("load")}}})};a.fn.progressbar=function(c){var d=a.extend({width:"300px",height:"25px",color:"#0ba1b5",padding:"0px",border:"1px solid #ddd"},c);a(this).css({width:d.width,border:d.border,"border-radius":"5px",overflow:"hidden",display:"inline-block",padding:d.padding,margin:"0px 10px 5px 5px"});var b=a("<div></div>");b.css({height:d.height,"text-align":"right","vertical-align":"middle",color:"#fff",width:"0px","border-radius":"3px","background-color":d.color});a(this).append(b);this.progress=function(f){var e=a(this).width()*f/100;b.width(e).html(f+"% ")};return this}})(jQuery);