/**
 * swRefValuesSetEditWindow - окно загрузки/сохранения наборов референсных значений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      19.01.2014
 * @comment
 */
sw.Promed.swRefValuesSetEditWindow = Ext.extend(sw.Promed.BaseForm,	{
	maximized: false,
	objectName: 'swRefValuesSetEditWindow',
	objectSrc: '/jscore/Forms/Admin/swRefValuesSetEditWindow.js',
	title: lang['nabor_referensnyih_znacheniy'],
	layout: 'border',
	id: 'RefValuesSetEditWindow',
	modal: true,
	shim: false,
	width: 700,
	height: 400,
	resizable: false,
	show: function()
	{
		var win = this;
		sw.Promed.swRefValuesSetEditWindow.superclass.show.apply(this, arguments);
		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.onLoadSave = Ext.emptyFn;
		this.AnalyzerTest_id = null;
		
		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { win.hide(); });
			return false;
		}
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazan_obyazatelnyiy_parametr_-_action'], function() { win.hide(); });
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onLoadSave && typeof arguments[0].onLoadSave == 'function' ) {
			this.onLoadSave = arguments[0].onLoadSave;
		}

		this.AnalyzerTest_id = arguments[0].AnalyzerTest_id || null;
		
		this.AnalyzerTest_IsTest = 2;
		if (arguments[0].AnalyzerTest_IsTest) {
			this.AnalyzerTest_IsTest = arguments[0].AnalyzerTest_IsTest;
		}
		
		win.RefValuesSetGrid.loadData({params:{AnalyzerTest_id: win.AnalyzerTest_id}, globalFilters:{AnalyzerTest_id: win.AnalyzerTest_id}});
		
		win.buttons[0].hide();
		
		switch (this.action) {
			case 'load':
				win.setTitle(lang['nabor_referensnyih_znacheniy_zagruzka']);
				win.buttons[0].show();
				win.RefValuesSetGrid.setActionHidden('action_add', true);
				win.RefValuesSetGrid.setActionHidden('action_edit', true);
				break;
			case 'save':
				win.setTitle(lang['nabor_referensnyih_znacheniy_sohranenie']);
				win.RefValuesSetGrid.setActionHidden('action_add', false);
				win.RefValuesSetGrid.setActionHidden('action_edit', false);
				break;
		}
	},
	showRefValues: function()
	{
		var win = this;
		var grid = win.RefValuesSetGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();

		if (selected_record && selected_record.get('RefValuesSet_id')) {
			var records = getStoreRecords(grid.getStore());
			
			getWnd('swRefValuesSetViewWindow').show({
				RefValuesSet_id: selected_record.get('RefValuesSet_id'),
				RefValuesSet_Name: selected_record.get('RefValuesSet_Name'),
				AnalyzerTest_IsTest: win.AnalyzerTest_IsTest,
				records: records
			});
		}
	},
	initComponent: function()
	{
		var win = this;
		
		// наборы референсных значений
		this.RefValuesSetGrid = new sw.Promed.ViewFrame({
			focusOnFirstLoad: true,
			actions: [
				{name: 'action_add', text: lang['dobavit'], icon: 'img/icons/save16.png',
					handler:function () {
						sw.swMsg.prompt(lang['nazvanie_nabora'], lang['vvedite_nazvanie_novogo_nabora_referentnyih_znacheniy'],
							function(btnId, newValue){
								if (btnId != 'ok') {
									return false;
								}
								
								win.getLoadMask(lang['sohranenie_nabora_referensnyih_znacheniy']).show();
								Ext.Ajax.request({
									url: '/?c=RefValuesSet&m=saveRefValuesSet',
									params:{
										AnalyzerTest_id: win.AnalyzerTest_id,
										RefValuesSet_Name: newValue
									},
									success: function(result) {
										win.getLoadMask().hide();
										if ( result.responseText.length > 0 ) {
											var resp_obj = Ext.util.JSON.decode(result.responseText);
											if (resp_obj.success == true) {
												win.onLoadSave(newValue);
												// обновить грид наборов референсных значений
												win.RefValuesSetGrid.getGrid().getStore().reload();
											}
										}
									},
									failure: function() {
										win.getLoadMask().hide();
									}
								});
							},
							this,
							false,
							''
						);
					}
				},
				{name: 'action_edit', text: lang['perezapisat'], icon: 'img/icons/save16.png',
					handler:function () {
						var grid = win.RefValuesSetGrid.getGrid();
						var selected_record = grid.getSelectionModel().getSelected();
						if (selected_record && selected_record.get('RefValuesSet_id')) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									if ( buttonId == 'yes' )
									{
										win.getLoadMask(lang['sohranenie_nabora_referensnyih_znacheniy']).show();
										Ext.Ajax.request({
											url: '/?c=RefValuesSet&m=resaveRefValuesSet',
											params:{
												RefValuesSet_id: selected_record.get('RefValuesSet_id')
											},
											success: function() {
												win.getLoadMask().hide();
												win.onLoadSave(selected_record.get('RefValuesSet_Name'));
												// обновить грид наборов референсных значений
												win.RefValuesSetGrid.getGrid().getStore().reload();
											},
											failure: function() {
												win.getLoadMask().hide();
											}
										});
									}
								},
								msg: lang['perezapisat_suschestvuyuschiy_nabor_referensnyih_znacheniy'],
								title: lang['podtverjdenie']
							});
						}
					}
				},
				{name: 'action_view', text: lang['prosmotr'],
					handler:function () {
						win.showRefValues();
					}
				},
				{name: 'action_delete',
					handler:function () {
						var grid = win.RefValuesSetGrid.getGrid();
						var selected_record = grid.getSelectionModel().getSelected();
						
						if (selected_record && selected_record.get('RefValuesSet_id')) {
							win.getLoadMask(lang['udalenie_nabora_referensnyih_znacheniy']).show();
							Ext.Ajax.request({
								url: '/?c=RefValuesSet&m=delete',
								params:{
									RefValuesSet_id: selected_record.get('RefValuesSet_id')
								},
								success: function() {
									win.getLoadMask().hide();
									// обновить грид наборов референсных значений
									win.RefValuesSetGrid.getGrid().getStore().reload();
								},
								failure: function() {
									win.getLoadMask().hide();
								}
							});
						}
					}
				},
				{name: 'action_print', hidden: true, disabled: true}
			],
			autoExpandColumn: 'autoexpand',
			region: 'center',
			autoExpandMin: 150,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=RefValuesSet&m=loadList',
			object: 'RefValuesSet',
			uniqueId: true,
			scheme: 'lis',
			paging: false,
			onTabAction: function() {
				if (win.buttons[0].hidden) {
					win.buttons[2].focus();
				} else {
					win.buttons[0].focus();
				}
			},
			onDblClick: function()
			{
				win.showRefValues();
			},
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'RefValuesSet_id', type: 'int', header: 'ID', key: true},
				{name: 'RefValuesSet_Name', type: 'string', header: lang['naimenovanie_nabora'], autoexpand: true},
				{name: 'RefValuesSet_insDT', header: lang['data_sozdaniya'], type: 'date', width: 100},
				{name: 'RefValuesSet_updDT', header: lang['data_izmeneniya'], type: 'date', width: 100},
				{name: 'pmUser_Name', header: lang['polzovatel'], type: 'string', width: 100}
			],
			title: '',
			toolbar: true
		});
		
		var form = new Ext.Panel({
			autoScroll: true,
			region: 'center',
			bodyBorder: false,
			border: false,
			layout: 'border',
			frame: false,
			labelAlign: 'right',
			items: [
				win.RefValuesSetGrid
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler:function () {
					var grid = win.RefValuesSetGrid.getGrid();
					var selected_record = grid.getSelectionModel().getSelected();
					
					if (selected_record && selected_record.get('RefValuesSet_id')) {
						win.getLoadMask(lang['zagruzka_nabora_referensnyih_znacheniy']).show();
						Ext.Ajax.request({
							url: '/?c=RefValuesSet&m=loadRefValuesSet',
							params:{
								RefValuesSet_id: selected_record.get('RefValuesSet_id')
							},
							success: function() {
								win.getLoadMask().hide();
								win.onLoadSave(selected_record.get('RefValuesSet_Name'));
								// обновить грид референсных значений
								win.callback();
								win.hide();
							},
							failure: function() {
								win.getLoadMask().hide();
							}
						});
					}
				},
				text: lang['zagruzit']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					win.hide();
				},
				onTabAction: function() {
					if (win.RefValuesSetGrid.getGrid().getTopToolbar().items.items[0].hidden) {
						win.RefValuesSetGrid.getGrid().getTopToolbar().items.items[2].focus();
					} else {
						win.RefValuesSetGrid.getGrid().getTopToolbar().items.items[0].focus();
					}
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		
		sw.Promed.swRefValuesSetEditWindow.superclass.initComponent.apply(this, arguments);
	}
});