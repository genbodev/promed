/**
 * swInvoiceEditWindow - окно учёта ТМЦ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Admin
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			06.10.2014
 */
/*NO PARSE JSON*/

sw.Promed.swInvoiceEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swInvoiceEditWindow',
	maximizable: false,
	maximized: true,
	layout: 'border',

	listeners: {
		'beforehide': function() {
			var base_form = this.FormPanel.getForm();
			var position_grid = this.InvoicePositionGrid.getGrid();

			var invoice_id = base_form.findField('Invoice_id').getValue();
			var invoice_type_id = base_form.findField('InvoiceType_id').getValue();

			if (!this.deleted && !Ext.isEmpty(invoice_id)) {
				if (this.presave) {
					this.deleted = true;
					this.cancelInvoice();
				} else if (position_grid.getStore().getCount() == 0 && this.positionGridLoaded == true) {
					sw.swMsg.show({
						buttons:{yes: lang['udalit_i_vyiyti'], no: lang['prodoljit_redatirovanie']},
						fn:function (buttonId, text, obj) {
							if (buttonId == 'yes') {
								this.deleted = true;
								this.cancelInvoice();
								this.hide();
							}
						}.createDelegate(this),
						icon:Ext.MessageBox.QUESTION,
						msg:lang['net_pozitsiy_v_nakladnoy_udalit_nakladnuyu'],
						title:lang['podtverjdenie']
					});
					return false;
				} else if (invoice_type_id == 2 && !this.saved && this.edited) {
					//В расходной накладной была изменена позиция, но
					// сама накладная не сохранялась, тогда вызываем пересчет накладной
					this.calculateInvoicePositions();
				}
			}
			return true;
		}
	},

	calculateSummary: function() {
		var position_grid = this.InvoicePositionGrid.getGrid();
		var bbar = this.InvoicePositionGrid.getBottomToolbar();
		var sum = 0;

		position_grid.getStore().each(function(rec) {
			sum += rec.get('InvoicePosition_Sum');
		});

		bbar.items.last().el.innerHTML = lang['obschaya_summa_po_nakladnoy']+sum;
	},

	doSave: function(options) {
		options = options || {};

		var base_form = this.FormPanel.getForm();
		var position_grid = this.InvoicePositionGrid.getGrid();

		if ( !base_form.isValid() ){
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

		var params = {};

		position_grid.getStore().clearFilter();
		if ( position_grid.getStore().getCount() > 0 ) {
			var InvoicePositionData = getStoreRecords(position_grid.getStore(),{
				exceptionFields: ['InventoryItem_Name','Okei_NationSymbol']
			});

			for(i=0; i<InvoicePositionData.length; i++) {
				if (base_form.findField('Storage_id').getValue() != InvoicePositionData[i].Storage_id) {
					InvoicePositionData[i].Storage_id = base_form.findField('Storage_id').getValue();
					if (InvoicePositionData[i].RecordStatus_Code == 1) {
						InvoicePositionData[i].RecordStatus_Code = 2;
					}
				}
				if (base_form.findField('Invoice_Date').getValue() != InvoicePositionData[i].Invoice_Date) {
					InvoicePositionData[i].Invoice_Date = base_form.findField('Invoice_Date').getValue();
					if (InvoicePositionData[i].RecordStatus_Code == 1) {
						InvoicePositionData[i].RecordStatus_Code = 2;
					}
				}
			}

			params.InvoicePositionData = Ext.util.JSON.encode(InvoicePositionData);

			position_grid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		} else {
			if (!options.presave) {
				Ext.Msg.alert(lang['oshibka'], lang['v_nakladnoy_ne_vvedeno_ni_odnoy_pozitsii']);
				return false;
			}
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			params: params,
			failure: function(result_form, action)
			{
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action)
			{
				loadMask.hide();
				base_form.findField('Invoice_id').setValue(action.result.Invoice_id);
				position_grid.getStore().baseParams.Invoice_id = action.result.Invoice_id;

				this.saved = true;
				if (options.presave) {
					options.callback();
				} else {
					this.presave = false;
					this.callback();
					this.hide();
				}
			}.createDelegate(this)
		});
	},

	calculateInvoicePositions: function() {
		var base_form = this.FormPanel.getForm();

		var params = {
			Invoice_id: base_form.findField('Invoice_id').getValue()
		};

		Ext.Ajax.request({
			params: params,
			url: '/?c=Invoice&m=calculateInvoicePositions',
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.Error_Msg) {
					Ext.Msg.alert(lang['oshibka'], response_obj.Error_Msg);
				} else {
					this.callback();
				}
			}.createDelegate(this),
			failure: function(response) {

			}.createDelegate(this)
		});
	},

	cancelInvoice: function() {
		var base_form = this.FormPanel.getForm();

		var params = {
			Invoice_id: base_form.findField('Invoice_id').getValue()
		};

		Ext.Ajax.request({
			params: params,
			url: '/?c=Invoice&m=deleteInvoice',
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.Error_Msg) {
					Ext.Msg.alert(lang['oshibka'], response_obj.Error_Msg);
				} else {
					this.callback();
				}
			}.createDelegate(this),
			failure: function(response) {

			}.createDelegate(this)
		});
	},

	openInvoicePositionEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return false;
		}

		var form = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.InvoicePositionGrid.getGrid();

		if (Ext.isEmpty(base_form.findField('Invoice_id').getValue())) {
			this.presave = true;
			this.doSave({
				presave: true,
				callback: function() {form.openInvoicePositionEditWindow(action)}
			});
			return;
		}

		var params = {};

		params.action = action;
		params.InvoiceType_id = base_form.findField('InvoiceType_id').getValue();
		params.formParams = {
			Storage_id: base_form.findField('Storage_id').getValue(),
			Invoice_Date: base_form.findField('Invoice_Date').getValue()
		};

		if ( action == 'add' ) {
			params.formParams.InvoicePosition_PositionNum = grid.getStore().getCount()+1;
			params.formParams.Invoice_id = base_form.findField('Invoice_id').getValue();
		} else {
			var record = grid.getSelectionModel().getSelected();
			if ( !record || !record.get('InvoicePosition_id') ) {
				 return false;
			}

			params.formParams.InvoicePosition_id = record.get('InvoicePosition_id');
		}

		params.callback = function(data) {
			this.edited = true;
			this.InvoicePositionGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swInvoicePositionEditWindow').show(params);

		return true;
	},

	deleteInvoicePosition: function() {
		var base_form = this.FormPanel.getForm();
		var grid = this.InvoicePositionGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('InvoicePosition_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var grid = this.InvoicePositionGrid.getGrid();
					var record = grid.getSelectionModel().getSelected();

					if (!record || !record.get('InvoicePosition_id')) {
						return false;
					}

					var params = {
						InvoicePosition_id: record.get('InvoicePosition_id'),
						InvoiceType_id: base_form.findField('InvoiceType_id').getValue()
					};

					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							this.edited = true;
							this.InvoicePositionGrid.getAction('action_refresh').execute();
						}.createDelegate(this),
						params: params,
						url: '/?c=Invoice&m=deleteInvoicePosition'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},

	genTitle: function() {
		var base_form = this.FormPanel.getForm();
		var invoice_type_id = base_form.findField('InvoiceType_id').getValue();
		var title = '';
		if (Ext.isEmpty(invoice_type_id) || Ext.isEmpty(this.action)) {
			return '';
		}
		switch(parseInt(invoice_type_id)) {
			case 1: title = lang['prihodnaya_nakladnaya']; break;
			case 2: title = lang['rashodnaya_nakladnaya']; break;
		}
		switch(this.action) {
			case 'add': title += lang['_dobavlenie']; break;
			case 'edit': title += lang['_redaktirovanie']; break;
			case 'view': title += lang['_prosmotr']; break;
		}
		return title;
	},

	show: function() {
		sw.Promed.swInvoiceEditWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;
		this.presave = false;
		this.deleted = false;
		this.saved = false;
		this.edited = false;
		this.positionGridLoaded = false;

		var base_form = this.FormPanel.getForm();
		var grid = this.InvoicePositionGrid.getGrid();

		base_form.reset();
		grid.getStore().removeAll();
		this.calculateSummary();

		if (!arguments[0] || !arguments[0].action || !arguments[0].formParams) {
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		this.action = arguments[0].action;

		base_form.setValues(arguments[0].formParams);

		base_form.items.each(function(f){f.validate();});

		var invoice_type_id = base_form.findField('InvoiceType_id').getValue();

		if (invoice_type_id == 1) {
			base_form.findField('InvoiceSubject_id').setFieldLabel(lang['postavschik']);
		} else if (invoice_type_id == 2) {
			base_form.findField('InvoiceSubject_id').setFieldLabel(lang['poluchatel']);
		}

		var invoice_subject_combo = base_form.findField('InvoiceSubject_id');
		var storage_field = base_form.findField('Storage_id');
		var storage_combo = base_form.findField('StorageStructLevel_id');

		invoice_subject_combo.lastQuery = 'This query sample that is not will never appear';
		storage_combo.lastQuery = 'This query sample that is not will never appear';
		storage_combo.getStore().baseParams.Lpu_aid = getGlobalOptions().lpu_id;

		//пока реализуется только расчет "Без расчета" (PayInvoiceType_Code = 0)
		base_form.findField('PayInvoiceType_id').setBaseFilter(function(rec){
			return (rec.get('InvoiceType_id')==invoice_type_id && rec.get('PayInvoiceType_Code')==0);
		});
		base_form.findField('PayInvoiceType_id').setFieldValue('PayInvoiceType_Code',0);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle(this.genTitle());
				this.enableEdit(true);
				this.InvoicePositionGrid.setReadOnly(false);
				this.positionGridLoaded = true;
				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				this.setTitle(this.genTitle());

				if (this.action=='edit') {
					this.enableEdit(true);
					this.InvoicePositionGrid.setReadOnly(false);
				} else {
					this.enableEdit(false);
					this.InvoicePositionGrid.setReadOnly(true);
				}

				base_form.load({
					url: '/?c=Invoice&m=loadInvoiceForm',
					params: {Invoice_id: base_form.findField('Invoice_id').getValue()},
					success: function ()
					{
						loadMask.hide();

						invoice_subject_combo.getStore().load({
							params: {InvoiceSubject_id: invoice_subject_combo.getValue()},
							callback: function() {
								invoice_subject_combo.setValue(invoice_subject_combo.getValue());
							}
						});
						storage_combo.getStore().load({
							params: {Storage_id: storage_field.getValue()},
							callback: function() {
								var index = storage_combo.getStore().findBy(function(rec) { return rec.get('Storage_id') == storage_field.getValue(); });
								var record = storage_combo.getStore().getAt(index);

								if (record) {
									storage_combo.setValue(record.get('StorageStructLevel_id'));
								}
							}
						});

						grid.getStore().load({
							params: {Invoice_id: base_form.findField('Invoice_id').getValue()},
							callback: function(){this.positionGridLoaded = true}.createDelegate(this)
						});
					}.createDelegate(this),
					failure: function (form,action)
					{
						loadMask.hide();
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
					}.createDelegate(this)
				});
				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'IEW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			url: '/?c=Invoice&m=saveInvoice',
			items: [{
				xtype: 'hidden',
				name: 'Invoice_id'
			}, {
				xtype: 'hidden',
				name: 'InvoiceType_id'
			}, {
				allowBlank: false,
				xtype: 'hidden',
				name: 'Storage_id'
			}, {
				allowBlank: false,
				xtype: 'swdatefield',
				name: 'Invoice_Date',
				fieldLabel: lang['data']
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'Invoice_Num',
				fieldLabel: lang['nomer']
			}, {
				allowBlank: false,
				xtype: 'swinvoicesubjectcombo',
				hiddenName: 'InvoiceSubject_id',
				fieldLabel: lang['postavschik'],
				width: 300
			}, {
				allowBlank: false,
				xtype: 'swstoragestructlevelcombo',
				hiddenName: 'StorageStructLevel_id',
				fieldLabel: lang['sklad'],
				listeners: {
					'select': function(combo, record, index) {
						var base_form = this.FormPanel.getForm();

						if (record.get('Storage_id')) {
							base_form.findField('Storage_id').setValue(record.get('Storage_id'));
						} else {
							base_form.findField('Storage_id').setValue(null);
						}
					}.createDelegate(this)
				},
				width: 300
			}, {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'PayInvoiceType',
				hiddenName: 'PayInvoiceType_id',
				fieldLabel: lang['raschet'],
				lastQuery: '',
				moreFields: [{name: 'InvoiceType_id', mapping: 'InvoiceType_id'}],
				width: 300
			}, {
				xtype: 'textfield',
				name: 'Invoice_Comment',
				fieldLabel: lang['primechanie'],
				width: 300
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'Invoice_id'},
				{name: 'InvoiceType_id'},
				{name: 'Invoice_Date'},
				{name: 'Invoice_Num'},
				{name: 'InvoiceSubject_id'},
				{name: 'Storage_id'},
				{name: 'PayInvoiceType_id'},
				{name: 'Invoice_Comment'}
			])
		});

		this.InvoicePositionGrid = new sw.Promed.ViewFrame({
			title: lang['pozitsiya'],
			id: 'IEW_InvoicePositionGrid',
			dataUrl: '/?c=Invoice&m=loadInvoicePositionGrid',
			border: true,
			autoLoadData: false,
			useEmptyRecord: false,
			root: 'data',
			region: 'center',
			editformclassname: 'swInvoicePositionEditWindow',
			bbar: [
				'->',
				lang['obschaya_summa_po_nakladnoy_0']
			],
			stringfields: [
				{name: 'InvoicePosition_id', type: 'int', header: 'ID', key: true},
				{name: 'Invoice_id', type: 'int', hidden: true},
				{name: 'RecordStatus_Code', type: 'int', hidden: true},
				{name: 'Okei_id', type: 'int', hidden: true},
				{name: 'InvoicePosition_Coeff', type: 'float', hidden: true},
				{name: 'Shipment_id', type: 'int', hidden: true},
				{name: 'InvoicePosition_PrevCount', type: 'int', hidden: true},
				{name: 'InventoryItem_id', type: 'int', hidden: true},
				{name: 'InvoicePosition_Comment', type: 'string', hidden: true},
				{name: 'Storage_id', type: 'int', hidden: true},
				{name: 'Invoice_Date', type: 'date', hidden: true},
				{name: 'InvoicePosition_PositionNum', header: lang['nomer_pozitsii'], type: 'int', width: 120},
				{name: 'InventoryItem_Name', header: lang['tmts'], type: 'string', width: 120, id: 'autoexpand'},
				{name: 'Okei_NationSymbol', header: lang['ed_izm'], type: 'string', width: 160},
				{name: 'InvoicePosition_Count', header: lang['kolichestvo'], type: 'float', width: 120},
				{name: 'InvoicePosition_Price', header: lang['tsena'], type: 'money', width: 120},
				{name: 'InvoicePosition_Sum', header: lang['summa'], type: 'money', width: 120}
			],
			actions: [
				{name:'action_add', handler: function(){this.openInvoicePositionEditWindow('add');}.createDelegate(this)},
				{name:'action_edit', handler: function(){this.openInvoicePositionEditWindow('edit');}.createDelegate(this)},
				{name:'action_view', handler: function(){this.openInvoicePositionEditWindow('view');}.createDelegate(this)},
				{name:'action_delete', handler: function(){this.deleteInvoicePosition();}.createDelegate(this)},
				{name:'action_refresh', disabled: true, hidden: true}
			],
			onLoadData: function(sm, index, record){
				this.calculateSummary();
			}.createDelegate(this)
		});

		Ext.apply(this,
		{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'IEW_SaveButton',
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
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel, this.InvoicePositionGrid]
		});

		sw.Promed.swInvoiceEditWindow.superclass.initComponent.apply(this, arguments);
	}
});