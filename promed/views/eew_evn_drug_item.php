<?php
if (!empty($IsGroup)) {
	?>
<div style="clear: both;"><b>{Drug_Code} {Drug_Name}</b> Общее количество: <b>{EvnDrug_Kolvo}</b>
<span class="link" id="EvnDrug_{EvnDrug_id}_toggleDisplayList">Показать</span>
</div>
<table id="EvnDrug_{EvnDrug_id}_List" style="display: none;">
    <col class="first last" />
    <col class="toolbar"/>
	<?php
} else {
	?>
    <tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDrug_{EvnDrug_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDrug_{EvnDrug_id}_toolbar').style.display='none'">
        <td>
            <div id="EvnDrug_{EvnDrug_id}">
                <div id="EvnDrug_{EvnDrug_id}_content"><b>{EvnDrug_setDate}</b> Количество: <b>{EvnDrug_Kolvo}</b></div>
            </div>
        </td>
        <td class="toolbar">
            <div id="EvnDrug_{EvnDrug_id}_toolbar" class="toolbar">
                <a id="EvnDrug_{EvnDrug_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
                <a id="EvnDrug_{EvnDrug_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a>
            </div>
        </td>
    </tr>
    {addHtml}
	<?php
}
?>
