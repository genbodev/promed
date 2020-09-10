/**
* swEvnPrescrDrugStreamWindow - окно списания медикаментов при выполнении лекарственного лечения.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-27.11.2011
* @comment      Префикс для id компонентов EPRDSTF (EvnPrescrDrugStreamForm)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrDrugStreamWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPrescrDrugStreamWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrDrugStreamWindow.js',

	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
    draggable: true,
    height: 350,
    id: 'EvnPrescrDrugStreamWindow',
    keys: [{
        alt: true,
        fn: function(inp, e) {
            var current_window = Ext.getCmp('EvnPrescrDrugStreamWindow');

            switch ( e.getKey() ) {

                case Ext.EventObject.J:
                    current_window.hide();
                    break;
            }
        },
        key: [
            Ext.EventObject.J
        ],
        scope: this,
        stopEvent: false
    }],
    layout: 'border',
    listeners: {
        hide: function(win) {
            win.onHide();
            if (win.isChanged) {
                win.callback();
            }
        }
    },
    maximizable: false,
    maximized: false,
    modal: true,
    onHide: Ext.emptyFn,
    plain: true,
    resizable: false,
    title: lang['spisanie_medikamentov'],
    width: 800,
    /**
     * Отмена списания
     * @return {Boolean}
     */
	cancelEvnDrug: function() {
		var thas = this,
            grid = this.DrugGrid.getGrid();
        var rec = grid.getSelectionModel().getSelected();
		if ( !rec || !rec.get('EvnDrug_id') ) {
			return false;
		}
        var loadMask = this.getLoadMask(LOAD_WAIT);
        loadMask.show();
        Ext.Ajax.request({
            failure: function(response) {
                loadMask.hide();
                sw.swMsg.alert(lang['oshibka'], (response.status ? response.status.toString() + ' ' + response.statusText : lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']));
            }.createDelegate(this),
            params: {
                EvnDrug_id: rec.get('EvnDrug_id'),
                EvnPrescrTreat_Fact: thas.formPanel.getForm().findField('EvnPrescrTreat_Fact_Cancel').getValue()
            },
            success: function(response) {
                loadMask.hide();
                if (response.responseText)
                {
                    var answer = Ext.util.JSON.decode(response.responseText);
                    if (!answer.success) {
                        if (!answer.Error_Msg) { // если не автоматически выводится
                            Ext.Msg.alert(lang['oshibka'], lang['otmena_spisaniya_nevozmojna']);
                        }
                    } else {
                        thas.DrugGrid.setActionDisabled('action_refresh',true);
                        thas.DrugGrid.loadData({callback: function(){
                            thas.DrugGrid.setActionDisabled('action_refresh',false);
                            var sm = thas.DrugGrid.getGrid().getSelectionModel();
                            rec = thas.DrugGrid.getGrid().getStore().getById(rec.get('EvnPrescrTreatDrug_id'));
                            var idx = thas.DrugGrid.getGrid().getStore().indexOf(rec);
                            if (rec) {
                                thas.DrugGrid.onRowSelect(sm, idx, rec);
                            }
                        }});/*
                        rec.set('EvnDrug_id', null);
                        rec.set('EvnPrescrTreatDrug_FactCount', (answer.EvnPrescrTreatDrug_FactCount||0));
                        rec.commit();
                        var sm = thas.DrugGrid.getGrid().getSelectionModel();
                        var idx = thas.DrugGrid.getGrid().getStore().indexOf(rec);
                        thas.DrugGrid.onRowSelect(sm, idx, rec);*/
                        thas.isChanged = true;
                    }
                }
            },
            url: '/?c=EvnDrug&m=deleteEvnDrug'
        });
        return true;
	},
    /**
     * Открытие формы списания медикамента
     * @return {Boolean}
     */
    openEvnDrugEditWindow: function() {
		var wndName = getEvnDrugEditWindowName();
        if ( getWnd(wndName).isVisible() ) {
            sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_spisyivaemogo_medikamenta_uje_otkryito']);
            return false;
        }
        var thas = this;
        var grid = this.DrugGrid.getGrid();
        var record = grid.getSelectionModel().getSelected();
        if ( !record ) {
            return false;
        }
        var params = {};
        params.openMode = 'prescription';
        params.Person_id = record.data.Person_id;
        params.action = 'add';//(!record.get('EvnDrug_id'))?'add':'edit';
        params.formParams = record.data;
        var EvnPrescrTreatDrug_FactCount = record.data.EvnPrescrTreatDrug_FactCount||0;
        //Количество невыполненных приемов
        params.formParams.PrescrFactCountDiff = record.data.EvnPrescrTreat_PrescrCount - EvnPrescrTreatDrug_FactCount;
        params.formParams.EvnPrescrTreat_Fact = thas.formPanel.getForm().findField('EvnPrescrTreat_Fact').getValue();
        params.formParams = record.data;
        params.formParams.EvnDrug_id = null;
        params.callback = function(data){
            if ( typeof data != 'object' || typeof data.evnDrugData != 'object' ) {
                return false;
            }
            thas.DrugGrid.setActionDisabled('action_refresh',true);
            /*thas.DrugGrid.loadData({callback: function(){
                thas.DrugGrid.setActionDisabled('action_refresh',false);
                var sm = thas.DrugGrid.getGrid().getSelectionModel();
                record = thas.DrugGrid.getGrid().getStore().getById(record.get('EvnPrescrTreatDrug_id'));
                var idx = thas.DrugGrid.getGrid().getStore().indexOf(record);
                if (record) {
                    thas.DrugGrid.onRowSelect(sm, idx, record);
                }
            }});*/
            
            record.set('EvnDrug_id', data.evnDrugData.EvnDrug_id);
            record.set('Drug_Name', data.evnDrugData.Drug_Name);
            record.set('DrugPrepFas_id', data.evnDrugData.DrugPrepFas_id);
            record.set('Drug_id', data.evnDrugData.Drug_id);
            record.set('EvnDrug_Kolvo', data.evnDrugData.EvnDrug_Kolvo);
            record.set('EvnDrug_KolvoEd', data.evnDrugData.EvnDrug_KolvoEd);
            record.set('DocumentUcStr_oid', data.evnDrugData.DocumentUcStr_oid);
            record.set('Mol_id', data.evnDrugData.Mol_id);
            record.set('EvnDrug_setDate', data.evnDrugData.EvnDrug_setDate);
            record.set('EvnDrug_setTime', data.evnDrugData.EvnDrug_setTime);
            record.set('EvnPrescrTreatDrug_FactCount', data.evnDrugData.EvnPrescrTreatDrug_FactCount);
            //record.set('Mol_Name', data.evnDrugData.Mol_Name);
            //record.set('DocumentUcStr_Name', data.evnDrugData.DocumentUcStr_Name);
            record.commit();
             var sm = thas.DrugGrid.getGrid().getSelectionModel();
             var idx = thas.DrugGrid.getGrid().getStore().indexOf(record);
             thas.DrugGrid.onRowSelect(sm, idx, record);
            
            thas.isChanged = true;
            return true;
        };
        params.onHide = function() {
            grid.getView().focusRow(grid.getStore().indexOf(record));
        };
        params.parentEvnComboData = [];
        params.parentEvnComboData.push({
            Evn_id: record.get('EvnDrug_pid')
            ,Evn_Name: Ext.util.Format.date(record.get('Evn_setDate'), 'd.m.Y') + ' / ' + record.get('LpuSection_Name') + ' / ' + record.get('MedPersonal_FIO')
            ,Evn_setDate: record.get('Evn_setDate')
            ,Lpu_id: record.get('Lpu_id')
            ,LpuSection_id: record.get('LpuSection_id')
            ,MedPersonal_id: record.get('MedPersonal_id')
        });
        getWnd(wndName).show(params);
        return true;
    },
	initComponent: function() {
        var thas = this;

		this.DrugGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', text: lang['spisat'], tooltip: lang['spisat_medikament_s_ostatkov'], icon: 'img/icons/add16.png', handler: function() { thas.openEvnDrugEditWindow(); } },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', text: lang['otmenit'], tooltip: lang['otmenit_spisanie_medikamenta'], handler: function() { thas.cancelEvnDrug(); } },
				{ name: 'action_refresh' },
				{ name: 'action_print', disabled: true, hidden: true }
			],
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=EvnPrescr&m=loadEvnDrugGrid',
			id: 'EPRDSTF_DrugGrid',
			region: 'center',
			stringfields: [
                { name: 'EvnPrescrTreatDrug_id', type: 'int', header: 'ID', key: true },
                { name: 'EvnCourseTreatDrug_id', type: 'int', hidden: true },
                { name: 'EvnDrug_id', type: 'int', hidden: true },
                { name: 'EvnDrug_rid', type: 'int', hidden: true },
                { name: 'EvnDrug_pid', type: 'int', hidden: true },
                { name: 'EvnCourse_id', type: 'int', hidden: true },
                { name: 'EvnPrescr_id', type: 'int', hidden: true },
                { name: 'Person_id', type: 'int', hidden: true },
                { name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
                { name: 'DrugPrepFas_id', type: 'int', hidden: true },
                { name: 'Drug_Fas', type: 'int', hidden: true },
				{ name: 'Drug_id', type: 'int', hidden: true },
                { name: 'DrugComplexMnn_id', type: 'int', hidden: true },
                { name: 'EvnPrescrTreat_Descr', type: 'string', hidden: true },
                { name: 'Drug_Name', header: lang['medikament'], type: 'string', id: 'autoexpand' },
                { name: 'DocumentUcStr_Ost', header: lang['ostatok'], type: 'float', width: 70, hidden: true },
                { name: 'EvnDrug_setDate', type: 'date', format: 'd.m.Y', hidden: true },
                { name: 'EvnDrug_setTime', type: 'string', hidden: true },
                { name: 'EvnDrug_Kolvo', header: lang['kol-vo_up'], type: 'float', width: 70, hidden: true },
                { name: 'EvnDrug_KolvoEd', header: lang['kol-vo_doz'], type: 'float', width: 70, hidden: true },
                { name: 'EvnPrescrTreatDrug_DoseDay', header: lang['dnevnaya_doza'], type: 'string', width: 120 },
                { name: 'EvnPrescrTreatDrug_FactCount', header: lang['kol-vo_vyipoln_priemov'], type: 'int', width: 160 },
                { name: 'EvnPrescrTreat_PrescrCount', header: lang['kol-vo_priemov_v_sutki'], type: 'int', width: 160 },

                { name: 'Evn_setDate', type: 'date', format: 'd.m.Y', hidden: true },
                { name: 'MedPersonal_id', type: 'int', hidden: true },
                { name: 'MedPersonal_FIO', type: 'string', hidden: true },
                { name: 'Lpu_id', type: 'int', hidden: true },
                { name: 'LpuSection_id', type: 'int', hidden: true },
                { name: 'LpuSection_Name', header: lang['otdelenie'], type: 'string', width: 150, hidden: true },

				{ name: 'DocumentUcStr_oid', type: 'int', hidden: true },
				{ name: 'Mol_id', type: 'int', hidden: true },
				{ name: 'Mol_Name', header: lang['mol'], type: 'string', width: 200, hidden: true },
				{ name: 'DocumentUcStr_Name', header: lang['partiya'], type: 'string', width: 200, hidden: true },
				{ name: 'DrugFinance_Name', header: lang['istochnik_finansirovaniya'], type: 'string', width: 200, hidden: true },
				{ name: 'WhsDocumentCostItemType_Name', header: lang['statya_rashoda'], type: 'string', width: 200, hidden: true }
			],
			style: 'margin-bottom: 0.5em;',
			title: lang['medikamentyi'],
			toolbar: true,
            /*setEvnPrescrTreatFactCount: function(cnt) {
                this.getGrid().getStore().each(function(rec){
                    rec.set('EvnPrescrTreatDrug_FactCount', cnt||null);
                    rec.commit();
                    return true;
                });
            },*/
            onRowSelect: function(sm, rowIdx, record) {
                var bf = thas.formPanel.getForm();
                var num_field_add = bf.findField('EvnPrescrTreat_Fact');
                var num_field_cancel = bf.findField('EvnPrescrTreat_Fact_Cancel');
                var factCount = record.get('EvnPrescrTreatDrug_FactCount')||0;
                //списать - активна, если выбран медикамент в гриде и число выполненных приемов меньше назначенного
                var is_allow_add = (record.get('Drug_id')&&factCount < record.get('EvnPrescrTreat_PrescrCount'));
                //отменить - если выбран медикамент и колчиество приемов больше или равно 1.
                var is_allow_cancel = (record.get('EvnDrug_id')&&factCount >= 1);

                this.setActionDisabled('action_edit', !is_allow_add);
                this.setActionDisabled('action_delete', !is_allow_cancel);
                num_field_add.setDisabled(!is_allow_add);
                num_field_cancel.setDisabled(!is_allow_cancel);

                num_field_add.strategy.maxValue = record.get('EvnPrescrTreat_PrescrCount')-factCount;
                num_field_add.setValue(num_field_add.strategy.maxValue);
                num_field_cancel.strategy.maxValue = factCount;
                num_field_cancel.setValue(num_field_cancel.strategy.maxValue);
            }
		});

        this.formPanel = new Ext.form.FormPanel({
            autoHeight: true,
            layout: 'form',
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0',
            border: false,
            frame: true,
            id: 'EvnPrescrDrugStreamWindowForm',
            labelAlign: 'right',
            labelWidth: 180,
            region: 'south',
            items: [new Ext.ux.form.Spinner({
                xtype: 'numberfield',
                width: 70,
                strategy: new Ext.ux.form.Spinner.NumberStrategy({minValue:'1', maxValue: '1000', defaultValue: 1, decimalPrecision: 0}),
                listeners: {
                    'change': function(field, newValue) {
                        var v = field.strategy.fixBoundries(newValue);
                        field.setValue(v);
                        field.setRawValue(v);
                    }
                },
                fieldLabel: lang['spisat_priemov'],
                name: 'EvnPrescrTreat_Fact',
                value: 1
            }),new Ext.ux.form.Spinner({
                xtype: 'numberfield',
                width: 70,
                strategy: new Ext.ux.form.Spinner.NumberStrategy({minValue:'1', maxValue: '1000', defaultValue: 1, decimalPrecision: 0}),
                listeners: {
                    'change': function(field, newValue) {
                        var v = field.strategy.fixBoundries(newValue);
                        field.setValue(v);
                        field.setRawValue(v);
                    }
                },
                fieldLabel: lang['otmenit_priemov'],
                name: 'EvnPrescrTreat_Fact_Cancel',
                value: 1
            }),{
                fieldLabel: lang['kommentariy'],
                disabled: true,
                height: 70,
                name: 'EvnPrescrTreat_Descr',
                anchor: '96%',
                xtype: 'textarea'
            }]
        });

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					thas.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					//
				},
				onTabAction: function () {
					//
				},
				text: BTN_FRMCLOSE
			}],
			items: [
				this.DrugGrid,
                this.formPanel
			],
			layout: 'border'
		});

		sw.Promed.swEvnPrescrDrugStreamWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swEvnPrescrDrugStreamWindow.superclass.show.apply(this, arguments);
        var thas = this;
		this.center();

		this.callback = Ext.emptyFn;
        this.onHide = Ext.emptyFn;
        this.isChanged = false;

        this.DrugGrid.removeAll({addEmptyRecord: false});
        var grid = this.DrugGrid.getGrid();
        var bf = this.formPanel.getForm();

		this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick||'';
		if (this.parentEvnClass_SysNick.inlist(['EvnPS','EvnSection'])) {
			this.DrugGrid.setColumnHeader('EvnPrescrTreat_PrescrCount', lang['kol-vo_priemov_v_sutki']);
		} else {
			this.DrugGrid.setColumnHeader('EvnPrescrTreat_PrescrCount', lang['naznacheno_priemov']);
		}

        if ( arguments[0].DrugGridData && Ext.isArray(arguments[0].DrugGridData)) {
            grid.getStore().loadData(arguments[0].DrugGridData, true);
            if (arguments[0].DrugGridData.length > 0) {
                bf.findField('EvnPrescrTreat_Descr').setValue(arguments[0].DrugGridData[0]['EvnPrescrTreat_Descr']);
                grid.getStore().baseParams['EvnPrescrTreat_id'] = arguments[0].DrugGridData[0]['EvnPrescr_id'];
            }
        } else if ( arguments[0].EvnPrescrTreat_id ) {
            var loadMask = this.getLoadMask(LOAD_WAIT);
            loadMask.show();
            grid.getStore().baseParams['EvnPrescrTreat_id'] = arguments[0].EvnPrescrTreat_id;
            this.DrugGrid.loadData({
                globalFilters: {
                    EvnPrescrTreat_id: arguments[0].EvnPrescrTreat_id
                },
                callback: function(records) {
                    loadMask.hide();
                    if (records.length > 0) {
                        bf.findField('EvnPrescrTreat_Descr').setValue(records[0]['EvnPrescrTreat_Descr']);
                    }
                }
            });
        } else {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { thas.hide(); } );
            return false;
        }

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
        return true;
	}
});