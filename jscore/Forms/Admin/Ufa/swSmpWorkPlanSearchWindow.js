/**
* swSmpWorkPlanSearchWindow - СМП: окно поиска планов выхода на смену автомобилей и бригад
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @author       Магафуров Салават
* @version      12.2017
*
*/

sw.Promed.swSmpWorkPlanSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSmpWorkPlanSearchWindow',
	title: langs('СМП. План выхода на смену автомобилей и бригад'),
	width: 1200,
	height: 700,
	buttonAlign: 'left',
	modal: false,
	maximized: true,
	closable: true,
	plain: true,
	closeAction: 'hide',
	layout: 'border',
	draggable: true,
	resizable: false,

	doSearch: function() {
		var searchGridStore = this.SearchGrid.getGrid().getStore();
		var filterForm = this.FiltersPanel.getForm();

		searchGridStore.baseParams = filterForm.getValues();
		searchGridStore.baseParams.Lpu_id = filterForm.findField('Lpu_id').getValue();
		searchGridStore.load();

	},

	show: function() {
		sw.Promed.swSmpWorkPlanSearchWindow.superclass.show.apply(this, arguments);

		var baseForm = this.FiltersPanel.getForm();
		var lpu = baseForm.findField('Lpu_id');
		var lpuBuilding = baseForm.findField('LpuBuilding_id');
		lpu.setValue( getGlobalOptions().lpu_id );
		lpu.setRawValue( getGlobalOptions().lpu_nick );
		lpu.setDisabled( getGlobalOptions().lpu_id != 150011 );
		lpu.addListener('change', function(combo,newValue,oldValue) {
			lpuBuilding.getStore().baseParams.Lpu_id = lpu.getValue();
			lpuBuilding.getStore().load();
		})
		this.doSearch();
	},

	openWindow: function(action) {
		var wnd = this;
		var row = this.SearchGrid.getGrid().getSelectionModel().getSelected();

		var params = new Object();
		params.action = action;
		params.LpuBuilding_id 		= row.get('LpuBuilding_id');
		params.CmpWorkPlan_BegDT 	= row.get('CmpWorkPlan_BegDT');
		params.CmpWorkPlan_EndDT	= row.get('CmpWorkPlan_EndDT');
		params.CmpWorkPlan_id		= row.get('CmpWorkPlan_id');
		params.Lpu_id				= row.get('Lpu_id');
		params.callback = function() {
			if(this.action == 'add')
				wnd.SearchGrid.getGrid().getStore().reload();
		}

		getWnd('swSmpWorkPlanEditWindow').show(params);
	},

	deletePlan: function() {
		var wnd = this;
		Ext.Ajax.request({
			url: '/?c=CmpWorkPlan&m=delWorkPlan',
			params: {
				'CmpWorkPlan_id': wnd.SearchGrid.getGrid().getSelectionModel().getSelected().get('CmpWorkPlan_id')
			},
			callback: function (options, success, response) {
				if (success === true) {
					wnd.doSearch();
				}
				else {
					sw.swMsg.alert('Ошибка', '');
				}
			}
		});
	},

	initComponent: function() {

		var wnd = this;

		wnd.FiltersPanel =  new Ext.form.FormPanel({
			id: wnd.id + '_FiltersPanel',
			autoHeight: true,
			tabPanelHeight: 250,
			region: 'north',
			frame: false,
			items: [
				new sw.Promed.Panel({
					bodyStyle: 'padding: 5px',
					autoHeight: true,
					border: true,
					collapsible: true,
					title: langs('Нажмите на заголовок чтобы свернуть/развернуть панель фильтров'),
					region: 'center',
					defaults: {
						border: false
					},
					items: [
						{
							layout: 'column',
							defaults: {
								border: false,
								layout: 'form',
								columnWidth:0.2,
								labelWidth: 100
							},
							items: [{
								defaults: {
									width: 200,
									labelWidth: 200
								},
								items: [{
									hiddenName: 'Lpu_id',
									fieldLabel: 'МО',
									xtype: 'swbaselocalcombo',
									valueField: 'Lpu_id',
									displayField: 'Lpu_Nick',
									tpl: new Ext.XTemplate(
										'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: left;">',
										'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
										'<td style="padding: 2px;">{Lpu_Nick}&nbsp;</td>',
										'</tr></tpl>',
										'</table>'
									),
									store: new Ext.data.JsonStore({
										autoLoad: true,
										url: '/?c=CmpWorkPlan&m=getLpuList',
										key: 'Lpu_id',
										fields: [
											{ name: 'Lpu_id', type: 'int' },
											{ name: 'Lpu_Nick', type: 'string' }
										],
										listeners: {
											load: function(store) {
												log('load');
												var baseForm = wnd.FiltersPanel.getForm();
												var Lpu = baseForm.findField('Lpu_id');
												Lpu.setValue( Lpu.getValue() );
											}
										}
									})
								}, {
									hiddenName: 'LpuBuilding_id',
									xtype: 'swbaselocalcombo',
									fieldLabel: 'Подстанция',
									listWidth: 380,
									displayField: 'name',
									valueField: 'id',
									tpl: new Ext.XTemplate(
										'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: left;">',
										'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
										'<td style="padding: 2px;">{name}&nbsp;</td>',
										'</tr></tpl>',
										'</table>'
									),
									store: new Ext.data.JsonStore({
										autoLoad: true,
										url: '/?c=CmpWorkPlan&m=getSubstationList',
										key: 'id',
										baseParams: { Lpu_id: getGlobalOptions().lpu_id },
										fields: [
											{ name: 'id', type: 'int' },
											{ name: 'name', type: 'string' }
										]
									})
								}]
							},{ 
								items: [{
									name: 'CmpWorkPlan_BegDT_Range',
									xtype: 'daterangefield',
									fieldLabel: 'Дата начала',
									width: 200
								},{
									name: 'CmpWorkPlan_EndDT_Range',
									xtype: 'daterangefield',
									fieldLabel: 'Дата окончания',
									width: 200
								}]
							}]
						}
					],
					listeners: {
						collapse: function(p) {
							wnd.doLayout();
							wnd.syncSize();
						},
						expand: function(p) {
							wnd.doLayout();
							wnd.syncSize();
						}
					}
				})
			]
		});

		wnd.SearchGrid = new sw.Promed.ViewFrame({
			title: 'План выхода автомобилей и бригад',
			id: wnd.id + 'PlanGrid',
			dataUrl: '/?c=CmpWorkPlan&m=getWorkPlans',
			autoLoadData: false,
			region: 'center',
			style: 'padding-top: 5px',
			border: true,
			paging: true,
			pageSize: 100,
			stringfields: [
				{ name: 'CmpWorkPlan_id', type: 'int', hidden: true},
				{ name: 'LpuBuilding_id', type: 'int', hidden: true},
				{ name: 'LpuBuilding_Name', type: 'string', header: 'Подстанция', autoexpand: true },
				{ name: 'CmpWorkPlan_BegDT', header: 'Дата начала', type: 'date' },
				{ name: 'CmpWorkPlan_EndDT', header: 'Дата окончания', type: 'date' },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'Lpu_Nick', type: 'string', header: 'МО'}
			],
			actions: [
				{ name: 'action_add', handler: function(){ wnd.openWindow('add') }},
				{ name: 'action_edit', handler: function(){ wnd.openWindow('edit') }},
				{ name: 'action_view', handler: function(){ wnd.openWindow('view') }},
				{ name: 'action_delete', handler: function(){ wnd.deletePlan() } },
				{ name: 'action_refresh'},
				{ name: 'action_print', hidden: true }
			]
		});

		wnd.SearchGrid.getGrid().addListener('rowclick', function(grid, rowindex, event){
			var row = grid.getStore().getAt(rowindex);
			wnd.PlanData.getStore().load({params: {CmpWorkPlan_id: row.data.CmpWorkPlan_id}});
		})

		wnd.PlanData = new Ext.grid.EditorGridPanel({
			title: 'Состав плана',
			border: true,
			region: 'south',
			style: 'padding-top: 5px',
			height: 250,
			loadMask: true,
			store: new Ext.data.JsonStore({
				url: '/?c=CmpWorkPlan&m=getWorkPlan',
				autoLoad: false,
				key: 'WorkPlan_id',
				fields: [
					{name: 'CmpWorkPlanData_id', type: 'int',hidden: true},
					{name: 'LpuBuilding_id', type: 'int'},
					{name: 'CmpPlanType_id', type: 'int'},
					{name: 'PlanType_Name', type: 'string'},
					{name: 'CmpWorkTime_id', type: 'int'},
					{name: 'WorkTime_Name', type: 'string'},
					{name: 'BrigadeCount', type: 'int'}
				]
			}),
			cm: new Ext.grid.ColumnModel([
				{ dataIndex: 'LpuBuilding_id', type: 'int', hidden: true},
				{ dataIndex: 'WorkPlan_id', type:'int', hidden:true },
				{ dataIndex: 'CmpPlanType_id', type:'int', hidden:true },
				{ dataIndex: 'CmpWorkTime_id', type:'int', hidden:true },
				{
					header: "Вид плана",
					dataIndex: 'PlanType_Name',
					sortable: true,
					menuDisabled: true,
					width: 150
				},
				{
					header: "Время суток",
					dataIndex: 'WorkTime_Name',
					sortable: true,
					menuDisabled: true,
					width: 150
				},
				{
					id: 'BrigadeCount',
					header: "Количество автомобилей/бригад",
					dataIndex: 'BrigadeCount',
					sortable: true,
					menuDisabled: true,
					allowBlank: false,
					width: 200,
					renderer: function(value, meta) {
						if(Ext.isEmpty(value)) {
							value = "";
							meta.css += 'x-grid-cell-invalid';
						}
						return '<div style="text-align: right; width:100%;">'+value+'</>';
					}
				}
			]),
			stripeRows: true
		});

		Ext.apply(wnd, {
			items: [
				wnd.FiltersPanel,
				wnd.SearchGrid,
				wnd.PlanData
			],
			buttons: [{
				text: BTN_FRMSEARCH,
				iconCls: 'search16',
				handler: function() {
					wnd.doSearch();
				}
			}, {
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET,
				handler: function() {
					filterForm = wnd.FiltersPanel.getForm();
					filterForm.reset();
					lpu = filterForm.findField('Lpu_id');
					lpu.setValue( getGlobalOptions().lpu_id );
					lpu.setRawValue ( getGlobalOptions().lpu_nick );
				}
			}, '-', HelpButton(wnd), {
				iconCls: 'close16',
				onTabElement: 'rifOk',
				text: BTN_FRMCLOSE,
				handler: function() {
					wnd.hide();
				}
			}]
		});

		sw.Promed.swSmpWorkPlanSearchWindow.superclass.initComponent.apply(wnd, arguments);
	}
});