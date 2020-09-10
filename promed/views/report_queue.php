<html>
<head>
<title>Отчет поставлен в очередь</title>
<style>
body { font-family: Tahoma, Verdana, Arial, sans-serif; }
</style>
</head>
<body bgcolor="white" text="black">
<script type="text/javascript">
function timer() {
	var obj = document.getElementById('tc');
	obj.innerHTML--;
	 
	if(obj.innerHTML == 0) {
		var obj = document.getElementById('wait');
		window.close();
	}
	else {
		setTimeout(timer,1000);
	}
}
setTimeout(timer,1000);
</script>
<table width="100%" height="100%">
<tr>
<td align="center" valign="middle">
<h1 style="color:red">Внимание!</h1>
<h2 style="color:red">Ваш отчет за период <b><?php echo $range?></b> <?php echo plural($range, 'день', 'дня', 'дней');?> поставлен в очередь на формирование.</h2>
Отчеты попадают в очередь автоматически в следующих случаях:<br/>
<table width="100%" height="50">
<tr>
<td width="20%"></td>
<td align="left" valign="top"  width="60%">
  <b>* Высокая текущая нагрузка на сервер отчетности;</b><br/>
  <b>* Отчет с большим периодом формирования;</b><br/>
  <b>* Отчет входит в перечень &quot;тяжелых&quot; отчетов;</b><br/>
</td>
<td width="20%"></td>
</tr>
</table>
<br/>
После завершения процесса формирования отчета вы будете информированы через функционал личных сообщений, также  в любой момент времени вы можете просмотреть информацию о формирующихся и выполненных отчетах на форме "Отчеты: Очередь и история".

<br/><br/><span id="wait"><b>Данное окно закроется автоматически через <span id="tc">30</span> сек.</b></span>
</td>
</tr>
</table>
</body>
</html>
