/**
* swRegistryErrorDataToDbfWindow - окно выгрузки данных по реестру с ошибочной страховой.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Пшеницын Иван
* @version      25.10.2010
* @comment      Префикс для id компонентов redtdw (swRegistryErrorDataToDbfWindow)
*
*
* @input data: Registry_id - ID реестра
*              RegistryType_id - Тип реестра
*/

sw.Promed.swRegistryErrorDataToDbfWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swRegistryErrorDataToDbfWindow',
	title: lang['vyigruzka_oshibok_dlya_sverki_smo'],
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		/*this.Status = new Ext.ProgressBar(
		{
			text:lang['ojidaetsya_deystvie_polzovatelya'],
			id:'stbar',
			textEl:'stbartext',
			cls:'custom',
			renderTo:'RegistryDbfPanel'
		});
		*/
		this.TextPanel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryErrorDataDbfTextPanel',
			html: lang['vyigruzka_oshibok_dlya_sverki_smo']
		});
		
		this.Panel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'RegistryErrorDataDbfPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [this.TextPanel]
			/*,
			keys: [
			{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.G:
							this.print();
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.G, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}]*/
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				id: 'redtdwOk',
				handler: function() 
				{
					this.ownerCt.createDBF();
				},
				iconCls: 'refresh16',
				text: lang['vyigruzit']
			}, 
			{				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'redtdwOk',
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swRegistryErrorDataToDbfWindow.superclass.initComponent.apply(this, arguments);
	},
	
	listeners: 
	{
		'hide': function() 
		{
			this.onHide();
		}
	},
	createDBF: function() 
	{
		var Registry_id = this.Registry_id;
		var RegistryType_id = this.RegistryType_id;
		//var id_salt = Math.random();
		//var win_id = 'dbf_export' + Math.floor(id_salt * 10000);
		//var win = window.open('/?c=Registry&m=exportRegistryToDbf&Registry_id=' + this.Registry_id, win_id);
		var form = this;
		form.getLoadMask().show();
		Ext.Ajax.request(
		{
			url: form.formUrl,
			params: 
			{
				 Registry_id: Registry_id
				,RegistryType_id: RegistryType_id
			},
			callback: function(options, success, response) 
			{
				form.getLoadMask().hide();
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					var alt = '';
					var msg = '';
					/*if (result.usePrevDbf)
					{
						alt = lang['izmeneniy_s_reestrom_ne_byilo_proizvedeno_ispolzuetsya_sohranennaya_dbf_predyiduschey_vyigruzki'];
						msg = lang['dbf_predyiduschey_vyigruzki'];
					}*/

					if ( result.success == true ) {
						if ( typeof result.Link == 'string' ) {
							form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить данные</a>'+msg;
						}
						else if ( typeof result.Link == 'object' ) {
							var i;
							var linkText = '';

							for ( i in result.Link ) {
								if ( typeof result.Link[i] == 'string' ) {
									linkText = linkText + '<div><a target="_blank" title="' + alt + '" href="' + result.Link[i] + '">Скачать и сохранить данные (' + i.toString() + ')</a> ' + msg + '</div>';
								}
							}

							form.TextPanel.getEl().dom.innerHTML = linkText;
						}
						else {
							form.TextPanel.getEl().dom.innerHTML = lang['nevernyiy_format_spiska_ssyilok'];
						}

						Ext.getCmp('redtdwOk').disable();
					}
					else {
						form.TextPanel.getEl().dom.innerHTML = (result.Error_Msg ? result.Error_Msg : lang['oshibka_pri_formirovanii_faylov']);
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
		sw.Promed.swRegistryErrorDataToDbfWindow.superclass.show.apply(this, arguments);
		var form = this;
		
		form.Registry_id = null;
		form.RegistryType_id = null;
		form.onHide = Ext.emptyFn;
		Ext.getCmp('redtdwOk').enable();
		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_oshibok_dlya_sverki_smo'];
		form.TextPanel.render();
		
		if (!arguments[0] || !arguments[0].Registry_id || !arguments[0].RegistryType_id) 
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

		form.syncShadow();

		if (arguments[0].Registry_id) 
		{
			form.Registry_id = arguments[0].Registry_id;
		}
		if (arguments[0].RegistryType_id) 
		{
			form.RegistryType_id = arguments[0].RegistryType_id;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].url) 
		{
			form.formUrl = arguments[0].url;
		}
		else 
		{
			form.formUrl = '/?c=Registry&m=exportRegistryErrorDataToDbf';
		}
	}
});