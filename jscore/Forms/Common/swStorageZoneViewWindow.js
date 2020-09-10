/**
* swStorageZoneViewWindow - форма просмотра номенклатурного справочника
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @comment      
*
*/

sw.Promed.swStorageZoneViewWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swStorageZoneViewWindow',
	objectSrc: '/jscore/Forms/Common/swStorageZoneViewWindow.js',
	id: 'StorageZoneViewWindow',
	doSearch: function(params) {
		if(!params){
			var params = {};
		}
		var form = this;
		var base_form = form.FilterPanel.getForm();
		var s_params = new Object();

		s_params = base_form.getValues();

		var active_tab_title = this.StorageZoneViewTabs.activeTab.title;
		switch(active_tab_title){
			case 'По местам хранения':
			case 'По медикаментам':
				if(params.Storage_id){
					s_params.Storage_id = params.Storage_id;
				}
				if(params.StorageZone_id){
					s_params.StorageZone_id = params.StorageZone_id;
				} else {
					s_params.StorageZone_id = null;
				}
				if(params.Without_sz){
					s_params.Without_sz = params.Without_sz;
				} else {
					s_params.Without_sz = null;
				}
				form.DrugGrid.loadData({params: s_params, globalFilters: s_params});
			//break;
			//case 'По медикаментам':

				s_params.start = 0;
				s_params.limit = 100;

				form.AllDrugGrid.loadData({params: s_params, globalFilters: s_params});
			break;
			case 'Журнал перемещений':
				s_params.start = 0;
				s_params.limit = 100;
				s_params.begDate = Ext.util.Format.date(base_form.findField('Journal_date_range').getValue1(),'d.m.Y');
				s_params.endDate = Ext.util.Format.date(base_form.findField('Journal_date_range').getValue2(),'d.m.Y');
				form.StorageDrugMoveGrid.loadData({params: s_params, globalFilters: s_params});
			break;
		}
		
	},
	doReset: function() {
		var form = this;
		var base_form = form.FilterPanel.getForm();
		base_form.reset();
	},
	// делает переданный элемент областью дропа
	createDDTarget: function(el, record) {
		var win = this;
		new Ext.dd.DropTarget(el, {
			ddGroup: this.id + '_gridDDGroup',
			isAccess: function(selections) {
				var flag = true;
				Ext.each(selections, function(r) {
					if( r.get('Storage_id') == 0 || record.Storage_id != r.get('Storage_id') ) {
						flag = false;
					}
				});
				return flag;
			},
			notifyOver: function(ddSource, e, data) {
				return this.isAccess(data.selections) ? this.dropAllowed : this.dropNotAllowed;
			},
			notifyDrop: function(ddSource, e, data) {
				var node = win.StorageZoneTree.getSelectionModel().selNode;
				if((Ext.isEmpty(node.attributes.object_value) && node.attributes.without_sz == 0) || node.attributes.object_value == record.object_value){
					return false;
				}
				if(!Ext.isEmpty(node.attributes.Liable_Object)){
					sw.swMsg.alert(lang['oshibka'], 'Передача медикаментов, находящихся на подотчете, не возможна.');
					return false;
				}
				var isDrop = this.isAccess(data.selections);
				if( isDrop ) {
					Ext.Msg.show({
						title: lang['vnimanie'],
						msg: 'Вы действительно хотите переместить выбранные медикаменты в другое место хранения?',
						buttons: Ext.Msg.YESNO,
						fn: function(btn) {
							if (btn === 'yes') {
								win.moveDrugsInOtherSz(data.selections, record.object_value);
							}
						},
						icon: Ext.MessageBox.WARNING
					});
				} else {	
					if( data.selections[0].get('Storage_id') != record.Storage_id ) {
						sw.swMsg.alert(lang['oshibka'], 'Медикаменты могут быть перемещены только на места хранения текущего склада. Для перемещения медикаментов на другой склад создайте документ на внутреннее перемещение.');
						return false;
					}
				}
				return isDrop;
			}
		});
	},
	moveDrugsInOtherSz: function (records, StorageZone_id, callback) {
		var wnd = this;
		var params = {
			StorageZone_id:StorageZone_id
		};
		var record_ids = new Array();
		for(var i = 0;i<records.length;i++){
			if(records[i].get('DrugStorageZone_id') && records[i].get('Drug_Kolvo') > 0){
				record_ids.push(records[i].get('DrugStorageZone_id')+':'+records[i].get('Drug_Kolvo'));
			}
		}
		record_ids = record_ids.join('|');
		params.record_ids = record_ids;

		var drugostatreg_ids = new Array();
		for(var i = 0;i<records.length;i++){
			if(records[i].get('DrugOstatRegistry_ids') && records[i].get('Drug_Kolvo') > 0){
				drugostatreg_ids.push(records[i].get('DrugOstatRegistry_ids')+':'+records[i].get('Drug_Kolvo'));
			}
		}
		drugostatreg_ids = drugostatreg_ids.join('|');
		params.drugostatreg_ids = drugostatreg_ids;

		Ext.Ajax.request({
			url: '/?c=StorageZone&m=moveDrugsToStorageZone',
			params: params,
			success: function (response)
			{
				var result = Ext.util.JSON.decode(response.responseText);
				this.loadTree();
			}.createDelegate(this),
			failure: function (response)
			{
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.Error_Msg) {
					// Ошибку уже показали
				} else {
					Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
				}
			}.createDelegate(this)
		});
	},
	loadStorageCombo: function(){
		var wnd = this;
		var base_form = this.FilterPanel.getForm();
		var storage = base_form.findField('Storage_id').getValue();
		var baseParams = {
			Org_id: base_form.findField('Org_id').getValue()
		};
		var org = base_form.findField('Org_id').getStore().getById(baseParams.Org_id);
		if(org && org.get('OrgType_SysNick') == 'lpu'){
			if(!Ext.isEmpty(base_form.findField('LpuBuilding_id').getValue())){
				baseParams.LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
			}
			if(!Ext.isEmpty(base_form.findField('LpuSection_id').getValue())){
				baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
			}
		}
		base_form.findField('Storage_id').getStore().baseParams = baseParams;
		base_form.findField('Storage_id').getStore().load({
			callback:function(){
				if(Ext.isEmpty(storage) || base_form.findField('Storage_id').getStore().getById(storage) == -1){
					storage = '';
				}
				base_form.findField('Storage_id').setValue(storage);
			}
		});
	},
	deleteStorageZone: function(params){
		var wnd = this;
		Ext.Ajax.request({
			url: '/?c=StorageZone&m=deleteStorageZone',
			params: params,
			success: function (response)
			{
				var result = Ext.util.JSON.decode(response.responseText);
				wnd.loadTree();
			}.createDelegate(this),
			failure: function (response)
			{
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.Error_Msg) {
					// Ошибку уже показали
				} else {
					Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
				}
			}.createDelegate(this)
		});
	},
	giveStorageZoneToPerson: function(teamData){
		var wnd = this;
		var team = teamData;
		if(!team || typeof team != 'object' || !team.team_id){
			Ext.Msg.alert('Ошибка', 'Не указано подотчетное лицо.');
			return false;
		}
		if(!team.team_num){
			team.team_num = '';
		}
		var params = {
			StorageZoneLiable_ObjectId: team.team_id,
			DrugDocumentType_id: 29 // Тип документа - 29 - Передача укладки
		};
		var node = this.StorageZoneTree.getSelectionModel().selNode;
		if(node && node.attributes && node.attributes.object_value > 0 && node.attributes.Storage_id > 0 && node.attributes.without_sz == 0){
			params.StorageZone_id = node.attributes.object_value;
		} else {
			Ext.Msg.alert('Ошибка', 'Не указано место хранения для передачи на подотчет.');
			return false;
		}
		
		Ext.Ajax.request({
			url: '/?c=StorageZone&m=giveStorageZoneToPerson',
			params: params,
			success: function (response)
			{
				var result = Ext.util.JSON.decode(response.responseText);
				if(result){
					if(result.Error_Msg){
						// Ошибку уже показали
					} else {
						Ext.Msg.alert('Сообщение', 'Место хранения '+node.attributes.text+' передано бригаде '+team.team_num+'.');
						wnd.loadTree();
					}
				}
				
			}.createDelegate(this),
			failure: function (response)
			{
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.Error_Msg) {
					// Ошибку уже показали
				} else {
					Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
				}
			}.createDelegate(this)
		});
	},
	checkActions: function(node){
		var wnd = this;
		this.StorageZoneToolbar.items.get(0).setDisabled(!wnd.isAdmin);
		this.StorageZoneToolbar.items.get(1).setDisabled((node && Ext.isEmpty(node.attributes.object_value)) || !wnd.isAdmin);
		this.StorageZoneToolbar.items.get(2).setDisabled(node && Ext.isEmpty(node.attributes.object_value));
		this.StorageZoneToolbar.items.get(3).setDisabled((node && Ext.isEmpty(node.attributes.object_value)) || !wnd.isAdmin);
		this.StorageZoneToolbar.items.get(4).menu.items.get(0).setDisabled(node && Ext.isEmpty(node.attributes.object_value));
		this.StorageZoneToolbar.items.get(4).menu.items.get(1).setVisible(getGlobalOptions().orgtype != 'lpu' && (node && Ext.isEmpty(node.attributes.object_value) && node.attributes.without_sz == 0) && wnd.isAdmin);
		this.StorageZoneToolbar.items.get(4).menu.items.get(2).setVisible(wnd.mode == 'smp');
		this.StorageZoneToolbar.items.get(4).menu.items.get(3).setVisible(wnd.mode == 'smp');
	},
	initComponent: function() {
		var form = this;

		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);

		form.StorageZoneTree = new Ext.tree.TreePanel({
			animate: false,
			autoLoad: false,
			autoScroll: true,
			height:500,
			border: true,
			frame: true,
			enableDD: false,
			region: 'west',
			root: {
				nodeType: 'async',
				text: 'Склады',
				id: 'root',
				expanded: true
			},
			rootVisible: false,
			selModel: new Ext.tree.KeyHandleTreeSelectionModel(),
			split: true,
			title: '',
			style:'float:left;overflow:auto;',
			anchor:'30%',
			minSize: 300,
			maxSize: 350,
			getLoadTreeMask: function(MSG) {
				if ( MSG )  {
					delete(this.loadMask);
				}

				if ( !this.loadMask ) {
					this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
				}

				return this.loadMask;
			},
			loader: new Ext.tree.TreeLoader( {
				listeners: {
					'beforeload': function (tl, node) {
						if(Ext.isEmpty(form.FilterPanel.getForm().findField('Org_id').getValue())){
							form.StorageZoneTree.removeAll();
							return false;
						}
						form.StorageZoneTree.getLoadTreeMask('Загрузка мест хранения').show();

						tl.baseParams.level = node.getDepth();
						tl.baseParams.mode = form.mode;
						
						if ( node.getDepth() > 0 ) {
							tl.baseParams.StorageZone_pid = node.attributes.object_value;
							tl.baseParams.Storage_id = node.attributes.Storage_id;
						} else {
							tl.baseParams.StorageZone_pid = null;
							tl.baseParams.Storage_id = null;
						}
						var base_form = form.FilterPanel.getForm();
						tl.baseParams.Org_id = base_form.findField('Org_id').getValue();
						tl.baseParams.LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
						tl.baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
						if(!Ext.isEmpty(base_form.findField('Storage_id').getValue())){
							tl.baseParams.Storage_id = base_form.findField('Storage_id').getValue();
						}
					},
					'load': function(node,loadedNode,response) {
						callback: {
							var mynode, child;
							var childs = loadedNode.childNodes;
							if (node.baseParams.level == 0) {
								mynode = form.StorageZoneTree.getRootNode();
								child = mynode.firstChild;
								form.StorageZoneTree.fireEvent('click', child);
							} else {
								for(var i = 0;i<childs.length;i++){
									//Создаем цели для драг енд дропа
									form.createDDTarget(childs[i].ui.elNode,childs[i].attributes);
								}
							}
							for(var i = 0;i<childs.length;i++){
								var attr = childs[i].attributes;
								if(!Ext.isEmpty(attr.id) && attr.without_sz == 0){
									//Добавляем хинты
									childs[i].ui.node.attributes.qtipCfg = {
								        text: attr.Comment,
								        enabled: true,
								        showDelay: 20,
								        trackMouse: true,
								        autoShow: true
								    };
								}
							}
							form.StorageZoneTree.getLoadTreeMask().hide();
						}
					}
				},
				dataUrl:'/?c=StorageZone&m=loadStorageZoneTree'
			})
		});
		
		// Двойной клик на ноде выполняет соответствующий акшен
		form.StorageZoneTree.on('dblclick', function(node, event) {
			var tree = node.getOwnerTree();
		});

		form.StorageZoneTree.getSelectionModel().on('selectionchange', function(sm, node) {
			form.onTreeSelect(sm, node);
		});
		
		form.DrugGrid = new sw.Promed.ViewFrame( {
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			selectionModel: 'multiselect',
			ddGroup: this.id + '_gridDDGroup',
			enableDragDrop: true,
			toolbar: false,
			dataUrl: '/?c=StorageZone&m=loadDrugGrid',
			id: form.id + 'DrugGrid',
			style:'float:left',
			anchor:'70%',
			onLoadData: function() {
                
			},
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh', hidden: true, disabled: true},
				{name: 'action_print', hidden: true, disabled: true}
			],
			paging: false,
			region: 'east',
			stringfields: [
				{ name: 'DrugListKey', type: 'string', header: 'ID', key: true },
				{ name: 'DrugOstatRegistry_ids', type: 'string', hidden: true },
				{ name: 'Drug_id', type: 'int', hidden: true },
				{ name: 'Drug_Kolvo', type: 'float', header: 'Кол-во к перемещению', width: 150, editor: new Ext.form.NumberField()},
				{ name: 'DrugCount', header: 'Остаток на месте хранения', width: 180 },
				{ name: 'GoodsUnit_Name', header: 'Ед.уч.', width: 100 },
				{ name: 'Drug_Name', header: 'Наименование', id: 'autoexpand'},
				{ name: 'Drug_Code', header: 'Код', width: 80 },
				{ name: 'PrepSeries_Ser', header: 'Серия', width: 80 },
				{ name: 'PrepSeries_GodnDate', header: 'Срок годности', type: 'date', width: 100 },
				{ name: 'DrugShipment_Row', header: 'Партия', width: 200 },
				{ name: 'Storage_id', type: 'int', hidden: true },
				{ name: 'DrugStorageZone_id', type: 'int', hidden: true },
				{ name: 'StorageZone_id', type: 'int', hidden: true }
			],
			totalProperty: 'totalCount',
            contextmenu: false,
            onAfterEditSelf: function(o) {
            	var record = o.grid.getStore().getById(o.record.data.DrugListKey);
				var newVal = parseFloat(o.value);
				if(isNaN(newVal)){
					newVal = 0;
					record.set('Drug_Kolvo',newVal);
					record.commit();
				}
				var oldVal = parseFloat(o.record.data.DrugCount);
				if(newVal > oldVal){
					sw.swMsg.alert('Ошибка', 'Количество не может быть больше остатка');
					record.set('Drug_Kolvo',oldVal);
					record.commit();
				}
				return true;
			}
		});

		form.StorageZoneToolbar = new sw.Promed.Toolbar({
			height: 26,
			region: 'north',
			items:
			[
				{
					name: 'szt_add',
					text: lang['dobavit'],
					iconCls: 'add16',
					handler: function(){
						var wnd = this;
						var base_form = this.FilterPanel.getForm();
						var params = {
							action: 'add',
							fromARM: wnd.fromARM,
							callback:function(){
								wnd.StorageZoneTree.getRootNode().select();
								wnd.loadTree();
							}
						};
						params.Org_id = base_form.findField('Org_id').getValue();
						params.LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
						params.LpuSection_id = base_form.findField('LpuSection_id').getValue();
						params.Storage_id = base_form.findField('Storage_id').getValue();
						var node = this.StorageZoneTree.getSelectionModel().selNode;
						if(node && node.attributes){
							if(Ext.isEmpty(params.Org_id) && node.attributes.Org_id){
								params.Org_id = node.attributes.Org_id;
							}
							if(Ext.isEmpty(params.LpuBuilding_id) && node.attributes.LpuBuilding_id){
								params.LpuBuilding_id = node.attributes.LpuBuilding_id;
							}
							if(Ext.isEmpty(params.LpuSection_id) && node.attributes.LpuSection_id){
								params.LpuSection_id = node.attributes.LpuSection_id;
							}
							if(Ext.isEmpty(params.Storage_id) && node.attributes.Storage_id){
								params.Storage_id = node.attributes.Storage_id;
							}
							if(node.attributes.object_value > 0 && wnd.fromARM != 'smp'){
								params.StorageZone_pid = node.attributes.object_value;
							}
						}
						getWnd('swStorageZoneEditWindow').show(params);
					}.createDelegate(this)
				},
				{
					name: 'szt_edit',
					text: lang['redaktirovat'],
					iconCls: 'edit16',
					handler: function(){
						var wnd = this;
						var node = this.StorageZoneTree.getSelectionModel().selNode;
						if(node && node.attributes && node.attributes.object_value > 0){
							var params = {
								action: 'edit',
								StorageZone_id: node.attributes.object_value,
								owner: wnd,
								callback:function(){
									wnd.loadTree();
								}
							};
							getWnd('swStorageZoneEditWindow').show(params);
						} else {
							Ext.Msg.alert('Ошибка', 'Не выбрано место хранения');
						}
						return true;
					}.createDelegate(this)
				},
				{
					name: 'szt_view',
					text: lang['prosmotret'],
					iconCls: 'view16',
					handler: function(){
						var wnd = this;
						var node = this.StorageZoneTree.getSelectionModel().selNode;
						if(node && node.attributes && node.attributes.object_value > 0){
							var params = {
								action: 'view',
								StorageZone_id: node.attributes.object_value
							};
							getWnd('swStorageZoneEditWindow').show(params);
						} else {
							Ext.Msg.alert('Ошибка', 'Не выбрано место хранения');
						}
						return true;
					}.createDelegate(this)
				},
				{
					name: 'szt_delete',
					text: lang['udalit'],
					iconCls: 'delete16',
					handler: function(){
						var wnd = this;
						var node = this.StorageZoneTree.getSelectionModel().selNode;
						if(node && node.attributes && node.attributes.object_value > 0){
							var params = {
								StorageZone_id: node.attributes.object_value
							};
							this.deleteStorageZone(params);
						} else {
							Ext.Msg.alert('Ошибка', 'Не выбрано место хранения');
						}
						return true;
					}.createDelegate(this)
				},
				{
                    name: 'szt_actions',
                    iconCls: 'actions16',
                    text: lang['deystviya'],
                    menu: [{
                        name: 'card',
                        scope: this,
                        handler: function(){
							var wnd = this;
							var node = this.StorageZoneTree.getSelectionModel().selNode;
							if(node && node.attributes && node.attributes.object_value > 0){
								var params = {
									StorageZone_id: node.attributes.object_value
								};
								var drug = this.DrugGrid.getGrid().getSelectionModel().getSelected();
								params.Drug_id = drug.get('Drug_id');
								if(params.StorageZone_id != drug.get('StorageZone_id')){
									Ext.Msg.alert('Сообщение', 'Выбранное место хранения не является конечным местом хранения для указанного медикамента, карточка с перемещениями по нему не формируется.');
								} else {
									getWnd('swStorageDrugMoveViewWindow').show(params);
								}
							} else {
								Ext.Msg.alert('Ошибка', 'Не выбрано место хранения');
							}
							return true;
						}.createDelegate(this),
                        text: 'Карточка'
                    }, {
                        name: 'bind_to_gk',
                        scope: this,
                        handler: function(){
							var wnd = this;
							var node = this.StorageZoneTree.getSelectionModel().selNode;
							if(node && node.attributes && !(node.attributes.object_value > 0) && node.attributes.Storage_id > 0 && node.attributes.without_sz == 0){
								var params = {
									Storage_id: node.attributes.Storage_id
								};
								getWnd('swStorageDocSupplyViewWindow').show(params);
							} else {
								Ext.Msg.alert('Ошибка', 'Не выбран склад');
							}
							return true;
						}.createDelegate(this),
                        text: 'Связать с контрактами'
                    }, {
                        name: 'give_to_person',
                        scope: this,
                        handler: function(){
							var wnd = this;
							var node = this.StorageZoneTree.getSelectionModel().selNode;
							if(node && node.attributes && node.attributes.object_value > 0 && node.attributes.Storage_id > 0 && node.attributes.without_sz == 0){
								if(node.attributes.isMobile != 1){
									Ext.Msg.alert('Сообщение', 'Для этого места хранения операция передачи не предусмотрена.');
									return true;
								}
								var params = {
									StorageZone_id: node.attributes.object_value,
									LpuBuilding_id: wnd.smp.LpuBuilding_id
								};
								var loadMask = new Ext.LoadMask(this.MainPanel.getEl(), {msg:'Загрузка бригад'});
        						loadMask.show();
								Ext.Ajax.request({
									url: '/?c=StorageZone&m=getBrigadesForGiveStorageZoneToPerson',
									params: params,
									success: function (response)
									{
										var result = Ext.util.JSON.decode(response.responseText);
										if(result){
											if(result[0] && result[0].Error_Msg){
												Ext.Msg.alert('Сообщение', result[0].Error_Msg);
											} else if (result.length > 0) {
												var me = this;
												var menu = me.StorageZoneToolbar.items.get(4).menu.items.get(2).menu;
												menu.removeAll();
												for (var i = 0; i < result.length; i++) {
													var textStr = result[i].EmergencyTeamSpec_Name+' Бригада №'+result[i].EmergencyTeam_Num;
													textStr += ' '+result[i].EmergencyTeam_HeadShiftFIO;
													if(result[i].EmergencyTeamDuty_id){
														textStr += ' '+result[i].EmergencyTeamDuty_DTStart+' - '+result[i].EmergencyTeamDuty_DTFinish;
													}
													menu.addMenuItem({
														text: textStr,
														value: result[i].EmergencyTeam_id,
														num: result[i].EmergencyTeam_Num,
														hideParent: true,
														style: (result[i].EmergencyTeamDuty_id ? 'font-weight:bold;' :''),
														handler: function () {
															Ext.getCmp('StorageZoneViewWindow').giveStorageZoneToPerson({team_id:this.value,team_num:this.num});
														}
													});
												}
												me.StorageZoneToolbar.items.get(4).menu.items.get(2).showDelay = 200;
												menu.render();
												menu.el.show();
												var item = me.StorageZoneToolbar.items.get(4).menu.items.get(2);
											    item.menu.show(item.container, item.parentMenu.subMenuAlign || "tl-tr?", item.parentMenu);
									            item.menu.tryActivate(0, 1);
											}
										}
										loadMask.hide();
									}.createDelegate(this),
									failure: function (response)
									{
										loadMask.hide();
										var result = Ext.util.JSON.decode(response.responseText);
										if (result.Error_Msg) {
											// Ошибку уже показали
										} else {
											Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
										}
									}.createDelegate(this)
								});
							} else {
								Ext.Msg.alert('Ошибка', 'Не выбрано место хранения');
							}
							return true;
						}.createDelegate(this),
						menu: [],
						hideOnClick: false,
                        text: 'Передать'
                    }, {
                        name: 'take_from_person',
                        scope: this,
                        handler: function(){
							var wnd = this;
							var node = this.StorageZoneTree.getSelectionModel().selNode;
							if(node && node.attributes && node.attributes.object_value > 0 && node.attributes.Storage_id > 0 && node.attributes.without_sz == 0){
								
								var params = {
									StorageZone_id: node.attributes.object_value,
									DrugDocumentType_id: 30 // Тип документа - 30 - Возврат укладки
								};
								Ext.Ajax.request({
									url: '/?c=StorageZone&m=takeStorageZoneFromPerson',
									params: params,
									success: function (response)
									{
										var result = Ext.util.JSON.decode(response.responseText);
										if(result){
											if(result[0] && result[0].Error_Msg){
												Ext.Msg.alert('Сообщение', result[0].Error_Msg);
											} else if (result.success) {
												var msg = 'Место хранения '+node.attributes.text+' принято от бригады';
												if(result.num){
													msg += ' '+result.num+'.';
												} else {
													msg += '.';
												}
												Ext.Msg.alert('Сообщение', msg);
												wnd.loadTree();
											}
										}
									}.createDelegate(this),
									failure: function (response)
									{
										var result = Ext.util.JSON.decode(response.responseText);
										if (result.Error_Msg) {
											// Ошибку уже показали
										} else {
											Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу. Попробуйте повторить операцию.');
										}
									}.createDelegate(this)
								});
							} else {
								Ext.Msg.alert('Ошибка', 'Не выбрано место хранения');
							}
							return true;
						}.createDelegate(this),
                        text: 'Принять'
                    }]
                }
			]
		});

		form.ByStorageZonePanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: false,
			height:'100%',
			bodyBorder: false,
			border: false,
			frame: false,
			defaults: {
				split: true,
				bodyStyle:'background:#fff'
			},
			items: [
				form.StorageZoneToolbar,
				form.StorageZoneTree,
				form.DrugGrid 
			]
		});

		form.AllDrugGrid = new sw.Promed.ViewFrame( {
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			toolbar: false,
			dataUrl: '/?c=StorageZone&m=loadAllDrugGrid',
			id: form.id + 'AllDrugGrid',
			onLoadData: function() {
                
			},
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh', hidden: true, disabled: true},
				{name: 'action_print', hidden: true, disabled: true}
			],
			paging: true,
			pageSize:100,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'DrugOstatRegistry_ids', type: 'string', header: 'ID', key: true },
				{ name: 'Drug_id', type: 'int', header: 'ID', hidden: true },
				{ name: 'Drug_Code', header: 'Код', width: 80 },
				{ name: 'Drug_Name', header: 'Наименование', id: 'autoexpand'},
				{ name: 'PrepSeries_Ser', header: 'Серия', width: 80 },
				{ name: 'PrepSeries_GodnDate', header: 'Срок годности', type: 'date', width: 100 },
				{ name: 'DrugCount', header: 'Остаток', width: 100 },
				{ name: 'GoodsUnit_Name', header: 'Ед.учета', width: 100 },
				{ name: 'DrugShipment_Row', header: 'Партия', width: 200 },
				{ name: 'PrepSeries_id', type: 'int', hidden: true },
				{ name: 'DrugShipment_id', type: 'int', hidden: true },
				{ name: 'Storage_id', type: 'int', hidden: true }
			],
			totalProperty: 'totalCount',
            contextmenu: false
		});

		this.AllDrugGrid.getGrid().getSelectionModel().on('rowselect', function(sm, rIdx, rec) {
            this.AllDrugStorageGrid.getGrid().getStore().baseParams.DrugOstatRegistry_ids = rec.get('DrugOstatRegistry_ids');
            this.AllDrugStorageGrid.getGrid().getStore().baseParams.Drug_id = rec.get('Drug_id');
            this.AllDrugStorageGrid.getGrid().getStore().baseParams.DrugShipment_id = rec.get('DrugShipment_id');
            this.AllDrugStorageGrid.getGrid().getStore().baseParams.PrepSeries_id = rec.get('PrepSeries_id');
            this.AllDrugStorageGrid.getGrid().getStore().baseParams.Storage_id = rec.get('Storage_id');
            if(!Ext.isEmpty(rec.get('DrugOstatRegistry_ids'))){
            	this.AllDrugStorageGrid.getGrid().getStore().load();
            } else {
            	this.AllDrugStorageGrid.getGrid().removeAll();
            }
		}, this);

		form.AllDrugStorageGrid = new sw.Promed.ViewFrame( {
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			toolbar: false,
			dataUrl: '/?c=StorageZone&m=loadAllDrugStorageGrid',
			id: form.id + 'AllDrugStorageGrid',
			onLoadData: function() {
                
			},
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh', hidden: true, disabled: true},
				{name: 'action_print', hidden: true, disabled: true}
			],
			paging: true,
			pageSize:100,
			region: 'south',
			root: 'data',
			stringfields: [
				{ name: 'Key_field', type: 'string', header: 'ID', key: true },
				{ name: 'Storage_Header', type: 'string', header: 'Склад', width: 200 },
				{ name: 'StorageZone_Header', header: 'Место хранения', id: 'autoexpand'},
				{ name: 'DrugCount', header: 'Остаток', width: 100 },
				{ name: 'StorageZoneLiable_ObjectName', header: 'Субъект', width: 200 }
			],
			totalProperty: 'totalCount',
            contextmenu: false
		});

		form.ByDrugPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: false,
			height:'100%',
			bodyBorder: false,
			border: false,
			frame: false,
			defaults: {
				split: true,
				bodyStyle:'background:#fff'
			},
			items: [
				form.AllDrugGrid,
				form.AllDrugStorageGrid
			]
		});

		form.StorageDrugMoveGrid = new sw.Promed.ViewFrame( {
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=StorageZone&m=loadStorageDrugMoveGrid',
			id: form.id + 'StorageDrugMoveGrid',
			onLoadData: function() {
                
			},
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true},
				{name: 'action_refresh', handler:function(){this.doSearch();}.createDelegate(this)},
				{name: 'action_print'}
			],
			paging: true,
			pageSize:100,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'StorageDrugMove_id', type: 'int', header: 'ID', key: true },
				{ name: 'StorageDrugMove_setDate', type: 'date', header: 'Дата', width: 100 },
				{ name: 'Drug_Name', header: 'Наименование', id: 'autoexpand'},
				{ name: 'PrepSeries_Ser', header: 'Серия', width: 80 },
				{ name: 'DrugShipment_Name', header: 'Партия', width: 100 },
				{ name: 'DocumentUc', header: 'Документ', width: 200 },
				{ name: 'PrepSeries_GodnDate', header: 'Срок годности', type: 'date', width: 100 },
				{ name: 'DrugCount', header: 'Остаток', width: 100 },
				{ name: 'GoodsUnit_Name', header: 'Ед.учета', width: 100 },
				{ name: 'oStorageZone', header: 'Откуда', width: 200 },
				{ name: 'nStorageZone', header: 'Куда', width: 200 },
                { name: 'DrugFinance_Name', header: 'Финансирование', width: 200 }
			],
			totalProperty: 'totalCount'
		});

		form.StorageDrugMovePanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: false,
			height:'100%',
			bodyBorder: false,
			border: false,
			frame: false,
			defaults: {
				split: true,
				bodyStyle:'background:#fff'
			},
			items: [
				form.StorageDrugMoveGrid
			]
		});

		form.MainFilterPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: false,
			height:'100%',
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 115,
			border: false,
			frame: true,
			items: [
            {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'sworgcomboex',
						hiddenName: 'Org_id',
						fieldLabel: 'Организация',
						width: 230,
						disabled: (!isSuperAdmin()),
						listeners: {
							'select':function (combo) {
								combo.fireEvent('change',combo, combo.getValue());
							},
							'change': function (combo,newValue){
								var base_form = form.FilterPanel.getForm();
								var rec = combo.getStore().getById(newValue);
								if(rec && rec.get('OrgType_SysNick') == 'lpu'){
									var LpuBuilding_id = base_form.findField('LpuBuilding_id').getValue();
									base_form.findField('LpuBuilding_id').showContainer();
									Ext.Ajax.request({
										url: '/?c=Org&m=getLpuData',
										params:
										{
											Org_id: newValue
										},
										success: function (response)
										{
											var result = Ext.util.JSON.decode(response.responseText);
											if(result && result[0] && result[0].Lpu_id) {
												base_form.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = result[0].Lpu_id;
												base_form.findField('LpuBuilding_id').getStore().load({
													params:{Lpu_id:result[0].Lpu_id},
													callback:function(){
														if(LpuBuilding_id && base_form.findField('LpuBuilding_id').getStore().getById(LpuBuilding_id)){
															base_form.findField('LpuBuilding_id').setValue(LpuBuilding_id);
														} else {
															base_form.findField('LpuBuilding_id').setValue('');
														}
													}
												});
											}
										}.createDelegate(this)
									});
									var LpuSection_id = base_form.findField('LpuSection_id').getValue();
									base_form.findField('LpuSection_id').showContainer();
									base_form.findField('LpuBuilding_id').getStore().baseParams.mode = 'combo';
									base_form.findField('LpuBuilding_id').getStore().baseParams.Org_id = newValue;
									base_form.findField('LpuSection_id').getStore().load({
										params:{Org_id:newValue,mode:'combo'},
										callback:function(){
											if(LpuSection_id && base_form.findField('LpuSection_id').getStore().getById(LpuSection_id)){
												base_form.findField('LpuSection_id').setValue(LpuSection_id);
											} else {
												base_form.findField('LpuSection_id').setValue('');
											}
										}
									});
								} else {
									base_form.findField('LpuBuilding_id').setValue('');
									base_form.findField('LpuSection_id').setValue('');
									base_form.findField('LpuBuilding_id').hideContainer();
									base_form.findField('LpuSection_id').hideContainer();
								}
								this.loadStorageCombo();
							}.createDelegate(this)
						}
					}, {
						hiddenName: 'LpuBuilding_id',
						fieldLabel: 'Подразделение',
						xtype: 'swlpubuildingglobalcombo',
						listeners:{
							'select':function (combo) {
								combo.fireEvent('change',combo);
							}.createDelegate(this),
							'change':function (combo, newValue, oldValue) {
								this.loadStorageCombo();
							}.createDelegate(this)
						},
						width: 230
					}, {
						hiddenName: 'LpuSection_id',
						fieldLabel: 'Отделение',
						xtype: 'swlpusectionglobalcombo',
						lastQuery:'',
						listeners:{
							'select':function (combo) {
								combo.fireEvent('change',combo, combo.getValue());
							}.createDelegate(this),
							'change':function (combo, newValue, oldValue) {
								this.loadStorageCombo();
							}.createDelegate(this)
						},
						width: 230
					}, {
						xtype: 'swstoragecombo',
						width: 230,
						hiddenName:'Storage_id',
						fieldLabel: 'Склад',
						listeners: {
							'keydown': form.onKeyDown,
							'select':function (combo) {
								combo.fireEvent('change',combo, combo.getValue());
							}.createDelegate(this),
							'change':function (combo, newValue, oldValue) {
								
							}.createDelegate(this)
						}
					}]
				}, {
					layout: 'form',
					labelWidth: 125,
					items: [{
						xtype: 'textfield',
						fieldLabel: 'МНН',
						name: 'DrugComplexMnn_Name',
						width: 230
					}, {
						xtype: 'textfield',
						fieldLabel: 'Медикамент',
						name: 'Drug_Name',
						width: 230
					}, {
						xtype: 'swcommonsprcombo',
						fieldLabel: 'Финансирование',
						comboSubject: 'DrugFinance',
						hiddenName: 'DrugFinance_id',
						width: 230
					}, {
						xtype: 'swcommonsprcombo',
						fieldLabel: 'Статья расхода',
						comboSubject: 'WhsDocumentCostItemType',
						hiddenName: 'WhsDocumentCostItemType_id',
						width: 230
					}]
				}, {
					layout: 'form',
					labelWidth: 115,
					items: [{
						fieldLabel: 'Период',
						xtype: 'daterangefield',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
						width: 180,
						name: 'Journal_date_range',
						listeners: {'keydown': form.onKeyDown}
					}, {
                        xtype: 'swgoodsunitcombo',
                        hiddenName: 'GoodsUnit_id',
                        fieldLabel: 'Ед.учета',
                        width: 180
                    }]
				}]
			}]
		});

		form.StorageZoneViewTabs = new Ext.TabPanel({
			id: 'SPW_StorageZoneViewTabsPanel',
			autoScroll: false,
			activeTab: 0,
			border: true,
			resizeTabs: true,
			region: 'center',
			enableTabScroll: true,
			height:'100%',
			minTabWidth: 120,
			tabWidth: 'auto',
			layoutOnTabChange: true,
			items:[
				{
					title: 'По местам хранения',
					layout: 'fit',
					border:false,
					items: [form.ByStorageZonePanel]
				},
				{
					title: 'По медикаментам',
					layout: 'fit',
					border:false,
					items: [form.ByDrugPanel]
				},
				{
					title: 'Журнал перемещений',
					layout: 'fit',
					border:false,
					items: [form.StorageDrugMovePanel]
				}
			]
		});

		form.StorageZoneViewTabs.on('tabchange', function(sm, rIdx, rec) {
            var title = this.StorageZoneViewTabs.activeTab.title;
            switch(title){
            	case 'По местам хранения':
            	var h = (form.ByStorageZonePanel.getSize().height - 26);
				form.StorageZoneTree.setHeight(h);
				form.DrugGrid.setHeight(h);
				this.FilterPanel.getForm().findField('Journal_date_range').hideContainer();
				this.FilterPanel.getForm().findField('GoodsUnit_id').showContainer();
            	break;
            	case 'По медикаментам':
            	var h = (form.ByDrugPanel.getSize().height / 2);
				form.AllDrugGrid.setHeight(h);
				form.AllDrugStorageGrid.setHeight(h);
				this.FilterPanel.getForm().findField('Journal_date_range').hideContainer();
				this.FilterPanel.getForm().findField('GoodsUnit_id').hideContainer();
            	break;
            	case 'Журнал перемещений':
            	var h = form.StorageDrugMovePanel.getSize().height;
				form.StorageDrugMoveGrid.setHeight(h);
				this.FilterPanel.getForm().findField('Journal_date_range').showContainer();
				this.FilterPanel.getForm().findField('GoodsUnit_id').hideContainer();
            	break;
            }
		}, this);

		form.FilterButtonsPanel = new sw.Promed.Panel({
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				items: [{
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'SPW_BtnSearch',
						text: lang['nayti'],
						iconCls: 'search16',
						minWidth: 100,
						handler: function()
						{
							this.doSearch();
							if(this.StorageZoneViewTabs.activeTab.title == 'По местам хранения'){
								this.StorageZoneTree.getRootNode().select();
								this.loadTree();
							}
						}.createDelegate(this)
					}]
				}, {
					layout:'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'SPW_BtnReset',
						text: lang['sbros'],
						iconCls: 'reset16',
						minWidth: 100,
						handler: function()
						{
							form.doReset();
							if(this.StorageZoneViewTabs.activeTab.title == 'По местам хранения'){
								this.StorageZoneTree.getRootNode().select();
								this.loadTree();
							} else {
								this.doSearch();
							}
						}.createDelegate(this)
					}]
				}]
			}]
		});

		form.FilterPanel = getBaseFiltersFrame({
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: form,
			toolBar: form.WindowToolbar,
			items: [
				form.MainFilterPanel,
				form.FilterButtonsPanel
			]
		});

		form.MainPanel = new Ext.Panel({
			items: [
				form.FilterPanel,
				form.StorageZoneViewTabs
			],
			title: '',
			layout: 'border',
			region: 'center',
			listeners: {
				'resize': function() {
					if(this.layout.layout)
						this.doLayout();
				}
			},
			split: true
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tooltip: lang['zakryit']
			}],
			defaults: {
				split: true
			},
			layout: 'border',
			region: 'center',
			xtype: 'panel',
			items: [
				 form.MainPanel
			]
		});

		sw.Promed.swStorageZoneViewWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	loadTree: function (reloadCurrent) {
		var node = this.StorageZoneTree.getSelectionModel().selNode;
		
		if ( node ) {
			if ( node.parentNode && !reloadCurrent) {
				node = node.parentNode;
			}
		}
		else {
			node = this.StorageZoneTree.getRootNode();
		}

		if ( node ) {
			if ( node.isExpanded() ) {
				node.collapse();
				this.StorageZoneTree.getLoader().load(node);
			}
			node.expand();
			// Выбираем первую ноду и эмулируем клик 
			node.select();
			this.StorageZoneTree.fireEvent('click', node);
		}
	},
	maximized: true,
	maximizable: false,
	mode: null,
	// функция выбора элемента дерева 
	onTreeSelect: function(sm, node) {
		if ( !node ) {
			return false;
		}
		this.checkActions(node);
		var params = {};
		params.StorageZone_id = node.attributes.object_value;
		params.Storage_id = node.attributes.Storage_id;
		params.Without_sz = node.attributes.without_sz; // Флаг - Без места хранения
		
		if (Ext.isEmpty(params.Storage_id)) {
			this.DrugGrid.removeAll();
			return false;
		}

		this.doSearch(params);
	},
	reloadStorageZoneTree: function(reload) {
		var tree = this.StorageZoneTree;
		var root = tree.getRootNode();

		root.select();
		tree.getLoader().load(root);
		root.expand();
		root.select();

		if ( reload ) {
			this.onTreeSelect(tree.getSelectionModel(), root);
		}
	},
	shim: false,
	show: function() {
		sw.Promed.swStorageZoneViewWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = this.FilterPanel.getForm();

		this.action = 'edit';
		this.isAdmin = false;
		this.fromARM = null;
		this.mode = null;
		this.smp = {LpuBuilding_id:null};
		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0] && arguments[0].fromARM) {
			this.fromARM = arguments[0].fromARM;
		}
		if (arguments[0] && arguments[0].smp && arguments[0].smp.LpuBuilding_id) {
			this.smp.LpuBuilding_id = arguments[0].smp.LpuBuilding_id;
		}
		if (this.fromARM == 'smp' || (this.fromARM == 'merch' && arguments[0] && arguments[0].mode && arguments[0].mode == 'smp')){
			this.mode = 'smp';
		}
		base_form.findField('LpuBuilding_id').enable();
		base_form.findField('LpuSection_id').enable();
		if(isSuperAdmin() || isOrgAdmin()){
			this.isAdmin = true;
		}

		base_form.reset();
		this.DrugGrid.removeAll();

		this.StorageZoneViewTabs.setActiveTab(2);
		this.StorageZoneViewTabs.setActiveTab(1);
		this.StorageZoneViewTabs.setActiveTab(0);

		if(this.fromARM != 'superadmin'){
			if (this.fromARM == 'merch') {
				if(arguments[0].Storage_id){
					base_form.findField('Storage_id').setValue(arguments[0].Storage_id);
				}
				base_form.findField('LpuBuilding_id').disable();
				base_form.findField('LpuSection_id').disable();
				if(arguments[0].LpuBuilding_id){
					var LpuBuilding_id = arguments[0].LpuBuilding_id;
					base_form.findField('LpuBuilding_id').setValue(LpuBuilding_id);
				}
				if(arguments[0].LpuSection_id){
					var LpuSection_id = arguments[0].LpuSection_id;
					base_form.findField('LpuSection_id').setValue(LpuSection_id);
				}
			}
			base_form.findField('Org_id').setValue(getGlobalOptions().org_id);
	        base_form.findField('Org_id').getStore().load({
	        	params:{Org_id:getGlobalOptions().org_id},
	        	callback: function(){
	        		base_form.findField('Org_id').setValue(getGlobalOptions().org_id);
	        		base_form.findField('Org_id').fireEvent('change',base_form.findField('Org_id'),getGlobalOptions().org_id);
	        		if(!isSuperAdmin()){
	        			base_form.findField('Org_id').disable();
	        		}
	        	}
	        });
		} else {
			base_form.findField('Org_id').fireEvent('change',base_form.findField('Org_id'),null);
		}
        var date = new Date();
		var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
		var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
		var date_str = Ext.util.Format.date(firstDay,'d.m.Y') + ' - ' + Ext.util.Format.date(lastDay,'d.m.Y');
		base_form.findField('Journal_date_range').setValue(date_str);

		this.StorageZoneTree.getRootNode().select();
		this.loadTree();

		this.StorageZoneToolbar.items.get(4).menu.addListener('show',function(){
			if(this.items.get(2).menu.items.length == 0){
				this.items.get(2).showDelay = 9999999999;
			} else {
				this.items.get(2).showDelay = 200;
			}
			if(this.items.get(2).el.hasClass('x-menu-item-arrow')){
				this.items.get(2).el.removeClass('x-menu-item-arrow');
			}
		});
		this.StorageZoneToolbar.items.get(4).menu.addListener('hide',function(){
			this.items.get(2).menu.removeAll();
		});
		
	},
	title: 'Размещение на складах'
});
