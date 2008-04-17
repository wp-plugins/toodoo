<?php
/*
Plugin Name: toodoo-snap
Plugin URI: http://toodoo.ru/support/wordpress2
Description: Всплывающие окна со snapshot-изображением сайта из каталога toodoo.
Author: Sol
Version: 2.1
Author URI: http://toodoo.ru/user/7701/profile
*/


add_action ('wp_head', 'toodoo_snap_init');
add_action ('wp_footer', 'toodoo_snap_done');
add_filter ('the_content','toodoo_snap_insert');

function toodoo_snap_init () {
        $pluginpath = get_bloginfo('url')."/wp-content/plugins/toodoo";

        echo <<<END
        <style type="text/css" media="screen">
        <!--
        	.snap_link {  }
            #snap { position: absolute; padding: 0 0 0 0; margin: 0px; width: 260px; height: 260px; }
            #snap .br, #snap .bl, #snap .tr, #snap .tl { width: 260px; height: 260px; }
            #snap .br { background: url({$pluginpath}/snap_br.png) left top no-repeat;//background:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader (src={$pluginpath}/snap_br.png); }
			#snap .bl { background: url({$pluginpath}/snap_bl.png) left top no-repeat;//background:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader (src={$pluginpath}/snap_bl.png); }
			#snap .tr { background: url({$pluginpath}/snap_tr.png) left top no-repeat;//background:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader (src={$pluginpath}/snap_tr.png); }
            #snap .tl { background: url({$pluginpath}/snap_tl.png) left top no-repeat;//background:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader (src={$pluginpath}/snap_tl.png); }
			#snap #inside { width: 160px; height: 180px; }		
			#snap .br #inside {	position: relative; left: 70px; top: 60px; }
			#snap .bl #inside {	position: relative; left: 30px; top: 60px; }
			#snap .tl #inside {	position: relative; left: 30px; top: 25px; }
			#snap .tr #inside {	position: relative; left: 70px; top: 25px; }
            #rate {	height: 25px; text-align: right; }
            #url { margin-top: 5px; width: 160px; text-align: center; overflow: hidden; font-size: 10px}
            
        //-->
        </style>
        <script src="{$pluginpath}/prototype.js" type="text/javascript"></script>
        <script language="JavaScript">
        
        	var snap_enable = 0;
        	var snap_timeout = 0;
        	
			function snap (link) {
        
                var reg = /^([0-9]+),([0-9]+)$/;
                var pos = reg.exec(Element.viewportOffset(link));
                var x = pos[1];
                var y = pos[2];
                var url = link.href;
                var snap_type = '';
                var snap_size = 260;
                
                if (snap_size > (document.viewport.getHeight() - y)) snap_type = 't'; else snap_type = 'b';
                if (snap_size > (document.viewport.getWidth () - x)) snap_type = snap_type + 'l'; else snap_type = snap_type + 'r';
                
                new Ajax.Request('{$pluginpath}/toodoo-wrap.php', {
                    method:'get',
                    parameters: {url: url},
                    requestHeaders: {Accept: 'application/json'},
                    onSuccess: function(transport) {
                        var json = transport.responseText.evalJSON(true);
                        var html = '';
                        var rate = '';
                        for (var i=0; i<5; i++) {
                            if (parseInt(json.rating) < 2*i) {
                                rate = rate + '<img src="{$pluginpath}/star0.png">';
                            }
                            else {
                                rate = rate + '<img src="{$pluginpath}//star1.png">';
                            }
                        }
                        if (json.favicon_url) favicon = '<img src="' + json.favicon_url + '" height="16"/>'; else favicon = '';
                        
                        html = html + '<div class="' + snap_type + '">';
                        html = html + '<div id="inside">';
                        html = html + '<div id="rate">' + rate + '</div>';
                        html = html + '<div id="shot"><a href="http://toodoo.ru/blog/'+ json.id +'/index"><img src="' + json.thumbnail_url +'" width="160" border="0"/></a></div>';
                        html = html + '<div id="url">' + favicon + '<br /> <a href="' + url + '">' + url + '</a></div>';
                        html = html + '</div>';
                        html = html + '</div>';
                        
                        $('snap').innerHTML = html;
                        switch (snap_type) {
                            case 'br': $('snap').clonePosition (link, {setLeft:true,setTop:true,setWidth:false,setHeight:false,offsetLeft:0,offsetTop:15});break;
                            case 'bl': $('snap').clonePosition (link, {setLeft:true,setTop:true,setWidth:false,setHeight:false,offsetLeft:-250,offsetTop:15});break;
                            case 'tr': $('snap').clonePosition (link, {setLeft:true,setTop:true,setWidth:false,setHeight:false,offsetLeft:0,offsetTop:-250});break;
                            case 'tl': $('snap').clonePosition (link, {setLeft:true,setTop:true,setWidth:false,setHeight:false,offsetLeft:-250,offsetTop:-250});break;
                        }
                        $('snap').show ();
                        snap_timeout = setTimeout("enable()",2000)
                        snap_enable = 1;
                    }
				});
            }

            function enable () {
        		snap_enable = 0;
        		snap_timeout = clearTimeout();
			}
            
            Event.observe(document, 'mouseover', function (event) {
       			if (snap_enable == 1) return;
				var do_hide = 1;
				var i = 0;
				
				while (Event.element(event).up(i)) {
        			if (event.element().up(i).identify() == 'snap') { 
        				do_hide = 0;
					}
					i++;
				}
				if (do_hide == 1) $('snap').hide (); 
			});
        </script>
END;
}

function toodoo_snap_done () {
    echo '<div id="snap"></div><script language="JavaScript">$("snap").hide();</script>';
}

function toodoo_snap_insert ($content) {
    
	$content = ereg_replace ('\<[aA] [hH][rR][eE][fF]=["\']http://([A-Za-z\.0-9\?\#\_\-]+)["\']([^\>]*)\>',"<a href=\"http://\\1\" class=\"snap_link\" onMouseOver=\"javascript:snap(this);\">",$content);
	$content = ereg_replace ('\<[aA] [hH][rR][eE][fF]=["\']http://([A-Za-z\.0-9\?\#\_\-]+)["\']([^\>]*)\><[iI][mM][gG]',"<a href=\"http://\\1\" class=\"snap_link\" onMouseOver=\"javascript:snap(this);\"><br /><img",$content);
	
    return $content;
}
