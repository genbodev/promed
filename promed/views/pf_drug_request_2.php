<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать заявки: {print_type_name}</title>
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

<div style="font-size: 16pt; font-weight: bold; margin-bottom: 1em; text-align: center;">ЗАЯВКА</div>

<div style="margin-bottom: 2em;">
	<div><span style="font-weight: bold;">ЛПУ:</span> {lpu_name}</div>
	<div><span style="font-weight: bold;">Группа отделений:</span> {lpu_unit_name}</div>
	<div><span style="font-weight: bold;">Отделение:</span> {lpu_section_name}</div>
	<div><span style="font-weight: bold;">Врач:</span> {med_personal_fio}</div>
	<div><span style="font-weight: bold;">Период:</span> {drug_request_period_name}</div>
	<div><span style="font-weight: bold;">Тип заявки:</span> {drug_request_type_name}</div>
	<div><span style="font-weight: bold;">Вариант печати:</span> {print_type_name}</div>
</div>

<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 10%;">№ п/п</td>
	<td style="width: 70%;">ФИО пациента</td>
	<td style="width: 20%;">Сумма</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td>1</td>
	<td>2</td>
	<td>3</td>
</tr>

{drug_request_data}
<tr>
<td style="text-align: right;">{Record_Num}</td>
<td>{Person_Fio}</td>
<td style="text-align: right;">{DrugRequestRow_Summa}</td>
<tr>
{/drug_request_data}

<tr style="background-color: #eee; font-weight: bold;">
	<td colspan="2">РЕЗЕРВ</td>
	<td style="text-align: right;">{drug_request_reserve_total_sum}</td>
</tr>
<tr style="background-color: #eee; font-weight: bold;">
	<td colspan="2">ИТОГО</td>
	<td style="text-align: right;">{drug_request_total_sum}</td>
</tr>
</tbody></table>

</body>

</html>