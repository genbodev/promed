/**
 * swCookWorkPlaceWindow - окно рабочего места работника пищеблока
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Cook
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2013
 */

sw.Promed.swCookWorkPlaceWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	enableDefaultActions: false,
	id: 'swCookWorkPlaceWindow',
	showToolbar: false,
	buttonPanelActions: {
		action_FoodStuff:
		{
			nn: 'action_FoodStuff',
			tooltip: lang['spravochnik_produktov'],
			text: lang['spravochnik_produktov'],
			iconCls: 'report32',
			disabled: false,

			handler: function() {
				getWnd('swFoodStuffViewWindow').show();
			}
		},
		action_Okei:
		{
			nn: 'action_Okei',
			tooltip: lang['spravochnik_okei'],
			text: lang['spravochnik_okei'],
			iconCls: 'report32',
			disabled: false,

			handler: function() {
				getWnd('swOkeiViewWindow').show();
			}
		}
	},

	openFoodCookEditForm: function(action) {
		if ( Ext.isEmpty(action) || !action.toString().inlist([ 'add', 'edit', 'view' ]) ) {
			return false;
		}

		if ( getWnd('swFoodCookEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_retsepta_blyuda_uje_otkryito']);
			return false;
		}

		var grid = this.GridPanel.getGrid();
		var params = new Object();

		if ( action == 'add' ) {
			params.FoodCook_id = 0;
		}
		else {
			var selected_record = grid.getSelectionModel().getSelected();

			if ( typeof selected_record != 'object' || Ext.isEmpty(selected_record.get('FoodCook_id')) ) {
				return false;
			}

			params.FoodCook_id = selected_record.get('FoodCook_id');
		}

		getWnd('swFoodCookEditWindow').show({
			action: action,
			callback: function(data) {
				this.GridPanel.ViewActions.action_refresh.execute();
			}.createDelegate(this),
			formParams: params,
			onHide: function() {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}.createDelegate(this)
		});
	},

	show: function()
	{
		sw.Promed.swCookWorkPlaceWindow.superclass.show.apply(this, arguments);

		with ( this.LeftPanel.actions ) {
			action_RLS.setHidden(true);
			action_Mes.setHidden(true);
			action_Report.setHidden(true);
		}

		this.GridPanel.setParam('start', 0);
	},

	initComponent: function()
	{
		var form = this;

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			filter: {
				title: lang['filtr'],
				layout: 'form',
				items: [{
					layout: 'column',
					labelWidth: 129,
					items: [{
						border: false,
						layout: 'form',
						labelWidth: 45,
						items: [{
							fieldLabel: lang['kod'],
							listeners: {
								'keydown': function (f, e) {
									if ( e.getKey() == e.ENTER ) {
										this.doSearch();
										this.GridPanel.setParam('start', 0);
									}
								}.createDelegate(this)
							},
							maxLength: 5,
							name: 'FoodStuff_Code',
							width: 175,
							xtype: 'textfield'
						}]
					}, {
						border: false,
						layout: 'form',
						//labelWidth: 100,
						items: [{
							fieldLabel: lang['naimenovanie'],
							listeners: {
								'keydown': function (f, e) {
									if ( e.getKey() == e.ENTER ) {
										this.doSearch();
										this.GridPanel.setParam('start', 0);
									}
								}.createDelegate(this)
							},
							maxLength: 100,
							name: 'FoodStuff_Name',
							width: 175,
							xtype: 'textfield'
						}]
					}, {
						bodyStyle: 'padding-left: 5px;',
						border: false,
						layout: 'form',
						items: [{
							disabled: false,
							handler: function () {
								this.doSearch();
								this.GridPanel.setParam('start', 0);
							}.createDelegate(this),
							minWidth: 125,
							text: lang['ustanovit_filtr'],
							topLevel: true,
							xtype: 'button'
						}]
					}, {
						bodyStyle: 'padding-left: 5px;',
						border: false,
						layout: 'form',
						items: [{
							disabled: false,
							handler: function () {
								this.doReset();
							}.createDelegate(this),
							minWidth: 125,
							text: lang['snyat_filtr'],
							topLevel: true,
							xtype: 'button'
						}]
					}]
				}]
			}
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { form.openFoodCookEditForm('add'); } },
				{ name: 'action_edit', handler: function() { form.openFoodCookEditForm('edit'); } },
				{ name: 'action_view', handler: function() { form.openFoodCookEditForm('view'); } },
				{ name: 'action_delete', handler: function() {} },
				{ name: 'action_print', disabled: true },
				{ name: 'action_refresh' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=FoodCook&m=loadFoodCookGrid',
			id: 'FoodCookGridPanel',
			layout: 'fit',
			object: 'FoodCook',
			onRowSelect: function(sm, index, record) {
				//
			},
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'FoodCook_id', type: 'int', header: 'ID', key: true },
				{ name: 'FoodCook_Code', type: 'string', header: lang['kod'], width: 100 },
				{ name: 'FoodCook_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' },
				{ name: 'FoodCook_Caloric', type: 'float', header: lang['kaloriynost'], width: 100, renderer: twoDecimalsRenderer },
				{ name: 'FoodCook_Protein', type: 'float', header: lang['soderjanie_belkov'], width: 100, renderer: twoDecimalsRenderer },
				{ name: 'FoodCook_Carbohyd', type: 'float', header: lang['soderjanie_uglevodov'], width: 100, renderer: twoDecimalsRenderer },
				{ name: 'FoodCook_Fat', type: 'float', header: lang['soderjanie_jirov'], width: 100, renderer: twoDecimalsRenderer },
				{ name: 'FoodCook_Time', type: 'int', header: lang['vremya_prigotovleniya'], width: 100 },
				{ name: 'FoodCook_Mass', type: 'string', header: lang['massa_gotovogo_blyuda'], width: 100 }
			],
			toolbar: true,
			totalProperty: 'totalCount'
		});

		sw.Promed.swCookWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});
