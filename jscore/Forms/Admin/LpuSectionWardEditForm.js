/**
* swLpuSectionWardEditForm - окно просмотра и редактирования палат
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009-2011 Swan Ltd.
* @author       Alexander Permyakov
* @version      15.03.2011
*/
/*NO PARSE JSON*/
sw.Promed.swLpuSectionWardEditForm = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swLpuSectionWardEditForm',
	objectSrc: '/jscore/Forms/Admin/LpuSectionWardEditForm.js',
	title:lang['palata_dobavlenie'],
	id: 'LpuSectionWardEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 700,
	height: 700,
	modal: true,
	
	show: function() {
		sw.Promed.swLpuSectionWardEditForm.superclass.show.apply(this, arguments);
		if (!arguments[0] || !arguments[0].action || !arguments[0].LpuSection_id || !arguments[0].LpuSection_Name)
		{
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		this.action = arguments[0].action;
		this.LpuSection_Name = arguments[0].LpuSection_Name;
		this.LpuSectionWard_Floor = arguments[0].LpuSectionWard_Floor;
		this.LpuUnitType_id = arguments[0].LpuUnitType_id || null;
        this.LpuSection_id = arguments[0].LpuSection_id;
        this.LpuSectionWard_id = arguments[0].LpuSectionWard_id || null;
        //this.LpuSectionWardComfortLink_id = arguments[0].LpuSectionWardComfortLink_id || null;
		this.Sex_id = arguments[0].Sex_id || null;
		this.owner = arguments[0].owner || null;
        //this.callback = Ext.emptyFn;

		if(arguments[0].callback) {
            this.returnFunc = arguments[0].callback;
            //this.callback = arguments[0].callback;
        }

		var form = this;
		var base_form = form.findById('LpuSectionWardEditFormPanel').getForm();
		base_form.reset();
		base_form.findField('LpuSectionWard_id').setValue(this.LpuSectionWard_id);
		base_form.findField('LpuSection_id').setValue(this.LpuSection_id);
		base_form.findField('LpuSectionWard_Floor').setValue(this.LpuSectionWard_Floor);
		base_form.findField('LpuSection_Name').setValue(this.LpuSection_Name);
		if(this.Sex_id == null)
		{
			base_form.findField('Sex_id').setValue(0);
		}

		switch (this.action)
		{
			case 'add':
				form.setTitle(lang['palata_dobavlenie']);
				base_form.findField('LpuSectionWard_DayCost').setValue('0');
				break;
			case 'edit':
				form.setTitle(lang['palata_redaktirovanie']);
				break;
			case 'view':
				form.setTitle(lang['palata_prosmotr']);
				break;
		}
		if (this.action=='view')
		{
			base_form.findField('LpuSectionWard_setDate').disable();
			base_form.findField('LpuSectionWard_disDate').disable();
			base_form.findField('LpuSectionWard_Floor').disable();
			base_form.findField('LpuSectionWard_Name').disable();
			base_form.findField('LpuSectionWard_DayCost').disable();
			base_form.findField('LpuSectionWard_BedRepair').disable();
			base_form.findField('LpuWardType_id').disable();
			base_form.findField('Sex_id').disable();
			//base_form.findField('LpuSectionWard_BedCount').disable();
			base_form.findField('LpuSectionWard_CountRoom').disable();
			base_form.findField('LpuSectionWard_DopPlace').disable();
            base_form.findField('LpuSectionWard_MainPlace').disable();
			base_form.findField('LpuSectionWard_Views').disable();
			base_form.findField('LpuSectionWard_Square').disable();
			form.buttons[0].disable();
		}
		else
		{
			base_form.findField('LpuSectionWard_setDate').enable();
			base_form.findField('LpuSectionWard_disDate').enable();
			base_form.findField('LpuSectionWard_Name').enable();
			base_form.findField('LpuSectionWard_Floor').enable();
			base_form.findField('LpuSectionWard_DayCost').enable();
			base_form.findField('LpuSectionWard_BedRepair').enable();
			base_form.findField('LpuWardType_id').enable();
			base_form.findField('Sex_id').enable();
			//base_form.findField('LpuSectionWard_BedCount').enable();
			base_form.findField('LpuSectionWard_CountRoom').enable();
			base_form.findField('LpuSectionWard_DopPlace').enable();
            base_form.findField('LpuSectionWard_MainPlace').enable();
			base_form.findField('LpuSectionWard_Views').enable();
			base_form.findField('LpuSectionWard_Square').enable();
			form.buttons[0].enable();
		}
		if (this.action!='add')
		{
			form.loadMask = form.getLoadMask(LOAD_WAIT);
			form.loadMask.show();
			form.findById('LpuSectionWardEditFormPanel').getForm().load({
				url: C_LPUSECTIONWARD_GET,
				params: {
					LpuSectionWard_isAct: 1,
					LpuSectionWard_id: this.LpuSectionWard_id,
					LpuSection_id: this.LpuSection_id
				},
				success: function () {
					form.loadMask.hide();
					base_form.findField('LpuSectionWard_Name').focus(true, 100);
					if(form.Sex_id == null)
					{
						base_form.findField('Sex_id').setValue(0);
					}
				},
				failure: function () {
					form.loadMask.hide();
					Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
				}
			});
		}
		else
		{
			base_form.findField('LpuSectionWard_Name').focus(true, 100);
		}

        this.findById('LPEW_DChamberComfortGrid').loadData({globalFilters:{LpuSectionWard_id: this.LpuSectionWard_id},params:{LpuSectionWard_id: this.LpuSectionWard_id}});
	},
	
	returnFunc: function(owner, kid) {},
	doSave: function() {

        this.formStatus = 'save';
        var cur = this;
		var form = this.MainPanel;
		var base_form = form.getForm();
		if (!base_form.isValid())
		{
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

		var begDate = base_form.findField('LpuSectionWard_setDate').getValue();
		var endDate = base_form.findField('LpuSectionWard_disDate').getValue();
		var BedCount = base_form.findField('LpuSectionWard_MainPlace').getValue();
		var CountRoom = base_form.findField('LpuSectionWard_CountRoom').getValue();
		var BedRepair = base_form.findField('LpuSectionWard_BedRepair').getValue();
		var DayCost = base_form.findField('LpuSectionWard_DayCost').getValue();
		//var LpuSectionBedState_Plan = base_form.findField('LpuSectionBedState_Plan').getValue();
		//var LpuSectionBedState_Repair = base_form.findField('LpuSectionBedState_Repair').getValue();

		if ((BedRepair) && (BedCount) && (parseInt(BedRepair,10)>parseInt(BedCount,10))) {
			Ext.Msg.alert(lang['oshibka'], lang['kolichestvo_koek_na_remonte_ne_mojet_prevyishat_ih_obschego_kolichestva']);
			return false;
		}
		
		if (BedCount.length>4) {
			Ext.Msg.alert(lang['oshibka'], lang['slishkom_dlinnoe_znachenie_polya_kolichestvo_koek']);
			return false;
		}
		else
		if (BedRepair.length>4) {
			Ext.Msg.alert(lang['oshibka'], lang['slishkom_dlinnoe_znachenie_polya_koyki_na_remonte']);
			return false;
		}
		else
		if (DayCost.length>10) {
			Ext.Msg.alert(lang['oshibka'], lang['slishkom_dlinnoe_znachenie_polya_stoimost_nahojdeniya_v_sutki']);
			return false;
		}
        else
		if (CountRoom.length>2) {
			Ext.Msg.alert(lang['oshibka'], lang['slishkom_dlinnoe_znachenie_polya_kolichestvo_komnat_v_palate']);
			return false;
		}
		
		if ((begDate) && (endDate) && (begDate>endDate)) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('LpuSectionWard_disDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        var params = new Object();

        // Собираем данные из грида
        var DChamberComfortGrid = this.findById('LPEW_DChamberComfortGrid').getGrid();
        DChamberComfortGrid.getStore().clearFilter();

        if ( DChamberComfortGrid.getStore().getCount() > 0 ) {
            var DChamberComfortData = getStoreRecords(DChamberComfortGrid.getStore(), {
                convertDateFields: true,
                exceptionFields: [
                    'DChamberComfort_Name'
                    ,'pmUser_Name'
                ]
            });

            params.DChamberComfortData = Ext.util.JSON.encode(DChamberComfortData);
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
                        sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
                    }
                }
            }.createDelegate(this),
            params: params,
            success: function(result_form, action) {
                this.formStatus = 'edit';
                loadMask.hide();

                if ( action.result ) {
                    if ( action.result.LpuSectionWard_id > 0 ) {
                        base_form.findField('LpuSectionWard_id').setValue(action.result.LpuSectionWard_id);

                        var data = new Object();

                        data.LpuSectionWard = [{
                            'LpuSectionWard_id': base_form.findField('LpuSectionWard_id').getValue(),
                            'accessType': 'edit'
                        }];
                        //cur.callback(data);
                        cur.hide();
                        cur.returnFunc(cur.owner, action.result.LpuSectionWard_id);
                    }
                    else {
                        if ( action.result.Error_Msg ) {
                            sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                        }
                        else {
                            sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
                        }
                    }
                }
                else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                }
            }.createDelegate(this),
            callback: function() {
                grid.getStore().loadData([data.DChamberComfortData], true);
            }.createDelegate(this)

        });
	},
    gridRecordDelete: function() {
        var wnd = this;

        if ( this.action == 'view' ) {
            return false;
        }

         sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ( buttonId == 'yes' ) {
                    var grid = this.findById('LPEW_DChamberComfortGrid').getGrid();
                    var idField = 'LpuSectionWardComfortLink_id';

                    if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
                        return false;
                    }

                    var record = grid.getSelectionModel().getSelected();
                    var params = new Object();
                    params.LpuSectionWardComfortLink_id = record.get('LpuSectionWardComfortLink_id');
                    var url = "/?c=LpuStructure&m=deleteSectionWardComfortLink";
                    var index = 0;
                    if (record.get('LpuSectionWardComfortLink_id') > 0) {
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
            msg: lang['vyi_deystvitelno_hotite_udalit_obyekt_komfortnosti'],
            title: lang['vopros']
        });
    },
    openDChamberComfortEditWindow: function(action) {
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

        if ( getWnd('swDChamberComfortEditWindow').isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_obyekta_komfortnosti_uje_otkryito']);
            return false;
        }

        //var base_form = this.MainPanel.getForm();
        var deniedComfortTypeList = new Array();
        var formParams = new Object();
        var grid = this.findById('LPEW_DChamberComfortGrid').getGrid();
        var params = new Object();
        params.LpuSection_id = this.LpuSection_id;
        params.LpuSectionWard_id = this.LpuSectionWard_id;
        var selectedRecord;

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('LpuSectionWardComfortLink_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

        if ( action == 'add' ) {


            params.onHide = function() {
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                }
            };

            grid.getStore().each(function(rec) {
                if ( rec.get('DChamberComfort_id') ) {
                    deniedComfortTypeList.push(rec.get('DChamberComfort_id'));
                }
            });

        }
        else {
            if ( !selectedRecord ) {
                return false;
            }

            grid.getStore().each(function(rec) {
                if ( rec.get('DChamberComfort_id') && selectedRecord.get('DChamberComfort_id') != rec.get('DChamberComfort_id') ) {
                    deniedComfortTypeList.push(rec.get('DChamberComfort_id'));
                }
            });


            formParams = selectedRecord.data;
            params.LpuSectionWardComfortLink_id = grid.getSelectionModel().getSelected().get('LpuSectionWardComfortLink_id');
                //log(grid.getSelectionModel().getSelected().get('LpuSectionWardComfortLink_id'));
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
            };

            if (params.LpuSectionWardComfortLink_id < 0) {
                params.DChamberComfort_id = grid.getSelectionModel().getSelected().get('DChamberComfort_id');
                params.LpuSectionWardComfortLink_Count = grid.getSelectionModel().getSelected().get('LpuSectionWardComfortLink_Count');
            }

        }

        params.action = action;
        params.callback = function(data) {
            if ( typeof data != 'object' || typeof data.DChamberComfortData != 'object' ) {
                return false;
            }

            var record = grid.getStore().getById(data.DChamberComfortData.LpuSectionWardComfortLink_id);

            if ( record ) {

                var grid_fields = new Array();

                grid.getStore().fields.eachKey(function(key, item) {
                    grid_fields.push(key);
                });

                for ( i = 0; i < grid_fields.length; i++ ) {
                    record.set(grid_fields[i], data.DChamberComfortData[grid_fields[i]]);
                }

                record.commit();
            }
            else {
                if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('LpuSectionWardComfortLink_id') ) {
                    grid.getStore().removeAll();
                }

                data.DChamberComfortData.LpuSectionWardComfortLink_id = -swGenTempId(grid.getStore());

                grid.getStore().loadData([ data.DChamberComfortData ], true);
            }
        }.createDelegate(this);
        params.deniedComfortTypeList = deniedComfortTypeList;
        params.formMode = 'local';
        params.formParams = formParams;

        getWnd('swDChamberComfortEditWindow').show(params);
    },
	initComponent: function() {
        var form = this;

        this.MainRecordAdd = function() {
            log(form.LpuSectionWard_id);
            if (Ext.isEmpty(form.LpuSectionWard_id)) {
                Ext.Msg.alert('Ошибка!', 'Что бы добавить объект комфортности сохраните палату');
            } else {
                log('true');
                return true;
            }

        }



		this.MainPanel = new sw.Promed.FormPanel(
		{
			id:'LpuSectionWardEditFormPanel',
			//height:this.height,
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: true,
			labelWidth: 180,
			region: 'center',
			items:[
			{
				name: 'Server_id',
				tabIndex: -1,
				xtype: 'hidden'
			},
			{
				name: 'LpuSectionWard_id',
				id: 'LSWE_LpuSectionWard_id',
				tabIndex: -1,
				xtype: 'hidden'
			},
			{
				name: 'LpuSection_id',
				id: 'LSWE_LpuSection_id',
				tabIndex: -1,
				xtype: 'hidden'
			},
			/*{
				name: 'LpuSectionBedState_Plan',
				tabIndex: -1,
				xtype: 'hidden'
			},
			{
				name: 'LpuSectionBedState_Repair',
				tabIndex: -1,
				xtype: 'hidden'
			},*/
			{
				name: 'LpuSection_Name',
				disabled: true,
				fieldLabel: lang['otdelenie'],
				tabIndex: 1,
				xtype: 'descfield'
			}, {
				name: 'LpuSectionWard_Floor',//#175315
				fieldLabel: langs('Этаж'),
				tabIndex: 1,
				xtype: 'numberfield',
				width: 151,
				allowBlank: false,
				autoCreate: {tag: "input", size:2, maxLength: "2", autocomplete: "off"},
				maskRe: /[0-9]/,
				validator: function(a){return (a.match(/^([0-9]|1[0-5])$/))?true:this.setValue(15);}
			}, {
				xtype: 'textfield',
				allowBlank: false,
				tabIndex:122000,
				name: 'LpuSectionWard_Name',
				fieldLabel: lang['naimenovanie_nomer'],
				maxLength: 64
			},/*
			{
				allowBlank: false,
				comboSubject: 'LpuWardType',
				fieldLabel: lang['komfortnost_palatyi'],
				hiddenName: 'LpuWardType_id',
				tabIndex: 122001,
				width: 288,
				xtype: 'swcommonsprcombo'
			},*/
			{
				allowBlank: false,
				fieldLabel: lang['vid_palatyi'],
				mode: 'local',
				store: new Ext.data.SimpleStore(
				{
					key: 'Sex_id',
					fields:
					[
						{name: 'Sex_id', type: 'int'},
						{name: 'Sex_Name', type: 'string'}
					],
					data: [[0, 'Общая'], [1,'Мужская'], [2,'Женская']]
				}),
				editable: false,
				triggerAction: 'all',
				displayField: 'Sex_Name',
				valueField: 'Sex_id',
				tpl: '<tpl for="."><div class="x-combo-list-item">{Sex_Name}</div></tpl>',
				hiddenName: 'Sex_id',
				tabIndex: 122002,
				xtype: 'combo'
			},
			{
				allowBlank: false,
	            typeCode: 'int',
				comboSubject: 'LpuWardType',
				fieldLabel: lang['tip_palatyi'],
				hiddenName: 'LpuWardType_id',
				tabIndex: 122001,
				width: 288,
				xtype: 'swcommonsprcombo'
			},
            {
                xtype: 'textfield',
                tabIndex:122003,
                name: 'LpuSectionWard_CountRoom',
                fieldLabel: lang['kolichestvo_komnat_v_palate'],
                maskRe: /[0-9]/
            },
			{
				xtype: 'textfield',
				allowBlank: false,
                disabled: true,
				tabIndex: 122004,
				name: 'LpuSectionWard_BedCount',
				fieldLabel: lang['obschee_kolichestvo_mest_koek'],
				maskRe: /[0-9]/
			},
            {
                xtype: 'textfield',
                allowBlank: false,
                tabIndex: 122004,
                name: 'LpuSectionWard_MainPlace',
                listeners: {
                    'change': function(field,value){
                        if(Ext.isEmpty(value))
                            value = 0;
                        var dopPlaceField = this.MainPanel.getForm().findField('LpuSectionWard_DopPlace');
                        var BedCountField = this.MainPanel.getForm().findField('LpuSectionWard_BedCount');
                        if(Ext.isEmpty(dopPlaceField.getValue()))
                            BedCountField.setValue(parseInt(value));
                        else
                            BedCountField.setValue(parseInt(value)+parseInt(dopPlaceField.getValue()));
                      }.createDelegate(this)
                },
                fieldLabel: lang['kolichestvo_osnovnyih_mest_koek'],
                maskRe: /[0-9]/
            },
            {
                xtype: 'textfield',
                tabIndex: 122004,
                name: 'LpuSectionWard_DopPlace',
                listeners: {
                    'change': function(field,value){
                        if(Ext.isEmpty(value))
                            value = 0;
                        var MainPlaceField = this.MainPanel.getForm().findField('LpuSectionWard_MainPlace');
                        var BedCountField = this.MainPanel.getForm().findField('LpuSectionWard_BedCount');
                        if(Ext.isEmpty(MainPlaceField.getValue()))
                            BedCountField.setValue(parseInt(value));
                        else
                            BedCountField.setValue(parseInt(value)+parseInt(MainPlaceField.getValue()));
                    }.createDelegate(this)
                },
                fieldLabel: lang['kolichestvo_dopolnitelnyih_mest_koek'],
                maskRe: /[0-9]/
            },
			{
				xtype: 'textfield',
				allowBlank: false,
				tabIndex: 122005,
				name: 'LpuSectionWard_BedRepair',
				fieldLabel: lang['koyki_na_remonte'],
				value: 0,
				maskRe: /[0-9]/
			},
			{
				xtype: 'textfield',
				allowBlank: false,
				emptyText: 0,
				tabIndex: 122006,
				name: 'LpuSectionWard_DayCost',
				fieldLabel: lang['stoimost_nahojdeniya_v_sutki'],
				maskRe: /[0-9]/
			},
            {
                xtype: 'numberfield',
                allowBlank: false,
                tabIndex: 122005,
                name: 'LpuSectionWard_Square',
                fieldLabel: lang['ploschad_palatyi_kv_m'],
                value: 0,
                maskRe: /[0-9]/
            },
            {
                xtype: 'textfield',
                tabIndex: 122005,
                name: 'LpuSectionWard_Views',
                fieldLabel: lang['vid_iz_okna']
            },
			{
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['period_deystviya'],
				style: 'padding: 2; padding-left: 5px',
				items: [
				{
					fieldLabel : lang['nachalo'],
					tabIndex: 122007,
					allowBlank: false,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionWard_setDate'
				},
				{
					fieldLabel : lang['okonchanie'],
					tabIndex: 122008,
					xtype: 'swdatefield',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					name: 'LpuSectionWard_disDate'
				}]
			},
            new sw.Promed.Panel({
                autoHeight: true,
                style: 'margin-bottom: 0.5em;',
                border: true,
                collapsible: true,
                layout: 'form',
                id: 'LPEW_DChamberComfort',
                title: lang['obyektyi_komfortnosti'],
                listeners: {
                    expand: function () {
                        this.findById('LPEW_DChamberComfortGrid').loadData({globalFilters:{LpuSectionWard_id: this.LpuSectionWard_id}, params:{LpuSectionWard_id: this.LpuSectionWard_id}});
                    }.createDelegate(this)
                },
                items: [
                    new sw.Promed.ViewFrame({
                        actions: [
                            {name: 'action_add', handler: function() { this.openDChamberComfortEditWindow('add'); }.createDelegate(this) },
                            {name: 'action_edit', handler: function() { this.openDChamberComfortEditWindow('edit'); }.createDelegate(this) },
                            {name: 'action_view', handler: function() { this.openDChamberComfortEditWindow('view'); }.createDelegate(this) },
                            {name: 'action_delete', handler: function() { this.gridRecordDelete(); }.createDelegate(this) },
                            {name: 'action_refresh', handler: function() { Ext.getCmp('LPEW_DChamberComfortGrid').loadData({ globalFilters:{LpuSectionWard_id: Ext.getCmp('LpuSectionWardEditFormPanel').findById('LSWE_LpuSectionWard_id').getValue()},params:{ LpuSectionWard_id: Ext.getCmp('LpuSectionWardEditFormPanel').findById('LSWE_LpuSectionWard_id').getValue() } })
                            } },
                            {name: 'action_print'}
                        ],
                        object: 'LpuSectionWardComfortLink',
                        editformclassname: 'swDChamberComfortEditWindow',
                        autoExpandColumn: 'autoexpand',
                        autoExpandMin: 150,
                        autoLoadData: false,
                        border: false,
                        scheme: 'fed',
                        dataUrl: '/?c=LpuStructure&m=loadLpuSectionWardComfortLink',
                        id: 'LPEW_DChamberComfortGrid',
                        paging: false,
                        region: 'center',
                        stringfields: [
                            {name: 'LpuSectionWardComfortLink_id', type: 'int', header: 'ID', key: true},
                            {name: 'LpuSectionWard_id', type: 'int', header: 'id', hidden: true},
                            {name: 'DChamberComfort_id', type: 'int', header: 'id', hidden: true},
                            {name: 'DChamberComfort_Name', type: 'string', header: lang['naimenovanie'], width: 270},
                            {name: 'LpuSectionWardComfortLink_Count', type: 'int', header: lang['kolichestvo'], width: 270}
                        ],
                        toolbar: true,
                        totalProperty: 'totalCount'
                    })
                ]
            })],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
				//alert('success');
				}
			},
			[
				{ name: 'Server_id' },
				//{ name: 'LpuSectionBedState_Plan' },
				//{ name: 'LpuSectionBedState_Repair' },
				{ name: 'LpuSectionWard_id' },
				{ name: 'LpuSection_id' },
				{ name: 'LpuSectionWard_Name' },
				{ name: 'LpuWardType_id' },
				{ name: 'LpuSectionWard_Floor' },
				{ name: 'Sex_id' },
				{ name: 'DChamberComfort_id' },
				{ name: 'LpuSectionWardComfortLink_id' },
				{ name: 'DChamberComfort_Name' },
				{ name: 'LpuSectionWardComfortLink_Count' },
				{ name: 'LpuSectionWard_BedCount' },
				{ name: 'LpuSectionWard_CountRoom' },
				{ name: 'LpuSectionWard_DopPlace' },
                { name: 'LpuSectionWard_MainPlace'},
				{ name: 'LpuSectionWard_Views' },
				{ name: 'LpuSectionWard_Square' },
				{ name: 'LpuSectionWard_BedRepair' },
				{ name: 'LpuSectionWard_DayCost' },
				{ name: 'LpuSectionWard_setDate' },
				{ name: 'LpuSectionWard_disDate' }
			]
			),
			url: C_LPUSECTIONWARD_SAVE//'/?c=LpuStructure&m=SaveLpuSectionWard';
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			border: false,
			bodyStyle: 'overflow: auto;',
			items: [this.MainPanel]
		});
		sw.Promed.swLpuSectionWardEditForm.superclass.initComponent.apply(this, arguments);
	},
	buttons:[{
		text: BTN_FRMSAVE,
		id: 'lswef_Ok',
		tabIndex: 122009,
		iconCls: 'save16',
		handler: function() {
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	}, 
	{
		text: BTN_FRMCANCEL,
		id: 'lswef_Cancel',
		tabIndex: 122010,
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
		}
	}],
	listeners:{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	}
});