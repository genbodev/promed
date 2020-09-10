/**
* swSelectReturnToSmpReason - форма выбора причины возврата из ПДД в СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Miyusov Alexandr
* @version      19.02.2013
*/
/**
* swSelectReturnToSmpReason - форма выбора причины возврата из ПДД в СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Miyusov Alexandr
* @version      19.02.2013
*/

sw.Promed.swSelectReturnToSmpReason = Ext.extend(sw.Promed.BaseForm, {
	width: 700,
	height: 500,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	callback: Ext.emptyFn,
	listeners: {
		hide: function() {
			this.GridPanel.ViewGridPanel.getStore().removeAll();
		}			
	},
	
	show: function() {
        sw.Promed.swSelectReturnToSmpReason.superclass.show.apply(this, arguments);
		
		if ( !arguments[0]) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		this.setTitle(lang['vyiberite_prichinu_peredachi']);
		
		with(this.GridPanel.ViewGridPanel.getStore()) {
			load();
		}
	
		this.center();
	},
	
	selectResult: function() {
		var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		var parentObject = this;
		if (record.data.requiredTextField == 1) {
			Ext.Msg.prompt(lang['vvedite_prichinu_peredachi'], lang['pojaluysta_vvedite_prichinu_peredachi'], function(btn, text){
				if (btn == 'ok'){
					text = text.replace(/^\s+/, "").replace(/\s+$/, "");
					if (text=="") {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								parentObject.selectResult();
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: lang['vvedite_prichinu_peredachi'],
							title: lang['pojaluysta_vvedite_prichinu_peredachi']
						});
					}
					else {
						record.data.comment = text
						parentObject.callback(record.data);
						parentObject.hide();
					}
				}
				else {
					parentObject.GridPanel.focus();
				}

			}, '', 60)
		}
		else {
			record.data.comment = null;
			this.callback(record.data);
			this.hide();
		}
	},
	
	initComponent: function() {
		this.height = 220;
		this.width = 300;
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
				{name: 'CmpReturnToSmpReason_id', type: 'int', hidden: true, key: true},
				{name: 'requiredTextField', type: 'int', hidden: true},
				{name: 'CmpReturnToSmpReason_Name', header: lang['prichina'], type: 'string', id: 'autoexpand'}
			],
			dataUrl: '/?c=CmpCallCard&m=getReturnToSmpReasons',
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
					button.ownerCt.hide();
				}
			}],
			items: [this.GridPanel]

		});
		
		sw.Promed.swSelectReturnToSmpReason.superclass.initComponent.apply(this, arguments);
	}
});