/**
 * swDrugRMZSearchWindow - окно поиска кода РЗН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Salakhov R.
 * @version      04.04.2014
 */

sw.Promed.swDrugRMZSearchWindow = Ext.extend(sw.Promed.BaseForm, {
    buttonAlign: 'left',
    closeAction : 'hide',
    doReset: function() {
        this.GridPanel.getGrid().getStore().removeAll();
        this.SearchForm.getForm().reset();
        this.SearchForm.getForm().findField('Query').focus(true, 250);
    },
    doSearch: function() {
        var grid = this.GridPanel.getGrid();
        var Mask = new Ext.LoadMask(this.getEl(), { msg: SEARCH_WAIT });
        var bf = this.SearchForm.getForm();
        var params = bf.getValues();
        params.query = bf.findField('Query').getValue();
		params.Reg_Num = this.Reg_Num;
		params.Drug_Ean = this.Drug_Ean;
		params.DrugComplexMnnFas_Kol = this.DrugComplexMnnFas_Kol;
		params.no_rls = 1;

        if (!params.query && !params.Reg_Num && !params.Drug_Ean) {
            sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { bf.findField('Query').focus(true, 250); });
            return false;
        }

        grid.getStore().removeAll();
        Mask.show();

        grid.getStore().load({
            callback: function() {
                Mask.hide();
                if ( grid.getStore().getCount() > 0 ) {
                    grid.getView().focusRow(0);
                    grid.getSelectionModel().selectFirstRow();
                }
            },
            params: params
        });
        return true;
    },
    draggable: true,
	title: lang['spravochnik_lp_roszdravnadzora_poisk'],
	width: 1000,
    height: 300,
    id: 'DrugRMZSearchWindow',
    initComponent: function() {
        var wnd = this;
        this.SearchForm = new Ext.form.FormPanel({
            autoHeight: true,
            border: false,
            buttonAlign: 'left',
            frame: true,
            items: [{
                anchor: '100%',
                enableKeyEvents: true,
                fieldLabel: lang['naimenovanie'],
                listeners: {
                    keydown: function(inp, e) {
                        if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
                            e.stopEvent();
                            wnd.buttons[wnd.buttons.length - 1].focus();
                        }
                    }
                },
                name: 'Query',
                xtype: 'textfield'
            }],
            keys: [{
                fn: function() {
                    wnd.doSearch();
                },
                key: Ext.EventObject.ENTER,
                stopEvent: true
            }],
            labelAlign: 'left',
            region: 'north',
            style: 'padding: 0px;'
        });

		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'drsw_DrugRMZSearchGrid',
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			stringfields: [
				{name: 'DrugRMZ_id', header: 'ID', type: 'string', key: true},
				{name: 'DrugRPN_id', header: lang['kod_rzn'], type: 'string', width: 75},
				{name: 'DrugRMZ_RegNum', header: lang['№_ru'], type: 'string', width: 150},
				{name: 'DrugRMZ_EAN13Code', header: lang['kod_ean'], type: 'string', width: 75},
				{name: 'DrugRMZ_Name', header: lang['torgovoe_naimenovanie'], type: 'string', width: 75, id: 'autoexpand'},
				{name: 'DrugRMZ_Form', header: lang['forma_vyipuska'], type: 'string', width: 75},
				{name: 'DrugRMZ_Dose', header: lang['dozirovka'], type: 'string', width: 75},
				{name: 'DrugRMZ_Pack', header: lang['upakovka'], type: 'string', width: 75},
				{name: 'DrugRMZ_PackSize', header: lang['kol-vo_lek_form_v_upakovke'], type: 'string', width: 75},
				{name: 'DrugRMZ_Firm', header: lang['proizvoditel'], type: 'string', width: 75},
				{name: 'DrugRMZ_Country', header: lang['strana'], type: 'string', width: 75},
				{name: 'DrugRMZ_FirmPack', header: lang['upakovschik'], type: 'string', width: 175}
			],
			dataUrl: '/?c=DrugNomen&m=loadDrugRMZListByQuery',
			paging: false,
			toolbar: false,
			title: false,
			contextmenu: false
		});
        
        Ext.apply(this, {
            buttons: [{
                handler: function() {
                    this.doSearch();
                }.createDelegate(this),
                iconCls: 'search16',
                text: BTN_FRMSEARCH
            }, {
                handler: function() {
                    this.doReset();
                }.createDelegate(this),
                iconCls: 'resetsearch16',
                text: BTN_FRMRESET
            }, {
                handler: function() {
                    this.doSelect();
                }.createDelegate(this),
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
                    text: BTN_FRMCANCEL
                }],
            items: [
                this.SearchForm,
                this.GridPanel
            ]
        });
        sw.Promed.swDrugRMZSearchWindow.superclass.initComponent.apply(this, arguments);
    },
    layout: 'border',
    listeners: {
        'hide': function() {
            this.onHide();
        }
    },
    modal: true,
	maximized: false,
    onSelect: Ext.emptyFn,
    onHide: Ext.emptyFn,
    doSelect: function() {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();

        if (!record) {
            this.hide();
            return false;
        }

		if (record.get('DrugRMZ_id') > 0) {
			this.onSelect(record.data);
		}

        return true;
    },
    plain: true,
    resizable: false,
    show: function() {
        sw.Promed.swDrugRMZSearchWindow.superclass.show.apply(this, arguments);

        this.onSelect = Ext.emptyFn;
        this.onHide = Ext.emptyFn;
        this.Reg_Num = null;
        this.Drug_Ean = null;
        this.DrugComplexMnnFas_Kol = null;

		var sf = this.SearchForm.getForm();

        if ( !arguments[0] ) {
            this.hide();
            return false;
        }

        if ( arguments[0].onHide ) {
            this.onHide = arguments[0].onHide;
        }

		if ( arguments[0].onSelect ) {
			this.onSelect = arguments[0].onSelect;
		}

        if ( arguments[0].Reg_Num && arguments[0].Reg_Num != '' ) {
            this.Reg_Num = arguments[0].Reg_Num;
        }

        if ( arguments[0].Drug_Ean && arguments[0].Drug_Ean != '' ) {
            this.Drug_Ean = arguments[0].Drug_Ean;
        }

        if ( arguments[0].DrugComplexMnnFas_Kol && arguments[0].DrugComplexMnnFas_Kol != '' ) {
            this.DrugComplexMnnFas_Kol = arguments[0].DrugComplexMnnFas_Kol;
        }

        this.doReset();
		this.doSearch();

        return true;
    }
});