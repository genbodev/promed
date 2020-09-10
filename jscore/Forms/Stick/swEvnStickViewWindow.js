/**
* swEvnStickViewWindow - форма поиска ЛВН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Stick
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      05.09.2010
* @comment      Префикс для id компонентов ESVW (EvnStickViewWindow)
*
*
* Использует: листок временной нетрудоспособности (swEvnStickEditWindow)
*/

sw.Promed.swEvnStickViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	deleteEvnStick: function() {
		var grid = this.SearchGrid.getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_lvn_spravki_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_lvn_spravka_iz_spiska']);
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var evnStickType = selected_record.get('evnStickType');
		var id = selected_record.get('EvnStickBase_id');

		if ( !id ) {
			return false;
		}

		var error = '';
		var question = '';
		var params = new Object();
		var url = '';

		if ( evnStickType == 3 ) {
			error = lang['pri_udalenii_spravki_uchaschegosya_voznikli_oshibki'];
			question = lang['udalit_spravku_uchaschegosya'];
			url = '/?c=Stick&m=deleteEvnStickStudent';

			params['EvnStickStudent_id'] = id;
		}
		else {
			error = lang['pri_udalenii_lista_netrudosposobnosti_voznikli_oshibki'];
			question = lang['udalit_list_netrudosposobnosti'];
			url = '/?c=Stick&m=deleteEvnStick';

			params['EvnStick_id'] = id;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();

							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								if (response_obj.IsDelQueue) {
									sw.swMsg.alert('Внимание', 'ЛВН добавлен в очередь на удаление');
									selected_record.set('EvnStick_IsDelQueue', 2);
									selected_record.commit();
								} else {
									grid.getStore().remove(selected_record);
								}

								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}
							}
							
							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						}.createDelegate(this),
						url: url
					});
				}
				else {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
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
	id: 'EvnStickViewWindow',
	getFilterForm: function() {
		return this.FilterPanel;
	},
	initComponent: function() {
		var win = this;
		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 210,
			id: 'EvnStickFilterForm',
			region: 'north',

			items: [{
				layout: 'column',
				border: false,	
				items: [{
					bodyStyle: 'padding-right: 5px;',
					border: false,
					layout: 'form',
					items: [{
						comboSubject: 'StickType',
						fieldLabel: lang['tip_lista'],
						hiddenName: 'StickType_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.FilterPanel.getForm();
								if (combo.getFieldValue('StickType_Code') == 1) {
									base_form.findField('EvnStick_IsClosed').enable();
								} else {
									base_form.findField('EvnStick_IsClosed').clearValue();
									base_form.findField('EvnStick_IsClosed').disable();
								}
							}.createDelegate(this)
						},
						listWidth: 200,
						width: 170,
						xtype: 'swcommonsprcombo'
					}, {
						allowBlank: true,
						codeField: 'SearchType_Code',
						displayField: 'SearchType_Name',
						editable: false,
						fieldLabel: lang['rejim_poiska'],
						hiddenName: 'SearchType_id',
						store: new Ext.data.SimpleStore({
							autoLoad: true,
							data: [
								[ 1, 1, lang['po_vyipisannyim_v_nashem_mo'] ],
								[ 2, 2, lang['po_napravlennyim_v_nashe_mo'] ]
							],
							fields: [
								{ name: 'SearchType_id', type: 'int'},
								{ name: 'SearchType_Code', type: 'int'},
								{ name: 'SearchType_Name', type: 'string'}
							],
							
							key: 'SearchType_id',
							sortInfo: { field: 'SearchType_id' }
							
						}),
						tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item"><font color="red">{SearchType_Code}</font>&nbsp;{SearchType_Name}</div></tpl>'),
						value: (getGlobalOptions().lpu_id>0)?1:0,
						valueField: 'SearchType_id',
						width: 170,
						listWidth: 200,
						xtype: 'swbaselocalcombo'
					}, {
						enableKeyEvents: true,
						fieldLabel: lang['seriya'],
						autoCreate: {tag: "input", size:20, maxLength: "20", autocomplete: "off"},
						name: 'EvnStickBase_Ser',
						width: 170,
						xtype: 'textfield'
					}, {
						enableKeyEvents: true,
						fieldLabel: lang['nomer'],
						autoCreate: {tag: "input", size:20, maxLength: "20", autocomplete: "off"},
						name: 'EvnStickBase_Num',
						width: 170,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['nachalo_perioda_osvobojdeniya'],
						format: 'd.m.Y',
						name: 'EvnStickBase_begDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}, {
						fieldLabel: lang['okonchanie_perioda_osvobojdeniya'],
						format: 'd.m.Y',
						name: 'EvnStickBase_endDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}]
				}, {
					bodyStyle: 'padding-right: 5px;',
					border: false,
					labelWidth: 110,
					layout: 'form',
					items: [{
						fieldLabel: lang['familiya'],
						maxLength: 30,
						name: 'Person_Surname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 175,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['imya'],
						maxLength: 30,
						name: 'Person_Firname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 175,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['otchestvo'],
						maxLength: 30,
						name: 'Person_Secname',
						plugins: [ new Ext.ux.translit(true, true) ],
						width: 175,
						xtype: 'textfield'
					}, {
						fieldLabel: lang['data_rojdeniya'],
						format: 'd.m.Y',
						name: 'Person_Birthday',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
						width: 100,
						xtype: 'swdatefield'
					}, {
						fieldLabel: lang['lvn_zakryit'],
						hiddenName: 'EvnStick_IsClosed',
						width: 100,
						disabled: true,
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: langs('Вид ЛВН'),
						hiddenName: 'LvnType',
						hidden: getRegionNick().inlist(['kz']), // базовый кроме казахстана
						width: 175,
						xtype: 'swbaselocalcombo',
						listeners: {
							beforerender: function (combo)
							{
								combo.setValue(null);
							}
						},
						store: new Ext.data.SimpleStore({
							autoLoad: true,
							data: [
								[ null, '' ],
								[ 1, langs('На бумажном носителе') ],
								[ 2, langs('Электронный') ]
							],
							fields: [
								{ name: 'LvnValue', type: 'int' },
								{ name: 'LvnType', type: 'string' }
							],
							key: 'LvnValue',
							sortInfo: { field: 'LvnValue' }
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red"></font>&nbsp;{LvnType}',
							'</div></tpl>'
						),
						valueField: 'LvnValue',
						displayField: 'LvnType'
					}]
				}, {
					bodyStyle: 'padding-right: 5px;',
					border: false,
					hidden: getRegionNick() == 'kz',
					labelWidth: 110,
					layout: 'form',
					items: [{
						fieldLabel: 'Врач 1',
						hiddenName: 'MedPersonal1_id',
						listWidth: 400,
						allowBlank: true,
						width: 175,
						xtype: 'swmedpersonalcombo'
					}, {
						fieldLabel: 'Врач 2',
						hiddenName: 'MedPersonal2_id',
						listWidth: 400,
						allowBlank: true,
						width: 175,
						xtype: 'swmedpersonalcombo'
					}, {
						fieldLabel: 'Врач 3',
						hiddenName: 'MedPersonal3_id',
						allowBlank: true,
						listWidth: 400,
						width: 175,
						xtype: 'swmedpersonalcombo'
					}, {
						fieldLabel: 'Нуждается в ЭП',
						hiddenName: 'EvnStick_IsNeedSign',
						width: 100,
						xtype: 'swyesnocombo'
					}, {
						fieldLabel: 'Не включен в реестр с типом',
						loadParams: {params: {where: getRegionNick() == 'astra' ? '' : ' where RegistryESType_id <> 2'}},
						comboSubject: 'RegistryESType',
						hiddenName: 'RegistryESType_id',
						width: 175,
						xtype: 'swcommonsprcombo'
					}]
				}, {
					border: false,
					layout: 'form',
					style: 'padding-left: 5px',
					hidden: !(getGlobalOptions().archive_database_enable),
					labelWidth: 0,
					items: [{
						allowBlank: true,
						name: 'autoLoadArchiveRecords',
						boxLabel: lang['uchityivat_arhivnyie_dannyie'],
						hideLabel: true,
						xtype: 'checkbox'
					}]
				}]
			}], keys: [{
                fn: function() {
                    win.loadGridWithFilter();
                },
                key: Ext.EventObject.ENTER,
                stopEvent: true
            }]
		});
		
		var base_form = this.FilterPanel.getForm();
		
		// TODO: 2. запретить редактирование и удаление ЛВН из формы поиска
		this.SearchGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', disabled: true, handler: Ext.emptyFn, hidden: true},
				{ name: 'action_edit', handler: function() { this.openEvnStickEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openEvnStickEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteEvnStick(); }.createDelegate(this), hidden: true, disabled: true },
                { name: 'action_print',
                    menuConfig: {
                        printObjectStick: { text: lang['pechat_lvn_spravki'], handler: function() { this.CheckWorkRelease(); }.createDelegate(this) }
                    }
                }
			],
			autoLoadData: false,
			dataUrl: '/?c=Stick&m=loadEvnStickSearchGrid',
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
				if ( record.get('accessType') == 'edit' && win.action !== 'view') {
					var disabled = false;
					if (getGlobalOptions().archive_database_enable) {
						disabled = disabled || (record.get('archiveRecord') == 1);
					}

					this.ViewActions.action_edit.setDisabled(disabled);
				}
				else {
					this.ViewActions.action_edit.setDisabled(true);
				}
			},
			paging: true,
			pageSize: 100,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'EvnStickBase_id', type: 'int', header: 'ID', key: true },
				{ name: 'accessType', type: 'string', hidden: true },
				{ name: 'evnStickType', type: 'int', hidden: true },
				{ name: 'parentClass', type: 'string', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Person_pid', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnStick_mid', type: 'int', hidden: true },
				{ name: 'EvnStick_pid', type: 'int', hidden: true },
				{ name: 'StickCause_Code', type: 'string', hidden: true },
				{ name: 'StickOrder_Code', type: 'string', header: lang['poryadok_vyidachi'], hidden: true },
				{ name: 'EvnStickClass_Name', type: 'string', header: lang['dokument_tip_zanyatosti'], width: 120 },
				{ name: 'EvnStickBase_Ser', type: 'string', header: lang['seriya'], width: 120 },
				{ name: 'EvnStickBase_Num', type: 'string', header: lang['nomer'], width: 120 },
				{ name: 'Lpu_Name', type: 'string', header: lang['lpu'], width: 200 },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 80 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 80 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 80 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'] },
				{ name: 'OrgJob_Name', type: 'string', header: lang['mesto_rabotyi'], width: 200 },
				{ name: 'Post_Name', type: 'string', header: lang['vyipolnyaemaya_rabota'], width: 200 },
				{ name: 'MedPersonalFirst_Fio', type: 'string', header: lang['vrach_vyidavshiy_lvn'], width: 200 },
				{ name: 'MedPersonalLast_Fio', type: 'string', header: lang['vrach_zakonchivshiy_lvn'], width: 200 },
				{ name: 'EvnStickWorkRelease_begDate', type: 'date', format: 'd.m.Y', header: lang['osvobojdenie_ot_rabotyi_s_kakogo_chisla'] },
				{ name: 'EvnStickWorkRelease_endDate', type: 'date', format: 'd.m.Y', header: lang['osvobojdenie_ot_rabotyi_po_kakoe_chislo'] },
				{ name: 'EvnStickWorkRelease_DaysCount', type: 'int', header: lang['chislo_kalendarnyih_dney_osvobojdeniya_ot_rabotyi'], width: 100 },/*,
				{ name: 'DirectLpu_Name', type: 'string', header: lang['napravlen_v_drugoe_lpu_naimenovanie_lpu'], width: 200 }*/
				{ name: 'CardType', type: 'string', header: lang['tap_kvs'], width: 100 },				
				{ name: 'NumCard', type: 'string', header: lang['nomer_tap_kvs'], width: 100 },
				{ name: 'EvnStatus_Name', type: 'string', header: lang['tip_lvn'], width: 100 },
				{ name: 'StickFSSType_Name', type: 'string', header: 'Состояние ЛВН в ФСС', width: 100 },
				{ name: 'RegistryESError_Descr', type: 'string', header: 'Наименование ошибки', width: 200 },
				{ name: 'EvnStick_IsDelQueue', type: 'int', hidden: true }
			],
			title: lang['lvn_spisok'],
			totalProperty: 'totalCount'
		});

		this.SearchGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('EvnStick_IsDelQueue') == 2) {
					cls = cls + 'x-grid-rowbackgray ';
				}
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});
		
		this.CancelButton = new Ext.Button({
			handler: function()  {
				this.hide()
			}.createDelegate(this),
			id: 'ESVW_CancelButton',
			onTabAction: function()
			{
				base_form.findField('StickType_id').focus();
			},
			iconCls: 'close16',
			text: BTN_FRMCLOSE
		});
		
		this.SearchGrid.focusPrev = this.CancelButton;
		this.SearchGrid.focusPrev.type = 'button';
		this.SearchGrid.focusPrev.name = this.SearchGrid.focusPrev.id;
		this.SearchGrid.focusOn = this.CancelButton;
		this.SearchGrid.focusOn.type = 'button';
		this.SearchGrid.focusOn.name = this.SearchGrid.focusOn.id;
		
		// TODO: Доп кнопки
		Ext.apply(this, {
			buttons: [
			{
			    text: BTN_FRMSEARCH,
			    handler: function(){
				this.loadGridWithFilter();
			    }.createDelegate(this),
			    iconCls: 'search16'
			},
			{
			    text: lang['sbros'],
			    handler: function(){
				this.loadGridWithFilter(true);
			    }.createDelegate(this),
			    iconCls: 'resetsearch16'
			},
			{
				text: '-'
			},
			HelpButton(this),
			this.CancelButton],
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

		sw.Promed.swEvnStickViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var win = Ext.getCmp('EvnStickViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					win.openEvnStickEditWindow('add');
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

		this.SearchGrid.getGrid().getStore().removeAll();

		
		if ( clear ) {
			base_form.reset();
			this.SearchGrid.getAction('action_refresh').setDisabled(true);
			this.SearchGrid.gFilters = null;
		}
		else {
			var params = base_form.getValues(), emptys = true;
			
			for (var key in params) {
				if (params[key] && key!='SearchType_id') {
					emptys = false;
				}
			};
			if ( /*this.FilterPanel.isEmpty()*/ emptys ) {
				
				sw.swMsg.alert(lang['oshibka'], lang['doljen_byit_zapolnen_hotya_byi_odin_iz_filtrov_krome_rejima_poiska'], function() {
					base_form.findField('StickType_id').focus();
				});
				return false;
			}
			
			params.limit = 100;
			params.start = 0;
			if (!Ext.isEmpty(params.autoLoadArchiveRecords)) {
				this.SearchGrid.showArchive = true;
			} else {
				this.SearchGrid.showArchive = false;
			}
			this.SearchGrid.ViewActions.action_refresh.setDisabled(false);
			this.SearchGrid.getGrid().getStore().removeAll();
            params.CurLpuSection_id = this.CurLpuSection_id;
            params.CurLpuUnit_id = this.CurLpuUnit_id;
            params.CurLpuBuilding_id = this.CurLpuBuilding_id;
            this.SearchGrid.getGrid().getStore().baseParams = params;
			this.SearchGrid.getGrid().getStore().load({
				params: params
			});
		}
	},
	maximizable: true,
	maximized: true,
	modal: false,
	plain: true,
	openEvnStickEditWindow: function(action) {
		if ( action != 'edit' && action != 'view' ) {
			return false;
		}

		var base_form = this.FilterPanel.getForm();
		var grid = this.SearchGrid.getGrid();

		var selected_record = grid.getSelectionModel().getSelected();

		if ( !selected_record || !selected_record.get('EvnStickBase_id') ) {
			return false;
		}

		if ( (selected_record.get('evnStickType') == 1 || selected_record.get('evnStickType') == 2) && getWnd('swEvnStickEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_vyipiski_lista_netrudosposobnosti_uje_otkryito']);
			return false;
		}
		else if ( selected_record.get('evnStickType') == 3 && getWnd('swEvnStickStudentEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_vyipiski_spravki_uchaschegosya_uje_otkryito']);
			return false;
		}

		var formParams = new Object();
		var params = new Object();

		if ( selected_record.get('accessType') != 'edit' ) {
			action = 'view';
		}

		formParams.EvnStick_mid = selected_record.get('EvnStick_mid');
		formParams.EvnStick_pid = selected_record.get('EvnStick_pid');
		
		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnStickData ) {
				return false;
			}
			// это никто не проверял, в результате сюда приходят вообще не те данные.
			// поэтому релоад нам поможет, хотя это и плохо 
			// TODO: Как только доделают ЛВН надо будет возвращать сюда правильные данные (одинаково называющиеся)
			
			grid.getStore().reload();
			
			/*
			var record = grid.getStore().getById(data.evnStickData[0].EvnStick_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnStickBase_id') ) {
					grid.getStore().removeAll();
				}
				
				grid.getStore().loadData(data.evnStickData[0], true);
			}
			else {
				var evn_stick_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					evn_stick_fields.push(key);
				});

				for ( i = 0; i < evn_stick_fields.length; i++ ) {
					record.set(evn_stick_fields[i], data.evnStickData[0][evn_stick_fields[i]]);
				}

				record.commit();
				
			}
			*/
		}.createDelegate(this);
		params.evnStickType = selected_record.get('evnStickType');
		params.onHide = function() {
			/*var index_record = grid.getStore().indexOf(selected_record);

			if (!Ext.isEmpty(index_record)){
				grid.getView().focusRow(index_record);
			}*/
		}.createDelegate(this);
		params.parentClass = selected_record.get('parentClass');
		params.Person_Birthday = selected_record.get('Person_Birthday');
		params.Person_Firname = selected_record.get('Person_Firname');
		params.Person_id = selected_record.get('Person_pid');
		params.Person_Secname = selected_record.get('Person_Secname');
		params.Person_Surname = selected_record.get('Person_Surname');

		formParams.EvnStick_id = selected_record.get('EvnStickBase_id');
		formParams.Person_id = selected_record.get('Person_id');
		formParams.Server_id = selected_record.get('Server_id');
        formParams.StickReg = this.StickReg;
        formParams.CurLpuSection_id = this.CurLpuSection_id;
        formParams.CurLpuUnit_id = this.CurLpuUnit_id;
        formParams.CurLpuBuilding_id = this.CurLpuBuilding_id;
		params.formParams = formParams;

		if (getGlobalOptions().archive_database_enable) {
			params.archiveRecord = selected_record.get('archiveRecord');
		}

		switch ( selected_record.get('evnStickType') ) {
			case 1:
			case 2:
				getWnd('swEvnStickEditWindow').show(params);
			break;

			case 3:
				getWnd('swEvnStickStudentEditWindow').show(params);
			break;

			default:
				return false;
			break;
		}
	},

	CheckWorkRelease: function(){ //https://redmine.swan.perm.ru/issues/83780
		var that = this;
		var grid = this.SearchGrid.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnStickBase_id') ) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		var url = '';
		var EvnStickBase_id = record.get('EvnStickBase_id');
		var evnStickType = record.get('evnStickType');
		if(evnStickType == 3)
		{
			if((getRegionNick()=='kz'))
				printBirt({
					'Report_FileName': 'f095u.rptdesign',
					'Report_Params': '&paramEvnStickStudent=' + EvnStickBase_id,
					'Report_Format': 'pdf'
				});
			else
			{
				url = '/?c=Stick&m=printEvnStickStudent&EvnStickStudent_id=' + EvnStickBase_id;
				window.open(url, '_blank');
			}
		}
		else
		{
			if(getRegionNick() == 'ekb'){
				Ext.Ajax.request({
					url: '/?c=Stick&m=WorkReleaseMedStaffFactCheck',
					params: {
						EvnStickBase_id: EvnStickBase_id
					},
					callback: function(opt, success, response) {
						if (success && response.responseText.length > 0) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (!Ext.isEmpty(result[0]) && !Ext.isEmpty(result[0].EvnStickWorkRelease_id)) {
								var b_date = result[0].evnStickWorkRelease_begDT;
								var e_date = result[0].evnStickWorkRelease_endDT;
								var msg = lang['Prodlenie_i_ili_vydacha_lvn_osushchestvlena_cherez_VK_ukazhite_vracha_№2_v_osvobozhdenie_ot_raboty_ot'] + b_date + " " + lang['do'] + " " + e_date;
								sw.swMsg.show(
									{
										buttons: Ext.Msg.OK,
										fn: function()
										{
											return 1;
										},
										icon: Ext.Msg.WARNING,
										msg: msg,
										title: lang['oshibka']
									});
							}
							else
							{
								that.printEvnStick(EvnStickBase_id,evnStickType);
							}
						}
						else
						{
							that.printEvnStick(EvnStickBase_id,evnStickType);
						}
					}
				});
			}
			else
				that.printEvnStick(EvnStickBase_id,evnStickType);
		}
	},

	printEvnStick: function(EvnStickBase_id,evnStickType) {
		switch ( evnStickType ) {
			case 1:
			case 2:
				// открыть форму печати.
				var params = new Object();
				params.EvnStick_id = EvnStickBase_id;
				params.evnStickType = evnStickType;
				getWnd('swEvnStickPrintWindow').show(params);
			break;
		}
	},
	resizable: false,
	show: function() {
		sw.Promed.swEvnStickViewWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();

		this.center();
		this.maximize();
		this.loadGridWithFilter(true);

        this.CurLpuSection_id = 0;
        this.CurLpuUnit_id = 0;
        this.CurLpuBuilding_id = 0;
        this.StickReg = 0;
        this.viewOnly = false;
        if(arguments[0])
        {
            if(arguments[0].CurLpuSection_id)
                this.CurLpuSection_id = arguments[0].CurLpuSection_id;
            if(arguments[0].CurLpuUnit_id)
                this.CurLpuUnit_id = arguments[0].CurLpuUnit_id;
            if(arguments[0].CurLpuBuilding_id)
                this.CurLpuBuilding_id = arguments[0].CurLpuBuilding_id;
            if(arguments[0].StickReg)
                this.StickReg = arguments[0].StickReg;
            if(arguments[0].viewOnly)
                this.viewOnly = arguments[0].viewOnly;
        }
		//проверка возможностb редактирваоть ЛВН
		checkEvnStickEditable('',this);
		if (!Ext.isEmpty(this.evnStickAction)) {
			this.action = this.evnStickAction;
		} else if (this.viewOnly === true) {
			this.action = 'view';
		} else {
			this.action = 'edit';
		}
		var base_form = this.FilterPanel.getForm();
		if( typeof(swMedStaffFactGlobalStore) !== 'undefined'){
			setMedStaffFactGlobalStoreFilter();		
			base_form.findField('MedPersonal1_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
			base_form.findField('MedPersonal2_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
			base_form.findField('MedPersonal3_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		}

		this.getLoadMask().hide();
	},
	title: lang['lvn_poisk'], 
	width: 800
});