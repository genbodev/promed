/**
* swPersonDoublesCancelWindow - окно выбора причины отказа в объединении
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Vlasenko Dmitry
* @version      13.05.2013
*/

sw.Promed.swPersonDoublesCancelWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	closeAction: 'hide',
	draggable: false,
	id: 'PersonDoublesCancelWindow',
	doSave: function() {
		var wnd = this;
		var form = this.FormPanel;
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
		
		wnd.getLoadMask(lang['otkaz_v_obyedinenii']).show();
		
		form.getForm().submit({
			failure: function(result_form, action) 
			{
				wnd.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				wnd.getLoadMask().hide();
				if (action.result) 
				{
					wnd.callback();
					wnd.hide();
				}
			}
		});
	},
	callback: Ext.emptyFn,
	initComponent: function() {
		
		var wnd = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			border: false,
			layout: 'form',
			frame: true,
			region: 'center',
			split: true,
			items: [{
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'Person_did',
				xtype: 'hidden'
			}, {
				anchor: '100%',
				hiddenName: 'PersonDoublesStatus_id',
				comboSubject: 'PersonDoublesStatus',
				fieldLabel: lang['prichina_otkaza'],
				xtype: 'swcommonsprcombo',
				allowBlank: false
			}],
			url: '/?c=PersonDoubles&m=cancelPersonDoubles',
			enableKeyEvents: true,
			keys: [{
				alt: true,
				fn: function(inp, e) {
					wnd.buttons[0].handler();
				},
				key: [ Ext.EventObject.C ],
				stopEvent: true
			}]
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: lang['sohranit']
			}, '-', HelpButton(this), {
				handler: function() {
					this.hide();
				}.createDelegate(this),
				onTabAction: function() {
					var base_form = wnd.FormPanel.getForm();
					base_form.findField('PersonDoublesStatus_id').focus(true, 50);
				},
				iconCls: 'close16',
				text: lang['zakryit']
			}],
			enableKeyEvents: true,
			items: [ this.FormPanel ],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if (e.altKey) {
						Ext.getCmp('PersonDoublesCancelWindow').hide();
					}
					else {
						return true;
					}
				},
				key: [ Ext.EventObject.P ],
				stopEvent: false
			}]
		});
		sw.Promed.swPersonDoublesCancelWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	maximized: false,
	width: 400,
	height: 110,
	modal: true,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPersonDoublesCancelWindow.superclass.show.apply(this, arguments);
		
		var wnd = this;
		
		if (!arguments[0] || Ext.isEmpty(arguments[0].Person_id) || Ext.isEmpty(arguments[0].Person_did))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					wnd.hide();
				}
			});
		}
		
		if (arguments[0].callback) 
		{
			wnd.callback = arguments[0].callback;
		}
		
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		base_form.setValues(arguments[0]);
		base_form.findField('PersonDoublesStatus_id').focus(true, 50);
	},
	title: lang['otkaz_v_obyedinenii']
});