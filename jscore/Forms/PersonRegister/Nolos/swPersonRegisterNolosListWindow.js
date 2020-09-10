/**
 * swPersonRegisterNolosListWindow - Регистр по ВЗН (7 нозологиям)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      02.2015
 * @comment      Префикс для id компонентов N7RW
 */
sw.Promed.swPersonRegisterNolosListWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	title: lang['registr_po_vzn'],
	PersonRegisterType_SysNick: 'nolos',
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
			win.findById('NolosRegistryFilterForm').doLayout();
	},
		'restore': function(win) {
			win.findById('NolosRegistryFilterForm').doLayout();
		},
		'beforeShow': function(win) {
			if(!getWnd('swWorkPlaceMZSpecWindow').isVisible() && !isUserGroup('MIACSuperAdmin'))
			{
				if (false == sw.Promed.personRegister.isAllow(win.PersonRegisterType_SysNick)) {
			return false;
		}
				if (false == sw.Promed.personRegister.isVznRegistryOperator()) {
					sw.swMsg.alert('Сообщение', 'Форма "'+ win.title +'" доступна только для пользователей, с указанной группой «Регистр по ВЗН');
			return false;
		}
					}
			return true;
				},
		'resize': function (win, nW, nH, oW, oH) {
			win.findById('N7RW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('NolosRegistryFilterForm').setWidth(nW - 5);
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
	isDisabled_ButtonAdd: false,
	isDisabled_ButtonEdit: false,
	isDisabled_ButtonView: false,
	// -----------------------------------------------------------------------------------------------------------------


	initComponent: function() {
		var me = this;
		this.SearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: !(isSuperAdmin() || (me.isMzSpecialist() && me.isVznRegistryOperator())), disabled: !(isSuperAdmin() || (me.isMzSpecialist() && me.isVznRegistryOperator())), handler: function() { me.openWindowAdd(); }},
				{name: 'action_edit', handler: function() { me.openWindowEdit(); }},
				{name: 'action_view', handler: function() { me.openWindowView(); }},
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
			disableActions:false,
			dataUrl: C_SEARCH,
			id: 'N7RW_NolosRegistrySearchGrid',
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
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150},
				{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo_prikr'], width: 150},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz_mkb-10'], width: 150, id: 'autoexpand'},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_vklyucheniya_v_registr'], width: 150},
				{name: 'PersonRegister_disDate', type: 'date', format: 'd.m.Y', header: lang['data_isklyucheniya_iz_registra'], width: 170}
				,{name: 'PersonRegisterOutCause_id', type: 'int', hidden: true}
				,{name: 'PersonRegisterOutCause_Name', type: 'string', header: lang['prichina_isklyucheniya_iz_registra'], width: 190}
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
                me.getButtonSearch().disable();
			},
			onLoadData: function() {
                me.getButtonSearch().enable();
			},
			onRowSelect: function(sm,index,record) {

				me.selectedRow = record;

				me.changeAccessToButtons();

				this.getAction('action_view').setDisabled( Ext.isEmpty(record.get('PersonRegister_id')) );

				if(Ext.isEmpty(me.fromARM) || !me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])){
					this.getAction('open_emk').setDisabled( false );
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
			id: 'NolosRegistryFilterForm',
			labelWidth: 130,
			ownerWindow: this,
			searchFormType: 'PersonRegisterBase',
			tabPanelHeight: 235,
			tabPanelId: 'N7RW_SearchFilterTabbar',
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
				}, {
					fieldLabel: lang['nomer_registrovoy_zapisi'],
					name: 'PersonRegister_Code',
					//plugins: [ new Ext.ux.InputTextMask('9999999999999', false)],
					maskRe: new RegExp("^[0-9]*$"),
					maxLength: 13,
					autoCreate: {tag: "input", size:14, maxLength: 13, autocomplete: "off"},
					width: 100,
					xtype: 'textfield'
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
					MorbusType_SysNick: 'vzn',
					PersonRegisterType_SysNick: 'nolos',
					xtype: 'swdiagcombo'
				},{
					fieldLabel: lang['po'],
					hiddenName: 'Diag_Code_To',
					valueField: 'Diag_Code',
					width: 450,
					MorbusType_SysNick: 'vzn',
					PersonRegisterType_SysNick: 'nolos',
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
				id: 'N7RW_SearchButton',
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
                    me.findById('N7RW_SearchFilterTabbar').getActiveTab().fireEvent('activate', me.findById('N7RW_SearchFilterTabbar').getActiveTab());
				},
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( me.filterForm == undefined ) {
                    me.filterForm = me.findById('NolosRegistryFilterForm');
				}
				return me.filterForm;
			},
			items: [ this.SearchFilters, this.SearchFrame]
		});

		sw.Promed.swPersonRegisterNolosListWindow.superclass.initComponent.apply(this, arguments);
		
	},
	show: function() {
		sw.Promed.swPersonRegisterNolosListWindow.superclass.show.apply(this, arguments);
		var me = this;
        if (!arguments[0] || !arguments[0].userMedStaffFact) {
            Ext.Msg.alert(lang['oshibka_otkryitiya_formyi'], lang['ne_peredanyi_parametryi_rabochego_mesta']);
            me.hide();
            return false;
        }
		me.userMedStaffFact = arguments[0].userMedStaffFact;

		me.SearchFrame.addActions({
			name:'action_csv_export',
			text:langs('Выгрузка регистра в формате CSV'),
			tooltip: langs('Выгрузка регистра в формате CSV'),
			hidden: false,
			disabled: true,
			iconCls : 'doc-reg16',
			downloadUrl: '/?c=PersonRegister&m=downloadVznRegisterCsv',
			handler: function() {

				window.open(this.downloadUrl);

				return true;
			}
		});

		me.SearchFrame.addActions({
			name:'action_export', 
			text:lang['vyigruzka_v_federalnyiy_registr'], 
			tooltip: lang['vyigruzka_v_federalnyiy_registr'],
			hidden: false,
			disabled: false,
			iconCls : 'doc-reg16',
			menu: new Ext.menu.Menu({
				items: [
					{
						text: langs('Регистровые записи'),
						tooltip: langs('Регистровые записи'),
						iconCls : 'doc-reg16',
						handler: function() {
							getWnd('swPersonRegisterNolosExportWindow').show({ExportMod: 'RegisterRecords'});
						}
					},
					{
						text: langs('Рецепты'),
						tooltip: langs('Сведения о выписанных и отпущенных лекарственных препаратах по ВЗН'),
						iconCls : 'doc-reg16',
						handler: function() {
							getWnd('swPersonRegisterNolosExportWindow').show({ExportMod: 'Recepts'});
						}
					}
				]
			})
		});
		
		me.SearchFrame.addActions({
			name:'person_register_dis', 
			text:lang['isklyuchit_iz_registra'], 
			tooltip: lang['isklyuchit_iz_registra'],
			iconCls: 'pers-disp16',
			handler: function() {
				me.openWindowPersonRegisterDis();
			}
		});
		
		me.SearchFrame.addActions({
			name:'open_emk', 
			text:lang['otkryit_emk'], 
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				me.emkOpen();
			}
		});
		
		var base_form = me.findById('NolosRegistryFilterForm').getForm();

		me.restore();
		me.center();
		me.maximize();
		me.doReset();

		me.fromARM = '';
		if(arguments[0] && arguments[0].fromARM) {
			me.fromARM = arguments[0].fromARM;
		}

		me.changeAccessToButtons();


		if(sw.Promed.personRegister.isVznRegistryOperator() && me.isMzSpecialist() && me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])){
			me.SearchFrame.setActionHidden('open_emk',true);
			me.SearchFrame.setActionDisabled('open_emk',true);
		}

		me.editType = 'all';

		if(arguments[0] && arguments[0].editType) {
			me.editType = arguments[0].editType;
			}

		if (arguments[0].userMedStaffFact)
		{
			me.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				me.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						me.userMedStaffFact = data;
					}
				});
			}
		}

        //Доступ к функциональной кнопке "Выгрузка в федеральный регистр" и "Выгрузка регистра в формате CSV" реализовать только если форма "Регистр по ВЗН" открыта из АРМ АДминистратор ЛЛО, из АРМ специалиста ЛЛО ОУЗ или АРМ Минздрава
		var arm_type = me.userMedStaffFact.ARMType || me.fromARM,
			exportRights = ['minzdravdlo','adminllo','spec_mz'];

		me.SearchFrame.getAction('action_export').setDisabled( false == arm_type.inlist(exportRights) );
		me.SearchFrame.getAction('action_csv_export').setDisabled( false == arm_type.inlist(exportRights) );

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

		if(sw.Promed.personRegister.isVznRegistryOperator() && getRegionNick().inlist(['kareliya'])){
			me.isDisabled_ButtonPersonRegisterDis = true;
		}

		// Если выбрана запись
		if(record){

			var PersonRegister_disDate = record.get('PersonRegister_disDate');
			var PersonRegister_setDate = record.get('PersonRegister_setDate');
			var curDate = getGlobalOptions().date;

			if(
				me.fromARM == null ||
				( ! me.fromARM.inlist([

					// Специалиста Минздрава
					'minzdravdlo',

					// Специалиста ЛЛО ОУЗ
					'spec_mz',

					// Главного внештатного специалиста при МЗ
					'mzchieffreelancer'
				])) ||
				 ! Ext.isEmpty(PersonRegister_disDate) ||
				( ! Ext.isEmpty(PersonRegister_setDate) &&
					PersonRegister_setDate.format('d.m.Y') == curDate
				)
			){
				is_open = false;
			}
		}

		// Убрал глобальную блокировку в рамках задачи https://redmine.swan-it.ru/issues/138346
		if(me.isDisabled_ButtonPersonRegisterDis == true ){
			is_open = true;
		}

		return is_open;
	},

	// кнопка "Добавить"
	_checkOpenAccessToButtonAdd: function(){
		var me = this;

		var is_open = true;


		// if( ! sw.Promed.personRegister.isVznRegistryOperator()){
		// 	me.isDisabled_ButtonAdd = true
		// }

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
			me.isDisabled_ButtonAdd = true
		}

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

		// Глобальная блокировка вне зависимости от выбранной строки
		if(me.isDisabled_ButtonAdd == true){
			is_open = false;
		}

		return is_open;
	},

	// кнопка "Удалить"
	_checkOpenAccessToButtonDelete: function(){
		var me = this;

		var is_open = true;
		var record = me.selectedRow;

		// if( ! sw.Promed.personRegister.isVznRegistryOperator()){
		// 	me.isDisabled_ButtonDelete = true;
		// }
		// else {
		// 	if(me.isMzSpecialist() && me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])){
		// 		me.isDisabled_ButtonDelete = true;
		// 	}
		// }

		if(Ext.isEmpty(me.fromARM) || ! me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])){
			me.isDisabled_ButtonDelete = true;
		}



		if(record){
			if(Ext.isEmpty(me.fromARM) || ! me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])){
				// if(Ext.isEmpty(record.get('PersonRegister_id'))){
				// 	is_open = false;
				// }
				is_open = false;
			}
		}

		// Глобальная блокировка вне зависимости от выбранной строки
		if(me.isDisabled_ButtonDelete == true){
			is_open = false;
		}

		return is_open;
	},

	// кнопка "Изменить"
	_checkOpenAccessToButtonEdit: function(){
		var me = this;
		var record = me.selectedRow;
		var is_open = true;

		if( ! sw.Promed.personRegister.isVznRegistryOperator()){
			me.isDisabled_ButtonEdit = true;
		}
		else {
			if(me.isMzSpecialist() && me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])){
				me.isDisabled_ButtonEdit = true;
			}
		}


		if(me.isDisabled_ButtonEdit == false){
			if(record){
				if(Ext.isEmpty(me.fromARM) || !me.fromARM.inlist(['minzdravdlo','spec_mz','mzchieffreelancer'])){
					if(Ext.isEmpty(record.get('PersonRegister_id')) || Ext.isEmpty(record.get('PersonRegister_disDate')) == false){
						is_open = false;
					}
				}
			}
		}


		// Глобальная блокировка вне зависимости от выбранной строки
		if(me.isDisabled_ButtonEdit == true){
			is_open = false;
		}


		return is_open;
	},

	// -----------------------------------------------------------------------------------------------------------------



	isMzSpecialist: function() {
		return (haveArmType('minzdravdlo') || haveArmType('spec_mz') || haveArmType('mzchieffreelancer'));
	},
	isVznRegistryOperator: function() {
		return (String(getGlobalOptions().groups).indexOf('VznRegistry', 0) >= 0);
	},


	getButtonSearch: function() {
		return Ext.getCmp('N7RW_SearchButton');
	},

	doReset: function() {

		var base_form = this.findById('NolosRegistryFilterForm').getForm();
		base_form.reset();
		this.SearchFrame.ViewActions.open_emk.setDisabled(true);
		this.SearchFrame.ViewActions.person_register_dis.setDisabled(true);
		this.SearchFrame.ViewActions.action_view.setDisabled(true);
		this.SearchFrame.ViewActions.action_delete.setDisabled(true);
		this.SearchFrame.ViewActions.action_refresh.setDisabled(true);
		this.SearchFrame.getGrid().getStore().removeAll();
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
	},
	doSearch: function(params) {

		if (typeof params != 'object') {
			params = {};
		}

		var base_form = this.findById('NolosRegistryFilterForm').getForm();

		/*// #108795 проверка уже не требуется, просят выводить весь список при пустых полях
		if ( !params.firstLoad && this.findById('NolosRegistryFilterForm').isEmpty() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено ни одно поле'), function() {
			});
			return false;
		}*/

		var grid = this.SearchFrame.getGrid();

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

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('NolosRegistryFilterForm'));
		//post.DispLpu_id = base_form.findField('DispLpu_id').getValue();

		post.limit = 100;
		post.start = 0;
		post.PersonRegisterType_SysNick = this.PersonRegisterType_SysNick;

		//log(post);

		if ( base_form.isValid() ) {
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

	emkOpen: function() {
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
		if (!action || !action.toString().inlist(['registry_export','person_register_dis','add','view','edit'])) {
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
					,Person_id: selected_record.get('Person_id')
					,Diag_Name: selected_record.get('Diag_Name')
					,PersonRegister_setDate: selected_record.get('PersonRegister_setDate')
					,callback: function(data) {
						grid.getStore().reload();
					}
					,Diag_id: selected_record.get('Diag_id')
				});
				break;
			case 'add':
				sw.Promed.personRegister.add({
					viewOnly: (me.editType=='onlyRegister' && !me.isVznRegistryOperator())?true:false,
					MorbusType_SysNick: 'vzn' // Регистр ВИЧ-инфицированных
					,callback: function(data) {
						form.findField('Person_Firname').setValue(data.Person_Firname);
						form.findField('Person_Secname').setValue(data.Person_Secname);
						form.findField('Person_Surname').setValue(data.Person_Surname);
						form.findField('Person_Birthday').setValue(data.Person_Birthday);
						me.doSearch();

						//открывает окно для редактирования специфики
						var params = new Object();
						params.userMedStaffFact = me.userMedStaffFact;
						params.action = 'view';
						params.callback = Ext.emptyFn;
						params.onHide = function() {
							grid.getView().focusRow(0);
						};
						if (getWnd('swPersonRegisterNolosEditWindow').isVisible()) {
							getWnd('swPersonRegisterNolosEditWindow').hide();
						}
						params.allowSpecificEdit = true;
						params.PersonRegister_id = data.PersonRegister_id;
						params.Person_id = data.Person_id;
						params.PersonEvn_id = data.PersonEvn_id;
						params.Server_id = data.Server_id;
						params.MorbusType_SysNick = data.MorbusType_SysNick
						log(data,'data123');
						Ext.Ajax.request({
							url: '/?c=PersonRegister&m=createEvnNotifyRegisterInclude',
							params: {
								Diag_id:data.Diag_id,
								Lpu_did:data.Lpu_iid,
								MedPersonal_id:	(getGlobalOptions().medpersonal_id ? getGlobalOptions().medpersonal_id : data.MedPersonal_iid ),
								PersonEvn_id:data.PersonEvn_id,
								PersonRegisterType_SysNick:	'nolos',
								Person_id:data.Person_id,
								Server_id:data.Server_id,
								PersonRegister_id: data.PersonRegister_id,
								EvnNotifyRegister_setDate: (data.Direction_setDate ? data.Direction_setDate :data.PersonRegister_setDate),
								EvnNotifyRegister_Comment: (data.Direction_Comment ? data.Direction_Comment :null),
								EvnNotifyRegister_Num: (data.Direction_Num ? data.Direction_Num :null)
							},
							callback: function(options, success, response) {

								if (success) {
									var obj = Ext.util.JSON.decode(response.responseText);
									if( obj.success ) {
										getWnd('swPersonRegisterNolosEditWindow').show(params);
										// We need to go deeper
										Ext.Ajax.request({
											params: {
												PersonRegister_id: data.PersonRegister_id
												,EvnNotifyBase_id: obj.EvnNotifyRegister_id
												,Person_id: data.Person_id
												,Diag_id: data.Diag_id
												,MorbusType_SysNick: data.MorbusType_SysNick
												,Morbus_id: null
												,PersonRegister_setDate: data.PersonRegister_setDate
												,Lpu_iid: getGlobalOptions().lpu_id || data.Lpu_iid
												,MedPersonal_iid: getGlobalOptions().medpersonal_id || data.MedPersonal_iid
												,Mode: null
												,PersonRegisterType_id: 49
											},
											url:'/?c=PersonRegister&m=save'
										});
									}
								} else {
									sw.swMsg.alert(langs('Ошибка'), langs('Ошибка'));
								}
							}
						});

					},
					searchMode: 'all'
				});
				break;
			case 'edit':
			case 'view':
				if (getWnd('swPersonRegisterNolosEditWindow').isVisible()) {
					getWnd('swPersonRegisterNolosEditWindow').hide();
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
				params.Person_id = selected_record.data.Person_id;
				params.MorbusType_SysNick = selected_record.data.MorbusType_SysNick;// для фильтрации диагнозов по той же нозологии
				params.action = me.SearchFrame.getAction('action_edit').isHidden()?'view':'edit';
				params.editType = me.editType;
				getWnd('swPersonRegisterNolosEditWindow').show(params);
				break;
		}
	}
	// -----------------------------------------------------------------------------------------------------------------

});
