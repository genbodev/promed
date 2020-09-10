<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать списка КВС</title>
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
<!-- /*NO PARSE JSON*/ -->
Отчёт сформирован: {date}
{search_results}
<h3 align="center" style="font-family: tahoma, verdana; font-size: 12pt;">{LpuSectionWard_Name}</h3>
<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 1%;" nowrap>№ п/п</td>
	<td style="width: 10%;">№ карты</td>
	<td style="width: 12%;">Фамилия</td>
	<td style="width: 12%;">Имя</td>
	<td style="width: 12%;">Отчество</td>
	<td style="width: 8%;">Дата рождения</td>
	<td style="width: 8%;">Поступление</td>
	<td style="width: 8%;">Выписка</td>
	<td style="width: 5%;">К/дни</td>
	<td style="width: 6%;">Вид оплаты</td>
	<td style="width: 3%;">Палата</td>
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
</tr>

{patients}
<tr>
<td style="text-align: right;">{Record_Num}</td>
<td style="text-align: center;">{EvnPS_NumCard}</td>
<td>{Person_Surname}</td>
<td>{Person_Firname}</td>
<td>{Person_Secname}</td>
<td style="text-align: center;">{Person_Birthday}</td>
<td style="text-align: center;">{EvnPS_setDate}</td>
<td style="text-align: center;">{EvnPS_disDate}</td>
<td style="text-align: right;">{EvnPS_KoikoDni}</td>
<td style="text-align: center;">{PayType_Name}</td>
<td style="text-align: right;">{LpuSectionWard_name}</td>
</tr>
{/patients}

</tbody></table>
{/search_results}

</body>

</html>