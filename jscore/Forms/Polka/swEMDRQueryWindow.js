
sw.Promed.swEMDRQueryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: "Выполнение запроса к РЭМД ЕГИСЗ",
	width: 600,
	autoHeight: true,
	modal: true,
	doQuery: function() {
		var me = this;
		if(Ext.isEmpty(me.Person_Snils)) {
			Ext.Msg.alert('Сообщение','У пациента не указан СНИЛС. Подача запроса невозможна');
		} else {
			var base_form = me.FormPanel.getForm();
			me.getLoadMask('Выполнение запроса...').show();
			Ext.Ajax.request({
				url: '/?c=EMD&m=searchRegistryItem',
				params: {
					Person_Snils: me.Person_Snils,
					EMDDocumentType_id: base_form.findField('DocumentType_id').getValue()
				},
				callback: function(options, success, response) {
					me.getLoadMask().hide();

					if (response && response.responseText) {
						var responseObj = Ext.util.JSON.decode(response.responseText);
						if (responseObj.success) {
							sw.swMsg.alert('Внимание', 'Запрос на список ЭМД пациента передан в РЭМД ЕГИСЗ. ЭМД пациента, созданные в других МИС, отобразятся в списке после получения ответа.');
						}
					}
				}
			});
		}
	},
	show: function() {
		var me = this,
			base_form = me.FormPanel.getForm();
		sw.Promed.swEMDRQueryWindow.superclass.show.apply(this, arguments);
		me.Person_id = null;
		me.PersonFullName = '';
		me.Person_Snils = '';
		
		if(arguments && arguments[0]) {
			if(arguments[0]['Person_id']) me.Person_id = arguments[0]['Person_id'];
			if(arguments[0]['PersonFullName']) me.PersonFullName +=arguments[0]['PersonFullName'];
			
			if(arguments[0]['Person_Snils']) {
				me.Person_Snils = arguments[0]['Person_Snils'];
				me.PersonFullName += ', '+me.Person_Snils;
			}
			base_form.findField('Person_id').setValue(me.PersonFullName);
		}
		base_form.findField('DocumentType_id').reset();
	},
	initComponent: function()
	{
		var me = this;

		me.FormPanel = new Ext.form.FormPanel({
			layout: 'form',
			region: 'center',
			labelAlign: 'right',
			labelWidth: 120,
			bodyStyle: 'padding: 10px;',
			items: [
				{
					xtype: 'textfield',
					name: 'Person_id',
					fieldLabel: 'Пациент',
					width: 400,
					readOnly: true
				}, {
					xtype: 'swemddocumenttype',
					fieldLabel: 'Вид документа',
					name: 'DocumentType_id',
					width: 400
				}
			]
		});
		Ext.apply(this, {
			xtype: 'panel',
			items: [
				me.FormPanel
			],
			buttons: [{
				text: 'Выполнить запрос',
				handler: function() {
					me.doQuery();
				}
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				iconCls: 'close16',
				handler: function() {
					me.hide();
				},
				text: langs('Отмена')
			}]
		});

		sw.Promed.swEMDOuterRegistryWindow.superclass.initComponent.apply(this, arguments);
	}
});