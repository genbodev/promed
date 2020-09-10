/**
* swRegistryExportWindow - окно настроек экспорта протоколов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Alexandr Chebukin
* @version      4.10.2012
*/

sw.Promed.swRegistryExportWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
    id: 'swRegistryExportWindow',
	objectName: 'swRegistryExportWindow',
	objectSrc: '/jscore/Forms/Common/swRegistryExportWindow.js',
	closable: false,
	width : 430,
	height : 400,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['vyigruzka_v_federalnyiy_registr'],
	params: null,
    exportMod: null,
	callback: Ext.emptyFn,
	mode: 'chbox',
    createXML: function(addParams)
    {
        var form = this;
        var ExportType = form.findById('rexmExportType').getValue();
        var ExportDate = form.findById('rexmDate').getValue();
        var VZNBegDate = Ext.util.Format.date(form.findById('VZNBegDate').getValue());
        var VZNEndDate = Ext.util.Format.date(form.findById('VZNEndDate').getValue());
        var base_form = form.findById('datePanel').getForm();

        if (!base_form.isValid()) {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }

        if ( VZNBegDate > VZNEndDate ) {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.WARNING,
                    msg: lang['data_okonchaniya_vyigruzki_ne_mojet_byit_ranshe_datyi_nachala'],
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }



        var params = {
			PersonRegisterType_SysNick: form.PersonRegisterType_SysNick,
			exportMod: form.exportMod,
            ExportType: ExportType,
            ExportDate: ExportDate,
            VZNBegDate: VZNBegDate,
            VZNEndDate: VZNEndDate,
            BegDate: VZNBegDate,
            EndDate: VZNEndDate
        };

        /*if (form.Panel.findById('rxw_radio_usenew').getValue()) {
            params.OverrideExportOneMoreOrUseExist = 2;
        }*/

        form.getLoadMask().show();
		form.LogGrid.clearLog();
		form.LogGrid.insertMessage(lang['start_vyigruzki']);

        Ext.Ajax.request({
            url: form.formUrl,
            params: params,
            timeout: 1800000,
            callback: function(options, success, response)
            {
                form.getLoadMask().hide();
                if (success)
                {

                    var result = Ext.util.JSON.decode(response.responseText);
                    var alt = '';
                    var msg = '';
                    form.refresh = true;
                    if (result.usePrevXml)
                    {
                        alt = lang['izmeneniy_s_reestrom_ne_byilo_proizvedeno_ispolzuetsya_sohranennyiy_xml_predyiduschey_vyigruzki'];
                        msg = lang['xml_predyiduschey_vyigruzki'];
                    }

					if (result.ExportErrorArray) {
						form.LogGrid.insertMessage(result.ExportErrorArray);
					}

                    if (result.Link) {
                        //form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить список</a>'+msg;
						form.LogGrid.insertMessage({Text: 'Окончание выгрузки.<br/><a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить список</a>'+msg});
                        //form.radioButtonGroup.hide();
                        //form.syncShadow();
                        //Ext.getCmp('rxfOk').disable();

                        /* // Формирование отчёта о прикрепленных возрастных категориях
                        var count_ppl= result.Doc.count_ppl;
                        var m04 = result.Doc.m04;
                        var w04 = result.Doc.w04;
                        var m517 = result.Doc.m517;
                        var w517 = result.Doc.w517;
                        var m1859 = result.Doc.m1859;
                        var w1854 = result.Doc.w1854;
                        var m60 = result.Doc.m60;
                        var w55 = result.Doc.w55;
                        var OrgSMO_id = result.Doc.OrgSMO_idd;

                        window.open('/?c=Registry&m=printAttachedList&m04=' + m04 + '&w04=' + w04 + '&m517=' + m517 + '&w517=' + w517 + '&m1859=' + m1859 + '&w1854=' +  w1854 + '&m60=' + m60 + '&w55=' + w55 + '&OrgSMO_id=' + OrgSMO_id + '&count_ppl=' + count_ppl);
                        */
                    }

                    if (result.success === false) {
                        //form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
						form.LogGrid.insertMessage(lang['oshibka']+result.Error_Msg);
                        //form.radioButtonGroup.hide();
                        //form.syncShadow();
                        //Ext.getCmp('rxfOk').disable();
                    }

                   // form.TextPanel.render();
                }
                else
                {
                    var result = Ext.util.JSON.decode(response.responseText);
                    //form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
                    //form.TextPanel.render();
					form.LogGrid.insertMessage(lang['oshibka']+result.Error_Msg);
                }
            }
        });
    },
	initComponent: function() {
		var current_window = this;

        this.TextPanel = new Ext.Panel(
            {
                autoHeight: true,
				style : 'padding: 10px',
                bodyBorder: false,
                border: false,
                id: 'RegistryXmlTextPanel',
                html: lang['vyigruzka_registra_po_orfannyim_zabolevaniyam_v_formate_xml']
            });

        this.DatePanel = new Ext.FormPanel ({
            autoHeight: true,
            id: 'datePanel',
            layout : 'form',
            border : false,
            frame : true,
            style : 'padding: 10px',
            labelWidth : 120,
            allowBlank: false,
            items : [
                {
                id: 'VZNBegDate',
                xtype: 'swdatefield',
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                format: 'd.m.Y',
                fieldLabel: lang['nachalo_perioda']
                },{
                id: 'VZNEndDate',
                xtype: 'swdatefield',
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                format: 'd.m.Y',
                fieldLabel: lang['konets_perioda']
                }
            ]
        });

		this.LogGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_refresh', hidden: true},
				{name: 'action_print', hidden: true}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			height: 180,
			id: 'reLogGrid',
			paging: false,
			stringfields: [
				{ name: 'LogMessage_id', type: 'int', header: 'ID', key: true },
				{ name: 'LogMessage_Time', type: 'string', header: lang['vremya'], width: 60 },
				{ name: 'LogMessage_Text', type: 'string', header: lang['soobschenie'], id: 'autoexpand' }
			],
			title: lang['log_vyigruzki'],
			toolbar: false,
			contextmenu: false,
			insertMessage: function(message_data) {
				var message_array = new Array();
				var current_date = new Date();
				var store = this.getGrid().getStore()
				var record = new Ext.data.Record.create(this.jsonData['store']);
				var record_count = store.getCount();

				if (message_data.Text) {
					message_array.push({
						LogMessage_Time: message_data.Time ? message_data.Time : null,
						LogMessage_Text: message_data.Text ? message_data.Text : null
					});
				} else if (Ext.isArray(message_data)) {
					for(var i = 0; i < message_data.length; i++) {
						message_array.push({
							LogMessage_Time: message_data[i].Time ? message_data[i].Time : null,
							LogMessage_Text: message_data[i].Text ? message_data[i].Text : null
						});
					}
				} else {
					message_array.push({
						LogMessage_Text: message_data
					});
				}
				for(var i = 0; i < message_array.length; i++) {
					message_array[i].LogMessage_id = record_count+i;
					if (!message_array[i].LogMessage_Time) {
						message_array[i].LogMessage_Time = current_date.format("H:i:s");
					}
					store.insert(record_count+i, new record(message_array[i]));
				}
				this.ViewGridPanel.getView().focusRow(record_count+(i-1));
			},
			clearLog: function() {
				this.getGrid().getStore().removeAll();
			}
		});

    	Ext.apply(this, {
			items : [
                this.TextPanel,
                this.DatePanel,
                new Ext.form.FormPanel({
				id : 'RegistryExportForm',
				autoHeight: true,
				layout : 'form',
				border : false,
				frame : true,
				style : 'padding: 10px',
				labelWidth : 1,
				items : [{
					style : 'padding-left: 5px',
					layout : 'form',
					labelWidth : 120,
					items: [{
						id: 'rexmDate',
						xtype: 'swdatefield',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						format: 'd.m.Y',
						fieldLabel: lang['data_vyigruzki'],
						allowBlank: false,
						disabled: true
					}, {
						id: 'rexmExportType',
						xtype:'combo',
						store: new Ext.data.SimpleStore({
							id: 0,
							fields: [
								'code',
								'name'
							],
							data: [
								['1', lang['vse_zapisi']],
								['2', lang['izmeneniya']]
							]
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{code}</font>&nbsp;{name}',
							'</div></tpl>'
						),
						displayField: 'name',
						valueField: 'code',
						allowBlank: false,
						mode: 'local',
						forceSelection: true,
						triggerAction: 'all',
						fieldLabel: lang['tip_vyigruzki'],
						width:  150,
						value: '1',
						selectOnFocus: true
                        }]
                    }]
                }
            ),
			this.LogGrid
			],
			buttons : [{
				text : lang['eksport'],
				iconCls : 'ok16',
				handler : function(button, event) {
					current_window.createXML();
				}
			}, {
				text: '-'
			}, {
				handler: function()
				{
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			buttonAlign : "right"
		});
    sw.Promed.swRegistryExportWindow.superclass.initComponent.apply(this, arguments);
	},
    show: function() {
        sw.Promed.swRegistryExportWindow.superclass.show.apply(this, arguments);
        var form = this;
		form.LogGrid.clearLog();
        form.TextPanel.reset;
        form.TextPanel.render();

        if (!arguments || !arguments[0]) {
            Ext.Msg.alert(lang['oshibka_otkryitiya_formyi'], lang['ne_ukazanyi_neobhodimyie_dannyie']);
            this.hide();
            return false;
        }
		this.PersonRegisterType_SysNick = arguments[0].PersonRegisterType_SysNick || null;
		this.formUrl = arguments[0].url || '/?c=MorbusOrphan&m=exportMorbusOrph';
		this.exportMod = arguments[0].exportMod || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		
		switch (this.PersonRegisterType_SysNick) {
			case 'nolos':
				form.formUrl = '/?c=PersonRegister&m=export';
				if (form.exportMod && form.exportMod == '06-FR') {
					form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_po_vzn_v_formate_xml_po_forme_06-fr'];
					form.findById('rexmExportType').setValue(1);
					form.findById('rexmExportType').disable();
				} else {
					form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_po_vzn_v_formate_xml'];
					form.findById('rexmExportType').setValue(2);
					form.findById('rexmExportType').enable();
				}
				break;
			default:
				form.findById('rexmExportType').setValue(1);
				if (form.exportMod && form.exportMod.inlist(['05-FR', '06-FR'])) {
					form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_po_vzn_v_formate_xml'];
					form.findById('rexmExportType').disable();
				} else {
					form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_prikreplennogo_naseleniya_v_formate_xml'];
				}
				break;
		}
		if (form.exportMod && form.exportMod == '06-FR') {
			form.DatePanel.show();
			form.findById('VZNBegDate').allowBlank = false;
			form.findById('VZNEndDate').allowBlank = false;
		} else {
			form.DatePanel.hide();
			form.findById('VZNBegDate').allowBlank = true;
			form.findById('VZNEndDate').allowBlank = true;
		}

        this.findById('rexmDate').setValue(getGlobalOptions().date);
        this.buttons[0].enable();
        this.syncShadow();
    }
});