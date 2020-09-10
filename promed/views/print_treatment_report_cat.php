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
<H3>Общее количество обращений всех типов</H3>
{all_data}
	<div><span style="font-weight: bold;">– Количество обращений по вопросам {Name}:</span> {Number}</div>
{/all_data}
</div>

<div style="margin-bottom: 2em;">
<H3>Жалобы</H3>
{data4}
	<div><span style="font-weight: bold;">– Количество жалоб на {Name}:</span> {Number}</div>
{/data4}
</div>

<div style="margin-bottom: 2em;">
<H3>Благодарности</H3>
{data2}
	<div><span style="font-weight: bold;">– Количество благодарностей за {Name}:</span> {Number}</div>
{/data2}
</div>

<div style="margin-bottom: 2em;">
<H3>Предложения</H3>
{data1}
	<div><span style="font-weight: bold;">– Количество предложений по поводу {Name}:</span> {Number}</div>
{/data1}
</div>

<div style="margin-bottom: 2em;">
<H3>Заявления</H3>
{data3}
	<div><span style="font-weight: bold;">– Количество заявлений по поводу {Name}:</span> {Number}</div>
{/data3}
</div>

</body>

</html>