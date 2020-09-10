/**
* swTTRScheduleEditWindow - окно редактирования расписания службы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      05.12.2011
*/

sw.Promed.swTTRScheduleEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	maximized: true,
	id: 'TTRScheduleEditWindow',
	title: WND_TTMSSEW,
	MedService_id: null,
	
    initComponent: function() {
		var win = this;
		
		// Панель редактирования расписания
		this.TTRScheduleEditPanel = new sw.Promed.swTTRScheduleEditPanel({
			id:'TTRScheduleEdit',
			frame: false,
			border: false,
			region: 'center'
		});
		
		this.UslugaComplexGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			border: false,
			region: 'center',
			width: 250,
			split: true,
			header: false,
			id: 'TTRSEW_UslugaComplexGrid',
			autoExpandMax: 2000,
			loadMask: true,
			stripeRows: true,
			enableKeyEvents: true,
			keys: [{
				key: [
					Ext.EventObject.TAB
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

					switch (e.getKey())
					{
						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								this.UslugaComplexGrid.getTopToolbar().items.item('UslugaFilter').focus();
							} else {
								this.buttons[this.buttons.length - 2].focus(true);
							}
						break;
					}
				}.createDelegate(this),
				stopEvent: true
			}],
			store: new Ext.data.JsonStore({
				autoLoad: false,
				url: '/?c=Reg&m=getResourceListForSchedule',
				fields: [
					'Resource_id',
					'Resource_Name'
				],
				listeners: {
					'load': function(store) {
						var field = this.UslugaComplexGrid.getTopToolbar().items.item('UslugaFilter');
						var exp = field.getValue();
						if (exp != "") {
							this.UslugaComplexGrid.getStore().filter('Resource_Name', new RegExp(exp, "i"));
						}
						this.UslugaComplexGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + store.getCount();

						// после загрузки встаём на первый элемент
						if (win.UslugaComplexGrid.getStore().getCount() > 0) {
							win.UslugaComplexGrid.getView().focusRow(0);
							win.UslugaComplexGrid.getSelectionModel().selectRow(0);
						}
					}.createDelegate(this)
				}
			}),
			columns: [
				{dataIndex: 'Resource_id', hidden: true, hideable: false},
				{id: 'autoexpand', header: lang['struktura'], dataIndex: 'Resource_Name', sortable: false}
			],
			tbar: new sw.Promed.Toolbar({
				autoHeight: true,
				items: [{
					xtype: 'label',
					text: lang['filtr'],
					style: 'margin-left: 5px; font-weight: bold'
				}, {
					xtype: 'textfield',
					id: 'UslugaFilter',
					style: 'margin-left: 5px',
					enableKeyEvents: true,
					listeners: {
						'keyup': function(field, e) {
							if (tm) {
								clearTimeout(tm);
							} else {
								var tm = null;
							}
							tm = setTimeout(function () {
									var field = this.UslugaComplexGrid.getTopToolbar().items.item('UslugaFilter');
									var exp = field.getValue();
									this.UslugaComplexGrid.getStore().filter('UslugaComplex_Name', new RegExp(exp, "i"));
									this.UslugaComplexGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + this.UslugaComplexGrid.getStore().getCount();
									field.focus();
								}.createDelegate(this),
								100
							);
						}.createDelegate(this),
						'keydown': function (inp, e) {
							if (e.getKey() == Ext.EventObject.TAB )
							{
								e.stopEvent();
								if  (e.shiftKey == false) {
									if ( this.UslugaComplexGrid.getStore().getCount() > 0 )
									{
										this.UslugaComplexGrid.getView().focusRow(0);
										this.UslugaComplexGrid.getSelectionModel().selectFirstRow();
									}
								} else {
									this.StructureTree.focus();
								}
							}
						}.createDelegate(this)
					}
				},
				{
					xtype: 'tbfill'
				}, {
					text: '0 / 0',
					xtype: 'tbtext'
				}]
			}),
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true,
				listeners: {
					'rowselect': function(sm, rowIdx, r) {
						log(r)
						
						this.UslugaComplexGrid.getTopToolbar().items.items[3].el.innerHTML = (rowIdx + 1) + ' / ' + this.UslugaComplexGrid.getStore().getCount();

						if (r.data.Resource_id) {
							// разрешаем редактировать расписание
							this.TTRScheduleEditPanel.getTopToolbar().items.items[6].enable();
							this.TTRScheduleEditPanel.MedService_id = null;
							this.TTRScheduleEditPanel.Resource_id = r.data.Resource_id;
						} else {
							// запрещаем редактировать расписание
							this.TTRScheduleEditPanel.getTopToolbar().items.items[6].disable();
							this.TTRScheduleEditPanel.MedService_id = this.MedService_id;
							this.TTRScheduleEditPanel.Resource_id = null;
						}
						
						this.TTRScheduleEditPanel.doResetAnnotationDate(this.TTRScheduleEditPanel.calendar.value);
						this.TTRScheduleEditPanel.loadSchedule(this.TTRScheduleEditPanel.calendar.value);
					}.createDelegate(this)
				}
			})
		});
		
		this.LeftPanel = new Ext.Panel({
			id: 'TTRScheduleEditLefPanel',
			layout:'border',
			border: false,
			region: 'west',
			width: 250,
			split: true,
			items: [
				this.UslugaComplexGrid
			]
		});
	    
	    Ext.apply(this, {
	    	border: false,
	    	layout: 'border',
			items: [
				this.LeftPanel,
				this.TTRScheduleEditPanel
			],
			buttons: [
				{
					text: '-'
				},
				{
					text: BTN_FRMHELP,
					iconCls: 'help16',
					handler: function(button, event) {
						ShowHelp(lang['rabota_s_zapisyu']);
					}.createDelegate(this)
				},
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: function() { this.hide() }.createDelegate(this)
				}
			],
			keys: [{
				key: [
					Ext.EventObject.F2,
					Ext.EventObject.F5,
					Ext.EventObject.F9
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

					switch (e.getKey())
					{
						case Ext.EventObject.F2:
							this.TTRScheduleEditPanel.openFillWindow();
						break;
						
						case Ext.EventObject.F5:
							this.TTRScheduleEditPanel.loadSchedule();
						break;

						case Ext.EventObject.F9:
							this.TTRScheduleEditPanel.printSchedule();
						break;
					}
				},
				scope: this,
				stopEvent: false
			}]
	    });
	    sw.Promed.swTTRScheduleEditWindow.superclass.initComponent.apply(this, arguments);
    },
	
    show: function () {
    	sw.Promed.swTTRScheduleEditWindow.superclass.show.apply(this, arguments);
		
		var title = WND_TTMSSEW;
		if (arguments[0] && arguments[0].isApparatus) {
			title = WND_TTMSSAPPEW;
		}

		if (arguments[0] && arguments[0]['readOnly']) {
			this.TTRScheduleEditPanel.setReadOnly(arguments[0]['readOnly']);
		}
		
		// Если в качестве параметра был передан MedService_id, то берём ее
		if (arguments[0] && arguments[0]['MedService_id']) {
			this.TTRScheduleEditPanel.MedService_id = arguments[0]['MedService_id'];
			this.MedService_id = arguments[0]['MedService_id'];
			this.setTitle(title + ' (' + arguments[0]['MedService_Name'] + ')');
		} else { // иначе, мы открываем форму из рабочего места врача параклиники то отделение берём из глобальных параметров
			this.TTRScheduleEditPanel.MedService_id = null;
			this.setTitle(title + ' (' + getGlobalOptions().CurMedService_Name + ')');
		}
		
		if(arguments[0].userClearTimeR) {
			this.TTRScheduleEditPanel.userClearTimeR = arguments[0].userClearTimeR;
		}
		
		// Очищаем панель расписания
    	this.TTRScheduleEditPanel.getSchedule().body.update('');
		// Запрещаем создавать расписание
		this.TTRScheduleEditPanel.getTopToolbar().items.items[6].disable();
		
		// Сразу загружаем список слуг службы
		this.UslugaComplexGrid.getStore().removeAll();
		this.UslugaComplexGrid.getStore().load({
			params: {
				MedService_id: this.TTRScheduleEditPanel.MedService_id,
				withMedservice: 1
			}
		});
    }
});
