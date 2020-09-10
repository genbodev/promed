<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Справка учащегося: Печать</title>
<style type="text/css">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	table { border-collapse: collapse; }
	span, div, td { font-family: 'courier new', courier, 'Times New Roman', Times, serif; font-size: 9pt; }
	td { vertical-align: bottom; border: none; }
</style>

<style type="text/css" media="print">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	span, div, td { font-family: 'courier new', courier, 'Times New Roman', Times, serif; font-size: 9pt; }
	td { vertical-align: bottom; border: none; }
</style>
</head>

<body>

<div style="text-align: right; margin-bottom: 1em;">
	<div>Код формы по ОКУД ____________________</div>
	<div>Код учреждения по ОКПО _________________</div>
</div>

<table style="border: none; width: 100%;" cellspacing="0" cellpadding="2">
<tr>
	<td style="width: 50%; text-align: center; vertical-align: top;">
		<div>Министерство здравоохранения</div>
		<div>{MZRegion}</div>
		<div style="border-bottom: 1px solid #000;">{Lpu_Name}</div>
		<div>наименование учреждения</div>
	</td>
	<td style="width: 50%; text-align: center; vertical-align: top;">
		<div>Медицинская документация</div>
		<div>Форма N 095/у</div>
		<div>Утверждена Минздравом СССР</div>
		<div>04.10.80 г. N 1030</div>
	</td>
</tr>
</table>

<div style="text-align: center; font-weight: bold; margin-top: 1em;">
	<div>СПРАВКА</div>
	<div>о временной нетрудоспособности студента, учащегося техникума,</div>
	<div>профессионально-технического училища, о болезни,</div>
	<div>карантине и прочих причинах отсутствия ребенка,</div>
	<div>посещающего школу, детское дошкольное учреждение</div>
	<div>(нужное подчеркнуть)</div>
</div>

<div style="text-align: center; margin: 1em;">
	<div>Дата выдачи "{EvnStickStudent_setDay}" <span style="text-decoration: underline;">{EvnStickStudent_setMonth}</span> {EvnStickStudent_setYear} г.</div>
</div>

<div><span style="{StickRecipient_1}">Студенту</span>, <span style="{StickRecipient_2}">учащемуся</span>, <span style="{StickRecipient_3}">ребенку, посещающему  дошкольное учреждение</span></div>
<div>(нужное подчеркнуть)</div>

<div style="border-bottom: 1px solid #000;">{Org_Name_1}</div>
<div style="text-align: center;">название учебного заведения,</div>
<div style="border-bottom: 1px solid #000;">{Org_Name_2}</div>
<div style="text-align: center;">дошкольного учреждения</div>
<div>&nbsp;</div>
<table style="border: none; width:100%" cellspacing="0" cellpadding="2">
    <tr>
		<td style="width: 10%;">Фамилия, имя, отчество </td>
		<td style="width: 90%;  border-bottom: 1px solid #000; text-align: left;">{Person_Fio}</td>
	</tr>
</table>
<table style="border: none; width:100%" cellspacing="0" cellpadding="2">
    <tr>
        <td style="width: 25%;">Дата рождения (год, месяц, для детей до 1-го года - день) </td>
        <td style="width: 75%;  border-bottom: 1px solid #000; text-align: left;">{Person_Birthay}</td>
    </tr>
</table>
<table style="border: none; width:100%" cellspacing="0" cellpadding="2">
    <tr>
        <td style="width: 25%;">Диагноз заболевания (прочие причины отсутствия) </td>
        <td style="width: 75%;  border-bottom: 1px solid #000; text-align: left;">{StickCause_Name}</td>
    </tr>
</table>
<div>&nbsp;</div>
<div>Наличие контакта с инфекционными больными (<span style="{EvnStickStudent_IsContact_0}">нет</span>, <span style="{EvnStickStudent_IsContact_1}">да</span>, какими)</div>
<div style="border-bottom: 1px solid #000;">{EvnStickStudent_ContactDescr_1}</div>
<div style="text-align: center;">(подчеркнуть, вписать)</div>
<div style="border-bottom: 1px solid #000;">{EvnStickStudent_ContactDescr_2}</div>

<div>освобожден от занятий, посещений детского дошкольного учреждения</div>
<div style="border-bottom: 1px solid #000;">{Org_Name_1}</div>
<div style="border-bottom: 1px solid #000;">{Org_Name_2}</div>

<table style="border: none; width: 100%;" cellspacing="0" cellpadding="2">
<tr>
	<td style="width: 20%; text-align: right; padding-right: 0.5em;">с</td>
	<td style="width: 30%; text-align: center; border-bottom: 1px solid #000;">{EvnStickWorkRelease_begDate_1}</td>
	<td style="width: 5%; text-align: center;">по</td>
	<td style="width: 30%; text-align: center; border-bottom: 1px solid #000;">{EvnStickWorkRelease_endDate_1}</td>
	<td style="width: 15%;">&nbsp;</td>
</tr>
<tr>
	<td style="text-align: right; padding-right: 0.5em;">с</td>
	<td style="text-align: center; border-bottom: 1px solid #000;">{EvnStickWorkRelease_begDate_2}</td>
	<td style="text-align: center;">по</td>
	<td style="text-align: center; border-bottom: 1px solid #000;">{EvnStickWorkRelease_endDate_2}</td>
	<td>&nbsp;</td>
</tr>
</table>

<div style="margin: 3em 0em 1em 3em;">М.П.</div>
<div style="text-indent: 8em;">Подпись врача _______________ {MedPersonal_Fin}</div>

<script language="javascript">
	if ( "{mode}" != 'preview' ) {
		window.print();
	}
</script>

</body>

</html>