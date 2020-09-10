<div id="EvnVizitPL_head_{EvnVizitPL_id}" class="columns">

	<div class="section frame evn_visit_pl"  onmouseover="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnVizitPL_data_{EvnVizitPL_id}_toolbar').style.display='block'; document.getElementById('EvnVizitPL_protocol_{EvnVizitPL_id}_toolbar').style.display='block'; } " onmouseout="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnVizitPL_data_{EvnVizitPL_id}_toolbar').style.display='none'; document.getElementById('EvnVizitPL_protocol_{EvnVizitPL_id}_toolbar').style.display='none'; }">
		<?php if (!empty($AlertReg_Msg)) { ?><p style="font-weight:bold; color: #DD3333">{AlertReg_Msg}</p><?php } ?>
		<div class="header_info">
			<div class="columns">
				<div class="left">Создан: {insDT} {ins_Name}
				<br /><!--span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayDocVersions" class="link" style="font-size: 8pt" title="Развернуть">Предыдущие версии документа</span-->
				<div id="DocVersions_{EvnVizitPL_id}"><!-- Предыдущие версии документа --></div>
				</div>
				<div class="right"><div id="EvnVizitPL_{EvnVizitPL_id}_toolbar" class="toolbar">
                <a class="button icon icon-print16" title="Печать КЛУ при ЗНО" onclick="printControlCardZno({EvnVizitPL_id})"><span></span></a>
                <?php if (getRegionNick() == 'ekb') { ?> <a class="button icon icon-print16" title="Печать выписки при онкологии" onclick="printControlCardOnko({EvnVizitPL_id})"><span></span></a> <?php } ?>
				</div></div>
			</div>
		</div>
		<div class="clear line"><hr></div>
		<div id="EvnVizitPL_{EvnVizitPL_id}">

			{EvnVizitPL_data}

			<div class="clear line"><hr></div>

			<div id="EvnVizitPL_protocol_{EvnVizitPL_id}">
				<div class="columns">

					<div class="left">
						<div id="EvnVizitPL_protocol_{EvnVizitPL_id}_content" class="content">

							{EvnVizitPL_protocol}

						</div>
					</div>

					<div class="right">
						<div id="EvnVizitPL_protocol_{EvnVizitPL_id}_toolbar" class="toolbar" style="display: none">
							<a id="EvnVizitPL_protocol_{EvnVizitPL_id}_search" class="button icon icon-select16" title="Выбор шаблона"><span></span></a>
							<a id="EvnVizitPL_protocol_{EvnVizitPL_id}_reload" class="button icon icon-template16" title="Восстановить шаблон"><span></span></a>
							<a id="EvnVizitPL_protocol_{EvnVizitPL_id}_clear" class="button icon icon-clear16" title="Очистить"><span></span></a>
							<a id="EvnVizitPL_protocol_{EvnVizitPL_id}_edit" style="display: none" class="button icon icon-edit16" title="Редактировать протокол осмотра"><span></span></a>
							<a id="EvnVizitPL_protocol_{EvnVizitPL_id}_del" style="display: none" class="button icon icon-delete16" title="Удалить протокол осмотра"><span></span></a>
							<a id="EvnVizitPL_protocol_{EvnVizitPL_id}_print" class="button icon icon-print16" title="Печать протокола осмотра"><span></span></a>
							<div class="emd-here" data-objectname="EvnXml" data-objectid="{EvnXml_id}" data-issigned="{EvnXml_IsSigned}"></div>
						</div>
					</div>

				</div>

				<div class="clear"></div>

			</div>

				{EvnDiagPL}

				<?php echo ((true)?'{EvnPrescrPolka}':''); ?>

				{EvnDirection}

				{EvnDrug}

				{EvnUsluga}

				{EvnRecept}

				{EvnReceptGeneral}

                <?php if ($EvnReceptKardio_isVisible) { ?>
                    {EvnReceptKardio}
                <?php } ?>

				<div id="EvnVizitPL_{EvnVizitPL_id}_specificsHepatitis" class="data-table"<?php if (empty($isVisibleHepa)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
                        <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyHepatitis)) { ?>block<?php } else { ?>none<?php } ?>;">
                            <a id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyHepatitis" class="button icon icon-add16" title="Создать Извещение о больном вирусным гепатитом"><span></span></a>
                        </div>
					<h2><span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayMorbusHepatitis" class="collapsible">Специфика (гепатит)</span></h2>
					</div>
					<div id="MorbusHepatitisData_{EvnVizitPL_id}" style="display: none;"></div>
				</div>

				<div id="EvnVizitPL_{EvnVizitPL_id}_specificsOnko" class="data-table"<?php if (empty($isVisibleOnko) || !$isVisibleOnko) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
                        <div style="float:right; display: <?php if (empty($isDisabledAddEvnOnkoNotify) && !empty($regionNick) && $regionNick == 'kz') { ?>block<?php } else { ?>none<?php } ?>;">
                            <a id="EvnVizitPL_{EvnVizitPL_id}_addEvnOnkoNotify" class="button icon icon-add16" title="Создать Извещение о больном с впервые в жизни установленным диагнозом злокачественного новообразования"><span></span></a>
                        </div>
					<h2><span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayMorbusOnko" class="collapsible">Специфика (онкология)</span></h2>
					</div>
					<div id="MorbusOnkoData_{EvnVizitPL_id}" style="display: none;"></div>
				</div>

				<div id="EvnVizitPL_{EvnVizitPL_id}_specificsPregn" class="data-table"<?php if (empty($isVisiblePregnancy)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
					<h2><span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayMorbusPregnancy" class="collapsible">Специфика (беременность)</span></h2>
					</div>
					<div id="MorbusPregnancyData_{EvnVizitPL_id}" style="display: none;"></div>
				</div>

				<div id="EvnVizitPL_{EvnVizitPL_id}_specificsCrazy" class="data-table"<?php if (empty($isVisibleCrazy)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
                        <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyCrazy) && !empty($isCrazy)) { ?>block<?php } else { ?>none<?php } ?>;">
                            <a id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyCrazy" class="button icon icon-add16" title="Создать Извещение о больном психическим заболеванием"><span></span></a>
                        </div>
                        <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyCrazy) && !empty($isNarc)) { ?>block<?php } else { ?>none<?php } ?>;">
                            <a id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyNarc" class="button icon icon-add16" title="Создать Извещение о больном наркологическим заболеванием"><span></span></a>
                        </div>
					<h2><span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayMorbusCrazy" class="collapsible">Специфика (психиатрия/наркология)</span></h2>
					</div>
					<div id="MorbusCrazyData_{EvnVizitPL_id}" style="display: none;"></div>
				</div>

				<div id="EvnVizitPL_{EvnVizitPL_id}_specificsTub" class="data-table"<?php if (empty($isVisibleTub)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
                        <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyTub)) { ?>block<?php } else { ?>none<?php } ?>;">
                            <a id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyTub" class="button icon icon-add16" title="Создать Извещение о больном туберкулезом"><span></span></a>
                        </div>
					<h2><span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayMorbusTub" class="collapsible">Специфика (туберкулез)</span></h2>
					</div>
					<div id="MorbusTubData_{EvnVizitPL_id}" style="display: none;"></div>
				</div>

				<div id="EvnVizitPL_{EvnVizitPL_id}_specificsVener" class="data-table"<?php if (empty($isVisibleVener)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
					<h2><span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayMorbusVener" class="collapsible">Специфика (венерология)</span></h2>
					</div>
					<div id="MorbusVenerData_{EvnVizitPL_id}" style="display: none;"></div>
				</div>
				<div id="EvnVizitPL_{EvnVizitPL_id}_specificsNephro" class="data-table"<?php if (empty($isVisibleNephro)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
                        <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyNephro)) { ?>block<?php } else { ?>none<?php } ?>;">
                            <a id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyNephro" class="button icon icon-add16" title="Создать Извещение по нефрологии"><span></span></a>
                        </div>
					<h2><span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayMorbusNephro" class="collapsible">Специфика (Нефрология)</span></h2>
					</div>
					<div id="MorbusNephroData_{EvnVizitPL_id}" style="display: none;"></div>
				</div>
				<div id="EvnVizitPL_{EvnVizitPL_id}_specificsProf" class="data-table"<?php if (empty($isVisibleProf)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
						<div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyProf)) { ?>block<?php } else { ?>none<?php } ?>;">
							<a id="EvnVizitPL_{EvnVizitPL_id}_addEvnNotifyProf" class="button icon icon-add16" title="Создать Извещение по профзаболеванию"><span></span></a>
						</div>
						<h2><span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayMorbusProf" class="collapsible">Специфика (Профзаболевания)</span></h2>
					</div>
					<div id="MorbusProfData_{EvnVizitPL_id}" style="display: none;"></div>
				</div>
            <?php if (getRegionNick() != 'kz') : ?>
				<div id="EvnVizitPL_{EvnVizitPL_id}_specificsPalliat" class="data-table"<?php if (empty($isVisiblePalliat)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
						<h2><span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayMorbusPalliat" class="collapsible">Специфика (паллиативная помощь)</span></h2>
					</div>
					<div id="MorbusPalliatData_{EvnVizitPL_id}" style="display: none;"></div>
				</div>
            <?php endif; ?>
				<div id="EvnVizitPL_{EvnVizitPL_id}_specificsGeriatrics" class="data-table"<?php if (empty($isVisibleGeriatrics)) { ?> style="display: none;"<?php } ?>>
					<div class="caption">
						<h2><span id="EvnVizitPL_{EvnVizitPL_id}_toggleDisplayMorbusGeriatrics" class="collapsible">Специфика (гериатрия)</span></h2>
					</div>
					<div id="MorbusGeriatricsData_{EvnVizitPL_id}" style="display: none;"></div>
				</div>
				{FreeDocument}
				<?php if ($regionNick != 'kz') { ?>
                {EvnPLDispScreenOnko}
				<?php } ?>
				{EvnXmlEpikriz}

				{RepositoryObserv}
			
	   </div>

	</div>
</div>