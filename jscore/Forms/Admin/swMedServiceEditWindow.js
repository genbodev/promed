/**
* swMedServiceEditWindow - окно просмотра, добавления и редактирования служб
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @comment      tabIndex: TABINDEX_MS + (0-15)
*/

/*NO PARSE JSON*/
sw.Promed.swMedServiceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedServiceEditWindow',
	objectSrc: '/jscore/Forms/Admin/swMedServiceEditWindow.js',

	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: '',
	draggable: true,
	id: 'swMedServiceEditWindow',
	width: 600,
	autoHeight: true,
	modal: true,
	plain: true,
	resizable: false,
	textGenerateCode: lang['[ne_ispolzuetsya]'],

	doReset: function() {
		var form = this.formPanel.getForm();
		form.reset();
	},
	getMedServiceCode: function() {
		var win = this, form = this.formPanel.getForm();
		var code = form.findField('MedService_Code');

		if ( this.action == 'view' || code.disabled || code.getValue()>0) { // если просмотр или компонент задисаблен, или код уже сгенерирован, то ничего не делаем дальше
			return false;
		}

		win.getLoadMask( "Определение порядкового номера лаборатории...").show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj[0]) {
						code.setValue(response_obj[0].MedService_Code);
						code.focus(true);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_poryadkovogo_nomera_laboratorii']);
						//sw.swMsg.alert('Ошибка', response.responseText);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_poryadkovogo_nomera_laboratorii']);
				}
			},
			url: '/?c=MedService&m=getMedServiceCode'
		});
	},
	doSave: function() {
		if(this.action == 'add' && this.formPanel.getForm().findField('LpuSection_id').getValue() > 0) {
            var me = this,
                typ_combo = me.formPanel.getForm().findField('MedServiceType_id'),
                question = lang['skopirovat_spiski_uslug_i_sotrudnikov_iz_spiskov_otdeleniya'],
                index = typ_combo.getStore().findBy(function(rec) {
                    return (rec.get('MedServiceType_id') == typ_combo.getValue());
                }),
                typ_rec = typ_combo.getStore().getAt(index);
            if (typ_rec && 'remoteconsultcenter' == typ_rec.get('MedServiceType_SysNick')) {
                question = lang['skopirovat_spisok_sotrudnikov_iz_spiska_otdeleniya']
            }
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
                        me.copyFromLpuSection = 1;
					} else {
                        me.copyFromLpuSection = 0;
					}
                    me.submit();
				},
				icon: Ext.MessageBox.QUESTION,
				msg: question,
				title: lang['vopros']
			});
		} else {
			this.submit();
		}
	},
	submit: function() {
		var win = this,
			form = this.formPanel.getForm(),
			params = {};

		if ( !form.isValid() ) {
			sw.swMsg.alert(lang['oshibka_zapolneniya_formyi'], lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			return;
		}

		if ( this.action == 'add' ) {
			params.copyFromLpuSection = this.copyFromLpuSection;
		}
		var typ_combo = form.findField('MedServiceType_id');
		if ( typ_combo.disabled ) {
			params.MedServiceType_id = typ_combo.getValue();
		}
		if (form.findField('MedService_Code').getValue() == this.textGenerateCode) {
			params.MedService_Code = null;
		}

		var MedService_WialonURLForm = form.findField('MedService_WialonURL');
		if(
			MedService_WialonURLForm.getValue().substring(0,7) != 'http://' &&
			MedService_WialonURLForm.getValue().substring(0,8) != 'https://'
		)
		{
			params.MedService_WialonURL = 'http://'+MedService_WialonURLForm.getValue();
		}


		//BOB - 25.01.2017
		//СОХРАНЕНИЕ ПРИКРЕПЛЁННЫХ ОТДЕЛЕНИЙ
		if (
			form.findField('MedServiceType_id').getFieldValue('MedServiceType_SysNick') == 'reanimation'
		) {
			var MedServiceSectionGrid = this.MedServiceSectionGrid.getGrid();
			MedServiceSectionGrid.getStore().clearFilter();

			if ( MedServiceSectionGrid.getStore().getCount() > 0 ) {
				var MedServiceSectionData = getStoreRecords(MedServiceSectionGrid.getStore(), {
					exceptionFields: [
						'LpuSection_Name',
						'LpuBuilding_Name'
					]
				});

				params.MedServiceSectionData = Ext.util.JSON.encode(MedServiceSectionData);

				MedServiceSectionGrid.getStore().filterBy(function(rec) {
					return (Number(rec.get('RecordStatus_Code')) != 3);
				});

			}
		}
        //BOB - 25.01.2017


		win.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		form.submit({
			failure: function (form, action) {
				win.getLoadMask().hide();
			},
			params: params,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				var data = {};
				data.MedService_id = action.result.MedService_id;
				if(win.owner && (win.owner.id == 'MedService' || win.owner.id == 'OrgStructureWindowMedServiceGrid'))
				{
					win.callback(win.owner,action.result.MedService_id);
				}
				else
				{
					win.callback(data);
				}
			}
		});
	},
	allowEdit: function(is_allow) {
		var win = this,
			form = this.formPanel.getForm(),
			save_btn = this.buttons[0],
			fields = [
				'MedService_Name'
				,'MedService_Code'
				,'MedService_Nick'
				,'MedServiceType_id'
				,'MedService_begDT'
				,'MedService_endDT'
				,'ApiServiceType_id'
				,'MseOffice_id'
				,'LpuEquipmentPacs_id' //#146135
			];

		for(var i=0;fields.length>i;i++) {
			form.findField(fields[i]).setDisabled(!is_allow);
		}

		if (is_allow)
		{
			form.findField('MedService_Name').focus(true, 250);
			save_btn.show();
		}
		else
		{
			save_btn.hide();
		}
	},
	openAddressEditWindow: function() {
		var base_form = this.formPanel.getForm();
		getWnd('swAddressEditWindow').show({
			fields: {
				Address_ZipEdit: base_form.findField('Address_Zip').getValue(),
				KLCountry_idEdit: base_form.findField('KLCountry_id').getValue(),
				KLRgn_idEdit: base_form.findField('KLRGN_id').getValue(),
				KLSubRGN_idEdit: base_form.findField('KLSubRGN_id').getValue(),
				KLCity_idEdit: base_form.findField('KLCity_id').getValue(),
				KLTown_idEdit: base_form.findField('KLTown_id').getValue(),
				KLStreet_idEdit: base_form.findField('KLStreet_id').getValue(),
				Address_HouseEdit: base_form.findField('Address_House').getValue(),
				Address_CorpusEdit: base_form.findField('Address_Corpus').getValue(),
				Address_FlatEdit: base_form.findField('Address_Flat').getValue(),
				Address_AddressEdit: base_form.findField('Address_Address').getValue()
			},
			callback: function(values) {
				base_form.findField('Address_Zip').setValue(values.Address_ZipEdit);
				base_form.findField('KLCountry_id').setValue(values.KLCountry_idEdit);
				base_form.findField('KLRGN_id').setValue(values.KLRgn_idEdit);
				base_form.findField('KLSubRGN_id').setValue(values.KLSubRGN_idEdit);
				base_form.findField('KLCity_id').setValue(values.KLCity_idEdit);
				base_form.findField('KLTown_id').setValue(values.KLTown_idEdit);
				base_form.findField('KLStreet_id').setValue(values.KLStreet_idEdit);
				base_form.findField('Address_House').setValue(values.Address_HouseEdit);
				base_form.findField('Address_Corpus').setValue(values.Address_CorpusEdit);
				base_form.findField('Address_Flat').setValue(values.Address_FlatEdit);
				base_form.findField('Address_Address').setValue(values.Address_AddressEdit);
				base_form.findField('Address_AddressText').setValue(values.Address_AddressEdit);
				base_form.findField('Address_AddressText').focus(true, 500);
			},
			onClose: function() {
				base_form.findField('Address_AddressText').focus(true, 500);
			}
		});
	},
	allowedMedServiceTypes: [],
	Org_id: false,
	initComponent: function() {
		var win = this;

		//BOB - 25.01.2017
		//СОЗДАНИЕ СВОЙСТВА ФОРМЫ - ОБЪЕКТ СПИСОК ОБСЛУЖИВАЕМЫХ ОТДЕЛЕНИЙ
		this.MedServiceSectionGrid = new sw.Promed.ViewFrame({
			id: win.id + '_MedServiceSectionGrid',
			dataUrl: '/?c=MedService&m=loadMedServiceSectionGrid',
			toolbar: true,
			autoLoadData: false,
            height: 350,
			stringfields:
				[
					{name: 'MedServiceSection_id', type: 'int', header: 'ID', key: true},
					{name: 'MedService_id', type: 'int', hidden: true},
					{name: 'LpuSection_id', type: 'int', hidden: true},
					{name: 'RecordStatus_Code', type: 'int', hidden: true},
					{name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], id: 'autoexpand'},
					{name: 'LpuBuilding_Name', type: 'string', header: lang['podrazdelenie'], width: 280}
				],
			actions:
				[
					{name:'action_add', handler: function(){ win.openMedServiceSectionEditWindow('add'); }},
					{name:'action_edit', hidden: true},
					{name:'action_view', hidden: true},
					{name:'action_delete', handler: function(){ win.deleteMedServiceSection(); }},
					{name:'action_refresh', hidden: true},
					{name:'action_print', hidden: true}
				]
		});
        //BOB - 25.01.2017



		this.checkLocalServerButton = new Ext.Button({
			text: 'Проверить доступ',
			tabIndex: TABINDEX_MS + 19,
			handler: function()
			{
				var form = win.formPanel.getForm();
				var url = form.findField('MedService_LocalCMPPath').getValue();
				// отправляем запрос серверу
				win.getLoadMask('Проверка доступности локального сервера').show();
				Ext.Ajax.request({
					callback: function(options, success, response) {
						win.getLoadMask().hide();
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success) {
								sw.swMsg.alert('Проверка', 'Локальный сервер СМП доступен');
							} else {
								sw.swMsg.alert('Проверка', 'Локальный сервер СМП не доступен. Обратитесь к администратору сервера');
							}
						}
						else {
							sw.swMsg.alert('Проверка', 'Локальный сервер СМП не доступен. Обратитесь к администратору сервера');
						}
					},
					url: url + '?c=Portal&m=checkIsLocalServer'
				});
			},
			style: 'margin-left: 155px'
		});

		this.localServerFields = new Ext.Panel({
			layout: 'form',
			items: [{
				layout : 'form',
				style: 'margin-left: 155px',
				items : [{
					boxLabel: 'Резервировать данные на локальный сервер СМП',
					name: 'MedService_IsLocalCMP',
					listeners: {
						'check': function(checkbox, checked) {
							// check
							win.onChangeIsLocalCMP();
						}
					},
					hideLabel: true,
					style: 'margin-left: 135px',
					tabIndex: TABINDEX_MS + 17,
					xtype: 'checkbox'
				}]
			}, {
				fieldLabel: 'Адрес локального сервера СМП',
				name: 'MedService_LocalCMPPath',
				allowBlank: true,
				anchor: '95%',
				maxLength: 100,
				tabIndex: TABINDEX_MS + 18,
				xtype: 'textfield'
			}, win.checkLocalServerButton ]
		});

		this.formPanel = new Ext.form.FormPanel({
			buttonAlign: 'left',
			frame: true,
			id: 'MedServiceRecordEditForm',
				items: [
					new Ext.TabPanel({
						region: 'south',
						id: 'MedServ-tabs-panel',
						//autoScroll: true,

						border:true,
						activeTab: 0,
						//resizeTabs: true,
						//enableTabScroll: true,
						//autoWidth: true,
						//tabWidth: 'auto',
						layoutOnTabChange: true,
						listeners: {
							'tabchange': function(tab, panel) {
								win.syncShadow();
							}
						},
						items:[
							{
							title: lang['obschie'],
							id: 'tab_Common',
							iconCls: 'info16',
							autoHeight: true,
							border:false,
							items: [
								{
									layout:'form',
									autoHeight: true,
									labelAlign: 'right',
									labelWidth: 200,
									items:[
										{
											anchor: '95%',
											allowBlank: false,
											fieldLabel: lang['naimenovanie'],
											name: 'MedService_Name',
											id: 'MSEW_MedService_Name',
											maxLength: 200,
											tabIndex: TABINDEX_MS,
											xtype: 'textfield'
										}, {
											anchor: '95%',
											allowBlank: false,
											fieldLabel: lang['kratkoe_naimenovanie'],
											name: 'MedService_Nick',
											maxLength: 50,
											tabIndex: TABINDEX_MS + 3,
											triggerClass: 'x-form-equil-trigger',
											onTriggerClick: function() {
												var base_form = win.formPanel.getForm();
												if ( base_form.findField('MedService_Nick').disabled ) {
													return false;
												}
												var fullname = base_form.findField('MedService_Name').getValue();
												base_form.findField('MedService_Nick').setValue(fullname);
											},
											xtype: 'trigger'
										}, {
											anchor: '95%',
											fieldLabel: lang['tip'],
											hiddenName: 'MedServiceType_id',
											comboSubject: 'MedServiceType',
											moreFields: [{ name: 'MedServiceType_IsAdmin', mapping: 'MedServiceType_IsAdmin' }],
											allowSysNick: true,
											allowBlank: false,
											tabIndex: TABINDEX_MS + 4,
											enableKeyEvents: true,
											beforeBlur: function() {
												// медитируем
												return true;
											},
											triggerAction: 'all',
											forceSelection: true,
											autoLoad: false,
											typeCode: 'int',
											onLoadStore: function () {
												// фильтрация по доступным типам мед сервисов.
												this.lastQuery = '';
												this.getStore().clearFilter();

												var MedServiceType_id = this.getValue();
												if (getGlobalOptions().lpu_isLab == 2) {
													var typesInLab = [6, 7, 11, 12, 28, 71];
													/*
													 * Разрешенные типы для лаборатории
													 * 7 - Пункт забора биоматериала
													 * 6 - Регистрационная служба лаборатории
													 * 28 - Лаборатория
													 * 11 - Отдел кадров
													 * 12 -	Медицинская статистика
													 */
													this.getStore().filterBy(function (rec) {
														return rec.get('MedServiceType_id').inlist(typesInLab);
													});
												}
												else {
													if (typeof win.allowedMedServiceTypes == 'object' && win.allowedMedServiceTypes.length > 0) {
														//var uuuu = this.getStore();  //BOB - 25.01.2017
														//console.log('BOB_Object=',uuuu);  //BOB - 25.01.2017
														//// console.log('BOB_Object=',win.allowedMedServiceTypes);  //BOB - 25.01.2017
														this.getStore().filterBy(function (rec) {
															return rec.get('MedServiceType_id').inlist(win.allowedMedServiceTypes);
														});
													}

													if (!Ext.isEmpty(MedServiceType_id)) {
														var index = this.getStore().findBy(function (rec) {
															return (rec.get('MedServiceType_id') == MedServiceType_id);
														});

														if (index >= 0) {
															this.setValue(MedServiceType_id);
														}
														else {
															this.clearValue();
														}
													}
												}
											},
											xtype: 'swcommonsprcombo',
											listeners: {
												'change': function(combo, newValue, oldValue) {
													var field = win.formPanel.getForm().findField('MedService_IsShowDiag');
													var MedService_IsQualityTestApprove = win.formPanel.getForm().findField('MedService_IsQualityTestApprove');
													if (newValue == 6) { // лаборатория
														var flag = ['kareliya', 'ekb'].indexOf(getRegionNick()) == -1;
														if (flag) field.showField();
														MedService_IsQualityTestApprove.showField();
													} else {
														field.hideField();
														field.setValue(0);
														MedService_IsQualityTestApprove.hideField();
														MedService_IsQualityTestApprove.setValue(0);
													}
													win.showServiceFields();
													win.showSendMbuField();
													var index = combo.getStore().findBy(function(rec) {
														return (rec.get(combo.valueField) == newValue);
													});
													combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
												}.createDelegate(this),
												//'change': function(combo, newValue, oldValue) {
												'select': function(combo, record, idx) {
													var base_form = this.formPanel.getForm();

													//this.formPanel.getForm().findField('MedService_Code').setContainerVisible((combo.getFieldValue('MedServiceType_SysNick') == 'lab'));
													var lab = (typeof record == 'object' && record.get('MedServiceType_SysNick') == 'lab');
													if (this.action && this.action!='view') {
														this.formPanel.getForm().findField('MedService_Code').setDisabled(!lab);
													}
													if (!lab) {
														this.formPanel.getForm().findField('MedService_Code').setValue(this.textGenerateCode);
													} else {
														if (this.formPanel.getForm().findField('MedService_Code').getValue() == this.textGenerateCode)
															this.formPanel.getForm().findField('MedService_Code').setValue('');
													}

													//показ-сокрытие поля "файловая интеграция"
													if (getRegionNick() === 'vologda')
														if (typeof record == 'object' && record.get('MedServiceType_Code').inlist([1, 2])) {
															base_form.findField('MedService_IsFileIntegration').showContainer();
														} else {
															base_form.findField('MedService_IsFileIntegration').hideContainer();
														}
													/*
													не срабатывает при выборе по коду
													if(this.action == 'add' && this.formPanel.getForm().findField('LpuSection_id').getValue() > 0) {
														sw.swMsg.show({
															buttons: Ext.Msg.YESNO,
															fn: function(buttonId, text, obj) {
																if ( buttonId == 'yes' ) {
																	this.copyFromLpuSection = 1;
																} else {
																	this.copyFromLpuSection = 0;
																}
															}.createDelegate(this),
															icon: Ext.MessageBox.QUESTION,
															msg: lang['skopirovat_spiski_uslug_i_sotrudnikov_iz_spiskov_otdeleniya'],
															title: lang['vopros']
														});
													}*/
													if ( typeof record == 'object' && record.get('MedServiceType_SysNick').inlist(['smp']) ) {
														var ApiServiceType_id = base_form.findField('ApiServiceType_id').getValue();

														base_form.findField('ApiServiceType_id').getStore().filterBy(function(rec) {
															return (rec.get('MedServiceType_id') == record.get('MedServiceType_id'));
														});

														if ( !Ext.isEmpty(ApiServiceType_id) ) {
															var index = base_form.findField('ApiServiceType_id').getStore().findBy(function(rec) {
																return (rec.get('ApiServiceType_id') == ApiServiceType_id);
															});

															if ( index >= 0 ) {
																base_form.findField('ApiServiceType_id').setValue(ApiServiceType_id);
															}
															else {
																base_form.findField('ApiServiceType_id').clearValue();
															}
														}

														base_form.findField('ApiServiceType_id').fireEvent('change', base_form.findField('ApiServiceType_id'), base_form.findField('ApiServiceType_id').getValue());
													}
													//BOB - 25.01.2017
													var tabPanel = this.findById('MedServ-tabs-panel');
													console.log('BOB_Object=',tabPanel);  //BOB - 25.01.2017
                                                                                                        console.log('BOB_Object=',record);  //BOB - 25.01.2017
													if ( typeof record == 'object' && record.get('MedServiceType_SysNick').inlist(['reanimation']) ) {
														tabPanel.unhideTabStripItem('tab_MedServiceSection');
														win.MedServiceSectionGrid.loadData({
															params: {
																MedService_id: this.formPanel.getForm().findField('MedService_id').getValue()
															},
															globalFilters: {
																MedService_id: this.formPanel.getForm().findField('MedService_id').getValue()
															}
														});
													}
													else {
															tabPanel.hideTabStripItem('tab_MedServiceSection');
													}
													//BOB - 25.01.2017

													base_form.findField('comboPACS').setContainerVisible(typeof record == 'object' &&
													record.get('MedServiceType_SysNick') == 'func' ); // Вывод комбобокса - выбор PACS-сервера, не виден если не диагностика

													base_form.findField('MseOffice_id').setContainerVisible(
														typeof record == 'object' &&
														record.get('MedServiceType_SysNick') == 'mse' &&
														getRegionNick() != 'kz'
													);

												}.createDelegate(this)
											}
										},{
											anchor: '95%',  //#146135
											fieldLabel: 'PACS Сервер',
											valueField: 'LpuEquipmentPacs_id',
											hiddenName: 'LpuEquipmentPacs_id',
											displayField: 'LpuEquipment_Name',
											xtype: 'swcommonsprcombo',
											name: 'LpuEquipmentPacs_id',
											id: 'comboPACS',
											store: new Ext.data.JsonStore({
												autoLoad: false,
												url: '/?c=LpuPassport&m=loadLpuEquipment',
												fields: [
													{ name: 'LpuEquipmentPacs_id', type: 'int' },
													{ name: 'LpuEquipment_Name', type: 'string' }
												]
											}),
											listeners: {
												'enable': function(pacsCombo) {
													if(pacsCombo.store.getCount() == 1) {
														var valuePACS = pacsCombo.store.data.items[0].get('LpuEquipmentPacs_id');
														pacsCombo.setValue(valuePACS);
														pacsCombo.setDisabled(true);
													}
												},
												'change': function(combo, newValue, oldValue) {
													combo.setValue(newValue);
												}.createDelegate(this)
											}
										}, {
											anchor: '95%',
											allowBlank: false,
											fieldLabel: lang['kod'],
											readOnly: true,
											disabled: true,
											name: 'MedService_Code',
											maxLength: 4,
											tabIndex: TABINDEX_MS + 5,
											triggerClass: 'x-form-plus-trigger',
											validateOnBlur: false,
											enableKeyEvents: true,
											onTriggerClick: function() {
												this.getMedServiceCode();
											}.createDelegate(this),
											listeners: {
												'keydown': function(inp, e) {
													switch ( e.getKey() ) {
														case Ext.EventObject.F4:
															e.stopEvent();
															inp.onTriggerClick();
															break;
													}
												}
											},
											xtype: 'trigger'
										},
										{
											xtype: 'swlpusectionagecombo',
											name: 'LpuSectionAge_id',
											allowBlank: true
										},
										{
											anchor: '95%',
											readOnly: true,
											fieldLabel: 'Адрес',
											name: 'Address_AddressText',
											triggerClass: 'x-form-search-trigger',
											onTriggerClick: function() {
												this.openAddressEditWindow();
											}.createDelegate(this),
											xtype: 'trigger'
										}, {
											fieldLabel: langs('Отображение диагноза в заявке'),
											name: 'MedService_IsShowDiag',
											xtype: 'checkbox',
											showField: function() {
												this.enable();
												this.show();
												this.getEl().up('.x-form-item').setDisplayed(true);
											},
											hideField: function() {
												this.disable();
												this.hide();
												this.getEl().up('.x-form-item').setDisplayed(false);
											}
										}, {
											fieldLabel: langs('Признак группового одобрения качественных тестов'),
											name: 'MedService_IsQualityTestApprove',
											xtype: 'checkbox',
											showField: function() {
												this.enable();
												this.show();
												this.getEl().up('.x-form-item').setDisplayed(true);
											},
											hideField: function() {
												this.disable();
												this.hide();
												this.getEl().up('.x-form-item').setDisplayed(false);
											}
										}, {
											anchor: '95%',
											fieldLabel: langs('Код ЕАВИИАС'),
											hiddenName: 'MseOffice_id',
											codeAlthoughNotEditable: true,
											listWidth: 800,
											lastQuery: '',
											store: new Ext.data.JsonStore({
												autoLoad: false,
												fields: [
													{ name: 'MseOffice_id', mapping: 'MseOffice_id' },
													{ name: 'MseOffice_Code', mapping: 'MseOffice_Code' },
													{ name: 'MseOffice_Name', mapping: 'MseOffice_Name' }
												],
												key: 'MseOffice_id',
												sortInfo: { field: 'MseOffice_Name' },
												url:'/?c=MedService&m=loadMseOfficeList'
											}),
											tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<font color="red">{MseOffice_Code}</font>&nbsp;{MseOffice_Name}',
												'</div></tpl>'
											),
											displayField: 'MseOffice_Name',
											codeField: 'MseOffice_Code',
											valueField: 'MseOffice_id',
											xtype: 'swbaselocalcombo'
										}, {
											fieldLabel: langs('Внешняя служба'),
											name: 'MedService_IsExternal',
											listeners: {
												'check': function(checkbox, checked) {
													win.showServiceFields();
													//win.showSendMbuField()
												}
											},
											xtype: 'checkbox'
										}, {
											fieldLabel: langs('Файловая интеграция'),
											name: 'MedService_IsFileIntegration',
											xtype: 'checkbox'
										}, {
											comboSubject: 'ApiServiceType',
											fieldLabel: lang['geoservis'],
											hiddenName: 'ApiServiceType_id',
											lastQuery: '',
											listeners: {
												'change': function(combo, newValue, oldValue) {
													var index = combo.getStore().findBy(function(rec) {
														return (rec.get(combo.valueField) == newValue);
													});
													combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
												}.createDelegate(this),
												'select': function(combo, record, index) {
													win.showServiceFields();
												}
											},
											moreFields: [
												{ name: 'MedServiceType_id', mapping: 'MedServiceType_id' }
											],
											tabIndex: TABINDEX_MS + 9,
											xtype: 'swcommonsprcombo'
										}, {
											fieldLabel: 'Адрес сервиса',
											name: 'MedService_WialonURL',
											allowBlank: true,
											anchor: '95%',
											maxLength: 100,
											tabIndex: TABINDEX_MS + 10,
											xtype: 'textfield'
										}, {
											fieldLabel: 'Адрес авторизации',
											name: 'MedService_WialonAuthURL',
											allowBlank: true,
											anchor: '95%',
											maxLength: 100,
											tabIndex: TABINDEX_MS + 10,
											xtype: 'textfield'
										}, {
											layout : 'column',
											id: 'WialonTokenPanel',
											items: [{
												layout: 'form',
												width: 400,
												items: [{
													fieldLabel: lang['token'],
													name: 'MedService_WialonToken',
													allowBlank: true,
													anchor: '95%',
													maxLength: 100,
													tabIndex: TABINDEX_MS + 10,
													xtype: 'textfield'
												}]
											}, {
												layout : 'form',
												items : [{
													text: 'Получить токен',
													tabIndex: TABINDEX_MS + 10,
													handler: function()
													{
														var base_form = win.formPanel.getForm();
														var url = base_form.findField('MedService_WialonAuthURL').getValue();

														getWnd('swIframeWindow').show({
															title: 'Получение токена Wialon',
															url: url
														})
													},
													xtype: 'button'
												}]
											}]
										}, {
											fieldLabel: lang['port'],
											name: 'MedService_WialonPort',
											allowBlank: true,
											anchor: '95%',
											maxLength: 50,
											tabIndex: TABINDEX_MS + 11,
											xtype: 'numberfield',
											allowDecimals: false,
											validator: function(a){	return (a.match(/^[1-9]\d*$/))?true:false;}
										}, {
											fieldLabel: lang['imya'],
											name: 'MedService_WialonLogin',
											allowBlank: true,
											anchor: '95%',
											maxLength: 50,
											tabIndex: TABINDEX_MS + 12,
											xtype: 'textfield'
										}, {
											fieldLabel: lang['parol'],
											name: 'MedService_WialonPasswd',
											allowBlank: true,
											anchor: '95%',
											maxLength: 50,
											tabIndex: TABINDEX_MS + 13,
											xtype: 'textfield'
										}, {
											fieldLabel: langs('Автоматический запрос результатов'),
											hidden: getRegionNick() != 'perm',
											name: 'MedService_IsAutoQueryRes',
											listeners: {
												'check': function(checkbox, checked) {
													win.showServiceFields();
												}
											},
											xtype: 'checkbox'
										}, {
											fieldLabel: langs('Периодичность запроса (в часах)'),
											hidden: true,
											name: 'MedService_FreqQuery',
											maxLength: 10,
											value: '2',
											xtype: 'numberfield',
											allowNegative: false,
											width: 60,
											maxValue: 9999.99,
											minValue: 0
										}, {
											fieldLabel: langs('Передавать данные в ПАК НИЦ МБУ'),
											name: 'MedService_IsSendMbu',
											listeners: {
												'check': function(checkbox, checked) {
													//win.showServiceFields();
												}
											},
											tabIndex: TABINDEX_MS + 13,
											xtype: 'checkbox'
										}, {
											fieldLabel: lang['predstavlyatsya_kak'],
											name: 'MedService_WialonNick',
											allowBlank: true,
											anchor: '95%',
											maxLength: 50,
											tabIndex: TABINDEX_MS + 14,
											xtype: 'textfield'
										}, {
											fieldLabel: lang['data_sozdaniya'],
											name: 'MedService_begDT',
											allowBlank: false,
											format: 'd.m.Y',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											tabIndex: TABINDEX_MS + 15,
											xtype: 'swdatefield'
										}, {
											fieldLabel: lang['data_zakryitiya'],
											name: 'MedService_endDT',
											allowBlank: true,
											format: 'd.m.Y',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											tabIndex: TABINDEX_MS + 16,
											xtype: 'swdatefield'
										}, win.localServerFields
									]
								}

							]
						},
							{
							title: lang['atributyi_er'],
							id: 'tab_Attribute',
							iconCls: 'info16',
							autoHeight: true,
							border:false,
							items: [
								{
									layout:'form',
									autoHeight: true,
									labelAlign: 'right',
									labelWidth: 150,
									items:[
										{
											fieldLabel: lang['tolko_svoya_mo'],
											name: 'MedService_IsThisLPU',
											xtype: 'checkbox'
										},{
											anchor: '95%',
											allowBlank: false,
											fieldLabel: lang['zapis_v_ochered'],
											hiddenName: 'RecordQueue_id',
											autoLoad: false,
											xtype: 'swrecordqueuecombo'
										}
									]
								}

							]
						},
							{
							//BOB - 25.01.2017
							title: lang['obslujivaemyie_otdeleniya'],
							id: 'tab_MedServiceSection',
							iconCls: 'info16',
							autoHeight: true,
							border: false,
							// layout: 'border',
							items: [
								win.MedServiceSectionGrid
							]
							//BOB - 25.01.2017
						}]
					})
			 , {
				name: 'MedService_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'Org_id',
				xtype: 'hidden'
			}, {
				name: 'OrgStruct_id',
				xtype: 'hidden'
			}, {
				name: 'LpuBuilding_id',
				xtype: 'hidden'
			}, {
				name: 'LpuUnitType_id',
				xtype: 'hidden'
			}, {
				name: 'LpuUnit_id',
				xtype: 'hidden'
			}, {
				name: 'LpuSection_id',
				xtype: 'hidden'
			}, {
				name: 'Address_id',
				xtype: 'hidden'
			}, {
				name: 'Address_Zip',
				xtype: 'hidden'
			}, {
				name: 'KLCountry_id',
				xtype: 'hidden'
			}, {
				name: 'KLRGN_id',
				xtype: 'hidden'
			}, {
				name: 'KLSubRGN_id',
				xtype: 'hidden'
			}, {
				name: 'KLCity_id',
				xtype: 'hidden'
			}, {
				name: 'KLTown_id',
				xtype: 'hidden'
			}, {
				name: 'KLStreet_id',
				xtype: 'hidden'
			}, {
				name: 'Address_House',
				xtype: 'hidden'
			}, {
				name: 'Address_Corpus',
				xtype: 'hidden'
			}, {
				name: 'Address_Flat',
				xtype: 'hidden'
			}, {
				name: 'Address_Address',
				xtype: 'hidden'
			}, {
				name: 'LpuBuildingType_id',
				xtype: 'hidden'
			}],
			keys:
			[{
				alt: true,
				fn: function(inp, e)
				{
					switch (e.getKey())
					{
						case Ext.EventObject.C:
							if (this.action != 'view')
							{
								this.doSave();
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//
				}
			},
			[
				{ name: 'MedService_id' },
				{ name: 'MedService_Name' },
				{ name: 'MedService_Nick' },
				{ name: 'MedService_Code' },
				{ name: 'MedServiceType_id' },
				{ name: 'MedService_begDT' },
				{ name: 'MedService_endDT' },
				{ name: 'MedService_WialonLogin' },
				{ name: 'MedService_WialonPasswd' },
				{ name: 'MedService_WialonNick' },
				{ name: 'MedService_WialonURL' },
				{ name: 'MedService_WialonAuthURL' },
				{ name: 'MedService_WialonToken' },
				{ name: 'MedService_WialonPort' },
				{ name: 'MedService_IsThisLPU'},
				{ name: 'RecordQueue_id'},
				{ name: 'Lpu_id' },
				{ name: 'Org_id' },
				{ name: 'OrgStruct_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'LpuUnitType_id' },
				{ name: 'LpuUnit_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedService_IsExternal' },
				{ name: 'MedService_IsAutoQueryRes'},
				{ name: 'MedService_FreqQuery'},
				{ name: 'MedService_IsShowDiag' },
				{ name: 'MedService_IsQualityTestApprove' },
				{ name: 'MedService_IsSendMbu' },
				{ name: 'MedService_IsFileIntegration'},
				{ name: 'MedService_IsLocalCMP' },
				{ name: 'MedService_LocalCMPPath' },
				{ name: 'ApiServiceType_id' },
				{ name: 'Address_id' },
				{ name: 'Address_Zip' },
				{ name: 'KLCountry_id' },
				{ name: 'KLRGN_id' },
				{ name: 'KLSubRGN_id' },
				{ name: 'KLCity_id' },
				{ name: 'KLTown_id' },
				{ name: 'KLStreet_id' },
				{ name: 'Address_House' },
				{ name: 'Address_Corpus' },
				{ name: 'Address_Flat' },
				{ name: 'Address_Address' },
				{ name: 'Address_AddressText' },
				{ name: 'MseOffice_id' },
				{ name: 'LpuSectionAge_id'},
				{ name: 'LpuBuildingType_id'},
				{ name: 'LpuEquipmentPacs_id'}  //#146135
			]),
			timeout: 600,
			url: '/?c=MedService&m=saveRecord'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_MS + 20,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'cancel16',
				handler: function() {
					this.ownerCt.hide();
				},
				onTabElement: 'MSEW_MedService_Name',
				tabIndex: TABINDEX_MS + 21,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.formPanel
			]
		});
		sw.Promed.swMedServiceEditWindow.superclass.initComponent.apply(this, arguments);
	},
	getDefaultGeoServiceApiId: function(){
		var
			apiStore = this.formPanel.getForm().findField('ApiServiceType_id').getStore(),
			code = '',
			index,
			result = '';

		//На уфе по умолчанию ТНЦ
		//Пермь Псков Виалон
		//Астрахани пока нет но будет ЕНДС
		switch ( true ) {
			case getRegionNick().inlist(['ufa']):
				code = 2;
			break;

			case getRegionNick().inlist(['perm', 'pskov']):
				code = 1;
			break;

			case getRegionNick().inlist(['astra']):
				code = '';
			break;
		}

		if ( typeof apiStore == 'object' && apiStore.getCount() > 0 && !Ext.isEmpty(code) ) {
			index = apiStore.findBy(function(rec) {
				return rec.get('ApiServiceType_Code') == code;
			});

			if ( index >= 0 ) {
				result = apiStore.getAt(index).get('ApiServiceType_id');
			}
		}

		return result;
	},
	onChangeIsLocalCMP: function() {
		var win = this;
		var form = this.formPanel.getForm();

		form.findField('MedService_LocalCMPPath').disable();
		form.findField('MedService_LocalCMPPath').setAllowBlank(true);
		win.checkLocalServerButton.setDisabled(true);
		if (form.findField('MedService_IsLocalCMP').checked) {
			form.findField('MedService_LocalCMPPath').enable();
			form.findField('MedService_LocalCMPPath').setAllowBlank(false);
			win.checkLocalServerButton.setDisabled(false);
		}
	},
	showSendMbuField: function() {
		var win = this;
		var form = this.formPanel.getForm();
		var field = form.findField('MedService_IsSendMbu');
		// Признак "Передавать данные в ПАК НИЦ МБУ" видим, если установлен флаг «Внешняя служба» и тип службы «Лаборатория» или «Микробиологическая лаборатория» и если не Казахстан
		// /*&& form.findField('MedService_IsExternal').checked*/ - изначально в ТЗ было еще это, но поскольку в ПМИ такого нет, да и не логично - убрали
		if (/*!field.isVisible() && */getRegionNick() != 'kz' && form.findField('MedServiceType_id').getFieldValue('MedServiceType_Code') && form.findField('MedServiceType_id').getFieldValue('MedServiceType_Code').inlist([2,60])) {
			field.setContainerVisible(true);
		} else {
			field.setContainerVisible(false);
			field.setValue(0);
		}
	},
	showServiceFields: function() {
		var win = this;
		var form = this.formPanel.getForm();

		// По умолчанию скрываем поля
		form.findField('MedService_WialonLogin').hideContainer();
		form.findField('MedService_WialonPasswd').hideContainer();
		form.findField('MedService_WialonNick').hideContainer();
		form.findField('MedService_WialonURL').hideContainer();
		form.findField('MedService_WialonAuthURL').hideContainer();
		win.findById('WialonTokenPanel').hide();
		form.findField('MedService_WialonPort').hideContainer();
		form.findField('ApiServiceType_id').hideContainer();
		form.findField('LpuSectionAge_id').hideContainer();
		form.findField('LpuSectionAge_id').allowBlank = true;
		if (['kareliya', 'ekb'].indexOf(getRegionNick()) != -1) form.findField('MedService_IsShowDiag').hideContainer();
		win.localServerFields.hide();

		//показ-сокрытие поля "файловая интеграция"
		form.findField('MedService_IsFileIntegration').hideContainer();
		if(form.findField('MedServiceType_id').getFieldValue('MedServiceType_Code') !== null)
			if (getRegionNick() === 'vologda' && form.findField('MedServiceType_id').getFieldValue('MedServiceType_Code').inlist([1,2]))
				form.findField('MedService_IsFileIntegration').showContainer();

		if (form.findField('MedService_IsExternal').checked) {
			form.findField('MedService_WialonLogin').showContainer();
			form.findField('MedService_WialonPasswd').showContainer();
			form.findField('MedService_WialonNick').showContainer();
			form.findField('MedService_WialonURL').showContainer();
		}
		if (getRegionNick() == 'perm') {
			if (form.findField('MedService_IsExternal').checked && form.findField('MedServiceType_id').getFieldValue('MedServiceType_Code') == 2){
				form.findField('MedService_IsAutoQueryRes').showContainer();
				if(form.findField('MedService_IsAutoQueryRes').checked) {
					form.findField('MedService_FreqQuery').showContainer();
					form.findField('MedService_FreqQuery').allowBlank = false;
				}else{
					form.findField('MedService_FreqQuery').hideContainer();
					form.findField('MedService_FreqQuery').allowBlank = true;
				}
			}else{
				form.findField('MedService_IsAutoQueryRes').hideContainer();
				form.findField('MedService_IsAutoQueryRes').setValue(false);
				form.findField('MedService_FreqQuery').hideContainer();
				form.findField('MedService_FreqQuery').allowBlank = true;
			}
		}

		switch (form.findField('MedServiceType_id').getFieldValue('MedServiceType_Code')) {
			// Диагностика
			case 3:
				if (getRegionNick() == 'perm') {
					form.findField('MedService_WialonNick').showContainer();
					form.findField('MedService_WialonURL').showContainer();
					form.findField('MedService_WialonPort').showContainer();
				}
				break;
			// Служба неотложной помощи
			case 18:
				form.findField('LpuSectionAge_id').showContainer();
				form.findField('LpuSectionAge_id').allowBlank = false;
				if(!form.findField('LpuBuildingType_id').getValue().inlist(['28'])){
					break;
				}
				form.findField('ApiServiceType_id').showContainer();
				form.findField('MedService_WialonLogin').showContainer();
				form.findField('MedService_WialonPasswd').showContainer();
				if (!Ext.isEmpty(form.findField('ApiServiceType_id').getValue()) && form.findField('ApiServiceType_id').getFieldValue('ApiServiceType_Code') == '1') {
					form.findField('MedService_WialonAuthURL').showContainer();
					win.findById('WialonTokenPanel').show();
				}
				break;
			// Служба скорой медицинской помощи
			case 19:
				form.findField('ApiServiceType_id').showContainer();
				form.findField('MedService_WialonLogin').showContainer();
				form.findField('MedService_WialonPasswd').showContainer();
				if (!Ext.isEmpty(form.findField('ApiServiceType_id').getValue()) && form.findField('ApiServiceType_id').getFieldValue('ApiServiceType_Code') == '1') {
					form.findField('MedService_WialonAuthURL').showContainer();
					win.findById('WialonTokenPanel').show();
				}
				win.localServerFields.show();
				break;

			case 53:
				form.findField('ApiServiceType_id').showContainer();
				form.findField('MedService_WialonLogin').showContainer();
				form.findField('MedService_WialonPasswd').showContainer();
				if (!Ext.isEmpty(form.findField('ApiServiceType_id').getValue()) && form.findField('ApiServiceType_id').getFieldValue('ApiServiceType_Code') == '1') {
					form.findField('MedService_WialonAuthURL').showContainer();
					win.findById('WialonTokenPanel').show();
				}
				break;
		}

		win.syncShadow();
	},
	show: function() {
		sw.Promed.swMedServiceEditWindow.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}
		this.action = arguments[0].action || 'add';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide ||  Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		this.copyFromLpuSection = 0;

		this.doReset();
		this.center();
		var tabPanel = this.findById('MedServ-tabs-panel');
		var win = this,
		form = this.formPanel.getForm(),
		typ_combo = form.findField('MedServiceType_id'),
		api_combo = form.findField('ApiServiceType_id'),
		pacs_combo = form.findField('LpuEquipmentPacs_id');

		pacs_combo.getStore().baseParams = { Lpu_id: arguments[0].Lpu_id };
		pacs_combo.getStore().load();

		form.findField('MseOffice_id').hideContainer();

        this.MedServiceSectionGrid.getGrid().getStore().removeAll(); //BOB - 25.01.2017  очистка грида обслуживаемых отделений

        tabPanel.setActiveTab(2);  //BOB - 25.01.2017  чтобы прошли события инициализации страниц
		tabPanel.setActiveTab(1);
		tabPanel.setActiveTab(0);

		typ_combo.getStore().removeAll();

		form.setValues(arguments[0]);
		if ( arguments[0].OrgStruct_pid ) {
			form.findField('OrgStruct_id').setValue(arguments[0].OrgStruct_pid);
		}
		//form.findField('MedService_Code').setContainerVisible(false);

		if ( !Ext.isEmpty(form.findField('Lpu_id').getValue()) ) {
			// Определяем уровень структурного элемента МО
			var MedServiceLevelType_id = 4;
			tabPanel.unhideTabStripItem('tab_Attribute')
			if ( !Ext.isEmpty(form.findField('LpuSection_id').getValue()) ) {
				MedServiceLevelType_id = 1;
			}
			else if ( !Ext.isEmpty(form.findField('LpuUnit_id').getValue()) ) {
				MedServiceLevelType_id = 2;
			}
			else if ( !Ext.isEmpty(form.findField('LpuBuilding_id').getValue()) ) {
				MedServiceLevelType_id = 3;
			}

			// Получение списка доступных типов служб в зависимости от уровеня структурного элемента МО
			Ext.Ajax.request({
				url: '/?c=LpuStructure&m=getAllowedMedServiceTypes',
				params: {
					MedServiceLevelType_id: MedServiceLevelType_id
				},
				callback: function(options, success, response) {
					if ( success ) {
						win.allowedMedServiceTypes = Ext.util.JSON.decode(response.responseText);
						if (form.findField('LpuUnitType_id').getValue() != 1) {
							var indx = win.allowedMedServiceTypes.indexOf(72); // "Осмотр с целью госпитализации" только для круглосуточного стационара
							if (indx !== -1) win.allowedMedServiceTypes.splice(indx, 1);
						}
						typ_combo.onLoadStore();
					}
				}
			});
		}
		else if ( !Ext.isEmpty(form.findField('Org_id').getValue()) ) {
			win.Org_id = form.findField('Org_id').getValue();
			tabPanel.hideTabStripItem('tab_Attribute')
			// получить для организации список доступных типов медсервисов в зависимости от типа организации.
			Ext.Ajax.request(
			{
				url: '/?c=OrgStruct&m=getAllowedMedServiceTypes',
				params: {
					Org_id: win.Org_id
				},
				callback: function(options, success, response)
				{
					if (success)
					{
						win.allowedMedServiceTypes = Ext.util.JSON.decode(response.responseText);
						typ_combo.onLoadStore();
					}
				}
			});
		}
		else {
			win.allowedMedServiceTypes = new Array();
			typ_combo.onLoadStore();
		}

		switch (this.action) {
			case 'view':
				this.setTitle(lang['slujba_prosmotr']);
			break;

			case 'edit':
				this.setTitle(lang['slujba_redaktirovanie']);
			break;

			case 'add':
				this.setTitle(lang['slujba_dobavlenie']);
			break;

			default:
				log('swMedServiceEditWindow - action invalid');
				return false;
			break;
		}

		var medservicetypeparams;
		if(!isSuperAdmin()) {
			// для пользователя накладываем фильтр #10475
			medservicetypeparams = {params: {where: " where MedServiceType_IsAdmin != 2"}};
		}
		if(this.action == 'add')
		{
			if ( Ext.isEmpty(medservicetypeparams) ) {
				medservicetypeparams = {params: {where: " where MedServiceType_SysNick != 'app'"}};
			}
			else {
				medservicetypeparams.params.where += " and MedServiceType_SysNick != 'app'";
			}
			this.allowEdit(true);
			typ_combo.getStore().load(medservicetypeparams);
			typ_combo.fireEvent('change', form.findField('MedServiceType_id'), form.findField('MedServiceType_id').getValue());
			win.onChangeIsLocalCMP();
			form.findField('RecordQueue_id').setValue(1);
			api_combo.setValue(this.getDefaultGeoServiceApiId());
			form.findField('MseOffice_id').getStore().load();
			this.syncSize();
			this.doLayout();
		}
		else
		{
			win.allowEdit(false);
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();
			this.formPanel.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
				},
				params: {
					MedService_id: form.findField('MedService_id').getValue()
				},
				success: function(pan,resp) {
					var result = resp.result.data;
					win.getLoadMask().hide();
					if(win.action == 'edit')
					{
						win.allowEdit(true);
					}
					// для пользователя убираем фильтр, чтобы отобразилось, но не даем изменить #10475
					medservicetypeparams = {
						callback: function(r,o,s){
							for(var i=0; i<r.length; i++){
								if( !isSuperAdmin() && typ_combo.getValue() == r[i].get('MedServiceType_id') && r[i].get('MedServiceType_IsAdmin') == 2 ) {
									typ_combo.setDisabled(true);
									break;
								}
							}
							typ_combo.fireEvent('change', form.findField('MedServiceType_id'), form.findField('MedServiceType_id').getValue());
							win.onChangeIsLocalCMP();
						}
					};

					if ( form.findField('MedServiceType_id').getValue() != 30 ) {
						medservicetypeparams.params = {
							where: " where MedServiceType_SysNick != 'app'"
						}
					}

					typ_combo.getStore().load(medservicetypeparams);

					api_combo.fireEvent('change', api_combo, api_combo.getValue());

					form.findField('MseOffice_id').getStore().load({
						callback: function() {
							form.findField('MseOffice_id').setValue(form.findField('MseOffice_id').getValue());
						}
					});

					if (getRegionNick() == 'perm') {
						form.findField('MedService_IsAutoQueryRes').setValue((result.MedService_IsAutoQueryRes == 1)?true:false);
						form.findField('MedService_FreqQuery').setValue((result.MedService_FreqQuery == null)?2:result.MedService_FreqQuery);
					}

					win.syncSize();
					win.doLayout();
				},
				url: '/?c=MedService&m=loadEditForm'
			});
		}
	},

	//BOB - 25.01.2017
	//открытие диалога выбора отделений для добавления в качестве обслуживаемых
	openMedServiceSectionEditWindow: function(action){
		//alert('Добавление обслуживаемого отделения - ' + action);

		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		var base_form = this.formPanel.getForm();
		var grid = this.MedServiceSectionGrid.getGrid();

		var params = new Object();
		params.action = action;
		params.LpuUnitType_id = base_form.findField('LpuUnitType_id').getValue();
		params.formParams = new Object();

		if (action == 'add') {
			params.formParams.LpuSection_id = base_form.findField('MedService_id').getValue();
		} else {   //!!!!!эта ветвь никогда не работает, по крайней мере пока данная функция вызывается тлько для добавления: action == 'add'
			var record = grid.getSelectionModel().getSelected();
			if ( !record || !record.get('MedServiceSection_id') ) {
				return false;
			}
			params.formParams.LpuSectionService_id = record.get('MedServiceSection_id');
		}

		params.callback = function(data) {
			//проверка объекты ли data data.MedServiceSectionData
			if ( typeof data != 'object' || typeof data.LpuSectionServiceData != 'object' ) {
				return false;
			}
			//перевод в мои проперти, чтобы в названиях не запутаться, а swLpuSectionServiceEditWindow не трогать  - всё сделать здесь,
			data.MedServiceSectionData = new Object();
			data.MedServiceSectionData.LpuSection_id = data.LpuSectionServiceData.LpuSection_did;
			data.MedServiceSectionData.MedService_id = data.LpuSectionServiceData.LpuSection_id;  //см. 1008
			data.MedServiceSectionData.MedServiceSection_id  = data.LpuSectionServiceData.LpuSectionService_id;
			data.MedServiceSectionData.RecordStatus_Code = data.LpuSectionServiceData.RecordStatus_Code;

			//проверка на выбор уже присоединённого отделения
			var index = grid.getStore().findBy(function(rec){
				return (rec.get('LpuSection_id')==data.MedServiceSectionData.LpuSection_id && rec.get('MedServiceSection_id')!=data.MedServiceSectionData.MedServiceSection_id);
			});
			if (index >= 0) {
				sw.swMsg.alert(lang['oshibka'], lang['vyibrannoe_otdelenie_uje_ukazano']);
				return false;
			}

			data.MedServiceSectionData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.MedServiceSectionData.MedServiceSection_id);  //пока функция вызывается тлько для добавления: action == 'add' MedServiceSection_id - пустой, record не найдётся

			Ext.Ajax.request({

				url: '/?c=MedService&m=getRowMedServiceSection',
				params: data.MedServiceSectionData,
				callback: function(options, success, response) {

					// если входной параметр success = false
					if (!success) {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						return false;
					}
					var response_obj = Ext.util.JSON.decode(response.responseText);


					// если переданный с сервера в response success = false
					if (!response_obj.success) {
						// если при этом нечто передано в Data, то это отделение уже прикреплённое к другой службе
						if (response_obj.data) {
							sw.swMsg.alert(lang['oshibka'], 'Данное отделение уже прикреплено к другой службе - "'+response_obj.data.MedService_Name+'" под "'+response_obj.data.ParentName+'"');
							return false;
						}
						else { // иначе не найдено данных для добавления
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
							return false;
						}

					}


					//    console.log('BOB_Object2=',response_obj);  //BOB - 25.01.2017
					if ( typeof record == 'object' ) {  //!!!!!эта ветвь никогда не работает, по крайней мере пока функция вызывается только для добавления: action == 'add', record - не найден

						if ( record.get('RecordStatus_Code') == 1 ) {
							response_obj.data.RecordStatus_Code = 2;
						}

						var grid_fields = new Array();
						grid.getStore().fields.eachKey(function(key, item) {
							grid_fields.push(key);
						});

						for ( i = 0; i < grid_fields.length; i++ ) {
							record.set(grid_fields[i], response_obj.data[grid_fields[i]]);
						}

						record.commit();

					} else {

						if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('MedServiceSection_id') ) {
							grid.getStore().removeAll();
						}
						response_obj.data.MedServiceSection_id = -swGenTempId(grid.getStore());

						grid.getStore().loadData([response_obj.data], true);
					}
				}
			});
		};

		getWnd('swLpuSectionServiceEditWindow').show(params);
	},

	//удаление обслуживаемых отделений из грида
	deleteMedServiceSection: function(action){
		var grid = this.MedServiceSectionGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('MedServiceSection_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
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
			},
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	}
    //BOB - 25.01.2017



});
