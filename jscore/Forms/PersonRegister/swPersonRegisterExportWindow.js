/**
 * swPersonRegisterExportWindow - окно экспорта
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      03.2015
 */

sw.Promed.swPersonRegisterExportWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: false,
	width : 700,
	height : 400,
	modal: true,
	resizable: false,
	autoHeight: true,
	closeAction :'hide',
	border : false,
	plain : false,
    createXML: function(addParams)
    {
        var form = this;
        var fieldExportType = form.FormPanel.getForm().findField('ExportType');
        var fieldExportDate = form.FormPanel.getForm().findField('ExportDate');
        var fieldBegDate = form.FormPanel.getForm().findField('BegDate');
        var fieldEndDate = form.FormPanel.getForm().findField('EndDate');
        var fieldMo = form.FormPanel.getForm().findField('Lpu_eid');
        var base_form = form.FormPanel.getForm();

        if (!form.FormPanel.getForm().isValid()) {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }

        if ( fieldBegDate.getValue() > fieldEndDate.getValue()  ) {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.WARNING,
                    msg: lang['data_okonchaniya_perioda_ne_mojet_byit_ranshe_datyi_nachala'],
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }

        var params = {
			PersonRegisterType_SysNick: form.PersonRegisterType_SysNick,
			ExportMod: form.ExportMod,
            Lpu_eid: fieldMo.getValue(),
            ExportType: fieldExportType.getValue(),
            ExportDate: fieldExportDate.getRawValue(),
            BegDate: fieldBegDate.getRawValue(),
            EndDate: fieldEndDate.getRawValue()
        };

        form.getLoadMask().show();
		form.LogGrid.clearLog();
		form.LogGrid.insertMessage(lang['start_vyigruzki']);

        Ext.Ajax.request({
            url: form.exportUrl,
            params: params,
            timeout: 1800000,
            callback: function(options, success, response)
            {
                form.getLoadMask().hide();
                var result = Ext.util.JSON.decode(response.responseText);
                if (result.success) {
                    var alt = '';
                    var msg = '';
                    if (result.usePrevXml) {
                        alt = lang['izmeneniy_s_reestrom_ne_byilo_proizvedeno_ispolzuetsya_sohranennyiy_xml_predyiduschey_vyigruzki'];
                        msg = lang['xml_predyiduschey_vyigruzki'];
                    }

					if (result.ExportErrorArray) {
						form.LogGrid.insertMessage(result.ExportErrorArray);
					}

                    if (result.Link) {
						form.LogGrid.insertMessage({Text: 'Окончание выгрузки.<br/><a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить список</a>'+msg});
						//form.buttons[0].disable();
					}
					
					if (result.html) {
						var id_salt = Math.random();
						var win_id = 'printForm_' + Math.floor(id_salt*10000);
						var win = window.open('', win_id);
						win.document.write(result.html);
					}
                } else {
					form.LogGrid.insertMessage(lang['oshibka']+result.Error_Msg);
                }
            }
        });
    },
	initComponent: function() {
		var me = this;

        this.TextPanel = new Ext.Panel(
		{
			autoHeight: true,
			style : 'padding: 10px',
			bodyBorder: false,
			border: false,
			html: lang['vyigruzka_v_formate_xml']
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
			autoExpandMin: 400,
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
				var message_array = [];
				var current_date = new Date();
				var store = this.getGrid().getStore();
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
		
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			layout : 'form',
			border : false,
			frame : true,
			style : 'padding: 10px',
			labelWidth : 120,
			items : [{
				fieldLabel: lang['mo'],
				hiddenName: 'Lpu_eid',// Может не совпадать с МО пользователя
				anchor: '100%',
				xtype: 'swlpucombo'
			}, {
				name: 'BegDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				fieldLabel: lang['nachalo_perioda']
			},{
				name: 'EndDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				fieldLabel: lang['konets_perioda']
			}, {
				name: 'ExportDate',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				format: 'd.m.Y',
				fieldLabel: lang['data_vyigruzki'],
				allowBlank: false,
				disabled: true
			}, {
				name: 'ExportType',
				hiddenName: 'ExportType',
				xtype:'combo',
				store: new Ext.data.SimpleStore({
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
		});

    	Ext.apply(this, {
			items : [
                this.TextPanel,
                this.FormPanel,
				this.LogGrid
			],
			buttons : [{
				text : lang['eksport'],
				iconCls : 'ok16',
				handler : function(button, event) {
					me.createXML();
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
		sw.Promed.swPersonRegisterExportWindow.superclass.initComponent.apply(this, arguments);
	},
    show: function() {
        sw.Promed.swPersonRegisterExportWindow.superclass.show.apply(this, arguments);
		
        var form = this,
            fieldBegDate = form.FormPanel.getForm().findField('BegDate'),
            fieldEndDate = form.FormPanel.getForm().findField('EndDate'),
            fieldMo = form.FormPanel.getForm().findField('Lpu_eid'),
            fieldExportType = form.FormPanel.getForm().findField('ExportType'),
            fieldExportDate = form.FormPanel.getForm().findField('ExportDate'),
			btnExport = form.buttons[0];
			
		form.LogGrid.clearLog();
        form.FormPanel.getForm().reset();
        form.TextPanel.render();

        if (!arguments || !arguments[0] || !arguments[0].PersonRegisterType_SysNick) {
            Ext.Msg.alert(lang['oshibka_otkryitiya_formyi'], lang['ne_ukazanyi_neobhodimyie_dannyie']);
            this.hide();
            return false;
        }
		this.PersonRegisterType_SysNick = arguments[0].PersonRegisterType_SysNick;
		this.exportUrl = arguments[0].url || '/?c=PersonRegister&m=export';
		this.ExportMod = arguments[0].ExportMod || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		switch (this.PersonRegisterType_SysNick) {
			case 'orphan':
				form.setTitle(lang['vyigruzka_v_federalnyiy_registr_po_orfannyim_zabolevaniyam']);
				form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_po_orfannyim_zabolevaniyam_v_formate_xml'];
				fieldExportType.setContainerVisible(true);
				fieldExportDate.setContainerVisible(true);
				fieldExportType.setValue(2);
				fieldExportType.enable();
				fieldExportDate.setValue(getGlobalOptions().date);
				fieldBegDate.setContainerVisible(false);
				fieldEndDate.setContainerVisible(false);
				fieldBegDate.allowBlank = true;
				fieldEndDate.allowBlank = true;
				fieldMo.allowBlank = true;
				fieldMo.setContainerVisible(false);
				form.LogGrid.show();
				btnExport.setText(lang['vyigruzit']);
				break;
			default:
				form.setTitle(lang['vyigruzka_v_federalnyiy_registr']);
				form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_v_formate_xml'];
				fieldExportType.setContainerVisible(true);
				fieldExportDate.setContainerVisible(true);
				fieldExportType.setValue(2);
				fieldExportType.enable();
				fieldExportDate.setValue(getGlobalOptions().date);
				fieldBegDate.setContainerVisible(true);
				fieldEndDate.setContainerVisible(true);
				fieldBegDate.allowBlank = false;
				fieldEndDate.allowBlank = false;
				fieldMo.allowBlank = false;
				fieldMo.setContainerVisible(true);
				form.LogGrid.show();
				btnExport.setText(lang['vyigruzit']);
				break;
		}

        btnExport.enable();
        this.syncShadow();
    }
});