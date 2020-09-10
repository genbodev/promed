<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaStac_{EvnUsluga_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaStac_{EvnUsluga_id}_toolbar').style.display='none'">
    <?php if($EvnClass_SysNick!=''){?>
	<td class="vPrint vPrint-none" ><input pid="{EvnUsluga_pid}" type="checkbox" onclick="Ext.getCmp('PersonEmkForm').changeCountPrint()" operProtocol="{EvnXml_id}" class="checkPrint chkEvnUsluga" print="{EvnClass_SysNick}" value="{EvnUsluga_id}"/></td>
	<?php }else{echo"<td class='vPrint vPrint-none'></td>";}?>
	<td>
        <div id="EvnUslugaStac_{EvnUsluga_id}">
            <div id="EvnUslugaStac_{EvnUsluga_id}_content" class="content">{EvnUsluga_setDate} {EvnClass_Name}: <strong>{Usluga_Code}. {Usluga_Name}</strong>.
	        <?php if (!empty($EvnUsluga_Kolvo)) { ?>Количество: {EvnUsluga_Kolvo}<?php } ?>
			<!-- Документ -->
			<?php if (!$isMseDepers) { ?>
			<?php if ($EvnClass_SysNick == 'EvnUslugaOper') { ?> 
				<?php if (!empty($EvnXml_id)) { ?> 
				<span class="collapsible" id="EvnUslugaStac_{EvnUsluga_id}_xml">Протокол&nbsp;операции...</span>
				<?php } elseif ($accessType != 'view') { ?> 
				<span class="collapsible" id="EvnUslugaStac_{EvnUsluga_id}_xml">Создать&nbsp;протокол...</span>
				<?php } ?>
				<?php if (!empty($EvnXmlNarcosis_id)) { ?> 
				<span class="collapsible" id="EvnUslugaStac_{EvnUsluga_id}_narcosis">Протокол&nbsp;анестезии...</span>
				<?php } elseif ($accessType != 'view') { ?> 
				<span class="collapsible" id="EvnUslugaStac_{EvnUsluga_id}_narcosis">Создать&nbsp;протокол&nbsp;анестезии...</span>
				<?php } ?>
			<?php } elseif ($EvnClass_SysNick == 'EvnUslugaCommon') { ?> 
				<?php if (!empty($EvnXml_id)) { ?> 
				<span class="collapsible" id="EvnUslugaStac_{EvnUsluga_id}_xml">Протокол&nbsp;выполнения&nbsp;услуги...</span>
				<?php } elseif ($accessType != 'view') { ?> 
				<span class="collapsible" id="EvnUslugaStac_{EvnUsluga_id}_xml">Создать&nbsp;протокол...</span>
				<?php } ?>
			<?php } elseif (!empty($EvnXml_id)) { ?> 
			<span class="collapsible" id="EvnUslugaStac_{EvnUsluga_id}_xml">Результаты...</span>
			<?php } ?>
			<?php } ?>
			<!-- // Документ -->
            </div>
        </div>
    </td>
    <td class="toolbar">
        <div id="EvnUslugaStac_{EvnUsluga_id}_toolbar" class="toolbar">
			<?php if (!$isMseDepers) { ?>
			<?php if ($EvnClass_SysNick == 'EvnUslugaPar') { ?><a id="EvnUslugaStac_{EvnUsluga_id}_editsimple" class="button icon icon-edit16" title="Редактировать привязку услуги"><span></span></a><?php } ?>
	        <a id="EvnUslugaStac_{EvnUsluga_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
            <a id="EvnUslugaStac_{EvnUsluga_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
			<a id="EvnUslugaStac_{EvnUsluga_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a>
			<?php } ?>
        </div>
    </td>
</tr>
