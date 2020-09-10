/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 30.09.14
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */
sw.Promed.swPersonCardAutoParamsWindow = Ext.extend(sw.Promed.BaseForm,
    {
        autoHeight: true,
        objectName: 'swPersonCardAutoParamsWindow',
        objectSrc: '/jscore/Forms/Polka/swPersonCardAutoParamsWindow.js',
        title:lang['parametryi_prikrepleniya'],
        layout: 'border',
        id: 'PCAP',
        modal: true,
        shim: false,
        resizable: false,
        maximizable: false,
        listeners:
        {
            hide: function()
            {
                this.onHide();
            }
        },
        onHide: Ext.emptyFn,
        width: 390,
		showMessage: function(title, message, fn) {
			if ( !fn )
				fn = function(){};
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: fn,
				icon: Ext.Msg.WARNING,
				msg: message,
				title: title
			});
		},
        getLoadMask: function()
        {
            if (!this.loadMask)
            {
                this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
            }
            return this.loadMask;
        },
        filterRegionType: function(){
            log(this.LpuRegionsByDate);
            var win = this;
            var base_form = Ext.getCmp('PCAP');
            var region_type_combo = base_form.findById('PCAP_LpuRegionTypeCombo');
            region_type_combo.getStore().filterBy(
                function(record){
                    if (
                        (getRegionNick() == 'perm' && record.get('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop', 'comp', 'prip'])) ||
                        (getRegionNick() != 'perm' && getRegionNick() != 'ufa' && record.get('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop'])) ||
                        (getRegionNick() == 'ufa' && record.get('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop']) && record.get('LpuRegionType_SysNick').inlist(win.LpuRegionsByDate))
                    )
                    {
                        return true;
                    }
                }
            );
        },
        show: function()
        {
            sw.Promed.swPersonCardAutoParamsWindow.superclass.show.apply(this, arguments);
            if(arguments[0].callback)
                this.callback = arguments[0].callback;
            var base_form = Ext.getCmp('PCAP');
            //base_form.findById('PCAP_LpuRegionTypeCombo').clearValue();
            this.LpuRegionsByDate = new Array();
			var region_type_combo = base_form.findById('PCAP_LpuRegionTypeCombo');
            var region_fap_combo = base_form.findById('PCAPW_LpuRegion_Fapid');
			region_type_combo.clearValue();
            region_type_combo.getStore().filterBy(
                function(record){
                    return false;
                }
            );
			/*region_type_combo.getStore().filterBy(
				function(record){
					if (
						(record.get('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop', 'comp', 'prip']) && getRegionNick() == 'perm' ) ||
							(record.get('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop']) && getRegionNick() != 'perm' )
						)
					{
						return true;
					}
				}
			);*/
            base_form.findById('PCAPLpuRegion_id').clearValue();
            base_form.findById('PCAPW_LpuRegion_Fapid').clearValue();

            base_form.findById('type_fieldset').items.items[0].setValue(true);
            base_form.findById('type_fieldset').items.items[1].setValue(false);
            //base_form.findById('PCAP_IsAttachCondit').setValue(0);
            //base_form.findById('PCAP_PersonCardAttach').setValue(0);
            if(getWnd('swWorkPlacePolkaRegWindow').isVisible() || getWnd('swWorkPlacePolkaRegWindowExt6').isVisible() || getWnd('swLpuAdminWorkPlaceWindow').isVisible() || getWnd('swLpuAdminWorkPlaceWindowExt6').isVisible())
            {
                base_form.findById('PCAP_Lpu_id').setValue(getGlobalOptions().lpu_id);
				base_form.findById('PCAP_Lpu_id').fireEvent('change', base_form.findById('PCAP_Lpu_id'), base_form.findById('PCAP_Lpu_id').getValue());
                base_form.findById('PCAP_Lpu_id').disable();

                region_fap_combo.setValue('');
                region_fap_combo.getStore().removeAll();
                region_fap_combo.getStore().load({
                    params:{
                        Org_id: getGlobalOptions().org_id,
                        add_without_region_line: true
                    },
                    callback: function() {
                        base_form.findById('PCAPW_LpuRegion_Fapid').clearValue();
                    }
                })
            }
            else{
                base_form.findById('PCAP_Lpu_id').clearValue();
                //base_form.findById('PCAP_Lpu_id').setValue('');
                base_form.findById('PCAP_Lpu_id').enable();
            }
            //if(getRegionNick()=='perm')
			if(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']))
            {
                base_form.findById('PCAPLpuRegion_id').setFieldLabel(lang['osnovnoy_uchastok']);
				base_form.findById('PCAP_LpuRegionTypeCombo').setFieldLabel(lang['tip_osnovnogo_uchastka']);
                //var region_fap_combo = base_form.findById('PCAPW_LpuRegion_Fapid');
                //region_fap_combo.getStore().load();
            }
            else
            {
                base_form.findById('PCAPLpuRegion_id').setFieldLabel(lang['uchastok']);
				base_form.findById('PCAP_LpuRegionTypeCombo').setFieldLabel(lang['tip_uchastka']);
            }

            var that = this;
        },
        submit: function()
        {
            var base_form = Ext.getCmp('PCAP');

			if(base_form.findById('PCAP_LpuRegionTypeCombo').getValue()=='')
			{
				this.showMessage(lang['soobschenie'], lang['ne_vse_polya_formyi_zapolnenyi_korrektno'], function() {
					base_form.findById('PCAP_LpuRegionTypeCombo').focus(true, 100);
					return false;
				});
			}
			else if(base_form.findById('PCAPLpuRegion_id').getValue()=='')
			{
				this.showMessage(lang['soobschenie'], lang['ne_vse_polya_formyi_zapolnenyi_korrektno'], function() {
					base_form.findById('PCAPLpuRegion_id').focus(true, 100);
					return false;
				});
			}
			else
			{
				var filters = base_form.mainPanel.getForm().getValues();
				filters.PCAPLpu_id = base_form.findById('PCAP_Lpu_id').getValue();
				var that = this;
				//https://redmine.swan.perm.ru/issues/72339
				if(getRegionNick() == 'perm'){
					var LpuRegionType_Combo = base_form.findById('PCAP_LpuRegionTypeCombo');
					var LpuRegion_Combo = base_form.findById('PCAPLpuRegion_id');
					var LpuRegion_id = LpuRegion_Combo.getValue();
					var LpuRegion_Name = LpuRegion_Combo.getFieldValue('LpuRegion_Name');
					var LpuRegionType_Name = LpuRegionType_Combo.getFieldValue('LpuRegionType_Name');
					var request_params = {
						LpuRegion_id: LpuRegion_id
					};
					Ext.Ajax.request({
						url: '/?c=LpuStructure&m=loadLpuRegionInfo',
						params: request_params,
						success: function(response, opts) {
							var result = Ext.util.JSON.decode(response.responseText);
							var LpuSection_id = result[0].LpuSection_id;
							var LpuBuilding_id = result[0].LpuBuilding_id;
							var MedStaffRegion_id = result[0].MedStaffRegion_id;
							var Msg_text = "Внимание! Для участка " + LpuRegionType_Name + " № " + LpuRegion_Name + "</br> ";
							var Msg_text_add = "";
							if(LpuBuilding_id == 0)
								Msg_text_add += "Отсутствует информация о подразделении </br>";
							if(LpuSection_id == 0)
								Msg_text_add += "Отсутствует информация об отделении </br>";
							if(MedStaffRegion_id == 0)
								Msg_text_add += "Отсутствует врач на участке либо период работы врача на участке закрыт </br>";
							if(Msg_text_add.length > 0){
								Msg_text += Msg_text_add;
								sw.swMsg.show(
									{
										title: '',
										msg: Msg_text,
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.WARNING,
										fn: function () {
											if(that.callback)
												that.callback(filters);
											that.hide();
										}
									})
							}
							else
							{
								if(that.callback)
									that.callback(filters);
								that.hide();
							}
						}
					});
				}
				else{
					if(this.callback)
						this.callback(filters);
					this.hide();
				}
			}
        },
        initComponent: function()
        {
            var form = this;

            this.mainPanel = new sw.Promed.FormPanel(
                {
                    region: 'center',
                    layout: 'form',
                    border: false,
                    frame: true,
                    style: 'padding: 10px;',
                    labelWidth: 90,
                    id: 'PCAP_mainPanel',
                    items:
                        [
                            new sw.Promed.SwLpuSearchCombo({
                                fieldLabel: lang['mo'],
                                allowBlank: false,
                                id: 'PCAP_Lpu_id',
                                disabled: false,//getWnd('swWorkPlacePolkaRegWindow').isVisible() || getWnd('swLpuAdminWorkPlaceWindow').isVisible(),
                                hiddenName: 'PCAPLpu_id',
                                listeners: {
                                    'change': function(combo, lpuId) {
										var form = this.ownerCt.ownerCt;
                                        if ( getGlobalOptions().superadmin )
                                        {
                                            var region_combo = form.findById('PCAPLpuRegion_id');
                                            var lpu_region_id = region_combo.getValue();

                                            form.findById('PCAPLpuRegion_id').getStore().removeAll();

                                            form.findById('PCAPLpuRegion_id').getStore().load({
                                                params: {
                                                    Lpu_id: lpuId,
                                                    LpuRegionType_id: form.findById('PCAP_LpuRegionTypeCombo').getValue(),
                                                    Object: 'LpuRegion',
                                                    showOpenerOnlyLpuRegions: 1
                                                },
                                                callback: function() {
                                                    form.findById('PCAPLpuRegion_id').clearValue();
                                                }
                                            });

                                            form.findById('PCAPW_LpuRegion_Fapid').getStore().removeAll();

                                            form.findById('PCAPW_LpuRegion_Fapid').getStore().load({
                                                params: {
                                                    Lpu_id: lpuId,
                                                    showOpenerOnlyLpuRegions: 1
                                                },
                                                callback: function() {
                                                    form.findById('PCAPW_LpuRegion_Fapid').clearValue();
                                                }
                                            });
                                        }
                                        if(getRegionNick()=='ufa')
                                        {
                                            var options = getGlobalOptions();
                                            var date = Date.parseDate(options['date'], 'd.m.Y');
                                            var request_params = {
                                                HolderDate: date,
                                                Lpu_id: lpuId
                                            };
                                            form.LpuRegionsByDate = [];
                                            Ext.Ajax.request({
                                                url: '/?c=LpuPassport&m=loadLpuPeriodFondHolderGrid',
                                                params: request_params,
                                                callback: function(options,success,response){
                                                    if(success) {
                                                        var resp = Ext.util.JSON.decode(response.responseText);
                                                        if(resp.length>0){
                                                            var i = 0;
                                                            for (i=0;i<resp.length;i++)
                                                                form.LpuRegionsByDate.push(resp[i].LpuRegionType_SysNick);
                                                            form.filterRegionType();
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                        else
                                            form.filterRegionType();
                                    }
                                },
                                listWidth: 200,
                                tabIndex: 2104,
                                width: 200
                            }),
                            {
                                allowBlank: false,
                                enableKeyEvents: true,
                                hiddenName : "PCAPLpuRegionType_id",
                                id: 'PCAP_LpuRegionTypeCombo',
                                listeners: {
                                    'change': function(combo, lpuRegionTypeId, oldLpuRegionTypeId) {
                                        var form = this.ownerCt.ownerCt;
                                        var region_combo = form.findById('PCAPLpuRegion_id');
                                        if ( lpuRegionTypeId != oldLpuRegionTypeId || region_combo.getStore().getCount() == 0 ) {
                                            var lpu_region_id = region_combo.getValue();
                                            var index = region_combo.getStore().findBy(function(record, id) {
                                                if ( record.data.LpuRegionType_id == lpuRegionTypeId && record.data.LpuRegion_id == lpu_region_id )
                                                    return true;
                                                else
                                                    return false;
                                            });
                                            if ( index == -1 )
                                            {
                                                form.findById('PCAPLpuRegion_id').clearValue();
                                            }
                                            form.findById('PCAPLpuRegion_id').getStore().removeAll();
                                            var lpu_id = getGlobalOptions().lpu_id;
                                            if ( getGlobalOptions().superadmin )
                                                lpu_id = form.findById('PCAP_Lpu_id').getValue();
                                            form.findById('PCAPLpuRegion_id').getStore().load({
                                                params: {
                                                    Lpu_id: lpu_id,
                                                    LpuRegionType_id: lpuRegionTypeId,
                                                    Object: 'LpuRegion',
                                                    showOpenerOnlyLpuRegions: 1
                                                },
                                                callback: function() {
                                                    form.findById('PCAPLpuRegion_id').setValue(form.findById('PCAPLpuRegion_id').getValue());
                                                }
                                            });
                                        }
                                    }
                                },
                                tabIndex: 2104,
                                width: 200,
                                xtype : "swlpuregiontypecombo"
                            },
                            {
                                allowBlank: false,
                                displayField: 'LpuRegion_Name',
                                fieldLabel: lang['uchastok'],
                                forceSelection: true,
                                hiddenName: 'PCAP_LpuRegion_id',
                                id: 'PCAPLpuRegion_id',
                                listeners: {
                                    'blur': function(combo) {

                                        if (combo.getRawValue()=='')
                                            combo.clearValue();
                                    },
                                    'change': function(combo, lpuRegionId) {

                                        var lpu_region_type_id = 0;
                                        combo.getStore().each(
                                            function( record ) {
                                                if ( record.data.LpuRegion_id == lpuRegionId )
                                                {
                                                    lpu_region_type_id = record.data.LpuRegionType_id;
                                                    return true;
                                                }
                                            }
                                        );
                                    }
                                },
                                minChars: 1,
                                mode: 'local',
                                queryDelay: 1,
                                setValue: function(v) {
                                    var text = v;
                                    if(this.valueField){
                                        var r = this.findRecord(this.valueField, v);
                                        if(r){
                                            text = r.data[this.displayField];
                                            if ( !(String(r.data['LpuRegion_Descr']).toUpperCase() == "NULL" || String(r.data['LpuRegion_Descr']) == "") )
                                            {
                                                if (r.data['LpuRegion_Descr']) {
                                                    text = text + ' ( '+ r.data['LpuRegion_Descr'] + ' )';
                                                }
                                            }
                                        } else if(this.valueNotFoundText !== undefined){
                                            text = this.valueNotFoundText;
                                        }
                                    }
                                    this.lastSelectionText = text;
                                    if(this.hiddenField){
                                        this.hiddenField.value = v;
                                    }
                                    Ext.form.ComboBox.superclass.setValue.call(this, text);
                                    this.value = v;

                                },
                                store: new Ext.data.Store({
                                    autoLoad: false,
                                    reader: new Ext.data.JsonReader({
                                        id: 'LpuRegion_id'
                                    }, [
                                        {name: 'LpuRegion_Name', mapping: 'LpuRegion_Name'},
                                        {name: 'LpuRegion_id', mapping: 'LpuRegion_id'},
                                        {name: 'LpuRegion_Descr', mapping: 'LpuRegion_Descr'},
                                        {name: 'LpuRegionType_id', mapping: 'LpuRegionType_id'},
                                        {name: 'LpuRegionType_Name', mapping: 'LpuRegionType_Name'}
                                    ]),
                                    url: C_LPUREGION_LIST
                                }),
                                tabIndex: 2106,
                                tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_Name} {[ (!values.LpuRegion_Descr || String(values.LpuRegion_Descr).toUpperCase() == "NULL" || String(values.LpuRegion_Descr) == "") ? "" : "( "+ values.LpuRegion_Descr +" )"]}</div></tpl>',
                                triggerAction: 'all',
                                typeAhead: true,
                                typeAheadDelay: 1,
                                valueField: 'LpuRegion_id',
                                width : 200,
                                xtype: 'combo'
                            },
                            {
                                allowBlank: true,
                                displayField: 'LpuRegion_FapName',
                                fieldLabel: lang['fap_uchastok'],
                                forceSelection: true,
                                hiddenName: 'LpuRegion_Fapid',
                                hidden: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']),//getRegionNick() != 'perm',
                                hideLabel: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']),//getRegionNick() != 'perm',
                                id: 'PCAPW_LpuRegion_Fapid',
                                minChars: 1,
                                mode: 'local',
                                queryDelay: 1,
                                setValue: function(v) {
                                    var text = v;
                                    if(this.valueField){
                                        var r = this.findRecord(this.valueField, v);
                                        if(r){
                                            text = r.data[this.displayField];
                                            if ( !(String(r.data['LpuRegion_FapDescr']).toUpperCase() == "NULL" || String(r.data['LpuRegion_FapDescr']) == "") )
                                            {
                                                if (r.data['LpuRegion_FapDescr']) {
                                                    text = text + ' ( '+ r.data['LpuRegion_FapDescr'] + ' )';
                                                }
                                            }
                                        } else if(this.valueNotFoundText !== undefined){
                                            text = this.valueNotFoundText;
                                        }
                                    }
                                    this.lastSelectionText = text;
                                    if(this.hiddenField){
                                        this.hiddenField.value = v;
                                    }
                                    Ext.form.ComboBox.superclass.setValue.call(this, text);
                                    this.value = v;
                                },
                                lastQuery: '',
                                store: new Ext.data.Store({
                                    autoLoad: false,
                                    reader: new Ext.data.JsonReader({
                                        id: 'LpuRegion_Fapid'
                                    }, [
                                        {name: 'LpuRegion_FapName', mapping: 'LpuRegion_FapName'},
                                        {name: 'LpuRegion_Fapid', mapping: 'LpuRegion_Fapid'},
                                        {name: 'LpuRegion_FapDescr', mapping: 'LpuRegion_FapDescr'}
                                    ]),
                                    url: '/?c=LpuRegion&m=getLpuRegionListFeld'
                                }),
                                tabIndex: 2106,
                                tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_FapName}</div></tpl>',
                                triggerAction: 'all',
                                typeAhead: true,
                                typeAheadDelay: 1,
                                valueField: 'LpuRegion_Fapid',
                                width : 200,
                                xtype: 'combo'
                            },
                            /*{
                                xtype:'checkbox',
                                hideLabel:true,
                                name:'PCAPIsAttachCondit',
                                id: 'PCAP_IsAttachCondit',
                                boxLabel: lang['uslovnoe_prikreplenie'],
                                disabled: false,
                                checked: false
                            },
                            {
                                xtype:'checkbox',
                                hideLabel:true,
                                name:'PCAPPersonCardAttach',
                                id: 'PCAP_PersonCardAttach',
                                boxLabel: lang['zayavlenie'],
                                disabled: false,
                                checked: false
                            },*/
							{
								xtype: 'fieldset',
								labelAlign: 'left',
								height: 50,
								style:'padding: 0px 3px 0px 6px;',
								id: 'type_fieldset',
								title: '',
								items: [
									{
										xtype: 'radio',
										hideLabel: true,
										boxLabel: lang['uslovnoe_prikreplenie'],
										name: 'PC_type',
										inputValue: 0
									}, {
										xtype: 'radio',
										hideLabel: true,
										boxLabel: lang['zayavlenie'],
										inputValue: 1,
										name: 'PC_type'
									}
								]
							}
                        ]
                });

            Ext.apply(this,
                {
                    region: 'center',
                    layout: 'form',
                    buttons:
                        [{
                            text: lang['ustanovit'],
                            id: 'lsqefOk',
                            iconCls: 'ok16',
                            handler: function() {
								this.ownerCt.submit();
                            }
                        },{
                            text: '-'
                        },
                            {
                                iconCls: 'cancel16',
                                text: BTN_FRMCLOSE,
                                handler: function() {this.hide();}.createDelegate(this)
                            }],
                    items:
                        [
                            form.mainPanel
                        ]

                });
            sw.Promed.swPersonCardAutoParamsWindow.superclass.initComponent.apply(this, arguments);
        }
    });