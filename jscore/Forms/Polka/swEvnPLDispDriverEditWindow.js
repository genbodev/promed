/**
* swEvnPLDispDriverEditWindow - окно редактирования/добавления талона по освидетельствованию водителей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright		Copyright (c) 2016 Swan Ltd.
* @comment		Префикс для id компонентов EPLDDEF (EvnPLDispDriverEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispDriverEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispDriverEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispDriverEditWindow.js',
	draggable: true,
	getDataForCallBack: function()
	{
		var win = this;
		var base_form = win.EvnPLDispDriverFormPanel.getForm();
		var personinfo = win.PersonInfoPanel;
		
		var response = new Object();
		
		response.EvnPLDispDriver_id = base_form.findField('EvnPLDispDriver_id').getValue();
		response.Person_id = base_form.findField('Person_id').getValue();
		response.Server_id = base_form.findField('Server_id').getValue();
		response.Person_Surname = personinfo.getFieldValue('Person_Surname');
		response.Person_Firname = personinfo.getFieldValue('Person_Firname');
		response.Person_Secname = personinfo.getFieldValue('Person_Secname');
		response.Person_Birthday = personinfo.getFieldValue('Person_Birthday');
		response.EvnPLDispDriver_disDate = typeof base_form.findField('EvnPLDispDriver_disDate').getValue() == 'object' ? base_form.findField('EvnPLDispDriver_disDate').getValue() : Date.parseDate(base_form.findField('EvnPLDispDriver_disDate').getValue(), 'd.m.Y');
		response.EvnPLDispDriver_IsFinish = (base_form.findField('EvnPLDispDriver_IsFinish').getValue() == 2) ? 'Да':'Нет';
		//response.UslugaComplex_Name = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Name');

		return response;
	},
	loadUslugaComplex: function() {
		var win = this;
		var base_form = win.EvnPLDispDriverFormPanel.getForm();

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
		var win = this;
		var base_form = win.EvnPLDispDriverFormPanel.getForm();
		var isDisabled = base_form.findField('EvnPLDispDriver_IsFinish').getValue() != 2;
		
		base_form.findField('ResultDispDriver_id').setDisabled(isDisabled);
		base_form.findField('EvnPLDispDriver_MedSer').setDisabled(isDisabled);
		base_form.findField('EvnPLDispDriver_MedNum').setDisabled(isDisabled);
		base_form.findField('EvnPLDispDriver_MedDate').setDisabled(isDisabled);
		
		base_form.findField('ResultDispDriver_id').setAllowBlank(isDisabled);
		base_form.findField('EvnPLDispDriver_MedSer').setAllowBlank(isDisabled);
		base_form.findField('EvnPLDispDriver_MedNum').setAllowBlank(isDisabled);
		base_form.findField('EvnPLDispDriver_MedDate').setAllowBlank(isDisabled);
		
		base_form.findField('ResultDispDriver_id').validate();
		base_form.findField('EvnPLDispDriver_MedSer').validate();
		base_form.findField('EvnPLDispDriver_MedNum').validate();
		base_form.findField('EvnPLDispDriver_MedDate').validate();
		
		if (win.DriverCategoryCB) win.DriverCategoryCB.setDisabled(isDisabled);
		if (win.DriverMedicalCloseCB) win.DriverMedicalCloseCB.setDisabled(isDisabled);
		if (win.DriverMedicalIndicationCB) win.DriverMedicalIndicationCB.setDisabled(isDisabled);
		
		if ( isDisabled ) {
			base_form.findField('ResultDispDriver_id').setValue(null);
			base_form.findField('EvnPLDispDriver_MedSer').setValue(null);
			base_form.findField('EvnPLDispDriver_MedNum').setValue(null);
			base_form.findField('EvnPLDispDriver_MedDate').setValue(null);
		}
	},
	checkEditPermission: function() {
		var win = this;
        var base_form = this.EvnPLDispDriverFormPanel.getForm();
		if ( win.action == 'view' || Ext.isEmpty(base_form.findField('EvnPLDispDriver_id').getValue()) ) {
			return false;
		}
		
	},
    printEvnPLDispTeenInspProf: function() {
		var win = this;
        var base_form = this.EvnPLDispDriverFormPanel.getForm();

		if ( win.action != 'view' ) {
			win.doSave({
				callback: function() {
					var paramEvnPLTeen = base_form.findField('EvnPLDispDriver_id').getValue();
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
			var paramEvnPLTeen = base_form.findField('EvnPLDispDriver_id').getValue();
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
		var EvnPLDispDriver_form = win.EvnPLDispDriverFormPanel;

		var base_form = win.EvnPLDispDriverFormPanel.getForm();

		if ( !EvnPLDispDriver_form.getForm().isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					EvnPLDispDriver_form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( Ext.isEmpty(win.findById('EPLDDEF_EvnPLDispDriver_consDate').getValue()) ) {
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDDEF_EvnPLDispDriver_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		win.verfGroup();
		
		base_form.findField('EvnPLDispDriver_consDate').setValue(typeof win.findById('EPLDDEF_EvnPLDispDriver_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDDEF_EvnPLDispDriver_consDate').getValue(), 'd.m.Y') : win.findById('EPLDDEF_EvnPLDispDriver_consDate').getValue());

		var params = new Object();
		params.DriverCategory = this.DriverCategoryCB.getValue();
		params.DriverMedicalClose = this.DriverMedicalCloseCB.getValue();
		params.DriverMedicalIndication = this.DriverMedicalIndicationCB.getValue();

		if ( base_form.findField('EvnPLDispDriver_IsFinish').getValue() == 2 && Ext.isEmpty(params.DriverCategory) ) {
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: 'Поле Категории ТС обязательно для заполнения',
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}
		
		win.getLoadMask("Подождите, идет сохранение...").show();

		EvnPLDispDriver_form.getForm().submit({
			failure: function(result_form, action) {
				win.getLoadMask().hide()
			},
			params: params,
			success: function(result_form, action) {
				win.getLoadMask().hide()
				
				if (action.result){
					win.FileUploadPanel.listParams = {Evn_id: action.result.EvnPLDispDriver_id};
					win.FileUploadPanel.saveChanges();
					win.callback({EvnPLDispDriverData: win.getDataForCallBack()});

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
	id: 'EvnPLDispDriverEditWindow',
	showEvnUslugaDispDopEditWindow: function(action) {
		var base_form = this.EvnPLDispDriverFormPanel.getForm();
		var grid = this.evnUslugaDispDopGrid.getGrid();
		var win = this;
		
		var record = grid.getSelectionModel().getSelected();
		
		if ( !record || !record.get('DopDispInfoConsent_id') ) {
			return false;
		}
		
		if (action == 'edit' && getRegionNick() == 'perm') {
			switch(true) {
				case isUserGroup('DrivingCommissionOphth') && record.get('SurveyType_Code') == 155:
				case isUserGroup('DrivingCommissionPsych') && record.get('SurveyType_Code') == 156:
				case isUserGroup('DrivingCommissionPsychNark') && record.get('SurveyType_Code') == 157:
				case isUserGroup('DrivingCommissionTherap'): // терапевт может всё
					action = 'edit';
					break;
				default:
					action = 'view';
					break;
			}
		}
		
		if (!win.action.inlist(['add','edit'])) {
			action = 'view';
		}
		
		if (action == 'view' && !record.get('EvnUslugaDispDop_id')) {
			return false;
		}
		
		var personinfo = win.PersonInfoPanel;
		
		getWnd('swEvnUslugaDispDop13EditWindow').show({
			archiveRecord: this.archiveRecord,
			action: action,
			object: 'EvnPLDispDriver',
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
				EvnVizitDispDop_pid: base_form.findField('EvnPLDispDriver_id').getValue(),
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue(),
				EvnUslugaDispDop_id: record.get('EvnUslugaDispDop_id')
			},
			DopDispInfoConsent_id: record.get('DopDispInfoConsent_id'),
			SurveyTypeLink_id: record.get('SurveyTypeLink_id'),
			SurveyType_Code: record.get('SurveyType_Code'),
			SurveyType_IsVizit: record.get('SurveyType_IsVizit'),
			OrpDispSpec_Code: record.get('OrpDispSpec_Code'),
			SurveyType_Name: record.get('SurveyType_Name'),
			type: 'DispMigrant',
			UslugaComplex_Date: win.findById('EPLDDEF_EvnPLDispDriver_consDate').getValue(),
			onHide: Ext.emptyFn,
			callback: function(data) {
				// обновить грид!
				grid.getStore().reload();
			}
		});	
	},
	initComponent: function() {
		var win = this;
				
		this.FileUploadPanel = new sw.Promed.FileUploadPanel({
			id: 'EPLDDEF_FileUploadPanel',
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
			id: 'EPLDDEF_FileTab',
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
			id: 'EPLDDEF_dopDispInfoConsentGrid',
			dataUrl: '/?c=EvnPLDispDriver&m=loadDopDispInfoConsent',
			region: 'center',
			height: 150,
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
				{ name: 'DopDispInfoConsent_IsEarlier', sortable: false, type: 'checkcolumnedit', isparams: true, header: 'Пройдено ранее', width: 120, hidden: getRegionNick() != 'perm' },
				{ name: 'DopDispInfoConsent_IsAgree', sortable: false, type: 'checkcolumnedit', isparams: true, header: 'Согласие на проведение', width: 180 }
			],
			onBeforeLoadData: function() {
				if ((isUserGroup('DrivingCommissionReg') || getRegionNick() != 'perm') && win.action != 'view') {
					win.findById('EPLDDEF_DopDispInfoConsentSaveBtn').enable();
					win.findById('EPLDDEF_DopDispInfoConsent_CheckAll').enable();
					win.findById('EPLDDEF_EvnPLDispDriver_consDate').enable();
					win.findById('EPLDDEF_EvnPLDispDriver_PayType').enable();
					this.setReadOnly(false);
				} else {
					win.findById('EPLDDEF_DopDispInfoConsentSaveBtn').disable();
					win.findById('EPLDDEF_DopDispInfoConsent_CheckAll').disable();
					win.findById('EPLDDEF_EvnPLDispDriver_consDate').disable();
					win.findById('EPLDDEF_EvnPLDispDriver_PayType').disable();
					this.setReadOnly(true);
				}
			},
			onLoadData: function() {
				this.getGrid().getStore().each(function(rec) {
					if (getRegionNick() == 'perm' && rec.get('SurveyType_Code').inlist([158])) {
						rec.set('DopDispInfoConsent_IsEarlier', 'hidden'); // у терапевта нет "пройдено ранее"
						rec.commit();
					}
				});
				this.doLayout();
				this.checkEnabled();
			},
			onAfterEdit: function(o) {
				if (o && o.field) {
					if (o.record.get('SurveyTypeLink_IsDel') == 2) {
						o.record.set('DopDispInfoConsent_IsAgree', false);
						o.record.set('DopDispInfoConsent_IsEarlier', false);
						o.value = false;
					}
				}
				if (getRegionNick() == 'perm') {
					if (o.field == 'DopDispInfoConsent_IsEarlier' && o.value == true && o.record.get('DopDispInfoConsent_IsAgree') != 'hidden') {
						o.record.set('DopDispInfoConsent_IsAgree', false);
					}					
					if (o.field == 'DopDispInfoConsent_IsAgree' && o.value == true && o.record.get('DopDispInfoConsent_IsEarlier') != 'hidden') {
						o.record.set('DopDispInfoConsent_IsEarlier', false);
					}
				}
				o.record.commit();
				this.checkEnabled();
			},
			checkEnabled: function() {
				var allEnabled = true;
				this.getGrid().getStore().each(function(rec) {
					if (rec.get('DopDispInfoConsent_IsAgree') == false) {
						allEnabled = false;
					}
				});
				this.manualCheck = true;
				win.findById('EPLDDEF_DopDispInfoConsent_CheckAll').setValue(allEnabled);
				this.manualCheck = false;
			},
			checkAll: function(enable) {
				if (this.manualCheck) {
					this.manualCheck = false;
					return false;
				}
				this.getGrid().getStore().each(function(rec) {
					if (rec.get('DopDispInfoConsent_IsEarlier') != 'hidden' && enable) {
						rec.set('DopDispInfoConsent_IsEarlier', false);
					}
					rec.set('DopDispInfoConsent_IsAgree', enable);
					rec.commit();
				});
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
			id: 'EPLDDEF_evnUslugaDispDopGrid',
			dataUrl: '/?c=EvnPLDispDriver&m=loadEvnUslugaDispDopGrid',
			region: 'center',
			height: 150,
			title: '',
			toolbar: true,
			stringfields: [
				{ name: 'DopDispInfoConsent_id', type: 'int', header: 'ID', key: true },
				{ name: 'SurveyTypeLink_id', type: 'int', hidden: true },
				{ name: 'SurveyType_Code', type: 'int', hidden: true },
				{ name: 'SurveyType_IsVizit', type: 'int', hidden: true },
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
				var base_form = win.EvnPLDispDriverFormPanel.getForm();
				
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
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						border: false,
						columnWidth: 1,
						items: [{
							typeCode: 'int',
							useCommonFilter: true,
							width: 300,
							xtype: 'swpaytypecombo',
							id: 'EPLDDEF_EvnPLDispDriver_PayType'
						}]
					}, {
						layout: 'form',
						border: false,
						width: 40,
						items: [{
							html: '',
							listeners: {
								'render': function() {
									win.swEMDPanel = Ext6.create('sw.frames.EMD.swEMDPanel', {
										renderTo: this.getEl(),
										width: 40,
										height: 30
									});

									win.swEMDPanel.setReadOnly(true);
								}
							},
							xtype: 'label'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						columnWidth: 0.9,
						items: [{
							allowBlank: false,
							fieldLabel: 'Дата подписания согласия/отказа',
							format: 'd.m.Y',
							id: 'EPLDDEF_EvnPLDispDriver_consDate',
							listeners: {
								'change': function(field, newValue, oldValue) {
									var base_form = win.EvnPLDispDriverFormPanel.getForm();

									if (getRegionNick().inlist([ 'perm', 'ufa' ]) && !Ext.isEmpty(oldValue) && !Ext.isEmpty(newValue) && win.checkEvnPLDispDriverIsSaved() && newValue.format('Y') != oldValue.format('Y')) {
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
															EvnPLDispDriver_id: base_form.findField('EvnPLDispDriver_id').getValue()
															,
															EvnPLDispDriver_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
														},
														globalFilters: {
															Person_id: base_form.findField('Person_id').getValue()
															,
															DispClass_id: base_form.findField('DispClass_id').getValue()
															,
															EvnPLDispDriver_id: base_form.findField('EvnPLDispDriver_id').getValue()
															,
															EvnPLDispDriver_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
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
													win.findById('EPLDDEF_EvnPLDispDriver_consDate').setValue(oldValue);
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
											,EvnPLDispDriver_id: base_form.findField('EvnPLDispDriver_id').getValue()
											,EvnPLDispDriver_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
										},
										globalFilters: {
											 Person_id: base_form.findField('Person_id').getValue()
											,DispClass_id: base_form.findField('DispClass_id').getValue()
											,EvnPLDispDriver_id: base_form.findField('EvnPLDispDriver_id').getValue()
											,EvnPLDispDriver_consDate: (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue)
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
						}]
					}, {
						border: false,
						layout: 'form',
						columnWidth: 0.1,
						items: [{
							boxLabel: 'Выбрать все',
							hideLabel: true,
							xtype: 'checkbox',
							anchor: '100%',
							id: 'EPLDDEF_DopDispInfoConsent_CheckAll',
							listeners: {
								check: function(checkbox, checked){
									win.dopDispInfoConsentGrid.checkAll(checked);
								}
							}
                        }]
					}]
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
							id: 'EPLDDEF_DopDispInfoConsentSaveBtn',
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
							hidden: getRegionNick() != 'perm',
							menu: [
								new Ext.Action({
									id: 'EPLDDEF_AllDataPrintButton',
									text: 'Все документы',
									hidden: getRegionNick() != 'perm',
									handler: function() {
										var base_form = win.EvnPLDispDriverFormPanel.getForm();
										var EvnPLDispDriver_id = base_form.findField('EvnPLDispDriver_id').getValue();
										if (getPrintOptions().is_driving_commission_twosidedprint) {
											printBirt({
												'Report_FileName': 'EvnPLDispDriver_forms_dbl_pnt.rptdesign',
												'Report_Params': '&paramEvnPLDispDriver_id=' + EvnPLDispDriver_id,
												'Report_Format': 'pdf'
											});
										} else {
											printBirt({
												'Report_FileName': 'EvnPLDispDriver_forms.rptdesign',
												'Report_Params': '&paramEvnPLDispDriver_id=' + EvnPLDispDriver_id,
												'Report_Format': 'pdf'
											});
										}
									}
								}),
								new Ext.Action({
									id: 'EPLDDEF_AcceptancePersDataPrintButton',
									text: 'Согласие на обработку перс. данных',
									hidden: getRegionNick() != 'perm',
									handler: function() {
										var base_form = win.EvnPLDispDriverFormPanel.getForm();
										var EvnPLDispDriver_id = base_form.findField('EvnPLDispDriver_id').getValue();
										printBirt({
											'Report_FileName': 'EvnPLDispDriver_ConsentPersData.rptdesign',
											'Report_Params': '&paramEvnPLDispDriver_id=' + EvnPLDispDriver_id,
											'Report_Format': 'pdf'
										});
									}
								}),
								new Ext.Action({
									id: 'EPLDDEF_AcceptanceDriverPrintButton',
									text: 'Информированное добровольное согласие ',
									hidden: getRegionNick() != 'perm',
									handler: function() {
										var base_form = win.EvnPLDispDriverFormPanel.getForm();
										var EvnPLDispDriver_id = base_form.findField('EvnPLDispDriver_id').getValue();
										printBirt({
											'Report_FileName': 'EvnPLDispDriver_ConsentMedExam.rptdesign',
											'Report_Params': '&paramEvnPLDispDriver_id=' + EvnPLDispDriver_id,
											'Report_Format': 'pdf'
										});
									}
								}),
								new Ext.Action({
									id: 'EPLDDEF_ContractForPaidMedServicePrintButton',
									text: 'Договор на оказание платных мед. услуг',
									hidden: getRegionNick() != 'perm',
									handler: function() {
										var base_form = win.EvnPLDispDriverFormPanel.getForm();
										var Person_id = base_form.findField('Person_id').getValue();
										printBirt({
											'Report_FileName': 'ContractForPaidMedService.rptdesign',
											'Report_Params': '&paramPerson_id=' + Person_id + '&paramLpu_id=' + getGlobalOptions().lpu_id,
											'Report_Format': 'pdf'
										});
									}
								}),
								new Ext.Action({
									id: 'EPLDDEF_EvnPLDispDriver_f025uPrintButton',
									text: 'Амбулаторная карта',
									hidden: getRegionNick() != 'perm',
									handler: function() {
										var base_form = win.EvnPLDispDriverFormPanel.getForm();
										var Person_id = base_form.findField('Person_id').getValue();
										printBirt({
											'Report_FileName': 'EvnPLDispDriver_f025u.rptdesign',
											'Report_Params': '&paramPerson=' + Person_id + '&paramLpu=' + getGlobalOptions().lpu_id,
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
			}],
			layout: 'form',
			border: false,
			autoHeight: true,
			collapsible: true,
			title: 'Информированное добровольное согласие'
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
		
		this.EvnPLDispDriverMainResultsPanel = new sw.Promed.Panel({
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
			labelWidth: 250,
			items: [{
					name: 'EvnPLDispDriver_id',
					value: null,
					xtype: 'hidden'
				}, {
					name:'EvnPLDispDriver_IsSigned',
					xtype:'hidden'
				}, {
					name:'EvnPLDispDriver_IsPaid',
					xtype:'hidden'
				}, {
					name:'EvnPLDispDriver_IndexRep',
					xtype:'hidden'
				}, {
					name:'EvnPLDispDriver_IndexRepInReg',
					xtype:'hidden'
				}, {
					name:'EvnDirection_id',
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
					value: 26,
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDriver_fid',
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
					name: 'EvnPLDispDriver_setDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDriver_disDate',
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDriver_consDate',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPLDispDriver_Num',
					xtype: 'hidden'
				},
				{
					fieldLabel: 'Медицинское обследование закончено',
					hiddenName: 'EvnPLDispDriver_IsFinish',
					allowBlank: false,
					xtype: 'swyesnocombo',
					listeners:{
						'select':function (combo, record) {
							win.verfGroup();
						}
					}
				},
				{
					autoHeight: true,
					border: true,
					xtype: 'fieldset',
					style: 'margin: 10px 5px 0px 5px; padding: 5px 7px;',
					title: 'Медицинское заключение',
					labelWidth: 70,
					width: 700,
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'Серия',
								name: 'EvnPLDispDriver_MedSer',
								tabIndex: this.tabIndexBase + 30,
								width: 100,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'Номер',
								name: 'EvnPLDispDriver_MedNum',
								tabIndex: this.tabIndexBase + 30,
								width: 150,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							width: 220,
							layout: 'form',
							labelWidth: 100,
							items: [{
								fieldLabel: 'Дата выдачи',
								name: 'EvnPLDispDriver_MedDate',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								tabIndex: this.tabIndexBase + 30,
								width: 100,
								xtype: 'swdatefield'
							}]
						}]
					}, {
						comboSubject: 'ResultDispDriver',
						fieldLabel: 'Результат',
						hiddenName: 'ResultDispDriver_id',
						tabIndex: this.tabIndexBase + 30,
						width: 325,
						listWidth: 400,
						xtype: 'swcommonsprcombo'
					},
					{
						id: 'DriverCategoryFS',
						autoHeight: true,
						border: true,
						xtype: 'fieldset',
						style: 'margin: 10px 5px 0px 5px; padding: 5px 7px;',
						title: 'Категории ТС, на управлении которыми предоставляется право',
						labelWidth: 70,
						width: 670,
						items: []
					},
					{
						id: 'DriverMedicalCloseFS',
						autoHeight: true,
						border: true,
						xtype: 'fieldset',
						style: 'margin: 10px 5px 0px 5px; padding: 5px 7px;',
						title: 'Медицинские ограничения к управлению ТС',
						labelWidth: 70,
						width: 670,
						items: []
					},
					{
						id: 'DriverMedicalIndicationFS',
						autoHeight: true,
						border: true,
						xtype: 'fieldset',
						style: 'margin: 10px 5px 0px 5px; padding: 5px 7px;',
						title: 'Медицинские показания к управлению ТС',
						labelWidth: 70,
						width: 670,
						items: []
					}
					]
				}
			],
			layout: 'form',
			region: 'center'
		});
		
		this.EvnPLDispDriverFormPanel = new Ext.form.FormPanel({
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
				win.EvnPLDispDriverMainResultsPanel
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
				{ name: 'EvnPLDispDriver_id' },
				{ name: 'EvnPLDispDriver_IsSigned' },
				{ name: 'EvnPLDispDriver_IsPaid' },
				{ name: 'EvnPLDispDriver_IndexRep' },
				{ name: 'EvnPLDispDriver_IndexRepInReg' },
				{ name: 'EvnDirection_id' },
				{ name: 'accessType' },
				{ name: 'PersonDispOrp_id' },
				{ name: 'DispClass_id' },
				{ name: 'PayType_id' },
				{ name: 'EvnPLDispDriver_fid' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'EvnPLDispDriver_Num' },
				{ name: 'EvnPLDispDriver_setDate' },
				{ name: 'EvnPLDispDriver_consDate' },
				{ name: 'EvnPLDispDriver_disDate' },
				{ name: 'EvnPLDispDriver_eduDT' },
				{ name: 'Org_id' },
				{ name: 'EvnPLDispDriver_IsFinish' },
				{ name: 'ResultDispDriver_id' },
				{ name: 'EvnPLDispDriver_MedSer' },
				{ name: 'EvnPLDispDriver_MedNum' },
				{ name: 'EvnPLDispDriver_MedDate' }
			]),
			url: '/?c=EvnPLDispDriver&m=saveEvnPLDispDriver'
		});
		
		this.EvnPLDispDriverMainPanel = new Ext.Panel({
			border: false,
			layout: 'form',
			region: 'center',
			autoScroll: true,					
			items: [
				win.EvnPLDispDriverFormPanel,
				// файлы
				win.FilePanel
			]
		});
		
		Ext.apply(this, {
			items: [
				// паспортная часть человека
				win.PersonInfoPanel,
				win.EvnPLDispDriverMainPanel
			],
			buttons: [{
				handler: function() {
					this.doSave(false);
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EPLDDEF_SaveButton',
				onTabAction: function() {
					Ext.getCmp('EPLDDEF_SaveSignButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					var base_form = win.EvnPLDispDriverFormPanel.getForm();
					base_form.findField('EvnPLDispDriver_IsFinish').focus(true, 200);
				},
				tabIndex: 2406,
				text: BTN_FRMSAVE
            }, {
				handler: function() {
					this.doSave({
						callback: function() {
							var base_form = win.EvnPLDispDriverFormPanel.getForm();
							var EvnPLDispDriver_id = base_form.findField('EvnPLDispDriver_id').getValue();
							getWnd('swEMDSignWindow').show({
								EMDRegistry_ObjectName: 'EvnPLDispDriver',
								EMDRegistry_ObjectID: EvnPLDispDriver_id,
								callback: function(data) {
									if (data.success) {
										win.callback({EvnPLDispDriverData: win.getDataForCallBack()});
									}
								}
							});
							win.hide();
						}
					});
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EPLDDEF_SaveSignButton',
				onTabAction: function() {
					Ext.getCmp('EPLDDEF_CancelButton').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EPLDDEF_SaveButton').focus(true, 200);
				},
				tabIndex: 2406,
				text: 'Сохранить и подписать'
            }, '-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EPLDDEF_CancelButton',
				tabIndex: 2409,
				text: BTN_FRMCANCEL
			}]
		});
		sw.Promed.swEvnPLDispDriverEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnPLDispDriverEditWindow');
			var tabbar = win.findById('EPLDDEF_EvnPLTabbar');

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
	checkEvnPLDispDriverIsSaved: function() {
		var base_form = this.EvnPLDispDriverFormPanel.getForm();
		if (Ext.isEmpty(base_form.findField('EvnPLDispDriver_id').getValue())) {
			// дисаблим все разделы кроме информированного добровольного согласия, а также основную кнопки сохранить и печать
			this.EvnUslugaDispDopPanel.collapse();
			this.EvnUslugaDispDopPanel.disable();
			this.EvnPLDispDriverMainResultsPanel.collapse();
			this.EvnPLDispDriverMainResultsPanel.disable();
			this.FilePanel.collapse();
			this.FilePanel.disable();
			this.buttons[0].hide();
			this.buttons[1].hide();
			this.buttons[2].hide();
			this.swEMDPanel.hide();
			//this.DopDispInfoConsentPanel.items.items[2].items.items[1].disable(); //Закрываем кнопку "Печать"
			return false;
		} else {
			this.EvnUslugaDispDopPanel.expand();
			this.EvnUslugaDispDopPanel.enable();
			this.EvnPLDispDriverMainResultsPanel.expand();
			this.EvnPLDispDriverMainResultsPanel.enable();
			this.FilePanel.expand();
			this.FilePanel.enable();
		
			if (this.action != 'view') {
				this.buttons[0].show();
			}
			this.buttons[1].show();
			this.buttons[2].show();
			this.swEMDPanel.show();
			this.swEMDPanel.setParams({
				EMDRegistry_ObjectName: 'EvnPLDispDriver',
				EMDRegistry_ObjectID: base_form.findField('EvnPLDispDriver_id').getValue()
			});
			//this.DopDispInfoConsentPanel.items.items[2].items.items[1].enable(); //Открываем кнопку "Печать"
			return true;
		}
	},
	setPrintActionsAvailability: function() {
		var disableButtons = Ext.isEmpty(this.EvnPLDispDriverFormPanel.getForm().findField('EvnPLDispDriver_id').getValue());

		Ext.getCmp('EPLDDEF_AllDataPrintButton').setDisabled(disableButtons);
		Ext.getCmp('EPLDDEF_AcceptancePersDataPrintButton').setDisabled(disableButtons);
		Ext.getCmp('EPLDDEF_AcceptanceDriverPrintButton').setDisabled(disableButtons);
		Ext.getCmp('EPLDDEF_ContractForPaidMedServicePrintButton').setDisabled(disableButtons);
		Ext.getCmp('EPLDDEF_EvnPLDispDriver_f025uPrintButton').setDisabled(disableButtons);
	},
	saveDopDispInfoConsent: function(options) {
		var win = this;
		var btn = win.findById('EPLDDEF_DopDispInfoConsentSaveBtn');
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

		var base_form = win.EvnPLDispDriverFormPanel.getForm();

		win.getLoadMask('Сохранение информированного добровольного согласия').show();
		// берём все записи из грида и посылаем на сервер, разбираем ответ
		// на сервере создать саму карту EvnPLDispDriver, если EvnPLDispDriver_id не задано, сохранить её информ. согласие DopDispInfoConsent, вернуть EvnPLDispDriver_id
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

		if ( Ext.isEmpty(win.findById('EPLDDEF_EvnPLDispDriver_consDate').getValue()) ) {
			btn.enable();
			win.getLoadMask().hide();

			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EPLDDEF_EvnPLDispDriver_consDate').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}
		
		// отказов быть не должно
		var IsOtkaz = false;
		win.dopDispInfoConsentGrid.getGrid().getStore().each(function(rec) {
			if (rec.get('DopDispInfoConsent_IsAgree') != true && rec.get('DopDispInfoConsent_IsEarlier') != true) {
				IsOtkaz = true;
			}
		});
		
		if (IsOtkaz) {
			btn.enable();
			win.getLoadMask().hide();
			if (getRegionNick() == 'perm') {
				sw.swMsg.alert(lang['oshibka'], 'Должно быть заполнено согласие на все обязательные осмотры и исследования не пройденные ранее');
			} else {
				sw.swMsg.alert(lang['oshibka'], 'Должно быть заполнено согласие на все обязательные осмотры и исследования');
			}
			return false;
		}

		params.EvnPLDispDriver_consDate = (typeof win.findById('EPLDDEF_EvnPLDispDriver_consDate').getValue() == 'object' ? Ext.util.Format.date(win.findById('EPLDDEF_EvnPLDispDriver_consDate').getValue(), 'd.m.Y') : win.findById('EPLDDEF_EvnPLDispDriver_consDate').getValue());
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.EvnPLDispDriver_id = base_form.findField('EvnPLDispDriver_id').getValue();
		params.PersonDispOrp_id = base_form.findField('PersonDispOrp_id').getValue();
		params.EvnPLDispDriver_fid = base_form.findField('EvnPLDispDriver_fid').getValue();
		params.DispClass_id = base_form.findField('DispClass_id').getValue();
		params.PayType_id = base_form.findField('PayType_id').getValue();

		params.DopDispInfoConsentData = Ext.util.JSON.encode(getStoreRecords( grid.getStore(), {
			exceptionFields: [
				'SurveyType_Name'
			]
		}));

		Ext.Ajax.request(
		{
			url: '/?c=EvnPLDispDriver&m=saveDopDispInfoConsent',
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
					if (answer.success && answer.EvnPLDispDriver_id > 0)
					{
						base_form.findField('EvnPLDispDriver_id').setValue(answer.EvnPLDispDriver_id);
						win.checkEvnPLDispDriverIsSaved();
						// запускаем callback чтобы обновить грид в родительском окне
						win.callback({EvnPLDispDriverData: win.getDataForCallBack()});
						// обновляем грид
						grid.getStore().load({
							params: {
								EvnPLDispDriver_id: answer.EvnPLDispDriver_id
							}
						});

						win.loadForm(answer.EvnPLDispDriver_id);
						win.setPrintActionsAvailability();
					}
				}
			}
		});
	},
	show: function() {
		sw.Promed.swEvnPLDispDriverEditWindow.superclass.show.apply(this, arguments);
		
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

		var form = this.EvnPLDispDriverFormPanel;
		form.getForm().reset();

		win.findById('EPLDDEF_EvnPLDispDriver_consDate').setRawValue('');
		
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
		if (arguments[0].EvnPLDispDriver_id) {
			this.FileUploadPanel.listParams = {
				Evn_id: arguments[0].EvnPLDispDriver_id
			};
			this.FileUploadPanel.loadData({
				Evn_id: arguments[0].EvnPLDispDriver_id
			});
		}		
		
		//Проверяем возможность редактирования документа
		if (this.action === 'edit' && arguments[0].EvnPLDispDriver_id) {
			Ext.Ajax.request({
				failure: function (response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
						this.hide();
					}.createDelegate(this));
				},
				params: {
					Evn_id: arguments[0].EvnPLDispDriver_id,
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
		var form = this.EvnPLDispDriverFormPanel;
		var base_form = this.EvnPLDispDriverFormPanel.getForm();
		var EvnPLDispDriver_id = base_form.findField('EvnPLDispDriver_id').getValue();
		var person_id = base_form.findField('Person_id').getValue();
		var server_id = base_form.findField('Server_id').getValue();
		var DispClass_id = base_form.findField('DispClass_id').getValue();

		if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'money');
		}
		
		if (win.action.inlist(['add', 'edit'])) {
			win.setTitle('Медицинское освидетельствование водителей: Редактирование');
		} else {
			win.setTitle('Медицинское освидетельствование водителей: Просмотр');
		}
		
		// пока не сохранена карта (сохраняется при информационно добровольном согласии) нельзя редактировать разделы кроме согласия
		this.checkEvnPLDispDriverIsSaved();
		
		this.checkRegistry();
		
		inf_frame_is_loaded = false;

		this.swEMDPanel.setParams({
			EMDRegistry_ObjectName: 'EvnPLDispDriver',
			EMDRegistry_ObjectID: null
		});
		this.swEMDPanel.setIsSigned(null);

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

				// проверка на возраст
				if (age < 15) {
					sw.swMsg.alert(lang['oshibka'], 'Возраст пациента меньше 15 лет. Создание случая медицинского освидетельствования водителя невозможно');
					win.hide();
				}
				
				if (win.action.inlist(['add', 'edit'])) {
					win.enableEdit(true);
					win.verfGroup();
				} else {
					win.enableEdit(false);
				}

				win.setPrintActionsAvailability();

				if (!Ext.isEmpty(EvnPLDispDriver_id)) {
					win.loadForm(EvnPLDispDriver_id);
				}
				else {
					// Грузим текущую дату
					setCurrentDateTime({
						callback: function(date) {
							win.findById('EPLDDEF_EvnPLDispDriver_consDate').fireEvent('change', win.findById('EPLDDEF_EvnPLDispDriver_consDate'), date);
						},
						dateField: win.findById('EPLDDEF_EvnPLDispDriver_consDate'),
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
	onEnableEdit: function(enable) {
		this.swEMDPanel.setReadOnly(!enable);
	},
	loadForm: function(EvnPLDispDriver_id) {
	
		var win = this;
		var base_form = this.EvnPLDispDriverFormPanel.getForm();
		win.getLoadMask(LOAD_WAIT).show();

		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				swEvnPLDispDriverEditWindow.hide();
			},
			params: {
				EvnPLDispDriver_id: EvnPLDispDriver_id,
				archiveRecord: win.archiveRecord
			},
			success: function(form, act) {
				win.getLoadMask().hide();
				
				var response_obj = Ext.util.JSON.decode(act.response.responseText);
				
				if ( base_form.findField('accessType').getValue() == 'view' ) {
					win.action = 'view';
					win.enableEdit(false);
				}

				win.swEMDPanel.setParams({
					EMDRegistry_ObjectName: 'EvnPLDispDriver',
					EMDRegistry_ObjectID: base_form.findField('EvnPLDispDriver_id').getValue()
				});
				win.swEMDPanel.setIsSigned(base_form.findField('EvnPLDispDriver_IsSigned').getValue());
				
				// грузим грид услуг
				win.evnUslugaDispDopGrid.loadData({
					params: { EvnPLDispDriver_id: EvnPLDispDriver_id, object: 'EvnPLDispDriver' }, globalFilters: { EvnPLDispDriver_id: EvnPLDispDriver_id }, noFocusOnLoad: true
				});
				
				// грузим чекбоксы
				win.loadCbGroups(response_obj);

				if (Ext.isEmpty(base_form.findField('PayType_id').getValue())) {
					base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'money');
				}
				
				win.findById('EPLDDEF_EvnPLDispDriver_consDate').setValue(base_form.findField('EvnPLDispDriver_consDate').getValue());
				win.findById('EPLDDEF_EvnPLDispDriver_consDate').fireEvent('change', win.findById('EPLDDEF_EvnPLDispDriver_consDate'), win.findById('EPLDDEF_EvnPLDispDriver_consDate').getValue());
			},
			url: '/?c=EvnPLDispDriver&m=loadEvnPLDispDriverEditForm'
		});
		
	},
	loadCbGroups: function(response_obj) {
		var win = this;
		
		var DriverCategoryFS = win.findById('DriverCategoryFS');
		var DriverMedicalCloseFS = win.findById('DriverMedicalCloseFS');
		var DriverMedicalIndicationFS = win.findById('DriverMedicalIndicationFS');
		
		DriverCategoryFS.removeAll();
		DriverMedicalCloseFS.removeAll();
		DriverMedicalIndicationFS.removeAll();
				
		if(response_obj[0].DriverCategory && response_obj[0].DriverCategory.length > 0) {
			win.DriverCategoryCB = new Ext.form.CheckboxGroup({
				xtype: 'checkboxgroup',
				hidden: false,
				hideLabel: true,
				vertical: true,
				allowBlank:true,
				columns: 4,
				width: 400,	
				items: response_obj[0].DriverCategory,
				listeners:{
					'change':function(a,b,c){
						//log(this,a,b,c)
					},
					'enable':function(a,b,c) {
						this.items.each(function(item){
							if (item.value.inlist([3,4,5,6,7,8,9,13,14,15,16])) {
								item.disable();
							}
						});
					}
				},
				getValue: function() {
					var out = [];
					this.items.each(function(item){
						if(item.checked){
							out.push(item.value);
						}
					});
					return out.join(',');
				}
			});
			DriverCategoryFS.add(win.DriverCategoryCB);
			DriverCategoryFS.doLayout();
			DriverCategoryFS.show();
		} else {
			DriverCategoryFS.hide();
		}
				
		if(response_obj[0].DriverMedicalClose && response_obj[0].DriverMedicalClose.length > 0) {
			win.DriverMedicalCloseCB = new Ext.form.CheckboxGroup({
				xtype: 'checkboxgroup',
				hidden: false,
				hideLabel: true,
				vertical: true,
				allowBlank:true,
				columns: 1,
				items: response_obj[0].DriverMedicalClose,
				listeners:{
					'change':function(a,b,c){
						//log(this,a,b,c)
					}
				},
				getValue: function() {
					var out = [];
					this.items.each(function(item){
						if(item.checked){
							out.push(item.value);
						}
					});
					return out.join(',');
				}
			});
			DriverMedicalCloseFS.add(win.DriverMedicalCloseCB);
			DriverMedicalCloseFS.doLayout();
			DriverMedicalCloseFS.show();
		} else {
			DriverMedicalCloseFS.hide();
		}
				
		if(response_obj[0].DriverMedicalIndication && response_obj[0].DriverMedicalIndication.length > 0) {
			win.DriverMedicalIndicationCB = new Ext.form.CheckboxGroup({
				xtype: 'checkboxgroup',
				hidden: false,
				hideLabel: true,
				vertical: true,
				allowBlank:true,
				columns: 1,
				items: response_obj[0].DriverMedicalIndication,
				listeners:{
					'change':function(a,b,c){
						//log(this,a,b,c)
					}
				},
				getValue: function() {
					var out = [];
					this.items.each(function(item){
						if(item.checked){
							out.push(item.value);
						}
					});
					return out.join(',');
				}
			});
			DriverMedicalIndicationFS.add(win.DriverMedicalIndicationCB);
			DriverMedicalIndicationFS.doLayout();
			DriverMedicalIndicationFS.show();
		} else {
			DriverMedicalIndicationFS.hide();
		}
		
		win.verfGroup();
	},
	checkRegistry: function() {
		var win = this;
		var base_form = win.EvnPLDispDriverFormPanel.getForm();
		var person_id = base_form.findField('Person_id').getValue();
		
		Ext.Ajax.request({
			failure: function(response, options) {
				win.loadMask.hide();
				showSysMsg('При загрузке информации возникли ошибки');
			},
			params: {
				Person_id: person_id
			},
			success: function(response, options) {
				win.loadMask.hide();
				if ( response.responseText ) {
					var result  = Ext.util.JSON.decode(response.responseText);
					if ( result && result.message ) {
						showSysMsg(result.message, 'Внимание', 'warning', {closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px; background:transparent; color: #900;'});
					}
				}
			},
			url: '/?c=EvnPLDispDriver&m=getRegistryInfo'
		});
	},
	title: '',
	width: 800
}
);
