<?php
// Вывод расписания для выписки направлений в стационар. Подвал таблицы
?>
</table>
<script>
	Ext.onReady(function (){
		<?="Ext.getCmp('{$data['PanelID']}').btnQueuePerson.setDisabled({$data['checkQueue']});"?>
	});
</script>
