<?php
/*
Plugin Name: toodoo-comment
Plugin URI: http://toodoo.ru/support/wordpress
Description: Авторизация toodoo для комментирования
Author: Sol
Version: 2.1.1
Author URI: http://toodoo.ru/user/7701/profile
*/

add_action ('activate_toodoo/toodoo-comment.php', 'toodoo_comment_activate');
add_action ('wp_head', 'toodoo_comment_head');
add_action ('comment_post', 'toodoo_comment_post');

function toodoo_comment_activate() {
    global $wpdb;
	$wpdb->query ("ALTER TABLE ".$wpdb->comments." ADD toodoo_id INTEGER");
	$wpdb->query ("ALTER TABLE ".$wpdb->comments." ADD toodoo_avatar VARCHAR(128)");
}

function toodoo_comment_head () {
    echo '<script id="toodoo_widget_login" type="text/javascript" src="http://toodoo.ru/widget/login"></script>';
}

function toodoo_comment_post ($comment_ID) {
	global $wpdb;

    $uid = $_POST['toodoo_id'];
    $uavatar = $_POST['toodoo_avatar'];

    if ($uid!='') {
		$wpdb->query("UPDATE ".$wpdb->comments." SET toodoo_id={$uid} WHERE comment_ID={$comment_ID}");
		$wpdb->query("UPDATE ".$wpdb->comments." SET toodoo_avatar='{$uavatar}' WHERE comment_ID={$comment_ID}");
    }
}

function toodoo_profile_link ($name = 'toodoo.ru', $before = '', $after = '') {
	global $wpdb;

	$comment_ID = get_comment_ID();
	$comment = $wpdb->get_row("SELECT toodoo_id FROM `".$wpdb->comments."` WHERE comment_ID={$comment_ID}", ARRAY_A);
	if ($comment['toodoo_id'] > 0) echo "{$before}<a href='http://toodoo.ru/user/".$comment['toodoo_id']."/profile' rel='nofolow'>{$name}</a>{$after}";
}

function toodoo_comment_avatar ($echo = true) {
	global $wpdb;

	$comment_ID = get_comment_ID();
	$comment = $wpdb->get_row("SELECT toodoo_avatar FROM `".$wpdb->comments."` WHERE comment_ID=$comment_ID", ARRAY_A);
	if ($comment['toodoo_avatar'] != '') {
        if ($echo)
            echo "<img alt='userpic' align='left' hspace='2' vspace='2' src='".$comment['toodoo_avatar']."'>";
        else
            return "<img alt='userpic' align='left' hspace='2' vspace='2' src='".$comment['toodoo_avatar']."'>";
    }
}
?>
