<html>
<head>
<title>Печать списка протоколов ВК</title>
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
<p align="center">ЖУРНАЛ УЧЕТА<br />КЛИНИКО - ЭКСПЕРТНОЙ РАБОТЫ<br />ЛЕЧЕБНО - ПРОФИЛАКТИЧЕСКОГО УЧРЕЖДЕНИЯ<br />{year}г.</p>

<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 5%;">№ п/п</td>
	<td style="width: 8%;">Дата экспертизы</td>
	<td style="width: 10%;">ФИО пациента</td>
	<td style="width: 10%;">Дата рождения</td>
	<td style="width: 10%;">Диагноз</td>
	<td style="width: 10%;">Характеристика случая экспертизы</td>
	<td style="width: 10%;">Вид экспертизы</td>
	<td style="width: 10%;">Отклонение от стандартов</td>
	<td style="width: 10%;">Дефекты</td>
	<td style="width: 10%;">Достижение результата</td>
	<td style="width: 10%;">Направлениe на МСЭ</td>
	<td style="width: 10%;">Протокол МСЭ</td>
	<td style="width: 10%;">На контроле</td>
	<td style="width: 10%;">Зарезервировано</td>
	
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

{search_results}
<tr>
<td style="text-align: center;">{num}</td>
<td style="text-align: center;">{EvnVK_ExpertiseDate}</td>
<td style="text-align: center;">{Person_Fio}</td>
<td style="text-align: center;">{Person_BirthDay}</td>
<td style="text-align: center;">{Diag_Name}</td>
<td style="text-align: center;">{ExpertiseEventType_Name}</td>
<td style="text-align: center;">{ExpertiseNameType_Name}</td>
<td style="text-align: center;">{EvnVK_isAberration}</td>
<td style="text-align: center;">{EvnVK_isErrors}</td>
<td style="text-align: center;">{EvnVK_isResult}</td>
<td style="text-align: center;">{EvnVK_DirectionDate}</td>
<td style="text-align: center;">{EvnVK_ConclusionDate}</td>
<td style="text-align: center;">{EvnVK_isControl}</td>
<td style="text-align: center;">{EvnVK_isReserve}</td>
</tr>
{/search_results}

</tbody></table>

</body>

</html>