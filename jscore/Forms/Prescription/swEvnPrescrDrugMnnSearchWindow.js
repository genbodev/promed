/**
 * swEvnPrescrDrugMnnSearchWindow - окно поиска медикаментов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Prescription
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Kirill Sabirov (ksabirov@swan.perm.ru)
 * @version      04.10.2013
 */

sw.Promed.swEvnPrescrDrugMnnSearchWindow = Ext.extend(sw.Promed.BaseForm, {
    buttonAlign: 'left',
    closeAction : 'hide',
    doReset: function() {
        this.viewFrame.removeAll({clearAll:true});
        this.searchForm.getForm().reset();
        this.searchForm.getForm().findField('DrugComplexMnn_Name').focus(true, 250);
    },
    doSearch: function(isIgnoreQuery) {
        var bf = this.searchForm.getForm();
        var params = bf.getValues();
        params.query = bf.findField('DrugComplexMnn_Name').getValue();
		if(!params.query&&bf.findField('isFromDocumentUcOst').checked){
			params.query='%';
		}
        if ( !isIgnoreQuery && !params.query ) {
            sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { bf.findField('DrugComplexMnn_Name').focus(true, 250); });
            return false;
        }
        params.limit = this.viewFrame.pageSize;
        params.start = 0;
        this.viewFrame.removeAll({clearAll:true});
        this.viewFrame.loadData({globalFilters: params});
        return true;
    },
    draggable: true,
    height: 500,
    id: 'EvnPrescrDrugMnnSearchWindow',
    initComponent: function() {
        var thas = this;
        this.searchForm = new Ext.form.FormPanel({
            autoHeight: true,
            border: false,
            buttonAlign: 'left',
            frame: true,
            items: [{
                anchor: '100%',
                enableKeyEvents: true,
                fieldLabel: lang['naimenovanie'],
                id: 'EPDMSW_Drug_Name',
                listeners: {
                    keydown: function(inp, e) {
                        if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
                            e.stopEvent();
                            thas.buttons[thas.buttons.length - 1].focus();
                        }
                    }
                },
                name: 'DrugComplexMnn_Name',
                xtype: 'textfield'
            },{
                name: 'LpuSection_id',
                xtype: 'hidden'
            },{
                name: 'Storage_id',
                xtype: 'hidden'
            },{
                name: 'UserLpuSection_id',
                xtype: 'hidden'
            },{
                name: 'isFromCentralStorageOst',
                xtype: 'hidden'
            },{
                boxLabel: langs('Только на остатках отделения'),
                checked: false,
                fieldLabel: '',
                labelSeparator: '',
                name: 'isFromDocumentUcOst',
                xtype: 'checkbox'
            }],
            keys: [{
                fn: function() {
                    thas.doSearch();
                },
                key: Ext.EventObject.ENTER,
                stopEvent: true
            }],
            labelAlign: 'left',
            region: 'north',
            style: 'padding: 0px;'
        });

        this.viewFrame = new sw.Promed.ViewFrame({
            id: 'EPDMSW_EvnPrescrDrugMnnSearchGrid',
            object: 'RlsDrugComplexMnn',
            dataUrl: '/?c=RlsDrug&m=loadDrugComplexMnnListWithPaging',
            toolbar: false,
            border: false,
            stripeRows: true,
            region: 'center',
            height: 220,
            collapsible: false,
            autoScroll: true,
            autoLoadData: false,
            stringfields:[
                {name: 'DrugComplexMnn_id', type: 'int', hidden: true, key: true},
                {name: 'RlsClsdrugforms_id', type: 'int', hidden: true},
                {name: 'RlsActmatters_id', type: 'int', hidden: true},
                {name: 'RlsClsdrugforms_Name', type: 'string', hidden: true},
                {name: 'RlsClsdrugforms_RusName', type: 'string', hidden: true},
                {name: 'DrugComplexMnn_Dose', type: 'string', hidden: true},
                {name: 'DrugComplexMnnDose_Mass', type: 'string', hidden: true},
                {name: 'DrugComplexMnn_Name',  type: 'string', header: lang['naimenovanie'], autoexpand: true, sortable: true, autoExpandMin: 200},
				{name: 'Ostat_Kolvo', header: 'Остаток<br>с учетом<br>назначения', type: 'string', align: 'right', hidden: getGlobalOptions().region.nick != 'ufa'},
				{name: 'DrugOstatRegistry_Kolvo', header: 'Остаток<br>на складе', type: 'string', align: 'right', hidden: getGlobalOptions().region.nick != 'ufa'},
				{name: 'EvnCourseTreatDrug_Count', header: 'Назначено', type: 'string', align: 'right', hidden: getGlobalOptions().region.nick != 'ufa'}
            ],
            actions: [
                {name:'action_add', hidden: true, disabled: true},
                {name:'action_edit', hidden: true, disabled: true},
                {name:'action_view', hidden: true, disabled: true},
                {name:'action_delete', hidden: true, disabled: true},
                {name:'action_refresh', hidden: true, disabled: true},
                {name:'action_print', hidden: true, disabled: true}
            ],
            onDblClick: function() {
                this.onEnter();
            },
            onEnter: function() {
                thas.onOkButtonClick();
            },
            paging: true,
            root: 'data',
            totalProperty: 'totalCount',
            pageSize: 100,
            focusOnFirstLoad: false
        });

        Ext.apply(this, {
            buttons: [{
                handler: function() {
                    thas.doSearch();
                },
                iconCls: 'search16',
                text: BTN_FRMSEARCH
            }, {
                handler: function() {
                    thas.doReset();
                },
                iconCls: 'resetsearch16',
                text: BTN_FRMRESET
            }, {
                handler: function() {
                    thas.onOkButtonClick();
                },
                iconCls: 'ok16',
                text: lang['vyibrat']
            }, {
                text: '-'
            },
                HelpButton(this),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    onTabElement: 'EPDMSW_Drug_Name',
                    text: BTN_FRMCANCEL
                }],
            items: [
                this.searchForm,
                this.viewFrame
            ]
        });
        sw.Promed.swEvnPrescrDrugMnnSearchWindow.superclass.initComponent.apply(this, arguments);
    },
    layout: 'border',
    listeners: {
        'hide': function() {
            this.onHide();
        }
    },
    modal: true,
    onDrugSelect: Ext.emptyFn,
    onHide: Ext.emptyFn,
    onOkButtonClick: function() {
        if ( !this.viewFrame.getGrid().getSelectionModel().getSelected() ) {
            this.hide();
            return false;
        }

        this.onDrugSelect(this.viewFrame.getGrid().getSelectionModel().getSelected().data);
        return true;
    },
    plain: true,
    resizable: false,
    show: function() {
        sw.Promed.swEvnPrescrDrugMnnSearchWindow.superclass.show.apply(this, arguments);

        this.onDrugSelect = Ext.emptyFn;
        this.onHide = Ext.emptyFn;

		var sf = this.searchForm.getForm();

        if ( !arguments[0] ) {
            this.hide();
            return false;
        }

        if ( arguments[0].onHide ) {
            this.onHide = arguments[0].onHide;
        }

        if ( arguments[0].onSelect ) {
            this.onDrugSelect = arguments[0].onSelect;
        }

		if ( arguments[0].hideIsFromDocumentUcOst ) {
			sf.findField('isFromDocumentUcOst').hide();
		} else {
			sf.findField('isFromDocumentUcOst').show();
		}

        this.doReset();

        if ( typeof arguments[0].formParams == 'object') {
            sf.setValues(arguments[0].formParams);
            if (sf.findField('isFromDocumentUcOst').getValue()) {
                this.doSearch(true);
            }
        }
        return true;
    },
    title: WND_SEARCH_DRUGMNN,
    width: 800
});