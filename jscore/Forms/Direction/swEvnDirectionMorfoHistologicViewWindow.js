/**
* swEvnDirectionMorfoHistologicViewWindow - журнал направлений на патоморфогистологическое исследование
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Direction
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      13.10.2010
* @comment      Префикс для id компонентов EDMHVW (EvnDirectionMorfoHistologicViewWindow)
*
*
* Использует: направление на патологогистологическое исследование (swEvnDirectionMorfoHistologicEditWindow)
*/

sw.Promed.swEvnDirectionMorfoHistologicViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	deleteEvnDirectionMorfoHistologic: function() {
		var grid = this.SearchGrid;

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_napravleniya_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrano_napravlenie_iz_spiska']);
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var id = selected_record.get('EvnDirectionMorfoHistologic_id');

		if ( id == null || selected_record.get('accessType') != 'edit' ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_napravleniya_voznikli_oshibki_[tip_oshibki_2]']);
						},
						params: {
							EvnDirectionMorfoHistologic_id: id
						},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_napravleniya_voznikli_oshibki_[tip_oshibki_3]']);
							}
							else {
								grid.getGrid().getStore().remove(selected_record);

								if ( grid.getGrid().getStore().getCount() == 0 ) {
									grid.addEmptyRecord(grid.getGrid().getStore());
								}
							}

							grid.focus();
						},
						url: '/?c=EvnDirectionMorfoHistologic&m=deleteEvnDirectionMorfoHistologic'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_napravlenie'],
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
	id: 'EvnDirectionMorfoHistologicViewWindow',
	initComponent: function() {
		
		var win = this;
		this.datePeriodToolbar = new sw.Promed.datePeriodToolbar({
			curDate: getGlobalOptions().date,
			mode: 'day',
			onSelectPeriod: function(begDate,endDate,allowLoad)
			{
				this.findById('EDMHVW_begDate').setValue(begDate.format('d.m.Y'));
				this.findById('EDMHVW_endDate').setValue(endDate.format('d.m.Y'));
				if(allowLoad)
					this.loadGridWithFilter();
			}.createDelegate(this)
		});
		this.datePeriodToolbar.dateMenu.addListener('blur', 
			function () {
				this.datePeriodToolbar.onSelectMode('range',false);
			}.createDelegate(this)
		);
		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 150,
			tbar: win.datePeriodToolbar,
			id: 'EvnDirectionMorfoHistologicFilterForm',
			region: 'north',

			items: [{
						name: 'begDate',
						id: 'EDMHVW_begDate',
						xtype: 'hidden'
					},
					{
						name: 'endDate',
						id: 'EDMHVW_endDate',
						xtype: 'hidden'

					}, {
					
				layout: 'column',
				border: false,	
				items: [
					{
						border: false,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['seriya_napravleniya'],
								name: 'EvnDirectionMorfoHistologic_Ser',
								width: 175,
								xtype: 'textfield'
							}
						]
					},{
						bodyStyle: 'padding-right: 5px;',
						border: false,
						layout: 'form',
						items: [
							{
								fieldLabel: lang['nomer_napravleniya'],
								name: 'EvnDirectionMorfoHistologic_Num',
								width: 100,
								xtype: 'textfield'
							}
						]
					}
				]
					
			
			}, {
				allowBlank: true,
				codeField: 'EvnType_Code',
				displayField: 'EvnType_Name',
				editable: false,
				fieldLabel: lang['sostoyanie_napravleniya'],
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
				width: 175,
				xtype: 'swbaselocalcombo'
			},{
					
				layout: 'column',
				border: false,	
				items: [
					{
						border: false,
						layout: 'form',
						items: [
							{
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
								width: 175,
								xtype: 'textfield'
							}
						]
					},{
						bodyStyle: 'padding-right: 5px;',
						border: false,
						layout: 'form',
						items: [
							{
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
								width: 175,
								xtype: 'textfield'
							}
						]
					}
				]
					
			
			}],
		buttonAlign: 'left',
		buttons: [{
				handler: function() {
				win.loadGridWithFilter();
				},
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
				win.loadGridWithFilter(true);
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openEvnDirectionMorfoHistologicEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openEvnDirectionMorfoHistologicEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openEvnDirectionMorfoHistologicEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteEvnDirectionMorfoHistologic(); }.createDelegate(this) },
				{ name: 'action_print',
					menuConfig: {
						printObjectEvn: { text: lang['pechat_napravleniya'], handler: function() { this.printEvnDirectionMorfoHistologic(); }.createDelegate(this) }
					}
				}
			],
			autoLoadData: false,
			dataUrl: '/?c=EvnDirectionMorfoHistologic&m=loadEvnDirectionMorfoHistologicGrid',
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
					switch ( record.get('accessType') ) {
						case 'edit':
							this.SearchGrid.ViewActions.action_edit.setDisabled(false);
							this.SearchGrid.ViewActions.action_delete.setDisabled(false);
						break;

						case 'view':
							this.SearchGrid.ViewActions.action_edit.setDisabled(true);
							this.SearchGrid.ViewActions.action_delete.setDisabled(true);
						break;
					}

					if ( record.get('EvnMorfoHistologicProto_id') ) {
						this.SearchGrid.ViewActions.action_proto.setDisabled(false);
					}
					else {
						this.SearchGrid.ViewActions.action_proto.setDisabled(true);
					}

					if ( Number(record.get('EvnDirectionMorfoHistologic_IsBad')) == 1 ) {
						this.SearchGrid.ViewActions.action_isbad.setDisabled(true);
						this.SearchGrid.ViewActions.action_isnotbad.setDisabled(false);
					}
					else {
						this.SearchGrid.ViewActions.action_isnotbad.setDisabled(true);

						if ( record.get('EvnDirectionMorfoHistologic_id') ) {
							this.SearchGrid.ViewActions.action_isbad.setDisabled(false);
						}
						else {
							this.SearchGrid.ViewActions.action_isbad.setDisabled(true);
						}
					}
				}
			}.createDelegate(this),
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'EvnDirectionMorfoHistologic_id', type: 'int', header: 'ID', key: true },
				{ name: 'accessType', type: 'string', hidden: true },
				{ name: 'EvnDirectionMorfoHistologic_IsBad', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnMorfoHistologicProto_id', type: 'int', hidden: true },
				{ name: 'EvnDirectionMorfoHistologic_Ser', type: 'string', header: lang['seriya_napravleniya'], width: 120 },
				{ name: 'EvnDirectionMorfoHistologic_Num', type: 'string', header: lang['nomer_napravleniya'], width: 120 },
				{ name: 'EvnDirectionMorfoHistologic_setDate', type: 'date', format: 'd.m.Y', header: lang['data_napravleniya'] },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 100 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'] },
				{ name: 'EvnDirectionMorfoHistologic_deathDate', type: 'date', format: 'd.m.Y', header: lang['data_smerti'] },
				{ name: 'OrgAnatom_Name', type: 'string', header: lang['pat_-anatom_byuro'], id: 'autoexpand' },
				{ name: 'EvnDirectionMorfoHistologic_IsProto', type: 'checkbox', header: lang['protokol'], width: 100 }
			],
			// title: 'Направления на патомрофогистологическое исследование: Список',
			totalProperty: 'totalCount'
		});

		this.SearchGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if ( row.get('EvnDirectionMorfoHistologic_IsBad') == 1 ) {
					cls = cls + 'x-grid-rowgray';
				}
				else {
					cls = 'x-grid-panel'; 
				}

				return cls;
			}
		});

		Ext.apply(this, {
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
			defaults: {
				split: true
			},
			layout: 'border',
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

		sw.Promed.swEvnDirectionMorfoHistologicViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnDirectionMorfoHistologicViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					win.openEvnDirectionMorfoHistologicEditWindow('add');
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
		this.SearchGrid.setActionDisabled('action_proto', true);

		if ( clear ) {
			base_form.reset();
			this.SearchGrid.getAction('action_refresh').setDisabled(true);
			this.SearchGrid.gFilters = null;
		}
		else {
			var params = base_form.getValues();

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
	openEvnDirectionMorfoHistologicEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnDirectionMorfoHistologicEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_napravlenie_na_patologogistologicheskoe_issledovanie_uje_otkryito']);
			return false;
		}

		var grid = this.SearchGrid.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnDirectionMorfoHistologicData ) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.evnDirectionMorfoHistologicData.EvnDirectionMorfoHistologic_id);

			if ( record ) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnDirectionMorfoHistologicData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnDirectionMorfoHistologic_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ data: [ data.evnDirectionMorfoHistologicData ], totalCount: 1 }, true);
			}
		}
		params.formParams = new Object();

		if ( action == 'add' ) {
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.onHide = function() {
						// TODO: getWnd
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.formParams.Person_id =  person_data.Person_id;
					params.formParams.PersonEvn_id = person_data.PersonEvn_id;
					params.formParams.Server_id = person_data.Server_id;

					getWnd('swEvnDirectionMorfoHistologicEditWindow').show(params);
				},
				personFirname: this.FilterPanel.getForm().findField('Person_Firname').getValue(),
				//personSecname: this.FilterPanel.getForm().findField('Person_Secname').getValue(),
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

			var evn_direction_morfo_histologic_id = selected_record.get('EvnDirectionMorfoHistologic_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');

			if ( evn_direction_morfo_histologic_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.formParams.EvnDirectionMorfoHistologic_id = evn_direction_morfo_histologic_id;
				params.formParams.Person_id = person_id;
				params.formParams.Server_id = server_id;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};

				getWnd('swEvnDirectionMorfoHistologicEditWindow').show(params);
			}
		}
	},
	openEvnMorfoHistologicProtoViewWindow: function() {
		var grid = this.SearchGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var params = new Object();
		params.action = 'view';

		var evn_morfo_histologic_proto_id = selected_record.get('EvnMorfoHistologicProto_id');
		var person_id = selected_record.get('Person_id');
		var server_id = selected_record.get('Server_id');

		if ( evn_morfo_histologic_proto_id > 0 && person_id > 0 && server_id >= 0 ) {
			params.formParams = new Object();
			params.formParams.EvnMorfoHistologicProto_id = evn_morfo_histologic_proto_id;
			params.formParams.Person_id =  person_id;
			params.formParams.Server_id = server_id;

			getWnd('swEvnMorfoHistologicProtoEditWindow').show(params);
		}
	},
	printEvnDirectionMorfoHistologic: function() {
		var grid = this.SearchGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDirectionMorfoHistologic_id') ) {
			return false;
		}

		window.open('/?c=EvnDirectionMorfoHistologic&m=printEvnDirectionMorfoHistologic&EvnDirectionMorfoHistologic_id=' + grid.getSelectionModel().getSelected().get('EvnDirectionMorfoHistologic_id'), '_blank');
	},
	resizable: false,
	setIsBad: function(flag) {
		var grid = this.SearchGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var id = selected_record.get('EvnDirectionMorfoHistologic_id');

		if ( !id ) {
			return false;
		};

		if ( flag == true && Number(selected_record.get('EvnDirectionMorfoHistologic_IsBad')) == 1 ) {
			return false;
		}
		else if ( !flag && Number(selected_record.get('EvnDirectionMorfoHistologic_IsBad')) != 1 ) {
			return false;
		}

		this.getLoadMask().show();

		Ext.Ajax.request({
			failure: function(response, options) {
				this.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri'] + (flag == true ? lang['annulirovanii'] : lang['otmene_annulirovaniya']) + lang['patomorfogistologicheskogo_napravleniya_[tip_oshibki_2]']);
			}.createDelegate(this),
			params: {
				EvnDirectionMorfoHistologic_id: id,
				EvnDirectionMorfoHistologic_IsBad: (flag == true ? 1 : 0)
			},
			success: function(response, options) {
				this.getLoadMask().hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if ( response_obj.success == false ) {
					sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri'] + (flag == true ? lang['annulirovanii'] : lang['otmene_annulirovaniya']) + lang['patomorfogistologicheskogo_napravleniya_[tip_oshibki_3]']);
				}
				else {
					var base_form = this.FilterPanel.getForm();

					var type = base_form.findField('EvnType_id').getValue();

					if ( (type == 2 && flag == true) || (type == 3 && !flag) ) {
						grid.getStore().remove(selected_record);
					}
					else {
						selected_record.set('EvnDirectionMorfoHistologic_IsBad', (flag == true ? 1 : 0));
						selected_record.commit();

						this.SearchGrid.onRowSelect(
							grid.getSelectionModel(),
							grid.getStore().indexOf(selected_record),
							selected_record
						);
					}
				}
			}.createDelegate(this),
			url: '/?c=EvnDirectionMorfoHistologic&m=setEvnDirectionMorfoHistologicIsBad'
		});
	},
	show: function() {
		sw.Promed.swEvnDirectionMorfoHistologicViewWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();
		this.datePeriodToolbar.onShow(true);
		if ( !this.SearchGrid.getAction('action_proto') ) {
			this.SearchGrid.addActions({
				disabled: true,
				handler: function() {
					this.openEvnMorfoHistologicProtoViewWindow();
				}.createDelegate(this),
				name: 'action_proto',
				text: lang['otkryit_protokol']
			});
		}

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
	title: lang['jurnal_napravleniy_na_patomorfogistologicheskoe_issledovanie_spisok'], 
	width: 800
});