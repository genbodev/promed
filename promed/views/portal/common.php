<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $titles['main_title']?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
        <link rel="stylesheet" type="text/css" href="/css/font-montserrat.css" />
        <link rel="stylesheet" type="text/css" href="/css/portal.css<?php echo "?", time(); ?>" />
		<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
        <script type="text/javascript">
            <?php echo "var ETOKEN_PRO_ENABLED = ".( $this->config->item('ETOKEN_PRO_ENABLED') ? $this->config->item('ETOKEN_PRO_ENABLED') : "0").";\n"; ?>
        </script>
		<script type="text/javascript" src="/jscore/locale/ru/portal.js"></script>
		<script type="text/javascript" src="/jscore/portal.js<?php echo "?", time(); ?>"></script>
		<script type="text/javascript" src="/jscore/libs/flot/jquery.min.js"></script>
		<script type="text/javascript" src="/jscore/libs/readmore.min.js"></script>
		<script type="text/javascript" src="/jscore/libs/jquery.jsonp.js"></script>
		<script type="text/javascript" src="/jscore/libs/signservice/authApplet.js"></script>
		<script type="text/javascript" src="/jscore/libs/signservice/ncaLayer.js"></script>
		<script type="text/javascript" src="/jscore/libs/signservice/authApi.js"></script>
		<script type="text/javascript" src="/jscore/libs/cadesplugin_api.js"></script>
		<script type="text/javascript" src="/jscore/libs/lss-client.js"></script>
		<script type="text/javascript" src="/jscore/cryptopro.js"></script>
		<script> 
            var $buoop = {required:{e:12,f:64,o:58,s:12,c:60},insecure:true,api:2020.04,l:"ru"}; 
            function $buo_f(){ 
             var e = document.createElement("script"); 
             e.src = "/jscore/libs/update.min.js"; 
             document.body.appendChild(e);
            };
            try {document.addEventListener("DOMContentLoaded", $buo_f,false)}
            catch(e){window.attachEvent("onload", $buo_f)}

			$(document).ready(function() {
				$('.notices-content > div > div').readmore({
					speed: 500,
					moreLink: '<a class="toggle-btn" href="#">Подробнее</a>',
					lessLink: '<a class="toggle-btn" href="#">Свернуть</a>',
					collapsedHeight: 46
				});
			});
			
        </script>
    </head>
    <body class="<?php echo !empty($newClsPage)?$newClsPage:''; ?>">
        <div class="page" id="pageCommon">
			<noscript><div class="noscript"><span>Для правильной работы системы необходима поддержка браузером JavaScript!</span></div></noscript>

            <div class="header">
                <div>
                    <div class="left">
						<?php
						if (empty($newClsPage)) {
						?>
							<a href="?c=portal"><?php echo $titles['RIAMS']?></a> &rarr; <span><?php echo $title ?></span>
						<?php
						} else {
						?>
							<a href="?c=portal" class="logo-index"></a>
						<?php
						}
						?>

                    </div>
                    <div class="right">
						<?php
						if (empty($newClsPage)) {
						?>

                        <!--<span>Ваш город: <span class="link underline-dashed">Пермь</span></span>-->
                        <?php if ( !empty($links['lastupdates']) ) { ?><span><a href="<?php echo $links['lastupdates']; ?>">Последние изменения</a></span><?php } ?>
                        <span><a href="<?php echo $links['promedhelp'] ?>">Справочная система</a></span>
                         <?php if ( !empty($links['forum']) ) { ?><span><a href="<?php echo $links['forum'] ?>">Форум технической поддержки</a></span><?php } ?>

						<?php
						} else {
						?>
							<div class="hamburger-menu">
								<input id="menu__toggle" type="checkbox" />
								<label class="menu__btn" for="menu__toggle">
									<span></span>
								</label>

								<ul class="menu__box">
									<li><a class="menu__item" href="https://er.promedweb.ru">Электронная регистратура</a></li>
									<li><a class="menu__item" href="#">ЕРМП</a></li>
									<li><a class="menu__item" href="#">ИАС: Информационный модуль</a></li>
									<li><a class="menu__item" href="#">ИАС: Модуль мониторинга и анализа</a></li>
									<li><a class="menu__item" href="#">ИАС: Модуль отчетности Запись к врачу</a></li>
									<li><a class="menu__item" href="#">Сервис: Поиск льготников</a></li>
									<li><a class="menu__item" href="#">Сервис: Мониторинг здравоохранения</a></li>
									<li><a class="menu__item" href="#">Сервис: Статистика трафика</a></li>
									<li><a class="menu__item" href="#">Сервис: Управление репликацией</a></li>
									<li class="split_bottom"><a class="menu__item" href="#">Справочная служба • Сайт РТ МИС</a></li>
								</ul>
							</div>
						<?php
						}
						?>
                    </div>
                </div>
            </div>
			
			<?php if (!empty($newClsPage) && !empty($notices)) { ?>
			<div class="notification-block">
				<?php echo $notices ?>
			</div>
			<?php } ?>

            <div class="content-wrapper">
                <div>
	                <div class="title">
	                    <?php echo $mainTitle; ?>
	                </div>
                    <div>
                        <div class="nav">
                            <ul>
								<?php echo $nav ?>
							</ul>
						</div>

						<div class="content" <?php if ($_REQUEST['m'] == 'udp') {?>style="background: url('/img/portal/bg/<?php echo mt_rand(1, 10); ?>.jpg') no-repeat center;"<?php } ?>>

							<?php echo $content ?>

                        </div>  

                    </div>
                </div>
            </div>

			<?php if(!empty($news)) { ?> 
            <div class="news-wrapper">
                <div>
				
					<?php echo $news; ?>
					
					<?php if (!empty($seminar)) { ?>
					<div class="right-col">
						<h2>Учебный центр</h2>
						<?php echo $seminar; ?>
						
						<a class="border-button" href="/?c=portal&m=seminars" class="right">Архив семинаров</a>
					</div>
					<?php } ?>
                </div>
            </div>
			<?php } ?> 

            <footer>
                <div>
					<span style="float:left; margin-top: 23px;">
                        По экстренным техническим вопросам в нерабочее время обращайтесь<br />
                        <?php if (getRegionNick() == 'krym') { ?>
                            по телефону: +7 342 2616161 (доб. 777)
						<?php }else { ?>

                            по телефону: +7 (342) 261-61-61 или в Skype: support_rtmis
						<?php } ?>
						<?php
						if (getRegionNick() == 'vologda') {
							echo('<br><br>Система трекинга обращений службы гарантийной поддержки ИС "РМИС ВО" Redmine в закрытой сети БУЗ ВО "МИАЦ" - <a href="http://redmine.vologdamed.local/">redmine.vologdamed.local</a>');
						}
						?>
					</span>
                    <span>
                        Разработка и поддержка &mdash; <a href="http://rtmis.ru">ООО &laquo;РТ МИС&raquo;</a>
                    </span>
                </div>
            </footer>

        </div>
    </body>
</html>
