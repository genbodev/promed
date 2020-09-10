<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Акт медико-экономической экспертизы счетов и персонифицированных реестров ЛС, отпущенных по рецептам за месяц</title>
</head>

<body class="portrait">

<b>Акт медико-экономической экспертизы счетов и персонифицированных реестров ЛС, отпущенных по рецептам за месяц</b><br />
Дата: {ReceptUploadLog_setDT}<br />
Поставщик: {Contragent_Name}<br />
<br />
<b>Формат представления реестров обслуженных рецептов в центр обработки данных:</b><br />
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
<b>Результаты экспертизы:</b>
<table border='1'>
<tr>
<td>Программа ЛЛО</td>
<td>Предъявлено рецептов</td>
<td>На сумму</td>
<td>Рецептов, принятых к оплате</td>
<td>На сумму</td>
<td>Рецептов, отклоненных от оплаты</td>
<td>На сумму</td>
</tr>
{results}
<tr>
<td>{programm}</td>
<td>{count_all}</td>
<td>{count_all_sum}</td>
<td>{count_accepted}</td>
<td>{count_accepted_sum}</td>
<td>{count_notaccepted}</td>
<td>{count_notaccepted_sum}</td>
</tr>
{/results}
</table>
<br />
<b>Причины отказа в оплате:</b>
<table border='1'>
<tr>
<td>Код ошибки</td>
<td>Содержание дефекта</td>
<td>Кол-во рецептов</td>
</tr>
{errors}
<tr>
<td>{RegistryReceptErrorType_Type}</td>
<td>{RegistryReceptErrorType_Name}</td>
<td>{quantity}</td>
</tr>
{/errors}
</table>
<br />
Протокол экспертизы в dbf-формате прилагается.

</body>

</html>