<article>
	<div class="datetime"><?php echo $datetime ?></div>
	<div class="body">

		<?php echo $body ?>

		<?php if (isset($cut) && $cut) { ?>
			<div class="cut-link">
				<a href="?c=portal&m=news_entry&id=<?php echo $id ?>">Читать полностью</a>
			</div>
		<?php } ?>

	</div>
</article>
