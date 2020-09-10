/* 
 * РћРєРЅРѕ СЂРµРґР°РєС‚РёСЂРѕРІР°РЅРёСЏ РґРµСЂРµРІР° СЂРµС€РµРЅРёР№ РґР»СЏ РґРёСЃРїРµС‚С‡РµСЂР° РІС‹Р·РѕРІРѕРІ
 */

Ext.define('common.HeadDutyWP.tools.swDecigionTreeEditWindow', {
//    extend: WINDOWS_ALIAS['swPromedBaseForm'],
	extend: 'Ext.window.Window',
    autoShow: true,
	maximized: true,
	refId : 'DecigionTreeEditWindow',
	id:  'DecigionTreeEditWindow',
	title: 'Р РµРґР°РєС‚РёСЂРѕРІР°РЅРёРµ РґРµСЂРµРІР° СЂРµС€РµРЅРёР№ РґР»СЏ РґРёСЃРїРµС‚С‡РµСЂР° РІС‹Р·РѕРІРѕРІ',
	renderTo: Ext.getCmp('inPanel').body,
	constrain: true,
	closable: true,
	//Р Р°Р·РґРµР»РёС‚РµР»СЊ РґР»СЏ С‚РµРєСЃС‚Р° РѕС‚РІРµС‚Р° Рё РєРѕРґР° РїРѕРІРѕРґР°, РµСЃР»Рё РѕРЅ СѓРєР°Р·Р°РЅ
	//РџСЂ-СЂ: Р°СЂС‚РµСЂРёР°Р»СЊРЅРѕРµ РєСЂРѕРІРѕС‚РµС‡РµРЅРёРµ -> !01 
	TextFied_Delimiter: ' -> ',
	//Р РµР¶РёРј: РґРѕР±Р°РІР»РµРЅРёСЏ/СЂРµРґР°РєС‚РёСЂРѕРІР°РЅРёСЏ
	mode:'',
	btns:{},
	buttonAlign : "left",
	layout: {
        type: 'fit'
    },
	onCancel: Ext.emptyFn,
	initComponent: function() {
		
		this.createBtn = Ext.create('Ext.button.Button',{
			text	: 'Р”РѕР±Р°РІРёС‚СЊ',
			tabIndex  : -1,
//			iconCls   : 'help16',
			handler   : function() {
				var node = this.getCurrentTreeNode(),
					nodeData;
					
				
				if (!node) {
					return false;
				}
				
				this.mode = 'create';
				
				nodeData = node.data;
				
				this.TreePanel.disable();
				this.FormPanel.enable();
				
				this.FormPanel.getForm().findField('CmpReason_id').setDisabled(nodeData['AmbulanceDecigionTree_Type']!=1)
				this.FormPanel.getForm().findField('TreeNode_Text').focus();
				
			}.bind(this)
		});
		
		this.deleteBtn = Ext.create('Ext.button.Button',{
			text	: 'РЈРґР°Р»РёС‚СЊ',
			tabIndex  : -1,
			handler   : function() {
				var selectedNode = this.getCurrentTreeNode();
				
				this.TreePanel.getSelectionModel().select(selectedNode.parentNode);
				if (selectedNode) {
					selectedNode.remove();
				} else {
					return false;
				}
			}.bind(this)
		});
		
		this.updateBtn = Ext.create('Ext.button.Button',{
			text	: 'Р РµРґР°РєС‚РёСЂРѕРІР°С‚СЊ',
			tabIndex  : -1,
			handler   : function() {
				var selectedNode = this.getCurrentTreeNode();
				
				if (!selectedNode) {
					return false;
				}
				
				this.mode = 'edit';
				
				var nodeData = selectedNode.data,
					form = this.FormPanel.getForm();
				
				this.TreePanel.disable();
				this.FormPanel.enable();
				
				form.findField('CmpReason_id').setDisabled(nodeData['AmbulanceDecigionTree_Type']!=2);
				this.FormPanel.getForm().findField('TreeNode_Text').focus();
				
				//РќР° СЃР»СѓС‡Р°Р№, РµСЃР»Рё РїРѕР»СЊР·РѕРІР°С‚РµР»СЊ РІРІРµРґС‘С‚ СЃРѕР±СЃС‚РІРµРЅРЅС‹Рµ СЃРёРІРѕР»С‹, СЃРѕРІРїР°РґР°СЋС‰РёРµ СЃ СЂР°Р·РґРµР»РёС‚РµР»РµРј
				
				var text = nodeData['AmbulanceDecigionTree_Text'];
				
				//РћС‚РґРµР»СЏРµРј С‚РµРєСЃС‚ РѕС‚РІРµС‚Р° РѕС‚ РєРѕРґР°, РµСЃР»Рё РєРѕРґ РµСЃС‚СЊ
				if (nodeData['CmpReason_id']) {
					var pos = text.indexOf(this.TextFied_Delimiter);
					while ( text.indexOf(this.TextFied_Delimiter,pos+1) != -1 ) {
					   pos = text.indexOf(this.TextFied_Delimiter,pos+1);
					}
					
					text = text.substr(0,pos)
				}
				
				if (nodeData['AmbulanceDecigionTree_Type']==2 && nodeData['CmpReason_id'])  {
					form.findField('CmpReason_id').setValue(nodeData['CmpReason_id']);
				}
				form.findField('TreeNode_Text').setValue(text);
			}.bind(this)
		});
		
		this.expandAllBtn = Ext.create('Ext.button.Button',{
			text	: 'Р Р°Р·РІРµСЂРЅСѓС‚СЊ РІСЃРµ',
			tabIndex  : -1,
			handler   : function() {
				this.TreePanel.expandAll();
			}.bind(this)
		});
		
		this.collapseAllBtn = Ext.create('Ext.button.Button',{
			text	: 'РЎРІРµСЂРЅСѓС‚СЊ РІСЃРµ',
			tabIndex  : -1,
			handler   : function() {
				this.TreePanel.collapseAll();
			}.bind(this)
		});
		
		
		this.TreePanel = Ext.create('Ext.tree.Panel',{
			flex:1,
			animate: false,
			rootVisible: true,
			id: this.id+'_TreePanel',
			title: 'Р”РµСЂРµРІРѕ СЂРµС€РµРЅРёР№',
			displayField: 'AmbulanceDecigionTree_Text',
			listeners: {
				cellkeydown: function( panel, td, cellIndex, record, tr, rowIndex, e, eOpts ){
					if (e.getKey() == e.ENTER) {
						this.updateBtn.handler()
					}
					if (e.getKey() == e.INSERT) {
						this.createBtn.handler()
					}
					if (e.getKey() == e.DELETE) {
						this.deleteBtn.handler()
					}
				}.bind(this)
			},
			store: Ext.create('Ext.data.TreeStore',{
				idProperty: 'AmbulanceDecigionTree_id',
				fields: [
					{name: 'AmbulanceDecigionTree_id', type: 'int'},
					{name: 'AmbulanceDecigionTree_nodeid', type: 'int'},
					{name: 'AmbulanceDecigionTree_nodepid', type: 'int'},
					{name: 'AmbulanceDecigionTree_Type', type: 'int'}, //1 - РІРѕРїСЂРѕСЃ, 2 - РѕС‚РІРµС‚
					{name: 'AmbulanceDecigionTree_Text', type: 'string'},
					{name: 'CmpReason_id', type: 'int'},
					{name: 'leaf'}
					
				],
				root:{
					leaf: false,
					expanded: true
				},
				autoLoad:false,
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=CmpCallCard&m=getDecigionTree',
					reader: {
						type: 'json'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				},
				listeners: {
					'load':function(store, node, records, successful, eOpts) {
						this.TreePanel.setRootNode(this.TreePanel.getRootNode().childNodes[0]);
						this.TreePanel.getSelectionModel().select(this.TreePanel.getRootNode());

					}.bind(this),
					'beforeappend': function( store, node, eOpts ) {
						if (node&&node['data']&&node['data']['AmbulanceDecigionTree_Type']) {
							node['data']['iconCls']='decigiontreeeditwindow-tree-icon-'+((node['data']['AmbulanceDecigionTree_Type'].toString()==='1')?'question':'answer');
						}
					}
				}
			}),
			dockedItems: [{
				xtype: 'toolbar',
				items:[
					this.createBtn,
					this.deleteBtn,
					this.updateBtn,
					this.expandAllBtn,
					this.collapseAllBtn
				]
			}]

		});
		
		this.TreePanel.on('selectionchange',function(panel,selection,opts) {
			if (selection && selection[0] && selection[0].data) {
				this.createBtn.setDisabled(!!selection[0].data['CmpReason_id']);
			}
		}.bind(this));
		
		this.saveNodeBtn = Ext.create('Ext.button.Button',{
			text: 'РЎРѕС…СЂР°РЅРёС‚СЊ',
			handler: function(){
				var form = this.FormPanel.getForm(),
				
					rc = form.findField('CmpReason_id'),//ReasonCombo
					
					currentNodeData = this.getCurrentTreeNode().data;
					
				
				
				var modifyDataText = function(data) { 
					if (data['AmbulanceDecigionTree_Type']==2 && data['CmpReason_id']) {
						var record = rc.getStore().findRecord(rc.valueField,data['CmpReason_id']);
						if (record) {
							data['AmbulanceDecigionTree_Text'] += this.TextFied_Delimiter+record.data['CmpReason_Code'];
						}
					}
					return data;
				}.bind(this)
				
				switch (this.mode) {
					case 'create':
						var data = {
							AmbulanceDecigionTree_id: null,
							AmbulanceDecigionTree_nodepid: this.getCurrentTreeNode().data['AmbulanceDecigionTree_nodeid'],
							AmbulanceDecigionTree_nodeid:  this.getTreeStoreLastNodeId()+1,
							AmbulanceDecigionTree_Type: currentNodeData['AmbulanceDecigionTree_Type']%2+1,//(form.findField('nodeType').getValue()==1)?2:1,
							AmbulanceDecigionTree_Text: form.findField('TreeNode_Text').getValue(),
							CmpReason_id: (currentNodeData['AmbulanceDecigionTree_Type']==1)?rc.getValue():null,
							iconCls: ('decigiontreeeditwindow-tree-icon-'+((currentNodeData['AmbulanceDecigionTree_Type']==1)?'answer':'question')),
							expanded: true
						}
						data = modifyDataText(data);
						this.getCurrentTreeNode().appendChild([
							data
						]);
						this.getCurrentTreeNode().expand();
						break;
					case 'edit':
						var node = this.getCurrentTreeNode(),
							data = node.data;
						data['CmpReason_id'] = rc.getValue();
						data['AmbulanceDecigionTree_Text'] = form.findField('TreeNode_Text').getValue();
						data = modifyDataText(data);
						node.set('CmpReason_id',data['CmpReason_id']);
						node.set('AmbulanceDecigionTree_Text',data['AmbulanceDecigionTree_Text']);
						node.commit();
						break;
					default:
						break;
				}
				
				
				this.TreePanel.enable();
				this.FormPanel.disable();
				
				this.FormPanel.getForm().findField('CmpReason_id').clearValue();
				this.FormPanel.getForm().findField('TreeNode_Text').reset();
				this.setFocusOnNode()
				
			}.bind(this)
		});
		
		this.cancelNodeCreatingBtn = Ext.create('Ext.button.Button',{
			text: 'РћС‚РјРµРЅРёС‚СЊ',
			handler: function(){
				this.FormPanel.getForm().findField('CmpReason_id').clearValue();
				this.FormPanel.getForm().findField('TreeNode_Text').reset();
				
				this.TreePanel.enable();
				this.FormPanel.disable();
				//РљРѕСЃС‚С‹Р»СЊ РґР»СЏ СѓСЃС‚Р°РЅРѕРІРєРё С„РѕРєСѓСЃР° РЅР° РЅРѕРґСѓ РґРµСЂРµРІР° РґР»СЏ РґР°Р»СЊРЅРµР№С€РµРіРѕ СѓРїСЂР°РІР»РµРЅРёСЏ СЃ РєР»Р°РІРёР°С‚СѓСЂС‹
				this.setFocusOnNode()
				
			}.bind(this)
		});
		
		this.FormPanel = Ext.create('Ext.form.Panel',{
			disabled: true,
			columnWidth: .5,
			id: this.id+'_BaseForm',
			frame: true,
			border: false,
			buttonAlign: 'left',
			layout: {
					padding: '5 20',
					align: 'stretch',
					type: 'vbox'
			},
			xtype: 'form',
			items:[
			{
				xtype: 'BaseForm',
				buttons: [
					this.saveNodeBtn,
					this.cancelNodeCreatingBtn
				],
				layout: {
					padding: '5 20',
					align: 'stretch',
					type: 'vbox'
				},
				border: false,
				items:[
				{
					xtype: 'textfield',
					fieldLabel: 'РўРµРєСЃС‚',
					name: 'TreeNode_Text',
					maxLength: 255,
					maxLengthText: 'РњР°РєСЃРёРјР°Р»СЊРЅРѕРµ РєРѕР»РёС‡РµСЃС‚РІРѕ СЃРёРјРІРѕР»РѕРІ - 255'
				},
				{
					xtype: 'cmpReasonCombo',
					name: 'CmpReason_id'
				}
				]
				
				
//				id: 'mainForm',
//				items: [
//				{
//					xtype:'fieldset',
//					layout: {
//						padding: '5 20',
//						align: 'stretch',
//						type: 'vbox'
//					},
//					border: false,
//					items:[
////						{
////							xtype: 'hidden',
////							name: 'nodeType' //1 - РІРѕРїСЂРѕСЃ, 2 - РѕС‚РІРµС‚
////						},
//						{
//							xtype: 'textfield',
//							fieldLabel: 'РўРµРєСЃС‚',
//							name: 'TreeNode_Text'
//						},
//						{
//							xtype: 'cmpReasonCombo',
//							name: 'CmpReason_id'
//						}
////						{	
////							
////							//@TODO: РџСЂРѕР±Р»РµРјР° СЃ РІС‹Р±РѕСЂРѕРј РїРѕ ENTER-Сѓ
////							disabled: true,
////							xtype:'combo',
////							disabledClass: 'field-disabled',
////							fieldLabel: 'РџРѕРІРѕРґ',
////							id: this.id+'_CmpReason',
////							allowBlank: false,
////							name: 'CmpReason_id',
////							width: 250,
////							plugins: [new Ux.Translit(true, true)],
////							valueField: 'CmpReason_id',
////							codeField: 'CmpReason_Code',
////							displayField: 'CmpReason_Name',
////							tpl: '<tpl for="."><div class="x-boundlist-item">' +
////								'<font color="red">{CmpReason_Code}</font>&nbsp;{CmpReason_Name}' +
////								'</div></tpl>',
////							displayTpl: '<tpl for="."> {CmpReason_Code}. {CmpReason_Name} </tpl>',
//////							queryMode: 'local',
//////							editable: true,
//////							enableKeyEvents : true,
//////							labelAlign: 'right',
////							store: Ext.create('swMongoComboStore',{
//////												xtype: 'swmongocombostore',
////								comboSubject: 'CmpReason',
////								filter: Ext.emptyFn,
////								clearFilter: Ext.emptyFn,
////								reasonFilter: function (reason_Code) {
////									Ext.data.Store.prototype.clearFilter.call(this);
////									Ext.data.Store.prototype.filter.call(this, 'CmpReason_Code', reason_Code);
////								}
////							}),
////							listeners: {
////								change: function(c, newV, oldV, o){
////									//Р­С‚Рѕ СЂР°Р±РѕС‚Р°РµС‚ РЅРµ С‚Р°Рє РєР°Рє РЅР°РґРѕ
//////									if (newV && oldV){
//////										c.store.reasonFilter(newV)
//////										if (c.store.count() == 1){
//////											var rec = c.store.findRecord('CmpReason_Code', newV)
//////											if (rec){c.setValue(rec.get('CmpReason_id'))}
//////										}
//////									}
//////									else{
//////										var rec = c.store.findRecord('CmpReason_id', newV)
//////										if (rec){c.setValue(rec.get('CmpReason_id'))}
//////									}
////								}
////							}
////						}
//
//					]
//				}
//			]
		}
		],
//			buttons: [
//				this.saveNodeBtn,
//				this.cancelNodeCreatingBtn
//			]
		});
		
		//Р”РѕР±Р°РІРёРј Р·РґРµСЃСЊ, РЅР° СЃР»СѓС‡Р°Р№ РµСЃР»Рё layout РёР·РјРµРЅРёС‚СЃСЏ
		Ext.applyIf(this,{
			items:[
				{
					xtype: 'panel',
					defaults: {
						border: false
					},
					layout: {
						type: 'hbox',
						pack: 'start',
						align: 'stretch'
					},
					items:[
						{
							flex: 1,
//							height: '100%',
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: this.TreePanel
						},
						{
							flex: 1,
							items:[
								this.FormPanel
							]
						}
					]
				}
			]
		})
		
		
		var wnd = this;
		this.btns = {
			'save' : Ext.create('Ext.button.Button',{
				text      : BTN_FRMSAVE,
				tabIndex  : -1,
				tooltip   : 'РЎРѕС…СЂР°РЅРёС‚СЊ РґР°РЅРЅС‹Рµ',
				iconCls   : 'save16',
				type      : 'submit',
				disabled  : false,
				handler  : function() {
					if (typeof wnd.onSave == 'function') {
						wnd.onSave(function(close){
							if (close) {
								wnd.close();
							}
						})
					} else {
						wnd.close();
					}
				}
			}),
			'cancel':Ext.create('Ext.button.Button',{
				text      : 'РћС‚РјРµРЅРёС‚СЊ',
				tabIndex  : -1,
				tooltip   : 'РћС‚РјРµРЅРёС‚СЊ СЃРѕС…СЂР°РЅРµРЅРёРµ',
				iconCls   : 'cancel16',
				handler   : function() {
					
					wnd.close();
				}
			}),
			'help':Ext.create('Ext.button.Button',{
				text	: BTN_FRMHELP,
				tabIndex  : -1,
				tooltip   : BTN_FRMHELP_TIP,
				iconCls   : 'help16',
				handler   : function() {
					ShowHelp(wnd.title);
				}
			})
		}
			
		Ext.applyIf(this,{
			buttons:[
				this.btns.save,
				'->',
				this.btns.cancel,
				this.btns.help
			]
		});
		
		
		
		
		this.callParent(arguments);
	},
	show: function() {
		this.callParent(arguments);
	},
	getCurrentTreeNode: function() {
		var selection = this.TreePanel.getSelectionModel().getSelection();
		if ((selection instanceof Array)&&selection[0]) {
			return selection[0];
		} else {
			return false;
		}
	},
	getTreeStoreLastNodeId: function() {
		var returnFunc = function(node) {
			for (key in node.childNodes) {
				returnFunc.lastId = (returnFunc.lastId<node.childNodes[key].data['AmbulanceDecigionTree_nodeid'])?node.childNodes[key].data['AmbulanceDecigionTree_nodeid']:returnFunc.lastId;
				returnFunc(node.childNodes[key]);
			}
		}.bind(this);
		
		returnFunc.lastId = this.TreePanel.getRootNode().data['AmbulanceDecigionTree_nodeid'];
		returnFunc(this.TreePanel.getRootNode());
		return returnFunc.lastId;
		
	},
	onSave: function(cb) {
		var data = [],
			collectData = function(node) {
			data.push(node.data);
			for (key in node.childNodes) {
				collectData(node.childNodes[key]);
			}
		}
		collectData(this.TreePanel.getRootNode());

		var loadMask = new Ext.LoadMask(this, {msg:"Р?РґРµС‚ СЃРѕС…СЂР°РЅРµРЅРёРµ РґРµСЂРµРІР° СЂРµС€РµРЅРёР№. РџРѕР¶Р°Р»СѓР№СЃС‚Р°, РїРѕРґРѕР¶РґРёС‚Рµ..."}).show();
		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=saveDecigionTree',
			timeout:360000,
			callback: function(opt, success, response) {
				if (success){
					var response_obj = Ext.JSON.decode(response.responseText);
					if (!response_obj['success']) {
						Ext.Msg.show({
							 title:'РћС€РёР±РєР°',
							 msg: response_obj['Error_Msg'],
							 buttons: Ext.Msg.OK,
							 icon: Ext.Msg.WARNING
						});
						cb(false);
					} else {
						cb(true);
					}
					loadMask.hide();
				} else {
					loadMask.hide();
				}
			}.bind(this),
			params: {
				data: Ext.JSON.encode(data)
			}
		})
		
	},
	setFocusOnNode: function(){
		var curNode = this.getCurrentTreeNode();
				
		if (curNode.parentNode) {
			this.TreePanel.getSelectionModel().select(curNode.parentNode)
		} else if (curNode.childNodes.length) {
			this.TreePanel.getSelectionModel().select(curNode.childNodes[0]);
		} else {
			//РЎР°РјС‹Р№ СѓР¶Р°СЃРЅС‹Р№ СЃР»СѓС‡Р°Р№ - РµСЃР»Рё РѕСЃС‚Р°Р»СЃСЏ РѕРґРёРЅ СЌР»РµРјРµРЅС‚ - РєРѕСЂРЅРµРІРѕР№
			curNode.appendChild({});
			this.TreePanel.getSelectionModel().select(curNode.childNodes[0]);
			this.TreePanel.getSelectionModel().select(curNode);
			curNode.childNodes[0].remove()
		}
		this.TreePanel.getSelectionModel().select(curNode);
	}
});
