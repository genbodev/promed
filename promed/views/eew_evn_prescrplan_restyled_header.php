
<div class="EvnPrescrList_header">
	<div class="EvnPrescrList_info">
		<span>{header}</span>
	</div>
	<div class="EvnPrescrList_calendar-layout">
		<div class="EvnPrescrList_calendar">
			<table>
				<tbody>
					<?php
						$month_all = '';
						$trW = '';
						$trD = '';
						foreach ($calendar as $yearNum => $year) {
							foreach ($year as $monthNum => $month) {
								$month_all .= '<th colspan="'.sizeof($month['days']).'">'.$month['name'].'</th>';
								foreach ($month['days'] as $dayNumber=>$day) {
									$weekendClass = ($day['isWeekend'])?' EvnPrescrList_weekend':'';
									$todayClass = ($day['isToday'])?' EvnPrescrList_isToday':'';
									$classAttr = ( ($weekendClass.$todayClass != '')? 'class="'.$weekendClass.' '.$todayClass.'"': '' );
									$trW .= '<td '.$classAttr.'>'.$day['dayName'].'</td>';
									$trD .= '<td '.$classAttr.'>'.$dayNumber.'</td>';
								}
							}
						}			
					?>

					<tr>
						<?php echo $month_all; ?>
					</tr>
					<tr>
						<?php echo $trW; ?>
					</tr>
					<tr>
						<?php echo $trD; ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div><!-- .header-->