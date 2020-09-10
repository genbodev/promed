/**
* swDrugOstatByDateViewWindow - просмотр медикаментов на дату остатка.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      14.01.2010
*/

sw.Promed.swDrugOstatByDateViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doReset: function(reset_form_flag) {
	},
	doSearch: function() {
		var form = this.findById('DrugOstatByDateViewForm');
		
		if ( form.getForm().findField('OstatDate').getValue() == '' || form.getForm().findField('OstatDate').getValue() == '__.__.____' )
		{
			sw.swMsg.alert(lang['oshibka'], lang['nekorrektno_zapolnena_data_otobrajeniya_ostatkov'], function() {
				form.getForm().findField('OstatDate').focus(true, 200);
			});
			return;
		}
		var params = form.getForm().getValues();
		params.start = 0;
		params.limit = 100;
		this.findById('DrugOstatByDateViewGrid').loadData({globalFilters: params});
	},
	draggable: true,
	getRecordsCount: function() {
		var current_window = this;

		var form = current_window.findById('PersonCardSearchForm');

		if ( !form.getForm().isValid() ) {
			sw.swMsg.alert(lang['poisk_po_kartoteke'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('PersonCardSearchWindow'), { msg: "Подождите, идет подсчет записей..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	height: 550,
	id: 'DrugOstatByDateViewWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				id: 'DOBDVW_SearchButton',
				tabIndex: TABINDEX_PERSCARDSW + 76,
				text: BTN_FRMSEARCH
			}/*, {
				handler: function() {
					this.ownerCt.findById('DrugOstatByDateViewWindow').getForm().submit();
				},
				iconCls: 'print16',
				tabIndex: TABINDEX_PERSCARDSW + 78,
				text: lang['pechat']
			}, {
				handler: function() {
					this.ownerCt.getRecordsCount();
				},
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_PERSCARDSW + 79,
				text: BTN_FRMCOUNT
			}*/,
			'-'/*,
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_POL_PERSCARDSEARCH);
				}.createDelegate(self),
				tabIndex: TABINDEX_PERSCARDSW + 80
			}*/,
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					Ext.getCmp('DOBDVW_SearchButton').focus();
				},
				onTabAction: function() {
					var current_window = this.ownerCt;
					current_window.findById('DrugOstatByDateViewForm').getForm().findField('OstatDate').focus(true, 200);
				},
				tabIndex: TABINDEX_PERSCARDSW + 81,
				text: BTN_FRMCANCEL
			}
			],
			items: [ new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				height: 30,
				id: 'DrugOstatByDateViewForm',
				items: [{
					fieldLabel: lang['data'],
					xtype: 'swdatefield',
					format: 'd.m.Y',
					id: 'DOBDVW_OstatDate',
					name: 'OstatDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					width: 100,
					enableKeyEvents: true,
					listeners: {
						'keydown': function (inp, e) {
							if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								var current_window = Ext.getCmp('DrugOstatByDateViewWindow');
								if ( current_window.findById('DrugOstatByDateViewGrid').ViewGridPanel.getStore().getCount() > 0 )
									current_window.findById('DrugOstatByDateViewGrid').focus();
								else
									Ext.getCmp('DOBDVW_SearchButton').focus();
							}
						}
					}
				}],
				keys: [{
					fn: function(e) {
						Ext.getCmp('DrugOstatByDateViewWindow').doSearch();
					},
					key: Ext.EventObject.ENTER,
					scope: this,
					stopEvent: true
				}],
				labelAlign: 'right',
				labelWidth: 60,
				region: 'north'
			}),
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', disabled: true },
					{ name: 'action_edit', disabled: true },
					{ name: 'action_view', disabled: true},
					{ name: 'action_delete', disabled: true },
					{ name: 'action_refresh' },
					{ name: 'action_print' }
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 250,
				autoLoadData: false,
				dataUrl: '?c=FarmacyDrugOstat&m=loadDrugOatatByDate',
				focusOn: {
					name: 'DOBDVW_SearchButton',
					type: 'field'
				},
				focusPrev: {
					name: 'DOBDVW_OstatDate',
					type: 'field'
				},
				id: 'DrugOstatByDateViewGrid',
				pageSize: 100,
				paging: true,
				region: 'center',
				root: 'data',
				stringfields: [
					{ name: 'row_id', type: 'int', header: 'ID', key: true },
					{ name: 'Drug_id', type: 'int', hidden: true},
					{ header: lang['naimenovanie'],  type: 'string', name: 'val1', id: 'autoexpand', width: 100 },
					{ header: lang['ed_ucheta'],  type: 'string', name: 'val2', width: 70 },
					{ header: lang['tsena'],  type: 'string', name: 'val3', width: 70, align: 'right' },
					{ header: lang['nach_ost'],  type: 'string', name: 'val4', width: 70, align: 'right' },
					{ header: lang['summa'],  type: 'string', name: 'val5', width: 70, align: 'right' },
					{ header: lang['prihod'],  type: 'string', name: 'val6', width: 70, align: 'right' },
					{ header: lang['summa'],  type: 'string', name: 'val7', width: 70, align: 'right' },
					{ header: lang['rashod'],  type: 'string', name: 'val8', width: 70, align: 'right' },
					{ header: lang['summa'],  type: 'string', name: 'val9', width: 70, align: 'right' },
					{ header: lang['spisano'],  type: 'string', name: 'val10', width: 70, align: 'right' },
					{ header: lang['summa'],  type: 'string', name: 'val11', width: 70, align: 'right' },
					{ header: lang['konech_ost'],  type: 'string', name: 'val12', width: 70, align: 'right' },
					{ header: lang['summa'],  type: 'string', name: 'val13', width: 70, align: 'right' }
				],
				toolbar: true,
				totalProperty: 'totalCount'
			})]
		});
		sw.Promed.swDrugOstatByDateViewWindow.superclass.initComponent.apply(this, arguments);
	},
	initGridListeners: false,
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('DrugOstatByDateViewWindow');
			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;				
			}
		},
		key: [ Ext.EventObject.J, Ext.EventObject.C ],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	listeners: {
		'hide': function() {
			Ext.getCmp('DrugOstatByDateViewWindow').findById('DrugOstatByDateViewGrid').removeAll();
		}
	},
	plain: true,
	resizable: true,
	refreshPersonCardViewGrid: function() {
		// так как у нас грид не обновляется, то просто ставим фокус в первое поле ввода формы
		//this.findById('PersonCardSearchForm').getForm().findField('Person_Surname').focus(true, 100);
	},
	show: function() {
		sw.Promed.swDrugOstatByDateViewWindow.superclass.show.apply(this, arguments);

		var current_window = this;
		var form = current_window.findById('DrugOstatByDateViewForm');

		current_window.restore();
		current_window.center();
		current_window.maximize();
		current_window.doReset(true);
		
		form.getForm().findField('OstatDate').focus(true, 200);
		
		if ( getGlobalOptions()['date'] )
		{
			form.getForm().findField('OstatDate').setValue(getGlobalOptions()['date']);
			this.doSearch();
		}
		

		form.getForm().getEl().dom.action = "/?c=Search&m=printSearchResults";
		form.getForm().getEl().dom.method = "post";
		form.getForm().getEl().dom.target = "_blank";
		form.getForm().standardSubmit = true;		
	},
	title: lang['ostatki_medikamentov'],
	width: 900
});