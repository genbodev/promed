<?php
	$is_perm = (isset($region_nick) && $region_nick == 'perm');
	$is_kareliya = (isset($region_nick) && $region_nick == 'kareliya');
	$is_penza = (isset($region_nick) && $region_nick == 'penza');
	$is_vologda = (isset($region_nick) && $region_nick == 'vologda');

	global $num; $num = 0;
	function setNum(){
		global $num;
		$num = $num +1;
		echo $num;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Печать электронного направления</title>
	<style type="text/css">
		@page land { size: landscape }
		@page port { size: portrait }
		@page { size: A4 landscape; }
		div.center { width:100%; }
		div.table:first-child { float:left; width:45%; padding:2%; }
		div.table:last-child { float:right; width:45%; padding:2%; padding-left: 3%; border-left:1px dashed gray;  }
		body { margin: 0px; padding: 0px; }
		table { border-collapse: collapse; }
		span, div, td { font-family: tahoma, verdana; font-size: 9px; }
		td { vertical-align: middle; border: 0px solid #000; }
		.style1 {font-size: 9px}
		.style2 {font-size: 9px}
		.page-break-before{ page-break-before: always; }
	</style>

	<style type="text/css" media="print">
		@page land { size: landscape }
		@page port { size: portrait }
		@page { size: A4 landscape; }
		div.center { width:100%; }
		div.table:first-child { float:left; width:46%; padding:0; padding-right: 3%; }
		div.table:last-child { float:right; width:46%; padding:0; padding-left: 4%; border-left:1px dashed gray;  }
		body { margin: 0px; padding: 0px;  }
		span, div, td { font-family: tahoma, verdana; font-size: 9px; }
		td { vertical-align: middle; border: 0px solid #ccc; }
		.style1 {font-size: 9px}
		.style2 {font-size: 9px}
		.page-break-before{ page-break-before: always; }
	</style>
</head>

<body class="land">

	<div align='center' class='center'>
		<div class='table'>
	<table width="100%" border='0'>
	<td>
	<tr>
	<td class=style2 style='width: 50%; text-align: center; vertical-align: top;'><small>Министерство здравоохранения и социального<br>&nbsp;&nbsp;&nbsp;&nbsp;pазвития Российской Федерации<br><br>
	<div style="border-bottom: 1px solid #000; text-align: center;">{Lpu_Name} <?php echo $is_perm?"($Lpu_f003mcod)":"";?></div>
	<center>(наименование лечебно-профилактического учреждения)</center>
	<div style="border-bottom: 1px solid #000; text-align: left">{Address_Address}</div>
	<div style="border-bottom: 1px solid #000; text-align: center;">{LpuUnit_Phone}</div>
	<center>(адрес, телефоны)</center>
	<div style="border-bottom: 1px solid #000; text-align: center;">{Lpu_OGRN}</div>
	<center>(ОГРН)</center>
	</td>
	<td class=style2 style='width: 50%; text-align: right; vertical-align: top; font-size:10px;'>
<?php
	if($is_perm && $DirType_id != 26)
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
	<?php if ($is_penza) { ?>
	<b>НАПРАВЛЕНИЕ № {Lpu_f003mcod}_{EvnDirection_Num}
	<?php } elseif($is_kareliya) { ?>
	<b>НАПРАВЛЕНИЕ № {Lpu_f003mcod}<?php echo date('Y');?>{EvnDirection_Num}
	<?php } elseif($is_vologda) { ?>
	<b>НАПРАВЛЕНИЕ № {Lpu_f003mcod_last4}{EvnDirection_Num}
	<?php } else { ?>
	<b>НАПРАВЛЕНИЕ № {EvnDirection_Num}
	<?php } ?>
	<br>
	<small>
	{dirstring}
	</b><br>
	<?php if($DirType_id != 26) { ?>(нужное подчеркнуть)<?php } ?>
	</small>

    <?php if (! $is_kareliya) { ?>

	<div style="border-bottom: 1px solid #000;font-size: 10px;">{dLpu_Name}</div>
	<small><center>(наименование <?php if($DirType_id == 26) { ?>организации<?php } else { ?>медицинского учреждения<?php } ?>, куда направлен пациент)</center></small>
	<div style="border-bottom: 1px solid #000; text-align: left;">{dLpuUnit_Name} {LpuUnit_Address}</div>
	<div style="border-bottom: 1px solid #000; text-align: left;"><small>{Сontact_Phone}</small></div>
	</table>
	<table width="100%" border='0'>
	<tr>
	<td class="style1"><?php setNum() ?>.Профиль</td>
	<td style='width: 300; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{LpuSectionProfile_Name} </div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'>{TType}</td>
	<td style='width: 330; text-align: right; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{RecMP}</div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'>
	<!--Дата и время приема-->&nbsp;</td>
	<td style='width: 120; text-align: right; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;"><!--{RecDate}-->&nbsp;</div></td>
	</tr>
	</table>
        <table width="100%" border='0'>
			<tr>
				<td class="style1"><?php setNum() ?>.Услуга</td>
				<td style=' width: 300;text-align: left; vertical-align: top;'>
					<?php
					foreach ($Usluga_Name as $value) { ?>
					<div style="border-bottom: 1px solid #000;"><?php echo $value;?></div>
					<?php } ?>
				</td>
				<td class="style1" style='text-align: right; vertical-align: top;'>Дата и время приема</td>
				<td style=' text-align: left; vertical-align: top;'>
					<div style=""><u>{RecDate}</u></div></td>
				<td class="style1" style='text-align: right; vertical-align: top;'>
					&nbsp;</td>
				<td style='width: 300; text-align: right; vertical-align: top;'>
					<div style="">&nbsp;</div></td>
			</tr>
        </table>

	<table width="100%" border='0'>
	<td class="style1" style='width: 200; text-align: left; vertical-align: top;'>
	<?php setNum() ?>.Номер страхового полиса ОМС</td>
	<td style='width: 200; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Polis}&nbsp;</div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'>
	<?php setNum() ?>.Страховая компания</td>
	<td style='width: 300; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{OrgSmo_Nick}&nbsp;</div></td>
	</table>

	<table width="100%" border='0'>
	<td class="style1" style='width: 120; text-align: left; vertical-align: top;'>
	<?php setNum() ?>.Тип госпитализации</td>
	<td style='width: 200; text-align: left; vertical-align: top;'>
	{hospstring}
	</td>
	<?php if ($is_penza) { ?>
		<td class="style1" style='width: 120; text-align: right; vertical-align: top;'>
		<?php setNum() ?>.Форма помощи: &nbsp;</td>
		<td style='width: 200; text-align: left; vertical-align: top;'>
		&nbsp;{MedicalCareFormType}
		</td>
	<?php } ?>
	<td class="style1" style='text-align: right; vertical-align: top;'>
	<?php setNum() ?>.Код льготы</td>
	<td style='width: 10%; text-align: right; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">&nbsp;</div></td><tr></tr></td>
	</table>

	<table width="100%" border='0'>
	<td class="style1">
	<?php setNum() ?>.Фамилия, имя, отчество</td>
	<td style='width: 86%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Person_FIO}</div></td><tr></tr></td>
	</table>

	<table width="100%" border='0'>
	<tr>
	<td class="style1"><?php setNum() ?>.Дата рождения</td>
	<td style='width: 500; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Person_Birthdate}</div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'>
	Контактный телефон</td>
	<td style='width: 200; text-align: right; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Person_Phone}&nbsp;</div></td>
	</td>
	</tr>
	</table>

	<table width="100%" border='0'>
	<td class="style1">
	<?php setNum() ?>.Адрес постоянного места жительства</td>
	<td style='width: 78%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Person_Address}</div></td><tr></tr></td>
	</table>

	<table width="100%" border='0'>
	<td class="style1">
	<?php setNum() ?>.Место работы, должность</td>
	<td style='width: 84%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{JobPost}</div></td><tr></tr></td>
	</table>

	<?php if (in_array($region_nick, array('astra', 'kareliya'))) { ?>
	<table width="100%" border='0'>
	<td class="style1">
	<?php setNum() ?>.Код диагноза по МКБ</td>
	<td style='width: 84%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Diag_Code} {Diag_Name}</div></td><tr></tr></td>
	</table>
	<?php } else { ?>
	<table width="100%" border='0'>
	<td class="style1" style='width: 140; text-align: left; vertical-align: top;'>
	<?php setNum() ?>.Код диагноза по МКБ</td>
	<td style='width: 80; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Diag_Code}</div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'></td>
	<td style='width: 80; text-align: left; vertical-align: top;'></td>
	</table>
	<?php } ?>

	<table width="100%" border='0'>
	<td class="style1" style='text-align: left; vertical-align: top;'>
	<?php setNum() ?>.Обоснование направления</td>
     <td style='width: 84%; text-align: left; vertical-align: top;'>
     <div style="border-bottom: 1px solid #000;">{EvnDirection_Descr}</div>
     <div style="border-bottom: 1px solid #000;">&nbsp;</div>
     </td>
    </table>


    <?php }
    if ($is_kareliya) { ?>
        <div style="border-bottom: 1px solid #000;font-size: 10px;">{dLpu_Name}</div>
        <small><center>(наименование медицинского учреждения, куда направлен пациент)</center></small>
        <div style="border-bottom: 1px solid #000; text-align: left; font-weight: bold;">{dLpuUnit_Name} {LpuUnit_Address}</div>
        <div style="border-bottom: 1px solid #000; text-align: left;"><small>{Сontact_Phone}</small></div>
        </table>
        <table width="100%" border='0'>
            <tr>
                <td class="style1" style="width: 5%"><?php setNum() ?>.Профиль</td>
                <td style='width: 40%; text-align: left; vertical-align: top;'>
                    <div style="border-bottom: 1px solid #000;">{LpuSectionProfile_Name} </div></td>
                <td class="style1" style='text-align: right; vertical-align: top; width: 7%;'>{TType}</td>
                <td style='width: 48%; text-align: right; vertical-align: top;'>
                    <div style="border-bottom: 1px solid #000; font-weight: bold;">{RecMP}</div></td>
                <!--	<td class="style1" style='width: 0%; text-align: right; vertical-align: top;'>-->
                <!--	<!--Дата и время приема--><!--&nbsp;</td>-->
                <!--	<td style='width: 0%; text-align: right; vertical-align: top; font-weight: bold;'>-->
                <!--	<div style="border-bottom: 1px solid #000;"><!--{RecDate}--><!--&nbsp;</div></td>-->
            </tr>
        </table>
        <table width="100%" border='0'>
            <tr>
                <td class="style1" style="width: 5%;"><?php setNum() ?>.Услуга</td>
                <td style=' width: 65%;text-align: left; vertical-align: top;'>
					<?php
					foreach ($Usluga_Name as $value) { ?>
                        <div style="border-bottom: 1px solid #000;"><?php echo $value;?></div>
					<?php } ?>
                </td>
                <td class="style1" style='width: 15%; text-align: right; vertical-align: top;'>Дата и время приема</td>
                <td style='width: 15%; text-align: left; vertical-align: top; font-weight: bold;'>
                    <div style=""><u>{RecDate}</u></div></td>
                <!--				<td class="style1" style='text-align: right; vertical-align: top;'>-->
                <!--					&nbsp;</td>-->
                <!--				<td style='width: 300; text-align: right; vertical-align: top;'>-->
                <!--					<div style="">&nbsp;</div></td>-->
            </tr>
        </table>

        <table width="100%" border='0'>
            <td class="style1" style='width: 20%;; text-align: left; vertical-align: top;'>
                <?php setNum() ?>.Номер страхового полиса ОМС</td>
            <td style='width: 15%; text-align: left; vertical-align: top;'>
                <div style="border-bottom: 1px solid #000;">{Polis}&nbsp;</div></td>
            <td class="style1" style='text-align: right; vertical-align: top;'>
                <?php setNum() ?>.Страховая компания</td>
            <td style='width: 45%; text-align: left; vertical-align: top;'>
                <div style="border-bottom: 1px solid #000;">{OrgSmo_Nick}&nbsp;</div></td>
        </table>

        <table width="100%" border='0'>
            <td class="style1" style='width: 120; text-align: left; vertical-align: top;'>
                <?php setNum() ?>.Тип госпитализации</td>
            <td style='width: 200; text-align: left; vertical-align: top;'>
                {hospstring}
            </td>
            <td class="style1" style='text-align: right; vertical-align: top;'>
                <?php setNum() ?>.Код льготы</td>
            <td style='width: 10%; text-align: right; vertical-align: top;'>
                <div style="border-bottom: 1px solid #000;">&nbsp;</div></td><tr></tr></td>
        </table>

        <table width="100%" border='0'>
            <td class="style1">
                <?php setNum() ?>.Фамилия, имя, отчество</td>
            <td style='width: 84%; text-align: left; vertical-align: top;'>
                <div style="border-bottom: 1px solid #000; font-weight: bold;">{Person_FIO}</div></td><tr></tr></td>
        </table>

        <table width="100%" border='0'>
            <tr>
                <td class="style1" style="width: 13%"><?php setNum() ?>.Дата рождения</td>
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
                <?php setNum() ?>.Адрес постоянного места жительства</td>
            <td style='width: 75%; text-align: left; vertical-align: top;'>
                <div style="border-bottom: 1px solid #000;">{Person_Address}</div></td><tr></tr></td>
        </table>

        <table width="100%" border='0'>
            <td class="style1">
                <?php setNum() ?>.Место работы, должность</td>
            <td style='width: 80%; text-align: left; vertical-align: top;'>
                <div style="border-bottom: 1px solid #000;">{JobPost}</div></td><tr></tr></td>
        </table>

		<?php if (in_array($region_nick, array('astra', 'kareliya'))) { ?>
            <table width="100%" border='0'>
                <td class="style1">
                    <?php setNum() ?>.Код диагноза по МКБ</td>
                <td style='width: 84%; text-align: left; vertical-align: top;'>
                    <div style="border-bottom: 1px solid #000;">{Diag_Code} {Diag_Name}</div></td><tr></tr></td>
            </table>
		<?php } else { ?>
            <table width="100%" border='0'>
                <td class="style1" style='width: 140; text-align: left; vertical-align: top;'>
                    <?php setNum() ?>.Код диагноза по МКБ</td>
                <td style='width: 80; text-align: left; vertical-align: top;'>
                    <div style="border-bottom: 1px solid #000;">{Diag_Code}</div></td>
                <td class="style1" style='text-align: right; vertical-align: top;'></td>
                <td style='width: 80; text-align: left; vertical-align: top;'></td>
            </table>
		<?php } ?>

        <table width="100%" border='0'>
            <td class="style1" style='text-align: left; vertical-align: top;'>
                <?php setNum() ?>.Обоснование направления</td>
            <td style='width: 80%; text-align: left; vertical-align: top;'>
                <div style="border-bottom: 1px solid #000;">{EvnDirection_Descr}&nbsp;</div>
            </td>
        </table>

    <?php } ?>

	<table width="100%" border='0'>
	<tr>
	<td class=style2 style='width: 400; text-align: left; vertical-align: top;'>Должность медицинского работника,<br>
	направившего больного <u>{PostMed_Name}</u>
	<div style="border-bottom: 1px solid #000;font-size:10px; text-align: left;">{med_init} <?php echo $is_perm?"($med_snils)":"";?></div>
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
	<br>
	<?php if ( !$is_kareliya ) { ?>
</div>
	<!--<hr style="border: none; border-top:1px dashed gray;" class='page-break-before'/>-->
<div class='table page-break-before'>
	<table width="100%" border='0'>
	<td>
	<tr>
	<td class=style2 style='width: 50%; text-align: center; vertical-align: top;'><small>Министерство здравоохранения и социального<br>&nbsp;&nbsp;&nbsp;&nbsp;pазвития Российской Федерации<br><br>
	<div style="border-bottom: 1px solid #000; text-align: center;">{Lpu_Name} <?php echo $is_perm?"($Lpu_f003mcod)":"";?></div>
	<center>(наименование лечебно-профилактического учреждения)</center>
	<div style="border-bottom: 1px solid #000; text-align: left">{Address_Address}</div>
	<div style="border-bottom: 1px solid #000; text-align: center;">{LpuUnit_Phone}</div>
	<center>(адрес, телефоны)</center>
	<div style="border-bottom: 1px solid #000; text-align: center;">{Lpu_OGRN}</div>
	<center>(ОГРН)</center>
	</td>
	<td class=style2 style='width: 50%; text-align: right; vertical-align: top; font-size:10px;'>
<?php
	$num = 0;
	if($is_perm && $DirType_id != 26)
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
	<?php if ($is_penza) { ?>
	<b>НАПРАВЛЕНИЕ № {Lpu_f003mcod}_{EvnDirection_Num}
	<?php } elseif($is_kareliya) { ?>
	<b>НАПРАВЛЕНИЕ № {Lpu_f003mcod}<?php echo date('Y');?>{EvnDirection_Num}
	<?php } elseif($is_vologda) { ?>
	<b>НАПРАВЛЕНИЕ № {Lpu_f003mcod_last4}{EvnDirection_Num}
	<?php } else { ?>
	<b>НАПРАВЛЕНИЕ № {EvnDirection_Num}
	<?php } ?>
	<br>
	<small>
	{dirstring}
	</b><br>
	<?php if($DirType_id != 26) { ?>(нужное подчеркнуть)<?php } ?>
	</small>
	<div style="border-bottom: 1px solid #000;font-size: 10px;">{dLpu_Name}</div>
	<small><center>(наименование <?php if($DirType_id == 26) { ?>организации<?php } else { ?>медицинского учреждения<?php } ?>, куда направлен пациент)</center></small>
	<div style="border-bottom: 1px solid #000; text-align: left;">{dLpuUnit_Name} {LpuUnit_Address}</div>
	<div style="border-bottom: 1px solid #000; text-align: left;"><small>{Сontact_Phone}</small></div>
	</table>
	<table width="100%" border='0'>
	<tr>
	<td class="style1"><?php setNum() ?>.Профиль</td>
	<td style='width: 300; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{LpuSectionProfile_Name} </div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'>{TType}</td>
	<td style='width: 330; text-align: right; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{RecMP}</div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'>
        <!--Дата и время приема-->&nbsp;</td>
        <td style='width: 120; text-align: right; vertical-align: top;'>
            <div style="border-bottom: 1px solid #000;"><!--{RecDate}-->&nbsp;</div></td>
	</tr>
	</table>
		<table width="100%" border='0'>
			<tr>
				<td class="style1"><?php setNum() ?>.Услуга</td>
				<td style=' width: 300;text-align: left; vertical-align: top;'>
					<?php
					foreach ($Usluga_Name as $value) { ?>
						<div style="border-bottom: 1px solid #000;"><?php echo $value;?></div>
					<?php } ?>
				</td>
				<td class="style1" style='text-align: right; vertical-align: top;'>Дата и время приема</td>
				<td style=' text-align: left; vertical-align: top;'>
					<div style=""><u>{RecDate}</u></div></td>
				<td class="style1" style='text-align: right; vertical-align: top;'>
					&nbsp;</td>
				<td style='width: 300; text-align: right; vertical-align: top;'>
					<div style="">&nbsp;</div></td>
			</tr>
		</table>
	<table width="100%" border='0'>
	<td class="style1" style='width: 200; text-align: left; vertical-align: top;'>
	<?php setNum() ?>.Номер страхового полиса ОМС</td>
	<td style='width: 200; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Polis}&nbsp;</div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'>
	<?php setNum() ?>.Страховая компания</td>
	<td style='width: 300; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{OrgSmo_Nick}&nbsp;</div></td>
	</table>

	<table width="100%" border='0'>
	<td class="style1" style='width: 120; text-align: left; vertical-align: top;'>
	<?php setNum() ?>.Тип госпитализации</td>
	<td style='width: 200; text-align: left; vertical-align: top;'>
	{hospstring}
	</td>
	<?php if ($is_penza) { ?>
		<td class="style1" style='width: 120; text-align: right; vertical-align: top;'>
		<?php setNum() ?>.Форма помощи: &nbsp;</td>
		<td style='width: 200; text-align: left; vertical-align: top;'>
		&nbsp;{MedicalCareFormType}
		</td>
	<?php } ?>
	<td class="style1" style='text-align: right; vertical-align: top;'>
	<?php setNum() ?>.Код льготы</td>
	<td style='width: 10%; text-align: right; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">&nbsp;</div></td><tr></tr></td>
	</table>

	<table width="100%" border='0'>
	<td class="style1">
	<?php setNum() ?>.Фамилия, имя, отчество</td>
	<td style='width: 86%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Person_FIO}</div></td><tr></tr></td>
	</table>

	<table width="100%" border='0'>
	<tr>
	<td class="style1"><?php setNum() ?>.Дата рождения</td>
	<td style='width: 500; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Person_Birthdate}</div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'>
	Контактный телефон</td>
	<td style='width: 200; text-align: right; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Person_Phone}&nbsp;</div></td>
	</td>
	</tr>
	</table>

	<table width="100%" border='0'>
	<td class="style1">
	<?php setNum() ?>.Адрес постоянного места жительства</td>
	<td style='width: 78%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Person_Address}</div></td><tr></tr></td>
	</table>

	<table width="100%" border='0'>
	<td class="style1">
	<?php setNum() ?>.Место работы, должность</td>
	<td style='width: 84%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{JobPost}</div></td><tr></tr></td>
	</table>

	<?php if (in_array($region_nick, array('astra', 'kareliya'))) { ?>
	<table width="100%" border='0'>
	<td class="style1">
	<?php setNum() ?>.Код диагноза по МКБ</td>
	<td style='width: 84%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Diag_Code} {Diag_Name}</div></td><tr></tr></td>
	</table>
	<?php } else { ?>
	<table width="100%" border='0'>
	<td class="style1" style='width: 140; text-align: left; vertical-align: top;'>
	<?php setNum() ?>.Код диагноза по МКБ</td>
	<td style='width: 80; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{Diag_Code}</div></td>
	<td class="style1" style='text-align: right; vertical-align: top;'></td>
	<td style='width: 80; text-align: left; vertical-align: top;'></td>
	</table>
	<?php } ?>

	<table width="100%" border='0'>
	<td class="style1" style='text-align: left; vertical-align: top;'>
	<?php setNum() ?>.Обоснование направления</td>
	<td style='width: 84%; text-align: left; vertical-align: top;'>
	<div style="border-bottom: 1px solid #000;">{EvnDirection_Descr}</div>
	<div style="border-bottom: 1px solid #000;">&nbsp;</div>
	</td>
	</table>

	<table width="100%" border='0'>
	<tr>
	<td class=style2 style='width: 400; text-align: left; vertical-align: top;'>Должность медицинского работника,<br>
	направившего больного <u>{PostMed_Name}</u>
	<div style="border-bottom: 1px solid #000;font-size:10px; text-align: left;">{med_init} <?php echo $is_perm?"($med_snils)":"";?></div>
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
	<?php } ?>
	</div>

	<?php if ($printResearches === true) {?>

	<style type="text/css">

		table.r-directions {

			width: 100%;
			font-size: 9px;;
		}

		table.r-directions tr {
		}

		table.r-directions td {

			vertical-align: middle;
			text-align: left;
			padding: 10px;
			width: 70%;
			border: 1px solid black;
		}

		table.r-directions td:first-child {

			text-align: center;
			width: 30%;
		}

		table.r-directions td.rd-header {

			text-align: center;
			font-weight: bold;
		}

		p.r-directions {

			width: 100%;
			text-align: center;
			padding: 10px;
			font-size: 9px;
			font-weight: bold;
			box-sizing: border-box;
		}

		.barcode-wrapper {

			padding-top: 20px;
			width: 100%;
			float:left;
			display: block
		}

		.r-directions-left-sided {

			float: right;
			display: block;
			clear: both;
		}

		.barcode-wrapper img {

			float: right;
			display: block;
			clear: both;
			width: 100px;
		}

		.r-directions-header {

			float: left;
			display: block;
			clear: both;
		}

		.r-direction-footer {

			padding-top: 20px;
			padding-bottom: 20px;
			text-align: right;
		}

		.r-direction-footer span {

			padding-left:50px;
		}

		.line {

			border-bottom:1px solid black;
			padding-right:100px;
		}
        .mnemonika-layout {
            width: 32%;
            display: inline-block;
            text-align: center;
        }

	</style>

	<?php
		if (isset($lab_services) && count($lab_services) > 0) {?>

        <div class="barcode-wrapper">
            <p class="r-directions-left-sided">Направление №{EvnDirection_Num}</p>
            <img src="/barcode/barcode_v501/barcode.php?s={EvnDirection_Num}&disableBarcodeText" />
            <p class="r-directions-header"><?php setNum() ?>. Исследования</p>
        </div>

        <?php
				foreach ($lab_services as $key => $lab_svc): ?>

                    <p class="r-directions"><?php echo $lab_svc['service_code']." ".$lab_svc['service_name']; ?></p>

                    <?php
                            if ( ! empty($lab_svc['subservices'])):

                                if ($lab_svc['mnemonika'] === true): ?>

                                    <?php foreach ($lab_svc['subservices'] as $subsvc): ?>
                                        <div class="mnemonika-layout">
                                            <?=$subsvc['AnalyzerTest_SysNick']?>
                                        </div>
                                    <?php endforeach;

                                else: ?>

                                        <table class="r-directions">
                                            <tr>
                                                <td class="rd-header">Код</td>
                                                <td class="rd-header">Наименование теста</td>
                                            </tr>

                                        <?php foreach ($lab_svc['subservices'] as $subsvc): ?>

                                            <tr>
                                                <td><?php echo $subsvc['Usluga_Code']  ?></td>
                                                <td><?php echo $subsvc['Usluga_Name']  ?></td>
                                            </tr>
                                        <?php endforeach; ?>

                                        </table>
                       <?php
                                endif;


                            else: ?>

                                    <table class="r-directions">
                                        <tr>
                                            <td class="rd-header">Код</td>
                                            <td class="rd-header">Наименование теста</td>
                                        </tr>
                                        <tr>
                                            <td><?php echo $lab_svc['service_code']  ?></td>
                                            <td><?php echo $lab_svc['service_name']  ?></td>
                                        </tr>
                                    </table>

                    <?php
                            endif;

                endforeach; ?>

			<div class="r-direction-footer" >
				<span>Врач-лаборант</span><span class="line"></span>
				<span>Дата</span><span class="line"></span>
			</div>

		<?php } } ?>
	</div>
	</body>
	</html>