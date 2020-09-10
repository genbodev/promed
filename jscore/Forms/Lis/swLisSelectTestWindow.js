/**
* swLisSelectTestWindow - форма выбора анализатора в ЛИС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      05.09.2013
*/

sw.Promed.swLisSelectTestWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 700,
	height: 500,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	onCancel: function() {},	
	show: function() {
        sw.Promed.swLisSelectTestWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		this.UslugaComplexMedService_pid = null;
		if (arguments[0].UslugaComplexMedService_pid) {
			this.UslugaComplexMedService_pid = arguments[0].UslugaComplexMedService_pid;
		}
		
		this.setTitle(lang['vyiberite_uslugu_testa_lis']);
		
		this.LisTestGrid.removeAll();
		this.LisTestGrid.getGrid().getStore().load({
			params: {
				UslugaComplexMedService_pid: win.UslugaComplexMedService_pid
			}
		});
	
		this.center();
	},
	
	callback: Ext.emptyFn,
	
	LisSelectTest: function() {
		var record = this.LisTestGrid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('test_id'))) {
			return false;
		}
		
		this.callback(record.data);
		this.hide();		
	},
	
	initComponent: function() {
    	
		var win = this;
		
		this.LisTestGrid = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			onEnter: this.LisSelectTest.createDelegate(this),
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			border: false,
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{name: 'test_id', type: 'int', hidden: true, key: true},
				{name: 'test_code', header: lang['kod'], type: 'string', width: 100},
				{name: 'test_name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'}
			],
			dataUrl: '/?c=LisSpr&m=loadTestsGrid',
			totalProperty: 'totalCount'
		});
		
		this.LisTestGrid.getGrid().on('rowdblclick', this.LisSelectTest.createDelegate(this));
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.LisSelectTest.createDelegate(this)
			}, 
			'-',
			{
				text: lang['zakryit'],
				iconCls: 'close16',
				handler: function(button, event) {
					win.onCancel();
					win.hide();
				}
			}],
			items: [this.LisTestGrid]

		});
		
		sw.Promed.swLisSelectTestWindow.superclass.initComponent.apply(this, arguments);
	}
});