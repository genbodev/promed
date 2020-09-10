<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>ProMed</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
html,body{ 
	margin: 5px 5px; 
	padding: 0px; 
	font-family: Tahoma, Geneva, Arial, Helvetica, sans-serif; 
	font-size: 12px; 
	color: black;
}
table{ 
	font-family: Tahoma, Geneva, Arial, Helvetica, sans-serif; 
	font-size: 12px; 
}
#printtimeTable{
	border: none;
	border-collapse: collapse;
	width: 100%
}
#printtimeTable td{
	border: 1px solid gray;
	text-align: left;
	font-size: 11px;
	padding: 2px 2px;
}
#printtimeTable td a{
	text-decoration: none;
}
#printtimeTable tr.head td.relax{
	background-color: #ffdddd;
}
#printtimeTable tr.head td.work{
	background-color: #ddffdd;	
}
#printtimeTable tr.time td.active{
	cursor: pointer;
}
#printtimeTable tr.head td{
	padding-left: 5px;
	padding-right: 5px;
}
#printtimeTable tr.time td{
	padding-left: 10px;
	padding-right: 10px;
	width: 33%;
}
#printtimeTable tr.time td.free{
	background-color: #ddffdd;
}
#printtimeTable tr.time td.locked{
	background-color: #dddddd;
	cursor: default;
}
#printtimeTable tr.time td.person{
	background-color: #ffdddd;
	cursor: default;
}
#printtimeTable tr.time td.reservedcz{
	background-color: yellow;
	cursor: default;
}
#printtimeTable tr.time td.reserved{
	background-color: yellow;
}
#printtimeTable tr.time td.reservedPerson{
	background-color: orange;
}
#printtimeTable tr.time td.dop{
	background-color: #ddddff;
}
#printtimeTable tr.time td.dopPerson{
	background-color: #ddaadd;
}
#printtimeTable tr.foot td{
	vertical-align: top;
}
</style>
</head>

<body class="land">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody><tr valign="top">
<td>
<div id="content">
	<h1 style="font-size:14pt;">Уважаемые пациенты.</h1>
	<h1 style="font-size:12pt;">Прием, за исключением экстренных больных, осуществляется строго по записи!</h1>
	{Info}
	<div style="font-size:14pt;">
		<b>Расписание приема</b>
        <?php if (!empty($MedPersonal_FIO)) { ?>
            <br>Врач: {MedPersonal_FIO}
		<?php } else { ?>
            <br>Служба: {MedService_Name}
		<?php } ?>
		<br>{Lpu_Nick}, {Address_Address}
		<br>Отделение: {LpuSection_Name}
        <br>Дата:
        <?php if (!empty($Day)) { ?>
			{Day}
        <?php } else { ?>
            {begDate} - {endDate}
		<?php } ?>
		<br>
	</div>
	<br>
	<div style="font-size:12pt;">
		<i>
			В целях соблюдения прав граждан на защиту тайны посещения врача 
			<b>в списке указываются только первые буквы фамилии имени и отчества.</b>
		</i>
	</div>
	<br>
	{printtimeTable}
	{OrgHead}
	<br>Дата распечатки: {Print_Date}<br><br><br>
	<div align="center">
		<a target="_blank" href="#" onclick="this.style.visibility= 'hidden'; window.print(); return false;">Печать</a>
	</div><br>
</div>

</td>
</tr>
</tbody></table>

</body></html>