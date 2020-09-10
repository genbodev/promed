/*Форма заполнения экспертных оценок*/
Ext.define('sw.tools.swExpertResponseWindow', {
    alias: 'widget.swExpertResponseWindow',
    extend: 'Ext.window.Window',
    title: 'Экспертная оценка',
    width: 900,
    height: 600,
    layout: 'fit',
    modal: true,

    initComponent: function () {

        var me = this;

		me.loadMask = new Ext.LoadMask(
			this,
			{msg: "Подождите, идет загрузка..."}
		);

        me.suspendEvents();

        me.on('show', function(){me.loadValues(arguments[0])});

        me.swExpertResponseFormPanel = Ext.create('sw.BaseForm', {
            refId: this.id + '_form',
            layout: 'form',
            floatable: true,
            region: 'middle',
            splitterResize: false,
            overflowY: 'scroll',
            items:[
                {
                    xtype: 'hidden',
                    name: 'CmpCloseCard_id'
                }
            ]
        });

        Ext.Ajax.request({
            url: '/?c=CmpCallCard&m=getExpertResponseFields',
            callback: function (obj, success, response) {
                var response_obj = Ext.JSON.decode(response.responseText),
                    Attributes = response_obj.Attributes,
                    ExpertResponseTypes = response_obj.ExpertResponseTypes;
                if(!Attributes || Attributes.length==0 || !ExpertResponseTypes) return;

				var accesses = {
					1: 'smpheaddoctor',//Старший врач
					2: 'smpnachmed',//СМП Начмед
					3: 'smpnachmed',//СМП Начмед
					4: 'zmk' //АРМ центра медицины катастроф».
				};

				for (var i = 0; i < ExpertResponseTypes.length; i++) {
					var type = ExpertResponseTypes[i],
						items = [];

					for (var j = 0; j < Attributes.length; j++) {

						var attr = Attributes[j],
							innerItems = [];

						var innerItems = [];

						innerItems.push(
							{
								name: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id,
								refId: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id,
								boxLabel: attr.AttributeValue_Value + ' - ' + attr.AttributeValue_Text,
								xtype: 'checkbox',
								hideLabel: true,
								flex: 1,
								AttributeValue_id: attr.AttributeValue_id,
								CMPCloseCardExpertResponse_id: null,
								listeners: {
									change: function (cmp, newVal, oldVal) {
										var labelField = Ext.ComponentQuery.query('[refId=' + cmp.refId + '_Label]')[0];
										Ext.ComponentQuery.query('[refId=' + cmp.refId + '_Comm]')[0].setVisible(newVal);
										if(newVal && !labelField.text){
											var arrFio = getGlobalOptions().CurMedPersonal_FIO.split(' '),
												date = Ext.Date.format(new Date(),'d.m.Y H:i');
											labelField.setText(date + ' ' + arrFio[0] + ' ' + arrFio[1].charAt(0) + '.' + arrFio[2].charAt(0) + '.')
										}else{
											labelField.setText()
										}
										labelField.setVisible(newVal);
									}
								}
							}
						);
						innerItems.push(
							{
								xtype: 'textfield',
								flex: 1,
								name: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeComm_' + attr.AttributeValue_id,
								refId: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id + '_Comm',
								hidden: true
							}
						);
						innerItems.push(
							{
								xtype: 'label',
								flex: 1,
								disabled: true,
								hidden: true,
								style: 'color:grey',
								refId: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id + '_Label',
							}
						);
						items.push(
							{
								xtype: 'container',
								layout: 'hbox',
								defaults: {
									margin: 3
								},
								items: innerItems
							}
						)

					}

					me.swExpertResponseFormPanel.add(
						{
							xtype: 'container',
							autoHeight: true,
							layout: 'form',
							margin: 10,
							items: [
								{
									columns: [700],
									vertical: true,
									disabled: !isUserGroup(accesses[type.ExpertResponseType_id]),
									name: 'ExpertResponseType_' + type.ExpertResponseType_id,
									ExpertResponseType_id: type.ExpertResponseType_id,
									fieldLabel: type.ExpertResponseType_Name,
									cls: 'expert-response-container',
									xtype: 'checkboxgroup',
									singleValue: false,
									items: items
								}
							]
						}
					)

				}





				/*
                var AttributeResponse = Attributes[147], //Оценка
                    AttributeResponseName = Attributes[145], //Наименование
                    //id раздела : группа доступа
                    accesses = {
                        1: 'smpheaddoctor',//Старший врач
                        2: 'smpnachmed',//СМП Начмед
                        3: 'smpnachmed',//СМП Начмед
                        4: 'zmk' //АРМ центра медицины катастроф».
                    };



                for (var i = 0; i < ExpertResponseTypes.length; i++) {
                    var type = ExpertResponseTypes[i],
                        items = [];
                    for (var j = 0; j < AttributeResponse.length; j++) {

                        var attr = AttributeResponse[j],
                            attrName = AttributeResponseName[j],
                            innerItems = [];

						debugger;

                        innerItems.push(
                            {
                                name: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id,
                                refId: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id,
                                boxLabel: attr.AttributeValue_ValueText + ' - ' + attrName.AttributeValue_ValueText,
                                xtype: 'checkbox',
                                hideLabel: true,
                                flex: 1,
                                AttributeValue_id: attr.AttributeValue_id,
                                CMPCloseCardExpertResponse_id: null,
                                listeners: {
                                    change: function (cmp, newVal, oldVal) {
                                        var labelField = Ext.ComponentQuery.query('[refId=' + cmp.refId + '_Label]')[0];
                                        Ext.ComponentQuery.query('[refId=' + cmp.refId + '_Comm]')[0].setVisible(newVal);
                                        if(newVal && !labelField.text){
                                            var arrFio = getGlobalOptions().CurMedPersonal_FIO.split(' '),
                                                date = Ext.Date.format(new Date(),'d.m.Y H:i');
                                            labelField.setText(date + ' ' + arrFio[0] + ' ' + arrFio[1].charAt(0) + '.' + arrFio[2].charAt(0) + '.')
                                        }else{
                                            labelField.setText()
                                        }
                                        labelField.setVisible(newVal);
                                    }
                                }
                            }
                        );
                        innerItems.push(
                            {
                                xtype: 'textfield',
                                flex: 1,
                                name: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeComm_' + attr.AttributeValue_id,
                                refId: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id + '_Comm',
                                hidden: true
                            }
                        );
                        innerItems.push(
                            {
                                xtype: 'label',
                                flex: 1,
                                disabled: true,
                                hidden: true,
                                style: 'color:grey',
                                refId: 'ExpertResponseType_' + type.ExpertResponseType_id + '_AttributeValue_' + attr.AttributeValue_id + '_Label',
                            }
                        );
                        items.push(
                            {
                                xtype: 'container',
                                layout: 'hbox',
                                defaults: {
                                    margin: 3
                                },
                                items: innerItems
                            }
                        )
                    }

                    me.swExpertResponseFormPanel.add(
                        {
                            xtype: 'container',
                            autoHeight: true,
                            layout: 'form',
                            margin: 10,
                            items: [
                                {
                                    columns: [700],
                                    vertical: true,
                                    disabled: !isUserGroup(accesses[type.ExpertResponseType_id]),
                                    name: 'ExpertResponseType_' + type.ExpertResponseType_id,
                                    ExpertResponseType_id: type.ExpertResponseType_id,
                                    fieldLabel: type.ExpertResponseType_Name,
                                    cls: 'expert-response-container',
                                    xtype: 'checkboxgroup',
                                    singleValue: false,
                                    items: items
                                }
                            ]
                        }
                    )
                }
				*/
            me.resumeEvents();
            me.loadValues(me.initialConfig);
            }
        });

        Ext.applyIf(me, {
            width: 900,
            items: [
                me.swExpertResponseFormPanel
            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                items: [
                    {
                        xtype: 'button',
                        refId: 'saveBtn',
                        iconCls: 'save16',
                        text: 'Сохранить',
                        margin: '0 5',
                        handler: function () {
                            me.saveExpertResponses()
                        }
                    },
                    '->',
                    {
                        xtype: 'button',
                        refId: 'cancelBtn',
                        iconCls: 'cancel16',
                        text: 'Закрыть',
                        margin: '0 5',
                        handler: function () {
                            this.up('window').close()
                        }
                    }
                ]
            }]
        });

        me.callParent(arguments);

    },
    saveExpertResponses: function(){
        var me = this,
            params,
            ExpertResponseParams = [];
        if(me.swExpertResponseFormPanel) {
            var gpoups = Ext.ComponentQuery.query('checkboxgroup',me.swExpertResponseFormPanel);

            for (var i = 0; i < gpoups.length; i++) {

                for (var j = 0; j < gpoups[i].items.items.length; j++) {

                    var responsefield = gpoups[i].items.items[j].down('checkbox');

                    var action = responsefield.CMPCloseCardExpertResponse_id ? "edit" : 'add';
                    if(responsefield.isDirty && responsefield.CMPCloseCardExpertResponse_id && !responsefield.getValue()){
                        action = 'del';
                    }

                    ExpertResponseParams.push(
                        {
                            name: responsefield.name,
                            value: responsefield.getValue(),
                            CMPCloseCardExpertResponse_id: responsefield.CMPCloseCardExpertResponse_id,
                            CMPCloseCardExpertResponseType_id: gpoups[i].ExpertResponseType_id,
                            AttributeValue_id:	responsefield.AttributeValue_id,
                            CMPCloseCardExpertResponse_Comment: Ext.ComponentQuery.query('[refId=' + responsefield.refId + '_Comm]')[0].getValue(),
                            action: action
                        }
                    )
                }
            }
        }
        params = {
            ExpertResponseJSON: Ext.JSON.encode(ExpertResponseParams),
            CmpCloseCard_id: me.swExpertResponseFormPanel.getForm().findField('CmpCloseCard_id').getValue()
        };
        Ext.Ajax.request({
            url: '/?c=CmpCallCard&m=saveCmpCloseCardExpertResponseList',
            params: params,
            callback: function(opt, success, response) {
                me.close();
            }
        });

    },
    loadValues: function (config) {

        var me = this,
            closecard_id = config.closecard_id,
            baseform = me.swExpertResponseFormPanel.getForm();
        baseform.findField('CmpCloseCard_id').setValue(closecard_id);

        me.loadMask.show();

        //загрузка значений
        if(!Ext.isEmpty(closecard_id)){
            Ext.Ajax.request({
                params: {
                    CmpCloseCard_id: closecard_id
                },
                url: '/?c=CmpCallCard&m=getCmpCloseCardExpertResponses',
                callback: function (obj, success, response) {
					me.loadMask.hide();
                    var response_obj = Ext.JSON.decode(response.responseText);
                    if(response_obj.length){
                        for(var key = 0; response_obj.length > key; key++) {
                            var elem = response_obj[key],
                                fieldId = 'ExpertResponseType_' + elem.CMPCloseCardExpertResponseType_id + '_AttributeValue_' + elem.AttributeValue_id,
                                field = Ext.ComponentQuery.query('[refId=' + fieldId + ']')[0],
                                fieldComm = Ext.ComponentQuery.query('[refId=' + fieldId + '_Comm]')[0],
                                fieldLabel = Ext.ComponentQuery.query('[refId=' + fieldId + '_Label]')[0];

                            field.CMPCloseCardExpertResponse_id = elem.CMPCloseCardExpertResponse_id;
                            fieldLabel.setText(elem.ResponseDT + ' ' + elem.Person_FIO);
                            field.setValue(1);
                            fieldComm.setValue(elem.CMPCloseCardExpertResponse_Comment);

                        }
                    }
                }
            })
        }
    }
});