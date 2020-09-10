/**
 * swDocZayavEditWindow - окно редактирования заявок на медикаменты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Farmacy
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			26.12.2013
 */

/*NO PARSE JSON*/

sw.Promed.swDocZayavEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDocZayavEditWindow',
	width: 800,
	height: 600,
	layout: 'border',
	callback: Ext.emptyFn,
	maximized: true,
	title: lang['zayavka_na_medikamentyi'],

	action: 'view',
	Contragent_tid: null,
	DrugDocumentStatus_id: null,

	openRecordEditWindow: function(action, gridCmp) {
		var wnd = this;
		var grid = gridCmp.getGrid();
		var base_form = wnd.FormPanel.getForm();
		var params = new Object();

		if (action.inlist(['edit','view'])) {
			var record = grid.getSelectionModel().getSelected();
			if (record.get('DocumentUcStr') > 0) {
				params.formParams = {DocumentUcStr_id: record.get('DocumentUcStr_id')};
			} else {
				params.formParams = record.data;
			}
		}
		params.DrugDocumentClass_Code = base_form.findField('DrugDocumentClass_id').getFieldValue('DrugDocumentClass_Code');
		params.action = action;

		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.DocumentUcStrData != 'object' ) {
				return false;
			}

			var record = grid.getStore().getById(data.DocumentUcStrData.DocumentUcStr_id);

			if ( typeof record == 'object' ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.DocumentUcStrData.RecordStatus_Code = 2;
				}
				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for (var i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.DocumentUcStrData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('DocumentUcStr_id') ) {
					grid.getStore().removeAll();
				}

				data.DocumentUcStrData.DocumentUcStr_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.DocumentUcStrData ], true);
			}
		}.createDelegate(this);

		getWnd(gridCmp.editformclassname).show(params);
	},

	getAllowedStatusConfig: function()
	{
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var params = new Object();

		var document_uc_id = base_form.findField('DocumentUc_id').getValue();
		if (!Ext.isEmpty(document_uc_id)) {
			params.DocumentUc_id = document_uc_id;
		}

		Ext.Ajax.request({
			url: '/?c=Farmacy&m=getAllowedDocZayavStatusConfig',
			params: params,
			callback: function(options, success, response) {
				var DrugDocumentStatusCombo = base_form.findField('DrugDocumentStatus_id');

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					DrugDocumentStatusCombo.getStore().clearFilter();
					DrugDocumentStatusCombo.lastQuery = '';

					DrugDocumentStatusCombo.getStore().filterBy(function(record) {
						for (var i=0;i<response_obj.data.length;i++) {
							if (record.get('DrugDocumentStatus_Code') == response_obj.data[i].DrugDocumentStatus_Code) {
								return true;
							}
						}
						return false;
					});

					DrugDocumentStatusCombo.setAllowBlank(response_obj.allowBlank);

					if (response_obj.disabled || !wnd.checkRole('edit') || !wnd.action.inlist(['add','edit'])) {
						DrugDocumentStatusCombo.disable();
					} else {
						DrugDocumentStatusCombo.enable();
					}

					DrugDocumentStatusCombo.setValue(DrugDocumentStatusCombo.getValue());
				}
				else {
					DrugDocumentStatusCombo.clearValue();
					DrugDocumentStatusCombo.getStore().filterBy(function(){return false});
				}
			}
		});
	},

	openStatusHistoryWindow: function()
	{
		var base_form = this.FormPanel.getForm();
		var params = new Object();

		params.DocumentUc_id = base_form.findField().getValue();

		if (params.DocumentUc_id > 0) {
			getWnd('swDrugDocumentStatusHistoryWindow').show(params);
		}
	},

	doSave: function(options)
	{
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();
		var grid = wnd.DocumentUcStrGridPanel.getGrid();

		if ( !base_form.isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();

		params.Contragent_tid = base_form.findField('Contragent_tid').getValue();

		params.changeStatus = 0;
		params.DrugDocumentStatus_id = base_form.findField('DrugDocumentStatus_id').getValue();
		if (wnd.action == 'add' || params.DrugDocumentStatus_id != wnd.DrugDocumentStatus_id) {
			params.changeStatus = 1;
		}

		var DocumentUcStrData = [];

		grid.getStore().clearFilter();
		if ( grid.getStore().getCount() > 0 ) {
			DocumentUcStrData = getStoreRecords(grid.getStore(),{
				exceptionFields: ['Drug_Name','Person_Fio']
			});

			grid.getStore().filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
		}

		params.DocumentUcStrData = Ext.util.JSON.encode(DocumentUcStrData);

		wnd.getLoadMask("Подождите, идет сохранение...").show();

		base_form.submit({
			failure: function(result_form, action) {
				wnd.getLoadMask().hide()
			},
			params: params,
			success: function(result_form, action) {
				wnd.getLoadMask().hide();
				if (action.result)
				{
					wnd.callback();
					if (!options || (!options.copy && !options.callback)) {
						wnd.hide();
					} else {
						options.callback();
					}
				}
				else
				{
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},

	doCopy: function()
	{
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.DocumentUcStrGridPanel.getGrid();

		if ( this.action == 'add' ) {
			this.doSave({
				copy: true,
				callback: function() {
					base_form.findField('DocumentUc_Num').setValue(null);
					base_form.findField('DrugDocumentStatus_id').setValue(null);
					wnd.getAllowedStatusConfig();
				}
			});
		}
		else {
			this.action = 'add';

			base_form.findField('DocumentUc_id').setValue(null);
			base_form.findField('DocumentUc_Num').setValue(null);
			base_form.findField('DrugDocumentStatus_id').setValue(null);
			this.getAllowedStatusConfig();

			grid.getStore().clearFilter();
			grid.getStore().each(function(record){
				record.set('DocumentUcStr_id', -swGenTempId(grid.getStore()));
				if (record.get('RecordStatus_Code' == 3)) {
					grid.getStore().remove(record);
				} else {
					record.set('RecordStatus_Code',0);
				}
			});

			this.enableEditForm(true);
			this.setTitle(lang['zayavka_na_medikamentyi_dobavlenie']);
		}
	},

	deleteRecord: function(gridCmp)
	{
		if ( !this.action.inlist(['add','edit']) ) {
			return false;
		}

		var question = lang['udalit_pozitsiyu_zayavki'];
		var grid = gridCmp.getGrid();
		var idField = grid.getStore().idProperty;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected();

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
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},

	doSearch: function(clear)
	{
		var wnd = this;
		var base_form = wnd.DocumentUcStrFilterPanel.getForm();
		if (clear) {
			base_form.reset();
		}
		var params = base_form.getValues();
		params.DocumentUc_id = wnd.FormPanel.getForm().findField('DocumentUc_id').getValue();

		wnd.DocumentUcStrGridPanel.loadData({globalFilters: params});
	},

	enableEditForm: function(enable)
	{
		if (!this.checkRole('edit')) {
			enable = false;
		}

		this.enableEdit(enable);

		var edit_panel = this.FormPanel;
		var edit_form = edit_panel.getForm();
		var filter_panel = this.DocumentUcStrFilterPanel;
		var filter_form = filter_panel.getForm();

		if (enable) {
			if (this.Contragent_tid > 0) {
				edit_form.findField('Contragent_tid').disable();
			}
		} else {
			if (edit_form.findField('DocumentUc_id').getValue() > 0) {
				edit_panel.findById('DZEW_BtnStatusHistory').enable();
			}
			filter_panel.findById('DZEW_BtnSearch').enable();
			filter_panel.findById('DZEW_BtnClear').enable();
			filter_form.findField('Drug_id').enable();
			filter_form.findField('Person_id').enable();
		}

		this.DocumentUcStrGridPanel.setReadOnly(!enable);
	},

    createDrugListByEvnCourseTreatDrug: function() {
        var wnd = this;
        var msf = sw.Promed.MedStaffFactByUser.current;

        if (Ext.isEmpty(msf.LpuSection_id)) {
            Ext.Msg.alert(lang['oshibka'], lang['dlya_polzovatelya_ne_ukazano_otdelenie_formirovanie_spiska_otmeneno']);
        }

        Ext.Ajax.request({
            params: {
                LpuSection_id: msf.LpuSection_id
            },
            callback: function (options, success, response) {
                var grid = wnd.DocumentUcStrGridPanel.getGrid();
                var drug_list = Ext.util.JSON.decode(response.responseText);

                /*if (wnd.owner && 'refreshRecords' in wnd.owner) {
                    wnd.owner.refreshRecords(null,0);
                }*/

                var str_data = new Array();

                for(var i = 0; i < drug_list.length; i++) {
                    str_data.push({
                        DocumentUcStr_id: -swGenTempId(grid.getStore()),
                        RecordStatus_Code: 0,
                        DocumentUcStr_Count: 0,
                        DocumentUcStr_Price: 0,
                        DocumentUcStr_Sum: 0,
                        Person_id: null,
                        Person_Fio: null,
                        Okei_id: 120,
                        Drug_id: drug_list[i].Drug_id,
                        Drug_Name: drug_list[i].Drug_Name,
                        DocumentUcStr_PlanKolvo: drug_list[i].Drug_Kolvo*1,
                        DocumentUcStr_PlanPrice: 0,
                        DocumentUcStr_PlanSum: 0
                    });
                }

                if (str_data.length > 0) {
                    if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('DocumentUcStr_id') ) {
                        grid.getStore().removeAll();
                    }
                    grid.getStore().loadData(str_data, true);
                }

            },
            url:'/?c=Farmacy&m=loadDocumentUcStrListByEvnCourseTreatDrug'
        });
    },

	show: function()
	{
		sw.Promed.swDocZayavEditWindow.superclass.show.apply(this, arguments);

		var wnd = this;
		var base_form = wnd.FormPanel.getForm();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].Contragent_tid) {
			this.Contragent_tid = arguments[0].Contragent_tid;
		}

        this.DocumentUcStrGridPanel.addActions({
            name:'action_dze_actions',
            text:lang['deystviya'],
            menu: [{
                name: 'action_dze_create_list_ectd',
                text: lang['sformirovat_na_osnove_naznacheniy'],
                tooltip: lang['sformirovat_na_osnove_naznacheniy'],
                handler: function() {
                    wnd.createDrugListByEvnCourseTreatDrug();
                },
                iconCls: 'add16'
            }],
            iconCls: 'actions16'
        });

		base_form.reset();
		base_form.clearInvalid();

		if (arguments[0] && arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		base_form.findField('DrugDocumentClass_id').getStore().load({
			callback: function() { base_form.findField('DrugDocumentClass_id').setValue(1); }
		});
		base_form.findField('DrugDocumentStatus_id').getStore().load({params: {DrugDocumentStatus_id: 9}});

		wnd.doSearch(true);

		wnd.FormPanel.findById('DZEW_BtnStatusHistory').disable();

		wnd.getLoadMask(lang['zagruzka_dannyih_formyi']).show();
		switch(wnd.action) {
			case 'add':
				if (wnd.Contragent_tid) {
					base_form.findField('Contragent_tid').disable();
					base_form.findField('Contragent_tid').setValue(wnd.Contragent_tid);
					loadContragent(wnd, 'DZEW_Contragent_tid', null);
				}

				if (base_form.findField('DrugDocumentStatus_id').getValue() > 0) {
					wnd.DrugDocumentStatus_id = base_form.findField('DrugDocumentStatus_id').getValue();
				}

				/*base_form.findField('DrugDocumentStatus_id').getStore().load({
					params: {DrugDocumentStatus_id: 9},
					callback: function(){wnd.getAllowedStatusConfig();}
				});*/

				setCurrentDateTime({
					callback:Ext.emptyFn,
					dateField:base_form.findField('DocumentUc_setDate'),
					loadMask:false,
					setDate:true,
					windowId:this.id
				});

				wnd.getAllowedStatusConfig();

				wnd.enableEditForm(true);
				wnd.getLoadMask().hide();
				wnd.setTitle(lang['zayavka_na_medikamentyi_dobavlenie']);
			break;
			case 'edit':
			case 'view':
				if (wnd.action == 'view') {
					wnd.setTitle(lang['zayavka_na_medikamentyi_prosmotr']);
					wnd.enableEditForm(false);
				} else {
					wnd.setTitle(lang['zayavka_na_medikamentyi_redaktirovanie']);
					wnd.enableEditForm(true);
				}
				if (base_form.findField('DocumentUc_id').getValue() > 0) {
					wnd.FormPanel.findById('DZEW_BtnStatusHistory').enable();
				}
				base_form.load({
					failure:function () {
						wnd.getLoadMask().hide();
						wnd.hide();
					},
					params:{
						DocumentUc_id: base_form.findField('DocumentUc_id').getValue()
					},
					success: function (response) {
						if (wnd.Contragent_tid) {
							base_form.findField('Contragent_tid').disable();
						}
						if (base_form.findField('Contragent_tid').getValue()) {
							loadContragent(wnd, 'DZEW_Contragent_tid', null);
						}
						if (base_form.findField('Contragent_sid').getValue()) {
							loadContragent(wnd, 'DZEW_Contragent_sid', null);
						}
						if (base_form.findField('DrugDocumentStatus_id').getValue() > 0) {
							wnd.DrugDocumentStatus_id = base_form.findField('DrugDocumentStatus_id').getValue();
						}

						/*base_form.findField('DrugDocumentStatus_id').getStore().load({
							params: {DrugDocumentStatus_id: 9},
							callback: function(){wnd.getAllowedStatusConfig();}
						});*/
						wnd.getAllowedStatusConfig();

						wnd.getLoadMask().hide();
					},
					url:'/?c=Farmacy&m=edit&method=DocZayav'
				});
			break;
		}
	},

	initComponent: function()
	{
		var wnd = this;

		wnd.DocumentUcStrFilterPanel = new Ext.form.FormPanel({
			height: 60,
			bodyBorder: false,
			border: false,
			frame: true,
			labelAlign: 'right',
			id: 'DZEW_DocumentUcStrFilterPanel',
			region: 'north',
			title: lang['pozitsiya_zayavki_na_medikamentyi'],
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						name: 'Drug_id',
						fieldLabel: lang['tmts'],
						xtype: 'swdrugsimplecombo',
						width: 420
					}]
				}, {
					layout: 'form',
					items: [{
						editable: false,
						fieldLabel: lang['patsient'],
						hiddenName: 'Person_id',
						width: 420,
						xtype: 'swpersoncombo',
						onTrigger1Click: function() {
							if (this.disabled) return false;
							var ownerWindow = Ext.getCmp('PersonEditWindow');
							var combo = this;
							getWnd('swPersonSearchWindow').show({
								onSelect: function(personData) {
									if ( personData.Person_id > 0 )
									{
										combo.getStore().loadData([{
											Person_id: personData.Person_id,
											Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
										}]);
										combo.setValue(personData.Person_id);
										combo.collapse();
										combo.focus(true, 500);
										combo.fireEvent('change', combo);
									}
									getWnd('swPersonSearchWindow').hide();
								},
								onClose: function() {combo.focus(true, 500)}
							});
						},
						enableKeyEvents: true,
						listeners: {
							'change': function(combo) {
							},
							'keydown': function( inp, e ) {
								if ( e.F4 == e.getKey() )
								{
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
									if ( Ext.isIE )
									{
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}
									inp.onTrigger1Click();
									return false;
								}
							},
							'keyup': function(inp, e) {
								if ( e.F4 == e.getKey() )
								{
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
									if ( Ext.isIE )
									{
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}
									return false;
								}
							}
						}
					}]
				}, {
					layout: 'form',
					items: [{
						style: "padding-left: 20px",
						xtype: 'button',
						id: 'DZEW_BtnSearch',
						text: lang['poisk'],
						iconCls: 'search16',
						handler: function() {
							wnd.doSearch();
						}
					}]
				}, {
					layout: 'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'DZEW_BtnClear',
						text: lang['sbros'],
						iconCls: 'resetsearch16',
						handler: function() {
							wnd.doSearch(true);
						}
					}]
				}]
			}]
		});

		wnd.DocumentUcStrGridPanel = new sw.Promed.ViewFrame({
			id: 'DZEW_DocumentUcStrGrid',
			region: 'center',
			dataUrl: '/?c=Farmacy&m=loadDocumentUcStrView',
			editformclassname: 'swDocumentUcStrZayavEditWindow',
			paging: false,
			autoLoadData: false,

			stringfields:
				[
					{name: 'DocumentUcStr_id', header: 'ID', key: true},
					{name: 'RecordStatus_Code', type: 'int', hidden: true},
					{name: 'DocumentUcStr_Count', type: 'float', hidden: true},
					{name: 'DocumentUcStr_Price', type: 'money', hidden: true},
					{name: 'DocumentUcStr_Sum', type: 'money', hidden: true},
					{name: 'Person_id', type: 'int', hidden: true},
					{name: 'Person_Fio', type: 'int', hidden: true},
					{name: 'Okei_id', type: 'int', hidden: true},
					{name: 'Drug_id', type: 'int', hidden: true},
					{name: 'Drug_Name', header: lang['tmts'], type: 'string', id: 'autoexpand'},
					{name: 'DocumentUcStr_PlanKolvo', header: lang['kolichestvo_plan'], type: 'float', width: 180},
					{name: 'DocumentUcStr_PlanPrice', header: lang['tsena_plan'], type: 'money', width: 180},
					{name: 'DocumentUcStr_PlanSum', header: lang['summa_plan'], type: 'money', width: 180}
				],
			actions:
				[
					{name:'action_add', handler: function (){wnd.openRecordEditWindow('add',wnd.DocumentUcStrGridPanel);}},
					{name:'action_edit', handler: function (){wnd.openRecordEditWindow('edit',wnd.DocumentUcStrGridPanel);}},
					{name:'action_view', handler: function (){wnd.openRecordEditWindow('view',wnd.DocumentUcStrGridPanel);}},
					{name:'action_delete', handler: function (){wnd.deleteRecord(wnd.DocumentUcStrGridPanel);}},
					{name:'action_refresh', disabled: true, hidden: true}
				]
		});

		this.DocumentUcStrPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',

			items: [ wnd.DocumentUcStrFilterPanel, wnd.DocumentUcStrGridPanel ]
		});

		wnd.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 20px 0',
			border: false,
			frame: true,
			height: 200,
			labelAlign: 'right',
			labelWidth: 100,
			id: 'DZEW_FormPanel',
			region: 'north',

			items: [{
				name: 'DocumentUc_id',
				xtype: 'hidden'
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						xtype: 'swdrugdocumentstatuscombo',
						hiddenName: 'DrugDocumentStatus_id',
						fieldLabel: lang['status'],
						disabled: true,
						width: 160
					}]
				}, {
					layout: 'form',
					items: [{
						style: "padding-left: 10px",
						xtype: 'button',
						id: 'DZEW_BtnStatusHistory',
						text: lang['istoriya_statusov'],
						handler: function() {
							wnd.openStatusHistoryWindow();
						}
					}]
				}]
			}, {
				allowBlank: false,
				xtype: 'swdrugdocumentclasscombo',
				hiddenName: 'DrugDocumentClass_id',
				fieldLabel: lang['vid_zayavki'],
				width: 240
			}, {
				allowBlank: false,
				width: 500,
				fieldLabel: lang['zakazchik'],
				xtype: 'swcontragentcombo',
				id: 'DZEW_Contragent_tid',
				hiddenName: 'Contragent_tid'
			}, {
				allowBlank: false,
				width: 500,
				fieldLabel: lang['ispolnitel'],
				xtype: 'swcontragentcombo',
				id: 'DZEW_Contragent_sid',
				hiddenName: 'Contragent_sid'
			}, {
				layout: 'column',
				items: [{
					layout: 'form',
					items: [{
						allowBlank: false,
						width: 140,
						fieldLabel: lang['nomer'],
						xtype: 'textfield',
						name: 'DocumentUc_Num'
					}, {
						width: 140,
						name: 'DocumentUc_planDate',
						fieldLabel: lang['data_plan'],
						xtype: 'swdatefield'
					}, {
						width: 140,
						name: 'DocumentUc_begDate',
						fieldLabel: lang['period_ispolzovaniya_s'],
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						xtype: 'swdatefield'
					}]
				}, {
					layout: 'form',
					labelWidth: 120,
					items: [{
						allowBlank: false,
						width: 140,
						name: 'DocumentUc_setDate',
						fieldLabel: lang['data_zayavki'],
						xtype: 'swdatefield'
					}, {
						width: 140,
						name: 'DocumentUc_didDate',
						fieldLabel: lang['data_fakt'],
						xtype: 'swdatefield'
					}, {
						width: 140,
						name: 'DocumentUc_endDate',
						fieldLabel: lang['do'],
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						xtype: 'swdatefield'
					}]
				}]
			}
			],
			reader: new Ext.data.JsonReader(
				{
					success: function()
					{
						//
					}
				},
				[
					{ name: 'DocumentUc_id' },
					{ name: 'DrugDocumentStatus_id' },
					{ name: 'DrugDocumentClass_id' },
					{ name: 'Contragent_tid' },
					{ name: 'Contragent_sid' },
					{ name: 'DocumentUc_Num' },
					{ name: 'DocumentUc_planDate' },
					{ name: 'DocumentUc_setDate' },
					{ name: 'DocumentUc_didDate' },
					{ name: 'DocumentUc_begDate' },
					{ name: 'DocumentUc_endDate' }
				]
			),
			url: '/?c=Farmacy&m=save&method=DocZayav'
		});

		Ext.apply(this, {
			items: [
				wnd.FormPanel,
				wnd.DocumentUcStrPanel
			],
			buttons: [
				{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'DZEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					handler: function() {
						this.doCopy();
					}.createDelegate(this),
					iconCls: 'copy16',
					id: 'DZEW_CopyButton',
					text: lang['kopiya']
				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'DZEW_CancelButton',
					text: lang['otmena']
				}]
		});

		sw.Promed.swDocZayavEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
