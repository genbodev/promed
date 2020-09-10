/**
 * amm_SprVaccineTypeForm - окно просмотра Справочника прививок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltdbutto
 * @author       Нигматуллин Тагир
 * @version      январь 2015
 * @comment      Префикс для id компонентов regv (amm_JournalViewWindow)
 */


sw.Promed.amm_SprVaccineTypeForm = Ext.extend(sw.Promed.BaseForm, {
    title: 'Справочник прививок',
    titleBase: 'Справочник прививок',
    maximized: true,
    maximizable: true,
    minWidth: 850,
    border: false,
    buttonAlign: "right",
    layout: 'border',
    id: 'amm_SprVaccineTypeForm',
    objectSrc: '/jscore/Forms/Vaccine/amm_SprVaccineTypeForm.js',
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

    initComponent: function() {


        frms = this;

        this.ViewFrame = new sw.Promed.ViewFrame(
                {
                    id: 'amm_VaccineTypeGrid',
                    object: 'amm_VaccineTypeGrid',
                    dataUrl: '/?c=VaccineCtrl&m=GetVaccineTypeGrid',
                    //root: 'data',
                    region: 'center',
                    toolbar: true,
                    setReadOnly: false,
                    autoLoadData: false,

                    stringfields:
                            [
                                {name: 'VaccineType_id', type: 'int', header: 'ID', key: true},
                                {name: 'VaccineType_Name', type: 'string', header: 'Наименование прививки', width: 250, id: 'autoexpand'},
                                {name: 'VaccineType_SignNatCal', type: 'checkbox', header: 'Национальный календарь', width: 190},
                                {name: 'VaccineType_SignNatCalName', type: 'string', header: 'Национальный календарь', hidden: true},
                                {name: 'VaccineType_SignScheme', type: 'checkbox', header: 'Наличие схемы вакцинации', width: 190},
                                {name: 'VaccineType_SignSchemeName', type: 'string', header: 'Наличие схемы вакцинации',hidden: true},
                                {name: 'VaccineType_SignEmergency', type: 'checkbox', header: 'Экстренная вакцинация', width: 190},
                                {name: 'VaccineType_SignEmergencyName', type: 'string', header: 'Экстренная вакцинация', hidden: true},
                                 {name: 'VaccineType_SignEpidem', type: 'checkbox', header: 'Вакцинация по эпид. показаниям', width: 220},
                                {name: 'VaccineType_SignEpidemName', type: 'string', header: 'Вакцинация по эпид. показаниям', hidden: true}
                              

                            ],
                    actions:
                            [
                          
                                {name: 'action_view', hidden: true},
                                {name: 'action_delete', hidden: true},
                                {name: 'action_edit', hidden: true},
                                {name: 'action_save',hidden: true},
                                {name: 'action_add',hidden: true}
                            ]
                });
         
        this.ViewFrame.getGrid().view = new Ext.grid.GridView(
        {
            getRowClass: function(row, index)
            {
                var cls = '';
                if (row.get('VaccineType_SignNatCal') != 1) 
                    cls = 'x-grid-rowblue ';
                else
                    if (row.get('VaccineType_SignScheme') == 1) 
                      cls = 'x-grid-rowbold ';
                return cls;
            }
        });

        Ext.apply(this, {
            items: [
                this.ViewFrame
            ]});

        sw.Promed.amm_SprVaccineTypeForm.superclass.initComponent.apply(this, arguments);
    },
    show: function() {
        sw.Promed.amm_SprVaccineTypeForm.superclass.show.apply(this, arguments);
        Ext.getCmp('amm_VaccineTypeGrid').ViewGridPanel.getStore().load();;


    }

});

