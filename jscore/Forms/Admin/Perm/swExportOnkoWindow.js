/**
* swExportOnkoWindow - окно выгрузки онко случаев
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Dmitry Vlasenko
* @comment
*
*/

sw.Promed.swExportOnkoWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	title: 'Выгрузка ОНКО случаев',
	width: 500,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		this.Panel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 150,
			items: [{
				xtype: 'swlpucombo',
				hiddenName: 'Lpu_oid',
				fieldLabel: 'МО',
				anchor: '100%',
				listWidth: 400
			}, {
				allowBlank: false,
				fieldLabel: 'Начало периода',
				name: 'Registry_begDate',
				value: '01.01.2019',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW + 2,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Окончание периода',
				name: 'Registry_endDate',
				value: '31.01.2019',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UREW + 3,
				width: 100,
				xtype: 'swdatefield'
			}]
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				handler: function()
				{
					this.ownerCt.createXML();
				},
				iconCls: 'refresh16',
				text: 'Сформировать'
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swExportOnkoWindow.superclass.initComponent.apply(this, arguments);
	},
	createXML: function()
	{
		var form = this;
		
		var params = {};

		var base_form = form.Panel.getForm();
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.Panel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		form.getLoadMask(LOAD_WAIT).show();
		base_form.submit({
			url: '/?c=Registry&m=exportOnko',
			params: params,
			timeout: 1800000,
			failurr: function() {
				form.getLoadMask().hide();
			},
			success: function(result_form, action) {
				form.getLoadMask().hide();

				if (action.result && action.result.link) {
					sw.swMsg.alert('Сообщение', '<a target="_blank" href="' + action.result.link + '">Скачать и сохранить реестр ОНКО случаев</a>');
				}
			}
		});
	},
	show: function() 
	{
		sw.Promed.swExportOnkoWindow.superclass.show.apply(this, arguments);
		var form = this;
		form.Panel.getForm().reset();
		form.onHide = Ext.emptyFn;
		form.buttons[0].enable();

		if (arguments[0].onHide)
		{
			form.onHide = arguments[0].onHide;
		}

		var base_form = form.Panel.getForm();
		if (!isSuperAdmin() && getGlobalOptions().lpu_id != 10011168) {
			base_form.findField('Lpu_oid').setValue(getGlobalOptions().lpu_id);
			base_form.findField('Lpu_oid').disable();
		} else {
			base_form.findField('Lpu_oid').clearValue();
			base_form.findField('Lpu_oid').enable();
		}
		
		this.syncShadow();
	}
});