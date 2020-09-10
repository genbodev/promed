/**
* swFunctions. Общие функции проекта PromedWeb для Башкирии
* @package      Libs
* @access       public
* @copyright    Copyright © 2009 НПК Прогресс.
* @version      23.09..2013
*/

//Заполнение полей индекса масмсы тела и  превышение нормы веса
//gaf 05022018 #106655
function setIMT(base_form) {
	//gaf 22112017 112144
	var pHeight = base_form.findField('PersonPregnancy_Height');
	var pWeight = base_form.findField('PersonPregnancy_Weight');
	var pim = 0;
	if (!Ext.isEmpty(pWeight.getValue()) && !Ext.isEmpty(pHeight.getValue())) {
		pim = pWeight.getValue() * 10000 / (pHeight.getValue() * pHeight.getValue())
	}
	//gaf 11122017
	if (pim != 'Infinity' && !isNaN(pim)) {
		base_form.findField('PersonPregnancy_IMT').setValue(Math.round(pim));
	} else {
		base_form.findField('PersonPregnancy_IMT').setValue('');
	}
	var pIsWeight25 = base_form.findField('PersonPregnancy_IsWeight25');
	if (Math.round(pim) > 30) {
		pIsWeight25.setValue(true);
	} else {
		//gaf 23112017
		pIsWeight25.setValue(false);
	}
}

//gaf 09022018 #106655
function setDisabledPregnancy(buttonobject, status) {
	var parent = buttonobject.el.parent('.x-fieldset-body');
	var childfieldset = parent.dom.children;
	var oobjnow = Ext.getCmp(childfieldset[1].id);

	if (getGlobalOptions().check_fullpregnancyanketa_allow && getGlobalOptions().check_fullpregnancyanketa_allow == '1') {
		//находим родителя
		var mainparent = oobjnow.ownerCt.ownerCt.ownerCt;
		//идекс текущего эелемента где клик
		var index = mainparent.items.keys.indexOf(oobjnow.ownerCt.ownerCt.id);
		//Смотрим количектво ITEMS наи нужно не 1!
		if (mainparent.items.items.length < 2) {
			mainparent = oobjnow.ownerCt.ownerCt.ownerCt.items.items[0];
			index = mainparent.items.keys.indexOf(oobjnow.ownerCt.id);

			if (buttonobject.QuestionType_Code == 643 || buttonobject.QuestionType_Code == 623) {
				//для предыдущих беременностей исключение
				var anketacategory = Ext.getCmp("swPersonPregnancyEditWindow").WizardPanel.getCategory("Anketa");
				var btnyes = anketacategory.findById("Button_625");
				if (btnyes){
					btnyes.setDisabled(status);
				}
				var btnno = anketacategory.findById("Button_645");
				if (btnno){
					btnno.setDisabled(status);
				}
			} else {
				if (buttonobject.QuestionType_Code == 642 || buttonobject.QuestionType_Code == 622)
				{
					index = index + 3;
				} else if (buttonobject.QuestionType_Code == 647 || buttonobject.QuestionType_Code == 656 || buttonobject.QuestionType_Code == 627 || buttonobject.QuestionType_Code == 636)
				{
					index = index + 2;
				} else {
					index = index + 1;
				}
				var nextItem = mainparent.items.items[index];
				if (typeof nextItem != 'undefined') {
					nextItem.items.items[1].items.items[0].items.items[0].setDisabled(status);
					nextItem.items.items[1].items.items[1].items.items[0].setDisabled(status);
				} else {
					if (mainparent.QuestionType_Code == 203) {
						//Родитель старший выше кнопки Далее
						mainparent.ownerCt.ownerCt.ownerCt.ownerCt.NextButton.setDisabled(status);
					}
					if (mainparent.QuestionType_Code == 261) {
						//Родитель старший выше кнопки Далее
						mainparent.ownerCt.ownerCt.ownerCt.ownerCt.SaveButton.setDisabled(status);
					}
				}
			}

		} else {

			if (buttonobject.QuestionType_Code == 644 || buttonobject.QuestionType_Code == 624) {

				mainparent.items.items[6].items.items[7].items.items[1].items.items[0].items.items[0].setDisabled(status);
				mainparent.items.items[6].items.items[7].items.items[1].items.items[1].items.items[0].setDisabled(status);

			} else if (buttonobject.QuestionType_Code == 645 || buttonobject.QuestionType_Code == 625) {

				mainparent.items.items[6].items.items[8].items.items[1].items.items[0].items.items[0].setDisabled(status);
				mainparent.items.items[6].items.items[8].items.items[1].items.items[1].items.items[0].setDisabled(status);

			} else if (buttonobject.QuestionType_Code == 646 || buttonobject.QuestionType_Code == 626) {
				mainparent = oobjnow.ownerCt.ownerCt.ownerCt.items.items[0];
				mainparent.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.NextButton.setDisabled(status);

			} else {

				var nextItem = mainparent.items.items[index + 1];
				if (typeof mainparent.items.items[index + 1] != 'undefined') {
					//блокирование кнопок первого уровня
					nextItem.items.items[0].items.items[1].items.items[0].items.items[0].setDisabled(status);
					nextItem.items.items[0].items.items[1].items.items[1].items.items[0].setDisabled(status);
					//блокируем эелементы

					//блокируем кнпоки следующих уровней

				} else {
					if (mainparent.QuestionType_Code == 173) {
						//Родитель старший выше кнопки Далее
						mainparent.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.items.items[1].NextButton.setDisabled(status);
					}
					if (mainparent.QuestionType_Code == 191) {
						//Родитель старший выше кнопки Далее
						mainparent.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.items.items[1].NextButton.setDisabled(status);
					}
				}
			}
		}
	}
}


//gaf 09022018 #106655
function hasSelectedElementPregnancy(inputelement) {
	var parent = inputelement.ownerCt.el.parent('.x-fieldset-body');
	var checkedcount = 0;
	var childfieldset = parent.dom.children;

	for (var i = 2; i < childfieldset.length; i++) {
		ready = true;

		var arr_obj = $("input[type=checkbox]", $("#" + childfieldset[i].id));
		for (var j = 0; j < arr_obj.length; j++) {
			if (arr_obj[j].checked) {
				checkedcount++;
			}
		}
		arr_obj = $("input[type=text]", $("#" + childfieldset[i].id));
		for (var j = 0; j < arr_obj.length; j++) {
			if (arr_obj[j].value != "") {
				checkedcount++;
			}
		}
	}
	return checkedcount > 0;
}

//test
function setbreakpoint3(){
	var checks = "";
}

function set395fields(paramchecked){	
	var array_356 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_356");
	Ext.getCmp(array_356[0].id).disabledClass = "disabled_field";
	var array_357 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_357");
	Ext.getCmp(array_357[0].id).disabledClass = "disabled_field";					
	var array_378 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_378");
	var array_414 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_414");
	var array_381 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_381");
	var array_382 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_382");
	var array_383 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_383");					
	var array_388 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_388");
	var array_390 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_390");
	var array_391 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_391");
	var array_401 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_401");
	var array_667 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_667");
	//30072018 делаем недоступными поля группы Плацентарная недостаточность и гипокция плода
	var array_662 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_662");
	var array_663 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_663");
	var array_664 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_664");
	var array_665 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("hiddenName","QuestionType_665");

	if (paramchecked) {
		//окружность живота
		Ext.getCmp(array_356[0].id).setDisabled(true);
		Ext.getCmp(array_356[0].id).setValue("");
		//высота стояния дна матки
		Ext.getCmp(array_357[0].id).setDisabled(true);
		Ext.getCmp(array_357[0].id).setValue("");
		//отеки беременных
		Ext.getCmp(array_378[0].id).setDisabled(true);
		Ext.getCmp(array_378[0].id).setValue(false);

		//Гестоз комбобокс
		Ext.getCmp(array_414[0].id).setDisabled(true);
		Ext.getCmp(array_414[0].id).setValue("");
		//положение плода
		Ext.getCmp(array_381[0].id).setDisabled(true);
		Ext.getCmp(array_381[0].id).setValue("");
		//предлежание
		Ext.getCmp(array_382[0].id).setDisabled(true);
		Ext.getCmp(array_382[0].id).setValue("");

		//крупный плод
		Ext.getCmp(array_383[0].id).setDisabled(true);
		Ext.getCmp(array_383[0].id).setValue(false);
		//обвитие пуповины
		Ext.getCmp(array_388[0].id).setDisabled(true);
		Ext.getCmp(array_388[0].id).setValue(false);
		//Фетоплацентарная недостаточность
		//Ext.getCmp(array_389[0].id).setDisabled(true);
		//Ext.getCmp(array_389[0].id).setValue(false);
		//Биологическая незрелость родовых путей в 40 недель беременности
		Ext.getCmp(array_390[0].id).setDisabled(true);
		Ext.getCmp(array_390[0].id).setValue(false);
		//Перенашивание беременности
		Ext.getCmp(array_391[0].id).setDisabled(true);
		Ext.getCmp(array_391[0].id).setValue(false);
		//Хроническая плацентарная недостаточность
		Ext.getCmp(array_401[0].id).setDisabled(true);
		Ext.getCmp(array_401[0].id).setValue(false);
		Ext.getCmp(array_667[0].id).setDisabled(true);
		Ext.getCmp(array_667[0].id).setValue("");				
		$(".disabled_field").parent().prev().css("color", "#777");							
		//30072018 делаем недоступными поля группы Плацентарная недостаточность и гипокция плода							
		Ext.getCmp(array_662[0].id).setDisabled(true);
		Ext.getCmp(array_662[0].id).setValue("");
		Ext.getCmp(array_663[0].id).setDisabled(true);
		Ext.getCmp(array_663[0].id).setValue("");
		Ext.getCmp(array_664[0].id).setDisabled(true);
		Ext.getCmp(array_664[0].id).setValue("");
		Ext.getCmp(array_665[0].id).setDisabled(true);
		Ext.getCmp(array_665[0].id).setValue("");
	} else {
		$(".disabled_field").parent().prev().css("color", "#222");
		Ext.getCmp(array_356[0].id).setDisabled(false);
		Ext.getCmp(array_357[0].id).setDisabled(false);
		Ext.getCmp(array_378[0].id).setDisabled(false);
		Ext.getCmp(array_414[0].id).setDisabled(false);
		Ext.getCmp(array_381[0].id).setDisabled(false);
		Ext.getCmp(array_382[0].id).setDisabled(false);
		Ext.getCmp(array_383[0].id).setDisabled(false);
		Ext.getCmp(array_388[0].id).setDisabled(false);
		Ext.getCmp(array_390[0].id).setDisabled(false);
		Ext.getCmp(array_391[0].id).setDisabled(false);
		Ext.getCmp(array_401[0].id).setDisabled(false);	
		Ext.getCmp(array_667[0].id).setDisabled(false);							
		Ext.getCmp(array_662[0].id).setDisabled(false);
		Ext.getCmp(array_663[0].id).setDisabled(false);
		Ext.getCmp(array_664[0].id).setDisabled(false);
		Ext.getCmp(array_665[0].id).setDisabled(false);							
	}		
}

function set396fields(paramchecked){
	var array_390 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_390");
	var array_391 = Ext.getCmp('swPersonPregnancyEditWindow').formPanels[1].find("name","QuestionType_391");
	if (paramchecked) {
		Ext.getCmp(array_390[0].id).setDisabled(true);
		Ext.getCmp(array_390[0].id).setValue(false);
		Ext.getCmp(array_391[0].id).setDisabled(true);
		Ext.getCmp(array_391[0].id).setValue(false);
	} else {
		Ext.getCmp(array_390[0].id).setDisabled(false);
		Ext.getCmp(array_391[0].id).setDisabled(false);
	}	
}


/**
 * @param {string} zpl - код на языке ZPL
 * @param {string} printer_name - название принтера (uid)
 */
function ZebraPrintZpl(zpl, printer_name, number) {

	if(Ext.isEmpty(printer_name)) {
		sw.swMsg.alert('Внимание','Выберите принтер (Сервис-Настройки-Стационар-Принтер)');
		return false;
	}

	if (number === undefined) number = 1;

	var errorCallback = function() {
		sw.swMsg.alert('Сообщение', 'При печати возникла ошибка. Проверьте настройки.');
	};

	var successCallback = function() {
		log(zpl);
	};

	BrowserPrint.getLocalDevices(function(printers) {
		if(!printers) {
			sw.swMsg.alert('Ошибка','Принтер: ' + printer_name + ' не найден');
			return;
		}

		for( var i = 0; i < printers.length; i++ ) {
			if ( printers[i].name !== printer_name ) continue;
			var selectedPrinter = printers[i];

			var callback = function (text) {
				for (var numCopy = 0; numCopy < number; numCopy++)
					selectedPrinter.send(zpl, successCallback, errorCallback);
			};

			switch ( selectedPrinter.connection ) {
				case 'usb':
					ZebraPrinterStatus( callback, selectedPrinter );
					return;

				case 'driver':
					callback();
					return;
			}
		}
	}, undefined, 'printer');
}


/**
 * Функция проверки статуса принтера
 * @param {function} finishedFunction - вызывается при статусе Ready to Print
 * @param {object} printer - принтер Zebra
 */
function ZebraPrinterStatus(finishedFunction,printer) {

	if(!printer) return false;

	var successFn =function(text) {
		var statuses = new Array();
		var is_error = text.charAt(70);
		var media = text.charAt(88);
		var head = text.charAt(87);
		var pause = text.charAt(84);

		if(!text) {
			statuses.push("Принтер не отвечает");
		}

		if (is_error == '0') {
			finishedFunction();
		}

		if (media == '1')
			statuses.push("Бумага закончилась");
		else if (media == '2')
			statuses.push("Лента закончилась");
		else if (media == '4')
			statuses.push("Открыта дверь");
		else if (media == '8')
			statuses.push("Ошибка резака");

		if (head == '1')
			statuses.push("Перегрев печатающей головки");
		else if (head == '2')
			statuses.push("Перегрев моторчика");
		else if (head == '4')
			statuses.push("Ошибка печатающей головки");
		else if (head == '8')
			statuses.push("Неправильная печатающая головка");

		if (pause == '1')
			statuses.push("Принтер приостановлен");

		if(statuses.length)
			sw.swMsg.alert('Сообщение', statuses.join(" "));
	};

	var errorFn = function() {
		sw.swMsg.alert('Сообщение', 'При печати возникла ошибка. Проверьте настройки.');
	};

	printer.sendThenRead("~HQES", successFn, errorFn);
};

/**
 * Возвращает параметры печати браслетов
 */
function getBandPrintOptions() {

	var options = new Object();
	stacOptions = Ext.globalOptions.stac;

	var model = stacOptions.band_printer_model;

	switch(model) {
		case '1':
			options.font_width  = 45;
			options.font_height = 35;
			options.barcode_size = 4;
			options.barcode_height = 150;
			options.margin_left = 1200;
			options.margin_bottom = 20;
			options.barcode_margin_bottom = 100;
			options.textfield_width = 1100;
			break;

		case '2':
			options.font_width  = 28;
			options.font_height = 23;
			options.barcode_size = 3;
			options.barcode_height = 100;
			options.margin_left = 975;
			options.margin_bottom = 10;
			options.barcode_margin_bottom = 65;
			options.textfield_width = 700;
			break;

		default: 
			options.font_width   = stacOptions.band_font_width;
			options.font_height  = stacOptions.band_font_height;
			options.barcode_size = stacOptions.band_barcode_size;
			options.barcode_height = stacOptions.band_barcode_height;
			options.margin_left   = stacOptions.band_margin_left;
			options.margin_bottom = stacOptions.band_margin_bottom;
			options.barcode_margin_bottom = stacOptions.band_barcode_margin_bottom;
			options.textfield_width = stacOptions.band_text_width;
			
	}

	return options;
}

/** 
 * Возвращает параметры печати штрих-кодов
 */
function getLisBarcodeOptions() {

	var options = new Object(),
		lisOptions = Ext.globalOptions.lis;

	options.printer = lisOptions.barcode_printer;
	options.printCount = lisOptions.ZebraPrintCount;
	options.printNumber = lisOptions.ZebraSampleNumber ? 'Y' : 'N';

	switch(lisOptions.barcode_size) {

		case '2030':
			options.width = 240;
			options.height = 160;
			options.barcode_size = 2;
			options.barcode_posX = 25;
			options.text_posX = 10,
			options.text_posY = 10,
			options.font_height = 15;
			options.font_width = 10;
		break;

		case '2040':
			options.width = 320;
			options.height = 165;
			options.barcode_size = 2;
			options.barcode_posX = 65;
			options.text_posX = 10,
			options.text_posY = 10,
			options.font_height = 16;
			options.font_width = 14;
		break;

		case '2540':
			options.width = 320;
			options.height = 200;
			options.barcode_size = 2;
			options.barcode_posX = 50;
			options.text_posX = 5,
			options.text_posY = 5,
			options.font_height = 20;
			options.font_width = 14;
		break;

		case '3050':
			options.width = 479;
			options.height = 240;
			options.barcode_size = 3;
			options.barcode_posX = 90;
			options.text_posX = 20,
			options.text_posY = 5,
			options.font_height = 20;
			options.font_width = 14;
		break;

		default:
			swMsg.alert(langs('Ошибка'),langs('Не заданы параметры печати для: '+ lisOptions.barcode_size));
	}

	return options;

}

/**
 * Всплывающее окно с заданными полями
 * @param params
 */
function showPopupWindow (params) {
	if(!params) return;

	var popupWindow = new Ext.Window({
		title: params.title,
		autoWidth: true,
		autoHeight: true,
		resizable: false,
		shadow: false,
		closeAction: 'close',
		layout: 'form',
		modal: true,
		buttons: [ {
			text      : 'Закрыть',
			iconCls   : 'cancel16',
			handler   : function() {
				popupWindow.close();
			}
		}]
	});

	var formPanel = new sw.Promed.FormPanel({ border: false, labelAlign: 'left', labelWidth: 200, style: 'padding: 5px;'});

	popupWindow.add(formPanel);

	params.fields.forEach(el => {
		formPanel.add({
			readOnly: true,
			fieldLabel: el.fieldLabel,
			value: el.value,
			xtype: 'textfield',
			width: 300
		})
	});
	popupWindow.show();
}