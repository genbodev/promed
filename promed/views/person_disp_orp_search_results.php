<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать регистра дополнительного диспансерного учета</title>
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
	<td style="width: 3%;">№ п/п</td>
	<td style="width: 8%;">Фамилия</td>
	<td style="width: 7%;">Имя</td>
	<td style="width: 9%;">Отчество</td>
	<td style="width: 5%;">Дата рождения</td>
	<td style="width: 5%;">Пол</td>
	<td style="width: 24%;">Адрес регистрации</td>
    <td style="width: 24%;">Адрес проживания</td>
	<td style="width: 10%;">В регистре др-го МО</td>
	<td style="width: 5%;">Карта диспансеризации</td>
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
</tr>

{search_results}
<tr>
<td style="text-align: right;">{Record_Num}</td>
<td>{Person_Surname}</td>
<td>{Person_Firname}</td>
<td>{Person_Secname}</td>
<td style="text-align: center;">{Person_Birthday}</td>
<td>{Sex_Name}</td>
<td>{ua_name}</td>
<td>{pa_name}</td>
<td>{OnDispInOtherLpu}</td>
<td>{ExistsDOPL}</td>
</tr>
{/search_results}

</tbody></table>

</body>

</html>