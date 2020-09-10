<html>
<head>
	<title>Протокол ВК</title>

	<style type="text/css">
		@page port { size: portrait }
		@page land { size: landscape }
		body { margin: 0px; padding: 10px; width: 800px }
		h3 { font-weight: normal; margin: 0; }
		.title { text-align: center }
		.underline { border-bottom: 1px solid black; }
		.col1 { vertical-align: top; }
	</style>

	<style type="text/css" media="print">
		@page port { size: portrait }
		@page land { size: landscape }
		body { margin: 0px; padding: 0px; width: 100% }
		h3 { font-weight: normal; margin: 0; }
		.title { text-align: center }
		.underline { border-bottom: 1px solid black; }
		.col1 { vertical-align: top; }
	</style>
</head>

<body class="land">
<!-- /*NO PARSE JSON*/ -->

<h3 class="title">{Lpu_Name}</h3>
<h3 class="title">Этапный эпикриз при направлении на ВК</h3>

<table width="100%">
	<tr>
		<th width="40%"></th>
		<th width="60%"></th>
	</tr>
	<tr>
		<td class="col1">При направлении на ВК больной</td>
		<td class="underline">{Person_Fio}</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="17%"></th>
		<th width="17%"></th>
		<th width="15%"></th>
		<th width="41%"></th>
	</tr>
	<tr>
		<td>Дата рождения</td>
		<td class="underline">{Person_BirthDay}</td>
		<td>Место работы</td>
		<td class="underline">{Job_Name}</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="15%"></th>
		<th width="85%"></th>
	</tr>
	<tr>
		<td>Должность</td>
		<td class="underline">{Post_Name}</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="10%"></th>
		<th width="20%"></th>
		<th width="5%"></th>
		<th width="20%"></th>
		<th width="5%"></th>
		<th width="20%"></th>
		<th width="5%"></th>
		<th width="15%"></th>
	</tr>
	<tr>
		<td>на л/н №</td>
		<td class="underline">{EvnStick_Ser} {EvnStick_Num}</td>
		<td>с</td>
		<td class="underline">{EvnStick_begDate}</td>
		<td>по</td>
		<td class="underline">{EvnStick_endDate}</td>
		<td>дней</td>
		<td class="underline">{EvnVK_StickPeriod}</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="15%"></th>
		<th width="25%"></th>
		<th width="15%"></th>
		<th width="45%"></th>
	</tr>
	<tr>
		<td>Койко-дней</td>
		<td class="underline">{EvnVK_StickDuration}</td>
		<td>план по МЭС</td>
		<td class="underline">&nbsp;</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="20%"></th>
		<th width="50%"></th>
		<th width="19%"></th>
		<th width="11%"></th>
	</tr>
	<tr>
		<td>Диагноз основной</td>
		<td class="underline">{Diag1_Name}</td>
		<td>шифр по МКБ-10</td>
		<td class="underline">{Diag1_Code}</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="20%"></th>
		<th width="80%"></th>
	</tr>
	<tr>
		<td>Основное заболевание</td>
		<td class="underline">{EvnVK_MainDisease}</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="15%"></th>
		<th width="85%"></th>
	</tr>
	<tr>
		<td>Сопутствующие</td>
		<td class="underline">{Diag2_Name}</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="15%"></th>
		<th width="85%"></th>
	</tr>
	<tr>
		<td>Осложнения</td>
		<td class="underline">{Diag3_Name}</td>
	</tr>
	<tr>
		<td class="underline" colspan="2">&nbsp;</td>
	</tr>
</table>

<table width="100%">
	<caption style="text-align: left;">
		Динамика состояния и эффективность проводимого лечения:
	</caption>
	<tr><td class="underline">&nbsp;</td></tr>
	<tr><td class="underline">&nbsp;</td></tr>
	<tr><td class="underline">&nbsp;</td></tr>
	<tr><td class="underline">&nbsp;</td></tr>
	<tr><td class="underline">&nbsp;</td></tr>
	<tr><td class="underline">&nbsp;</td></tr>
</table>

<span>При направлении на ВК рекомендовано:</span>

<table width="100%">
	<tr>
		<th width="45%"></th>
		<th width="25%"></th>
		<th width="5%"></th>
		<th width="25%"></th>
	</tr>
	<tr>
		<td>7. Продление листка нетрудоспособности с</td>
		<td class="underline">&nbsp;</td>
		<td>по</td>
		<td class="underline">&nbsp;</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="42%"></th>
		<th width="58%"></th>
	</tr>
	<tr>
		<td>8. Выдача справки для трудоустройства</td>
		<td class="underline">&nbsp;</td>
	</tr>
	<tr>
		<td class="underline" colspan="2">&nbsp;</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="67%"></th>
		<th width="33%"></th>
	</tr>
	<tr>
		<td>9. Разрешение выписки лекарственных средств сверх формуляра</td>
		<td class="underline">&nbsp;</td>
	</tr>
	<tr>
		<td class="underline" colspan="2">&nbsp;</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<td>10. Решение вопроса о направлении на МСЭ</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="47%"></th>
		<th width="25%"></th>
		<th width="5%"></th>
		<th width="23%"></th>
	</tr>
	<tr>
		<td>11. Направление на долечивание в санаторий</td>
		<td class="underline">&nbsp;</td>
		<td>c</td>
		<td class="underline">&nbsp;</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="35%"></th>
		<th width="65%"></th>
	</tr>
	<tr>
		<td>12. Другие причины (указать)</td>
		<td class="underline">&nbsp;</td>
	</tr>
	<tr>
		<td class="underline" colspan="2">&nbsp;</td>
	</tr>
</table>

<table width="100%">
	<caption>
		Решение ВК
	</caption>
	{EvnVK_DecisionVK}
</table>

<table width="100%">
	<tr>
		<th width="35%"></th>
		<th width="20%"></th>
		<th width="45%"></th>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Председатель ВК</td>
		<td class="underline">{vkchairman}</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="35%"></th>
		<th width="17%"></th>
		<th width="48%"></th>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Члены ВК врач</td>
		<td class="underline">{vkexpert1}</td>
	</tr>
</table>

<table width="100%">
	<tr>
		<th width="35%"></th>
		<th width="10%"></th>
		<th width="55%"></th>
	</tr>

	{vkexperts}
	<tr>
		<td>&nbsp;</td>
		<td>Врач</td>
		<td class="underline">{MP_Person_Fio}</td>
	</tr>
	{/vkexperts}

</table>

</body>

</html>