<div id="EvnReceptGeneralView_{EvnReceptGeneral_id}" class="frame" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptGeneralView_{EvnReceptGeneral_id}_toolbar').style.visibility='visible'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptGeneralView_{EvnReceptGeneral_id}_toolbar').style.visibility='hidden'">

        <div style="float: right">
            <div id="EvnReceptGeneralView_{EvnReceptGeneral_id}_toolbar" class="toolbar" style="visibility: hidden">
                <a id="EvnReceptGeneralView_{EvnReceptGeneral_id}_editEvnReceptGeneral" class="button icon icon-view16" title="Просмотреть рецепт"><span></span></a>
                <a id="EvnReceptGeneralView_{EvnReceptGeneral_id}_printEvnReceptGeneral" class="button icon icon-print16" title="Печатать рецепт"><span></span></a>
				
            </div>
        </div>

		<div id="EvnReceptGeneralView_{EvnReceptGeneral_id}_content">
				<div style="text-align: left" class="data-table">
					<?php if(!empty($isMseDepers)) { ?>
					<p><strong>* * *</strong></p>
					<?php } else { ?>
					<p><strong>{Person_Surname} {Person_Firname} {Person_Secname}, {Person_Birthday} г.р.</strong></p>
					<?php } ?>
					<p><span id="EvnReceptGeneralView_{EvnReceptGeneral_id}_showDiagList" class="link" title="Показать список уточненных диагнозов">Диагноз</span>: <span id="EvnReceptGeneralView_{EvnReceptGeneral_id}_selDiag" class="link" title="Показать все случаи лечения по данному диагнозу">{Diag_Code}</span>. {Diag_Name}.</p>
					
						<thead>
							<tr>
								<th colspan="2" style="text-align: left"><span id="EvnReceptGeneralView_{EvnReceptGeneral_id}_showFarmTherapy">РЕЦЕПТ</span>
									серия {EvnReceptGeneral_Ser} № {EvnReceptGeneral_Num} Дата выписки: {EvnReceptGeneral_setDate} Форма: {ReceptForm_Name}
									<br>{ReceptUrgency}
									<br>{AddInfo}
								</th>
							</tr>
						</thead>
						<table>
						<col style="width: 15%;" class="first">
						<col class="last">
						
						<?php if(!empty($EvnReceptGeneralDrugLink_id0)){
						?>
							<tr style="text-align: left">
								<td>
									Rp.:
								</td>
								<td>
									<!--<span id="EvnReceptGeneralView_{EvnReceptGeneral_id}_showRls" class="link" title="Показать информацию о препарате в справочнике РЛС">{DrugMnn_Name}</span>-->
									{DrugMnn_Name0}
								</td>
							</tr>
							<tr style="text-align: left">
								<td>
									Кол-во уп.:
								</td>
								<td>
									{EvnReceptGeneral_Kolvo0}
								</td>
							</tr>
							<?php if(!empty($GeneralReceptSupply0)){
									?>
							<tr style="text-align: left">
								<td>
									Обеспечение
								</td>
								<td>
									{SuppInfo0}
								</td>
							</tr>
							<?php } ?>
							<!--
							<tr style = "text-align: left">
								<td>
									Дата обеспечения, аптека
								</td>
								<td>
									{SuppDateFarm0}
								</td>
							</tr>
							<tr style = "text-align: left">
								<td>
									Медикамент, выданный по рецепту
								</td>
								<td>
									{SuppDrug0}
								</td>
							</tr>
							<tr style = "text-align: left">
								<td>
									Цена медикамента, отпущенного по рецепту
								</td>
								<td>
									{SuppPrice0}
								</td>
							</tr>
							<tr style = "text-align: left">
								<td>
									Количество выданных упаковок
								</td>
								<td>
									{SuppKolvo0}
								</td>
							</tr>-->
							<!-- <tr style="text-align: left">
								<td>
									S.
								</td>
								<td>
									{EvnReceptGeneral_Signa0}
								</td>
							</tr> -->
						<?php } ?>
						</table>
						<br>
						<table>
						<col style="width: 15%;" class="first">
						<col class="last">
						<?php if(!empty($EvnReceptGeneralDrugLink_id1)){
						?>
							<tr style="text-align: left">
								<td>
									Rp.:
								</td>
								<td>
									<!--<span id="EvnReceptGeneralView_{EvnReceptGeneral_id}_showRls" class="link" title="Показать информацию о препарате в справочнике РЛС">{DrugMnn_Name}</span>-->
									{DrugMnn_Name1}
								</td>
							</tr>
							<tr style="text-align: left">
								<td>
									Кол-во уп.:
								</td>
								<td>
									{EvnReceptGeneral_Kolvo1}
								</td>
							</tr>
							<?php if(!empty($GeneralReceptSupply1)){
									?>
							<tr style="text-align: left">
								<td>
									Обеспечение
								</td>
								<td>
									{SuppInfo1}
								</td>
							</tr>
							<?php } ?>
							<!--<tr style = "text-align: left">
								<td>
									Дата обеспечения, аптека
								</td>
								<td>
									{SuppDateFarm1}
								</td>
							</tr>
							<tr style = "text-align: left">
								<td>
									Медикамент, выданный по рецепту
								</td>
								<td>
									{SuppDrug1}
								</td>
							</tr>
							<tr style = "text-align: left">
								<td>
									Цена медикамента, отпущенного по рецепту
								</td>
								<td>
									{SuppPrice1}
								</td>
							</tr>
							<tr style = "text-align: left">
								<td>
									Количество выданных упаковок
								</td>
								<td>
									{SuppKolvo1}
								</td>
							</tr> -->
							<!-- <tr style="text-align: left">
								<td>
									S.
								</td>
								<td>
									{EvnReceptGeneral_Signa1}
								</td>
							</tr> -->
						<?php } ?>
						</table>
						<br>
						<table>
						<col style="width: 15%;" class="first">
						<col class="last">
						<?php if(!empty($EvnReceptGeneralDrugLink_id2)){
						?>
							<tr style="text-align: left">
								<td>
									Rp.:
								</td>
								<td>
									<!--<span id="EvnReceptGeneralView_{EvnReceptGeneral_id}_showRls" class="link" title="Показать информацию о препарате в справочнике РЛС">{DrugMnn_Name}</span>-->
									{DrugMnn_Name2}
								</td>
							</tr>
							<tr style="text-align: left">
								<td>
									Кол-во уп.:
								</td>
								<td>
									{EvnReceptGeneral_Kolvo2}
								</td>
							</tr>
							<?php if(!empty($GeneralReceptSupply2)){
									?>
							<tr style="text-align: left">
								<td>
									Обеспечение
								</td>
								<td>
									{SuppInfo2}
								</td>
							</tr>
							<?php } ?>
							<!--
							<tr style = "text-align: left">
								<td>
									Дата обеспечения, аптека
								</td>
								<td>
									{SuppDateFarm2}
								</td>
							</tr>
							<tr style = "text-align: left">
								<td>
									Медикамент, выданный по рецепту
								</td>
								<td>
									{SuppDrug2}
								</td>
							</tr>
							<tr style = "text-align: left">
								<td>
									Цена медикамента, отпущенного по рецепту
								</td>
								<td>
									{SuppPrice2}
								</td>
							</tr>
							<tr style = "text-align: left">
								<td>
									Количество выданных упаковок
								</td>
								<td>
									{SuppKolvo2}
								</td>
							</tr>-->
							<!-- <tr style="text-align: left">
								<td>
									S.
								</td>
								<td>
									{EvnReceptGeneral_Signa2}
								</td>
							</tr> -->
						<?php } ?>
					</table>
					
				</div>

				<div style="text-align: left">
					<p>МО: <strong>{Lpu_Name}</strong></p>
					<p>Отделение: <strong>{LpuSection_Name}</strong>, Врач: <strong>{MedPersonal_Fin}</strong></p>
					<p>Протокол ВК: {ProtocolVK}</p>
					<p>Срок действия: <strong>{EvnReceptGeneral_Valid}</strong></p>
					<?php if (!empty($Drug_Price)) {
						?><p>Цена: <strong>{Drug_Price} руб.</strong></p><?php
					}
					?>
					<p>Статус: <strong>{ReceptDelayType_Name}</strong></p>
				</div>
			</div>

</div>

