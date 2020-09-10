/**
 * amm_SprNacCalForm - окно просмотра Национального календаря прививок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltdbutto
 * @author       Нигматуллин Тагир
 * @version      июль 2012
 * @comment      Префикс для id компонентов regv (amm_JournalViewWindow)
 */


sw.Promed.amm_SprNacCalForm = Ext.extend(sw.Promed.BaseForm, {
    title: 'Национальный календарь прививок',
    titleBase: 'Национальный календарь прививок',
    maximized: true,
    maximizable: true,
    minWidth: 850,
    border: false,
    buttonAlign: "right",
    layout: 'border',
    id: 'amm_SprNacCalForm',
//	objectSrc: '/jscore/Forms/Vaccine/amm_SprNacCalForm.js',
    region: 'north',
    buttons:
            [
                //HelpButton(this, 1),
                {text: BTN_FRMHELP,
                    iconCls: 'help16',
                    //tabIndex : TABINDEX_VACMAINFRM + 21,
                    handler: function(button, event)
                    {
                        ShowHelp(this.ownerCt.titleBase);
                    }
                },
                {
                    text: BTN_GRIDPRINT,
                    tooltip: BTN_GRIDPRINT,
                    iconCls: 'print16',
                    handler: function()
                    {
                        Ext.getCmp('amm_SprNC').printRecords();
                    }
                },
                {
                    text: BTN_FRMCLOSE,
                    //tabIndex  : -1,
                    tooltip: 'Закрыть структуру',
                    iconCls: 'cancel16',
                    handler: function()
                    {
                        this.ownerCt.hide();
                    }
                }
            ],

    vacAccess: function()
    {
        var result = false;

        //if (sw.Promed.vac.utils.vacSprAccesFull(getGlobalOptions().pmuser_id) || isAdmin)
        if ((getGlobalOptions().vacSprAccesFull == 1) || isAdmin)
            result = true;

        return result;  },
    initComponent: function() {


        frms = this;
        frms.SprNacCalEditWindow = getWnd('amm_SprNacCalEditWindow');

        this.ViewFrame = new sw.Promed.ViewFrame(
                {
                    id: 'amm_SprNC',
                    object: 'amm_SprNC',
//                        editformclassname: 'amm_Kard063',
                    dataUrl: '/?c=Vaccine_List&m=getNCGrid',
                    //C_SPRVACCINE_EDIT,
                    region: 'center',
                    toolbar: true,
                    setReadOnly: false,
                    autoLoadData: true,
//                         cls: 'txtwrap',
//                         setActionDisabled: ('action_add', true),

                    stringfields:
                            [
                                {name: 'NationalCalendarVac_id', type: 'int', header: 'ID', key: true},
                                {name: 'NationalCalendarVac_Scheme_id', type: 'string', header: 'Идентификатор схемы', width: 100, hidden: false},
                                {name: 'NationalCalendarVac_AgeRange', type: 'string', header: 'Возрастной диапазон', id: 'autoexpand', width: 100},
                                {name: 'vaccineTypeName', type: 'string', header: 'Название прививки', width: 250},
                                {name: 'NationalCalendarVac_typeName', type: 'string', header: 'Вид иммунизации', width: 150},
                                {name: 'PeriodVacName', type: 'string', header: 'Периодичность', width: 150},
                                {name: 'SequenceVac', type: 'int', header: 'Очередность прививки', hidden: true},
                                {name: 'max_SequenceVac', type: 'int', header: 'Максимальное количество в схеме', hidden: true},
                                 {name: 'VaccineType_SignScheme', type: 'checkbox', header: 'Наличие схемы вакцинации', width: 150},
                                {name: 'VaccineType_SignEmergency', type: 'checkbox', header: 'Экстренная вакцинация', width: 150},
                                {name: 'VaccineType_SignEpidem', type: 'checkbox', header: 'По эпид. показаниям', width: 150}

                            ],
                    listeners: {
                        'success': function(source, params) {
                            /* source - string - источник события (например форма)
                             * params - object - объект со свойствами в завис-ти от источника
                             */
                            sw.Promed.vac.utils.consoleLog('success | ' + source);
                            switch (source) {
                                case 'amm_SprNC':
                                    Ext.getCmp('amm_SprNC').ViewGridPanel.getStore().reload();
                                    break;
                                case 'amm_SprNacCalEditWindow':
                                    Ext.getCmp('amm_SprNC').ViewGridPanel.getStore().reload();
                                    break;
                            }
                        }
                    },
                    actions:
                            [
                                {
                                    name: 'action_add',
                                    disabled: !isSuperAdmin(),
                                    hidden: !isSuperAdmin(),
                                    handler: function() {
                                        var record = {
                                            'New': true
                                        };
                                        sw.Promed.vac.utils.callVacWindow({
                                            record: record,
                                            type1: 'btnForm',
                                            type2: 'btnSprNacCalEditWindow'
                                        }, this.findById('amm_SprNC'));
                                    }.createDelegate(this)
                                },
                                {
                                    disabled: !isSuperAdmin(),
                                    hidden: !isSuperAdmin(),
                                    name: 'action_edit',
                                    handler: function() {
                                        var record = {
                                            'New': false,
                                            'NationalCalendarVac_id': this.findById('amm_SprNC').getGrid().getSelectionModel().getSelected().data.NationalCalendarVac_id

                                        };
                                        sw.Promed.vac.utils.callVacWindow({
                                            record: record,
                                            type1: 'btnForm',
                                            type2: 'btnSprNacCalEditWindow'
                                        }, this.findById('amm_SprNC'));
                                    }.createDelegate(this)
                                },
                                {//Удаление записи из национального календаря
                                    disabled: !isSuperAdmin(),
                                    hidden: !isSuperAdmin(),
                                    name: 'action_delete',
                                    handler: function()
                                    {
                                        var record = Ext.getCmp('amm_SprNC').getGrid().getSelectionModel().getSelected();
                                        if (record.get('SequenceVac') != record.get('max_SequenceVac')) {
                                            sw.swMsg.alert('Удаление записи', 'Запись удалить невозможно! <br> Удаляется только последняя запись в схеме!');
                                            return;
                                        }
                                        sw.swMsg.show({
                                            buttons: Ext.Msg.YESNO,
                                            fn: function(buttonId, text, obj) {
                                                if (buttonId === 'yes') {
                                                    var params = {
                                                        'NationalCalendarVac_id': record.get('NationalCalendarVac_id')
                                                    };

                                                    Ext.Ajax.request({
                                                        url: '/?c=VaccineCtrl&m=deleteSprNC',
                                                        method: 'POST',
                                                        params: params,
                                                        success: function(response, opts) {
                                                            sw.Promed.vac.utils.consoleLog(response);
                                                            if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
//												Ext.getCmp(params.parent_id).fireEvent('success', 'amm_SprNC',
                                                                Ext.getCmp('amm_SprNC').fireEvent('success', 'amm_SprNC',
                                                                        {}
                                                                );
                                                            }
                                                        }
                                                    });
                                                }
                                            }.createDelegate(this),
                                            icon: Ext.MessageBox.QUESTION,
                                            msg: 'Удалить запись из национального календаря?',
                                            title: 'Удаление записи из национального календаря'
                                        });

                                    }.createDelegate(this)
                                },
                                {
                                    name: 'action_view',
                                    hidden: true
                                }
                            ]
                });

        Ext.apply(this, {
            items: [
                this.ViewFrame
            ]});

        sw.Promed.amm_SprNacCalForm.superclass.initComponent.apply(this, arguments);
    },
    show: function() {
        sw.Promed.amm_SprNacCalForm.superclass.show.apply(this, arguments);
    }

});

