/**
* swDrugRequestEditForm - форма ввода заявки.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Andrew Markoff
* @version      09.2009
* @comment      
*
*
* @input data: 
               
               
*/
/*NO PARSE JSON*/
sw.Promed.swDrugRequestEditForm = Ext.extend(sw.Promed.BaseForm,
{
	title:langs('Заявка врача на лекарственные средства'),
	layout: 'border',
	id: 'DrugRequestEditForm',
	maximized: true,
	maximizable: false,
	shim: false,
	buttons:
	[
		{
			text: BTN_FRMSAVE,
			id: 'dreButtonSave',
			tabIndex: 4131,
			tooltip: lang['sohranit'],
			iconCls: 'save16',
			handler: function()
			{
				this.ownerCt.DrugRequestSave();
				this.ownerCt.returnFunc(this.ownerCt.owner, 1);
				this.ownerCt.hide();
			}
		},
		{
			text: '-'
		},
		{
			text: BTN_FRMHELP,
			tabIndex: 4132,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text: BTN_FRMCLOSE,
			id: 'dreButtonCancel',
			tabIndex: 4133,
			tooltip: lang['zakryit'],
			iconCls: 'cancel16',
			handler: function()
			{
				this.ownerCt.hide();
				this.ownerCt.returnFunc(this.ownerCt.owner, -1);
			}
		}
	],
	returnFunc: function(owner) {},
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
    getDrugRequestTypeId: function() { //вытащил эту конструкцию из onrowselect грида с пациентами
        var win = this;
        var DRType_id = -1;
        var record = this.PacientPanel.getGrid().getSelectionModel().getSelected();

        if (record && record.get('Person_id') > 0) {
            var OtkNextYear = (record.get('Person_IsRefuseCurr') == 'true') ? true : false;

            // Федеральная льгота
            if ((record.get('Person_IsFedLgotCurr')=='true') && (record.get('Person_IsRegLgotCurr')!='true') && (record.get('Person_IsRegLgotCurr')!='gray') && (!OtkNextYear)) {
                if (getGlobalOptions().isMinZdrav) {
                    DRType_id = 0;
                } else {
                    DRType_id = 1;
                }
            }
            else if ((record.get('Person_IsFedLgotCurr')!='true') && ((record.get('Person_IsRegLgotCurr')=='true') || (record.get('Person_IsRegLgotCurr')=='gray')))
            {
                if (OtkNextYear) {
                    // Если отказник , то никаких медикаментов
                    DRType_id = -1;
                } else {
                    DRType_id = 2;
                }
            }
            else if ((record.get('Person_IsFedLgotCurr')=='true') && ((record.get('Person_IsRegLgotCurr')=='true') || (record.get('Person_IsRegLgotCurr')=='gray')))
            {
                if (OtkNextYear) {
                    // Если отказник , то никаких медикаментов
                    DRType_id = -1;
                } else {
                    if (!getGlobalOptions().isMinZdrav) {
                        DRType_id = 1;
                    } else {
                        DRType_id = 0;
                    }
                }
            } else {
                if ((win.action=='view') || (win.findById('dreDrugRequestStatus_id').getValue()!=3)) {
                    DRType_id = -1;
                }
            }
        }

        return DRType_id;
    },
    excludeDrugRequestPerson: function() {
        var wnd = this;
        var selection_model = wnd.PacientPanel.getGrid().getSelectionModel();
        var selected_record = selection_model.getSelected();
        var DrugRequest_id = wnd.DrugRequest_id || wnd.findById('dreDrugRequest_id').getValue();
        var DrugRequestPerson_id = selected_record.get('DrugRequestPerson_id');

        if (Ext.isEmpty(DrugRequest_id) || Ext.isEmpty(DrugRequestPerson_id)) {
            return false;
        }

        sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            scope : wnd,
            fn: function(buttonId) {
                if (buttonId == 'yes') {
                    Ext.Ajax.request({
                        url: '/?c=DrugRequest&m=excludeDrugRequestPerson',
                        params: {
                            DrugRequest_id: DrugRequest_id,
                            DrugRequestPerson_id: DrugRequestPerson_id
                        },
                        callback: function(options, success, response) {
                            if (success) {
                                var result = Ext.util.JSON.decode(response.responseText);
                                if (result.Error_Msg) {
                                    sw.swMsg.alert('Ошибка', result.Error_Msg);
                                } else {
                                    wnd.DrugPacientPanel.getGrid().getStore().removeAll();
                                    wnd.DrugReservePanel.refreshRecords(null,0);
                                    wnd.PacientPanel.refreshRecords(null,0);
                                }
                            } else {
                                sw.swMsg.alert('Ошибка', 'При обработке данных произошла ошибка.');
                                return false;
                            }
                        }
                    });
                }
            },
            icon: Ext.Msg.QUESTION,
            msg: 'Медикаменты из заявки пациента будут перенесены в резерв врача, а пациент будет исключен из заявки. Выполнить действие?',
            title: 'Внимание'
        });
    },
    moveDrugRequestRow: function(data) {
        if (!Ext.isEmpty(data.DrugRequestRow_id)) {
            Ext.Ajax.request({
                url: '/?c=DrugRequest&m=moveDrugRequestRow',
                params: {
                    DrugRequestRow_id: data.DrugRequestRow_id,
                    Person_id: !Ext.isEmpty(data.Person_id) ? data.Person_id : null,
                    DrugRequestRow_Kolvo: !Ext.isEmpty(data.DrugRequestRow_Kolvo) ? data.DrugRequestRow_Kolvo : null
                },
                callback: function(options, success, response) {
                    if (success) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (result.Error_Msg) {
                            sw.swMsg.alert('Ошибка', result.Error_Msg);
                        } else if (data.callback && typeof data.callback == 'function') {
                            data.callback(result);
                        }
                    } else {
                        sw.swMsg.alert('Ошибка', 'При обработке данных произошла ошибка.');
                        return false;
                    }
                }
            });
        }
    },
	loadSprData: function()
	{
		frm = this;
		frm.findById('dreDrugRequestPeriod_id').getStore().reload();
		
		frm.findById('dreLpuUnit_id').getStore().load(
		{
			params:
			{
				Object: 'LpuUnit',
				LpuUnit_id: '',
				Lpu_id: frm.Lpu_id || getGlobalOptions().lpu_id,
				LpuUnit_Name: ''
			},
			callback: function()
			{
				if (frm.findById('dreLpuSection_id').getValue()>0)
				{
					//form.findById('dreLpuSection_id').getValue();
					frm.findById('dreLpuSection_id').getStore().load(
					{
						params:
						{
							Object: 'LpuSection',
							LpuSection_id: '',
							Lpu_id: frm.Lpu_id || getGlobalOptions().lpu_id,
							LpuUnit_id: frm.findById('dreLpuUnit_id').getValue(),
							LpuSection_Name: '',
                            LpuSection_maxSetDate: null,
                            LpuSection_minDisDate: null
						},
						callback: function()
						{
							frm.findById('dreLpuSection_id').setValue(frm.findById('dreLpuSection_id').getValue());
							// Заполним LpuUnit из LpuSection
							{
								var combo = frm.findById('dreLpuSection_id');
								idx = combo.getStore().indexOfId(combo.getValue());
								if (idx<0)
									idx = combo.getStore().findBy(function(rec) { return rec.get('LpuSection_id') == combo.getValue(); });
								if (idx<0)
									return;
								var row = combo.getStore().getAt(idx);
								frm.findById('dreLpuUnit_id').setValue(row.data.LpuUnit_id); 
							}
							frm.findById('dreMedPersonal_id').getStore().load(
							{
								params:
								{
									LpuSection_id: frm.findById('dreLpuSection_id').getValue(),
									Lpu_id: frm.Lpu_id || getGlobalOptions().lpu_id,
									IsDlo: (!getGlobalOptions().isOnko && !getGlobalOptions().isRA)?1:0,
									checkDloDate: true,
                                    begDate: null,
                                    endDate: null
								},
								callback: function()
								{
									frm.findById('dreMedPersonal_id').setValue(frm.findById('dreMedPersonal_id').getValue());
								}
							});
						}
					});
					
				}
			}
		});
	},
	setEnabled: function() {
		var groups = getGlobalOptions().groups;
        var status = this.findById('dreDrugRequestStatus_id').getValue();

		if (this.action=='view' || this.action=='edit')
		{
			this.findById('dreDrugRequestPeriod_id').disable();
			this.findById('dreLpuUnit_id').disable();
			this.findById('dreLpuSection_id').disable();
			this.findById('dreMedPersonal_id').disable();
			this.findById('dreLpuUnitPanel').setVisible(false);
			this.findById('dreYoungChildCountPanel').setVisible(true);
			this.Actions.action_DrugRequestPrint.setDisabled(false);
			this.Actions.action_DrugRequestSetStatus.setDisabled((this.action == 'view') || (status == 3 || status == 6) || (status == 2 && !isUserGroup(['SuperAdmin', 'LpuAdmin', 'ChiefLLO'])));
			this.Actions.action_DrugRequestReallocate.setDisabled((this.action=='view') || (status != 2 && status != 3));
			this.PersonTab.unhideTabStripItem('tab_reserve');
		}
		else 
		{
			this.findById('dreDrugRequestPeriod_id').enable();
			this.findById('dreLpuUnit_id').enable();
			this.findById('dreLpuSection_id').enable();
			this.findById('dreMedPersonal_id').enable();
			this.Actions.action_DrugRequestPrint.setDisabled(true);
			this.Actions.action_DrugRequestSetStatus.setDisabled(true);
			this.Actions.action_DrugRequestReallocate.setDisabled(true);
		}
		
		if ((this.action=='view') || (this.findById('dreDrugRequestStatus_id').getValue().inlist([2,3,6])))
		{
			this.buttons[0].disable();
			this.findById('dreDrugRequest_YoungChildCount').disable();
			// В зависимости от статуса, при статусе равном трем доступность редактирования
			if ((this.findById('dreDrugRequestStatus_id').getValue()==3) || (getGlobalOptions().isMinZdrav))
			{
				this.DrugPacientPanel.setReadOnly(false);
				this.DrugReservePanel.setReadOnly(false);
				this.PacientPanel.setReadOnly(false);
				this.EditPanel.findById('dreDrugProtoMnn_id').enable();
				this.EditPanel.findById('dreDrugRequestRow_Kolvo').enable();
				this.EditPanel.findById('dreDrugRequestType_id').enable();
				this.EditPanel.findById('dreButtonAdd').enable();
				this.EditPanel.findById('dreIsDrug').setDisabled(!getGlobalOptions().isMinZdrav);
				this.EditReservePanel.findById('drerDrugProtoMnn_id').enable();
				this.EditReservePanel.findById('drerDrugRequestRow_Kolvo').enable();
				this.EditReservePanel.findById('drerDrugRequestType_id').enable();
				this.EditReservePanel.findById('drerButtonAdd').enable();
				this.EditReservePanel.findById('drerIsDrug').setDisabled(!getGlobalOptions().isMinZdrav);
			}
			else
			{
				this.DrugPacientPanel.setReadOnly(true);
				this.DrugReservePanel.setReadOnly(true);
				this.PacientPanel.setReadOnly(true);
				this.EditPanel.findById('dreDrugProtoMnn_id').disable();
				this.EditPanel.findById('dreDrugRequestRow_Kolvo').disable();
				this.EditPanel.findById('dreDrugRequestType_id').disable();
				this.EditPanel.findById('dreButtonAdd').disable();
				this.EditPanel.findById('dreIsDrug').disable();
				this.EditReservePanel.findById('drerDrugProtoMnn_id').disable();
				this.EditReservePanel.findById('drerDrugRequestRow_Kolvo').disable();
				this.EditReservePanel.findById('drerDrugRequestType_id').disable();
				this.EditReservePanel.findById('drerButtonAdd').disable();
				this.EditReservePanel.findById('drerIsDrug').disable();
			}

			if ((this.findById('dreDrugRequestStatus_id').getValue()==2))
			{
				this.Actions.action_DrugRequestSetStatus.setText(lang['redaktirovat']);
				if (!this.findById('dreDrugRequestSetStatus').pressed)
					this.findById('dreDrugRequestSetStatus').toggle();
			}
			/*else 
			{
				this.findById('dreDrugRequestSetStatus').disable();
			}*/
            this.clearValues();
		}
		else 
		{
			this.buttons[0].enable();
			this.PacientPanel.setReadOnly(false);
			this.DrugPacientPanel.setReadOnly(false);
			this.DrugReservePanel.setReadOnly(false);
			this.findById('dreDrugRequest_YoungChildCount').enable();
			this.EditPanel.findById('dreDrugProtoMnn_id').enable();
			this.EditPanel.findById('dreDrugRequestRow_Kolvo').enable();
			this.EditPanel.findById('dreDrugRequestType_id').enable();
			this.EditPanel.findById('dreButtonAdd').enable();
			this.EditPanel.findById('dreIsDrug').enable();
			this.EditReservePanel.findById('drerDrugProtoMnn_id').enable();
			this.EditReservePanel.findById('drerDrugRequestRow_Kolvo').enable();
			this.EditReservePanel.findById('drerDrugRequestType_id').enable();
			this.EditReservePanel.findById('drerButtonAdd').enable();
			this.EditReservePanel.findById('drerIsDrug').enable();
			this.Actions.action_DrugRequestSetStatus.setText(lang['sformirovat']);
			if (this.findById('dreDrugRequestSetStatus').pressed)
			{
				this.findById('dreDrugRequestSetStatus').toggle();
			}
			// Принудительно вызываем onRowSelect
			if (this.PacientPanel.getCount()>0)
			{
				//в новой версии
				//this.PacientPanel.focus();
				// в старой версии 
				if (this.PacientPanel.getGrid().getSelectionModel().getSelected())
					this.PacientPanel.onRowSelect(this.PacientPanel.getGrid().getSelectionModel(), 0, this.PacientPanel.getGrid().getSelectionModel().getSelected());
				else 
				{
					// Может быть на персоне муха не валялась, но на всякий случай почистим его от мухиных следов 
					this.clearValues();
				}
			}
		}

		/*if (isSuperAdmin()) {
			this.PacientPanel.getAction('action_dre_actions').enable();
		} else {
			this.PacientPanel.getAction('action_dre_actions').disable();
		}*/

		if (
			this.action != 'view' &&
				this.findById('dreDrugRequestStatus_id').getValue() == 1 &&
				(groups.indexOf("SuperAdmin") > -1 || groups.indexOf("LpuAdmin") > -1 || groups.indexOf("LpuUser") > -1) &&
				this.findById('dreLpu_id').getValue() == getGlobalOptions().lpu_id
			) {
			this.PacientPanel.getAction('action_dre_actions').enable();
		} else {
			this.PacientPanel.getAction('action_dre_actions').disable();
		}

        if (this.action != 'view' && status == 6) {
            this.PacientPanel.getAction('action_dre_exclude').setDisabled(false);
            this.DrugPacientPanel.getAction('action_move_row_from_reserve').setDisabled(false);
            this.DrugPacientPanel.getAction('action_move_row_to_reserve').setDisabled(false);
        } else {
            this.PacientPanel.getAction('action_dre_exclude').setDisabled(true);
            this.DrugPacientPanel.getAction('action_move_row_from_reserve').setDisabled(true);
            this.DrugPacientPanel.getAction('action_move_row_to_reserve').setDisabled(true);
        }
		
		// Заголовок формы
		switch (this.action)
		{
			case 'add':
				this.setTitle(langs('Заявка врача на лекарственные средства: Добавление'));
				break;
			/*case 'edit':
				this.setTitle(langs('Заявка врача на лекарственные средства: Редактирование'));
				break;*/
			case 'view':
				this.setTitle(langs('Заявка врача на лекарственные средства: Просмотр'));
				break;
		}
	},
	clearValues: function ()
	{
		// Обнуление данных
		this.EditPanel.findById('dreDrugRequestRow_id').setValue('');
		this.EditPanel.findById('drePerson_id').setValue('');
		this.EditPanel.findById('dreDrugProtoMnn_id').setValue('');
		this.EditPanel.findById('dreDrugRequestRow_Kolvo').setValue('');
		this.EditPanel.findById('dreDrugRequestType_id').setValue('');
		this.findById('dreButtonEndEdit').setVisible(false);
		this.findById('drerButtonEndEdit').setVisible(false);
		this.findById('dreIsDrugPanel').setVisible(getGlobalOptions().isMinZdrav);
		if (this.findById('drerIsDrug').pressed)
			this.findById('drerIsDrug').toggle();
		if (this.findById('dreIsDrug').pressed)
			this.findById('dreIsDrug').toggle();
	},
	show: function()
	{
		sw.Promed.swDrugRequestEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('DrugRequestEditForm'), { msg: LOAD_WAIT });
		
		this.PersonTab.hideTabStripItem('tab_reserve');
		var form = this;
		this.insertPersonBottomBar();
		loadMask.show();
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;

		this.owner = arguments[0].owner || null;
		this.action = arguments[0].action || null;
		this.DrugRequest_id = arguments[0].DrugRequest_id || null;
		this.Lpu_id = arguments[0].Lpu_id || getGlobalOptions().lpu_id;
		this.LpuSection_id = arguments[0].LpuSection_id || null;
		this.MedPersonal_id = arguments[0].MedPersonal_id || null;
		this.DrugRequestStatus_id = arguments[0].DrugRequestStatus_id || null;
		this.DrugRequestPeriod_id = arguments[0].DrugRequestPeriod_id || null;

		this.PacientPanel.addActions({
			name:'action_dre_actions',
			text:lang['deystviya'],
			menu: [{
				name: 'action_request_copy',
				text: lang['kopirovat_predyiduschuyu_zayavku'],
				tooltip: lang['kopirovat_predyiduschuyu_zayavku'],
				handler: function() {
					form.generateRequestData(this.name);
				},
				iconCls: 'view16'
			}, {
				name: 'action_create_person_list',
				text: lang['sozdat_spisok_lgotnikov_po_prikrepleniyu'],
				tooltip: lang['sozdat_spisok_lgotnikov_po_prikrepleniyu'],
				handler: function() {
					form.generateRequestData(this.name);
				},
				iconCls: 'view16'
			}, {
				name: 'action_drug_copy',
				text: lang['kopirovat_medikamentyi_iz_predyiduschey_zayavki'],
				tooltip: lang['kopirovat_medikamentyi_iz_predyiduschey_zayavki'],
				handler: function() {
					form.generateRequestData(this.name);
				},
				iconCls: 'view16'
			}],
			iconCls: 'actions16'
		});

        this.PacientPanel.addActions({
			name:'action_dre_exclude',
			text:'Исключить из заявки',
            tooltip: 'Исключить из заявки',
            handler: function() {
                form.excludeDrugRequestPerson();
            },
            iconCls: 'delete16'
		});

		this.DrugPacientPanel.addActions({
			name:'action_dose_edit',
			text:lang['redaktirovanie_dozirovok'],
			tooltip: lang['redaktirovanie_dozirovok'],
			handler: function() {
				var view_frame = form.DrugPacientPanel;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
				if (selected_record.get('DrugRequestRow_id') > 0) {
					getWnd('swDrugRequestRowDoseEditWindow').show({
						callback: function() { view_frame.refreshRecords(null,0); this.hide(); },
						action: 'edit',
						DrugRequestRow_id: selected_record.get('DrugRequestRow_id'),
						Drug_Name: selected_record.get('DrugRequestRow_Name'),
						DrugRequestRow_DoseOnce: selected_record.get('DrugRequestRow_DoseOnce'),
						DrugRequestRow_DoseDay: selected_record.get('DrugRequestRow_DoseDay'),
						DrugRequestRow_DoseCource: selected_record.get('DrugRequestRow_DoseCource'),
						Okei_oid: selected_record.get('Okei_oid')
					});
				}
			},
			iconCls: 'edit16'
		});

		this.DrugPacientPanel.addActions({
			name:'action_move_row_to_reserve',
			text:'Перенести в резерв',
			tooltip: 'Перенести в резерв',
			handler: function() {
				var selected_record = form.DrugPacientPanel.getGrid().getSelectionModel().getSelected();
                var DrugRequest_id = form.DrugRequest_id || form.findById('dreDrugRequest_id').getValue();
				if (selected_record.get('DrugRequestRow_id') > 0 && selected_record.get('DrugRequest_id') == DrugRequest_id) {
                    form.moveDrugRequestRow({
                        DrugRequestRow_id: selected_record.get('DrugRequestRow_id'),
                        Person_id: null,
                        DrugRequestRow_Kolvo: null, //если количество не указано, то переносится все
                        callback: function() {
                            form.DrugPacientPanel.refreshRecords(null,0);
                            form.DrugReservePanel.refreshRecords(null,0);
                        }
                    });
				}
			},
			iconCls: 'actions16'
		});

		this.DrugPacientPanel.addActions({
			name:'action_move_row_from_reserve',
			text:'Добавить из резерва',
			tooltip: 'Добавить из резерва',
			handler: function() {
                var selected_record = form.PacientPanel.getGrid().getSelectionModel().getSelected();
                var DrugRequest_id = form.DrugRequest_id || form.findById('dreDrugRequest_id').getValue();
                var Person_id = selected_record.get('Person_id');
                var DRType_id = form.getDrugRequestTypeId();

                if (Ext.isEmpty(DrugRequest_id)) {
                    Ext.Msg.alert('Ошибка', 'Не выбрана заявка.');
                    return false;
                }
                if (Ext.isEmpty(Person_id)) {
                    Ext.Msg.alert('Ошибка', 'Не выбран пациент.');
                    return false;
                }
                if(DRType_id < 0) {
                    Ext.Msg.alert('Ошибка', 'Для пациента указан отказ по льготе.');
                    return false;
                }

                var params = new Object();

                if (DRType_id > 0) {
                    params.DrugRequestType_id = DRType_id;
                }

                params.DrugRequest_id = DrugRequest_id;
                params.onSelect = function(data) {
                    if (!Ext.isEmpty(data.DrugRequestRow_id) > 0) {
                        form.moveDrugRequestRow({
                            DrugRequestRow_id: data.DrugRequestRow_id,
                            Person_id: Person_id,
                            DrugRequestRow_Kolvo: data.DrugRequestRow_Kolvo,
                            callback: function() {
                                form.DrugPacientPanel.refreshRecords(null,0);
                                form.DrugReservePanel.refreshRecords(null,0);
                            }
                        });
                    }
                }

                getWnd('swReservedDrugRequestRowSelectWindow').show(params);
			},
			iconCls: 'actions16'
		});

		// Обнуление данных
		form.clearValues();
		
		if (!this.action || (this.action=='')) 
			this.action = 'add';
		// Установить первую закладку 
		this.PersonTab.setActiveTab(0);
		if ((this.Lpu_id != getGlobalOptions().lpu_id) && getGlobalOptions().isMinZdrav)
		{
			this.action = 'view';
			form.findById('dreDrugRequestMedPersonalEditPanel').setVisible(true);
		}
		else 
		{
			form.findById('dreDrugRequestMedPersonalEditPanel').setVisible(!getGlobalOptions().isMinZdrav);
			form.findById('dreIsDrugPanel').setVisible(getGlobalOptions().isMinZdrav);
			form.findById('drerIsDrugPanel').setVisible(getGlobalOptions().isMinZdrav);
		}
		// Очистим все
		form.DRType_id = -1;
		form.findById('DrugRequestParamsPanel').getForm().reset();
		form.DrugReservePanel.removeAll(true);
		form.PacientPanel.removeAll(true);
		form.DrugPacientPanel.removeAll(true);
		// На просмотр
		if (this.action!='add')
		{
			form.DrugRequestLoad();
			loadMask.hide();
			form.PersonTab.unhideTabStripItem('tab_reserve');
		}
		else 
		{
			form.setEnabled();
			form.findById('dreDrugRequestPeriod_id').setValue(this.DrugRequestPeriod_id);
			form.findById('dreMedPersonal_id').setValue(this.MedPersonal_id);
			form.findById('dreLpuSection_id').setValue(this.LpuSection_id);
			form.findById('dreDrugRequestStatus_id').setValue(1);
			form.findById('dreDrugRequestPeriod_id').focus(true, 50);
			if (!getGlobalOptions().isMinZdrav)
			{
				form.loadSprData();
			}
            form.DrugRequestPersonLoad();
			loadMask.hide();
		}
		
		/*
		if (!this.Tree.loader.baseParams.type)
		{
			this.Tree.loader.baseParams.type = 0;
			this.option_type = 0;
		}
		this.Tree.getRootNode().expand();
		//this.Tree.getRootNode().collapse();
		
		// Выбираем первую ноду и эмулируем клик 
		var node = this.Tree.getRootNode();
		if (node)
		{
			node.select();
			this.Tree.fireEvent('click', node);
		}
		//this.Tree.loader.load(this.Tree.root);
		*/
		//this.personSearchWindow = getWnd('swDrugRequestPersonFindForm');
		//this.drugSearchWindow = getWnd('swDrugRequestMedikamentSearchWindow');
		
	},
	/**
	* DrugRequestPersonLoad - функция для обновления списка пациентов, в зависимости от выбранных параментов ввода - фильтров.
	*
	*/
	DrugRequestPersonLoad: function (set_focus)  
	{
		var form = this;
		var DrugRequestPeriod_id = form.DrugRequestPeriod_id || this.findById('dreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = form.MedPersonal_id || this.findById('dreMedPersonal_id').getValue();
		var DrugRequest_id = form.DrugRequest_id || this.findById('dreDrugRequest_id').getValue();
		
		var findFamily = this.fieldFamily.getValue();
		
		var Lpu_id = form.Lpu_id || getGlobalOptions().lpu_id;
		if ((DrugRequestPeriod_id>0) && (MedPersonal_id>0))
		{
			// Сохранение формы 
			if ((!DrugRequest_id) || (DrugRequest_id==0))
				{
					// Попытка сохранить, а если данные уже присутствуют, то перечитать
					form.DrugRequestSave();
				}
			var no_set_focus = false; // !set_focus;
			form.PacientPanel.loadData({globalFilters: {start:0, limit: 50, Lpu_id: Lpu_id, MedPersonal_id: MedPersonal_id, DrugRequestPeriod_id: DrugRequestPeriod_id, Person_SurName: findFamily}, noFocusOnLoad:no_set_focus});
		}
		else 
		{
			if ((DrugRequestPeriod_id>0) && (getGlobalOptions().isMinZdrav) && (Lpu_id == getGlobalOptions().lpu_id))
			{
				form.PacientPanel.loadData({globalFilters: {start:0, limit: 50, Lpu_id:'', MedPersonal_id: '', DrugRequestPeriod_id: DrugRequestPeriod_id, Person_SurName: findFamily}});
			}
			else 
				if (form.PacientPanel.getCount()>0)
				{
					form.PacientPanel.loadData({globalFilters: {start:0, limit: 50, Lpu_id:'', MedPersonal_id: '', DrugRequestPeriod_id: '', Person_SurName: findFamily}, noFocusOnLoad:true});
				}
		}
	},
	DrugRequestPersonAdd: function (data)
	{
		// Здесь запись человека и обновление грида...
		// или запись человека только при сохранении? 
		var form = this;
		var loadMask = new Ext.LoadMask(Ext.get('DrugRequestEditForm'), { msg: LOAD_WAIT });
		loadMask.show();
		var DrugRequestPeriod_id = this.findById('dreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = this.findById('dreMedPersonal_id').getValue();
		if ((!DrugRequestPeriod_id) || ((!MedPersonal_id) && (!getGlobalOptions().isMinZdrav)))
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_zapolnenyi_neobhodimyie_polya_proverte_zapolnenie_poley_period_i_vrach']);
			loadMask.hide();
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=saveDrugRequestPerson',
			params: 
			{	
				DrugRequestPerson_id: '',
				Person_id: data.person_data.Person_id,
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				MedPersonal_id: MedPersonal_id
			},
			callback: function(opt, success, resp) 
			{
				loadMask.hide();
				form.PacientPanel.loadData({globalFilters: {start:0, limit: 50, MedPersonal_id: MedPersonal_id, DrugRequestPeriod_id: DrugRequestPeriod_id}});
				// Обработка добавления 
			}
		});
	},
	DrugReserveLoad: function (set_focus)
	{
		var win = this;
		var DrugRequestPeriod_id = win.DrugRequestPeriod_id || this.findById('dreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = win.MedPersonal_id || this.findById('dreMedPersonal_id').getValue();
		var DrugRequest_id = win.DrugRequest_id || this.findById('dreDrugRequest_id').getValue();
		if ((DrugRequestPeriod_id>0) && ((MedPersonal_id>0) || getGlobalOptions().isMinZdrav))
		{
			// Сохранение формы 
			if ((!DrugRequest_id) || (DrugRequest_id==0))
			{
				// Попытка сохранить, а если данные уже присутствуют, то перечитать
				win.DrugRequestSave();
			}
			win.DrugReservePanel.loadData({globalFilters: {DrugRequest_id: DrugRequest_id, MedPersonal_id: MedPersonal_id, DrugRequestPeriod_id: DrugRequestPeriod_id}, noFocusOnLoad:!set_focus});
		}
		else 
		{
			win.DrugReservePanel.loadData({globalFilters: {DrugRequest_id: '', MedPersonal_id: '', DrugRequestPeriod_id: ''}, noFocusOnLoad:true});
		}
	},
	DrugRequestLoad: function ()
	{
		// Чтение заявки
		var form = this;
		var DrugRequestPeriod_id = form.DrugRequestPeriod_id || this.findById('dreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = form.MedPersonal_id || this.findById('dreMedPersonal_id').getValue();
		var DrugRequest_id = form.DrugRequest_id || this.findById('dreDrugRequest_id').getValue();
		var Lpu_id = form.Lpu_id || this.findById('dreLpu_id').getValue();
		
		
		var loadMask = new Ext.LoadMask(Ext.get('DrugRequestEditForm'), { msg: lang['sohranenie_zayavki'] });
		form.findById('DrugRequestParamsPanel').getForm().load(
		{
			url: '/?c=DrugRequest&m=index&method=getDrugRequest',
			params:
			{
				Lpu_id: Lpu_id,
				DrugRequest_id: DrugRequest_id,
				MedPersonal_id: MedPersonal_id,
				DrugRequestPeriod_id: DrugRequestPeriod_id
			},
			success: function (a,b)
			{
				var result = Ext.util.JSON.decode(b.response.responseText);
				log(result);
				
				loadMask.hide();
				if(result[0].RequestMO_day&&result[0].RequestMO_time){
					Ext.Msg.alert(lang['informatsiya'], lang['zayavka_doljna_byit_zaschischena_v_mz']+result[0].RequestMO_day+' '+result[0].RequestMO_time);
					var oToday = new Date(); // текущая дата
					var oDeadLineDate = Date.parseDate(result[0].RequestMO_day+' '+result[0].RequestMO_time,'d.m.Y H:i')
					var nDaysLeft = oDeadLineDate > oToday ? Math.ceil((oDeadLineDate - oToday) / (1000 * 60 * 60 * 24)) : null; // а тут мы вычисляем, сколько же осталось дней — находим разницу в миллисекундах и переводим её в дни
					if(form.action=='edit'){
						if(nDaysLeft<7){
							form.setTitle('Заявка врача на лекарeственные средства: Редактирование<span style="float:right;color:red"><b>'+result[0].RequestMO_day+' '+result[0].RequestMO_time+'</b></span>')
						}else{
							form.setTitle('Заявка врача на лекарeственные средства: Редактирование<span style="float:right">'+result[0].RequestMO_day+' '+result[0].RequestMO_time+'</span>')
						}
					}
				}
				
				if ((form.findById('dreDrugRequestTotalStatus_IsClose').getValue()==2) && (form.findById('dreDrugRequestStatus_id').getValue()!=3))
				{
					form.action='view';
				}
				form.setEnabled();
				if ((!getGlobalOptions().isMinZdrav) || ((this.Lpu_id != getGlobalOptions().lpu_id) && getGlobalOptions().isMinZdrav))
				{
					form.loadSprData();
				}
				form.DrugRequestPersonLoad(true);
				//form.findById('dreDrugRequestPeriod_id').focus(true);
			},
			failure: function ()
			{
				loadMask.hide();
				form.returnFunc(form.owner, -1);
				//Ext.Msg.alert('Ошибка', 'Ошибка запроса к серверу! <br/>Попробуйте повторить операцию.');
			}
		});
	},
	PersonDrugProtoMnnLoad: function (DrugRequestType_id, DrugProtoMnn_id, DrugProtoMnn_Name, query, focusOnKolvo)
	{
        var win = this;
		win.findById('dreDrugProtoMnn_id').clearValue();
		win.findById('dreDrugProtoMnn_id').getStore().removeAll();
		win.findById('dreDrugProtoMnn_id').lastQuery = '';
		win.findById('dreDrugProtoMnn_id').getStore().baseParams.ReceptFinance_id = DrugRequestType_id;
		if (DrugProtoMnn_Name.length==0)
			win.findById('dreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = DrugProtoMnn_id;
		else 
			win.findById('dreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = '';
		win.findById('dreDrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
		// Для торговых наименований
		win.findById('dreDrugProtoMnn_id').getStore().baseParams.IsDrug = win.findById('dreIsDrug').pressed?1:0;
		win.findById('dreDrugProtoMnn_id').getStore().baseParams.MedPersonal_id = win.findById('DrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
		win.findById('dreDrugProtoMnn_id').getStore().baseParams.query = query;
		win.findById('dreDrugProtoMnn_id').getStore().load({
			callback: function()
			{
				if (DrugProtoMnn_id!='')
					win.findById('dreDrugProtoMnn_id').setValue(DrugProtoMnn_id);
				if (DrugProtoMnn_Name!='')
					win.findById('dreDrugProtoMnn_id').setRawValue(DrugProtoMnn_Name);
				if (focusOnKolvo)
					win.findById('dreDrugRequestRow_Kolvo').focus(true);
			}
		});
	},
	DrugRequestSave: function ()
	{
		// Запись заявки 
		var form = this;
		var callback = arguments[0] && arguments[0].callback && typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext.emptyFn;
		var loadMask = new Ext.LoadMask(Ext.get('DrugRequestEditForm'), { msg: lang['sohranenie_zayavki'] });
		loadMask.show();
		var DrugRequest_id = this.findById('dreDrugRequest_id').getValue();
		var DrugRequestStatus_id = this.findById('dreDrugRequestStatus_id').getValue();
		var DrugRequestPeriod_id = this.findById('dreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = this.findById('dreMedPersonal_id').getValue();
		var LpuSection_id = this.findById('dreLpuSection_id').getValue();
		var DrugRequest_YCC = this.findById('dreDrugRequest_YoungChildCount').getValue();
		if (!getGlobalOptions().isMinZdrav)
			var DrugRequest_Name = lang['zayavka_vracha'];
		else 
			var DrugRequest_Name = lang['zayavka_ministerstva_zdravoohraneniya'];
		if ((!DrugRequestPeriod_id) || ((!MedPersonal_id) && (!getGlobalOptions().isMinZdrav)))
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya_proverte_zapolnenie_poley_period_i_vrach']);
			loadMask.hide();
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=saveDrugRequest',
			params: 
			{	
				DrugRequest_id: DrugRequest_id,
				DrugRequestStatus_id: DrugRequestStatus_id,
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				MedPersonal_id: MedPersonal_id,
				LpuSection_id: LpuSection_id,
				DrugRequest_Name: DrugRequest_Name,
				DrugRequest_YoungChildCount: DrugRequest_YCC
			},
			callback: function(options, success, response) 
			{
				loadMask.hide();
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.DrugRequest_id)
					{
						form.findById('dreDrugRequest_id').setValue(result.DrugRequest_id);
						if (form.findById('dreDrugRequestStatus_id').getValue()!=result.DrugRequestStatus_id)
							form.findById('dreDrugRequestStatus_id').setValue(result.DrugRequestStatus_id);
						if (form.findById('dreDrugRequestTotalStatus_IsClose').getValue()!=result.DrugRequestTotalStatus_IsClose)
							form.findById('dreDrugRequestTotalStatus_IsClose').setValue(result.DrugRequestTotalStatus_IsClose);
						form.findById('dreLpu_id').setValue(getGlobalOptions().lpu_id);
						form.action = 'edit';
						if ((form.findById('dreDrugRequestTotalStatus_IsClose').getValue()==2) && (form.findById('dreDrugRequestStatus_id').getValue()!=3))
						{
							form.action='view';
						}
						form.setEnabled();
                        form.findById('dreDrugProtoMnn_id').getStore().baseParams.MedPersonal_id = form.findById('DrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
                        form.findById('drerDrugProtoMnn_id').getStore().baseParams.MedPersonal_id = form.findById('DrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
						callback();
					}
				}
				else 
				{
					form.hide();
				}
			}
		});
	},
	checkPersonMedikamentKolvo: function()
	{
		this.checkPersonMedikamentAdd();
		return;
		
		var form = this;
		var DrugRequestRow_Kolvo = form.findById('dreDrugRequestRow_Kolvo').getValue();
		if (DrugRequestRow_Kolvo>10)
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNO,
				scope : form,
				fn: function(buttonId) 
				{
					if ( buttonId == 'yes' )
					{
						form.findById('dreDrugRequestRow_Kolvo').focus();
					}
					else
					{
						form.checkPersonMedikamentAdd();
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: lang['vyi_ukazali_medikament_v_kolichestve_bolee_10_izmenit_kolichestvo_zayavlyaemogo_preparata'],
				title: lang['vnimanie']
			});
		}
		else 
		{
			form.checkPersonMedikamentAdd();
		}
	},
	checkPersonMedikamentAdd: function()
	{
		var form = this;
		var Person_id = form.findById('drePerson_id').getValue();
		var DrugRequestPeriod_id = form.findById('dreDrugRequestPeriod_id').getValue();
		if (form.findById('dreButtonAdd').getText() == lang['izmenit']){
			var record = this.DrugPacientPanel.getGrid().getSelectionModel().getSelected();
			var med = (record.get('DrugProtoMnn_id')=='')?record.get('Drug_id'):record.get('DrugProtoMnn_id');
			var DrugProtoMnn_id = med;
		} else {
			var DrugProtoMnn_id = form.findById('dreDrugProtoMnn_id').getValue();
		}
		var DrugRequestRow_Kolvo = form.findById('dreDrugRequestRow_Kolvo').getValue();
		var DrugRequestRow_id = form.findById('dreDrugRequestRow_id').getValue();
		if (Person_id && DrugRequestPeriod_id && DrugProtoMnn_id)
		{
			Ext.Ajax.request(
			{
				url: '/?c=DrugRequest&m=checkUniAllLpuDrugRequestRow',
				params: 
				{	
					Person_id: Person_id,
					DrugRequestRow_id: DrugRequestRow_id,
					DrugRequestPeriod_id: DrugRequestPeriod_id,
					DrugProtoMnn_id: DrugProtoMnn_id
				},
				callback: function(options, success, response) 
				{
					if (success)
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.count>0)
						{
							sw.swMsg.show(
							{
								buttons: Ext.Msg.YESNO,
								scope : form,
								fn: function(buttonId) 
								{
									if ( buttonId == 'yes' )
									{
										form.DrugRequestRowSave();
									}
								},
								icon: Ext.Msg.QUESTION,
								msg: lang['dannyiy_preparat_uje_byil_vklyuchen_v_personifitsirovannuyu_zayavku_dannogo_lgotopoluchatelya_vyi_hotite_dobavit_vyibrannyiy_medikament'],
								title: lang['vopros']
							});
						}
						else 
						{
							form.DrugRequestRowSave();
						}
					}
					else 
					{
						form.DrugRequestRowSave();
					}
				}
			});
		}
	},
	DrugRequestRowSave: function(isReserve) 
	{
		if (isReserve)
		{
			var prefix='drer';
			var prefixPanel='Reserve';
		}
		else 
		{
			var prefix='dre';
			var prefixPanel='';
		}
		var win = Ext.getCmp('DrugRequestEditForm');
		
		// Добавление в грид - проверки 
		if (!win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').getValue())
		//|| ((!win['Edit'+prefixPanel+'Panel'].findById(prefix+'Drug_id').getValue()) && (getGlobalOptions().isMinZdrav)))
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_medikament']);
			win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').focus();
			return false;
		}
		if (!win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestRow_Kolvo').getValue())
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_ukazano_kolichestvo']);
			win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestRow_Kolvo').focus();
			return false;
		}
		if (!win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestType_id').getValue())
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_ukazan_tip']);
			win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestType_id').focus();
			return false;
		}
		var DrugRequest_id = win.findById('dreDrugRequest_id').getValue();
		if (!isReserve)
			var Person_id = win.findById('drePerson_id').getValue();
		else 
			var Person_id = null;
		if (win.findById(prefix+'ButtonAdd').getText() == lang['izmenit'] && !isReserve){
			var record = this.DrugPacientPanel.getGrid().getSelectionModel().getSelected();
			var med = (record.get('DrugProtoMnn_id')=='')?record.get('Drug_id'):record.get('DrugProtoMnn_id');
			var DrugProtoMnn_id = med;
		} else {
			var DrugProtoMnn_id = win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').getValue();
		}
		
		var DrugRequestRow_Kolvo = win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestRow_Kolvo').getValue();
		var DrugRequestRow_id = win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestRow_id').getValue();
		var DrugRequestType_id = win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestType_id').getValue();
		var DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
		// Если режим редактирования, тогда берем данные из грида
		var IsDrug = 0;
		if (win['Edit'+prefixPanel+'Panel'].findById(prefix+'IsDrugEdit').getValue()>=0)
			IsDrug = win['Edit'+prefixPanel+'Panel'].findById(prefix+'IsDrugEdit').getValue();
		else 
			IsDrug = win['Edit'+prefixPanel+'Panel'].findById(prefix+'IsDrug').pressed?1:0; //win.findById('dreDrugProtoMnn_id').getStore().baseParams.IsDrug
		// Если заявка новая, то она должна сохраниться на моменте ввода людей
		if (DrugRequest_id==0)
		{
			// Тут заявку надо сохранить и дождаться сохранения  - а именно ID заявки 
			// Здесь поправить еще позже  
			win.DrugRequestSave();
			return false;
		}
		if ((!isReserve) && (!Person_id))
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_chelovek_vyiberite_cheloveka_na_kotorogo_neobhodimo_vvesti_medikament']);
			return false;
		}
		var MedPersonal_id = win.findById('DrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
		var loadMask = new Ext.LoadMask(Ext.get('DrugRequestEditForm'), { msg: LOAD_WAIT });
		loadMask.show();
		
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=saveDrugRequestRow',
			params: 
			{	
				DrugRequestRow_id: DrugRequestRow_id || '',
				IsDrug: IsDrug, // Что сохраняем: торговое или МНН
				DrugRequest_id: DrugRequest_id,
				Person_id: Person_id,
				DrugProtoMnn_id: DrugProtoMnn_id,
				DrugRequestRow_Kolvo: DrugRequestRow_Kolvo,
				DrugRequestType_id: DrugRequestType_id,
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				MedPersonal_id: MedPersonal_id
			},
			callback: function(opt, success, resp) 
			{
				loadMask.hide();
				var DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
				var MedPersonal_id = win.findById('dreMedPersonal_id').getValue();
				if (!isReserve)
				{
					win.DrugPacientPanel.loadData(
					{
						globalFilters:{Person_id:Person_id, DrugRequestPeriod_id: DrugRequestPeriod_id}, noFocusOnLoad:true
					});
				}
				else 
				{
					win.DrugReservePanel.loadData(
					{
						globalFilters:{DrugRequest_id: DrugRequest_id, DrugRequestPeriod_id: DrugRequestPeriod_id, MedPersonal_id: MedPersonal_id}, noFocusOnLoad:true
					});
				}
				
				if (win.findById(prefix+'ButtonAdd').getText() == lang['izmenit'])
				{
					win.findById(prefix+'ButtonAdd').enable();
					win.findById(prefix+'ButtonAdd').setText(lang['dobavit']);
					win.findById(prefix+'IsDrug').enable();
					win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = '';
					win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
					win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestRow_id').setValue('');
					win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').enable();
					
					if (isReserve)
					{
						//win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').enable();
						win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestType_id').enable();
						win.findById(prefix+'ButtonEndEdit').setVisible(true);
						win['DrugReservePanel'].getGrid().getSelectionModel().clearSelections();
					}
					else 
					{
						win.findById(prefix+'ButtonEndEdit').setVisible(false);
						win['DrugPacientPanel'].getGrid().getSelectionModel().clearSelections();
					}
				}
				// Очистить поля для ввода и переставить фокус
				/*
				win.EditPanel.findById('dreDrugProtoMnn_id').setValue('');
				win.EditPanel.findById('dreDrugRequestRow_Kolvo').setValue('');
				win.EditPanel.findById('dreDrugRequestType_id').setValue('');
				*/
				win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').focus();
			}
		});
	},
	insertPersonBottomBar: function()
	{
		if (!this.fieldSetFamily)
		{
			this.fieldFamily = new Ext.form.TextField({
				allowBlank: true,
				enableKeyEvents: true,
				fieldLabel: '&nbsp;Фамилия',
				tooltip: lang['dlya_togo_chtobyi_otfiltrovat_dannyie_po_familii_vvedite_familiyu_ili_chast_i_najmite_[enter]'],
				name: 'fieldFindFamily',
				width: 180,
				listeners: {
					'keydown': function (inp, e) 
					{
						if (e.getKey() == Ext.EventObject.ENTER)
						{
                            this.DrugRequestPersonLoad(true);
						}
					}.createDelegate(this),
					'change': function(f,nv,ov)
					{
						this.PacientPanel.setParam('Person_SurName', nv, true);
					}.createDelegate(this)
				}
			});
			this.fieldSetFamily = new Ext.form.FieldSet(
			{
				border: false,
				autoHeight: true,
				style: 'padding:0px;margin:0px;',
				labelWidth: 50,
				items: [this.fieldFamily]
			});
			this.PacientPanel.getGrid().getBottomToolbar().addSeparator();
			this.PacientPanel.getGrid().getBottomToolbar().add(this.fieldSetFamily);
		}
	},
	generateRequestData: function(action) {
		var wnd = this;

		wnd.DrugRequestSave({callback: function(){ //предварительное сохранение заявки
			wnd.grdSetOptions(action, function(params) { //получение от пользователя входящих данных
				wnd.grdCheckExistsData(action, function(data_exists) { //проверка текущей заявки на наличие данных
					wnd.grdConfirm(data_exists, function() { //получение подтверждения пользователя на изменение данных
						wnd.grdRegionCountConfirm(action, function() { //проверка количества участков врача, и получение подтверждения от пользователя в случае необходимости
							wnd.grdExecute(action, params, function() { //изменение данных
								wnd.grdCallback(action); //отображение изменения данных на форме
							});
						});
					});
				});
			});
		}});
	},
	grdSetOptions: function(action, callback) {
		var DrugRequest_id = this.findById('dreDrugRequest_id').getValue();
		var params = {
			DrugRequest_id: DrugRequest_id
		};
		if (action != 'action_create_person_list') {
			getWnd('swMzDrugRequestCopyOptionsWindow').show({
				DrugRequest_id: DrugRequest_id,
				onSelect: function(prm) {
					if (prm.DrugRequest_id > 0) {
						params.SourceDrugRequest_id = prm.DrugRequest_id;
						callback(params);
					}
				}
			});
		} else {
			callback(params);
		}
	},
	grdCheckExistsData: function(action, callback) {
		var DrugRequest_id = this.findById('dreDrugRequest_id').getValue();
		var pacient_store = this.PacientPanel.getGrid().getStore();

		if (DrugRequest_id <= 0) {
			return false;
		}

		if (pacient_store.getCount() > 0 && pacient_store.getAt(0).get('Person_id') > 0) { //если грид с пациентами не пуст значит данные есть
			callback(true);
		} else if (action == 'action_create_person_list') { //для создания списка льготников достаточным критерием отсутствия данных является пустой список пациентов
			callback(false);
		} else {
			Ext.Ajax.request({
				url: '/?c=MzDrugRequest&m=getDrugRequestRowCount',
				params: {
					DrugRequest_id: DrugRequest_id
				},
				callback: function(options, success, response) {
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						callback(result.cnt > 0);
					}
				}
			});
		}
	},
	grdConfirm: function(data_exists, callback) {
		if (data_exists) {
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: lang['tekuschaya_zayavka_vracha_soderjit_dannyie_kotoryie_mogut_byit_izmenenyi_prodoljit_operatsiyu_kopirovaniya_dannyih'],
				title: lang['vopros'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						callback();
					}
				}
			});
		} else {
			callback();
		}
	},
	grdRegionCountConfirm: function(action, callback) {
		var DrugRequest_id = this.findById('dreDrugRequest_id').getValue();

		if (action == 'action_create_person_list') {
			Ext.Ajax.request({
				url: '/?c=DrugRequest&m=getLpuRegionCountByDrugRequestId',
				params: {
					DrugRequest_id: DrugRequest_id
				},
				callback: function(options, success, response) {
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);

						if (result.cnt > 1) {
							sw.swMsg.show({
								icon: Ext.MessageBox.QUESTION,
								msg: lang['vnimanie_vrach_yavlyaetsya_uchastkovyim_vrachom_na_neskolkih_uchastkah_v_zayavku_budut_dobavlenyi_patsientyi_prikreplennyie_ko_vsem_uchastkam_vracha_prodoljit_formirovanie_spiska_lgotnikov_zayavki'],
								title: lang['vopros'],
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ('yes' == buttonId) {
										callback();
									}
								}
							});
						} else {
							callback();
						}
					}
				}
			});
		} else {
			callback();
		}
	},
	grdExecute: function(action, params, callback) {
		var method = null;

		switch(action) {
			case 'action_request_copy':
				method = 'createDrugRequestCopy';
				break;
			case 'action_create_person_list':
				method = 'createDrugRequestPersonList';
				break;
			case 'action_drug_copy':
				method = 'createDrugRequestDrugCopy';
				break;
		}

		var loadMask = new Ext.LoadMask(Ext.get('DrugRequestEditForm'), { msg: LOAD_WAIT });
		loadMask.show();
		if (method) {
			Ext.Ajax.request({
				url: '/?c=DrugRequest&m='+method,
				params: params,
				callback: function(options, success, response) {
					loadMask.hide();
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.Error_Msg) {
							sw.swMsg.alert(lang['oshibka'], result.Error_Msg);
						} else {
							callback();
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_obrabotke_dannyih_proizoshla_oshibka']);
						return false;
					}
				}
			});
		}
	},
	grdCallback: function(action) {
		switch(action) {
			case 'action_request_copy':
				sw.swMsg.alert(lang['kopirovanie_zaversheno'], lang['kopirovanie_medikamentov_zaversheno_vnimanie_neobhodimo_otredaktirovat_kolichestvo_medikamentov_v_tekuschey_zayavke']);

				break;
			case 'action_create_person_list':
				sw.swMsg.alert(lang['formirovanie_zaversheno'], lang['spisok_lgotnikov_sformirovan_v_nego_vklyuchenyi_lgotniki_prikreplennyie_k_uchastku_vracha_i_obraschavshiesya_za_retseptami_v_poslednie_90_dney']);

				break;
			case 'action_drug_copy':
				sw.swMsg.alert(lang['kopirovanie_zaversheno'], lang['kopirovanie_medikamentov_zaversheno_vnimanie_neobhodimo_otredaktirovat_kolichestvo_medikamentov_v_tekuschey_zayavke']);
				break;
		}
		this.DrugRequestPersonLoad(true);
		//фиксируем необходимость перезагрузки грида с резервом
		this.DrugReservePanel.load_complete = false;

	},
	initComponent: function()
	{
		var form = this;
		form.DrugRecord = Ext.data.Record.create(
		[
			{name: 'DrugRequestRow_id', mapping: 'DrugRequestRow_id'},
			{name: 'DrugRequest_id', mapping: 'DrugRequest_id', type: 'int'},
			{name: 'Person_id', mapping: 'Person_id', type: 'int'},
			{name: 'DrugProtoMnn_id', mapping: 'DrugProtoMnn_id', type: 'int'},
			{name: 'DrugRequestRow_Name', mapping: 'DrugRequestRow_Name', type: 'string'},
			{name: 'DrugRequestRow_Code', mapping: 'DrugRequestRow_Code', type: 'int'},
			{name: 'DrugRequestRow_Kolvo', mapping: 'DrugRequestRow_Kolvo', type: 'int'},
			{name: 'DrugRequestRow_Price', mapping: 'DrugRequestRow_Price', type: 'float'},
			{name: 'DrugRequestRow_Summa', mapping: 'DrugRequestRow_Summa', type: 'float'},
			{name: 'DrugRequestType_Name', mapping: 'DrugRequestType_Name', type: 'string'},
			{name: 'MedPersonal_FIO', mapping: 'MedPersonal_FIO', type: 'string'},
			{name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string'},
			{name: 'DrugRequestRow_insDT', mapping: 'DrugRequestRow_insDT', dateFormat: 'd.m.Y'},
			{name: 'DrugRequestRow_updDT', mapping: 'DrugRequestRow_updDT', dateFormat: 'd.m.Y'},
			{name: 'DrugRequestRow_delDT', mapping: 'DrugRequestRow_delDT', dateFormat: 'd.m.Y'}
		]);
		// События формы 
		this.Actions = new Array();
		this.Actions =
		{
			action_DrugAdd: new Ext.Action(
			{
				tooltip: lang['dobavlenie_redaktirovanie_medikamenta'],
				id: 'dreButtonAdd',
				text: lang['dobavit'],
				icon: 'img/icons/add16.png', 
				iconCls : 'x-btn-text',
				disabled: false, 
				handler: function() 
				{
					var win = Ext.getCmp('DrugRequestEditForm');
					win.checkPersonMedikamentKolvo();
				}
			}),
			action_DrugReserveAdd: new Ext.Action(
			{
				tooltip: lang['dobavlenie_redaktirovanie_medikamenta'],
				id: 'drerButtonAdd',
				text: lang['dobavit'],
				icon: 'img/icons/add16.png', 
				iconCls : 'x-btn-text',
				disabled: false, 
				handler: function() 
				{
					var win = Ext.getCmp('DrugRequestEditForm');
					win.DrugRequestRowSave(true);
				}
			}),
			action_DrugEditEndEdit: new Ext.Action(
			{
				tooltip: lang['prodoljit_vvod_medikamentov'],
				id: 'dreButtonEndEdit',
				text: lang['prodoljit_vvod'],
				iconCls : 'ok16',
				hidden: true,
				disabled: false, 
				handler: function() 
				{
					var win = Ext.getCmp('DrugRequestEditForm');
					win.findById('dreButtonEndEdit').setVisible(false);
					win.findById('dreButtonAdd').enable();
					win.findById('dreButtonAdd').setText(lang['dobavit']);
					win['EditPanel'].findById('dreIsDrugEdit').setValue(-1);
					win['EditPanel'].findById('dreIsDrug').enable();
					win['EditPanel'].findById('dreDrugRequestRow_id').setValue('');
					win['EditPanel'].findById('dreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = '';
					win['EditPanel'].findById('dreDrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
					
					win['EditPanel'].findById('dreDrugProtoMnn_id').setValue('');
					win['EditPanel'].findById('dreDrugRequestRow_Kolvo').setValue('');
					win['EditPanel'].findById('dreDrugProtoMnn_id').enable();
					win['EditPanel'].findById('dreDrugRequestRow_Kolvo').enable('');
					// Unselect grid
					win.DrugPacientPanel.getGrid().getSelectionModel().clearSelections();
					if (win.DRType_id !=0) 
					{
						if (win.findById('dreDrugRequestType_id').getValue()!=win.DRType_id)
						{
							// Перегружаем справочник медикаментов
							win.PersonDrugProtoMnnLoad(win.DRType_id, '', '', '', false);
						}
						win['EditPanel'].findById('dreDrugRequestType_id').setValue(win.DRType_id);
						win['EditPanel'].findById('dreDrugRequestType_id').disable();
						win['EditPanel'].findById('dreDrugProtoMnn_id').focus();
						
					}
					else 
					{
						win['EditPanel'].findById('dreDrugRequestType_id').setValue('');
						win['EditPanel'].findById('dreDrugRequestType_id').enable();
						win['EditPanel'].findById('dreDrugRequestType_id').focus();
					}
				}
			}),
			action_DrugReserveEndEdit: new Ext.Action(
			{
				tooltip: lang['prodoljit_vvod_medikamentov'],
				id: 'drerButtonEndEdit',
				text: lang['prodoljit_vvod'],
				iconCls : 'ok16',
				hidden: true,
				disabled: false, 
				handler: function() 
				{
					var win = Ext.getCmp('DrugRequestEditForm');
					win.findById('drerButtonEndEdit').setVisible(false);
					win.findById('drerButtonAdd').enable();
					win.findById('drerButtonAdd').setText(lang['dobavit']);
					win['EditReservePanel'].findById('drerIsDrugEdit').setValue(-1);
					win['EditReservePanel'].findById('drerIsDrug').enable();
					win['EditReservePanel'].findById('drerDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = '';
					win['EditReservePanel'].findById('drerDrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
					win['EditReservePanel'].findById('drerDrugRequestRow_id').setValue('');
					
					win['EditReservePanel'].findById('drerDrugProtoMnn_id').setValue('');
					win['EditReservePanel'].findById('drerDrugRequestRow_Kolvo').setValue('');
					win['EditReservePanel'].findById('drerDrugRequestType_id').setValue('');
					
					win['EditReservePanel'].findById('drerDrugProtoMnn_id').enable();
					win['EditReservePanel'].findById('drerDrugRequestRow_Kolvo').enable();
					win['EditReservePanel'].findById('drerDrugRequestType_id').enable();
					win['EditReservePanel'].findById('drerDrugRequestType_id').focus();
				}
			}),
			action_PersonAdd: new Ext.Action(
			{
				tooltip: lang['dobavlenie_patsienta'],
				text: lang['dobavit_patsienta'],
				icon: '', 
				iconCls : 'x-btn-text',
				disabled: true, 
				handler: function() 
				{
					var win = Ext.getCmp('DrugRequestEditForm');
					if (win.findById('dreDrugRequest_id').getValue()==0)
						win.DrugRequestSave();

					var searchMode = 'attachrecipients';

					if (getGlobalOptions().lpu_sysnick == 'osindint') {
						searchMode = 'withlgotonly';
					}

					getWnd('swPersonSearchWindow').show(
					{
						onSelect: function(person_data) 
						{
							win.DrugRequestPersonAdd({person_data: person_data});
						},
						searchMode: searchMode
					});
				}
			}),
			action_DrugRequestPrint: new Ext.Action(
			{
				tooltip: lang['pechat_zayavki'],
				id: 'dreDrugRequestPrint',
				text: lang['pechat_zayavki'],
				iconCls: 'print16',
				minWidth: 150,
				disabled: true, 
				handler: function() {
					if ( getWnd('swDrugRequestPrintWindow').isVisible() ) {
						sw.swMsg.alert(lang['oshibka'], lang['okno_pechati_zayavki_uje_otkryito']);
						return false;
					}

					var win = Ext.getCmp('DrugRequestEditForm');

					getWnd('swDrugRequestPrintWindow').show({
						DrugRequestPeriod_id: win.findById('dreDrugRequestPeriod_id').getValue(),
						LpuSection_id: win.findById('dreLpuSection_id').getValue(),
						LpuUnit_id: win.findById('dreLpuUnit_id').getValue(),
						MedPersonal_id: win.findById('dreMedPersonal_id').getValue()
					});
				}
			}),
			action_DrugRequestSetStatus: new Ext.Action(
			{
				tooltip: lang['sformirovat_zayavku'],
				id: 'dreDrugRequestSetStatus',
				text: lang['sformirovat'],
				iconCls: 'actions16',
				minWidth: 150,
				enableToggle: true,
				disabled: true, 
				handler: function() 
				{
					var win = Ext.getCmp('DrugRequestEditForm');
					var status = win.findById('dreDrugRequestStatus_id').getValue();
					if (status == 2)
					{
						sw.swMsg.show(
						{
							icon: Ext.MessageBox.QUESTION,
							msg: lang['izmenit_status_zayavki_na_nachalnaya'],
							title: lang['vopros'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj)
							{
								if ('yes' == buttonId)
								{
									win.findById('dreDrugRequestStatus_id').setValue(1);
									win.DrugRequestSave();
								}
								else 
								{
									win.findById('dreDrugRequestSetStatus').toggle();
								}
							}
						});
					}
					else 
					if (status == 1 || status == 6)
					{
						sw.swMsg.show(
						{
							icon: Ext.MessageBox.QUESTION,
							msg: lang['zayavka_so_statusom_sformirovannaya_dostupna_tolko_dlya_prosmotra_izmenit_status_zayavki_na_sformirovannaya'],
							title: lang['vopros'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj)
							{
								if ('yes' == buttonId)
								{
									win.findById('dreDrugRequestStatus_id').setValue(2);
									win.DrugRequestSave();
								}
								else 
								{
									win.findById('dreDrugRequestSetStatus').toggle();
								}
							}
						});
					}
				}
			}),
			action_DrugRequestReallocate: new Ext.Action(
			{
				tooltip: 'Перераспределить заявку',
				id: 'dreDrugRequestReallocate',
				text: 'Перераспределение',
				iconCls: 'actions16',
				minWidth: 150,
				enableToggle: true,
				disabled: true,
				hidden: true,
				handler: function() {
					var win = Ext.getCmp('DrugRequestEditForm');
					var status = win.findById('dreDrugRequestStatus_id').getValue();
                    win.findById('dreDrugRequestStatus_id').setValue(6); //6 - Перераспределение
                    win.DrugRequestSave(/*{
                        callback: function() {
                        }
                    }*/);
				}
			})
		};
		/*
		this.GroupActions = new Array();
		
		// Группа акшенов уровней
		this.GroupActions['actions'] = new Ext.Action(
		{
			text:lang['deystviya'], 
			menu: [
				form.Actions.action_New_EvnPL, 
				form.Actions.action_PersonAdd
			]
		});
		this.GroupActions['settings'] = new Ext.Action(
		{
			text:lang['nastroyki'], 
			menu: 
			{
				items: 
				[{
					text: lang['vyivodit_sobyitiya_po_date'],
					checked: true,
					group: 'group',
					handler: function ()
					{
						form.Tree.loader.baseParams.type = 0;
						form.option_type = 0;
						form.Tree.getRootNode().select()
						form.Tree.loader.load(form.Tree.root);
						form.Tree.getRootNode().expand();
					},
					checkHandler: function () 
					{
					}
				}, 
				{
					text: lang['gruppirovat_sobyitiya_po_tipam'],
					checked: false,
					group: 'group',
					handler: function ()
					{
						form.Tree.loader.baseParams.type = 1;
						form.option_type = 1;
						form.Tree.getRootNode().select()
						form.Tree.loader.load(form.Tree.root);
						form.Tree.getRootNode().expand();
					},
					checkHandler: function () 
					{
						
					}
				}]
			}
		});
		*/
		/*
		this.TreeToolbar = new Ext.Toolbar(
		{
			id : form.id+'Toolbar',
			items:
			[
				form.GroupActions.actions,
				{
					xtype : "tbseparator"
				},
				form.GroupActions.settings
			]
		});
		
		// Формируем меню по правой кнопке 
		this.ContextMenu = new Ext.menu.Menu();
		for (key in this.Actions)
		{
			this.ContextMenu.add(this.Actions[key]);
		}
		*/
		// Кнопка Печать 
		var btnDrugRequestPrint = new Ext.Button(this.Actions.action_DrugRequestPrint);
		var btnDrugRequestSetStatus = new Ext.Button(this.Actions.action_DrugRequestSetStatus);
		var btnDrugRequestReallocate = new Ext.Button(this.Actions.action_DrugRequestReallocate);
		/*btnDrugRequestSetStatus.on('toggle',
			function(btn, pressed)
			{
				if (pressed)
				{
					btn.setText(lang['redaktirovat']);
				}
				else
				{
					btn.setText(lang['sformirovat']);
				}
			});
		*/
		this.ParamsPanel = new Ext.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: false,
			collapsible: false,
			autoHeight: true,
			//height: 80,
			region: 'north',
			//labelAlign: 'top',
			labelWidth: 110,
			layout: 'column',
			//title: 'Параметры',
			id: 'DrugRequestParamsPanel',
			items: 
			[{
				// Левая часть параметров ввода
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px;',
				columnWidth: .4,
				labelWidth: 100,
				items: 
				[{
					id: 'dreDrugRequest_id',
					name: 'DrugRequest_id',
					value: null,
					xtype: 'hidden'
				}, 
				{
					id: 'dreLpu_id',
					name: 'Lpu_id',
					value: null,
					xtype: 'hidden'
				}, 
				{
					id: 'dreDrugRequestTotalStatus_IsClose',
					name: 'DrugRequestTotalStatus_IsClose',
					value: null,
					xtype: 'hidden'
				}, 
				{
					allowBlank: false,
					disabled: true,
					id: 'dreDrugRequestStatus_id',
					xtype: 'swdrugrequeststatuscombo',
					tabIndex:4111
				},
				{
					allowBlank: false,
					disabled: false,
					id: 'dreDrugRequestPeriod_id',
					xtype: 'swdrugrequestperiodcombo',
					tabIndex:4112,
					listeners: {
						change: function(combo) {
                            var lpuunit_combo = form.findById('dreLpuUnit_id');
                            if (lpuunit_combo.getValue() > 0) {
                                lpuunit_combo.fireEvent('change', lpuunit_combo, lpuunit_combo.getValue(), null);
                            } else {
                                form.DrugRequestPersonLoad();
                            }
                        }
					}
				}]
			},
			{
				// Средняя часть параметров ввода
				layout: 'form',
				border: false,
				id: 'dreDrugRequestMedPersonalEditPanel',
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .4,
				labelWidth: 110,
				items:
				[{
					xtype:'panel',
					layout: 'form',
					border: false,
					id: 'dreLpuUnitPanel',
					bodyStyle:'background:#DFE8F6;padding-right:0px;',
					labelWidth: 110,
					items: 
					[{
						anchor: '100%',
						name: 'LpuUnit_id',
						tabIndex: 4113,
						disabled: false,
						xtype: 'swlpuunitcombo',
						topLevel: true,
						allowBlank:false, 
						id: 'dreLpuUnit_id',
						listeners: {
							change: function(combo) {
                                var tut = form;
                                if (combo.getValue() > 0) {
                                    var period_combo = tut.findById('dreDrugRequestPeriod_id');
                                    var maxSetDate = period_combo.getFieldValue('DrugRequestPeriod_endDate');
                                    var minDisDate = period_combo.getFieldValue('DrugRequestPeriod_begDate');

                                    if (Ext.isEmpty(period_combo.getValue())) {
                                        maxSetDate = Ext.util.Format.date(new Date(),'d.m.Y');
                                        minDisDate = maxSetDate;
                                    }

                                    tut.findById('dreLpuSection_id').getStore().load({
                                        params: {
                                            Object: 'LpuSection',
                                            Lpu_id: frm.Lpu_id || getGlobalOptions().lpu_id,
                                            LpuUnit_id: combo.getValue(),
                                            LpuSection_maxSetDate: maxSetDate,
                                            LpuSection_minDisDate: minDisDate
                                        },
                                        callback: function() {
                                            tut.findById('dreLpuSection_id').setValue('');
                                            tut.findById('dreMedPersonal_id').setValue('');
                                            tut.DrugRequestPersonLoad();
                                        }
                                    });
                                } else {
                                    tut.findById('dreLpuSection_id').setValue('');
                                    tut.findById('dreMedPersonal_id').setValue('');
                                    tut.DrugRequestPersonLoad();
                                }
                            }
						}
					}]
				},
				{
					xtype: 'swlpusectioncombo',
					anchor: '100%',
					tabIndex:3,
					name: 'LpuSection_id',
					id: 'dreLpuSection_id',
					allowBlank: false,
					/*width: 290,
					listWidth: 500,*/
					tabIndex:4114,
					listeners: {
						change: function(combo) {
                            var tut = this.ownerCt.ownerCt.ownerCt;
                            if (combo.getValue() > 0) {
                                var period_combo = tut.findById('dreDrugRequestPeriod_id');
                                var begDate = period_combo.getFieldValue('DrugRequestPeriod_begDate');
                                var endDate = period_combo.getFieldValue('DrugRequestPeriod_endDate');

                                if (period_combo.getValue() > 0) {
                                    begDate = !Ext.isEmpty(begDate) ? Ext.util.Format.date(Date.parseDate(begDate,'d.m.Y'),'Y-m-d') : null;
                                    endDate = !Ext.isEmpty(endDate) ? Ext.util.Format.date(Date.parseDate(endDate,'d.m.Y'),'Y-m-d') : null;
                                } else {
                                    begDate = Ext.util.Format.date(new Date(),'Y-m-d');
                                    endDate = begDate;
                                }

                                tut.findById('dreMedPersonal_id').getStore().load({
                                    params: {
                                        LpuSection_id: combo.getValue(),
                                        IsDlo: (!getGlobalOptions().isOnko && !getGlobalOptions().isRA)?1:0,
                                        checkDloDate: true,
                                        begDate: begDate,
                                        endDate: endDate
                                    },
                                    callback: function() {
                                        tut.findById('dreMedPersonal_id').setValue('');
                                        //tut.DrugRequestPersonLoad();
                                    }
                                });

                                if (!tut.findById('dreLpuUnit_id').getValue()) {
                                    idx = combo.getStore().indexOfId(combo.getValue());
                                    if (idx<0)
                                        idx = combo.getStore().findBy(function(rec) { return rec.get('LpuSection_id') == combo.getValue(); });
                                    if (idx<0)
                                        return;
                                    var row = combo.getStore().getAt(idx);
                                    tut.findById('dreLpuUnit_id').setValue(row.data.LpuUnit_id);
                                }
                            } else {
                                tut.findById('dreMedPersonal_id').setValue('');
                                tut.DrugRequestPersonLoad();
                            }
                        }
					}
				},
				{
					xtype: 'swmedpersonalcombo',
					anchor: '100%',
					name: 'MedPersonal_id',
					id: 'dreMedPersonal_id',
					loadingText: lang['idet_poisk'],
					minChars: 1,
					minLength: 1,
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					tabIndex:4115,
					listeners:
					{
						blur:
							function(combo)
							{
								var tut = this.ownerCt.ownerCt.ownerCt; 
								tut.DrugRequestPersonLoad();
							}
					}
				},
				{
					xtype:'panel',
					layout: 'form',
					border: false,
					id: 'dreYoungChildCountPanel',
					bodyStyle:'background:#DFE8F6;padding-right:0px;',
					labelWidth: 180,
					items: 
					[{
						xtype: 'numberfield',
						maxValue: 500,
						minValue: 0,
						autoCreate: {tag: "input", size:5, maxLength: "3", autocomplete: "off"},
						fieldLabel: lang['kolichestvo_detey_do_3-h_let'],
						//anchor: '100%',
						name: 'DrugRequest_YoungChildCount',
						id: 'dreDrugRequest_YoungChildCount',
						tabIndex:4116
					}]
				}]
			},
			{
				// Правая часть параметров ввода
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .2,
				labelWidth: 110,
				items: [btnDrugRequestPrint, btnDrugRequestSetStatus, btnDrugRequestReallocate]
				
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
				//
				}
			},
			[
				{ name: 'DrugRequest_id' },
				{ name: 'DrugRequestPeriod_id' },
				{ name: 'DrugRequestStatus_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'DrugRequestTotalStatus_IsClose' },
				{ name: 'DrugRequest_YoungChildCount'}
			])
		});
		
		// Пациенты
		this.PacientPanel = new sw.Promed.ViewFrame(
		{
			//title:'Пациенты',
			id: 'DrugRequestPacientPanel',
			region: 'center',
			height: 303,
			minSize: 200,
			maxSize: 400,
			object: 'DrugRequestPerson',
			paging: true,
			pageSize: 50,
			root: 'data',
			totalProperty: 'totalCount',
			//editformclassname: 'swLpuSectionShiftEditForm',
			dataUrl: '/?c=DrugRequest&m=index&method=getPersonGrid',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'DrugRequestPerson_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', hidden: true, isparams: true},
				{name: 'Server_id', hidden: true, isparams: true},
				{name: 'PersonEvn_id', hidden: true, hideable: false},
				{name: 'DrugRequestRow_Count', hidden: true},
				{name: 'Person_SurName', width: 100, header: lang['familiya']},
				{name: 'Person_FirName', width: 100, header: lang['imya']},
				{name: 'Person_SecName', width: 100, header: lang['otchestvo']},
				//{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО'},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_Nick', header: lang['lpu_prikrepleniya'], width: 150},
				{name: 'LpuRegion_Name', header: lang['uchastok'], width: 80},
				{name: 'Person_IsBDZ', type:'checkbox', header: lang['bdz'], width: 35},
				{name: 'Person_IsFedLgot', type:'checkbox', header: lang['fed_lg'], width: 50},
				
				{name: 'Person_IsFedLgotCurr', type:'checkbox', header: lang['fed_zayavka'], width: 70},
				
				{name: 'Person_IsRefuse', type:'checkbox', header: lang['otkaz'], width: 50},
				{name: 'Person_IsRefuseNext', type:'checkbox', header: lang['otk_na_sl_god'], width: 80},
				{name: 'Person_IsRefuseCurr', type:'checkbox', header: lang['otk_zayavka'], width: 70},
				{name: 'Person_IsRegLgot', type:'checkbox', header: lang['reg_lg'], width: 50},
				
				{name: 'Person_IsRegLgotCurr', type:'checkbox', header: lang['reg_zayavka'], width: 70},
				
				{name: 'Person_Is7Noz', type:'checkbox', header: lang['7_noz'], width: 50},
				{name: 'Person_IsDead', type:'checkbox', header: lang['umer'], width: 50},
				{name: 'DrugRequestPerson_insDT', type:'date', header: lang['vnesen'], width: 70},
				{name: 'DrugRequestPerson_updDT', type:'date', header: lang['izmenen'], width: 70},
				{name: 'set', type:'int', hidden: true}
			],
			actions:
			[
				{name:'action_add', handler: function() { Ext.getCmp('DrugRequestEditForm').Actions.action_PersonAdd.execute();}},
				{name:'action_edit', disabled: true},
				{name:'action_view', disabled: true},
				{name:'action_delete', url: '/?c=DrugRequest&m=index&method=deleteDrugRequestPerson'}
			],
			onLoadData: function()
			{
				var win = Ext.getCmp('DrugRequestEditForm');
				// TODO: Подумать над таким неверным использванием getWnd
				if (
					typeof Ext.getCmp('swPersonSearchWindow') == 'object'
					&& Ext.getCmp('swPersonSearchWindow').isVisible()
					&& Ext.getCmp('swPersonSearchWindow').findById('PersonSearchViewFrame').getGrid().getSelectionModel().getSelected()
				) {
					var record = Ext.getCmp('swPersonSearchWindow').findById('PersonSearchViewFrame').getGrid().getSelectionModel().getSelected();
					Ext.getCmp('swPersonSearchWindow').findById('PersonSearchViewFrame').getGrid().getView().focusRow(0);
					Ext.getCmp('swPersonSearchWindow').findById('PersonSearchViewFrame').getGrid().getSelectionModel().selectRow(0);
				}
				// Синенькие пациенты и красненькие 
			},
			loadDrugGrid: function (sm,index,record)
			{
				var win = Ext.getCmp('DrugRequestEditForm');
				var DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
				var MedPersonal_id = win.findById('dreMedPersonal_id').getValue();
				//var DrugRequest_id = win.findById('dreDrugRequest_id').getValue();
				if (this.getCount()>0)
				{
					win.DrugPacientPanel.loadData(
					{
						globalFilters:
						{
							Person_id:record.data['Person_id'], 
							//DrugRequest_id: DrugRequest_id,
							DrugRequestPeriod_id: DrugRequestPeriod_id
							//MedPersonal_id: MedPersonal_id 
						}, 
						noFocusOnLoad:true
					});
				}
				else 
				{
					win.DrugPacientPanel.removeAll();
				}
				// ЗаBOLDим пациента
				win.PacientPanel.ViewGridStore.each(function(record) 
				{
					if (record.get('set')>0)
					{
						record.set('set', 0);
						record.commit();
					}
				});
				record.set('set', 1);
				record.commit();
				// Затычка на тыкание мышкой 
				// Условие убрать после добавления нормального поиска
				// TODO: Вообще ветку if можно убрать (и проверить)
				if (!(getWnd('swPersonSearchWindow') && getWnd('swPersonSearchWindow').isVisible()))
				{
					win.PacientPanel.ViewGridPanel.getView().focusRow(index);
					//С ExtJS 2.3.0 похоже неактуально
					//sm.selectRow(index);
				}
				else 
				{
					// TODO: Подумать над таким неверным использванием getWnd
					if (getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getSelectionModel().getSelected())
					{
						var record = getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getSelectionModel().getSelected();
						//getWnd('swPersonSearchWindow').findById('PersonSearchGrid').focus();
						getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getView().focusRow(0);
						getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getSelectionModel().selectRow(0);
					}
				}
				/*
				sm.selectRow(index);*/
			},
			onRowSelect: function (sm,index,record)
			{
				var win = Ext.getCmp('DrugRequestEditForm');
                var status = win.findById('dreDrugRequestStatus_id').getValue();
				/*
				// ЗаBOLDим пациента
				win.PacientPanel.ViewGridStore.each(function(record) 
				{
					if (record.get('set')>0)
					{
						record.set('set', 0);
						record.commit();
					}
				});
				record.set('set', 1);
				record.commit();
				*/

                win.PacientPanel.ViewActions.action_add.setDisabled(this.readOnly && (win.action == 'view' || status != 6));
                win.PacientPanel.ViewActions.action_dre_exclude.setDisabled(win.action == 'view' || Ext.isEmpty(record.get('DrugRequestPerson_id')) || status != 6);

				// ЗаTIMEим загрузку подчиненного грида
				if (!win.delayRowSelect)
					win.delayRowSelect = new Ext.util.DelayedTask();
				win.delayRowSelect.delay(600, win.PacientPanel.loadDrugGrid, win.PacientPanel, [sm,index,record]);
				win.findById('drePerson_id').setValue(record.data['Person_id']);
				if ((((win.action!='view') && (status != 2 && status != 6))
				|| (getGlobalOptions().isMinZdrav)) && (win.PacientPanel.getCount()>0))
				{
					win.findById('dreIsDrugEdit').setValue(-1);
					// Проверяем льготы пациента
					win.findById('dreDrugProtoMnn_id').enable();
					win.findById('dreDrugRequestRow_Kolvo').enable();
					win.findById('dreButtonAdd').enable();
					win.findById('dreDrugRequestType_id').enable();
					if (win.findById('dreButtonAdd').getText()==lang['izmenit'])
					{
						win.findById('dreDrugRequestRow_id').setValue('');
						win.findById('dreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = '';
						win.findById('dreDrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
						win.findById('dreButtonAdd').setText(lang['dobavit']);
						win.findById('dreIsDrug').enable();
						win.findById('dreButtonEndEdit').setVisible(false);
					}
					
					var OtkNextYear = (record.get('Person_IsRefuseCurr')=='true')?true:false;
					// Федеральная льгота
					if ((record.get('Person_IsFedLgotCurr')=='true') && (record.get('Person_IsRegLgotCurr')!='true') && (record.get('Person_IsRegLgotCurr')!='gray') && (!OtkNextYear))
					{
						if (win.findById('dreDrugRequestType_id').getValue()!=1)
						{
							// Перегружаем справочник медикаментов без сброса параметров
							win.PersonDrugProtoMnnLoad(1, '', '', win.findById('dreDrugProtoMnn_id').getRawValue(), false);
						}
						if (getGlobalOptions().isMinZdrav)
						{
							win.findById('dreDrugRequestType_id').enable();
							win.DRType_id = 0;
						}
						else 
						{
							win.findById('dreDrugRequestType_id').disable();
							win.DRType_id = 1;
						}
						//form.MessageTip.showAt([100,100]);
						//form.MessageTip.show();
						
						win.findById('dreDrugRequestType_id').setValue(1);
					}
					else if ((record.get('Person_IsFedLgotCurr')!='true') && ((record.get('Person_IsRegLgotCurr')=='true') || (record.get('Person_IsRegLgotCurr')=='gray')))
					{
						if (OtkNextYear)
						{
							// Если отказник , то никаких медикаментов 
							win.findById('dreDrugRequestType_id').setValue('');
							win.findById('dreDrugRequestType_id').disable();
							win.findById('dreDrugProtoMnn_id').disable();
							win.findById('dreDrugRequestRow_Kolvo').disable();
							win.findById('dreButtonAdd').disable();
							win.DRType_id = -1;
						}
						else 
						{
							if (win.findById('dreDrugRequestType_id').getValue()!=2)
							{
								// Перегружаем справочник медикаментов без сброса параметров
								win.PersonDrugProtoMnnLoad(2, '', '', win.findById('dreDrugProtoMnn_id').getRawValue(), false);
							}
							win.findById('dreDrugRequestType_id').disable();
							win.findById('dreDrugRequestType_id').setValue(2);
							win.DRType_id = 2;
						}
					}
					else if ((record.get('Person_IsFedLgotCurr')=='true') && ((record.get('Person_IsRegLgotCurr')=='true') || (record.get('Person_IsRegLgotCurr')=='gray')))
					{
						if (OtkNextYear)
						{
							// Если отказник , то никаких медикаментов 
							win.findById('dreDrugRequestType_id').setValue('');
							win.findById('dreDrugRequestType_id').disable();
							win.findById('dreDrugProtoMnn_id').disable();
							win.findById('dreDrugRequestRow_Kolvo').disable();
							win.findById('dreButtonAdd').disable();
							win.DRType_id = -1;
						}
						else 
						{
							if (!getGlobalOptions().isMinZdrav)
							{
								win.findById('dreDrugRequestType_id').disable();
								win.findById('dreDrugRequestType_id').setValue(1);
								win.PersonDrugProtoMnnLoad(1, '', '', win.findById('dreDrugProtoMnn_id').getRawValue(), false);
								win.DRType_id = 1;
							}
							else 
							{
								win.findById('dreDrugRequestType_id').enable();
								win.DRType_id = 0;
							}
						}
					}
					else
					{
					if ((win.action=='view') || (win.findById('dreDrugRequestStatus_id').getValue()!=3))
					{
						win.findById('dreDrugRequestType_id').setValue('');
						win.findById('dreDrugRequestType_id').disable();
						win.findById('dreDrugProtoMnn_id').disable();
						win.findById('dreDrugRequestRow_Kolvo').disable();
						win.findById('dreButtonAdd').disable();
						win.DRType_id = -1;
					}
					}
				}
			},
			focusOn: {name:'dreDrugRequestType_id',type:'field'},
			focusPrev: {name:'dreMedPersonal_id',type:'field'}
		});
		this.PacientPanel.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('set')>0)
					cls = cls+'x-grid-rowselect ';
				if (row.get('DrugRequestRow_Count')>0)
					cls = cls+'x-grid-rowblue ';
				/*
				if (row.get('Person_IsRefuseCurr')=='true')
					cls = cls+'x-grid-rowgray ';
				*/
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		//this.PacientPanel.ViewGridPanel.getSelectionModel().addListener('rowdeselect', function (sm, index, record) {alert(1);record.set('set', 0);record.commit()});
		
		// Кнопка "Добавить медикамент"
		var btnDrugAdd = new Ext.Button(this.Actions.action_DrugAdd);
		btnDrugAdd.tabIndex = 4120;
		//btnDrugAdd.id = 'dreButtonAdd';
		var btnDrugEditEndEdit = new Ext.Button(this.Actions.action_DrugEditEndEdit);
		btnDrugEditEndEdit.tabIndex = 4121;
		
		// Панелька ввода
		this.EditPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: false,
			collapsible: false,
			region: 'south',
			height: 30,
			minSize: 30,
			maxSize: 30,
			layout: 'column',
			//title: 'Ввод',
			id: 'DrugRequestEditPanel',
			items: 
			[{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .15,
				labelWidth: 30,
				items: 
				[{
					allowBlank: false,
					fieldLabel: lang['tip'],
					id: 'dreDrugRequestType_id',
					name: 'DrugRequestType_id',
					xtype: 'swdrugrequesttypecombo',
					tabIndex:4117,
					listeners: 
					{
						'beforeselect': function() 
						{
							//this.ownerCt.ownerCt.findById('dreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id =''; // :)
						},
						'change': function(combo, newValue, oldValue) 
						{
							var drugcombo = this.ownerCt.ownerCt.findById('dreDrugProtoMnn_id');
							var win = Ext.getCmp('DrugRequestEditForm');
							drugcombo.clearValue();
							drugcombo.getStore().removeAll();
							drugcombo.lastQuery = '';
							drugcombo.getStore().baseParams.ReceptFinance_id = newValue;
							drugcombo.getStore().baseParams.DrugProtoMnn_id = '';
							drugcombo.getStore().baseParams.DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
							drugcombo.getStore().baseParams.MedPersonal_id = win.findById('DrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
							drugcombo.getStore().baseParams.query = '';
							if (newValue > 0)
							{
								drugcombo.getStore().load();
							}
						}
					}
				}]
			},
			{
				layout: 'form',
				id: 'dreIsDrugPanel',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .03,
				hidden: true,
				items: 
				[{
					id: 'dreIsDrugEdit',
					name: 'IsDrugEdit',
					xtype: 'hidden',
					value: -1
				},
				{
					allowBlank: false,
					iconCls: 'checked-gray16',
					widht: 16,
					tooltip: lang['vyibirat_iz_torgovyih_naimenovaniy'],
					id: 'dreIsDrug',
					name: 'IsDrug',
					xtype: 'button',
					enableToggle: true,
					tabIndex:4117,
					toggleHandler: function (btn, state)
					{
						var cls = (state)?'checked16':'checked-gray16';
						btn.setIconClass(cls);
						btn.ownerCt.ownerCt.findById('dreDrugProtoMnn_id').getStore().baseParams.IsDrug = state?1:0;
						btn.ownerCt.ownerCt.findById('dreDrugProtoMnn_id').getStore().removeAll();
						btn.ownerCt.ownerCt.findById('dreDrugProtoMnn_id').clearValue();
					}
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .4,
				labelWidth: 80,
				items: 
				[{
					id: 'dreDrugRequestRow_id',
					name: 'DrugRequestRow_id',
					value: null,
					xtype: 'hidden'
				}, 
				{
					id: 'drePerson_id',
					name: 'Person_id',
					value: null,
					xtype: 'hidden'
				}, 
				{
					anchor: '100%',
					allowBlank: false,
					fieldLabel: lang['medikament'],
					id: 'dreDrugProtoMnn_id',
					name: 'DrugProtoMnn_id',
					xtype: 'swdrugprotomnncombo',
					tabIndex:4118,
					loadingText: lang['idet_poisk'],
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					//plugins: [new Ext.ux.translit(true)],
					queryDelay: 250,
					listeners: 
					{
						/*'beforeselect': function(combo, record, index) 
						{
							combo.setValue(record.get('Drug_id'));
							Ext.getCmp('EREF_Drug_Price').setValue(record.get('Drug_Price'));

							var drug_mnn_combo = Ext.getCmp('EREF_DrugMnnCombo');
							var drug_mnn_record = drug_mnn_combo.getStore().getById(record.get('DrugMnn_id'));
							var org_farmacy_combo = Ext.getCmp('EREF_OrgFarmacyCombo');

							drug_mnn_combo.lastQuery = '';

								if (drug_mnn_record)
								{
									drug_mnn_combo.setValue(record.get('DrugMnn_id'));
								}
								else
								{
									drug_mnn_combo.getStore().load({
										callback: function() {
											drug_mnn_combo.setValue(record.get('DrugMnn_id'));
										},
										params: {
											DrugMnn_id: record.get('DrugMnn_id')
										}
									})
								}

								if ( record.get('Drug_id') > 0 )
								{
									org_farmacy_combo.clearValue();
									org_farmacy_combo.getStore().removeAll();
									org_farmacy_combo.getStore().load({
										params: {
											Drug_id: record.get('Drug_id')
										}
									});
								}
							},*/
						'change': function() 
						{
							//this.ownerCt.ownerCt.findById('dreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = ''; // :)
						},
						'keydown': function(inp, e) 
						{
							if (e.getKey() == e.DELETE || e.getKey() == e.F4)
							{
								e.stopEvent();
								if (e.browserEvent.stopPropagation)
								{
									e.browserEvent.stopPropagation();
								}
								else
								{
									e.browserEvent.cancelBubble = true;
								}
								if (e.browserEvent.preventDefault)
								{
									e.browserEvent.preventDefault();
								}
								else
								{
									e.browserEvent.returnValue = false;
								}

								e.returnValue = false;

								if (Ext.isIE)
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								switch (e.getKey())
								{
									case e.DELETE:
										inp.clearValue();
										inp.ownerCt.ownerCt.findById('dreDrugProtoMnn_id').setRawValue(null);
										break;
									case e.F4:
										inp.onTrigger2Click();
										break;
								}
							}
						}
					},
					onTrigger2Click: function() 
					{
						
						if (this.disabled)
							return false;
						var win = Ext.getCmp('DrugRequestEditForm');
						var combo = this;
						if (!this.formList)
						{
							this.formList = new sw.Promed.swListSearchWindow(
							{
								title: lang['poisk_medikamenta'],
								id: 'DrugProtoMnnSearch',
								object: 'DrugProtoMnn',
								//editformclassname: 'swEditForm',
								//dataUrl: '/?c=DrugRequest&m=index&method=getPersonGrid',
								//stringfields: 
								//[
								//	{name: 'DrugProtoMnn_id', key: true},
								//	{name: 'DrugProtoMnn_Name', id: 'autoexpand', header: 'Наименование'},
								//	{name: 'Lpu_Nick', hidden: false, header: 'ЛПУ прикрепления', width: 100}
								//],
								store: this.getStore()
							});
						}
						if (!win.EditPanel.findById('dreDrugRequestType_id').getValue())
						{
							sw.swMsg.alert(lang['oshibka'], lang['nelzya_otkryit_formu_poiska_vyibora_poskolku_ne_ukazan_tip_zayavki'], function() {win.EditPanel.findById('dreDrugRequestType_id').focus(true, 50);});
							return false;
						}
						this.formList.show(
						{
							onSelect: function(data) 
							{
								win.PersonDrugProtoMnnLoad(data['DrugRequestType_id'], data['DrugProtoMnn_id'], data['DrugProtoMnn_Name'], '', true);
							}, 
							onHide: function() 
							{
								//combo.focus(false);;
							}, 
							IsDrug: win.findById('dreIsDrug').pressed?1:0, 
							ReceptFinance_id: win.EditPanel.findById('dreDrugRequestType_id').getValue()
						});
						
						return false;
					}
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .11,
				labelWidth: 50,
				items: 
				[{
					anchor: '100%',
					xtype: 'numberfield',
					name: 'DrugRequestRow_Kolvo',
					id:  'dreDrugRequestRow_Kolvo',
					maxValue: 9999999,
					minValue: 0,
					autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
					allowBlank: false,
					fieldLabel: lang['kol-vo'],
					tabIndex:4119
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .1,
				items: [btnDrugAdd]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .14,
				items: [btnDrugEditEndEdit]
			}]
		});
		/*
		this.MessageTip = new Ext.ToolTip(
		{
			target: form.EditPanel, 
			title: lang['vnimanie'],  
			html:lang['vyi_mojete_vyipisyivat_federalnomu_lgotniku_medikamentyi_po_regionalnoy_zayavke'], 
			hideDelay:1000, 
			showDelay: 1000, 
			autoHide: true,  
			dismissDelay: 1000
		});
		*/
		// Медикаменты по пациентам
		this.DrugPacientPanel = new sw.Promed.ViewFrame(
		{
			//title:'Пациенты',
			id: 'DrugRequestDrugPacientPanel',
			region: 'center',
			height: 200,
			minSize: 200,
			maxSize: 300,
			object: 'DrugRequestRow',
			editformclassname: '',
			dataUrl: '/?c=DrugRequest&m=index&method=getDrugRequestRow',
			toolbar: true,
			autoLoadData: false,
			/*
			saveAtOnce: false,
			saveAllParams: true,
			*/
			stringfields:
			[
				{name: 'DrugRequestRow_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugRequest_id', hidden: true, isparams: true},
				{name: 'DrugProtoMnn_id', hidden: true, isparams: true},
				{name: 'Drug_id', hidden: true, isparams: true},
				{name: 'Person_id', hidden: true, isparams: true},
				{id: 'autoexpand', name: 'DrugRequestRow_Name', header: lang['medikament']},
				{name: 'DrugRequestRow_Code', hidden: true, type: 'int', header: lang['kod'], width: 50},
				{name: 'DrugRequestRow_Kolvo', type: 'float', header: lang['kol-vo'], width: 80}, // , editor: new Ext.form.NumberField({allowBlank: false,allowNegative: false, minValue: 1, maxValue: 100000})
				{name: 'DrugRequestRow_Price', type: 'float', header: lang['tsena'], width: 80, type: 'money', align: 'right'},
				{name: 'DrugRequestRow_Summa', type: 'float', header: lang['summa'], width: 80, type: 'money', align: 'right'},
				{name: 'DrugRequestType_id', hidden: true, isparams: true},
				{name: 'DrugRequestType_Name', header: lang['tip'], width: 100},
				{name: 'MedPersonal_FIO', header: lang['vrach'], width: 200},
				{name: 'MedPersonal_id', hidden: true},
				{name: 'Lpu_id', hidden: true},
				{name: 'Lpu_Nick', header: lang['lpu'], width: 200},
				{name: 'DrugRequestRow_insDT', type: 'date', header: lang['vnesen'], width: 70},
				{name: 'DrugRequestRow_updDT', type: 'date', header: lang['izmenen'], width: 70},
				{name: 'DrugRequestRow_delDT', type: 'date', header: lang['udalen'], width: 70},
				{name: 'DrugRequestRow_DoseOnce', header: lang['razovaya_doza'], width: 100, renderer: function(v, p, r){
					return (r.get('DrugRequestRow_DoseOnce') != null) ? r.get('DrugRequestRow_DoseOnce') + ' ' + r.get('Okei_oid_NationSymbol') : '';
				}},
				{name: 'DrugRequestRow_DoseDay', header: lang['dnevnaya_doza'], width: 100, renderer: function(v, p, r){
					return (r.get('DrugRequestRow_DoseDay') != null) ? r.get('DrugRequestRow_DoseDay') + ' ' + r.get('Okei_oid_NationSymbol') : '';
				}},
				{name: 'DrugRequestRow_DoseCource', header: lang['kursovaya_doza'], width: 100, renderer: function(v, p, r){
					return (r.get('DrugRequestRow_DoseCource') != null) ? r.get('DrugRequestRow_DoseCource') + ' ' + r.get('Okei_oid_NationSymbol') : '';
				}},
				{name: 'Okei_oid', hidden: true, isparams: true},
				{name: 'Okei_oid_NationSymbol', hidden: true},
				{name: 'DrugRequestRow_Deleted', hidden: true}
			],
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', disabled: true},
				{name:'action_view', disabled: true},
				{name:'action_delete', url: '/?c=DrugRequest&m=index&method=deleteDrugRequestRow'}/*,
				{name:'action_save', url: '/?c=DrugRequest&m=index&method=saveDrugRequestRow'}*/
			],
			focusPrev: {name:'dreButtonAdd',type:'button'},
			focusOn: {name:'dreButtonSave',type:'button'},
			onLoadData: function (result)
			{
				var win = Ext.getCmp('DrugRequestEditForm');
				
				var MainLpu_id = win.findById('dreLpu_id').getValue();
				var MedPersonal_id = win.findById('dreMedPersonal_id').getValue();
                var status = win.findById('dreDrugRequestStatus_id').getValue();
				
				if (win.DrugPacientPanel.ViewGridPanel.getSelectionModel().getSelected())
				{
					var record = win.DrugPacientPanel.ViewGridPanel.getSelectionModel().getSelected();
					if ((record.get('Lpu_id')!=MainLpu_id) || (record.get('MedPersonal_id')!=MedPersonal_id))
					{
						win.DrugPacientPanel.ViewActions.action_delete.setDisabled(true);
						win.DrugPacientPanel.ViewActions.action_dose_edit.setDisabled(true);
						win.DrugPacientPanel.ViewActions.action_move_row_to_reserve.setDisabled(true);
						win.findById('dreDrugRequestRow_Kolvo').disable();
						win.findById('dreButtonAdd').disable();
					}
					else 
					{
						if (win.action!='view')
						{
							win.findById('dreDrugRequestRow_Kolvo').enable();
							win.findById('dreDrugRequestRow_Kolvo').focus(true);
							win.DrugPacientPanel.ViewActions.action_delete.setDisabled(this.readOnly || !(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
							win.DrugPacientPanel.ViewActions.action_dose_edit.setDisabled(!(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
							win.DrugPacientPanel.ViewActions.action_move_row_to_reserve.setDisabled(status != 6 || !(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
						}
					}
				}
				else 
				{
					win.DrugPacientPanel.ViewActions.action_delete.setDisabled(true);
					win.DrugPacientPanel.ViewActions.action_dose_edit.setDisabled(true);
					win.DrugPacientPanel.ViewActions.action_move_row_to_reserve.setDisabled(true);
				}
				
				// Собираем суммы
				var sumFed = 0;
				var sumReg = 0;
				var sumFedAll = 0;
				var sumRegAll = 0;
				var sumFedLimit = 0;
				var sumRegLimit = 0;
				if (result)
				{
					win.DrugPacientPanel.ViewGridStore.each(function(record) 
					{
						if ((record.get('Lpu_id')==MainLpu_id) && ((record.get('MedPersonal_id')==MedPersonal_id) || ((record.get('MedPersonal_id')=='') && (getGlobalOptions().isMinZdrav))) && (record.get('DrugRequestRow_Deleted')!=2))
						{
							if (record.get('DrugRequestType_id')==1)
								sumFed = sumFed + record.get('DrugRequestRow_Summa');
							if (record.get('DrugRequestType_id')==2)
								sumReg = sumReg + record.get('DrugRequestRow_Summa');
						}
						if (record.get('Lpu_id')!=MainLpu_id)
						{
							if (record.get('DrugRequestType_id')==1)
								sumFedAll = sumFedAll + record.get('DrugRequestRow_Summa');
							if (record.get('DrugRequestType_id')==2)
								sumRegAll = sumRegAll + record.get('DrugRequestRow_Summa');
						}
					});
				}
				sumRegAll = sumRegAll + sumReg;
				sumFedAll = sumFedAll + sumFed;
				// Установить суммы по пациенту
				// Начало быдлоблока - удалить потом как будет реализована нормально периодичность лимитов
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 12010)
				{
					var normativ_fed_lgot = 400;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 75;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 22010)
				{
					var normativ_fed_lgot = 560;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 125;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 32010)
				{
					var normativ_fed_lgot = 590;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 190;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 42010)
				{
					var normativ_fed_lgot = 570;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 190;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 12011)
				{
					var normativ_fed_lgot = 600;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 100;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 22011)
				{
					var normativ_fed_lgot = 590;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 110;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 32011)
				{
					var normativ_fed_lgot = 590;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 130;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 42011)
				{
					var normativ_fed_lgot = 590;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 130;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 12012)
				{
					var normativ_fed_lgot = 630;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 140;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 22012)
				{
					var normativ_fed_lgot = 630;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 140;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 52012)
				{
					var normativ_fed_lgot = 630;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 140;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 32012)
				{
					var normativ_fed_lgot = 800;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 180;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 42012)
				{
					var normativ_fed_lgot = 800;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 180;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue() == 12013)
				{
					var normativ_fed_lgot = 700;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 220;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue().inlist([22013, 32013, 42013, 12014, 22014, 32014]))
				{
					var normativ_fed_lgot = 650;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 250;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue().inlist([42014]))
				{
					var normativ_fed_lgot = 380;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 50;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue().inlist([12015]))
				{
					var normativ_fed_lgot = 390;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 70;
					var koef_reg_lgot = 1;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue().inlist([22015, 32015, 42015]))
				{
					var normativ_fed_lgot = 390;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 75;
					var koef_reg_lgot = 1;
				}
				var sumFedLimit = normativ_fed_lgot*3*koef_fed_lgot;
				var sumRegLimit = normativ_reg_lgot*3*koef_reg_lgot;
				
				if (win.findById('dreDrugRequestPeriod_id').getValue().inlist([62036]))
				{
					var normativ_fed_lgot = 310;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 90;
					var koef_reg_lgot = 1;
					sumFedLimit = normativ_fed_lgot*12*koef_fed_lgot;
					sumRegLimit = normativ_reg_lgot*12*koef_reg_lgot;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue().inlist([62039]))
				{
					var normativ_fed_lgot = 408;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 130;
					var koef_reg_lgot = 1;
					sumFedLimit = normativ_fed_lgot*12*koef_fed_lgot;
					sumRegLimit = normativ_reg_lgot*12*koef_reg_lgot;
				}
				if (win.findById('dreDrugRequestPeriod_id').getValue().inlist([62157]))
				{
					var normativ_fed_lgot = 425;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 138;
					var koef_reg_lgot = 1;
					sumFedLimit = normativ_fed_lgot*12*koef_fed_lgot;
					sumRegLimit = normativ_reg_lgot*12*koef_reg_lgot;
				}
                if (win.findById('dreDrugRequestPeriod_id').getValue().inlist([62161,62190]))
                {
                    var normativ_fed_lgot = 573;
                    var koef_fed_lgot = 1;
                    var normativ_reg_lgot = 185;
                    var koef_reg_lgot = 1;
                    sumFedLimit = normativ_fed_lgot*12*koef_fed_lgot;
                    sumRegLimit = normativ_reg_lgot*12*koef_reg_lgot;
                }
				if (win.findById('dreDrugRequestPeriod_id').getValue().inlist([62201]))
				{
					var normativ_fed_lgot = 649;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 210;
					var koef_reg_lgot = 1;
					sumFedLimit = normativ_fed_lgot*12*koef_fed_lgot;
					sumRegLimit = normativ_reg_lgot*12*koef_reg_lgot;
				}
				
				// Конец быдлоблока 
				/*
				var sumFedLimit = getGlobalOptions().normativ_fed_lgot*3*getGlobalOptions().koef_fed_lgot;
				var sumRegLimit = getGlobalOptions().normativ_reg_lgot*3*getGlobalOptions().koef_reg_lgot;
				*/
				/*
				if (sumFedK<sumFed)
					var sumFed_string = '<span style="color:red;">'+sw.Promed.Format.rurMoney(sumFed)+' ('+sw.Promed.Format.rurMoney(Math.round((sumFed-sumFedK)*100)/100)+')</span>';
				else
					var sumFed_string = sw.Promed.Format.rurMoney(sumFed);
				
				if (sumRegK<sumReg)
					var sumReg_string = '<span style="color:red;">'+sw.Promed.Format.rurMoney(sumReg)+' ('+sw.Promed.Format.rurMoney(Math.round((sumReg-sumRegK)*100)/100)+')</span>';
				else
					var sumReg_string = sw.Promed.Format.rurMoney(sumReg);
				*/
				sumFed = sw.Promed.Format.rurMoney(sumFed);
				sumFedAll = sw.Promed.Format.rurMoney(sumFedAll);
				sumFedLimit = sw.Promed.Format.rurMoney(sumFedLimit);
				sumReg = sw.Promed.Format.rurMoney(sumReg);
				sumRegAll = sw.Promed.Format.rurMoney(sumRegAll);
				sumRegLimit = sw.Promed.Format.rurMoney(sumRegLimit);
				
				win.SumDPTpl.overwrite(win.SumDPPanel.body, {sumReg:sumReg, sumFed:sumFed, sumFedAll:sumFedAll, sumFedLimit:sumFedLimit, sumRegAll:sumRegAll,sumRegLimit:sumRegLimit});
			},
			onRowSelect: function (sm,index,record) {
				var win = Ext.getCmp('DrugRequestEditForm');
				var medpersonal_id = win.findById('dreMedPersonal_id').getValue();
                var DrugRequest_id = win.DrugRequest_id || wnd.findById('dreDrugRequest_id').getValue();
                var status = win.findById('dreDrugRequestStatus_id').getValue();

				this.setActionDisabled('action_dose_edit', !(isLpuAdmin() || record.get('MedPersonal_id') == medpersonal_id || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == '')));
				this.setActionDisabled('action_move_row_to_reserve', status != 6 || !(record.get('DrugRequest_id') == DrugRequest_id &&(isLpuAdmin() || record.get('MedPersonal_id') == medpersonal_id || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == ''))));
				this.setActionDisabled('action_delete', this.readOnly || !(isLpuAdmin() || record.get('MedPersonal_id') == medpersonal_id || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == '')));

				if ((((win.action!='view') && (!win.findById('dreDrugRequestStatus_id').getValue().inlist([2,3])))
				|| (getGlobalOptions().isMinZdrav)) && (win.DrugPacientPanel.getCount()>0))
				{
					win.findById('dreDrugRequestRow_id').setValue(record.get('DrugRequestRow_id'));
					win.findById('dreDrugRequestType_id').setValue(record.get('DrugRequestType_id'));
					win.findById('dreDrugRequestRow_Kolvo').setValue(record.get('DrugRequestRow_Kolvo'));
					// Берем состояние из грида для редактирования 
					if (record.get('DrugProtoMnn_id')=='')
						win.findById('dreIsDrugEdit').setValue(1);
					else 
						win.findById('dreIsDrugEdit').setValue(0);
					win.findById('dreDrugProtoMnn_id').setValue(record.get('DrugProtoMnn_id'));
					win.findById('dreButtonAdd').setText(lang['izmenit']);
					win.findById('dreButtonEndEdit').setVisible(status != 6);
					win.findById('dreDrugProtoMnn_id').clearValue();
					win.findById('dreDrugProtoMnn_id').getStore().removeAll();
					win.findById('dreDrugProtoMnn_id').lastQuery = '';
					var med = (record.get('DrugProtoMnn_id')=='')?record.get('Drug_id'):record.get('DrugProtoMnn_id');
					
					win.findById('dreDrugProtoMnn_id').getStore().loadData([{
					 	'DrugProtoMnn_Name' : record.get('DrugProtoMnn_Name'),
						'DrugProtoMnn_Code' : record.get('DrugProtoMnn_Code'),
						'DrugProtoMnn_id' : med,
						'DrugMnn_id' : null,
						'ReceptFinance_id' : record.get('DrugRequestType_id'),
						'DrugProtoMnn_Price' : record.get('DrugRequestRow_Price')
					}]);

					win.findById('dreDrugProtoMnn_id').setValue(med);
					win.findById('dreDrugProtoMnn_id').setRawValue(record.get('DrugRequestRow_Name'));
					win.findById('dreDrugRequestRow_Kolvo').focus(true);
					win.findById('dreDrugRequestType_id').disable();
					win.findById('dreDrugProtoMnn_id').disable();
					win.findById('dreIsDrug').disable();
					if (status != 6 && ((record.get('Lpu_id')==win.findById('dreLpu_id').getValue() && record.get('MedPersonal_id') == medpersonal_id) || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == '')))
					{
						win.findById('dreDrugRequestRow_Kolvo').enable();
						win.findById('dreDrugRequestRow_Kolvo').focus(true);
						win.DrugPacientPanel.ViewActions.action_delete.setDisabled(this.readOnly || !(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == medpersonal_id) || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == '')));
						win.findById('dreButtonAdd').enable();
					}
					else 
					{
						win.DrugPacientPanel.ViewActions.action_delete.setDisabled(true);
						win.findById('dreDrugRequestRow_Kolvo').disable();
						win.findById('dreButtonAdd').disable();
					}
				}
				else // Если не просмотр, то оставляем возможность удаления записей в случае, если заявка утвержденная 
					if ((win.action!='view') && (win.DrugPacientPanel.getCount()>0) && (win.findById('dreDrugRequestStatus_id').getValue()==3))
					{
						win.DrugPacientPanel.ViewActions.action_delete.setDisabled(true);
						if ((record.get('Lpu_id')==win.findById('dreLpu_id').getValue()) || (getGlobalOptions().isMinZdrav))
						{
							if (record.get('DrugRequestRow_Deleted')!=2)
							{
								win.DrugPacientPanel.ViewActions.action_delete.setDisabled(this.readOnly || !(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == medpersonal_id) || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == '')));
							}
						}
					}
			}
		});
		
		this.DrugPacientPanel.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('DrugRequestRow_Deleted')>0)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		var sumTplMark = 
		[
			'<div style="height:32px;padding-top:0px;font-weight:bold;">'+
			'<span style="color:#444;">&nbsp;&nbsp;Суммы по выбранному пациенту: Федеральная заявка (свои/общ/'+(getRegionNick()=='perm'?'норматив':'лимит')+'):</span> {sumFed} / {sumFedAll} / {sumFedLimit}<br/>'+
			'<span style="color:#444;">&nbsp;&nbsp;Суммы по выбранному пациенту: Региональная заявка (свои/общ/'+(getRegionNick()=='perm'?'норматив':'лимит')+'):</span> {sumReg} / {sumRegAll} / {sumRegLimit}</div>'
			//'Product Group: {ProductGroup}<br/>'
		];
		this.SumDPTpl = new Ext.Template(sumTplMark);
		this.SumDPPanel = new Ext.Panel(
		{
			id: 'SumDPPanel',
			bodyStyle: 'padding:2px',
			layout: 'fit',
			region: 'south',
			border: true,
			frame: false,
			height: 32,
			maxSize: 32,
			html: ''
		});
		
		// Закладка #2 - Резерв 
		// Кнопка "Добавить медикамент" в резерве 
		var btnDrugReserveAdd = new Ext.Button(this.Actions.action_DrugReserveAdd);
		btnDrugReserveAdd.tabIndex = 4124;
		
		var btnDrugReserveEndEdit = new Ext.Button(this.Actions.action_DrugReserveEndEdit);
		btnDrugReserveEndEdit.tabIndex = 4125;
		
		
		//btnDrugAdd.id = 'dreButtonAdd';

		// Панелька ввода для ввода резерва   
		this.EditReservePanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: false,
			collapsible: false,
			height: 30,
			minSize: 30,
			maxSize: 30,
			region: 'north',
			layout: 'column',
			//title: 'Ввод',
			id: 'DrugRequestEditReservePanel',
			items: 
			[{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .15,
				labelWidth: 30,
				items: 
				[{
					allowBlank: false,
					fieldLabel: lang['tip'],
					id: 'drerDrugRequestType_id',
					name: 'DrugRequestType_id',
					xtype: 'swdrugrequesttypecombo',
					tabIndex:4117,
					listeners: 
					{
						'beforeselect': function() 
						{
							//this.ownerCt.ownerCt.findById('drerDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id =''; // :)
						},
						'change': function(combo, newValue, oldValue) 
						{
							var drugcombo = this.ownerCt.ownerCt.findById('drerDrugProtoMnn_id');
							var win = Ext.getCmp('DrugRequestEditForm');
							drugcombo.clearValue();
							drugcombo.getStore().removeAll();
							drugcombo.lastQuery = '';
							drugcombo.getStore().baseParams.ReceptFinance_id = newValue;
							drugcombo.getStore().baseParams.DrugProtoMnn_id = '';
							drugcombo.getStore().baseParams.DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
							drugcombo.getStore().baseParams.MedPersonal_id = win.findById('DrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
							drugcombo.getStore().baseParams.query = '';
							if (newValue > 0)
							{
								drugcombo.getStore().load();
							}
						}
					}
				}]
			},
			{
				layout: 'form',
				id: 'drerIsDrugPanel',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .03,
				hidden: true,
				items: 
				[{
					id: 'drerIsDrugEdit',
					name: 'IsDrugReserveEdit',
					xtype: 'hidden',
					value: -1
				},
				{
					allowBlank: false,
					iconCls: 'checked-gray16',
					widht: 16,
					tooltip: lang['vyibirat_iz_torgovyih_naimenovaniy'],
					id: 'drerIsDrug',
					name: 'IsDrugReserve',
					xtype: 'button',
					enableToggle: true,
					tabIndex:4117,
					toggleHandler: function (btn, state)
					{
						var cls = (state)?'checked16':'checked-gray16';
						btn.setIconClass(cls);
						btn.ownerCt.ownerCt.findById('drerDrugProtoMnn_id').getStore().baseParams.IsDrug = state?1:0;
						btn.ownerCt.ownerCt.findById('drerDrugProtoMnn_id').getStore().removeAll();
						btn.ownerCt.ownerCt.findById('drerDrugProtoMnn_id').clearValue();
					}
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .4,
				labelWidth: 80,
				items: 
				[{
					id: 'drerDrugRequestRow_id',
					name: 'DrugRequestRow_id',
					value: null,
					xtype: 'hidden'
				}, 
				{
					anchor: '100%',
					allowBlank: false,
					fieldLabel: lang['medikament'],
					id: 'drerDrugProtoMnn_id',
					name: 'DrugProtoMnn_id',
					xtype: 'swdrugprotomnncombo',
					tabIndex:4118,
					loadingText: lang['idet_poisk'],
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					queryDelay: 250,
					listeners: 
					{
						'change': function() 
						{
							this.ownerCt.ownerCt.findById('drerDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = ''; // :)
						},
						'keydown': function(inp, e) 
						{
							if (e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4)
							{
								e.stopEvent();
								if (e.browserEvent.stopPropagation)
								{
									e.browserEvent.stopPropagation();
								}
								else
								{
									e.browserEvent.cancelBubble = true;
								}
								if (e.browserEvent.preventDefault)
								{
									e.browserEvent.preventDefault();
								}
								else
								{
									e.browserEvent.returnValue = false;
								}

								e.returnValue = false;

								if (Ext.isIE)
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								switch (e.getKey())
								{
									case Ext.EventObject.DELETE:
										inp.clearValue();
										inp.ownerCt.ownerCt.findById('drerDrugProtoMnn_id').setRawValue(null);
										break;
									case Ext.EventObject.F4:
										inp.onTrigger2Click();
										break;
								}
							}
						}
					},
					onTrigger2Click: function() 
					{
						return false;
					}
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .11,
				labelWidth: 50,
				items: 
				[{
					anchor: '100%',
					xtype: 'numberfield',
					name: 'DrugRequestRow_Kolvo',
					id:  'drerDrugRequestRow_Kolvo',
					maxValue: 9999999,
					minValue: 0,
					autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
					allowBlank: false,
					fieldLabel: lang['kol-vo'],
					tabIndex:4119
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .1,
				items: [btnDrugReserveAdd]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .15,
				items: [btnDrugReserveEndEdit]
			}]
		});
		
		// Медикаменты по резерву 
		this.DrugReservePanel = new sw.Promed.ViewFrame(
		{
			id: 'DrugRequestDrugReservePacientPanel',
			region: 'center',
			height: 303,
			minSize: 200,
			maxSize: 400,
			object: 'DrugRequestRow',
			editformclassname: '',
			dataUrl: '/?c=DrugRequest&m=index&method=getDrugRequestRow',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'DrugRequestRow_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugRequest_id', hidden: true, isparams: true},
				{name: 'DrugProtoMnn_id', hidden: true, isparams: true},
				{id: 'autoexpand', name: 'DrugRequestRow_Name', header: lang['medikament']},
				{name: 'DrugRequestRow_Code', hidden: true, type: 'int', header: lang['kod'], width: 50},
				{name: 'DrugRequestRow_Kolvo', type: 'float', header: lang['kol-vo'], width: 80},
				{name: 'DrugRequestRow_Price', type: 'float', header: lang['tsena'], width: 80},
				{name: 'DrugRequestRow_Summa', type: 'money', align:'right', header: lang['summa'], width: 80},
				{name: 'DrugRequestType_id', hidden: true, isparams: true},
				{name: 'DrugRequestType_Name', header: lang['tip'], width: 100},
				{name: 'MedPersonal_FIO', header: lang['vrach'], width: 200},
				{name: 'MedPersonal_id', hidden: true},
				{name: 'Lpu_id', hidden: true},
				{name: 'Lpu_Nick', header: lang['lpu'], width: 200},
				{name: 'DrugRequestRow_insDT', type: 'date', header: lang['vnesen'], width: 70},
				{name: 'DrugRequestRow_updDT', type: 'date', header: lang['izmenen'], width: 70},
				{name: 'DrugRequestRow_delDT', type: 'date', header: lang['udalen'], width: 70},
				{name: 'DrugRequestRow_Deleted', hidden: true}
			],
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', disabled: true},
				{name:'action_view', disabled: true},
				{name:'action_delete', url: '/?c=DrugRequest&m=index&method=deleteDrugRequestRow'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			focusPrev: {name:'drerButtonAdd',type:'button'},
			focusOn: {name:'dreButtonSave',type:'button'},
			onLoadData: function ()
			{
				var win = Ext.getCmp('DrugRequestEditForm');
				var medpersonal_id = win.findById('dreMedPersonal_id').getValue();

				if (win.DrugReservePanel.ViewGridPanel.getSelectionModel().getSelected())
				{
					var record = win.DrugReservePanel.ViewGridPanel.getSelectionModel().getSelected();
					if ((record.get('Lpu_id')==win.findById('dreLpu_id').getValue()) && (record.get('MedPersonal_id')==medpersonal_id))
					{
						win.findById('drerDrugRequestRow_Kolvo').enable();
						win.findById('drerDrugRequestRow_Kolvo').focus(true);
						win.DrugReservePanel.ViewActions.action_delete.setDisabled(this.readOnly || !(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == medpersonal_id) || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == '')));
					}
					else 
					{
						win.DrugReservePanel.ViewActions.action_delete.setDisabled(true);
						win.findById('drerDrugRequestRow_Kolvo').disable();
						win.findById('drerButtonAdd').disable();
					}
				}
				else 
				{
					win.DrugPacientPanel.ViewActions.action_delete.setDisabled(true);
				}
			},
			onRowSelect: function (sm,index,record)
			{
				var win = Ext.getCmp('DrugRequestEditForm');
				var medpersonal_id = win.findById('dreMedPersonal_id').getValue();
                var status = win.findById('dreDrugRequestStatus_id').getValue();

				this.setActionDisabled('action_edit', this.readOnly || !(isLpuAdmin() || record.get('MedPersonal_id') == medpersonal_id || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == '')));
				this.setActionDisabled('action_delete', this.readOnly || !(isLpuAdmin() || record.get('MedPersonal_id') == medpersonal_id || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == '')));

				if ((((win.action!='view') && (!win.findById('dreDrugRequestStatus_id').getValue().inlist([2,3])))
				|| (getGlobalOptions().isMinZdrav)) && (win.DrugReservePanel.getCount()>0))
				{
					win.findById('drerDrugRequestRow_id').setValue(record.get('DrugRequestRow_id'));
					win.findById('drerDrugRequestType_id').setValue(record.get('DrugRequestType_id'));
					win.findById('drerDrugRequestRow_Kolvo').setValue(record.get('DrugRequestRow_Kolvo'));
					//win.findById('drreDrugProtoMnn_id').setValue(record.get('DrugProtoMnn_id'));
					win.findById('drerButtonAdd').setText(lang['izmenit']);
					if (record.get('DrugProtoMnn_id')=='')
						win.findById('drerIsDrugEdit').setValue(1);
					else 
						win.findById('drerIsDrugEdit').setValue(0);

					win.findById('drerButtonEndEdit').setVisible(status != 6);
					win.findById('drerDrugProtoMnn_id').clearValue();
					win.findById('drerDrugProtoMnn_id').getStore().removeAll();
					win.findById('drerDrugProtoMnn_id').lastQuery = '';
					win.findById('drerDrugProtoMnn_id').getStore().baseParams.ReceptFinance_id = record.get('DrugRequestType_id');
					win.findById('drerDrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('dreDrugRequestPeriod_id').getValue();
					win.findById('drerDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = record.get('DrugProtoMnn_id');
					win.findById('drerDrugProtoMnn_id').getStore().baseParams.query = '';
					var med = (record.get('DrugProtoMnn_id')=='')?record.get('Drug_id'):record.get('DrugProtoMnn_id');
						
						win.findById('drerDrugProtoMnn_id').getStore().loadData([{
							'DrugProtoMnn_Name' : record.get('DrugProtoMnn_Name'),
							'DrugProtoMnn_Code' : record.get('DrugProtoMnn_Code'),
							'DrugProtoMnn_id' : med,
							'DrugMnn_id' : null,
							'ReceptFinance_id' : record.get('DrugRequestType_id'),
							'DrugProtoMnn_Price' : record.get('DrugRequestRow_Price')
						}]);
						
						win.findById('drerDrugProtoMnn_id').setValue(med);
						win.findById('drerDrugProtoMnn_id').setRawValue(record.get('DrugRequestRow_Name'));
					/*
					win.findById('drerDrugProtoMnn_id').getStore().load(
					{
						callback: function()
						{
							//win.findById('dreDrugProtoMnn_id').setValue(win.findById('dreDrugProtoMnn_id').getValue());
							win.findById('drerDrugProtoMnn_id').setValue(record.get('DrugProtoMnn_id'));
							win.findById('drerDrugProtoMnn_id').setRawValue(record.get('DrugRequestRow_Name'));
							win.findById('drerDrugRequestRow_Kolvo').focus(true);
							//alert(win.findById('dreDrugProtoMnn_id').getValue());
						}
					});
					*/
					win.findById('drerDrugRequestType_id').disable();
					win.findById('drerDrugProtoMnn_id').disable();
					win.findById('drerIsDrug').disable();
					if ((record.get('Lpu_id')==win.findById('dreLpu_id').getValue()) && ((record.get('MedPersonal_id')==win.findById('dreMedPersonal_id').getValue()) || (getGlobalOptions().isMinZdrav)) && status != 6)
					{
						win.findById('drerButtonAdd').enable();
						win.findById('drerDrugRequestRow_Kolvo').enable();
						win.findById('drerDrugRequestRow_Kolvo').focus(true);
						win.DrugReservePanel.ViewActions.action_delete.setDisabled(this.readOnly || !(isLpuAdmin() || record.get('MedPersonal_id') == medpersonal_id || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == '')));
					}
					else 
					{
						win.DrugReservePanel.ViewActions.action_delete.setDisabled(true);
						win.findById('drerDrugRequestRow_Kolvo').disable();
						win.findById('drerButtonAdd').disable();
					}
				}
				else // Если не просмотр, то оставляем возможность удаления записей в случае, если заявка утвержденная 
					if ((win.action!='view') && (win.DrugReservePanel.getCount()>0) && (win.findById('dreDrugRequestStatus_id').getValue()==3))
					{
						win.DrugReservePanel.ViewActions.action_delete.setDisabled(true);
						if ((record.get('Lpu_id')==win.findById('dreLpu_id').getValue()) && ((record.get('MedPersonal_id')==win.findById('dreMedPersonal_id').getValue()) || (getGlobalOptions().isMinZdrav)))
						{
							if (record.get('DrugRequestRow_Deleted')!=2)
							{
								win.DrugReservePanel.ViewActions.action_delete.setDisabled(this.readOnly || !(isLpuAdmin() || record.get('MedPersonal_id') == medpersonal_id || (getGlobalOptions().isMinZdrav && record.get('MedPersonal_id') == '')));
							}
						}
					}
			}
		});
		
		this.DrugReservePanel.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('DrugRequestRow_Deleted')>0)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		this.PersonTab = new Ext.TabPanel(
		{
			resizeTabs:true,
			region: 'center',
			id: 'DrugRequestPersonTab',
			plain: true,
			activeTab:0,
			enableTabScroll:true,
			minTabWidth: 120,
			autoScroll: true,
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			layoutOnTabChange: true,
			listeners:
			{
                tabchange:function (tab, panel) {
                    var els = '';
                    var type = 0;
                    if (els == '') {
                        els = panel.findByType('textfield', false);
                        type = 1;
                    }
                    if (els == '') {
                        els = panel.findByType('combo', false);
                        type = 1;
                    }
                    if (els == '') {
                        els = panel.findByType('grid', false);
                        type = 2;
                    }
                    if (els == '') {
                        type = 0;
                    }
                    var el;
                    if (type != 0)
                        el = els[0];
                    if (el != 'undefined' && el.focus && type == 1) {
                        el.focus(true, 100);
                    }
                    else if (el != 'undefined' && el.focus && type == 2) {
                        if (el.getStore().getCount() > 0) {
                            el.getView().focusRow(0);
                            el.getSelectionModel().selectFirstRow();
                        }
                    }
                    var win = Ext.getCmp('DrugRequestEditForm');
                    // Далее взависимости от таба
                    if (tab.getActiveTab().id == 'tab_person') {

                    }
                    else
                    if (tab.getActiveTab().id == 'tab_reserve') {
                        if (win.DrugReservePanel.getCount() == 0) {
                            win.DrugReserveLoad(false);
                        }
                    }
                }
			},
			items:
			[{
				title: lang['po_patsientam'],
				layout:'border',
				defaults: {split: true},
				id: 'tab_pacient',
				iconCls: 'info16',
				//header:false,
				border:false,
				items: 
				[
					{
						border: false,
						layout:'border',
						region: 'center',
						items: [form.PacientPanel, form.EditPanel]
					},
					{
						layout:'border',
						border: false,
						height: 230,
						region: 'south',
						//defaults: {split: true},
						items: [form.DrugPacientPanel, form.SumDPPanel]
					}
				]
			},
			{
				title: lang['rezerv'],
				layout:'border',
				id: 'tab_reserve',
				iconCls: 'info16',
				defaults: {split: true},
				border:false,
				items: 
				[
					form.EditReservePanel,
					form.DrugReservePanel
				]
			}]
		});

		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			items:
			[
				form.ParamsPanel,
				form.PersonTab
			]
		});
		sw.Promed.swDrugRequestEditForm.superclass.initComponent.apply(this, arguments);
		this.PacientPanel.addListenersFocusOnFields();
		this.DrugPacientPanel.addListenersFocusOnFields();
	}

});
