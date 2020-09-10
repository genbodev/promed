/**
 * swPrepBlockEditWindow - окно редактирования блокировки cерии ЛС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Dlo
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.02.2015
 */
/*NO PARSE JSON*/

sw.Promed.swPrepBlockEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPrepBlockEditWindow',
	width: 760,
	autoHeight: true,
	modal: true,

	addFileToFilesPanel: function(text, link, fieldName, deletable) {
		var base_form = this.FormPanel.getForm();
		var FilesPanel = this.findById('PBEW_FilesPanel');

		var FileEl = new Ext.Panel({
			layout: 'column',
			bodyStyle: 'margin-bottom: 5px;',
			items: [{
				layout: 'form',
				items: [{
					style: 'font: 12px;',
					html: '<a target="_blank" href="'+link+'">'+text+'</a>'
				}]
			}, {
				layout: 'form',
				items: [{
					id: 'PBEW_Delete_'+fieldName,
					style: 'margin-left: 10px;',
					hidden: !deletable,
					xtype: 'button',
					iconCls: 'delete16',
					handler: function() {
						FilesPanel.remove(FileEl.id);
						base_form.findField(fieldName).setValue(null);
						if (Ext.isEmpty(base_form.findField('DocNormative_File_1').getValue()) || Ext.isEmpty(base_form.findField('DocNormative_File_2').getValue())) {
							Ext.getCmp('PBEW_AddFileBtn').show();
						}
						if (Ext.isEmpty(base_form.findField('DocNormative_File_2').getValue())) {
							base_form.findField('DocNormative_Num_2').setAllowBlank(true);
							base_form.findField('DocNormative_begDate_2').setAllowBlank(true);
							base_form.findField('DocNormative_Name_2').setAllowBlank(true);
						}
						FilesPanel.doLayout();
						this.syncShadow();
					}.createDelegate(this)
				}]
			}]
		});

		FilesPanel.add(FileEl);
		FilesPanel.doLayout();
	},

	openDocNormativeFileUploadWindow: function() {
		var wnd = this;
		var base_form = this.FormPanel.getForm();

		var params = {excludeDocNormativeTypes: []};

		if (!Ext.isEmpty(base_form.findField('DocNormative_File_1').getValue())) {
			params.excludeDocNormativeTypes.push(1);
		}
		if (!Ext.isEmpty(base_form.findField('DocNormative_File_2').getValue())) {
			params.excludeDocNormativeTypes.push(2);
		}

		params.callback = function(data) {
			if (data.DocNormativeData && data.DocNormativeData.DocNormativeType_id > 0) {
				var n = data.DocNormativeData.DocNormativeType_id;
				base_form.findField('DocNormative_File_'+n).setValue(data.DocNormativeData.DocNormative_File);
				var arr = data.DocNormativeData.DocNormative_File.split('/');
				var filename = arr[arr.length-1];
				wnd.addFileToFilesPanel(filename, data.DocNormativeData.DocNormative_File, 'DocNormative_File_'+n, true);
			}
			if (!Ext.isEmpty(base_form.findField('DocNormative_File_1').getValue()) && !Ext.isEmpty(base_form.findField('DocNormative_File_2').getValue())) {
				Ext.getCmp('PBEW_AddFileBtn').hide();
			}
			if (!Ext.isEmpty(base_form.findField('DocNormative_File_2').getValue())) {
				base_form.findField('DocNormative_Num_2').setAllowBlank(false);
				base_form.findField('DocNormative_begDate_2').setAllowBlank(false);
				base_form.findField('DocNormative_Name_2').setAllowBlank(false);
			}
		};

		getWnd('swDocNormativeFileUploadWindow').show(params);
	},

	selectDocNormative: function(DocNormative_id, DocNormativeType_Code) {
		var base_form = this.FormPanel.getForm();

		var n = DocNormativeType_Code;

		var id_field   = base_form.findField('DocNormative_id_'+n);
		var num_field  = base_form.findField('DocNormative_Num_'+n);
		var date_field = base_form.findField('DocNormative_begDate_'+n);
		var name_field = base_form.findField('DocNormative_Name_'+n);
		var file_field = base_form.findField('DocNormative_File_'+n);

		var delFileBtn = Ext.getCmp('PBEW_Delete_DocNormative_File_'+n);
		if (delFileBtn) {
			delFileBtn.handler();
		}

		var record = num_field.getStore().getById(DocNormative_id);
		if (record) {
			id_field.setValue(record.get('DocNormative_id'));
			num_field.setValue(record.get('DocNormative_Num'));
			date_field.setValue(record.get('DocNormative_begDate'));
			name_field.setValue(record.get('DocNormative_Name'));

			date_field.disable();
			name_field.disable();

			if (!Ext.isEmpty(record.get('DocNormative_File'))) {
				file_field.setValue(record.get('DocNormative_File'));

				var arr = record.get('DocNormative_File').split('/');
				var filename = arr[arr.length-1];

				this.addFileToFilesPanel(filename, record.get('DocNormative_File'), 'DocNormative_File_'+n, false);
			}
		} else {
			id_field.setValue('');

			date_field.enable();
			name_field.enable();
		}
	},

	doSave: function() {
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

		var params = {
			PrepBlock_id: base_form.findField('PrepBlock_id').getValue(),
			Drug_id: base_form.findField('Drug_id').getValue(),
			PrepSeries_id: base_form.findField('PrepSeries_id').getFieldValue('PrepSeries_id'),
			PrepSeries_Ser: base_form.findField('PrepSeries_id').getFieldValue('PrepSeries_Ser') || base_form.findField('PrepSeries_id').getValue(),
			PrepBlockCause_id: base_form.findField('PrepBlockCause_id').getValue(),
			PrepBlock_begDate: base_form.findField('DocNormative_begDate_1').getValue(),
			PrepBlock_endDate: base_form.findField('DocNormative_begDate_2').getValue(),
			PrepBlock_Comment: base_form.findField('PrepBlock_Comment').getValue()
		};

		var values = getAllFormFieldValues(this.FormPanel);

		var DocNormativeList = [];

		for (var i=1; i<=2; i++) {
			if (!Ext.isEmpty(values['DocNormative_Num_'+i]) && !Ext.isEmpty(values['DocNormative_begDate_'+i])) {
				DocNormativeList.push({
					'DocNormative_id': values['DocNormative_id_'+i],
					'DocNormative_Num': values['DocNormative_Num_'+i],
					'DocNormative_begDate': values['DocNormative_begDate_'+i],
					'DocNormative_Name': values['DocNormative_Name_'+i],
					'DocNormative_File': values['DocNormative_File_'+i],
					'DocNormativeType_id': i
				});
			}
		}

		params.DocNormativeList = Ext.util.JSON.encode(DocNormativeList);

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		Ext.Ajax.request({
			params: params,
			url: '/?c=RlsDrug&m=savePrepBlock',
			success: function(response, options) {
				loadMask.hide();

				var responseObj = Ext.util.JSON.decode(response.responseText);

				if (responseObj.PrepBlock_id ){
					base_form.findField('PrepBlock_id').setValue(responseObj.PrepBlock_id);
					this.callback();
					this.hide();
				} else if (!Ext.isEmpty(responseObj.Error_Msg)) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function()
						{
							//this.hide();
						}.createDelegate(this),
						icon: Ext.Msg.ERROR,
						msg: responseObj.Error_Msg,
						title: 'Ошибка'
					});
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function()
						{
							//this.hide();
						}.createDelegate(this),
						icon: Ext.Msg.ERROR,
						msg: lang['proizoshla_oshibka_pojaluysta_povtorite_popyitku_pozje'],
						title: lang['oshibka']
					});
				}
			}.createDelegate(this),
			failure: function(response, options) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swPrepBlockEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.findById('PBEW_FilesPanel').removeAll();
		this.syncShadow();

		Ext.getCmp('PBEW_AddFileBtn').show();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		base_form.items.each(function(f){f.validate()});

		var prepseries_combo = base_form.findField('PrepSeries_id');
		var prepblockcause_combo = base_form.findField('PrepBlockCause_id');
		var drug_combo = base_form.findField('Drug_id');

		drug_combo.getStore().baseParams = {};
		prepseries_combo.getStore().baseParams = {};
		prepseries_combo.getStore().load();
		prepblockcause_combo.getStore().load();

		var store1 = base_form.findField('DocNormative_Num_1').getStore();
		store1.load({
			params: {DocNormativeType_id: 1},
			callback: function(){
				var records = getStoreRecords(store1);
				base_form.findField('DocNormative_begDate_1').getStore().loadData(records);
				base_form.findField('DocNormative_Name_1').getStore().loadData(records);
			}
		});

		var store2 = base_form.findField('DocNormative_Num_2').getStore();
		store2.load({
			params: {DocNormativeType_id: 2},
			callback: function(){
				var records = getStoreRecords(store2);
				base_form.findField('DocNormative_begDate_2').getStore().loadData(records);
				base_form.findField('DocNormative_Name_2').getStore().loadData(records);
			}
		});

		base_form.findField('DocNormative_Num_2').setAllowBlank(true);
		base_form.findField('DocNormative_begDate_2').setAllowBlank(true);
		base_form.findField('DocNormative_Name_2').setAllowBlank(true);

		switch(this.action) {
			case 'add':
				this.setTitle(lang['seriya_blokirovannogo_ls_dobavlenie']);
				this.enableEdit(true);
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['seriya_blokirovannogo_ls_redaktirovanie']);
					this.enableEdit(true);
				} else {
					this.setTitle(lang['seriya_blokirovannogo_ls_prosmotr']);
					this.enableEdit(false);
				}

				base_form.load({
					params: {
						PrepBlock_id: base_form.findField('PrepBlock_id').getValue()
					},
					url: '/?c=RlsDrug&m=loadPrepBlockForm',
					success: function() {
						drug_combo.getStore().load({
							params: {Drug_id: drug_combo.getValue()},
							callback: function() {
								drug_combo.setValue(drug_combo.getValue());
								prepseries_combo.getStore().baseParams.Drug_id = drug_combo.getValue();
								prepseries_combo.getStore().load({
									callback: function() {
										prepseries_combo.setValue(prepseries_combo.getValue());
									}
								});
							}
						});

						var doc_normative_id_1 = base_form.findField('DocNormative_id_1').getValue();
						var doc_normative_id_2 = base_form.findField('DocNormative_id_2').getValue();
						var doc_normative_File_1 = base_form.findField('DocNormative_File_1').getValue();
						var doc_normative_File_2 = base_form.findField('DocNormative_File_2').getValue();

						if (!Ext.isEmpty(doc_normative_File_1) && !Ext.isEmpty(doc_normative_File_2)) {
							Ext.getCmp('PBEW_AddFileBtn').hide();
						}
						if (!Ext.isEmpty(doc_normative_id_1)) {
							this.selectDocNormative(doc_normative_id_1, 1);
						}
						if (!Ext.isEmpty(doc_normative_id_2)) {
							this.selectDocNormative(doc_normative_id_2, 2);
						}
					}.createDelegate(this)
				});

				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'PBEW_FormPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 145,
			url: '/?c=RlsDrug&m=savePrepBlock',
			items: [{
				xtype: 'hidden',
				name: 'PrepBlock_id'
			}, {
				allowBlank: false,
				xtype: 'swdrugsimplecombo',
				fieldLabel: lang['torgovoe_naimenovanie'],
				hiddenName: 'Drug_id',
				width: 540,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var prep_series_combo = base_form.findField('PrepSeries_id');

						prep_series_combo.clearValue();
						prep_series_combo.getStore().removeAll();
						prep_series_combo.lastQuery = 'This query sample that is not will never appear';
						prep_series_combo.getStore().baseParams.Drug_id = newValue;
					}.createDelegate(this),
					'render': function() {
						this.getStore().proxy.conn.url = "/?c=RlsDrug&m=loadDrugList";
					}
				},
				loadingText: lang['idet_poisk'],
				onTrigger2Click: function() {
					if (this.disabled)
						return false;

					var combo = this;
					getWnd('swRlsDrugTorgSearchWindow').show({
						searchFull: true,
						onHide: function() {
							combo.focus(false);
						},
						onSelect: function(drugData) {
							combo.fireEvent('beforeselect', combo);

							combo.getStore().removeAll();
							combo.getStore().loadData([{
								Drug_id: drugData.Drug_id,
								Drug_Dose: drugData.Drug_Dose,
								DrugForm_Name: drugData.DrugForm_Name,
								Drug_Name: drugData.Drug_Name,
								Drug_Code: drugData.Drug_Code
							}], true);

							combo.setValue(drugData.Drug_id);
							var index = combo.getStore().findBy(function(rec) { return rec.get('Drug_id') == drugData.Drug_id; });

							if (index == -1)
							{
								return false;
							}

							var record = combo.getStore().getAt(index);

							if ( typeof record == 'object' ) {
								combo.fireEvent('select', combo, record, 0);
								combo.fireEvent('change', combo, record.get('Drug_id'));
							}

							getWnd('swRlsDrugTorgSearchWindow').hide();
						}
					});
				}
			}, {
				allowBlank: false,
				xtype: 'swprepseriescombo',
				hiddenName: 'PrepSeries_id',
				fieldLabel: lang['nomer_serii'],
				forceSelection: false,
				width: 540
			}, {
				allowBlank: false,
				xtype: 'swprepblockcausecombo',
				hiddenName: 'PrepBlockCause_id',
				fieldLabel: lang['osnovanie_blokirovki'],
				width: 540
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['dokument_o_priostanovlenii_realizatsii_lekarstvennogo_sredstva'],
				labelWidth: 135,
				items: [{
					xtype: 'hidden',
					name: 'DocNormative_id_1'
				}, {
					xtype: 'hidden',
					name: 'DocNormative_File_1'
				}, {
					allowBlank: false,
					xtype: 'swdocnormativecombo',
					fieldLabel: lang['№'],
					comboField: 'DocNormative_Num',
					hiddenName: 'DocNormative_Num_1',
					width: 540,
					listeners: {
						'select': function(combo, record, index) {
							var id = (record && record.get('DocNormative_id')) ? record.get('DocNormative_id') : null;
							this.selectDocNormative(id, 1);
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function(rec) { return rec.get('DocNormative_Num') == newValue; });
							if (Ext.isEmpty(newValue) || index < 0) {
								this.selectDocNormative(null, 1);
							}
						}.createDelegate(this)
					}
				}, {
					allowBlank: false,
					xtype: 'swdocnormativedatecombo',
					fieldLabel: lang['ot'],
					comboField: 'DocNormative_begDate',
					hiddenName: 'DocNormative_begDate_1',
					width: 540,
					listeners: {
						'select': function(combo, record, index) {
							var id = (record && record.get('DocNormative_id')) ? record.get('DocNormative_id') : null;
							this.selectDocNormative(id, 1);
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							if (Ext.isEmpty(newValue)) {
								this.selectDocNormative(null, 1);
							}
						}.createDelegate(this)
					}
				}, {
					allowBlank: false,
					xtype: 'swdocnormativecombo',
					fieldLabel: lang['naimenovanie'],
					comboField: 'DocNormative_Name',
					hiddenName: 'DocNormative_Name_1',
					width: 540,
					listeners: {
						'select': function(combo, record, index) {
							var id = (record && record.get('DocNormative_id')) ? record.get('DocNormative_id') : null;
							this.selectDocNormative(id, 1);
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							if (Ext.isEmpty(newValue)) {
								this.selectDocNormative(null, 1);
							}
						}.createDelegate(this)
					}
				}]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['dokument_o_vozobnovlenii_realizatsii_lekarstvennogo_sredstva'],
				labelWidth: 135,
				items: [{
					xtype: 'hidden',
					name: 'DocNormative_id_2'
				}, {
					xtype: 'hidden',
					name: 'DocNormative_File_2'
				}, {
					xtype: 'swdocnormativecombo',
					fieldLabel: lang['№'],
					comboField: 'DocNormative_Num',
					hiddenName: 'DocNormative_Num_2',
					width: 540,
					listeners: {
						'select': function(combo, record, index) {
							var id = (record && record.get('DocNormative_id')) ? record.get('DocNormative_id') : null;
							this.selectDocNormative(id, 2);
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function(rec) { return rec.get('DocNormative_Num') == newValue; });
							if (Ext.isEmpty(newValue) || (newValue != oldValue && index < 0)) {
								this.selectDocNormative(null, 2);
							}
						}.createDelegate(this)
					}
				}, {
					xtype: 'swdocnormativedatecombo',
					fieldLabel: lang['ot'],
					comboField: 'DocNormative_begDate',
					hiddenName: 'DocNormative_begDate_2',
					width: 540,
					listeners: {
						'select': function(combo, record, index) {
							var id = (record && record.get('DocNormative_id')) ? record.get('DocNormative_id') : null;
							this.selectDocNormative(id, 2);
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							if (Ext.isEmpty(newValue)) {
								this.selectDocNormative(null, 2);
							}
						}.createDelegate(this)
					}
				}, {
					xtype: 'swdocnormativecombo',
					fieldLabel: lang['naimenovanie'],
					comboField: 'DocNormative_Name',
					hiddenName: 'DocNormative_Name_2',
					width: 540,
					listeners: {
						'select': function(combo, record, index) {
							var id = (record && record.get('DocNormative_id')) ? record.get('DocNormative_id') : null;
							this.selectDocNormative(id, 2);
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							if (Ext.isEmpty(newValue)) {
								this.selectDocNormative(null, 2);
							}
						}.createDelegate(this)
					}
				}]
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['prikreplennyie_faylyi'],
				items: [{
					xtype: 'panel',
					fieldWidth: 135,
					id: 'PBEW_FilesPanel'
				}, {
					xtype: 'button',
					//style: 'margin-bottom: 10px;',
					id: 'PBEW_AddFileBtn',
					iconCls: 'add16',
					text: lang['dobavit_fayl'],
					handler: function(){
						this.openDocNormativeFileUploadWindow();
					}.createDelegate(this)
				}]
			}, {
				layout: 'form',
				labelWidth: 80,
				items: [{
					xtype: 'textfield',
					name: 'PrepBlock_Comment',
					fieldLabel: lang['primechanie'],
					anchor: '100%'
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'PrepBlock_id'},
				{name: 'Drug_id'},
				{name: 'PrepSeries_id'},
				{name: 'PrepBlockCause_id'},
				{name: 'DocNormative_id_1'},
				{name: 'DocNormative_File_1'},
				{name: 'DocNormative_Num_1'},
				{name: 'DocNormative_begDate_1'},
				{name: 'DocNormative_Name_1'},
				{name: 'DocNormative_id_2'},
				{name: 'DocNormative_File_2'},
				{name: 'DocNormative_Num_2'},
				{name: 'DocNormative_begDate_2'},
				{name: 'DocNormative_Name_2'},
				{name: 'PrepBlock_Comment'}
			])
		});

		Ext.apply(this,
		{
			buttons: [
			{
				handler: function () {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'PBEW_SaveButton',
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
			items: [this.FormPanel]
		});

		sw.Promed.swPrepBlockEditWindow.superclass.initComponent.apply(this, arguments);
	}
});