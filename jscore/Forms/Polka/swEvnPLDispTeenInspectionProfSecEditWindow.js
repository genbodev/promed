/**
* swEvnPLDispTeenInspectionProfSecEditWindow - окно редактирования/добавления талона по дополнительной диспансеризации
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
* @comment		Префикс для id компонентов EPLDTIPROS (EvnPLDispTeenInspectionProfSecEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispTeenInspectionProfSecEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispTeenInspectionProfSecEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispTeenInspectionProfSecEditWindow.js',
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
			title = langs(' согласия');
		}
		else{ //отказ
			pattern = 'EvnPLDispTeenInspectionOtkaz.rptdesign';
			pattern_dep = 'EvnPLDispTeenInspectionOtkaz_Deputy.rptdesign';
			title = langs(' отказа');
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
				title: langs('Вид') + title,
				msg:langs('Выберите вид') + title,
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
					var evnvizitdispdop_grid = win.findById('EPLDTIPROSEvnVizitDispDopGrid');

					if (!evnvizitdispdop_grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = evnvizitdispdop_grid.getSelectionModel().getSelected();
					var EvnVizitDispDop_id = selected_record.get('EvnVizitDispDop_id');
					
					win.getLoadMask().show();
					Ext.Ajax.request({
						url: '/?c=EvnPLDispTeenInspection&m=deleteEvnVizitDispDop',
						params: {EvnVizitDispDop_id: EvnVizitDispDop_id},
						failure: function(response, options) {
							win.getLoadMask().hide();
						},
						success: function(response, action) {
							win.getLoadMask().hide();
							evnvizitdispdop_grid.getStore().reload({
								callback: function() {
									win.reloadDopDispInfoConsentGrid();
								}
							});
						}
					});
					
					// удаляем соответствующую строку из грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
					var index = win.EvnDiagAndRecomendationPanel.getGrid().getStore().findBy(function(rec) {
						if ( rec.get('EvnVizitDispDop_id') == EvnVizitDispDop_id ) {
							return true;
						}
						else {
							return false;
						}
					});
					
					if ( index >= 0 ) {
						var record = win.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(index);
						if (record) {
							win.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(record);
						}
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

					/*if ( evnvizitdispdop_grid.getStore().getCount() == 0 )
						LoadEmptyRow(evnvizitdispdop_grid);*/
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить осмотр врача-специалиста?'),
			title: langs('Вопрос')
		})
	},
	deleteEvnUslugaDispDop: function() {
		var win = this;
		
		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId)
				{
					var evnuslugadispdop_grid = win.findById('EPLDTIPROSEvnUslugaDispDopGrid');

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
			msg: langs('Удалить лабораторное исследование?'),
			title: langs('Вопрос')
		})
	},
	draggable: true,
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
		response.Sex_Name = personinfo.getFieldValue('Sex_Name');
		response.ua_name = personinfo.getFieldValue('Person_RAddress');
		response.pa_name = personinfo.getFieldValue('Person_PAddress');
		response.AgeGroupDisp_Name = base_form.findField('AgeGroupDisp_id').getFieldValue('AgeGroupDisp_Name');
		response.OrgExist = base_form.findField('OrgExist').getValue();
		response.EvnPLDispTeenInspection_setDate = typeof base_form.findField('EvnPLDispTeenInspection_setDate').getValue() == 'object' ? base_form.findField('EvnPLDispTeenInspection_setDate').getValue() : Date.parseDate(base_form.findField('EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y');
		response.EvnPLDispTeenInspection_disDate = typeof base_form.findField('EvnPLDispTeenInspection_disDate').getValue() == 'object' ? base_form.findField('EvnPLDispTeenInspection_disDate').getValue() : Date.parseDate(base_form.findField('EvnPLDispTeenInspection_disDate').getValue(), 'd.m.Y');
		response.EvnPLDispTeenInspection_IsFinish = (base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2) ? langs('Да'):langs('Нет');
		response.EvnPLDispTeenInspection_hasDirection = null;
		if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 2) {
			response.EvnCostPrint_IsNoPrintText = langs('Отказ от справки');
		} else if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 1) {
			response.EvnCostPrint_IsNoPrintText = langs('Справка выдана');
		} else {
			response.EvnCostPrint_IsNoPrintText = '';
		}
		response.EvnCostPrint_setDT = base_form.findField('EvnCostPrint_setDT').getValue();
		response.UslugaComplex_Name = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Name');
		
		return response;
	},
	loadUslugaComplex: function() {
		var win = this;
		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm(),
			EvnPLDispTeenInspection_setDate = win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate').getValue(),
			UslugaComplex_Date = typeof EvnPLDispTeenInspection_setDate == 'object' ? Ext.util.Format.date(EvnPLDispTeenInspection_setDate, 'd.m.Y') : EvnPLDispTeenInspection_setDate;

		if (getRegionNick() == 'buryatiya') {
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.dispOnly = 1;
			base_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_id = base_form.findField('DispClass_id').getValue();
			if(swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), '31.12.' + Date.parseDate(UslugaComplex_Date, 'd.m.Y').getFullYear()) >= 3){
				UslugaComplex_Date = '31.12.' + Date.parseDate(UslugaComplex_Date, 'd.m.Y').getFullYear();
			}
			base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = UslugaComplex_Date;
			base_form.findField('UslugaComplex_id').getStore().baseParams.Person_id = base_form.findField('Person_id').getValue();
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
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

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
					win.formStatus = 'edit';
					EvnPLDispTeenInspection_form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var EvnDiagAndRecomendationGrid = this.EvnDiagAndRecomendationPanel.getGrid();

		if (
			!Ext.isEmpty(base_form.findField('HealthKind_id').getValue())
			&& base_form.findField('HealthKind_id').getValue() == 1
			&& EvnDiagAndRecomendationGrid.getStore().getCount() > 0
			&& !Ext.isEmpty(EvnDiagAndRecomendationGrid.getStore().getAt(0).get('EvnVizitDispDop_id'))
		) {
			sw.swMsg.alert(langs('Ошибка'), 'Нельзя выбрать I группу здоровья при указании диагнозов и рекомендаций по результатам диспансеризации / профосмотра');
			win.formStatus = 'edit';
			return false;
		}

		if (
			getRegionNick() == 'adygeya'
			&& base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue() == 2
			&& base_form.findField('HealthKind_id').getValue() == 1
		) {
			sw.swMsg.alert('Ошибка', 'Нельзя выбрать I группу здоровья при подозрении на ЗНО.');
			return false;
		}

		// Проверяем заполнение данных в диагнозах и рекомендациях по результатам диспансеризации / профосмотра
		// @task https://redmine.swan.perm.ru/issues/77880
		if (
			base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2 
			&& EvnDiagAndRecomendationGrid.getStore().getCount() > 0
			&& !Ext.isEmpty(EvnDiagAndRecomendationGrid.getStore().getAt(0).get('EvnVizitDispDop_id'))
			&& !getRegionNick().inlist([ 'ufa' ])
		) {
			var
				FormDataJSON,
				noRequiredData = false;

			EvnDiagAndRecomendationGrid.getStore().each(function(rec) {
				if ( Ext.isEmpty(rec.get('FormDataJSON')) ) {
					noRequiredData = true;
				}
				else {
					FormDataJSON = Ext.util.JSON.decode(rec.get('FormDataJSON'));

					if ( Ext.isEmpty(FormDataJSON.DispSurveilType_id) || Ext.isEmpty(FormDataJSON.EvnVizitDisp_IsFirstTime) || Ext.isEmpty(FormDataJSON.EvnVizitDisp_IsVMP) ) {
						noRequiredData = true;
					}
				}
			});

			if ( noRequiredData == true ) {
				sw.swMsg.alert('Ошибка', 'Не заполнены обязательные поля в разделе «Диагнозы и рекомендации по результатам диспансеризации / профосмотра».');
				win.formStatus = 'edit';
				return false;
			}
		}

		if ( Ext.isEmpty(win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').getValue()) ) {
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			win.formStatus = 'edit';
			return false;
		}

        if ( Ext.isEmpty(win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate').getValue()) ) {
            win.getLoadMask().hide();

            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate').focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });

			win.formStatus = 'edit';
            return false;
        }
		
		win.verfGroup();
		
		base_form.findField('EvnPLDispTeenInspection_consDate').setValue(typeof win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').getValue());

		base_form.findField('EvnPLDispTeenInspection_setDate').setValue(typeof win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate').getValue());

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
		params.EvnDiagAndRecomendation = Ext.util.JSON.encode(getStoreRecords(win.EvnDiagAndRecomendationPanel.getGrid().getStore()));
		
		params.AgeGroupDisp_id = base_form.findField('AgeGroupDisp_id').getValue();
		params.PayType_id = base_form.findField('PayType_id').getValue();
		params.Org_id = base_form.findField('Org_id').getValue();
		win.getLoadMask("Подождите, идет сохранение...").show();

		params.ignoreOsmotrDlit = (!Ext.isEmpty(options.ignoreOsmotrDlit) && options.ignoreOsmotrDlit === 1) ? 1 : 0;

		EvnPLDispTeenInspection_form.getForm().submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide();
				win.formStatus = 'edit';
				if (action.result)
				{
					if (action.result.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (action.result.Error_Code == 110) {
										options.ignoreOsmotrDlit = 1;
									}

									win.doSave(options);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: langs(' Продолжить сохранение?')
						});
					} else
					if (action.result.Error_Msg)
					{
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else
					{
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
					}
				}
			},
			params: params,
			success: function(result_form, action) {
				win.getLoadMask().hide();
				
				if (action.result.EvnPLDispTeenInspection_id)
				{
					base_form.findField('EvnPLDispTeenInspection_id').setValue(action.result.EvnPLDispTeenInspection_id);
					win.EvnVizitDispDopPanel.getStore().load({ 
						params: { EvnPLDispTeenInspection_id: action.result.EvnPLDispTeenInspection_id }
					});
					
					win.callback({evnPLDispTeenInspectionData: win.getDataForCallBack()});

					if (options.callback) {
						win.formStatus = 'edit';
						options.callback();
					} else {
						win.hide();
					}
				}
				else
				{
					Ext.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
				}
			}
		});
	},
	height: 570,
	id: 'EvnPLDispTeenInspectionProfSecEditWindow',
	openEvnDiagAndRecomendationEditWindow: function(action) {
		var grid = this.EvnDiagAndRecomendationPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		
		if (!record) {
			return false;
		}
		
		var params = {
			callback: function(FormDataJSON) {
				// обновляем JSON-поле.
				record.set('FormDataJSON', FormDataJSON);
				record.commit();
			},
			FormDataJSON: record.get('FormDataJSON'),
			Diag_id: record.get('Diag_id'),
			action: action
		};
		
		if (getWnd('swEvnDiagAndRecomendationEditWindow').isVisible())
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: Ext.emptyFn,
				icon: Ext.Msg.WARNING,
				msg: langs('Окно "Состояние здоровья: Редактирование" уже открыто'),
				title: ERR_WND_TIT
			});
			return false;
		}
		params.archiveRecord = this.archiveRecord;
		getWnd('swEvnDiagAndRecomendationEditWindow').show(params);
	},
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
			id: 'EPLDTIPROS_dopDispInfoConsentGrid',
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
				{ name: 'UslugaComplex_Name', type: 'string', sortable: false, header: langs('Осмотр, исследование'), id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsAgree', sortable: false, type: 'checkbox', isparams: true, header: langs('Согласие гражданина'), width: 180 }
			]
		});

		this.DispAppointGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swDispAppointEditForm',
			object: 'DispAppoint',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete' },
				{ name: 'action_refresh' },
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
						fieldLabel: langs('Услуга диспансеризации'),
						disabled: true,
						emptyText: '',
						nonDispOnly: false,
						xtype: 'swuslugacomplexnewcombo'
					}]
				}, {
					fieldLabel: langs('Повторная подача'),
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
					allowBlank: false,
					comboSubject: 'AgeGroupDisp',
					fieldLabel: langs('Возрастная группа'),
					loadParams: {params: {where: "where DispType_id = 4"}},
					hiddenName: 'AgeGroupDisp_id',
					moreFields: [
						{ name: 'AgeGroupDisp_From', mapping: 'AgeGroupDisp_From' },
						{ name: 'AgeGroupDisp_To', mapping: 'AgeGroupDisp_To' },
						{ name: 'AgeGroupDisp_monthFrom', mapping: 'AgeGroupDisp_monthFrom' },
						{ name: 'AgeGroupDisp_monthTo', mapping: 'AgeGroupDisp_monthTo' },
						{ name: 'AgeGroupDisp_begDate', mapping: 'AgeGroupDisp_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{ name: 'AgeGroupDisp_endDate', mapping: 'AgeGroupDisp_endDate', type: 'date', dateFormat: 'd.m.Y' }
					],
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					disabled: true,
					fieldLabel: langs('Обучающийся'),
					name: 'OrgExist',
					listeners: {
						'check': function(checkbox, value) {
							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
							
							if ( value == true && win.action != 'view' ) {
								base_form.findField('Org_id').setAllowBlank(false);
								// base_form.findField('Org_id').enable();
							} else {
								base_form.findField('Org_id').setAllowBlank(true);
								base_form.findField('Org_id').clearValue();
								base_form.findField('Org_id').disable();
							}
						}
					},
					xtype: 'checkbox'
				}, {
					disabled: true,
					editable: false,
					allowBlank: true,
					enableKeyEvents: true,
					fieldLabel: langs('Образовательное учреждение'),
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
					name: 'EvnPLDispTeenInspection_firSetDate',
					disabled: true,
					fieldLabel: langs('Дата начала (1 этап)'),
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				}, {
                    allowBlank: false,
                    fieldLabel: langs('Дата начала медицинского осмотра'),
                    format: 'd.m.Y',
                    id: 'EPLDTIPROS_EvnPLDispTeenInspection_setDate',
					listeners: {
						'change': function(field, newValue, oldValue) {
							win.loadUslugaComplex();

							win.setAgeGroupDispCombo(newValue, oldValue);
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
					fieldLabel: langs('Дата подписания согласия/отказа'),
					format: 'd.m.Y',
					id: 'EPLDTIPROS_EvnPLDispTeenInspection_consDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}, {
					fieldLabel: langs('Случай обслужен мобильной бригадой'),
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
					fieldLabel: langs('МО мобильной бригады'),
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
							DispClass_id: 12,
							Disp_consDate: (typeof win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y') : win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').getValue()),
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
							if (!getRegionNick().inlist([ 'ekb', 'penza' ])) {
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
			title: langs('Информированное добровольное согласие 1 этап')
		});
		
		this.EvnVizitDispDopPanel = new Ext.grid.GridPanel({
			animCollapse: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			columns: [{
				dataIndex: 'EvnVizitDispDop_setDate',
				header: langs('Дата'),
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'UslugaComplex_Name',
				header: langs('Осмотр'),
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'DopDispAlien_Name',
				header: langs('Сторонний специалист'),
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'LpuSection_Name',
				header: langs('Отделение'),
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'MedPersonal_Fio',
				header: langs('Врач'),
				hidden: false,
				id: 'autoexpand',
				resizable: true,
				sortable: true
			}, {
				dataIndex: 'Diag_Code',
				header: langs('Диагноз'),
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}],
			collapsible: true,
			frame: false,
			height: 200,
			id: 'EPLDTIPROSEvnVizitDispDopGrid',
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

					var grid = Ext.getCmp('EPLDTIPROSEvnVizitDispDopGrid');

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
								Ext.getCmp('EPLDTIPROSIsFinishCombo').focus(true, 200);
							}
							else
							{
								var usluga_grid = Ext.getCmp('EPLDTIPROSEvnUslugaDispDopGrid');
								if ( usluga_grid.getStore().getCount() > 0 )
								{
									usluga_grid.focus();
									usluga_grid.getSelectionModel().selectFirstRow();
									usluga_grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('EPLDTIPROSSaveButton').focus();
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
			title: langs('Осмотр врача-специалиста')
		});
		
		this.EvnUslugaDispDopPanel = new Ext.grid.GridPanel({
			animCollapse: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			columns: [{
				dataIndex: 'EvnUslugaDispDop_setDate',
				header: langs('Дата'),
				hidden: false,
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: false,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'UslugaComplex_Name',
				header: langs('Обследование'),
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'ExaminationPlace_Name',
				header: langs('Место выполнения'),
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'LpuSection_Name',
				header: langs('Отделение'),
				hidden: false,
				resizable: true,
				sortable: true,
				width: 130
			}, {
				dataIndex: 'MedPersonal_Fio',
				header: langs('Врач'),
				hidden: false,
				id: 'autoexpand',
				resizable: true,
				sortable: true
			}],
			frame: false,
			height: 200,
			id: 'EPLDTIPROSEvnUslugaDispDopGrid',
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

					var grid = Ext.getCmp('EPLDTIPROSEvnUslugaDispDopGrid');

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
								var vizit_grid = Ext.getCmp('EPLDTIPROSEvnVizitDispDopGrid');
								if ( vizit_grid.getStore().getCount() > 0 )
								{
									vizit_grid.focus();
									vizit_grid.getSelectionModel().selectFirstRow();
									vizit_grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('EPLDTIPROSIsFinishCombo').focus(true, 200);
								}
							}
							else
							{
								Ext.getCmp('EPLDTIPROSSaveButton').focus();
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
			title: langs('Обследования')
		});
		
		this.EvnDiagAndRecomendationPanel = new sw.Promed.ViewFrame({
			autoLoadData: false,
			editformclassname: 'swEvnDiagAndRecomendationEditForm',
			object: 'EvnDiagAndRecomendation',
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() {
					win.openEvnDiagAndRecomendationEditWindow('edit');
				}},
				{ name: 'action_view', handler: function() {
					win.openEvnDiagAndRecomendationEditWindow('view');
				}},
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh', disabled: true, hidden: true },
				{ name: 'action_print', disabled: true, hidden: true }
			],
			id: 'EPLDTIPROS_EvnDiagAndRecomendationGrid',
			dataUrl: '/?c=EvnPLDispTeenInspection&m=loadEvnDiagAndRecomendationSecGrid',
			region: 'center',
			height: 200,
			onLoadData: function() {
				this.setActionDisabled('action_edit', (!win.action.inlist(['add','edit'])));
			},
			title: langs('Диагнозы и рекомендации по результатам диспансеризации / профосмотра'),
			toolbar: true,
			stringfields: [
				{ name: 'EvnVizitDispDop_id', type: 'int', header: 'ID', key: true },
				{ name: 'Diag_id', type: 'int', hidden: true },
				{ name: 'FormDataJSON', type: 'string', hidden: true }, // данные формы "Состояние здоровья: Редактирование"
				{ name: 'UslugaComplex_Name', type: 'string', header: langs('Специальность'), width: 300 },
				{ name: 'Diag_Name', type: 'string', header: langs('Диагноз'), id: 'autoexpand' }
			]
		});
		
		this.EvnPLDispTeenInspectionMainResultsPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: langs('Общая оценка здоровья'),
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
					value: 12,
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
					title: langs('Оценка физического развития'),
					width: 600,
					items: [
						{
							fieldLabel: langs('Масса (кг)'),
							name: 'AssessmentHealth_Weight',
							decimalPrecision: 1,
							minValue: 2,
							maxValue: 500,
							xtype: 'numberfield'
						},
						{
							fieldLabel: langs('Рост (см)'),
							name: 'AssessmentHealth_Height',
							minValue: 20,
							maxValue: 275,
							xtype: 'numberfield'
						},
						{
							fieldLabel: langs('Окружность головы, см'),
							name: 'AssessmentHealth_Head',
							minValue: 6,
							maxValue: 99,
							xtype: 'numberfield'
						},
						{
							fieldLabel: langs('Отклонение (масса)'),
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
							fieldLabel: langs('Тип отклонения (масса)'),
							hiddenName: 'WeightAbnormType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						},
						{
							fieldLabel: langs('Отклонение (рост)'),
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
							fieldLabel: langs('Тип отклонения (рост)'),
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
					title: langs('Оценка психического развития (состояния)'),
					width: 600,
					items: [
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: langs('Познавательная функция (возраст развития) (мес.)'),
							minValue: 0,
							name: 'AssessmentHealth_Gnostic',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: langs('Моторная функция (возраст развития) (мес.)'),
							minValue: 0,
							name: 'AssessmentHealth_Motion',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: langs('Эмоциональная и социальная (контакт с окружающим миром) функции (возраст развития) (мес.)'),
							minValue: 0,
							name: 'AssessmentHealth_Social',
							xtype: 'numberfield'
						},
						{
							allowDecimals: false,
							allowNegative: false,
							fieldLabel: langs('Предречевое и речевое развитие (возраст развития) (мес.)'),
							minValue: 0,
							name: 'AssessmentHealth_Speech',
							xtype: 'numberfield'
						},
						{
							fieldLabel: langs('Психомоторная сфера'),
							hiddenName: 'NormaDisturbanceType_id',
							xtype: 'swnormadisturbancetypecombo'
						},
						{
							fieldLabel: langs('Интеллект'),
							hiddenName: 'NormaDisturbanceType_uid',
							xtype: 'swnormadisturbancetypecombo'
						},
						{
							fieldLabel: langs('Эмоционально-вегетативная сфера'),
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
					title: langs('Оценка полового развития'),
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
							id: 'EPLDTIPROS_menarhe',
							title: langs('Характеристика менструальной функции: menarhe'),
							width: 580,
							items: [
								{
									fieldLabel: langs('Лет'),
									minValue: 6,
									maxValue: 17,
									name: 'AssessmentHealth_Years',
									xtype: 'numberfield'
								},
								{
									fieldLabel: langs('Месяцев'),
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
							id: 'EPLDTIPROS_menses',
							title: langs('menses (характеристика)'),
							width: 580,
							items: [
								{
									boxLabel: langs('Регулярные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsRegular',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Нерегулярные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsIrregular',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Обильные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsAbundant',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Умеренные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsModerate',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Скудные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsScanty',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Болезненные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsPainful',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Безболезненные'),
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
					autoHeight: true,
					style: 'padding: 0px;',
					title: langs('Инвалидность'),
					width: 600,
					items: [
						{
							comboSubject: 'InvalidType',
							fieldLabel: langs('Инвалидность'),
							hiddenName: 'InvalidType_id',
							loadParams: {params: {where: ' where InvalidType_Code <= 3'}},
							lastQuery: '',
							xtype: 'swcommonsprcombo'
						},
						{
							fieldLabel: langs('Дата установления'),
							name: 'AssessmentHealth_setDT',
							xtype: 'swdatefield'
						},
						{
							fieldLabel: langs('Дата последнего освидетельствования'),
							name: 'AssessmentHealth_reExamDT',
							xtype: 'swdatefield'
						},
						{
							comboSubject: 'InvalidDiagType',
							fieldLabel: langs('Заболевания, обусловившие возникновение инвалидности'),
							hiddenName: 'InvalidDiagType_id',
							lastQuery: '',
							width: 300,
							xtype: 'swcommonsprcombo'
						},
						{
							autoHeight: true,
							style: 'padding: 0px;',
							title: langs('Виды нарушений'),
							width: 580,
							items: [
								{
									boxLabel: langs('Умственные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsMental',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Другие психологические'),
									hideLabel: true,
									name: 'AssessmentHealth_IsOtherPsych',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Языковые и речевые'),
									hideLabel: true,
									name: 'AssessmentHealth_IsLanguage',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Слуховые и вестибулярные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsVestibular',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Зрительные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsVisual',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Висцеральные и метаболические расстройства питания'),
									hideLabel: true,
									name: 'AssessmentHealth_IsMeals',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Двигательные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsMotor',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Уродующие'),
									hideLabel: true,
									name: 'AssessmentHealth_IsDeform',
									xtype: 'checkbox'
								},
								{
									boxLabel: langs('Общие и генерализованные'),
									hideLabel: true,
									name: 'AssessmentHealth_IsGeneral',
									xtype: 'checkbox'
								}
							],
							bodyStyle: 'padding: 5px;',
							xtype: 'fieldset'
						},
						{
							autoHeight: true,
							style: 'padding: 0px;',
							title: langs('Индивидуальная программа реабилитации ребенка инвалида'),
							width: 580,
							items: [
								{
									fieldLabel: langs('Дата назначения'),
									name: 'AssessmentHealth_ReabDT',
									xtype: 'swdatefield'
								},
								{
									comboSubject: 'RehabilitEndType',
									fieldLabel: langs('Выполнение на момент диспансеризации'),
									hiddenName: 'RehabilitEndType_id',
									lastQuery: '',
									xtype: 'swcommonsprcombo'
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
					fieldLabel: 'Подозрение на ЗНО',
					hiddenName: 'EvnPLDispTeenInspection_IsSuspectZNO',
					id: 'EPLDTIPROS_EvnPLDispTeenInspection_IsSuspectZNO',
					width: 100,
					xtype: 'swyesnocombo',
					listeners:{
						'change':function (combo, newValue, oldValue) {
							var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

							var index = combo.getStore().findBy(function (rec) {
								return (rec.get(combo.valueField) == newValue);
							});
							combo.fireEvent('select', combo, combo.getStore().getAt(index), index);

							if (base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue() == 2) {
								Ext.getCmp('EPLDTIPROS_PrintKLU').enable();
								Ext.getCmp('EPLDTIPROS_PrintOnko').enable();
							} else {
								Ext.getCmp('EPLDTIPROS_PrintKLU').disable();
								Ext.getCmp('EPLDTIPROS_PrintOnko').disable();
							}
						},
						'select':function (combo, record, idx) {
							if (record.get('YesNo_id') == 2) {
								Ext.getCmp('EPLDTIPROS_Diag_spid').showContainer();
								Ext.getCmp('EPLDTIPROS_Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]));
							} else {
								Ext.getCmp('EPLDTIPROS_Diag_spid').setValue('');
								Ext.getCmp('EPLDTIPROS_Diag_spid').hideContainer();
								Ext.getCmp('EPLDTIPROS_Diag_spid').setAllowBlank(true);
							}
						}
					}
				},
				{
					fieldLabel: 'Подозрение на диагноз',
					hiddenName: 'Diag_spid',
					id: 'EPLDTIPROS_Diag_spid',
					additQueryFilter: "(Diag_Code like 'C%' or Diag_Code like 'D0%')",
					baseFilterFn: function(rec){
						if(typeof rec.get == 'function') {
							return (rec.get('Diag_Code').substr(0,1) == 'C' || rec.get('Diag_Code').substr(0,2) == 'D0');
						} else if (rec.attributes && rec.attributes.Diag_Code) {
							return (rec.attributes.Diag_Code.substr(0,1) == 'C' || rec.attributes.Diag_Code.substr(0,2) == 'D0');
						} else {
							return true;
						}
					},
					width: 300,
					xtype: 'swdiagcombo'
				},
				{
					comboSubject: 'ProfVaccinType',
					fieldLabel: langs('Проведение профилактических прививок'),
					hiddenName: 'ProfVaccinType_id',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: langs('Группа здоровья'),
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
					fieldLabel: langs('Медицинская группа для занятий физ.культурой'),
					hiddenName: 'HealthGroupType_id',
					lastQuery: '',
					width: 300,
					xtype: 'swcommonsprcombo'
				},
				{
					fieldLabel: langs('Случай закончен'),
					value: 1,
					hiddenName: 'EvnPLDispTeenInspection_IsFinish',
					allowBlank: false,
					width: 100,
					xtype: 'swyesnocombo',
					listeners:{
						'select':function (combo, record) {
							win.verfGroup();
						},
						'change': function() {
							win.checkForCostPrintPanel();
							win.checkForPrintCardButton();
						}
					}
				}
			],
			layout: 'form',
			region: 'center'
		});

		this.CostPrintPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: langs('Справка о стоимости лечения'),
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
					fieldLabel: langs('Дата выдачи справки/отказа'),
					width: 100,
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'EvnCostPrint_setDT',
					xtype: 'swdatefield'
				},{
					fieldLabel: langs('Отказ'),
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
				// Диагнозы и рекомендации по результатам диспансеризации / профосмотра
				win.EvnDiagAndRecomendationPanel,
				// общая карта здоровья
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
				{ name: 'AgeGroupDisp_id' },
				{ name: 'OrgExist' },
				{ name: 'Org_id' },
				{ name: 'Lpu_mid' },
				{ name: 'EvnPLDispTeenInspection_IsMobile' },
				{ name: 'EvnPLDispTeenInspection_IsOutLpu' },
				{ name: 'InstitutionNatureType_id' },
				{ name: 'InstitutionType_id' },
				{ name: 'AssessmentHealth_Weight' },
				{ name: 'AssessmentHealth_Height' },
				{ name: 'AssessmentHealth_Head' },
				{ name: 'WeightAbnormType_YesNo' },
				{ name: 'WeightAbnormType_id' },
				{ name: 'HeightAbnormType_YesNo' },
				{ name: 'HeightAbnormType_id' },
				{ name: 'AssessmentHealth_Gnostic' },
				{ name: 'AssessmentHealth_Motion' },
				{ name: 'AssessmentHealth_Social' },
				{ name: 'AssessmentHealth_Speech' },
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
				{ name: 'InvalidType_id' },
				{ name: 'AssessmentHealth_setDT' },
				{ name: 'AssessmentHealth_reExamDT' },
				{ name: 'InvalidDiagType_id' },
				{ name: 'AssessmentHealth_IsMental' },
				{ name: 'AssessmentHealth_IsOtherPsych' },
				{ name: 'AssessmentHealth_IsLanguage' },
				{ name: 'AssessmentHealth_IsVestibular' },
				{ name: 'AssessmentHealth_IsVisual' },
				{ name: 'AssessmentHealth_IsMeals' },
				{ name: 'AssessmentHealth_IsMotor' },
				{ name: 'AssessmentHealth_IsDeform' },
				{ name: 'AssessmentHealth_IsGeneral' },
				{ name: 'AssessmentHealth_ReabDT' },
				{ name: 'RehabilitEndType_id' },
				{ name: 'ProfVaccinType_id' },
				{ name: 'HealthGroupType_oid' },
				{ name: 'HealthGroupType_id' },
				{ name: 'HealthKind_id' },
				{ name: 'EvnPLDispTeenInspection_IsFinish' },
				{ name: 'NormaDisturbanceType_eid' },
				{ name: 'NormaDisturbanceType_id' },
				{ name: 'NormaDisturbanceType_uid' },
				{ name: 'EvnCostPrint_setDT' },
				{ name: 'EvnCostPrint_IsNoPrint' },
				{ name: 'EvnPLDispTeenInspection_IsSuspectZNO' },
				{ name: 'Diag_spid' }
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
				id: 'EPLDTIPROS_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDTIPROS_PrintButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();
					base_form.findField('EvnPLDispTeenInspection_IsFinish').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
			},{
				handler: function() {
					this.printEvnPLDispTeenInspMedZak();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDTIPROS_PrintZak',
				tabIndex: 2408,
				text: langs('Печать мед. заключения'),
				tooltip:langs('Печать медицинского заключения о принадлежности несовершеннолетнего к медициснкой группе для занятий физической культурой')
			}, {
				hidden: true,
				handler: function() {
					this.printEvnPLDispTeenInspProf();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDTIPROS_PrintButton',
				tabIndex: 2407,
				text: langs('Печать карты мед. осмотра')
			}, {
				hidden: getRegionNick() == 'kz',
				handler: function() {
					this.printKLU();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDTIPROS_PrintKLU',
				tabIndex: 2408,
				text: 'Печать КЛУ при ЗНО'
			}, {
				hidden: getRegionNick() != 'ekb',
				handler: function() {
					this.printOnko();
				}.createDelegate(this),
				iconCls: 'print16',
				id: 'EPLDTIPROS_PrintOnko',
				tabIndex: 2409,
				text: 'Печать выписки по онкологии'
			}, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDTIPROS_CancelButton',
				tabIndex: 2410,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispTeenInspectionProfSecEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPLDispTeenInspectionProfSecEditWindow');
			var tabbar = win.findById('EPLDTIPROS_EvnPLTabbar');

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
	openEvnVizitDispDopEditWindow: function(action) {
        var win = this;
		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

		if (getWnd('swEvnVizitDispDop13SecEditWindow').isVisible())
		{
			Ext.Msg.alert(langs('Сообщение'), langs('Окно редактирования осмотра врача-специалиста уже открыто'));
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

		var selected_record = win.findById('EPLDTIPROSEvnVizitDispDopGrid').getSelectionModel().getSelected();

		if (action == 'add')
		{
			params = win.params;

			// буду собирать максимальную дату осмотра или анализов
			var max_date = false;
			
			params.EvnVizitDispDop_id = null;
			params.Record_Status = 0;
			params['EvnPLDispTeenInspection_id'] = base_form.findField('EvnPLDispTeenInspection_id').getValue();
			params['PersonEvn_id'] = base_form.findField('PersonEvn_id').getValue();
			params['Server_id'] = base_form.findField('Server_id').getValue();
			
			params['Not_Z_Group_Diag'] = false;
			
			var usedUslugaComplexCodeList = [];
			win.findById('EPLDTIPROSEvnVizitDispDopGrid').getStore().each(function(rec) {
				if ( rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else if ((action == 'edit') || (action == 'view'))
		{			
			if (!win.findById('EPLDTIPROSEvnVizitDispDopGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			if ( !selected_record.data.EvnVizitDispDop_id == null || selected_record.data.EvnVizitDispDop_id == '' )
				return;
			
			params = selected_record.data;
			params['EvnPLDispTeenInspection_id'] = base_form.findField('EvnPLDispTeenInspection_id').getValue();
			
			params['Not_Z_Group_Diag'] = false;
			
			var usedUslugaComplexCodeList = [];
			win.findById('EPLDTIPROSEvnVizitDispDopGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else
		{
			return false;
		}

		params["EvnVizitDispDop_setDate"] = base_form.findField('EPLDTIPROS_EvnPLDispTeenInspection_setDate').getValue();
		var show_params = {
			archiveRecord: this.archiveRecord,
        	action: action,
        	callback: function(data, add_flag) {
				var i;
				var vizit_fields = new Array();

				win.findById('EPLDTIPROSEvnVizitDispDopGrid').getStore().fields.eachKey(function(key, item) {
					vizit_fields.push(key);					
				});
				
				win.findById('EPLDTIPROSEvnVizitDispDopGrid').getStore().reload({
					callback: function() {
						if ( add_flag == true )
						{
							// добавляем соответствующую строку в грид "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
							if (!Ext.isEmpty(data[0].Diag_Code) && data[0].Diag_Code.substring(0, 1) != 'Z') {
								data[0].FormDataJSON = null;

								if ( win.EvnDiagAndRecomendationPanel.getGrid().getStore().getCount() == 1
									&& Ext.isEmpty(win.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0).get('EvnVizitDispDop_id'))
								) {
									win.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(win.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0));
								}

								win.EvnDiagAndRecomendationPanel.getGrid().getStore().loadData(data, true);
							}
						}
						else {
							index = -1;

							// ищем соответствующую строку в гриде "Диагнозы и рекомендации по результатам диспансеризации / профосмотра", если нет, то добавляем, иначе редактируем
							if (!Ext.isEmpty(data[0].Diag_Code) && data[0].Diag_Code.substring(0, 1) != 'Z') {
								index = win.EvnDiagAndRecomendationPanel.getGrid().getStore().findBy(function(rec) { return rec.get('EvnVizitDispDop_id') == data[0].EvnVizitDispDop_id; });
								if (index == -1)
								{
									data[0].FormDataJSON = null;

									if ( win.EvnDiagAndRecomendationPanel.getGrid().getStore().getCount() == 1
										&& Ext.isEmpty(win.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0).get('EvnVizitDispDop_id'))
									) {
										win.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(win.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(0));
									}

									win.EvnDiagAndRecomendationPanel.getGrid().getStore().loadData(data, true);
								} else {
									var record = win.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(index);
									record.set('Diag_id', data[0].Diag_id);
									record.set('Diag_Name', data[0].Diag_Name);
									record.set('UslugaComplex_Name', data[0].UslugaComplex_Name);
									record.commit();
								}
							} else {
								index = win.EvnDiagAndRecomendationPanel.getGrid().getStore().findBy(function(rec) { return rec.get('EvnVizitDispDop_id') == data[0].EvnVizitDispDop_id; });
								if (index != -1)
								{
									var record = win.EvnDiagAndRecomendationPanel.getGrid().getStore().getAt(index);
									win.EvnDiagAndRecomendationPanel.getGrid().getStore().remove(record);
								}
							}
						}

						if (getRegionNick() == 'buryatiya') {
							if (data[0].Diag_Code && data[0].Diag_Code == 'Z03.1') {
								base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').setValue(2);
							} else {
								base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').clearValue();
							}
							base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').fireEvent('change', base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO'), base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue());
						}

						win.reloadDopDispInfoConsentGrid();
					}
				});
				
        		return true;
        	},			
        	formParams: params,
        	onHide: function() {
				if(win.findById('EPLDTIPROSEvnVizitDispDopGrid').getSelectionModel().getCount()>0){
					win.findById('EPLDTIPROSEvnVizitDispDopGrid').getSelectionModel().selectFirstRow();
					win.findById('EPLDTIPROSEvnVizitDispDopGrid').getView().focusRow(0);
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
		};
		
		if (base_form.findField('EvnPLDispTeenInspection_id').getValue() > 0) {
			getWnd('swEvnVizitDispDop13SecEditWindow').show(show_params);
		} else {
			win.doSave({
				callback: function() {
					show_params.formParams['EvnPLDispTeenInspection_id'] = base_form.findField('EvnPLDispTeenInspection_id').getValue();
					getWnd('swEvnVizitDispDop13SecEditWindow').show(show_params);
				}
			});
		}
	},
	openEvnUslugaDispDopEditWindow: function(action) {
        var win = this;
		var base_form = win.EvnPLDispTeenInspectionFormPanel.getForm();

		if (getWnd('swEvnUslugaDispDop13SecEditWindow').isVisible())
		{
			Ext.Msg.alert(langs('Сообщение'), langs('Окно редактирования лабораторного исследования уже открыто'));
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
			Ext.getCmp('EPLDTIPROS_EvnPLDispTeenInspection_consDate').setValue(set_date);
		} else {
			var set_date = Date.parseDate(Ext.getCmp('EPLDTIPROS_EvnPLDispTeenInspection_consDate').getValue(), 'd.m.Y');
		}

		if (action == 'add')
		{
			params = win.params;

			params.EvnUslugaDispDop_id = swGenTempId(this.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore(), 'EvnUslugaDispDop_id');
			params.Record_Status = 0;
			
			var usedUslugaComplexCodeList = [];
			win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else if ((action == 'edit') || (action == 'view'))
		{
			if (!win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getSelectionModel().getSelected())
			{
				return false;
			}

			var selected_record = win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getSelectionModel().getSelected();
			
			if ( !selected_record.data.EvnUslugaDispDop_id == null || selected_record.data.EvnUslugaDispDop_id == '' )
				return;

			params = selected_record.data;
			
			var usedUslugaComplexCodeList = [];
			win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().each(function(rec) {
				if (rec!=selected_record && rec.data.Record_Status != 3 )
					usedUslugaComplexCodeList.push(rec.data.UslugaComplex_Code);
			});
			params['usedUslugaComplexCodeList']=usedUslugaComplexCodeList;
		}
		else
		{
			return false;
		}
		

		params["EvnUsluga_setDate"] = base_form.findField('EPLDTIPROS_EvnPLDispTeenInspection_setDate').getValue();
		getWnd('swEvnUslugaDispDop13SecEditWindow').show({
			archiveRecord: this.archiveRecord,
        	action: action,
        	callback: function(data, add_flag) {
				var i;
				var usluga_fields = new Array();

				win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().fields.eachKey(function(key, item) {
					usluga_fields.push(key);
				});
				if (add_flag == true)
        		{
					// удаляем пустую строку если она есть
					if ( win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().getCount() == 1 )
					{
						var selected_record = win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().getAt(0);
						if ( !selected_record.data.EvnUslugaDispDop_id == null || selected_record.data.EvnUslugaDispDop_id == '' )
							win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().removeAll();
					}
					
					win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().clearFilter();
					win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().loadData(data, add_flag);
					win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().filterBy(function(record) {
						if (record.data.Record_Status != 3)
						{
							return true;
						}
					});
	        	}
				else {
	        		index = win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().findBy(function(rec) { return rec.get('EvnUslugaDispDop_id') == data[0].EvnUslugaDispDop_id; });

	        		if (index == -1)
	        		{
	        			return false;
	        		}

					var record = win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().getAt(index);

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
				if ( win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().getCount() > 0 ) {
					win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getSelectionModel().selectFirstRow();
					win.findById('EPLDTIPROSEvnUslugaDispDopGrid').getView().focusRow(0);
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
			UslugaComplex_Date: win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').getValue()
		});
	},
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null
	},

	plain: true,
	resizable: true,
	printEvnPLDispTeenInspProf: function() {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var paramEvnPLTeen = base_form.findField('EvnPLDispTeenInspection_id').getValue();
        var paramDispType = base_form.findField('DispClass_id').getValue();

		// в любом случае надо сохранить, т.к. могли что то изменить на форме
		win.doSave({
			callback: function() {
				printBirt({
					'Report_FileName': 'pan_EvnPLTeenCard.rptdesign',
					'Report_Params': '&paramEvnPLTeen=' + paramEvnPLTeen + '&paramDispType=' + paramDispType,
					'Report_Format': 'pdf'
				});
			}
		});
	},
	printEvnPLDispTeenInspMedZak: function(){
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var paramEvnPLTeen = base_form.findField('EvnPLDispTeenInspection_id').getValue();

		// в любом случае надо сохранить, т.к. могли что то изменить на форме
		win.doSave({
			callback: function() {
				printBirt({
					'Report_FileName': 'pan_EvnPLTeenMedZak.rptdesign',
					'Report_Params': '&paramEvnPLTeen=' + paramEvnPLTeen,
					'Report_Format': 'pdf'
				});
			}
		});
	},
	printKLU: function() {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		
		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispTeenInspection_id').getValue();
			printBirt({
				'Report_FileName': 'CheckList_MedCareOnkoPatients.rptdesign',
				'Report_Params': '&Evn_id=' + evn_pl_id, 
				'Report_Format': 'pdf'
			});
		}
		if ( 'add' == this.action || 'edit' == this.action ) {
            this.doSave({
            	callback:print,
            	print: true
            });
        }
        else if ( 'view' == this.action ) {
            print();
        }
	},
	printOnko: function() {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var print = function() {
			var evn_pl_id = base_form.findField('EvnPLDispTeenInspection_id').getValue();
			printBirt({
				'Report_FileName': 'WritingOut_MedCareOnkoPatients.rptdesign',
				'Report_Params': '&Evn_id=' + evn_pl_id,
				'Report_Format': 'pdf'
			});
		}
		if ( 'add' == this.action || 'edit' == this.action ) {
			this.doSave({
				callback:print,
				print: true
			});
		}
		else if ( 'view' == this.action ) {
			print();
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
	setAgeGroupDispCombo: function(newSetDate, oldSetDate) {
		var win = this;
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();
		var age = -1;
		var month = -1;
		if ( !Ext.isEmpty(newSetDate) ) {
			age = swGetPersonAge(win.PersonInfoPanel.getFieldValue('Person_Birthday'), newSetDate);
			month = swGetPersonAgeMonth(win.PersonInfoPanel.getFieldValue('Person_Birthday'), newSetDate);			
		}
		
		win.onChangeAge(age, newSetDate);
	},
	checkForPrintCardButton: function() {
		var base_form = this.EvnPLDispTeenInspectionFormPanel.getForm();

		this.buttons[2].hide();
		if (base_form.findField('EvnPLDispTeenInspection_IsFinish').getValue() == 2) {
			this.buttons[2].show();
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
	show: function() {
		sw.Promed.swEvnPLDispTeenInspectionProfSecEditWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if (!arguments[0] || (Ext.isEmpty(arguments[0].EvnPLDispTeenInspection_fid) && Ext.isEmpty(arguments[0].EvnPLDispTeenInspection_id)))
		{
			Ext.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() { win.hide(); } );
			return false;
		}
		
		win.getLoadMask(LOAD_WAIT).show();

		this.formStatus = 'edit';
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
		win.EvnDiagAndRecomendationPanel.removeAll();
		win.EvnUslugaDispDopPanel.removeAll();
		Ext.getCmp('EPLDTIPROS_PrintKLU').disable();
		Ext.getCmp('EPLDTIPROS_PrintOnko').disable();

		win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').setRawValue('');
		
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
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы'), function () {
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
							sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : langs('Ошибка при загрузке данных формы'));
							this.action = 'view';
						}

						if (response_obj.Alert_Msg) {
							sw.swMsg.alert(langs('Внимание'), response_obj.Alert_Msg);
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
		base_form.findField('Diag_spid').hideContainer();
		
		base_form.findField('HeightAbnormType_YesNo').setValue(1);
		base_form.findField('WeightAbnormType_YesNo').setValue(1);

		base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
		base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());
		
		win.DopDispInfoConsentPanel.setTitle(langs('Информированное добровольное согласие'));
		
		if (win.action == 'edit') {
			win.setTitle(langs('Профилактический осмотр несовершеннолетнего - 2 этап: Редактирование'));
		} else {
			win.setTitle(langs('Профилактический осмотр несовершеннолетнего - 2 этап: Просмотр'));
		}
		
		inf_frame_is_loaded = false;

		this.EvnDiagAndRecomendationPanel.getGrid().getStore().removeAll();
		this.DispAppointGrid.getGrid().getStore().removeAll();

		this.findById('EPLDTIPROSEvnVizitDispDopGrid').getStore().removeAll();
		this.findById('EPLDTIPROSEvnVizitDispDopGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('EPLDTIPROSEvnVizitDispDopGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('EPLDTIPROSEvnVizitDispDopGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLDTIPROSEvnVizitDispDopGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLDTIPROSEvnVizitDispDopGrid').getTopToolbar().items.items[3].disable();

		this.findById('EPLDTIPROSEvnUslugaDispDopGrid').getStore().removeAll();
		this.findById('EPLDTIPROSEvnUslugaDispDopGrid').getTopToolbar().items.items[0].disable();
		if (this.action != 'view') {
			this.findById('EPLDTIPROSEvnUslugaDispDopGrid').getTopToolbar().items.items[0].enable();
		}
		this.findById('EPLDTIPROSEvnUslugaDispDopGrid').getTopToolbar().items.items[1].disable();
		this.findById('EPLDTIPROSEvnUslugaDispDopGrid').getTopToolbar().items.items[2].disable();
		this.findById('EPLDTIPROSEvnUslugaDispDopGrid').getTopToolbar().items.items[3].disable();

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
		
		this.PersonInfoPanel.load({ 
			Person_id: person_id, 
			Server_id: server_id, 
			callback: function() {
				win.getLoadMask().hide();
				inf_frame_is_loaded = true; 
				
				var sex_id = win.PersonInfoPanel.getFieldValue('Sex_id');
				base_form.findField('Server_id').setValue(win.PersonInfoPanel.getFieldValue('Server_id'));
				base_form.findField('PersonEvn_id').setValue(win.PersonInfoPanel.getFieldValue('PersonEvn_id'));
				
				if ( sex_id == 1 ) {
					// скрыть поля для девочек
					base_form.findField('AssessmentHealth_Ma').hideContainer();
					base_form.findField('AssessmentHealth_Me').hideContainer();
					win.findById('EPLDTIPROS_menarhe').hide();
					win.findById('EPLDTIPROS_menses').hide();
				}
				else {
					base_form.findField('AssessmentHealth_Ma').showContainer();
					base_form.findField('AssessmentHealth_Me').showContainer();
					win.findById('EPLDTIPROS_menarhe').show();
					win.findById('EPLDTIPROS_menses').show();
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
				base_form.findField('OrgExist').fireEvent('check', base_form.findField('OrgExist'), base_form.findField('OrgExist').getValue());

				if (!Ext.isEmpty(EvnPLDispTeenInspection_id)) {
					win.loadForm(EvnPLDispTeenInspection_id);
				}
				else {
					win.setTitle(langs('Профилактический осмотр несовершеннолетнего - 2 этап: Добавление'));
					
					// Грузим текущую дату
					setCurrentDateTime({
						callback: function(date) {
							win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').fireEvent('change', win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate'), date);
						},
						dateField: win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate'),
						loadMask: true,
						setDate: true,
						setDateMaxValue: true,
						windowId: win.id
					});
                    setCurrentDateTime({
						callback: function(date) {
							win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate').fireEvent('change', win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate'), date);
						},
                        dateField: win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate'),
                        loadMask: true,
                        setDate: true,
                        setDateMaxValue: true,
                        windowId: win.id
                    });
					
					// прогрузить данные с 1 этапа
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(langs('Ошибка'), langs('При загрузке данных карты 1 этапа произошла ошибка'), function() {
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
									base_form.findField('OrgExist').setValue(answer.OrgExist);
									base_form.findField('OrgExist').fireEvent('check', base_form.findField('OrgExist'), base_form.findField('OrgExist').getValue());
									base_form.findField('Org_id').setValue(answer.Org_id);
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
									base_form.findField('AssessmentHealth_Weight').setValue(answer.AssessmentHealth_Weight);
									base_form.findField('AssessmentHealth_Height').setValue(answer.AssessmentHealth_Height);
									base_form.findField('AssessmentHealth_Head').setValue(answer.AssessmentHealth_Head);
									base_form.findField('WeightAbnormType_YesNo').setValue(answer.WeightAbnormType_YesNo);
									base_form.findField('WeightAbnormType_YesNo').fireEvent('change', base_form.findField('WeightAbnormType_YesNo'), base_form.findField('WeightAbnormType_YesNo').getValue());
									base_form.findField('WeightAbnormType_id').setValue(answer.WeightAbnormType_id);
									base_form.findField('HeightAbnormType_YesNo').setValue(answer.HeightAbnormType_YesNo);
									base_form.findField('HeightAbnormType_YesNo').fireEvent('change', base_form.findField('HeightAbnormType_YesNo'), base_form.findField('HeightAbnormType_YesNo').getValue());
									base_form.findField('HeightAbnormType_id').setValue(answer.HeightAbnormType_id);
									base_form.findField('AssessmentHealth_Gnostic').setValue(answer.AssessmentHealth_Gnostic);
									base_form.findField('AssessmentHealth_Motion').setValue(answer.AssessmentHealth_Motion);
									base_form.findField('AssessmentHealth_Social').setValue(answer.AssessmentHealth_Social);
									base_form.findField('AssessmentHealth_Speech').setValue(answer.AssessmentHealth_Speech);
									base_form.findField('NormaDisturbanceType_id').setValue(answer.NormaDisturbanceType_id);
									base_form.findField('NormaDisturbanceType_uid').setValue(answer.NormaDisturbanceType_uid);
									base_form.findField('NormaDisturbanceType_eid').setValue(answer.NormaDisturbanceType_eid);
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
									base_form.findField('InvalidType_id').setValue(answer.InvalidType_id);
									base_form.findField('AssessmentHealth_setDT').setValue(answer.AssessmentHealth_setDT);
									base_form.findField('AssessmentHealth_reExamDT').setValue(answer.AssessmentHealth_reExamDT);
									base_form.findField('InvalidDiagType_id').setValue(answer.InvalidDiagType_id);
									base_form.findField('AssessmentHealth_IsMental').setValue(answer.AssessmentHealth_IsMental);
									base_form.findField('AssessmentHealth_IsOtherPsych').setValue(answer.AssessmentHealth_IsOtherPsych);
									base_form.findField('AssessmentHealth_IsLanguage').setValue(answer.AssessmentHealth_IsLanguage);
									base_form.findField('AssessmentHealth_IsVestibular').setValue(answer.AssessmentHealth_IsVestibular);
									base_form.findField('AssessmentHealth_IsVisual').setValue(answer.AssessmentHealth_IsVisual);
									base_form.findField('AssessmentHealth_IsMeals').setValue(answer.AssessmentHealth_IsMeals);
									base_form.findField('AssessmentHealth_IsMotor').setValue(answer.AssessmentHealth_IsMotor);
									base_form.findField('AssessmentHealth_IsDeform').setValue(answer.AssessmentHealth_IsDeform);
									base_form.findField('AssessmentHealth_IsGeneral').setValue(answer.AssessmentHealth_IsGeneral);
									base_form.findField('AssessmentHealth_ReabDT').setValue(answer.AssessmentHealth_ReabDT);
									base_form.findField('RehabilitEndType_id').setValue(answer.RehabilitEndType_id);
									base_form.findField('ProfVaccinType_id').setValue(answer.ProfVaccinType_id);
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
				swEvnPLDispTeenInspectionProfSecEditWindow.hide();
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

				if (!Ext.isEmpty(base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO')) && base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue() == 2) {
					Ext.getCmp('EPLDTIPROS_PrintKLU').enable();
					Ext.getCmp('EPLDTIPROS_PrintOnko').enable();
				} else {
					Ext.getCmp('EPLDTIPROS_PrintKLU').disable();
					Ext.getCmp('EPLDTIPROS_PrintOnko').disable();
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

				// загрузка грида "Диагнозы и рекомендации по результатам диспансеризации / профосмотра"
				win.EvnDiagAndRecomendationPanel.loadData({
					params: {
						EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id
					},
					globalFilters: {
						EvnPLDispTeenInspection_id: EvnPLDispTeenInspection_id
					},
					noFocusOnLoad: true
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
				base_form.findField('OrgExist').fireEvent('check', base_form.findField('OrgExist'), base_form.findField('OrgExist').getValue());
				base_form.findField('EvnPLDispTeenInspection_IsMobile').fireEvent('check', base_form.findField('EvnPLDispTeenInspection_IsMobile'), base_form.findField('EvnPLDispTeenInspection_IsMobile').getValue());
				
				win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').setValue(base_form.findField('EvnPLDispTeenInspection_consDate').getValue());
				win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').fireEvent('change', win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate'), win.findById('EPLDTIPROS_EvnPLDispTeenInspection_consDate').getValue());

                win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate').setValue(base_form.findField('EvnPLDispTeenInspection_setDate').getValue());
				win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate').fireEvent('change', win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate'), win.findById('EPLDTIPROS_EvnPLDispTeenInspection_setDate').getValue());
				
				base_form.findField('Diag_spid').setContainerVisible(base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue() == 2);
				base_form.findField('Diag_spid').setAllowBlank(!getRegionNick().inlist([ 'astra', 'perm' ]) || base_form.findField('EvnPLDispTeenInspection_IsSuspectZNO').getValue() != 2);
				var diag_spid = base_form.findField('Diag_spid').getValue();
				if (diag_spid) {
					base_form.findField('Diag_spid').getStore().load({
						callback:function () {
							base_form.findField('Diag_spid').getStore().each(function (rec) {
								if (rec.get('Diag_id') == diag_spid) {
									base_form.findField('Diag_spid').fireEvent('select', base_form.findField('Diag_spid'), rec, 0);
								}
							});
						},
						params:{where:"where DiagLevel_id = 4 and Diag_id = " + diag_spid}
					});
				}
			},
			url: '/?c=EvnPLDispTeenInspection&m=loadEvnPLDispTeenInspectionEditForm'
		});
		
	},
	title: '',
	width: 800
}
);
