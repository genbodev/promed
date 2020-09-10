<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>{title}</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: tahoma, verdana; font-size: 10pt; }
td { vertical-align: middle; border: 1px solid #000; }
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: tahoma, verdana; font-size: 10pt; }
td { vertical-align: middle; border: 1px solid #ccc; }
</style>
</head>

<body class="land">

<div style="font-size: 16pt; font-weight: bold; margin-bottom: 1em; text-align: center;">{title}</div>

<div style="margin-bottom: 2em;">
	<div><span style="font-weight: bold;"> Диапазон дат:</span> с {date_start} по {date_end}</div>
</div>

<div style="margin-bottom: 2em;">
	<div><span style="font-weight: bold;">Общее количество обращений:</span> {all_item}</div>
	<div><span style="font-weight: bold;">– Количество направленных пациентами:</span> {patientes}</div>
	<div><span style="font-weight: bold;">– Количество направленных организациями:</span> {org}</div>
	<div><span style="font-weight: bold;">– Количество направленных коллективами:</span> {com}</div>
	<div><span style="font-weight: bold;">– Количество направленных руководителями ЛПУ:</span> {glav_vrach}</div>
	<div><span style="font-weight: bold;">– Количество направленных заведующими отделениями:</span> {zav_otd}</div>
	<div><span style="font-weight: bold;">– Количество направленных врачами:</span> {vrach}</div>
	<div><span style="font-weight: bold;">– Количество направленных средними медработниками:</span> {sister}</div>
	<div><span style="font-weight: bold;">– Всего направлено медработниками:</span> {medpersonal}</div>
	<div><span style="font-weight: bold;">– Количество направленных прочими лицами:</span> {other}</div>
</div>

</body>

</html>