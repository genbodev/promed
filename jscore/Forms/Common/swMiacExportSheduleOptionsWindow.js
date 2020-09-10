/**
* swMiacExportSheduleOptionsWindow - окно редактирования настроек планировщика выгрузки в МИАЦ.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Ivan Pshenitcyn aka IVP (ipshon@gmail.com)
* @version      05.05.2011
*/

sw.Promed.swMiacExportSheduleOptionsWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'border',
	width: 700,
	height: 560,
	modal: true,
	resizable: false,
	title: lang['nastroyki_avtomaticheskoy_vyigruzki_v_miats'],
	draggable: false,
	closeAction: 'hide',
	doSave: function() {
		var form = this.findById('miac_export_shedule_options_form');
		var base_form = form.getForm();
		
		var current_window = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		// сохраняем
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Настройки сохраняются..."});
		loadMask.show();
		form.getForm().submit({
			success: function(form, action) {
				loadMask.hide();
				current_window.hide();				
			},
			failure: function() {
				loadMask.hide();
			}
		});
	},
	buttonAlign: 'left',
	plain: true,
	id: 'miac_export_shedule_options_window',
	show: function() {		
		sw.Promed.swMiacExportSheduleOptionsWindow.superclass.show.apply(this, arguments);
		var form = this.findById('miac_export_shedule_options_form');		
		this.center();
		form.getForm().reset();
		current_window = this;
		
		var Mask = new Ext.LoadMask(Ext.get('miac_export_shedule_options_window'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..."} );
		Mask.show();
		form.getForm().load({
			failure: function() {
				sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { current_window.hide(); } );
			},
			success: function() {
				Mask.hide();
			},
			url: '/?c=MiacExport&m=getMiacExportSheduleOptions'
		});
	},
	initComponent: function() {
		Ext.apply(this, {
			items: [
				new Ext.form.FormPanel({
					frame: true,
					autoScroll:true,
					url: '/?c=MiacExport&m=saveMiacExportSheduleOptions',
					autoHeight: true,
					region: 'center',
					id: 'miac_export_shedule_options_form',
					autoLoad: false,
					labelWidth: 120,
					reader: new Ext.data.JsonReader({
						success: Ext.emptyFn
					}, [
						{ name: 'DataStorage_id' },
						{ name: 'Marker_R' },
						{ name: 'Marker_U' },
						{ name: 'Marker_D' },
						{ name: 'IsDay' },
						{ name: 'IsWeek' },
						{ name: 'IsMonth' },
						{ name: 'WeekDay' },
						{ name: 'MonthDay' },
						{ name: 'DayTime' },
						{ name: 'WeekTime' },
						{ name: 'MonthTime' },
						{ name: 'UploadPath' }
					]),
					items: [{
						name: 'DataStorage_id',
						xtype: 'hidden',
						value: null
					}, {
						autoHeight: true,
						xtype: 'fieldset',
						labelAlign: 'right',
						labelWidth: 15,
						layout: 'form',
						title: lang['markeryi_dlya_vyigruzki'],
						items: [{
							boxLabel: lang['retseptyi'],
							checked: false,
							id: 'MESOW_Marker_R',
							name: 'Marker_R',
							fieldLabel: 'R',
							tabIndex: 10532,
							xtype: 'checkbox'
						}, {
							boxLabel: lang['poliklinicheskie_posescheniya'],
							checked: false,
							id: 'MESOW_Marker_U',
							name: 'Marker_U',
							fieldLabel: 'U',
							tabIndex: 10533,
							xtype: 'checkbox'
						}, {
							boxLabel: lang['sluchai_vremennoy_netrudosposobnosti'],
							checked: false,
							id: 'MESOW_Marker_D',
							name: 'Marker_D',
							fieldLabel: 'D',
							tabIndex: 10534,
							xtype: 'checkbox'
						}]
					}, {
						xtype: 'textfield',
						id: 'MESOW_UploadPath',
						name: 'UploadPath',
						fieldLabel: lang['polnyiy_put_dlya_vyigruzki'],
						width: 300,
						value: ''
					}, {
						autoHeight: true,
						xtype: 'fieldset',
						labelAlign: 'right',
						labelWidth: 100,
						layout: 'form',
						title: lang['period_vyigruzki_mojno_vyibrat_tolko_odin'],
						items: [{
							autoHeight: true,
							xtype: 'fieldset',
							labelAlign: 'right',
							labelWidth: 100,
							layout: 'form',
							items: [{
								checked: false,
								id: 'MESOW_IsDay',
								name: 'IsDay',
								fieldLabel: lang['ejednevno'],
								tabIndex: 10534,
								xtype: 'checkbox',
								listeners: {
									'check': function(field, newVal)
									{
										if ( newVal == true )
										{
											this.findById('MESOW_IsWeek').setValue(false);
											this.findById('MESOW_IsMonth').setValue(false);
										}
									}.createDelegate(this)
								}
							}, {
								fieldLabel: lang['vremya_vyigruzki'],
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								id: 'MESOW_DayTime',
								name: 'DayTime',
								onTriggerClick: function() {
									var time_field = Ext.getCmp('MESOW_DayTime');
									time_field.setValue('00:00');
									if ( time_field.disabled ) {
										return false;
									}
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: 10534,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield',
								value: '00:00'
							}]
						}, {
							autoHeight: true,
							xtype: 'fieldset',
							labelAlign: 'right',
							labelWidth: 100,
							layout: 'form',
							items: [{
								checked: false,
								id: 'MESOW_IsWeek',
								name: 'IsWeek',
								fieldLabel: lang['ejenedelno'],
								tabIndex: 10534,
								xtype: 'checkbox',
								listeners: {
									'check': function(field, newVal)
									{
										if ( newVal == true )
										{
											this.findById('MESOW_IsDay').setValue(false);
											this.findById('MESOW_IsMonth').setValue(false);
										}
									}.createDelegate(this)
								}
							}, {
								allowBlank: true,
								codeField: 'WeekDay_Code',
								displayField: 'WeekDay_Name',
								editable: false,
								fieldLabel: lang['den_nedeli'],
								hiddenName: 'WeekDay',
								id: 'MESOW_WeekDay',
								hideEmptyRow: true,
								listeners: {
									'blur': function(combo)  {
										if ( combo.value == '' )
											combo.setValue(1);
									}
								},
								store: new Ext.data.SimpleStore({
										autoLoad: true,
										data: [
												[ 1, 1, lang['ponedelnik'] ],
												[ 2, 2, lang['vtornik'] ],
												[ 3, 3, lang['sreda'] ],
												[ 4, 4, lang['chetverg'] ],
												[ 5, 5, lang['pyatnitsa'] ],
												[ 6, 6, lang['subbota'] ],
												[ 7, 7, lang['voskresene'] ]
										],
										fields: [
												{name: 'WeekDay_id', type: 'int'},
												{name: 'WeekDay_Code', type: 'int'},
												{name: 'WeekDay_Name', type: 'string'}
										],
										key: 'WeekDay_id',
										sortInfo: {field: 'WeekDay_Code'}
								}),
								tabIndex: 100534,
								tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<font color="red">{WeekDay_Code}</font>&nbsp;{WeekDay_Name}',
										'</div></tpl>'
								),
								value: 1,
								valueField: 'WeekDay_id',
								width: 150,
								xtype: 'swbaselocalcombo'
							}, {
								fieldLabel: lang['vremya_vyigruzki'],
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								id: 'MESOW_WeekTime',
								name: 'WeekTime',
								onTriggerClick: function() {
									var time_field = Ext.getCmp('MESOW_WeekTime');
									time_field.setValue('00:00');
									if ( time_field.disabled ) {
										return false;
									}
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: 10534,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield',
								value: '00:00'
							}]
						}, {
							autoHeight: true,
							xtype: 'fieldset',
							labelAlign: 'right',
							labelWidth: 100,
							layout: 'form',
							items: [{
								checked: false,
								id: 'MESOW_IsMonth',
								name: 'IsMonth',
								fieldLabel: lang['ejemesyachno'],
								tabIndex: 10534,
								xtype: 'checkbox',
								listeners: {
									'check': function(field, newVal)
									{
										if ( newVal == true )
										{
											this.findById('MESOW_IsWeek').setValue(false);
											this.findById('MESOW_IsDay').setValue(false);
										}
									}.createDelegate(this)
								}
							}, {
								allowBlank: true,
								displayField: 'MonthDay_Name',
								editable: false,
								fieldLabel: lang['den_mesyatsa'],
								hiddenName: 'MonthDay',
								id: 'MESOW_MonthDay',
								hideEmptyRow: true,
								listeners: {
									'blur': function(combo)  {
										if ( combo.value == '' )
											combo.setValue(1);
									}
								},
								store: new Ext.data.SimpleStore({
										autoLoad: true,
										data: [
												[ 1, '1' ],
												[ 2, '2' ],
												[ 3, '3' ],
												[ 4, '4' ],
												[ 5, '5' ],
												[ 6, '6' ],
												[ 7, '7' ],
												[ 8, '8' ],
												[ 9, '9' ],
												[ 10, '10' ],
												[ 11, '11' ],
												[ 12, '12' ],
												[ 13, '13' ],
												[ 14, '14' ],
												[ 15, '15' ],
												[ 16, '16' ],
												[ 17, '17' ],
												[ 18, '18' ],
												[ 19, '19' ],
												[ 20, '20' ],
												[ 21, '21' ],
												[ 22, '22' ],
												[ 23, '23' ],
												[ 24, '24' ],
												[ 25, '25' ],
												[ 26, '26' ],
												[ 27, '27' ],
												[ 28, '28' ],
												[ 29, '29' ],
												[ 30, '30' ],
												[ 31, '31' ]
										],
										fields: [
												{name: 'MonthDay_id', type: 'int'},
												{name: 'MonthDay_Name', type: 'string'}
										],
										key: 'MonthDay_id',
										sortInfo: {field: 'MonthDay_id'}
								}),
								tabIndex: 100534,
								tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'{MonthDay_Name}',
										'</div></tpl>'
								),
								value: 1,
								valueField: 'MonthDay_id',
								width: 50,
								xtype: 'swbaselocalcombo'
							}, {
								fieldLabel: lang['vremya_vyigruzki'],
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								id: 'MESOW_MonthTime',
								name: 'MonthTime',
								onTriggerClick: function() {
									var time_field = Ext.getCmp('MESOW_MonthTime');
									time_field.setValue('00:00');
									if ( time_field.disabled ) {
										return false;
									}
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: 10534,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield',
								value: '00:00'
							}]
						}]
					}]
				})
			],
			buttons: [{
				text: BTN_FRMSAVE,
				iconCls: 'save16',
				handler: function() {
					this.doSave();
				}.createDelegate(this)
			}, {
				text:'-'
			}, {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'MESOW_HelpButton',
				handler: function(button, event) {
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}
			],
			enableKeyEvents: true,
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;
					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;
					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J)
					{
						Ext.getCmp('miac_export_shedule_options_window').hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C)
					{
						Ext.getCmp('miac_export_shedule_options_window').buttons[0].handler();
						return false;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swMiacExportSheduleOptionsWindow.superclass.initComponent.apply(this, arguments);
	}
});