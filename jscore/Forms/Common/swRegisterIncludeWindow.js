/**
* swRegisterIncludeWindow - Включение в регистр
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @author       Magafurov SM
* @version      05.2019
*/
sw.Promed.swRegisterIncludeWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Добавление в регистр',
	autoHeight: true,
	modal: true,
	callback: Ext.emptyFn,
	saveForm: function() {
		var win = this,
			baseForm = win.MainForm.getForm(),
			RegisterIdField = baseForm.findField('Register_id');
		win.showLoadMask('Сохранение..');
		baseForm.submit({
			clientValidation: true,
			success: function(form, action) {
				win.hideLoadMask();
				var result = action.result;
				if(result && result.Register_id) {
					RegisterIdField.setValue(result.Register_id);
					win.callback( baseForm.getValues() );
					win.hide();
				}
			},
			failure: function(form,action) {
				win.hideLoadMask();
				switch (action.failureType) {
					case Ext.form.Action.CLIENT_INVALID:
						Ext.Msg.alert("Ошибка", "Заполните обязательные поля");
						break;
					default:
						Ext.Msg.alert("Ошибка", "Ошибка при сохранении");
				}
			}
		});
	},
	
	onHide: function() {
		var baseForm = this.MainPanel.getForm();
		
	},

	show: function() {
		sw.Promed.swRegisterIncludeWindow.superclass.show.apply(this, arguments);

		var params = arguments[0] || {},
			baseForm = this.MainForm.getForm();

			
		if(params.callback) {
			this.callback = params.callback;
		}

		if(!params.Register_setDate) {
			params.Register_setDate = new Date();
		}

		this.InformationPanel.load({
			Person_id: params.Person_id
		});

		baseForm.setValues(params);
	},
	initComponent: function() {
		var win = this;

		win.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});

		win.MainForm = new sw.Promed.FormPanel({
			frame: true,
			labelWidth: 200,
			region: 'center',
			bodyStyle: 'padding: 5px',
			labelAlign: 'right',
			url: '/?c=Register&m=add',
			items: [
				{
					xtype: 'swdatefield',
					name: 'Register_setDate',
					fieldLabel: 'Дата включения в регистр',
					maxValue: new Date(),
					allowBlank: false
				},
				{
					xtype: 'hidden',
					name: 'Person_id'
				},
				{
					xtype: 'hidden',
					name: 'RegisterType_Code'
				},
				{
					xtype: 'hidden',
					name: 'Register_id'
				}
			]
		});
		win.buttons = [
			{
				text      : BTN_FRMSAVE,
				tabIndex  : -1,
				tooltip   : 'Сохранить',
				iconCls   : 'save16',
				type      : 'submit',
				handler  : function() {
					win.saveForm();
				}
			}, '-', {
				text      : 'Закрыть',
				tabIndex  : -1,
				tooltip   : 'Закрыть',
				iconCls   : 'cancel16',
				handler   : function() {
					win.hide();
				}
			}, {
				text	: BTN_FRMHELP,
				tabIndex  : -1,
				tooltip   : BTN_FRMHELP_TIP,
				iconCls   : 'help16',
				handler   : function() {
					ShowHelp(win.title);
				}
			}
		],
		Ext.apply(win, {
			items: [
				win.InformationPanel,
				win.MainForm 
			]
		});
		sw.Promed.swRegisterIncludeWindow.superclass.initComponent.apply(this, arguments);
	}
});