<tr id="EvnPLDispInfo_{EvnPLDisp_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispInfo_{EvnPLDisp_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnPLDispInfo_{EvnPLDisp_id}_toolbar').style.display='none'">
    <td>{DispClass_Name}</td>
    <td>{EvnPLDisp_setDate}</td>
    <td>{EvnPLDisp_disDate}</td>
    <td>{Lpu_Nick}</td>
    <td>{HealthKind_Name}</td>
	<td>{Diag_FullName}</td>
    <td class="toolbar">
        <div id="EvnPLDispInfo_{EvnPLDisp_id}_toolbar" class="toolbar">
            <?php if(!$isMseDepers) {?><a id="EvnPLDispInfo_{EvnPLDisp_id}_view" class="button icon icon-view16" title="Просмотр"><span></span></a><?php } ?>
        </div>
    </td>
</tr>
