/**
* swMzDrugRequestExtendedErrorViewWindow - окно для вывода ошибок в заявках
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Salakhov R.
* @version      08.2018
* @comment      
*/
sw.Promed.swMzDrugRequestExtendedErrorViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Ошибка',
	layout: 'border',
	id: 'MzDrugRequestExtendedErrorViewWindow',
	modal: true,
	shim: false,
	width: 550,
    height: 200,
	resizable: false,
	maximizable: false,
	maximized: false,
    setErrorType: function() {
	    var wnd = this;

        switch(this.Error_Type) {
            case 'drugrequest_forming_not_attach_persons': //в заявке участка есть пациенты не прикрипленные к участку
                this.ButtonText1 = 'Вывести список';
                this.doAction1 = function() {
                    var header_data = [
                        {   dataIndex: 'Person_Fio',
                            header: 'ФИО'
                        },
                        {   dataIndex: 'Person_BirthDay',
                            header: 'Дата рождения'
                        }
                    ];
                    var print_data = new Object({
                        title: 'Список пациентов',
                        addNumberColumn: true,
                        header_data: header_data,
                        row_data: this.Error_Data.Person_List
                    });
                    printDataOnNewTab(print_data);
                    wnd.hide();
                }
                break;
            case 'drugrequest_forming_missing_persons': //в заявке участка отсутствуют некоторые пациенты имеющие прикрипленние к участку
                this.ButtonText1 = 'Вывести список пациентов';
                this.ButtonText2 = 'Добавить пациентов заявку врача';
                this.doAction1 = function() {
                    var header_data = [
                        {   dataIndex: 'Person_Fio',
                            header: 'ФИО'
                        },
                        {   dataIndex: 'Person_BirthDay',
                            header: 'Дата рождения'
                        }
                    ];
                    var print_data = new Object({
                        title: 'Список пациентов',
                        addNumberColumn: true,
                        header_data: header_data,
                        row_data: wnd.Error_Data.Person_List
                    });
                    printDataOnNewTab(print_data);
                };
                this.doAction2 = function() {
                    if (wnd.Error_Data.DrugRequest_id) {
                        wnd.getLoadMask(langs('Выполняется добавление пациентов в разнарядку заявки участка...')).show();
                        Ext.Ajax.request({
                            params: {
                                DrugRequest_id: wnd.Error_Data.DrugRequest_id
                            },
                            callback: function (options, success, response) {
                                wnd.getLoadMask().hide();
                                var result = Ext.util.JSON.decode(response.responseText);
                                if (result && result.success) {
                                    getWnd('swMzDrugRequestEditWindow').reshow({
                                        callback: function() {
                                            sw.swMsg.alert(langs('Сообщение'), langs('Пациенты добавлены в заявку участкового врача'));
                                        }
                                    });
                                    wnd.hide();
                                }
                            },
                            url:'/?c=MzDrugRequest&m=addDrugRequestPersonOrderMissingPerson'
                        });
                    }
                };
                break;
            case 'drugrequest_mo_confirmation_missing_and_unattached': //в заявке участка отсутствуют некоторые пациенты имеющие прикрипленние к участку
                var header_data = [
                    {   dataIndex: 'DrugRequest_Name',
                        header: 'Заявка'
                    },
                    {   dataIndex: 'Person_Fio',
                        header: 'Пациент'
                    },
                    {   dataIndex: 'Person_BirthDay',
                        header: 'Дата рождения'
                    },
                    {   dataIndex: 'Person_deadDT',
                        header: 'Дата смерти'
                    },
                    {   dataIndex: 'LpuRegion_Name',
                        header: 'Прикрепление'
                    }
                ];

                if (!Ext.isEmpty(wnd.Error_Data.UnattachedPerson_List)) {
                    this.ButtonText1 = 'Неправильный участок пациента в заявке';
                    this.doAction1 = function() {
                        var print_data = new Object({
                            title: 'Список пациентов',
                            addNumberColumn: true,
                            header_data: header_data,
                            row_data: wnd.Error_Data.UnattachedPerson_List
                        });
                        printDataOnNewTab(print_data);
                    };
                }

                if (!Ext.isEmpty(wnd.Error_Data.MissingPerson_List)) {
                    this.ButtonText2 = 'Не включены в заявку по участку';
                    this.doAction2 = function() {
                        var print_data = new Object({
                            title: 'Список пациентов',
                            addNumberColumn: true,
                            header_data: header_data,
                            row_data: wnd.Error_Data.MissingPerson_List
                        });
                        printDataOnNewTab(print_data);
                    };
                }
                break;
        }
    },
    updateButtons: function() {
	    if (!Ext.isEmpty(this.ButtonText1)) {
            this.Button1.setText(this.ButtonText1);
            this.Button1.show();
        } else {
            this.Button1.hide();
        }
	    if (!Ext.isEmpty(this.ButtonText2)) {
            this.Button2.setText(this.ButtonText2);
            this.Button2.show();
        } else {
            this.Button2.hide();
        }
    },
	show: function() {
        var wnd = this;
		sw.Promed.swMzDrugRequestExtendedErrorViewWindow.superclass.show.apply(this, arguments);
		this.Error_Type = null;
		this.Error_Msg = null;
		this.Error_Data = null;
		this.ButtonText1 = null;
		this.ButtonText2 = null;
        this.doAction1 = Ext.emptyFn;
        this.doAction2 = Ext.emptyFn;

        if (arguments[0]) {
            if (arguments[0].Error_Type) {
                this.Error_Type = arguments[0].Error_Type;
            }
            if (arguments[0].Error_Msg) {
                this.Error_Msg = arguments[0].Error_Msg;
            }
            if (arguments[0].Error_Data) {
                this.Error_Data = arguments[0].Error_Data;
            }
        }

        this.TextPanel.body.update(this.Error_Msg);
        this.setErrorType();
        this.updateButtons();
	},
	initComponent: function() {
		var wnd = this;

		this.TextPanel =  new Ext.Panel({
			region: 'center',
            frame: true,
			html: 'test'
		});

		this.Button1 = new Ext.Button({
            text: langs('Действие 1'),
            id: 'mdreev_ActionButton1',
            iconCls: null,
            style: 'padding: 0px 5px 0px 0px',
            handler: function (){
                wnd.doAction1();
            }
        });

		this.Button2 = new Ext.Button({
            xtype: 'button',
            text: langs('Действие 2'),
            id: 'mdreev_ActionButton2',
            iconCls: null,
            style: 'padding: 0px 5px 0px 0px',
            handler: function (){
                wnd.doAction2();
            }
        });

        var form = new Ext.form.FormPanel({
            url: null,
            region: 'south',
            autoHeight: true,
            frame: true,
			layout: 'column',
            labelAlign: 'right',
            labelWidth: 80,
            bodyStyle: 'padding: 10px',
            defaults: {
                anchor: '100%'
            },
            items: [{
                layout: 'form',
                items: [wnd.Button1]
            }, {
                layout: 'form',
                items: [wnd.Button2]
            }, {
                layout: 'form',
                items: [{
                    xtype: 'button',
					text: langs('Закрыть'),
                    id: 'mdreev_CloseButton',
                    iconCls: null,
                    handler: function (){
                        wnd.hide();
                    }
                }]
            }]
        });

		Ext.apply(this, {
			layout: 'border',
			buttons: false,
			items:[
				this.TextPanel,
				form
			]
		});
		sw.Promed.swMzDrugRequestExtendedErrorViewWindow.superclass.initComponent.apply(this, arguments);
	}
});