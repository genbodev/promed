<html>
<head>
    <title>Паспорт здоровья</title>
    <style type="text/css">
        div.selector { display:none; }
        @page port { size: portrait }
        @page land { size: landscape }
        body { page: land; margin: 0px; padding: 0px; font-size:10pt; }
        .pagetable TD.page { border: 1px none black; width:100%; text-align: center; vertical-align: top; padding: 0em 5em; }
    </style>

    <style type="text/css" media="print">
        @page port { size: portrait }
        @page land { size: landscape }
        body { page: land; margin: 0px; padding: 0px; font-size:10pt; }
        .twopage { page-break-after: auto; }
        .pagetable TD.page { border: 1px none black; width:75%; text-align: right; vertical-align: top; padding: 0em 0em; }
    </style>


    <style>
        .pagetable { width:100%;  border-collapse: collapse; }
        .pagetable TD { font-size:15pt; font-family:"Times New Roman",Georgia,Serif; }
        .pagetable TD.page_separetor { display: block; }
        .divlft { text-align: left; }
        .divrgt { text-align: right; }
        .trhidden { display: none; }
        .pagenumber { margin-bottom: 1em; }
        .chaptername {font-size: 15pt; font-weight: bolder;}
        .simpletable TD { border: 1px solid black; }

        TABLE.title { width: 75%; margin-left: 5em; }
        TABLE.title TD { font-size: 12pt; font-weight: bolder; }
        TD.title { font-size: 12pt; }

        .struc { border-collapse: collapse; width:100%; }
        .struc TD { border: 1px none red; vertical-align: top; padding: 0em 0.5em; padding-top: 0.4em; text-align: left; }
        .struc TD.underline { border-bottom: 1px solid black; text-align: center; }
        .struc TD.ct { text-align: center; }
        .struc TD.measure { padding-left: 0em; }
        .struc TD.measure_rt { padding-right: 0em; }
        .struc TD.u_text { padding-top: 0em; }

        .info { border-collapse: collapse;  margin-bottom: 1em; width:100%; }
        .info TD { border: 1px solid black; vertical-align: top; padding: 0em 0.5em; text-align: center; }
        .info TD.header { font-weight: bolder; }
        .info TD.table_header { font-weight: bolder; border-style:none; height:2em; }
        .info TD.measure, .info TD.m { text-align:left; }
        .info TD.y { width: 50px; }
        .info TD.y2 { width: 60px; }

        .measures { border-collapse: collapse;  margin-bottom: 1em; width:100%; }
        .measures TD { border: 1px solid black; vertical-align: top; padding: 0; text-align: center;  font-size: 9pt; }
        .measures TD.ms_name { text-align: left; font-size: 10pt; }
        .measures TD.date { width:62px; }
        .measures TD.s_date { width:58px; }
        .measures TD.value { width:63px; }
        .measures TD.s_value { width:56px; }
        .measures TR.headers TD { font-size: 9pt; }

        .diseases { border-collapse: collapse;  margin-bottom: 1em; width:100%; }
        .diseases TD { border: 1px solid black; vertical-align: top; padding: 0em 0.5em; text-align: center; }
        .diseases TD.text_block { height: 3em; text-align:left; }

        .recommendations { border-collapse: collapse;  margin-bottom: 1em; width:100%; }
        .recommendations TD { border: 1px solid black; vertical-align: top; padding: 0em 0.5em; text-align: center; }
        .recommendations TD.spec_name { text-align: left; vertical-align: middle; height: 4em; }
        .recommendations TD.text_block { text-align: left; vertical-align: top; }

        .conclusion_tbl { border-collapse: collapse; margin-bottom: 2em; width:100%; }
        .conclusion_tbl TD { border: 1px solid black; vertical-align: top; padding: 0em 0.5em; }
        .conclusion_tbl TD.text_block { padding: 0.5em; }
        .conclusion_tbl TD.left_panel { width: 6em; height: 10em; }
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
<table class="pagetable">
    <tr class="1trhidden twopage"> <!-- page 1 -->
        <td class="page">
            <div  style="text-align:right;  padding-left:35%;  margin-top:2em; margin-bottom: 3em;">
                Медицинская документация</br>
                Учетная форма № 125/у-ПЗ</br>
                Утверждена приказом Минздрава России</br>
                от &nbsp;18 июня 2013 года &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;№382н
            </div>
        </td>
    </tr>
    <tr class="1trhidden twopage">
        <td class="page">
            <div class="chaptername" style="text-align: center;"><b>Паспорт здоровья</b></div>

            <table class="struc">
                <tr><td>&nbsp;</td></tr>
                <tr><td class="measure" style="width: 50px;">1.Ф.И.О.</td><td class="underline">{person_surname}&nbsp;{person_firname}&nbsp;{person_secname}</td></tr>
            </table>
            <table class="struc">
                <tr>
                    <td class="measure">2.Пол:  <span class="val_1">М-1</span>;  <span class="val_2">2-Ж</span><div class="selector">{sex_id}</div></td>
                </tr>
            </table>
			<table class="struc">
				<tr><td class="measure" style="width: 600px;">3.Номер страхового полиса обязательного медицинского страхования</td><td class="underline">{polis_ser} {polis_num}</td></tr>
			</table>
            <table class="struc">
                <tr><td class="measure" style="width: 315px;">4.Дата рождения (число, месяц, год)</td><td class="underline">{p_bd_d} {p_bd_m} {p_bd_y}</td></tr>
            </table>
            <table class="struc">
                <tr><td class="measure" style="width: 205px;">
                        <nobr>5.Адрес места жительства (места пребывания):<span class="val_1"> город -1</span>;  <span class="val_2">село - 2</span><div class="selector">{area_type}</div></nobr>
                    </td>
                    <td class="underline">{p_a}</td>
                </tr>
            </table>
            <table class="struc">
                <tr>
                    <td class="measure" style="width: 20px;">ул.</td>
                    <td class="underline">{p_a_st}</td>
                    <td class="measure_rt" style="width: 26px;">дом</td>
                    <td class="underline" style="width: 20px;">{p_a_h}</td>
                    <td class="measure_rt" style="width: 32px;">корп.</td>
                    <td class="underline" style="width: 20px;">{p_a_c}</td>
                    <td class="measure_rt" style="width: 15px;">кв.</td>
                    <td class="underline" style="width: 20px;">{p_a_fl}</td>
                </tr>
            </table>
            <table class="struc">
                <tr><td class="measure" style="width: 130px;"><nobr>6.Контактный телефон</nobr></td><td class="underline">{person_phone}</td></tr>
            </table>

            <table class="struc">
                <tr><td class="measure"><nobr>7. Медицинская организация, в которой гражданин получает первичную медико-санитарную помощь:</nobr></td></tr>
				<tr><td class="underline">{dd_lpu}</td></tr>
                <tr><td class="underline">{l_address}</td></tr>
				<tr><td class="ct"><sup>(полное наименование, адрес местонахождения)</sup></td></tr>
            </table>
            <table class="struc">
                <tr><td class="measure" style="width: 145px;"><nobr>8. Медицинская организация, в которой гражданину выдан паспорт здоровья:</nobr></td></tr>
                <tr><td class="underline" style="width: 400px">{dd_lpu}</td></tr>
                <tr><td class="underline" style="width: 400px">{l_address}</td></tr>
                <tr><td class="ct"><sup>(полное наименование, адрес местонахождения)</sup></td></tr>
            </table>
            <table class="struc">
				<tr><td class="measure"style="width: 425px;">9.Медицинская карта амбулаторного больного №</td><td class="underline">{personcard_code}</td></tr>
            </table>


            <table class="struc">
                <tr><td class="measure"><nobr>10. Установленные заболевания:<sup>1</sup></nobr></td></tr>
                <tr><td>
					<table class="info">

                    	<tr><td style="width: 60%;">Диагноз</td><td>Код МКБ-10</td><td>Дата постановки диагноза</td></tr>
                    	<tr><td class="m" style="width: 60%;">{Diag_Name_0}</td><td class="m">{Diag_Code_0}</td><td class="m">{Diag_date_0}</td></tr>
                        <tr><td class="m" style="width: 60%;">{Diag_Name_1}</td><td class="m">{Diag_Code_1}</td><td class="m">{Diag_date_1}</td></tr>
                        <tr><td class="m" style="width: 60%;">{Diag_Name_2}</td><td class="m">{Diag_Code_2}</td><td class="m">{Diag_date_2}</td></tr>
                        <tr><td class="m" style="width: 60%;">{Diag_Name_3}</td><td class="m">{Diag_Code_3}</td><td class="m">{Diag_date_3}</td></tr>
						<tr><td class="m" style="width: 60%;">{Diag_Name_4}</td><td class="m">{Diag_Code_4}</td><td class="m">{Diag_date_4}</td></tr>
                        <tr><td class="m" style="width: 60%;">{Diag_Name_5}</td><td class="m">{Diag_Code_5}</td><td class="m">{Diag_date_5}</td></tr>
                        <tr><td class="m" style="width: 60%;">{Diag_Name_6}</td><td class="m">{Diag_Code_6}</td><td class="m">{Diag_date_6}</td></tr>
                        <tr><td class="m" style="width: 60%;">{Diag_Name_7}</td><td class="m">{Diag_Code_7}</td><td class="m">{Diag_date_7}</td></tr>
                        <tr><td class="m" style="width: 60%;">{Diag_Name_8}</td><td class="m">{Diag_Code_8}</td><td class="m">{Diag_date_8}</td></tr>
                        <tr><td class="m" style="width: 60%;">{Diag_Name_9}</td><td class="m">{Diag_Code_9}</td><td class="m">{Diag_date_9}</td></tr>
                    	<tr>
	                        <td colspan="3" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
    	                        <sup>1</sup>Заполняется по результатам диспансеризации, профилактических медицинских осмотров, а также по данным медицинской карты амбулаторного больного.
        	                </td>
            	        </tr>
                	</table>
				</td></tr>
			</table>
			<br><br><br><br><br><br>
            <table class = "struc">
                <tr><td class="measure">11. Факторы риска развития хронических неинфекционных заболеваний, являющихся основной причиной
					инвалидности и преждевременной смертности населения Российской Федерации (далее - хронические неинфекционные заболевания): <sup>**</sup></td></tr>
				<tr><td>
					<table class="info">
                        <tr><td rowspan="2" style="width: 200px;">Факторы риска развития заболеваний развития хронических неинфекционных заболеваний</td><td colspan="5">Дата проведения диспансеризации (профилактического медицинского осмотра)</td></tr>
                        <tr>
							<td class="y">{dd_date_1}</td>
                            <td class="y">{dd_date_2}</td>
                            <td class="y">{dd_date_3}</td>
                            <td class="y">{dd_date_4}</td>
                            <td class="y">{dd_date_5}</td>
						</tr>
                        <tr><td class="m">Рост(см)</td>
							<td>{person_height_1}</td>
                            <td>{person_height_2}</td>
                            <td>{person_height_3}</td>
                            <td>{person_height_4}</td>
                            <td>{person_height_5}</td>
						</tr>
                        <tr>
							<td class="m">Вес(кг)</td>
							<td>{person_weight_1}</td>
                            <td>{person_weight_2}</td>
                            <td>{person_weight_3}</td>
                            <td>{person_weight_4}</td>
                            <td>{person_weight_5}</td>
						</tr>
                        <tr>
							<td class="m">Индекс массы тела<sup>2</sup></td>
							<td>{body_mass_index_1}</td>
                            <td>{body_mass_index_2}</td>
                            <td>{body_mass_index_3}</td>
                            <td>{body_mass_index_4}</td>
                            <td>{body_mass_index_5}</td>
						</tr>
                        <tr>
							<td class="m">Избыточная масса тела (ожирение)(есть, нет)</td>
							<td>{risk_overweight_1}</td>
                            <td>{risk_overweight_2}</td>
                            <td>{risk_overweight_3}</td>
                            <td>{risk_overweight_4}</td>
                            <td>{risk_overweight_5}</td>
						</tr>
                        <tr>
							<td class="m">Уровень общего холестерина крови<sup>3</sup> (указать значение ммоль/л)</td>
							<td>{total_cholesterol_1}</td>
                            <td>{total_cholesterol_2}</td>
                            <td>{total_cholesterol_3}</td>
                            <td>{total_cholesterol_4}</td>
                            <td>{total_cholesterol_5}</td>
						</tr>
                        <tr>
							<td class="m">Дислипидемия<sup>4</sup> (есть, нет)</td>
							<td>{risk_dyslipidemia_1}</td>
                            <td>{risk_dyslipidemia_2}</td>
                            <td>{risk_dyslipidemia_3}</td>
                            <td>{risk_dyslipidemia_4}</td>
                            <td>{risk_dyslipidemia_5}</td>
						</tr>
                        <tr>
							<td class="m">Уровень глюкозы крови<sup>5</sup> (указать значение ммоль/л)</td>
							<td>{glucose_1}</td>
                            <td>{glucose_2}</td>
                            <td>{glucose_3}</td>
                            <td>{glucose_4}</td>
                            <td>{glucose_5}</td>
						</tr>
                        <tr>
							<td class="m">Повышенный уровень глюкозы крови (есть, нет)</td>
							<td>{risk_gluk_1}</td>
                            <td>{risk_gluk_2}</td>
                            <td>{risk_gluk_3}</td>
                            <td>{risk_gluk_4}</td>
                            <td>{risk_gluk_5}</td>
						</tr>
                        <tr>
							<td class="m">Артериальное давление<sup>6</sup> (указать значение мм рт.ст.)</td>
							<td>{person_pressure_1}</td>
                            <td>{person_pressure_2}</td>
                            <td>{person_pressure_3}</td>
                            <td>{person_pressure_4}</td>
                            <td>{person_pressure_5}</td>
						</tr>
                        <tr>
							<td class="m">Повышенный уровень артериального давления(есть, нет)</td>
							<td>{risk_high_pressure_1}</td>
                            <td>{risk_high_pressure_2}</td>
                            <td>{risk_high_pressure_3}</td>
                            <td>{risk_high_pressure_4}</td>
                            <td>{risk_high_pressure_5}</td>
						</tr>
                        <tr>
							<td class="m">Курение табака<sup>7</sup> (есть, нет)</td>
							<td>{IsSmoking_1}</td>
                            <td>{IsSmoking_2}</td>
                            <td>{IsSmoking_3}</td>
                            <td>{IsSmoking_4}</td>
                            <td>{IsSmoking_5}</td>
						</tr>
                        <tr>
							<td class="m">Низкая физическая активность<sup>8</sup> (есть, нет)</td>
							<td>{IsLowActiv_1}</td>
                            <td>{IsLowActiv_2}</td>
                            <td>{IsLowActiv_3}</td>
                            <td>{IsLowActiv_4}</td>
                            <td>{IsLowActiv_5}</td>
						</tr>
                        <tr>
							<td class="m">Нерациональное питание<sup>9</sup></td>
							<td>{IsIrrational_1}</td>
                            <td>{IsIrrational_2}</td>
                            <td>{IsIrrational_3}</td>
                            <td>{IsIrrational_4}</td>
                            <td>{IsIrrational_5}</td>

						</tr>
                        <tr>
							<td class="m">Риск пагубного потребление алкоголя (есть, нет)</td>
							<td>{IsRiskAlco_1}</td>
                            <td>{IsRiskAlco_2}</td>
                            <td>{IsRiskAlco_3}</td>
                            <td>{IsRiskAlco_4}</td>
                            <td>{IsRiskAlco_5}</td>
						</tr>
                        <tr>
							<td class="m">Риск потребления наркотических средств и психотропных веществ без назначения врача(есть, нет)</td>
							<td>{risk_narco_1}</td>
                            <td>{risk_narco_2}</td>
                            <td>{risk_narco_3}</td>
                            <td>{risk_narco_4}</td>
                            <td>{risk_narco_5}</td>
						</tr>
                        <tr>
							<td class="m">Суммарный сердечно-сосудистый риск (указать значение(%); умеренный, средний, высокий</td>
							<td>{summ_risk_1}</td>
                            <td>{summ_risk_2}</td>
                            <td>{summ_risk_3}</td>
                            <td>{summ_risk_4}</td>
                            <td>{summ_risk_5}</td>
						</tr>
                        <tr>
							<td class="m">Отягощенная наследственность по хроническим неинфекционным заболеваниям (указать заболевания)</td>
							<td>{her_diag_1}</td>
                            <td>{her_diag_2}</td>
                            <td>{her_diag_3}</td>
                            <td>{her_diag_4}</td>
                            <td>{her_diag_5}</td>
						</tr>
                        <tr>
							<td class="m">Высокий уровень стресса (есть, нет)</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
                        <tr>
							<td class="m">Должность, Ф.И.О. и подпись медицинского работника</td>
							<td>{dd_medpersonal_1}</td>
                            <td>{dd_medpersonal_2}</td>
                            <td>{dd_medpersonal_3}</td>
                            <td>{dd_medpersonal_4}</td>
                            <td>{dd_medpersonal_5}</td>
						</tr>

                        <tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 11pt;">
                                <sup>2</sup>Индекс массы тела - отношение веса (кг) к росту (м<sup>2</sup>). Целевое значение - не более 25,0 кг/м<sup>2</sup>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
                                <sup>3</sup>Целевое значение - ниже 5,0 ммоль/л.
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
                                <sup>4</sup>Дисплидемия - отклонение от нормы одного или более показателей липидного обмена (общий холестерин более 5 ммоль/л; холестерин липопротеидов высокой
								плотности у женщин менее 1,0 ммоль/л, у мужчин менее 1,2 ммоль/л; холестерин липопротеидов низкой плотности более 3 ммоль/л; триглицериды более 1,7 ммоль/л)
								или проведение гиполипидемической терапии (приложение №2 к порядку проведения диспансеризации определенных групп взрослого населения, утвержденному приказом
								Министерства здравоохранения Российской Федерации от 3 декабря 2012 г. № 1006н (зарегистрирован Министерством юстиции Российской Федерации 1 апреля 2013 г.,
								регистрационный №27930) (далее - Порядок).
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
                                <sup>5</sup>Целевое значение - 5,6-6,0 ммоль/л.
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
                                <sup>6</sup>Целевое значение - ниже 140/90 мм рт. ст.
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
                                <sup>7</sup>Курение табака  - ежедневное выкуривание одной сигареты и более (приложение №2 к Порядку). <i>Справочно:</i> Курение табака - один из основных
								факторов риска сердечно-сосудистых, бронхо-легочных, онкологичесих и других хронических неинфекционных заболеваний. Не существует безопасных доз и форм табака.
								Отказ от курения будет полезен для здоровья в любом возрасте, вне зависимости от "стажа" курения. Пассивное курение так же вредно, как и активное.
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
                                <sup>8</sup>Оптимальная физическая активность, полезная для здоровья - ходьба в умеренном темпе не менее 30 минут в день.
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
                                <sup>9</sup>Нерациональное питание - избыточное потребление пищи, жиров, углеводов, потребление поваренной соли более 5 граммов в сутки (досаливание
								приготовленной пищи, частое употребление соленостей, консервов, колбасных изделий), недостаточное потребление фруктов и овощей (менее 400 граммов или менее
								4-6 порций в сутки) (приложение №2 к Порядку).
                            </td>
                        </tr>

					</table>
				</td></tr>
            </table>

			<table class="struc">
				<tr><td class="measure">12. Группа состояния здоровья<sup>10</sup></td></tr>
				<tr><td>
					<table class="info">
                        <tr>
							<td style="width: 200px;">Дата</td>
                            <td class="y">{dd_date_1}</td>
                            <td class="y">{dd_date_2}</td>
                            <td class="y">{dd_date_3}</td>
                            <td class="y">{dd_date_4}</td>
                            <td class="y">{dd_date_5}</td>
						</tr>

                        <tr>
							<td class="m">Группа здоровья</td>
							<td>{hk_1}</td>
							<td>{hk_2}</td>
							<td>{hk_3}</td>
							<td>{hk_4}</td>
							<td>{hk_5}</td>
						</tr>
                        <tr>
							<td class="m" style="height: 2.5em;">Должность, Ф.И.О. и подпись медицинского работника</td>
                            <td>{dd_medpersonal_1}</td>
                            <td>{dd_medpersonal_2}</td>
                            <td>{dd_medpersonal_3}</td>
                            <td>{dd_medpersonal_4}</td>
                            <td>{dd_medpersonal_5}</td>
						</tr>

                        <tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
                                <sup>10</sup>В соответствии с пунктом 17 Порядка:
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
                                I группа состояния здоровья - граждане, у которых не установлены хронические неинфекционные заболевания, отсутствуют факторы риска развития таких заболеваний
								или имеются указанные факторы риска при низком или среднем суммарном сердечно-сосудистом риске и которые не нуждаются в диспансерном наблюдении по поводу
								других заболеваний (состояний).
                            </td>
                        </tr>
						<tr>
                            <td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
								II группа состояния здоровья - граждане, у которых не установлены хронические неинфекционные заболевания, имеются факторы риска развития таких заболеваний при
								высоком или очень высоком суммарном сердечно-сосудистом риске и которые не нуждаются в диспансерном наблюдении по поводу других заболеваний (состояний).
							</td>
						</tr>
						<tr>
							<td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
								{IIIa}
							</td>
						</tr>
						<tr>
							<td colspan="6" class='m' style="border-style:none; padding-left: 0em; padding-top: 0.3em; font-size: 12pt;">
								{IIIb}
							</td>
						</tr>

					</table>
				</td></tr>
			</table>

			<table class="struc">
				<tr><td class="measure">13. Рекомендации по проведению лабораторных и инструментальных обследований, осмотров (консультаций) врачей-специалистов, мероприятий, направленных
				на профилактику хронических неинфекционных заболеваний:</td></tr>
				<tr><td>
					<table class="info">
						<tr><td style="width: 500px;">Рекомендации</td><td>Дата назначений</td><td>Должность, Ф.И.О. и подпись медицинского работника</td></tr>
						<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
					</table>
				</td></tr>
			</table>
        </td>
    </tr>
</table>
<script type="text/javascript">activateSelectors();</script>
</body>
</html>