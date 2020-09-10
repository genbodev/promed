<div class="news">

	<h2>Развитие продуктов</h2>

	<div class="timeline"></div>

	<div id="newsContainer">
		<?php echo $news_entries ?>
	</div>

	<?php if ($more !== false) { ?>
		<div class="more-link" id="getNews"><a href="javascript:void(0)" onclick="getNewsMore();">ЕЩЁ <?php echo $more ?></a></div>

		<input type="hidden" id="startNews" name="startNews" value="<?php echo $start ?>" />
		<input type="hidden" id="numNews" name="numNews" value="<?php echo $num ?>" />
	<?php } ?>

</div>
