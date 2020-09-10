<tr id="RepositoryObserv_{RepositoryObserv_id}" class="list-item" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('RepositoryObserv_{RepositoryObserv_id}_toolbar').style.display='block'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('RepositoryObserv_{RepositoryObserv_id}_toolbar').style.display='none'">
	<td>{RepositoryObserv_setDT}</td>
	<td>{MedPersonal_FIO}</td>
	<td class="toolbar">
		<div id="RepositoryObserv_{RepositoryObserv_id}_toolbar" class="toolbar">
			<a id="RepositoryObserv_{RepositoryObserv_id}_edit" class="button icon icon-edit16" title="Редактировать"><span></span></a>
			<a id="RepositoryObserv_{RepositoryObserv_id}_view" class="button icon icon-view16" title="Просмотреть"><span></span></a>
			<a id="RepositoryObserv_{RepositoryObserv_id}_delete" class="button icon icon-delete16" title="Удалить"><span></span></a>
			<?php if (getRegionNick() == 'msk' && !empty($EvnClass_id) && in_array($EvnClass_id, [32])) { ?>
				<a id="RepositoryObserv_{RepositoryObserv_id}_print" class="button icon icon-print16" title="Печать"><span></span></a>
			<?php } ?>
		</div>
	</td>
</tr>
