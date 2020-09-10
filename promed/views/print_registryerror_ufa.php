<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
	<link href="/css/gridprint.css" rel="stylesheet" type="text/css" media="screen,print" />
	<title>Ошибки данных</title>
</head>

<body>
<table>
<tr>
	<th>Код</th>
	<th>Наименование</th>
	<th>ФИО пациента</th>
	<th>Дата рождения</th>
	<th>{Usluga_Code_TH}</th>
	<th>Отделение</th>
	<th>Подразделение</th>
	<th>Врач</th>
	<th>Начало</th>
	<th>Окончание</th>
	<th>Тип</th>
</tr>
{items}
<tr>
	<td>{RegistryErrorType_Code}</td>
	<td>{RegistryErrorType_Name}</td>
	<td>{Person_FIO}</td>
	<td>{Person_BirthDay}</td>
	<td>{Usluga_Code}</td>
	<td>{LpuSection_name}</td>
	<td>{LpuBuilding_Name}</td>
	<td>{MedPersonal_Fio}</td>
	<td>{Evn_setDate}</td>
	<td>{Evn_disDate}</td>
	<td>{RegistryErrorClass_Name}</td>
</tr>
{/items}
</table>
</body>
</html>
