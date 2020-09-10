/**
 * swUslugaComplexMSESelectWindow - Обследования и исследования: Добавление
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package     Common
 * @access      public
 * @copyright	Copyright (c) 2018 Swan Ltd.
 */
/*NO PARSE JSON*/
sw.Promed.swUslugaComplexMSESelectWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: false,
	maximized: false,
	height: 500,
	width: 900,
	id: 'swUslugaComplexMSESelectWindow',
	title: 'Обследования и исследования: Добавление',
	layout: 'border',
	resizable: true,
	doSave: function() {
		var records = [];
		this.UslugaComplexMSEGrid.getMultiSelections().forEach(function (el) {
			if (!Ext.isEmpty(el.get('EvnUsluga_id'))) {
				records.push({
					EvnUsluga_id: el.get('EvnUsluga_id'),
					EvnClass_SysNick: el.get('EvnClass_SysNick'),
					ParentClass_SysNick: el.get('ParentClass_SysNick'),
					Person_id: el.get('Person_id'),
					EvnUsluga_setDate: el.get('EvnUsluga_setDate'),
					UslugaComplex_Name: el.get('UslugaComplex_Name'),
					UslugaComplex_id: el.get('UslugaComplex_id'),
					EvnUsluga_isActual: el.get('EvnUsluga_isActual')
				});
			}
		});
		
		this.callback(records);
		this.hide();
	},
	openEvnUslugaEditWindow: function() {
		
		var grid = this.UslugaComplexMSEGrid.getGrid(),
			record = grid.getSelectionModel().getSelected();
			
		if (!record || !record.get('EvnUsluga_id')) return false;
		
		var evn_usluga_id = record.get('EvnUsluga_id');
		var params = new Object();
		params.action = 'view';
		params.Person_id = record.get('Person_id');
		params.parentEvnComboData = null;
		params.parentClass = record.get('ParentClass_SysNick');
		
		switch ( record.get('EvnClass_SysNick') ) {
			case 'EvnUslugaCommon':
				params.formParams = {
					EvnUslugaCommon_id: evn_usluga_id
				}
				getWnd('swEvnUslugaEditWindow').show(params);
			break;

			case 'EvnUslugaOper':
				params.formParams = {
					EvnUslugaOper_id: evn_usluga_id
				}
				getWnd('swEvnUslugaOperEditWindow').show(params);
			break;

			case 'EvnUslugaPar':
				params.EvnUslugaPar_id = evn_usluga_id;
				getWnd('swEvnUslugaParEditWindow').show(params);
			break;
		}
	},
	doResetFilters: function() {
	
		var base_form = this.filtersPanel.getForm();
		base_form.reset();
		
		if (this.Diag_id != ''){
			var diag_combo = base_form.findField('Diag_id');
			diag_combo.setValue(this.Diag_id);
			diag_combo.getStore().load({
				callback: function(){
					diag_combo.getStore().each(function(rec){
						if (rec.get('Diag_id') == diag_combo.getValue())
							diag_combo.fireEvent('select', diag_combo, rec, 0);
					});
				},
				params: { where: "where Diag_id = " + diag_combo.getValue() }
			});
		}
	},
	doFilter: function() {
	
		var base_form = this.filtersPanel.getForm();
		var filters = base_form.getValues();
		filters.Person_id = this.Person_id;
		filters.EvnPrescrMse_IsFirstTime = this.EvnPrescrMse_IsFirstTime;
		filters.start = 0;
		filters.limit = 100;

		this.UslugaComplexMSEGrid.removeAll({ clearAll: true });
		this.UslugaComplexMSEGrid.loadData({ globalFilters: filters });
	},
	show: function() {
		sw.Promed.swUslugaComplexMSESelectWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = this.filtersPanel.getForm();
		
		this.doLayout();
		
		if (!arguments[0] || !arguments[0].Person_id) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { win.hide(); });
		}
		
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.Person_id = arguments[0].Person_id;
		this.Diag_id = arguments[0].Diag_id || null;
		this.EvnPrescrMse_IsFirstTime = arguments[0].EvnPrescrMse_IsFirstTime || null;
		
		base_form.findField('UslugaComplex_id').setUslugaCategoryList(['gost2011']);

		this.doResetFilters();
		this.doFilter();
	},
	initComponent: function()
	{
		var win = this;

		this.filtersPanel = new Ext.FormPanel({
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 100,
			frame: true,
			border: false,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					win.doFilter();
				},
				stopEvent: true
			}],
			items: [{
				buttons: [{
					text: BTN_FIND,
					tabIndex: TABINDEX_RRLW + 10,
					handler: function() {
						win.doFilter();
					},
					iconCls: 'search16'
				}, {
					text: BTN_RESETFILTER,
					tabIndex: TABINDEX_RRLW + 11,
					handler: function() {
						win.doResetFilters();
						win.doFilter();
					},
					iconCls: 'resetsearch16'
				}, '-'],
				xtype: 'fieldset',
				autoHeight: true,
				collapsible: true,
				listeners: {
					collapse: function(p) {
						win.doLayout();
					},
					expand: function(p) {
						win.doLayout();
					}
				},
				title: langs('Фильтр'),
				items: [{
					border: false,
					layout: 'column',
					labelWidth: 140,
					anchor: '-10',
					items: [{
						layout: 'form',
						columnWidth: .45,
						border: false,
						items: [{
							boxLabel: 'Только рекомендованные методы обследования/исследования при направлении на МСЭ по данному Основному диагнозу',
							name: 'RecommendedOnly',
							xtype: 'checkbox',
							hideLabel: true,
							checked: true
						}, {
							boxLabel: 'Все диагнозы',
							name: 'AllDiag',
							xtype: 'checkbox',
							hideLabel: true
						}]
					}, {
						layout: 'form',
						columnWidth: .55,
						border: false,
						items: [{
							hiddenName: 'Diag_id',
							fieldLabel: 'Диагноз',
							xtype: 'swdiagcombo',
							width: 300
						}, {
							hiddenName: 'UslugaComplex_id',
							fieldLabel: 'Услуга',
							xtype: 'swuslugacomplexnewcombo',
							listWidth: 600,
							width: 300
						}, {
							name: 'EvnUsluga_DateRange',
							fieldLabel: 'Период оказания услуги',
							xtype: 'daterangefield',
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							width: 180
						}]
					}]
				}]
			}]
		});

		this.UslugaComplexMSEGrid = new sw.Promed.ViewFrame({
			id: win.id+'UslugaComplexMSEGrid',
			selectionModel: 'multiselect2',
			title:'',
			object: 'Mse',
			dataUrl: '/?c=Mse&m=searchUslugaComplexMSE',
			autoLoadData: false,
			region: 'center',
			toolbar: true,
			useEmptyRecord: false,
			noSelectFirstRowOnFocus: true,
			stringfields: [
				{name: 'EvnUsluga_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'EvnClass_SysNick', type: 'string', hidden: true},
				{name: 'ParentClass_SysNick', type: 'string', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'EvnUsluga_setDate', header: 'Дата выполнения', width: 120},
				{name: 'UslugaComplex_id', type: 'int', hidden: true},
				{name: 'UslugaComplex_Name', header: 'Услуга', id: 'autoexpand'},
				{name: 'EvnUsluga_isActual', type: 'checkbox', header: 'Актуальность', width: 120, hidden: getRegionNick() == 'kz'}
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', handler: function() { win.openEvnUslugaEditWindow(); }},
				{name:'action_delete', disabled: true, hidden: true},
				{name:'action_refresh', disabled: true, hidden: true},
				{name:'action_print', disabled: true, hidden: true}
			]
		});

		this.formPanel = new Ext.Panel({
			region: 'center',
			labelAlign: 'right',
			layout: 'border',
			labelWidth: 50,
			border: false,
			items: [
				this.filtersPanel,
				this.UslugaComplexMSEGrid
			]
		});

		Ext.apply(this, {
		items: [
			win.formPanel
		],
		buttons: [{
				iconCls: 'add16',
				tabIndex: TABINDEX_RRLW + 13,
				handler: function() {
					win.doSave();
				},
				text: 'Добавить'
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_RRLW + 13),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RRLW + 14,
				handler: function() {
					win.hide();
				},
				text: BTN_FRMCANCEL
			}]
		});

		sw.Promed.swUslugaComplexMSESelectWindow.superclass.initComponent.apply(this, arguments);
	}
});