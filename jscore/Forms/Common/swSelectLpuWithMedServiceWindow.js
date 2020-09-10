/**
* swSelectLpuWithMedServiceWindow - форма выбора ЛПУ, в которой есть служба определенного типа
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Storozhev
* @version      27.06.2012
*/

sw.Promed.swSelectLpuWithMedServiceWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 700,
	height: 500,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	onCancel: function() {},
	listeners: {
		hide: function() {
			this.GridPanel.ViewGridPanel.getStore().removeAll();
			this.onCancel();
		}
	},
	
	show: function() {
        sw.Promed.swSelectLpuWithMedServiceWindow.superclass.show.apply(this, arguments);
		if( !arguments[0] || !arguments[0].MedServiceType_id || !arguments[0].MedServiceType_Name ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], this.hide.createDelegate(this));
		}
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		this.setTitle(lang['lpu_v_kotoryih_est'] + arguments[0].MedServiceType_Name);
		
		with(this.GridPanel.ViewGridPanel.getStore()) {
			baseParams = {MedServiceType_id: arguments[0].MedServiceType_id};
			load();
		}
		
		this.center();
	},
	
	callback: Ext.emptyFn,
	
	selectLpu: function() {
		var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		this.callback(record.data);
		this.hide();
	},
	
	initComponent: function() {
		this.GridPanel = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			onEnter: this.selectLpu.createDelegate(this),
			//pageSize: 20,
			//paging: true,
			border: false,
			autoLoadData: false,
			stripeRows: true,
			//root: 'data',
			stringfields: [
				{name: 'Lpu_id', type: 'int', hidden: true, key: true},
				{name: 'Lpu_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
				{name: 'Lpu_Nick', header: lang['kratkoe_naimenovanie'], type: 'string', width: 200}
			],
			dataUrl: '/?c=MedService&m=getLpusWithMedService',
			totalProperty: 'totalCount'
		});
		
		this.GridPanel.ViewGridPanel.on('rowdblclick', this.selectLpu.createDelegate(this));
		this.GridPanel.ViewGridPanel.on('render', function() {
			this.GridPanel.ViewContextMenu = new Ext.menu.Menu();
		}.createDelegate(this));
		var parentObject = this;
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.selectLpu.createDelegate(this)
			}, 
			'-',
			{
				text: lang['zakryit'],
				iconCls: 'close16',
				handler: function(button, event) {
//					parentObject.onCancel();
					parentObject.hide();
				}
			}],
			items: [this.GridPanel]
		});
		
		sw.Promed.swSelectLpuWithMedServiceWindow.superclass.initComponent.apply(this, arguments);
	}
});