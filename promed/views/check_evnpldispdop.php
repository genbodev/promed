<html>
<head>
<title>Профилактические осмотры {CurYear}</title>
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
<center><h3>Профилактические осмотры {CurYear}</h3></center>
<table style="width: 100%; border: none; border-collapse: collapse;" cellspacing="0" cellpadding="2"><tbody>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td style="width: 20%;" rowspan="2">ФИО</td>
	<td style="width: 5%;" rowspan="2">ДР</td>
	<td style="width: 5%;" rowspan="2">Возраст</td>
	<td style="width: 10%;" rowspan="2">МО прикрепления</td>
	<td style="width: 5%;" rowspan="2">Участок</td>
	<td style="width: 25%;" colspan="4">Последнее пройденное проф мероприятие</td>
	<td style="width: 30%;" colspan="4">Предстоящее проф мероприятие</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<td>Тип</td>
	<td>Возрастная группа</td>
	<td>Дата начала</td>
	<td>Дата окончания</td>
	<td>План</td>
	<td>Тип</td>
	<td>Возрастная группа</td>
	<td>Дата начала</td>
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

{Persons}
<tr>

<td style="text-align: center;">{Person_Fio}</td>
<td style="text-align: center;">{Person_BirthDay}</td>
<td style="text-align: center;">{Person_Age}</td>
<td style="text-align: center;">{Lpu_Nick}</td>
<td style="text-align: center;">{LpuRegion_Name}</td>

<td style="text-align: center;">{DispClass_id}</td>
<td style="text-align: center;">{AgeGroupDisp_Name}</td>
<td style="text-align: center;">{EvnPLDisp_setDate}</td>
<td style="text-align: center;">{EvnPLDisp_disDate}</td>

<td style="text-align: center;">{pEvnPLDisp_plan}</td>
<td style="text-align: center;">{pDispClass_id}</td>
<td style="text-align: center;">{pAgeGroupDisp_Name}</td>
<td style="text-align: center;">{pEvnPLDisp_setDate}</td>

</tr>
{/Persons}

</tbody></table>

</body>

</html>