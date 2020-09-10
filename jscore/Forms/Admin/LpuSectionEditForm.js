/**
* swLpuSectionEditForm - окно просмотра и редактирования отделений
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      10.04.2009
*/
/*NO PARSE JSON*/
sw.Promed.swLpuSectionEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:langs('Отделение'),
	id: 'LpuSectionEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 700,
	height: 550,
	minWidth: 700,
	minHeight: 550,
	modal: true,
	codeRefresh: true,
	objectName: 'swLpuSectionEditForm',
	objectSrc: '/jscore/Forms/Admin/LpuSectionEditForm.js',
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lsOk',
		tabIndex: 2448,
		iconCls: 'save16',
		handler: function()
		{
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
		id: 'lsCancel',
		tabIndex: 2449,
		iconCls: 'cancel16',
		onTabAction: function()
		{
			this.ownerCt.findById('lsLpuSection_setDate').focus();
		},
		onShiftTabAction: function()
		{
			Ext.getCmp('lsOk').focus();
		},
		handler: function()
		{
			this.ownerCt.hide();
			this.ownerCt.returnFunc(this.ownerCt.owner, -1);
		}
	}
	],
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	returnFunc: function(owner) {},	
	filterLpuSectionMedicalCareKindGrid: function() {
	
		var grid = this.LpuSectionMedicalCareKindGrid;
		var store = grid.getGrid().getStore();	
		store.clearFilter();
		
		if (!grid.gFilters || (grid.gFilters && grid.gFilters.isClose == 1)) {	
		
			store.filterBy(function(rec) {
				return ( Number(rec.get('RecordStatus_Code')) != 3 && Ext.isEmpty(rec.get('LpuSectionMedicalCareKind_endDate')) );
			});
			
		} else if (grid.gFilters && grid.gFilters.isClose == 2) {
		
			store.filterBy(function(rec) {
				return ( Number(rec.get('RecordStatus_Code')) != 3 && !Ext.isEmpty(rec.get('LpuSectionMedicalCareKind_endDate')) );
			});		
			
		} else {
		
			store.filterBy(function(rec) {
				return (Number(rec.get('RecordStatus_Code')) != 3);
			});
			
		}
		
		return true;
	},
	addLpuSectionMedicalCareKindCloseFilterMenu: function(){
	
		var win = this;
		var grid = this.LpuSectionMedicalCareKindGrid;

		if ( !grid.getAction('action_isclosefilter_'+grid.id) ) {
			var menuIsCloseFilter = new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: langs('Все'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = null;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText('Показывать: <b>Все</b>');
							win.filterLpuSectionMedicalCareKindGrid();
						}
					}),
					new Ext.Action({
						text: langs('Открытые'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 1;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText('Показывать: <b>Открытые</b>');
							win.filterLpuSectionMedicalCareKindGrid();
						}
					}),
					new Ext.Action({
						text: langs('Закрытые'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 2;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText('Показывать: <b>Закрытые</b>');
							win.filterLpuSectionMedicalCareKindGrid();
						}
					})
				]
			});

			grid.addActions({
				isClose: 1,
				name: 'action_isclosefilter_'+grid.id,
				text: 'Показывать: <b>Открытые</b>',
				menu: menuIsCloseFilter
			});
			
			this.filterLpuSectionMedicalCareKindGrid();
			
		}

		return true;
	},
	loadFpList: function() {
		if ( !getRegionNick().inlist([ 'kz' ]) ) {
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: langs('Загрузка списка функциональных подразделений...') });
		loadMask.show();

		var base_form = this.MainPanel.getForm(),
			FPIDField = base_form.findField('FPID'),
			FPID = FPIDField.getValue();
		
		FPIDField.clearValue();
		FPIDField.getStore().removeAll();
		FPIDField.getStore().load({
			callback: function() {
				loadMask.hide();
				if ( FPIDField.getStore().getCount() ) {
					if ( !Ext.isEmpty(FPID) ) {
						var index = FPIDField.getStore().findBy(function(rec) {
							return (rec.get('FPID') == FPID);
						});

						if ( index >= 0 ) {
							FPIDField.setValue(FPID);
							FPIDField.fireEvent('select', FPIDField, FPIDField.getStore().getAt(index), index);
						}
					}
				}
			},
			params: {
				Lpu_id: this.Lpu_id
			}
		});
		
	},
	loadLpuSectionCodeList: function() {
		if ( !getRegionNick().inlist([ 'pskov' ]) ) {
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: langs('Загрузка списка кодов отделений...') });
		loadMask.show();

		var base_form = this.MainPanel.getForm();

		var
			LpuSectionCode_id = base_form.findField('LpuSectionCode_id').getValue(),
			params = {
				LpuSectionCode_begDate: Ext.util.Format.date(base_form.findField('LpuSection_setDate').getValue(), 'd.m.Y'),
				LpuSectionCode_endDate: Ext.util.Format.date(base_form.findField('LpuSection_disDate').getValue(), 'd.m.Y'),
				LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
				LpuUnitType_id: this.LpuUnitType_id
			};

		base_form.findField('LpuSectionCode_id').clearValue();
		base_form.findField('LpuSectionCode_id').getStore().removeAll();
		base_form.findField('LpuSectionCode_id').getStore().load({
			callback: function() {
				loadMask.hide();

				if ( base_form.findField('LpuSectionCode_id').getStore().getCount() == 0 ) {
					base_form.findField('LpuSection_Code').setContainerVisible(true);
					base_form.findField('LpuSectionCode_id').setAllowBlank(true);
					base_form.findField('LpuSectionCode_id').setContainerVisible(false);
				}
				else {
					base_form.findField('LpuSection_Code').setContainerVisible(false);
					base_form.findField('LpuSectionCode_id').setAllowBlank(false);
					base_form.findField('LpuSectionCode_id').setContainerVisible(true);

					if ( !Ext.isEmpty(LpuSectionCode_id) ) {
						var index = base_form.findField('LpuSectionCode_id').getStore().findBy(function(rec) {
							return (rec.get('LpuSectionCode_id') == LpuSectionCode_id);
						});

						if ( index >= 0 ) {
							base_form.findField('LpuSectionCode_id').setValue(LpuSectionCode_id);
						}
					}

					base_form.findField('LpuSectionCode_id').fireEvent('change', base_form.findField('LpuSectionCode_id'), base_form.findField('LpuSectionCode_id').getValue());
				}
			},
			params: params
		});
	},
	loadRemoteStores: function()
	{
		var lM = new Ext.LoadMask(Ext.get('LpuSectionEditForm'), { msg: LOAD_WAIT });
		//lM.show();
		var lsfm = this;
		var params = {
			Object: 'LpuUnit',
			LpuUnit_id: '',
			LpuBuilding_id: lsfm.LpuBuilding_id,
			LpuUnit_Name: ''
		};
		if (this.Lpu_id) {
			params.Lpu_id = this.Lpu_id;
		}
		lsfm.findById('lsLpuUnit_id').getStore().load({
			params: params,
			callback: function()
			{
				lsfm.findById('lsLpuUnit_id').setValue(lsfm.LpuUnit_id);
				lsfm.findById('lsLpuUnit_id').fireEvent('change', lsfm.findById('lsLpuUnit_id'), lsfm.LpuUnit_id);
				lsfm.findById('lsLpuSection_pid').getStore().load(
				{
					params:
					{
						Object: 'LpuSection',
						LpuUnit_id: lsfm.LpuUnit_id,
						LpuSection_id: lsfm.LpuSection_id
					},
					callback: function()
					{
						if ((lsfm.LpuUnit_id==lsfm.findById('lsLpuUnit_id').getValue()) && (lsfm.findById('lspidcount').getValue() == 0))
							lsfm.findById('lsLpuSection_pid').setValue(lsfm.LpuSection_pid);
						else 
							if ((!lsfm.findById('lsLpuUnit_id').getValue()) && (lsfm.findById('lspidcount').getValue() == 0))
								lsfm.findById('lsLpuSection_pid').setValue(lsfm.LpuSection_pid);
							else 
								lsfm.findById('lsLpuSection_pid').setValue('');
							
						var lpu_section_profile_id = lsfm.findById('lsLpuSectionProfile_id').getValue();
						if (lpu_section_profile_id > 0) {
							// https://redmine.swan.perm.ru/issues/31730
							var lspParams = new Object();

							if ( getGlobalOptions().region.nick != 'ufa' || isSuperAdmin() == false ) {
								// NGS: WAS ADDED - OR LpuSectionProfile_code = 134
								lspParams.where = "where LpuSectionProfile_id = " + lpu_section_profile_id + " OR LpuSectionProfile_code = 134 OR LpuSectionProfile_endDT is null"; 
								// NGS: WAS ADDED - OR LpuSectionProfile_code = 134
								lspParams.clause = { where: 'record["LpuSectionProfile_id"] == "' + lpu_section_profile_id + '" || record["LpuSectionProfile_Code"] == 134 || record["LpuSectionProfile_endDT"] == ""', limit: null };
							}

							lsfm.findById('lsLpuSectionProfile_id').getStore().removeAll();
							lsfm.findById('lsLpuSectionProfile_id').getStore().load({
								params: lspParams,
								callback: function()
								{
									lsfm.findById('lsLpuSectionProfile_id').setValue(lpu_section_profile_id);
									lsfm.findById('lsLpuSection_disDate').fireEvent('change', lsfm.findById('lsLpuSection_disDate'), lsfm.findById('lsLpuSection_disDate').getValue());
									lsfm.findById('lsLpuSection_setDate').focus(true, 100);
									lM.hide();
								}
							});
						} else {
							lsfm.findById('lsLpuSectionProfile_id').getStore().removeAll();
							lsfm.findById('lsLpuSectionProfile_id').getStore().load({
								callback: function()
								{
									this.filterBy(function(rec){
										return ((Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || (!Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) && rec.get('LpuSectionProfile_endDT') > new Date())) && getRegionNick() == 'khak');
									});
									lsfm.findById('lsLpuSection_setDate').focus(true, 100);
									lM.hide();
								}
							});
						}
					}
				});
			}
		});
	},
	filterFRMOSection: function() {
		var form = this;

		var FRMOUnit_OID = null;
		var base_form = this.MainPanel.getForm();

		if (!Ext.isEmpty(form.FRMOUnit_OID)) {
			FRMOUnit_OID = form.FRMOUnit_OID;
		}
		if (!Ext.isEmpty(base_form.findField('FRMOUnit_id').getValue())) {
			FRMOUnit_OID = base_form.findField('FRMOUnit_id').getFieldValue('FRMOUnit_OID');
		}

		base_form.findField('FRMOSection_id').getStore().baseParams.FRMOUnit_OID = FRMOUnit_OID;
		base_form.findField('FRMOSection_id').lastQuery = 'This query sample that is not will never appear';

		var currentFRMOUnit_OID = base_form.findField('FRMOSection_id').getFieldValue('FRMOUnit_OID');
		if (FRMOUnit_OID && currentFRMOUnit_OID != FRMOUnit_OID) {
			base_form.findField('FRMOSection_id').clearValue();
		}
	},
	show: function()
	{
		sw.Promed.swLpuSectionEditForm.superclass.show.apply(this, arguments);

		var win = this;

		if ( !arguments[0] ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют необходимые параметры'), function() { win.hide(); });
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionEditForm'), { msg: LOAD_WAIT });
		loadMask.show();

		this.LpuBuildingType_id = null;
		this.LpuSection_FRMOBuildingOid = null;
		this.LpuSectionProfile_id = null;

		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;
		if (arguments[0].owner)
			this.owner = arguments[0].owner;
		if (arguments[0].action)
			this.action = arguments[0].action;
		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;
		else 
			this.LpuSection_id = null;
		if (arguments[0].LpuUnit_id)
			this.LpuUnit_id = arguments[0].LpuUnit_id;
		else 
			this.LpuUnit_id = null;
		if (arguments[0].LpuUnitType_id)
			this.LpuUnitType_id = arguments[0].LpuUnitType_id;
		else 
			this.LpuUnitType_id = null;
		
		if (arguments[0].LpuUnitType_Nick)
			this.LpuUnitType_Nick = arguments[0].LpuUnitType_Nick;
		else 
			this.LpuUnitType_Nick = null;
			
		if (arguments[0].LpuBuilding_id)
			this.LpuBuilding_id = arguments[0].LpuBuilding_id;
		else 
			this.LpuBuilding_id = null;
		if (arguments[0].LpuSection_pid)
			this.LpuSection_pid = arguments[0].LpuSection_pid;
		else 
			this.LpuSection_pid = null;
		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		else
			this.Lpu_id = null;
		if (arguments[0].RegisterMO_OID)
			this.RegisterMO_OID = arguments[0].RegisterMO_OID;
		else
			this.RegisterMO_OID = null;
		if (arguments[0].UnitDepartType_fid)
			this.UnitDepartType_fid = arguments[0].UnitDepartType_fid;
		else
			this.UnitDepartType_fid = null;

		if (arguments[0].FRMOUnit_OID) {
			this.FRMOUnit_OID = arguments[0].FRMOUnit_OID;
		} else {
			this.FRMOUnit_OID = null;
		}

		var form = this;
		var base_form = this.MainPanel.getForm();

		base_form.reset();

		this.LpuSectionAttributeValueGrid.getGrid().getStore().removeAll();
		this.LpuSectionMedicalCareKindGrid.getGrid().getStore().removeAll();
		this.LpuSectionServiceGrid.getGrid().getStore().removeAll();
		this.LpuSectionLpuSectionProfileGrid.getGrid().getStore().removeAll();
		this.LpuSectionMedProductTypeLinkGrid.getGrid().getStore().removeAll();
        Ext.Ajax.request({
            callback: function(options, success, response) {
                if (success) {
                    var result = Ext.util.JSON.decode(response.responseText);

                    if (!Ext.isEmpty(result[0])) {
						form.LpuBuildingType_id = result[0].LpuBuildingType_id;

                        if (result[0].LpuBuildingType_id != 9) // Морг
                        {
                            form.findById('LpuSection_PlanAutopShift').disable();
                            form.findById('LpuSection_PlanAutopShift').setContainerVisible(false);
                        } else {
                            form.findById('LpuSection_PlanAutopShift').enable();
                            form.findById('LpuSection_PlanAutopShift').setContainerVisible(true);
                        }

                        if (result[0].LpuBuildingType_id != 2) // Лечебно-амбулаторный поликлинический корпус
                        {
                            form.findById('LpuSection_PlanVisitShift').disable();
                            form.findById('LpuSection_PlanVisitShift').setContainerVisible(false);
                        } else {
                            form.findById('LpuSection_PlanVisitShift').setContainerVisible(true);
                            form.findById('LpuSection_PlanVisitShift').enable();
                        }

                        if (form.LpuUnitType_id != 13) // Скорая-медицинская помощь
                        {
                            form.findById('LpuSection_PlanTrip').disable();
                            form.findById('LpuSection_PlanTrip').setContainerVisible(false);
                            form.findById('LpuSection_KolAmbul').disable();
                            form.findById('LpuSection_KolAmbul').setContainerVisible(false);
                        } else {
                            form.findById('LpuSection_PlanTrip').enable();
                            form.findById('LpuSection_PlanTrip').setContainerVisible(true);
                            form.findById('LpuSection_KolAmbul').enable();
                            form.findById('LpuSection_KolAmbul').setContainerVisible(true);
                        }

                        if (result[0].LpuBuildingType_id != 6) // Лечебно-трудовые мастерские
                        {
                            form.findById('LpuSection_KolJob').disable();
                            form.findById('LpuSection_KolJob').setContainerVisible(false);
                        } else {
                            form.findById('LpuSection_KolJob').enable();
                            form.findById('LpuSection_KolJob').setContainerVisible(true);
                        }

                        if (result[0].LpuBuildingType_id != 25  && form.LpuUnitType_id != 3) // Лабораторно-инструментальные подразделения или параклиника
                        {
                            form.findById('LpuSection_PlanResShift').disable();
                            form.findById('LpuSection_PlanResShift').setContainerVisible(false);
                        } else {
                            form.findById('LpuSection_PlanResShift').enable();
                            form.findById('LpuSection_PlanResShift').setContainerVisible(true);
                        }

                        if (form.LpuUnitType_id != 16 && form.LpuUnitType_id != 17 && result[0].LpuBuildingType_id != 23) // Экстренная медицинская помощь, приемныое отделение, приемно-диагностическое
                        {
                            form.findById('LpuSection_PlanVisitDay').disable();
                            form.findById('LpuSection_PlanVisitDay').setContainerVisible(false);
                        } else {
                            form.findById('LpuSection_PlanVisitDay').enable();
                            form.findById('LpuSection_PlanVisitDay').setContainerVisible(true);
                        }

                        form.setFRMOFieldsVisibility();
                    }
                } else {
                    Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
                    form.hide();
                }
            }.createDelegate(this),
            params: {
                LpuBuilding_id: this.LpuBuilding_id
            },
            url:'/?c=LpuStructure&m=loadLpuBuildingType'
        });

		if ( getRegionNick() == 'kareliya' && this.LpuSection_id != null )
		{
			form.findById('mainpanel').unhideTabStripItem('tab_averageduration');
		} else {
			form.findById('mainpanel').hideTabStripItem('tab_averageduration');
		}

		if ( getRegionNick() == 'perm' || (getRegionNick() == 'krym' && !Ext.isEmpty(this.LpuUnitType_id) && this.LpuUnitType_id.inlist([ 6, 7, 9 ])) ) {
			form.findById('mainpanel').unhideTabStripItem('tab_lpusectionmedicalcarekind');
		}
		else {
			form.findById('mainpanel').hideTabStripItem('tab_lpusectionmedicalcarekind');
		}

		/*if ( getRegionNick().inlist([ 'astra', 'ekb', 'kz' ]) ) {
			form.findById('mainpanel').unhideTabStripItem('tab_lpusectionprofile');
		}
		else {
			form.findById('mainpanel').hideTabStripItem('tab_lpusectionprofile');
		}*/

		if ( !getRegionNick().inlist([ 'kz' ]) ) {
			form.findById('mainpanel').unhideTabStripItem('tab_medproduct');
		}
		else {
			form.findById('mainpanel').hideTabStripItem('tab_medproduct');
		}

		if ( getRegionNick().inlist([ 'ekb' ]) ) {
			form.findById('mainpanel').unhideTabStripItem('spec_tab');
			form.findById('HistSpec').loadData({globalFilters:{LpuSection_id:this.LpuSection_id}});
			form.findById('mainpanel').setActiveTab('spec_tab');
		}
		else {
			form.findById('mainpanel').hideTabStripItem('spec_tab');
		}

		if ( getRegionNick() == 'kz' || this.LpuSection_pid ) {
			form.findById('mainpanel').hideTabStripItem('frmo_tab');
		}
		else {
			form.findById('mainpanel').unhideTabStripItem('frmo_tab');
		}

		form.findById('mainpanel').hideTabStripItem('tab_lpusectionservice');

		form.findById('mainpanel').setActiveTab('tab_lpusectionattribute');
		form.findById('mainpanel').setActiveTab('tab_lpusectionservice');
		form.findById('mainpanel').setActiveTab('tab_medproduct');
		form.findById('mainpanel').setActiveTab('tab_lpusectionprofile');
		form.findById('mainpanel').setActiveTab('frmo_tab');
		form.findById('mainpanel').setActiveTab('er_tab');
		form.findById('mainpanel').setActiveTab('main_tab');

		base_form.findField('LpuSection_IsNotFRMO').setContainerVisible(true);
		base_form.findField('LpuSection_IsNotFRMO').fireEvent('check', base_form.findField('LpuSection_IsNotFRMO'));

		if ( getRegionNick() != 'kz' ) {
			if (this.RegisterMO_OID) {
				base_form.findField('FRMOSection_id').getStore().baseParams.RegisterMO_OID = this.RegisterMO_OID;
				base_form.findField('FRMOSection_id').getStore().baseParams.Lpu_id = null;
				base_form.findField('FRMOUnit_id').getStore().baseParams.RegisterMO_OID = this.RegisterMO_OID;
				base_form.findField('FRMOUnit_id').getStore().baseParams.Lpu_id = null;
			} else {
				base_form.findField('FRMOSection_id').getStore().baseParams.RegisterMO_OID = null;
				base_form.findField('FRMOSection_id').getStore().baseParams.Lpu_id = this.Lpu_id;
				base_form.findField('FRMOUnit_id').getStore().baseParams.RegisterMO_OID = null;
				base_form.findField('FRMOUnit_id').getStore().baseParams.Lpu_id = this.Lpu_id;
			}

			base_form.findField('FRMOSection_id').lastQuery = 'This query sample that is not will never appear';
			base_form.findField('FRMOUnit_id').lastQuery = 'This query sample that is not will never appear';

			this.filterFRMOSection();
		}

		if ( getRegionNick() == 'ufa' ) {
			var lpu_level_code = (Number(getGlobalOptions().lpu_level_code) > 0 ? getGlobalOptions().lpu_level_code : 0);

			if ( lpu_level_code != 0 ) {
				// TODO: Правильнее здесь сделать фильтрацией, а не load
				
				// если поликлиника то фильтруем (refs #7129)
				// if (form.LpuUnitType_id.inlist(['2'])){
				
				// не фильтруем вообще (refs #7523)
				if (false) {
					var params = {
						where: "where substr(cast(LpuSectionProfile_Code as char), 1, 1) = '" + lpu_level_code + "' OR LpuSectionProfile_Code='0001'",
						clause: {where: 'record["LpuSectionProfile_Code"][0] == "' + lpu_level_code + '" || record["LpuSectionProfile_Code"] == "0001"', limit: null} // Попытка намбеван
					};
				// иначе нет
				} else {
					var params = {};
				}

				this.findById('lsLpuSectionProfile_id').getStore().load({
					params: params,
					callback: function()
					{
						win.findById('lsLpuSectionProfile_id').setValue(win.findById('lsLpuSectionProfile_id').getValue());
					}
				});
			}
		} 
		
		// Закрываем вывод некоторых компонентов
		if (form.LpuUnitType_id.inlist(['1','6','7','9','3'])) // Стац + параклиника
		{
			form.findById('er_tab').enable();
			form.findById('lsOnlyStacPanel').show();
		}
		else
			if (form.LpuUnitType_id.inlist(['2','10','12']))
			{
				form.findById('er_tab').enable();
				form.findById('lsOnlyStacPanel').hide();
			}
			else 
			{
				form.findById('mainpanel').setActiveTab('main_tab');
				form.findById('er_tab').disable();
			}

		if (form.LpuUnitType_id.inlist(['1','6','7','9'])){
			form.findById('lsLpuSection_IsHTMedicalCareCheckBox').show();
		} else {
			form.findById('lsLpuSection_IsHTMedicalCareCheckBox').hide();
		}

		if (getRegionNick() == 'ekb') {
			if (form.LpuUnitType_id.inlist(['1', '6', '7', '9'])) {
				base_form.findField('LpuSection_IsNoKSG').show();
			} else {
				base_form.findField('LpuSection_IsNoKSG').hide();
			}
		}
		
		form.findById('LpuSectionEditFormPanel').getForm().reset();
		form.findById('lspidcount').setValue('');

		form.findById('lsLpuUnit_id').fireEvent('change', form.findById('lsLpuUnit_id'), form.findById('lsLpuUnit_id').getValue());

		if (this.LpuSection_pid) {
			form.findById('mainpanel').hideTabStripItem('tab_lpusectionattribute');
		} else {
			form.findById('mainpanel').unhideTabStripItem('tab_lpusectionattribute');
		}

		form.findById('lsPalliativeType_id').setContainerVisible(getRegionNick() != 'kz' && form.LpuUnitType_id.inlist([ '1', '2', '6', '7', '9', '12' ]));

		var titl = '';
		if (this.LpuSection_pid && (form.LpuUnitType_id.inlist(['1', '2', '3', '5', '6','7','9']))) // Стац
		{
			titl = langs('Подотделение');
			form.findById('lsLpuSection_IsUseReg').setVisible(true);
			form.findById('lsLpuSection_pid').setContainerVisible(true);
			form.findById('FRMPSubdivisionSection_id').setContainerVisible(false); 
			form.findById('lsLpuSectionType_id').setContainerVisible(false);
			form.setTitle(titl);
		}
		else 
		{
			titl = langs('Отделение');
			form.findById('FRMPSubdivisionSection_id').setContainerVisible(true);
			form.findById('lsLpuSection_IsUseReg').setVisible(false);
			form.findById('lsLpuSection_pid').setContainerVisible(false);
			form.findById('lsLpuSection_pid').disable();
			form.findById('lsLpuSectionType_id').setContainerVisible(true);
			
			switch (this.action)
			{
			case 'add':
				form.setTitle(titl+': Добавление');
				break;
			case 'edit':
				form.setTitle(titl+': Редактирование');
				break;
			case 'view':
				form.setTitle(titl+': Просмотр');
				break;
			}
		}
		form.findById('FRMPSubdivisionSection_id').setContainerVisible(getRegionNick() != 'kz');
		form.findById('lsLpuSection_F14').setContainerVisible(getRegionNick() != 'kz');
		form.findById('lsLpuSection_IsExportLpuRegionCheckbox').setContainerVisible(getRegionNick() == 'ekb');
		form.findById('lsLpuSection_IsConsCheckbox').setContainerVisible(getRegionNick() == 'astra');
		form.findById('lsLpuSectionCode_id').setContainerVisible(getRegionNick() == 'pskov');
		form.findById('lsLpuSection_Code').setContainerVisible(getRegionNick() != 'pskov');
		form.findById('lsMedicalCareKind_id').setContainerVisible(getRegionNick() == 'ekb');
		form.findById('lsLpuSectionProfile_fedid').setContainerVisible(getRegionNick() == 'perm');
		form.findById('lsLevelType_id').setContainerVisible(getRegionNick() == 'perm');
		form.findById('lsLpuSectionDopType_id').setContainerVisible(getRegionNick().inlist(['perm', 'ufa']));

		if (this.action=='view')
		{
			form.findById('FRMPSubdivisionSection_id').disable();
			form.findById('lsLpuSectionProfile_id').disable();
			//form.findById('lsLpuSectionType_id').disable();
			form.findById('lsLpuSection_Code').disable();
			form.findById('lsLpuSectionCode_id').disable();
			form.findById('lsLpuSection_Name').disable();
			form.findById('lsPalliativeType_id').disable();
			form.findById('lsMedicalCareKind_id').disable();
			form.findById('lsLpuSection_setDate').disable();
			form.findById('lsLpuSection_disDate').disable();
			form.findById('lsLpuSectionAge_id').disable();
			form.findById('lsLpuSectionBedProfile_id').disable();
			form.findById('lsMESLevel_id').disable();
			form.findById('lsLevelType_id').disable();
			form.findById('lsLpuSection_F14').disable();
			form.findById('lsLpuSection_IsHTMedicalCareCheckBox').disable();
			form.findById('lsLpuUnit_id').disable();
			form.findById('lsLpuSection_pid').disable();
			form.findById('lsLpuSection_Descr').disable();
			form.findById('lsLpuSection_Contacts').disable();
			form.findById('lsLpuSectionHospType_id').disable();
			form.findById('lsLpuSection_IsDirRec').disable();
			form.findById('lsLpuSection_IsQueueOnFree').disable();
			form.findById('lsLpuSection_IsUseReg').disable();
			form.findById('lsLpuSection_IsConsCheckbox').disable();
			form.findById('lsLpuSection_IsExportLpuRegionCheckbox').disable();
			form.findById('lsLpuSection_IsNoKSG').disable();
			form.findById('lsFRMOUnit_id').disable();
			form.findById('lsFRMOSection_id').disable();
			form.findById('lsLpuSection_IsNotFRMO').disable();
			form.LpuSectionLpuSectionProfileGrid.setActionDisabled('action_add', true);
			form.LpuSectionMedProductTypeLinkGrid.setActionDisabled('action_add', true);
			form.buttons[0].hide();
		}
		else
		{
			form.findById('FRMPSubdivisionSection_id').enable();
			form.findById('lsLpuSectionProfile_id').enable();
			//form.findById('lsLpuSectionType_id').enable();
			form.findById('lsLpuSection_Code').enable();
			form.findById('lsLpuSectionCode_id').enable();
			form.findById('lsLpuSection_Name').enable();
			form.findById('lsPalliativeType_id').enable();
			form.findById('lsMedicalCareKind_id').enable();
			form.findById('lsLpuSection_setDate').enable();
			form.findById('lsLpuSection_disDate').enable();
			form.findById('lsLpuSectionAge_id').enable();
			form.findById('lsLpuSectionBedProfile_id').enable();
			form.findById('lsMESLevel_id').enable();
			form.findById('lsLevelType_id').enable();
			form.findById('lsLpuSection_F14').enable();
			form.findById('lsLpuSection_IsHTMedicalCareCheckBox').enable();
			form.findById('lsLpuSection_IsConsCheckbox').enable();
			form.findById('lsLpuSection_IsExportLpuRegionCheckbox').enable();
			form.findById('lsLpuSection_IsNoKSG').enable();
			form.findById('lsLpuSection_IsNotFRMO').enable();
			form.findById('lsFRMOUnit_id').enable();
			form.findById('lsFRMOSection_id').enable();
			// Согласно задаче https://redmine.swan.perm.ru/issues/4983
			if (form.LpuUnitType_id.inlist(['1','6','7','9']) && !Ext.isEmpty(this.LpuSection_pid)) // Стац
			{
				form.findById('lsLpuUnit_id').disable();
				form.findById('lsLpuSection_pid').enable(); // по задаче https://redmine.swan.perm.ru/issues/74180
			}
			else
			{
				//form.findById('lsLpuUnit_id').enable();
				form.findById('lsLpuSection_pid').enable();
			}

			form.findById('lsLpuSection_Descr').enable();
			form.findById('lsLpuSection_Contacts').enable();
			form.findById('lsLpuSectionHospType_id').enable();
			form.findById('lsLpuSection_IsDirRec').enable();
			form.findById('lsLpuSection_IsQueueOnFree').enable();
			form.findById('lsLpuSection_IsUseReg').enable();
			form.LpuSectionLpuSectionProfileGrid.setActionDisabled('action_add', false);
			form.LpuSectionMedProductTypeLinkGrid.setActionDisabled('action_add', false);
			form.buttons[0].show();
		}
		var that = this;
		if (this.action!='add')
		{
			form.findById('tab_medproduct').dataIsLoaded = false;
			form.findById('tab_lpusectionprofile').dataIsLoaded = false;
			form.findById('tab_lpusectionservice').dataIsLoaded = false;
			form.findById('tab_lpusectionattribute').dataIsLoaded = false;
			form.findById('tab_lpusectionmedicalcarekind').dataIsLoaded = false;
			form.findById('LpuSectionEditFormPanel').getForm().load(
			{
				url: C_LPUSECTION_GET,
				params:
				{
					object: 'LpuSection',
					LpuUnit_id: form.LpuUnit_id,
					LpuSection_pid: '',
					LpuSection_id: form.LpuSection_id,
					LpuSectionProfile_id: '',
					LpuSection_Code: '',
					LpuSection_Name: '',
					LpuSection_setDate: '',
					LpuSection_disDate: '',
					LpuSectionAge_id: '',
					LpuSectionBedProfile_id: '',
					MESLevel_id: '',
					LevelType_id: '',
					LpuSection_F14: '',
					LpuSection_Descr: '',
					LpuSection_Contacts: '',
					LpuSectionHospType_id: '',
					LpuSection_IsDirRec: '',
					LpuSection_IsQueueOnFree: '',
					LpuSection_IsHTMedicalCare: '',
					pidcount:'',
					UnitDepartType_fid:''
				},
				success: function (result_form, action)
				{
					form.LpuSectionProfile_id = action.result.data.LpuSectionProfile_id;
					form.LpuSection_FRMOBuildingOid = action.result.data.LpuSection_FRMOBuildingOid;

					form.loadRemoteStores();
					if (form.action!='view')
						{
							form.onStacDetail();
						}
					// Если количество подотделений для данного отделения больше нуля
					if ((form.LpuUnit_id !=form.findById('lsLpuUnit_id').getValue()) && (form.findById('lsLpuUnit_id').getValue()!=''))
					{
						sw.swMsg.alert('Ошибка', '<span style="color:maroon;">Обнаружены ошибки в уровнях групп отделений-отделений.</span><br/>Для исправления сохраните данное (под)отделение.');
						//form.findById('lsLpuSection_pid').setValue('');
					}
					if ( !Ext.isEmpty(base_form.findField('LpuSection_FRMOBuildingOid').getValue()) ) {
						base_form.findField('LpuSection_IsNotFRMO').setValue(false);
						base_form.findField('LpuSection_IsNotFRMO').setContainerVisible(false);
					}
					form.setFRMOFieldsVisibility();
					// В любом случае ставим - и если нуловое и прочее.
					form.findById('lsLpuUnit_id').setValue(form.LpuUnit_id);
					form.findById('lsLpuUnit_id').fireEvent('change', form.findById('lsLpuUnit_id'), form.LpuUnit_id);
					if (form.findById('lspidcount').getValue() > 0)
					{
						// То нельзя ему проставить подотделение
						form.findById('lsLpuSection_pid').disable();
						//form.findById('lsLpuSection_pid').setValue('');
					}
					if ( form.findById('lsLpuSection_IsCons').getValue() == 2 ) {
						form.findById('lsLpuSection_IsConsCheckbox').setValue(true);
					}

					if ( form.findById('lsLpuSection_IsExportLpuRegion').getValue() == 2 ) {
						form.findById('lsLpuSection_IsExportLpuRegionCheckbox').setValue(true);
					}

					if (
						form.findById('lsLpuSectionProfile_id').getFieldValue('LpuSectionProfile_SysNick') == 'priem'
						|| (getRegionNick() == 'kareliya' && form.findById('lsLpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code') == '160')
					) {
						form.findById('mainpanel').unhideTabStripItem('tab_lpusectionservice');
					} else {
						form.findById('mainpanel').hideTabStripItem('tab_lpusectionservice');
					}

					if ( getRegionNick() == 'pskov' ) {
						form.loadLpuSectionCodeList();
					}

					base_form.findField('LpuSection_IsHTMedicalCareCheckBox').setValue(base_form.findField('LpuSection_IsHTMedicalCare').getValue() == 2 ? true : false);

					var FRMOUnit_id = base_form.findField('FRMOUnit_id').getValue();
					if (!Ext.isEmpty(FRMOUnit_id)) {
						base_form.findField('FRMOUnit_id').getStore().load({
							params: {
								FRMOUnit_id: FRMOUnit_id
							},
							callback: function() {
								if ( base_form.findField('FRMOUnit_id').getStore().getCount() > 0 ) {
									base_form.findField('FRMOUnit_id').setValue(FRMOUnit_id);
								}
								else {
									base_form.findField('FRMOUnit_id').clearValue();
								}

								form.setLpuSectionFRMOBuildingOid();
							}
						});
					}
					var FRMOSection_id = base_form.findField('FRMOSection_id').getValue();
					if (!Ext.isEmpty(FRMOSection_id)) {
						base_form.findField('FRMOSection_id').getStore().load({
							params: {
								FRMOSection_id: FRMOSection_id
							},
							callback: function() {
								if ( base_form.findField('FRMOSection_id').getStore().getCount() > 0 ) {
									base_form.findField('FRMOSection_id').setValue(FRMOSection_id);
								}
								else {
									base_form.findField('FRMOSection_id').clearValue();
								}

								form.setLpuSectionFRMOBuildingOid();
							}
						});
					}

					if ( getRegionNick() == 'kz' ) {
						form.loadFpList();
					}

					form.filterFRMOSection();

					loadMask.hide();
				},
				failure: function ()
				{
					loadMask.hide();
					Ext.Msg.alert(langs('Ошибка'), langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'));
					form.ownerCt.hide();
					form.ownerCt.returnFunc(form.ownerCt.owner, -1);
				}
			});
		}
		else
		{
			form.findById('tab_medproduct').dataIsLoaded = true;
			form.findById('tab_lpusectionprofile').dataIsLoaded = true;
			form.findById('tab_lpusectionservice').dataIsLoaded = true;
			form.findById('tab_lpusectionattribute').dataIsLoaded = true;
			form.findById('tab_lpusectionmedicalcarekind').dataIsLoaded = true;
			if ( getRegionNick() == 'kz' ) {
				form.loadFpList();
			}
			loadMask.hide();
			form.loadRemoteStores();
		}

		/*if(getRegionNick() != 'kz')
			this.findById('LpuSection_PlanVisitShift').setAllowBlank(Ext.isEmpty(this.UnitDepartType_fid) || this.UnitDepartType_fid != 1);*/

		//if ( getRegionNick().inlist([ 'astra', 'ekb', 'kz' ]) ) {
		if ( !Ext.isEmpty(this.LpuSection_id) ) {
			form.findById('tab_lpusectionprofile').dataIsLoaded = true;

			this.LpuSectionLpuSectionProfileGrid.loadData({
				params: {
					LpuSection_id: this.LpuSection_id
				},
				globalFilters: {
					LpuSection_id: this.LpuSection_id
				}
			});
			
			form.findById('tab_medproduct').dataIsLoaded = true;

			this.LpuSectionMedProductTypeLinkGrid.loadData({
				params: {
					LpuSection_id: this.LpuSection_id
				},
				globalFilters: {
					LpuSection_id: this.LpuSection_id
				}
			});
		}
		
		if(getRegionNick() == 'khak') {
			base_form.findField('LpuSectionProfile_id').getStore().filterBy(function(rec){
				// NGS: #196545 WAS ADDED - || rec.get('LpuSectionProfile_Code') == 134
				return ((Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || (!Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) && rec.get('LpuSectionProfile_endDT') > new Date())) || rec.get('LpuSectionProfile_Code') == 134);
			});

			base_form.findField('LpuSectionProfile_id').setBaseFilter(function(rec){
				// NGS: #196545 WAS ADDED - || rec.get('LpuSectionProfile_Code') == 134
				return ((Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || (!Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) && rec.get('LpuSectionProfile_endDT') > new Date())) || rec.get('LpuSectionProfile_Code') == 134);
			});
		}
	},
	doSave: function(options)
	{
		var form = this.findById('LpuSectionEditFormPanel');

		if ( !options ) {
			options = new Object();
		}

		if(getRegionNick() != 'kz'){
			//проверка поля "наименование"
			var controlLsLpuSection_Name = this.controlOfTheFieldLsLpuSection();
			if( controlLsLpuSection_Name ){
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					msg: controlLsLpuSection_Name,
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
		var begDate = form.findById('lsLpuSection_setDate').getValue();
		var endDate = form.findById('lsLpuSection_disDate').getValue();
		if ((begDate) && (endDate) && (begDate>endDate))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					form.findById('lsLpuSection_setDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: langs('Дата окончания не может быть меньше даты начала.'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		// Проверяем, чтобы профиль был актуален на в выбранные даты начала и окончания действия отделения
		// @task https://redmine.swan.perm.ru/issues/60125
		var
			LpuSectionProfile_begDT = form.findById('lsLpuSectionProfile_id').getFieldValue('LpuSectionProfile_begDT'),
			LpuSectionProfile_endDT = form.findById('lsLpuSectionProfile_id').getFieldValue('LpuSectionProfile_endDT');

		if ( !Ext.isEmpty(LpuSectionProfile_begDT) || !Ext.isEmpty(LpuSectionProfile_endDT) ) {
			var checkResult = false;

			if ( !Ext.isEmpty(begDate) && Ext.isEmpty(endDate) ) {
				checkResult = (
					(Ext.isEmpty(LpuSectionProfile_begDT) || typeof LpuSectionProfile_begDT != 'object' || LpuSectionProfile_begDT <= begDate)
					&& (Ext.isEmpty(LpuSectionProfile_endDT) || typeof LpuSectionProfile_endDT != 'object' || LpuSectionProfile_endDT >= begDate)
				);
			}
			else if ( Ext.isEmpty(begDate) && !Ext.isEmpty(endDate) ) {
				checkResult = (
					(Ext.isEmpty(LpuSectionProfile_begDT) || typeof LpuSectionProfile_begDT != 'object' || LpuSectionProfile_begDT <= endDate)
					&& (Ext.isEmpty(LpuSectionProfile_endDT) || typeof LpuSectionProfile_endDT != 'object' || LpuSectionProfile_endDT >= endDate)
				);
			}
			else {
				checkResult = (
					(Ext.isEmpty(LpuSectionProfile_begDT) || typeof LpuSectionProfile_begDT != 'object' || (LpuSectionProfile_begDT <= endDate && LpuSectionProfile_begDT <= begDate))
					&& (Ext.isEmpty(LpuSectionProfile_endDT) || typeof LpuSectionProfile_endDT != 'object' || (LpuSectionProfile_endDT >= endDate && LpuSectionProfile_endDT >= begDate))
				);
			}

			if ( checkResult == false ) {
				var errorText = '<div>Указанный профиль является недействующим в период действия отделения.</div>';

				errorText = errorText + '<div>Дата начала действия профиля: ' + (!Ext.isEmpty(LpuSectionProfile_begDT) ? Ext.util.Format.date(LpuSectionProfile_begDT, 'd.m.Y') : 'не указана') + '</div>';
				errorText = errorText + '<div>Дата окончания действия профиля: ' + (!Ext.isEmpty(LpuSectionProfile_endDT) ? Ext.util.Format.date(LpuSectionProfile_endDT, 'd.m.Y') : 'не указана') + '</div>';

				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						form.findById('lsLpuSectionProfile_id').markInvalid();
						form.findById('lsLpuSectionProfile_id').focus(false);
					},
					icon: Ext.Msg.ERROR,
					msg: errorText,
					title: ERR_INVFIELDS_TIT
				});
				
				return false;
			}
		}

		if (
			!options.ignoreCheckPalliativeMedicalCareKind
			&& !Ext.isEmpty(this.LpuUnitType_id) && this.LpuUnitType_id.inlist([ '1', '2', '6', '7', '9', '12' ])
			&& getRegionNick().inlist([ 'perm' ])
		) {
			var palliativeMedicalCareKind = false;

			this.LpuSectionMedicalCareKindGrid.getGrid().getStore().each(function(rec) {
				if ( rec.get('MedicalCareKind_Code') == 4 ) {
					palliativeMedicalCareKind = true;
				}
			});

			if ( Ext.isEmpty(form.findById('lsPalliativeType_id').getValue()) && palliativeMedicalCareKind == true ) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreCheckPalliativeMedicalCareKind = true;
							this.doSave(options);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'Для отделения указан вид оказания медицинской помощи «Паллиативная медицинская помощь». Для корректного форм30ирования отчетов проверьте значение в поле «Вид отделения ПМП» на вкладке «Основные данные». Продолжить сохранение?',
					title: langs('Вопрос')
				});
				return false;
			}
		}
		
		this.submit();
	},
	submit: function()
	{
		var form = this.findById('LpuSectionEditFormPanel');
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionEditForm'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		if ( form.findById('lsLpuSection_IsConsCheckbox').getValue() == true ) {
			form.findById('lsLpuSection_IsCons').setValue(2);
		}
		else {
			form.findById('lsLpuSection_IsCons').setValue(1);
		}

		if ( form.findById('lsLpuSection_IsExportLpuRegionCheckbox').getValue() == true ) {
			form.findById('lsLpuSection_IsExportLpuRegion').setValue(2);
		}
		else {
			form.findById('lsLpuSection_IsExportLpuRegion').setValue(1);
		}

		var params = {
			LpuUnit_id: form.findById('lsLpuUnit_id').getValue(),
			LpuSection_pid: form.findById('lsLpuSection_pid').getValue()
		}

		if (
			(
				form.findById('lsLpuSectionProfile_id').getFieldValue('LpuSectionProfile_SysNick') == 'priem'
				|| (getRegionNick() == 'kareliya' && form.findById('lsLpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code') == '160')
			)
			&& this.findById('tab_lpusectionservice').dataIsLoaded == true
		) {
			var LpuSectionServiceGrid = this.LpuSectionServiceGrid.getGrid();
			LpuSectionServiceGrid.getStore().clearFilter();

			if ( LpuSectionServiceGrid.getStore().getCount() > 0 ) {
				var LpuSectionServiceData = getStoreRecords(LpuSectionServiceGrid.getStore(), {
					exceptionFields: [
						'LpuSection_Name',
						'LpuBuilding_Name'
					]
				});

				params.LpuSectionServiceData = Ext.util.JSON.encode(LpuSectionServiceData);

				LpuSectionServiceGrid.getStore().filterBy(function(rec) {
					return (Number(rec.get('RecordStatus_Code')) != 3);
				});

			}
		}
				
		if ( getRegionNick().inlist([ 'perm' ]) || (getRegionNick().inlist([ 'krym' ]) && !Ext.isEmpty(this.LpuUnitType_id) && this.LpuUnitType_id.inlist([ 6, 7, 9 ])) ) {
			var grid = this.LpuSectionMedicalCareKindGrid.getGrid();
			var LpuSectionMedicalCareKindData = [];
			grid.getStore().clearFilter();
			
			if ( grid.getStore().getCount() > 0 ) {
				LpuSectionMedicalCareKindData = getStoreRecords(grid.getStore());
				this.filterLpuSectionMedicalCareKindGrid();
			}
			
			params.LpuSectionMedicalCareKindData = Ext.util.JSON.encode(LpuSectionMedicalCareKindData);
		}		

		if ( /*getRegionNick().inlist([ 'astra', 'ekb', 'kz' ]) &&*/ this.findById('tab_lpusectionprofile').dataIsLoaded == true ) {
			var LpuSectionLpuSectionProfileGrid = this.LpuSectionLpuSectionProfileGrid.getGrid();

			LpuSectionLpuSectionProfileGrid.getStore().clearFilter();

			// Собираем список дополнительных профилей отделения
			if ( LpuSectionLpuSectionProfileGrid.getStore().getCount() > 0 ) {
				var lpuSectionProfileData = getStoreRecords(LpuSectionLpuSectionProfileGrid.getStore(), {
					convertDateFields: true,
					exceptionFields: [
						 'LpuSectionProfile_Name'
						,'LpuSectionProfile_Code'
					]
				});

				params.lpuSectionProfileData = Ext.util.JSON.encode(lpuSectionProfileData);

				LpuSectionLpuSectionProfileGrid.getStore().filterBy(function(rec) {
					return !(Number(rec.get('RecordStatus_Code')) == 3);
				});
			}
		}

		if ( !getRegionNick().inlist([ 'kz' ]) && this.findById('tab_medproduct').dataIsLoaded == true ) {
			var LpuSectionMedProductTypeLinkGrid = this.LpuSectionMedProductTypeLinkGrid.getGrid();

			LpuSectionMedProductTypeLinkGrid.getStore().clearFilter();

			// Собираем список дополнительных профилей отделения
			if ( LpuSectionMedProductTypeLinkGrid.getStore().getCount() > 0 ) {
				var lpuSectionMedProductTypeLinkData = getStoreRecords(LpuSectionMedProductTypeLinkGrid.getStore(), {
					convertDateFields: true,
					exceptionFields: [
						 'MedProductType_Name'
					]
				});


				params.lpuSectionMedProductTypeLinkData = Ext.util.JSON.encode(lpuSectionMedProductTypeLinkData);

				LpuSectionMedProductTypeLinkGrid.getStore().filterBy(function(rec) {
					return !(Number(rec.get('RecordStatus_Code')) == 3);
				});
			}
		}

		if ( this.findById('tab_lpusectionattribute').dataIsLoaded == true && this.LpuSectionAttributeValueGrid.formMode == 'local' ) {
			var LpuSectionAttributeValueGrid = this.LpuSectionAttributeValueGrid.getGrid();

			LpuSectionAttributeValueGrid.getStore().clearFilter();
			LpuSectionAttributeValueGrid.getStore().filterBy(function(rec) {
				return rec.get('RecordStatus_Code') !== null;
			});

			if ( LpuSectionAttributeValueGrid.getStore().getCount() > 0 ) {
				var AttributeSignValueData = getStoreRecords(LpuSectionAttributeValueGrid.getStore(), {
					convertDateFields: true,
					exceptionFields: [
						 'AttributeSign_Code'
						,'AttributeSign_Name'
						,'AttributeValueLoadParams'
					]
				});

				params.AttributeSignValueData = Ext.util.JSON.encode(AttributeSignValueData);
			}
			LpuSectionAttributeValueGrid.getStore().filterBy(function(rec) {
				return !(Number(rec.get('RecordStatus_Code')) == 3);
			});
		}

		form.getForm().submit(
			{
				params: params,
				failure: function(result_form, action) 
				{
					loadMask.hide();
				},
				success: function(result_form, action)
				{
					loadMask.hide();
					if (action.result)
					{
						if (action.result.LpuSection_id)
						{
							form.ownerCt.hide();
							form.ownerCt.returnFunc(form.ownerCt.owner, action.result.LpuSection_id);
						}
						else
							Ext.Msg.alert('Ошибка #100004', 'При сохранении произошла ошибка!');
					}
					else
						Ext.Msg.alert('Ошибка #100005', 'При сохранении произошла ошибка!');
				}
			});
	},
	onStacDetail: function ()
	{
		var field_value = this.LpuUnitType_id;
		if (field_value)
		{
			if (field_value == 2)
			{
				this.findById('lsLpuSectionAge_id').enable();
				this.findById('lsLpuSectionBedProfile_id').disable();
				this.findById('lsMESLevel_id').disable();
				this.findById('lsLevelType_id').disable();
			}
			else if (field_value.inlist(['1','6','7','9']))
			{
				this.findById('lsLpuSectionAge_id').enable();
				this.findById('lsMESLevel_id').enable();
				this.findById('lsLevelType_id').enable();
			}
			else
			{
				this.findById('lsLpuSectionAge_id').disable();
				this.findById('lsLpuSectionBedProfile_id').disable();
				this.findById('lsMESLevel_id').disable();
				this.findById('lsLevelType_id').disable();
			}
		}
	},
	openSectionAverageDurationEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( getWnd('swSectionAverageDurationEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования средней продолжительности лечения уже открыто'));
			return false;
		}

		var wnd = this;
		var grid = this.SectionAverageDurationGrid.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			grid.getStore().reload();
		};
		params.formMode = 'remote';
		params.formParams = new Object();
		
		if ( action == 'add' ) {
			params.formParams.LpuSection_id = grid.getStore().baseParams.LpuSection_id || wnd.LpuSection_id;
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('SectionAverageDuration_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();
			
			params.formParams = record.data;
			params.formParams.LpuSection_id = grid.getStore().baseParams.LpuSection_id || wnd.LpuSection_id;
		}

		getWnd('swSectionAverageDurationEditWindow').show(params);
	},
	openLpuSectionMedicalCareKindEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( getWnd('swLpuSectionMedicalCareKindEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования вида оказания МП уже открыто'));
			return false;
		}

		var
			formParams = new Object(),
			grid = this.LpuSectionMedicalCareKindGrid.getGrid(),
			params = new Object(),
			wnd = this;

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.LpuSectionMedicalCareKindData != 'object' ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют необходимые данные'));
				return false;
			}

			data.LpuSectionMedicalCareKindData.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) {
				return (rec.get('LpuSectionMedicalCareKind_id') == data.LpuSectionMedicalCareKindData.LpuSectionMedicalCareKind_id);
			});

			if ( index == -1 ) {
				data.LpuSectionMedicalCareKindData.LpuSectionMedicalCareKind_id = -swGenTempId(grid.getStore());
			}

			// Добавляем проверку на дубли
			grid.getStore().clearFilter();

			var checkIndex = grid.getStore().findBy(function(rec) {
				if ( getRegionNick() == 'krym' ) {
					return (
						data.LpuSectionMedicalCareKindData.LpuSectionMedicalCareKind_id != rec.get('LpuSectionMedicalCareKind_id')
						&& (Ext.isEmpty(data.LpuSectionMedicalCareKindData.LpuSectionMedicalCareKind_endDate) || rec.get('LpuSectionMedicalCareKind_begDate') <= data.LpuSectionMedicalCareKindData.LpuSectionMedicalCareKind_endDate)
						&& (Ext.isEmpty(rec.get('LpuSectionMedicalCareKind_endDate')) || data.LpuSectionMedicalCareKindData.LpuSectionMedicalCareKind_begDate <= rec.get('LpuSectionMedicalCareKind_endDate'))
					);
				}
				else {
					return (
						data.LpuSectionMedicalCareKindData.LpuSectionMedicalCareKind_id != rec.get('LpuSectionMedicalCareKind_id')
						&& (Ext.isEmpty(data.LpuSectionMedicalCareKindData.LpuSectionMedicalCareKind_endDate) || rec.get('LpuSectionMedicalCareKind_begDate') <= data.LpuSectionMedicalCareKindData.LpuSectionMedicalCareKind_endDate)
						&& (Ext.isEmpty(rec.get('LpuSectionMedicalCareKind_endDate')) || data.LpuSectionMedicalCareKindData.LpuSectionMedicalCareKind_begDate <= rec.get('LpuSectionMedicalCareKind_endDate'))
						&& data.LpuSectionMedicalCareKindData.MedicalCareKind_id == rec.get('MedicalCareKind_id')
					);
				}
			});

			wnd.filterLpuSectionMedicalCareKindGrid();

			if ( checkIndex >= 0 ) {
				var errorText;

				if ( getRegionNick() == 'krym' ) {
					errorText = 'Запрещено вводить записи с пересекающимися периодами';
				}
				else {
					errorText = 'Запрещено вводить записи с одним видом МП и пересекающимися периодами';
				}

				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: errorText,
					title: ERR_INVFIELDS_TIT
				});
				
				return false;
			}

			if ( index >= 0 ) {
				var record = grid.getStore().getAt(index);

				if ( record.get('RecordStatus_Code') == 1 ) {
					data.LpuSectionMedicalCareKindData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.LpuSectionMedicalCareKindData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && Ext.isEmpty(grid.getStore().getAt(0).get('LpuSectionMedicalCareKind_id')) ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData([ data.LpuSectionMedicalCareKindData ], true);
			}

			setTimeout(function(){wnd.filterLpuSectionMedicalCareKindGrid()}, 250);

			return true;
		};
		params.formMode = 'local';

		if ( action == 'add' ) {
			formParams.LpuSectionMedicalCareKind_begDate = wnd.findById('lsLpuSection_setDate').getValue();
			formParams.LpuSectionMedicalCareKind_endDate = wnd.findById('lsLpuSection_disDate').getValue();
			params.formParams = formParams;
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('LpuSectionMedicalCareKind_id') ) {
				return false;
			}

			var selectedRecord = grid.getSelectionModel().getSelected();

			formParams = selectedRecord.data;			
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.formParams = formParams;
		getWnd('swLpuSectionMedicalCareKindEditWindow').show(params);

		return true;
	},
	deleteLpuSectionMedicalCareKind: function() {
		var wnd = this;

		if ( wnd.action == 'view' ) {
			return false;
		}
				
		function formatDate(value){
			return value ? new Date(value).dateFormat('d.m.Y') : '';
		};

		var grid = wnd.LpuSectionMedicalCareKindGrid.getGrid();

		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || Ext.isEmpty(grid.getSelectionModel().getSelected().get('LpuSectionMedicalCareKind_id')) ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
						break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();
							wnd.filterLpuSectionMedicalCareKindGrid();
						break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: '<div>' + langs('Удалить запись?') + '</div>' +
				'<div>' + record.get('MedicalCareKind_Name') + ' / ' + 
				formatDate(record.get('LpuSectionMedicalCareKind_begDate')) + ' ... ' + 
				formatDate(record.get('LpuSectionMedicalCareKind_endDate')) + '</div>',
			title: langs('Вопрос')
		});

		return true;
	},
	deleteLpuSectionLpuSectionProfile: function() {
		var wnd = this;

		if ( wnd.action == 'view' ) {
			return false;
		}

		var grid = wnd.LpuSectionLpuSectionProfileGrid.getGrid();

		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || Ext.isEmpty(grid.getSelectionModel().getSelected().get('LpuSectionLpuSectionProfile_id')) ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
						break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								return (Number(rec.get('RecordStatus_Code')) != 3);
							});
						break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить дополнительный профиль отделения?'),
			title: langs('Вопрос')
		});

		return true;
	},
	deleteLpuSectionMedProductTypeLink: function() {
		var wnd = this;

		if ( wnd.action == 'view' ) {
			return false;
		}

		var grid = wnd.LpuSectionMedProductTypeLinkGrid.getGrid();

		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || Ext.isEmpty(grid.getSelectionModel().getSelected().get('LpuSectionMedProductTypeLink_id')) ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
						break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								return (Number(rec.get('RecordStatus_Code')) != 3);
							});
						break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить мед. оборудование?'),
			title: langs('Вопрос')
		});

		return true;
	},
	openLpuSectionLpuSectionProfileEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( getWnd('swLpuSectionLpuSectionProfileEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования дополнительного профиля уже открыто'));
			return false;
		}

		var
			formParams = new Object(),
			grid = this.LpuSectionLpuSectionProfileGrid.getGrid(),
			params = new Object(),
			wnd = this;

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.lpuSectionProfileData != 'object' ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют необходимые данные'));
				return false;
			}

			var combo = wnd.findById('lsLpuSectionProfile_id');
			if ( combo.getValue() != '' || combo.getValue() != null ) {
				var idx = combo.getStore().indexOfId(combo.getValue());
				var row = combo.getStore().getAt(idx);
				if (row.data.LpuSectionProfile_id == data.lpuSectionProfileData.LpuSectionProfile_id) {
					sw.swMsg.alert(langs('Ошибка'), langs('Профиль добавить нельзя. Наименование профиля указано в качестве основного профиля отделения.'));
					return false;
				}
			}

			data.lpuSectionProfileData.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) {
				return (rec.get('LpuSectionLpuSectionProfile_id') == data.lpuSectionProfileData.LpuSectionLpuSectionProfile_id);
			});

			if ( index >= 0 ) {
				var record = grid.getStore().getAt(index);

				if ( record.get('RecordStatus_Code') == 1 ) {
					data.lpuSectionProfileData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.lpuSectionProfileData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && Ext.isEmpty(grid.getStore().getAt(0).get('LpuSectionLpuSectionProfile_id')) ) {
					grid.getStore().removeAll();
				}
				
				data.lpuSectionProfileData.LpuSectionLpuSectionProfile_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.lpuSectionProfileData ], true);
			}

			return true;
		};
		params.formMode = 'local';
		params.LpuSection_disDate = wnd.findById('lsLpuSection_disDate').getValue();
		params.LpuSection_setDate = wnd.findById('lsLpuSection_setDate').getValue();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('LpuSectionLpuSectionProfile_id') ) {
				return false;
			}

			var selectedRecord = grid.getSelectionModel().getSelected();

			formParams = selectedRecord.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.formParams = formParams;

		getWnd('swLpuSectionLpuSectionProfileEditWindow').show(params);

		return true;
	},
	openLpuSectionMedProductTypeLinkEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( getWnd('swLpuSectionMedProductTypeLinkEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования мед. оборудования уже открыто'));
			return false;
		}

		var
			formParams = new Object(),
			grid = this.LpuSectionMedProductTypeLinkGrid.getGrid(),
			params = new Object(),
			wnd = this;

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.lpuSectionMedProductTypeLinkData != 'object' ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Отсутствуют необходимые данные'));
				return false;
			}
			
			var checkIdx = grid.getStore().findBy(function(rec) {
				return (
					rec.get('LpuSectionMedProductTypeLink_id') != data.lpuSectionMedProductTypeLinkData.LpuSectionMedProductTypeLink_id
					&& rec.get('MedProductType_id') == data.lpuSectionMedProductTypeLinkData.MedProductType_id
					&& (
						(
							rec.get('LpuSectionMedProductTypeLink_begDT') >= data.lpuSectionMedProductTypeLinkData.LpuSectionMedProductTypeLink_begDT
							&& (Ext.isEmpty(data.lpuSectionMedProductTypeLinkData.LpuSectionMedProductTypeLink_endDT) || rec.get('LpuSectionMedProductTypeLink_begDT') <= data.lpuSectionMedProductTypeLinkData.LpuSectionMedProductTypeLink_endDT)
						) || (
							!Ext.isEmpty(rec.get('LpuSectionMedProductTypeLink_endDT'))
							&& rec.get('LpuSectionMedProductTypeLink_endDT') >= data.lpuSectionMedProductTypeLinkData.LpuSectionMedProductTypeLink_begDT
							&& (Ext.isEmpty(data.lpuSectionMedProductTypeLinkData.LpuSectionMedProductTypeLink_endDT) || rec.get('LpuSectionMedProductTypeLink_endDT') <= data.lpuSectionMedProductTypeLinkData.LpuSectionMedProductTypeLink_endDT)
						) || (
							Ext.isEmpty(rec.get('LpuSectionMedProductTypeLink_endDT'))
							&& rec.get('LpuSectionMedProductTypeLink_begDT') <= data.lpuSectionMedProductTypeLinkData.LpuSectionMedProductTypeLink_begDT
						)
					)
				);
			});
			if (checkIdx >= 0) {
				var rec = grid.getStore().getAt(checkIdx);
				var error = 'В список медицинского оборудования уже внесена информация по выбранному типу оборудования: ' + rec.get('MedProductType_Name') + ' период действия: ' + rec.get('LpuSectionMedProductTypeLink_begDT').format('d.m.Y');
				if (!Ext.isEmpty(rec.get('LpuSectionMedProductTypeLink_endDT'))) {
					error += '  по ' + rec.get('LpuSectionMedProductTypeLink_endDT').format('d.m.Y');
				}
				error += '. Необходимо изменить тип оборудования или период действия.';
				sw.swMsg.alert(langs('Ошибка'), langs(error));
				return false;
			}
			
			data.lpuSectionMedProductTypeLinkData.RecordStatus_Code = 0;

			var index = grid.getStore().findBy(function(rec) {
				return (rec.get('LpuSectionMedProductTypeLink_id') == data.lpuSectionMedProductTypeLinkData.LpuSectionMedProductTypeLink_id);
			});

			if ( index >= 0 ) {
				var record = grid.getStore().getAt(index);

				if ( record.get('RecordStatus_Code') == 1 ) {
					data.lpuSectionMedProductTypeLinkData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.lpuSectionMedProductTypeLinkData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && Ext.isEmpty(grid.getStore().getAt(0).get('LpuSectionMedProductTypeLink_id')) ) {
					grid.getStore().removeAll();
				}
				
				data.lpuSectionMedProductTypeLinkData.LpuSectionMedProductTypeLink_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.lpuSectionMedProductTypeLinkData ], true);
			}

			return true;
		};
		params.formMode = 'local';
		params.LpuSection_disDate = wnd.findById('lsLpuSection_disDate').getValue();
		params.LpuSection_setDate = wnd.findById('lsLpuSection_setDate').getValue();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('LpuSectionMedProductTypeLink_id') ) {
				return false;
			}

			var selectedRecord = grid.getSelectionModel().getSelected();

			formParams = selectedRecord.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};
		}

		params.formParams = formParams;

		getWnd('swLpuSectionMedProductTypeLinkEditWindow').show(params);

		return true;
	},
	openLpuSectionServiceEditWindow: function(action){
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		var form = this;
		var base_form = this.MainPanel.getForm();
		var grid = this.LpuSectionServiceGrid.getGrid();

		var params = new Object();
		params.action = action;
		params.LpuUnitType_id = this.LpuUnitType_id;
		params.formParams = new Object();

		if (action == 'add') {
			params.formParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		} else {
			var record = grid.getSelectionModel().getSelected();
			if ( !record || !record.get('LpuSectionService_id') ) {
				return false;
			}
			params.formParams.LpuSectionService_id = record.get('LpuSectionService_id');
		}

		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.LpuSectionServiceData != 'object' ) {
				return false;
			}

			var index = grid.getStore().findBy(function(rec){
				return (rec.get('LpuSection_did')==data.LpuSectionServiceData.LpuSection_did && rec.get('LpuSectionService_id')!=data.LpuSectionServiceData.LpuSectionService_id);
			});
			if (index >= 0) {
				sw.swMsg.alert(langs('Ошибка'), langs('Выбранное отделение уже указано'));
				return false;
			}

			data.LpuSectionServiceData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.LpuSectionServiceData.LpuSectionService_id);

			Ext.Ajax.request({
				url: '/?c=LpuStructure&m=getRowLpuSectionService',
				params: data.LpuSectionServiceData,
				callback: function(options, success, response) {
					if (!success) {
						sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
						return false;
					}
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( typeof record == 'object' ) {
						if ( record.get('RecordStatus_Code') == 1 ) {
							response_obj.data.RecordStatus_Code = 2;
						}

						var grid_fields = new Array();

						grid.getStore().fields.eachKey(function(key, item) {
							grid_fields.push(key);
						});

						for ( i = 0; i < grid_fields.length; i++ ) {
							record.set(grid_fields[i], response_obj.data[grid_fields[i]]);
						}

						record.commit();
					} else {
						if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('LpuSectionService_id') ) {
							grid.getStore().removeAll();
						}
						response_obj.data.LpuSectionService_id = -swGenTempId(grid.getStore());

						grid.getStore().loadData([response_obj.data], true);
					}
				}
			});
		}

		getWnd('swLpuSectionServiceEditWindow').show(params);
	},
	deleteLpuSectionService: function(action){
		var grid = this.LpuSectionServiceGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('LpuSectionService_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					switch ( Number(record.get('RecordStatus_Code')) ) {
						case 0:
							grid.getStore().remove(record);
							break;

						case 1:
						case 2:
							record.set('RecordStatus_Code', 3);
							record.commit();

							grid.getStore().filterBy(function(rec) {
								return (Number(rec.get('RecordStatus_Code')) != 3);
							});
							break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			},
			icon:Ext.MessageBox.QUESTION,
			msg:langs('Вы хотите удалить запись?'),
			title:langs('Подтверждение')
		});
	},
	controlOfTheFieldLsLpuSection: function(){
    	var form = this.findById('LpuSectionEditFormPanel');

		var LLBPass = form.findById('lsLpuSection_Name').getValue(); // поле "Наименование"
		// удалим пробелы в начале и конце строки
		LLBPass = LLBPass.trim();
		form.findById('lsLpuSection_Name').setValue(LLBPass);
		
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
	setFRMOFieldsVisibility: function() {
		if ( getRegionNick() == 'kz' ) {
			return false;
		}

		var base_form = this.MainPanel.getForm();

		var LpuSection_IsNotFRMO = base_form.findField('LpuSection_IsNotFRMO').getValue();

		base_form.findField('FRMOUnit_id').setContainerVisible(!LpuSection_IsNotFRMO);
		base_form.findField('FRMOSection_id').setContainerVisible(!LpuSection_IsNotFRMO);
		base_form.findField('LpuSection_FRMOBuildingOid').setContainerVisible(!LpuSection_IsNotFRMO);
		base_form.findField('FRMPSubdivision_id').setContainerVisible(!LpuSection_IsNotFRMO && getRegionNick() != 'kz' && !this.LpuSection_pid);
		base_form.findField('LpuSection_PlanVisitShift').setContainerVisible(!LpuSection_IsNotFRMO && this.LpuBuildingType_id == 2 && getRegionNick() != 'kz' && !this.LpuSection_pid);
		base_form.findField('LpuSection_PlanResShift').setContainerVisible(!LpuSection_IsNotFRMO && (this.LpuBuildingType_id == 25 || this.LpuUnitType_id == 3) && getRegionNick() != 'kz' && !this.LpuSection_pid);
		base_form.findField('LpuSection_PlanTrip').setContainerVisible(!LpuSection_IsNotFRMO && this.LpuUnitType_id == 13 && getRegionNick() != 'kz' && !this.LpuSection_pid);
		base_form.findField('LpuSection_KolAmbul').setContainerVisible(!LpuSection_IsNotFRMO && this.LpuUnitType_id == 13 && getRegionNick() != 'kz' && !this.LpuSection_pid);

		if ( LpuSection_IsNotFRMO ) {
			base_form.findField('FRMOUnit_id').clearValue();
			base_form.findField('FRMOSection_id').clearValue();
			base_form.findField('LpuSection_FRMOBuildingOid').setValue('');
			base_form.findField('FRMPSubdivision_id').clearValue();
			base_form.findField('LpuSection_PlanVisitShift').setValue('');
			base_form.findField('LpuSection_PlanResShift').setValue('');
			base_form.findField('LpuSection_PlanTrip').setValue('');
			base_form.findField('LpuSection_KolAmbul').setValue('');
		}

		base_form.findField('FRMPSubdivision_id').setAllowBlank(!base_form.findField('FRMPSubdivision_id').isVisible());
		base_form.findField('LpuSection_PlanVisitShift').setAllowBlank(!base_form.findField('LpuSection_PlanVisitShift').isVisible());
		base_form.findField('LpuSection_PlanResShift').setAllowBlank(!base_form.findField('LpuSection_PlanResShift').isVisible());
		base_form.findField('LpuSection_PlanTrip').setAllowBlank(!base_form.findField('LpuSection_PlanTrip').isVisible());
		base_form.findField('LpuSection_KolAmbul').setAllowBlank(!base_form.findField('LpuSection_KolAmbul').isVisible());
	},
	setLpuSectionFRMOBuildingOid: function() {
		if ( getRegionNick() == 'kz' || this.LpuSection_FRMOBuildingOid ) {
			return false;
		}

		var
			base_form = this.MainPanel.getForm(),
			LpuSection_FRMOBuildingOid = '';

		if ( !Ext.isEmpty(base_form.findField('FRMOSection_id').getValue()) ) {
			LpuSection_FRMOBuildingOid = base_form.findField('FRMOSection_id').getFieldValue('FRMOSection_OID');
		}

		if ( Ext.isEmpty(LpuSection_FRMOBuildingOid) && !Ext.isEmpty(base_form.findField('FRMOUnit_id').getValue()) ) {
			LpuSection_FRMOBuildingOid = base_form.findField('FRMOUnit_id').getFieldValue('FRMOUnit_OID');
		}

		base_form.findField('LpuSection_FRMOBuildingOid').setValue(LpuSection_FRMOBuildingOid);
	},
	initComponent: function()
	{
		var wnd = this;
		
		function SetLpuSectionName(field)
		{
			if ( !field.disabled ) {
				// Тип подразделения
				// 28.08.2009
				var txt = field.ownerCt.ownerCt.ownerCt.ownerCt.LpuUnitType_Nick;
				if (!txt)
					txt = '';
				// Профиль отделения
				combo = field.ownerCt.ownerCt.ownerCt.findById('lsLpuSectionProfile_id');
				if ( combo.getValue() == '' || combo.getValue() == null )
					return;
				// Получаем индекс выбранной записи
				idx = combo.getStore().indexOfId(combo.getValue());
				if (idx<0)
					idx = combo.getStore().findBy(function(rec) { return rec.get('LpuSectionProfile_id') == combo.getValue(); });
				if (idx<0)
					return;
				var row = combo.getStore().getAt(idx);
				field.setValue(String(row.data.LpuSectionProfile_Name).trim() + '. ' + txt);
			}
		}
		
		this.SectionAverageDurationGrid = new sw.Promed.ViewFrame(
		{
			id: wnd.id+'SectionAverageDurationGrid',
			title: '',
			scheme: 'r10',
			object: 'SectionAverageDuration',
			region: 'center',
			dataUrl: '/?c=LpuStructure&m=loadSectionAverageDurationGrid',
			paging: false,
			autoLoadData: false,
			border: false,
			stringfields:
			[
				{name: 'SectionAverageDuration_id', type: 'int', header: 'SectionAverageDuration_id', key: true, hidden: true},
				{name: 'LpuSection_id', type: 'int', hidden:true},
				{name: 'SectionAverageDuration_Duration', header: langs('Средняя длительность'), width: 200},
				{name: 'SectionAverageDuration_begDate', type: 'date', header: langs('Дата начала'), width: 100},
				{name: 'SectionAverageDuration_endDate', type: 'date', header: langs('Дата окончания'), width: 100}
			],
			actions:
			[
				{name:'action_add', handler: function() { wnd.openSectionAverageDurationEditWindow('add'); }},
				{name:'action_edit', handler: function() { wnd.openSectionAverageDurationEditWindow('edit'); }},
				{name:'action_view', handler: function() { wnd.openSectionAverageDurationEditWindow('view'); }},
				{name:'action_delete'}
				
			],
			onLoadData: function()
			{
			},
			onDblClick: function()
			{
			},
			onEnter: function()
			{
			},
			onRowSelect: function(sm,rowIdx,record)
			{
			}
		});

		this.LpuSectionLpuSectionProfileGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { wnd.openLpuSectionLpuSectionProfileEditWindow('add'); }},
				{ name: 'action_edit', handler: function() { wnd.openLpuSectionLpuSectionProfileEditWindow('edit'); }},
				{ name: 'action_view', handler: function() { wnd.openLpuSectionLpuSectionProfileEditWindow('view'); }},
				{ name: 'action_delete', handler: function() { wnd.deleteLpuSectionLpuSectionProfile(); }},
				{ name: 'action_refresh', disabled: true, hidden: true }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=LpuStructure&m=loadLpuSectionLpuSectionProfileGrid',
			id: wnd.id + 'LpuSectionLpuSectionProfileGrid',
			object: 'LpuSectionLpuSectionProfile',
			onDblClick: function() {
				//
			},
			onEnter: function() {
				//
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm,rowIdx,record) {
				//
			},
			paging: false,
			region: 'center',
			scheme: 'dbo',
			stringfields: [
				{ name: 'LpuSectionLpuSectionProfile_id', type: 'int', header: 'LpuSectionLpuSectionProfile_id', key: true, hidden: true },
				{ name: 'LpuSectionProfile_id', type: 'int', hidden: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'LpuSectionProfile_Code', header: langs('Код'), width: 100 },
				{ name: 'LpuSectionProfile_Name', header: langs('Профиль'), width: 200, id: 'autoexpand' },
				{ name: 'LpuSectionLpuSectionProfile_begDate', type: 'date', header: langs('Дата начала'), width: 100 },
				{ name: 'LpuSectionLpuSectionProfile_endDate', type: 'date', header: langs('Дата окончания'), width: 100 }
			],
			title: ''
		});

		this.LpuSectionMedProductTypeLinkGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { wnd.openLpuSectionMedProductTypeLinkEditWindow('add'); }},
				{ name: 'action_edit', handler: function() { wnd.openLpuSectionMedProductTypeLinkEditWindow('edit'); }},
				{ name: 'action_view', handler: function() { wnd.openLpuSectionMedProductTypeLinkEditWindow('view'); }},
				{ name: 'action_delete', handler: function() { wnd.deleteLpuSectionMedProductTypeLink(); }},
				{ name: 'action_refresh', disabled: true, hidden: true }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=LpuStructure&m=loadLpuSectionMedProductTypeLinkGrid',
			id: wnd.id + 'LpuSectionMedProductTypeLinkGrid',
			object: 'LpuSectionMedProductTypeLink',
			onDblClick: function() {
				//
			},
			onEnter: function() {
				//
			},
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm,rowIdx,record) {
				//
			},
			paging: false,
			region: 'center',
			scheme: 'dbo',
			stringfields: [
				{ name: 'LpuSectionMedProductTypeLink_id', type: 'int', header: 'LpuSectionMedProductTypeLink_id', key: true, hidden: true },
				{ name: 'MedProductType_id', type: 'int', hidden: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'MedProductType_Name', header: langs('Тип оборудования'), width: 200, id: 'autoexpand' },
				{ name: 'LpuSectionMedProductTypeLink_TotalAmount', type: 'int', header: langs('Общее количество'), width: 100 },
				{ name: 'LpuSectionMedProductTypeLink_IncludePatientKVI', type: 'int', header: langs('В т.ч. для КВИ'), width: 100 },
				{ name: 'LpuSectionMedProductTypeLink_IncludeReanimation', type: 'int', header: langs('В т.ч. для реанимации'), width: 100 },
				{ name: 'LpuSectionMedProductTypeLink_begDT', type: 'date', header: langs('Дата начала'), width: 100 },
				{ name: 'LpuSectionMedProductTypeLink_endDT', type: 'date', header: langs('Дата окончания'), width: 100 }
			],
			title: ''
		});

		this.LpuSectionMedicalCareKindGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { wnd.openLpuSectionMedicalCareKindEditWindow('add'); }},
				{ name: 'action_edit', handler: function() { wnd.openLpuSectionMedicalCareKindEditWindow('edit'); }},
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', handler: function() { wnd.deleteLpuSectionMedicalCareKind(); }},
				{ name: 'action_refresh', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=LpuSectionMedicalCareKind&m=loadList',
			id: wnd.id + 'LpuSectionMedicalCareKindGrid',
			object: 'LpuSectionMedicalCareKind',
			onLoadData: function() {
				if (!this.getCount()) {
					this.getGrid().getStore().removeAll();
				}
				wnd.filterLpuSectionMedicalCareKindGrid();
			},
			paging: false,
			region: 'center',
			scheme: 'dbo',
			stringfields: [
				{ name: 'LpuSectionMedicalCareKind_id', type: 'int', key: true, hidden: true },
				{ name: 'RecordStatus_Code', type: 'int', hidden: true },
				{ name: 'MedicalCareKind_id', header: langs('Код'), hidden: true },
				{ name: 'MedicalCareKind_Code', header: langs('Код'), width: 100 },
				{ name: 'MedicalCareKind_Name', header: langs('Наименование'), width: 200, id: 'autoexpand' },
				{ name: 'LpuSectionMedicalCareKind_begDate', type: 'date', header: langs('Дата начала'), width: 100 },
				{ name: 'LpuSectionMedicalCareKind_endDate', type: 'date', header: langs('Дата окончания'), width: 100 }
			],
			title: ''
		});
		
		this.LpuSectionMedicalCareKindGrid.ViewToolbar.on('render', function(vt){return this.addLpuSectionMedicalCareKindCloseFilterMenu();}.createDelegate(this));

		this.LpuSectionServiceGrid = new sw.Promed.ViewFrame({
			id: wnd.id + 'LpuSectionServiceGrid',
			dataUrl: '/?c=LpuStructure&m=loadLpuSectionServiceGrid',
			toolbar: true,
			autoLoadData: false,
			stringfields:
				[
					{name: 'LpuSectionService_id', type: 'int', header: 'ID', key: true},
					{name: 'LpuSection_id', type: 'int', hidden: true},
					{name: 'LpuSection_did', type: 'int', hidden: true},
					{name: 'RecordStatus_Code', type: 'int', hidden: true},
					{name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), id: 'autoexpand'},
					{name: 'LpuBuilding_Name', type: 'string', header: langs('Подразделение'), width: 280}
				],
			actions:
				[
					{name:'action_add', handler: function(){ wnd.openLpuSectionServiceEditWindow('add'); }},
					{name:'action_edit', hidden: true},
					{name:'action_view', hidden: true},
					{name:'action_delete', handler: function(){ wnd.deleteLpuSectionService(); }},
					{name:'action_refresh', hidden: true},
					{name:'action_print', hidden: true}
				]
		});

		this.LpuSectionAttributeValueGrid = new sw.Promed.AttributeSignValueGridPanel({
			tableName: 'dbo.LpuSection',
			formMode: 'local'
		});
		
		this.MainPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyStyle:'width:100%;height:100%;background:#DFE8F6;padding:0px;',
			border: false,
			frame: false,
			id:'LpuSectionEditFormPanel',
			layout: 'border',
			region: 'center',
			items: [{
				xtype: 'tabpanel',
				enableTabScroll: true,
				region: 'center',
				id: 'mainpanel',
				activeTab: 0,
				layoutOnTabChange: true,
				defaults: {bodyStyle:'width:100%;height:100%;background:#DFE8F6;padding:4px;'}, //
				listeners: {
					tabchange: function(tab, panel) {
						switch (panel.id) {
							case 'main_tab':
								this.ownerCt.findById('lsLpuSection_Code').focus(true);
							break;

							case 'er_tab':
								this.ownerCt.findById('lsLpuSection_Descr').focus(true);
							break;

							case 'frmo_tab':
								//this.ownerCt.findById('lsLpuSection_Descr').focus(true);
							break;

							case 'tab_medproduct':
							break;

							case 'tab_averageduration':
								wnd.SectionAverageDurationGrid.loadData({params:{LpuSection_id:wnd.LpuSection_id}, globalFilters:{LpuSection_id:wnd.LpuSection_id}});
							break;

							case 'tab_lpusectionprofile':
							break;

							case 'tab_lpusectionattribute':
								if ( panel.dataIsLoaded == false && !Ext.isEmpty(wnd.LpuSection_id) ) {
									panel.dataIsLoaded = true;

									wnd.LpuSectionAttributeValueGrid.doLoad({tablePKey: wnd.LpuSection_id});
								}
							break;

							case 'tab_lpusectionservice':
								if (
									this.ownerCt.findById('lsLpuSectionProfile_id').getFieldValue('LpuSectionProfile_SysNick') == 'priem'
									|| (getRegionNick() == 'kareliya' && this.ownerCt.findById('lsLpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code') == '160')
								) {
									if ( panel.dataIsLoaded == false && !Ext.isEmpty(wnd.LpuSection_id) ) {
										panel.dataIsLoaded = true;

										wnd.LpuSectionServiceGrid.loadData({
											params: {
												LpuSection_id: wnd.LpuSection_id
											},
											globalFilters: {
												LpuSection_id: wnd.LpuSection_id
											}
										});
									}
								}
							break;

							case 'tab_lpusectionmedicalcarekind':
								if ( panel.dataIsLoaded == false && !Ext.isEmpty(wnd.LpuSection_id) ) {
									panel.dataIsLoaded = true;

									wnd.LpuSectionMedicalCareKindGrid.loadData({
										params:{LpuSection_id:wnd.LpuSection_id}, 
										globalFilters:{LpuSection_id:wnd.LpuSection_id, isClose: (wnd.LpuSectionMedicalCareKindGrid.gFilters) ? wnd.LpuSectionMedicalCareKindGrid.gFilters.isClose : 1}
									});
								}
							break;
							
						}
					}
				},
				items: [{
					title: langs('Основные данные'),
					layout:'form',
					bodyStyle:'padding: 4px; overflow: auto;background:#DFE8F6;',
					id: 'main_tab',
					labelWidth: 170,
					items: [{
						allowBlank: false,
						fieldLabel: langs('Дата создания'),
						format: 'd.m.Y',
						id: 'lsLpuSection_setDate',
						listeners: {
							'change':function (combo, newValue, oldValue) {
								var form = this.MainPanel.getForm();
								form.findField('LpuSection_disDate').fireEvent('change', form.findField('LpuSection_disDate'), form.findField('LpuSection_disDate').getValue());
							}.createDelegate(this)
						},
						name: 'LpuSection_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: 2411,
						xtype: 'swdatefield'
					}, {
						fieldLabel: langs('Дата закрытия'),
						format: 'd.m.Y',
						id: 'lsLpuSection_disDate',
						listeners: {
							'change':function (field, newValue, oldValue) {
								var
									win = this,
									form = this.MainPanel.getForm();

								var
									index,
									LpuSectionProfile_id = form.findField('LpuSectionProfile_id').getValue(),
									setDate = form.findField('LpuSection_setDate').getValue();

								// Фильтруем список профилей отделений
								form.findField('LpuSectionProfile_id').clearValue();
								form.findField('LpuSectionProfile_id').getStore().clearFilter();
								form.findField('LpuSectionProfile_id').lastQuery = '';

								if ( !Ext.isEmpty(setDate) || !Ext.isEmpty(newValue) ) {
									form.findField('LpuSectionProfile_id').getStore().filterBy(function(rec) {
										if ( !Ext.isEmpty(win.LpuSectionProfile_id) && rec.get('LpuSectionProfile_id') == win.LpuSectionProfile_id ) {
											LpuSectionProfile_id = win.LpuSectionProfile_id;
											win.LpuSectionProfile_id = null;
											return true;
										}

										if ( Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) && Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) ) {
											return true;
										}

										if ( !Ext.isEmpty(setDate) && Ext.isEmpty(newValue) ) {
											return (
												(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || typeof rec.get('LpuSectionProfile_begDT') != 'object' || rec.get('LpuSectionProfile_begDT') <= setDate)
												&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || typeof rec.get('LpuSectionProfile_endDT') != 'object' || rec.get('LpuSectionProfile_endDT') >= setDate)
											);
										}
										else if ( Ext.isEmpty(setDate) && !Ext.isEmpty(newValue) ) {
											return (
												(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || typeof rec.get('LpuSectionProfile_begDT') != 'object' || rec.get('LpuSectionProfile_begDT') <= newValue)
												&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || typeof rec.get('LpuSectionProfile_endDT') != 'object' || rec.get('LpuSectionProfile_endDT') >= newValue)
											);
										}
										else {
											return (
												(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || typeof rec.get('LpuSectionProfile_begDT') != 'object' || (rec.get('LpuSectionProfile_begDT') <= newValue && rec.get('LpuSectionProfile_begDT') <= setDate))
												&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || typeof rec.get('LpuSectionProfile_endDT') != 'object' || (rec.get('LpuSectionProfile_endDT') >= newValue && rec.get('LpuSectionProfile_endDT') >= setDate))
											);
										}
									});
								}

								index = form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
									return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
								});

								if ( index >= 0 ) {
									form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
								}

								form.findField('LpuSectionProfile_id').fireEvent('select', form.findField('LpuSectionProfile_id'), form.findField('LpuSectionProfile_id').getStore().getAt(index));

								// Фильтруем список профилей коек
								this.filterBeds();
								//form.findField('LpuSectionBedProfile_id').fireEvent('select', form.findField('LpuSectionBedProfile_id'), form.findField('LpuSectionBedProfile_id').getStore().getAt(index));

								this.loadLpuSectionCodeList();
							}.createDelegate(this)
						},
						name: 'LpuSection_disDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: 2412,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						anchor: '95%',
						disabled: true,
						hiddenName: 'LpuUnit_id',
						id: 'lsLpuUnit_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							},
							'select': function(combo, record, index) {
								if (wnd.findById('lspidcount').getValue() == 0) {
									wnd.findById('lsLpuSection_pid').getStore().load({
										params: {
											Object: 'LpuSection',
											LpuUnit_id: combo.getValue(),
											LpuSection_id: wnd.LpuSection_id
										},
										callback: function () {
											wnd.findById('lsLpuSection_pid').setValue('');
										}
									});
								}

								var filterLpuSectionType = [];
								switch (combo.getFieldValue('LpuUnitType_SysNick')) {
									case 'fap' :
										filterLpuSectionType = ['2', '4'];
										break;
									case 'pstac':
										filterLpuSectionType = ['3'];
										break;
									default:
										filterLpuSectionType = ['2'];
										break;
								}

								wnd.findById('lsLpuSectionType_id').getStore().filterBy(function (rec) {
									return rec.get('LpuSectionType_Code').inlist(filterLpuSectionType);
								});

								if (typeof record == 'object' && record.get('LpuUnit_IsEnabled') == 2) {
									wnd.findById('lsLpuSectionAge_id').setAllowBlank(false);
								} else {
									wnd.findById('lsLpuSectionAge_id').setAllowBlank(true);
								}

								if ( typeof record == 'object' && !Ext.isEmpty(record.get('LpuUnit_FRMOUnitID')) ) {
									wnd.findById('lsFRMOUnit_id').getStore().baseParams.FRMOUnit_OID = record.get('LpuUnit_FRMOUnitID');
								}
								else {
									wnd.findById('lsFRMOUnit_id').getStore().baseParams.FRMOUnit_OID = null;
								}
							}
						},
						tabIndex: 2413,
						topLevel: true,
						xtype: 'swlpuunitcombo'
					}, {
						anchor: '95%',
						disabled: false,
						fieldLabel: langs('Верхний уровень'),
						hiddenName: 'LpuSection_pid',
						id: 'lsLpuSection_pid',
						tabIndex: 2414,
						valueField: 'LpuSection_id',
						xtype: 'swlpusectionpidcombo'
					}, {
						anchor: '95%',
						comboSubject: 'LpuSectionType',
						fieldLabel: langs('Пункт'),
						hiddenName: 'LpuSectionType_id',
						id: 'lsLpuSectionType_id',
						lastQuery: '',
						tabIndex: 2415,
						xtype: 'swcommonsprcombo'
					}, {
						name: 'LpuSection_id',
						xtype: 'hidden',
						id: 'lsLpuSection_id'
					}, {
						name: 'pidcount',
						xtype: 'hidden',
						id: 'lspidcount'
					}, {
						//профиль
						allowBlank: false, 
						anchor: '95%',
						autoLoad: false,
						disabled: false,
						hiddenName: 'LpuSectionProfile_id',
						id: 'lsLpuSectionProfile_id',
						lastQuery: '',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								if ( this.action == 'add' ) {
									SetLpuSectionName(this.findById('lsLpuSection_Name'));
								}

								this.onStacDetail();
								this.loadLpuSectionCodeList();

								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							}.createDelegate(this),
							'beforeselect': function(combo, record) {
								var index = this.LpuSectionLpuSectionProfileGrid.getGrid().getStore().findBy(function(rec) {
									return (rec.get('LpuSectionProfile_id') == record.get('LpuSectionProfile_id') && rec.get('RecordStatus_Code') != 3 );
								});

								if ( index >= 0) {
									Ext.Msg.alert(langs('Ошибка'), langs('Профиль изменить нельзя. Наименование профиля указано в списке дополнительных профилей отделения.'));
									return false;
								}
							}.createDelegate(this),
							'select': function(combo, record, idx) {
								var base_form = this.MainPanel.getForm();

								if (
									typeof record == 'object'
									&& (
										record.get('LpuSectionProfile_SysNick') == 'priem'
										|| (getRegionNick() == 'kareliya' && record.get('LpuSectionProfile_Code') == '160')
									)
								) {
									this.findById('mainpanel').unhideTabStripItem('tab_lpusectionservice');
								}
								else {
									this.findById('mainpanel').hideTabStripItem('tab_lpusectionservice');
									this.LpuSectionServiceGrid.getGrid().getStore().removeAll();
									this.findById('tab_lpusectionservice').dataIsLoaded = false;
								}

								if ( getRegionNick() == 'perm' ) {
									if ( typeof record == 'object' && !Ext.isEmpty(record.get('LpuSectionProfile_fedid')) ) {
										base_form.findField('LpuSectionProfile_fedid').setValue(record.get('LpuSectionProfile_fedid'));
									}
									else {
										base_form.findField('LpuSectionProfile_fedid').clearValue();
									}
								}
							}.createDelegate(this)
						},
						tabIndex: 2416,
						xtype: 'swlpusectionprofilecombo'
					}, {
						// Фед. профиль (только Пермь)
						anchor: '95%',
						comboSubject: 'LpuSectionProfile',
						disabled: true,
						fieldLabel: langs('Фед. профиль'),
						hiddenName: 'LpuSectionProfile_fedid',
						id: 'lsLpuSectionProfile_fedid',
						suffix: 'Fed',
						tabIndex: 2417,
						typeCode: 'int',
						xtype: 'swcommonsprcombo'
					}, {
						anchor: '95%',
						codeField: 'LpuSectionCode_Code',
						//displayField: 'LpuSectionCode_Name',
						displayField: 'LpuSectionCode_Display',
						fieldLabel: langs('Код'),
						hiddenName: 'LpuSectionCode_id',
						id: 'lsLpuSectionCode_id',
						//sortInfo: { field: 'LpuSectionCode_Code', direction: 'DESC' },
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							}.createDelegate(this),
							'select': function(combo, record, idx) {
								var base_form  = this.MainPanel.getForm();

								if ( typeof record == 'object' ) {
									base_form.findField('LpuSection_Code').setValue(record.get('LpuSectionCode_Code'));
								}
								else {
									base_form.findField('LpuSection_Code').setValue('');
								}
							}.createDelegate(this)
						},
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'LpuSectionCode_id', type: 'int'/*, multipleSortInfo: [
									{ field: 'LpuSectionCode_endDT', direction: 'ASC' }, { field: 'LpuSectionCode_Code', direction: 'ASC' }
									]*/ },
								{ name: 'LpuSectionCode_Code', type: 'string' },
								{ name: 'LpuSectionCode_Name', type: 'string' },
								{ name: 'LpuSectionCode_begDT', type: 'date', dateFormat: 'd.m.Y' },
								{ name: 'LpuSectionCode_endDT', type: 'date', dateFormat: 'd.m.Y', multipleSortInfo: [
									{ field: 'LpuSectionCode_endDT', direction: 'ASC' },
									{ field: 'LpuSectionCode_Code', direction: 'ASC' }
								] },
								{ name: 'LpuSectionCode_Display', convert: function ( val, row ) {
									return row.LpuSectionCode_Name + (row.LpuSectionCode_endDT?' (закрыт с '+row.LpuSectionCode_endDT+' г.)':'');
								} },
							],
							key: 'LpuSectionCode_id',
							sortInfo: { field: 'LpuSectionCode_endDT' },
							url: '/?c=LpuStructure&m=loadLpuSectionCodeList'
						}),
						tabIndex: 2418,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{LpuSectionCode_Code}</font>&nbsp;{LpuSectionCode_Name}',
							'<span>&nbsp;{[(values.LpuSectionCode_endDT)?"(закрыт с "+this.printDate( values.LpuSectionCode_endDT )+" г.)":""]}</span>',
							'</div></tpl>',
							{
								printDate: function(date) {
									return date.format('d.m.Y');
								}
							}
						),
						valueField: 'LpuSectionCode_id',
						xtype: 'swbaselocalcombo'
					}, {
						allowBlank: false,
						autoCreate: {tag: "input", size:((getRegionNick()=="perm")?8:9), maxLength: ((getRegionNick()=="perm")?"8":"9"), autocomplete: "off"},
						fieldLabel: langs('Код'),
						id: 'lsLpuSection_Code',
						maskRe: /\d/,
						name: 'LpuSection_Code',
						tabIndex: 2419,
						xtype: 'textfield'
					}, {
						allowBlank: false,
						anchor: '95%',
						enableKeyEvents: true,
						fieldLabel: langs('Наименование'),
						id: 'lsLpuSection_Name',
						listeners: {
							keydown: function(inp, e) {
								if (e.getKey() == e.F2) {
									this.onTriggerClick();
									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}
									e.stopEvent(); 
								}
							}
						},
						name: 'LpuSection_Name',
						onTriggerClick: function() {
							SetLpuSectionName(this);
						},
						tabIndex: 2421,
						triggerAction: 'all',
						triggerClass: 'x-form-plus-trigger',
						xtype: 'trigger'
					}, {
						anchor: '95%',
						comboSubject: 'PalliativeType',
						fieldLabel: langs('Вид отделения ПМП'),
						hiddenName: 'PalliativeType_id',
						id: 'lsPalliativeType_id',
						loadParams: {
							params: {where: " where PalliativeType_id != 5"}
						},
						tabIndex: 2421.5,
						typeCode: 'int',
						xtype: 'swcommonsprcombo'
					}, {
						anchor: '95%',
						editable: false,
						hidden: getRegionNick() != 'kz', 
						hideLabel: getRegionNick() != 'kz',
						fieldLabel: langs('Функциональное подразделение (СУР)'),
						id: 'lsFPID',
						hiddenName: 'FPID',
						tabIndex: 2421,
						codeField: 'CodeRu',
						displayField: 'NameRU',
						valueField: 'FPID',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'FPID', type: 'string' },
								{ name: 'CodeRu', type: 'string' },
								{ name: 'NameRU', type: 'string' }
							],
							key: 'FPID',
							sortInfo: {field: 'NameRU'},
							url: '/?c=LpuStructure&m=getFpList'
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{NameRU} ({CodeRu})',
							'</div></tpl>'
						),
						xtype: 'swbaselocalcombo',
						listeners: {
							'select': function(inp, record, index) {
								setTimeout(function() {
									if ( (inp.codeField != undefined ) && ( inp.editable == false ) && (typeof record == 'object') ) {
										inp.setValue(record.data[inp.valueField]);
										if (record.data[inp.valueField] != "") {
											if (record.data[inp.valueField] != "")
												if (!inp.ignoreCodeField && record.data[inp.codeField] != "" && record.data[inp.codeField] != -1 )
													inp.setRawValue(record.data[inp.displayField] +  ' (' + record.data[inp.codeField] + ')');
												else
													inp.setRawValue(record.data[inp.displayField]);
										}
									}
								}, 0);
							}
						},
					}, {
						anchor: '95%',
						comboSubject: 'MedicalCareKind',
						fieldLabel: langs('Вид МП'),
						hiddenName: 'MedicalCareKind_id',
						id: 'lsMedicalCareKind_id',
						suffix: 'Fed',
						tabIndex: 2422,
						typeCode: 'int',
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: langs('Плановое число вскрытий в смену'),//Морг
						id: 'LpuSection_PlanAutopShift',
						maskRe: /[0-9]/,
						name: 'LpuSection_PlanAutopShift',
						tabIndex: 2423,
						xtype: 'textfield'
					}, {
						anchor: '95%',
						comboSubject: 'LpuCostType',
						fieldLabel: langs('Признак участия в формировании затрат МО'),
						hiddenName: 'LpuCostType_id',
						lastQuery: '',
						id: 'lsLpuCostType_id',
						tabIndex: 2425,
						xtype: 'swcommonsprcombo'
					},
						// LpuSection_CountShift
						// выпилено по https://redmine.swan.perm.ru/issues/108145
					{
						autoCreate: {tag: "input", maxLength: "10", autocomplete: "off"},
						fieldLabel: langs('Площадь отделения, кв. м.'),
						maskRe: /[0-9]/,
						name: 'LpuSection_Area',
						tabIndex: 2426,
						xtype: 'numberfield'
					}, {
						fieldLabel: langs('Количество рабочих мест'),//Лечебно-трудовые мастерские
						id: 'LpuSection_KolJob',
						maskRe: /[0-9]/,
						name: 'LpuSection_KolJob',
						tabIndex: 2429,
						xtype: 'textfield'
					}, {
						fieldLabel: langs('Плановое число посещений в сутки'),
						id: 'LpuSection_PlanVisitDay',
						maskRe: /[0-9]/,
						name: 'LpuSection_PlanVisitDay',
						tabIndex: 2431,
						xtype: 'textfield'
					}, {
						anchor: '95%',
						hiddenName: 'LpuSectionAge_id',
						id: 'lsLpuSectionAge_id',
						tabIndex: 2432,
						xtype: 'swlpusectionagecombo'
					}, {
						anchor: '95%',
						hiddenName: 'LpuSectionBedProfile_id',
						id: 'lsLpuSectionBedProfile_id',
						setValue: function(v) {
							sw.Promed.SwBaseLocalCombo.superclass.setValue.apply(this, arguments);
							
							var r = this.findRecord(this.valueField, v);
							if(r){
								var strTxt = r.get(this.displayField);
								var strDate = '';
								var begDate = r.get('LpuSectionBedProfile_begDT');
								var endDate = r.get('LpuSectionBedProfile_endDT');
								if(typeof begDate == 'object') strDate += Ext.util.Format.date(begDate, 'd.m.Y') + ' -';
								if(typeof endDate == 'object') strDate += '- ' + Ext.util.Format.date(endDate, 'd.m.Y');
								if(strDate) {
									strTxt += ' (' + strDate + ')';
									Ext.form.ComboBox.superclass.setRawValue.call(this, strTxt);
								}
							}
						},
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{LpuSectionBedProfile_Code}</font>&nbsp;{LpuSectionBedProfile_Name}',
							'<font style="font-size: 70%;">{[this.formatDate(values.LpuSectionBedProfile_begDT, values.LpuSectionBedProfile_endDT)]}</div>',
							'</font></tpl>',
							{
								formatDate: function(dateBeg, dateEnd) {
									var str = '';
									if(typeof dateBeg == 'object'){
										str += Ext.util.Format.date(dateBeg, 'd.m.Y') + ' -';
									}
									if(typeof dateEnd == 'object'){
										str += '- ' + Ext.util.Format.date(dateEnd, 'd.m.Y');
									}
									if(str){
										return '&nbsp('+str+')';
									}else{
										return '';
									}
								}
							}
						),
						listeners: {
							'change':function (combo, newValue, oldValue) {
								var form = this.MainPanel.getForm();
								form.findField('lsLpuSection_disDate').fireEvent('change', form.findField('lsLpuSection_disDate'), form.findField('lsLpuSection_disDate').getValue());
							}.createDelegate(this)
						},
						tabIndex: 2433,
						xtype: 'swlpusectionbedprofilecombo'
					}, {
						anchor: '95%',
						fieldLabel: langs('Уровень') + getMESAlias(),
						hiddenName: 'MESLevel_id',
						id: 'lsMESLevel_id',
						tabIndex: 2434,
						xtype: 'swmeslevelcombo'
					}, {
						anchor: '95%',
						comboSubject: 'LevelType',
						fieldLabel: langs('Уровень оказания МП'),
						hiddenName: 'LevelType_id',
						id: 'lsLevelType_id',
						tabIndex: 2435,
						xtype: 'swcommonsprcombo'
					}, {
						anchor: '95%',
						fieldLabel: langs('Доп. признак отделения'),
						hiddenName: 'LpuSectionDopType_id',
						comboSubject: 'LpuSectionDopType',
						id: 'lsLpuSectionDopType_id',
						tabIndex: 2436,
						xtype: 'swcommonsprcombo'
					}, {
						id: 'lsLpuSection_IsHTMedicalCare',
						name: 'LpuSection_IsHTMedicalCare',
						xtype: 'hidden'
					}, {
						boxLabel: langs('Выполнение высокотехнологичной медицинской помощи'),
						id: 'lsLpuSection_IsHTMedicalCareCheckBox',
						labelSeparator: '',
						listeners: {
							'check': function(field, value) {
								this.findById('lsLpuSection_IsHTMedicalCare').setValue(value === true ? 2 : 1);
							}.createDelegate(this)
						},
						name: 'LpuSection_IsHTMedicalCareCheckBox',
						tabIndex: 2437,
						xtype: 'checkbox'
					}, {
						boxLabel: langs('Использовать в форме 14-ОМС'),
						id: 'lsLpuSection_F14',
						labelSeparator: '',
						name: 'LpuSection_F14',
						tabIndex: 2438,
						xtype: 'checkbox'
					}, {
						boxLabel: langs('Использовать данные подотделения в реестрах'),
						id: 'lsLpuSection_IsUseReg',
						labelSeparator: '',
						name: 'LpuSection_IsUseReg',
						tabIndex: 2439,
						xtype: 'checkbox'
					}, {
						id: 'lsLpuSection_IsCons',
						name: 'LpuSection_IsCons',
						value: 1, // По умолчанию при добавлении 
						xtype: 'hidden'
					}, {
						id: 'lsLpuSection_IsExportLpuRegion',
						name: 'LpuSection_IsExportLpuRegion',
						value: 1, // По умолчанию при добавлении 
						xtype: 'hidden'
					}, {
						boxLabel: langs('Консультационное отделение'),
						id: 'lsLpuSection_IsConsCheckbox',
						labelSeparator: '',
						name: 'LpuSection_IsConsCheckbox',
						tabIndex: 2440,
						xtype: 'checkbox'
					}, {
						boxLabel: langs('Выгружать участки'),
						hidden: true,
						id: 'lsLpuSection_IsExportLpuRegionCheckbox',
						labelSeparator: '',
						name: 'LpuSection_IsExportLpuRegionCheckbox',
						tabIndex: 2441,
						xtype: 'checkbox'
					}, {
						boxLabel: langs('Без КСГ'),
						hidden: true,
						id: 'lsLpuSection_IsNoKSG',
						labelSeparator: '',
						name: 'LpuSection_IsNoKSG',
						tabIndex: 2442,
						xtype: 'checkbox'
					}]
				}, {
					id: 'spec_tab',
					labelWidth: 120,
					layout:'form',
					title: langs('Список специальностей'),
					items: [ new sw.Promed.ViewFrame({
						autoExpandColumn: 'autoexpand',
						autoExpandMin: 250,
						autoLoadData: false,
						dataUrl: '/?c=MedPersonal&m=getHistSpec',
						id: 'HistSpec',
						actions: [
							{name:'action_add', hidden:true },
							{name:'action_edit', hidden:true},
							{name:'action_view', hidden:true},
							{name:'action_delete', hidden:true}
						],
						totalProperty: 'totalCount',
						region: 'center',
						stringfields: [
							{ header: 'ID', type: 'int', name: 'MedStaffFact_id', key: true },
							{ header: langs('Специальность'),  type: 'string', name: 'PostMed_Name', id: 'autoexpand',width: 170 },
							{ header: langs('Период действия записи'),  type: 'string', name: 'MedStaffFact_Interval', width: 170},
							{ header: langs('Отделение'),  type: 'string', name: 'LpuSection_Name', width: 150 }
						],
						toolbar: true,
						onRowSelect: function(sm,rowIdx,record) {
							//
						},
						onDblClick: function(grid, rowIdx, colIdx, event) {
							//
						},
						onEnter: function() {
							//
						}
					})]
				}, {
					title: langs('Электронная регистратура'),
					layout:'form',
					id: 'er_tab',
					labelWidth: 120,
					items: 
					[{
						anchor: '95%',
						tabIndex: 2443,
						fieldLabel : langs('Примечание'),
						name: 'LpuSection_Descr',
						xtype: 'textarea',
						autoCreate: {tag: "textarea", autocomplete: "off"},
						id: 'lsLpuSection_Descr'
					},
					{
						anchor: '95%',
						tabIndex: 2444,
						fieldLabel : langs('Контакты'),
						name: 'LpuSection_Contacts',
						xtype: 'textarea',
						autoCreate: {tag: "textarea", autocomplete: "off"},
						id: 'lsLpuSection_Contacts'
					},
					// Только стационары
					{
						xtype: 'fieldset',
						border: false,
						autoHeight: true,
						style: 'padding:0px;margin:0px;',
						id: 'lsOnlyStacPanel',
						items:
						[{
							anchor: '95%',
							tabIndex: 2445,
							name: 'LpuSectionHospType_id',
							xtype: 'swcommonsprcombo',
                            fieldLabel: langs('Вид госпитализации'),
                            comboSubject: 'LpuSectionHospType',
							id: 'lsLpuSectionHospType_id'
						},
						{
							xtype: 'checkbox',
							height:24,
							hideLabel: true,
							tabIndex: 2446,
							name: 'LpuSection_IsDirRec',
							id: 'lsLpuSection_IsDirRec',
							boxLabel: 'Разрешить запись из других МО'
						},
						{
							xtype: 'checkbox',
							height:24,
							hideLabel: true,
							tabIndex: 2447,
							name: 'LpuSection_IsQueueOnFree',
							id: 'lsLpuSection_IsQueueOnFree',
							boxLabel: langs('Разрешить помещать в очередь при наличии свободных мест')
						}]
					}]
				}, {
					title: langs('ФРМО'),
					layout:'form',
					id: 'frmo_tab',
					labelWidth: 170,
					items: [{
						fieldLabel: langs('Не передавать на ФРМО'),
						name: 'LpuSection_IsNotFRMO',
						xtype: 'checkbox',
						id: 'lsLpuSection_IsNotFRMO',
						listeners: {
							'check': function (checkbox, value) {
								this.setFRMOFieldsVisibility();
							}.createDelegate(this)
						}
					}, {
						anchor: '95%',
						displayField: 'FRMOUnit_Display',
						fieldLabel: langs('ФРМО справочник структурных подразделений'),
						hiddenName: 'FRMOUnit_id',
						id: 'lsFRMOUnit_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							}.createDelegate(this),
							'select': function(combo, record, idx) {
								var base_form = this.MainPanel.getForm();

								if ( typeof record == 'object' ) {
									this.filterFRMOSection();
								}

								this.setLpuSectionFRMOBuildingOid();
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
								{name: 'FRMOUnit_Display',
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
						tabIndex: 2454.5,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{FRMOUnit_Display}',
							'</div></tpl>'
						),
						onTrigger2Click: function() {
							if ( !this.disabled ) {
								this.clearValue();
								this.fireEvent('change', this);
							}
						},
						valueField: 'FRMOUnit_id',
						minChars: 0,
						xtype: 'swbaseremotecombo'
					}, {
						anchor: '95%',
						displayField: 'FRMOSection_Display',
						fieldLabel: langs('ФРМО справочник отделений и кабинетов'),
						hiddenName: 'FRMOSection_id',
						id: 'lsFRMOSection_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get(combo.valueField) == newValue);
								});
								combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
							}.createDelegate(this),
							'select': function(combo, record, idx) {
								this.setLpuSectionFRMOBuildingOid();
							}.createDelegate(this)
						},
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{name: 'FRMOSection_id', type: 'int'},
								{name: 'Lpu_id', type: 'int'},
								{name: 'FRMOSection_MOID', type: 'string'},
								{name: 'FRMOSection_OID', type: 'string'},
								{name: 'FRMOUnit_OID', type: 'string'},
								{name: 'FRMOSection_Name', type: 'string'},
								{name: 'FRMOSection_Address', type: 'string'},
								{name: 'FRMOSection_LiquidationDate', type: 'date'},
								{name: 'FRMOSection_Display',
									convert: function(val, row) {
										return row.FRMOSection_Name + ' (' + row.FRMOSection_OID + ')';
									}
								}
							],
							key: 'FRMOSection_id',
							sortInfo: {
								field: 'FRMOSection_Name'
							},
							url: C_FRMOSECTION_LIST
						}),
						tabIndex: 2423.5,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<div>{FRMOSection_Display}&nbsp;</div>',
							'<div style="font-size: 10px;">{[!Ext.isEmpty(values.FRMOSection_Address) ? "Адрес: " + values.FRMOSection_Address : ""]}</div>',
							'</div></tpl>'
						),
						onTrigger2Click: function() {
							if ( !this.disabled ) {
								this.clearValue();
								this.fireEvent('change', this);
							}
						},
						valueField: 'FRMOSection_id',
						minChars: 0,
						xtype: 'swbaseremotecombo'
					}, {
						anchor: '95%',
						fieldLabel: 'ОИД ФРМО отделения/кабинета',
						readOnly: true,
						name: 'LpuSection_FRMOBuildingOid',
						xtype: 'textfield'
					}, {
						anchor: '95%',
						name: 'FRMPSubdivision_id',
						hiddenName: 'FRMPSubdivision_id',
						xtype: 'swfrmpsubdivisiontypecombo',
						id: 'FRMPSubdivisionSection_id',
						valueField: 'id'
					}, {
						fieldLabel: langs('Плановое число посещений в смену'),//Лечебно-амбулаторный поликлинический корпус
						id: 'LpuSection_PlanVisitShift',
						maskRe: /[0-9]/,
						name: 'LpuSection_PlanVisitShift',
						tabIndex: 2424,
						xtype: 'textfield'
					}, {
						fieldLabel: langs('Плановое число исследований в смену'),//Подразделениях с типом Лабораторно-инструментальные или  в Группе отделений с типом Параклиника
						id: 'LpuSection_PlanResShift',
						maskRe: /[0-9]/,
						name: 'LpuSection_PlanResShift',
						tabIndex: 2430,
						xtype: 'textfield'
					}, {
						fieldLabel: langs('Плановое число выездов в смену'),//Скорая медицинская помощь
						id: 'LpuSection_PlanTrip',
						maskRe: /[0-9]/,
						name: 'LpuSection_PlanTrip',
						tabIndex: 2427,
						xtype: 'textfield'
					}, {
						fieldLabel: langs('Количество бригад скорой помощи'),
						id: 'LpuSection_KolAmbul',
						maskRe: /[0-9]/,
						name: 'LpuSection_KolAmbul',
						tabIndex: 2428,
						xtype: 'textfield'
					}]
				}, {
					title: langs('Мед. оборудование'),
					layout:'border',
					id: 'tab_medproduct',
					labelWidth: 120,
					items: [ wnd.LpuSectionMedProductTypeLinkGrid ]
				}, {
					title: langs('Средняя длительность'),
					layout:'border',
					id: 'tab_averageduration',
					labelWidth: 120,
					items: 
					[ wnd.SectionAverageDurationGrid ]
				}, {
					title: langs('Дополнительные профили'),
					layout: 'border',
					id: 'tab_lpusectionprofile',
					labelWidth: 120,
					items: [
						wnd.LpuSectionLpuSectionProfileGrid
					]
				}, {
					title: langs('Обслуживаемые отделения'),
					layout: 'border',
					id: 'tab_lpusectionservice',
					labelWidth: 120,
					items: [
						wnd.LpuSectionServiceGrid
					]
				}, {
					title: langs('Атрибуты'),
					layout: 'border',
					id: 'tab_lpusectionattribute',
					labelWidth: 120,
					items: [
						wnd.LpuSectionAttributeValueGrid
					]
				}, {
					title: langs('Вид оказания МП '),
					layout: 'border',
					id: 'tab_lpusectionmedicalcarekind',
					labelWidth: 120,
					items: [
						wnd.LpuSectionMedicalCareKindGrid
					]
				}]
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
				alert('success');
				}
			},
			[
				{ name: 'LpuUnit_id' },
				{ name: 'LpuSection_id' },
				{ name: 'LpuSection_pid' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'LpuSection_Code' },
				{ name: 'LpuSectionCode_id' },
				{ name: 'LpuSection_Name' },
				{ name: 'PalliativeType_id' },
				{ name: 'LpuSection_setDate' },
				{ name: 'LpuSection_disDate' },
				{ name: 'LpuSectionAge_id' },
				{ name: 'LpuSectionBedProfile_id' },
				{ name: 'MESLevel_id' },
				{ name: 'LpuSection_PlanVisitShift' },
				{ name: 'LpuSection_PlanTrip' },
				{ name: 'LpuSection_PlanVisitDay' },
				{ name: 'LpuSection_PlanAutopShift' },
				{ name: 'LpuSection_PlanResShift' },
				{ name: 'LpuSection_KolJob' },
				{ name: 'LpuSection_KolAmbul' },
				{ name: 'LpuSection_F14' },
				{ name: 'LpuSection_Descr' }, 
				{ name: 'LpuSection_Contacts' }, 
				{ name: 'LpuSectionHospType_id' }, 
				{ name: 'LpuSection_IsDirRec' }, 
				{ name: 'LpuSection_IsQueueOnFree' },
				{ name: 'LpuSection_IsUseReg' },
				{ name: 'LpuSection_IsCons' },
				{ name: 'LpuSection_IsExportLpuRegion' },
				{ name: 'LpuSection_IsHTMedicalCare' },
				{ name: 'LpuSection_IsNoKSG' },
				{ name: 'LevelType_id' },
				{ name: 'MedicalCareKind_id' },
				{ name: 'LpuSectionType_id' },
				{ name: 'LpuSection_Area' },
				{ name: 'LpuSectionDopType_id' },
				{ name: 'FRMPSubdivision_id' },
				{ name: 'LpuCostType_id' },
				{ name: 'LpuSection_IsNotFRMO' },
				{ name: 'FRMOUnit_id' },
				{ name: 'FRMOSection_id' },
				{ name: 'LpuSection_FRMOBuildingOid' },
				{ name: 'FPID' },
				{ name: 'pidcount' }
			]
			),
			url: C_LPUSECTION_SAVE
		});

		Ext.apply(this,
		{
			xtype: 'panel',
			bodyStyle:'padding:0px; overflow: auto;',
			border: false,
			items: [this.MainPanel]
		});

		sw.Promed.swLpuSectionEditForm.superclass.initComponent.apply(this, arguments);

		this.findById('lsLpuSectionProfile_id').setBaseFilter(function(rec) {
			var
				disDate = this.findById('lsLpuSection_disDate').getValue(),
				setDate = this.findById('lsLpuSection_setDate').getValue();

			if ( !Ext.isEmpty(setDate) || !Ext.isEmpty(disDate) /*|| getRegionNick() == 'khak'*/ ) {
				if ( Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) && Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) ) {
					return true;
				}

				if ( !Ext.isEmpty(setDate) && Ext.isEmpty(disDate) ) {
					return (
						(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= setDate)
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= setDate)
					);
				}
				else if ( Ext.isEmpty(setDate) && !Ext.isEmpty(disDate) ) {
					return (
						(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= disDate)
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= disDate)
					);
				}
				else {
					return (
						(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || (rec.get('LpuSectionProfile_begDT') <= disDate && rec.get('LpuSectionProfile_begDT') <= setDate))
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || (rec.get('LpuSectionProfile_endDT') >= disDate && rec.get('LpuSectionProfile_endDT') >= setDate))
					);
				}
			} else if ( getRegionNick() == 'khak' ) {
				return (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || (!Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) && rec.get('LpuSectionProfile_endDT') > new Date()));
			}
			
			return true;
		}.createDelegate(this));

	},

	filterBeds: function ()
	{
		var form = this.MainPanel.getForm(),
			setDate = form.findField('LpuSection_setDate').getValue(),
			disDate = form.findField('LpuSection_disDate').getValue();

		setDate = Ext.isDate(setDate) ? setDate : Date.parse(setDate);
		disDate = Ext.isDate(disDate) ? disDate : Date.parse(disDate);

		form.findField('LpuSectionBedProfile_id').filterByDates(setDate, disDate);

		return;
	}
});