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
	<td style="width: 5%;" rowspan="2">№ п/п</td>
	<td colspan="8">Заявлено: Потребность в лекарственных средствах на указанный период</td>
	<td colspan="6">Выписано</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 8%;">ЛПУ</td>
	<td style="width: 8%;">Отделение</td>
	<td style="width: 8%;">Врач</td>
	<td style="width: 5%;">Код</td>
	<td style="width: 11%;">Медикамент</td>
	<td style="width: 5%;">Кол-во</td>
	<td style="width: 5%;">Цена</td>
	<td style="width: 5%;">Сумма</td>
	<td style="width: 9%;">ЛПУ</td>
	<td style="width: 8%;">Отделение</td>
	<td style="width: 8%;">Врач</td>
	<td style="width: 5%;">Кол-во</td>
	<td style="width: 5%;">Цена</td>
	<td style="width: 5%;">Сумма</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td>1</td>
	<td>2</td>
	<td>3</td>
	<td>4</td>
	<td>5</td>
	<td>6</td>
	<td>7</td>
	<td>8</td>
	<td>9</td>
	<td>10</td>
	<td>11</td>
	<td>12</td>
	<td>13</td>
	<td>14</td>
	<td>15</td>
</tr>

{drug_request_data}
<tr><td colspan="15" style="font-weight: bold;">
	<div>{Person_Fio}, {Person_Birthday}, {UAddress_Name}</div>
</td></tr>
{drug_list}
<tr>
<td style="text-align: right;">{Record_Num}</td>
<td>{Lpu_Nick}</td>
<td>{LpuSection_Name}</td>
<td>{MedPersonal_Fio}</td>
<td style="text-align: right;">{DrugRequestRow_Code}</td>
<td>{DrugRequestRow_Name}</td>
<td style="text-align: right;">{DrugRequestRow_Kolvo}</td>
<td style="text-align: right;">{DrugRequestRow_Price}</td>
<td style="text-align: right;">{DrugRequestRow_Summa}</td>
<td>{ER_Lpu_Nick}</td>
<td>{ER_LpuSection_Name}</td>
<td>{ER_MedPersonal_Fio}</td>
<td style="text-align: right;">{EvnRecept_Kolvo}</td>
<td style="text-align: right;">{Drug_Price}</td>
<td style="text-align: right;">{EvnRecept_Summa}</td>
<tr>
{/drug_list}
<tr style="background-color: #eee; font-weight: bold;">
	<td colspan="3">ИТОГО по пациенту</td>
	<td colspan="6" style="text-align: right;">{PersonDrugRequest_Sum}</td>
	<td colspan="6" style="text-align: right;">{PersonEvnRecept_Sum}</td>
</tr>
{/drug_request_data}

<tr style="background-color: #eee; font-weight: bold;">
	<td colspan="3">ИТОГО по заявке</td>
	<td colspan="6" style="text-align: right;">{drug_request_total_sum}</td>
	<td colspan="6" style="text-align: right;">{evn_recept_total_sum}</td>
</tr>
</tbody></table>

</body>

</html>