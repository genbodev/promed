/**
 * swRegistryESExportToXMLWindow - окно экспротра реестра ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Mse
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			03.10.2014
 */

/*NO PARSE JSON*/

sw.Promed.swRegistryESExportToXMLWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swRegistryESExportToXMLWindow',
	title: lang['formirovanie_lvn_dlya_fss'],
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function()
	{
		this.TextPanel = new Ext.Panel(
			{
				autoHeight: true,
				bodyBorder: false,
				border: false,
				style: 'margin-bottom: 5px;',
				id: 'RegistryXmlTextPanel',
				html: lang['vyigruzka_dannyih_reestra_v_formate_xml_dlya_fss']
			});

		this.Panel = new Ext.form.FormPanel(
			{
				autoHeight: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: true,
				id: 'RegistryXmlPanel',
				labelAlign: 'right',
				labelWidth: 50,
				items: [this.TextPanel]
			});

		Ext.apply(this,
			{
				autoHeight: true,
				buttons: [
					{
						id: 'rxfOk',
						handler: function()
						{
							this.ownerCt.createXML();
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
				items: [this.Panel]
			});
		sw.Promed.swRegistryESExportToXMLWindow.superclass.initComponent.apply(this, arguments);
	},

	listeners:
	{
		'hide': function()
		{
			if (this.refresh)
				this.onHide();
		}
	},
	createXML: function(addParams)
	{
		var RegistryES_id = this.RegistryES_id;
		var form = this;

		var base_form = form.Panel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show(
				{
					buttons:Ext.Msg.OK,
					fn:function () {
						form.Panel.getFirstInvalidEl().focus(true);
					},
					icon:Ext.Msg.WARNING,
					msg:ERR_INVFIELDS_MSG,
					title:ERR_INVFIELDS_TIT
				});
			return false;
		}

		form.getLoadMask().show();

		var params = {
			RegistryES_id: RegistryES_id
		};

		if (addParams != undefined) {
			for(var par in addParams) {
				params[par] = addParams[par];
			}
		} else {
			addParams = [];
		}

		Ext.Ajax.request(
			{
				url: '/?c=RegistryES&m=exportRegistryESToXMLManual',
				params: params,
				callback: function(options, success, response)
				{
					form.getLoadMask().hide();
					if (success)
					{
						if (!response.responseText) {
							var newParams = addParams;
							newParams.OverrideExportOneMoreOrUseExist = 1;
							newParams.onlyLink = 1;
							form.createXML(newParams);
							return false;
						}
						var result = Ext.util.JSON.decode(response.responseText);

						if (result.Error_Code && result.Error_Code == '11') { // Уже есть выгруженный XML
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									if ( buttonId == 'yes' )
									{
										var newParams = addParams;
										newParams.OverrideExportOneMoreOrUseExist = 2;
										form.createXML(newParams);
									} else {
										var newParams = addParams;
										newParams.OverrideExportOneMoreOrUseExist = 1;
										form.createXML(newParams);
									}
								},
								msg: lang['fayl_reestra_suschestvuet_na_servere_esli_vyi_hotite_sformirovat_novyiy_fayl_vyiberete_da_esli_hotite_skachat_fayl_s_servera_najmite_net'],
								title: lang['podtverjdenie']
							});

							return false;
						}

						var alt = '';
						var msg = '';
						form.refresh = true;
						/*if (result.usePrevXml)
						{
							alt = lang['izmeneniy_s_reestrom_ne_byilo_proizvedeno_ispolzuetsya_sohranennyiy_xml_predyiduschey_vyigruzki'];
							msg = lang['xml_predyiduschey_vyigruzki'];
						}*/
						if (result.Link) {
							form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить реестр</a>'+msg;
							Ext.getCmp('rxfOk').disable();
						}
						if (result.success === false) {
							form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
							Ext.getCmp('rxfOk').disable();
						} else {
							form.callback();
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
		sw.Promed.swRegistryESExportToXMLWindow.superclass.show.apply(this, arguments);
		var form = this;

		form.RegistryES_id = null;
		form.onHide = Ext.emptyFn;
		form.callback = Ext.emptyFn;
		Ext.getCmp('rxfOk').enable();
		form.refresh = false;
		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_reestra_v_formate_xml'];
		form.TextPanel.render();

		if (!arguments[0] || !arguments[0].RegistryES_id)
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: lang['oshibka_otkryitiya_formyi'] + form.id + lang['ne_ukazanyi_neobhodimyie_vhodnyie_parametryi'],
					title: lang['oshibka']
				});
			this.hide();
		}

		if (arguments[0].RegistryES_id)
		{
			form.RegistryES_id = arguments[0].RegistryES_id;
		}
		if (arguments[0].onHide)
		{
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].callback)
		{
			form.callback = arguments[0].callback;
		}
	}
});