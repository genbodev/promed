<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>{recept_template_title}</title>
<style type="text/css">
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: tahoma, verdana; font-size: 6px; }

.polis_ser_num { width: 3%; border: 1px solid #000; font-size: 10px; }
.drugform { font-weight: bold; font-size: 6px; }
</style>

<style type="text/css" media="print">
body { margin: 0px; padding: 0px; }
span, div, td { font-family: tahoma, verdana; font-size: 6px; }
td { vertical-align: bottom; }
</style>
</head>

<body>
<script>//window.print();
</script>
<div style="border-right: 1px dashed #000; height: 725px; position: absolute; top: 0; left: 0; width: 352;">&nbsp;</div>
<div style="border-right: 1px dashed #000; height: 725px; position: absolute; top: 0; left: 0; width: 707;">&nbsp;</div>

<!-- ============================== ЭКЗЕМПЛЯР МО ============================== -->

<div style="position: absolute; top: 0; left: 0; width: 120;">Министерство здравоохранения Российской Федерации</div>

<div style="position: absolute; top: 18; left: 70;"><img width="210" src="/barcode.php?s={barcode_string}" /></div>

<div style="position: absolute; top: 85; left: 0; width: 80; text-align: center;">Медицинская организация</div>

<div style="position: absolute; top: 70; left: 210; width: 140; text-align: center;">
  УТВЕРЖДЕНА<br />
  приказом Министерства здравоохранения Российской Федерации <br>от 20 декабря 2012 г. N 1175н
</div>

<div style="position: absolute; top: 105; left: 0; width: 70;">
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

<div style="position: absolute; top: 118; left: 0; width: 110;">
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

<div style="position: absolute; top: 108; left: 245; width: 100;">
  Код формы по ОКУД 3108805<br />
  Форма № 148-1/у-06 (л)
</div>

<div style="position: absolute; top: 138; left: 0; width: 35; text-align: center;">
  Код категории граждан
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 33%; border: 1px solid #000; font-size: 9px;">{privilege_type_code_1}</td>
    <td style="width: 33%; border: 1px solid #000; font-size: 9px;">{privilege_type_code_2}</td>
    <td style="width: 34%; border: 1px solid #000; font-size: 9px;">{privilege_type_code_3}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 145; left: 40; width: 58; text-align: center;">
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

<div style="position: absolute; top: 128; left: 120; width: 225;">
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
      <div style="text-align: center;">Рецепт действителен в течение:</div>
	<div style="margin-left:10px">
        <div><span style="border-bottom: {recept_valid_4};">&nbsp; {r_v_0} &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_7};">&nbsp; {r_v_1}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_1};">&nbsp; {r_v_2}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_2};">&nbsp; {r_v_3} &nbsp;</span></div>
	</div>
     <div style="text-size:7px;text-align:center"> (нужное подчеркнуть)</div>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 198; left: 0; width: 345;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 10%; font-weight: bold;">РЕЦЕПТ</td>
    <td style="width: 7%;">Серия</td>
    <td style="width: 18%; font-weight: bold; font-size: 8px;">{recept_ser}</td>
    <td style="width: 3%;">№</td>
    <td style="width: 19%; font-weight: bold; font-size: 8px;">{recept_num}</td>
    <td style="width: 3%;">от</td>
    <td style="width: 40%;">
      <table width="110" cellspacing="0" cellpadding="0">
      <tr style="text-align: center; font-weight: bold;">
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_1}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_2}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_3}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_4}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_5}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_6}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_7}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_8}</td>
      </tr></table>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 215; left: 0; width: 345;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 17%; vertical-align: bottom;">Ф.И.О. пациента</td>
    <td style="width: 83%; font-weight: bold; font-size: 10px; border-bottom: 1px solid #000;">&nbsp; {person_fio}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 245; left: 0;">Дата рождения</div>

<div style="position: absolute; top: 240; left: 50; width: 110;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_1}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_2}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_3}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_4}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_5}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_6}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_7}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_8}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 245; left: 170;">СНИЛС</div>

<div style="position: absolute; top: 240; left: 195; width: 150;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_1}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_2}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_3}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_4}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_5}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_6}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_7}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_8}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_9}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_10}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_11}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_12}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_13}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_14}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 260; left: 0; width: 95;">№ полиса обязательного медицинского страхования</div>

<div style="position: absolute; top: 258; left: 95; width: 250;">
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

<div style="position: absolute; top: 280; left: 0; width: 200;">{str_amb_header}</div>

<div style="position: absolute; top: 278; left: 205; width: 140; border-bottom: 1px solid #000; text-align: center; font-size: 8px;">{ambul_card_num}</div>

<!--<div style="position: absolute; top: 300; left: 0;">Адрес:</div>

<div style="position: absolute; top: 297; left: 25; width: 320; border-bottom: 1px solid #000; font-size: 8px;">&nbsp; {address_string_1}</div>-->

<div style="position: absolute; top: 313; left: 0; width: 345; border-bottom: 1px solid #000; font-size: 8px;">&nbsp; {address_string_1} {address_string_2}</div>

<div style="position: absolute; top: 335; left: 0;">Ф.И.О. лечащего врача</div>

<div style="position: absolute; top: 330; left: 85; width: 260; border-bottom: 1px solid #000; font-size: 10px;">&nbsp; {medpersonal_fio}</div>

<div style="position: absolute; top: 350; left: 0; width: 120;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center;">
    <td style="width: 34%; text-align: left;">Код лечащего врача</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_1}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_2}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_3}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_4}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_5}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_6}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 370; left: 0;">Выписано:</div>

<div style="position: absolute; top: 379; left: 0; width: 170; border-bottom: 1px solid #000;">Rp:</div>

<div style="position: absolute; top: 390; left: 0; width: 170; height: 35; border-bottom: 1px solid #000; font-weight: bold; font-family: courier new; text-align: center; font-size: 7pt;">{drug_name}</div>

<div style="position: absolute; top: 430; left: 0;">D.t.d.</div>

<div class="drugform" style="position: absolute; top: 428; left: 65; width: 100; font-size: {drug_form_length}; line-height: {drug_form_line_height};">{drug_form}</div>

<div style="position: absolute; top: 450; left: 0;">Дозировка</div>

<div style="position: absolute; top: 448; left: 65; width: 110; font-weight: bold; font-size: 7px;">{drug_dose}</div>

<div style="position: absolute; top: 470; left: 0;">Количество единиц</div>

<div style="position: absolute; top: 468; left: 65; width: 105; font-weight: bold; font-size: 7px;">{drug_kolvo}</div>

<div style="position: absolute; top: 485; left: 65; width: 135; font-weight: bold; font-size: 7px;">{signa}</div>

<div style="position: absolute; top: 485; left: 0; width: 170; border-top: 1px solid #000;">Signa</div>

<div style="position: absolute; top: 510; left: 0; width: 170; border-top: 1px solid #000;">&nbsp;</div>

<div style="position: absolute; top: 515; left: 0; width: 168; border-bottom: 1px solid #000; padding-bottom: 1em;">
  Подпись лечащего врача и личная<br />
  печать лечащего врача
</div>


<div style="position: absolute; top: 350; left: 180; width: 163; border: 1px solid #000;">
  <div style="padding: 2px;">(Заполняется специалистом аптечной организации)</div>
  <div style="padding-left: 5px; padding-right: 5px;">
    <div>Отпущено по рецепту:</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Дата отпуска</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Код лекарственного<br />препарата</div>
    <div style="border-bottom: 1px solid #000; padding-top: 1em; height: 30px;">Торговое<br />наименование</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">&nbsp;</div>
    <div style="border-bottom: 1px solid #000; padding-top: 1em; padding-bottom: 1em;">Количество</div>
    <div style="border-bottom: 1px solid #000; padding-top: 2em; padding-bottom: 2em;">На общую сумму</div>
    <div style="height: 15px;">&nbsp;</div>
  </div>
</div>
<div style="position: absolute; top: 550; left: 250;">М.П.</div>
<div style="position: absolute; top: 610; left: 0; width: 345; border-bottom: 1px dashed #000; text-align: center;">(линия отрыва)</div>

<div style="position: absolute; top: 625; left: 0; width: 345; text-align: center;">
  Корешок РЕЦЕПТА &nbsp;&nbsp;&nbsp;
  Серия &nbsp;&nbsp; <span style="font-weight: bold; font-size: 8px;">{recept_ser}</span> &nbsp;&nbsp;&nbsp;
  № &nbsp;&nbsp; <span style="font-weight: bold; font-size: 8px;">{recept_num}</span> &nbsp;&nbsp;&nbsp;
  от &nbsp;&nbsp; <span style="font-weight: bold; font-size: 8px;">{recept_date}</span>
</div>

<div style="position: absolute; top: 640; left: 0;">Способ применения:</div>

<div style="position: absolute; top: 655; left: 0; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">Продолжительность</div>

<div style="position: absolute; top: 655; left: 150;">дней</div>

<div style="position: absolute; top: 680; left: 0; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">Количество приемов в день:</div>

<div style="position: absolute; top: 680; left: 155;">раз</div>

<div style="position: absolute; top: 705; left: 0; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">На 1 прием:</div>

<div style="position: absolute; top: 705; left: 155;">ед.</div>

<div style="position: absolute; top: 655; left: 175; width: 170; border-bottom: 1px solid #000; padding-bottom: 1em;">Наименование лекарственного препарата:</div>

<div style="position: absolute; top: 680; left: 175; width: 170; border-bottom: 1px solid #000; padding-bottom: 1em;">&nbsp;</div>

<div style="position: absolute; top: 705; left: 175; width: 170; border-bottom: 1px solid #000; padding-bottom: 1em;">Дозировка:</div>

<!-- ============================== END OF ЭКЗЕМПЛЯР МО ============================== -->


<!-- ============================== ЭКЗЕМПЛЯР АПТЕЧНОЙ ОРГАНИЗАЦИИ ============================== -->

<div style="position: absolute; top: 0; left: 360; width: 120;">Министерство здравоохранения Российской Федерации</div>

<div style="position: absolute; top: 18; left: 430;"><img width="210" src="/barcode.php?s={barcode_string}" /></div>

<div style="position: absolute; top: 85; left: 360; width: 80; text-align: center;">Медицинская организация</div>

<div style="position: absolute; top: 70; left: 563; width: 140; text-align: center;">
  УТВЕРЖДЕНА<br />
  приказом Министерства здравоохранения Российской Федерации <br>от 20 декабря 2012 г. N 1175н
</div>

<div style="position: absolute; top: 105; left: 360; width: 70;">
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

<div style="position: absolute; top: 118; left: 360; width: 110;">
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

<div style="position: absolute; top: 108; left: 600; width: 100;">
  Код формы по ОКУД 3108805<br />
  Форма № 148-1/у-06 (л)
</div>

<div style="position: absolute; top: 138; left: 360; width: 35; text-align: center;">
  Код категории граждан
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 33%; border: 1px solid #000; font-size: 9px;">{privilege_type_code_1}</td>
    <td style="width: 33%; border: 1px solid #000; font-size: 9px;">{privilege_type_code_2}</td>
    <td style="width: 34%; border: 1px solid #000; font-size: 9px;">{privilege_type_code_3}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 145; left: 400; width: 58; text-align: center;">
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

<div style="position: absolute; top: 128; left: 475; width: 225;">
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
	<div style="margin-left:10px">
        <div><span style="border-bottom: {recept_valid_4};">&nbsp; {r_v_0} &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_7};">&nbsp; {r_v_1}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_1};">&nbsp; {r_v_2}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_2};">&nbsp; {r_v_3}, &nbsp;</span></div>
	</div>
     <div style="text-size:7px;text-align:center"> (нужное подчеркнуть)</div>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 198; left: 360; width: 345;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 10%; font-weight: bold;">РЕЦЕПТ</td>
    <td style="width: 7%;">Серия</td>
    <td style="width: 18%; font-weight: bold; font-size: 8px;">{recept_ser}</td>
    <td style="width: 3%;">№</td>
    <td style="width: 19%; font-weight: bold; font-size: 8px;">{recept_num}</td>
    <td style="width: 3%;">от</td>
    <td style="width: 40%;">
      <table width="110" cellspacing="0" cellpadding="0">
      <tr style="text-align: center; font-weight: bold;">
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_1}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_2}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_3}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_4}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_5}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_6}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_7}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_8}</td>
      </tr></table>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 215; left: 360; width: 340;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 17%; vertical-align: bottom;">Ф.И.О. пациента</td>
    <td style="width: 83%; font-weight: bold; font-size: 10px; border-bottom: 1px solid #000;">&nbsp; {person_fio}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 245; left: 360;">Дата рождения</div>

<div style="position: absolute; top: 240; left: 410; width: 110;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_1}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_2}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_3}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_4}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_5}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_6}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_7}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_8}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 245; left: 525;">СНИЛС</div>

<div style="position: absolute; top: 240; left: 550; width: 150;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_1}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_2}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_3}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_4}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_5}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_6}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_7}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_8}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_9}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_10}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_11}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_12}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_13}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_14}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 260; left: 360; width: 95;">№ полиса обязательного медицинского страхования</div>

<div style="position: absolute; top: 258; left: 450; width: 250;">
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

<div style="position: absolute; top: 280; left: 360; width: 200;">{str_amb_header}</div>

<div style="position: absolute; top: 278; left: 560; width: 140; border-bottom: 1px solid #000; text-align: center; font-size: 8px;">{ambul_card_num}</div>

<!--<div style="position: absolute; top: 300; left: 360;">Адрес:</div>

<div style="position: absolute; top: 297; left: 385; width: 315; border-bottom: 1px solid #000; font-size: 8px;">&nbsp; {address_string_1}</div>-->

<div style="position: absolute; top: 313; left: 360; width: 340; border-bottom: 1px solid #000; font-size: 8px;">&nbsp; {address_string_1} {address_string_2}</div>

<div style="position: absolute; top: 335; left: 360;">Ф.И.О. лечащего врача</div>

<div style="position: absolute; top: 330; left: 445; width: 255; border-bottom: 1px solid #000; font-size: 10px;">&nbsp; {medpersonal_fio}</div>

<div style="position: absolute; top: 350; left: 360; width: 120;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center;">
    <td style="width: 34%; text-align: left;">Код лечащего врача</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_1}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_2}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_3}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_4}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_5}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_6}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 370; left: 360;">Выписано:</div>

<div style="position: absolute; top: 379; left: 360; width: 170; border-bottom: 1px solid #000;">Rp:</div>

<div style="position: absolute; top: 390; left: 360; width: 170; height: 35; border-bottom: 1px solid #000; font-weight: bold; font-family: courier new; text-align: center; font-size: 7pt;">{drug_name}</div>

<div style="position: absolute; top: 430; left: 360;">D.t.d.</div>

<div class="drugform" style="position: absolute; top: 428; left: 425; width: 100; font-size: {drug_form_length}; line-height: {drug_form_line_height};">{drug_form}</div>

<div style="position: absolute; top: 450; left: 360;">Дозировка</div>

<div style="position: absolute; top: 448; left: 425; width: 110; font-weight: bold; font-size: 7px;">{drug_dose}</div>

<div style="position: absolute; top: 470; left: 360;">Количество единиц</div>

<div style="position: absolute; top: 468; left: 425; width: 105; font-weight: bold; font-size: 7px;">{drug_kolvo}</div>

<div style="position: absolute; top: 485; left: 360; width: 170; border-top: 1px solid #000;">Signa</div>

<div style="position: absolute; top: 485; left: 425; width: 135; font-weight: bold; font-size: 7px;">{signa}</div>

<div style="position: absolute; top: 510; left: 360; width: 170; border-top: 1px solid #000;">&nbsp;</div>

<div style="position: absolute; top: 515; left: 360; width: 168; border-bottom: 1px solid #000; padding-bottom: 1em;">
  Подпись лечащего врача и личная<br />
  печать лечащего врача
</div>

<div style="position: absolute; top: 350; left: 540; width: 158; border: 1px solid #000;">
  <div style="padding: 2px;">(Заполняется специалистом аптечной организации)</div>
  <div style="padding-left: 5px; padding-right: 5px;">
    <div>Отпущено по рецепту:</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Дата отпуска</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Код лекарственного<br />препарата</div>
    <div style="border-bottom: 1px solid #000; height: 30px; padding-top: 1em;">Торговое<br />наименование</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">&nbsp;</div>
    <div style="border-bottom: 1px solid #000; padding-top: 1em; padding-bottom: 1em;">Количество</div>
    <div style="border-bottom: 1px solid #000; padding-top: 2em; padding-bottom: 2em;">На общую сумму</div>
    <div style="height: 15px;">&nbsp;</div>
  </div>
</div>
<div style="position: absolute; top: 550; left: 610;">М.П.</div>
<div style="position: absolute; top: 610; left: 360; width: 340; border-bottom: 1px dashed #000; text-align: center;">(линия отрыва)</div>

<div style="position: absolute; top: 625; left: 360; width: 340; text-align: center;">
  Корешок РЕЦЕПТА &nbsp;&nbsp;&nbsp;
  Серия &nbsp;&nbsp; <span style="font-weight: bold; font-size: 8px;">{recept_ser}</span> &nbsp;&nbsp;&nbsp;
  № &nbsp;&nbsp; <span style="font-weight: bold; font-size: 8px;">{recept_num}</span> &nbsp;&nbsp;&nbsp;
  от &nbsp;&nbsp; <span style="font-weight: bold; font-size: 8px;">{recept_date}</span>
</div>

<div style="position: absolute; top: 640; left: 360;">Способ применения:</div>

<div style="position: absolute; top: 655; left: 360; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">Продолжительность</div>

<div style="position: absolute; top: 655; left: 510;">дней</div>

<div style="position: absolute; top: 680; left: 360; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">Количество приемов в день:</div>

<div style="position: absolute; top: 680; left: 515;">раз</div>

<div style="position: absolute; top: 705; left: 360; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">На 1 прием:</div>

<div style="position: absolute; top: 705; left: 515;">ед.</div>

<div style="position: absolute; top: 655; left: 535; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">Наименование лекарственного препарата:</div>

<div style="position: absolute; top: 680; left: 535; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">&nbsp;</div>

<div style="position: absolute; top: 705; left: 535; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">Дозировка:</div>

<!-- ============================== END OF ЭКЗЕМПЛЯР АПТЕЧНОЙ ОРГАНИЗАЦИИ ============================== -->


<!-- ============================== ЭКЗЕМПЛЯР АПТЕЧНОЙ ОРГАНИЗАЦИИ ============================== -->

<div style="position: absolute; top: 0; left: 715; width: 120;">Министерство здравоохранения Российской Федерации</div>

<div style="position: absolute; top: 18; left: 785;"><img width="210" src="/barcode.php?s={barcode_string}" /></div>

<div style="position: absolute; top: 85; left: 715; width: 80; text-align: center;">Медицинская организация</div>

<div style="position: absolute; top: 70; left: 915; width: 140; text-align: center;">
  УТВЕРЖДЕНА<br />
  приказом Министерства здравоохранения Российской Федерации <br>от 20 декабря 2012 г. N 1175н
</div>

<div style="position: absolute; top: 105; left: 715; width: 70;">
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

<div style="position: absolute; top: 118; left: 715; width: 110;">
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

<div style="position: absolute; top: 108; left: 955; width: 100;">
  Код формы по ОКУД 3108805<br />
  Форма № 148-1/у-06 (л)
</div>

<div style="position: absolute; top: 138; left: 715; width: 35; text-align: center;">
  Код категории граждан
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 33%; border: 1px solid #000; font-size: 9px;">{privilege_type_code_1}</td>
    <td style="width: 33%; border: 1px solid #000; font-size: 9px;">{privilege_type_code_2}</td>
    <td style="width: 34%; border: 1px solid #000; font-size: 9px;">{privilege_type_code_3}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 145; left: 755; width: 58; text-align: center;">
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

<div style="position: absolute; top: 128; left: 830; width: 225;">
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
	<div style="margin-left:10px">
        <div><span style="border-bottom: {recept_valid_4};">&nbsp; {r_v_0} &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_7};">&nbsp; {r_v_1}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_1};">&nbsp; {r_v_2}, &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_2};">&nbsp; {r_v_3} &nbsp;</span></div>
	</div>
     <div style="text-size:7px;text-align:center"> (нужное подчеркнуть)</div>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 198; left: 715; width: 345;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 10%; font-weight: bold;">РЕЦЕПТ</td>
    <td style="width: 7%;">Серия</td>
    <td style="width: 18%; font-weight: bold; font-size: 8px;">{recept_ser}</td>
    <td style="width: 3%;">№</td>
    <td style="width: 19%; font-weight: bold; font-size: 8px;">{recept_num}</td>
    <td style="width: 3%;">от</td>
    <td style="width: 40%;">
      <table width="110" cellspacing="0" cellpadding="0">
      <tr style="text-align: center; font-weight: bold;">
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_1}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_2}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_3}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_4}</td>
        <td style="width: 10%;">&nbsp;</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_5}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_6}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_7}</td>
        <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{recept_date_8}</td>
      </tr></table>
    </td>
  </tr></table>
</div>

<div style="position: absolute; top: 215; left: 715; width: 340;">
  <table width="100%" cellspacing="0" cellpadding="0"><tr>
    <td style="width: 17%; vertical-align: bottom;">Ф.И.О. пациента</td>
    <td style="width: 83%; font-weight: bold; font-size: 10px; border-bottom: 1px solid #000;">&nbsp; {person_fio}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 245; left: 715; width: 100;">Дата рождения</div>

<div style="position: absolute; top: 240; left: 765; width: 110;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_1}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_2}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_3}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_4}</td>
    <td style="width: 10%;">&nbsp;</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_5}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_6}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_7}</td>
    <td style="width: 10%; border: 1px solid #000; font-size: 8px;">{person_birthday_8}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 245; left: 880;">СНИЛС</div>

<div style="position: absolute; top: 240; left: 905; width: 150;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center; font-weight: bold;">
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_1}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_2}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_3}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_4}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_5}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_6}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_7}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_8}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_9}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_10}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_11}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_12}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_13}</td>
    <td style="width: 7%; border: 1px solid #000; font-size: 8px;">{person_snils_14}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 260; left: 715; width: 95;">№ полиса обязательного медицинского страхования</div>

<div style="position: absolute; top: 258; left: 805; width: 250;">
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

<div style="position: absolute; top: 280; left: 716; width: 200;">{str_amb_header}</div>

<div style="position: absolute; top: 278; left: 915; width: 140; border-bottom: 1px solid #000; text-align: center; font-size: 8px;">{ambul_card_num}</div>

<!--<div style="position: absolute; top: 300; left: 715;">Адрес:</div>

<div style="position: absolute; top: 297; left: 740; width: 315; border-bottom: 1px solid #000; font-size: 8px;">&nbsp; {address_string_1}</div>-->

<div style="position: absolute; top: 313; left: 715; width: 340; border-bottom: 1px solid #000; font-size: 8px;">&nbsp; {address_string_1} {address_string_2}</div>

<div style="position: absolute; top: 335; left: 715; width: 100;">Ф.И.О. лечащего врача</div>

<div style="position: absolute; top: 330; left: 800; width: 255; border-bottom: 1px solid #000; font-size: 10px;">&nbsp; {medpersonal_fio}</div>

<div style="position: absolute; top: 350; left: 715; width: 120;">
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr style="text-align: center;">
    <td style="width: 34%; text-align: left;">Код лечащего врача</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_1}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_2}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_3}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_4}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_5}</td>
    <td style="width: 11%; border: 1px solid #000; font-size: 10px;">{medpersonal_code_6}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 370; left: 715;">Выписано:</div>

<div style="position: absolute; top: 379; left: 715; width: 170; border-bottom: 1px solid #000;">Rp:</div>

<div style="position: absolute; top: 390; left: 715; width: 170; height: 35; border-bottom: 1px solid #000; font-weight: bold; font-family: courier new; text-align: center; font-size: 7pt;">{drug_name}</div>

<div style="position: absolute; top: 430; left: 715;">D.t.d.</div>

<div class="drugform" style="position: absolute; top: 428; left: 780; width: 100; font-size: {drug_form_length}; line-height: {drug_form_line_height};">{drug_form}</div>

<div style="position: absolute; top: 450; left: 715;">Дозировка</div>

<div style="position: absolute; top: 448; left: 780; width: 110; font-weight: bold; font-size: 7px;">{drug_dose}</div>

<div style="position: absolute; top: 470; left: 715; width: 155;">Количество единиц</div>

<div style="position: absolute; top: 468; left: 780; width: 105; font-weight: bold; font-size: 7px;">{drug_kolvo}</div>

<div style="position: absolute; top: 485; left: 715; width: 170; border-top: 1px solid #000;">Signa</div>

<div style="position: absolute; top: 485; left: 780; width: 135; font-weight: bold; font-size: 7px;">{signa}</div>

<div style="position: absolute; top: 510; left: 715; width: 170; border-top: 1px solid #000;">&nbsp;</div>

<div style="position: absolute; top: 515; left: 715; width: 168; border-bottom: 1px solid #000; padding-bottom: 1em;">
  Подпись лечащего врача и личная<br />
  печать лечащего врача
</div>

<div style="position: absolute; top: 350; left: 895; width: 158; border: 1px solid #000;">
  <div style="padding: 2px;">(Заполняется специалистом аптечной организации)</div>
  <div style="padding-left: 5px; padding-right: 5px;">
    <div>Отпущено по рецепту:</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Дата отпуска</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Код лекарственного<br />препарата</div>
    <div style="border-bottom: 1px solid #000; height: 30px; padding-top: 1em;">Торговое<br />наименование</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">&nbsp;</div>
    <div style="border-bottom: 1px solid #000; padding-top: 1em; padding-bottom: 1em;">Количество</div>
    <div style="border-bottom: 1px solid #000; padding-top: 2em; padding-bottom: 2em;">На общую сумму</div>
    <div style="height: 15px;">&nbsp;</div>
  </div>
</div>
<div style="position: absolute; top: 550; left: 965;">М.П.</div>
<div style="position: absolute; top: 610; left: 715; width: 340; border-bottom: 1px dashed #000; text-align: center;">(линия отрыва)</div>
<?php
if (!empty($OrgFarmacy_id) && $OrgFarmacy_id != 1) {
?>
<div style="position: absolute; top: 625; left: 715; width: 340; text-align: center; font-weight: bold; font-size: 12px;">Наличие лекарственных препаратов:</div>

<div style="position: absolute; top: 645; left: 715; width: 340;">
  <div style="font-size: 12px; font-weight: bold;">Аптека: {orgfarmacy_name}</div>
  <div style="font-size: 12px; font-weight: bold;">Адрес: {orgfarmacy_howgo}</div>
  <div style="font-size: 12px; font-weight: bold;">Телефон: {orgfarmacy_phone}</div>
</div>
<?php
}
?>
<!-- ============================== END OF ЭКЗЕМПЛЯР АПТЕЧНОЙ ОРГАНИЗАЦИИ ============================== -->

</body>

</html>