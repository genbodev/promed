/**
 * swDirectionMasterMisRbWindow - Мастер выписки направления в МИС РБ.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Direction
 * @access       public
 * @copyright    Copyright (c) 2009-2016 Swan Ltd.
 * @author
 * @version      05.09.2016
 * @comment
 **/
/*NO PARSE JSON*/
sw.Promed.swDirectionMasterMisRbWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swDirectionMasterMisRbWindow',
	objectSrc: '/jscore/Forms/Direction/swDirectionMasterMisRbWindow.js',
	title: 'Мастер выписки направления в МИС РБ',
	layout: 'border',
	maximized: true,
	minHeight: 400,
	minWidth: 700,
	modal: true,
	plain: true,
	id: 'swDirectionMasterMisRbWindow',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	initComponent: function() {
		var win = this;

		this.selectDistrictFilterPanel = new Ext.FormPanel({
			layout: 'form',
			border: false,
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 200,
			region: 'north',
			keys: [{
				fn: function(e) {
					win.selectDistrictFilterPanel.applyFilter();
				},
				key: Ext.EventObject.ENTER,
				scope: this,
				stopEvent: true
			}],
			items: [{
				fieldLabel: 'ОКАТО',
				name: 'Okato',
				xtype: 'textfield'
			}, {
				fieldLabel: 'Район',
				name: 'DistrictName',
				xtype: 'textfield'
			}],
			loadData: function() {
				win.selectDistrictViewFrame.loadData({
					callback: function(records, options, success) {
						// если не получилось загрузить, то выходим из формы
						if (records.length == 0) {
							sw.swMsg.alert('Внимание', 'Запись пациента невозможна. Обратитесь к администратору системы.', function () {
								win.hide();
							});
						}
					}
				});
			},
			applyFilter: function() {
				var filter_form = win.selectDistrictFilterPanel.getForm();
				win.selectDistrictViewFrame.getGrid().getStore().filterBy(function(rec) {
					var result = true;
					if (result && !Ext.isEmpty(filter_form.findField('Okato').getValue())) {
						// проверяем Okato
						result = result && !Ext.isEmpty(rec.get('Okato')) && rec.get('Okato').indexOf(filter_form.findField('Okato').getValue()) !== -1
					}
					if (result && !Ext.isEmpty(filter_form.findField('DistrictName').getValue())) {
						result = result && !Ext.isEmpty(rec.get('DistrictName')) && rec.get('DistrictName').toLowerCase().indexOf(filter_form.findField('DistrictName').getValue().toLowerCase()) !== -1
					}
					return result;
				});
			},
			clearFilters: function() {
				this.getForm().reset();
				this.applyFilter();
			},
			bbar: [{
				xtype: 'button',
				text: lang['nayti'],
				iconCls: 'search16',
				handler: function () {
					win.selectDistrictFilterPanel.applyFilter();
				}
			}, {
				xtype: 'button',
				text: lang['sbros'],
				iconCls: 'resetsearch16',
				handler: function () {
					win.selectDistrictFilterPanel.clearFilters(true);
				}
			}, {
				xtype: 'tbseparator'
			}]
		});

		this.selectDistrictViewFrame = new sw.Promed.ViewFrame({
			region: 'center',
			uniqueId: true,
			stringfields: [
				{name: 'IdDistrict', type: 'int', header: 'ID', key: true},
				{name: 'Okato', header: 'ОКАТО района'},
				{name: 'DistrictName', id: 'autoexpand', header: 'Наименование района', sort: true}
			],
			onLoadData: function() {
				win.selectDistrictFilterPanel.applyFilter();
			},
			onDblClick: function() {
				this.onEnter();
			},
			onRowSelect: function(sm, index, record) {
				if (record && record.get('IdDistrict')) {
					win.masterData.IdDistrict = record.get('IdDistrict');
					win.masterData.DistrictName = record.get('DistrictName');
				}
			},
			onEnter: function() {
				var record = this.getGrid().getSelectionModel().getSelected();

				if (record && record.get('IdDistrict')) {
					win.masterData.IdDistrict = record.get('IdDistrict');
					win.masterData.DistrictName = record.get('DistrictName');
					// идём в следующий шаг
					win.setStep('selectLpu');
				}
			},
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			dataUrl: '/?c=MisRB&m=getDistrictList',
			autoLoadData: false
		});

		this.selectDistrictPanel = new sw.Promed.Panel({
			layout: 'border',
			items: [
				win.selectDistrictFilterPanel,
				win.selectDistrictViewFrame
			]
		});

		this.selectLpuFilterPanel = new Ext.FormPanel({
			layout: 'form',
			border: false,
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 200,
			region: 'north',
			keys: [{
				fn: function(e) {
					win.selectLpuFilterPanel.applyFilter();
				},
				key: Ext.EventObject.ENTER,
				scope: this,
				stopEvent: true
			}],
			items: [{
				fieldLabel: 'Тип МО',
				name: 'LPUType',
				xtype: 'textfield'
			}, {
				fieldLabel: 'Краткое наименование МО',
				name: 'LPUShortName',
				xtype: 'textfield'
			}, {
				fieldLabel: 'Полное наименование МО',
				name: 'LPUFullName',
				xtype: 'textfield'
			}],
			loadData: function() {
				var params = {
					IdDistrict: win.masterData.IdDistrict
				};
				win.selectLpuViewFrame.loadData({
					globalFilters: params
				});
			},
			applyFilter: function() {
				var filter_form = win.selectLpuFilterPanel.getForm();
				win.selectLpuViewFrame.getGrid().getStore().filterBy(function(rec) {
					var result = true;
					if (result && !Ext.isEmpty(filter_form.findField('LPUType').getValue())) {
						result = result && !Ext.isEmpty(rec.get('LPUType')) && rec.get('LPUType').toLowerCase().indexOf(filter_form.findField('LPUType').getValue().toLowerCase()) !== -1
					}
					if (result && !Ext.isEmpty(filter_form.findField('LPUShortName').getValue())) {
						result = result && !Ext.isEmpty(rec.get('LPUShortName')) && rec.get('LPUShortName').toLowerCase().indexOf(filter_form.findField('LPUShortName').getValue().toLowerCase()) !== -1
					}
					if (result && !Ext.isEmpty(filter_form.findField('LPUFullName').getValue())) {
						result = result && !Ext.isEmpty(rec.get('LPUFullName')) && rec.get('LPUFullName').toLowerCase().indexOf(filter_form.findField('LPUFullName').getValue().toLowerCase()) !== -1
					}
					return result;
				});
			},
			clearFilters: function() {
				this.getForm().reset();
				this.applyFilter();
			},
			bbar: [{
				xtype: 'button',
				text: lang['nayti'],
				iconCls: 'search16',
				handler: function () {
					win.selectLpuFilterPanel.applyFilter();
				}
			}, {
				xtype: 'button',
				text: lang['sbros'],
				iconCls: 'resetsearch16',
				handler: function () {
					win.selectLpuFilterPanel.clearFilters(true);
				}
			}, {
				xtype: 'tbseparator'
			}]
		});

		this.selectLpuViewFrame = new sw.Promed.ViewFrame({
			region: 'center',
			uniqueId: true,
			stringfields: [
				{name: 'IdLPU', type: 'int', header: 'ID', key: true},
				{name: 'LPUShortName', header: 'Краткое наименование МО'},
				{name: 'LPUFullName', id: 'autoexpand', header: 'Полное наименование МО', sort: true},
				{name: 'LPUType', header: 'Тип'}
			],
			onLoadData: function() {
				win.selectLpuFilterPanel.applyFilter();
			},
			onDblClick: function() {
				this.onEnter();
			},
			onRowSelect: function(sm, index, record) {
				if (record && record.get('IdLPU')) {
					win.masterData.IdLPU = record.get('IdLPU');
					win.masterData.LPUShortName = record.get('LPUShortName');
				}
			},
			onEnter: function() {
				var record = this.getGrid().getSelectionModel().getSelected();

				if (record && record.get('IdLPU')) {
					win.masterData.IdLPU = record.get('IdLPU');
					win.masterData.LPUShortName = record.get('LPUShortName');
					// идём в следующий шаг
					win.setStep('selectSpesiality');
				}
			},
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			dataUrl: '/?c=MisRB&m=getLpuList',
			autoLoadData: false
		});

		this.selectLpuPanel = new sw.Promed.Panel({
			layout: 'border',
			items: [
				win.selectLpuFilterPanel,
				win.selectLpuViewFrame
			]
		});

		this.selectSpesialityFilterPanel = new Ext.FormPanel({
			layout: 'form',
			border: false,
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 200,
			region: 'north',
			keys: [{
				fn: function(e) {
					win.selectSpesialityFilterPanel.applyFilter();
				},
				key: Ext.EventObject.ENTER,
				scope: this,
				stopEvent: true
			}],
			items: [{
				fieldLabel: 'Специальность',
				name: 'NameSpesiality',
				xtype: 'textfield'
			}],
			loadData: function() {
				var params = {
					IdLPU: win.masterData.IdLPU
				};
				win.selectSpesialityViewFrame.loadData({
					globalFilters: params
				});
			},
			applyFilter: function() {
				var filter_form = win.selectSpesialityFilterPanel.getForm();
				win.selectSpesialityViewFrame.getGrid().getStore().filterBy(function(rec) {
					var result = true;
					if (result && !Ext.isEmpty(filter_form.findField('NameSpesiality').getValue())) {
						result = result && !Ext.isEmpty(rec.get('NameSpesiality')) && rec.get('NameSpesiality').toLowerCase().indexOf(filter_form.findField('NameSpesiality').getValue().toLowerCase()) !== -1
					}
					return result;
				});
			},
			clearFilters: function() {
				this.getForm().reset();
				this.applyFilter();
			},
			bbar: [{
				xtype: 'button',
				text: lang['nayti'],
				iconCls: 'search16',
				handler: function () {
					win.selectSpesialityFilterPanel.applyFilter();
				}
			}, {
				xtype: 'button',
				text: lang['sbros'],
				iconCls: 'resetsearch16',
				handler: function () {
					win.selectSpesialityFilterPanel.clearFilters(true);
				}
			}, {
				xtype: 'tbseparator'
			}]
		});

		this.selectSpesialityViewFrame = new sw.Promed.ViewFrame({
			region: 'center',
			uniqueId: true,
			stringfields: [
				{name: 'IdSpesiality', type: 'int', header: 'ID', key: true},
				{name: 'NameSpesiality', id: 'autoexpand', header: 'Специальность', sort: true},
				{name: 'CountFreeTicket', header: 'Общее количество свободных бирок'},
				{name: 'CountFreeParticipantIE', header: 'Количество доступных бирок для записи'},
				{name: 'LastDate', header: 'Дата последней доступной бирки'},
				{name: 'NearestDate', header: 'Дата первой доступной бирки'}
			],
			onLoadData: function() {
				win.selectSpesialityFilterPanel.applyFilter();
			},
			onDblClick: function() {
				this.onEnter();
			},
			onRowSelect: function(sm, index, record) {
				if (record && record.get('IdSpesiality')) {
					win.masterData.IdSpesiality = record.get('IdSpesiality');
					win.masterData.NameSpesiality = record.get('NameSpesiality');
				}
			},
			onEnter: function() {
				var record = this.getGrid().getSelectionModel().getSelected();

				if (record && record.get('IdSpesiality')) {
					win.masterData.IdSpesiality = record.get('IdSpesiality');
					win.masterData.NameSpesiality = record.get('NameSpesiality');
					// идём в следующий шаг
					win.setStep('selectDoctor');
				}
			},
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', text: 'Поставить в очередь', handler: function() {
					win.setWaiting();
				}},
				{name:'action_delete', disabled: true, hidden: true}
			],
			dataUrl: '/?c=MisRB&m=getSpesialityList',
			autoLoadData: false
		});

		this.selectSpesialityPanel = new sw.Promed.Panel({
			layout: 'border',
			items: [
				win.selectSpesialityFilterPanel,
				win.selectSpesialityViewFrame
			]
		});

		this.selectDoctorFilterPanel = new Ext.FormPanel({
			layout: 'form',
			border: false,
			frame: true,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 200,
			region: 'north',
			keys: [{
				fn: function(e) {
					win.selectDoctorFilterPanel.applyFilter();
				},
				key: Ext.EventObject.ENTER,
				scope: this,
				stopEvent: true
			}],
			items: [{
				fieldLabel: 'ФИО врача',
				name: 'Name',
				xtype: 'textfield'
			}],
			loadData: function() {
				var params = {
					IdSpesiality: win.masterData.IdSpesiality,
					IdLPU: win.masterData.IdLPU
				};
				win.selectDoctorViewFrame.loadData({
					globalFilters: params
				});
			},
			applyFilter: function() {
				var filter_form = win.selectDoctorFilterPanel.getForm();
				win.selectDoctorViewFrame.getGrid().getStore().filterBy(function(rec) {
					var result = true;
					if (result && !Ext.isEmpty(filter_form.findField('Name').getValue())) {
						result = result && !Ext.isEmpty(rec.get('Name')) && rec.get('Name').toLowerCase().indexOf(filter_form.findField('Name').getValue().toLowerCase()) !== -1
					}
					return result;
				});
			},
			clearFilters: function() {
				this.getForm().reset();
				this.applyFilter();
			},
			bbar: [{
				xtype: 'button',
				text: lang['nayti'],
				iconCls: 'search16',
				handler: function () {
					win.selectDoctorFilterPanel.applyFilter();
				}
			}, {
				xtype: 'button',
				text: lang['sbros'],
				iconCls: 'resetsearch16',
				handler: function () {
					win.selectDoctorFilterPanel.clearFilters(true);
				}
			}, {
				xtype: 'tbseparator'
			}]
		});

		this.selectDoctorViewFrame = new sw.Promed.ViewFrame({
			region: 'center',
			uniqueId: true,
			stringfields: [
				{name: 'IdDoc', type: 'int', header: 'ID', key: true},
				{name: 'Snils', header: 'СНИЛС врача'},
				{name: 'Name', id: 'autoexpand', header: 'ФИО врача', sort: true},
				{name: 'CountFreeTicket', header: 'Общее количество свободных бирок'},
				{name: 'CountFreeParticipantIE', header: 'Количество доступных бирок для записи'},
				{name: 'LastDate', header: 'Дата последней доступной бирки'},
				{name: 'NearestDate', header: 'Дата первой доступной бирки'}
			],
			onLoadData: function() {
				win.selectDoctorFilterPanel.applyFilter();
			},
			onDblClick: function() {
				this.onEnter();
			},
			onRowSelect: function(sm, index, record) {
				if (record && record.get('IdDoc')) {
					win.masterData.IdDoc = record.get('IdDoc');
					win.masterData.Name = record.get('Name');
				}
			},
			onEnter: function() {
				var record = this.getGrid().getSelectionModel().getSelected();

				if (record && record.get('IdDoc')) {
					win.masterData.IdDoc = record.get('IdDoc');
					win.masterData.Name = record.get('Name');
					// идём в следующий шаг
					win.setStep('selectDate');
				}
			},
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', text: 'Поставить в очередь', handler: function() {
					win.setWaiting();
				}},
				{name:'action_delete', disabled: true, hidden: true}
			],
			dataUrl: '/?c=MisRB&m=getDoctorList',
			autoLoadData: false
		});

		this.selectDoctorPanel = new sw.Promed.Panel({
			layout: 'border',
			items: [
				win.selectDoctorFilterPanel,
				win.selectDoctorViewFrame
			]
		});

		this.selectDateViewFrame = new sw.Promed.ViewFrame({
			region: 'center',
			uniqueId: true,
			stringfields: [
				{name: 'IdDate', type: 'int', header: 'ID', key: true},
				{name: 'Date', id: 'autoexpand', header: 'Дата приёма', sort: true}
			],
			onDblClick: function() {
				this.onEnter();
			},
			onRowSelect: function(sm, index, record) {
				if (record && record.get('Date')) {
					win.masterData.Date = record.get('Date');
				}
			},
			onEnter: function() {
				var record = this.getGrid().getSelectionModel().getSelected();

				if (record && record.get('Date')) {
					win.masterData.Date = record.get('Date');
					// идём в следующий шаг
					win.setStep('selectAppointment');
				}
			},
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', text: 'Поставить в очередь', handler: function() {
					win.setWaiting();
				}},
				{name:'action_delete', disabled: true, hidden: true}
			],
			dataUrl: '/?c=MisRB&m=getDateList',
			autoLoadData: false
		});

		this.selectDatePanel = new sw.Promed.Panel({
			layout: 'border',
			items: [
				win.selectDateViewFrame
			]
		});

		this.selectAppointmentViewFrame = new sw.Promed.ViewFrame({
			region: 'center',
			uniqueId: true,
			stringfields: [
				{name: 'IdAppointment', type: 'string', header: 'ID', key: true},
				{name: 'VisitStart', id: 'autoexpand', header: 'Время приёма', sort: true}
			],
			onDblClick: function() {
				this.onEnter();
			},
			onRowSelect: function(sm, index, record) {
				if (record && record.get('IdAppointment')) {
					win.masterData.IdAppointment = record.get('IdAppointment');
					win.masterData.VisitStart = record.get('VisitStart');
				}
			},
			onEnter: function() {
				var record = this.getGrid().getSelectionModel().getSelected();

				if (record && record.get('IdAppointment')) {
					win.masterData.IdAppointment = record.get('IdAppointment');
					win.masterData.VisitStart = record.get('VisitStart');
					// записываем
					win.setAppointment();
				}
			},
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', text: 'Поставить в очередь', handler: function() {
					win.setWaiting();
				}},
				{name:'action_delete', disabled: true, hidden: true}
			],
			dataUrl: '/?c=MisRB&m=getAppointmentList',
			autoLoadData: false
		});

		this.selectAppointmentPanel = new sw.Promed.Panel({
			layout: 'border',
			items: [
				win.selectAppointmentViewFrame
			]
		});

		this.CardPanel = new sw.Promed.Panel({
			region: 'center',
			layout: 'card',
			activeItem: 0,
			items: [
				win.selectDistrictPanel,
				win.selectLpuPanel,
				win.selectSpesialityPanel,
				win.selectDoctorPanel,
				win.selectDatePanel,
				win.selectAppointmentPanel
			]
		});

		Ext.apply(this, {
			buttons: [{
				iconCls: 'arrow-previous16',
				text: lang['nazad'],
				handler: function () {
					this.setPrevStep();
				}.createDelegate(this)
			}, {
				iconCls: 'home16',
				text: lang['v_nachalo'],
				handler: function () {
					this.setStep('selectDistrict')
				}.createDelegate(this)
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function () {
					win.hide();
				},
				iconCls: 'cancel16',
				onTabAction: function () {
					win.dateMenu.focus(true);
				},
				text: BTN_FRMCLOSE
			}],
			border: false,
			items: [
				win.CardPanel
			]
		});

		sw.Promed.swDirectionMasterMisRbWindow.superclass.initComponent.apply(this, arguments);
	},
	setPrevStep: function() {
		var win = this;
		switch(win.curStep) {
			case 'selectLpu':
				win.setStep('selectDistrict');
				break;
			case 'selectSpesiality':
				win.setStep('selectLpu');
				break;
			case 'selectDoctor':
				win.setStep('selectSpesiality');
				break;
			case 'selectDate':
				win.setStep('selectDoctor');
				break;
			case 'selectAppointment':
				win.setStep('selectDate');
				break;
		}
	},
	curStep: null,
	setStep: function(step) {
		var win = this;
		win.curStep = step;

		var Person_Fio = (this.personData && this.personData.Person_Fio)?this.personData.Person_Fio:'';

		switch(step) {
			case 'selectDistrict':
				win.setTitle('Мастер выписки направления ' + Person_Fio + ' | На консультацию в другую МИС > Выбор района');
				win.buttons[0].hide(); // назад
				win.buttons[1].hide(); // в начало
				win.CardPanel.getLayout().setActiveItem(0);
				win.selectDistrictFilterPanel.loadData();
				win.selectLpuFilterPanel.getForm().reset();

				delete win.masterData.IdDistrict;
				delete win.masterData.DistrictName;
				delete win.masterData.IdLPU;
				delete win.masterData.LPUShortName;
				delete win.masterData.IdSpesiality;
				delete win.masterData.NameSpesiality;
				delete win.masterData.IdDoc;
				delete win.masterData.Name;
				delete win.masterData.Date;
				delete win.masterData.IdAppointment;
				delete win.masterData.VisitStart;
				break;
			case 'selectLpu':
				win.setTitle('Мастер выписки направления ' + Person_Fio + ' | На консультацию в другую МИС > ' + win.masterData.DistrictName + ' > ' + 'Выбор МО');
				win.buttons[0].show(); // назад
				win.buttons[1].show(); // в начало
				win.CardPanel.getLayout().setActiveItem(1);
				win.selectLpuFilterPanel.loadData();
				win.selectSpesialityFilterPanel.getForm().reset();

				delete win.masterData.IdLPU;
				delete win.masterData.LPUShortName;
				delete win.masterData.IdSpesiality;
				delete win.masterData.NameSpesiality;
				delete win.masterData.IdDoc;
				delete win.masterData.Name;
				delete win.masterData.Date;
				delete win.masterData.IdAppointment;
				delete win.masterData.VisitStart;
				break;
			case 'selectSpesiality':
				win.setTitle('Мастер выписки направления ' + Person_Fio + ' | На консультацию в другую МИС > ' + win.masterData.DistrictName + ' > ' + win.masterData.LPUShortName + ' > ' + 'Выбор специальности');
				win.buttons[0].show(); // назад
				win.buttons[1].show(); // в начало
				win.CardPanel.getLayout().setActiveItem(2);
				win.selectSpesialityFilterPanel.loadData();
				win.selectDoctorFilterPanel.getForm().reset();

				delete win.masterData.IdSpesiality;
				delete win.masterData.NameSpesiality;
				delete win.masterData.IdDoc;
				delete win.masterData.Name;
				delete win.masterData.Date;
				delete win.masterData.IdAppointment;
				delete win.masterData.VisitStart;
				break;
			case 'selectDoctor':
				win.setTitle('Мастер выписки направления ' + Person_Fio + ' | На консультацию в другую МИС > ' + win.masterData.DistrictName + ' > ' + win.masterData.LPUShortName + ' > ' + win.masterData.NameSpesiality + ' > ' + 'Выбор врача');
				win.buttons[0].show(); // назад
				win.buttons[1].show(); // в начало
				win.CardPanel.getLayout().setActiveItem(3);
				win.selectDoctorFilterPanel.loadData();

				delete win.masterData.IdDoc;
				delete win.masterData.Name;
				delete win.masterData.Date;
				delete win.masterData.IdAppointment;
				delete win.masterData.VisitStart;
				break;
			case 'selectDate':
				win.setTitle('Мастер выписки направления ' + Person_Fio + ' | На консультацию в другую МИС > ' + win.masterData.DistrictName + ' > ' + win.masterData.LPUShortName + ' > ' + win.masterData.NameSpesiality + ' > ' + win.masterData.Name + ' > ' + 'Выбор даты');
				win.buttons[0].show(); // назад
				win.buttons[1].show(); // в начало
				win.CardPanel.getLayout().setActiveItem(4);
				var params = {
					IdDoc: win.masterData.IdDoc,
					IdLPU: win.masterData.IdLPU
				};
				win.selectDateViewFrame.loadData({
					globalFilters: params
				});

				delete win.masterData.Date;
				delete win.masterData.IdAppointment;
				delete win.masterData.VisitStart;
				break;
			case 'selectAppointment':
				win.setTitle('Мастер выписки направления ' + Person_Fio + ' | На консультацию в другую МИС > ' + win.masterData.DistrictName + ' > ' + win.masterData.LPUShortName + ' > ' + win.masterData.NameSpesiality + ' > ' + win.masterData.Name + ' > ' + win.masterData.Date + ' > ' + 'Выбор времени');
				win.buttons[0].show(); // назад
				win.buttons[1].show(); // в начало
				win.CardPanel.getLayout().setActiveItem(5);
				var params = {
					IdDoc: win.masterData.IdDoc,
					IdLPU: win.masterData.IdLPU,
					Date: win.masterData.Date
				};
				win.selectAppointmentViewFrame.loadData({
					globalFilters: params
				});

				delete win.masterData.IdAppointment;
				delete win.masterData.VisitStart;
				break;
		}

		// win.CardPanel.doLayout();
	},
	setAppointment: function() {
		var win = this;
		var Person_Fio = (this.personData && this.personData.Person_Fio)?this.personData.Person_Fio:'';

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					win.getLoadMask('Запись пациента к врачу').show();
					Ext.Ajax.request({
						url: '/?c=MisRB&m=setAppointment',
						params: {
							Person_id: win.personData.Person_id,
							idAppointment: win.masterData.IdAppointment,
							idLpu: win.masterData.IdLPU
						},
						callback: function(options, success, response)  {
							win.getLoadMask().hide();
							if (success) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (result.success) {
									sw.swMsg.alert('Сообщение', 'Пациент успешно записан на прием', function () {
										win.hide();
									});
								} else {
									if (result.Error_Msg && result.Error_Msg == 'Полис пациента не найден') {
										sw.swMsg.alert('Ошибка', 'Запись пациента невозможна, в МО записи отсутствует информация о полисе пациента. Для записи на прием следует обратиться в МО - ' + win.masterData.LPUShortName);
									}
								}
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Вы действительно хотите записать ' + Person_Fio + ' в ' + win.masterData.LPUShortName + ' к ' + win.masterData.NameSpesiality + ' на ' + win.masterData.Date + ' ' + win.masterData.VisitStart + '?',
			title: 'Вопрос'
		});
	},
	setWaiting: function() {
		var win = this;
		var Person_Fio = (this.personData && this.personData.Person_Fio)?this.personData.Person_Fio:'';

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					win.getLoadMask('Постановка пациента в очередь').show();
					Ext.Ajax.request({
						url: '/?c=MisRB&m=setWaitingList',
						params: {
							Person_id: win.personData.Person_id,
							idDoc: win.masterData.IdDoc,
							nameDoc: win.masterData.Name,
							idSpesiality: win.masterData.IdSpesiality,
							nameSpesiality: win.masterData.NameSpesiality,
							idLpu: win.masterData.IdLPU
						},
						callback: function(options, success, response)  {
							win.getLoadMask().hide();
							if (success) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (result.success) {
									sw.swMsg.alert('Сообщение', 'Пациент добавлен в очередь', function () {
										win.hide();
									});
								}
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Поставить пациента ' + Person_Fio + ' в очередь по специальности ' + win.masterData.NameSpesiality + (win.masterData.Name ? ' к врачу ' + win.masterData.Name : '') + '?',
			title: 'Вопрос'
		});
	},
	show: function() {
		sw.Promed.swDirectionMasterMisRbWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.personData = null;
		if ( arguments && arguments[0] && typeof arguments[0].personData == 'object' && arguments[0].personData.Person_id ) {
			this.personData = arguments[0].personData;
		} else {
			sw.swMsg.alert('Сообщение', 'Не переданы параметры открытия формы', function() {
				win.hide();
			});
			return false;
		}

		this.masterData = {};
		win.selectDistrictFilterPanel.getForm().reset();

		this.setStep('selectDistrict')
	}
});