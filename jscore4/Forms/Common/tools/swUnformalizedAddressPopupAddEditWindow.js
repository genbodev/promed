/*
 Объект СМП добавление/редактирование
 */

Ext.define('sw.tools.swUnformalizedAddressPopupAddEditWindow', {
    extend: 'Ext.window.Window',
    modal: true,
    width: 600,
    height: 800,
    title: 'Объект СМП',

    getBaseForm: function () {
        var me = this;

        me.mapPanel = Ext.create('sw.MapPanel', {
            height: 450,
            width: 600,
            callMarker: null,
            showTraffic: false,
            typeList: ['yandex'],
            addMarkByClick: true,
            toggledButtons: true,
            showCloseHelpButtons: false,
            point: null,
            klPoint: {},
            klTextAddtess: {},
            listeners: {
            	mapClick: function(e){

                    me.mapPanel.klPoint = {};
                    me.mapPanel.klTextAddtess = {};

            		var myMask = new Ext.LoadMask(me, {msg:"Идет определение КЛАДР адреса..."});
            		myMask.show();

            		//src = 'extjs4/resources/themes/images/default/grid/loading.gif';

            		this.point = e.point;

                    me.mapPanel.addAccidentMarker(this.point[0], this.point[1]);

            		//saveBtn.setDisabled(true);

            		//addBtn.setDisabled(true);

                    me.mapPanel.getAddressFromLatLng(e.point, function(results){
            			var reqestdata = {};
            			if(results.countryShortName.toLowerCase()!='ru' && (!getRegionNick().inlist(['kz'])) ){
            				Ext.MessageBox.alert('Ошибка', 'Адрес за пределами РФ');
            			}

            			//сервис Google порой выдает такое название 'Unnamed Road'
            			if(results.streetLongName == 'Unnamed Road') results.streetLongName='';
            			if(results.streetShortName == 'Unnamed Road') results.streetShortName = '';

            			for (var key in results) {
            				switch(key){
            					//case 'streetNum' : {reqestdata.streetNumber = results[key] ;break;}
            					case 'streetLongName' : {reqestdata.streetName = results[key] ;break;}
            					//case 'regionName' : {reqestdata.regionName = results[key] ;break;}
            					case 'areaShortName' : {reqestdata.cityName = results[key] ;break;}
            					//case 'areaShortName': {reqestdata.areaShortName = results[key] ;break;}
            					//case 'cityShortName' : {reqestdata.cityShortName = results[key] ;break;}
            				}
            			};

            			Ext.Ajax.request({
            			url: '/?c=CmpCallCard4E&m=getUnformalizedAddressStreetKladrParams',
            			params: reqestdata,
            				callback: function(opt, success, response) {

            					myMask.hide();
            					if (success){
            						var res = Ext.JSON.decode(response.responseText);
                                    var form = me.down('form').getForm();

            						if (res[0]){
            							var data = res[1][0],
            								textAdderss = '';

            							var region = parseInt(data.KLAdr_Code.substr(0,2)); // первые два символа - регион
            							if(!getGlobalOptions().region.nick.inlist(['krym', 'kz']) && data.KLArea_pid && region != getGlobalOptions().region.number ){
            								var msg = Ext.Msg.alert('Ошибка', 'Адрес за пределами региона');
            								Ext.defer(function() {msg.hide();}, 1500);
            								return false;
            							};

            							if(results.streetLongName == undefined || !results.streetLongName){
            								// если улицы нет, то ее упоминания не должно быть в data.
            								data.KLStreet_Nick = '';
            								data.KLStreet_Name = '';
            								data.KLStreet_id = '';
            							}
                                        me.mapPanel.klPoint.KLAreaStat_id = data.KLAreaStat_id;
                                        me.mapPanel.klPoint.KLArea_id = data.KLArea_id;
                                        me.mapPanel.klPoint.KLStreet_id =  data.KLStreet_id;
                                        me.mapPanel.klPoint.KLSubRgn_id = data.KLSubRgn_id;
                                        me.mapPanel.klPoint.KLRgn_id = data.KLArea_pid;
                                        me.mapPanel.klPoint.KLTown_id = data.KLTown_id;
            							if (results.establishmentName){}
            							//край
            							if (data.pKLArea_Name){textAdderss += data.pKLArea_Name}
            							if (data.region ){textAdderss += ' '+data.region }
            							//город
            							if (data.KLSocr_Nick){textAdderss += ', '+data.KLSocr_Nick}
            							if (data.KLArea_Name){textAdderss += ' '+data.KLArea_Name}
            							//улица
            							if (data.KLStreet_Nick){textAdderss += ', '+data.KLStreet_Nick}
            							if (data.KLStreet_Name){textAdderss += ' '+data.KLStreet_Name}
            							//номер
            							if(results.streetNum){textAdderss += ' '+results.streetNum; me.mapPanel.klPoint.streetNum = results.streetNum}
            							//доп адрес
            							if(results.establishmentName){me.mapPanel.klTextAddtess.alt = results.establishmentName;}

                                        me.mapPanel.klTextAddtess.base = textAdderss;

                                        var params = {
                                            'UnformalizedAddressDirectory_lat': e.point[0],
                                            'UnformalizedAddressDirectory_lng': e.point[1],
                                            'KLAreaStat_id': data.KLAreaStat_id,
                                            'KLCity_id': data.KLArea_id,
                                            'KLTown_id': data.KLTown_id,
                                            'KLRgn_id': data.KLArea_pid,
                                            'KLStreet_id': data.KLStreet_id,
                                            'KLSubRgn_id': data.KLSubRgn_id,
                                            'UnformalizedAddressDirectory_Dom': results.streetNum,
                                            'UnformalizedAddressDirectory_Address': textAdderss
                                        };

                                        form.setValues(params)
            						}

            					}
            				}
            			});

            		})
            	}
            },
            addAccidentMarker: function(lat, lng){
                me.mapPanel.removeMarkers([me.mapPanel.findMarkerBy('type', 'placementCursor')]);

            	var marker = [{
            		point:[lat, lng],
            		baloonContent:'Возможное местоположение',
            		imageHref: '/img/googlemap/firstaid.png',
            		imageSize: [30,35],
            		imageOffset: [-16,-37],
            		additionalInfo: {type:'placementCursor'},
            		center: true
            	}];

                me.mapPanel.setMarkers(marker);
            }
        });

        return Ext.create('sw.BaseForm', {
            xtype: 'BaseForm',
            id: this.id + '_BaseForm',
            items: [

                {
                    xtype: 'container',
                    padding: '10 0 0 0',
                    width: '100%',
                    bodyPadding: 10,
                    layout: 'vbox',
                    defaults: {
                        labelAlign: 'left',
                        labelWidth: 250,
                        width: '100%'
                    },
                    items: [{
                        border: false,
                        padding: '10 10 10 10',
                        xtype: 'fieldset',
                        title: 'Основной раздел',
                        items: [

                            {
                                xtype: 'textfield',
                                //allowBlank: false,
                                //translate: false,
                                fieldLabel: 'Название',
                                name: 'UnformalizedAddressDirectory_Name',
                                //displayField: 'UnformalizedAddressDirectory_Name',
                                //valueField: 'UnformalizedAddressDirectory_id',
                                //autocompleteField: false
                            },
                            {
                                xtype: 'swTypeOfUnformalizedAddress',
                                displayField: 'UnformalizedAddressType_Name',
                                valueField: 'UnformalizedAddressType_id',
                                name: 'UnformalizedAddressType_id',
                                fieldLabel: 'Тип объекта',
                                listeners: {
                                    select: function(){
                                        me.setRequiredFields()
                                    }
                                }
                            },
                            {
                                xtype: 'lpuAllLocalCombo',
                                fieldLabel: 'МО',
                                labelAlign: 'left',
                                name: 'Lpu_aid',
                                bigFont: false,
                                autoFilter: false,
                                refId: 'lpuAllLocalCombo'
                            },
                            {
                                xtype: 'AddressCombo',
                                fieldLabel: 'Адрес',
                                name: 'UnformalizedAddressDirectory_Address',
                                onTrigger2Click : function(){
                                    if(!this.getValue()) return;
                                    var form = me.down('form').getForm();

                                    form.findField('KLRgn_id').setValue(0);
                                    form.findField('KLSubRgn_id').setValue(0);
                                    form.findField('KLCity_id').setValue(0);
                                    form.findField('KLTown_id').setValue(0);
                                    form.findField('KLStreet_id').setValue(0);
                                    form.findField('UnformalizedAddressDirectory_Dom').setValue('');
                                    form.findField('UnformalizedAddressDirectory_Corpus').setValue('');
                                    form.findField('UnformalizedAddressDirectory_Address').setValue('');

                                    this.setValue('');
                                },
                                onTrigger1Click : function(){
                                    var field = this,
                                        form = me.down('form').getForm(),
                                        addressObj = {
                                            Country_id: ( getRegionNick() == 'kz' ) ? 398 : 643,
                                            KLRegion_id: parseInt(( form.findField('KLRgn_id').getValue() ) ? form.findField('KLRgn_id').getValue() : getGlobalOptions().region.number),
                                            KLSubRGN_id: parseInt(form.findField('KLSubRgn_id').getValue()),
                                            KLCity_id: parseInt(form.findField('KLCity_id').getValue()),
                                            KLTown_id: parseInt(form.findField('KLTown_id').getValue()),
                                            KLStreet_id: parseInt(form.findField('KLStreet_id').getValue()),
                                            House: form.findField('UnformalizedAddressDirectory_Dom').getValue(),
                                            Corpus: form.findField('UnformalizedAddressDirectory_Corpus').getValue(),
                                            full_address: form.findField('UnformalizedAddressDirectory_Address').getValue()
                                        };

                                    field.showAddressWindow(addressObj, function(data){
                                        form.findField('KLRgn_id').setValue(data.KLRegion_id);
                                        form.findField('KLSubRgn_id').setValue(data.KLSubRGN_id);
                                        form.findField('KLCity_id').setValue(data.KLCity_id);
                                        form.findField('KLTown_id').setValue(data.KLTown_id);
                                        form.findField('KLStreet_id').setValue(data.KLStreet_id);
                                        form.findField('UnformalizedAddressDirectory_Dom').setValue(data.House);
                                        form.findField('UnformalizedAddressDirectory_Corpus').setValue(data.Corpus);
                                        form.findField('UnformalizedAddressDirectory_Address').setValue(data.full_address);
                                    });

                                }
                            },
                            {
                                xtype: 'textfield',
                                name: 'UnformalizedAddressDirectory_lat',
                                fieldLabel: 'Широта'
                            },
                            {
                                xtype: 'textfield',
                                name: 'UnformalizedAddressDirectory_lng',
                                fieldLabel: 'Долгота'
                            },
                            {
                                xtype: 'SmpUnitsSelectedUser',
                                name: 'LpuBuilding_id',
                                fieldLabel: 'Подразделение СМП'
                            },
                            { xtype: 'hidden', name: 'KLAreaStat_id'},
                            { xtype: 'hidden', name: 'KLCity_id'},
                            { xtype: 'hidden', name: 'KLTown_id'},
                            { xtype: 'hidden', name: 'KLRgn_id'},
                            { xtype: 'hidden', name: 'KLStreet_id'},
                            { xtype: 'hidden', name: 'KLSubRgn_id'},
                            { xtype: 'hidden', name: 'UnformalizedAddressDirectory_Dom'},
                            { xtype: 'hidden', name: 'UnformalizedAddressDirectory_Corpus'},
                            { xtype: 'hidden', name: 'UnformalizedAddressDirectory_id'}
                        ]
                    },
                        {
                            xtype: 'container',
                            height: 500,
                            width: 600,
                            items: [
                                me.mapPanel
                            ]
                        }

                    ]
                }]
        });
    },

    initComponent: function () {
        var me = this,
            conf = me.initialConfig;

        me.addEvents({
            saveUnformalizedAdress: true
        });

        me.on('show', function (cmp) {
            var form = me.down('form').getForm();

			if(conf.record) {
				form.setValues(conf.record);
				if (conf.record.UnformalizedAddressDirectory_lat && conf.record.UnformalizedAddressDirectory_lng) {
					me.mapPanel.on('afterMapRender', function () {
						me.mapPanel.addAccidentMarker(conf.record.UnformalizedAddressDirectory_lat, conf.record.UnformalizedAddressDirectory_lng);
					});
				};
			};

        });

        Ext.applyIf(me, {
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                this.getBaseForm()
            ],
            dockedItems: [{
                xtype: 'container',
                dock: 'bottom',
                layout: {
                    type: 'hbox',
                    align: 'stretch',
                    padding: 4
                },
                items: [{
                    xtype: 'container',
                    layout: 'column',
                    items: []
                }, {
                    xtype: 'container',
                    flex: 1,
                    layout: {
                        type: 'hbox',
                        align: 'stretch',
                        pack: 'end'
                    },
                    items: [
                        {
                            xtype: 'button',
                            iconCls: 'ok16',
                            text: 'Сохранить',
                            refId: 'saveBtn',
                            handler: function () {
                                me.saveUnformalizedAdress();
                            }
                        },
                        {xtype: 'tbfill'},
                        {
                            xtype: 'button',
                            text: 'Помощь',
                            iconCls: 'help16',
                            tabIndex: 30,
                            handler: function () {
                                ShowHelp(this.up('window').title);
                            }
                        },
                        {
                            xtype: 'button',
                            iconCls: 'cancel16',
                            text: 'Закрыть',
                            margin: '0 5',
                            handler: function () {
                                me.close()
                            }
                        }
                    ]
                }]
            }]
        });
        me.callParent(arguments);
    },

    saveUnformalizedAdress: function (mode) {
        var me = this,
            conf = me.initialConfig,
            frm = me.down('form').getForm(),
            params = frm.getValues(),
            records = [];

        if (!frm.isValid()) {
            Ext.Msg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG);
            return;
        }
        records.push(params);
        Ext.Ajax.request({
            url: '/?c=CmpCallCard4E&m=saveUnformalizedAddress',
            params: {
                addresses: Ext.encode(records)
            },
            callback: function(options, success, response){

                if (!success) {
                    Ext.Msg.alert(ERR_WND_TIT, ERR_CONNECT_FAILURE);
                    return;
                }

                var responseText = Ext.decode(response.responseText);
                if (!responseText.success) {
                    Ext.Msg.alert(ERR_WND_TIT, responseText.Error_Msg + (responseText.Error_Code ? '('+ responseText.Error_Code +')' : ''));
                    return;
                }

                me.fireEvent('saveUnformalizedAdress', responseText.UnformalizedAddressDirectory_id);
                me.close();

                var msg = Ext.Msg.alert('Сообщение', 'Изменения сохранены.');
                Ext.defer(function(){
                    msg.hide();
                }, 1000);
            }
        });

    },
    setRequiredFields: function(){
        var me = this,
            frm = me.down('form').getForm(),
            UnformalizedAddressTypeField = frm.findField('UnformalizedAddressType_id'),
            LpuField = frm.findField('Lpu_aid');

        console.log('UnformalizedAddressTypeField.getValue()=', UnformalizedAddressTypeField.getValue())
        console.log('frm.isValid()=', frm.isValid())
        LpuField.allowBlank = (UnformalizedAddressTypeField.getValue() != 7);
        LpuField.validate();
        frm.isValid()
    }
});