/**
* swEvnPLDispDriverSearchWindow - окно поиска талона по осмотрам водителей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2009 - 2016 Swan Ltd.
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispDriverSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispDriverSearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispDriverSearchWindow.js',
	addEvnPLDD: function() {
		var current_window = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnPLDispDriverEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dispanserizatsii_teen14ti_letnih_uje_otkryito']);
			return false;
		}
		
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				current_window.refreshEvnPLDDList();
			},
			onSelect: function(person_data) {
				getWnd('swEvnPLDispDriverEditWindow').show({
					action: 'add',
					Person_id: person_data.Person_id,
					PersonEvn_id: person_data.PersonEvn_id,
					Server_id: person_data.Server_id
				});
				return;			
			}
		});
	},
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnPLDD: function() {
		var current_window = this;
		var grid = current_window.findById('EPLDDSW_EvnPLDispDriverSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispDriver_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_DT14_id = record.get('EvnPLDispDriver_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_kartyi']);
								}
								else {
									grid.getStore().remove(record);

									if ( grid.getStore().getCount() == 0 ) {
										LoadEmptyRow(grid);
									}
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_voznikli_oshibki']);
							}
						},
						params: {
							EvnPLDispDriver_id: evn_pl_DT14_id
						},
						url: '/?c=EvnPLDispDriver&m=deleteEvnPLDispDriver'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить случай?',
			title: lang['vopros']
		});
	},
	doReset: function() {
		var current_window = this;
		var filter_form = current_window.findById('EvnPLDispDriverSearchFilterForm');
		filter_form.getForm().reset();
		current_window.findById('EPLDDSW_EvnPLDispDriverSearchGrid').getGrid().getStore().removeAll();
		current_window.findById('EPLDDSW_EvnPLDispDriverSearchGrid').getAction('action_print').setDisabled(false); // печать всегда доступна
	},
	searchInProgress: false,
	getFilterForm: function() {
		if ( this.filterForm == undefined ) {
			this.filterForm = this.findById('EvnPLDispDriverSearchFilterForm');
		}
		return this.filterForm;
	},
	doSearch: function(params) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;

		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		
		if ( this.isStream )
		{
			this.doStreamInputSearch();
			return true;
		}
		var current_window = this;
		var filter_form = current_window.findById('EvnPLDispDriverSearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var EvnPLDispDriver_grid = current_window.findById('EPLDDSW_EvnPLDispDriverSearchGrid').ViewGridPanel;

		var vals = filter_form.getForm().getValues();
		var flag = true;
		for ( var value in vals )
		{
			if ( vals[value] != "" )
			flag = false;
		}
		if ( flag )
		{
			sw.swMsg.alert("Внимание", "Заполните хотя бы одно поле для поиска.",
			function () { filter_form.getForm().findField(0).focus()});
			return false;
		}

		var arr = filter_form.find('disabled', true);
		var params = filter_form.getForm().getValues();

		for (i = 0; i < arr.length; i++)
		{
			if (arr[i].getValue)
			{
				if (arr[i].hiddenName != undefined)
					params[arr[i].hiddenName] = arr[i].getValue();
				else if (arr[i].name != undefined)
					params[arr[i].name] = arr[i].getValue();
			}
		}

		if (filter_form.getForm().isValid())
		{
			if ( soc_card_id )
			{
				var params = {
					soc_card_id: soc_card_id,
					SearchFormType: params.SearchFormType
				};
			}			
			params.start = 0;
			params.limit = 100;

			params.SearchFormType = "EvnPLDispDriver",
			EvnPLDispDriver_grid.getStore().removeAll();
			EvnPLDispDriver_grid.getStore().baseParams = params;
			EvnPLDispDriver_grid.getStore().load({
				params: params,
				callback: function (){
					thisWindow.searchInProgress = false;
				}
			});
		}
		else {
			thisWindow.searchInProgress = false;
			sw.swMsg.alert('Поиск', 'Проверьте правильность заполнения полей на форме поиска');
		}
	},
	doStreamInputSearch: function() {
		var grid = this.findById('EPLDDSW_EvnPLDispDriverSearchGrid').ViewGridPanel;
		var form = this.findById('EvnPLDispDriverSearchFilterForm');
		var thisWindow = this;
		var params = {};
		params.EvnPLDispDriverStream_begDate = this.begDate;
		params.EvnPLDispDriverStream_begTime = this.begTime;
		if ( !params.EvnPLDispDriverStream_begDate && !params.EvnPLDispDriverStream_begTime ) {
			this.getBegDateTime();
			thisWindow.searchInProgress = false;
		}
		else
		{
			params.start = 0;
			params.limit = 100;
			params.SearchFormType = "EvnPLDispDriverStream",
			grid.getStore().removeAll();
			grid.getStore().baseParams = params;
			grid.getStore().load({
				params: params,
				callback: function (){
					thisWindow.searchInProgress = false;
				}
			});
		}
	},
	draggable: true,
	getBegDateTime: function() {
		var current_window = this;
		Ext.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);

					current_window.begDate = response_obj.begDate;
					current_window.begTime = response_obj.begTime;
					if ( current_window.isStream ) {
						current_window.doStreamInputSearch();
					}
					//current_window.findById('EPLDDSW_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});
	},
	height: 550,
	id: 'EvnPLDispDriverSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDDSW_SearchButton');
	},
	initComponent: function() {
		var win = this;

		// стандартные фильтры не подходят, форма сильно отличается
		this.mainFilters = {
			autoHeight: true,
			autoScroll: true,
			bodyStyle: 'margin: 5px 5px 10px;',
			layout: 'form',
			border: false,
			items: [{
				autoHeight: true,
				autoScroll: true,
				labelWidth: 114,
				layout: 'form',
				style: 'margin: 0px 5px 0px 5px; padding: 5px 7px;',
				title: 'Пациент',
				width: 755,
				xtype: 'fieldset',
				items: [{
					border: false,
					layout: 'column',
					items: [{
						name: 'SearchFormType',
						value: this.searchFormType,
						xtype: 'hidden'
					}, {
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: lang['familiya'],
							listeners: {
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											// Переход к последней кнопке в окне
											this.getOwnerWindow().buttons[this.ownerWindow.buttons.length-1].focus();
									}
								}.createDelegate(this)
							},
							name: 'Person_Surname',
							maskRe: /[^%]/,
							tabIndex: this.tabIndexBase + 1,
							width: 200,
							xtype: 'textfieldpmw'
						}, {
							fieldLabel: lang['imya'],
							maskRe: /[^%]/,
							name: 'Person_Firname',
							tabIndex: this.tabIndexBase + 2,
							width: 200,
							xtype: 'textfieldpmw'
						}, {
							fieldLabel: lang['otchestvo'],
							maskRe: /[^%]/,
							name: 'Person_Secname',
							tabIndex: this.tabIndexBase + 3,
							width: 200,
							xtype: 'textfieldpmw'
						}]
					}, {
						border: false,
						labelWidth: 160,
						layout: 'form',
						items: [{
							fieldLabel: lang['data_rojdeniya'],
							name: 'Person_Birthday',
							plugins: [
									new Ext.ux.InputTextMask('99.99.9999', false)
							],
							tabIndex: this.tabIndexBase + 5,
							width: 100,
							xtype: 'swdatefield'
						}, {
							fieldLabel: lang['diapazon_dat_rojdeniya'],
							name: 'Person_Birthday_Range',
							plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							tabIndex: this.tabIndexBase + 6,
							width: 170,
							xtype: 'daterangefield'
						}, {
							fieldLabel: lang['nomer_amb_kartyi'],
							maskRe: /[^%]/,
							name: 'PersonCard_Code',
							tabIndex: this.tabIndexBase + 7,
							width: 100,
							xtype: 'textfield'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowNegative: false,
							// allowDecimals: false,
							fieldLabel: lang['god_rojdeniya'],
							name: 'PersonBirthdayYear',
							tabIndex: this.tabIndexBase + 4,
							width: 60,
							xtype: 'numberfield'
						}, {
							allowNegative: false,
							// allowDecimals: false,
							fieldLabel: lang['vozrast'],
							name: 'PersonAge',
							tabIndex: this.tabIndexBase + 10,
							width: 60,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							allowNegative: false,
							// allowDecimals: false,
							fieldLabel: lang['god_rojdeniya_s'],
							name: 'PersonBirthdayYear_Min',
							tabIndex: this.tabIndexBase + 8,
							width: 61,
							xtype: 'numberfield'
						}, {
							allowNegative: false,
							// allowDecimals: false,
							fieldLabel: lang['vozrast_s'],
							name: 'PersonAge_Min',
							tabIndex: this.tabIndexBase + 11,
							width: 61,
							xtype: 'numberfield'
						}]
					}, {
						border: false,
						labelWidth: 40,
						layout: 'form',
						items: [{
							allowNegative: false,
							// allowDecimals: false,
							fieldLabel: lang['po'],
							name: 'PersonBirthdayYear_Max',
							tabIndex: this.tabIndexBase + 9,
							width: 61,
							xtype: 'numberfield'
						}, {
							allowNegative: false,
							// allowDecimals: false,
							fieldLabel: lang['po'],
							name: 'PersonAge_Max',
							tabIndex: this.tabIndexBase + 12,
							width: 61,
							xtype: 'numberfield'
						}]
					}]
				}]
			}, {
				autoHeight: true,
				autoScroll: true,
				labelWidth: 114,
				layout: 'form',
				style: 'margin: 5px 5px 0px 5px; padding: 5px 7px;',
				title: lang['dokument'],
				width: 755,
				xtype: 'fieldset',
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
							items: [{
								fieldLabel: lang['tip'],
								editable: false,
								forceSelection: true,
								hiddenName: 'DocumentType_id',
								listWidth: 500,
								tabIndex: this.tabIndexBase + 25,
								width: 200,
								xtype: 'swdocumenttypecombo'
							}]
						}, {
							border: false,
							labelWidth: 80,
							layout: 'form',
							items: [{
								fieldLabel: lang['seriya'],
								maskRe: /[^%]/,
								name: 'Document_Ser',
								tabIndex: this.tabIndexBase + 26,
								width: 100,
								xtype: 'textfield'
							}]
						}, {
							border: false,
							labelWidth: 80,
							layout: 'form',
							items: [{
								allowNegative: false,
								// allowDecimals: false,
								fieldLabel: lang['nomer'],
								name: 'Document_Num',
								tabIndex: this.tabIndexBase + 27,
								width: 100,
								xtype: 'numberfield'
							}]
						}]
				}, {
					editable: false,
					enableKeyEvents: true,
					hiddenName: 'OrgDep_id',
					listeners: {
						'keydown': function( inp, e ) {
							if ( inp.disabled ) {
								return;
							}

							if ( e.F4 == e.getKey() ) {
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								inp.onTrigger1Click();

								return false;
							}
						},
						'keyup': function(inp, e) {
							if ( e.F4 == e.getKey() ) {
								if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
								else
										e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
								else
										e.browserEvent.returnValue = false;

								e.returnValue = false;

								if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
								}

								return false;
							}
						}
					},
					listWidth: 400,
					onTrigger1Click: function() {
						if ( this.disabled ) {
								return;
						}

						var combo = this;

						getWnd('swOrgSearchWindow').show({
							onSelect: function(orgData) {
								if ( orgData.Org_id > 0 ) {
									combo.getStore().load({
										callback: function() {
											combo.setValue(orgData.Org_id);
											combo.focus(true, 250);
											combo.fireEvent('change', combo);
										},
										params: {
											Object: 'OrgDep',
											OrgDep_id: orgData.Org_id,
											OrgDep_Name: ''
										}
									});
								}
								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {
									combo.focus(true, 200)
							},
							object: 'dep'
						});
					},
					tabIndex: this.tabIndexBase + 28,
					width: 500,
					xtype: 'sworgdepcombo'
				}]
			}, {
				autoHeight: true,
				autoScroll: true,
				labelWidth: 114,
				layout: 'form',
				style: 'margin: 5px 5px 0px 5px; padding: 5px 7px;',
				title: 'Результат',
				width: 755,
				xtype: 'fieldset',
				items: [{
					comboSubject: 'ResultDispDriver',
					fieldLabel: 'Результат',
					hiddenName: 'ResultDispDriver_id',
					tabIndex: this.tabIndexBase + 29,
					width: 350,
					xtype: 'swcommonsprcombo'
				}]
			}]
		};

		Ext.apply(this, {
			items: [
			new Ext.Panel({
				height: 250,
				autoHeight: true,
				id: 'EPLDDSW_SearchFilterPanel',
				region: 'north',
				items: [
					new sw.Promed.Panel({
						autoHeight: true,
						border: false,
						collapsible: true,
						title: lang['najmite_na_zagolovok_chtobyi_svernut_razvernut_panel_filtrov'],
						region: 'center',
						items: [
						new Ext.form.FormPanel({
							afterRender : function() {
								var map = new Ext.KeyMap(this.getEl(), [{
									key: [13],
									fn: function() {
										win.doSearch();
									},
									scope: this
								}]);
							},
							autoScroll: true,
							bodyBorder: false,
							labelAlign: 'right',
							labelWidth: 130,
							id: 'EvnPLDispDriverSearchFilterForm',
							items: [
								this.mainFilters
							]
						})],
						listeners: {
							collapse: function(p) {
								win.doLayout();
								win.syncSize();
							},
							expand: function(p) {
								win.doLayout();
								win.syncSize();
							}
						}
					})
				]
			}),
			new sw.Promed.ViewFrame({
				useArchive: 0,
				actions: [
					{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispDriverSearchWindow').addEvnPLDD(); } },
					{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispDriverSearchWindow').openEvnPLDDEditWindow('edit'); } },
					{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispDriverSearchWindow').openEvnPLDDEditWindow('view'); } },
					{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispDriverSearchWindow').deleteEvnPLDD(); } },
					{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispDriverSearchWindow').refreshEvnPLDDList(); } },
					{ name: 'action_print', hidden: true}
				],
				autoExpandColumn: 'autoexpand',
				autoLoadData: false,
				dataUrl: C_SEARCH,
				focusOn: {
					name: 'EPLDDSW_SearchButton', type: 'field'
				},
				id: 'EPLDDSW_EvnPLDispDriverSearchGrid',
				layout: 'fit',
				object: 'EvnPLDD',
				pageSize: 100,
				paging: true,
				region: 'center',
				root: 'data',
				totalProperty: 'totalCount',
				onBeforeLoadData: function() {
					this.getButtonSearch().disable();
				}.createDelegate(this),
				onLoadData: function() {
					this.getButtonSearch().enable();
				}.createDelegate(this),
				title: '',
				toolbar: true,
				stringfields: [
					{ name: 'EvnPLDispDriver_id', type: 'int', header: 'ID', key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'EvnPLDispDriver_signDT', type: 'string', hidden: true },
					{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150 },
					{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150 },
					{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: 'Дата рождения' },
					{ name: 'PersonPAddress', type: 'string', header: 'Адрес проживания', width: 150 },
					{ name: 'PersonUAddress', type: 'string', header: 'Адрес регистрации', width: 150 },
					{ name: 'EvnPLDispDriver_setDate', type: 'date', format: 'd.m.Y', header: 'Дата начала мед. освид.' },
					{ name: 'EvnPLDispDriver_disDate', type: 'date', format: 'd.m.Y', header: 'Дата окончания мед. освид.' },
					{ name: 'ResultDispDriver_Name', type: 'string', header: 'Результат', width: 100 },
					{ name: 'EvnPLDispDriver_IsSigned', renderer: function(v, p, r) {
						var s = '';
						if (!Ext.isEmpty(r.get('EvnPLDispDriver_IsSigned'))) {
							if (r.get('EvnPLDispDriver_IsSigned') == 2) {
								s += '<img src="/img/icons/emd/doc_signed.png">';
							} else {
								s += '<img src="/img/icons/emd/doc_notactual.png">';
							}

							s += r.get('EvnPLDispDriver_signDT');
						} else {
							s += '<img src="/img/icons/emd/doc_notsigned.png">';
						}
						return s;
					}, header: 'ЭЦП', width: 100 }
				],
				onRowSelect: function(sm, index, record) {
					if (win.viewOnly == true)
					{
						this.getAction('action_view').setDisabled(false);
						this.getAction('action_edit').setDisabled(true);
						this.getAction('action_delete').setDisabled(true);
					}
					else
					{
						// Запретить редактирование/удаление архивных записей
						if (getGlobalOptions().archive_database_enable) {
							this.getAction('action_edit').setDisabled(record.get('archiveRecord') == 1);
							this.getAction('action_delete').setDisabled(record.get('archiveRecord') == 1);
						}
					}
				}
			})],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),				
				iconCls: 'search16',
				id: 'EPLDDSW_SearchButton',
				tabIndex: TABINDEX_EPLDDSW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDDSW+91,
				text: BTN_FRMRESET
			},
			{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_EPLDDSW+94,
				text: BTN_FRMCANCEL
			}
			]
		});
		sw.Promed.swEvnPLDispDriverSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		key: [
			Ext.EventObject.C,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
		],
		stopEvent: true
	}, {
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispDriverSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.INSERT:
					current_window.addEvnPLDD();
					break;									
			}
		},
		key: [
			Ext.EventObject.INSERT	
		],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPLDDEditWindow: function(action) {
		var current_window = this;
		var EvnPLDispDriver_grid = current_window.findById('EPLDDSW_EvnPLDispDriverSearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispDriverEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dop_dispanserizatsii_uje_otkryito']);
			return false;
		}

		if (!EvnPLDispDriver_grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var EvnPLDispDriver_id = EvnPLDispDriver_grid.getSelectionModel().getSelected().data.EvnPLDispDriver_id;
		var person_id = EvnPLDispDriver_grid.getSelectionModel().getSelected().data.Person_id;
		var server_id = EvnPLDispDriver_grid.getSelectionModel().getSelected().data.Server_id;

		if (EvnPLDispDriver_id > 0 && person_id > 0 && server_id >= 0)
		{
			getWnd('swEvnPLDispDriverEditWindow').show({
				action: action,
				EvnPLDispDriver_id: EvnPLDispDriver_id,
				onHide: Ext.emptyFn,
				callback: function() {
					current_window.refreshEvnPLDDList();
				},
				Person_id: person_id,
				Server_id: server_id
			});
		}
	},
	plain: true,
	refreshEvnPLDDList: function(action) {
		this.doSearch();
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLDispDriverSearchWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();
		this.doReset();

		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		this.findById('EPLDDSW_EvnPLDispDriverSearchGrid').setActionDisabled('action_add', this.viewOnly);

		var win = this;
		if (!this.findById('EPLDDSW_EvnPLDispDriverSearchGrid').getAction('sign_actions')) {
			this.findById('EPLDDSW_EvnPLDispDriverSearchGrid').addActions({ name:'sign_actions', key: 'sign_actions', hidden: false, text:langs('Подписать'), menu: [
				new Ext.Action({
					name: 'action_signEvnPLDispDriver',
					text: langs('Подписать'),
					tooltip: langs('Подписать'),
					handler: function() {
						var me = this;
						var rec = win.findById('EPLDDSW_EvnPLDispDriverSearchGrid').getGrid().getSelectionModel().getSelected();
						if (rec && rec.get('EvnPLDispDriver_id')) {
							getWnd('swEMDSignWindow').show({
								EMDRegistry_ObjectName: 'EvnPLDispDriver',
								EMDRegistry_ObjectID: rec.get('EvnPLDispDriver_id'),
								callback: function(data) {
									if (data.preloader) {
										me.disable();
									}

									if (data.success || data.error) {
										me.enable();
									}

									if (data.success) {
										win.findById('EPLDDSW_EvnPLDispDriverSearchGrid').getGrid().getStore().reload();
									}
								}
							});
						}
					}
				}),
				new Ext.Action({
					name: 'action_showEvnPLDispDriverVersionList',
					text: langs('Версии документа'),
					tooltip: langs('Версии документа'),
					handler: function() {
						var rec = win.findById('EPLDDSW_EvnPLDispDriverSearchGrid').getGrid().getSelectionModel().getSelected();
						if (rec && rec.get('EvnPLDispDriver_id')) {
							getWnd('swEMDVersionViewWindow').show({
								EMDRegistry_ObjectName: 'EvnPLDispDriver',
								EMDRegistry_ObjectID: rec.get('EvnPLDispDriver_id')
							});
						}
					}
				})
			], tooltip: langs('Подписать'), iconCls : 'x-btn-text', icon: 'img/icons/digital-sign16.png', handler: function() {} });
		}

		if(this.viewOnly == true)
			this.buttons[2].hide();
		else
			this.buttons[2].show();
		
		var form = this.findById('EvnPLDispDriverSearchFilterForm');
		var base_form = form.getForm();

		this.findById('EPLDDSW_EvnPLDispDriverSearchGrid').getAction('action_print').setDisabled(false); // печать всегда доступна
	},
	title: 'Медицинское освидетельствование водителей: Поиск',
	width: 800
});
