<?php
/*
Template Name: toodooNET
*/
?>

<?php get_header(); ?>
<div id="content" class="widecolumn">

<?php
    require "toodooAPI.php";
    $toodoo = new toodooAPI (get_option('toodoo_blogid'),get_option('toodoo_key'));

    if (ereg("user/([0-9]+)", $_SERVER['REQUEST_URI'], $tokens)) {
		$id = $tokens[1];
	}

    if (ereg("\?page_id=([0-9]+)&user_id=([0-9]+)", $_SERVER['REQUEST_URI'], $tokens)) {
        $pid  = $tokens[1];
        $id   = $tokens[2];
	}

    if (ereg("\?page_id=([0-9]+)", $_SERVER['REQUEST_URI'], $tokens)) $pid  = $tokens[1]; else $permalinks = true;
    if (ereg("page=([0-9]+)", $_SERVER['REQUEST_URI'], $tokens)) $pagenum = $tokens[1]-1; else $pagenum = 0;
?>

<?php if ($id) : ?>
	<?php $user = $toodoo->user_info($id); ?>
	<h2><?=$user->NickName;?></h2>
	<div style="position:left; padding:5px;"><img border="0" src="http://static.toodoo.ru/photos/<?=($user->UserPhoto);?>user_<?=$user->Id;?>_100x100.jpg"/></div>
	<?=$user->LastName;?> <?=$user->FirstName;?> <?=$user->MiddleName;?><br />
	<a href="http://toodoo.ru/user/<?=$user->Id;?>/profile">toodoo.ru</a>
	<div style="position: left;">
		<h2>Интересы</h2>
		<?php if (is_array($user->tags->tag)) : foreach ($user->tags->tag as $tag) : ?>
		<a href="http://toodoo.ru/catalogue/index/<?=$tag->name; ?>" rel="nofolow"><?=$tag->name; ?></a>
		<?php endforeach; elseif (isset($user->tags->tag)) : ?>
		<a href="http://toodoo.ru/catalogue/index/<?=$user->tags->tag->name; ?>" rel="nofolow"><?=$user->tags->tag->name; ?></a>
        <?php else: ?>
        <p>Cписок интересов пуст</p>
		<?php endif; ?>

		<h2>Читает сайты</h2>
		<?php if (is_array($toodoo->api->blogs->blog)) : foreach ($toodoo->api->blogs->blog as $blog) : ?>
		<a href="http://toodoo.ru/blog/<?=$blog->id; ?>/index" rel="nofolow"><?=$blog->url; ?></a><br />
		<?php endforeach; elseif (isset($toodoo->api->blogs->blog)) :?>
		<a href="http://toodoo.ru/blog/<?=$toodoo->api->blogs->blog->id; ?>/index" rel="nofolow"><?=$toodoo->api->blogs->blog->url; ?></a><br />
        <?php else: ?>
        <p>Cписок сайтов пуст</p>
        <?php endif; ?>
	</div>
<?php else : ?>
<h2>Пользователи</h2>
    <?php $users = $toodoo->network (50,$pagenum); foreach ($users->user as $user) :	?>
    <div style="width:49%; height:50px; float:left;">
		<div style="width:50px; height:50px; float:left; padding:5px;"><img border="0" src="<?=$user->photo->url?$user->photo->url:'http://static.toodoo.ru/img/hidden_photo_male.png';?>"/></div>
		<div>
                <?php if ($permalinks) : ?>
                <a href="/user/<?=$user->id; ?>"><?=$user->user_name; ?></a><br />
                <?php else : ?>
                <a href="?page_id=<?=$pid; ?>&user_id=<?=$user->id; ?>"><?=$user->user_name; ?></a><br />
                <?php endif; ?>
				<a href="http://toodoo.ru/user/<?=$user->id; ?>/profile" rel="nofolow">Профиль на toodoo.ru</a><br />
		</div>
	</div>
    <?php endforeach; ?>
	<?php for ($i=1; $i<($toodoo->api->total/50+1); $i++) { ?>
                <?php if ($permalinks) : ?>
                <a href="?page=<?=$i;?>"><?=$i;?></a><br />
                <?php else : ?>
                <a href="?page_id=<?=$pid; ?>&page=<?=$i;?>"><?=$i;?></a><br />
                <?php endif; ?>
    <?php } ?>
<?php endif; ?>
<div style="clear:both;"></div>

</div>
<?php get_footer(); ?>
