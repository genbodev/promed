/**
* swNewDrugRequestViewForm - форма просмотра заявки (слегка переделанная копия swNewDrugRequestViewForm).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Andrew Markoff 
* @version      10.2009
* @comment      
*
*
* @input data: 
               
               
*/

sw.Promed.swNewDrugRequestViewForm = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['zayavka_na_lekarstvennyie_sredstva'],
	layout: 'border',
	id: 'NewDrugRequestViewForm',
	maximized: true,
	maximizable: false,
	shim: false,
	buttonAlign : "right",
	buttons:
	[
		{
			text: lang['proverit_limityi'],
			tabIndex: -1,
			tooltip: lang['proverka_prevyisheniya_limitov_po_zayavke'],
			// iconCls: 'save16',
			handler: function()
			{
				this.ownerCt.checkDrugRequestLimitExceed();
			}
		},
		{
			text: '-'
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
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
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
		sw.Promed.swNewDrugRequestViewForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('NewDrugRequestViewForm'), { msg: LOAD_WAIT });
		loadMask.show();
		var form = this;
		form.enableLpuValues(false);
		// Установка фильтров при открытии формы просмотра
		// form.findById('ndrvDrugRequestStatus_id').setValue('');
		
		// Для МинЗдрава скрываем панель фильтров по врачу
		this.isCloseLpu = 1;
		form.findById('ndrvDrugRequestMedPersonalFilter').setVisible(!getGlobalOptions().isMinZdrav);
		if (!getGlobalOptions().isMinZdrav)
		{
			//form.EditLpuPanel.setVisible(true);
			form.findById('ndrvLpuPanel').setVisible(false);
			form.findById('ndrvDrugRequestSetStatus3').setVisible(false);
			form.findById('ndrvLpuUnit_id').getStore().load(
			{
				params:
				{
					Object: 'LpuUnit',
					LpuUnit_id: '',
					LpuUnit_Name: ''
				},
				callback: function()
				{
					//form.findField('ndrvLpuSection_id').setValue(lpusectionId);
				}
			});
		}
		else 
		{
			// По умолчанию текущая ЛПУ, отображается только на Минздраве и он может выбрать
			form.findById('ndrvLpuPanel').setVisible(true);
			form.findById('ndrvDrugRequestSetStatus3').setVisible(true);
			form.findById('ndrvLpu_id').getStore().clearFilter();
			form.findById('ndrvLpu_id').setValue(getGlobalOptions().lpu_id);
			//form.EditLpuPanel.setVisible(false);
		}
		/*
		if (!this.Tree.loader.baseParams.type)
		{
			this.Tree.loader.baseParams.type = 0;
			this.option_type = 0;
		}
		this.Tree.getRootNode().expand();
		//this.Tree.getRootNode().collapse();
		
		// Выбираем первую ноду и эмулируем клик 
		var node = this.Tree.getRootNode();
		if (node)
		{
			node.select();
			this.Tree.fireEvent('click', node);
		}
		//this.Tree.loader.load(this.Tree.root);
		*/
		//form.loadIsOnko();
		// закрытие возможности добавлять заявку для пользователей и админов ЛПУ взависимости от настройки
		form.DrugRequestGrid.setActionDisabled('action_add', (!isSuperAdmin() && (getGlobalOptions().is_create_drugrequest != 1)));
		form.DrugRequestGrid.setActionDisabled('action_edit', (!isSuperAdmin() && (getGlobalOptions().is_create_drugrequest != 1)));
		
		form.DrugRequestGrid.addActions({
			name:'action_actions',
			text:lang['deystviya'],
			menu: [{
				name: 'action_action1',
				text: 'Установить статус заявки «Сформированная»',
				tooltip: 'Установить статус заявки «Сформированная»',
				handler: function() {
					var record = form.DrugRequestGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequest_id') > 0) {
					}
				},
				iconCls: 'view16'
			}, {
				name: 'action_action2',
				text: 'Установить статус заявки «Начальная»',
				tooltip: 'Установить статус заявки «Начальная»',
				handler: function() {
					var record = form.DrugRequestGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequest_id') > 0) {
					}
				},
				iconCls: 'view16'
			}, {
				name: 'action_action3',
				text: lang['zakryit_zayavku_mo'],
				tooltip: lang['zakryit_zayavku_mo'],
				handler: function() {
					var record = form.DrugRequestGrid.getGrid().getSelectionModel().getSelected();
					if (record.get('DrugRequest_id') > 0) {
					}
				},
				iconCls: 'view16'
			}],
			iconCls: 'actions16'
		});
		
		form.loadGridWithFilter(true);
		loadMask.hide();
		if ( !isAdmin )
			Ext.getCmp('NewDrugRequestViewForm').buttons[0].hide();
	},
	/*
	loadIsOnko: function()
	{
		if (getGlobalOptions().isOnko == undefined)
		{
			Ext.Ajax.request(
			{
				url: '/?c=DrugRequest&m=isOnko',
				callback: function(options, success, response) 
				{
					if (success)
					{
						var result = Ext.util.JSON.decode(response.responseText);
							getGlobalOptions().isOnko = result.result;
					}
				}
			});
		}
	},*/
	/**
	* checkDrugRequestLimitExceed - проверка превышения лимита
	*/
	checkDrugRequestLimitExceed: function() {
		if ( !isAdmin ) {
			return false;
		}
		
		var form = this;

		var PersonRegisterType_id = this.findById('ndrvPersonRegisterType_id').getValue();
		var DrugRequestPeriod_id = this.findById('ndrvDrugRequestPeriod_id').getValue();
		var Lpu_id = form.findById('ndrvLpu_id').getValue() || getGlobalOptions().lpu_id;

		if ( !DrugRequestPeriod_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['ne_zapolneno_pole_period_zayavki'],
				title: lang['oshibka']
			});
			return false;
		}

		// Выдавать сообщение "Превышение лимита по заявке..."
		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					var error_text = response_obj.Error_Msg;
					var fed_lgot_exceed_sum = response_obj.FedLgotExceed_Sum;
					var reg_lgot_exceed_sum = response_obj.RegLgotExceed_Sum;

					if ( error_text.length == 0 ) {
						if ( fed_lgot_exceed_sum > 0 ) {
							error_text += '<div>Федеральная заявка ЛПУ не принята. Превышение составляет ' + Number(fed_lgot_exceed_sum).toFixed(2) + ' рублей</div>';
						}

						if ( reg_lgot_exceed_sum > 0 ) {
							error_text += '<div>Региональная заявка ЛПУ не принята. Превышение составляет ' + Number(reg_lgot_exceed_sum).toFixed(2) + ' рублей</div>';
						}
					}

					if ( error_text.length > 0 ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: error_text,
							title: (getRegionNick() == 'perm' ? lang['prevyishenie_normativa'] : lang['prevyishenie_limita'])
						});
					}
/*
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								//
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: lang['limityi_ne_prevyishenyi'],
							title: lang['prevyishenie_limita']
						});
					}
*/
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_proverke_limitov_po_zayavke'], function() { /* */ } );
				}
			}.createDelegate(this),
			params: {
				PersonRegisterType_id: PersonRegisterType_id,
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				Lpu_id: Lpu_id
			},
			url: '/?c=DrugRequest&m=index&method=checkDrugRequestLimitExceed'
		});
	},
	clearFilters: function ()
	{
		this.findById('ndrvDrugRequestPeriod_id').setValue('');
		this.findById('ndrvMedPersonal_id').setValue('');
		this.findById('ndrvDrugRequestStatus_id').setValue('');
		this.findById('ndrvLpuSection_id').setValue('');
		this.findById('ndrvPersonRegisterType_id').setValue('');
		this.enableLpuValues(false);
		if (getGlobalOptions().isMinZdrav)
			this.findById('ndrvLpu_id').setValue(getGlobalOptions().lpu_id);
		//this.findById('ndrvLpu_id').setValue(getGlobalOptions().lpu_id);
	},
	enableLpuValues: function (flag)
	{
		if (flag)
		{
			//this.EditLpuPanel.setVisible(true);
			//this.doLayout();
		}
		else 
		{
			//this.EditLpuPanel.setVisible(false);
			//this.doLayout();
			this.findById('ndrvFedLgotCount').setValue('');
			this.findById('ndrvRegLgotCount').setValue('');
		}
	},
	loadGridWithFilter: function(clear)
	{
		var form = this;
		var Lpu_id = this.findById('ndrvLpu_id').getValue() || getGlobalOptions().lpu_id;
		if (clear)
		{
			form.findById('ndrvLpu_id').getStore().clearFilter();
			form.clearFilters();
			// {params: {Lpu_id: Lpu_id, MedPersonal_id: '', DrugRequestPeriod_id: '', DrugRequestStatus_id: '', LpuSection_id: ''},
			form.DrugRequestGrid.loadData({globalFilters: {Lpu_id: Lpu_id, MedPersonal_id: '', DrugRequestPeriod_id: '', DrugRequestStatus_id: '', LpuSection_id: ''}});
			form.setVisualLpuClose(false);
			form.setVisualLpuUt(false);
		}
		else 
		{
			var DrugRequestPeriod_id = this.findById('ndrvDrugRequestPeriod_id').getValue() || '';
			var MedPersonal_id = this.findById('ndrvMedPersonal_id').getValue() || '';
			var DrugRequestStatus_id = this.findById('ndrvDrugRequestStatus_id').getValue() || '';
			var LpuSection_id = this.findById('ndrvLpuSection_id').getValue() || '';
			var PersonRegisterType_id = this.findById('ndrvPersonRegisterType_id').getValue() || '';
			
			if (DrugRequestPeriod_id==0)
			{
				sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ndrvDrugRequestPeriod_id').focus();});
				return false;
			}
			form.getLpuClose();
			form.getLpuUt();
			// params: {Lpu_id: Lpu_id, MedPersonal_id: MedPersonal_id, DrugRequestPeriod_id: DrugRequestPeriod_id, DrugRequestStatus_id: DrugRequestStatus_id, LpuSection_id: LpuSection_id},
			form.DrugRequestGrid.loadData({globalFilters: {Lpu_id: Lpu_id, MedPersonal_id: MedPersonal_id, DrugRequestPeriod_id: DrugRequestPeriod_id, DrugRequestStatus_id: DrugRequestStatus_id, LpuSection_id: LpuSection_id, PersonRegisterType_id: PersonRegisterType_id}});
		}
	},
	setVisualLpuClose: function(togg)
	{
		/*if (this.findById('ndrvDrugRequestSetStatus').pressed!=togg) 
		{
			this.findById('ndrvDrugRequestSetStatus').toggle();
		}*/
		if (togg) 
		{
			if (getGlobalOptions().isMinZdrav)
			{
				//this.findById('ndrvDrugRequestSetStatus').enable();
				this.findById('ndrvDrugRequestSetStatus3').enable();
			}
			/*else 
			{
				this.findById('ndrvDrugRequestSetStatus').disable();
			}*/
			this.isCloseLpu = 2;
			//this.findById('ndrvDrugRequestSetStatus').setText('Открыть заявки ЛПУ');
		}
		else 
		{
			this.isCloseLpu = 1;
			/*if (this.findById('ndrvDrugRequestPeriod_id').getValue()>0) 
				this.findById('ndrvDrugRequestSetStatus').enable();
			else 
				this.findById('ndrvDrugRequestSetStatus').disable();*/
			//this.findById('ndrvDrugRequestSetStatus').setText('Закрыть заявки ЛПУ');
			this.findById('ndrvDrugRequestSetStatus3').disable();
		}
	},
	setVisualLpuUt: function(togg)
	{
		if (this.findById('ndrvDrugRequestSetStatus3').pressed!=togg) 
		{
			this.findById('ndrvDrugRequestSetStatus3').toggle();
		}
		if (togg) 
		{
			if ((getGlobalOptions().isMinZdrav) && (this.isCloseLpu==2))
			{
				this.findById('ndrvDrugRequestSetStatus3').enable();
			}
			else 
			{
				this.findById('ndrvDrugRequestSetStatus3').disable();
			}
			this.findById('ndrvDrugRequestSetStatus3').setText(lang['snyat_utv_s_lpu']);
			//this.findById('ndrvDrugRequestSetStatus').disable();
		}
		else 
		{
			if ((this.findById('ndrvDrugRequestPeriod_id').getValue()>0) && (getGlobalOptions().isMinZdrav) && (this.isCloseLpu==2))
			{
				this.findById('ndrvDrugRequestSetStatus3').enable();
				//this.findById('ndrvDrugRequestSetStatus').enable();
			}
			else 
				this.findById('ndrvDrugRequestSetStatus3').disable();
			this.findById('ndrvDrugRequestSetStatus3').setText(lang['utverdit_zayavki_lpu']);
			
		}
	},
	getLpuClose: function()
	{
		var form = this;
		var DrugRequestPeriod_id = form.findById('ndrvDrugRequestPeriod_id').getValue() || 0;
		var Lpu_id = form.findById('ndrvLpu_id').getValue() || getGlobalOptions().lpu_id;
		if (DrugRequestPeriod_id==0)
		{
			sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ndrvDrugRequestPeriod_id').focus();});
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=getDrugRequestLpuClose',
			params: 
			{	
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				Lpu_id: Lpu_id
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.DrugRequestTotalStatus_IsClose)
					{
						form.enableLpuValues(!getGlobalOptions().isMinZdrav);
						form.setVisualLpuClose((result.DrugRequestTotalStatus_IsClose==2));
						form.findById('ndrvFedLgotCount').setValue(result.FedLgotCount);
						form.findById('ndrvRegLgotCount').setValue(result.RegLgotCount);
						//form.EditLpuPanel.setVisible(!getGlobalOptions().isMinZdrav);
					}
					else 
					{
						form.setVisualLpuClose(false);
						//form.EditLpuPanel.setVisible(false);
						form.enableLpuValues(false);
					}
				}
			}
		});
	},
	saveLpuValue: function()
	{
		var form = this;
		var DrugRequestPeriod_id = form.findById('ndrvDrugRequestPeriod_id').getValue() || 0;
		var DrugRequestTotalStatus_IsClose = form.isCloseLpu;
		var DrugRequestTotalStatus_FedLgotCount = form.findById('ndrvDrugRequestTotalStatus_FedLgotCount').getValue();
		var DrugRequestTotalStatus_RegLgotCount = form.findById('ndrvDrugRequestTotalStatus_RegLgotCount').getValue();
		var Lpu_id = form.findById('ndrvLpu_id').getValue() || getGlobalOptions().lpu_id;
		if (DrugRequestPeriod_id==0)
		{
			sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ndrvDrugRequestPeriod_id').focus();});
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=saveDrugRequestLpu',
			params: 
			{	
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				DrugRequestTotalStatus_IsClose: DrugRequestTotalStatus_IsClose,
				Lpu_id: Lpu_id,
				DrugRequestTotalStatus_FedLgotCount:DrugRequestTotalStatus_FedLgotCount,
				DrugRequestTotalStatus_RegLgotCount:DrugRequestTotalStatus_RegLgotCount
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					sw.swMsg.alert(lang['soobschenie'], lang['dannyie_dlya_rascheta_limita_uspeshno_sohranenyi']);
				}
				else 
				{
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka_poprobuyte_povtorit_sohranenie']);
				}
			}
		});
	},
	setLpuClose: function()
	{
		var form = this;
		var DrugRequestPeriod_id = form.findById('ndrvDrugRequestPeriod_id').getValue() || 0;
		var DrugRequestTotalStatus_IsClose = (form.isCloseLpu==1)?2:1;
		var Lpu_id = form.findById('ndrvLpu_id').getValue() || getGlobalOptions().lpu_id;
		if (DrugRequestPeriod_id==0)
		{
			sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ndrvDrugRequestPeriod_id').focus();});
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=setDrugRequestLpuClose',
			params: 
			{	
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				DrugRequestTotalStatus_IsClose: DrugRequestTotalStatus_IsClose,
				Lpu_id: Lpu_id
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.DrugRequestTotalStatus_IsClose)
					{
						form.setVisualLpuClose((result.DrugRequestTotalStatus_IsClose==2));
						form.loadGridWithFilter();
					}
					else 
					{
						form.setVisualLpuClose(false);
					}
				}
			}
		});
	},
	setLpuUt: function()
	{
		var form = this;
		var DrugRequestPeriod_id = form.findById('ndrvDrugRequestPeriod_id').getValue() || 0;
		var Lpu_id = form.findById('ndrvLpu_id').getValue() || getGlobalOptions().lpu_id;
		var DrugRequestStatus_id = (form.findById('ndrvDrugRequestSetStatus3').pressed)?2:3;
		if (DrugRequestPeriod_id==0)
		{
			sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ndrvDrugRequestPeriod_id').focus();});
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=setDrugRequestLpuUt',
			params: 
			{	
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				DrugRequestStatus_id: DrugRequestStatus_id,
				Lpu_id: Lpu_id
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.DrugRequestStatus_id)
					{
						form.setVisualLpuUt((result.DrugRequestStatus_id==3));
						form.loadGridWithFilter();
					}
					else 
					{
						form.setVisualLpuUt(false);
					}
				}
			}
		});
	},
	getLpuUt: function()
	{
		var form = this;
		var DrugRequestPeriod_id = form.findById('ndrvDrugRequestPeriod_id').getValue() || 0;
		var Lpu_id = form.findById('ndrvLpu_id').getValue() || getGlobalOptions().lpu_id;
		if (DrugRequestPeriod_id==0)
		{
			sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ndrvDrugRequestPeriod_id').focus();});
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=getDrugRequestLpuUt',
			params: 
			{	
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				Lpu_id: Lpu_id
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.DrugRequestStatus_id)
					{
						form.setVisualLpuUt((result.DrugRequestStatus_id==3));
					}
					else 
					{
						form.setVisualLpuUt(false);
					}
				}
			}
		});
	},
	initComponent: function()
	{
		var form = this;
		/*
		this.GroupActions = new Array();
		
		// Группа акшенов уровней
		this.GroupActions['actions'] = new Ext.Action(
		{
			text:lang['deystviya'], 
			menu: [
				form.Actions.action_New_EvnPL, 
				form.Actions.action_PersonAdd
			]
		});
		this.GroupActions['settings'] = new Ext.Action(
		{
			text:lang['nastroyki'], 
			menu: 
			{
				items: 
				[{
					text: lang['vyivodit_sobyitiya_po_date'],
					checked: true,
					group: 'group',
					handler: function ()
					{
						form.Tree.loader.baseParams.type = 0;
						form.option_type = 0;
						form.Tree.getRootNode().select()
						form.Tree.loader.load(form.Tree.root);
						form.Tree.getRootNode().expand();
					},
					checkHandler: function () 
					{
					}
				}, 
				{
					text: lang['gruppirovat_sobyitiya_po_tipam'],
					checked: false,
					group: 'group',
					handler: function ()
					{
						form.Tree.loader.baseParams.type = 1;
						form.option_type = 1;
						form.Tree.getRootNode().select()
						form.Tree.loader.load(form.Tree.root);
						form.Tree.getRootNode().expand();
					},
					checkHandler: function () 
					{
						
					}
				}]
			}
		});
		*/
		/*
		this.TreeToolbar = new Ext.Toolbar(
		{
			id : form.id+'Toolbar',
			items:
			[
				form.GroupActions.actions,
				{
					xtype : "tbseparator"
				},
				form.GroupActions.settings
			]
		});
		
		// Формируем меню по правой кнопке 
		this.ContextMenu = new Ext.menu.Menu();
		for (key in this.Actions)
		{
			this.ContextMenu.add(this.Actions[key]);
		}
		*/
		
		this.FiltersPanel = new Ext.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: false,
			collapsible: false,
			height: 80,
			region: 'north',
			labelWidth: 110,
			layout: 'column',
			//title: 'Фильтры',
			id: 'DrugRequestFiltersPanel',
			items: 
			[{
				// Левая часть фильтров
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px;',
				columnWidth: .35,
				labelWidth: 110,
				items: 
				[
				{
					allowBlank: true,
					fieldLabel: lang['tip_zayavki'],
					comboSubject: 'PersonRegisterType',
					name: 'PersonRegisterType_id',
					id: 'ndrvPersonRegisterType_id',
					anchor: '100%',
					xtype: 'swcustomobjectcombo'
				},
				{
					allowBlank: true,
					disabled: false,
					id: 'ndrvDrugRequestPeriod_id',
					xtype: 'swdrugrequestperiodcombo',
					tabIndex:4212
				},
				{
					allowBlank: true,
					disabled: false,
					id: 'ndrvDrugRequestPeriod_id',
					xtype: 'swdrugrequestperiodcombo',
					tabIndex:4212
				},
				{
					allowBlank: true,
					disabled: false,
					id: 'ndrvDrugRequestStatus_id',
					xtype: 'swdrugrequeststatuscombo',
					tabIndex:4211
				},
				{
					xtype:'panel',
					layout: 'form',
					border: false,
					id: 'ndrvLpuPanel',
					bodyStyle:'background:#DFE8F6;padding-right:0px;',
					labelWidth: 110,
					items: 
					[{
						allowBlank: true,
						disabled: false,
						id: 'ndrvLpu_id',
						anchor: '100%',
						hiddenName: 'Lpu_id',
						xtype: 'swlpulocalcombo',
						tabIndex:4213
					}]
				}]
			},
			{
				// Средняя часть параметров ввода
				layout: 'form',
				border: false,
				id: 'ndrvDrugRequestMedPersonalFilter',
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .35,
				labelWidth: 110,
				items:
				[{
					anchor: '100%',
					name: 'LpuUnit_id',
					tabIndex: 4214,
					disabled: false,
					xtype: 'swlpuunitcombo',
					topLevel: true,
					allowBlank:true, 
					id: 'ndrvLpuUnit_id',
					listeners:
					{
						change:
							function(combo)
							{
								var tut = this.ownerCt.ownerCt.ownerCt;
								if (combo.getValue() > 0)
								{
									tut.findById('ndrvLpuSection_id').getStore().load(
									{
										params:
										{
											Object: 'LpuSection',
											LpuUnit_id: combo.getValue()
										},
										callback: function()
										{
											tut.findById('ndrvLpuSection_id').setValue('');
											tut.findById('ndrvMedPersonal_id').setValue('');
											//tut.loadGridWithFilter();
										}
									});
								}
								else 
								{
									tut.findById('ndrvLpuSection_id').setValue('');
									tut.findById('ndrvMedPersonal_id').setValue('');
									//tut.loadGridWithFilter();
								}
							}
					}
				},
				{
					xtype: 'swlpusectioncombo',
					anchor: '100%',
					tabIndex:3,
					name: 'LpuSection_id',
					id: 'ndrvLpuSection_id',
					allowBlank: true,
					tabIndex:4215,
					listeners:
					{
						change:
							function(combo)
							{
								var tut = this.ownerCt.ownerCt.ownerCt; 
								if (combo.getValue() > 0)
								{
									tut.findById('ndrvMedPersonal_id').getStore().load(
									{
										params:
										{
											LpuSection_id: combo.getValue(),
											IsDlo: (!getGlobalOptions().isOnko && !getGlobalOptions().isRA)?1:0
										},
										callback: function()
										{
											tut.findById('ndrvMedPersonal_id').setValue('');
											//tut.loadGridWithFilter();
										}
									});
								}
								else 
								{
									tut.findById('ndrvMedPersonal_id').setValue('');
									//tut.loadGridWithFilter();
								}
							}
					}
				},
				{
					xtype: 'swmedpersonalcombo',
					anchor: '100%',
					allowBlank: true,
					name: 'MedPersonal_id',
					id: 'ndrvMedPersonal_id',
					loadingText: lang['idet_poisk'],
					minChars: 1,
					minLength: 1,
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					tabIndex:4216,
					listeners:
					{
						change: 
							function(combo)
							{
								Ext.getCmp('NewDrugRequestViewForm').loadGridWithFilter();
							}
					}
				}]
			},
			{
				// Правая часть параметров ввода
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .12,
				items:
				[{
					xtype: 'button',
					text: lang['poisk'],
					tabIndex: 4217,
					minWidth: 125,
					disabled: false,
					topLevel: true,
					allowBlank:true, 
					id: 'ndrvButtonSetFilter',
					handler: function ()
					{
						Ext.getCmp('NewDrugRequestViewForm').checkDrugRequestLimitExceed();
						Ext.getCmp('NewDrugRequestViewForm').loadGridWithFilter();
					}
				},
				{
					xtype: 'button',
					text: lang['sbros'],
					tabIndex: 4218,
					minWidth: 125,
					disabled: false,
					topLevel: true,
					allowBlank:true, 
					id: 'ndrvButtonUnSetFilter',
					handler: function ()
					{
						Ext.getCmp('NewDrugRequestViewForm').loadGridWithFilter(true);
					}
				}]
			},
			{
				// Еще более правая часть параметров ввода
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .15,
				items:
				[/*{
					xtype: 'button',
					text: lang['pechat'],
					iconCls: 'print16',
					tabIndex: 4219,
					minWidth: 140,
					disabled: false,
					topLevel: true,
					allowBlank:true, 
					id: 'ndrvButtonPrint',
					handler: function () {
						sw.swMsg.alert(lang['vnimanie'], lang['otchetyi_po_zayavke_perenesenyi_v_ctatisticheskie_otchetyi_-_llo_-_zayavka_izvinite_za_dostavlennyie_neudobstva']);
					}
				},
				{
					xtype: 'button',
					minWidth: 140,
					//iconCls:'request-lock16',
					text: lang['zakryit_zayavki_lpu'],
					tooltip: (getGlobalOptions().isMinZdrav)?lang['zakryit_otkryit_vse_zayavki_lpu']:lang['zakryit_vse_zayavki_lpu'],
					id: 'ndrvDrugRequestSetStatus',
					disabled: true, 
					handler: function(checkbox, check) 
					{
						var win = Ext.getCmp('NewDrugRequestViewForm');
						var period = win.findById('ndrvDrugRequestPeriod_id').getValue() || 0;
						if (period==0)
						{
							sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ndrvDrugRequestPeriod_id').focus();});
							//win.findById('ndrvDrugRequestSetStatus').toggle();
							return false;
						}
						if ((period > 0) && (win.isCloseLpu==2) && (getGlobalOptions().isMinZdrav))
						{
							sw.swMsg.show(
							{
								icon: Ext.MessageBox.QUESTION,
								msg: lang['otkryit_dlya_redaktirovaniya_zayavki_tekuschey_lpu'],
								title: lang['vopros'],
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj)
								{
									if ('yes' == buttonId)
									{
										win.setLpuClose();
									}
									else 
									{
										//win.findById('ndrvDrugRequestSetStatus').toggle();
									}
								}
							});
						}
						else 
						if ((period > 0) && (win.isCloseLpu==1))
						{
							if (!getGlobalOptions().isMinZdrav)
								var message_for_lpu = '<b style="color:red;">Отменить данное действие вы не сможете. Открывает заявки для повторного редактирования МЗ ПК.</b> ';
							else 
								var message_for_lpu = '';
							sw.swMsg.show(
							{
								//icon: (!getGlobalOptions().isMinZdrav)?Ext.MessageBox.QUESTION,
								icon: Ext.MessageBox.WARNING,
								msg: lang['pri_zakryitii_zayavki_lpu_vse_zayavki_vrachey_stanut_nedostupnyi_dlya_izmeneniya']+message_for_lpu+lang['zakryit_zayavki_lpu_za_vyibrannyiy_period'],
								title: lang['vopros'],
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj)
								{
									if ('yes' == buttonId)
									{
										win.setLpuClose();
									}
									else 
									{
										//win.findById('ndrvDrugRequestSetStatus').toggle();
									}
								}
							});
						}
					}
				},*/
				{
					xtype: 'button',
					minWidth: 140,
					text: lang['utverdit_zayavki_lpu'],
					tooltip: lang['utverdit_snyat_utverjdenie_po_vsem_zayavkam_lpu'],
					id: 'ndrvDrugRequestSetStatus3',
					disabled: true, 
					handler: function(checkbox, check) 
					{
						var win = Ext.getCmp('NewDrugRequestViewForm');
						var period = win.findById('ndrvDrugRequestPeriod_id').getValue() || 0;
						if (period==0)
						{
							sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ndrvDrugRequestPeriod_id').focus();});
							return false;
						}
						if ((period > 0) && (getGlobalOptions().isMinZdrav))
						{
							if (win.findById('ndrvDrugRequestSetStatus3').pressed)
							{
								sw.swMsg.show(
								{
									icon: Ext.MessageBox.QUESTION,
									msg: lang['snyat_priznak_utverjdeniya_s_zayavok_tekuschey_lpu'],
									title: lang['vopros'],
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj)
									{
										if ('yes' == buttonId)
										{
											win.setLpuUt();
										}
									}
								});
							}
							else 
							{
								sw.swMsg.show(
								{
									icon: Ext.MessageBox.QUESTION,
									msg: lang['utverdit_zayavki_lpu_za_vyibrannyiy_period'],
									title: lang['vopros'],
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj)
									{
										if ('yes' == buttonId)
										{
											win.setLpuUt();
										}
									}
								});
							}
						}
					}
				}]
			}]
		});
		
		this.EditLpuPanel = new Ext.Panel(
		{
			bodyStyle:'padding:0px;margin:0px;',
			autoHeight: true,
			border: false,
			frame: true,
			//collapsible: true,
			region: 'north',
			labelWidth: 100,
			layout: 'column',
			id: 'DrugRequestEditLpuPanel',
			items: 
			[{
				// 1 левая часть вводилки 
				layout: 'form',
				border: false,
				bodyStyle:'padding-right:5px;',
				columnWidth: .35,
				labelWidth: 280,
				items: 
				[{
					xtype: 'numberfield',
					disabled: true,
					maxValue: 999999,
					minValue: 0,
					autoCreate: {tag: "input", size:6, maxLength: "6", autocomplete: "off"},
					fieldLabel: lang['kol-vo_federalnyih_lgotnikov_prikr_k_lpu'],
					name: 'FedLgotCount',
					id: 'ndrvFedLgotCount',
					tabIndex:4231
				}]
			},
			{
				// 1 правая часть вводилки 
				layout: 'form',
				border: false,
				bodyStyle:'padding-right:5px;',
				columnWidth: .35,
				labelWidth: 280,
				items: 
				[{
					xtype: 'numberfield',
					disabled: true,
					maxValue: 999999,
					minValue: 0,
					autoCreate: {tag: "input", size:6, maxLength: "6", autocomplete: "off"},
					fieldLabel: lang['kol-vo_regionalnyih_lgotnikov_prikr_k_lpu'],
					name: 'RegLgotCount',
					id: 'ndrvRegLgotCount',
					tabIndex:4232
				}]
			}]
		});
		
		// Пациенты
		this.DrugRequestGrid = new sw.Promed.ViewFrame(
		{
			//title:'Заявки',
			id: this.id + 'GridPanel',
			region: 'center',
			height: 303,
			minSize: 200,
			maxSize: 400,
			object: 'DrugRequest',
			editformclassname: 'swNewDrugRequestEditForm',
			dataUrl: '/?c=DrugRequest&m=index&method=getDrugRequestSum',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'DrugRequest_id', type: 'int', header: 'ID', key: true},
				{name: 'PersonRegisterType_Name', header: lang['tip_zayavki'], width: 140},
				{name: 'PersonRegisterType_id', hidden: true, isparams: true},
				{name: 'DrugRequestPeriod_Name', header: lang['period'], width: 140},
				{name: 'DrugRequestPeriod_id', hidden: true, isparams: true},
				{name: 'DrugRequestStatus_Name', header: lang['status'], width: 100},
				{name: 'DrugRequestStatus_id', hidden: true, hideable: false},
				{name: 'DrugRequest_Name', id: 'autoexpand', header: lang['zayavka']},
				{name: 'DrugRequest_SummaFed', type: 'money', align: 'right', header: lang['summa_fed_svoi'], width: 100},
				{name: 'DrugRequest_SummaFedAll', type: 'money', align: 'right', header: lang['summa_fed_obsch'], width: 100},
				{name: 'DrugRequest_SummaFedLimit', type: 'money', align: 'right', header: (getRegionNick()=='perm'?lang['normativ_fed']:lang['limit_fed']), width: 80},
				{name: 'DrugRequest_SummaReg', type: 'money', align: 'right', header: lang['summa_reg_svoi'], width: 100},
				{name: 'DrugRequest_SummaRegAll', type: 'money', align: 'right', header: lang['summa_reg_obsch'], width: 100},
				{name: 'DrugRequest_SummaRegLimit', type: 'money', align: 'right', header: (getRegionNick()=='perm'?lang['normativ_reg']:lang['limit_reg']), width: 80},
				{name: 'LpuSection_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'LpuSection_Name', header: lang['otdelenie'], width: 200},
				{name: 'MedPersonal_id', hidden: true, isparams: true},
				{name: 'MedPersonal_FIO', header: lang['vrach'], width: 200},
				{name: 'DrugRequest_insDT', type: 'date', header: lang['vnesen'], width: 80},
				{name: 'DrugRequest_updDT', type: 'date', header: lang['izmenen'], width: 80}
			],
			actions:
			[
				{name:'action_delete', url: '/?c=DrugRequest&m=index&method=deleteDrugRequest'}
			], 
			onLoadData: function(result)
			{
				// Собираем суммы по заявкам 
				var win = Ext.getCmp('NewDrugRequestViewForm');
				var sumFed = 0;
				var sumReg = 0;
				/*
				var sumFedAll = 0;
				var sumRegAll = 0;
				var sumFedLimit = 0;
				var sumRegLimit = 0;
				*/
				if (result)
				{
					win.DrugRequestGrid.ViewGridStore.each(function(record) 
					{
						if ((record.get('DrugRequest_SummaFed')!=undefined) && (record.get('DrugRequest_SummaFed')!=''))
							sumFed = sumFed + record.get('DrugRequest_SummaFed');
						if ((record.get('DrugRequest_SummaReg')!=undefined) && (record.get('DrugRequest_SummaReg')!=''))
							sumReg = sumReg + record.get('DrugRequest_SummaReg');
						/*
						if ((record.get('DrugRequest_SummaFedAll')!=undefined) && (record.get('DrugRequest_SummaFedAll')!=''))
							sumFedAll = sumFedAll + record.get('DrugRequest_SummaFedAll');
						if ((record.get('DrugRequest_SummaRegAll')!=undefined) && (record.get('DrugRequest_SummaRegAll')!=''))
							sumRegAll = sumRegAll + record.get('DrugRequest_SummaRegAll');
						if ((record.get('DrugRequest_SummaFedLimit')!=undefined) && (record.get('DrugRequest_SummaFedLimit')!=''))
							sumFedLimit = sumFedLimit + record.get('DrugRequest_SummaFedLimit');
						if ((record.get('DrugRequest_SummaRegLimit')!=undefined) && (record.get('DrugRequest_SummaRegLimit')!=''))
							sumRegLimit = sumRegLimit + record.get('DrugRequest_SummaRegLimit');
						*/
					});
				}
				// Установить суммы по пациенту
				sumFed = sw.Promed.Format.rurMoney(sumFed);
				sumReg = sw.Promed.Format.rurMoney(sumReg);
				/*
				sumFedAll = sw.Promed.Format.rurMoney(sumFedAll);
				sumRegAll = sw.Promed.Format.rurMoney(sumRegAll);
				sumFedLimit = sw.Promed.Format.rurMoney(sumFedLimit);
				sumRegLimit = sw.Promed.Format.rurMoney(sumRegLimit);
				*/
				win.SumDRTpl.overwrite(win.SumDRPanel.body, {sumFed:sumFed, sumReg:sumReg}); //, sumFedAll:sumFedAll, sumRegAll:sumRegAll, sumFedLimit:sumFedLimit, sumRegLimit:sumRegLimit
			}
		});
		
		var sumTplMark = 
		[
			'<div style="height:22px;padding-top:2px;font-weight:bold;"><span style="color:#444;">&nbsp;&nbsp;Сумма заявок с учетом фильтра, в руб. (фед, свои/рег, свои):</span> {sumFed} / {sumReg} </div>' // {sumFedAll} / {sumFedLimit} / {sumReg} / {sumRegAll} / {sumRegLimit}
			//'Product Group: {ProductGroup}<br/>'
		];
		this.SumDRTpl = new Ext.Template(sumTplMark);
		this.SumDRPanel = new Ext.Panel(
		{
			id: 'SumDRPanel',
			bodyStyle: 'padding:2px',
			layout: 'fit',
			region: 'south',
			border: true,
			frame: false,
			height: 22,
			maxSize: 22,
			html: ''
		});
		
		/*
		this.Tree = new Ext.tree.TreePanel( 
		{
			//title: 'События',
			id: 'PersonEPHTree',
			region: 'center',
			animate:false,
			enableDD: false,
			autoScroll: true,
			autoLoad:false,
			border: false,
			//rootVisible: false,
			split: true,
			tbar: form.TreeToolbar,
			contextMenu: form.ContextMenu,
			root: 
			{
				nodeType: 'async',
				text:lang['elektronnyiy_pasport_zdorovya'],
				id:'root',
				expanded: false
			},
			loader: new Ext.tree.TreeLoader(
			{
				listeners:
				{
					beforeload: function (tl, node)
					{
						tl.baseParams.level = node.getDepth();
						if (node.getDepth()==0)
						{
							tl.baseParams.object = 'Person';
							if (form.Person_id)
								tl.baseParams.object_id = form.Person_id;
							else
								tl.baseParams.object_id = 0;
						}
						else
						{
							tl.baseParams.object = node.attributes.object;
							tl.baseParams.object_id = node.attributes.object_value;
						}
					}
				},
				dataUrl:'/?c=EPH&m=getPersonEPHData'
			})
		});
		
		this.Tree.addListener('contextmenu', onMessageContextMenu,this);
		*/
		/*
		this.Tree.on('contextmenu', function(node, e)
		{
			e.stopEvent();
			e.browserEvent.returnValue = false;
			e.returnValue = false;
			if (Ext.isIE)
			{
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}
		});
		*/
		/*
		// Функция вывода меню по клику правой клавиши
		function onMessageContextMenu(node, e)
		{
			// На правый клик переходим на выделяемую запись
			node.select();
			// Отрабатываем метод Click
			this.Tree.fireEvent('click', node);
			
			var c = node.getOwnerTree().contextMenu;
			c.contextNode = node;
			c.showAt(e.getXY());
			
			//node.getOwnerTree().contextMenu.contextNode = node;
			//node.getOwnerTree().contextMenu.showAt(e.getXY());
		}
		// Меню к гриду добавили
		this.Tree.on('dblclick', function(node, event)
		{
			
			//if (!tree.ownerCt.ownerCt.Actions.action_edit.isDisabled())
			//	tree.ownerCt.ownerCt.Actions.action_edit.execute();
			//alert('Событие на dblclick');
		});
		
		// функция выбора элемента дерева 
		this.OnTreeClick = function(node, e) 
		{
			var lvl = node.getDepth();
			//var form = this;

			this.Actions.action_PersonAdd.hide();
			var type = this.option_type;
		};
		
		this.Tree.on('click', function(node, e) 
		{
			form.OnTreeClick(node, e);
		});
		*/
		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			layout:'border',
			items: 
			[
				form.FiltersPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					defaults: {split: true},
					items: 
					[
						form.EditLpuPanel,
						{
							border: false,
							region: 'center',
							layout: 'fit',
							items: [form.DrugRequestGrid]
						}
					]
				},
				form.SumDRPanel
			]
			/*
			items:
			[
				form.FiltersPanel,
				form.DrugRequestGrid
			]
			*/
		});
		sw.Promed.swNewDrugRequestViewForm.superclass.initComponent.apply(this, arguments);
	}

});
