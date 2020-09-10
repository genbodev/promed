<tr class="list-item" <?php if ($EvnStickBase_IsDelQueue == 2) { echo 'style="background: #DAD6D3;"'; } ?> onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnStick_{EvnStick_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnStick_{EvnStick_id}_toolbar').style.display='none'">
    <td>
        <div id="EvnStick_{EvnStick_id}">
            <div id="EvnStick_{EvnStick_id}_content">
				{StickType_Name} {StickWorkType_Name} {StickOrder_Name} <span style="color: darkblue;">{EvnStick_Ser} {EvnStick_Num} {EvnStick_setDate}</span> {EvnStick_ParentTypeName} {EvnStick_ParentNum} {EvnStick_ParentDate}
				<?php if(in_array($evnStickType, array(1,2))) { ?>
					{StickLeaveType_OpenName}
					<?php if(!empty($StickLeaveType_id)) { ?><br/>{StickLeaveType_Name}, <span style="color: darkblue;">{EvnStick_disDate}</span><?php } ?>
				<?php } ?>
				<?php if(getRegionNick() != 'kz') { ?>
					<?php if(!empty($StickFSSType_Name)) { ?><br/>Состояние ЛВН в ФСС: {StickFSSType_Name}<?php } ?>
					<?php if(!empty($StickFSSDataStatus_Name)) { ?><br/>Статус запроса в ФСС: {StickFSSDataStatus_Name}<?php } ?>
				<?php } ?>
			</div>
        </div>
    </td>
    <td class="toolbar">
        <div id="EvnStick_{EvnStick_id}_toolbar" class="toolbar">
        	<?php if (
        		!empty($EvnStick_isELN) && empty($requestExist)
			) { ?>
				<a id="EvnStick_{EvnStick_id}_stickfssdata" class="button icon icon-checked16" title="Создать запрос в ФСС"><span></span></a>
			<?php } ?>
            <a id="EvnStick_{EvnStick_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
			<?php if (!empty($accessType) && $accessType == 'edit') { ?>
				<a id="EvnStick_{EvnStick_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
			<?php } ?>
			<?php if ($EvnStickBase_IsDelQueue == 2) { ?>
				<a id="EvnStick_{EvnStick_id}_undodelete" class="button icon icon-refresh16" title="Восстановить"><span></span></a>
			<?php } else if ((!empty($delAccessType) && $delAccessType == 'edit') || (!empty($evnStickType) && $evnStickType == '3')) { ?>
				<a id="EvnStick_{EvnStick_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a>
			<?php } else if (!empty($cancelAccessType) && $cancelAccessType == 'edit') { ?>
				<a id="EvnStick_{EvnStick_id}_cancel" class="button icon icon-action_cancel" title="Аннулировать"><span></span></a>
			<?php } ?>
        </div>
    </td>
</tr>
