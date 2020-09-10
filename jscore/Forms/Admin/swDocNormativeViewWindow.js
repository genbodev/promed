/**
 * swDocNormativeViewWindow - окно просмотра списка нормативных документов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			18.02.2016
 */

/*NO PARSE JSON*/

sw.Promed.swDocNormativeViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDocNormativeViewWindow',
	layout: 'border',
	maximized: true,
	maximizable: false,
	title: 'Справочник нормативных документов',

	openEditWindow: function(action, gridPanel) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var grid = gridPanel.getGrid();
		var idFieldName = gridPanel.object+'_id';

		var params = {action: action};
		params.callback = function() {
			gridPanel.getAction('action_refresh').execute();
		};
		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();

			if (!record || Ext.isEmpty(record.get(idFieldName))) {
				return false;
			}

			params.formParams = {};
			params.formParams[idFieldName] = record.get(idFieldName);
		}

		getWnd(gridPanel.editformclassname).show(params);
		return true;
	},

	doSearch: function(reset) {
		var base_form = this.FilterPanel.getForm();
		var grid = this.GridPanel.getGrid();

		if (reset) {
			base_form.reset();
		}

		var params = base_form.getValues();
		params.start = 0;
		params.limit = 100;

		grid.getStore().load({params: params});
	},

	select: function() {
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('DocNormative_id'))) {
			return false;
		}
		log(['record.data',record.data]);
		this.onSelect(record.data);
		return true;
	},

	show: function() {
		sw.Promed.swDocNormativeViewWindow.superclass.show.apply(this, arguments);

		this.mode = null;
		this.onSelect = Ext.emptyFn;

		if (arguments[0].mode) {
			this.mode = arguments[0].mode;
		}
		if (arguments[0].onSelect) {
			this.onSelect = arguments[0].onSelect;
		}

		Ext.getCmp('DNVW_SelectButton').hide();

		if (this.mode == 'select') {
			Ext.getCmp('DNVW_SelectButton').show();
		}

		this.doSearch(true);
	},

	initComponent: function() {
		var wnd = this;

		this.FilterPanel = new Ext.FormPanel({
			frame: true,
			autoHeight: true,
			id: 'DNVW_FilterPanel',
			region: 'north',
			labelAlign: 'right',
			labelWidth: 120,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					labelWidth: 90,
					items: [{
						xtype: 'textfield',
						name: 'DocNormative_Num',
						fieldLabel: 'Номер',
						width: 320
					}, {
						xtype: 'textfield',
						name: 'DocNormative_Name',
						fieldLabel: 'Наименование',
						width: 320
					}]
				}, {
					layout: 'form',
					labelWidth: 100,
					items: [{
						xtype: 'textfield',
						name: 'DocNormative_Editor',
						fieldLabel: 'Издатель',
						width: 320
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'DocNormativeType',
						hiddenName: 'DocNormativeType_id',
						fieldLabel: 'Тип документа',
						width: 320
					}]
				}, {
					layout: 'form',
					items: [{
						xtype: 'daterangefield',
						name: 'DocNormative_DateRange',
						fieldLabel: 'Период действия',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180
					}]
				}]
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						style: 'margin-left: 5px',
						handler: function() {
							this.doSearch();
						}.createDelegate(this),
						xtype: 'button',
						iconCls: 'search16',
						id: 'DNVW_SearchButton',
						text: BTN_FRMSEARCH
					}]
				}, {
					layout: 'form',
					items: [{
						style: 'margin-left: 20px',
						handler: function() {
							this.doSearch(true);
						}.createDelegate(this),
						xtype: 'button',
						iconCls: 'resetsearch16',
						id: 'DNVW_ResetButton',
						text: BTN_FRMRESET
					}]
				}]
			}
			/*, {
				xtype: 'textfield',
				name: 'DocNormative_Num',
				fieldLabel: 'Номер',
				width: 120
			}, {
				xtype: 'textfield',
				name: 'DocNormative_Name',
				fieldLabel: 'Наименование',
				width: 320
			}, {
				xtype: 'swcommonsprcombo',
				comboSubject: 'DocNormativeType',
				hiddenName: 'DocNormativeType_id',
				fieldLabel: 'Тип документа',
				width: 320
			}, {
				xtype: 'textfield',
				name: 'DocNormative_Editor',
				fieldLabel: 'Издатель',
				width: 320
			}, {
				xtype: 'daterangefield',
				name: 'DocNormative_DateRange',
				fieldLabel: 'Период действия',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
				width: 180
			}*/],
			keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'DNVW_GridPanel',
			dataUrl: '/?c=DocNormative&m=loadDocNormativeGrid',
			object: 'DocNormative',
			editformclassname: 'swDocNormativeEditWindow',
			region: 'center',
			border: false,
			autoLoadData: false,
			paging: false,
			root: 'data',
			stringfields: [
				{name: 'DocNormative_id', type: 'int', header: 'ID', key: true},
				{name: 'DocNormativeType_id', type: 'int', hidden: true},
				{name: 'DocNormative_Num', header: 'Номер', type: 'string', width: 120},
				{name: 'DocNormative_Name', header: 'Наименование', type: 'string', id: 'autoexpand'},
				{name: 'DocNormativeType_Name', header: 'Тип документа', type: 'string', width: 120},
				{name: 'DocNormative_Editor', header: 'Издатель', type: 'string', width: 240},
				{name: 'DocNormative_begDate', header: 'Дата начала', type: 'date', width: 120},
				{name: 'DocNormative_endDate', header: 'Дата окончания', type: 'date', width: 120}
			],
			actions: [
				{name:'action_add', handler: function(){this.openEditWindow('add',this.GridPanel)}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openEditWindow('edit',this.GridPanel)}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openEditWindow('view',this.GridPanel)}.createDelegate(this)},
				{name:'action_delete'}
			],
			onDblClick: function() {
				var gridPanel = this.GridPanel;

				if (this.mode == 'select') {
					this.select();
				} else if (!gridPanel.getAction('action_edit').isDisabled()) {
					gridPanel.getAction('action_edit').execute();
				} else if (!gridPanel.getAction('action_view').isDisabled()) {
					gridPanel.getAction('action_edit').execute();
				}
			}.createDelegate(this)
		});

		Ext.apply(this, {
			items: [
				this.FilterPanel,
				this.GridPanel
			],
			buttons: [
				{
					id: 'DNVW_SelectButton',
					hidden: true,
					handler: function() {
						this.select();
					}.createDelegate(this),
					iconCls: 'ok16',
					text: 'Выбрать'
				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			]
		});

		sw.Promed.swDocNormativeViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
