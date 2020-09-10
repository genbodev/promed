<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<style type="text/css">
			<!--
			td.swvertext { /* Стиль текста */
				-moz-transform: rotate(270deg);
				-webkit-transform: rotate(270deg);
				-o-transform: rotate(270deg);
				text-align: center;
				font-weight: bold;
				height: 100px;
				width: 20px;
			}
			-->
		</style>
	</head>

	<body class="land" style="font-family: tahoma, verdana; font-size: 10pt; ">

		<?php
		$l = count($ep_list) - 1;
		$max_day = $ep_list[$l]['max_day'];
		$EvnPrescr_begDate = $ep_list[$l]['EvnPrescr_begDate'];
		unset($ep_list[$l]);

		/**
		 * Рекурсивная функция постраничной печати листа
		 * @param array $rows строки
		 * @param int $max_day Номер последнего дня
		 * @param int $beg_day Номер дня, с которого должна начинаться страница листа
		 * @param int $beg_time Дата (timestamp) дня, с которого должна начинаться страница листа
		 */
		function printNewPage($rows, $max_day, $beg_day, $beg_time) {
			$limit_day = 10; //число дней на странице
			$new_time = $beg_time;
			echo '
				<div style="text-align: left;"><p><b> {Head}&emsp; &#8470; {NumCard}</b></p></div>
				<div style="text-align: left;"><b>МО:</b> {Lpu_Nick}</div>
				<div style="text-align: left;"><b>Отделение:</b> {LpuSection_Code} {LpuSection_Name}</div>
				<div style="text-align: left;"><b>Лечащий врач:</b> {MedPersonal_Fio}</div>
				<div style="margin-bottom: 1em; text-align: left;"><b>Пациент:</b> {Person_FIO} &emsp; {Person_Birthday}</div>
				<table cellspacing="0" cellpadding="2" border="1" bordercolor="#000000" width="100%" style="border-collapse: collapse;">
				<thead>
				<tr>
				<td class="swcieltitle" rowspan="2"><b>Назначения</b></td>
				<td class="swvertext" rowspan="2" style="text-align: center;"><b><font size="2">Исполнит.</font></b></td>
				<td class="swvertext" rowspan="2" style="text-align: center;"><b><font size="2">Время</font></b></td>
				<td class="swcieltitle" colspan="10" style="text-align: center;"><b>Отметка о назначении и выполнении на дату</b></td>
				</tr>
				<tr>';
			for ($i = 1; $i <= $limit_day; $i++) {
				echo '
					<td class="swcieltitleday"><font size="1">' . date('d.m.y', $new_time) . '</font></td>';
				$new_time = strtotime('+' . $i . ' day', $beg_time);
			}
			echo '
				</tr>
				</thead>
				<tbody>';
			$nursesArr = array();
			$nursesArrFio = array();
			foreach ($rows as $row) {
				$kol = $row['CountTimes'];
				$arr_time = explode(", ", $row['allTime']);
				//врач

				echo '
				<tr><td class="swciellevel0" rowspan="' . (2 * $kol) . '">' . $row['EvnPrescr_Name'] . '</td>
				<td class="swciellevel1" rowspan="' . $kol . '" style="text-align: center; font-weight: bold;">врач</td>
				<td class="swciellevel1" style="text-align: center; height: 20"><font size="2">' . $arr_time[0] . '</font></td>';

				for ($i = $beg_day; $i < ($beg_day + $limit_day); $i++) {
					if (isset($row['EvnPrescr_Day' . $i]))
						$pmUserPrescr_Name = $row['EvnPrescr_Day' . $i];
					else
						$pmUserPrescr_Name = '';

					$pmUserPrescr_FIO = ''; //
					if (isset($row['EvnPrescr_Day' . $i . '_FIO']))
						$pmUserPrescr_FIO = $row['EvnPrescr_Day' . $i . '_FIO'];
					else
						$pmUserPrescr_FIO = '';
					echo '
						<td class="swciellevel1" rowspan="' . $kol . '" style="text-align: center;">' . (empty($pmUserPrescr_FIO) ? '&nbsp;' : $pmUserPrescr_FIO) . '</td>';
					if (!empty($pmUserPrescr_Name) && $pmUserPrescr_Name != '' && !in_array($pmUserPrescr_Name, $nursesArr)) {
						$nursesArr[] = $pmUserPrescr_Name;
						$nursesArrFio[] = $pmUserPrescr_FIO;
					}
				}
				echo '
					</tr>';

				for ($i = 1; $i < $kol; $i++) {
					echo '
						<tr><td class="swciellevel1" style="text-align: center; height: 20"><font size="2">' . $arr_time[$i] . '</font></td></tr>';
				}

				//сестра
				echo '
					<tr>
					<td class="swciellevel1" rowspan="' . $kol . '" style="text-align: center; font-weight: bold;">сестра</td>';

				for ($t = 0; $t < $kol; $t++) {
					$tr = ($t == 0) ? '' : '<tr>';
					echo '
						' . $tr . '<td class="swciellevel1" style="text-align: center; height: 20"><font size="2">' . $arr_time[$t] . '</font></td>';

					for ($i = $beg_day; $i < ($beg_day + $limit_day); $i++) {
						$pmUserExec_Name = '';
						$pmUserExec_FIO = '';
						if (isset($row['EvnPrescr_Day' . $i . 'Exec'])) {
							$arr_execTime = explode(", ", $row['EvnPrescr_Day' . $i . 'Exec']);
							foreach ($arr_execTime as $eTime) {
								$exec = explode("_", $eTime);
								if ($exec[0] == $arr_time[$t] || $exec[0] == '') {
									$pmUserExec_Name = $exec[1];
									$pmUserExec_FIO = $exec[2];
									break;
								}
							}
						} else
							$arr_execTime = '';

						echo '
							<td class="swciellevel1" style="text-align: center;">' . $pmUserExec_FIO . '</td>';
						if ($pmUserExec_Name != '' && !in_array($pmUserExec_Name, $nursesArr)) {
							$nursesArr[] = $pmUserExec_Name;
							$nursesArrFio[] = $pmUserExec_FIO;
						}
					}

					echo '
						</tr>';
				}
				//			}
			}
			echo '
				</tbody>
				</table>
				<!--div>i: ' . $i . '; max_day: ' . $max_day . '</div-->';

			if ((count($nursesArr) > 0)) {
				$nurses = 'Список медперсонала:<br>&emsp;';
				$nursesStr = $nurses . '';

				$nursArr = array();
				$kol = count($nursesArr);
				for ($a = 0; $a < $kol; $a++) {
					$nursArr[] = "<p><b>" . $nursesArrFio[$a] . "</b> - " . $nursesArr[$a];
				}
				$nursesStr .= implode(',</p>', $nursArr);
			} else {
				$nurses = '&nbsp;';
				$nursesStr = '&nbsp;';
			}

			echo '
				<table width="100%" style="margin-top: 20px;"><tr>
				<td width="50%" style="text-align: left;">' . $nursesStr . '</td>
				<td width="25%" style="text-align: right;">Подпись:</td>
				<td width="25%">___________________________</td>
				</tr></table>';
			if ($i < ($max_day + 1)) {
				echo '
					<pagebreak />';
				printNewPage($rows, $max_day, $i, $new_time);
			}
		}

		printNewPage($ep_list, $max_day, 1, strtotime($EvnPrescr_begDate));
		?>

	</body>

</html>