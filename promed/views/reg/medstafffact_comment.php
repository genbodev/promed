<!---/*NO PARSE JSON*/--->
<table width="100%" cellspacing="0" cellpadding="3" style="border: 1px solid gray; background-color: white">
	<thead>
	<tr style="background-color: rgb(204, 221, 255);">
		<td width="100%"><img border="0" src="/img/icons/info16.png" align="top"> <b>Примечание на врача</b></td>
	</tr>
	</thead>
	<tbody>
<?php
if ( isset($data) && count($data) ) {
		foreach ($data as $comment) {
		if ( !empty($comment['Annotation_Comment']) ) {
?>
		<tr>
			<td width="100%" colspan="2" style="border-top: 1px solid gray; padding-left: 10px;"><?php echo nl2br($comment['Annotation_Comment']);?></td>
		</tr><tr>
			<td colspan="2" class="smallfont"><b>Отредактировано:</b> <?php echo $comment['pmUser_Name'] . ", " . ConvertDateFormat($comment['Annotation_updDT'], "H:i d.m.Y");?>
			</td>
		</tr>
<?php
		}
		}
	}
?>
	</tbody>
</table>