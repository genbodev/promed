<?php
// Вывод расписания для выписки направления в поликлинику. Подвал таблицы
?>
</table>
<script>
Ext.onReady(function (){
	<?="Ext.getCmp('{$data['PanelID']}').btnQueuePerson.setDisabled({$data['checkQueue']});"?>
});
</script>
