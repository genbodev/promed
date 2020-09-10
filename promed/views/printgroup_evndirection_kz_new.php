<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head><!--
    <title>Печать электронного направления</title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? 'utf-8' : 'windows-1251'); ?>">
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
    </style>-->	
</head>

<body class="land">
<div style="font-size: 1em; font-family: tahoma,arial,helvetica,sans-serif; text-align: center;">
<table width='100%' border='0'>
<td>
    <tr>
        <td style='width: 50%; text-align: center; vertical-align: top;'>
            <br>
            <div style="border-bottom: 1px solid #000; text-align: center; font-weight:bold;">{Lpu_Name}</div>
            <div style="font-size: 0.7em; text-align: center;">(наименование лечебно-профилактического учреждения)</div>
            <div style="border-bottom: 1px solid #000; text-align: left; font-size: 0.8em;">{Address_Address}</div>
            <div style="border-bottom: 1px solid #000; text-align: center; font-weight:bold;">{LpuUnit_Phone}</div>
            <div style="font-size: 0.7em; text-align: center;">(адрес, телефоны)</div>
            <div style="border-bottom: 1px solid #000; text-align: center; font-weight:bold;">{Lpu_OGRN}</div>
            <div style="font-size: 0.7em; text-align: center;">(ОГРН)</div>
        </td>
        <td style='width: 50%; text-align: right; vertical-align: top; font-size:10px;'>
			&nbsp;
		</td>
	</tr>
</table>
<br>
<table width='100%' border='0'>
	<tr>
		<td style='width: 100%; text-align: center; vertical-align: top;'>			
			<b>
				НАПРАВЛЕНИЕ № {EvnDirection_Num}
				<br>
				<span style="font-size: 0.8em;">{dirstring}</span>
			</b>
			<div style="font-size: 0.7em; text-align: center;">(нужное подчеркнуть)</div>
			
			<div style="border-bottom: 1px solid #000;font-size: 0.9em;">{dLpu_Name}</div>
			<div style="font-size: 0.7em; text-align: center;">(наименование медицинского учреждения, куда направлен пациент)</div>
			<div style="border-bottom: 1px solid #000; text-align: left;">{dLpuUnit_Name} {LpuUnit_Address}</div>
			<div style="border-bottom: 1px solid #000; text-align: left; font-size: 0.7em;">{Сontact_Phone}</div>
			
		</td>
	</tr>
</table>


<table class="table_namempdf" border=0>
	<tr>
		<td style='width: 200;'>1.Профиль</td>
		<td style='width: 300; padding-left: 5px;'>
			<div style="border-bottom: 1px solid #000;">{LpuSectionProfile_Name} </div>
		</td>
		<td>{TType}</td>
		<td style='width: 330; padding-left: 5px;'>
			<div style="border-bottom: 1px solid #000;">{RecMP}</div>
		</td>
		<td style='width: 120;'>
			Дата и время приема
		</td>
		<td style='padding-left: 5px;'>
			<div style="border-bottom: 1px solid #000;">{RecDate}</div>
		</td>
	</tr>
	<tr>
        <td>
            2.ИИН</td>
        <td colspan='2' style='padding-left: 5px;'>
            <div>{INN}&nbsp;</div></td>
        <td colspan='3'>
            &nbsp;
		</td>
	</tr>
	<tr>
        <td>
            4.Тип госпитализации
		</td>
        <td colspan='2'>
            {hospstring}
        </td>
        <td>
            5.Код льготы
		</td>
        <td colspan='2'>
            <div style="border-bottom: 1px solid #000;">&nbsp;</div>
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">
			6.Фамилия, имя, отчество
		</td>
		<td colspan='5' style='padding-left: 5px;'>
			<div style="border-bottom: 1px solid #000;">{Person_FIO}</div>
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">7.Дата рождения</td>
		<td colspan='2' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{Person_Birthdate}</div></td>
		<td style="text-align: left;">
			Контактный телефон
		</td>
		<td colspan='2' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{Person_Phone}&nbsp;</div></td>
		</td>
	</tr>
	<tr>
		<td colspan='2' style="text-align: left;">8.Адрес постоянного места жительства</td>
		<td colspan='4' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{Person_Address}</div>
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">9.Место работы, должность</td>
		<td colspan='5' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{JobPost}</div>
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">
			10.Код диагноза по МКБ
		</td>
		<td colspan='5' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{Diag_Code}</div>
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">
			11.Обоснование направления</td>
		<td colspan='5' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{EvnDirection_Descr}</div>
			<div style="border-bottom: 1px solid #000;">&nbsp;</div>
		</td>
	</tr>
</table>  

<table class="table_namempdf2" border='0'>
	<tr>
		<td colspan='2' style='width: 400; text-align: left; vertical-align: top;'>
			<div>Должность медицинского работника,<br>
			направившего больного <span>{MedDol}</span></div>
			<div style="border-bottom: 1px solid #000;font-size:10px; text-align: left;">{med_init}</div>
		</td>
		<td width='50'></td>
		<td colspan='2' style='width: 400; text-align: left; vertical-align: top;'>
			<div>Заведующий отделением</div>
			<div style="border-bottom: 1px solid #000;font-size:10px; text-align: left;">{zav_init}</div>
		</td>
	</tr>
	<tr>
		<td style='width: 200; text-align: left; vertical-align: top;'>
			ФИО
		</td>
		<td style='width: 200; text-align: left; vertical-align: top;'>
			подпись
		</td>
		<td width='50'></td>
		<td style='width: 200; text-align: left; vertical-align: top;'>
			ФИО
		</td>
		<td style='width: 200; text-align: left; vertical-align: top;'>
			подпись
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			&nbsp;
		</td>
		<td colspan='2' style='width: 200; text-align: left; vertical-align: center; text-align: center;'>
			<u>«{Dir_Day}»{Dir_Month} {Dir_Year}</u> г.&nbsp;&nbsp;&nbsp;&nbsp;МП
		</td>
	</tr>
</table>

<br/>
<hr style="border: none; border-top:1px dashed gray;"/>
<br/>

<table width='100%' border='0'>
<td>
    <tr>
        <td style='width: 50%; text-align: center; vertical-align: top;'>
            <br>
            <div style="border-bottom: 1px solid #000; text-align: center; font-weight:bold;">{Lpu_Name}</div>
            <div style="font-size: 0.7em; text-align: center;">(наименование лечебно-профилактического учреждения)</div>
            <div style="border-bottom: 1px solid #000; text-align: left; font-size: 0.8em;">{Address_Address}</div>
            <div style="border-bottom: 1px solid #000; text-align: center; font-weight:bold;">{LpuUnit_Phone}</div>
            <div style="font-size: 0.7em; text-align: center;">(адрес, телефоны)</div>
            <div style="border-bottom: 1px solid #000; text-align: center; font-weight:bold;">{Lpu_OGRN}</div>
            <div style="font-size: 0.7em; text-align: center;">(ОГРН)</div>
        </td>
        <td style='width: 50%; text-align: right; vertical-align: top; font-size:10px;'>
			&nbsp;
		</td>
	</tr>
</table>
<br>
<table width='100%' border='0'>
	<tr>
		<td style='width: 100%; text-align: center; vertical-align: top;'>			
			<b>
				НАПРАВЛЕНИЕ № {EvnDirection_Num}
				<br>
				<span style="font-size: 0.8em;">{dirstring}</span>
			</b>
			<div style="font-size: 0.7em; text-align: center;">(нужное подчеркнуть)</div>
			
			<div style="border-bottom: 1px solid #000;font-size: 0.9em;">{dLpu_Name}</div>
			<div style="font-size: 0.7em; text-align: center;">(наименование медицинского учреждения, куда направлен пациент)</div>
			<div style="border-bottom: 1px solid #000; text-align: left;">{dLpuUnit_Name} {LpuUnit_Address}</div>
			<div style="border-bottom: 1px solid #000; text-align: left; font-size: 0.7em;">{Сontact_Phone}</div>
			
		</td>
	</tr>
</table>


<table class="table_namempdf" border=0>
	<tr>
		<td style='width: 200;'>1.Профиль</td>
		<td style='width: 300; padding-left: 5px;'>
			<div style="border-bottom: 1px solid #000;">{LpuSectionProfile_Name} </div>
		</td>
		<td>{TType}</td>
		<td style='width: 330; padding-left: 5px;'>
			<div style="border-bottom: 1px solid #000;">{RecMP}</div>
		</td>
		<td style='width: 120;'>
			Дата и время приема
		</td>
		<td style='padding-left: 5px;'>
			<div style="border-bottom: 1px solid #000;">{RecDate}</div>
		</td>
	</tr>
	<tr>
        <td>
            2.ИИН</td>
        <td colspan='2' style='padding-left: 5px;'>
            <div>{INN}&nbsp;</div></td>
        <td colspan='3'>
            &nbsp;
		</td>
	</tr>
	<tr>
        <td>
            4.Тип госпитализации
		</td>
        <td colspan='2'>
            {hospstring}
        </td>
        <td>
            5.Код льготы
		</td>
        <td colspan='2'>
            <div style="border-bottom: 1px solid #000;">&nbsp;</div>
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">
			6.Фамилия, имя, отчество
		</td>
		<td colspan='5' style='padding-left: 5px;'>
			<div style="border-bottom: 1px solid #000;">{Person_FIO}</div>
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">7.Дата рождения</td>
		<td colspan='2' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{Person_Birthdate}</div></td>
		<td style="text-align: left;">
			Контактный телефон
		</td>
		<td colspan='2' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{Person_Phone}&nbsp;</div></td>
		</td>
	</tr>
	<tr>
		<td colspan='2' style="text-align: left;">8.Адрес постоянного места жительства</td>
		<td colspan='4' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{Person_Address}</div>
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">9.Место работы, должность</td>
		<td colspan='5' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{JobPost}</div>
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">
			10.Код диагноза по МКБ
		</td>
		<td colspan='5' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{Diag_Code}</div>
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">
			11.Обоснование направления</td>
		<td colspan='5' style="text-align: left;">
			<div style="border-bottom: 1px solid #000;">{EvnDirection_Descr}</div>
			<div style="border-bottom: 1px solid #000;">&nbsp;</div>
		</td>
	</tr>
</table>  

<table class="table_namempdf2" border='0'>
	<tr>
		<td colspan='2' style='width: 400; text-align: left; vertical-align: top;'>
			<div>Должность медицинского работника,<br>
			направившего больного <span>{MedDol}</span></div>
			<div style="border-bottom: 1px solid #000;font-size:10px; text-align: left;">{med_init}</div>
		</td>
		<td width='50'></td>
		<td colspan='2' style='width: 400; text-align: left; vertical-align: top;'>
			<div>Заведующий отделением</div>
			<div style="border-bottom: 1px solid #000;font-size:10px; text-align: left;">{zav_init}</div>
		</td>
	</tr>
	<tr>
		<td style='width: 200; text-align: left; vertical-align: top;'>
			ФИО
		</td>
		<td style='width: 200; text-align: left; vertical-align: top;'>
			подпись
		</td>
		<td width='50'></td>
		<td style='width: 200; text-align: left; vertical-align: top;'>
			ФИО
		</td>
		<td style='width: 200; text-align: left; vertical-align: top;'>
			подпись
		</td>
	</tr>
	<tr>
		<td colspan='3'>
			&nbsp;
		</td>
		<td colspan='2' style='width: 200; text-align: left; vertical-align: center; text-align: center;'>
			<u>«{Dir_Day}»{Dir_Month} {Dir_Year}</u> г.&nbsp;&nbsp;&nbsp;&nbsp;МП
		</td>
	</tr>
</table>



</div>
</body>
</html>