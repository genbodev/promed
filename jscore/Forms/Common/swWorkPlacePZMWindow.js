/**
 * АРМ пункта забора биоматериала
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @autor
 * @copyright    Copyright (c) 2012 Swan Ltd.
 * @version      апрель.2012
 */
sw.Promed.swWorkPlacePZMWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	useUecReader: true,
    //объект с параметрами АРМа, с которыми была открыта форма
    userMedStaffFact: null,
	id:'swWorkPlacePZMWindow',
	buttons:[
		'-',
		{
			text:BTN_FRMHELP,
			iconCls:'help16',
			handler:function (button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text:'Закрыть',
			tabIndex:-1,
			tooltip:'Закрыть',
			iconCls:'cancel16',
			handler:function () {
				this.ownerCt.hide();
			}
		}
	],
	convertDates:function (obj) {
		for (var field_name in obj) {
			if (obj.hasOwnProperty(field_name)) {
				if (typeof(obj[field_name]) == 'object') {
					if (obj[field_name] instanceof Date) {
						obj[field_name] = obj[field_name].format('d.m.Y H:i');
					}
				}
			}
		}

		return obj;
	},
	getLoadMask:function () {
		if (!this.loadMask) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg:'Подождите... ' });
		}

		return this.loadMask;
	},
	getDataFromBarcode: function(barcodeData, person_data) {
		var f = this.FilterPanel.getForm();
		f.findField('Person_SurName').setValue(barcodeData.Person_Surname);
		f.findField('Person_FirName').setValue(barcodeData.Person_Firname);
		f.findField('Person_SecName').setValue(barcodeData.Person_Secname);
		f.findField('Person_BirthDay').setValue(barcodeData.Person_Birthday);
		this.doSearch();
	},
	openLabRequestEditWindow:function (action, EvnLabRequest_BarCode) {
		if (!action || !action.toString().inlist([ 'add', 'edit', 'view' ])) {
			return false;
		}
        var swPersonSearchWindow = getWnd('swPersonSearchWindow');
		if (action == 'add' && swPersonSearchWindow.isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно поиска человека уже открыто. Для продолжения необходимо закрыть окно поиска человека.');
			return false;
		}

		if (getWnd('swEvnLabRequestEditWindow').isVisible()) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования заявки уже открыто. Для продолжения необходимо закрыть окно редактирования заявки.');
			return false;
		}

		var curWnd = this;
		var grid = this.GridPanel.getGrid();
		var params = new Object();

		params.action = action;
		params.ARMType = 'pzm';
        params.callback = function(data) {
            // здесь функция должна проверять ид который приходит назад, находить его в списке и устанавливать на него фокус
            var gridpanel = this.GridPanel;
            gridpanel.loadData({valueOnFocus: {EvnLabRequest_id: data.EvnLabRequest_id}});
        }.createDelegate(this);
		params.MedService_id = this.MedService_id;

		if (action == 'add') {
            swPersonSearchWindow.show({
				onClose:function () {
					if (grid.getSelectionModel().getSelected()) {
						grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
					}
					else {
						//grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onSelect:function (person_data) {
					swPersonSearchWindow.hide();
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
					if (undefined != EvnLabRequest_BarCode) {
						params.EvnLabRequest_BarCode = EvnLabRequest_BarCode;
					} else {
						params.EvnLabRequest_BarCode = '';
					}
					getWnd('swEvnLabRequestEditWindow').show(params);
				},
				searchMode: 'all'
			});
		}
		else {
			if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDirection_id')) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.EvnDirection_id = record.get('EvnDirection_id');
			params.onHide = function () {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
			};
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');

			getWnd('swEvnLabRequestEditWindow').show(params);
		}
	},
	onBeforeHide: function()
	{
		sw.Applets.BarcodeScaner.stopBarcodeScaner();
	},
	show:function () {
		var that = this;
		sw.Promed.swWorkPlacePZMWindow.superclass.show.apply(this, arguments);
		this.MedService_id = null;
		if (!arguments[0] || !arguments[0].MedService_id) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function () {
				this.hide();
			}.createDelegate(this));
			return false;
		}
        this.userMedStaffFact = arguments[0];
		this.MedService_id = arguments[0].MedService_id;
		this.FilterPanel.getForm().findField('MedService_id').setValue(this.MedService_id);

		this.GridPanel.setParam('limit', 50);
		this.GridPanel.setParam('start', 0);
		var today = (new Date()).format('d.m.Y');
		this.GridPanel.setParam('begDate', today);
		this.GridPanel.setParam('endDate', today);

		this.FilterPanel.getForm().findField('UslugaComplex_id').getStore().load({
			params:{
				filter_by_exists:1,
				level:0,
				MedService_id:this.MedService_id
			}
		});

		this.GridPanel.loadData({
			globalFilters:{
				MedService_id:this.MedService_id
			}
		});

		sw.Applets.BarcodeScaner.startBarcodeScaner({ callback: this.getDataFromBarcode.createDelegate(this) });
	},
	doSearch: function () {
		this.searchParams = {
			MedService_id: this.MedService_id
		};
		sw.Promed.swWorkPlacePZMWindow.superclass.doSearch.apply(this, arguments);
	},
	initComponent:function () {
		var curWnd = this;
		var cur_win = this;

        this.buttonPanelActions = {
            action_Timetable: {
                nn: 'action_Timetable',
                tooltip: 'Работа с расписанием',
                text: 'Расписание',
                iconCls : 'mp-timetable32',
                disabled: false,
                handler: function()
                {
                    getWnd('swTTMSScheduleEditWindow').show({
                        MedService_id: curWnd.userMedStaffFact.MedService_id,
                        MedService_Name: curWnd.userMedStaffFact.MedService_Name,
                        userClearTimeMS: null
                    });
                }
            },
            action_MSLManage: {
                nn: 'action_MSLManage',
                tooltip: 'Лаборатории',
                text: 'Лаборатории',
                iconCls : 'testtubes32',
                disabled: false,
                handler: function()
                {
                    getWnd('swMedServiceLinkManageWindow').show({
                        MedService_id: curWnd.userMedStaffFact.MedService_id
                    });
                }
            },
            action_Defect: {
				nn: 'action_Defect',
				tooltip: 'Журнал отбраковки',
				text: 'Журнал отбраковки',
				iconCls : 'lab32',
				disabled: false, 
				handler: function() 
				{
					getWnd('swEvnLabSampleDefectViewWindow').show({
						MedService_id: curWnd.MedService_id
					});
				}
            }
        };

		cur_win.gridKeyboardInput = '';
		cur_win.gridKeyboardInputSequence = 1;
		cur_win.resetGridKeyboardInput = function (sequence) {
			var result = false;
			if (sequence == cur_win.gridKeyboardInputSequence) {
				if (cur_win.gridKeyboardInput.length >= 4) {
					cur_win.GridPanel.onKeyboardInputFinished(cur_win.gridKeyboardInput);
					result = true;
				}
				cur_win.gridKeyboardInput = '';
			}
			return result;
		};

		this.gridPanelAutoLoad = false;

		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				curWnd.doSearch();
				curWnd.GridPanel.setParam('start', 0);
			}
		};

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner:curWnd,
			labelWidth:120,
			filter:{
				title:'Фильтры',
				layout:'form',
				items:[
					{
						name:'MedService_id',
						value:0,
						xtype:'hidden'
					},
					{
						layout:'column',
						items:[
							{
								layout:'form',
								labelWidth:55,
								items:[
									{
										xtype:'textfieldpmw',
										width:120,
										name:'Person_SurName',
										fieldLabel:'Фамилия',
										listeners:{
											'keydown':function (inp, e) {
												var form = Ext.getCmp('swWorkPlacePZMWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.doSearch();
												}
											}
										}
									}
								]
							},
							{
								layout:'form',
								labelWidth:35,
								items:[
									{
										xtype:'textfieldpmw',
										width:120,
										name:'Person_FirName',
										fieldLabel:'Имя',
										listeners:{
											'keydown':function (inp, e) {
												var form = Ext.getCmp('swWorkPlacePZMWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.doSearch();
												}
											}
										}
									}
								]
							},
							{
								layout:'form',
								labelWidth:60,
								items:[
									{
										xtype:'textfieldpmw',
										width:120,
										name:'Person_SecName',
										fieldLabel:'Отчество',
										listeners:{
											'keydown':function (inp, e) {
												var form = Ext.getCmp('swWorkPlacePZMWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.doSearch();
												}
											}
										}
									}
								]
							},
							{
								layout:'form',
								labelWidth:25,
								items:[
									{
										xtype:'swdatefield',
										format:'d.m.Y',
										plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name:'Person_BirthDay',
										fieldLabel:'ДР',
										listeners:{
											'keydown':function (inp, e) {
												var form = Ext.getCmp('swWorkPlacePZMWindow');
												if (e.getKey() == Ext.EventObject.ENTER) {
													e.stopEvent();
													form.doSearch();
												}
											}
										}
									}
								]
							},
							{
								layout:'form',
								border:false,
								labelWidth:35,
								items:[
									{
										comboSubject:'YesNo',
										fieldLabel:'Cito',
										hiddenName:'EvnDirection_IsCito',
										listeners:{
											'keydown':curWnd.onKeyDown
										},
										width:100,
										xtype:'swcommonsprcombo'
									}
								]
							},
                            {
                                layout:'form',
                                labelWidth:130,
                                items:[
                                    {
                                        xtype:'textfield',
                                        width:120,
                                        name:'EvnDirection_Num',
                                        fieldLabel:'Номер направления',
                                        listeners:{
                                            'keydown':function (inp, e) {
                                                var form = Ext.getCmp('swWorkPlacePZMWindow');
                                                if (e.getKey() == Ext.EventObject.ENTER) {
                                                    e.stopEvent();
                                                    form.doSearch();
                                                }
                                            }
                                        }
                                    }
                                ]
                            },
							{
								layout:'form',
								labelWidth:55,
								border:false,
								items:[
									{
										fieldLabel:'Услуга',
										hiddenName:'UslugaComplex_id',
										listeners:{
											'keydown':curWnd.onKeyDown
										},
										width:500,
										xtype:'swuslugacomplexpidcombo'
									}
								]
							},
							{
								layout:'form',
								items:[
									{
										style:"padding-left: 20px",
										xtype:'button',
										id:curWnd.id + 'BtnSearch',
										text:'Найти',
										iconCls:'search16',
										handler:function () {
											curWnd.doSearch();
											curWnd.GridPanel.setParam('start', 0);
										}
									}
								]
							},
							{
								layout:'form',
								items:[
									{
										style:"padding-left: 10px",
										xtype:'button',
										id:curWnd.id + 'BtnClear',
										text:'Сброс',
										iconCls:'reset16',
										handler:function () {
											curWnd.doReset();
                                            curWnd.doSearch();
										}
									}
								]
							}
						]
					}
				]
			}
		});

		this.GridPanel = new sw.Promed.ViewFrame({
			groupTextTpl: '{gvalue} ({[values.rs.length]} {[((values.rs.length>=10)&&(values.rs.length<=20))?"заявок":parseInt(values.rs.length.toString().charAt(values.rs.length.toString().length-1)).inlist([1]) ?"заявка" :(parseInt(values.rs.length.toString().charAt(values.rs.length.toString().length-1)).inlist([2,3,4]) ? "заявки" : "заявок")]})',
			useEmptyRecord: false,
			actions:[
				{ name:'action_add', text:'Добавить без записи', handler:function () {
					curWnd.openLabRequestEditWindow('add');
				} },
				{ name:'action_edit', handler:function () {
					curWnd.openLabRequestEditWindow('edit');
				} },
				{ name:'action_view', hidden:true },
				{
					name:'action_delete',
					text: 'Отменить',
					disabled: true,
					handler: function (){
						var selected = curWnd.GridPanel.getGrid().getSelectionModel().getSelected();
						if (!Ext.isEmpty(selected.data.EvnDirection_id) && selected.data.EvnDirection_id > 0) {
							// отмена направления
							getWnd('swSelectDirFailTypeWindow').show({formType: 'labdiag', LpuUnitType_SysNick: 'parka', onSelectValue: function(responseData) {
								if (!Ext.isEmpty(responseData.DirFailType_id)) {
									var loadMask = new Ext.LoadMask(curWnd.GridPanel.getEl(), {msg: "Отмена направления на лабораторное обследование..."});
									loadMask.show();
									Ext.Ajax.request({
										params: {
											EvnDirection_id: selected.data.EvnDirection_id,
											DirFailType_id: responseData.DirFailType_id,
											EvnComment_Comment: responseData.EvnComment_Comment
										},
										url: '/?c=EvnLabRequest&m=cancelDirection',
										callback: function(options, success, response) {
											loadMask.hide();
											if(success) {
												curWnd.GridPanel.loadData();
											}
										}
									});
								}
							}});
						} else if (!Ext.isEmpty(selected.data.EvnLabRequest_id) && selected.data.EvnLabRequest_id > 0) {
							Ext.Msg.show({
								title: 'Удаление заявки',
								msg: 'Вы действительно хотите удалить заявку?',
								buttons: Ext.Msg.YESNO,
								fn: function(btn) {
									if (btn === 'yes') {
										var loadMask = new Ext.LoadMask(curWnd.GridPanel.getEl(), {msg: "Удаление заявки на лабораторное обследование..."});
										loadMask.show();
										Ext.Ajax.request({
											params: {
												EvnLabRequest_id: selected.data.EvnLabRequest_id
											},
											url: '/?c=EvnLabRequest&m=delete',
											callback: function(options, success, response) {
												loadMask.hide();
												if(success) {
													curWnd.GridPanel.loadData();
												}
											}
										});
									}
								},
								icon: Ext.MessageBox.QUESTION
							});
						}
					}
				},
				{ name:'action_refresh' },
				{ name:'action_print', text:'Печать списка' },
				{ name:'action_print_record', tooltip: 'Печать записи', icon: 'img/icons/print16.png', text:'Печать записи', handler: function() {
                    var selected_record = this.GridPanel.getGrid().getSelectionModel().getSelected();
                    if (!selected_record) {
                        return false;
                    }
                    var EvnLabRequest_id = selected_record.data.EvnLabRequest_id;
                    var EvnLabRequest_BarCode = selected_record.data.EvnLabRequest_BarCode;
                    if (EvnLabRequest_id) {
                        var s = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'/run?__report=report/f209u.rptdesign'
                            + '&paramEvnLabRequest=' + EvnLabRequest_id
                            + '&s=' + ((EvnLabRequest_BarCode)?(EvnLabRequest_BarCode):('0000000000000'))
                            + '&__format=pdf';
                        log(EvnLabRequest_BarCode);
                        window.open(s, '_blank');
                        return false;
                    }
                    return true;
                }.createDelegate(this)}
			],
			autoExpandColumn:'autoexpand',
			autoLoadData:false,
			dataUrl:'/?c=EvnLabRequest&m=loadPZMWorkPlace',
            object: 'EvnLabRequest',
			id:curWnd.id + 'PZMWorkPlacePanel',
			onDblClick:function () {
				this.onEnter();
			},
			onEnter:function () {
				if (!cur_win.resetGridKeyboardInput(cur_win.gridKeyboardInputSequence)) {
					if ( !this.ViewActions.action_edit.isDisabled() ) {
						this.ViewActions.action_edit.execute();
					}
					else {
						this.ViewActions.action_view.execute();
					}
				}
			},
			onRowSelect:function (sm, index, record) {
				var disabled = (!(record.get('canEdit') == 1) || Ext.isEmpty(record.get('LabRequestState')) || !record.get('LabRequestState').substr(0,1).inlist(['1','2']));
				this.getAction('action_delete').setDisabled(disabled);
				
				// печать только выполненных заявок
				disabled = (Ext.isEmpty(record.get('LabRequestState')) || record.get('LabRequestState').substr(0,1) != '5');
				this.getAction('action_print_record').setDisabled(disabled);
			},
			onKeyDown1:function () {
				var e = arguments[0][0];
				if ((e.getCharCode() == 9 ) || e.getCharCode() == 13) {
					return;
				}
				cur_win.gridKeyboardInputSequence++;
				var s = cur_win.gridKeyboardInputSequence;
				var pressed = String.fromCharCode(e.getCharCode());
				var alowed_chars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

				if ((pressed != '') && (alowed_chars.indexOf(pressed) >= 0)) {
					cur_win.gridKeyboardInput = cur_win.gridKeyboardInput + String.fromCharCode(e.getCharCode());
					setTimeout(function () {
						cur_win.resetGridKeyboardInput(s);
					}, 500);
				}
			},
            onKeyboardInputFinished: function (input){
				if (input.length>0) {

                    var found = cur_win.GridPanel.getGrid().getStore().findBy(function (el) {
                        return (el.get('EvnLabRequest_BarCode').indexOf(input) != -1);
                    });
                    if (found >= 0) {
                        cur_win.GridPanel.getGrid().getSelectionModel().selectRow(found);
                        cur_win.openLabRequestEditWindow('edit');
                    }
				}
			},
			pageSize:50,
			paging:false,
			region:'center',
			root:'data',
			stringfields:[
				// Поля для отображение в гриде
				{ name:'EvnDirection_id', type:'int', header:'ID', key:true },
				{ name:'EvnLabRequest_id', type:'int', hidden:true },
				{ name:'LabRequestState', hidden:true, group:true, sort:true, direction:'ASC', header:'' },
				{ name:'TimetableMedService_begTime', type: 'datetime', dateFormat: 'd.m.Y H:i', header: 'Запись', sort: true, direction: 'ASC', width: 120 },
				{ name:'EvnDirection_IsCito', header:'Cito', sort:true, direction:'DESC', type:'string', width:40 },
				{ name:'UslugaComplex_Name', header:'Услуга', width:280, id:'autoexpand' },
				{ name:'EvnLabRequest_BarCode', type:'string', header:'Штрих-код'},
				{ name:'EvnDirection_Num', header:'Номер направления', width:120 },
				{ name:'EvnDirection_setDate', dateFormat:'d.m.Y', type:'date', header:'Дата направления', width:120 },
				{ name:'PrehospDirect_Name', header:'Кем направлен', width:320 },
				{ name:'Person_FIO', header:'ФИО пациента', width:240 },
                { name:'Person_Phone', type: 'string', header: 'Телефон', width: 100},
				{ name:'canEdit', type:'int', hidden:true }
			],
			title:'Журнал рабочего места',
			totalProperty:'totalCount'
		});

		sw.Promed.swWorkPlacePZMWindow.superclass.initComponent.apply(this, arguments);
	}
});