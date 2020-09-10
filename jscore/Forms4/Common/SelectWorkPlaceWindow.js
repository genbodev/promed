/**
* swSelectWorkPlaceWindow - окно выбора места работы врача, в случае если у врача их несколько
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* 
*/
Ext6.define('common.SelectWorkPlaceWindow', {
	alias: 'widget.swSelectWorkPlaceWindowExt6',
	width: 720,	height: 345,
	title: langs('Выбор места работы (АРМ) по умолчанию'),
	cls: 'arm-window-new ',
	
	noTaskBarButton: true,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	extend: 'base.BaseForm',
	renderTo: Ext6.getBody(),
	layout: 'border',
	constrain: true,
	addCodeRefresh: Ext6.emptyFn,
	modal: true,
	resizable: true,
	
	show: function() {
		this.callParent(arguments);
		
		this.grid.getStore().load();
	},
	
	/**
	 * Функция установки места работы врача по умолчанию.
	 * Записывает данные в локальные настройки пользователя (LDAP)
	 * Сохраняемые значения: название арма, тип арма, врач, отделение, служба
	 */
	setDefaultWorkPlace: function(record) {
		var win = this;
		win.loadMask.show();
		Ext6.Ajax.request({
			url: '/?c=User&m=setDefaultWorkPlace',
			params: record,
			callback: function(options, success, response) {
				win.loadMask.hide();
				if (success) {
					// Думаем что все сохранилось в настройках
					var result = Ext6.util.JSON.decode(response.responseText);
					// Устанавливаем правильные глобальные переменные 
					Ext.globalOptions.defaultARM = result;
					sw.Promed.MedStaffFactByUser.selectARM({
						ARMType: 'common',
						onSelect: null
					});
					win.hide();
				}
			}
		});
	},
	onSelect: function() {
		var sm = this.grid.getSelectionModel();
		if ( sm.hasSelection() ) {
			var record = sm.getSelection()[0];
			// Установить выбранное место работы по умолчанию 
			this.setDefaultWorkPlace(record.data);
		}
		else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Выберите необходимое место работы для того, <br/>чтобы установить его основным при входе в АРМ.<br/>'));
		}
	},
	initComponent: function() {
		var win = this;
		win.loadMask = new Ext6.LoadMask(win, { msg: LOAD_WAIT });
		
		win.grid = new Ext6.grid.GridPanel({
			xtype: 'grid',
			cls: 'EmkGrid ARMselect',
			region: 'center',
			columns:[{
				dataIndex: 'ARMNameLpu',
				header: langs('АРМ/МО'),
				resizable: false,
				sortable: false,
				width: '30%'
			}, {
				dataIndex: 'Name',
				header: 'Подразделение / Отделение / Служба',
				resizable: false,
				sortable: false,
				width: '37%'
			}, {
				dataIndex: 'PostMed_Name',
				header: langs('Должность'),
				resizable: false,
				sortable: false,
				flex: 1
			}, {
				dataIndex: 'Timetable_isExists',
				header: langs('Расписание'),
				resizable: false,
				renderer: sw.Promed.Format.checkColumn,
				sortable: false,
				width: 85
			}, {
				dataIndex: 'MedStaffFact_id',
				hidden: true
			}, {
				dataIndex: 'LpuSection_id',
				hidden: true
			}, {
				dataIndex: 'MedPersonal_id',
				hidden: true
			}, {
				dataIndex: 'PostMed_Code',
				hidden: true
			}, {
				dataIndex: 'PostMed_id',
				hidden: true
			}, {
				dataIndex: 'MedService_id',
				hidden: true
			}, {
				dataIndex: 'MedService_Name',
				hidden: true
			}, {
				dataIndex: 'MedServiceType_SysNick',
				hidden: true
			}],
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					this.onSelect();
				}.createDelegate(this)
			},
			store: new Ext6.create('Ext6.data.Store', {
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=User&m=getMSFList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: {
					property: 'ARMNameLpu',
					direction: 'ASC'
				},
				reader: new Ext6.data.JsonReader(
				{
					id: 'id'
				},
				[
					{name: 'id', mapping: 'id'},
					{name: 'MedStaffFact_id', mapping: 'MedStaffFact_id'},
					{name: 'LpuSection_id', mapping: 'LpuSection_id'},
					{name: 'MedPersonal_id', mapping: 'MedPersonal_id'},
					{name: 'LpuSection_Name', type: 'string', mapping: 'LpuSection_Name'},
					{name: 'LpuSection_Nick', type: 'string', mapping: 'LpuSection_Nick'},
					{name: 'LpuSectionProfile_id', type: 'string', mapping: 'LpuSectionProfile_id'},
					{name: 'LpuSectionProfile_Code', type: 'string', mapping: 'LpuSectionProfile_Code'},
					{name: 'LpuSectionProfile_Name', type: 'string', mapping: 'LpuSectionProfile_Name'},
					{name: 'PostMed_Name', type: 'string', mapping: 'PostMed_Name'},
					{name: 'PostMed_Code', type: 'string', mapping: 'PostMed_Code'},
					{name: 'PostMed_id', mapping: 'PostMed_id'},
					{name: 'LpuBuilding_id', mapping: 'LpuBuilding_id'},
					{name: 'LpuBuilding_Name', type: 'string', mapping: 'LpuBuilding_Name'},
					{name: 'LpuUnit_id', mapping: 'LpuUnit_id'},
					{name: 'LpuUnit_Name', type: 'string', mapping: 'LpuUnit_Name'},
					{name: 'Timetable_isExists', type: 'string', mapping: 'Timetable_isExists'},
					{name: 'LpuUnitType_SysNick', type: 'string', mapping: 'LpuUnitType_SysNick'},
					{name: 'LpuUnitType_id', mapping: 'LpuUnitType_id'},
					{name: 'MedService_id', mapping: 'MedService_id'},
					{name: 'MedService_Nick', type: 'string', mapping: 'MedService_Nick'},
					{name: 'MedService_Name', type: 'string', mapping: 'MedService_Name'},
					{name: 'MedServiceType_SysNick', type: 'string', mapping: 'MedServiceType_SysNick'},
					{name: 'MedService_IsExternal', type: 'int', mapping: 'MedService_IsExternal'},
					{name: 'MedService_IsLocalCMP', type: 'int', mapping: 'MedService_IsLocalCMP'},
					{name: 'MedService_LocalCMPPath', type: 'string', mapping: 'MedService_LocalCMPPath'},
					{name: 'Name', type: 'string', mapping: 'Name'},
					{name: 'Lpu_Nick', type: 'string', mapping: 'Lpu_Nick'},
					{name: 'Org_Nick', type: 'string', mapping: 'Org_Nick'},
					{name: 'ARMNameLpu', mapping: 'ARMNameLpu'},
					{name: 'Lpu_id', type: 'int', mapping: 'Lpu_id'},
					{name: 'Org_id', type: 'int', mapping: 'Org_id'},
					{name: 'ARMType', mapping: 'ARMType'},
					{name: 'ARMName', mapping: 'ARMName'},
					{name: 'ARMForm', mapping: 'ARMForm'},
					{name: 'ARMType_id', mapping: 'ARMType_id'},
					{name: 'MedicalCareKind_id', mapping: 'MedicalCareKind_id'},
					{name: 'PostKind_id', mapping: 'PostKind_id'},
					{name: 'MedStaffFactLink_id', mapping: 'MedStaffFactLink_id'},
					{name: 'MedStaffFactLink_begDT', mapping: 'MedStaffFactLink_begDT'},
					{name: 'MedStaffFactLink_endDT', mapping: 'MedStaffFactLink_endDT'},
					{name: 'Post_Name', type: 'string', mapping: 'Post_Name'},
					{name: 'MedPersonal_FIO', mapping: 'MedPersonal_FIO'},
					{name: 'UslugaComplex_Name', mapping: 'UslugaComplex_Name'},
					{name: 'MedStaffFactCache_IsDisableInDoc', mapping: 'MedStaffFactCache_IsDisableInDoc'},
					{name: 'ElectronicService_id', mapping: 'ElectronicService_id'},
					{name: 'ElectronicService_Num', mapping: 'ElectronicService_Num'},
					{name: 'ElectronicService_Name', mapping: 'ElectronicService_Name'},
					{name: 'ElectronicQueueInfo_id', type: 'int', mapping: 'ElectronicQueueInfo_id'},
					{name: 'ElectronicService_isShownET', type: 'int', mapping: 'ElectronicService_isShownET'},
					{name: 'ElectronicTreatment_ids', type: 'string', mapping: 'ElectronicTreatment_ids'},
					{name: 'ElectronicQueueInfo_CallTimeSec', type: 'int', mapping: 'ElectronicQueueInfo_CallTimeSec'},
					{name: 'ElectronicQueueInfo_PersCallDelTimeMin', type: 'int', mapping: 'ElectronicQueueInfo_PersCallDelTimeMin'},
					{name: 'ElectronicQueueInfo_CallCount', type: 'int', mapping: 'ElectronicQueueInfo_CallCount'},
					{name: 'ElectronicScoreboard_id', type: 'int', mapping: 'ElectronicScoreboard_id'},
					{name: 'ElectronicScoreboard_IPaddress', type: 'string', mapping: 'ElectronicScoreboard_IPaddress'},
					{name: 'ElectronicScoreboard_Port', type: 'int', mapping: 'ElectronicScoreboard_Port'},
					{name: 'UslugaComplexMedService_id', type: 'int', mapping: 'UslugaComplexMedService_id'},
					{name: 'UslugaComplex_id', type: 'int', mapping: 'UslugaComplex_id'},
					{name: 'client', mapping: 'client'}
				]),
				url: '/?c=User&m=getMSFList'
			})
		});
		
		win.MainPanel = new Ext6.form.FormPanel({
			bodyPadding: 30,
			bodyStyle: 'background-color: white;',
			region: 'center',
			layout: 'border',
			border: false,
			items:[ win.grid ]
		});
		
		Ext6.apply(win, {
			items: [
				win.MainPanel
			],
			buttons: ['->',
			{
				text: langs('ОТМЕНА'),
				itemId: 'button_cancel',
				userCls:'buttonPoupup buttonCancel',
				handler:function () {
					win.hide();
				}
			},
			{
				text: langs('ПРИМЕНИТЬ'),
				itemId: 'button_save',
				userCls:'buttonPoupup buttonAccept',
				handler: function() {
					this.onSelect();
				}.createDelegate(this)
			}
			]
		});

		this.callParent(arguments);
	}
});