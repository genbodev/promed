/**
* swRegistryReceptImportWindow - окно импорта реестра рецептов из DBF.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      RegistryRecept
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      20.12.2012
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swRegistryReceptImportWindow = Ext.extend(sw.Promed.BaseForm, 
{
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'RegistryReceptImportWindow',
	title: WND_REGISTRYRECEPT_IMPORT_DBF,
	width: 400,
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		var win = this;
		
		this.RegistryReceptImportTpl = new Ext.Template(
		[
			'<div>{recRecAll} {recRecPersAll}</div> <div>{dates}</div>'
		]);
		
		this.RegistryReceptImportPanel = new Ext.Panel(
		{
			bodyStyle: 'padding:2px',
			layout: 'fit',
			border: true,
			frame: false,
			height: 36,
			html: ''
		});
	
		this.TextPanel = new Ext.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			fileUpload: true,
			bodyStyle: 'padding: 5px 5px 0',
			frame: true,
			labelWidth: 100,
			url: '/?c=RegistryRecept&m=importRegistryFromDbf',
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{ name: 'recRecAll' },
				{ name: 'recRecPersAll' }
			]),
			defaults: 
			{
				anchor: '95%',
				allowBlank: false,
				msgTarget: 'side'
			},
			items: 
			[{
					hiddenName: 'RegistryReceptType_id',
					comboSubject: 'RegistryReceptType',
					fieldLabel: lang['tip_reestra'],
					tabindex: TABINDEX_RRIW + 0,
					allowBlank: false,
					xtype: 'swcommonsprcombo'
			}, {
				xtype: 'fileuploadfield',
				anchor: '95%',
				tabindex: TABINDEX_RRIW + 1,
				emptyText: lang['vyiberite_fayl_reestra'],
				fieldLabel: lang['reestr'],
				name: 'RegistryFile'
			},
			this.RegistryReceptImportPanel]
		});
		
		this.Panel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			labelAlign: 'right',
			labelWidth: 100,
			items: [this.TextPanel]
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				handler: function() 
				{
					win.doSave();
				},
				iconCls: 'refresh16',
				tabIndex: TABINDEX_RRIW + 10,
				text: lang['zagruzit']
			}, 
			{				text: '-'
			},
			HelpButton(this, TABINDEX_RRIW + 11),
			{
				handler: function() 
				{
					win.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_RRIW + 12,
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swRegistryReceptImportWindow.superclass.initComponent.apply(this, arguments);
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
		var win = this;
		
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

		win.getLoadMask().show(lang['zagruzka_i_analiz_reestra_podojdite']);
		win.buttons[0].disable();
		
		form.getForm().submit(
		{
			failure: function(result_form, action) 
			{
				win.getLoadMask().hide();
				win.buttons[0].enable();
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
			},
			success: function(result_form, action) 
			{
				win.getLoadMask().hide();
				var answer = action.result;
				if (answer) 
				{
					if (answer.recRecAll)
					{
						
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: answer.Message,
							title: lang['soobschenie']
						});
						win.RegistryReceptImportTpl.overwrite(win.RegistryReceptImportPanel.body, 
						{
							recRecAll: "Всего записей о рецептах: <b>"+answer.recRecAll+"</b><br>",
							recRecPersAll: "Всего записей о пациентах: <b>"+answer.recRecPersAll+"</b>"
						});
						win.buttons[0].disable();
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['vo_vremya_vyipolneniya_operatsii_zagruzki_reestra_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
						win.buttons[0].enable();
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
		sw.Promed.swRegistryReceptImportWindow.superclass.show.apply(this, arguments);
		var win = this;
		win.onHide = Ext.emptyFn;
		win.buttons[0].enable();
		win.TextPanel.getForm().reset();
		
		win.RegistryReceptImportTpl.overwrite(win.RegistryReceptImportPanel.body, {});
						
		if (arguments[0] && arguments[0].onHide) 
		{
			win.onHide = arguments[0].onHide;
		}
	}
});