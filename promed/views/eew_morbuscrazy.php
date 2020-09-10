<?php
$empty_str = '<span class="empty">Не указано</span>';
$is_allow_edit = (1 == $accessType);
$birt_suffix = usePostgre() ? '_pg' : '';
?>
<div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}" class="specifics">

    <div style="display: <?php echo (!empty($EvnClass_SysNick) && $EvnClass_SysNick == 'EvnSection') ? 'block' : 'none'; ?>; clear: both;">
        <?php if($regionNick == 'kz' && !$isMseDepers) { ?>
        <?php if($Diagtype == 'narko') { ?>
        <a href="javascript:printBirt({'Report_FileName': 'han_EvnPS_f066_1u<?php echo $birt_suffix; ?>.rptdesign','Report_Params': '<?php echo "&paramEvnSection=".$Evn_id."&paramEvnPS=".$Evn_pid."&paramMorbus=".$Morbus_id; ?>','Report_Format': 'pdf'});">Форма N 066-1/у "Статистическая карта выбывшего из наркологического стационара"</a><br />
        <?php } else { ?>
        <a href="javascript:printBirt({'Report_FileName': 'han_EvnPS_f066_3u<?php echo $birt_suffix; ?>.rptdesign','Report_Params': '<?php echo "&paramEvnSection=".$Evn_id."&paramEvnPS=".$Evn_pid."&paramMorbus=".$Morbus_id; ?>','Report_Format': 'pdf'});">Форма N 066-3/у "Статистическая карта выбывшего из психиатрического стационара"</a><br />
        <?php } ?>
        <?php } else { ?>
        <a href="javascript:printBirt({'Report_FileName': 'hosp_f661u2<?php echo $birt_suffix; ?>.rptdesign','Report_Params': '<?php echo "&paramEvnSection=".$Evn_id."&paramEvnPS=".$Evn_pid."&paramMorbus=".$Morbus_id; ?>','Report_Format': 'pdf'});">Форма N 066-1/у-02 "Статистическая карта выбывшего из психиатрического (наркологического) стационара"</a><br />
        <?php } ?>
    </div><br />
    <div style="display: <?php echo (!empty($EvnClass_SysNick) && $EvnClass_SysNick == 'EvnVizitPL' && !$isMseDepers) ? 'block' : 'none'; ?>; clear: both;">
        <a href="javascript:printBirt({'Report_FileName': 'f030u02crazy<?php echo $birt_suffix; ?>.rptdesign','Report_Params': '<?php echo "&paramMorbus=".$Morbus_id; ?>','Report_Format': 'pdf'});">Форма N 030-1/у-02 "Карта обратившегося за психиатрической (наркологической) помощью"</a><br/>
    </div><br />

    <div style="display: <?php echo (false && !empty($EvnClass_SysNick) && $EvnClass_SysNick == 'EvnSection') ? 'none' : 'block'; ?>; clear: both;">
        <div class="caption">
            <h2><span id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_toggleDisplayDiag" class="collapsible">Диагноз</span></h2>
            <div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_toolbarDiag" class="toolbar" style="display: none">
                <a id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_saveDiag" class="button icon icon-save16" title="Сохранить"><span></span></a>
            </div>
        </div>
        <div id="MorbusCrazyDiag_{MorbusCrazy_pid}_{MorbusCrazy_id}" style="display:block; padding:0px 5px 5px; border: 1px solid gray;">

			<div class="data-row-container"><div class="data-row">Дата включения в регистр: <span<?php if ($is_allow_edit && empty($EvnClass_SysNick)) {
						?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputPersonRegister_setDate" class="value link"<?php
					} else {
						echo ' class="value"';
					} ?>><?php echo empty($PersonRegister_setDate) ? $empty_str : '{PersonRegister_setDate}'; ?></span></div>
				<div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaPersonRegister_setDate" class="input-area"></div>
			</div>
			<div class="data-row-container"><div class="data-row">Дата начала заболевания: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbus_setDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($Morbus_setDT) ? $empty_str : '{Morbus_setDT}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbus_setDT" class="input-area"></div></div>
            <div class="data-row-container"><div class="data-row">Сопутствующее психическое (наркологическое) заболевание: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputDiag_nid" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($Diag_nid) ? $empty_str : '{Diag_nid_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaDiag_nid" class="input-area"></div></div>
            <div class="data-row-container"><div class="data-row">Сопутствующее соматическое (в т.ч. неврологическое) заболевание: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputDiag_sid" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($Diag_sid) ? $empty_str : '{Diag_sid_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaDiag_sid" class="input-area"></div></div>
            <div class="data-row-container"><div class="data-row">Исход заболевания: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputCrazyResultDeseaseType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{CrazyResultDeseaseType_id}'><?php echo empty($CrazyResultDeseaseType_id_Name) ? $empty_str : '{CrazyResultDeseaseType_id_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaCrazyResultDeseaseType" class="input-area"></div></div>

            {MorbusCrazyDiag}<!--«Диагнозы»-->
        </div>
    </div>
    <div style="display: <?php echo (false && !empty($EvnClass_SysNick) && $EvnClass_SysNick == 'EvnSection') ? 'none' : 'block'; ?>; clear: both;">
        <div class="caption">
            <h2><span id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_toggleMorbusCrazyDynamicsObserv" class="collapsible">Динамика наблюдения</span></h2>
            <div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_toolbarMorbusCrazyDynamicsObserv" class="toolbar" style="display: none">
                <a id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_saveMorbusCrazyDynamicsObserv" class="button icon icon-save16" title="Сохранить"><span></span></a>
            </div>
        </div>
        <div id="MorbusCrazyDynamicsObserv_{MorbusCrazy_pid}_{MorbusCrazy_id}" style="display:block; padding:0px 5px 5px; border: 1px solid gray;">
            <div class="data-row-container"><div class="data-row"><?php echo ((!empty($regionNick) && $regionNick=='ufa') ? 'Дата снятия с учета:' : 'Дата закрытия карты (снятия с учета):'); ?> <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbus_disDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($Morbus_disDT) ? $empty_str : '{Morbus_disDT}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbus_disDT" class="input-area"></div></div>
            <?php if (!empty($regionNick) && $regionNick=='ufa') { ?><div class="data-row-container"><div class="data-row"><?php echo ((!empty($regionNick) && $regionNick=='ufa') ? 'Дата закрытия карты:' : ''); ?> <span<?php if ($is_allow_edit && (!empty($regionNick) && $regionNick=='ufa')) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazy_CardEndDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php if (!empty($regionNick) && $regionNick=='ufa') {echo empty($MorbusCrazy_CardEndDT) ? $empty_str : '{MorbusCrazy_CardEndDT}';} ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazy_CardEndDT" class="input-area"></div></div><?php } ?>
			<div class="data-row-container"><div class="data-row">Причина прекращения наблюдения: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputCrazyCauseEndSurveyType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{CrazyCauseEndSurveyType_id}'><?php echo empty($CrazyCauseEndSurveyType_id_Name) ? $empty_str : '{CrazyCauseEndSurveyType_id_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaCrazyCauseEndSurveyType" class="input-area"></div></div>

            {MorbusCrazyDynamicsObserv}<!--«Динаммика наблюдения»-->
            {MorbusCrazyVizitCheck}<!--«Контроль посещений»-->
        </div>
    </div>
    <div style="display: <?php echo (false && !empty($EvnClass_SysNick) && $EvnClass_SysNick == 'EvnSection') ? 'none' : 'block'; ?>; clear: both;">
        {MorbusCrazyDynamicsState}<!--«Динамика состояния»-->
    </div>

    <div class="caption"><h2><span id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_toggleMorbusCrazyBasePS" class="collapsible">Сведения о госпитализациях</span></h2></div>
    <div id="MorbusCrazyBasePS_{MorbusCrazy_pid}_{MorbusCrazy_id}" style="display: block; padding:0px 5px 5px; border: 1px solid gray;">
        {MorbusCrazyBasePS}<!--«Сведения о госпитализациях»-->
        {MorbusCrazyForceTreat}<!--«Принудительное лечение»-->
    </div>



    <div class="caption"><h2><span id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_toggleMorbusCrazyPerson" class="collapsible">Дополнительные сведения о больном</span></h2></div>
    <div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_toolbarMorbusCrazyPerson" class="toolbar" style="display: none">
        <a id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_saveMorbusCrazyPerson" class="button icon icon-save16" title="Сохранить"><span></span></a>
    </div>
    <div id="MorbusCrazyPerson_{MorbusCrazy_pid}_{MorbusCrazy_id}" style="display: block; padding:0px 5px 5px; border: 1px solid gray;">

        <div class="data-row-container"><div class="data-row">Число дней работы в ЛТМ: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyBase_LTMDayCount" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusCrazyBase_LTMDayCount) ? $empty_str : '{MorbusCrazyBase_LTMDayCount}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyBase_LTMDayCount" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Число дней лечебных отпусков (за период госпитализации): <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyBase_HolidayDayCount" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusCrazyBase_HolidayDayCount) ? $empty_str : '{MorbusCrazyBase_HolidayDayCount}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyBase_HolidayDayCount" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Число лечебных отпусков (за период госпитализации): <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyBase_HolidayCount" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusCrazyBase_HolidayCount) ? $empty_str : '{MorbusCrazyBase_HolidayCount}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyBase_HolidayCount" class="input-area"></div></div>

        <div class="data-row-container"><div class="data-row">Инвалид ВОВ: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyPerson_IsWowInvalid" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusCrazyPerson_IsWowInvalid}'><?php echo empty($MorbusCrazyPerson_IsWowInvalid_Name) ? $empty_str : '{MorbusCrazyPerson_IsWowInvalid_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyPerson_IsWowInvalid" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Участник ВОВ: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyPerson_IsWowMember" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusCrazyPerson_IsWowMember}'><?php echo empty($MorbusCrazyPerson_IsWowMember_Name) ? $empty_str : '{MorbusCrazyPerson_IsWowMember_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyPerson_IsWowMember" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Образование: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputCrazyEducationType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{CrazyEducationType_id}'><?php echo empty($CrazyEducationType_id_Name) ? $empty_str : '{CrazyEducationType_id_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaCrazyEducationType" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Число законченных классов среднеобразовательного учреждения: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyPerson_CompleteClassCount" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusCrazyPerson_CompleteClassCount) ? $empty_str : '{MorbusCrazyPerson_CompleteClassCount}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyPerson_CompleteClassCount" class="input-area"></div></div>

        <div class="data-row-container"><div class="data-row">Учится: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyPerson_IsEducation" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusCrazyPerson_IsEducation}'><?php echo empty($MorbusCrazyPerson_IsEducation_Name) ? $empty_str : '{MorbusCrazyPerson_IsEducation_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyPerson_IsEducation" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Источник средств существования: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputCrazySourceLivelihoodType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{CrazySourceLivelihoodType_id}'><?php echo empty($CrazySourceLivelihoodType_id_Name) ? $empty_str : '{CrazySourceLivelihoodType_id_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaCrazySourceLivelihoodType" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Проживает: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputCrazyResideType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{CrazyResideType_id}'><?php echo empty($CrazyResideType_id_Name) ? $empty_str : '{CrazyResideType_id_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaCrazyResideType" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Условия проживания: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputCrazyResideConditionsType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{CrazyResideConditionsType_id}'><?php echo empty($CrazyResideConditionsType_id_Name) ? $empty_str : '{CrazyResideConditionsType_id_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaCrazyResideConditionsType" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Дата обращения к психиатру (наркологу) впервые в жизни: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyBase_firstDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusCrazyBase_firstDT) ? $empty_str : '{MorbusCrazyBase_firstDT}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyBase_firstDT" class="input-area"></div></div>

        <div class="data-row-container"><div class="data-row">Судимости до обращения к психиатру (наркологу): <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyPerson_IsConvictionBeforePsych" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusCrazyPerson_IsConvictionBeforePsych}'><?php echo empty($MorbusCrazyPerson_IsConvictionBeforePsych_Name) ? $empty_str : '{MorbusCrazyPerson_IsConvictionBeforePsych_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyPerson_IsConvictionBeforePsych" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Дата смерти: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyBase_DeathDT" class="value link"<?php } else { echo ' class="value"';} ?>><?php echo empty($MorbusCrazyBase_DeathDT) ? $empty_str : '{MorbusCrazyBase_DeathDT}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyBase_DeathDT" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Причина смерти: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputCrazyDeathCauseType" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{CrazyDeathCauseType_id}'><?php echo empty($CrazyDeathCauseType_id_Name) ? $empty_str : '{CrazyDeathCauseType_id_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaCrazyDeathCauseType" class="input-area"></div></div>

        {MorbusCrazyNdOsvid}<!--«Недобровольное освидетельствование»-->
        {MorbusCrazyPersonSurveyHIV}<!--«Обследование на ВИЧ»-->

    </div>

    <div style="display: <?php echo (false && !empty($EvnClass_SysNick) && $EvnClass_SysNick == 'EvnSection') ? 'none' : 'block'; ?>; clear: both;">
        {MorbusCrazyPersonStick}<!--«Временная нетрудоспособност»-->
        {MorbusCrazyPersonInvalid} <!--«Инвалидность по психическому заболеванию»-->
        {MorbusCrazyPersonSuicidalAttempt}<!--«Суицидальные попытки»-->
        {MorbusCrazyPersonSocDangerAct}<!--«Общественно-опасные действия»-->
    </div>

    <div class="caption"><h2><span id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_toggleMorbusCrazyDrug" class="collapsible">Сведения об употреблении психоактивных средств</span></h2></div>
    <div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_toolbarMorbusCrazyDrug" class="toolbar" style="display: none">
        <a id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_saveMorbusCrazyDrug" class="button icon icon-save16" title="Сохранить"><span></span></a>
    </div>
    <div id="MorbusCrazyDrug_{MorbusCrazy_pid}_{MorbusCrazy_id}" style="display: block; padding:0px 5px 5px; border: 1px solid gray;">
        <div class="data-row-container"><div class="data-row">Использование чужих шприцов, игл, приспособлений: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyBase_IsUseAlienDevice" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusCrazyBase_IsUseAlienDevice}'><?php echo empty($MorbusCrazyBase_IsUseAlienDevice_Name) ? $empty_str : '{MorbusCrazyBase_IsUseAlienDevice_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyBase_IsUseAlienDevice" class="input-area"></div></div>
        <div class="data-row-container"><div class="data-row">Проживание с потребителем психоактивных средств: <span<?php if ($is_allow_edit) { ?> id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputMorbusCrazyBase_IsLivingConsumDrug" class="value link"<?php } else { echo ' class="value"';} ?> dataid='{MorbusCrazyBase_IsLivingConsumDrug}'><?php echo empty($MorbusCrazyBase_IsLivingConsumDrug_Name) ? $empty_str : '{MorbusCrazyBase_IsLivingConsumDrug_Name}'; ?></span></div><div id="MorbusCrazy_{MorbusCrazy_pid}_{MorbusCrazy_id}_inputareaMorbusCrazyBase_IsLivingConsumDrug" class="input-area"></div></div>
        {MorbusCrazyBaseDrugStart}<!--«Возраст начала употребления психоактивных веществ»-->
        {MorbusCrazyDrug}<!--«Сведения об употреблении психоактивных средств»-->
        {MorbusCrazyDrugVolume}<!--«Полученный объем наркологической помощи»-->
    </div>
    <div style="display: <?php echo (false && !empty($EvnClass_SysNick) && $EvnClass_SysNick == 'EvnSection') ? 'none' : 'block'; ?>; clear: both;">
        {MorbusCrazyBBK}<!--«Сведения о Военно-врачебных комиссиях»-->
    </div>


</div>