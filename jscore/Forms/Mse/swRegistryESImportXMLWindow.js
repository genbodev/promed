/**
 * swRegistryESImportXMLWindow - окно импорта реестра ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Mse
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			24.10.2014
 */

/*NO PARSE JSON*/

sw.Promed.swRegistryESImportXMLWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'swRegistryESImportXMLWindow',
	title: lang['import_otveta_ot_fss'],
	width: 500,
	//layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function()
	{

		this.RegistryImportTpl = new Ext.Template(
			[
				'<div>{recErr}</div> <div>{dates}</div>'
			]);

		this.RegistryImportPanel = new Ext.Panel(
			{
				id: 'RegistryESImportPanel',
				bodyStyle: 'padding:2px',
				layout: 'fit',
				border: true,
				frame: false,
				height: 36,
				//maxSize: 30,
				html: ''
			});

		this.TextPanel = new Ext.FormPanel(
			{
				autoHeight: true,
				bodyBorder: false,
				fileUpload: true,
				bodyStyle: 'padding: 5px 5px 0',
				frame: true,
				id: 'RegistryESImportTextPanel',
				labelWidth: 50,
				url: '/?c=RegistryES&m=importRegistryESFromXml',
				reader: new Ext.data.JsonReader(
					{
						success: Ext.emptyFn
					},
					[
						{ name: 'RegistryES_id' },
						{ name: 'recErr' }
					]),
				//html: 'Загрузка данных проверенного реестра'
				defaults:
				{
					anchor: '95%',
					allowBlank: false,
					msgTarget: 'side'
				},
				items:
					[{
						xtype: 'fileuploadfield',
						id: 'RegistryESFile',
						anchor: '95%',
						emptyText: lang['vyiberite_fayl_reestra'],
						fieldLabel: lang['reestr'],
						name: 'RegistryESFile'
						/*,
						 buttonText: '...',
						 buttonCfg:
						 {
						 text: '',
						 iconCls: 'file-upload16'
						 }*/
					},
						this.RegistryImportPanel]
			});

		this.Panel = new Ext.Panel(
			{
				autoHeight: true,
				bodyBorder: false,
				border: false,
				//frame: true,
				id: 'RegistryESImportPanelPanel',
				labelAlign: 'right',
				labelWidth: 100,
				items: [this.TextPanel]
			});

		Ext.apply(this,
			{
				autoHeight: true,
				buttons: [
					{
						id: 'resixfOk',
						handler: function()
						{
							this.ownerCt.doSave();
						},
						iconCls: 'refresh16',
						text: lang['zagruzit']
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
						onTabElement: 'resixfOk',
						text: BTN_FRMCANCEL
					}],
				items: [this.Panel]
			});
		sw.Promed.swRegistryESImportXMLWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners:
	{
		'hide': function()
		{
			this.onHide();
		}
	},
	doSave: function()
	{
		var form = this.TextPanel;
		if (!form.getForm().isValid())
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
		this.submit();
		return true;
	},
	submit: function()
	{
		var form = this.TextPanel;
		var win = this;
		// win.getLoadMask('Загрузка и анализ реестра. Подождите...').show();

		form.getForm().submit(
			{
				params:
				{
					RegistryES_id: win.RegistryES_id
				},
				failure: function(result_form, action)
				{
					if ( action.result )
					{

						if ( action.result.Error_Msg )
						{
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else
						{
							sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_vyipolneniya_operatsii_zagruzki_reestra_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje']);
						}
					}
					win.getLoadMask().hide();
				},
				success: function(result_form, action)
				{
					win.getLoadMask().hide();
					var answer = action.result;
					if (answer)
					{
						if (answer.RegistryES_id)
						{

							sw.swMsg.show(
								{
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.INFO,
									msg: answer.Message,
									title: lang['soobschenie']
								});
							win.RegistryImportTpl.overwrite(win.RegistryImportPanel.body,
								{
									recErr: "Всего записей с ошибками:  <b>"+answer.recErr+"</b>"
								});
							Ext.getCmp('resixfOk').disable();
							win.callback();
						}
						else
						{
							sw.swMsg.show(
								{
									buttons: Ext.Msg.OK,
									fn: function()
									{
										form.hide();
									},
									icon: Ext.Msg.ERROR,
									msg: lang['vo_vremya_vyipolneniya_operatsii_zagruzki_reestra_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
									title: lang['oshibka']
								});
						}
					}
				}
			});
	},
	getLoadMask: function(MSG)
	{
		if (MSG)
		{
			delete(this.loadMask);
		}
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
		}
		return this.loadMask;
	},
	show: function()
	{
		sw.Promed.swRegistryESImportXMLWindow.superclass.show.apply(this, arguments);
		var form = this;
		form.RegistryES_id = null;
		form.onHide = Ext.emptyFn;
		form.callback = Ext.emptyFn;
		Ext.getCmp('resixfOk').enable();
		form.TextPanel.getForm().reset();
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
		//form.findById('riwuRegistryFile').setValue('');
		form.RegistryImportTpl.overwrite(form.RegistryImportPanel.body,
			{});

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