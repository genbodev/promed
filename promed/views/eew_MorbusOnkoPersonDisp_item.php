<?php
/**
 * @package      MorbusOnko
 * @author       Куракин Александр
 * @version      04.2017
 */
?>
<tr id="MorbusOnkoPersonDisp_{MorbusOnko_pid}_{MorbusOnkoPersonDisp_id}" class="list-item" 
	onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoPersonDisp_{MorbusOnko_pid}_{MorbusOnkoPersonDisp_id}_toolbar').style.display='block'" 
	onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('MorbusOnkoPersonDisp_{MorbusOnko_pid}_{MorbusOnkoPersonDisp_id}_toolbar').style.display='none'">
	<td>{PersonDisp_begDate}</td>
	<td>{PersonDisp_endDate}</td>
	<td>{MedPersonal_Fio}</td>
	<td>{MedPersonalH_Fio}</td>
	<td class="toolbar">
        <div id="MorbusOnkoPersonDisp_{MorbusOnko_pid}_{MorbusOnkoPersonDisp_id}_toolbar" class="toolbar">
            <a id="MorbusOnkoPersonDisp_{MorbusOnko_pid}_{MorbusOnkoPersonDisp_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
            <a id="MorbusOnkoPersonDisp_{MorbusOnko_pid}_{MorbusOnkoPersonDisp_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
        </div>
    </td>
</tr>