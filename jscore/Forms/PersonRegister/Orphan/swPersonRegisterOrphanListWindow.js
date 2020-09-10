/**
 * swPersonRegisterOrphanListWindow - Регистр по орфанным заболеваниям
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      03.2015
 * @comment      Префикс для id компонентов PROLW
 */
sw.Promed.swPersonRegisterOrphanListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	title: lang['registr_po_orfannyim_zabolevaniyam'],
	PersonRegisterType_SysNick: 'orphan',
	MorbusType_SysNick: 'orphan',
	width: 800,
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
			win.findById('OrphanRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('OrphanRegistryFilterForm').doLayout();
		},
		'beforeShow': function(win) {
			if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin')){
				if (false == sw.Promed.personRegister.isAllow(win.PersonRegisterType_SysNick)) {
					return false;
				}
				if (false == sw.Promed.personRegister.isOrphanRegistryOperator()) {
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой "Регистр по орфанным заболеваниям"');
					return false;
				}
			}
			return true;

		},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('PROLW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('OrphanRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,

	fromARM: null,
	editType: 'all',
	ARMType: null,
	userMedStaffFact: null,
	SearchFrame: null,

	// selectedRow = record (при выборе строки)
	selectedRow: null,

	// -----------------------------------------------------------------------------------------------------------------
	// Глобальные блокировки вне зависимости от выбранной строки
	// По умолчанию: не блокируем
	isDisabled_ButtonPersonRegisterDis: false,
	isDisabled_ButtonDelete: false,
	isDisabled_ButtonEdit: false,
	isDisabled_ButtonView: false,
	// -----------------------------------------------------------------------------------------------------------------

	initComponent: function() {
		var me = this;
		this.SearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{
					name: 'action_add',
					handler: function(){
						me.openWindowAdd();
					}
				},
				{
					name: 'action_edit',
					handler: function(){
						me.openWindowEdit();
					}
				},
				{
					name: 'action_view',
					handler: function(){
						me.openWindowView();
					}
				},
				{name: 'action_delete', handler: this.deletePersonRegister.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print', hidden: true, menuConfig: null,
					handler: function()
					{
						me.SearchFrame.printRecords();
					}
				}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			disableActions: false,
			dataUrl: C_SEARCH,
			id: 'PROLW_OrphanRegistrySearchGrid',
			object: 'PersonRegisterBase',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PersonRegister_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnNotifyBase_id', type: 'int', hidden: true},
				{name: 'MedPersonal_iid', type: 'int', hidden: true},
				{name: 'Lpu_iid', type: 'int', hidden: true},
				{name: 'Diag_id', type: 'int', hidden: true},
				{name: 'PersonRegisterType_SysNick', type: 'string', hidden: true},
				{name: 'MorbusType_SysNick', type: 'string', hidden: true},
				{name: 'Morbus_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 150},
				{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150},
				{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Дата рождения'), width: 90},
				{name: 'Lpu_Nick', type: 'string', header: langs('МО прикр.'), width: 150},
				{name: 'Diag_Name', type: 'string', header: langs('Диагноз МКБ-10'), width: 150, id: 'autoexpand'},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата включения в регистр'), width: 150},
				{name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата исключения из регистра'), width: 170},
				{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true},
				{name: 'PersonRegisterOutCause_Name', type: 'string', header: langs('Причина исключения из регистра'), width: 190}
			],
			toolbar: true,
			totalProperty: 'totalCount',
			onBeforeLoadData: function() {
				me.getButtonSearch().disable();
			},
			onLoadData: function() {
				me.getButtonSearch().enable();
			},
			onRowSelect: function(sm, index, record) {
				me.selectedRow = record;

				me.changeAccessToButtons();


				// TODO изменить как другие кнопки и перенести в changeAccessToButtons
				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );

				// TODO изменить как другие кнопки и перенести в changeAccessToButtons
				if(me.fromARM === null || ! me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])){
					this.getAction('open_emk').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );
				} else if(me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])) {
					this.getAction('open_emk').setDisabled( true );
				}


			},
			onEnter: function() {
				var record = this.getGrid().getSelectionModel().getSelected();
				if (record && record.get('PersonRegister_id')) {
					if (Ext.isEmpty(record.get('PersonRegister_disDate')) == false) {
						this.getAction('action_view').execute();
					} else {
						this.getAction('action_edit').execute();
					}
				}
			},
			onDblClick: function() {
				this.onEnter();
			}
		});

		this.SearchFilters = getBaseSearchFiltersFrame({
			allowPersonPeriodicSelect: true,
			id: 'OrphanRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'PersonRegisterBase',
			tabPanelHeight: 235,
			tabPanelId: 'PROLW_SearchFilterTabbar',
			tabs: [{
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				labelWidth: 220,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						var form = me.getFilterForm().getForm();
						form.findField('PersonRegisterType_id').focus(250, true);
					}
				},
				title: lang['6_registr'],
				items: [{
					xtype: 'swpersonregistertypecombo',
					hiddenName: 'PersonRegisterType_id',
					fieldLabel: lang['tip_zapisi_registra'],
					width: 200
				}, {
					fieldLabel: lang['data_vklyucheniya_v_registr'],
					name: 'PersonRegister_setDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield'
				}, {
					fieldLabel: lang['data_isklyucheniya_iz_registra'],
					name: 'PersonRegister_disDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					width: 180,
					xtype: 'daterangefield'
				}]
			}, {
				autoHeight: true,
				bodyStyle: 'margin-top: 5px;',
				border: false,
				layout: 'form',
				labelWidth: 180,
				listeners: {
					'activate': function(panel) {
						me.getFilterForm().getForm().findField('Diag_Code_From').focus(250, true);
					}
				},
				title: lang['7_diagnozyi'],
				items: [{
					fieldLabel: lang['diagnoz_s'],
					hiddenName: 'Diag_Code_From',
					valueField: 'Diag_Code',
					width: 450,
					PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
					MorbusType_SysNick: me.MorbusType_SysNick,
					xtype: 'swdiagcombo'
				},{
					fieldLabel: lang['po'],
					hiddenName: 'Diag_Code_To',
					valueField: 'Diag_Code',
					width: 450,
					PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
					MorbusType_SysNick: me.MorbusType_SysNick,
					xtype: 'swdiagcombo'
				}]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					me.doSearch();
				},
				iconCls: 'search16',
				id: 'PROLW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					me.doReset();
					me.doSearch({firstLoad: true});
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				handler: function() {
					me.SearchFrame.printRecords();
				},
				iconCls: 'print16',
				text: lang['pechat_spiska']
			}, {
				handler: function() {
					me.getRecordsCount();
				},
				hidden: true,
				// iconCls: 'resetsearch16',
				text: BTN_FRMCOUNT
			}, {
				text: '-'
			},
				HelpButton(this, -1),
				{
					handler: function() {
						me.hide();
					},
					iconCls: 'cancel16',
					onShiftTabAction: function() {
						me.buttons[me.buttons.length - 2].focus();
					},
					onTabAction: function() {
						me.findById('PROLW_SearchFilterTabbar').getActiveTab().fireEvent('activate', me.findById('PROLW_SearchFilterTabbar').getActiveTab());
					},
					text: BTN_FRMCLOSE
				}],
			getFilterForm: function() {
				if ( me.filterForm == undefined ) {
					me.filterForm = me.findById('OrphanRegistryFilterForm');
				}
				return me.filterForm;
			},
			items: [ this.SearchFilters, this.SearchFrame]
		});

		sw.Promed.swPersonRegisterOrphanListWindow.superclass.initComponent.apply(this, arguments);

	},
	show: function() {
		sw.Promed.swPersonRegisterOrphanListWindow.superclass.show.apply(this, arguments);
		var me = this;

		me.SearchFrame.addActions({
			name:'action_export',
			text:langs('Выгрузка в федеральный регистр'),
			tooltip: langs('Выгрузка в федеральный регистр'),
			iconCls : 'doc-reg16',
			handler: function() {
				getWnd('swPersonRegisterExportWindow').show({PersonRegisterType_SysNick: 'orphan'});
			}
		});

		me.SearchFrame.addActions({
			name:'person_register_dis',
			text:langs('Исключить из регистра'),
			tooltip: langs('Исключить из регистра'),
			iconCls: 'pers-disp16',
			handler: function() {
				me.openWindowPersonRegisterDis();
			}
		});

		me.SearchFrame.addActions({
			name:'open_emk',
			text:langs('Открыть ЭМК'),
			tooltip: langs('Открыть электронную медицинскую карту пациента'),
			iconCls: 'open16',
			handler: function() {
				me.emkOpen();
			}
		});

		me.restore();
		me.center();
		me.maximize();
		me.doReset();


		if(arguments[0] && arguments[0].editType) {
			me.editType = arguments[0].editType;
		}

		if(arguments[0] && arguments[0].fromARM) {
			me.fromARM = arguments[0].fromARM;
		}


		if(arguments[0].ARMType){
			me.ARMType = arguments[0].ARMType;
		}


		if (arguments[0].userMedStaffFact) {
			me.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		else {
			if (sw.Promed.MedStaffFactByUser.last) {
				me.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else {
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: me.ARMType,
					onSelect: function(data) {
						me.userMedStaffFact = data;
					}
				});
			}
		}


		var base_form = me.findById('OrphanRegistryFilterForm').getForm();




		me.changeAccessToButtons();


		// TODO изменить как другие кнопки и перенести в changeAccessToButtons
		if(me.fromARM !== null && me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])){
			me.SearchFrame.setActionHidden('open_emk',false);
			me.SearchFrame.setActionDisabled('open_emk',false);
		}
		else {
			me.SearchFrame.setActionHidden('open_emk',true);
			me.SearchFrame.setActionDisabled('open_emk',true);
		}



		me.doLayout();

		base_form.findField('PersonRegisterType_id').setValue(1);

		me.doSearch({firstLoad: true});
	},



	// -----------------------------------------------------------------------------------------------------------------
	// Изменяем доступ к кнопкам
	changeAccessToButtons: function(){
		var me = this;

		me.SearchFrame.setActionDisabled('person_register_dis', true);
		if(me._checkOpenAccessToButtonPersonRegisterDis()){
			me.SearchFrame.setActionDisabled('person_register_dis', false);
		}

		me.SearchFrame.setActionDisabled('action_export', true);
		if(me._checkOpenAccessToButtonExport()){
			me.SearchFrame.setActionDisabled('action_export', false);
		}


		me.SearchFrame.setActionDisabled('action_edit', true);
		if(me._checkOpenAccessToButtonEdit()){
			me.SearchFrame.setActionDisabled('action_edit', false);
		}


		me.SearchFrame.setActionDisabled('action_delete', true);
		if(me._checkOpenAccessToButtonDelete()){
			me.SearchFrame.setActionDisabled('action_delete', false);
		}


		me.SearchFrame.setActionDisabled('action_add', true);
		if(me._checkOpenAccessToButtonAdd()){
			me.SearchFrame.setActionDisabled('action_add', false);
		}

		return me;
	},

	// кнопка "Исключить из регистра"
	// Проверяем можно ли открыть доступ к кнопке "Исключить из регистра"
	_checkOpenAccessToButtonPersonRegisterDis: function(){
		var me = this;

		var record = me.selectedRow;
		var is_open = true;


		if(
			 ! sw.Promed.personRegister.isOrphanRegistryOperator() &&
			 ! (this.fromARM !== null && this.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer']))
		){
			me.isDisabled_ButtonPersonRegisterDis = true;
		}


		// Если выбрана запись
		if(record){
			var PersonRegister_id = record.get('PersonRegister_id');
			var PersonRegister_disDate = record.get('PersonRegister_disDate');
			var PersonRegister_setDate = record.get('PersonRegister_setDate');
			var curDate = getGlobalOptions().date;

			if(
				Ext.isEmpty(PersonRegister_id) ||
				Ext.isEmpty(PersonRegister_disDate) == false ||
				PersonRegister_setDate.format('d.m.Y') == curDate
			){
				is_open = false;
			}
		}

		// Глобальная блокировка вне зависимости от выбранной строки
		if(me.isDisabled_ButtonPersonRegisterDis == true ){
			is_open = false;
		}

		return is_open;
	},

	// кнопка "Добавить"
	_checkOpenAccessToButtonAdd: function(){
		var me = this;

		var is_open = true;

		/*
			Кнопка доступна, если:
				- форма вызвана из АРМ:
					- Специалиста Минздрава
					- Специалиста ЛЛО ОУЗ
					- Главного внештатного специалиста при МЗ

		*/
		if(
				me.fromARM == null ||
				( ! me.fromARM.inlist([

					// Специалиста Минздрава
					'minzdravdlo',

					// Специалиста ЛЛО ОУЗ
					'spec_mz',

					// Главного внештатного специалиста при МЗ
					'mzchieffreelancer'
				]))
			){
				is_open = false;
		}

		return is_open;
	},

	// кнопка "Удалить"
	_checkOpenAccessToButtonDelete: function(){
		var me = this;

		var is_open = true;

		if(
			me.fromARM == null ||
			( ! me.fromARM.inlist([
				// Специалиста Минздрава
				'minzdravdlo',

				// Специалиста ЛЛО ОУЗ
				'spec_mz',

				// Главного внештатного специалиста при МЗ
				'mzchieffreelancer'

			]))
		){
			me.isDisabled_ButtonDelete = true;
		}


		// Глобальная блокировка вне зависимости от выбранной строки
		if(me.isDisabled_ButtonDelete == true){
			is_open = false;
		}

		return is_open;
	},

	// TODO нужно перепроверить, временно закоментировал глобальную блокировку чтобы работало как раньше т.к. по задаче изменять эту кнопку не нужно было
	// кнопка "Изменить"
	_checkOpenAccessToButtonEdit: function(){
		var me = this;
		var record = me.selectedRow;
		var is_open = true;


		if(
			me.ARMType == 'spesexpertllo' ||
			me.ARMType == 'adminllo' ||
			( ! sw.Promed.personRegister.isOrphanRegistryOperator())
		) {

			if(
				me.fromARM == null ||
				( ! me.fromARM.inlist([
					// Специалиста Минздрава
					'minzdravdlo',

					// Специалиста ЛЛО ОУЗ
					'spec_mz',

					// Главного внештатного специалиста при МЗ
					'mzchieffreelancer'

				]))
			){
				// ГЛОБАЛЬНАЯ БЛОКИРОВКА
				//me.isDisabled_ButtonEdit = true;
				is_open = false;
			}

		} else {
			if(
				me.fromARM == null ||
				( ! me.fromARM.inlist([
					// Специалиста Минздрава
					'minzdravdlo',

					// Специалиста ЛЛО ОУЗ
					'spec_mz',

					// Главного внештатного специалиста при МЗ
					'mzchieffreelancer'

				]))
			){
				// ГЛОБАЛЬНАЯ БЛОКИРОВКА
				//me.isDisabled_ButtonEdit = true;
				is_open = false;
			}
		}

		if(
			me.fromARM == null ||
			me.fromARM.inlist([
				// Специалиста Минздрава
				'minzdravdlo',

				// Специалиста ЛЛО ОУЗ
				'spec_mz',

				// Главного внештатного специалиста при МЗ
				'mzchieffreelancer'

			])
		) {
			//me.isDisabled_ButtonEdit = true;
			is_open = false;
		}

		if(record){
			if(
				me.fromARM == null ||
				( ! me.fromARM.inlist([

					// Специалиста Минздрава
					'minzdravdlo',

					// Специалиста ЛЛО ОУЗ
					'spec_mz',

					// Главного внештатного специалиста при МЗ
					'mzchieffreelancer'
				]))
			){
				if(Ext.isEmpty(record.get('PersonRegister_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false){
					is_open = false;
				} else {
					is_open = true;
				}
			}
		}

		// Глобальная блокировка вне зависимости от выбранной строки
		if(me.isDisabled_ButtonEdit == true){
			is_open = false;
		}


		return is_open;
	},

	// кнопка "Выгрузка в федеральный регистр"
	_checkOpenAccessToButtonExport: function(){

		if(getRegionNick()=='kz')
			return false;
		
		var me = this,
			is_open = true;		

		if(
			me.ARMType != 'spesexpertllo' &&
			me.ARMType != 'adminllo' &&
			sw.Promed.personRegister.isOrphanRegistryOperator()
		){
			if (
				me.fromARM == null ||
				!me.fromARM.inlist([
					// Специалиста Минздрава
					'minzdravdlo',

					// Специалиста ЛЛО ОУЗ
					'spec_mz',

					// Главного внештатного специалиста при МЗ
					'mzchieffreelancer'
				])
			){
				is_open = false;
			}
		}

		return is_open;
	},
	// -----------------------------------------------------------------------------------------------------------------


	getButtonSearch: function() {
		return Ext.getCmp('PROLW_SearchButton');
	},

	doReset: function() {
		var me = this;

		var base_form = me.findById('OrphanRegistryFilterForm').getForm();
		base_form.reset();
		me.SearchFrame.ViewActions.open_emk.setDisabled(true);
		me.SearchFrame.ViewActions.person_register_dis.setDisabled(true);
		me.SearchFrame.ViewActions.action_view.setDisabled(true);
		me.SearchFrame.ViewActions.action_delete.setDisabled(true);
		me.SearchFrame.ViewActions.action_refresh.setDisabled(true);
		me.SearchFrame.getGrid().getStore().removeAll();
		me.fromARM = null;
		me.editType = 'all';
		me.ARMType = null;
		this.SearchFrame.getGrid().getViewFrame().removeAll();

		var el;
		el = base_form.findField('PersonRegisterType_id');// Регистр - Тип записи регистра - Все
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('PrivilegeStateType_id');// Льгота - Актуальность льготы - 1. Действующие льготы
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('PersonCardStateType_id');// Прикрепление - Актуальность прикр-я - 1. Актуальные прикрепления
		if(typeof(el) !== 'undefined') el.setValue(1);
		el = base_form.findField('AddressStateType_id');// Адрес - Тип адреса - 1. Адрес регистрации
		if(typeof(el) !== 'undefined') el.setValue(1);
		
		return me;
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('OrphanRegistryFilterForm').getForm();
		
		/*if ( !params.firstLoad && this.findById('OrphanRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено ни одно поле'), function() {
			});
			return false;
		}*/

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

		if ( base_form.findField('PersonPeriodicType_id').getValue().toString().inlist([ '2', '3' ]) && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
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

		var post = getAllFormFieldValues(this.findById('OrphanRegistryFilterForm'));
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;
		post.PersonRegisterType_SysNick = this.PersonRegisterType_SysNick;
		
		//log(post);
		var grid = this.SearchFrame.getGrid();

		if ( base_form.isValid() ) {
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
			loadMask.show();
			this.SearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	getRecordsCount: function() {
		var st = this.SearchFrame.getGrid().getStore();
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

	emkOpen: function(){
		var grid = this.SearchFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
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
			ARMType: 'common',
			readOnly: (this.editType == 'onlyRegister')?true:false,
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	deletePersonRegister: function() {
		var grid = this.SearchFrame.getGrid();
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('PersonRegister_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected(),
			me = this;
		
		Ext.Msg.show({
			title: langs('Вопрос'),
			msg: langs('Перед удалением записи удостоверьтесь, что данные по пациенту отсутствуют на федеральном портале по ВЗН. Удалить выбранную запись регистра?'),
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					me.getLoadMask(lang['udalenie']).show();
					Ext.Ajax.request({
						url: '/?c=PersonRegister&m=delete',
						params: {
							PersonRegisterType_SysNick: me.PersonRegisterType_SysNick,
							PersonRegister_id: record.get('PersonRegister_id')
						},
						callback: function(options, success, response) {
							me.getLoadMask().hide();
							if (success) {	
								var obj = Ext.util.JSON.decode(response.responseText);
								if( obj.success )
									grid.getStore().remove(record);
							} else {
								sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_zapisi_registra']);
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},


	// -----------------------------------------------------------------------------------------------------------------
	openWindowAdd: function(){
		var me = this;
		return me._openWindow('add');
	},
	openWindowEdit: function(){
		var me = this;
		return me._openWindow('edit');
	},
	openWindowView: function(){
		var me = this;
		return me._openWindow('view');
	},
	openWindowPersonRegisterDis: function(){
		var me = this;
		return me._openWindow('person_register_dis');
	},
	_openWindow: function(action) {

		// проверяем чтобы action был допустимый (тип окна)
		if (!action || !action.toString().inlist(['person_register_dis','add','view','edit'])) {
			return false;
		}
		var me = this;
		var form = this.getFilterForm().getForm();
		var grid = this.SearchFrame.getGrid();
		if (!grid.getSelectionModel().getSelected() && action!='add') {
			return false;
		}
		var selected_record = grid.getSelectionModel().getSelected();

		var params = {};
		params.userMedStaffFact = this.userMedStaffFact;
		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		switch(action) {
			case 'person_register_dis':
				sw.Promed.personRegister.doExcept({
					PersonRegister_id: selected_record.get('PersonRegister_id')
					,PersonRegisterType_SysNick: me.PersonRegisterType_SysNick
					,MorbusType_SysNick: me.MorbusType_SysNick
					,Person_id: selected_record.get('Person_id')
					,Diag_Name: selected_record.get('Diag_Name')
					,PersonRegister_setDate: selected_record.get('PersonRegister_setDate')
					,callback: function(data) {
						grid.getStore().reload();
					}
				});
				break;
			case 'add':
				sw.Promed.personRegister.create({
					PersonRegisterType_SysNick: me.PersonRegisterType_SysNick
					,MorbusType_SysNick: me.MorbusType_SysNick
					,callback: function(data) {
						me.doSearch({firstLoad: true});
					}
				});
				break;
			case 'edit':
			case 'view':
				if (getWnd('swPersonRegisterOrphanEditWindow').isVisible()) {
					getWnd('swPersonRegisterOrphanEditWindow').hide();
				}
				if ( Ext.isEmpty(selected_record.get('PersonRegister_id')) ) {
					sw.swMsg.alert(langs('Сообщение'), langs('Ошибка выбора записи!'));
					return false;
				}
				params.onHide = function(isChange) {
					if (isChange) {
						grid.getStore().reload();
					} else {
						grid.getView().focusRow(grid.getStore().indexOf(selected_record));
					}
				};

				params.allowSpecificEdit = ('edit' == action);
				params.callback = Ext.emptyFn;
				params.PersonRegister_id = selected_record.data.PersonRegister_id;
				params.MorbusType_SysNick = selected_record.data.MorbusType_SysNick;
				params.Person_id = selected_record.data.Person_id;
				params.editType = me.editType;
				params.action = this.SearchFrame.getAction('action_edit').isDisabled()?'view':'edit';
				getWnd('swPersonRegisterOrphanEditWindow').show(params);
				break;
		}
	}
	// -----------------------------------------------------------------------------------------------------------------
});
