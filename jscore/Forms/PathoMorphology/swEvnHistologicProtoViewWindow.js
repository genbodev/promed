/**
* swEvnHistologicProtoViewWindow - журнал протоколов патологогистологических исследований
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      PathoMorphology
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      28.08.2010
* @comment      Префикс для id компонентов EHPVW (EvnHistologicProtoViewWindow)
*
*
* Использует: протокол патологогистологического исследования (swEvnHistologicProtoEditWindow)
*/

sw.Promed.swEvnHistologicProtoViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	deleteEvnHistologicProto: function() {
		var grid = this.SearchGrid;

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_protokola_patologogistologicheskogo_issledovaniya_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_protokol_patologogistologicheskogo_issledovaniya_iz_spiska']);
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var id = selected_record.get('EvnHistologicProto_id');

		if ( !id || selected_record.get('accessType') != 'edit' ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
					failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_protokola_patologogistologicheskogo_issledovaniya_voznikli_oshibki_[tip_oshibki_2]']);
						},
						params: {
							EvnHistologicProto_id: id
						},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_protokola_patologogistologicheskogo_issledovaniya_voznikli_oshibki_[tip_oshibki_3]']);
							}
							else {
								grid.getGrid().getStore().remove(selected_record);

								if ( grid.getGrid().getStore().getCount() == 0 ) {
									grid.addEmptyRecord(grid.getGrid().getStore());
								}
							}

							grid.focus();
						},
						url: '/?c=EvnHistologicProto&m=deleteEvnHistologicProto'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_protokol_patologogistologicheskogo_issledovaniya'],
			title: lang['vopros']
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['podojdite'] });
		}

		return this.loadMask;
	},
	height: 500,
	id: 'EvnHistologicProtoViewWindow',
	initComponent: function() {
		var win = this;
		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 180,
			id: 'EvnHistologicProtoFilterForm',
			region: 'north',

			items: [{
				layout: 'column',
				border: false,	
				items: [{
					bodyStyle: 'padding-right: 5px;',
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: lang['familiya'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						maxLength: 30,
						name: 'Person_Surname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 220,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['imya'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						maxLength: 30,
						name: 'Person_Firname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 220,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['otchestvo'],
						listeners: {
							'keydown': function (f, e) {
								if ( e.getKey() == e.ENTER ) {
									this.loadGridWithFilter();
								}
							}.createDelegate(this)
						},
						maxLength: 30,
						name: 'Person_Secname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 220,
						xtype: 'textfield'
					},
					{
						border: false,
						layout: 'column',
						items: [
						{	layout: 'form',
							border: false,
							labelWidth: 180,
							items: [{
								xtype: 'numberfield',
								name: 'minAge',
								fieldLabel: lang['vozrast_s'],
								value: '',
								maxValue: 99,
								minValue: 0,
								width:40,
								listeners: {
									'keydown': function (f, e) {alert("d");
										if ( e.getKey() == e.ENTER ) {
											this.loadGridWithFilter();
										}
									}.createDelegate(this)
								}
								}]},
						{
							
							layout: 'form',
							border: false,
							labelWidth: 30,
							items: [{
								xtype: 'numberfield',
								name: 'maxAge',
								fieldLabel: lang['po'],
								value: '',
								maxValue: 99,
								minValue: 0,
								width:40,
								listeners: {
									'keydown': function (f, e) {
										if ( e.getKey() == e.ENTER ) {
											this.loadGridWithFilter();
										}
									}.createDelegate(this)
								}
							}]}
							]
						},
						{
							allowBlank: false,
							name : "setDateRange",
							xtype : "daterangefield",
							width : 170,
							fieldLabel : "Дата поступления материала",
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							tabIndex : 304
						},
						{
							name : "didDateRange",
							xtype : "daterangefield",
							width : 170,
							fieldLabel : "Дата исследования",
							plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							tabIndex : 304
						},
						{
						items : [{
							labelWidth : 180,
							items : [{
								allowBlank: true,
								displayField: 'Diag_Name',
								emptyText: lang['vvedite_kod_diagnoza'],
								fieldLabel: lang['kod_diagnoza_s'],
								hiddenName: 'PT_Diag_Code_From',
								hideTrigger: false,
								id: 'PTS_DiagComboFrom',
								valueField: 'Diag_Code',
								width: 220,
								xtype: 'swdiagcombo',
								tabIndex : 311
							}],
							layout : "form",
							width : 410,
							border : false
						}, {
							labelWidth : 20,
							items : [{
								allowBlank: true,
								displayField: 'Diag_Name',
								emptyText: lang['vvedite_kod_diagnoza'],
								fieldLabel: lang['po'],
								hiddenName: 'PT_Diag_Code_To',
								hideTrigger: false,
								id: 'PTS_DiagComboTo',
								listeners: {
									'keydown': function (inp, e) {
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB) {
											if (EvnReceptSearchViewGrid.getStore().getCount() > 0) {
												e.stopEvent();
												TabToGrid(EvnReceptSearchViewGrid);
											}
										}
									}
								},
								valueField: 'Diag_Code',
								width: 220,
								xtype: 'swdiagcombo',
								tabIndex : 312
							}],
							layout : "form",
							width : 300,
							border : false
						}],
						layout : "column",
						autoHeight : true,
						border : false
					}]
				}, {
					bodyStyle: 'padding-right: 5px;',
					border: false,
					layout: 'form',
					items: [{
						disabled: false,
						handler: function () {
							this.loadGridWithFilter();
						}.createDelegate(this),
						minWidth: 125,
						text: lang['ustanovit_filtr'],
						topLevel: true,
						xtype: 'button'
					}, {
						disabled: false,
						handler: function () {
							this.loadGridWithFilter(true);
						}.createDelegate(this),
						minWidth: 125,
						text: lang['snyat_filtr'],
						topLevel: true,
						xtype: 'button'
					}]
				}]
			}, {
				allowBlank: true,
				codeField: 'EvnType_Code',
				displayField: 'EvnType_Name',
				editable: false,
				fieldLabel: lang['sostoyanie_protokola'],
				hiddenName: 'EvnType_id',
				hideEmptyRow: true,
				listeners: {
					'blur': function(combo)  {
						if ( combo.value == '' )
							combo.setValue(1);
					}
				},
				store: new Ext.data.SimpleStore({
					autoLoad: true,
					data: [
						[ 1, 1, lang['vse'] ],
						[ 2, 2, lang['tolko_deystvuyuschie'] ],
						[ 3, 3, lang['tolko_isporchennyie'] ]
					],
					fields: [
						{ name: 'EvnType_id', type: 'int'},
						{ name: 'EvnType_Code', type: 'int'},
						{ name: 'EvnType_Name', type: 'string'}
					],
					key: 'EvnType_id',
					sortInfo: { field: 'EvnType_Code' }
				}),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<font color="red">{EvnType_Code}</font>&nbsp;{EvnType_Name}',
					'</div></tpl>'
				),
				value: 1,
				valueField: 'EvnType_id',
				width: 220,
				xtype: 'swbaselocalcombo'
			}]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: !(isPathoMorphoUser() || isOperator()), handler: function() { this.openEvnHistologicProtoEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openEvnHistologicProtoEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openEvnHistologicProtoEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteEvnHistologicProto(); }.createDelegate(this) },
				{ name: 'action_print',
					menuConfig: {
						printObjectProt: { text: langs('Печать протокола'), handler: function() { this.printF014u('1'); }.createDelegate(this), hidden: getRegionNick() == 'kz' },
						printObjectDirection: { text: langs('Печать направления'), handler: function() { this.printF014u('0'); }.createDelegate(this), hidden: getRegionNick() == 'kz' },
						printObjectF014u: { text: langs('Печать формы № 014/у'), handler: function() { this.printF014u('2'); }.createDelegate(this) }
					}
				}
			],
			autoLoadData: false,
			dataUrl: '/?c=EvnHistologicProto&m=loadEvnHistologicProtoGrid',
			height: 203,
			id: this.id + 'SearchGrid',
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
				else {
					this.ViewActions.action_view.execute();
				}
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, index, record) {
				var disallowEdit = !(record.get('accessType') == 'edit'),
					disallowIsNotBad = !(Number(record.get('EvnHistologicProto_IsBad')) == 1),
					disallowIsBad = (Number(record.get('EvnHistologicProto_IsBad')) == 1) || !record.get('EvnHistologicProto_id');
				if(win.viewOnly == true)
				{
					this.SearchGrid.ViewActions.action_add.setDisabled(true);
					this.SearchGrid.ViewActions.action_edit.setDisabled(true);
					this.SearchGrid.ViewActions.action_delete.setDisabled(true);
					this.SearchGrid.ViewActions.action_isbad.setDisabled(true);
					this.SearchGrid.ViewActions.action_isnotbad.setDisabled(true);
				}
				else
				{
					this.SearchGrid.ViewActions.action_edit.setDisabled(disallowEdit);
					this.SearchGrid.ViewActions.action_delete.setDisabled(disallowEdit);
					this.SearchGrid.ViewActions.action_isnotbad.setDisabled(disallowIsNotBad);
					this.SearchGrid.ViewActions.action_isbad.setDisabled(disallowIsBad);
				}

			}.createDelegate(this),
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'EvnHistologicProto_id', type: 'int', header: 'ID', key: true },
				{ name: 'accessType', type: 'string', hidden: true },
				{ name: 'EvnDirectionHistologic_id', type: 'int', hidden: true },
				{ name: 'EvnDirectionHistologic_IsBad', type: 'int', hidden: true },
				{ name: 'EvnHistologicProto_IsBad', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'MedPersonal_id', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'EvnHistologicProto_Ser', type: 'string', header: langs('Серия'), width: 120 },
				{ name: 'EvnHistologicProto_Num', type: 'string', header: langs('Номер'), width: 120 },
				{ name: 'EvnHistologicProto_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата поступления материала') },
				{ name: 'EvnHistologicProto_didDate', type: 'date', format: 'd.m.Y', header: langs('Дата исследования') },
				{ name: 'Lpu_Name', type: 'string', header: langs('Направившее ЛПУ'), width: 250 },
				{ name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 250 },
				{ name: 'EvnDirectionHistologic_NumCard', type: 'string', header: langs('№ карты'), width: 80 },
				{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 100 },
				{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 100 },
				{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения') },
				{ name: 'MedPersonal_Fio', type: 'string', header: langs('ФИО патологоанатома'), width: 250 }
			],
			title: lang['protokolyi_patologogistologicheskih_issledovaniy_spisok'],
			totalProperty: 'totalCount'
		});
		
		this.SearchGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if ( row.get('EvnHistologicProto_IsBad') == 1 ) {
					cls = cls + 'x-grid-rowgray';
				}
				else {
					cls = 'x-grid-panel'; 
				}

				return cls;
			}
		});

		Ext.apply(this, {
			layout:'border',
			defaults: {
				split: true
			},
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()  {
					this.hide()
				}.createDelegate(this),
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: [ this.FilterPanel,
			{
				border: false,
				layout: 'border',
				region: 'center',
				xtype: 'panel',

				items: [
					this.SearchGrid
				]
			}]
		});

		sw.Promed.swEvnHistologicProtoViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnHistologicProtoViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					win.openEvnHistologicProtoEditWindow('add');
				break;
			}
		},
		key: [ Ext.EventObject.INSERT ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'beforeshow': function() {
			//
		}
	},
	loadGridWithFilter: function(clear) {
		var base_form = this.FilterPanel.getForm();

		this.SearchGrid.removeAll();

		if ( clear ) {
			base_form.reset();
			var date1 = getValidDT(getGlobalOptions().date, '').add(Date.MONTH, -1).format('d.m.Y');
			var date2 = getGlobalOptions().date;

			base_form.findField('setDateRange').setValue(date1 + ' - ' + date2);

			this.SearchGrid.getAction('action_refresh').setDisabled(true);
			this.SearchGrid.gFilters = null;
		}
		else {
			if ( !base_form.isValid() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.FilterPanel.getFirstInvalidEl().focus(true);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			var didMax = Ext.util.Format.date(base_form.findField('didDateRange').getValue2(),'d.m.Y');
			var didMin = Ext.util.Format.date(base_form.findField('didDateRange').getValue1(),'d.m.Y');
			var setMax = Ext.util.Format.date(base_form.findField('setDateRange').getValue2(),'d.m.Y');
			var setMin = Ext.util.Format.date(base_form.findField('setDateRange').getValue1(),'d.m.Y');
			var params = base_form.getValues();
			params.didRangeStart = didMin;
			params.didRangeEnd = didMax;
			params.setRangeStart = setMin;
			params.setRangeEnd = setMax;
			params.limit = 100;
			params.start = 0;
		
			this.SearchGrid.loadData({
				globalFilters: params
			});
		}
	},
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	openEvnHistologicProtoEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnHistologicProtoEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_protokola_patologogistologicheskogo_issledovaniya_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var grid = this.SearchGrid.getGrid();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnHistologicProtoData ) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.evnHistologicProtoData.EvnHistologicProto_id);

			if ( record ) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					if ( data.evnHistologicProtoData[grid_fields[i]] != undefined ) {
						record.set(grid_fields[i], data.evnHistologicProtoData[grid_fields[i]]);
					}
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnHistologicProto_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ data: [ data.evnHistologicProtoData ], totalCount: 1 }, true);
			}
		}
		params.formParams = new Object();

		if ( action == 'add' ) {
			getWnd('swPersonSearchWindow').show({
				//allowUnknownPerson: true,
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.onHide = function() {
						// TODO: Продумать использование getWnd в таких случаях
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.formParams.Person_id =  person_data.Person_id;
					params.formParams.PersonEvn_id = person_data.PersonEvn_id;
					params.formParams.Server_id = person_data.Server_id;

					getWnd('swEvnHistologicProtoEditWindow').show(params);
				},
				personFirname: this.FilterPanel.getForm().findField('Person_Firname').getValue(),
				personSecname: this.FilterPanel.getForm().findField('Person_Secname').getValue(),
				personSurname: this.FilterPanel.getForm().findField('Person_Surname').getValue(),
				searchMode: 'all'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			if ( selected_record.get('accessType') != 'edit' ) {
				params.action = 'view';
			}

			var evn_histologic_proto_id = selected_record.get('EvnHistologicProto_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');

			if ( evn_histologic_proto_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.formParams.EvnHistologicProto_id = evn_histologic_proto_id;
				params.formParams.Person_id =  person_id;
				params.formParams.Server_id = server_id;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};

				getWnd('swEvnHistologicProtoEditWindow').show(params);
			}
		}
	},
	printF014u: function(mode) {
		if ( typeof mode != 'string' || !mode.inlist([ '0', '1', '2' ]) ) {
			return false;
		}

		var grid = this.SearchGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var
			EvnDirectionHistologic_id = grid.getSelectionModel().getSelected().get('EvnDirectionHistologic_id'),
			EvnHistologicProto_id = grid.getSelectionModel().getSelected().get('EvnHistologicProto_id');

		if ( Ext.isEmpty(EvnDirectionHistologic_id) && Ext.isEmpty(EvnHistologicProto_id) ) {
			return false;
		}
		else if ( mode == '0' && Ext.isEmpty(EvnDirectionHistologic_id) ) {
			return false;
		}
		else if ( mode == '1' && Ext.isEmpty(EvnHistologicProto_id) ) {
			return false;
		}
		else if ( mode == '2' && (Ext.isEmpty(EvnDirectionHistologic_id) || Ext.isEmpty(EvnHistologicProto_id)) ) {
			return false;
		}

		if ( mode == '2' ) {
			printBirt({
				'Report_FileName': 'f014u.rptdesign',
				'Report_Params': '&paramPrintPage=0&paramEvnDirectionHistologic=' + EvnDirectionHistologic_id + '&paramEvnHistologicProto=-1',
				'Report_Format': 'pdf'
			});

			printBirt({
				'Report_FileName': 'f014u.rptdesign',
				'Report_Params': '&paramPrintPage=1&paramEvnDirectionHistologic=-1&paramEvnHistologicProto=' + EvnHistologicProto_id,
				'Report_Format': 'pdf'
			});
		}
		else {
			if ( mode == '0' ) {
				printBirt({
					'Report_FileName': 'f014u_DirectionHistologic.rptdesign',
					'Report_Params': '&paramEvnDirectionHistologic=' + EvnDirectionHistologic_id,
					'Report_Format': 'pdf'
				});
			}

			if ( mode == '1' ) {
				printBirt({
					'Report_FileName': 'f014u_HistologicProtocol.rptdesign',
					'Report_Params': '&paramEvnHistologicProto=' + EvnHistologicProto_id,
					'Report_Format': 'pdf'
				});
			}
		}

		return true;
	},
	resizable: false,
	setIsBad: function(flag) {
		var grid = this.SearchGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var id = selected_record.get('EvnHistologicProto_id');

		if ( !id ) {
			return false;
		};

		if ( flag == true && Number(selected_record.get('EvnHistologicProto_IsBad')) == 1 ) {
			return false;
		}
		else if ( !flag && Number(selected_record.get('EvnHistologicProto_IsBad')) != 1 ) {
			return false;
		}

		this.getLoadMask().show();

		Ext.Ajax.request({
			failure: function(response, options) {
				this.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri'] + (flag == true ? lang['annulirovanii'] : lang['otmene_annulirovaniya']) + lang['protokola_patologogistologicheskogo_issledovaniya_[tip_oshibki_2]']);
			}.createDelegate(this),
			params: {
				EvnHistologicProto_id: id,
				EvnHistologicProto_IsBad: (flag == true ? 1 : 0)
			},
			success: function(response, options) {
				this.getLoadMask().hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if ( response_obj.success == false ) {
					sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri'] + (flag == true ? lang['annulirovanii'] : lang['otmene_annulirovaniya']) + lang['protokola_patologogistologicheskogo_issledovaniya_[tip_oshibki_3]']);
				}
				else {
					var base_form = this.FilterPanel.getForm();

					var type = base_form.findField('EvnType_id').getValue();

					if ( (type == 2 && flag == true) || (type == 3 && !flag) ) {
						grid.getStore().remove(selected_record);
					}
					else {
						selected_record.set('EvnHistologicProto_IsBad', (flag == true ? 1 : 0));
						selected_record.commit();

						this.SearchGrid.onRowSelect(
							grid.getSelectionModel(),
							grid.getStore().indexOf(selected_record),
							selected_record
						);
					}
				}
			}.createDelegate(this),
			url: '/?c=EvnHistologicProto&m=setEvnHistologicProtoIsBad'
		});
	},
	show: function() {
		sw.Promed.swEvnHistologicProtoViewWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();

		if ( !this.SearchGrid.getAction('action_isbad') ) {
			this.SearchGrid.addActions({
				disabled: true,
				handler: function() {
					this.setIsBad(true);
				}.createDelegate(this),
				name: 'action_isbad',
				text: lang['annulirovat']
			});
		}

		if ( !this.SearchGrid.getAction('action_isnotbad') ) {
			this.SearchGrid.addActions({
				disabled: true,
				handler: function() {
					this.setIsBad(false);
				}.createDelegate(this),
				name: 'action_isnotbad',
				text: lang['otmenit_annulirovanie']
			});
		}

		this.center();
		this.maximize();
		this.loadGridWithFilter(true);

		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		//this.SearchGrid.ViewActions.action_add.setDisabled(this.viewOnly);
		this.SearchGrid.setActionDisabled('action_add', this.viewOnly);

		this.getLoadMask().hide();
	},
	title: lang['jurnal_protokolov_patologogistologicheskih_issledovaniy'], 
	width: 800
});