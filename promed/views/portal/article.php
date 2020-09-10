<div class="article">
	
	<div class="breadcrumbs-block">
		<a href="?c=portal">РИАМС</a> <span>Список обновлений от <?php echo $datetime_news; ?></span>
	</div>
	
	<h1 class="header-curnews">Список обновлений от <?php echo $datetime_news; ?></h1>

	<?php echo $body ?>

    <?php if (!empty($schedule)) { ?>
        <p>Семинар проводится  <?php echo $schedule ?></p>
		<a href="/?c=portal&m=seminars">Все семинары</a>
    <?php } ?>

</div>  
