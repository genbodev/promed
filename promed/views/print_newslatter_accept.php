<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
<style>
body {Font-family:'Times New Roman', Times, serif; font-size: 12pt;}    
small {font-size: 11pt;}
hr {background: none; border: none; border-bottom: 2px solid #000; margin: 3px 0; height: 0;}
</style>

</head>
<body>

<div style="text-align: center"> 
{Lpu_Name} 
<br>
{UAddress_Address}
<hr>
<small>Наименование и адрес медицинской организации (оператор), получающей согласие субъекта персональных данных</small><br>
<br>
СОГЛАСИЕ<br>
на получение информации по каналам связи (СМС/e-mail рассылка)<br>
</div>
<br>
Фамилия, Имя, Отчество <u>{Person_SurName} {Person_FirName} {Person_SecName}</u><br>
Адрес 
<? if (!empty($UAddress_Name)) { ?> 
	<u>{UAddress_Name}</u>
<? } else { ?>
	__________________________________________________________________<br>________________________________________________________________________
<? } ?><br>
Документ, удостоверяющий личность 
<? if (!empty($DocumentType_Name)) { ?> 
	<u>{DocumentType_Name}</u>
<? } else { ?>
	______________________
<? } ?>
серия
<? if (!empty($Document_Ser)) { ?> 
	<u>{Document_Ser}</u>
<? } else { ?>
	______
<? } ?>
номер
<? if (!empty($Document_Num)) { ?> 
	<u>{Document_Num}</u>
<? } else { ?>
	_____________
<? } ?>
<br>
Кем и когда выдан
<? if (!empty($OrgDep_Name) && !empty($Document_begDate)) { ?> 
	<u>{Document_begDate}, {OrgDep_Name}</u>
<? } else { ?>
	________________________________________________________
<? } ?>
<br>
Номер телефона 
<? if (!empty($NewslatterAccept_Phone)) { ?> 
	<u>{NewslatterAccept_Phone}</u>
<? } else { ?>
	___________________________
<? } ?>
<br>
E-mail 
<? if (!empty($NewslatterAccept_Email)) { ?> 
	<u>{NewslatterAccept_Email}</u>
<? } else { ?>
	____________________________________
<? } ?>
<br>
<br>
Даю согласие на обработку моих персональных данных (фамилия, имя, отчество, номер телефона, e-mail) с целью получения информации по каналам связи: <br><br>

<div style="border: 1px solid #000; padding: 3px 10px; float: left;">{NewslatterAccept_IsSMS}</div>
<div style="float: left; margin: 4px 100px 0 10px">СМС рассылка</div>

<div style="border: 1px solid #000; padding: 3px 10px; float: left;">{NewslatterAccept_IsEmail}</div>
<div style="float: left; margin: 4px 100px 0 10px">e-mail рассылка</div>
		
<br><br>
Настоящее согласие действует без ограничения срока. Согласие может быть отозвано путем направления соответствующего заявления в адрес оператора. Указанный телефонный номер и e-mail будут исключены из рассылки не позднее 30 (тридцати) дней с момента получения оператором указанного заявления.<br>
<br>
Я подтверждаю, что являюсь владельцем указанного выше телефонного номера и электронного ящика.<br>

</body>
</html>