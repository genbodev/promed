/**
 * swEvnPLDispScreenOnkoWindow 
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @access       public
 * @comment
 */

Ext6.define('common.EMK.swEvnPLDispScreenOnkoWindow', {
	/* свойства */
	requires: [
		'common.EMK.PersonInfoPanel',
		'common.EMK.EvnPLDispScreenOnkoForm',
	],
	alias: 'widget.swEvnPLDispScreenOnkoWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	swMaximized: true,
	//renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Первичный онкологический скрининг',
	width: 1150,
	maxHeight: 800,
	noTaskBarButton: true,
	userMedStaffFact: null,
	listeners: {
		resize: function (el, width, height, oldWidth, oldHeight) {
			el.FormPanel.setHeight(height-140);
		},
		hide: function(win) {
			win.callback();
		},
	},
	onSprLoad: function(arguments) {

		var me = this;
		
		this.userMedStaffFact = arguments[0].userMedStaffFact || {};
		this.userMedStaffFact.LpuSection_id = this.userMedStaffFact.LpuSection_id || arguments[0].LpuSection_id || null;
		this.userMedStaffFact.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id || arguments[0].MedStaffFact_id || null;
		this.userMedStaffFact.MedPersonal_id = this.userMedStaffFact.MedPersonal_id || getGlobalOptions().medpersonal_id || null;
		
		this.Person_id = arguments[0].Person_id;
		this.Server_id = arguments[0].Server_id;
		this.PersonEvn_id = arguments[0].PersonEvn_id;
		this.EvnPLDispScreenOnko_id = arguments[0].EvnPLDispScreenOnko_id;
		this.callback = arguments[0].callback || Ext6.emptyFn;
		
		this.PersonInfoPanel.load({
			Person_id: me.Person_id,
			Server_id: me.Server_id,
			PersonEvn_id: me.PersonEvn_id,
			noToolbar: true
		});
		
		var setParams = {
			Person_id: me.Person_id,
			Server_id: me.Server_id,
			EvnPLDispScreenOnko_id: me.EvnPLDispScreenOnko_id
		};
		
		this.FormPanel.PrescribePanel.userMedStaffFact = this.userMedStaffFact;
		
		this.FormPanel.setParams(setParams);
		this.FormPanel.loadData();
	},

	show: function() {
		this.callParent(arguments);
	},

	/* конструктор */
    initComponent: function() {
		var me = this;

		this.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanel', {
			region: 'north',
			buttonPanel: false,
			border: true,
			bodyStyle: 'border-width: 0 0 1px 0;',
			userMedStaffFact: this.userMedStaffFact,
			ownerWin: this,
			listeners: {
				resize: function (el, width, height, oldWidth, oldHeight) {
					//el.ownerCt.FormPanel.setHeight(el.ownerCt.getHeight() - height - 80);
					el.ownerCt.FormPanel.setHeight(el.ownerCt.getHeight() - height - 40);
				}
			}
		});
		
		this.FormPanel = Ext6.create('common.EMK.EvnPLDispScreenOnkoForm', {
			height: 530,
			callback: function() {
				me.hide();
			},
			ownerWin: this
		});

		Ext6.apply(this, {
			items: [
				this.PersonInfoPanel,
				this.FormPanel
			],
			/*buttons: [{
				xtype: 'SimpleButton',
				text: 'Закрыть',
				handler:function () {
					me.hide();
				}
			}]*/
		});

		this.callParent(arguments);
    }
});