<div>
	<h3><?php echo $title ?></h3>
	<div class="datetime"><?php echo $datetime ?></div>
	<div class="body">

		<?php echo $body ?>

		<?php if (isset($cut) && $cut) { ?>
			<span class="cut-link"><a href="?c=portal&m=news_entry&id=<?php echo $id ?>">Читать далее</a></span>
		<?php } ?>

	</div>
</div>
