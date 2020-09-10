
<body>
<!-- ============================== ЭКЗЕМПЛЯР МО ============================== -->

<div style="position: absolute; top: 0; left: 0; width: 120;">Министерство здравоохранения Российской Федерации</div>

<div style="position: absolute; top: 0; left: 130;"><img width="210" src="/barcode.php?s={barcode_string}" /></div>

<div style="position: absolute; top: 30; left: 0; width: 80; text-align: center;">Медицинская организация:</div>

<div style="position: absolute; top: 110; left: 98; width: 140; text-align: center;">
  УТВЕРЖДЕН<br />
  приказом Министерства здравоохранения Российской Федерации от 20 декабря 2012 г.<br />№1175н
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

<div style="position: absolute; top: 118; left: 0; width: 80;">
  <div>Код ОГРН</div>
  <div style="border: 1px solid #000; text-align: center;">{lpu_ogrn}</div>
</div>

<div style="position: absolute; top: 118; left: 260; width: 100;">
  Код формы по ОКУД 3108805<br />
  Форма №148-1/у-06(л)
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
    <td style="width: 100%; border: 1px solid #000; font-size: 9px;">{noz_form_code}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 138; left: 140; width: 205;">
  <!--div style="font-weight: bold; text-align: right;">Экземпляр МО</div-->
  <table width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td style="width: 42%; border: 1px solid #000; vertical-align: top; padding-left: 5px;">
      <div style="text-align: center;">Источник финансирования (нужное подчеркнуть):</div>
      <div><span style="border-bottom: {recept_finance_1};">1. федеральный бюджет</span></div>
      <div><span style="border-bottom: {recept_finance_2};">2. бюджет субъекта РФ</span></div>
      <div><span style="border-bottom: {recept_finance_3};">3. муниципальный бюджет</span></div>
      <div>&nbsp;</div>
    </td>
    <td style="width: 28%; border: 1px solid #000; vertical-align: top;">
      <div style="text-align: center;">% оплаты из источника финансирования (нужное подчеркнуть):</div>
      <div style="text-align: center;"><span style="border-bottom: {recept_discount_1};">&nbsp; 1. 100% &nbsp;</span></div>
      <div style="text-align: center;"><span style="border-bottom: {recept_discount_2};">&nbsp; 2. 50% &nbsp;</span></div>
      <div>&nbsp;</div>
    </td>
    <td style="width: 30%; border: 1px solid #000; vertical-align: top;">
      <div style="text-align: center;">Рецепт действителен в течение:</div>
	<div style="margin-left:20px">
	    <div><span style="border-bottom: {recept_valid_4};">&nbsp; 1. 5 дней &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_7};">&nbsp; 2. 10 дней &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_1};">&nbsp; 3. 1 месяца &nbsp;</span></div>
	    <div><span style="border-bottom: {recept_valid_2};">&nbsp; 4. 3 месяцев &nbsp;</span></div>
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
        <td style="width: 10%;">v</td>
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
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_1}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_2}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_3}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_4}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_5}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_6}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_7}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_8}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_9}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_10}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_11}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_12}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_13}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_14}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_15}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_16}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_17}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_18}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_19}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_20}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_21}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_22}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_23}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_24}</td>
    <td style="width: 2.5%; border: 1px solid #000;">{polis_ser_num_25}</td>
    <td style="width: 37.5%; border: 1px solid #000; text-align: left; padding-left: 2px;">{orgsmo_name}</td>
  </tr></table>
</div>

<div style="position: absolute; top: 280; left: 0; width: 200;">Адрес или № медицинской карты амбулаторного пациента (истории развития ребенка)</div>

<div style="position: absolute; top: 278; left: 205; width: 140; border-bottom: 1px solid #000; text-align: center; font-size: 8px;">{ambul_card_num}</div>

<!--<div style="position: absolute; top: 300; left: 0;">Адрес:</div>

<div style="position: absolute; top: 297; left: 25; width: 320; border-bottom: 1px solid #000; font-size: 8px;">&nbsp; {address_string_1}</div>

<div style="position: absolute; top: 313; left: 0; width: 345; border-bottom: 1px solid #000; font-size: 8px;">&nbsp; {address_string_2}</div>-->

<div style="position: absolute; top: 300; left: 0;">Ф.И.О. лечащего врача :</div>

<div style="position: absolute; top: 295; left: 85; width: 260; border-bottom: 1px solid #000; font-size: 10px;">&nbsp; {medpersonal_fio}</div>

<div style="position: absolute; top: 315; left: 0; width: 120;">
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

<div style="position: absolute; top: 335; left: 0;">Выписано:</div>

<div style="position: absolute; top: 344; left: 0; width: 165; border-bottom: 1px solid #000;">Rp.</div>

<div style="position: absolute; top: 355; left: 0; width: 165; height: 35; border-bottom: 1px solid #000; font-weight: bold; font-family: courier new; text-align: center; font-size: 7pt;">{drug_name}</div>

<div style="position: absolute; top: 405; left: 0;">D.t.d.</div>

<div style="position: absolute; top: 393; left: 30; width: 135; font-weight: bold; font-size: 8px;">{drug_form}</div>

<div style="position: absolute; top: 415; left: 0;">Дозировка</div>

<div style="position: absolute; top: 413; left: 40; width: 125; font-weight: bold; font-size: 8px;">{drug_dose}</div>

<div style="position: absolute; top: 435; left: 0;">Количество единиц</div>

<div style="position: absolute; top: 433; left: 65; width: 100; font-weight: bold; font-size: 8px;">{drug_kolvo}</div>

<div style="position: absolute; top: 453; left: 20; width: 130; font-weight: bold; font-size: 8px;">{signa}</div>

<div style="position: absolute; top: 452; left: 0; width: 165; border-top: 1px solid #000;">Signa</div>

<div style="position: absolute; top: 475; left: 0; width: 165; border-top: 1px solid #000;">&nbsp;</div>

<div style="position: absolute; top: 490; left: 70; width: 165; text-align: center;">М.П.</div>

<div style="position: absolute; top: 315; left: 175; width: 168; border: 1px solid #000;">
  <div style="padding-top: 1px; text-align: center;">(заполняется специалистом аптечной организации)</div>
  <div style="padding-left: 5px; padding-right: 5px;">
    <div>Отпущено по рецепту:</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Дата отпуска</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">Код лекарственного<br />препарата</div>
    <div style="border-bottom: 1px solid #000; padding-top: 1em; height: 30px;">Торговое<br />наименование</div>
    <div style="border-bottom: 1px solid #000; height: 20px;">&nbsp;</div>
    <div style="border-bottom: 1px solid #000; padding-top: 1em; padding-bottom: 1em;">Количество</div>
    <div style="border-bottom: 1px solid #000; padding-top: 2em; padding-bottom: 2em;">На общую сумму</div>
    <div style="height: 5px;">&nbsp;</div>
  </div>
</div>

<div style="position: absolute; top: 505; left: 175; width: 165; padding-bottom: 1em; border-bottom: 1px solid #000;">
  Подпись лечащего врача и<br />личная печать лечащего врача
</div>

<div style="position: absolute; top: 580; left: 0; width: 345; text-align: center; font-size: 9px;"></div>

<div style="position: absolute; top: 600; left: 0; width: 345; border-bottom: 1px dashed #000; text-align: center;">(линия отрыва)</div>

<div style="position: absolute; top: 615; left: 0; width: 345; text-align: center;">
  Корешок РЕЦЕПТА &nbsp;&nbsp;&nbsp;
  Серия &nbsp;&nbsp; <span style="font-weight: bold; font-size: 8px;">{recept_ser}</span> &nbsp;&nbsp;&nbsp;
  № &nbsp;&nbsp; <span style="font-weight: bold; font-size: 8px;">{recept_num}</span> &nbsp;&nbsp;&nbsp;
  от &nbsp;&nbsp; <span style="font-weight: bold; font-size: 8px;">{recept_date}</span>
</div>

<div style="position: absolute; top: 630; left: 0;">Способ применения:</div>

<div style="position: absolute; top: 645; left: 0; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">Продолжительность</div>

<div style="position: absolute; top: 645; left: 150;">дней</div>

<div style="position: absolute; top: 675; left: 0; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">Количество приемов в день:</div>

<div style="position: absolute; top: 675; left: 155;">раз</div>

<div style="position: absolute; top: 705; left: 0; width: 165; border-bottom: 1px solid #000; padding-bottom: 1em;">На 1 прием:</div>

<div style="position: absolute; top: 705; left: 155;">ед.</div>

<div style="position: absolute; top: 645; left: 175; width: 170; border-bottom: 1px solid #000; padding-bottom: 1em;">Наименование лекарственного препарата:</div>

<div style="position: absolute; top: 675; left: 175; width: 170; border-bottom: 1px solid #000; padding-bottom: 1em;">&nbsp;</div>

<div style="position: absolute; top: 705; left: 175; width: 170; border-bottom: 1px solid #000; padding-bottom: 1em;">Дозировка:</div>

<!-- ============================== END OF ЭКЗЕМПЛЯР МО ============================== -->

</body>
