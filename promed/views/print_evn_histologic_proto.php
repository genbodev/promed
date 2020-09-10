<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать протокола патологогистологического исследования</title>
<style type="text/css">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	table { border-collapse: collapse; }
	span, div, td { font-family: 'courier new', courier, 'Times New Roman', Times, serif; font-size: 9pt; }
	td { vertical-align: bottom; border: none; }
</style>

<style type="text/css" media="print">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	span, div, td { font-family: 'courier new', courier, 'Times New Roman', Times, serif; font-size: 9pt; }
	td { vertical-align: bottom; border: none; }
</style>

<style type="text/css">
	table.ct { width:100%; }
	table.ct td { border: none 1px black; vertical-align: top; }
	table.ctleft td { text-align:left; }
	table.ctcenter td { text-align:center; }
	table.ct td.small { width: 14px; }
	table.ct td.tleft { text-align: left; }
	table.ct td.dashed { border-bottom-style: dashed; vertical-align: bottom; }
</style>

<style type="text/css">
	.small { font-size: 8pt; }	
	div.selector { display:none; }
	div.cutline { border: 1px black none; border-bottom-style:dashed; border-weight:1px; text-align:center; font-size:0.8em; }	
</style>
</head>

<body class="portrait">

<div>Оборотная сторона ф. N 014/у</div>
<div style="margin: 1em 0em 1em 0em; text-align: center;">Патологогистологические исследования N <span style="text-decoration: underline;">{EvnHistologicProto_Ser} {EvnHistologicProto_Num}</span> <*>. Дата и часы поступления <span style="text-decoration: underline;">{EvnHistologicProto_setDate} {EvnHistologicProto_setTime}</span></div>
<div>--------------------------------</div>
<div style="margin-bottom: 1em;"><*> - Заполняется под копирку в двух экземплярах. Необходимое вписать, подчеркнуть.</div>

<table style="border: none; width: 100%;">
<tr>
<td style="width: 25%;">Биопсия диагностическая</td>
<td style="width: 25%; border-bottom: 1px solid #000; text-align: center;">{EvnHistologicProto_IsDiag}</td>
<td style="width: 25%; text-align: center;">Биопсия срочная</td>
<td style="width: 25%; border-bottom: 1px solid #000; text-align: center;">{EvnHistologicProto_IsUrgent}</td>
</tr>
</table>

<table style="border: none; width: 100%;">
<tr>
<td style="width: 25%;">Операционный материал</td>
<td style="width: 10%; border-bottom: 1px solid #000; text-align: center;">{EvnHistologicProto_IsOper}</td>
<td style="width: 25%; text-align: center;">Количество кусочков</td>
<td style="width: 10%; border-bottom: 1px solid #000; text-align: center;">{EvnHistologicProto_BitCount}</td>
<td style="width: 5%;">,</td>
<td style="width: 15%; text-align: center;">блоков</td>
<td style="width: 10%; border-bottom: 1px solid #000; text-align: center;">{EvnHistologicProto_BlockCount}</td>
</tr>
</table>

<table style="border: none; width: 100%;">
<tr>
<td style="width: 20%;">Методика окраски</td>
<td style="width: 20%; border-bottom: 1px solid #000;">&nbsp;</td>
<td style="width: 40%; text-align: center;">Макро- и микроскопическое описание:</td>
<td style="width: 20%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr>
</table>
{EvnHistologicProtoDescrList}
<div style="border-bottom: 1px solid #000;">{EvnHistologicProto_Descr}</div>
{/EvnHistologicProtoDescrList}

<table style="border: none; width: 100%;">
<tr>
<td style="width: 50%;">Патологогистологическое заключение (диагноз):</td>
<td style="width: 50%; border-bottom: 1px solid #000;">&nbsp;</td>
</tr>
</table>
{EvnHistologicProtoHistologicConclusionList}
<div style="border-bottom: 1px solid #000;">{EvnHistologicProto_HistologicConclusion}</div>
{/EvnHistologicProtoHistologicConclusionList}

<table style="border: none; width: 100%;">
<tr>
<td style="width: 10%;">Код</td>
<td style="width: 90%; border-bottom: 1px solid #000;">{Diag_Code}</td>
</tr>
</table>

<div>
Дата исследования
&quot;<span style="text-decoration: underline;">&nbsp;{EvnHistologicProto_didDay}&nbsp;</span>&quot;
<span style="text-decoration: underline;">&nbsp;{EvnHistologicProto_didMonth}&nbsp;</span>
<span style="text-decoration: underline;">&nbsp;{EvnHistologicProto_didYear}&nbsp;</span>г.
</div>

<div style="margin-top: 2em; padding-right: 3em; text-align: right;">
	<div>Фамилия патологоанатома <span style="text-decoration: underline;">&nbsp;&nbsp;{MedPersonal_Fio}&nbsp;&nbsp;</span></div>
	<div style="margin-bottom: 1em; margin-top: 1em;">Подпись _________________</div>
	<div>Фамилия лаборанта <span style="text-decoration: underline;">&nbsp;&nbsp;{MedPersonalS_Fio}&nbsp;&nbsp;</span></div>
	<div style="margin-top: 1em;">Подпись _________________</div>
</div>

</body>

</html>