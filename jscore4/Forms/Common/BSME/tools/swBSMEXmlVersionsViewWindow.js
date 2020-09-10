
Ext.define('common.BSME.tools.swBSMEXmlVersionsViewWindow', {
	extend: 'Ext.window.Window',
	minHeight: 500,
	title: 'Просмотр документа',
	modal: true,
	autoShow: true,
	width: '50%',
	height: '80%',
	refId: 'swBSMEXmlVersionsViewWindow',
	closable: true,
	id: 'swBSMEXmlVersionsViewWindow',
	border: false,
	layout: {
        type: 'fit'
    },
	EvnXml_id: null,
	show: function() {
		
		this.callParent(arguments);
		
		if (!this.EvnXml_id) {
			Ext.Msg.alert('Ошибка', 'Не передан идентификатор случая');
			this.close();
		}
		
		this.loadXmlPanel(this.EvnXml_id);
		
	},
	loadXmlPanel: function(EvnXml_id) {
		var win = this;
		if (!EvnXml_id) {
			Ext.Msg.alert('Ошибка', 'Не передан идентификатор заключения');
			win.close();
			return false;
		}
		
		var loadMask = new Ext.LoadMask(win.XmlPanel, {msg:"Пожалуйста, подождите, идёт получение данных об экспертизе..."}); 
			loadMask.show();
			
		Ext.Ajax.request({
			params: {
				EvnXml_id: EvnXml_id
			},
			url: '/?c=EvnXml&m=loadEvnXmlForm', 
			callback: function(params,success,result) {

				var resp = Ext.JSON.decode(result.responseText, true);
				if (resp === null || result.status !== 200) {
					loadMask.hide();
					Ext.Msg.alert('Ошибка', 'Ошибка обработки запроса');
					return false;
				}
				
				win.updateXmlPanel(resp)
				
				loadMask.hide();
			}
		});
	},
	updateXmlPanel: function(data) {
		
		if (!data || !data['html'] || !data['formData']) {
			return false;
		}
		
		var me = this,
			panel = me.XmlPanel,
			html = data['html'],
			fieldParams = {},
			key, 
			value, 
			i, 
			value_indexes,
			item,
			formData = data['formData'];
		
		
		for (key in formData) {
			fieldParams = formData[key];
			value = '';
			switch (fieldParams.type) {
				case 'combobox':
				case 'checkboxgroup':
				case 'radiogroup':
					if (!fieldParams.value) {
						value = fieldParams.value;
					} else {
						
						value_indexes = (fieldParams.value+'').split(',');
						
						for (i=0; i<fieldParams.items.length; i++) {
							item = fieldParams.items[i];
							if (value_indexes.indexOf(item.id) != -1) {
								value += item.fieldLabel+'; ';
							}
						}
					}
					break;
				
				case 'textarea':
				default:
					value = fieldParams.value;
					break;	
			}
			html = html.replace('{'+ fieldParams.name + '}' , '<div class = "'+ fieldParams.name + '" > '+ value + ' </div>');
		}
		
		panel.update(html);
		panel.doLayout();
		
	},
	initComponent: function() {
		var win = this;
		
		win.XmlPanel  = Ext.create('Ext.panel.Panel',{ });
		
		win.showButton = Ext.create('Ext.button.Button',{
			xtype: 'button',
			margin: '0 5 0 0',
			text: 'Просмотр',
			handler: function(){
				
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
								
		Ext.apply(win,{
			items: [
				
				{
					height: '100%',
					autoScroll: true,
					xtype: 'panel',

					items: [
						win.XmlPanel
					]
				}
				
				
				
				
			],
			buttons: [
				'->',
				win.cancelButton
			]
		})

        this.callParent(arguments);
	}

});
