/**
 * swStorageEditWindow - окно редактирования/добавления склада.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			02.07.2014
 */

sw.Promed.swStorageEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	split: true,
	width: 600,
	layout: 'form',
	id: 'swStorageEditWindow',
	listeners:
	{
		hide: function()
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	doSave: function()
	{
		var base_form = this.FormPanel.getForm();
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.submit();
		return true;
	},
	submit: function()
	{
		var base_form = this.FormPanel.getForm();
		var struct_grid = this.StorageStructLevelGrid.getGrid();
		var mol_grid = this.MolGrid.getGrid();

		var params = new Object();

		struct_grid.getStore().clearFilter();
		if ( struct_grid.getStore().getCount() > 0 ) {
			var StorageStructLevelData = getStoreRecords(struct_grid.getStore(), {
				exceptionFields: [
					'StorageStructLevelType_Nick',
					'StorageStructLevelType_Name',
					'StorageStructLevel_Name'
				]
			});

			params.StorageStructLevelData = Ext.util.JSON.encode(StorageStructLevelData);

			struct_grid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		}

		mol_grid.getStore().clearFilter();
		if ( mol_grid.getStore().getCount() > 0 ) {
			var MolData = getStoreRecords(mol_grid.getStore(), {
				exceptionFields: [
					'Person_FIO'
				]
			});

			params.MolData = Ext.util.JSON.encode(MolData);

			mol_grid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		base_form.submit({
			params: params,
			failure: function(result_form, action)
			{
				loadMask.hide();
				/*if (action.result)
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}*/
			}.createDelegate(this),
			success: function(result_form, action)
			{
				loadMask.hide();
				if (action.result){
					if (action.result.Storage_id ){
						this.callback();
						this.hide();
					} else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function()
							{
								this.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}.createDelegate(this)
		});
	},
	openStorageStructLevelEditWindow: function(action) {
		if ( !action.inlist(['add','edit','view']) ) {
			return false;
		}
		var grid = this.StorageStructLevelGrid.getGrid();
		var base_form = this.FormPanel.getForm();

		if (action != 'add') {
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty('StorageStructLevel_id')) {
				return false;
			}
		}

		var params = new Object();

		params.mode = this.mode;
		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.StorageStructLevelData != 'object' ) {
				return false;
			}
			data.StorageStructLevelData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.StorageStructLevelData.StorageStructLevel_id);

			Ext.Ajax.request({
				url: '/?c=Storage&m=getRowStorageStructLevel',
				params: data.StorageStructLevelData,
				callback: function(options, success, response) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( typeof record == 'object' ) {
						if ( record.get('RecordStatus_Code') == 1 ) {
							response_obj.data.RecordStatus_Code = 2;
						}

						var grid_fields = new Array();

						grid.getStore().fields.eachKey(function(key, item) {
							grid_fields.push(key);
						});

						for ( i = 0; i < grid_fields.length; i++ ) {
							record.set(grid_fields[i], response_obj.data[grid_fields[i]]);
						}

						record.commit();
					} else {
						if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('StorageStructLevel_id') ) {
							grid.getStore().removeAll();
						}
						response_obj.data.StorageStructLevel_id = -swGenTempId(grid.getStore());

						grid.getStore().loadData([response_obj.data], true);
					}

				}
			});
		}.createDelegate(this);

		params.formParams = new Object();

		if ( action == 'add' ) {
			if (this.struct && !Ext.isEmpty(this.struct.Lpu_id)) {
				params.formParams.Lpu_id = this.struct.Lpu_id;
			}
			if (this.struct && !Ext.isEmpty(this.struct.Org_id)) {
				params.formParams.Org_id = this.struct.Org_id;
			}
			params.formParams.Storage_id = base_form.findField('Storage_id').getValue();

			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('StorageStructLevel_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swStorageStructLevelEditWindow').show(params);
	},
	openMolEditWindow: function(action) {
		if ( !action.inlist(['add','edit','view']) ) {
			return false;
		}
		var struct_grid = this.MolGrid.getGrid();
		var struct_level_grid = this.StorageStructLevelGrid.getGrid();
		var base_form = this.FormPanel.getForm();

		if (action != 'add') {
			var record = struct_grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty('Mol_id')) {
				return false;
			}
		}

		var params = new Object();

		params.mode = this.mode;
		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' ) {
				return false;
			}
			data.RecordStatus_Code = 0;

			var record = struct_grid.getStore().getById(data.Mol_id);

			if ( typeof record == 'object' ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.RecordStatus_Code = 2;
				}

				var struct_grid_fields = new Array();

				struct_grid.getStore().fields.eachKey(function(key, item) {
					struct_grid_fields.push(key);
				});

				for ( i = 0; i < struct_grid_fields.length; i++ ) {
					record.set(struct_grid_fields[i], data[struct_grid_fields[i]]);
				}

				record.commit();
			} else {
				if ( struct_grid.getStore().getCount() == 1 && !struct_grid.getStore().getAt(0).get('Mol_id') ) {
					struct_grid.getStore().removeAll();
				}
				data.Mol_id = -swGenTempId(struct_grid.getStore());

				struct_grid.getStore().loadData([data], true);
			}
		}



		params.formParams = new Object();

		if ( action == 'add' ) {
			if (this.struct && !Ext.isEmpty(this.struct.Lpu_id)) {
				params.formParams.Lpu_id = this.struct.Lpu_id;
			}
			if (this.struct && !Ext.isEmpty(this.struct.Org_id)) {
				params.formParams.Org_id = this.struct.Org_id;
			}
			params.formParams.Storage_id = base_form.findField('Storage_id').getValue();

			params.onHide = function() {
				if ( struct_grid.getStore().getCount() > 0 ) {
					struct_grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !struct_grid.getSelectionModel().getSelected() || !struct_grid.getSelectionModel().getSelected().get('Mol_id') ) {
				return false;
			}

			var record = struct_grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				struct_grid.getView().focusRow(struct_grid.getStore().indexOf(record));
			};
		}
		params.formParams.Storage_Name = base_form.findField('Storage_Name').getValue();
		params.formParams.Mol_MaxCode = this.MolGrid.getMaxCode();
		params.formParams.Storage_begDate = base_form.findField('Storage_begDate').getValue();
		params.formParams.Storage_endDate = base_form.findField('Storage_endDate').getValue();
		params.struct_level_grid = struct_level_grid;
		params.struct = this.struct;

		getWnd('swDloMolEditWindow').show(params);
	},
	deleteStorageStructLevel: function() {
		var grid = this.StorageStructLevelGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('StorageStructLevel_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {

					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								return (Number(rec.get('RecordStatus_Code')) != 3);
							});
							break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},
	deleteMol: function() {
		var grid = this.MolGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('Mol_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
							break;
						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();
							grid.getStore().filterBy(function(rec) {
								return (Number(rec.get('RecordStatus_Code')) != 3);
							});
							break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},
	show: function()
	{
		sw.Promed.swStorageEditWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var struct_grid = this.StorageStructLevelGrid.getGrid();
		var mol_grid = this.MolGrid.getGrid();
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.mode = null;
		this.struct = null;

		if (!arguments[0])
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}

		this.action = arguments[0].action;

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].mode) {
			this.mode = arguments[0].mode;
		}
		if (arguments[0].struct) {
			this.struct = arguments[0].struct;
		}

		struct_grid.getStore().removeAll();
		mol_grid.getStore().removeAll();
		base_form.reset();
		base_form.setValues(arguments[0].formParams);

        this.p_storage_combo.fullReset();
		this.p_storage_combo.getStore().baseParams.Org_id = (this.struct && !Ext.isEmpty(this.struct.Org_id)) ? this.struct.Org_id : null;
		this.p_storage_combo.getStore().baseParams.Lpu_id = (this.struct && !Ext.isEmpty(this.struct.Lpu_id)) ? this.struct.Lpu_id : null;

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		//loadMask.show();
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['sklad_dobavlenie']);
				this.enableEdit(true);

				if (this.struct && (!Ext.isEmpty(this.struct.Lpu_id) || !Ext.isEmpty(this.struct.Org_id))) {
					var addStructData = Ext.apply({}, this.struct);
					addStructData.StorageStructLevel_id = -swGenTempId(struct_grid.getStore());
					addStructData.RecordStatus_Code = 0;

					Ext.Ajax.request({
						url: '/?c=Storage&m=getRowStorageStructLevel',
						params: addStructData,
						callback: function(options, success, response) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							struct_grid.getStore().loadData([response_obj.data]);
						}
					});
				}

				var params = Ext.apply({}, this.struct);
				Ext.Ajax.request({
					url: '/?c=LpuStructure&m=getAddressByLpuStructure',
					params: params,
					callback: function(options, success, response) {
						var response_obj = Ext.util.JSON.decode(response.responseText);

						base_form.setValues(response_obj.data);

						var address = base_form.findField('Address_Address').getValue();
						base_form.findField('Address_AddressText').setValue(address);
					}
				});

				base_form.findField('Storage_IsPKU').setValue(1); // 0. Нет
				base_form.findField('TempConditionType_id').setValue(2); // Комнатная температура

				loadMask.hide();
			break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['sklad_redaktirovanie']);
					this.enableEdit(true);
					this.StorageStructLevelGrid.setReadOnly(false);
					this.MolGrid.setReadOnly(false);
				} else {
					this.setTitle(lang['sklad_prosmotr']);
					this.enableEdit(false);
					this.StorageStructLevelGrid.setReadOnly(true);
					this.MolGrid.setReadOnly(true);
				}

				var storage_id = base_form.findField('Storage_id').getValue();

				base_form.load({
					url: '/?c=Storage&m=loadStorageForm',
					params: {Storage_id: storage_id},
					success: function(result_form, action) {
						var address = base_form.findField('Address_Address').getValue();

                        loadMask.hide();
						base_form.findField('Address_AddressText').setValue(address);

                        if(action.result) {
                        	if (action.result.data && !Ext.isEmpty(action.result.data.Storage_pid)) {
                        		wnd.p_storage_combo.setValueById(action.result.data.Storage_pid);
							}
						}
					},
					failure: function ()
					{
						loadMask.hide();
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
					}
				});

				struct_grid.getStore().load({params: {Storage_id: storage_id}});
				mol_grid.getStore().load({params: {Storage_id: storage_id}});

				loadMask.hide();
			break;
		}

	},
	initComponent: function()
	{
		var wnd = this;

		this.p_storage_combo = new sw.Promed.SwCustomRemoteCombo({
			fieldLabel: langs('Подчинен складу'),
			hiddenName: 'Storage_pid',
			displayField: 'Storage_Name',
			valueField: 'Storage_id',
			editable: true,
			allowBlank: true,
			anchor: '-10',
			listWidth: 800,
			triggerAction: 'all',
			tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item" style="{[values.MedService_IsMerch > 0 ? "font-weight: bolder;" : ""]}">',
                '{Storage_Name}&nbsp;{MedService_Name}',
                '</div></tpl>'
            ),
			store: new Ext.data.Store({
				autoLoad: false,
				reader: new Ext.data.JsonReader({
					id: 'Storage_id'
				}, [
					{name: 'Storage_id', mapping: 'Storage_id'},
					{name: 'Storage_Name', mapping: 'Storage_Name'},
					{name: 'MedService_Name', mapping: 'MedService_Name'},
					{name: 'MedService_IsMerch', mapping: 'MedService_IsMerch'}
				]),
				url: '/?c=Storage&m=getStorageListByOrgLpu'
			})
		});

		this.FormPanel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'SEW_StorageForm',
			labelAlign: 'right',
			labelWidth: 120,
			items:
				[{
					name: 'Storage_id',
					xtype: 'hidden'
				},{
					name: 'Address_id',
					xtype: 'hidden'
				},{
					name: 'Address_Zip',
					xtype: 'hidden'
				},{
					name: 'KLCountry_id',
					xtype: 'hidden'
				},{
					name: 'KLRgn_id',
					xtype: 'hidden'
				},{
					name: 'KLSubRgn_id',
					xtype: 'hidden'
				},{
					name: 'KLCity_id',
					xtype: 'hidden'
				},{
					name: 'KLTown_id',
					xtype: 'hidden'
				},{
					name: 'KLStreet_id',
					xtype: 'hidden'
				},{
					name: 'Address_House',
					xtype: 'hidden'
				},{
					name: 'Address_Corpus',
					xtype: 'hidden'
				},{
					name: 'Address_Flat',
					xtype: 'hidden'
				},{
					name: 'Address_Address',
					xtype: 'hidden'
				},{
					fieldLabel: lang['nomer'],
					allowBlank: false,
					autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
					maskRe: /[0-9]/,
					xtype: 'textfield',
					name: 'Storage_Code',
                    width: 100
				},{
					fieldLabel: lang['naimenovanie'],
					allowBlank: false,
					xtype: 'textfield',
                    anchor: '-10',
					name: 'Storage_Name'
				},{
                    border: false,
                    layout: 'column',
                    //anchor: '-10',
                    items: [{
                        layout: 'form',
                        border: false,
                        //columnWidth: .50,
                        items: [{
                            fieldLabel: lang['ploschad_m^2'],
                            allowNegative: false,
                            xtype: 'numberfield',
                            name: 'Storage_Area',
							width: 100
                        }]
                    }, {
                        layout: 'form',
                        border: false,
                        labelWidth: 95,
                        //columnWidth: .50,
                        items: [{
                            fieldLabel: lang['obyem_m^3'],
                            allowNegative: false,
                            xtype: 'numberfield',
                            name: 'Storage_Vol',
							width: 100
                        }]
                    }]
                },{
					border: false,
					layout: 'column',
					items: [{
						layout: 'form',
						border: false,
						items: [{
								xtype: 'swyesnocombo',
								fieldLabel: 'ПКУ',
								hiddenName: 'Storage_IsPKU',
								width: 100
							}]
					}, {
						layout: 'form',
						border: false,
						labelWidth: 95,
						items: [{
							xtype: 'swcommonsprcombo',
							fieldLabel: 'Темп.режим',
							comboSubject: 'TempConditionType',
							hiddenName: 'TempConditionType_id',
							allowBlank: true,
							width: 230
						}]
					}]
				},{
                    anchor: '-10',
					fieldLabel: lang['priem_spisaniya'],
					allowBlank: false,
					xtype: 'swcommonsprcombo',
					comboSubject: 'StorageRecWriteType',
					width: 300,
					hiddenName: 'StorageRecWriteType_id'
				},{
                    anchor: '-10',
					fieldLabel: lang['tip_sklada'],
					xtype: 'swcommonsprcombo',
					comboSubject: 'StorageType',
					width: 300,
					hiddenName: 'StorageType_id'
				},
				new Ext.form.TwinTriggerField ({
					enableKeyEvents: true,
					fieldLabel: lang['adres'],
                    anchor: '-10',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
								if ( e.F4 == e.getKey() )
									inp.onTrigger1Click();
								if ( e.DELETE == e.getKey() && e.altKey)
									inp.onTrigger2Click();

								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.browserEvent.returnValue = false;
								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								return false;
							}
						},
						'keyup': function( inp, e ) {
							if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

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
					name: 'Address_AddressText',
					onTrigger2Click: function() {
						var base_form = this.FormPanel.getForm();
						base_form.findField('Address_Zip').setValue('');
						base_form.findField('KLCountry_id').setValue('');
						base_form.findField('KLRgn_id').setValue('');
						base_form.findField('KLSubRgn_id').setValue('');
						base_form.findField('KLCity_id').setValue('');
						base_form.findField('KLTown_id').setValue('');
						base_form.findField('KLStreet_id').setValue('');
						base_form.findField('Address_House').setValue('');
						base_form.findField('Address_Corpus').setValue('');
						base_form.findField('Address_Flat').setValue('');
						base_form.findField('Address_Address').setValue('');
						base_form.findField('Address_AddressText').setValue('');
					}.createDelegate(this),
					onTrigger1Click: function() {
						var base_form = this.FormPanel.getForm();
						getWnd('swAddressEditWindow').show({
							fields: {
								Address_ZipEdit: base_form.findField('Address_Zip').value,
								KLCountry_idEdit: base_form.findField('KLCountry_id').value,
								KLRgn_idEdit: base_form.findField('KLRgn_id').value,
								KLSubRgn_idEdit: base_form.findField('KLSubRgn_id').value,
								KLCity_idEdit: base_form.findField('KLCity_id').value,
								KLTown_idEdit: base_form.findField('KLTown_id').value,
								KLStreet_idEdit: base_form.findField('KLStreet_id').value,
								Address_HouseEdit: base_form.findField('Address_House').value,
								Address_CorpusEdit: base_form.findField('Address_Corpus').value,
								Address_FlatEdit: base_form.findField('Address_Flat').value,
								Address_AddressEdit: base_form.findField('Address_Address').value
							},
							callback: function(values) {
								base_form.findField('Address_Zip').setValue(values.Address_ZipEdit);
								base_form.findField('KLCountry_id').setValue(values.KLCountry_idEdit);
								base_form.findField('KLRgn_id').setValue(values.KLRgn_idEdit);
								base_form.findField('KLSubRgn_id').setValue(values.KLSubRgn_idEdit);
								base_form.findField('KLCity_id').setValue(values.KLCity_idEdit);
								base_form.findField('KLTown_id').setValue(values.KLTown_idEdit);
								base_form.findField('KLStreet_id').setValue(values.KLStreet_idEdit);
								base_form.findField('Address_House').setValue(values.Address_HouseEdit);
								base_form.findField('Address_Corpus').setValue(values.Address_CorpusEdit);
								base_form.findField('Address_Flat').setValue(values.Address_FlatEdit);
								base_form.findField('Address_Address').setValue(values.Address_AddressEdit);
								base_form.findField('Address_AddressText').setValue(values.Address_AddressEdit);
								base_form.findField('Address_AddressText').focus(true, 500);
							},
							onClose: function() {
								base_form.findField('Address_AddressText').focus(true, 500);
							}
						})
					}.createDelegate(this),
					readOnly: true,
					trigger1Class: 'x-form-search-trigger',
					trigger2Class: 'x-form-clear-trigger'
				}),{
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							allowBlank: false,
							fieldLabel: lang['data_otkryitiya'],
							xtype: 'swdatefield',
							width: 100,
							name: 'Storage_begDate'
						}]
					},{
						layout: 'form',
						labelWidth: 95,
						items: [{
							fieldLabel: lang['data_zakryitiya'],
							xtype: 'swdatefield',
							width: 100,
							name: 'Storage_endDate'
						}]
					}]
				},
				wnd.p_storage_combo],
			reader: new Ext.data.JsonReader(
				{
					success: function()
					{
						//
					}
				},
				[
					{name: 'Storage_id'},
					{name: 'Storage_pid'},
					{name: 'Storage_Code'},
					{name: 'Storage_Name'},
					{name: 'Storage_Area'},
					{name: 'Storage_Vol'},
					{name: 'StorageRecWriteType_id'},
					{name: 'StorageType_id'},
					{name: 'Storage_IsPKU'},
					{name: 'TempConditionType_id'},
					{name: 'Address_Address'},
					{name: 'Storage_begDate'},
					{name: 'Storage_endDate'},
					{name: 'Address_id'},
					{name: 'Address_Zip'},
					{name: 'KLCountry_id'},
					{name: 'KLRgn_id'},
					{name: 'KLSubRgn_id'},
					{name: 'KLCity_id'},
					{name: 'KLTown_id'},
					{name: 'KLStreet_id'},
					{name: 'Address_House'},
					{name: 'Address_Corpus'},
					{name: 'Address_Flat'},
					{name: 'Address_Address'}
				]),
			url: '/?c=Storage&m=saveStorage'
		});

		this.StorageStructLevelGrid = new sw.Promed.ViewFrame({
			title: lang['urovni_organizatsii'],
			id: 'SEW_StorageStructLevelGrid',
			object: 'Storage',
			dataUrl: '/?c=Storage&m=loadStorageStructLevelGrid',
			height:150,
			autoLoadData: false,
			stringfields: [
				{name: 'StorageStructLevel_id', type: 'int', header: 'ID', key: true},
				{name: 'Storage_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'Org_id', type: 'int', hidden: true},
				{name: 'OrgStruct_id', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'LpuBuilding_id', type: 'int', hidden: true},
				{name: 'LpuUnit_id', type: 'int', hidden: true},
				{name: 'LpuSection_id', type: 'int', hidden: true},
				{name: 'MedService_id', type: 'int', hidden: true},
				{name: 'StorageStructLevelType_Nick', type: 'string', hidden: true},
				{name: 'StorageStructLevelType_Name', type: 'string', header: lang['tip'], width: 140},
				{name: 'StorageStructLevel_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand'}
			],
			actions: [
				{name:'action_add', handler: function(){this.openStorageStructLevelEditWindow('add');}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openStorageStructLevelEditWindow('edit');}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openStorageStructLevelEditWindow('view');}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteStorageStructLevel();}.createDelegate(this)},
				{name:'action_refresh', hidden: true},
				{name:'action_print'}
			]
		});

		this.MolGrid = new sw.Promed.ViewFrame({
			title: lang['materialno-otvetstvennoe_litso'],
			id: 'SEW_MolGrid',
			object: 'Mol',
			dataUrl: '/?c=Storage&m=loadMolGrid',
			height:150,
			autoLoadData: false,
			stringfields: [
				{name: 'Mol_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'MedPersonal_id', type: 'int', hidden: true},
				{name: 'MedStaffFact_id', type: 'int', hidden: true},
				{name: 'Org_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'Mol_Code', type:'int', header: lang['kod'], width: 80},
				{name: 'Person_FIO', type: 'string', id: 'autoexpand', header: lang['familiya_imya_otchestvo']},
				{name: 'Mol_begDT', type: 'string', width: 100, header: lang['data_nachala']},
				{name: 'Mol_endDT', type: 'string', width: 100, header: lang['data_okonchaniya']}
			],
			actions: [
				{name:'action_add', handler: function(){this.openMolEditWindow('add');}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openMolEditWindow('edit');}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openMolEditWindow('view');}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteMol();}.createDelegate(this)},
				{name:'action_refresh', hidden: true},
				{name:'action_print'}
			],
			getMaxCode: function() {
				var max_code = 0;
				this.getGrid().getStore().each(function(record) {
					if (record.get('Mol_Code') > max_code) {
						max_code = record.get('Mol_Code');
					}
				});
				return max_code;
			}
		});

		Ext.apply(this,
		{
			buttons:
			[{
				handler: function()
				{
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_LPEEW + 17,
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel, this.StorageStructLevelGrid, this.MolGrid]
		});
		sw.Promed.swStorageEditWindow.superclass.initComponent.apply(this, arguments);
	}
});