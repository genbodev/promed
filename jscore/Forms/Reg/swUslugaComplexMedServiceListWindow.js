/**
 * swUslugaComplexMedServiceListWindow - окно направления на службы.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009-2012 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      07.2013
 **/
/*NO PARSE JSON*/
sw.Promed.swUslugaComplexMedServiceListWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh: true,
    objectName: 'swUslugaComplexMedServiceListWindow',
    objectSrc: '/jscore/Forms/Reg/swUslugaComplexMedServiceListWindow.js',

    title: lang['napravlenie_na_slujbyi'],
    buttonAlign: 'left',
    closable: true,
    closeAction: 'hide',
    layout: 'border',
    maximized: true,
    minHeight: 400,
    minWidth: 700,
    modal: true,
    plain: true,
    id: 'UslugaComplexMedServiceListWindow',

    onDirection: Ext.emptyFn,
    onHide: Ext.emptyFn,
    listeners:
    {
        hide: function()
        {
            this.onHide();
        }
    },

    doSearch: function() {
        this.loadGridWithFilter(false);
    },
    doReset: function() {
        this.loadGridWithFilter(true);
    },
    loadGridWithFilter: function(clear) {
        var grid = this.rootViewFrame,
			_this = this;
		var MedService_Caption = this.rootViewFrame.getTopToolbar().items.items[3].getValue();
        grid.removeAll();
        var baseParams = {
            limit: 100,
            start: 0,
            LpuSection_id: this.userMedStaffFact.LpuSection_id
        };
        grid.getGrid().getStore().baseParams = baseParams;
        if (this.dirTypeData) {
            baseParams.DirType_id = this.dirTypeData.DirType_id;
        }
        if (this.evnPrescrData) {
            baseParams.EvnPrescr_id = this.evnPrescrData.EvnPrescr_id;
            baseParams.PrescriptionType_Code = this.evnPrescrData.PrescriptionType_Code;
        }
		if(MedService_Caption!=''){
			baseParams.MedService_Caption = MedService_Caption;
		}

		var cbx = this.rootViewFrame.getTopToolbar().items.items[1];
		if (cbx.checked) {
			baseParams.onlyMyLpu = 1;
		}

        if (clear) {
            grid.loadData({
                callback: function() {
                    //grid.getTopToolbar().items.items[1].setValue(true);
                },
                params: baseParams,
                globalFilters: baseParams
            });
        } else {
            grid.loadData({
                params: baseParams,
                globalFilters: baseParams
            });
        }

    },
    onSelectRecord: function()
    {
        var record = this.rootViewFrame.getGrid().getSelectionModel().getSelected();
        var thas = this;
        if (record && this.dirTypeData && 13 == this.dirTypeData.DirType_Code) {
            this.directionData.EvnDirection_id = null;
            this.directionData.MedService_id = record.data.MedService_id;
            this.directionData.Lpu_did = record.data.Lpu_id;
            if (record.data.FirstFreeDate.toString().length > 0) {
                this.personData.LpuSectionProfileIdList = record.data.FirstFreeDate.split(',');
            } else {
				Ext.Msg.alert(lang['oshibka'], lang['otsutstvuet_svobodnoe_vremya_dlya_napravleniya']);
                return false;
            }
            this.personData.action = 'add';
            this.personData.EvnDirection_id = null;
            this.personData.callback = function(){
                thas.hide();
                thas.onDirection(); 
				//BOB - 22.04.2019
				if (thas.directionData.parentWindow_id) {
					Ext.getCmp(thas.directionData.parentWindow_id).fireEvent('success', thas.id, {EvnDirection_id: arguments[0].evnDirectionData.EvnDirection_id});
				}
				//BOB - 22.04.2019
            };
            this.personData.formParams = this.directionData;
            getWnd('swEvnDirectionEditWindow').show(this.personData);
            return true;
        }
        if (!record)
        {
            Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
            return false;
        }
        if (this.evnPrescrData) {
            if (!thas.Order) {
                var callback = function(scope, id, values){
                    thas.Order = values;
                    thas.Order.Usluga_isCito = thas.evnPrescrData.EvnPrescr_IsCito;
                    thas.onSelectRecord();
                };
                var useWindow = (this.evnPrescrData.PrescriptionType_Code == 11);
                var uc_prescid = (this.evnPrescrData.PrescriptionType_Code == 11)?record.data.UslugaComplex_id:null;
                sw.Promed.Direction.createOrder(record, this.personData, callback, useWindow, null, uc_prescid, thas.evnPrescrData.EvnPrescr_IsCito);
                return false;
            }
            this.directionData.EvnPrescr_id = this.evnPrescrData.EvnPrescr_id;
            this.directionData.PrescriptionType_Code = this.evnPrescrData.PrescriptionType_Code;
        }

        var params = {
            Order: this.Order,
            Person: this.personData,
            MedService_id: record.data.MedService_id,
            MedServiceType_id: record.data.MedServiceType_id,
            MedService_Nick: record.data.MedService_Nick,
            MedService_Name: record.data.MedService_Name,
            MedServiceType_SysNick: record.data.MedServiceType_SysNick,
            Lpu_did: record.data.Lpu_id,
            LpuUnitType_SysNick: record.data.LpuUnitType_SysNick,
            LpuSection_uid: record.data.LpuSection_id,
            LpuSection_Name: record.data.LpuSection_Name,
            LpuUnit_did: record.data.LpuUnit_id,
            LpuSectionProfile_id: record.data.LpuSectionProfile_id,
            //fromEmk: true,
            callback: function(){
                getWnd('swTTMSScheduleRecordWindow').hide();
                thas.hide();
                thas.onDirection();
            },
            userMedStaffFact: this.userMedStaffFact,
            UslugaComplexMedService_id: record.get('UslugaComplexMedService_id') || null,
            UslugaComplex_id: record.get('UslugaComplex_id') || null,
            Diag_id: this.directionData.Diag_id || null,
            EvnDirection_pid: this.directionData.EvnDirection_pid || null,
            EvnQueue_id: this.directionData.EvnQueue_id || null,
            QueueFailCause_id: this.directionData.QueueFailCause_id || null,
            EvnPrescr_id: this.directionData.EvnPrescr_id || null,
            PrescriptionType_Code: this.directionData.PrescriptionType_Code || null,
			EvnPrescrVKData: this.directionData.EvnPrescrVKData || null
        };

        if (this.dirTypeData && 8 == this.dirTypeData.DirType_Code) {
            // Направление на ВК или МСЭ
            params.ARMType = record.data.MedServiceType_SysNick;
            /*params.userClearTimeMS = function() {
                this.getLoadMask(lang['osvobojdenie_zapisi']).show();
                Ext.Ajax.request({
                    url: '/?c=Mse&m=clearTimeMSOnEvnPrescrVK',
                    params: {
                        TimetableMedService_id: this.TimetableMedService_id
                    },
                    callback: function(o, s, r) {
                        this.getLoadMask().hide();
                        if(s) {
                            this.loadSchedule();
                        }
                    }.createDelegate(this)
                });
            };*/
        }
        getWnd('swTTMSScheduleRecordWindow').show(params);
        return true;
    },

	addOtherLpuDirection: function() {
		var directionData = {
			userMedStaffFact: Ext.apply({}, this.userMedStaffFact),
			person: Ext.apply({}, this.personData),
			direction: Ext.apply({}, this.directionData),
			callback: function(data){
				this.onDirection(data);
				if (data) {
					this.hide();
				}
			}.createDelegate(this),
			mode: 'nosave',
			windowId: this.getId()
		};
		directionData.direction.LpuUnitType_SysNick = 'parka';
		directionData.direction.isNotForSystem = true;
		sw.Promed.Direction.queuePerson(directionData);
	},

    show: function() {

        sw.Promed.swUslugaComplexMedServiceListWindow.superclass.show.apply(this, arguments);

        if ( !arguments[0]
            || typeof arguments[0].userMedStaffFact != 'object'
            || (typeof arguments[0].dirTypeData != 'object' && typeof arguments[0].evnPrescrData != 'object')
            || typeof arguments[0].directionData != 'object'
        ) {
            return false;
        }

        var cbx = this.rootViewFrame.getTopToolbar().items.items[1];

        this.userMedStaffFact = arguments[0].userMedStaffFact;
        this.directionData = arguments[0].directionData;
        this.dirTypeData = arguments[0].dirTypeData || null;
        this.evnPrescrData = arguments[0].evnPrescrData || null;
        this.Order = false;
        this.personData = null;

        if ( typeof arguments[0].personData == 'object' ) {
            this.personData = arguments[0].personData;
        }
		if (getRegionNick().inlist(['astra','ekb','msk']) && this.dirTypeData.DirType_Code.inlist(['13'])) {
			this.rootViewFrame.getAction('action_view').show();
		} else {
			this.rootViewFrame.getAction('action_view').hide();
		}

		if (this.dirTypeData.DirType_Code.inlist(['8', '13'])) {
			this.rootViewFrame.getTopToolbar().show();
			cbx.setValue(this.dirTypeData.DirType_Code == '8');
		} else {
			this.rootViewFrame.getTopToolbar().hide()
			cbx.setValue(true);
		}

        this.onDirection = (typeof arguments[0].onDirection == 'function')
            ? arguments[0].onDirection
            : Ext.emptyFn;

        this.onHide =  (typeof arguments[0].onHide == 'function')
            ? arguments[0].onHide
            : Ext.emptyFn;

        this.doReset();

        return true;
    },
    initComponent: function() {
        var thas = this;

        this.rootViewFrame = new sw.Promed.ViewFrame({
            id: 'UslugaComplexMedServiceListViewFrame',
            actions: [
                { name: 'action_add', hidden: true, disabled: true },
                { name: 'action_edit', text: lang['vyibrat'], tooltip: lang['napravit_na_vyibrannuyu_slujbu'], icon: 'img/icons/ok16.png', handler: function(){ thas.onSelectRecord(); } },
				{ name: 'action_view', text: 'Направление в другую МО', icon: 'img/icons/ok16.png', handler: function(){ thas.addOtherLpuDirection(); } },
                { name: 'action_delete', hidden: true, disabled: true },
                { name: 'action_refresh' },
                { name: 'action_print' },
				{name:'action_search'
				}
            ],
            stringfields: [
                {name: 'MedService_id', type: 'int', header: 'ID', key: true},
                {name: 'UslugaComplexMedService_id', type: 'int', header: 'ID', key: true},
                {name: 'UslugaComplex_id', hidden: true},
                {name: 'LpuUnit_id', hidden: true, isparams: true},
                {name: 'Lpu_id', hidden: true, isparams: true},
                {name: 'LpuBuilding_id', hidden: true},
                {name: 'LpuSection_id', hidden: true},
                {name: 'LpuUnitType_id', hidden: true},
                {name: 'LpuSectionProfile_id', hidden: true},
                {name: 'MedServiceType_id', hidden: true},
                {name: 'LpuUnitType_SysNick', hidden: true},
                {name: 'MedService_Nick', hidden: true},
                {name: 'MedServiceType_SysNick', hidden: true},
                {name: 'FirstFreeDate', type: 'string', hidden: true},
                {name: 'user_Lpu_id', hidden: true},
                {name: 'user_LpuBuilding_id', hidden: true},
                {name: 'user_LpuUnit_id', hidden: true},
                {name: 'user_LpuSection_id', hidden: true},
                { name: 'Lpu_Nick', type: 'string', header: lang['lpu'], width: 100 },
                { name: 'LpuBuilding_Name', type: 'string', header: lang['podrazdelenie'], width: 120 },
                { name: 'LpuUnit_Name', type: 'string', header: lang['gruppa_otdeleniy'], width: 120 },
                { name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 120 },
                { name: 'LpuUnit_Address', type: 'string', header: lang['adres'], width: 100},
                { name: 'MedService_Name', type: 'string', header: lang['slujba'], width: 120 },
                { name: 'UslugaComplex_Name', type: 'string', header: lang['usluga'], autoexpand: true, autoExpandMin: 150 },
                { name: 'FirstFreeTime', header: lang['pervoe_svobodnoe_vremya'], width: 150, renderer: function(value, cellEl, rec){
                    if (value) {
                        return rec.get('FirstFreeDate') +' '+ value;
                    }
                    return '';
                } }
            ],
            autoLoadData: false,
            border: false,
            dataUrl: '/?c=MedService&m=loadUslugaComplexMedServiceList',
            object: 'UslugaComplexMedService',
            layout: 'fit',
            root: 'data',
            totalProperty: 'totalCount',
            paging: true,
            region: 'center',
            toolbar:true,
			tbar:new Ext.Toolbar(
		{
			id:this.id+"toolbar",
			labelAlign: 'left',
			items:[{
                xtype:'label',
                text: "Только своя МО: ",
                name: 'label_current_MO',
                style: 'margin-left:7px;font-size:13px;'
            },{
                xtype: 'checkbox',
                checked: true,
                name: 'current_MO',
				tabIndex: 120,
                listeners: {
                    'check': function (cbx)
                    {
						this.loadGridWithFilter(false);
                    }.createDelegate(this)
                }

            },{
							xtype:'label',
							text: "Служба: ",
							name: 'labDays',
							style: 'margin-left:7px;font-size:13px;'
				},
				{
					enableKeyEvents: true,
					id: 'MedService_Caption',
					name: 'MedService_Caption',
					xtype: 'textfield',
					listeners: {
						'keydown': function (inp, e)
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								thas.loadGridWithFilter();
							}
						}.createDelegate(this)
					}
				},
				{
				tabIndex: 120,
				xtype: 'button',
				id: 'BtnMPSearch',
				text: lang['nayti'],
				iconCls: 'search16',
				handler: function() {
					thas.loadGridWithFilter();
				}
			},
			{
				tabIndex: 120,
				xtype: 'button',
				id: 'BtnMPClear',
				text: lang['ochistit'],
				iconCls: 'delete16',
				handler: function() {
					thas.rootViewFrame.getTopToolbar().items.items[1].setValue(true);
					thas.rootViewFrame.getTopToolbar().items.items[3].setValue('');
					thas.loadGridWithFilter(true);
				}
			}

			]
		}),
            onLoadData: function() {

                var wnd = thas,
                    cbx = wnd.rootViewFrame.getTopToolbar().items.items[1];

                thas.isAutoApply = false;

                /*
                 Если имеется только одна служба нашего отделения или подразделения или ЛПУ, то автоматом открывается окно с расписанием службы,
                 после направления закрывается окно расписания и окно направления на службы (если эта служба не устаривает, то просто надо закрыть окно с расписанием. При этом созданный заказ услуги аннулируется.).
                 */
                if (thas.evnPrescrData) {

                    var grid = this.getGrid();
                    var cnt = 0;
                    var records = [];

                    grid.getStore().each(function(rec){
                        if (!rec.get('UslugaComplexMedService_id')) {
                            return false;
                        }
                        if (rec.get('LpuSection_id') == rec.get('user_LpuSection_id')
                            || rec.get('LpuUnit_id') == rec.get('user_LpuUnit_id')
                            || rec.get('LpuBuilding_id') == rec.get('user_LpuBuilding_id')
                            || rec.get('Lpu_id') == rec.get('user_Lpu_id')
                        ) {
                            cnt++;
                            records.push(rec);
                        }
                        return true;
                    }, thas);

                    if (cnt == 1) {
                        var index = grid.getStore().indexOf(records[0]);
                        grid.getView().focusRow(index);
                        grid.getSelectionModel().selectRow(index);
                        grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel(), index, grid.getSelectionModel().getSelected());
                        thas.onSelectRecord();
                        thas.isAutoApply = true;
                    }
                }
            },
            onDblClick: function() {
                thas.onSelectRecord();
            },
            onEnter: function() {
                thas.onSelectRecord();
            }
        });

        Ext.apply(this, {
            buttons: [{
                text: '-'
            },
            HelpButton(this),
            {
                handler: function() {
                    thas.hide();
                },
                iconCls: 'cancel16',
                onTabAction: function() {
                    //
                },
                text: BTN_FRMCLOSE
            }],
            items: [
                this.rootViewFrame
            ]
        });
        sw.Promed.swUslugaComplexMedServiceListWindow.superclass.initComponent.apply(this, arguments);
    }
});
