/**
* swLisSelectEquipmentTestWindow - форма выбора тестов анализатора
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      29.11.2013
*/

sw.Promed.swLisSelectEquipmentTestWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 800,
	height: 600,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	onCancel: function() {},	
	show: function() {
        sw.Promed.swLisSelectEquipmentTestWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		this.equipment_id = null;
		this.equipment_name = '';
		if (arguments[0].equipment_id) {
			this.equipment_id = arguments[0].equipment_id;
		}
		if (arguments[0].equipment_name) {
			this.equipment_name = arguments[0].equipment_name;
		}
		
		this.setTitle(lang['vyiberite_uslugi_analizatora'] + this.equipment_name);
		
		this.LisTestGrid.removeAll();
		this.LisTestGrid.getGrid().getStore().load({
			params: {
				equipment_id: win.equipment_id
			}
		});
	
		this.center();
	},
	
	callback: Ext.emptyFn,
	
	LisSelectEquipmentTest: function() {
		var selections = this.LisTestGrid.getGrid().getSelectionModel().getSelections();
		var tests = [];

		for	(var key in selections) {
			if (selections[key].data && !Ext.isEmpty(selections[key].data['target_test_id'])) {
				tests.push(selections[key].data['target_test_id']);
			}
		}
		
		this.callback(tests);
		this.hide();		
	},
	
	initComponent: function() {
    	
		var win = this;
		
		this.LisTestGrid = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			onEnter: this.LisSelectEquipmentTest.createDelegate(this),
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			border: false,
			selectionModel: 'multiselect',
			autoLoadData: false,
			stripeRows: true,
			onLoadData:	function() {
				win.LisTestGrid.getGrid().getSelectionModel().selectAll();
			},
			stringfields: [
				{name: 'target_test_id', type: 'string', hidden: true, key: true},
				{name: 'target_id', type: 'int', hidden: true},
				{name: 'target_code', header: lang['kod_issledovaniya'], type: 'string', width: 100},
				{name: 'target_name', header: lang['naimenovanie_issledovaniya'], type: 'string', width: 220},
				{name: 'test_id', type: 'int', hidden: true},
				{name: 'test_code', header: lang['kod_testa'], type: 'string', width: 100},
				{name: 'test_name', header: lang['naimenovanie_testa'], type: 'string', id: 'autoexpand'},
				{name: 'test_sysnick', header: lang['mnemonika'], type: 'string', width: 80}
			],
			dataUrl: '/?c=LisSpr&m=loadEquipmentTestsGrid',
			totalProperty: 'totalCount'
		});
		
		this.LisTestGrid.getGrid().on('rowdblclick', this.LisSelectEquipmentTest.createDelegate(this));
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.LisSelectEquipmentTest.createDelegate(this)
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
		
		sw.Promed.swLisSelectEquipmentTestWindow.superclass.initComponent.apply(this, arguments);
	}
});