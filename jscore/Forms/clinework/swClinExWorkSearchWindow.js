/**
* Журнал учета клинико-экспертной работы МУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      01.08.2011
*/

sw.Promed.swClinExWorkSearchWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: 'Журнал учета клинико-экспертной работы МУ (форма 035/у-02)',
	maximized: true,
	maximizable: false,
	shim: false,
	mode: 'all',
	buttonAlign: "right",
	objectName: 'swClinExWorkSearchWindow',
	closeAction: 'hide',
	id: 'swClinExWorkSearchWindow',
	objectSrc: '/jscore/Forms/clinework/swClinExWorkSearchWindow.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.doSearch();
			},
			iconCls: 'search16',
			text: BTN_FRMSEARCH
		}, {
			handler: function()
			{
				this.ownerCt.doReset();
			},
			iconCls: 'resetsearch16',
			text: BTN_FRMRESET
		}, {
				iconCls: 'ok16',
				text: 'Выбрать',
				id: 'CEWSW_selectBtn',
				hidden: true,
				handler: function() { this.ownerCt.onOkButtonClick(); }
		},
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : 'Закрыть',
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	listeners:
	{
		'hide': function(w)
		{
			w.mode = 'all';
			w.gridActionsHide();
			w.doReset();
		}
	},
	onOkButtonClick: function()
	{
		var grid = this.findById('ClinExWorkSearchGrid').ViewGridPanel;
		var selected_record = grid.getSelectionModel().getSelected();
		if ( selected_record )
		{
			var data_to_return = {};
			Ext.apply(data_to_return, selected_record.data);
			this.onClinExWorkSelect(data_to_return);
			return;
		}
	},
	show: function()
	{
		sw.Promed.swClinExWorkSearchWindow.superclass.show.apply(this, arguments);
		
		if(arguments[0] && arguments[0].mode == 'view')
			this.mode = arguments[0].mode;
			
		var base_form = this.searchFilterPanel.getForm();

		var daterangefield1 = this.findById('ExpertiseDateRange');
		daterangefield1.setValue(getGlobalOptions().date+' - '+getGlobalOptions().date);
		
		if ( arguments[0] && arguments[0].params ) {
			this.params = arguments[0].params;
				
			var ExpertiseNameType = this.params.ExpertiseNameType || null;
			var ExpertiseEventType = this.params.ExpertiseEventType || null;
			var Person_FirName = this.params.Person_FirName || '';
			var Person_SecName = this.params.Person_SecName || '';
			var Person_SurName = this.params.Person_SurName || '';
			var Person_BirthDay = this.params.Person_BirthDay || '';
			
			this.startDate = Ext.util.Format.date(this.params.startDate, 'd.m.Y') || getGlobalOptions().date;
			this.endDate = Ext.util.Format.date(this.params.endDate, 'd.m.Y') || getGlobalOptions().date;
			
			daterangefield1.setValue(this.startDate+' - '+this.endDate);
			base_form.findField('ExpertiseNameType').setValue(ExpertiseNameType);
			base_form.findField('ExpertiseEventType').setValue(ExpertiseEventType);
			base_form.findField('Person_FirName').setValue(Person_FirName);
			base_form.findField('Person_SecName').setValue(Person_SecName);
			base_form.findField('Person_SurName').setValue(Person_SurName);
			base_form.findField('Person_BirthDay').setValue(Person_BirthDay);
		}
		
		if ( arguments[0] && arguments[0].onSelect ) {
			Ext.getCmp('CEWSW_selectBtn').show();		
			this.onClinExWorkSelect = arguments[0].onSelect;
			this.doSearch();
		} else {
			Ext.getCmp('CEWSW_selectBtn').hide();
			this.onClinExWorkSelect = Ext.emptyFn;
		}

		this.userMedStaffFact = null;
		if (arguments[0] && arguments[0].userMedStaffFact) {
			log(arguments[0].userMedStaffFact);
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		
		/*
		var lpusection_combo = this.findById('lpusection_combo');
		var medstafffact_combo = this.findById('medstafffact_combo');
		
		setLpuSectionGlobalStoreFilter({
			isStacAndPolka: true
		});
		lpusection_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		
		setMedStaffFactGlobalStoreFilter({
			isStacAndPolka: true
		});		
		medstafffact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		*/
		
		base_form.findField('EvnStatus_id').lastQuery = '';
		base_form.findField('EvnStatus_id').getStore().filterBy(function(rec) {
			return rec.get('EvnStatus_id').inlist([27,28,29,30,31]);
		});
		
		var medservice_combo = base_form.findField('MedService_id');
		// Загружаем доступные службы ВК
		medservice_combo.getStore().load({
			callback: function() {
				medservice_combo.getStore().filterBy(function(r) {
					var f_f = false;
					 // Только ВК
					if(parseInt(r.get('MedServiceType_id')) == 1)
						f_f = true;
					return f_f;
				});
				medservice_combo.getStore().loadData(getStoreRecords(medservice_combo.getStore()));
				//this.loadMSMPCombo(null);
			}.createDelegate(this)
		});
		
		//var daterangefield2 = this.findById('FromMSEdaterange');
		//daterangefield2.setValue(getGlobalOptions().date+' - '+getGlobalOptions().date);
		
		if(this.mode == 'view') {
			this.gridActionsHide();
			//base_form.findField('MedStaffFact_id').setValue(getGlobalOptions().CurMedStaffFact_id);
		}
	},
	
	loadMSMPCombo: function(MedService_id)
	{
		var base_form = this.searchFilterPanel.getForm(),
			msmp_combo = base_form.findField('MedServiceMedPersonal_id');
		
		msmp_combo.getStore().baseParams.MedService_id = MedService_id;
		
		msmp_combo.getStore().baseParams.MedServiceType_id = 1; // Только ВК
		msmp_combo.getStore().load({
			callback: function() {
				msmp_combo.getStore().each(function(rec) {
					if(rec.get('MedPersonal_id') == getGlobalOptions().medpersonal_id )
						msmp_combo.setValue(rec.get('MedServiceMedPersonal_id'));
				});
			}
		});
	},
	
	gridActionsHide: function()
	{
		var o = (this.mode == 'view') ? true : false;
		var grid_actions = this.searchResultsGrid.ViewActions;
		grid_actions.action_add.setHidden(o);
		grid_actions.action_delete.setHidden(o);
		grid_actions.action_edit.setHidden(o);
	},
	
	doSearch: function()
	{
		if(!this.searchFilterPanel.getForm().isValid())
		{
			sw.swMsg.alert('Ошибка', 'Проверьте правильность заполнения формы!');
			return false;
		}
		params = this.searchFilterPanel.getForm().getValues();
		
		params.EvnVK_isUseStandard = this.searchFilterPanel.getForm().findField('EvnVK_isUseStandard').getValue();
		params.EvnVK_isAberration = this.searchFilterPanel.getForm().findField('EvnVK_isAberration').getValue();
		params.EvnVK_isErrors = this.searchFilterPanel.getForm().findField('EvnVK_isErrors').getValue();
		params.EvnVK_isResult = this.searchFilterPanel.getForm().findField('EvnVK_isResult').getValue();
		params.Lpu_id = getGlobalOptions().lpu_id;
		
		params.start = 0;
		params.limit = 50;
		
		var grid = this.searchResultsGrid.ViewGridPanel;
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params,
			callback: function(r) {
				if ( r.length > 0 ) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}
		});
	},
	
	doReset: function()
	{
		this.searchFilterPanel.getForm().reset();
		this.searchResultsGrid.ViewGridPanel.getStore().removeAll();
	},
	
	openClinExWorkEditWindow: function(showtype)
	{
		var base_form = this.searchFilterPanel.getForm();

		args = {};
		args.showtype = showtype;
		args.onHide = function() {
			this.doSearch();
		}.createDelegate(this);
		switch(showtype) {
			case 'add':
				if ( Ext.isEmpty(base_form.findField('MedService_id').getValue()) ) {
					sw.swMsg.alert('Сообщение', 'Не указана служба!', function() { base_form.findField('MedService_id').focus(true); });
					return false;
				}

				args.MedService_id = base_form.findField('MedService_id').getValue();

				getWnd('swPersonSearchWindow').show({
					onSelect: function(personData) {
						args.PersonData = personData;
						getWnd('swPersonSearchWindow').hide();
					},
					onClose: function() {
						if(args.PersonData && !getWnd('swClinExWorkEditWindow').isVisible()) {
							var lm = new Ext.LoadMask(Ext.get('swClinExWorkSearchWindow'), {msg: 'Загрузка данных...'});
							lm.show();
							Ext.Ajax.request({
								url: '/?c=ClinExWork&m=getNewEvnVKNumber',
								method: 'POST',
								callback: function(options, success, response) {
									lm.hide();
									if(success) {
										var result = Ext.util.JSON.decode(response.responseText);
										args.EvnVK_NumProtocol = parseInt(result[0].EvnVK_NumProtocol) + 1;
										getWnd('swClinExWorkEditWindow').show(args);
									} else {
										sw.swMsg.alert('Ошибка', 'Не удалось определить номер нового протокола ВК!');
									}
								}
							});
						}
					}
				});
				
			break;
			
			case 'edit':
			case 'view':
				var grid = this.findById('ClinExWorkSearchGrid').ViewGridPanel;
				args.EvnVK_id = grid.getSelectionModel().getSelected().get('EvnVK_id');
				getWnd('swClinExWorkEditWindow').show(args);
			break;
		}
	},
	deleteEvnVK: function()
	{
		win = this;
		var grid = this.findById('ClinExWorkSearchGrid').ViewGridPanel;
		var EvnVK_id = grid.getSelectionModel().getSelected().get('EvnVK_id');
		if(!EvnVK_id)
			return false;
		
		var lm = this.getLoadMask('Удаление протокола...');
		
		Ext.Msg.show({
			title: 'Внимание!',
			msg: 'Вы действительно хотите удалить выбранный протокол ВК?',
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					lm.show();
					Ext.Ajax.request({
						url: '/?c=ClinExWork&m=deleteEvnVK',
						params: {EvnVK_id: EvnVK_id},
						callback: function(options, success, response) {
							lm.hide();
							if (success) {
								win.doSearch();
							} else {
								sw.swMsg.alert('Ошибка', 'Ошибка при удалении протокола!');
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},
	printEvnVK_all: function()
	{
		params = this.searchFilterPanel.getForm().getValues();
		params.EvnVK_isUseStandard = this.searchFilterPanel.getForm().findField('EvnVK_isUseStandard').getValue();
		params.EvnVK_isAberration = this.searchFilterPanel.getForm().findField('EvnVK_isAberration').getValue();
		params.EvnVK_isErrors = this.searchFilterPanel.getForm().findField('EvnVK_isErrors').getValue();
		params.EvnVK_isResult = this.searchFilterPanel.getForm().findField('EvnVK_isResult').getValue();
        params.print_list = 1;
		Ext.Ajax.request({
			url: '/?c=ClinExWork&m=printEvnVK_all',
			params: params,
			callback: function(options, success, response) {
				if (success) {
					openNewWindow(response.responseText);
				}
			}
		});
        params.print_list = 2;
        Ext.Ajax.request({
            url: '/?c=ClinExWork&m=printEvnVK_all',
            params: params,
            callback: function(options, success, response) {
                if (success) {
                    openNewWindow(response.responseText);
                }
            }
        });
	},
	exportEvnVK_all: function()
	{
		var base_form = this.searchFilterPanel.getForm();
		params = this.searchFilterPanel.getForm().getValues();
		params.EvnVK_isUseStandard = this.searchFilterPanel.getForm().findField('EvnVK_isUseStandard').getValue();
		params.EvnVK_isAberration = this.searchFilterPanel.getForm().findField('EvnVK_isAberration').getValue();
		params.EvnVK_isErrors = this.searchFilterPanel.getForm().findField('EvnVK_isErrors').getValue();
		params.EvnVK_isResult = this.searchFilterPanel.getForm().findField('EvnVK_isResult').getValue();
        params.print_list = 1;

        base_form.getEl().dom.action = "/?c=ClinExWork&m=exportEvnVK_all";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;
		var baseParams = params;
		base_form.submit();
	},
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.frmlisteners = {
			'keydown': function (inp, e) {
				if (inp.isValid() && e.getKey() == Ext.EventObject.ENTER) {
					e.stopEvent();
					cur_win.doSearch();
				}
			}.createDelegate(this)
		};
		
		this.searchFilterPanel = new Ext.form.FormPanel({
			title: 'Журнал протоколов ВК: поиск',
			autoHeight: true,
			animCollapse: false,
			floatable: false,
			region: 'north',
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			collapsible: true,
			titleCollapse: true,
			items: [
				{
					layout: 'column',
					style: 'margin: 5px;',
					border: false,
					items: [
						{
							layout: 'form',
							labelAlign: 'right',
							labelWidth: 180,
							width: 400,
							border: false,
							items: [
								{
									xtype: 'daterangefield',
									id: 'ExpertiseDateRange',
									width: 170,
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
									listeners: {
										'keydown': function (inp, e) {
											var form = Ext.getCmp('swClinExWorkSearchWindow');
											if (inp.isValid() && e.getKey() == Ext.EventObject.ENTER) {
												e.stopEvent();
												form.doSearch();
											}
										}.createDelegate(this)
									},
									fieldLabel: 'Даты экспертиз от - до'
								}, /*{
									xtype: 'swlpusectionglobalcombo',
									id: 'lpusection_combo',
									anchor: '100%',
									editable: false,
									listWidth: 400,
									hiddenName: 'LpuSection_id',
									linkedElements: [
										'medstafffact_combo'
									],
									fieldLabel: 'Отделение',
									listeners:
									{
										select: function(combo, record, index)
										{
											var LpuSection_id = record.get('LpuSection_id');
											medstafffact_combo = this.ownerCt.findById('medstafffact_combo');
											medstafffact_combo.reset();
											if(LpuSection_id != '')
											{
												setMedStaffFactGlobalStoreFilter({
													LpuSection_id: LpuSection_id,
													isStacAndPolka: true
												});
											}
											else
											{
												setMedStaffFactGlobalStoreFilter({
													isStacAndPolka: true
												});
											}
											medstafffact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
										},
										'keydown': function (inp, e) 
										{
											var form = Ext.getCmp('swClinExWorkSearchWindow');
											if (e.getKey() == Ext.EventObject.ENTER)
											{
												e.stopEvent();
												form.doSearch();
											}
										}.createDelegate(this)
									}
								}, {
									xtype: 'swmedstafffactglobalcombo',
									anchor: '100%',
									editable: false,
									listWidth: 600,
									listeners: this.frmlisteners,
									parentElementId: 'lpusection_combo',
									hiddenName: 'MedStaffFact_id',
									id: 'medstafffact_combo',
									fieldLabel: 'Врач'
								},*/{
									xtype: 'swmedserviceglobalcombo',
									anchor: '100%',
									listeners: {
										select: function(c, r, i) {
											//this.loadMSMPCombo(r.get('MedService_id'));
										}.createDelegate(this)
									},
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<tpl if="this.isClosed(values)"><div><span style="color:grey;">{MedService_Name}&nbsp;</span></div></tpl>',
										'<tpl if="!this.isClosed(values)"><div><h3>{MedService_Name}&nbsp;</h3></div></tpl>',
										'</div></tpl>',
										{
											isClosed: function(values) {
												return (values.MedService_IsClosed == 1);
											}
										}
									),
									fieldLabel: 'Служба ВК'
								}, /*{
									xtype: 'swmedservicemedpersonalcombo',
									fieldLabel: 'Врач службы ВК',
									listWidth: 300,
									anchor: '100%'
								},*/ {
									xtype: 'swexpertisenametypecombo',
									id: 'ExpertiseNameType',
									editable: true,
									linkedElements: [
										'ExpertiseEventType'
									],
									fieldLabel: 'Вид экспертизы',
									listWidth: 650,
									listeners:
									{
										select: function(combo, record, index)
										{
											expertiseeventtype_combo = this.ownerCt.findById('ExpertiseEventType');
											expertiseeventtype_combo.reset();
											store = this.ownerCt.ownerCt.ownerCt.ownerCt.ExpertiseEventTypeStore;
											store.clearFilter();
											store.filterBy(function(rec){
												var filter_flag = true;
												if(rec.get('ExpertiseEventType_Code') != record.id)
												{
													filter_flag = false;
												}
												return filter_flag;
											});
											expertiseeventtype_combo.getStore().loadData(getStoreRecords(store));
										},
										'keydown': function (inp, e) 
										{
											var form = Ext.getCmp('swClinExWorkSearchWindow');
											if (e.getKey() == Ext.EventObject.ENTER)
											{
												e.stopEvent();
												form.doSearch();
											}
										}.createDelegate(this)
									}
								}, {
									xtype: 'swexpertiseeventtypecombo',
									parentElementId: 'ExpertiseNameType',
									id: 'ExpertiseEventType',
									listeners: this.frmlisteners,
									listWidth: 550,
									editable: true,
									fieldLabel: 'Случай экспертизы'
								}, {
									xtype: 'textfieldpmw',
									name: 'Person_SurName',
									listeners: this.frmlisteners,
									anchor: '100%',
									fieldLabel: 'Фамилия пациента'
								}, {
									xtype: 'textfieldpmw',
									anchor: '100%',
									listeners: this.frmlisteners,
									name: 'Person_FirName',
									fieldLabel: 'Имя пациента'
								}, {
									xtype: 'textfieldpmw',
									anchor: '100%',
									name: 'Person_SecName',
									listeners: this.frmlisteners,
									fieldLabel: 'Отчество пациента'
								}, {
									xtype: 'swdatefield',
									name: 'Person_BirthDay',
									listeners: this.frmlisteners,
									fieldLabel: 'Дата рождения'
								}
							]
						}, {
							layout: 'form',
							labelAlign: 'right',
							labelWidth: 250,
							width: 500,
							style: 'margin-left: 50px;',
							border: false,
							items: [
								{
									xtype: 'swpatientstatustypecombo',
									editable: true,
									listeners: this.frmlisteners,
									id: 'patientstatustype_combo',
									listWidth: 500,
									fieldLabel: 'Статус пациента'
								}, {
									xtype: 'swdiagcombo',
									anchor: '100%',
									//editable: false,
									fieldLabel: 'Диагноз'
								}, {
									xtype: 'daterangefield',
									id: 'FromMSEdaterange',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
									listeners: this.frmlisteners,
									name: 'EvnVK_DirectionDate',
									width: 170,
									fieldLabel: 'Даты направлений на МСЭ: от - до'
								}, {
									xtype: 'daterangefield',
									id: 'ToMSEdaterange',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
									width: 170,
									listeners: this.frmlisteners,
									name: 'EvnVK_ConclusionDate',
									fieldLabel: 'Даты выписки обратного талона: от - до'
								}, {
									xtype: 'swevnstatuscombo',
									hiddenName: 'EvnStatus_id',
									fieldLabel: 'Статус направления на МСЭ',
									anchor: '100%'
								}, {
									xtype: 'swyesnocombo',
									anchor: '100%',
									listeners: this.frmlisteners,
									hiddenName: 'EvnVK_isUseStandard',
									fieldLabel: 'Использовались стандарты',
									hidden: (getRegionNick()=='kz'),
									hideLabel: (getRegionNick()=='kz')
								},
								{
									xtype: 'swyesnocombo',
									anchor: '100%',
									listeners: this.frmlisteners,
									hiddenName: 'EvnVK_isAberration',
									fieldLabel: 'Отклонения от стандартов'
								}, {
									xtype: 'swyesnocombo',
									anchor: '100%',
									listeners: this.frmlisteners,
									hiddenName: 'EvnVK_isErrors',
									fieldLabel: 'Дефекты'
								}, {
									xtype: 'swyesnocombo',
									anchor: '100%',
									listeners: this.frmlisteners,
									hiddenName: 'EvnVK_isResult',
									fieldLabel: 'Достижение результата',
									hidden: (getRegionNick()!='kz'),
									hideLabel: (getRegionNick()!='kz')
								}, {
									xtype: 'checkbox',
									//style: 'margin-right: 0px;',
									boxLabel: false,
									//anchor: '100%',
									name: 'EvnVK_isControl',
									fieldLabel: 'На контроле'
								}, {
									xtype: 'combo',
									mode: 'local',
									anchor: '100%',
									hiddenName: 'isSigned',
									displayField: 'isSigned_Text',
									valueField: 'isSigned_id',
									hidden: getRegionNick() != 'vologda',
									hideLabel: getRegionNick() != 'vologda',
									fieldLabel: 'Статус подписания протокола',
									store: new Ext.data.SimpleStore({
										autoLoad: true,
										fields: [
											{name:'isSigned_id', type:'int'},
											{name:'isSigned_Text', type:'string'}
										],
										data: [[1, 'Все'], [2, 'Полностью подписан'], [3, 'Требует подписания']]
									}),
									listeners: {
										render: function(combo){
											combo.setValue(1);
										}
									},
									triggerAction: 'all',
									editable: false
								}
							]
						}
					]
				}
			]
		});
		
		this.searchResultsGrid = new sw.Promed.ViewFrame({
			id: 'ClinExWorkSearchGrid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			anchor: '100%',
			region: 'center',
			pageSize: 20,
			border: false,
			actions: [
				{ name: 'action_add', tooltip: 'Добавить новый протокол ВК', hidden: false, handler: function(){this.openClinExWorkEditWindow('add');}.createDelegate(this) },
				{ name: 'action_edit', tooltip: 'Изменить протокол ВК', hidden: false, handler: function(){this.openClinExWorkEditWindow('edit');}.createDelegate(this) },
				{ name: 'action_view', tooltip: 'Открыть протокол ВК', hidden: false, handler: function(){this.openClinExWorkEditWindow('view');}.createDelegate(this) },
				{ name: 'action_delete', disabled: true, tooltip: 'Удалить протокол ВК', hidden: false, handler: function(){this.deleteEvnVK();}.createDelegate(this) },
				{ name:	'vk_del', disabled: true, text:'Удалить протокол ВК', tooltip: 'Удалить протокол ВК', iconCls : 'x-btn-text', icon: 'img/icons/delete16.png', handler: function() {this.vkDelete()}.createDelegate(this)},
				{ name: 'action_refresh' },
                { name: 'action_print',
                    menuConfig: {
                        printObjectEvnVK: { text: 'Печать журнала ВК', handler: function() { this.printEvnVK_all(); }.createDelegate(this) },
                        exportObjectEvnVK: { text: 'Экспорт журнала ВК', handler: function() { this.exportEvnVK_all(); }.createDelegate(this) }
                    }
                }
			],
			autoLoadData: false,
			stripeRows: true,
			root: 'data',
			stringfields: [
				{ name: 'EvnVK_id', type: 'int', hidden: true, key: true },
				{ name: 'EvnPrescrMse_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'MedService_id', type: 'int', hidden: true },
				{ name: 'EvnVK_SignCount', type: 'int', hidden: true },
				{ name: 'EvnVK_MinSignCount', type: 'int', hidden: true },
				{ name: 'EvnVK_signDT', type: 'date', hidden: true },
				{ name: 'num', type: 'string',  header: 'Номер п/п', width: 40 },
				{ name: 'EvnVK_ExpertiseDate', type: 'date', header: 'Дата экспертизы', width: 70, renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				//{ name: 'LpuSection_FullName', type: 'string', header: 'Отделение', width: 140 },
				//{ name: 'MSFPerson_FIO', type: 'string', header: 'Врач', width: 200 },
				{ name: 'MedService_Name', type: 'string', header: 'Служба ВК', width: 200 },
				{ name: 'EvnVK_IsSigned', type: 'string', header: 'Статус подписания', width: 100, hidden: getRegionNick() != 'vologda', renderer: function(v, p, r) {
					if (!r || !r.get('EvnVK_id')) return '';
					var s = '';
					if (!Ext.isEmpty(r.get('EvnVK_IsSigned'))) {
						if (r.get('EvnVK_IsSigned') == 2) {
							s += '<img src="/img/icons/emd/doc_signed.png">';
						} else {
							s += '<img src="/img/icons/emd/doc_notactual.png">';
						}

						s += Ext.util.Format.date(r.get('EvnVK_signDT'), 'd.m.Y');
					} else if (r.get('EvnVK_SignCount') > 0) {
						s += '<span class="sp_doc_unsigned" data-qtip="' + r.get('EvnVK_SignCount') + ' из ' + r.get('EvnVK_MinSignCount') + '">' + r.get('EvnVK_SignCount') + '</span>';
					} else {
						s += '<img src="/img/icons/emd/doc_notsigned.png">';
					}
					return s;
				}},
				{ name: 'Person_Fio', type: 'string', header: 'ФИО пациента', width: 200 },
				{ name: 'Person_BirthDay', type: 'date', width: 70, header: 'Дата рождения', renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'Diag_Name', id: 'autoexpand', type: 'string', header: 'Диагноз', width: 150 },
				{ name: 'ExpertiseEventType_Code', type: 'int', hidden: true},
				{ name: 'ExpertiseEventType_Name', type: 'string', header: 'Характеристика случая экспертизы',  width: 200 },
				{ name: 'ExpertiseNameType_Name', type: 'string', header: 'Вид экспертизы', width: 160},
				//{ name: 'EvnVK_isAberration', type: 'checkbox', header: 'Отклонения от стандартов', width: 150 },
				//{ name: 'EvnVK_isErrors', type: 'checkbox', header: 'Дефекты', width: 60 },
				//{ name: 'EvnVK_isResult', type: 'checkbox', header: 'Достижение результата', width: 150 },
				{ name: 'EvnVK_DirectionDate', header: 'Направление на МСЭ', renderer: function(v, p, r){
					if(v != null && v != '')
						v = '<a href="javascript:">'+v+'</a>';
					else
						v = '';
					return v;
				}, width: 120 },
				{ name: 'EvnStatus_Name', header: "Статус направления на МСЭ", width: 140 }, 
				{ header: "Дата и время назначения МСЭ", name: 'EvnMse_setDT', width: 140 },
				{ name: 'EvnVK_ConclusionDate', header: 'Обратный талон', renderer: function(v, p, r){
					if(v != null && v != '')
						v = '<a href="javascript:">'+v+'</a>';
					else
						v = '';
					return v;
				}, width: 120 }
				//{ name: 'EvnVK_isControl', type: 'checkbox', width: 100, header: 'На контроле' },
				//{ name: 'EvnVK_isReserve', type: 'checkbox', header: 'Зарезервировано', width: 120 }
			],
			paging: true,
			pageSize: 50,
			dataUrl: '/?c=ClinExWork&m=searchData',
			totalProperty: 'totalCount'
		});
		
		this.searchResultsGrid.ViewGridPanel.on('cellclick', function(grid, rowIdx, colIdx) {
			var flag_idx = grid.getColumnModel().findColumnIndex('EvnVK_DirectionDate'),
				flag2_idx = grid.getColumnModel().findColumnIndex('EvnVK_ConclusionDate'),
				rec = grid.getSelectionModel().getSelected();
			if(colIdx == flag_idx){
				if(!rec || rec.get('EvnPrescrMse_id') == '') return false;
				getWnd('swDirectionOnMseEditForm').show({
					action: 'view',
					Person_id: rec.get('Person_id'),
					Server_id: rec.get('Server_id'),
					EvnVK_id: rec.get('EvnVK_id')
				});
			}
			if(colIdx == flag2_idx){
				if(rec.get('EvnVK_ConclusionDate') != null && rec.get('EvnVK_ConclusionDate') != ''){
					getWnd('swProtocolMseEditForm').show({
						action: 'view',
						Person_id: rec.get('Person_id'),
						Server_id: rec.get('Server_id'),
						EvnPrescrMse_id: rec.get('EvnPrescrMse_id')
					});
				}
			}
		}.createDelegate(this));
		
		this.searchResultsGrid.ViewGridPanel.getSelectionModel().on('rowselect', function(grid, rowIdx, record){
			if( cur_win.mode == 'view' ) {
				cur_win.searchResultsGrid.ViewActions.action_edit.disable();
				cur_win.searchResultsGrid.ViewActions.action_delete.disable();
				return false;
			}

			var userMedStaffFact = cur_win.userMedStaffFact;
			if (//userMedStaffFact && userMedStaffFact.MedService_id && userMedStaffFact.MedService_id == record.get('MedService_id') &&
				Ext.isEmpty(record.get('EvnPrescrMse_id'))) {
				cur_win.searchResultsGrid.getAction('action_edit').enable();
			} else {
				cur_win.searchResultsGrid.getAction('action_edit').disable();
			}

			if(Ext.isEmpty(record.get('EvnPrescrMse_id')) && (record.get('ExpertiseEventType_Code') != 14)){
				cur_win.searchResultsGrid.getAction('action_delete').enable();
			}
			else{
				cur_win.searchResultsGrid.getAction('action_delete').disable();
			}
		});
		
		this.searchResultsPanel = new Ext.Panel({
			title: 'Результаты поиска',
			//autoHeight: true,
			region: 'center',
			layout: 'border',
			border: false,
			collapsible: false,
			titleCollapse: false,
			items: [this.searchResultsGrid]
		});
	
		this.ExpertiseEventTypeStore = new Ext.db.AdapterStore({
			dbFile: 'Promed.db',
			tableName: 'ExpertiseEventType',
			key: 'ExpertiseEventType_id',
			autoLoad: true,
			fields: [
				{name: 'ExpertiseEventType_id',  type:'int'},
				{name: 'ExpertiseEventType_Code',  type:'string'},
				{name: 'ExpertiseEventType_Name',  type:'string'}
			],
			sortInfo: {
				field: 'ExpertiseEventType_id'
			}
		});
	
		Ext.apply(this,
		{
			xtype: 'panel',
			layout: 'border',
			items: [
				this.searchFilterPanel,
				this.searchResultsPanel
			]
		});
		sw.Promed.swClinExWorkSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});