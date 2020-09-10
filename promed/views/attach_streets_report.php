<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Отчет по зонам обслуживания участков ЛПУ: {Lpu_Nick}</title>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<style type="text/css">
form, span, div, td { font-family: tahoma, verdana; font-size: 10pt; }
td { vertical-align: middle; border: 1px solid #000; }
.detailsTable{
	border-collapse: collapse;
}
.detailsTable td{
	padding: 3px;
	border: 1px solid gray;
}
.detailsTable tr.head td{
	font-weight: bold;
}
</style>
</head>

<body>
<div>Отчет по зонам обслуживания участков ЛПУ: {Lpu_Nick}</div>
<table  class="detailsTable" cellspacing="0" cellpadding="0" border="0">
<tr>
<td align="center"><b>Участок</b></td>
<td align="center"><b>Тип участка</b></td>
<td align="center"><b>Прикреплено</b></td>
<td align="center"><b>Ф.И.О. врача</b></td>
<td align="center"><b>Зоны и границы обслуживания врачебных участков (улица, дом)</b></td>
</tr>
{lpu_region_streets_data}
<tr>
<td rowspan="{streets_count}">{LpuRegion_Name}</td>
<td rowspan="{streets_count}">{LpuRegionType_Name}</td>
<td rowspan="{streets_count}">{Attached_Count}</td>
<td rowspan="{streets_count}" nowrap>{MedPersonal_Name}</td>
</tr>
{streets}
<tr>
<td>{KLArea_Name}</td>
</tr>
{/streets}
{/lpu_region_streets_data}
</table>
</body>

</html>