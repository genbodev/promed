<html>
<head>
<title>Печать данных по регистратуре </title>
<style type="text/css">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 30px; font-family: times, tahoma, verdana; font-size: 14px;}
table { border-collapse: collapse; width: 100%}
span, div, td { font-family: times, tahoma, verdana; font-size: 14px; }
th { text-align: center; font-size: 14px; border-collapse: collapse; border: 1px solid black; padding: 3px; vertical-align: top;}
h1 { text-align: center; font-size: 20px; font-weight: bold}
.printtable {border: 1px solid black}
.printtable th {text-align: center; padding: 3px; font-weight: normal}
.printtable td {text-align: center; padding: 3px; border: 1px solid black}
</style>

<style type="text/css" media="print">
@page port { size: portrait }
@page land { size: landscape }
body { margin: 0px; padding: 30px; font-family: times, tahoma, verdana; font-size: 14px;}
table {  border-collapse: collapse; width: 100%}
span, div, td { font-family: times, tahoma, verdana; font-size: 14px; }
td { vertical-align: top; }
th { text-align: center; font-size: 14px; border-collapse: collapse; border: 1px solid black; padding: 3px; vertical-align: top;}
h1 { text-align: center; font-size: 20px; font-weight: bold}
.cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
.pg {page-break-before: always}
.printtable {border: 1px solid black}
.printtable th {text-align: center; padding: 3px; font-weight: normal}
.printtable td {text-align: center; padding: 3px; border: 1px solid black}
</style>
</head>

<body>
<div style="text-align: right">Приложение №1</div>
<div style="text-align: center; font-weight: bold; font-size: 14pt;">{form_caption}</div>
<div style="text-align: right;">Данная форма предоставляется на ЛПУ в целом</div>
<div style="font-weight: bold; padding: 10px 0 10px 0">Общая информация об ЛПУ</div>
<table border='1'>
<tr>
<td style="width: 50%">Название ЛПУ</td>
<td>&nbsp;{Lpu_Nick}</td>
</tr>
<tr>
<td style="width: 50%">Серия, № и дата окончания лицензии</td>
<td>&nbsp;{Lpu_Licence}</td>
</tr>
<tr>
<td style="width: 50%">Полное наименование ЛПУ</td>
<td>&nbsp;{Lpu_Name}</td>
</tr>
<tr>
<td style="width: 50%">Юридический адрес поликлиники</td>
<td>&nbsp;{Lpu_UAddress}</td>
</tr>
<tr>
<td style="width: 50%">Адрес (а) электронной почты администрации ЛПУ</td>
<td>&nbsp;{Lpu_Email}</td>
</tr>
<tr>
<td style="width: 50%">ФИО главного врача, контактный телефон</td>
<td>&nbsp;{Lpu_GlavVrach_FioPhone}</td>
</tr>
<tr>
<td style="width: 50%">ФИО заместителя главного врача, контактный телефон</td>
<td>&nbsp;{Lpu_ZamGlavVrach_FioPhone}</td>
</tr>
<tr>
<td style="width: 50%">ФИО заведующего отделением терапии (педиатрии), контактный телефон</td>
<td>&nbsp;{Lpu_ZavTer_FioPhone}</td>
</tr>
<tr>
<td style="width: 50%">ФИО заведующего узкими специалистами,  контактный телефон</td>
<td>&nbsp;{Lpu_ZavUzk_FioPhone}</td>
</tr>
<tr>
<td style="width: 50%">{label_er_info}</td>
<td>&nbsp;{Lpu_ErInfo}</td>
</tr>
<tr>
<td style="width: 50%">Адрес сайта ЛПУ</td>
<td>&nbsp;{Lpu_Www}</td>
</tr>
</table>
<div style="font-weight: bold; padding: 10px 0 10px 0">Информация об ответственных сотрудниках ЛПУ</div>
<table border='1'>
<tr>
<th>Зона ответственности</th>
<th>ФИО</th>
<th>Должность</th>
<th>e-mail</th>
<th>Рабочий телефон</th>
<th>Мобильный телефон (для SMS-уведомлений)</th>
<th>№ и дата приказа о назначении</th>
<th>Адрес, № рабочего кабинета</th>
</tr>
<tr>
<td>Общая координация работ по внедрению проекта «Единая регистратура» (заместитель главного врача по медицинской части)</td>
<td>&nbsp;<?=$orghead_lpu[6]['OrgHead_FIO']?></td>
<td>&nbsp;<?=$orghead_lpu[6]['OrgHeadPost_Name']?></td>
<td>&nbsp;<?=$orghead_lpu[6]['OrgHead_Email']?></td>
<td>&nbsp;<?=$orghead_lpu[6]['OrgHead_Phone']?></td>
<td>&nbsp;<?=$orghead_lpu[6]['OrgHead_Mobile']?></td>
<td>&nbsp;<?=$orghead_lpu[6]['OrgHead_CommissNum'].' '.$orghead_lpu[6]['OrgHead_CommissDate']?></td>
<td>&nbsp;<?=$orghead_lpu[6]['OrgHead_Address']?></td>
</tr>
<tr>
<td>Контроль качества оказания услуг (заместитель главного врача по медицинской части или ЭВН)<sup>2</sup></td>
<td>&nbsp;<?=$orghead_lpu[7]['OrgHead_FIO']?></td>
<td>&nbsp;<?=$orghead_lpu[7]['OrgHeadPost_Name']?></td>
<td>&nbsp;<?=$orghead_lpu[7]['OrgHead_Email']?></td>
<td>&nbsp;<?=$orghead_lpu[7]['OrgHead_Phone']?></td>
<td>&nbsp;<?=$orghead_lpu[7]['OrgHead_Mobile']?></td>
<td>&nbsp;<?=$orghead_lpu[7]['OrgHead_CommissNum'].' '.$orghead_lpu[7]['OrgHead_CommissDate']?></td>
<td>&nbsp;<?=$orghead_lpu[7]['OrgHead_Address']?></td>
<td></td>
</tr>
<tr>
<td>Ведение расписания<sup>3</sup></td>
<td>&nbsp;<?=$orghead_lpu[8]['OrgHead_FIO']?></td>
<td>&nbsp;<?=$orghead_lpu[8]['OrgHeadPost_Name']?></td>
<td>&nbsp;<?=$orghead_lpu[8]['OrgHead_Email']?></td>
<td>&nbsp;<?=$orghead_lpu[8]['OrgHead_Phone']?></td>
<td>&nbsp;<?=$orghead_lpu[8]['OrgHead_Mobile']?></td>
<td>&nbsp;<?=$orghead_lpu[8]['OrgHead_CommissNum'].' '.$orghead_lpu[8]['OrgHead_CommissDate']?></td>
<td>&nbsp;<?=$orghead_lpu[8]['OrgHead_Address']?></td>
</tr>
<tr>
<td>Ведение очереди<sup>4</sup></td>
<td>&nbsp;<?=$orghead_lpu[9]['OrgHead_FIO']?></td>
<td>&nbsp;<?=$orghead_lpu[9]['OrgHeadPost_Name']?></td>
<td>&nbsp;<?=$orghead_lpu[9]['OrgHead_Email']?></td>
<td>&nbsp;<?=$orghead_lpu[9]['OrgHead_Phone']?></td>
<td>&nbsp;<?=$orghead_lpu[9]['OrgHead_Mobile']?></td>
<td>&nbsp;<?=$orghead_lpu[9]['OrgHead_CommissNum'].' '.$orghead_lpu[9]['OrgHead_CommissDate']?></td>
<td>&nbsp;<?=$orghead_lpu[9]['OrgHead_Address']?></td>
</tr>
<tr>
<td>{forum_label}</td>
<td>&nbsp;<?=$orghead_lpu[10]['OrgHead_FIO']?></td>
<td>&nbsp;<?=$orghead_lpu[10]['OrgHeadPost_Name']?></td>
<td>&nbsp;<?=$orghead_lpu[10]['OrgHead_Email']?></td>
<td>&nbsp;<?=$orghead_lpu[10]['OrgHead_Phone']?></td>
<td>&nbsp;<?=$orghead_lpu[10]['OrgHead_Mobile']?></td>
<td>&nbsp;<?=$orghead_lpu[10]['OrgHead_CommissNum'].' '.$orghead_lpu[10]['OrgHead_CommissDate']?></td>
<td>&nbsp;<?=$orghead_lpu[10]['OrgHead_Address']?></td>
</tr>
<tr>
<td>Техническое обеспечение работы комплекса «Единая регистратура»</td>
<td>&nbsp;<?=$orghead_lpu[11]['OrgHead_FIO']?></td>
<td>&nbsp;<?=$orghead_lpu[11]['OrgHeadPost_Name']?></td>
<td>&nbsp;<?=$orghead_lpu[11]['OrgHead_Email']?></td>
<td>&nbsp;<?=$orghead_lpu[11]['OrgHead_Phone']?></td>
<td>&nbsp;<?=$orghead_lpu[11]['OrgHead_Mobile']?></td>
<td>&nbsp;<?=$orghead_lpu[11]['OrgHead_CommissNum'].' '.$orghead_lpu[11]['OrgHead_CommissDate']?></td>
<td>&nbsp;<?=$orghead_lpu[11]['OrgHead_Address']?></td>
</tr>
</table>
<div style="width: 200px; border-bottom: 1px solid black;">&nbsp;</div>
<div><sup>1</sup>Необходимо указать допускается или нет запись на резервные бирки, если не допускается, то куда обращаться пациенту, если допускается, то каким образом производится согласование записи.</div>
<div><sup>2</sup>В целях повышения качества обслуживания населения, ФИО, должность, рабочий телефон, адрес и № рабочего кабинета данного сотрудника будут открыты для общего доступа.</div>
<div><sup>3</sup>При условии, что данная работа ведется в целом по ЛПУ, и не ведется раздельно по филиалам.</div>
<div><sup>4</sup>При условии, что данная работа ведется в целом по ЛПУ, и не ведется раздельно по филиалам</div>
{lpuunit_data}
<div class="pg" style="text-align: right; padding-top: 30px;">Данная форма предоставляется на каждый филиал ЛПУ в отдельности</div>
<div style="font-weight: bold; padding: 10px 0 10px 0">Общая информация о филиале ЛПУ</div>
<table border='1'>
<tr>
<td style="width: 50%">Название филиала</td>
<td>&nbsp;{LpuUnit_Name}</td>
</tr>
<tr>
<td style="width: 50%">Адрес филиала</td>
<td>&nbsp;{LpuUnit_Address}</td>
</tr>
<tr>
<td style="width: 50%">Телефон регистратуры</td>
<td>&nbsp;{LpuUnit_Phone}</td>
</tr>
<tr>
<td style="width: 50%">Адрес (а) электронной почты филиала (если есть)</td>
<td>&nbsp;{LpuUnit_Email}</td>
</tr>
<tr>
<td style="width: 50%">ФИО руководителя филиала, контактный телефон</td>
<td>&nbsp;{OrgHead_FIO12} {OrgHead_Phone12}</td>
</tr>
<tr>
<td style="width: 50%">ФИО заведующего отделением терапии (педиатрии), контактный телефон</td>
<td>&nbsp;{OrgHead_FIO13} {OrgHead_Phone13}</td>
</tr>
<tr>
<td style="width: 50%">ФИО заведующего узкими специалистами, контактный телефон</td>
<td>&nbsp;{OrgHead_FIO14} {OrgHead_Phone14}</td>
</tr>
<tr>
<td style="width: 50%">Ip-адрес филиала<sup>5</sup></td>
<td>&nbsp;{LpuUnit_IP}</td>
</tr>
</table>
<div style="font-weight: bold; padding: 10px 0 10px 0">Информация об ответственных сотрудниках филиала ЛПУ</div>
<table border='1'>
<tr>
<th>Зона ответственности</th>
<th>ФИО</th>
<th>Должность</th>
<th>e-mail</th>
<th>Рабочий телефон</th>
<th>Мобильный телефон (для SMS-уведомлений)</th>
<th>№ и дата приказа о назначении</th>
<th>Адрес, № рабочего кабинета</th>
</tr>
<tr>
<td>Ведение расписания<sup>6</sup></td>
<td>&nbsp;{OrgHead_FIO8}</td>
<td>&nbsp;{OrgHeadPost_Name8}</td>
<td>&nbsp;{OrgHead_Email8}</td>
<td>&nbsp;{OrgHead_Phone8}</td>
<td>&nbsp;{OrgHead_Mobile8}</td>
<td>&nbsp;{OrgHead_CommissNum8} {OrgHead_CommissDate8}</td>
<td>&nbsp;{OrgHead_Address8}</td>
</tr>
<tr>
<td>Ведение очереди<sup>7</sup></td>
<td>&nbsp;{OrgHead_FIO9}</td>
<td>&nbsp;{OrgHeadPost_Name9}</td>
<td>&nbsp;{OrgHead_Email9}</td>
<td>&nbsp;{OrgHead_Phone9}</td>
<td>&nbsp;{OrgHead_Mobile9}</td>
<td>&nbsp;{OrgHead_CommissNum9} {OrgHead_CommissDate9}</td>
<td>&nbsp;{OrgHead_Address9}</td>
</tr>
<tr>
<td>{forum_label}</td>
<td>&nbsp;{OrgHead_FIO10}</td>
<td>&nbsp;{OrgHeadPost_Name10}</td>
<td>&nbsp;{OrgHead_Email10}</td>
<td>&nbsp;{OrgHead_Phone10}</td>
<td>&nbsp;{OrgHead_Mobile10}</td>
<td>&nbsp;{OrgHead_CommissNum10} {OrgHead_CommissDate10}</td>
<td>&nbsp;{OrgHead_Address10}</td>
</tr>
</table>
<div style="width: 200px; border-bottom: 1px solid black;">&nbsp;</div>
<div><sup>5</sup>Если подключение производится через внутреннюю сеть ЛПУ, вместо ip-адреса необходимо указать, через какой филиал осуществляется подключение</div>
<div><sup>6</sup>При условии, что данная работа ведется в данном филиале обособленно.</div>
<div><sup>7</sup>При условии, что данная работа ведется в данном филиале обособленно.</div>
{/lpuunit_data}
</body>
</html>