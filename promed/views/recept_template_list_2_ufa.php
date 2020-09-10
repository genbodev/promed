<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>{recept_template_title}</title>
<style type="text/css">
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: tahoma, verdana; font-size: 8px; }

.polis_ser_num { width: 3%; border: 1px solid #000; font-size: 10px; }
.drugform { font-weight: bold; font-size: 7px; }
</style>

<style type="text/css" media="print">
body { margin: 0px; padding: 0px; }
span, div, td { font-family: tahoma, verdana; font-size: 8px; }
td { vertical-align: bottom; }
</style>
</head>

<body>
<script>window.print();</script>
<!-- ============================== ЭКЗЕМПЛЯР МО ============================== -->

<div style="position: relative; height: 730px; page-break-after: always; border: 1px solid #fff;">

<div style="position: absolute; top: 0; left: 0; width: 150;">Министерство здравоохранения Российской Федерации</div>

<div style="position: absolute; top: 0; left: 190;"><img width="170" src="/barcode.php?s={barcode_string}" /></div>

<div style="position: absolute; top: 0; left: 350; width: 150; text-align: right;">
  УТВЕРЖДЕНА<br />
  приказом Министерства здравоохранения Российской Федерации <br>от 20 декабря 2012 г. N 1175н
</div>

<div style="position: absolute; top: 55; left: 350; width: 150; text-align: right;">
  Код формы по ОКУД 3108805<br />
  Форма № 148-1/у-06 (л)
</div>

<div style="position: absolute; top: 40; left: 0; width: 180;">Медицинская организация</div>

<div style="position: absolute; top: 60; left: 0; width: 120;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 40%;">Штамп</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_1}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_2}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_3}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_4}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_5}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 75; left: 0; width: 160;">
  <div>Код ОГРН</div>
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr style="text-align: center;">
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_0}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_1}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_2}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_3}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_4}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_5}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_6}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_7}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_8}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_9}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_10}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_11}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_12}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_13}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_14}</td>
		</tr>
	</table>
</div>

<div style="position: absolute; top: 100; left: 0; width: 50; text-align: center;">
  Код категории граждан
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 33%; border: 1px solid #000; font-size: 10px;">{privilege_type_code_1}</td>
    <td style="width: 33%; border: 1px solid #000; font-size: 10px;">{privilege_type_code_2}</td>
    <td style="width: 34%; border: 1px solid #000; font-size: 10px;">{privilege_type_code_3}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 110; left: 75; width: 80; text-align: center;">
  Код нозологической формы (по МКБ-10)
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_1}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_2}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_3}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_4}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_5}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 90; left: 180; width: 320;">
  <!--div style="font-weight: bold; text-align: right;">Экземпляр МО</div-->
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td style="width: 42%; border: 1px solid #000; vertical-align: top; padding-left: 5px;">
      <div style="text-align: center;">Источник финансирования</div>
      <div><span style="border-bottom: {recept_finance_1};">1. федеральный бюджет</span></div>
      <div><span style="border-bottom: {recept_finance_2};">2. бюджет субъекта РФ</span></div>
      <div><span style="border-bottom: {recept_finance_3};">3. муниципальный бюджет</span></div>
      <div>(нужное подчеркнуть)</div>
    </td>
    <td style="width: 28%; border: 1px solid #000; vertical-align: top;">
      <div style="text-align: center;">% оплаты из источника финансирования</div>
      <div style="text-align: center;"><span style="border-bottom: {recept_discount_1};">&nbsp; 1. 100% &nbsp;</span></div>
      <div style="text-align: center;"><span style="border-bottom: {recept_discount_2};">&nbsp; 2. 50% &nbsp;</span></div>
	  <div>(нужное подчеркнуть)</div>
    </td>
    <td style="width: 30%; border: 1px solid #000; vertical-align: top;">
      <div style="text-align: center;">Рецепт действителен в течение</div>
	<div style="margin-left:20px">
        <div><span style="border-bottom: {recept_valid_4};">&nbsp; {r_v_0} &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_7};">&nbsp; {r_v_1}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_1};">&nbsp; {r_v_2}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_2};">&nbsp; {r_v_3} &nbsp;</span></div>
	</div>
     <div style="text-size:7px;text-align:center"> (нужное подчеркнуть)</div>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 190; left: 0; width: 500;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 12%; font-weight: bold;">РЕЦЕПТ</td>
    <td style="width: 9%;">Серия</td>
    <td style="width: 19%; font-weight: bold; font-size: 10px; text-decoration: underline;">{recept_ser}</td>
    <td style="width: 3%;">№</td>
    <td style="width: 19%; font-weight: bold; font-size: 10px; text-decoration: underline;">{recept_num}</td>
    <td style="width: 3%;">от</td>
    <td style="width: 35%;">
      <table width="150" cellspacing="0" cellpadding="0">
      <tr style="text-align: center; font-weight: bold;">
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_1}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_2}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_3}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_4}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_5}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_6}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_7}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_8}</td>
      </tr></table>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 205; left: 0; width: 500;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 17%; vertical-align: bottom;">Ф.И.О. пациента</td>
    <td style="width: 83%; font-weight: bold; font-size: 12px; border-bottom: 1px solid #000;">&nbsp; {person_fio}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 230; left: 0;">Дата рождения</div>

<div style="position: absolute; top: 228; left: 80; width: 150;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_1}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_2}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_3}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_4}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_5}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_6}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_7}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_8}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 230; left: 240;">СНИЛС</div>

<div style="position: absolute; top: 228; left: 270; width: 230;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_1}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_2}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_3}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_4}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_5}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_6}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_7}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_8}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_9}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_10}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_11}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_12}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_13}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_14}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 250; left: 0; width: 130;">№ полиса обязательного медицинского страхования</div>

<div style="position: absolute; top: 250; left: 140; width: 360;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center;">
    <td class="polis_ser_num">{polis_ser_num_1}</td>
    <td class="polis_ser_num">{polis_ser_num_2}</td>
    <td class="polis_ser_num">{polis_ser_num_3}</td>
    <td class="polis_ser_num">{polis_ser_num_4}</td>
    <td class="polis_ser_num">{polis_ser_num_5}</td>
    <td class="polis_ser_num">{polis_ser_num_6}</td>
    <td class="polis_ser_num">{polis_ser_num_7}</td>
    <td class="polis_ser_num">{polis_ser_num_8}</td>
    <td class="polis_ser_num">{polis_ser_num_9}</td>
    <td class="polis_ser_num">{polis_ser_num_10}</td>
    <td class="polis_ser_num">{polis_ser_num_11}</td>
    <td class="polis_ser_num">{polis_ser_num_12}</td>
    <td class="polis_ser_num">{polis_ser_num_13}</td>
    <td class="polis_ser_num">{polis_ser_num_14}</td>
    <td class="polis_ser_num">{polis_ser_num_15}</td>
    <td class="polis_ser_num">{polis_ser_num_16}</td>
    <td class="polis_ser_num">{polis_ser_num_17}</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
  </tr></table>
</div>

<div style="position: absolute; top: 275; left: 0; width: 300;">{str_amb_header}</div>

<div style="position: absolute; top: 275; left: 350; width: 150; border-bottom: 1px solid #000; text-align: center; font-size: 10px;">{ambul_card_num}</div>

<!--<div style="position: absolute; top: 275; left: 0;">Адрес:</div>

<div style="position: absolute; top: 273; left: 40; width: 460; border-bottom: 1px solid #000; font-size: 10px;">&nbsp; {address_string_1}</div>-->

<div style="position: absolute; top: 295; left: 0; width: 500; border-bottom: 1px solid #000; font-size: 10px;">&nbsp; {address_string_1} {address_string_2}</div>

<div style="position: absolute; top: 335; left: 0;">Ф.И.О. лечащего врача</div>

<div style="position: absolute; top: 330; left: 140; width: 360; border-bottom: 1px solid #000; font-size: 12px;">&nbsp; {medpersonal_fio}</div>

<div style="position: absolute; top: 355; left: 0; width: 250;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center;">
    <td style="width: 34%; text-align: left;">Код лечащего врача</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_1}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_2}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_3}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_4}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_5}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_6}</td>
	<td style="width: 30%; text-align: left;">&nbsp;</td>
  </tr></table>
</div>

<div style="position: absolute; top: 380; left: 0;">Выписано:</div>

<div style="position: absolute; top: 390; left: 0; width: 245; border-bottom: 1px solid #000;">Rp:</div>

<div style="position: absolute; top: 405; left: 0; width: 245; height: 50; border-bottom: 1px solid #000; font-weight: bold; font-family: courier new; text-align: center; font-size: 10pt;">{drug_name}</div>

<div style="position: absolute; top: 460; left: 0;">D.t.d.</div>

<div class="drugform" style="position: absolute; top: 458; left: 85; width: 170; font-size: {drug_form_length};  line-height: {drug_form_line_height};">{drug_form}</div>

<div style="position: absolute; top: 485; left: 0;">Дозировка</div>

<div style="position: absolute; top: 483; left: 85; width: 185; font-weight: bold; font-size: 7pt;">{drug_dose}</div>

<div style="position: absolute; top: 505; left: 0;">Количество единиц</div>

<div style="position: absolute; top: 503; left: 85; width: 165; font-weight: bold; font-size: 10px;">{drug_kolvo}</div>

<div style="position: absolute; top: 520; left: 85; width: 210; font-weight: bold; font-size: 10px;">{signa}</div>

<div style="position: absolute; top: 520; left: 0; width: 245; border-top: 1px solid #000;">Signa</div>

<div style="position: absolute; top: 550; left: 0; width: 245; border-top: 1px solid #000;">
  Подпись врача и личная<br />
  печать лечащего врача
</div>
<div style="position: absolute; top: 595; left: 0; width: 245; border-top: 1px solid #000; "></div>
<div style="position: absolute; top: 355; left: 255; width: 245; border: 1px solid #000;">
  <div style="padding-top: 2px;">(Заполняется специалистом аптечной организации)</div>
  <div style="padding-left: 5px; padding-right: 5px;">
    <div>Отпущено по рецепту:</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Дата отпуска</div>
    <div style="border-bottom: 1px solid #000; height: 30px;">Код лекарственного<br />препарата</div>
    <div style="border-bottom: 1px solid #000; height: 35px;">Торговое<br />наименование</div>
    <div style="border-bottom: 1px solid #000; height: 25px;">&nbsp;</div>
    <div style="border-bottom: 1px solid #000; padding-top: 2em; padding-bottom: 2em;">Количество</div>
    <div style="border-bottom: 1px solid #000; padding-top: 2em; padding-bottom: 1em;">На общую сумму</div>
    <div style="height: 25px;">&nbsp;</div>
  </div>
</div>
<div style="position: absolute; top: 605; left: 365;">М.П.</div>

<div style="position: absolute; top: 615; left: 0; width: 500; border-bottom: 1px dashed #000; text-align: center;">(линия отрыва)</div>

<div style="position: absolute; top: 635; left: 0; width: 500; text-align: center;">
  Корешок РЕЦЕПТА &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  Серия &nbsp;&nbsp; <span style="font-weight: bold; font-size: 10px;">{recept_ser}</span> &nbsp;&nbsp;&nbsp;
  № &nbsp;&nbsp; <span style="font-weight: bold; font-size: 10px;">{recept_num}</span> &nbsp;&nbsp;&nbsp;
  от &nbsp;&nbsp; <span style="font-weight: bold; font-size: 10px;">{recept_date}</span>
</div>{recept_date}

<div style="position: absolute; top: 655; left: 0;">Способ применения:</div>

<div style="position: absolute; top: 670; left: 0; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">Продолжительность</div>

<div style="position: absolute; top: 670; left: 220;">дней</div>

<div style="position: absolute; top: 700; left: 0; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">Количество приемов в день:</div>

<div style="position: absolute; top: 700; left: 220;">раз</div>

<div style="position: absolute; top: 730; left: 0; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">На 1 прием:</div>

<div style="position: absolute; top: 730; left: 220;">ед.</div>

<div style="position: absolute; top: 670; left: 255; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">Наименование лекарственного препарата:</div>

<div style="position: absolute; top: 700; left: 255; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">&nbsp;</div>

<div style="position: absolute; top: 730; left: 255; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">Дозировка:</div>

</div>

<!-- ============================== END OF ЭКЗЕМПЛЯР МО ============================== -->


<!-- ============================== ЭКЗЕМПЛЯР АПТЕЧНОЙ ОРГАНИЗАЦИИ ============================== -->

<div style="position: relative; height: 730px; page-break-after: always; border: 1px solid #fff; top:50;">

<div style="position: absolute; top: 0; left: 0; width: 150;">Министерство здравоохранения Российской Федерации</div>

<div style="position: absolute; top: 0; left: 190;"><img width="170" src="/barcode.php?s={barcode_string}" /></div>

<div style="position: absolute; top: 0; left: 350; width: 150; text-align: right;">
  УТВЕРЖДЕНА<br />
  приказом Министерства здравоохранения Российской Федерации <br>от 20 декабря 2012 г. N 1175н
</div>

<div style="position: absolute; top: 55; left: 350; width: 150; text-align: right;">
  Код формы по ОКУД 3108805<br />
  Форма № 148-1/у-06 (л)
</div>

<div style="position: absolute; top: 40; left: 0; width: 180;">Медицинская организация</div>

<div style="position: absolute; top: 60; left: 0; width: 120;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 40%;">Штамп</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_1}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_2}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_3}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_4}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_5}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 75; left: 0; width: 160;">
  <div>Код ОГРН</div>
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr style="text-align: center;">
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_0}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_1}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_2}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_3}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_4}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_5}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_6}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_7}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_8}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_9}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_10}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_11}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_12}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_13}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_14}</td>
		</tr>
	</table>
</div>

<div style="position: absolute; top: 100; left: 0; width: 50; text-align: center;">
  Код категории граждан
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 33%; border: 1px solid #000; font-size: 10px;">{privilege_type_code_1}</td>
    <td style="width: 33%; border: 1px solid #000; font-size: 10px;">{privilege_type_code_2}</td>
    <td style="width: 34%; border: 1px solid #000; font-size: 10px;">{privilege_type_code_3}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 110; left: 75; width: 80; text-align: center;">
  Код нозологической формы (по МКБ-10)
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_1}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_2}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_3}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_4}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_5}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 90; left: 180; width: 320;">
  <!--div style="font-weight: bold; text-align: right;">Экземпляр аптечной организации</div-->
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td style="width: 42%; border: 1px solid #000; vertical-align: top; padding-left: 5px;">
      <div style="text-align: center;">Источник финансирования</div>
      <div><span style="border-bottom: {recept_finance_1};">1. федеральный бюджет</span></div>
      <div><span style="border-bottom: {recept_finance_2};">2. бюджет субъекта РФ</span></div>
      <div><span style="border-bottom: {recept_finance_3};">3. муниципальный бюджет</span></div>
      <div>(нужное подчеркнуть)</div>
    </td>
    <td style="width: 28%; border: 1px solid #000; vertical-align: top;">
      <div style="text-align: center;">% оплаты из источника финансирования</div>
      <div style="text-align: center;"><span style="border-bottom: {recept_discount_1};">&nbsp; 1. 100% &nbsp;</span></div>
      <div style="text-align: center;"><span style="border-bottom: {recept_discount_2};">&nbsp; 2. 50% &nbsp;</span></div>
	  <div>(нужное подчеркнуть)</div>
    </td>
    <td style="width: 30%; border: 1px solid #000; vertical-align: top;">
     <div style="text-align: center;">Рецепт действителен в течение</div>
	<div style="margin-left:20px">
        <div><span style="border-bottom: {recept_valid_4};">&nbsp; {r_v_0} &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_7};">&nbsp; {r_v_1}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_1};">&nbsp; {r_v_2}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_2};">&nbsp; {r_v_3} &nbsp;</span></div>
	</div>
     <div style="text-size:7px;text-align:center"> (нужное подчеркнуть)</div>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 190; left: 0; width: 500;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 12%; font-weight: bold;">РЕЦЕПТ</td>
    <td style="width: 9%;">Серия</td>
    <td style="width: 19%; font-weight: bold; font-size: 10px; text-decoration: underline;">{recept_ser}</td>
    <td style="width: 3%;">№</td>
    <td style="width: 19%; font-weight: bold; font-size: 10px; text-decoration: underline;">{recept_num}</td>
    <td style="width: 3%;">от</td>
    <td style="width: 35%;">
      <table width="150" cellspacing="0" cellpadding="0">
      <tr style="text-align: center; font-weight: bold;">
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_1}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_2}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_3}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_4}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_5}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_6}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_7}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_8}</td>
      </tr></table>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 205; left: 0; width: 500;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 17%; vertical-align: bottom;">Ф.И.О. пациента</td>
    <td style="width: 83%; font-weight: bold; font-size: 12px; border-bottom: 1px solid #000;">&nbsp; {person_fio}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 230; left: 0;">Дата рождения</div>

<div style="position: absolute; top: 228; left: 80; width: 150;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_1}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_2}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_3}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_4}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_5}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_6}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_7}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_8}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 230; left: 240;">СНИЛС</div>

<div style="position: absolute; top: 228; left: 270; width: 230;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_1}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_2}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_3}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_4}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_5}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_6}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_7}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_8}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_9}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_10}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_11}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_12}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_13}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_14}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 250; left: 0; width: 130;">№ страхового медицинского полиса</div>

<div style="position: absolute; top: 250; left: 140; width: 360;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center;">
    <td class="polis_ser_num">{polis_ser_num_1}</td>
    <td class="polis_ser_num">{polis_ser_num_2}</td>
    <td class="polis_ser_num">{polis_ser_num_3}</td>
    <td class="polis_ser_num">{polis_ser_num_4}</td>
    <td class="polis_ser_num">{polis_ser_num_5}</td>
    <td class="polis_ser_num">{polis_ser_num_6}</td>
    <td class="polis_ser_num">{polis_ser_num_7}</td>
    <td class="polis_ser_num">{polis_ser_num_8}</td>
    <td class="polis_ser_num">{polis_ser_num_9}</td>
    <td class="polis_ser_num">{polis_ser_num_10}</td>
    <td class="polis_ser_num">{polis_ser_num_11}</td>
    <td class="polis_ser_num">{polis_ser_num_12}</td>
    <td class="polis_ser_num">{polis_ser_num_13}</td>
    <td class="polis_ser_num">{polis_ser_num_14}</td>
    <td class="polis_ser_num">{polis_ser_num_15}</td>
    <td class="polis_ser_num">{polis_ser_num_16}</td>
    <td class="polis_ser_num">{polis_ser_num_17}</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
  </tr></table>
</div>

<div style="position: absolute; top: 275; left: 0; width: 300;">{str_amb_header}</div>

<div style="position: absolute; top: 275; left: 350; width: 150; border-bottom: 1px solid #000; text-align: center; font-size: 10px;">{ambul_card_num}</div>

<!--<div style="position: absolute; top: 275; left: 0;">Адрес:</div>

<div style="position: absolute; top: 273; left: 40; width: 460; border-bottom: 1px solid #000; font-size: 10px;">&nbsp; {address_string_1}</div>-->

<div style="position: absolute; top: 295; left: 0; width: 500; border-bottom: 1px solid #000; font-size: 10px;">&nbsp; {address_string_1} {address_string_2}</div>

<div style="position: absolute; top: 335; left: 0;">Ф.И.О. лечащего врача</div>

<div style="position: absolute; top: 330; left: 140; width: 360; border-bottom: 1px solid #000; font-size: 12px;">&nbsp; {medpersonal_fio}</div>

<div style="position: absolute; top: 355; left: 0; width: 250;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center;">
    <td style="width: 34%; text-align: left;">Код лечащего врача</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_1}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_2}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_3}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_4}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_5}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_6}</td>
	<td style="width: 30%; text-align: left;">&nbsp;</td>
  </tr></table>
</div>

<div style="position: absolute; top: 380; left: 0;">Выписано:</div>

<div style="position: absolute; top: 390; left: 0; width: 245; border-bottom: 1px solid #000;">Rp:</div>

<div style="position: absolute; top: 405; left: 0; width: 245; height: 50; border-bottom: 1px solid #000; font-weight: bold; font-family: courier new; text-align: center; font-size: 10pt;">{drug_name}</div>

<div style="position: absolute; top: 460; left: 0;">D.t.d.</div>

<div class="drugform" style="position: absolute; top: 458; left: 85; width: 170; font-size: {drug_form_length};  line-height: {drug_form_line_height};">{drug_form}</div>

<div style="position: absolute; top: 485; left: 0;">Дозировка</div>

<div style="position: absolute; top: 483; left: 85; width: 185; font-weight: bold; font-size: 10px;">{drug_dose}</div>

<div style="position: absolute; top: 505; left: 0;">Количество единиц</div>

<div style="position: absolute; top: 503; left: 85; width: 165; font-weight: bold; font-size: 10px;">{drug_kolvo}</div>

<div style="position: absolute; top: 520; left: 85; width: 245; font-weight: bold; font-size: 10px;">{signa}</div>

<div style="position: absolute; top: 520; left: 0; width: 245; border-top: 1px solid #000;">Signa</div>

<div style="position: absolute; top: 550; left: 0; width: 245; border-top: 1px solid #000;">
  Подпись врача и личная<br />
  печать лечащего врача
</div>
<div style="position: absolute; top: 595; left: 0; width: 245; border-top: 1px solid #000; "></div>
<div style="position: absolute; top: 355; left: 255; width: 245; border: 1px solid #000;">
  <div style="padding-top: 2px;">(Заполняется специалистом аптечной организации)</div>
  <div style="padding-left: 5px; padding-right: 5px;">
    <div>Отпущено по рецепту:</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Дата отпуска</div>
    <div style="border-bottom: 1px solid #000; height: 30px;">Код лекарственного<br />препарата</div>
    <div style="border-bottom: 1px solid #000; height: 35px;">Торговое<br />наименование</div>
    <div style="border-bottom: 1px solid #000; height: 25px;">&nbsp;</div>
    <div style="border-bottom: 1px solid #000; padding-top: 2em; padding-bottom: 2em;">Количество</div>
    <div style="border-bottom: 1px solid #000; padding-top: 2em; padding-bottom: 1em;">На общую сумму</div>
    <div style="height: 25px;">&nbsp;</div>
  </div>
</div>
<div style="position: absolute; top: 605; left: 365;">М.П.</div>
<div style="position: absolute; top: 615; left: 0; width: 500; border-bottom: 1px dashed #000; text-align: center;">(линия отрыва)</div>

<div style="position: absolute; top: 635; left: 0; width: 500; text-align: center;">
  Корешок РЕЦЕПТА &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  Серия &nbsp;&nbsp; <span style="font-weight: bold; font-size: 10px;">{recept_ser}</span> &nbsp;&nbsp;&nbsp;
  № &nbsp;&nbsp; <span style="font-weight: bold; font-size: 10px;">{recept_num}</span> &nbsp;&nbsp;&nbsp;
  от &nbsp;&nbsp; <span style="font-weight: bold; font-size: 10px;">{recept_date}</span>
</div>

<div style="position: absolute; top: 655; left: 0;">Способ применения:</div>

<div style="position: absolute; top: 670; left: 0; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">Продолжительность</div>

<div style="position: absolute; top: 670; left: 220;">дней</div>

<div style="position: absolute; top: 700; left: 0; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">Количество приемов в день:</div>

<div style="position: absolute; top: 700; left: 220;">раз</div>

<div style="position: absolute; top: 730; left: 0; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">На 1 прием:</div>

<div style="position: absolute; top: 730; left: 220;">ед.</div>

<div style="position: absolute; top: 670; left: 255; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">Наименование лекарственного препарата:</div>

<div style="position: absolute; top: 700; left: 255; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">&nbsp;</div>

<div style="position: absolute; top: 730; left: 255; width: 245; border-bottom: 1px solid #000; padding-bottom: 1em;">Дозировка:</div>

</div>

<!-- ============================== END OF ЭКЗЕМПЛЯР АПТЕЧНОЙ ОРГАНИЗАЦИИ ============================== -->


<!-- ============================== ЭКЗЕМПЛЯР АПТЕЧНОЙ ОРГАНИЗАЦИИ ============================== -->

<div style="position: relative; height: 730px; border: 1px solid #fff; top:100;">

<div style="position: absolute; top: 0; left: 0; width: 150;">Министерство здравоохранения Российской Федерации</div>

<div style="position: absolute; top: 0; left: 190;"><img width="170" src="/barcode.php?s={barcode_string}" /></div>

<div style="position: absolute; top: 0; left: 350; width: 150; text-align: right;">
  УТВЕРЖДЕНА<br />
  приказом Министерства здравоохранения Российской Федерации <br>от 20 декабря 2012 г. N 1175н
</div>

<div style="position: absolute; top: 55; left: 350; width: 150; text-align: right;">
  Код формы по ОКУД 3108805<br />
  Форма № 148-1/у-06 (л)
</div>

<div style="position: absolute; top: 40; left: 0; width: 180;">Медицинская организация</div>

<div style="position: absolute; top: 60; left: 0; width: 120;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 40%;">Штамп</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_1}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_2}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_3}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_4}</td>
    <td style="width: 12%; border: 1px solid #000;">{lpu_stamp_5}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 75; left: 0; width: 160;">
  <div>Код ОГРН</div>
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr style="text-align: center;">
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_0}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_1}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_2}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_3}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_4}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_5}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_6}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_7}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_8}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_9}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_10}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_11}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_12}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_13}</td>
			<td style="width: 6.6%; border: 1px solid #000; font-size: 10px;">{lpu_ogrn_14}</td>
		</tr>
	</table>
</div>

<div style="position: absolute; top: 100; left: 0; width: 50; text-align: center;">
  Код категории граждан
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 33%; border: 1px solid #000; font-size: 10px;">{privilege_type_code_1}</td>
    <td style="width: 33%; border: 1px solid #000; font-size: 10px;">{privilege_type_code_2}</td>
    <td style="width: 34%; border: 1px solid #000; font-size: 10px;">{privilege_type_code_3}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 110; left: 75; width: 80; text-align: center;">
  Код нозологической формы (по МКБ-10)
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_1}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_2}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_3}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_4}</td>
    <td style="width: 20%; border: 1px solid #000; font-size: 10px;">{noz_form_code_5}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 90; left: 180; width: 320;">
  <!--div style="font-weight: bold; text-align: right;">Экземпляр аптечной организации</div-->
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td style="width: 42%; border: 1px solid #000; vertical-align: top; padding-left: 5px;">
      <div style="text-align: center;">Источник финансирования</div>
      <div><span style="border-bottom: {recept_finance_1};">1. федеральный бюджет</span></div>
      <div><span style="border-bottom: {recept_finance_2};">2. бюджет субъекта РФ</span></div>
      <div><span style="border-bottom: {recept_finance_3};">3. муниципальный бюджет</span></div>
      <div>(нужное подчеркнуть)</div>
    </td>
    <td style="width: 28%; border: 1px solid #000; vertical-align: top;">
      <div style="text-align: center;">% оплаты из источника финансирования</div>
      <div style="text-align: center;"><span style="border-bottom: {recept_discount_1};">&nbsp; 1. 100% &nbsp;</span></div>
      <div style="text-align: center;"><span style="border-bottom: {recept_discount_2};">&nbsp; 2. 50% &nbsp;</span></div>
	  <div>(нужное подчеркнуть)</div>
    </td>
    <td style="width: 30%; border: 1px solid #000; vertical-align: top;">
     <div style="text-align: center;">Рецепт действителен в течение:</div>
	<div style="margin-left:20px">
        <div><span style="border-bottom: {recept_valid_4};">&nbsp; {r_v_0} &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_7};">&nbsp; {r_v_1}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_1};">&nbsp; {r_v_2}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_2};">&nbsp; {r_v_3} &nbsp;</span></div>
	</div>
     <div style="text-size:7px;text-align:center"> (нужное подчеркнуть)</div>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 190; left: 0; width: 500;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 12%; font-weight: bold;">РЕЦЕПТ</td>
    <td style="width: 9%;">Серия</td>
    <td style="width: 19%; font-weight: bold; font-size: 10px; text-decoration: underline;">{recept_ser}</td>
    <td style="width: 3%;">№</td>
    <td style="width: 19%; font-weight: bold; font-size: 10px; text-decoration: underline;">{recept_num}</td>
    <td style="width: 3%;">от</td>
    <td style="width: 35%;">
      <table width="150" cellspacing="0" cellpadding="0">
      <tr style="text-align: center; font-weight: bold;">
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_1}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_2}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_3}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_4}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_5}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_6}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_7}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{recept_date_8}</td>
      </tr></table>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 205; left: 0; width: 500;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 17%; vertical-align: bottom;">Ф.И.О. пациента</td>
    <td style="width: 83%; font-weight: bold; font-size: 12px; border-bottom: 1px solid #000;">&nbsp; {person_fio}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 230; left: 0;">Дата рождения</div>

<div style="position: absolute; top: 228; left: 80; width: 150;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_1}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_2}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_3}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_4}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_5}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_6}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_7}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 10px;">{person_birthday_8}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 230; left: 240;">СНИЛС</div>

<div style="position: absolute; top: 228; left: 270; width: 230;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_1}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_2}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_3}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_4}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_5}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_6}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_7}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_8}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_9}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_10}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_11}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_12}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_13}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 10px;">{person_snils_14}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 250; left: 0; width: 130;">№ страхового медицинского полиса</div>

<div style="position: absolute; top: 250; left: 140; width: 360;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center;">
    <td class="polis_ser_num">{polis_ser_num_1}</td>
    <td class="polis_ser_num">{polis_ser_num_2}</td>
    <td class="polis_ser_num">{polis_ser_num_3}</td>
    <td class="polis_ser_num">{polis_ser_num_4}</td>
    <td class="polis_ser_num">{polis_ser_num_5}</td>
    <td class="polis_ser_num">{polis_ser_num_6}</td>
    <td class="polis_ser_num">{polis_ser_num_7}</td>
    <td class="polis_ser_num">{polis_ser_num_8}</td>
    <td class="polis_ser_num">{polis_ser_num_9}</td>
    <td class="polis_ser_num">{polis_ser_num_10}</td>
    <td class="polis_ser_num">{polis_ser_num_11}</td>
    <td class="polis_ser_num">{polis_ser_num_12}</td>
    <td class="polis_ser_num">{polis_ser_num_13}</td>
    <td class="polis_ser_num">{polis_ser_num_14}</td>
    <td class="polis_ser_num">{polis_ser_num_15}</td>
    <td class="polis_ser_num">{polis_ser_num_16}</td>
    <td class="polis_ser_num">{polis_ser_num_17}</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
    <td class="polis_ser_num">&nbsp;</td>
  </tr></table>
</div>

<div style="position: absolute; top: 275; left: 0; width: 300;">{str_amb_header}</div>

<div style="position: absolute; top: 275; left: 350; width: 150; border-bottom: 1px solid #000; text-align: center; font-size: 10px;">{ambul_card_num}</div>

<!--<div style="position: absolute; top: 275; left: 0;">Адрес:</div>

<div style="position: absolute; top: 273; left: 40; width: 460; border-bottom: 1px solid #000; font-size: 10px;">&nbsp; {address_string_1}</div>-->

<div style="position: absolute; top: 295; left: 0; width: 500; border-bottom: 1px solid #000; font-size: 10px;">&nbsp; {address_string_1} {address_string_2}</div>

<div style="position: absolute; top: 335; left: 0;">Ф.И.О. лечащего врача</div>

<div style="position: absolute; top: 330; left: 140; width: 360; border-bottom: 1px solid #000; font-size: 12px;">&nbsp; {medpersonal_fio}</div>

<div style="position: absolute; top: 355; left: 0; width: 250;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center;">
    <td style="width: 34%; text-align: left;">Код лечащего врача</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_1}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_2}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_3}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_4}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_5}</td>
    <td style="width: 6%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_6}</td>
	<td style="width: 30%; text-align: left;">&nbsp;</td>
  </tr></table>
</div>

<div style="position: absolute; top: 380; left: 0;">Выписано:</div>

<div style="position: absolute; top: 390; left: 0; width: 245; border-bottom: 1px solid #000;">Rp:</div>

<div style="position: absolute; top: 405; left: 0; width: 245; height: 50; border-bottom: 1px solid #000; font-weight: bold; font-family: courier new; text-align: center; font-size: 10pt;">{drug_name}</div>

<div style="position: absolute; top: 460; left: 0;">D.t.d.</div>

<div class="drugform" style="position: absolute; top: 458; left: 85; width: 170; font-size: {drug_form_length};  line-height: {drug_form_line_height};">{drug_form}</div>

<div style="position: absolute; top: 485; left: 0;">Дозировка</div>

<div style="position: absolute; top: 483; left: 85; width: 185; font-weight: bold; font-size: 10px;">{drug_dose}</div>

<div style="position: absolute; top: 505; left: 0;">Количество единиц</div>

<div style="position: absolute; top: 503; left: 85; width: 165; font-weight: bold; font-size: 10px;">{drug_kolvo}</div>

<div style="position: absolute; top: 520; left: 85; width: 240; font-weight: bold; font-size: 10px;">{signa}</div>

<div style="position: absolute; top: 520; left: 0; width: 245; border-top: 1px solid #000;">Signa</div>

<div style="position: absolute; top: 550; left: 0; width: 245; border-top: 1px solid #000;">
  Подпись врача и личная<br />
  печать лечащего врача
</div>
<div style="position: absolute; top: 585; left: 0; width: 245; border-top: 1px solid #000; "></div>

<div style="position: absolute; top: 345; left: 255; width: 245; border: 1px solid #000;">
  <div style="padding-top: 2px;">(Заполняется специалистом аптечной организации)</div>
  <div style="padding-left: 5px; padding-right: 5px;">
    <div>Отпущено по рецепту:</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Дата отпуска</div>
    <div style="border-bottom: 1px solid #000; height: 30px;">Код лекарственного<br />препарата</div>
    <div style="border-bottom: 1px solid #000; height: 35px;">Торговое<br />наименование</div>
    <div style="border-bottom: 1px solid #000; height: 25px;">&nbsp;</div>
    <div style="border-bottom: 1px solid #000; padding-top: 2em; padding-bottom: 2em;">Количество</div>
    <div style="border-bottom: 1px solid #000; padding-top: 2em; padding-bottom: 1em;">На общую сумму</div>
    <div style="height: 25px;">&nbsp;</div>
  </div>
</div>
<div style="position: absolute; top: 605; left: 365;">М.П.</div>

<div style="position: absolute; top: 615; left: 0; width: 500; border-bottom: 1px dashed #000; text-align: center;">(линия отрыва)</div>
<?php
if (!empty($OrgFarmacy_id) && $OrgFarmacy_id != 1) {
?>
<div style="position: absolute; top: 635; left: 0; width: 500; text-align: center; font-weight: bold; font-size: 12px;">Наличие лекарственных препаратов:</div>

<div style="position: absolute; top: 650; left: 0; width: 500;">
  <div style="font-size: 12px; font-weight: bold;">Аптека: {orgfarmacy_name}</div>
  <div style="font-size: 12px; font-weight: bold;">Адрес: {orgfarmacy_howgo}</div>
  <div style="font-size: 12px; font-weight: bold;">Телефон: {orgfarmacy_phone}</div>
</div>
<?php
}
?>
</div>

<!-- ============================== END OF ЭКЗЕМПЛЯР АПТЕЧНОЙ ОРГАНИЗАЦИИ ============================== -->

</body>

</html>