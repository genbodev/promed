<div id="EvnUslugaCommon_data_{EvnUslugaCommon_id}" class="section ">
    <div id="EvnUslugaCommon_data_{EvnUslugaCommon_id}_content">
        <div style="text-align: center">
            <p>{Lpu_Name}</p>
            <p>{Lpu_Address}</p>
        </div>
        <br>

        <div style="text-align: left; line-height: 0.8em;">
			<?php if($isMseDepers) { ?>
            <p><strong>Пациент: ***</strong></p>
			<?php } else { ?>
            <p><strong>Пациент: {Person_Fio} <?php if (!empty($Person_Birthday)) { echo ', {Person_Birthday} г.р.'; } ?></strong></p>
			<?php } ?>
            <p><span id="EvnUslugaCommon_data_{EvnUslugaCommon_id}_showDiagList" class="link" title="Показать список уточненных диагнозов">Диагноз</span>: <span id="EvnUslugaCommon_data_{EvnUslugaCommon_id}_selDiag" class="link" title="Показать все случаи лечения по коду диагноза">{Diag_Code}</span>. {Diag_Name}.</p>
            <p><strong>Услуга: {Usluga_Name}</strong></p>
            <?php if (!empty($LpuSection_Code)) { ?><p><strong>Отделение: {LpuSection_Code}. {LpuSection_Name}</strong></p><?php } ?>
            <p>Врач: {MedPersonal_Fin}</p>
            <p>Выполнено: {EvnUslugaCommon_setDate} {EvnUslugaCommon_setTime}</p>
        </div>
        <br>

        <div style="text-align: left; line-height: 0.8em;">
            <p>Кем направлен: {OrgDirectSubject_Name}<?php if (!empty($DirectSubject_Code)) { ?>, {DirectSubject_Code} {DirectSubject_Name}<?php } ?></p>
            <p>Направление № <span id="EvnUslugaCommon_data_{EvnUslugaCommon_id}_showEvnDirection" class="link" title="Показать электронное направление">{EvnDirection_Num}</span> от: <strong>{EvnDirection_setDate}</strong></p>
            <p>Врач: <strong>{MedPersonalDirect_Fin}</strong></p>
        </div>
        <br>

    </div>
</div>
