<html>
<head>
<title>{statement_template_title}</title>
<style type="text/css">
body { margin: 0px; padding: 10px; }
table { border-collapse: collapse; }
span, div, td { font-family: verdana; font-size: 12px; }
.bottomText { text-align: center; margin-top: -3px; font-family: verdana; font-size: 10pt; }
</style>

<style type="text/css" media="print">
body { margin: 0px; padding: 5px; }
span, div, td { font-family: verdana; font-size: 10px; }
.bottomText { text-align: center; margin-top: -3px; font-family: verdana; font-size: 8pt; }
td { vertical-align: bottom; }
</style>
</head>
<body>
<!-- /*NO PARSE JSON*/ -->

<span>Форма заявления о выборе МО представителем пациента:</span>

<table width="100%">
<tr>
<td width="70%">
</td>
<td>
{Perm_Head}
</td>
</tr>
</table>

<table width="100%">
<tr>
<td width="50%">
</td>
<td>Главному врачу (руководителю)<br />
<p align="center" style="margin-bottom: -3px;"><span></span></p>
<hr />
<p align="center" style="margin-bottom: -3px;"><span style="font-size: 14px;">{Lpu_Name}</span></p>
<hr />
<p style="font-size:10pt;" class="bottomText">(наименование медицинской организации)</p>
</td>
</tr>
</table>

<h4 align="center">ЗАЯВЛЕНИЕ<br />
о выборе медицинской организации (врача)</h4>

<p style="margin-bottom: -3px;"><span style="font-size: 14px;">Я, {Deputy_Fio},</span></p>
<hr />
<p class="bottomText">(Ф.И.О.)</p>

<p style="margin-bottom: -3px;"><span style="font-size: 14px;">являясь родителем (законным представителем) {Person_FIO}</span></p>
<hr />
<p class="bottomText">(Ф.И.О. ребенка, гражданина,</p>

<p style="margin-bottom: -3px;"><span><br /></span></p>
<hr />
<p class="bottomText">признанного недееспособным)</p>
<!-- @task https://redmine.swan-it.ru/issues/194242 - добавлены поля свидетельства о рождении -->
<p style="margin-bottom: -3px;"><span>{DocumentType_Name}, {Document_Ser} {Document_Num}</span></p>
<hr />
<p class="bottomText">(реквизиты свидетельства о рождении/реквизиты документа, удостоверяющего</p>

<p style="margin-bottom: -3px;"><span><br /></span></p>
<hr />
<p class="bottomText">право законного представительства)</p>

<p style="margin-bottom: -3px;"><span style="font-size: 14px;">проживающий(-ая) по адресу: {Person_PAddress}</span></p>
<hr />
<p class="bottomText">(адрес постоянного места жительства)</p>

<p style="margin-bottom: -3px;"><span>застрахованного(-ой) по обязательному медицинскому страхованию в</span></p>
<hr />

<p style="margin-bottom: -3px; text-align:center"><span style="font-size: 14px;">{OrgSmo_Name},</span></p>
<hr />
<p class="bottomText">(наименование страховой медицинской организации)</p>


<p><span>полис обязательного медицинского страхования (временное свидетельство, подтверждающее оформление полиса обязательного медицинского страхования)</span></p>
<p style="margin-bottom: -3px; text-align: center"><span style="font-size: 14px;">{Polis_Ser} {Polis_Num}</span></p>
<hr />
<p class="bottomText">(серия, номер)</p>


<table border="1" width="100%" style="text-align:center">
<tr>
<td width="40%" style="font-size: 14px;">{Polis_begDate}</td>
<td width="10%">выдан</td>
<td width="45%" style="font-size: 14px;">{OrgSmo_Name}</td>
<td width="5%"></td>
</tr>
<tr>
<td><span style="font-size:10pt;">(дата выдачи)</span></td>
<td></td>
<td></td>
<td></td>
</tr>
</table>

<br />

			
<p><span>настоящим подтверждаю выбор</span></p>
<p style="margin-bottom: -3px; text-align: center"><span style="font-size: 14px;">{Lpu_Name}</span></p>
<hr />
<p class="bottomText">(наименование медицинской организации)</p>

<p><span>а также участкового врача</span></p>
<p style="margin-bottom: -3px; text-align: center"><span></span></p>
<hr />

<table border="1" width="60%" align="right" style="text-align:center">
<tr>
<td width="35%"><br /></td>
<td width="10%"></td>
<td width="55%"></td>
</tr>
<tr>
<td><font style="font-size:10pt;">(подпись)</font></td>
<td></td>
<td><font style="font-size:10pt;">(фамилия и инициалы)</font></td>
</tr>
</table>

<br />

<table border="1" width="70%" align="right" style="margin-top: 10px; text-align:center">
<tr>
<td width="15%"><br /></td>
<td width="10%"></td>
<td width="15%"></td>
<td width="25%"></td>
<td width="15%"><font style="font-size:10pt;">20</font></td>
<td width="5%"></td>
<td width="15%"></td>
</tr>
<tr>
<td></td>
<td></td>
<td></td>
<td><font style="font-size:10pt;">(дата)</font></td>
<td></td>
<td></td>
<td><font style="font-size:10pt;"><!--(фамилия и инициалы)--></font></td>
</tr>
</table>

<br />
<?php if(getRegionNumber()!='10') { ?>
	<table border="1" width="70%" align="right" style="margin-top: 10px; text-align:center">
	<tr>
	<td width="15%"><br /></td>
	<td width="10%"></td>
	<td width="15%"></td>
	<td width="25%"></td>
	<td width="15%"><font style="font-size:10pt;">20</font></td>
	<td width="5%"></td>
	<td width="15%"></td>
	</tr>
	<tr>
	<td></td>
	<td></td>
	<td></td>
	<td><font style="font-size:10pt;">(дата)</font></td>
	<td></td>
	<td></td>
	<td><font style="font-size:10pt;"><!--(фамилия и инициалы)--></font></td>
	</tr>
	</table>
<?php } ?>
</body>
</html>