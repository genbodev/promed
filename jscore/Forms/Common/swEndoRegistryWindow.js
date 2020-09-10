/**
* swEndoRegistryWindow - окно регистра по эндопротезированию
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2015 Swan Ltd.
* @author       Dmitry Vlasenko
* @comment      Префикс для id компонентов ERW (EndoRegistryWindow)
*
*/

/*NO PARSE JSON*/
sw.Promed.swEndoRegistryWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['registr_po_endoprotezirovaniyu'],
	width: 800,    
    codeRefresh: true,
	objectName: 'swEndoRegistryWindow',
	id: 'swEndoRegistryWindow',	
    buttonAlign: 'left',
	closable: true,

	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp('ERW_SearchButton');
	},
    inArray: function(needle, array){
        for(var k in array){
            if(array[k] == needle)
                return true;
        }
        
        return false;
    },
	doReset: function() {
		
		var base_form = this.findById('EndoRegistryFilterForm').getForm();
		base_form.reset();
		this.EndoRegistrySearchFrame.removeAll({ clearAll: true });
				
	},
	doSearch: function(params) {
		
		if (typeof params != 'object') {
			params = {};
		}
		
		var base_form = this.findById('EndoRegistryFilterForm').getForm();
		var grid = this.EndoRegistrySearchFrame.getGrid();

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

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('EndoRegistryFilterForm'));

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.EndoRegistrySearchFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	height: 550,
	emkOpen: function()
	{
		var grid = this.EndoRegistrySearchFrame.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id') )
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();

		getWnd('swPersonEmkWindow').show({
			readOnly: true,
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			ARMType: 'common',
			callback: function()
			{
				//
			}.createDelegate(this)
		});
	},
	openPersonRegisterEndoEditWindow: function(action) {
		var win = this;
		var grid = this.EndoRegistrySearchFrame.getGrid();

		var params = {};
		params.action = action;
		params.callback = function(data) {
			this.doSearch();
		}.createDelegate(this);

		var viewOnly = false;
		if(win.editType == 'onlyRegister')
			viewOnly = true;

		if ( action != 'add' ) {
			var record = grid.getSelectionModel().getSelected();
			if (!record || Ext.isEmpty(record.get('PersonRegisterEndo_id'))) {
				return false;
			}

			params.PersonRegisterEndo_id = record.get('PersonRegisterEndo_id');
			params.editType = win.editType;
			getWnd('swPersonRegisterEndoEditWindow').show(params);
		} else {
			// сначала форма поиска пациента
			getWnd('swPersonSearchWindow').show({
				viewOnly: viewOnly,	
				onSelect: function(person_data) {
					getWnd('swPersonSearchWindow').hide();

					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
					params.Lpu_iid = getGlobalOptions().lpu_id;
					params.editType = win.editType;
					if (win.userMedStaffFact && win.userMedStaffFact.MedPersonal_id) {
						params.MedPersonal_iid = win.userMedStaffFact.MedPersonal_id;
					}

					getWnd('swPersonRegisterEndoEditWindow').show(params);
				},
				searchMode: 'all'
			});
		}
	},
	deletePersonRegisterEndo: function() {
		var grid = this.EndoRegistrySearchFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('PersonRegisterEndo_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						callback: function(opt, scs, response) {
							this.EndoRegistrySearchFrame.getAction('action_refresh').execute();
						}.createDelegate(this),
						params: {
							PersonRegisterEndo_id: record.get('PersonRegisterEndo_id')
						},
						url: '/?c=PersonRegisterEndo&m=deletePersonRegisterEndo'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_zapis'],
			title:lang['podtverjdenie']
		});
	},
	initComponent: function() {
		var win = this;
        
		this.EndoRegistrySearchFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler: function() { this.openPersonRegisterEndoEditWindow('add'); }.createDelegate(this)},
                {name: 'action_edit', handler: function() { this.openPersonRegisterEndoEditWindow('edit'); }.createDelegate(this)},
                {name: 'action_view',  handler: function() { this.openPersonRegisterEndoEditWindow('view'); }.createDelegate(this)},
				{name: 'action_delete',  handler: function() { this.deletePersonRegisterEndo(); }.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: C_SEARCH,
			object: 'EndoRegistry',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'PersonRegisterEndo_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonRegister_Code', type: 'int', header: lang['nomer'], width: 100},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 200},
				{name: 'Person_Firname', type: 'string', header: lang['imya'], width: 200},
				{name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 200},
				{name: 'Person_Age', type: 'string', header: lang['vozrast'], width: 80},
				{name: 'Diag_Name', type: 'string', header: lang['diagnoz'], width: 150},
				{name: 'CategoryLifeDegreeType_Name', type: 'string', header: lang['stepen'], width: 90},
				{name: 'ProsthesType_Name', type: 'string', header: lang['tip_protezirovaniya'], width: 150},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo'], width: 150},
				{name: 'MedPersonal_Fio', type: 'string', header: lang['vrach'], width: 150},
				{name: 'PersonRegisterEndo_obrDate', type: 'date', format: 'd.m.Y', header: lang['data_obrascheniya'], width: 80},
				{name: 'PersonRegister_setDate', type: 'date', format: 'd.m.Y', header: lang['data_postanovki'], width: 80},
				{name: 'PersonRegisterEndo_callDate', type: 'date', format: 'd.m.Y', header: lang['data_vyizova_na_operatsiyu'], width: 80},
				{name: 'PersonRegisterEndo_hospDate', type: 'date', format: 'd.m.Y', header: lang['data_gospitalizatsii_v_statsionar'], width: 80},
				{name: 'PersonRegisterEndo_operDate', type: 'date', format: 'd.m.Y', header: lang['data_operatsii'], width: 80},
				{name: 'PersonRegisterEndo_Contacts', type: 'string', header: lang['adres_i_telefon'], width: 150},
				{name: 'PersonRegisterEndo_Comment', type: 'string', header: lang['primechanie'], width: 150}
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

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				tabIndex: TABINDEX_ERW + 120,
				id: 'ERW_SearchButton',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_ERW + 121,
				text: BTN_FRMRESET
			},
            {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('ERW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('ERW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_ERW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EndoRegistryFilterForm');
				}
				return this.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
                isDisplayPersonRegisterRecordTypeField: false,
				allowPersonPeriodicSelect: true,
				id: 'EndoRegistryFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'EndoRegistry',
				tabIndexBase: TABINDEX_ERW,
				tabPanelHeight: 225,
				tabPanelId: 'ERW_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 220,
					layout: 'form',
					listeners: {
						'activate': function() {
							this.getFilterForm().getForm().findField('PersonRegister_Code').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['6_registr'],
					items: [{
						xtype: 'numberfield',
						name: 'PersonRegister_Code',
						fieldLabel: lang['nomer'],
						allowDecimals: false,
						allowNegative: false,
						width: 200
					}, {
						fieldLabel: lang['tip_protezirovaniya'],
						comboSubject: 'ProsthesType',
						hiddenName: 'ProsthesType_id',
						width: 200,
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: lang['mo_postanovki_na_uchet'],
						plugins: [ new Ext.ux.translit(true) ],
						hiddenName: 'Lpu_iid',
						listeners: {
							'change': function(combo, newValue) {
								// прогрузить врачей
								var base_form = win.findById('EndoRegistryFilterForm').getForm();

								base_form.findField('MedPersonal_iid').clearValue();
								base_form.findField('MedPersonal_iid').getStore().removeAll();
								if (!Ext.isEmpty(newValue)) {
									base_form.findField('MedPersonal_iid').getStore().load({
										params: {
											Lpu_id: newValue
										},
										callback: function () {

										}
									});
								}
							}
						},
						width: 400,
						ctxSerach: true,
						xtype: 'swlpucombo'
					}, {
						fieldLabel: lang['vrach'],
						plugins: [ new Ext.ux.translit(true) ],
						hiddenName: 'MedPersonal_iid',
						width: 400,
						allowBlank: true,
						anchor: '',
						editable: true,
						xtype: 'swmedpersonalcombo'
					}, {
						fieldLabel: lang['data_postanovki_na_uchet'],
						name: 'PersonRegister_setDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}, {
						fieldLabel: lang['data_gospitalizatsii_v_statsionar'],
						name: 'PersonRegisterEndo_hospDate_Range',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
						width: 170,
						xtype: 'daterangefield'
					}]
				}]
			}),
			this.EndoRegistrySearchFrame]
		});
		
		sw.Promed.swEndoRegistryWindow.superclass.initComponent.apply(this, arguments);
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('EndoRegistryFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('EndoRegistryFilterForm').doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.findById('ERW_SearchFilterTabbar').setWidth(nW - 5);
			win.findById('EndoRegistryFilterForm').setWidth(nW - 5);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEndoRegistryWindow.superclass.show.apply(this, arguments);
		
		var base_form = this.findById('EndoRegistryFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		//this.findById('ERW_SearchFilterTabbar').setActiveTab(0);
		this.editType = 'all';
		if(arguments[0] && arguments[0].editType)
		{
			this.editType = arguments[0].editType;
		}
		this.EndoRegistrySearchFrame.addActions({
			name:'open_emk',
			text:lang['otkryit_emk'],
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			iconCls: 'open16',
			handler: function() {
				this.emkOpen();
			}.createDelegate(this)
		});
		this.EndoRegistrySearchFrame.setActionHidden('action_add',!isUserGroup('EndoRegistry'));
		this.EndoRegistrySearchFrame.setActionDisabled('action_edit',!isUserGroup('EndoRegistry'));
		this.EndoRegistrySearchFrame.setActionHidden('action_edit',!isUserGroup('EndoRegistry'));
		this.EndoRegistrySearchFrame.setActionHidden('action_delete',!isUserGroup('EndoRegistry'));
		if (arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}

		this.findById('ERW_SearchFilterTabbar').setActiveTab(5);
		this.doSearch();

		this.doLayout();
	}
});