<?php
$queueWarning = '';
$days = null;
$EXDLLStyle = array(array('10px', '0px', '10px', '0px'), array('0px', '0px', '0px', '0px')); // индекс 0 - если есть данные, 1 - если нет
if (!empty($DirType_Code) && in_array($DirType_Code, array(12, 3)) && !empty($_SESSION['setting']['server']['promed_waiting_period_polka'])) { // На поликлинический прием и На консультацию
	$days = $_SESSION['setting']['server']['promed_waiting_period_polka'];
} else if (!empty($DirType_Code) && in_array($DirType_Code, array(1, 5)) && !empty($_SESSION['setting']['server']['promed_waiting_period_stac'])) { // На госпитализацию экстренную и На госпитализацию плановую
	$days = $_SESSION['setting']['server']['promed_waiting_period_stac'];
}

if (
	getRegionNick() == 'msk' && 
	$Org_IsNotForSystem == 2 && 
	$DirType_Code == 13 && 
	empty($EvnUslugaTelemed_id)
) {
	$RecWhat = "<span title=\"Необходимо заполнить информацию о выполнение консультации\" style=\"color: red\">{$RecWhat}</span>";
}

if (!empty($days) && !empty($EvnQueue_Days) && $EvnQueue_Days > $days) {
	$daysText = $days.' '.ru_word_case('день','дня','дней', $days);
	$queueWarning = "<img src='/img/icons/warn_red_round12.png' ext:qtip='Направление с периодом ожидания более {$daysText}!' /> ";
}
?>
<!--
<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDirection_{EvnDirection_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDirection_{EvnDirection_id}_toolbar').style.display='none'">
    <td>
        <div id="EvnDirection_{EvnDirection_id}">
            <div id="EvnDirection_{EvnDirection_id}_content">{EvnDirection_setDate} / Электронное направление:<strong> {EvnDirection_Num}</strong> / <span id="timetable_{timetable}_{timetable_id}"><span id="timetable_{timetable}_{timetable_id}_go" class="link" title="перейти по записи">{DirType_Name}</span></span> / {Lpu_Nick} / {LpuSectionProfile_Code}.{LpuSectionProfile_Name} / {RecDate}</div>
        </div>
    </td>
    <td class="toolbar">
        <div id="EvnDirection_{EvnDirection_id}_toolbar" class="toolbar">
            <a id="EvnDirection_{EvnDirection_id}_print" class="button icon icon-print16" title="Печать направления"><span></span></a>
            <a id="EvnDirection_{EvnDirection_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
            <a id="EvnDirection_{EvnDirection_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a>
        </div>
    </td>
</tr>
-->                                 
<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('timetable_{timetable}_{timetable_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('timetable_{timetable}_{timetable_id}_toolbar').style.display='none'">
	<td class='vPrint vPrint-none' width="1"><input pid="{timetable_pid}" type="checkbox" onclick="Ext.getCmp('PersonEmkForm').changeCountPrint()" class="checkPrint chkEvnDirection" print="EvnDirection" value="{EvnDirection_id}"/></td>
	<td class="content">
		<div id="timetable_{timetable}_{timetable_id}"><span id="timetable_{timetable}_{timetable_id}_go" class="link" title="Просмотр направления"><?php echo $RecWhat; ?></span> {RecTo} / {RecDate} <?php echo $queueWarning; ?><span id="timetable_{timetable}_{timetable_id}_num">{EvnDirection_Num}</span>
		<?php
		if (!empty($EvnStatus_id)) {
			if (in_array($EvnStatus_id, array(12,13))) {
				?>/ <span style="color: red;">{EvnStatus_Name} {EvnDirection_statusDate}</span> <?php
			} else {
				?>/ <span>{EvnStatus_Name} {EvnDirection_statusDate}</span> <?php
			}
		}
		if (!empty($EvnStatusCause_Name) && in_array($EvnStatus_id, array(12,13))) {
			?>/ по причине: <span style="color: red;">{EvnStatusCause_Name}</span> <?php
		}
		if (!empty($EvnStatus_epvkName)) {
			?>/ {EvnStatus_epvkName} <?php
		}
		if (!empty($DirType_Code) && 13 == $DirType_Code) {
		?></div>
		<div><?php
			if (empty($EvnXmlDir_id)) {
				?> <span id="timetable_{timetable}_{timetable_id}_addBlankDir" class="link"
						 title="Открыть форму Бланк направления: Добавление">Заполнить бланк</span> <?php
			} else {
				?> <span id="timetable_{timetable}_{timetable_id}_editBlankDir" class="link"
						 title="Открыть форму Бланк направления: Просмотр/редактирование">Просмотр/редактирование бланка</span> <?php
			}?>

            <div id="EvnXmlDirectionLinkList_{EvnDirection_id}" style="padding: <?php echo implode(' ', $EXDLLStyle[(int) empty($EvnXmlDirectionLink)]) ?> ">{EvnXmlDirectionLink}</div>

			<?php if ( empty($EvnStatus_id) || ! in_array($EvnStatus_id, array(12,13)) ): ?>
            <div id="EvnDirection_{EvnDirection_id}" style="margin: 0 0 0 0;">
                <span id="EvnDirection_{EvnDirection_id}_addDoc" class="link"
                      title="Открыть форму Список документов">Добавить документ</span>
            </div>

            <?php endif;
			if (getRegionNick() == 'msk' && isCurrentARMType("remoteconsultcenter") && empty($RCP_FilePath)): ?>
				<span id="EvnDirection_{EvnDirection_id}_uploadFile" class="link"
					  title="Кликните для загрузки документа">Добавить протокол</span>
			<?php endif;
			if (getRegionNick() == 'msk' && isCurrentARMType("remoteconsultcenter") && !empty($RCP_FilePath)):
				$tmp = explode('/', $RCP_FilePath);
				$filename = $tmp[count($tmp)-1];
				?>
				<div style="margin: 0 0 0 0;">
					<a class="RCP_file_name" datapath="{RCP_FilePath}"><?php echo $filename; ?></a>
					<a id="EvnDirectionStac_{EvnDirection_id}_deleteProtocol" dataid="{RCP_id}" class="link EvnDirection_deleteProtocol">Удалить протокол</a>
				</div>
			<?php endif;
		} ?>
		</div>
	</td>
	<td class="toolbar">
		
		<div id="timetable_{timetable}_{timetable_id}_toolbar" class="toolbar">
			<?php if (
				getRegionNick() == 'msk' && 
				$Org_IsNotForSystem == 2 && 
				$DirType_Code == 13 && 
				empty($ServiceListPackage_id)
			) { ?>
				<a id="timetable_{timetable}_{timetable_id}_eutedit" class="button icon icon-edit16" title="Редактировать выполнение услуги"><span></span></a>
			<?php } ?>
			<?php if (in_array(getRegionNick(), ['perm', 'vologda']) && isset($DirType_Code) && $DirType_Code == 8 && isset($EvnStatus_epvkSysNick) && in_array($EvnStatus_epvkSysNick, ['New', 'Rework'])) { ?>
			<a id="timetable_{timetable}_{timetable_id}_directZav" class="button icon icon-add16" title="Направить зав. отделением"><span></span></a>
			<?php } ?>
			<!--<a <?php /*if($EvnDirection_IsAuto==2) echo 'style="display: none;" ';*/ ?>id="timetable_{timetable}_{timetable_id}_print" class="button icon icon-print16" title="Печать направления"><span></span></a>
            --><a id="timetable_{timetable}_{timetable_id}_printselect" class="button icon icon-print16" title="Печать"><span></span></a>
			<!--a id="timetable_{timetable}_{timetable_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a-->
			<?php
			if (empty($EvnStatus_id) || !in_array($EvnStatus_id, array(12,13))) {
				?><a id="timetable_{timetable}_{timetable_id}_delete" class="button <?php if ( ! $allowCancel ): ?> disabled <?php endif; ?> icon icon-delete16" title="Отменить"><span></span></a><?php
			}
			?>
			<?php
				if (!empty($EvnPrescrMse_id)) {
			?>
			<div class="emd-here" data-objectname="EvnPrescrMse" data-objectid="{EvnPrescrMse_id}" data-issigned="{EvnPrescrMse_IsSigned}"></div>
			<?php
				} else {
			?>
			<div class="emd-here" data-objectname="EvnDirection" data-objectid="{EvnDirection_id}" data-issigned="{EvnDirection_IsSigned}" data-minsigncount="{EvnDirection_MinSignCount}" data-signcount="{EvnDirection_SignCount}"></div>
			<?php
				}
			?>
		</div>
	</td>
</tr>