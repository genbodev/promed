<div class="EvnPrescrList_right">
	<div id="EvnPrescrList_scroll-layout">
		<div id="EvnPrescrList_today"></div>
		
		<?php
			$currentGrupId = NULL;
			if (!function_exists('processingContent')) {
				function processingContent($content, $i, $count, $row) {
					if (6 == $row['PrescriptionType_id']) {
						$parse_data = array('{forProcessingContent_position}'=>'position:absolute; right: 0; top: 21px;');
						if ($i<($count-4)) {
							$parse_data['{forProcessingContent_position}'] = 'position:absolute;';
						}
						$content = strtr($content, $parse_data);
					}
					return $content;
				}
			}
			foreach ($response as $row) {
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

						<div class="EvnPrescrList_line EvnPrescrList_group EvnPrescrList_collapsed">
							<div class="EvnPrescrList_group_name">
								<span></span>
							</div>
						</div>

						<div class="<?php echo $currentGrupId; ?>">

					<?php
					} else {
						//Выводим элементы группы
						$isAlert = false;
						$contentFree = false;
						if (in_array($row['PrescriptionType_id'], array(7,11,12,13)) && !empty($row['EvnPrescr_execDT'])) {
							foreach ($row['days'] as $key => &$day) {
								$timestamp = strtotime($row['EvnPrescr_execDT']);
								$time_str = date('H:i',  $timestamp);
								$date_str = date('d.m.Y',  $timestamp);
								$day['content'] = '<span title="Выполнено '.$date_str.'">'.$time_str.'<span>';
								/*
								$key_arr = explode('-',$key);
								if (count($key_arr)==2) {
									if ($key_arr[1]==$date_str) {
										$day['content']=$time_str;
									} else {
										$day['content']=$date_str.' '.$time_str;
									}
								}
								*/
								$day['apointed']=true;
							}
						} else if(array_key_exists('timetable', $row)&&($row['timetable']=='EvnQueue')) {
							//Если в очереди
							$contentFree = true;
						} else if ($row['RecDate']) {
								if ($row['EvnPrescr_IsExec']==2){
									//Если записан
									foreach ($row['days'] as $key => &$day) {
										$day['content']=date('H:i',  strtotime($row['RecDate']));
										$day['apointed']=true;
									}
								} else {
									//Если просрок больше одного дня, считаем назначение просроченым, иначе запись активна
									if (mktime(0, 0, 0) > strtotime(substr($row['RecDate'],0,10))) {
										$isAlert = true;
										foreach ($row['days'] as $key => &$day) {
											$day['content']='<span class="EvnPrescrList_icon EvnPrescrList_type6" title="Просрочено"></span>';
											$day['apointed']=true;
										}
									} else {
										foreach ($row['days'] as $key => &$day) {
											$day['content']=date('H:i',  strtotime($row['RecDate']));
											$day['apointed']=true;
										}
									}
								}
						} else if (in_array($row['PrescriptionType_id'], array(7,11,12,13))) {
							$isAlert = ($row['EvnPrescr_IsExec']!=2);
							$contentFree = true;
						} else if (in_array($row['PrescriptionType_id'], array(1,2,10))){
							
						} else if (in_array($row['PrescriptionType_id'], array(5,6))){
							foreach ($row['days'] as $key => &$day) {
								$id = 'EvnPrescrRows'.str_replace('.','-',$key);
								$rows = array();
								if (!empty($day['EvnPrescrDataList'])) {
									$width = '330px';
									$styleLink = 'color: #000079;cursor: pointer;text-decoration: underline;';
									$cutLength = 26;
									foreach($day['EvnPrescrDataList'] as $EvnPrescr_id => $ep) {
										$orderStr = $EvnPrescr_id;
										if ('TimetableMedService' == $ep['timetable']) {
											$rowTime = substr($ep['RecDate'],11,5);
											$orderStr = $rowTime;
										} else if (!empty($ep['RecDate'])) {
											$rowTime = 'В очереди';
										} else {
											$rowTime = 'Записать';
										}
										$rowTitle = $ep['RecTo']?$ep['RecTo']:'Без направления';
										$rowName = (mb_strlen($rowTitle,'cp1251')>$cutLength)?(mb_strcut($rowTitle, 0, $cutLength).'...'):$rowTitle;
										if (2 == $ep['EvnPrescr_IsExec']) {
											$cancelStr = "<span style='{$styleLink}' onclick=\"Ext.getCmp('EvnPrescrPlanRestyleWindow').unExecEvnPrescrProc('{$row['EvnPrescr_key']}','{$key}','{$EvnPrescr_id}')\">Отменить</span>";
											if ($ep['EvnDirection_id']||2==$ep['EvnPrescr_IsHasEvn']) {
												$cancelStr = "<span>Отменить</span>";
											}
											$rows[$orderStr] = "<tr>
												<td style='text-align: left; width: 20%;'><span>{$rowTime}</span></td>
												<td style='text-align: left; width: 60%;'><span title='{$rowTitle}'>{$rowName}</span><span style='margin: 0 3px 0 0;' class='EvnPrescrList_icon EvnPrescrList_type4' title='Назначение выполнено'></span></td>
												<td style='text-align: center; width: 20%;'>{$cancelStr}</td>
											</tr>";
										} else {
											$rows[$orderStr] = "<tr>
												<td style='text-align: left; width: 20%;'><span style='{$styleLink}' onclick=\"Ext.getCmp('EvnPrescrPlanRestyleWindow').editEvnPrescrProc('{$row['EvnPrescr_key']}','{$key}','{$EvnPrescr_id}')\">{$rowTime}</span></td>
												<td style='text-align: left; width: 60%;'><span title='{$rowTitle}'>{$rowName}</span></td>
												<td style='text-align: center; width: 20%;'><span style='{$styleLink}' onclick=\"Ext.getCmp('EvnPrescrPlanRestyleWindow').cancelEvnPrescrProc('{$row['EvnPrescr_key']}','{$key}','{$EvnPrescr_id}')\">Отменить</span></td>
											</tr>";
										}
									}
									ksort($rows);
									$content = "<div onmouseover=\"Ext.getCmp('EvnPrescrPlanRestyleWindow').mouseoverEvnPrescrRows('{$id}')\" onmouseout=\"Ext.getCmp('EvnPrescrPlanRestyleWindow').mouseoutEvnPrescrRows('{$id}')\" style='position: relative;'>
									<span>{$day['CountInDay']}</span>
									<div id='{$id}' style='z-index:9999;text-align: left; background-color: #D9E8FB; display: none; {forProcessingContent_position} width: {$width};'>
										<table style='border-width: 0; border-collapse: collapse; border-spacing: 0; width: {$width};'>
										".implode('', $rows)."
										</table>
										<div><span style='{$styleLink}' onclick=\"Ext.getCmp('EvnPrescrPlanRestyleWindow').addEvnPrescrProc('{$row['EvnPrescr_key']}','{$key}')\">Добавить</span></div>
									</div>
								</div>";
								}
								if (!empty($day['DrugDataList'])) {
									foreach($day['DrugDataList'] as $EvnPrescrTreatDrug_id => $drug) {
										//$drug['DrugTorg_Name']
										$str = $drug['Drug_Name'];
										/*
										if (!empty($drug['Kolvo']) && !empty($drug['EdUnits_Nick'])) {
											$str .= ' Доза разовая – '.$drug['Kolvo'].' '.$drug['EdUnits_Nick'];
										} else if (!empty($drug['KolvoEd']) && !empty($drug['DrugForm_Nick'])) {
											$str .= ' Доза разовая – '.$drug['KolvoEd'].' '.$drug['DrugForm_Nick'];
										} else {
											$str .= '.';
										}
										*/
										if (!empty($drug['DoseDay'])) {
											$str .= ', дневная доза – '.$drug['DoseDay'];
										}
										if (!empty($drug['PrescrCntDay'])) {
											$str .= ', '.(empty($drug['FactCntDay'])?0:$drug['FactCntDay']).'/'.$drug['PrescrCntDay'].'.';
										} else {
											$str .= '.';
										}
										$rows[] = $str;
									}
									if (!empty($day['EvnPrescr_Descr'])) {
										$rows[] = 'Комментарий: '.htmlspecialchars($day['EvnPrescr_Descr']);
									}
									$content = "<div title='".implode("\n", $rows)."'><span>{$day['cntDrug']}</span></div>";
								}
								//$row['days'][$key]['apointed']=true;
								$day['content']=$content;
							}
						}

						if ($row['PrescriptionType_id']==5) {
							$isAlert = ((!array_key_exists('EvnPrescr_cnt',$row))||($row['EvnPrescr_cnt']==0));
						}
						
						?>
						<div class="EvnPrescrList_line <?php if ($isAlert){ ?>EvnPrescrList_alert <?php } ?> EvnPrescrList_no1-1" >
							<?php
								$oneDay = mktime(0, 0, 0, 0, 1, 0)-mktime(0, 0, 0, 0, 0, 0);
								for ($i=0;$i<$count;$i++) {
									$content = '';
									$apointedClass = '';
									$execClass = '';
									$currentDay = $startDate+$i*$oneDay;
									if ((array_key_exists('days', $row))&&(is_array($row['days']))) {
										$key = empty($row['EvnPrescr_id'])?$row['EvnCourse_id']:$row['EvnPrescr_id'];
										$key .= '-'.date('d.m.Y',$currentDay);
										if (isset($row['days'][$key])) {
											$execClass = (($row['days'][$key]['Day_IsExec']==2))?'EvnPrescrList_exec':'';
											$apointedClass = 'EvnPrescrList_appointed';
											if (array_key_exists('content', $row['days'][$key])) {
												$content = processingContent($row['days'][$key]['content'], $i, $count, $row);
											} else if (!in_array($row['PrescriptionType_id'], array(1,2))&&($row['days'][$key]['Day_IsExec']!=2)&&(strtotime($row['days'][$key]['date'])<mktime(0,0,0))) {
												$content='<span class="EvnPrescrList_icon EvnPrescrList_type6" title="Просрочено"></span>';
											}
										}
									}
									$date = date('d.m.Y', $currentDay);
									$content = ($contentFree)?'':$content;
									$apointedClass = ($contentFree)?'':$apointedClass;
									$execClass = ($contentFree)?'':$execClass;
									echo "<div class=\"EvnPrescrList_scale_bar {$apointedClass} {$execClass} EvnPrescrList_day-{$i}\" onclick=\"
											Ext.getCmp('EvnPrescrPlanRestyleWindow').onCellClick(event,
											{
												'cellType':'EvnPrescrDayCell',
												'EvnPrescr_key':'{$row['EvnPrescr_key']}',
												'date':'{$date}'
											});
									 \">{$content}</div>";
								}
							?> 

						</div>				

					<?php }
				}
			}
			echo '</div>';//Закрывающий div для последней группы назначений
		?>
	</div>
</div>