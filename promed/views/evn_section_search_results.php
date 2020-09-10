<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать списка движений</title>
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
<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td>№ п/п</td>
	<td>№ карты</td>
	<td>Палата</td>
	<td>Фамилия</td>
	<td>Имя</td>
	<td>Отчество</td>
	<td>Дата рождения</td>
	<td>Поступление</td>
	<td>Выписка</td>
	<td>Отделение</td>
	<td>Диагноз</td>
	<td>Врач</td>
	<td>К/дни</td>
	<td><?php echo getMESAlias(); ?></td>
	<td>Вид оплаты</td>
	<td>Исход</td>
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
	<td>16</td>
</tr>

{search_results}
<tr>
<td style="text-align: right;">{Record_Num}</td>
<td style="text-align: center;">{EvnPS_NumCard}</td>
<td>{LpuSectionWard_Name}</td>
<td>{Person_Surname}</td>
<td>{Person_Firname}</td>
<td>{Person_Secname}</td>
<td style="text-align: center;">{Person_Birthday}</td>
<td style="text-align: center;">{EvnSection_setDate}</td>
<td style="text-align: center;">{EvnSection_disDate}</td>
<td>{LpuSection_Name}</td>
<td>{Diag_Name}</td>
<td>{MedPersonal_Fio}</td>
<td style="text-align: right;">{EvnSection_KoikoDni}</td>
<td>{Mes_Code}</td>
<td>{PayType_Name}</td>
<td>{LeaveType_Name}</td>
</tr>
{/search_results}

</tbody></table>

</body>

</html>