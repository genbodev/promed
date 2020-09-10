<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
	<link href="/css/gridprint.css" rel="stylesheet" type="text/css" media="screen,print" />
	<title>Реестр ОМС</title>
</head>

<body>
<table>
<tr>
	<th>№ талона</th>
	<th>ФИО пациента</th>
	<th>Дата рождения</th>
	<th>БДЗ</th>
	<th>Отделение</th>
	<th>Врач</th>
	<th>Посещение</th>
	<th>Выписка</th>
	<th>УЕТ факт</th>
	<th>УЕТ норматив</th>
	<th>УЕТ к оплате</th>
	<th>Тариф</th>
	<th>Сумма к оплате</th>
	<th><img src="/img/grid/hourglass.gif" /></th>
	<th>Изменена</th>
</tr>
{items}
<tr>
	<td>{EvnPL_NumCard}</td>
	<td>{Person_FIO}</td>
	<td>{Person_BirthDay}</td>
	<td>{Person_IsBDZ}</td>
	<td>{LpuSection_name}</td>
	<td>{MedPersonal_Fio}</td>
	<td>{EvnVizitPL_setDate}</td>
	<td>{Evn_disDate}</td>
	<td>{RegistryData_Uet}</td>
	<td>{RegistryData_KdPlan}</td>
	<td>{RegistryData_KdPay}</td>
	<td>{RegistryData_Tariff}</td>
	<td>{RegistryData_ItogSum}</td>
	<td>{checkReform}</td>
	<td>{timeReform}</td>
</tr>
{/items}
</table>
</body>
</html>
