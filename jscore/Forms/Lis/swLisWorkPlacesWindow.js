/**
* ЛИС: форма "Анализаторы (рабочие места ЛИС)"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      01.03.2012
*/

sw.Promed.swLisWorkPlacesWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['analizatoryi_rabochie_mesta_lis'],
	modal: true,
	height: 400,
	width: 600,
	shim: false,
	resizable: false,
	plain: true,
	onSelect: Ext.emptyFn,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swLisWorkPlacesWindow',
	closeAction: 'hide',
	id: 'swLisWorkPlacesWindow',
	objectSrc: '/jscore/Forms/Lis/swLisWorkPlacesWindow.js',
	buttons: [
		/*{
			handler: function()
			{
				this.ownerCt.doSelect();
			},
			iconCls: 'ok16',
			text: lang['vyibrat']
		},*/
		'-',
		{
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	show: function()
	{
		sw.Promed.swLisWorkPlacesWindow.superclass.show.apply(this, arguments);
		
		this.pmUser_Login = arguments[0].pmUser_Login || getGlobalOptions().pmUser_Login;
		if (!this.pmUser_Login || this.pmUser_Login == null) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		var gridStore = this.Grid.ViewGridPanel.getStore();
		gridStore.baseParams.pmUser_Login = this.pmUser_Login;
		gridStore.load();
		this.center();
	},
	
	deleteLisSetting: function()
	{
		var record = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		
		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_analizator'],
			title: lang['podtverjdenie'],
			buttons: Ext.Msg.YESNO,
			fn: function(b, text, obj) {
				if(b == 'yes') {
					this.getLoadMask(lang['udalenie_analizatora']).show();
					Ext.Ajax.request({
						params: {					
							pmUser_Login: this.pmUser_Login,
							lis_login: record.get('lis_login')
						},
						callback: function(o, s, r) {
							this.getLoadMask().hide();
							if(s) {
								this.Grid.ViewActions.action_refresh.execute();
							}
						}.createDelegate(this),
						url: '/?c=User&m=deleteLisSetting'
					});
				}
			}.createDelegate(this)
		});
	},
	
	openLisSettingsWindow: function(action)
	{
		var record = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if(!record && action != 'add') return false;
		
		getWnd('swLisSettingsWindow').show({
			action: action,
			pmUser_Login: this.pmUser_Login,
			lis_login: record.get('lis_login') || null,
			onHide: function() {
				this.Grid.ViewActions.action_refresh.execute();
			}.createDelegate(this)
		});
	},
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.Grid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			editformclassname: 'swLisSettingsWindow',
			autoScroll: true,
			actions: [
				{ name: 'action_add', handler: this.openLisSettingsWindow.createDelegate(this, ['add']) },
				{ name: 'action_edit', handler: this.openLisSettingsWindow.createDelegate(this, ['edit']) },
				{ name: 'action_view', hidden: true },
				{ name: 'action_print', hidden: true },
				{ name: 'action_delete', handler: this.deleteLisSetting.createDelegate(this) }
			],
			stringfields: [
				{ name: 'lis_login', type: 'string', hidden: true, key: true },
				{ name: 'lis_analyzername', header: lang['naimenovanie_analizatora'], type: 'string', width: 200 },
				{ name: 'lis_note', header: lang['primechanie'], type: 'string', id: 'autoexpand' }
			],
			dataUrl: '/?c=User&m=loadLisWPGrid'
		});
		
		Ext.apply(this,	{
			bodyStyle: 'padding: 5px;',
			items: [this.Grid]
		});
		sw.Promed.swLisWorkPlacesWindow.superclass.initComponent.apply(this, arguments);
	}
});