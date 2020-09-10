<?php
	$empty_str = '<span style="color: #666;">Не указано</span>';
?>
<tr class="list-item">
	<td>
		<div id="EvnVizitDisp_{EvnVizitDisp_id}">
			<div id="EvnUsluga_{EvnUsluga_id}_content" class="content">
				<p><strong>{Diag_Code} {Diag_Name}</strong></p>
				<p>
					Консультации и исследования:<br/>
					назначено: <?php echo empty($ConditMedCareType1_nName)?$empty_str:'{ConditMedCareType1_nName}';?>,
					место назначения: <?php echo empty($PlaceMedCareType1_nName)?$empty_str:'{PlaceMedCareType1_nName}';?>
				</p>
				<p>
					Лечение:<br/>
					назначено: <?php echo empty($ConditMedCareType2_nName)?$empty_str:'{ConditMedCareType2_nName}';?>,
					место назначения: <?php echo empty($PlaceMedCareType2_nName)?$empty_str:'{PlaceMedCareType2_nName}';?>
				</p>
				<p>
					Медицинская реабилитация / санаторно-курортное лечение:<br/>
					назначено: <?php echo empty($ConditMedCareType3_nName)?$empty_str:'{ConditMedCareType3_nName}';?>,
					место назначения: <?php echo empty($PlaceMedCareType3_nName)?$empty_str:'{PlaceMedCareType3_nName}';?>
				</p>
				<p>{Dolgnost_Name}, {MedPerson_Fio}</p>
			</div>
		</div>
	</td>
</tr>