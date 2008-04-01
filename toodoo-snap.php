<?php
/*
Plugin Name: toodoo-snap
Plugin URI: http://toodoo.ru/support/wordpress2
Description: Всплывающие окна с изображением (snapshot) сайта из toodoo-каталога.
Author: Sol
Version: 2.0.2
Author URI: http://toodoo.ru/user/7701/profile
*/


add_action ('wp_head', 'toodoo_snap_init');
add_action ('wp_footer', 'toodoo_snap_done');
add_filter ('the_content','toodoo_snap_insert');

function toodoo_snap_init () {
        $pluginpath = get_bloginfo('url')."/wp-content/plugins/toodoo";

        echo <<<END
            <script type='text/javascript' src='$pluginpath/js/prototype.js'></script>
            <script type='text/javascript' src='$pluginpath/js/scriptaculous.js?load=effects'></script>
            <script type='text/javascript' src='$pluginpath/js/prototip.js'></script>
            <script type='text/javascript' >var toodoopath='$pluginpath/';</script>
            <style>
                .prototip { position: absolute; }
                .prototip .effectWrapper { position: relative; }
                .prototip .tooltip { position: relative; }
                .prototip .toolbar {
                	position: relative;
                	display: block;
                	}
                .prototip .toolbar .title {
                	display: block;
                	position: relative;
                	}
                .prototip .content { clear: both; }
                .prototip .toolbar a.close {
                	position: relative;
                	text-decoration: none;
                	float: right;
                	width: 15px;
                	height: 15px;
                	background: transparent;
                	display: block;
                	line-height: 0;
                	font-size: 0px;
                	border: 0;
                	}
                .prototip .toolbar a.close:hover { background: transparent; }

                .iframeShim {
                	position: absolute;
                	border: 0;
                	margin: 0;
                    padding: 0;
                    background: none;
                }
                #snap {
                    padding: 0px;
                    margin: 0px;
                    width: 260px;
                    height: 265px;
                    background: url({$pluginpath}/images/snap.png) left top no-repeat;//background:none; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader (src={$pluginpath}/images/snap.png);
                }

                #snap #rate {
                    position: relative;
                    left: 70px;
                    top: 55px;
                }

                #snap #block {
                    position: relative;
                    left: 22px;
                    top: 75px;
                }

                #snap #block #thumbnail img {
                    width: 160px;
                }

                #snap #block #url {
                    margin-top: 5px;
                    margin-left: auto;
                    margin-right: auto;
                    width: 160px;
                    overflow: hidden;
                }

                #snap #url img {
                    margin-bottom: -3px;
                }

                #snap #url a {
                    font-family: Tahoma;
                    font-size: 11px;
                    text-decoration: underline;
                    text-align: center;
                    color: white;
                }
            </style>
END;
}

function toodoo_snap_done () {
    echo "<script type='text/javascript' src='".get_bloginfo('url')."/wp-content/plugins/toodoo/js/snapshot.js'></script>";
}

function toodoo_snap_insert ($content) {
    
	$content = ereg_replace ('\<[aA] [hH][rR][eE][fF]=["\']http://([A-Za-z\.0-9\?\#\_\-]+)["\']([^\>]*)\>',"<a href=\"http://\\1\" class=\"snapshot\">",$content);
	$content = ereg_replace ('\<[aA] [hH][rR][eE][fF]=["\']http://([A-Za-z\.0-9\?\#\_\-]+)["\']([^\>]*)\><[iI][mM][gG]',"<a href=\"http://\\1\" class=\"snapshot\"><br /><img",$content);
    return $content;
}
