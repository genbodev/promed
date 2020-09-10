<tr>
	<td onclick="window.location = '?c=portalAdmin&m=article_edit&id=<?php echo $id?>'"><?php echo $title ?></td>
	<td onclick="window.location = '?c=portalAdmin&m=article_edit&id=<?php echo $id?>'" class="body"><?php echo $body ?></td>
	<td class="tools"><a href="?c=portalAdmin&m=article_delete&id=<?php echo $id?>" title="Удалить" onclick="if(!confirm('Удалить?')) return false;"><img src="/img/portal/icon-delete.png" /></a></td>
</tr>
