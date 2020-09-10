/**
* swInetUserInfoWindow - окно просмотра информации об интернет пользователе
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      26.07.2010
*/

sw.Promed.swInetUserInfoWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 220,
	id: 'InetUserInfoWindow',

	initComponent: function() {

		Ext.apply(this, {
			buttons: [
				HelpButton(this, TABINDEX_IUI + 10),
				{
					handler: function() {
						this.ownerCt.returnFunc();
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					tabIndex: TABINDEX_IUI + 11,
					text: BTN_FRMCLOSE
				}
			],
			items: [
				new Ext.Panel({
					id : 'InetUserInfoForm',
					height : 185,
					layout : 'fit',
					border : false,
					frame : true,
					style : 'padding: 10px',
					labelWidth : 100,
					url : C_INETUSERINFO
				})
			]
		});
		sw.Promed.swInetUserInfoWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'fit',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: false,
	minHeight: 220,
	minWidth: 350,
	modal: false,
	plain: true,
	resizable: false,
	returnFunc: Ext.emptyFn,
	show: function() {
		sw.Promed.swInetUserInfoWindow.superclass.show.apply(this, arguments);

		this.onHide = Ext.emptyFn;

		if (arguments[0]['pmUser_id']) {
			this.pmUser_id = arguments[0]['pmUser_id'];
		}
		if (arguments[0].callback) {
			this.returnFunc = arguments[0].callback;
		}

		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}

		this.restore();
		this.center();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		//loadMask.show();
		
		this.findById('InetUserInfoForm').load({
			url: C_INETUSERINFO,
			params: {
				pmUser_id: this.pmUser_id
			},
			success: function (form, action)
			{
				loadMask.hide();
			},
			failure: function (form, action)
			{
				loadMask.hide();
				Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
			},
			scripts:true
		});
	},
	title: lang['informatsiya_o_polzovatele_internet-portala'],
	width: 350
});
