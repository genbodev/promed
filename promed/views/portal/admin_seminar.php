<tr>
	<td class="text-nowrap"><?php echo "$begdt <br> $enddt" ?></td>
	<td class="gradient"><p><b><?php echo $title ?></b></p><?php echo $body ?></td>
	<td class="actions">
		<a class="dropdown-item" href="?c=portalAdmin&m=seminar_delete&id=<?php echo $id ?>"
		   onclick="if(!confirm('Удалить?')) return false;" title="Удалить"><i
					class="dropdown-icon fe fe-trash"></i></a>
		<a class="dropdown-item" href="?c=portalAdmin&m=seminar_edit&id=<?php echo $id ?>" title="Редактировать"><i
					class="dropdown-icon fe fe-edit-2"></i></a>
	</td>
</tr>