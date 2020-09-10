/**
 * swMPExportToXML - окно экспорта реестра медработников в XML.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 - 2015 Swan Ltd.
 * @author		 Shorev
 * @version      05.03.2015
 */

sw.Promed.swMPExportToXML = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'MPExportToXML',
	title: lang['reestr_meditsinskih_rabotnikov_oms'],
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() {
		var win = this;

		win.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'MPXmlTextPanel',
			html: lang['vyigruzka_reestra_med_rabotnikov_v_formate_xml']
		});

		win.Panel = new Ext.form.FormPanel(
			{
				autoHeight: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: true,
				id: 'MPXmlPanel',
				labelAlign: 'right',
				items: [{
					border: false,
					labelWidth: 25,
					allowBlank: false,
					layout: 'form',
					items: [/*{
						allowBlank: false,
						fieldLabel: lang['data'],
						name: 'MPExportDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						width: 120,
						xtype: 'swdatefield'
					}*/]
				},
					win.TextPanel
				]
			});

		Ext.apply(this,
			{
				autoHeight: true,
				buttons: [
					{
						id: 'rxfOk',
						handler: function()
						{
							win.createXML();
						},
						iconCls: 'refresh16',
						text: lang['sformirovat']
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
						onTabElement: 'rxfOk',
						text: BTN_FRMCANCEL
					}],
				items: [
					win.Panel
				]
			});

		sw.Promed.swMPExportToXML.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function() {
			if ( this.refresh ) {
				this.onHide();
			}
		}
	},
	createXML: function(addParams)
	{
		var form = this;
		//var MPExportDate = this.Panel.getForm().findField('MPExportDate').getValue();
		//alert(MPExportDate);
		form.getLoadMask().show();

		//var params = {
		//	MPExportDate: MPExportDate
		//};


		if ( !Ext.isEmpty(addParams) ) {
			for ( var par in addParams) {
				params[par] = addParams[par];
			}
		}
		else {
			addParams = [];
		}

		Ext.Ajax.request({
			url: '/?c=MedPersonal&m=exportMedPersonalToXml',
			//params: params,
			timeout: 1800000,
			callback: function(options, success, response)
			{
				form.getLoadMask().hide();
				if (success)
				{
					if (!response.responseText) {
						var newParams = addParams;
						newParams.onlyLink = 1;
						form.createXML(newParams);
						return false;
					}
					var result = Ext.util.JSON.decode(response.responseText);

					var alt = '';
					var msg = '';
					form.refresh = true;
					if (result.Link) {
						form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить список</a>'+msg;
						form.syncShadow();
						Ext.getCmp('rxfOk').disable();
					}
					if (result.success === false) {
						form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
						form.syncShadow();
						Ext.getCmp('rxfOk').disable();
					}
					form.TextPanel.render();
				}
				else
				{
					var result = Ext.util.JSON.decode(response.responseText);
					form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
					form.TextPanel.render();
				}
			}
		});
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
		}
		return this.loadMask;
	},
	show: function()
	{
		sw.Promed.swMPExportToXML.superclass.show.apply(this, arguments);

		var
			base_form = this.Panel.getForm(),
			form = this;

		base_form.reset();


		form.onHide = Ext.emptyFn;
		form.buttons[0].enable();
		form.refresh = false;
		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_reestra_med_rabotnikov_xml'];
		form.TextPanel.render();

		this.syncShadow();
	}
});