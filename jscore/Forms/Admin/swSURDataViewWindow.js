/**
 * swSURDataViewWindow - окно просмотра данных СУР
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.01.2016
 */
/*NO PARSE JSON*/

sw.Promed.swSURDataViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSURDataViewWindow',
	layout: 'border',
	title: lang['prosmotr_dannyih_sur'],
	maximizable: false,
	maximized: true,

	doSearch: function(doReset) {
		var base_form = this.FilterPanel.getForm();
		var lpu_combo = base_form.findField('Lpu_oid');
		var setDate_combo = base_form.findField('setDate');

		this.overwriteMOInfoTpl();
		
		if(doReset) {
			setDate_combo.setValue(null);
		}
		
		var Lpu_oid = lpu_combo.getValue();
		var setDate = setDate_combo.getRawValue();

		if (Ext.isEmpty(Lpu_oid)) {
			this.RoomGridPanel.removeAll();
			this.BedGridPanel.removeAll();
			this.FPStructureTreePanel.clearTree(true);

			this.PersonalGridPanel.removeAll();
			this.PersonalHistoryGridPanel.removeAll();
			this.FPPersonalTreePanel.clearTree(true);
		} else {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение данных..."});
			loadMask.show();

			Ext.Ajax.request({
				params: {Lpu_oid: Lpu_oid},
				url: '/?c=ServiceSUR&m=getMOInfo',
				success: function(response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					var doings = sw.Promed.Doings();

					this.overwriteMOInfoTpl(response_obj[0]);

					doings.start('loadFPStructTree');
					this.RoomGridPanel.removeAll();
					this.BedGridPanel.removeAll();
					this.FPStructureTreePanel.showTree();
					this.FPStructureTreePanel.getRootNode().setText(lpu_combo.getFieldValue('Lpu_Nick'));
					this.FPStructureTreePanel.getLoader().baseParams.Lpu_oid = Lpu_oid;
					this.FPStructureTreePanel.getLoader().baseParams.setDate = setDate;
					this.FPStructureTreePanel.getLoader().baseParams.ParentID = null;
					this.FPStructureTreePanel.getLoader().load(this.FPStructureTreePanel.getRootNode(), function() {
						doings.finish('loadFPStructTree');
					});

					doings.start('loadFPPersonalTree');
					this.PersonalGridPanel.removeAll();
					this.PersonalHistoryGridPanel.removeAll();
					this.FPPersonalTreePanel.showTree();
					this.FPStructureTreePanel.getRootNode().setText(lpu_combo.getFieldValue('Lpu_Nick'));
					this.FPPersonalTreePanel.getLoader().baseParams.Lpu_oid = Lpu_oid;
					this.FPPersonalTreePanel.getLoader().baseParams.setDate = setDate;
					this.FPPersonalTreePanel.getLoader().baseParams.ParentID = null;
					this.FPPersonalTreePanel.getLoader().load(this.FPPersonalTreePanel.getRootNode(), function() {
						doings.finish('loadFPPersonalTree');
					});

					doings.doLater('hideLoadMask', function(){loadMask.hide()});
				}.createDelegate(this),
				failure: function() {
					loadMask.hide();
				}.createDelegate(this)
			});
		}
	},

	overwriteMOInfoTpl: function(params){
		var sparams = {
			BIN: '',
			FullAddress: '',
			FullNameKZ: '',
			FullNameRU: '',
			ID: '',
			MedCode: '',
			RNN: ''
		};
		if (params) {
			sparams = Ext.apply(sparams, params);
		}
		this.MOInfoTpl.overwrite(this.MOInfoPanel.body, sparams);
	},

	overwriteFPInfoTpl: function(params){
		var sparams = {
			CodeKz: '',
			CodeRu: '',
			NameKZ: '',
			NameRU: '',
			NomenclatureKZ: '',
			NomenclatureRU: '',
			TypeKZ: '',
			TypeRU: ''
		};
		if (params) {
			sparams = Ext.apply(sparams, params);
		}
		this.FPInfoTpl.overwrite(this.FPInfoPanel.body, sparams);
	},

	show: function() {
		sw.Promed.swSURDataViewWindow.superclass.show.apply(this, arguments);

		var base_form = this.FilterPanel.getForm();

		this.overwriteMOInfoTpl();

		base_form.findField('Lpu_oid').setDisabled(!isSuperAdmin());
		base_form.findField('setDate').setValue(null);
		//base_form.findField('Lpu_oid').setValue(getGlobalOptions().lpu_id);
		base_form.findField('Lpu_oid').getStore().load({
			callback: function() {
				var index = base_form.findField('Lpu_oid').getStore().findBy(function(rec) {
					return rec.get('Lpu_id') == getGlobalOptions().lpu_id;
				});
				var record = base_form.findField('Lpu_oid').getStore().getAt(index);
				if (record) {
					base_form.findField('Lpu_oid').setValue(record.get('Lpu_id'));
				}
				this.doSearch();
			}.createDelegate(this)
		});
	},

	initComponent: function() {
		this.FPInfoTpl = new Ext.XTemplate([
			'<div style="padding:2px;font-size: 12px;"><b>Код подразделения (каз.):</b> {CodeKz}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Код подразделения (рус.):</b> {CodeRu}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Наименование подразделения (каз.):</b> {NameKZ}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Наименование подразделения (рус.):</b> {NameRU}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Номенклатура подразделения (каз.):</b> {NomenclatureKZ}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Номенклатура подразделения (рус.):</b> {NomenclatureRU}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Тип подразделения (каз.):</b> {TypeKZ}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Тип подразделения (рус.):</b> {TypeRU}</div>'
		]);

		this.FPInfoPanel = new Ext.Panel({
			id: 'SDVW_FPInfoPanel',
			bodyStyle: 'background:#DFE8F6;padding:5px;',
			//border: false,
			frame: true,
			layout: 'fit',
			html: ''
		});

		this.FPInfoWindow = new Ext.Window({
			owner: this,
			id: 'SDVW_FPInfoWindow',
			closable: false,
			width : 500,
			height : 120,
			modal: true,
			resizable: false,
			autoHeight: true,
			closeAction :'hide',
			border : false,
			plain : false,
			title: lang['podrazdelenie'],
			items : [this.FPInfoPanel],
			buttons : [{
				handler: function() {
					this.FPInfoWindow.hide();
					this.doLayout();
				}.createDelegate(this),
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			buttonAlign : "right"
		});

		this.FilterPanel = new Ext.FormPanel({
			autoHeight: true,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					labelWidth: 60,
					items: [{
						allowBlank: false,
						xtype: 'swlpucombo',
						hiddenName: 'Lpu_oid',
						fieldLabel: lang['mo'],
						listeners: {
							'select': function(combo, newValue, oldValue) {
								this.doSearch();
							}.createDelegate(this)
						},
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'Lpu_id', mapping: 'Lpu_id'},
								{name: 'Lpu_IsOblast', mapping: 'Lpu_IsOblast'},
								{name: 'Lpu_Name', mapping: 'Lpu_Name'},
								{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
								{name: 'Lpu_Ouz', mapping: 'Lpu_Ouz'},
								{name: 'Lpu_RegNomC', mapping: 'Lpu_RegNomC'},
								{name: 'Lpu_RegNomC2', mapping: 'Lpu_RegNomC2'},
								{name: 'Lpu_RegNomN2', mapping: 'Lpu_RegNomN2'},
								{name: 'Lpu_DloBegDate', mapping: 'Lpu_DloBegDate'},
								{name: 'Lpu_DloEndDate', mapping: 'Lpu_DloEndDate'},
								{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
								{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'},
								{name: 'LpuLevel_Code', mapping: 'LpuLevel_Code'},
								{name: 'Lpu_IsAccess', mapping: 'Lpu_IsAccess'}
							],
							key: 'Lpu_id',
							sortInfo: {field: 'Lpu_Nick'},
							tableName: 'Lpu',
							url: '/?c=ServiceSUR&m=loadPromedLpuList'
						}),
						width: 450
					}]
				}, {
					layout: 'form',
					labelWidth: 130,
					items: [{
						xtype: 'swdatefield',
						name: 'setDate',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						fieldLabel: lang['data_prosmotra']
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'button',
						handler: function() {
							this.doSearch();
						}.createDelegate(this),
						iconCls: 'search16',
						id: 'SDVW_SearchButton',
						text: BTN_FRMSEARCH,
						style: 'padding-left: 20px;'
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'button',
						handler: function() {
							this.doSearch(true);
						}.createDelegate(this),
						iconCls: 'resetsearch16',
						id: 'SDVW_ResetButton',
						text: BTN_FRMRESET,
						style: 'padding-left: 5px;'
					}]
				}]
			}]
		});

		this.MOInfoTpl = new Ext.XTemplate([
			'<div style="padding:2px;font-size: 12px;"><b>БИН:</b> {BIN}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Полный адрес МО:</b> {FullAddress}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Наименование на каз.:</b> {FullNameKZ}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Наименование на рус.:</b> {FullNameRU}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Идентификатор МО:</b> {ID}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>Код МО:</b> {MedCode}</div>',
			'<div style="padding:2px;font-size: 12px;"><b>РНН:</b> {RNN}</div>'
		]);

		this.MOInfoPanel = new Ext.Panel({
			id: 'SDVW_MOInfoPanel',
			bodyStyle: 'background:#DFE8F6;padding:5px;',
			border: false,
			layout: 'fit',
			html: ''
		});

		this.FPStructureTreePanel = new Ext.tree.TreePanel({
			split: true,
			region: 'west',
			autoScroll: true,
			id: 'SDVW_FPStructureTreePanel',
			width: 260,
			showTree: function() {
				if (!this.getRootNode()) return;
				this.getRootNode().getUI().show();

			},
			clearTree: function(hideRoot) {
				if (!this.getRootNode()) return;

				var root = this.getRootNode();
				var nodes = root.childNodes;

				while(nodes.length != 0) {
					root.removeChild(nodes[nodes.length-1]);
				}

				if (hideRoot) {
					root.getUI().hide();
				}
			},
			/**
			 * Событие при выборе ноды
			 */
			onSelectNode: function(node) {
				var base_form = this.FilterPanel.getForm();

				this.RoomGridPanel.removeAll();
				this.BedGridPanel.removeAll();

				var grid = this.RoomGridPanel.getGrid();

				if (node.attributes.id == 'root') {
					return false;
				}

				grid.getStore().baseParams.Lpu_oid = base_form.findField('Lpu_oid').getValue();
				grid.getStore().baseParams.setDate = base_form.findField('setDate').getRawValue();
				grid.getStore().baseParams.FPID = node.attributes.id;

				grid.getStore().load();
			}.createDelegate(this),
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					/*var node = this.StructureTree.getSelectionModel().selNode;
					if ( node.id == 'root' )
					{
						if ( node.isExpanded() )
							node.collapse();
						else
							node.expand();
						return;
					}
					if ( node.isExpandable() )
					{
						if ( node.isExpanded() )
							node.collapse();
						else
							node.expand();
					}

					this.StructureTree.onSelectNode(node);*/
				}.createDelegate(this),
				stopEvent: true
			}, {
				key: Ext.EventObject.TAB,
				stopEvent: true,
				shift: false,
				fn: function() {
					//this.MedPersonalGrid.getTopToolbar().items.item('FIOFilter').focus();
				}.createDelegate(this)
			},
			{
				key: Ext.EventObject.TAB,
				stopEvent: true,
				shift: true,
				fn: function() {
					//this.SelectLpuCombo.focus();
				}.createDelegate(this)
			}
			],
			root: {
				id: 'root',
				text: lang['koren']//this.params.Lpu_Nick
			},
			title: lang['podrazdeleniya'],
			enableKeyEvents: true,
			listeners: {
				'beforeload': function(node) {
					var base_form = this.FilterPanel.getForm();

					this.FPStructureTreePanel.getLoader().baseParams.Lpu_oid = base_form.findField('Lpu_oid').getValue();

					if (node.attributes.id != 'root') {
						this.FPStructureTreePanel.getLoader().baseParams.ParentID = node.attributes.id;
					}
				}.createDelegate(this),
				'beforeclick': function(node) {
					this.FPStructureTreePanel.onSelectNode(node);
				}.createDelegate(this),
				'dblclick': function(node) {
					if (node.attributes.id == 'root') {
						return;
					}
					this.FPInfoWindow.show();
					this.overwriteFPInfoTpl(node.attributes);
					this.doLayout();
				}.createDelegate(this)
			},
			loader: new Ext.tree.TreeLoader({
				url: '/?c=ServiceSUR&m=loadFPTree'
			})
		});

		this.FPPersonalTreePanel = new Ext.tree.TreePanel({
			split: true,
			region: 'west',
			autoScroll: true,
			id: 'SDVW_FPPersonalTreePanel',
			width: 260,
			showTree: function() {
				if (!this.getRootNode()) return;
				this.getRootNode().getUI().show();
			},
			clearTree: function(hideRoot) {
				if (!this.getRootNode()) return;

				var root = this.getRootNode();
				var nodes = root.childNodes;

				while(nodes.length != 0) {
					root.removeChild(nodes[nodes.length-1]);
				}

				if (hideRoot) {
					root.getUI().hide();
				}
			},
			/**
			 * Событие при выборе ноды
			 */
			onSelectNode: function(node) {
				var base_form = this.FilterPanel.getForm();

				this.PersonalGridPanel.removeAll();
				this.PersonalHistoryGridPanel.removeAll();

				var grid = this.PersonalGridPanel.getGrid();

				if (node.attributes.id == 'root') {
					return false;
				}

				grid.getStore().baseParams.Lpu_oid = base_form.findField('Lpu_oid').getValue();
				grid.getStore().baseParams.setDate = base_form.findField('setDate').getRawValue();
				grid.getStore().baseParams.fp = node.attributes.id;

				grid.getStore().load();
			}.createDelegate(this),
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					/*var node = this.StructureTree.getSelectionModel().selNode;
					 if ( node.id == 'root' )
					 {
					 if ( node.isExpanded() )
					 node.collapse();
					 else
					 node.expand();
					 return;
					 }
					 if ( node.isExpandable() )
					 {
					 if ( node.isExpanded() )
					 node.collapse();
					 else
					 node.expand();
					 }

					 this.StructureTree.onSelectNode(node);*/
				}.createDelegate(this),
				stopEvent: true
			}, {
				key: Ext.EventObject.TAB,
				stopEvent: true,
				shift: false,
				fn: function() {
					//this.MedPersonalGrid.getTopToolbar().items.item('FIOFilter').focus();
				}.createDelegate(this)
			},
				{
					key: Ext.EventObject.TAB,
					stopEvent: true,
					shift: true,
					fn: function() {
						//this.SelectLpuCombo.focus();
					}.createDelegate(this)
				}
			],
			root: {
				id: 'root',
				text: lang['koren']//this.params.Lpu_Nick
			},
			title: lang['podrazdeleniya'],
			enableKeyEvents: true,
			listeners: {
				'beforeload': function(node) {
					var base_form = this.FilterPanel.getForm();

					this.FPPersonalTreePanel.getLoader().baseParams.Lpu_oid = base_form.findField('Lpu_oid').getValue();

					if (node.attributes.id != 'root') {
						this.FPPersonalTreePanel.getLoader().baseParams.ParentID = node.attributes.id;
					}
				}.createDelegate(this),
				'beforeclick': function(node) {
					this.FPPersonalTreePanel.onSelectNode(node);
				}.createDelegate(this),
				'dblclick': function(node) {
					if (node.attributes.id == 'root') {
						return;
					}
					this.FPInfoWindow.show();
					this.overwriteFPInfoTpl(node.attributes);
				}.createDelegate(this)
			},
			loader: new Ext.tree.TreeLoader({
				url: '/?c=ServiceSUR&m=loadFPTree'
			})
		});

		this.RoomGridPanel = new sw.Promed.ViewFrame({
			id: 'SDVW_RoomGridPanel',
			dataUrl: '/?c=ServiceSUR&m=loadRoomGrid',
			border: false,
			autoLoadData: false,
			paging: false,
			title: lang['palatyi'],
			stringfields: [
				{name: 'ID', type: 'string', header: 'ID', key: true},
				{name: 'Number', header: lang['nomer'], type: 'string', width: 60},
				{name: 'Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
				{name: 'SpecNameRu', header: lang['spetsializatsiya'], type: 'string', width: 200},
				{name: 'NameSetRoomRu', header: lang['nazvanie'], type: 'string', width: 120},
				{name: 'SetRoomRu', header: lang['naznachenie'], type: 'string', width: 120},
				{name: 'SexRu', header: lang['polovaya_prinadlejnost'], type: 'string', width: 120},
				{name: 'Child', header: lang['detskoe'], type: 'checkcolumn', width: 60},
				{name: 'Area', header: lang['ploschad'], type: 'float', width: 60},
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			],
			onRowSelect: function(sm, index, record) {
				var base_form = this.FilterPanel.getForm();
				this.BedGridPanel.removeAll();
				if (!record || Ext.isEmpty(record.get('ID'))) {
					return false;
				}
				this.BedGridPanel.getGrid().getStore().load({
					params: {
						'idRoom': record.get('ID'),
						'setDate': base_form.findField('setDate').getRawValue()
					}
				});
			}.createDelegate(this)
		});

		this.BedGridPanel = new sw.Promed.ViewFrame({
			id: 'SDVW_BedGridPanel',
			dataUrl: '/?c=ServiceSUR&m=loadBedGrid',
			border: false,
			autoLoadData: false,
			paging: false,
			title: lang['koyki'],
			stringfields: [
				{name: 'ID', type: 'string', header: 'ID', key: true},
				{name: 'BedType', header: lang['tip'], type: 'string', width: 60},
				{name: 'Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
				{name: 'BedProfileRu', header: lang['profil'], type: 'string', width: 120},
				{name: 'LastProfileDateBeg', header: lang['data_ustanovki_poslednego_profilya'], type: 'date', width: 120},
				{name: 'LastActionRu', header: lang['sostoyanie'], type: 'string', width: 120},
				{name: 'LastActionDateBeg', header: lang['data_ustanovki_poslednego_sostoyaniya'], type: 'date', width: 120},
				{name: 'Temporary', header: lang['vremennaya'], type: 'checkcolumn', width: 80},
				{name: 'StacTypeRu', header: lang['tip_statsionara'], type: 'string', width: 120},
				{name: 'StacDayKindRu', header: lang['prinadlejnost_k_statsionaru'], type: 'string', width: 120},
				{name: 'LastStacTypeDateBeg', header: lang['data_poslednego_statsionara'], type: 'date', width: 120},
				{name: 'TypeSrcFinRu', header: lang['istochnik_finansirovaniya'], type: 'string', width: 120},
				{name: 'LastTypSrcFinDateBeg', header: lang['data_ustanovki_istochnika_finansirovaniya'], type: 'date', width: 120},
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', handler: function() {
					var base_form = this.FilterPanel.getForm();
					var record = this.BedGridPanel.getGrid().getSelectionModel().getSelected();
					if (!record || Ext.isEmpty(record.get('ID'))) {
						return false;
					}
					getWnd('swSURBedHistoryViewWindow').show({
						idBed: record.get('ID'),
						Lpu_oid: base_form.findField('Lpu_oid').getValue()
					});
				}.createDelegate(this)},
				{name:'action_delete', hidden: true}
			]
		});

		this.FPDetailPanel = new Ext.Panel({
			id: 'SDVW_FPDetailPanel',
			layout: 'border',
			region: 'center',
			border: false,
			defaults: {
				split: true
			},
			items: [{
				region: 'south',
				layout: 'fit',
				height: 260,
				items: [this.BedGridPanel]
			}, {
				region: 'center',
				layout: 'fit',
				items: [this.RoomGridPanel]
			}]
		});

		this.PersonalGridPanel = new sw.Promed.ViewFrame({
			id: 'SDVW_PersonalGridPanel',
			dataUrl: '/?c=ServiceSUR&m=loadPersonalGrid',
			border: false,
			autoLoadData: false,
			paging: false,
			stringfields: [
				{name: 'PersonalID', type: 'string', header: 'ID', key: true},
				{name: 'PersonId', type: 'int', hidden: true},
				{name: 'FIO', header: lang['fio'], type: 'string', id: 'autoexpand'},
				{name: 'IIN', header: lang['iin'], type: 'string', width: 100},
				{name: 'PersonalTypeRU', header: lang['tip_personala'], type: 'string', width: 120},
				{name: 'PostCategoryRu', header: lang['kategoriya_doljnosti'], type: 'string', width: 120},
				{name: 'PostFuncRU', header: lang['naimenovanie_doljnosti'], type: 'string', width: 120},
				{name: 'PostCount', header: lang['kol-vo_stavok'], type: 'float', width: 120},
				{name: 'PostTypeRU', header: lang['tip_doljnosti'], type: 'string', width: 120},
				{name: 'SpecialityRU', header: lang['spetsialnost'], type: 'string', width: 120},
				{name: 'StatusPostRu', header: lang['sostoyanie_doljnosti'], type: 'string', width: 120},
				{name: 'TypSrcFinRu', header: lang['istochnik_finansirovaniya'], type: 'string', width: 120},
				{name: 'TypeEmployeeRu', header: lang['vid_vneshtatnoy_rabotyi'], type: 'string', width: 120},
				{name: 'Comment', header: lang['poyasnenie_k_doljnosti'], type: 'string', width: 120}
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			],
			onRowSelect: function(sm, index, record) {
				var base_form = this.FilterPanel.getForm();
				this.PersonalHistoryGridPanel.removeAll();
				if (!record || Ext.isEmpty(record.get('PersonalID'))) {
					return false;
				}
				this.PersonalHistoryGridPanel.getGrid().getStore().load({
					params: {
						//personalId: record.get('PersonId'),
						personalId: record.get('PersonalID'),
						setDate: base_form.findField('setDate').getRawValue(),
						Lpu_oid: base_form.findField('Lpu_oid').getValue()
					}
				});
			}.createDelegate(this)
		});

		this.PersonalHistoryGridPanel = new sw.Promed.ViewFrame({
			id: 'SDVW_PersonalHistoryGridPanel',
			dataUrl: '/?c=ServiceSUR&m=loadPersonalHistoryGrid',
			border: false,
			autoLoadData: false,
			paging: false,
			stringfields: [
				{name: 'ID', type: 'string', header: 'ID', key: true},
				{name: 'BeginDate', header: lang['data_nachala'], type: 'date', width: 120},
				{name: 'EndDate', header: lang['data_okonchaniya'], type: 'date', width: 120},
				{name: 'rsnWorkRu', header: lang['osnovanie_dlya_oformleniya_trudovyih_otnosheniy'], type: 'string', width: 220},
				{name: 'rsnWorkTerminationRu', header: lang['utochnenie_prichinyi_rastorjeniya_trudovogo_dogovora'], type: 'string', width: 220},
				{name: 'Commen', header: lang['primechanie'], type: 'string', id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', hidden: true},
				{name:'action_edit', hidden: true},
				{name:'action_view', hidden: true},
				{name:'action_delete', hidden: true}
			]
		});

		this.PersonalPanel = new Ext.Panel({
			id: 'SDVW_PersonalPanel',
			layout: 'border',
			region: 'center',
			border: false,
			defaults: {
				split: true
			},
			items: [{
				region: 'south',
				layout: 'fit',
				height: 260,
				items: [this.PersonalHistoryGridPanel]
			}, {
				region: 'center',
				layout: 'fit',
				items: [this.PersonalGridPanel]
			}]
		});

		this.TabPanel = new Ext.TabPanel({
			activeTab: 0,
			id: 'SDVW_TabPanel',
			layoutOnTabChange: true,
			region: 'center',
			items: [{
				id: 'MOInfo',
				layout: 'fit',
				title: lang['obschaya_informatsiya_po_mo'],
				items: [this.MOInfoPanel]
			}, {
				id: 'FPStructure',
				layout: 'fit',
				title: lang['funktsionalnaya_struktura'],
				items: [{
					layout: 'border',
					border: false,
					items: [
						this.FPStructureTreePanel,
						this.FPDetailPanel
					]
				}]
			}, {
				id: 'FPPersonalList',
				layout: 'fit',
				title: lang['sotrudniki'],
				items: [{
					layout: 'border',
					border: false,
					items: [
						this.FPPersonalTreePanel,
						this.PersonalPanel
					]
				}]
			}],
			listeners:
			{
				tabchange: function(tab, panel) {
					var base_form = this.FilterPanel.getForm();
					var lpu_combo = base_form.findField('Lpu_oid');

					switch(panel.id) {
						case 'MOInfo':
							break;
						case 'FPStructure':
							if (Ext.isEmpty(lpu_combo.getValue())) {
								this.FPStructureTreePanel.clearTree(true);
							} else {
								this.FPStructureTreePanel.getRootNode().setText(lpu_combo.getFieldValue('Lpu_Nick'));

								if (this.FPStructureTreePanel.getLoader().baseParams.Lpu_oid != lpu_combo.getValue()) {
									this.FPStructureTreePanel.getLoader().baseParams.Lpu_oid = lpu_combo.getValue();
									this.FPStructureTreePanel.getLoader().baseParams.ParentID = null;
									this.FPStructureTreePanel.getLoader().load(this.FPStructureTreePanel.getRootNode());
								}
							}
							break;
						case 'FPPersonalList':
							if (Ext.isEmpty(lpu_combo.getValue())) {
								this.FPPersonalTreePanel.clearTree(true);
							} else {
								this.FPPersonalTreePanel.getRootNode().setText(lpu_combo.getFieldValue('Lpu_Nick'));

								if (this.FPPersonalTreePanel.getLoader().baseParams.Lpu_oid != lpu_combo.getValue()) {
									this.FPPersonalTreePanel.getLoader().baseParams.Lpu_oid = lpu_combo.getValue();
									this.FPPersonalTreePanel.getLoader().baseParams.ParentID = null;
									this.FPPersonalTreePanel.getLoader().load(this.FPPersonalTreePanel.getRootNode());
								}
							}
							break;
					}
					this.doLayout();
				}.createDelegate(this)
			}
		});

		Ext.apply(this,{
			buttons: [
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [
				this.FilterPanel,
				this.TabPanel
			]
		});

		sw.Promed.swSURDataViewWindow.superclass.initComponent.apply(this, arguments);
	}
});