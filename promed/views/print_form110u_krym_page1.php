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
            height: 25cm;
            float: left;
            display: block;
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

<div class="page pageLeft page1">


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
            улица <?= isset($Street)  ?  $Street : ''?>
        </p>
        <p>
			дом <?php
			if ( !empty($secondStreetName)) { ?>
				{secondStreetName}
			<?php } else{ ?>
				{House}
			<?php } ?>
			, корп. <?= isset($Korpus)  ?  $Korpus : ''?> кв. <?= isset($Office)  ?  $Office : ''?> комн.<?= isset($Room)  ?  $Room : ''?>
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

            foreach(str_split($BirthDay) as $simvol) {
                ?><div class="v_no big"><?=$simvol?></div><?
            }} else {
            for ($i=0; $i<10; $i++) { ?><div class="v_no big"></div><?
            }} ?>

        Возраст <?= (isset($AgePS) && $AgePS>0)  ?  $AgePS : $Age?>

        <?= isset($AgeTypeValue)  ?  $AgeTypeValue : ''?>
        Пол <div class="<?= isset($Sex_id) && $Sex_id == 1 ?  'v_ok' : 'v_no'?>"></div>  М <div class="<?= isset($Sex_id) && $Sex_id == 2 ?  'v_ok' : 'v_no'?>"></div>  Ж
        <div style="clear: both"></div>

        <p style="float:left; padding-right: 5px">Паспорт (св. о рожд):</p>

        <? if(isset($Document_Ser) && strlen($Document_Ser) > 0 && $Document_Ser != '&nbsp;') {

            foreach(preg_split('//u',$Document_Ser,-1,PREG_SPLIT_NO_EMPTY) as $simvol) {
            ?><div class="v_no big"><?= $simvol ?></div><?
        }}?>

        <?

        if(isset($Document_Num) && strlen($Document_Num) > 0 && $Document_Num != '&nbsp;') {

            foreach(str_split($Document_Num) as $simvol) {
                ?><div class="v_no big"><?= $simvol ?></div><?
        }} ?>

        <? if(!isset($Document_Ser) && !isset($Document_Num)) {

            for ($i=0; $i<10; $i++) { ?><div class="v_no big"></div><?
        }} ?>

        <div style="clear: both"></div>
        <p style="float:left">
            Полис ОМС  </p><?if(isset($Federal_Num) && strlen($Federal_Num) > 0 && $Federal_Num != '&nbsp;') : foreach(str_split($Federal_Num) as $simvol):?><div class="v_no big"><?=$simvol?></div><?endforeach?><?endif?>
        <div style="clear: both"></div>
        <p>
            <strong>СМО</strong>
            <?= isset($Org_Name) ? $Org_Name : "" ?> <strong>Дата выдачи </strong> <?= isset($Polis_begDate) ? $Polis_begDate : "" ?>
        </p>
        <p>
            <strong>Гражданство</strong>
            __________________________________________
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
        <div class="<?= isset($c641)  ?  'v_ok' : 'v_no'?>"></div> в п/о больницы, травм.п <?= (isset($c641) && isset($c689))  ? $OrgNick689   : '____'?>
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
            <?= isset($s_Diag_Name)  ?  $s_Diag_Name : ''?>
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
            <strong>Оказанная помощь:</strong>
            <?php foreach($druglist as $drug):?>
                <span ><?= isset($drug['DrugComplexMnn_RusName'])  ?  $drug['DrugComplexMnn_RusName'] : ''?>, <?= isset($drug['DrugTorg_Name'])  ?  $drug['DrugTorg_Name'] : ''?>,
                            Кол-во: <?= isset($drug['CmpCallCardDrug_Kolvo'])  ?  $drug['CmpCallCardDrug_Kolvo'] : ''?>, Ед. изм.: <?= isset($drug['GoodsUnit_Name'])  ?  $drug['GoodsUnit_Name'] : ''?>;</span>
            <?php endforeach?>
        </p>
        <p>
            Время : <?if(isset($HelpDT)) echo $HelpDT?>
        </p>
        <p>
            <span style="text-decoration:underline"><?if(isset($HelpAuto)) echo $HelpAuto?></span>
        </p>
    </div>

</div>



</body>
</html>

