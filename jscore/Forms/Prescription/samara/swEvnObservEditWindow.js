/**
* swEvnObservEditWindow - окно Выполнение назначения: Наблюдение
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-05.10.2011
* @comment      Префикс для id компонентов EOBSEF (EvnObservEditForm)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnObservEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnObservEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnObservEditWindow.js',
	saveObject:null,
	buttonAlign: 'left',
	EvnObserv_pid:null,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();
		
		var params = new Object();
		var evnObservDataList = this.getSaveObject();
		
		if(evnObservDataList==false){
			return false;
		}
        //проверяем, чтобы было заполнено хотя бы одно поле
        if ( !base_form.isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                    this.FormPanel.getFirstInvalidEl().focus(true);
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: 'Не верно заполнены поля!',
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

		params.evnObservDataList = Ext.util.JSON.encode(evnObservDataList);

		/*if ( base_form.findField('ObservTimeType_id').disabled ) {
			params.ObservTimeType_id = base_form.findField('ObservTimeType_id').getValue();
		}*/
        if (base_form.findField('EvnObserv_setDate')) {
            params.EvnObserv_setDate = base_form.findField('EvnObserv_setDate').getRawValue();
        }

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if (action.result.isExec) {
                        var data = {};
                        var evnObservData = {};
                        evnObservData.EvnObserv_id = action.result.EvnObserv_id;
                        data.evnObservData = evnObservData;
                        this.callback(data);
                    }
					this.hide();
				}
				else {
					sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	formStatus: 'edit',
	height: 500,
	id: 'EvnObservEditWindow',
	setEnabled: function(values){
		this.setDisable();
		var base_form = this.FormPanel.getForm();
		for(var val in values){
			base_form.findField('EvnPrescrObserv_id_'+val).setValue(values[val].EvnPrescrObserv_id)
			base_form.findField('EvnObserv_id_'+val).setValue(values[val].EvnObserv_id)
			for(var par in values[val])
			{
				switch(Number(par)){
					
					case 1:case 2:case 3:case 4:
						base_form.findField('val'+val+'_'+par).enable();
						base_form.findField('val'+val+'_'+par).setValue(values[val][par].value);
						base_form.findField('val'+val+'_'+par).isMain =values[val][par].isMain;
						//if(values[val][par].value>0)base_form.findField('val'+val+'_'+par).disable();
						break;
					case 5:case 6:case 7:case 8:case 9:case 10:case 11: case 12:  case 13:  case 14:
						base_form.findField('val0_'+par).enable();
						base_form.findField('val0_'+par).setValue(values[val][par].value);
						base_form.findField('val0_'+par).isMain = values[val][par].isMain;
						//if(values[val][par].value>0)base_form.findField('val0_'+par).disable();
						break;
				}
				
			}
		}
	},
	getSaveObject: function(){
		var base_form = this.FormPanel.getForm();
		var data={};
		var flag = false;//флаг на проверку заполнености хотя бы одного поля.
		var values = this.saveObject;
		var i=0;
		for(var val in values){
			var s =0;
			data[i]={};
			data[i].EvnObserv_pid = base_form.findField('EvnPrescrObserv_id_'+val).getValue();
			//data[i].EvnObserv_pid = base_form.findField('EvnObserv_pid').getValue();
			data[i].EvnObserv_id = base_form.findField('EvnObserv_id_'+val).getValue();
			data[i].EvnObserv_setDate = Ext.util.Format.date(base_form.findField('EvnPrescr_setDT').getValue(), 'm.d.Y');
			data[i].ObservTimeType_id  = val;
			data[i].dataList={};
			base_form.items.each(function(rec){
				if(rec.name.indexOf('val'+val)==0&&rec.isMain==0&&rec.getValue()>0){
					data[i].dataList[s]={};
					data[i].dataList[s].EvnObservData_id= null;
					data[i].dataList[s].EvnObservData_Value= rec.getValue();
					data[i].dataList[s].ObservParamType_id= rec.name.substr(-1);
					data[i].dataList[s].isMain = false;
					if(rec.value>0){flag=true;}
					s++;
				}
				if(rec.name.indexOf('val0')==0&&rec.isMain==0&&rec.getValue()>0){
					data[i].dataList[s]={};
					data[i].dataList[s].EvnObservData_id= null;
					data[i].dataList[s].EvnObservData_Value= rec.getValue();
					data[i].dataList[s].ObservParamType_id= (rec.name.length==6)?rec.name.substr(-1):rec.name.substr(-2);
					data[i].dataList[s].isMain = false;
					if(rec.value>0){flag=true;}
					s++;
				}
			})
			//alert(data[i].EvnObserv_setDate)
			for(var par in values[val])
			{
				if(Number(par)>0){
				data[i].dataList[s]={};
				//var preasure = base_form.findField('val'+val+'_1').getValue().split('/');
				switch(Number(par)){
					
					case 1:case 2:case 3:case 4:
						if(Number(par)>0){
							data[i].dataList[s].EvnObservData_id= values[val][par].EvnObservData_id;
							data[i].dataList[s].EvnObservData_Value= base_form.findField('val'+val+'_'+par).getValue();
							//base_form.findField('val'+val+'_'+par).disable();
							data[i].dataList[s].ObservParamType_id= par;
							data[i].dataList[s].isMain = (values[val][par].isMain==1)?false:true;
							if(!flag){flag=(base_form.findField('val'+val+'_'+par).getValue()>0)}
						}
						break;
					case 5:case 6:case 7:case 8:case 9:case 10:case 11: case 12: case 13: case 14:
						if(Number(par)>0){
							data[i].dataList[s].EvnObservData_id= values[val][par].EvnObservData_id;
							data[i].dataList[s].EvnObservData_Value= base_form.findField('val0_'+par).getValue();
							data[i].dataList[s].ObservParamType_id= par;
							data[i].dataList[s].isMain = (values[val][par].isMain==1)?false:true;
							if(!flag){flag=(base_form.findField('val0_'+par).getValue()>0)}
						}
						break;
				}
				s++;
				}
			}
			i++;
		}
		 if (!flag) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: 'Должно быть указано значение хотя бы одного измеряемого параметра!',
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }
		return data;
	},
	initComponent: function() {
        var thas = this;
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'EvnObservEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{name: 'EvnPrescrObserv_id'},
				{name: 'EvnObserv_setDT'},
				{name: 'ObservParamType_id'},
				{name: 'ObservTimeType_id'}
				
			]),
			url: '/?c=EvnPrescr&m=saveEvnObserv',

			items: [ 
				{
				allowBlank: false,
				fieldLabel: 'Дата',
				name: 'EvnPrescr_setDT',
				value:getGlobalOptions().date,
				plugins: [
					new Ext.ux.InputTextMask('99.99.9999', false)
				],
				format: 'd.m.Y',
				xtype: 'swdatefield',
				listeners:{
					'change': function(l,c,s){
						var base_form = thas.FormPanel.getForm();
						//base_form.reset()
						base_form.load({
						failure: function() {
						this.getLoadMask().hide();
						sw.swMsg.alert('Ошибка', 'На эту дату нет назначения с типом "Наблюдение"', function() {thas.setDisable()}.createDelegate(this) );
					}.createDelegate(thas),
					params: {
						'EvnObserv_pid': thas.EvnObserv_pid
						,'EvnObserv_setDate':  Ext.util.Format.date(c,'d.m.Y')
					},
					success: function(frm, act) {
						base_form.findField('EvnPrescr_setDT').setValue(c);
						var response_obj = Ext.util.JSON.decode(act.response.responseText);
						log(response_obj);
						thas.saveObject = response_obj[0].ObservParamType_id;
						thas.setEnabled(thas.saveObject);
						//this.getLoadMask().hide();
						//base_form.clearInvalid();
					},
					url: '/?c=EvnPrescr&m=loadEvnPrescrObservPosList'
				});
					}
				}
			}, {
				name: 'EvnObserv_pid', // Идентификатор родительского события (EvnPrescrObserv_id)
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Person_id', // Идентификатор человека
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id', // Идентификатор состояния человека
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Server_id', // Идентификатор сервера
				value: -1,
				xtype: 'hidden'
			},{
				layout:'column',
				width:500,
				items:[{
					layout:'form',
					width:270,
					items:[
					{
						name: 'EvnPrescrObserv_id_1', // Идентификатор состояния человека
						value: -1,
						xtype: 'hidden'
					},{
						name: 'EvnObserv_id_1', // Идентификатор состояния человека
						value: -1,
						xtype: 'hidden'
					},{
						xtype:'label',
						text: "Утро",
						name: 'labDays',
						style: 'margin-left: 172px;font-size:13px;'
					},{
						layout:'column',
						items:[{
							layout:'form',
							items:[
								{
									disabled: true,
									fieldLabel: 'Арт. давление',
									name: 'val1_1',
									// tabIndex: TABINDEX_EDEW + 6,
									/*plugins: [
										new Ext.ux.InputTextMask('999/999',false)
									],*/
									// tabIndex: TABINDEX_EDEW + 6,
									width: 30,
									allowDecimals: false,
									allowNegative: false,
									xtype: 'textfield',
									regex: new RegExp("^[0-9]{2,3}$"),
									regexText:'80-140',
									maskRe: /[0-9]/
								}]
							},{
									xtype:'label',
									html: '/',
									style: 'padding:2px;font-size:13px;'
							},{
							layout:'form',
							items:[
								{
									disabled: true,
									hideLabel:true,
									name: 'val1_2',
									// tabIndex: TABINDEX_EDEW + 6,
									/*plugins: [
										new Ext.ux.InputTextMask('999/999',false)
									],*/
									// tabIndex: TABINDEX_EDEW + 6,
									width: 30,
									allowDecimals: false,
									allowNegative: false,
									xtype: 'textfield',
									regex: new RegExp("^[0-9]{2,3}$"),
									regexText:'110-60',
									maskRe: /[0-9]/
								}]
							}
						]
					}/*,{
						disabled: true,
						fieldLabel: 'Диаст. давление',
						name: 'val1_2',
						// tabIndex: TABINDEX_EDEW + 6,
						width: 60,
						allowDecimals: false,
						allowNegative: false,
						xtype: 'numberfield'
					}*/,{
						layout:'column',
						items:[
							{
								layout:'form',
								items:[
								{
									disabled: true,
									fieldLabel: 'Температура',
									//hideLabel:true,
									name: 'val1_4',
									plugins: [
										new Ext.ux.InputTextMask('99.9',true)
									],
									// tabIndex: TABINDEX_EDEW + 6,
									width: 70,
									allowDecimals: true,
									allowNegative: false,
									xtype: 'textfield'
								}]
							},{
									xtype:'label',
									html: '°C',
									style: 'margin-left:7px;font-size:13px;'
							}]
					},{
						disabled: true,
						fieldLabel: 'Пульс',
						name: 'val1_3',
						// tabIndex: TABINDEX_EDEW + 6,
						width: 70,
						allowDecimals: false,
						allowNegative: false,
						xtype: 'numberfield'
					}
					]	
				},{layout:'form',
					width:110,
					items:[
					{
						name: 'EvnPrescrObserv_id_2', // Идентификатор состояния человека
						value: -1,
						xtype: 'hidden'
					},{
						name: 'EvnObserv_id_2', // Идентификатор состояния человека
						value: -1,
						xtype: 'hidden'
					},{
						xtype:'label',
						text: "День",
						name: 'labDays',
						style: 'margin-left:7px;font-size:13px;'
					},{
						layout:'column',
						items:[{
							layout:'form',
							items:[
								{
									disabled: true,
									hideLabel:true,
									name: 'val2_1',
									// tabIndex: TABINDEX_EDEW + 6,
									/*plugins: [
										new Ext.ux.InputTextMask('999/999',false)
									],*/
									// tabIndex: TABINDEX_EDEW + 6,
									width: 30,
									allowDecimals: false,
									allowNegative: false,
									xtype: 'textfield',
									regex: new RegExp("^[0-9]{2,3}$"),
									regexText:'80-140',
									maskRe: /[0-9]/
								}]
							},{
									xtype:'label',
									html: '/',
									style: 'padding:2px;font-size:13px;'
							},{
							layout:'form',
							items:[
								{
									disabled: true,
									hideLabel:true,
									name: 'val2_2',
									// tabIndex: TABINDEX_EDEW + 6,
									/*plugins: [
										new Ext.ux.InputTextMask('999/999',false)
									],*/
									// tabIndex: TABINDEX_EDEW + 6,
									width: 30,
									allowDecimals: false,
									allowNegative: false,
									xtype: 'textfield',
									regex: new RegExp("^[0-9]{2,3}$"),
									regexText:'110-60',
									maskRe: /[0-9]/
								}]
							}
						]
					},/*{
						disabled: true,
						//fieldLabel: 'Диаст. давление',
						hideLabel:true,
						name: 'val2_2',
						// tabIndex: TABINDEX_EDEW + 6,
						width: 60,
						allowDecimals: false,
						allowNegative: false,
						xtype: 'numberfield'
					},*/{
						layout:'column',
						items:[
							{
								layout:'form',
								items:[
								{
									disabled: true,
									//fieldLabel: 'Температура',
									hideLabel:true,
									name: 'val2_4',
									// tabIndex: TABINDEX_EDEW + 6,
									plugins: [
										new Ext.ux.InputTextMask('99.9', true)
									],
									width: 70,
									allowDecimals: true,
									allowNegative: false,
									xtype: 'textfield'
								}]
							},{
									xtype:'label',
									html: '°C',
									style: 'margin-left:7px;font-size:13px;'
							}]
					},{
						disabled: true,
						//fieldLabel: 'Пульс',
						hideLabel:true,
						name: 'val2_3',
						// tabIndex: TABINDEX_EDEW + 6,
						width: 70,
						allowDecimals: false,
						allowNegative: false,
						xtype: 'numberfield'
					}
					]	
				},{layout:'form',
					width:110,
					items:[
					{
						name: 'EvnPrescrObserv_id_3', // Идентификатор состояния человека
						value: -1,
						xtype: 'hidden'
					},{
						name: 'EvnObserv_id_3', // Идентификатор состояния человека
						value: -1,
						xtype: 'hidden'
					},{
						xtype:'label',
						text: "Вечер",
						name: 'labDays',
						style: 'margin-left:7px;font-size:13px;'
					},{
						layout:'column',
						items:[{
							layout:'form',
							items:[
								{
									disabled: true,
									hideLabel:true,
									name: 'val3_1',
									// tabIndex: TABINDEX_EDEW + 6,
									/*plugins: [
										new Ext.ux.InputTextMask('999/999',false)
									],*/
									// tabIndex: TABINDEX_EDEW + 6,
									width: 30,
									allowDecimals: false,
									allowNegative: false,
									xtype: 'textfield',
									regex: new RegExp("^[0-9]{2,3}$"),
									regexText:'80-140',
									maskRe: /[0-9]/
								}]
							},{
									xtype:'label',
									html: '/',
									style: 'padding:2px;font-size:13px;'
							},{
							layout:'form',
							items:[
								{
									disabled: true,
									hideLabel:true,
									name: 'val3_2',
									// tabIndex: TABINDEX_EDEW + 6,
									/*plugins: [
										new Ext.ux.InputTextMask('999/999',false)
									],*/
									// tabIndex: TABINDEX_EDEW + 6,
									width: 30,
									allowDecimals: false,
									allowNegative: false,
									xtype: 'textfield',
									regex: new RegExp("^[0-9]{2,3}$"),
									regexText:'110-60',
									maskRe: /[0-9]/
								}]
							}
						]
					}/*,{
						disabled: true,
						//fieldLabel: 'Диаст. давление',
						hideLabel:true,
						name: 'val3_2',
						// tabIndex: TABINDEX_EDEW + 6,
						width: 60,
						allowDecimals: false,
						allowNegative: false,
						xtype: 'numberfield'
					}*/,{
						layout:'column',
						items:[
							{
								layout:'form',
								items:[
								{
									disabled: true,
									//fieldLabel: 'Температура',
									hideLabel:true,
									name: 'val3_4',
									// tabIndex: TABINDEX_EDEW + 6,
									plugins: [
										new Ext.ux.InputTextMask('99.9', true)
									],
									width: 70,
									allowDecimals: true,
									allowNegative: false,
									xtype: 'textfield'
								}]
							},{
									xtype:'label',
									html: '°C',
									style: 'margin-left:7px;font-size:13px;'
							}]
					},{
						disabled: true,
						//fieldLabel: 'Пульс',
						hideLabel:true,
						name: 'val3_3',
						// tabIndex: TABINDEX_EDEW + 6,
						width: 70,
						allowDecimals: false,
						allowNegative: false,
						xtype: 'numberfield'
					}
					]	
				}
				]
			},{
				xtype:'label',
				html: '<hr>'
			 },{layout:'column',items:[{layout:'form',items:[
			 {
				disabled: true,
				fieldLabel: 'Частота дыхания',
				name: 'val0_5',
				// tabIndex: TABINDEX_EDEW + 6,
				width: 50,
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield'
			}]},{
				xtype:'label',
				html: 'в минуту',
				style: 'margin-left:7px;font-size:13px;'
			}]},{layout:'column',items:[{layout:'form',items:[
			{
				disabled: true,
				fieldLabel: 'Вес',
				name: 'val0_6',
				// tabIndex: TABINDEX_EDEW + 6,
				width: 50,
				allowDecimals: true,
				allowNegative: false,
				xtype: 'numberfield'
			}]},{
				xtype:'label',
				html: 'кг',
				style: 'margin-left:7px;font-size:13px;'
			}]},{layout:'column',items:[{layout:'form',items:[ // ipavelpetrov
			{
				disabled: true,
				fieldLabel: 'Рост',
				name: 'val0_14',
				// tabIndex: TABINDEX_EDEW + 6,
				width: 50,
				allowDecimals: true,
				allowNegative: false,
				xtype: 'numberfield'
			}]},{
				xtype:'label',
				html: 'см',
				style: 'margin-left:7px;font-size:13px;'
			}]},{layout:'column',items:[{layout:'form',items:[
			{
				disabled: true,
				fieldLabel: 'Выпито жидкости',
				name: 'val0_7',
				// tabIndex: TABINDEX_EDEW + 6,
				width: 50,
				allowDecimals: true,
				allowNegative: false,
				xtype: 'numberfield'
			}]},{
				xtype:'label',
				html: 'мл',
				style: 'margin-left:7px;font-size:13px;'
			}]},{layout:'column',items:[{layout:'form',items:[
			{
				disabled: true,
				fieldLabel: 'Суточное кол-во мочи',
				name: 'val0_8',
				// tabIndex: TABINDEX_EDEW + 6,
				width: 50,
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield'
			}]},{
				xtype:'label',
				html: 'мл',
				style: 'margin-left:7px;font-size:13px;'
			}]},{
				xtype:'label',
				html: '<hr>'
			},{
				disabled: true,
				xtype:'swyesnocombo',
				fieldLabel: 'Стул',
				name: 'val0_9'
			},{
				disabled: true,
				xtype:'swyesnocombo',
				fieldLabel: 'Ванна',
				name: 'val0_10'
			},{
				disabled: true,
				xtype:'swyesnocombo',
				fieldLabel: 'Смена белья',
				name: 'val0_11'
			},{ //ipavelpetrov
				disabled: true,
				xtype:'swyesnocombo',
				fieldLabel: 'Pediculosis',
				name: 'val0_12'
			},{ //ipavelpetrov
				disabled: true,
				xtype:'swyesnocombo',
				fieldLabel: 'Scabies',
				name: 'val0_13'
			}
			 /*{
				allowBlank: false,
				fieldLabel: 'Дата выполнения',
				format: 'd.m.Y',
				name: 'EvnObserv_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				// tabIndex: TABINDEX_EPSEF + 4,
                listeners: {
                    change: function(field, newValue, oldValue) {
                        var base_form = thas.FormPanel.getForm();
                        if (newValue && base_form.findField('ObservTimeType_id').getValue()) {
                            thas.EvnObservDataGrid.getGrid().getStore().removeAll();
                            thas.EvnObservDataGrid.loadData({
                                globalFilters: {
                                    EvnObserv_pid: base_form.findField('EvnObserv_pid').getValue(),
                                    EvnObserv_setDate: field.getRawValue(),
                                    ObservTimeType_id: base_form.findField('ObservTimeType_id').getValue()
                                }
                            });
                        }
                    }
                },
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				comboSubject: 'ObservTimeType',
				fieldLabel: 'Время наблюдения',
				hiddenName: 'ObservTimeType_id',
				listeners: {
                    render: function(combo) {
                        combo.getStore().load();
                    },
                    change: function(field, newValue, oldValue) {
                        var base_form = thas.FormPanel.getForm();
                        if (base_form.findField('EvnObserv_setDate').getValue() && newValue) {
                            thas.EvnObservDataGrid.getGrid().getStore().removeAll();
                            thas.EvnObservDataGrid.loadData({
                                globalFilters: {
                                    EvnObserv_pid: base_form.findField('EvnObserv_pid').getValue(),
                                    EvnObserv_setDate: base_form.findField('EvnObserv_setDate').getRawValue(),
                                    ObservTimeType_id: newValue
                                }
                            });
                        }
                    }
				},
				// tabIndex: TABINDEX_EPREF + 1,
				width: 100,
				xtype: 'swcommonsprcombo'
			}*/]
		});


		Ext.apply(this, {
			buttons: [{
				text: '-'
			},{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					// var base_form = this.FormPanel.getForm();
				}.createDelegate(this),
				onTabAction: function () {
					// this.buttons[1].focus();
				}.createDelegate(this),
				// tabIndex: TABINDEX_EOBSEF + 34,
				text: BTN_FRMSAVE
			}],
			items: [
					//this.PersonInfo,
					this.FormPanel
			]
			
		});

		sw.Promed.swEvnObservEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnObservEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			//
		},
		'restore': function(win) {
			//
		}
	},
	loadMask: null,
	maximizable: false,
	maximized: false,
	minHeight: 450,
	minWidth: 450,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	setDisable: function(){
		var base_form = this.FormPanel.getForm();
		base_form.items.each(function(rec){
			if(rec.name.indexOf('val')==0){
				rec.setValue('');
				rec.isMain=0;
				rec.enable();
			}
		})
		var values = this.saveObject;
		for(var val in values){
			switch(val){
				case 1:case 3:case '1':case '3':
					base_form.findField('val2_1').disable();
					base_form.findField('val2_2').disable();
					base_form.findField('val2_3').disable();
					base_form.findField('val2_4').disable();
					break;
				case 2:case '2':
					base_form.findField('val1_1').disable();
					base_form.findField('val1_2').disable();
					base_form.findField('val1_3').disable();
					base_form.findField('val1_4').disable();
					base_form.findField('val3_1').disable();
					base_form.findField('val3_2').disable();
					base_form.findField('val3_3').disable();
					base_form.findField('val3_4').disable();
					break;
			}
		}
	},
	show: function() {
		sw.Promed.swEvnObservEditWindow.superclass.show.apply(this, arguments);
		var win = this;
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.EvnObserv_pid = null;
		this.saveObject= null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() {this.hide();}.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);
		var timed;
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		if ( arguments[0].formParams && arguments[0].formParams.EvnObserv_pid ) {
			this.EvnObserv_pid = arguments[0].formParams.EvnObserv_pid;
		}
		if ( arguments[0].formParams && arguments[0].formParams.EvnObserv_setDate) {
			
			if(typeof arguments[0].formParams.EvnObserv_setDate=='object'||(typeof arguments[0].formParams.EvnObserv_setDate=='string'&&arguments[0].formParams.EvnObserv_setDate.length==10))
			base_form.findField('EvnPrescr_setDT').setValue(arguments[0].formParams.EvnObserv_setDate);
			
		}

		
				base_form.load({
					failure: function() {
						this.getLoadMask().hide();
						sw.swMsg.alert('Ошибка', 'На эту дату нет назначения с типом "Наблюдение"', function() {win.setDisable()}.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnObserv_pid': win.EvnObserv_pid
						,'EvnObserv_setDate': Ext.util.Format.date(base_form.findField('EvnPrescr_setDT').getValue(),'d.m.Y')
					},
					success: function(frm, act) {
						var response_obj = Ext.util.JSON.decode(act.response.responseText);
						win.saveObject = response_obj[0].ObservParamType_id;
						win.setEnabled(win.saveObject);
						//this.getLoadMask().hide();
						//base_form.clearInvalid();
					},
					url: '/?c=EvnPrescr&m=loadEvnPrescrObservPosList'
				});
       // base_form.findField('EvnObserv_setDate').setDisabled(arguments[0].disableChangeTime || false);
       // base_form.findField('ObservTimeType_id').setDisabled(arguments[0].disableChangeTime || false);

		/*this.PersonInfo.load({
			Person_id: base_form.findField('Person_id').getValue()
		});*/

       /* if (base_form.findField('EvnObserv_setDate').getValue() && base_form.findField('ObservTimeType_id').getValue()) {
            this.EvnObservDataGrid.loadData({
                globalFilters: {
                    EvnObserv_pid: base_form.findField('EvnObserv_pid').getValue(),
                    EvnObserv_setDate: base_form.findField('EvnObserv_setDate').getRawValue(),
                    ObservTimeType_id: base_form.findField('ObservTimeType_id').getValue()
                }
            });
        }*/

		base_form.clearInvalid();
       /* if (!base_form.findField('EvnObserv_setDate').disabled) {
            base_form.findField('EvnObserv_setDate').focus(true, 250);
        }*/
	},
	title: 'Выполнение назначения: Наблюдение',
	width: 550
});