<table width="100%" cellspacing="0" cellpadding="3" style="border: 1px solid gray; background-color: white">
	<thead>
	<tr style="background-color: rgb(204, 221, 255);">
		<td width="100%"><img border="0" src="/img/icons/info16.png" align="top"> <b>Примечание</b></td>
		<td class = 'erlink' ><a href="#" onclick="Ext.getCmp('<?php echo $data['PanelID']?>').openLpuSectionCommentWindow(); return false;">Редактировать</a>&nbsp;</td>
	</tr>
	</thead>
	<tbody>
<?php
	if ( isset($data['LpuSection_Descr']) ) {
?>
	<tr>
		<td width="100%" colspan="2" style="border-top: 1px solid gray; padding-left: 10px;"><?php echo nl2br($data['LpuSection_Descr']);?></td>
	</tr><tr>
		<td colspan="2" class="smallfont"><b>Отредактировано:</b> <?php echo $data['pmUser_Name'] . ", " . $data['LpuSection_updDT']->format("H:i d.m.Y");?>
		</td>
	</tr>
<?php
	}
?>
	</tbody>
</table>