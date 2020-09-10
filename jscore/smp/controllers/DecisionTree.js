/**
 * Контроллер дерева решений
 */
Ext6.define('smp.controllers.DecisionTree', {
    extend: 'Ext6.app.Controller',

	views: [
		'smp.views.decisionTree.edit.Window',
		'smp.views.decisionTree.edit.Form',
		'smp.views.decisionTree.Tree',
		'smp.views.decisionTree.StructuresTree',
		'smp.views.decisionTree.FormCopyTree'
	],

	refs: [
		{
			ref: 'editWindow',
			selector: '[xtype=decisionTree.edit.window]'
		},
		{
			ref: 'formPanel',
			selector: '[xtype=decisionTree.edit.form]'
		},
		{
			ref: 'structuresTree',
			selector: '[xtype=decisionTree.structuresTree]'
		},
		{
			ref: 'tree',
			selector: '[xtype=decisionTree.tree]'
		},
		{
			ref: 'formCopyTree',
			selector: '[xtype=decisionTree.formCopyTree]'
		}
	],
	
    init: function(){
    	var me = this;
		this.listen({
			component: {
				// Окно редактирования дерева решений
				'window[xtype=decisionTree.edit.window]':{
					show:function (cmp) {
						var formReadOnly = cmp.params.readOnly;
						cmp.down('button#create').setHidden(formReadOnly);
						cmp.down('button#update').setHidden(formReadOnly);
						cmp.down('button#delete').setHidden(formReadOnly);
						me.getFormPanel().setHidden(formReadOnly);
					}
				},
				'window[xtype=decisionTree.edit.window] button#create': {
					click: this.onEditWindowButtonCreateClick
				},
				'window[xtype=decisionTree.edit.window] button#update': {
					click: this.onEditWindowButtonUpdateClick
				},
				'window[xtype=decisionTree.edit.window] button#delete': {
					click: this.onEditWindowButtonDeleteClick
				},
				'window[xtype=decisionTree.edit.window] button#collapse-all': {
					click: this.onEditWindowButtonCollapseAllClick
				},
				'window[xtype=decisionTree.edit.window] button#expand-all': {
					click: this.onEditWindowButtonExpandAllClick
				},
				'window[xtype=decisionTree.edit.window] > toolbar > button#save': {
					click: this.onEditWindowButtonSaveClick
				},
				'window[xtype=decisionTree.edit.window] > toolbar > button#cancel': {
					click: this.onEditWindowButtonCancelClick
				},
				
				// Структура дерева решений
				'treepanel[xtype=decisionTree.tree]': {
					selectionchange: this.onTreeSelectionChange,
					itemdblclick: this.onEditWindowButtonUpdateClick
				},
				
				// Форма редактирования дерева решений
				'window[xtype=decisionTree.edit.window] form[xtype=decisionTree.edit.form] button#save': {
					click: this.onEditFormButtonSaveClick
				},
				'window[xtype=decisionTree.edit.window] form[xtype=decisionTree.edit.form] button#cancel': {
					click: this.onEditFormButtonCancelClick
				},
				'window[xtype=decisionTree.edit.window] form[xtype=decisionTree.edit.form] [xtype=ambulanceDecigionTreeType]': {
					change:function ( cmp, newValue, oldValue, eOpts ) {
						var CmpReason = cmp.up('form').getForm().findField('CmpReason_id');
						if(newValue===1){
							CmpReason.reset();
						}
						CmpReason.setDisabled(newValue===1);
					}
				},
				'window[xtype=decisionTree.structuresTree]': {
					show:function () {
						var smpAdminRegion = isUserGroup('smpAdminRegion'),
							structuresTreePanel = me.getStructuresTree();

						structuresTreePanel.down('#toolbarTreeEdit').setHidden(!smpAdminRegion);
					}
				},
				'window[xtype=decisionTree.structuresTree] #createTree': {
					click:function () {
						var record = me.getStructuresTree().down('#structuresTreePanel').getSelection()[0],
							data = {
								Lpu_id: record.get('Lpu_id'),
								LpuBuilding_id: record.get('LpuBuilding_id')
							};

						me.createTree(data);
					}
				},
				'window[xtype=decisionTree.structuresTree] #updateTree': {
					click:function () {
						var record = me.getStructuresTree().down('#structuresTreePanel').getSelection()[0],
							data = {
								Lpu_id: record.get('Lpu_id'),
								LpuBuilding_id: record.get('LpuBuilding_id')
							};
						me.showCopyTreeWindow(data);

					}
				},
				'window[xtype=decisionTree.structuresTree] #structuresTreePanel': {
					select ( cmp, record, index, eOpts ){
						var issetTree =  record.get('issetTree') === 'true',
							structuresTreePanel = me.getStructuresTree() ;

						structuresTreePanel.down('#createTree').setDisabled(issetTree);
						structuresTreePanel.down('#updateTree').setDisabled(issetTree);
					},
					itemdblclick:function( cmp, record, item, index, e){
						if(record.get('issetTree') === 'true'){
							var data = {
								AmbulanceDecigionTreeRoot_id: record.get('AmbulanceDecigionTreeRoot_id')
							};

							if(isUserGroup('smpAdminRegion')){
								data.readOnly= record.get('depth')===1
							}else{
								data.readOnly = record.get('depth').inlist([1,2])
							}

							me.showEditWindow(data);
						}
					},
				},
				'window[xtype=decisionTree.formCopyTree]':{
					show: function (cmp) {
						this.getStructuresTree().mask();
					},
					close: function () {
						this.getStructuresTree().unmask();
					}
				},
				'window[xtype=decisionTree.formCopyTree] [xtype=ambulanceTreeLevel]': {
					change:function (cmp, newValue, oldValue, eOpts )  {
						var structuresLevel = cmp.up('form').query('[xtype=getStructuresLevel]')[0];

						structuresLevel.store.getProxy().extraParams.level = newValue;
						structuresLevel.store.load()
					}
				}, 
				'window[xtype=decisionTree.formCopyTree] button#selectTree':{
					click:function (cmp, e) {
						var formCopyTree = this.getFormCopyTree(),
							selectedTree = formCopyTree.query('[xtype=getStructuresLevel]')[0],
							params={
								AmbulanceDecigionTreeRoot_id: selectedTree.getValue(),
								LpuBuilding_id: formCopyTree.params.LpuBuilding_id,
								Lpu_id : formCopyTree.params.Lpu_id
							};

						if (!formCopyTree.down('form').isValid()) {
							Ext6.Msg.alert(langs('Ошибка заполнения формы'), langs('Проверьте правильность заполнения полей формы.'));
							return false;
						}

						Ext6.Ajax.request({
							url: '/?c=CmpCallCard&m=copyDecigionTree',
							params: params,
							success: function(response,options){
								Ext6.Msg.alert('Элемент сохранен');
								me.getStructuresTree().down('#structuresTreePanel').store.reload();
							},
							failure: function(response,options){
								Ext6.Msg.alert('Ошибка','Во время сохранения возникла непредвиденная ошибка.');
							}
						});
					}
				}
			},
			store: {
				'#DecisionTree': {
					load: this.onDecisionTreeStoreLoad
				}
			}
		});
		
		this.on('onAfterDecisionTreeSave', this.afterDecisionTreeSave);
    },
	
	/**
	 * Вызов окна редактирования дерева решений
	 */
	showEditWindow: function(data){
		var win = Ext6.widget('decisionTree.edit.window',{
			params: data
		});
		win.show();
	},

	/**
	 * Создание нового дерева
	 * */
	createTree:function(data){
		var me = this;
		Ext6.Ajax.request({
			url: '/?c=CmpCallCard&m=createDecigionTree',
			params: data,
			success: function(response,options){
				if(response.responseText){
					var msg = Ext6.Msg.alert('Сообщение', 'Элемент сохранен');
					me.getStructuresTree().down('#structuresTreePanel').store.reload();
					Ext6.defer(function(){msg.close()}, 1000);
				}
			},
			failure: function(response,options){
				Ext6.Msg.alert('Ошибка','Во время сохранения возникла непредвиденная ошибка.');
			}
		});
	},

	/**
	 * Вызов структуры дерева решений
	 */
	showStucturesWindow: function(){
		Ext6.widget('decisionTree.structuresTree').show();
	},

	showCopyTreeWindow:function(data){
		Ext6.widget('decisionTree.formCopyTree',{
			params: data
		}).show();
	},
	
	/**
	 * Событие смены выбора элемента дерева решений
	 * 
	 * @param object selection Ext.selection.Model
	 * @param object selected Ext.data.Model[]
	 * @param object eOpts
	 * @returns void
	 */
	onTreeSelectionChange: function(selection, selected, eOpts){
		if (!selection.hasSelection()) {
			return;
		}
		this.getEditWindow().query('button#create')[0].setDisabled(!!selected[0].data['CmpReason_id']);
	},
	
	/**
	 * Событие клика по кнопке сохранения в форме редактирования дерева решений
	 */
	onEditFormButtonSaveClick: function(btn, e, eOpts){
		var selected_node = this.getTree().getSelectedTreeNode();
		if (selected_node === null) {
			Ext6.Msg.alert('Предупреждение','Произошла непредвиденная ошибка, выбранная запись не найдена. Отмените редактирование и попробуйте заново.');
			return false;
		}
		
		var win = this.getEditWindow(),
			form_panel = btn.up('form'),
			window = btn.up('window'),
			form = form_panel.getForm(),
			AmbulanceDecigionTree_id = form.findField('AmbulanceDecigionTree_id').getValue(),
			TreeNode_Text = form.findField('TreeNode_Text'),
			AmbulanceDecigionTree_Type = form.findField('AmbulanceDecigionTree_Type'),
			CmpReason_id = form.findField('CmpReason_id'),
			action = AmbulanceDecigionTree_id ? 'update' : 'create',
			type = AmbulanceDecigionTree_Type.getValue(),
			text = TreeNode_Text.getValue();


		if(type === 2 && selected_node.childNodes.length!==0 && action === 'update'){
			Ext6.Msg.alert('Ошибка','Элемент содержит дочерние значения. Изменение типа невозможно.');
			return false;
		}

		if (!form_panel.isValid()) {
			Ext6.Msg.alert('Ошибка', ERR_INVFIELDS_MSG);
			return false;
		}

		// Имя ветки в дереве, скомбинированное с поводом

		if (type === 2 && CmpReason_id.getValue()) {
			var combo_reason_record = CmpReason_id.findRecordByValue(CmpReason_id.getValue());
			text += this.getFormPanel().text_delimiter + combo_reason_record.get('CmpReason_Code');
		}

		var nodeParams = {
			AmbulanceDecigionTree_Type: type,
			AmbulanceDecigionTree_Text: text,
			CmpReason_id: CmpReason_id.getValue(),
			AmbulanceDecigionTreeRoot_id: selected_node.get('AmbulanceDecigionTreeRoot_id'),
			//Для подержки старого функицонала
			Lpu_id: window.params.Lpu_id,
			LpuBuilding_id : window.params.LpuBuilding_id,
		};

		if (action === 'create') {
			nodeParams.AmbulanceDecigionTree_id = null;
			nodeParams.AmbulanceDecigionTree_nodepid= selected_node.get('AmbulanceDecigionTree_nodeid');
			nodeParams.AmbulanceDecigionTree_nodeid= (this.getTree().getLastTreeNodeId() + 1);
		}else{
			nodeParams.AmbulanceDecigionTree_id = selected_node.get('AmbulanceDecigionTree_id');
			nodeParams.AmbulanceDecigionTree_nodepid = selected_node.get('AmbulanceDecigionTree_nodepid');
			nodeParams.AmbulanceDecigionTree_nodeid = selected_node.get('AmbulanceDecigionTree_nodeid');
		}

		//сохранение 1 записи
		Ext6.Ajax.request({
			url: '/?c=CmpCallCard&m=saveDecigionTreeNode',
			params: nodeParams,
			success: function(response,options){
				if(response.responseText){
					var resp = Ext6.JSON.decode(response.responseText);
					if (!resp['success']) {
						Ext6.Msg.alert('Ошибка',resp['Error_Msg']);
					}else{
						switch (action) {
							case 'create':
								var nodeData = {
									AmbulanceDecigionTree_id: resp.AmbulanceDecigionTree_id,
									AmbulanceDecigionTree_nodepid: nodeParams.AmbulanceDecigionTree_nodepid,
									AmbulanceDecigionTree_nodeid: nodeParams.AmbulanceDecigionTree_nodeid,
									AmbulanceDecigionTree_Type: nodeParams.AmbulanceDecigionTree_Type,
									AmbulanceDecigionTree_Text: nodeParams.AmbulanceDecigionTree_Text,
									CmpReason_id: nodeParams.CmpReason_id,
									leaf: true
								};

								selected_node.set('leaf', false);
								selected_node.appendChild(nodeData);
								selected_node.expand();
								break;

							case 'update':

								selected_node.set('CmpReason_id', nodeParams.CmpReason_id);
								selected_node.set('AmbulanceDecigionTree_Type', nodeParams.AmbulanceDecigionTree_Type);
								selected_node.set('AmbulanceDecigionTree_Text', nodeParams.AmbulanceDecigionTree_Text);
								selected_node.commit();
								break;
						}
					}
					var msg = Ext6.Msg.alert('Сообщение', 'Элемент сохранен');

					Ext6.defer(function(){msg.close()}, 1000);
				}
			},
			failure: function(response,options){
				Ext6.Msg.alert('Ошибка','Во время сохранения возникла непредвиденная ошибка.');
			}
		});

		var button_cancel = form_panel.query('button#cancel')[0];
		button_cancel.fireEvent('click', button_cancel);
	},
	
	/**
	 * Событие клика по кнопке отмены в форме редактирования дерева решений
	 */
	onEditFormButtonCancelClick: function(btn, e, eOpts){
		var form_panel = btn.up('form'),
			form = form_panel.getForm();
	
		form.findField('AmbulanceDecigionTree_id').reset();
		form.findField('TreeNode_Text').reset();
		form.findField('CmpReason_id').reset();
		
		this.getFormPanel().disable();
		this.getTree().enable();
	},
	
	/**
	 * Событие клика по кнопке добавления в окне дерева решений
	 */
	onEditWindowButtonCreateClick: function(btn, e, eOpts){
		var parent_node = this.getTree().getSelectedTreeNode();
		if (parent_node === null) {
			Ext6.Msg.alert('Предупреждение','Выберите запись, в которую будет добавлена новая.');
			return false;
		}
		
		this.getTree().disable();
		this.getFormPanel().enable();

		var form = this.getFormPanel().getForm();
		form.findField('TreeNode_Text').focus();
		form.findField('AmbulanceDecigionTree_Type').setValue(parent_node.data['AmbulanceDecigionTree_Type'] === 1 ? 2:1);
		//form.findField('CmpReason_id').setDisabled(parent_node.data['AmbulanceDecigionTree_Type'] == 2);
	},
	
	/**
	 * Событие клика по кнопке редактирования в окне дерева решений
	 */
	onEditWindowButtonUpdateClick: function(btn, e, eOpts){
		if (this.getFormPanel().hidden===true){
			return false
		}
		var node = this.getTree().getSelectedTreeNode();
		if (node === null) {
			Ext6.Msg.alert('Предупреждение','Выберите запись которую необходимо отредактировать.');
			return false;
		}
		
		this.getTree().disable();
		this.getFormPanel().enable();

		// Заполняем форму значениями
		var form = this.getFormPanel().getForm(),
			TreeNode_Text = form.findField('TreeNode_Text'),
			AmbulanceDecigionTree_Type = form.findField('AmbulanceDecigionTree_Type'),
			CmpReason_id = form.findField('CmpReason_id');
	
		TreeNode_Text.focus();
		CmpReason_id.setDisabled(node.data['AmbulanceDecigionTree_Type'] == 1);
		
		form.findField('AmbulanceDecigionTree_id').setValue(node.data['AmbulanceDecigionTree_id']);

		var text = node.data['AmbulanceDecigionTree_Text'];
				
		// Отделяем текст ответа от кода, если код есть
		if (node.data['CmpReason_id']) {
			var text_delimiter = this.getFormPanel().text_delimiter,
				pos = text.indexOf(text_delimiter);
			while (text.indexOf(text_delimiter,pos+1) != -1 ) {
				pos = text.indexOf(text_delimiter,pos+1);
			}
			text = text.substr(0,pos)
		}

		TreeNode_Text.setValue(text);
		AmbulanceDecigionTree_Type.setValue(node.data['AmbulanceDecigionTree_Type']);
		
		if (node.data['AmbulanceDecigionTree_Type'] == 2 && node.data['CmpReason_id']) {
			CmpReason_id.setValue(node.data['CmpReason_id']);
		}
	},
	
	/**
	 * Событие клика по кнопке удаления в окне дерева решений
	 */
	onEditWindowButtonDeleteClick: function(btn, e, eOpts){
		var tree = this.getTree(),
			node = tree.getSelectedTreeNode();
		if (node === null) {
			Ext6.Msg.alert('Предупреждение','Выберите запись, которую необходимо удалить.');
			return false;
		}
		
		Ext6.Msg.confirm('Подтвердите','Подтвердите удаление элемента',function(btn){
			if (btn == 'yes') {

				//сохранение 1 записи
				Ext6.Ajax.request({
					url: '/?c=CmpCallCard&m=deleteDecigionTreeNode',
					params: {
						AmbulanceDecigionTree_id: node.get('AmbulanceDecigionTree_id')
					},
					success: function(response,options){
						if(response.responseText){
							var data = Ext6.JSON.decode(response.responseText);
							if (!data['success']) {
								Ext6.Msg.alert('Ошибка',data['Error_Msg']);
							}else{
								tree.getSelectionModel().select(node.parentNode);
								node.remove();

								var msg = Ext6.Msg.alert('Сообщение', 'Элемент удален');
								Ext6.defer(function(){msg.close()}, 1000);
							}
						}
					},
					failure: function(response,options){
						Ext6.Msg.alert('Ошибка','Во время сохранения возникла непредвиденная ошибка.');
					}
				});
			}
		});


	},
	
	/**
	 * Событие клика по кнопке свернуть все в окне дерева решений
	 */
	onEditWindowButtonCollapseAllClick: function(btn, e, eOpts){
		this.getTree().collapseAll();
	},
	
	/**
	 * Событие клика по кнопке развернуть все в окне дерева решений
	 */
	onEditWindowButtonExpandAllClick: function(btn, e, eOpts){
		this.getTree().expandAll();
	},
	
	/**
	 * Событие клика по кнопке сохранения изменений в окне дерева решений
	 */
	onEditWindowButtonSaveClick: function(btn, e, eOpts){
		var me = this,
			win = this.getEditWindow();
		win.setLoading('Сохранение...');
		
		Ext6.Ajax.request({
			url: '/?c=CmpCallCard&m=saveDecigionTree',
			params: {
				data: Ext6.JSON.encode(this.getTree().collectData())
			},
			//timeout: 3600,
			callback: function(options,success,response){
				win.setLoading(false);
			},
			success: function(response,options){
				if(response.responseText){
					var data = Ext6.JSON.decode(response.responseText);
					if (!data['success']) {
						Ext6.Msg.alert('Ошибка',data['Error_Msg']);
					}
					
					me.fireEvent('onAfterDecisionTreeSave', data);
				}
				win.setLoading(false);
			},
			failure: function(response,options){
				Ext6.Msg.alert('Ошибка','Во время сохранения возникла непредвиденная ошибка.');
				log({response:response,options:options});
				win.setLoading(false);
			}
		});
	},
	
	/**
	 * Событие клика по кнопке отмены изменений в окне дерева решений
	 */
	onEditWindowButtonCancelClick: function(btn, e, eOpts){
		this.getEditWindow().close();
	},
	
	/**
	 * Вызывается после сохранения
	 * 
	 * @param object data Данные ответа сервера
	 */
	afterDecisionTreeSave: function(data){
		this.getEditWindow().close();
	},
	
	/**
	 * Событие загрузки хранилища дерева решений
	 */
	onDecisionTreeStoreLoad: function(store, node, records, successful, eOpts){
		var tree = this.getTree();
//		
		tree.setRootNode(tree.getRootNode().childNodes[0]);
		tree.getSelectionModel().select(tree.getRootNode());
	}

});

