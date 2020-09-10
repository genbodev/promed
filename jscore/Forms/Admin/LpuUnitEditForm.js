/**
* swLpuUnitEditForm - окно просмотра и редактирования подразделений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      08.04.2009
*/
/*NO PARSE JSON*/
sw.Promed.swLpuUnitEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:langs('Группа отделений'),
	id: 'LpuUnitEditForm',
	layout: 'border',
	maximizable: false,
	resizable: false,
	shim: false,
	width: 700,
	height: 650,
	minWidth: 700,
	minHeight: 650,
	modal: true,
	buttons: [{
		text: BTN_FRMSAVE,
		id: 'luOk',
		tabIndex: 2459,
		iconCls: 'save16',
		handler: function() {
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	}, 
	{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) 
		{
			ShowHelp(this.ownerCt.title);
		}
	},
	{
		text: BTN_FRMCANCEL,
		id: 'luCancel',
		tabIndex: 2460,
		iconCls: 'cancel16',
		onTabAction: function()
		{
			this.ownerCt.findById('luLpuUnit_Code').focus();
		},
		onShiftTabAction: function()
		{
			Ext.getCmp('luOk').focus();
		},
		handler: function()
		{
			this.ownerCt.hide();
			this.ownerCt.returnFunc(this.ownerCt.owner, -1);
		}
	}],
	returnFunc: function(owner) {},
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	show: function() 
	{
		sw.Promed.swLpuUnitEditForm.superclass.show.apply(this, arguments);

		var win = this;

		if ( !arguments[0] ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют необходимые параметры'), function() { win.hide(); });
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('LpuUnitEditForm'), { msg: LOAD_WAIT });
		loadMask.show();

		var isKZ = (getRegionNick() == 'kz' || getGlobalOptions().region.nick.inlist([ 'kz' ]));

		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].LpuUnit_id)
			this.LpuUnit_id = arguments[0].LpuUnit_id;
		else 
			this.LpuUnit_id = null;
		if (arguments[0].LpuBuilding_id)
			this.LpuBuilding_id = arguments[0].LpuBuilding_id;
		else 
			this.LpuBuilding_id = null;
		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		else 
			this.Lpu_id = null;
		if (arguments[0].RegisterMO_OID)
			this.RegisterMO_OID = arguments[0].RegisterMO_OID;
		else
			this.RegisterMO_OID = null;
		if (arguments[0].LpuUnitType_id)
			this.LpuUnitType_id = arguments[0].LpuUnitType_id;
		else 
			this.LpuUnitType_id = null;

		win.findById('LpuUnitEditFormTabPanel').setActiveTab('luef_frmo_tab');
		win.findById('LpuUnitEditFormTabPanel').setActiveTab('luef_main_tab');

		var base_form = this.findById('LpuUnitEditFormPanel').getForm();
		var form = this;

		base_form.reset();
		var LpuUnitTypeField =  Ext.getCmp('luLpuUnitType_id');
		LpuUnitTypeField.getStore().reload();
		if (getGlobalOptions().lpu_isLab == 2) {
			LpuUnitTypeField.isLab = true;
		} else {
			LpuUnitTypeField.isLab = false;
		}

		base_form.findField('FRMOUnit_id').setContainerVisible(false);
		base_form.findField('LpuUnitSet_id').setContainerVisible(getRegionNick() == 'ufa');

		if ( getRegionNick() != 'kz' ) {
			if (this.RegisterMO_OID) {
				base_form.findField('FRMOUnit_id').getStore().baseParams.RegisterMO_OID = this.RegisterMO_OID;
				base_form.findField('FRMOUnit_id').getStore().baseParams.Lpu_id = null;
			} else {
				base_form.findField('FRMOUnit_id').getStore().baseParams.RegisterMO_OID = null;
				base_form.findField('FRMOUnit_id').getStore().baseParams.Lpu_id = this.Lpu_id;
			}

			base_form.findField('FRMOUnit_id').lastQuery = 'This query sample that is not will never appear';
		}

		if ( this.action=='view' ) {
			base_form.findField('LpuUnit_begDate').disable();
			base_form.findField('LpuUnit_endDate').disable();
			base_form.findField('LpuBuilding_id').disable();
			base_form.findField('LpuUnit_Code').disable();
			base_form.findField('LpuUnit_Name').disable();
			base_form.findField('LpuUnitType_id').disable();
			base_form.findField('LpuUnitTypeDop_id').disable();
			base_form.findField('LpuUnitSet_id').disable();
			base_form.findField('LpuUnit_IsOMS').disable();
			base_form.findField('LpuUnit_Phone').disable();
			base_form.findField('LpuUnit_Descr').disable();
			base_form.findField('LpuUnit_Email').disable();
			base_form.findField('LpuUnit_IP').disable();
			base_form.findField('LpuUnit_IsEnabled').disable();
			base_form.findField('UnitDepartType_fid').disable();
			base_form.findField('LpuUnitProfile_fid').disable();
			base_form.findField('LpuUnit_isStandalone').disable();
			base_form.findField('LpuBuildingPass_id').disable();
			base_form.findField('LpuUnit_isHomeVisit').disable();
			base_form.findField('LpuUnit_isCMP').disable();
			base_form.findField('LpuUnit_isPallCC').disable();
			base_form.findField('LpuUnit_IsNotFRMO').disable();
			base_form.findField('FRMOUnit_id').disable();

			form.buttons[0].hide();
		}
		else {
			base_form.findField('LpuUnit_begDate').setDisabled(!isAdmin && !isLpuAdmin());
			base_form.findField('LpuUnit_endDate').setDisabled(!isAdmin && !isLpuAdmin());
			base_form.findField('LpuBuilding_id').enable();
			base_form.findField('LpuUnit_Code').enable();
			base_form.findField('LpuUnit_Name').enable();
			base_form.findField('UnitDepartType_fid').enable();
			base_form.findField('LpuUnitProfile_fid').enable();
			base_form.findField('LpuUnit_isStandalone').enable();
			base_form.findField('LpuBuildingPass_id').enable();
			base_form.findField('LpuUnit_isHomeVisit').enable();
			base_form.findField('LpuUnit_isCMP').enable();
			base_form.findField('LpuUnit_isPallCC').enable();
			base_form.findField('LpuUnit_IsNotFRMO').enable();
			base_form.findField('FRMOUnit_id').enable();

			if ( !this.LpuUnitType_id ) {
				base_form.findField('LpuUnitType_id').enable();
			}
			else {
				base_form.findField('LpuUnitType_id').disable();
			}

			base_form.findField('LpuUnitTypeDop_id').enable();
			base_form.findField('LpuUnitSet_id').enable();
			base_form.findField('LpuUnit_IsOMS').enable();
			base_form.findField('LpuUnit_Phone').enable();
			base_form.findField('LpuUnit_Descr').enable();
			base_form.findField('LpuUnit_Email').enable();
			base_form.findField('LpuUnit_IP').enable();

			base_form.findField('LpuUnit_IsEnabled').setDisabled(!isAdmin && !isLpuAdmin());

			form.buttons[0].show();
		}

		base_form.findField('Lpu_id').setValue(this.Lpu_id);
		base_form.findField('LpuBuilding_id').setValue(this.LpuBuilding_id);
		base_form.findField('LpuUnitType_id').setValue(this.LpuUnitType_id);
		LpuUnitTypeField.fireEvent('change', LpuUnitTypeField, this.LpuUnitType_id)
		base_form.findField('LpuUnitTypeDop_id').getStore().removeAll();
		base_form.findField('LpuUnitTypeDop_id').getStore().load();
		
		base_form.findField('LpuBuilding_id').getStore().baseParams.Lpu_id = this.Lpu_id;
		base_form.findField('LpuBuilding_id').getStore().load({
			callback: function () {
				base_form.findField('LpuBuilding_id').setValue(form.LpuBuilding_id);
			}
		});

		var grid = form.findById('luLpuUnit_OrgHeadGrid').getGrid();
		grid.getStore().removeAll();

		grid.getTopToolbar().items.items[0].disable();
		grid.getTopToolbar().items.items[1].disable();
		grid.getTopToolbar().items.items[2].disable();
		grid.getTopToolbar().items.items[3].disable();

		switch ( this.action ) {
			case 'add':
				form.setTitle(WND_LPUSTRUCT_LPUUNIT + ': ' + FRM_ACTION_ADD);

				grid.getTopToolbar().items.items[0].enable();
			break;

			case 'edit':
				form.setTitle(WND_LPUSTRUCT_LPUUNIT + ': ' + FRM_ACTION_EDIT);

				grid.getTopToolbar().items.items[0].enable();
			break;

			case 'view':
				form.setTitle(WND_LPUSTRUCT_LPUUNIT + ': ' + FRM_ACTION_VIEW);
			break;
		}
		if(!isKZ)
		{
			var LPBcombo = form.findById('LPEW_LpuBuildingPass_id');
			LPBcombo.getStore().load({params:{ Lpu_id: this.Lpu_id }});
		}

		base_form.findField('LpuUnit_IsNotFRMO').setContainerVisible(getRegionNick() != 'kz');
		base_form.findField('FRMOUnit_id').setContainerVisible(getRegionNick() != 'kz');

		if (this.action!='add')
		{
			form.findById('LpuUnitEditFormPanel').getForm().load(
			{
				url: C_LPUUNIT_GET,
				params:
				{
					object: 'LpuUnit',
					Lpu_id: '',
					LpuBuilding_id: '',
					LpuUnit_id: form.LpuUnit_id,
					LpuUnit_Code: '',
					LpuUnit_Name: '',
					LpuUnitType_id: '',
					LpuUnitTypeDop_id: '', 
					LpuUnitSet_id: '', 
					LpuUnit_Phone: '',
					LpuUnit_Descr: '',
					LpuUnit_IsEnabled: ''
				},
				success: function (f, l)
				{
					if(!isKZ)
					{
						var LpuUnit_IsNotFRMO = base_form.findField('LpuUnit_IsNotFRMO').getValue();

						if ( LpuUnit_IsNotFRMO == false ) {
						var LUDT = Ext.getCmp('luUnitDepartType_fid'), haveTypeFRMO = LUDT.getValue();
						f.items.each(function(item){
							switch(item.name){
								case 'LpuUnit_isStandalone': {
									var LPEWcombo = Ext.getCmp('LPEW_LpuBuildingPass_id');
									LPEWcombo.setContainerVisible(item.value == 2);
									LPEWcombo.setAllowBlank(item.value != 2);
									break;
								}
								case 'LpuUnitType_id':{
									var arrCt = [[1, 'LpuUnit_isCMPyn'], [2,'LpuUnit_isHomeVisityn']];
									var input;
									Ext.each(arrCt, function(el, i){

										input = Ext.getCmp(el[1]).setContainerVisible(item.value == el[0]);
										input.setAllowBlank(!(item.value == el[0]));
										if(item.value != el[0])
											input.reset();
									});
										win.changeLpuUnitType(item.value, 'id', haveTypeFRMO);
									break;
								}
							}
						});
					}
					}
					loadMask.hide();
					if (getRegionNick().inlist([ 'ufa' ]))
					{
						var LpuUnitSet_id = form.findById('luLpuUnitSet_id').getValue();
						if (getRegionNick() == 'ufa') {
							var LpuUnit_IsOMS = form.findById('luLpuUnit_IsOMS').getValue();
							form.findById('luLpuUnitSet_id').setAllowBlank(!LpuUnit_IsOMS);
						} else {
							form.findById('luLpuUnitSet_id').setAllowBlank(false);
						}
						form.findById('luLpuUnitSet_id').clearValue();
						form.findById('luLpuUnitSet_id').getStore().removeAll();
						form.findById('luLpuUnitSet_id').getStore().load({
							callback: function() {
								base_form.findField('LpuUnit_begDate').fireEvent('change', base_form.findField('LpuUnit_begDate'), base_form.findField('LpuUnit_begDate').getValue());

								var idx = form.findById('luLpuUnitSet_id').getStore().findBy(function(rec) {
									return (rec.get('LpuUnitSet_id') == LpuUnitSet_id);
								});

								if ( idx >= 0 ) {
									form.findById('luLpuUnitSet_id').setValue(LpuUnitSet_id);
								}
							}
						});
					}
					else 
					{
						form.findById('luLpuUnitSet_id').setAllowBlank(true);
					}

					if ( form.findById('luChildsCount').getValue() == 1 ) {
						form.findById('luLpuBuilding_id').disable();
					}

					if ( getRegionNick() != 'kz' )  {
						base_form.findField('LpuUnit_IsNotFRMO').fireEvent('check', base_form.findField('LpuUnit_IsNotFRMO'), base_form.findField('LpuUnit_IsNotFRMO').getValue());
					}

					var FRMOUnit_id = base_form.findField('FRMOUnit_id').getValue();
					if (!Ext.isEmpty(FRMOUnit_id)) {
						base_form.findField('FRMOUnit_id').getStore().load({
							params: {
								FRMOUnit_id: FRMOUnit_id
							},
							callback: function() {
								if ( base_form.findField('FRMOUnit_id').getStore().getCount() > 0 ) {
									base_form.findField('FRMOUnit_id').setValue(FRMOUnit_id);

									if ( Ext.isEmpty(base_form.findField('LpuUnit_FRMOUnitID').getValue()) ) {
										base_form.findField('FRMOUnit_id').fireEvent('change', base_form.findField('FRMOUnit_id'), FRMOUnit_id);
								}
								}
								else {
									base_form.findField('FRMOUnit_id').clearValue();
								}
							}
						});
					}

					form.findById('luLpuUnit_Code').focus(true);

					grid.getStore().load({params: {LpuUnit_id: win.LpuUnit_id}});
				},
				failure: function ()
				{
					loadMask.hide();
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
				}
			});
		}
		else 
		{
			if(!isKZ)
			{
				Ext.getCmp('LpuUnit_isCMPyn').setContainerVisible(false);
				Ext.getCmp('LpuUnit_isHomeVisityn').setContainerVisible(false);
				Ext.getCmp('luLpuUnitProfile_fid').setContainerVisible(false);
				Ext.getCmp('luUnitDepartType_fid').setContainerVisible(this.LpuUnitType_id);
				this.changeLpuUnitType(this.LpuUnitType_id, 'id');
				var LPEWcombo = Ext.getCmp('LPEW_LpuBuildingPass_id');
				var LPScombo = Ext.getCmp('luLpuUnit_isStandalone');
				LPEWcombo.setContainerVisible((!Ext.isEmpty(LPScombo.value)) && (LPScombo.getValue() == 2));
				LPEWcombo.reset();
				LPEWcombo.setAllowBlank(Ext.isEmpty(LPScombo.value) || (LPScombo.getValue() != 2)); // true допускается
			}

			if (getRegionNick().inlist([ 'ufa' ]))
			{
				if (getRegionNick() == 'ufa') {
					form.findById('luLpuUnit_IsOMS').setValue(true);
					var LpuUnit_IsOMS = form.findById('luLpuUnit_IsOMS').getValue();
					form.findById('luLpuUnitSet_id').setAllowBlank(!LpuUnit_IsOMS);
				} else {
					form.findById('luLpuUnitSet_id').setAllowBlank(false);
				}
				form.findById('luLpuUnitSet_id').getStore().clearFilter();
				form.findById('luLpuUnitSet_id').getStore().removeAll();
				form.findById('luLpuUnitSet_id').getStore().load();
			}
			else 
			{
				form.findById('luLpuUnitSet_id').setAllowBlank(true);
			}
			loadMask.hide();
		}
		
		form.findById('luLpuUnit_Code').focus(true, 50);
	},
	doSave: function(is_edit_org_head) 
	{
		if ( !is_edit_org_head )
			is_edit_org_head = false;
		var form = this.findById('LpuUnitEditFormPanel');

		if(getRegionNick() != 'kz'){
			//проверка поля "наименование"
			var controlLpuUnit_Name = this.controlOfTheFieldluLpuUnit();
			if( controlLpuUnit_Name ){
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: controlLpuUnit_Name,
					title: 'Ошибка в поле «Наименование»'
				});
				return false;
			}
		}
		if (!form.getForm().isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		form.ownerCt.submit(is_edit_org_head);
	},
	submit: function(is_edit_org_head, options)
	{
		if (!options) {
			options = {};
		}

		var form = this.findById('LpuUnitEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuUnitEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		var window = this;
		var post = {LpuUnitType_id: form.findById('luLpuUnitType_id').getValue(), LpuUnit_IsEnabled: (form.findById('luLpuUnit_IsEnabled').getValue()===true)?'on':'off'};

		if ( form.findById('luLpuBuilding_id').disabled ) {
			post.LpuBuilding_id = form.findById('luLpuBuilding_id').getValue();
		}

		if ( Ext.isEmpty(form.findById('luLpuUnitTypeDop_id').getValue()) ) {
			post.DopNew = form.findById('luLpuUnitTypeDop_id').getRawValue();
		}
		else {
			if (form.findById('luLpuUnitTypeDop_id').getStore().findBy(function(rec) { return rec.get('LpuUnitTypeDop_Name') == form.findById('luLpuUnitTypeDop_id').getRawValue(); }) >= 0 )
			{
				post.DopNew = '';
			}
			else
			{
				post.DopNew = form.findById('luLpuUnitTypeDop_id').getRawValue();
				form.findById('luLpuUnitTypeDop_id').setValue('');
			}
		}

		if (options.ignoreFRMOUnitCheck) {
			post.ignoreFRMOUnitCheck = 1;
		}

		/*
		if (getRegionNick() == 'ufa')
		{
			if (form.findById('luLpuUnitSet_id').getValue() == null)
			{
				post.LpuUnitSet = form.findById('luLpuUnitSet_id').getRawValue();
			}
			else
			{
				if (form.findById('luLpuUnitSet_id').getStore().findBy(function(rec) { return rec.get('LpuUnitSet_Code') == form.findById('luLpuUnitSet_id').getRawValue(); }) >= 0 )
				{
					post.LpuUnitSet_id = '';
				}
				else
				{
					post.LpuUnitSet_id = form.findById('luLpuUnitSet_id').getRawValue();
					form.findById('luLpuUnitSet_id').setValue('');
				}
			}
		}
		*/
		form.getForm().submit(
			{
				params: post,
				/*
				{
					LpuUnitType_id: form.findById('luLpuUnitType_id').getValue()
				},
				*/
				failure: function(result_form, action) 
				{
					if (action.result)
					{
						if (action.result.Error_Msg && action.result.Error_Msg == 'YesNo') {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										options.ignoreFRMOUnitCheck = true;
										window.submit(is_edit_org_head, options);
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: action.result.Alert_Msg,
								title: langs('Продолжить сохранение?')
							});
						}
						else if (action.result.Error_Code)
						{
							Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
						}
						else
						{
							//Ext.Msg.alert('Ошибка #100003', 'При сохранении произошла ошибка!');
						}
					}
					loadMask.hide();
				},
				success: function(result_form, action) 
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.LpuUnit_id)
						{
							form.getForm().findField('LpuUnit_id').setValue(action.result.LpuUnit_id);
							if ( is_edit_org_head && is_edit_org_head == true )
							{
								window.action = 'edit';
								window.LpuUnit_id = action.result.LpuUnit_id;
								window.openOrgHeadEditWindow('add');
								return true;
							}
							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuUnit_id);
						}
						else
							Ext.Msg.alert(langs('Ошибка #100004'), langs('При сохранении произошла ошибка'));
					}
					else
						Ext.Msg.alert(langs('Ошибка #100005'), langs('При сохранении произошла ошибка'));
				}
			});
	},
	openOrgHeadEditWindow: function(action) {
		var current_window = this;

		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}
		else if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( action == 'add' && getWnd('swOrgHeadEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования руководства уже открыто'));
			return false;
		}

		// предварительно надо сохранить
		if ( this.action == 'add' ) {
			this.doSave(true);
			return;
		}

		var grid = this.findById('luLpuUnit_OrgHeadGrid').getGrid();

		var params = new Object();
		params.LpuUnit_id = this.LpuUnit_id;
		params.action = action;
        params.Lpu_id = this.Lpu_id;
		params.callback = function(data) {
			if ( !data ) {
				return false;
			}
			var grid = current_window.findById('luLpuUnit_OrgHeadGrid').getGrid();
			var record = grid.getStore().getById(data.OrgHead_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !(grid.getStore().getAt(0).get('OrgHead_id') > 0) ) {
					grid.getStore().removeAll();
				}
				grid.getStore().loadData([ data ], true);
			}
			else {
				var head = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					head.push(key);
				});

				for ( i = 0; i < head.length; i++ ) {
					record.set(head[i], data[head[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);

		if ( action == 'add' ) {
			params.OrgHead_id = 0;
			params.onHide = function() {
				current_window.findById('luLpuUnit_OrgHeadGrid').focus(true, 100);
			};
			// ищем человека и передаем его
			if (getWnd('swPersonSearchWindow').isVisible())
			{
				current_window.showMessage(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
				return false;
			}
			getWnd('swPersonSearchWindow').show({
				onClose: function() {
					//current_window.refreshPersonDispSearchGrid();
				},
				onSelect: function(person_data) {
					getWnd('swPersonSearchWindow').hide();
					params.Person_id = person_data.Person_id;
					getWnd('swOrgHeadEditWindow').show(params);
				},
				searchMode: 'all'
			});
		}
		else
		{
			if ( !grid.getSelectionModel().getSelected() )
				return;
			params.OrgHead_id = grid.getSelectionModel().getSelected().get('OrgHead_id');
			params.Person_id = grid.getSelectionModel().getSelected().get('Person_id');
			params.onHide = function() {
				current_window.findById('luLpuUnit_OrgHeadGrid').focus(true, 100);
			};
			getWnd('swOrgHeadEditWindow').show(params);
		}
	},
	changeFRMOType: function(n){
		var
			base_form = this.findById('LpuUnitEditFormPanel').getForm(),
			index,
			LUPcombo = base_form.findField('LpuUnitProfile_fid'),
			LpuUnitProfile_fid = LUPcombo.getValue(),
			UnitDepartTypeArray = [ n ];

		LUPcombo.setContainerVisible(!Ext.isEmpty(n));
		LUPcombo.setAllowBlank(Ext.isEmpty(n));
		LUPcombo.clearValue();

		if ( !Ext.isEmpty(n) && (n == 1 || n == 2) ) {
			UnitDepartTypeArray.push(7);
		}

		LUPcombo.lastQuery = '';
		LUPcombo.getStore().filterBy(function(rec) {
			return (rec.get('UnitDepartType_id').inlist(UnitDepartTypeArray));
		});

		if ( !Ext.isEmpty(LpuUnitProfile_fid) ) {
			index = LUPcombo.getStore().findBy(function(rec) {
				return (rec.get(LUPcombo.valueField) == LpuUnitProfile_fid);
			});

			if ( index >= 0 ) {
				LUPcombo.setValue(LpuUnitProfile_fid);
			}
		}
	},
	changeLpuUnitType: function(n, code, haveTypeFRMO){
		var base_form = this.findById('LpuUnitEditFormPanel').getForm();
		var LpuUnit_IsNotFRMO = base_form.findField('LpuUnit_IsNotFRMO').getValue();

		if ( LpuUnit_IsNotFRMO == true ) {
			return false;
		}

		// как все нормальные люди взаимодействуем с кодом, но это же 2-ка, здесь нет findRecordByValue
		if(code != 'code')
		{
			var combo = Ext.getCmp('luLpuUnitType_id');
			var index = combo.getStore().findBy(function (rec) {
				if ( rec.get('LpuUnitType_id') == n ) {
					n = String(rec.get('LpuUnitType_Code'));
					return true;
				}
				else {
					return false;
				}
			});
		}

		var input,value = '';
		// Для круглосуточного стационара появляются поля вызов СМП, для Поликлиники - вызов на дом
		var arrCt = [[2, 'LpuUnit_isCMPyn'], [1,'LpuUnit_isHomeVisityn']];
		Ext.each(arrCt, function(el){
			input = Ext.getCmp(el[1]).setContainerVisible(n == el[0]);
			if(n != el[0])
				input.reset();
			input.setAllowBlank(!(n == el[0]));
		});

		var FRMOType = Ext.getCmp('luUnitDepartType_fid');
		var store = FRMOType.getStore();

		if(n == '6') // Параклиника
		{
			FRMOType.clearValue();
			FRMOType.lastQuery = '';
			store.filter('UnitDepartType_id', new RegExp('^[34]')); // Лабораторно-диагностические, инструментально-диагностический
		}
		else
			store.clearFilter();

		FRMOType.setContainerVisible(n && n != '');
		FRMOType.setAllowBlank(!(n && n != ''));
		switch(n){ // Задаем значение "Типа ФРМО" по коду LpuUnitType_Code типа группы отделений #109901
			case '1':
				value = 1;
				break;
			case '2':
			case '3':
			case '4':
			case '5':
				value = 2;
				break;
			case '9':
			case '10':
				value = 6;
				break;
			case '12':
				value = 5;
				break;
		}

		if(!Ext.isEmpty(haveTypeFRMO))
			value = haveTypeFRMO;

		if(value != '')
		{
			FRMOType.setValue(value);
			FRMOType.fireEvent('change', FRMOType, value)
		}
	},
	controlOfTheFieldluLpuUnit: function(){
		var form = this.findById('LpuUnitEditFormPanel');

		var LLBPass = form.findById('luLpuUnit_Name').getValue(); // поле "Наименование"
		// удалим пробелы в начале и конце строки
		LLBPass = LLBPass.trim();
		form.findById('luLpuUnit_Name').setValue(LLBPass);
		
		if(!LLBPass) return false; 
		var rxArr = [
			{
				rx: /([^А-Яа-яёЁ\d\s-,№()"«»\.])|(^\([^)(]*\))|(^"[^"]*")|(^«[^«]*»)|(№.*№)/,
				error_msg: 'В наименовании допустимо использование только следующих знаков: буквы (кириллица), цифры, круглые парные скобки "(" и ")", дефис, пробел, точка, запятая, парные кавычки типов " " и « » и один знак "№"».',
				res: true
			},
			{
				rx: /^[А-Яа-я0-9][\d\D]+/,
				//rx: /^[А-Яа-я0-9]*[\s-][А-Яа-я\s-,№("«\.]{2,}/,
				//rx:/^[А-Яа-я0-9]{2,}$|^[А-Яа-я0-9]*[\s-][А-Яа-я\s-,№("«\.]{2,}/,
				//error_msg: 'Наименование может начинаться только на букву или цифру, за которой должны следовать либо пробел и слово, либо дефис и слово. Словом считается любая последовательность кириллических букв более двух знаков',
				error_msg: 'Введено некорректное наименование. Используйте букву или цифру в начале наименования',
				res: false
			},
			{
				rx: /(--)|(\s\s)|(\.\.)/,
				error_msg: 'В наименовании не должно быть более одного пробела, точки или дефиса подряд',
				res: true
			},
			{
				rx: /\s-/,
				error_msg: 'В наименовании не должны располагаться подряд пробел и дефис',
				res: true
			},
			{
				rx: /(№[^\s\d])|(№\s\D)/,
				error_msg: 'В наименовании после знака номера "№" допустимы либо цифра, либо один пробел и цифра',
				res: true
			},
			{
				rx: /\([\(-,:\.\s]/,
				error_msg: 'В наименовании, после открывающейся скобки "(", должны следовать цифра или слово. Не допускается использование после скобки "(" другой скобки, дефиса, запятой или пробела.',
				res: true
			},
			{
				rx: /[^\s]\(/,
				error_msg: 'В наименовании обязательно использование пробела перед открывающейся скобкой "(".',
				res: true
			},
			{
				rx: /\)[^\s]/,
				error_msg: 'В наименовании обязательно использование пробела после закрывающейся скобки ")", расположенной не в конце',
				res: true
			},
			{
				rx: /(\).)$/,
				error_msg: 'В конце наименования после закрывающейся скобки ")" недопустимы иные символы',
				res: true
			},
			{
				rx: /\s,/,
				error_msg: 'Перед запятой недопустим пробел',
				res: true
			},
			{
				rx: /,[^\s]/,
				error_msg: 'После запятой обязателен пробел',
				res: true
			},
			{
				rx: /(».)|(".)$/,
				error_msg: 'После закрывающейся кавычки в конце наименования недопустимы иные символы',
				res: true
			},
			{
				rx: /("[^А-Яа-я0-9].*")|(«[^А-Яа-я0-9].*»)/,
				error_msg: 'После открывающейся кавычки должны следовать цифра или слово и недопустимы: другая кавычка, дефис, запятая, скобка, пробел',
				res: true
			},
			{
				rx: /(".*["\s,\)\(-]")|(«.*[«\s,\)\(-]»)/,
				error_msg: 'Перед закрывающей кавычкой недопустимы кавычки, дефис, запятая, скобка, пробел',
				res: true
			},
			
		];
	
		for (i = 0; i < rxArr.length; i++) {
			var elem = rxArr[i];
			if( elem.rx.test(LLBPass) == elem.res){
				return elem.error_msg;
			}
		}

		function quotation(LLBPass){
			//парные скобки, кавычки
			var opening_parenthesis = LLBPass.match(/\(/g);
			var closing_parenthesis = LLBPass.match(/\)/g);
			var quotation_mark = LLBPass.match(/\"/g);
			var opening_quotation = LLBPass.match(/\«/g);
			var closing_quotation = LLBPass.match(/\»/g);
			if( quotation_mark && quotation_mark.length%2 ){
				//не четные
				return false;
			}else if(
				(opening_parenthesis && closing_parenthesis && opening_parenthesis.length!=closing_parenthesis.length)
				|| (opening_parenthesis && !closing_parenthesis)
				|| (!opening_parenthesis && closing_parenthesis)
			){
				return false;
			}else if(
				(opening_quotation && closing_quotation && opening_quotation.length!=closing_quotation.length)
				|| (opening_quotation && !closing_quotation)
				|| (!opening_quotation && closing_quotation)
			){
				return false;
			}else{
				return true;
			}
		}

		if( !quotation(LLBPass) ){
			return 'В наименовании допустимо использование только следующих знаков: буквы (кириллица), цифры, круглые парные скобки "(" и ")", дефис, пробел, запятая, парные кавычки типов " " и « » и один знак "№"».';
		}else{
			return false;
		}
    },
	initComponent: function() 
	{
		var wnd = this;

		var isKZ = (getRegionNick() == 'kz' || getGlobalOptions().region.nick.inlist([ 'kz' ]));
		var isUfa = (getRegionNick() == 'ufa' || getGlobalOptions().region.nick.inlist([ 'ufa' ]));

		this.MainPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyStyle:'width:100%;height:100%;background:#DFE8F6;padding:0px;',
			border: false,
			frame: false,
			id:'LpuUnitEditFormPanel',
			layout: 'border',
			region: 'center',
			items: [{
				xtype: 'tabpanel',
				enableTabScroll: true,
				region: 'center',
				id: 'LpuUnitEditFormTabPanel',
				activeTab: 0,
				layoutOnTabChange: true,
				defaults: {bodyStyle: 'width:100%;height:100%;background:#DFE8F6;padding:4px;'}, //
				listeners: {
					tabchange: function (tab, panel) {
						switch (panel.id) {
							case 'luef_main_tab':
								//this.ownerCt.findById('lsLpuSection_Code').focus(true);
								break;

							case 'luef_frmo_tab':
								//this.ownerCt.findById('lsLpuSection_Descr').focus(true);
								break;
						}
					}
				},
				items: [{
					title: langs('Основные данные'),
					layout: 'form',
					bodyStyle: 'padding: 4px; overflow: auto; background: #DFE8F6;',
					id: 'luef_main_tab',
					labelWidth: 170,
					items: [{
				name: 'LpuUnit_id',
				xtype: 'hidden',
				id: 'luLpuUnit_id'
					}, {
				name: 'ChildsCount',
				xtype: 'hidden',
				id: 'luChildsCount'
					}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: langs('Период действия'),
						labelWidth: 160,
						items: [{
						fieldLabel: langs('Начало'),
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						id: 'luLpuUnit_begDate',
						name: 'LpuUnit_begDate',
						listeners:{
							'change': function (combo, newValue, oldValue) {
								if ( !isUfa ) {
									return false;
								}

								var form = this.findById('LpuUnitEditFormPanel').getForm();
								form.findField('LpuUnit_endDate').fireEvent('change', form.findField('LpuUnit_endDate'), form.findField('LpuUnit_endDate').getValue());
							}.createDelegate(this)
						}
					}, {
						fieldLabel : langs('Окончание'),
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						id: 'luLpuUnit_endDate',
						name: 'LpuUnit_endDate',
						listeners:{
							'change': function (field, newValue, oldValue) {
								if ( !isUfa ) {
									return false;
								}

								var form = this.findById('LpuUnitEditFormPanel').getForm();

								var
									begDate = form.findField('LpuUnit_begDate').getValue(),
									index,
									LpuUnitSet_id = form.findField('LpuUnitSet_id').getValue();

								// Фильтруем список кодов подразделений
								// https://redmine.swan.perm.ru/issues/26892
								form.findField('LpuUnitSet_id').clearValue();
								form.findField('LpuUnitSet_id').getStore().clearFilter();
								form.findField('LpuUnitSet_id').lastQuery = '';

								if ( !Ext.isEmpty(begDate) || !Ext.isEmpty(newValue) ) {
									form.findField('LpuUnitSet_id').getStore().filterBy(function(rec) {
										if ( Ext.isEmpty(rec.get('LpuUnitSet_begDate')) && Ext.isEmpty(rec.get('LpuUnitSet_endDate')) ) {
											return true;
										}

										if ( !Ext.isEmpty(begDate) && Ext.isEmpty(newValue) ) {
											return (
												(Ext.isEmpty(rec.get('LpuUnitSet_begDate')) || rec.get('LpuUnitSet_begDate') <= begDate)
												&& (Ext.isEmpty(rec.get('LpuUnitSet_endDate')) || rec.get('LpuUnitSet_endDate') > begDate)
											);
											} else if (Ext.isEmpty(begDate) && !Ext.isEmpty(newValue)) {
											return (
												(Ext.isEmpty(rec.get('LpuUnitSet_begDate')) || rec.get('LpuUnitSet_begDate') <= newValue)
												&& (Ext.isEmpty(rec.get('LpuUnitSet_endDate')) || rec.get('LpuUnitSet_endDate') > newValue)
											);
											} else {
											return (
												(Ext.isEmpty(rec.get('LpuUnitSet_begDate')) || (rec.get('LpuUnitSet_begDate') <= newValue && rec.get('LpuUnitSet_begDate') <= begDate))
												&& (Ext.isEmpty(rec.get('LpuUnitSet_endDate')) || (rec.get('LpuUnitSet_endDate') > newValue && rec.get('LpuUnitSet_endDate') > begDate))
											);
										}
									});
								}

								index = form.findField('LpuUnitSet_id').getStore().findBy(function(rec) {
									return (rec.get('LpuUnitSet_id') == LpuUnitSet_id);
								});
								
								if ( index >= 0 ) {
									form.findField('LpuUnitSet_id').setValue(LpuUnitSet_id);
								}
							}.createDelegate(this)
						}
						}]
					}, {
						anchor: '95%',
				name: 'LpuBuilding_id',
				tabIndex: 2450,
				xtype: 'swlpubuildingcombo',
				id: 'luLpuBuilding_id',
				allowBlank:false
					}, {
				name: 'Lpu_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'luLpu_id'
					}, {
				layout: 'form',
				bodyStyle: 'overflow: auto; background: #DFE8F6; border: none;',
				hidden: getRegionNick() != 'ufa',
				items: [{
					fieldLabel: langs('Работает в ОМС'),
					name: 'LpuUnit_IsOMS',
					tabIndex: 2450,
					xtype: 'checkbox',
					id: 'luLpuUnit_IsOMS',
					listeners: {
						'check': function(checkbox, value) {
							var base_form = this.findById('LpuUnitEditFormPanel').getForm();
							base_form.findField('LpuUnitSet_id').setAllowBlank(!value);
						}.createDelegate(this)
					}
				}]
					}, {
						fieldLabel: langs('Код'),
						maskRe: /\d/,
						autoCreate: {tag: "input", size:8, maxLength: "8", autocomplete: "off"},
						tabIndex: 2451,
						name: 'LpuUnit_Code',
						xtype: 'textfield',
						id: 'luLpuUnit_Code',
						allowBlank:false
					}, {
						anchor: '95%',
						allowBlank: false,
						fieldLabel: langs('Код подр. ТФОМС'),
						forceSelection: false,
						hiddenName: 'LpuUnitSet_id',
						id: 'luLpuUnitSet_id',
						tabIndex:2451,
						xtype: 'swlpuunitsetcombo'
					}, {
						anchor: '95%',
				tabIndex: 2452,
				fieldLabel : langs('Наименование'),
				name: 'LpuUnit_Name',
				xtype: 'textfield',
				id: 'luLpuUnit_Name',
				allowBlank:false
					}, {
						anchor: '95%',
				tabIndex:2453,
				disabled: false,
				name: 'LpuUnitType_id',
				xtype: 'swlpuunittypecombo',
				id: 'luLpuUnitType_id',
				allowBlank:false,
				isLab: false,
				listeners: {
					select: function(ct, rec) {
								if (!isKZ) {
							var form = Ext.getCmp('LpuUnitEditForm');
							var n = '';
							if(!Ext.isEmpty(rec.get('LpuUnitType_Code')))
								n = String(rec.get('LpuUnitType_Code'));
							form.changeLpuUnitType(n, 'code');
						}
					},
					expand: function(){
						if(this.isLab === true){
							var cmpMedServiceType = this;
							var typesInLab = [3,4,5];
							/*
							 * Разрешенные типы для лаборатории
							 * 3 - Параклиника
							 * 4 - Администрация
							 * 5 - Другое
							 */
							cmpMedServiceType.getStore().filterBy(function (rec) {
								return rec.get('LpuUnitType_id').inlist(typesInLab);
							});
						}
					},
					change: function(combo, n, o){
								if (!isKZ) {
							var form = Ext.getCmp('LpuUnitEditForm');
							form.changeLpuUnitType(n, 'id');
						}
					}
				}
					}, {
						anchor: '95%',
				xtype: 'swlpuunittypedopcombo',
				tabIndex:2454,
				hiddenName: 'LpuUnitTypeDop_id',
				id: 'luLpuUnitTypeDop_id',
				allowBlank: true,
				forceSelection: false
			}, {
						xtype: 'checkbox',
						height: 22,
						hideLabel: true,
						hidden: isKZ,
						tabIndex: 2457,
						name: 'LpuUnit_isPallCC',
						id: 'luLpuUnit_isPallCC',
						boxLabel: langs('Центр паллиативной медицинской помощи (ПМП)')
			}, {
						xtype: 'fieldset',
						autoHeight: true,
						title: langs('Электронная регистратура'),
						labelWidth: 160,
						items: [{
							width: 470,
							tabIndex: 2455,
							fieldLabel: langs('Телефоны'),
							name: 'LpuUnit_Phone',
							xtype: 'textfield',
							id: 'luLpuUnit_Phone'
						}, {
							width: 470,
							tabIndex: 2456,
							fieldLabel: langs('Примечание'),
							name: 'LpuUnit_Descr',
							xtype: 'textarea',
							autoCreate: {tag: "textarea", autocomplete: "off", maxlength: 200},
							id: 'luLpuUnit_Descr'
						}, {
							xtype: 'checkbox',
							height: 22,
							hideLabel: true,
							hidden: !(getGlobalOptions().groups && ((getGlobalOptions().groups.toString().indexOf('SuperAdmin') != -1)
								|| ((getGlobalOptions().region.nick == 'kareliya') && (getGlobalOptions().groups.toString().indexOf('LpuAdmin') != -1)))),
							tabIndex: 2457,
							name: 'LpuUnit_IsEnabled',
							id: 'luLpuUnit_IsEnabled',
							boxLabel: langs('Включить запись операторами')
						}, {
							width: 470,
							autoCreate: {tag: "input", maxLength: "100", autocomplete: "off"},
							tabIndex: 2458,
							fieldLabel: 'E-mail',
							name: 'LpuUnit_Email',
							xtype: 'textfield',
							id: 'luLpuUnit_Email'
						}, {
							width: 470,
							autoCreate: {tag: "input", maxLength: "100", autocomplete: "off"},
							tabIndex: 2458,
							fieldLabel: langs('IP-адрес'),
							name: 'LpuUnit_IP',
							xtype: 'textfield',
							id: 'luLpuUnit_IP'
						}]
					},
					new sw.Promed.ViewFrame({
						actions: [
							{
								name: 'action_add', handler: function () {
									this.openOrgHeadEditWindow('add');
								}.createDelegate(this)
							},
							{
								name: 'action_edit', handler: function () {
									this.openOrgHeadEditWindow('edit');
								}.createDelegate(this)
							},
							{
								name: 'action_view', handler: function () {
									this.openOrgHeadEditWindow('view');
								}.createDelegate(this)
							}
						],
						autoExpandColumn: 'autoexpand',
						autoExpandMin: 150,
						autoLoadData: false,
						object: 'OrgHead',
						dataUrl: '/?c=Org&m=loadOrgHeadGrid',
						focusOn: {
							name: 'luOk',
							type: 'button'
						},
						focusPrev: {
							name: 'luOk',
							type: 'button'
						},
						height: 170,
						id: 'luLpuUnit_OrgHeadGrid',
						//pageSize: 100,
						paging: false,
						region: 'center',
						//root: 'data',
						stringfields: [
							{name: 'OrgHead_id', type: 'int', header: 'ID', key: true},
							{name: 'Person_id', type: 'int', hidden: true},
							{name: 'OrgHeadPerson_Fio', type: 'string', header: langs('ФИО'), width: 200},
							{name: 'OrgHeadPost_Name', type: 'string', header: langs('Должность'), width: 150},
							{name: 'OrgHead_Phone', type: 'string', header: langs('Телефон(ы)'), width: 110},
							{name: 'OrgHead_Fax', type: 'string', header: langs('Факс'), width: 110}
						],
						title: langs('Руководство'),
						toolbar: true
					})]
				}, {
					title: langs('ФРМО'),
					layout: 'form',
					bodyStyle: 'padding: 4px; overflow: auto; background: #DFE8F6;',
					id: 'luef_frmo_tab',
					labelWidth: 170,
					items: [{
						fieldLabel: langs('Не передавать на ФРМО'),
						name: 'LpuUnit_IsNotFRMO',
						xtype: 'checkbox',
						id: 'luLpuUnit_IsNotFRMO',
						listeners: {
							'check': function (checkbox, value) {
								var base_form = this.MainPanel.getForm();

								base_form.findField('FRMOUnit_id').setContainerVisible(!value);
								base_form.findField('LpuUnit_FRMOUnitID').setContainerVisible(!value);
								base_form.findField('UnitDepartType_fid').setContainerVisible(!value && !Ext.isEmpty(base_form.findField('LpuUnitType_id').getValue()));
								base_form.findField('LpuUnitProfile_fid').setContainerVisible(!value && !Ext.isEmpty(base_form.findField('LpuUnitType_id').getValue()));
								base_form.findField('LpuUnit_isStandalone').setContainerVisible(!value);
								base_form.findField('LpuBuildingPass_id').setContainerVisible(!value && base_form.findField('LpuUnit_isStandalone').getValue() == 2);
								base_form.findField('LpuUnit_isHomeVisit').setContainerVisible(!value && base_form.findField('LpuUnitType_id').getValue() == 2);
								base_form.findField('LpuUnit_isCMP').setContainerVisible(!value && base_form.findField('LpuUnitType_id').getValue() == 1);

								if ( value ) {
									base_form.findField('FRMOUnit_id').clearValue();
									base_form.findField('LpuUnit_FRMOUnitID').setValue('');
									base_form.findField('UnitDepartType_fid').clearValue();
									base_form.findField('LpuUnitProfile_fid').clearValue();
									base_form.findField('LpuUnit_isStandalone').clearValue();
									base_form.findField('LpuBuildingPass_id').clearValue();
									base_form.findField('LpuUnit_isHomeVisit').clearValue();
									base_form.findField('LpuUnit_isCMP').clearValue();
								}

								base_form.findField('UnitDepartType_fid').setAllowBlank(value || !Ext.isEmpty(base_form.findField('LpuUnitType_id').getValue()));
								base_form.findField('LpuUnitProfile_fid').setAllowBlank(value || !Ext.isEmpty(base_form.findField('LpuUnitProfile_fid').getValue()));
								base_form.findField('LpuUnit_isStandalone').setAllowBlank(value);
								base_form.findField('LpuBuildingPass_id').setAllowBlank(value || base_form.findField('LpuUnit_isStandalone').getValue() != 2);
								base_form.findField('LpuUnit_isHomeVisit').setAllowBlank(value || base_form.findField('LpuUnitType_id').getValue() != 2);
								base_form.findField('LpuUnit_isCMP').setAllowBlank(value || base_form.findField('LpuUnitType_id').getValue() != 1);
							}.createDelegate(this)
						}
					}, {
						anchor: '95%',
				displayField: 'FRMOUnit_Display',
				fieldLabel: langs('ФРМО справочник структурных подразделений'),
				hiddenName: 'FRMOUnit_id',
				id: 'luFRMOUnit_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					}.createDelegate(this),
					'select': function(combo, record, idx) {
						var base_form = this.findById('LpuUnitEditFormPanel').getForm();

						if ( typeof record == 'object' && !Ext.isEmpty(record.get('FRMOUnit_id')) ) {
									base_form.findField('LpuUnit_FRMOUnitID').setValue(record.get('FRMOUnit_OID'));
							base_form.findField('UnitDepartType_fid').setValue(record.get('FRMOUnit_TypeId'));
							base_form.findField('UnitDepartType_fid').fireEvent('change', base_form.findField('UnitDepartType_fid'), base_form.findField('UnitDepartType_fid').getValue());
							base_form.findField('LpuUnitProfile_fid').setValue(record.get('FRMOUnit_KindId'));
						}
								else {
									base_form.findField('LpuUnit_FRMOUnitID').setValue('');
								}
					}.createDelegate(this)
				},
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{name: 'FRMOUnit_id', type: 'int'},
						{name: 'Lpu_id', type: 'int'},
						{name: 'FRMOUnit_MOID', type: 'string'},
						{name: 'FRMOUnit_OID', type: 'string'},
						{name: 'FRMOUnit_Name', type: 'string'},
						{name: 'FRMOUnit_TypeId', type: 'int'},
						{name: 'FRMOUnit_TypeName', type: 'string'},
						{name: 'FRMOUnit_KindId', type: 'int'},
						{name: 'FRMOUnit_KindName', type: 'string'},
						{name: 'FRMOUnit_Address', type: 'string'},
						{name: 'FRMOUnit_LiquidationDate', type: 'date'},
								{
									name: 'FRMOUnit_Display',
							convert: function(val, row) {
								return row.FRMOUnit_Name + ' (' + row.FRMOUnit_OID + ')';
							}	
						}
					],
					key: 'FRMOUnit_id',
					sortInfo: {
						field: 'FRMOUnit_Name'
					},
					url: C_FRMOUNIT_LIST
				}),
						onTrigger2Click: function() {
							if ( !this.disabled ) {
								this.clearValue();
								this.fireEvent('change', this);
							}
						},
				tabIndex: 2454.5,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{FRMOUnit_Display}',
					'</div></tpl>'
				),
				valueField: 'FRMOUnit_id',
				minChars: 0,
				xtype: 'swbaseremotecombo'
			}, {
						anchor: '95%',
						fieldLabel: 'ОИД ФРМО Структурного подразделения',
						readOnly: true,
						name: 'LpuUnit_FRMOUnitID',
						xtype: 'textfield'
					}, {
				allowBlank: true,
						anchor: '95%',
				comboSubject: 'UnitDepartType',
				fieldLabel: langs('Тип (ФРМО)'),
				hiddenName: 'UnitDepartType_fid',
				id: 'luUnitDepartType_fid',
				lastQuery: '',
				listeners: {
					'render': function(ct){
						if ( isKZ ) {
							ct.setContainerVisible(false);
						}
					},
					'select': function(ct, rec) {
						if ( !isKZ ) {
							var form = Ext.getCmp('LpuUnitEditForm');
							form.changeFRMOType(typeof rec == 'object' ? rec.get('UnitDepartType_id') : null);
						}
					},
					'change': function(cmp, n, o) {
						if ( !isKZ ) {
							var index = cmp.getStore().findBy(function(rec) {
								return (rec.get(cmp.valueField) == n);
							});
							cmp.fireEvent('select', cmp, cmp.getStore().getAt(index), index);
						}
					}
				},
				onLoadStore: function() {
					if ( getRegionNick().inlist([ 'kz', 'vologda' ]) ) {
						return false;
					}

					this.getStore().each(function(rec) {
						if (rec.get('UnitDepartType_Code') == 7) {
							this.getStore().remove(rec);
						}
					}.createDelegate(this));
				},
				xtype: 'swcommonsprcombo'
					}, {
						anchor: '95%',
				comboSubject: 'LpuUnitProfile',
				fieldLabel: langs('Профиль (ФРМО)'),
				hiddenName: 'LpuUnitProfile_fid',
				id: 'luLpuUnitProfile_fid',
				listeners: {
					'render': function(ct){
						if ( isKZ ) {
							ct.setContainerVisible(false);
						}
					}
				},
				moreFields: [
					{ name: 'LpuUnitProfile_pid', mapping: 'LpuUnitProfile_pid', type: 'int' },
					{ name: 'UnitDepartType_id', mapping: 'UnitDepartType_id', type: 'int' },
					{ name: 'LpuUnitProfile_Form30', mapping: 'LpuUnitProfile_Form30', type: 'string' }
				],
				xtype: 'swcommonsprcombo'
					}, {
				allowBlank: isKZ,
				xtype: 'combo',
				fieldLabel: 'Обособленность',
				hidden: isKZ,
				hiddenName: 'LpuUnit_isStandalone',
				name: 'LpuUnit_isStandalone',
						id: 'luLpuUnit_isStandalone',
				labelAlign: 'left',
				editable: false,
				mode:'local',
				width: 50,
				triggerAction : 'all',
				store:new Ext.data.SimpleStore(  {
					fields: ['answer','answer_id'],
					data: [
						[langs('Да'), 2],
						[langs('Нет'), 1]
					]
				}),
				listeners: {
					render: function(ct){
						if(isKZ)
							ct.setContainerVisible(false);
					},
					select: function(cmp, rec){
						if(isKZ)
							return false;
						var n = '';
						if(!Ext.isEmpty(rec.get('LpuBuildingPass_id')))
							n = String(rec.get('LpuBuildingPass_id'));
						var LPEWcombo = Ext.getCmp('LPEW_LpuBuildingPass_id');
						LPEWcombo.setContainerVisible(n == 2);
						LPEWcombo.setAllowBlank(n != 2);
						if(n != 2)
							LPEWcombo.reset();
					},
					change: function(cmp, n, o){
						if(isKZ)
							return false;
						var LPEWcombo = Ext.getCmp('LPEW_LpuBuildingPass_id');
						LPEWcombo.setContainerVisible((n == 2));
						LPEWcombo.setAllowBlank(n != 2);
						if(n != 2)
							LPEWcombo.reset();
					}
				},
				displayField:'answer',
				valueField:'answer_id',
				tpl: '<tpl for="."><div class="x-combo-list-item">'+
					'{answer} '+ '&nbsp;' +
					'</div></tpl>'
					}, {
				name: 'LpuBuildingPass_id',
				hiddenName: 'LpuBuildingPass_id',
				id: 'LPEW_LpuBuildingPass_id',
				hidden: isKZ,
				hideEmptyRow: true,
				ignoreIsEmpty: true,
				tabIndex: 1105,
						anchor: '95%',
				allowBlank: isKZ,
				xtype: 'swLpuBuildingPasscombo',
				listeners: {
					render: function(ct){
						if(isKZ)
							ct.setContainerVisible(false);
					}
				}
					}, {
				allowBlank: isKZ,
				xtype: 'combo',
				hidden: isKZ,
				fieldLabel: 'Прием на дому',
				name: 'LpuUnit_isHomeVisit',
				hiddenName: 'LpuUnit_isHomeVisit',
				id: 'LpuUnit_isHomeVisityn',
				labelAlign: 'left',
				editable: false,
				//disabled: true,
				mode:'local',
				width: 50,
				triggerAction : 'all',
				store:new Ext.data.SimpleStore(  {
					fields: ['answer','answer_id'],
					data: [
						[langs('Да'), 2],
						[langs('Нет'), 1]
					]
				}),
				displayField:'answer',
				valueField:'answer_id',
				tpl: '<tpl for="."><div class="x-combo-list-item">'+
					'{answer} '+ '&nbsp;' +
					'</div></tpl>',
				listeners: {
					render: function(ct){
						if(isKZ)
							ct.setContainerVisible(false);
					}
				}
					}, {
				allowBlank: isKZ,
				xtype: 'combo',
				fieldLabel: 'Прием скорой помощи',
				hiddenName: 'LpuUnit_isCMP',
				name: 'LpuUnit_isCMP',
				id: 'LpuUnit_isCMPyn',
				labelAlign: 'left',
				editable: false,
				mode:'local',
				width: 50,
				triggerAction : 'all',
				store:new Ext.data.SimpleStore(  {
					fields: ['answer','answer_id'],
					data: [
						[langs('Да'), 2],
						[langs('Нет'), 1]
					]
				}),
				displayField:'answer',
				valueField:'answer_id',
				tpl: '<tpl for="."><div class="x-combo-list-item">'+
					'{answer} '+ '&nbsp;' +
					'</div></tpl>',
				listeners: {
					render: function(ct){
						if(isKZ)
							ct.setContainerVisible(false);
					}
				}
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Lpu_id' },
				{ name: 'LpuBuilding_id' },
				{ name: 'LpuUnit_begDate' },
				{ name: 'LpuUnit_endDate' },
				{ name: 'LpuUnit_id' },
				{ name: 'LpuUnit_Code' },
				{ name: 'LpuUnit_Name' },
				{ name: 'LpuUnitType_id' },
				{ name: 'LpuUnitTypeDop_id' },
				{ name: 'UnitDepartType_fid' },
				{ name: 'LpuUnitProfile_fid' },
				{ name: 'LpuUnit_isStandalone' },
				{ name: 'LpuUnit_isCMP' },
				{ name: 'LpuUnit_isHomeVisit' },
				{ name: 'LpuBuildingPass_id' },
				{ name: 'LpuUnitSet_id' },
				{ name: 'LpuUnit_Phone' },
				{ name: 'LpuUnit_Descr' },
				{ name: 'LpuUnit_IsEnabled' },
				{ name: 'LpuUnit_isPallCC' },
				{ name: 'LpuUnit_IsNotFRMO' },
				{ name: 'LpuUnit_IsOMS' },
				{ name: 'LpuUnit_Email' },
				{ name: 'LpuUnit_IP' },
				{ name: 'FRMOUnit_id' },
				{ name: 'LpuUnit_FRMOUnitID' },
				{ name: 'ChildsCount' }
			]),
			url: C_LPUUNIT_SAVE
		});
		
		Ext.apply(this, {
			xtype: 'panel',
			bodyStyle:'padding:0px; overflow: auto;',
			border: false,
			items: [
				this.MainPanel
			]
		});

		sw.Promed.swLpuUnitEditForm.superclass.initComponent.apply(this, arguments);

		this.findById('luLpuUnitSet_id').setBaseFilter(function(rec) {
			var
				begDate = this.findById('luLpuUnit_begDate').getValue(),
				endDate = this.findById('luLpuUnit_endDate').getValue();

			if ( !Ext.isEmpty(begDate) || !Ext.isEmpty(endDate) ) {
				if ( Ext.isEmpty(rec.get('LpuUnitSet_begDate')) && Ext.isEmpty(rec.get('LpuUnitSet_endDate')) ) {
					return true;
				}

				if ( !Ext.isEmpty(begDate) && Ext.isEmpty(endDate) ) {
					return (
						(Ext.isEmpty(rec.get('LpuUnitSet_begDate')) || rec.get('LpuUnitSet_begDate') <= begDate)
						&& (Ext.isEmpty(rec.get('LpuUnitSet_endDate')) || rec.get('LpuUnitSet_endDate') >= begDate)
					);
				}
				else if ( Ext.isEmpty(begDate) && !Ext.isEmpty(endDate) ) {
					return (
						(Ext.isEmpty(rec.get('LpuUnitSet_begDate')) || rec.get('LpuUnitSet_begDate') <= endDate)
						&& (Ext.isEmpty(rec.get('LpuUnitSet_endDate')) || rec.get('LpuUnitSet_endDate') >= endDate)
					);
				}
				else {
					return (
						(Ext.isEmpty(rec.get('LpuUnitSet_begDate')) || (rec.get('LpuUnitSet_begDate') <= endDate && rec.get('LpuUnitSet_begDate') <= begDate))
						&& (Ext.isEmpty(rec.get('LpuUnitSet_endDate')) || (rec.get('LpuUnitSet_endDate') >= endDate && rec.get('LpuUnitSet_endDate') >= begDate))
					);
				}
			}
			
			return true;
		}.createDelegate(this));
	}
});