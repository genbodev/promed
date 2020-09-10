<div class="section frame evn_visit_pl"  onmouseover="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnSection_data_{EvnSection_id}_toolbar').style.display='block'; } " onmouseout="if (isMouseLeaveOrEnter(event, this)) { document.getElementById('EvnSection_data_{EvnSection_id}_toolbar').style.display='none'; }">
	<?php if (!empty($AlertReg_Msg)) { ?><p style="font-weight:bold; color: #DD3333">{AlertReg_Msg}</p><?php } ?>
	<?php if($EvnSection_id != $EvnSection_pid)
	{
	?>
     <div class="header_info">
		<div class="columns">
			<div class="left">Создан: {insDT} {ins_Name}
			<br /><!--span id="EvnSection_{EvnSection_id}_toggleDisplayDocVersions" class="link" style="font-size: 8pt" title="Развернуть">Предыдущие версии документа</span-->
			<div id="DocVersions_{EvnSection_id}"><!-- Предыдущие версии документа --></div>
			</div>
            <div class="right"><div id="EvnSection_{EvnSection_id}_toolbar" class="toolbar">
			</div></div>
		</div>
	</div>
    <div class="clear line"><hr></div>
	<?php
	}
	?>
	<div id="EvnSection_{EvnSection_id}">

        {EvnSection_data}

        <div class="clear line"><hr></div>

            <?php if ( ($EvnSection_id != $EvnSection_pid) /*&& (in_array(getRegionNumber(), array(2)))*/ ) { ?>
			{EvnSectionNarrowBed}
			<?php } ?>

            {EvnDrug}

            <?php if ($EvnReceptKardio_isVisible) { ?>
            {EvnReceptKardio}
		    <?php } ?>

            <?php
            if(getRegionNumber() == 63) {
                if (FALSE === \stripos($LpuSection_Name, 'Приемное отделение')) {
                    echo '{EvnUslugaStac}';
                }
            }
                else {
                    echo '{EvnUslugaStac}';
            }
            ?>
            {EvnReanimatPeriod} <?php //BOB - 19.04.2017 ?>
			<?php if (!empty($EvnClass_id) && $EvnClass_id == 32) { ?>
 			<div id="EvnSection_{EvnSection_id}_specificsHepatitis" class="data-table"<?php if (empty($isVisibleHepa)) { ?> style="display: none;"<?php } ?>>
				<div class="caption">
                    <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyHepatitis)) { ?>block<?php } else { ?>none<?php } ?>;">
                        <a id="EvnSection_{EvnSection_id}_addEvnNotifyHepatitis" class="button icon icon-add16" title="Создать Извещение о больном вирусным гепатитом"><span></span></a>
                    </div>
				<h2><span id="EvnSection_{EvnSection_id}_toggleDisplayMorbusHepatitis" class="collapsible">Специфика (гепатит)</span></h2>
				</div>
				<div id="MorbusHepatitisData_{EvnSection_id}" style="display: none;"></div>
			</div>

			<div id="EvnSection_{EvnSection_id}_specificsOnko" class="data-table"<?php if (empty($isVisibleOnko)) { ?> style="display: none;"<?php } ?>>
				<div class="caption">
                    <div style="float:right; display: <?php if (empty($isDisabledAddEvnOnkoNotify) && !empty($regionNick) && $regionNick == 'kz') { ?>block<?php } else { ?>none<?php } ?>;">
                        <a id="EvnSection_{EvnSection_id}_addEvnOnkoNotify" class="button icon icon-add16" title="Создать Извещение о больном с впервые в жизни установленным диагнозом злокачественного новообразования"><span></span></a>
                    </div>
				<h2><span id="EvnSection_{EvnSection_id}_toggleDisplayMorbusOnko" class="collapsible">Специфика (онкология)</span></h2>
				</div>
				<div id="MorbusOnkoData_{EvnSection_id}" style="display: none;"></div>
			</div>

			<div id="EvnSection_{EvnSection_id}_specificsCrazy" class="data-table"<?php if (empty($isVisibleCrazy)) { ?> style="display: none;"<?php } ?>>
				<div class="caption">
                    <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyCrazy) && !empty($isCrazy)) { ?>block<?php } else { ?>none<?php } ?>;">
                        <a id="EvnSection_{EvnSection_id}_addEvnNotifyCrazy" class="button icon icon-add16" title="Создать Извещение о больном психическим/наркологическим заболеванием"><span></span></a>
                    </div>
					<div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyCrazy) && !empty($isNarc)) { ?>block<?php } else { ?>none<?php } ?>;">
						<a id="EvnSection_{EvnSection_id}_addEvnNotifyNarc" class="button icon icon-add16" title="Создать Извещение о больном наркологическим заболеванием"><span></span></a>
					</div>
				<h2><span id="EvnSection_{EvnSection_id}_toggleDisplayMorbusCrazy" class="collapsible">Специфика (психиатрия/наркология)</span></h2>
				</div>
				<div id="MorbusCrazyData_{EvnSection_id}" style="display: none;"></div>
			</div>
			
			<div id="EvnSection_{EvnSection_id}_specificsTub" class="data-table"<?php if (empty($isVisibleTub)) { ?> style="display: none;"<?php } ?>>
				<div class="caption">
                    <div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyTub)) { ?>block<?php } else { ?>none<?php } ?>;">
                        <a id="EvnSection_{EvnSection_id}_addEvnNotifyTub" class="button icon icon-add16" title="Создать Извещение о больном туберкулезом"><span></span></a>
                    </div>
					<h2><span id="EvnSection_{EvnSection_id}_toggleDisplayMorbusTub" class="collapsible">Специфика (туберкулез)</span></h2>
				</div>
				<div id="MorbusTubData_{EvnSection_id}" style="display: none;"></div>
			</div>

			<div id="EvnSection_{EvnSection_id}_specificsVener" class="data-table"<?php if (empty($isVisibleVener)) { ?> style="display: none;"<?php } ?>>
				<div class="caption">
				<h2><span id="EvnSection_{EvnSection_id}_toggleDisplayMorbusVener" class="collapsible">Специфика (венерология)</span></h2>
				</div>
				<div id="MorbusVenerData_{EvnSection_id}" style="display: none;"></div>
			</div>

			<div id="EvnSection_{EvnSection_id}_specificsProf" class="data-table"<?php if (empty($isVisibleProf)) { ?> style="display: none;"<?php } ?>>
				<div class="caption">
					<div style="float:right; display: <?php if (empty($isDisabledAddEvnNotifyProf)) { ?>block<?php } else { ?>none<?php } ?>;">
						<a id="EvnSection_{EvnSection_id}_addEvnNotifyProf" class="button icon icon-add16" title="Создать Извещение о профзаболевании"><span></span></a>
					</div>
					<h2><span id="EvnSection_{EvnSection_id}_toggleDisplayMorbusProf" class="collapsible">Специфика (Профзаболевания)</span></h2>
				</div>
				<div id="MorbusProfData_{EvnSection_id}" style="display: none;"></div>
			</div>

			<div id="EvnSection_{EvnSection_id}_specificsPalliat" class="data-table"<?php if (empty($isVisiblePalliat)) { ?> style="display: none;"<?php } ?>>
				<div class="caption">
					<h2><span id="EvnSection_{EvnSection_id}_toggleDisplayMorbusPalliat" class="collapsible">Специфика (паллиативная помощь)</span></h2>
				</div>
				<div id="MorbusPalliatData_{EvnSection_id}" style="display: none;"></div>
			</div>

			<div id="EvnSection_{EvnSection_id}_specificsGeriatrics" class="data-table"<?php if (empty($isVisibleGeriatrics)) { ?> style="display: none;"<?php } ?>>
				<div class="caption">
					<h2><span id="EvnSection_{EvnSection_id}_toggleDisplayMorbusGeriatrics" class="collapsible">Специфика (гериатрия)</span></h2>
				</div>
				<div id="MorbusGeriatricsData_{EvnSection_id}" style="display: none;"></div>
			</div>
			<?php } ?>

        {EvnXmlProtokol}

		{RepositoryObserv}

        <?php
        if(getRegionNumber() == 63) {
            if (FALSE === \stripos($LpuSection_Name, 'Приемное отделение')) {
                echo '{EvnXmlRecord}';
                echo '{EvnXmlEpikriz}';
                echo '{EvnXmlOther}';
            }
        }
        else {
            echo '{EvnXmlRecord}';
            echo '{EvnXmlEpikriz}';
            echo '{EvnXmlOther}';
        }
        ?>

   </div>

</div>

