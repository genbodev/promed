<tr class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagPL_{EvnDiagPL_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnDiagPL_{EvnDiagPL_id}_toolbar').style.display='none'">
    <td>
        <div id="EvnDiagPL_{EvnDiagPL_id}">
            <div id="EvnDiagPL_{EvnDiagPL_id}_content">{Diag_Code}. {Diag_Name}. <!--{DeseaseType_Name}. {EvnDiagPL_setDate} {MedPersonal_Fin}/{LpuSection_Name}--></div>
        </div>
    </td>
    <td class="toolbar">
        <div id="EvnDiagPL_{EvnDiagPL_id}_toolbar" class="toolbar">
            <a id="EvnDiagPL_{EvnDiagPL_id}_edit" class="button icon icon-edit16" title="Редактировать диагноз"><span></span></a>
            <a id="EvnDiagPL_{EvnDiagPL_id}_delete" class="button icon icon-delete16" title="Удалить диагноз"><span></span></a>
        </div>
    </td>
</tr>
