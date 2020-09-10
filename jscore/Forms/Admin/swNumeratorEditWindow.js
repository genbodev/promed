/**
 * swNumeratorEditWindow - окно редактирования нумератора
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 */

/*NO PARSE JSON*/

sw.Promed.swNumeratorEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swNumeratorEditWindow',
	width: 750,
	height: 500,
	modal: true,
	layout: 'border',
	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	generateNumeratorNumSample: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var num = '';
		var curValue = '1';

		if (!Ext.isEmpty(base_form.findField('Numerator_Num').getValue())) {
			curValue = base_form.findField('Numerator_Num').getValue();
		}

		if (!Ext.isEmpty(base_form.findField('Numerator_NumLen').getValue())) {
			while(curValue.length < base_form.findField('Numerator_NumLen').getValue()) {
				curValue = '0' + curValue;
			}
		}

		num = base_form.findField('Numerator_Ser').getValue() + ' ' + base_form.findField('Numerator_PreNum').getValue() + curValue + base_form.findField('Numerator_PostNum').getValue();
		if (num.includes('{YY}')) {
			var yy = String((new Date()).getFullYear()).substring(2, 4);
			num = num.replace(/{YY}/g, yy);
		}

		if (num.includes('{ОКАТО}')) { //ОКАТО на русском
			win.getOKATO(function () {
				num = num.replace(/{ОКАТО}/g, win.OKATO); //ОКАТО на русском
				win.findById('NumeratorNumSample').setText('<b>'+num+'</b>', false);
			});
		}

		this.findById('NumeratorNumSample').setText('<b>'+num+'</b>', false);
	},
	getOKATO: function(callback) {
		var that = this;
		if (!that.OKATO) {
			that.getLoadMask('Загрузка ОКАТО').show();
			Ext.Ajax.request({
				url: '/?c=LpuPassport&m=getOKATO',
				callback: function(opt, success, response) {
					that.getLoadMask().hide();
					if ( success ) {
						var res = Ext.util.JSON.decode(response.responseText)[0];
						that.OKATO = res.OKATO;
						if (typeof(callback) == 'function') {
							callback();
						}
						else {
							return that.OKATO;
						}
					}
				}.createDelegate(that)
			});
		}
		else {
			if (typeof(callback) == 'function') {
				callback();
			}
			else {
				return that.OKATO;
			}
		}
	},
	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';

		var win = this;
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (win.NumeratorLinkGrid.getGrid().getStore().getCount() == 0) {
			sw.swMsg.alert(lang['oshibka'], lang['nelzya_sohranit_numerator_bez_svyazannyih_dokumentov']);
			this.formStatus = 'edit';
			return false;
		}

		if (win.NumeratorRezervGenGrid.getGrid().getStore().getCount() == 0) {
			sw.swMsg.alert(lang['oshibka'], lang['nelzya_sohranit_numerator_bez_diapazonov_generatsii']);
			this.formStatus = 'edit';
			return false;
		}

		win.getLoadMask(LOAD_WAIT_SAVE).show();

		var params = {};
		win.NumeratorLinkGrid.getGrid().getStore().clearFilter();
		params.NumeratorLinkGridData = Ext.util.JSON.encode(getStoreRecords( win.NumeratorLinkGrid.getGrid().getStore() ));
		win.NumeratorLinkGrid.getGrid().getStore().filterBy(function(record) {
			if (record.data.Record_Status != 3) { return true; } else { return false; }
		});

		win.NumeratorRezervGrid.getGrid().getStore().clearFilter();
		params.NumeratorRezervGridData = Ext.util.JSON.encode(getStoreRecords( win.NumeratorRezervGrid.getGrid().getStore() ));
		win.NumeratorRezervGrid.getGrid().getStore().filterBy(function(record) {
			if (record.data.Record_Status != 3) { return true; } else { return false; }
		});

		win.NumeratorRezervGenGrid.getGrid().getStore().clearFilter();
		params.NumeratorRezervGenGridData = Ext.util.JSON.encode(getStoreRecords( win.NumeratorRezervGenGrid.getGrid().getStore() ));
		win.NumeratorRezervGenGrid.getGrid().getStore().filterBy(function(record) {
			if (record.data.Record_Status != 3) { return true; } else { return false; }
		});

		win.LpuGrid.getGrid().getStore().clearFilter();
		params.LpuGridData = Ext.util.JSON.encode(getStoreRecords( win.LpuGrid.getGrid().getStore() ));
		win.LpuGrid.getGrid().getStore().filterBy(function(record) {
			if (record.data.Record_Status != 3) { return true; } else { return false; }
		});

		win.LpuStructureGrid.getGrid().getStore().clearFilter();
		params.LpuStructureGridData = Ext.util.JSON.encode(getStoreRecords( win.LpuStructureGrid.getGrid().getStore() ));
		win.LpuStructureGrid.getGrid().getStore().filterBy(function(record) {
			if (record.data.Record_Status != 3) { return true; } else { return false; }
		});

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				win.getLoadMask().hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				win.getLoadMask().hide();

				if (action.result) {
					if (!Ext.isEmpty(action.result.Numerator_id)) {
						base_form.findField('Numerator_id').setValue(action.result.Numerator_id);
					}
				}

				if (typeof this.callback == 'function') {
					this.callback();
				}
				this.formStatus = 'edit';

				if (options.callback) {
					options.callback();
				} else {
					this.hide();
				}
			}.createDelegate(this)
		});
	},
	openNumeratorLinkEditWindow: function(action) {
		if (this.action == 'view' && action == 'add') {
			return false;
		}
		if (this.action == 'view' && action == 'edit') {
			action = 'view';
		}

		var win = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.NumeratorLinkGrid.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();
		params.formParams.Numerator_id = base_form.findField('Numerator_id').getValue();

		if (action == 'add') {
			params.formParams.NumeratorLink_id = -swGenTempId(grid.getStore(), 'NumeratorLink_id');
			params.formParams.Record_Status = 0;
		} else {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record.get('NumeratorLink_id')) { return false; }
			params.formParams = selected_record.data;
		}

		params.formMode = 'local';

		params.callback = function(data) {
			var i;
			var grid_fields = new Array();

			grid.getStore().fields.eachKey(function(key, item) {
				grid_fields.push(key);
			});

			if ( action == 'add' )
			{
				grid.getStore().clearFilter();
				grid.getStore().loadData(data, true);
				grid.getStore().filterBy(function(record) {
					if (record.data.Record_Status != 3)
					{
						return true;
					}
				});
			}
			else {
				index = grid.getStore().findBy(function(rec) { return rec.get('NumeratorLink_id') == data[0].NumeratorLink_id; });

				if (index == -1)
				{
					return false;
				}

				var record = grid.getStore().getAt(index);
				for (i = 0; i < grid_fields.length; i++)
				{
					record.set(grid_fields[i], data[0][grid_fields[i]]);
				}

				record.commit();
			}

			return true;
		};

		getWnd('swNumeratorLinkEditWindow').show(params);
	},
	openNumeratorRezervEditWindow: function(action) {
		if (this.action == 'view' && action == 'add') {
			return false;
		}
		if (this.action == 'view' && action == 'edit') {
			action = 'view';
		}

		var win = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.NumeratorRezervGrid.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();
		params.formParams.Numerator_id = base_form.findField('Numerator_id').getValue();

		if (action == 'add') {
			params.formParams.NumeratorRezerv_id = -swGenTempId(grid.getStore(), 'NumeratorRezerv_id');
			params.formParams.Record_Status = 0;
		} else {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record.get('NumeratorRezerv_id')) { return false; }
			params.formParams = selected_record.data;
		}

		params.formParams.NumeratorRezervType_id = 1;

		params.formMode = 'local';

		params.callback = function(data) {
			var i;
			var grid_fields = new Array();

			grid.getStore().fields.eachKey(function(key, item) {
				grid_fields.push(key);
			});

			if ( action == 'add' )
			{
				grid.getStore().clearFilter();
				grid.getStore().loadData(data, true);
				grid.getStore().filterBy(function(record) {
					if (record.data.Record_Status != 3)
					{
						return true;
					}
				});
			}
			else {
				index = grid.getStore().findBy(function(rec) { return rec.get('NumeratorRezerv_id') == data[0].NumeratorRezerv_id; });

				if (index == -1)
				{
					return false;
				}

				var record = grid.getStore().getAt(index);
				for (i = 0; i < grid_fields.length; i++)
				{
					record.set(grid_fields[i], data[0][grid_fields[i]]);
				}

				record.commit();
			}

			return true;
		};

		getWnd('swNumeratorRezervEditWindow').show(params);
	},
	openNumeratorRezervGenEditWindow: function(action) {
		if (this.action == 'view' && action == 'add') {
			return false;
		}
		if (this.action == 'view' && action == 'edit') {
			action = 'view';
		}

		var win = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.NumeratorRezervGenGrid.getGrid();

		var params = new Object();
		params.action = action;
		params.formParams = new Object();
		params.formParams.Numerator_id = base_form.findField('Numerator_id').getValue();

		if (action == 'add') {
			params.formParams.NumeratorRezerv_id = -swGenTempId(grid.getStore(), 'NumeratorRezerv_id');
			params.formParams.Record_Status = 0;
		} else {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record.get('NumeratorRezerv_id')) { return false; }
			params.formParams = selected_record.data;
		}

		params.formParams.NumeratorRezervType_id = 2;

		params.formMode = 'local';

		params.callback = function(data) {
			var i;
			var grid_fields = new Array();

			grid.getStore().fields.eachKey(function(key, item) {
				grid_fields.push(key);
			});

			if ( action == 'add' )
			{
				grid.getStore().clearFilter();
				grid.getStore().loadData(data, true);
				grid.getStore().filterBy(function(record) {
					if (record.data.Record_Status != 3)
					{
						return true;
					}
				});
			}
			else {
				index = grid.getStore().findBy(function(rec) { return rec.get('NumeratorRezerv_id') == data[0].NumeratorRezerv_id; });

				if (index == -1)
				{
					return false;
				}

				var record = grid.getStore().getAt(index);
				for (i = 0; i < grid_fields.length; i++)
				{
					record.set(grid_fields[i], data[0][grid_fields[i]]);
				}

				record.commit();
			}

			return true;
		};

		getWnd('swNumeratorRezervEditWindow').show(params);
	},
	deleteNumeratorRezerv: function() {
		var win = this;
		var grid = this.NumeratorRezervGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('NumeratorRezerv_id') ) {
			return false;
		}

		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					if (!grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = grid.getSelectionModel().getSelected();
					if (selected_record.data.Record_Status == 0)
					{
						grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						grid.getStore().filterBy(function(record)
						{
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
					}

					if (grid.getStore().getCount() == 0)
					{
						grid.getTopToolbar().items.items[1].disable();
						grid.getTopToolbar().items.items[2].disable();
						grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_diapazon_rezervirovaniya'],
			title: lang['vopros']
		});
	},
	deleteNumeratorRezervGen: function() {
		var win = this;
		var grid = this.NumeratorRezervGenGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('NumeratorRezerv_id') ) {
			return false;
		}

		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					if (!grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = grid.getSelectionModel().getSelected();
					if (selected_record.data.Record_Status == 0)
					{
						grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						grid.getStore().filterBy(function(record)
						{
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
					}

					if (grid.getStore().getCount() == 0)
					{
						grid.getTopToolbar().items.items[1].disable();
						grid.getTopToolbar().items.items[2].disable();
						grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_diapazon_generatsii'],
			title: lang['vopros']
		});
	},
	deleteNumeratorLink: function() {
		var win = this;
		var grid = this.NumeratorLinkGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('NumeratorLink_id') ) {
			return false;
		}

		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					if (!grid.getSelectionModel().getSelected())
					{
						return false;
					}

					var selected_record = grid.getSelectionModel().getSelected();
					if (selected_record.data.Record_Status == 0)
					{
						grid.getStore().remove(selected_record);
					}
					else
					{
						selected_record.set('Record_Status', 3);
						selected_record.commit();
						grid.getStore().filterBy(function(record)
						{
							if (record.data.Record_Status != 3)
							{
								return true;
							}
						});
					}

					if (grid.getStore().getCount() == 0)
					{
						grid.getTopToolbar().items.items[1].disable();
						grid.getTopToolbar().items.items[2].disable();
						grid.getTopToolbar().items.items[3].disable();
					}
					else
					{
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_svyazannyiy_dokument'],
			title: lang['vopros']
		});
	},
	show: function() {
		sw.Promed.swNumeratorEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = win.FormPanel.getForm();

		// чтобы всё зарендерилось
		win.TabPanel.setActiveTab('tab_numeratorgen');
		win.TabPanel.setActiveTab('tab_numeratorrez');
		win.TabPanel.setActiveTab('tab_numeratormo');
		win.TabPanel.setActiveTab('tab_numeratordoc');
		win.TabPanel.setActiveTab('tab_numeratorinfo');

		base_form.reset();
		win.NumeratorRezervGrid.removeAll({ clearAll: true });
		win.NumeratorRezervGenGrid.removeAll({ clearAll: true });
		win.NumeratorLinkGrid.removeAll({ clearAll: true });
		win.LpuGrid.removeAll({ clearAll: true });
		win.LpuStructureGrid.removeAll({ clearAll: true });
		if (isSuperAdmin()) {
			base_form.findField('Lpu_id').showContainer();
		} else {
			base_form.findField('Lpu_id').hideContainer();
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);

			if (!Ext.isEmpty(base_form.findField('Lpu_id').getValue())) {
				win.LpuGrid.getGrid().getStore().loadData([{
					Lpu_id: getGlobalOptions().lpu_id,
					Lpu_Nick: base_form.findField('Lpu_id').getFieldValue('Lpu_Nick'),
					OrgType_Name: base_form.findField('Lpu_id').getFieldValue('OrgType_Name'),
					Record_Status: 0
				}], true);
				win.LpuGrid.getGrid().getView().refresh();
			}
		}
		base_form.findField('Lpu_id').fireEvent('change', base_form.findField('Lpu_id'), base_form.findField('Lpu_id').getValue());
		this.syncShadow();

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		win.NumeratorRezervGrid.setReadOnly(false);
		win.NumeratorRezervGenGrid.setReadOnly(false);
		win.NumeratorLinkGrid.setReadOnly(false);
		win.LpuGrid.setReadOnly(false);
		win.LpuStructureGrid.setReadOnly(false);
		win.generateNumeratorNumSample();
		win.getLoadMask(LOAD_WAIT).show();

		switch (this.action) {
			case 'add':
				win.getLoadMask().hide();
				win.enableEdit(true);
				win.setTitle(lang['numerator_dobavlenie']);
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					win.enableEdit(true);
					win.setTitle(lang['numerator_redaktirovanie']);
				} else {
					win.enableEdit(false);
					win.NumeratorRezervGrid.setReadOnly(true);
					win.NumeratorRezervGenGrid.setReadOnly(true);
					win.NumeratorLinkGrid.setReadOnly(true);
					win.LpuGrid.setReadOnly(true);
					win.LpuStructureGrid.setReadOnly(true);
					win.setTitle(lang['numerator_prosmotr']);
				}

				base_form.load({
					failure:function () {
						win.getLoadMask().hide();
						win.hide();
					},
					url: '/?c=Numerator&m=loadNumeratorEditForm',
					params: {
						Numerator_id: base_form.findField('Numerator_id').getValue()
					},
					success: function() {
						win.getLoadMask().hide();
						win.generateNumeratorNumSample();
						base_form.findField('Lpu_id').fireEvent('change', base_form.findField('Lpu_id'), base_form.findField('Lpu_id').getValue());
					}
				});

				this.NumeratorRezervGrid.loadData({ globalFilters: { Numerator_id: base_form.findField('Numerator_id').getValue(), NumeratorRezervType_id: 1 } });
				this.NumeratorRezervGenGrid.loadData({ globalFilters: { Numerator_id: base_form.findField('Numerator_id').getValue(), NumeratorRezervType_id: 2 } });
				this.NumeratorLinkGrid.loadData({ globalFilters: { Numerator_id: base_form.findField('Numerator_id').getValue() } });
				this.LpuGrid.loadData({ globalFilters: { Numerator_id: base_form.findField('Numerator_id').getValue() } });
				this.LpuStructureGrid.loadData({ globalFilters: { Numerator_id: base_form.findField('Numerator_id').getValue() } });

				break;
		}

		if (base_form.findField('Numerator_Name').disabled) {
			win.buttons[0].focus();
		} else {
			base_form.findField('Numerator_Name').focus();
		}
	},
	onChangeLpu: function() {
		var win = this;
		var base_form = win.FormPanel.getForm();

		// проверяем количество МО в гриде
		if (win.LpuGrid.getGrid().getStore().getCount() == 1) {
			var Lpu_id = win.LpuGrid.getGrid().getStore().getAt(0).get('Lpu_id');
			base_form.findField('LpuStructure_id').clearValue();
			base_form.findField('LpuStructure_id').getStore().removeAll();
			win.getLoadMask(lang['zagruzka_strukturyi_mo']).show();
			base_form.findField('LpuStructure_id').getStore().load({
				params: {
					Lpu_id: Lpu_id
				},
				callback: function() {
					win.getLoadMask().hide();
				}
			});
		} else {
			base_form.findField('LpuStructure_id').clearValue();
			base_form.findField('LpuStructure_id').getStore().removeAll();
			var grid = win.LpuStructureGrid.getGrid();
			grid.getStore().each(function(rec) {
				if (rec.data.Record_Status == 0)
				{
					grid.getStore().remove(rec);
				}
				else
				{
					rec.set('Record_Status', 3);
					rec.commit();
					grid.getStore().filterBy(function(record)
					{
						if (record.data.Record_Status != 3)
						{
							return true;
						}
					});
				}
			});
		}
	},
	initComponent: function() {
		var win = this;

		this.NumeratorRezervGrid = new sw.Promed.ViewFrame({
			id: win.id+'NumeratorRezervGrid',
			title: lang['diapazonyi_rezervirovaniya'],
			object: 'NumeratorRezerv',
			dataUrl: '/?c=Numerator&m=loadNumeratorRezervList',
			autoLoadData: false,
			region: 'center',
			toolbar: true,
			useEmptyRecord: false,
			stringfields:
			[
				{name: 'NumeratorRezerv_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'NumeratorRezerv_From', header: lang['nachalo_diapazona'], width: 200},
				{name: 'NumeratorRezerv_To', header: lang['konets_diapazona'], width: 200},
				{name: 'Record_Status', type: 'int', hidden: true}
			],
			onDblClick: function() {
				win.openNumeratorRezervEditWindow('edit');
			},
			onRowSelect: function(sm,index,record)
			{
				this.getAction('action_edit').disable();
				this.getAction('action_view').disable();
				this.getAction('action_delete').disable();
				if (record.get('NumeratorRezerv_id')) {
					this.getAction('action_view').enable();
					this.getAction('action_edit').enable();
					this.getAction('action_delete').enable();
				}
			},
			actions:
			[
				{name:'action_add', handler: function() { win.openNumeratorRezervEditWindow('add'); }},
				{name:'action_edit', handler: function() { win.openNumeratorRezervEditWindow('edit'); }},
				{name:'action_view', handler: function() { win.openNumeratorRezervEditWindow('view'); }},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_refresh', disabled: false},
				{name:'action_delete', handler: function() { win.deleteNumeratorRezerv(); }},
			]
		});

		this.NumeratorRezervGenGrid = new sw.Promed.ViewFrame({
			id: win.id+'NumeratorRezervGenGrid',
			title: lang['diapazonyi_generatsii'],
			object: 'NumeratorRezerv',
			dataUrl: '/?c=Numerator&m=loadNumeratorRezervList',
			autoLoadData: false,
			region: 'center',
			toolbar: true,
			useEmptyRecord: false,
			stringfields:
			[
				{name: 'NumeratorRezerv_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'NumeratorRezerv_From', header: lang['nachalo_diapazona'], width: 200},
				{name: 'NumeratorRezerv_To', header: lang['konets_diapazona'], width: 200},
				{name: 'Record_Status', type: 'int', hidden: true}
			],
			onDblClick: function() {
				win.openNumeratorRezervGenEditWindow('edit');
			},
			onRowSelect: function(sm,index,record)
			{
				this.getAction('action_edit').disable();
				this.getAction('action_view').disable();
				this.getAction('action_delete').disable();
				if (record.get('NumeratorRezerv_id')) {
					this.getAction('action_view').enable();
					this.getAction('action_edit').enable();
					this.getAction('action_delete').enable();
				}
			},
			actions:
			[
				{name:'action_add', handler: function() { win.openNumeratorRezervGenEditWindow('add'); }},
				{name:'action_edit', handler: function() { win.openNumeratorRezervGenEditWindow('edit'); }},
				{name:'action_view', handler: function() { win.openNumeratorRezervGenEditWindow('view'); }},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_refresh', disabled: false},
				{name:'action_delete', handler: function() { win.deleteNumeratorRezervGen(); }},
			]
		});

		this.NumeratorLinkGrid = new sw.Promed.ViewFrame({
			id: win.id+'NumeratorLinkGrid',
			title: lang['svyazannyie_dokumentyi'],
			object: 'NumeratorLink',
			height: 130,
			dataUrl: '/?c=Numerator&m=loadNumeratorLinkList',
			autoLoadData: false,
			region: 'center',
			toolbar: true,
			useEmptyRecord: false,
			stringfields:
			[
				{name: 'NumeratorLink_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'NumeratorObject_TableName', header: lang['dokument'], width: 200, id: 'autoexpand'},
				{name: 'NumeratorObject_id', type: 'int', hidden: true},
				{name: 'Record_Status', type: 'int', hidden: true}
			],
			onDblClick: function() {

			},
			onRowSelect: function(sm,index,record)
			{
			},
			actions:
			[
				{name:'action_add', handler: function() { win.openNumeratorLinkEditWindow('add'); }},
				{name:'action_edit', handler: function() { win.openNumeratorLinkEditWindow('edit'); }},
				{name:'action_view', handler: function() { win.openNumeratorLinkEditWindow('view'); }},
				{name:'action_print', disabled: true, hidden: true},
				{name:'action_refresh', disabled: true, hidden: true},
				{name:'action_delete', handler: function() { win.deleteNumeratorLink(); }},
			]
		});

		this.LpuGrid = new sw.Promed.ViewFrame({
			id: win.id+'LpuGrid',
			title: lang['mo'],
			object: 'NumeratorLpu',
			height: 150,
			dataUrl: '/?c=Numerator&m=loadNumeratorLpuList',
			autoLoadData: false,
			region: 'center',
			toolbar: false,
			useEmptyRecord: false,
			stringfields:
			[
				{name: 'NumeratorLpu_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'Lpu_Nick', header: lang['naimenovanie'], width: 200, id: 'autoexpand'},
				{name: 'OrgType_Name', header: lang['tip'], width: 200},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'Record_Status', type: 'int', hidden: true}
			],
			onLoadData: function() {
				win.onChangeLpu();
			},
			onDblClick: function() {

			},
			onRowSelect: function(sm,index,record)
			{
			},
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_print', hidden: true, disabled: true},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true}
			]
		});

		this.LpuStructureGrid = new sw.Promed.ViewFrame({
			id: win.id+'LpuStructureGrid',
			title: lang['struktura_mo'],
			object: 'NumeratorLpuStructure',
			height: 150,
			dataUrl: '/?c=Numerator&m=loadNumeratorLpuStructureList',
			autoLoadData: false,
			region: 'center',
			toolbar: false,
			useEmptyRecord: false,
			stringfields:
			[
				{name: 'NumeratorLpu_id', type: 'int', header: 'ID', key: true, hidden: true},
				{name: 'LpuStructure_Name', header: lang['naimenovanie'], width: 200, id: 'autoexpand'},
				{name: 'LpuStructureType_Name', header: lang['tip'], width: 200},
				{name: 'LpuStructure_id', type: 'int', hidden: true},
				{name: 'LpuBuilding_id', type: 'int', hidden: true},
				{name: 'LpuUnit_id', type: 'int', hidden: true},
				{name: 'LpuSection_id', type: 'int', hidden: true},
				{name: 'Record_Status', type: 'int', hidden: true}
			],
			onDblClick: function() {

			},
			onRowSelect: function(sm,index,record)
			{
			},
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_edit', hidden: true, disabled: true},
				{name:'action_view', hidden: true, disabled: true},
				{name:'action_print', hidden: true, disabled: true},
				{name:'action_refresh', hidden: true, disabled: true},
				{name:'action_delete', hidden: true, disabled: true}
			]
		});
		
		this.InfoPanel = new sw.Promed.Panel({
			layout: 'form',
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			labelWidth: 160,
			labelAlign: 'right',
			items: [{
				name: 'Numerator_id',
				xtype: 'hidden'
			}, {
				name: 'Numerator_Num',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				name: 'Numerator_Name',
				fieldLabel: lang['naimenovanie'],
				xtype: 'textfield',
				anchor: '-10'
			}, {
				title: lang['parametryi_formirovaniya_nomera'],
				autoHeight: true,
				labelWidth: 150,
				xtype: 'fieldset',
				items: [{
					layout : 'column',
					items : [{
						layout : 'form',
						columnWidth: 0.6,
						items : [{
							name: 'Numerator_Ser',
							fieldLabel: lang['seriya'],
							listeners: {
								'keyup': function(inp, e) {
									win.generateNumeratorNumSample();
								}
							},
							enableKeyEvents: true,
							xtype: 'textfield',
							width: 180
						}, {
							name: 'Numerator_NumLen',
							fieldLabel: lang['dlina_nomera'],
							listeners: {
								'keyup': function(inp, e) {
									win.generateNumeratorNumSample();
								}
							},
							enableKeyEvents: true,
							allowDecimal: false,
							allowNegative: false,
							xtype: 'numberfield',
							width: 180
						}, {
							name: 'Numerator_PreNum',
							listeners: {
								'keyup': function(inp, e) {
									win.generateNumeratorNumSample();
								}
							},
							enableKeyEvents: true,
							fieldLabel: lang['prefiks'],
							xtype: 'textfield',
							width: 180
						}, {
							name: 'Numerator_PostNum',
							listeners: {
								'keyup': function(inp, e) {
									win.generateNumeratorNumSample();
								}
							},
							enableKeyEvents: true,
							fieldLabel: lang['postfiks'],
							xtype: 'textfield',
							width: 180
						}, {
							hiddenName: 'NumeratorGenUpd_id',
							comboSubject: 'NumeratorGenUpd',
							fieldLabel: lang['chastota_obnuleniya'],
							xtype: 'swcommonsprcombo',
							width: 180
						}]
					}, {
						layout : 'form',
						columnWidth: 0.4,
						items : [{
							title: lang['primer_sformirovannogo_nomera'],
							autoHeight: true,
							xtype: 'fieldset',
							items: [{
								xtype: 'label',
								id: 'NumeratorNumSample',
								html: ''
							}]
						}]
					}]
				}]
			}, {
				allowBlank: false,
				name: 'Numerator_begDT',
				fieldLabel: lang['nachalo_deystviya'],
				xtype: 'swdatefield'
			}, {
				name: 'Numerator_endDT',
				fieldLabel: lang['okonchanie_deystviya'],
				xtype: 'swdatefield'
			}]
		});

		this.TabPanel = new Ext.TabPanel({
			activeTab: 0,
			border: false,
			region: 'center',
			layoutOnTabChange: true,
			region: 'center',
			items: [{
				id: 'tab_numeratorinfo',
				layout: 'form',
				frame: true,
				title: lang['osnovnyie_svedeniya'],
				bodyStyle: 'padding:10px 0px 0px 10px;',
				items: [
					win.InfoPanel
				]
			}, {
				id: 'tab_numeratordoc',
				layout: 'border',
				frame: true,
				title: lang['svyazannyie_dokumentyi'],
				items: [
					win.NumeratorLinkGrid
				]
			}, {
				id: 'tab_numeratormo',
				layout: 'form',
				frame: true,
				title: lang['svyazannyie_mo'],
				items: [
					{
						layout : 'column',
						bodyStyle: 'padding:10px 0px 0px 10px;',
						items : [{
							layout : 'form',
							labelWidth: 100,
							labelAlign: 'right',
							columnWidth: 0.9,
							items : [{
								allowBlank: true,
								hiddenName: 'Lpu_id',
								fieldLabel: lang['mo'],
								xtype: 'swlpucombo',
								anchor: '-10'
							}]
						}, {
							layout : 'form',
							hidden: !isSuperAdmin(),
							items : [{
								xtype: 'button',
								style: 'margin: 0px 2px 0px 3px;',
								text: BTN_GRIDADD,
								tabIndex : 1702,
								iconCls : 'add16',
								handler : function() {
									var base_form = win.FormPanel.getForm();
									var combo = base_form.findField('Lpu_id');

									if (combo.getValue() == '' || combo.getValue() == null) {
										return;
									}
									var store = combo.getStore();
									var row = store.getAt(store.findBy(function(rec) { return rec.get('Lpu_id') == combo.getValue(); }));
									var lpu_id = row.data.Lpu_id;
									var lpu_nick = row.data.Lpu_Nick;
									var OrgType_Name = row.data.OrgType_Name;
									var grid = win.LpuGrid.getGrid();
									if (grid.getStore().findBy(function(rec) { return rec.get('Lpu_id') == lpu_id; }) > -1) {
										return;
									}
									grid.getStore().loadData([{
										Lpu_id: lpu_id,
										Lpu_Nick: lpu_nick,
										OrgType_Name: OrgType_Name,
										Record_Status: 0
									}], true);
									grid.getView().refresh();
								}
							}]
						}, {
							layout : 'form',
							hidden: !isSuperAdmin(),
							items : [{
								xtype : 'button',
								text : BTN_GRIDDEL,
								tabIndex : 1703,
								iconCls : 'delete16',
								handler : function() {
									var grid = win.LpuGrid.getGrid();

									var selected_record = grid.getSelectionModel().getSelected();
									if (selected_record.data.Record_Status == 0)
									{
										grid.getStore().remove(selected_record);
									}
									else
									{
										selected_record.set('Record_Status', 3);
										selected_record.commit();
										grid.getStore().filterBy(function(record)
										{
											if (record.data.Record_Status != 3)
											{
												return true;
											}
										});
									}

									grid.getView().refresh();
									win.onChangeLpu();
								}
							}]
						}]
					}, win.LpuGrid, {
						layout : 'column',
						bodyStyle: 'padding:10px 0px 0px 10px;',
						items : [{
							layout : 'form',
							labelWidth: 100,
							labelAlign: 'right',
							columnWidth: 0.9,
							items: [{
								allowBlank: true,
								hiddenName: 'LpuStructure_id',
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'LpuStructure_id'
									}, [
										{ name: 'LpuStructure_id', mapping: 'LpuStructure_id' },
										{ name: 'LpuStructureType_Name', mapping: 'LpuStructureType_Name' },
										{ name: 'LpuStructure_Name', mapping: 'LpuStructure_Name' },
										{ name: 'LpuBuilding_id', mapping: 'LpuBuilding_id' },
										{ name: 'LpuUnit_id', mapping: 'LpuUnit_id' },
										{ name: 'LpuSection_id', mapping: 'LpuSection_id' },
										{ name: 'sort', mapping: 'sort' }
									]),
									key: 'LpuStructure_id',
									sortInfo: {
										field: 'sort'
									},
									url: '/?c=Numerator&m=loadLpuStructureCombo'
								}),
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'{[(values.LpuBuilding_id > 0) ? "<b>" : ""]}',
									'{[(values.LpuSection_id > 0) ? "<i>" : ""]}',
									'{LpuStructure_Name}',
									'{[(values.LpuSection_id > 0) ? "</i>" : ""]}',
									'{[(values.LpuBuilding_id > 0) ? "</b>" : ""]}',
									'</div></tpl>'
								),
								displayField: 'LpuStructure_Name',
								valueField: 'LpuStructure_id',
								fieldLabel: lang['struktura_mo'],
								xtype: 'swbaselocalcombo',
								anchor: '-10'
							}]
						}, {
							layout : 'form',
							items : [{
								xtype: 'button',
								style: 'margin: 0px 2px 0px 3px;',
								text: BTN_GRIDADD,
								tabIndex : 1702,
								iconCls : 'add16',
								handler : function() {
									var base_form = win.FormPanel.getForm();
									var combo = base_form.findField('LpuStructure_id');

									if (combo.getValue() == '' || combo.getValue() == null) {
										return;
									}
									var store = combo.getStore();
									var record = store.getAt(store.findBy(function(rec) { return rec.get('LpuStructure_id') == combo.getValue(); }));
									var grid = win.LpuStructureGrid.getGrid();
									if (grid.getStore().findBy(function(rec) { return rec.get('LpuStructure_id') == LpuStructure_id; }) > -1) {
										return;
									}
									grid.getStore().loadData([{
										LpuStructure_id: record.get('LpuStructure_id'),
										LpuStructure_Name: record.get('LpuStructure_Name'),
										LpuStructureType_Name: record.get('LpuStructureType_Name'),
										LpuBuilding_id: record.get('LpuBuilding_id'),
										LpuUnit_id: record.get('LpuUnit_id'),
										LpuSection_id: record.get('LpuSection_id'),
										Record_Status: 0
									}], true);
									grid.getView().refresh();
								}
							}]
						}, {
							layout : 'form',
							items : [{
								xtype : 'button',
								text : BTN_GRIDDEL,
								tabIndex : 1703,
								iconCls : 'delete16',
								handler : function() {
									var grid = win.LpuStructureGrid.getGrid();

									var selected_record = grid.getSelectionModel().getSelected();
									if (selected_record.data.Record_Status == 0)
									{
										grid.getStore().remove(selected_record);
									}
									else
									{
										selected_record.set('Record_Status', 3);
										selected_record.commit();
										grid.getStore().filterBy(function(record)
										{
											if (record.data.Record_Status != 3)
											{
												return true;
											}
										});
									}

									grid.getView().refresh();
								}
							}]
						}]
					}, win.LpuStructureGrid
				]
			}, {
				id: 'tab_numeratorrez',
				layout: 'border',
				frame: true,
				tabTip: lang['rezerv_nomerov_dlya_vyipiski_dokumentov_na_blankah'],
				title: lang['diapazonyi_rezervirovaniya'],
				items: [
					win.NumeratorRezervGrid
				]
			}, {
				id: 'tab_numeratorgen',
				layout: 'border',
				frame: true,
				tabTip: lang['diapazonyi_nomerov_dlya_vyipiski_dokumentov_na_listah'],
				title: lang['diapazonyi_generatsii'],
				items: [
					win.NumeratorRezervGenGrid
				]
			}],
			listeners:
			{
				tabchange: function(tab, panel) {
					switch(panel.id) {
						case 'tab_attributes':
							win.AttributeGrid.loadData();
							break;

						case 'tab_tariffsattribute':
							win.TariffClassGrid.loadData();
							break;

						case 'tab_volumesattribute':
							win.VolumeTypeGrid.loadData();
							break;
					}
					win.doLayout();
				}
			}
		});

		this.FormPanel = new Ext.form.FormPanel({
			url: '/?c=Numerator&m=saveNumerator',
			bodyBorder: false,
			border: false,
			frame: true,
			region: 'center',
			layout: 'border',
			items: [
				this.TabPanel
			],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'Numerator_id'},
				{name: 'Numerator_Name'},
				{name: 'Numerator_Num'},
				{name: 'Numerator_begDT'},
				{name: 'Numerator_endDT'},
				{name: 'NumeratorGenUpd_id'},
				{name: 'Numerator_Ser'},
				{name: 'Numerator_NumLen'},
				{name: 'Numerator_PreNum'},
				{name: 'Numerator_PostNum'}
			]),
			keys: [{
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swNumeratorEditWindow.superclass.initComponent.apply(this, arguments);
	}
});