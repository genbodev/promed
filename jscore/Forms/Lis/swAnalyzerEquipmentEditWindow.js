/**
 * swAnalyzerEquipmentEditWindow - окно редактирования "Анализатор"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Common
 * @access	   public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @author	   Dmitry Vlasenko
 * @version	  24.08.2013
 * @comment
 */
sw.Promed.swAnalyzerEquipmentEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	title: lang['analizator'],
	layout: 'form',
	id: 'AnalyzerEquipmentEditWindow',
	modal: true,
	shim: false,
	width: 500,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	getAnalyzerCode: function() {
		var win = this, form = this.formPanel.getForm();
		var code = form.findField('Analyzer_Code');
		var MedServiceId = form.findField('MedService_id').getValue();
		
		//if ( this.action == 'view' || code.disabled || code.getValue().length==6) { // если просмотр или компонент задисаблен, или код уже сгенерирован, то ничего не делаем дальше
		if ( this.action == 'view' || code.disabled ) { // если просмотр или компонент задисаблен, или код уже сгенерирован, то ничего не делаем дальше
			return false;
		}
		
		if (code.getValue().length > 0) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' )
					{
						win.getLoadMask( "Определение порядкового номера анализатора...").show();
						win.requestAnalyzerCode( MedServiceId, code );
						win.getLoadMask().hide();
					}
				},
				msg: 'Код анализатора не пустой! Вы уверены, что хотите повторно его сгенерировать?',
				title: 'Подтверждение'
			});
		} else {
			win.getLoadMask( "Определение порядкового номера анализатора...").show();
			win.requestAnalyzerCode( MedServiceId, code );
			win.getLoadMask().hide();
		}
	},
	requestAnalyzerCode: function( MedServiceId, code ) {
		Ext.Ajax.request({
			params: {
					MedService_id: MedServiceId
			},
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj[0]) {
						code.setValue(response_obj[0].Analyzer_Code);
						code.focus(true);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_poryadkovogo_nomera_analizatora']);
						//sw.swMsg.alert('Ошибка', response.responseText);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_poryadkovogo_nomera_analizatora']);
				}
			},
			url: '/?c=Analyzer&m=getAnalyzerCode'
		});
	},
	visibleIsManualTechnic: function () {
		let form = this.formPanel.getForm();
		form.findField('Analyzer_Name').disable();
		form.findField('Analyzer_Code').disable();
		form.findField('AnalyzerModel_id').disable();
		form.findField('Analyzer_begDT').disable();
		form.findField('Analyzer_IsManualTechnic').disable();
		form.findField('Analyzer_2wayComm').hideContainer();
	},
	showLisSelectEquipmentWindow: function() {
		var win = this;
		var base_form = this.formPanel.getForm();
		
		if (win.action != 'view') {
			getWnd('swLisSelectEquipmentWindow').show({
				MedService_id: base_form.findField('MedService_id').getValue(),
				callback: function(rec, tests) {
					if (!Ext.isEmpty(rec.equipment_id)) {
						base_form.findField('equipment_id').setValue(rec.equipment_id);
						base_form.findField('Test_JSON').setValue(Ext.util.JSON.encode(tests));
						base_form.findField('Analyzer_Name').setValue(rec.equipment_name);
						base_form.findField('Analyzer_Code').setValue(rec.equipment_code);
						base_form.findField('Analyzer_Name').focus(true);
					} else {
						win.hide();
					}
				},
				onCancel: function() {
					win.hide();
				}
			});
		}
	},
	show: function() {
		sw.Promed.swAnalyzerEquipmentEditWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = this.formPanel.getForm();
		win.formStatus = 'edit';
		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.Analyzer_id = null;
		this.fromLIS = false;
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { win.hide(); });
			return false;
		}
		var MedService_id;
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].Analyzer_id ) {
			this.Analyzer_id = arguments[0].Analyzer_id;
		}
		if ( arguments[0].fromLIS ) {
			this.fromLIS = arguments[0].fromLIS;
		}
		base_form.findField('AnalyzerModel_id').getStore().load();
		base_form.reset();
		
		win.getLoadMask(lang['zagruzka_dannyih_formyi']).show();		
		switch (win.action) {
			case 'add':
				win.enableEdit(true);
				if ( arguments[0].MedService_id ) {
					base_form.findField('MedService_id').setValue(arguments[0].MedService_id);
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { loadMask.hide(); win.hide(); });
					return false;
				}
				win.setTitle(lang['analizator_dobavlenie']);
				win.getLoadMask().hide();

				win.formPanel.getForm().findField('Analyzer_IsManualTechnic').showContainer();
				win.formPanel.getForm().findField('Analyzer_2wayComm').showContainer();
				if (this.fromLIS && Ext.isEmpty(base_form.findField('equipment_id').getValue())) {
					// показать форму выбора анализатора в ЛИС
					win.showLisSelectEquipmentWindow();
				}
				break;
			case 'edit':
			case 'view':
				if (win.action == 'edit') {
					win.setTitle(lang['analizator_redaktirovanie']);
					win.enableEdit(true);
				} else {
					win.setTitle(lang['analizator_prosmotr']);
					win.enableEdit(false);
				}
				win.formPanel.loadForm({
						Analyzer_id: win.Analyzer_id
				});
				break;
		}
		return true;
	},
	initComponent: function() {
		var win = this;
		win.formPanel = new sw.Promed.FormPanel({
			saveUrl: '/?c=Analyzer&m=save',
			url: '/?c=Analyzer&m=load',
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			autoHeight: true,
			region: 'center',
			labelAlign: 'right',
			labelWidth: 150,
			items: [{
				name: 'Analyzer_id',
				xtype: 'hidden',
				value: 0
			}, {
				name: 'equipment_id',
				xtype: 'hidden'
			}, {
				name: 'Test_JSON',
				xtype: 'hidden'
			},
			{
				fieldLabel: lang['naimenovanie'],
				name: 'Analyzer_Name',
				allowBlank:false,
				xtype: 'textfield',
				anchor: '100%'
			}, {
				anchor: '100%',
				allowBlank: false,
				fieldLabel: lang['kod'],
				readOnly: true,
				//disabled: true,
				name: 'Analyzer_Code',
				maxLength: 6,
				//tabIndex: TABINDEX_MS + 5,
				triggerClass: 'x-form-plus-trigger',
				validateOnBlur: false,
				enableKeyEvents: true,
				onTriggerClick: function() {
					this.getAnalyzerCode();
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
				fieldLabel: lang['model_analizatora'],
				hiddenName: 'AnalyzerModel_id',
				xtype: 'swanalyzermodelcombo',
				allowBlank:true,
				anchor: '100%'
			},
			{
				name: 'MedService_id',
				xtype: 'hidden'
			},
			{
				fieldLabel: lang['data_otkryitiya'],
				name: 'Analyzer_begDT',
				allowBlank:false,
				xtype: 'swdatefield'
			},
			{
				fieldLabel: lang['data_zakryitiya'],
				name: 'Analyzer_endDT',
				allowBlank:true,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['ispolzovanie_dvustoronney_svyazi'],
				name: 'Analyzer_2wayComm',
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['ruchnie_metodiki'],
				name: 'Analyzer_IsManualTechnic',
				xtype: 'checkbox',
					listeners: {
						check: function(field, checked) {
							var modelField = win.formPanel.getForm().findField('AnalyzerModel_id');
							modelField.setAllowBlank(checked);
							modelField.setDisabled(checked);
						}
					}
					
			}, {
				fieldLabel: lang['ispolzovanie_avtomaticheskogo_ucheta'],
				name: 'Analyzer_IsUseAutoReg',
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['neaktivnyiy'],
				name: 'Analyzer_IsNotActive',
				xtype: 'checkbox'
			}, {
				xtype: 'swbaselocalcombo',
				fieldLabel: langs('Автоодобрение'),
				anchor: '100%',
				hiddenName: 'AutoOkType',
				displayField: 'name',
				allowBlank: false,
				valueField: 'id',
				value: 1,
				store: new Ext.data.SimpleStore({
					fields: [ { name:'id' }, {name: 'name'} ],
					data: [
						[0, 'Нет'],
						[1, 'Все тесты'],
						[2, 'Только без патологий']
					]
				})
			}],
			beforeSave: function(params) {
				if (win.fromLIS && !params.equipment_id) {
					// если вдруг не выбран анализатор лис -> показать форму выбора анализатора в ЛИС
					win.formStatus = 'edit';
					win.showLisSelectEquipmentWindow();
					return false;
				}
			},
			saveForm: function (params) {
				let panel = this,
					win = panel.getOwnerWindow(),
					onSave = Ext.emptyFn,
					form = panel.getForm(),
					mask = new Ext.LoadMask(panel.getEl(), langs('Сохранение..'));

				form.findField('Analyzer_Name').enable();
				form.findField('Analyzer_Code').enable();
				form.findField('Analyzer_begDT').enable();
				form.findField('Analyzer_IsManualTechnic').enable();

				let saveParams = {
					params: form.getValues(),
					url: form.saveUrl,
					callback: function (options, success, response) {
						if (panel.maskOnlyContainer) {
							mask.hide();
						} else if (win.hideLoadMask) {
							win.hideLoadMask();
						}

						var resp_obj = jsonDecode(response.responseText);
						if (!resp_obj || !success || resp_obj.Error_Code || resp_obj.Error_Msg) {
							return;
						}

						onSave(resp_obj);
						if (panel.afterSave) {
							panel.afterSave(resp_obj);
						}
					}
				};

				if (panel.beforeSave) {
					panel.beforeSave(saveParams.params);
				}

				//функция вызываемая при ошибке валидации
				if (typeof (panel.onValidationError) !== "function") {
					panel.onValidationError = function () {
						var inviledFields = panel.getInvalid();
						if (inviledFields.length) {
							inviledFields[0].focus();
						}
					};
				}

				if (!form.isValid()) {
					sw.swMsg.alert(langs("Ошибка"), panel.getInvalidFieldsMessage(), panel.onValidationError);
					return
				}

				if (typeof (panel.validateForm) === "function" && !panel.validateForm(saveParams)) {
					return;
				}

				if (panel.maskOnlyContainer) {
					mask.show();
				} else if (win.showLoadMask) {
					win.showLoadMask('Сохранение..');
				}

				Ext.Ajax.request(saveParams);
			},
			afterSave: function(result) {
				win.callback(win.owner, result.Analyzer_id );
				win.hide();
			},
			afterLoad: function (response) {
				win.getLoadMask().hide();
				if (win.fromLIS && !response.equipment_id) {
				// показать форму выбора анализатора в ЛИС
				 	win.showLisSelectEquipmentWindow();
				}
				
				var form = win.formPanel.getForm();
				//Если ручные методики, то вырубаем некоторые поля
				if (win.action != 'add' && (response.Analyzer_IsManualTechnic === '1' || response.Analyzer_IsManualTechnic === 1)) {
					win.visibleIsManualTechnic();
					form.findField('Analyzer_IsManualTechnic').showContainer();
				} else {
					form.findField('Analyzer_IsManualTechnic').hideContainer();
					form.findField('Analyzer_2wayComm').showContainer();
				}
			}
		});
		Ext.apply(this, {
			items:[ this.formPanel ]
		});
		sw.Promed.swAnalyzerEquipmentEditWindow.superclass.initComponent.apply(this, arguments);
	}
});