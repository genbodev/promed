/**
* swMorbusGEBTPlanEditWindow - Планируемое лечение
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      PersonRegister
* @access       public
* @copyright	Copyright (c) 2019 Swan Ltd.
*/

sw.Promed.swMorbusGEBTPlanEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Планируемое лечение'),
	id: 'swMorbusGEBTPlanEditWindow',
	layout: 'form',
	maximizable: false,
	shim: false,
	width: 620,
	autoHeight: true,
	minHeight: 420,
	modal: true,
	show: function() {
		sw.Promed.swMorbusGEBTPlanEditWindow.superclass.show.apply(this, arguments);
		
		this.MorbusGEBT_id = arguments[0].MorbusGEBT_id || null;
		this.MorbusGEBTPlan_id = arguments[0].MorbusGEBTPlan_id || null;
		this.returnFunc = arguments[0].callback || Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		this.action = arguments[0].action || 'add';
		
		this.disableFields(this.action == 'view');
		
		var win = this;
		base_form = win.MainPanel.getForm();
		base_form.reset();
		
		var years = [];
		year = new Date().getFullYear();
		for (var i=0; i<3; i++) {
			years.push([year]);
			year++;
		}
		
		base_form.findField('MorbusGEBTPlan_Year').getStore().loadData(years);
		
		if (this.action == 'add') {
			win.setTitle('Планируемое лечение: Добавление');
			base_form.findField('MorbusGEBT_id').setValue(this.MorbusGEBT_id);
			base_form.findField('DrugComplexMNN_id').getStore().load({
				params: {MorbusGEBT_id: this.MorbusGEBT_id}
			});
		} else {
			win.setTitle('Планируемое лечение: ' + (this.action == 'view' ? 'Просмотр' : 'Редактирование'));
			var loadMask = new Ext.LoadMask(Ext.get('swMorbusGEBTPlanEditWindow'), { msg: "Загрузка..." });
			loadMask.show();
			base_form.load({
				params:{
					MorbusGEBTPlan_id: win.MorbusGEBTPlan_id
				},
				failure: function(f, o, a){
					loadMask.hide();
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							current_window.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'),
						title: langs('Ошибка')
					});
				},
				success: function(result, request) {
					loadMask.hide();
					base_form.findField('MorbusGEBTPlan_Year').fireEvent('change', base_form.findField('MorbusGEBTPlan_Year'), base_form.findField('MorbusGEBTPlan_Year').getValue());
					base_form.findField('DrugComplexMNN_id').getStore().load({
						params: {MorbusGEBT_id: win.MorbusGEBT_id},
						callback: function(){
							base_form.findField('DrugComplexMNN_id').setValue(base_form.findField('DrugComplexMNN_id').getValue());
						}
					});
				},
				url: '/?c=MorbusGEBT&m=loadMorbusGEBTPlan'
			});
		}
	},
	doSave: function() {
		var form = this.MainPanel;
		if (!form.getForm().isValid()){
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.ownerCt.submit();
	},
	submit: function() {
		
		var win = this;
		var base_form = this.MainPanel.getForm();
		var loadMask = new Ext.LoadMask(Ext.get('swMorbusGEBTPlanEditWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		
		var params = {};

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			}, 
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.MorbusGEBTPlan_id) {
						params.MorbusGEBTPlan_id = action.result.MorbusGEBTPlan_id;
						win.hide();
						win.returnFunc(win.owner, true, params);
					} else {
						Ext.Msg.alert(langs('Ошибка #100004'), langs('При сохранении произошла ошибка'));
					}
				} else {
					Ext.Msg.alert(langs('Ошибка #100005'), langs('При сохранении произошла ошибка'));
				}
			}
		});
	},
	disableFields: function(action) {
		base_form = this.MainPanel.getForm();
		base_form.findField('Lpu_id').setDisabled(action);
		base_form.findField('MedicalCareType_id').setDisabled(action);
		base_form.findField('MorbusGEBTPlan_Year').setDisabled(action);
		base_form.findField('MorbusGEBTPlan_Month').setDisabled(action);
		base_form.findField('DrugComplexMNN_id').setDisabled(action);
		base_form.findField('MorbusGEBTPlan_Treatment').setDisabled(action);
		this.buttons[0].setVisible(!action);
	},
	initComponent: function() {
		var win = this;
		
		this.MainPanel = new sw.Promed.FormPanel({
			frame: true,
			region: 'center',
			labelWidth: 160,
			items:
			[{
				name: 'MorbusGEBTPlan_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusGEBT_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: 'Год лечения',
				hiddenName: 'MorbusGEBTPlan_Year',
				store: new Ext.data.SimpleStore({
					fields: ['MorbusGEBTPlan_Year']
				}),
                displayField : 'MorbusGEBTPlan_Year',
                valueField : 'MorbusGEBTPlan_Year',
				width: 80,
				xtype: 'swbaselocalcombo',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('MorbusGEBTPlan_Year') == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(c, r, idx) {
						var base_form = win.MainPanel.getForm();
						base_form.findField('MorbusGEBTPlan_Month').lastQuery = '';
						base_form.findField('MorbusGEBTPlan_Month').getStore().clearFilter();
						if (new Date().getFullYear() == r.get('MorbusGEBTPlan_Year')) {
							base_form.findField('MorbusGEBTPlan_Month').getStore().filterBy(function(rec) {
								return (rec.get('value') > new Date().getMonth());
							});
						}
					}
				}
			}, {
				allowBlank: false,
				fieldLabel: 'Месяц лечения',
				hiddenName: 'MorbusGEBTPlan_Month',
				store: [
					[1, langs('Январь')],
					[2, langs('Февраль')],
					[3, langs('Март')],
					[4, langs('Апрель')],
					[5, langs('Май')],
					[6, langs('Июнь')],
					[7, langs('Июль')],
					[8, langs('Август')],
					[9, langs('Сентябрь')],
					[10, langs('Октябрь')],
					[11, langs('Ноябрь')],
					[12, langs('Декабрь')]
				],
				width: 150,
				xtype: 'swbaselocalcombo',
			}, {
				allowBlank: false,
				fieldLabel: 'Условия оказания МП',
				hiddenName: 'MedicalCareType_id',
				width: 400,
				prefix: 'fed_',
				comboSubject: 'MedicalCareType',
				xtype: 'swcommonsprcombo',
			}, {
				allowBlank: false,
				fieldLabel: 'МО планируемого лечения',
				hiddenName: 'Lpu_id',
				width: 400,
				xtype: 'swlpucombo',
			}, {
				allowBlank: false,
				fieldLabel: 'Препарат',
				hiddenName: 'DrugComplexMNN_id',
				width: 400,
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'DrugComplexMNN_id', type: 'int' },
						{ name: 'Drug_Name', type: 'string' },
					],
					key: 'DrugComplexMNN_id',
					sortInfo: {
						field: 'Drug_Name'
					},
					url: '/?c=MorbusGEBT&m=getDrugList'
				}),
                displayField : 'Drug_Name',
                valueField : 'DrugComplexMNN_id',
				xtype: 'swbaselocalcombo',
			}, {
				allowBlank: false,
				fieldLabel: 'Лечение проведено',
				hiddenName: 'MorbusGEBTPlan_Treatment',
				width: 80,
				value: 1,
				xtype: 'swyesnocombo',
			}],
			reader: new Ext.data.JsonReader({
				success: function() {}
			},[
				{ name: 'MorbusGEBTPlan_id' },
				{ name: 'MorbusGEBT_id' },
				{ name: 'Lpu_id' },
				{ name: 'MorbusGEBTPlan_Year' },
				{ name: 'MorbusGEBTPlan_Month' },
				{ name: 'MedicalCareType_id' },
				{ name: 'DrugComplexMNN_id' },
				{ name: 'MorbusGEBTPlan_Treatment' },
			]),
			url: '/?c=MorbusGEBT&m=saveMorbusGEBTPlan'
		});
		
		Ext.apply(this, 
		{
			items: [
				this.MainPanel
			]
		});
		sw.Promed.swMorbusGEBTPlanEditWindow.superclass.initComponent.apply(this, arguments);
	},
	buttons: [{
		text: BTN_FRMSAVE,
		id: 'lbOk',
		iconCls: 'save16',
		handler: function() {
			this.ownerCt.doSave();
		}
	},{
		text:'-'
	},{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) {
			ShowHelp(this.ownerCt.title);
		}
	},{
		text: BTN_FRMCANCEL,
		id: 'lbCancel',
		iconCls: 'cancel16',
		handler: function() {
			this.ownerCt.hide();
		}
	}]
});