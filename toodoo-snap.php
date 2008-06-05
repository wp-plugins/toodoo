<?php
/*
Plugin Name: toodoo-snap
Plugin URI: http://toodoo.ru/support/wordpress2
Description: Всплывающие окна со snapshot-изображением сайта из каталога toodoo.
Author: Sol
Version: 2.1.1
Author URI: http://toodoo.ru/user/7701/profile
*/


add_action ('wp_head', 'toodoo_snap_init');
add_action ('wp_footer', 'toodoo_snap_done');
add_filter ('the_content','toodoo_snap_insert');
add_filter ('comment_text','toodoo_snap_insert');

function toodoo_snap_init () {
        $pluginpath = get_bloginfo('url')."/wp-content/plugins/toodoo";
		if (substr($_SERVER['HTTP_USER_AGENT'],0,9) == 'Mozilla/5') {
			$n1 = 70;
			$n2 = 30;
		}
		else {
			$n1 = 35;
			$n2 = -35;
		}
		
        echo <<<END
        <style type="text/css" media="screen">
			.snap_link      { text-decoration:none;  }
			
			#snap           {position: absolute; left:0; top: 0; width: 0px; height: 0px; }
			#toodoo_snap	{position: absolute; left:0; top: 0; width: 260px; height: 260px; }
			#toodoo_snap    #toodoo_data { position: relative; width: 160px; height: 190px; display:block; }
			
			#toodoo_snap.br { background: url({$pluginpath}/snap_br.png) left top no-repeat; //background:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader (src={$pluginpath}/snap_br.png); }
			#toodoo_snap.bl { background: url({$pluginpath}/snap_bl.png) left top no-repeat; //background:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader (src={$pluginpath}/snap_bl.png); }
			#toodoo_snap.tr { background: url({$pluginpath}/snap_tr.png) left top no-repeat; //background:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader (src={$pluginpath}/snap_tr.png); }
			#toodoo_snap.tl { background: url({$pluginpath}/snap_tl.png) left top no-repeat; //background:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader (src={$pluginpath}/snap_tl.png); }
			
			#toodoo_snap.br #toodoo_data { margin-left:70px; margin-top:55px; //margin-left:35px; margin-top:55px; }
			#toodoo_snap.bl #toodoo_data { margin-left:30px; margin-top:55px; //margin-left:-40px; margin-top:55px; }
			#toodoo_snap.tr #toodoo_data { margin-left:70px; margin-top:20px; //margin-left:35px; margin-top:55px; }
			#toodoo_snap.tl #toodoo_data { margin-left:30px; margin-top:20px; //margin-left:-40px; margin-top:55px; }
			
			#favicon.toodoo { margin-top:5px; float:left; display:block; }
			#rate.toodoo    { height:25px; text-align:right; }
			#shot.toodoo    { margin-top:5px; }
			#url.toodoo     { margin-top:5px; margin-left:2px; width:140px; overflow:hidden; }
			#url.toodoo a   { text-decoration:none; color:#eeeeff; font-family:Tahoma; font-size: 13px;}
        </style>
        <script src="{$pluginpath}/prototype.js" type="text/javascript"></script>
        <script language="JavaScript">
        
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
                                rate = rate + '<img class="snap" src="{$pluginpath}/star0.png">';
                            }
                            else {
                                rate = rate + '<img class="snap" src="{$pluginpath}//star1.png">';
                            }
                        }
                        if (json.favicon_url) favicon = '<img class="snap" src="' + json.favicon_url + '" height="16"/>'; else favicon = '';

                    	html = html + '<div id="toodoo_snap" class="' + snap_type + '">';
						html = html + '<div id="toodoo_data">';
						html = html + '<div id="rate" class="toodoo">' + rate + '</div>';
						html = html + '<div id="shot" class="toodoo"><a href="http://toodoo.ru/blog/'+ json.id +'/index"><img class="toodoo" src="' + json.thumbnail_url +'" width="160" height="120" border="0"/></a></div>';
						html = html + '<div id="favicon" class="toodoo">' + favicon + '</div>';
						html = html + '<div id="url" class="toodoo"><a class="toodoo" href="' + url + '">' + url + '</a></div>';
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
                        setTimeout(snap_listener, 1000);
                    }
				});
            }
            
            function snap_listener () {
            	Event.observe(document, 'mouseover', snap_hide);
            	clearTimeout();
			}
            
            function snap_hide (event) {
        		if ((!event.element().hasClassName('toodoo')) && (!event.element().hasClassName('snap_link'))) {
        			$('snap').hide ();
        			Event.stopObserving (document, 'mouseover', snap_hide);
				}
			}
        </script>
END;
}

function toodoo_snap_done () {
    echo '<div id="snap"></div>';
}

function toodoo_snap_insert ($content) {
	$content = ereg_replace ('\<[aA] [hH][rR][eE][fF]=["\']http://([:/A-Za-z\.0-9\?\#\_\-]+)["\']([^\>]*)\>',"<a href=\"http://\\1\" class=\"snap_link\" onMouseOver=\"javascript:snap(this);\">",$content);
	$content = ereg_replace ('\<[aA] [hH][rR][eE][fF]=["\']http://([:/A-Za-z\.0-9\?\#\_\-]+)["\']([^\>]*)\><[iI][mM][gG]',"<a href=\"http://\\1\" class=\"snap_link\" onMouseOver=\"javascript:snap(this);\"><br /><img",$content);
    return $content;
}