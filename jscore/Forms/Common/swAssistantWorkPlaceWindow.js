/**
* АРМ лаборанта, регистратуры лаборатории, пункта забора
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    Common
* @access      public
* @autor        Dmitry Vlasenko
* @copyright    Copyright (c) 2014 Swan Ltd.
* @version    12.03.2014
*/
sw.Promed.swAssistantWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm, {
    title: langs('<b>Рабочее место</b>'),
    modal: false,
    shim: false,
    maximized: true,
    plain: true,
    layout: 'fit',
    buttonAlign: 'right',
    closable: true,
    closeAction: 'hide',
    useUecReader: true,
	formMode: false,
	isFormIfa: function() {
		return this.formMode == 'ifa';
	},
	setFormMode: function(mode) {
		var win = this;
		if(win.formMode) {
			win.disableMode(win.formMode);
		}
		if(mode) {
			win.enableMode(mode);
		}
		win.formMode = mode;
		win.doSearch();
	},
	enableMode: function(mode) {
		var win = this;
		switch (mode) {
			case 'ifa':
				win.setVisibleIfaFields(true);
				win.formActions.methodsIFA.getStore().load();
				win.formActions.analyzerTestIFA.getStore().load();
				win.tabletGrid.loadData();
				break;
		}
	},
	disableMode: function(mode) {
		var win = this;
		switch (mode) {
			case 'ifa':
				win.setVisibleIfaFields(false);
				break;
		}
	},
    getDataFromUec: function(uecData, person_data) {
        var win = this;
        if (this.labMode == 0) {
            var cm = this.GridPanel.getColumnModel();
            var index = cm.findColumnIndex('Person_ShortFio');
            var id = cm.getColumnId(index);
            var column = cm.getColumnById(id);
            if (column && column.filter) {
                column.filter.setValue(uecData.surName+' '+uecData.firName[0]+'.'+uecData.secName[0]+'.');
                win.doSearch();
            }
        } else {
            var cm = this.LabSampleGridPanel.getColumnModel();
            var index = cm.findColumnIndex('Person_ShortFio');
            var id = cm.getColumnId(index);
            var column = cm.getColumnById(id);
            if (column && column.filter) {
                column.filter.setValue(uecData.surName+' '+uecData.firName[0]+'.'+uecData.secName[0]+'.');
                win.doSearch();
            }
        }
    },
    id: 'swAssistantWorkPlaceWindow',
    stateful: false,
    barCodeIsFocused: false,
    onBeforeHide: function()
    {
        var win = this;
        if(win.intervalCheckSamples)
        {
            clearInterval(win.intervalCheckSamples);
        }
    },
    buttons: [],
    printLabSmplList: function(options) {
        if ( typeof options != 'object' ) {
            options = new Object();
        }

        var win = this;
        var grid = this.GridPanel.getGrid();

        var s = "";
        grid.getSelectionModel().getSelections().forEach(function (el) {
            if (!Ext.isEmpty(el.data.EvnLabRequest_id)) {
                if (!Ext.isEmpty(s)) {
                    s = s + ",";
                }
                s = s + el.data.EvnLabRequest_id;
            }
        });

        if (!Ext.isEmpty(s)) {
            var Report_Params = '&s=' + s + '&paramLpu=' + getGlobalOptions().lpu_id,
                Report_Format = '',
				Report_FileName = (Ext.globalOptions.lis.use_postgresql_lis ? 'EvnLabSmpl_List_pg' : 'EvnLabSmpl_List') + '.rptdesign';

            switch (Ext.globalOptions.lis.list_of_samples_print_method) {
                case '2':
					Report_Format = 'html';
                    break;
                case '3':
					Report_Format = 'pdf';
                    break;
                default:
					Report_Format = 'xls';
                    break;
            }

            printBirt({
                'Report_FileName': Report_FileName,
                'Report_Params': Report_Params,
                'Report_Format': Report_Format
            });
        }

        return false;
    },
    // <- https://redmine.swan.perm.ru/issues/106759
    /**
     * @author Gubaidullin Robert
     * @email borisworking@gmail.com
     * @copyright Copyright (c) 2017 Emsis
     */
    JsBarcodeInit: function() {
        if(document.body.className.search("x-no-print-body") == -1) {
            document.body.classList.add('x-no-print-body');
        } else {
            document.body.classList.remove('x-no-print-body');
        }

        var elements = document.getElementsByClassName('x-js-barcode');
        while(elements.length > 0) {
            elements[0].parentNode.removeChild(elements[0]);
        }
    },
    JsBarcode: function(panel) {
        if(typeof(jsPrintSetup) !== 'undefined')
        {
            var win = this;
            win.JsBarcodeInit();

            switch(panel) {
                // заявки
                case 'GridPanel':
                    var printSelected = this.GridPanel.getGrid().getSelectionModel().getSelections();
                    win.JsBarcodeData(printSelected, panel);
                break;

                // пробы
                case 'LabSampleGridPanel':
                    var printSelected = this.LabSampleGridPanel.getGrid().getSelectionModel().getSelections();
                    if(Ext.globalOptions.lis.ZebraUsluga_Name) {
                        // 1. Создаем массив с выбранными пробами
                        var labSample = [];
                        for(var k in printSelected) {
                            if(typeof(printSelected[k]) == 'object') {
                                // печать только со штрихкодом
                                if(printSelected[k].get('EvnLabRequest_FullBarCode') === null)
                                {
                                    printSelected.splice(k, 1);
                                } else {
                                    labSample[k] = printSelected[k].get('EvnLabSample_id');
                                }
                            }
                        }

                        var labSamples = Ext.util.JSON.encode(labSample);

                        // 2. Выбираем услуги в пробах
                        Ext.Ajax.request({
                            url: '/?c=EvnLabSample&m=getSampleUsluga',
                            params: {EvnLabSample_id:labSamples},
                            callback: function(opt, success, response) {
                                var response = Ext.util.JSON.decode(response.responseText);

                                var usluga = [];
                                if(response.length > 0) {

                                    // Условие: если > 1 услуги в одной пробе, то не печатаем
                                    var result = {};
                                    response.map(function(item) {
                                        var itemPropertyName = item['EvnLabSample_id'];
                                        if (itemPropertyName in result) {
                                            delete result[itemPropertyName];
                                        } else {
                                            result[itemPropertyName] = item;
                                        }
                                    });

                                    var size = 0, key;
                                    for (key in result) {
                                        if (result.hasOwnProperty(key)) size++;
                                    }

                                    if(size > 0) {
                                        for(i=0; i<labSample.length; i++) {
                                            if(typeof result[labSample[i]] !== 'undefined' && labSample[i] == result[labSample[i]].EvnLabSample_id) {
                                                usluga[labSample[i]] = result[labSample[i]].ResearchName;
                                            } else {
                                                usluga[labSample[i]] = null;
                                            }
                                        }
                                    }

                                }

                                win.JsBarcodeData(printSelected, panel, usluga);
                            }
                        });
                    } else {
                        win.JsBarcodeData(printSelected, panel);
                    }
                break;

                default:
                    Ext.MessageBox.getDialog().getEl().setStyle('z-index','80000');
                    Ext.Msg.alert('Ошибка печати', 'В этой форме печать невозможна');
                break;
            }

        } else {
            sw.swMsg.alert(langs('Ошибка'), langs('Установите расширение <a href="https://addons.mozilla.org/ru/firefox/addon/js-print-setup/" target="_blank">JS Print Setup</a>'));
        }
    },
    JsBarcodeData: function(printSelected, panel, uslugaSample) {
        var win = this;
        var print = [];
        for(var k in printSelected) {
            if (typeof(printSelected[k]) == 'object') {

                // проверяем пустые ли штрихкоды
                if(panel == 'GridPanel' && printSelected[k].get('EvnLabRequest_FullBarCode') === null) {
                    printSelected.splice(k, 1);
                } else if(panel == 'LabSampleGridPanel' && printSelected[k].get('EvnLabSample_BarCode') === null){
                    printSelected.splice(k, 1);
                } else {

                    // штрихкоды
                    if (panel == 'GridPanel') {
                        var barcodes = printSelected[k].get('EvnLabRequest_FullBarCode').split(',');
                    } else if (panel == 'LabSampleGridPanel') {
                        var barcodes = [printSelected[k].get('EvnLabSample_BarCode')];
                    }

                    var countBarcode = barcodes.length;

                    for(var i in barcodes) {
                        if (typeof(barcodes[i]) == 'string') {

                            // 1. штрихкод
                            if(panel == 'GridPanel') {
                                var barcode = barcodes[i].replace(/\s/g,'').split(':')[1];
                            } else {
                                var barcode = barcodes[i].replace(/\s/g,'')
                            }

                            // 2. Наименование службы
                            if(Ext.globalOptions.lis.ZebraServicesName)
                            {
                                // В пункт забора
                                var medNick = printSelected[k].get('MedService_Nick');
                                if(medNick !== undefined && medNick !== "")
                                {
                                    var serviceList = medNick.split('<br />');
                                    if(serviceList.length > 0) {
                                        var service = serviceList[i] !== "" ? serviceList[i] : null;
                                    } else {
                                        var service = null;
                                    }
                                }

                                // В заявках и пробах
                                else {
                                    var medNick = getGlobalOptions().CurMedService_Name;
                                    if (medNick !== undefined && medNick != null && medNick !== "") {
                                        var service = medNick !== "" ? medNick : null;
                                    } else {
                                        var service = null;
                                    }
                                }
                            } else {
                                var service = null;
                            }

                            // 3. ФИО пациента
                            if(Ext.globalOptions.lis.ZebraFIO) {
                                var fio = printSelected[k].get('Person_ShortFio') !== "" ? printSelected[k].get('Person_ShortFio') : null;
                            } else {
                                var fio = null;
                            }

                            // 4. Кем направлен
                            if(Ext.globalOptions.lis.ZebraDirect_Name) {
                                var direction = printSelected[k].get('PrehospDirect_Name') !== "" ? printSelected[k].get('PrehospDirect_Name') : null;
                            } else {
                                var direction = null;
                            }

                            // 5. Услуга
                            if(Ext.globalOptions.lis.ZebraUsluga_Name)
                            {
                                // В заявках
                                if(panel == 'GridPanel')
                                {
                                    if(printSelected[k].get('EvnLabRequest_UslugaName') !== undefined && printSelected[k].get('EvnLabRequest_UslugaName') !== "")
                                    {
                                        var uslugaList = [];
                                        var uslugaName = printSelected[k].get('EvnLabRequest_UslugaName');
                                        if (!Ext.isEmpty(uslugaName) && uslugaName[0] == "[" && uslugaName[uslugaName.length-1] == "]") {
                                            // разджейсониваем
                                            var uslugas = Ext.util.JSON.decode(uslugaName);
                                            for(var ku in uslugas) {
                                                if (uslugas[ku].UslugaComplex_Name) {
                                                    uslugaList.push(uslugas[ku].UslugaComplex_Name);
                                                }
                                            }
                                        } else {
                                            uslugaList = uslugaName.split('<br>');
                                        }
                                        if(countBarcode === uslugaList.length) {
                                            var usluga = uslugaList[i] !== "" ? uslugaList[i] : null;
                                        } else {
                                            var usluga = null;
                                        }
                                    } else {
                                        var usluga = null;
                                    }
                                }

                                else

                                // В пробах
                                if(panel == 'LabSampleGridPanel')
                                {
                                    if(uslugaSample.length > 0) {
                                        var sample_id = printSelected[k].get('EvnLabSample_id');
                                        var usluga = uslugaSample[sample_id];
                                    } else {
                                        var usluga = null;
                                    }
                                }
                            } else {
                                var usluga = null;
                            }

                            var obj = {
                                barcode: barcode,
                                service: service,
                                fio: fio,
                                direction: direction,
                                usluga: usluga
                            };

                            print.push(obj);
                        }
                    }
                }
            }
        }

        console.log('print', print);

        win.JsBarcodeEngine(print);
    },
    JsBarcodeEngine: function(print) {
        var win = this;
        var div = document.createElement('div');
            div.className = "x-js-barcode";
            document.body.appendChild(div);

        var option = {}, printer = {};

        printer = {top:1,bottom:1,left:1,right:1};
        printer.size = Number(Ext.globalOptions.lis.barcode_size);
        printer.height = String(Ext.globalOptions.lis.barcode_size).substring(0, 2);
        printer.width = String(Ext.globalOptions.lis.barcode_size).substring(2);
        printer.count = Ext.globalOptions.lis.ZebraPrintCount !== undefined ? Number(Ext.globalOptions.lis.ZebraPrintCount) : 1;
        option.barcodeText = Ext.globalOptions.lis.ZebraSampleNumber;
        option.barcodeFormat = Ext.globalOptions.lis.barcode_format;
        option.fontFamily = 'monospace';

        for (var x in print) {
            if(print[x].barcode !== undefined) {

            var svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                svg.setAttribute("id", "barcode_"+print[x].barcode);
                div.appendChild(svg);

            var count = 0;
            if(print[x].service   !== null) count++;
            if(print[x].fio       !== null) count++;
            if(print[x].direction !== null) count++;
            if(print[x].usluga    !== null) count++;

            switch(printer.size)
            {
                case 2030:
                    option.width = 4.4; option.height = 99; option.top = 161; option.left = 65;
                    option.x = 40; option.y = 36;
                break;

                case 2040:
                    option.width = 5.8; option.height = 95; option.top = 175; option.left = 85;
                    option.x = 40; option.y = 40;
                    printer.top = 2;
                    printer.bottom = 0;
                break;

                case 2540:
                    option.width = 6.2; option.height = 154; option.top = 206; option.left = 60;
                    option.x = 0; option.y = 46;
                break;

                case 3050:
                    option.width = 8; option.height = 184; option.top = 256; option.left = 70;
                    option.x = 0; option.y = 56;
                break;

                default:
                    Ext.MessageBox.getDialog().getEl().setStyle('z-index','80000'); //Т.к окно иногда бывает на заднем фоне
                    Ext.Msg.alert('Ошибка печати', 'Для принтера Zebra необходимо указать в настройках печати штрихкода ширину и высоту наклейки, выбирая из трех значений: 20x30, 20х40, 25x40 и 30х50');
                break;
            }

            switch(count)
            {
                case 0:
                case 1:
                    option.height += option.y + option.y; option.top -= option.y + option.y;
                break;

                case 2:
                    option.height += option.y; option.top -= option.y;
                break;
            }

            if(option.barcodeText === false)
            {
                option.height += option.y;
                option.top -= option.y - option.y;
            }

            if(    print[x].fio       === null
                || print[x].service   === null
                || print[x].direction === null
                || print[x].usluga    === null
              )
            {
                option.height += option.y;
                option.top -= option.y;
            }

            if(    option.barcodeText === false
                && print[x].fio       === null
                && print[x].service   === null
                && print[x].direction === null
                && print[x].usluga    === null
              )
            {
                option.height += option.y;
                option.top -= option.y;
            }

            option.fontSize = option.y;
            option.data = print[x];

            win.JsBarcodeSVG(option);
        }}

        win.JsBarcodePrint(printer);
    },
    JsBarcodeSVG: function(option) {
        var barcode = String(option.data.barcode);
        JsBarcode('#barcode_'+barcode, barcode, {
            width: option.width,
            height: option.height,
            marginTop: option.top,
            marginLeft: option.left,
            displayValue:option.barcodeText,
            textAlign: "center",
            fontSize:option.fontSize,
            fontFamily:option.fontFamily,
            format:"CODE"+option.barcodeFormat,
        });

        var y = option.y; svgNS = "http://www.w3.org/2000/svg";

        if(option.data.service !== null)
        {
            var strService = document.createElementNS(svgNS,"text");
            strService.setAttributeNS(null,"x",option.x);
            strService.setAttributeNS(null,"y",option.y);
            strService.setAttributeNS(null,"font-family",option.fontFamily);
            strService.setAttributeNS(null,"font-size",option.fontSize);
            var textService = document.createTextNode(option.data.service);
            strService.appendChild(textService);
            document.getElementById('barcode_'+barcode).appendChild(strService);
            option.y += y;
        }

        if(option.data.fio !== null)
        {
            var strFio = document.createElementNS(svgNS,"text");
            strFio.setAttributeNS(null,"x",option.x);
            strFio.setAttributeNS(null,"y",option.y);
            strFio.setAttributeNS(null,"font-family",option.fontFamily);
            strFio.setAttributeNS(null,"font-size",option.fontSize);
            var textFio = document.createTextNode(option.data.fio);
            strFio.appendChild(textFio);
            document.getElementById('barcode_'+barcode).appendChild(strFio);
            option.y += y;
        }

        if(option.data.direction !== null)
        {
            var strDirection = document.createElementNS(svgNS,"text");
            strDirection.setAttributeNS(null,"x",option.x);
            strDirection.setAttributeNS(null,"y",option.y);
            strDirection.setAttributeNS(null,"font-family",option.fontFamily);
            strDirection.setAttributeNS(null,"font-size",option.fontSize);
            var textDirection = document.createTextNode(option.data.direction);
            strDirection.appendChild(textDirection);
            document.getElementById('barcode_'+barcode).appendChild(strDirection);
            option.y += y;
        }

        if(option.data.usluga !== null)
        {
            var strUsluga = document.createElementNS(svgNS,"text");
            strUsluga.setAttributeNS(null,"x",option.x);
            strUsluga.setAttributeNS(null,"y",option.y);
            strUsluga.setAttributeNS(null,"font-family",option.fontFamily);
            strUsluga.setAttributeNS(null,"font-size",option.fontSize);
            var textUsluga = document.createTextNode(option.data.usluga);
            strUsluga.appendChild(textUsluga);
            document.getElementById('barcode_'+barcode).appendChild(strUsluga);
            option.y += y;
        }
    },
    JsBarcodePrint: function(printer) {
        var win = this;
        var printerDefault = jsPrintSetup.getPrinter();
        var printers = jsPrintSetup.getPrintersList();
        var arr = printers.split(',');
        var zebra = arr.filter(function(v) {
            return v == 'ZDesigner GK420t';
        });

        if(zebra.length > 0) {
            jsPrintSetup.setPrinter('ZDesigner GK420t');
            jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
            jsPrintSetup.setOption('marginTop', printer.top);
            jsPrintSetup.setOption('marginBottom', printer.bottom);
            jsPrintSetup.setOption('marginLeft', printer.left);
            jsPrintSetup.setOption('marginRight', printer.right);
            jsPrintSetup.setOption('headerStrLeft', '');
            jsPrintSetup.setOption('headerStrCenter', '');
            jsPrintSetup.setOption('headerStrRight', '');
            jsPrintSetup.setOption('footerStrLeft', '');
            jsPrintSetup.setOption('footerStrCenter', '');
            jsPrintSetup.setOption('footerStrRight', '');
            jsPrintSetup.setOption('title', '');
            jsPrintSetup.setOption('paperHeight', printer.height);
            jsPrintSetup.setOption('paperWidth', printer.width);
            jsPrintSetup.setOption('shrinkToFit', 1);
            jsPrintSetup.setOption('numCopies', printer.count);
            jsPrintSetup.setShowPrintProgress(true);
            jsPrintSetup.setSilentPrint(true);
            jsPrintSetup.print();
        } else {
            sw.swMsg.alert(langs('Ошибка'), 'Принтер с возможностью автоматической печати не обнаружен');
        }

        win.JsBarcodeInit();
    },
    // https://redmine.swan.perm.ru/issues/106759 ->
    printonZebra: function(panel) {
		    var copyCount = Ext.globalOptions.lis.ZebraPrintCount !== undefined ? Number(Ext.globalOptions.lis.ZebraPrintCount) : 1;
            //Считывание информации с гридов для печати
            var print = [];
            if (panel == 'GridPanel') {
                    var recsselect = this.GridPanel.getGrid().getSelectionModel().getSelections();
            } else if (panel == 'LabSampleGridPanel') {
                    var recsselect = this.LabSampleGridPanel.getGrid().getSelectionModel().getSelections();
            }
            for (var k in recsselect) {
                    if (typeof(recsselect[k]) == 'object') {
                            if (panel == 'GridPanel') {
                                    var arr = recsselect[k].get('EvnLabRequest_FullBarCode').trim().split(','); //штрихкодов может быть несколько
                            } else if (panel == 'LabSampleGridPanel') {
                                    var arr = [recsselect[k].get('EvnLabSample_BarCode')].trim();
                            }
                            for (var i in arr) {
                                    if (typeof(arr[i]) == 'string' && arr[i].trim()) {
                                            arr[i] = arr[i].trim();
                                            //console.log('Service',getGlobalOptions().CurMedService_Name);
                                            //console.log('FIO', recsselect[k].get('Person_ShortFio'));
                                            //console.log('BarCode',arr[i].replace(/\s/g,'').split(':')[1]);
                                            //console.log('PrehospDirect_Name', recsselect[k].get('PrehospDirect_Name'));
                                            var CurMedService_Name_base = getGlobalOptions().CurMedService_Name;
                                            if (CurMedService_Name_base != null && CurMedService_Name_base != '') {
                                                this.CurMedService_Name = getGlobalOptions().CurMedService_Name;
                                            }

                                            var obj = {
                                                    barcode_size: Ext.globalOptions.lis.barcode_size,
                                                    Service: Ext.globalOptions.lis.ZebraServicesName? this.CurMedService_Name:null,
                                                    FIO: Ext.globalOptions.lis.ZebraFIO?recsselect[k].get('Person_ShortFio'):null,
                                                    BarCode: panel == 'GridPanel'?arr[i].replace(/\s/g,'').split(':')[1]:arr[i].replace(/\s/g,''),
                                                    SampleNumber: Ext.globalOptions.lis.ZebraSampleNumber,
                                                    PrehospDirect_Name: Ext.globalOptions.lis.ZebraDirect_Name?recsselect[k].get('PrehospDirect_Name'):null,
                                                    barcode_format: Ext.globalOptions.lis.barcode_format
                                            };

                                            for (var i = 0; i < copyCount; i++)
                                                print.push(obj); //массив данных для печати на Zebra
                                    }
                            }
                    }
            }
            //Перевод на язык принтера зебра
            var sup = 0;//счетчик пустых полей сверху штрихкода
            var sdown = 0;//счетчик пустых полей снизу штрихкода
            var str = 'Y';//необходимо или нет ставить численное значение штрихкода
            var prnstr = '^XA^CWQ,E:ARI000.FNT^XZ'; //строка вывода на принтер (вначале идет инициализация шрифта)
            var htmlstr = ''; //Вывод наименования штрихкода в окно

            switch(Number(print[0].barcode_size))
            {
                //Для этикеток 30x50
                case 3050:
                    for (var k in print) {
                        if (typeof(print[k]) == 'object') {
                            sup = 0; sdown = 0;
                            prnstr = prnstr + '^XA';
                            if (print[k].Service != null && print[k].Service != '') {
                                    prnstr = prnstr + '^FO 0,29 ^AQN,10,10,^FD'+print[k].Service+'^FS';
                                    htmlstr = htmlstr + 'Сервис: '+print[k].Service;
                            }
                            else {
                                    sup++;
                            }
                            if (print[k].FIO != null && print[k].FIO != '' && sup == 0) {
                                    prnstr = prnstr + '^FO 0,51 ^AQN,10,10,^FD'+print[k].FIO+'^FS';
                                    htmlstr = htmlstr + '; Ф.И.О.: ' + print[k].FIO;
                            }
                            else if (print[k].FIO != null && print[k].FIO != '' && sup == 1){
                                    prnstr = prnstr + '^FO 0,29 ^AQN,10,10,^FD'+print[k].FIO+'^FS';
                                    htmlstr = htmlstr + '; Ф.И.О.: ' + print[k].FIO;
                            } else {
                                    sup++;
                            }
                            if (print[k].PrehospDirect_Name != null && print[k].PrehospDirect_Name != '') {
                                    prnstr = prnstr + '^FO 0,215 ^AQN,10,10,^FD'+print[k].PrehospDirect_Name+'^FS'
                                    htmlstr = htmlstr + '; Кем направлен: ' + print[k].PrehospDirect_Name + '<br/>';
                            } else {
                                    sdown++;
                            }
                            if (print[k].SampleNumber) {
                                    str = 'Y';
                            } else {
                                    str = 'N'; sdown++;
                            }
                            if (print[k].barcode_format == 39) {
                                    prnstr = prnstr + '^CVY^FO 10,'+22*(-sup+3)+' ^BY2,2 ^B3N,N,'+(110+21*(sup+sdown))+','+str+',N ^FD'+print[k].BarCode+'^FS ^XZ';
                            }
                            else if (print[k].barcode_format == 128) {
                                    prnstr = prnstr + '^CVY^FO 25,'+22*(-sup+3)+' ^BY2,2 ^BCN,'+(110+21*(sup+sdown))+','+str+',N,N ^FD'+print[k].BarCode+'^FS ^XZ';
                            }
                        }
                    }

                this.fprint(prnstr,htmlstr);
                break;

                //Для этикеток 25х40
                case 2540:
                    for (var k in print) {
                        if (typeof(print[k]) == 'object') {
                            sup = 0; sdown = 0;
                            prnstr = prnstr + '^XA';
                            if (print[k].Service != null && print[k].Service != '') {
                                    prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+print[k].Service+'^FS';
                                    htmlstr = htmlstr + 'Сервис: '+print[k].Service;
                            }
                            else {
                                    sup++;
                            }
                            if (print[k].FIO != null && print[k].FIO != '' && sup == 0) {
                                    prnstr = prnstr + '^FO 20,44 ^AQN,8,8,^FD'+print[k].FIO+'^FS';
                                    htmlstr = htmlstr + '; Ф.И.О.: ' + print[k].FIO;
                            }
                            else if (print[k].FIO != null && print[k].FIO != '' && sup == 1){
                                    prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+print[k].FIO+'^FS';
                                    htmlstr = htmlstr + '; Ф.И.О.: ' + print[k].FIO;
                            } else {
                                    sup++;
                            }
                            if (print[k].PrehospDirect_Name != null && print[k].PrehospDirect_Name != '') {
                                    prnstr = prnstr + '^FO 20,185 ^AQN,8,8,^FD'+print[k].PrehospDirect_Name+'^FS'
                                    htmlstr = htmlstr + '; Кем направлен: ' + print[k].PrehospDirect_Name + '<br/>';
                            } else {
                                    sdown++;
                            }
                            if (print[k].SampleNumber) {
                                    str = 'Y';
                            } else {
                                    str = 'N'; sdown++;
                            }
                            if (print[k].barcode_format == 39) {
                                    prnstr = prnstr + '^CVY^FO 57,'+19*(-sup+3)+' ^BY1,3.0 ^B3N,N,'+(95+18*(sup+sdown))+','+str+',N ^FD'+print[k].BarCode+'^FS ^XZ';
                            }
                            else if (print[k].barcode_format == 128) {
                                    prnstr = prnstr + '^CVY^FO 90,'+19*(-sup+3)+' ^BY1,3.0 ^BCN,'+(95+18*(sup+sdown))+','+str+',N,N ^FD'+print[k].BarCode+'^FS ^XZ';
                            }
                        }
                    }

                this.fprint(prnstr,htmlstr);
                break;

                //Для этикеток 20x40
                case 2040:
                    for (var k in print) {
                        if (typeof(print[k]) == 'object') {
                            sup = 0; sdown = 0;
                            prnstr = prnstr + '^XA';
                            if (print[k].Service != null && print[k].Service != '') {
                                    prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+print[k].Service+'^FS';
                                    htmlstr = htmlstr + 'Сервис: '+print[k].Service;
                            }
                            else {
                                    sup++;
                            }

                            if (print[k].FIO != null && print[k].FIO != '' && sup == 0) {
                                    prnstr = prnstr + '^FO 20,45 ^AQN,8,8,^FD'+print[k].FIO+'^FS';
                                    htmlstr = htmlstr + '; Ф.И.О.: ' + print[k].FIO;
                            }

                            else if (print[k].FIO != null && print[k].FIO != '' && sup == 1){
                                    prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+print[k].FIO+'^FS';
                                    htmlstr = htmlstr + '; Ф.И.О.: ' + print[k].FIO;
                            } else {
                                    sup++;
                            }

                            if (print[k].PrehospDirect_Name != null && print[k].PrehospDirect_Name != '') {
                                    prnstr = prnstr + '^FO 20,145 ^AQN,8,8,^FD'+print[k].PrehospDirect_Name+'^FS'
                                    htmlstr = htmlstr + '; Кем направлен: ' + print[k].PrehospDirect_Name + '<br/>';
                            } else {
                                    sdown++;
                            }
                            if (print[k].SampleNumber) {
                                    str = 'Y';
                            } else {
                                    str = 'N'; sdown++
                            }
                            if (print[k].barcode_format == 39) {
                                    prnstr = prnstr + '^CVY^FO 45,'+19*(-sup+3)+' ^BY1,3.0 ^B3N,N,'+(60+20*(sup+sdown))+','+str+',N ^FD'+print[k].BarCode+'^FS ^XZ';
                            }
                            else if (print[k].barcode_format == 128) {
                                    prnstr = prnstr + '^CVY^FO 70,'+19*(-sup+3)+' ^BY1,3.0 ^BCN,'+(60+20*(sup+sdown))+','+str+',N,N ^FD '+print[k].BarCode+'^FS ^XZ';
                            }
                        }
                    }

                this.fprint(prnstr,htmlstr);
                break;

                //Для этикеток 20х30
                case 2030:
                    for (var k in print) {
                        if (typeof(print[k]) == 'object') {
                            sup = 0; sdown = 0;
                            prnstr = prnstr + '^XA';
                            if (print[k].Service != null && print[k].Service != '') {
                                    prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+print[k].Service+'^FS';
                                    htmlstr = htmlstr + 'Сервис: '+print[k].Service;
                            }
                            else {
                                    sup++;
                            }

                            if (print[k].FIO != null && print[k].FIO != '' && sup == 0) {
                                    prnstr = prnstr + '^FO 20,45 ^AQN,8,8,^FD'+print[k].FIO+'^FS';
                                    htmlstr = htmlstr + '; Ф.И.О.: ' + print[k].FIO;
                            }

                            else if (print[k].FIO != null && print[k].FIO != '' && sup == 1){
                                    prnstr = prnstr + '^FO 20,25 ^AQN,8,8,^FD'+print[k].FIO+'^FS';
                                    htmlstr = htmlstr + '; Ф.И.О.: ' + print[k].FIO;
                            } else {
                                    sup++;
                            }

                            if (print[k].PrehospDirect_Name != null && print[k].PrehospDirect_Name != '') {
                                    prnstr = prnstr + '^FO 20,145 ^AQN,8,8,^FD'+print[k].PrehospDirect_Name+'^FS'
                                    htmlstr = htmlstr + '; Кем направлен: ' + print[k].PrehospDirect_Name + '<br/>';
                            } else {
                                    sdown++;
                            }
                            if (print[k].SampleNumber) {
                                    str = 'Y';
                            } else {
                                    str = 'N'; sdown++;
                            }
                            if (print[k].barcode_format == 39) {
                                    prnstr = prnstr + '^CVY^FO 45,'+19*(-sup+3)+' ^BY1,3.0 ^B3N,N,'+(60+20*(sup+sdown))+','+str+',N ^FD'+print[k].BarCode+'^FS ^XZ';
                            }
                            else if (print[k].barcode_format == 128) {
                                    prnstr = prnstr + '^CVY^FO 70,'+19*(-sup+3)+' ^BY1,3.0 ^BCN,'+(60+20*(sup+sdown))+','+str+',N,N ^FD '+print[k].BarCode+'^FS ^XZ';
                            }
                        }
                    }

                this.fprint(prnstr,htmlstr);
                break;

                default:
                    Ext.MessageBox.getDialog().getEl().setStyle('z-index','80000'); //Т.к окно иногда бывает на заднем фоне
                    Ext.Msg.alert('Ошибка печати', 'Для принтера Zebra необходимо указать в настройках печати штрихкода ширину и высоту наклейки, выбирая из трех значений: 20x30, 25x40 и 30х50');
                break;
            }
    },

    //Выов аплета для печати штрихкодов.
    //html - вывод того, что идет на печать.
    fprint: function(prnstr, htmlstr) {
        //console.log('prnstr ',prnstr, ' htmlstr ',htmlstr);
        if (navigator.javaEnabled() ) {
            if (Ext.get('Zebra_applet') != null) Ext.get('Zebra_applet').remove();

            //Окно апплета вставляется под стрелку изменения дат в виде 1 пикселя (Без окна аплет не работает.)
            var applet = Ext.getCmp('prevArrowLis').getEl().parent().createChild({
                    name: 'PrintZebra',
                    tag: 'object',
                    archive:'/documents/Zebra/PrintZebra.jar',
                    codetype: 'application/java',
                    width: 1,
                    height: 1,
                    classid: "java:PrintZebra.class",
                    id: 'Zebra_applet',
                    children: [
                        {tag: 'param', name: 'zebracode', value: prnstr},
                        {tag: 'param', name: 'PrinterName', value: 'ZDesigner GK420t'},
                        //{tag: 'param', name: 'boxbgcolor', value: '220,230,245'}
                    ]
            });
        } else {
            setPromedInfo('Отсутствует java машина. Работа с картами будет недоступна.<br/>Для установки java машины зайдите на сайт <a href=http://java.com/ru>http://java.com/ru</a>', 'javamashine-info');
        }
    },

    printBarcodes: function(options) {
        if ( typeof options != 'object' ) {
            options = new Object();
        }

        var win = this;
        var grid = this.GridPanel.getGrid();
        var EvnDirection_ids = new Array();

        var s = "";
        grid.getSelectionModel().getSelections().forEach(function (el) {
            if (!Ext.isEmpty(el.data.EvnLabSample_ids)) {
                if (!Ext.isEmpty(s)) {
                    s = s + ",";
                }
                s = s + el.data.EvnLabSample_ids.replace(/\s+/g,'');
            }
        });

        if (!Ext.isEmpty(s)) {
            var Report_Params = '&s=' + s,
				Report_FileName = (Ext.globalOptions.lis.use_postgresql_lis ? 'barcodesprint_resize_pg' : 'barcodesprint_resize') + '.rptdesign';

            if ( Ext.globalOptions.lis ) {
                var ZebraDateOfBirth = (Ext.globalOptions.lis.ZebraDateOfBirth) ? 1 : 0;
                var ZebraUsluga_Name = (Ext.globalOptions.lis.ZebraUsluga_Name) ? 1: 0;
                var ZebraDirect_Name = (Ext.globalOptions.lis.ZebraDirect_Name) ? 1 : 0;
                var ZebraFIO = (Ext.globalOptions.lis.ZebraFIO) ? 1 : 0;
                Report_Params = Report_Params + '&paramPrintType=1';
                Report_Params = Report_Params + '&marginTop=' + Ext.globalOptions.lis.labsample_barcode_margin_top;
                Report_Params = Report_Params + '&marginBottom=' + Ext.globalOptions.lis.labsample_barcode_margin_bottom;
                Report_Params = Report_Params + '&marginLeft=' + Ext.globalOptions.lis.labsample_barcode_margin_left;
                Report_Params = Report_Params + '&marginRight=' + Ext.globalOptions.lis.labsample_barcode_margin_right;
                Report_Params = Report_Params + '&width=' + Ext.globalOptions.lis.labsample_barcode_width;
                Report_Params = Report_Params + '&height=' + Ext.globalOptions.lis.labsample_barcode_height;
                Report_Params = Report_Params + '&barcodeFormat=' + Ext.globalOptions.lis.barcode_format;
                Report_Params = Report_Params + '&ZebraDateOfBirth=' + ZebraDateOfBirth;
                Report_Params = Report_Params + '&ZebraUsluga_Name=' + ZebraUsluga_Name;
                Report_Params = Report_Params + '&paramFrom=' + ZebraDirect_Name;
                Report_Params = Report_Params + '&paramFIO=' + ZebraFIO;
            }

            Report_Params = Report_Params + '&paramLpu=' + getGlobalOptions().lpu_id

            printBirt({
                'Report_FileName': Report_FileName,
                'Report_Params': Report_Params,
                'Report_Format': 'pdf'
            });
        }

        return false;
    },
    printLabSampleBarcodes: function() {
        var s = "";
        this.LabSampleGridPanel.getGrid().getSelectionModel().getSelections().forEach(function (el) {
            if (!Ext.isEmpty(el.data.EvnLabSample_id)) {
                if (!Ext.isEmpty(s)) {
                    s = s + ",";
                }
                s = s + el.data.EvnLabSample_id;
            }
        });

        if (!Ext.isEmpty(s)) {
            var Report_Params = '&s=' + s,
				Report_FileName = (Ext.globalOptions.lis.use_postgresql_lis ? 'barcodesprint_resize_pg' : 'barcodesprint_resize') + '.rptdesign';
            if ( Ext.globalOptions.lis ) {
                var ZebraDateOfBirth = (Ext.globalOptions.lis.ZebraDateOfBirth) ? 1 : 0;
                var ZebraUsluga_Name = (Ext.globalOptions.lis.ZebraUsluga_Name) ? 1: 0;
                var ZebraDirect_Name = (Ext.globalOptions.lis.ZebraDirect_Name) ? 1 : 0;
                var ZebraFIO = (Ext.globalOptions.lis.ZebraFIO) ? 1 : 0;
                Report_Params = Report_Params + '&paramPrintType=1';
                Report_Params = Report_Params + '&marginTop=' + Ext.globalOptions.lis.labsample_barcode_margin_top;
                Report_Params = Report_Params + '&marginBottom=' + Ext.globalOptions.lis.labsample_barcode_margin_bottom;
                Report_Params = Report_Params + '&marginLeft=' + Ext.globalOptions.lis.labsample_barcode_margin_left;
                Report_Params = Report_Params + '&marginRight=' + Ext.globalOptions.lis.labsample_barcode_margin_right;
                Report_Params = Report_Params + '&width=' + Ext.globalOptions.lis.labsample_barcode_width;
                Report_Params = Report_Params + '&height=' + Ext.globalOptions.lis.labsample_barcode_height;
                Report_Params = Report_Params + '&barcodeFormat=' + Ext.globalOptions.lis.barcode_format;
                Report_Params = Report_Params + '&ZebraDateOfBirth=' + ZebraDateOfBirth;
                Report_Params = Report_Params + '&ZebraUsluga_Name=' + ZebraUsluga_Name;
                Report_Params = Report_Params + '&paramFrom=' + ZebraDirect_Name;
                Report_Params = Report_Params + '&paramFIO=' + ZebraFIO;
            }

            Report_Params = Report_Params + '&paramLpu=' + getGlobalOptions().lpu_id

            printBirt({
                'Report_FileName': Report_FileName,
                'Report_Params': Report_Params,
                'Report_Format': 'pdf'
            });
        }

        return false;
    },
    checkSamples: function()
    {
        var win = this;
        // делаем запрос кол-ва проб полученных с анализатора
        Ext.Ajax.request({
            url: '/?c=EvnLabSample&m=getEvnLabSampleFromLisWithResultCount',
            params: {
                MedService_id: win.MedService_id
            },
            callback: function(opt, success, response) {
                if (success && response.responseText != '') {
                    var result  = Ext.util.JSON.decode(response.responseText);
                    if (result.cnt > 0) {
                        if (!Ext.isEmpty(win.lastLabSampleCount) && result.cnt > win.lastLabSampleCount) {
                            showSysMsg(langs('Новые результаты получены для ') + result.cnt + langs(' проб(ы)'), langs('Внимание'));
                        } else if (Ext.isEmpty(win.lastLabSampleCount) || result.cnt < win.lastLabSampleCount) {
                            showSysMsg(langs('Результаты получены для ') + result.cnt + langs(' проб(ы) за текущий день'), langs('Внимание'));
                        }
                    }

                    win.lastLabSampleCount = result.cnt;
                }
            }
        });
    },
    resetGridKeyboardInput: function(sequence) {
        var win = this;
        var result = false;
        if (sequence == win.gridKeyboardInputSequence) {
            if (win.gridKeyboardInput.length >= 4) {
                if (win.labMode == 0) {
                    win.GridPanel.onKeyboardInputFinished(win.gridKeyboardInput);
                } else {
                    win.LabSampleGridPanel.onKeyboardInputFinished(win.gridKeyboardInput);
                }
                result = true;
            }
            win.gridKeyboardInput = '';
        }
        return result;
    },
    sendToLis: function(options) {
        if ( typeof options != 'object' ) {
            options = new Object();
        }

        var win = this;
        var g = win.LabSampleGridPanel;

		if(win.isFormIfa()) {
			sw.swMsg.alert('Сообщение', 'Недоступно для лаборатории ИФА');
			return;
		}

        // Проверяем есть ли выбранные записи
        var selections = g.getGrid().getSelectionModel().getSelections();
        var ArrayId = [];

        for (var key in selections) {
            if (selections[key].data) {
                if (Ext.isEmpty(selections[key].data['EvnLabSample_setDT'])) {
                    sw.swMsg.alert(langs('Ошибка'), langs('<b>Выбранная проба не содержит данных о составе пробы.</b><br/>Откройте пробу и заполните информацию о взятии пробы.'));
                    return false;
                }
                if (Ext.isEmpty(selections[key].data['Analyzer_id'])) {
                    sw.swMsg.alert(langs('Ошибка'), langs('Необходимо указать анализатор для всех выбранных проб.'));
                    return false;
                }
                ArrayId.push(selections[key].data['EvnLabSample_id'].toString());
            }
        }

        var params = {
            MedServiceType_SysNick: win.MedServiceType_SysNick
        };
        params.EvnLabSamples = Ext.util.JSON.encode(ArrayId);
        if (options.onlyNew) {
            params.onlyNew = options.onlyNew;
        }
		if (options.changeNumber) {
			params.changeNumber = options.changeNumber;
		}
        if (g.getGrid().getSelectionModel().getCount() > 0) {
            win.getLoadMask(langs('Создание ')+((ArrayId.length>1)?langs('заявок'):langs('заявки'))+langs(' для анализатора')).show();
            // получаем выделенную запись
            Ext.Ajax.request({
                url: '/?c='+getLabController()+'&m=createRequestSelections',
                params: params,
                callback: function(opt, success, response) {
                    win.getLoadMask(LOAD_WAIT).hide();
                    if (success && response.responseText != '') {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (result.success) {
							if (result.sysMsg) {
								showSysMsg(result.sysMsg);
							}
							if (result.Alert_Code) {
								switch(result.Alert_Code) {
									case 100:
										sw.swMsg.show({
											buttons: {
												yes: langs('Только новые'),
												no: langs('Все'),
												cancel: langs('Отмена')
											},
											fn: function(buttonId, text, obj) {
												if ( buttonId == 'yes' ) {
													options.onlyNew = 2;
													win.sendToLis(options);
												} else if (buttonId == 'no') {
													options.onlyNew = 1;
													win.sendToLis(options);
												}
											}.createDelegate(this),
											icon: Ext.MessageBox.QUESTION,
											msg: result.Alert_Msg,
											title: langs('Вопрос')
										});
										break;
									case 101:
										sw.swMsg.show({
											buttons: Ext.Msg.YESNOCANCEL,
											fn: function(buttonId, text, obj) {
												if ( buttonId == 'yes' ) {
													options.changeNumber = 2;
													win.sendToLis(options);
												} else if (buttonId == 'no') {
													options.changeNumber = 1;
													win.sendToLis(options);
												}
											}.createDelegate(this),
											icon: Ext.MessageBox.QUESTION,
											msg: result.Alert_Msg,
											title: langs('Вопрос')
										});
										break;
								}
							} else {
                                g.getGrid().getStore().reload();
                                showSysMsg(langs('Заявка для анализатора успешно создана'), langs('Заявка для анализатора'));
                            }
                        } else {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: function() {
                                },
                                icon: Ext.Msg.WARNING,
                                msg: result.Error_Msg,
                                title: langs('Заявка для анализатора')
                            });
                        }
                    }
                }
            });
        } else {
            sw.swMsg.alert(langs('Проба не выбрана'), langs('Для создания заявки необходимо выбрать хотя бы одну пробу'));
        }
    },
    sendToInnova: function() {
		var win = this;
		var g = win.GridPanel;
		var selections = g.getGrid().getSelectionModel().getSelections(),
			ArrayId = [];
		if (!Ext.isEmpty(selections)) {
			for (var key = 0; key < selections.length; key++) {
				if (selections[key].data && !Ext.isEmpty(selections[key].data['EvnLabSample_ids'])) {
					ArrayId.push(selections[key].data['EvnLabRequest_id'].toString());
				} else {
					ArrayId = [];
					sw.swMsg.alert(langs('Ошибка отправки на анализатор'), 'Не взяты все пробы');
					break;
				}
			}

			var curParams = {
				'EvnLabRequests': Ext.util.JSON.encode(ArrayId)
			};
			if (!Ext.isEmpty(curParams.EvnLabRequests)) {
				Ext.Ajax.request({
					url: '/?c=InnovaSysService&m=makeRequests',
					params: curParams,
					failure: function () {
						sw.swMsg.alert(langs('Ошибка отправки на анализатор'), 'Не удалось отправить пробы в ЛИС');
					}
				});
			}
		}
    },
    sendRequestsToLis: function(options) {
        if ( typeof options != 'object' ) {
            options = new Object();
        }

        var win = this;
        var g = win.GridPanel;

		if(win.isFormIfa()) {
			sw.swMsg.alert('Сообщение', 'Недоступно для лаборатории ИФА');
			return;
		}

        // Проверяем есть ли выбранные записи
        var selections = g.getGrid().getSelectionModel().getSelections();
        var ArrayId = [];

        for (var key in selections) {
            if (selections[key].data) {
                ArrayId.push(selections[key].data['EvnLabRequest_id'].toString());
            }
        }

        var params = {}
        params.EvnLabRequests = Ext.util.JSON.encode(ArrayId);
        if (options.onlyNew) {
            params.onlyNew = options.onlyNew;
        }
        if (options.changeNumber) {
            params.changeNumber = options.changeNumber;
        }
        
        //#PROMEDWEB-10508 попытка решить магическую пропажу CurMedService_id из сессии
        if (!Ext.isEmpty(win.MedService_id)) {
			params.CurMedService_id = win.MedService_id;
		}
        else{
			params.CurMedService_id = getGlobalOptions().CurMedService_id;
		}
        	
        if (g.getGrid().getSelectionModel().getCount() > 0) {
            win.getLoadMask(langs('Создание ')+((ArrayId.length>1)?langs('заявок'):langs('заявки'))+langs(' для анализатора')).show();
            // получаем выделенную запись
            Ext.Ajax.request({
                url: '/?c='+getLabController()+'&m=createRequestSelectionsLabRequest',
                params: params,
                callback: function(opt, success, response) {
                    win.getLoadMask(LOAD_WAIT).hide();
                    if (success && response.responseText != '') {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (result.success) {
							if (result.sysMsg) {
								showSysMsg(result.sysMsg);
							}
                            if (result.Alert_Code) {
								switch(result.Alert_Code) {
									case 100:
										sw.swMsg.show({
											buttons: {
												yes: langs('Только новые'),
												no: langs('Все'),
												cancel: langs('Отмена')
											},
											fn: function(buttonId, text, obj) {
												if ( buttonId == 'yes' ) {
													options.onlyNew = 2;
													win.sendRequestsToLis(options);
												} else if (buttonId == 'no') {
													options.onlyNew = 1;
													win.sendRequestsToLis(options);
												}
											}.createDelegate(this),
											icon: Ext.MessageBox.QUESTION,
											msg: result.Alert_Msg,
											title: langs('Вопрос')
										});
										break;
									case 101:
										sw.swMsg.show({
											buttons: Ext.Msg.YESNOCANCEL,
											fn: function(buttonId, text, obj) {
												if ( buttonId == 'yes' ) {
													options.changeNumber = 2;
													win.sendRequestsToLis(options);
												} else if (buttonId == 'no') {
													options.changeNumber = 1;
													win.sendRequestsToLis(options);
												}
											}.createDelegate(this),
											icon: Ext.MessageBox.QUESTION,
											msg: result.Alert_Msg,
											title: langs('Вопрос')
										});
										break;
								}
                            } else {
                                g.getGrid().getStore().reload();
                                showSysMsg(langs('Заявка для анализатора успешно создана'), langs('Заявка для анализатора'));
                            }
                        } else {
                            sw.swMsg.show({
                                buttons: Ext.Msg.OK,
                                fn: function() {
                                },
                                icon: Ext.Msg.WARNING,
                                msg: result.Error_Msg,
                                title: langs('Заявка для анализатора')
                            });
                        }
                    }
                }
            });
        } else {
            sw.swMsg.alert(langs('Заявка не выбрана'), langs('Для создания заявки необходимо выбрать хотя бы одну заявку'));
        }
    },
	addWithoutRegIsAllowed: function() {
		//console.log('addWithoutRegIsAllowed!');
		var win = this;
		if	(	(getGlobalOptions().CurMedServiceType_SysNick == 'lab' ||
				getGlobalOptions().CurMedServiceType_SysNick == 'pzm' ||
				getGlobalOptions().CurMedServiceType_SysNick == 'reglab') &&
				win.MedServiceMedPersonal_isNotWithoutRegRights
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
				},
				icon: Ext.Msg.WARNING,
				msg: langs('Добавление запрещено'),
				title: langs('Недостаточно прав')
			});
			return 0;
		} else return 1;
	},
	approveIsAllowed: function() {//Проверка прав на "Одобрение"
		var win = this;
		//console.log('approveIsAllowed:'+win.MedServiceMedPersonal_isNotApproveRights);
		if (win.MedServiceMedPersonal_isNotApproveRights) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
				},
				icon: Ext.Msg.WARNING,
				msg: langs('Одобрение запрещено'),
				title: langs('Недостаточно прав')
			});
			return 0;
		} else return 1;
	},
    renderEMDButtons: function(el) {
        if (getRegionNick() == 'kz') {
            return; // для Казахстана не нужна подпись refs #113642
        }

        var form = this;
        // рендерим кнопку для подписи (компонент 6-го ExtJS).
        if (el && typeof el.query == 'function') {
            var els = el.query('div.emd-here-tiny');
            Ext6.each(els, function(domEl) {
                //#160801 если в элементе уже есть emd панель, дублировать не будем
                var emd = domEl.querySelector('.emd-panel');
                if(!emd){
                    var el = Ext6.get(domEl);
                    var swEMDPanel = Ext6.create('sw.frames.EMD.swEMDPanel', {
                        tinyMode: true,
                        renderTo: domEl,
                        width: 40,
                        height: 15
                    });
                    swEMDPanel.setParams({
                        EMDRegistry_ObjectName: el.getAttribute('data-objectname'),
                        EMDRegistry_ObjectID: el.getAttribute('data-objectid')
                    });
                    swEMDPanel.setIsSigned(el.getAttribute('data-issigned'));
                    if (el.getAttribute('data-disabledsign') && el.getAttribute('data-disabledsign') == "1") {
                        swEMDPanel.setReadOnly(true);
                    }
                }
            });
        }
    },
    show: function()
    {
		//console.log("swAssistantWorkPlaceWindow:");
		//console.log('medpersonal_id: '+getGlobalOptions().medpersonal_id);

		var win = this;
		win.globalSampleList = {
			normalSamples: [],
			pathologySamples: []
		};
		win.fileIntegration = false;

        win.filterWorkELRByDate = 1;
        win.filterDoneELRByDate = 1;

        win.filterNewELSByDate = 1;
        win.filterWorkELSByDate = 1;
        win.filterDoneELSByDate = 1;

		win.MedServiceMedPersonal_isNotApproveRights = false;
		win.MedServiceMedPersonal_isNotWithoutRegRights = false;

		//получаем права пользователя на одобрение заявок/проб
		Ext.Ajax.request({
			url: '/?c=MedService&m=getApproveRights',
			params:{
				MedPersonal_id: getGlobalOptions().medpersonal_id,
				MedService_id: arguments[0].MedService_id,
				armMode: 'Lis'
			},
			callback: function(opt, success, response) {
				if (success && response.responseText != '') {
					var result = Ext.util.JSON.decode(response.responseText);
					//console.log('getApproveRights:'); console.log(result[0]);
					win.MedServiceMedPersonal_isNotApproveRights = result[0].MedServiceMedPersonal_isNotApproveRights;
					win.MedServiceMedPersonal_isNotWithoutRegRights = result[0].MedServiceMedPersonal_isNotWithoutRegRights;
				}
			}
		});

        sw.Promed.swAssistantWorkPlaceWindow.superclass.show.apply(this, arguments);

        if ( arguments[0] ) {
            if ( arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.ARMType ) {
                this.ARMType = arguments[0].userMedStaffFact.ARMType;
                this.userMedStaffFact = arguments[0].userMedStaffFact;
            }
            else {
                if ( arguments[0].MedService_id ) {
                    this.MedService_id = arguments[0].MedService_id;
                    this.userMedStaffFact = arguments[0];
                }
                else {
                    if ( arguments[0].ARMType ) { // Это АРМ без привязки к врачу - АРМ администратора или кадровика
                        this.userMedStaffFact = arguments[0];
                    } else {
                        this.hide();
                        sw.swMsg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указан тип АРМа.');
                        return false;
                    }
                }
            }
        }

        // фильтр по услуге должен заново прогрузиться.
        var uslugaFilter = this.filterRowReq.getFilter('UslugaComplex_id');
        if (uslugaFilter.rendered) {
            uslugaFilter.getStore().removeAll();
            uslugaFilter.lastQuery = 'This query sample that is not will never appear';
            uslugaFilter.clearValue();
        }

		// фильтр по услуге должен заново прогрузиться.
		var uslugaFilterLab = this.filterRowLab.getFilter('UslugaComplex_id');
		if (uslugaFilterLab.rendered) {
			uslugaFilterLab.getStore().removeAll();
			uslugaFilterLab.lastQuery = 'This query sample that is not will never appear';
			uslugaFilterLab.clearValue();
		}

        this.getCurrentDateTime();

        // Создаем свой заголовок, единый для всех армов, на основании данных пришедших с сервера
        sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);
        if ( arguments[0].MedService_id ) {
            this.MedService_id = arguments[0].MedService_id;
            this.MedService_Name = arguments[0].MedService_Name;
        } else {
            // Не понятно, что за АРМ открывается
            sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function () {
                this.hide();
            }.createDelegate(this));
            return false;
        }

        this.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick || 'lab';
        this.MedService_IsExternal = (arguments[0].MedService_IsExternal && arguments[0].MedService_IsExternal == 2);

		//проверка коннекта к основной БД
		var runner = new Ext.util.TaskRunner();
		var sidePanel = win.findById(win.id + '_buttPanel');

		this.task = runner.start({
			run: function() {
				Ext.Ajax.request({
					url: '/?c=Utils&m=checkMainDBConnection',
					success: function(response) {
					    if (response.responseText != '') {
					        var resp = Ext.util.JSON.decode(response.responseText);
					        if (resp.success) {
					            //если кнопки выключались
					            if (win.panelsWereHidden) {
									//включение кнопок
									for (var act in win.LeftPanel.actions) {
										if (act.inlist(win.enabledKeys)) {
											win.LeftPanel.actions[act].setHidden(false);
										} else {
											win.LeftPanel.actions[act].setHidden(true);
										}
									}
                                }
                            } else {
								showPopupWarningMsg(langs('Отсутствует подключение к основной БД. Функционал ограничен'), langs('Внимание'));
								//могут работать независимо от коннекта к основной БД
								var keysToEnable = ['action_Usluga', 'action_Defect', 'action_Form250u'];
								win.panelsWereHidden = true;
								if (getRegionNick() == 'perm') {
								    keysToEnable.push('action_AnalyzerControlSeries');
								}

								if (Ext.isEmpty(win.enabledKeys)) {
									Ext.each(sidePanel.actions, function(actionList) {
										var keys = Object.keys(actionList);
										var enabledKeys = [];

										//составление списка изначально доступных кнопок
										keys.forEach(function(key) {
											if (!actionList[key].initialConfig.hidden) {
												enabledKeys.push(key);
											}
										});
										win.enabledKeys = enabledKeys;
									});
								}
								//выключение кнопок
								for (var act in win.LeftPanel.actions) {
									if (act.inlist(keysToEnable)) {
										win.LeftPanel.actions[act].setHidden(false);
									} else {
										win.LeftPanel.actions[act].setHidden(true);
									}
								}
                            }
                        }
					}
				});
			},
			interval: 60000 //раз в минуту
		});

        win.ElectronicQueuePanel.initElectronicQueue(); // инициализируем до вызова doSearch();

		this.LabRequestTabPanel.setTabTitle(0, "<div class='tab_title'>Все заявки</div> <div class='tab_title_count'></div>");
		this.LabRequestTabPanel.setTabTitle(1, "<div class='tab_title'>Новые заявки</div> <div class='tab_title_count lrstate1'></div>");
		this.LabRequestTabPanel.setTabTitle(2, "<div class='tab_title'>В работе</div> <div class='tab_title_count lrstate3'></div>");
		this.LabRequestTabPanel.setTabTitle(3, "<div class='tab_title'>"+(getRegionNick() == 'ufa' ? 'Выполненные' : 'С результатами')+"</div> <div class='tab_title_count lrstate4'></div>");
		this.LabRequestTabPanel.setTabTitle(4, "<div class='tab_title'>Одобренные</div> <div class='tab_title_count lrstate5'></div>");
		this.LabRequestTabPanel.setTabTitle(5, "<div class='tab_title'>Невыполненные</div> <div class='tab_title_count lrstate6'></div>");
		if (this.MedServiceType_SysNick == 'pzm') {
			this.LabRequestTabPanel.setActiveTab(0); // по умолчанию "Все заявки"
		} else {
			this.LabRequestTabPanel.setActiveTab(1); // по умолчанию "Новые заявки"
		}
		this.LabSampleTabPanel.setActiveTab(0);

        if (this.MedServiceType_SysNick != 'lab') {
            var combo = Ext.getCmp(win.id+'MedServiceComboField');
            if (combo) {
                var params = new Object();
                params.MedServiceTypeIsLabOrFenceStation = 1;
                params.Lpu_isAll = 2;
                // фильтруем лаборатории по MedService_id.
                params.MedService_id = win.MedService_id;
                params.ARMType = 'pzm';
                combo.getStore().removeAll();
                combo.getStore().load({
                    params: params
                });
            }
		}

        this.GridPanel.getGrid().getView().mainBody.addClass(this.GridPanel+'-body');
        this.LabSampleGridPanel.getGrid().getView().mainBody.addClass(this.LabSampleGridPanel+'-body');

        // this.GridPanel.addActions({name:'action_get_barcode', text: '', handler: function() {}});

        this.GridPanel.addActions({name:'action_outsourcing_create', text: langs('Аутсорсинг'), handler: function (){
				if (getRegionNick() == 'vologda' && win.MedServiceType_SysNick == 'pzm' && win.fileIntegration) {
					win.sendToInnova();
				} else {
					win.sendRequestsToLis();
				}
        }});

        this.GridPanel.setActionHidden('action_outsourcing_create', !win.MedServiceType_SysNick.inlist(['reglab']) && !win.MedService_IsExternal);

        this.GridPanel.addActions({name:'action_lis_create', text: langs('Отправить на анализатор'), handler: function (){
            win.sendRequestsToLis();
        }});

		win.tabletGrid.setParam('MedService_id', win.MedService_id);
		win.tabletGrid.removeAll();
		win.tabletGrid.addActions({
			//iconCls: 'actions16',
			name: 'action_menu',
			text: langs('Действия'),
			menu: new Ext.menu.Menu({
				items: [
					win.tabletGrid.actionEdit,
					win.tabletGrid.actionCreateChild,
					win.tabletGrid.actionDisable,
					win.tabletGrid.actionDelete,
					win.tabletGrid.actionPrint
				]
			})
		}, 1);
		win.tabletGrid.addActions({
			name: 'action_lis_create',
			text: langs('Отправить на анализатор'),
			handler: function () {
				sw.swMsg.alert(langs('Сообщение'), langs('Функционал в разработке'));
			}
		});
		win.tabletGrid.addActions({
			name: 'action_lis_sample',
			text: langs('Проверить результат'),
			handler: function () {
				sw.swMsg.alert(langs('Сообщение'), langs('Функционал в разработке'));
			}
		});

        //this.GridPanel.setActionHidden('action_lis_create', win.MedServiceType_SysNick.inlist(['pzm']) || win.MedService_IsExternal);
        this.GridPanel.setActionHidden('action_lis_create', 1);

        this.GridPanel.addActions({name:'action_sign_all', text: langs('Подписать'), handler: function (){
			if ( !win.approveIsAllowed() ) return;

            var selected_record = win.GridPanel.getGrid().getSelectionModel().getSelected();
            if (!selected_record) {
                return false;
            }

            var grid = win.GridPanel.getGrid();
            var records = grid.getSelectionModel().getSelections();
            var EvnDirection_ids = [];
            for (var i = 0; i < records.length; i++) {
                if (!Ext.isEmpty(records[i].get('EvnDirection_id')) && records[i].get('EvnDirection_id') > 0) {
                    EvnDirection_ids = EvnDirection_ids.concat(records[i].get('EvnDirection_id').toString());
                }
            }

            if (!Ext.isEmpty(EvnDirection_ids) && EvnDirection_ids.length > 0) {
                // получаем EvnUslugaPar_id's для заявок, по ним печатаем с использованием нового шаблона
                win.getLoadMask('Получение данных заявок').show();
                // обновить на стороне сервера
                Ext.Ajax.request({
                    url: '/?c=EvnLabRequest&m=getEvnUslugaParForPrint',
                    params: {
                        EvnDirections: Ext.util.JSON.encode(EvnDirection_ids)
                    },
                    callback: function (options, success, response) {
                        win.getLoadMask().hide();
                        if (success && response.responseText != '') {
                            var result  = Ext.util.JSON.decode(response.responseText);

                            var EvnUslugaParIds = [];
                            for (var i = 0; i < result.length; i++) {
                                if (!Ext.isEmpty(result[i].EvnUslugaPar_id) && result[i].EvnUslugaPar_IsSigned != 2) {
                                    EvnUslugaParIds.push(result[i].EvnUslugaPar_id);
                                }
                            }

                            var doc_signtype = getOthersOptions().doc_signtype;
                            if (EvnUslugaParIds.length > 1 && !doc_signtype.inlist(['cryptopro', 'authapi'])) {
                                sw.swMsg.alert(langs('Ошибка'), langs('Для плагина') + ' ' + doc_signtype + ' ' + langs('множественная подпись невозможна.'));
                            } else {
                                getWnd('swEMDSignWindow').show({
                                    EMDRegistry_ObjectName: 'EvnUslugaPar',
                                    EMDRegistry_ObjectIDs: EvnUslugaParIds,
                                    callback: function(data) {
                                        grid.getStore().reload();
                                    }
                                });
                            }
                        }
                    }
                });
            }

            return false;
        }});

        this.GridPanel.addActions({name:'action_lis_approve', text: langs('Одобрить'), handler: function (){
			if ( !win.approveIsAllowed() ) return;
            var g = win.GridPanel;
            var selections = g.getGrid().getSelectionModel().getSelections(),
                allIds = [],
                isOutNorm = 0;

			if (selections.length == 0) {
				sw.swMsg.alert(langs('Заявка не выбрана'), langs('Выберите заявку, результаты которой требуется одобрить'));
				return;
			}

            for (var key in selections) {
				if (!selections[key].data) continue;

                if (selections[key].data['EvnLabSample_IsOutNorm'] == 2) {
                    isOutNorm++;
                }
                allIds.push(Ext.util.JSON.encode(selections[key].data['EvnLabRequest_id']));
            }
			var params = {};
			params.EvnLabRequests = Ext.util.JSON.encode(allIds);
			params.onlyNormal = 2;

			if (isOutNorm == 0) {
				win.approveEvnLabRequestResults(params);
			} else {
				Ext.Msg.show({
					title: 'Одобрение проб',
					msg: 'В выбранных заявках имеются пробы с патологиями. Выберите дальнейшее действие.',
					icon: Ext.Msg.WARNING,
					buttons: {
						yes: 'Одобрить без патологий',
						no: 'Одобрить все пробы',
						cancel: 'Отмена'
					},
					fn: function (btn, text) {
						if (btn == 'yes') {
							win.approveEvnLabRequestResults(params);
						} else if (btn == 'no') {
  						  Ext.Msg.show({
								title: 'Одобрение проб',
								msg: 'Будут одобрены все пробы, в том числе с выявленной патологией. Продолжить?',
								icon: Ext.Msg.WARNING,
								buttons: {
									yes: 'Ок',
									no: 'Одобрить без патологий',
									cancel: 'Отмена'
								},
								fn: function (btn, text) {
									if (btn == 'yes') {
										params.EvnLabRequests = Ext.util.JSON.encode(allIds);
										params.onlyNormal = 1;
										win.approveEvnLabRequestResults(params);

									} else if (btn == 'no') {
										win.approveEvnLabRequestResults(params);
									}
								},
							});
						}
					},
				});
			}
        }});

        this.GridPanel.setActionHidden('action_lis_approve', win.MedServiceType_SysNick == 'pzm');

        this.GridPanel.addActions({name:'action_lis_sample_cancel', text: langs('Отмена взятия проб'), handler: function (){
            var g = win.GridPanel;

            var selections = g.getGrid().getSelectionModel().getSelections();
            var ArrayId = [];

            for (var key in selections) {
                if (selections[key].data) {
                    ArrayId.push(selections[key].data['EvnLabRequest_id'].toString());
                }
            }
            var params = {
                MedServiceType_SysNick: win.MedServiceType_SysNick
            };
            params.EvnLabRequests = Ext.util.JSON.encode(ArrayId);
            params.MedService_did = win.MedService_id;

            if (getRegionNick() == 'ufa' && !win.MedServiceType_SysNick.inlist(['pzm','reglab'])) {
                params.sendToLis = 1;
            }

            if (g.getGrid().getSelectionModel().getCount() > 0) {
                Ext.Msg.show({
                    title: langs('Отмена взятия проб'),
                    msg: langs('Вы действительно хотите отменить взятие проб?'),
                    buttons: Ext.Msg.YESNO,
                    fn: function(btn) {
                        if (btn === 'yes') {
                            win.getLoadMask(langs('Отмена взятия проб')).show();
                            // получаем выделенную запись
                            Ext.Ajax.request({
                                url: '/?c=EvnLabRequest&m=cancelLabSample',
                                params: params,
                                callback: function(opt, success, response) {
                                    win.getLoadMask().hide();
                                    if (success && response.responseText != '') {
                                        var result = Ext.util.JSON.decode(response.responseText);
                                        if (result.success) {

                                            g.getGrid().getStore().reload();
                                            if (result.Alert_Msg) {
                                                sw.swMsg.alert(langs('Ошибка отправки на анализатор'), result.Alert_Msg);
                                            }
                                        } else {
                                            sw.swMsg.show({
                                                buttons: Ext.Msg.OK,
                                                fn: function() {
                                                },
                                                icon: Ext.Msg.WARNING,
                                                msg: result.Error_Msg,
                                                title: langs('Отмена взятия проб')
                                            });
                                        }
                                    }
                                }
                            });
                        }
                    },
                    icon: Ext.MessageBox.QUESTION
                });
            } else {
                sw.swMsg.alert(langs('Заявка не выбрана'), langs('Выберите заявку, для которой нужно отменить взятие проб'));
            }
        }});

        this.GridPanel.addActions({name:'action_lis_sample', text: langs('Взять пробы'), handler: function (){
            var g = win.GridPanel;

            var selections = g.getGrid().getSelectionModel().getSelections();
            var ArrayId = [];

            for (var key in selections) {
                if (selections[key].data) {
                    ArrayId.push(selections[key].data['EvnLabRequest_id'].toString());
                }
            }
            var params = {
                MedServiceType_SysNick: win.MedServiceType_SysNick
            };
            params.EvnLabRequests = Ext.util.JSON.encode(ArrayId);
            params.MedService_did = win.MedService_id;

            //default url
            if (getRegionNick() == 'ufa' && !win.MedServiceType_SysNick.inlist(['pzm','reglab'])) {
                params.sendToLis = 1;
            }

            if (g.getGrid().getSelectionModel().getCount() > 0) {
				win.getLoadMask(langs('Взятие проб')).show();
				// получаем выделенную запись
				Ext.Ajax.request({
					url: '/?c=EvnLabRequest&m=takeLabSample',
					params: params,
					callback: function (opt, success, response) {
						win.getLoadMask().hide();
						if (success && response.responseText != '') {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.data && result.data[0])
							    result = result.data[0];
							if (!result.Error_Msg) {
								g.getGrid().getStore().reload();
								if (result.Alert_Msg) {
									sw.swMsg.alert(langs('Ошибка отправки на анализатор'), result.Alert_Msg);
								}
							} else {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									fn: function () {
									},
									icon: Ext.Msg.WARNING,
									msg: result.Error_Msg,
									title: langs('Взятие проб')
								});
							}
						}
					}
				});
            } else {
                sw.swMsg.alert(langs('Заявка не выбрана'), langs('Выберите заявку, для которой нужно взять пробы'));
            }
        }});

        this.GridPanel.addActions({
			name:'action_extdir',
			text: langs('Внешнее направление'),
			handler: function (){
				var win = getWnd('swAssistantWorkPlaceWindow');
				var swPersonSearchWindow = getWnd('swPersonSearchWindow');
				if ( swPersonSearchWindow.isVisible() ) {
					sw.swMsg.alert('Окно поиска человека уже открыто', 'Для продолжения необходимо закрыть окно поиска человека.');
					return false;
				}

				var params = {
					MedService_id: win.MedService_id,
					armMode: 'lis',
					action: 'add',
					callback: function(data) {},
					swWorkPlaceFuncDiagWindow: win,
					onSelect: function(pdata)
					{
						getWnd('swPersonSearchWindow').hide();
						var personData = new Object();

						personData.Person_id = pdata.Person_id;
						personData.Person_IsDead = pdata.Person_IsDead;
						personData.Person_Firname = pdata.PersonFirName_FirName;
						personData.Person_Surname = pdata.PersonSurName_Surname;
						personData.Person_Secname = pdata.PersonSecName_Secname;
						personData.PersonEvn_id = pdata.PersonEvn_id;
						personData.Server_id = pdata.Server_id;
						personData.Person_Birthday = pdata.Person_Birthday;

						getWnd('swDirectionMasterWindow').show({
							type: 'ExtDirLab',
							MedServiceType_SysNick: win.MedServiceType_SysNick,
							dirTypeData: {DirType_id: 10, DirType_Code: 9, DirType_Name: 'На исследование'},
							date: null,
							personData: personData,
							onClose: function() {
								this.buttons[0].show();
								this.buttons[1].show();
							},
							onDirection: function (dataEvnDirection_id) {
								var EvnDirId = false;
							    if(dataEvnDirection_id.EvnDirection_id) {
									EvnDirId = dataEvnDirection_id.EvnDirection_id;
                                } else {
							        if(dataEvnDirection_id.evnDirectionData && dataEvnDirection_id.evnDirectionData.EvnDirection_id){
										EvnDirId = dataEvnDirection_id.evnDirectionData.EvnDirection_id;
									}
								}
                                if(!EvnDirId) {
									sw.swMsg.alert(langs('Сообщение'), langs('Мастер выписки направлений не вернул идентификатор направления.'));
									return false;
                                }
								if (getWnd('swEvnLabRequestEditWindow').isVisible()) {
									sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования заявки уже открыто. Для продолжения необходимо закрыть окно редактирования заявки.'));
									return false;
								}


								if (getWnd('swEvnLabRequestEditWindow').isVisible()) {
									sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования заявки уже открыто. Для продолжения необходимо закрыть окно редактирования заявки.'));
									return false;
								}

								if (win.labMode == 0) {
									var viewframe = win.GridPanel;
								} else {
									var viewframe = win.LabSampleGridPanel;
								}

								var grid = viewframe.getGrid();

								Ext.Ajax.request({
									params: {EvnDirection_id: EvnDirId },
									url: '/?c=EvnDirection&m=getDataEvnDirection',
									callback: function(options, success, response) {
										if ( success ) {
											var response_obj = Ext.util.JSON.decode(response.responseText);
											if(response_obj[0]) {
												var data = response_obj[0];
												var params2 = Object.assign(data,false,true);
												params2.action = 'edit';
												params2.ARMType = win.MedServiceType_SysNick;
												params2.swAssistantWorkPlaceWindow = win;
												params2.callback = function(data) {
													// здесь функция должна проверять ид который приходит назад, находить его в списке и устанавливать на него фокус
													viewframe.loadData({valueOnFocus: {EvnLabRequest_id: data.EvnLabRequest_id}});
												};
												params2.MedService_id = win.MedService_id;
											/*	
												params2.MedService_sid = win.MedService_id;
												params2.MedStaffFact_id = data.MedStaffFact_id;
												params2.LpuSection_id = data.LpuSection_id;*/
												params2.EvnDirection_id = EvnDirId;
												params2.Person_id = personData.Person_id;
												params2.PersonEvn_id = personData.PersonEvn_id;
												params2.Server_id = personData.Server_id;
												params2.ExtDirection = true;

												getWnd('swEvnLabRequestEditWindow').show(params2);
											}
										}
									}
								});
							}
						});
					},
					searchMode: 'all'
				};
				getWnd('swPersonSearchWindow').show(params);
        }});

        // this.LabSampleGridPanel.addActions({name:'action_get_barcode', text: '', handler: function() {}});

		this.LabSampleGridPanel.addActions({
			name: 'action_lis_sample', text: langs('Проверить результат'), handler: function () {
				var g = win.LabSampleGridPanel;
				var selections = g.getGrid().getSelectionModel().getSelections();
				var arraySampleId = [];

				if(win.isFormIfa()) {
					sw.swMsg.alert('Сообщение','Недоступно для лаборатории ИФА');
					return;
				}

				for (var key in selections)
					if (selections[key].data) {
						arraySampleId.push({
							'id': selections[key].data['EvnLabSample_id'].toString(),
							'barcode': selections[key].data['EvnLabSample_BarCode'].toString(),
							'analyzer2way': selections[key].data['Analyzer_2wayComm'].toString()
						});
					}
				var params = {};
				params.EvnLabSamples = Ext.util.JSON.encode(arraySampleId);

				if (g.getGrid().getSelectionModel().getCount() > 0) {
					win.getLoadMask(langs('Получение результатов с анализатора')).show();

					Ext.Ajax.request({
						url: '/?c=' + getLabController() + '&m=getResultSamples',
						params: params,
						callback: function (opt, success, response) {
							win.getLoadMask().hide();
							if (success && response.responseText != '') {
								var result = Ext.util.JSON.decode(response.responseText);
								if (result.success) {
									g.getGrid().getStore().reload();
									showSysMsg(langs('Результаты анализов получены с анализатора и сохранены в пробе'), langs('Получение результатов'));
								} else {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function () {
										},
										icon: Ext.Msg.WARNING,
										msg: result.Error_Msg,
										title: langs('Получение результатов')
									});
								}
							}
						}
					});

					if (getRegionNick() == 'vologda') {
						var MedService_id = getGlobalOptions().CurMedService_id;
						Ext.Ajax.request({
							url: '/?c=Utils&m=withFileIntegration',
							params: {MedService_id: MedService_id},
							success: function (response) {
								if (response.responseText != '') {
								    var resp = Ext.util.JSON.decode(response.responseText);
								    if (!Ext.isEmpty(resp[0]) && resp[0]) {
										var evnLabRequest_ids = [];
										for (var key in selections)
											if (selections[key].data) {
												evnLabRequest_ids.push(selections[key].data['EvnLabRequest_id']);
											}
										var curParams = {
											'EvnLabRequest_ids': Ext.util.JSON.encode(evnLabRequest_ids),
											'lpu_nick': getGlobalOptions().lpu_nick,
											'lpu_id': getGlobalOptions().lpu_id
										};
										Ext.Ajax.request({
											url: '/?c=InnovaSysService&m=makeUnloadRequests',
											params: curParams,
											failure: function () {
												sw.swMsg.alert(langs('Ошибка отправки на анализатор'), 'Не удалось отправить пробы в ЛИС');
											}
										});
                                    }
								}
							}
						});
					}
				} else {
					sw.swMsg.alert(langs('Проба не выбрана'), langs('Выберите пробу, результаты которой требуется получить с анализатора'));
				}
			}
		});

        this.LabSampleGridPanel.addActions({name:'action_outsourcing_create', text: langs('Аутсорсинг'), handler: function (){
            win.sendToLis();
        }});

        this.LabSampleGridPanel.addActions({name:'action_lis_create', text: langs('Отправить на анализатор'), handler: function (){//Отправить на анализатор
            win.sendToLis();
        }});

        this.LabSampleGridPanel.addActions({name:'action_lis_selectanalyzer', text: langs('Выбрать анализатор'), handler: function (){
            var g = win.LabSampleGridPanel;

            var selections = g.getGrid().getSelectionModel().getSelections();
            var ArrayId = [];
            var MedService_ids = [];

            for (var key in selections) {
                if (selections[key].data) {
                    ArrayId.push(selections[key].data['EvnLabSample_id'].toString());

                    if (!selections[key].data['MedService_id'].toString().inlist(MedService_ids)) {
                        MedService_ids.push(selections[key].data['MedService_id'].toString());
                    }
                }
            }
            var params = {}
            params.EvnLabSamples = Ext.util.JSON.encode(ArrayId);
            params.Analyzer_IsNotActive = 1;
            params.MedService_id = null;

            if (MedService_ids.length == 1) {
                params.MedService_id = MedService_ids[0];
            }

            var createAnalyzerMenu = function(analyzers) {
                var analyzerMenu = new Ext.menu.Menu({
                    width: 300
                });
                analyzerMenu.addListener('beforeshow', function(m) {
                    swSetMaxMenuHeight(m, 300);
                });
                analyzerMenu.add(new Ext.menu.Item({
                    id: -1,
                    text: langs('Не выбран'),
                    disabled: false,
                    handler: function(item) {
                        win.saveAnalyzerForLabSamples(params, item, selections);
                    }
                }));
                for (var i=0; i < analyzers.length; i++) {
                    analyzerMenu.add(new Ext.menu.Item({
                        id: analyzers[i].Analyzer_id,
                        text: analyzers[i].Analyzer_Name,
                        disabled: analyzers[i].disabled,
                        handler: function(item) {
                            win.saveAnalyzerForLabSamples(params, item, selections);
                        }
                    }));
                }
                analyzerMenu.show(Ext.get('id_action_lis_selectanalyzer'),'tl-bl?');
            };

            if (Ext.isEmpty(params.MedService_id)) {
                createAnalyzerMenu([]);
            } else if (g.getGrid().getSelectionModel().getCount() > 0) {
                win.getLoadMask(langs('Получение списка доступных анализаторов для выбранных проб...')).show();
                // получаем выделенную запись
                Ext.Ajax.request({
                    url: '/?c=Analyzer&m=loadList',
                    params: params,
                    callback: function(opt, success, response) {
                        win.getLoadMask().hide();
                        if (success && response.responseText != '') {
                            var response_obj = Ext.util.JSON.decode(response.responseText);
                            if (Ext.isArray(response_obj) && response_obj.length >= 0) {
                                createAnalyzerMenu(response_obj);
                            }
                        }
                    }
                });
            } else {
                sw.swMsg.alert(langs('Проба не выбрана'), langs('Выберите пробу, для которой нужно выбрать анализатор'));
            }
        }});

        this.LabSampleGridPanel.setActionHidden('action_lis_create', win.MedServiceType_SysNick.inlist(['pzm']) || win.MedService_IsExternal);
        this.LabSampleGridPanel.setActionHidden('action_outsourcing_create', !win.MedServiceType_SysNick.inlist(['reglab']) && (win.MedServiceType_SysNick.inlist(['pzm']) || !win.MedService_IsExternal));
        this.LabSampleGridPanel.setActionHidden('action_lis_sample', win.MedServiceType_SysNick.inlist(['reglab']));

		if (
			getRegionNick() == 'adygeya'
			&& getGlobalOptions().CurMedService_IsExternal
			&& getGlobalOptions().CurMedService_IsExternal == 2
			&& win.MedServiceType_SysNick.inlist(['lab'])
		) {
			this.LabSampleGridPanel.setActionHidden('action_outsourcing_create', true);
			this.LabSampleGridPanel.setActionHidden('action_lis_sample', true);

			if (!Ext.isEmpty(this.GridPanel.getAction('action_lis_sample'))) {
				this.GridPanel.setActionHidden('action_lis_sample', true);
			}
			if (!Ext.isEmpty(this.GridPanel.getAction('action_outsourcing_create'))) {
				this.GridPanel.setActionHidden('action_outsourcing_create', true);
			}
		}
        this.LabSampleGridPanel.addActions({name:'action_lis_approve', text: langs('Одобрить'), handler: function (){
			if ( !win.approveIsAllowed() ) return;
			var selections = win.LabSampleGridPanel.getGrid().getSelectionModel().getSelections();
			if (selections.length < 0) {
				sw.swMsg.alert(langs('Проба не выбрана'), langs('Выберите пробу, результаты которой требуется одобрить'));
			}

			var allSamples = [], normalSamples = [];
			var pathologyFlag = false;
			for (var i = 0; i < selections.length; i++) {
				var id = selections[i].id;
				if (win.globalSampleList.pathologySamples.indexOf(id) == -1) {
					normalSamples.push(id);
				} else pathologyFlag = true;
				allSamples.push(id);

			}

			var params = {};
			params.onlyNormal = 1;
			params.EvnLabSamples = Ext.util.JSON.encode(normalSamples);

			if (pathologyFlag == false) {
				win.approveEvnLabSampleResults(params);
			} else {
				Ext.Msg.show({
					title: 'Одобрение результатов',
					msg: 'В выбранных пробах имеются тесты с патологиями. Выберите дальнейшее действие.',
					icon: Ext.Msg.WARNING,
					buttons: {
						yes: 'Одобрить без патологий',
						no: 'Одобрить все пробы',
						cancel: 'Отмена'
					},
					fn: function (btn, text){
						if (btn == 'yes') {
							win.approveEvnLabSampleResults(params);
						} else if (btn == 'no') {
							Ext.Msg.show({
								title: 'Одобрение результатов',
								msg: 'Будут одобрены все тесты, в том числе с выявленной патологией. Продолжить?',
								icon: Ext.Msg.WARNING,
								buttons: {
									yes: 'Ок',
									no: 'Одобрить без патологий',
									cancel: 'Отмена'
								},
								fn: function (btn, text){
									if (btn == 'yes') {
										params.EvnLabSamples = Ext.util.JSON.encode(allSamples);
										params.onlyNormal = 1;
										win.approveEvnLabSampleResults(params);
									} else if (btn == 'no') {
										win.approveEvnLabSampleResults(params);
									}
								},
							});
						}
					},
				});
			}
        }});

        this.LabSampleGridPanel.setActionHidden('action_lis_approve', win.MedServiceType_SysNick == 'pzm');

        // скрываем или открываем кнопки для регистрационной службы/лаборатории
        var actions_list = [
        	'action_Timetable',
			'action_Usluga',
			'action_Reactive',
			/*'action_AnalyzerWorksheetJournal',*/
			'action_PZ',
			'action_Shedule',
			'action_Settings',
			'action_MSL_manage',
			'action_JourNotice',
			'action_reports',
			'action_Templ',
			'action_Defect',
			'action_Form250u',
			'action_CanceledRequests',
			'action_EvnUslugaParSearch',
			'action_DirectionJournal',
			'action_References',
			'actions_settings'
		];
        if (win.MedServiceType_SysNick == 'reglab') {
            actions_list = [
            	'action_Timetable',
				'action_PZ',
				'action_Podr',
				'action_JourNotice',
				'action_reports',
				'action_Defect',
				'action_Form250u',
				'action_CanceledRequests',
				'action_EvnUslugaParSearch',
				'action_PrintBarcodes',
				'action_sendMbu'
			];
        }
        if (win.MedServiceType_SysNick == 'pzm') {
            actions_list = [
            	'action_Timetable',
				'action_EvnUslugaParSearch',
				'action_Defect',
				'action_CanceledRequests'
			];
            if (getRegionNick() != 'ufa') {
                actions_list.push('action_MSLManage');
            }
        }
		if (win.MedServiceType_SysNick.inlist(['lab','reglab'])) {
			actions_list.push('action_AnalyzerQualityControl');
		}
		if (getRegionNick() == 'perm' && win.MedServiceType_SysNick.inlist(['lab','reglab'])) {
			actions_list.push('action_AnalyzerControlSeries');
		}
		if (win.MedServiceType_SysNick.inlist(['pzm','reglab'])) {
			actions_list.push('action_DirectionCVI');
		}

        for(var k in this.LeftPanel.actions) {
            if (k.inlist(actions_list)) {
                this.LeftPanel.actions[k].setHidden(false);
            } else {
                this.LeftPanel.actions[k].setHidden(true);
            }
        }

        if (win.MedServiceType_SysNick == 'pzm') {
            win.setLabMode(0);
            Ext.getCmp(win.id + 'modeLabRequest').toggle(true);

            // скрыть переключатели Заявки / Пробы
            this.formActions.modeLabRequest.hide();
            this.formActions.modeLabSample.hide();

            // скрыть вкладки
            this.LabRequestTabPanel.hideTabStripItem('tab_new');
            this.LabRequestTabPanel.hideTabStripItem('tab_work');
            this.LabRequestTabPanel.hideTabStripItem('tab_done');
            this.LabRequestTabPanel.hideTabStripItem('tab_approved');
			this.LabRequestTabPanel.hideTabStripItem('tab_notdone');
			this.LabRequestTabPanel.unhideTabStripItem('tab_count_fp');

            if (getRegionNick() == 'vologda') {
				if (getRegionNick() == 'vologda' && win.MedServiceType_SysNick == 'pzm') {
					Ext.Ajax.request({
						url: '/?c=Utils&m=withFileIntegration',
						params: {MedService_id: getGlobalOptions().CurMedService_id},
						success: function (response) {
							if (response.responseText != '') {
								var resp = Ext.util.JSON.decode(response.responseText);
								if (!Ext.isEmpty(resp[0]) && resp[0]) {
									win.GridPanel.setActionHidden('action_outsourcing_create', false);
									win.fileIntegration = true;
								}
							}
						},
						failure: function(response, opts){
							sw.swMsg.alert(langs('Ошибка отправки на анализатор'), 'Не удалось отправить пробы в ЛИС');
						}
					});
				}
            }

        } else {
            this.formActions.modeLabRequest.show();
            this.formActions.modeLabSample.show();

            this.LabRequestTabPanel.unhideTabStripItem('tab_new');
            this.LabRequestTabPanel.unhideTabStripItem('tab_work');
            this.LabRequestTabPanel.unhideTabStripItem('tab_done');
            this.LabRequestTabPanel.unhideTabStripItem('tab_approved');
			this.LabRequestTabPanel.unhideTabStripItem('tab_notdone');
			this.LabRequestTabPanel.hideTabStripItem('tab_count_fp');
        }

        // высота равна родителю
        this.lastSize = null;
        this.setHeight(this.getEl().parent().getHeight());

		var colModel = this.GridPanel.getColumnModel();
		colModel.setHidden(colModel.findColumnIndex('EvnLabRequest_RegNum'), win.MedServiceType_SysNick != 'lab' );
        colModel.setHidden(colModel.findColumnIndex('MedService_Nick'), win.MedServiceType_SysNick == 'lab');
        colModel.setHidden(colModel.findColumnIndex('EvnLabRequest_SampleNum'), win.MedServiceType_SysNick != 'pzm');

		var labColModel = this.LabSampleGridPanel.getColumnModel();
        labColModel.setHidden(labColModel.findColumnIndex('EvnLabRequest_RegNum'), win.MedServiceType_SysNick != 'lab');

        // интервал проверки новых проб
        if(win.intervalCheckSamples)
        {
            clearInterval(win.intervalCheckSamples);
        }
        win.intervalCheckSamples = setInterval(function(){
            win.checkSamples();
        },120000);
        // выполняем первый раз
        win.checkSamples();

        this.GridPanel.setParam('limit', 50);
        this.GridPanel.setParam('start', 0);
        var today = (new Date()).format('d.m.Y');
        this.GridPanel.setParam('begDate', today);
        this.GridPanel.setParam('endDate', today);

        win.doSearch('day');

        if (this.MedServiceType_SysNick == 'lab') {
            // делаем запрос непривязанных услуг, если есть такие то открываем форму связывания услуг с анализаторами
            Ext.Ajax.request({
                url: '/?c=AnalyzerTest&m=getUnlinkedUslugaComplexMedServiceCount',
                params: {
                    MedService_id: win.MedService_id
                },
                callback: function(opt, success, response) {
                    if (success && response.responseText != '') {
                        var result  = Ext.util.JSON.decode(response.responseText);
                        if (result.cnt > 0) {
                            getWnd('swUslugaComplexMedServiceLinkToAnalyzerWindow').show({
                                MedService_id: win.MedService_id
                            });
                        }
                    }
                }
            });
            
			//#PROMEDWEB-9689 проверям реактивы на срок годности
			if (!Ext.isEmpty(Ext.globalOptions.lis.reagents_GodnDate)) {
				Ext.Ajax.request({
					url: '/?c=Assistant&m=checkReagentsGodnDate',
					params: {MedService_id: win.MedService_id, ReagentsGodnDate: Ext.globalOptions.lis.reagents_GodnDate},
					callback: function (opt, success, response) {
						var result = JSON.parse(response.responseText);
						if (result.length > 0) {
							sw.swMsg.show({
								title: 'Сообщение',
								icon: Ext.Msg.WARNING,
								msg: 'Найдены реагенты с истекающим сроком годности, нажмите на кнопку «Печать списка реагентов» для просмотра списка',
								buttons: {ok: 'Печать списка реагентов', yes: 'Ок'},
								fn: function (btn) {
									if (btn == 'ok') {
										window.open('/?c=Assistant&m=printReagentsGodnDate&MedService_id=' + win.MedService_id + '&ReagentsGodnDate=' + Ext.globalOptions.lis.reagents_GodnDate, '_blank');
									}
								}.bind(this)
							});
						}
					}
				});
			}
        }
        this.focusOnGrid();
		if (win.formMode) {
			win.setFormMode(win.formMode);
		}
		win.formActions.methodsIFA.getStore().baseParams = { MedService_id: win.MedService_id };
		win.formActions.analyzerTestIFA.getStore().baseParams.MedService_id = win.MedService_id;
		win.tabletGrid.setParam('MedService_id', win.MedService_id);
    },
    /**
     * Открывает форму редактирования заявки на лабораторное исследование
     * @param action
     */
    openLabRequestEditWindow: function(action) {

        if ( action != 'add' && action != 'edit' && action != 'view' ) return false;

        var swPersonSearchWindow = getWnd('swPersonSearchWindow');
        if ( action == 'add' && swPersonSearchWindow.isVisible() ) {

            sw.swMsg.alert(
                langs('Окно поиска человека уже открыто'),
                langs('Для продолжения необходимо закрыть окно поиска человека.')
            );

            return false;
        }

        if (getWnd('swEvnLabRequestEditWindow').isVisible()) {

            sw.swMsg.alert(
                langs('Сообщение'),
                langs('Окно редактирования заявки уже открыто. Для продолжения необходимо закрыть окно редактирования заявки.')
            );

            return false;
        }

        var gridPanel = this.LabSampleGridPanel;
        if (this.labMode == 0) gridPanel = this.GridPanel;

        var win = this,
            grid = gridPanel.getGrid(),
            params = new Object();

        params.action = action;
        params.ARMType = this.MedServiceType_SysNick;
        params.MedService_id = this.MedService_id;

        params.callback = function(retParams) {

            if (win.ElectronicQueuePanel.electronicQueueEnable) {
                if (action == 'edit') {

                    if (retParams && retParams.callback
                        && typeof retParams.callback === 'function'
                    ) { retParams.callback(); } // выполняем кэллбэк
                }
            } else {

                // здесь функция должна проверять
                // ид который приходит назад, находить его
                // в списке и устанавливать на него фокус

                gridPanel.loadData({
                    valueOnFocus: {EvnLabRequest_id: retParams.EvnLabRequest_id}
                });
            }
        };

        if ( action == 'add' ) {

            swPersonSearchWindow.show({

                armMode: 'LIS',
                onClose: function() {

                    if (grid.getSelectionModel().getSelected()) {

                        grid.getView().focusRow(
                            grid.getStore().indexOf(
                                grid.getSelectionModel().getSelected()
                            )
                        );

                    } else { grid.getSelectionModel().selectFirstRow(); }

                }.createDelegate(this),

                onSelect: function(person_data) {

                    swPersonSearchWindow.hide();

                    params.Person_id = person_data.Person_id;
                    params.PersonEvn_id = person_data.PersonEvn_id;
                    params.Server_id = person_data.Server_id;
                    params.Person_Firname = person_data.Person_Firname;//Параметры для печати на принтере Zebra
                    params.Person_Secname = person_data.Person_Secname;//Параметры для печати на принтере Zebra
                    params.Person_Surname = person_data.Person_Surname;//Параметры для печати на принтере Zebra

                    // При попытке добавить пациента без записи по кнопке «Добавить», перед отображением формы создания заявки, выполняется поиск заявок данного пациента в статусе "Новая" , созданных 3 месяца назад от текущей даты и позднее.
                    getWnd('swEvnLabRequestSelectWindow').show({
                        Person_id: params.Person_id,
                        MedService_id: params.MedService_id,
                        ARMType: params.ARMType,
                        onNewEvnLabRequest: function() {
                            getWnd('swEvnLabRequestEditWindow').show(params);
                        }
                    });

                }, searchMode: 'all'
            });

        } else {

            var record = grid.getSelectionModel().getSelected();

            if (win.ElectronicQueuePanel.electronicQueueEnable) {
                // данные с выбранной строки
                var electronicQueueData = (win.ElectronicQueuePanel.electronicQueueData
                        ? win.ElectronicQueuePanel.electronicQueueData
                        : win.ElectronicQueuePanel.getElectronicQueueData()
                );

                if (!record) record = win.ElectronicQueuePanel.getLastSelectedRecord();

                // сбросим временные данные
                if (win.ElectronicQueuePanel.electronicQueueData)
                    win.ElectronicQueuePanel.electronicQueueData = null;

                params.userMedStaffFact = this.userMedStaffFact;
                params.electronicQueueData = electronicQueueData;
            }

            if ( !record || !record.get('EvnDirection_id') ) {
                sw.swMsg.alert(langs('Ошибка'), langs('Не выбрана заявка из списка'));
                return false;
            }

            //для печати на принтере Zebra
            params.Person_ShortFio = record.get('Person_ShortFio');
            params.EvnDirection_id = record.get('EvnDirection_id');
            params.Person_id = record.get('Person_id');
            params.PersonEvn_id = record.get('PersonEvn_id');
            params.Server_id = record.get('Server_id');

            getWnd('swEvnLabRequestEditWindow').show(params);
        }
    },
    focusOnGrid: function() {

        // фокус туда где работает сканер штрих-кодов
        var gridPanel = this.LabSampleGridPanel;
        if (this.labMode == 0) gridPanel = this.GridPanel;

        if (gridPanel.ViewToolbar.items.get('id_action_get_barcode')) {
            gridPanel.ViewToolbar.items.get('id_action_get_barcode').focus();
        }
    },
    getPeriodToggle: function (mode)
    {
        switch(mode)
        {
        case 'day':
            return this.formActions.day.items[0];
            break;
        case 'week':
            return this.formActions.week.items[0];
            break;
        case 'month':
            return this.formActions.month.items[0];
            break;
        case 'range':
            return this.formActions.range.items[0];
            break;
        default:
            return null;
            break;
        }
    },
    getCurrentDateTime: function() {
        if (!getGlobalOptions().date) {
            frm.getLoadMask(LOAD_WAIT).show();
            Ext.Ajax.request({
                url: C_LOAD_CURTIME,
                callback: function(opt, success, response) {
                    if (success && response.responseText != '') {
                        var result  = Ext.util.JSON.decode(response.responseText);
                        this.curDate = result.begDate;
                        // Проставляем время и режим
                        this.mode = 'day';
                        this.currentDay();
                        this.getLoadMask().hide();
                    }
                }.createDelegate(this)
            });
        } else {
            this.curDate = getGlobalOptions().date;
            // Проставляем время и режим
            this.mode = 'day';
            this.currentDay();
        }
    },
    addGridFilter: function(tabChange) {
        var win = this;

        if (this.labMode == 0) {
			// считаем количество заявок и выводим в header
			var tab_all = 0;
			var tab_new = 0;
			var tab_work = 0;
			var tab_done = 0;
			var tab_approved = 0;
			var tab_notdone = 0;

			this.GridPanel.getGrid().getStore().each( function(rec) {
				tab_all++;
				switch (Number(rec.get('EvnStatus_id'))) {
					case 1:
						tab_new++;
						break;
					case 2:
						tab_work++;
						break;
					case 3:
						tab_done++;
						break;
					case 4:
						tab_approved++;
						break;
					case 5:
						tab_notdone++;
						break;
				}
			});

			// если на вкладке все, то обновляем кол-во на всех вкладках, иначе только на той на которую перешли
			switch(this.LabRequestTabPanel.getActiveTab().id) {
				case 'tab_all':
				var titleTabDone = getRegionNick() == 'ufa' ? 'Выполненные' : 'С результатами';
				var tabCountFpDesign = [
					'<span title="Новые заявки" class="tab_title_count_fp lrstate1">' + tab_new,
					'<span title="В работе" class="tab_title_count_fp lrstate3">' + tab_work,
					'<span title=' + titleTabDone + ' class="tab_title_count_fp lrstate4">' + tab_done,
					'<span title="Одобренные" class="tab_title_count_fp lrstate5">' + tab_approved,
					'<span title="Невыполненные" class="tab_title_count_fp lrstate6">' + tab_notdone
				].join('</span>\n');

					win.LabRequestTabPanel.setTabTitle(0, "<div class='tab_title'>Все заявки</div> <div class='tab_title_count'>"+tab_all+"</div>");
					win.LabRequestTabPanel.setTabTitle(1, "<div class='tab_title'>Новые заявки</div> <div class='tab_title_count lrstate1'>"+tab_new+"</div>");
					win.LabRequestTabPanel.setTabTitle(2, "<div class='tab_title'>В работе</div> <div class='tab_title_count lrstate3'>"+tab_work+"</div>");
					win.LabRequestTabPanel.setTabTitle(3, "<div class='tab_title'>"+(getRegionNick() == 'ufa' ? 'Выполненные' : 'С результатами')+"</div> <div class='tab_title_count lrstate4'>"+tab_done+"</div>");
					win.LabRequestTabPanel.setTabTitle(4, "<div class='tab_title'>Одобренные</div> <div class='tab_title_count lrstate5'>"+tab_approved+"</div>");
					win.LabRequestTabPanel.setTabTitle(5, "<div class='tab_title'>Невыполненные</div> <div class='tab_title_count lrstate6'>"+tab_notdone+"</div>");
					win.LabRequestTabPanel.setTabTitle(6, tabCountFpDesign);
					break;
				case 'tab_new':
					win.LabRequestTabPanel.setTabTitle(1, "<div class='tab_title'>Новые заявки</div> <div class='tab_title_count lrstate1'>"+tab_new+"</div>");
					break;
				case 'tab_work':
					win.LabRequestTabPanel.setTabTitle(2, "<div class='tab_title'>В работе</div> <div class='tab_title_count lrstate3'>"+tab_work+"</div>");
					break;
				case 'tab_done':
					win.LabRequestTabPanel.setTabTitle(3, "<div class='tab_title'>"+(getRegionNick() == 'ufa' ? 'Выполненные' : 'С результатами')+"</div> <div class='tab_title_count lrstate4'>"+tab_done+"</div>");
					break;
				case 'tab_approved':
					win.LabRequestTabPanel.setTabTitle(4, "<div class='tab_title'>Одобренные</div> <div class='tab_title_count lrstate5'>"+tab_approved+"</div>");
					break;
				case 'tab_notdone':
					win.LabRequestTabPanel.setTabTitle(5, "<div class='tab_title'>Невыполненные</div> <div class='tab_title_count lrstate6'>"+tab_notdone+"</div>");
					break;
			}
        } else {
            var clearFilter = false;
            var LabSampleStatus_id = null;
            // получаем tab на котором стоим, в зависимости от него фильтруем
            switch(this.LabSampleTabPanel.getActiveTab().id) {
                case 'tab_all':

                break;
                case 'tab_new':
                    LabSampleStatus_id = 1;
                    if (win.filterNewELSByDate == 0) {
                        // clearFilter = true;
                    }
                break;
                case 'tab_work':
                    LabSampleStatus_id = 2;
                    if (win.filterWorkELSByDate == 0) {
                        // clearFilter = true;
                    }
                break;
                case 'tab_done':
                    LabSampleStatus_id = 3;
                    if (win.filterDoneELSByDate == 0) {
                        // clearFilter = true;
                    }
                break;
                case 'tab_approved':
                    LabSampleStatus_id = 4;
                break;
                case 'tab_defect':
                    LabSampleStatus_id = 5;
                break;
            }

            if (tabChange) {
                if (clearFilter) {
                    win.dateMenu.setValue(null);
                } else {
                    win.restorePeriod();
                }
            }

            this.LabSampleGridPanel.getGrid().getStore().clearFilter();

            // считаем количество заявок и выводим в header
            var tab_all = 0;
            var tab_new = 0;
            var tab_work = 0;
            var tab_done = 0;
            var tab_approved = 0;
            var tab_defect = 0;

            this.LabSampleGridPanel.getGrid().getStore().each( function(rec) {
                tab_all++;
                switch (Number(rec.get('LabSampleStatus_id'))) {
                    case 1:
                        tab_new++;
                        break;
                    case 2:
                    case 7:
                        tab_work++;
                        break;
                    case 3:
                        tab_done++;
                        break;
                    case 4:
                    case 6:
                        tab_approved++;
                        break;
                    case 5:
                        tab_defect++;
                        break;
                }
            });

            win.LabSampleTabPanel.setTabTitle(0, "<div class='tab_title'>Все пробы</div> <div class='tab_title_count'>"+tab_all+"</div>");
            win.LabSampleTabPanel.setTabTitle(1, "<div class='tab_title'>Новые пробы</div> <div class='tab_title_count lsstate1'>"+tab_new+"</div>");
            win.LabSampleTabPanel.setTabTitle(2, "<div class='tab_title'>В работе</div> <div class='tab_title_count lsstate2'>"+tab_work+"</div>");
            win.LabSampleTabPanel.setTabTitle(3, "<div class='tab_title'>"+(getRegionNick() == 'ufa' ? 'Выполненные' : 'С результатами')+"</div> <div class='tab_title_count lsstate3'>"+tab_done+"</div>");
            win.LabSampleTabPanel.setTabTitle(4, "<div class='tab_title'>Одобренные</div> <div class='tab_title_count lsstate4'>"+tab_approved+"</div>");
            win.LabSampleTabPanel.setTabTitle(5, "<div class='tab_title'>Забракованные</div> <div class='tab_title_count lsstate5'>"+tab_defect+"</div>");


            this.LabSampleGridPanel.getGrid().getStore().filterBy(function(rec) {
                if (!Ext.isEmpty(LabSampleStatus_id)) {
                    if (!Ext.isEmpty(rec.get('LabSampleStatus_id'))) {
                        var recLabSampleStatus_id = rec.get('LabSampleStatus_id');
                        if (recLabSampleStatus_id == 6) {
                            recLabSampleStatus_id = 4;
                        }
                        if (recLabSampleStatus_id == 7) {
                            recLabSampleStatus_id = 2;
                        }
                        if (recLabSampleStatus_id == LabSampleStatus_id) {
                            return true;
                        }
                    }

                    return false;
                } else {
                    return true;
                }
            });
        }
    },
    doSearch: function(mode, findEvnLabRequest_id)
    {
        var win = this;

        win.savePeriod();
        if (Ext.isEmpty(this.MedService_id)) { return false; }

        this.searchParams = {
            MedService_id: this.MedService_id,
            MedServiceType_SysNick: this.MedServiceType_SysNick
        };


        var params = this.searchParams || {};

        var btn = this.getPeriodToggle(mode);
        if (btn) {
            if (mode != 'range') {
                if (this.mode == mode) {
                    btn.toggle(true);
                } else {
                    this.mode = mode;
                }
            }
            else {
                btn.toggle(true);
                this.mode = mode;
            }
        }

        params.begDate = Ext.util.Format.date(this.dateMenu.saveValue1, 'd.m.Y');
        params.endDate = Ext.util.Format.date(this.dateMenu.saveValue2, 'd.m.Y');

        if (findEvnLabRequest_id) {
            params.EvnLabRequest_id = findEvnLabRequest_id;
            this.visiblePeriod(false);
            params.begDate = null;
            params.endDate = null;
        }

        if (this.labMode == 0) {
            if (!Ext.isEmpty(this.dateMenu.getValue1()) && !Ext.isEmpty(this.dateMenu.getValue2())) {
                switch(this.LabRequestTabPanel.getActiveTab().id) {
                    case 'tab_work':
                        win.filterWorkELRByDate = 1;
                        break;
                    case 'tab_done':
                        win.filterDoneELRByDate = 1;
                        break;
                }
            } else {
                switch(this.LabRequestTabPanel.getActiveTab().id) {
                    case 'tab_work':
                        win.filterWorkELRByDate = 0;
                        break;
                    case 'tab_done':
                        win.filterDoneELRByDate = 0;
                        break;
                }
            }

			switch(this.LabRequestTabPanel.getActiveTab().id) {
				case 'tab_all':
					break;
				case 'tab_new':
					params.EvnStatus_id = 1;
					break;
				case 'tab_work':
					params.EvnStatus_id = 2;
					break;
				case 'tab_done':
					params.EvnStatus_id = 3;
					break;
				case 'tab_approved':
					params.EvnStatus_id = 4;
					break;
				case 'tab_notdone':
					params.EvnStatus_id = 5;
					break;
			}

            // надо получить параметры фильтров
            let fparams = this.filterRowReq.getFilters();
            let searchOnlyPersonId = true;
            if (fparams)for (var t in fparams) {
                params[t] = fparams[t];
                if ((t !== "Person_id" && !Ext.isEmpty(params[t]))
					|| (t === "Person_id" && Ext.isEmpty(params[t])))
					searchOnlyPersonId = false;
            }

            //Если ищем только по Person_id то по определённому временному отрезку для уфы(#PROMEDWEB-11494)
            if (getRegionNick() === 'ufa' && searchOnlyPersonId) {
				let startSearch = getValidDT(getGlobalOptions().date,'');
				startSearch.setDate( startSearch.getDate() - 7 );
				startSearch = Ext.util.Format.date(startSearch, 'd.m.Y');

				let endSearch = getValidDT(getGlobalOptions().date,'');
				endSearch.setDate( endSearch.getDate() + (7 * 3) );
				endSearch = Ext.util.Format.date(endSearch, 'd.m.Y');
				
				params.begDate = startSearch;
				params.endDate = endSearch;
			}
            
            params.filterWorkELRByDate = win.filterWorkELRByDate;
            params.filterDoneELRByDate = win.filterDoneELRByDate;
			params.formMode = win.formMode;
			if(win.isFormIfa()) {
				params.MethodsIFA_id = win.formActions.methodsIFA.getValue();
				params.AnalyzerTest_id = win.formActions.analyzerTestIFA.getValue();
			}
            if (this.ElectronicQueuePanel.electronicQueueEnable) {

                if (this.ElectronicQueuePanel.showOnlyActive) {
                    params.ElectronicService_id = win.userMedStaffFact.ElectronicService_id;
                    params.ElectronicQueueInfo_id = win.userMedStaffFact.ElectronicQueueInfo_id;
                }
            }

            this.GridPanel.removeAll({clearAll:true});
            this.GridPanel.loadData({globalFilters: params, callback: function() {
                if (!Ext.isEmpty(findEvnLabRequest_id)) {
                    var found = win.GridPanel.getGrid().getStore().findBy(function (rec) {
                        return (rec.get('EvnLabRequest_id') == findEvnLabRequest_id);
                    });
                    if (found >= 0) {
                        win.GridPanel.getGrid().getSelectionModel().selectRow(found);
                        win.GridPanel.getGrid().getView().focusRow(found);
                    }
                }
            }});
            if (win.GridPanel.getGrid().getStore().sortToggle.EvnDirection_setDate == "DESC")
                win.GridPanel.getGrid().getStore().sort('EvnDirection_setDate');
        } else {
            if (!Ext.isEmpty(this.dateMenu.getValue1()) && !Ext.isEmpty(this.dateMenu.getValue2())) {
                switch(this.LabSampleTabPanel.getActiveTab().id) {
                    case 'tab_new':
                        win.filterNewELSByDate = 1;
                        break;
                    case 'tab_work':
                        win.filterWorkELSByDate = 1;
                        break;
                    case 'tab_done':
                        win.filterDoneELSByDate = 1;
                        break;
                }
            } else {
                switch(this.LabSampleTabPanel.getActiveTab().id) {
                    case 'tab_new':
                        win.filterNewELSByDate = 0;
                        break;
                    case 'tab_work':
                        win.filterWorkELSByDate = 0;
                        break;
                    case 'tab_done':
                        win.filterDoneELSByDate = 0;
                        break;
                }
            }

            // надо получить параметры фильтров
            var fparams = this.filterRowLab.getFilters();
            if (fparams)for (var t in fparams) {
                params[t] = fparams[t];
            }

            params.filterNewELSByDate = win.filterNewELSByDate;
            params.filterWorkELSByDate = win.filterWorkELSByDate;
            params.filterDoneELSByDate = win.filterDoneELSByDate;

			params.formMode = win.formMode;
			if(win.isFormIfa()) {
				params.MethodsIFA_id = win.formActions.methodsIFA.getValue();
				params.AnalyzerTest_id = win.formActions.analyzerTestIFA.getValue();
			}
            this.LabSampleGridPanel.removeAll({clearAll:true});
            this.LabSampleGridPanel.loadData({globalFilters: params});
            if(this.LabSampleGridPanel.getGrid().getStore().sortToggle.EvnLabSample_setDT == "DESC")
            this.LabSampleGridPanel.getGrid().getStore().sort('EvnLabSample_setDT');
        }
    },
    coeffRefValues: function(rec, coeff) {
        if (!Ext.isEmpty(coeff)) {
            var UslugaTest_ResultLower = rec.get('UslugaTest_ResultLower');
            var UslugaTest_ResultUpper = rec.get('UslugaTest_ResultUpper');
            var UslugaTest_ResultLowerCrit = rec.get('UslugaTest_ResultLowerCrit');
            var UslugaTest_ResultUpperCrit = rec.get('UslugaTest_ResultUpperCrit');
            var UslugaTest_ResultValue = rec.get('UslugaTest_ResultValue');

            if ( !Ext.isEmpty(UslugaTest_ResultLower) ) {
                UslugaTest_ResultLower = UslugaTest_ResultLower.toString().replace(',', '.');
            }

            if ( !Ext.isEmpty(UslugaTest_ResultUpper) ) {
                UslugaTest_ResultUpper = UslugaTest_ResultUpper.toString().replace(',', '.');
            }

            if ( !Ext.isEmpty(UslugaTest_ResultLowerCrit) ) {
                UslugaTest_ResultLowerCrit = UslugaTest_ResultLowerCrit.toString().replace(',', '.');
            }

            if ( !Ext.isEmpty(UslugaTest_ResultUpperCrit) ) {
                UslugaTest_ResultUpperCrit = UslugaTest_ResultUpperCrit.toString().replace(',', '.');
            }

            if ( !Ext.isEmpty(UslugaTest_ResultValue) ) {
                UslugaTest_ResultValue = UslugaTest_ResultValue.toString().replace(',', '.');
            }

            if (!Ext.isEmpty(UslugaTest_ResultLower)) {
                UslugaTest_ResultLower = UslugaTest_ResultLower * coeff;
            }

            if (!Ext.isEmpty(UslugaTest_ResultUpper)) {
                UslugaTest_ResultUpper = UslugaTest_ResultUpper * coeff;
            }

            if (!Ext.isEmpty(UslugaTest_ResultLowerCrit)) {
                UslugaTest_ResultLowerCrit = UslugaTest_ResultLowerCrit * coeff;
            }

            if (!Ext.isEmpty(UslugaTest_ResultUpperCrit)) {
                UslugaTest_ResultUpperCrit = UslugaTest_ResultUpperCrit * coeff;
            }

            if (!Ext.isEmpty(UslugaTest_ResultValue) && !isNaN(parseFloat(UslugaTest_ResultValue))) {
                UslugaTest_ResultValue = UslugaTest_ResultValue * coeff;
            }

            rec.set('UslugaTest_ResultNorm',UslugaTest_ResultLower + ' - ' + UslugaTest_ResultUpper);
            rec.set('UslugaTest_ResultCrit',UslugaTest_ResultLowerCrit + ' - ' + UslugaTest_ResultUpperCrit);
            rec.set('UslugaTest_ResultLower',UslugaTest_ResultLower);
            rec.set('UslugaTest_ResultUpper',UslugaTest_ResultUpper);
            rec.set('UslugaTest_ResultLowerCrit',UslugaTest_ResultLowerCrit);
            rec.set('UslugaTest_ResultUpperCrit',UslugaTest_ResultUpperCrit);
            rec.set('UslugaTest_ResultValue',UslugaTest_ResultValue);
        }
    },
    setRefValues: function(rec, refvalues) {
        if (!Ext.isEmpty(refvalues.UslugaTest_ResultQualitativeNorms)) {
            rec.set('UslugaTest_ResultQualitativeNorms', refvalues.UslugaTest_ResultQualitativeNorms);
            var resp = Ext.util.JSON.decode(refvalues.UslugaTest_ResultQualitativeNorms);
            var UslugaTest_ResultNorm = '';
            for (var k1 in resp) {
                if (typeof resp[k1] != 'function') {
                    if (UslugaTest_ResultNorm.length > 0) {
                        UslugaTest_ResultNorm = UslugaTest_ResultNorm + ', ';
                    }

                    UslugaTest_ResultNorm = UslugaTest_ResultNorm + resp[k1];
                }
            }
            rec.set('UslugaTest_ResultNorm',UslugaTest_ResultNorm);
            rec.set('UslugaTest_ResultCrit','');
            rec.set('UslugaTest_ResultLower','');
            rec.set('UslugaTest_ResultUpper','');
            rec.set('UslugaTest_ResultLowerCrit','');
            rec.set('UslugaTest_ResultUpperCrit','');
            rec.set('UslugaTest_ResultUnit', refvalues.UslugaTest_ResultUnit);
            rec.set('UslugaTest_Comment', refvalues.UslugaTest_Comment);
            rec.set('RefValues_Name', refvalues.RefValues_Name);
            rec.set('RefValues_id', refvalues.RefValues_id);
            rec.set('Unit_id', refvalues.Unit_id);
        } else {
            rec.set('UslugaTest_ResultQualitativeNorms', '');
            // избавляемся от null'ов:
            if (Ext.isEmpty(refvalues.UslugaTest_ResultLower)) {
                refvalues.UslugaTest_ResultLower = '';
            }
            if (Ext.isEmpty(refvalues.UslugaTest_ResultUpper)) {
                refvalues.UslugaTest_ResultUpper = '';
            }
            if (Ext.isEmpty(refvalues.UslugaTest_ResultLowerCrit)) {
                refvalues.UslugaTest_ResultLowerCrit = '';
            }
            if (Ext.isEmpty(refvalues.UslugaTest_ResultUpperCrit)) {
                refvalues.UslugaTest_ResultUpperCrit = '';
            }

            rec.set('UslugaTest_ResultNorm',refvalues.UslugaTest_ResultLower + ' - ' + refvalues.UslugaTest_ResultUpper);
            rec.set('UslugaTest_ResultCrit',refvalues.UslugaTest_ResultLowerCrit + ' - ' + refvalues.UslugaTest_ResultUpperCrit);
            rec.set('UslugaTest_ResultLower',refvalues.UslugaTest_ResultLower);
            rec.set('UslugaTest_ResultUpper',refvalues.UslugaTest_ResultUpper);
            rec.set('UslugaTest_ResultLowerCrit',refvalues.UslugaTest_ResultLowerCrit);
            rec.set('UslugaTest_ResultUpperCrit',refvalues.UslugaTest_ResultUpperCrit);
            rec.set('UslugaTest_ResultUnit', refvalues.UslugaTest_ResultUnit);
            rec.set('UslugaTest_Comment', refvalues.UslugaTest_Comment);
            rec.set('RefValues_Name', refvalues.RefValues_Name);
            rec.set('RefValues_id', refvalues.RefValues_id);
            rec.set('Unit_id', refvalues.Unit_id);
        }
    },
    queueUpdateEvnLabSample: [],
    processQueueUpdateEvnLabSample: function() {
        var win = this;

        // работаем с очередью
        if (win.queueUpdateEvnLabSample.length < 1) {
			//win.LabSampleGridPanel.getGrid().getStore().reload();
            return false;
        }

        // берём первые параметры из очереди
        var params = win.queueUpdateEvnLabSample[0].params;
        var o = win.queueUpdateEvnLabSample[0].o;

        // признак АРМ Лаборанта для расчетных тестов
        params.EvnLabSample_id = Number(o.record.json.EvnLabSample_id);
        params.UslugaTest_Code = o.record.json.UslugaComplex_Code;

        Ext.Ajax.request({
            url: '/?c=EvnLabSample&m=updateResult',
            params: params,
            failure: function(response, options) {
                // убираем из очереди первый элемент и снова обрабатываем
                win.queueUpdateEvnLabSample.shift();
                win.processQueueUpdateEvnLabSample();
            },
            success: function(response, action) {
                // убираем из очереди первый элемент и снова обрабатываем
                win.queueUpdateEvnLabSample.shift();
                win.processQueueUpdateEvnLabSample();

                var result = Ext.util.JSON.decode(response.responseText);

                if (result[0].Error_Code === null && result[0].Error_Msg === null) {
                    if (o.record) {
                        o.record.commit();
                    }

                    // если есть расчетные тесты
                    if(result[1] !== null)
                    {
                        // по массиву
                        o.grid.getStore().each(function(rec){
                            for(v in result[1]) {
                                if(rec.get('UslugaComplex_Code') == result[1][v].code)
                                {
                                    rec.set('UslugaTest_ResultValue',result[1][v].value);
									rec.set('UslugaTest_setDT', new Date());

                                    // Автоодобрение расчетных тестов
                                    if(rec.get('Analyzer_IsAutoOk') == 2) {
                                        if(result[1][v].value !== '') {
                                            rec.set('UslugaTest_ResultApproved', 2);
                                            rec.set('UslugaTest_Status', langs('Одобрен'));
                                        } else {
                                            rec.set('UslugaTest_ResultApproved', 1);
                                            rec.set('UslugaTest_Status', langs('Назначен'));
                                        }
                                    } else {
                                        rec.set('UslugaTest_ResultApproved', 1);
                                        rec.set('UslugaTest_Status', langs('Выполнен'));
                                    }

                                    rec.commit();
                                }
                            }
                        });
                    }
                    // q: Загружаем в глобальную переменную пробы без патологий
				    win.loadPathologySamples();
                } else {
                    sw.swMsg.show({
                        icon: Ext.MessageBox.WARNING,
                        buttons: Ext.Msg.OK,
                        msg: langs('При сохранении результатов и проверке статуса заявки произошло блокирование записи другими процессами. Проверьте данные, при необходимости исправьте и повторите попытку сохранения.'),
                        title: langs('Ошибка'),
                        fn: function() {
                            if (o.grid) {
                                o.grid.getStore().reload();
                            }
                        }
                    });
                }
            }
        });
    },
    updateEvnLabSample: function(params, o) {
        var win = this;

        // добавляем в очередь
        win.queueUpdateEvnLabSample.push({
            params: params,
            o: o
        });

        // если в очереди уже что то было, выходим
        if (win.queueUpdateEvnLabSample.length > 1) {
            return false;
        }

        win.processQueueUpdateEvnLabSample();
    },
    getGrid: function(rec,element){
        var expanderd = new Ext.ux.grid.RowExpander({actAsTree: true});
        var win = this;

        var EvnUslugaDataGrid = new sw.Promed.ViewFrame({
            useEmptyRecord: false,
            selectionModel: 'multiselect',
            noSelectFirstRowOnFocus: true,
            showCountInTop: false,
            id: 'swEvnUslugaDataGrid_' + rec.get('EvnLabSample_id'),
            autoLoadData: false,
            border: true,
            gridplugins: [Ext.ux.grid.plugins.GroupCheckboxSelection, expanderd],
            defaults: {border: false},
            cls: 'EvnUslugaDataGrid',
            autoExpandColumn: 'autoexpand',
            object: 'EvnLabSample',
            dataUrl: '/?c=EvnLabSample&m=getLabSampleResultGrid',
            region: 'center',
            height: 150,
            width: 'auto',
            saveAtOnce: false,
            toolbar: true,
            clicksToEdit: 1,
            onBeforeEdit: function(o) {
                if (o.field && o.field == 'UslugaTest_ResultValue' && o.record) {
                    var combo = Ext.getCmp(win.id + '_ResultCombo' + rec.get('EvnLabSample_id'));
                    combo.getStore().removeAll();
                    combo.getStore().load({
                        params: {
                            UslugaTest_id: o.record.get('UslugaTest_id')
                        }
                    });
                }

                if (o.field && o.field == 'UslugaTest_ResultUnit' && o.record) {
                    var combo = Ext.getCmp(win.id + '_ResultUnitCombo' + rec.get('EvnLabSample_id'));
                    combo.getStore().removeAll();
                    combo.getStore().load({
                        params: {
                            UslugaTest_id: o.record.get('UslugaTest_id')
                        }
                    });
                }

                if (o.field && o.field == 'RefValues_Name' && o.record) {
                    var combo = Ext.getCmp(win.id + '_AnalyzerTestRefValuesCombo' + rec.get('EvnLabSample_id'));
                    combo.getStore().removeAll();
                    combo.getStore().load({
                        params: {
                            UslugaTest_id: o.record.get('UslugaTest_id')
                        }
                    });
                }

                var ed = o.grid.getColumnModel().getCellEditor(o.column, o.row);
                if (!ed) {
                    o.cancel = true;
                }

                return o;
            },
            onAfterEdit: function(o) {
                o.grid.stopEditing(true);

				var rec = o.record;
				if (o.field && o.field == 'UslugaTest_ResultValue' && rec) {
					var combo = Ext.getCmp(win.id + '_ResultCombo' + rec.get('EvnLabSample_id'));
					rec.set('UslugaTest_ResultValue', o.rawvalue);

					var isSetValue = o.rawvalue !== '';
					var isAutoOk = isSetValue && rec.get('Analyzer_IsAutoOk') == 2;
					var isAutoGood =  isAutoOk && rec.get('Analyzer_IsAutoGood') == 2;
					var isQualitativeResult = !Ext.isEmpty( rec.get('UslugaTest_ResultQualitativeNorms') );
					var value = rec.get('UslugaTest_ResultValue');
					var upperValue = rec.get('UslugaTest_ResultUpper');
					var lowerValue = rec.get('UslugaTest_ResultLower');

					getFloatResult = function(string) {
						string = string.replace(',', '.');
						if(isNaN(string)) {
							return null;
						};
						return parseFloat(string);
					};

					isPathologicalQuantitativeTest = function(value, lowerValue, upperValue) {
						if(value == null) {
							return true;
						}
						lowerValue = !lowerValue ? -Infinity : lowerValue;
						upperValue = !upperValue ? Infinity : upperValue;
						return value < lowerValue || upperValue < value;
					};

					//debugger;
					if( isAutoGood ) {
						if (isQualitativeResult) {
							var qualitativeNorms = jsonDecode(rec.get('UslugaTest_ResultQualitativeNorms'));
						} else {
							value = getFloatResult(value);
							upperValue = getFloatResult(upperValue);
							lowerValue = getFloatResult(lowerValue);
						}
					}

					// Автоодобрение
					switch (true) {
						case isAutoOk && !isAutoGood:
							rec.set('UslugaTest_Status', isSetValue ? langs('Одобрен') : langs('Назначен'));
							rec.set('UslugaTest_ResultApproved', isSetValue ? 2 : 1);
							!isSetValue || rec.set('UslugaTest_setDT', new Date());
							break;

						case isAutoOk && !isQualitativeResult:
							var isPathologic = isPathologicalQuantitativeTest(value, lowerValue, upperValue);
							rec.set('UslugaTest_Status', !isPathologic ? langs('Одобрен') : langs('Выполнен'));
							rec.set('UslugaTest_ResultApproved', !isPathologic ? 2 : 1);
							!isPathologic || rec.set('UslugaTest_setDT', new Date());
							break;

						case isAutoOk && isQualitativeResult:
							var qualitiveNorms = jsonDecode(rec.get('UslugaTest_ResultQualitativeNorms'));
							var isPathologic = !value.inlist(qualitiveNorms);
							rec.set('UslugaTest_Status', !isPathologic ? langs('Одобрен') : langs('Выполнен'));
							rec.set('UslugaTest_ResultApproved', !isPathologic ? 2 : 1);
							!isPathologic || rec.set('UslugaTest_setDT', new Date());
							break;

						default:
							rec.set('UslugaTest_Status', isSetValue ? langs('Выполнен') :  langs('Назначен'));
							rec.set('UslugaTest_ResultApproved', 1);
							!isSetValue || rec.set('UslugaTest_setDT', new Date());
							break;
					}

                    this.setActionDisabled('action_cancel', o.record.get('UslugaTest_Status') != langs('Назначен'));
					this.onRowSelectionChange();

                    var params = {};
                    params.UslugaTest_id = o.record.get('UslugaTest_id');
                    params.UslugaTest_ResultValue = o.rawvalue;
                    params.updateType = 'value';
                    win.updateEvnLabSample(params, o);
                }

                if (o.field && o.field == 'UslugaTest_ResultUnit' && o.record) {
                    var combo = Ext.getCmp(win.id + '_ResultUnitCombo' + rec.get('EvnLabSample_id'));
                    o.record.set('UslugaTest_ResultUnit', o.rawvalue);
                    o.record.set('Unit_id', combo.getValue());

                    win.coeffRefValues(o.record, combo.getFieldValue('Unit_Coeff'));

                    var refvalues = {};
                    refvalues.UslugaTest_ResultQualitativeNorms = o.record.get('UslugaTest_ResultQualitativeNorms');
                    refvalues.UslugaTest_ResultNorm = o.record.get('UslugaTest_ResultNorm');
                    refvalues.UslugaTest_ResultCrit = o.record.get('UslugaTest_ResultCrit');
                    refvalues.UslugaTest_ResultLower = o.record.get('UslugaTest_ResultLower');
                    refvalues.UslugaTest_ResultUpper = o.record.get('UslugaTest_ResultUpper');
                    refvalues.UslugaTest_ResultLowerCrit = o.record.get('UslugaTest_ResultLowerCrit');
                    refvalues.UslugaTest_ResultUpperCrit = o.record.get('UslugaTest_ResultUpperCrit');
                    refvalues.UslugaTest_ResultUnit = o.record.get('UslugaTest_ResultUnit');
                    refvalues.UslugaTest_Comment = o.record.get('UslugaTest_Comment');
                    refvalues.RefValues_id = o.record.get('RefValues_id');
                    refvalues.Unit_id = o.record.get('Unit_id');

                    var params = {};
                    params.UslugaTest_id = o.record.get('UslugaTest_id');
                    params.UslugaTest_RefValues = Ext.util.JSON.encode(refvalues);
                    params.UslugaTest_ResultValue = o.record.get('UslugaTest_ResultValue');
                    params.updateType = 'value';
                    win.updateEvnLabSample(params, o);
                }

                if (o.field && o.field == 'RefValues_Name' && o.record) {
                    var combo = Ext.getCmp(win.id + '_AnalyzerTestRefValuesCombo' + rec.get('EvnLabSample_id'));
                    win.setRefValues(o.record, {
                        UslugaTest_ResultQualitativeNorms: combo.getFieldValue('UslugaTest_ResultQualitativeNorms'),
                        UslugaTest_ResultUnit: combo.getFieldValue('UslugaTest_ResultUnit'),
                        UslugaTest_Comment: combo.getFieldValue('UslugaTest_Comment'),
                        RefValues_id: combo.getFieldValue('RefValues_id'),
                        Unit_id: combo.getFieldValue('Unit_id'),
                        RefValues_Name: combo.getFieldValue('RefValues_Name'),
                        UslugaTest_ResultLower: combo.getFieldValue('UslugaTest_ResultLower'),
                        UslugaTest_ResultUpper: combo.getFieldValue('UslugaTest_ResultUpper'),
                        UslugaTest_ResultLowerCrit: combo.getFieldValue('UslugaTest_ResultLowerCrit'),
                        UslugaTest_ResultUpperCrit: combo.getFieldValue('UslugaTest_ResultUpperCrit')
                    });

                    var refvalues = {};
                    refvalues.UslugaTest_ResultQualitativeNorms = o.record.get('UslugaTest_ResultQualitativeNorms');
                    refvalues.UslugaTest_ResultNorm = o.record.get('UslugaTest_ResultNorm');
                    refvalues.UslugaTest_ResultCrit = o.record.get('UslugaTest_ResultCrit');
                    refvalues.UslugaTest_ResultLower = o.record.get('UslugaTest_ResultLower');
                    refvalues.UslugaTest_ResultUpper = o.record.get('UslugaTest_ResultUpper');
                    refvalues.UslugaTest_ResultLowerCrit = o.record.get('UslugaTest_ResultLowerCrit');
                    refvalues.UslugaTest_ResultUpperCrit = o.record.get('UslugaTest_ResultUpperCrit');
                    refvalues.UslugaTest_ResultUnit = o.record.get('UslugaTest_ResultUnit');
                    refvalues.UslugaTest_Comment = o.record.get('UslugaTest_Comment');
                    refvalues.RefValues_id = o.record.get('RefValues_id');
                    refvalues.Unit_id = o.record.get('Unit_id');

                    var params = {};
                    params.UslugaTest_id = o.record.get('UslugaTest_id');
                    params.UslugaTest_RefValues = Ext.util.JSON.encode(refvalues);
                    win.updateEvnLabSample(params, o);
                }

                if (o.field && o.field == 'UslugaTest_Comment' && o.record) {
                    var combo = Ext.getCmp(win.id + '_AnalyzerTestRefValuesCombo' + rec.get('EvnLabSample_id'));
                    o.record.set('UslugaTest_Comment', o.rawvalue);

                    var params = {};
                    params.UslugaTest_id = o.record.get('UslugaTest_id');
                    params.UslugaTest_Comment = o.rawvalue;
                    params.updateType = 'comment';
                    win.updateEvnLabSample(params, o);
				}
            },
            grouping: true,
            interceptMouse : function(e){
                var editLink = e.getTarget('.editResearchLink', this.mainBody);
                var commentLink = e.getTarget('.commentResearchLink', this.mainBody);
                var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
                if(hd && !editLink && !commentLink){
                    e.stopEvent();
                    this.toggleGroup(hd.parentNode);
                }
            },
            groupTextTpl: '<b>{[ values.rs[0].data["ResearchName"] ]}</b>&nbsp;&nbsp;&nbsp;<a class="editResearchLink" href="javascript://" onClick="Ext.getCmp(\''+win.id+'\').openResearchEditWindow(this, {[ values.rs[0].data["UslugaTest_pid"] ]});" style="color:#000;font-weight:normal;">Редактировать</a> &nbsp; <img class="commentResearchLink" id="'+win.id+'EvnLabSample_Comment_icon_{[ values.rs[0].data["UslugaTest_pid"] ]}" onClick="Ext.getCmp(\''+win.id+'\').editEvnLabSampleComment(this, {[ values.rs[0].data["UslugaTest_pid"] ]}, 1);" title="Добавить комментарий к исследованию" src="img/icons/comment_icon.png">' +
                '<p class="commentResearchLink" id="'+win.id+'EvnLabSample_Comment_block_{[ values.rs[0].data["UslugaTest_pid"] ]}" style="margin: 7px 0 5px -14px; color: black; font-weight:normal;" onClick="Ext.getCmp(\''+win.id+'\').editEvnLabSampleComment(this, {[ values.rs[0].data["UslugaTest_pid"] ]}, 2);"><img style="margin-right: 7px;" src="img/icons/comment_icon.png"><span style="text-overflow: ellipsis; white-space: nowrap; width: 870px; overflow: hidden; display: inline-block;" id="'+win.id+'EvnLabSample_Comment_text_{[ values.rs[0].data["UslugaTest_pid"] ]}"></span></p>',
            groupingView: {showGroupName: false, showGroupsText: true},
            stringfields:
            [
                {name: 'UslugaTest_id', type: 'int', header: 'UslugaTest_id', key: true, hidden: true},
                {name: 'UslugaTest_pid', type: 'int', group: true, sort: true, direction: 'ASC', header: langs('Группа'), width: 200},
                {name: 'EvnUslugaPar_pComment', type: 'string', hidden: true},
                {name: 'ResearchName', type: 'string', hidden: true},
                {name: 'UslugaComplex_id', type:'int', header: 'UslugaComplex_id', hidden: true},
                {name: 'UslugaComplex_Code', type:'string', header: langs('Код'), width: 80},
                {name: 'UslugaComplex_Name', type: 'string', header: langs('Название теста'), id: 'autoexpand'},
                {name: 'UslugaTest_ResultValue', editor: new sw.Promed.SwQualitativeTestAnswerAnalyzerTestCombo({
                    id: win.id + '_ResultCombo' + rec.get('EvnLabSample_id'),
                    editable: true,
                    forceSelection: false,
                    allowTextInput: true,
                    useRawValueForGrid: true,
                    listeners: {
                        'select': function(combo, record) {
                            combo.setValue(record.get('QualitativeTestAnswerAnalyzerTest_id'));
                            combo.fireEvent('blur', combo);
                        },
                        'blur': function(combo) {
                            EvnUslugaDataGrid.getGrid().stopEditing();
                        }
                    },
                    allowBlank: true,
                    listWidth: 300
                }), header: langs('Результат'), renderer: function(v, p, row){
                    var type = null;
                    var addit = "";
                    var clr = "#000";
                    var UslugaTest_ResultLower = row.get('UslugaTest_ResultLower');
                    var UslugaTest_ResultUpper = row.get('UslugaTest_ResultUpper');
                    var UslugaTest_ResultLowerCrit = row.get('UslugaTest_ResultLowerCrit');
                    var UslugaTest_ResultUpperCrit = row.get('UslugaTest_ResultUpperCrit');
                    var UslugaTest_ResultQualitativeNorms = row.get('UslugaTest_ResultQualitativeNorms');
                    var UslugaTest_ResultValue = row.get('UslugaTest_ResultValue');

                    // https://redmine.swan.perm.ru/issues/41725
                    // Меняем запятую на точку, ибо parseFloat('4,7') = 4, а не 4.7
                    if ( !Ext.isEmpty(UslugaTest_ResultLower) ) {
                        UslugaTest_ResultLower = UslugaTest_ResultLower.toString().replace(',', '.');
                    }

                    if ( !Ext.isEmpty(UslugaTest_ResultUpper) ) {
                        UslugaTest_ResultUpper = UslugaTest_ResultUpper.toString().replace(',', '.');
                    }

                    if ( !Ext.isEmpty(UslugaTest_ResultLowerCrit) ) {
                        UslugaTest_ResultLowerCrit = UslugaTest_ResultLowerCrit.toString().replace(',', '.');
                    }

                    if ( !Ext.isEmpty(UslugaTest_ResultUpperCrit) ) {
                        UslugaTest_ResultUpperCrit = UslugaTest_ResultUpperCrit.toString().replace(',', '.');
                    }

                    if ( !Ext.isEmpty(UslugaTest_ResultValue) ) {
                        UslugaTest_ResultValue = UslugaTest_ResultValue.toString().replace(',', '.');
                    }

                    if (!Ext.isEmpty(UslugaTest_ResultValue)) {
                        if (!Ext.isEmpty(UslugaTest_ResultQualitativeNorms)) {
                            var resp = Ext.util.JSON.decode(UslugaTest_ResultQualitativeNorms);
                            if (!UslugaTest_ResultValue.inlist(resp)) {
                                clr = "#F00";
                            }
                        } else if (!isNaN(parseFloat(UslugaTest_ResultValue))) {
                            UslugaTest_ResultValue = parseFloat(UslugaTest_ResultValue);
                            UslugaTest_ResultLowerCrit = parseFloat(UslugaTest_ResultLowerCrit);
                            UslugaTest_ResultUpperCrit = parseFloat(UslugaTest_ResultUpperCrit);
                            UslugaTest_ResultLower = parseFloat(UslugaTest_ResultLower);
                            UslugaTest_ResultUpper = parseFloat(UslugaTest_ResultUpper);

                            // https://redmine.swan.perm.ru/issues/41725
                            // Поменял на строгие неравенства, т.к. границы диапазона являются допустимыми значениями
                            if (!Ext.isEmpty(UslugaTest_ResultLowerCrit) && UslugaTest_ResultValue < UslugaTest_ResultLowerCrit) {
                                clr = "#F00";
                                addit = "&#x25BC;&#x25BC;";
                            } else if (!Ext.isEmpty(UslugaTest_ResultUpperCrit) && UslugaTest_ResultValue > UslugaTest_ResultUpperCrit) {
                                clr = "#F00";
                                addit = "&#x25B2;&#x25B2;";
                            } else if (!Ext.isEmpty(UslugaTest_ResultLower) && UslugaTest_ResultValue < UslugaTest_ResultLower) {
                                clr = "#F00";
                                addit = "&#x25BC;";
                            } else if (!Ext.isEmpty(UslugaTest_ResultUpper) && UslugaTest_ResultValue > UslugaTest_ResultUpper) {
                                clr = "#F00";
                                addit = "&#x25B2;";
                            }
                        }
                    }

                    if (v == null) {
                        v = "";
                    }

                    if (Ext.isEmpty(v)) {
                        v = "&nbsp;";
                    }

                    return "<span style='color:"+clr+"; float: left;'>"+v+"</span>" + "<span style='color:#F00; float: right;'>" + addit + "</span>";
                }, width: 80},
                {name: 'UslugaTest_ResultUnit', editor: new sw.Promed.SwTestUnitCombo({
                    id: win.id + '_ResultUnitCombo' + rec.get('EvnLabSample_id'),
                    listeners: {
                        'select': function(combo, record) {
                            combo.setValue(record.get('Unit_id'));
                            combo.fireEvent('blur', combo);
                        },
                        'blur': function(combo) {
                            EvnUslugaDataGrid.getGrid().stopEditing();
                        }
                    },
                    allowBlank: true,
                    listWidth: 300
                }), renderer: function(v, p, row) {
                    if (!Ext.isEmpty(v)) {
                        v = "<span class='canbecombobox'>" + v + "</span>";
                    }
                    return v;
                }, header: langs('Ед. изм.'), width: 80},
                {name: 'RefValues_id', type:'int', hidden:true},
                {name: 'Unit_id', type:'int', hidden:true},
                {name: 'UslugaTest_ResultNorm', type: 'string', header: langs('Реф. зн.'), width: 80},
                {name: 'RefValues_Name', editor: new sw.Promed.SwAnalyzerTestRefValuesCombo({
                    id: win.id + '_AnalyzerTestRefValuesCombo' + rec.get('EvnLabSample_id'),
                    listeners: {
                        'select': function(combo, record) {
                            combo.setValue(record.get('AnalyzerTestRefValues_id'));
                            combo.fireEvent('blur', combo);
                        },
                        'blur': function(combo) {
                            EvnUslugaDataGrid.getGrid().stopEditing();
                        }
                    },
                    allowBlank: true,
                    listWidth: 300
                }), renderer: function(v, p, row) {
                    if (!Ext.isEmpty(v)) {
                        v = "<span class='canbecombobox'>" + v + "</span>";
                    }
                    return v;
                }, header: langs('<b>Наименование реф. зн.</b>'), width: 110},
                {name: 'UslugaTest_ResultCrit', type: 'string', hidden: true, header: langs('Критич. диапазон'), width: 160},
                {name: 'UslugaTest_ResultLower', type: 'string', hidden: true},
                {name: 'UslugaTest_ResultUpper', type: 'string', hidden: true},
                {name: 'UslugaTest_ResultLowerCrit', type: 'string', hidden: true},
                {name: 'UslugaTest_ResultUpperCrit', type: 'string', hidden: true},
                {name:'EvnLabRequest_id',type:'int', hidden:true},
                {name: 'UslugaTest_ResultQualitativeNorms', type: 'string', hidden: true},
                {name: 'UslugaTest_Comment', header: langs('Комментарий'), width: 80, editor: new Ext.form.TextField({}), renderer: function(v,m,rec){
                    if (v != null){
                        if (m)
							m.attr = 'title="'+rec.get('UslugaTest_Comment')+'"';
                        return v;
                    }
                }},
				{name: 'UslugaTest_setDT', type: 'timedate', hidden: false, header: langs('Время выполнения'), width: 80},
				{name: 'UslugaTest_Status', header: langs('Статус'), width: 80, renderer: function(v, p, row) {
                    if (v == langs('Не назначен')) {
                        v = "<span class='notprescr'>" + v + "</span>";
                    }
                    return v;
                }},
				{name: 'UslugaTest_CheckDT', header: langs('Время одобрения'), width: 110, renderer: function(v, p, row) {
						if (v != null) {
							return v;
						}
					}},
                {name: 'UslugaTest_ResultApproved', hidden: true, header:langs('Признак одобрения')},
				{name: 'Analyzer_IsAutoOk', hidden: true, header:langs('Автоодобрение')},
				{name: 'Analyzer_IsAutoGood', hidden: true}
            ],
            actions:
            [
                {name:'action_add', disabled: true, hidden: true},
                {name:'action_edit', disabled: true, hidden: true},
                {name:'action_view', disabled: true, hidden: true},
                {name:'action_delete', disabled: true, hidden: true},
                {name:'action_print', disabled: true, hidden: true},
                {name:'action_refresh', disabled: true, hidden: true},
                {name:'action_save', url: '/?c=EvnLabSample&m=updateResult', hidden: true }
            ],
			onBeforeLoadData: function () {
				let viewframe = this,
					isIfa = win.isFormIfa();
				viewframe.setParam('formMode', win.formMode);
				viewframe.setParam('MethodsIFA_id',isIfa ? win.formActions.methodsIFA.getValue() : null);
				viewframe.setParam('AnalyzerTest_id',isIfa ? win.formActions.analyzerTestIFA.getValue() : null);
			},
            onLoadData: function() {
                this.onRowSelectionChange();
                var store = this.getGrid().getStore();

                store.each(function(rec) {
                    if (!Ext.isEmpty(rec.get('UslugaTest_ResultQualitativeNorms'))) {
                        var resp = Ext.util.JSON.decode(rec.get('UslugaTest_ResultQualitativeNorms'));
                        var UslugaTest_ResultNorm = '';
                        for (var k1 in resp) {
                            if (typeof resp[k1] != 'function') {
                                if (UslugaTest_ResultNorm.length > 0) {
                                    UslugaTest_ResultNorm = UslugaTest_ResultNorm + ', ';
                                }

                                UslugaTest_ResultNorm = UslugaTest_ResultNorm + resp[k1];
                            }
                        }
                        rec.set('UslugaTest_ResultNorm',UslugaTest_ResultNorm);
                        rec.set('UslugaTest_ResultCrit','');
                        rec.set('UslugaTest_ResultLower','');
                        rec.set('UslugaTest_ResultUpper','');
                        rec.set('UslugaTest_ResultLowerCrit','');
                        rec.set('UslugaTest_ResultUpperCrit','');
                        rec.commit();
                    }

                    setTimeout(function() {
                        if (!Ext.isEmpty(rec.get('EvnUslugaPar_pComment')) && Ext.get(win.id + 'EvnLabSample_Comment_text_'+rec.get('UslugaTest_pid'))) {
                            Ext.get(win.id + 'EvnLabSample_Comment_text_'+rec.get('UslugaTest_pid')).update(rec.get('EvnUslugaPar_pComment'));
                            Ext.get(win.id + 'EvnLabSample_Comment_text_'+rec.get('UslugaTest_pid')).setAttribute('title', rec.get('EvnUslugaPar_pComment'));
                        }
                        win.setEvnLabSampleCommentMode(rec.get('UslugaTest_pid'));
                    }, 100);
                });

                // можно сделать динамическую высоту окна с максимальным ограничением 15-20 записей
                var count = store.getCount();
                if (count > 15) {
                    count = 15;
                }
                if (count < 1) {
                    count = 1;
                }

                var groupcount = this.getGrid().getEl().query(".x-grid-group").length;

                this.setHeight(170+count*22+groupcount*22);
                //Переопределение ширины колонок в относительных единицах
                var percent = this.getGrid().getView().cm.totalWidth / 100;
                // this.getGrid().getView().cm.setColumnWidth(0, percent*1); // на малых разрешениях скрывается
                this.getGrid().getView().cm.setColumnWidth(0, 26);
                this.getGrid().getView().cm.setColumnWidth(5, percent*4);
                this.getGrid().getView().cm.setColumnWidth(6, percent*4);
                this.getGrid().getView().cm.setColumnWidth(7, percent*23);
                this.getGrid().getView().cm.setColumnWidth(8, percent*4);
                this.getGrid().getView().cm.setColumnWidth(11, percent*9);
                this.getGrid().getView().cm.setColumnWidth(12, percent*9);
                this.getGrid().getView().cm.setColumnWidth(21, percent*9);
                this.getGrid().getView().cm.setColumnWidth(22, percent*9);
            },
            onRowSelectionChange: function() {
                // кнопка одобрить доступна если есть хоть одна в статусе Выполнен
                var approveFlag = true;
                // кнопка снять одобрение доступна если есть хоть одна в статусе Одобрен
                var unapproveFlag = true;
                // кнопка отменить недоступна если есть хоть одна в статусе не Назначен
                var cancelFlag = false;
                // кнопка назначить недоступна если есть хоть одна в статусе не Не назначен
				var prescrFlag = false;
				var historyFlag = true;

                var records = this.getGrid().getSelectionModel().getSelections();
                for (var i = 0; i < records.length; i++) {
                    if (records[i].get('UslugaTest_Status') == langs('Выполнен')) {
                        approveFlag  = false;
                    }
                    if (records[i].get('UslugaTest_Status') == langs('Одобрен')) {
                        unapproveFlag  = false;
                        historyFlag = false;
                    }
                    if (records[i].get('UslugaTest_Status') != langs('Назначен')) {
                        cancelFlag  = true;
                    }
                    if (records[i].get('UslugaTest_Status') != langs('Не назначен')) {
                        prescrFlag  = true;
                    }
                }

                this.setActionDisabled('action_approveone', approveFlag);
                this.setActionDisabled('action_unapproveone', unapproveFlag);
                this.setActionDisabled('action_prescr', prescrFlag);
				this.setActionDisabled('action_cancel', cancelFlag);
				this.setActionDisabled('action_history', historyFlag);
            },
            onRowSelect: function(sm,rowIdx,record)
            {
                this.onRowSelectionChange();
            },
            onRowDeSelect: function(sm,rowIdx,record) {
                this.onRowSelectionChange();
            },
            onRenderGrid: function() {
                if (!EvnUslugaDataGrid.getAction('action_labsampleparams') ) {
                    EvnUslugaDataGrid.addActions({
                        name:'action_labsampleparams',
                        cls: 'newInGridButton',
                        iconCls: 'labSampleParams16',
                        text: langs('Параметры пробы'),
                        tooltip: langs('Параметры пробы'),
                        handler: function() {
                            win.openEvnLabSampleEditWindow('edit', rec.get('EvnLabSample_id'));
                        }.createDelegate(this)
                    });
                }

                if (!EvnUslugaDataGrid.getAction('action_findinlabreqs') ) {
                    EvnUslugaDataGrid.addActions({
                        name:'action_findinlabreq',
                        iconCls: 'findInLabReq16',
                        cls: 'newInGridButton',
                        text: langs('Найти в списке заявок'),
                        tooltip: langs('Найти в списке заявок'),
                        handler: function() {
                            // открыть грид заявок и найти заявку
                            win.setLabMode(0, rec.get('EvnLabRequest_id'));
                            win.LabRequestTabPanel.setActiveTab(0);
                            Ext.getCmp(win.id + 'modeLabRequest').toggle(true);
                        }.createDelegate(this)
                    });
                }

                if (!EvnUslugaDataGrid.getAction('action_printresults') ) {
                    EvnUslugaDataGrid.addActions({
                        name:'action_printresults',
                        cls: 'newInGridButton',
                        iconCls: 'printResults16',
                        text: langs('Печатать результаты'),
                        tooltip: langs('Печатать результаты'),
                        handler: function() {
                            EvnUslugaDataGrid.printRecords();
                        }.createDelegate(this)
                    });
                }

                if (!EvnUslugaDataGrid.getAction('action_cancel') ) {
                    EvnUslugaDataGrid.addActions({
                        name:'action_cancel',
                        iconCls: 'archive16',
                        cls: 'newInGridButton',
                        text: langs('Отменить'),
                        tooltip: langs('Отменить тест'),
                        handler: function() {
                            var params = {};
                            params.EvnLabSample_id = rec.get('EvnLabSample_id');
                            params.EvnLabRequest_id = rec.get('EvnLabRequest_id');
                            var records = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelections();
                            var tests = [];
                            for (var i = 0; i < records.length; i++) {
                                if (!Ext.isEmpty(records[i].get('UslugaComplex_id'))) {
                                    tests.push({
                                        UslugaTest_pid: records[i].get('UslugaTest_pid').toString(),
                                        UslugaComplex_id: records[i].get('UslugaComplex_id').toString()
                                    });
                                }
                            }

                            if (!Ext.isEmpty(tests) && tests.length > 0) {
                                params.tests = Ext.util.JSON.encode(tests);

                                sw.swMsg.show({
                                    icon: Ext.MessageBox.QUESTION,
                                    msg: langs('Выбранные тесты будут удалены. Вы действительно хотите их отменить?'),
                                    title: langs('Вопрос'),
                                    buttons: Ext.Msg.YESNO,
                                    fn: function(buttonId, text, obj) {
                                        if ('yes' == buttonId) {
                                            win.showLoadMask(langs('Отмена теста'));
                                            Ext.Ajax.request({
                                                url: '/?c=EvnLabSample&m=cancelTest',
                                                params: params,
                                                failure: function(response, options) {
                                                    win.hideLoadMask();
                                                },
                                                success: function(response, action) {
                                                    win.hideLoadMask();
                                                    EvnUslugaDataGrid.getGrid().getStore().reload();
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });
                }

                if (!EvnUslugaDataGrid.getAction('action_prescr') ) {
                    EvnUslugaDataGrid.addActions({
                        name:'action_prescr',
                        iconCls: 'archive16',
                        cls: 'newInGridButton',
                        text: langs('Назначить'),
                        tooltip: langs('Назначить тест'),
                        handler: function() {
                            var params = {};
                            params.EvnLabSample_id = rec.get('EvnLabSample_id');
                            params.EvnLabRequest_id = rec.get('EvnLabRequest_id');
                            var records = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelections();
                            var tests = [];
                            for (var i = 0; i < records.length; i++) {
                                if (!Ext.isEmpty(records[i].get('UslugaComplex_id'))) {
                                    tests.push({
                                        UslugaTest_pid: records[i].get('UslugaTest_pid').toString(),
                                        UslugaComplex_id: records[i].get('UslugaComplex_id').toString()
                                    });
                                }
                            }

                            if (!Ext.isEmpty(tests) && tests.length > 0) {
                                params.tests = Ext.util.JSON.encode(tests);

                                win.showLoadMask(langs('Назначение теста'));
                                Ext.Ajax.request({
                                    url: '/?c=EvnLabSample&m=prescrTest',
                                    params: params,
                                    failure: function(response, options) {
                                        win.hideLoadMask();
                                    },
                                    success: function(response, action) {
                                        win.hideLoadMask();
                                        EvnUslugaDataGrid.getGrid().getStore().reload();
                                    }
                                });
                            }
                        }
                    });
                }

                if (!EvnUslugaDataGrid.getAction('action_unapproveone') ) {
                    EvnUslugaDataGrid.addActions({
                        name:'action_unapproveone',
                        iconCls: 'archive16',
                        cls: 'newInGridButton',
                        text: langs('Снять одобрение'),
                        tooltip: langs('Снять одобрение результата'),
                        handler: function() {
							if ( !win.approveIsAllowed() ) return;

                            var params = {};
                            params.EvnLabSample_id = rec.get('EvnLabSample_id');

                            var records = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelections();
                            var UslugaTest_ids = [];
                            for (var i = 0; i < records.length; i++) {
                                if (!Ext.isEmpty(records[i].get('UslugaTest_id')) && !Ext.isEmpty(records[i].get('UslugaTest_ResultValue'))) {
                                    UslugaTest_ids = UslugaTest_ids.concat(records[i].get('UslugaTest_id').toString());
                                }
                            }

                            if (!Ext.isEmpty(UslugaTest_ids) && UslugaTest_ids.length > 0) {
                                params.UslugaTest_ids = Ext.util.JSON.encode(UslugaTest_ids);

                                win.showLoadMask(langs('Снятие одобрения результатов'));
                                Ext.Ajax.request({
                                    url: '/?c=EvnLabSample&m=unapproveResults',
                                    params: params,
                                    failure: function(response, options) {
                                        win.hideLoadMask();
                                    },
                                    success: function(response, action) {
                                        win.hideLoadMask();
                                        EvnUslugaDataGrid.getGrid().getStore().reload();
										win.LabSampleGridPanel.getGrid().getStore().reload();
                                    }
                                });
                            }
                        }
                    });
                }
				if (!EvnUslugaDataGrid.getAction('action_history') ) {
					EvnUslugaDataGrid.addActions({
						name:'action_history',
						iconCls: 'archive16',
						cls: 'newInGridButton',
                        text: langs('История исследований'),
                        tooltip: langs('Показать историю исследований'),
						handler: function() {
							var selections = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelections();
							var codeList = [];
							var EvnLabSample_id = "";
							for (var i = 0; i < selections.length; i++) {
								codeList.push("'" + selections[i].get('UslugaComplex_id') + "'");
								EvnLabSample_id = selections[i].json.EvnLabSample_id;
							}
							getWnd('swResearchHistory').show({
								Codes: codeList.join(','),
								EvnLabSample_id: EvnLabSample_id
                            });
						}.createDelegate(this)
					});
				}
                if (!EvnUslugaDataGrid.getAction('action_approveone') ) {
                    EvnUslugaDataGrid.addActions({
                        name:'action_approveone',
                        iconCls: 'archive16',
                        cls: 'newInGridButton',
                        text: langs('Одобрить'),
                        tooltip: langs('Одобрить результат'),
                        handler: function() {
							if ( !win.approveIsAllowed() ) return;

                            var params = {};
                            params.EvnLabSample_id = rec.get('EvnLabSample_id');

                            var records = EvnUslugaDataGrid.getGrid().getSelectionModel().getSelections();
                            var UslugaTest_ids = [];
                            for (var i = 0; i < records.length; i++) {
                                if (!Ext.isEmpty(records[i].get('UslugaTest_id')) && !Ext.isEmpty(records[i].get('UslugaTest_ResultValue'))) {
                                    UslugaTest_ids = UslugaTest_ids.concat(records[i].get('UslugaTest_id').toString());
                                }
                            }

                            if (!Ext.isEmpty(UslugaTest_ids) && UslugaTest_ids.length > 0) {
                                params.UslugaTest_ids = Ext.util.JSON.encode(UslugaTest_ids);

                                win.showLoadMask(langs('Одобрение результатов'));
                                Ext.Ajax.request({
                                    url: '/?c=EvnLabSample&m=approveResults',
                                    params: params,
                                    failure: function(response, options) {
                                        win.hideLoadMask();
                                    },
                                    success: function(response, action) {
                                        win.hideLoadMask();
                                        EvnUslugaDataGrid.getGrid().getStore().reload();
										win.LabSampleGridPanel.getGrid().getStore().reload();
                                    }
                                });
                            }
                        }
                    });
                }

                EvnUslugaDataGrid.setActionHidden('action_approveone', win.MedServiceType_SysNick == 'pzm');
                EvnUslugaDataGrid.setActionHidden('action_unapproveone', win.MedServiceType_SysNick == 'pzm');
				EvnUslugaDataGrid.setColumnHidden('UslugaTest_CheckDT', true);
            }
        });
        EvnUslugaDataGrid.getGrid().getView().getRowClass = function (row, index) {
            var cls = '';
            if (row.get('UslugaTest_Status') == langs('Не назначен')) {
                cls = cls+'x-grid-rowgray ';
            }
            return cls;
        };
        var params = {
            EvnLabSample_id: rec.get('EvnLabSample_id')
        };

        EvnUslugaDataGrid.loadData({
            params: params,
            globalFilters: params,
            callback: function() {
                // пометить все записи галочками :)
                EvnUslugaDataGrid.getGrid().getSelectionModel().selectAll();
            }
        });

        EvnUslugaDataGrid.getGrid().getColumnModel().isCellEditable = function(colIndex, rowIndex) {
            if (this.config[colIndex].editable || (typeof this.config[colIndex].editable == "undefined" && this.config[colIndex].editor)) {
                var grid = EvnUslugaDataGrid.getGrid();
                var store = grid.getStore();

                if (win.MedServiceType_SysNick.inlist(['pzm'])) {
                    return false;
                }

                if (Ext.isEmpty(store.baseParams.EvnLabSample_id)) {
                    return false;
                }

                var record = store.getAt(rowIndex);
                if (!record || Ext.isEmpty(record.get('UslugaTest_id'))) {
                    return false;
                }

                return true;
            }

            return false;
        };

        this.EvnUslugaDataGrid = EvnUslugaDataGrid;
        element:EvnUslugaDataGrid.render(element)
    },
	getSelectedTests: function() {
		let win = this,
			expandedRows = [],
			EvnUslugaDataGridRows = [],
			expanderState = win.LabSampleGridExpander.state,
			getRecord = function(rowId) {
				let rec = win.LabSampleGridPanel.getGrid().getStore().getById(rowId);
				if(!rec) return;

				let grid = Ext.getCmp('swEvnUslugaDataGrid_' + rec.get('EvnLabSample_id'));
				if(!grid) return false;

				EvnUslugaDataGridRows = EvnUslugaDataGridRows.concat(grid.getMultiSelections());
			};

		for (let rowId in expanderState) {
			expanderState[rowId] ? expandedRows.push(rowId) : null;
		}

		Ext.each(expandedRows, getRecord);
		return EvnUslugaDataGridRows;
	},
	addSelectedTestsToTablet: function (Tablet_id, panel) {
		let win = this,
			EvnUslugaDataGridRecs = win.getSelectedTests(),
			UslugaTest_ids = [];

		Ext.each(EvnUslugaDataGridRecs, function(rec) {
			let UslugaTest_id = rec.get('UslugaTest_id');
			if(!UslugaTest_id) return;
			UslugaTest_ids.push(UslugaTest_id);
		});

		if(!UslugaTest_ids.length) {
			sw.swMsg.alert('Сообщение', 'Выберите тесты');
			return;
		}

		ajaxRequest({
			url: '?c=Hole&m=addUslugaTests',
			params: {
				Tablet_id: Tablet_id,
				UslugaTest_ids: Ext.util.JSON.encode(UslugaTest_ids)
			},
			maskEl: panel.getEl(),
			maskText: langs('Добавление тестов'),
			onSuccess: function() {
				win.tabletGrid.Tablet_id = Tablet_id;
				win.tabletGrid.loadData();
			}
		});
		return UslugaTest_ids;
	},
	drawTabletPanel: function(data, rec, el) {
		let win = this,
			horizSize = rec.get('Tablet_HorizSize'),
			vertSize = rec.get('Tablet_VertSize'),
			tablet_id = rec.get('Tablet_id'),
			isHorizFill = rec.get('Tablet_IsHorizFill') === 2,
			isDoublesFill = rec.get('Tablet_IsDoublesFill') === 2,
			countTestsObj = {},
			startCharCode = "A".charCodeAt(0),
			tabletPanel = new Ext.Panel({
				id: win.id + '_TabletId_' + tablet_id,
				border: false,
				items: []
			});

		for (let i = 0; i <= vertSize; i++) {
			let panel = new Ext.Panel({
				layout: 'column',
				border: false,
				buttonAlign: 'left',
				items: []
			});

			tabletPanel.add(panel);

			if(i===0) {
				for(let j = 1; j <= horizSize; ++j) {
					let label = new Ext.form.Label({
						style: j==1 ? 'margin-left:41px;' : '',
						cls: 'tabletHoleLabel',
						html: String.fromCharCode(startCharCode++)
					});
					panel.add(label);
				}
			} else {

				let cls = '';
				let temp = new Ext.Template("<div class='tabletHole'><div class='tabletHoleChild'></div></div>");

				for(let j = 0; j <= horizSize; ++j) {
					let comp;
					let holeNum = isHorizFill ? (i - 1) * horizSize + j : (j-1) * vertSize + i;
					let holeObj = data[holeNum-1];

					if(j!==0) {
						//номер лунки
						switch (holeObj.HoleState_id) {
							case '1':
								//Пустая лунка
								cls = 'tabletHoleGray';
								break;
							case '2':
								//Пустая контрольная лунка
								cls = 'tabletHoleLightBlue';
								break;
							case '3':
								//Лунка с контрольным материалом
								cls = 'tabletHoleBrown';
								break;
							case '4':
								//Лунка с тестом

								//нет результатов исследования
								if(!holeObj.UslugaTest_ResultValue) {
									cls = 'tabletHoleLightYellow';
									break;
								}

								//нет реф значений
								if(!holeObj.RefValues_id) {
									cls = 'tabletHoleYellow';
									break;
								}

								//если выходит за пределы
								if(holeObj.UslugaTest_ResultValue < holeObj.RefValues_LowerLimit
								|| holeObj.UslugaTest_ResultValue > holeObj.RefValues_UpperLimit) {
									cls = 'tabletHoleRed';
								} else {
									//если входит в пределы реф значений
									cls = 'tabletHoleGreen';
								}
								break;
							case '5':
								//Брак
								cls = 'tabletHoleDarkGray';
								break;
							case '6':
								//Лунка недоступна
								cls = 'tabletHoleBlack';
								break;
							case '7':
								cls = 'tabletHoleBlue';
								break;
						}

						let toolTipMsg = 'Номер лунки: '+ holeObj.Hole_Number + '<br>';
						toolTipMsg += 'Статус: '+ holeObj.HoleState_Name + '<br>';
						if(holeObj.UslugaTest_id) {
							toolTipMsg += 'Номер пробы:' + holeObj.EvnLabSample_BarCode + '<br>';
							toolTipMsg += 'ФИО: ' + holeObj.Person_ShortFio + '<br>';
							toolTipMsg += 'Тест: ' + holeObj.UslugaComplex_Name + '<br>';
						}

						if(isDoublesFill && holeObj.UslugaTest_id) {
							if (countTestsObj[holeObj.UslugaTest_id]) {
								countTestsObj[holeObj.UslugaTest_id]++;
								cls += ' tabletHoleDouble';
							} else {
								countTestsObj[holeObj.UslugaTest_id] = 1;
							}
						}

						if(holeObj.Hole_IsDefect) {
							toolTipMsg += 'Дата отбраковки: ' + holeObj.Hole_defectDT + '<br>';
							toolTipMsg += 'Причина брака: ' + holeObj.DefectCauseType_Name + '<br>';
							toolTipMsg += 'Комментарий: ' + holeObj.Hole_Comment + '<br>';
						}

						comp = new Ext.Button({
							// readOnly: true,
							cls: cls,
							name: i,
							height: 50,
							width: 50,
							menuClassTarget: 'div',
							buttonSelector: 'div:first-child',
							template: temp,
							tooltip: toolTipMsg,
							menu: new Ext.menu.Menu({
								items: [
									{
										text: langs('Найти пробу'),
										handler: function () {
											let barcodeFilter = win.LabSampleGrid_BarcodeFilter;
											barcodeFilter.setValue(holeObj.EvnLabSample_BarCode);
											win.doSearch(win.mode);
										}
									},
									{
										text:  langs('Бланк'),
										handler: function () {
											win.setEmptyControlHole(tablet_id, holeObj.Hole_id, tabletPanel);
										}
									},
									{
										text:  langs('Контрольная лунка'),
										handler: function () {
											win.createControlHole(tablet_id, tabletPanel);
										}
									},
									{
										text: langs('Калибратор'),
										handler: function() {
											win.setCalibratorHole(tablet_id, holeObj.Hole_id, tabletPanel);
										}
									},
									{
										text:  langs('Очистить лунку'),
										handler: function () {
											win.clearHole(tablet_id, holeObj.Hole_id, tabletPanel);
										}
									},
									{
										text:  langs('Брак лунки'),
										handler: function () {
											win.defectHole(tablet_id, holeObj.Hole_id, 'Hole');
										}
									},
									{
										text:  langs('Добавить выбранный тест'),
										handler: function () {
											win.addSelectedTestsToTablet(tablet_id, tabletPanel);
										}
									}
								]
							}),
							listeners: {
								click: function (e) {
								}
							},
							handler: function () {
							}
						});
					} else {
						comp = new Ext.form.Label({
							cls: 'tabletHoleLabel',
							html: (i)
						});
					}
					panel.add(comp);
				}
			}
		}
		tabletPanel.render(el);
	},
	getTabletPanel: function(rec, el) {

		if(!rec) return;

		let win = this;

		Ext.Ajax.request({
			url: '?c=Hole&m=loadGrid',
			params: {
				Tablet_id: rec.get('Tablet_id')
			},
			success: function(response) {
				if(!response.responseText) {
					sw.swMsg.alert('Ошибка', 'При загрузке произошла ошибка');
					return;
				}
				let data = Ext.util.JSON.decode(response.responseText);
				if(!data.length) {
					sw.swMsg.alert('Ошибка', 'При загрузке произошла ошибка');
					return;
				}
				win.drawTabletPanel(data, rec, el);
			}
		});
	},
	setEmptyControlHole: function(Tablet_id, Hole_id, panel) {
		let win = this;
		ajaxRequest({
			url: '?c=Hole&m=setEmptyControlHole',
			params: {
				Hole_id: Hole_id
			},
			maskEl: panel.getEl(),
			maskText: langs('Создание пустой контрольной лунки'),
			onSuccess: function() {
				win.tabletGrid.Tablet_id = Tablet_id;
				win.tabletGrid.loadData();
			}
		});
	},
	clearHole: function(Tablet_id, Hole_id, panel) {
		let win = this;
		let callbackFn = function(btn) {
			if(btn === 'yes') {
				ajaxRequest({
					url: '?c=Hole&m=clearHole',
					params: {
						Hole_id: Hole_id
					},
					maskEl: panel.getEl(),
					maskText: langs('Очистка лунки'),
					onSuccess: function() {
						win.tabletGrid.Tablet_id = Tablet_id;
						win.tabletGrid.loadData();
					}
				});
			}
		};
		sw.swMsg.confirm('Сообщение', 'Отчистить выбранную лунку?', callbackFn)
	},
	defectHole: function(Tablet_id, Hole_id, object) {
		let win = this,
			isHole = object === "Hole",
			defectHoleWin = getWnd('swTabletDefectWindow'),
			params = {
				_id: isHole ? Hole_id : Tablet_id,
				dbObject: object,
				callback: function () {
						win.tabletGrid.Tablet_id = Tablet_id;
						win.tabletGrid.loadData();
				}
			};
		defectHoleWin.show(params);
	},
	createControlHole: function(Tablet_id, panel) {
		let win = this;
		ajaxRequest({
			url: '?c=Hole&m=createControlHole',
			params: {
				Tablet_id: Tablet_id
			},
			maskEl: panel.getEl(),
			maskText: langs('Создание контрольной лунки'),
			onSuccess: function() {
				win.tabletGrid.Tablet_id = Tablet_id;
				win.tabletGrid.loadData();
			}
		});
	},
	setCalibratorHole: function(Tablet_id, Hole_id, panel) {
		let win = this;
		ajaxRequest({
			url: '?c=Hole&m=setCalibratorHole',
			params: {
				Hole_id: Hole_id
			},
			maskEl: panel.getEl(),
			maskText: langs('Установка калибровочной лунки'),
			onSuccess: function() {
				win.tabletGrid.Tablet_id = Tablet_id;
				win.tabletGrid.loadData();
			}
		});
	},
    stepDay: function(day)
    {
        var frm = this;
        frm.visiblePeriod(true);
        var date1 = (frm.dateMenu.getValue1() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
        var date2 = (frm.dateMenu.getValue2() || Date.parseDate(frm.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
        frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
        // frm.dateMenu.fireEvent("select", frm.dateMenu);
    },
    prevDay: function ()
    {
        this.stepDay(-1);
    },
    nextDay: function ()
    {
        this.stepDay(1);
    },
    currentDay: function ()
    {
        var frm = this;
        frm.visiblePeriod(true);
        var date1 = Date.parseDate(frm.curDate, 'd.m.Y');
        var date2 = Date.parseDate(frm.curDate, 'd.m.Y');
        frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
        frm.dateMenu.mode = 'oneday';
        // frm.dateMenu.fireEvent("select", frm.dateMenu);
    },
    currentWeek: function ()
    {
        var frm = this;
        frm.visiblePeriod(true);
        var date1 = (Date.parseDate(frm.curDate, 'd.m.Y'));
        var dayOfWeek = (date1.getDay() + 6) % 7;
        date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
        var date2 = date1.add(Date.DAY, 6).clearTime();
        frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
        frm.dateMenu.mode = 'twodays';
        // frm.dateMenu.fireEvent("select", frm.dateMenu);
    },
    currentMonth: function ()
    {
        var frm = this;
        frm.visiblePeriod(true);
        var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
        var date2 = date1.getLastDateOfMonth();
        frm.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
        frm.dateMenu.mode = 'twodays';
        // frm.dateMenu.fireEvent("select", frm.dateMenu);
    },
    savePeriod: function () {
        if (!Ext.isEmpty(this.dateMenu.getValue1())) {
            this.dateMenu.saveValue1 = this.dateMenu.getValue1();
        }
        if (!Ext.isEmpty(this.dateMenu.getValue2())) {
            this.dateMenu.saveValue2 = this.dateMenu.getValue2();
        }
    },
    restorePeriod: function () {
        if (this.dateMenu.saveValue1 && this.dateMenu.saveValue2) {
            this.dateMenu.setValue(Ext.util.Format.date(this.dateMenu.saveValue1, 'd.m.Y')+' - '+Ext.util.Format.date(this.dateMenu.saveValue2, 'd.m.Y'));
        }
    },
    visiblePeriod: function(visibled) {
        if (visibled) {
            this.dateMenu.show();
            this.labelNoFilter.hide();
        } else {
            this.dateMenu.hide();
            this.labelNoFilter.show();
        }
    },
    labMode: 0,
    setLabMode: function(mode, findEvnLabRequest_id) {
        this.labMode = mode;
        this.visiblePeriod(true);
        this.WorkPanel.getLayout().setActiveItem(mode);
        this.doSearch(null, findEvnLabRequest_id);
		this.focusOnGrid();
    },
    showInputBarCodeField: function(inputPlace, EvnLabSample_id, element) {
        var win = this;
        var oldBarCode = element.innerHTML;
        Ext.get(inputPlace).setDisplayed('none');
        Ext.get(inputPlace + '_inp').setDisplayed('block');

        var cmp = new Ext.form.TextField({
            hideLabel: true
            ,renderTo: inputPlace + '_inp'
            ,width: 100
            ,listeners:
            {
                blur: function(f) {
                    Ext.get(inputPlace).setDisplayed('block');
                    Ext.get(inputPlace + '_inp').setDisplayed('none');
                    f.destroy();
                    win.barCodeIsFocused = false;
                },
                render: function(f) {
                    f.setValue(oldBarCode);
                    f.focus(true);
                    win.barCodeIsFocused = true;
                },
                change: function(f,n,o) {
                    if (!Ext.isEmpty(n) && n != oldBarCode) {
                        // проверить на уникальность и обновить в БД
                        win.getLoadMask(langs('Сохранение штрих-кода')).show();
                        Ext.Ajax.request({
                            url: '/?c=EvnLabSample&m=saveNewEvnLabSampleBarCode',
                            params: {
                                EvnLabSample_id: EvnLabSample_id,
                                EvnLabSample_BarCode: n
                            },
                            callback: function(opt, success, response) {
                                win.getLoadMask().hide();
                                if (success && response.responseText != '') {
                                    var result = Ext.util.JSON.decode(response.responseText);
                                    if (result.success) {
                                        element.innerHTML = n;
                                        var num = n.substr(-4);
                                        // если сохранился штрих-код, предлагаем менять номер пробы
                                        Ext.Msg.show({
                                            title: 'Внимание',
                                            msg: 'Штрих код изменен на №'+ n +'. Изменить номер пробы на №'+num+'?',
                                            buttons: Ext.Msg.YESNO,
                                            fn: function(btn) {
                                                if (btn === 'yes') {
                                                    win.getLoadMask("Сохранение номера пробы...").show();
                                                    Ext.Ajax.request({
                                                        params: {
                                                            EvnLabSample_id: EvnLabSample_id,
                                                            EvnLabSample_ShortNum: num
                                                        },
                                                        url: '/?c=EvnLabSample&m=saveNewEvnLabSampleNum',
                                                        callback: function(options, success, response) {
                                                            win.getLoadMask().hide();
                                                            if(success) {
                                                                var grid = win.LabSampleGridPanel.getGrid();
                                                                var record = grid.getStore().getById(EvnLabSample_id);

                                                                if (record) {
                                                                    record.set('EvnLabSample_BarCode', n);
                                                                    record.set('EvnLabSample_ShortNum', num);
                                                                    record.commit();
                                                                }
                                                            }
                                                        }
                                                    });
                                                }
                                            },
                                            icon: Ext.MessageBox.QUESTION
                                        });
                                    }
                                }
                            }
                        });
                    }
                }
            }
        });

        // cmp.focus(true, 500);
    },
    openEvnLabSampleEditWindow: function(action, EvnLabSample_id) {
        var win = this;
        var g = win.LabSampleGridPanel.getGrid();
        var selection = g.getSelectionModel().getSelected();
        if (!Ext.isEmpty(EvnLabSample_id)) {
            var index = g.getStore().findBy(function(rec) {
                return (rec.get('EvnLabSample_id') == EvnLabSample_id);
            });
            selection = g.getStore().getAt(index);
        }
        // если уж загрузка до показа формы, то надо хотя бы показать, что что то делается.
        if (selection) {
            win.getLoadMask(langs('Загрузка данных пробы...')).show();
            Ext.Ajax.request({
                url: '/?c=EvnLabSample&m=load',
                params:{
                    EvnLabSample_id: selection.data.EvnLabSample_id
                },
                callback: function(opt, success, response) {
                    win.getLoadMask().hide();
                    if (success && response.responseText != '') {
                        var result = Ext.util.JSON.decode(response.responseText);
                        var params = new Object();
                        params.action = action;
                        params.remoteCallback = function() {
                            win.getLoadMask().hide();
                            win.doSearch('day');
                        };
                        params.formParams = new Object();
                        params.formParams = result[0];
                        params.formParams.EvnLabSample_ShortNum = params.formParams.EvnLabSample_Num.substr(-4);
                        params.onHide = function() {
                            g.getView().focusRow(g.getStore().indexOf(selection));
                        };

                        params.Person_id = params.formParams.Person_id;
                        params.MedService_id = win.MedService_id;
                        params.EvnDirection_id = selection.data.EvnDirection_id;
                        params.UslugaComplexTarget_id = selection.data.UslugaComplexTarget_id;

                        getWnd('swLabSampleEditWindow').show(params);
                    }
                }
            });
        }
    },
    createFormActions: function() {

        var win = this;

        this.dateMenu = new Ext.form.DateRangeFieldAdvanced({
            width: 150,
            showApply: false,
            id:'dateRangeLis',
            fieldLabel: langs('Период'),
            plugins:
            [
                new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
            ]
        });
        this.labelNoFilter = new Ext.form.Label({
            id:'labelLisNoFilter',
            html: '<div style="width:138px;text-align:center;" class="x-form-text x-form-field"><a href=# onclick="Ext.getCmp(\''+this.id+'\').visiblePeriod(true);");">фильтр отключен</a>&nbsp;&nbsp;&nbsp;</div>',
            xtype: 'label',
            hidden: true
        });

        this.dateMenu.addListener('keydown',function (inp, e) {
            if (e.getKey() == Ext.EventObject.ENTER) {
                e.stopEvent();
                this.doSearch('range');
            }
        }.createDelegate(this));
        this.dateMenu.addListener('select',function () {
            // Читаем расписание за период
            this.doSearch('range');
        }.createDelegate(this));

        this.formActions = new Array();
        this.formActions.modeLabRequest = new Ext.Action(
        {
            text: langs('Заявки'),
            style: "",
            minWidth: 150,
            ctCls: 'newButton',
            xtype: 'button',
            id: win.id + 'modeLabRequest',
            toggleGroup: 'labModeToggle',
            iconCls: '',
            pressed: true,
            handler: function()
            {
                win.setLabMode(0);
                this.toggle(true);
            }
        });
        this.formActions.modeLabSample = new Ext.Action(
        {
            text: langs('Пробы'),
            style: "",
            ctCls: 'newButton',
            minWidth: 150,
            xtype: 'button',
            id: win.id + 'modeLabSample',
            toggleGroup: 'labModeToggle',
            iconCls: '',
            handler: function()
            {
				win.setLabMode(1);
                this.toggle(true);
            }
        });
        this.formActions.prev = new Ext.Action(
        {
            text: '',
            id:'prevArrowLis',
            xtype: 'button',
            iconCls: 'arrow-previous16',
            handler: function()
            {
                // на один день назад
                this.prevDay();
                this.doSearch(this.mode);
            }.createDelegate(this)
        });
        this.formActions.next = new Ext.Action(
        {
            text: '',
            id:'nextArrowList',
            xtype: 'button',
            iconCls: 'arrow-next16',
            handler: function()
            {
                // на один день вперед
                this.nextDay();
                this.doSearch(this.mode);
            }.createDelegate(this)
        });
        this.formActions.day = new Ext.Action(
        {
            text: langs('День'),
            id:'dayLis',
            xtype: 'button',
            toggleGroup: 'periodToggle',
            pressed: true,
            handler: function()
            {
                this.currentDay();
                this.doSearch('day');
				//АИГ - 29.07.2019 - printListPersons
				this.GridPanel.ViewActions.action_print.menu['action_print_ListPersons'] .setDisabled(false);
            }.createDelegate(this)
        });
        this.formActions.week = new Ext.Action(
        {
            text: langs('Неделя'),
            id:'weekLis',
            xtype: 'button',
            toggleGroup: 'periodToggle',
            handler: function()
            {
                this.currentWeek();
                this.doSearch('week');
				//АИГ - 29.07.2019 - printListPersons
				this.GridPanel.ViewActions.action_print.menu['action_print_ListPersons'] .setDisabled(true);
            }.createDelegate(this)
        });
        this.formActions.month = new Ext.Action(
        {
            id:'monthLis',
            text: langs('Месяц'),
            xtype: 'button',
            toggleGroup: 'periodToggle',
            handler: function()
            {
                this.currentMonth();
                this.doSearch('month');
				//АИГ - 29.07.2019 - printListPersons
				this.GridPanel.ViewActions.action_print.menu['action_print_ListPersons'] .setDisabled(true);
            }.createDelegate(this)
        });
        this.formActions.range = new Ext.Action(
        {
            text: langs('Период'),
            disabled: true,
            hidden: true,
            xtype: 'button',
            toggleGroup: 'periodToggle',
            handler: function()
            {
                this.doSearch('range');
            }.createDelegate(this)
        });
		this.formActions.methodsIFALabel = new Ext.form.Label({
			xtype: 'label',
			text: 'Методика:',
			style: 'margin-left: 15px;',
			hidden: true
		});
		this.formActions.methodsIFA = new sw.Promed.SwBaseLocalCombo({
			hiddenName: 'MethodsIFA_id',
			valueField: 'MethodsIFA_id',
			displayField: 'MethodsIFA_Name',
			//codeField: 'MethodsIFA_Code',
			hidden: true,
			listWidth: 300,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<div>{MethodsIFA_Name}&nbsp;</div>',
				'</div></tpl>'
			),
			store: new Ext.data.JsonStore({
				url: '?c=MethodsIFA&m=loadFilterCombo',
				fields: [
					{ type: 'int', name: 'MethodsIFA_id' },
					{ type: 'int', name: 'MethodsIFA_Code' },
					{ type: 'string', name: 'MethodsIFA_Name' }
				]
			}),
			listeners: {
				select: function(combo, rec, idx) {
					win.GridPanel.setParam('MethodsIFA_id', combo.getValue());
					win.LabSampleGridPanel.setParam('MethodsIFA_id', combo.getValue());
					win.tabletGrid.setParam('MethodsIFA_id', combo.getValue());
					win.doSearch(win.mode);
					win.tabletGrid.loadData();
				}
			}
		});
		this.formActions.analyzerTestIFA = new sw.Promed.SwBaseLocalCombo({
			valueField: 'AnalyzerTest_id',
			displayField: 'AnalyzerTest_Name',
			//codeField: 'AnalyzerTest_Code',
			listWidth: 300,
			hidden: true,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<div>{AnalyzerTest_Name}&nbsp;</div>',
				'</div></tpl>'
			),
			store: new Ext.data.JsonStore({
				baseParams: { mode: 'ifalab' },
				fields: [
					{ type: 'int', name: 'AnalyzerTest_id' },
					{ type: 'string', name: 'AnalyzerTest_Code' },
					{ type: 'string', name: 'AnalyzerTest_Name' }
				],
				key: 'AnalyzerTest_id',
				url: '/?c=AnalyzerTest&m=loadList'
			}),

			listeners: {
				select: function(combo, rec, idx) {
					win.GridPanel.setParam('AnalyzerTest_id', combo.getValue());
					win.LabSampleGridPanel.setParam('AnalyzerTest_id', combo.getValue());
					win.doSearch(win.mode);
					win.tabletGrid.setParam('AnalyzerTest_id', combo.getValue());
					win.tabletGrid.loadData();
				}
			}
		});
		this.formActions.analyzerTestIFALabel = new Ext.form.Label({
			xtype: 'label',
			text: 'Тест: ',
			style: 'margin-left: 15px;',
			hidden: true
		});

		this.formActions.formLab = new Ext.Action({
			text: langs('Лаборатория'),
			cls: 'leftLisToogleEl',
			xtype: 'button',
			toggleGroup: 'formMode',
			hidden: !isUserGroup('ifalab'),
			pressed: true,
			handler: function () {
				win.setFormMode(false);
			}
		});
		this.formActions.formIfaLab = new Ext.Action({
			text: langs('Лаборатория ИФА'),
			width: 150,
			cls: 'rightLisToogleEl',
			xtype: 'button',
			toggleGroup: 'formMode',
			hidden: !isUserGroup('ifalab'),
			handler: function () {
				win.setFormMode('ifa');
			}
		});
	},
	setVisibleIfaFields: function(visibled) {
		this.LeftPanel.actions.actions_methodsIFA.setHidden(!visibled);
		this.formActions.methodsIFALabel.setVisible(visibled);
		this.formActions.analyzerTestIFALabel.setVisible(visibled);
		this.formActions.methodsIFA.setVisible(visibled);
		this.formActions.analyzerTestIFA.setVisible(visibled);
		this.tabletPanel.setVisible(visibled);
		this.syncSize();
		this.doLayout();
	},
    buttonPanelActions: {},
    loadCompositionMenu: function(callback, rec)
    {
        var win = this;

        if (typeof callback != 'function') {
            return false;
        }
        if (!rec) {
            return false;
        }
        win.getLoadMask(LOAD_WAIT).show();
        Ext.Ajax.request({
            params: {
                EvnDirection_id: rec.get('EvnDirection_id')
            },
            callback: function(options, success, response) {
                win.getLoadMask().hide();
                if ( success ) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (Ext.isArray(response_obj) && response_obj.length > 0) {
                        rec.compositionMenu = new Ext.menu.Menu();
                        rec.compositionMenu.addListener('beforeshow', function(m) {
                            rec.compositionMenu.changed = false;
                            swSetMaxMenuHeight(m, 300);
                        });
                        rec.compositionMenu.addListener('hide', function(m) {
                            rec.isVisibleCompositionMenu = false;
                            if (!rec.compositionMenu.changed) {
                                return false;
                            }

                            var checked = [];

                            m.items.each(function(item){
                                if (item.checked&&'checkAll'!=item.id&&item.setChecked!=undefined) {
                                    checked.push(item.UslugaComplex_id.toString());
                                }
                            });

                            if (checked.length == 0) {
                                sw.swMsg.alert(langs('Ошибка'), langs('Нельзя сохранить пустой состав исследования.'));
                                delete rec.compositionMenu; // удаляем, чтобы заного прогрузилось меню.
                                return false;
                            }

                            win.getLoadMask(langs('Сохранение состава исследования')).show();
                            Ext.Ajax.request({
                                url: '/?c=EvnLabRequest&m=saveEvnLabRequestContent',
                                params: {
                                    EvnDirection_id: rec.get('EvnDirection_id'),
                                    UslugaComplexContent_ids: Ext.util.JSON.encode(checked)
                                },
                                callback: function() {
                                    win.getLoadMask().hide();
                                    win.GridPanel.getGrid().getStore().reload();
                                }
                            });
                        });
                        if(response_obj.length > 1){
                            rec.compositionMenu.add(new Ext.menu.CheckItem({
                                    id: "checkAll",
                                    text: "Выбрать/Снять все",
                                    iconCls: "uslugacomplex-16",
                                    checked: true,
                                    hideOnClick: false,
                                    handler: function(item) {
                                        rec.compositionMenu.changed = true;
                                        rec.compositionMenu.items.each(function(rec){
                                            if(rec.id!=item.id&&rec.setChecked!=undefined){
                                                rec.setChecked(!item.checked)
                                            }
                                        })
                                    }
                                }));
                            rec.compositionMenu.add(new Ext.menu.Separator());
                        }
                       for (var i=0; i < response_obj.length; i++) {
                            rec.compositionMenu.add(new Ext.menu.CheckItem({
                                id: response_obj[i].UslugaComplex_id,
                                text: response_obj[i].UslugaComplex_Code+' '+response_obj[i].UslugaComplex_Name,
                                UslugaComplex_id: response_obj[i].UslugaComplex_id,
                                iconCls: "uslugacomplex-16",
                                rec: rec,
                                checked: (response_obj[i].UslugaComplex_InRequest == 1),
                                hideOnClick: false,
                                handler: function(item) {
                                    rec.compositionMenu.changed = true;
                                }
                            }));
                        }
                        callback(rec.compositionMenu);
                    }
                }
            },
            url: '/?c=EvnLabRequest&m=loadCompositionMenu'
        });
        return true;
    },
    showComposition: function(key){
        var win = this,
            rec = this.GridPanel.getGrid().getStore().getById(key);
        if (!rec) {
            return false;
        }
        if (!rec.compositionMenu) {
            this.loadCompositionMenu(function(menu){
                menu.show(Ext.get('composition_'+ key),'tl-bl?');
                rec.isVisibleCompositionMenu = true;
                win._lastRowKey = key;
            }, rec);
            return true;
        }
        if (win._lastRowKey == key) {
            if (rec.isVisibleCompositionMenu) {
                if (!rec.compositionMenu.hidden) {
                    rec.compositionMenu.hide();
                }
                rec.isVisibleCompositionMenu = false;
                //при повторном клике меню отобразится
                return true;
            }
        } else {
            rec.isVisibleCompositionMenu = false;
        }
        rec.compositionMenu.show(Ext.get('composition_'+ key),'tl-bl?');
        rec.isVisibleCompositionMenu = true;
        win._lastRowKey = key;
        return true;
    },
    openResearchEditWindow: function(button, EvnUslugaPar_id) {
        var win = this;

        getWnd('swResearchEditWindow').show({
            EvnUslugaPar_id: EvnUslugaPar_id,
            callback: function (data) {
                if (data && data.EvnUslugaPar_Comment) {
                    Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).update(data.EvnUslugaPar_Comment);
                    Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).setAttribute('title', data.EvnUslugaPar_Comment);
                    win.setEvnLabSampleCommentMode(EvnUslugaPar_id);
                }
            }
        });
    },
    saveEvnLabSampleComment: function(text, EvnUslugaPar_id, callback) {
        var win = this;
        var oldtext = Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).dom.innerText;

        if(win.els_comment_edit) {
            win.els_comment_edit.remove();
            delete win.els_comment_edit;
        }

        if (oldtext == text) {
            if (callback && typeof callback == 'function') {
                callback();
            }
            return false;
        }

        Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).update(text);
        Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).setAttribute('title', text);
        win.setEvnLabSampleCommentMode(EvnUslugaPar_id);

        var params = {
            EvnUslugaPar_id: EvnUslugaPar_id,
            EvnUslugaPar_Comment: text
        };

        Ext.Ajax.request({
            failure: function () {
                sw.swMsg.alert('Ошибка', 'Не удалось сохранить комментарий');
            },
            params: params,
            success: function (response) {
                var result = Ext.util.JSON.decode(response.responseText);
                if (result && result.success) {
                    if (callback && typeof callback == 'function') {
                        callback();
                    }
                }
            },
            url: '/?c=EvnLabSample&m=saveComment'
        });
    },
    setEvnLabSampleCommentMode: function(EvnUslugaPar_id) {
        var win = this;
        if (!Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id)) {
            return false;
        }
        var text = Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).dom.innerText;
        var block = Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id);
        var icon = Ext.get(win.id + 'EvnLabSample_Comment_icon_'+EvnUslugaPar_id);
        block.setVisibilityMode(Ext.Element.DISPLAY);
        if (Ext.isEmpty(text)) {
            block.hide();
            icon.show();
        } else {
            block.show();
            icon.hide();
        }
    },
    editEvnLabSampleComment: function(button, EvnUslugaPar_id, mode) {

        var win = this;

        if (this.els_comment_edit) {
            win.saveEvnLabSampleComment(Ext.get(win.id + 'EvnLabSample_Comment_input').dom.value, this.els_comment_edit.EvnUslugaPar_id, function () {
                setTimeout(function () {
                    win.editEvnLabSampleComment(button, EvnUslugaPar_id, mode);
                }, 70);
            });
            return false;
        }

        if (mode == 1) { // 1 - текст примечания закрыт, запуск с иконки
            var btn = Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id);
            Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id).show();
            Ext.get(win.id + 'EvnLabSample_Comment_icon_'+EvnUslugaPar_id).hide();
        } else { // 2 - текст примечания открыт, клик по полю
            var btn = Ext.get(button);
        }
        var text = Ext.get(win.id + 'EvnLabSample_Comment_text_'+EvnUslugaPar_id).dom.innerText;

        // никогда так больше не делайте
        // ещё больше костылей
        var v = Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id).parent('.x-grid-group-hd').getTop() - Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id).parent('.x-grid3-body').getTop();
        var x = 22;
        var y = v + 27;
        var w = this.body.getWidth() - 130;

        this.els_comment_edit = Ext.get(win.id + 'EvnLabSample_Comment_block_'+EvnUslugaPar_id).parent('.x-grid-group').createChild({
            html: '<div style="position: absolute; z-index: 9999; left: '+x+'px; top: '+y+'px;">' +
                '<input size="24" autocomplete="off" id="'+win.id+'EvnLabSample_Comment_input" class="x-form-text x-form-field x-form-focus" style="width: '+w+'px;" type="text" value="'+text+'">' +
                '</div>'
        });
        this.els_comment_edit.EvnUslugaPar_id = EvnUslugaPar_id;
        var input = Ext.get(win.id + 'EvnLabSample_Comment_input');
        input.focus(true);
        input.addListener('keydown', function (e, inp) {
            if (e.getKey() == Ext.EventObject.DELETE) {
                if ( e.browserEvent.stopPropagation ) {
                    e.browserEvent.stopPropagation();
                }
            }
            if (e.getKey() == Ext.EventObject.ENTER) {
                e.stopEvent();
                win.saveEvnLabSampleComment(inp.value, EvnUslugaPar_id);
            }
            if (e.getKey() == Ext.EventObject.ESC) {
                e.stopEvent();
                inp.value = text;
                win.els_comment_edit.remove();
                delete win.els_comment_edit;
                win.setEvnLabSampleCommentMode(EvnUslugaPar_id);
            }
        });
        input.addListener('blur', function (e, inp) {
            setTimeout(function () {
                if (win.els_comment_edit) {
                    win.saveEvnLabSampleComment(inp.value, win.els_comment_edit.EvnUslugaPar_id);
                }
            }, 50);
        });
    },
    saveAnalyzerForLabSamples: function(params, item, selections) {
        var win = this;
        if (item && item.id) {
            for (var key in selections) {
                if (selections[key].data) {
                    if (item.id == -1) {
                        selections[key].set('Analyzer_Name', '');
                        selections[key].set('Analyzer_id', null);
                    } else {
                        selections[key].set('Analyzer_Name', item.text);
                        selections[key].set('Analyzer_id', item.id);
                    }
                    selections[key].commit();
                }
            }
            win.getLoadMask(langs('Изменение анализатора')).show();
            // обновить на стороне сервера
            if (item.id == -1) {
                params.Analyzer_id = null;
            } else {
                params.Analyzer_id = item.id;
            }
            Ext.Ajax.request({
                url: '/?c=EvnLabSample&m=saveLabSamplesAnalyzer',
                params: params,
                callback: function(options, success, response) {
                    win.getLoadMask().hide();
                    if(success) {
                        win.LabSampleGridPanel.getGrid().getStore().reload();
                    }
                }
            });
        }
    },
    initComponent: function() {
		var win = this;

        win.panelsWereHidden = false;
        this.filterRowReq = new Ext.ux.grid.FilterRow({
            id:'filterRowReq',
            fixed: true,
            parId:win.id,
            group: true,
            listeners:  {
                'search':function(params){
                    win.doSearch();
                }
            }
        })

        this.filterRowLab = new Ext.ux.grid.FilterRow({
            id:'filterRowLab',
            fixed: true,
            parId:win.id,
            group: true,
            listeners:  {
                'search':function(params){
                    win.doSearch();
                }
            }
        });

        win.gridKeyboardInput = '';
        win.gridKeyboardInputSequence = 1;
        win.on('activate', function() {
            win.focusOnGrid();
        });

        this.buttonPanelActions = {
            action_Timetable:
            {
                nn: 'action_Timetable',
                tooltip: langs('Работа с расписанием'),
                text: langs('Рaсписание'),
                iconCls : 'mp-timetable32',
                disabled: false,
                handler: function()
                {
                    getWnd('swTTMSScheduleEditWindow').show({
                        MedService_id: this.MedService_id,
                        MedService_Name: this.MedService_Name
                    });
                }.createDelegate(this)
            },
            action_AnalyzerWorksheetJournal:
            {
                nn: 'action_AnalyzerWorksheetJournal',
                tooltip: langs('Рабочие списки'),
                text: langs('Рабочие списки'),
                iconCls : 'worksheets32',
                disabled: false,
                handler: function()
                {
                    getWnd('swAnalyzerWorksheetJournalWindow').show({MedService_id: this.MedService_id});
                }.createDelegate(this)
            },
            action_Usluga:
            {
                nn: 'action_Usluga',
                tooltip: langs('sample_and_container_customization'),
                text: langs('sample_and_container_customization'),
                iconCls : 'lab-service32',
                disabled: false,
                handler: function()
                {
                    getWnd('swLabServicesWindow').show({MedService_id: this.MedService_id});
                }.createDelegate(this)
            },
            action_Reactive:
            {
                nn: 'action_Reactive',
                tooltip: langs('Реактивы'),
                text: langs('Реактивы'),
                iconCls : 'doc-notify32',
                disabled: false,
                menuAlign: 'tr?',
                menu: new Ext.menu.Menu({
                    items: [
                        {
                            tooltip: langs('Справочник реактивов'),
                            text: langs('Справочник реактивов'),
                            iconCls : '',
                            handler: function()
                            {
                                getWnd('swDrugNomenSprWindow').show({mode: 'lab', readOnly: false});
                            }
                        },
                        {
                            tooltip: langs('Нормативы расхода'),
                            text: langs('Нормативы расхода'),
                            iconCls : '',
                            handler: function()
                            {
                                getWnd('swNormCostItemViewWindow').show({MedService_id: win.MedService_id});
                            }
                        },
                        {
                            tooltip: langs('Просмотр остатков'),
                            text: langs('Просмотр остатков'),
                            iconCls : '',
                            handler: function()
                            {
                                getWnd('swDrugOstatRegistryListWindow').show({mode: 'suppliers'});
                            }
                        },
                        {
                            tooltip: langs('Учет реактивов'),
                            text: langs('Учет реактивов'),
                            iconCls : '',
                            handler: function()
                            {
                                getWnd('swReagentConsumptionCalculationWindow').show({MedService_id: win.MedService_id});
                            }
                        },
                        {
                            tooltip: langs('Статистика расхода реактивов'),
                            text: langs('Статистика расхода реактивов'),
                            iconCls : '',
                            handler: function()
                            {
                                getWnd('swReagentAutoRateWindow').show({MedService_id: win.MedService_id});
                            }
                        }
                    ]
                })
            },
            /*action_LIS_RJ:
            {
                nn: 'action_LIS_RJ',
                tooltip: langs('Журнал лабораторных исследований'),
                text: langs('Журнал лабораторных исследований'),
                iconCls : 'lis-settings32',
                disabled: false,
                handler: function()
                {
                    getWnd('swRegistrationJournalSearchWindow').show();
                }
            },*/
            action_PZ:
            {
                nn: 'action_PZ',
                tooltip: langs('Пункты забора'),
                text: langs('Пункты забора'),
                iconCls : 'testtubes32',
                disabled: false,
                handler: function()
                {
                    getWnd('swMedServiceLinkManageWindow').show({
                        MedService_lid: win.MedService_id,
                        MedServiceLinkType_id: 1,
                        MedServiceType_SysNick: win.MedServiceType_SysNick,
                        parentARMType: 'lab'
                    });
                }
            },
            action_Podr:
            {
                nn: 'action_Podr',
                tooltip: langs('Подразделения'),
                text: langs('Подразделения'),
                iconCls : 'sections-move32',
                disabled: false,
                handler: function()
                {
                    getWnd('swMedServiceLinkManageWindow').show({
                        MedService_id: win.MedService_id,
                        MedServiceLinkType_id: 2,
                        MedServiceType_SysNick: win.MedServiceType_SysNick,
						parentARMType: 'reglab'
                    });
                }
            },
            /*action_Settings:
            {
                nn: 'action_Settings',
                tooltip: langs('Настройки'),
                text: langs('Настройки'),
                iconCls : 'settings32',
                disabled: false,
                handler: function()
                {
                    getWnd('swLisUserEditWindow').show({MedService_id: win.MedService_id});
                }
            },*/
            action_JourNotice: {
                handler: function() {
                    getWnd('swMessagesViewWindow').show();
                }.createDelegate(this),
                iconCls: 'notice32',
                nn: 'action_JourNotice',
                text: langs('Журнал уведомлений'),
                tooltip: langs('Журнал уведомлений')
            },
            action_AnalyzerControlSeries: {
                handler: function() {
                    getWnd('swAnalyzerControlSeriesListWindow').show({
                        MedService_id: win.MedService_id,
                        MedServiceType_SysNick: win.MedServiceType_SysNick
					});
                }.createDelegate(this),
                iconCls: 'lab32',
                nn: 'action_AnalyzerControlSeries',
                text: langs('Контроль качества'),
                tooltip: langs('Контроль качества')
            },
            action_reports: //http://redmine.swan.perm.ru/issues/18509
            {
                nn: 'action_reports',
                tooltip: langs('Отчеты'),
                text: langs('Отчеты'),
                iconCls: 'report32',
                handler: function() {
                    if (sw.codeInfo.loadEngineReports)
                    {
                        getWnd('swReportEndUserWindow').show();
                    }
                    else
                    {
                        getWnd('reports').load(
                            {
                                callback: function(success)
                                {
                                    sw.codeInfo.loadEngineReports = success;
                                    // здесь можно проверять только успешную загрузку
                                    getWnd('swReportEndUserWindow').show();
                                }
                            });
                    }
                }
            },
			action_Templ: {
				handler: function() {
					var params = {
						XmlType_id: 7,
						allowSelectXmlType: false,
						EvnClass_id: 47
					};
					getWnd('swTemplSearchWindow').show(params);
				},
				iconCls : 'card-state32',
					nn: 'action_Templ',
					text: 'Шаблоны документов',
					tooltip: 'Шаблоны документов'
			},
            action_MSLManage: {
                nn: 'action_MSLManage',
                tooltip: langs('Лаборатории'),
                text: langs('Лаборатории'),
                iconCls : 'testtubes32',
                disabled: false,
                handler: function()
                {
                    getWnd('swMedServiceLinkManageWindow').show({
                        MedService_id: this.MedService_id,
						parentARMType: 'pzm'
                    });
                }.createDelegate(this)
            },
            action_Defect:
            {
                nn: 'action_Defect',
                tooltip: langs('Журнал отбраковки'),
                text: langs('Журнал отбраковки'),
                iconCls : 'lab32',
                disabled: false,
                handler: function()
                {
                    getWnd('swEvnLabSampleDefectViewWindow').show({
                        MedService_id: this.MedService_id,
						MedServiceType_SysNick: this.MedServiceType_SysNick
                    });
                }.createDelegate(this)
            },

            action_DirectionCVI: {
                nn: 'action_DirectionCVI',
                tooltip: 'Журнал направлений во внешние лаборатории по КВИ',
                iconCls : 'mp-directions32',
				hidden: getRegionNick() == 'kz',
                disabled: false, 
                handler: function() {
                    getWnd('swEvnDirectionCVIJournalWindow').show({
                        MedService_id: this.MedService_id,
						MedServiceType_SysNick: this.MedServiceType_SysNick
                    });
                }.createDelegate(this)
            },
			
            action_Form250u:
            {
                nn: 'action_Form250u',
                tooltip: 'Журнал регистрации анализов и их результатов',
                text: 'Журнал регистрации анализов и их результатов',
                iconCls : 'lab-journal32',
                disabled: false,
                handler: function()
                {
                    getWnd('swForm250u').show({
                        MedService_id: this.MedService_id,
                        MedServiceType_SysNick: this.MedServiceType_SysNick
                    });
                }.createDelegate(this)
            },

			action_CanceledRequests:
				{
					nn: 'action_CanceledRequests',
					tooltip: 'Отклонённые заявки',
					text: 'Отклонённые заявки',
					iconCls : 'stop_red16',
					disabled: false,
					handler: function() {
						getWnd('swEvnLabRequestCanceledWindow').show({
							MedService_id: win.MedService_id
						});
					}
				},

            action_AnalyzerQualityControl:
            {
                nn: 'action_AnalyzerQualityControl',
                tooltip: 'Контроль качества',
                text: 'Контроль качества',
                iconCls : 'testtubes32',
                handler: function() {
                    getWnd('swAnalyzerQualityControlWindow').show({
                        MedService_id: win.MedService_id,
                        ARMType: win.userMedStaffFact.ARMType
                    });
                }
            },
            action_EvnUslugaParSearch: {
                nn: 'action_EvnUslugaParSearch',
                text: 'Параклинические услуги: Поиск',
                tooltip: 'Параклинические услуги: Поиск',
                iconCls: 'para-service32',
                handler: function () {
                    getWnd('swEvnUslugaParSearchWindow').show({
                        LpuSection_id: win.userMedStaffFact.LpuSection_id
                    });
                }.createDelegate(this)
            },
            action_PrintBarcodes:
            {
                nn: 'action_PrintBarcodes',
                tooltip: langs('Печать штрих-кодов без привязки к заявке/пробе'),
                text: langs('Печать штрих-кодов'),
                iconCls : 'print16',
                disabled: false,
                handler: function()
                {
                    getWnd('swLabPrintBarcodesForm').show({
                        MedService_id: this.MedService_id
                    });
                }.createDelegate(this)
            },
            action_sendMbu:
            {
                nn: 'action_sendMbu',
                tooltip: langs('Передача результатов в ПАК НИЦ МБУ'),
                text: langs('Передача результатов в ПАК НИЦ МБУ'),
                iconCls : 'database-export32',
                hidden: (getGlobalOptions().region.nick != 'kz'), // todo: Еше надо показывать кнопку только если есть связь у МО в таблице MbuLpu
                disabled: false,
                handler: function()
                {
                    getWnd('swSendMBUViewWindow').show({
                        MedService_id: this.MedService_id
                    });
                }.createDelegate(this)
            },
            action_DirectionJournal: {
                nn: 'action_DirectionJournal',
                text: WND_DIRECTION_JOURNAL,
                tooltip: WND_DIRECTION_JOURNAL,
                iconCls : 'mp-queue32',
                handler: function() {
                    getWnd('swMPQueueWindow').show({
                        ARMType: 'labdiag',
                        callback: function(data) {
                            // this.createTtgAndOpenPersonEPHForm(data);
                            // this.scheduleRefresh();
                        }.createDelegate(this),
                        mode: 'view',
                        userMedStaffFact: this.userMedStaffFact,
                        onSelect: function(data) { // на тот случай если из режима просмотра очереди будет сделана запись
                            getWnd('swMPQueueWindow').hide();
                            getWnd('swMPRecordWindow').hide();
                            // Ext.getCmp('swMPWorkPlaceWindow').scheduleSave(data);
                        }
                    });
                }.createDelegate(this)
            },
            action_References: {
                nn: 'action_References',
                tooltip: langs('Справочники'),
                text: langs('Справочники'),
                iconCls : 'book32',
                disabled: false,
                menu: new Ext.menu.Menu({
                    items: [
                        {
                            text: langs('Справочник услуг'),
                            tooltip: langs('Справочник услуг'),
                            iconCls: 'services-complex16',
                            handler: function() {
                                getWnd('swUslugaTreeWindow').show({action: 'view'});
                            }
                        },
                        {
                            tooltip: getRLSTitle(),
                            text: getRLSTitle(),
                            iconCls: 'rls16',
                            handler: function() {
                                if ( !getWnd('swRlsViewForm').isVisible() )
                                    getWnd('swRlsViewForm').show({onlyView: true});
                            }
                        },
                        sw.Promed.Actions.swDrugDocumentSprAction
                    ]
                })
            },
            actions_settings: {
                nn: 'actions_settings',
                iconCls: 'settings32',
                text: langs('Сервис'),
                tooltip: langs('Сервис'),
                listeners: {
                    'click': function(){
                        var menu = Ext.menu.MenuMgr.get('wpfdw_menu_windows');
                        menu.removeAll();
                        var number = 1;
                        Ext.WindowMgr.each(function(wnd){
                            if ( wnd.isVisible() )
                            {
                                if ( Ext.WindowMgr.getActive().id == wnd.id )
                                {
                                    menu.add(new Ext.menu.Item(
                                        {
                                            text: number + ". " + wnd.title,
                                            iconCls : 'checked16',
                                            checked: true,
                                            handler: function()
                                            {
                                                Ext.getCmp(wnd.id).toFront();
                                            }
                                        })
                                    );
                                    number++;
                                }
                                else
                                {
                                    menu.add(new Ext.menu.Item(
                                        {
                                            text: number + ". " + wnd.title,
                                            iconCls : 'x-btn-text',
                                            handler: function()
                                            {
                                                Ext.getCmp(wnd.id).toFront();
                                            }
                                        })
                                    );
                                    number++;
                                }
                            }
                        });
                        if ( menu.items.getCount() == 0 )
                            menu.add({
                                text: langs('Открытых окон нет'),
                                iconCls : 'x-btn-text',
                                handler: function()
                                {
                                }
                            });
                        else
                        {
                            menu.add(new Ext.menu.Separator());
                            menu.add(new Ext.menu.Item(
                                {
                                    text: langs('Закрыть все окна'),
                                    iconCls : 'close16',
                                    handler: function()
                                    {
                                        Ext.WindowMgr.each(function(wnd){
                                            if ( wnd.isVisible() )
                                            {
                                                wnd.hide();
                                            }
                                        });
                                    }
                                })
                            );
                        }
                    }
                },
                menu: new Ext.menu.Menu({
                    items: [
                        /*{
                            nn: 'action_Settings',
                            tooltip: langs('Пользователь ЛИС-системы'),
                            text: langs('Пользователь ЛИС-системы'),
                            iconCls : 'settings32',
                            disabled: false,
                            handler: function()
                            {
                                getWnd('swLisUserEditWindow').show({MedService_id: win.MedService_id});
                            }
                        },*/
                        {
                            text: langs('Данные об учетной записи пользователя'),
                            nn: 'action_user_about',
                            iconCls: 'user16',
                            menu: new Ext.menu.Menu(
                                {
                                    //plain: true,
                                    id: 'user_menu',
                                    items:
                                        [
                                            {
                                                disabled: true,
                                                iconCls: 'user16',
                                                text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+Ext.globalOptions.globals.lpu_nick,
                                                xtype: 'tbtext'
                                            }
                                        ]
                                })
                        },
                        {
                            nn: 'action_settings',
                            text: langs('Настройки'),
                            tooltip: langs('Просмотр и редактирование настроек'),
                            iconCls : 'settings16',
                            handler: function()
                            {
                                getWnd('swOptionsWindow').show();
                            }
                        },
                        {
                            nn: 'action_selectMO',
                            text: langs('Выбор МО'),
                            tooltip: langs('Выбор МО'),
                            hidden: !isSuperAdmin(),
                            iconCls: 'lpu-select16',
                            handler: function()
                            {
                                Ext.WindowMgr.each(function(wnd){
                                    if ( wnd.isVisible() )
                                    {
                                        wnd.hide();
                                    }
                                });
                                getWnd('swSelectLpuWindow').show({});
                            }
                        },
                        {
                            text: langs('Выбор АРМ по умолчанию'),
                            tooltip: langs('Выбор АРМ по умолчанию'),
                            iconCls: 'lab-assist16',
                            handler: function()
                            {
                                getWnd('swSelectWorkPlaceWindow').show();
                            }
                        },
                        {
                            nn: 'action_UserProfile',
                            text: langs('Мой профиль'),
                            tooltip: langs('Профиль пользователя'),
                            iconCls : 'user16',
                            hidden: false,
                            handler: function()
                            {
                                args = {};
                                args.action = 'edit';
                                getWnd('swUserProfileEditWindow').show(args);
                            }
                        },
                        {
                            text: langs('Окна'),
                            nn: 'action_windows',
                            iconCls: 'windows16',
                            listeners: {
                                'click': function(e) {
                                    var menu = Ext.menu.MenuMgr.get('wpfdw_menu_windows');
                                    menu.removeAll();
                                    var number = 1;
                                    Ext.WindowMgr.each(function(wnd){
                                        if ( wnd.isVisible() )
                                        {
                                            if ( Ext.WindowMgr.getActive().id == wnd.id )
                                            {
                                                menu.add(new Ext.menu.Item(
                                                    {
                                                        text: number + ". " + wnd.title,
                                                        iconCls : 'checked16',
                                                        checked: true,
                                                        handler: function()
                                                        {
                                                            Ext.getCmp(wnd.id).toFront();
                                                        }
                                                    })
                                                );
                                                number++;
                                            }
                                            else
                                            {
                                                menu.add(new Ext.menu.Item(
                                                    {
                                                        text: number + ". " + wnd.title,
                                                        iconCls : 'x-btn-text',
                                                        handler: function()
                                                        {
                                                            Ext.getCmp(wnd.id).toFront();
                                                        }
                                                    })
                                                );
                                                number++;
                                            }
                                        }
                                    });
                                    if ( menu.items.getCount() == 0 )
                                        menu.add({
                                            text: langs('Открытых окон нет'),
                                            iconCls : 'x-btn-text',
                                            handler: function()
                                            {
                                            }
                                        });
                                    else
                                    {
                                        menu.add(new Ext.menu.Separator());
                                        menu.add(new Ext.menu.Item(
                                            {
                                                text: langs('Закрыть все окна'),
                                                iconCls : 'close16',
                                                handler: function()
                                                {
                                                    Ext.WindowMgr.each(function(wnd){
                                                        if ( wnd.isVisible() )
                                                        {
                                                            wnd.hide();
                                                        }
                                                    });
                                                }
                                            })
                                        );
                                    }
                                },
                                'mouseover': function() {
                                    var menu = Ext.menu.MenuMgr.get('wpfdw_menu_windows');
                                    menu.removeAll();
                                    var number = 1;
                                    Ext.WindowMgr.each(function(wnd){
                                        if ( wnd.isVisible() )
                                        {
                                            if ( Ext.WindowMgr.getActive().id == wnd.id )
                                            {
                                                menu.add(new Ext.menu.Item(
                                                    {
                                                        text: number + ". " + wnd.title,
                                                        iconCls : 'checked16',
                                                        checked: true,
                                                        handler: function()
                                                        {
                                                            Ext.getCmp(wnd.id).toFront();
                                                        }
                                                    })
                                                );
                                                number++;
                                            }
                                            else
                                            {
                                                menu.add(new Ext.menu.Item(
                                                    {
                                                        text: number + ". " + wnd.title,
                                                        iconCls : 'x-btn-text',
                                                        handler: function()
                                                        {
                                                            Ext.getCmp(wnd.id).toFront();
                                                        }
                                                    })
                                                );
                                                number++;
                                            }
                                        }
                                    });
                                    if ( menu.items.getCount() == 0 )
                                        menu.add({
                                            text: langs('Открытых окон нет'),
                                            iconCls : 'x-btn-text',
                                            handler: function()
                                            {
                                            }
                                        });
                                    else
                                    {
                                        menu.add(new Ext.menu.Separator());
                                        menu.add(new Ext.menu.Item(
                                            {
                                                text: langs('Закрыть все окна'),
                                                iconCls : 'close16',
                                                handler: function()
                                                {
                                                    Ext.WindowMgr.each(function(wnd){
                                                        if ( wnd.isVisible() )
                                                        {
                                                            wnd.hide();
                                                        }
                                                    });
                                                }
                                            })
                                        );
                                    }
                                }
                            },
                            menu: new Ext.menu.Menu(
                                {
                                    //plain: true,
                                    id: 'wpfdw_menu_windows',
                                    items: [
                                        '-'
                                    ]
                                }),
                            tabIndex: -1
                        }
                    ]
                })
            },
			actions_methodsIFA: {
				nn: 'actions_methodsIFA',
				text: langs('Методики ИФА'),
				tooltip: langs('Методики ИФА'),
				hidden: true,
				iconCls : 'methodsifa16',
				handler: function() {
					params = {}
					params.MedService_id = win.MedService_id;
					params.callback = function () {
						win.formActions.methodsIFA.getStore().load();
						win.formActions.analyzerTestIFA.getStore().load();
					}
					getWnd('swMethodsIFAWindow').show(params);
				}
			}
        };

		var uslugaNameRendererFn = function(value, cellEl, rec) {
			var result = '';
			if (!Ext.isEmpty(value) && value[0] == "[" && value[value.length-1] == "]") {
				var uslugas = Ext.util.JSON.decode(value);
				for(var k in uslugas) {
					if (uslugas[k].UslugaComplex_Name) {
						if (!Ext.isEmpty(result)) {
							result += '<br />';
						}
						result += uslugas[k].UslugaComplex_Name;
					}
				}

				return result;
			} else {
				return value;
			}
		};

		var UslugaFilter = Ext.extend(sw.Promed.SwUslugaComplexPidCombo,{
			name:'UslugaComplex_id',
			enableKeyEvents: true,
			listeners:{
				'render': function() {
					var store = this.getStore();
					store.addListener('beforeload', function() {
						store.baseParams.MedService_id = win.MedService_id;
						store.baseParams.medServiceComplexOnly = true; // только комплексные (refs #21804)
						store.baseParams.level = 0;

						if (win.MedServiceType_SysNick.inlist(['pzm', 'reglab'])) {
							store.baseParams.linkedMesServiceOnly = true; // для ПЗ и рег.службы услуги со связанных служб
						}
					});
				}
			}
		});

		var MedStaffFactFilter = Ext.extend(sw.Promed.SwMedStaffFactGlobalCombo, {
			name:'MedStaffFact_id',
			width:500,
			listWidth: 500,
			disabled: true,
			enableKeyEvents: true
		});

		var updateChildCombobox = function (value) {
			var combo = this;
			var isEmpty = !value;
			var keyField = combo.getStore().key;
			combo.childs.forEach( function(childCombo) {
				var childStore = childCombo.getStore();
				childCombo.setDisabled(isEmpty);
				childCombo.clearValue();
				if(childCombo.updateChild) {
					childCombo.updateChild(null);
				}
				if( childStore.baseParams[keyField] != value || !childStore.getCount() ) {
					childStore.removeAll();
					childStore.baseParams[keyField] = value;
					isEmpty || childStore.load();
				}
			});
		};

		var LpuSectionFilter = Ext.extend(sw.Promed.SwLpuSectionCombo, {
			name: 'LpuSection_id',
			hiddenName:'LpuSection_id',
			xtype:'swlpusectioncombo',
			listWidth: 500,
			enableKeyEvents: true,
			disabled: true,
			separateStore: true,
			ctxSerach: true,
			updateChild: updateChildCombobox,
			listeners: {
				change: function(combo) {
					combo.updateChild( combo.getValue() );
				},
				select: function(combo, rec, idx) {
					combo.updateChild( combo.getValue() );
				}
			}
		});

		var LpuFilter = Ext.extend(sw.Promed.SwLpuOpenedCombo, {
			autoLoad: true,
			enableKeyEvents: true,
			ctxSerach: true,
			typeAhead: false,
			name: 'Lpu_sid',
			hiddenName: 'Lpu_sid',
			listWidth: 300,
			childs: [],
			updateChild: updateChildCombobox,
			listeners: {
				change: function(combo) {
					combo.updateChild( combo.getValue() );
				},
				select: function(combo, rec, idx) {
					combo.updateChild( combo.getValue() );
				}
			}
		});

		var reqMedStaffFactFilterCombo = new MedStaffFactFilter(),
			labMedStaffFactFilterCombo = new MedStaffFactFilter(),
			reqLpuSectionFilterCombo = new LpuSectionFilter({
				childs: [ reqMedStaffFactFilterCombo ]
			}),
			labLpuSectionFilterCombo = new LpuSectionFilter({
				childs: [ labMedStaffFactFilterCombo ]
			});

		labMedStaffFactFilterCombo.getStore().baseParams.mode = "combo";
		reqMedStaffFactFilterCombo.getStore().baseParams.mode = "combo";

        var personOnQuarantineRenderer = function(value, meta, record) {
			if (record.get('PersonQuarantine_IsOn') == 2) {
				value = '<font color="red">' + value + '</font>';
			}
			return value;
        };
        
		var uslugaNameRendererFn = function(value, cellEl, rec) {
			var result = '';
			if (!Ext.isEmpty(value) && value[0] == "[" && value[value.length-1] == "]") {
				var uslugas = Ext.util.JSON.decode(value);
				for(var k in uslugas) {
					if (uslugas[k].UslugaComplex_Name) {
						if (!Ext.isEmpty(result)) {
							result += '<br />';
						}
						result += uslugas[k].UslugaComplex_Name;
					}
				}

				return result;
			} else {
				return value;
			}
		};

		var UslugaFilter = Ext.extend(sw.Promed.SwUslugaComplexPidCombo,{
			name:'UslugaComplex_id',
			enableKeyEvents: true,
			listeners:{
				'render': function() {
					var store = this.getStore();
					store.addListener('beforeload', function() {
						store.baseParams.MedService_id = win.MedService_id;
						store.baseParams.medServiceComplexOnly = true; // только комплексные (refs #21804)
						store.baseParams.level = 0;

						if (win.MedServiceType_SysNick.inlist(['pzm', 'reglab'])) {
							store.baseParams.linkedMesServiceOnly = true; // для ПЗ и рег.службы услуги со связанных служб
						}
					});
				}
			}
		});

		var MedStaffFactFilter = Ext.extend(sw.Promed.SwMedStaffFactGlobalCombo, {
			name:'MedStaffFact_id',
			width:500,
			listWidth: 500,
			disabled: true,
			enableKeyEvents: true
		});

		var updateChildCombobox = function (value) {
			var combo = this;
			var isEmpty = !value;
			var keyField = combo.getStore().key;
			combo.childs.forEach( function(childCombo) {
				var childStore = childCombo.getStore();
				childCombo.setDisabled(isEmpty);
				childCombo.clearValue();
				if(childCombo.updateChild) {
					childCombo.updateChild(null);
				}
				if( childStore.baseParams[keyField] != value || !childStore.getCount() ) {
					childStore.removeAll();
					childStore.baseParams[keyField] = value;
					isEmpty || childStore.load();
				}
			});
		};

		var LpuSectionFilter = Ext.extend(sw.Promed.SwLpuSectionCombo, {
			name: 'LpuSection_id',
			hiddenName:'LpuSection_id',
			xtype:'swlpusectioncombo',
			listWidth: 500,
			enableKeyEvents: true,
			disabled: true,
			separateStore: true,
			ctxSerach: true,
			updateChild: updateChildCombobox,
			listeners: {
				change: function(combo) {
					combo.updateChild( combo.getValue() );
				},
				select: function(combo, rec, idx) {
					combo.updateChild( combo.getValue() );
				}
			}
		});

		var LpuFilter = Ext.extend(sw.Promed.SwLpuOpenedCombo, {
			autoLoad: true,
			enableKeyEvents: true,
			ctxSerach: true,
			typeAhead: false,
			name: 'Lpu_sid',
			hiddenName: 'Lpu_sid',
			listWidth: 300,
			childs: [],
			updateChild: updateChildCombobox,
			listeners: {
				change: function(combo) {
					combo.updateChild( combo.getValue() );
				},
				select: function(combo, rec, idx) {
					combo.updateChild( combo.getValue() );
				}
			}
		});

		var reqMedStaffFactFilterCombo = new MedStaffFactFilter(),
			labMedStaffFactFilterCombo = new MedStaffFactFilter(),
			reqLpuSectionFilterCombo = new LpuSectionFilter({
				childs: [ reqMedStaffFactFilterCombo ]
			}),
			labLpuSectionFilterCombo = new LpuSectionFilter({
				childs: [ labMedStaffFactFilterCombo ]
			});

		labMedStaffFactFilterCombo.getStore().baseParams.mode = "combo";
		reqMedStaffFactFilterCombo.getStore().baseParams.mode = "combo";

        // грид заявок      
        this.GridPanel = new sw.Promed.ViewFrame({
            noSelectFirstRowOnFocus: true,
            showCountInTop: false,
            checkBoxWidth: 25,
            border: false,
            id: win.id + 'LabRequestGridPanel',
            region: 'center',
            stateful: true,
            selectionModel: 'multiselect',
            autoExpandColumn: 'autoexpand',
            groups:false,
            useEmptyRecord: false,
            printWithNumberColumn: true,
            actions: [
                { name:'action_add', text: langs('Добавить'), handler: function() {
					if ( !win.addWithoutRegIsAllowed() ) return;
					this.openLabRequestEditWindow('add'); }.createDelegate(this)
				},
                { name:'action_edit', handler: function() { this.openLabRequestEditWindow('edit'); }.createDelegate(this) },
                { name:'action_view', handler: function() { this.openLabRequestEditWindow('view'); }.createDelegate(this) },
                {
                    name:'action_delete',
                    text: langs('Отклонить'),
                    disabled: true,
                    handler: function (){

                        var records = win.GridPanel.getGrid().getSelectionModel().getSelections();
                        var EvnDirection_ids = [];

                        for (var i = 0; i < records.length; i++) {
                            if (
                                !Ext.isEmpty(records[i].get('EvnDirection_id'))
                                && records[i].get('EvnDirection_id') > 0
                            ) {
                                // если есть ЭО
                                if (win.ElectronicQueuePanel.electronicQueueEnable) {
                                    if ( // если статус Ожидает, то можем отклонить
                                        !Ext.isEmpty(records[i].get('ElectronicTalon_id'))
                                        && !Ext.isEmpty(records[i].get('ElectronicTalonStatus_id'))
                                        && records[i].get('ElectronicTalonStatus_id') < 2) {
                                        EvnDirection_ids = EvnDirection_ids.concat(records[i].get('EvnDirection_id').toString());
                                    }
                                } else {
                                    EvnDirection_ids = EvnDirection_ids.concat(records[i].get('EvnDirection_id').toString());
                                }
                            }
                        }

                        if (!Ext.isEmpty(EvnDirection_ids) && EvnDirection_ids.length > 0) {

                            getWnd('swSelectEvnStatusCauseWindow').show({
                                EvnClass_id: 27,
                                formType: 'labdiag',
                                callback: function(EvnStatusCauseData) {

                                    if (!Ext.isEmpty(EvnStatusCauseData.EvnStatusCause_id)) {

                                        win.getLoadMask("Отмена направлений на лабораторное обследование...").show();

                                        Ext.Ajax.request({
                                            url: '/?c=EvnLabRequest&m=cancelDirection',
                                            params: {
                                                EvnDirection_ids: Ext.util.JSON.encode(EvnDirection_ids),
                                                EvnStatusCause_id: EvnStatusCauseData.EvnStatusCause_id,
                                                EvnStatusHistory_Cause: EvnStatusCauseData.EvnStatusHistory_Cause
                                            },
                                            callback: function (options, success, response) {

                                                win.getLoadMask().hide();
                                                if (success) {win.GridPanel.loadData(); }
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                },
                {name:'action_refresh', handler: function() {
                    win.GridPanel.getGrid().getStore().reload();
                    win.focusOnGrid();
                }},
                {name: 'action_print'}
            ],
            printObject: function() {
                var selected_record = win.GridPanel.getGrid().getSelectionModel().getSelected();
                if (!selected_record) {
                    return false;
                }

                var grid = win.GridPanel.getGrid();

                var records = win.GridPanel.getGrid().getSelectionModel().getSelections();
                var EvnDirection_ids = [];
                for (var i = 0; i < records.length; i++) {
                    if (!Ext.isEmpty(records[i].get('EvnDirection_id')) && records[i].get('EvnDirection_id') > 0) {
                        EvnDirection_ids = EvnDirection_ids.concat(records[i].get('EvnDirection_id').toString());
                    }
                }

                if (!Ext.isEmpty(EvnDirection_ids) && EvnDirection_ids.length > 0) {
                    // получаем EvnUslugaPar_id's для заявок, по ним печатаем с использованием нового шаблона
                    win.getLoadMask('Получение данных заявок').show();
                    // обновить на стороне сервера
                    Ext.Ajax.request({
                        url: '/?c=EvnLabRequest&m=getEvnUslugaParForPrint',
                        params: {
							EvnDirections: Ext.util.JSON.encode(EvnDirection_ids),
							isProtocolPrinted: 1
                        },
                        callback: function (options, success, response) {
							win.getLoadMask().hide();
							grid.getStore().reload();
                            if (success && response.responseText != '') {
                                var result  = Ext.util.JSON.decode(response.responseText);
                                var Report_Params = '&paramEvnUslugaPar=',
									Report_FileName = (Ext.globalOptions.lis.use_postgresql_lis ? 'EvnParCard_list_pg' : 'EvnParCard_list') + '.rptdesign';
                                var ids = [];
                                for (var i = 0; i < result.length; i++) {
                                    if (!Ext6.isEmpty(result[i].EvnUslugaPar_id)) {
                                        ids.push(result[i].EvnUslugaPar_id);
                                    }
                                    if ((i+1)%300 == 0) {
                                        ids = ids.join(',');
                                        Report_Params += ids;
                                        printBirt({
                                            'Report_FileName': Report_FileName,
                                            'Report_Params': Report_Params,
                                            'Report_Format': 'pdf'
                                        });
                                        Report_Params = '&paramEvnUslugaPar=';
                                        ids = [];
                                    }
                                }
                                if (ids.length != 0) {
                                    ids = ids.join(',');
                                    Report_Params += ids;
                                    printBirt({
                                        'Report_FileName': Report_FileName,
                                        'Report_Params': Report_Params,
                                        'Report_Format': 'pdf'
                                    });
                                }

                                win.focusOnGrid();
                            }
                        }
                    });
                }

                return false;
            },
			// массовая печать протоколов COVID
			// 1. Проверяем наличие услуги с атрибутом "Требуется код контингента COVID" в заявке
			// 2. Отправляем отфильтрованный список заявок в BIRT
			printCovidProtocol: function() {
				var selectedRecords = win.GridPanel.getMultiSelections();
				var EvnLabRequest_ids = [];

				selectedRecords.forEach( function(rec) {
					EvnLabRequest_ids.push( rec.get('EvnLabRequest_id') );
				} );

				ajaxRequest({
					maskEl: win.GridPanel.el,
					maskText: langs('Проверка наличия атрибутов "Требуется код контингента COVID"'),
					url: '/?c=EvnLabRequest&m=filterEvnLabRequests',
					params: {
						UslugaTestStatuses: "ok,exec",
						UslugaComplexAttributeType_SysNick: "contingent_covid",
						EvnLabRequest_ids: EvnLabRequest_ids.join(',')
					},
					onSuccess: function(LabRequests) {
						if( typeof LabRequests != 'object' || !LabRequests.length ) {
							sw.swMsg.alert(langs('Ошибка'), langs("В выбранных заявках нет одобренных или выполненных исследований COVID для вывода на печать"));
							return;
						}

						var EvnLabRequest_ids = [];
						LabRequests.forEach( function(requestObj) {
							EvnLabRequest_ids.push(requestObj.EvnLabRequest_id);
						});

						printBirt({
							'Report_FileName': 'printCOVIDprotocol.rptdesign',
							'Report_Params': '&paramEvnLabRequest=' + EvnLabRequest_ids.join(','),
							'Report_Format': 'pdf'
						})

					},
					onError: function() {
						sw.swMsg.alert(langs('Ошибка'), langs("Ошибка при выполнении запроса"));
					}
				});

			},
            gridplugins: [win.filterRowReq],
            autoLoadData: false,
            stringfields: [
                // Поля для отображение в гриде
                {name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
                {name: 'EvnLabRequest_id', type: 'int', hidden: true},
                //{name: 'Person_id', type: 'int', hidden: false},
                {name: 'Person_Surname', type: 'string', hidden: true},
                {name: 'Person_Secname', type: 'string', hidden: true},
                {name: 'Person_Firname', type: 'string', hidden: true},
                {name: 'PersonQuarantine_IsOn', type: 'int', hidden: true},
                {name: 'EvnStatus_id', type: 'string', hidden: true},
                {name: 'Person_id', type: 'int', width: 100, header: 'ID пациента',
                	renderer: personOnQuarantineRenderer,
                    filter: new Ext.form.TextField({
                        enableKeyEvents: true,
                        name:'Person_id'
                    })
                },
                {name: 'Person_ShortFio', header: '<div style="height:16px;"><div style="float:left;">Фамилия И.О.</div> <a title="Считать с карты" href="#" onClick="getWnd(\'swAssistantWorkPlaceWindow\').readFromCard();"><img style="float:right; margin-right: 10px; vertical-align: bottom;" src="/img/icons/idcard16.png" /></a></div>', width: 120,
					renderer: personOnQuarantineRenderer,
                    filter: new Ext.form.TextField({
                        enableKeyEvents: true,
                        name:'Person_SurName'
                    })
                },
				// Артамонов И.Г. 08.07.2019
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения') , width: 100,
					renderer: function(value, meta, record) {
						if (!value) return value;
						return personOnQuarantineRenderer(value.format('d.m.Y'), meta, record);
					}
//                    filter: new Ext.form.TextField({
//                        enableKeyEvents: true,
//                        name:'Person_BornDate',
//						maskRe: /\d/,
//						plugins: [new Ext.ux.InputTextMask('99.99.9999', true)]
//                    })
                },
                {name: 'TimetableMedService_begTime', type: 'timedate', header: langs('Запись'), direction: 'ASC', width: 110},
                {name: 'MedService_Nick', type: 'string', header: 'Лаборатория', width: 200,
                    filter: new sw.Promed.SwMedServiceGlobalCombo({
                        id: win.id + 'MedServiceComboField',
                        fieldLabel: 'Служба',
                        hiddenName: 'MedServiceLab_id',
                        name: 'MedServiceLab_id',
                        listWidth: 400,
                        enableKeyEvents: true
                    })
                },
                {name: 'EvnDirection_IsCito', headerAlign: 'left', align: 'center', header: 'Cito!', direction: 'DESC', type: 'string', width: 60, filterStyle: "text-align: center;",
                    filter:new Ext.form.Checkbox({
                        autoLoad: true,
                        hiddenName:'EvnDirection_IsCito',
                        name:'EvnDirection_IsCito',
                        enableKeyEvents: true,
                        width:100
                    })
                },
				{name: 'Person_IsBDZ', header: 'БДЗ', type: 'checkcolumn', width: 40, hidden: (getRegionNick() != 'kz')},
                {name: 'EvnLabRequest_UslugaName', header: langs('Услуга (исследование)'), width: 280, id: 'autoexpand',
                    renderer: uslugaNameRendererFn,
                    filter: new UslugaFilter(),
                },
                {name:'ProbaStatus', headerAlign: 'left', align: 'center', width: 30, renderer:function(x,c,rec){
                    var n = rec.get('ProbaStatus');
                    var qtip = '';

                    switch(rec.get('ProbaStatus')) {
                        case 'needmore':
                            qtip = langs('Нужно взять две или более проб');
                        break;
                        case 'needone':
                            qtip = langs('Нужно взять одну пробу');
                        break;
                        case 'notall':
                            qtip = langs('Взяты не все пробы');
                        break;
                        case 'new':
                            qtip = langs('Новая проба взята, но не отправлена на анализатор');
                        break;
                        case 'toanaliz':
                            qtip = langs('Проба отправлена на анализатор (результатов нет)');
                        break;
                        case 'exec':
                            qtip = langs('Выполнено. Есть результаты');
                        break;
                        case 'someOk':
                            qtip = langs('Частично одобрено');
                        break;
                        case 'Ok':
                            qtip = langs('Полностью одобрено');
                        break;
                        case 'bad':
                            qtip = langs('Брак пробы');
                        break;
                    }

                    return "<img ext:qtip='"+qtip+"' src='../img/icons/lis-prob-"+n+".png'/>"
                }, header: langs('Статус'),width: 70},
                {name: 'EvnLabRequest_SampleNum', type: 'string', header: langs('Номер пробы'), width: 80},
                {name: 'EvnLabRequest_Tests', headerAlign: 'left', align: 'center', width: 55, header: langs('Тесты'), renderer: function(value, cellEl, rec) {
                    if (rec.get('needTestMenu') != 1) {
                        return value;
                    } else {
                        return value + ' <a href="#" class="showComposition" ' +
                            'id="composition_'+ rec.get('EvnDirection_id') +'" '+
                            'onclick="Ext.getCmp(\'swAssistantWorkPlaceWindow\').showComposition('+
                            "'"+ rec.get('EvnDirection_id') +"'"+
                            ')"><img src="/img/lis/customlis.png" /></a>';
                    }
                }},
                {name: 'EvnLabSample_IsOutNorm', headerAlign: 'left', align: 'center', width: 25,
                    renderer:function(x,c,rec) {
                        if (rec.get('EvnLabSample_IsOutNorm') == 2) {
                            return "<img src='../img/icons/warning16.png'/>"
                        } else {
                            return "";
                        }
                    }, filterStyle: "text-align: center;",
                    filter:new Ext.form.Checkbox({
                        autoLoad: true,
                        hiddenName:'EvnLabSample_IsOutNorm',
                        name:'EvnLabSample_IsOutNorm',
                        enableKeyEvents: true,
                        width:100
                    }), header: langs('Отклонение'),width:80
                },
                {name: 'EvnLabRequest_FullBarCode', header: langs('Штрих-код'), width: 110,
                    filter: new Ext.form.TextField({
                        enableKeyEvents: true,
                        name:'EvnLabRequest_FullBarCode'
                    }),
                    renderer: function(value, cellEl, rec) {
                        var result = "";
                        // разделить value по ,
                        if (!Ext.isEmpty(value)) {
                            var val_array = value.split(',');
                            for(var k in val_array) {
                                if (!Ext.isEmpty(val_array[k]) && typeof val_array[k] == 'string') {
                                    var valone_array = val_array[k].split(':');
                                    if (!Ext.isEmpty(valone_array[1])) {
                                        if (!Ext.isEmpty(result)) {
                                            result = result + ", ";
                                        }
                                        result = result + "<a href='javascript://' onClick='Ext.getCmp(\"swAssistantWorkPlaceWindow\").showInputBarCodeField(\"lrbarcode"+rec.get('EvnDirection_id')+"\", "+valone_array[0].trim()+",this);'>"+valone_array[1].trim()+"</a>";
                                    }
                                }
                            }
                        }

                        return "<div id='lrbarcode"+rec.get('EvnDirection_id')+"_inp'></div><div id='lrbarcode"+rec.get('EvnDirection_id')+"'>" + result + "</div>";
                    },
                    sortType: function(value) {
                        if (!Ext.isEmpty(value)) {
                            var val_array = value.split(',');
                            for (var k in val_array) {
                                if (!Ext.isEmpty(val_array[k]) && typeof val_array[k] == 'string') {
                                    var valone_array = val_array[k].split(':');
                                    if (!Ext.isEmpty(valone_array[1])) {
                                        return valone_array[1]; // сортировка по штрих-коду
                                    }
                                }
                            }
                        }
                        return value;
                    }
                },
                {name: 'EvnDirection_Num', header: langs('№ напр.'), width: 55,
                    filter: new Ext.form.TextField({
                        enableKeyEvents: true,
                        name:'EvnDirection_Num'
                    })
                },
                {name: 'EvnDirection_setDate', sort: true, dateFormat: 'd.m.Y', type: 'date', header: langs('Дата напр.'), width: 80},
                {name: 'PrehospDirect_Name', header: langs('Кем направлен'), width: 100, hidden: true, //todo удалить после правки печати штрихкодов?
					filter: new Ext.form.TextField({
						enableKeyEvents: true,
						name:'PrehospDirect_Name'
					})
                },
                {name: 'EMD', header: langs('ЭМД'), width: 100,
					filter: new Ext.form.ComboBox({
                        enableKeyEvents: true,
                        allowBlank: true,
                        fieldLabel: langs('ЭМД'),
                        hiddenName: 'filterSign',
                        name: 'filterSign',
                        width: 230,
                        triggerAction: 'all',
                        value: null,
                        tpl: '<tpl for="."><div class="x-combo-list-item">&nbsp;{text}</div></tpl>',
                        store: [
                            [1, langs('Подписан')],
                            [2, langs('Не подписан')],
                            [3, langs('Не актуален')]
                        ]
                    }),
                    renderer: function(value, cellEl, rec) {
                        var result = '';
                        var uslugaName = rec.get('EvnLabRequest_UslugaName');
                        if (!Ext.isEmpty(uslugaName) && uslugaName[0] == "[" && uslugaName[uslugaName.length-1] == "]") {
                            // разджейсониваем
                            var uslugas = Ext.util.JSON.decode(uslugaName);
                            for(var k in uslugas) {
                                if (uslugas[k].EvnUslugaPar_id) {
                                    result += '<div class="emd-here-tiny" data-objectname="EvnUslugaPar" data-disabledsign="' + (Ext.isEmpty(uslugas[k].EvnUslugaPar_setDate) ? "1" : "0") + '" data-objectid="' + uslugas[k].EvnUslugaPar_id + '" data-issigned="' + uslugas[k].EvnUslugaPar_IsSigned + '"></div>';
                                }
                            }
                        }

                        return result;
                    }
                },
				{name: 'EvnLabRequest_IsProtocolPrinted', type: 'int', width: 100, header: langs('Протокол распечатан'),
					align: 'center',
					renderer: function(value, cellEl, rec) {
						var checkPrinted = '';

						if(!Ext6.isEmpty(value)) {
							checkPrinted = "<img src='../img/icons/input/checkbox-on.png'/>";
						}
						
						return checkPrinted;
					}
				},
				{name: 'Lpu_Nick', header:langs('Медицинская организация'),
					filter: new LpuFilter({
						childs: [ reqLpuSectionFilterCombo ]
					})
				},
				{name: 'LpuSection_Name', header:langs('Отделение направления'),
					filter: reqLpuSectionFilterCombo
				},
				{name: 'EDMedPersonalSurname', header:langs('Фамилия врача'),
					filter: reqMedStaffFactFilterCombo
				},
				{name: 'EvnLabRequest_RegNum', header:langs('Регистрационный номер'), width: 100,
					filter: new Ext.form.TextField({
						enableKeyEvents: true,
						name: 'EvnLabRequest_RegNum'
					})
				},
                {name: 'canEdit', hidden: true},
                {name: 'LpuSection_Code', type: 'string', hidden: true},
                {name: 'EvnLabSample_ids', type: 'string', hidden: true},
                {name: 'needTestMenu', type: 'int', hidden: true},
                win.filterRowReq
            ],
            groupSortInfo: {
                field: 'Person_ShortFio',
                direction: 'ASC'
            },
            dataUrl: '/?c=EvnLabRequest&m=loadEvnLabRequestList',
            totalProperty: 'totalCount',
            title: '',
            onLoadData: function() {
                win.addGridFilter();
                win.focusOnGrid();
                win.renderEMDButtons(this.getEl());

                sm = this.getGrid().getSelectionModel();

                //при загрузке заявок цветовая дифференциация должна стоять
                var v = this.getGrid().getView(),
                    i = 0;
				this.getGrid().getStore().data.items.forEach(function(el) {
					var row = v.getRow(i);
					if (!Ext.isEmpty(row))
					    Ext.fly(row).replaceClass("x-grid-state-"+el.get('EvnStatus_id'),"x-grid-state-"+el.get('EvnStatus_id')+"-selected");
					i++;
				});
            },
            onRowDeSelect: function(sm,rowIdx,record) {
                            var row = this.getGrid().view.getRow(rowIdx);
                            Ext.fly( row).replaceClass("x-grid-state-"+record.get('EvnStatus_id')+"-selected","x-grid-state-"+record.get('EvnStatus_id'));
                        },
            onRowSelect: function(sm,rowIdx,record) {
                            var row = this.getGrid().view.getRow(rowIdx);
                            Ext.fly(row).replaceClass("x-grid-state-"+record.get('EvnStatus_id'),"x-grid-state-"+record.get('EvnStatus_id')+"-selected");
                        },
            onMultiSelectionChangeAdvanced: function() {
                var disableddel = true;
                var disabledprint = true; //е дизаблить печать протоколов если хотя бы одна запись выполненная
                var disableAll = true;
                var disableApprove = true;
                var disableSign = true;
                var disablePrintBarCodes = true;
                var disableTake = true;
                var disableCancel = true;
                var current_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');


                if (sm.getCount() >= 1){
                    sm.getSelections().forEach(function (el) {
                        // печать только для "С результатами" и "Одобренные"
                        if (disabledprint){
                            disabledprint = (el.get('EvnStatus_id') != 3 && el.get('EvnStatus_id') != 4);
                        }
                    });
                }

                if (sm.getCount() > 0) {
                    disableAll = false;
                    disableddel = false;
					if (getGlobalOptions().hasEMDCertificate) {
						disableSign = false;
					}
                    // идём по выделенным и смотрим можно ли их удалять
                    var records = this.getGrid().getSelectionModel().getSelections();
                    for (var i = 0; i < records.length; i++) {
                        disableApprove = false;//disableApprove && (records[i].get('EvnLabSample_IsOutNorm') == 2);

                        if (!Ext.isEmpty(records[i].get('EvnLabSample_ids'))) {
                            disablePrintBarCodes  = false;
                        }

                        disableddel =
                            disableddel ||
                            current_date > Date.parseDate(Ext.util.Format.date(records[i].get('TimetableMedService_begTime')),'d.m.Y') ||
                            (!(records[i].get('canEdit') == 1) || Ext.isEmpty(records[i].get('EvnStatus_id')) || !records[i].get('EvnStatus_id').inlist([1]));

                        if (win.ElectronicQueuePanel.electronicQueueEnable) {
                            if (
                                records[i].get('ElectronicTalonStatus_id')
                                && records[i].get('ElectronicTalonStatus_id') > 1
                            ) {
                                disableddel = true;
                            }
                        }

                        if (records[i].get('ProbaStatus') && records[i].get('ProbaStatus').inlist(['needmore', 'needone', 'notall'])) {
                            disableTake = false;
                        }
                        if (records[i].get('EvnStatus_id') && records[i].get('EvnStatus_id') == 2) {
                            disableCancel = false;
                        }
                    }
                }

                this.ViewActions.action_print.menu['action_print_barcodes'].setDisabled(disablePrintBarCodes);
                this.ViewActions.action_print.menu['action_print_lab_smpl_list'].setDisabled(sm.getCount() < 1);
                this.getAction('action_delete').setDisabled(disableddel);
                this.getAction('action_print').menu.printObject.setDisabled(disabledprint);
                this.getAction('action_print').menu.printCovidProtocol.setHidden(disabledprint || !getLisOptions().PrintResearchCovid);

                this.getAction('action_lis_sample').setDisabled(disableTake);
                this.getAction('action_lis_sample_cancel').setDisabled(disableCancel);
				//disableApprove = disableApprove || win.MedServiceMedPersonal_isNotApproveRights;
                this.getAction('action_lis_approve').setDisabled(disableApprove);
                this.getAction('action_lis_create').setDisabled(disableAll);
                this.getAction('action_outsourcing_create').setDisabled(disableAll);
                this.getAction('action_sign_all').setDisabled(disableSign);
            },
            onEnter: function () {
                win.resetGridKeyboardInput(win.gridKeyboardInputSequence);
            },
            onKeyboardInputFinished: function (input){
                log(input);
                if (input.length>0) {
                    var found = win.GridPanel.getGrid().getStore().findBy(function (el) {
                        return (!Ext.isEmpty(el.get('EvnLabRequest_FullBarCode')) && el.get('EvnLabRequest_FullBarCode').indexOf(input) != -1);
                    });
                    if (found >= 0) {
                        win.GridPanel.getGrid().getSelectionModel().selectRow(found);
                        win.openLabRequestEditWindow('edit');
                    }
                }
            }
		});
		
		this.GridPanel.getGrid().addListener('sortchange', function(view) {
			win.GridPanel.getGrid().getSelectionModel().clearSelections();
		});
        
        this.GridPanel.getGrid().view.getRowClass = function (row, index)
        {
            var cls = 'x-grid-state-'+row.get('EvnStatus_id');
            return cls;
        }

        this.GridPanel.ViewToolbar.on('render', function(vt){
            this.ViewActions.action_print.menu['action_print_barcodes']=new Ext.Action({name:'action_print_barcodes', text: langs('Печать штрих-кодов'), handler: function () {
                if (getGlobalOptions().region.nick == 'ufa') {
                    switch(Number(Ext.globalOptions.lis.barcode_print_method))
                    {
                        // JS
                        case 1:
                            win.JsBarcode('GridPanel');
                        break;

                        // PDF
                        case 2:
                            win.printBarcodes();
                        break;

                        // JAVA
                        case 3:
                            win.printonZebra('GridPanel');
                        break;

                        case 4:
                            win.zebraBrowserPrint('GridPanel');
                        break;

                        default:
                            sw.swMsg.alert(langs('Ошибка'), 'Выберите метод печати');
                        break;
                    }
                } else {
                    win.printBarcodes();
                }

                win.focusOnGrid();
            }});

            this.getAction('action_print').menu.printObject.setText(langs('Печать протоколов исследования'));
			this.getAction('action_print').menu['printCovidProtocol'] = new Ext.Action({
				name: 'printCovidProtocol',
				text: langs('Печать протоколов исследования COVID'),
				handler: function() {
					win.GridPanel.printCovidProtocol();
				}
			});

			//АИГ - 29.07.2019 - printListPersons
			this.ViewActions.action_print.menu['action_print_ListPersons'] = new Ext.Action({name: 'action_print_ListPersons', text: langs('Печать списка пациентов'), handler: function () {

//					console.log('MedService_id=', win.MedService_id);
//					console.log('MedService_Name=', win.MedService_Name);
//					console.log('MedServiceType_SysNick=', win.MedServiceType_SysNick);
//					console.log('win11=', win);

					var url = ((getGlobalOptions().birtpath) ? getGlobalOptions().birtpath : '') + '/run?__report=report/LispListPersons.rptdesign';
					url += '&MedServiceName=' + win.MedService_Name;  //CurMedService_Name
					url += '&MedServiceType=' + win.MedServiceType_SysNick;  //CurMedServiceType_SysNick
					url += '&MedServiceId=' + win.MedService_id;  //CurMedService_id
					url += '&DateList=' + win.dateMenu.value.toString().substr(0, 10);//'+ "-" + win.dateMenu.value.toString().substr(3,2) + "-" + win.dateMenu.value.toString().substr(0,2);
					url += '&__format=pdf';
					window.open(url, '_blank');
					//win.focusOnGrid();

//					printBirt({
//                        'Report_FileName': 'LispListPersons.rptdesign',
//                        //'Report_Params':  '&MedService_Name=' + getGlobalOptions().CurMedService_Name + '&MedService_Type=' + getGlobalOptions().CurMedServiceType_SysNick + '&MedService_id=' + getGlobalOptions().CurMedService_id + '&Date=' + win.dateMenu.value.toString().substr(0,10),
//						'Report_Params':  '&MedService_Name=' + 'dfdfd' + '&MedService_Type=' + 'rrrr' + '&MedService_id=' + getGlobalOptions().CurMedService_id + '&Date=' + win.dateMenu.value.toString().substr(0,10),
//                        'Report_Format': 'pdf'
//                    });

				}});


            this.ViewActions.action_print.menu['action_print_lab_smpl_list']=new Ext.Action({name:'action_print_lab_smpl_list', text: langs('Печать списка проб'), handler: function () {
                win.printLabSmplList();
                win.focusOnGrid();
            }});

        },this.GridPanel);

        win.LabSampleGridExpander = new Ext.ux.grid.RowExpander({
            tpl: '<div class="ux-row-expander-box"><div>',
            treeLeafProperty: 'is_leaf',
            fixed: true,
            listeners: {
                expand: function(expander, record, body, rowIndex){
                    win.getGrid(record,Ext.get(this.grid.getView().getRow(rowIndex)).child('.ux-row-expander-box'));
                }
            }
        });

		win.LabSampleGrid_BarcodeFilter = new Ext.form.TextField({
			enableKeyEvents: true,
			name:'EvnLabSample_BarCode'
		});

        // грид проб
        this.LabSampleGridPanel = new sw.Promed.ViewFrame({
            noSelectFirstRowOnFocus: true,
            showCountInTop: false,
            checkBoxWidth: 25,
            id: win.id + 'LabSampleGridPanel',
            border: false,
            selectionModel: 'multiselect',
            region: 'center',
            stateful: true,
            layout: 'fit',
            groups:false,
            autoLoadData: false,
            gridplugins: [win.LabSampleGridExpander,this.filterRowLab], // this.filterRowLab
            object: 'EvnLabSample',
            dataUrl: '/?c=EvnLabSample&m=loadWorkList',
            autoExpandColumn: 'autoexpand',
            // grouping: true,
            // groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "заявки" : "заявок"]})',
            // groupingView: {showGroupName: false, showGroupsText: true},
            useEmptyRecord: false,
            printWithNumberColumn: true,
            onEnter: function () {
                win.resetGridKeyboardInput(win.gridKeyboardInputSequence);
            },
            onKeyboardInputFinished: function (input){
                log(input);
                if (input.length>0) {
                    var found = win.LabSampleGridPanel.getGrid().getStore().findBy(function (el){
                        return (!Ext.isEmpty(el.get('EvnLabSample_Num')) && el.get('EvnLabSample_Num').toString().indexOf(input) != -1);
                    });

                    var selections = win.LabSampleGridPanel.getGrid().getSelectionModel().getSelections();
                    if (found >= 0) {
                        var record = win.LabSampleGridPanel.getGrid().getStore().getAt(found);
                        selections.push(record);
                        this.getGrid().getSelectionModel().selectRecords(selections, true);
                    }
                }
            },
            saveAtOnce: false,
            saveAllParams: false,
            stringfields:[
                win.LabSampleGridExpander,
                {name: 'EvnLabSample_id', type: 'int', header: 'ID', key: true},
                {name: 'EvnLabSample_ShortNum', type:'string', header: langs('№ пробы'), width: 80,
                    filter: new Ext.form.TextField({
                        enableKeyEvents: true,
                        name:'EvnLabSample_ShortNum'
                    })
                },
                {name:'ProbaStatus', headerAlign: 'left', align: 'center', width: 30, renderer:function(x,c,rec){
                    var n = rec.get('ProbaStatus');
                    var qtip = '';

                    switch(rec.get('ProbaStatus')) {
                        case 'needmore':
                            qtip = langs('Нужно взять две или более проб');
                        break;
                        case 'needone':
                            qtip = langs('Нужно взять одну пробу');
                        break;
                        case 'notall':
                            qtip = langs('Взяты не все пробы');
                        break;
                        case 'new':
                            qtip = langs('Новая проба взята, но не отправлена на анализатор');
                        break;
                        case 'toanaliz':
                            qtip = langs('Проба отправлена на анализатор (результатов нет)');
                        break;
                        case 'exec':
                            qtip = langs('Выполнено. Есть результаты');
                        break;
                        case 'someOk':
                            qtip = langs('Частично одобрено');
                        break;
                        case 'Ok':
                            qtip = langs('Полностью одобрено');
                        break;
                        case 'bad':
                            qtip = langs('Брак пробы');
                        break;
                        case 'inWorkRuch':
                            qtip = langs('Проба взята, но не отправлена на анализатор');
                            n = 'new';
                        break;
                    }

                    return "<img ext:qtip='"+qtip+"' src='../img/icons/lis-prob-"+n+".png'/>"
                }, header: langs('Статус')},
                {name: 'EvnDirection_IsCito', headerAlign: 'left', align: 'center', type:'string',header: 'Cito!', width: 50, filterStyle: "text-align: center;",
                    filter:new Ext.form.Checkbox({
                        autoLoad: true,
                        hiddenName:'EvnDirection_IsCito',
                        name:'EvnDirection_IsCito',
                        enableKeyEvents: true,
                        width:100
                    })
                },
	            {name: 'Person_IsBDZ', header: 'БДЗ', type: 'checkcolumn', width: 40, hidden: (getRegionNick() != 'kz')},
                {name: 'RefMaterial_id', type:'int', hidden: true},
                {name: 'LabSampleStatus_id', type:'int', hidden: true},
                {name: 'Person_id', type: 'int', hidden: true},
                {name: 'EvnLabRequest_id', type: 'int', hidden: true},
                {name: 'EvnLabSample_Num', type: 'int', hidden: true},
                {name: 'RefMaterial_Name', header: langs('Биоматериал'), width: 100},
                {name: 'EvnLabSample_BarCode', header: langs('Штрих-код'), width: 110,
                    filter: win.LabSampleGrid_BarcodeFilter,
                    renderer: function(value, cellEl, rec) {
                        var result = "";
                        if (!Ext.isEmpty(value)) {
                            result = result + "<a href='javascript://' onClick='Ext.getCmp(\"swAssistantWorkPlaceWindow\").showInputBarCodeField(\"lsbarcode"+rec.get('EvnLabSample_id')+"\","+rec.get('EvnLabSample_id')+",this);'>"+value+"</a>";
                        }

                        return "<div id='lsbarcode"+rec.get('EvnLabSample_id')+"_inp'></div><div id='lsbarcode"+rec.get('EvnLabSample_id')+"'>" + result + "</div>";
                    }
                },
                {name: 'Analyzer_id', hidden: true},
                {name: 'MedService_id', hidden: true},
                {name: 'EvnDirection_id', hidden: true},
                {name: 'UslugaComplexTarget_id', hidden: true},
                {name: 'AnalyzerWorksheetEvnLabSample_id', hidden: true},
                {name: 'MedService_Nick',hidden: true},
                {name: 'EvnLabSample_setDT', sort: true, type:'timedate', header: langs('Время взятия пробы'), width: 110},
                {name: 'EvnLabSample_Tests', headerAlign: 'left', align: 'center', width: 35, header: langs('Тесты'), type: 'int'},
                {name: 'EvnLabSample_IsOutNorm', headerAlign: 'left', align: 'center', width: 25,
                    renderer:function(x,c,rec) {
                        if (rec.get('EvnLabSample_IsOutNorm') == 2) {
                            return "<img src='../img/icons/warning16.png'/>"
                        } else {
                            return "";
                        }
                    }, filterStyle: "text-align: center;",
                    filter:new Ext.form.Checkbox({
                        autoLoad: true,
                        hiddenName:'EvnLabSample_IsOutNorm',
                        name:'EvnLabSample_IsOutNorm',
                        enableKeyEvents: true,
                        width:100
                    }), header: langs('Откл-е')
                },
                {name: 'EvnDirection_Num', header: langs('№ напр.'), width: 55,
                    filter: new Ext.form.TextField({
                        enableKeyEvents: true,
                        name:'EvnDirection_Num'
                    })
                },
                {name: 'PrehospDirect_Name', header: langs('Кем направлен'), width: 100, hidden: true,
					filter: new Ext.form.TextField({
						enableKeyEvents: true,
						name:'PrehospDirect_Name'
					})
                },
				{name: 'Lpu_Nick', header:langs('Медицинская организация'), width: 150,
					filter: new LpuFilter({
						childs: [ labLpuSectionFilterCombo ]
					})
				},
				{name: 'LpuSection_Name', header:langs('Отделение направления'), width: 150,
					filter: labLpuSectionFilterCombo
				},
				{name: 'EDMedPersonalSurname', header:langs('Фамилия врача'), width: 150,
					filter: labMedStaffFactFilterCombo
				},
				{name: 'EvnLabRequest_RegNum', header:langs('Регистрационный номер'), width: 100,
					filter: new Ext.form.TextField({
						enableKeyEvents: true,
						name: 'EvnLabRequest_RegNum'
					})
				},
				{name: 'EvnLabRequest_UslugaName', header: langs('Услуга (исследование)'), width: 150, id: 'autoexpand',
					renderer: uslugaNameRendererFn,
					filter: new UslugaFilter()
				},
				{name: 'Analyzer_2wayComm', hidden: true},
                {name: 'Analyzer_Name',
                    id:win.id+'AnalyzerComboField',
                    editor:new sw.Promed.SwAnalyzerCombo({
                    id: win.id + '_AnalyzerCombo',
                    listeners: {
                        'select': function(combo, record) {
                            combo.setValue(record.get('Analyzer_id'));
                            combo.fireEvent('blur', combo);
                        },
                        'blur': function(combo) {
                            win.LabSampleGridPanel.getGrid().stopEditing();
                        }
                    },
                    listWidth: 300
                }), header: langs('Анализатор'), width: 100},
                {name: 'Person_ShortFio', header: '<div style="height:16px;"><div style="float:left;">Фамилия И.О.</div> <a title="Считать с карты" href="#" onClick="getWnd(\'swAssistantWorkPlaceWindow\').readFromCard();"><img style="float:right; margin-right: 10px; vertical-align: bottom;" src="/img/icons/idcard16.png" /></a>',  width: 126,
                    filter: new Ext.form.TextField({
                        enableKeyEvents: true,
                        name:'Person_ShortFio'
                    })
                },
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения') , width: 100},
                {name: 'lis_id', hidden: true, header: 'LisId' },
                {name: 'LpuSection_Code', type: 'string', hidden: true},
                win.filterRowLab
            ],
            //groupSortInfo: {
            //    field: 'EvnLabSample_id',
            //    direction:'ASC'
            //},
            clicksToEdit: 1,
            onBeforeEdit: function(o) {
                if (o.field && o.field == 'Analyzer_Name' && o.record) {
                    var combo = Ext.getCmp(win.id + '_AnalyzerCombo');
                    combo.getStore().removeAll();
                    combo.getStore().load({
                        params: {
                            EvnLabSample_id: o.record.get('EvnLabSample_id'),
                            MedService_id: o.record.get('MedService_id'),
                            Analyzer_IsNotActive: 1
                        }
                    });
                }

                return o;
            },
            onAfterEdit: function(o) {
                o.grid.stopEditing(true);

                if (o.field && o.field == 'Analyzer_Name' && o.record) {
                    var combo = Ext.getCmp(win.id + '_AnalyzerCombo');
                    o.record.set('Analyzer_Name', o.rawvalue);
                    o.record.set('Analyzer_id', combo.getValue());
                    o.record.commit();
                    win.getLoadMask(langs('Изменение анализатора')).show();
                    // обновить на стороне сервера
                    Ext.Ajax.request({
                        url: '/?c=EvnLabSample&m=saveLabSampleAnalyzer',
                        params: {
                            Analyzer_id: combo.getValue(),
                            EvnLabSample_id: o.record.get('EvnLabSample_id')
                        },
                        callback: function(options, success, response) {
                            win.getLoadMask().hide();
                            if(success) {
                                win.LabSampleGridPanel.getGrid().getStore().reload();
                            }
                        }
                    });
                }
            },
            actions:[
                {name:'action_add', text: langs('Добавить'), handler: function() {
					if ( !win.addWithoutRegIsAllowed() ) return;
					this.openLabRequestEditWindow('add'); }.createDelegate(this)
				},
                {name:'action_edit', handler: function () { win.openEvnLabSampleEditWindow('edit'); } },
                {name:'action_view', handler: function () { win.openEvnLabSampleEditWindow('view'); } },
                {
                    name:'action_delete',
                    text: langs('Отменить'),
                    handler: function (){
                        var records = win.LabSampleGridPanel.getGrid().getSelectionModel().getSelections();
                        var EvnLabSample_ids = [];
                        for (var i = 0; i < records.length; i++) {
                            if (!Ext.isEmpty(records[i].get('EvnLabSample_id')) && records[i].get('EvnLabSample_id') > 0) {
                                EvnLabSample_ids = EvnLabSample_ids.concat(records[i].get('EvnLabSample_id').toString());
                            }
                        }

                        if (!Ext.isEmpty(EvnLabSample_ids) && EvnLabSample_ids.length > 0) {
                            Ext.Msg.show({
                                title: langs('Отмена проб'),
                                msg: langs('Вы действительно хотите отменить выбранные пробы?'),
                                buttons: Ext.Msg.YESNO,
                                fn: function(btn) {
                                    if (btn === 'yes') {
                                        win.getLoadMask("Отмена проб...").show();
                                        Ext.Ajax.request({
                                            params: {
                                                EvnLabSample_ids: Ext.util.JSON.encode(EvnLabSample_ids)
                                            },
                                            url: '/?c=EvnLabSample&m=cancel',
                                            callback: function(options, success, response) {
                                                win.getLoadMask().hide();
                                                if(success) {
                                                    win.LabSampleGridPanel.loadData();

                                                }
                                            }
                                        });
                                    }
                                },
                                icon: Ext.MessageBox.QUESTION
                            });
                        }
                    }
                },
                {name:'action_refresh', handler: function() {
                    win.LabSampleGridPanel.getGrid().getStore().reload();
                    win.focusOnGrid();
                }},
                {name:'action_save', hidden: true, disabled: true},
                {name:'action_print'}
            ],
            onLoadData: function() {
				win.LabSampleGridExpander.state = {};
                win.addGridFilter();
                win.focusOnGrid();

				sm = this.getGrid().getSelectionModel();
				win.loadPathologySamples();
            },
            isSend2AnalyzerEnabled: function() {
                var selections = this.getGrid().getSelectionModel().getSelections();
                var ArrayId = [];

                for (var key in selections) {
                    if (selections[key].data) {
                        ArrayId.push(selections[key].data['EvnLabSample_id'].toString());
                    }
                }

                Ext.Ajax.request({
                    url: '/?c=AsMlo&m=isSend2AnalyzerEnabled',
                    params: {
                        EvnLabSamples: Ext.util.JSON.encode(ArrayId),
                        MedService_id: win.MedService_id
                    },
                    callback: function(options, success, response) {
                        console.log('response:');
                        sw.Promed.vac.utils.consoleLog(response);
                        console.log(success);
                        if (success && response.responseText != '') {
                            var result  = Ext.util.JSON.decode(response.responseText);
                            console.log("Count2wayOnService:");
                            console.log(result[0].Count2wayOnService);
                            console.log("CountNo2wayOnSamples:");
                            console.log(result[0].CountNo2wayOnSamples);
                            var btnSend2AnalyzerEnabled = 1;
                            if (result[0].Count2wayOnService < 1) {
                                //На службе нет анализаторов с двусторонней связью
                                btnSend2AnalyzerEnabled = 0;
                            }

                            if (result[0].CountNo2wayOnSamples > 0) {
                                //К заявкам не привязаны анализаторы, либо они не имеют режима двусторонней связи
                                btnSend2AnalyzerEnabled = 0;
                            }

                            if ( btnSend2AnalyzerEnabled ) {//активируем кнопку
                                win.LabSampleGridPanel.getAction('action_lis_create').setDisabled(false);
                            } else {//деактивируем кнопку
                                win.LabSampleGridPanel.getAction('action_lis_create').setDisabled(true);
                            }
                        }
                    }
                });
            },
            onRowDeSelect: function(sm,rowIdx,record) {
                //win.LabSampleGridPanel.isSend2AnalyzerEnabled(); //активация/деактивация кнопки "Отправить на анализатор"

                var row = this.getGrid().view.getRow(rowIdx);
                Ext.fly(row).replaceClass("x-grid-state-prob-"+record.get('LabSampleStatus_id')+"-selected","x-grid-state-prob-"+record.get('LabSampleStatus_id'));
            },
            onRowSelect: function(sm,rowIdx,record) {
                //win.LabSampleGridPanel.isSend2AnalyzerEnabled(); //активация/деактивация кнопки "Отправить на анализатор"

                var row = this.getGrid().view.getRow(rowIdx);
                Ext.fly(row).replaceClass("x-grid-state-prob-"+record.get('LabSampleStatus_id'),"x-grid-state-prob-"+record.get('LabSampleStatus_id')+"-selected");
            },
            onMultiSelectionChangeAdvanced: function(sm) {
                let disableDel = true;
                let disableAll = true;
				let disableApprove = true;
				let disablePrintBarCodes = true;
				let disableCheckSample = true;

                if (sm.getCount() > 0) {
                    disableAll = false;
					disableDel = false;
                    disablePrintBarCodes = false;
					disableCheckSample = false;
                    // идём по выделенным и смотрим можно ли их удалять
                    var records = this.getGrid().getSelectionModel().getSelections();
                    for (var i = 0; i < records.length; i++) {
                        disableApprove = false;//disableApprove && (records[i].get('EvnLabSample_IsOutNorm') == 2);
						disableDel = disableDel || (records[i].get('LabSampleStatus_id').inlist([3,4,6]));
                        //Вырубаем кнопку проверить результат если проба со статусом новая или забракована
						disableCheckSample = disableCheckSample || (records[i].get('LabSampleStatus_id').inlist([1,5]));
                    }
                }

                this.ViewActions.action_print.menu['action_print_barcodes'].setDisabled(disablePrintBarCodes);
                this.getAction('action_delete').setDisabled(disableDel);
                this.getAction('action_lis_sample').setDisabled(disableCheckSample);
				//disableApprove = disableApprove || win.MedServiceMedPersonal_isNotApproveRights;
                this.getAction('action_lis_approve').setDisabled(disableApprove);
                this.getAction('action_lis_create').setDisabled(disableAll);
                this.getAction('action_outsourcing_create').setDisabled(disableAll);
                this.getAction('action_lis_selectanalyzer').setDisabled(disableAll);
            }
        });

        this.LabSampleGridPanel.getGrid().view.getRowClass = function (row, index)
        {
            var cls = 'x-grid-state-prob-'+row.get('LabSampleStatus_id');
            return cls;
        };

        this.LabSampleGridPanel.getGrid().getColumnModel().isCellEditable = function(colIndex, rowIndex) {
            if (this.config[colIndex].editable || (typeof this.config[colIndex].editable == "undefined" && this.config[colIndex].editor)) {
                var grid = win.LabSampleGridPanel.getGrid();
                var store = grid.getStore();
                var record = store.getAt(rowIndex);

                if (!record || !Ext.isEmpty(record.get('AnalyzerWorksheetEvnLabSample_id'))) {
                    return false;
                }

                return true;
            }

            return false;
        };

        this.LabSampleGridPanel.ViewToolbar.on('render', function(vt){
            this.ViewActions.action_print.menu['action_print_barcodes']= new Ext.Action({name:'action_print_barcodes', text: langs('Печать штрих-кодов'), handler: function () {
                if (getGlobalOptions().region.nick == 'ufa') {
                    switch(Number(Ext.globalOptions.lis.barcode_print_method))
                    {
                        // JS
                        case 1:
                            win.JsBarcode('LabSampleGridPanel');
                        break;

                        // PDF
                        case 2:
                            win.printLabSampleBarcodes();
                        break;

                        // JAVA
                        case 3:
                            win.printonZebra('LabSampleGridPanel');
                        break;

                        case 4:
                            win.zebraBrowserPrint('LabSampleGridPanel');
                        break;

                        default:
                            sw.swMsg.alert(langs('Ошибка'), 'Выберите метод печати');
                        break;
                    }
                } else {
                    win.printLabSampleBarcodes();
                }
            }});

            this.getAction('action_print').menu.printObject.hide();
        },this.LabSampleGridPanel);

        this.buttonPanelActions.action_Report = { //http://redmine.swan.perm.ru/issues/18509
            nn: 'action_Report',
                tooltip: langs('Просмотр отчетов'),
                text: langs('Просмотр отчетов'),
                iconCls: 'report32',
                //hidden: !this.enableDefaultActions,//( !document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
                handler: function() {
                if (sw.codeInfo.loadEngineReports)
                {
                    getWnd('swReportEndUserWindow').show();
                }
                else
                {
                    getWnd('reports').load({
                        callback: function(success)
                        {
                            sw.codeInfo.loadEngineReports = success;
                            // здесь можно проверять только успешную загрузку
                            getWnd('swReportEndUserWindow').show();
                        }
                    });
                }
            }
        };

        this.createFormActions();

        this.LeftPanel = new sw.Promed.BaseWorkPlaceButtonsPanel({
            animCollapse: false,
            width: 60,
            minSize: 60,
            maxSize: 120,
            region: 'west',
            floatable: false,
            collapsed: true,
            collapsible: true,
            id: win.id + '_buttPanel',
            layoutConfig:
            {
                titleCollapse: true,
                animate: true,
                activeOnTop: false
            },
            listeners:
            {
                collapse: function()
                {
                    return;
                },
                resize: function (p,nW, nH, oW, oH)
                {
                    var el = null;
                    el = win.findById(win.id + '_buttPanel_slid');
                    if(el)
                        el.setHeight(this.body.dom.clientHeight-42);

                    return;
                }

            },
            border: true,
            title: ' ',
            titleCollapse: true,
            enableDefaultActions: (typeof win.enableDefaultActions == 'boolean')?win.enableDefaultActions:true,
            panelActions: win.buttonPanelActions
        });

        this.WindowToolbar = new Ext.Toolbar({
            id:'WindowToolbarLis',
            items: [
                this.formActions.modeLabRequest,
                this.formActions.modeLabSample,

				{ xtype: 'tbfill' },

				this.formActions.methodsIFALabel,
				this.formActions.methodsIFA,
				this.formActions.analyzerTestIFALabel,
				this.formActions.analyzerTestIFA,

				{ xtype: 'tbfill' },

                this.formActions.prev,
                this.labelNoFilter,
                this.dateMenu,

                this.formActions.next,

                this.formActions.day,
                this.formActions.week,
                this.formActions.month,
                this.formActions.range,

				this.formActions.formLab,
				this.formActions.formIfaLab
            ]
        });

        this.LabRequestTabPanel = new Ext.TabPanel({
            region: 'north',
            border: false,
            id: win.id+'LabRequestTabPanel',
            items:
            [{
                title: "<div class='tab_title'>Все заявки</div> <div class='tab_title_count'></div>",
                id: 'tab_all'
            },
            {
                title: "<div class='tab_title'>Новые заявки</div> <div class='tab_title_count lrstate1'></div>",
                id: 'tab_new'
            },
            {
                title: "<div class='tab_title'>В работе</div> <div class='tab_title_count lrstate3'></div>",
                id: 'tab_work'
            },
            {
                title: "<div class='tab_title'>"+(getRegionNick() == 'ufa' ? 'Выполненные' : 'С результатами')+"</div> <div class='tab_title_count lrstate4'></div>",
                id: 'tab_done'
            },
            {
                title: "<div class='tab_title'>Одобренные</div> <div class='tab_title_count lrstate5'></div>",
                id: 'tab_approved'
            },
            {
                title: "<div class='tab_title'>Невыполненные</div> <div class='tab_title_count lrstate6'></div>",
                id: 'tab_notdone'
			}, 
			{
				title: [
					'<span title="Новые заявки" class="tab_title_count_fp lrstate1">',
					'<span title="В работе" class="tab_title_count_fp lrstate3">',
					'<span title=' + (getRegionNick() == 'ufa' ? 'Выполненные' : 'С результатами') + ' class="tab_title_count_fp lrstate4">',
					'<span title="Одобренные" class="tab_title_count_fp lrstate5">',
					'<span title="Невыполненные" class="tab_title_count_fp lrstate6">'
				].join('</span>\n'),
				id: 'tab_count_fp',
				hidden: true,
				disabled: true
			}],
            listeners:
            {
                tabchange: function(tab, panel)
                {
                    win.doSearch();
                }
            }
        });

        this.ElectronicQueuePanel = new sw.Promed.ElectronicQueuePanel({
            ownerWindow: win,
            ownerGrid: win.GridPanel.getGrid(), // передаем грид для работы с ЭО
            gridPanel: win.GridPanel, // свяжем так же грид панель
            applyCallActionFn: function(){ win.openLabRequestEditWindow('edit') }, // передаем то что будет отрываться при на жатии на принять
            region: 'south',
            refreshTimer: 30000
        });

        this.LabRequestPanel = new sw.Promed.Panel({
            region: 'center',
            border: false,
            layout: 'border',
            items: [
                this.LabRequestTabPanel,
                this.GridPanel,
                this.ElectronicQueuePanel
            ]
        });

        this.LabSampleTabPanel = new Ext.TabPanel({
            region: 'north',
            border: false,
            id: win.id+'LabSampleTabPanel',
            items:
            [{
                title: "<div class='tab_title'>Все пробы</div> <div class='tab_title_count'></div>",
                id: 'tab_all'
            },
            {
                title: "<div class='tab_title'>Новые пробы</div> <div class='tab_title_count lsstate1'></div>",
                id: 'tab_new'
            },
            {
                title: "<div class='tab_title'>В работе</div> <div class='tab_title_count lsstate2'></div>",
                id: 'tab_work'
            },
            {
                title: "<div class='tab_title'>"+(getRegionNick() == 'ufa' ? 'Выполненные' : 'С результатами')+"</div> <div class='tab_title_count lsstate3'></div>",
                id: 'tab_done'
            },
            {
                title: "<div class='tab_title'>Одобренные</div> <div class='tab_title_count lsstate4'></div>",
                id: 'tab_approved'
            },
            {
                title: "<div class='tab_title'>Забракованные</div> <div class='tab_title_count lsstate5'></div>",
                id: 'tab_defect'
            }],
            listeners:
            {
                tabchange: function(tab, panel)
                {
                    win.addGridFilter(true);
                    // win.doSearch();
                }
            }
        });

        this.LabSamplePanel = new sw.Promed.Panel({
            region: 'center',
            border: false,
            layout: 'border',
            items: [
                this.LabSampleTabPanel,
                this.LabSampleGridPanel
            ]
        });

        this.WorkPanel = new sw.Promed.Panel({
            region: 'center',
            border: false,
            layout: 'card',
            activeItem: 0,
            items: [this.LabRequestPanel, this.LabSamplePanel]
        });

		win.tabletGridExpander = new Ext.ux.grid.RowExpander({
			tpl: '<div class="ux-row-expander-box"><div>',
			treeLeafProperty: 'is_leaf',
			fixed: true,
			listeners: {
				expand: function(expander, record, body, rowIndex) {
					let grid = this.grid,
						view = grid.getView();
					view.focusRow(rowIndex);
					grid.getSelectionModel().selectRow(rowIndex);
					win.tabletGrid.Tablet_id = record.get('Tablet_id');
					win.getTabletPanel(record, Ext.get(view.getRow(rowIndex)).child('.ux-row-expander-box'));
				}
			}
		});

		win.tabletFilterRow = new Ext.ux.grid.FilterRow({
			id: 'tabletFilterRow',
			parId:win.id,
			listeners: {
				'search':function(params){

					let tabletFilterFn = function(rec) {
						let flag = true;
						if(params.Tablet_Barcode) {
							flag &= rec.get('Tablet_Barcode').includes(params.Tablet_Barcode);
						}
						if(params.MethodsIFA_id) {
							flag &= rec.get('MethodsIFA_id') == params.MethodsIFA_id;
						}
						return flag;
					};
					win.tabletGrid.getGrid().getStore().filterBy(tabletFilterFn);
				}
			}
		});

		win.tabletGrid = new sw.Promed.ViewFrame({
			border: false,
			region: 'center',
			groups:false,
			autoLoadData: false,
			useEmptyRecord: false,
			gridplugins: [win.tabletGridExpander, win.tabletFilterRow ],
			id: win.id + 'TabletGridPanel',
			dataUrl: '/?c=Tablet&m=loadGrid',
			disField: 'Tablet_defectDT',
			actions: [
				{ name: 'action_print', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_edit', hidden: true },
				{
					name: 'action_refresh',
					handler: function() {
						delete win.tabletGrid.Tablet_id;
						win.tabletGrid.loadData();
					}
				}
			],
			stringfields: [
				win.tabletGridExpander,
				{ type: 'int', name: 'Tablet_id',  header: 'ID', key: true },
				{ type: 'int', name: 'Tablet_VertSize', hidden: true },
				{ type: 'int', name: 'Tablet_HorizSize', hidden: true },
				{ type: 'int', name: 'Tablet_HoleCount', hidden: true },
				{ type: 'int', name: 'Tablet_IsHorizFill', hidden: true },
				{ type: 'int', name: 'Tablet_IsDoublesFill', hidden: true },
				{ type: 'int', name: 'MethodsIFA_id', hidden: true },
				{ name: 'Tablet_Barcode', header: 'Штрих код планшета', width: 100,
					filter: new Ext.form.TextField({
						enableKeyEvents: true,
						name:'Tablet_Barcode',
						listeners: {
							keyup: function() {
								win.tabletFilterRow._search();
							}
						}
					})
				},
				{ name: 'MethodsIFA_Name', header: 'Методика', id: 'autoexpand',
					filter: new sw.Promed.SwBaseLocalCombo({
						name: 'MethodsIFA_id',
						valueField: 'MethodsIFA_id',
						displayField: 'MethodsIFA_Name',
						enableKeyEvents: true,
						store: new Ext.data.JsonStore({
							autoLoad: true,
							url: '/?c=MethodsIFA&m=loadCombo',
							fields: [
								{ type: 'int', name: 'MethodsIFA_id' },
								{ type: 'int', name: 'FIRMS_id' },
								{ type: 'string', name: 'MethodsIFA_Name' },
								{ type: 'string', name: 'MethodsIFA_Code' }
							]
						}),
						listeners:{
							valid: function() {
								win.tabletFilterRow._search();
							}
						}
					})
				},
				{ name: 'emptyHolesCount', header: 'Количество свободных ячеек',
					renderer: function(v,p,rec) {
						if(!rec) return;
						return rec.get('statusName') == 'В работе' ? rec.get('emptyHolesCount') : '-';
					}
				},
				{ name: 'statusName', header: 'Статус' },
				{ type: 'string', name: 'Tablet_defectDT', hidden: true },
				win.tabletFilterRow
			],
			onRowSelect: function(sm,rowIdx,record) {
				if(!record) return;
				let editDisabled = record.get('statusName') !== "Новый";
				this.actionEdit.setDisabled(editDisabled);
				this.actionDelete.setDisabled(editDisabled);

				let defectDisabled = Boolean(record.get('Tablet_defectDT'));
				this.actionDisable.setDisabled(defectDisabled);
			},
			function_action_add: function() {
				var addWin = getWnd('swTabletWindow'),
					params = {
						action: 'add',
						MedService_id: win.MedService_id,
						callback: function (Tablet_id) {
							win.tabletGrid.Tablet_id = Tablet_id;
							win.tabletGrid.loadData();
						}
					};
				addWin.show(params);
			},
			onEnter: function() {
				win.tabletGrid.toogleTabletPanel();
			},
			onDblClick: function() {
				win.tabletGrid.toogleTabletPanel();
			},
			toogleTabletPanel: function() {
				let grid = this.getGrid(),
					rowIndex = this.getSelectedIndex();
				if(!rowIndex) return;
				let row = grid.getView().getRow(rowIndex);
				win.tabletGridExpander.toggleRow(row);
			},
			refreshTabletPanel: function(Tablet_id) {
				let viewframe = this;

				Tablet_id = Tablet_id || viewframe.Tablet_id;

				let grid = viewframe.getGrid(),
					store = grid.getStore(),
					view = grid.getView(),
					rowIndex = store.findBy(function(rec) {
						return rec.get('Tablet_id') == Tablet_id;
					});
				if(rowIndex == -1) return;
				let row = view.getRow(rowIndex);
				view.focusRow(rowIndex);
				grid.getSelectionModel().selectRow(rowIndex);
				win.tabletGridExpander.collapseRow(row);
				win.tabletGridExpander.expandRow(row);
			},
			checkBeforeLoadData: function() {
				return Boolean(this.getParam('MedService_id'));
			},
			onBeforeLoadData: function() {
				win.tabletGridExpander.state = {};
			},
			onLoadData: function(loaded) {
				let viewframe = this;
				win.tabletPanelTabs.setTabletTabTitle();
				if(!loaded) {
					this.actionEdit.disable();
					this.actionDelete.disable();
					this.actionDisable.disable();
				} else {
					viewframe.refreshTabletPanel();
				}
			}
		});

		win.tabletGrid.actionEdit = new Ext.Action({
			name: 'action_edit',
			disabled: true,
			text: langs('Изменить'),
			handler: function() {
				let rec = win.tabletGrid.getGrid().getSelectionModel().getSelected();
				if(!rec) {
					sw.swMsg.alert('Сообщение', 'Не выбрана запись');
					return;
				}
				let Tablet_id = rec.get('Tablet_id');
				let addWin = getWnd('swTabletWindow'),
					params = {
						action: 'edit',
						MedService_id: win.MedService_id,
						Tablet_id: Tablet_id,
						callback: function (Tablet_id) {
							win.tabletGrid.Tablet_id = Tablet_id;
							win.tabletGrid.loadData();
						}
					};
				addWin.show(params);
			}
		});

		win.tabletGrid.actionDelete = new Ext.Action({
			name: 'action_delete',
			disabled: true,
			text: langs('Удалить'),
			handler: function() {
				let viewframe = win.tabletGrid,
					rec = viewframe.getGrid().getSelectionModel().getSelected();
				if(!rec) {
					sw.swMsg.alert('Сообщение', 'Не выбрана запись');
					return;
				}
				let fnDel = function(btn) {
					if(btn != 'yes') return;
					Ext.Ajax.request({
						url: '/?c=Tablet&m=doDelete',
						params: { Tablet_id: rec.get('Tablet_id') },
						success: function () {
							delete viewframe.Tablet_id;
							viewframe.loadData();
						}
					})
				};
				sw.swMsg.confirm('Сообщение', 'Удалить выбранный планшет?', fnDel);
			}
		});

		win.tabletGrid.actionDisable = new Ext.Action({
			name: 'action_disable',
			text: langs('Забраковать'),
			handler: function() {
				let rec = win.tabletGrid.getGrid().getSelectionModel().getSelected();
				if(!rec) {
					sw.swMsg.alert('Сообщение', 'Не выбрана запись');
					return;
				}
				win.defectHole(rec.get('Tablet_id'), null, 'Tablet');
			}
		});

		win.tabletGrid.actionPrint = new Ext.Action({
			name: 'action_print',
			text: langs('Печать'),
			handler: function() {
				let rec = win.tabletGrid.getGrid().getSelectionModel().getSelected();
				if(!rec) {
					sw.swMsg.alert('Не выбрана запись');
					return;
				}

				let fileName = "AnalyzerTablet";
				if(Ext.globalOptions.lis.use_postgresql_lis) {
					fileName += '_pg';
				}

				let params = {
					Report_Format: 'pdf',
					Report_FileName: fileName + '.rptdesign',
					Report_Params: '&Tablet_id='+rec.get('Tablet_id')
				};
				printBirt(params);
			}
		});

		win.tabletGrid.actionCreateChild = new Ext.Action({
			name: 'action_create_child',
			text: langs('Копировать планшет'),
			handler: function() {
				let rec = win.tabletGrid.getGrid().getSelectionModel().getSelected();
				if(!rec) {
					sw.swMsg.alert('Не выбрана запись');
					return;
				}

				Ext.Ajax.request({
					url: '/?c=Tablet&m=createChild',
					params: { Tablet_id: rec.get('Tablet_id') },
					success: function () {
						win.tabletGrid.loadData();
					}
				})
			}
		});

		win.tabletGrid.getGrid().view.getRowClass = function (row, index) {
			let cls = "";
			if(row.get(win.tabletGrid.disField)) {
				cls = cls+'x-grid-rowgray ';
			}
			return cls;
		};

		win.tabletPanelTabs = new Ext.TabPanel({
			region: 'north',
			id: win.id+'TabletTabPanel',
			activeTab: 0,
			defaults: {
				setTabletCount: function(count) {
					this.setTitle(win.getTabletPanelTabTitle(this.titleText, this.titleCls, count));
				}
			},
			items:
			[
				{
					name: 'all',
					titleText: 'Все',
					titleCls: '',
					title: win.getTabletPanelTabTitle('Все', '', 0)
				},
				{
					name: 'work',
					titleText: 'В работе',
					titleCls: 'lrstate3',
					title: win.getTabletPanelTabTitle('В работе', 'lrstate3', 0)
				},
				{
					name: 'result',
					titleText: 'С результатами',
					titleCls: 'lrstate4',
					title: win.getTabletPanelTabTitle('С результатами', 'lrstate4', 0)
				},
				{
					name: 'accepted',
					titleText: 'Одобренные',
					titleCls: 'lsstate4',
					title: win.getTabletPanelTabTitle('Одобренные', 'lsstate4', 0)
				},
				{
					name: 'defect',
					titleText: 'Забракованные',
					titleCls: 'lrstate6',
					title: win.getTabletPanelTabTitle('Забракованные', 'lrstate6', 0)
				}
			],
			listeners:
			{
				tabchange: function(tab, panel) {
					win.tabletGrid.setParam('tabletStatus', panel.name);
					win.tabletGrid.loadData();
				}
			},
			getTabByName: function(name) {
				let panels = this.findBy(function(el) {
					return el.name == name
				});
				return panels[0];
			},
			getTabTitle: function(text, cls, count) {
				if(!cls) cls = '';
				if(count == null) count = '';
				return "<div class='tab_title'>" + text + "</div> <div class='tab_title_count " + cls + "'>"+count+"</div>";
			},
			setTabletTabTitle: function() {
				let tabbar = this,
					panel = tabbar.getActiveTab(),
					tabletStore = win.tabletGrid.getGrid().getStore(),
					newCount = acceptedCount = allCount = workCount = resultCount = defectCount = 0;

				panel.setTabletCount(tabletStore.getCount());

				if(panel.name != 'all') {
					return;
				};

				let calcCount = function(rec) {
					let status = rec.get('statusName');
					switch (status) {
						case 'Новый':
							newCount++;
							break;
						case 'В работе':
							workCount++;
							break;
						case 'Выполнен':
							resultCount++;
							break;
						case 'Одобрен':
							acceptedCount++;
							break;
						case 'Забракован':
							defectCount++;
							break;
					}
				};
				tabletStore.each(calcCount);
				let panelWork = tabbar.getTabByName('work'),
					panelDefect = tabbar.getTabByName('defect'),
					panelResult = tabbar.getTabByName('result'),
					panelAccepted = tabbar.getTabByName('accepted');
				panelWork.setTabletCount(workCount);
				panelDefect.setTabletCount(defectCount);
				panelResult.setTabletCount(resultCount);
				panelAccepted.setTabletCount(acceptedCount);
			}
		});

		win.tabletPanel = new Ext.Panel({
			split: true,
			hidden: true,
			title: 'Планшеты',
			region: 'east',
			layout: 'border',
			width: 650,
			bodyStyle:'width:100%;background:#DFE8F6;padding:1px;padding-top:4px;',
			items: [
				win.tabletPanelTabs,
				win.tabletGrid
			]
		});

        this.CenterPanel = new sw.Promed.Panel({
            region: 'center',
            listeners:{
                render:function(panel) {
                    panel.el.on('keyup', function(e) {
                        var key = e.getCharCode();
                        if (key == 9) {
                            return;
                        }
                        var pressed = String.fromCharCode(key);
                        var alowed_chars = ['0','1','2','3','4','5','6','7','8','9'];
                        if ((pressed != '') && (alowed_chars.indexOf(pressed) >= 0)) {
                            win.gridKeyboardInputSequence++;
                            var s = win.gridKeyboardInputSequence;
                            win.gridKeyboardInput = win.gridKeyboardInput + pressed;
                            setTimeout(function () {
                                win.resetGridKeyboardInput(s);
                            }, 500);
                        }

                        if ( e.getKey() == e.DELETE && win.barCodeIsFocused) {
                            forceError;
                        }
                    });
                }
            },
            border: false,
            layout: 'border',
            items: [this.LeftPanel, this.WorkPanel, win.tabletPanel]
        });

        Ext.apply(this, {
            layout: 'border',
            items: [
                this.CenterPanel
            ],
            tbar: this.WindowToolbar,
            keys:
            [{
                fn: function(inp, e)
                {
                    switch (e.getKey())
                    {
                        case Ext.EventObject.F5:
                            win.doSearch();
                        break;
                    }
                },
                key: [ Ext.EventObject.F5 ],
                scope: this,
                stopEvent: true
            }]
        });

        sw.Promed.swAssistantWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
    },

	getTabletPanelTabTitle: function(text, cls, count) {
		if(!cls) cls = '';
		if(count == null) count = '';
		return "<div class='tab_title'>" + text + "</div> <div class='tab_title_count " + cls + "'>"+count+"</div>";
	},

	zebraBrowserPrint: function(panel) {
		if(panel == 'GridPanel') {
			this.getDataFromPanelGrid();
		} else if( panel == 'LabSampleGridPanel') {
			this.getDataFromLabSampleGrid();
		}
	},

	getDataFromPanelGrid: function() {

		var options = Ext.globalOptions.lis,
			tickets = [],
			records = this.GridPanel.getGrid().getSelectionModel().getSelections();

		if(options.ZebraUsluga_Name) {
			var labSamples = [];

			for(var i in records) {
				if(typeof(records[i]) != 'object') continue;

				var rec = records[i],
					EvnLabSample_ids = rec.get('EvnLabSample_ids');

				if(Ext.isEmpty(EvnLabSample_ids.trim())) continue;

				EvnLabSample_ids = EvnLabSample_ids.split([',']);
				labSamples = labSamples.concat(EvnLabSample_ids.map(Function.prototype.call, String.prototype.trim));
			}
			this.getLoadMask('Подготовка к печати').show();
			Ext.Ajax.request({
				url: '/?c=EvnLabSample&m=getSampleUsluga',
				params: { EvnLabSample_id: Ext.util.JSON.encode(labSamples) },
				callback: function(opt, success, response) {

					this.getLoadMask().hide();

					response = Ext.util.JSON.decode(response.responseText);

					var tickets = [],
						uslugaList = {},
						result = {};

					if(response.length <= 0) return;

					for(var i=0; i< response.length; ++i) {
						var usluga = response[i];
						uslugaList[usluga.EvnLabSample_id] = usluga.ResearchName;
					}

					for(var i in records) {

						if(typeof(records[i]) != 'object') continue;

						var rec = records[i],
							barcodes = rec.get('EvnLabRequest_FullBarCode'),
							sample_id = null;

						if(Ext.isEmpty(barcodes)) continue;
						barcodes = barcodes.trim().split(',');
						barcodes.forEach(function(code,k) {
						    if (code.trim()) {
								barcodes[k] = {
									sample_id: code.split(':')[0].trim(),
									barcode: code.split(':')[1].trim()
								};
                            }
						});

						for(j in barcodes){
							if(typeof(barcodes[j]) != 'object') continue;

							var data = Object();

							data.Barcode = barcodes[j].barcode;

							if(options.ZebraServicesName) {
								var medServices = rec.get('MedService_Nick');
								if(!Ext.isEmpty(medServices)) {
								    serviceList = medServices.split('<br />');

									if (serviceList.length > 1)
									    data.Service = (!Ext.isEmpty(serviceList[j])) ? serviceList[j].trim() : null;
									else data.Service = (!Ext.isEmpty(serviceList[0])) ? serviceList[0].trim() : null;
								} else {
									data.Service = getGlobalOptions().CurMedService_Name;
								}
							}

							if(options.ZebraFIO)
								data.FIO = rec.get('Person_ShortFio');

							if(options.ZebraDateOfBirth) {
								data.DateOfBirth = rec.get('Person_Birthday').format('d.m.Y');
							}

							if(options.ZebraDirect_Name)
								data.Direction = rec.get('PrehospDirect_Name');


							let uslugaName = uslugaList.hasOwnProperty(barcodes[j].sample_id) ? uslugaList[barcodes[j].sample_id] : null;
							if (!Ext.isEmpty(uslugaName)) {
								data.Usluga = uslugaName;
							}

							tickets.push(data);


						}
					}

					this.printZpl(tickets);
				}.createDelegate(this)
			});
		} else {
			for(i in records) {

			if(typeof(records[i]) != 'object') continue;

			var rec = records[i],
			    barcodes = rec.get('EvnLabRequest_FullBarCode')

			if(Ext.isEmpty(barcodes)) continue;

			barcodes = barcodes.split(',');
			barcodes.forEach(function(code,k) {
				barcodes[k] = code.split(':')[1];
			});

			for(j in barcodes){
				if(typeof(barcodes[j]) != 'string') continue;

				var data = Object();

				data.Barcode = barcodes[j];

				if(options.ZebraServicesName) {
					var medServices = rec.get('MedService_Nick');
					if(!Ext.isEmpty(medServices)) {
					    serviceList = medServices.split('<br />');

					    if (serviceList.length > 1)
							data.Service = (!Ext.isEmpty(serviceList[j])) ? serviceList[j].trim() : null;
						else data.Service = (!Ext.isEmpty(serviceList[0])) ? serviceList[0].trim() : null;
					} else {
						data.Service = getGlobalOptions().CurMedService_Name;
					}
				}

				if(options.ZebraFIO)
					data.FIO = rec.get('Person_ShortFio');

				if(options.ZebraDateOfBirth) {
					data.DateOfBirth = rec.get('Person_Birthday').format('d.m.Y');
				}

				if(options.ZebraDirect_Name)
					data.Direction = rec.get('PrehospDirect_Name');

				tickets.push(data);
			}
		}

			this.printZpl(tickets);
		}
	},

	getDataFromLabSampleGrid: function() {

		var records = this.LabSampleGridPanel.getGrid().getSelectionModel().getSelections(),
			options = Ext.globalOptions.lis;

		if(Ext.globalOptions.lis.ZebraUsluga_Name) {
			// 1. Создаем массив с выбранными пробами
			var labSample = [];
			for(var k in records) {
				if(typeof(records[k]) != 'object') continue;
				// печать только со штрихкодом
				if(records[k].get('EvnLabRequest_FullBarCode') === null)
					records.splice(k, 1);
				else
					labSample[k] = records[k].get('EvnLabSample_id');
			}

			this.getLoadMask('Подготовка к печати').show();
			var finishedFunc = function(opt, success, response) {
				this.getLoadMask().hide();
				response = Ext.util.JSON.decode(response.responseText);

				var tickets = [],
					uslugaList = {},
					result = {};

				if(response.length <= 0) return;

				// Условие: если > 1 услуги в одной пробе, то не печатаем

				response.map(function(item) {
					var itemPropertyName = item['EvnLabSample_id'];
					if (itemPropertyName in result) {
						delete result[itemPropertyName];
					} else {
						result[itemPropertyName] = item;
					}
				});

				var size = 0, key;
				for (key in result) {
					if (result.hasOwnProperty(key)) size++;
				}

				if(size <= 0) return;

				for(i=0; i<labSample.length; i++) {
					if(typeof result[labSample[i]] !== 'undefined' && labSample[i] == result[labSample[i]].EvnLabSample_id) {
						uslugaList[labSample[i]] = result[labSample[i]].ResearchName;
					} else {
						uslugaList[labSample[i]] = null;
					}
				}

				for(i in records) {

					if(typeof(records[i]) != 'object') continue;


					var data = Object(),
					    rec = records[i];

					data.Barcode = rec.get('EvnLabSample_BarCode');

					if(options.ZebraServicesName) {
						var medService = rec.get('MedService_Nick');
						if(!Ext.isEmpty(medService)) {
							data.Service = medService;
						} else {
							data.Service = getGlobalOptions().CurMedService_Name;
						}
					}

					if(options.ZebraFIO)
						data.FIO = rec.get('Person_ShortFio');

					if(options.ZebraDateOfBirth) {
					    data.DateOfBirth = rec.get('Person_Birthday').format('d.m.Y');
					}

					if(options.ZebraDirect_Name)
						data.Direction = rec.get('PrehospDirect_Name');

					var sample_id = rec.get('EvnLabSample_id');
					if(uslugaList.hasOwnProperty(sample_id)) {
						data.Usluga= uslugaList[sample_id];
					}

					tickets.push(data);

				}

				this.printZpl(tickets);

			}.createDelegate(this);

			// 2. Выбираем услуги в пробах
			Ext.Ajax.request({
				url: '/?c=EvnLabSample&m=getSampleUsluga',
				params: { EvnLabSample_id: Ext.util.JSON.encode(labSample) },
				callback: finishedFunc
			});
		}
		else {
			var tickets = [];
			for(i in records) {

				if(typeof(records[i]) != 'object') continue;

				var data = Object(),
					rec = records[i];

				data.Barcode = rec.get('EvnLabSample_BarCode');

				if(options.ZebraServicesName) {
					data.Service = rec.get('MedService_Nick') || getGlobalOptions().CurMedService_Name;
				}

				if(options.ZebraFIO)
				    data.FIO = rec.get('Person_ShortFio');

				if(options.ZebraDateOfBirth) {
				    data.DateOfBirth = rec.get('Person_Birthday').format('d.m.Y');
				}

				if(options.ZebraDirect_Name)
					data.Direction = rec.get('PrehospDirect_Name');

				tickets.push(data);

			}
			this.printZpl(tickets);
		}
	},

	getEncodedURIText: function(text) {
		return encodeURIComponent(text).split("%").join("_");
	},

	printZpl: function(ticketList) {

		var win = this,
			options = getLisBarcodeOptions(),
			font_height = parseInt(options.font_height),
			font_width = parseInt(options.font_width),
			ticket_height = parseInt(options.height),
			zpl = "",
			font = '^A@N,' + font_height + ',' + font_width + ',E:TT0003M_.TTF';

		var getZplTextField = function(text, posX, posY) {
			return font + '^FO' + posX + ',' + posY + '^FH^FD' + win.getEncodedURIText(text) + '^FS';
		};

		ticketList.forEach( function(ticket) {

			var posX = parseInt(options.text_posX),
				posY = parseInt(options.text_posY);

			var fieldList = [];


			if(ticket.Service) {
				fieldList.push( getZplTextField(ticket.Service, posX, posY ) );
				posY += font_height;
			}
			if(ticket.FIO && ticket.DateOfBirth)
			{
				fieldList.push( getZplTextField(ticket.FIO + ' ' + ticket.DateOfBirth,  posX, posY) );
				posY += font_height;
			} else {
				if(ticket.FIO) {
					fieldList.push( getZplTextField(ticket.FIO,  posX, posY) );
					posY += font_height;
				}

				if(ticket.DateOfBirth) {
					fieldList.push( getZplTextField(ticket.DateOfBirth,  posX, posY) );
					posY += font_height;
				}
			}
			if(ticket.Direction) {
				fieldList.push( getZplTextField(ticket.Direction,  posX, posY) );
				posY += font_height;
			}

			if(ticket.Usluga) {
				fieldList.push( getZplTextField(ticket.Usluga,  posX, posY) );
				posY += font_height;
			}

			var barcode_height = ticket_height - 30 - posY;


			zpl += '^XA ^CI28 ^LH0,0 ^PW' + options.width
				+ fieldList.join(' ')
				+ '^FO' + options.barcode_posX + ',' + posY
				+ '^BY' + options.barcode_size + '^BCN,' + barcode_height +',' + options.printNumber + ',N,N'
				+ '^FD>;' + ticket.Barcode + '^FS^XZ ';

		});

		if(Ext.isEmpty(options.printer)) {
			sw.swMsg.alert('Внимание','Выберите принтер (Сервис-Настройки-Лаборатория-Принтер)');
			return false;
		}

		ZebraPrintZpl(zpl, options.printer, options.printCount);

	},

	approveEvnLabSampleResults: function(params) {
		var win = this;
		if (Ext.util.JSON.decode(params.EvnLabSamples).length == 0) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: "Отсутствуют пробы для одобрения",
				title: langs('Одобрение результатов')
			});
			return;
		}

		win.getLoadMask(langs('Одобрение результатов')).show();
		Ext.Ajax.request({
			url: '/?c=EvnLabSample&m=approveEvnLabSampleResults',
			params: params,
			callback: function (opt, success, response) {
				var result = {};
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						win.LabSampleGridPanel.getGrid().getStore().reload();
						return;
					}
				}
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: result.Error_Msg,
					title: langs('Одобрение результатов')
				});
			}
		});
	},

	approveResults: function(params) {
		var win = this;
		if (Ext.util.JSON.decode(params.UslugaTest_ids).length == 0) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: "Отсутствуют тесты для одобрения",
				title: langs('Одобрение результатов')
			});
			return;
		}
		win.getLoadMask(langs('Одобрение результатов')).show();
		Ext.Ajax.request({
			url: '/?c=EvnLabSample&m=approveResults',
			params: params,
			callback: function (opt, success, response) {
				var result = {};
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						win.EvnUslugaDataGrid.getGrid().getStore().reload();
						win.LabSampleGridPanel.getGrid().getStore().reload();
						return;
					}
				}
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: result.Error_Msg,
					title: langs('Одобрение результатов')
				});
			}
		});
	},

	approveEvnLabRequestResults: function(params) {
		var win = this;
		if (Ext.util.JSON.decode(params.EvnLabRequests).length == 0) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: "Отсутствуют пробы для одобрения",
				title: langs('Одобрение результатов')
			});
			return;
		}

		win.getLoadMask(langs('Одобрение результатов')).show();

		Ext.Ajax.request({
			url: '/?c=EvnLabRequest&m=approveEvnLabRequestResults',
			params: params,
			callback: function(opt, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						win.GridPanel.getGrid().getStore().reload();
						win.LabSampleGridPanel.getGrid().getStore().reload({
								params: { MedService_id: win.MedService_id }
							}
						);
						return;
					}

				}
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: result.Error_Msg,
					title: langs('Одобрение результатов')
				});
			}
		});
	},

	loadPathologySamples: function() {
		var win = this;
		if (win.LabSampleGridPanel.getGrid().getStore().getCount() == 0) return;
		Ext.Ajax.request({
			url: '/?c=EvnLabSample&m=loadPathologySamples',
			callback: function(opt, success, response) {
				var result = JSON.parse(response.responseText);
				win.globalSampleList.normalSamples = result[0];
				win.globalSampleList.pathologySamples = result[1];
			},
			params: { EvnLabSample_id: win.LabSampleGridPanel.getGrid().getStore().data.keys.join(",") }
		});
	}
});
