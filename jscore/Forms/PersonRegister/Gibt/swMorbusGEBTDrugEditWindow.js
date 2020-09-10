/**
* swMorbusGEBTDrugEditWindow - Курс препарата
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      PersonRegister
* @access       public
* @copyright	Copyright (c) 2019 Swan Ltd.
*/

sw.Promed.swMorbusGEBTDrugEditWindow = Ext.extend(sw.Promed.BaseForm, {
	title: langs('Курс препарата'),
	id: 'swMorbusGEBTDrugEditWindow',
	layout: 'form',
	maximizable: false,
	shim: false,
	width: 580,
	autoHeight: true,
	minHeight: 420,
	modal: true,
	show: function() {
		sw.Promed.swMorbusGEBTDrugEditWindow.superclass.show.apply(this, arguments);
		
		this.MorbusGEBT_id = arguments[0].MorbusGEBT_id || null;
		this.MorbusGEBTDrug_id = arguments[0].MorbusGEBTDrug_id || null;
		this.returnFunc = arguments[0].callback || Ext.emptyFn;
		this.owner = arguments[0].owner || null;
		this.action = arguments[0].action || 'add';
		
		this.disableFields(this.action == 'view');
		
		var win = this;
		base_form = win.MainPanel.getForm();
		base_form.reset();	
		
		if (this.action == 'add') {
			win.setTitle('Курс препарата: Добавление');
			base_form.findField('MorbusGEBT_id').setValue(this.MorbusGEBT_id);
		} else {
			win.setTitle('Курс препарата: ' + (this.action == 'view' ? 'Просмотр' : 'Редактирование'));
			var loadMask = new Ext.LoadMask(Ext.get('swMorbusGEBTDrugEditWindow'), { msg: "Загрузка..." });
			loadMask.show();
			base_form.load({
				params:{
					MorbusGEBTDrug_id: win.MorbusGEBTDrug_id
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
					base_form.findField('DrugComplexMNN_id').setValueById(base_form.findField('DrugComplexMNN_id').getValue());
				},
				url: '/?c=MorbusGEBT&m=loadMorbusGEBTDrug'
			});
		}
	},
	doSave: function() {
		var form = this.MainPanel;
		if (!form.getForm().isValid()) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
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
		var loadMask = new Ext.LoadMask(Ext.get('swMorbusGEBTDrugEditWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		
		var params = {
			MorbusGEBTDrug_BoxYear: base_form.findField('MorbusGEBTDrug_BoxYear').getValue()
		};

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
			}, 
			success: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.MorbusGEBTDrug_id) {
						params.MorbusGEBTDrug_id = action.result.MorbusGEBTDrug_id;
						win.hide();
						win.returnFunc(win.owner, true, params);
					} else {
						Ext.Msg.alert(langs('При сохранении произошла ошибка'));
					}
				} else {
					Ext.Msg.alert(langs('При сохранении произошла ошибка'));
				}
			}
		});
	},
	disableFields: function(action) {
		var base_form = this.MainPanel.getForm();
		base_form.findField('DrugComplexMNN_id').setDisabled(action);
		base_form.findField('MorbusGEBTDrug_OneInject').setDisabled(action);
		base_form.findField('MorbusGEBTDrug_InjectCount').setDisabled(action);
		base_form.findField('MorbusGEBTDrug_InjectQuote').setDisabled(action);
		base_form.findField('MorbusGEBTDrug_QuoteYear').setDisabled(action);
		this.buttons[0].setVisible(!action);
	},
	recalcBoxYear: function() {
		var base_form = this.MainPanel.getForm();
		base_form.findField('MorbusGEBTDrug_BoxYear').setValue(parseInt(base_form.findField('MorbusGEBTDrug_OneInject').getValue()) * parseInt(base_form.findField('MorbusGEBTDrug_InjectCount').getValue()));
	},
	initComponent: function() {		
		var win = this;
		
		this.MainPanel = new sw.Promed.FormPanel({
			frame: true,
			region: 'center',
			labelWidth: 180,
			items: [{
				name: 'MorbusGEBTDrug_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusGEBT_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				xtype: 'swdrugcomplexmnncombo',
				hiddenName: 'DrugComplexMNN_id',
				fieldLabel: 'МНН',
				anchor: '100%',
				setValueById: function(DrugComplexMnn_id) {
					var combo = this;
					combo.store.load({
						params: {DrugComplexMnn_id: DrugComplexMnn_id},
						callback: function(){
							combo.setValue(DrugComplexMnn_id);
						}
					});
				}
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				hideTrigger: true,
				fieldLabel: 'На одно введение',
				name: 'MorbusGEBTDrug_OneInject',
				width: 90,
				xtype: 'numberfield',
				listeners: {
					'change': function() {win.recalcBoxYear()}
				}
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				hideTrigger: true,
				fieldLabel: 'Количество введений',
				name: 'MorbusGEBTDrug_InjectCount',
				width: 90,
				xtype: 'numberfield',
				listeners: {
					'change': function() {win.recalcBoxYear()}
				}
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				hideTrigger: true,
				fieldLabel: 'Количество введений на квоту',
				name: 'MorbusGEBTDrug_InjectQuote',
				width: 90,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				hideTrigger: true,
				fieldLabel: 'Количество квот в год',
				name: 'MorbusGEBTDrug_QuoteYear',
				width: 90,
				xtype: 'numberfield'
			}, {
				hideTrigger: true,
				disabled: true,
				fieldLabel: 'Упаковок в год',
				name: 'MorbusGEBTDrug_BoxYear',
				width: 90,
				xtype: 'numberfield'
			}],
			reader: new Ext.data.JsonReader({
				success: function() {}
			}, [
				{ name: 'MorbusGEBTDrug_id' },
				{ name: 'MorbusGEBT_id' },
				{ name: 'DrugComplexMNN_id' },
				{ name: 'MorbusGEBTDrug_OneInject' },
				{ name: 'MorbusGEBTDrug_InjectCount' },
				{ name: 'MorbusGEBTDrug_InjectQuote' },
				{ name: 'MorbusGEBTDrug_QuoteYear' },
				{ name: 'MorbusGEBTDrug_BoxYear' },
			]),
			url: '/?c=MorbusGEBT&m=saveMorbusGEBTDrug'
		});
		
		Ext.apply(this, {
			items: [
				this.MainPanel
			]
		});
		sw.Promed.swMorbusGEBTDrugEditWindow.superclass.initComponent.apply(this, arguments);
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