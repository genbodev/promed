<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать счета</title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: arial, tahoma, verdana; font-size: 10pt; }
td { vertical-align: middle; border: none; }
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 0px; }
span, div, td { font-family: arial, tahoma, verdana; font-size: 10pt; }
td { vertical-align: middle; border: none; }
</style>
</head>

<body class="portrait">

<div style="font-size: 16pt; font-weight: bold; margin-bottom: 0.5em; text-align: center;">СЧЕТ № {Registry_Num}</div>
<div style="font-size: 16pt; margin-bottom: 1em; text-align: center;">от {Registry_accDate} г. к реестру счетов № {Registry_Num} от {Registry_accDate} г.</div>

<table style="width: 100%; border: none;" cellspacing="0" cellpadding="2">
<tr><td style="width: 20%;">Поставщик</td><td style="width: 80%;">{Lpu_Name}</td></tr>
<tr><td>Адрес</td><td>{Lpu_Address}</td></tr>
<tr><td>Телефон</td><td>{Lpu_Phone}</td></tr>
<tr><td>Р/счет</td><td>{Lpu_Account}</td></tr>
<tr><td>Наименование банка</td><td>{LpuBank_Name}</td></tr>
<tr><td>БИК</td><td>{LpuBank_BIK}</td></tr>
<tr><td>ИНН</td><td>{Lpu_INN}</td></tr>
<tr><td>КПП</td><td>{Lpu_KPP}</td></tr>
<tr><td>Код по ОКВЭД</td><td>{Lpu_OKVED}</td></tr>
<tr><td>Код по ОКПО</td><td>{Lpu_OKPO}</td></tr>
<tr><td>Код по ОКТМО</td><td>{Lpu_OKTMO}</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>Плательщик</td><td>{OrgP_Name}</td></tr>
<tr><td>Адрес</td><td>{OrgP_Address}</td></tr>
<tr><td>Телефон</td><td>{OrgP_Phone}</td></tr>
<tr><td>Р/счет</td><td>{OrgP_RSchet}</td></tr>
<tr><td>Наименование банка</td><td>{OrgP_Bank}</td></tr>
<tr><td>БИК</td><td>{OrgP_BankBIK}</td></tr>
<tr><td>ИНН</td><td>{OrgP_INN}</td></tr>
<tr><td>КПП</td><td>{OrgP_KPP}</td></tr>
<tr><td>Код по ОКВЭД</td><td>{OrgP_OKVED}</td></tr>
<tr><td>Код по ОКПО</td><td>{OrgP_OKPO}</td></tr>
<tr><td>Код по ОКТМО</td><td>{OrgP_OKTMO}</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>Назначение платежа</td><td>За медицинские услуги по стационарной помощи, оказанные в {Month} {Year} года</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
</table>

<table style="width: 100%; border-collapse: collapse;" cellspacing="0" cellpadding="2">
<tr style="text-align: center;">
	<td style="width: 80%; border: 1px solid #000; padding: 1em 2em 1em 2em;">Наименование услуг</td>
	<td style="width: 20%; border: 1px solid #000; padding: 1em 2em 1em 2em;">Сумма (рублей)</td>
</tr>
<tr>
	<td style="border: 1px solid #000; padding: 1em; text-align: left;">За медицинские услуги по стационарной помощи, оказанные в {Month} {Year} года</td>
	<td style="border: 1px solid #000; padding: 1em; text-align: right;">{Registry_Sum}</td>
</tr>
</table>

<div style="padding: 2em 1em 1em 0em;">ИТОГО</div>

<div style="padding: 1em 1em 2em 0em;">{Registry_Sum_Words}</div>

<table style="width: 100%; border-collapse: collapse;" cellspacing="0" cellpadding="2">
<tr>
	<td style="width: 20%;">Руководитель</td>
	<td style="width: 30%; border-bottom: 1px solid #000;">&nbsp;</td>
	<td style="width: 50%; padding-left: 3em;">{Lpu_Director}&nbsp;</td>
</tr>
<tr><td colspan="3" style="padding: 1em;">&nbsp;</tr>
<tr>
	<td>Главный бухгалтер</td>
	<td style="border-bottom: 1px solid #000;">&nbsp;</td>
	<td style="padding-left: 3em;">{Lpu_GlavBuh}&nbsp;</td>
</tr>
</table>

</body>

</html>