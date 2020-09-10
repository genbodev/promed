<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>КАРТА ДИНАМИЧЕСКОГО НАБЛЮДЕНИЯ БОЛЬНОГО С ХПН</title>
    <style type="text/css">
        table {
            border-collapse: collapse;
	        width: 100%;
        }
        th {
            text-align: left; /* Выравнивание по левому краю */
        }
        td, th {
            border: 1px solid #000;
            padding: 3px;
        }
        span.selected {
            text-decoration: underline;
        }
        span.unselected {
            text-decoration: none;
        }
    </style>
</head>
<body>
<p style="text-align: center">КАРТА ДИНАМИЧЕСКОГО НАБЛЮДЕНИЯ БОЛЬНОГО С ХПН
    <br />(заполняется нефрологом ККБ 3)</p>
<p>РАЗДЕЛ 1 - идентификация</p>
<p>1. Номер в регистре {PersonRegister_Num}</p>
<p>2. Учреждение, отделение ____________________________________________</p>
<p>3. Ф.И.О. больного  {Person_SurName} {Person_FirName} {Person_SecName}</p>
<p>4. Пол: {Sex_Code} - {Sex_Name}</p>
<p>5. Дата рождения (число, месяц, год), возраст {Person_BirthDay}, {Person_Age}</p>
<p>6. Адрес, почтовый индекс, телефон {Person_Address}<?php
if (!empty($Person_Phone)) {
	echo ', ' . $Person_Phone;
}
?></p>

<p>РАЗДЕЛ 2 - сведения о заболевании</p>
<p>1. Диагноз {Diag_Code} {Diag_Name}</p>
<p>2. Дата установления {MorbusNephro_diagDate}<?php
if (empty($MorbusNephro_diagDate)) {
	echo '________________________________________________';
}
?></p>
<p>3. Давность заболевания до установления диагноза {MorbusNephro_firstDate}</p>
<p>4. Способ установления диагноза: {NephroDiagConfType_Code} - {NephroDiagConfType_Name}</p>
<p>5. Подтверждение диагноза: <span class="<?php
echo ((isset($NephroDiagConfTypeC_Code)) ? '' : 'un');
?>selected">1 - да</span>, <span class="<?php
echo ((empty($NephroDiagConfTypeC_Code)) ? '' : 'un');
?>selected">2 - нет</span>.</p>
<p>6. Способ подтверждения диагноза: {NephroDiagConfTypeC_Code} - {NephroDiagConfTypeC_Name}</p>
<p>7. Наличие ХПН: {NephroCRIType_Code} - {NephroCRIType_Name}</p>
<p>8. Динамика ХПН: <?php
	if (empty($MorbusNephro_CRIDinamic)) {
		echo 'нет';
	} else {
		echo $MorbusNephro_CRIDinamic;
	}
?></p>
<p>9. Рост (в см) {PersonHeight_Height}</p>
<p>10. Вес (в кг) {PersonWeight_Weight}</p>
<p>11. Дата постановки на учет (число, месяц, год) {MorbusNephro_begDate}</p>

<p style="text-align: center">ТАБЛИЦА ДИНАМИЧЕСКОГО НАБЛЮДЕНИЯ</p>
<table>
	<thead>
	<tr>
		<th style="width: 40%">ДАТА</th>
<?php
	$dates = array();
	if (isset($MorbusNephroDispDates)) {
		$dates = $MorbusNephroDispDates;
	}
	$cntDates = count($dates);
	foreach ($dates as $d) {
		echo "<th>{$d}</th>";
	}
?>
	</tr>
	</thead>
    <tbody>
<?php
	$arr = array();
	if (isset($MorbusNephroDisp)) {
	    $arr = $MorbusNephroDisp;
	}
	foreach ($arr as $type) {
		$values = '';
		for ($i=0; $i < $cntDates; $i++) {
			$d = $dates[$i];
			if (empty($type['RateValues'][$d])) {
				$values .= '<th>&nbsp;</th>';
			} else  {
				$values .= "<th>{$type['RateValues'][$d]}</th>";
			}
		}
		foreach ($type['RateValues'] as $d => $value) {
		}
		echo "
		<tr>
	        <td>{$type['RateType_Name']}</td>
	        {$values}
	    </tr>";
	}
?>
    </tbody>
</table>
<p style="text-align: center">ТАБЛИЦА ПРОВОДИМОЙ ТЕРАПИИ</p>
<p>НАЗНАЧЕНИЯ</p>
<table>
	<tbody>
	<tr>
        <td style="width: 40%">&nbsp;</td>
        <td style="width: 10%">&nbsp;</td>
        <td style="width: 10%">&nbsp;</td>
        <td style="width: 10%">&nbsp;</td>
        <td style="width: 10%">&nbsp;</td>
        <td style="width: 10%">&nbsp;</td>
        <td style="width: 10%">&nbsp;</td>
	</tr>
    <tr>
        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
	    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
	</tbody>
</table>

<p>РАЗДЕЛ 3 - группы диспансерного учета</p>
<p><span class="<?php
echo ((isset($DispGroupType_id) && 1 == $DispGroupType_id) ? '' : 'un');
?>selected">1. Обсервационная</span>  -  контроль    лабораторных   показателей   1  раз в 6 месяцев</p>
<p><span class="<?php
echo ((isset($DispGroupType_id) && 2 == $DispGroupType_id) ? '' : 'un');
?>selected">2. Контролируемая</span>  -  контроль    лабораторных   показателей   1  раз в 3 месяца.</p>
<p><span class="<?php
echo ((isset($DispGroupType_id) && 3 == $DispGroupType_id) ? '' : 'un');
?>selected">3. Перманентно контролируемая</span>  -  контроль  лабораторных  показателей 1 раз в месяц.</p>
<p>РАЗДЕЛ 4 - исход наблюдения</p>
<p><span class="<?php
echo ((isset($NephroResultType_id) && 1 == $NephroResultType_id) ? '' : 'un');
?>selected">1 - продолжение наблюдения</span>,</p>
<p><span class="<?php
echo ((isset($NephroResultType_id) && 2 == $NephroResultType_id) ? '' : 'un');
?>selected">2 - начало</span> <span class="<?php
echo ((isset($DialysisType_id) && 1 == $DialysisType_id) ? '' : 'un');
?>selected">гемодиализа</span>, <span class="<?php
echo ((isset($DialysisType_id) && 2 == $DialysisType_id) ? '' : 'un');
?>selected">перитонеального диализа</span>, дата {MorbusNephro_dialDate},</p>
<p><span class="<?php
echo ((isset($NephroResultType_id) && 3 == $NephroResultType_id) ? '' : 'un');
?>selected">3 - трансплантация почки</span>, <span class="<?php
echo ((isset($KidneyTransplantType_id) && 1 == $KidneyTransplantType_id) ? '' : 'un');
?>selected">трупная</span>, <span class="<?php
echo ((isset($KidneyTransplantType_id) && 2 == $KidneyTransplantType_id) ? '' : 'un');
?>selected">родственная</span>, дата {MorbusNephro_transDate},</p>
<p><span class="<?php
echo ((isset($NephroResultType_id) && 4 == $NephroResultType_id) ? '' : 'un');
?>selected">4 - смерть</span>, дата {MorbusNephro_deadDT},</p>
<p><span class="<?php
echo ((isset($NephroResultType_id) && 5 == $NephroResultType_id) ? '' : 'un');
?>selected">5 - выбывание из-под наблюдения по другим причинам (...)</span>,</p>
<p>Дата __________</p>
<p>Ответственный врач_________________________ (Ф.И.О.)</p>
</body>
</html>