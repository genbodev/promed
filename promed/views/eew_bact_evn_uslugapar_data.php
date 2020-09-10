<div id="EvnUslugaPar_data_{EvnUslugaPar_id}" class="section ">
	<div id="EvnUslugaPar_data_{EvnUslugaPar_id}_content">
		<div style="text-align: center">
			<p>{Lpu_Name}</p>
			<p>{Lpu_Address}</p>
		</div>
		<br>

		<div style="text-align: left; line-height: 0.8em;">
			<?php
			if (in_array(getRegionNumber(), array(59,66))) {
				if (!empty($EvnCostPrint_setDT)) {
					$costprint = "<p>Стоимость лечения: ".$CostPrint."</p>";
					if ($EvnCostPrint_IsNoPrint == 2) {
						$costprint .= "<p>Отказ в получении справки";
					} else {
						$costprint .= "<p>Справка выдана";
					}

					$costprint .= " ".$EvnCostPrint_setDT."</p>";
					echo $costprint;
				}
			}
			?>
			<?php if($isMseDepers) { ?>
			<p><strong>Пациент: ***</strong></p>
			<?php } else { ?>
			<p><strong>Пациент: {Person_Fio} <?php if (!empty($Person_Birthday)) { echo ', {Person_Birthday} г.р.'; } ?></strong></p>
			<?php } ?>
			<p><span id="EvnUslugaPar_data_{EvnUslugaPar_id}_showDiagList" class="link" title="Показать список уточненных диагнозов">Диагноз</span>: <span id="EvnUslugaPar_data_{EvnUslugaPar_id}_selDiag" class="link" title="Показать все случаи лечения по коду диагноза">{Diag_Code}</span>. {Diag_Name}</p>
			<p><strong>Услуга: {Usluga_Name}</strong></p>
			<p><strong>Отделение: {LpuSection_Code}. {LpuSection_Name}</strong></p>
			<p>Врач: {MedPersonal_Fin}</p>
			<p>Выполнено: {EvnUslugaPar_setDate} {EvnUslugaPar_setTime}</p>
		</div>
		<br>

		<div style="text-align: left; line-height: 0.8em;">
			<p>Кем направлен: {OrgDirectSubject_Name}, {DirectSubject_Code} {DirectSubject_Name}</p>
			<p>Направление № <span id="EvnUslugaPar_data_{EvnUslugaPar_id}_showEvnDirection" class="link" title="Показать электронное направление">{EvnDirection_Num}</span> от: <strong>{EvnDirection_setDate}</strong></p>
			<p>Врач: <strong>{MedPersonalDirect_Fin}</strong></p>
		</div>
		<br>
		<?php if (!empty($BactIsNotFind)): ?>
			<div class="data-table">
				<table>
					<col style="width: 10%" class="first" />
					<col>
					<col class="last" />
					<thead>
					<tr>
						<th>№№</th>
						<th>Выделенные микроорганизмы</th>
						<th>КОЕ/мл</th>
					</tr>
					</thead>

					<tbody>
					<tr class="list-item">
						<td>1</td>
						<td>Микроорганизмы не обнаружены</td>
						<td>-</td>
					</tr>
					</tbody>
				</table>
			</div>
		<?php elseif (!$BactMicro_isEmpty): ?>
		<div class="data-table">
			<table>
				<col style="width: 10%" class="first" />
				<col>
				<col class="last" />
				<thead>
					<tr>
						<th>№№</th>
						<th>Выделенные микроорганизмы</th>
						<th>КОЕ/мл</th>
					</tr>
				</thead>
	
				<tbody>
				{BactMicroList}
					<tr class="list-item">
						<td>{RowNumber}</td>
						<td>{BactMicro_Name}</td>
						<td>{UslugaTest_ResultValue}</td>
					</tr>
				{/BactMicroList}
				</tbody>
			</table>
		</div>
		<?php endif; ?>
		
        <?php if (!$BactAntibiotic_isEmpty): ?>
		<div class="data-table">
			<table>
				<col style="width: 40%" class="first" />
				{BactAntibiotic_col}
				<thead>
				<tr>
					{BactAntibiotic_head}
				</tr>
				</thead>
				
				<tbody>
                	{BactAntibiotic_items}
				</tbody>
			</table>
		</div>
		<?php endif; ?>
	</div>
</div>
