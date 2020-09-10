<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>{recept_template_title}</title>
<style type="text/css">
body { margin: 0px; padding: 0px; }
table { border-collapse: collapse; }
span, div, td { font-family: tahoma, verdana; font-size: 12px; font-weight: bold; }
</style>

<style type="text/css" media="print">
body { margin: 0px; padding: 0px; }
span, div, td { font-family: tahoma, verdana; font-size: 12px; font-weight: bold; }
td { vertical-align: bottom; }
</style>
</head>

<body>
<script>window.print();</script>
<!-- Код категории граждан -->
<!-- Замена: 3 значащих цифры -->
<div style="position: absolute; top: 168; left: 7;">{privilege_type_code_1}</div>
<div style="position: absolute; top: 168; left: 22;">{privilege_type_code_2}</div>
<div style="position: absolute; top: 168; left: 37;">{privilege_type_code_3}</div>

<!-- Источник финансирования -->
<!-- Замена: отступ сверху для линии подчеркивания -->
<div style="position: absolute; top: 137; left: 130; width: 100; border-bottom: {recept_finance_1};">&nbsp;</div>
<div style="position: absolute; top: 150; left: 130; width: 100; border-bottom: {recept_finance_2};">&nbsp;</div>
<div style="position: absolute; top: 163; left: 130; width: 100; border-bottom: {recept_finance_3};">&nbsp;</div>

<!-- % оплаты -->
<!-- Замена: отступ сверху для линии подчеркивания -->
<div style="position: absolute; top: 139; left: 283; width: 70; border-bottom: {recept_discount_1};">&nbsp;</div>
<div style="position: absolute; top: 153; left: 283; width: 70; border-bottom: {recept_discount_2};">&nbsp;</div>

<!-- Код лекарственного средства -->
<!-- Замена: 8 значащих цифр -->
<div style="position: absolute; top: 154; left: 370;">{drug_code_1}</div>
<div style="position: absolute; top: 154; left: 385;">{drug_code_2}</div>
<div style="position: absolute; top: 154; left: 400;">{drug_code_3}</div>
<div style="position: absolute; top: 154; left: 415;">{drug_code_4}</div>
<div style="position: absolute; top: 154; left: 430;">{drug_code_5}</div>
<div style="position: absolute; top: 154; left: 445;">{drug_code_6}</div>
<div style="position: absolute; top: 154; left: 460;">{drug_code_7}</div>
<div style="position: absolute; top: 154; left: 475;">{drug_code_8}</div>

<!-- Дата выписки -->
<!-- Замена: 6 значащих цифр -->
<div style="position: absolute; top: 204; left: 348;">{recept_date_1}</div>
<div style="position: absolute; top: 204; left: 363;">{recept_date_2}</div>
<div style="position: absolute; top: 204; left: 393;">{recept_date_3}</div>
<div style="position: absolute; top: 204; left: 408;">{recept_date_4}</div>
<div style="position: absolute; top: 204; left: 443;">{recept_date_7}</div>
<div style="position: absolute; top: 204; left: 455;">{recept_date_8}</div>

<!-- ФИО пациента -->
<!-- Замена: строка -->
<div style="position: absolute; top: 232; left: 90;">{person_fio}</div>

<!-- Дата рождения -->
<!-- Замена: 8 значащих цифр -->
<div style="position: absolute; top: 260; left: 113;">{person_birthday_1}</div>
<div style="position: absolute; top: 260; left: 128;">{person_birthday_2}</div>
<div style="position: absolute; top: 260; left: 157;">{person_birthday_3}</div>
<div style="position: absolute; top: 260; left: 172;">{person_birthday_4}</div>
<div style="position: absolute; top: 260; left: 204;">{person_birthday_5}</div>
<div style="position: absolute; top: 260; left: 219;">{person_birthday_6}</div>
<div style="position: absolute; top: 260; left: 234;">{person_birthday_7}</div>
<div style="position: absolute; top: 260; left: 249;">{person_birthday_8}</div>

<!-- СНИЛС -->
<!-- Замена: 11 значащих цифр -->
<div style="position: absolute; top: 293; left: 112;">{person_snils_1}</div>
<div style="position: absolute; top: 293; left: 127;">{person_snils_2}</div>
<div style="position: absolute; top: 293; left: 142;">{person_snils_3}</div>
<div style="position: absolute; top: 293; left: 158;">{person_snils_5}</div>
<div style="position: absolute; top: 293; left: 173;">{person_snils_6}</div>
<div style="position: absolute; top: 293; left: 188;">{person_snils_7}</div>
<div style="position: absolute; top: 293; left: 203;">{person_snils_9}</div>
<div style="position: absolute; top: 293; left: 218;">{person_snils_10}</div>
<div style="position: absolute; top: 293; left: 233;">{person_snils_11}</div>
<div style="position: absolute; top: 293; left: 249;">{person_snils_13}</div>
<div style="position: absolute; top: 293; left: 264;">{person_snils_14}</div>

<!-- Серия полиса -->
<!-- Замена: 6 значащих символов -->
<div style="position: absolute; top: 315; left: 110;">{polis_ser_1}</div>
<div style="position: absolute; top: 315; left: 125;">{polis_ser_2}</div>
<div style="position: absolute; top: 315; left: 140;">{polis_ser_3}</div>
<div style="position: absolute; top: 315; left: 155;">{polis_ser_4}</div>
<div style="position: absolute; top: 315; left: 170;">{polis_ser_5}</div>
<div style="position: absolute; top: 315; left: 185;">{polis_ser_6}</div>

<!-- Номер полиса -->
<!-- Замена: 8 значащих цифр -->
<div style="position: absolute; top: 315; left: 203;">{polis_num_1}</div>
<div style="position: absolute; top: 315; left: 218;">{polis_num_2}</div>
<div style="position: absolute; top: 315; left: 233;">{polis_num_3}</div>
<div style="position: absolute; top: 315; left: 249;">{polis_num_4}</div>
<div style="position: absolute; top: 315; left: 264;">{polis_num_5}</div>
<div style="position: absolute; top: 315; left: 279;">{polis_num_6}</div>
<div style="position: absolute; top: 315; left: 294;">{polis_num_7}</div>
<div style="position: absolute; top: 315; left: 309;">{polis_num_8}</div>

<!-- Наименование страховой -->
<!-- Замена: строка -->
<div style="position: absolute; top: 315; left: 324;">{orgsmo_name}</div>

<!-- Адрес или номер амбулаторной карты -->
<!-- Замена: 2 строки -->
<div style="position: absolute; top: 337; left: 279;">{person_state_1}</div>
<div style="position: absolute; top: 359; left: 135;">{person_state_2}</div>

<!-- ФИО врача -->
<!-- Замена: строка -->
<div style="position: absolute; top: 381; left: 75;">{medpersonal_fio}</div>

<!-- Код врача -->
<!-- Замена: 6 значащих цифр -->
<div style="position: absolute; top: 487; left: 28;">{medpersonal_code_1}</div>
<div style="position: absolute; top: 487; left: 43;">{medpersonal_code_2}</div>
<div style="position: absolute; top: 487; left: 58;">{medpersonal_code_3}</div>
<div style="position: absolute; top: 487; left: 73;">{medpersonal_code_4}</div>
<div style="position: absolute; top: 487; left: 88;">{medpersonal_code_5}</div>
<div style="position: absolute; top: 487; left: 103;">{medpersonal_code_6}</div>

<!-- Рецепт действителен -->
<!-- Замена: строка -->
<div style="position: absolute; top: 508; left: 280; font-weight: normal; font-size: 9px; text-decoration: underline;">&nbsp;{recept_valid}&nbsp;</div>

</body>

</html>