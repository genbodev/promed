/**
* swMzDrugRequestAddWindow - окно редактирования "Справочник медикаментов: заявки по медикаментам"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Rustam Salakhov
* @version      07.2015
* @comment      
*/
sw.Promed.swMzDrugRequestAddWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['zayavka_vracha_dobavlenie'],
	layout: 'border',
	id: 'MzDrugRequestAddWindow',
	modal: true,
	shim: false,
	resizable: false,
	maximizable: false,
	maximized: false,
	height: 317,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	setValues: function() {
		var wnd = this;
		var field_arr = [
			'DrugRequestPeriod_id',
			'PersonRegisterType_id',
			'DrugGroup_id',
			'DrugRequestKind_id',
			'Lpu_id',
			'LpuUnit_id',
			'LpuSection_id',
            'LpuRegion_id',
			'MedPersonal_id'
		];

		this.form.reset();

		//установка параметров комбобоксов
		this.form.findField('MedPersonal_id').getStore().proxy.conn.url = '/?c=MzDrugRequest&m=loadMedPersonalCombo';

		for (var i in field_arr) {
			if (this.form.findField(field_arr[i])) {
				var combo_name = field_arr[i];
				var combo = this.form.findField(combo_name);
				var value = this.FormParams[combo_name];

				if (combo.childrenList) {
					combo.childrenList.forEach(function(field_name) {
						var field = wnd.form.findField(field_name);
						if (field.getStore()) {
							field.getStore().baseParams[combo_name] = !Ext.isEmpty(value) ? value : null;
						}
					})
				}

				if (combo_name in this.FormParams) {
					combo.disable();
				} else {
					combo.enable();
				}

				if (!Ext.isEmpty(value)) {
					if ('setValueById' in combo) {
						combo.setValueById(value);
					} else {
						combo.setValue(value);
					}
				} else {
					if ('loadData' in combo) {
						combo.loadData();
					}
				}
			}
		}
	},
	doSave:  function() {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('MzDrugRequestAddForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit();
		return true;		
	},
	submit: function() {
		var wnd = this;
		var save_enabled = true;

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		//loadMask.show();

		var params = new Object();

		params.DrugRequestCategory_Code = 1 //1 - Начальная;
		params.DrugRequestStatus_Code = 1 //1 - Заявка врача;
		params.DrugRequest_Name = lang['zayavka_vracha'];

		params.DrugRequestPeriod_id = this.form.findField('DrugRequestPeriod_id').getValue();
		params.PersonRegisterType_id = this.form.findField('PersonRegisterType_id').getValue();
		params.DrugGroup_id = this.form.findField('DrugGroup_id').getValue();
		params.DrugRequestKind_id = this.form.findField('DrugRequestKind_id').getValue();
		params.Lpu_id = this.form.findField('Lpu_id').getValue();
		params.LpuUnit_id = this.form.findField('LpuUnit_id').getValue();
		params.LpuRegion_id = this.form.findField('LpuRegion_id').getValue();
		params.LpuSection_id = this.form.findField('LpuSection_id').getValue();
		params.MedPersonal_id = this.form.findField('MedPersonal_id').getValue();

		//проверки
		if (params.PersonRegisterType_id > 0) {
			if (Ext.isEmpty(params.Lpu_id)) {
                Ext.Msg.alert(langs('Ошибка'), 'Для сохранения заявки необходимо указать МО');
                save_enabled = false;
            }
            if (Ext.isEmpty(params.LpuRegion_id) && Ext.isEmpty(params.LpuSection_id)) {
                Ext.Msg.alert(langs('Ошибка'), 'Для сохранения заявки необходимо указать отделение или участок');
                save_enabled = false;
            }
		}

		if (save_enabled) {
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				//loadMask.hide();
				if (action.result)  {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#'] + action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				//loadMask.hide();
				if(action.result && action.result.alreadyExist) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						scope : wnd,
						fn: function(buttonId) 
						{
							if ( buttonId == 'yes' )
							{
								if (typeof wnd.onSave == 'function') {
									wnd.onSave(wnd.owner, action.result.DrugRequest_id);
								}
								wnd.hide();
							} else if ( buttonId == 'no' )
							{
								wnd.hide();
							}
						},
						icon: Ext.Msg.QUESTION,
						msg: 'Заявка с указанными параметрами уже существует. Открыть существующую заявку?',
						title: 'Вопрос'
					});
				} else {
					if (typeof wnd.onSave == 'function') {
						wnd.onSave(wnd.owner, action.result.DrugRequest_id);
					}
					wnd.hide();
				}
			}
		});
		}
	},
	show: function() {
        var wnd = this;
		sw.Promed.swMzDrugRequestAddWindow.superclass.show.apply(this, arguments);

		this.action = null;
		this.owner = null;
		this.onHide = Ext.emptyFn;
		this.onSave = Ext.emptyFn;
		this.FormParams = new Object();

        if (!arguments[0]) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].owner) {
			this.action = arguments[0].owner;
		}
		if (arguments[0].onSave && typeof arguments[0].onSave == 'function') {
			this.onSave = arguments[0].onSave;
		}
		if (arguments[0].onHide && typeof arguments[0].onHide == 'function') {
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].FormParams) {
			this.FormParams = arguments[0].FormParams;
		}

		this.setValues();
	},
	initComponent: function() {
		var wnd = this;

		this.lpu_combo = new sw.Promed.SwBaseRemoteCombo ({
			fieldLabel: langs('МО'),
			hiddenName: 'Lpu_id',
			displayField: 'Lpu_Name',
			valueField: 'Lpu_id',
			allowBlank: false,
			editable: true,
			anchor: '100%',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'{Lpu_Name}&nbsp;',
				'</div></tpl>'
			),
			store: new Ext.data.SimpleStore({
				autoLoad: false,
				fields: [
					{ name: 'Lpu_id', mapping: 'Lpu_id' },
					{ name: 'Lpu_Name', mapping: 'Lpu_Name' }
				],
				key: 'Lpu_id',
				sortInfo: { field: 'Lpu_Name' },
				url:'/?c=MzDrugRequest&m=loadLpuCombo'
			}),
			childrenList: ['LpuRegion_id', 'LpuSection_id', 'LpuUnit_id', 'MedPersonal_id'],
			listeners: {
				'change': function(combo, newValue) {
					combo.childrenList.forEach(function(field_name){
						var f_combo = wnd.form.findField(field_name);
						if (!f_combo.disabled) {
							f_combo.getStore().baseParams[combo.hiddenName] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
							f_combo.loadData();
						}
					});
				}
			},
			onTrigger2Click: function() {
				var combo = this;

				if (combo.disabled) {
					return false;
				}

				combo.clearValue();
				combo.lastQuery = '';
				combo.getStore().removeAll();
				combo.getStore().baseParams.query = '';
				combo.fireEvent('change', combo, null);
			},
			setValueById: function(id) {
				var combo = this;
				combo.store.baseParams.Lpu_id = id;
				combo.store.load({
					callback: function(){
						combo.setValue(id);
						combo.store.baseParams.Lpu_id = null;
					}
				});
			}
		});

		this.lpusection_combo = new sw.Promed.SwBaseLocalCombo ({
			fieldLabel: lang['otdelenie'],
			hiddenName: 'LpuSection_id',
			displayField: 'LpuSection_Name',
			valueField: 'LpuSection_id',
			allowBlank: true,
			editable: true,
			anchor: '100%',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'{LpuSection_Name}&nbsp;',
				'</div></tpl>'
			),
			store: new Ext.data.SimpleStore({
				autoLoad: false,
				fields: [
					{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
					{ name: 'LpuSection_Name', mapping: 'LpuSection_Name' }
				],
				key: 'LpuSection_id',
				sortInfo: { field: 'LpuSection_Name' },
				url:'/?c=MzDrugRequest&m=loadLpuSectionCombo'
			}),
			childrenList: ['LpuRegion_id', 'MedPersonal_id'],
			listeners: {
				'change': function(combo, newValue) {
					combo.childrenList.forEach(function(field_name){
						var f_combo = wnd.form.findField(field_name);
						if (!f_combo.disabled) {
							f_combo.getStore().baseParams[combo.hiddenName] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
							f_combo.loadData();
						}
					});
				}
			},
			setValueById: function(id) {
				var combo = this;
				combo.store.baseParams.LpuSection_id = id;
				combo.store.load({
					callback: function(){
						combo.setValue(id);
						combo.store.baseParams.LpuSection_id = null;
					}
				});
			},
			loadData: function() {
				var combo = this;
				combo.store.load({
					callback: function(){
						combo.setValue(null);
					}
				});
			}
		});

		this.lpuregion_combo = new sw.Promed.SwBaseLocalCombo ({
			fieldLabel: lang['uchastok'],
			hiddenName: 'LpuRegion_id',
			displayField: 'LpuRegion_Name',
			valueField: 'LpuRegion_id',
			allowBlank: true,
			editable: true,
			anchor: '100%',
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'{LpuRegion_Name}&nbsp;',
				'</div></tpl>'
			),
			store: new Ext.data.SimpleStore({
				autoLoad: false,
				fields: [
					{ name: 'LpuRegion_id', mapping: 'LpuRegion_id' },
					{ name: 'LpuRegion_Name', mapping: 'LpuRegion_Name' }
				],
				key: 'LpuRegion_id',
				sortInfo: { field: 'LpuRegion_Name' },
				url:'/?c=MzDrugRequest&m=loadLpuRegionCombo'
			}),
			setValueById: function(id) {
				var combo = this;
				combo.store.baseParams.LpuRegion_id = id;
				combo.store.load({
					callback: function(){
						combo.setValue(id);
						combo.store.baseParams.LpuRegion_id = null;
					}
				});
			},
			loadData: function() {
				var combo = this;
				combo.store.load({
					callback: function(){
						combo.setValue(null);
					}
				});
			}
		});

		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			//height: 270,
			autoHeight: true,
			border: false,
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'MzDrugRequestAddForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 160,
				collapsible: true,
				url:'/?c=MzDrugRequest&m=saveDrugRequest',
				items: [{
					fieldLabel: lang['rabochiy_period'],
					hiddenName: 'DrugRequestPeriod_id',
					xtype: 'swdynamicdrugrequestperiodcombo',
					allowBlank: false,
					anchor: '100%',
					setValueById: function(id) {
						var combo = this;
						combo.store.baseParams.DrugRequestPeriod_id = id;
						combo.store.load({
							callback: function(){
								combo.setValue(id);
								combo.store.baseParams.DrugRequestPeriod_id = null;
							}
						});
					}
				}, {
					name: 'PersonRegisterType_id',
					fieldLabel: lang['tip_registra_patsientov'],
					xtype: 'swcommonsprcombo',
					comboSubject: 'PersonRegisterType',
					anchor: '100%'
				}, {
					name: 'DrugGroup_id',
					fieldLabel: lang['gruppa_medikamentov'],
					xtype: 'swcommonsprcombo',
					comboSubject: 'DrugGroup',
					anchor: '100%'
				}, {
					hiddenName: 'DrugRequestKind_id',
					fieldLabel: lang['vid_zayavki'],
					xtype: 'swcommonsprcombo',
					comboSubject: 'DrugRequestKind',
					anchor: '100%'
				},
				this.lpu_combo,
				{
					name: 'LpuUnit_id',
					fieldLabel: lang['gruppa_otdeleniy'],
					xtype: 'swlpuunitcombo',
					anchor: '100%',
					childrenList: ['LpuSection_id', 'MedPersonal_id'],
					listeners: {
						select: function(combo) {
							combo.childrenList.forEach(function(field_name){
								var f_combo = wnd.form.findField(field_name);
								if (!f_combo.disabled) {
									f_combo.getStore().baseParams[combo.name] = !Ext.isEmpty(combo.getValue()) ? combo.getValue()  : null;
									f_combo.loadData();
								}
							});
						}
					},
					setValueById: function(id) {
						var combo = this;
						combo.store.baseParams.LpuUnit_id = id;
						combo.store.load({
							callback: function(){
								combo.setValue(id);
								combo.store.baseParams.LpuUnit_id = null;
							}
						});
					},
					loadData: function() {
						var combo = this;
						combo.store.load({
							callback: function(){
								combo.setValue(null);
							}
						});
					}
				},
				this.lpusection_combo,
				this.lpuregion_combo,
				{
					hisddenName: 'MedPersonal_id',
					fieldLabel: lang['vrach'],
					xtype: 'swmedpersonalcombo',
					anchor: '100%',
					allowBlank: false,
					setValueById: function(id) {
						var combo = this;
						combo.store.baseParams.MedPersonal_id = id;
						combo.store.load({
							callback: function(){
								combo.setValue(id);
								combo.store.baseParams.MedPersonal_id = null;
							}
						});
					},
					loadData: function() {
						var combo = this;
						combo.store.load({
							callback: function(){
								combo.setValue(null);
							}
						});
					}
				}]
			}]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swMzDrugRequestAddWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('MzDrugRequestAddForm').getForm();
	}	
});