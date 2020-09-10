<div class="news">

    <h1>Семинары</h1>

	<?php if (!empty($seminars)) { ?>
		<div id="newsContainer">
			<?php echo $seminars ?>
		</div>
	<?php } else { ?>
		<div>Нет объявлений о семинарах</div>
	<?php } ?>

    <?php if (!$end) { ?>
        <span class="more-link" id="getNews"><a href="javascript:void(0)" onclick="getSeminarsMore();">Показать еще</a></span>

        <input type="hidden" id="startNews" name="startNews" value="<?php echo $start ?>" />
        <input type="hidden" id="numNews" name="numNews" value="<?php echo $num ?>" />
    <?php } ?>

</div>