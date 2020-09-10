<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать списка найденных прикрепленных</title>
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
	<td style="width: 10%;">Фамилия</td>
	<td style="width: 10%;">Имя</td>
	<td style="width: 10%;">Отчество</td>
	<td style="width: 10%;">Дата рождения</td>
	<td style="width: 10%;">ЛПУ прикрепления</td>
	<td style="width: 10%;">БДЗ</td>
	<td style="width: 10%;">Фед. льгот.</td>
	<td style="width: 10%;">Отказ</td>
	<td style="width: 10%;">Рег. лгот</td>
	<td style="width: 5%;">7 ноз.</td>
	<td style="width: 20%;">Адрес регистрации</td>
	<td style="width: 20%;">Адрес проживания</td>
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
<td>{Person_Surname}</td>
<td>{Person_Firname}</td>
<td>{Person_Secname}</td>
<td style="text-align: center;">{Person_Birthday}</td>
<td>{Lpu_Nick}</td>
<td>{Person_IsBDZ}</td>
<td>{Person_IsFedLgot}</td>
<td>{Person_IsRefuse}</td>
<td>{Person_IsRegLgot}</td>
<td>{Person_Is7Noz}</td>
<td>{Person_UAddress}</td>
<td>{Person_PAddress}</td>
</tr>
{/search_results}

</tbody></table>

</body>

</html>