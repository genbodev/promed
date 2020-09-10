/**
* swLpuSectionBedStateEditForm - окно просмотра и редактирования коечного фонда
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      08.10.2010
*/
/*NO PARSE JSON*/
sw.Promed.swLpuSectionBedStateEditForm = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swLpuSectionBedStateEditForm',
	objectSrc: '/jscore/Forms/Admin/LpuSectionBedStateEditForm.js',
	title:lang['koechnyiy_fond'],
	id: 'LpuSectionBedStateEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 700,
	height: 600,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lsbsefOk',
		tabIndex: 1326,
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
		id: 'lsbsefCancel',
		tabIndex: 1327,
		iconCls: 'cancel16',
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
	doSave: function()  {
        var cur = this;
		var form = this.findById('LpuSectionBedStateEditFormPanel');
        var base_form = form.getForm();
		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var begDate = form.findById('lsbsefLpuSectionBedState_begDate').getValue();
		var endDate = form.findById('lsbsefLpuSectionBedState_endDate').getValue();
		var BedState_Plan = form.findById('lsbsefLpuSectionBedState_Plan').getValue();
		var BedState_Fact = form.findById('lsbsefLpuSectionBedState_Fact').getValue();
		var BedState_Oms = form.findById('lsbsefLpuSectionBedState_CountOms').getValue();

		if ((parseInt(BedState_Fact,10)>parseInt(BedState_Plan,10)))
		{
			Ext.Msg.alert(lang['oshibka'], lang['kolichestvo_fakt_ne_mojet_prevyishat_planovogo_kolichestva_koek']);
			return false;
		}
		var BedState_MalePlan = form.findById('lsbsefLpuSectionBedState_MalePlan').getValue();
		var BedState_MaleFact = form.findById('lsbsefLpuSectionBedState_MaleFact').getValue();
		var BedState_FemalePlan = form.findById('lsbsefLpuSectionBedState_FemalePlan').getValue();
		var BedState_FemaleFact = form.findById('lsbsefLpuSectionBedState_FemaleFact').getValue();
		if ((parseInt(BedState_MaleFact,10)>parseInt(BedState_MalePlan,10)))
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Фактическое количество мужских коек не может превышать плановое количество коек'));
			return false;
		}
		if ((parseInt(BedState_FemaleFact,10)>parseInt(BedState_FemalePlan,10)))
		{
			Ext.Msg.alert(langs('Ошибка'), langs('Фактическое количество женских коек не может превышать плановое количество коек'));
			return false;
		}

		if ((parseInt(BedState_Oms,10)>parseInt(BedState_Fact,10)))
		{
			Ext.Msg.alert(lang['oshibka'], lang['kolichestvo_koek_oplachivaemyih_po_oms_ne_mojet_prevyishat_fakticheskogo_kolichestva_koek']);
			return false;
		}


		if ((begDate) && (endDate) && (begDate>endDate)) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					form.findById('lsbsefLpuSectionBedState_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        var params = new Object();

        // Собираем данные из грида
        var DBedOperationGrid = this.findById('LPEW_DBedOperationGrid').getGrid();
        DBedOperationGrid.getStore().clearFilter();

        if ( DBedOperationGrid.getStore().getCount() > 0 ) {
            var DBedOperationData = getStoreRecords(DBedOperationGrid.getStore(), {
                convertDateFields: true,
                exceptionFields: [
                    'DBedOperation_Name'
                    ,'pmUser_Name'
                ]
            });
            params.DBedOperationData = Ext.util.JSON.encode(DBedOperationData);
        }
        var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение услуги..." });
        loadMask.show();

        base_form.submit({
            failure: function(result_form, action) {
                this.formStatus = 'edit';
                loadMask.hide();

                if ( action.result ) {
                    if ( action.result.Error_Msg ) {
                        sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                    }
                    else {
                        sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
                    }
                }
            }.createDelegate(this),
            params: params,
            success: function(result_form, action) {
                this.formStatus = 'edit';
                loadMask.hide();

                if ( action.result ) {
                    if ( action.result.LpuSectionBedState_id > 0 ) {
                        base_form.findField('LpuSectionBedState_id').setValue(action.result.LpuSectionBedState_id);

                        var data = new Object();

                        data.LpuSectionBedState = [{
                            'LpuSectionBedState_id': base_form.findField('LpuSectionBedState_id').getValue(),
                            'accessType': 'edit'
                        }];

                        cur.hide();
                        cur.returnFunc(form.ownerCt.owner, action.result.LpuSectionBedState_id);
                    }
                    else {
                        if ( action.result.Error_Msg ) {
                            sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                        }
                        else {
                            sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
                        }
                    }
                }
                else {
                    sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
                }
            }.createDelegate(this),
            callback: function() {
                grid.getStore().loadData([data.DBedOperationData], true);
            }.createDelegate(this)

        });
	},
	returnFunc: function(owner, kid) {},
	show: function() {
		sw.Promed.swLpuSectionBedStateEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('LpuSectionBedStateEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

        this.callback = Ext.emptyFn;

        if(arguments[0].callback) {
            this.returnFunc = arguments[0].callback;
            this.callback = arguments[0].callback;
        }
		if (arguments[0].owner)
			this.owner = arguments[0].owner;

		if (arguments[0].action)
			this.action = arguments[0].action;

		if (arguments[0].LpuSectionBedState_id)
			this.LpuSectionBedState_id = arguments[0].LpuSectionBedState_id;
		else 
			this.LpuSectionBedState_id = null;

		if (arguments[0].LpuSectionBedStateOper_id)
			this.LpuSectionBedStateOper_id = arguments[0].LpuSectionBedStateOper_id;
		else
			this.LpuSectionBedStateOper_id = null;

		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;
		else 
			this.LpuSection_id = null;

		if (arguments[0].LpuSection_pid)
			this.LpuSection_pid = arguments[0].LpuSection_pid;
		else 
			this.LpuSection_pid = null;

		if (arguments[0].child_count || 0 == arguments[0].child_count)
			this.child_count = arguments[0].child_count;
		else 
			this.child_count = null;

		if (arguments[0].LpuSection_Name)
			this.LpuSection_Name = arguments[0].LpuSection_Name;
		else 
			this.LpuSection_Name = null;

		if (arguments[0].LpuUnitType_id)
			this.LpuUnitType_id = arguments[0].LpuUnitType_id;
		else 
			this.LpuUnitType_id = null;

        this.findById('LPEW_DBedOperationGrid').params = {
            LpuSectionBedState_id: this.LpuSectionBedState_id
            //LpuSection_id: this.LpuSection_id
        }
		
		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		
		var form = this;
		form.findById('LpuSectionBedStateEditFormPanel').getForm().reset();

		switch (this.action)
		{
			case 'add':
				form.setTitle(lang['profil_koyki_dobavlenie']);
				break;
			case 'edit':
				form.setTitle(lang['profil_koyki_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['profil_koyki_prosmotr']);
				break;
		}
		
		var LpuSectionBedProfile_combo = form.MainPanel.getForm().findField('LpuSectionBedProfileLink_id');
		var LpuSectionBedProfileKZ_combo = form.MainPanel.getForm().findField('LpuSectionBedProfile_id');
		var LpuSectionProfile_combo = form.MainPanel.getForm().findField('LpuSectionProfile_id');
		
		LpuSectionBedProfile_combo.getStore().clearFilter();

		if(getRegionNick() == 'kz'){
			LpuSectionBedProfileKZ_combo.showContainer();
			LpuSectionBedProfileKZ_combo.setAllowBlank(false);
			LpuSectionBedProfile_combo.hideContainer();
			LpuSectionBedProfile_combo.setAllowBlank(true);
		}else{
			LpuSectionBedProfile_combo.showContainer();
			LpuSectionBedProfile_combo.setAllowBlank(false);
			LpuSectionBedProfileKZ_combo.hideContainer();
			LpuSectionBedProfileKZ_combo.setAllowBlank(true);
		}
		
		//LpuSectionBedProfile_combo.hideContainer();
		LpuSectionBedProfile_combo.setAllowBlank( (getRegionNick() == 'kz') );
		LpuSectionProfile_combo.showContainer();
		LpuSectionProfile_combo.setAllowBlank(true);
		LpuSectionProfile_combo.getStore().load({params: {LpuSection_id: this.LpuSection_id}});
		
		if (this.action=='view')
		{
			form.findById('lsbsefLpuSectionBedState_begDate').disable();
			form.findById('lsbsefLpuSectionBedState_endDate').disable();
			form.findById('lsbsefLpuSectionBedState_Plan').disable();
			form.findById('lsbsefLpuSectionBedState_Fact').disable();
			//form.findById('lsbsefLpuSectionBedState_Repair').disable();
			form.findById('lsbsefLpuSection_id').disable();
			form.findById('lsbsefLpuSectionBedState_ProfileName').disable();
			form.findById('lsbsefLpuSectionBedState_id').disable();
			form.buttons[0].disable();
		}
		else
		{
			form.findById('lsbsefLpuSectionBedState_begDate').enable();
			form.findById('lsbsefLpuSectionBedState_endDate').enable();			
			form.findById('lsbsefLpuSectionBedState_Plan').enable();
			form.findById('lsbsefLpuSectionBedState_Fact').enable();
			//form.findById('lsbsefLpuSectionBedState_Repair').enable();
			form.findById('lsbsefLpuSection_id').enable();
			form.findById('lsbsefLpuSectionBedState_ProfileName').enable();
			form.findById('lsbsefLpuSectionBedState_id').enable();
			form.buttons[0].enable();
		}
		
		form.findById('lsbsefLpuSection_id').setValue(this.LpuSection_id);
		form.findById('lsbsefLpuSection_Name').setValue(this.LpuSection_Name);

		if (this.action!='add') {
			form.findById('LpuSectionBedStateEditFormPanel').getForm().load({
				url: C_LPUSECTIONBEDSTATE_GET,
				params: {
					object: 'LpuSectionBedState',
					LpuSectionBedState_id: this.LpuSectionBedState_id,
					LpuSectionBedState_Plan: '',
					LpuSectionBedState_Fact: '',
					//LpuSectionBedState_Repair: '',					
					LpuSectionBedState_begDate: '',
					LpuSectionBedState_endDate: '',
					LpuSection_id: ''
				},
				success: function () {
					form.filterLpuSectionBedProfileLink();
					if (form.action!='view') {
							//
					}
					loadMask.hide();
				},
				failure: function () {
					loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
				}
			});
		} else {		
			form.filterLpuSectionBedProfileLink();	
			loadMask.hide();
		}

        Ext.getCmp('LPEW_DBedOperationGrid').loadData({
            globalFilters:{LpuSectionBedState_id: this.LpuSectionBedState_id},
            params:{ LpuSectionBedState_id: this.LpuSectionBedState_id }
        })
	},
	filterLpuSectionBedProfileLink: function(){
		var that = this;
		var LpuSectionBedProfileLinkCombo = this.MainPanel.getForm().findField('LpuSectionBedProfileLink_id');
		if(LpuSectionBedProfileLinkCombo.hidden) return false;
		
		var base_form = this.MainPanel.getForm();
		var LpuSection_id = base_form.findField('LpuSection_id').getValue();
		var LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
		var params = {};
		if( LpuSection_id ) params.LpuSection_id = LpuSection_id;
		if( LpuSectionProfile_id )params.LpuSectionProfile_id = LpuSectionProfile_id;
		if(!LpuSection_id) return false;
		params.validityLpuSection = true; // по периоду действия отделения
		sw.Promed.LpuSectionBedProfile.getLpuSectionBedProfileLink({
			params,
			callback: function(response_obj) {
				var LpuSectionBedProfilesLink = [];
				response_obj.forEach(function (el){LpuSectionBedProfilesLink.push(parseInt(el.LpuSectionBedProfileLink_id))});
				
				var LpuSectionBedProfileLinkCombo = that.MainPanel.getForm().findField('LpuSectionBedProfileLink_id');
				LpuSectionBedProfileLinkCombo.getStore().clearFilter();
				LpuSectionBedProfileLinkCombo.lastQuery = '';

				if (getRegionNick() == 'ekb' && LpuSectionBedProfilesLink.length == 0) {
					LpuSectionBedProfileLinkCombo.setBaseFilter(function(rec) {
						return true;
					});
				}
				else {
					LpuSectionBedProfileLinkCombo.getStore().filterBy(function (el) {
						return 0<=LpuSectionBedProfilesLink.indexOf(el.data.LpuSectionBedProfile_id);
					});

					LpuSectionBedProfileLinkCombo.setBaseFilter(function(rec) {
						return (0 <= LpuSectionBedProfilesLink.indexOf(rec.get('LpuSectionBedProfileLink_id')));
					});
				}

				//если значение которые было установлено отфильтровалось, очищаю комбик
				if ( Ext.isEmpty(LpuSectionBedProfileLinkCombo.getStore().getById(LpuSectionBedProfileLinkCombo.getValue())) ) {
					LpuSectionBedProfileLinkCombo.clearValue();
				}
				LpuSectionBedProfileLinkCombo.fireEvent('change', LpuSectionBedProfileLinkCombo, LpuSectionBedProfileLinkCombo.getValue());
			}
		});
	},
    gridRecordDelete: function() {

        if ( this.action == 'view' ) {
            return false;
        }

        sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ( buttonId == 'yes' ) {
                    var grid = this.findById('LPEW_DBedOperationGrid').getGrid();
                    var idField = 'LpuSectionBedStateOper_id';

                    if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
                        return false;
                    }

                    var record = grid.getSelectionModel().getSelected();
                    var params = new Object();
                    params.LpuSectionBedStateOper_id = record.get('LpuSectionBedStateOper_id');
                    var url = "/?c=LpuStructure&m=deleteSectionBedStateOper";
                    var index = 0;
                    if (record.get('LpuSectionBedStateOper_id') > 0) {
                        index = 1;
                    }

                    switch (index) {
                        case 0:
                            grid.getStore().remove(record);
                            break;
                        case 1:
                            if (!Ext.isEmpty(url)) {
                                Ext.Ajax.request({
                                    callback: function(opt, scs, response) {
                                        if (scs) {
                                            grid.getStore().remove(record);
                                        }
                                    }.createDelegate(this),
                                    params: params,
                                    url: url
                                });
                            }
                            grid.getStore().remove(record);
                            break;
                    }

                    if ( grid.getStore().getCount() > 0 ) {
                        grid.getView().focusRow(0);
                        grid.getSelectionModel().selectFirstRow();
                    }
                }
            }.createDelegate(this),
            icon: Ext.MessageBox.QUESTION,
            msg: lang['vyi_deystvitelno_hotite_udalit_operatsiyu_nad_koykoy'],
            title: lang['vopros']
        });
    },
    gridFilter: function(data) {
        var grid = this.findById('LPEW_DBedOperationGrid').getGrid();
        var records = getStoreRecords(grid.getStore());
        for ( i = 0; i < records.length; i++ ) {
            if ( (records[i]['DBedOperation_id'] == data.DBedOperationData.DBedOperation_id) &&
                (Ext.util.JSON.encode(records[i]['LpuSectionBedStateOper_OperDT']) == Ext.util.JSON.encode(data.DBedOperationData.LpuSectionBedStateOper_OperDT)) ) {
                sw.swMsg.alert(lang['oshibka'], lang['nelzya_dobavit_dve_odinakovyie_operatsii_na_odnu_datu']);
                return false;
            }
        }
    },
    openDBedOperationEditWindow: function(action) {
        if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
            return false;
        }

        if ( this.action == 'view' ) {
            if ( action == 'add' ) {
                return false;
            }
            else if ( action == 'edit' ) {
                action = 'view';
            }
        }

        if ( getWnd('swDBedOperationEditWindow').isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_operatsii_uje_otkryito']);
            return false;
        }

        var formParams = new Object();
        var grid = this.findById('LPEW_DBedOperationGrid').getGrid();
        var params = new Object();
        params.LpuSectionBedState_id = this.LpuSectionBedState_id;
        params.LpuSectionBedStateOper_id = this.LpuSectionBedStateOper_id;
        var selectedRecord;

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('LpuSectionBedStateOper_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

        if ( action == 'add' ) {


            params.onHide = function() {
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                }
            };
        }
        else {
            if ( !selectedRecord ) {
                return false;
            }

            formParams = selectedRecord.data;
            params.LpuSectionBedStateOper_id = grid.getSelectionModel().getSelected().get('LpuSectionBedStateOper_id');
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
            };

            if (params.LpuSectionBedStateOper_id < 0) {
                params.DBedOperation_id = grid.getSelectionModel().getSelected().get('DBedOperation_id');
                params.LpuSectionBedStateOper_OperDT = grid.getSelectionModel().getSelected().get('LpuSectionBedStateOper_OperDT');
            }
        }

        params.action = action;
        params.callback = function(data) {
            if ( typeof data != 'object' || typeof data.DBedOperationData != 'object' ) {
                return false;
            }

            var record = grid.getStore().getById(data.DBedOperationData.LpuSectionBedStateOper_id);

            if ( record ) {

                var grid_fields = new Array();

                if (this.gridFilter(data) == false) {return false;}

                grid.getStore().fields.eachKey(function(key, item) {
                    grid_fields.push(key);
                });

                for ( i = 0; i < grid_fields.length; i++ ) {
                    record.set(grid_fields[i], data.DBedOperationData[grid_fields[i]]);
                }

                record.commit();
            }
            else {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('LpuSectionBedStateOper_id') ) {
                    grid.getStore().removeAll();
                }
                if (this.gridFilter(data) == false) {return false;}

                data.DBedOperationData.LpuSectionBedStateOper_id = -swGenTempId(grid.getStore());
                grid.getStore().loadData([ data.DBedOperationData ], true);
            }

        }.createDelegate(this);
        params.formMode = 'local';
        params.formParams = formParams;

        getWnd('swDBedOperationEditWindow').show(params);
    },
    filterLpuSectionBedProfile: function(LpuSection_id){
		if(!LpuSection_id) return false;
		var that = this;
		sw.Promed.LpuSectionBedProfile.getLpuSectionBedProfileLink({
			LpuSection_id: LpuSection_id,
			callback: function(response_obj) {
				var LpuSectionBedProfiles = [];
				response_obj.forEach(function (el){LpuSectionBedProfiles.push(parseInt(el.LpuSectionBedProfile_id))});
				//накладываю фильтр на профили коек
				var LpuSectionBedProfileCombo = that.MainPanel.getForm().findField('LpuSectionBedProfile_id');
				LpuSectionBedProfileCombo.lastQuery = '';
				LpuSectionBedProfileCombo.getStore().filterBy(function (el) {
					return 0<=LpuSectionBedProfiles.indexOf(el.data.LpuSectionBedProfile_id);
				});

				LpuSectionBedProfileCombo.setBaseFilter(function(rec) {
					return (0 <= LpuSectionBedProfiles.indexOf(rec.get('LpuSectionBedProfile_id')));
				});
				//если значение которые было установлено отфильтровалось, очищаю комбик
				if ( Ext.isEmpty(LpuSectionBedProfileCombo.getStore().getById(LpuSectionBedProfileCombo.getValue())) ) {
					LpuSectionBedProfileCombo.clearValue();
				}
			}
		});
	},
	initComponent: function() {
	    var form = this;

		this.MainPanel = new sw.Promed.FormPanel(
		{
			id:'LpuSectionBedStateEditFormPanel',
			height:this.height,
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			region: 'center',
			labelWidth: 130,
			items:
			[
			{
				name: 'LpuSectionBedState_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lsbsefLpuSectionBedState_id'
			},
            {
				name: 'LpuSectionBedStateOper_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lsbsefLpuSectionBedStateOper_id'
			},
			{
				name: 'LpuSection_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lsbsefLpuSection_id'
			},
			/*
			{
				name: 'LpuSectionBedProfile_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'lsbsefLpuSectionBedProfile_id'
			},
			*/
			{
				name: 'LpuSection_Name',
				disabled: true,
				fieldLabel: lang['otdelenie'],
				tabIndex: 1,
				xtype: 'descfield',
				id: 'lsbsefLpuSection_Name'
			},
            {
                fieldLabel: lang['naimenovanie'],
                xtype: 'textfield',
                //disabled: true,
                autoCreate: {tag: "input", maxLength: "100", autocomplete: "off"},
                anchor: '100%',
                name: 'LpuSectionBedState_ProfileName',
                id: 'lsbsefLpuSectionBedState_ProfileName',
                tabIndex: TABINDEX_LPEEW + 3
            },
			{
				fieldLabel: langs('Профиль отделения'),
				allowBlank: false,
				tabIndex: 1220,
				enableKeyEvents: true,
				editable: false,
				mode: 'local',
				triggerAction: 'all',
				store: new Ext.data.Store({
					url: '/?c=LpuStructure&m=getLpuSectionProfileforCombo',
					autoLoad: false,
					listeners: {
						'load': function(s)
						{
							var combo = this.findById('LpuSectionBedStateEditFormPanel').getForm().findField('LpuSectionProfile_id');
							combo.focus(true, 100);
						}.createDelegate(this)
					},
					reader: new Ext.data.JsonReader({
						id: 'LpuSectionProfile_id'
					}, [
						{mapping: 'LpuSectionProfile_id', name: 'LpuSectionProfile_id', type: 'int'},
						{mapping: 'LpuSectionProfile_Code', name: 'LpuSectionProfile_Code', type: 'int'},
						{mapping: 'LpuSectionProfile_Name', name: 'LpuSectionProfile_Name', type: 'string'}
					])
				}),
				tpl: '<tpl for="."><div class="x-combo-list-item">'+
					'<font color="red">{LpuSectionProfile_Code}</font>&nbsp;{LpuSectionProfile_Name}'+
					'</div></tpl>',
				hiddenName: 'LpuSectionProfile_id',
				name: 'LpuSectionProfile_id',
				displayField: 'LpuSectionProfile_Name',
				valueField: 'LpuSectionProfile_id',
				anchor: '100%',
				xtype: 'combo',
				listeners: {
					'change': function(combo, newValue){
						combo.ownerCt.ownerCt.filterLpuSectionBedProfileLink();
					}
				}
			}, 
			{
				allowBlank: false,
				tabIndex: 1220,
				fieldLabel: langs('Профиль койки'),
				hidden: ( getRegionNick() == 'kz'),
				anchor: '100%',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var LpuSectionBedProfileLink = form.MainPanel.getForm().findField('LpuSectionBedProfile_id');
						LpuSectionBedProfileLink.setValue(combo.getSelectedRecordData().LpuSectionBedProfile_id);
					}.createDelegate(this)
				},
				//xtype:'swlpusectionbedprofilecombo'
				//hiddenName:'LpuSectionBedProfile_id',
				hiddenName:'LpuSectionBedProfileLink_id',
				xtype: 'swlpusectionbedprofilelinkcombo'
			},
			{
				xtype: 'hidden',
				name: 'LpuSectionBedState_Repair'
			},
			{
				hiddenName:'LpuSectionBedProfile_id',
				hidden: ( getRegionNick() != 'kz'),
				tabIndex: 1220,
				fieldLabel: langs('Профиль койки'),
				listeners: {
					'change': function(combo, newValue, oldValue) {
						//
					}
				},
				id:'LpuSectionBedProfileCombo',
				anchor: '100%',
				xtype:'swlpusectionbedprofilecombo'
			},
			/*
			{
				fieldLabel: langs('Профиль койки'),
				allowBlank: false,
				tabIndex: 1220,
				enableKeyEvents: true,
				editable: false,
				mode: 'local',
				triggerAction: 'all',
				store: new Ext.data.Store({
					url: '/?c=LpuStructure&m=getLpuSectionBedProfileforCombo',
					autoLoad: false,
					listeners: {
						'load': function(s)
						{
							var combo = this.findById('LpuSectionBedStateEditFormPanel').getForm().findField('LpuSectionBedProfile_id');
							combo.focus(true, 100);
						}.createDelegate(this)
					},
					reader: new Ext.data.JsonReader({
						id: 'LpuSectionBedProfile_id'
					}, [
						{mapping: 'LpuSectionBedProfile_id', name: 'LpuSectionBedProfile_id', type: 'int'},
						{mapping: 'LpuSectionBedProfile_Code', name: 'LpuSectionBedProfile_Code', type: 'string'},
						{mapping: 'LpuSectionBedProfile_Name', name: 'LpuSectionBedProfile_Name', type: 'string'}
					])
				}),
				tpl: '<tpl for="."><div class="x-combo-list-item">'+
					'<font color="red">{LpuSectionBedProfile_Code}</font>&nbsp;{LpuSectionBedProfile_Name}'+
					'</div></tpl>',
				hiddenName: 'LpuSectionBedProfile_id',
				name: 'LpuSectionBedProfile_id',
				displayField: 'LpuSectionBedProfile_Name',
				valueField: 'LpuSectionBedProfile_id',
				anchor: '100%',
				xtype: 'combo'
			},
			{
				xtype: 'textfield',
				allowBlank: false,
				tabIndex: 1221,
				hiddenName: 'LpuSectionBedState_Plan',
				name: 'LpuSectionBedState_Plan',
				id: 'lsbsefLpuSectionBedState_Plan',				
				fieldLabel: lang['kolichestvo_plan'],
				disabled: true,
				maskRe: /[0-9]/,
				listeners: {
					'change': function(field, newValue, oldValue) {
						// var val = Ext.getCmp('lsbsefLpuSectionBedState_Repair').getValue();
						// var summ = newValue != '' && newValue > 0 ? newValue : 0;
						// summ -= val != '' && val > 0 ? val : 0;
						// Ext.getCmp('lsbsefLpuSectionBedState_Fact').setValue(summ > 0 ? summ : 0);					
					}
				}
			},
			{
				xtype: 'textfield',
				allowBlank: false,
				tabIndex: 1222,
				hiddenName: 'LpuSectionBedState_Repair',
				name: 'LpuSectionBedState_Repair',
				id: 'lsbsefLpuSectionBedState_Repair',				
				fieldLabel: lang['remont'],
				disabled: true,
				value: 0,
				maskRe: /[0-9]/,
				listeners: {
					'change': function(field, newValue, oldValue) {
						var val = Ext.getCmp('lsbsefLpuSectionBedState_Plan').getValue();
						var summ = newValue != '' && newValue > 0 ? newValue : 0;
						summ = (val != '' && val > 0 ? val : 0) - summ;
						Ext.getCmp('lsbsefLpuSectionBedState_Fact').setValue(summ > 0 ? summ : 0);
					}
				}	
			},
			{
				xtype: 'textfield',
				tabIndex:1223,
				hiddenName: 'LpuSectionBedState_Fact',
				name: 'LpuSectionBedState_Fact',
				id: 'lsbsefLpuSectionBedState_Fact',
				allowBlank: false,
				fieldLabel: lang['kolichestvo_fakt'],
				listeners:
				{
					focus: function(f)
					{
						var val = Ext.getCmp('lsbsefLpuSectionBedState_Plan').getValue();
						if(f.getValue() == "" && val != "")
						{
							f.setValue(val);
						}
					},
					change: function(f)
					{
						var val = Ext.getCmp('lsbsefLpuSectionBedState_Plan').getValue();
						var repair = f.ownerCt.getForm().findField('LpuSectionBedState_Repair');
						repair.setValue(val-f.getValue());
					}
				},
				disabled: true,
				maskRe: /[0-9]/
			},
			{
				xtype: 'textfield',
				tabIndex:1223,
				name: 'LpuSectionBedState_CountOms',
				id: 'lsbsefLpuSectionBedState_CountOms',
				fieldLabel: lang['v_t_ch_oplachivaemyih_po_oms'],
				maskRe: /[0-9]/
			},*/
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: langs('Количество коек'),
				style: 'padding: 2; padding-left: 5px',
				items: [
					{
						xtype: 'container',
						autoEl:{},
						layout: 'table',
						layoutConfig: {
							columns: 5
			},
					    defaults:{
			                style: 'text-align:left; margin: 2px 7px;'
			            },  
						items: [
			{
								//colspan: 2,
								html: '',
								width: 100
							}, 
							{
								html: 'Плановое<br>количество'
							}, 
							{
								html: 'Фактическое<br>количество'
							},
							{
								colspan: 2,
								html: ''
							}, 
							{
								xtype: 'label',
								text: 'Мужские койки:',
							}, 
							{
								xtype: 'textfield',
								width: '50%',
								name: 'LpuSectionBedState_MalePlan',
								id: 'lsbsefLpuSectionBedState_MalePlan',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var val = (newValue) ? parseInt(newValue, 10) : 0;
										var onlyPlan = Ext.getCmp('lsbsefLpuSectionBedState_Plan');
										var femalePlanVal = Ext.getCmp('lsbsefLpuSectionBedState_FemalePlan').getValue();
										femalePlanVal = (femalePlanVal) ? parseInt(femalePlanVal, 10) : 0;
										if(val !== NaN && femalePlanVal !== NaN) onlyPlan.setValue(val + femalePlanVal);
									}
								},
								maskRe: /[0-9]/
							}, 
							{
								xtype: 'textfield',
								width: '50%',
								name: 'LpuSectionBedState_MaleFact',
								id: 'lsbsefLpuSectionBedState_MaleFact',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var val = (newValue) ? parseInt(newValue, 10) : 0;
										var onlyFact = Ext.getCmp('lsbsefLpuSectionBedState_Fact');
										var femaleFactVal = Ext.getCmp('lsbsefLpuSectionBedState_FemaleFact').getValue();
										femaleFactVal = (femaleFactVal) ? parseInt(femaleFactVal, 10) : 0;
										if(val !== NaN && femaleFactVal !== NaN) onlyFact.setValue(val + femaleFactVal);
										onlyFact.fireEvent('change',onlyFact);
									}
								},
								maskRe: /[0-9]/
							}, 
							{
								colspan: 2,
								html: ''
							},
							{
								xtype: 'label',
								text: 'Женские койки:',
							},
							{
								xtype: 'textfield',
								width: '50%',
								name: 'LpuSectionBedState_FemalePlan',
								id: 'lsbsefLpuSectionBedState_FemalePlan',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var val = (newValue) ? parseInt(newValue, 10) : 0;
										var onlyPlan = Ext.getCmp('lsbsefLpuSectionBedState_Plan');
										var malePlanVal = Ext.getCmp('lsbsefLpuSectionBedState_MalePlan').getValue();
										malePlanVal = (malePlanVal) ? parseInt(malePlanVal, 10) : 0;
										if(val !== NaN && malePlanVal !== NaN) onlyPlan.setValue(val + malePlanVal);
									}
								},
								maskRe: /[0-9]/
							},
							{
								xtype: 'textfield',
								width: '50%',
								name: 'LpuSectionBedState_FemaleFact',
								id: 'lsbsefLpuSectionBedState_FemaleFact',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var val = (newValue) ? parseInt(newValue, 10) : 0;
										var onlyFact = Ext.getCmp('lsbsefLpuSectionBedState_Fact');
										var maleFactVal = Ext.getCmp('lsbsefLpuSectionBedState_MaleFact').getValue();
										maleFactVal = (maleFactVal) ? parseInt(maleFactVal, 10) : 0;
										if(val !== NaN && maleFactVal !== NaN) onlyFact.setValue(val + maleFactVal);
										onlyFact.fireEvent('change',onlyFact);
									}
								},
								maskRe: /[0-9]/
							},
							{
								colspan: 2,
								html: ''
							},
							{
								xtype: 'label',
								text: 'Всего:',
								style: 'padding: 5px;'
							},
							{
								xtype: 'textfield',
								width: '50%',
								name: 'LpuSectionBedState_Plan',
								id: 'lsbsefLpuSectionBedState_Plan',
								maskRe: /[0-9]/
							},
							{
								xtype: 'textfield',
								width: '50%',
								name: 'LpuSectionBedState_Fact',
								id: 'lsbsefLpuSectionBedState_Fact',
								allowBlank: false,
								maskRe: /[0-9]/,
								listeners: {
									change: function(f){
										var val = Ext.getCmp('lsbsefLpuSectionBedState_Plan').getValue();
										var repair = f.ownerCt.ownerCt.ownerCt.getForm().findField('LpuSectionBedState_Repair');
										repair.setValue(val-f.getValue());
									}
								}
							},
							{
								xtype: 'label',
								text: 'в т.ч. оплачиваемых по ОМС:',
							},
							{
								xtype: 'textfield',
								width: '50%',
								name: 'LpuSectionBedState_CountOms',
								id: 'lsbsefLpuSectionBedState_CountOms',
								maskRe: /[0-9]/
							}
					    ]
					},
				]
			},
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['period_deystviya'],
				style: 'padding: 2; padding-left: 5px',
				items: [
				{
					fieldLabel : lang['data_nachala'],
					tabIndex: 1324,
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionBedState_begDate',
					id: 'lsbsefLpuSectionBedState_begDate'
				},
				{
					fieldLabel : lang['data_okonchaniya'],
					tabIndex: 1325,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionBedState_endDate',
					id: 'lsbsefLpuSectionBedState_endDate'
				}]
			},
            new sw.Promed.Panel({
                autoHeight: true,
                style: 'margin-bottom: 0.5em;',
                border: true,
                collapsible: true,
                //id: 'Lpu_Transport',//Округ горно-санитарной охраны id
                layout: 'form',
                id: 'LPEW_DBedOperation',
                title: lang['operatsii'],
                listeners: {
                    expand: function () {
                        this.findById('LPEW_DBedOperationGrid').loadData({globalFilters:{LpuSectionBedState_id: this.LpuSectionBedState_id}, params:{LpuSectionBedState_id: this.LpuSectionBedState_id}});
                    }.createDelegate(this)
                },
                items: [
                    new sw.Promed.ViewFrame({
                        actions: [
                            {name: 'action_add', handler: function() { this.openDBedOperationEditWindow('add'); }.createDelegate(this) },
                            {name: 'action_edit', handler: function() { this.openDBedOperationEditWindow('edit'); }.createDelegate(this) },
                            {name: 'action_view', handler: function() { this.openDBedOperationEditWindow('view'); }.createDelegate(this) },
                            {name: 'action_delete', handler: function() { this.gridRecordDelete(); }.createDelegate(this) },
                            {name: 'action_refresh', handler: function() { Ext.getCmp('LPEW_DBedOperationGrid').loadData({ globalFilters:{LpuSectionBedState_id: Ext.getCmp('LpuSectionBedStateEditFormPanel').findById('lsbsefLpuSectionBedState_id').getValue()},params:{ LpuSectionBedState_id: Ext.getCmp('LpuSectionBedStateEditFormPanel').findById('lsbsefLpuSectionBedState_id').getValue() } })
                            } },
                            {name: 'action_print'}
                        ],
                        object: 'LpuSectionBedStateOper',
                        editformclassname: 'swDBedOperationEditWindow',
                        autoExpandColumn: 'autoexpand',
                        autoExpandMin: 150,
                        autoLoadData: false,
                        border: false,
                        scheme: 'fed',
                        dataUrl: '/?c=LpuStructure&m=loadDBedOperation',
                        id: 'LPEW_DBedOperationGrid',
                        paging: false,
                        region: 'center',
                        stringfields: [
                            {name: 'LpuSectionBedStateOper_id', type: 'int', header: 'ID', key: true},
                            {name: 'DBedOperation_Name', type: 'string', header: lang['naimenovanie_operatsii'], width: 270},
                            {name: 'DBedOperation_id', type: 'int', header: 'id', hidden: true},
                            {name: 'LpuSectionBedState_id', type: 'int', header: 'id', hidden: true},
                            {name: 'LpuSectionBedStateOper_OperDT', type: 'date', header: lang['data_operatsii'], width: 270}
                        ],
                        toolbar: true,
                        totalProperty: 'totalCount'
                    })
                ]
            })
			],
			reader: new Ext.data.JsonReader(
                {
                    success: function()
                    {
                    //alert('success');
                    }
                },
                [
                    { name: 'LpuSectionBedState_id' },
                    { name: 'LpuSectionBedStateOper_id' },
                    { name: 'LpuSection_id' },
                    { name: 'LpuSectionProfile_id' },
                    { name: 'LpuSectionBedState_ProfileName' },
                    { name: 'LpuSectionBedProfile_id' },
                    { name: 'LpuSectionBedProfileLink_id' },
                    { name: 'LpuSectionBedState_Plan' },
                    { name: 'LpuSectionBedState_Fact' },
                    { name: 'LpuSectionBedState_CountOms' },
                    { name: 'LpuSectionBedState_Repair' },
                    { name: 'LpuSectionBedState_begDate' },
                    { name: 'LpuSectionBedState_endDate' },
                    { name: 'LpuSectionBedState_MalePlan' },
                    { name: 'LpuSectionBedState_MaleFact' },
                    { name: 'LpuSectionBedState_FemalePlan' },
                    { name: 'LpuSectionBedState_FemaleFact' }
                ]
			),
			url: C_LPUSECTIONBEDSTATE_SAVE
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swLpuSectionBedStateEditForm.superclass.initComponent.apply(this, arguments);
	}
});