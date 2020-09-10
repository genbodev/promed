/*
 * Новый АРМ Диспетчера вызовов
 */
Ext.define('common.DispatcherCallWP.swDispatcherCallWorkPlace', {
	extend: 'Ext.window.Window',
	alias: 'widget.swDispatcherCallWorkPlace',
    //autoShow: true,
	maximized: true,
	width: 1000,
	refId: 'smpdispatchcall',
	findWindow: false,
	closable: true,
	closeAction: 'hide',
	time: 0,
	objectClass: 'swWorkPlaceSMPDispatcherCallWindow',
	refreshCodeWithDependecies: function(formData) {
		var objectClass = this.objectClass;
		var lastArguments = this.lastArguments;
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.destroy();
		window[this.objectName] = null;
		delete sw.Promed[this.objectName];

		// загружаем зависимости формы и выполняем их.
		Ext.Ajax.request({
			url: '/?c=promed&m=getJSFile',
			params: {
				wnd: objectClass,
				getDependecies: 1
			},
			callback: function(opt, success, response) {
				if (success) {
					// Читаем и пересоздаем (добавляем в DOM)
					if (response.responseText) {
						var result  = {success: false};
						try {
							var result  = Ext.JSON.decode(response.responseText);
							if ( result.success ) {
								var responseText = result.data;
							}
						} catch(e) {
							var responseText = response.responseText;
							result.success = true;
						}
						if ( result.success ) {
							try {
								globalEval(responseText);
								if (typeof callback == "function") {
									callback(success);
								}
							} catch(e) {
								if (IS_DEBUG==2)
									throw e;
								else {
									showFatalError(e, callback);
								}
							}
						}
					}
				}
			}
		});

		lastArguments.formData = formData;

		getWnd(objectClass).show(lastArguments);
	},
	baseCls: 'arm-window',
	title: 'АРМ диспетчера по приему вызовов',
    header: false,
	renderTo: Ext.getCmp('inPanel').body,
	callback:Ext.emptyFn,
	id: 'DispatchCallWorkPlace',
	layout: {
        type: 'fit'
    },
	constrain: true,
	onEsc: function(){					
		return false;
	},
	//@todo не должно быть этого здесь... предстоит рефактор
	show: function() {
		var me = this;

		if (arguments && arguments[0]) {
			me.lastArguments = arguments[0];
		}

		if(arguments[0] && arguments[0].showByDP) {
			if (arguments[0].params && arguments[0].params.typeEditCard) me.setTitle('Новый вызов');
			else me.setTitle('Новый вызов');
		}

		me.callParent(arguments);

		//отключаем сортировку в CmpCallerType
		if(getRegionNick() === 'ufa' ) {
			var cmpCallerType = this.BaseForm.getForm().findField('CmpCallerType_id');
			cmpCallerType.getStore().sorters.clear();
		}


	/*	if (arguments[0] && arguments[0].showByDP) {
			me.additionalParams = {
				showByDP: arguments[0].showByDP
			};

			if(arguments[0].params){
				//вызов с параметрами
				me.showWithParams(arguments[0].params);
			}else{
				//новый вызов без параметров
				me.showByDp();
			}
		}
		if (arguments[0] && arguments[0].swDispatcherCallWorkPlaceInstance_modal) {
			me.swDispatcherCallWorkPlaceInstance.modal = arguments[0].swDispatcherCallWorkPlaceInstance_modal;
		}

		if (arguments[0] && arguments[0].onSaveByDp && !me.hasListeners.savebydp) {
			me.on('saveByDp', arguments[0].onSaveByDp);
		}
		if (arguments[0] && arguments[0].onClose) {
			me.on('close', arguments[0].onClose);
		}

		if (arguments[0] && arguments[0].formData) {
			this.extraParams = arguments[0].formData;
			if (me.extraParams.dCityCombo) {
				this.extraParams.Town_id = me.extraParams.dCityCombo;
			}
			if (me.extraParams.dStreetsCombo) {
				this.extraParams.StreetAndUnformalizedAddressDirectory_id = me.extraParams.dStreetsCombo;
			}

			var bf = this.BaseForm.getForm();
			bf.setValues(me.extraParams);

			setTimeout(function(){
				bf.clearInvalid(); // костыль, чтобы поле зелёным не светилось :(
			}, 1000);
		}*/
	},
    initComponent: function() {
        var me = this,
			fieldLabels = {},
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType;

		me.isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp', 'nmpgranddoc']);
		//showByDp - метод загрузки из ДП
	/*	me.addEvents({
			showByDp: true,
			saveByDp: true,
			showWithParams: true
		});
		
		me.showByDp = function(){
			me.fireEvent("showByDp", me);
		};

		me.showWithParams = function(params){
			me.fireEvent("showWithParams", me, params);
		};
		*/
		switch (getGlobalOptions().region.nick) {
			case 'ufa':
			case 'krym':
			case 'kz':
				fieldLabels = {
					CmpCallCard_prmDate: 'Дата вызова',
					CmpCallCard_Numv: 'Номер обращения за день',
					CmpCallCard_prmTime: 'Время поступления обращения',
					CmpCallCard_Ngod: 'Номер обращения за год'
				};
				break;
			
			default:
				fieldLabels = {
					CmpCallCard_prmDate: 'Дата вызова',
					CmpCallCard_Numv: '№ вызова (за день)',
					CmpCallCard_prmTime: 'Время поступления вызова',
					CmpCallCard_Ngod: '№ вызова (за год)'
				};
		}

		var leftColumnLabelWidth = 150;
		
		var cityCombo = Ext.create('sw.dCityCombo', {
				mainAddressField: true,
				tabIndex: 5,
				refId: 'cityCombo',
				labelWidth: leftColumnLabelWidth,
				tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">'+
					'{Town_Name}'+
					'<span style="color:gray; font-size: 12px"> {Socr_Name}</span>'+
					'</br><span style="color:gray; font-size: 10px"> {Region_Name}</span>'+
					'<span style="color:gray; font-size: 10px"> {Region_Socr}</span>'+
					'</div></tpl>',
				displayTpl: '<tpl for="."><tpl if="{Region_Nick}">{[values.Region_Nick]}</tpl> ' +
				'<tpl if="{Region_Name}">{[values.Region_Name]}</tpl> ' +
				'<tpl if="{Socr_Nick}">{[values.Socr_Nick]}</tpl> ' +
				'<tpl if="{Town_Name}">{[values.Town_Name]}</tpl></tpl>'

			}),
			streetsCombo = Ext.create('sw.streetsSpeedCombo', {
				mainAddressField: true,
				tabIndex: 6,
				name:'dStreetsCombo',
				labelAlign: 'right',
				labelWidth: leftColumnLabelWidth,
				listConfig: {minWidth: 800, width: 800},
				defaultListConfig: {minWidth: 800, width: 800},
				forceSelection: (!getRegionNick().inlist(['krym'])),
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="enlarged-font x-boundlist-item">'+
						'{[ this.addressObj(values) ]}'+
					'</div></tpl>',
					{
						addressObj: function(val){
							//var city = ( getRegionNick().inlist(['perm', 'ekb', 'ufa']) ) ? val.Address_Name+' ' : '';
							var city = val.Address_Name+' ';

							if(val.UnformalizedAddressDirectory_id){
								var nameUnformalizedStreet = '';

								nameUnformalizedStreet += val.AddressOfTheObject ? val.AddressOfTheObject + ', ' : '';
								nameUnformalizedStreet += val.StreetAndUnformalizedAddressDirectory_Name ? val.StreetAndUnformalizedAddressDirectory_Name : '';

								return nameUnformalizedStreet;
							}else{
								return val.AddressOfTheObject +', ' + val.StreetAndUnformalizedAddressDirectory_Name + ' <span style="color:gray">' + val.Socr_Nick +'</span>';
								//return city + val.StreetAndUnformalizedAddressDirectory_Name + ' <span style="color:gray">' + val.Socr_Nick +'</span>';
							}
						}
					}
				),
				displayTpl: new Ext.XTemplate(
					'<tpl for=".">' +
						'{[ this.getDateFinish(values) ]}',
						'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>'+
					'</tpl>',
					{
						getDateFinish: function(val){
							if (val.UnformalizedAddressDirectory_id){
								var nameUnformalizedStreet = '';

								nameUnformalizedStreet += val.AddressOfTheObject ? val.AddressOfTheObject + ', ' : '';
								nameUnformalizedStreet += val.StreetAndUnformalizedAddressDirectory_Name ? val.StreetAndUnformalizedAddressDirectory_Name : '';

								return nameUnformalizedStreet;
							}
							else{
								return val.Socr_Nick + " " + val.StreetAndUnformalizedAddressDirectory_Name;
							}
						}
					}	
				),
				onTrigger1Click: function(e) {
					this.clearValue();
					this.store.clearFilter();
					this.focus();
					this.lastQuery='';
				},
			}),
            secondStreetCombo = Ext.create('sw.streetsSpeedCombo', {
				mainAddressField: true,
				tabIndex: 7,
				flex: 1,
				name:'secondStreetCombo',
                hidden: true,
                fieldLabel: 'Улица',
				labelAlign: 'right',
				enableKeyEvents : true,
				labelWidth: leftColumnLabelWidth,
				listConfig: {minWidth: 800, width: 800},
				defaultListConfig: {minWidth: 800, width: 800},
				forceSelection: (!getRegionNick().inlist(['krym'])),

                tpl: new Ext.XTemplate(
					'<tpl for="."><div class="enlarged-font x-boundlist-item">'+
						'{[ this.addressObj(values) ]} '+
					'</div></tpl>',
					{
						addressObj: function(val){
							var city = val.Address_Name+' ';

							if(val.UnformalizedAddressDirectory_id){
								return val.AddressOfTheObject + ', ' + val.StreetAndUnformalizedAddressDirectory_Name;
							}else{
								return val.AddressOfTheObject +', ' + val.StreetAndUnformalizedAddressDirectory_Name + ' <span style="color:gray">' + val.Socr_Nick +'</span>';
							}
						}
					}
				),
				displayTpl: new Ext.XTemplate(
					'<tpl for=".">' +
						'{[ this.getDateFinish(values) ]} ',
						'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
					'</tpl>',
					{
						getDateFinish: function(val){
							if (val.UnformalizedAddressDirectory_id){
								return val.AddressOfTheObject + ', ' + val.StreetAndUnformalizedAddressDirectory_Name;
							}
							else{
								return val.Socr_Nick + " " + val.StreetAndUnformalizedAddressDirectory_Name;
							}
						}
					}
				)
            }),
			cmpCallPlaceCombo = Ext.create('sw.CmpCallPlaceType', 
			{
				name:'CmpCallPlaceType_id', 
				fieldLabel: 'Тип места вызова',
				labelAlign: 'right',
				labelWidth: leftColumnLabelWidth,
				tabIndex: 13,
				triggerClear: true,
				//hideTrigger:true,
				//editable: false,
				autoFilter: true,
				typeAhead: true,
				codeField: 'CmpCallPlaceType_Code',
				minChars: 1,
				fields: [
					{name: 'CmpCallPlaceType_id', type:'int'},
					{name: 'CmpCallPlaceType_Code', type:'int'},
					{name: 'CmpCallPlaceType_Name', type:'string'}
				],
				displayTpl: '<tpl for="."> {CmpCallPlaceType_Code}. {CmpCallPlaceType_Name} </tpl>',
				tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">'+
					'<font color="red">{CmpCallPlaceType_Code}</font> {CmpCallPlaceType_Name}'+
					'</div></tpl>'
			}),
			callTypeCombo = Ext.create('swCmpCallTypeCombo', {
				flex: 2,
				name: 'CmpCallType_id',
				margin: '0 10 0 0',
				//tabIndex: (!getRegionNick().inlist(['ufa', 'krym', 'kz']))?27:16,
				tabIndex: 28,
				labelWidth: leftColumnLabelWidth,
				//fieldLabel: ((getRegionNick() == 'ufa' || getGlobalOptions().region.nick == 'krym') ? 'Тип вызова' : 'Тип вызова (F7-повт.)'),
				fieldLabel: 'Тип обращения',
				forceSelection: true,
				autoFilter: true,
				editable: false,
				activeTypeCodeArr: [1, 6, 15, 16, 19], // 1. Первичный; 6. Консультативный; 15. Справка; 16. Абонент отключился; 19. Актив
				isCodeIn: function(arr) {
					var rec = this.getSelectedRecord();
					if(!rec) return false;
					return rec.get(this.codeField).inlist(arr);
				},
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="enlarged-font x-boundlist-item">'+
					'{[this.activeType(values)]}'+
					'</div></tpl>',
					{
						activeType: function(val){
							if(callTypeCombo.activeTypeCodeArr.indexOf(val.CmpCallType_Code) !== -1){
								return '<font color="red">'+val.CmpCallType_Code+'.</font> ' + val.CmpCallType_Name + '</font>';
							}else{
								return '<font color="gray">' + val.CmpCallType_Code + '. ' + val.CmpCallType_Name + '</font>';
							}
						}
					}
				),
				listeners: {
					beforeselect: function(combo, record, index){
						if( callTypeCombo.activeTypeCodeArr.indexOf(record.data.CmpCallType_Code) === -1){
							return false;
						}
					}
				}
			}),
			smpUnitsCombo = Ext.create('sw.SmpUnitsNested',{
				name: 'LpuBuilding_id',
				//allowBlank: false, // для Уфы отключили обязательный ввод подразделения СМП
				tabIndex: 33,
				hidden: me.isNmpArm,
				autoFilter: true,
				labelWidth: leftColumnLabelWidth,
				displayTpl: '<tpl for="."> {LpuBuilding_Code}. {LpuBuilding_Name} </tpl>',
				tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">'+
					'<font color="red">{LpuBuilding_Code}</font> {LpuBuilding_Name}'+
					'</div></tpl>',
				onTrigger1Click: function() {
					this.clearValue();
					this.store.clearFilter();
					this.focus();
					this.fireEvent('select');
				},
			}),
			smpRegionUnitsCombo = Ext.create('sw.RegionSmpUnits',{
				name: 'LpuBuilding_id',
				labelWidth: leftColumnLabelWidth,
				hidden: me.isNmpArm,
				flex: 1,
				tabIndex: 33,
				autoFilter: true,
				fieldLabel: 'Подразделение СМП',
				displayTpl: '<tpl for=".">{LpuBuilding_Name}/{Lpu_Nick}</tpl>',
				tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">'+
				'{LpuBuilding_Name}/{Lpu_Nick}'+
				'</div></tpl>'
			}),
			
			smpCallerTypeCombo = Ext.create('sw.CmpCallerTypeCombo',{
				name: 'CmpCallerType_id',
				tabIndex: 15,
				labelWidth: leftColumnLabelWidth,
				triggerClear: true,
				//hideTrigger:true,
				autoFilter: getRegionNick() == 'ufa' ? false : true,
				forceSelection: false,
				autoSelect: false,
				minChars:2,
				validator: function(){if(!this.allowBlank && this.getValue()){return this.getValue().toString().trim().length > 0} return true},
				tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">'+
					'{CmpCallerType_Name}'+
					'</div></tpl>'
			});

		smpUnitsCombo.getStore().getProxy().url = '/?c=CmpCallCard4E&m=loadSmpUnitsNestedALL';
		smpUnitsCombo.getStore().getProxy().extraParams = {};


		var lpuLocalCombo = Ext.create('sw.lpuLocalCombo', {
			tabIndex: 29,
            flex: 1,
			bigFont: true,
			autoFilter: true,
			enableKeyEvents: true,
			name: 'lpuLocalCombo',
			labelWidth: leftColumnLabelWidth,
			fieldLabel: 'МО передачи'//НМП
		});

		var nmpCombo = Ext.create('sw.selectNmpCombo', {
			tabIndex: 30,
            flex: 1,
			enableKeyEvents : true,
			autoFilter: true,
			name: 'selectNmpCombo',
			labelWidth: leftColumnLabelWidth,
			fieldLabel: 'Служба НМП',
			isClose: 1
		});
			
		var pacientSearchResText = Ext.create('Ext.panel.Panel', {
			width: 300,
            //flex: 1,
			height: 31,
			name: 'status_panel',
			refId: 'pacientSearchResText',
			html: '',
			hidden: true
		});
		
		var diagnosesPersonOnDispText = Ext.create('Ext.panel.Panel', {
			width: 700,
			minHeight: 74,
			name: 'diagnoses_panel',
			refId: 'diagnosesPersonOnDispText',
			html: '',
			hidden: true
		});

		var cmpIllegalActText = Ext.create('Ext.panel.Panel', {
			width: 700,
			minHeight: 74,
			name: 'illegalActPanel',
			refId: 'illegalActPanel',
			html: '654545454545454545454545454545',
			hidden: true
		});

		var isUfa = getRegionNick() === 'ufa';

		this.BaseForm = Ext.create('sw.BaseForm',{
			xtype: 'BaseForm',
			id: 'MainForm',
			refId: 'dispatchCallForm',
			autoMask: true,
			title: 'Новый вызов',
			items: [
				{
					xtype: 'container',
					layout: 'border',
					items:[
                    {
                        xtype: 'container',
                        region: 'north',
                        layout: {
                            type: 'hbox'
                        },
                        padding: '5 30 5 5',
					    items: [
                        {
                            xtype: 'datefield',
                            fieldLabel: fieldLabels.CmpCallCard_prmDate,
							disabled: (getRegionNick() == 'ufa'),
                            labelAlign: 'right',
                            labelWidth: 120,
                            allowBlank: false,
                            flex: 1,
                            minWidth: 230,
                            format: 'd.m.Y',
                            plugins: [new Ux.InputTextMask('99.99.9999')],
                            name: 'CmpCallCard_prmDate',
                            tabIndex: 1
                        },
                        {
                            xtype: 'datefield',
                            name: 'CmpCallCard_prmTime',
                            //fieldLabel: fieldLabels.CmpCallCard_prmTime,
                            fieldLabel: 'Время',
                            format: 'H:i:s',
                            //flex: 1,
							disabled: (getRegionNick() == 'ufa'),
                            labelWidth: 70,
                            width: 150,
                            allowBlank: false,
                            hideTrigger: true,
                            invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                            plugins: [new Ux.InputTextMask('99:99:99')],
                            labelAlign: 'right',
                            tabIndex: 3
                        },
                        {
                            xtype: 'numberfield',
                            hideTrigger: true,
                            disabled: ((getRegionNick() == 'perm')?true:false),
                            keyNavEnabled: false,
                            mouseWheelEnabled: false,
                            //flex: 1,
                            //fieldLabel: fieldLabels.CmpCallCard_Numv,
                            fieldLabel: 'Номер за день',
                            labelAlign: 'right',
                            name: 'CmpCallCard_Numv',
                            labelWidth: 120,
                            minWidth: 180,
                            maxWidth: 190,
                            flex: 1,
                            tabIndex: 2
                        },
                        {
                            xtype: 'numberfield',
                            disabled: ((getRegionNick() == 'perm')?true:false),
                            hideTrigger: true,
                            keyNavEnabled: false,
                            mouseWheelEnabled: false,
                            minWidth: 190,
                            flex: 1,
                            //fieldLabel: fieldLabels.CmpCallCard_Ngod,
                            fieldLabel: 'Номер за год',
                            labelAlign: 'right',
                            name: 'CmpCallCard_Ngod',
                            tabIndex: 4
                        },
                           /*
						{
							xtype: 'container',
							margin: '0 10 0 15',
							minWidth: 40,
							flex: 1,
							maxWidth: 80,
							items: [
								{
									id: this.id+'_ProfileLabel',
									height: 50,
									cls: 'font-30px',
									xtype: 'label',
									text: 'ПР:'
								},
							]
						},
						*/
                        {
                            id: this.id+'_UrgencyLabel',
                            xtype: 'combobox',
                            flex: 1,
							hidden: me.isNmpArm,
                            minWidth: 150,
                            fieldLabel: 'Срочность',
                            labelWidth: 90,
                            hiddenName: 'CmpCallCard_Urgency',
                            disabled: getRegionNick() != 'krym' ? true : false,
                            displayField: "name",
							name: 'UrgencyLabel',
                            store: Ext.create('Ext.data.Store', {
                                fields: ['name'],
                                data : [
                                    {"name":"1"},
                                    {"name":"2"},
                                    {"name":"3"}
                                ]
                            })
                        },
                        {
                            xtype: 'textfield',
                            id: this.id+'_ProfileLabel',
							disabled: true,
                            labelAlign: 'right',
                            flex: 1,
                            minWidth: 150,
                            labelWidth: 80,
                            fieldLabel: 'Профиль',
							name: 'ProfileLabel'
                        },
                        {
                            xtype: 'button',
                            name: 'showCard112',
                            text: 'Карточка 112 (F8)',
                            hidden: true,
                            id: 'showCard112',
                            iconCls: 'card112_16',
                            height: 27
                        },
						{
							xtype: 'hidden',
							value: '',
							name: 'ARMType'
						},
						{
							xtype: 'hidden',
							value: 'default',
							name: 'typeEditCard'
						},
						{
							xtype: 'hidden',
							value: null,
							name: 'CmpCallCard_IsActiveCall'
						},
						{
							xtype: 'hidden',
							value: 0,
							name: 'CmpCallCard_id'
						},
						{
							name: 'CmpLpu_Name',
							value: '',
							xtype: 'hidden'
						},
						{
							name: 'CmpLpu_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Person_Age',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Person_IsUnknown',
							xtype: 'hidden'
						},
						{
							name: 'Person_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Area_id',
							value: 0,
							xtype: 'hidden'
						},
						/*{
							name: 'Street_id',
							value: 0,
							xtype: 'hidden'
						},*/
						{
							name: 'CmpCallCard_Ulic',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Person_isOftenCaller',
							value: 1,
							xtype: 'hidden'
						},
						{
							name: 'Person_deadDT',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_CallLtd',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_CallLng',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCardStatus_Comment',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpRejectionReason_id',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_DateTper',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_DateVyez',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_DatePrzd',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_DateTgsp',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_DateTsta',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_DateTisp',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_DateTvzv',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_HospitalizedTime',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'EmergencyTeam_id',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCardStatusType_id',
							value: null,
							xtype: 'hidden'
						},
						{
							name: 'CmpCallCard_IsDeterior',
							value: null,
							xtype: 'hidden'
						},
                        {
                            name: 'Reason_isNMP',
                            value: 0,
                            xtype: 'hidden'
                        },
                        {
                            name: 'CmpCallCard_sid',
							value: null,
                            xtype: 'hidden'
                        },
						{
                            name: 'AmbulanceDecigionTree_id',
							value: null,
                            xtype: 'hidden'
                        }
					]
				},
				{
					xtype: 'container',
					region: 'center',
					id: 'mainContainer',
					margin: '0 0 0 10',

					//autoScroll: true,
					//overflowY: 'scroll',
					layout: {
						align: 'stretch',
						type: 'auto'
					},
					items:[{
						xtype: 'container',
						layout: {
							//align: 'stretch',
							type: 'hbox'
						},
						flex: 3,
						margin: '0 0 0 0',

						items:[{
							flex: 1,
							xtype: 'container',
							margin: '0 0 0 10',

							autoScroll: true,
							layout: {
								align: 'stretch',
								type: 'vbox'
							},
							items: [
								{
									xtype: 'fieldset',
									autoRender: false,
									flex: 1,
									id: 'callPalceFS',
									defaultAlign: 'left',
									layout: {
										align: 'stretch',
										type: 'vbox'
									},
									defaults: {
										labelWidth: leftColumnLabelWidth
									},
                                    padding: '0 30 0 0',
									title: 'Место вызова',
									items: [
										cityCombo,
										streetsCombo,
                                        {
                                            xtype: 'fieldcontainer',
                                            layout: {
                                                align: 'stretch',
                                                type: 'hbox'
                                            },
                                            autoShow: false,
                                            //margin: '4 0 10',
                                            items:[
												secondStreetCombo,
												{
													xtype: 'textfield',
													plugins: [new Ux.Translit(true, true)],
													fieldLabel: 'Дом',
													labelWidth: leftColumnLabelWidth,
													minWidth: 200,
													flex: 1,
													labelAlign: 'right',
													name: 'CmpCallCard_Dom',
													enableKeyEvents : true,
													mainAddressField: true,
													tabIndex: 8
												},
                                                {
                                                     xtype: 'textfield',
                                                     plugins: [new Ux.Translit(true, true)],
                                                     fieldLabel: 'Корпус',
                                                     enforceMaxLength: true,
                                                     maxLength: 5,
                                                     labelWidth: 70,
                                                     flex: 1,
                                                     minWidth: 100,
                                                     // hidden: (!getRegionNick().inlist(['ufa', 'krym', 'kz'])),
                                                     labelAlign: 'right',
                                                     name: 'CmpCallCard_Korp',
                                                     enableKeyEvents : true,
                                                     mainAddressField: true,
                                                     tabIndex: 9
                                                },
                                                {
                                                    xtype: 'textfield',
                                                    //maskRe: /[0-9:]/,
                                                    enforceMaxLength: true,
                                                    maxLength: 5,
                                                    plugins: [new Ux.Translit(true, true)],
                                                    fieldLabel: 'Кварт',
                                                    labelAlign: 'right',
                                                    name: 'CmpCallCard_Kvar',
                                                    enableKeyEvents : true,
                                                    mainAddressField: true,
                                                    tabIndex: 9,
                                                    labelWidth: 50,
                                                    minWidth: 100,
                                                    flex: 1,
                                                },
                                                {
                                                    xtype: 'textfield',
                                                    maskRe: /[0-9:]/,
                                                    fieldLabel: 'Подъезд',
                                                    labelAlign: 'right',
                                                    name: 'CmpCallCard_Podz',
                                                    enableKeyEvents : true,
                                                    tabIndex: 10,
                                                    flex: 1,
                                                    minWidth: 120,
                                                    labelWidth: 70,
                                                },

                                            ]
                                        },
                                        {
                                            xtype: 'fieldcontainer',
                                            layout: {
                                                align: 'stretch',
                                                type: 'hbox'
                                            },
                                            items: [
                                                {
                                                    xtype: 'textfield',
                                                    maskRe: /[0-9:]/,
                                                    fieldLabel: 'Этаж',
                                                    labelAlign: 'right',
                                                    name: 'CmpCallCard_Etaj',
                                                    enableKeyEvents : true,
                                                    tabIndex: 11,
                                                    minWidth: 100,
                                                    flex: 1,
                                                    labelWidth: leftColumnLabelWidth,
                                                },
                                                {
                                                    xtype: 'textfield',
                                                    //maskRe: /[0-9:]/,
                                                    fieldLabel: 'Домофон/Код',
                                                    labelAlign: 'right',
                                                    name: 'CmpCallCard_Kodp',
                                                    enableKeyEvents : true,
                                                    tabIndex: 12,
                                                    flex: 1,
                                                    labelWidth: 150,
                                                }
                                            ]
                                        },

										cmpCallPlaceCombo,
										{
											xtype: 'textfield',
											width: 300,
											fieldLabel: 'Телефон',
											enableKeyEvents : true,
											maskRe: /[0-9:]/,
											labelAlign: 'right',
											name: 'CmpCallCard_Telf',
											tabIndex: 14,
											enforceMaxLength: true,
											invalidText: '232'
										},
										smpCallerTypeCombo,
										{
											//убрал area #87385
											//xtype: 'textareafield',
											xtype: 'textfield',
											//flex: 1,
											//plugins: [new Ux.Translit(true)],
											minHeight: 15,
											//height: 40,
											fieldLabel: 'Доп. информация',
											enableKeyEvents : true,
											labelAlign: 'right',
											refId: 'CmpCallCard_Comm',
											name: 'CmpCallCard_Comm',
											tabIndex: 16
										},
										/*{
											id: this.id+'_OopLabel',
											labelWidth: 450,
											height: 50,
											style: 'font-size:20px;',
											xtype: 'label',
											text: ''
										},
                                        */
									]
								}
							]
						},
						{
							xtype: 'container',
							flex: 1,
							margin: '0 0 0 10',
							//defaultAlign: 'left',
							layout: {
								align: 'stretch',
								type: 'vbox'
							},
                            padding: '0 30 0 0',
							items: [
								{
									xtype: 'fieldset',
									id: 'clientInfoFS',
									layout: {
										type: 'vbox',
                                        align: 'stretch',
									},
                                    minHeight: 318,
									flex: 1,
                                    padding: '0 30 0 0',
									title: 'Пациент',
									items: [
										{
											xtype: 'container',
											flex: 1,
											layout: {
												type: 'hbox',
												align: 'stretch'
											},
											items: [
												{
													xtype: 'container',
                                                    flex: 1,
													layout: {
														type: 'vbox',
														align: 'stretch'
													},
                                                    defaults: {
                                                        labelWidth: 120,
                                                    },
													items: [
														{
															xtype: 'cmpReasonCombo',
															name: 'CmpReason_id',
															forceSelection: true,
															fieldLabel: 'Повод/Отказ',
															listConfig: {minWidth: 600, width: 600},
															//hidden: (!getRegionNick().inlist(['perm'])),															
															hidden: false,
															bigFont: true,
															allowBlank: false,
															tabIndex: 20,
															isCVIReason: function() {
																var rec = this.getSelectedRecord();
																return rec ? rec.get('CmpReason_Code') === 'НГ1' : false;
															},
															onTriggerClick: function() {
																var me = this;

																me.duringTriggerClick = true;
																if (!me.readOnly && !me.disabled) {
																	if (me.isExpanded) {
																		me.collapse();
																	} else {
																		me.onFocus({});
															 			if(me.getValue()){
																			if (me.triggerAction === 'all') {
																				me.doQuery(me.allQuery, true);
																			} else if (me.triggerAction === 'last') {
																				me.doQuery(me.lastQuery, true);
																			} else {
																				me.doQuery(me.getRawValue(), false, true);
																			}
																		}
																	}
																	me.inputEl.focus();
																}
																delete me.duringTriggerClick;
															},
															tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">' +
																'<font color="red">{CmpReason_Code}.</font> {CmpReason_Name} </div></tpl>',
															listeners: {
																expand: function(combo){

																	if(getRegionNick().inlist(['ufa'])){
																		combo.store.sort([
																			{
																				sorterFn: function(v1,v2){
																					var num1 =  v1.get('CmpReason_Code').match(/\d{1,}/);
																					var num2 =  v2.get('CmpReason_Code').match(/\d{1,}/);
																					var str1 =  v1.get('CmpReason_Code').match(/[А-Я]{1,}/g);
																					var str2 =  v2.get('CmpReason_Code').match(/[А-Я]{1,}/g);

																					return (str1 && str2 && str1[0] != str2[0]) ? 0 : num1 - num2;

																				}
																			}
																		])
																	}

																},
															}
														},
														{
															xtype: 'swUnformalizedAddressDirectoryCombo',
															name: 'UnformalizedAddressDirectory_wid',
															fieldLabel: 'МО перевозки',
															type_id: 7,
															tabIndex: 21,
															autoFilter: true,
															hidden: true,
															bigFont: true
														},
														// наверное позже можно будет выпилить
														// т.к. в контроллере остались только фокусы на это поле
														// код причины (для НМП) теперь берется напрямую из стора в контроллере
														{
															xtype: 'textfield',
//															plugins: [new Ux.Translit(true, true)],
															width: 250,
															fieldLabel: 'Повод / Отказ(F9)',
															labelAlign: 'right',
															name: 'CmpReason_Name',
															enableKeyEvents : true,
															tabIndex: 20,
															tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">' +
															'<font color="red">{CmpReason_Code}.</font> {CmpReason_Name} </div></tpl>',
															hidden: true
															//hidden: (getRegionNick().inlist(['perm']))
														},
														{
															xtype: 'swDiag',
															name: 'Diag_uid',
															fieldLabel: 'Диагноз',
															labelAlign: 'right',
															hidden: true,
															matchFieldWidth: false,
															tabIndex: 20
														},
														{
															xtype: 'textfield',
															plugins: [new Ux.Translit(true, true)],
															width: 250,
															fieldLabel: 'Фамилия',
															labelAlign: 'right',
															name: 'Person_Surname',
															enableKeyEvents : true,
															tabIndex: 22,
															showWnd: false,
															/*
															валидность поля устанавливается в контроллере, метод setAllowBlankDependingCallType
															validator: function(){
																return this.getValue().trim().length > 0
															}*/
														},
														{
															xtype: 'textfield',
															plugins: [new Ux.Translit(true, true)],
															width: 250,
															fieldLabel: 'Имя',
															labelAlign: 'right',
															name: 'Person_Firname',
															enableKeyEvents : true,
															tabIndex: 23
														},
														{
															xtype: 'textfield',
															plugins: [new Ux.Translit(true, true)],
															width: 250,
															fieldLabel: 'Отчество',
															labelAlign: 'right',
															name: 'Person_Secname',
															tabIndex: 24
														}
													]
												},
												/*
                                                {
													xtype: 'container',
													//width: 150,
													margin: '0 10',
													layout: {
														type: 'vbox'																
													},
													items: [
														{
															xtype: 'label',
															text: ' ',
															margin: '2 0',
															height: 27
														},
														{
															xtype: 'button',
															name: 'clearPersonFields',
															text: 'Сброс',
															id: 'CCCSEF_PersonResetBtn',
															iconCls: 'delete16',
															height: 27
														},
														{
															xtype: 'button',
															name: 'searchPersonBtn',
															text: (getRegionNick().inlist(['ufa', 'krym', 'kz']))?'Поиск':'Поиск (F3)',
															iconCls: 'search16',
															margin: '5 0',
															height: 27
														},
														{
															xtype: 'button',
															name: 'unknowPersonBtn',
															text: 'Неизвестен (F2)',
															iconCls: 'warning16',
															height: 27
														},
														{
															xtype: 'button',
															name: 'personByAddressBtn',
															text: 'Проживают по адресу вызова (F6)',
															margin: '5 0',
															iconCls: 'search16',
															height: 27
														}
													]
												}*/
											]
										},
										{
											xtype: 'container',
											flex: 1,
											layout: {
												type: 'vbox',
                                                align: 'stretch'
											},

											items: [
												{
													xtype: 'container',													
													layout: {
                                                        type: 'hbox',
                                                        align: 'stretch'
                                                    },
                                                    defaults: {
                                                        labelWidth: 120
                                                    },
                                                    maxWidth: 700,
													margin: '0 0 10',
													items:[
                                                    {
														xtype: 'numberfield',
														fieldLabel: 'Возраст',														
														hideTrigger:true,
														tabIndex: 25,
														minValue: 1,
														maxValue: 3000,
														enableKeyEvents : true,
														keyNavEnabled: true,
														mouseWheelEnabled: false,
														maskRe: /[0-9]+/,
														name: 'Person_Birthday_YearAge',
                                                        flex: 1
													},
													{
														xtype: 'swDAgeUnitCombo',
														tabIndex: 26,
														value: 'years',
														fieldLabel: 'Ед.измерения возраста',
														hideLabel: true,
														name: 'ageUnit',
														displayField: 'ageUnit_name',
														enableKeyEvents : true,
                                                        bigFont: true,
														valueField: 'ageUnit_id',
                                                        width: 100,
                                                        triggerClear: false
													},
                                                    {
                                                        xtype: 'sexCombo',
                                                        enableKeyEvents : true,
                                                        tabIndex: 27,
                                                        width: 180,
                                                        name: 'Sex_id',
                                                        bigFont: true,
                                                        hideTrigger:false,
                                                        labelWidth: 50,
                                                        flex: 1
                                                    }
													]
												},
                                                {
                                                    xtype: 'container',
                                                    layout: {
                                                        type: 'hbox',
                                                        align: 'stretch'
                                                    },
                                                    defaults: {
                                                        labelWidth: 120
                                                    },
                                                    maxWidth: 700,
                                                    margin: '0 0 10 0',
                                                    items: [
                                                        {
                                                            xtype: "textfield",
                                                            //xtype: (!getRegionNick().inlist(['ufa', 'krym', 'kz']))?'textfield':'hidden',
                                                            fieldLabel: 'Полис',
                                                            labelAlign: 'right',
                                                           // width: 250,
                                                            flex: 1,
                                                            name: 'Polis_Number_fake',
                                                            disabled: true
                                                        },
                                                        {
                                                            xtype: 'panel',
                                                            //width: 300,
                                                            flex: 1,
                                                            height: 31,
                                                            name: 'status_panel',
                                                            refId: 'pacientSearchResText',
                                                            html: '',
                                                            //hidden: true
                                                        }
                                                       //pacientSearchResText
                                                    ]
                                                },
												{
													//hidden: !isUfa,
													xtype: 'container',
													layout: 'hbox',
													flex: 1,
													margin: '0 0 0 10',
													items: [
														{
															xtype: 'checkbox',
															//disabled: !isUfa,
															name: 'CmpCallCard_IsQuarantine',
															boxLabel: 'Карантин',
															margin: '0 10 0 10',
															hidden: true
														},
														{
															refId: 'ApplicationCVIBtn',
															xtype:'button',
															text: 'Анкета КВИ',
															cls: 'simple-button-link',
															width: 120,
															hidden: true
														}
													]
												},
												{
													refId: 'ApplicationCVI',
													xtype: 'fieldset',
													hidden: true,
													defaults: {
														xtype: 'hidden',
														//disabled: !isUfa,
													},
													items: [
														{ name: 'isSavedCVI' },
														{ name: 'PlaceArrival_id' },
														{ name: 'KLCountry_id' },
														{ name: 'OMSSprTerr_id' },
														{ name: 'ApplicationCVI_arrivalDate' },
														{ name: 'ApplicationCVI_flightNumber' },
														{ name: 'ApplicationCVI_isContact' },
														{ name: 'ApplicationCVI_isHighTemperature' },
														{ name: 'Cough_id' },
														{ name: 'Dyspnea_id' },
														{ name: 'ApplicationCVI_Other'}
													]
												},
												{
													xtype: 'hidden',
													fieldLabel: 'Дата рожденья',
													name: 'Person_Birthday'
												},
//												{
//													xtype: 'textfield',
//													plugins: [new Ux.Translit(true, true)],
//													width: 450,
//													fieldLabel: 'Серия полиса',
//													labelAlign: 'right',													
//													name: 'Polis_Ser',
//													enableKeyEvents : true,
//													hideTrigger: true,
//													keyNavEnabled: false,
//													mouseWheelEnabled: false,
//													//tabIndex: 20,
//													disabled: true
//												},
//												{
//													xtype: 'textfield',
//													width: 450,
//													fieldLabel: 'Номер полиса',
//													labelAlign: 'right',
//													name: 'Polis_Num',
//													maskRe: /[0-9]+/,
//													enableKeyEvents : true,
//													hideTrigger: true,
//													keyNavEnabled: false,
//													mouseWheelEnabled: false,
//													//tabIndex: 21,
//													disabled: true
//												},
//												{
//													xtype: 'textfield',
//													width: 450,
//													fieldLabel: 'Единый номер',
//													labelAlign: 'right',
//													name: 'Polis_EdNum',
//													maskRe: /[0-9]+/,
//													enableKeyEvents : true,
//													hideTrigger: true,
//													keyNavEnabled: false,
//													mouseWheelEnabled: false,
//													//tabIndex: 22,
//													disabled: true
//												},
												//pacientSearchResText,
                                                {
                                                    xtype: 'container',
                                                    layout: {
                                                        type: 'hbox',
                                                        align: 'stretch'
                                                    },
                                                    defaults: {
                                                        labelWidth: 120
                                                    },
                                                    maxWidth: 700,
                                                    height: 22,
                                                    margin: '0 0 10 0',
                                                    items: [
                                                        {
                                                            xtype: 'container',
                                                            //width: 300,
                                                            flex: 1,
                                                           // height: 31,
                                                            refId: 'pacientButtonHistory',
                                                            style: 'border: none;  background: none;',
                                                            html: '',
                                                            margin: '0 0 0 120'
                                                            //hidden: true
                                                        },
                                                        {
                                                            xtype: 'button',
                                                            name: 'clearPersonFields',
                                                            text: 'Сброс',
                                                            //id: 'CCCSEF_PersonResetBtn',
                                                            //iconCls: 'delete16',
                                                           // margin: '0 0 0 125',
                                                            padding: '0 10',
                                                            //height: 27,
                                                            width: 100
                                                        },
                                                    ]
                                                },
                                                diagnosesPersonOnDispText,
												cmpIllegalActText
											]
										}
									]
								}
							]
						}]
					},{
						flex: 1,
						xtype: 'container',
//								height: 320,
						margin: '0 0 0 10',
//								width: 400,
						defaultAlign: 'left',
						layout: {
							align: 'stretch',
							type: 'vbox'
						},
						items: [{
							xtype: 'fieldset',
							flex: 1,
							id: 'callInfoFS',
							layout: {
								align: 'stretch',
								type: 'vbox'
							},
                            margin: '0 30 0 0',
                            padding: '0 30 0 0',
							title: (getRegionNick().inlist(['ufa']))?'Обращение':'Вызов',
							defaults: {
								labelWidth: 230
							},
							items: [
								{
									xtype: 'container',
									//width: 150,

									layout: {
										type: 'hbox'
									},
									items: [
										callTypeCombo,
                                        {
                                            xtype: 'container',
                                            layout: {
                                                type: 'vbox'
                                            },
											flex: 2,
                                            items: [
                                                {
                                                    xtype: 'container',
                                                    layout: 'hbox',
													style: 'margin-left: 25px',
                                                    items: [
                                                        {
                                                            flex: 2,
                                                            margin: '0 0 0 10',
                                                            name: 'CmpCallCard_rid',
                                                            fieldLabel: '№ первичного обращения',	// id первичного вызова
                                                            xtype: 'textfield',
                                                            hidden: true,
                                                            labelWidth: 260,
                                                            regexText: 'Выберите первичный вызов в окне выбора возможных дублей',
                                                            //labelAlign: 'right',
                                                        },
                                                        {
                                                            flex: 2,
                                                            margin: '0 0 0 0',
                                                            name: 'CmpCallCard_DayNumberRid',		// номер за день первичного вызова
                                                            xtype: 'textfield',
                                                            fieldLabel: '№ первичного обращения',
                                                            labelWidth: 200,
                                                            labelAlign: 'right',
                                                            readOnly: true

                                                        },
														{
															flex: 2,
															margin: '0 0 0 10',
															xtype: 'button',
															name: 'cmpcallcard_ridBtn',
															text: 'Просмотр талона',
															hidden: true
														}
                                                    ]
                                                },
                                                {
                                                    xtype: 'container',
                                                    layout: 'hbox',
                                                    items:[
                                                        {
                                                            flex: 2,
                                                            margin: '15 0 0 -5',
                                                            name: 'CmpCallCard_storDateShow',
                                                            fieldLabel: 'Первичный вызов отложен до:',
                                                            xtype: 'datefield',
															format: 'd.m.Y',
                                                            hidden: true,
                                                            labelWidth: 230,
                                                            labelAlign: 'right',
															readOnly: true
                                                        },
                                                        {
                                                            flex: 2,
                                                            margin: '15 0 0 10',
                                                            name: 'CmpCallCard_storTimeShow',
                                                            xtype: 'datefield',
															format: 'H.i.s',
                                                            labelWidth: 200,
                                                            labelAlign: 'right',
                                                            readOnly: true,
                                                            hidden: true
                                                        }
                                                    ]
                                                }
                                            ]
                                        }
									]
								},
								{
									xtype: 'container',
									margin: '6 0',
									//width: 150,

									layout: {
										type: 'hbox'										
									},
									items: [
										{
											xtype: 'swCmpCallTypeIsExtraCombo',
                                            tabIndex: 29,
											fieldLabel: 'Вид вызова',
                                            bigFont: true,
											value: 1,
											enableKeyEvents : true,
											labelWidth: 150,
											//margin: '0 0 0 20',
											flex: 1,
											name: 'CmpCallCard_IsExtra',
											editable: true,
											allowBlank: false
										},
										{
											xtype: 'container',
											flex: 1,
											margin: '0 0 0 10',
											items: [
												{
													xtype: 'checkbox',
													flex: 1,
													name: 'CmpCallCard_IsPoli',
													hidden: me.isNmpArm,
													boxLabel: 'Вызов передан в поликлинику по телефону (рации)',
													margin: '0 0 0 10'
												}
											]
										}

									]
								},
                                {
                                    xtype: 'container',
                                    margin: '0 0 6',
                                    layout: {
                                        type: 'hbox'
                                    },
                                    items: [
                                        lpuLocalCombo,
                                        {xtype: 'container', flex: 1, margin: '0 0 0 10'}
                                    ]
                                },
                                {
                                    xtype: 'container',
                                    margin: '0 0 6',
                                    layout: {
                                        type: 'hbox'
                                    },
                                    items: [
                                        nmpCombo,
										{
											xtype: 'container',
											flex: 1,
											margin: '0 0 0 10',
											items: [
												{
													xtype: 'checkbox',
													name: 'CmpCallCard_IsPassSSMP',
													hidden: (getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1) || me.isNmpArm,
													boxLabel: 'Вызов передан в другую ССМП по телефону (рации)',
													margin: '0 0 0 10',
													flex: 1,
													tabIndex: 30
												}
											]
										}
                                    ]
                                },

								{
									xtype: 'lpuAllLocalCombo',
									name: 'Lpu_smpid',
									fieldLabel: 'МО передачи (СМП)',
									labelWidth: leftColumnLabelWidth,
									tabIndex: 31,
									margin: '0 0 10 20',
									autoFilter: true,
									hidden: true
								},
								(getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1) ? smpUnitsCombo : smpRegionUnitsCombo
								,
								{
									xtype: 'container',
									layout: {type: 'hbox'},
									margin: '10 0 10',
									items:[
										{
											xtype: 'checkbox',
											name: 'CmpCallCard_deferred',
											boxLabel: 'Отложенный вызов',
											margin: '0 10 10 155',
											tabIndex: 34
										},
										{
											xtype: 'datefield',
											fieldLabel: 'Дата',
											format: 'd.m.Y',
											width: 160,
                                            labelWidth: 60,
											plugins: [new Ux.InputTextMask('99.99.9999')],
											name: 'CmpCallCard_storDate'
										},
										{
											xtype: 'datefield',
											name: 'CmpCallCard_storTime',
											fieldLabel: 'Время',
											format: 'H:i:s',
											width: 160,
                                            labelWidth: 60,
											hideTrigger: true,
											invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ:CC',
											plugins: [new Ux.InputTextMask('99:99:99')]
										},
										{
											xtype: 'textfield',
											minHeight: 15,
											//height: 40,
											labelWidth: 110,
                                            flex: 1,
											fieldLabel: 'Комментарий',
											enableKeyEvents : true,
											name: 'CmpCallCard_defCom'
										}
									]
								}
							]
						}]
					}
					]
				},
                {
                    xtype: 'toolbar',
                    region: 'south',
                    //height: 40,
                    //id: 'footer',
                    margin: '0 0 40 10',
                    layout: 'fit',
                    items: [
                        {
                            xtype: 'container',
                            refId: 'bottomButtons',
                            //height: 40,
                            margin: '5 4',
                            layout: {
                                type: 'hbox',
                                align: 'stretch'

                            },
                            items: [
                                {
                                    xtype: 'button',
                                    id: 'helpBtn',
                                    tabIndex: 60,
                                    refId: 'helpBtn',
                                    text: 'F1 Помощь',
                                    flex: 1,
                                    handler: function () {
                                        ShowHelp(this.up('window').title);
                                    }
                                },
                                {
                                    xtype: 'button',
                                    refId: 'unknownBtn',
                                    // iconCls: 'cancel16',
                                    text: 'F2 Неизвестен',
                                    tabIndex: 61,
                                    flex: 1,
                                    margin: '0 5'
                                },
                                {
                                    xtype: 'button',
                                    refId: 'searchBtn',
                                    text: 'F3 Поиск',
                                    tabIndex: 62,
                                    flex: 1,
                                    margin: '0 5'
                                },
                                {
                                    xtype: 'button',
                                    refId: 'mapBtn',
                                    text: 'F4 Карта',
                                    tabIndex: 63,
                                    flex: 1,
                                    margin: '0 5'
                                },
                                {
                                    xtype: 'button',
                                    refId: 'searchByAddressBtn',
                                    text: 'F6 Поиск по адресу',
                                    tabIndex: 64,
									disabled: true,
									flex: 1,
                                    margin: '0 5'
                                },
                                {
                                    xtype: 'button',
                                    refId: 'MOserviceBtn',
                                    text: 'F7 МО обслуживания',
                                    hidden: !getRegionNick().inlist(['ufa','buryatiya']) ,
                                    flex: 1,
                                    tabIndex: 65,
                                    margin: '0 5'
                                },
                                {
                                    xtype: 'button',
                                    // refId: 'card112Btn',
                                    refId: 'callHistoryBtn',
                                    text: 'F8 История обращений',
                                    disabled: true,
                                    tabIndex: 66,
                                    flex: 1,
                                    margin: '0 5'
                                },
                                {
                                    xtype: 'button',
                                    refId: 'callSearchBtn',
                                    text: 'F9 Поиск вызова',
                                    tabIndex: 67,
                                    flex: 1,
                                    margin: '0 5'
                                },
								{
									xtype: 'button',
									refId: 'saveContinueBtn',
									text: 'ALT + F10 Сохранить и продолжить',
									tabIndex: 69,
									flex: 2,
									margin: '0 5'
								},
                                {
                                    xtype: 'button',
                                    refId: 'saveBtn',
                                    id: 'saveBtn',
                                    text: 'F10 Сохранить',
                                    tabIndex: 68,
                                    flex: 1,
                                    margin: '0 5'
                                },
                                {
                                    xtype: 'button',
                                    refId: 'cancelBtn',
                                    text: 'Esc Закрыть',
                                    tabIndex: 70,
                                    flex: 1,
                                    margin: '0 5'
                                }

                            ]
                        }

                        /*{
                         xtype: 'button',
                         refId: 'saveBtn',
                         iconCls: 'save16',
                         text: 'Сохранить',
                         id: 'saveBtn',
                         tabIndex: 29
                         },
                         {
                         xtype: 'button',
                         //id: 'helpBtn',
                         text: 'Помощь',
                         iconCls   : 'help16',
                         tabIndex: 30,
                         handler   : function()
                         {
                         ShowHelp(this.up('window').title);
                         //window.open('/wiki/main/wiki/Карта_вызова:_Добавление');
                         }
                         },{
                         xtype: 'button',
                         refId: 'cancelBtn',
                         iconCls: 'cancel16',
                         text: 'Закрыть',
                         margin: '0 5',
                         handler: function(){
                         this.up('window').close()
                         }
                         }*/

                    ]
                }
			]
			}]
		});

		var tabPanel = Ext.create('Ext.tab.Panel', {
			refId: 'mainTabPanelDW',
			border: false,
			items: [
				this.BaseForm
				,{
					xtype: 'container',
					flex: 1,
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					//id: 'callsListDW',
					refId: 'callsListDW',
					title: 'Журнал вызовов',
					items: [
						Ext.create('sw.CmpCallsList', {armtype: 'smpdispatchcall'})
					],
					listeners: {
						activate: function (tab) {
							var journal = tab.child();
							if(journal.store) journal.getStore().load();
						}
					}
				},{
					xtype: 'container',
					flex: 1,
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					//id: 'calls112ListDW',
					refId: 'calls112ListDW',
					hidden: true,
					title: 'Журнал карточек 112',

					items: [
						Ext.create('sw.CmpCalls112List')
					]
				}
			]
		});
		Ext.applyIf(me, {
			items: [
				tabPanel
			]
		});

		if (getRegionNick().inlist(['pskov'])) {
			this.BaseForm.getForm().findField('Diag_uid').store.load();
		}

		this.callParent(arguments);
    }

});

sw.Promed['swWorkPlaceSMPDispatcherCallWindow'] = common.DispatcherCallWP.swDispatcherCallWorkPlace;