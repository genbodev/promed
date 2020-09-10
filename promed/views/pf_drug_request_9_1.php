<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать заявки: {print_type_name}</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: tahoma, verdana; font-size: 8pt; }
td { vertical-align: middle; border: 1px solid #000; }
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: tahoma, verdana; font-size: 8pt; }
td { vertical-align: middle; border: 1px solid #ccc; }
</style>
</head>

<body class="land">

<div style="font-size: 16pt; font-weight: bold; margin-bottom: 1em; text-align: center;">ЗАЯВКА</div>

<div style="margin-bottom: 2em;">
	<div><span style="font-weight: bold;">ЛПУ:</span> {lpu_name}</div>
	<div><span style="font-weight: bold;">Период:</span> {drug_request_period_name}</div>
	<div><span style="font-weight: bold;">Тип заявки:</span> {drug_request_type_name}</div>
	<div><span style="font-weight: bold;">Вариант печати:</span> {print_type_name}</div>
</div>

<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 9%;" rowspan="2">ЛПУ</td>
	<td style="width: 7%;" rowspan="2">Кол-во пациентов в заявке</td>
	<td style="width: 7%;" rowspan="2">Лимит по кол-ву пациентов в заявке</td>
	<td style="width: 7%;" rowspan="2">Кол-во прикрепленных федеральных льготников (не отказников)</td>
	<td style="width: 7%;" rowspan="2">Лимит по кол-ву прикрепленных федеральных льготников</td>
	<td colspan="2">Заявлено врачами МУ</td>
	<td style="width: 7%;" rowspan="2">Заявлено другими МУ на прикрепленных пациентов, за исключением МЗ, онкодиспансера и онкогематологии</td>
	<td style="width: 7%;" rowspan="2">Всего заявлено на прикрепленных пациентов, за исключением МЗ, онкодиспансера и онкогематологии (гр. 6 + 7 + 8)</td>
	<td style="width: 7%;" rowspan="2">Превышение лимита (гр. 9/5), %</td>
	<td style="width: 7%;" rowspan="2">Заявлено МЗ на прикрепленных пациентов</td>
	<td style="width: 7%;" rowspan="2">Заявлено онкодиспансером на прикрепленных пациентов</td>
	<td style="width: 7%;" rowspan="2">Заявлено онкогематологией на прикрепленных пациентов</td>
	<td style="width: 7%;" rowspan="2">Всего заявлено на прикрепленных пациентов (гр. 9 + 11 + 12 + 13)</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 7%;">на прикрепленных пациентов</td>
	<td style="width: 7%;">в резерв</td>
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
</tr>

{drug_request_data}
<tr style="text-align: right;">
	<td style="text-align: left;">{Lpu_Name}</td>
	<td>{Request_PersonCount}</td>
	<td>{Request_SummaLimit}</td>
	<td>{Attach_PersonCount}</td>
	<td>{Attach_SummaLimit}</td>
	<td>{Attach_SummaPerson}</td>
	<td>{Attach_SummaReserve}</td>
	<td>{Attach_SummaOtherLpu}</td>
	<td>{Attach_SummaTotal1}</td>
	<td>{Attach_LimitOverflow1}</td>
	<td>{Attach_SummaMinZdrav}</td>
	<td>{Attach_SummaOnkoDisp}</td>
	<td>{Attach_SummaOnkoGemat}</td>
	<td>{Attach_SummaTotal2}</td>
</tr>
{/drug_request_data}

</tbody></table>

</body>

</html>