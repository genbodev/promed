<html>
    <head>
        <title>58-ДТП-1/у - {Person_Fio}</title>
        <style type="text/css">
            body { margin: 0px; padding: 0 0 0 4em; }
            table { border-collapse: collapse; }
            span, div, td { font-family: times, tahoma, verdana; font-size: 10pt; }
            .sq { border:#000 solid 1px; font-family: arial, verdana; padding: 0 3px;}
            .small_sub { font-size: 8pt }
            .header1 { width: 100% }
            .header1 .one { width: 50%; }
            .header1 .two { width: 50%; font-size: 14pt; padding-left: 1em }
            .header2 { width: 100%; margin-top: 5em; }
            .header2 .one { width: 50%; vertical-align: middle; text-align: center; }
            .header2 .two { width: 50%; padding-left: 1em; }
            .header2 .two div { text-align:center; }
            .header3 { text-align: center; font-weight: bold; padding-left: 1em; padding-top: 1em; }
            .main_table { width: 100%; margin-top: 1em; }
            .main_table td { padding-top: 0.4em; }
            .main_table .one { width: 30px; vertical-align: top; text-align: left; }
            .footer { width: 100%; margin-top: 3em; }
            .footer .one { width: 50%; vertical-align: top; text-align: center; }
            .footer .two { width: 20%; vertical-align: top; text-align: center; }
            .footer .three { width: 30%; vertical-align: top; text-align: center; white-space: nowrap; }
        </style>
    </head>

    <body>

        <table class="header1">
            <tr>
                <td class="one"></td>
                <td class="two">Приложение № 1<br />
                    к приказу Министерства<br />
                    здравоохранения и социального<br />
                    развития Российской Федерации<br />
                    от 26 января 2009 г. № 18</td>
            </tr>
        </table>

        <table class="header2"><tr>
                <td class="one">
                    {Lpu_Name}<br />
                    <span class="small_sub">(наименование медицинской организации)</span><br />
                    {Lpu_UAddress}, {Lpu_Phone}<br />
                    <span class="small_sub">(адрес, телефон)</span>
                </td>
                <td class="two">
                    <div>Учетная документация<br /><br />
                        <b>Форма № 58-ДТП-1/у</b><br /><br /></div>
                    Утверждена приказом<br />
                    Минздравсоцразвития России<br />
                    от&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;№<br /><br />
                    Представляется в орган внутренних дел<br />
                    Российской Федерации<br />
                    по месту нахождения медицинской организации
                </td>
            </tr>
        </table>

        <div class="header3">
            <div>Извещение<br />о раненом, скончавшемся в течение 30 суток после<br /> дорожно-транспортного происшествия</div>
        </div>

        <table class="main_table">
            <tr>
                <td class="one">1.</td>
                <td>Ф.И.О. скончавшегося: {Person_Fio}</td>
            </tr>
            <tr>
                <td class="one">2.</td>
                <td>Пол: М 1<span class="sq">{Sex_Name_Male}</span>, Ж 2<span class="sq">{Sex_Name_Female}</span></td>
            </tr>
            <tr>
                <td class="one">3.</td>
                <td>Дата рождения: {Person_Birthday}</td>
            </tr>
            <tr>
                <td class="one">4.</td>
                <td>Дата дорожно-транспортного происшествия: {EvnDtpDeath_DtpDate}</td>
            </tr>
            <tr>
                <td class="one">5.</td>
                <td>Дата поступления в стационар: {EvnDtpDeath_HospDate}</td>
            </tr>
            <tr>
                <td class="one">6.</td>
                <td>Диагноз при поступлении в стационар:<br />{DiagP_Name}, код по МКБ-10: {DiagP_Code}</td>
            </tr>
            <tr>
                <td class="one">7.</td>
                <td>Дата смерти: {EvnDtpDeath_DeathDate}</td>
            </tr>
            <tr>
                <td class="one">8.</td>
                <td>Непосредственная причина смерти:<br />{DiagI_Name}, код внешней причины по МКБ-10: {DiagI_Code}</td>
            </tr>
            <tr>
                <td class="one">9.</td>
                <td>Основная причина смерти:<br />{DiagM_Name}, код внешней причины по МКБ-10: {DiagM_Code}</td>
            </tr>
            <tr>
                <td class="one">10.</td>
                <td>Внешняя причина смерти:<br />{DiagE_Name}, код внешней причины по МКБ-10: {DiagE_Code}</td>
            </tr>
                        <tr>
                <td class="one">11.</td>
                <td>
                    Смерть наступила:<br />
                    в машине скорой помощи &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1 <span class="sq">{inSMP}</span><br />
                    в стационаре: в течение 30 суток после дорожно-транспортного происшествия 2 <span class="sq">{inStac30}</span><br />
                    из них: в течение первых 7 суток после дорожно-транспортного происшествия 3 <span class="sq">{inStac7}</span><br />
                    на дому: в течение 30 суток после дорожно-транспортного происшествия 4 <span class="sq">{atHome30}</span><br />
                    из них: в течение первых 7 суток после дорожно-транспортного происшествия 5 <span class="sq">{atHome7}</span>
                </td>
            </tr>
        </table>

        <table class="footer"><tr>
                <td class="one">
                    {EvnDtpDeath_setDate} г.<br />
                    <span class="small_sub">(дата заполнения извещения)</span><br />
                </td>
                <td class="two">
                    _________________<br />
                    <span class="small_sub">(подпись)</span><br />
                </td>
                <td class="three">
                    {MedPersonal_Fin}, {MedPersonal_Dolgnost}<br />
                    <span class="small_sub">(фамилия, должность медицинского работника,<br />составившего извещение)</span><br />
                </td>
            </tr>
        </table>

    </body>
</html>