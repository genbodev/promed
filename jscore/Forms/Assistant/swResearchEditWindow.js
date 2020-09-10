/**
 * swResearchEditWindow.js - Редактирование исследования
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 */

sw.Promed.swResearchEditWindow = Ext.extend(sw.Promed.BaseForm,
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
	id: 'ResearchEditWindow',
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
	doSave: function(options) {
		var win = this;
		if ( win.formStatus == 'save' || win.action == 'view' ) {
			return false;
		}

		if (typeof options != 'object') {
			options = new Object();
		}

		win.formStatus = 'save';
		var form = this.ResearchEditForm;
		var base_form = form.getForm();
		var form_data = base_form.getValues()

		if (!base_form.isValid()) {
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					win.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		win.getLoadMask("Подождите, идет сохранение...").show();
		base_form.submit({
			url: '/?c=EvnLabSample&m=saveResearch',
			params: {
				EvnUslugaPar_id: win.EvnUslugaPar_id
			},
			failure: function(result_form, action)
			{
				win.formStatus = 'edit';
				win.getLoadMask().hide();
			},
			success: function(result_form, action)
			{
				win.formStatus = 'edit';
				win.getLoadMask().hide();
				if (action.result)
				{
					if (action.result.EvnUslugaPar_id)
					{
						win.hide();
						win.callback(form_data);
					}
					else
						Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
				}
				else
					Ext.Msg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka']);
			}
		});
	},
	initComponent: function()
	{
        var win = this;

		this.ResearchEditForm = new Ext.form.FormPanel(
		{
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			id: 'ResearchEditForm',
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
					title: lang['vyipolnenie_analiza'],
					id: 'REW_AnalyzePanel',
					items: [{
						name: 'EvnUslugaPar_minDate',
						xtype: 'hidden'
					}, {
						name: 'EvnUslugaPar_maxDate',
						xtype: 'hidden'
					}, {
						name: 'EvnUslugaPar_IsPaid',
						xtype: 'hidden'
					}, {
						name: 'EvnUslugaPar_IndexRep',
						xtype: 'hidden'
					}, {
						name: 'EvnUslugaPar_IndexRepInReg',
						xtype: 'hidden'
					}, {
						fieldLabel: 'Повторная подача',
						listeners: {
							'check': function(checkbox, value) {
								if ( getRegionNick() != 'perm' ) {
									return false;
								}

								var base_form = this.ResearchEditForm.getForm();

								var
									EvnUslugaPar_IndexRep = parseInt(base_form.findField('EvnUslugaPar_IndexRep').getValue()),
									EvnUslugaPar_IndexRepInReg = parseInt(base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()),
									EvnUslugaPar_IsPaid = parseInt(base_form.findField('EvnUslugaPar_IsPaid').getValue());

								var diff = EvnUslugaPar_IndexRepInReg - EvnUslugaPar_IndexRep;

								if ( EvnUslugaPar_IsPaid != 2 || EvnUslugaPar_IndexRepInReg == 0 ) {
									return false;
								}

								if ( value == true ) {
									if ( diff == 1 || diff == 2 ) {
										EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep + 2;
									}
									else if ( diff == 3 ) {
										EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep + 4;
									}
								}
								else if ( value == false ) {
									if ( diff <= 0 ) {
										EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep - 2;
									}
								}

								base_form.findField('EvnUslugaPar_IndexRep').setValue(EvnUslugaPar_IndexRep);

							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EUPAREF + 57,
						name: 'EvnUslugaPar_RepFlag',
						xtype: 'checkbox'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								fieldLabel: lang['data_nachala_vyipolneniya'],
								format: 'd.m.Y',
								name: 'EvnUslugaPar_setDate',
								//maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								fieldLabel: lang['vremya'],
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								name: 'EvnUslugaPar_setTime',
								onTriggerClick: function() {
									var base_form = this.ResearchEditForm.getForm();

									var time_field = base_form.findField('EvnUslugaPar_setTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										callback: function() {
											base_form.findField('EvnUslugaPar_setDate').fireEvent('change', base_form.findField('EvnUslugaPar_setDate'), base_form.findField('EvnUslugaPar_setDate').getValue());
										}.createDelegate(this),
										dateField: base_form.findField('EvnUslugaPar_setDate'),
										loadMask: true,
										setDate: true,
										//setDateMaxValue: true,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: this.id
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					}, {
						displayField: 'Org_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: lang['organizatsiya'],
						disabled: true,
						hiddenName: 'Lpu_aid',
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
							var base_form = this.ResearchEditForm.getForm();
							var combo = base_form.findField('Lpu_aid');

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
										base_form.findField('LpuSection_aid').getStore().load(
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
						hiddenName: 'LpuSection_aid',
						xtype: 'swlpusectionglobalcombo',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.ResearchEditForm.getForm();
								base_form.findField('MedPersonal_aid').clearValue();
								base_form.findField('MedPersonal_aid').getStore().load({
									params: {
										LpuSection_id: newValue,
										Lpu_id: base_form.findField('Lpu_aid').getValue()
									},
									callback: function() {
										base_form.findField('MedPersonal_aid').setValue(base_form.findField('MedPersonal_aid').getValue());
									}
								});
								base_form.findField('MedPersonal_said').clearValue();
								base_form.findField('MedPersonal_said').getStore().load({
									params: {
										LpuSection_id: newValue,
										Lpu_id: base_form.findField('Lpu_aid').getValue()
									},
									callback: function() {
										base_form.findField('MedPersonal_said').setValue(base_form.findField('MedPersonal_said').getValue());
									}
								});
							}.createDelegate(this)
						},
						anchor: '100%'
					}, {
						fieldLabel: lang['vrach'],
						hiddenName: 'MedPersonal_aid',
						xtype: 'swmedpersonalcombo',
						anchor: '100%',
                        allowBlank: true
					}, {
						comboSubject: 'UslugaMedType',
						enableKeyEvents: true,
						hidden: getRegionNick() !== 'kz',
						allowBlank: getRegionNick() !== 'kz',
						fieldLabel: langs('Вид услуги'),
						hiddenName: 'UslugaMedType_id',
						lastQuery: '',
						typeCode: 'int',
						width: 450,
						xtype: 'swcommonsprcombo'
					}, {
						fieldLabel: langs('Ср. медперсонал'),
						hiddenName: 'MedPersonal_said',
						xtype: 'swmedpersonalcombo',
						anchor: '100%',
                        allowBlank: true
					}, {
						fieldLabel: lang['kommentariy'],
						name: 'EvnUslugaPar_Comment',
						xtype: 'textfield',
						anchor: '100%',
                        allowBlank: true
					}]
				})
			],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'EvnUslugaPar_minDate'},
				{name: 'EvnUslugaPar_maxDate'},
				{name: 'EvnUslugaPar_setDate'},
				{name: 'EvnUslugaPar_setTime'},
				{name: 'Lpu_aid'},
				{name: 'LpuSection_aid'},
				{name: 'MedPersonal_aid'},
				{name: 'MedPersonal_said'},
				{name: 'UslugaMedType_id'},
				{name: 'EvnUslugaPar_Comment'},
				{name: 'EvnUslugaPar_IsPaid'},
				{name: 'EvnUslugaPar_IndexRep'},
				{name: 'EvnUslugaPar_IndexRepInReg'}
			]),
			url: '/?c=EvnLabSample&m=saveResearch'
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
			{
				text: BTN_FRMHELP,
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
				this.ResearchEditForm
			]
		});
		sw.Promed.swResearchEditWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		var win = this;
		sw.Promed.swResearchEditWindow.superclass.show.apply(this, arguments);
		
		this.callback = Ext.emptyFn;
		this.EvnUslugaPar_id = null;

        if ( !arguments[0] || !arguments[0].EvnUslugaPar_id ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { this.hide(); }.createDelegate(this));
            return false;
        }

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].EvnUslugaPar_id ) {
			this.EvnUslugaPar_id = arguments[0].EvnUslugaPar_id;
		}

		win.syncShadow();

        win.getLoadMask(lang['zagruzka_dannyih_formyi']).show();

		if (this.action == 'view') {
			this.enableEdit(false);
		} else {
			this.enableEdit(true);
		}

		var base_form = this.ResearchEditForm.getForm();
		base_form.reset();

		base_form.findField('EvnUslugaPar_RepFlag').hideContainer();

		win.getLoadMask(LOAD_WAIT).show();
		base_form.load({
			failure: function() {
				win.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
			}.createDelegate(this),
			params: {
				EvnUslugaPar_id: win.EvnUslugaPar_id
			},
			success: function() {
				win.getLoadMask().hide();

				// если нет даты выполнения, значит нет одобренных тестов, значит..
				if (Ext.isEmpty(base_form.findField('EvnUslugaPar_setDate').getValue())) {
					sw.swMsg.alert("Ошибка", "В исследовании нет одобренных тестов, изменение выполнения исследования невозможно.");
					win.hide();
				}

				base_form.findField('EvnUslugaPar_setDate').setMinValue(base_form.findField('EvnUslugaPar_minDate').getValue());
				base_form.findField('EvnUslugaPar_setDate').setMaxValue(base_form.findField('EvnUslugaPar_maxDate').getValue());

				p1 = {};
				p1.lpu_id = base_form.findField('Lpu_aid').getValue() || getGlobalOptions().lpu_id;
				p1.CurLpuSection_id = base_form.findField('LpuSection_aid').getValue();
				p1.CurMedPersonal_id = base_form.findField('MedPersonal_aid').getValue();
				p1.CurMedPersonal_said = base_form.findField('MedPersonal_said').getValue();
				var CMPRLQT_id = base_form.findField('MedPersonal_aid').lastQueryText,
					CMPRLQT_said = base_form.findField('MedPersonal_said').lastQueryText;

				p1.CurMedPersonalRaw_id = (Ext.isEmpty(CMPRLQT_id)) ? null : base_form.findField('MedPersonal_aid').lastQueryText;
				p1.CurMedPersonalRaw_said = (Ext.isEmpty(CMPRLQT_said)) ? null : base_form.findField('MedPersonal_said').lastQueryText;

				// начинаем загрузку комбиков разделов "Выполнение анализа"
				base_form.findField('Lpu_aid').getStore().removeAll();
				base_form.findField('Lpu_aid').setValue(null);
				base_form.findField('LpuSection_aid').getStore().removeAll();
				base_form.findField('LpuSection_aid').setValue(null);
				base_form.findField('MedPersonal_aid').getStore().removeAll();
				base_form.findField('MedPersonal_aid').setValue(null);
				base_form.findField('MedPersonal_said').getStore().removeAll();
				base_form.findField('MedPersonal_said').setValue(null);

				base_form.findField('Lpu_aid').getStore().load({
					callback: function(records, options, success) {
						base_form.findField('Lpu_aid').setValue(p1.lpu_id);
						if (p1.lpu_id) {
							base_form.findField('LpuSection_aid').getStore().load({
								callback: function(records, options, success) {
									base_form.findField('LpuSection_aid').setValue(p1.CurLpuSection_id);
									if (p1.CurLpuSection_id) {
										base_form.findField('MedPersonal_aid').getStore().load({
											callback: function(records, options, success) {
												if (base_form.findField('MedPersonal_aid').getStore().data.length > 0)
													base_form.findField('MedPersonal_aid').setValue( p1.CurMedPersonal_id);
												else base_form.findField('MedPersonal_aid').setValue(p1.CurMedPersonalRaw_id);
											},
											params: {
												LpuSection_id: p1.CurLpuSection_id,
												Lpu_id: p1.lpu_id,
												withPosts: "1",//только врачи
												MedPersonalNotNeeded: "true",
												hideNotWork: true
											}
										});
										base_form.findField('MedPersonal_said').getStore().load({
											callback: function(records, options, success) {
												if (base_form.findField('MedPersonal_said').getStore().data.length > 0)
													base_form.findField('MedPersonal_said').setValue( p1.CurMedPersonal_said);
												else base_form.findField('MedPersonal_said').setValue(p1.CurMedPersonalRaw_said);
											},
											params: {
												LpuSection_id: p1.CurLpuSection_id,
												Lpu_id: p1.lpu_id,
												withPosts: "6",//только средний мед. персонал
												MedPersonalNotNeeded: "true",
												hideNotWork: true
											}
										});
									}
								},
								params: {
									Lpu_id: p1.lpu_id
								}
							});
						}
					},
					params: {
						Lpu_oid: p1.lpu_id,
						OrgType: 'lpu'
					}
				});

				if ( getRegionNick() == 'perm' && base_form.findField('EvnUslugaPar_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()) > 0 ) {
					base_form.findField('EvnUslugaPar_RepFlag').showContainer();

					if ( parseInt(base_form.findField('EvnUslugaPar_IndexRep').getValue()) >= parseInt(base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()) ) {
						base_form.findField('EvnUslugaPar_RepFlag').setValue(true);
					}
					else {
						base_form.findField('EvnUslugaPar_RepFlag').setValue(false);
					}
				}

				var UslugaMedType_id = base_form.findField('UslugaMedType_id');
				UslugaMedType_id.setContainerVisible(getRegionNick() === 'kz');
				if( getRegionNick() === 'kz' ){
					if (Ext.isEmpty(UslugaMedType_id.getValue())) {
						UslugaMedType_id.setFieldValue('UslugaMedType_Code', '1400');
					}
				}

				win.ResearchEditForm.getForm().findField('Lpu_aid').focus(true,2000);
				this.syncShadow();
			}.createDelegate(this),
			url: '/?c=EvnLabSample&m=loadResearchEditForm'
		});
	},
	title: lang['parametryi_issledovaniya']

});