<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Печать Обоснования начальной (максимальной) цены контракта (лота)</title>
<style type="text/css">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	table { border-collapse: collapse; }
	span, div, td { font-family:  'Times New Roman', Times, serif; font-size: 8pt; }
	td { vertical-align: middle; border: solid 1px black; }
	th { border: solid 1px black; }
	.noprint { display: auto; }
	td.tright { text-align: right; } 
</style>

<style type="text/css" media="print">
	@page port { size: portrait }
	@page land { size: landscape }
	body { margin: 0px; padding: 0px; }
	span, div, td { font-family:  'Times New Roman', Times, serif; font-size: 8pt; }
	td { vertical-align: middle; border: none; }
	.noprint { display: none; }
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
	div.selector { display:none; }
	div.show_selector { display:none; }
	div.single_selector { display:inline; }
	div.cutline { border: 1px black none; border-bottom-style:dashed; border-weight:1px; text-align:center; font-size:0.8em; }	
</style>
</head>

<body class="portrait">
<table style="width: 100%; border: none; margin-bottom: 2em;" cellspacing="0" cellpadding="2">
<tr>
	<td>&nbsp;</td>
	<td style="float: right; width: 280px;">
		Приложение № 3<br/>
        <b>Утверждаю</b>
	</td>
</tr>
</table>

<div style="font-weight: bold;">
    Обоснование начальной (максимальной) цены<br/>
    контракта для определения поставщика для<br/><br>
    <?php if(!empty($obj)) { ?>
    {obj}
	{PurchObjType_Name} {WName}<br/>
	{/obj}
	<?php } ?>
	<br/>
</div>
<div>
Начальная (максимальная) цена  Контракта (далее - НМЦК) определена в соответствии с Федеральным законом, приказом Минэкономразвития России от 02.10.2013 N 567 «Об утверждении Методических рекомендаций по применению методов определения начальной (максимальной) цены контракта, цены контракта, заключаемого с единственным поставщиком (подрядчиком, исполнителем)».
<br/><br/>
</div>

<?php if(!empty($goods)) { ?>
<div>
<b>Метод определения НМЦК:</b> метод сопоставимых рыночных цен (анализа рынка).
</div>
<table>
	<tr>
		<th rowspan="3">№ п/п</th>
		<th></th>
		<th colspan="<?php echo ($count + 8); ?>">Таблица цен для определения начальной (максимальной) цены контракта</th>
		<th colspan="2">Справочно*******</th>
	</tr>
	<tr>
		<th rowspan="2">ОКПД</th>
		<th rowspan="2">Международное непатентованное наименование или химическое, группировочное наименование лекарственного препарата</th>
		<th rowspan="2">Лекарственная форма*</th>
		<th rowspan="2">Дозировка**</th>
		<th rowspan="2">Единица измерения товара***</th>
		<th rowspan="2">Количество (единиц измерения)</th>
		<th colspan="<?php echo ($count); ?>">Источники информации и цена за единицу, руб.****</th>
		<th colspan="3">Определение однородности и средних значений цен*****</th>
		<th rowspan="2">Форма выпуска</th>
		<th rowspan="2">Количество упаковок с учетом формы выпуска</th>
	</tr>
	<tr>
		{columns}
		<th>Предложение поставщика {num}<br />{name}</th>
		{/columns}
		<th>Коэфф. вариации (V), %</th>
		<th>совокупн. значений</th>
		<th>Сред. цена******, руб.</th>
	</tr>
	{goods}
	<tr>
		<td class="tright">{numb}</td>
		<td>{Okpd_Name}</td>
		<td>{DMnnName}<br />{Tradename}</td>
		<td>{DrugForm}</td>
		<td>{DoseName}</td>
		<td>{GUNick}</td>
		<td class="tright">{WCount}</td>
		{prices}
		<td class="tright">{price}</td>
		{/prices}
		<td class="tright">{coef}</td>
		<td>{sovokupn}</td>
		<td class="tright">{midprice}</td>
		<td class="tright">{FasName}</td>
		<td class="tright">{SpecKolvo}</td>
	</tr>
	<tr style="border-bottom:solid black 1px;">
		<td colspan="<?php echo (9+$count); ?>"></td>
		<td class="tright">{rowprice}</td>
		<td colspan="2"></td>
	</tr>
	{/goods}
</table>
<h4>Итого: {itogo} руб</h4>
<?php } ?>


<?php if(!empty($goods2)) { ?>
<div>
<b>Метод определения НМЦК: тарифный метод</b>
</div>
<table>
	<tr>
		<th rowspan="3">№ п/п</th>
		<th></th>
		<th colspan="<?php echo ($count2 + 6); ?>">Таблица цен для определения начальной (максимальной) цены контракта</th>
		<th colspan="2">Справочно*******</th>
	</tr>
	<tr>
		<th rowspan="2">ОКПД</th>
		<th rowspan="2">Международное непатентованное наименование или химическое, группировочное наименование лекарственного препарата</th>
		<th rowspan="2">Лекарственная форма*</th>
		<th rowspan="2">Дозировка**</th>
		<th rowspan="2">Единица измерения товара***</th>
		<th rowspan="2">Количество (единиц измерения)</th>
		<th colspan="<?php echo ($count2); ?>">Источники информации и цена за единицу, руб.****</th>
		<th>Определение однородности и средних значений цен*****</th>
		<th rowspan="2">Форма выпуска</th>
		<th rowspan="2">Количество упаковок с учетом формы выпуска</th>
	</tr>
	<tr>
		{columns2}
		<th>{name}</th>
		{/columns2}
		<th>Сред. цена******, руб.</th>
	</tr>
	{goods2}
	<tr>
		<td class="tright">{numb}</td>
		<td>{Okpd_Name}</td>
		<td>{DMnnName}<br />{Tradename}</td>
		<td>{DrugForm}</td>
		<td>{DoseName}</td>
		<td>{GUNick}</td>
		<td class="tright">{WCount}</td>
		{prices}
		<td class="tright">{price}</td>
		{/prices}
		<td class="tright">{midprice}</td>
		<td class="tright">{FasName}</td>
		<td class="tright">{SpecKolvo}</td>
	</tr>
	<tr style="border-bottom:solid black 1px;">
		<td colspan="<?php echo (7+$count2); ?>"></td>
		<td class="tright">{rowprice}</td>
		<td colspan="2"></td>
	</tr>
	{/goods2}
</table>
<h4>Итого: {itogo2} руб</h4>
<?php } ?>

<?php if(!empty($goods) || !empty($goods2)) { ?>
<div>
* Участник имеет право предложить любую из представленных лекарственных форм.
</div>
<div>
** Дозировка лекарственного препарата должна быть указана в соответствии  с регистрационным удостоверением на данное лекарственное средство.	
</div>
<div>
*** Единица измерения товара может быть указана как единица измерения массы действующего вещества, так и, как единица измерения, указанная в государственном реестре лекарственных средств по данному МНН, например, первичная упаковка. Действующее вещество – химическое вещество или уникальная биологическая субстанция в составе лекарственного средства, с физиологическим действием которой на организм связывают лечебные свойства данного препарата.	
</div>
<div>
**** Применение корректирующих коэффициентов и индексов в рамках данного исследования нецелесообразно.	
</div>
<div>
***** Определение однородности совокупности цен в соответствии с п.3.20 приказа Минэкономразвития России от 02.10.2013 N 567	
</div>
<div>
****** Среднее значение цен определено по формуле в соответствии с п.3.21 приказа Минэкономразвития России от 02.10.2013 N 567	
</div>
<div>
******* Участник может предложить другое количество упаковок в случае изменения фасовки, но не менее потребности Заказчика, указанной в графе "Количество". Участник вправе предлагать иную упаковку, только в случае если такая упаковка зарегистрирована в государственном реестре лекарственных средств по данному МНН.	
</div>
<?php } ?>

<div style="text-align:center;">
<b>ВЫВОД:</b> Проведенные исследования позволяют определить начальную (максимальную) цену контракта в размере <span>{mainitogo}</span><b> рублей.</b>
</div>

</body>
</html>