/**
* swMedCert2XMLWindow - окно выгрузки информации о наличии сертификатов у мед. работников в XML
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2015 Swan Ltd.
* @author		Abakhri Samir
* @version      01.07.2015
* @comment      Префикс для id компонентов MCX
*
* @input data: arm - из какого АРМа ведётся выгрузка
*/

sw.Promed.swMedCert2XMLWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'MedCert2XMLWindow',
	title: lang['vyigruzka_sertifikatov_med_rabotnikov_v_xml'],
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
			id: 'MCX_RegistryXmlTextPanel',
			html: lang['vyigruzka_dannyih_o_sertifikatah_med_rabotnikov_v_xml']
		});

		win.Panel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'RegistryXmlPanel',
			labelAlign: 'right',
			labelWidth: 60,
			items: [{
				allowBlank: false,
				fieldLabel: lang['mo'],
				hiddenName: 'Lpu_id',
				width: 300,
				xtype: 'swlpucombo'
			},
			{
			    name : "Date_range",
			    xtype : "daterangefield",
			    width : 300,
			    fieldLabel : "диапазон",
			    plugins: [new Ext.ux.InputTextMask('99.99.9999-99.99.9999', true)],
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

		sw.Promed.swMedCert2XMLWindow.superclass.initComponent.apply(this, arguments);
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
		var base_form = form.Panel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var Lpu_id = base_form.findField('Lpu_id').getValue();
		var Date_range = base_form.items.items[1].value;
		
		form.getLoadMask().show();

		var params = {
			Lpu_id: Lpu_id,
			Date_range: Date_range
		};

		if ( !Ext.isEmpty(addParams) ) {
			for ( var par in addParams) {
				params[par] = addParams[par];
			}
		}
		else {
			addParams = [];
		}

		Ext.getCmp('rxfOk').disable();
		Ext.Ajax.request({
			url: '/?c=MedPersonal&m=exportMedCert2XML',
			params: params,
			timeout: 1800000,
			callback: function(options, success, response)
			{
				form.getLoadMask().hide();
				if (success)
				{
					if (!response.responseText) {
						var newParams = addParams;
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
					}
					if (result.success === false) {
						form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
						form.syncShadow();
						Ext.getCmp('rxfOk').enable();
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
		sw.Promed.swMedCert2XMLWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.Panel.getForm(),
			lpuStore = base_form.findField('Lpu_id').getStore(),
			form = this;

		base_form.reset();

		form.onHide = Ext.emptyFn;
		form.buttons[0].enable();
		form.refresh = false;
		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_o_sertifikatah_med_rabotnikov_v_xml'];
		form.TextPanel.render();

		if (!Ext.isEmpty(lpuStore) && lpuStore.indexOfId('all') === -1) {
			lpuStore.loadData([{
				Lpu_id: 'all',
				Lpu_Nick: lang['vse_mo']
			}], true);
		}
		base_form.findField('Lpu_id').setValue('all');
		this.syncShadow();
	}
});