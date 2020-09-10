<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать списка рецептов</title>
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
	<td style="width: 12%;">Статус</td>
	<td style="width: 12%;">Подписан</td>
	<td style="width: 12%;">Тип</td>
	<td style="width: 12%;">Фамилия</td>
	<td style="width: 12%;">Имя</td>
	<td style="width: 12%;">Отчество</td>
	<td style="width: 10%;">Дата рождения</td>
	<td style="width: 10%;">СНИЛС</td>
	<td style="width: 10%;">МО выписки рецепта</td>
	<td style="width: 10%;">Форма рецепта</td>
	<td style="width: 6%;">Серия</td>
	<td style="width: 6%;">Номер</td>
	<td style="width: 11%;">Финансирование</td>
	<td style="width: 20%;">Врач</td>
	<td style="width: 16%;">МНН, выписанное в рецепте МО</td>
	<td style="width: 20%;">Торговое наименование отпущенное АУ</td>
	<td style="width: 5%;">кол-во уп., выписанных МО</td>
	<td style="width: 14%;">Аптека</td>
	<td style="width: 6%;">Дата выписки</td>
	<td style="width: 6%;">Действителен до</td>
	<td style="width: 6%;">Вкл. в заявку</td>
	<td style="width: 6%;">Дата обращения</td>
	<td style="width: 6%;">Дата отоваривания</td>
	<td style="width: 5%;">Срок обращения</td>
	<td style="width: 5%;">Отсрочка</td>
	<td style="width: 5%;">Срок отоваривания</td>
</tr>
<tr style="background-color: #eee; font-weight: bold; text-align: center;">
	<?php for($i=1; $i!=28; $i++) {
		echo "<td>".$i."</td>";
	} ?>
</tr>

{search_results}
<tr>
<td style="text-align: right;">{Record_Num}</td>
<td style="text-align: center;">{ReceptDelayType_Name}</td>
<td style="text-align: center;">{EvnRecept_IsSigned}</td>
<td style="text-align: center;">{ReceptType_Name}</td>
<td>{Person_Surname}</td>
<td>{Person_Firname}</td>
<td>{Person_Secname}</td>
<td style="text-align: center;">{Person_Birthday}</td>
<td style="text-align: center;">{Person_Snils}</td>
<td style="text-align: center;">{Lpu_Nick}</td>
<td style="text-align: center;">{ReceptForm_Code}</td>
<td style="text-align: center;">{EvnRecept_Ser}</td>
<td style="text-align: center;">{EvnRecept_Num}</td>
<td style="text-align: center;">{ReceptFinance_Name}</td>
<td style="text-align: center;">{MedPersonal_Fio}</td>
<td style="text-align: center;">{DrugMnn_Name}</td>
<td style="text-align: center;">{Drug_Name}</td>
<td style="text-align: center;">{EvnRecept_firKolvo}</td>
<td style="text-align: center;">{OrgFarmacy_Name}</td>
<td style="text-align: center;">{EvnRecept_setDate}</td>
<td style="text-align: center;">{EvnRecept_Godn}</td>
<td style="text-align: center;">{EvnRecept_InRequest}</td>
<td style="text-align: right;">{EvnRecept_obrDate}</td>
<td style="text-align: right;">{EvnRecept_otpDate}</td>
<td style="text-align: right;">{EvnRecept_obrDay}</td>
<td style="text-align: right;">{EvnRecept_otsDay}</td>
<td style="text-align: right;">{EvnRecept_otovDay}</td>
</tr>
{/search_results}

</tbody></table>

</body>

</html>