
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<style type="text/css">
<!--
td.swvertext { /* Стиль текста */
	-moz-transform: rotate(270deg);
	-webkit-transform: rotate(270deg);
	-o-transform: rotate(270deg);
	text-align: center;
	font-weight: bold;
	height: 100px;
	width: 20px;
}
-->
</style>
<!--[if IE]>
<style type="text/css">
td.swvertext { /* Отдельные стили для IE */
	writing-mode:tb-rl;
	text-align: center;
	font-weight: bold;
	height: 100px;
	width: 20px;
}
</style><![endif]--> 
</head>
<body class="land" style="font-family: tahoma, verdana; font-size: 10pt; ">
<div style="text-align: center;">
	<p>
		<b>{Org_Name}</b><br/>
		{Org_Address}
	</p>
</div>
<div style="text-align: center;"><p><b>Единое направление на лабораторные исследования</b></p></div>
<table style="width: 100%;">
	<tr>
		<td>
			ФИО пациента:<br>
			Дата рождения:
		</td>
  		<td>
			  {Person_FIO}<br/>
			  {Person_Birthday}
		</td>
		<td rowspan="2" style="text-align: right;">
			<img src="http://data:image/png;base64,{Barcode}">

		</td>
	</tr>
</table>
<div style="text-align: center;"><p><b>Перечень направлений</b></p></div>
<table style="border-collapse: collapse; font-size: 11px; width: 100%;">
	<tbody>
		<tr>
			<th style="border: 1px solid black;">№</th>
			<th style="border: 1px solid black;">Наименование услуги</th>
			<th style="border: 1px solid black;">Дата и время записи</th>
			<th style="border: 1px solid black;">Место оказания</th>
			<th style="border: 1px solid black;">Адрес оказания</th>
		</tr>
		{LabDirections}
		<tr>
			<td style="border: 1px solid black; text-align: left;">{row_number}</td>
			<td style="border: 1px solid black; text-align: left;">{UslugaComplex_Name}</td>
			<td style="border: 1px solid black; text-align: left;">{TimetableMedService_begTime}</td>
			<td style="border: 1px solid black; text-align: left;">{MedService_Name}</td>
			<td style="border: 1px solid black; text-align: left;">{Address}</td>
		</tr>
		{/LabDirections}
	</tbody>
</table>
</body>

</html>
