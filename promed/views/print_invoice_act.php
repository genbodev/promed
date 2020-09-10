<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Акт приема данных о Поставках ЛС в АУ</title>
</head>

<body class="portrait">

<b>Акт приема данных о Поставках ЛС в АУ</b><br />
Дата: {curDT}<br />
Поставщик: {Contragent_Name}<br />
<br />
<b>Формат представления данных:</b><br />
<table border='1'>
<tr>
<td>№ п/п</td>
<td>Имя файла в архиве</td>
<td>Размер файла (байт)</td>
<td>Контрольная сумма файла в архиве</td>
</tr>
{files}
<tr>
<td>{number}</td>
<td>{filename}</td>
<td>{filesize}</td>
<td>{crc}</td>
</tr>
{/files}
</table>
<br />
<b>Результаты экспертизы данных:</b>
<table border='1'>
<tr>
<td></td>
<td>Кол-во накладных</td>
<td>Кол-во ЛС (уп.)</td>
<td>На сумму (руб.)</td>
</tr>
<tr>
<td>Предъявлено</td>
<td>{countinvoices_all}</td>
<td>{countinvoicedrugs_all}</td>
<td>{countsum_all}</td>
</tr>
<tr>
<td>Приняты</td>
<td>{countinvoices_accepted}</td>
<td>{countinvoicedrugs_accepted}</td>
<td>{countsum_accepted}</td>
</tr>
<tr>
<td>Отклонены</td>
<td>{countinvoices_notaccepted}</td>
<td>{countinvoicedrugs_notaccepted}</td>
<td>{countsum_notaccepted}</td>
</tr>
</table>
<br />
<b>Протокол ошибок:</b>
{errors}

</body>

</html>