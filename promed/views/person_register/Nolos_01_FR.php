<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Форма N 01-ФР</title>
<style type="text/css">
	body { font-family: "Times New Roman", serif; font-size: 10pt; margin: 0; padding: 0; }
	span.fromBD { font-weight: bold; }
	.newpage {page-break-before: always;}
	p {margin: 0 0 .6em}
</style>
</head>

<body>

<p style="float: right; text-align: left; font-size: 8pt;">
	Приложение № 2<br />
	к приказу Министерства здравоохранения<br />
	Российской Федерации<br />
	от 15.02.2013 № 69н<br />
	&nbsp;<br />
	(в ред. Приказа Минздрава России<br />
	от 10.04.2015 № 181н)
</p>

<div style="clear: both"></div>

<p style="float: right; font-size: 10pt;">
	<b>Форма № 01-ФР</b>
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
		Направление № {EvnNotifyRegister_Num}<br />
		на включение сведений (внесение изменений в сведения) о больном в Федеральный регистр лиц, больных гемофилией,<br />
		муковисцидозом, гипофизарным нанизмом, болезнью Гоше, злокачественными новообразованиями лимфоидной,<br />
		кроветворной и родственных им тканей, рассеянным склерозом, лиц после трансплантации органов<br />
		и (или) тканей
	</span>
</p>

<p>1. Серия и номер полиса ОМС&nbsp;&nbsp;&nbsp;<?php
	if (!empty($Polis_Ser)) {
		echo "<span class='fromBD'>{$Polis_Ser}</span>";
	}
?> <?php
	if (empty($Polis_Num)) {
		echo '___________________________________________';
	} else {
		echo "<span class='fromBD'>{$Polis_Num}</span>";
	}
?></p>
<p>2. Фамилия, имя, отчество, а также фамилия, данная при рождении: <span class='fromBD'><?php
echo mb_strtoupper($Person_SurName);
echo '&nbsp;';
if (!empty($Birth_SurName) && $Birth_SurName != $Person_SurName) {
	echo mb_strtoupper("({$Birth_SurName})");
	echo '&nbsp;';
}
echo mb_strtoupper($Person_FirName);
echo '&nbsp;'; 
echo mb_strtoupper($Person_SecName);
?></span></p>
<p>2.б. Наименование страховой медицинской организации, выдавшей полис ОМС: <?php
	if (empty($OrgSmo_Name)) {
		echo '__________________________________________';
	} else {
		echo "<span class='fromBD'>{$OrgSmo_Name}</span>";
	}
?></p>
<p>3. Дата рождения: <span class='fromBD'>{Person_BirthDay}</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4. Пол <span class='fromBD'>{Sex_Nick}</span></p>
<p>5. Адрес места жительства (с указанием кода по Общероссийскому классификатору территорий муниципальных образований): <?php
	if (!empty($Oktmo_Code)) {
		echo "<span class='fromBD'>{$Oktmo_Code}</span>";
	}
	?> <?php
	if (empty($Person_PAddress)) {
		echo '_______________________________________';
	} else {
		echo "<span class='fromBD'>{$Person_PAddress}</span>";
	}
?></p>
<p>6. Место работы, должность (профессия): <?php 
	if (empty($Person_Job)) {
		echo '__________________________';
	} else {
		echo "<span class='fromBD'>{$Person_Job}</span>";
	}
?> <?php
	if (empty($Person_Post)) {
		echo '___________________________';
	} else {
		echo ", <span class='fromBD'>{$Person_Post}</span>";
	}
?></p>
<div style="float: left;">
	7. Код заболевания по МКБ-10 <span class='fromBD'>{Diag_Code}</span>
</div>
<p style="margin-left: 55%;">8. Документ, удостоверяющий личность: <br /><?php
	if (empty($DocumentType_Name)) {
		echo '____________________';
	} else {
		echo "<span class='fromBD'>{$DocumentType_Name}</span>";
	}
?> <br>серия <?php
	if (empty($Document_Ser)) {
		echo '_______';
	} else {
		echo "<span class='fromBD'>{$Document_Ser}</span>";
	}
?> № <?php
	if (empty($Document_Num)) {
		echo '____________________';
	} else {
		echo "<span class='fromBD'>{$Document_Num}</span>";
	}
?></p><div style="clear: both;"></div><div style="float: left;">Кем выдан:<br> <?php
	if (empty($OrgDep_Name)) {
		echo '_______________________________________';
	} else {
		echo "<span class='fromBD'>{$OrgDep_Name}</span>";
	}
?></div> <p style="margin-left: 55%">Дата выдачи:<br><?php
	if (empty($Document_begDate)) {
		echo '___________________';
	} else {
		echo "<span class='fromBD'>{$Document_begDate}</span>";
	}
?></p>
<p>
	8.1. Сведения об инвалидности (в случае установления группы инвалидности или категории “ребенок-инвалид”):<br><?php
	if (empty($physically_challenged)) {
		echo '___________________________________________________________________________________________________________________';
	} else {
		echo "<span class='fromBD'>______{$physically_challenged}_______</span>";
	}
?></p>
<p>9. Гражданин учтен в Федеральном регистре лиц, имеющих право на государственную социальную помощь
	в соответствии с Федеральным законом от 17 июля 1999 г. № 178-ФЗ “О государственной социальной помощи”<sup>1</sup>:<br>
	да, нет (указать) &nbsp;&nbsp;
	<span class='fromBD'>
		<?php
		if (empty($punct9)) {
			echo '_________';
		} else if (2 == $punct9) {
			echo 'да';
		} else {
			echo 'нет';
		}
		?>
	</span>
	&nbsp;&nbsp;&nbsp;
	Если “да”: код категории в соответствии с Федеральным законом &nbsp;&nbsp;
	<span class='fromBD'>
		<?php
		if (empty($punct91)) {
			echo '_________';
		} else {
			echo $punct91;
		}
		?>
	</span>
</p>
<p>10. СНИЛС (если "да" в п.9)  <?php
	if (empty($punct9)) {
		echo '________________________________________________';
	} else if (2 == $punct9) {
		if (empty($Person_Snils)) {
			echo '________________________________________________';
		} else {
			echo "<span class='fromBD'>{$Person_Snils}</span>";
		}
	}
?></p>
<p>11. Гражданин включен в число лиц, имеющих право на льготное и бесплатное обеспечение лекарственными препаратами в
	соответствии с постановлением Правительства Российской Федерации от 30 июля 1994 г. № 890 “О государственной поддержке развития медицинской
	промышленности и улучшении обеспечения населения и учреждений здравоохранения лекарственными средствами и изделиями медицинского назначения”<sup>2</sup>:
</p>
<p style="text-align: right">
	да, нет (указать) &nbsp;&nbsp;
	<span class="fromBD">
		<?php
		if (empty($punct11)) {
			echo '_________';
		} else if (2 == $punct11) {
			echo 'да';
		} else {
			echo 'нет';
		}
		?>
	</span>
</p>
<p>12. Обоснование направления: <?php
	if (empty($EvnNotifyRegister_Comment)) {
		echo '______________________________________________________<br />';
		echo '____________________________________________________________________________________';
	} else {
		echo "<span class='fromBD'>{$EvnNotifyRegister_Comment}</span>";
	}
?></p>


<div style="font-size: 7pt">
	<p>
		<sup>1</sup> Собрание законодательства Российской Федерации, 1999, № 29, ст. 3699; 2004, № 35, ст. 3607; 2006, № 48, ст. 4945; 2007, № 43, ст. 5084; 2008, № 9, ст. 817, № 29, ст. 3410, № 52, ст. 6224; 2009, № 18, ст. 2152, № 30, ст. 3739, № 52, ст. 6417; 2010, № 50, ст. 6603; 2011, № 27, ст. 3880; 2012, № 31, ст. 4322, № 53, ст. 7583.
	</p>
	<p>
		<sup>2</sup> Собрание законодательства Российской Федерации, 1994, № 15, ст. 1791; 1995, № 29, ст. 2806; 1998, № 1, ст. 133, № 32, ст. 3917; 1999, № 15, ст. 1824; 2000, № 39, ст. 3880; 2002, № 7, ст. 699.
	</p>
</div>

<div class="newpage"></div>

<p>Врач, выдавший направление: <span class='fromBD'>{Doctor_Fio}</span>   _____________________(подпись)</p>
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
<p>Дата: <span class='fromBD'>{EvnNotifyRegister_setDate}</span>
	<br /><br /><br /><br />
	М.П.</p>

</body>
</html>