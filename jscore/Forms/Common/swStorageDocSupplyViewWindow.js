/**
* swStorageDocSupplyViewWindow - окно Контракты склада
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2017 Swan Ltd.
* @author       Alexander Kurakin
* @version      02.2017
* @comment      
*/
sw.Promed.swStorageDocSupplyViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Контракты склада',
	layout: 'border',
	id: 'StorageDocSupplyViewWindow',
	modal: true,
	shim: false,
	width: 750,
	height: 500,
	resizable: false,
	maximizable: true,
	maximized: false,
	doSearch: function() {
		var wnd = this;
		var params = new Object();

		wnd.SearchGrid.removeAll();
		params.Storage_id = this.Storage_id;
		wnd.SearchGrid.getGrid().getStore().baseParams.Storage_id = this.Storage_id;
		wnd.SearchGrid.loadData({params: params, globalFilters: params});
	},
	doReset: function() {
		var wnd = this;
		wnd.SearchGrid.removeAll();
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swStorageDocSupplyViewWindow.superclass.show.apply(this, arguments);		
		this.Storage_id = null;	
		if(arguments[0].Storage_id){
			this.Storage_id = arguments[0].Storage_id;
		}
		wnd.document_combo.getStore().load();
		this.findById('LinkAddButton').disable();
		this.doReset();
		this.doSearch();
	},
	saveStorageDocSupplyLink: function(params) {
		var wnd = this;
		var prms = {
			Storage_id: this.Storage_id,
			WhsDocumentSupply_id: params.WhsDocumentSupply_id
		};
		if(params.StorageDocSupplyLink_id){
			prms.StorageDocSupplyLink_id = params.StorageDocSupplyLink_id;
		}
		var loadMask = new Ext.LoadMask(this.FilterPanel.getEl(), {msg:'Сохранение...'});
		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=StorageZone&m=saveStorageDocSupplyLink',
			params:prms,
			success: function (response)
			{
				loadMask.hide();
				var result = Ext.util.JSON.decode(response.responseText);
				wnd.SearchGrid.getGrid().getStore().load();
				if(result.AlertMsg){
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId, text, obj) {
							if (buttonId == 'yes') {
								prms.StorageDocSupplyLink_id = result.StorageDocSupplyLink_id;
								wnd.saveStorageDocSupplyLink(prms);
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: result.AlertMsg,
						title: lang['prodoljit_sohranenie']
					});
					return false;
				}
				wnd.document_combo.setValue('');
				wnd.findById('LinkAddButton').disable();
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
				this.hide();
			}.createDelegate(this)
		});	
	},
	deleteStorageDocSupplyLink: function() {
		var wnd = this;
		var record = this.SearchGrid.getGrid().getSelectionModel().getSelected();
		if(Ext.isEmpty(record)){
			Ext.Msg.alert('Ошибка', 'Не выбран контракт');
			return false;
		}
		var prms = {
			StorageDocSupplyLink_id: record.get('StorageDocSupplyLink_id')
		};
		var loadMask = new Ext.LoadMask(this.FilterPanel.getEl(), {msg:'Удаление...'});
		loadMask.show();
		Ext.Ajax.request({
			url: '/?c=StorageZone&m=deleteStorageDocSupplyLink',
			params:prms,
			success: function (response)
			{
				loadMask.hide();
				var result = Ext.util.JSON.decode(response.responseText);
				wnd.SearchGrid.getGrid().getStore().load();
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
				this.hide();
			}.createDelegate(this)
		});	
	},
	initComponent: function() {
		var wnd = this;

		wnd.document_combo = new sw.Promed.SwDrugComplexMnnCombo({
			width: 525,
			allowBlank: true,
			displayField: 'WhsDocumentUc_Num',
			enableKeyEvents: true,
			fieldLabel: lang['kontrakt'],
			forceSelection: true,
			hiddenName: 'WhsDocumentUc_id',
			loadingText: lang['idet_poisk'],
			queryDelay: 250,
			minChars: 1,
			minLength: 1,
			mode: 'remote',
			trigger2Class: 'x-form-search-trigger',
			trigger3Class: '',
			resizable: true,
			selectOnFocus: true,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="width:100%;border: 0;"><td style="width:80%;"><h3>{WhsDocumentUc_Num}</h3></td><td style="width:20%;">&nbsp;</td></tr></table>',
				'</div></tpl>'
			),
			triggerAction: 'all',
			valueField: 'WhsDocumentUc_id',
			listeners: {
				keydown: function(combo, e) {
					if ( e.getKey() == e.DELETE)
					{
						combo.setValue(null);
						if (combo.allowBlank) {
							var record = combo.getStore().getAt(0);
						}
						e.stopEvent();
						return true;
					}

					if (e.getKey() == e.F4)
					{
						combo.onTrigger2Click();
					}
				}.createDelegate(this),
				select: function(combo,newValue){
					combo.fireEvent('change',combo,combo.getValue());
				}.createDelegate(this),
				change: function(combo,newValue){
					if(Ext.isEmpty(newValue)){
						this.findById('LinkAddButton').disable();
					} else {
						this.findById('LinkAddButton').enable();
					}
				}.createDelegate(this)
			},
			onTrigger2Click: function() {
				if (this.disabled) {
                    return false;
                }

				var params = this.getStore().baseParams;
				var combo = this;
				combo.disableBlurAction = true;
				getWnd('swWhsDocumentSupplySelectWindow').show({
					params: params,
					searchUrl: '/?c=Farmacy&m=loadWhsDocumentSupplyList',
					FilterPanelEnabled: true,
					onHide: function() {
						combo.focus(false);
						combo.disableBlurAction = false;
					},
					onSelect: function (data) {
						combo.fireEvent('beforeselect', combo);

						combo.getStore().removeAll();
						combo.getStore().loadData([{
							WhsDocumentUc_id: data.WhsDocumentUc_id,
							WhsDocumentSupply_id: data.WhsDocumentSupply_id,
							WhsDocumentUc_Name: data.WhsDocumentUc_Name,
							WhsDocumentUc_Date: data.WhsDocumentUc_Date,
							WhsDocumentUc_Num: data.WhsDocumentUc_Num,
							WhsDocumentType_Code: data.WhsDocumentType_Code,
							Contragent_sid: data.Contragent_sid,
							DrugFinance_id: data.DrugFinance_id,
							WhsDocumentCostItemType_id: data.WhsDocumentCostItemType_id
						}], true);

						combo.setValue(data.WhsDocumentUc_id);
						var index = combo.getStore().findBy(function(rec) { return rec.get('WhsDocumentUc_id') == data.WhsDocumentUc_id; });

						if (index == -1) {
							return false;
						}

						var record = combo.getStore().getAt(index);

						if ( typeof record == 'object' ) {
							combo.fireEvent('select', combo, record, 0);
							combo.fireEvent('change', combo, record.get('WhsDocumentUc_id'));
						}
						getWnd('swWhsDocumentSupplySelectWindow').hide();
					}
				});
			},
			resetCombo: function() {
				this.lastQuery = '';
				this.getStore().removeAll();
				this.getStore().baseParams.query = '';
			},
			setValueById: function(document_id) {
				var combo = this;
				combo.store.baseParams.WhsDocumentUc_id = document_id;
				combo.store.load({
					callback: function(){
						combo.setValue(document_id);
						combo.store.baseParams.WhsDocumentUc_id = null;
					}
				});
			},
			initComponent: function() {
				sw.Promed.SwDrugComplexMnnCombo.prototype.initComponent.apply(this, arguments);

                this.triggerConfig = {
                    tag:'span',
                    cls:'x-form-twin-triggers',
                    cn:[
                        {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger1Class},
                        {tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger " + this.trigger2Class}
                    ]
                };

				this.store = new Ext.data.Store({
					autoLoad: false,
					reader: new Ext.data.JsonReader({
							id: 'WhsDocumentUc_id'
						},
						[
							{name: 'WhsDocumentUc_id', mapping: 'WhsDocumentUc_id'},
							{name: 'WhsDocumentType_Code', mapping: 'WhsDocumentType_Code'},
							{name: 'WhsDocumentSupply_id', mapping: 'WhsDocumentSupply_id'},
							{name: 'WhsDocumentUc_Name', mapping: 'WhsDocumentUc_Name'},
							{name: 'WhsDocumentUc_Date', mapping: 'WhsDocumentUc_Date'},
							{name: 'WhsDocumentUc_Num', mapping: 'WhsDocumentUc_Num'},
							{name: 'Contragent_sid', mapping: 'Contragent_sid'},
							{name: 'DrugFinance_id', mapping: 'DrugFinance_id'},
							{name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id'}
						]),
					url: '/?c=Farmacy&m=loadWhsDocumentSupplyList'
				});
			}
		});

		this.FilterCommonPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 70,
			border: false,
			frame: true,
			items: [{
				layout : 'column',
				items:[
					{
						layout : 'form',
						items : [wnd.document_combo]
					},
					{
						layout : 'form',
						items : [{
							xtype : 'button',
							style : 'margin: 0px 2px 0px 3px;',
							id : 'LinkAddButton',
							text : BTN_GRIDADD,
							iconCls : 'add16',
							handler : function() {
								var wnd = this;
								var doc = wnd.document_combo.getValue();
								if(Ext.isEmpty(doc)){
									Ext.Msg.alert('Ошибка', 'Не выбран контракт');
									return false;
								}
								this.saveStorageDocSupplyLink({WhsDocumentSupply_id:doc});
							}.createDelegate(this)
						}]
					}
				]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterCommonPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', handler: function(){this.deleteStorageDocSupplyLink();}.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=StorageZone&m=loadStorageDocSupplyList',
			height: 180,
			object: 'StorageDocSupply',
			id: wnd.id+'StorageDocSupplyGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'StorageDocSupplyLink_id', type: 'int', header: 'ID', key: true },
				{ name: 'WhsDocumentSupply_id', type: 'int', hidden: true },
				{ name: 'WhsDocumentUc_Num', type: 'string', header: 'Номер', width:150 },
				{ name: 'WhsDocumentUc_Date', type: 'date', header: 'Дата', width:100 },
				{ name: 'WhsDocumentUc_Year', type: 'int', header: 'Год', width:100 },
				{ name: 'WhsDocumentUc_Sum', type: 'string', header: 'Сумма', width:150 },
				{ name: 'Org_Nick', type: 'string', header: 'Поставщик', id: 'autoexpand' }
			],
			title: null,
			toolbar: true
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				wnd.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					items:[{
						border: false,
						region: 'center',
						layout: 'fit',
						items: [this.SearchGrid]
					}]
				}
			]
		});
		sw.Promed.swStorageDocSupplyViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});