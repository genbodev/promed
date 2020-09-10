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
	<th>{Usluga_Code_TH}</th>
    <th>Сумма</th>
	<th>Код диагноза</th>
	<th>Отделение</th>
	<th>Подразделение</th>
	<th>Врач</th>
	<th>{EvnVizitPL_TH}</th>
    <th>Выписка</th>
	<th>{RegistryData_Uet_TH}</th>
	<th>Оплата</th>
    <th>КСГ</th>
    <th>КПГ</th>
</tr>
{items}
<tr>
	<td>{EvnPL_NumCard}</td>
	<td>{Person_FIO}</td>
	<td>{Person_BirthDay}</td>
	<td>{Usluga_Code}</td>
    <td>{RegistryData_Sum_R}</td>
	<td>{Diag_Code}</td>
	<td>{LpuSection_name}</td>
	<td>{LpuBuilding_Name}</td>
	<td>{MedPersonal_Fio}</td>
	<td>{EvnVizitPL_setDate}</td>
    <td>{Evn_disDate}</td>
	<td>{RegistryData_Uet}</td>
	<td>{Paid}</td>
    <td>{Mes_Code_KSG}</td>
    <td>{Mes_Code_KPG}</td>
</tr>
{/items}
</table>
</body>
</html>
