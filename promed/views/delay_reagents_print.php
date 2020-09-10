<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
	<link href="/css/gridprint.css" rel="stylesheet" type="text/css" media="screen,print" />
	<title>Реагенты с истекающим сроком годности</title>
</head>

<body>
<table>
	<tr>
		<th>Склад</th>
		<th>Серия</th>
		<th>Срок годности</th>
		<th>Остаточный срок годности в днях</th>
		<th>Наименование медикамента</th>
		<th>Номер партии</th>
	</tr>
	{items}
	<tr>
		<td>{Storage_name}</td>
		<td>{PrepSeries_Ser}</td>
		<td>{PrepSeries_GodnDate}</td>
		<td>{Ostat_GodnDate}</td>
		<td>{Drug_name}</td>
		<td>{DrugShipment_Name}</td>
	</tr>
	{/items}
</table>
</body>
</html>
