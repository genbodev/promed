<!DOCTYPE html>
<html>
    <head>
        <!--<title>РИАМС ПроМед</title>
        -->
		<title><?php echo $titles['main_title']; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
		<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
        <link rel="stylesheet" type="text/css" href="/css/portal.css" />
    </head>
    <body>
        <div class="page" id="pageMain">
			<noscript><div class="noscript"><span>Для правильной работы системы необходима поддержка браузером JavaScript!</span></div></noscript>

			<?php
				if (isset($login_warning)) {
			?>
				<div class="login-warning"><span>Для входа в систему необходима авторизация!</span></div>
			<?php
				}
			?>
				
            <div class="header">
                <div>
                    <h1>
                        <strong><?php echo $titles['main_page_1']; ?></strong>
                        <small><?php echo $titles['main_page_2']; ?></small>
                    </h1>
                    <div class="links">
                        <!--<span>Ваш город: <span class="link underline-dashed">Пермь</span></span>-->
                        <?php if ( !empty($links['forum']) ) { ?><span><a href="<?php echo $links['forum']; ?>">Форум технической поддержки</a></span><?php } ?>
                    </div>
                    <div class="content">
                        <div class="text">

                            <a class="highlight" href="?c=portal&m=article&action=view&id=1">Подробнее о системе</a>
                            <?php if (!empty($seminar)) { ?>
                                <div class="highlight"><?php echo $seminar ?></div>
                            <?php } ?>
                        </div>
                        <div class="image">
                            <img src="/img/portal/riams-header-photo1.jpg" width="250" height="167" />
                        </div>

                    </div>
                </div>
            </div>

            <div class="content-wrapper">
                <div class="content">

					<?php if (!empty($notices)) { ?>
						<div class="notices">
							<div>
								<h1>Внимание!</h1>

								<?php echo $notices; ?>

                            </div>
                        </div>
                    <?php } ?>

                    <div class="products">

						<?php echo $products; ?>

                    </div>

                    <div class="news">

                        <h2>Новости</h2>

						<div id="newsContainer">

							<?php echo $news_entries; ?>

						</div>

						<?php if (!$end) { ?>
							<span class="more-link"><a href="?c=portal&m=news">Архив новостей</a></span>
						<?php } ?>

                    </div>

                </div>
            </div>

            <div style="clear: both; width: 100%; height: 70px; padding-top: 10px; background-color: #3e5b90; color: #ffffff;">
                <div style="width: 970px; margin: auto;">
					<span style="float:left; font-size: 70%; width: 510px;">
                        По экстренным техническим вопросам <b>В НЕРАБОЧЕЕ ВРЕМЯ</b> обращайтесь<br />
                        по телефону: (342) 261-61-61 или в Skype: support_promed
					</span>
                    <span style="float: right; width: 200px; font-size: 60%">
                        Разработка и поддержка<br />
                        <a href="http://swan.perm.ru">ООО &laquo;СВАН&raquo;</a>
                    </span>
                </div>
            </div>

        </div>
    </body>
</html>
