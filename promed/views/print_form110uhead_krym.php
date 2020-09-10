<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<style>
html {margin:0; padding: 0;}
body {Font-family:'Times New Roman', Times, serif; font-size:12pt; margin: 0; padding: 0;}
p {margin:0 0 5px}
table {font-size:20pt; vertical-align: top;}
table td {font-size:12pt;}

.head110 {vertical-align: top; height: 0px;}
.head110 big {line-height:14px;}
table.time {width: 100%; border-collapse:collapse;}
    table.time td {border:1px solid #000; text-align: left; border-bottom: 0px; padding-left: 5px; vertical-align: top;}
	table.time td.ender {border-bottom: 1px solid #000;}
span {display:inline-block;}
.under {border-bottom: 1px solid;}
.lister{
	width: 26cm;
	height: 18cm;
	/*-webkit-transform: rotate(-90deg);*/
	/*-moz-transform: rotate(-90deg);*/
	/*-o-transform: rotate(-90deg);*/
	/*-ms-transform: rotate(-90deg);*/
	/*transform: rotate(-90deg);*/
	/*filter:progid:DXImageTransform.Microsoft.BasicImage(rotation=3);*/
	/*margin-left: -4cm;*/
    /*margin-top: 4cm;*/
	/*border: 1px solid;*/
}
.page {
	width: 25.4cm;
	height: 17.4cm;
	float: left;
	display: block;
}
.pageLeft{
}
.pageRight {
}
</style>
<script type="text/javascript">
window.print();
</script>
<title></title>

</head>
<body>

<div class="lister">
	<div class="page pageLeft">
		<center>
			<h2>Талон вызова скорой медицинской помощи</h2>
		</center>
		<table  class="time">
			<tr>
				<td width="30%">№ {Year_num} / {Day_num},  {CallCardDate}г. </td>
				<td width="30%">Повод: {Reason}</td>
				<td width="20%">Вызвал: {CmpCallerType_Name}</td>
				<td width="20%">Бригада № {EmergencyTeam_Num}</td>
			</tr>

			<tr>
				<td class="ender" width="30%">
					{Address_Address}
				</td>
				<td class="ender" width="30%">
					{FIO}
					<?=(($Age>0)?$Age:$AgePS)?> <?=(($Age>0)?'Лет':$AgeTypeValue)?>	   <?=(mb_substr($Sex_name, 0, 1))?><br>
					{Phone}
				</td>
				<td class="ender" width="20%">
					{LpuBuilding_Name}<br>
					Принял: Д № {Dsp_prm}<br>
					Передал: Д № {Dsp_per}<br>
					Линия №1 <br>
					Способ: ГС
				</td>
				<td class="ender" width="20%">
					Принят: {AcceptTime}<br>
					Передан: {PerTime}<br>
					Исполнен: {IspTime}<br>
					Общее время: <?=($Dlit>0)?$Dlit.' мин.':''?><br>
					Врач {Doc}
				</td>
			</tr>
		</table>
	</div>
</div>
</body>
</html>