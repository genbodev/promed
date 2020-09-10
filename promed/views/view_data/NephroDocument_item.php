<tr class="list-item" 
	onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NephroDocument_{MorbusNephro_pid}_{NephroDocument_id}_toolbar').style.display='block'"
	onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('NephroDocument_{MorbusNephro_pid}_{NephroDocument_id}_toolbar').style.display='none'">
	<td>
		<div id="NephroDocument_{MorbusNephro_pid}_{NephroDocument_id}">
			<!--div id="NephroDocument_{NephroDocument_id}_content"> <a href="{NephroDocument__Dir}{NephroDocument_FilePath}" target="_blank" title="{NephroDocument_Comment}">{NephroDocument_FileName}</a> &raquo; <a href="{NephroDocument_Link}">Быстрый просмотр</a></div -->
			<div id="NephroDocument_{MorbusNephro_pid}_content"> <a href="/?c=MorbusNephro&amp;m=getDocument&amp;NephroDocument_id={NephroDocument_id}" target="_blank" title="{NephroDocument_Comment}">{NephroDocument_filename}</a> <!-- &raquo; a id="NephroDocument_{MorbusNephro_pid}_{NephroDocument_id}_download" >Скачать</a--></div>
		</div>
	</td>
	<td>
		<div id="NephroDocument_{MorbusNephro_pid}_{NephroDocument_id}_content"> {NephroDocument_description}</div>
	</td>
	<td class="toolbar">
		<div id="NephroDocument_{MorbusNephro_pid}_{NephroDocument_id}_toolbar" class="toolbar">
			<!--a id="NephroDocument_{MorbusNephro_pid}_{NephroDocument_id}_download" class="button icon icon-terr16" title="Открыть"><span></span></a-->
			<a id="NephroDocument_{MorbusNephro_pid}_{NephroDocument_id}_delete" class="button icon icon-delete16" title="Удалить файл"><span></span></a>
		</div>
	</td>
</tr>
