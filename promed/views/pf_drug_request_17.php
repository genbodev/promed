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

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2"><tbody>
<tr>
	<td style="width: 40%; border: none;">
		<div>Согласовано:</div>
		<div>Руководитель территориального органа</div>
		<div>управления здравоохранением</div>
		<div>____________________________________</div>
		<div>"____" ____________________ 20___ г.</div>
	</td>
	<td style="width: 20%; border: none;">
		&nbsp;
	</td>
	<td style="width: 40%; border: none;">
		<div>Согласовано:</div>
		<div>Руководитель фармацевтической</div>
		<div>организации</div>
		<div>____________________________________</div>
		<div>"____" ____________________ 20___ г.</div>
	</td>
</tr>
</table>

<div style="font-weight: bold; margin-top: 1em; margin-bottom: 1em; text-align: center;">
	<div style="font-size: 12pt;">Заявка для обеспечения необходимыми лекарственными средствами {privilege_type_name} льготополучателей Пермского края </div>
	<div style="font-size: 12pt; font-style: italic; margin-top: 1em;">{lpu_name} (сводная {drug_request_type_name})</div>
	<div style="font-weight: normal;">(наименование субъекта РФ)</div>
	<div style="font-size: 12pt; margin-top: 1em;">{drug_request_period_name}</div>
</div>

<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 6%;" rowspan="2">№ п/п</td>
	<td style="width: 10%;" rowspan="2">Код</td>
	<td style="width: 40%;" rowspan="2">Медикамент</td>
	<td colspan="3">Потребность в лекарственных средствах на указанный период</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 15%;">Кол-во</td>
	<td style="width: 15%;">Цена</td>
	<td style="width: 15%;">Сумма</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td>1</td>
	<td>2</td>
	<td>3</td>
	<td>4</td>
	<td>5</td>
	<td>6</td>
</tr>

{drug_request_data}
<tr>
<td style="text-align: right;">{Record_Num}</td>
<td style="text-align: right;">{DrugRequestRow_Code}</td>
<td>{DrugRequestRow_Name}</td>
<td style="text-align: right;">{DrugRequestRow_Kolvo}</td>
<td style="text-align: right;">{DrugRequestRow_Price}</td>
<td style="text-align: right;">{DrugRequestRow_Summa}</td>
<tr>
{/drug_request_data}

<tr style="background-color: #eee; font-weight: bold;">
	<td colspan="5">ИТОГО</td>
	<td style="text-align: right;">{drug_request_total_sum}</td>
</tr>
</tbody></table>

<div style="margin-top: 2em;">Главный врач</div>

</body>

</html>