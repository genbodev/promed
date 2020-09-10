/**
* swLpuOperEnvWindow - Оперативная обстановка по выбранному ЛПУ
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

sw.Promed.swLpuOperEnvWindow = Ext.extend(sw.Promed.BaseForm, {
	
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
	
	groups: [
		lang['postupivshie_iz_smp'],
		lang['prinyatyie_iz_smp'],
		lang['obslujennyie_iz_smp'],
		lang['postupivshie_iz_nmp'],
		lang['prinyatyie_iz_nmp'],
		lang['obslujennyie_iz_nmp'],
		lang['otkaz']
	],
	
	getGroupName: function( idx ){
		return this.groups[ idx-1 ];
	},
	
	show: function() {
        sw.Promed.swLpuOperEnvWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] || !arguments[0].Lpu_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], this.hide.createDelegate(this));
		}
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		this.setTitle(lang['prosmotr_operativnoy_obstanovki_vyizovov_v_lpu']);
		
		with(this.GridPanel.ViewGridPanel.getStore()) {
			baseParams = {Lpu_id: arguments[0].Lpu_id};
			load();
		}
		
		this.center();
	},
	
	initComponent: function() {
    	
		var current_win = this;
		
		this.GridPanel = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: false,
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			border: false,
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'CmpCallCard_prmDate', type: 'date', header: lang['data_vremya'], renderer: Ext.util.Format.dateRenderer('d.m.Y H:i') },
				{ name: 'CmpCallCard_Ngod', header: lang['№_vyizova_za_god'], width: 100 },
				{ name: 'Person_FIO', header: lang['patsient'], width: 250 },
				{ name: 'CmpReason_Name', header: lang['povod'], width: 200 },
				{ name: 'CmpGroup_id', header: lang['status'], width: 200, renderer: function(v,p,r){
					return current_win.getGroupName( v );
				}}
			],
			dataUrl: '/?c=CmpCallCard&m=loadLpuOperEnv',
			totalProperty: 'totalCount'
		});
		
		this.GridPanel.ViewGridPanel.on('render', function() {
			this.GridPanel.ViewContextMenu = new Ext.menu.Menu();
		}.createDelegate(this));
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['zakryit'],
				iconCls: 'close16',
				handler: function(button, event) {
					button.ownerCt.onCancel();
					button.ownerCt.hide();
				}
			}],
			items: [this.GridPanel]
		});
		
		sw.Promed.swLpuOperEnvWindow.superclass.initComponent.apply(this, arguments);
	}
});