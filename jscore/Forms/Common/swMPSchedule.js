/**
* swMPSchedule - окно просмотра расписания врача
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      30.11.2009
*/

sw.Promed.swMPScheduleWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	height: 400,
	id: 'MPSchedule',
	title: lang['raspisanie_rabotyi_vracha'],
	
	
	/**
	 * Запись человека на время
	 * 
	 * @param {Object} data Объект с данными по выбранному человеку
	 */
	applyRecord: function ( data ) {
		if (this.getSchedule().getSelectionModel().getSelected() == null)
			return;
		if (this.getSchedule().getSelectionModel().getSelected().get('TimetableGraf_id') == null)
			return;
		if (this.getSchedule().getSelectionModel().getSelected().get('Person_id') != null)
			return;
		data.TimetableGraf_id = this.getSchedule().getSelectionModel().getSelected().get('TimetableGraf_id');
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		Ext.Ajax.request({
			url: C_TTG_APPLY,
			params: data,
			callback: function(options, success, response) {
				loadMask.hide();
				this.getSchedule().getStore().reload();
			}.createDelegate(this),
			failure: function() {
				loadMask.hide();
			}
		});
	},
	
	
	/**
	 * Освобождение бирки
	 */
	clearRecord: function ( ) {
		if (this.getSchedule().getSelectionModel() == null)
			return;
		if (this.getSchedule().getSelectionModel().getSelected().get('TimetableGraf_id') == null)
			return;
		if (this.getSchedule().getSelectionModel().getSelected().get('Person_id') == null)
			return;
		sw.swMsg.show({
			title: lang['podtverjdenie'],
			msg: lang['vyi_deystvitelno_jelaete_osvobodit_vremya_priema'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
					loadMask.show();
					Ext.Ajax.request({
						url: C_TTG_CLEAR,
						params: {
							TimetableGraf_id: this.getSchedule().getSelectionModel().getSelected().get('TimetableGraf_id')
						},
						callback: function(options, success, response) {
							loadMask.hide();
							this.getSchedule().getStore().reload();
						}.createDelegate(this),
						failure: function() {
							loadMask.hide();
						}
					});
				}
			}.createDelegate(this)
		});
	},
	getCalendar : function () {
		return this.calendar;
	},
	getSchedule : function () {
		return this.schedule;
	},
	getTopDataView : function () {
		return Ext.getCmp('MPS_TopDataView');
	},
	/**
	 * Загрузка расписания с севрера
	 */
	loadSchedule: function () {
		var dt = this.getCalendar().getValue();
		this.getTopDataView().getStore().removeAll();
		this.getTopDataView().getStore().loadData([{
			SelDay: Date.dayNames[dt.getDay()] + ', ' + dt.getDate() + ' ' + Date.monthNamesRod[dt.getMonth()] + ' ' + dt.getFullYear()
		}]);
		Ext.getCmp('MPS_ApplyBtn').disable();
		Ext.getCmp('MPS_ViewBtn').disable();
		Ext.getCmp('MPS_ClearBtn').disable();
		
		this.getSchedule().getStore().removeAll();
		var params = new Object();
		params.Date = Ext.util.Format.date(this.getCalendar().getValue(), 'd.m.Y');
		params.MedStaffFact_id = getGlobalOptions().msf_id;
		this.getSchedule().getStore().load({params: params});
	},
	/**
	 * Печать грида с расписанием
	 */
	printSchedule: function() {
		var grid = this.getSchedule();
		Ext.ux.GridPrinter.print(grid);
	},
    initComponent: function() {
    	this.calendar = new Ext.ux.DatePickerRange({
	        xtype: 'datepickerrange',
	        fieldLabel: 'Range Date',
	        selectionMode: 'day',
	        id: 'date-range',
	        startDay: 1,
	        listeners: {
	        	'select': function () {
	        		this.loadSchedule();
	        	}.createDelegate(this)
	        }
	    });
    	
	    this.schedule = new Ext.grid.GridPanel({
	    	region: 'center',
	        id: 'schedule',
	        frame: true,
	        loadMask : true,
	        /**
	         * Открывает паспорт здоровья человека
	         */
	        openPersonPassport: function(PersonInfo) {
	        	getWnd('swPersonEmkWindow').show( PersonInfo );
	        },
	        /**
	         * Открывает окно выбора человека для записи на выбранное время
	         */
	        openPersonSelect: function() {
				if (getWnd('swPersonSearchWindow').isVisible())
				{
					sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
					return false;
				}
		
				getWnd('swPersonSearchWindow').show({
		            onClose: function() {
		        		// do nothing
		            },
		    		onSelect: function(person_data) {
		    			getWnd('swPersonSearchWindow').hide();
						Ext.getCmp('MPSchedule').applyRecord(person_data);
		            },
		            searchMode: 'all'
		        });
	        },
	        /**
	         * Открывает окно записи человека или электронный паспорт человека в зависимости от того занята бирка или нет
	         */
	        openRecord: function () {
	        	var record = this.getSelectionModel().getSelected();
				if (record.get('Person_id') != null) {
					this.openPersonPassport({
						Person_id: record.get('Person_id'),
						Server_id: record.get('Server_id'),
						PersonEvn_id: record.get('PersonEvn_id')
					});
				} else {
					this.openPersonSelect();
				}
	        },
			keys: [
				{
				key: [
					Ext.EventObject.DELETE,
					Ext.EventObject.ENTER,
					Ext.EventObject.F3,
					Ext.EventObject.F4,
					Ext.EventObject.F5,
					Ext.EventObject.F6,
					Ext.EventObject.F9,
					Ext.EventObject.F10,
					Ext.EventObject.F11,
					Ext.EventObject.F12,
					Ext.EventObject.INSERT,
					Ext.EventObject.TAB,
					Ext.EventObject.PAGE_UP,
					Ext.EventObject.PAGE_DOWN,
					Ext.EventObject.HOME,
					Ext.EventObject.END
				],
				fn: function(inp, e) {
					e.stopEvent();
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

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					var grd = this.getSchedule();

					var selected_record = grd.getSelectionModel().getSelected();
					var params = new Object();
					params.Person_id = selected_record.get('Person_id');
					params.Server_id = selected_record.get('Server_id');
					params.Person_Birthday = selected_record.get('Person_Birthday');
					params.Person_Firname = selected_record.get('Person_Firname');
					params.Person_Secname = selected_record.get('Person_Secname');
					params.Person_Surname = selected_record.get('Person_Surname');

					switch (e.getKey())
					{
						case Ext.EventObject.ENTER:
							grd.openRecord();
						break;
						
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
							grd.openRecord();
						break;

						case Ext.EventObject.F5:
							this.loadSchedule();
						break;

						case Ext.EventObject.F9:
							this.printSchedule();
						break;
				
						case Ext.EventObject.F6:
							ShowWindow('swPersonCardHistoryWindow', params);
							return false;
						break;

						case Ext.EventObject.F10:
							ShowWindow('swPersonEditWindow', params);
							return false;
						break;

						case Ext.EventObject.F11:
							ShowWindow('swPersonCureHistoryWindow', params);
							return false;
						break;

						case Ext.EventObject.F12:
							if (e.ctrlKey)
							{
								ShowWindow('swPersonDispHistoryWindow', params);
							}
							else
							{
								ShowWindow('swPersonPrivilegeViewWindow', params);
							}
							return false;
						break;
							
						case Ext.EventObject.INSERT:
							grd.openPersonSelect();
						break;

						case Ext.EventObject.DELETE:
							this.clearRecord();
						break;

						case Ext.EventObject.TAB:
							if (e.shiftKey == false) {
								this.buttons[0].focus(false, 100);
							}
						break;
						
						case Ext.EventObject.END:
							GridEnd(grd);
						break;
						
						case Ext.EventObject.HOME:
							GridHome(grd);
						break;
						
						case Ext.EventObject.PAGE_DOWN:
							GridPageDown(grd);
						break;
						
						case Ext.EventObject.PAGE_UP:
							GridPageUp(grd);
						break;
					}
				}.createDelegate(this),
				stopEvent: true
			}],
			listeners : {
				'rowdblclick' : function (grd, rowIndex, e) {
					grd.openRecord();
				}
			},
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true,
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						if (record.get('Person_id') == null) {
							Ext.getCmp('MPS_ApplyBtn').enable();
							Ext.getCmp('MPS_ViewBtn').disable();
							Ext.getCmp('MPS_ClearBtn').disable();
						}
						if (record.get('Person_id') != null) {
							Ext.getCmp('MPS_ApplyBtn').disable();
							Ext.getCmp('MPS_ViewBtn').enable();
							Ext.getCmp('MPS_ClearBtn').enable();
						}
						record.set('set', 1);
						record.commit();
					},
					'rowdeselect': function(sm, rowIndex, record) {
						record.set('set', 0);
						record.commit();
					}
				}
			}),
	        tbar: new Ext.Toolbar({
		        autoHeight: true,
		        buttons: [{
		            text: '< Предыдущий день',
		            tooltip : "Перейти к предыдущему дню",
		            handler: function() {
		            	Ext.getCmp('MPSchedule').getCalendar().selectPrevDay();
		            }.createDelegate(this)
		        }, '-', {
		            iconCls: 'add16',
		            id: 'MPS_ApplyBtn',
		            text: lang['zapisat'],
		            tooltip : "Записать на выбранную бирку <b>(Enter)</b>",
		            handler: function () {
		            	this.getSchedule().openPersonSelect();
		            }.createDelegate(this)
		        }, {
		            iconCls: 'view16',
		            id: 'MPS_ViewBtn',
		            text: lang['prosmotr'],
		            tooltip : "Открыть паспорт здоровья <b>(Enter)</b>",
		            handler: function () {
		            	var record = this.getSchedule().getSelectionModel().getSelected();
		            	this.getSchedule().openPersonPassport({
							Person_id: record.get('Person_id'),
							Server_id: record.get('Server_id'),
							PersonEvn_id: record.get('PersonEvn_id')
						});
		            }.createDelegate(this)
		        }, {
		            iconCls: 'delete16',
		            id: 'MPS_ClearBtn',
		            text: lang['ochistit'],
		            tooltip : "Очистить запись <b>(Del)</b>",
		            handler: function () {
		            	this.clearRecord();
		            }.createDelegate(this)
		        }, '-', {
		            iconCls: 'actions16',
		            text: lang['deystviya'],
		            menu: [{
						text: lang['dobavit_birki'],
						xtype: 'tbbutton'
					}, {
						text: lang['udalit_birku'],
						xtype: 'tbbutton'
					}, {
						text: lang['ochistit_raspisanie'],
						xtype: 'tbbutton'
					}, {
						text: lang['tip_birki'],
						menu: { 
							items: [
		                        {
		                            text: lang['obyichnaya'],
		                            checked: true,
		                            group: 'rec',
		                            checkHandler: function() {}
		                        }, {
		                            text: lang['rezervnaya'],
		                            checked: false,
		                            group: 'rec',
		                            checkHandler: function() {}
		                        }, {
		                            text: lang['platnaya'],
		                            checked: false,
		                            group: 'rec',
		                            checkHandler: function() {}
		                        }, {
		                            text: lang['veteranskaya'],
		                            checked: false,
		                            group: 'rec',
		                            checkHandler: function() {}
		                        }, {
		                            text: lang['po_napravleniyu'],
		                            checked: false,
		                            group: 'rec',
		                            checkHandler: function() {}
		                        }
		                    ]
						}
					}]
		        }, '-', {
		            iconCls: 'refresh16',
		            text: lang['obnovit'],
		            tooltip : "Обновить расписание <b>(F5)</b>",
		            handler: function () {
		            	this.loadSchedule();
		            }.createDelegate(this)
		        }, '-', {
		            iconCls: 'print16',
		            text: lang['pechat'],
		            tooltip : "Печать расписания <b>(F9)</b>",
		            handler: function () {
		            	this.printSchedule();
		            }.createDelegate(this)
		        }, {
		        	xtype: 'tbfill'
		        }, '-', {
		        	text: 'Следующий день >',
		        	tooltip : "Перейти к следующему дню",
		        	handler: function() {
		            	Ext.getCmp('MPSchedule').getCalendar().selectNextDay();
		            }.createDelegate(this)
		        }
		        ]
		    }),
	        store: new Ext.data.JsonStore({
				autoLoad: false,
				url: C_TTG_LISTDAY,
				fields: [
					'TimetableGraf_id',
					'Person_id',
					'PersonEvn_id',
					'Server_id',
					'TimetableGraf_begTime',
					'Person_FIO',
					'Person_Surname',
					'Person_Firname',
					'Person_Secname',
					'Person_Birthday',
					'TimetableGraf_updDT',
					'pmUser_Name'
				]
			}),
	        columns: [{
	            header: lang['vremya'],
	            width: 80,
	            dataIndex: 'TimetableGraf_begTime',
	            xtype: 'datecolumn',
	            format: 'H:i',
	            sortable: false
	        }, {
	            header: lang['f_i_o'],
	            width: 300,
	            dataIndex: 'Person_FIO',
	            sortable: false
	        }, {
	            header: lang['dr'],
	            width: 80,
	            dataIndex: 'Person_Birthday',
	            format: 'd.m.Y',
	            sortable: false
	        }, {
	            header: lang['zapisan'],
	            width: 120,
	            dataIndex: 'TimetableGraf_updDT',
	            format: 'd.m.Y H:i',
	            sortable: false
	        }, {
	            header: lang['operator'],
	            width: 300,
	            dataIndex: 'pmUser_Name',
	            sortable: false
	        }]
	    });
		/**
		 * 
		 */
		this.schedule.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index, rowParams)
			{
				var cls = '';
				if (row.get('set') == 0 || row.get('set') == undefined) {
					if ( row.get('Person_FIO') == null )
						cls = cls+'x-grid-rowbackgreen ';
					if ( row.get('Person_FIO') != null )
						cls = cls+'x-grid-rowbackred ';
					if (cls.length == 0)
						cls = 'x-grid-panel';
				}
				return cls;
			}
		});
	    
	    Ext.apply(this, {
	    	//autoHeight: true,
	    	layout: 'border',
			items: [{
	            region: 'west',
	            width: 200,
	            items: [
		           	this.calendar
			    ] 
	        }, {
	            region: 'center',
	            layout: 'border',
	            frame: false,
	            items: [{
		            	region: 'north',
		                xtype: 'panel',
		                height: 40,
		                items: [
		                	new Ext.DataView({
								border: false,
								frame: false,
								id: 'MPS_TopDataView',
								itemSelector: 'div',
								region: 'center',
								store: new Ext.data.JsonStore({
									autoLoad: false,
									fields: [
										{ name: 'SelDay' }
									]
								}),
								style: 'padding: 10px;',
								tpl: new Ext.XTemplate(
									'<tpl for=".">',
									'<div style="font-size: 18px;">Выбранный день: <b>{SelDay}</b></div>',
									'</tpl>'
								)
							})
		                ]
	            	}, 
	            	this.schedule
	            ],
	            xtype: 'panel'
		    }],
			buttons: [{
					iconCls: 'print16',
					text: BTN_FRMPRINT,
					handler: function() { },
					tabIndex: TABINDEX_MPSCHED+90
				},
				{
					text: '-'
				},
				HelpButton(this, TABINDEX_MPSCHED+98),
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function() { this.hide() }.createDelegate(this),
					tabIndex: TABINDEX_MPSCHED+99
				}
			]
	    });
	    sw.Promed.swMPScheduleWindow.superclass.initComponent.apply(this, arguments);
    },
    show: function () {
    	sw.Promed.swMPScheduleWindow.superclass.show.apply(this, arguments);
    	//Сразу загружаем расписание на текущий день
    	this.loadSchedule();
    }
});
