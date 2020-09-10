<?php $isKz = (getRegionNick() == 'kz'); ?>
<div id="EvnReceptView_{EvnRecept_id}" class="frame" onmouseover="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptView_{EvnRecept_id}_toolbar').style.visibility='visible'" onmouseout="if (isMouseLeaveOrEnter(event, this)) document.getElementById('EvnReceptView_{EvnRecept_id}_toolbar').style.visibility='hidden'">
	<?php if($isKardio == '1') { ?>
        <div style="float: right">
            <div id="EvnReceptView_{EvnRecept_id}_toolbar" class="toolbar" style="visibility: hidden">
                <a id="EvnReceptView_{EvnRecept_id}_editEvnReceptKardio" class="button icon icon-edit16" title="Редактировать рецепт"><span></span></a>
                <a id="EvnReceptView_{EvnRecept_id}_printEvnRecept" class="button icon icon-print16" title="Печатать рецепт"><span></span></a>
            </div>
        </div>
	<?php } else { ?>
        <div style="float: right">
            <div id="EvnReceptView_{EvnRecept_id}_toolbar" class="toolbar" style="visibility: hidden">
                <a id="EvnReceptView_{EvnRecept_id}_editEvnRecept" class="button icon icon-edit16" title="Редактировать рецепт"><span></span></a>
                <a id="EvnReceptView_{EvnRecept_id}_printEvnRecept" class="button icon icon-print16" title="Печатать рецепт"><span></span></a>
				<div class="emd-here" data-objectname="EvnRecept" data-objectid="{EvnRecept_id}" data-issigned="{EvnRecept_IsSigned}"></div>
            </div>
        </div>
	<?php } ?>
		<div id="EvnReceptView_{EvnRecept_id}_content">
				<div style="text-align: left" class="data-table">
					<?php if(!empty($isMseDepers)) { ?>
					<p><strong>* * *</strong></p>
					<?php } else { ?>
					<p><strong>{Person_Surname} {Person_Firname} {Person_Secname}, {Person_Birthday} г.р.</strong></p>
					<?php } ?>
					<p><span id="EvnReceptView_{EvnRecept_id}_showDiagList" class="link" title="Показать список уточненных диагнозов">Диагноз</span>: <span id="EvnReceptView_{EvnRecept_id}_selDiag" class="link" title="Показать все случаи лечения по данному диагнозу">{Diag_Code}</span>. {Diag_Name}.</p>
					<table>
						<col style="width: 15%;" class="first">
						<col class="last">
						<thead>
							<tr>
								<th colspan="2" style="text-align: left"><span id="EvnReceptView_{EvnRecept_id}_showFarmTherapy" class="link" title="Показать всю лекарственную терапию у данного пациента">РЕЦЕПТ</span>
									<?php if(!$isKz){ ?>серия {EvnRecept_Ser} <?php } ?>№ {EvnRecept_Num} Дата выписки: {EvnRecept_setDate}
								</th>
							</tr>
						</thead>
						<tbody>
							<tr style="text-align: left">
								<td>
									Rp.:
								</td>
								<td>
									<span id="EvnReceptView_{EvnRecept_id}_showRls" class="link" title="Показать информацию о препарате в справочнике РЛС">{DrugMnn_Name}</span>
								</td>
							</tr>
							<tr style="text-align: left">
								<td>
									D.t.d:
								</td>
								<td>
									{EvnRecept_Kolvo}
								</td>
							</tr>
							<tr style="text-align: left">
								<td>
									S.
								</td>
								<td>
									{EvnRecept_Signa}
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div style="text-align: left">
					<p>Категория: <span id="EvnReceptView_{EvnRecept_id}_showPrivilegeList" class="link" title="Показать экспертный анамнез и льготы пациента">
						{PrivilegeType_VCode} {PrivilegeType_Name}<?php if(!empty($SubCategoryPrivType_Name)){ ?> / {SubCategoryPrivType_Name}<?php } ?>
					</span></p>
					<?php if (empty($Drug_rlsid)) {
						if(!$isKz){ ?><p>«7 нозологий»: <strong>{EvnRecept_Is7Noz}</strong></p><?php }
					} else {
						?><p>Программа ЛЛО: <strong>{WhsDocumentCostItemType_Name}</strong></p><?php
					}
					?>
					<p>Финансирование: <strong>{ReceptFinance_Name}</strong>, скидка: <strong>{ReceptDiscount_Name}</strong></p>
					<p>МО: <strong>{Lpu_Name}</strong></p>
					<p>Отделение: <strong>{LpuSection_Name}</strong>, Врач: <strong>{MedPersonal_Fin}</strong></p>
					<p>Протокол ВК: {ProtocolVK}</p>
					<p>Срок действия: <strong>{ReceptValid_Name}</strong></p>
					<?php if (getRegionNick() == 'perm'){
						?><p> Результат: <strong>{ReceptResult}</strong></p> <?php
					}
					?>
					<?php if (!empty($Drug_Price)) {
						?><p>Цена: <strong>{Drug_Price} руб.</strong></p><?php
					}
					?>
					<p>Статус: <strong>{ReceptDelayType_Name}</strong></p>
					<?php if (empty($EvnRecept_otpDate)) {
						?><p>Аптека: <span id="EvnReceptView_{EvnRecept_id}_showDrugResidues" class="link" title="Показать остатки препарата в аптеке">{OrgFarmacy_Name}</span></p><?php
					} else {
						?>
						<p>Аптека: <?php
						if (empty($OrgFarmacyOtp_Name)) {
							?><strong>{OrgFarmacy_Name}</strong><?php
						} else {
							?><strong>{OrgFarmacyOtp_Name}</strong><?php
						}
						?></p>
						<div><?php
							if (getRegionNick() == 'ufa') {
								echo '<div style="float:right; clear:both;"><div>';
								switch ($EvnRecept_IsOtvSigned) {
									case 2:
										echo 'Обеспечение подписано: {signDT} {sign_Name}';
										break;
									case 1:
										echo 'Подпись обеспечения не актуальна.';
										break;
									default:
										echo 'Обеспечение не подписано.';
										break;
								}
								echo '</div>';
								if ($EvnRecept_IsOtvSigned == 2) {
									echo '<div style="float: right;"><a id="EvnReceptView_{EvnRecept_id}_verifyDoc" class="button icon icon-verif16" title="Верификация документа"><span></span></a><a id="EvnReceptView_{EvnRecept_id}_showDocVersionList" class="button icon icon-spis16" title="Список версий документа"><span></span></a></div>';
								}
								echo '</div>';
							}
						?>Дата обеспечения: <strong>{EvnRecept_otpDate}</strong><br><?php
							if (!empty($DrugOtp_List) && is_array($DrugOtp_List)) {
								?> По рецепту выданы:
								<table>
								<col style="width: 70%" class="first" />
								<col />
								<col class="last" />
								<thead>
								<tr>
									<th>Торговое наименование</th>
									<th>Количество</th>
									<th>Сумма</th>
								</tr>
								</thead>
								<tbody><?php
								foreach ($DrugOtp_List as $row) {
									?>
									<tr>
										<td><?php echo $row['DrugOtp_Name']; ?></td>
										<td><?php echo $row['EvnRecept_Kolvo']; ?></td>
										<td><?php echo $row['EvnRecept_Kolvo']*$row['DocumentUcStr_Price']; ?> руб.</td>
									</tr>
								<?php
								}
								?></tbody></table><?php
							}
						?></div>
						<?php
					}
					?>
				</div>
			</div>

</div>

