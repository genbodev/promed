/**
* Регистр пациентов с предраковым состоянием
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
*/

sw.Promed.swPreOnkoRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'Регистр пациентов с предраковым состоянием',
	modal: true,
	resizable: false,
	maximized: true,
	shim: false,
	plain: true,
	layout: 'fit',
	buttonAlign: "right",
	closeAction: 'hide',
	show: function() {
		sw.Promed.swPreOnkoRegistryWindow.superclass.show.apply(this, arguments);

		this.userMedStaffFact = arguments[0].userMedStaffFact || {};
		this.doReset();
	},
	
	doSearch: function() {
		var grid = this.Grid.getGrid(),
			form = this.SearchFilters.getForm();
			
		if( !form.isValid() ) {
			return false;
		}
		
		grid.getStore().baseParams = form.getValues();
		grid.getStore().load();
	},
	
	doReset: function() {
		this.Grid.getGrid().getStore().removeAll();
		this.SearchFilters.getForm().reset();
	},
	
	emkOpen: function()	{
		var grid = this.Grid.getGrid(),
			record = grid.getSelectionModel().getSelected(),
			readOnly = !isUserGroup('PreOnkoRegistryFull');

		if ( !record || !record.get('Person_id') ) {
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}

		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			readOnly: readOnly,
			ARMType: 'common',
			callback: function() {
				//
			}.createDelegate(this)
		});
	},
	
	openViewWindow: function() {
		if (getWnd('swMorbusOnkoWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_prosmotra_uje_otkryito']);
			return false;
		}

		var grid = this.Grid.getGrid(),
			record = grid.getSelectionModel().getSelected(),
			action = !isUserGroup('PreOnkoRegistryFull') ? 'view' : 'edit';
		
		if (!record || !record.get('MorbusOnko_id')) {
			return false;
		}

		var params = {};
		params.onHide = function(isChange) {
			if(isChange) {
				grid.getStore().reload();
			} else {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			}
		};
		params.allowSpecificEdit = ('edit' == action);
		params.Person_id = record.get('Person_id');
		params.PersonEvn_id = record.get('PersonEvn_id');
		params.Server_id = record.get('Server_id');
		params.EvnOnkoNotifyNeglected_id = record.get('EvnOnkoNotifyNeglected_id');
		params.MorbusOnkoVizitPLDop_id = record.get('MorbusOnkoVizitPLDop_id');
		params.MorbusOnkoLeave_id = record.get('MorbusOnkoLeave_id');
		params.Morbus_id = record.get('Morbus_id');
		params.MorbusOnko_pid = record.get('MorbusOnko_pid');
		params.EvnSection_id = record.get('EvnSection_id');
		params.EvnVizitPL_id = record.get('EvnVizitPL_id');
		params.EvnVizitDispDop_id = record.get('EvnVizitDispDop_id');
		params.userMedStaffFact = this.userMedStaffFact;
		params.action = action;
		params.ARMType = this.ARMType;
		getWnd('swMorbusOnkoWindow').show(params);
	},

	doRegisterOut: function() {
		var win = this,
			grid = this.Grid.getGrid(),
			record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('Morbus_id')) {
			return false;
		}

		sw.swMsg.confirm(
			'Вопрос',
			'Вы действительно хотите исключить пациента из регистра?',
			function(btn) {
				if (btn === 'yes') {
					var lm = win.getLoadMask(LOAD_WAIT);
					lm.show();
					Ext.Ajax.request({
						url: '/?c=MorbusOnkoSpecifics&m=doRegisterOut',
						params: {
							Morbus_id: record.get('Morbus_id')
						},
						callback: function(options, success, response) {
							lm.hide();
							if (success) {
								win.doSearch();
							} else {
								sw.swMsg.alert(lang['oshibka'], 'Ошибка при исключении из регистра');
							}
						}
					});
				}
			}
		);
	},
	
	initComponent: function() {
		var win = this;
		
		this.SearchFilters = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px 5px 0',
			border: true,
			frame: true,
			region: 'north',
			labelAlign: 'right',
			height: 120,
			labelWidth: 120,
			items: [{
				layout: 'column',
				border: false,
				defaults: {
					border: false,
					style: 'margin-right: 20px;'
				},
				items: [{
					layout: 'form',
					items: [{
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_SurName',
						fieldLabel: 'Фамилия'
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_FirName',
						fieldLabel: 'Имя'
					}, {
						xtype: 'textfieldpmw',
						width: 250,
						name: 'Person_SecName',
						fieldLabel: 'Отчество'
					}, {
						xtype: 'numberfield',
						allowDecimals: false,
						allowNegative: false,
						autoCreate: {tag: "input", type: "text", size: "11", maxLength: "4", autocomplete: "off"},
						width: 100,
						fieldLabel: 'Год рождения',
						name: 'Person_BirthDayYear',
					}]
				}, {
					layout: 'form',
					labelWidth: 180,
					items: [{
						xtype: 'swdatefield',
						width: 120,
						fieldLabel: 'Дата рождения',
						name: 'Person_BirthDay',
					}, {
						xtype: 'swcommonsprcombo',
						comboSubject: 'Sex',
						fieldLabel: 'Пол',
						showCodefield: false,
						width: 120
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['diagnoz_s'],
								hiddenName: 'Diag_Code_From',
								listWidth: 620,
								valueField: 'Diag_Code',
								MorbusType_SysNick: 'onko',
								width: 290,
								xtype: 'swdiagcombo'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 35,
							items: [{
								fieldLabel: lang['po'],
								hiddenName: 'Diag_Code_To',
								listWidth: 620,
								valueField: 'Diag_Code',
								MorbusType_SysNick: 'onko',
								width: 290,
								xtype: 'swdiagcombo'
							}]
						}]
					}, {
						fieldLabel: 'Дата установления диагноза',
						name: 'MorbusOnko_setDateRange',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 180,
						xtype: 'daterangefield'
					}]
				}]
			}],
			keys: [{
				fn: function(e) {
					win.doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});
		
		this.Grid = new sw.Promed.ViewFrame({
			id: this.id + 'Grid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
            root: 'data',
			border: false,
			enableColumnHide: false,
			linkedTables: '',
			actions: [
				{ name: 'action_add', text: 'Открыть ЭМК', icon: 'img/icons/open16.png', handler: this.emkOpen.createDelegate(this) },
				{ name: 'action_edit', text: 'Открыть специфику', handler: this.openViewWindow.createDelegate(this) },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', text: 'Исключить из регистра', handler: this.doRegisterOut.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'MorbusOnko_id', type: 'int', hidden: true, key: true },
				{ name: 'MorbusOnko_pid', type: 'int', hidden: true },
				{ name: 'EvnVizitDispDop_id', type: 'int', hidden: true },
				{ name: 'EvnVizitPL_id', type: 'int', hidden: true },
				{ name: 'EvnSection_id', type: 'int', hidden: true },
				{ name: 'MorbusOnkoVizitPLDop_id', type: 'int', hidden: true },
				{ name: 'MorbusOnkoLeave_id', type: 'int', hidden: true },
				{ name: 'Morbus_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Person_SurName', type: 'string', header: 'Фамилия', width: 150},
				{ name: 'Person_FirName', type: 'string', header: 'Имя', width: 150},
				{ name: 'Person_SecName', type: 'string', header: 'Отчество', width: 150},
				{ name: 'Person_BirthDay', type: 'date', header: 'Д/р', width: 100},
				{ name: 'Diag_FullName', type: 'string', header: 'Диагноз МКБ-10', width: 150, id: 'autoexpand'},
				{ name: 'MorbusOnko_setDiagDT', type: 'date', header: 'Дата установки диагноза', width: 200},
				{ name: 'Morbus_disDT', type: 'string', header: 'Дата исключения из регистра', width: 200},
			],
			onRowSelect: function(sm, rowIdx, rec) {
				win.Grid.getAction('action_delete').setDisabled(!isUserGroup('PreOnkoRegistryFull') || !rec || !!rec.get('Morbus_disDT'));
			},
			paging: true,
			pageSize: 100,
			dataUrl: '/?c=MorbusOnkoSpecifics&m=loadPreOnkoRegister',
			totalProperty: 'totalCount'
		});
		
		Ext.apply(this,	{
			layout: 'border',
			buttons: [{
				handler: this.doSearch.createDelegate(this),
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			},
			{
				handler: this.doReset.createDelegate(this),
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			},
			'-',
			HelpButton(this),
			{
				text: BTN_FRMCLOSE,
				tabIndex: -1,
				tooltip: BTN_FRMCLOSE,
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}],
			items: [
				this.SearchFilters, 
				this.Grid
			]
		});
		
		sw.Promed.swPreOnkoRegistryWindow.superclass.initComponent.apply(this, arguments);
	}
});