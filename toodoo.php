<?php
/*
Plugin Name: toodoo
Plugin URI: http://toodoo.ru/support/wordpress2
Description: Интеграция блога с сервисами toodoo - авторизация, аватары, профили читаталей, snapshot-ы сайтов.
Author: Sol
Version: 2.0
Author URI: http://toodoo.ru/user/7701/profile
*/

class toodoo {
	var $page_title;
	var $short_description;
	var $menu_title;
	var $access_level;
	var $add_page_to;
	var $API;
	var $toodoo_user;
	var $toodoo_avatar;

	function toodoo () {
		$this->menu_title        = 'toodoo';
		$this->page_title        = 'Настройки toodoo';
		$this->add_page_to       = 2;
		$this->access_level      = 5;

		$this->get_options ();
		$plugin = 'toodoo/toodoo.php';

		add_action ('activate_'   . $plugin, array($this, 'activate'));
		add_action ('deactivate_' . $plugin, array($this, 'deactivate'));
		add_action ('admin_menu', array($this, 'add_admin_menu'));
		add_action ('comment_post', array($this,'comment_post'));
		add_action ('wp_head', array($this,'head'));
		add_action ('wp_head', array($this,'toodoo_snap_init'));
		add_action ('wp_footer', array($this,'toodoo_snap_done'));
		add_filter ('the_content',array($this,'toodoo_snap_insert'));
	}

	function toodoo_snap_init () {
	    if ($this->snap == 'on'){
	    
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
	}
	
	function toodoo_snap_done () {
	    if ($this->snap == 'on'){
            echo "<script type='text/javascript' src='".get_bloginfo('url')."/wp-content/plugins/toodoo/js/snapshot.js'></script>";
        }
	}

	function toodoo_snap_insert ($content) {
	    $content = ereg_replace ('\<a href="http://([A-Za-z\.0-9\%\#]+)"([^\>]*)\>',"<a href=\"http://\\1\" class=\"snapshot\">",$content);
	    return $content;
	}

	
	function head () {
        echo '<script id="toodoo_widget_login" type="text/javascript" src="http://toodoo.ru/widget/login"></script>';
	}

	function comment_post ($comment_ID) {
		global $wpdb;

        $uid = $_POST['toodoo_id'];
        $uavatar = $_POST['toodoo_avatar'];

        if ($uid!='') {
			$wpdb->query("UPDATE ".$wpdb->comments." SET toodoo_id={$uid} WHERE comment_ID={$comment_ID}");
			$wpdb->query("UPDATE ".$wpdb->comments." SET toodoo_avatar='{$uavatar}' WHERE comment_ID={$comment_ID}");
        }
    }

	function get_options() {
		foreach (array('blogid','key','snap') as $option) $this->$option = get_option('toodoo_'.$option);
	}

	function add_admin_menu() {
		if     ( $this->add_page_to == 1 ) add_menu_page($this->page_title, $this->menu_title, $this->access_level, __FILE__, array($this, 'admin_page'));
		elseif ( $this->add_page_to == 2 ) add_options_page($this->page_title, $this->menu_title, $this->access_level, __FILE__, array($this, 'admin_page'));
		elseif ( $this->add_page_to == 3 ) add_management_page($this->page_title, $this->menu_title, $this->access_level, __FILE__, array($this, 'admin_page'));
		elseif ( $this->add_page_to == 4 ) add_theme_page($this->page_title, $this->menu_title, $this->access_level, __FILE__, array($this, 'admin_page'));
	}

	function activate() {
        global $wpdb;
		$wpdb->query ("ALTER TABLE ".$wpdb->comments." ADD toodoo_id INTEGER");
		$wpdb->query ("ALTER TABLE ".$wpdb->comments." ADD toodoo_avatar VARCHAR(128)");

        add_option ('toodoo_blogid', '', 'toodoo BlogID');
		add_option ('toodoo_key', '', 'toodoo API key');
		add_option ('toodoo_snap', '', 'toodoo snapshots');
	}

	function deactivate() {
	//	Хорошо бы вернуть структуру базы взад и удалить настройки
	}

	function admin_page() {
		echo "<div class='wrap'>";
		echo "<h2>toodoo.wordpress</h2>";

		if (isset($_POST['UPDATE'])) {
            echo "<div class='updated'>Настройки сохранены</div><p>";

			if (isset($_POST['blogid'])) update_option ('toodoo_blogid',$_POST['blogid']);
			if (isset($_POST['key'])) update_option ('toodoo_key',$_POST['key']);
			if (isset($_POST['snap'])) update_option ('toodoo_snap',$_POST['snap']);
			else {
				update_option ('toodoo_snap','off');
			}
		}
		
		$this->get_options ();

		echo "<form action='' method='POST'>";
		echo "<h3>Настройки</h3>";
		
		echo "<b>Идентификатор сайта в toodoo</b><br />";
		echo "<input type='text' name='blogid' size='10' value='{$this->blogid}'><br /><br />";

		echo "<b>Партнерский ключ</b> <font color='red'>(Храните в секрете!)</font><br />";
		echo "<input type='text' name='key' size='40' value='{$this->key}'><br /><br />";

		echo "<b>Всплывающие окна для ссылок</b><br />";
		echo "<input type='checkbox' name='snap' ".(($this->snap == 'on')?'checked':'')."> Включено<br /><br />";

		echo "<input type='submit' name='UPDATE' value='Готово'>";
		echo "</form>";

		echo '</div>';
	}
}

$toodoo = new toodoo();

/* Интерфейсные функции для внедрения в тему */

function toodoo_switcher ($message = 'Использовать данные <a href="http://toodoo.ru/account/signup" ref="nofolow">toodoo</a>') {
    echo "<span id='switcher'><input type='checkbox' name='toodoo_auth' id='switch' checked onClick='javascript:if (document.getElementById(\"switch\").checked) { wordpress_update(); document.getElementById(\"toodoo_auth\").style.display = \"none\"; } else { document.getElementById(\"toodoo_auth\").style.display = \"block\"; }' > $message <br /></span>";
    echo "<script language=\"Javascript\">";
    echo "if (toodoo_id) {document.getElementById(\"toodoo_auth\").style.display = \"none\"; } else { document.getElementById(\"switcher\").style.display = \"none\"; }";
    echo "</script>";
}

function toodoo_profile_link ($name = 'toodoo.ru', $before = '(', $after = ')') {
	global $wpdb;

	$comment_ID = get_comment_ID();
	$comment = $wpdb->get_row("SELECT toodoo_id FROM `".$wpdb->comments."` WHERE comment_ID={$comment_ID}", ARRAY_A);
	if ($comment['toodoo_id'] > 0) echo "{$before}<a href='http://toodopo.ru/user/".$comment['toodoo_id']."/profile' rel='nofolow'>{$name}</a>{$after}";
}

function toodoo_comment_avatar () {
	global $wpdb;

	$comment_ID = get_comment_ID();
	$comment = $wpdb->get_row("SELECT toodoo_avatar FROM `".$wpdb->comments."` WHERE comment_ID=$comment_ID", ARRAY_A);
	if ($comment['toodoo_avatar'] != '') echo "<img alt='userpic' align='left' hspace='2' vspace='2' src='".$comment['toodoo_avatar']."'>";
}
?>
