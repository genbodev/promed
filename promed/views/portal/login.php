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
  </head>
  <body>
    <div class="page">
      <div class="page-single">
        <div class="container">
          <div class="row">
            <div class="col col-login mx-auto">
			
			
				<?php if (isset($msg)) { ?>
					<div class="error_msg">
						<?php echo $msg ?>
					</div>
				<?php } ?>
				
				
              <form class="card" action="?c=portal&m=login" method="POST">
                <div class="card-body p-6">
                  <div class="card-title">Вход</div>
                  <div class="form-group">
                    <input required id="username" name="username" class="form-control" type="text" placeholder="Имя пользователя">
                  </div>
                  <div class="form-group">
                    <input required id="password" name="password" class="form-control" type="password" placeholder="Пароль">
                  </div>
				  <!-- 
                  <div class="form-group">
                    <label class="custom-control custom-checkbox">
                      <input class="custom-control-input" type="checkbox"><span class="custom-control-label">Запомнить</span>
                    </label>
                  </div>
				  -->
                  <div class="form-footer">
                    <button class="btn btn-primary btn-block" type="submit">Войти </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="/jscore/portal.admin.2020.js"></script>
		<script type="text/javascript">
			window.onload = function() {
				document.getElementById('username').focus();
			}
		</script>
  </body>
</html>