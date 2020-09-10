<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<!--title>Лист назначений</title-->
<!--style type="text/css">
@media print {
	table {
		page-break-after: always;
	} 
}
</style-->
   <!--style type="text/css">
td.swcielnumeric { vertical-align: middle; text-align: center; padding: 2px; }
td.swciellevel0 { vertical-align: top; text-align: center; padding: 2px; }
td.swciellevel1 { vertical-align: top; text-align: center; padding: 2px; padding-left: 5px; }
td.swcieltitle { vertical-align: middle; text-align: center; padding: 5px; font-weight: bold; }
h2.swsectiontitle { text-align: center; margin: 10px; }
</style-->

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

<div style="text-align: left;"><p>Лист врачебных назначений &emsp; &#8470; {NumCard}</p></div>
<div style="text-align: left;"><p><b>МО:</b> {Lpu_Name}</p></div>
<div style="text-align: left;"><p><b>Отделение:</b> {LpuSection_Code} {LpuSection_Name}</p></div>
<div style="text-align: left;"><p><b>Лечащий врач:</b> {MedPersonal_Fio}</p></div>
<div style="margin-bottom: 1em; text-align: left;"><p><b>Пациент:</b> {Person_FIO} &emsp; {Person_Birthday}</p></div>
<?php
$l = count($ep_list)-1;
unset($ep_list[$l]);

foreach($ep_list as $row) {
    echo $row['EvnPrescr_Name'];
}
?>

</body>

</html>