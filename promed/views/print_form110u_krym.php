<html>
<head>
    <meta http-equiv=Content-Type
          content="text/html; charset=<?php echo(defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>">
    <style>

        html {
            margin: 0;
            padding: 0;
        }

        body {
            Font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            margin: 0;
            padding: 0;
        }

        p {
            margin: 0 0 5px
        }

        table {
            line-height: 1;
            font-size: 20pt;
            vertical-align: top;
        }

        table td {
            font-size: 10pt;

        }

        .head110 {
            vertical-align: top;
            height: 0px;
        }

        .head110 big {
            line-height: 14px;
        }

        table.time {
            width: 100%;
            border-collapse: collapse;
        }

        table.time td {
            border: 1px solid #000;
            text-align: left;
            border-bottom: 0px;
            padding-left: 5px;
            vertical-align: top;
        }

        table.time td.ender {
            border-bottom: 1px solid #000;
        }

        span {
            display: inline-block;
        }

        .under {
            border-bottom: 1px solid;
        }

        .lister {
            width: 26cm;
            height: 18cm;
            -webkit-transform: rotate(-90deg);
            -moz-transform: rotate(-90deg);
            -o-transform: rotate(-90deg);
            -ms-transform: rotate(-90deg);
            transform: rotate(-90deg);
            filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
            margin-left: -4cm;
            margin-top: 4cm;
            /*border: 1px solid;*/
        }

        .page {
            width: 41cm;
            /*height: 25cm;*/
            float: left;
            display: block;
        }

        .pageLeft {
        }

        .pageRight {
        }
        .page1{
            height: 29cm;
        }
        .page2 table{
           line-height: 1;
        }
        .page2 table p{
           max-width: 400px;
        }
        .v_ok{display: inline-block;}
        .v_ok:after {content: "V"; font-family: Verdana; font-size: 14px; font-weight: bold; border: 1px solid #000; height: 12px; display: inline-block; line-height: 12px;}
        .v_no {border: 1px solid #000; width:11px; height: 12px;display: inline-block;}
        .v_no.big{height: 15px; width: 12px; margin-right: 1px; font-weight: bold;text-align: center}
        .v_no.giant{
            height: 40px;
            position: relative;
            width: 100px;
            background-color: #fff;
            text-align: center;
            line-height: 40px;
            float: right;
        }
        .column{
            position: relative;
            width: 445px;
            float: left;
            line-height: 0.9;
        }
        .border{
            border-left: 1px solid black;
            border-right: 1px solid black;
        }
        .time-table td{text-align: center; line-height: 0.7}
        .time{line-height: 0.8}
        .text{
            max-width: 400px;
            overflow: hidden;
        }
        .person-social {
            line-height: 1.2;
            float: left;
        }
        .person-social .innerwrapper {
            display: inline;
            float: left;
        }
        .person-social .innerwrapper .v_no {
            float:left;
            margin: 0px 5px;
        }
        .person-social .innerwrapper .v_ok {
            float:left;
            margin: 0px 5px;
        }
    </style>

    <script type="text/javascript">
        window.print();
    </script>
    <title></title>

</head>
<body>

<!--<div class="lister">-->
    <div class="page pageLeft page1">

<!--        <table border="0" cellspacing="0" cellpadding="0">-->
<!--            <tbody>-->
<!--            <tr>-->
                <div class="column" >
                    <p>
                        <strong>ГБУЗ РК «КРЦМКИСМП» </strong>
                    </p>
                    <p>
                        штамп МО
                    </p>
                    <p>
                        Станция {StationNum} п/ст № {LpuBuilding_Code}
                    </p>
                    <p style="float:left">
                       Вызов № {Day_num}
                        <div class="<?= ($CmpCloseCard_IsExtra == 1 || !isset($CmpCloseCard_IsExtra)) ? 'v_ok' : 'v_no'?>"></div>
                        <strong>экстренный </strong>
                        <div class="<?= ($CmpCloseCard_IsExtra == 2) ? 'v_ok' : 'v_no'?>"></div>
                        <strong>неотложный</strong>
                    </p>
                    <p>
                    <strong style="float:left">Обоснованность </strong>
                    <div class="<?= (isset($CmpCloseCard_IsProfile) && $CmpCloseCard_IsProfile  == 2) ? 'v_ok' : 'v_no'?>"></div>профильный
                    <div class="<?= (isset($CmpCloseCard_IsProfile) && $CmpCloseCard_IsProfile  == 1) ? 'v_ok' : 'v_no'?>"></div>непрофильный
                    </p>
                    <p>
                        Время:
                    </p>
                    <div align="center">
                        <table border="1" cellspacing="0" cellpadding="0" width="440" class="time-table">
                            <tbody>
                            <tr>
                                <td width="20%" valign="top">
                                    <p>
                                        Приема вызова
                                    </p>
                                    <p class="time">
                                        {AcceptTime}
                                    </p>
                                </td>
                                <td width="18%" valign="top">
                                    <p>
                                        Передачи бригаде
                                    </p>
                                    <p class="time">
                                        {TransTime}
                                    </p>
                                </td>
                                <td width="24%" colspan="2" valign="top">
                                    <p>
                                        Выезда на вызов
                                    </p>
                                    <p class="time">
                                        {GoTime}
                                    </p>
                                </td>
                                <td width="36%" colspan="2" valign="top">
                                    <p>
                                        Прибытия на вызов
                                    </p>
                                    <p class="time">
                                        {ArriveTime}
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td width="20%" valign="top">
                                    <p>
                                        Начало
                                        тр-ки
                                    </p>
                                    <p class="time">
                                        {TransportTime}
                                    </p>
                                </td>
                                <td width="18%" valign="top">
                                    <p>
                                        Окончание тр-ки
                                    </p>
                                    <p class="time">
                                        {TranspEndDT}
                                    </p>
                                </td>
                                <td width="18%" valign="top">
                                    <p>
                                        Окончание вызова
                                    </p>
                                    <p class="time">
                                        {EndTime}
                                    </p>
                                </td>
                                <td width="21%" colspan="2" valign="top">
                                    <p>
                                        Возвращение на станцию
                                    </p>
                                    <p class="time">
                                        {BackTime}
                                    </p>
                                </td>
                                <td width="20%" valign="top">
                                    <p>
                                        Время, затрачен на вызов
                                    </p>
                                    <p class="time">
                                        {SummTime}
                                    </p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <table>
                        <tr>
                            <td width="210" valign="top">
                                <p>
                                    <strong>Вызов получен: </strong>
                                </p>
                                <p>									
                                <div class="<?= isset($c592)  ?  'v_ok' : 'v_no'?>"></div> на станции<strong> </strong>
                                </p>
                                <p>
                                    <div class="<?= isset($c593)  ?  'v_ok' : 'v_no'?>"></div> вне станции<strong> </strong>
                                </p>
                                <p>
                                    <strong>Вызов: </strong>
                                </p>

                                <p>
                                    <div class="<?= isset($CmpCallType_Code) && $CmpCallType_Code == 1 ?  'v_ok' : 'v_no'?>"></div> Первичный
                                </p>
                                <p>
                                    <div class="<?= isset($CmpCallType_Code) && $CmpCallType_Code == 2 ?  'v_ok' : 'v_no'?>"></div> Повторный
                                </p>
                                <p>
                                    <div class="<?= isset($CmpCallType_Code) && $CmpCallType_Code == 10 ?  'v_ok' : 'v_no'?>"></div> «в помощь»
                                </p>
                                <p>
                                    <div class="<?= isset($CmpCallType_Code) && $CmpCallType_Code == 18 ?  'v_ok' : 'v_no'?>"></div> Остановка в пути
                                </p>
                                <p>
                                    <div class="<?= isset($CmpCallType_Code) && $CmpCallType_Code == 11 ?  'v_ok' : 'v_no'?>"></div> Амбулаторный
                                </p>
                                <p>
                                    <div class="<?= isset($CmpCallType_Code) && $CmpCallType_Code == 3 ?  'v_ok' : 'v_no'?>"></div> Активный
                                </p>
                                <p>
                                    <strong>Причина обращения </strong>
                                </p>
                                <p style="margin: 10px 0;line-height: 0.8"> {ReasonCode} {Reason} </p>
                                <? /* <p>
                                    <div class="<?= isset($c156)  ?  'v_ok' : 'v_no'?>"></div> Острое внезапное
                                </p>
                                <p>
                                    заболевание
                                </p>
                                <p>
                                    <div class="<?= isset($c155)  ?  'v_ok' : 'v_no'?>"></div> Несчастный случай
                                </p>
                                <p>
                                    <div class="<?= isset($c158)  ?  'v_ok' : 'v_no'?>"></div> Патология беременности
                                </p>
                                <p>
                                    <div class="<?= isset($c159)  ?  'v_ok' : 'v_no'?>"></div> Роды
                                </p>
                                <p>
                                    <div class="v_no"></div> ЧС (угроза)
                                </p>
                                <p>
                                    <div class="v_no"></div> Врач. сопровождение
                                </p>
                                <p>
                                    <div class="<?= isset($c161)  ?  'v_ok' : 'v_no'?>"></div> Экстренная перевозка
                                </p>
                                <p>
                                    <div class="<?= isset($c160)  ?  'v_ok' : 'v_no'?>"></div> Плановая перевозка
                                </p>
                                <p>
                                    <div class="v_no"></div> Мед.сопровождение
                                </p>
                                <p>
                                    <div class="<?= isset($c157)  ?  'v_ok' : 'v_no'?>"></div> Обострение
                                </p>
                                <p>
                                    хронического заболевания
                                </p>
                                <p>
                                    <div class="v_no"></div> Непрофильный
                                </p>
                                <p>
                                    <div class="<?= isset($c162)  ?  'v_ok' : 'v_no'?>"></div> Прочее
                                </p>
 */?>
                                <p>
                                    <strong>Место вызова</strong>
                                </p>
                                <p>
                                    <div class="<?= isset($c181)  ?  'v_ok' : 'v_no'?>"></div> Квартира
                                </p>
                                <p>
                                    <div class="<?= isset($c180)  ?  'v_ok' : 'v_no'?>"></div> Улица
                                </p>
                                <p>
                                    <div class="<?= isset($c183)  ?  'v_ok' : 'v_no'?>"></div> Общественное место
                                </p>
                                <p>
                                    <div class="<?= isset($c182)  ?  'v_ok' : 'v_no'?>"></div> Рабочее место
                                </p>
                                <p>
                                    <div class="<?= isset($c359)  ?  'v_ok' : 'v_no'?>"></div> Полиция
                                </p>
                                <p>
                                    <div class="<?= isset($c600)  ?  'v_ok' : 'v_no'?>"></div> Трасса (в т.ч. федерал )
                                </p>
                                <p>
                                    <div class="<?= isset($c189)  ?  'v_ok' : 'v_no'?>"></div> Дошкольное учреждение
                                </p>
                                <p>
                                    <div class="<?= isset($c188)  ?  'v_ok' : 'v_no'?>"></div> Школа №
                                </p>
                                <p>
                                    <div class="<?= isset($c599)  ?  'v_ok' : 'v_no'?>"></div> МО
                                </p>
                                <p>
                                    <div class="<?= isset($c673)  ?  'v_ok' : 'v_no'?>"></div> Другое <?= isset($c673) ? $c191 : ''?>
                                </p>
                            </td>
                            <td width="236" valign="top">
                                <p>
                                    <strong>Причина длительного доезда</strong>
                                </p>
                                <p>
                                    <div class="<?= isset($c361) ? 'v_ok' : 'v_no'?>"></div> затруднен проезд
                                </p>
                                <p>
                                    <div class="<?= isset($c362) ? 'v_ok' : 'v_no'?>"></div> уточнение адреса
                                </p>
                                <p>
                                    <div class="<?= isset($c363) ? 'v_ok' : 'v_no'?>"></div> поломка а/м
                                </p>
                                <p>
                                    <div class="<?= isset($c364) ? 'v_ok' : 'v_no'?>"></div> др.указать <?= isset($c364) ? $c364 : ''?>
                                </p>
                                <p>
                                    <strong>Причина несчастного случая</strong>
                                </p>
                                <p>
                                    <div class="<?= isset($c343)  ?  'v_ok' : 'v_no'?>"></div> Неизвестна
                                </p>
                                <p>
                                    <div class="<?= isset($c344)  ?  'v_ok' : 'v_no'?>"></div> Бытовая
                                </p>
                                <p>
                                    <div class="<?= isset($c345)  ?  'v_ok' : 'v_no'?>"></div> Производственная
                                </p>
                                <p>
                                    <div class="<?= isset($c346)  ?  'v_ok' : 'v_no'?>"></div> Спортивная
                                </p>
                                <p>
                                    <div class="<?= isset($c194)  ?  'v_ok' : 'v_no'?>"></div> ДТП
                                </p>
                                <p>
                                    <div class="<?= isset($c347)  ?  'v_ok' : 'v_no'?>"></div> Гололед
                                </p>
                                <p>
                                    <div class="<?= isset($c348)  ?  'v_ok' : 'v_no'?>"></div> Недосмотр
                                </p>
                                <p>
                                    <div class="<?= isset($c349)  ?  'v_ok' : 'v_no'?>"></div> Пожар
                                </p>
                                <p>
                                    <div class="<?= isset($c193)  ?  'v_ok' : 'v_no'?>"></div> Криминальная
                                </p>
                                <p>
                                    <div class="<?= isset($c350)  ?  'v_ok' : 'v_no'?>"></div> Суицид
                                </p>
                                <p>
                                    <div class="<?= isset($c351)  ?  'v_ok' : 'v_no'?>"></div> Отравление
                                </p>
                                <p>
                                    <div class="<?= isset($c352)  ?  'v_ok' : 'v_no'?>"></div> Утопление
                                </p>
                                <p>
                                    <div class="<?= isset($c353)  ?  'v_ok' : 'v_no'?>"></div> Ж/Д пр
                                </p>
                                <p>
                                <div class="<?= isset($c355) || isset($c356) || isset($c357) || isset($c358) || isset($c204)   ?  'v_ok' : 'v_no'?>"></div> Травма :
                                </p>
                                <div style="padding-left: 15px">
                                <p>
                                <div class="<?= isset($c355)  ?  'v_ok' : 'v_no'?>"></div> термическая
                                </p>
                                <p>
                                <div class="<?= isset($c356)  ?  'v_ok' : 'v_no'?>"></div> механическая
                                </p>
                                <p>
                                <div class="<?= isset($c357)  ?  'v_ok' : 'v_no'?>"></div>  <strong>сочетанная</strong>
                                </p>
                                <p>
                                <div class="<?= isset($c358)  ?  'v_ok' : 'v_no'?>"></div>  комбинированная
                                </p>
                                <p>
                                <div class="<?= isset($c204)  ?  'v_ok' : 'v_no'?>"></div> другое <?= isset($c204)  ?  $c204 : ''?>
                                </p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="446" colspan="2" valign="top">
                                <p style="float: left">
                                    <strong>НАЛИЧИЕ КЛИНИКИ ОПЬЯНЕНИЯ </strong></p>
                                <div style="margin-top: -5px">
                                <div class="<?= isset($isAlco) && $isAlco == 2 ?  'v_ok' : 'v_no'?>"></div>
                                    <strong> ДА </strong>
                                <div class="<?= isset($isAlco) && $isAlco == 1 ?  'v_ok' : 'v_no'?>"></div>
                                    <strong> НЕТ </strong>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="446" colspan="2" valign="top">
                                <p>
                                    <strong>Способ доставки пациента в машину: </strong>
                                </p>
                                <p>
                                    <div class="<?= isset($c116)  ?  'v_ok' : 'v_no'?>"></div> передвигался самостоятельно
                                </p>
                                <p style="float:left">
                                    Перенесен: <div class="<?= isset($c114)  ?  'v_ok' : 'v_no'?>"></div> на носилках, <div class="<?= isset($c115)  ?  'v_ok' : 'v_no'?>"></div> на подручных средствах
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="column border" >
                    <p align="center">
                        <strong>КАРТА ВЫЗОВА № {Day_num} от {CallCardDate} г.</strong>
                    </p>
                    <br/>
                    <p align="center">

                    </p>
                    <p>
                        Адрес вызова: <?= isset($Area)  ?  'район '.$Area : ''?>
                    </p>
                    <p>
                        город/село <?= isset($City)  ?  $City : ''?>
                    </p>
                    <p>
                        улица <?= isset($Street)  ?  $Street : ''?>
                    </p>
                    <p>
						дом <?php
						if ( !empty($secondStreetName)) { ?>
							{secondStreetName}
						<?php } else{ ?>
							{House}
						<?php } ?>
						,
						корп. <?= isset($Korpus)  ?  $Korpus : ''?> кв. <?= isset($Office)  ?  $Office : ''?> комн.<?= isset($Room)  ?  $Room : ''?>
                    </p>
                    <p>
                        подъезд <?= isset($Entrance)  ?  $Entrance : ''?> код подъезда <?= isset($CodeEntrance)  ?  $CodeEntrance : ''?> этаж <?= isset($Level)  ?  $Level : ''?>
                    </p>
                    <p>
                        Фамилия <?= isset($Fam)  ?  $Fam : ''?>
                    </p>
                    <p>
                        Имя <?= isset($Name)  ?  $Name : ''?>
                    </p>
                    <p>
                        Отчество <?= isset($Middle)  ?  $Middle : ''?>
                    </p>

                    <p style="float:left; padding-right: 5px">Дата рожд:</p>
                    <? if (isset($BirthDay) && strlen($BirthDay) > 0 && $BirthDay != '&nbsp;') {

                        $symbols = 10;

                        foreach(str_split($BirthDay) as $simvol){
                        ?><div class="v_no big"><?=$simvol?></div><?
                        }} else {
                        for ($i=0; $i<10; $i++){?><div class="v_no big"></div><? }} ?>

                    Возраст <?= (isset($AgePS) && $AgePS>0)  ?  $AgePS : $Age?>

                    <?= isset($AgeTypeValue)  ?  $AgeTypeValue : ''?>
					Пол <div class="<?= isset($Sex_id) && $Sex_id == 1 ?  'v_ok' : 'v_no'?>"></div>  М <div class="<?= isset($Sex_id) && $Sex_id == 2 ?  'v_ok' : 'v_no'?>"></div>  Ж
                    <div style="clear: both"></div>
					<p style="float:left; padding-right: 5px">Паспорт (св. о рожд):</p>
                    <?if(isset($Document_Ser) && strlen($Document_Ser) > 0 && $Document_Ser != '&nbsp;') : foreach(preg_split('//u',$Document_Ser,-1,PREG_SPLIT_NO_EMPTY) as $simvol):?><div class="v_no big"><?=$simvol?></div><?endforeach?><?endif?>
                    <?if(isset($Document_Num) && strlen($Document_Num) > 0 && $Document_Num != '&nbsp;') : foreach(str_split($Document_Num) as $simvol):?><div class="v_no big"><?=$simvol?></div><?endforeach?><?endif?>
                    <div style="clear: both"></div>
                    <p style="float:left">
                        Полис ОМС  </p><?if(isset($Federal_Num) && strlen($Federal_Num) > 0 && $Federal_Num != '&nbsp;') : foreach(str_split($Federal_Num) as $simvol):?><div class="v_no big"><?=$simvol?></div><?endforeach?><?endif?>
                    <div style="clear: both"></div>
                    <p>
                        <strong>СМО</strong>
                            <?= isset($SMO_Name) ? $SMO_Name : "" ?>
                        <strong>Дата выдачи </strong>
                            <?= isset($Polis_begDate) ? $Polis_begDate : "" ?>
                    </p>
                    <p>
                        <strong>Гражданство</strong>
                        <?= isset($KLCountry_Name) ? $KLCountry_Name : ''?>
                    </p>
                    <p>
                        Адрес пост. регистрации: <?= isset($UAddress_AddressText) ? $UAddress_AddressText : ''?>
                    <p>
                        <strong>Кто вызвал, телефон</strong>
                        {Ktov},{Phone}
                    </p>
                    <div class="person-social">
                        <strong>Социальное положение больного</strong>
                        {C_PersonSocial_id}
                    </div>
                    <p>
                        <strong>РЕЗУЛЬТАТ ВЫЕЗДА:</strong> <div class="<?= isset($c224)  ?  'v_ok' : 'v_no'?>"></div> оказана пом. оставл. на месте
                    </p>
                    <p>
                        <div class="<?= isset($c227)  ?  'v_ok' : 'v_no'?>"></div> передан спец. бригаде № <?= (isset($c227) && isset($c244))  ? $c244 : '____'?> в <?= (isset($c227) && isset($c245))  ? $c245 : '____'?>
                    </p>
                    <p>
                        <div class="<?= isset($c225)  ?  'v_ok' : 'v_no'?>"></div> доставлен в тр. пункт <?= (isset($c225) && isset($c687))  ?  $OrgNick687 : '____'?> <div class="<?= (isset($c640) && isset($c688))  ?  'v_ok' : 'v_no'?>"></div> морг<?= (isset($c640) && isset($c688))  ?  $OrgNick688 : '____'?>
                    </p>
                    <p>
                        <div class="<?= isset($c641)  ?  'v_ok' : 'v_no'?>"></div> в п/о больницы, травм.п <?= (isset($c641) && isset($c689))  ? (isset($OrgNick689) ? $OrgNick689 : '____' ) : '____' ?>
                    </p>
                    <p>
                    <div class="<?= isset($c130)  ?  'v_ok' : 'v_no'?>"></div> Адрес не найден
                    <div class="<?= isset($c128)  ?  'v_ok' : 'v_no'?>"></div> Больного нет на месте
                    </p>
                    <p>
                    <div class="<?= isset($c129)  ?  'v_ok' : 'v_no'?>"></div> Отказ от осмотра
                    <div class="<?= isset($c135)  ?  'v_ok' : 'v_no'?>"></div> Вызов отменен
                    </p>
                    <p>
                    <div class="<?= isset($c134)  ?  'v_ok' : 'v_no'?>"></div> Обслед. до приезда
                    <div class="<?= isset($c132)  ?  'v_ok' : 'v_no'?>"></div> Смерть до приезда
                    </p>
                    <p>
                    <div class="<?= isset($c131)  ?  'v_ok' : 'v_no'?>"></div> Ложный <div class="v_no"></div> Прочее
                    </p>
                    <p>
                        Эпид.изв.№ <?= isset($c690)  ?  $c690 : '_____'?>С/л № <?= isset($c691)  ?  $c691 : '_____'?>Т/ф № <?= isset($c692)  ?  $c692 : '_____'?>
                    </p>
                    <p style="float: left; margin-right: 10px">
                        Отметка о госпитализации </p><div class="<?= isset($c644)  ?  'v_ok' : 'v_no'?>"></div>да <div class="<?= !isset($c644)  ?  'v_ok' : 'v_no'?>"></div>нет

                    <p>
                        <div class="<?= isset($c645)  ?  'v_ok' : 'v_no'?>"></div> активное посещение врачом п-ки № <?= isset($c693)  ?  $c693 : '_____'?> ССМП № _____
                    </p>
                    <p>
                        <?= isset($c694)  ?  $c694 : 'Дата__________ время_______'?> Принял____________________
                    </p>
                    <p>
                        <strong>Основной диагноз </strong>
                    <span style="text-decoration:underline"><?= isset($main_Diag_Name)  ?  $main_Diag_Name : ''?></span>
                    </p>
                    <p>
                        <strong>Сопутствующий диагноз </strong>
                        <span style="text-decoration:underline"><?= isset($s_Diag_Name)  ?  $s_Diag_Name : ''?></span>
                    </p>
                    <p>
                        <strong>Оказанная помощь</strong>

                        <?php foreach($druglist as $drug):?>
                            <span ><?= isset($drug['DrugComplexMnn_RusName'])  ?  $drug['DrugComplexMnn_RusName'] : ''?>, <?= isset($drug['DrugTorg_Name'])  ?  $drug['DrugTorg_Name'] : ''?>,
                            Кол-во: <?= isset($drug['CmpCallCardDrug_Kolvo'])  ?  $drug['CmpCallCardDrug_Kolvo'] : ''?>, Ед. изм.: <?= isset($drug['GoodsUnit_Name'])  ?  $drug['GoodsUnit_Name'] : ''?>;</span>
                        <?php endforeach?>
                    </p>
                    <p>
                        <strong>Осложнения</strong>
                        <div class="<?= isset($c82)  ?  'v_ok' : 'v_no'?>"></div> шок, <div class="<?= isset($c83)  ?  'v_ok' : 'v_no'?>"></div> кома, <div class="<?= isset($c84)  ?  'v_ok' : 'v_no'?>"></div> сердечная астма, <div class="<?= isset($c85)  ?  'v_ok' : 'v_no'?>"></div> эмболия, <div class="<?= isset($c90)  ?  'v_ok' : 'v_no'?>"></div>коллапс, <div class="<?= isset($c86)  ?  'v_ok' : 'v_no'?>"></div> отек легких, <div class="<?= isset($c87)  ?  'v_ok' : 'v_no'?>"></div> асфиксия, <div class="<?= isset($c88)  ?  'v_ok' : 'v_no'?>"></div> аспирация, <div class="<?= isset($c89)  ?  'v_ok' : 'v_no'?>"></div> о.кровотеч,
                    </p>
                    <p>
                        <div class="<?= isset($c91)  ?  'v_ok' : 'v_no'?>"></div> анурия, <div class="<?= isset($c94)  ?  'v_ok' : 'v_no'?>"></div> ОДН, <div class="<?= isset($c98)  ?  'v_ok' : 'v_no'?>"></div> энцефалопатия <div class="<?= isset($c92)  ?  'v_ok' : 'v_no'?>"></div> наруш. сердеч. ритма,
                    </p>
                    <p>
                        <div class="<?= isset($c93)  ?  'v_ok' : 'v_no'?>"></div> судороги, <div class="<?= isset($c99)  ?  'v_ok' : 'v_no'?>"></div> токсикоз,<div class="<?= isset($c95)  ?  'v_ok' : 'v_no'?>"></div> синдром полиорганной недостаточности <br/>
                        <div class="<?= isset($c100)  ?  'v_ok' : 'v_no'?>"></div> другое <?= isset($c100)  ?  $c100 : '____'?>
                    </p>
                </div>
                <div class="column" >
                    <p>
                        Медицинская документация <strong>Учетная форма № 110/у-м</strong>
                    </p>
                    <br/>
                    <p>
                        Бригада № {EmergencyTeamNum}, профиль <?= isset($EmergencyTeamSpec_Name) ? $EmergencyTeamSpec_Name : ''?>
                    </p>
                    <p>
                        СОСТАВ БРИГАДЫ:
                    </p>
                    <p>
                        Врач <?if(isset($c674)) echo $MPFio674?>
                    </p>
                    <p>
                        Старший бригады <?if(isset($c714)) echo $MPFio714?>
                    </p>
                    <p>
                        Фельдшер <?if(isset($c675)) echo $MPFio675?>
                    </p>
                    <p>
                        Фельдшер, м/с <?if(isset($c676)) echo $MPFio676?>
                    </p>
                    <p>
                        Водитель <?if(isset($c677)) echo $MPFio677?>
                    </p>
                    <p>
                        <div class="<?= isset($c635)  ?  'v_ok' : 'v_no'?>"></div> <strong>отказ от транспортировки для госпитализации</strong>
                    </p>
                    <p>
                        Возможные осложнения и последствия отказа в доступной для меня форме разъяснены
                    </p>
                    <p>
                        _______ ______________ 20______г. в ______ часов.
                    </p>
                    <p>
                        (число) (месяц)
                    </p>
                    <p>
                        ___________________________________________________
                    </p>
                    <p  style="float: left;margin-right: 5px">
                        <strong>Аллергические реакции в анамнезе:</strong></p>
                        <div class="<?= isset($c366) ? "v_ok" : "v_no"?>"></div> да; <div class="<?= isset($c367) ? "v_ok" : "v_no"?>"></div> нет
<div style="clear: both;"></div>
                    <p style="float: left; margin-right: 5px">
                        Осмотр <strong>на педикулез</strong>  </p><div class="<?= isset($c369) ? "v_ok" : "v_no"?>"></div>да; <div class="<?= isset($c370) ? "v_ok" : "v_no"?>"></div>нет
                    <div style="clear: both;"></div>
                    <p>
                        Тема беседы <span style="text-decoration:underline"><?if(isset($CmpCloseCard_Topic)) echo $CmpCloseCard_Topic?></span>
                    </p>
                    <p>
                        Время : <?if(isset($HelpDT)) echo $HelpDT?>
                    </p>
                    <p>
                        <span style="text-decoration:underline"><?if(isset($HelpAuto)) echo $HelpAuto?></span>
                    </p>
                    <p>
                        <span style="text-decoration:underline"><?if(isset($CmpCloseCard_ClinicalEff)) echo $CmpCloseCard_ClinicalEff?></span>
                    </p>
                </div>
<!--            </tr>-->
<!--            </tbody>-->
<!--        </table>-->
    </div>
<div style="clear: both"></div>
<div class="page pageLeft page2" style="position: relative;">
<table border="1" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td  rowspan="6">
            <p>
                <strong>Жалобы:</strong>
            </p>
                <span class="text">{Complaints}</span>
        </td>
        <td width="171" colspan="2" valign="top">
            <p>
                <strong>Общее состояние</strong>
            </p>
        </td>
        <td width="174" colspan="3" valign="top">
            <p>
                <strong>Мышечный тонус</strong>
            </p>
        </td>
        <td width="177" colspan="2" valign="top">
            <p>
                <strong>Сердцебиение </strong>
            </p>
        </td>
        <td rowspan="27" valign="top">
            <p>
                <strong>Дополнительные данные: (St/Localis)</strong><br/>
                <span style="text-decoration: underline"><?= isset($LocalStatus) ? $LocalStatus : ''?></span>
                <span style="text-decoration: underline"><?= isset($CmpCloseCard_AddInfo) ? $CmpCloseCard_AddInfo : ''?></span>
				<?=(isset($c28) ? '<p><strong>Отеки:</strong></p>'.$c28 : '')?>
				<?=(isset($c32) ? '<p><strong>Сыпь:</strong></p>'.$c32 : '')?>
            </p>
        </td>

    </tr>
    <tr>
        <td width="151" valign="top">
            <p>
                удовлетворительн
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c2) ? "X" : ''?>
        </td>
        <td width="174" colspan="2" valign="top">
            <p>
                нормальный D<? if(isset($c646))switch($c646){case '1': echo '>'; break;case '2':echo '<'; break;case '3':echo '='; break;}?>
                S
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c646) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                ритмичное
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c415) ? "X" : ''?>
        </td>

    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                ср. тяжести
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c3) ? "X" : ''?>
        </td>
        <td width="174" colspan="2" valign="top">
            <p>
                повышенный D<? if(isset($c647))switch($c647){case '1': echo '>'; break;case '2':echo '<'; break;case '3':echo '='; break;}?>
                S
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c647) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                аритмичное
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c416) ? "X" : ''?>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                тяжелое
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c4) ? "X" : ''?>
        </td>
        <td width="174" colspan="2" valign="top">
            <p>
                снижен D<? if(isset($c648))switch($c648){case '1': echo '>'; break;case '2':echo '<'; break;case '3':echo '='; break;}?>
                S
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c648) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                отсутствует
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c417) ? "X" : ''?>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                крайне тяжелое
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c382) ? "X" : ''?>
        </td>
        <td width="174" colspan="3" valign="top">
            <p align="center">
                <strong>Рефлексы</strong>
            </p>
        </td>
        <td width="177" colspan="2" valign="top">
            <p align="center">
                <strong>Тоны сердца</strong>
            </p>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                преагональное
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c381) ? "X" : ''?>
        </td>
        <td width="174" colspan="3" valign="top">
            <p>
                очаг. невр. симпт.
            </p>
        </td>
        <td width="151" valign="top">
            <p>
                ясные
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c52) ? "X" : ''?>
        </td>


    </tr>
    <tr>
        <td rowspan="12">
            <p>
                Анамнез<strong> </strong>(заболевания, жизни, акушерск.- гинекологич.,травматогенез):
            </p>
            <span class="text">{Anamnez}</span>
        </td>
        <td width="151" valign="top">
            <p>
                агональное
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c5) ? "X" : ''?>
        </td>
        <td width="174" colspan="3" valign="top">
            <p>
            <div class="<?= isset($c626) ? "v_ok" : 'v_no'?>"></div>нет <div class="<?= isset($c625) ? "v_ok" : 'v_no'?>"></div>есть
            </p>
        </td>
        <td width="151" valign="top">
            <p>
                усиление
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c418) ? "X" : ''?>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                клинич .смерть
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c6) ? "X" : ''?>
        </td>
        <td width="174" colspan="3" valign="top">
            <p>
                <strong>Менингеальн. знаки</strong>
            </p>
        </td>
        <td width="151" valign="top">
            <p>
                ослабление
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c419) ? "X" : ''?>
        </td>


    </tr>
    <tr>

        <td width="171" colspan="2" valign="top">
            <p>
                <strong>Поведение </strong>
            </p>
        </td>
        <td width="174" colspan="3" valign="top">
            <p>
            <div class="<?=$isMenen == 1 ? 'v_ok': 'v_no'?>"></div> нет <div class="<?=$isMenen == 2 ? 'v_ok': 'v_no'?>"></div>есть
            </p>
        </td>
        <td width="177" colspan="2" valign="top">
            <p>
                <strong>Шумы </strong>
            </p>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                спокойное
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c8) ? "X" : ''?>
        </td>
        <td width="174" colspan="3" valign="top">
            <p>
                <strong>Одышка </strong>
            </p>
        </td>
        <td width="177" colspan="2" valign="top">
            <p>
            <div class="<?= (!isset($c55) || !isset($c56) || !isset($c57)) ? "v_ok" : 'v_no'?>"></div>нет <div class="<?= (isset($c55) || isset($c56) || isset($c57)) ? "v_ok" : 'v_no'?>"></div>есть
            </p>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                возбужденное
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c9) ? "X" : ''?>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                нет
            </p>
        </td>
        <td width="23" align="center">
            <?= (!isset($c46) && !isset($c47) && !isset($c48)) ? "X" : ''?>
        </td>
        <td width="177" colspan="2" valign="top">
            <p>
                <strong>Язык </strong>
            </p>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                агрессивное
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c10) ? "X" : ''?>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                инспираторная
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c46) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                влажный
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c67) ? "X" : ''?>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                депрессивное
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c11) ? "X" : ''?>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                экспираторная
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c47) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                сухой
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c68) ? "X" : ''?>
        </td>


    </tr>
    <tr>

        <td width="171" colspan="2" valign="top">
            <p>
                <strong>Сознание </strong>
            </p>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                смешенная
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c48) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                чистый
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c69) ? "X" : ''?>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                ясное
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c13) ? "X" : ''?>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                ЧДД
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c393) ? $c393 : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                обложен
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c70) ? "X" : ''?>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                угнетено
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c383) ? "X" : ''?>
        </td>
        <td width="174" colspan="3" valign="top">
            <p>
                <strong>Дыхание </strong>
            </p>
        </td>
        <td width="151" valign="top">
            <p>
                следы прикуса
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c420) ? "X" : ''?>
        </td>


    </tr>
    <tr>

        <td width="151" valign="top">
            <p>
                отсутствует
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c16) ? "X" : ''?>
        </td>
        <td width="174" colspan="3" valign="top">
            <p>
                <div class="<?= isset($c394) ? "v_ok" : 'v_no'?>"></div> ритмич <div class="<?= isset($c395) ? "v_ok" : 'v_no'?>"></div>аритмич
            </p>
        </td>
        <td width="177" colspan="2" valign="top">
            <p>
                <strong>Ротоглотка</strong>
            </p>
        </td>


    </tr>
    <tr>

        <td width="171" colspan="2" rowspan="2" valign="top">
            <p>
                <strong>Оценка по шкале Глазго <?= isset($CmpCloseCard_Glaz) ? $CmpCloseCard_Glaz : '___'?> баллов</strong>
            </p>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                отсутствует
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c40) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                чистая
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c422) ? "X" : ''?>
        </td>


    </tr>
    <tr>
        <td >
            <p>
                Эпид. анамнез: <?= isset($c609) ? 'Нет' : ''?><?= isset($c610) ? $c610 : ''?>
            </p>
        </td>
        <td width="151" colspan="3" valign="top">
            <p>
                <strong>Аускультация </strong>
            </p>
        </td>
        <td width="151" valign="top">
            <p>
                гиперемия
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c423) ? "X" : ''?>
        </td>


    </tr>
    <tr>
        <td >
            <p>
                Посещение эпид. неблагополучных стран и регионов за 3 года:
            </p>
        </td>
        <td width="171" colspan="2" valign="top">
            <p>
                (E <?= isset($CmpCloseCard_e1) ? $CmpCloseCard_e1 : ''?> M <?= isset($CmpCloseCard_m1) ? $CmpCloseCard_m1 : ''?> V <?= isset($CmpCloseCard_v1) ? $CmpCloseCard_v1 : ''?>)
            </p>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                везикулярное
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c397) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                отек
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c424) ? "X" : ''?>
        </td>


    </tr>
    <tr>
        <td >
            <p>
                <div class="<?= isset($c612) ? "v_ok" : 'v_no'?>"></div> нет <div class="<?= isset($c613) ? "v_ok" : 'v_no'?>"></div> да <?= isset($c613) ? $c613 : ''?>
            </p>
        </td>
        <td width="171" colspan="2" valign="top">
            <p>
                <strong>Запах алкоголя</strong>
            </p>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                жесткое
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c398) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                налеты
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c425) ? "X" : ''?>
        </td>


    </tr>
    <tr>
        <td >
            <p style="float: left">
                Инф. заболев, в анамнезе: <div class="<?= isset($c615) ? "v_ok" : 'v_no'?>"></div> нет,<div class="<?= isset($c616) ? "v_ok" : 'v_no'?>"></div> гепатит «<?= isset($c616) ? $c616 : ' '?>», <div class="<?= isset($c617) ? "v_ok" : 'v_no'?>"></div>малярия.
            </p>
        </td>
        <td width="171" colspan="2" valign="top">
            <p>
                <div class="<?= isset($c386) ? 'v_ok' : 'v_no'?>"></div> нет <div class="<?= isset($c385) ? 'v_ok' : 'v_no'?>"></div>есть
            </p>
        </td>
        <td width="151" valign="top">
            <p>
                ослабленное D<? if(isset($c649) && isset($c696)) echo '=';elseif(isset($c649)) echo '>'; elseif(isset($c696)) echo '<';?>
                S
            </p>
        </td>
        <td width="24" colspan="2" align="center">
            <?= isset($c399) ? "X" : ''?>
        </td>
        <td width="177" colspan="2" valign="top">
            <p>
                <strong>Живот </strong>
            </p>
        </td>


    </tr>
    <tr>
        <td >
            <p>
            <div class="<?= isset($c618) ? "v_ok" : 'v_no'?>"></div> ТВС,<div class="<?= isset($c619) ? "v_ok" : 'v_no'?>"></div> ВИЧ,<div class="<?= isset($c620) ? "v_ok" : 'v_no'?>"></div> Другое: <?= isset($c620) ? $c620 : ' '?>
            </p>
        </td>
        <td width="171" colspan="2" valign="top">
            <p>
                <strong>Кожные покровы</strong>
            </p>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                бронхиальное
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c400) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                мягкий
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c72) ? "X" : ''?>
        </td>

    </tr>
    <tr>
        <td >
            <p>
                Инъекции, оперативные вмеш-ва за последние 6 мес
            </p>
        </td>
        <td width="151" valign="top">
            <p>
                нормальные
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c22) ? "X" : ''?>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                пуэрильное
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c401) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                напряжен
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c75) ? "X" : ''?>
        </td>

    </tr>
    <tr>
        <td >
            <p>
                <div class="<?= isset($c622) ? 'v_ok' : 'v_no'?>"></div> нет <div class="<?= isset($c623) ? 'v_ok' : 'v_no'?>"></div> да <?= isset($c623) ? $c623 : ''?>
            </p>
        </td>
        <td width="151" valign="top">
            <p>
                сухие
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c23) ? "X" : ''?>
        </td>
        <td width="174" colspan="3" valign="top">
            <p style="float: left">
                <strong>Хрипы</strong>
            <div class="<?= isset($c42) ? "v_ok" : 'v_no'?>"></div>нет <div class="<?= (isset($c43) || isset($c44) || isset($c1584) ) ? "v_ok" : 'v_no'?>"></div>есть
            </p>
        </td>
        <td width="151" valign="top">
            <p>
                безболезненный
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c73) ? "X" : ''?>
        </td>

    </tr>
    <tr>
        <td  valign="top">
            <p>
                Обследования: до лечения/после лечения
            </p>
        </td>
        <td width="151" valign="top">
            <p>
                влажные
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c387) ? "X" : ''?>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                сухие D<? if(isset($c679) && isset($c691)) echo '=';elseif(isset($c679)) echo '>'; elseif(isset($c691)) echo '<';?>
                S
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c43) ? "X" : ''?>
        </td>
        <td width="151" valign="top">
            <p>
                болезненный
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c76) ? "X" : ''?>
        </td>


    </tr>
    <tr>
        <td >
            <p>
                {CmpCloseCard_BegTreatDT} SatO2(до леч) {Sat}  глюкометрия {Gluck}
            </p>
        </td>
        <td width="151">
            <p>
                профузный пот
            </p>
        </td>
        <td width="20" valign="top">
            <p>
                <?= isset($c388) ? "X" : ''?>
            </p>
        </td>
        <td width="151" colspan="2">
            <p>
                влажные D<? if(isset($c680) && isset($c697)) echo '=';elseif(isset($c680)) echo '>'; elseif(isset($c697)) echo '<';?>
                S
            </p>
        </td>
        <td width="23" valign="top">
            <?= isset($c44) ? "X" : ''?>
        </td>
        <td width="151">
            <p>
                вздут
            </p>
        </td>
        <td width="26" align="center">
            <?= isset($c74) ? "X" : ''?>
        </td>


    </tr>
    <tr>
        <td >
            <p>
                {CmpCloseCard_EndTreatDT} SatO2(п/леч) {AfterSat} глюкометрия {EfGluck}
            </p>
        </td>
        <td width="151">
            <p>
                бледность
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c23) ? "X" : ''?>
        </td>
        <td width="151" colspan="2">
            <p>
                крепитация D<? if(isset($c681) && isset($c698)) echo '=';elseif(isset($c681)) echo '>'; elseif(isset($c698)) echo '<';?>
                S
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c402) ? "X" : ''?>
        </td>
        <td width="177" colspan="2" rowspan="2">
            <p>
                участие в акте дыхания
            </p>
            <p>
            <div class="<?= isset($isHale) && $isHale == 2 ? "v_ok" : "v_no"?>"></div>участвует <div class="<?= isset($isHale) && $isHale == 1 ? "v_ok" : "v_no"?>"></div>нет
            </p>
        </td>
        <td >
            <p style="float: left">
                Согласие на медицинское вмешательство получено </p><div class="<?= (isset($isSogl) && $isSogl == '2') ? "v_ok" : "v_no"?>"></div>

        </td>

    </tr>
    <tr>
        <td >
            <p>
                ЭКГ {Ekg1}
            </p>
        </td>
        <td width="151">
            <p>
                гиперемия
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c24) ? "X" : ''?>
        </td>
        <td width="151" colspan="2">
            <p>
                <strong>Перкуссия </strong>
            </p>
        </td>
        <td width="23" align="center">
        </td>
        <td  valign="top">
            <p style="float: left">
                Отказ от обследования </p><div class="<?= isset($c631) ? "v_ok" : "v_no"?>"></div> мед. помощи <div class="<?= isset($c632) ? "v_ok" : "v_no"?>"></div> от трансп <div class="<?= isset($c635) ? "v_ok" : "v_no"?>"></div>

        </td>

    </tr>
    <tr>
        <td >
		
        </td>
        <td width="151">
            <p>
                желтушность
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c25) ? "X" : ''?>
        </td>
        <td width="151" colspan="2">
            <p>
                звук легочный
            </p>
        </td>
        <td width="23" valign="top">
            <?= isset($c408) ? "X" : ''?>
        </td>
        <td width="177" colspan="2" rowspan="2">
            <p>
                кишечные шумы :
            </p>
            <p>
            <div class="<?= isset($c426) ? "v_ok" : 'v_no'?>"></div>есть <div class="<?= !isset($c426) ? "v_ok" : 'v_no'?>"></div>нет
            </p>
        </td>
        <td >
            <p style="float: left">
                мед. эвакуации  </p><div class="<?= isset($c633) ? "v_ok" : "v_no"?>"></div> переноски <div class="<?= isset($c634) ? "v_ok" : "v_no"?>"></div> др. <div class="<?= isset($c636) ? "v_ok" : "v_no"?>"><?= isset($c636) ? $c636 : ""?></div>

        </td>

    </tr>
    <tr>
        <td >
        </td>
        <td width="151">
            <p>
                цианоз
            </p>
        </td>
        <td width="20" valign="top">
            <p>
                <?= isset($c389) ? "X" : ''?>
            </p>
        </td>
        <td width="151" colspan="2">
            <p>
                коробочный D<? if(isset($c661) && isset($c699)) echo '=';elseif(isset($c661)) echo '>'; elseif(isset($c699)) echo '<';?>
                S
            </p>
        </td>
        <td width="23" valign="top">
            <?= isset($c409) ? "X" : ''?>
        </td>
        <td  rowspan="4" valign="top">
            <p>
                <strong>Эффективность проведенных меро</strong>
                <strong>приятий {CmpCloseCard_EndTreatDT}:</strong>
            </p>
            <p>
                АД {EfAD} ЧСС {EfChss} Пульс  {EfPulse} ЧДД {EfChd} Т° {EfTemperature}
            </p>
            <p>
                Оценка по шкале Глазго <?= isset($CmpCloseCard_GlazAfter) ? $CmpCloseCard_GlazAfter : ''?> баллов (E <?= isset($CmpCloseCard_e2) ? $CmpCloseCard_e2 : ''?> M <?= isset($CmpCloseCard_m2) ? $CmpCloseCard_m2 : ''?> V <?= isset($CmpCloseCard_v2) ? $CmpCloseCard_v2 : ''?>)

            </p>
        </td>

    </tr>
    <tr>
        <td >
			ЧСС {Chss} Пульс {Pulse} ЧДД {Chd} ритм {CmpCloseCard_Rhythm}
        </td>
        <td width="151">
            <p>
                акроцианоз
            </p>
        </td>
        <td width="20" align="center">
            <?=$isAcro == 2 ? "X" : ''?>
        </td>
        <td width="151" colspan="2">
            <p>
                притупление D<? if(isset($c663) && isset($c700)) echo '=';elseif(isset($c663)) echo '>'; elseif(isset($c700)) echo '<';?>
                S
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c410) ? "X" : ''?>
        </td>
        <td width="177" colspan="2" rowspan="2">
            <p>
                С-мы раздр. брюшины
            </p>
            <p>
            <div class="<?= isset($isHale) && $isHale == 2 ? "v_ok" : "v_no"?>"></div>нет <div class="<?= isset($isHale) && $isHale == 1 ? "v_ok" : "v_no"?>"></div>есть
            </p>
        </td>

    </tr>
    <tr>
        <td >
        </td>
        <td width="151">
            <p>
                мраморность
            </p>
        </td>
        <td width="20" align="center">
            <?=$isMramor == 2 ? "X" : ''?>

        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                <strong>Границы сердца</strong>
            </p>
        </td>
        <td width="23" align="center">
        </td>

    </tr>
    <tr>
        <td >
        </td>
        <td width="171" colspan="2">
            <p>
                <strong>Т° тела</strong>
                {Temperature}
            </p>
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                не изменены
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c413) ? "X" : ''?>
        </td>
        <td width="177" colspan="2">
            <p>
                <strong>Печень </strong>
            </p>
        </td>

    </tr>
    <tr>
        <td >
        </td>
        <td width="171" colspan="2">
            <p style="float: left">
                Отеки:  </p><div class="<?= isset($c29) ? 'v_ok' : 'v_no'?>"></div>нет <div class="<?= isset($c28) ? 'v_ok' : 'v_no'?>"></div>есть
        </td>
        <td width="151" colspan="2" valign="top">
            <p>
                расширены
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c412) ? "X" : ''?>
        </td>
        <td width="177" colspan="2" valign="top">
            <p>
            <div class="<?= isset($c427) ? "v_ok" : 'v_no'?>"></div>не пальпир <div class="<?= isset($c428) ? "v_ok" : 'v_no'?>"></div>пальпир
            </p>
        </td>
        <td >
            <p>
                ЭКГ {Ekg2}
            </p>
        </td>

    </tr>
    <tr>
        <td >
        </td>
        <td width="171" colspan="2">
            <p style="float: left">
                Сыпь: </p><div class="<?= isset($c33) ? 'v_ok' : 'v_no'?>"></div> нет <div class="<?= isset($c32) ? 'v_ok' : 'v_no'?>"></div>есть

        </td>
        <td width="174" colspan="2">
            <p>
                <strong>Пульс </strong><br/>
                Ритмичный
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c60) ? "X" : ''?>
        </td>
        <td width="177" colspan="2">
            <p>
            <div class="<?= isset($c429) ? "v_ok" : 'v_no'?>"></div>б/болезн. <div class="<?= isset($c430) ? "v_ok" : 'v_no'?>"></div>болез
            </p>
        </td>
        <td >
            <p>

            </p>
        </td>

    </tr>
    <tr>
        <td rowspan="7">
            <p>
                Примечание
            </p>
            <span class="text">{DescText}</span>
        </td>
        <td width="171" colspan="2">
            <p>
                <strong>Зрачки </strong>
            </p>
        </td>
        <td width="151" colspan="2">
            <p>
                норм наполн
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c59) ? "X" : ''?>
        </td>
        <td width="177" colspan="2">
            <p style="float: left">
                Рвота: </p><div class="<?= isset($IsVomit) && $IsVomit == 1 ? "v_ok" : "v_no"?>"></div>нет <div class="<?= isset($IsVomit) && $IsVomit == 2 ? "v_ok" : "v_no"?>"></div>есть

        </td>
        <td >
        </td>

    </tr>
    <tr>

        <td width="151">
            <p>
                нормальные D&nbsp;<? if(isset($c18))switch($c18){case '1': echo '>'; break;case '2':echo '<'; break;case '3':echo '='; break;}?>&nbsp;S
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c18) ? "X" : ''?>
        </td>
        <td width="151" colspan="2">
            <p>
                напряженный
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c62) ? "X" : ''?>
        </td>
        <td width="177" colspan="2">
            <p>
                <strong>Наруш. диуреза</strong>
            </p>
        </td>
        <td >
        </td>

    </tr>
    <tr>

        <td width="151">
            <p>
                широкие D&nbsp;<? if(isset($c19))switch($c19){case '1': echo '>'; break;case '2':echo '<'; break;case '3':echo '='; break;}?>&nbsp;S
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c19) ? "X" : ''?>
        </td>
        <td width="151" colspan="2">
            <p>
                слабого наполн.
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c63) ? "X" : ''?>
        </td>
        <td width="177" colspan="2">
            <p>
            <div class="<?= isset($IsDiuresis) && $IsDiuresis == 1 ? "v_ok" : "v_no"?>"></div>нет <div class="<?= isset($IsDiuresis) && $IsDiuresis == 2 ? "v_ok" : "v_no"?>"></div> есть
            </p>
        </td>
        <td  rowspan="5" valign="top">
            <p>
                <strong>Результат оказания скорой медицинской помощи</strong>
                :
            </p>
            <p>
                <div class="<?= isset($c106) ? "v_ok" : 'v_no'?>"></div> улучшение; <div class="<?= isset($c107) ? "v_ok" : 'v_no'?>"></div> без эффекта; <div class="<?= isset($c108) ? "v_ok" : 'v_no'?>"></div> ухудшение;
            </p>
            <p>
                <div class="<?= isset($c684) ? "v_ok" : 'v_no'?>"></div> летальный. исход <div class="<?= (isset($c685) && $c685 == '1') ? "v_ok" : 'v_no'?>"></div> в присутствии (на месте);
            </p>
            <p>
                <div class="<?= (isset($c685)&& $c685 == '2' )? "v_ok" : 'v_no'?>"></div> в авто при транспортировке;
            </p>
            <p align="center">
                время констатации смерти <?= isset($c686) ? $c686 : ''?>
            </p>
        </td>

    </tr>
    <tr>

        <td width="151">
            <p>
                узкие D&nbsp;<? if(isset($c20))switch($c20){case '1': echo '>'; break;case '2':echo '<'; break;case '3':echo '='; break;}?>&nbsp;S
            </p>
        </td>
        <td width="20" align="center">
            <?= isset($c20) ? "X" : ''?>
        </td>
        <td width="151" colspan="2">
            <p>
                нитевидный
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c64) ? "X" : ''?>
        </td>
        <td width="177" colspan="2">
            <p>
                <strong>Наруш. дефекации</strong>
            </p>
        </td>

    </tr>
    <tr>
        <td width="171" colspan="2">
            <p>
                <strong>Реакция на свет</strong>
            </p>
        </td>
        <td width="151" colspan="2">
            <p>
                аритмичный
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c61) ? "X" : ''?>
        </td>
        <td width="177" colspan="2">
            <p>
            <div class="<?= isset($IsDefecation) && $IsDefecation == 1 ? "v_ok" : "v_no"?>"></div>нет <div class="<?= isset($IsDefecation) && $IsDefecation == 2 ? "v_ok" : "v_no"?>"></div> есть
            </p>
        </td>

    </tr>
    <tr>

        <td width="171" colspan="2">
            <div class="<?= isset($isLight) && $isLight == 2 ?  'v_ok' : 'v_no'?>"></div> есть <div class="<?= isset($isLight) && $isLight == 1 ?  'v_ok' : 'v_no'?>"></div>нет
        </td>
        <td width="151" colspan="2">
            <p>
                отсутствует
            </p>
        </td>
        <td width="23" align="center">
            <?= isset($c65) ? "X" : ''?>
        </td>
        <td width="177" colspan="2">
            <p>
                <strong>Травмы, повреждения</strong>
            </p>
        </td>

    </tr>
    <tr>

        <td width="171" colspan="2">
            <p>
                <strong>Нистагм</strong>
            </p>
        </td>
        <td width="174" colspan="3">
            <p>
                <strong>ЧСС</strong>
                {EfChss}
            </p>
        </td>
        <td width="177" colspan="2">

            <div class="<?= isset($IsTrauma) && $IsTrauma == 2 ? "v_ok" : "v_no"?>"></div> есть <div class="<?= isset($IsTrauma) && $IsTrauma == 1 ? "v_ok" : "v_no"?>"></div>нет

        </td>

    </tr>
    <tr>
        <td >
            <p>
                Ф.И.О., подпись медработника:
                {Doc}
            </p>
        </td>
        <td width="171" colspan="2" valign="top">

            <div class="<?= isset($isNist) && $isNist == 2 ?  'v_ok' : 'v_no'?>"></div> есть <div class="<?= isset($isNist) && $isNist == 1 ?  'v_ok' : 'v_no'?>"></div>нет

        </td>
        <td width="174" colspan="3">
            <p>
                <strong>АД</strong>
                {AD}
            </p>
        </td>
        <td width="177" colspan="2">
            <p>
                <strong>Др. симптомы</strong>
            </p>
        </td>
        <td >
            <p>
                Карта проверена:
				{DocCid}
            </p>
        </td>

    </tr>
    <tr>
        <td >
        </td>
        <td width="171" colspan="2" align="center">
        </td>
        <td width="174" colspan="3">
            <p>
                <strong>АД (рабочее)</strong>
                {WorkAD}
            </p>
        </td>
        <td width="177" colspan="2">
            <p>
               <span class="text">{OtherSympt}</span>

            </p>
        </td>
        <td >
            <p>
                Ф.И.О. должность, подпись проверяющего

            </p>
        </td>

    </tr>
    </tbody>
</table>
</div>
<!--</div>-->

</body>
</html>

