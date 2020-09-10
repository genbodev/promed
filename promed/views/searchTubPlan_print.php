<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать найденных в результате поиска запланированных прививок манту</title>
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
	<td style="width: 5%;">№ п/п</td>
	<td style="width: 10%;">Дата планирования</td>
	<td style="width: 10%;">Фамилия</td>
	<td style="width: 10%;">Имя</td>
	<td style="width: 10%;">Отчество</td>
	<td style="width: 10%;">Дата рождения</td>
	<td style="width: 10%;">Возраст</td>
	<td style="width: 10%;">Участок</td>
	<td style="width: 10%;">Адрес</td>
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
</tr>

{search_results}
<tr>
<td style="text-align: right;">{Record_Num}</td>
<td style="text-align: center;">{Date_Plan}</td>
<td>{SurName}</td>
<td>{FirName}</td>
<td>{SecName}</td>
<td style="text-align: center;">{BirthDay}</td>
<td style="text-align: center;">{Age}</td>
<td style="text-align: center;">{uch}</td>
<td>{Address}</td>
</tr>
{/search_results}

</tbody></table>

</body>

</html>