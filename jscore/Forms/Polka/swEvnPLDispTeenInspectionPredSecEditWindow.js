/**
* swEvnPLDispTeenInspectionPredSecEditWindow - окно редактирования/добавления талона по дополнительной диспансеризации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author		Dmitry Vlasenko
* @originalauthor	Ivan Petukhov aka Lich (megatherion@list.ru) / Stas Bykov aka Savage (savage1981@gmail.com)
* @version		16.10.2013
* @comment		Префикс для id компонентов EPLDTIPRES (EvnPLDispTeenInspectionEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispTeenInspectionPredSecEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispTeenInspectionPredSecEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispTeenInspectionPredSecEditWindow.js',
	draggable: true,
	printDopDispInfoConsent: function() {
		var win = this;
		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
		var isOtkaz = true;
		win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
			if(!Ext.isEmpty(rec.get('DopDispInfoConsent_IsAgree'))) {
				isOtkaz = false;
			}
		});
		var pattern = '';//Шаблон для печати от имени пациента
		var pattern_dep = '';//Шаблон для печати от имени представителя
		var title = ''; //Название формы вопроса о выборе варианта печати
		if (!isOtkaz) { //согласие
			pattern = 'EvnPLDispTeenInspectionConsent.rptdesign';
			pattern_dep = 'EvnPLDispTeenInspectionConsent_Deputy.rptdesign';
			title = lang['soglasiya'];
		}
		else{ //отказ
			pattern = 'EvnPLDispTeenInspectionOtkaz.rptdesign';
			pattern_dep = 'EvnPLDispTeenInspectionOtkaz_Deputy.rptdesign';
			title = lang['otkaza'];
		}
		var paramEvnPLDispTeenInspection = base_form.findField('EvnPLDispTeenInspection_id').getValue();
		if (Ext.isEmpty(paramEvnPLDispTeenInspection)) {
			win.doSave({
				callback: function() {
					win.printDopDispInfoConsent();
				}
			});
		} else {
			var dialog_wnd = Ext.Msg.show({
				title: lang['vid'] + title,
				msg:lang['vyiberite_vid'] + title,
				buttons: {yes: "От лица пациента", no: "От лица представителя", cancel: "Отмена"},
				fn: function(btn){
					if (btn == 'cancel') {
						return;
					}
					if(btn == 'yes'){ //От имени пациента
						printBirt({
							'Report_FileName': pattern,
							'Report_Params': '&EvnPLDispTeenInspection=' + paramEvnPLDispTeenInspection + '&paramMedPersonal=' + sw.Promed.MedStaffFactByUser.current.MedPersonal_id,
							'Report_Format': 'pdf'
						});
					}
					if(btn == 'no') { //От имени законного представителя
						printBirt({
							'Report_FileName': pattern_dep,
							'Report_Params': '&EvnPLDispTeenInspection=' + paramEvnPLDispTeenInspection + '&paramMedPersonal=' + sw.Promed.MedStaffFactByUser.current.MedPersonal_id,
							'Report_Format': 'pdf'
						});
					}
				}
			});
		}
	},
	deleteEvnVizitDispDop: function()
	{
		var win = this;
		
		sw.swMsg.show(
		{
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) 
			{
				if ('yes' == buttonId)
				{
					var evnvizitdispdop_grid = win.findById('EPLDTIPRESEvnVizitDispDopGrid');

					if (!evnvizitdispdop_grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = evnvizitdispdop_grid.getSelectionModel().getSelected();
					var EvnVizitDispDop_id = selected_record.get('EvnVizitDispDop_id');
					
					if (selected_record.data.Record_Status == 0)
					{
						evnvizitdispdop_grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						evnvizitdispdop_grid.getStore().filterBy(function(record) 
						{
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
					}
					
					if (evnvizitdispdop_grid.getStore().getCount() == 0)
					{
						evnvizitdispdop_grid.getTopToolbar().items.items[1].disable();
						evnvizitdispdop_grid.getTopToolbar().items.items[2].disable();
						evnvizitdispdop_grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						evnvizitdispdop_grid.getView().focusRow(0);
						evnvizitdispdop_grid.getSelectionModel().selectFirstRow();
					}

					win.reloadDopDispInfoConsentGrid();
					
					/*if ( evnvizitdispdop_grid.getStore().getCount() == 0 )
						LoadEmptyRow(evnvizitdispdop_grid);*/
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_osmotr_vracha-spetsialista'],
			title: lang['vopros']
		})
	},
	deleteEvnUslugaDispDop: function() {
		var win = this;
		
		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId)
				{
					var evnuslugadispdop_grid = win.findById('EPLDTIPRESEvnUslugaDispDopGrid');

					if (!evnuslugadispdop_grid.getSelectionModel().getSelected())
					{
						return false;
					}					

					var selected_record = evnuslugadispdop_grid.getSelectionModel().getSelected();
					if (selected_record.data.Record_Status == 0)
					{
						evnuslugadispdop_grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						evnuslugadispdop_grid.getStore().filterBy(function(record) {
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
					}

					if (evnuslugadispdop_grid.getStore().getCount() == 0)
					{
						evnuslugadispdop_grid.getTopToolbar().items.items[1].disable();
						evnuslugadispdop_grid.getTopToolbar().items.items[2].disable();
						evnuslugadispdop_grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						evnuslugadispdop_grid.getView().focusRow(0);
						evnuslugadispdop_grid.getSelectionModel().selectFirstRow();
					}

					win.reloadDopDispInfoConsentGrid();
					
					/*if ( evnuslugadispdop_grid.getStore().getCount() == 0 )
						LoadEmptyRow(evnuslugadispdop_grid);*/
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_laboratornoe_issledovanie'],
			title: lang['vopros']
		})
	},
	getDataForCallBack: function()
	{
		var win = this;
		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
		var personinfo = win.PersonInfoPanel;
		
		var response = new Object();
		
		response.EvnPLDispTeenInspection_id = base_form.findField('EvnPLDispTeenInspection_id').getValue();
		response.EvnPLDispTeenInspection_fid = base_form.findField('EvnPLDispTeenInspection_fid').getValue();
		response.Person_id = base_form.findField('Person_id').getValue();
		response.Server_id = base_form.findField('Server_id').getValue();
		response.Person_Surname = personinfo.getFieldValue('Person_Surname');
		response.Person_Firname = personinfo.getFieldValue('Person_Firname');
		response.Person_Secname = personinfo.getFieldValue('Person_Secname');
		response.Person_Birthday = personinfo.getFieldValue('Person_Birthday');
		response.ua_name = personinfo.getFieldValue('Person_RAddress');
		response.pa_name = personinfo.getFieldValue('Person_PAddress');
		response.EvnPLDispTeenInspection_setDate = typeof base_form.findField('EvnPLDispTeenInspection_setDate').getValue() == 'object' ? base_form.findField('EvnPLDispTeenInspection_setDate').getValue() : Date.parseDate(base_form.findField('EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y');
		response.EvnPLDispTeenInspection_disDate = typeof base_form.findField('EvnPLDispTeenInspection_disDate').getValue() == 'object' ? base_form.findField('EvnPLDispTeenInspection_disDate').getValue() : Date.parseDate(base_form.findField('EvnPLDispTeenInspection_disDate').getValue(), 'd.m.Y');
		response.EvnPLDispTeenInspection_VizitCount = null; // TODO
		response.EvnPLDispTeenInspection_IsFinish = (base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2) ? lang['da']:lang['net'];
		if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 2) {
			response.EvnCostPrint_IsNoPrintText = lang['otkaz_ot_spravki'];
		} else if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 1) {
			response.EvnCostPrint_IsNoPrintText = lang['spravka_vyidana'];
		} else {
			response.EvnCostPrint_IsNoPrintText = '';
		}
		response.EvnCostPrint_setDT = base_form.findField('EvnCostPrint_setDT').getValue();
		response.UslugaComplex_Name = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Name');
		
		return response;
	},
	loadUslugaComplex: function() {
		var win = this;
		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

		if (getRegionNick() == 'buryatiya') {
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.dispOnly = 1;
			base_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_id = base_form.findField('DispClass_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').getValue());
			base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.EducationInstitutionType_id = base_form.findField('EducationInstitutionType_id').getValue();
			base_form.findField('UslugaComplex_id').getStore().load({
				callback: function() {
					if (base_form.findField('UslugaComplex_id').getStore().getCount() > 0) {
						base_form.findField('UslugaComplex_id').setValue(base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id'));
					}
				}
			});
		}
	},
	verfGroup:function(){
		var wins = this;
		var base_form = wins.EvnPLDispTeenInspectionFormPanel.getForm();
		if ( base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 ) {
			//Проверка на Группу здоровья
			base_form.findField('HealthKind_id').setAllowBlank(false);
			base_form.findField('HealthKind_id').validate();
		}else{
			base_form.findField('HealthKind_id').setAllowBlank(true);
			base_form.findField('HealthKind_id').validate();
		}
	},
	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;
		var EvnPLDispTeenInspection_form = win.EvnPLDispTeenInspectionFormPanel;

		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

		if ( !EvnPLDispTeenInspection_form.getForm().isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					EvnPLDispTeenInspection_form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( Ext.isEmpty(win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue()) ) {
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

        if ( Ext.isEmpty(win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').getValue()) ) {
            win.getLoadMask().hide();

            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });

            return false;
        }
		
		win.verfGroup();
		
		base_form.findField('EvnPLDispTeenInspection_consDate').setValue(typeof win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue());

		base_form.findField('EvnPLDispTeenInspection_setDate').setValue(typeof win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').getValue());

		var EvnPLDispDop_setDate = win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').getValue();
		
		// При сохранении карты диспансеризации реализовать контроль: Дата оказания любой услуги (осмотра/исследования) должна быть не меньше, чем за месяц до осмотра
		// врача-терапевта. При невыполнении данного контроля выводить сообщение: "Дата любого исследования не может быть раньше, чем 1 месяц до даты осмотра врача-педиатра (ВОП)", сохранение отменить.
		var EvnUslugaDispDop_minDate, EvnVizitDispDop_pedDate, EvnUslugaDispDop_maxDate;
		var EvnPLDispTeenInspection_firSetDate = base_form.findField('EvnPLDispTeenInspection_firSetDate').getValue();
		var age = win.PersonInfoPanel.getFieldValue('Person_Age');
		
		var ErrorPedMsg = lang['data_lyubogo_issledovaniya_ne_mojet_byit_ranshe_chem_1_mesyats_do_datyi_osmotra_vracha-pediatra_vop'];
		var monthPed = 1;
		if (age >= 2) {
			ErrorPedMsg = lang['data_lyubogo_issledovaniya_ne_mojet_byit_ranshe_chem_3_mesyatsa_do_datyi_osmotra_vracha-pediatra_vop'];
			monthPed = 3;
		}
		
		var pedcodes = ['01090128'];
		// https://redmine.swan.perm.ru/issues/56948
		if (getRegionNick() == 'perm') {
			pedcodes = [/*'01090128',*/ 'B04.031.002', 'B04.031.004', 'B04.026.002'];
		} else if (getRegionNick() == 'ekb') {
			pedcodes = ['B04.031.002', 'B04.000.002', 'B04.026.002'];
		} else if (getRegionNick() == 'astra') {
			pedcodes = ['B04.031.004'];
		} else if (getRegionNick() == 'pskov') {
			pedcodes = ['B04.031.001'];
		} else if (getRegionNick() == 'krym') {
			pedcodes = ['B04.031.001'];
		} else if (getRegionNick() == 'buryatiya') {
			pedcodes = ['161014', '161078', '161150'];
		} else if (getRegionNick() == 'ufa') {
			pedcodes = [/*'01090128',*/ 'B04.031.002', 'B04.031.004', 'B04.026.002'];
		}
		
		// Вытаскиваем минимальную и максимальную дату осмотра и дату осмотра врачом терапевтом
		this.EvnVizitDispDopPanel.getStore().each(function(rec) {
			if (!Ext.isEmpty(rec.get('EvnVizitDispDop_setDate'))) {
				if (rec.get('UslugaComplex_Code') && rec.get('UslugaComplex_Code').inlist(pedcodes)) {
					EvnVizitDispDop_pedDate = rec.get('EvnVizitDispDop_setDate');
				}
				else {
					if (Ext.isEmpty(EvnUslugaDispDop_maxDate) || EvnUslugaDispDop_maxDate < rec.get('EvnVizitDispDop_setDate')) {
						EvnUslugaDispDop_maxDate = rec.get('EvnVizitDispDop_setDate');
					}

					if (Ext.isEmpty(EvnUslugaDispDop_minDate) || EvnUslugaDispDop_minDate > rec.get('EvnVizitDispDop_setDate')) {
						EvnUslugaDispDop_minDate = rec.get('EvnVizitDispDop_setDate');
					}
				}
			}
		});
		
		// Вытаскиваем минимальную и максимальную дату исследования
		this.EvnUslugaDispDopPanel.getStore().each(function(rec) {
			if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_setDate'))) {
				if (Ext.isEmpty(EvnUslugaDispDop_maxDate) || EvnUslugaDispDop_maxDate < rec.get('EvnUslugaDispDop_setDate')) {
					EvnUslugaDispDop_maxDate = rec.get('EvnVizitDispDop_setDate');
				}

				if (Ext.isEmpty(EvnUslugaDispDop_minDate) || EvnUslugaDispDop_minDate > rec.get('EvnUslugaDispDop_setDate')) {
					EvnUslugaDispDop_minDate = rec.get('EvnVizitDispDop_setDate');
				}
			}
		});

		if ( !Ext.isEmpty(EvnUslugaDispDop_minDate) && !Ext.isEmpty(EvnVizitDispDop_pedDate) && EvnUslugaDispDop_minDate < EvnVizitDispDop_pedDate.add(Date.MONTH, -monthPed) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				},
				icon: Ext.Msg.ERROR,
				msg: ErrorPedMsg,
				title: lang['oshibka']
			});
			return false;
		}
		
		// Если в поле «Случай закончен» указано значение «Да», то проводить контроль на сохранение осмотра врача-педиатра (ВОП)». 
		// Если осмотр врача-педиатра (ВОП) не сохранен, то выводить сообщение: «Случай не может быть закончен, так как не сохранен осмотр врача-педиатра (ВОП)». 
		// ОК. Сохранение отменить. Кодирование осмотра врача-педиатра (ВОП). 
		if ( !getRegionNick().inlist(['kareliya','penza','pskov']) && Ext.isEmpty(EvnVizitDispDop_pedDate) && base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('EvnPLDispTeenInspection_IsFinish').focus(true);
				},
				icon: Ext.Msg.ERROR,
				msg: lang['sluchay_ne_mojet_byit_zakonchen_tak_kak_ne_sohranen_osmotr_vracha-pediatra_vop'] + pedcodes.join(', ') + ')',
				title: lang['oshibka']
			});
			return false;
		}
		
		// Дата осмотра врача-педиатра (ВОП) не может быть больше 42 дней, чем дата подписания информированного согласия 1 этапа. При невыполнении контроля выводить
		// сообщение "Длительность 1 и 2 этапов диспансеризации несовершеннолетнего не может быть больше 30 рабочих дней. ОК". Сохранение отменить
		if ( !Ext.isEmpty(EvnVizitDispDop_pedDate) && EvnVizitDispDop_pedDate.add(Date.DAY, -42) > EvnPLDispTeenInspection_firSetDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: "Длительность 1 и 2 этапов предварительного осмотра несовершеннолетнего не может быть больше 30 рабочих дней.",
				title: lang['oshibka']
			});
			return false;
		}
		
		// Дата осмотра врача-педиатра должна быть больше (равна) датам всех остальных осмотров / исследований. При невыполнение данного контроля выводить сообщение:
		// "Дата осмотра / исследования по диспансеризации несовершеннолетнего не может быть больше даты осмотра врача-педиатра. ОК ". Сохранение карты отменить.
		if ( !Ext.isEmpty(EvnUslugaDispDop_maxDate) && !Ext.isEmpty(EvnVizitDispDop_pedDate) && EvnUslugaDispDop_maxDate > EvnVizitDispDop_pedDate ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: "Дата любого осмотра/исследования не может быть больше даты осмотра врача-педиатра (ВОП).",
				title: lang['oshibka']
			});
			return false;
		}

		// Функция get_grid_records возвращает записи из store
		function get_grid_records(store, save_trigger)
		{
			var fields = new Array();
			var result = new Array();

			store.fields.eachKey(function(key, item) {
				if (save_trigger == true && key.indexOf('_Name') == -1 && key.indexOf('_Fio') == -1)
				{
					fields.push(key);
				}
				else if (save_trigger == false)
				{
					fields.push(key);
				}
			});

			store.clearFilter();
			store.each(function(record) {
				if (record.data.Record_Status == 0 || record.data.Record_Status == 2 || record.data.Record_Status == 3)
				{
					var temp_record = new Object();
					for (i = 0; i < fields.length; i++)
					{
						if (save_trigger == true && fields[i].indexOf('Date') != -1)
						{
							temp_record[fields[i]] = Ext.util.Format.date(record.data[fields[i]], 'd.m.Y');
						}
						else
						{
							temp_record[fields[i]] = record.data[fields[i]];
						}
					}
					result.push(temp_record);
				}
			});

			store.filterBy(function(record) {
				if (record.data.Record_Status != 3)
				{
					return true;
				}
			});

			return result;
		}

		// Собираем данные из гридов
		var params = new Object();
		params.EvnVizitDispDop = Ext.util.JSON.encode(get_grid_records(win.EvnVizitDispDopPanel.getStore(), true));
		params.EvnUslugaDispDop = Ext.util.JSON.encode(get_grid_records(win.EvnUslugaDispDopPanel.getStore(), true));
		params.DispAppointData = Ext.util.JSON.encode(getStoreRecords(win.DispAppointGrid.getGrid().getStore(), {
			exceptionFields: [
				'DispAppointType_Name'
				,'DispAppoint_Comment'
			],
			clearFilter: true
		}));

		params.PayType_id = base_form.findField('PayType_id').getValue();
		params.EducationInstitutionType_id = base_form.findField('EducationInstitutionType_id').getValue();
		params.Org_id = base_form.findField('Org_id').getValue();
		win.getLoadMask("Подождите, идет сохранение...").show();

		EvnPLDispTeenInspection_form.getForm().submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide()
			},
			params: params,
			success: function(result_form, action) {
				win.getLoadMask().hide()
				
				if (action.result.EvnPLDispTeenInspection_id)
				{
					base_form.findField('EvnPLDispTeenInspection_id').setValue(action.result.EvnPLDispTeenInspection_id);
					win.callback({evnPLDispTeenInspectionData: win.getDataForCallBack()});

					if (options.callback) {
						options.callback();
					} else {
						win.hide();
					}
				}
				else
				{
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	height: 570,
	id: 'EvnPLDispTeenInspectionPredSecEditWindow',
	reloadDopDispInfoConsentGrid: function() {
		// чистим грид согласий
		var newstore = [];
		this.dopDispInfoConsentGrid.getGrid().getStore().removeAll();
		// собираем данные в гридах осмотров и обследований
		this.EvnVizitDispDopPanel.getStore().each(function(rec) {
			if (!Ext.isEmpty(rec.get('EvnVizitDispDop_id'))) {
				newstore.push({
					DopDispInfoConsent_id: 'viz'+rec.get('EvnVizitDispDop_id'),
					UslugaComplex_Name: rec.get('UslugaComplex_Name'),
					DopDispInfoConsent_IsAgree: true
				});
			}
		});
		this.EvnUslugaDispDopPanel.getStore().each(function(rec) {
			if (!Ext.isEmpty(rec.get('EvnUslugaDispDop_id'))) {
				newstore.push({
					DopDispInfoConsent_id: 'viz'+rec.get('EvnUslugaDispDop_id'),
					UslugaComplex_Name: rec.get('UslugaComplex_Name'),
					DopDispInfoConsent_IsAgree: true
				});
			}
		});
		// запихиваем в грид согласий
		this.dopDispInfoConsentGrid.getGrid().getStore().loadData(newstore);
	},
	initComponent: function() {
		var win = this;
		
		this.dopDispInfoConsentGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			id: 'EPLDTIPRES_dopDispInfoConsentGrid',
			dataUrl: '/?c=EvnPLDispTeenInspection&m=loadDopDispInfoConsent',
			region: 'center',
			height: 200,
			title: '',
			toolbar: false,
			saveAtOnce: false, 
			saveAllParams: false, 
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print', disabled: true, hidden: true },
				{ name: 'action_save', disabled: true, hidden: true }
			],
			stringfields: [
				{ name: 'DopDispInfoConsent_id', type: 'string', header: 'ID', key: true },
				{ name: 'UslugaComplex_Name', type: 'string', sortable: false, header: lang['osmotr_issledovanie'], id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsAgree', sortable: false, type: 'checkbox', isparams: true, header: lang['soglasie_grajdanina'], width: 180 }
			]
		});

		this.DispAppointGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swDispAppointEditForm',
			object: 'DispAppoint',
			actions: [
				{ name: 'action_add', handler: function() { this.openDispAppointEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openDispAppointEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openDispAppointEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteGridRecord('DispAppoint'); }.createDelegate(this) },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print'}
			],
			uniqueId: true,
			dataUrl: '/?c=DispAppoint&m=loadDispAppointGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'DispAppoint_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'DispAppointType_id', type: 'int', hidden: true },
				{ name: 'MedSpecOms_id', type: 'int', hidden: true },
				{ name: 'ExaminationType_id', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionBedProfile_id', type: 'int', hidden: true },
				{ name: 'LpuSectionBedProfile_fid', type: 'int', hidden: true },
				{ name: 'DispAppointType_Name', type: 'string', header: 'Назначение', width: 350 },
				{ name: 'DispAppoint_Comment', type: 'string', header: 'Комментарий', id: 'autoexpand' }
			]
		});

		this.DispAppointPanel = new sw.Promed.Panel({
			hidden: getRegionNick() == 'kz',
			items: [
				win.DispAppointGrid
			],
			animCollapse: true,
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: 'Назначения'
		});
		
		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			button2Callback: function(callback_data) {
				var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
				
				base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				base_form.findField('Server_id').setValue(callback_data.Server_id);
				
				win.PersonInfoPanel.load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
			},
			region: 'north'
		});
		
		this.FirstPanel = new sw.Promed.Panel({
			items: [{
				border: false,
				labelWidth: 200,
				layout: 'form',
				style: 'padding: 5px;',
				items: [{
					hidden: getRegionNick() != 'buryatiya',
					layout: 'form',
					border: false,
					items: [{
						hiddenName: 'UslugaComplex_id',
						width: 400,
						fieldLabel: lang['usluga_dispanserizatsii'],
						disabled: true,
						emptyText: '',
						nonDispOnly: false,
						xtype: 'swuslugacomplexnewcombo'
					}]
				}, {
					fieldLabel: lang['povtornaya_podacha'],
					listeners: {
						'check': function(checkbox, value) {
							if ( getRegionNick() != 'perm' ) {
								return false;
							}

							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

							var
								EvnPLDispTeenInspection_IndexRep = parseInt(base_form.findField('EvnPLDispTeenInspection_IndexRep').getValue()),
								EvnPLDispTeenInspection_IndexRepInReg = parseInt(base_form.findField('EvnPLDispTeenInspection_IndexRepInReg').getValue()),
								EvnPLDispTeenInspection_IsPaid = parseInt(base_form.findField('EvnPLDispTeenInspection_IsPaid').getValue());

							var diff = EvnPLDispTeenInspection_IndexRepInReg - EvnPLDispTeenInspection_IndexRep;

							if ( EvnPLDispTeenInspection_IsPaid != 2 || EvnPLDispTeenInspection_IndexRepInReg == 0 ) {
								return false;
							}

							if ( value == true ) {
								if ( diff == 1 || diff == 2 ) {
									EvnPLDispTeenInspection_IndexRep = EvnPLDispTeenInspection_IndexRep + 2;
								}
								else if ( diff == 3 ) {
									EvnPLDispTeenInspection_IndexRep = EvnPLDispTeenInspection_IndexRep + 4;
								}
							}
							else if ( value == false ) {
								if ( diff <= 0 ) {
									EvnPLDispTeenInspection_IndexRep = EvnPLDispTeenInspection_IndexRep - 2;
								}
							}

							base_form.findField('EvnPLDispTeenInspection_IndexRep').setValue(EvnPLDispTeenInspection_IndexRep);
						}
					},
					name: 'EvnPLDispTeenInspection_RepFlag',
					xtype: 'checkbox'
				}, {
					allowBlank: false,
					disabled: true,
					typeCode: 'int',
					useCommonFilter: true,
					width: 300,
					xtype: 'swpaytypecombo'
				}, {
					disabled: true,
					editable: false,
					allowBlank: false,
					enableKeyEvents: true,
					fieldLabel: lang['obrazovatelnoe_uchrejdenie'],
					hiddenName: 'Org_id',
					triggerAction: 'none',
					width: 300,
					xtype: 'sworgcombo',
					onTrigger1Click: function() {
						var combo = this;
						if (combo.disabled) {
							return false;
						}
						getWnd('swOrgSearchWindow').show({
							enableOrgType: true,
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 )
								{
									combo.getStore().load({
										params: {
											Object:'Org',
											Org_id: orgData.Org_id,
											Org_Name:''
										},
										callback: function()
										{
											combo.setValue(orgData.Org_id);
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
										}
									});
								}
								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					}
				}, {
					disabled: true,
					allowBlank: false,
					comboSubject: 'EducationInstitutionType',
					fieldLabel: lang['tip_obrazovatelnogo_uchrejdeniya'],
					hiddenName: 'EducationInstitutionType_id',
					listeners: {
						'change': function(field, newValue, oldValue) {
							win.loadUslugaComplex();
						}
					},
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					name: 'EvnPLDispTeenInspection_firSetDate',
					disabled: true,
					fieldLabel: lang['data_nachala_1_etap'],
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				}, {
                    allowBlank: false,
                    fieldLabel: lang['data_nachala_meditsinskogo_osmotra'],
                    format: 'd.m.Y',
                    id: 'EPLDTIPRES_EvnPLDispTeenInspection_setDate',
					listeners: {
						'change': function(field, newValue, oldValue) {
							win.loadUslugaComplex();
							win.setAgeGroupDispCombo(newValue, oldValue, true);
						}
					},
                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                    width: 100,
                    xtype: 'swdatefield'
				}]
			}],
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: false,
			title: ''
		});
		
		this.DopDispInfoConsentPanel = new sw.Promed.Panel({
			items: [{
				border: false,
				labelWidth: 200,
				layout: 'form',
				style: 'padding: 5px;',
				items: [{
					allowBlank: false,
					fieldLabel: lang['data_podpisaniya_soglasiya_otkaza'],
					format: 'd.m.Y',
					id: 'EPLDTIPRES_EvnPLDispTeenInspection_consDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}, {
					fieldLabel: lang['sluchay_obslujen_mobilnoy_brigadoy'],
					name: 'EvnPLDispTeenInspection_IsMobile',
					xtype: 'checkbox',
					listeners: {
						'check': function(checkbox, value) {
							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
							
							if ( value == true && win.action != 'view' ) {
								base_form.findField('Lpu_mid').setAllowBlank(false);
								base_form.findField('Lpu_mid').enable();
							} else {
								base_form.findField('Lpu_mid').setAllowBlank(true);
								base_form.findField('Lpu_mid').clearValue();
								base_form.findField('Lpu_mid').disable();
							}
						}
					}
				}, {
					fieldLabel: lang['mo_mobilnoy_brigadyi'],
					valueField: 'Lpu_id',
					hiddenName: 'Lpu_mid',
					xtype: 'sworgcombo',
					onTrigger1Click: function() {
						var combo = this;
						if (combo.disabled) {
							return false;
						}
						
						getWnd('swOrgSearchWindow').show({
							enableOrgType: false,
							onlyFromDictionary: true,
							object: 'lpu',
							DispClass_id: 11,
							Disp_consDate: (typeof win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue()),
							onSelect: function(lpuData) {
								if ( lpuData.Lpu_id > 0 )
								{
									combo.getStore().load({
										params: {
											OrgType: 'lpu',
											Lpu_oid: lpuData.Lpu_id
										},
										callback: function()
										{
											combo.setValue(lpuData.Lpu_id);
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
										}
									});
								}
								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					}
				},  {
					fieldLabel: 'Проведен вне МО',
					listeners: {
						'render': function() {
							if (getRegionNick() != 'ekb') {
								this.hideContainer();
							}
						}
					},
					name: 'EvnPLDispTeenInspection_IsOutLpu',
					xtype: 'checkbox'
				}]
			},
				win.dopDispInfoConsentGrid,
				// кнопки Печать и Сохранить
				{
					border: false,
					bodyStyle: 'padding:5px;',
					layout: 'column',
					items: [{
						border: false,
						bodyStyle: 'margin-left: 5px;',
						layout: 'form',
						items: [
							new Ext.Button({
								handler: function() {
									win.printDopDispInfoConsent();
								}.createDelegate(this),
								iconCls: 'print16',
								text: BTN_FRMPRINT
							})
						]
					}]
				}
			],
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: lang['informirovannoe_dobrovolnoe_soglasie_1_etap']
		});
		
		this.EvnVizitDispDopPanel = new Ext.grid.GridPanel({
			animCollapse: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			columns: [{
				dataIndex: 'EvnVizitDispDop_setDate',
				header: lang['data'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'UslugaComplex_Name',
				header: lang['osmotr'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'DopDispAlien_Name',
				header: lang['storonniy_spetsialist'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'LpuSection_Name',
				header: lang['otdelenie'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'MedPersonal_Fio',
				header: lang['vrach'],
				hidden: false,
				id: 'autoexpand',
				resizable: true,
				sortable: true
			}, {
				dataIndex: 'Diag_Code',
				header: lang['diagnoz'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}],
			collapsible: true,
			frame: false,
			height: 200,
			id: 'EPLDTIPRESEvnVizitDispDopGrid',
			keys: [{
				key: [
					Ext.EventObject.DELETE,
					Ext.EventObject.END,
					Ext.EventObject.ENTER,
					Ext.EventObject.F3,
					Ext.EventObject.F4,
					Ext.EventObject.HOME,
					Ext.EventObject.INSERT,
					Ext.EventObject.PAGE_DOWN,
					Ext.EventObject.PAGE_UP,
					Ext.EventObject.TAB
				],
				fn: function(inp, e) {
					e.stopEvent();

					if (e.browserEvent.stopPropagation)
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if (e.browserEvent.preventDefault)
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					var grid = Ext.getCmp('EPLDTIPRESEvnVizitDispDopGrid');

					switch (e.getKey())
					{
						case Ext.EventObject.DELETE:
							win.deleteEvnVizitDispDop();
							break;

						case Ext.EventObject.END:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(grid.getStore().getCount() - 1);
								grid.getSelectionModel().selectLastRow();
							}

							break;

						case Ext.EventObject.ENTER:
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
						case Ext.EventObject.INSERT:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							var action = 'add';

							if (e.getKey() == Ext.EventObject.F3)
							{
								action = 'view';
							}
							else if (e.getKey() == Ext.EventObject.F4)
							{
								action = 'edit';
							}
							else if (e.getKey() == Ext.EventObject.ENTER)
							{
								action = 'edit';
							}

							win.openEvnVizitDispDopEditWindow(action);

							break;

						case Ext.EventObject.HOME:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							break;

						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								Ext.getCmp('EPLDTIPRESIsFinishCombo').focus(true, 200);
							}
							else
							{
								var usluga_grid = Ext.getCmp('EPLDTIPRESEvnUslugaDispDopGrid');
								if ( usluga_grid.getStore().getCount() > 0 )
								{
									usluga_grid.focus();
									usluga_grid.getSelectionModel().selectFirstRow();
									usluga_grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('EPLDTIPRESSaveButton').focus();
								}
							}
						break;
							
						case Ext.EventObject.PAGE_DOWN:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnVizitDispDop_id') == selected_record.data.EvnVizitDispDop_id; });

								if (index + 10 <= records_count - 1)
								{
									index = index + 10;
								}
								else
								{
									index = records_count - 1;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;

						case Ext.EventObject.PAGE_UP:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnVizitDispDop_id') == selected_record.data.EvnVizitDispDop_id; });

								if (index - 10 >= 0)
								{
									index = index - 10;
								}
								else
								{
									index = 0;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;
					}
				},
				stopEvent: true
			}],
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					win.openEvnVizitDispDopEditWindow('edit');
				}
			},
			loadMask: true,
			region: 'center',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						var evn_vizitdispdop_id = sm.getSelected().data.EvnVizitDispDop_id;
						var record_status = sm.getSelected().data.Record_Status;
						var toolbar = this.grid.getTopToolbar();
						toolbar.items.items[1].disable();
						toolbar.items.items[2].disable();
						toolbar.items.items[3].disable();
						if (evn_vizitdispdop_id) {
							toolbar.items.items[2].enable();
							if (win.action != 'view') {
								toolbar.items.items[1].enable();
								toolbar.items.items[3].enable();
							}
						}
					}
				}
			}),
			stripeRows: true,
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					'load': function(store, records, options) {
						store.filterBy(function(record){
							if (record.data.Record_Status != 3 && record.data.Record_Status != 2)
							{
								return true;
							}
						});
					}
				},
				reader: new Ext.data.JsonReader({
					id: 'EvnVizitDispDop_id'
				}, [{
					mapping: 'EvnVizitDispDop_id',
					name: 'EvnVizitDispDop_id',
					type: 'int'
				}, {
					mapping: 'EvnDiagDopDispGridData',
					name: 'EvnDiagDopDispGridData'
				}, {
					mapping: 'Server_id',
					name: 'Server_id',
					type: 'int'
				}, {
					mapping: 'PersonEvn_id',
					name: 'PersonEvn_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_id',
					name: 'LpuSection_id',
					type: 'int'
				}, {
					mapping: 'Lpu_uid',
					name: 'Lpu_uid',
					type: 'int'
				}, {
					mapping: 'MedSpecOms_id',
					name: 'MedSpecOms_id',
					type: 'int'
				}, {
					mapping: 'LpuSectionProfile_id',
					name: 'LpuSectionProfile_id',
					type: 'int'
				}, {
					mapping: 'DopDispInfoConsent_id',
					name: 'DopDispInfoConsent_id',
					type: 'int'
				}, {
					mapping: 'UslugaComplex_id',
					name: 'UslugaComplex_id',
					type: 'int'
				}, {
					mapping: 'UslugaComplex_Code',
					name: 'UslugaComplex_Code',
					type: 'string'
				}, {
					mapping: 'MedStaffFact_id',
					name: 'MedStaffFact_id',
					type: 'int'
				}, {
					mapping: 'MedPersonal_id',
					name: 'MedPersonal_id',
					type: 'int'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnVizitDispDop_setDate',
					name: 'EvnVizitDispDop_setDate',
					type: 'date'
				}, {
					mapping: 'EvnVizitDispDop_setTime',
					name: 'EvnVizitDispDop_setTime',
					type: 'string'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnVizitDispDop_disDate',
					name: 'EvnVizitDispDop_disDate',
					type: 'date'
				}, {
					mapping: 'EvnVizitDispDop_disTime',
					name: 'EvnVizitDispDop_disTime',
					type: 'string'
				}, {
					mapping: 'Diag_id',
					name: 'Diag_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_Name',
					name: 'LpuSection_Name',
					type: 'string'
				}, {
					mapping: 'UslugaComplex_Name',
					name: 'UslugaComplex_Name',
					type: 'string'
				}, {
					mapping: 'DopDispAlien_Name',
					name: 'DopDispAlien_Name',
					type: 'string'
				}, {
					mapping: 'MedPersonal_Fio',
					name: 'MedPersonal_Fio',
					type: 'string'
				}, {
					mapping: 'Diag_Code',
					name: 'Diag_Code',
					type: 'string'
				}, {
					mapping: 'DopDispDiagType_id',
					name: 'DopDispDiagType_id',
					type: 'int'
				}, {
					mapping: 'DopDispAlien_id',
					name: 'DopDispAlien_id',
					type: 'int'
				}, {
					mapping: 'Record_Status',
					name: 'Record_Status',
					type: 'int'
				}]),
				url: '/?c=EvnPLDispTeenInspection&m=loadEvnVizitDispDopSecGrid'
			}),
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						win.openEvnVizitDispDopEditWindow('add');
					},
					iconCls: 'add16',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP
				}, {
					handler: function() {
						win.openEvnVizitDispDopEditWindow('edit');
					},
					iconCls: 'edit16',
					text: BTN_GRIDEDIT,
					tooltip: BTN_GRIDEDIT_TIP
				}, {
					handler: function() {
						win.openEvnVizitDispDopEditWindow('view');
					},
					iconCls: 'view16',
					text: BTN_GRIDVIEW,
					tooltip: BTN_GRIDVIEW_TIP
				}, {
					handler: function() {
						win.deleteEvnVizitDispDop();
					},
					iconCls: 'delete16',
					text: BTN_GRIDDEL,
					tooltip: BTN_GRIDDEL_TIP
				}]
			}),
			title: lang['osmotr_vracha-spetsialista']
		});
		
		this.EvnUslugaDispDopPanel = new Ext.grid.GridPanel({
			animCollapse: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			columns: [{
				dataIndex: 'EvnUslugaDispDop_setDate',
				header: lang['data'],
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'UslugaComplex_Name',
				header: lang['obsledovanie'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'ExaminationPlace_Name',
				header: lang['mesto_vyipolneniya'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'LpuSection_Name',
				header: lang['otdelenie'],
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'MedPersonal_Fio',
				header: lang['vrach'],
				hidden: false,
				id: 'autoexpand',
				resizable: true,
				sortable: true
			}],
			frame: false,
			height: 200,
			id: 'EPLDTIPRESEvnUslugaDispDopGrid',
			keys: [{
				key: [
					Ext.EventObject.DELETE,
					Ext.EventObject.END,
					Ext.EventObject.ENTER,
					Ext.EventObject.F3,
					Ext.EventObject.F4,
					Ext.EventObject.HOME,
					Ext.EventObject.INSERT,
					Ext.EventObject.PAGE_DOWN,
					Ext.EventObject.PAGE_UP,
					Ext.EventObject.TAB
				],
				fn: function(inp, e) {
					e.stopEvent();

					if (e.browserEvent.stopPropagation)
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if (e.browserEvent.preventDefault)
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					var grid = Ext.getCmp('EPLDTIPRESEvnUslugaDispDopGrid');

					switch (e.getKey())
					{
						case Ext.EventObject.DELETE:
							win.deleteEvnUslugaDispDop();
							break;

						case Ext.EventObject.END:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(grid.getStore().getCount() - 1);
								grid.getSelectionModel().selectLastRow();
							}

							break;
							
						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								var vizit_grid = Ext.getCmp('EPLDTIPRESEvnVizitDispDopGrid');
								if ( vizit_grid.getStore().getCount() > 0 )
								{
									vizit_grid.focus();
									vizit_grid.getSelectionModel().selectFirstRow();
									vizit_grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('EPLDTIPRESIsFinishCombo').focus(true, 200);
								}
							}
							else
							{
								Ext.getCmp('EPLDTIPRESSaveButton').focus();
							}
						break;

						case Ext.EventObject.ENTER:
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
						case Ext.EventObject.INSERT:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							var action = 'add';

							if (e.getKey() == Ext.EventObject.F3)
							{
								action = 'view';
							}
							else if ((e.getKey() == Ext.EventObject.F4) || (e.getKey() == Ext.EventObject.ENTER))
							{
								action = 'edit';
							}

							win.openEvnUslugaDispDopEditWindow(action);

							break;

						case Ext.EventObject.HOME:
							if (!grid.getSelectionModel().getSelected())
							{
								return false;
							}

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							break;

						case Ext.EventObject.PAGE_DOWN:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnUslugaDispDop_id') == selected_record.data.EvnUslugaDispDop_id; });

								if (index + 10 <= records_count - 1)
								{
									index = index + 10;
								}
								else
								{
									index = records_count - 1;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;

						case Ext.EventObject.PAGE_UP:
							var records_count = grid.getStore().getCount();

							if (records_count > 0 && grid.getSelectionModel().getSelected())
							{
								var selected_record = grid.getSelectionModel().getSelected();

								var index = grid.getStore().findBy(function(rec) { return rec.get('EvnUslugaDispDop_id') == selected_record.data.EvnUslugaDispDop_id; });

								if (index - 10 >= 0)
								{
									index = index - 10;
								}
								else
								{
									index = 0;
								}

								grid.getView().focusRow(index);
								grid.getSelectionModel().selectRow(index);
							}
							break;
					}
				},
				stopEvent: true
			}],
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					win.openEvnUslugaDispDopEditWindow('edit');
				}
			},
			loadMask: true,
			region: 'south',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						var evn_uslugadispdop_id = sm.getSelected().data.EvnUslugaDispDop_id;
						var record_status = sm.getSelected().data.Record_Status;
						var toolbar = this.grid.getTopToolbar();
						toolbar.items.items[1].disable();
						toolbar.items.items[2].disable();
						toolbar.items.items[3].disable();
						if (evn_uslugadispdop_id) {
							toolbar.items.items[2].enable();
							if (win.action != 'view') {
								toolbar.items.items[1].enable();
								toolbar.items.items[3].enable();
							}
						}
					}
				}
			}),
			stripeRows: true,
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					'load': function(store, records, options) {
						store.filterBy(function(record){
							if (record.data.Record_Status != 3 && record.data.Record_Status != 2)
							{
								return true;
							}
						});
					}
				},
				reader: new Ext.data.JsonReader({
					id: 'EvnUslugaDispDop_id'
				}, [{
					mapping: 'EvnUslugaDispDop_id',
					name: 'EvnUslugaDispDop_id',
					type: 'int'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispDop_setDate',
					name: 'EvnUslugaDispDop_setDate',
					type: 'date'
				}, {
					mapping: 'EvnUslugaDispDop_setTime',
					name: 'EvnUslugaDispDop_setTime',
					type: 'string'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispDop_disDate',
					name: 'EvnUslugaDispDop_disDate',
					type: 'date'
				}, {
					mapping: 'EvnUslugaDispDop_disTime',
					name: 'EvnUslugaDispDop_disTime',
					type: 'string'
				}, {
					dateFormat: 'd.m.Y',
					mapping: 'EvnUslugaDispDop_didDate',
					name: 'EvnUslugaDispDop_didDate',
					type: 'date'
				}, {
					mapping: 'DopDispInfoConsent_id',
					name: 'DopDispInfoConsent_id',
					type: 'int'
				}, {
					mapping: 'UslugaComplex_id',
					name: 'UslugaComplex_id',
					type: 'int'
				}, {
					mapping: 'ExaminationPlace_id',
					name: 'ExaminationPlace_id',
					type: 'int'
				}, {
					mapping: 'ExaminationPlace_Name',
					name: 'ExaminationPlace_Name',
					type: 'string'
				}, {
					mapping: 'LpuSection_id',
					name: 'LpuSection_id',
					type: 'int'
				}, {
					mapping: 'Lpu_uid',
					name: 'Lpu_uid',
					type: 'int'
				}, {
					mapping: 'MedSpecOms_id',
					name: 'MedSpecOms_id',
					type: 'int'
				}, {
					mapping: 'LpuSectionProfile_id',
					name: 'LpuSectionProfile_id',
					type: 'int'
				}, {
					mapping: 'LpuSection_Name',
					name: 'LpuSection_Name',
					type: 'string'
				}, {
					mapping: 'MedStaffFact_id',
					name: 'MedStaffFact_id',
					type: 'int'
				}, {
					mapping: 'MedPersonal_id',
					name: 'MedPersonal_id',
					type: 'int'
				}, {
					mapping: 'MedPersonal_Fio',
					name: 'MedPersonal_Fio',
					type: 'string'
				}, {
					mapping: 'UslugaComplex_Code',
					name: 'UslugaComplex_Code',
					type: 'string'
				}, {
					mapping: 'UslugaComplex_Name',
					name: 'UslugaComplex_Name',
					type: 'string'
				}, {
					mapping: 'EvnUslugaDispDop_Result',
					name: 'EvnUslugaDispDop_Result',
					type: 'string'
				}, {
					mapping: 'Record_Status',
					name: 'Record_Status',
					type: 'int'
				}]),
				url: '/?c=EvnPLDispTeenInspection&m=loadEvnUslugaDispDopSecGrid'
			}),
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						win.openEvnUslugaDispDopEditWindow('add');
					},
					iconCls: 'add16',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP
				}, {
					handler: function() {
						win.openEvnUslugaDispDopEditWindow('edit');
					},
					iconCls: 'edit16',
					text: BTN_GRIDEDIT,
					tooltip: BTN_GRIDEDIT_TIP
				}, {
					handler: function() {
						win.openEvnUslugaDispDopEditWindow('view');
					},
					iconCls: 'view16',
					text: BTN_GRIDVIEW,
					tooltip: BTN_GRIDVIEW_TIP
				}, {
					handler: function() {
						win.deleteEvnUslugaDispDop();
					},
					iconCls: 'delete16',
					text: BTN_GRIDDEL,
					tooltip: BTN_GRIDDEL_TIP
				}]
			}),
			title: lang['obsledovaniya']
		});
		
		this.EvnPLDispTeenInspectionMainResultsPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: lang['osnovnyie_rezultatyi_predvaritelnogo_osmotra'],
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			bodyStyle: 'padding: 5px',
			labelAlign: 'right',
			labelWidth: 195,
			items: [{
					name: 'EvnPLDispTeenInspection_id',
					value: null,
					xtype: 'hidden'
				}, {
					name:'EvnPLDispTeenInspection_IsPaid',
					xtype:'hidden'
				}, {
					name:'EvnPLDispTeenInspection_IndexRep',
					xtype:'hidden'
				}, {
					name:'EvnPLDispTeenInspection_IndexRepInReg',
					xtype:'hidden'
				}, {
					name: 'accessType',
					xtype: 'hidden'
				}, {
					name: 'PersonDispOrp_id',
					value: null,
					xtype: 'hidden'
				}, {
					name: 'DispClass_id',
					value: 11,
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispTeenInspection_fid',
					value: null,
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispTeenInspection_setDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispTeenInspection_disDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispTeenInspection_consDate',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					autoHeight: true,
					style: 'padding: 0px;',
					title: lang['obrazovatelnoe_uchrejdenie'],
					width: 600,
					items: [
						{
							allowBlank: true,
							fieldLabel: lang['data_postupleniya'],
							format: 'd.m.Y',
							name: 'EvnPLDispTeenInspection_eduDT',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 100,
							xtype: 'swdatefield'
						},
						{
							allowBlank: false,
							comboSubject: 'EducationInstitutionClass',
							fieldLabel: lang['obrazovatelnoe_uchrejdenie'],
							hiddenName: 'EducationInstitutionClass_id',
							moreFields: [{ name: 'EducationInstitutionType_id', mapping: 'EducationInstitutionType_id' }],
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						},
						{
							comboSubject: 'InstitutionNatureType',
							fieldLabel: lang['harakter_uchrejdeniya'],
							hiddenName: 'InstitutionNatureType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						},
						{
							comboSubject: 'InstitutionType',
							fieldLabel: lang['vid_uchrejdeniya'],
							hiddenName: 'InstitutionType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						}
					],
					bodyStyle: 'padding: 5px;',
					xtype: 'fieldset'
				},
				{
					autoHeight: true,
					style: 'padding: 0px;',
					title: lang['otsenka_fizicheskogo_razvitiya'],
					width: 600,
					items: [
						{
							fieldLabel: lang['massa_kg'],
							name: 'AssessmentHealth_Weight',
							decimalPrecision: 1,
							minValue: 2,
							maxValue: 500,
							xtype: 'numberfield'
						},
						{
							fieldLabel: lang['rost_sm'],
							name: 'AssessmentHealth_Height',
							minValue: 20,
							maxValue: 275,
							xtype: 'numberfield'
						},
						{
							fieldLabel: lang['otklonenie_massa'],
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
									if (newValue == 2) {
										if (win.action != 'view') {
											base_form.findField('WeightAbnormType_id').enable();
										}
										base_form.findField('WeightAbnormType_id').setAllowBlank(false);
									} else {
										base_form.findField('WeightAbnormType_id').clearValue();
										base_form.findField('WeightAbnormType_id').disable();
										base_form.findField('WeightAbnormType_id').setAllowBlank(true);
									}
								}
							},
							hiddenName: 'WeightAbnormType_YesNo',
							xtype: 'swyesnocombo'
						},
						{
							comboSubject: 'WeightAbnormType',
							disabled: true,
							fieldLabel: lang['tip_otkloneniya_massa'],
							hiddenName: 'WeightAbnormType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						},
						{
							fieldLabel: lang['otklonenie_rost'],
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
									if (newValue == 2) {
										if (win.action != 'view') {
											base_form.findField('HeightAbnormType_id').enable();
										}
										base_form.findField('HeightAbnormType_id').setAllowBlank(false);
									} else {
										base_form.findField('HeightAbnormType_id').clearValue();
										base_form.findField('HeightAbnormType_id').disable();
										base_form.findField('HeightAbnormType_id').setAllowBlank(true);
									}
								}
							},
							hiddenName: 'HeightAbnormType_YesNo',
							xtype: 'swyesnocombo'
						},
						{
							comboSubject: 'HeightAbnormType',
							disabled: true,
							fieldLabel: lang['tip_otkloneniya_rost'],
							hiddenName: 'HeightAbnormType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						}
					],
					bodyStyle: 'padding: 5px;',
					xtype: 'fieldset'
				},
				{
					autoHeight: true,
					style: 'padding: 0px;',
					title: lang['otsenka_psihicheskogo_razvitiya_sostoyaniya'],
					width: 600,
					items: [
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['poznavatelnaya_funktsiya_vozrast_razvitiya_mes'],
							minValue: 0,
							name: 'AssessmentHealth_Gnostic',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['motornaya_funktsiya_vozrast_razvitiya_mes'],
							minValue: 0,
							name: 'AssessmentHealth_Motion',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['emotsionalnaya_i_sotsialnaya_kontakt_s_okrujayuschim_mirom_funktsii_vozrast_razvitiya_mes'],
							minValue: 0,
							name: 'AssessmentHealth_Social',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: lang['predrechevoe_i_rechevoe_razvitie_vozrast_razvitiya_mes'],
							minValue: 0,
							name: 'AssessmentHealth_Speech',
							xtype: 'numberfield'
						},
						{
							fieldLabel: lang['psihomotornaya_sfera'],
							hiddenName: 'NormaDisturbanceType_id',
							xtype: 'swnormadisturbancetypecombo'
						},
						{
							fieldLabel: lang['intellekt'],
							hiddenName: 'NormaDisturbanceType_uid',
							xtype: 'swnormadisturbancetypecombo'
						},
						{
							fieldLabel: lang['emotsionalno-vegetativnaya_sfera'],
							hiddenName: 'NormaDisturbanceType_eid',
							xtype: 'swnormadisturbancetypecombo'
						}
					],
					bodyStyle: 'padding: 5px;',
					xtype: 'fieldset'
				},
				{
					autoHeight: true,
					style: 'padding: 0px;',
					title: lang['otsenka_polovogo_razvitiya'],
					width: 600,
					items: [
						{
							fieldLabel: 'P',
							minValue: 0,
							maxValue: 5,
							name: 'AssessmentHealth_P',
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Ax',
							minValue: 0,
							maxValue: 5,
							name: 'AssessmentHealth_Ax',
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Fa',
							minValue: 0,
							maxValue: 5,
							name: 'AssessmentHealth_Fa',
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Ma',
							minValue: 0,
							maxValue: 5,
							name: 'AssessmentHealth_Ma',
							xtype: 'numberfield'
						},
						{
							fieldLabel: 'Me',
							minValue: 0,
							maxValue: 5,
							name: 'AssessmentHealth_Me',
							xtype: 'numberfield'
						},
						{
							autoHeight: true,
							style: 'padding: 0px;',
							id: 'EPLDTIPRES_menarhe',
							title: lang['harakteristika_menstrualnoy_funktsii_menarhe'],
							width: 580,
							items: [
								{
									fieldLabel: lang['let'],
									minValue: 6,
									maxValue: 17,
									name: 'AssessmentHealth_Years',
									xtype: 'numberfield'
								},
								{
									fieldLabel: lang['mesyatsev'],
									minValue: 0,
									maxValue: 12,
									name: 'AssessmentHealth_Month',
									xtype: 'numberfield'
								}
							],
							bodyStyle: 'padding: 5px;',
							xtype: 'fieldset'
						},
						{
							autoHeight: true,
							style: 'padding: 0px;',
							id: 'EPLDTIPRES_menses',
							title: lang['menses_harakteristika'],
							width: 580,
							items: [
								{
									boxLabel: lang['regulyarnyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsRegular',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['neregulyarnyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsIrregular',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['obilnyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsAbundant',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['umerennyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsModerate',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['skudnyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsScanty',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['boleznennyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsPainful',
									xtype: 'checkbox'
								},
								{
									boxLabel: lang['bezboleznennyie'],
									hideLabel: true,
									name: 'AssessmentHealth_IsPainless',
									xtype: 'checkbox'
								}
							],
							bodyStyle: 'padding: 5px;',
							xtype: 'fieldset'
						}
					],
					bodyStyle: 'padding: 5px;',
					xtype: 'fieldset'
				},
				{
					fieldLabel: lang['gruppa_zdorovya'],
					hiddenName: 'HealthKind_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
							if (
								!Ext.isEmpty(base_form.findField('HealthKind_id').getValue())
								&& base_form.findField('HealthKind_id').getValue() != 1
								&& base_form.findField('HealthKind_id').getValue() != 2
							) {
								win.DispAppointPanel.expand();
								win.DispAppointPanel.enable();
							} else {
								win.DispAppointPanel.collapse();
								win.DispAppointPanel.disable();
							}
						}
					},
					loadParams: {params: {where: ' where HealthKind_Code <= 5'}},
					xtype: 'swhealthkindcombo'
				},
				{
					comboSubject: 'HealthGroupType',
					fieldLabel: 'Медицинская группа для занятий физ.культурой до проведения обследования',
					hiddenName: 'HealthGroupType_oid',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				},
				{
					comboSubject: 'HealthGroupType',
					fieldLabel: lang['meditsinskaya_gruppa_dlya_zanyatiy_fiz_kulturoy'],
					hiddenName: 'HealthGroupType_id',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: lang['sluchay_zakonchen'],
					value: 1,
					hiddenName: 'EvnPLDispTeenInspection_IsFinish',
					allowBlank: false,
					xtype: 'swyesnocombo',
					listeners:{
						'select':function (combo, record) {
							win.verfGroup();
						},
						'change': function() {
							win.checkForCostPrintPanel();
						}
					}
				}
			],
			layout: 'form',
			region: 'center'
		});

		this.CostPrintPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: lang['spravka_o_stoimosti_lecheniya'],
			hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']),
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			labelAlign: 'right',
			labelWidth: 195,
			items: [{
				bodyStyle: 'padding: 5px',
				border: false,
				height: 90,
				layout: 'form',
				region: 'center',
				items: [{
					fieldLabel: lang['data_vyidachi_spravki_otkaza'],
					width: 100,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'EvnCostPrint_setDT',
					xtype: 'swdatefield'
				},{
					fieldLabel: lang['otkaz'],
					hiddenName: 'EvnCostPrint_IsNoPrint',
					width: 60,
					xtype: 'swyesnocombo'
				}]
			}]
		});

		this.EvnPLDispTeenInspectionFormPanel = new Ext.form.FormPanel({
			border: false,
			layout: 'form',
			region: 'center',
			autoScroll: true,
			items: [
				win.FirstPanel,
				// информированное добровольное согласие
				win.DopDispInfoConsentPanel,
				// Осмотры
				win.EvnVizitDispDopPanel,
				// Обследования
				win.EvnUslugaDispDopPanel,
				// основные результаты диспансеризации
				win.EvnPLDispTeenInspectionMainResultsPanel,
				// назначения
				win.DispAppointPanel,
				// Справка о стоимости лечения
				win.CostPrintPanel
			],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch (e.getKey())
					{
						case Ext.EventObject.C:
							if (this.action != 'view')
							{
								this.doSave(false);
							}
							break;

						case Ext.EventObject.G:
							this.printEvnPLDispTeenInspProf();
							break;

						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.G, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'EvnPLDispTeenInspection_id' },
				{ name: 'EvnPLDispTeenInspection_IsPaid' },
				{ name: 'EvnPLDispTeenInspection_IndexRep' },
				{ name: 'EvnPLDispTeenInspection_IndexRepInReg' },
				{ name: 'accessType' },
				{ name: 'PersonDispOrp_id' },
				{ name: 'DispClass_id' },
				{ name: 'PayType_id' },
				{ name: 'EvnPLDispTeenInspection_fid' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'EvnPLDispTeenInspection_setDate' },
				{ name: 'EvnPLDispTeenInspection_firSetDate' },
				{ name: 'EvnPLDispTeenInspection_consDate' },
				{ name: 'EvnPLDispTeenInspection_disDate' },
				{ name: 'EvnPLDispTeenInspection_eduDT' },
				{ name: 'Org_id' },
				{ name: 'Lpu_mid' },
				{ name: 'EvnPLDispTeenInspection_IsMobile' },
				{ name: 'EvnPLDispTeenInspection_IsOutLpu' },
				{ name: 'EducationInstitutionType_id' },
				{ name: 'EducationInstitutionClass_id' },
				{ name: 'InstitutionNatureType_id' },
				{ name: 'InstitutionType_id' },
				{ name: 'AssessmentHealth_Weight' },
				{ name: 'AssessmentHealth_Height' },
				{ name: 'WeightAbnormType_YesNo' },
				{ name: 'WeightAbnormType_id' },
				{ name: 'HeightAbnormType_YesNo' },
				{ name: 'HeightAbnormType_id' },
				{ name: 'AssessmentHealth_Gnostic'},
				{ name: 'AssessmentHealth_Motion'},
				{ name: 'AssessmentHealth_Social'},
				{ name: 'AssessmentHealth_Speech'},
				{ name: 'NormaDisturbanceType_id'},
				{ name: 'NormaDisturbanceType_uid'},
				{ name: 'NormaDisturbanceType_eid'},
				{ name: 'AssessmentHealth_P' },
				{ name: 'AssessmentHealth_Ax' },
				{ name: 'AssessmentHealth_Fa' },
				{ name: 'AssessmentHealth_Ma' },
				{ name: 'AssessmentHealth_Me' },
				{ name: 'AssessmentHealth_Years' },
				{ name: 'AssessmentHealth_Month' },
				{ name: 'AssessmentHealth_IsRegular' },
				{ name: 'AssessmentHealth_IsIrregular' },
				{ name: 'AssessmentHealth_IsAbundant' },
				{ name: 'AssessmentHealth_IsModerate' },
				{ name: 'AssessmentHealth_IsScanty' },
				{ name: 'AssessmentHealth_IsPainful' },
				{ name: 'AssessmentHealth_IsPainless' },
				{ name: 'HealthKind_id' },
				{ name: 'HealthGroupType_oid' },
				{ name: 'HealthGroupType_id' },
				{ name: 'EvnPLDispTeenInspection_IsFinish' },
				{ name: 'EvnCostPrint_setDT' },
				{ name: 'EvnCostPrint_IsNoPrint' }
			]),
			url: '/?c=EvnPLDispTeenInspection&m=saveEvnPLDispTeenInspectionSec'
		});
		
		Ext.apply(this, {
			items: [
				// паспортная часть человека
				win.PersonInfoPanel,
				win.EvnPLDispTeenInspectionFormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EPLDTIPRES_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDTIPRES_CancelButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
					base_form.findField('EvnPLDispTeenInspection_IsFinish').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnPLDispTeenInspMedZak();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDTIPRES_PrintZak',
				tabIndex: 2408,
				text: lang['pechat_zaklyucheniya_o_gruppe_dlya_zanyatiy_fiz_kulturoy'],
				tooltip:lang['pechat_meditsinskogo_zaklyucheniya_o_prinadlejnosti_nesovershennoletnego_k_meditsisnkoy_gruppe_dlya_zanyatiy_fizicheskoy_kulturoy']
			}, {
                hidden: false,
                handler: function() {
                    this.printEvnPLDispTeenInspProf();
                }.createDelegate(this),
                iconCls: 'print16',
                id: 'EPLDTIPRES_PrintButton',
                tabIndex: 2407,
                text: lang['pechat_kartyi_med_osmotra']
            }, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDTIPRES_CancelButton',
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispTeenInspectionPredSecEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPLDispTeenInspectionPredSecEditWindow');
			var tabbar = win.findById('EPLDTIPRES_EvnPLTabbar');

			switch (e.getKey())
			{
				case Ext.EventObject.C:
					win.doSave();
					break;

				case Ext.EventObject.J:
					win.hide();
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 570,
	minWidth: 800,
	modal: true,
	onHide: Ext.emptyFn,
    printEvnPLDispTeenInspProf: function() {
		var win = this;
        var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
        var paramEvnPLTeen = base_form.findField('EvnPLDispTeenInspection_id').getValue();
        var paramDispType = base_form.findField('DispClass_id').getValue();
		if (Ext.isEmpty(paramEvnPLTeen)) {
			win.doSave({
				callback: function() {
					win.printEvnPLDispTeenInspProf();
				}
			});
		} else {
			printBirt({
				'Report_FileName': 'pan_EvnPLTeenCard.rptdesign',
				'Report_Params': '&paramEvnPLTeen=' + paramEvnPLTeen + '&paramDispType=' + paramDispType,
				'Report_Format': 'pdf'
			});
		}
    },
	printEvnPLDispTeenInspMedZak: function(){
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var paramEvnPLTeen = base_form.findField('EvnPLDispTeenInspection_id').getValue();
		if (Ext.isEmpty(paramEvnPLTeen)) {
			win.doSave({
				callback: function() {
					win.printEvnPLDispTeenInspMedZak();
				}
			});
		} else {
			printBirt({
				'Report_FileName': 'pan_EvnPLTeenMedZak.rptdesign',
				'Report_Params': '&paramEvnPLTeen=' + paramEvnPLTeen,
				'Report_Format': 'pdf'
			});
		}
	},
	openEvnVizitDispDopEditWindow: function(action) {
        var win = this;
		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

		if (getWnd('swEvnVizitDispDop13SecEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_osmotra_vracha-spetsialista_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = win.PersonInfoPanel.getFieldValue('Person_id');
		var person_birthday = win.PersonInfoPanel.getFieldValue('Person_Birthday');
		var person_surname = win.PersonInfoPanel.getFieldValue('Person_Surname');
		var person_firname = win.PersonInfoPanel.getFieldValue('Person_Firname');
		var person_secname = win.PersonInfoPanel.getFieldValue('Person_Secname');
		var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
		var age = win.PersonInfoPanel.getFieldValue('Person_Age');

		var selected_record = win.findById('EPLDTIPRESEvnVizitDispDopGrid').getSelectionModel().getSelected();

		if (action == 'add')
		{
			params = win.params;

			// буду собирать максимальную дату осмотра или анализов
			var max_date = false;
			params.EvnVizitDispDop_id = swGenTempId(this.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore(), 'EvnVizitDispDop_id');
			params.Record_Status = 0;
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			params.Server_id = base_form.findField('Server_id').getValue();
			
			params['Not_Z_Group_Diag'] = false;
			
			var usedUslugaComplexCodeList = [];
			win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().each(function(rec) {
				if ( rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else if ((action == 'edit') || (action == 'view'))
		{			
			if (!win.findById('EPLDTIPRESEvnVizitDispDopGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			if ( !selected_record.data.EvnVizitDispDop_id == null || selected_record.data.EvnVizitDispDop_id == '' )
				return;
			
			params = selected_record.data;

			params['Not_Z_Group_Diag'] = false;
			
			var usedUslugaComplexCodeList = [];
			win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else
		{
			return false;
		}

        getWnd('swEvnVizitDispDop13SecEditWindow').show({
			archiveRecord: this.archiveRecord,
        	action: action,
        	callback: function(data, add_flag) {
				var i;
				var vizit_fields = new Array();

				win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().fields.eachKey(function(key, item) {
					vizit_fields.push(key);
				});

				if ( add_flag == true )
        		{
	        		// удаляем пустую строку если она есть
					if ( win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().getCount() == 1 )
					{
						var selected_record = win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().getAt(0);
						if ( !selected_record.data.EvnVizitDispDop_id == null || selected_record.data.EvnVizitDispDop_id == '' )
							win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().removeAll();
					}

					win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().clearFilter();
					win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().loadData(data, add_flag);
					win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().filterBy(function(record) {
						if (record.data.Record_Status != 3)
						{
							return true;
						}
					});
	        	}
				else {
	        		index = win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().findBy(function(rec) { return rec.get('EvnVizitDispDop_id') == data[0].EvnVizitDispDop_id; });

	        		if (index == -1)
	        		{
	        			return false;
	        		}

					var record = win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().getAt(index);
					for (i = 0; i < vizit_fields.length; i++)
					{
						record.set(vizit_fields[i], data[0][vizit_fields[i]]);
					}

					record.commit();
				}
				
				win.reloadDopDispInfoConsentGrid();
				
        		return true;
        	},
        	formParams: params,
        	onHide: function() {
				if ( win.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().getCount() > 0 ) {
					win.findById('EPLDTIPRESEvnVizitDispDopGrid').getSelectionModel().selectFirstRow();
					win.findById('EPLDTIPRESEvnVizitDispDopGrid').getView().focusRow(0);
				}
			},
			ownerWindow: win,
			DispClass_id: base_form.findField('DispClass_id').getValue(),
			Person_id: person_id,
			Person_Birthday: person_birthday,
			Person_Surname: person_surname,
			Person_Firname: person_firname,
			Person_Secname: person_secname,
			Year: this.Year,
			Sex_id: sex_id,
			Person_Age: age,
			max_date: max_date
		});
	},
	openEvnUslugaDispDopEditWindow: function(action) {
        var win = this;
		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

		if (getWnd('swEvnUslugaDispDop13SecEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_laboratornogo_issledovaniya_uje_otkryito']);
			return false;
		}

		var params = new Object();

		var person_id = win.PersonInfoPanel.getFieldValue('Person_id');
		var person_birthday = win.PersonInfoPanel.getFieldValue('Person_Birthday');
		var person_surname = win.PersonInfoPanel.getFieldValue('Person_Surname');
		var person_firname = win.PersonInfoPanel.getFieldValue('Person_Firname');
		var person_secname = win.PersonInfoPanel.getFieldValue('Person_Secname');
		var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
		var age = win.PersonInfoPanel.getFieldValue('Person_Age');
		
		if (win.action == 'add') {
			var set_date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
			Ext.getCmp('EPLDTIPRES_EvnPLDispTeenInspection_consDate').setValue(set_date);
		} else {
			var set_date = Date.parseDate(Ext.getCmp('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y');
		}

		if (action == 'add')
		{
			params = win.params;

			params.EvnUslugaDispDop_id = swGenTempId(this.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore(), 'EvnUslugaDispDop_id');
			params.Record_Status = 0;
			
			var usedUslugaComplexCodeList = [];
			win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else if ((action == 'edit') || (action == 'view'))
		{
			if (!win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			var selected_record = win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getSelectionModel().getSelected();
			
			if ( !selected_record.data.EvnUslugaDispDop_id == null || selected_record.data.EvnUslugaDispDop_id == '' )
				return;

			params = selected_record.data;

			var usedUslugaComplexCodeList = [];
			win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else
		{
			return false;
		}
		
        getWnd('swEvnUslugaDispDop13SecEditWindow').show({
			archiveRecord: this.archiveRecord,
        	action: action,
        	callback: function(data, add_flag) {
				var i;
				var usluga_fields = new Array();

				win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().fields.eachKey(function(key, item) {
					usluga_fields.push(key);
				});
				if (add_flag == true)
        		{
					// удаляем пустую строку если она есть
					if ( win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().getCount() == 1 )
					{
						var selected_record = win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().getAt(0);
						if ( !selected_record.data.EvnUslugaDispDop_id == null || selected_record.data.EvnUslugaDispDop_id == '' )
							win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().removeAll();
					}

					win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().clearFilter();
					win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().loadData(data, add_flag);
					win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().filterBy(function(record) {
						if (record.data.Record_Status != 3)
						{
							return true;
						}
					});
	        	}
				else {
	        		index = win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().findBy(function(rec) { return rec.get('EvnUslugaDispDop_id') == data[0].EvnUslugaDispDop_id; });

	        		if (index == -1)
	        		{
	        			return false;
	        		}

					var record = win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().getAt(index);

					for (i = 0; i < usluga_fields.length; i++)
					{
						record.set(usluga_fields[i], data[0][usluga_fields[i]]);
					}

					record.commit();
				}
				
				win.reloadDopDispInfoConsentGrid();
				
        		return true;
        	},
        	formParams: params,
        	onHide: function() {
				if ( win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().getCount() > 0 ) {
					win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getSelectionModel().selectFirstRow();
					win.findById('EPLDTIPRESEvnUslugaDispDopGrid').getView().focusRow(0);
				}
			},
        	ownerWindow: win,
			DispClass_id: base_form.findField('DispClass_id').getValue(),
		    Person_id: person_id,
		    Person_Birthday: person_birthday,
			Person_Surname: person_surname,
		    Person_Firname: person_firname,
			Person_Secname: person_secname,
			Sex_id: sex_id,
			Person_Age: age,
			set_date: set_date,
			UslugaComplex_Date: win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue()
		});
	},
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null
	},

	plain: true,
	resizable: true,
	setAgeGroupDispCombo: function(newSetDate, oldSetDate, recalc) {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var age_start = -1;
		var month_start = -1;
		var age_end = -1;
		if ( !Ext.isEmpty(newSetDate) ) {
			age_start = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), newSetDate);
			var year = newSetDate.getFullYear();
			var endYearDate = new Date(year, 11, 31);
			age_end = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), endYearDate);
			month_start = swGetPersonAgeMonth(win.PersonInfoPanel.getFieldValue('Person_Birthday'), newSetDate);
			win.onChangeAge(age_start, newSetDate);
		}
	},
	onChangeAge: function(age, setDate) {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		if ( age >= 0 && age <= 4 ) {
			if ( win.action != 'view' ) {
				base_form.findField('NormaDisturbanceType_id').clearValue();
				base_form.findField('NormaDisturbanceType_uid').clearValue();
				base_form.findField('NormaDisturbanceType_eid').clearValue();

				base_form.findField('AssessmentHealth_Gnostic').enable();
				base_form.findField('AssessmentHealth_Motion').enable();
				base_form.findField('AssessmentHealth_Social').enable();
				base_form.findField('AssessmentHealth_Speech').enable();
				base_form.findField('NormaDisturbanceType_id').disable();
				base_form.findField('NormaDisturbanceType_uid').disable();
				base_form.findField('NormaDisturbanceType_eid').disable();
			}
		}
		else if ( age >= 5 && age <= 17 ) {
			if ( win.action != 'view' ) {
				base_form.findField('AssessmentHealth_Gnostic').setRawValue('');
				base_form.findField('AssessmentHealth_Motion').setRawValue('');
				base_form.findField('AssessmentHealth_Social').setRawValue('');
				base_form.findField('AssessmentHealth_Speech').setRawValue('');

				base_form.findField('AssessmentHealth_Gnostic').disable();
				base_form.findField('AssessmentHealth_Motion').disable();
				base_form.findField('AssessmentHealth_Social').disable();
				base_form.findField('AssessmentHealth_Speech').disable();
				base_form.findField('NormaDisturbanceType_id').enable();
				base_form.findField('NormaDisturbanceType_uid').enable();
				base_form.findField('NormaDisturbanceType_eid').enable();
			}
		}
		else {
			// Закрыть для редактирования все поля блока "Оценка психического развития (состояния)"
			base_form.findField('AssessmentHealth_Gnostic').setRawValue('');
			base_form.findField('AssessmentHealth_Motion').setRawValue('');
			base_form.findField('AssessmentHealth_Social').setRawValue('');
			base_form.findField('AssessmentHealth_Speech').setRawValue('');
			base_form.findField('NormaDisturbanceType_id').clearValue();
			base_form.findField('NormaDisturbanceType_uid').clearValue();
			base_form.findField('NormaDisturbanceType_eid').clearValue();

			base_form.findField('AssessmentHealth_Gnostic').disable();
			base_form.findField('AssessmentHealth_Motion').disable();
			base_form.findField('AssessmentHealth_Social').disable();
			base_form.findField('AssessmentHealth_Speech').disable();
			base_form.findField('NormaDisturbanceType_id').disable();
			base_form.findField('NormaDisturbanceType_uid').disable();
			base_form.findField('NormaDisturbanceType_eid').disable();
		}
	},
	checkForCostPrintPanel: function() {
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();

		this.CostPrintPanel.hide();
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		if (base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 && !Ext.isEmpty(base_form.findField('EvnCostPrint_setDT').getValue()) && getRegionNick().inlist(['perm', 'kz', 'ufa'])) {
			this.CostPrintPanel.show();
			// поля обязтаельные
			base_form.findField('EvnCostPrint_setDT').setAllowBlank(false);
			base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	openDispAppointEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		var formParams = new Object();
		var grid = this.DispAppointGrid.getGrid();
		var params = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('DispAppoint_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			formParams = record.data;

			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		params.action = action;
		params.EvnPLDisp_consDate = this.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue();
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.DispAppointData != 'object' ) {
				return false;
			}

			data.DispAppointData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.DispAppointData.DispAppoint_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.DispAppointData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.DispAppointData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('DispAppoint_id') ) {
					grid.getStore().removeAll();
				}

				data.DispAppointData.DispAppoint_id = -swGenTempId(grid.getStore());
				grid.getStore().loadData([ data.DispAppointData ], true);
			}
		}.createDelegate(this);

		params.formMode = 'local';
		params.formParams = formParams;

		getWnd('swDispAppointEditForm').show(params);
	},
	deleteGridRecord: function(object) {
		var wnd = this;

		if ( this.action == 'view' ) {
			return false;
		}

		if ( typeof object != 'string' || !(object.inlist([ 'DispAppoint' ])) ) {
			return false;
		}

		var grid = null;
		var question = lang['udalit'];

		switch(object) {
			case 'DispAppoint':
				question = 'Удалить назначение?';
				grid = this.DispAppointGrid.getGrid();
				break;
		}

		var idField = object + '_id';
		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
									return false;
								}
								else {
									return true;
								}
							});
							break;
					}

					if ( grid.getStore().getCount() == 0 ) {
						// LoadEmptyRow(grid);
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	show: function() {
		sw.Promed.swEvnPLDispTeenInspectionPredSecEditWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if (!arguments[0] || (Ext.isEmpty(arguments[0].EvnPLDispTeenInspection_fid) && Ext.isEmpty(arguments[0].EvnPLDispTeenInspection_id)))
		{
			Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { win.hide(); } );
			return false;
		}

		win.getLoadMask(LOAD_WAIT).show();

		this.restore();
		this.center();
		this.maximize();

		var form = this.EvnPLDispTeenInspectionFormPanel;
		var base_form = form.getForm();
		base_form.reset();
		base_form.findField('HealthKind_id').fireEvent('change', base_form.findField('HealthKind_id'), base_form.findField('HealthKind_id').getValue());
		this.checkForCostPrintPanel();

		win.dopDispInfoConsentGrid.removeAll();
		win.EvnVizitDispDopPanel.removeAll();
		win.EvnUslugaDispDopPanel.removeAll();

		win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').setRawValue('');
		win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').setRawValue('');

		this.PersonFirstStageAgree = false; // Пациент не согласен на этап диспансеризации
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		
		form.getForm().setValues(arguments[0]);
		
		if (arguments[0].action)
		{
			this.action = arguments[0].action;
		}
		
		if (arguments[0].Year)
		{
			this.Year = arguments[0].Year;
		}
		else 
		{
			this.Year = null;
		}
		
		if (arguments[0].callback)
		{
			this.callback = arguments[0].callback;
		}

		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}
		
		// определенный медстафффакт
		if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 )
		{
			this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		}
		else
		{
			this.UserMedStaffFact_id = null;
			// если в настройках есть medstafffact, то имеем список мест работы
			if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 )
			{
				this.UserMedStaffFacts = Ext.globalOptions.globals['medstafffact'];
				this.UserLpuSections = Ext.globalOptions.globals['lpusection'];			
			}
			else
			{				
				// свободный выбор врача и отделения
				this.UserMedStaffFacts = null;
				this.UserLpuSections = null;
			}
		}
		
		// определенный LpuSection
		if ( arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0 )
		{
			this.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}
		else
		{
			this.UserLpuSection_id = null;
			// если в настройках есть lpusection, то имеем список мест работы
			if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 )
			{
				this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
			}
			else
			{				
				// свободный выбор врача и отделения
				this.UserLpuSectons = null;
			}
		}

		
		//Проверяем возможность редактирования документа
		if (this.action === 'edit' && arguments[0].EvnPLDispTeenInspection_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: arguments[0].EvnPLDispTeenInspection_id,
					MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
					ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
				},
				success: function (response, options) {
					if (!Ext.isEmpty(response.responseText)) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						if (response_obj.success == false) {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_zagruzke_dannyih_formyi']);
							this.action = 'view';
						}

						if (response_obj.Alert_Msg) {
							sw.swMsg.alert(lang['vnimanie'], response_obj.Alert_Msg);
						}
					}

					//вынес продолжение show в отдельную функцию, т.к. иногда callback приходит после выполнения логики
					this.onShow();
				}.createDelegate(this),
				url: '/?c=Evn&m=CommonChecksForEdit'
			});
		} else {
			this.onShow();
		}
	},
	
	onShow: function(){
		
		var win = this;
		var form = this.EvnPLDispTeenInspectionFormPanel;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var EvnPLDispTeenInspection_id = base_form.findField('EvnPLDispTeenInspection_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();
		var DispClass_id = base_form.findField('DispClass_id').getValue();

		base_form.findField('EvnPLDispTeenInspection_RepFlag').hideContainer();
		
		base_form.findField('HeightAbnormType_YesNo').setValue(1);
		base_form.findField('WeightAbnormType_YesNo').setValue(1);

		var orgcombo = base_form.findField('Org_id');
		if (!Ext.isEmpty(orgcombo.getValue())) {
			orgcombo.getStore().load({
				params: {
					Object:'Org',
					Org_id: orgcombo.getValue(),
					Org_Name:''
				},
				callback: function()
				{
					orgcombo.setValue(orgcombo.getValue());
					orgcombo.focus(true, 500);
					orgcombo.fireEvent('change', orgcombo);
				}
			});
		}
				
		base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
		base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());
		base_form.findField('EducationInstitutionType_id').fireEvent('change', base_form.findField('EducationInstitutionType_id'), base_form.findField('EducationInstitutionType_id').getValue());
		
		win.DopDispInfoConsentPanel.setTitle(lang['informirovannoe_dobrovolnoe_soglasie']);
		
		if (win.action == 'edit') {
			win.setTitle(lang['predvaritelnyiy_osmotr_nesovershennoletnego_-_2_etap_redaktirovanie']);
		} else {
			win.setTitle(lang['predvaritelnyiy_osmotr_nesovershennoletnego_-_2_etap_prosmotr']);
		}
		
		inf_frame_is_loaded = false;

		this.DispAppointGrid.getGrid().getStore().removeAll();

		this.findById('EPLDTIPRESEvnVizitDispDopGrid').getStore().removeAll();
		this.findById('EPLDTIPRESEvnVizitDispDopGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('EPLDTIPRESEvnVizitDispDopGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('EPLDTIPRESEvnVizitDispDopGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLDTIPRESEvnVizitDispDopGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLDTIPRESEvnVizitDispDopGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPLDTIPRESEvnUslugaDispDopGrid').getStore().removeAll();
		this.findById('EPLDTIPRESEvnUslugaDispDopGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('EPLDTIPRESEvnUslugaDispDopGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('EPLDTIPRESEvnUslugaDispDopGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLDTIPRESEvnUslugaDispDopGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLDTIPRESEvnUslugaDispDopGrid').getTopToolbar().items.items[3].disable();

		this.PersonInfoPanel.load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				win.getLoadMask().hide();
				inf_frame_is_loaded = true; 
				
				var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
				var age = win.PersonInfoPanel.getFieldValue('Person_Age');
				base_form.findField('Server_id').setValue(win.PersonInfoPanel.getFieldValue('Server_id'));
				base_form.findField('PersonEvn_id').setValue(win.PersonInfoPanel.getFieldValue('PersonEvn_id'));
				
				if ( sex_id == 1 ) {
					// скрыть поля для девочек
					base_form.findField('AssessmentHealth_Ma').hideContainer();
					base_form.findField('AssessmentHealth_Me').hideContainer();
					win.findById('EPLDTIPRES_menarhe').hide();
					win.findById('EPLDTIPRES_menses').hide();
				}
				else {
					base_form.findField('AssessmentHealth_Ma').showContainer();
					base_form.findField('AssessmentHealth_Me').showContainer();
					win.findById('EPLDTIPRES_menarhe').show();
					win.findById('EPLDTIPRES_menses').show();
				}
				
				if ( sex_id == 2 ) {
					// скрыть поля для мальчиков
					base_form.findField('AssessmentHealth_Fa').hideContainer();
				}
				else {
					base_form.findField('AssessmentHealth_Fa').showContainer();
				}
				
				if (win.action == 'edit') {
					win.enableEdit(true);
				} else {
					win.enableEdit(false);
				}
				
				base_form.findField('EvnPLDispTeenInspection_IsMobile').fireEvent('check', base_form.findField('EvnPLDispTeenInspection_IsMobile'), base_form.findField('EvnPLDispTeenInspection_IsMobile').getValue());

				if (!Ext.isEmpty(EvnPLDispTeenInspection_id)) {
					win.loadForm(EvnPLDispTeenInspection_id);
				}
				else {
					win.setTitle(lang['predvaritelnyiy_osmotr_nesovershennoletnego_-_2_etap_dobavlenie']);
						
					// Грузим текущую дату
					setCurrentDateTime({
						callback: function(date) {
							win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').fireEvent('change', win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate'), date);
						},
						dateField: win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate'),
						loadMask: true,
						setDate: true,
						setDateMaxValue: true,
						windowId: win.id
					});
                    setCurrentDateTime({
						callback: function(date) {
							win.setAgeGroupDispCombo(date, null, true);
						},
                        dateField: win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate'),
                        loadMask: true,
                        setDate: true,
                        setDateMaxValue: true,
                        windowId: win.id
                    });
					
					// прогрузить данные с 1 этапа
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_zagruzke_dannyih_kartyi_1_etapa_proizoshla_oshibka'], function() {
								current_window.hide();
							});
							return false;
						},
						params: {
							EvnPLDispTeenInspection_id: base_form.findField('EvnPLDispTeenInspection_fid').getValue()
						},
						success: function(response, options) {
							if (response.responseText)
							{
								var answer = Ext.util.JSON.decode(response.responseText);
								
								if (answer && answer[0]) {
									answer = answer[0];
								}
								
								if (answer.EvnPLDispTeenInspection_id)
								{
									base_form.findField('PayType_id').setValue(answer.PayType_id);
									base_form.findField('EvnPLDispTeenInspection_firSetDate').setValue(answer.EvnPLDispTeenInspection_setDate);
									base_form.findField('EvnPLDispTeenInspection_firSetDate').fireEvent('change', base_form.findField('EvnPLDispTeenInspection_firSetDate'), base_form.findField('EvnPLDispTeenInspection_firSetDate').getValue());
									base_form.findField('AssessmentHealth_Weight').setValue(answer.AssessmentHealth_Weight);
									base_form.findField('AssessmentHealth_Height').setValue(answer.AssessmentHealth_Height);
									base_form.findField('WeightAbnormType_YesNo').setValue(answer.WeightAbnormType_YesNo);
									base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
									base_form.findField('WeightAbnormType_id').setValue(answer.WeightAbnormType_id);
									base_form.findField('HeightAbnormType_YesNo').setValue(answer.HeightAbnormType_YesNo);
									base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());
									base_form.findField('HeightAbnormType_id').setValue(answer.HeightAbnormType_id);
									base_form.findField('AssessmentHealth_P').setValue(answer.AssessmentHealth_P);
									base_form.findField('AssessmentHealth_Ax').setValue(answer.AssessmentHealth_Ax);
									base_form.findField('AssessmentHealth_Fa').setValue(answer.AssessmentHealth_Fa);
									base_form.findField('AssessmentHealth_Ma').setValue(answer.AssessmentHealth_Ma);
									base_form.findField('AssessmentHealth_Me').setValue(answer.AssessmentHealth_Me);
									base_form.findField('AssessmentHealth_Years').setValue(answer.AssessmentHealth_Years);
									base_form.findField('AssessmentHealth_Month').setValue(answer.AssessmentHealth_Month);
									base_form.findField('AssessmentHealth_IsRegular').setValue(answer.AssessmentHealth_IsRegular);
									base_form.findField('AssessmentHealth_IsIrregular').setValue(answer.AssessmentHealth_IsIrregular);
									base_form.findField('AssessmentHealth_IsAbundant').setValue(answer.AssessmentHealth_IsAbundant);
									base_form.findField('AssessmentHealth_IsModerate').setValue(answer.AssessmentHealth_IsModerate);
									base_form.findField('AssessmentHealth_IsScanty').setValue(answer.AssessmentHealth_IsScanty);
									base_form.findField('AssessmentHealth_IsPainful').setValue(answer.AssessmentHealth_IsPainful);
									base_form.findField('AssessmentHealth_IsPainless').setValue(answer.AssessmentHealth_IsPainless);
									base_form.findField('HealthKind_id').setValue(answer.HealthKind_id);
									base_form.findField('HealthGroupType_oid').setValue(answer.HealthGroupType_oid);
									base_form.findField('HealthGroupType_id').setValue(answer.HealthGroupType_id);
								}
							}
						},
						url: '/?c=EvnPLDispTeenInspection&m=loadEvnPLDispTeenInspectionEditForm'
					});
				}
				
				win.buttons[0].focus();
			} 
		});
		
		form.getForm().clearInvalid();
		this.doLayout();
	},
	
	loadForm: function(EvnPLDispTeenInspection_id) {
	
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		win.getLoadMask(LOAD_WAIT).show();

		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				swEvnPLDispTeenInspectionPredSecEditWindow.hide();
			},
			params: {
				EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id,
				archiveRecord: win.archiveRecord
			},
			success: function() {
				win.getLoadMask().hide();
				
				if ( base_form.findField('accessType').getValue() == 'view' ) {
					win.action = 'view';
					win.enableEdit(false);
				}

				if ( getRegionNick() == 'perm' && base_form.findField('EvnPLDispTeenInspection_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnPLDispTeenInspection_IndexRepInReg').getValue()) > 0 ) {
					base_form.findField('EvnPLDispTeenInspection_RepFlag').showContainer();

					if ( parseInt(base_form.findField('EvnPLDispTeenInspection_IndexRep').getValue()) >= parseInt(base_form.findField('EvnPLDispTeenInspection_IndexRepInReg').getValue()) ) {
						base_form.findField('EvnPLDispTeenInspection_RepFlag').setValue(true);
					}
					else {
						base_form.findField('EvnPLDispTeenInspection_RepFlag').setValue(false);
					}
				}

				base_form.findField('HealthKind_id').fireEvent('change', base_form.findField('HealthKind_id'), base_form.findField('HealthKind_id').getValue());
				win.checkForCostPrintPanel();
				
				// загрузка грида осмотров
				win.EvnVizitDispDopPanel.getStore().load({
					params: { EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id },
					callback: function() {
						win.reloadDopDispInfoConsentGrid();
						if ( win.EvnVizitDispDopPanel.getStore().getCount() == 0 )
							LoadEmptyRow(win.EvnVizitDispDopPanel);
					}
				});

				// загрузка грида обследований
				win.EvnUslugaDispDopPanel.getStore().load({
					params: { EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id },
					callback: function() {
						win.reloadDopDispInfoConsentGrid();
						if ( win.EvnUslugaDispDopPanel.getStore().getCount() == 0 )
							LoadEmptyRow(win.EvnUslugaDispDopPanel);
					}
				});

				if (getRegionNick() != 'kz') {
					win.DispAppointGrid.loadData({
						params: {EvnPLDisp_id: EvnPLDispTeenInspection_id, object: 'EvnPLDispOrp'},
						globalFilters: {EvnPLDisp_id: EvnPLDispTeenInspection_id},
						noFocusOnLoad: true
					});
				}
				
				if ( base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 ) {
				//Проверка на Группу здоровья
					base_form.findField('HealthKind_id').setAllowBlank(false);
					base_form.findField('HealthKind_id').validate();
				}else{
					base_form.findField('HealthKind_id').setAllowBlank(true);
					base_form.findField('HealthKind_id').validate();
				}
				
				var orgcombo = base_form.findField('Org_id');
				if (!Ext.isEmpty(orgcombo.getValue())) {
					orgcombo.getStore().load({
						params: {
							Object:'Org',
							Org_id: orgcombo.getValue(),
							Org_Name:''
						},
						callback: function()
						{
							orgcombo.setValue(orgcombo.getValue());
							orgcombo.focus(true, 500);
							orgcombo.fireEvent('change', orgcombo);
						}
					});
				}
				
				var lpucombo = base_form.findField('Lpu_mid');
				if (!Ext.isEmpty(lpucombo.getValue())) {
					lpucombo.getStore().load({
						params: {
							OrgType: 'lpu',
							Lpu_oid: lpucombo.getValue()
						},
						callback: function()
						{
							lpucombo.setValue(lpucombo.getValue());
							lpucombo.focus(true, 500);
							lpucombo.fireEvent('change', lpucombo);
						}
					});
				}
						
				base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
				base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());
				base_form.findField('EducationInstitutionType_id').fireEvent('change', base_form.findField('EducationInstitutionType_id'), base_form.findField('EducationInstitutionType_id').getValue());
				base_form.findField('EvnPLDispTeenInspection_IsMobile').fireEvent('check', base_form.findField('EvnPLDispTeenInspection_IsMobile'), base_form.findField('EvnPLDispTeenInspection_IsMobile').getValue());

				win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').setValue(base_form.findField('EvnPLDispTeenInspection_consDate').getValue());
				win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').fireEvent('change', win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate'), win.findById('EPLDTIPRES_EvnPLDispTeenInspection_consDate').getValue());

                win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').setValue(base_form.findField('EvnPLDispTeenInspection_setDate').getValue());
				win.setAgeGroupDispCombo(win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').getValue(), null, false);
				win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').fireEvent('change', win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate'), win.findById('EPLDTIPRES_EvnPLDispTeenInspection_setDate').getValue());
			},
			url: '/?c=EvnPLDispTeenInspection&m=loadEvnPLDispTeenInspectionEditForm'
		});
		
	},
	title: '',
	width: 800
}
);
