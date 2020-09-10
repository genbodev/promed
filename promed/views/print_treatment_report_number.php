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
	<div><span style="font-weight: bold;">- Из них предложений:</span> {number_1}</div>
	<div><span style="font-weight: bold;">- Из них благодарностей:</span> {number_2}</div>
	<div><span style="font-weight: bold;">- Из них заявлений:</span> {number_3}</div>
	<div><span style="font-weight: bold;">– Из них жалоб:</span> {number_4}</div>
</div>

</body>

</html>