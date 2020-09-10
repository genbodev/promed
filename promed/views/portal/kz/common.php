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
        <title><?php echo $titles['main_title']?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
        <link rel="stylesheet" type="text/css" href="/css/portal_old.css" />
		<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
		<?php
			if(isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'kz')
				echo '<script type="text/javascript" src="/jscore/locale/kz/portal.js"></script>';
			else
				echo '<script type="text/javascript" src="/jscore/locale/ru/portal.js"></script>';
		?>
		<!--<script type="text/javascript" src="/jscore/locale/ru/portal.js"></script>-->
        <script type="text/javascript">
            <?php echo "var ETOKEN_PRO_ENABLED = ".( $this->config->item('ETOKEN_PRO_ENABLED') ? $this->config->item('ETOKEN_PRO_ENABLED') : "0").";\n"; ?>
        </script>
		<script type="text/javascript" src="/jscore/portal.js"></script>
		<script type="text/javascript" src="/jscore/libs/flot/jquery.min.js"></script>
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
        </script>
    </head>
    <body>
        <div class="page" id="pageCommon">
			<noscript><div class="noscript"><span>Для правильной работы системы необходима поддержка браузером JavaScript!</span></div></noscript>

            <div class="header">
                
                <div>
                    <div class="left">
                        <a href="?c=portal"><?php echo $titles['RIAMS']?></a> &rarr; <span><?php echo $title ?></span>
                    </div>
                    <div class="right">
                        <!--<span>Ваш город: <span class="link underline-dashed">Пермь</span></span>-->
                        <?php if ( !empty($links['lastupdates']) ) { ?><span><a href="<?php echo $links['lastupdates']; ?>"><?php echo $last_upd; ?></a></span><?php } ?>
                        <span><a href="<?php echo $links['promedhelp'] ?>"><?php echo $help_sys; ?></a></span>
                        <!--span><a href="<?php echo $links['forum'] ?>">Форум технической поддержки</a></span-->
 
				<?php
					if(isset($_SESSION['lang']) && $_SESSION['lang'] == 'kz'){
						echo '<font style = "color: #a4bbdb;">ҚАЗ</font>';
						echo ' | ';
						echo '<a class = "lang" href="/?c=portal&m=promed&lang=ru" style="color: white;">РУС</a>';
					}
					else {
						echo '<a class = "lang" href="/?c=portal&m=promed&lang=kz" style="color: white;">ҚАЗ</a>';
						echo ' | ';
						echo '<font style = "color: #a4bbdb;">РУС</font>';
					}
				?>
                    </div>
                </div>
            </div>

            <div class="content-wrapper">
                <div>
                    <div>
                        <div class="nav">
                            <ul>
								<?php echo $nav ?>
							</ul>
						</div>

						<div class="content">

							<?php echo $content ?>

                        </div>  

                    </div>
                </div>
            </div>

            <div class="footer">
                <div>
                    <span>
						<?php echo $bottom_1; ?><br /><?php echo $bottom_2; ?>
                    </span>
                </div>
            </div>

        </div>
    </body>
</html>
