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
	<div><span style="font-weight: bold;"> Диапазон дат регистрации:</span> с {date_start} по {date_end}</div>
	<div><span style="font-weight: bold;"> ЛПУ:</span> {Lpu}</div>
	<div><span style="font-weight: bold;"> Способ получения обращения:</span> {TreatmentMethodDispatch}</div>
	<div><span style="font-weight: bold;"> Кратность обращения:</span> {TreatmentMultiplicity}</div>
	<div><span style="font-weight: bold;"> Тип обращения:</span> {TreatmentType}</div>
	<div><span style="font-weight: bold;"> Категория обращения:</span> {TreatmentCat}</div>
	<div><span style="font-weight: bold;"> Адресат обращения:</span> {TreatmentRecipientType}</div>
	<div><span style="font-weight: bold;"> Статус обращения:</span> {TreatmentReview}</div>
	<div><span style="font-weight: bold;"> Диапазон дат рассмотрения:</span> {dateReview}</div>
</div>

<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 30%;">ЛПУ</td>
	<td style="width: 40%;">Врач</td>
	<td style="width: 30%;">Количество обращений</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td>1</td>
	<td>2</td>
	<td>3</td>
</tr>
{data}
<tr style="text-align: left;">
	<td style="text-align: left;">{lpu}</td>
	<td style="text-align: left;">{name}</td>
	<td style="text-align: center;">{number}</td>
</tr>
{/data}
</tbody></table>

</body>

</html>