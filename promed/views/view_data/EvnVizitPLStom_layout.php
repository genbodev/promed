<div id="EvnVizitPLStom_head_{EvnVizitPLStom_id}" class="columns">

	<div class="section frame evn_visit_pl"  onmouseover="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnVizitPLStom_data_{EvnVizitPLStom_id}_toolbar').style.display='block'; document.getElementById('EvnVizitPLStom_protocol_{EvnVizitPLStom_id}_toolbar').style.display='block'; } " onmouseout="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnVizitPLStom_data_{EvnVizitPLStom_id}_toolbar').style.display='none'; document.getElementById('EvnVizitPLStom_protocol_{EvnVizitPLStom_id}_toolbar').style.display='none'; }">
		<?php if (!empty($AlertReg_Msg)) { ?><p style="font-weight:bold; color: #DD3333">{AlertReg_Msg}</p><?php } ?>
		<div class="header_info">
			<div class="columns">
				<div class="left">Создан: {insDT} {ins_Name}
				<br /><!--span id="EvnVizitPLStom_{EvnVizitPLStom_id}_toggleDisplayDocVersions" class="link" style="font-size: 8pt" title="Развернуть">Предыдущие версии документа</span-->
				<div id="DocVersions_{EvnVizitPLStom_id}"><!-- Предыдущие версии документа --></div>
				</div>
			</div>
		</div>
		<div class="clear line"><hr></div>
		<div id="EvnVizitPLStom_{EvnVizitPLStom_id}">

			{EvnVizitPLStom_data}

			{EvnDiagPLStom}

            <div class="clear data-table">
                <div class="caption">
                    <h2>
                        <span>Зубная карта</span>
                    </h2>
                </div>
            </div>
            <div class="clear" id="EvnVizitPLStom_{EvnVizitPLStom_id}_ToothMap"></div>

            <div class="clear" id="EvnVizitPLStom_{EvnVizitPLStom_id}_ParodontogramWrap">
                <div class="data-table">
                    <div class="caption">
                        <h2>
                        <span id="EvnVizitPLStom_{EvnVizitPLStom_id}_toggleParodontogram" class="<?php
                        if (!empty($EvnUslugaParodontogram_id)) {
                            ?>collapsible<?php
                        } ?>">Пародонтограмма</span>
                        </h2>
                        <a id="EvnVizitPLStom_{EvnVizitPLStom_id}_addParodontogram" class="button icon" title="Новая" style="display: none">
                            <span style="padding-left: 2px;">Новая</span>
                        </a>
                    </div>
                </div>
                <div id="EvnVizitPLStom_{EvnVizitPLStom_id}_Parodontogram" style="display: block;"></div>
            </div>


			<div class="clear data-table"><div class="caption"><h2>Осмотр</h2></div></div>
			<div id="EvnVizitPLStom_protocol_{EvnVizitPLStom_id}">
				<div class="columns">

					<div class="left">
						<div id="EvnVizitPLStom_protocol_{EvnVizitPLStom_id}_content" class="content">
							
							{EvnVizitPLStom_protocol}
							
						</div>
					</div>

					<div class="right">
						<div id="EvnVizitPLStom_protocol_{EvnVizitPLStom_id}_toolbar" class="toolbar" style="display: none">
							<a id="EvnVizitPLStom_protocol_{EvnVizitPLStom_id}_search" class="button icon icon-select16" title="Выбор шаблона"><span></span></a>
							<a id="EvnVizitPLStom_protocol_{EvnVizitPLStom_id}_reload" class="button icon icon-template16" title="Восстановить шаблон"><span></span></a>
							<a id="EvnVizitPLStom_protocol_{EvnVizitPLStom_id}_clear" class="button icon icon-clear16" title="Очистить"><span></span></a>
							<a id="EvnVizitPLStom_protocol_{EvnVizitPLStom_id}_edit" style="display: none" class="button icon icon-edit16" title="Редактировать протокол осмотра"><span></span></a>
							<a id="EvnVizitPLStom_protocol_{EvnVizitPLStom_id}_del" style="display: none" class="button icon icon-delete16" title="Удалить протокол осмотра"><span></span></a>
							<a id="EvnVizitPLStom_protocol_{EvnVizitPLStom_id}_print" class="button icon icon-print16" title="Печать протокола осмотра"><span></span></a>
						</div>
					</div>

				</div>

				<div class="clear"></div>
				
			</div>

				{EvnDiagPLStomSop}

                {EvnPrescrStom}

				{EvnDirectionStom}
				
				{EvnDrug}
				
				{EvnUslugaStom}
				
				<div id="EvnVizitPLStom_{EvnVizitPLStom_id}_specificsHepatitis" class="data-table"<?php if (empty($isVisibleHepa)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
                        <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyHepatitis)) { ?>block<?php } else { ?>none<?php } ?>;">
                            <a id="EvnVizitPLStom_{EvnVizitPLStom_id}_addEvnNotifyHepatitis" class="button icon icon-add16" title="Создать Извещение о больном вирусным гепатитом"><span></span></a>
                        </div>
					<h2><span id="EvnVizitPLStom_{EvnVizitPLStom_id}_toggleDisplayMorbusHepatitis" class="collapsible">Специфика (гепатит)</span></h2>
					</div>
					<div id="MorbusHepatitisData_{EvnVizitPLStom_id}" style="display: none;"></div>
				</div>
				
				<div id="EvnVizitPLStom_{EvnVizitPLStom_id}_specificsCrazy" class="data-table"<?php if (empty($isVisibleCrazy)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
                        <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyCrazy) && !empty($isCrazy)) { ?>block<?php } else { ?>none<?php } ?>;">
                            <a id="EvnVizitPLStom_{EvnVizitPLStom_id}_addEvnNotifyCrazy" class="button icon icon-add16" title="Создать Извещение о больном психическим/наркологическим заболеванием"><span></span></a>
                        </div>
						<div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyCrazy) && !empty($isNarc)) { ?>block<?php } else { ?>none<?php } ?>;">
							<a id="EvnVizitPLStom_{EvnVizitPLStom_id}_addEvnNotifyNarc" class="button icon icon-add16" title="Создать Извещение о больном наркологическим заболеванием"><span></span></a>
						</div>
					<h2><span id="EvnVizitPLStom_{EvnVizitPLStom_id}_toggleDisplayMorbusCrazy" class="collapsible">Специфика (психиатрия/наркология)</span></h2>
					</div>
					<div id="MorbusCrazyData_{EvnVizitPLStom_id}" style="display: none;"></div>
				</div>

				<div id="EvnVizitPLStom_{EvnVizitPLStom_id}_specificsTub" class="data-table"<?php if (empty($isVisibleTub)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
                        <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyTub)) { ?>block<?php } else { ?>none<?php } ?>;">
                            <a id="EvnVizitPLStom_{EvnVizitPLStom_id}_addEvnNotifyTub" class="button icon icon-add16" title="Создать Извещение о больном туберкулезом"><span></span></a>
                        </div>
					<h2><span id="EvnVizitPLStom_{EvnVizitPLStom_id}_toggleDisplayMorbusTub" class="collapsible">Специфика (туберкулез)</span></h2>
					</div>
					<div id="MorbusTubData_{EvnVizitPLStom_id}" style="display: none;"></div>
				</div>
				<div id="EvnVizitPLStom_{EvnVizitPLStom_id}_specificsVener" class="data-table"<?php if (empty($isVisibleVener)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
					<h2><span id="EvnVizitPLStom_{EvnVizitPLStom_id}_toggleDisplayMorbusVener" class="collapsible">Специфика (венерология)</span></h2>
					</div>
					<div id="MorbusVenerData_{EvnVizitPLStom_id}" style="display: none;"></div>
				</div>

				<div id="EvnVizitPLStom_{EvnVizitPLStom_id}_specificsProf" class="data-table"<?php if (empty($isVisibleProf)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
						<div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyProf)) { ?>block<?php } else { ?>none<?php } ?>;">
							<a id="EvnVizitPLStom_{EvnVizitPLStom_id}_addEvnNotifyProf" class="button icon icon-add16" title="Создать Извещение по профзаболеванию"><span></span></a>
						</div>
						<h2><span id="EvnVizitPLStom_{EvnVizitPLStom_id}_toggleDisplayMorbusProf" class="collapsible">Специфика (Профзаболевания)</span></h2>
					</div>
					<div id="MorbusProfData_{EvnVizitPLStom_id}" style="display: none;"></div>
				</div>
				{FreeDocument}

            {EvnMediaData}
				
	   </div>
    </div>
</div>