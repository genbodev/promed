<html>
 <head>
  <title>Заявление представителя на прикрепление: {Person_FIO}</title>
  <style type="text/css">
   p, span, div { font-family: verdana; font-size: 14pt; }
   p.ulText { margin-bottom: -0.35%; }
   .header { margin-left: 50%; }
   .bottomText { text-align: center; margin-top: -3px; font-size: 10pt; }
   p.title { text-align: center; font-weight: bold; margin-top: 40pt; }
   span.ulString { text-decoration: underline; }
   p.cdSign { text-align: left; margin-left: 70px; }
  </style>
 </head>
 <body>
  <!-- /*NO PARSE JSON*/ -->
  <p class="header ulText">Главному врачу (руководителю)<br/>
   {Lpu_Name}
  </p>
  <hr class="header"/>
  <p class="header bottomText">(наименование медицинской организации)</p>

  <p class="header ulText">
   {Lpu_Address}
  </p>
  <hr class="header"/>
  <p class="header bottomText">(адрес медицинской организации)</p>

  <p class="header ulText">
   от {DeputyPerson_FIO}
  </p>
  <hr class="header"/>
  <p class="header bottomText">(Ф.И.О. полностью)</p>

  <p class="title">ЗАЯВЛЕНИЕ<br/>о выборе медицинской организации.</p>

  <p class="ulText">Я, {DeputyPerson_FIO}, прошу зарегистрировать гражданина
  <hr/>
  <p class="bottomText">(фамилия, имя, отчество)</p>

  <p class="ulText">{Person_FIO}, {Person_Birthday}, {Sex_Name}
  <hr/>
  <p class="bottomText">(фамилия, имя, отчество, дата рождения, пол – М/Ж)</p>

  <p>законным представителем которого я являюсь:</p>

  <p class="ulText">{DeputyReason}
  <hr/>
  <p class="bottomText">(указать основание: а) несовершеннолетний ребенок; б) недееспособность; в) попечительство и т.д., а также вид, номер, дату и место выдачи документа, подтверждающего право законного представителя)</p>

  <p class="ulText">в {Lpu_Name},
  <hr/>
  <p class="bottomText">(полное наименование медицинской организации)</p>

  <p class="ulText">участковый врач {MedStaffRegion_Fio}
  <hr/>
  <p class="bottomText">(Ф.И.О.
   <?php if ($MedStaffRegionPost_id == 74) echo('<span class="bottomText ulString">'); ?>врача-терапевта участкового<?php if ($MedStaffRegionPost_id == 74) echo('</span>'); ?>,
   <?php if ($MedStaffRegionPost_id == 47) echo('<span class="bottomText ulString">'); ?>врача-педиатра участкового<?php if ($MedStaffRegionPost_id == 47) echo('</span>'); ?>,
   <?php if ($MedStaffRegionPost_id == 40) echo('<span class="bottomText ulString">'); ?>врача общей практики<?php if ($MedStaffRegionPost_id == 40) echo('</span>'); ?>
   – нужное подчеркнуть)
  </p>

  <p class="ulText">Полис ОМС: № {Polis_Num}
  <hr/>
  <p class="bottomText">(
   <?php if ($PolisType_id == 1) echo('<span class="bottomText ulString">'); ?>1 - полис старого образца<?php if ($PolisType_id == 1) echo('</span>'); ?>,
   <?php if ($PolisType_id == 3) echo('<span class="bottomText ulString">'); ?>2 - временное свидетельство<?php if ($PolisType_id == 3) echo('</span>'); ?>,
   <?php if ($PolisType_id == 4) echo('<span class="bottomText ulString">'); ?>3 - полис единого образца<?php if ($PolisType_id == 4) echo('</span>'); ?>
   – нужное подчеркнуть)
  </p>

  <p class="ulText">выдан страховой медицинской организацией {OrgSmo_Name}
  <hr/>
  <p class="bottomText">(название страховой медицинской организации)</p>

  <div style="width: 200px">
   <p class="ulText">{Polis_begDate}
   <hr/>
   <p class="bottomText">(дата выдачи полиса)</p>
  </div>

  <p class="ulText">Гражданство {KLCountry_Name}
  <hr/>

  <p class="ulText">Место рождения {Person_BAddress}
  <hr/>

  <p class="ulText">Место жительства {Person_PAddress}
  <hr/>
  <p class="bottomText">(адрес проживания по постоянной регистрации, по временной регистрации,
   <?php if ($Person_PAddress_id && $Person_PAddress_id != $Person_RAddress_id) echo('<span class="bottomText ulString">'); ?>по месту фактического проживания без регистрации<?php if ($Person_PAddress_id && $Person_PAddress_id != $Person_RAddress_id) echo('</span>'); ?>
   - нужное подчеркнуть)
  </p>

  <p class="ulText">Место и дата регистрации (если имеется) {Person_RAddress}
  <hr/>

  <p class="ulText">Находится на обслуживании в медицинской организации {PrevLpu_Name},
  <hr/>
  <p class="bottomText">(название медицинской организации)</p>

  <p class="ulText">расположенной по адресу {PrevLpu_Address},
  <hr/>
  <p class="bottomText">(адрес медицинской организации)</p>

  <p>Не находится на обслуживании в медицинской организации (подчеркнуть, если не находится на обслуживании в медицинской организации).</p>

  <p class="ulText">Паспорт (другой документ, удостоверяющий личность) {DeputyDocumentType_Name}: серия {DeputyDocument_Ser} № {DeputyDocument_Num}, выдан {DeputyDocument_begDate}
  <hr/>

  <p class="ulText">{DeputyOrgDep_Name}&nbsp;
  <hr/>
  <p class="bottomText">(название органа, выдавшего документ)</p>

  <p class="ulText">Контактный телефон {DeputyPerson_Phone}
  <hr/>

  <br/><br/><br/>
  <p>Личная подпись _______________________________<p>
  <p>"____" __________________ 20____ г.</p>

  <br/><br/>
  <p><b>РЕШЕНИЕ ГЛАВНОГО ВРАЧА:</b></p>
  <p>Зарегистрировать с "____" __________________ 20____ г.</p>
  <p>Отказать в регистрации в связи</p>
  <hr/><br/><hr/>
  <br/><br/><hr/>
  <p class="bottomText cdSign">(подпись)<span class="bottomText" style="margin-left: 100px">(ФИО главного врача)</span></p>
  <p>"____" __________________ 20____ г.</p>
  <br/>
  <p>М. П.</p>
  <br/>

  <p>По требованию заявителя копия заявления с решением главного врача выдана на руки</p>
  <p>"____" __________________ 20____ г.</p>

  <p>Получил копию заявления</p>
  <br/><hr/>
  <p class="bottomText cdSign">(подпись)<span class="bottomText" style="margin-left: 100px">(ФИО)</span></p>
</html>
