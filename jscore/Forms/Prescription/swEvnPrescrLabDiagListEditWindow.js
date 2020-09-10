/**
* swEvnPrescrLabDiagListEditWindow - окно добавления назначений c типом Лабораторная диагностика.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      08.2013
* @comment      Префикс для id компонентов EPRLDEF (EvnPrescrLabDiagListEditForm)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrLabDiagListEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swEvnPrescrLabDiagListEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrLabDiagListEditWindow.js',

	action: null,
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	autoHeight: true,
	width: 800,
    closable: true,
    //maximizable: true,
	closeAction: 'hide',
	split: true,
	layout: 'form',
	id: 'EvnPrescrLabDiagListEditWindow',
	modal: true,
	plain: true,
	resizable: false,
	listeners: 
	{
		hide: function(win) 
		{
            win.onCancel();
            win.onHide();
		}
    },
    onCancel: function()
    {
		// если 19465 == ревизия формы /jscore/Forms/Reg/swTTMSScheduleRecordWindow.js,
		// в которой при выборе времени блокируется бирка
		if (true) {
			var store = this.uslugaFrame.getGrid().getStore();
			store.each(function(rec){
				if (rec.get('TimetableMedService_id') > 0) {
					Ext.Ajax.request({
						url: '/?c=TimetableMedService&m=unlock',
						params: {
							TimetableMedService_id: rec.get('TimetableMedService_id')
						},
						callback: function() {
							//
						}
					});
				}
				return true;
			}, this);
		}
    },
    validate: function(rec)
    {
        if (rec.get('ttms_MedService_id') && !rec.get('TimetableMedService_id')) {
            sw.swMsg.alert(lang['oshibka'], lang['sohranenie_nevozmojno_dlya_odnoy_ili_neskolkih_uslug_ne_vyibrano_vremya_priema_vyiberite_vremya_zapisi_na_priem']);
            return false;
        }
        if (rec.get('isComposite') == 1 && !rec.compositionMenu) {
            sw.swMsg.alert(lang['oshibka'], lang['sohranenie_nevozmojno_dlya_odnoy_ili_neskolkih_uslug_ne_vyibran_sostav_vyiberite_sostav_uslugi']);
            return false;
        }
        return true;
	},
    title: lang['naznachenie_laboratornoy_diagnostiki_dobavlenie'],
	doSave: function()
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
        var thas = this;
        var store = this.uslugaFrame.getGrid().getStore();
        var records = [];
        var hasError = false;
        store.each(function(rec){
            if (false == hasError && rec.get('UslugaComplexMedService_IsSelected') == 1) {
                if (this.validate(rec)) {
                    records.push(rec);
                    return true;
                } else {
                    hasError = true;
                    return false;
                }
            }
            return true;
        }, this);

        if (false == hasError && records.length == 0 ) {
            sw.swMsg.alert(lang['oshibka'], lang['sohranenie_nevozmojno_ne_vyibrano_ni_odnoy_uslugi_vyiberite_uslugi']);
            hasError = true;
        }

        var base_form = this.FormPanel.getForm();
        if ( false == hasError && !base_form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    thas.FormPanel.getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            hasError = true;
        }

        if ( hasError ) {
            return false;
        }

        //Для каждой услуги создаем назначение и направление (в очередь или запись на бирку)
        this.formStatus = 'save';
        var evnPrescrData = base_form.getValues();
        var direction = {
            LpuUnitType_SysNick: 'parka'
            ,PrehospDirect_id: 1
            ,PrescriptionType_Code: '11'
            ,EvnDirection_pid: evnPrescrData.EvnPrescrLabDiag_pid
            ,Diag_id: this.Diag_id || null
            ,MedPersonal_id: this.userMedStaffFact.MedPersonal_id
            ,Lpu_id: this.userMedStaffFact.Lpu_id
            ,LpuSection_id: this.userMedStaffFact.LpuSection_id
            ,UslugaComplex_id: null
            ,LpuSection_Name: null
            ,LpuSection_uid: null
            ,LpuSectionProfile_id: null
            ,EvnPrescr_id: null
            ,MedService_id: null
            ,MedService_Nick: null
            ,MedServiceType_SysNick: null
            ,Lpu_did: null
        };
        var params = {
            person: {
                Person_id: evnPrescrData.Person_id
                ,PersonEvn_id: evnPrescrData.PersonEvn_id
                ,Server_id: evnPrescrData.Server_id
            },
            needDirection: false,
            mode: 'nosave',
            //loadMask: false,
            windowId: 'EvnPrescrLabDiagListEditWindow'
        };

        for (var i=0; i < records.length; i++) {
            this.getLoadMask(LOAD_WAIT_SAVE).show();
            var checked = [];
            if (records[i].compositionMenu) {
                records[i].compositionMenu.items.each(function(item){
                    if (item.checked) {
                        checked.push(item.UslugaComplex_id);
                    }
                });
            }
            var uslugaData = records[i].data;
            uslugaData.checked = checked;
            uslugaData.i = i;
            evnPrescrData.UslugaComplex_id = uslugaData.UslugaComplex_id;
            evnPrescrData.parentEvnClass_SysNick = thas.parentEvnClass_SysNick;
            evnPrescrData.EvnPrescrLabDiag_uslugaList = checked.toString();
            Ext.Ajax.request({
                url: '/?c=EvnPrescr&m=saveEvnPrescrLabDiag',
                params: evnPrescrData,
                uslugaData: uslugaData,
                callback: function(o, s, r) {
                    thas.getLoadMask().hide();
                    if(s) {
                        var response_obj = Ext.util.JSON.decode(r.responseText);
                        if ( response_obj.success && response_obj.success === true) {
                            direction.EvnPrescr_id = response_obj.EvnPrescrLabDiag_id;
                            direction.UslugaComplex_id = o.uslugaData.UslugaComplex_id;
                            direction.LpuSection_Name = o.uslugaData.LpuSection_Name;
                            direction.LpuSection_did = o.uslugaData.LpuSection_id;
                            direction.LpuSection_uid = o.uslugaData.LpuSection_id;
                            direction.Lpu_did = o.uslugaData.Lpu_id;
                            direction.LpuUnit_did = o.uslugaData.LpuUnit_id;
                            direction.LpuSectionProfile_id = o.uslugaData.LpuSectionProfile_id;
                            direction.MedService_did = o.uslugaData.MedService_id;
                            direction.MedService_id = o.uslugaData.MedService_id;
                            direction.MedService_Nick = o.uslugaData.MedService_Nick;
                            direction.MedServiceType_SysNick = o.uslugaData.MedServiceType_SysNick;
                            direction.From_MedStaffFact_id = thas.userMedStaffFact.MedStaffFact_id;
                            params.direction = direction;
                            params.order = {
                                LpuSectionProfile_id: o.uslugaData.LpuSectionProfile_id
                                ,UslugaComplex_id: o.uslugaData.UslugaComplex_id
                                ,checked: Ext.util.JSON.encode(o.uslugaData.checked)
                                ,Usluga_isCito: ('on' == evnPrescrData.EvnPrescrLabDiag_IsCito)?2:1
                                ,UslugaComplex_Name: o.uslugaData.UslugaComplex_Name
                                ,UslugaComplexMedService_id: o.uslugaData.UslugaComplexMedService_id
                                ,MedService_id: o.uslugaData.MedService_id
                                ,MedService_pzNick: o.uslugaData.pzm_MedService_Name
                                ,MedService_pzid: o.uslugaData.pzm_MedService_id
                            };
                            if ((1+o.uslugaData.i) == records.length) {
                                params.callback = function(){
                                    thas.formStatus = 'edit';
                                    thas.hide();
                                    thas.callback();
                                };
                            }
                            if (o.uslugaData.TimetableMedService_id > 0) {
                                params.Timetable_id = o.uslugaData.TimetableMedService_id;
                                params.order.TimetableMedService_id = o.uslugaData.TimetableMedService_id;
                                sw.Promed.Direction.recordPerson(params);
                            } else {
                                sw.Promed.Direction.queuePerson(params);
                            }
                        } else {
                            thas.formStatus = 'edit';
                            thas.hide();
                            thas.callback();
                        }
                    }
                }
            });
        }
        return true;
    },
    showComposition: function(UslugaComplexMedService_id)
    {
        var rec = this.uslugaFrame.getGrid().getStore().getById(UslugaComplexMedService_id);
        if (!rec) {
            return false;
        }
        if (rec.get('UslugaComplexMedService_IsSelected') != 1) {
            rec.set('UslugaComplexMedService_IsSelected', 1);
            rec.commit();
            this.onCheckedUslugaComplexMedService(true, rec);
        }
        if (rec.compositionMenu) {
            rec.compositionMenu.show(Ext.get('composition_'+ UslugaComplexMedService_id),'tr');
            return true;
        }
        return true;
	},
    doApply: function(UslugaComplexMedService_id)
    {
        var store = this.uslugaFrame.getGrid().getStore();
        var rec = store.getById(UslugaComplexMedService_id);
        if (!rec) {
            return false;
        }
        if (rec.get('UslugaComplexMedService_IsSelected') != 1) {
            rec.set('UslugaComplexMedService_IsSelected', 1);
            rec.commit();
            this.onCheckedUslugaComplexMedService(true, rec);
        }

        var ms_data = {
            MedService_id: rec.data.MedService_id,
            MedServiceType_id: rec.data.MedServiceType_id,
            MedService_Nick: rec.data.MedService_Nick,
            MedService_Name: rec.data.MedService_Name,
            MedServiceType_SysNick: rec.data.MedServiceType_SysNick
        };

        if (rec.data.pzm_MedService_id && rec.data.pzm_MedService_id == rec.data.ttms_MedService_id) {
            //будем записывать в пункт забора
            ms_data.MedService_id = rec.data.pzm_MedService_id;
            ms_data.MedServiceType_id = rec.data.pzm_MedServiceType_id;
            ms_data.MedServiceType_SysNick = rec.data.pzm_MedServiceType_SysNick;
            ms_data.MedService_Nick = rec.data.pzm_MedService_Nick;
            ms_data.MedService_Name = rec.data.pzm_MedService_Name;
        }

        getWnd('swTTMSScheduleRecordWindow').show({
            disableRecord: true,
            MedService_id: ms_data.MedService_id,
            MedServiceType_id: ms_data.MedServiceType_id,
            MedService_Nick: ms_data.MedService_Nick,
            MedService_Name: ms_data.MedService_Name,
            MedServiceType_SysNick: ms_data.MedServiceType_SysNick,
            Lpu_did: rec.data.Lpu_id,
            callback: function(ttms){
                if (ttms.TimetableMedService_id > 0) {
                    rec.set('TimetableMedService_id', ttms.TimetableMedService_id);
                    rec.set('TimetableMedService_begTime', ttms.TimetableMedService_begTime);
                    rec.commit();
                }
                getWnd('swTTMSScheduleRecordWindow').hide();
            },
            userClearTimeMS: function() {
                /*this.getLoadMask(lang['osvobojdenie_zapisi']).show();
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
                 });*/
            }
        });

        return true;
    },
    changePzm: function(UslugaComplexMedService_id)
    {
        var store = this.uslugaFrame.getGrid().getStore();
        var rec = store.getById(UslugaComplexMedService_id);
        if (!rec) {
            return false;
        }
        if (rec.get('UslugaComplexMedService_IsSelected') != 1) {
            rec.set('UslugaComplexMedService_IsSelected', 1);
            rec.commit();
            this.onCheckedUslugaComplexMedService(true, rec);
        }
        var pzmLink = Ext.get('select_pzm_link_'+ UslugaComplexMedService_id);
        var pzmCombo = new sw.Promed.SwMedServiceCombo({
            rec: rec,
            width: 180,
            hideLabel: true,
            hiddenName: 'MedService_pzid',
            params:{
                Lpu_id: getGlobalOptions().lpu_id,
                MedServiceType_SysNick: 'pzm',
                MedService_lid: rec.get('MedService_id')
            },
            renderTo: 'select_pzm_input_'+ UslugaComplexMedService_id
        });
        pzmCombo.on('select', function(cmp, pzm){
            cmp.rec.set('pzm_MedService_id', pzm.get('MedService_id'));
            cmp.rec.set('pzm_MedServiceType_id', 7);
            cmp.rec.set('pzm_MedServiceType_SysNick', 'pzm');
            cmp.rec.set('pzm_MedService_Nick', pzm.get('MedService_Name'));
            cmp.rec.set('pzm_MedService_Name', pzm.get('MedService_Name'));
            cmp.rec.commit();
        });
        pzmCombo.on('blur', function(cmp){
            cmp.destroy();
            pzmLink.setDisplayed('block');
        });
        pzmLink.setDisplayed('none');
        pzmCombo.getStore().load({
            callback: function(){
                pzmCombo.setValue(pzmCombo.rec.get('pzm_MedService_id'));
                pzmCombo.focus(true, 500);
            }
        });

        return true;
    },
    onCheckedUslugaComplexMedService: function(checked, rec)
    {
        if (!checked) {
            return true;
        }
        if (!rec) {
            return false;
        }
        if (1 != rec.get('isComposite')) {
            return true;
        }
        if (rec.compositionMenu) {
            return true;
        }
        var thas = this;
        this.getLoadMask(LOAD_WAIT).show();
        Ext.Ajax.request({
            params: {UslugaComplexMedService_pid: rec.get('UslugaComplexMedService_id')},
            callback: function(options, success, response) {
                thas.getLoadMask().hide();
                if ( success ) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (Ext.isArray(response_obj) && response_obj.length > 0) {
                        rec.compositionMenu = new Ext.menu.Menu();
						rec.compositionMenu.addListener('show', function(m) { swSetMaxMenuHeight(m, 300); });
                        for (var i=0; i < response_obj.length; i++) {
                            rec.compositionMenu.add(new Ext.menu.CheckItem({
                                id: response_obj[i].UslugaComplexComposition_id,
                                text: response_obj[i].UslugaComplex_Code +' '+ response_obj[i].UslugaComplex_Name,
                                UslugaComplex_id: response_obj[i].UslugaComplex_id,
                                rec: rec,
                                checked: true,
                                hideOnClick: false,
                                iconCls: '',
                                handler: function(item) {
                                    var cnt_checked = item.rec.get('compositionCntChecked');
                                    if (item.checked) {
                                        cnt_checked = cnt_checked - 1;
                                    } else {
                                        cnt_checked = cnt_checked + 1;
                                    }
                                    item.rec.set('compositionCntChecked', cnt_checked);
                                    item.rec.commit();
                                }
                            }));
                        }
                        rec.set('compositionCntChecked', response_obj.length);
                        rec.set('compositionCntAll', response_obj.length);
                        rec.commit();
                    }
                }
            },
            url: '/?c=UslugaComplex&m=getUslugaComplexMedServiceCompositionList'
        });
        return true;
    },
	
	show: function() 
	{
		sw.Promed.swEvnPrescrLabDiagListEditWindow.superclass.show.apply(this, arguments);
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

        this.userMedStaffFact = null;
        this.parentEvnClass_SysNick = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.Diag_id = null;
		
		if ( !arguments[0] || typeof arguments[0].formParams != 'object' || typeof arguments[0].userMedStaffFact != 'object' ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);
        this.userMedStaffFact = arguments[0].userMedStaffFact;

        if ( typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
            this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
        }

		if ( typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].Diag_id ) {
			this.Diag_id = arguments[0].Diag_id;
		}


        this.uslugaFrame.removeAll();
        var baseParams = {
            uslugaCategoryList: Ext.util.JSON.encode(['lpu']),
            allowedUslugaComplexAttributeList: Ext.util.JSON.encode(['lab']),
            LpuSection_id: this.userMedStaffFact.LpuSection_id
        };
        this.uslugaFrame.loadData({
            globalFilters: baseParams
        });
        base_form.findField('EvnPrescrLabDiag_setDate').focus(true, 250);
        return true;
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;

        this.uslugaFrame = new sw.Promed.ViewFrame({
            id: 'EvnPrescrLabDiagListEditViewFrame',
            title: lang['vyibor_uslug_dlya_naznacheniya_i_napravleniya'],
            actions: [
                {name:'action_add', hidden: true, disabled: true},
                {name:'action_edit', hidden: true, disabled: true},
                {name:'action_view', hidden: true, disabled: true},
                {name:'action_delete', hidden: true, disabled: true},
                {name:'action_refresh', hidden: true, disabled: true},
                {name:'action_print', hidden: true, disabled: true},
                {name:'action_resetfilter', hidden: true, disabled: true},
                {name:'action_save', hidden: true, disabled: true}
            ],
            stringfields: [
                {name: 'UslugaComplexMedService_id', type: 'int', header: 'ID', key: true},
                {name: 'MedService_id', type: 'int', hidden: true},
                {name: 'UslugaComplex_id', type: 'int', hidden: true},
                {name: 'LpuUnit_id', type: 'int', hidden: true, isparams: true},
                {name: 'Lpu_id', type: 'int', hidden: true, isparams: true},
                {name: 'LpuBuilding_id', type: 'int', hidden: true},
                {name: 'LpuSection_id', type: 'int', hidden: true},
                {name: 'LpuUnitType_id', type: 'int', hidden: true},
                {name: 'LpuSectionProfile_id', type: 'int', hidden: true},
                {name: 'MedServiceType_id', type: 'int', hidden: true},
                {name: 'LpuUnitType_SysNick', type: 'string', hidden: true},
                {name: 'MedService_Nick', type: 'string', hidden: true},
                {name: 'MedServiceType_SysNick', type: 'string', hidden: true},
                {name: 'isComposite', type: 'int', hidden: true},
                {name: 'ttms_MedService_id', type: 'int', hidden: true},
                {name: 'pzm_MedService_id', type: 'int', hidden: true},
                {name: 'pzm_MedServiceType_id', type: 'int', hidden: true},
                {name: 'pzm_MedService_Nick', type: 'string', hidden: true},
                {name: 'pzm_MedService_Name', type: 'string', hidden: true},
                {name: 'pzm_MedServiceType_SysNick', type: 'string', hidden: true},
                { name: 'UslugaComplexMedService_IsSelected', header: lang['otmetka'], type: 'checkcolumnedit', width: 65 },
                { name: 'MedService_Name', type: 'string', header: lang['slujba'], width: 120 },
                { name: 'UslugaComplex_Name', type: 'string', header: lang['usluga'], autoexpand: true, autoExpandMin: 150 },
                {name: 'compositionCntAll', type: 'int', hidden: true},
                {name: 'compositionCntChecked', type: 'int', hidden: true},
                { name: 'composition', header: lang['sostav'], width: 90, renderer: function(value, cellEl, rec){
                    if (1 == rec.get('isComposite')) {
                        var text = lang['izmenit'];
                        if (rec.get('compositionCntAll') > 0) {
                            text += ' ('+rec.get('compositionCntChecked')+'/'+rec.get('compositionCntAll')+')';
                        }
                        return '<a href="#" ' +
                            'id="composition_'+ rec.get('UslugaComplexMedService_id') +'" '+
                            'onclick="Ext.getCmp(\'EvnPrescrLabDiagListEditWindow\').showComposition('+
                            "'"+ rec.get('UslugaComplexMedService_id') +"'"+
                            ')">'+ text +'</a>';
                    }
                    return '';
                } },
                {name: 'TimetableMedService_id', type: 'int', hidden: true},
                {name: 'TimetableMedService_begTime', type: 'string', hidden: true},
                { name: 'timetable', header: lang['raspisanie'], width: 100, renderer: function(value, cellEl, rec){
                    var text = lang['vyibrat_vremya'];
                    if (rec.get('TimetableMedService_begTime')) text = rec.get('TimetableMedService_begTime');
                    if (rec.get('ttms_MedService_id')) {
                        return '<a href="#" ' +
                            'id="apply_link_'+ rec.get('UslugaComplexMedService_id') +'" '+
                            'onclick="Ext.getCmp(\'EvnPrescrLabDiagListEditWindow\').doApply('+
                            "'"+ rec.get('UslugaComplexMedService_id') +"'"+
                            ')">'+ text +'</a>';
                    }
                    return '';
                } },
                { name: 'pz', header: lang['punkt_zabora'], width: 210, renderer: function(value, cellEl, rec){
                    if (rec.get('pzm_MedService_id') > 0) {
                        return '<div id="select_pzm_link_'+ rec.get('UslugaComplexMedService_id') +'"><a href="#" ' +
                            'onclick="Ext.getCmp(\'EvnPrescrLabDiagListEditWindow\').changePzm('+
                            "'"+ rec.get('UslugaComplexMedService_id') +"'"+
                            ')">'+ rec.get('pzm_MedService_Name') +'</a></div><div id="select_pzm_input_'+ rec.get('UslugaComplexMedService_id') +'"></div>';
                    }
                    return '';
                } }
            ],
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MedService&m=getUslugaComplexMedServiceList',
            object: 'UslugaComplexMedService',
            layout: 'fit',
            height: 300,
            //root: 'data',
            //totalProperty: 'totalCount',
            paging: false,
            //region: 'center',
            toolbar: false,
            editing: true,
            onAfterEditSelf: function(o) {
                o.record.commit();
                if ('UslugaComplexMedService_IsSelected' == o.field) {
                    form.onCheckedUslugaComplexMedService(o.value, o.record);
                }
            },
            onLoadData: function() {
                //this.getGrid().getStore()
            },
            onDblClick: function() {
                //
            },
            onEnter: function() {
                //
            }
        });

		this.FormPanel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'EvnPrescrLabDiagListEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			region: 'center',
			items: 
			[{
				name: 'EvnPrescrLabDiag_pid',
				value: null,
				xtype: 'hidden'
            },
            {
                name: 'Person_id',
                value: null,
                xtype: 'hidden'
			}, 
			{
				name: 'PersonEvn_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'Server_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				allowBlank: false,
				fieldLabel: lang['planovaya_data'],
				format: 'd.m.Y',
				name: 'EvnPrescrLabDiag_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				xtype: 'swdatefield'
			}, 
			this.uslugaFrame 
			,{
				boxLabel: 'Cito',
				checked: false,
				fieldLabel: '',
				labelSeparator: '',
				name: 'EvnPrescrLabDiag_IsCito',
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnPrescrLabDiag_Descr',
				width: 390,
				xtype: 'textarea'
			}],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{name: 'EvnPrescrLabDiag_pid'},
                {name: 'Person_id'},
                {name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'EvnPrescrLabDiag_setDate'},
				{name: 'EvnPrescrLabDiag_IsCito'},
				{name: 'EvnPrescrLabDiag_Descr'}
			]),
			timeout: 600,
			url: '/?c=EvnPrescr&m=saveEvnPrescrLabDiag'
		});
		
		Ext.apply(this, 
		{
			buttons: [{
				handler: function() {
                    form.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			//HelpButton(this, -1),
			{
				handler: function() {
                    form.hide();
				},
				onTabAction: function () {
					form.FormPanel.getForm().findField('EvnPrescrLabDiag_setDate').focus(true, 250);
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});
		sw.Promed.swEvnPrescrLabDiagListEditWindow.superclass.initComponent.apply(this, arguments);
	}
});