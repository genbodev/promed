<?php
function printGraph( $data, $info, $lpu, $index=1 ){
	$html = '';
	
	// Просчитываем ряд недостающих дат
	if ( ( $data['date_start'] + 86400 ) < $data['date_finish'] ) {
		for( $date=$data['date_start']; $date<$data['date_finish']; $date+=86400 ) if ( !in_array( $date, $data['dates'] ) ) {
			$data['dates'][] = $date;
		}
		// Отсортируем список дат
		sort( $data['dates'] );
	}
	
	// Всего дней
	$total_dates = sizeof( $data['dates'] );
	// Максимальное количество дней на графике
	$graph_max_days = 15;
	// Всего страниц
	$total_pages = ceil( $total_dates / $graph_max_days );
	// П/н дня начала болезни для текущего графика
	$start_ill_day = $graph_max_days * $index - $graph_max_days + 1;
	// П/н дня окончания болезни для текущего графика
	$finish_ill_day = $start_ill_day + $graph_max_days - 1;
	// Делений за день, например, У и В, на случай если захотят ввести Д и тп
	$division = 2;
	// Высота ячейки сетки px
	$cell_height = 12;
	// Ширина ячейки сетки px
	$cell_width = 20;
	// Ячеек в вертикальной группе
	$cells_in_group = 5;
	// Количество вертикальных групп ячеек
	$cells_groups = 7;
	// Высота вертикальной группы ячеек
	$cell_group_height = $cell_height*$cells_in_group + $cells_in_group*1 - 1 - 3*2; // Высота группы ячеек px: сумма высот всех ячеек + сумма толщин границ ячеек - корректирующее число - паддинг ячейки
	// Длина слоя с графиком
	$layout_width = $graph_max_days * $division * $cell_width + $graph_max_days * $division;
	// Высота слоя с графиком
	$layout_height = $cells_groups * $cells_in_group * $cell_height + $cells_groups * $cells_in_group;
	// Группы графика. Данные для генерации по оси Y
	$data_y = array(
		1 => array(140,120,100,90,80,70,60),
		2 => array(200,175,150,125,100,75,50),
		3 => array(41,40,39,38,37,36,35)
	);
	
	$html .= '
		<div class="list-print">
			<table>
	';
	
	// Список дат п/п
	$html .= '
			<tr class="print-date">
				<td class="title" colspan="3">Дата</td>
	';
	for( $i=($start_ill_day-1); $i<$finish_ill_day; $i++ ){
		$html .= '<td colspan="2">'.( array_key_exists( $i, $data['dates'] ) ? date('d.m',$data['dates'][$i]) : '&nbsp;' ).'</td>';
	}
	$html .= '</tr>';
	
	// Список дней болезни п/п
	$html .= '
			<tr class="illness-day-num">
				<td class="title" colspan="3">День болезни</td>
	';
	for( $i=$start_ill_day; $i<=$finish_ill_day; $i++ ){
		// @todo Высчитывать дни болезни
		// $html .= '<td colspan="2">'.( $i <= $total_dates ? $i : '&nbsp;' ).'</td>';
		$html .= '<td colspan="2">'.$i.'</td>';
	}
	$html .= '</tr>';
	
	// День пребывания в стационаре п/п
	$html .= '
			<tr class="hospital-day-num">
				<td class="title" colspan="3">День пребывания<br />в стационаре</td>
	';
	for( $i=$start_ill_day; $i<=$finish_ill_day; $i++ ){
		// @todo Высчитывать дни пребывания в стационаре
		// $html .= '<td colspan="2">'.( $i <= $total_dates ? $i : '&nbsp;' ).'</td>';
		$html .= '<td colspan="2">'.$i.'</td>';
	}
	$html .= '</tr>';
	
	// Заголовок столбцов графика
	$html .= '
			<tr class="time_type">
				<td class="title pulse">П</td>
				<td class="title pressure">Д</td>
				<td class="title temperature">t&deg;</td>
	';
	for( $i=1; $i<=($graph_max_days*$division); $i++ ){
		$html .= ($i%$division) ? '<td>у</td>' : '<td>в</td>';
	}
	$html .= '</tr>';
	
	// График
	$html .= '
		<tr>
			<td colspan="3" class="groups" style="vertical-align: top; padding: 0;">
	';
	
	$html .= '<table>';
	for( $r=0; $r<$cells_groups; $r++ ){
		$html .= '
			<tr class="time_type">
				<td class="title pulse" style="height: '.$cell_group_height.'px;">'.$data_y[1][$r].'</td>
				<td class="title pressure">'.$data_y[2][$r].'</td>
				<td class="title temperature">'.$data_y[3][$r].'</td>
			</tr>
		';
	}
	$html .= '</table>';
	
	$html .= '
			</td>
			<!--
			<td class="grid-column" colspan="'.($graph_max_days*$division).'">
				<div class="grid-wrapper">
					<div class="graph-grid" id="graph_grid_'.$index.'" style="height: '.($layout_height-1).'px;"></div>
					<div class="graph"><div id="graph_'.$index.'" style="width: '.($layout_width-1).'px; height: '.($layout_height-1).'px;"></div></div>
				</div>
			</td>
			-->
			<td colspan="'.($graph_max_days*$division).'">
				<div class="grid-layout">
					<div class="graph-grid" id="graph_grid_'.$index.'" style="height: '.($layout_height-1).'px;"></div>
					<div class="grid-wrapper">
						<div class="graph"><div id="graph_'.$index.'" style="width: '.($layout_width-1).'px; height: '.($layout_height-1).'px;"></div></div>
					</div>
				</div>
			</td>
		</tr>
	';
	
	// Дыхание
	$html .= '
			<tr class="simple-params breath">
				<td class="title" colspan="3">Дыхание</td>
	';
	for( $i=($start_ill_day-1); $i<$finish_ill_day; $i++ ){
		$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_5'][ $data['dates'][ $i ] ] ) ? $data['param_5'][ $data['dates'][ $i ] ] : '&nbsp;';
		$html .= '<td colspan="'.$division.'" class="value">'.$val.'</td>';
	}
	$html .= '</tr>';
	
	// Вес
	$html .= '
			<tr class="simple-params weight">
				<td class="title" colspan="3">Вес</td>
	';
	for( $i=($start_ill_day-1); $i<$finish_ill_day; $i++ ){
		$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_6'][ $data['dates'][ $i ] ] ) ? $data['param_6'][ $data['dates'][ $i ] ] : '&nbsp;';
		$html .= '<td colspan="'.$division.'" class="value">'.$val.'</td>';
	}
	$html .= '</tr>';
	
	// Выпито жидкости
	$html .= '
			<tr class="simple-params aqua">
				<td class="title" colspan="3">Выпито жидкости</td>
	';
	for( $i=($start_ill_day-1); $i<$finish_ill_day; $i++ ){
		$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_7'][ $data['dates'][ $i ] ] ) ? $data['param_7'][ $data['dates'][ $i ] ] : '&nbsp;';
		$html .= '<td colspan="'.$division.'" class="value">'.$val.'</td>';
	}
	$html .= '</tr>';
	
	// Суточное количество мочи
	$html .= '
			<tr class="simple-params urine">
				<td class="title" colspan="3">Суточное количество мочи</td>
	';
	for( $i=($start_ill_day-1); $i<$finish_ill_day; $i++ ){
		$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_8'][ $data['dates'][ $i ] ] ) ? $data['param_8'][ $data['dates'][ $i ] ] : '&nbsp;';
		$html .= '<td colspan="'.$division.'" class="value">'.$val.'</td>';
	}
	$html .= '</tr>';
	
	// Стул
	$html .= '
			<tr class="simple-params feces">
				<td class="title" colspan="3">Стул</td>
	';
	for( $i=($start_ill_day-1); $i<$finish_ill_day; $i++ ){
//		$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_9'][ $data['dates'][ $i ] ] ) ? 'val_'.(int)$data['param_9'][ $data['dates'][ $i ] ] : '';
//		$html .= '<td colspan="'.$division.'" class="value"><span class="'.$val.'"><img src="/img/EvnPrescrPlan/list_print_evn_observ_graphs_yn.png" alt="" /></span></td>';
		$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_9'][ $data['dates'][ $i ] ] ) ?
				'<img src="/img/EvnPrescrPlan/list_print_evn_observ_graphs_'.( $data['param_9'][ $data['dates'][ $i ] ] == 2 ? 'y' : 'n' ).'.png" alt="" />'
				: '';
		$html .= '<td colspan="'.$division.'" class="value"><div>'.$val.'</div></td>';
	}
	$html .= '</tr>';
	
	// Ванна
	$html .= '
			<tr class="simple-params bath">
				<td class="title" colspan="3">Ванна</td>
	';
	for( $i=($start_ill_day-1); $i<$finish_ill_day; $i++ ){
//		$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_10'][ $data['dates'][ $i ] ] ) ? 'val_'.(int)$data['param_10'][ $data['dates'][ $i ] ] : '';
//		$html .= '<td colspan="'.$division.'" class="value"><span class="'.$val.'"><img src="/img/EvnPrescrPlan/list_print_evn_observ_graphs_yn.png" alt="" /></span></td>';
		$val = array_key_exists( $i, $data['dates'] ) && isset( $data['param_10'][ $data['dates'][ $i ] ] ) ?
				'<img src="/img/EvnPrescrPlan/list_print_evn_observ_graphs_'.( $data['param_10'][ $data['dates'][ $i ] ] == 2 ? 'y' : 'n' ).'.png" alt="" />'
				: '';
		$html .= '<td colspan="'.$division.'" class="value"><div>'.$val.'</div></td>';
	}
	$html .= '</tr>';
	
	// Подвал
	$html .= '
			</table>
		</div>
	';
	
	// График по оси Y разделен на N ячеек
	// Для того чтобы отрисовать показатель, необходимо привести его к значению от 0 до N
	// для этого из полученного значения показателя, мы вычитаем его нижнюю границу на графике
	// и производим необходимые вычисления
	// Например, температура 38.8, нижняя граница температуры 34, получем значение:
	// 38.8 - 34 = 4.8;
	// В случае с пульсом немного сложей, ячейки 1-5 (50-100) соответствуют 10-ке значений,
	// а 6-7 (100-140) уже 20-ке. Поэтому нам надо привести значения больше 100
	// и меньше 100 поотдельности.
	
	// Тип времени суток из таблицы ObservTimeType
	// array( <id> => <коэфициент смещения> )
	$time_types = array(
		1 => 1,
		2 => 2,
		3 => 3
	);
	
	$temperature = array();
	foreach( $data['temperature'] as $date => $v ) {
		$key = array_search( $date, $data['dates'] );
		$key *= $division;
		
		foreach( $time_types as $t ) if ( array_key_exists( $t, $v ) ) {
			$val = (float) $v[ $t ] - 34;
			$key1 = $key + $t * 0.5; // смещаем график в зависимости от времени суток: утро, день, вечер

			// Смещаем график на втором и последующих листах
			if ( $index >= 2 ) $key1 -= $start_ill_day * $division - $division;
			$temperature[] = '['.$key1.','.$val.']';
			if ( sizeof( $data['temperature'] ) == 1 && sizeof( $v ) == 1 ) $temperature[] = '['.$key1.','.($val+0.1).']';
		}
	}
	$temperature = '['.implode(',',$temperature).']';
	
	$pulse = array();
	foreach( $data['pulse'] as $date => $v ) {
		$key = array_search( $date, $data['dates'] );
		$key *= $division;
		
		foreach( $time_types as $t ) if ( array_key_exists( $t, $v ) ) {
			if(!is_numeric($v[ $t ])) continue;
			$val = $v[ $t ];
			$key1 = $key + $t * 0.5; // смещаем график в зависимости от времени суток: утро, день, вечер

			if ( $val > 100 ) {
				$val1 = ( 100 - 50 ) / 10;
				$val2 = ( $val-100 ) / 20;
				$val = $val1 + $val2;
			} else {
				$val = ( (int) $val - 50 ) / 10;
			}

			// Смещаем график на втором и последующих листах
			if ( $index >= 2 ) $key1 -= $start_ill_day * $division - $division;
			$pulse[] = '['.$key1.','.$val.']';
		}
	}
	$pulse = '['.implode(',',$pulse).']';
	
	$pressure = array();
	foreach( $data['blood_pressure'] as $date => $v ){
		$key = array_search( $date, $data['dates'] );
		$key *= $division;
		
		foreach( $time_types as $t ) if ( array_key_exists( $t, $v ) ) {
			$val = $v[ $t ];
			if(!is_numeric($val['low']) || !is_numeric($val['high'])) continue;
			$key1 = $key + $t * 0.5; // смещаем график в зависимости от времени суток: утро, день, вечер
			
			$val_low = ( (int) $val['low'] - 25 ) / 25;
			$val_high = ( (int) $val['high'] - 25 ) / 25;

			// Смещаем график на втором и последующих листах
			if ( $index >= 2 ) $key1 -= $start_ill_day * $division - $division;
			$pressure[] = '[['.$key1.','.$val_low.'],['.$key1.','.$val_high.']]';
		}
	}
	$pressure = '['.implode(',',$pressure).']';
	
	$html .= '
		<script>
		window.addEventListener("load",function(){
			drawGrid( document.getElementById("graph_grid_'.$index.'"), {
				width: '.( $graph_max_days * $division * $cell_width + $graph_max_days * $division + 1 ).',
				height: '.( $cells_groups * $cells_in_group * $cell_height + $cells_groups * $cells_in_group + 1 ).',
				cells_x: '.( $graph_max_days * $division ).',
				cells_y: '.( $cells_in_group * $cells_groups ).',
				cell_w: '.$cell_width.',
				cell_h: '.$cell_height.'
			});

			var data = [];
			
			// Температура
			data.push({
				shadowSize: 0,
				data: '.$temperature.',
				lines: {
					show: true,
					lineWidth: 3
				}
			});
			
			// Пульс
			data.push({
				data: '.$pulse.',
				lines: {
					show: true,
					lineWidth: 1,
					steps: false,
					stacked: true
				},
				points: {
					show: true
				}
			});
			
			// Артериальное давление
			var pressure = '.$pressure.';
			for( var key in pressure ){
				data.push({
					shadowSize: 0,
					data: pressure[key],
					lines: {
						show: true,
						lineWidth: 1
					},
					markers: {
						show: true,           // => setting to true will show markers, false will hide
						lineWidth: 1,          // => line width of the rectangle around the marker
						color: "#000000",      // => text color
						fill: false,           // => fill or not the marekers rectangles
						fillColor: "#FFFFFF",  // => fill color
						fillOpacity: 0.4,      // => fill opacity
						stroke: false,         // => draw the rectangle around the markers
						position: "tc",        // => the markers position (vertical align: b, m, t, horizontal align: l, c, r)
						verticalMargin: -3,     // => the margin between the point and the text.
						labelFormatter: function(o){
							return "  _"
						},
						fontSize: Flotr.defaultOptions.fontSize,
						stacked: false,        // => true if markers should be stacked
						stackingType: "b",     // => define staching behavior, (b- bars like, a - area like) (see Issue 125 for details)
						horizontal: false      // => true if markers should be horizontal (For now only in a case on horizontal stacked bars, stacks should be calculated horizontaly)

					}
				});
			}
			
			drawGraph( document.getElementById("graph_'.$index.'"), data, {
				xaxis_max: '.( $graph_max_days * $division ).'
			});
		});
		</script>
	';
	
	if ( $total_dates > $finish_ill_day ) {
		$html .= '<p style="page-break-before: always;">&nbsp;</p>';
		$html .= printGraph( $data, $info, $lpu, ++$index );
	}
	
	return $html;
}
?>
<?php if ( !isset( $is_pdf ) || !$is_pdf ): ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	<title>Температурный график</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<link href="/css/print_evn_observ_graphs.css" rel="stylesheet">
	
	<script src="/jscore/libs/flotr/flotr2.min.js" type="text/javascript"></script>
	<script src="/jscore/print_evn_observ_graphs.js" type="text/javascript"></script>
</head>

<body class="all-cities">
<?php endif; ?>

<div class="wrapper">
	
	<div class="print-wrapper">
		<div class="print-title">
			<div class="head-wrapper">
				<div class="right OKUD">Код формы по ОКУД <span>&nbsp;</span></div>
				<div class="right OKPO">Код учреждения по ОКПО <span>&nbsp;</span></div>
				<div class="right info">
					Медицинская документация
					<br /><br />
					Форма N 004/У
					<br />
					Утверждена Минздравом СССР
					<br />
					04.10.80 г. N 1030
				</div>
				<div class="lpu">
					<?php echo $lpu['Lpu_Name']; ?>
				</div>
				
				<h1>Температурный лист</h1>
			</div>
		</div>
		<div class="patient-info">
			Карта N <span class="writeable">&nbsp;<?php echo $info['EvnPs_NumCard']; ?>&nbsp;</span>
			Фамилия. имя, о. больного <span class="writeable">&nbsp;<?php echo $info['Person_Fio']; ?>&nbsp;</span>
			Палата N <span class="writeable">&nbsp;<?php echo $info['LpuSectionWard_Name']; ?>&nbsp;</span>
		</div>
		<?php echo printGraph( $graph_data, $info, $lpu ); ?>
		<div class="pring-footer">
			<img src="/img/EvnPrescrPlan/list_print_evn_observ_graphs_legend.png" alt="Легенда" />
		</div>
	</div>
	
</div><!-- .wrapper -->

<?php if ( !isset( $is_pdf ) || !$is_pdf ): ?>
<footer class="footer">
	<strong>Подвал</strong>
</footer><!-- .footer -->

</body>
</html>
<?php endif; ?>