/**
 * swMorbusNephroDispWindow - Динамическое наблюдение.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Nephro
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Alexander Permyakov
 * @version      11.2014
 */
sw.Promed.swMorbusNephroDispWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	winTitle: lang['dinamicheskoe_nablyudenie'],
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	modal: true,
	doSave: function() 
	{
        var me = this;
		if ( me.formStatus == 'save' ) {
			return false;
		}

        me.formStatus = 'save';
		
		var form = me.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    me.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(me.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		
		var params = {};
		var data = {};

		switch ( this.formMode ) {
			case 'local':
				data.BaseData = {
					'MorbusNephroDisp_id': base_form.findField('MorbusNephroDisp_id').getValue(),
					'MorbusNephroDisp_Date': base_form.findField('MorbusNephroDisp_Date').getValue(),
                    'Rate_id': base_form.findField('Rate_id').getValue(),
					'Rate_ValueStr': base_form.findField('Rate_ValueStr').getValue(),
                    'RateType_id': base_form.findField('RateType_id').getValue(),
					'RateType_Name': base_form.findField('RateType_id').getRawValue()
				};
                me.callback(data);
                me.formStatus = 'edit';
				loadMask.hide();
                me.hide();
			break;
			case 'remote':
			base_form.findField('MorbusNephroDisp_EndDate').setDisabled(false);
				base_form.submit({
					failure: function(result_form, action) {
                        me.formStatus = 'edit';
						loadMask.hide();
						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
							}
						}
						base_form.findField('MorbusNephroDisp_EndDate').setDisabled(true);
					},
					params: params,
					success: function(result_form, action) {
                        me.formStatus = 'edit';
						loadMask.hide();
						if ( action.result ) {
							if ( action.result.MorbusNephroDisp_id > 0 ) {
								base_form.findField('MorbusNephroDisp_id').setValue(action.result.MorbusNephroDisp_id);

								data.BaseData = {
									'MorbusNephroDisp_id': base_form.findField('MorbusNephroDisp_id').getValue(),
									'MorbusNephroDisp_Date': base_form.findField('MorbusNephroDisp_Date').getValue(),
                                    'Rate_id': base_form.findField('Rate_id').getValue(),
									'Rate_ValueStr': base_form.findField('Rate_ValueStr').getValue(),
									'RateType_id': base_form.findField('RateType_id').getValue(),
									'RateType_Name': base_form.findField('RateType_id').getRawValue()
								};

								if(getRegionNick() == 'ufa' && base_form.findField('RateType_id').getValue() == 109)  { //#135648
									me.saveCkdEpiResult(data);
								} else {
									me.callback(data);
									me.hide();
								}
							} else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
								}
							}
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}
				});
			break;

			default:
				loadMask.hide();
			break;
			
		}
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	show: function() 
	{
		sw.Promed.swMorbusNephroDispWindow.superclass.show.apply(this, arguments);
		
		var that = this;
		if (!arguments[0] || !arguments[0].formParams) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
                    that.hide();
				}
			});
		}
		this.focus();
		
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.formMode = 'remote';
		this.formStatus = 'edit';
        this.action = arguments[0].action || null;
        this.MorbusNephroDisp_id = arguments[0].MorbusNephroDisp_id || null;
        this.owner = arguments[0].owner || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.onHide = arguments[0].onHide || Ext.emptyFn;
		if ( arguments[0].formMode
            && typeof arguments[0].formMode == 'string'
            && arguments[0].formMode.inlist([ 'local', 'remote' ])
        ) {
			this.formMode = arguments[0].formMode;
		}
		if (!this.action) {
            if ( ( this.MorbusNephroDisp_id ) && ( this.MorbusNephroDisp_id > 0 ) )
                this.action = "edit";
            else
                this.action = "add";
		}
		
		base_form.setValues(arguments[0].formParams);
		
		var rateTypeField = base_form.findField('RateType_id');
		
		this.getLoadMask().show();
		switch (this.action) 
		{
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
		if (this.action != 'add' && this.formMode == 'remote') {
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					that.getLoadMask().hide();
				},
				params:{
					MorbusNephroDisp_id: that.MorbusNephroDisp_id
				},
				success: function (response) {
					that.getLoadMask().hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (!result[0]) { return false; }
					base_form.setValues(result[0]);
					base_form.findField('MorbusNephroDisp_Date').focus(true,200);

					if(getRegionNick() == 'ufa') //#135648
						rateTypeField.fireEvent('change', rateTypeField, rateTypeField.getValue());
				},
				url:'/?c=MorbusNephro&m=doLoadEditFormMorbusNephroDisp'
			});				
		} else {
			this.getLoadMask().hide();
			base_form.findField('MorbusNephroDisp_Date').focus(true,200);
		}

		if(getRegionNick() == 'ufa') { //#135648
			rateTypeField.getStore().getTotalCount() ? null : rateTypeField.getStore().load();

			rateTypeField.getStore().on('load',function(){
				rateTypeField.setValue(rateTypeField.getValue());
			}); 

			that.setVisibilityCkdEpi(false);
			rateTypeField.addListener('change', function(combo, newValue, oldValue) {
				that.setVisibilityCkdEpi(newValue == 109);
			});

			var calcCkdEpi = function(){
				isCreatinine = rateTypeField.getValue() == 109;
				if(isCreatinine)
					this.calcCkdEpi();
			}.createDelegate(this);

			base_form.findField('Unit_id').addListener('change', calcCkdEpi);
			base_form.findField('CkdEpi_race').addListener('change', calcCkdEpi);
			base_form.findField('Rate_ValueStr').addListener('change', function(combo,n,o){
				isCreatinine = rateTypeField.getValue() == 109;
				value = combo.getValue();
				value = value.replace(/,/,".");
				combo.setValue(value);
				if(!isCreatinine) return;
				that.calcCkdEpi();
			})

		}
		base_form.findField('MorbusNephroDisp_EndDate').setDisabled(true);
	},	
	initComponent: function() 
	{
		var CkdEpiCalcPanel = {};

		if(getRegionNick() == 'ufa') { //#135648
			CkdEpiCalcPanel = new Ext.form.FieldSet({
				title: langs('Расчет СКФ'),
				id: this.id + 'CkdEpi',
				bodyStyle: 'padding: 5px',
				autoHeight: true,
				border: true,
				hidden: true,
				layout: 'form',
				labelWidth: 150,
				items:[
					{
						name: 'NephroCkdEpi_id',
						xtype: 'hidden',
					},{
						name: 'Person_Age',
						xtype: 'hidden',
					}, {
						name: 'Person_Sex',
						xtype: 'hidden',
					}, {
						name: 'NephroCRIType_id',
						xtype: 'hidden'
					}, {
						name: 'CkdEpi_race',
						fieldLabel: 'Раса',
						disabled: true,
						xtype: 'swbaselocalcombo',
						valueField: 'id',
						displayField: 'name',
						store: new Ext.data.SimpleStore({
							fields: [
								{ name: 'id', type: 'int' },
								{ name: 'name', type: 'string' },
							],
							data: [
								[ 1, 'Белые и остальные'],
								[ 2, 'Чернокожие'],
								[ 3, 'Азиаты'],
								[ 4, 'Испаноамериканцы и индейцы']
							]
						}),
						listeners: {
							render: function() {
								this.setValue(1);
							}
						}
					}, {
						fieldLabel: 'Результат расчета СКФ',
						name: 'CkdEpi_value',
						xtype: 'numberfield',
						disabled:true
					}
				]})
		}


		var me = this;
		this.FormPanel = new Ext.form.FormPanel(
		{	
			autoScroll: true,
			frame: true,
			region: 'north',
			bodyStyle: 'padding: 5px',
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 120,
			items: 
			[{
				name: 'MorbusNephroDisp_id',
				xtype: 'hidden'
			}, {
				name: 'MorbusNephro_id',
				xtype: 'hidden'
			}, {
                name: 'Rate_id',
                xtype: 'hidden'
            }, {
				fieldLabel: lang['pokazatel'],
				isDinamic: 2,
				allowBlank: false,
				anchor:'100%',
				xtype: 'swnephroratetypecombo',
				listeners: {
					change: function(field, value) {
						var baseForm = me.FormPanel.getForm();
						var begDateValue = baseForm.findField('MorbusNephroDisp_Date').value;
						if (begDateValue != null) {
							var newEndDateValue = me.calcEndDateValue(begDateValue, field.value);
							baseForm.findField('MorbusNephroDisp_EndDate').setValue(newEndDateValue);
						}
					}
				}
			}, {
				fieldLabel: langs('Дата с'),
				name: 'MorbusNephroDisp_Date',
				allowBlank: false,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				listeners: {
					change: function(field, value) {
						var baseForm = me.FormPanel.getForm();
						var rateTypeValue = baseForm.findField('RateType_id').value;
						if (rateTypeValue != null) {
							var newEndDateValue = me.calcEndDateValue(field.value, rateTypeValue);
							baseForm.findField('MorbusNephroDisp_EndDate').setValue(newEndDateValue);
						}
					}
				}
			}, {
				fieldLabel: langs('Дата по'),
				name: 'MorbusNephroDisp_EndDate',
				allowBlank: true,
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				listeners: {
					render: function() {
						if (getRegionNick() != 'ufa') {
							this.setDisabled(true);
							this.setContainerVisible(false);
						}
					}
				}
			}, {
				name: 'Rate_ValueStr',
                allowBlank: false,
				fieldLabel: lang['znachenie'],
				width: 150,
				maxLength: 50,
				xtype: 'textfield'
			},  {
				hiddenName: 'Unit_id',
				name: 'Unit_id',
				fieldLabel: langs('Единица измерения'),
				disabled: true,
				xtype: 'swbaselocalcombo',
				valueField: 'id',
				displayField: 'name',
				/*store: new Ext.db.AdapterStore({
					autoLoad: this.autoLoad,
					dbFile: 'Promed.db',
					fields: [
						{name: 'Lpu_id', mapping: 'Lpu_id'},
						{name: 'Org_id', mapping: 'Org_id'}
					],
					key: 'Lpu_id',
					listeners: {
						'load': function(store) {
							this.setValue(this.getValue());
						}.createDelegate(this)
			}, 
					tableName: 'Lpu'
				})*/
				store: new Ext.data.SimpleStore({
					fields: [
						{ name: 'id', type: 'int' },
						{ name: 'name', type: 'string' },
					],
					data: [
						[ 1, 'ммоль/л'],
						[ 2, 'мкмоль/л'],
						[ 3, 'мг/дл'],
						[ 4, 'мг/100мл'],
						[ 5, 'мг%'],
						[ 6, 'мг/л'],
						[ 7, 'мкг/мл'],
						[ 8, 'г/л'],
						[ 9, '%'],
						[ 10, 'мкг/л'],
						[ 11, 'пг/мл']
					]
				})
			},
			CkdEpiCalcPanel
			
			],
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{name: 'MorbusNephroDisp_id'},
                {name: 'MorbusNephro_id'},
                {name: 'Rate_id'},
				{name: 'MorbusNephroDisp_Date'},
				{name: 'MorbusNephroDisp_EndDate'},
				{name: 'Rate_ValueStr'},
				{name: 'RateType_id'}
			]),
			url: '/?c=MorbusNephro&m=doSaveMorbusNephroDisp'
		});
		Ext.apply(this, 
		{
			buttons: 
			[{
				handler: function() {
                    me.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
                    me.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swMorbusNephroDispWindow.superclass.initComponent.apply(this, arguments);
	},
	setVisibilityCkdEpi: function(visibled) {
		var calcPanel = this.findById( this.id + 'CkdEpi');
		visibled ? calcPanel.show() : calcPanel.hide();
		var base_form = this.FormPanel.getForm();
		
		var CkdEpi_race = base_form.findField('CkdEpi_race');

		CkdEpi_race.setDisabled(!visibled);
		CkdEpi_race.setAllowBlank(!visibled);

		this.doLayout();
		this.syncSize();
	},
	/**
	 * Функция для расчета СКФ, на основе которого проставляется "Стадия ХБП"
	 * в окне "Запись регистра по нефрологии"
	 */
	calcCkdEpi: function(){

		var form = this.FormPanel.getForm();
		var age = form.findField('Person_Age').getValue();
		var sex = form.findField('Person_Sex').getValue();
		var race = form.findField('CkdEpi_race').getValue();
		var unit = form.findField('Unit_id').getValue();
		var value = form.findField('Rate_ValueStr').getValue();
		var ckdEpiValueField = form.findField('CkdEpi_value');
		var NephroCRITypeField = form.findField('NephroCRIType_id');

		if(isNaN(value)) {
			sw.swMsg.alert(langs('Внимание'),langs('Для рассчета СКФ введите в поле "Значение" число. <br> Не целые числа записываются через символ точка "."'));
			form.findField('Rate_ValueStr').setValue(null);
			return;
		}

		if(!unit || !race) return;

		// конвертируем в мкмоль/л
		switch (unit) {
			case 1:
				value = value * 1000;
				break;
			case 3:
			case 4:
			case 5:
				value = value * 88.4017;
				break;
			case 6:
			case 7:
				value = value * 8.8402;
				break;
		}

		//(Cr) умножаем на коэффициент креатинина
		value = value * 0.011312;

		var sex_koef = {
			1: 0.9,
			2: 0.7
		};

		var value_sex_koef;
		if(sex == 2)
			value_sex_koef = value <= 0.7 ? -0.328 : -1.210;
		else
			value_sex_koef = value <= 0.9 ? -0.412 : -1.210;

		var race_koef = {
			1: { 1: 141, 2: 144 },
			2: { 1: 164, 2: 167 },
			3: { 1: 149, 2: 151 },
			4: { 1: 143, 2: 145 },
		}

		//(коэф зависящий от расы и пола) * 0.993^(возраст) * (Cr/(половой коэф))^(коэф зависящий от Cr и пола)
		$result = Math.round( race_koef[race][sex] * Math.pow(0.993, age) * Math.pow(value/sex_koef[sex], value_sex_koef) );
		ckdEpiValueField.setValue($result);

		if( !isNaN($result) )
		if( $result >= 90 )
			NephroCRITypeField.setValue(1);

		else if ( $result <= 89 && $result >= 60 )
			NephroCRITypeField.setValue(2);

		else if ( $result <= 59 && $result >= 45 )
			NephroCRITypeField.setValue(3);

		else if ( $result <= 44 && $result >= 30 )
			NephroCRITypeField.setValue(4);

		else if ( $result <= 29 && $result >= 15)
			NephroCRITypeField.setValue(5);

		else if ( $result < 15 )
			NephroCRITypeField.setValue(6);

		return $result;
	},
	/**
	 * Сохранение результатов расчета СКФ
	 */
	saveCkdEpiResult: function(data) {
		var wnd = this;
		var form = this.FormPanel.getForm();
		var params = new Object();

		params['NephroCkdEpi_id']       = form.findField('NephroCkdEpi_id').getValue();
		params['NephroCkdEpi_value']    = form.findField('CkdEpi_value').getValue();
		if (getRegionNick() == 'ufa') {
			params['CreatinineUnitType_id'] = form.findField('Unit_id').getValue();
		}
		params['MorbusNephroRate_id']   = form.findField('MorbusNephroDisp_id').getValue();
		params['MorbusNephro_id']       = form.findField('MorbusNephro_id').getValue();

		wnd.getLoadMask("Сохранение результата расчета СКФ").show();

		Ext.Ajax.request({
			url: '/?c=MorbusNephro&m=saveCkdEpiResult',
			params: params,
			callback: function (options, success, response) {
				wnd.getLoadMask().hide();
				if (success) {
					if ( response.responseText.length > 0 ) {
						var resp_obj = Ext.util.JSON.decode(response.responseText);
						result = resp_obj[0];
						if (result) {
							if(!result.isLastRate)
								wnd.onHide = Ext.emptyFn;

							form = wnd.FormPanel.getForm();
							rateType_id = form.findField('RateType_id').getValue();
							if(rateType_id != 109) return;

							params = new Object();
							params.MorbusNephroRate_rateDT = form.findField('MorbusNephroDisp_Date').getValue().format('Y-m-d');
							params.MorbusNephro_id = form.findField('MorbusNephro_id').getValue();
							params.CRIType_id = form.findField('NephroCRIType_id').getValue();

							wnd.onHide(params);
							wnd.callback(data);
							wnd.hide();
						} else {
							sw.swMsg.alert('Ошибка', 'Не удалось сохранить результат расчета СКФ');
						}
					} else {
						sw.swMsg.alert('Ошибка сервера','Не удалось сохранить результат расчета СКФ');
					}
				} else {
					sw.swMsg.alert('Ошибка сервера','Не удалось сохранить результат расчета СКФ');
				}
			}
		})
	},
	/**
	 * Функция для расчета значения поля [Дата по]
	 */
	calcEndDateValue: function(begDataValue, rateTypeId) {
		var date = begDataValue.split('.');

		step = 0;
		switch (parseInt(rateTypeId)) {
			case 4:
			case 119:
			case 118:
			case 115:
				step = 1;
				break;
			case 123:
			case 203:
			case 204:
			case 205:
				step = 3;
			break;
			default: return null;
	}

		date[1] = parseInt(date[1]) + step;
		if (date[1] > 12) {
			date[1] -= 12;
			date[2] = parseInt(date[2]) + 1;
		}
		return (new Date(date[2], date[1] - 1, date[0]).format("d.m.Y"));
	}
});