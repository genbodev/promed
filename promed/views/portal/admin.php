<!DOCTYPE html>
<html>
<head>
	<title>Управление порталом</title>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta content="true" name="HandheldFriendly">
	<meta content="width" name="MobileOptimized">
	<meta content="yes" name="apple-mobile-web-app-capable">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro&amp;display=swap">
	<link rel="stylesheet" type="text/css" href="/css/portal.admin.2020.css">
	<script type="text/javascript" src="/jscore/portal.js"></script>
</head>
<body>

<header>
	<div class="container">
		<a href="/"><img src="/img/logo.svg" alt="Логотип РТ МИС"></a>
	</div>
</header>
<nav>
	<div class="container">
		<div class="col-md-12 row">
			<div class="nav-actions">
				<ul class="nav nav-tabs border-0 flex-column flex-lg-row">
					<!-- <li class="nav-item"><a class="nav-link" href="?c=portalAdmin">Панель управления</a></li> -->
					<li class="nav-item"><a class="nav-link" href="?c=portalAdmin&m=news"><i class="fe fe-github"></i>Новости</a></li>
					<li class="nav-item"><a class="nav-link" href="?c=portalAdmin&m=notices"><i class="fe fe-alert-circle"></i>Объявления</a></li>
					<li class="nav-item"><a class="nav-link" href="?c=portalAdmin&m=seminars"><i class="fe fe-monitor"></i>Семинары</a></li>
					<!-- <li class="nav-item"><a class="nav-link" href="?c=portalAdmin&m=articles"><i class="fe fe-type"></i>Статьи</a></li> -->
					<li class="nav-item logout"><a class="nav-link" href="?c=portalAdmin&m=logout"><i class="fe fe-log-out"></i>Выход</a></li>
				</ul>
			</div>
		</div>
	</div>
</nav>

<div class="my-3 my-md-5">
	<div class="container">
		<?php echo $content; ?>
	</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="/jscore/portal.admin.2020.js"></script>
</body>
</html>
