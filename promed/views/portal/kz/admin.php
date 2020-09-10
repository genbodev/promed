<!DOCTYPE html>
<html>
	<head>
		<title>Управление порталом ПроМед</title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
		<link rel="stylesheet" type="text/css" href="/css/portal.css" />
		<link rel="stylesheet" type="text/css" href="/css/portalAdmin.css" />
		<script type="text/javascript" src="/jscore/portal.js"></script>
	</head>
	<body>
		<div class="page" id="pageAdmin">
			<noscript><div class="noscript"><span>Для правильной работы системы необходима поддержка браузером JavaScript!</span></div></noscript>

			<div class="header">
				<div>
					<ul>
						<li id="portal">&larr;&nbsp;<a href="?c=portal">На портал</a></li>
						<li><a href="?c=portalAdmin">Панель управления</a></li>
						<li><a href="?c=portalAdmin&m=news">Новости</a></li>
						<li><a href="?c=portalAdmin&m=notices">Объявления</a></li>
						<li><a href="?c=portalAdmin&m=seminars">Семинары</a></li>
						<li><a href="?c=portalAdmin&m=articles">Статьи</a></li>
						<li id="logout"><a href="?c=portalAdmin&m=logout">Выход</a></li>
					</ul>
				</div>
			</div>

			<div class="content-wrapper">
				<div>
					<div class="content">
						<?php echo $content; ?>
					</div>
				</div>
			</div>

			<div class="footer">
				<div>
					<span>
						Разработка и поддержка<br />
						ТОО «Жаркын Болашак» KZ
					</span>
				</div>
			</div>

		</div>
	</body>
</html>
