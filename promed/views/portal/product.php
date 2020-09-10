<div>
	<a href="<?php echo $url ?>"><img src="<?php echo $icon ?>" /></a>
	<div class="title"><a href="<?php echo $url ?>"><?php echo $title ?></a></div>
	<div class="description">
		<?php echo $description ?>
		<?php if (isset($users_count) && $users_count >= 0) { ?>
			<small><?php echo (isset($users_descr))?$users_descr:'Пользователей онлайн' ?>: <?php echo $users_count ?></small>
		<?php } ?>
	</div>
</div>
