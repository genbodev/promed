/**
* swEvnPLDispMigrantEditWindow - окно редактирования/добавления талона по освидетельствованию мигрантов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright		Copyright (c) 2016 Swan Ltd.
* @comment		Префикс для id компонентов EPLDMEF (EvnPLDispMigrantEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispMigrantEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispMigrantEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispMigrantEditWindow.js',
	draggable: true,
	getDataForCallBack: function()
	{
		var win = this;
		var base_form = win.EvnPLDispMigrantFormPanel.getForm();
		var personinfo = win.PersonInfoPanel;
		
		var response = new Object();
		
		response.EvnPLDispMigrant_id = base_form.findField('EvnPLDispMigrant_id').getValue();
		response.Person_id = base_form.findField('Person_id').getValue();
		response.Server_id = base_form.findField('Server_id').getValue();
		response.Person_Surname = personinfo.getFieldValue('Person_Surname');
		response.Person_Firname = personinfo.getFieldValue('Person_Firname');
		response.Person_Secname = personinfo.getFieldValue('Person_Secname');
		response.Person_Birthday = personinfo.getFieldValue('Person_Birthday');
		response.EvnPLDispMigrant_disDate = typeof base_form.findField('EvnPLDispMigrant_disDate').getValue() == 'object' ? base_form.findField('EvnPLDispMigrant_disDate').getValue() : Date.parseDate(base_form.findField('EvnPLDispMigrant_disDate').getValue(), 'd.m.Y');
		response.EvnPLDispMigrant_IsFinish = (base_form.findField('EvnPLDispMigrant_IsFinish').getValue() == 2) ? 'Да':'Нет';
		//response.UslugaComplex_Name = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Name');

		return response;
	},
	loadUslugaComplex: function() {
		var win = this;
		var base_form = win.EvnPLDispMigrantFormPanel.getForm();

		if (getRegionNick() == 'buryatiya') {
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().baseParams.dispOnly = 1;
			base_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_id = base_form.findField('DispClass_id').getValue();
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
		var base_form = wins.EvnPLDispMigrantFormPanel.getForm();
		if ( base_form.findField('EvnPLDispMigrant_IsFinish').getValue() == 2 ) {
			base_form.findField('ResultDispMigrant_id').setAllowBlank(false);
			base_form.findField('ResultDispMigrant_id').validate();
		}else{
			base_form.findField('ResultDispMigrant_id').setAllowBlank(true);
			base_form.findField('ResultDispMigrant_id').validate();
		}
	},
	checkEditPermission: function() {
		var win = this;
        var base_form = this.EvnPLDispMigrantFormPanel.getForm();
		if ( win.action == 'view' || Ext.isEmpty(base_form.findField('EvnPLDispMigrant_id').getValue()) ) {
			return false;
		}
		
		Ext.Ajax.request({
			url: '/?c=EvnPLDispMigrant&m=getInfectData',
			success: function(response){
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if ( response_obj && response_obj[0] && !Ext.isEmpty(response_obj[0].EvnPLDispMigrant_id) ) {
					
					this.MigrantContactPanel.setDisabled(!response_obj[0].IsInfected);
					
					base_form.findField('EvnPLDispMigran_SertHIVNumber').setDisabled(!!response_obj[0].IsHiv);
					base_form.findField('EvnPLDispMigran_SertHIVDate').setDisabled(!!response_obj[0].IsHiv);
					this.findById('EPLDMEF_HIVCertifPrintButton').setDisabled(!!response_obj[0].IsHiv);
					
					base_form.findField('EvnPLDispMigran_SertInfectNumber').setDisabled(!!response_obj[0].IsInfect);
					base_form.findField('EvnPLDispMigran_SertInfectDate').setDisabled(!!response_obj[0].IsInfect);
					this.findById('EPLDMEF_MigrantInfectionConclusionPrintButton').setDisabled(!!response_obj[0].IsInfect);
					
					base_form.findField('EvnPLDispMigran_SertNarcoNumber').setDisabled(!!response_obj[0].IsNarco);
					base_form.findField('EvnPLDispMigran_SertNarcoDate').setDisabled(!!response_obj[0].IsNarco);
					this.findById('EPLDMEF_MigrantNarcoConclusionPrintButton').setDisabled(!!response_obj[0].IsNarco);
				}
			}.createDelegate(this),
			params: {
				EvnPLDispMigrant_id: base_form.findField('EvnPLDispMigrant_id').getValue()
			}
		});
		
	},
    printEvnPLDispTeenInspProf: function() {
		var win = this;
        var base_form = this.EvnPLDispMigrantFormPanel.getForm();

		if ( win.action != 'view' ) {
			win.doSave({
				callback: function() {
					var paramEvnPLTeen = base_form.findField('EvnPLDispMigrant_id').getValue();
					var paramDispType = base_form.findField('DispClass_id').getValue();
					printBirt({
						'Report_FileName': 'pan_EvnPLTeenCard.rptdesign',
						'Report_Params': '&paramEvnPLTeen=' + paramEvnPLTeen + '&paramDispType=' + paramDispType,
						'Report_Format': 'pdf'
					});
				}
			});
		}
		else {
			var paramEvnPLTeen = base_form.findField('EvnPLDispMigrant_id').getValue();
			var paramDispType = base_form.findField('DispClass_id').getValue();
			printBirt({
				'Report_FileName': 'pan_EvnPLTeenCard.rptdesign',
				'Report_Params': '&paramEvnPLTeen=' + paramEvnPLTeen + '&paramDispType=' + paramDispType,
				'Report_Format': 'pdf'
			});
		}
    },
	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var win = this;
		var EvnPLDispMigrant_form = win.EvnPLDispMigrantFormPanel;

		var base_form = win.EvnPLDispMigrantFormPanel.getForm();

		if ( !EvnPLDispMigrant_form.getForm().isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					EvnPLDispMigrant_form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( Ext.isEmpty(win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue()) ) {
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDMEF_EvnPLDispMigrant_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		win.verfGroup();
		
		base_form.findField('EvnPLDispMigrant_consDate').setValue(typeof win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue(), 'd.m.Y') : win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue());
		/*if ( base_form.findField('EvnPLDispMigrant_IsFinish').getValue() == 2 ) {

			// считаем количество сохраненных осмотров/исследований
			var kolvo = 0;
			var kolvoAgree = 0;
			win.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
				if (!Ext.isEmpty(rec.get('DopDispInfoConsent_id'))) {
					kolvo++;
					if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
						kolvoAgree++;
					}
				}
			});
			if (kolvoAgree < kolvo) {
				sw.swMsg.alert('Ошибка', 'Случай не может быть закончен, так как заполнены не все исследования или осмотры.');
				return false;
			}
		}*/

		// При сохранении карты диспансеризации реализовать контроль: Дата оказания любой услуги (осмотра/исследования) должна быть не меньше, чем за месяц до осмотра
		// врача-терапевта. При невыполнении данного контроля выводить сообщение: "Дата любого исследования не может быть раньше, чем 1 месяц до даты осмотра врача-педиатра (ВОП)", сохранение отменить.
		var EvnUslugaDispDop_minDate, EvnUslugaDispDop_pedDate, EvnUslugaDispDop_fluDate;
		var age = win.PersonInfoPanel.getFieldValue('Person_Age');
		
		var ErrorPedMsg = 'Дата любого исследования не может быть раньше, чем 1 месяц до даты осмотра врача-педиатра (ВОП)';
		var monthPed = 1;
		if (age >= 2) {
			ErrorPedMsg = 'Дата любого исследования не может быть раньше, чем 3 месяца до даты осмотра врача-педиатра (ВОП)';
			monthPed = 3;
		}
		
		// Вытаскиваем минимальную дату услуги и дату осмотра врачом терапевтом, а также дату проведения флюорографии
		this.evnUslugaDispDopGrid.getGrid().getStore().each(function(rec) {
			if ( !Ext.isEmpty(rec.get('EvnUslugaDispDop_didDate')) ) {
				if ( rec.get('SurveyType_Code') == 16 ) {
					EvnUslugaDispDop_fluDate = rec.get('EvnUslugaDispDop_didDate');
				} else if ( rec.get('SurveyType_Code') == 27 ) {
					EvnUslugaDispDop_pedDate = rec.get('EvnUslugaDispDop_didDate');
				}
				else {
					if ( Ext.isEmpty(EvnUslugaDispDop_minDate) || EvnUslugaDispDop_minDate > rec.get('EvnUslugaDispDop_didDate') ) {
						EvnUslugaDispDop_minDate = rec.get('EvnUslugaDispDop_didDate');
					}
				}
			}
		});

		if ( getRegionNick() == 'buryatiya' && base_form.findField('EvnPLDispMigrant_IsFinish').getValue() == 2 && Ext.isEmpty(EvnUslugaDispDop_pedDate) ) {
			sw.swMsg.alert('Ошибка', 'Дата выполнения осмотра врача педиатра обязательна для заполнения.');
			return false;
		}

		if ( !Ext.isEmpty(EvnUslugaDispDop_minDate) && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_minDate < EvnUslugaDispDop_pedDate.add(Date.MONTH, -monthPed) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				},
				icon: Ext.Msg.ERROR,
				msg: ErrorPedMsg,
				title: 'Ошибка'
			});
			return false;
		}
		
		// http://redmine.swan.perm.ru/issues/21226
		// Дата исследования "Флюорография" не может быть меньше 12 месяца, чем дата осмотра врача-педиатра. При невыполнении выводить сообщение "Дата исследования 
		// "Флюорография" не может быть раньше, чем 12 месяцев до даты осмотра врача-педиатра. ОК". Сохранение отменить.
		if ( !Ext.isEmpty(EvnUslugaDispDop_fluDate) && !Ext.isEmpty(EvnUslugaDispDop_pedDate) && EvnUslugaDispDop_fluDate < EvnUslugaDispDop_pedDate.add(Date.MONTH, -12) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Дата исследования "Флюорография" не может быть раньше, чем 12 месяцев до даты осмотра врача-педиатра.',
				title: 'Ошибка'
			});
			return false;
		}

		var params = new Object();
		win.MigrantContactGrid.getGrid().getStore().clearFilter();
		params.MigrantContactJSON = Ext.util.JSON.encode(getStoreRecords(win.MigrantContactGrid.getGrid().getStore()));
		win.MigrantContactGrid.getGrid().getStore().filterBy(function(rec) {
			return (rec.get('Record_Status') != 3);
		});
		
		win.getLoadMask("Подождите, идет сохранение...").show();
		

		EvnPLDispMigrant_form.getForm().submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide()
			},
			params: params,
			success: function(result_form, action) {
				win.getLoadMask().hide()
				
				if (action.result){
					win.FileUploadPanel.listParams = {Evn_id: action.result.EvnPLDispMigrant_id};
					win.FileUploadPanel.saveChanges();
					win.callback({EvnPLDispMigrantData: win.getDataForCallBack()});

					if (options.callback) {
						options.callback();
					} else {
						win.hide();
					}
				}
				else
				{
					Ext.Msg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}
		});
	},
	height: 570,
	id: 'EvnPLDispMigrantEditWindow',
	showEvnUslugaDispDopEditWindow: function(action) {
		var base_form = this.EvnPLDispMigrantFormPanel.getForm();
		var grid = this.evnUslugaDispDopGrid.getGrid();
		var win = this;
		
		var record = grid.getSelectionModel().getSelected();
		
		if ( !record || !record.get('DopDispInfoConsent_id') ) {
			return false;
		}
		
		if (!win.action.inlist(['add','edit'])) {
			action = 'view';
		}
		
		// если опрос то открываем форму анкетирования.
		if (record.get('SurveyType_Code') == 2) {
			getWnd('swDopDispQuestionEditWindow').show({
				archiveRecord: this.archiveRecord,
				action: action,
				object: 'EvnPLDispMigrant',
				DopDispQuestion_setDate: record.get('EvnUslugaDispDop_didDate'),
				EvnPLDisp_id: base_form.findField('EvnPLDispMigrant_id').getValue(),
				EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id'),
				onHide: Ext.emptyFn,
				callback: function(qdata) {
					// обновить грид
					grid.getStore().reload();
					// сюда приходит ответ по нажатию кнопки расчёт на форме анкетирования => нужно заполнить соответсвующие поля на форме.
				}
				
			});
		// иначе форму услуги
		} else {
			var personinfo = win.PersonInfoPanel;
			
			getWnd('swEvnUslugaDispDop13EditWindow').show({
				archiveRecord: this.archiveRecord,
				action: action,
				object: 'EvnPLDispMigrant',
				DispClass_id: base_form.findField('DispClass_id').getValue(),
				OmsSprTerr_Code: personinfo.getFieldValue('OmsSprTerr_Code'),
				Person_id: personinfo.getFieldValue('Person_id'),
				Person_Birthday: personinfo.getFieldValue('Person_Birthday'),
				Person_Firname: personinfo.getFieldValue('Person_Firname'),
				Person_Secname: personinfo.getFieldValue('Person_Secname'),
				Person_Surname: personinfo.getFieldValue('Person_Surname'),
				Sex_id: personinfo.getFieldValue('Sex_id'),
				Sex_Code: personinfo.getFieldValue('Sex_Code'),
				Person_Age: personinfo.getFieldValue('Person_Age'),
				UserLpuSection_id: win.UserLpuSection_id,
				UserMedStaffFact_id: win.UserMedStaffFact_id,
				formParams: {
					DopDispInfoConsent_id: record.get('DopDispInfoConsent_id'),
					EvnVizitDispDop_pid: base_form.findField('EvnPLDispMigrant_id').getValue(),
					PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
					Server_id: base_form.findField('Server_id').getValue(),
					EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id')
				},
				DopDispInfoConsent_id: record.get('DopDispInfoConsent_id'),
				SurveyTypeLink_id: record.get('SurveyTypeLink_id'),
				SurveyType_Code: record.get('SurveyType_Code'),
				OrpDispSpec_Code: record.get('OrpDispSpec_Code'),
				SurveyType_Name: record.get('SurveyType_Name'),
				type: 'DispMigrant',
				UslugaComplex_Date: win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue(),
				onHide: Ext.emptyFn,
				callback: function(data) {
					// обновить грид!
					grid.getStore().reload();
				}
				
			});
		}		
	},
	doMigrantContact: function(action) {
		
		var grid = this.MigrantContactGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		
		switch (action) {
			case 'add':
				getWnd('swPersonSearchWindow').show({
					onClose: function() {},
					onSelect: function(person_data) {
						getWnd('swPersonSearchWindow').hide();
						// небольшие дополнения
						person_data.MigrantContact_id = -swGenTempId(grid.getStore());
						person_data.RecordStatus_Code = 0;
						person_data.Person_cid = person_data.Person_id;
						grid.getStore().loadData([person_data], true);
						return;			
					}
				});
				break;
				
			case 'edit':
			case 'view':
				if (!record) return false;
				getWnd('swPersonEditWindow').show({
					action: action,
					Person_id: record.get('Person_cid')
				});
				break;
				
			case 'del':
				if (!record) return false;
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
										return (Number(rec.get('RecordStatus_Code')) != 3);
									});
									break;
							}

							if ( grid.getStore().getCount() > 0 ) {
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'Удалить контактное лицо?',
					title: lang['vopros']
				});
				break;
		}
		
	},
	initComponent: function() {
		var win = this;
		
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			id: 'EPLDMEF_FileUploadPanel',
			win: this,
			width: 1000,
			buttonAlign: 'left',
			buttonLeftMargin: 100,
			labelWidth: 150,
			folder: 'evnmedia/',
			style: 'background: transparent',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});

		this.FilePanel = new Ext.Panel({
			title: lang['faylyi'],
			id: 'EPLDMEF_FileTab',
			border: true,
			collapsible: true,
			autoHeight: true,
			titleCollapse: true,
			animCollapse: false,
			items: [
				this.FileUploadPanel
			]
		});
		
		this.dopDispInfoConsentGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			id: 'EPLDMEF_dopDispInfoConsentGrid',
			dataUrl: '/?c=EvnPLDispMigrant&m=loadDopDispInfoConsent',
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
				{ name: 'DopDispInfoConsent_id', type: 'int', header: 'ID', key: true },
				{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyTypeLink_IsNeedUsluga', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'SurveyTypeLink_IsDel', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', sortable: false, header: 'Наименование осмотра (исследования)', id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsAgree', sortable: false, type: 'checkcolumnedit', isparams: true, header: 'Согласие на проведение', width: 180 }
			],
			onLoadData: function() {
				if ( win.action != 'view' ) {
					win.findById('EPLDMEF_DopDispInfoConsentSaveBtn').enable();
				}
				this.doLayout();
			},
			onAfterEdit: function(o) {
				if (o && o.field) {
					if (o.record.get('SurveyTypeLink_IsDel') == 2) {
						o.record.set('DopDispInfoConsent_IsAgree', false);
						o.value = false;
					}
				}
			}
		});
		
		this.evnUslugaDispDopGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', handler: function() { win.showEvnUslugaDispDopEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { win.showEvnUslugaDispDopEditWindow('view'); } },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			onLoadData: function() {
				this.setActionDisabled('action_edit', (!win.action.inlist(['add','edit'])));
				this.doLayout();
				win.checkEditPermission();
			},
			id: 'EPLDMEF_evnUslugaDispDopGrid',
			dataUrl: '/?c=EvnPLDispMigrant&m=loadEvnUslugaDispDopGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'DopDispInfoConsent_id', type: 'int', header: 'ID', key: true },
				{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'OrpDispSpec_Code', type: 'int', hidden: true },
				{ name: 'EvnUslugaDispDop_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Name', type: 'string', header: 'Наименование осмотра (исследования)', id: 'autoexpand' },
				{ name: 'EvnUslugaDispDop_ExamPlace', type: 'string', header: 'Место проведения', width: 200 },
				{ name: 'EvnUslugaDispDop_setDate', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'), header: 'Дата и время проведения', width: 200 },
				{ name: 'EvnUslugaDispDop_didDate', type: 'date', header: 'Дата выполнения', width: 100 },
				{ name: 'EvnUslugaDispDop_WithDirection', type: 'checkbox', header: 'Направление / назначение', width: 100 }
			]
		});
	
		this.PersonInfoPanel = new sw.Promed.PersonInformationPanel({
			additionalFields: [
				'UAddress_id',
				'PAddress_id',
				'DocumentType_id'
			],
			button2Callback: function(callback_data) {
				var base_form = win.EvnPLDispMigrantFormPanel.getForm();
				
				base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
				base_form.findField('Server_id').setValue(callback_data.Server_id);
				
				win.PersonInfoPanel.load( { Person_id: callback_data.Person_id, Server_id: callback_data.Server_id } );
			},
			region: 'north'
		});
		
		this.DopDispInfoConsentPanel = new sw.Promed.Panel({
			items: [{
				border: false,
				labelWidth: 230,
				layout: 'form',
				style: 'padding: 5px;',
				items: [{
					typeCode: 'int',
					useCommonFilter: true,
					width: 300,
					xtype: 'swpaytypecombo'
				}, {
					allowBlank: false,
					fieldLabel: 'Дата подписания согласия/отказа',
					format: 'd.m.Y',
					id: 'EPLDMEF_EvnPLDispMigrant_consDate',
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = win.EvnPLDispMigrantFormPanel.getForm();

							if (getRegionNick().inlist([ 'perm', 'ufa' ]) && !Ext.isEmpty(oldValue) && !Ext.isEmpty(newValue) && win.checkEvnPLDispMigrantIsSaved() && newValue.format('Y') != oldValue.format('Y')) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											win.saveDopDispInfoConsentAfterLoad = true;
											win.blockSaveDopDispInfoConsent = true;
											win.dopDispInfoConsentGrid.loadData({
												params: {
													Person_id: base_form.findField('Person_id').getValue()
													,
													DispClass_id: base_form.findField('DispClass_id').getValue()
													,
													EvnPLDispMigrant_id: base_form.findField('EvnPLDispMigrant_id').getValue()
													,
													EvnPLDispMigrant_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
												},
												globalFilters: {
													Person_id: base_form.findField('Person_id').getValue()
													,
													DispClass_id: base_form.findField('DispClass_id').getValue()
													,
													EvnPLDispMigrant_id: base_form.findField('EvnPLDispMigrant_id').getValue()
													,
													EvnPLDispMigrant_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
												},
												noFocusOnLoad: true,
												callback: function () {
													win.blockSaveDopDispInfoConsent = false;
													if (win.saveDopDispInfoConsentAfterLoad) {
														win.saveDopDispInfoConsent();
													}
													win.saveDopDispInfoConsentAfterLoad = false;
													win.setPrintActionsAvailability();
												}
											});
										} else {
											win.findById('EPLDMEF_EvnPLDispMigrant_consDate').setValue(oldValue);
										}
									},
									msg: 'При изменении даты начала медицинского осмотра введенная информация по осмотрам / исследованиям будет удалена. Изменить?',
									title: 'Подтверждение'
								});
								return false;
							}

							win.blockSaveDopDispInfoConsent = true;
							win.dopDispInfoConsentGrid.loadData({
								params: {
									 Person_id: base_form.findField('Person_id').getValue()
									,DispClass_id: base_form.findField('DispClass_id').getValue()
									,EvnPLDispMigrant_id: base_form.findField('EvnPLDispMigrant_id').getValue()
									,EvnPLDispMigrant_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
								},
								globalFilters: {
									 Person_id: base_form.findField('Person_id').getValue()
									,DispClass_id: base_form.findField('DispClass_id').getValue()
									,EvnPLDispMigrant_id: base_form.findField('EvnPLDispMigrant_id').getValue()
									,EvnPLDispMigrant_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
								},
								noFocusOnLoad: true,
								callback: function() {
									win.blockSaveDopDispInfoConsent = false;
									if (win.saveDopDispInfoConsentAfterLoad) {
										win.saveDopDispInfoConsent();
									}
									win.saveDopDispInfoConsentAfterLoad = false;
									win.setPrintActionsAvailability();
								}
							});
						}
					},
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					xtype: 'swdatefield'
				}, {
					name: 'EvnPLDispMigran_RFDateRange',
					id: 'EPLDMEF_EvnPLDispMigrant_RFDateRange',
					fieldLabel: 'Планируемый период пребывания в РФ',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield'
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
						layout: 'form',
						items: [
							new Ext.Button({
								id: 'EPLDMEF_DopDispInfoConsentSaveBtn',
								handler: function() {
									win.saveDopDispInfoConsent();
								}.createDelegate(this),
								iconCls: 'save16',
								text: BTN_FRMSAVE
							})
						]
					}, {
						border: false,
						bodyStyle: 'margin-left: 5px;',
						layout: 'form',
						items: [
							new Ext.Button({
								menu: [
									// @task https://redmine.swan.perm.ru/issues/101255
									new Ext.Action({
										id: 'EPLDMEF_AcceptancePersDataPrintButton',
										text: 'Согласие на обработку перс. данных (для мигрантов)',
										handler: function() {
											var base_form = win.EvnPLDispMigrantFormPanel.getForm();
											var Person_id = base_form.findField('Person_id').getValue();
											Ext.Ajax.request({
												url: '/?c=Person&m=savePersonLpuInfo',
												success: function(response){
													var response_obj = Ext.util.JSON.decode(response.responseText);
													if (response_obj && response_obj.Error_Msg ) {
														sw.swMsg.alert('Ошибка', 'Ошибка при сохранении согласия на обработку перс. данных');
														return false;
													} else if ( response_obj && !Ext.isEmpty(response_obj.PersonLpuInfo_id) ) {
														printBirt({
															'Report_FileName': 'Acceptance_PersData.rptdesign',
															'Report_Params': '&paramEvnPLDispMigrant_id=' + base_form.findField('EvnPLDispMigrant_id').getValue(),
															'Report_Format': 'pdf'
														});
													}
												}.createDelegate(this),
												params: {
													Person_id: Person_id,
													PersonLpuInfo_IsAgree: 2
												}
											});
										}
									}),
									// @task https://redmine.swan.perm.ru/issues/101265
									new Ext.Action({
										id: 'EPLDMEF_AcceptanceMigrantsPrintButton',
										text: 'Добровольное информированное согласие',
										handler: function() {
											var base_form = win.EvnPLDispMigrantFormPanel.getForm();
											var EvnPLDispMigrant_id = base_form.findField('EvnPLDispMigrant_id').getValue();
											printBirt({
												'Report_FileName': 'Acceptance_migrants.rptdesign',
												'Report_Params': '&paramEvnPLDispMigrant_id=' + EvnPLDispMigrant_id,
												'Report_Format': 'pdf'
											});
										}
									}),
									new Ext.Action({
										id: 'EPLDMEF_EvnPLDispMigrantHIVPrintButton',
										text: 'Информированное согласие на обследование ВИЧ',
										handler: function() {
											var base_form = win.EvnPLDispMigrantFormPanel.getForm();
											var EvnPLDispMigrant_id = base_form.findField('EvnPLDispMigrant_id').getValue();
											printBirt({
												'Report_FileName': 'Acceptance_HIV.rptdesign',
												'Report_Params': '&paramEvnPLDispMigrant_id=' + EvnPLDispMigrant_id,
												'Report_Format': 'pdf'
											});
										}
									})
								],
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
			title: 'Информированное добровольное согласие 1 этап'
		});
		
		this.EvnUslugaDispDopPanel = new sw.Promed.Panel({
			items: [
				win.evnUslugaDispDopGrid
			],
			animCollapse: true,
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: 'Маршрутная карта'
		});
		
		this.MigrantContactGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			useEmptyRecord: false,
			actions: [
				{ name: 'action_add', handler: function() { win.doMigrantContact('add'); } },
				{ name: 'action_edit', handler: function() { win.doMigrantContact('edit'); } },
				{ name: 'action_view', handler: function() { win.doMigrantContact('view'); } },
				{ name: 'action_delete', handler: function() { win.doMigrantContact('del'); } },
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			onLoadData: function() {
				this.setActionDisabled('action_edit', (!win.action.inlist(['add','edit'])));
				this.doLayout();
			},
			id: 'EPLDMEF_MigrantContactGrid',
			dataUrl: '/?c=EvnPLDispMigrant&m=loadMigrantContactGrid',
			region: 'center',
			height: 200,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'MigrantContact_id', type: 'int', header: 'ID', key: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'EvnPLDispMigrant_id', type: 'int', hidden: true },
				{ name: 'Person_cid', type: 'int', hidden: true },
				{ name: 'Person_Surname', type: 'string', header: 'Фамилия', width: 150 },
				{ name: 'Person_Firname', type: 'string', header: 'Имя', width: 150 },
				{ name: 'Person_Secname', type: 'string', header: 'Отчество', width: 150 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: 'Дата рождения', width: 100 }
			]
		});
		
		this.MigrantContactPanel = new sw.Promed.Panel({
			items: [
				win.MigrantContactGrid
			],
			animCollapse: true,
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: 'Контактные лица'
		});
		
		this.EvnPLDispMigrantMainResultsPanel = new sw.Promed.Panel({
			bodyBorder: false,
			title: 'Результат',
			border: false,
			collapsible: true,
			titleCollapse: true,
			animCollapse: false,
			buttonAlign: 'left',
			frame: false,
			bodyStyle: 'padding: 5px',
			labelAlign: 'right',
			labelWidth: 170,
			items: [{
					name: 'EvnPLDispMigrant_id',
					value: null,
					xtype: 'hidden'
				}, {
					name:'EvnPLDispMigrant_IsPaid',
					xtype:'hidden'
				}, {
					name:'EvnPLDispMigrant_IndexRep',
					xtype:'hidden'
				}, {
					name:'EvnPLDispMigrant_IndexRepInReg',
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
					value: 19,
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispMigrant_fid',
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
					name: 'EvnPLDispMigrant_setDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispMigrant_disDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispMigrant_consDate',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispMigrant_Num',
					xtype: 'hidden'
				},
				{
					fieldLabel: 'Медицинское обследование закончено',
					hiddenName: 'EvnPLDispMigrant_IsFinish',
					allowBlank: false,
					xtype: 'swyesnocombo',
					listeners:{
						'select':function (combo, record) {
							win.verfGroup();
						}
					}
				},
				{
					comboSubject: 'ResultDispMigrant',
					fieldLabel: 'Результат',
					hiddenName: 'ResultDispMigrant_id',
					tabIndex: this.tabIndexBase + 29,
					width: 350,
					xtype: 'swcommonsprcombo'
				},
				{
					autoHeight: true,
					border: true,
					style: 'margin: 10px 5px 0px 5px; padding: 5px 7px;',
					title: 'Сертификат об обследовании на ВИЧ',
					labelWidth: 70,
					width: 600,
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
								items: [{
									fieldLabel: 'Номер',
									name: 'EvnPLDispMigran_SertHIVNumber',
									tabIndex: this.tabIndexBase + 30,
									width: 150,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								width: 220,
								layout: 'form',
								items: [{
									fieldLabel: 'Дата',
									name: 'EvnPLDispMigran_SertHIVDate',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									tabIndex: this.tabIndexBase + 5,
									width: 100,
									xtype: 'swdatefield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [new Ext.Button({
									id: 'EPLDMEF_HIVCertifPrintButton',
									handler: function() {
										// @task https://redmine.swan.perm.ru/issues/101321
										win.doSave({
											callback: function() {
												var base_form = win.EvnPLDispMigrantFormPanel.getForm();

												printBirt({
													'Report_FileName': 'HIV_Certif.rptdesign',
													'Report_Params': '&paramDispMigrant=' + base_form.findField('EvnPLDispMigrant_id').getValue(),
													'Report_Format': 'pdf'
												});
											}
										});
									}.createDelegate(this),
									iconCls: 'print16',
									text: 'Печать'
								})]
							}]
					}],
					xtype: 'fieldset'
				},
				{
					autoHeight: true,
					border: true,
					style: 'margin: 10px 5px 0px 5px; padding: 5px 7px;',
					title: 'Мед. заключение об инфекционных заболеваниях',
					labelWidth: 70,
					width: 600,
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
								items: [{
									fieldLabel: 'Номер',
									name: 'EvnPLDispMigran_SertInfectNumber',
									tabIndex: this.tabIndexBase + 30,
									width: 150,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								width: 220,
								layout: 'form',
								items: [{
									fieldLabel: 'Дата',
									name: 'EvnPLDispMigran_SertInfectDate',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									tabIndex: this.tabIndexBase + 5,
									width: 100,
									xtype: 'swdatefield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [new Ext.Button({
									id: 'EPLDMEF_MigrantInfectionConclusionPrintButton',
									handler: function() {
										win.doSave({
											callback: function() {
												var base_form = win.EvnPLDispMigrantFormPanel.getForm();

												printBirt({
													'Report_FileName': 'Migrant_Infection_Conclusion.rptdesign',
													'Report_Params': '&paramEvnPLDispMigrant=' + base_form.findField('EvnPLDispMigrant_id').getValue(),
													'Report_Format': 'pdf'
												});
											}
										});
									}.createDelegate(this),
									iconCls: 'print16',
									text: 'Печать'
								})]
							}]
					}],
					xtype: 'fieldset'
				},
				{
					autoHeight: true,
					border: true,
					style: 'margin: 10px 5px 0px 5px; padding: 5px 7px;',
					title: 'Мед. заключение о наркомании',
					labelWidth: 70,
					width: 600,
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
								items: [{
									fieldLabel: 'Номер',
									name: 'EvnPLDispMigran_SertNarcoNumber',
									tabIndex: this.tabIndexBase + 30,
									width: 150,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								width: 220,
								layout: 'form',
								items: [{
									fieldLabel: 'Дата',
									name: 'EvnPLDispMigran_SertNarcoDate',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									tabIndex: this.tabIndexBase + 5,
									width: 100,
									xtype: 'swdatefield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [new Ext.Button({
									id: 'EPLDMEF_MigrantNarcoConclusionPrintButton',
									handler: function() {
										win.doSave({
											callback: function() {
												var base_form = win.EvnPLDispMigrantFormPanel.getForm();

												printBirt({
													'Report_FileName': 'Migrant_Narco_Conclusion.rptdesign',
													'Report_Params': '&paramEvnPLDispMigrant=' + base_form.findField('EvnPLDispMigrant_id').getValue(),
													'Report_Format': 'pdf'
												});
											}
										});
									}.createDelegate(this),
									iconCls: 'print16',
									text: 'Печать'
								})]
							}]
					}],
					xtype: 'fieldset'
				}
			],
			layout: 'form',
			region: 'center'
		});
		
		this.EvnPLDispMigrantFormPanel = new Ext.form.FormPanel({
			border: false,
			layout: 'form',
			region: 'center',
			autoScroll: true,					
			items: [
				// информированное добровольное согласие
				win.DopDispInfoConsentPanel,
				// маршрутная карта
				win.EvnUslugaDispDopPanel,
				// основные результаты диспансеризации
				win.EvnPLDispMigrantMainResultsPanel,
				// контактные лица
				win.MigrantContactPanel
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
				{ name: 'EvnPLDispMigrant_id' },
				{ name: 'EvnPLDispMigrant_IsPaid' },
				{ name: 'EvnPLDispMigrant_IndexRep' },
				{ name: 'EvnPLDispMigrant_IndexRepInReg' },
				{ name: 'accessType' },
				{ name: 'PersonDispOrp_id' },
				{ name: 'DispClass_id' },
				{ name: 'PayType_id' },
				{ name: 'EvnPLDispMigrant_fid' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'EvnPLDispMigrant_Num' },
				{ name: 'EvnPLDispMigrant_setDate' },
				{ name: 'EvnPLDispMigrant_consDate' },
				{ name: 'EvnPLDispMigrant_disDate' },
				{ name: 'EvnPLDispMigrant_eduDT' },
				{ name: 'Org_id' },
				{ name: 'EvnPLDispMigrant_IsFinish' },
				{ name: 'ResultDispMigrant_id' },
				{ name: 'EvnPLDispMigran_RFDateRange' },
				{ name: 'EvnPLDispMigran_SertHIVNumber' },
				{ name: 'EvnPLDispMigran_SertHIVDate' },
				{ name: 'EvnPLDispMigran_SertInfectNumber' },
				{ name: 'EvnPLDispMigran_SertInfectDate' },
				{ name: 'EvnPLDispMigran_SertNarcoNumber' },
				{ name: 'EvnPLDispMigran_SertNarcoDate' }
			]),
			url: '/?c=EvnPLDispMigrant&m=saveEvnPLDispMigrant'
		});
		
		this.EvnPLDispMigrantMainPanel = new Ext.Panel({
			border: false,
			layout: 'form',
			region: 'center',
			autoScroll: true,					
			items: [
				win.EvnPLDispMigrantFormPanel,
				// файлы
				win.FilePanel
			]
		});
		
		Ext.apply(this, {
			items: [
				// паспортная часть человека
				win.PersonInfoPanel,
				win.EvnPLDispMigrantMainPanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EPLDMEF_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDMEF_CancelButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var base_form = win.EvnPLDispMigrantFormPanel.getForm();
					base_form.findField('EvnPLDispMigrant_IsFinish').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
            }, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDMEF_CancelButton',
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispMigrantEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPLDispMigrantEditWindow');
			var tabbar = win.findById('EPLDMEF_EvnPLTabbar');

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
	params: {
		EvnVizitPL_setDate: null,
		LpuSection_id: null,
		MedPersonal_id: null
	},

	plain: true,
	resizable: true,
	checkEvnPLDispMigrantIsSaved: function() {
		var base_form = this.EvnPLDispMigrantFormPanel.getForm();
		if (Ext.isEmpty(base_form.findField('EvnPLDispMigrant_id').getValue())) {
			// дисаблим все разделы кроме информированного добровольного согласия, а также основную кнопки сохранить и печать
			this.EvnUslugaDispDopPanel.collapse();
			this.EvnUslugaDispDopPanel.disable();
			this.EvnPLDispMigrantMainResultsPanel.collapse();
			this.EvnPLDispMigrantMainResultsPanel.disable();
			this.MigrantContactPanel.collapse();
			this.MigrantContactPanel.disable();
			this.FilePanel.collapse();
			this.FilePanel.disable();
			this.buttons[0].hide();
			this.buttons[1].hide();
			this.buttons[2].hide();
			//this.DopDispInfoConsentPanel.items.items[2].items.items[1].disable(); //Закрываем кнопку "Печать"
			return false;
		} else {
			this.EvnUslugaDispDopPanel.expand();
			this.EvnUslugaDispDopPanel.enable();
			this.EvnPLDispMigrantMainResultsPanel.expand();
			this.EvnPLDispMigrantMainResultsPanel.enable();
			this.MigrantContactPanel.expand();
			this.MigrantContactPanel.enable();
			this.FilePanel.expand();
			this.FilePanel.enable();
		
			if (this.action != 'view') {
				this.buttons[0].show();
			}
			this.buttons[1].show();
			this.buttons[2].show();
			//this.DopDispInfoConsentPanel.items.items[2].items.items[1].enable(); //Открываем кнопку "Печать"
			return true;
		}
	},
	setPrintActionsAvailability: function() {
		var disableButtons = Ext.isEmpty(this.EvnPLDispMigrantFormPanel.getForm().findField('EvnPLDispMigrant_id').getValue());

		Ext.getCmp('EPLDMEF_AcceptancePersDataPrintButton').setDisabled(disableButtons);
		Ext.getCmp('EPLDMEF_AcceptanceMigrantsPrintButton').setDisabled(disableButtons);
		
		var hiv_agree = 0;
		this.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
			if (rec.get('SurveyType_Code').toString().inlist(['159']) && rec.get('DopDispInfoConsent_IsAgree') == true) {
				hiv_agree++;
			}
		});
		
		Ext.getCmp('EPLDMEF_EvnPLDispMigrantHIVPrintButton').setDisabled(disableButtons || hiv_agree != 1);
	},
	saveDopDispInfoConsent: function(options) {
		var win = this;
		var btn = win.findById('EPLDMEF_DopDispInfoConsentSaveBtn');
		if ( btn.disabled || win.action == 'view' ) {
			return false;
		}

		if (win.blockSaveDopDispInfoConsent) {
			win.saveDopDispInfoConsentAfterLoad = true;
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		btn.disable();

		var base_form = win.EvnPLDispMigrantFormPanel.getForm();

		win.getLoadMask('Сохранение информированного добровольного согласия').show();
		// берём все записи из грида и посылаем на сервер, разбираем ответ
		// на сервере создать саму карту EvnPLDispMigrant, если EvnPLDispMigrant_id не задано, сохранить её информ. согласие DopDispInfoConsent, вернуть EvnPLDispMigrant_id
		var grid = win.dopDispInfoConsentGrid.getGrid();
		var params = {};

		if ( Ext.isEmpty(base_form.findField('PayType_id').getValue()) ) {
			btn.enable();
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('PayType_id').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		if ( Ext.isEmpty(win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue()) ) {
			btn.enable();
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDMEF_EvnPLDispMigrant_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var xdate = new Date(2015,0,1);
		if ( getRegionNick().inlist([ 'kareliya' ]) && win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue() >= xdate ) {
			// отказов быть не должно
			var IsOtkaz = false;
			win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
				if (rec.get('DopDispInfoConsent_IsAgree') != true) {
					IsOtkaz = true;
				}
			});

			if (IsOtkaz && !options.ignoreRefuse) {
				btn.enable();
				win.getLoadMask().hide();
				sw.swMsg.show({
					buttons: {yes: 'Сохранить', cancel: 'Отмена'},
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							win.saveDopDispInfoConsent({
								ignoreRefuse: true
							});
						}
					},
					msg: 'Карта подлежит оплате только при проведении всех осмотров / исследований. Продолжить сохранение?',
					title: 'Подтверждение'
				});
				return false;
			}
		}

		params.EvnPLDispMigrant_consDate = (typeof win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue(), 'd.m.Y') : win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue());
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.EvnPLDispMigrant_id = base_form.findField('EvnPLDispMigrant_id').getValue();
		params.PersonDispOrp_id = base_form.findField('PersonDispOrp_id').getValue();
		params.EvnPLDispMigrant_fid = base_form.findField('EvnPLDispMigrant_fid').getValue();
		params.DispClass_id = base_form.findField('DispClass_id').getValue();
		params.PayType_id = base_form.findField('PayType_id').getValue();
		params.EvnPLDispMigran_RFDateRange = base_form.findField('EvnPLDispMigran_RFDateRange').getRawValue();

		params.DopDispInfoConsentData = Ext.util.JSON.encode(getStoreRecords( grid.getStore(), {
			exceptionFields: [
				'SurveyType_Name'
			]
		}));

		Ext.Ajax.request(
		{
			url: '/?c=EvnPLDispMigrant&m=saveDopDispInfoConsent',
			params: params,
			failure: function(response, options)
			{
				btn.enable();
				win.getLoadMask().hide();
			},
			success: function(response, action)
			{
				btn.enable();
				win.getLoadMask().hide();
				if (response.responseText)
				{
					var answer = Ext.util.JSON.decode(response.responseText);
					if (answer.success && answer.EvnPLDispMigrant_id > 0)
					{
						base_form.findField('EvnPLDispMigrant_id').setValue(answer.EvnPLDispMigrant_id);
						win.checkEvnPLDispMigrantIsSaved();
						// запускаем callback чтобы обновить грид в родительском окне
						win.callback({EvnPLDispMigrantData: win.getDataForCallBack()});
						// обновляем грид
						grid.getStore().load({
							params: {
								EvnPLDispMigrant_id: answer.EvnPLDispMigrant_id
							}
						});

						win.loadForm(answer.EvnPLDispMigrant_id);
						win.setPrintActionsAvailability();
					}
				}
			}
		});
	},
	show: function() {
		sw.Promed.swEvnPLDispMigrantEditWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0])
		{
			Ext.Msg.alert('Сообщение', 'Неверные параметры');
			return false;
		}
		
		var win = this;
		win.getLoadMask(LOAD_WAIT).show();

		this.restore();
		this.center();
		this.maximize();

		win.blockSaveDopDispInfoConsent = false;
		win.saveDopDispInfoConsentAfterLoad = false;
		win.ignoreEmptyFields = false;

		var form = this.EvnPLDispMigrantFormPanel;
		form.getForm().reset();

		win.findById('EPLDMEF_EvnPLDispMigrant_consDate').setRawValue('');
		
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
		
		this.PersonInfoPanel.ButtonPanel.items.items[0].hide();
		this.PersonInfoPanel.ButtonPanel.items.items[2].hide();
		this.PersonInfoPanel.ButtonPanel.items.items[3].hide();
		this.PersonInfoPanel.ButtonPanel.items.items[4].hide();
		
		//загружаем файлы
		this.FileUploadPanel.reset();
		if (arguments[0].EvnPLDispMigrant_id) {
			this.FileUploadPanel.listParams = {
				Evn_id: arguments[0].EvnPLDispMigrant_id
			};
			this.FileUploadPanel.loadData({
				Evn_id: arguments[0].EvnPLDispMigrant_id
			});
		}
		
		//Проверяем возможность редактирования документа
		if (this.action === 'edit' && arguments[0].EvnPLDispMigrant_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: arguments[0].EvnPLDispMigrant_id,
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
		var form = this.EvnPLDispMigrantFormPanel;
		var base_form = this.EvnPLDispMigrantFormPanel.getForm();
		var EvnPLDispMigrant_id = base_form.findField('EvnPLDispMigrant_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();
		var DispClass_id = base_form.findField('DispClass_id').getValue();

		if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'money');
		}
		
		win.DopDispInfoConsentPanel.setTitle('Информированное добровольное согласие');
		
		if (win.action.inlist(['add', 'edit'])) {
			win.setTitle('Медицинское освидетельствование мигрантов: Редактирование');
		} else {
			win.setTitle('Медицинское освидетельствование мигрантов: Просмотр');
		}
		
		// пока не сохранена карта (сохраняется при информационно добровольном согласии) нельзя редактировать разделы кроме согласия
		this.checkEvnPLDispMigrantIsSaved();
		
		inf_frame_is_loaded = false;

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
				
				if (win.action.inlist(['add', 'edit'])) {
					win.enableEdit(true);
				} else {
					win.enableEdit(false);
				}

				win.setPrintActionsAvailability();

				if (!Ext.isEmpty(EvnPLDispMigrant_id)) {
					win.loadForm(EvnPLDispMigrant_id);
				}
				else {
					// Грузим текущую дату
					setCurrentDateTime({
						callback: function(date) {
							win.findById('EPLDMEF_EvnPLDispMigrant_consDate').fireEvent('change', win.findById('EPLDMEF_EvnPLDispMigrant_consDate'), date);
						},
						dateField: win.findById('EPLDMEF_EvnPLDispMigrant_consDate'),
						loadMask: true,
						setDate: true,
						setDateMaxValue: true,
						windowId: win.id
					});
				}
				
				win.buttons[0].focus();
			} 
		});
		
		form.getForm().clearInvalid();
		this.doLayout();
	},
	
	loadForm: function(EvnPLDispMigrant_id) {
	
		var win = this;
		var base_form = this.EvnPLDispMigrantFormPanel.getForm();
		win.getLoadMask(LOAD_WAIT).show();

		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				swEvnPLDispMigrantEditWindow.hide();
			},
			params: {
				EvnPLDispMigrant_id: EvnPLDispMigrant_id,
				archiveRecord: win.archiveRecord
			},
			success: function() {
				win.getLoadMask().hide();
				
				if ( base_form.findField('accessType').getValue() == 'view' ) {
					win.action = 'view';
					win.enableEdit(false);
				}
				
				// грузим грид услуг
				win.evnUslugaDispDopGrid.loadData({
					params: { EvnPLDispMigrant_id: EvnPLDispMigrant_id, object: 'EvnPLDispMigrant' }, globalFilters: { EvnPLDispMigrant_id: EvnPLDispMigrant_id }, noFocusOnLoad: true
				});
				
				// грузим грид контактных лиц
				win.MigrantContactGrid.loadData({
					params: { EvnPLDispMigrant_id: EvnPLDispMigrant_id, object: 'EvnPLDispMigrant' }, globalFilters: { EvnPLDispMigrant_id: EvnPLDispMigrant_id }, noFocusOnLoad: true
				});

				if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
					base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'money');
				}
				
				win.findById('EPLDMEF_EvnPLDispMigrant_consDate').setValue(base_form.findField('EvnPLDispMigrant_consDate').getValue());
				win.findById('EPLDMEF_EvnPLDispMigrant_consDate').fireEvent('change', win.findById('EPLDMEF_EvnPLDispMigrant_consDate'), win.findById('EPLDMEF_EvnPLDispMigrant_consDate').getValue());
			},
			url: '/?c=EvnPLDispMigrant&m=loadEvnPLDispMigrantEditForm'
		});
		
	},
	title: '',
	width: 800
}
);
