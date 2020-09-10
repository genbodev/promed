/**
 * swLabPrintBarcodesForm
 */

sw.Promed.swLabPrintBarcodesForm = Ext.extend(sw.Promed.BaseForm,
    {
        action: null,
        autoHeight: true,
        buttonAlign: 'left',
        callback: Ext.emptyFn,
        closable: true,
        closeAction: 'hide',
        draggable: true,
        split: true,
        width: 600,
		modal: true,
        layout: 'form',
        id: 'swLabPrintBarcodesForm',
        title: 'Печать штрих-кодов без привязки к заявке/пробе',
        listeners:
        {
            hide: function()
            {
                this.onHide();
            }
        },
        modal: true,
        onHide: Ext.emptyFn,
        plain: true,
        resizable: false,
        doSave: function()
        {
            //
        },
        printBarcodes: function() {
        	var form = this.findById('LabPrintBarcodesForm');
            var listCombo = form.findById('serviceListComboStat');
            var fieldQuantity = form.findById('fieldQuantity');

            if(!listCombo.getValue() || !fieldQuantity.getValue()){
                sw.swMsg.show(
                {
                    buttons:Ext.Msg.OK,
                    fn:function () {
                        //
                    }.createDelegate(this),
                    icon:Ext.Msg.WARNING,
                    msg:ERR_INVFIELDS_MSG,
                    title:ERR_INVFIELDS_TIT
                });
                return false;
            }
            if(fieldQuantity.getValue() > 100){
                sw.swMsg.show(
                {
                    buttons:Ext.Msg.OK,
                    fn:function () {
                        //
                    }.createDelegate(this),
                    icon:Ext.Msg.WARNING,
                    msg: 'Количество необходимых штрих-кодов не должно превышать 100',
                    title:ERR_INVFIELDS_TIT
                });
                return false;
            }

            var params = {
                'MedService_id': listCombo.getValue(),
                'quantity': fieldQuantity.getValue()
            };
            current_window.showLoadMask(langs('Загрузка данных ...'));

            Ext.Ajax.request({
                failure:function () {
                    current_window.hideLoadMask();
                    sw.swMsg.alert(langs('Ошибка !!!'), langs('Не удалось получить данные с сервера'));
                },
                params: params,
                success:function (response) {
                    current_window.hideLoadMask();
                    
                    var result = Ext.util.JSON.decode(response.responseText);
                    if(result.barcodesNums){
                        if ( Ext.globalOptions.lis ) {
                            var Report_Params = '&s=' + result.barcodesNums;
                            var ZebraDateOfBirth = (Ext.globalOptions.lis.ZebraDateOfBirth) ? 1 : 0;
                            var ZebraUsluga_Name = (Ext.globalOptions.lis.ZebraUsluga_Name) ? 1 : 0;
                            var ZebraDirect_Name = (Ext.globalOptions.lis.ZebraDirect_Name) ? 1 : 0;
                            var ZebraFIO = (Ext.globalOptions.lis.ZebraFIO) ? 1 : 0;
                            Report_Params = Report_Params + '&paramPrintType=3'
                            Report_Params = Report_Params + '&marginTop=' + Ext.globalOptions.lis.labsample_barcode_margin_top;
                            Report_Params = Report_Params + '&marginBottom=' + Ext.globalOptions.lis.labsample_barcode_margin_bottom;
                            Report_Params = Report_Params + '&marginLeft=' + Ext.globalOptions.lis.labsample_barcode_margin_left;
                            Report_Params = Report_Params + '&marginRight=' + Ext.globalOptions.lis.labsample_barcode_margin_right;
                            Report_Params = Report_Params + '&width=' + Ext.globalOptions.lis.labsample_barcode_width;
                            Report_Params = Report_Params + '&height=' + Ext.globalOptions.lis.labsample_barcode_height;
                            Report_Params = Report_Params + '&barcodeFormat=' + Ext.globalOptions.lis.barcode_format;
                            Report_Params = Report_Params + '&paramLpu=' + getGlobalOptions().lpu_id;
                            Report_Params = Report_Params + '&ZebraDateOfBirth=' + ZebraDateOfBirth;
                            Report_Params = Report_Params + '&ZebraUsluga_Name=' + ZebraUsluga_Name;
                            Report_Params = Report_Params + '&paramFrom=' + ZebraDirect_Name;
                            Report_Params = Report_Params + '&paramFIO=' + ZebraFIO;

                            printBirt({
                                'Report_FileName': (Ext.globalOptions.lis.use_postgresql_lis ? 'barcodesprint_resize_pg' : 'barcodesprint_resize') + '.rptdesign',
                                'Report_Params': Report_Params,
                                'Report_Format': 'pdf'
                            });
                        }
                    }
                    current_window.hide();
                },
                url:'/?c=EvnLabSample&m=getNewListEvnLabSampleNum'
            });
		},
        submit: function()
        {
           //
        },
        show: function()
        {
            sw.Promed.swLabPrintBarcodesForm.superclass.show.apply(this, arguments);
            current_window = this;

            var form = this.findById('LabPrintBarcodesForm');
            var listCombo = form.findById('serviceListComboStat');

            this.MedService_id = arguments[0].MedService_id;
            
            if (listCombo) {
                var params = new Object();
                params.MedServiceTypeIsLabOrFenceStation = 1;
                params.Lpu_isAll = 2;
                // фильтруем лаборатории по MedService_id.
                params.MedService_id = this.MedService_id;
                params.ARMType = 'pzm';
                listCombo.getStore().removeAll();
                listCombo.getStore().load({
                    params: params
                });
            }
        },
        returnFunc: function(owner, kid) {},
        initComponent: function()
        {
            // Форма с полями 
            this.FormPanel = new Ext.form.FormPanel(
                {
                    autoHeight: true,
                    bodyStyle: 'padding: 5px',
                    border: false,
                    buttonAlign: 'left',
                    frame: true,
                    id: 'LabPrintBarcodesForm',
                    labelAlign: 'right',
                    labelWidth: 130,
                    items:[
                        {
							allowBlank: false,
							//xtype: 'swmedservicecombo',
							xtype: 'swmedserviceglobalcombo',
							id: 'serviceListComboStat',
							fieldLabel : 'Лаборатория',
							//autoLoad: true,
							hiddenName: 'MedService_id',
							value: '',
							//params: {Lpu_id: getGlobalOptions().lpu_id},
							listeners: {
								'change': function(combo, newValue, oldValue) {
									// 
								}.createDelegate(this)
							},
							width: 360
						}, {
							fieldLabel: langs('Количество'),
                            allowBlank: false,
                            xtype: 'textfield',
                            anchor: '40%',
                            //width: 300,
                            name: 'fieldQuantity',
                            id: 'fieldQuantity',
                            maskRe: /[0-9]/,
						}
                    ]
                });
            Ext.apply(this,
                {
                    buttons:
                        [{
                            handler: function()
                            {
                                this.ownerCt.printBarcodes();
                            },
                            iconCls: 'print16',
                            text: 'Печать'
                        },
                        {
                            text: '-'
                        },
                        HelpButton(this),
                        {
                            handler: function()
                            {
                                this.ownerCt.hide();
                            },
                            iconCls: 'cancel16',
                            text: BTN_FRMCLOSE
                        }],
                    items: [this.FormPanel]
                });
            sw.Promed.swLabPrintBarcodesForm.superclass.initComponent.apply(this, arguments);
        }
    });