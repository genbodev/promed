<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <title>{recept_template_title}</title>
    <style type="text/css">
        @page port { size: portrait }
        @page land { size: landscape }
        body { margin: 5px; padding: 5px; }
        table { border-collapse: collapse; }
        span, div, td { font-family: tahoma, verdana; font-size: 12px; }
    </style>

    <style type="text/css" media="print">
        @page port { size: portrait }
        @page land { size: landscape }
        body { margin: 3px; padding: 3px; }
        span, div, td { font-family: tahoma, verdana; font-size: 9px; }
        td { vertical-align: top; }
        .more {
            page-break-after: always;
        }
    </style>
</head>

<body class="land">
<script>window.print();</script>
<table style="width: 100%; height: 100%;">
<tr style="height: 100%;">
<td style="width: 49%;">

    <div style="text-align: right;">
        Утверждена <br>
        Приказом Министерства здравоохранения <br>
        Российской Федерации <br>
        от 20 декабря 2012 г. N 1181н <br>
        Форма N 1-МИ
    </div>
    <div style="text-align: center; font-size: 15px;"><b>РЕЦЕПТУРНЫЙ БЛАНК НА МЕДИЦИНСКИЕ ИЗДЕЛИЯ</b></div>
    <br>
    <div>Наименование медицинской организации: <b>{lpu_name}</b></div>
    <div>Штамп &nbsp;</div>
    <div>ОГРН {lpu_ogrn}</div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width: 42%; border: 1px solid #000; vertical-align: top; padding-left: 5px;">
                <div style="text-align: left;">Источник финансирования при льготном обеспечении (нужное подчеркнуть):</div>
                <div><span style="border-bottom: {recept_finance_1};">1. федеральный бюджет</span></div>
                <div><span style="border-bottom: {recept_finance_2};">2. бюджет субъекта Российской Федерации</span></div>
                <br>
            </td>
            <td style="width: 28%; border: 1px solid #000; vertical-align: top;">
                <div style="text-align: left;">Процент оплаты пациентом:</div>
                <div style="text-align: left;"><span style="border-bottom: {recept_discount_000};">1. 100%</span></div>
                <div style="text-align: left;"><span style="border-bottom: {recept_discount_mi1};">2. бесплатно</span></div>
            </td>
            <td style="width: 30%; border: 1px solid #000; vertical-align: top;">
                <div style="text-align: left;">Рецепт действителен в течение</div>
                <div style="margin-left:0px">
                    <div><span style="border-bottom: {recept_valid_1};">1 месяца</span></div>
                    <div><span style="border-bottom: {recept_valid_2};">3 месяцев</span></div>
                </div>
            </td>
        </tr></table>
    <br>
    <div>
        РЕЦЕПТ
        Серия <ins>{recept_ser}</ins>&nbsp;&nbsp;&nbsp;
        N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <ins>{recept_num}</ins>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        Дата выдачи от <ins>{recept_date_1}{recept_date_2}.{recept_date_3}{recept_date_4}.{recept_date_5}{recept_date_6}{recept_date_7}{recept_date_8}г.</ins>
    </div>
    <br>
    <center>
        <table style="height:20px; width: 320px;">
            <tr>
                <td width="20px" height="20px" style="border: 1px solid #000;"></td>
                <td width="300px" height="20px" style="vertical-align:middle;">&nbsp;&nbsp;"Пациенту с хроническим заболеванием"</td>
            </tr>
        </table>
    </center>
    <table style="width: 100%;" cellpadding="5px">
        <tr style="border-bottom: 1px solid #000;">
            <td style="width: 40%;">
                Ф.И.О. пациента
            </td>
            <td style="width: 60%;">
                <b>{person_fio}</b>
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Дата рождения от</td>
            <td><b>{person_birthday_1}{person_birthday_2}.{person_birthday_3}{person_birthday_4}.{person_birthday_5}{person_birthday_6}{person_birthday_7}{person_birthday_8}г.</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Номер страхового медицинского полиса</td>
            <td><b>ОС {polis_ser_num}, СНИЛС:{person_snils}</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Номер медицинской карты пациента</td>
            <td><b>{ambul_card_num}</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Ф.И.О. медицинского работника</td>
            <td><b>{medpersonal_fio}</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Номер телефона медицинского работника</td>
            <td><b>&nbsp;</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Наименование медицинского изделия</td>
            <td><b>{drugmnn_name}</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Количество единиц</td>
            <td><b>{drug_kolvo}</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Подпись медицинского работника</td>
            <td><b>&nbsp;</b></td>
        </tr>
        <tr>
            <td>Личная печать медицинского работника</td>
            <td>М.П.</td>
        </tr>
    </table>
    <br>
    <br>
    <table style="width: 100%;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-bottom: 1px dashed #000;">&nbsp;</td>
        </tr>
    </table>
    <div style="text-align: center;"><sup>(заполняется специалистом аптечной организации)</sup></div>
    <div><b>Отпущено по рецепту:</b></div>
    <br>
    <div>Дата отпуска "_____" _____________ _______ г.</div>
    <table style="width: 100%;" cellpadding="5px">
        <tr style="border-bottom: 1px solid #000;">
            <td style="width: 40%;">
                Наименование медицинского изделия
            </td>
            <td style="width: 60%;">
                <b>&nbsp;</b>
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Количество единиц</td>
            <td>&nbsp;</td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>На общую сумму</td>
            <td>&nbsp;</td>
        </tr>
    </table>
    <table style="width: 100%;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-bottom: 1px dashed #000;">&nbsp;</td>
        </tr>
    </table>
    <div style="text-align: center;"><sup>(линия отрыва)</sup></div>
    <div>
        Корешок рецепта
        Серия <ins>{recept_ser}</ins>&nbsp;&nbsp;&nbsp;
        N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <ins>{recept_num}</ins>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        Дата выдачи от <ins>{recept_date_1}{recept_date_2}.{recept_date_3}{recept_date_4}.{recept_date_5}{recept_date_6}{recept_date_7}{recept_date_8}г.</ins>
    </div>
    <br>
    <table style="width: 100%;" cellpadding="5px">
        <tr style="border-bottom: 1px solid #000;">
            <td style="width: 40%;">
                Наименование медицинского изделия
            </td>
            <td style="width: 60%;">
                <b>&nbsp;</b>
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Количество единиц</td>
            <td>&nbsp;</td>
        </tr>
    </table>

</td>

<td style="width: 2%;"></td>
<!--------------------------------------------------------------------------------------------------------------------------------------------------->
<td style="width: 49%;">

    <div style="text-align: right;">
        Утверждена <br>
        Приказом Министерства здравоохранения <br>
        Российской Федерации <br>
        от 20 декабря 2012 г. N 1181н <br>
        Форма N 1-МИ
    </div>
    <div style="text-align: center; font-size: 15px;"><b>РЕЦЕПТУРНЫЙ БЛАНК НА МЕДИЦИНСКИЕ ИЗДЕЛИЯ</b></div>
    <br>
    <div>Наименование медицинской организации: <b>{lpu_name}</b></div>
    <div>Штамп &nbsp;</div>
    <div>ОГРН {lpu_ogrn}</div>
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width: 42%; border: 1px solid #000; vertical-align: top; padding-left: 5px;">
                <div style="text-align: left;">Источник финансирования при льготном обеспечении (нужное подчеркнуть):</div>
                <div><span style="border-bottom: {recept_finance_1};">1. федеральный бюджет</span></div>
                <div><span style="border-bottom: {recept_finance_2};">2. бюджет субъекта Российской Федерации</span></div>
                <br>
            </td>
            <td style="width: 28%; border: 1px solid #000; vertical-align: top;">
                <div style="text-align: left;">Процент оплаты пациентом:</div>
                <div style="text-align: left;"><span style="border-bottom: {recept_discount_000};">1. 100%</span></div>
                <div style="text-align: left;"><span style="border-bottom: {recept_discount_mi1};">2. бесплатно</span></div>
            </td>
            <td style="width: 30%; border: 1px solid #000; vertical-align: top;">
                <div style="text-align: left;">Рецепт действителен в течение</div>
                <div style="margin-left:0px">
                    <div><span style="border-bottom: {recept_valid_1};">1 месяца</span></div>
                    <div><span style="border-bottom: {recept_valid_2};">3 месяцев</span></div>
                </div>
            </td>
        </tr></table>
    <br>
    <div>
        РЕЦЕПТ
        Серия <ins>{recept_ser}</ins>&nbsp;&nbsp;&nbsp;
        N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <ins>{recept_num}</ins>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        Дата выдачи от <ins>{recept_date_1}{recept_date_2}.{recept_date_3}{recept_date_4}.{recept_date_5}{recept_date_6}{recept_date_7}{recept_date_8}г.</ins>
    </div>
    <br>
    <center>
        <table style="height:20px; width: 320px;">
            <tr>
                <td width="20px" height="20px" style="border: 1px solid #000;"></td>
                <td width="300px" height="20px" style="vertical-align:middle;">&nbsp;&nbsp;"Пациенту с хроническим заболеванием"</td>
            </tr>
        </table>
    </center>
    <table style="width: 100%;" cellpadding="5px">
        <tr style="border-bottom: 1px solid #000;">
            <td style="width: 40%;">
                Ф.И.О. пациента
            </td>
            <td style="width: 60%;">
                <b>{person_fio}</b>
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Дата рождения от</td>
            <td><b>{person_birthday_1}{person_birthday_2}.{person_birthday_3}{person_birthday_4}.{person_birthday_5}{person_birthday_6}{person_birthday_7}{person_birthday_8}г.</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Номер страхового медицинского полиса</td>
            <td><b>ОС {polis_ser_num}, СНИЛС:{person_snils}</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Номер медицинской карты пациента</td>
            <td><b>{ambul_card_num}</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Ф.И.О. медицинского работника</td>
            <td><b>{medpersonal_fio}</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Номер телефона медицинского работника</td>
            <td><b>&nbsp;</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Наименование медицинского изделия</td>
            <td><b>{drugmnn_name}</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Количество единиц</td>
            <td><b>{drug_kolvo}</b></td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Подпись медицинского работника</td>
            <td><b>&nbsp;</b></td>
        </tr>
        <tr>
            <td>Личная печать медицинского работника</td>
            <td>М.П.</td>
        </tr>
    </table>
    <br>
    <br>
    <table style="width: 100%;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-bottom: 1px dashed #000;">&nbsp;</td>
        </tr>
    </table>
    <div style="text-align: center;"><sup>(заполняется специалистом аптечной организации)</sup></div>
    <div><b>Отпущено по рецепту:</b></div>
    <br>
    <div>Дата отпуска "_____" _____________ _______ г.</div>
    <table style="width: 100%;" cellpadding="5px">
        <tr style="border-bottom: 1px solid #000;">
            <td style="width: 40%;">
                Наименование медицинского изделия
            </td>
            <td style="width: 60%;">
                <b>&nbsp;</b>
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Количество единиц</td>
            <td>&nbsp;</td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>На общую сумму</td>
            <td>&nbsp;</td>
        </tr>
    </table>
    <table style="width: 100%;" cellpadding="0" cellspacing="0">
        <tr>
            <td style="border-bottom: 1px dashed #000;">&nbsp;</td>
        </tr>
    </table>
    <div style="text-align: center;"><sup>(линия отрыва)</sup></div>
    <div>
        Корешок рецепта
        Серия <ins>{recept_ser}</ins>&nbsp;&nbsp;&nbsp;
        N&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <ins>{recept_num}</ins>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        Дата выдачи от <ins>{recept_date_1}{recept_date_2}.{recept_date_3}{recept_date_4}.{recept_date_5}{recept_date_6}{recept_date_7}{recept_date_8}г.</ins>
    </div>
    <br>
    <table style="width: 100%;" cellpadding="5px">
        <tr style="border-bottom: 1px solid #000;">
            <td style="width: 40%;">
                Наименование медицинского изделия
            </td>
            <td style="width: 60%;">
                <b>&nbsp;</b>
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #000;">
            <td>Количество единиц</td>
            <td>&nbsp;</td>
        </tr>
    </table>
    {farm_info}
</td>
</tr>

</table>

</body>

</html>