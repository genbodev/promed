<!---/*NO PARSE JSON*/--->
<b>Логин:</b> <?=$data['username']?><br/>
<b>E-mail:</b> <?=$data['email']?><br/>
<b>ФИО:</b> <?=$data['Person_FIO']?><br/>
<b>Дата рождения:</b> <?=$data['birthday']->format('d.m.Y')?><br/>
<b>Телефон для СМС оповещений:</b> <?=$data['UserNotify_Phone']?><br/>
<b>Дата создания аккаунта:</b> <?=$data['creating_date']->format('d.m.Y')?><br/>
<b>Дата последнего входа:</b> <?=$data['last_login']->format('d.m.Y')?><br/>