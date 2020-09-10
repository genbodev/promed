/**
* swTreatmentStreamInputWindow - окно поточного ввода журнала обращений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      4.08.2010
* @comment      Префикс для id компонентов ETSIF (EvnTreatmentStreamInputForm). TABINDEX_ETSIF
*/

sw.Promed.swTreatmentStreamInputWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	maximized: true,
	modal: true,
	plain: true,
	pmUser_Name: null,
	resizable: false,
	minHeight: 550,
	minWidth: 800,
	width : 800,
	height : 550,
	border : false,
	layout: 'border',
	//maximizable: true,
	id: 'EvnTreatmentStreamInputWindow',
	title: lang['registratsiya_obrascheniy_potochnyiy_vvod'],
	initComponent: function() {
		Ext.apply(this, {
			buttonAlign : "right",
			buttons : [{
				text : lang['zakryit'],
				iconCls: 'close16',
				tabIndex: TABINDEX_ETSIF + 3,
				handler : function(button, event) {
					button.ownerCt.hide();
				}
			}],
			items : [
			{
				layout: 'form',
				border: false,
				region: 'north',
				autoHeight: true,
				labelAlign: 'right',
				labelWidth: 120,
				items: [ 
					new Ext.form.FormPanel({
						frame: false,
						border: false,
						bodyStyle: 'padding: 5px',
						labelAlign: 'right',
						labelWidth: 120,
						id: 'ETSIF_StreamInformationForm',
						items: [{
							disabled: true,
							fieldLabel: lang['polzovatel'],
							id: 'ETSIF_pmUser_Name',
							width: 380,
							xtype: 'textfield'
						}, {
							disabled: true,
							fieldLabel: lang['data_nachala_vvoda'],
							id: 'ETSIF_Stream_begDateTime',
							width: 180,
							xtype: 'textfield'
						}]
					}),
					new Ext.form.FormPanel({
						animCollapse: false,
						autoHeight: true,
						bodyStyle: 'padding: 5px 5px 0',
						border: false,
						buttonAlign: 'left',
						frame: false,
						id: 'EvnTreatmentStreamInputForm',
						labelAlign: 'right',
						labelWidth: 165,
						title: lang['parametryi_vvoda'],
						items: [{
							autoHeight: true,
							style: 'padding: 0px;',
							title: lang['registratsiya'],
							width: 780,
							xtype: 'fieldset',
							items: [{
								fieldLabel: lang['data_registratsii'],
								format: 'd.m.Y',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								id: 'ETSIF_Treatment_setDateReg',
								name: 'Treatment_setDateReg',
								tabIndex: TABINDEX_ETSIF + 1,
								width: 100,
								xtype: 'swdatefield',
								listeners: {
									'keydown': function (f,e){
										if (e.getKey() == e.ENTER) Ext.getCmp('EvnTreatmentStreamInputWindow').loadGridWithFilter();
									}
								}
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0px;',
							title: lang['obraschenie'],
							width: 780,
							xtype: 'fieldset',
							items: [{
								fieldLabel: lang['tip_obrascheniya'],
								allowBlank: true,
								disabled: false,
								comboSubject: 'TreatmentType',
								tabIndex: TABINDEX_ETSIF + 2,
								width: 180,
								value: 4, //default value
								idPrefix: 'ETSIF_',
								xtype: 'swtreatmentcombo',
								listeners: {
									'keydown': function (f,e){
										if (e.getKey() == e.ENTER) Ext.getCmp('EvnTreatmentStreamInputWindow').loadGridWithFilter();
									}
								}
							}]
						}]
					})
				]
			},
				new sw.Promed.ViewFrame({
					actions: [
						{ name: 'action_add', handler: function() { this.openTreatmentEditWindow('add'); }.createDelegate(this) },
						{ name: 'action_edit', handler: function() { this.openTreatmentEditWindow('edit'); }.createDelegate(this) },
						{ name: 'action_view', handler: function() { this.openTreatmentEditWindow('view'); }.createDelegate(this) },
						{ name: 'action_delete' },
						{ name: 'action_refresh', hidden: true, disabled: true },
                        { name: 'action_print',
                            menuConfig: {
                                printObjectReg: { text: lang['pechat_obrascheniya'], handler: function() { this.print(); }.createDelegate(this) }
                            }
                        }
					],
					autoExpandColumn: 'autoexpand_dir',
					autoExpandMin: 200,
					autoLoadData: false,
					clearSelectionsOnTab: false,
					id: 'ETSIF_TreatmentGrid',
					object: 'Treatment',
					editformclassname: 'swTreatmentEditWindow',
					name: 'TreatmentGrid',
					dataUrl: '/?c=Treatment&m=getTreatmentList',
					focusOn: {
						name: 'TreatmentGrid',
						type: 'grid'
					},
					focusPrev: {
						name: 'TreatmentType',
						type: 'field'
					},
					minHeight: 500,
					pageSize: 100,
					paging: true,
					region: 'center',
					root: 'data',
					stringfields: [
						//{ name: 'Server_id', type: 'int', hidden: true },
						{ name: 'Treatment_id', type: 'int', hidden: true, key: true },
						{ name: 'PMUser', type: 'string', header: lang['sozdatel'], width: 120 },
						{ name: 'Treatment_Reg', type: 'string', header: lang['nomer_registratsii'], width: 120 },
						{ name: 'Treatment_DateReg', type: 'date', format: 'd.m.Y', header: lang['data_registratsii'], width: 120 },
						{ name: 'TreatmentType', type: 'string', header: lang['tip_obrascheniya'], width: 150 },
						{ name: 'TreatmentSenderType', type: 'string', header: lang['tip_initsiatora_obrascheniya'], width: 150 },
						{ name: 'Treatment_SenderDetails', type: 'string', header: lang['initsiator'], id: 'autoexpand_dir' },
						{ name: 'TreatmentRecipientType', type: 'string', header: lang['adresat_obrascheniya'], width: 150 }
					],
					toolbar: true ,
					totalProperty: 'totalCount'
				})
			]
		});
		sw.Promed.swTreatmentStreamInputWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [
		{
			fn: function(inp, e) {
				Ext.getCmp('EvnTreatmentStreamInputWindow').openTreatmentEditWindow('add');
			},
			key: [
				Ext.EventObject.INSERT
			],
			stopEvent: true
		}
	],
	show: function() {
		sw.Promed.swTreatmentStreamInputWindow.superclass.show.apply(this, arguments);
		this.center();
		//this.maximize();
		// Заполнение полей "Пользователь", "Дата начала ввода", "Дата регистрации"
		this.setBegDateTime();
		this.loadCombo('ETSIF_TreatmentType_id', 4);
		this.findById('EvnTreatmentStreamInputForm').findById('ETSIF_Treatment_setDateReg').focus(100, true);
		Ext.getCmp('ETSIF_TreatmentGrid').removeAll();
	},
	setBegDateTime: function() {
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					this.begDate = response_obj.begDate;
					this.begTime = response_obj.begTime;

					this.findById('ETSIF_StreamInformationForm').findById('ETSIF_pmUser_Name').setValue(response_obj.pmUser_Name);
					this.findById('ETSIF_StreamInformationForm').findById('ETSIF_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
					this.findById('EvnTreatmentStreamInputForm').findById('ETSIF_Treatment_setDateReg').setValue(response_obj.begDate);
					this.findById('EvnTreatmentStreamInputForm').findById('ETSIF_Treatment_setDateReg').setMaxValue( response_obj.begDate )
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},/**/
	loadCombo: function(id_combo, value) {
		var combo = this.findById('EvnTreatmentStreamInputForm').findById(id_combo);
		//combo.clearValue();
		//combo.getStore().removeAll();
		combo.getStore().baseParams.Object = combo.comboSubject;
		switch ( combo.comboSubject ) {
			case 'TreatmentCat':
				combo.getStore().baseParams.TreatmentCat_id = value;
				combo.getStore().baseParams.TreatmentCat_Name = '';
				break;
			case 'TreatmentMethodDispatch':
				combo.getStore().baseParams.TreatmentMethodDispatch_id = value;
				combo.getStore().baseParams.TreatmentMethodDispatch_Name = '';
				break;
			case 'TreatmentRecipientType':
				combo.getStore().baseParams.TreatmentRecipientType_id = value;
				combo.getStore().baseParams.TreatmentRecipientType_Name = '';
				break;
			case 'TreatmentType':
				combo.getStore().baseParams.TreatmentType_id = value;
				combo.getStore().baseParams.TreatmentType_Name = '';
				break;
			case 'TreatmentMultiplicity':
				combo.getStore().baseParams.TreatmentMultiplicity_id = value;
				combo.getStore().baseParams.TreatmentMultiplicity_Name = '';
				break;
			case 'TreatmentReview':
				combo.getStore().baseParams.TreatmentReview_id = value;
				combo.getStore().baseParams.TreatmentReview_Name = '';
				break;
			case 'TreatmentSenderType':
				combo.getStore().baseParams.TreatmentSenderType_id = value;
				combo.getStore().baseParams.TreatmentSenderType_Name = '';
				break;
			case 'TreatmentSubjectType':
				combo.getStore().baseParams.TreatmentSubjectType_id = value;
				combo.getStore().baseParams.TreatmentSubjectType_Name = '';
				break;
		}
		combo.getStore().load({
			callback: function() {
				combo.setValue(value);
			}
		});
	},
	openTreatmentEditWindow: function(action) {
		if ( !action || !action.inlist(['add','edit','view']) ) {
			return false;
		}

		if ( getWnd('swTreatmentEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_obrascheniya_uje_otkryito']);
			return false;
		}

		var base_form = this.findById('EvnTreatmentStreamInputForm').getForm();
		var grid = this.findById('ETSIF_TreatmentGrid').getGrid();
		var params = new Object();
		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.TEW_Data )
			{
				return false;
			}
			var record = grid.getStore().getById(data.TEW_Data.Treatment_id);
			if ( !record )
			{
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('Treatment_id') ) {
					grid.getStore().removeAll();
				}/* */
				grid.getStore().loadData({ 'data': [ data.TEW_Data ]}, true);
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}
			else
			{
				var treatment_fields = new Array();
				grid.getStore().fields.eachKey(function(key, item) {
					treatment_fields.push(key);
				});
				for ( i = 0; i < treatment_fields.length; i++ ) {
					record.set(treatment_fields[i], data.TEW_Data[treatment_fields[i]]);
				}
				record.commit(); 
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(grid.getStore().indexOf(record));
					grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
				}
			}
		}.createDelegate(this);
		if ( action == 'add' ) {
			params.Treatment_setDateReg = base_form.findField('ETSIF_Treatment_setDateReg').getValue();
			params.TreatmentType_id = base_form.findField('ETSIF_TreatmentType_id').getValue();
			getWnd('swTreatmentEditWindow').show(params);
		}
		else {
			if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Treatment_id') ) {
				return false;
			}
			var record = grid.getSelectionModel().getSelected();
			params.Treatment_setDateReg = record.get('Treatment_DateReg');
			params.Treatment_id = record.get('Treatment_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
			};
			getWnd('swTreatmentEditWindow').show(params);
		}
	},
	loadGridWithFilter: function(clear) {
		var grid = Ext.getCmp('ETSIF_TreatmentGrid');
		if (clear) {
			//form.clearFilters();
			grid.removeAll();
			grid.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					Treatment_DateReg: '',
					TreatmentType_id: 0
				}
			});
		} else {
			var treatment_date_reg = this.findById('ETSIF_Treatment_setDateReg').getValue().format('Y.m.d') || '';
			var treatment_type_id = this.findById('ETSIF_TreatmentType_id').getValue() || 0;
			grid.removeAll();
			grid.loadData({
				globalFilters: {
					limit: 100,
					start: 0,
					Treatment_DateReg: treatment_date_reg,
					TreatmentType_id: treatment_type_id
				}
			});
		}
	},
	print: function() {
		if ( !this.findById('ETSIF_TreatmentGrid').getGrid().getSelectionModel().getSelected().get('Treatment_id') )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('ETSIF_TreatmentGrid').getGrid().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['ne_vyibrano_obraschenie'],
				title: lang['registratsiya_obrascheniy_pechat']
			});
			return false;
		}
		var Treatment_id = this.findById('ETSIF_TreatmentGrid').getGrid().getSelectionModel().getSelected().get('Treatment_id');
		var query_string = '/?c=Treatment&m=printTreatment&Treatment_id=' + Treatment_id;
		window.open(query_string, '_blank');
	}
});