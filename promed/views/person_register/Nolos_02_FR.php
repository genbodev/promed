<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Форма N 02-ФР</title>
<style type="text/css">
	body { font-family: "Times New Roman", serif; font-size: 10pt; margin: 0; padding: 0; }
	span.fromBD { font-weight: bold; }
	p {margin: 0 0 .7em}
</style>
</head>

<body>

<p style="float: right; text-align: left; font-size: 8pt;">
	Приложение № 3<br />
	к приказу Министерства здравоохранения<br />
	Российской Федерации<br />
	от 15.02.2013 № 69н<br />
	&nbsp;<br />
	(в ред. Приказа Минздрава России<br />
	от 10.04.2015 № 181н)
</p>

<div style="clear: both"></div>

<p style="float: right; font-size: 10pt;">
	<b>Форма № 02-ФР</b>
</p>

<div style="margin-right: 55%">
	<p style="text-align: center; margin-bottom: 0;">
		Министерство здравоохранения<br />
		Российской Федерации<br />
	</p>
	<div style="text-align: center;">
		<span class='fromBD'>{Lpu_Name}</span>
		<hr size="1" style="margin: 0.5pt;">
		<span style="font-size:7pt;">(наименование медицинской организации)</span><br /><br />
		<span class='fromBD'>{Lpu_Adress}</span>
		<hr size="1" style="margin: 0.5pt;">
		<span style="font-size:7pt;">(адрес)</span><br /><br />
	</div>
	<div style="text-align: left;">
		<div style="float: left;">код медицинской<br> организации<br> по ОКПО, по ОГРН</div>
		<div style="float: left; padding: 12pt 7pt; border: 1px solid #000; margin-left: 10pt;"><span class='fromBD'>{Lpu_OGRN}</span></div>
	</div>
</div>

<div style="clear: both; height: 10pt;"></div>

<p style="text-align: center;">
	<span style="font-size:8pt; font-weight: bold;">
		Извещение № {EvnNotifyRegister_Num}<br />
		об исключении сведений о больном из Федерального регистра лиц, больных гемофилией, муковисцидозом,<br />
		гипофизарным нанизмом, болезнью Гоше, злокачественными новообразованиями лимфоидной, кроветворной<br />
		и родственных им тканей, рассеянным склерозом, лиц после трансплантации органов и (или) тканей<br />
	</span>
</p>


<br />
<br />

<p>1. Фамилия, имя, отчество больного, а также фамилия, данная при рождении: <span class='fromBD'><?php
echo mb_strtoupper($Person_SurName);
echo '&nbsp;';
if (!empty($Birth_SurName) && $Birth_SurName != $Person_SurName) {
	echo mb_strtoupper($Birth_SurName);
	echo '&nbsp;';
}
echo mb_strtoupper($Person_FirName);
echo '&nbsp;'; 
echo mb_strtoupper($Person_SecName);
?></span></p>
<p>2. Дата рождения: <span class='fromBD'>{Person_BirthDay}</span></p>
<p>3. Адрес места жительства (с указанием кода по Общероссийскому классификатору территорий муниципальных образований):<br> <?php
	if (!empty($Oktmo_Code)) {
		echo "<span class='fromBD'>{$Oktmo_Code}</span>";
	}
	?> <?php if (empty($Person_PAddress)) {
		echo '_______________________________________';
	} else {
		echo "<span class='fromBD'>{$Person_PAddress}</span>";
	}
?></p>
<p>4. Код заболевания по МКБ-10 <span class='fromBD'>{Diag_Code}</span></p>
<p>5. Документ, удостоверяющий личность: <br /><?php
	if (empty($DocumentType_Name)) {
		echo '____________________';
	} else {
		echo "<span class='fromBD'>{$DocumentType_Name}</span>";
	}
?> серия <?php
	if (empty($Document_Ser)) {
		echo '_______';
	} else {
		echo "<span class='fromBD'>{$Document_Ser}</span>";
	}
?> N <?php
	if (empty($Document_Num)) {
		echo '____________________';
	} else {
		echo "<span class='fromBD'>{$Document_Num}</span>";
	}
?><br />
Кем, когда выдан: <?php
	if (empty($OrgDep_Name)) {
		echo '_______________________________________';
	} else {
		echo "<span class='fromBD'>{$OrgDep_Name}</span>";
	}
?>, <?php
	if (empty($Document_begDate)) {
		echo '___________________';
	} else {
		echo "<span class='fromBD'>{$Document_begDate}</span>";
	}
?></p>
<p>6. Обоснование для исключения: <?php
	if (empty($PersonRegisterOutCause_Name)) {
		echo '__________________________________________________';
	} else {
		echo "<span class='fromBD'>{$PersonRegisterOutCause_Name}</span>";
	}
?><br /><?php
	if (empty($EvnNotifyRegister_Comment)) {
		echo '____________________________________________________________________________________';
	} else {
		echo "<span class='fromBD'>{$EvnNotifyRegister_Comment}</span>";
	}
?></p>
<p>Врач, выдавший извещение: <span class='fromBD'>{Doctor_Fio}</span>   _____________________(подпись)</p>
<p>Код врача: <?php
	if (empty($MedPersonal_Code)) {
		echo '________________';
	} else {
		echo "<span class='fromBD'>{$MedPersonal_Code}</span>";
	}
?>&nbsp;&nbsp;&nbsp;&nbsp;телефон: <?php
	if (empty($Doctor_Phone)) {
		echo '________________';
	} else {
		echo "<span class='fromBD'>{$Doctor_Phone}</span>";
	}
?>
<p>Заведующий отделением: ____________________________________________   _________________(подпись)</p>

<p>Председатель врачебной комиссии<br />
медицинской организации: <?php
	if (empty($Predsedatel_Fio)) {
		echo '_________________________________________';
	} else {
		echo "<span class='fromBD'>{$Predsedatel_Fio}</span>";
	}
?>  _________________(подпись)</p>
<p>Дата: <span class='fromBD'>{EvnNotifyRegister_setDate}</span><br /><br /><br /><br />
М.П.</p>

</body>
</html>