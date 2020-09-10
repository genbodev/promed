<?php
	$is_morbus = (strtotime($EvnPLStom_setDate) >= getEvnPLStomNewBegDate());
?>
<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaStom_{EvnUsluga_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnUslugaStom_{EvnUsluga_id}_toolbar').style.display='none'">
    <td>
        <div id="EvnUslugaStom_{EvnUsluga_id}">
            <div id="EvnUslugaStom_{EvnUsluga_id}_content" class="content">{EvnUsluga_setDate}: <strong>{Usluga_Code}. {Usluga_Name}</strong>. 
            <?php if (!empty($EvnUsluga_Price)) { ?>Цена&nbsp;(УЕТ):&nbsp;{EvnUsluga_Price};<?php } ?> 
            <?php if (!empty($EvnUsluga_Kolvo)) { ?>Количество:&nbsp;{EvnUsluga_Kolvo};<?php } ?>
            <?php if (!empty($EvnUsluga_Summa)) { ?>Сумма&nbsp;(УЕТ):&nbsp;{EvnUsluga_Summa};<?php } ?>
            <?php
				if ( $is_morbus === true ) {
					if (!empty($Diag_Code)) { ?>Заболевание:&nbsp;{Diag_Code};<?php }
					if (!empty($Tooth_Code)) { ?> Номер&nbsp;зуба:&nbsp;{Tooth_Code}<?php }
				}
			 ?>
			<!-- Документ -->
			<?php if (!empty($isoper)) { ?>
				<?php if (!empty($EvnXml_id)) { ?> 
				<span class="collapsible" id="EvnUslugaStom_{EvnUsluga_id}_xml">Протокол&nbsp;операции...</span>
				<?php } elseif ($accessType != 'view') { ?> 
				<span class="collapsible" id="EvnUslugaStom_{EvnUsluga_id}_xml">Создать&nbsp;протокол...</span>
				<?php } ?>
			<?php } else { ?> 
				<?php if (!empty($EvnXml_id)) { ?> 
				<span class="collapsible" id="EvnUslugaStom_{EvnUsluga_id}_xml">Протокол&nbsp;выполнения&nbsp;услуги...</span>
				<?php } elseif ($accessType != 'view') { ?> 
				<span class="collapsible" id="EvnUslugaStom_{EvnUsluga_id}_xml">Создать&nbsp;протокол...</span>
				<?php } ?>
			<?php } ?>
			<!-- // Документ -->
            </div>
        </div>
    </td>
    <td class="toolbar">
        <div id="EvnUslugaStom_{EvnUsluga_id}_toolbar" class="toolbar">
            <a id="EvnUslugaStom_{EvnUsluga_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a>
<?php
	if ( $is_morbus === false ) {
?>
            <a id="EvnUslugaStom_{EvnUsluga_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
<?php
	}
?>
	        <?php
	    if (!empty($EvnUslugaStom_isParodontogram) &&
	        (!empty($EvnUslugaStom_hasParodontogram) || $accessType != 'view')
        ) {
		    ?><a id="EvnUslugaStom_{EvnUsluga_id}_parodontogram" class="button icon icon-template16" title="Пародонтограмма"><span></span></a><?php
	    }
	        ?>
<?php
	if ( $is_morbus === false ) {
?>
            <a id="EvnUslugaStom_{EvnUsluga_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a>
<?php
	}
?>
        </div>
    </td>
</tr>
