/**
* swApparatusSelectWindow - форма выбора аппарата
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      02.10.2013
*/

sw.Promed.swApparatusSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 700,
	height: 500,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	onCancel: function() {},	
	show: function() {
        sw.Promed.swApparatusSelectWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		this.MedService_pid = null;
		if (arguments[0].MedService_pid) {
			this.MedService_pid = arguments[0].MedService_pid;
		}
		
		this.setTitle(lang['vyiberite_apparat']);
		
		this.ApparatusGrid.removeAll();
		this.ApparatusGrid.getGrid().getStore().load({
			params: {
				MedService_pid: win.MedService_pid
			}
		});
	
		this.center();
	},
	
	callback: Ext.emptyFn,
	
	OnApparatusSelect: function() {
		var record = this.ApparatusGrid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('MedService_id'))) {
			return false;
		}
		
		this.callback(record.data);
		this.hide();		
	},
	
	initComponent: function() {
    	
		var win = this;
		
		this.ApparatusGrid = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			onEnter: this.OnApparatusSelect.createDelegate(this),
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
				{name: 'MedService_id', type: 'int', header: 'ID', key: true},
				{id: 'autoexpand', name: 'MedService_Name',  type: 'string', header: lang['naimenovanie'], width: 150},
				{name: 'MedService_begDT',  type: 'date', header: lang['data_sozdaniya'], width: 100},
				{name: 'MedService_endDT',  type: 'date', header: lang['data_zakryitiya'], width: 100}
			],
			dataUrl: '/?c=MedService&m=loadApparatusList',
			totalProperty: 'totalCount'
		});
		
		this.ApparatusGrid.getGrid().on('rowdblclick', this.OnApparatusSelect.createDelegate(this));
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.OnApparatusSelect.createDelegate(this)
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
			items: [this.ApparatusGrid]

		});
		
		sw.Promed.swApparatusSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});