<html>

<head>
	<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
	<meta name=ProgId content=Word.Document>
	<meta name=Generator content="Microsoft Word 11">
	<meta name=Originator content="Microsoft Word 11">
	<style>
		table {font-size:11pt; border-collapse: collapse;}
		.tit {font-family:Arial CYR; font-weight:bold; font-size:12pt; text-align:center}
		.bold {font-family:Arial CYR; font-weight:bold; font-size:12pt;}
		.border {border: solid 1px black;}
	</style>

	<title>Медкарта пациента - {Person_SurName}&nbsp;{Person_FirName}&nbsp;{Person_SecName}. Сторона А</title>

</head>
<body>
<!-- /*NO PARSE JSON*/ -->

<div style="text-align: right;">Медицинская документация. Форма № 025/у-04</div>

<table width="100%">
	<tr>
		<th width="70%"></th>
		<th width="10%"></th>
		<th width="20%"></th>
	</tr>
	<tr class="tit">
		<td>{Lpu_Name}</td>
		<td>ОГРН - </td>
		<td>{Lpu_OGRN}</td>
	</tr>
	<tr class="tit">
		<td colspan="2">Медицинская карта больного №</td>
		<td>{PersonCard_Code}</td>
	</tr>
</table>

<table width="100%" style="margin-top: 10px;">
	<tr>
		<th width="8%"></th>
		<th width="15%"></th>
		<th width="15%"></th>
		<th width="20%"></th>
		<th width="15%"></th>
		<th width="20%"></th>
		<th width="7%"></th>
	</tr>
	<tr>
		<td>СМО</td>
		<td class="border" colspan="3">{OrgSMO_Nick}</td>
		<td>Полис</td>
		<td class="border">{Polis_Ser}{Polis_Num}</td>
	</tr>
	<tr>
		<td>Льгота</td>
		<td class="border">{PrivilegeType_Code}</td>
		<td>СНИЛС</td>
		<td class="border" colspan="2">{Person_Snils}</td>
		<td>Пол</td>
		<td><u>{Sex_NameFull}</u></td>
	</tr>
</table>

<div class="bold" style="margin: 10px auto 5px; width: 100%">
	<div style="float: left; padding-top: 2px; width: 60%">
		{Person_SurName} {Person_FirName} {Person_SecName}, {Person_BirthDay}
	</div>
	<div style="overflow: hidden; height: 40px; width: 40%">
		<img style="float: left; padding-left: 10px;" src="/barcode/barcode_v501/barcode.php?s={barcode_string}" />
	</div>
	<div style="clear: both;"></div>
</div>

<table width="100%">
	<tr>
		<th width="10%"></th>
		<th width="40%"></th>
		<th width="10%"></th>
		<th width="40%"></th>
	</tr>
	<tr>
		<td>Участок</td>
		<td style="border-bottom: 1px solid black;">{LpuRegion_Name}</td>
		<td>Телефон</td>
		<td style="border-bottom: 1px solid black;">{Person_Phone}</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<td style="border-bottom: 1px dotted black;">
			<span style="padding-right: 5px;">Адрес регистрации:</span> {UAddress_Address}
		</td>
	</tr>
	<tr style="border-bottom: 1px solid black">
		<td>&nbsp;<td>
	</tr>
	<tr>
		<td style="border-bottom: 1px dotted black;">
			<span style="padding-right: 5px;">Адрес проживания:</span> {PAddress_Address}
		</td>
	</tr>
	<tr style="border-bottom: 1px solid black">
		<td>&nbsp;</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="17%"></th>
		<th width="10%"></th>
		<th width="10%"></th>
		<th width="15%"></th>
		<th width="48%"></th>
	</tr>
	<tr>
		<td colspan="5">
			Документы, удостоверяющие право на льготы (Наименование, №, серия, дата, кем выдан)
		</td>
	</tr>
	<tr>
		<td colspan="5" style="border-bottom: 1px solid black;">{EvnUdost} {EvnUdost_Date}</td>
	</tr>
	<tr>
		<td>Инвалидность - </td>
		<td class="border">{InvalidGroupType_Name}</td>
		<td>&nbsp;</td>
		<td>Иждивенец:</td>
		<td class="border">&nbsp;</td>
	</tr>
	<tr style="border-bottom: 1px solid black;">
		<td >Место работы:</td>
		<td colspan="4">{Job_Name}</td>
	</tr>
	<tr style="border-bottom: 1px solid black;">
		<td>профессия</td>
		<td colspan="2">&nbsp;</td>
		<td>должность</td>
		<td>{Post_Name}</td>
	</tr>
</table>

</body>

</html>