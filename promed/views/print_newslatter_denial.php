<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<style>
body {Font-family:'Times New Roman', Times, serif; font-size: 12pt;}    
small {font-size: 11pt;}
hr {background: none; border: none; border-bottom: 2px solid #000; margin: 3px 0; height: 0;}
td {padding-left: 5px; vertical-align: top;}
</style>

</head>
<body>

<div style="text-align: center"> 
{Lpu_Name} 
<br>
{UAddress_Address}
<hr>
<small>Наименование и адрес медицинской организации (оператор рассылки), получающей согласие субъекта персональных данных</small><br>
<br>
</div>


<table style="float: right; width: 450px;">
	<tr>
		<td style="text-align: right; width: 170px;">ФИО</td>
		<td><u>{Person_SurName} {Person_FirName} {Person_SecName}</u></td>
	</tr>
	<tr>
		<td style="text-align: right; width: 170px;">Проживающего по адресу</td>
		<td><u>{UAddress_Name}</u></td>
	</tr>
	<tr>
		<td style="text-align: right; width: 170px;">Паспорт (серия, номер, кем и когда выдан)</td>
		<td><u>{Document_Ser} {Document_Num} выдан {Document_begDate}, {OrgDep_Name}</u></td>
	</tr>
<table>

<div style="clear: both;"></div>
<br>

Заявление на отказ  в получении информации по каналам связи (СМС/e-mail рассылки )<br>
<br>
Прошу исключить мой номер телефона
<? if (!empty($NewslatterAccept_Phone)) { ?> 
	<u>{NewslatterAccept_Phone}</u>
<? } else { ?>
	________________
<? } ?>
мой e-mail
 <? if (!empty($NewslatterAccept_Email)) { ?> 
	<u>{NewslatterAccept_Email}</u>
<? } else { ?>
	_______________________
<? } ?> 
из списка для получения информационных рассылок. <br>
<br>
<div style="text-align: center"> 
________________________________________________________________________<br>
(дата, подпись, расшифровка)<br>
</div>


</body>
</html>