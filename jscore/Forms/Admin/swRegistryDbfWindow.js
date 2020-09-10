/**
* swRegistryDbfWindow - окно выгрузки реестра в DBF.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Анлрей
* @version      22.12.2009
* @comment      Префикс для id компонентов rdf (RegistryDbfWindow)
*
*
* @input data: Registry_id - ID регистра
*/

sw.Promed.swRegistryDbfWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'RegistryDbfWindow',
	title: lang['formirovanie_dbf'],
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
			id: 'RegistryDbfTextPanel',
			html: '<br/>'
		});
		this.TextDbfPanel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryDbfPanel',
			html: lang['vyigruzka_v_formate_dbf_mojet_byit_ispolzovana_isklyuchitelno_dlya_vnutrennih_tseley_fond_ne_prinimaet_reestryi_v_takom_formate']
		});
		
		this.Panel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'RegistryDbfPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [this.TextDbfPanel, this.TextPanel]
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
				id: 'rdfOk',
				handler: function() 
				{
					this.ownerCt.createDBF();
				},
				iconCls: 'refresh16',
				text: lang['sformirovat']
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
				onTabElement: 'rdfOk',
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swRegistryDbfWindow.superclass.initComponent.apply(this, arguments);
	},
	
	listeners: 
	{
		'hide': function() 
		{
			if (this.refresh)
				this.onHide();
		}
	},
	createDBF: function() 
	{
		var Registry_id = this.Registry_id;
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
			},
			callback: function(options, success, response) 
			{
				form.getLoadMask().hide();
				if (success)
				{
					if (!response.responseText) {
						var newParams = {};
						newParams.onlyLink = 1;
						form.createDBF(newParams);
						return false;
					}
					
					var result = Ext.util.JSON.decode(response.responseText);
					
					if (result.success) {
						var alt = '';
						var msg = '';
						form.refresh = true;
						if (result.usePrevDbf)
						{
							alt = lang['izmeneniy_s_reestrom_ne_byilo_proizvedeno_ispolzuetsya_sohranennaya_dbf_predyiduschey_vyigruzki'];
							msg = lang['dbf_predyiduschey_vyigruzki'];
						}
						msg = msg + lang['dlya_sohraneniya_fayla_na_diske_najmite_na_ssyilku_pravoy_knopkoy_myishi_vyiberite_deystvie_sohranit_obyekt_kak'];
						form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Сохранить файл реестра</a>'+msg;
						Ext.getCmp('rdfOk').disable();
						form.TextPanel.render();
						form.doLayout();
						form.syncShadow();
					}
				}
				else 
				{
					var result = Ext.util.JSON.decode(response.responseText);
					form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
					form.TextPanel.render();
					form.doLayout();
					form.syncShadow();
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
		sw.Promed.swRegistryDbfWindow.superclass.show.apply(this, arguments);
		var form = this;
		
		form.Registry_id = null;
		form.onHide = Ext.emptyFn;
		Ext.getCmp('rdfOk').enable();
		form.refresh = false;
		form.TextPanel.getEl().dom.innerHTML = '<br/>';
		form.TextPanel.render();
		
		if (!arguments[0] || !arguments[0].Registry_id) 
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

		if (arguments[0].RegistryType_id && arguments[0].RegistryType_id.inlist([4,5])) {
			form.TextDbfPanel.body.update(lang['dlya_formirovaniya_reestra_najmite_knopku_sformirovat_dlya_otmenyi_deystviya_najmite_otmena']);
		} else {
			form.TextDbfPanel.body.update(lang['vyigruzka_v_formate_dbf_mojet_byit_ispolzovana_isklyuchitelno_dlya_vnutrennih_tseley_fond_ne_prinimaet_reestryi_v_takom_formate']);
		}
		
		form.syncShadow();
		
		if (arguments[0].Registry_id) 
		{
			form.Registry_id = arguments[0].Registry_id;
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
			form.formUrl = '/?c=Registry&m=exportRegistryToDbf';
		}
	}
});