<html>
<head>
<title>Отчет за длительный период</title>
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
		obj.innerHTML = "<b><a href='<?php echo $_SERVER['REQUEST_URI']."&RangeOverride=1" ?>'>Сформировать отчет</a></b>";
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
<h2 style="color:red">Вы пытаетесь сформировать отчет за период <b><?php echo $range?></b> <?php echo plural($range, 'день', 'дня', 'дней');?>.</h2>
Такие длительные периоды отчетов дают серьезную нагрузку на базу данных, что приводит к следующему:<br/>
<table width="100%" height="50">
<tr>
<td width="20%"></td>
<td align="left" valign="top"  width="60%">
  <b>* Отчет может не успеть сформироваться в течение получаса и вы не получите результат;</b><br/>
  <b>* Остальные отчеты будут формироваться медленней или вообще не будут выполняться.</b><br/>
</td>
<td width="20%"></td>
</tr>
</table>
Подумайте, может быть отчет можно разбить на несколько отчетов по меньшим периодам времени или <b>сформировать с дополнительными фильтрами</b>.<br/>
Если вы передумали формировать отчет - просто <b>закройте это окно</b> либо дождитесь появления ссылки и нажмите на нее для начала формирования отчета.<br/><br/>
<span id="wait"><b>Пожалуйста, подождите <span id="tc"><?php echo $region == 'kareliya' ? '10' : '60';?></span> сек. для начала формирования отчета.</b></span>
</td>
</tr>
</table>
</body>
</html>
