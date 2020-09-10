/**
 * swEvnUslugaOnkoChemEditWindow - окно редактирования "Химиотерапевтическое лечение"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @version      06.2013
 * @comment
 */
sw.Promed.swEvnUslugaOnkoChemEditWindow = Ext.extend(sw.Promed.BaseForm, {
    winTitle: lang['himioterapevticheskoe_lechenie'],
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 700,
	height: 650,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var thas = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					thas.findById('EvnUslugaOnkoChemEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit();
		return true;		
	},
	submit: function() {
		var thas = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {};
		params.action = this.action;
		
		this.form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
                if (typeof thas.onSave == 'function') {
                    thas.onSave(action.result.EvnUslugaOnkoChem_id);
                    thas.form.findField('EvnUslugaOnkoChem_id').setValue(action.result.EvnUslugaOnkoChem_id);
                    thas.action = 'edit';
                    thas.setTitle(thas.winTitle +lang['_redaktirovanie']);
                } else {
                    thas.callback(thas.owner, action.result.EvnUslugaOnkoChem_id);
                    thas.hide();
                }
			}
		});
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
        form.MorbusOnkoDrugFrame.setReadOnly(d);
		form.buttons[0].setDisabled(d);
	},
    openMorbusOnkoDrugWindow: function(action) {
        if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
            return false;
        }

        if (getWnd('swMorbusOnkoDrugWindow').isVisible()) {
            getWnd('swMorbusOnkoDrugWindow').hide();
        }

        var thas = this;
        var win = this;
        var grid = this.MorbusOnkoDrugFrame.getGrid();
        var selected_record = grid.getSelectionModel().getSelected();
        var params = {
            EvnUsluga_setDT: this.form.findField('EvnUslugaOnkoChem_setDate').getValue(),
            EvnUsluga_disDT: this.form.findField('EvnUslugaOnkoChem_disDate').getValue()
        };
        params.action = action;
        params.callback = function(data) {
            //thas.MorbusOnkoDrugFrame.ViewActions.action_refresh.execute();
            grid.getStore().load({
                params: {Evn_id: data.Evn_id},
                globalFilters: {Evn_id: data.Evn_id}
            });
            win.onSaveDrug();
        };
        if (action == 'add') {
            this.onSave = null;
            var evn_id = thas.form.findField('EvnUslugaOnkoChem_id').getValue();
            if (evn_id) {
                params.formParams = {
                    MorbusOnko_id: thas.form.findField('MorbusOnko_id').getValue(),
                    Evn_id: evn_id,
                    MorbusOnkoDrug_begDT: thas.form.findField('EvnUslugaOnkoChem_setDate').getValue()
                };
                getWnd('swMorbusOnkoDrugWindow').show(params);
            } else {
                this.onSave = function(evn_id){
                    params.formParams = {
                        MorbusOnko_id: thas.form.findField('MorbusOnko_id').getValue(),
                        Evn_id: evn_id,
                        MorbusOnkoDrug_begDT: thas.form.findField('EvnUslugaOnkoChem_setDate').getValue()
                    };
                    getWnd('swMorbusOnkoDrugWindow').show(params);
                    thas.onSave = null;
                };
                this.doSave();
            }
        } else {
            if (!selected_record) {
                return false;
            }
            params.formParams = selected_record.data;
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selected_record));
            };
            getWnd('swMorbusOnkoDrugWindow').show(params);
        }
        return true;
    },
	disabledDatePeriods: null,
	setAllowedDates: function() {
		var that = this;
		var set_dt_field = that.form.findField('EvnUslugaOnkoChem_setDate');
		var morbus_id = that.form.findField('Morbus_id').getValue();

		that.disabledDatePeriods = null;

		if (morbus_id) {
			var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
			loadMask.show();
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					loadMask.hide();
				},
				params: {
					Morbus_id: morbus_id
				},
				method: 'POST',
				success: function (response) {
					loadMask.hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && Ext.isArray(result[0].disabledDatePeriods) && result[0].disabledDatePeriods.length > 0) {
						that.disabledDatePeriods = result[0].disabledDatePeriods;
						// в поле set_dt_field даём выбирать только те, что подходят к одному из периодов
						var disabledDates = [];
						for(var k in that.disabledDatePeriods) {
							if (typeof that.disabledDatePeriods[k] == 'object') {
								for (var k2 in that.disabledDatePeriods[k]) {
									if (typeof that.disabledDatePeriods[k][k2] == 'string') {
										disabledDates.push(that.disabledDatePeriods[k][k2]);
									}
								}
							}
						}
						set_dt_field.setAllowedDates(disabledDates);
						that.setAllowedDatesForDisField();
					} else {
						set_dt_field.setAllowedDates(null);
						that.setAllowedDatesForDisField();
					}
				},
				url:'/?c=MorbusOnkoSpecifics&m=getMorbusOnkoSpecTreatDisabledDates'
			});
		} else {
			set_dt_field.setAllowedDates(null);
			that.setAllowedDatesForDisField();
		}
	},
	setAllowedDatesForDisField: function() {
		var that = this;
		var set_dt_field = that.form.findField('EvnUslugaOnkoChem_setDate');
		var set_dt_value = null;
		if (!Ext.isEmpty(set_dt_field.getValue())) {
			set_dt_value = set_dt_field.getValue().format('d.m.Y');
		}
		var dis_dt_field = that.form.findField('EvnUslugaOnkoChem_disDate');

		dis_dt_field.setAllowedDates(null);

		if (Ext.isArray(that.disabledDatePeriods) && that.disabledDatePeriods.length > 0) {
			// в поле dis_dt_field даём выбирать только те, что подходят к одному из периодов соответствующим полю set_dt
			var disabledDates = [];
			for(var k in that.disabledDatePeriods) {
				if (typeof that.disabledDatePeriods[k] == 'object') {
					if (Ext.isEmpty(set_dt_value) || set_dt_value.inlist(that.disabledDatePeriods[k])) {
						for (var k2 in that.disabledDatePeriods[k]) {
							if (typeof that.disabledDatePeriods[k][k2] == 'string') {
								disabledDates.push(that.disabledDatePeriods[k][k2]);
							}
						}
					}
				}
			}
			dis_dt_field.setAllowedDates(disabledDates);
		}
	},
	show: function() {
        var thas = this;
		sw.Promed.swEvnUslugaOnkoChemEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.EvnUslugaOnkoChem_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { thas.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSaveDrug && typeof arguments[0].onSaveDrug == 'function' ) {
			this.onSaveDrug = arguments[0].onSaveDrug;
		} else {
			this.onSaveDrug = function() {};
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].EvnUslugaOnkoChem_id ) {
			this.EvnUslugaOnkoChem_id = arguments[0].EvnUslugaOnkoChem_id;
		}
		this.form.reset();
        var grid = this.MorbusOnkoDrugFrame.getGrid();
        grid.getStore().removeAll();

		switch (arguments[0].action) {
			case 'add':
				this.setTitle(this.winTitle +lang['_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(this.winTitle +lang['_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(this.winTitle +lang['_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				thas.form.setValues(arguments[0].formParams);

				thas.form.findField('EvnUslugaOnkoChem_setTime').setValue('00:00');
				thas.form.findField('EvnUslugaOnkoChem_disTime').setValue('00:00');

				thas.InformationPanel.load({
					Person_id: arguments[0].formParams.Person_id
				});
				//thas.form.findField('EvnUslugaOnkoChem_setDate').setValue(getGlobalOptions().date);
				loadMask.hide();
				thas.setAllowedDates();
			break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						thas.hide();
					},
					params:{
						EvnUslugaOnkoChem_id: thas.EvnUslugaOnkoChem_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
                            thas.form.setValues(result[0]);
                            thas.InformationPanel.load({
                                Person_id: result[0].Person_id
                            });
                            loadMask.hide();
                            grid.getStore().load({
                                params: {Evn_id: result[0].EvnUslugaOnkoChem_id},
                                globalFilters: {Evn_id: result[0].EvnUslugaOnkoChem_id}
                            });
							thas.setAllowedDates();
                        }
					},
					url:'/?c=EvnUslugaOnkoChem&m=load'
				});				
			break;	
		}
        return true;
	},
	initComponent: function() {
        var thas = this;

		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});

        this.MorbusOnkoDrugFrame = new sw.Promed.ViewFrame({
            id: 'OnkoChemDrug',
            title: lang['2_preparat'],
            collapsible: true,
            actions: [
                {name: 'action_add', handler: function() {
                    thas.openMorbusOnkoDrugWindow('add');
                }},
                {name: 'action_edit', handler: function() {
                    thas.openMorbusOnkoDrugWindow('edit');
                }},
                {name: 'action_view', handler: function() {
                    thas.openMorbusOnkoDrugWindow('view');
                }},
                {name: 'action_delete'},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MorbusOnkoDrug&m=readList',
            paging: false,
            object: 'MorbusOnkoDrug',
            obj_isEvn: false,
            stringfields: [
                {name: 'MorbusOnkoDrug_id', type: 'int', header: 'ID', key: true},
                {name: 'MorbusOnko_id', type: 'int', hidden: true},
                {name: 'Evn_id', type: 'int', hidden: true},
                {name: 'MorbusOnkoDrug_begDT', type: 'date',header: 'Қабылдау басталған күн (Дата начала приема)'},
                {name: 'MorbusOnkoDrug_endDT', type: 'date',header: 'Қабылдау аяқталған күн (Дата окончания приема)'},
                {name: 'OnkoDrug_Name', type: 'string', header: 'Препараттың атауы (Наименование препарата)', id: 'autoexpand'},
                {name: 'OnkoDrugUnitType_Name', type: 'string', header: 'Өлшем бiрлiгi (Ед. измерения)', width: 100},
                {name: 'MorbusOnkoDrug_SumDose', type: 'string', header: 'Қосынды доза (Суммарная доза)', width: 100}
            ],
            toolbar: true
        });

		var form = new Ext.Panel({
			autoHeight: true,
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: false,
			region: 'center',
			items: [{
                title: lang['1_lechenie'],
				xtype: 'form',
				autoHeight: true,
				id: 'EvnUslugaOnkoChemEditForm',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: false,
				labelWidth: 200,
				collapsible: true,
				labelAlign: 'right',
				region: 'center',
				url:'/?c=EvnUslugaOnkoChem&m=save',
				items: [{
					name: 'EvnUslugaOnkoChem_id',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaOnkoChem_pid',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaOnkoChem_setTime',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaOnkoChem_disTime',
					xtype: 'hidden'
                }, {
                    name: 'MorbusOnko_id',
                    xtype: 'hidden'
                }, {
                    name: 'Morbus_id',
                    xtype: 'hidden'
				}, {
					name: 'Server_id',
					xtype: 'hidden'
                }, {
                    name: 'Person_id',
                    xtype: 'hidden'
                }, {
                    name: 'PersonEvn_id',
                    xtype: 'hidden'
				}, {
					fieldLabel: lang['data_nachala'],
					name: 'EvnUslugaOnkoChem_setDate',
					listeners: {
						'change': function(field, newValue) {
							thas.setAllowedDatesForDisField();
						}
					},
                    allowBlank: false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: lang['data_okonchaniya'],
					name: 'EvnUslugaOnkoChem_disDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: 'Химиятерапия түрі (Вид химиотерапии)',//lang['vid_himioterapii'],
					hiddenName: 'OnkoUslugaChemKindType_id',
					xtype: 'swcommonsprlikecombo',
					allowBlank: false,
					sortField:'OnkoUslugaChemKindType_Code',
					comboSubject: 'OnkoUslugaChemKindType',
					width: 300
				}, {
					fieldLabel: 'үшін химиятерапия бойынша емдеу сатылары (Этапы  лечения по химиотерапии)',//lang['vid_himioterapii'],
					hiddenName: 'OnkoUslugaChemStageType_id',
					xtype: 'swcommonsprlikecombo',
					sortField:'OnkoUslugaChemStageType_Code',
					comboSubject: 'OnkoUslugaChemStageType',
					width: 300
				}, {
					fieldLabel: 'Химиятерапия схемасы (Схема  химиотерапии)',//lang['vid_himioterapii'],
					name: 'EvnUslugaOnkoChem_Scheme',
					xtype: 'textfield',
					width: 300
				}]
			}, this.MorbusOnkoDrugFrame],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
                {name: 'EvnUslugaOnkoChem_id'},
                {name: 'EvnUslugaOnkoChem_pid'},
				{name: 'Server_id'},
                {name: 'Person_id'},
                {name: 'PersonEvn_id'},
                {name: 'MorbusOnko_id'},
                {name: 'Morbus_id'},
				{name: 'EvnUslugaOnkoChem_setDate'},
				{name: 'EvnUslugaOnkoChem_setTime'},
                {name: 'EvnUslugaOnkoChem_disDate'},
                {name: 'EvnUslugaOnkoChem_disTime'},
				{name: 'Lpu_uid'},
				{name: 'OnkoUslugaChemKindType_id'}, 
				{name: 'OnkoUslugaChemStageType_id'}, 
				{name: 'EvnUslugaOnkoChem_Scheme'},
				{name: 'OnkoTreatType_id'}
			]),
			url: '/?c=EvnUslugaOnkoChem&m=save'
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.InformationPanel,form]
		});
		sw.Promed.swEvnUslugaOnkoChemEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('EvnUslugaOnkoChemEditForm').getForm();
	}	
});