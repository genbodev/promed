/**
* swLisSelectEquipmentWindow - форма выбора анализатора в ЛИС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      24.08.2013
*/

sw.Promed.swLisSelectEquipmentWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 800,
	height: 600,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	onCancel: function() {},	
	show: function() {
        sw.Promed.swLisSelectEquipmentWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		this.MedService_id = null;
		if (arguments[0].MedService_id) {
			this.MedService_id = arguments[0].MedService_id;
		}
		
		this.setTitle(lang['vyiberite_analizator_lis']);
		
		this.LisEquipmentGrid.removeAll();
		this.LisEquipmentGrid.getGrid().getStore().load({
			params: {
				MedService_id: win.MedService_id
			}
		});
	
		this.center();
	},
	
	callback: Ext.emptyFn,
	
	LisSelectEquipment: function() {
		var win = this;
		var record = this.LisEquipmentGrid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('equipment_id'))) {
			return false;
		}
		
		getWnd('swLisSelectEquipmentTestWindow').show({
			equipment_id: record.get('equipment_id'),
			equipment_name: record.get('equipment_name'),
			callback: function(tests) {
				win.callback(record.data, tests);
				win.hide();
			},
			onCancel: function() {
				win.hide();
			}
		});
	},
	
	initComponent: function() {
    	
		var win = this;
		
		this.LisEquipmentGrid = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			onEnter: this.LisSelectEquipment.createDelegate(this),
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
				{name: 'equipment_id', type: 'int', hidden: true, key: true},
				{name: 'equipment_code', header: lang['kod'], type: 'string', width: 100},
				{name: 'equipment_name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
				{name: 'department_name', header: lang['podrazdelenie'], type: 'string', width: 150}				
			],
			dataUrl: '/?c=LisSpr&m=loadEquipmentsGrid',
			totalProperty: 'totalCount'
		});
		
		this.LisEquipmentGrid.getGrid().on('rowdblclick', this.LisSelectEquipment.createDelegate(this));
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.LisSelectEquipment.createDelegate(this)
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
			items: [this.LisEquipmentGrid]

		});
		
		sw.Promed.swLisSelectEquipmentWindow.superclass.initComponent.apply(this, arguments);
	}
});