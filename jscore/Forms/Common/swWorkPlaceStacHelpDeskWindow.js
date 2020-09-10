/**
* АРМ справочного стола стационара
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      04.2012
*/
sw.Promed.swWorkPlaceStacHelpDeskWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,
{
	useUecReader: true,
	ARMType: 'sprst',
	id: 'swWorkPlaceStacHelpDeskWindow',
	gridPanelAutoLoad: false,
	show: function()
	{
		sw.Promed.swWorkPlaceStacHelpDeskWindow.superclass.show.apply(this, arguments);
		
		// Свои функции при открытии
		this.doReset();
		this.FilterPanel.fieldSet.expand();
		
		if(!arguments[0]) {
			arguments = [{}];
		}
		
		this.ARMType = arguments[0].ARMType || null;
		this.ARMName = arguments[0].ARMName || null;

		this.params = {};
		
		if ( arguments[0].MedService_id ) {
			this.params.MedService_id = arguments[0].MedService_id;
		} else {
			// Не понятно, что за АРМ открывается 
			this.hide();
			return false;
		}
		this.params.MedServiceType_SysNick = arguments[0].MedServiceType_SysNick || null;
		this.params.MedService_Name = arguments[0].MedService_Name || null;
		this.params.MedService_Nick = arguments[0].MedService_Nick || null;
		this.params.Lpu_id = arguments[0].Lpu_id || null;
		this.params.Lpu_Nick = arguments[0].Lpu_Nick || null;
		this.params.LpuUnit_id = arguments[0].LpuUnit_id || null;
		this.params.LpuUnit_Name = arguments[0].LpuUnit_Name || null;
		this.params.LpuUnitType_id = arguments[0].LpuUnitType_id || null;
		this.params.LpuUnitType_SysNick = arguments[0].LpuUnitType_SysNick || null;
		this.params.MedPersonal_id = arguments[0].MedPersonal_id || null;
		this.params.MedPersonal_FIO = arguments[0].MedPersonal_FIO || null;

		var lpusection_combo = this.FilterPanel.getForm().findField('LpuSection_id');
		
		if ( lpusection_combo.getStore().getCount() == 0 ) {
			setLpuSectionGlobalStoreFilter({
				allowLowLevel: 'yes',
				isStac: true
			});
			lpusection_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		}
	},
	doReset: function() {
		/*var date1 = Date.parseDate(getGlobalOptions().date, 'd.m.Y').add(Date.DAY, -7).clearTime();
		var date2 = Date.parseDate(getGlobalOptions().date, 'd.m.Y').clearTime();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));*/
		var base_form = this.FilterPanel.getForm();
		base_form.reset();
		this.GridPanel.removeAll();
		this.GridPanel.getGrid().getStore().removeAll();
		this.GridPanel.getGrid().params = {};
	},
	getButtonSearch: function() {
		return Ext.getCmp(this.id+'BtnSearch');
	},
	openEvnPSEditWindow: function() {
		var params = new Object();
		var grid = this.GridPanel.getGrid();
		var win = this;

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		
		var selected_record = grid.getSelectionModel().getSelected();

		var evn_ps_id = selected_record.get('EvnPS_id');
		var person_id = selected_record.get('Person_id');
		var server_id = selected_record.get('Server_id');
		var ok = (evn_ps_id > 0) && (person_id > 0) && (server_id >= 0);

		if ( ok ) {
			params.EvnPS_id = evn_ps_id;
			params.onHide = function() {
				if (!win.isVisible()) {
					return false;
				}
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
			params.Person_id = person_id;
			params.Server_id = server_id;
			params.action = 'view';

			getWnd('swEvnPSEditWindow').show(params);
		}

	},
	doSearch: function(params) {
		var params = this.FilterPanel.getForm().getValues();
		
		params.beg_date = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.end_date = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');

		this.GridPanel.removeAll();
		this.GridPanel.getGrid().getStore().removeAll();
		this.GridPanel.getGrid().params = params;
		this.GridPanel.getGrid().getStore().load({
			params: params
		});
	},

	initComponent: function() {
		var form = this;
		
		this.buttonPanelActions = {
			action_PersonEvnPSList: {
			nn:'action_PersonEvnPSList',
			text:'Контроль движения оригиналов ИБ',
			tooltip: 'Открыть контроль движения оригиналов ИБ',
			iconCls : 'patient-data32',
			handler: function() { getWnd('swPersonEvnPSListWindow').show(); }.createDelegate(this)
		},
			action_Timetable: {nn:'action_Timetable', text:'Расписание', tooltip: 'Ведение расписания', iconCls : 'mp-timetable32', disabled: false, /*hidden: !IS_DEBUG,*/ handler: function() { getWnd('swScheduleEditMasterWindow').show(form.params); }},
			action_LpuStructure: {nn:'action_LpuStructure', text:'Структура ЛПУ', tooltip: 'Структура ЛПУ', iconCls : 'structure32', disabled: false, handler: function() {getWnd('swLpuStructureViewForm').show({action: 'view', Lpu_id: getGlobalOptions().lpu_id});}},
			action_LpuPassport: {nn:'action_LpuPassport', text:'Паспорт ЛПУ', tooltip: 'Паспорт ЛПУ', iconCls : 'lpu-passport16', disabled: false, handler: function() {getWnd('swLpuPassportEditWindow').show({action: 'view', Lpu_id: getGlobalOptions().lpu_id});}}
		};
	
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);
		
		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form, 
			filter: {
				title: lang['filtr'],
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 65,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 170,
							name: 'Person_Surname',
							fieldLabel: lang['familiya'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 39,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 170,
							name: 'Person_Firname',
							fieldLabel: lang['imya'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 65,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 170,
							name: 'Person_Secname',
							fieldLabel: lang['otchestvo'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 39,
						items:
						[{
							xtype: 'swdatefield',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							format: 'd.m.Y',
							width: 90,
							name: 'Person_Birthday',
							fieldLabel: lang['d_r'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 90,
						items:
						[{
							xtype: 'textfield',
							width: 60,
							name: 'EvnPS_NumCard',
							fieldLabel: lang['nomer_kartyi'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 90,
						style: "padding-left: 10px",
						items:
						[{
							hiddenName: 'LpuSection_id',
							emptyText: lang['otdelenie_lpu'],
							hideLabel: true,
							lastQuery: '',
							listWidth: 350,
							width: 350,
							xtype: 'swlpusectionglobalcombo',
							//fieldLabel: 'Отделение ЛПУ',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						items: 
						[{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id+'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function()
							{
								form.doSearch();
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items: 
						[{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id+'BtnClear',
							text: lang['sbros'],
							iconCls: 'reset16',
							handler: function()
							{
								form.doReset();
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								text: lang['schitat_s_kartyi'],
								iconCls: 'idcard16',
								handler: function()
								{
									form.readFromCard();
								}
							}]
					}]
				}]
			}
		});
		
		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true, handler: function() { return false; }.createDelegate(this)},
				{name: 'action_edit', disabled: true, hidden: true, handler: function() { return false; }.createDelegate(this)},
				{name: 'action_view', handler: function() { this.openEvnPSEditWindow(); }.createDelegate(this)},
				{name: 'action_delete', disabled: true, hidden: true, handler: function() { return false; }.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=EvnPS&m=loadWorkPlaceSprst',
			object: 'EvnPS',
			onRowSelect: function(sm, index, record) {

			}.createDelegate(this),
			pageSize: 100,
			paging: false,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'EvnPS_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'EvnPS_NumCard', type: 'string', header: lang['№_kartyi'], width: 70},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], id: 'autoexpand'},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 200},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 200},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 90},
				{name: 'EvnPS_setDate', type: 'date', format: 'd.m.Y', header: lang['postuplenie'], width: 90},
				{name: 'EvnPS_disDate', type: 'date', format: 'd.m.Y', header: lang['vyipiska'], width: 90},
				{name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 150 },
				{name: 'EvnPS_KoikoDni', type: 'int', header: lang['k_dni'], width: 90},
				{name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkbox', width: 30},
				{name: 'PayType_Name', type: 'string', header: lang['vid_oplatyi'], width: 100 },
				{name: 'LeaveType_Name', type: 'string', header: lang['ishod'], width: 100 },
				{name: 'MP_Fio', type: 'string', header: lang['vrach'], width: 200 }
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this)
		});
		
		sw.Promed.swWorkPlaceStacHelpDeskWindow.superclass.initComponent.apply(this, arguments);
	}
});
