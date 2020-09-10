<style>
    .evn-observ-graph {
        width: 680px;
        height: 370px;
    }
    .eog-table-data {
        overflow: auto;
    }
</style>

<?php
if (!empty($data)) {
?>
<h3 id="eog-{EvnObservGraphs_id}-temperature-title">Температура</h3>
<div id="eog-{EvnObservGraphs_id}-temperature" class="evn-observ-graph"> </div>

<h3 id="eog-{EvnObservGraphs_id}-blood-pressure-title">Артериальное давление</h3>
<div id="eog-{EvnObservGraphs_id}-blood-pressure" class="evn-observ-graph"> </div>

<h3 id="eog-{EvnObservGraphs_id}-pulse-title">Пульс</h3>
<div id="eog-{EvnObservGraphs_id}-pulse" class="evn-observ-graph"> </div>

<h3 id="eog-{EvnObservGraphs_id}-table-title">Суточный мониторинг</h3>
<div id="eog-{EvnObservGraphs_id}-table" class="evn-observ-graph">
	<?php
	// Просчитываем ряд недостающих дат
	if ( $data['date_start'] !== null && $data['date_finish'] !== null ) {
		if ( ( $data['date_start'] + 86400 ) < $data['date_finish'] ) {
			for( $date=$data['date_start']; $date<$data['date_finish']; $date+=86400 ) if ( !in_array( $date, $data['dates'] ) ) {
				$data['dates'][] = $date;
			}
			// Отсортируем список дат
			sort( $data['dates'] );
		}
	}

	// Всего дней
	$total_dates = sizeof( $data['dates'] );
	?>


	<div class="eog-table-data">
		<table>
		<tr class="simple-params breath">
			<td class="title">Дыхание</td>
			<?php
			for( $i=0; $i<$total_dates; $i++ ){
				$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_5'][ $data['dates'][ $i ] ] ) ? $data['param_5'][ $data['dates'][ $i ] ] : '&nbsp;';
				echo '<td class="value">'.$val.'</td>';
			}
			?>
		</tr>

		<tr class="simple-params weight">
			<td class="title">Вес</td>
			<?php
			for( $i=0; $i<$total_dates; $i++ ){
				$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_6'][ $data['dates'][ $i ] ] ) ? $data['param_6'][ $data['dates'][ $i ] ] : '&nbsp;';
				echo '<td class="value">'.$val.'</td>';
			}
			?>
		</tr>

		<tr class="simple-params aqua">
			<td class="title">Выпито жидкости</td>
			<?php
			for( $i=0; $i<$total_dates; $i++ ){
				$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_7'][ $data['dates'][ $i ] ] ) ? $data['param_7'][ $data['dates'][ $i ] ] : '&nbsp;';
				echo '<td class="value">'.$val.'</td>';
			}
			?>
		</tr>

		<tr class="simple-params urine">
			<td class="title">Суточное количество мочи</td>
			<?php
			for( $i=0; $i<$total_dates; $i++ ){
				$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_8'][ $data['dates'][ $i ] ] ) ? $data['param_8'][ $data['dates'][ $i ] ] : '&nbsp;';
				echo '<td class="value">'.$val.'</td>';
			}
			?>
		</tr>

		<tr class="simple-params feces">
			<td class="title">Стул</td>
			<?php
			for( $i=0; $i<$total_dates; $i++ ){
				$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_9'][ $data['dates'][ $i ] ] ) ?
						'<img src="/img/EvnPrescrPlan/list_print_evn_observ_graphs_'.( $data['param_9'][ $data['dates'][ $i ] ] == 2 ? 'y' : 'n' ).'.png" alt="" />'
						: '';
				echo '<td class="value"><div>'.$val.'</div></td>';
			}
			?>
		</tr>

		<tr class="simple-params bath">
			<td class="title">Ванна</td>
			<?php
			for( $i=0; $i<$total_dates; $i++ ){
				$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_10'][ $data['dates'][ $i ] ] ) ?
						'<img src="/img/EvnPrescrPlan/list_print_evn_observ_graphs_'.( $data['param_10'][ $data['dates'][ $i ] ] == 2 ? 'y' : 'n' ).'.png" alt="" />'
						: '';
				echo '<td class="value"><div>'.$val.'</div></td>';
			}
			?>
		</tr>
		<tr class="simple-params bath">
			<td class="title">Смена белья</td>
			<?php
			for( $i=0; $i<$total_dates; $i++ ){
				$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_11'][ $data['dates'][ $i ] ] ) ?
						'<img src="/img/EvnPrescrPlan/list_print_evn_observ_graphs_'.( $data['param_11'][ $data['dates'][ $i ] ] == 2 ? 'y' : 'n' ).'.png" alt="" />'
						: '';
				echo '<td class="value"><div>'.$val.'</div></td>';
			}
			?>
		</tr>
		<tr class="illness-day-num">
			<td class="title">&nbsp;</td>
			<?php
			// Список дней болезни п/п
			for( $i=1; $i<=$total_dates; $i++ ){
				echo '<td>'.$i.'</td>';
			}
			?>
		</tr>

		<tr class="print-date">
			<td class="title">&nbsp;</td>
			<?php
			// Список дат п/п
			for( $i=0; $i<$total_dates; $i++ ){
				echo '<td>'.( array_key_exists( $i, $data['dates'] ) ? date('d.m',$data['dates'][$i]) : '&nbsp;' ).'</td>';
			}
			?>
		</tr>

		</table>
	</div>
</div>
<?php
}