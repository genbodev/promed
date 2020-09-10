<html>

<head>
	<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
	<meta name=ProgId content=Word.Document>
	<meta name=Generator content="Microsoft Word 11">
	<meta name=Originator content="Microsoft Word 11">
	<style>
		table {font-size:11pt; border-collapse: collapse;}
		table td, table th {border: 1px solid black;}
		.tit {font-family:Arial CYR; font-weight:bold; font-size:12pt; text-align:center}
		.border {border: solid 1px black;}
		.podval {font-size:8pt}
		.disp td {text-align: center}
	</style>

	<title>Медкарта пациента - {Person_SurName}&nbsp;{Person_FirName}&nbsp;{Person_SecName}. Сторона Б</title>

</head>
<body>
<!-- /*NO PARSE JSON*/ -->

<table width="100%">
	<caption class="tit">Изменение адреса и места работы</caption>
	<tr>
		<th>Дата</th>
		<th>Новый адрес, новое место работы</th>
	</tr>
	{adr_list}
		<tr class="disp">
			<td>{PAdr_Date}&nbsp;</td>
			<td>{PAddress}&nbsp;</td>
		</tr>
	{/adr_list}
</table>

<table width="100%" style="margin-top: 10px;">
	<caption class="tit">Заболевания, подлежащие диспансерному наблюдению</caption>
	<tr>
		<th rowspan="2">Наименование заболевания</th>
		<th rowspan="2">МКБ-10</th>
		<th rowspan="2">Дата взятия</th>
		<th colspan="2">Врач</th>
		<th rowspan="2">Дата снятия</th>
		<th colspan="2">Врач</th>
	</tr>
	<tr>
		<th>Должность</th>
		<th>Подпись</th>
		<th>Должность</th>
		<th>Подпись</th>
	</tr>
	{disp_list}
		<tr class="disp">
			<td>{Diag_Name}&nbsp;</td>
			<td>{Diag_Code}&nbsp;</td>
			<td>{PersonDisp_begDate}&nbsp;</td>
			<td>{Med_Post_Name}&nbsp;</td>
			<td>{MedPersonal_FIO}&nbsp;</td>
			<td>{PersonDisp_endDate}&nbsp;</td>
			<td>{Med_Post_Name_end}&nbsp;</td>
			<td>{MedPersonal_FIO_end}&nbsp;</td>
		</tr>
	{/disp_list}
</table>

</body>

</html>