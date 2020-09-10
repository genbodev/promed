/**
* swKsgEkbSearchWindow - окно поиска диагноза по наименованию
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-01.06.2009
*/

sw.Promed.swKsgEkbSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction : 'hide',
	doReset: function() {
		this.KsgEkbSearchGrid.removeAll({ clearAll: true });
		this.filterPanel.getForm().reset();
	},
	doSearch: function() {
		var win = this;
		var grid = this.KsgEkbSearchGrid.getGrid();
		var base_form = this.filterPanel.getForm();

		var params = this.baseParams;
		if (base_form.findField('DiagFilter').checked) {
			params.DiagFilter = 1;
		} else {
			params.DiagFilter = 0;
		}
		if (base_form.findField('DiagGroupFilter').checked) {
			params.DiagGroupFilter = 1;
		} else {
			params.DiagGroupFilter = 0;
		}
		if (base_form.findField('UslugaComplexFilter').checked) {
			params.UslugaComplexFilter = 1;
		} else {
			params.UslugaComplexFilter = 0;
		}
		if (base_form.findField('PersonAgeGroupFilter').checked) {
			params.PersonAgeGroupFilter = 1;
		} else {
			params.PersonAgeGroupFilter = 0;
		}

		this.KsgEkbSearchGrid.removeAll({ clearAll: true });
		grid.getStore().load({
			params: params
		});
	},
	draggable: true,
	height: 500,
	id: 'KsgEkbSearchWindow',
	initComponent: function() {
		var win = this;

		this.filterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			frame: true,
			region: 'north',
			items: [{
				title: lang['filtryi'],
				autoHeight: true,
				xtype: 'fieldset',
				items: [{
					hideLabel: true,
					boxLabel: lang['diagnoz'],
					name: 'DiagFilter',
					xtype: 'checkbox'
				}, {
					hideLabel: true,
					boxLabel: lang['gruppa_diagnozov'],
					name: 'DiagGroupFilter',
					xtype: 'checkbox'
				}, {
					hideLabel: true,
					boxLabel: lang['usluga'],
					name: 'UslugaComplexFilter',
					xtype: 'checkbox'
				}, {
					hideLabel: true,
					boxLabel: 'Возраст',
					name: 'PersonAgeGroupFilter',
					xtype: 'checkbox'
				}]
			}]
		});

		this.KsgEkbSearchGrid = new sw.Promed.ViewFrame({
			id: win.id+'KsgEkbSearchGrid',
			title:'',
			object: 'KsgEkb',
			dataUrl: '/?c=Usluga&m=loadKsgEkbList',
			autoLoadData: false,
			paging: false,
			region: 'center',
			toolbar: true,
			stringfields:
			[
				{name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'Mes_id', type: 'int', hidden: true},
				{name: 'UslugaComplex_Code', header: lang['ksg'], width: 50},
				{name: 'UslugaComplex_Name', header: lang['naimenovanie_ksg'], width: 250, id: 'autoexpand'},
				{name: 'PersonAgeGroup_Name', header: lang['vozrast'], width: 100},
				{name: 'UslugaComplexTariff_Tariff', header: lang['tarif'], width: 100},
				{name: 'UslugaComplexPartitionLink_IsMesSid', type: 'checkcolumn', header: lang['operatsiya'], width: 60},
				{name: 'UslugaComplexPartitionLink_IsFullPay', type: 'checkcolumn', header: lang['oplata'], width: 60},
				{name: 'UslugaComplexPartitionLink_IsUseLS', type: 'checkcolumn', header: lang['usluga'], width: 60},
				{name: 'UslugaComplexPartitionLink_Signrao', type: 'checkcolumn', header: lang['rao'], width: 60}
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_refresh', disabled: true, hidden: true},
				{name:'action_print', disabled: false},
				{name:'action_delete', disabled: true, hidden: true}
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.ownerCt.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.ownerCt.onOkButtonClick();
				},
		        iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [ win.filterPanel, win.KsgEkbSearchGrid ]
		});
		sw.Promed.swKsgEkbSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
    modal: true,
	onKsgEkbSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	onOkButtonClick: function() {
		if (!this.KsgEkbSearchGrid.getGrid().getSelectionModel().getSelected())
        {
        	this.hide();
        	return false;
        }

		var selected_record = this.KsgEkbSearchGrid.getGrid().getSelectionModel().getSelected();

		if (selected_record) {
			this.onKsgEkbSelect(selected_record.data);
			this.hide();
		}
	},
    plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swKsgEkbSearchWindow.superclass.show.apply(this, arguments);
		this.onKsgEkbSelect = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] )
		{
			this.hide();
			return false;
		}

		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}

		this.baseParams = {};
		if (arguments[0].baseParams)
		{
			this.baseParams = arguments[0].baseParams;
		}

		if (arguments[0].onSelect)
		{
			this.onKsgEkbSelect = arguments[0].onSelect;
		}

		var base_form = this.filterPanel.getForm();
		base_form.findField('UslugaComplexFilter').disable();
		if (arguments[0].enableUslugaComplexFilter)
		{
			base_form.findField('UslugaComplexFilter').enable();
		}

		this.doReset();
	},
	title: lang['ksg_poisk'],
	width: 800
});