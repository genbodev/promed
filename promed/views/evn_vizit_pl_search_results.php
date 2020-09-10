<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать списка посещений</title>
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

<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 2%;">№ п/п</td>
	<td style="width: 3%;">№ талона</td>
	<td style="width: 9%;">Фамилия</td>
	<td style="width: 9%;">Имя</td>
	<td style="width: 9%;">Отчество</td>
	<td style="width: 8%;">Дата рождения</td>
	<td style="width: 15%;">Основной диагноз</td>
	<td style="width: 10%;">Отделение</td>
	<td style="width: 15%;">Врач</td>
	<td style="width: 5%;">Дата посещения</td>
	<td style="width: 5%;">Место обслуживания</td>
	<td style="width: 5%;">Цель посещения</td>
	<td style="width: 5%;">Вид оплаты</td>
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
</tr>

{search_results}
<tr>
<td style="text-align: right;">{Record_Num}</td>
<td style="text-align: center;">{EvnPL_NumCard}</td>
<td>{Person_Surname}</td>
<td>{Person_Firname}</td>
<td>{Person_Secname}</td>
<td style="text-align: center;">{Person_Birthday}</td>
<td>{Diag_Name}</td>
<td>{LpuSection_Name}</td>
<td>{MedPersonal_Fio}</td>
<td style="text-align: center;">{EvnVizitPL_setDate}</td>
<td>{ServiceType_Name}</td>
<td>{VizitType_Name}</td>
<td>{PayType_Name}</td>
</tr>
{/search_results}

</tbody></table>

</body>

</html>