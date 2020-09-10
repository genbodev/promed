<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>ИЗВЕЩЕНИЕ О БОЛЬНОМ С ХПН</title>
    <style type="text/css">
        span.selected {
            text-decoration: underline;
        }
        span.unselected {
            text-decoration: none;
        }
    </style>
</head>
<body>
<p style="text-align: center">ИЗВЕЩЕНИЕ О БОЛЬНОМ С ХПН</p>
<p>РАЗДЕЛ 1 - идентификация</p>
<p>1. МО, отделение {Lpu_Name}<?php
	if (!empty($LpuSection_Name)) {
		echo ', ' . $LpuSection_Name;
	}
?></p>
<p>2. Ф.И.О. пациента {Person_SurName} {Person_FirName} {Person_SecName}</p>
<p>3. Пол: {Sex_Code} - {Sex_Name}</p>
<p>4. Дата рождения (число, месяц, год) {Person_BirthDay}</p>
<p>5. Адрес места жительства/Адрес регистрации, телефон {Person_Address}<?php
	if (!empty($Person_Phone)) {
		echo ', ' . $Person_Phone;
	}
?></p>
<p>РАЗДЕЛ 2 - сведения о заболевании</p>
<p>1. Диагноз_(по МКБ 10) {Diag_Code} {Diag_Name}</p>
<p>2. Дата установления (число, месяц, год) {EvnNotifyNephro_diagDate}</p>
<p>3. Давность заболевания до установления диагноза {EvnNotifyNephro_firstDate}</p>
<p>4. Способ установления диагноза: {NephroDiagConfType_Code} - {NephroDiagConfType_Name}</p>
<p>5. Наличие ХПН: {NephroCRIType_Code} - {NephroCRIType_Name}</p>
<p>6. Артериальная гипертензия: <span class="<?php
echo ((isset($EvnNotifyNephro_IsHyperten) && 2 == $EvnNotifyNephro_IsHyperten) ? '' : 'un');
?>selected">1 - да</span>, <span class="<?php
echo ((empty($EvnNotifyNephro_IsHyperten) || 1 == $EvnNotifyNephro_IsHyperten) ? '' : 'un');
?>selected">2 - нет</span></p>
<p>7. Рост (в см) {PersonHeight_Height}</p>
<p>8. Вес (в кг) {PersonWeight_Weight}</p>
<p>9. Назначенное лечение (диета, препараты) {EvnNotifyNephro_Treatment}</p>
<p>10. Последние лабораторные данные: Общий белок крови __________
    <br />Креатинин крови <*> {EvnNotifyNephro_Kreatinin} Мочевина крови ________
    <br />Клубочковая фильтрация ________ Гемоглобин <*> {EvnNotifyNephro_Haemoglobin}
    <br />Суточная протеинурия __________ Культура мочи ________
    <br />Белок мочи <*> {EvnNotifyNephro_Protein} Удельный вес <*> {EvnNotifyNephro_SpecWeight} Цилиндры <*> {EvnNotifyNephro_Cast}
    <br />Лейкоциты <*> {EvnNotifyNephro_Leysk} Эритроциты <*> {EvnNotifyNephro_Erythrocyt} Соли <*> {EvnNotifyNephro_Salt}
    <br />Бактерии ________</p>
<p>Дата заполнения {EvnNotifyNephro_setDate}</p>
<p>Лечащий врач _________________________ (Ф.И.О.) {MedPersonal_Fin}</p>
<p>Заведующий отделением ________________ (Ф.И.О.) {MedPersonal_Fih}</p>
<p>-------------------------------
<br /><span style="color: gray;"><*> Обязательные исследования</span></p>
</body>
</html>