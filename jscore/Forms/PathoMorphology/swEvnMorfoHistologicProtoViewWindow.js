/**
* swEvnMorfoHistologicProtoViewWindow - журнал протоколов патоморфогистологических исследований
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      PathoMorphology
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      10.02.2011
* @comment      Префикс для id компонентов EMHPVW (EvnMorfoHistologicProtoViewWindow)
*
*
* Использует: протокол патоморфогистологического исследования (swEvnMorfoHistologicProtoEditWindow)
*/

sw.Promed.swEvnMorfoHistologicProtoViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	deleteEvnMorfoHistologicProto: function() {
		var grid = this.SearchGrid;

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_protokola_patomorfogistologicheskogo_issledovaniya_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_protokol_patomorfogistologicheskogo_issledovaniya_iz_spiska']);
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var id = selected_record.get('EvnMorfoHistologicProto_id');

		if ( !id || selected_record.get('accessType') != 'edit' ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_protokola_patomorfogistologicheskogo_issledovaniya_voznikli_oshibki_[tip_oshibki_2]']);
						},
						params: {
							EvnMorfoHistologicProto_id: id
						},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_protokola_patomorfogistologicheskogo_issledovaniya_voznikli_oshibki_[tip_oshibki_3]']);
							}
							else {
								grid.getGrid().getStore().remove(selected_record);

								if ( grid.getGrid().getStore().getCount() == 0 ) {
									grid.addEmptyRecord(grid.getGrid().getStore());
								}
							}

							grid.focus();
						},
						url: '/?c=EvnMorfoHistologicProto&m=deleteEvnMorfoHistologicProto'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_protokol_patomorfogistologicheskogo_issledovaniya'],
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
	id: 'EvnMorfoHistologicProtoViewWindow',
	initComponent: function() {
		var win = this;
		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 150,
			id: 'EvnMorfoHistologicProtoFilterForm',
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
						width: 175,
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
						width: 175,
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
						width: 175,
						xtype: 'textfield'
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
				width: 170,
				xtype: 'swbaselocalcombo'
			}]
		});
		
		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: !(isPathoMorphoUser() || isOperator()), handler: function() { this.openEvnMorfoHistologicProtoEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openEvnMorfoHistologicProtoEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openEvnMorfoHistologicProtoEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteEvnMorfoHistologicProto(); }.createDelegate(this) },
				{ name: 'action_print',
					menuConfig: {
						printObjectProt: { text: lang['pechat_protokola'], handler: function() { this.printEvnMorfoHistologicProto(); }.createDelegate(this) }
					}
				}
			],
			autoLoadData: false,
			dataUrl: '/?c=EvnMorfoHistologicProto&m=loadEvnMorfoHistologicProtoGrid',
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
					disallowIsNotBad = !(Number(record.get('EvnMorfoHistologicProto_IsBad')) == 1),
					disallowIsBad = (Number(record.get('EvnMorfoHistologicProto_IsBad')) == 1) || !record.get('EvnMorfoHistologicProto_id');
				
				if(win.viewOnly == true)
				{
					this.SearchGrid.setActionDisabled('action_add', true);
					this.SearchGrid.setActionDisabled('action_edit', true);
					this.SearchGrid.setActionDisabled('action_delete', true);
					this.SearchGrid.setActionDisabled('action_isbad', true);
					this.SearchGrid.setActionDisabled('action_isnotbad', true);
				}
				else
				{
					this.SearchGrid.setActionDisabled('action_edit', disallowEdit);
					this.SearchGrid.setActionDisabled('action_delete', disallowEdit);
					this.SearchGrid.setActionDisabled('action_isnotbad', disallowIsNotBad);
					this.SearchGrid.setActionDisabled('action_isbad', disallowIsBad);
				}
			}.createDelegate(this),
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'EvnMorfoHistologicProto_id', type: 'int', header: 'ID', key: true },
				{ name: 'accessType', type: 'string', hidden: true },
				{ name: 'EvnDirectionMorfoHistologic_IsBad', type: 'int', hidden: true },
				{ name: 'EvnMorfoHistologicProto_IsBad', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'MedPersonal_id', type: 'int', hidden: true },
				{ name: 'MedPersonal_aid', type: 'int', hidden: true },
				{ name: 'Lpu_id', type: 'int', hidden: true },
				{ name: 'EvnMorfoHistologicProto_Ser', type: 'string', header: lang['seriya'], width: 120 },
				{ name: 'EvnMorfoHistologicProto_Num', type: 'string', header: lang['nomer'], width: 120 },
				{ name: 'Lpu_Name', type: 'string', header: lang['napravivshee_lpu'], width: 250 },
				{ name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 250 },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 100 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'] },
				{ name: 'MedPersonal_Fio', type: 'string', header: lang['fio_patologoanatoma'], width: 250 }
			],
			title: lang['protokolyi_patomorfogistologicheskih_issledovaniy_spisok'],
			totalProperty: 'totalCount'
		});
		
		this.SearchGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if ( row.get('EvnMorfoHistologicProto_IsBad') == 1 ) {
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

		sw.Promed.swEvnMorfoHistologicProtoViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnMorfoHistologicProtoViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					win.openEvnMorfoHistologicProtoEditWindow('add');
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
	openEvnMorfoHistologicProtoEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnMorfoHistologicProtoEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_protokola_patomorfogistologicheskogo_issledovaniya_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var grid = this.SearchGrid.getGrid();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnMorfoHistologicProtoData ) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.evnMorfoHistologicProtoData.EvnMorfoHistologicProto_id);

			if ( record ) {
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnMorfoHistologicProtoData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnMorfoHistologicProto_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ data: [ data.evnMorfoHistologicProtoData ], totalCount: 1 }, true);
			}
		}
		params.formParams = new Object();

		if ( action == 'add' ) {
			getWnd('swPersonSearchWindow').show({
				allowUnknownPerson: true,
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					params.onHide = function() {
						// TODO: Продумать использование getWnd в таких случаях
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					params.formParams.Person_id =  person_data.Person_id;
					params.formParams.PersonEvn_id = person_data.PersonEvn_id;
					params.formParams.Server_id = person_data.Server_id;

					getWnd('swEvnMorfoHistologicProtoEditWindow').show(params);
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

			var evn_morfo_histologic_proto_id = selected_record.get('EvnMorfoHistologicProto_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');

			if ( evn_morfo_histologic_proto_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.formParams.EvnMorfoHistologicProto_id = evn_morfo_histologic_proto_id;
				params.formParams.Person_id =  person_id;
				params.formParams.Server_id = server_id;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};

				getWnd('swEvnMorfoHistologicProtoEditWindow').show(params);
			}
		}
	},
	printEvnMorfoHistologicProto: function() {
		var grid = this.SearchGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnMorfoHistologicProto_id') ) {
			return false;
		}

		window.open('/?c=EvnMorfoHistologicProto&m=printEvnMorfoHistologicProto&EvnMorfoHistologicProto_id=' + grid.getSelectionModel().getSelected().get('EvnMorfoHistologicProto_id'), '_blank');
	},
	resizable: false,
	setIsBad: function(flag) {
		var grid = this.SearchGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var id = selected_record.get('EvnMorfoHistologicProto_id');

		if ( !id ) {
			return false;
		};

		if ( flag == true && Number(selected_record.get('EvnMorfoHistologicProto_IsBad')) == 1 ) {
			return false;
		}
		else if ( !flag && Number(selected_record.get('EvnMorfoHistologicProto_IsBad')) != 1 ) {
			return false;
		}

		this.getLoadMask().show();

		Ext.Ajax.request({
			failure: function(response, options) {
				this.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri'] + (flag == true ? lang['annulirovanii'] : lang['otmene_annulirovaniya']) + lang['protokola_patomorfogistologicheskogo_issledovaniya_[tip_oshibki_2]']);
			}.createDelegate(this),
			params: {
				EvnMorfoHistologicProto_id: id,
				EvnMorfoHistologicProto_IsBad: (flag == true ? 1 : 0)
			},
			success: function(response, options) {
				this.getLoadMask().hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);

				if ( response_obj.success == false ) {
					sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri'] + (flag == true ? lang['annulirovanii'] : lang['otmene_annulirovaniya']) + lang['protokola_patomorfogistologicheskogo_issledovaniya_[tip_oshibki_3]']);
				}
				else {
					var base_form = this.FilterPanel.getForm();

					var type = base_form.findField('EvnType_id').getValue();

					if ( (type == 2 && flag == true) || (type == 3 && !flag) ) {
						grid.getStore().remove(selected_record);
					}
					else {
						selected_record.set('EvnMorfoHistologicProto_IsBad', (flag == true ? 1 : 0));
						selected_record.commit();

						this.SearchGrid.onRowSelect(
							grid.getSelectionModel(),
							grid.getStore().indexOf(selected_record),
							selected_record
						);
					}
				}
			}.createDelegate(this),
			url: '/?c=EvnMorfoHistologicProto&m=setEvnMorfoHistologicProtoIsBad'
		});
	},
	show: function() {
		sw.Promed.swEvnMorfoHistologicProtoViewWindow.superclass.show.apply(this, arguments);
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
		this.SearchGrid.removeAll();
		this.SearchGrid.gFilters = null;
		
		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		this.SearchGrid.setActionDisabled('action_add', this.viewOnly);

		this.getLoadMask().hide();
	},
	title: lang['jurnal_protokolov_patomorfogistologicheskih_issledovaniy'], 
	width: 800
});