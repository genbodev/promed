/**
* swKLCitySearchWindow - окно поиска города.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      10.08.2009
* @tabIndex     5100
*/

sw.Promed.swKLCitySearchWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'border',
    width: 800,
	height: 500,
    modal: true,
	resizable: false,
	draggable: false,
    closeAction : 'hide',
    title:lang['gorod_poisk'],
	id: 'klcity_search_window',
	buttonAlign: 'left',
    plain: true,
	listeners: {
		'hide': function() {this.onWinClose();}
	},
	onSelect: function() {},
	onOkButtonClick: function() {
		if (this.findById('KLCitySearchGrid').ViewGridPanel.getSelectionModel().getSelected())
        {
            this.onSelect({
            	KLCity_id: this.findById('KLCitySearchGrid').ViewGridPanel.getSelectionModel().getSelected().data.KLCity_id,
            	KLCity_Name: this.findById('KLCitySearchGrid').ViewGridPanel.getSelectionModel().getSelected().data.KLCity_Name
            });
        }
        else
        {
            this.hide();
        }
	},
	disableAllActions: function(disable) {
		if ( (disable === true) || (disable == undefined) )
		{
		}
		else
		{
		}
	},
	show: function() {
		sw.Promed.swKLCitySearchWindow.superclass.show.apply(this, arguments);

		if ( arguments[0] )
		{
			if ( arguments[0].onSelect )
				this.onSelect = arguments[0].onSelect;
			else
				this.onSelect = function() {};
			if ( arguments[0].onClose )
				this.onWinClose = arguments[0].onClose;
			else
				this.onWinClose = function() {};
			if ( arguments[0].params )
				this.params = arguments[0].params;
			else
				this.params = {KLRegion_id: 0, KLRegion_Name: '', KLSubRegion_id: 0, KLSubRegion_Name: ''};
		}

		this.findById('KLCitySearchGrid').ViewGridPanel.getStore().removeAll();
  		this.findById('KLCity_search_form').getForm().reset();
		this.findById('KLCSW_SearchField').focus(true, 500);

		// устанавливаем параметры поиска (по городу/району или по всей стране)
		var filter_checkbox = this.findById('KLCSW_FilterBy');
		filter_checkbox.enable();
		if ( this.params.KLSubRegion_id > 0 )
		{
			filter_checkbox.wrap.child('.x-form-cb-label').update(lang['rayon'] + ' ' + this.params.KLSubRegion_Name.substr(0,20));
			filter_checkbox.setValue(true);
		}
		else
		{
			filter_checkbox.wrap.child('.x-form-cb-label').update(lang['rayon_ne_zadan']);
			filter_checkbox.setValue(false);
			filter_checkbox.disable();
		}
		var filterreg_checkbox = this.findById('KLCSW_FilterByReg');
		filterreg_checkbox.enable();
		if ( this.params.KLRegion_id > 0 )
		{
			filterreg_checkbox.wrap.child('.x-form-cb-label').update(lang['region'] + ' ' + this.params.KLRegion_Name.substr(0,20));
			filterreg_checkbox.setValue(true);
		}
		else
		{
			filterreg_checkbox.wrap.child('.x-form-cb-label').update(lang['region_ne_zadan']);
			filterreg_checkbox.setValue(false);
			filterreg_checkbox.disable();
		}
		
		var grid = this.findById('KLCitySearchGrid').ViewGridPanel;
		var grid_toolbar = grid.getTopToolbar();
		// прячем кнопки акшенов
		grid_toolbar.items.items[0].hide();
		grid_toolbar.items.items[1].hide();
		grid_toolbar.items.items[2].hide();
		grid_toolbar.items.items[3].hide();
		grid_toolbar.items.items[4].hide();
		grid_toolbar.items.items[5].hide();
		grid_toolbar.items.items[6].hide();
		grid_toolbar.items.items[7].hide();
		grid_toolbar.items.items[8].hide();
		// провешиваем хандлеры на модель селекции
		grid.addListener('rowdblclick', function(grid, rowNumber, e) {
			var current_window = Ext.getCmp('klcity_search_window');
			current_window.onTownSelect();
		});
		grid.addListener('keypress', function(e) {
			if ( e.getKey() == e.ENTER )
			{
				var current_window = Ext.getCmp('klcity_search_window');
				current_window.onTownSelect();
			}
		});
	},
	doSearch: function() {
		this.findById('KLCitySearchGrid').ViewGridPanel.getStore().removeAll();
//  		var Mask = new Ext.LoadMask(Ext.get('klcity_search_window'), {msg:SEARCH_WAIT});
//		Mask.show();

		var params = {KLSubRegion_id: 0, KLRegion_id: 0};
		
		if ( this.findById('KLCSW_FilterBy').getValue() )
		{
			params.KLSubRegion_id = this.params.KLSubRegion_id;
		}
		
		if ( this.findById('KLCSW_FilterByReg').getValue() )
		{
			params.KLRegion_id = this.params.KLRegion_id;
		}
		
		params.KLCity_Name = this.findById('KLCSW_SearchField').getValue();
		var grid = this.findById('KLCitySearchGrid').ViewGridPanel;
		grid.getStore().load({
			params: params,
			callback: function() {
//                Mask.hide();
				if (grid.getStore().getCount() > 0)
				{
					grid.getSelectionModel().selectFirstRow();
					grid.getView().focusRow(0);
				}
			}
		});
	},
	initComponent: function() {
		Ext.apply(this, {
 			items: [
				new Ext.form.FormPanel({
					frame: true,
					autoHeight: true,
					region: 'north',
            		labelAlign: 'right',
					id: 'KLCity_search_form',
					buttonAlign: 'left',
					items: [{
						layout: 'column',
						items: [{
							columnWidth: .4,
							layout: 'form',
							items: [{
								xtype: 'textfield',
								id: 'KLCSW_SearchField',
								fieldLabel: lang['gorod'],
								anchor: '100%',
								name: 'KLCity_Name',
								enableKeyEvents: true,
								listeners: {
									'keydown': function (inp, e) {
		                               	if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
		                               	{
										}
									}
								}
							}]
						},
						{
							anchor: '95%',
							layout: 'form',
							labelWidth: 5,
							columnWidth: .3,
							items: [{
								xtype: 'checkbox',
								id: 'KLCSW_FilterByReg',
								labelSeparator: '',
								boxLabel: lang['poisk_po_regionu']
							}]
						},
						{
							anchor: '95%',
							layout: 'form',
							labelWidth: 5,
							columnWidth: .3,
							items: [{
								xtype: 'checkbox',
								id: 'KLCSW_FilterBy',
								labelSeparator: '',
								boxLabel: lang['poisk_po_gorodu_rayonu']
							}]
						}]
					}],
					keys: [{
						key: Ext.EventObject.ENTER,
						fn: function(e) {
							Ext.getCmp('klcity_search_window').doSearch();
						},
						stopEvent: true
					}]
				}),
				new sw.Promed.ViewFrame(
				{
					actions:
					[
						{name: 'action_add', disabled: true},
						{name: 'action_edit', disabled: true, handler: function() { Ext.getCmp('klcity_search_window').onOkButtonClick(); } },
						{name: 'action_view', disabled: true},
						{name: 'action_delete', disabled: true},
						{name: 'action_refresh', disabled: true},
						{name: 'action_print'}
					],
					autoLoadData: false,
					dataUrl: '/?c=Address&m=searchKLCity',
					id: 'KLCitySearchGrid',
					focusOn: {name:'KLCSW_CloseButton', type:'button'},
					region: 'center',
					stringfields:
					[
						{name: 'KLCity_id', type: 'int', header: 'ID', key: true},
						{name: 'KLSubRegion_id', type: 'int', hidden: true},
						{name: 'KLRegion_id', type: 'int', hidden: true},
						{name: 'KLCountry_id', type: 'int', hidden: true},
						{name: 'KLRegion_Name',  type: 'string', header: lang['region'], width: 200},
						{name: 'KLSubRegion_Name',  type: 'string', header: lang['rayon'], width: 200},
						{name: 'KLCity_Name',  type: 'string', header: lang['gorod'], width: 200},
						{name: 'KLCity_Socr',  type: 'string', header: lang['tip_goroda'], width: 200}
					],
					title: lang['naselennyie_punktyi'],
					toolbar: true
				})
			],
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(inp, e) {
					e.stopEvent();

		            if (e.browserEvent.stopPropagation)
		                e.browserEvent.stopPropagation();
		            else
		                e.browserEvent.cancelBubble = true;

		            if (e.browserEvent.preventDefault)
		                e.browserEvent.preventDefault();
		            else
		                e.browserEvent.returnValue = false;

		            e.browserEvent.returnValue = false;
		            e.returnValue = false;

		            if (Ext.isIE)
		            {
		                e.browserEvent.keyCode = 0;
		                e.browserEvent.which = 0;
		            }
     				Ext.getCmp('klcity_search_window').onOkButtonClick();
				},
				stopEvent: true
			}],
			buttons: [{
				text: lang['vyibrat'],
		        iconCls: 'ok16',
				id: 'KLCSW_CloseButton',
				handler: function() { this.ownerCt.onTownSelect() }
			}, '-',
			HelpButton(this),
			{
				text: lang['zakryit'],
		        iconCls: 'close16',
				id: 'KLCSW_CloseButton',
				handler: function() { this.ownerCt.hide() }
			}
			]
		});
		sw.Promed.swKLCitySearchWindow.superclass.initComponent.apply(this, arguments);
	},
	onTownSelect: function() {
		var grid = Ext.getCmp('KLCitySearchGrid').ViewGridPanel;
		var town_row = grid.getSelectionModel().getSelected();
		if ( town_row )
		{
			this.hide();
			this.onSelect(town_row.data);
		}
	}
});