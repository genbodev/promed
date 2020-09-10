/**
* swDispatchOperEnvWindow - Оперативная обстановка по диспетчерам СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dyomin Dmitry
* @version      01.10.2012
*/

sw.Promed.swDispatchOperEnvWindow = Ext.extend(sw.Promed.BaseForm, {
	
	id: 'swDispatchOperEnvWindow',
	
	modal: true,
	
	width: 800,
	
	autoHeight: true,
	
	onCancel: Ext.emptyFn,
	
	callback: Ext.emptyFn,
	
	listeners: {
		hide: function() {
			this.GridPanel.ViewGridPanel.getStore().removeAll();
		}
	},
	
	show: function() {
        sw.Promed.swDispatchOperEnvWindow.superclass.show.apply(this, arguments);
		
		filterButton = Ext.getCmp('filterOnlineUsers');
		filterButton.setText(lang['vse_dispetcheryi']);
		/*
		if( arguments[0] ) {
			if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
				this.callback = arguments[0].callback;
			}

			if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
				this.onCancel = arguments[0].onCancel;
			}
		}
		*/
		this.setTitle(lang['operativnaya_obstanovka_po_dispetcheram_smp']);
		
		with(this.GridPanel.ViewGridPanel.getStore()) {
		
			baseParams = {};
			load({callback: function() {
				this.filter('online', true);
				}
			});
		}
		
		this.center();
	},
	
	filterOnline: function(){
		storeDispatcher = this.GridPanel.ViewGridPanel.getStore();
		filterButton = Ext.getCmp('filterOnlineUsers');
		switch (filterButton.getText())
		{
			case lang['vse_dispetcheryi'] : {
				filterButton.setText(lang['onlayn']);
				storeDispatcher.clearFilter();
				break;		
			}
			case lang['onlayn'] : {
				filterButton.setText(lang['vse_dispetcheryi']);	
				storeDispatcher.filter('online', 'true');
				break;		
			}
		}
		
//		console.log(storeDispatcher);
		//storeDispatcher.filter('online', 'false');
		
		//console.log(Ext.get('filterOnlineUsers'))
	},
	
	initComponent: function() {
		
		this.GridPanel = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			border: false,
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'pmUser_id', header: 'ID', key: true, hidden: true, hideable: false},
				{ name: 'Lpu_Name', header: lang['lpu'], width: 250 },
				{ name: 'pmUser_name', type: 'string', header: lang['dispetcher'], width: 300 },
				{ name: 'online', header: lang['onlayn'], width: 100, hidden: true  }
				//{ name: 'Dispatch_DTStart', type: 'date', header: 'Время расботы с', renderer: Ext.util.Format.dateRenderer('H:i') },
				//{ name: 'Dispatch_DTFinish', type: 'date', header: 'Время расботы по', renderer: Ext.util.Format.dateRenderer('H:i') },
				//{ name: 'Dispatcher_ReceivedCalls', type: 'int', header: 'Вызовов принято/обслужено' }
			],
			dataUrl: '/?c=EmergencyTeam&m=loadDispatchOperEnv',
			totalProperty: 'totalCount'
		});
		
		this.GridPanel.ViewGridPanel.on('render', function() {
			this.GridPanel.ViewContextMenu = new Ext.menu.Menu();
		}.createDelegate(this));
		
		
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [
				{
					text: lang['onlayn'],
					id : 'filterOnlineUsers',
					//iconCls: 'close16',
					handler: function(button, event) {
						this.filterOnline();
					}.createDelegate(this)
				},
				'-',
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event){ShowHelp(this.ownerCt.title);}
				},			
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
		
		sw.Promed.swDispatchOperEnvWindow.superclass.initComponent.apply(this, arguments);
	}
});