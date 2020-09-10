<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $titles['main_title']?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
        <link rel="stylesheet" type="text/css" href="/css/portal.css" />
		<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
        <script type="text/javascript">
            <?php echo "var ETOKEN_PRO_ENABLED = ".( $this->config->item('ETOKEN_PRO_ENABLED') ? $this->config->item('ETOKEN_PRO_ENABLED') : "0").";\n"; ?>
        </script>
		<script type="text/javascript" src="/jscore/locale/ru/portal.js"></script>
		<script type="text/javascript" src="/jscore/portal.js<?php echo "?", time(); ?>"></script>
		<script type="text/javascript" src="/jscore/libs/flot/jquery.min.js"></script>
		<script type="text/javascript" src="/jscore/libs/jquery.jsonp.js"></script>
		<script type="text/javascript" src="/jscore/libs/signservice/authApplet.js"></script>
		<script type="text/javascript" src="/jscore/libs/signservice/ncaLayer.js"></script>
		<script type="text/javascript" src="/jscore/libs/signservice/authApi.js"></script>
		<script type="text/javascript" src="/jscore/libs/cadesplugin_api.js"></script>
		<script type="text/javascript" src="/jscore/libs/lss-client.js"></script>
		<script type="text/javascript" src="/jscore/cryptopro.js"></script>
    </head>
    <body>
        <div class="page" id="pageCommon">
			<noscript><div class="noscript"><span>Для правильной работы системы необходима поддержка браузером JavaScript!</span></div></noscript>

            <div class="header">
                <div>
                    <div class="left">
                        <a href="?c=portal"><?php echo (getRegionNick() != 'msk') ? $titles['RIAMS'] : $titles['main_title'] ?></a> &rarr; <span><?php echo $title ?></span>
                    </div>
                    <div class="right">
                        <!--<span>Ваш город: <span class="link underline-dashed">Пермь</span></span>-->
                        <?php if ( !empty($links['lastupdates']) ) { ?><span><a href="<?php echo $links['lastupdates']; ?>">Последние изменения</a></span><?php } ?>
                        <span><a href="<?php echo $links['promedhelp'] ?>">Справочная система</a></span>
                         <?php if ( !empty($links['forum']) && getRegionNick() != 'msk' ) { ?><span><a href="<?php echo $links['forum'] ?>">Форум технической поддержки</a></span><?php } ?>
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
						&nbsp;
                    </span>
                </div>
            </div>

        </div>
    </body>
</html>
