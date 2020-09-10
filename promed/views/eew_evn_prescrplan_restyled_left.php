<div class="EvnPrescrList_left">
	<?php
		$currentGrupId = NULL;
		foreach ($response as $key => $row) {
			//Фиксируем общие данные
			if ($row['EvnPrescr_key']=='CommonData') {
				$Evn_pid = $row['days']['Evn_pid'];
			}
			
			if (array_key_exists('isGroupTitle', $row)) {				
				//Выводим заголовок группы
				if ($row['isGroupTitle']) {
					
					$closeGroupDiv = ( is_null($currentGrupId) )?'':'</div>';
					echo $closeGroupDiv;//Закрываем div предыдущей группы, если она существует
					
					$currentGrupId = $Evn_pid.'-'.$row['PrescriptionType_Code'];
					$headerGroupId = 'GroupTitle-'.$currentGrupId;
					?>
					
					<div class="EvnPrescrList_line EvnPrescrList_group EvnPrescrList_expanded" id="<?php echo $headerGroupId; ?>">
						<div class="EvnPrescrList_group_name">
							<em title="Свернуть" onclick="
								var groupHeader = $('#<?php echo $headerGroupId; ?>');
								if (groupHeader.hasClass('EvnPrescrList_collapsed')) {
									groupHeader.removeClass('EvnPrescrList_collapsed');
									groupHeader.addClass('EvnPrescrList_expanded');
									$('.<?php echo $currentGrupId; ?>').show()
								} else {
									groupHeader.removeClass('EvnPrescrList_expanded');
									groupHeader.addClass('EvnPrescrList_collapsed');
									$('.<?php echo $currentGrupId; ?>').hide()
								}					
							"></em>
							<span><?php echo $row['EvnPrescrGroup_Title']; ?></span><?php if($row['PrescriptionType_Code']==5){
							echo '<a href="#"
							   onclick="Ext.getCmp(\'EvnPrescrPlanRestyleWindow\').createCourseTreatPrescription()">
								<img src="/img/EvnPrescrPlan/add.png" title="Добавить"/> Добавить
							</a>';
							 }else{?>
							<a href="#"
							   onclick="Ext.getCmp('EvnPrescrPlanRestyleWindow').addPrescrByType({EvnPrescr_key:'<?php echo $row['EvnPrescr_key']; ?>'})">
								<img src="/img/EvnPrescrPlan/add.png" title="Добавить"/> Добавить
							</a>
							<?php }?>
						</div>
					</div>
	
					<div class="<?php echo $currentGrupId ?>">
				<?php
				} else {
					//Выводим элементы группы
					$cito = ($row['EvnPrescr_IsCito']==2)?'<span class="EvnPrescrList_cito">Cito!</span>':'';
					$cutLength = ($cito)?30:40;

					$PrescriptionNameKeysInRow = array(1=>'PrescriptionRegimeType_Name',2=>'PrescriptionDietType_Name',10=>'ObservParamType_Names',6=>'Usluga_List',7=>'Usluga_List',11=>'Usluga_List',12=>'Usluga_List',13=>'Usluga_List',);
					if ($row['PrescriptionType_id'] == 5) {
						$DrugInfo = array();
						$DrugData = array();
						if ( !empty($row['DrugDataList']) && is_array($row['DrugDataList'])) {
							foreach($row['DrugDataList'] as $id => $drug) {
								//$drug['Drug_Name']
								$str = $drug['DrugTorg_Name'];
								if (!empty($drug['Kolvo']) && !empty($drug['EdUnits_Nick'])) {
									$str .= ' Доза разовая – '.$drug['Kolvo'].' '.$drug['EdUnits_Nick'];
								} else if (!empty($drug['KolvoEd']) && !empty($drug['DrugForm_Nick'])) {
									$str .= ' Доза разовая – '.$drug['KolvoEd'].' '.$drug['DrugForm_Nick'];
								} else {
									$str .= '.';
								}
								if (!empty($drug['MaxDoseDay']) && $drug['MaxDoseDay'] == $drug['MinDoseDay']) {
									$str .= '; дневная – '.$drug['MaxDoseDay'];
								} else if (!empty($drug['MinDoseDay'])) {
									$str .= '; дневная '.$drug['MinDoseDay'].' – '.$drug['MaxDoseDay'];
								} else {
									$str .= '.';
								}
								if (!empty($drug['PrescrDose'])) {
									$str .= '; курсовая – '.$drug['PrescrDose'].'.';
								} else {
									$str .= '.';
								}
								$DrugData[] = $str;
								$DrugInfo[] = $drug['DrugTorg_Name'];
							}
						}
						$rowName = implode(', ',$DrugInfo);
						$rowName = (mb_strlen($rowName,'cp1251')>$cutLength)?(mb_strcut($rowName, 0, $cutLength).'...'):$rowName;
						$rowTitle = implode("\n",$DrugData);
						if ( !empty($row['EvnCourse_begDate']) && !empty($row['Duration']) && !empty($row['DurationType_Nick'])) {
							$rowTitle .= "\nС {$row['EvnCourse_begDate']} продолжительность {$row['Duration']} {$row['DurationType_Nick']}.";
						}
						if ( !empty($row['PrescriptionIntroType_Name'])) {
							$rowTitle .= "\nМетод введения: {$row['PrescriptionIntroType_Name']}.";
						}
						if ( !empty($row['PerformanceType_Name'])) {
							$rowTitle .= "\nИсполнение: {$row['PerformanceType_Name']}.";
						}
						if ( !empty($row['EvnPrescr_Descr'])) {
							$rowTitle .= "\nКомментарий: {$row['EvnPrescr_Descr']}";
						}
					} else {
						$rowTitle = $row[$PrescriptionNameKeysInRow[$row['PrescriptionType_id']]];
						$rowName = (mb_strlen($rowTitle,'cp1251')>$cutLength)?(mb_strcut($rowTitle, 0, $cutLength).'...'):$rowTitle;
					}
					$rowName = "<span title='{$rowTitle}'>{$rowName}</span>";

					$controlContent = '';
					$controlClass = '';
					$controlTitle = '';
					
					$isAlert = false;
					
					if ($row['PrescriptionType_id'] == 6 && array_key_exists('CountInDay', $row)&&($row['CountInDay']>0)) {
						$controlContent = '№'.$row['CountInDay'];
						$controlClass = 'EvnPrescrList_text';
						$controlTitle = $row['CountInDay'].' раз в день';
					} else if(array_key_exists('timetable', $row)&&($row['timetable']=='EvnQueue')) {
						//Если в очереди
						$controlContent = '';
						$controlClass = 'EvnPrescrList_icon EvnPrescrList_type3';
						$controlTitle = $row['RecDate'];
					} else if ($row['RecDate']!='') {
							if ($row['EvnPrescr_IsExec']==2){
								//Если записан
								$controlContent = '';
								$controlTitle = 'Назначение выполнено '.($row['RecDate']);
								$controlClass = 'EvnPrescrList_icon EvnPrescrList_type4';
							} else {
								//Если просрок больше одного дня, считаем назначение просроченым, иначе запись активна
								if (mktime(0, 0, 0) > strtotime(substr($row['RecDate'],0,10))) {
									$isAlert = true;
									$controlContent = '';
									$controlTitle = 'Назначение просрочено '.($row['RecDate']);
									$controlClass = 'EvnPrescrList_icon EvnPrescrList_type2';
								} else {
									$controlContent = '';
									$controlTitle = 'Назначенено на '.($row['RecDate']);
									$controlClass = 'EvnPrescrList_icon EvnPrescrList_type1';
								}
							}
					} else if (in_array($row['PrescriptionType_id'], array(7,11,12,13))) {
						if ($row['EvnPrescr_IsExec']==2){
							//Если не записан, но выполнено
							$controlContent = '';
							$controlTitle = 'Назначение выполнено';
							$controlClass = 'EvnPrescrList_icon EvnPrescrList_type4';
						} else {
							//Не записан но запись нужна
							$isAlert = true;
							$controlContent = '';
							$controlTitle = 'Требуется запись';
							$controlClass = 'EvnPrescrList_icon EvnPrescrList_type5';
						}
					}

					if ($row['PrescriptionType_id']==5) {
						$isAlert = ((!array_key_exists('EvnPrescr_cnt',$row))||($row['EvnPrescr_cnt']==0));
					}

					//https://redmine.swan.perm.ru/issues/28788#note-30
					if (in_array($row['PrescriptionType_id'], array(7,11,12,13)) && $row['EvnPrescr_IsExec']==2) {
						//выполнено
						$isAlert = false;
						$controlContent = '';
						$controlTitle = 'Назначение выполнено';
						$controlClass = 'EvnPrescrList_icon EvnPrescrList_type4';
					}

					?>
					<div class="EvnPrescrList_line <?php if ($isAlert) { ?>EvnPrescrList_alert <?php } ?> EvnPrescrList_no1-1">
						<div class="EvnPrescrList_scale_bar">
							<?php echo $rowName; ?>
							<?php echo $cito; ?>
							<span class="EvnPrescrList_control">
								<span class="EvnPrescrList_button" onclick="
										var obj = $(this);
										Ext.getCmp('EvnPrescrPlanRestyleWindow').onCellClick(event,{
											'cellType':'EvnPrescrCell',
											'EvnPrescr_key':'<?php echo $row['EvnPrescr_key']; ?>',
											callback: function() {
												obj.addClass('EvnPrescrList_click');
											}
										})
									  "></span>
								<span title="<?php echo $controlTitle; ?>" class="<?php echo $controlClass; ?>"><?php echo $controlContent; ?></span>
							</span>
						</div>   
					</div>
	
				<?php }
			}
		}
		echo '</div>';//Закрывающий div для последней группы назначений
	?>
</div>