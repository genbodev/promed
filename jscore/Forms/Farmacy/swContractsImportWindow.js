/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 16.03.17
 */

/*NO PARSE JSON*/

sw.Promed.swContractsImportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swContractsImportWindow',
	width: 600,
	height: 170,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: true,
	title: 'Импорт контрактов и дополнительных соглашений',

	doImport: function(type) {
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		//Сначала узнаем, что ввели в поле "Номер контракта" - выбрали из базы или ввели руками
		var Doc_combo =  base_form.findField('WhsDocumentUc_Num');
		var WhsDocumentUc_Num = Doc_combo.getValue();
		var suppl_agreement = base_form.findField('suppl_agreement').getValue();
		var doc_index = Doc_combo.getStore().findBy(function(rec) {
			return (rec.get('WhsDocumentUc_Num') == WhsDocumentUc_Num)
		});

		if(type == 1) { //Импорт данных контракта
			if(doc_index > -1) //Если выборали уже существующий
			{
				//Выдаем сообщение о невозможности импорта контракта (он уже есть в базе).
				Ext.Msg.alert('Невозможно выполнить импорт', 'Контракт с таким номером уже существует');
			}
			else
			{
				var params = {
					WhsDocumentUc_Num: WhsDocumentUc_Num,
					suppl_agreement: suppl_agreement,
					import_Type: 1
				};
				Ext.Ajax.request({
					url: '/?c=ServiceEFIS&m=importWhsDocumentUc',
					params: params,
					callback: function(options, success, response) {
						if (!success) {
							Ext.Msg.alert(lang['oshibka'], 'Ошибка при запуске импорта из сервиса ЕФИС');
						} else {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if(!response_obj.success)
								Ext.Msg.alert(lang['oshibka'], 'Ошибка при запуске импорта из сервиса ЕФИС');
						}
						wnd.callback(wnd.owner, null, null);
						wnd.hide();
					}.createDelegate(this)
				});
			}
		}
		if(type == 2) { //Импорт данных доп соглашения
			if(doc_index == -1) //Если такого контракта нет
			{
				//Выдаем сообщение о невозможности иморта доп соглашения (контракта нет в базе)
				Ext.Msg.alert('Невозможно выполнить импорт', 'Контракта с таким номером не существует. Необходимо предварительно загрузить контракт.');
			}
			else
			{
				var params = {
					WhsDocumentUc_Num: WhsDocumentUc_Num,
					suppl_agreement: suppl_agreement,
					import_Type: 2
				};
				Ext.Ajax.request({
					url: '/?c=ServiceEFIS&m=importWhsDocumentUc',
					params: params,
					callback: function(options, success, response) {
						if (!success) {
							Ext.Msg.alert(lang['oshibka'], 'Ошибка при запуске импорта из сервиса ЕФИС');
						} else {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if(!response_obj.success)
								Ext.Msg.alert(lang['oshibka'], 'Ошибка при запуске импорта из сервиса ЕФИС');
						}
						wnd.callback(wnd.owner, null, null);
						wnd.hide();
					}.createDelegate(this)
				});
			}
		}
	},

	show: function()
	{
		sw.Promed.swContractsImportWindow.superclass.show.apply(this, arguments);
		this.callback = Ext.emptyFn;
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		base_form.reset();
		base_form.clearInvalid();
		base_form.findField('supplier').setValue('ТОО СК "Фармация"');
		
		base_form.findField('WhsDocumentUc_Num').getStore().load({
			params: {
				Org_id: getGlobalOptions().org_id
			}
		});

		wnd.TextPanel.getEl().dom.innerHTML = '';
		wnd.TextPanel.render();
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			html: ''
		});

		wnd.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px;',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 150,
			region: 'center',
			items: [
				{
					allowBlank: false,
					fieldLabel: 'Поставщик',
					disabled: true,
					name: 'supplier',
					width: 400,
					xtype: 'textfield'
				},
				{
					fieldLabel: '№ контракта',
					name: 'WhsDocumentUc_Num',
					width: 400,
					xtype: 'swwhsdocumentsupplynumcombo',
					listeners: {
						'change': function(cmp, value) {
							var base_form = wnd.FormPanel.getForm();
							if(value != '' && base_form.findField('suppl_agreement').getValue() == '')
							{
								wnd.buttons[0].enable();
								wnd.buttons[1].disable();
							}
							if(value == base_form.findField('suppl_agreement').getValue())
							{
								wnd.buttons[0].disable();
								wnd.buttons[1].disable();	
							}
							if(value != '' && base_form.findField('suppl_agreement').getValue() != '' && value != base_form.findField('suppl_agreement').getValue())
							{
								wnd.buttons[0].disable();
								wnd.buttons[1].enable();		
							}
						}
					}
				},
				{
					allowBlank: true,
					fieldLabel: '№ доп.соглашения',
					disabled: false,
					name: 'suppl_agreement',
					width: 400,
					xtype: 'textfield',
					listeners: {
						'change' :function (cmp, value) {
							var base_form = wnd.FormPanel.getForm();
								if(value != '' && base_form.findField('WhsDocumentUc_Num').getValue() == '')
								{
									wnd.buttons[0].disable();
									wnd.buttons[1].disable();
								}
								if(value == base_form.findField('WhsDocumentUc_Num').getValue())
								{
									wnd.buttons[0].disable();
									wnd.buttons[1].disable();	
								}
								if(value != '' && base_form.findField('WhsDocumentUc_Num').getValue() != '' && value != base_form.findField('WhsDocumentUc_Num').getValue())
								{
									wnd.buttons[0].disable();
									wnd.buttons[1].enable();		
								}
								if(value == '' && base_form.findField('WhsDocumentUc_Num').getValue() != '')
								{
									wnd.buttons[0].enable();
									wnd.buttons[1].disable();
								}
						}
					}
				},
				wnd.TextPanel
			]
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel
			],
			buttons: [
				{
					handler: function () {
						wnd.doImport(1);
					}.createDelegate(this),
					iconCls: 'refresh16',
					disabled: true,
					text: 'Получить даные контракта'
				},
				{
					handler: function () {
						wnd.doImport(2);
					}.createDelegate(this),
					iconCls: 'refresh16',
					disabled: true,
					text: 'Получить даные соглашения'
				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swContractsImportWindow.superclass.initComponent.apply(this, arguments);
	}
});
