Ext.define('common.BSME.tools.swBSMEXmlVersionListWindow', {
	extend: 'Ext.window.Window',
	minHeight: 500,
	title: 'Версии документа',
	modal: true,
	EvnForensic_id: null,
	autoShow: true,
	width: '50%',
	height: '50%',
	refId: 'swBSMEXmlVersionListWindow',
	closable: true,
	id: 'swBSMEXmlVersionListWindow',
	border: false,
	layout: {
        type: 'fit'
    },
	store: new Ext.data.JsonStore({
		//autoLoad: true,
		pageSize: 10,
		idProperty: 'ForensicEvnXmlVersion_id',
		fields: [
			{name: 'ForensicEvnXmlVersion_id', type: 'int'},
			{name: 'ForensicEvnXmlVersion_Num', type: 'string'},
			{name: 'ForensicEvnXmlVersion_insDT', type: 'string'},
			{name: 'EvnXml_id', type: 'int'},
			{name: 'pmUser_Name', type: 'string'},
		],				
		proxy: {
			type: 'ajax',
			url: '/?c=BSME&m=getForensicXmlVersionList',
			reader: {
				type: 'json',
				successProperty: 'success'
			},
			actionMethods: {
				create : 'POST',
				read   : 'POST',
				update : 'POST',
				destroy: 'POST'
			}
		}
	}),
	show: function() {
		
		this.callParent(arguments);
		
		if (!this.EvnForensic_id) {
			Ext.Msg.alert('Ошибка', 'Не передан идентификатор случая');
			this.close();
		}
		
		this.store.load({params: {
			EvnForensic_id: this.EvnForensic_id	
		}});
	},
	initComponent: function() {
		var win = this;
		
		
		win.grid = Ext.create('Ext.grid.Panel',{
			store: win.store,
			flex: 1,
			height: '100%',
			//layout: 'auto',
			columns: [
				{ text: 'Номер версии',  dataIndex: 'ForensicEvnXmlVersion_Num', width: 100 },
				{ text: 'Дата создания версии', dataIndex: 'ForensicEvnXmlVersion_insDT', flex: 1 },
				{ text: 'Фио создавшего пользователя', dataIndex: 'pmUser_Name', flex: 1 }
			],
			listeners: {
				
			}
		}),
		
		win.store.on('load',function(store, records, successful, eOpts) {
			if (records && records.length>0) {
				win.grid.getSelectionModel().select(0);
			} else {
				Ext.Msg.alert('Внимание', 'У заявки не создано ни одной версии заключения');
				win.close();
			}
		});
		
		win.showButton = Ext.create('Ext.button.Button',{
			xtype: 'button',
			margin: '0 5 0 0',
			text: 'Просмотр',
			handler: function(){
				var selectionModel = win.grid.getSelectionModel();
				if (!selectionModel.hasSelection()) {
					Ext.Msg.alert('Внимание','Не выбрано ни одной версии документа.');
					return false;
				}
				
				Ext.create('common.BSME.tools.swBSMEXmlVersionsViewWindow',{
					EvnXml_id: selectionModel.getSelection()[0].get('EvnXml_id')
				})
			}
		});
		
		win.printButton = Ext.create('Ext.button.Button',{
			xtype: 'button',
			text: 'Печать',
			handler: function(){
				var selectionModel = win.grid.getSelectionModel();
				if (!selectionModel.hasSelection()) {
					Ext.Msg.alert('Внимание','Не выбрано ни одной версии документа.');
					return false;
				}
				
				Ext.Ajax.request({
					url: '/?c=EvnXml&m=doPrint',
					params: {
						EvnXml_id: selectionModel.getSelection()[0].get('EvnXml_id')
					},
					callback: function(opt, success, response){
						if ( !success ) {
							Ext.Msg.alert('Ошибка','Во время загрузки печатной формы произошла ошибка.');
							return;
						}
						var win = window.open();
						win.document.write(response.responseText);
						
						
					}
				});
			}
		});
		
		win.cancelButton = Ext.create('Ext.button.Button', {
			xtype: 'button',
			iconCls: 'cancel16',
			text: 'Закрыть',
			handler: function(){
				win.close()
			}
		})
								
//		win.helpButton = Ext.create('Ext.button.Button', {
//			xtype: 'button',
//			text: 'Помощь',
//			margin: '0 5 0 0',
//			iconCls   : 'help16',
//			handler   : function()
//			{
//				//ShowHelp(this.ownerCt.title);
//			}
//		})
								
		
		Ext.apply(win,{
			autoScroll: true,
			items: [
				win.grid
			],
			buttons: [
				win.showButton,
				win.printButton,
				'->',
//				win.helpButton,
				win.cancelButton
			]
		})

        this.callParent(arguments);
	}

});
