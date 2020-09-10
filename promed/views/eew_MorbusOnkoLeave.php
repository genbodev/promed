<div id="MorbusOnkoLeave_{MorbusOnkoLeave_id}" class="frame" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoLeave_{MorbusOnkoLeave_id}_toolbar').style.visibility='visible'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoLeave_{MorbusOnkoLeave_id}_toolbar').style.visibility='hidden'">

	<div style="float: right">
		<div id="MorbusOnkoLeave_{MorbusOnkoLeave_id}_toolbar" class="toolbar" style="visibility: hidden">
			<a id="MorbusOnkoLeave_{MorbusOnkoLeave_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
		</div>
	</div>

	<div id="MorbusOnkoLeave_{MorbusOnkoLeave_id}_content">

		<p style="text-align: right; font-weight: bold;">УТВЕРЖДЕНО
		<br />Приказ Министерства
		<br />здравоохранения
		<br />Российской Федерации
		<br />от 19.04.99 г. N 135</p>
		<p>{Lpu_Name}</p>
		<p style="text-align: right">Ф. N 027-1/У Утв. МЗ
		<br />Российской Федерации
		<br />19 апреля 1999 г. N 135
		<br /></p>
		<p style="text-align: center; font-weight: bold;">ВЫПИСКА
			<br />ИЗ МЕДИЦИНСКОЙ КАРТЫ СТАЦИОНАРНОГО БОЛЬНОГО ЗЛОКАЧЕСТВЕННЫМ
			<br />НОВООБРАЗОВАНИЕМ
			<br />(заполняется всеми стационарами)
		<br /></p>
<?php
$Ethnos_Name = empty($Ethnos_Name) ? str_repeat('_', 75) : "<b>{$Ethnos_Name}</b>";
$Person_Address = empty($Person_PAddress) ? str_repeat('_', 75) : "<b>{$Person_PAddress}</b>";
$Person_Phone = empty($Person_Phone) ? str_repeat('_', 30) : "<b>{$Person_Phone}</b>";
if (empty($KLAreaType_Name)) {
	if (empty($Person_PAddress)) {
		$KLAreaType_Name = 'неизвестно';
	} else {
		$Person_PAddress = strtoupper($Person_PAddress);
		if (false !== stripos($Person_PAddress, 'Г ') || false !== strpos($Person_PAddress, 'ПГТ ') ) {
			$KLAreaType_Name = 'города';
		} else {
			$KLAreaType_Name = 'села';
		}
	}
}
$KLAreaType_Name = "<b>{$KLAreaType_Name}</b>";
$OnkoOccupationClass_Name = empty($OnkoOccupationClass_Name) ? str_repeat('_', 49) : "<b>{$OnkoOccupationClass_Name}</b>";
$EvnSection_disDate = empty($EvnSection_disDate) ? 'число ___________ месяц ___________ год _____' : "<b>{$EvnSection_disDate}</b>";
$OnkoPurposeHospType_Name = empty($OnkoPurposeHospType_Name) ? str_repeat('_', 72) : "<b>{$OnkoPurposeHospType_Name}</b>";
$EvnDiagPsSopList = empty($EvnDiagPsSopList) ? str_repeat('_', 90) : "<b>{$EvnDiagPsSopList}</b>";
$TumorPrimaryTreatType_Name = empty($TumorPrimaryTreatType_Name) ? str_repeat('_', 90) : "<b>{$TumorPrimaryTreatType_Name}</b>";
$TumorRadicalTreatIncomplType_Name = empty($TumorRadicalTreatIncomplType_Name) ? str_repeat('_', 90) : "<b>{$TumorRadicalTreatIncomplType_Name}</b>";
?>
		<p>01. Название и адрес учреждения, выдавшего выписку <b>{Lpu_Nick} {Lpu_Address}</b></p>
		<p>02. Название и адрес учреждения, куда направляется выписка _______________________________</p>
		<p>03. Фамилия Имя Отчество больного <b>{Person_SurName} {Person_FirName} {Person_SecName}</b></p>
		<p>04. Дата рождения: <b>{Person_BirthDay}</b></p>
		<p>05. Пол <b>{Sex_Name}</b></p>
		<p>06. Этническая группа <?php echo $Ethnos_Name; ?></p>
		<p>07. Адрес больного: <?php echo $Person_Address; ?> телефон <?php echo $Person_Phone; ?></p>
		<p>08. Житель: <?php echo $KLAreaType_Name; ?></p>
		<p>09. Социально - профессиональная группа <?php echo $OnkoOccupationClass_Name; ?></p>
		<p>10. Дата поступления в стационар: <b>{EvnSection_setDate}</b></p>
		<p>11. Дата выписки из стационара или смерти: <?php echo $EvnSection_disDate; ?></p>
		<p>12. Длительность пребывания в стационаре в днях <b>{EvnSection_Day}</b></p>
		<p>13. Диагноз данного злокачественного новообразования установлен впервые в жизни в период данной госпитализации  <b>{IsFirst_Name}</b></p>
		<p>14. Цель госпитализации: <?php echo $OnkoPurposeHospType_Name; ?></p>
		<p>15. Заключительный диагноз</p>
		<p>15.1. Топография опухоли <b>{Diag_FullName}</b></p>
		<p>15.2. Морфологический тип опухоли <b>{OnkoDiag_Name}</b></p>
		<p>15.3. Стадия по системе TNM: T (0-4х) <b>{OnkoT_Name}</b>; N (0-3,х) <b>{OnkoN_Name}</b>; M (0,1,х) <b>{OnkoM_Name}</b></p>
		<p>15.4. Стадия опухолевого процесса: <b>{TumorStage_Name}</b></p>
		<p>15.5. Локализация отдаленных метастазов (при IV стадии заболевания):
			<br />Неизвестна: <b>{IsTumorDepoUnknown_Name}</b>
			<br />Отдаленные лимфатические узлы: <b>{IsTumorDepoLympha_Name}</b>
			<br />Кости: <b>{IsTumorDepoBones_Name}</b>
			<br />Печень: <b>{IsTumorDepoLiver_Name}</b>
			<br />Легкие и/или плевра: <b>{IsTumorDepoLungs_Name}</b>
			<br />Головной мозг: <b>{IsTumorDepoBrain_Name}</b>
			<br />Кожа: <b>{IsTumorDepoSkin_Name}</b>
			<br />Почки: <b>{IsTumorDepoKidney_Name}</b>
			<br />Яичники: <b>{IsTumorDepoOvary_Name}</b>
			<br />Брюшина: <b>{IsTumorDepoPerito_Name}</b>
			<br />Костный мозг: <b>{IsTumorDepoMarrow_Name}</b>
			<br />Другие органы: <b>{IsTumorDepoOther_Name}</b>
			<br />Множественные: <b>{IsTumorDepoMulti_Name}</b></p>
		<p>15.6. Метод подтверждения диагноза: <b>{OnkoDiagConfType_Name}</b></p>
		<p>16. Сопутствующие заболевания: <?php echo $EvnDiagPsSopList; ?></p>
		<p>17. Характер проведенного за период данной госпитализации лечения <?php echo $TumorPrimaryTreatType_Name; ?></p>
		<p>18. Причина незавершенности радикального лечения <?php echo $TumorRadicalTreatIncomplType_Name; ?></p>
<?php
$EvnUslugaOnkoSurg_setDate = empty($EvnUslugaOnkoSurg_setDate) ? 'число ___________ месяц ___________ год _____' : "<b>{$EvnUslugaOnkoSurg_setDate}</b>";
$Operation_Name = empty($Operation_Name) ? str_repeat('_', 90) : "<b>{$Operation_Name}</b>";
$SurgOslList = empty($SurgOslList) ? str_repeat('_', 90) : "<b>{$SurgOslList}</b>";
?>
		<p>19. Хирургическое лечение</p>
		<p>19.1. Дата операции: <?php echo $EvnUslugaOnkoSurg_setDate; ?></p>
		<p>19.2. Название операции <?php echo $Operation_Name; ?></p>
		<p>19.3. Осложнения хирургического лечения: <?php echo $SurgOslList; ?></p>
<?php
$EvnUslugaOnkoBeam_setDate = empty($EvnUslugaOnkoBeam_setDate) ? 'число ___________ месяц ___________ год _____' : "<b>{$EvnUslugaOnkoBeam_setDate}</b>";
$OnkoUslugaBeamIrradiationType_Name = empty($OnkoUslugaBeamIrradiationType_Name) ? str_repeat('_', 90) : "<b>{$OnkoUslugaBeamIrradiationType_Name}</b>";
$OnkoUslugaBeamKindType_Name = empty($OnkoUslugaBeamKindType_Name) ? str_repeat('_', 90) : "<b>{$OnkoUslugaBeamKindType_Name}</b>";
$OnkoUslugaBeamMethodType_Name = empty($OnkoUslugaBeamMethodType_Name) ? str_repeat('_', 90) : "<b>{$OnkoUslugaBeamMethodType_Name}</b>";
$OnkoUslugaBeamRadioModifType_Name = empty($OnkoUslugaBeamRadioModifType_Name) ? str_repeat('_', 90) : "<b>{$OnkoUslugaBeamRadioModifType_Name}</b>";
$OnkoUslugaBeamFocusType_Name = empty($OnkoUslugaBeamFocusType_Name) ? str_repeat('_', 90) : "<b>{$OnkoUslugaBeamFocusType_Name}</b>";
$EvnUslugaOnkoBeam_TotalDoseTumor = empty($EvnUslugaOnkoBeam_TotalDoseTumor) ? str_repeat('_', 20) : "<b>{$EvnUslugaOnkoBeam_TotalDoseTumor}</b>";
$TotalDoseTumor_Unit = empty($TotalDoseTumor_Unit) ? 'Гр' : "<b>{$TotalDoseTumor_Unit}</b>";
$EvnUslugaOnkoBeam_TotalDoseRegZone = empty($EvnUslugaOnkoBeam_TotalDoseRegZone) ? str_repeat('_', 20) : "<b>{$EvnUslugaOnkoBeam_TotalDoseRegZone}</b>";
$TotalDoseRegZone_Unit = empty($TotalDoseRegZone_Unit) ? 'Гр' : "<b>{$TotalDoseRegZone_Unit}</b>";
$BeamOslList = empty($BeamOslList) ? str_repeat('_', 90) : "<b>{$BeamOslList}</b>";
?>
		<p>20. Лучевое лечение</p>
		<p>20.1. Дата начала курса лучевой терапии: <?php echo $EvnUslugaOnkoBeam_setDate; ?></p>
		<p>20.2. Способ облучения  <?php echo $OnkoUslugaBeamIrradiationType_Name; ?></p>
		<p>20.3. Вид лучевой терапии  <?php echo $OnkoUslugaBeamKindType_Name; ?></p>
		<p>20.4. Методы лучевой терапии  <?php echo $OnkoUslugaBeamMethodType_Name; ?></p>
		<p>20.5. Радиомодификаторы, применявшиеся при проведении лучевой терапии  <?php echo $OnkoUslugaBeamRadioModifType_Name; ?></p>
		<p>20.6. Поля облучения <?php echo $OnkoUslugaBeamFocusType_Name; ?></p>
		<p>20.7. Суммарная доза на опухоль <?php echo $EvnUslugaOnkoBeam_TotalDoseTumor; ?> (<?php echo $TotalDoseTumor_Unit; ?>);
			<br />Суммарная доза на зоны регионарного метастазирования <?php echo $EvnUslugaOnkoBeam_TotalDoseRegZone; ?> (<?php echo $TotalDoseRegZone_Unit; ?>)</p>
		<p>20.8. Осложнения лучевого лечения: <?php echo $BeamOslList; ?></p>
<?php
$EvnUslugaOnkoChem_setDate = empty($EvnUslugaOnkoChem_setDate) ? 'число ___________ месяц ___________ год _____' : "<b>{$EvnUslugaOnkoChem_setDate}</b>";
$OnkoUslugaChemKindType_Name = empty($OnkoUslugaChemKindType_Name) ? str_repeat('_', 90) : "<b>{$OnkoUslugaChemKindType_Name}</b>";
$OnkoDrugChemList = empty($OnkoDrugChemList) ? str_repeat('_', 90) : "<b>{$OnkoDrugChemList}</b>";
$ChemOslList = empty($ChemOslList) ? str_repeat('_', 90) : "<b>{$ChemOslList}</b>";
?>
		<p>21. Химиотерапевтическое лечение</p>
		<p>21.1. Дата начала курса химиотерапии: <?php echo $EvnUslugaOnkoChem_setDate; ?></p>
		<p>21.2. Вид химиотерапии: <?php echo $OnkoUslugaChemKindType_Name; ?></p>
		<p>21.3. Препараты, суммарные дозы: <?php echo $OnkoDrugChemList; ?></p>
		<p>21.4. Осложнения химиотерапевтического лечения: <?php echo $ChemOslList; ?></p>
<?php
$EvnUslugaOnkoGormun_setDate = empty($EvnUslugaOnkoGormun_setDate) ? 'число ___________ месяц ___________ год _____' : "<b>{$EvnUslugaOnkoGormun_setDate}</b>";
$types_arr = array(
	'EvnUslugaOnkoGormun_IsBeam' => 'лучевая',
	'EvnUslugaOnkoGormun_IsSurg' => 'хирургическая',
	'EvnUslugaOnkoGormun_IsDrug' => 'лекарственная',
	'EvnUslugaOnkoGormun_IsOther' => 'неизвестно',
);
$types = array();
foreach ($types_arr as $key => $type) {
	if ( isset($$key) && 2 == $$key ) {
		$types[] = $type;
	}
}
if (empty($types)) {
	$EvnUslugaOnkoGormunKindType = str_repeat('_', 90);
} else {
	$types = implode(', ', $types);
	$EvnUslugaOnkoGormunKindType = "<b>{$types}</b>";
}
$OnkoDrugGormunList = empty($OnkoDrugGormunList) ? str_repeat('_', 90) : "<b>{$OnkoDrugGormunList}</b>";
$GormunOslList = empty($GormunOslList) ? str_repeat('_', 90) : "<b>{$GormunOslList}</b>";
?>
		<p>22. Гормоноиммунотерапевтическое лечение:</p>
		<p>22.1. Дата начала курса: <?php echo $EvnUslugaOnkoGormun_setDate; ?></p>
		<p>22.2. Вид гормонотерапии: <?php echo $EvnUslugaOnkoGormunKindType; ?></p>
		<p>22.3. Препараты, дозы <?php echo $OnkoDrugGormunList; ?></p>
		<p>22.4. Осложнения гормоноиммунотерапевтического лечения: <?php echo $GormunOslList; ?></p>
		<p>23. Другие виды специального лечения: ____________________________________________________
			<br />__________________________________________________________________________________________</p>
		<p>24. Особенности случая: __________________________________________________________________
			<br />__________________________________________________________________________________________</p>
		<p>25. Лечебные и трудовые рекомендации: ____________________________________________________
			<br />__________________________________________________________________________________________</p>
		<p>26. Фамилия и инициалы, телефон врача, заполнившего выписку <b>{MedPersonal_Fin}</b>_______________</p>
		<p>Дата заполнения выписки "___"________________ 20__ г. Подпись врача ___________________</p>
		<p>Выписка пересылается в онкологический диспансер (кабинет) по месту жительства больного.</p>
	</div>
</div>


