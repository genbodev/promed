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
	<div><span style="font-weight: bold;">– Количество первичных обращений:</span> {first}</div>
	<div><span style="font-weight: bold;">– Количество повторных обращений:</span> {doubl}</div>
</div>

</body>

</html>