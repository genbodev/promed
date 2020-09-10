/**
 * swCommercialOfferViewWindow - cписок коммерческих предложений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Farmacy
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Salakhov R.
 * @version      06.2013
 * @comment
 */
sw.Promed.swCommercialOfferViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['spisok_kommercheskih_predlojeniy'],
	layout: 'border',
	id: 'CommercialOfferViewWindow',
	modal: false,
	shim: false,
	resizable: false,
	maximizable: false,
	maximized: true,
    changeYear: function(value) {
        var year_field = this.WindowToolbar.items.get(3);
        var val = year_field.getValue();
        if (!val || value == 0) {
            val = (new Date()).getFullYear();
        }
        year_field.setValue(val+value);
    },
    doSearch: function(default_values) {
        var year_field = this.WindowToolbar.items.get(3);

        if (default_values) {
            this.changeYear(0);
        }

        var params = new Object();
        params.Year = year_field.getValue();
        params.Org_did = getGlobalOptions().org_id;

        this.DataGrid.removeAll();
        this.DataGrid.loadData({
            globalFilters: params
        });
    },
    confirmAction: function (message, callback) {
        sw.swMsg.show({
            icon: Ext.MessageBox.QUESTION,
            msg: message,
            title: lang['podtverjdenie'],
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId, text, obj) {
                if ('yes' == buttonId) {
                    callback();
                }
            }
        });
    },
	show: function() {
		var wnd = this;
		sw.Promed.swCommercialOfferViewWindow.superclass.show.apply(this, arguments);

		wnd.doSearch(true);

        this.onlyView = false;

        if(arguments[0] && arguments[0].onlyView){
            this.onlyView = true;
        }

        if(!wnd.DataGrid.getAction('action_cov_delete_menu')) {
            wnd.DataGrid.addActions({
                name:'action_cov_delete_menu',
                text: lang['udalit'],
                menu: [{
                    name: 'action_cov_delete',
                    text: lang['udalit'],
                    tooltip: lang['udalit'],
                    handler: function() {
                        wnd.confirmAction(lang['vyi_hotite_udalit_zapis'], function() {wnd.DataGrid.deleteRecord();});
                    },
                    iconCls: 'delete16'
                }, {
                    name: 'action_cov_delete_all',
                    text: lang['udalit_vse'],
                    tooltip: lang['udalit_vse'],
                    handler: function() {
                        wnd.confirmAction(lang['vy_hotite_udalit_vse_zapisi'], function() {wnd.DataGrid.deleteAllRecords();});
                    },
                    iconCls: 'delete16'
                }],
                iconCls: 'delete16'
            }, 3);
        }

        this.DataGrid.setReadOnly(this.onlyView);
	},
	initComponent: function() {
		var wnd = this;

        this.WindowToolbar = new Ext.Toolbar({
            items: [{
                xtype: 'button',
                disabled: true,
                text: 'Год'
            }, {
                text: null,
                xtype: 'button',
                iconCls: 'arrow-previous16',
                handler: function() {
                    wnd.changeYear(-1);
                    wnd.doSearch();
                }.createDelegate(this)
            }, {
                xtype : "tbseparator"
            }, {
                xtype : 'numberfield',
                allowDecimal: false,
                allowNegtiv: false,
                width: 35,
                enableKeyEvents: true,
                listeners: {
                    'keydown': function (inp, e) {
                        if (e.getKey() == Ext.EventObject.ENTER) {
                            e.stopEvent();
                            wnd.doSearch();
                        }
                    }
                }
            }, {
                xtype : "tbseparator"
            }, {
                text: null,
                xtype: 'button',
                iconCls: 'arrow-next16',
                handler: function() {
                    wnd.changeYear(1);
                    wnd.doSearch();
                }.createDelegate(this)
            }, {
                xtype: 'tbfill'
            }]
        });

		this.DataGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', disabled: true, hidden: true},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			schema: 'dbo',
			obj_isEvn: false,
			border: true,
			dataUrl: '/?c=CommercialOffer&m=loadList',
			height: 180,
			region: 'center',
			object: 'CommercialOffer',
			editformclassname: 'swCommercialOfferEditWindow',
			id: wnd.id+'Grid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'CommercialOffer_id', type: 'int', header: 'ID', key: true},
				{name: 'CommercialOffer_begDT', type: 'date', header: lang['data'], width: 120},
                {name: 'CommercialOffer_Name', type: 'string', header: lang['naimenovanie'], width: 120},
				{name: 'Org_id_Name', type: 'string', header: lang['postavschik'], width: 120},
				{name: 'Org_id', type: 'int', hidden: true},
				{name: 'CommercialOffer_Status', header: lang['status'], renderer: function(v, p, r) {
                    var val = '';
                    if (v == '1') {
                        val =  'Действующий';
                    }
                    return val;
                }},
				{name: 'CommercialOffer_Comment', type: 'string', header: lang['primechanie'], id: 'autoexpand'}
			],
			title: false,
			toolbar: true,
            deleteRecord: function(){
                var selected_record = this.getGrid().getSelectionModel().getSelected();
                if (selected_record && selected_record.get('CommercialOffer_id') > 0) {
                    this.doDelete({
                        id: selected_record.get('CommercialOffer_id')
                    });
                }
            },
            deleteAllRecords: function(){
                var id_array = new Array();
                this.getGrid().getStore().each(function(record) {
                    if (record.get('CommercialOffer_id') > 0) {
                        id_array.push(record.get('CommercialOffer_id'));
                    }
                });
                this.doDelete({
                    id_list: id_array.join(',')
                });
            },
            doDelete: function(params) {
                var loadMask = new Ext.LoadMask(wnd.DataGrid.getEl(), {msg:lang['udalenie']});
                loadMask.show();

                //специфическая проверка для Казахстана: нельзя удалять прайсы СК Фармацея, если учетка не имеет доступа к АРМ Администратора ЦОД-а
                if (getRegionNick() == 'kz' && !haveArmType('superadmin')) {
                    params.check_list = "kz_org_ogrn";
                }

                Ext.Ajax.request({
                    url: '\?c=CommercialOffer&m=delete',
                    params: params,
                    failure: function(response, options) {
                        loadMask.hide();
                        Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
                    },
                    success: function(response, action) {
                        loadMask.hide();
                        if (response.responseText) {
                            var answer = Ext.util.JSON.decode(response.responseText);
                            if (answer.success) {
                                wnd.DataGrid.getGrid().getStore().reload();
                            }
                        } else {
                            Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_otsutstvuet_otvet_servera']);
                        }
                    }
                });
            }
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:[
				{
					text: '-'
				},
				HelpButton(this, 0),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
            tbar: this.WindowToolbar,
			items: [this.DataGrid]
		});
		sw.Promed.swCommercialOfferViewWindow.superclass.initComponent.apply(this, arguments);
	}
});