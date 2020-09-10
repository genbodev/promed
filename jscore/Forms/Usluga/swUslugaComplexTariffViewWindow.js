/**
* swUslugaComplexTariffViewWindow - форма просмотра тарифов комлексной услуги
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Usluga
* @access		public
* @copyright	Copyright (c) 2009 Swan Ltd.
* @author		Stas Bykov aka Savage (savage1981@gmail.com)
* @version		25.02.2013
* @comment		Префикс для id компонентов UCTVW (UslugaComplexTariffViewWindow)
*/

sw.Promed.swUslugaComplexTariffViewWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaComplexTariffViewWindow',
	objectSrc: '/jscore/Forms/Usluga/swUslugaComplexTariffViewWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	formParams: new Object(),
	height: 550,
	id: 'UslugaComplexTariffViewWindow',
	initComponent: function() {
		var form = this;

		form.uslugaComplexTariffGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=Usluga&m=loadUslugaComplexTariffList',
			id: 'UCTVW_UslugaComplexTariffGrid',
			onDblClick: function() {
				form.onSelect();
			},
			onEnter: function() {
				form.onSelect();
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				//
			},
			paging: false,
			region: 'center',
			stringfields: [
				{ name: 'UslugaComplexTariff_id', type: 'int', header: 'ID', key: true },
				{ name: 'UslugaComplexTariff_Code', type: 'string', header: lang['kod'], width: 50 },
				{ name: 'UslugaComplexTariff_Name', type: 'string', header: lang['naimenovanie'], width: 100 },
				{ name: 'PayType_Name', type: 'string', header: lang['vid_oplatyi'], width: 80 },
				{ name: 'UslugaComplexTariffType_Name', type: 'string', header: lang['tip_tarifa'], width: 100 },
				{ name: 'LpuLevel_Name', type: 'string', header: lang['uroven_lpu'], width: 100 },
				{ name: 'Lpu_Name', type: 'string', header: lang['lpu'], width: 200 },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil'], width: 150 },
				{ name: 'LpuUnitType_Name', type: 'string', header: lang['vid_med_pomoschi'], width: 100 },
				{ name: 'MesAgeGroup_Name', type: 'string', header: lang['vozrastnaya_gruppa'], width: 100 },
				{ name: 'Sex_Name', type: 'string', header: lang['pol_patsienta'], width: 80 },
				{ name: 'UslugaComplexTariff_Tariff', type: 'float', header: lang['tarif'], width: 80 },
				{ name: 'UslugaComplexTariff_UED', type: 'float', header: lang['uet_vracha'], width: 80 },
				{ name: 'UslugaComplexTariff_UEM', type: 'float', header: lang['uet_sr_medpersonala'], width: 80 },
				{ name: 'UslugaComplexTariff_begDate', type: 'date', header: lang['data_nachala'], width: 80 },
				{ name: 'UslugaComplexTariff_endDate', type: 'date', header: lang['data_okonchaniya'], width: 80 }
			],
			title: lang['tarifyi']
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.onSelect();
				},
				iconCls: 'ok16',
				onShiftTabAction: function () {
					//
				},
				onTabAction: function () {
					//
				},
				text: BTN_FRMSELECT
			}, {
				text: '-'
			},
			HelpButton(form, -1),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					//
				},
				onTabAction: function () {
					//
				},
				text: BTN_FRMCANCEL
			}],
			items: [
				 form.uslugaComplexTariffGrid
			],
			layout: 'border'
		});

		sw.Promed.swUslugaComplexTariffViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaComplexTariffViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: true,
	modal: true,
	onHide: Ext.emptyFn,
	onSelect: function() {
		var grid = this.uslugaComplexTariffGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplexTariff_id') ) {
			return false;
		}

		this.callback(grid.getSelectionModel().getSelected().data);
		this.hide();

		return true;
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swUslugaComplexTariffViewWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.params = new Object();

		this.uslugaComplexTariffGrid.removeAll();

		if ( !arguments[0] || !arguments[0].formParams
			|| Ext.isEmpty(arguments[0].formParams.PayType_id) || Ext.isEmpty(arguments[0].formParams.Person_id)
			|| Ext.isEmpty(arguments[0].formParams.UslugaComplex_id) || Ext.isEmpty(arguments[0].formParams.UslugaComplexTariff_Date)
		) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.formParams = arguments[0].formParams;
		this.formParams.IsForGrid = 1;

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.uslugaComplexTariffGrid.getGrid().getStore().load({
			params: this.formParams
		});
	},
	title: lang['usluga_tarifyi'],
	width: 750
});