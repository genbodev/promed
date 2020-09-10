/**
 * swFinDocumentEditWindow - окно редактирования счета для реестра рецептов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Salakhov R.
 * @version      06.2013
 * @comment
 */
sw.Promed.swFinDocumentEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['informatsiya_o_schetah_i_platejnyih_porucheniyah'],
	layout: 'border',
	id: 'FinDocumentEditWindow',
	modal: true,
	shim: false,
	width: 500,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
    setOrgValueById: function(combo, id) {
        var wnd = this;
        if (id > 0) {
            Ext.Ajax.request({
                url: C_ORG_LIST,
                params: {
                    Org_id: id
                },
                success: function(response){
                    var result = Ext.util.JSON.decode(response.responseText);
                    if (result[0] && result[0].Org_id && result[0].Org_Name) {
                        wnd.setOrgValueByData(combo, {
                            Org_id: result[0].Org_id,
                            Org_Name: result[0].Org_Name
                        });
                    } else {
                        combo.reset();
                    }
                }
            });
        }
    },
    setOrgValueByData: function(combo, data) {
        combo.getStore().removeAll();
        combo.getStore().loadData([{
            Org_id: data.Org_id,
            Org_Name: data.Org_Name
        }]);
        combo.setValue(data[combo.valueField]);

        var index = combo.getStore().findBy(function(rec) { return rec.get(combo.valueField) == data[combo.valueField]; });

        if (index == -1) {
            return false;
        }

        var record = combo.getStore().getAt(index);
        combo.fireEvent('select', combo, record, 0)
    },
    generateFinDocumentNumber: function() {
        var wnd = this;

        wnd.getLoadMask().show();
        Ext.Ajax.request({
           callback: function(opt, success, resp) {
                wnd.getLoadMask().hide();
                var response_obj = Ext.util.JSON.decode(resp.responseText);
                if (response_obj && !Ext.isEmpty(response_obj.FinDocument_Number)) {
                    var new_num = response_obj.FinDocument_Number;
                    var field = wnd.form.findField('FinDocument_Number');
                    field.setValue(new_num);
                    field.fireEvent('change', field, new_num);
                }
            },
            url: '/?c=FinDocument&m=generateFinDocumentNumber'
        });
    },
    setDefaultValues: function() {
        if (getGlobalOptions().org_id > 0) {
            this.setOrgValueById(this.form.findField('Org_id'), getGlobalOptions().org_id);
        }
        this.generateFinDocumentNumber();
        this.form.findField('FinDocument_Date').setValue(new Date());
        if (getGlobalOptions().minzdrav_org_id > 0) {
            this.setOrgValueById(this.form.findField('Org_mid'), getGlobalOptions().minzdrav_org_id);
        }
    },
	doDelete: function() {
		var wnd = this;

		if (wnd.FinDocument_id <= 0)
			return false;

		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_hotite_udalit_schet'],
			title: lang['podtverjdenie'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId) {
					Ext.Ajax.request({
						url: '/?c=FinDocument&m=delete',
						params: {
							id: wnd.FinDocument_id
						},
						failure: function(response, options) {
							Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
						},
						success: function(response, action) {
							if (response.responseText) {
								var answer = Ext.util.JSON.decode(response.responseText);
								if (!answer.success) {
									if (answer.Error_Code && !answer.Error_Msg) {
										Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
									} else {
										if (!answer.Error_Msg) {
											Ext.Msg.alert(lang['oshibka'], lang['udalenie_nevozmojno']);
										}
									}
								} else {
									var params = {
										FinDocument_id: null,
										FinDocument_Number: null,
										FinDocument_Date: null,
										FinDocument_Sum: 0,
										FinDocumentSpec_Sum: 0,
										RefreshGrid: true
									}
									wnd.callback(wnd.owner, params);
									wnd.hide();
								}
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_otsutstvuet_otvet_servera']);
							}
						}
					});
				}
			}
		});
	},
	doSave:  function() {
		var wnd = this;
		if (!this.form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('FinDocumentEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		//сохраняем данные из грида с платежными поручениями
		wnd.form.findField('DocListJSON').setValue(wnd.DocGrid.getJSONChangedData());

		this.submit();
		return true;
	},
	submit: function() {
		var wnd = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {};
		params.action = wnd.action;
		this.form.submit({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();
				if (action.result) {
					if (action.result.Error_Code) {
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) {
				loadMask.hide();
				var params = new Object();
				params.FinDocument_id = action.result.FinDocument_id;
				params.FinDocumentSpec_Sum = wnd.DocGrid.getSum();

				if (action.result.RegistryStatus_id) {
                    params.RegistryStatus_id = action.result.RegistryStatus_id;
                }
				if (action.result.RegistryStatus_Code) {
                    params.RegistryStatus_Code = action.result.RegistryStatus_Code;
                }
				if (action.result.RegistryStatus_Name) {
                    params.RegistryStatus_Name = action.result.RegistryStatus_Name;
                }
				if (action.result.RegistryStatus_isChanged) {
                    params.RegistryStatus_isChanged = action.result.RegistryStatus_isChanged;
                }

				wnd.callback(wnd.owner, params);
				wnd.hide();
			}
		});
	},
	show: function() {
		var wnd = this;
		sw.Promed.swFinDocumentEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.FinDocument_id = null;
		this.RegistryLLO_id = null;
        this.ARMType = null;
        this.HideDocGrid = false;

		if ( !arguments[0] || !arguments[0].RegistryLLO_id ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
			return false;
		} else {
			this.RegistryLLO_id = arguments[0].RegistryLLO_id;
		}
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		} else {
			this.action = arguments[0].FinDocument_id && arguments[0].FinDocument_id > 0 ? 'edit' : 'add';
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].FinDocument_id ) {
			this.FinDocument_id = arguments[0].FinDocument_id;
		}
        if ( arguments[0] && !Ext.isEmpty(arguments[0].ARMType) ) {
            this.ARMType = arguments[0].ARMType;
        }
        if ( arguments[0] && arguments[0].HideDocGrid ) {
            this.HideDocGrid = arguments[0].HideDocGrid;
        }

		this.form.reset();
        this.DocGrid.removeAll();
        this.buttons[1].hide(); //скрываем кнопку удаления

        this.form.findField('Org_id').getStore().proxy.conn.url = C_ORG_LIST;
        this.form.findField('Org_mid').getStore().proxy.conn.url = '/?c=FinDocument&m=loadOrgMidCombo';
        this.form.findField('UslugaComplex_id').setUslugaCategoryList(['llo']);

        if (this.HideDocGrid) {
            this.DocGrid.hide();
            this.setHeight(237);
        } else {
            this.DocGrid.show();
            this.setHeight(537);
            this.doLayout();
        }

        this.setTitle("Информация о счетах и платежных поручениях");

		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
		loadMask.show();
		switch (this.action) {
			case 'add':
                wnd.setTitle(wnd.title + ": Добавление");
                wnd.form.setValues(arguments[0]);
				wnd.setDefaultValues();
				loadMask.hide();
				break;
			case 'edit':
			case 'view':
                wnd.setTitle(wnd.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
				wnd.DocGrid.loadData({
					globalFilters: {
						FinDocumentType_id: 2, //Платежные поручения
						RegistryLLO_id: wnd.RegistryLLO_id
					}
				});
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						wnd.hide();
					},
					params:{
						FinDocument_id: wnd.FinDocument_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) {
							return false;
						}

						wnd.form.setValues(result[0]);
                        wnd.setOrgValueById(wnd.form.findField('Org_id'), result[0].Org_id);
                        wnd.setOrgValueById(wnd.form.findField('Org_mid'), result[0].Org_mid);

                        if (wnd.ARMType == 'merch') {
                            wnd.buttons[1].show(); //показываем кнопку удаления
                        }
						loadMask.hide();
						return true;
					},
					url:'/?c=FinDocument&m=load'
				});
				break;
		}
		return true;
	},
	initComponent: function() {
		var wnd = this;

		wnd.DocGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { wnd.DocGrid.editGrid('add') }},
				{name: 'action_edit', handler: function() { wnd.DocGrid.editGrid('edit') }},
				{name: 'action_view', hidden: true, handler: function() { wnd.DocGrid.editGrid('view') }},
				{name: 'action_delete', handler: function() { wnd.DocGrid.deleteRecord() }},
				{name: 'action_print', hidden: true},
				{name: 'action_refresh', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			schema: 'dbo',
			obj_isEvn: false,
			border: true,
			dataUrl: '/?c=FinDocument&m=loadList',
			height: 180,
			region: 'center',
			object: 'FinDocument',
			editformclassname: 'swFinDocumentSpecEditWindow',
			id: 'FinDocumentGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'FinDocument_id', type: 'int', header: 'ID', key: true},
				{name: 'FinDocument_Number', type: 'string', header: lang['№'], width: 120},
				{name: 'FinDocument_Date', type: 'string', header: lang['data'], width: 120},
				{name: 'FinDocument_Sum', type: 'money', align: 'right', header: lang['summa'], id: 'autoexpand'},
				{name: 'state', type: 'string', header: 'state', hidden: true}
			],
			title: lang['platejnyie_porucheniya'],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
				if (record.get('FinDocument_id') > 0 && !this.readOnly) {
					this.ViewActions.action_edit.setDisabled(false);
					this.ViewActions.action_view.setDisabled(false);
					this.ViewActions.action_delete.setDisabled(false);
				} else {
					this.ViewActions.action_edit.setDisabled(true);
					this.ViewActions.action_view.setDisabled(true);
					this.ViewActions.action_delete.setDisabled(true);
				}
			},
			editGrid: function (action) {
				if (action == null)	action = 'add';

				var view_frame = this;
				var store = view_frame.getGrid().getStore();

				if (action == 'add') {
					var record_count = store.getCount();
					if ( record_count == 1 && !store.getAt(0).get('FinDocument_id') ) {
						view_frame.removeAll({ addEmptyRecord: false });
						record_count = 0;
					}

					var params = new Object();
					getWnd(view_frame.editformclassname).show({
						action: action,
						params: params,
						callback: function(data) {
							if ( record_count == 1 && !store.getAt(0).get('FinDocument_id') ) {
								view_frame.removeAll({ addEmptyRecord: false });
							}
							var record = new Ext.data.Record.create(view_frame.jsonData['store']);
							view_frame.clearFilter();
							data.FinDocument_id = Math.floor(Math.random()*10000); //генерируем временный идентификатор
							data.state = 'add';
							view_frame.getGrid().getStore().insert(record_count, new record(data));
							view_frame.setFilter();
						}
					});
				}
				if (action == 'edit' || action == 'view') {
					var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
					if (selected_record.get('FinDocument_id') > 0) {
						var params = selected_record.data;

						getWnd(view_frame.editformclassname).show({
							action: action,
							params: params,
							callback: function(data) {
								view_frame.clearFilter();
								for(var key in data) {
                                    selected_record.set(key, data[key]);
                                }
								if (selected_record.get('state') != 'add') {
                                    selected_record.set('state', 'edit');
                                }
                                selected_record.commit();
								view_frame.setFilter();
							}
						});
					}
				}
			},
			deleteRecord: function(){
				var view_frame = this;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
				if (selected_record.get('state') == 'add') {
					view_frame.getGrid().getStore().remove(selected_record);
				} else {
					selected_record.set('state', 'delete');
					selected_record.commit();
					view_frame.setFilter();
				}
			},
			getChangedData: function(){ //возвращает новые и измненные показатели
				var data = new Array();
				this.clearFilter();
				this.getGrid().getStore().each(function(record) {
					if (record.data.state == 'add' || record.data.state == 'edit' ||  record.data.state == 'delete')
						data.push(record.data);
				});
				this.setFilter();
				return data;
			},
			getJSONChangedData: function(){ //возвращает новые и измненные записи в виде закодированной JSON строки
				var dataObj = this.getChangedData();
				return dataObj.length > 0 ? Ext.util.JSON.encode(dataObj) : "";
			},
			clearFilter: function() { //очищаем фильтры (необходимо делать всегда перед редактированием store)
				this.getGrid().getStore().clearFilter();
			},
			setFilter: function() { //скрывает удаленные записи
				this.getGrid().getStore().filterBy(function(record){
					return (record.get('state') != 'delete');
				});
			},
			getSum: function() {
				var sum = 0;
				this.getGrid().getStore().each(function(record) {
					if (record.get('state') != 'delete' && record.get('FinDocument_Sum') > 0)
						sum += record.get('FinDocument_Sum')*1;
				});
				return sum;
			}
		});

		var form = new Ext.form.FormPanel({
            id: 'FinDocumentEditForm',
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,
			frame: true,
			region: 'north',
			height: 170,
			labelAlign: 'right',
            url:'/?c=FinDocument&m=save',
			items: [{
                name: 'FinDocument_id',
                xtype: 'hidden'
            }, {
                name: 'RegistryLLO_id',
                xtype: 'hidden'
            }, {
                name: 'FinDocumentType_id',
                xtype: 'hidden',
                value: 1 //Счет
            }, {
                name: 'DocListJSON',
                xtype: 'hidden'
            }, {
                xtype: 'sworgcomboex',
                fieldLabel : lang['organizatsiya'],
                tabIndex: wnd.firstTabIndex + 10,
                hiddenName: 'Org_id',
                id: 'fde_Org_id',
                width: 350,
                editable: true,
                allowBlank: true,
                tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
                emptyText: lang['vvedite_chast_nazvaniya'],
                onTriggerClick: function() {
                    if (this.disabled) {
                        return false;
                    }
                    var combo = this;

                    if (!this.formList) {
                        this.formList = new sw.Promed.swListSearchWindow({
                            title: lang['poisk_organizatsii'],
                            id: 'OrgSearch_' + this.id,
                            object: 'Org',
                            prefix: 'lsswdse1',
                            editformclassname: 'swOrgEditWindow',
                            stringfields: [
                                {name: 'Org_id',    type:'int'},
                                {name: 'Org_Name',  type:'string'}
                            ],
                            dataUrl: C_ORG_LIST
                        });
                    }
                    this.formList.show({
                        params: this.getStore().baseParams,
                        onSelect: function(data) {
                            wnd.setOrgValueByData(combo, data);
                        }
                    });
                }
            }, {
                fieldLabel: lang['№'],
                name: 'FinDocument_Number',
                allowBlank: false,
                maxLength: 50,
                xtype: 'textfield',
                width: 350
            }, {
                fieldLabel: lang['data'],
                name: 'FinDocument_Date',
                allowBlank: false,
                xtype: 'swdatefield'
            }, {
                fieldLabel: lang['usluga'],
                name: 'UslugaComplex_id',
                allowBlank: true,
                xtype: 'swuslugacomplexallcombo',
                width: 350,
                listWidth: 600
            }, {
                xtype: 'sworgcomboex',
                fieldLabel : lang['platelschik'],
                tabIndex: wnd.firstTabIndex + 10,
                hiddenName: 'Org_mid',
                id: 'fde_Org_mid',
                width: 350,
                editable: true,
                allowBlank: true,
                tpl: '<tpl for="."><div class="x-combo-list-item">{Org_Name}</div></tpl>',
                emptyText: lang['vvedite_chast_nazvaniya'],
                onTriggerClick: function() {
                    if (this.disabled) {
                        return false;
                    }
                    var combo = this;

                    if (!this.formList) {
                        this.formList = new sw.Promed.swListSearchWindow({
                            title: lang['poisk_organizatsii'],
                            id: 'OrgSearch_' + this.id,
                            object: 'Org',
                            prefix: 'lsswdse1',
                            editformclassname: 'swOrgEditWindow',
                            stringfields: [
                                {name: 'Org_id',    type:'int'},
                                {name: 'Org_Name',  type:'string'}
                            ],
                            dataUrl: '/?c=FinDocument&m=loadOrgMidCombo'
                        });
                    }
                    this.formList.show({
                        params: this.getStore().baseParams,
                        onSelect: function(data) {
                            wnd.setOrgValueByData(combo, data);
                        }
                    });
                }
            }, {
                fieldLabel: lang['summa'],
                name: 'FinDocument_Sum',
                width: 100,
                allowDecimals: true,
                allowNegative: false,
                allowBlank: false,
                xtype:'numberfield'
            }],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'FinDocument_id'},
				{name: 'RegistryLLO_id'},
				{name: 'FinDocumentType_id'},
				{name: 'FinDocument_Number'},
				{name: 'FinDocument_Date'},
				{name: 'FinDocument_Sum'}
			]),
			url: '/?c=FinDocument&m=save'
		});
		Ext.apply(this, {
			layout: 'border',
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
			{
				handler: function() {
					this.ownerCt.doDelete();
				},
				iconCls: 'delete16',
				text: lang['udalit_schet']
			},
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function()
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form,wnd.DocGrid]
		});
		sw.Promed.swFinDocumentEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}
});