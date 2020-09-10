<div class="seminar">
	<div class="schedule">
		<?php echo $schedule;?>
	</div>
	<div class="title">
		<a href="/?c=portal&m=seminar&id=<?php echo $id?>"><?php echo $title;?></a>
	</div>
	<div class="body <?php echo $cut ? 'cutted' : '' ?>">
		<?php echo $body;?>
	</div>
</div>