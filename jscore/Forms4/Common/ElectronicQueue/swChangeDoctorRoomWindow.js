/**
 * swChangeDoctorRoomWindow - Диалог изменения кабинета приема
 *
 */

Ext6.define('common.ElectronicQueue.swChangeDoctorRoomWindow', {
    extend: 'base.BaseForm',
    title: 'Изменение кабинета приема',
    layout: 'border',
    width: 500,
    height: 290,
    resizable: true,
    maximizable: false,
    closable: true,
    modal: true,
    header: true,
    constrain: true,
    listeners: {
      'beforehide': function(){

          var form = this.mainPanel.getForm();

          if (this.onClose && typeof this.onClose === 'function') {
              var officeCombo = form.findField('LpuBuildingOffice_id');
              var rec = officeCombo.getSelection();

              if (rec && this.isSaved) {
                  this.onClose({selectedValue: rec.get('LpuBuildingOffice_id')});
              } else {
                  if (this.lastSelected_LpuBuildingOffice_id) {
                      this.onClose({selectedValue: this.lastSelected_LpuBuildingOffice_id});
                  }
              }
          }
      }
    },
    show: function(data) {

        var wnd = this;
        this.callParent(arguments);

        if (!data) {
            Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
                wnd.hide();
            });
            return false;
        }

        if (data) {

            // присваиваем все пришедшие переменные окну
            Object.keys(data).forEach(function(obj){
                wnd[obj] = data[obj];
            });

        } else data = {};

        wnd.isSaved = false;

        var panel = wnd.mainPanel.getForm();
        var officeCombo = panel.findField('LpuBuildingOffice_id');

        if (data.Lpu_id && data.LpuBuilding_id) {
            officeCombo.setLoadParams(
                {
                    Lpu_id: data.Lpu_id,
                    LpuBuilding_id: data.LpuBuilding_id
                }
            );

            this.doLoad();
        }
    },
    doSave: function() {

        var wnd = this;
        var form = wnd.mainPanel.getForm();

        form.submit({
            params: {
                MedStaffFact_id: (wnd.MedStaffFact_id) ? wnd.MedStaffFact_id : null,
                MedService_id: (wnd.MedService_id) ? wnd.MedService_id : null
            },
            url: '/?c=LpuBuildingOfficeMedStaffLink&m=changeCurrentOffice',
            success: function(res) {
                wnd.isSaved = true;
                wnd.hide();
            }
        });
    },
    doLoad: function() {

        var wnd = this;

        var panel = wnd.mainPanel.getForm();
        var officeCombo = panel.findField('LpuBuildingOffice_id');

        officeCombo.getStore().load({

            callback: function(){

                if (wnd.selected_LpuBuildingOffice_id) {
                    officeCombo.setValue(wnd.selected_LpuBuildingOffice_id);
                } else {
                    Ext.Ajax.request({
                        url: '/?c=LpuBuildingOfficeMedStaffLink&m=getCurrentOffice',
                        params: {
                            MedStaffFact_id: (wnd.MedStaffFact_id) ? wnd.MedStaffFact_id : null,
                            MedService_id: (wnd.MedService_id) ? wnd.MedService_id : null
                        },
                        callback: function (opt, success, response) {

                            if (success && response.responseText.length > 0) {

                                var result = Ext.util.JSON.decode(response.responseText);
                                if (result && result[0]) {

                                    officeCombo.setValue(result[0].LpuBuildingOffice_id);
                                    if (result[0].begTime) {
                                        var begTimeField = panel.findField('LpuBuildingOfficeVizitTime_begDate');
                                        begTimeField.setValue(result[0].begTime);
                                    }

                                    if (result[0].endTime) {
                                        var endTimeField = panel.findField('LpuBuildingOfficeVizitTime_endDate');
                                        endTimeField.setValue(result[0].endTime);
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    },
    initComponent: function() {

        var wnd = this;

        wnd.mainPanel = Ext6.create('Ext6.form.FormPanel', {
            autoScroll: true,
            region: 'center',
            border: false,
            bodyStyle: 'padding: 20px 20px 20px 20px;',
            fieldDefaults: {
                labelAlign: 'left',
                msgTarget: 'side'
            },
            defaults: {
                border: false,
                xtype: 'panel',
                width: 500,
                layout: 'anchor'
            },
            layout: 'vbox',
            items: [
                {
                    name: 'LpuBuildingOfficeMedStaffLink_id',
                    xtype: 'hidden'
                },
                {
                    xtype: 'swLpuBuildingOfficeCombo',
                    fieldLabel: 'Кабинет приема',
                    name: 'LpuBuildingOffice_id',
                    reference: 'LpuBuildingOffice_id',
                    allowBlank: false,
                    labelWidth: 120,
                    width: 400
                },
                {
                    xtype: 'fieldset',
                    width: 400,
                    title: 'Время приема',
                    style: 'padding-left: 5px',
                    items: [
                        {
                            fieldLabel: 'с',
                            name: 'LpuBuildingOfficeVizitTime_begDate',
                            xtype: 'swTimeField',
                            labelWidth: 120,
                            width: 250
                        },
                        {
                            fieldLabel: 'по',
                            name: 'LpuBuildingOfficeVizitTime_endDate',
                            xtype: 'swTimeField',
                            labelWidth: 120,
                            width: 250
                        }
                    ]
                }
            ]
        });

        Ext6.apply(wnd, {
            items: [wnd.mainPanel],
            buttons: [{
                handler: function() {
                    wnd.hide();
                },
                text: BTN_FRMCANCEL
                }, '->', {
                handler: function() {
                    wnd.doSave();
                },
                cls: 'flat-button-primary',
                text: langs('Сохранить')
            }]
        });

        this.callParent(arguments);
    }
});