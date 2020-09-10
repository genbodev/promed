/**
 * Схема лекарственной терапии
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 *
 */
Ext6.define('common.EMK.DrugTherapySchemeAddWindow', {
	alias: 'widget.DrugTherapySchemeAddWindow',
	title: 'Схема лекарственной терапии: Добавление',
	extend: 'base.BaseForm',
	layout: 'anchor',
	
	closeToolText: 'Закрыть',
	maximized: false,
	width: 760,
	modal: true,
	cls: 'arm-window-new emk-forms-window',
	renderTo: Ext6.getBody(),
	
	onSprLoad: function(args) {
		var win = this;
		var arguments = args;
		var base_form = win.FormPanel.getForm();
		
		base_form.reset();
		
		win.callback = args[0].callback || Ext6.emptyFn;
		
		base_form.findField('EvnVizitPL_id').setValue(args[0].EvnVizitPL_id);
		base_form.findField('DrugTherapyScheme_id').getStore().clearFilter();
		if (args[0].FilterIds) {
			base_form.findField('DrugTherapyScheme_id').getStore().filterBy(function(rec){
				return rec.get('DrugTherapyScheme_id').inlist(args[0].FilterIds);
			});
		}
	},

	show: function() {
		this.callParent(arguments);
	},
	
	doSave: function() {
		var win = this;
		var form = win.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}

		win.mask(LOAD_WAIT_SAVE);
		
		base_form.submit({
			success: function(form, action) {
				win.unmask();

				if ( !Ext6.isEmpty(action.result.Error_Msg) ) {
					sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					return false;
				}
		
				var data = {
					EvnVizitPLDrugTherapyLink_id: action.result.EvnVizitPLDrugTherapyLink_id,
					DrugTherapyScheme_id: base_form.findField('DrugTherapyScheme_id').getValue(),
					DrugTherapyScheme_Code: base_form.findField('DrugTherapyScheme_id').getFieldValue('DrugTherapyScheme_Code'),
					DrugTherapyScheme_Name: base_form.findField('DrugTherapyScheme_id').getFieldValue('DrugTherapyScheme_Name'),
				}; 
				
				win.callback(data);
				win.hide();
			},
			failure: function(form, action) {
				win.unmask();
			}
		});
	},

	initComponent: function() { 
		var win = this;
		win.FormPanel = new Ext6.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 20px 5px 30px 30px',
			border: false,
			defaults: {
				labelWidth: 200,
				width: 670
			},
			region: 'center',
			items: [{
				xtype: 'hidden',
				name: 'EvnVizitPL_id'
			},{
				xtype: 'commonSprCombo',
				comboSubject: 'DrugTherapyScheme',
				displayCode: true,
				allowBlank: false,
				fieldLabel: 'Схема лекарственной терапии',
				name: 'DrugTherapyScheme_id', 
			}],
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {})
			}),
			url: '/?c=EMK&m=saveDrugTherapyScheme'
		});
		
		Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			border: false,
			buttons:
			[ '->',
			{
				userCls: 'buttonCancel',
				text: langs('Отмена'),
				itemId: 'cancelBtn',
				handler: function() {
					win.hide();
				}
			}, {
				cls: 'buttonAccept',
				text: langs('Сохранить'),
				itemId: 'saveBtn',
				handler: function() {
					win.doSave();
				}
			}]
		});

		win.callParent(arguments);
	}
});