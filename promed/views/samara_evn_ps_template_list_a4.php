<html>
<head>
    <title>{EvnPSTemplateTitle}</title>
    <style type="text/css">
        @page port {
            size: portrait;
        }

        @page land {
            size: landscape;
        }

        body {
            padding: 0px;
        	width: 190mm;
            margin: auto;
        }

        table {
            border-collapse: collapse;			
        }

        .wordbreak{
            word-break:break-all;
        }
        
        .bordertable{
            border: 1px solid #000;
			font-size: 1em;
        }
        
        .bordertable table, .bordertable tr, .bordertable td {
            border: 1px solid #000;
        }
        
        .t2 table, .t2 tr, .t2 td {
            border: 1px solid #000;
        }

        span, div, td {
            font-family: times, tahoma, verdana;
            font-size: 1em;
        	padding: 0 0 0 .5em;
        }
        
        td.value{
        	vertical-align: top;
			font-weight : bold;
        }
        
        td.title{
        	vertical-align: top;
        }

        .t {
            width: 100%;
        }

            .t td:nth-child(odd) {
                white-space: nowrap;
            }

            .t td:nth-child(even) {            	
                border-bottom: 1px solid #000;
            }

        .c1 td:nth-child(even) {
            width: 100%;
        }

        .c2 td:nth-child(even) {
            width: 50%;
        }

        .c3 td:nth-child(even) {
            width: 33.3%;
        }


        thead {
            text-align: center;
        }

        .rotate {
            -webkit-transform: rotate(-90deg);
            -moz-transform: rotate(-90deg);
            -ms-transform: rotate(-90deg);
            -o-transform: rotate(-90deg);
            transform: rotate(-90deg);
            /* also accepts left, right, top, bottom coordinates; not required, but a good idea for styling */
            -webkit-transform-origin: 50% 50%;
            -moz-transform-origin: 50% 50%;
            -ms-transform-origin: 50% 50%;
            -o-transform-origin: 50% 50%;
            transform-origin: 50% 50%;
            /* Should be unset in IE9+ I think. */
            filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);        	
        }
        
@media all
{
    div#print-control-block a.print-control
    {
        background-position: 0px 2px;
        display: block;        
        border-radius: 6px;
        height: 28px;
        width: 120px;
        font-weight: bold;
        color: #575656;
        text-decoration: none;
        padding: 12px 1px 1px 44px;
        background-color: #FFFFFF;
        float: right;
        background-image: url('/img/Print-icon_small.png');
        background-repeat: no-repeat;
    	font-size: .8em;    	
    }

    div#print-control-block a.print-control:active
    {
        background-position: 1px 3px;
        padding: 13px 0 0 45px;   
    	
    }

    div#print-control-block
    {
        display: block;
        width: 100%;
        top: 0px;
        left: 0px;        
        position:fixed;        
        padding: .5em 1em .5em 0;
        height: 44px;     
    }
}

@media print
{
    div#print-control-block, div#separator
    {
        display: none;
    }    
    
    
    div.printLandscape
    {
     -moz-transform: rotate(90deg); /* Для Firefox */
     -ms-transform: rotate(90deg); /* Для IE */
     -webkit-transform: rotate(90deg); /* Для Safari, Chrome, iOS */
     -o-transform: rotate(90deg); /* Для Opera */
     transform: rotate(90deg);    	
    }
}
    </style>
</head>
<body>

<div id="print-control-block">
        <a class="print-control" href="javascript: window.print()" title="Вывод документа на печать">Отправить на печать</a>    
</div>

<div style='width:180mm'>
    <table style="font-size:0.8em">
        <tr>
            <td style="width: 50%; margin-left:1em">Министерство здравоохранения
                <br />
                Российской Федерации <br /><br />
                
                {Lpu_Name}
            </td>

            <td style="width: 50%;">
            <div style='margin-left:40px'>
            Приложение № 5
            <br />
                к  приказу Минздрава России  от 30.12.2002 № 413 Медицинская документация
            <br />
                Форма № 066/у-02
            <br />
                Утверждена приказом Минздрава РФ
            <br />
                от 30.12.2002 № 413 
                </div>
            </td>
        </tr>
       
    </table>

    <div style="text-align: center; font-weight: bold; margin: 1em;">
        <div>СТАТИСТИЧЕСКАЯ КАРТА ВЫБЫВШЕГО ИЗ СТАЦИОНАРА</div>
        <div>круглосуточного пребывания, дневного стационара при больничном</div>
        <div>учреждении, дневного стационара при  амбулаторно-поликлиническом</div>
        <div>учреждении, стационара на дому</div>
        <div>№ медицинской карты <span style="text-decoration: underline">{EvnPS_NumCard}</span></div>
    </div>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">1. Код пациента</td>
                <td class="value">{PersonCard_Code}</td>
                <td class="title">2. Ф.И.О:</td>
                <td class="value">{Person_Fio}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">3. Пол:</td>
                <td class="value">{Sex_Name}</td>
                <td class="title">4. Дата рождения</td>
                <td class="value">{Person_Birthday}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">5. Документ, удостов. личность: </td>
                <td class="value">{DocumentType_Name} {Document_Ser}&nbsp;&nbsp;{Document_Num}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">6. Адрес:</td>
                <td class="value">{UAddress_Name}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">7. Код территории проживания:</td>
                <td class="value">{Ocato}</td>
                <td class="title">Житель:</td>
                <td class="value">{KLAreaType_Name}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">8. Страховой полис (серия, номер):</td>
                <td class="value">{Polis_Ser} {Polis_Num}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">Выдан:</td>
                <td class="value">{OrgSmo_Nick}</td>
                <td class="title">Код терр.:</td>
                <td class="value">{OmsSprTerr_Code}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">9. Вид  оплаты:</td>
                <td class="value">{PayType_Name}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">10. Социальный статус:</td>
                <td class="value">{SocStatus_Name}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">11. Категория льготности: </td>
                <td class="value">{PrivilegeType_Name}</td>
            </tr>
        </tbody>
    </table>
    
    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">12. Кем направлен</td>
                <td class="value">{PrehospLpu_Id} {PrehospOrg_Nick}</td>
                        </tr>
        </tbody>
    </table>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">№ напр.</td>
                <td class="value">{EvnDirection_Num}</td>
                <td class="title">Дата:</td>
                <td class="value">{EvnDirection_setDate}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c3">
        <tbody>
            <tr>
                <td class="title">13. Кем доставлен</td>
                <td class="value">{PrehospArrive_Name}</td>
                <td class="title">Код</td>
                <td class="value">{EvnPS_CodeConv}</td>
                <td class="title">Номер наряда:</td>
                <td class="value">{EvnPS_NumConv}</td>
            </tr>
        </tbody>
    </table>


    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">14. Диагноз направившего учреждения</td>
                <td class="value">{PrimaryHospDiag_Name}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">15. Диагноз приемного отделения</td>
                <td class="value">{PrimaryRecepDiag_Name}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">16. Доставлен в состоянии опьянения:</td>
                <td class="value">{PrehospToxic_Name}</td>
            </tr>
        </tbody>
    </table>

    <!-- !!! no data -->
    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">17. Госпитализирован по поводу данного заболевания в текущем году: </td>
                <td class="value">{EvnPS_HospCount}</td><!-- в первые 6 часов  -1;   в теч. 7-24 часов - 2;  позднее 24-х часов – 3.         -->
            </tr>
        </tbody>
    </table>

    <!-- !!! no data -->
    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">18. Доставлен в стационар от начала заболевания (получения травмы): </td>
                <td class="value">{EvnPS_TimeDesease}</td>  <!-- в первые 6 часов  -1;   в теч. 7-24 часов - 2;  позднее 24-х часов – 3.       -->
            </tr>
        </tbody>
    </table>


    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">19. Травма: </td>
                <td class="value">{PrehospTrauma_Name}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">20. Дата поступления в приемное отделение:</td>
                <td class="value">{EvnPS_setDate}</td>
                <td class="title">Время</td>
                <td class="value">{EvnPS_setTime}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c3">
        <tbody>
            <tr>
                <td class="title">21. Название отделения</td>
                <td class="value" colspan="3">&nbsp;{HospSection_Name}</td>
			</tr>
			<tr>
                <td class="title">Дата поступления</td>
                <td class="value">{EvnPS_outcomeDate}</td>
                <td class="title">Время</td>
                <td class="value">{EvnPS_outcomeTime}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">Подпись врача приемного отделения</td>
                <td class="value">&nbsp;</td>
                <td class="title">Код</td>
                <td class="value">{PreHospMedPersonal_Code}&nbsp;&nbsp;&nbsp;{PreHospMedPersonal_Fio}</td>
            </tr>
        </tbody>
    </table>

    <!-- Пустая подчёркнутая строка-->
    <table class="t c1">
        <tbody>
            <tr>                
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">22. Дата выписки (смерти):</td>
                <td class="value">{EvnPS_disDate}</td>
                <td class="title">Время</td>
                <td class="value">{EvnPS_disTime}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">23. Продолжительность госпитализации (койко-дней):</td>
                <td class="value">{SectionsDays}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">24. Исход госпитализации:</td>
                <td class="value">{LeaveType_Name}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td class="title">24.1.  Результат госпитализации: </td>
                <td class="value">{ResultDesease_Name}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c2">
        <tbody>
            <tr>
                <td class="title">25. Листок нетрудоспособности: открыт</td>
                <td class="value">{EvnStick_begDate}</td>
                <td class="title">закрыт:</td>
                <td class="value">{EvnStick_endDate}</td>
            </tr>
        </tbody>
    </table>

    <table class="t c3">
        <tbody>
            <tr>
                <td class="title">25.1. По уходу за больным</td>
                <td>&nbsp;</td>
                <td class="title">Полных лет:</td>
                <td class="value">{EvnStick_Age}</td>                
                <td class="title">Пол:</td>
                <td class="value">{EvnStick_Sex_Name}</td>
                
            </tr>
        </tbody>
    </table>

    <div style="page-break-after: always;"></div>

    <div style="margin-top: 1em; margin-bottom: 1em;">
        <span>26. Движение пациента по отделениям:</span>
        <table class='bordertable wordbreak'>
            <thead>
                <tr>
                    <td>№<br />
                        №</td>
                    <td>Код отделения</td>
                    <td>Профиль коек</td>
                    <td>Код врача</td>
                    <td>Дата поступления</td>
                    <td>Дата выписки перевода</td>
                    <td>Код диагноза по МКБ</td>
                    <td>Код <br /> медицинского стандарта</td>
                    <td>Код <br /> прерванного случа</td>
                    <td>Вид оплаты</td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>2</td>
                    <td>3</td>
                    <td>4</td>
                    <td>5</td>
                    <td>6</td>
                    <td>7</td>
                    <td>8</td>
                    <td>9</td>
                    <td>10</td>
                </tr>
            </thead>
            <tbody style='font-size:0.8em;'>
                {EvnSectionData}
                <tr>
                    <td></td>
                    <td>{EvnSection_id}</td>  <!-- -->
                    <td style='text-transform:lowercase'>{LpuSectionBedProfile_Name}</td>
                    <td>{MedPersonal_Code}</td>
                    <td>{EvnSection_setDT}</td>
                    <td>{EvnSection_disDT}</td>
                    <td>{EvnSectionDiagOsn_Code}</td>
                    <td></td>
                    <td></td>
                    <td>{PayType_Name}</td>
                </tr>
                {/EvnSectionData}
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1em; margin-bottom: 1em;">
        <span>27. Хирургические операции (обозначить: основную операцию, использование спец. аппаратуры):</span>
        <table class="bordertable wordbreak">
            <thead>
                <tr>
                    <td rowspan="2" style="height:140px; width:40px;"><div class="rotate" style="white-space:nowrap; width: 40px; margin: 60px 0 0 0;">Дата, время</div></td>
                    <td rowspan="2"><div class="rotate" style="white-space:nowrap; width: 40px; margin: 60px 0 0 0;">Код врача</div></td>
                    <td rowspan="2"><div class="rotate" style="white-space:nowrap; width: 40px; margin: 60px 0 0 0;">Код отделения</div></td>
                    <td colspan="2">Операция</td>
                    <td colspan="2">Осложнение</td>
                    <td rowspan="2">Анестезия</td>
                    <td colspan="3">Использ. спец. аппаратуры</td>
                    <td rowspan="2"><div class="rotate" style="white-space:nowrap; width: 40px; margin: 60px 0 0 0;">Вид оплаты</div></td>
                </tr>
                <tr>
                    <td style="width: 60px;">Наиме<br />нование</td>
                    <td style="width: 30px;">Код</td>
                    <td style="width: 60px;">Наиме<br />нование</td>
                    <td style="width: 30px;">Код</td>
                    <td style="height: 50px;"><div class="rotate" style="white-space:nowrap; width: 40px; margin: 10px 0 0 0;">энд.</div></td>
                    <td ><div class="rotate" style="white-space:nowrap; width: 40px; margin: 10px 0 0 0;">лазер.</div></td>
                    <td ><div class="rotate" style="white-space:nowrap; width: 40px; margin: 10px 0 0 0;">криог.</div></td>
                </tr>
                <tr>
                    <td>1</td>
                    <td>2</td>
                    <td>3</td>
                    <td>4</td>
                    <td>5</td>
                    <td>6</td>
                    <td>7</td>
                    <td>8</td>
                    <td>9</td>
                    <td>10</td>
                    <td>11</td>
                    <td>12</td>
                </tr>
            </thead>
            <tbody style='font-size:0.8em;'>
                {EvnUslugaOperData}
                <tr>
                    <td>{EvnUslugaOper_setDT}</td>
                    <td>{MedPersonal_Code}</td>
                    <td>{LpuSection_Code}</td>
                    <td>{Usluga_Name}</td>
                    <td>{Usluga_Code}</td>
                    <td>{AggType_Name}</td>
                    <td>{AggType_Code}</td>
                    <td>{AnesthesiaClass_Name}</td>
                    <td>{EvnUslugaOper_IsEndoskop}</td>
                    <td>{EvnUslugaOper_IsLazer}</td>
                    <td>{EvnUslugaOper_IsKriogen}</td>
                    <td>{PayType_Name}</td>
                </tr>
                {/EvnUslugaOperData}

            </tbody>
        </table>
    </div>

    <span>28. Обследован: RW  1 [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]
	AIDS  2 [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;]</span>

    <div style="margin-top: 1em; margin-bottom: 1em;">
        <span>29. Диагноз стационара (при выписке):</span>
        <table class="bordertable">
            <thead>
                <tr style="height: 15px;">
                    <td style="height: 120px; width: 10px;" rowspan="2"><div class="rotate" style="white-space:nowrap; width: 40px; margin: 80px 0 0 0;">Клинический<br />заключительный</div></td>
                    <td>Основное заболевание</td>
                    <td>Код МКБ</td>
                    <td>Осложнение</td>
                    <td>Код МКБ</td>
                    <td>Сопутствующие заболевания</td>
                    <td>Код МКБ</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="height: 120px; text-align: center;"><div class="rotate" style="white-space:nowrap; width: 40px; margin: 80px 0 0 0;">Паталого-<br />анатомический</div></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="t c1">
        <tbody>
            <tr>
                <td>В случае смерти указать основную причину</td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <table class="t c2">
        <tbody>
            <tr>
                <td></td>
                <td>&nbsp;</td>
                <td>код по МКБ</td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <table class="t c1">
        <tbody>
            <tr>
                <td style="vertical-align:top;">31. Дефекты догоспитального этапа:</td>
                <td>
                    несвоевременность госпитализации [&nbsp;{EvnPS_IsImperHosp}&nbsp;];
                    недостаточный объем клинико-диагностического обследования [&nbsp;{EvnPS_IsShortVolume}&nbsp;];
                    неправильная тактика лечения [&nbsp;{EvnPS_IsWrongCure}&nbsp;];
                    несовпадение диагноза [&nbsp;{EvnPS_IsDiagMismatch}&nbsp;].
                </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 1em; margin-bottom: 1em;">
        <table class="t c1">
            <tbody>
                <tr>
                    <td>Подпись лечащего врача</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <table class="t c1">
            <tbody>
                <tr>
                    <td>Подпись заведующего отделением</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>
    </div>
    
</div>    
</body>
</html>
