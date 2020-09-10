<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Печать журнала регистрации поступления и выдачи тел умерших</title>
	<meta http-equiv=Content-Type
		  content="text/html; charset=<?php echo(defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
	<style type="text/css">
		@page port {
			size: portrait
		}

		@page land {
			size: landscape
		}

		body {
			margin: 20px 30px;
			padding: 0;
		}

		table {
			border-collapse: collapse;
		}

		/*span, div, td {*/
		/*	font-family: Times New Roman, serif, verdana;*/
		/*	font-size: 9px;*/
		/*}*/

		td {
			vertical-align: middle;
			border: 0 solid #000;
		}

		.style1 {
			font-size: 12px
		}

		.style2 {
			font-size: 12px
		}

		.vivod_ymershix {
			font-size: 16px !important;
			border-collapse: collapse;
			text-align: center;
		}

		.vivod_ymershix th, .vivod_ymershix td {
			border: 1px solid black;
		}


		/*.style3 {*/
		/*	width: 50%;*/
		/*	text-align: left;*/
		/*	vertical-align: top;*/
		/*	font-size: 9px;*/
		/*	color: grey;*/
		/*}*/
	</style>

	<style type="text/css" media="print">
		@page port {
			size: portrait
		}

		@page land {
			size: landscape
		}

		body {
			margin: 20px 30px;
			padding: 0;
		}

		/*span, div, td {*/
		/*	font-family: Times New Roman, serif, verdana;*/
		/*	font-size: 9px;*/
		/*}*/

		td {
			vertical-align: middle;
			border: 0 solid #ccc;
		}

		.style1 {
			font-size: 10px
		}

		.style2 {
			font-size: 10px
		}

		.vivod_ymershix {
			font-size: 16px !important;
			border-collapse: collapse;
			text-align: center;
		}

		.vivod_ymershix th, .vivod_ymershix td {
			border: 1px solid black;
		}

		/*.style3 {*/
		/*	width: 50%;*/
		/*	text-align: left;*/
		/*	vertical-align: top;*/
		/*	font-size: 8px;*/
		/*	color: grey;*/
		/*}*/
	</style>
</head>

<body class="land">
<!-- /*NO PARSE JSON*/ -->
<div align='center'>
	<table width="100%" border='0'>
		<!--		<tr>-->
		<!--			<td class=style3><b>Подготовлено с использованием системы ГАРАНТ</b><br></td>-->
		<!--		</tr>-->
		<tr>
			<td class=style2 style='width: 40%; text-align: center; vertical-align: top;'><small><br>
					<div style="border-bottom: 1px solid #000; text-align: center;margin-top: 30px;">{Lpu_Name}
					</div>
					<center>(полное наименование медицинской организации)</center>
					<div style="border-bottom: 1px solid #000; text-align: center;margin-top: 30px;">
						{Lpu_Address}
					</div>
					<center>(адрес медицинской организации)</center>
			</td>
			<td class=style2 style='width: 50%; text-align: right; vertical-align: top; font-size:12px;'>
				<b>Приложение №4</b><br>
				<b>к приказу Министерства здравоохранения</b><br>
				<b>Российской Федерации</b><br>
				<b>от 6 июня 2013 г. № 354н</b><br>
				<table width="100%" border='0'>
					<tr>
						<td class="style1" style='text-align: right; vertical-align: top;'>
							Код формы по ОКУД
						</td>
						<td style='width: 150px; text-align: right; vertical-align: top;'>
							<div style="border-bottom: 1px solid #000;width: 180px;">&nbsp;</div>
						</td>
					</tr>
				</table>
				<table width="100%" border='0'>
					<tr>
						<td class="style1" style='text-align: right; vertical-align: top;'>
							Код учреждения по ОКПО
						</td>
						<td style='width: 151px; text-align: right; vertical-align: top;'>
							<div style="border-bottom: 1px solid #000;">&nbsp;</div>
						</td>
					</tr>
				</table>
				<table width="60%" border='0' style="float: right">
					<tr>
						<td style="padding-top: 20px">
							<center>
								<span class="style1">Медицинская документация</span><br>
								<span class="style1">Форма <b>№ 015/у</b></span><br>
								<span class="style1">Утверждена приказом Минздрава России</span><br>
								<span class="style1">от 6 июня 2013 г. № 354н</span><br>
								</small>
							</center>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
	<table width="100%" border='0'>
		<td style="font-size: 15px;padding-top: 17%;padding-bottom: 30%">
			<center>
				<b style="letter-spacing: 5px">ЖУРНАЛ</b><br>
				<b style="margin: 5px 0">регистрации поступления и выдачи тел умерших</b><br>
				<!--				<b>Начат <u>«20» февраля 2020</u> г. окончен<u>«20» февраля 2020</u> г.</b>-->
				<b>Начат <u>«{begDay}»{begMonth} {begYear}</u> г. окончен<u>«{endDay}»{endMonth} {endYear}</u> г.</b>
				</small>
			</center>
		</td>
	</table>

<!--	<hr style="border: none; border-top:1px dashed gray;"/>-->

	<table class="vivod_ymershix" width="100%" border='1' id="number">
		<tr>
			<td style="width: 30px">№ п/п</td>
			<td>Дата поступления тела умершего</td>
			<td>ФИО умершего (в случае доставки плода или мертворожденного — ФИО матери)</td>
			<td>Наименование медицинской организации (отделения медицинской организации), из которой доставлено тело
				умершего
			</td>
			<td>Номер медицинской карты*</td>
			<td>Дата проведения патологоанатомического вскрытия или отметка об отказе от его проведения</td>
			<td>Дата выдачи тела умершего</td>
			<td>ФИО лица, которому выдано тело умершего и данные документа, удостоверяющего его личность</td>
			<td>Подпись лица, которому выдано тело умершего</td>
		</tr>
		<tr>
			<td>1</td>
			<td>2</td>
			<td>3</td>
			<td>4</td>
			<td>5</td>
			<td>6</td>
			<td>7</td>
			<td>8</td>
			<td>9</td>
		</tr>
		{list}
	</table>


	<table width="100%" border='0'>
		<tr>
			<td class='style2' style="text-align: left;"><b>* Медицинская карта стационарного пациента, медицинская
					карта амбулаторного пациента, медицинская карта родов, медицинская карта новорожденного.</b><br>
			</td>
		</tr>
	</table>
</div>
</body>
</html>