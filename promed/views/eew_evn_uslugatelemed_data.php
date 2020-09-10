<div id="EvnUslugaTelemed_data_{EvnUslugaTelemed_id}" class="section ">
    <div id="EvnUslugaTelemed_data_{EvnUslugaTelemed_id}_content">
        <div style="text-align: center">
            <p>{Lpu_Name}</p>
            <p>{Lpu_Address}</p>
        </div>
        <br>

        <div style="text-align: left; line-height: 0.8em;">
            <p><strong>Пациент: {Person_Fio} <?php if (!empty($Person_Birthday)) { echo ', {Person_Birthday} г.р.'; } ?></strong></p>
            <p><span id="EvnUslugaTelemed_data_{EvnUslugaTelemed_id}_showDiagList" class="link" title="Показать список уточненных диагнозов">Диагноз</span>: <span id="EvnUslugaTelemed_data_{EvnUslugaTelemed_id}_selDiag" class="link" title="Показать все случаи лечения по коду диагноза">{Diag_Code}</span>. {Diag_Name}.</p>
            <p>Профиль: {LpuSectionProfile_Name}</p>
            <p>Результат: {UslugaTelemedResultType_Name}</p>
            <p><strong>Отделение: {LpuSection_Code}. {LpuSection_Name}</strong></p>
            <p>Врач: {MedPersonal_Fin}</p>
            <?php if ( getRegionNick() != 'kz' ) { echo "<p>Услуга: {UslugaComplex_Code} {UslugaComplex_Name}</p>"; } ?>
            <p>Выполнено: {EvnUslugaTelemed_setDate} {EvnUslugaTelemed_setTime}</p>
        </div>
        <br>

        <div style="text-align: left; line-height: 0.8em;">
            <p>Кем направлен: {OrgDirectSubject_Name}, {DirectSubject_Code} {DirectSubject_Name}</p>
            <p>Направление № <span id="EvnUslugaTelemed_data_{EvnUslugaTelemed_id}_showEvnDirection" class="link" title="Показать электронное направление">{EvnDirection_Num}</span> от: <strong>{EvnDirection_setDate}</strong></p>
            <p>Врач: <strong>{MedPersonalDirect_Fin}</strong></p>
        </div>
        <br>

		<?php if ($EvnReceptKardio_isVisible) { ?>
            {EvnReceptKardio}
		<?php } ?>
    </div>
</div>
