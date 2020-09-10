Ext6.define('common.PolkaWP.RemoteMonitoring.ConsentWindow', {
	addCodeRefresh: Ext.emptyFn,
	closeToolText: 'Закрыть',

	alias: 'widget.swRemoteMonitoringConsentWindow',
	title: 'Согласие на участие в программе "Дистанционный мониторинг"',
	extend: 'base.BaseForm',
	maximized: false,
	width: 600,
	height: 322-80,
	modal: true,

	findWindow: false,
	closable: true,
	cls: 'arm-window-new emk-forms-window arm-window-new-without-padding',
	renderTo: Ext6.getBody(),
	layout: 'border',

	autoScroll: true,
	autoShow: false,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	
	loadData: function() {
		var me = this;
		if(Ext6.isEmpty(me.params.Person_id)) {
			Ext6.Msg.alert('Сообщение','Неверно указаны параметры');
			me.hide();
			return;
		}
		if(!me.LoadMask)
			me.LoadMask = new Ext6.LoadMask(me, {msg: LOAD_WAIT});
		me.LoadMask.show();
		Ext6.Ajax.request({
			params: {
				Person_id: me.params.Person_id
			},
			callback: function(options, success, response) {
				if (success) {
					var res = Ext6.JSON.decode(response.responseText);
					if(!Ext6.isEmpty(res.Error_Msg)) {
						Ext6.Msg.alert(langs('Ошибка'), res.Error_Msg);
					} else {
						var fio=res[0]['Person_FIO'];
						if(me.params.Person_Birthday) {
							fio+=' Д/Р: '+me.params.Person_Birthday;
							var dt=Date.parseDate(me.params.Person_Birthday,'d.m.Y');
							
						}
						
						me.queryById('personfio').setValue(res[0]['Person_FIO']+' Д/Р: '+me.params.Person_Birthday+' ('+getAgeString({
								Person_BirthDay: me.params.Person_Birthday,
								DateFormat: 'd.m.Y',
								Template: ' {letter}.',
								useYearLetterForElder: true
							})+')');
						me.queryById('phone').setValue(res[0]['Phone_Promed'] ? res[0]['Phone_Promed'] : res[0]['Phone_Site']);
						me.queryById('phone').isValid();
						me.LoadMask.hide();
					}
				}
			},
			url: '/?c=Person&m=getPersonPhoneInfo'
		});
	},
	show: function() {
		var me = this;
		me.callParent(arguments);
		me.FormPanel.reset();
		if(!arguments[0]) {
			me.errorInParams();
			return false;
		}
		
		me.params = arguments[0];
		me.FormPanel.reset();
		me.queryById('printbutton').setVisible(me.params.Label_id!=7);
		if(Ext6.isEmpty(arguments[0].PersonFio)) {//вызов не из мониторинга
			me.loadData();
		} else {
			
			me.queryById('personfio').setValue(arguments[0].PersonFio + 
				' Д/Р: ' + arguments[0].BirthDayFormatted + 
				' ('+arguments[0].AgeFormatted+')');
			me.queryById('phone').setValue(arguments[0].Person_Phone);
			me.queryById('phone').isValid();
		}
		me.queryById('dateConsent').setValue(Date.now());
	},
	print: function() {
		var me = this,
			form = me.FormPanel,
			base_form = me.FormPanel.getForm(),
			formdate = me.queryById('dateConsent').getValue().dateFormat('d.m.Y'),
			allowMailing = me.queryById('allowMailing').getValue();
		if (!base_form.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		printBirt({
			'Report_FileName': 'DistMonitoringConsent.rptdesign',
			'Report_Format': 'pdf',
			'Report_Params': 
				'&paramPerson=' + me.params.Person_id +
				'&paramMedStaffFact=' + getGlobalOptions().CurMedStaffFact_id +
				'&paramLpu=' + getGlobalOptions().lpu_id +
				'&paramPhone=' + me.queryById('phone').getValue() + 
				'&paramDate=' + formdate + 
				'&paramFlag=' + (allowMailing ? '2' : '1')
		});
	},
	updateEmk: function() {
		var me = this;
		var emks = Ext6.ComponentQuery.query('[refId=common]');
		emks.forEach(function(emk){
			if(emk.Person_id==me.params.Person_id) {
				var emkpanel = emk.queryById('ObserveChartPanel');
				if(emk.isVisible()) {
					if(!Ext6.isEmpty(emkpanel)) {
						emkpanel = emkpanel.ownerWin.down('[refId=ObserveChartPanel]');
						if(!Ext6.isEmpty(emkpanel)) emkpanel.reload();
					}
					emk.PersonInfoPanel.load({Person_id:me.params.Person_id});
				}
			}
		});
	},
	doSave: function() {
		var me = this,
			form = me.FormPanel,
			base_form = me.FormPanel.getForm();
		if (!base_form.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if(me.params.Label_id==7) {//метка температура
			if(!me.SaveMask)
				me.SaveMask = new Ext6.LoadMask(me, {msg: LOAD_WAIT});
			me.SaveMask.show();
			Ext6.Ajax.request({
				params: {
					Label_id: 7,
					Person_id: me.params.Person_id,
					Diag_id: null,
					dateConsent: me.queryById('dateConsent').getValue(),
					MedStaffFact_id: getGlobalOptions().CurMedStaffFact_id,
					phone: me.queryById('phone').getValue()
				},
				callback: function(options, success, response) {
					me.SaveMask.hide();
					if (success) {
						var res = Ext6.JSON.decode(response.responseText);
						if(!Ext6.isEmpty(res.Error_Msg)) {
							Ext6.Msg.alert(langs('Ошибка'), res.Error_Msg);
						} else {
							Ext6.Msg.alert(langs('Сообщение'), langs('Пациент добавлен в программу мониторинга по температуре'));
							me.params.callback({
								dateConsent: me.queryById('dateConsent').getValue(),
								Person_Phone: me.queryById('phone').getValue(),
								allowMailing: me.queryById('allowMailing').getValue() ? true : null
							});
							me.updateEmk();
							me.hide();
						}
					}
				},
				url: '/?c=PersonDisp&m=createPersonLabel'
			});
		} else {
			me.params.callback({
				dateConsent: me.queryById('dateConsent').getValue(),
				Person_Phone: me.queryById('phone').getValue(),
				allowMailing: me.queryById('allowMailing').getValue() ? true : null
			});
			me.hide();
		}
	},
	initComponent: function() {
		var me = this;

		me.FormPanel = new Ext6.form.FormPanel({
			border: false,
            bodyPadding: '25 25 25 30',
			region: 'center',
			defaults: {
				labelWidth: 100,
				width: 100+424
			},
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Пациент',
				itemId: 'personfio',
				readOnly: true
			}, {
				layout: 'column',
				border: false,
				items: [
					{
						//~ xtype: 'swDateField',
						xtype: 'datefield',
						name: 'dateConsent',
						itemId: 'dateConsent',
						startDay: 1,
						fieldLabel: 'Дата согласия',
						allowBlank: false,
						labelWidth: 100,
						width: 230,
						invalidText: 'Неправильная дата',
						plugins: [ new Ext6.ux.InputTextMask('99.99.9999', true) ],
						formatText: null,
					}, {
						xtype: 'textfield',
						padding: '0 0 0 23',
						labelWidth: 120,
						width: 271,
						fieldLabel: 'Номер телефона',
						allowBlank: false,
						name: 'phone',
						itemId: 'phone',
						getValue: function() {
							var v = this.getRawValue();
							if(v && v.length>0) {
								v = v.replace(/[ \(\)_]/g,'');
								if(v.length==12 && v.slice(0,2)=='+7') return v;
								else return null;
							} else return null;
						},
						setValue: function(x) {
							if(!x) { this.setRawValue(null); return '';}
							var regexp = /^(\+?7)?[\s\-]?\(?(\d{3})\)?[\s\-]?(\d{3})[\s\-]?(\d{2})[\s\-]?(\d{2})$/;

							if ( !regexp.test(x) ) {
								this.setRawValue(null);
							} else {
								this.setRawValue(x.replace(regexp,'+7 $2 $3 $4 $5'));
							}
						},
						listeners: {
							blur: function(el) {
								setTimeout(function() {el.isValid();}, 10);
							}
						},
						plugins: [ new Ext6.ux.InputTextMask('+7 999 999 99 99', true) ]
					},
					/*{
						xtype: 'swPhoneNumber',
						fieldLabel: 'Номер телефона',
						labelWidth: 120,
						width: 271,
						padding: '0 0 0 23',
						name: 'phone',
						itemId: 'phone',
						allowBlank: false
					}*/
				]
			}, {
				xtype: 'checkboxfield',
				padding: '0 0 0 105',
				name: 'allowMailing',
				itemId: 'allowMailing',
				boxLabel: 'Согласие на получение оповещений <br>'+
					'(смс/мобильное приложение/электронная почта)',
				width: 424,
				checked: true
			}, {
				layout: 'column',
				border: false,
				itemId: 'printbutton',
				padding: '0 0 0 100',
				items: [
					{
						xtype: 'button',
						iconCls: 'panicon-print',
						handler: function() {
							me.print();
						}
					}, {
						xtype: 'label',
						padding: '5 0 0 0',
						html: '<a href="#" onclick="Ext6.getCmp(\''+me.id+
								'\').print(\'add\');">Распечатать</a>'
					}
				]
			}]
		});

		Ext6.apply(me, {
			items: [
				me.FormPanel
			],
			border: false,
			buttons:
			[ '->'
			, {
				userCls:'buttonCancel buttonPoupup',
				text: langs('Отмена'),
				margin: 0,
				handler: function() {
					me.hide();
				}
			}, {
				userCls:'buttonAccept buttonPoupup',
				text: langs('Сохранить'),
				margin: '0 19 0 0',
				handler: function() {
					me.doSave();
				}
			}]
		});

		this.callParent(arguments);
	}
});