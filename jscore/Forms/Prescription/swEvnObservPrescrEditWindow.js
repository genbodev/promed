/**
 * swEvnObservPrescrEditWindow - окно для редактирования данных наблюдения за пациентом
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

sw.Promed.swEvnObservPrescrEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnObservPrescrEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnObservPrescrEditWindow.js',
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
        /*if ( !base_form.isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                    this.FormPanel.getFirstInvalidEl().focus(true);
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: lang['ne_verno_zapolnenyi_polya'],
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }*/

		params.evnObservDataList = Ext.util.JSON.encode(evnObservDataList);

		/*if ( base_form.findField('ObservTimeType_id').disabled ) {
			params.ObservTimeType_id = base_form.findField('ObservTimeType_id').getValue();
		}*/
        if (base_form.findField('EvnObserv_setDate')) {
            params.EvnObserv_setDate = base_form.findField('EvnObserv_setDate').getRawValue();
        }
		if (base_form.findField('PersonEvn_id')) {
			params.PersonEvn_id = base_form.findField('PersonEvn_id').getRawValue();
		}
		if (base_form.findField('Server_id')) {
			params.Server_id = base_form.findField('Server_id').getRawValue();
		}
        this.setAllowBlank();
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});
		loadMask.show();

		var url = '/?c=EvnPrescr&m=saveEvnObserv';

		base_form.submit({
			url: url,
			params: params,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if (action.result.isExec) {
                        var data = {};
                        var evnObservData = {};
                        evnObservData.EvnObserv_id = action.result.EvnObserv_id;
                        data.evnObservData = evnObservData;
                        //this.callback(data);
                    }
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	formStatus: 'edit',
	//height: 450,
	autoHeight: true,
	id: 'EvnObservPrescrEditWindow',
	setEnabled: function(values){
		this.setAllowBlank();
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
						// Если открыто м/с - обязательны согласно типу времени открытия
						if(this.OpenedByMS){
							if(this.ObservTimeType_id == val)
								base_form.findField('val'+val+'_'+par).setAllowBlank(false);
						} else {
						// Если открыто не м/с - обязательны все назначенные
							if(this.ObservTimeType_id == val){
								if(val == 2){
									base_form.findField('val'+val+'_'+par).setAllowBlank(false);
								} else {
									base_form.findField('val1_'+par).setAllowBlank(false);
									base_form.findField('val3_'+par).setAllowBlank(false);
								}
							}
						}
						
						//if(values[val][par].value>0)base_form.findField('val'+val+'_'+par).disable();
						break;
					case 5:case 6:case 7:case 8:case 9:case 10:case 11:case 12:case 13:
						base_form.findField('val0_'+par).enable();
						base_form.findField('val0_'+par).setValue(values[val][par].value);
						base_form.findField('val0_'+par).isMain = values[val][par].isMain;
						base_form.findField('val0_'+par).setAllowBlank(false);
						base_form.findField('val0_'+par).showContainer();
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
			data[i].EvnObserv_setDate = Ext.util.Format.date(base_form.findField('EvnObserv_setDate').getValue(), 'm.d.Y');
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
					case 5:case 6:case 7:case 8:case 9:case 10:case 11:case 12:case 13:
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
		 /*if (!flag) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: lang['doljno_byit_ukazano_znachenie_hotya_byi_odnogo_izmeryaemogo_parametra'],
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }*/
		return data;
	},

	refreshFieldsVisibility: function() {
		var base_form = this.FormPanel.getForm();
		var hasAge = !Ext.isEmpty(this.Person_Age);

		var val0_10 = base_form.findField('val0_10');
		var val0_10_allow = (!Ext.isEmpty(val0_10.getValue()) || !hasAge || this.Person_Age >= 1);
		val0_10.setContainerVisible(val0_10_allow);
		if (!val0_10_allow) val0_10.setAllowBlank(true);

		var val0_11 = base_form.findField('val0_11');
		var val0_11_allow = (!Ext.isEmpty(val0_11.getValue()) || !hasAge || this.Person_Age >= 1);
		val0_11.setContainerVisible(val0_11_allow);
		if (!val0_11_allow) val0_11.setAllowBlank(true);

		var val0_12 = base_form.findField('val0_12');
		var val0_12_allow = (!Ext.isEmpty(val0_12.getValue()) || (hasAge && this.Person_Age < 1));
		val0_12.setContainerVisible(val0_12_allow);
		if (!val0_12_allow) val0_12.setAllowBlank(true);

		var val0_13 = base_form.findField('val0_13');
		var val0_13_allow = (!Ext.isEmpty(val0_13.getValue()) || (hasAge && this.Person_Age < 1));
		val0_13.setContainerVisible(val0_13_allow);
		if (!val0_13_allow) val0_13.setAllowBlank(true);

		this.syncShadow();
	},

	loadEvnObservData: function(callback) {
		callback = callback || Ext.emptyFn;
		var wnd = this;
		var base_form = wnd.FormPanel.getForm();
		var url = '/?c=EvnPrescr&m=loadEvnPrescrObservPosList';

		var params = {
			'EvnObserv_pid': wnd.EvnObserv_pid,
			'EvnObserv_setDate':  Ext.util.Format.date(base_form.findField('EvnObserv_setDate').getValue(),'d.m.Y')
		};

		base_form.load({
			params: params,
			failure: function() {
				sw.swMsg.alert(lang['oshibka'], lang['na_etu_datu_net_naznacheniya_s_tipom_nablyudenie'], function() {wnd.setDisable()});
			},
			success: function(form, action) {
				var response_obj = Ext.util.JSON.decode(action.response.responseText);
				log(response_obj);

				wnd.saveObject = response_obj[0].ObservParamType_id;
				wnd.setEnabled(wnd.saveObject);

				callback();
			},
			url: url
		});
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
				{name: 'EvnObserv_setDate'},
				{name: 'ObservParamType_id'},
				{name: 'ObservTimeType_id'}
			]),

			items: [{
				allowBlank: false,
				fieldLabel: lang['data'],
				name: 'EvnObserv_setDate',
				value: getGlobalOptions().date,
				xtype: 'swdatefield',
				listeners:{
					'change': function(field, newValue, oldValue){
						var base_form = thas.FormPanel.getForm();

						var Person_Birthday = base_form.findField('Person_Birthday').getValue();

						if (!Ext.isEmpty(Person_Birthday)) {
							Person_Birthday = Date.parseDate(Person_Birthday, 'd.m.Y');

							thas.Person_Age = swGetPersonAge(Person_Birthday, newValue);
						}
						thas.refreshFieldsVisibility();

						thas.loadEvnObservData(function() {
							field.setValue(newValue);
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
				name: 'Person_Birthday', // Дата рождения человека
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
						text: lang['utro'],
						name: 'labDays',
						style: 'margin-left: 172px;font-size:13px;'
					},{
						layout:'column',
						items:[{
							layout:'form',
							items:[
								{
									disabled: true,
									fieldLabel: lang['art_davlenie'],
									name: 'val1_1',
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
						fieldLabel: lang['diast_davlenie'],
						name: 'val1_2',
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
									fieldLabel: lang['temperatura'],
									//hideLabel:true,
									name: 'val1_4',
									plugins: [
										new Ext.ux.InputTextMask('99.9',true)
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
						fieldLabel: lang['puls'],
						name: 'val1_3',
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
						text: lang['den'],
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
						text: lang['vecher'],
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
				fieldLabel: lang['chastota_dyihaniya'],
				name: 'val0_5',
				width: 50,
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield'
			}]},{
				xtype:'label',
				html: lang['v_minutu'],
				style: 'margin-left:7px;font-size:13px;'
			}]},{layout:'column',items:[{layout:'form',items:[
			{
				disabled: true,
				fieldLabel: lang['ves'],
				name: 'val0_6',
				width: 50,
				allowDecimals: true,
				allowNegative: false,
				xtype: 'numberfield'
			}]},{
				xtype:'label',
				html: lang['kg'],
				style: 'margin-left:7px;font-size:13px;'
			}]},{layout:'column',items:[{layout:'form',items:[
			{
				disabled: true,
				fieldLabel: lang['vyipito_jidkosti'],
				name: 'val0_7',
				width: 50,
				allowDecimals: true,
				allowNegative: false,
				xtype: 'numberfield'
			}]},{
				xtype:'label',
				html: lang['ml'],
				style: 'margin-left:7px;font-size:13px;'
			}]},{layout:'column',items:[{layout:'form',items:[
			{
				disabled: true,
				fieldLabel: lang['sutochnoe_kol-vo_mochi'],
				name: 'val0_8',
				width: 50,
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield'
			}]},{
				xtype:'label',
				html: lang['ml'],
				style: 'margin-left:7px;font-size:13px;'
			}]},{
				xtype:'label',
				html: '<hr>'
			},{
				disabled: true,
				xtype:'swyesnocombo',
				fieldLabel: lang['stul'],
				name: 'val0_9'
			},{
				disabled: true,
				xtype:'swyesnocombo',
				fieldLabel: lang['vanna'],
				name: 'val0_10'
			},{
				disabled: true,
				xtype:'swyesnocombo',
				fieldLabel: lang['smena_belya'],
				name: 'val0_11'
			},{
				disabled: true,
				xtype:'swcommonsprcombo',
				comboSubject: 'ObservPesultType',
				fieldLabel: lang['reaktsiya_na_osmotr'],
				hiddenName: 'val0_13',
				name: 'val0_13'
			},{
				disabled: true,
				xtype:'swcommonsprcombo',
				comboSubject: 'ObservPesultType',
				fieldLabel: lang['reaktsiya_zrachka'],
				hiddenName: 'val0_12',
				name: 'val0_12'
			}]
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

		sw.Promed.swEvnObservPrescrEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnObservPrescrEditWindow');

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
	setAllowBlank: function(){
		var base_form = this.FormPanel.getForm();
		base_form.items.each(function(rec){
			if(rec.name.indexOf('val')==0){
				rec.setAllowBlank(true);
			}
		})
	},
	show: function() {
		sw.Promed.swEvnObservPrescrEditWindow.superclass.show.apply(this, arguments);
		var win = this;
		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.EvnObserv_pid = null;
		this.saveObject= null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.Person_Age = null;
		
		if ( !arguments[0] || !arguments[0].formParams) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

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
			
			if(typeof arguments[0].formParams.EvnObserv_setDate=='object'||(typeof arguments[0].formParams.EvnObserv_setDate=='string'&&arguments[0].formParams.EvnObserv_setDate.length==10)) {
				base_form.findField('EvnObserv_setDate').setValue(arguments[0].formParams.EvnObserv_setDate);
			} else {
				base_form.findField('EvnObserv_setDate').setValue(getGlobalOptions().date);
			}
		}
		// Тип наблюдения 1-утро, 2-день, 3-вечер
		if ( arguments[0].formParams && arguments[0].formParams.ObservTimeType_id ) {
			this.ObservTimeType_id = arguments[0].formParams.ObservTimeType_id;
		}
		// Кем открыта форма врачом или м/с
		if ( arguments[0].disableChangeTime ) {
			this.OpenedByMS = true;
		} else {
			this.OpenedByMS = false;
		}

		var Person_Birthday = base_form.findField('Person_Birthday').getValue();
		var EvnObserv_setDate = base_form.findField('EvnObserv_setDate').getValue();

		if (!Ext.isEmpty(Person_Birthday)) {
			Person_Birthday = Date.parseDate(Person_Birthday, 'd.m.Y');

			this.Person_Age = swGetPersonAge(Person_Birthday, EvnObserv_setDate);
		}
		this.refreshFieldsVisibility();

		this.loadEvnObservData(function() {
			base_form.findField('EvnObserv_setDate').setValue(EvnObserv_setDate);
		});

		base_form.clearInvalid();
		this.buttons[1].show();
	},
	title: lang['vyipolnenie_naznacheniya_nablyudenie'],
	width: 550
});