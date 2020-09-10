<html>

<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 11">
<meta name=Originator content="Microsoft Word 11">
<style>
table {font-size:10pt}
.tit {font-family:Arial CYR; font-weight:bold; font-size:12pt; text-align:center}
.podval {font-size:8pt}
</style>

<title>Медкарта пациента - {Person_SurName}&nbsp;{Person_FirName}&nbsp;{Person_SecName}</title>

</head>
<body>
<!-- /*NO PARSE JSON*/ -->
<table width="100%" border="0">
	<tr>
		<td colspan="2"><b>{Lpu_Name}</b></td>
		<td width="20%"></td>
		<td width="30%">Форма №025/у-04</td>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2"><b>ОГРН&nbsp;{Lpu_OGRN}</b></td>
		<td></td>
		<td colspan="3">Утверждена приказом Минздравсоцразвития</td>
	</tr>
	<tr>
		<td colspan="2"><b>{Address_Address}</b></td>
		<td></td>
		<td>от 22.11.2004 №255</td>
		<td colspan="2"></td>
	</tr>
	<tr class="tit">
		<td colspan="6" height="50">Медицинская карта амбулаторного больного №{PersonCard_Code}</td>
	</tr>
	<tr>
		<td width="14%">ФИО</td>
		<td><b><font style="font-size:16pt;">{Person_SurName}&nbsp;{Person_FirName}&nbsp;{Person_SecName}</font></b></td>
		<td></td>
		<td>Дата рождения&nbsp;&nbsp;<b>{Person_BirthDay}</b></td>
		<td>Пол&nbsp;&nbsp;<b>{Sex_Name}</b></td>
		<td width="4%"></td>
	</tr>
	<tr>
		<td></td>
		<td></td>
        <td></td>
        <td>Код льготы&nbsp;&nbsp;<b>{PrivilegeType_Code}</b></td>
	</tr>
	<tr>
		<td>СНИЛС</td>
		<td><b>{Person_Snils}</b></td>
		<td></td>
		<td>Инвалидность&nbsp;&nbsp;<b>{PrivilegeType_Name}</b></td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>СМО</td>
		<td><b>{OrgSMO_Nick}</b></td>
		<td></td>
		<td colspan="3">Полис ОМС&nbsp;<b>{Polis_Ser}&nbsp;{Polis_Num}</b></td>
	</tr>
	
	<tr>
		<td>Адрес регистрации</td>
		<td colspan="5"><b>{UAddress_Address}</b></td>
	</tr>
	<tr>
		<td>Адрес проживания</td>
		<td colspan="5"><b>{PAddress_Address}</b></td>
	</tr>
	<tr>
		<td>Телефон</td>
		<td colspan="5"><b>{Person_Phone}</b></td>
	</tr>
	<tr>
		<td>Документ</td>
		<td colspan="5"><b>{DocumentType_Name}&nbsp;{Document_Ser}&nbsp;{Document_Num}</b></td>
	</tr>
	<tr>
		<td>Место работы</td>
		<td colspan="5"><b>{Job_Name}</b></td>
	</tr>
	<!--tr>
		<td>Подразделение</td>
		<td colspan="5"><b>{OrgUnion_Name}</b></td>
	</tr-->
	<tr>
		<td>Профессия</td>
		<td colspan="5"><b>{Post_Name}</b></td>
	</tr>
	<tr>
		<td>Социальный статус</td>
		<td colspan="5"><b>{SocStatus_Name}</b></td>
	</tr>
	<tr>
		<td colspan="2">Пенсионное удостоверение</td>
		<td colspan="4"></td>
	</tr>
	<tr>
		<td colspan="2">Документ, удостоверяющий право на льготное обеспечение</td>
		<td colspan="4"><b>{EvnUdost}</b></td>
	</tr>
	<tr>
		<td colspan="2">Непереносимость лекарственных средств</td>
		<td colspan="4"></td>
	</tr>
	<tr class="podval" height="40" valign="bottom">
		<td colspan="3">___________________<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;дата</td>
		<td colspan="3" align="right">__________________<br />(подпись больного)&nbsp;&nbsp;</td>
	</tr>
</table>






</body>

</html>