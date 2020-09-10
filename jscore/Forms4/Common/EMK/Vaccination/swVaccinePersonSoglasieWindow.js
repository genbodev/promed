/**
 * swVaccinePersonSoglasieWindow - Форма Согласие на вакцинацию
 *
 */
Ext6.define('common.EMK.Vaccination.swVaccinePersonSoglasieWindow', {
    extend: 'base.BaseForm',
	alias: 'widget.swVaccinePersonSoglasieWindow',
	autoShow: false,
	cls: 'arm-window-new save-template-window arm-window-new-without-padding',
	title: 'Согласие на вакцинацию',
	renderTo: main_center_panel.body.dom,
	width: 560,
    modal: true,
    initComponent: function() {
		var me = this;
		var labelWidth = 140;
		me.formPanel = Ext6.create('Ext6.form.Panel', {
            url:'/?c=EvnPrescr&m=saveEvnPrescrPermission',
            region: 'center',
            autoHeight: true,
            border: false,
            id: 'vaccineConfirmPersonForm',
			bodyPadding: '20 20 20 20',
            trackResetOnLoad: false,
			defaults: {
				anchor: '100%',
				labelWidth: labelWidth
            },
			items: [
                {
                    xtype: 'checkbox',
					fieldLabel: 'Личное заявление',
                    name: 'PersonalStatement',
                    id: 'PersonalStatement',
                    inputValue: true,
					uncheckedValue: false,
					listeners: {
						change: function(checkbox, checked){
                            me.changePersonalStatementState(checked)
						}
					}
                },
                {
                    name: 'Person_pid',
                    id: 'Person_pid',  // законный представитель (id)
                    value: 0,
                    xtype: 'hidden'
                }, 
                {
                    fieldLabel: 'Законный представитель',
                    xtype: 'textfield',
                    name: 'LegalRepresent',
                    id: 'LegalRepresent',
                    hidden: false,
                    readOnly: true,
                    listeners: {
                        afterrender: function(component) {
                          component.getEl().on('click', function() { 
                            me.openSwPersonSearchWindowExt6()
                          });  
                        }
                    },
                }
            ]
        })

        Ext6.apply(me, {
            items: [
                me.formPanel
            ],
            buttons: [
                '->',
                {
                    cls: 'buttonCancel',
                    text: 'Отмена',
                    margin: 0,
                    handler: function() {
                        me.close();
                    }
                }, {
                    cls: 'buttonAccept',
                    text: 'Сохранить',
                    margin: '0 19 0 0',
                    handler: function() {
                        me.submit();
                    }
                }, {
                    cls: 'buttonAccept',
                    text: 'Сохранить и распечатать',
                    margin: '0 19 0 0',
                    handler: function() {
                        console.log('Сохранено, печать...')
                        me.submit();
                    }
                }
            ]
        });
        me.callParent(arguments);
    },
    close : function(){
		this.hide();
		this.destroy();
	},
    submit: function() {
        var wnd = this;
		var params = new Object();
        var base_form = wnd.formPanel.getForm()

        params.MedPersonal_id = wnd.params.MedPersonal_id;
        params.EvnPrescrVaccination_id = wnd.params.EvnPrescrVaccination_id
        params.EvnVaccination_id = wnd.params.EvnVaccination_id

        // законный представитель
        if(!base_form.findField('PersonalStatement').value) {
            // Валидация 
            if (base_form.findField('Person_pid').value == 0) {
                sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'));
                return false
            }
            // params.Person_pid = base_form.findField('Person_pid').value
        }
		wnd.getLoadMask('Подождите, идет сохранение...').show();
		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
            },
			success: function(result_form, action) {
                wnd.successCallback()
				wnd.getLoadMask().hide();
				wnd.close();
			}
		});
	},
    // смена состояния формы: личное заявление/законный представитель
    changePersonalStatementState: function (checked) {
        if(checked) {
            this.formPanel.getForm().findField('LegalRepresent').setDisabled(true)
            this.formPanel.getForm().findField('LegalRepresent').setValue(null)
            this.formPanel.getForm().findField('Person_pid').setValue(null)
            
        }
        else {
            this.formPanel.getForm().findField('LegalRepresent').setDisabled(false)
        }
    },
    // установка законного представителя
    changeLegalRepresent: function (personData) {
        PersonSurName_SurName = Ext.isEmpty(personData.PersonSurName_SurName)?'':personData.PersonSurName_SurName;
        PersonFirName_FirName = Ext.isEmpty(personData.PersonFirName_FirName)?'':personData.PersonFirName_FirName;
        PersonSecName_SecName = Ext.isEmpty(personData.PersonSecName_SecName)?'':personData.PersonSecName_SecName;

        var Person_Fio = PersonSurName_SurName + ' ' + PersonFirName_FirName + ' ' + PersonSecName_SecName
        var Person_id = personData.Person_id;
        var base_form = this.formPanel.getForm()
        base_form.findField('LegalRepresent').setValue(Person_Fio)
        base_form.findField('Person_pid').setValue(Person_id)
    },
    // выбор законного представителя
    openSwPersonSearchWindowExt6: function (action) {
        var win = this;
        var personParams = {
            notHideOnSelect: true,
            needUecIdentification: true,
            onSelect: function (pdata) {
				checkPersonDead({
					Person_id: pdata.Person_id,
					onIsLiving: function () {
                        getWnd('swPersonSearchWindowExt6').hide();
						win.changeLegalRepresent(pdata);
					},
					onIsDead: function (res) {
						Ext6.Msg.alert(langs('Ошибка'), langs('Запись невозможна в связи со смертью пациента'));
					}
				});
			},
            searchMode: 'all'
        }
        getWnd('swPersonSearchWindowExt6').show(personParams)
    }
})