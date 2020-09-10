<!DOCTYPE html>
<html>
<style>
	a.lang {
		text-decoration: none
	}
	a.lang:hover {
		text-decoration: underline
	}
</style>
    <head>
        <!--<title>РИАМС ПроМед</title>
        -->
		<title><?php echo $titles['main_title']; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
        <link rel="stylesheet" type="text/css" href="/css/portal_old.css" />
		<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
    </head>
    <body class="kz">
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
                <div class='logo-container'>

                    <div class="lang-container" style="height: 34px">
                        <div class="lang" style="float: right">
                            <?php
                        if(isset($_SESSION['lang']) && $_SESSION['lang'] == 'kz'){
                            echo '<font style="color: #a4bbdb;">ҚАЗ</font>';
                            echo '<font style="color: white;"> | </font>';
                            echo '<a class="lang" href="/?c=portal&lang=ru" style="color: white;">РУС</a>';
                        }
                        else {
                            echo '<a class="lang" href="/?c=portal&lang=kz" style="color: white;">ҚАЗ</a>';
                            echo '<font style="color: white;"> | </font>';
                            echo '<font style="color: #a4bbdb;">РУС</font>';
                        }
                        ?>
                        </div>
                    </div>

                    <h1>
                        <strong><?php echo $titles['main_page_1']; ?></strong>
                        <small><?php echo $titles['main_page_2']; ?></small>
                    </h1>
                    <div class="image">
                        <img src="/img/portal/riams-header-photo2.jpg" width="250" height="167" />
                    </div>

                    <div class="set-region">
                        <div class="location-pointer"></div>
                        <span class="location-title"><?php echo $region_title; ?></span>
                    </div>
                    
                    <div class='logo-description'>
                        <?php echo $titles['main_description']; ?>
                    </div>
                </div>
            </div>

            <div class="content-wrapper">
                <div class="content">

                     <div class="links">
                        <!--<span>Ваш город: <span class="link underline-dashed">Пермь</span></span>-->
                        <!--span><a href="<?php echo $links['forum']; ?>">Форум технической поддержки</a></span-->
                    </div>

                    <div class="text">
                        <div id="seminar">
                            <!--a class="highlight" href="?c=portal&m=article&action=view&id=1">Подробнее о системе</a-->
                            <?php if (!empty($seminar)) { ?>
                                <div class="highlight"><?php echo $seminar ?></div>
                            <?php } ?>
                        </div>
                    </div>

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
                    <!--<div class="news">
                        <h2>Новости</h2>
						<div id="newsContainer">
							<?php echo $news_entries; ?>
						</div>
						<?php if (!$end) { ?>
							<span class="more-link"><a href="?c=portal&m=news">Архив новостей</a></span>
						<?php } ?>
                    </div>-->
                </div>
            </div>

            <div class="footer">
                <div>
                    <span>
                        <?php echo $bottom_1." ".$bottom_2; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="modal" style="display: none">
            <div class="modal-content">
                <div class="modal-close"></div>
                <div class="title">Выберите Ваш регион</div>

                <ul class="kz-regions">
                    <?php $regions = getKzRegions();
					$cfg = $this->config->item('portal');
                    foreach($regions as $region) { $active_class = ($region['name'] == 1) ? "active" : "";
                        $url = "http://".((!empty($cfg['kz_main_domain']))?$cfg['kz_main_domain']:"localhost")."/?c=portal&". http_build_query(array('kz_region_name' => $region['name']), '', '&'); ?>
                        <li style="width: 300px;display: block;float: left; line-height: 32px; ">
                            <?php if($region_title == $region['title']):?>
                                <span class="change-region"><?php echo $region_title; ?></span>
                            <?php else: ?>
                                <a class="<?php echo $active_class; ?>" href="<?php echo $url; ?>"><?php echo $region['title']; ?></a>
                            <?php endif; ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <script>
            $(function() {
                $('.modal').click(function(e){
                    e.stopPropagation();
                    $(this).hide();
                });
                <?php if ($region_title == "Выберите регион"
                    && strripos($_SERVER['SERVER_NAME'], 'localhost') === false
                    && strripos($_SERVER['SERVER_NAME'], 'kz.swn.local') === false) { ?>
                $('.products').find(':first-child').find('a').click(function(){
                        $('.modal').show();
                        return false;
                });
                <?php } ?>

                $('.modal-content').click(function(e){
                    e.stopPropagation();
                });

                $('.modal-close').click(function(){
                    $('.modal').hide();
                });

                $('.location-title').click(function(){
                    $('.modal').show();
                })
            })
        </script>

    </body>
</html>
