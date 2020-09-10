/**
* swSelectResultWindow - форма выбора результата для ПДД
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Sergey Popkov
* @version      21.08.2012
*/

sw.Promed.swSelectResultWindow = Ext.extend(sw.Promed.BaseForm, {
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
		}			
	},
	
	show: function() {
        sw.Promed.swSelectResultWindow.superclass.show.apply(this, arguments);
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		this.setTitle(lang['vyiberite_rezultat']);
		
		with(this.GridPanel.ViewGridPanel.getStore()) {
			//baseParams = {MedServiceType_id: arguments[0].MedServiceType_id};
			load();
		}
	
		this.center();
	},
	
	callback: Ext.emptyFn,
	
	selectResult: function() {
		var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					this.callback(record.data);
					this.hide();
				}
				if ( buttonId == 'no' ) {
					this.GridPanel.focus();
				}				
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['podtverdit_rezultat'],
			title: lang['vopros']
		});				
	},
	
	initComponent: function() {
    	
		var cur_win = this;

		
		this.GridPanel = new sw.Promed.ViewFrame({					
			id: this.id + '_Grid',
			toolbar: false,
			onEnter: this.selectResult.createDelegate(this),
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			//pageSize: 20,
			//paging: true,
			border: false,
			autoLoadData: false,
			stripeRows: true,
			//root: 'data',
			stringfields: [
				{name: 'CmpPPDResult_id', type: 'int', hidden: true, key: true},
				{name: 'CmpPPDResult_Name', header: lang['rezultat'], type: 'string', id: 'autoexpand'}
			],
			dataUrl: '/?c=CmpCallCard&m=getResults',
			totalProperty: 'totalCount'
		});
		
		this.GridPanel.ViewGridPanel.on('rowdblclick', this.selectResult.createDelegate(this));
		this.GridPanel.ViewGridPanel.on('render', function() {
			this.GridPanel.ViewContextMenu = new Ext.menu.Menu();
		}.createDelegate(this));
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.selectResult.createDelegate(this)
			}, 
			'-',
			{
				text: lang['zakryit'],
				iconCls: 'close16',
				handler: function(button, event) {
					button.ownerCt.onCancel();
					button.ownerCt.hide();
				}
			}],
			items: [this.GridPanel]

		});
		
		sw.Promed.swSelectResultWindow.superclass.initComponent.apply(this, arguments);
	}
});