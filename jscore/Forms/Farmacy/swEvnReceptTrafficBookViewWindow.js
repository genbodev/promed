/**
* swEvnReceptTrafficBookViewWindow - просмотр журнала движения рецептов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-15.01.2010
* @comment      Префикс для id компонентов ERTBVW (EvnReceptTrafficBookViewWindow)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnReceptTrafficBookViewWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnReceptTrafficBookViewWindow',
	objectSrc: '/jscore/Forms/Farmacy/swEvnReceptTrafficBookViewWindow.js',
	
	buttonAlign: 'left',
	clearFilter: function() {
		var base_form = this.findById('ERTBVW_FilterForm').getForm();

		base_form.reset();

		this.EvnReceptTrafficBookGrid.removeAll();

		base_form.findField('Person_Surname').focus(true, 100);
	},
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	evnReceptReleaseRollback: function() {
		if ( !this.EvnReceptTrafficBookGrid.getGrid().getSelectionModel().getSelected() ) {
			return false;
		}

		var record = this.EvnReceptTrafficBookGrid.getGrid().getSelectionModel().getSelected();

		if ( !record.get('EvnRecept_id') || record.get('ReceptDelayType_id') != 1 ) {
			return false;
		}

		var evn_recept_id = record.get('EvnRecept_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Отмена отоваривания рецепта..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['pri_otmene_otovarivaniya_retsepta_proizoshli_oshibki']);
						}.createDelegate(this),
						params: {
							EvnRecept_id: evn_recept_id
						},
						success: function(response, options) {
							loadMask.hide();

							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										getWnd('swEvnReceptProcessWindow').show({
											EvnRecept_id: evn_recept_id,
											onHide: function() {
												this.setFilter();
											}.createDelegate(this)
										});
									}
									else {
										this.setFilter();
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: lang['otovarivanie_retsepta_uspeshno_otmeneno_jelaete_pereyti_k_otovarivaniyu_retsepta'],
								title: lang['vopros']
							});
						}.createDelegate(this),
						url: '/?c=Farmacy&m=evnReceptReleaseRollback'
					});
				}
				else {
					this.EvnReceptTrafficBookGrid.focus();
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['otmenit_otovarivanie_retsepta'],
			title: lang['vopros']
		});
	},
	height: 400,
	id: 'EvnReceptTrafficBookViewWindow',
	initComponent: function() {
		this.EvnReceptTrafficBookGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', disabled: true },
				{ name: 'action_view', disabled: true },
				{ name: 'action_delete', disabled: true },
				{ name: 'action_refresh' },
				{ name: 'action_print'}
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=Farmacy&m=loadEvnReceptTrafficBook',
			focusOn: {
				name: 'ERTBVW_CancelButton',
				type: 'button'
			},
			focusPrev: {
				name: 'ERTBVW_ClearFilterButton',
				type: 'button'
			},
			id: 'ERTBVW_EvnReceptTrafficBookGrid',
			object: 'EvnRecept',
			onDblClick: function() {
				if ( !this.EvnReceptTrafficBookGrid.getGrid().getSelectionModel().getSelected() || !this.EvnReceptTrafficBookGrid.getGrid().getSelectionModel().getSelected().get('EvnRecept_id') ) {
					return false;
				}

				var record = this.EvnReceptTrafficBookGrid.getGrid().getSelectionModel().getSelected();

				if ( record.get('ReceptDelayType_id') == 1 ) {
					this.evnReceptReleaseRollback();
				}
				else if ( record.get('ReceptDelayType_id') == 2 ) {
					getWnd('swEvnReceptProcessWindow').show({
						EvnRecept_id: this.EvnReceptTrafficBookGrid.getGrid().getSelectionModel().getSelected().get('EvnRecept_id')
					});
				}
			}.createDelegate(this),
			onLoadData: function(result) {
				//
			},
			onRowSelect: function(sm, index, record) {
				var record = sm.getSelected();

				if ( record && record.get('EvnRecept_id') && record.get('ReceptDelayType_id') == 1 ) {
					this.EvnReceptTrafficBookGrid.getAction('action_rollback').setDisabled(false);
				}
				else {
					this.EvnReceptTrafficBookGrid.getAction('action_rollback').setDisabled(true);
				}
			}.createDelegate(this),
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnRecept_id', type: 'int', header: 'ID', key: true },
				{ name: 'DocumentUcStr_Count', type: 'int', hidden: true },
				{ name: 'ReceptDelayType_id', type: 'int', hidden: true },
				{ name: 'Person_Surname', header: lang['familiya'], id: 'autoexpand',  autoExpandMin: 120 },
				{ name: 'Person_Firname', header: lang['imya'], width: 120 },
				{ name: 'Person_Secname', header: lang['otchestvo'], width: 120 },
				{ name: 'Person_Birthday', header: lang['data_rojdeniya'], type: 'date', width: 90 },
				{ name: 'EvnRecept_Ser', header: lang['seriya'], width: 80 },
				{ name: 'EvnRecept_Num', header: lang['nomer'], width: 80 },
				{ name: 'EvnRecept_setDate', header: lang['data_vyipiski'], type: 'date', width: 90 },
				{ name: 'Lpu_Name', header: lang['lpu'], width: 150 },
				{ name: 'MedPersonal_Fio', header: lang['vrach'], width: 150 },
				{ name: 'EvnRecept_obrDate', header: lang['data_obrascheniya'], type: 'date', width: 90 },
				{ name: 'EvnRecept_otpDate', header: lang['data_otpuska'], type: 'date', width: 90 },
				{ name: 'Drug_Name', header: lang['medikament'], width: 150 },
				{ name: 'DocumentUcStr_SumNdsR', header: lang['summa'], align: 'right', type: 'money', width: 80 },
				{ name: 'ReceptDelayType_Name', header: lang['status'], width: 100 }
			],
			toolbar: true,
			totalProperty: 'totalCount'
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'ERTBVW_CancelButton',
				onTabAction: function() {
					var base_form = this.findById('ERTBVW_FilterForm').getForm();
					base_form.findField('EvnRecept_setDate').focus(true);
				}.createDelegate(this),
				tabIndex: 4912,
				text: lang['zakryit']
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyStyle: 'padding: 5px 5px 0;',
				border: false,
				buttonAlign: 'left',
				buttons: [{
					handler: function() {
						this.setFilter();
					}.createDelegate(this),
					tabIndex: 4910,
					text: lang['pokazat'],
					tooltip: lang['pokazat_retseptyi']
				}, {
					handler: function() {
						this.clearFilter();
					}.createDelegate(this),
					id: 'ERTBVW_ClearFilterButton',
					onShiftTabAction: function() {
						this.findById('ERTBVW_FilterForm').buttons[0].focus();
					}.createDelegate(this),
					tabIndex: 4911,
					text: lang['ochistit_filtr'],
					tooltip: lang['ochistit_filtr_alt_+_ch']
				}],
				collapsible: false,
				id: 'ERTBVW_FilterForm',
				labelAlign: 'right',
				labelWidth: 150,
				region: 'north',
				// title: 'Параметры',
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: lang['familiya'],
							name: 'Person_Surname',
							tabIndex: 4901,
							width: 200,
							xtype: 'textfieldpmw'
						}, {
							fieldLabel: lang['imya'],
							name: 'Person_Firname',
							tabIndex: 4902,
							width: 200,
							xtype: 'textfieldpmw'
						}, {
							fieldLabel: lang['otchestvo'],
							name: 'Person_Secname',
							tabIndex: 4903,
							width: 200,
							xtype: 'textfieldpmw'
						}, {
							fieldLabel: lang['data_rojdeniya'],
							name: 'Person_Birthday',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							tabIndex: 4904,
							width: 200,
							xtype: 'daterangefield'
						}]
					}, {
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: lang['data_vyipiski'],
							name: 'EvnRecept_setDate',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							tabIndex: 4905,
							width: 200,
							xtype: 'daterangefield'
						}, {
							fieldLabel: lang['data_obrascheniya'],
							name: 'EvnRecept_obrDate',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							tabIndex: 4906,
							width: 200,
							xtype: 'daterangefield'
						}, {
							fieldLabel: lang['data_otpuska'],
							name: 'EvnRecept_otpDate',
							plugins: [
								new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
							],
							tabIndex: 4907,
							width: 200,
							xtype: 'daterangefield'
						}, {
							fieldLabel: lang['status_retsepta'],
							name: 'ReceptDelayType_id',
							hiddenName: 'ReceptDelayType_id',
							tabIndex: 4908,
							width: 200,
							xtype: 'swreceptdelaytypecombo'
						}]
					}]
				}]
			}),
				this.EvnReceptTrafficBookGrid
			]
		});
		sw.Promed.swEvnReceptTrafficBookViewWindow.superclass.initComponent.apply(this, arguments);

		this.EvnReceptTrafficBookGrid.addListenersFocusOnFields();
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnReceptTrafficBookViewWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.P:
					current_window.hide();
				break;

				case Ext.EventObject.X:
					current_window.clearFilter();
				break;
			}
		},
		key: [
			Ext.EventObject.P,
			Ext.EventObject.X
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.EvnReceptTrafficBookGrid.removeAll();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 750,
	modal: false,
	openEvnReceptProcessWindow: Ext.emptyFn,
	plain: true,
	printEvnReceptDelayList: function() {
		Ext.ux.GridPrinter.print(this.EvnReceptTrafficBookGrid.getGrid());
	},
	resizable: true,
	setFilter: function() {
		var params = getAllFormFieldValues(this.findById('ERTBVW_FilterForm'));

		params.limit = 100;
		params.start = 0;

		this.EvnReceptTrafficBookGrid.getAction('action_rollback').setDisabled(true);

		this.EvnReceptTrafficBookGrid.loadData({
			globalFilters: params
		});
	},
	show: function() {
		sw.Promed.swEvnReceptTrafficBookViewWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('ERTBVW_FilterForm').getForm();

		if ( this.EvnReceptTrafficBookGrid.getAction('action_rollback') == null ) {
			this.EvnReceptTrafficBookGrid.addActions({
				handler: function() {
					this.evnReceptReleaseRollback();
				}.createDelegate(this),
				// iconCls: 'copy16',
				name: 'action_rollback',
				text: lang['otmenit_otovarivanie'],
				tooltip: lang['otmenit_otovarivanie_retsepta']
			});
			this.EvnReceptTrafficBookGrid.getAction('action_rollback').setDisabled(true);
		}

		this.restore();
		this.maximize();
		this.clearFilter();
		
		if (arguments[0] && arguments[0].filters) {
			base_form.setValues(arguments[0].filters);
			this.setFilter();
		}
	},
	title: lang['jurnal_dvijeniya_retseptov'],
	width: 750
});