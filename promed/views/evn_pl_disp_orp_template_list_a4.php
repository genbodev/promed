<html xmlns="http://www.w3.org/1999/html">
<head>
    <title>{EvnPLTemplateTitle}</title>
    <style type="text/css">
        @page port { size: portrait }
        @page land { size: landscape }
        body { margin: 0px; padding: 30px; font-family: times, tahoma, verdana; font-size: 14px;}
        table { border-collapse: collapse; width: 100%}
        span, div, td { font-family: times, tahoma, verdana; font-size: 16px; }
        div.selector { display:none; }
        th { text-align: center; font-size: 14px; border-collapse: collapse; border: 1px solid black; }
        h1 { text-align: center; font-size: 20px; font-weight: bold}
        .printtable {border: 1px solid black}
        .printtable th {text-align: center; padding: 3px; font-weight: normal}
        .printtable td {text-align: center; padding: 3px; border: 1px solid black}
    </style>

    <style type="text/css" media="print">
        @page port { size: portrait }
        @page land { size: landscape }
        div.selector { display:none; }
        body { margin: 0px; padding: 10px; font-family: times, tahoma, verdana; font-size: 14px;}
        table {  border-collapse: collapse; width: 100%}
        span, div, td { font-family: times, tahoma, verdana; font-size: 14px; }
        td { vertical-align: bottom; }
        th { text-align: center; font-size: 14px; border-collapse: collapse; border: 1px solid black; }
        h1 { text-align: center; font-size: 20px; font-weight: bold}
        .cell { border: 1px solid #000; border-collapse: collapse; vertical-align: top; }
        .pg {page-break-before: always}
        .printtable {border: 1px solid black}
        .printtable th {text-align: center; padding: 3px; font-weight: normal}
        .printtable td {text-align: center; padding: 3px; border: 1px solid black}
    </style>

    <script type="text/javascript">
        function activateSelectors() {
            var arr = document.getElementsByTagName("div");
            for(var i = 0; i < arr.length; i++) {
                if (arr[i].className == "selector") {
                    var span_arr = arr[i].parentNode.getElementsByTagName("span");
                    for(var j = 0; j < span_arr.length; j++) {
                        if (span_arr[j].className == "val_" + arr[i].innerHTML) span_arr[j].style.textDecoration = "underline";
                    }
                }
                if (arr[i].className == "show_selector") {
                    var span_arr = arr[i].parentNode.getElementsByTagName("span");
                    for(var j = 0; j < span_arr.length; j++) {
                        if (span_arr[j].className == "val_" + arr[i].innerHTML)
                            span_arr[j].style.display = "inline";
                        else
                            span_arr[j].style.display = "none";
                    }
                }
                if (arr[i].className == "single_selector") {
                    var span_arr = arr[i].getElementsByTagName("span");
                    var empty = true;
                    for(var j = 0; j < span_arr.length; j++) {
                        if (!empty)
                            span_arr[j].style.display = 'none';
                        if(span_arr[j].innerHTML.replace('&nbsp;','') != '')
                            empty = false;
                    }
                }
                if (arr[i].id.substring(0,18) == 'broken_text_start_') {
                    var obj_arr = arr[i].id.split('_');
                    var start_obj = arr[i];
                    var end_obj = document.getElementById('broken_text_end_'+obj_arr[3]);
                    var max_len = obj_arr[4];
                    var words = start_obj.innerHTML.split(' ');
                    var cont = false;
                    start_obj.innerHTML = '';
                    end_obj.innerHTML = '';

                    for (var j = 0; j < words.length; j++) {
                        if (start_obj.innerHTML.length + words[j].length + 1 <= max_len && !cont)
                            start_obj.innerHTML += words[j] + ' ';
                        else
                            cont = true;
                        if (cont)
                            end_obj.innerHTML += words[j] + ' ';
                    }
                }
            }
        }
    </script>
</head>
    <body>
        <table>
            <tr>
                <td style="width: 100%;" align="right"><b>
                    Приложение №2</br>
                    к приказу Министерства здравоохранения</br>
                    Российской Федерации</br>
                    от 15 февраля 2013 г. № 72н</b>
                </td>
            </tr>
        </table>
        <br>
        <div align="center" style="font-size: 18px;"> Медицинская документация </div>
        <br>
        <div align="center" style="font-size: 18px;">Учетная форма № 030-Д/с/у-13</div>
        <br>
        <table width='100%'>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                1. Полное наименование стационарного учреждения
            </td></tr>
            <tr><td style="padding-top: 3px; border-bottom: 1px solid; text-align: center;"> {OrgStac_Name}</td> </tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                1.1. Прежнее наименование (в случае его изменения)
            </td></tr>
            <tr><td style="padding-top: 3px; border-bottom: 1px solid; text-align: center;"> &nbsp;</td> </tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                1.2. Ведомственная принадлежность: <u>органы здравоохранения</u>, образования,
                социальной защиты, другое (нужное подчеркнуть).
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                1.3. Юридический адрес стационарного учреждения:
            </td></tr>
            <tr><td style="padding-top: 3px; border-bottom: 1px solid; text-align: center;"> {Lpu_Address}</td> </tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                2. Фамилия, имя, отчество несовершеннолетнего:
            </td></tr>
            <tr><td style="padding-top: 3px; border-bottom: 1px solid; text-align: center;"> {Person_FIO}</td> </tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                2.1. Пол: <span class= "val_1">муж.</span>/<span class="val_2">жен.</span> (нужное подчеркнуть)<div class="selector">{Sex_id}</div>
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                2.2. Дата рождения: {Person_BirthDay}
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                2.3. Категория   учета  ребенка,  находящегося  в  тяжелой  жизненной
                ситуации:
                    <span class="val_2"> ребенок-сирота; </span>
                    <span class="val_3">  ребенок,  оставшийся  без  попечения родителей;</span>
                    <span class="val_4"> ребенок,  находящийся  в  трудной жизненной ситуации,</span>
                    <span class="val_1"> нет категории </span>
                    <div class="selector">{CategoryChildType_Code}</div>
                   (нужное подчеркнуть).
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                2.4.  На  момент  проведения  диспансеризации  находится
                    <span class="val_1">в стационарном учреждении,</span>
                    <span class="val_2">под опекой,</span>
                    <span class="val_3">попечительством,</span>
                    <span class="val_4">передан в приемную семью,</span>
                    <span class="val_5">передан в патронатную семью,</span>
                    <span class="val_6">усыновлен (удочерена),</span>
                    <span class="val_7">другое</span> (нужное подчеркнуть).
                    <div class="selector">{ChildStatusType_Code}</div>
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                3. Полис обязательного медицинского страхования:
            </td></tr>
            <tr><td style="padding-top: 3px; padding-left: 25px;"> серия <u>{Polis_Ser}</u> № <u>{Polis_Num}</u> </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                Страховая медицинская организация: <u>{OrgSMO_Nick}</u>
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                Страховой номер индивидуального лицевого счета: <u>{Person_Snils}</u>
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                4. Дата поступления в стационарное учреждение: <u>{PersonDispOrp_setDate}</u>
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                5.  Причина выбытия из стационарного учреждения:
                <?
                    $DisposalCause = array(
                        'опека',
                        'попечительство',
                        'усыновление  (удочерение)',
                        'передан в приемную семью',
                        'передан в патронатную семью',
                        'выбыл  в другое стационарное учреждение',
                        'выбыл по возрасту',
                        'смерть',
                        'другое'
                    );
                    foreach ($DisposalCause as $k => &$v) {
                        if (++$k==$DisposalCause_id) {
                            $v = "<u>{$v}</u>";
                        }
                    }
                    echo join(', ', $DisposalCause);
                ?>
                (нужное подчеркнуть).
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                5.1. Дата выбытия: <u>{PersonDispOrp_DisposDate}</u>
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                6. Отсутствует на момент проведения диспансеризации: _________________________________________ (указать причину)
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                7. Адрес места жительства:
            </td></tr>
            <tr><td style="padding-top: 3px; border-bottom: 1px solid; text-align: center;">&nbsp;{Person_Address}</td> </tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                8.    Полное    наименование    медицинской    организации,   выбранной
                несовершеннолетним  (его  родителем  или  иным законным представителем) для
                получения первичной медико-санитарной помощи:
            </td></tr>
            <tr><td style="padding-top: 3px; border-bottom: 1px solid; text-align: center;">&nbsp;</td> </tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                9.     Юридический    адрес    медицинской    организации,    выбранной
                несовершеннолетним  (его  родителем  или  иным законным представителем) для
                получения первичной медико-санитарной помощи:
            </td></tr>
            <tr><td style="padding-top: 3px; border-bottom: 1px solid; text-align: center;">&nbsp;</td> </tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                10. Дата начала диспансеризации: <u>{EvnPLDispOrp_setDate}</u>
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                11.  Полное  наименование  и юридический адрес медицинской организации,
                проводившей диспансеризацию:
            </td></tr>
            <tr><td style="padding-top: 3px; border-bottom: 1px solid; text-align: center;">{Lpu_Name}</td> </tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                12.   Оценка   физического   развития   с  учетом  возраста  на  момент
                диспансеризации: <u>&nbsp;&nbsp;{days_diff}&nbsp;&nbsp;</u> (число дней) <u>&nbsp;&nbsp;{month_diff}&nbsp;&nbsp;</u> (месяцев) <u>&nbsp;&nbsp;{year_diff}&nbsp;&nbsp;</u> лет.
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                12.1. Для детей в возрасте 0 - 4 лет: масса (кг) <u>{AssesmentHealth_Weight_0}</u>; рост (см)
                <u>{AssesmentHealth_Height_0}</u>; окружность головы (см) <u>{AssessmentHealth_Head_0}</u>; физическое развитие
                {condition_0} ({weight_condition_0}, {height_condition_0} - нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                12.2. Для детей в возрасте 5 - 17 лет включительно: масса (кг) <u>{AssesmentHealth_Weight_1}</u>; рост (см)
                <u>{AssesmentHealth_Height_1}</u>; окружность головы (см) <u>{AssessmentHealth_Head_1}</u>; физическое развитие
                {condition_1} ({weight_condition_1}, {height_condition_1} - нужное подчеркнуть).
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                13. Оценка психического развития (состояния):
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                13.1. Для детей в возрасте 0 - 4 лет:
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                познавательная функция (возраст развития) <u>{AssessmentHealth_Gnostic}</u>;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                моторная функция (возраст развития) <u>{AssessmentHealth_Motion}</u>;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                эмоциональная     и  социальная  (контакт с окружающим  миром)  функции
                (возраст развития) <u>{AssessmentHealth_Social}</u>;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                предречевое и речевое развитие (возраст развития) <u>{AssessmentHealth_Speech}</u>.
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                13.2. Для детей в возрасте 5 - 17 лет:
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                13.2.1. Психомоторная сфера: (<span class="val_1">норма</span>, <span class="val_2">отклонение</span>) <div class="selector">{NormaDisturbanceType_id}</div> (нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                13.2.2. Интеллект: (<span class="val_1">норма</span>, <span class="val_2">отклонение</span>) <div class="selector">{NormaDisturbanceType_uid}</div> (нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                13.2.3.  Эмоционально-вегетативная  сфера:  (<span class="val_1">норма</span>, <span class="val_2">отклонение</span>) <div class="selector">{NormaDisturbanceType_eid}</div> (нужное подчеркнуть).
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                14. Оценка полового развития (с 10 лет):
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                14.1. Половая формула мальчика: P <u>{AssessmentHealth_P_b}</u> Ax <u>{AssessmentHealth_Ax_b}</u> Fa <u>{AssessmentHealth_Fa_b}</u>.
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                14.2. Половая формула девочки:  P <u>{AssessmentHealth_P_g}</u> Ax <u>{AssessmentHealth_Ax_g}</u> Ma <u>{AssessmentHealth_Ma_g}</u> Me <u>{AssessmentHealth_Me_g}</u>;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                характеристика менструальной функции: menarhe (лет, месяцев) <u>{AssessmentHealth_Years_g}</u>, <u>{AssessmentHealth_Month_g}</u>;
                menses  (характеристика):  {IsRegular},  {IsIrregular},  {IsAbundant}, {IsModerate},
                {IsScanty}, {IsPainful} и {IsPainless} (нужное подчеркнуть).
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                15. Состояние здоровья до проведения диспансеризации:
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                15.1. Практически здоров _____________________________________________________ (код по МКБ <1>).
            </td></tr>
            {before_disp_0}{before_disp_1}{before_disp_2}{before_disp_3}{before_disp_4}
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                15.9.   Группа   состояния   здоровья:
                <span class="val_1"> I,</span>
                <span class="val_2"> II,</span>
                <span class="val_3"> III,</span>
                <span class="val_4"> IV,</span>
                <span class="val_5"> V</span>
                <div class="selector">{HealthKind_id}</div>
                (нужное подчеркнуть).
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16. Состояние здоровья по результатам проведения диспансеризации:
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.1. Практически здоров _____________________________________________________ (код по МКБ <1>).
            </td></tr>
            {after_disp_0}{after_disp_1}{after_disp_2}{after_disp_3}{after_disp_4}
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.7. Инвалидность:
                <span class="val_2"> да </span>,
                <span class="val_1"> нет </span>
                <div class="selector">{Is_Invalid}</div>
                (нужное подчеркнуть);
                если "да":
                <span class="val_3"> с    рождения,</span>
                <span class="val_4">приобретенная   (нужное   подчеркнуть);</span>
                <div class="selector">{InvalidType_id}</div>
                установлена впервые (дата) <u>{AssessmentHealth_setDT}</u>;
                дата последнего освидетельствования <u>{ssessmentHealth_reExamDT}</u>.
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.7.1. Заболевания, обусловившие возникновение инвалидности:
                (некоторые  инфекционные  и  паразитарные, из них:
                <span class="val_1">туберкулез</span>,
                <span class="val_2">сифилис,</span>
                <span class="val_3">ВИЧ-инфекция;</span>
                <span class="val_4">новообразования;</span>
                <span class="val_5">болезни  крови,  кроветворных  органов  и отдельные  нарушения,  вовлекающие  иммунный  механизм;</span>
                <span class="val_24">болезни эндокринной системы,  расстройства питания и нарушения обмена веществ,</span>
                из них:
                <span class="val_6">сахарный диабет;</span>
                <span class="val_7">психические  расстройства  и  расстройства  поведения, в том числе умственная  отсталость;</span>
                <span class="val_25">болезни  нервной  системы,</span>
                из  них:
                <span class="val_8">церебральный паралич,</span>
                <span class="val_9">другие  паралитические синдромы;</span>
                <span class="val_10">болезни глаза и его придаточного аппарата;</span>
                <span class="val_11">болезни   уха   и   сосцевидного   отростка;</span>
                <span class="val_12">болезни  системы кровообращения;</span>
                <span class="val_26">болезни  органов  дыхания,</span>
                из  них:
                <span class="val_13">астма, астматический статус;</span>
                <span class="val_14">болезни  органов  пищеварения;</span>
                <span class="val_15">болезни кожи и подкожной клетчатки;</span>
                <span class="val_16">болезни   костно-мышечной    системы    и   соединительной  ткани;</span>
                <span class="val_17">болезни  мочеполовой  системы;</span>
                <span class="val_18">отдельные  состояния,  возникающие  в  перинатальном периоде;</span>
                <span class="val_27">врожденные  аномалии,  </span>
                из них:
                <span class="val_19">аномалии нервной системы, </span>
                <span class="val_20">аномалии системы кровообращения, </span>
                <span class="val_21">аномалии опорно-двигательного аппарата; </span>
                <span class="val_22">последствия травм,   отравлений   и   других   воздействий   внешних   причин) </span>
                <div class="selector">{InvalidDiagType_id}</div>
                (нужное подчеркнуть).
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.7.2. Виды нарушений в состоянии здоровья:
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                {IsMental};
                {IsOtherPsych};
                {IsLanguage};
                {IsVestibular};
                {IsVisual};
                {IsMeals};
                {IsMotor};
                {IsDeform};
                {IsGeneral}
                (нужное подчеркнуть)
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.7.3.  Индивидуальная  программа  реабилитации ребенка инвалида:
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                дата назначения: <u>{AssessmentHealth_ReabDT}</u>
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                выполнение  на  момент диспансеризации:
                <span class="val_1">полностью</span>,
                <span class="val_2">частично</span>,
                <span class="val_3">начато</span>,
                <span class="val_4">не выполнена</span>
                <div class="selector">{RehabilitEndType_id}</div>
                (нужное подчеркнуть).
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.8.   Группа   состояния   здоровья:
                <span class="val_1"> I,</span>
                <span class="val_2"> II,</span>
                <span class="val_3"> III,</span>
                <span class="val_4"> IV,</span>
                <span class="val_5"> V</span>
                <div class="selector">{HealthKind_id}</div>
                (нужное подчеркнуть).
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                16.9. Проведение профилактических прививок:
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px;">
                <span class="val_1">привит по возрасту;</span>
                не привит по медицинским показаниям:
                <span class="val_2">полностью,</span>
                <span class="val_3">частично;</span>
                не  привит  по другим причинам:
                <span class="val_4">полностью,</span>
                <span class="val_5">частично;</span>
                <span class="val_6">нуждается в проведении  вакцинации  (ревакцинации)</span>
                <div class="selector">{ProfVaccinType_id}</div>
                с  указанием  наименования прививки
                (нужное подчеркнуть):
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                16.10. Рекомендации по формированию здорового образа жизни, режиму дня,
                питанию,  физическому  развитию,  иммунопрофилактике,  занятиям  физической
                культурой:
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
				{AssessmentHealth_HealthRecom}
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                16.11.  Рекомендации  по диспансерному наблюдению, лечению, медицинской
                реабилитации  и  санаторно-курортному  лечению с указанием диагноза (код по
                МКБ), вида медицинской организации и специальности (должности) врача:
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
				{AssessmentHealth_DispRecom}
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>
            <tr><td style="padding-left: 25px; padding-top: 3px; border-bottom: 1px solid;">
                &nbsp;
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                17. Перечень и даты проведения осмотров врачами-специалистами: <u>{vizitDispOrp13_string}</u>
            </td></tr>

            <tr><td style="padding-left: 25px; padding-top: 3px;">
                18. Перечень, даты и результаты проведения исследований:: <u>{uslugaDispOrp_string}</u>
            </td></tr>
        </table>
        <br><br>
        <table>
            <tr>
                <td style="padding-top: 3px; width: 12%;">Врач</td>
                <td style="padding-top: 3px; width: 12%;">&nbsp;</td>

                <td style="padding-top: 3px; width: 23%;border-bottom: 1px solid;"></td>
                <td style="padding-top: 3px; width: 5%;">&nbsp;</td>
                <td style="padding-top: 3px; width: 40%;border-bottom: 1px solid;"></td>
            </tr>
            <tr>
                <td style="padding-top: 3px; width: 12%;">&nbsp;</td>
                <td style="padding-top: 3px; width: 12%;">&nbsp;</td>

                <td style="padding-top: 3px; width: 23%; text-align: center;"><sup>(подпись)</sup></td>
                <td style="padding-top: 3px; width: 5%;">&nbsp;</td>
                <td style="padding-top: 3px; width: 40%; text-align: center;"><sup>(фамилия и инициалы)</sup></td>
            </tr>
            <tr>
                <td style="padding-top: 3px; width: 12%;">Руководитель медицинской организации</td>
                <td style="padding-top: 3px; width: 12%;">&nbsp;</td>

                <td style="padding-top: 3px; width: 23%;border-bottom: 1px solid;"></td>
                <td style="padding-top: 3px; width: 5%;">&nbsp;</td>
                <td style="padding-top: 3px; width: 40%;border-bottom: 1px solid;"></td>
            </tr>
            <tr>
                <td style="padding-top: 3px; width: 12%;">&nbsp;</td>
                <td style="padding-top: 3px; width: 12%;">&nbsp;</td>

                <td style="padding-top: 3px; width: 23%; text-align: center;"><sup>(подпись)</sup></td>
                <td style="padding-top: 3px; width: 5%;">&nbsp;</td>
                <td style="padding-top: 3px; width: 40%; text-align: center;"><sup>(фамилия и инициалы)</sup></td>
            </tr>
            <tr>
                <td style="padding-top: 3px; width: 12%;">&nbsp;</td>
                <td style="padding-top: 3px; width: 12%;">&nbsp;</td>

                <td style="padding-top: 3px; width: 23%;">&nbsp;</td>
                <td style="padding-top: 3px; width: 5%;">&nbsp;</td>
                <td style="padding-top: 3px; width: 40%;">&nbsp;</td>
            </tr>
            <tr>
                <td style="padding-top: 3px; width: 12%;">Дата заполнения </td>
                <td style="padding-top: 3px; width: 12%;">&nbsp;</td>

                <td style="padding-top: 3px; width: 23%;text-align: center;">"_________" ________________________ 20___ г.</td>
                <td style="padding-top: 3px; width: 5%;">&nbsp;</td>
                <td style="padding-top: 3px; width: 40%;text-align: center;">М.П.</td>
            </tr>
        </table>
    <script type="text/javascript">activateSelectors();</script>
    </body>
</html>