/**
* swGeriatricsRegistryWindow - окно регистра по гериатрии
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @package      PersonRegister
* @author       Быков Станислав
* @version      12.2018
* @comment      Префикс для id компонентов GRW (GeriatricsRegistryWindow)
*
*/
sw.Promed.swGeriatricsRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	height: 550,
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('GeriatricsRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('GeriatricsRegistryFilterForm').doLayout();
		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('GRW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('GeriatricsRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	tabindexFirst: TABINDEX_GRW,
	title: langs('Регистр по гериатрии'),
	width: 800,

	/* методы */
	doReset: function() {
		var base_form = this.findById('GeriatricsRegistryFilterForm').getForm();
		base_form.reset();

		this.GeriatricsRegistrySearchFrame.ViewActions.open_emk.setDisabled(true);
		this.GeriatricsRegistrySearchFrame.ViewActions.action_view.setDisabled(true);
		this.GeriatricsRegistrySearchFrame.ViewActions.action_delete.setDisabled(true);
		this.GeriatricsRegistrySearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.GeriatricsRegistrySearchFrame.removeAll({
			clearAll: true
		});
	},
	doSearch: function(params) {
		if ( typeof params != 'object' ) {
			params = {};
		}
		
		var base_form = this.findById('GeriatricsRegistryFilterForm').getForm();
		
		if ( !params.firstLoad && this.findById('GeriatricsRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено ни одно поле'), function() {});
			return false;
		}
		
		var grid = this.GeriatricsRegistrySearchFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (!params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Выбран тип поиска человека ') + (base_form.findField('PersonPeriodicType_id').getValue() == 2 ? langs('по состоянию на момент случая') : langs('По всем периодикам')) + langs('.<br />При выбранном варианте поиск работает <b>значительно</b> медленнее.<br />Хотите продолжить поиск?'),
				title: langs('Предупреждение')
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('GeriatricsRegistryFilterForm'));

		post.limit = 100;
		post.start = 0;
		
		if ( base_form.isValid() ) {
			this.GeriatricsRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	emkOpen: function(readOnly) {
		var grid = this.GeriatricsRegistrySearchFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			readOnly: readOnly || (this.editType == 'onlyRegister')?true:false,
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	getButtonSearch: function() {
		return Ext.getCmp('GRW_SearchButton');
	},
	getRecordsCount: function() {
		var st = this.GeriatricsRegistrySearchFrame.getGrid().getStore();
		var noLines = false;
		if(st.totalLength == 0){
			noLines = true;
		}else if(st.totalLength == 1){
			if(typeof(st.getAt(0)) == 'undefined'){// бывает после нажатия "Обновить"
				noLines = true;
			}else if(! st.getAt(0).get('PersonRegister_id')){// если запись пустая
				noLines = true;
			}
		}
		if(noLines){
			sw.swMsg.alert('Подсчет записей', 'Найдено записей: 0');
			return;
		}

		var base_form = this.getFilterForm().getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(langs('Поиск'), langs('Проверьте правильность заполнения полей на форме поиска'));
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет подсчет записей..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.getFilterForm());

		if ( post.PersonPeriodicType_id == null ) {
			post.PersonPeriodicType_id = 1;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(langs('Подсчет записей'), langs('Найдено записей: ') + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(langs('Подсчет записей'), response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При подсчете количества записей произошли ошибки'));
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	openWindow: function(action) {
		if ( !action || !action.toString().inlist(['out', 'include', 'view', 'edit']) ) {
			return false;
		}

		var win = this;
		var form = this.getFilterForm().getForm();
		var grid = this.GeriatricsRegistrySearchFrame.getGrid();
		var params = {};

		if ( !grid.getSelectionModel().getSelected() && action != 'include' ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		params.userMedStaffFact = this.userMedStaffFact;
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		switch(action) {
			case 'include':
				if ( isSuperAdmin() || isUserGroup('GeriatryRegistry') || isUserGroup('GeriatryRegistryFullAccess') ) {
					sw.Promed.personRegister.add({
						 PersonRegisterType_SysNick: 'geriatrics'
						,MorbusType_SysNick: 'geriatrics'
						,registryType: 'geriatrics'
						,searchMode: 'geriatrics'
						,viewOnly: (win.editType == 'onlyRegister')
						,callback: function(data) {
							form.findField('Person_Firname').setValue(data.Person_Firname);
							form.findField('Person_Secname').setValue(data.Person_Secname);
							form.findField('Person_Surname').setValue(data.Person_Surname);
							form.findField('Person_Birthday').setValue(data.Person_Birthday);
							win.doSearch();
						}
					});
				}
				break;

			case 'out':
				if ( isSuperAdmin() || isUserGroup('GeriatryRegistryFullAccess') ) {
					sw.Promed.personRegister.out({
						 PersonRegister_id: selected_record.get('PersonRegister_id')
						,MorbusType_SysNick: 'geriatrics'
						,PersonRegisterType_SysNick: 'geriatrics'
						,Person_id: selected_record.get('Person_id')
						,Diag_Name: selected_record.get('Diag_Name')
						,PersonRegister_setDate: selected_record.get('PersonRegister_setDate')
						,callback: function(data) {
							grid.getStore().reload();
						}
					});
				}
				break;

			case 'edit':
			case 'view':
				if ( getWnd('swMorbusGeriatricsEditWindow').isVisible() ) {
					getWnd('swMorbusGeriatricsEditWindow').hide();
				}

				if ( Ext.isEmpty(selected_record.get('MorbusGeriatrics_id')) ) {
					sw.swMsg.alert(langs('Сообщение'), langs('Ошибка выбора записи!'));
					return false;
				}

				if ( !isSuperAdmin() && !isUserGroup('GeriatryRegistry') && !isUserGroup('GeriatryRegistryFullAccess') ) {
					params.action = 'view';
				}

                params.MorbusGeriatrics_id = selected_record.get('MorbusGeriatrics_id');
				params.Person_id = selected_record.get('Person_id');

				getWnd('swMorbusGeriatricsEditWindow').show(params);
				break;
		}
	},
	show: function() {
		sw.Promed.swGeriatricsRegistryWindow.superclass.show.apply(this, arguments);

		this.GeriatricsRegistrySearchFrame.addActions({
			name: 'open_emk', 
			text: langs('Открыть ЭМК'), 
			tooltip: langs('Открыть электронную медицинскую карту пациента'),
			iconCls: 'open16',
			hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible() || getWnd('swWorkPlaceAdminLLOWindow').isVisible(),
			handler: function() {
				this.emkOpen(!isUserGroup('GeriatryRegistryFullAccess') && !isSuperAdmin());
			}.createDelegate(this)
		});
		
		var base_form = this.findById('GeriatricsRegistryFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		this.editType = 'all';

		if ( arguments[0] && arguments[0].editType ) {
			this.editType = arguments[0].editType;
		}

		if ( arguments[0].userMedStaffFact ) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		else {
			if ( sw.Promed.MedStaffFactByUser.last ) {
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else {
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		
		if ( getRegionNick() != 'kareliya' ) {
			base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		}
		
		if ( isUserGroup('GeriatryRegistryFullAccess') || isSuperAdmin() ) {
			base_form.findField('AttachLpu_id').setDisabled(false);
		}
		else {
			base_form.findField('AttachLpu_id').setDisabled(true);
		}

		if ( getWnd('swWorkPlaceMZSpecWindow').isVisible() ) {
			base_form.findField('AttachLpu_id').setValue(null);	
			base_form.findField('AttachLpu_id').setDisabled(false);
		}

		this.ARMType = null;
		
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}

		if ( isUserGroup('GeriatryRegistryFullAccess') || isSuperAdmin() ) {
			this.GeriatricsRegistrySearchFrame.setActionHidden('action_delete', false);
		}
		else {
			this.GeriatricsRegistrySearchFrame.setActionHidden('action_delete', true);
		}

		if ( isUserGroup('GeriatryRegistry') || isUserGroup('GeriatryRegistryFullAccess') || isSuperAdmin() ) {
			this.GeriatricsRegistrySearchFrame.setActionHidden('action_add', false);
			this.GeriatricsRegistrySearchFrame.setActionHidden('action_edit', false);
		}
		else {
			this.GeriatricsRegistrySearchFrame.setActionHidden('action_add', true);
			this.GeriatricsRegistrySearchFrame.setActionHidden('action_edit', true);
		}

		this.doLayout();
		
		base_form.findField('PersonRegisterType_id').setValue(1);
	},

	/* конструктор */
	initComponent: function() {
		var win = this;

		win.GeriatricsRegistrySearchFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: 'GRW_GeriatricsRegistrySearchGrid',
			object: 'GeriatricsRegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'MorbusGeriatrics_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},	
				{name: 'Server_id', type: 'int', hidden: true},			
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'Diag_Name', type: 'string', hidden: true, header: langs('Диагноз'), width: 190},
				{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_Name', type: 'string', hidden: true, header: langs('Причина исключения из регистра'), width: 190},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 150, id: 'autoexpand'},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 90},
				{name: 'SocStatus_Name', type: 'string', header: langs('Соц. статус'), width: 150},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата включения в регистр'), width: 150},
				{name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата исключения из регистра'), width: 150},
				{name: 'AgeNotHindrance_Name', type: 'string', header: langs('Градация пациента по скриннингу'), width: 150},
				{name: 'MorbusGeriatrics_IsKGO', type: 'string', header: langs('Комплексная гериатрическая оценка (КГО)'), width: 150},
				{name: 'MorbusGeriatrics_IsWheelChair', type: 'string', header: langs('Колясочник'), width: 60},
				{name: 'MorbusGeriatrics_IsFallDown', type: 'string', header: langs('Падения'), width: 60},
				{name: 'MorbusGeriatrics_IsWeightDecrease', type: 'string', header: langs('Снижение веса'), width: 60},
				{name: 'MorbusGeriatrics_IsCapacityDecrease', type: 'string', header: langs('Снижение функциональной активности'), width: 60},
				{name: 'MorbusGeriatrics_IsCognitiveDefect', type: 'string', header: langs('Когнитивные нарушения'), width: 60},
				{name: 'MorbusGeriatrics_IsMelancholia', type: 'string', header: langs('Депрессия'), width: 60},
				{name: 'MorbusGeriatrics_IsEnuresis', type: 'string', header: langs('Недержание мочи'), width: 60},
				{name: 'MorbusGeriatrics_IsPolyPragmasy', type: 'string', header: langs('Полипрагмазия'), width: 60},
				{name: 'Lpu_Nick', type: 'string', header: langs('МО прикрепления'), width: 150}
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				win.getButtonSearch().disable();
			},
			onLoadData: function() {
				win.getButtonSearch().enable();
			},
			onRowSelect: function(sm, index, record) {
				this.getAction('open_emk').setDisabled(false);
				this.getAction('action_delete').setDisabled(Ext.isEmpty(record.get('PersonRegister_id')) || (!isUserGroup('GeriatryRegistryFullAccess') && !isSuperAdmin()));
				this.getAction('action_edit').setDisabled(Ext.isEmpty(record.get('MorbusGeriatrics_id')) || !Ext.isEmpty(record.get('PersonRegister_disDate')));
				this.getAction('action_view').setDisabled(Ext.isEmpty(record.get('MorbusGeriatrics_id')));
			},
			onDblClick: function(x,c,v) {
				if (!this.ViewActions.action_edit.isDisabled()) {
					win.openWindow('edit')
				}else{
					win.openWindow('view')
				}
			},
			actions: [
				{name: 'action_add', handler: function() { win.openWindow('include'); }},
				{name: 'action_edit', handler: function() { win.openWindow('edit'); }},
				{name: 'action_view', handler: function() { win.openWindow('view'); }},
				{name: 'action_delete', handler: function() { win.openWindow('out'); }},
				{name: 'action_refresh'},
				{name: 'action_print'}
			]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSearch();
				},
				iconCls: 'search16',
				tabIndex: TABINDEX_GRW + 120,
				id: 'GRW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					win.doReset();
				},
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_GRW + 121,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					win.getRecordsCount();
				},
				tabIndex: TABINDEX_GRW + 123,
				text: BTN_FRMCOUNT
			}, {
				text: '-'
			},
			HelpButton(win, -1),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					win.buttons[win.buttons.length - 2].focus();
				},
				onTabAction: function() {
					win.findById('GRW_SearchFilterTabbar').getActiveTab().fireEvent('activate', win.findById('GRW_SearchFilterTabbar').getActiveTab());
				},
				tabIndex: TABINDEX_GRW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('GeriatricsRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				isDisplayPersonRegisterRecordTypeField: false,
				allowPersonPeriodicSelect: true,
				id: 'GeriatricsRegistryFilterForm',
				labelWidth: 130,
				ownerWindow: win,
				searchFormType: 'GeriatricsRegistry',
				tabIndexBase: TABINDEX_GRW,
				tabPanelHeight: 225,
				tabPanelId: 'GRW_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 220,
					layout: 'form',
					listeners: {
						'activate': function() {
							win.getFilterForm().getForm().findField('PersonRegisterType_id').focus(250, true);
						}
					},
					title: langs('<u>6</u>. Регистр'),
					items: [{
						border: false,
						layout: 'column',
						labelWidth: 220,
						items: [{
							border: false,
							layout: 'form',
							items: [{
								xtype: 'swpersonregistertypecombo',
								hiddenName: 'PersonRegisterType_id',
								width: 180
							}, {
								fieldLabel: langs('Дата включения в регистр'),
								name: 'PersonRegister_setDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 180,
								xtype: 'daterangefield'
							}, {
								fieldLabel: langs('Дата исключения из регистра'),
								name: 'PersonRegister_disDate_Range',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								width: 180,
								xtype: 'daterangefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 300,
							items: [{
								comboSubject: 'AgeNotHindrance',
								fieldLabel: langs('Градация пациента по скринингу'),
								hiddenName: 'AgeNotHindrance_id',
								width: 180,
								xtype: 'swcommonsprcombo'
							}, {
								fieldLabel: langs('Заполнена Карта комплексной гериатрической оценки (КГО)'),
								hiddenName: 'MorbusGeriatrics_IsKGO',
								width: 105,
								xtype: 'swyesnocombo'
							}, {
								fieldLabel: langs('Колясочник'),
								hiddenName: 'MorbusGeriatrics_IsWheelChair',
								width: 105,
								xtype: 'swyesnocombo'
							}]
						}]
					}, {
						autoHeight: true,
						layout: 'form',
						style: 'padding-left: 0px;',
						title: langs('Наличие основных синдромов'),
						width: 900,
						xtype: 'fieldset',
						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								labelWidth: 220,
								layout: 'form',
								items: [{
									fieldLabel: langs('Падения'),
									hiddenName: 'MorbusGeriatrics_IsFallDown',
									xtype: 'swyesnocombo'
								}, {
									fieldLabel: langs('Снижение веса'),
									hiddenName: 'MorbusGeriatrics_IsWeightDecrease',
									xtype: 'swyesnocombo'
								}, {
									fieldLabel: langs('Снижение функциональной активности'),
									hiddenName: 'MorbusGeriatrics_IsCapacityDecrease',
									xtype: 'swyesnocombo'
								}, {
									fieldLabel: langs('Когнитивные нарушения'),
									hiddenName: 'MorbusGeriatrics_IsCognitiveDefect',
									xtype: 'swyesnocombo'
								}]
							}, {
								border: false,
								labelWidth: 150,
								layout: 'form',
								items: [{
									fieldLabel: langs('Депрессия'),
									hiddenName: 'MorbusGeriatrics_IsMelancholia',
									xtype: 'swyesnocombo'
								}, {
									fieldLabel: langs('Недержание мочи'),
									hiddenName: 'MorbusGeriatrics_IsEnuresis',
									xtype: 'swyesnocombo'
								}, {
									fieldLabel: langs('Полипрагмазия'),
									hiddenName: 'MorbusGeriatrics_IsPolyPragmasy',
									xtype: 'swyesnocombo'
								}]
							}]
						}]
					}]
				}]
			}),
			this.GeriatricsRegistrySearchFrame ]
		});

		sw.Promed.swGeriatricsRegistryWindow.superclass.initComponent.apply(this, arguments);
	}
});