/**
 * swLabSampleEditWindow.js - Редактирование пробы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 */

sw.Promed.swLabSampleEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoScroll: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	formStatus: 'edit',
	draggable: true,
	split: true,
	showCloseButtonInTop: false,
    oldEvnSampleNum: '',
	layout: 'form',
	width: 710,
	cls: 'newStyle',
	autoHeight: true,
	id: 'LabSampleEditWindow',
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
	maximizable: false,
	resizable: false,
	convertDates: function (obj){
		for(var field_name in obj) {
			if (field_name.inlist(['EvnLabSample_setDT','EvnLabSample_DelivDT'])) {
				if (obj.hasOwnProperty(field_name)) {
					var value = obj[field_name];
					var datetime = value;
					if (typeof(datetime) != 'object') {
						var datetime = Date.parseDate(value, 'd.m.Y H:i');
						if (typeof(datetime) != 'object') {
							datetime = Date.parseDate(value, 'd.m.Y');
							if (typeof(datetime) != 'object') {
								var v = Date.parse(value);
								if (!isNaN(v)) {
									datetime = new Date();
									datetime.setTime(v);
								}
							}
						}
					}
					
					if (typeof(datetime) == 'object') {
						obj[field_name] = datetime.format('d.m.Y H:i');
					}
				}
			}
		}
		return obj;
    },
	doSave: function(options)
	{
		if ( typeof options != 'object' ) {
            options = new Object();
        }
		
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.LabSampleEditForm;
		var base_form = form.getForm();
        var win = this;
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		// Передаём 12-ти цифровой код
		base_form.findField('EvnLabSample_Num').setValue(base_form.findField('EvnLabSample_Num').getValue().substr(0,8) + base_form.findField('EvnLabSample_ShortNum').getValue());
		// Если изменился номер пробы то проверяем переданный цифровой код на уникальность
		if (!options.ignoreCheckLabSampleNum && win.oldEvnSampleNum != base_form.findField('EvnLabSample_Num').getValue()) {
			win.getLoadMask(lang['proverka_unikalnosti_shtrih-koda']).show();
			Ext.Ajax.request({
				url: '/?c=EvnLabSample&m=checkEvnLabSampleUnique',
				params: {
					EvnLabSample_Num: base_form.findField('EvnLabSample_Num').getValue(),
					MedService_id: win.MedService_id
				},
				callback: function(opt, success, response) {
					win.getLoadMask().hide();
					if (success || response.responseText.Error_Msg != '') {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!Ext.isEmpty(result.Error_Msg)) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function() {
									base_form.findField('EvnLabSample_Num').focus(false);
								}.createDelegate(this),
								icon: Ext.Msg.WARNING,
								msg: result.Error_Msg,
								title: lang['proverka_nomera_probyi_na_unikalnost']
							});
							return false;
						} else {
							options.ignoreCheckLabSampleNum = true;

							Ext.Msg.show({
								title: 'Внимание',
								msg: 'Номер пробы изменен на №'+ base_form.findField('EvnLabSample_ShortNum').getValue() +'. Изменить штрих-код на №'+base_form.findField('EvnLabSample_Num').getValue()+'?',
								buttons: Ext.Msg.YESNO,
								fn: function(btn) {
									if (btn === 'yes') {
										base_form.findField('EvnLabSample_BarCode').setValue(base_form.findField('EvnLabSample_Num').getValue());
										win.doSave(options);
									} else {
										win.doSave(options);
									}
								},
								icon: Ext.MessageBox.QUESTION
							});
						}
					}
				}
			});
			
			win.formStatus = 'edit';
			return false;
		}

		if ( base_form.findField('EvnLabSample_ShortNum').getValue().length != 4 ) {

            sw.swMsg.show({
				buttons: Ext.Msg.OK,
                fn: function() {
                    win.formStatus = 'edit';
                    base_form.findField('EvnLabSample_Num').focus(false);
                }.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['nomer_probyi_shtrih_koda_doljen_sostoyat_iz_4_znakov'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		win.getLoadMask(lang['podojdite_idet_sohranenie']).show();

		var index;
		var params = {};


		var ref_sample_id = base_form.findField('RefSample_id').getValue();

		if (getRegionNick() == 'vologda')
			params.MedService_sid = this.MedService_id;
		params.EvnLabSample_setDT = base_form.findField('EvnLabSample_setDT').getValue();
		params.EvnLabSample_Num = base_form.findField('EvnLabSample_Num').getValue();
		params.EvnLabSample_BarCode = base_form.findField('EvnLabSample_BarCode').getValue();
		params.EvnLabSample_id = base_form.findField('EvnLabSample_id').getValue();
		params.RefMaterial_Name = base_form.findField('RefMaterial_Name').getValue();
		params.RefSample_id = ref_sample_id;
		params.Lpu_did = base_form.findField('Lpu_did').getValue();
		params.LpuSection_did = base_form.findField('LpuSection_did').getValue();
		params.MedPersonal_did = base_form.findField('MedPersonal_did').getValue();
		params.MedPersonal_sdid = base_form.findField('MedPersonal_sdid').getValue();
		params.EvnLabSample_DelivDT = base_form.findField('EvnLabSample_DelivDT').getValue();
		params.DefectCauseType_id = base_form.findField('DefectCauseType_id').getValue();
		params.EvnLabSample_Comment = base_form.findField('EvnLabSample_Comment').getValue();
		params.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
		params.Server_id = base_form.findField('Server_id').getValue();
		params.Analyzer_id = base_form.findField('Analyzer_id').getValue();
		params.EvnLabRequest_id = base_form.findField('EvnLabRequest_id').getValue();

		var data = {};
		data.EvnLabSampleData = params;
		// win.callback(data);
		//win.convertDates(data.EvnLabSampleData);//без конвертации даты корректно сохраняются, читаются
		//с конвертацией не сохраняются
		
		Ext.Ajax.request({
			url: '/?c=EvnLabSample&m=save',
			params: data.EvnLabSampleData,
			method: 'post',
			callback: function (opt, success, response){
				win.getLoadMask().hide();
				win.formStatus = 'edit';
				
				if (success && response.responseText != '') {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						if (result.Alert_Msg) {
							sw.swMsg.alert(lang['oshibka_otpravki_v_lis'], result.Alert_Msg);
						}

						// после того как сохранили обновляем старый штрих-код, запускаем калбеки
						win.oldEvnSampleNum = base_form.findField('EvnLabSample_Num').getValue();
						var callbackResult = win.remoteCallback(data);
						win.callback({
							'EvnLabSample_ShortNum': base_form.findField('EvnLabSample_ShortNum').getValue(),
							'EvnLabSample_BarCode': base_form.findField('EvnLabSample_BarCode').getValue(),
							'UslugaExecutionType_id': result.UslugaExecutionType_id
						});
						if (options.callback) {
							options.callback();
						} else if (!callbackResult) {
							win.hide();
						}
					}
				}
			}
		});
	},
	initComponent: function()
	{
        var win = this;

		this.LabSampleEditForm = new Ext.form.FormPanel(
		{
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			id: 'LabSampleEditForm',
			frame: true,
			labelAlign: 'right',
			labelWidth: 140,
			items: [
				new Ext.form.FieldSet({
					autoHeight: true,
					style: 'margin-bottom: 0.5em;',
					bodyStyle:'padding:5px;',
					border: true,
					region: 'north',
					layout: 'form',
					title: lang['vzyatie_probyi'],
					items: [{
						name: 'PersonEvn_id',
						xtype: 'hidden',
						value: 0
					}, {
						name: 'Server_id',
						xtype: 'hidden'
					}, {
						name: 'Analyzer_id',
						xtype: 'hidden'
					}, {
						name: 'RefSample_id',
						xtype: 'hidden'
					}, {
						name: 'EvnLabSample_id',
						xtype: 'hidden'
					}, {
						name: 'EvnLabRequest_id',
						xtype: 'hidden'
					}, {
						name: 'EvnLabSample_Num',
						xtype: 'hidden'
					}, {
						name: 'EvnLabSample_BarCode',
						xtype: 'hidden'
					}, {
                        dateLabel: lang['data_vzyatiya_probyi'],
                        hiddenName: 'EvnLabSample_setDT',
						allowBlank: false,
                        xtype: 'swdatetimefield'
					},{
                        dateLabel: lang['data_dostavki_probyi'],
                        hiddenName: 'EvnLabSample_DelivDT',
                        xtype: 'swdatetimefield'
					},{
						fieldLabel: lang['nomer_probyi'],
						name: 'EvnLabSample_ShortNum',
						disabled: false,
						xtype: 'textfield',
						autoCreate: {tag: "input", minLength: "4", maxLength: "4", autocomplete: "off"},
						width: 85,
						maskRe: /[0-9]/
					}, {
						fieldLabel: lang['biomaterial'],
						name: 'RefMaterial_Name',
						xtype: 'textfield',
						disabled: true,
						anchor: '70%'
					}, {
						displayField: 'Org_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: lang['organizatsiya'],
						hiddenName: 'Lpu_did',
						listeners: {
							'keydown': function( inp, e ) {
								if ( inp.disabled )
									return;

								if ( e.F4 == e.getKey() ) {
									if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
									else
										e.browserEvent.cancelBubble = true;

									if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
									else
										e.browserEvent.returnValue = false;

									e.returnValue = false;

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									inp.onTrigger1Click();
									return false;
								}
							},
							'keyup': function(inp, e) {
								if ( e.F4 == e.getKey() ) {
									if ( e.browserEvent.stopPropagation )
										e.browserEvent.stopPropagation();
									else
										e.browserEvent.cancelBubble = true;

									if ( e.browserEvent.preventDefault )
										e.browserEvent.preventDefault();
									else
										e.browserEvent.returnValue = false;

									e.returnValue = false;

									if ( Ext.isIE ) {
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}

									return false;
								}
							}
						},
						mode: 'local',
						onTrigger1Click: function() {
							var base_form = this.LabSampleEditForm.getForm();
							var combo = base_form.findField('Lpu_did');

							if ( combo.disabled ) {
								return false;
							}
							var org_type = 'lpu';

							getWnd('swOrgSearchWindow').show({
								object: org_type,
								onClose: function() {
									combo.focus(true, 200)
								},
								onSelect: function(org_data) {
									if ( org_data.Lpu_id > 0 ) {
										combo.getStore().loadData([{
											Lpu_id: org_data.Lpu_id,
											Org_Name: org_data.Org_Name
										}]);
										combo.setValue(org_data.Lpu_id);
										getWnd('swOrgSearchWindow').hide();
										base_form.findField('LpuSection_did').getStore().load(
											{ params: { Lpu_id: org_data.Lpu_id }
										});
										combo.collapse();
									}
								}
							});
						}.createDelegate(this),
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'Lpu_id', type: 'int' },
								{ name: 'Org_Name', type: 'string' }
							],
							key: 'Lpu_id',
							sortInfo: {
								field: 'Org_Name'
							},
							url: C_ORG_LIST
						}),
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Org_Name}',
							'</div></tpl>'
						),
						trigger1Class: 'x-form-search-trigger',
						triggerAction: 'none',
						valueField: 'Lpu_id',
						anchor: '100%',
						xtype: 'swbaseremotecombo'
					}, {
						hiddenName: 'LpuSection_did',
						xtype: 'swlpusectionglobalcombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.LabSampleEditForm.getForm();

								base_form.findField('MedPersonal_did').clearValue();
								base_form.findField('MedPersonal_did').getStore().load(
								{
									params:
									{
										LpuSection_id: newValue,
										Lpu_id: base_form.findField('Lpu_did').getValue(),
										withPosts: "1"
									},
									callback: function()
									{
										base_form.findField('MedPersonal_did').setValue(base_form.findField('MedPersonal_did').getValue());
									}
								});

								base_form.findField('MedPersonal_sdid').clearValue();
								base_form.findField('MedPersonal_sdid').getStore().load(
								{
									params:
									{
										LpuSection_id: newValue,
										Lpu_id: base_form.findField('Lpu_did').getValue(),
										withPosts: "6"
									},
									callback: function()
									{
										base_form.findField('MedPersonal_sdid').setValue(base_form.findField('MedPersonal_sdid').getValue());
									}
								});
							}.createDelegate(this)
						},
						anchor: '100%'
					}, {
						fieldLabel: lang['vrach'],
						hiddenName: 'MedPersonal_did',
						xtype: 'swmedpersonalcombo',
                        allowBlank: true,
						anchor: '100%'
					}, {
						fieldLabel: lang['sr_medpersonal'],
						hiddenName: 'MedPersonal_sdid',
						xtype: 'swmedpersonalcombo',
                        allowBlank: true,
						anchor: '100%'
					},{
						layout: 'column',
						border: false,
						defaults: {border: false},
						items: [{
							layout: 'form',
							columnWidth: .25,
							items: [{
								fieldLabel: lang['brak_probyi'],
								name: 'EvnLabSample_IsDefect',
								listeners: {
									'check': function(field, value) {
										var base_form = win.LabSampleEditForm.getForm();
										if (value) {
											base_form.findField('DefectCauseType_id').setAllowBlank(false);
											base_form.findField('DefectCauseType_id').enable();
										} else {
											base_form.findField('DefectCauseType_id').setAllowBlank(true);
											base_form.findField('DefectCauseType_id').clearValue();
											base_form.findField('DefectCauseType_id').disable();
										}
									}
								},
								xtype: 'checkbox'
							}]
						}, {
							layout: 'form',
							columnWidth: .75,
							labelWidth: 100,
							items: [{
								fieldLabel: lang['prichina'],
								heddenName: 'DefectCauseType_id',
								typeCode: 'int',
								prefix: 'lis_',
								comboSubject: 'DefectCauseType',
								xtype: 'swcommonsprcombo',
								anchor: '100%'
							}]
						}]
					}, {
						fieldLabel: lang['kommentariy'],
						name: 'EvnLabSample_Comment',
						height: 50,
						xtype: 'textarea',
						anchor: '100%'
					}]
				})
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'PersonEvn_id'},
				{name: 'Server_id'},
				{name: 'Analyzer_id'},
				{name: 'EvnLabSample_id'},
				{name: 'EvnLabRequest_id'},
				{name: 'EvnLabSample_setDT'},
				{name: 'EvnLabSample_Num'},
				{name: 'EvnLabSample_BarCode'},
				{name: 'RefMaterial_Name'},
				{name: 'Lpu_did'},
				{name: 'LpuSection_did'},
				{name: 'MedPersonal_did'},
				{name: 'MedPersonal_sdid'},
				{name: 'EvnLabSample_DelivDT'},
				{name: 'DefectCauseType_id'}
			]),
			url: '/?c=EvnLabSample&m=save'
		});
		Ext.apply(this,
		{
			buttons:
			[{
				handler: function()
				{
                    win.doSave();
				},
				cls: 'newInGridButton save',
				iconCls: 'save16',
				text: lang['sohranit']
			},
			{
				text: '-'
			},
			/*HelpButton(this),*/
				{text: BTN_FRMHELP,
				cls: 'newInGridButton help',
			    iconCls: 'help16',
			    handler: function(button, event) {
					ShowHelp(win.title);
				}.createDelegate(this)
			},			   
			{
				handler: function()
				{
					this.ownerCt.hide();
				},
				cls: 'newInGridButton close',
				iconCls: 'cancel16',
				text: lang['zakryit']
			}],
			items: [
				this.LabSampleEditForm
			]
		});
		sw.Promed.swLabSampleEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		var win = this;
		sw.Promed.swLabSampleEditWindow.superclass.show.apply(this, arguments);
		
		this.action = null;
		this.ARMType = null;
		this.callback = Ext.emptyFn;
		this.remoteCallback = Ext.emptyFn;
		this.EvnLabRequest_id = null;
		this.EvnLabSample_id = null;

        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { this.hide(); }.createDelegate(this));
            return false;
        }

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].remoteCallback ) {
			this.remoteCallback = arguments[0].remoteCallback;
		}

		if ( arguments[0].formParams.EvnLabSample_id ) {
			this.EvnLabSample_id = arguments[0].formParams.EvnLabSample_id;
		}

		if ( arguments[0].formParams.EvnLabSample_Num ) {
			this.oldEvnSampleNum = arguments[0].formParams.EvnLabSample_Num;
		}

		if ( arguments[0].formParams.EvnLabRequest_id ) {
			this.EvnLabRequest_id = arguments[0].formParams.EvnLabRequest_id;
		}
		
		this.Person_id = arguments[0].Person_id || null;
		this.MedService_id = arguments[0].MedService_id || null;
		this.EvnDirection_id = arguments[0].EvnDirection_id || null;
		this.UslugaComplexTarget_id = arguments[0].UslugaComplexTarget_id || null;

		win.syncShadow();

        win.getLoadMask(lang['zagruzka_dannyih_formyi']).show();

		if (this.action == 'view') {
			this.enableEdit(false);
		} else {
			this.enableEdit(true);
		}

		var base_form = this.LabSampleEditForm.getForm();
		base_form.reset();
		base_form.setValues(arguments[0].formParams);
		
		base_form.findField('EvnLabSample_IsDefect').fireEvent('check', base_form.findField('EvnLabSample_IsDefect'), base_form.findField('EvnLabSample_IsDefect').getValue());
		
        var params = {
            EvnLabSample_id: this.EvnLabSample_id
        };
		
		this.formParams = arguments[0].formParams;
		getCurrentDateTime({
			callback: function(r) {
				if (r.success) {
					this.date = r.date;
					this.time = r.time;
				}
				
				// все что ниже - мутная хрень, пробую рассеять тьму
				var p; // параметры раздела "Взятие пробы" АРМ пункта забора
				var p1; // параметры раздела "Выполнение анализа" АРМ лаборанта
				if (
					(Ext.isEmpty(this.formParams.EvnLabSample_setDT)) //проба не сохранена
				) {
					//ставим умолчания на полях раздела "Взятие пробы"
					p = getGlobalOptions();
					
					//дата взятия - сегодня
					
					base_form.findField('EvnLabSample_setDT').setValue(Date.parseDate(this.date + ' ' + this.time,'d.m.Y H:i'));
					
					//ЛПУ взявшее пробу	Lpu_did - текущее ЛПУ
					if ( this.ARMType != 'pzm' ) { // Если не пункт забора материала
						base_form.findField('EvnLabSample_DelivDT').setValue(Date.parseDate(this.date + ' ' + this.time,'d.m.Y H:i'));
					}
				} else {
					// редактируем сохраненную или не сохраненную пробу
					p = {};
					p.lpu_id = this.formParams.Lpu_did;
					p.CurLpuSection_id = this.formParams.LpuSection_did;
					p.CurMedPersonal_id = this.formParams.MedPersonal_did;
					p.CurMedPersonal_sid = this.formParams.MedPersonal_sdid;
				}

				var recursive_load = function(combo, value, success, next_combo, next_load_options, next_combo_value) {
					if ( success ) {
						if (!value || !combo.getStore().getById(value)) {
							value = null;
						}
						combo.setValue(value);
						if ( !value ) {
							next_combo.setValue(null);
							next_load_options.callback(null, null, false); // переходим к загрузке следующего
							return true;
						} else {
							next_combo.getStore().load(next_load_options);
							return true;
						}
					} else {
						next_load_options.callback(null, null, false);
					}
					
					win.getLoadMask().hide();
					return true;
				};

				// комбики раздела "Взятие пробы"
				var lpu_did_combo = base_form.findField('Lpu_did'); //ЛПУ взявшее пробу
				var lpusection_did_combo = base_form.findField('LpuSection_did'); //Отделение взявшее пробу
				var medpersonal_did_combo = base_form.findField('MedPersonal_did'); //Врач взявший пробу
				var medpersonal_sdid_combo = base_form.findField('MedPersonal_sdid'); //Средний медперсонал взявший пробу
				var medpersonal_sdid_combo_options = {
					callback: function(records, options, success) {
						if ( success ) {
							if (!p.CurMedPersonal_sid || !medpersonal_sdid_combo.getStore().getById(p.CurMedPersonal_sid)) {
								p.CurMedPersonal_sid = null;
							}
							medpersonal_sdid_combo.setValue(p.CurMedPersonal_sid);
						}

						win.getLoadMask().hide();
					},
					//hideNotWork - Флаг сокрытия неработающих сотрудников
					params: { LpuSection_id: p.CurLpuSection_id, Lpu_id: p.lpu_id, withPosts: "6", MedPersonalNotNeeded: "true", hideNotWork: true }
				};
				var medpersonal_did_combo_options = {
					callback: function(records, options, success) {
						recursive_load(medpersonal_did_combo, p.CurMedPersonal_id, success,
							medpersonal_sdid_combo, medpersonal_sdid_combo_options, 1);
					},
					params: { LpuSection_id: p.CurLpuSection_id, Lpu_id: p.lpu_id, withPosts: "1", MedPersonalNotNeeded: "true", hideNotWork: true }
				};
				var lpusection_did_combo_options = {
					callback: function(records, options, success) {
						recursive_load(lpusection_did_combo, p.CurLpuSection_id, success,
							medpersonal_did_combo, medpersonal_did_combo_options, p.CurMedPersonal_id);
					},
					params: { Lpu_id: p.lpu_id}
				};
				var lpu_did_combo_options = {
					callback: function(records, options, success) {
						recursive_load(lpu_did_combo, p.lpu_id, success,
							lpusection_did_combo, lpusection_did_combo_options, p.CurLpuSection_id);
					},
					params: { Lpu_oid: p.lpu_id, OrgType: 'lpu' }
				};

				// начинаем загрузку комбиков разделов "Взятие пробы"
				lpu_did_combo.getStore().removeAll();
				lpusection_did_combo.getStore().removeAll();
				medpersonal_did_combo.getStore().removeAll();
				medpersonal_sdid_combo.getStore().removeAll();
				lpu_did_combo.setValue(null);
				lpusection_did_combo.setValue(null);
				medpersonal_did_combo.setValue(null);
				medpersonal_sdid_combo.setValue(null);
				lpu_did_combo.getStore().load(lpu_did_combo_options);
				medpersonal_sdid_combo.getStore().load(medpersonal_sdid_combo_options);
				//base_form.findField('EvnLabRequest_BarCode').setValue(base_form.findField('EvnLabSample_Num').getValue());

			}.createDelegate(this)
		});

        this.LabSampleEditForm.getForm().findField('EvnLabSample_Num').focus(true,2000);
	},
	title: lang['parametryi_probyi']

});