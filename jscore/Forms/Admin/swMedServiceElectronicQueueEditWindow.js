/**
 * swMedServiceElectronicQueueEditWindow - Электронная очередь службы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Admin
 * @access      public
 * @copyright	Copyright (c) 2017 Swan Ltd.
 */
/*NO PARSE JSON*/
sw.Promed.swMedServiceElectronicQueueEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: false,
	maximized: false,
	modal: true,
	autoHeight: true,
	width: 550,
	id: 'swMedServiceElectronicQueueEditWindow',
	title: 'Электронная очередь',
	layout: 'form',
	resizable: false,
	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = {};
		}

		var win = this,
			form = this.formEditPanel.getForm(),
			loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
			
		if (!form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formEditPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {};
        params.ignoreDoublesByMedPersonal = (options && !Ext.isEmpty(options.ignoreDoublesByMedPersonal) && options.ignoreDoublesByMedPersonal === 1) ? 1 : 0;
		params.MedServiceType_SysNick = ((win.MedServiceType_SysNick) ? win.MedServiceType_SysNick : "");

		loadMask.show();
		form.submit({
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
                    if ( action.result.Alert_Msg && 'YesNo' == action.result.Error_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									switch ( true ) {
										case ('MSEQ001' == action.result.Error_Code):
											options.ignoreDoublesByMedPersonal = 1;
											break;
									}

									win.doSave(options);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: lang['prodoljit_sohranenie']
						});
					}
					else if ( !Ext.isEmpty(action.result.Error_Msg) ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
				}
			},
			params: params,
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if ( action.result.MedServiceElectronicQueue_id ) {
						win.hide();
						win.returnFunc(Ext.getCmp('swMedServiceElectronicQueueEditWindow').owner, action.result.MedServiceElectronicQueue_id);
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], 'При сохранении возникли ошибки');
				}	
			}.createDelegate(this)
		});
	},
	initComponent: function()
	{
		var win = this;

		this.formEditPanel = new Ext.FormPanel({
			region: 'center',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 150,
			frame: true,
			border: false,
			items: [{
				name: 'MedServiceElectronicQueue_id',
				xtype: 'hidden'
			}, {
				name: 'MedService_id',
				xtype: 'hidden'
			},{
				name: 'LpuBuilding_id',
				xtype: 'hidden'
			},
			{
				name: 'LpuSection_id',
				xtype: 'hidden'
			},
			{
				allowBlank: true,
				fieldLabel: lang['usluga'],
				hiddenName: 'UslugaComplexMedService_id',
				valueField: 'UslugaComplexMedService_id',
				listWidth: 600,
				width: 350,
				xtype: 'swuslugacomplexpidcombo'
			},{
				allowBlank: false,
				fieldLabel: langs('Ресурс'),
				hiddenName: 'Resource_id',
				valueField: 'Resource_id',
				listWidth: 600,
				width: 350,
				xtype: 'swresourceremotecombo'
			},{
				editable: false,
				allowBlank: false,
				fieldLabel: 'Пункт обслуживания',
				hiddenName: 'ElectronicService_id',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var form = win.formEditPanel.getForm()
							num_filed = form.findField('ElectronicService_Num');
						if (newValue) {
							num_filed.setValue(field.getFieldValue('ElectronicService_Num'));
						} else {
							num_filed.setValue(null);
						}
					}
				},
				mode: 'local',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{name: 'ElectronicService_id', type: 'int'},
						{name: 'ElectronicService_Code', type: 'string'},
						{name: 'ElectronicService_Name', type: 'string'},
						{name: 'ElectronicService_Nick', type: 'string'},
						{name: 'ElectronicService_Num', type: 'int'},
						{name: 'ElectronicQueueInfo_Name', type: 'string'}
					],
					key: 'ElectronicService_id',
					sortInfo: {
						field: 'ElectronicService_Code'
					},
					url: '/?c=ElectronicService&m=loadElectronicServicesList'
				}),
				width: 350,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<div style="font-weight: bold; white-space: normal;">{ElectronicService_Name}' +
					'<span style="color: red">&nbsp;{ElectronicService_Nick}</span></div>',
					'<div style="font-size: 10px;">{ElectronicQueueInfo_Name}</div>',
					'</div></tpl>'
				),
				displayField: 'ElectronicService_Name',
				valueField: 'ElectronicService_id',
				xtype: 'swbaselocalcombo'
			}, {
				name: 'ElectronicService_Num',
				fieldLabel: 'Порядковый номер',
				xtype: 'numberfield',
				disabled: true,
				changeDisabled: false,
				width: 100
			}, {
				hiddenName: 'MedServiceMedPersonal_id',
				fieldLabel: 'Сотрудник на службе',
				xtype: 'swmedservicemedpersonalcombo',
				allowBlank: true,
				width: 350
			},
			{
				hiddenName: 'MedStaffFact_id',
				fieldLabel: 'Врач',
				xtype: 'swmedstafffactbylpustructurecombo',
				allowBlank: true,
				listWidth: 700,
				width: 350
			}
			],
			reader: new Ext.data.JsonReader({}, [
				{ name: 'MedServiceElectronicQueue_id' },
				{ name: 'MedService_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'LpuSection_id' },
				{ name: 'UslugaComplexMedService_id' },
				{ name: 'ElectronicService_id' },
				{ name: 'ElectronicService_Num' },
				{ name: 'MedServiceMedPersonal_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'Resource_id' }
			]),
			url: '/?c=MedServiceElectronicQueue&m=save'
		});

		Ext.apply(this, {
			items: [
				this.formEditPanel
			],
			buttons: [{
				text: BTN_FRMSAVE,
				iconCls: 'save16',
				handler: function() {
					win.doSave();
				}
			}, {
				text: '-'
			},
			//HelpButton(this, TABINDEX_RRLW + 13),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RRLW + 14,
				handler: function() {
					win.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});

		sw.Promed.swMedServiceElectronicQueueEditWindow.superclass.initComponent.apply(this, arguments);
	},
	setFieldsDisabled: function(d) {
		var form = this.formEditPanel.getForm();
		form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		this.buttons[0].show();
		this.buttons[0].setDisabled(d);
	},
	hideCombos: function(combos) {

		Object.keys(combos).forEach(function(k){
			combos[k].hideContainer();
		});
	},
	clearComboParams: function(combos) {

		var win = this;

		Object.keys(combos).forEach(function(k){
			win.clearBaseParams(combos[k]);
		});
	},
	setNotRequired: function(combos) {

		Object.keys(combos).forEach(function(k){
			combos[k].setAllowBlank(true);
		});
	},
	initCombos: function(combos) {

		var win = this;

		win.hideCombos(combos);
		win.clearComboParams(combos);
		win.setNotRequired(combos);
		win.loadCombosByTargetParam({combos: combos, mainParam: win.targetParam});

	},
	show: function() {
		sw.Promed.swMedServiceElectronicQueueEditWindow.superclass.show.apply(this, arguments);
		
		var win = this,
			form = this.formEditPanel.getForm();

		this.MedService_id = null;
		this.LpuBuilding_id = null;
		this.LpuSection_id = null;
		this.MedServiceElectronicQueue_id = null;
		
		form.reset();
		
		if (arguments[0]['action']) this.action = arguments[0]['action'];
		if (arguments[0].owner) this.owner = arguments[0].owner;
		if (arguments[0]['callback']) this.returnFunc = arguments[0]['callback'];
		
		if (arguments[0]['MedServiceElectronicQueue_id']) {
			win.MedServiceElectronicQueue_id = arguments[0]['MedServiceElectronicQueue_id'];
		}
		
		if (arguments[0]['MedService_id']) {
			win.targetParam = "MedService_id";
			win.MedService_id = arguments[0]['MedService_id'];
			win.MedServiceType_SysNick = (arguments[0]['MedServiceType_SysNick']) ? arguments[0]['MedServiceType_SysNick'] : '';

			form.findField('MedService_id').setValue(win.MedService_id);
		}

		if (arguments[0]['LpuBuilding_id']) {
			win.targetParam = "LpuBuilding_id";
			win.LpuBuilding_id = arguments[0]['LpuBuilding_id'];

			form.findField('LpuBuilding_id').setValue(win.LpuBuilding_id);
		}

		if (arguments[0]['LpuSection_id']) {
			win.targetParam = "LpuSection_id";
			win.LpuSection_id = arguments[0]['LpuSection_id'];

			form.findField('LpuSection_id').setValue(win.LpuSection_id);
		}

		this.setFieldsDisabled(this.action == 'view');

		var combos = {
			usluga: form.findField('UslugaComplexMedService_id'),
			mp: form.findField('MedServiceMedPersonal_id'),
			es: form.findField('ElectronicService_id'),
			msf: form.findField('MedStaffFact_id'),
			resource:  form.findField('Resource_id')
		};

		win.initCombos(combos);
		switch (win.targetParam) {

			case "LpuBuilding_id":
			case "LpuSection_id":

				combos.msf.showContainer();
				combos.msf.setAllowBlank(false);
				break;

			case "MedService_id":

				combos.mp.showContainer();
				combos.mp.setAllowBlank(false);

				combos.usluga.showContainer();

				switch (this.MedServiceType_SysNick) {
					case "pzm" :
					case "regpol" :
						combos.usluga.setAllowBlank(true); // не обяз
						break;
					case "func" :
						combos.resource.showContainer();
						combos.resource.setAllowBlank(false);
						combos.usluga.hideContainer();
						combos.usluga.setAllowBlank(true);
						break;
					default:
						combos.usluga.setAllowBlank(false);
						combos.usluga.focus(true, 100);
				}

				break;
		}

		combos.es.showContainer();
		combos.es.setAllowBlank(false);

		win.syncSize();
		win.syncShadow();
		
		switch (this.action){
			case 'add':

				this.setTitle('Электронная очередь: Добавление');

				if (!(this.MedService_id || this.LpuBuilding_id || this.LpuSection_id)) {
					Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
					this.hide();
					return false;
				}

				break;

			case 'edit':
				this.setTitle('Электронная очередь: Редактирование');
				break;
			case 'view':
				this.setTitle('Электронная очередь: Просмотр');
				break;
		}
		
		if (this.action != 'add') {
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
			loadMask.show();
			form.load({
				url: '/?c=MedServiceElectronicQueue&m=load',
				params: {
					MedServiceElectronicQueue_id: win.MedServiceElectronicQueue_id
				},
				success: function (frm, action) {
					win.loadCombo();
					loadMask.hide();
				},
				failure: function (frm, action) {
					loadMask.hide();
					if (!action.result.success) {
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
						this.hide();
					}
				},
				scope: this
			});
		}
	},
	loadCombo: function(combo, mainParam) {

		if (combo) {

			combo.getStore().removeAll();
			combo.getStore().clearFilter();
			combo.lastQuery = '';
			combo.getStore().baseParams[mainParam] = this[mainParam];
			combo.getStore().load({
				callback: function () {
					combo.setValue(combo.getValue());
				}
			});
		}
	},
	loadCombosByTargetParam: function(p) {

		var wnd = this;

		// подгружаем комбо связанные со службой
		if (p.mainParam == 'MedService_id') {

			wnd.loadCombo(p.combos.usluga, p.mainParam);
			wnd.loadCombo(p.combos.mp, p.mainParam);
			wnd.loadCombo(p.combos.resource, p.mainParam)

		} else 	wnd.loadCombo(p.combos.msf, p.mainParam);

		// подгружаем комбо с пунктами обслуживания
		wnd.loadCombo(p.combos.es, p.mainParam);
	},
	clearBaseParams: function(combo) {

		var params = [
			'MedService_id',
			'LpuBuilding_id',
			'LpuSection_id'
		];

		params.forEach(function(prm){
			combo.getStore().baseParams[prm] = '';
		});
	}
});