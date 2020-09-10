/**
* Форма Журнал направлений на МСЭ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
*/

sw.Promed.swEvnPrescrMseReturnWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Причина возврата в МО на доработку',
	modal: true,
	resizable: false,
	maximized: false,
	width: 600,
	height: 200,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swEvnPrescrMseReturnWindow',
	closeAction: 'hide',
	id: 'swEvnPrescrMseReturnWindow',
	objectSrc: '/jscore/Forms/Mse/swEvnPrescrMseReturnWindow.js',
	onHide: Ext.emptyFn,
	onSave: Ext.emptyFn,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	show: function()
	{
		sw.Promed.swEvnPrescrMseReturnWindow.superclass.show.apply(this, arguments);
		
		if (!arguments[0] || !arguments[0].EvnPrescrMse_id) {
			this.hide();
			return false;
		}
        if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
        }
        if (arguments[0].onSave) {
			this.onSave = arguments[0].onSave;
        }
		
		var b_f = this.FilterPanel.getForm();
		b_f.reset();
		b_f.findField('Evn_id').setValue(arguments[0].EvnPrescrMse_id);
		b_f.findField('EvnStatusHistory_Cause').focus(true, 100);
	},
	
	doSave: function()
	{
		var win = this;
		var form = this.FilterPanel.getForm();
		var lm = this.getLoadMask(lang['sohranenie_dannyih']);
		lm.show();
		form.submit({
			success: function(form,action){
				lm.hide();
				win.hide();
				win.onSave();
			},
			failure: function(){
				lm.hide();
			}
		});
	},
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.FilterPanel = new Ext.FormPanel({
			collapsible: false,
			region: 'center',
			autoHeight: true,
			url: '/?c=Evn&m=updateEvnStatus',
			defaults: {
				border: false
			},
			floatable: false,
			bodyStyle: 'padding: 3px;',
			items: [{
				xtype: 'hidden',
				name: 'Evn_id'
			}, {
				xtype: 'hidden',
				name: 'EvnStatus_id',
				value: 30
			}, {
				xtype: 'hidden',
				name: 'EvnClass_id',
				value: 71
			}, {
				name: 'EvnStatusHistory_Cause',
				xtype: 'textarea',
				anchor: '100%',
				height: 120,
				hideLabel: true
			}]
		});
		
		Ext.apply(this,	{
			layout: 'border',
			buttons: [{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: lang['sohranit']
			}, {
				text      : lang['otmena'],
				tabIndex  : -1,
				tooltip   : lang['otmena'],
				iconCls   : 'cancel16',
				handler   : function()
				{
					this.ownerCt.hide();
				}
			}],
			keys: 
			[{
				fn: function(inp, e) {
					if ( e.getKey() == Ext.EventObject.ENTER ) {
						this.doSearch();
					}
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			items: [this.FilterPanel]
		});
		sw.Promed.swEvnPrescrMseReturnWindow.superclass.initComponent.apply(this, arguments);
	}
});