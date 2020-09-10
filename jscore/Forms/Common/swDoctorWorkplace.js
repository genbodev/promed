/**
 * swDoctorWorkplaceWindow - окно рабочего места врача
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010, Swan.
 * @author       Alexander "Alf" Arefyev (avaref@gmail.com)
 * @version      26.02.2010
 */
sw.Promed.swDoctorWorkplaceWindow = Ext.extend(sw.Promed.BaseForm, {
    closable: true,
    closeAction: 'hide',
    maximized: true,
    title: lang['rabochee_mesto_vracha'],
    iconCls: 'workplace-mp16',
    
    loadSchedule: function(mode){
    //swalert(this.dateText);
    },
    
    initComponent: function(){
        this.scheduleWeekData = [['25/02/2010', '09:00', '', '', '', ''], ['25/02/2010', '09:30', 'Пупкин Василий Моисеевич', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['25/02/2010', '10:00', '', '', '', ''], ['25/02/2010', '10:30', 'Христорождественский Иммануил Иммануилович', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['25/02/2010', '11:00', 'Константинопольский Константин Константинович', '03/02/1970', '12/02/2010 12:23', 'Юзер Операторов'], ['25/02/2010', '11:30', 'Норрис Чак Брюслиевич', '09/05/1988', '11/02/2010 14:33', 'Юзер Операторов'], ['25/02/2010', '12:00', 'Иванов Иван Иванович', '02/05/1973', '13/02/2010 15:23', 'Юзер Операторов'], ['25/02/2010', '12:30', '', '', '', ''], ['25/02/2010', '13:00', '', '', '', ''], ['25/02/2010', '13:30', '', '', '', ''], ['26/02/2010', '09:00', '', '', '', ''], ['26/02/2010', '09:30', 'Пупкин Василий Моисеевич', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['26/02/2010', '10:00', '', '', '', ''], ['26/02/2010', '10:30', 'Христорождественский Иммануил Иммануилович', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['26/02/2010', '11:00', 'Константинопольский Константин Константинович', '03/02/1970', '12/02/2010 12:23', 'Юзер Операторов'], ['26/02/2010', '11:30', 'Норрис Чак Брюслиевич', '09/05/1988', '11/02/2010 14:33', 'Юзер Операторов'], ['26/02/2010', '12:00', 'Иванов Иван Иванович', '02/05/1973', '13/02/2010 15:23', 'Юзер Операторов'], ['26/02/2010', '12:30', '', '', '', ''], ['26/02/2010', '13:00', '', '', '', ''], ['26/02/2010', '13:30', '', '', '', ''], ['24/02/2010', '09:00', '', '', '', ''], ['24/02/2010', '09:30', 'Пупкин Василий Моисеевич', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['24/02/2010', '10:00', '', '', '', ''], ['24/02/2010', '10:30', 'Христорождественский Иммануил Иммануилович', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['24/02/2010', '11:00', 'Константинопольский Константин Константинович', '03/02/1970', '12/02/2010 12:23', 'Юзер Операторов'], ['24/02/2010', '11:30', 'Норрис Чак Брюслиевич', '09/05/1988', '11/02/2010 14:33', 'Юзер Операторов'], ['24/02/2010', '12:00', 'Иванов Иван Иванович', '02/05/1973', '13/02/2010 15:23', 'Юзер Операторов'], ['24/02/2010', '12:30', '', '', '', ''], ['24/02/2010', '13:00', '', '', '', ''], ['24/02/2010', '13:30', '', '', '', '']];
        this.scheduleMonthData = [['01/02/2010', '09:00', '', '', '', ''], ['01/02/2010', '09:30', 'Пупкин Василий Моисеевич', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['01/02/2010', '10:00', '', '', '', ''], ['01/02/2010', '10:30', 'Христорождественский Иммануил Иммануилович', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['01/02/2010', '11:00', 'Константинопольский Константин Константинович', '03/02/1970', '12/02/2010 12:23', 'Юзер Операторов'], ['01/02/2010', '11:30', 'Норрис Чак Брюслиевич', '09/05/1988', '11/02/2010 14:33', 'Юзер Операторов'], ['01/02/2010', '12:00', 'Иванов Иван Иванович', '02/05/1973', '13/02/2010 15:23', 'Юзер Операторов'], ['01/02/2010', '12:30', '', '', '', ''], ['01/02/2010', '13:00', '', '', '', ''], ['01/02/2010', '13:30', '', '', '', ''], ['25/02/2010', '09:00', '', '', '', ''], ['25/02/2010', '09:30', 'Пупкин Василий Моисеевич', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['25/02/2010', '10:00', '', '', '', ''], ['25/02/2010', '10:30', 'Христорождественский Иммануил Иммануилович', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['25/02/2010', '11:00', 'Константинопольский Константин Константинович', '03/02/1970', '12/02/2010 12:23', 'Юзер Операторов'], ['25/02/2010', '11:30', 'Норрис Чак Брюслиевич', '09/05/1988', '11/02/2010 14:33', 'Юзер Операторов'], ['25/02/2010', '12:00', 'Иванов Иван Иванович', '02/05/1973', '13/02/2010 15:23', 'Юзер Операторов'], ['25/02/2010', '12:30', '', '', '', ''], ['25/02/2010', '13:00', '', '', '', ''], ['25/02/2010', '13:30', '', '', '', ''], ['26/02/2010', '09:00', '', '', '', ''], ['26/02/2010', '09:30', 'Пупкин Василий Моисеевич', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['26/02/2010', '10:00', '', '', '', ''], ['26/02/2010', '10:30', 'Христорождественский Иммануил Иммануилович', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['26/02/2010', '11:00', 'Константинопольский Константин Константинович', '03/02/1970', '12/02/2010 12:23', 'Юзер Операторов'], ['26/02/2010', '11:30', 'Норрис Чак Брюслиевич', '09/05/1988', '11/02/2010 14:33', 'Юзер Операторов'], ['26/02/2010', '12:00', 'Иванов Иван Иванович', '02/05/1973', '13/02/2010 15:23', 'Юзер Операторов'], ['26/02/2010', '12:30', '', '', '', ''], ['26/02/2010', '13:00', '', '', '', ''], ['26/02/2010', '13:30', '', '', '', '']];
        this.scheduleDayData = [['25/02/2010', '09:00', '', '', '', ''], ['25/02/2010', '09:30', 'Пупкин Василий Моисеевич', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['25/02/2010', '10:00', '', '', '', ''], ['25/02/2010', '10:30', 'Христорождественский Иммануил Иммануилович', '01/01/1970', '13/02/2010 11:23', 'Юзер Операторов'], ['25/02/2010', '11:00', 'Константинопольский Константин Константинович', '03/02/1970', '12/02/2010 12:23', 'Юзер Операторов'], ['25/02/2010', '11:30', 'Норрис Чак Брюслиевич', '09/05/1988', '11/02/2010 14:33', 'Юзер Операторов'], ['25/02/2010', '12:00', 'Иванов Иван Иванович', '02/05/1973', '13/02/2010 15:23', 'Юзер Операторов'], ['25/02/2010', '12:30', '', '', '', ''], ['25/02/2010', '13:00', '', '', '', ''], ['25/02/2010', '13:30', '', '', '', '']];
        
        this.reader = new Ext.data.ArrayReader({}, [{
            name: 'date',
            type: 'date',
            dateFormat: 'd/m/Y'
        }, {
            name: 'time',
            type: 'date',
            dateFormat: 'H:i'
        }, {
            name: 'name'
        }, {
            name: 'birthdate',
            type: 'date',
            dateFormat: 'd/m/Y'
        }, {
            name: 'record',
            type: 'date',
            dateFormat: 'd/m/Y H:i'
        }, {
            name: 'operator'
        }]);
        
        this.gridStore = new Ext.data.GroupingStore({
            reader: this.reader,
            data: this.scheduleDayData,
            sortInfo: {
                field: 'time',
                direction: "ASC"
            },
            groupField: 'date'
        });
        
        this.dateMenu = new Ext.menu.DateRangeMenu({
            selectionMode: 'day'
        });
        
        this.dateText = new Ext.Toolbar.TextItem({
            text: '<span style="font-weight: bold; font-size: 16px">Четверг, 25 февраля 2010</span>'
        })
        
        this.DoctorToolbar = new Ext.Toolbar({
            items: [{
                text: lang['predyiduschiy'],
                xtype: 'button',
                iconCls: 'arrow-previous16'
            }, {
                iconCls: 'datepicker16',
                menu: this.dateMenu,
                tooltip: lang['vyiberite_den_ili_period']
            }, this.dateText, {
                text: lang['sleduyuschiy'],
                xtype: 'button',
                iconCls: 'arrow-next16'
            }, {
                xtype: 'tbfill'
            }, {
                text: lang['den'],
                xtype: 'button',
                toggleGroup: 'periodToggle',
                iconCls: 'datepicker-day16',
                pressed: true,
                handler: function(){
                    this.loadSchedule('day');
                }
                .createDelegate(this)
            }, {
                text: lang['nedelya'],
                xtype: 'button',
                toggleGroup: 'periodToggle',
                iconCls: 'datepicker-week16',
                handler: function(){
                    this.loadSchedule('week');
                }
                .createDelegate(this)
            }, {
                text: lang['mesyats'],
                xtype: 'button',
                toggleGroup: 'periodToggle',
                iconCls: 'datepicker-month16',
                handler: function(){
                    this.loadSchedule('month');
                }
                .createDelegate(this)
            }]
        
        })
        
        this.TopPanel = new Ext.Panel({
            region: 'north',
            frame: true,
            height: 105,
            tbar: this.DoctorToolbar,
            items: [{
                xtype: 'form',
                labelAlign: 'right',
                labelWidth: 50,
                items: [{
                    xtype: 'fieldset',
                    height: 60,
                    title: lang['poisk'],
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        items: [{
                            xtype: 'textfield',
                            width: 300,
                            fieldLabel: lang['fio']
                        
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            xtype: 'datefield',
                            fieldLabel: lang['dr']
                        }]
                    }, {
                        layout: 'form',
                        items: [{
                            style: "padding-left: 20px",
                            xtype: 'button',
                            text: lang['nayti'],
                            iconCls: 'search16'
                        }]
                    }]
                }]
            }]
        })
        
        this.gridToolBar = new Ext.Toolbar({
            autoHeight: true,
            buttons: [{
                iconCls: 'add16',
                text: lang['novyiy']
            }, {
                iconCls: 'edit16',
                text: lang['izmenit']
            }, {
                iconCls: 'copy16',
                text: lang['skopirovat']
            }, {
                iconCls: 'view16',
                text: lang['prosmotr']
            }, {
                iconCls: 'delete16',
                text: lang['udalit']
            }, '-', {
                iconCls: 'actions16',
                text: lang['deystviya']
            }, '-', {
                iconCls: 'refresh16',
                text: lang['obnovit']
            }, '-', {
                iconCls: 'print16',
                text: lang['pechat']
            }]
        });
        
        this.ScheduleGrid = new Ext.grid.GridPanel({
            region: 'center',
            frame: true,
            tbar: this.gridToolBar,
            store: this.gridStore,
            
            columns: [{
                header: lang['data'],
                width: 10,
                sortable: true,
                dataIndex: 'date',
                renderer: Ext.util.Format.dateRenderer('d/m/Y')
            }, {
                header: lang['vremya'],
                width: 10,
                sortable: true,
                renderer: Ext.util.Format.dateRenderer('H:i'),
                dataIndex: 'time'
            }, {
                header: "Фамилия Имя Отчество",
                width: 60,
                sortable: true,
                dataIndex: 'name'
            }, {
                header: "Дата рождения",
                width: 10,
                sortable: true,
                dataIndex: 'birthdate',
                renderer: Ext.util.Format.dateRenderer('d/m/Y')
            }, {
                header: lang['zapisan'],
                width: 20,
                sortable: true,
                dataIndex: 'record',
                renderer: Ext.util.Format.dateRenderer('d/m/Y H:i')
            
            }, {
                header: lang['operator'],
                width: 30,
                sortable: true,
                dataIndex: 'operator'
            }],
            
            view: new Ext.grid.GroupingView({
                forceFit: true,
                enableGrouping:false,
                enableGroupingMenu:false,
                groupTextTpl: lang['{text}_5_chelovek']
            })
        
        });
        
        Ext.apply(this, {
            layout: 'border',
            items: [this.TopPanel, this.ScheduleGrid],
            buttons: [{
                text: '-'
            }, HelpButton(this, TABINDEX_MPSCHED + 98), {
                iconCls: 'cancel16',
                text: BTN_FRMCLOSE
            }]
        });
        sw.Promed.swDoctorWorkplaceWindow.superclass.initComponent.apply(this, arguments);
    },
    
    show: function(){
        sw.Promed.swDoctorWorkplaceWindow.superclass.show.apply(this, arguments);
    }
});
