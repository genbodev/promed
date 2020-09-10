/**
 * swRegistryDataEditWindow - окно редактирования данных о случае в реестре.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.08.2014
 */

sw.Promed.swRegistryDataEditWindow = Ext.extend(sw.Promed.BaseForm,
	{
		action: null,
		autoHeight: true,
		buttonAlign: 'left',
		closable: true,
		closeAction: 'hide',
		draggable: true,
		split: true,
		width: 600,
		layout: 'form',
		id: 'swRegistryDataEditWindow',
		listeners:
		{
			hide: function()
			{
				this.onHide();
			}
		},
		modal: true,
		onHide: Ext.emptyFn,
		plain: true,
		resizable: false,
		doSave: function()
		{
			var base_form = this.FormPanel.getForm();
			if ( !base_form.isValid() )
			{
				sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function()
						{
							form.getFirstInvalidEl().focus(true);
						},
						icon: Ext.Msg.WARNING,
						msg: ERR_INVFIELDS_MSG,
						title: ERR_INVFIELDS_TIT
					});
				return false;
			}

			var data = new Object();
			data.RegistryData = getAllFormFieldValues(this.FormPanel);
			data.RegistryData.RegistryErrorClass_id = base_form.findField('RegistryErrorType_id').getFieldValue('RegistryErrorClass_id');
			this.callback(data);

			this.hide();

			return true;
		},

		show: function()
		{
			sw.Promed.swRegistryDataEditWindow.superclass.show.apply(this, arguments);

			var base_form = this.FormPanel.getForm();

			if (!arguments[0])
			{
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: 'Ошибка открытия формы.<br/>Не указаны нужные входные параметры.',
					title: 'Ошибка',
					fn: function() {
						this.hide();
					}
				});
			}

			this.callback = (typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext.emptyFn);
			this.Registry_id = (!Ext.isEmpty(arguments[0].Registry_id) ? arguments[0].Registry_id : null);
			this.RegistryType_id = (!Ext.isEmpty(arguments[0].RegistryType_id) ? arguments[0].RegistryType_id : null);

			base_form.findField('RegistryErrorType_id').getStore().load();

			base_form.reset();
			base_form.setValues(arguments[0].formParams);

			this.setTitle('Запись в реестре: Редактирование');
		},
		initComponent: function()
		{
			this.FormPanel = new Ext.form.FormPanel(
			{
				autoHeight: true,
				bodyStyle: 'padding: 5px',
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'SEW_StorageForm',
				labelAlign: 'right',
				labelWidth: 120,
				items:
					[{
						xtype: 'hidden',
						name: 'Registry_id'
					}, {
						xtype: 'hidden',
						name: 'Evn_id'
					}, {
						name: 'RecordStatus_Code',
						value: 0,
						xtype: 'hidden'
					}, {
						name: 'RegistryErrorType_Code',
						value: 0,
						xtype: 'hidden'
					}, /*{
						allowBlank: false,
						xtype: 'numberfield',
						name: 'RegistryData_EvnNum',
						fieldLabel: '№ п/п в реестре',
						width: 240
					}, {
						allowBlank: false,
						xtype: 'textfield',
						name: 'Registry_xmlExportFile',
						fieldLabel: 'Имя файла',
						width: 240
					},*/ {
						allowBlank: false,
						xtype: 'swregistryerrortypecombo',
						hiddenName: 'RegistryErrorType_id',
						fieldLabel: 'Код ошибки',
						listeners: {
							'select': function(combo, record) {
								var base_form = this.FormPanel.getForm();

								base_form.findField('RegistryErrorType_Code').setValue(record.get('RegistryErrorType_Code'));
							}.createDelegate(this)
						},
						anchor: '95%'
					}],
				url: '/?c=Registry&m=saveRegistryData'
			});
			Ext.apply(this,
			{
				buttons:
					[{
						handler: function()
						{
							this.doSave();
						}.createDelegate(this),
						iconCls: 'save16',
						text: BTN_FRMSAVE
					},
						{
							text: '-'
						},
						HelpButton(this),
						{
							handler: function()
							{
								this.hide();
							}.createDelegate(this),
							iconCls: 'cancel16',
							tabIndex: TABINDEX_LPEEW + 17,
							text: BTN_FRMCANCEL
						}],
				items: [this.FormPanel]
			});
			sw.Promed.swRegistryDataEditWindow.superclass.initComponent.apply(this, arguments);
		}
	});