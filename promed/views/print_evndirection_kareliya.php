<?php
	$is_perm = (isset($region_nick) && $region_nick == 'perm');
	$is_kareliya = (isset($region_nick) && $region_nick == 'kareliya');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Печать электронного направления</title>
	<style type="text/css">
		@page port { size: portrait }
		@page land { size: landscape }
		body { margin: 0px; padding: 0px; }
		table { border-collapse: collapse; }
		span, div, td { font-family: tahoma, verdana; font-size: 9px; }
		td { vertical-align: middle; border: 0px solid #000; }
		.style1 {font-size: 9px}
		.style2 {font-size: 9px}
	</style>

	<style type="text/css" media="print">
		@page port { size: portrait }
		@page land { size: landscape }
		body { margin: 0px; padding: 0px; }
		span, div, td { font-family: tahoma, verdana; font-size: 9px; }
		td { vertical-align: middle; border: 0px solid #ccc; }
		.style1 {font-size: 9px}
		.style2 {font-size: 9px}
	</style>
</head>

<body class="land">
	<div align='center'>
	<table width="100%" border='0'>
	<td>
	<tr>
	<td class=style2 style='width: 50%; text-align: center; vertical-align: top;'><small>Министерство здравоохранения и социального<br>&nbsp;&nbsp;&nbsp;&nbsp;pазвития Российской Федерации<br><br>
	<div style="border-bottom: 1px solid #000; text-align: center;">{Lpu_Name}</div>
	<center>(наименование лечебно-профилактического учреждения)</center>
	<div style="border-bottom: 1px solid #000; text-align: left">{Address_Address}</div>
	<div style="border-bottom: 1px solid #000; text-align: center;">{LpuUnit_Phone}</div>
	<center>(адрес, телефоны)</center>
	<div style="border-bottom: 1px solid #000; text-align: center;">{Lpu_OGRN}</div>
	<center>(ОГРН)</center>
	</td>
	<td class=style2 style='width: 50%; text-align: right; vertical-align: top; font-size:10px;'>
<?php
	if($is_perm)
	{
?>
	Приложение №2<br>
	к Порядку оказания медицинской помощи<br>
	на территории Пермского края<br>
	учреждениями здравоохранения независимо от формы собственности<br>
	в системе обязательного медицинского страхования<br>
	в том числе в условиях фондодержания,<br>
	оплаты по подушевым нормативам амбулаторно-поликлинической помощи<br>
	<center><span class="style1">
	Медицинская документация<br>
	&nbsp;&nbsp;&nbsp;&nbsp;Форма _________</span></center>
<?php
	}
	else
	{
		echo '&nbsp;';
	}
?>
	</td></tr>
	</table>
	<br>
	<table width="100%" border='0'>
	<td>
	<center>
	<b>НАПРАВЛЕНИЕ № {EvnDirection_Num}
	<br>
	<small>
	{dirstring}
	</b><br>
	(нужное подчеркнуть)
	</small>
	<div style="border-bottom: 1px solid #000;font-size: 10px;">{dLpu_Name}</div>
	<small><center>(наименование медицинского учреждения, куда направлен пациент)</center></small>
	<div style="border-bottom: 1px solid #000; text-align: left; font-weight: bold;">{dLpuUnit_Name} {LpuUnit_Address}</div>
	<div style="border-bottom: 1px solid #000; text-align: left;"><small>{Сontact_Phone}</small></div>
	</table>
	<table width="100%" border='0'>
	<tr>
	<td class="style1" style="width: 5%;">1.Профиль</td>
	<td style='width: 22%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{LpuSectionProfile_Name} </div></td>
	<td class="style1" style='width: 12%; text-align: right; vertical-align: top;'>{TType}</td>
	<td style='width: 33%; text-align: right; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000; font-weight: bold;">{RecMP}</div></td>
	<td class="style1" style='width: 13%; text-align: right; vertical-align: top;'>
	Дата и время приема</td>
	<td style='width: 15%; text-align: right; vertical-align: top; font-weight: bold;'>
	<div style="border-bottom: 1px solid #000;">{RecDate}</div></td>
	</tr>
	</table>

	<table width="100%" border='0'>
	<td class="style1" style='width: 20%; text-align: left; vertical-align: top;'>
	2.Номер страхового полиса ОМС</td>
	<td style='width: 20&; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Polis}&nbsp;</div></td>
	<td class="style1" style='width: 15%; text-align: right; vertical-align: top;'>
	3.Страховая компания</td>
	<td style='width: 45%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{OrgSmo_Nick}&nbsp;</div></td>
	</table>

	<table width="100%" border='0'>
	<td class="style1" style='width: 120; text-align: left; vertical-align: top;'>
	4.Тип госпитализации</td>
	<td style='width: 200; text-align: left; vertical-align: top;'>
	{hospstring}
	</td>
	<td class="style1" style='text-align: right; vertical-align: top;'>
	5.Код льготы</td>
	<td style='width: 10%; text-align: right; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">&nbsp;</div></td><tr></tr></td>
	</table>

	<table width="100%" border='0'>
	<td class="style1">
	6.Фамилия, имя, отчество</td>
	<td style='width: 84%; text-align: left; vertical-align: top; font-weight: bold;'>
	<div style="border-bottom: 1px solid #000;">{Person_FIO}</div></td><tr></tr></td>
	</table>

	<table width="100%" border='0'>
	<tr>
	<td class="style1" style="width: 13%">7.Дата рождения</td>
	<td style='width: 34%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000; font-weight: bold;">{Person_Birthdate}</div></td>
	<td class="style1" style='width: 14%; text-align: right; vertical-align: top;'>
	Контактный телефон</td>
	<td style='width: 39%; text-align: right; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000; font-weight: bold;">{Person_Phone}&nbsp;</div></td>
	</td>
	</tr>
	</table>

	<table width="100%" border='0'>
	<td class="style1">
	8.Адрес постоянного места жительства</td>
	<td style='width: 75%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Person_Address}</div></td><tr></tr></td>
	</table>

	<table width="100%" border='0'>
	<td class="style1">
	9.Место работы, должность</td>
	<td style='width: 80%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{JobPost}</div></td><tr></tr></td>
	</table>

	<?php if ($is_kareliya) { ?>
	<table width="100%" border='0'>
	<td class="style1">
	10.Код диагноза по МКБ</td>
	<td style='width: 84%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Diag_Code} {Diag_Name}</div></td><tr></tr></td>
	</table>
	<?php } else { ?>
	<table width="100%" border='0'>
	<td class="style1" style='width: 140; text-align: left; vertical-align: top;'>
	10.Код диагноза по МКБ</td>
	<td style='width: 80; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Diag_Code}</div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'></td>
	<td style='width: 80; text-align: left; vertical-align: top;'></td>
	</table>
	<?php } ?>

	<table width="100%" border='0'>
	<td class="style1" style='text-align: left; vertical-align: top;'>
	11.Обоснование направления</td>
	<td style='width: 80%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{EvnDirection_Descr}</div>
	<div style="border-bottom: 1px solid #000;">&nbsp;</div>
	</td>
	</table>

	<table width="100%" border='0'>
	<tr>
	<td class=style2 style='width: 400; text-align: left; vertical-align: top;'>Должность медицинского работника,<br>
	направившего больного <u>{MedDol}</u>
	<div style="border-bottom: 1px solid #000;font-size:10px; text-align: left;">{med_init}</div>
	ФИО&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;подпись
	</td>
	<td width='50'></td>
	<td class=style2 style='width: 400; text-align: left; vertical-align: top;'>
	Заведующий отделением
	<div style="border-bottom: 1px solid #000;font-size:10px; text-align: left;">{zav_init}</div>
	ФИО&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;подпись
	<br><br><center>
	<u>«{Dir_Day}»{Dir_Month} {Dir_Year}</u> г.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;МП
	</td></tr>
	</table>
	<br/>
	</div>
	</body>
	</html>