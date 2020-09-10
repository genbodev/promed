/**
* swEvnStickEditWindow - окно редактирования/добавления листа временной нетрудоспособности.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Stick
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.003-05.09.2010
* @comment      Префикс для id компонентов EStEF (EvnStickEditForm)
*
*
* @input data: action - действие (add, edit, view)
*/
/*NO PARSE JSON*/
var _l = [];

function __l(msg){
	// console.log(msg);
	// _l.push(msg);
}

sw.Promed.swEvnStickEditWindow = function(){

	this.id = 'swEvnStickEditWindow';
	var win = this;

	var evnStickNumField = {
		allowBlank: !isPolkaRegistrator() && (isPolkaVrach() || isStacVrach() || isStacReceptionVrach() || isOperator() || isMedStatUser() || isRegLvn()),
		fieldLabel: langs('Номер'),
		name: 'EvnStick_Num',
		maxLength: 12, // Длина  номера составляет 12 цифр #136679
		minLength: 12,
		enableKeyEvents: true,
		listeners: {
			'change': function(field, newValue) {
				if (getRegionNick() != 'kz'){
					this.checkGetEvnStickNumButton();
				}
				this.checkOrgFieldDisabled();

				Ext.getCmp('EvnStickES_Type').setVisible(false);
				if (newValue.length > 0 && getRegionNick != 'kz') {
					Ext.getCmp('EvnStickES_Loader').setVisible(true);
				}
				else {
					Ext.getCmp('EvnStickES_Loader').setVisible(false);
				}

				if (newValue.length == 12) {
					this.checkEvnStickNumDouble();
				}
			}.createDelegate(this),
			'keyup': function (thisField, e) {
				thisField.fireEvent('change', thisField, thisField.getValue());
			}.createDelegate(this)
		},
		maskRe: /\d/,
		tabIndex: TABINDEX_ESTEF + 7,
		width: 100,
		xtype: 'textfield'
	}
	var evnStickSerField = {
		allowBlank: true,
		fieldLabel: langs('Серия'),
		name: 'EvnStick_Ser',
		tabIndex: TABINDEX_ESTEF + 6,
		maskRe: /[a-zA-Zа-яА-Я]/,
		regex : /[a-zA-Zа-яА-Я]+/,
		width: 100,
		xtype: 'textfield'
	}
	if ( getRegionNick() == 'kz' ) {
		evnStickNumField.allowBlank = true;
		evnStickNumField.autoCreate = {
			tag: 'input',
			type: 'text',
			maxLength: '7'
		};
		evnStickNumField.maxLength = 7;
		evnStickNumField.minLength = 7;

		evnStickSerField.allowBlank = true;
		evnStickSerField.autoCreate = {
			tag: 'input',
			type: 'text',
			maxLength: '2'
		};
		evnStickSerField.maxLength = 2;
		evnStickSerField.minLength = 2;
		evnStickSerField.toUpperCase = true;
		evnStickSerField.xtype = 'textfieldpmw';
	}

	this.enableField = function (field) {
		var name = '';
		if (field.name) {
			name = field.name;
		}
		if (field.hiddenName) {
			name = field.hiddenName;
		}

		var EvnStickDop_pid = this.FormPanel.getForm().findField('EvnStickDop_pid').getValue();
		var isFieldInList = name.inlist([
			'EvnStick_IsOriginal',
			'StickWorkType_id',
			'EvnStickLast_Title',
			'EvnStickDop_pid',
			'EvnStick_Ser',
			'EvnStick_Num',
			'Org_id',
			'EvnStick_OrgNick',
			'Post_Name',
			'EvnStick_setDate',
			'EvnStick_disDate',
			'StickOrder_id',
			'StickCause_id',
			'StickLeaveType_id',
			'MedStaffFact_id'
		]);

		if(
			(isFieldInList || Ext.isEmpty(EvnStickDop_pid) || EvnStickDop_pid == -1) &&

			// если нет особой логики для enable / disable
			this.checkFieldDisabled(name) == false
		){
			field.enable();
		}
	}.createDelegate(this);


	// Верхняя панель с информацией о пациенте
	this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
		id: this.id + 'EStEF_PersonInformationFrame',
		region: 'north'
	});

	// 1. Список пациентов, нуждающихся в уходе
	this.panelEvnStickCarePerson = new sw.Promed.Panel({
		border: true,
		collapsible: true,
		height: 175,
		id: this.id + 'EStEF_EvnStickCarePersonPanel',
		isLoaded: false,
		layout: 'border',
		listeners: {
			'expand': function(panel){

				var me = this;
				var base_form = this.FormPanel.getForm();

				if ( panel.isLoaded === false ) {
					var evn_stick_id;

					if ( this.FormPanel.getForm().findField('EvnStickDop_pid').getValue() > 0 ) {
						evn_stick_id = this.FormPanel.getForm().findField('EvnStickDop_pid').getValue();
					}
					else {
						evn_stick_id = this.FormPanel.getForm().findField('EvnStick_id').getValue();
					}

					panel.isLoaded = true;

					panel.findById(this.id+'EStEF_EvnStickCarePersonGrid').getStore().load({
						params: {
							EvnStick_id: evn_stick_id,
							EvnStickBase_IsFSS: base_form.findField('EvnStickBase_IsFSS').getValue()
						},
						callback: function(){
							me.checkRebUhod();
						}
					});
				}

				panel.doLayout();
			}.createDelegate(this)
		},
		style: 'margin-bottom: 0.5em;',
		title: langs('1. Список пациентов, нуждающихся в уходе'),
		items: [ new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand_careperson',
			autoExpandMin: 100,
			border: false,
			columns: [
				{
					dataIndex: 'Person_Fio',
					header: langs('Пациент'),
					hidden: false,
					id: 'autoexpand_careperson',
					resizable: true,
					sortable: true
				},
				{
					dataIndex: 'Person_Age',
					header: langs('Возраст на начало случая лечения'),
					hidden: false,
					resizable: true,
					sortable: true,
					width: 200
				},
				{
					dataIndex: 'RelatedLinkType_Name',
					header: langs('Родственная связь'),
					hidden: false,
					resizable: true,
					sortable: true,
					width: 200
				}
			],
			frame: false,
			id: this.id + 'EStEF_EvnStickCarePersonGrid',
			keys: [{
				key: [
					Ext.EventObject.DELETE,
					Ext.EventObject.END,
					Ext.EventObject.ENTER,
					Ext.EventObject.F3,
					Ext.EventObject.F4,
					Ext.EventObject.HOME,
					Ext.EventObject.INSERT,
					Ext.EventObject.PAGE_DOWN,
					Ext.EventObject.PAGE_UP,
					Ext.EventObject.TAB
				],
				fn: function(inp, e) {
					e.stopEvent();

					this._stopPagination(e);

					var grid = this.findById(this.id+'EStEF_EvnStickCarePersonGrid');

					switch ( e.getKey() ) {
						case Ext.EventObject.DELETE:
							this.deleteGridRecord('EvnStickCarePerson');
							break;

						case Ext.EventObject.END:
							GridEnd(grid);
							break;

						case Ext.EventObject.ENTER:
						case Ext.EventObject.F3:
						case Ext.EventObject.F4:
						case Ext.EventObject.INSERT:
							if ( !grid.getSelectionModel().getSelected() ) {
								return false;
							}

							var action = 'add';

							if ( e.getKey() == Ext.EventObject.F3 ) {
								action = 'view';
							}
							else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
								action = 'edit';
							}

							this.openEvnStickCarePersonEditWindow(action);
							break;

						case Ext.EventObject.HOME:
							GridHome(grid);
							break;

						case Ext.EventObject.PAGE_DOWN:
							GridPageDown(grid);
							break;

						case Ext.EventObject.PAGE_UP:
							GridPageUp(grid);
							break;

						case Ext.EventObject.TAB:
							var base_form = this.FormPanel.getForm();

							grid.getSelectionModel().clearSelections();
							grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

							if ( e.shiftKey == false ) {

								this._keydownFocus8();

							}
							else {
								if ( !base_form.findField('Org_did').hidden && !base_form.findField('Org_did').disabled ) {
									base_form.findField('Org_did').focus(true);
								}
								else if ( !base_form.findField('EvnStick_BirthDate').hidden && !base_form.findField('EvnStick_BirthDate').disabled ) {
									base_form.findField('EvnStick_BirthDate').focus(true);
								}
								else if ( !base_form.findField('StickCause_did').disabled ) {
									base_form.findField('StickCause_did').focus(true);
								}
								else {
									this._focusButtonCancel();
								}
							}
							break;
					}
				}.createDelegate(this),
				scope: this,
				stopEvent: true
			}],
			layout: 'fit',
			listeners: {
				'rowdblclick': function(grid, number, obj) {
					this.openEvnStickCarePersonEditWindow('edit');
				}.createDelegate(this)
			},
			loadMask: true,
			region: 'center',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIndex, record) {
						var access_type = 'view';
						var id = null;
						var selected_record = sm.getSelected();
						var toolbar = this.findById(this.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar();

						if ( selected_record ) {
							access_type = selected_record.get('accessType');
							id = selected_record.get('EvnStickCarePerson_id');
						}

						if ( this.evnStickType == 2 ) {
							access_type = 'view';
						}

						toolbar.items.items[1].disable();
						toolbar.items.items[3].disable();

						if ( id ) {
							toolbar.items.items[2].enable();

							if ( this.action != 'view' && access_type == 'edit' && this.evnStickType == 1 ) {
								toolbar.items.items[1].enable();
								toolbar.items.items[3].enable();
							}
						}
						else {
							toolbar.items.items[2].disable();
						}
					}.createDelegate(this)
				}
			}),
			stripeRows: true,
			store: new Ext.data.Store({
				autoLoad: false,
				listeners: {
					'load': function(store, records, index) {
						var base_form = this.FormPanel.getForm();

						if ( store.getCount() == 0 ) {

							this._clearEvnStickCarePersonGrid();

						} else {			
							if (
								this.action == 'add'
								&& base_form.findField('EvnStick_IsOriginal').getValue() == 2 //Дубликат
							) {
								Ext.getCmp(this.id + 'EStEF_EvnStickCarePersonGrid').getStore().each(function(rec) {
									rec.set('EvnStickCarePerson_id', -1);
									rec.set('RecordStatus_Code', 0);
								});
							}
						}
					}.createDelegate(this)
				},
				reader: new Ext.data.JsonReader(
					{
						id: 'EvnStickCarePerson_id'
					},
					[
						{
							mapping: 'accessType',
							name: 'accessType',
							type: 'string'
						},
						{
							mapping: 'EvnStickCarePerson_id',
							name: 'EvnStickCarePerson_id',
							type: 'int'
						},
						{
							mapping: 'Person_id',
							name: 'Person_id',
							type: 'int'
						},
						{
							// Тот, кому выдается ЛВН
							mapping: 'Person_pid',
							name: 'Person_pid',
							type: 'int'
						},
						{
							mapping: 'RelatedLinkType_id',
							name: 'RelatedLinkType_id',
							type: 'int'
						},
						{
							mapping: 'RecordStatus_Code',
							name: 'RecordStatus_Code',
							type: 'int'
						},
						{
							mapping: 'Person_Age',
							name: 'Person_Age',
							type: 'int'
						},
						{
							mapping: 'Person_Fio',
							name: 'Person_Fio',
							type: 'string'
						},
						{
							mapping: 'RelatedLinkType_Name',
							name: 'RelatedLinkType_Name',
							type: 'string'
						},
						{
							mapping: 'Person_Surname',
							name: 'Person_Surname',
							type: 'string'
						},
						{
							mapping: 'Person_Firname',
							name: 'Person_Firname',
							type: 'string'
						},
						{
							mapping: 'Person_Secname',
							name: 'Person_Secname',
							type: 'string'
						},
						{
							mapping: 'Person_Birthday',
							name: 'Person_Birthday',
							type: 'string'
						}
					]
				),
				url: '/?c=Stick&m=loadEvnStickCarePersonGrid'
			}),
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						this.openEvnStickCarePersonEditWindow('add');
					}.createDelegate(this),
					iconCls: 'add16',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP
				}, {
					handler: function() {
						this.openEvnStickCarePersonEditWindow('edit');
					}.createDelegate(this),
					iconCls: 'edit16',
					text: BTN_GRIDEDIT,
					tooltip: BTN_GRIDEDIT_TIP
				}, {
					handler: function() {
						this.openEvnStickCarePersonEditWindow('view');
					}.createDelegate(this),
					iconCls: 'view16',
					text: BTN_GRIDVIEW,
					tooltip: BTN_GRIDVIEW_TIP
				}, {
					handler: function() {
						this.deleteGridRecord('EvnStickCarePerson');
					}.createDelegate(this),
					iconCls: 'delete16',
					text: BTN_GRIDDEL,
					tooltip: BTN_GRIDDEL_TIP
				}]
			})
		})]
	});

	// 2. Режим
	this.panelStickRegime = new sw.Promed.Panel({
		autoHeight: true,
		bodyStyle: 'padding-top: 0.5em;',
		border: true,
		labelWidth: 200,
		collapsible: true,
		id: this.id + 'EStEF_StickRegimePanel',
		layout: 'form',
		listeners: {
			'expand': function(panel) {
				//
			}.createDelegate(this)
		},
		style: 'margin-bottom: 0.5em;',
		title: langs('2. Режим'),
		items: [
			{
				border: false,
				labelWidth: 400,
				layout: 'form',
				items: [{
					comboSubject: 'YesNo',
					enableKeyEvents: true,
					fieldLabel: langs('Поставлена на учет в ранние сроки беременности (до 12 недель)'),
					hiddenName: 'EvnStick_IsRegPregnancy',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								var base_form = this.FormPanel.getForm();

								e.stopEvent();

								if ( !base_form.findField('EvnStick_BirthDate').hidden && !base_form.findField('EvnStick_BirthDate').disabled ) {
									base_form.findField('EvnStick_BirthDate').focus(true);
								}
								else {
									this._focusButtonCancel();
								}
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_ESTEF + 20,
					width: 100,
					xtype: 'swcommonsprcombo'
				}]
			},
			{
				allowBlank: true,
				comboSubject: 'StickIrregularity',
				enableKeyEvents: true,
				fieldLabel: langs('Нарушение режима'),
				hiddenName: 'StickIrregularity_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var record = combo.getStore().getById(newValue);

						if ( newValue ) {
							base_form.findField('EvnStick_irrDate').setContainerVisible(true);
							base_form.findField('EvnStick_irrDate').setAllowBlank(false);
						}
						else {
							base_form.findField('EvnStick_irrDate').setContainerVisible(false);
							base_form.findField('EvnStick_irrDate').setAllowBlank(true);
							base_form.findField('EvnStick_irrDate').setRawValue('');
							this.findById('SIrrStatus_Name').getEl().dom.innerHTML = '';
							this.findById('SIrrStatus_Name').render();
						}
						this.refreshFormPartsAccess();
					}.createDelegate(this),
					'keydown': function(inp, e) {
						if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {

							e.stopEvent();

							this._keydownFocus9();
						}
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_ESTEF + 21,
				width: 500,
				xtype: 'swcommonsprcombo'
			},
			{
				allowBlank: true,
				fieldLabel: langs('Дата нарушения режима'),
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
					}.createDelegate(this),
					'keydown': function(inp, e) {
						var base_form = this.FormPanel.getForm();

						if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false && base_form.findField('EvnStick_stacBegDate').disabled ) {
							e.stopEvent();

							this._keydownFocus();
						}
					}.createDelegate(this)
				},
				name: 'EvnStick_irrDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_ESTEF + 22,
				width: 100,
				xtype: 'swdatefield'
			},
			{
				layout: 'column',
				labelWidth: 180,
				bodyStyle: 'margin: 10px 0',
				width: 980,
				border: false,
				hidden: getRegionNick() == 'kz',
				items: [
					{
						layout: 'form',
						border: false,
						width: 620,
						items: [{
							style: 'padding: 4px 10px 0 205px; font-size: 1.1em; text-align: right',
							autoHeight: true,
							bodyBorder: false,
							border: false,
							id: 'SIrrStatus_Name',
							html: ''
						}]
					},
					{
						layout: 'form',
						border: false,
						width: 30,
						items: [{
							xtype: 'button',
							id: 'swSignStickIrr',
							tooltip: 'Подписать',
							iconCls: 'signature16',
							handler: function(){
								this.doSign_StickRegime()
							}.createDelegate(this)
						}]
					},
					{
						layout: 'form',
						border: false,
						width: 30,
						items: [{
							xtype: 'button',
							id: 'swSignStickIrrList',
							tooltip: 'Список версий документа',
							iconCls: 'document16',
							handler: function() {
								this.doOpenSignHistory_WorkRelease({SignObject: 'irr'});
							}.createDelegate(this)
						}]
					},
					{
						layout: 'form',
						border: false,
						width: 30,
						items: [{
							xtype: 'button',
							id: 'swSignStickIrrCheck',
							tooltip: 'Верификация документа',
							iconCls: 'ok16',
							handler: function() {
								this.doVerifySign_WorkRelease({SignObject: 'irr'});
							}.createDelegate(this)
						}]
					}
				]
			},
			{
				autoHeight: true,
				style: 'padding: 2px 0px 0px 0px;',
				title: langs('Лечение в стационаре'),
				xtype: 'fieldset',
				items: [
					{
						border: false,
						layout: 'column',
						items: [
							{
								border: false,
								layout: 'form',
								items: [
									{
										allowBlank: true,
										fieldLabel: langs('Дата начала'),
										format: 'd.m.Y',
										listeners: {
											'change': this._listenerChange_EvnStick_stacBegDate.createDelegate(this)
										},
										name: 'EvnStick_stacBegDate',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										selectOnFocus: true,
										tabIndex: TABINDEX_ESTEF + 23,
										width: 100,
										xtype: 'swdatefield'
									}
								]
							},
							{
								border: false,
								layout: 'form',
								labelWidth: 120,
								items: [{
									text: '=',
									tooltip: langs('Подставить минимальную дату поступления со связанных карт выбывшего из стационара'),
									handler: function() {
										var base_form = this.FormPanel.getForm();
										if (!base_form.findField('EvnStick_stacBegDate').disabled && this.advanceParams.stacBegDate) {
											base_form.findField('EvnStick_stacBegDate').setValue(this.advanceParams.stacBegDate);
										} else {
											base_form.findField('EvnStick_stacBegDate').setValue('');
										}
										base_form.findField('EvnStick_stacBegDate').fireEvent('change', base_form.findField('EvnStick_stacBegDate'), base_form.findField('EvnStick_stacBegDate').getValue());
									}.createDelegate(this),
									id: this.id + 'EStEF_btnSetMinDateFromPS',
									xtype: 'button'
								}]
							}
						]
					},
					{
						border: false,
						layout: 'column',
						items: [
							{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									enableKeyEvents: true,
									fieldLabel: langs('Дата окончания'),
									format: 'd.m.Y',
									listeners: {

										'keydown': function(inp, e) {
											if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
												var base_form = this.FormPanel.getForm();

												e.stopEvent();

												this._keydownFocus_noPanels_CarePersonAndRegime();

											}
										}.createDelegate(this)
									},
									name: 'EvnStick_stacEndDate',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									selectOnFocus: true,
									tabIndex: TABINDEX_ESTEF + 24,
									width: 100,
									xtype: 'swdatefield'
								}]
							},
							{
								border: false,
								layout: 'form',
								labelWidth: 120,
								items: [{
									text: '=',
									tooltip: langs('Подставить максимальную дату выписки со связанных карт выбывшего из стационара'),
									handler: function() {
										var base_form = this.FormPanel.getForm();
										if (!base_form.findField('EvnStick_stacEndDate').disabled && this.advanceParams.stacEndDate) {
											base_form.findField('EvnStick_stacEndDate').setValue(this.advanceParams.stacEndDate);
										} else {
											base_form.findField('EvnStick_stacEndDate').setValue('');
										}
										base_form.findField('EvnStick_stacEndDate').fireEvent('change', base_form.findField('EvnStick_stacEndDate'), base_form.findField('EvnStick_stacEndDate').getValue());
									}.createDelegate(this),
									id: this.id + 'EStEF_btnSetMaxDateFromPS',
									xtype: 'button'
								}]
							}
						]
					}
				]
			},
			{
				hidden: getRegionNick()!='kz',
				autoHeight: true,
				style: 'padding: 2px 0px 0px 0px;',
				title: langs('Перевести временно на другую работу'),
				xtype: 'fieldset',
				items: [
					{
						allowBlank: true,
						enableKeyEvents: true,
						fieldLabel: langs('Дата начала'),
						format: 'd.m.Y',
						name: 'EvnStick_regBegDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: TABINDEX_ESTEF + 15,
						width: 100,
						xtype: 'swdatefield'
					},
					{
						allowBlank: true,
						enableKeyEvents: true,
						fieldLabel: langs('Дата окончания'),
						format: 'd.m.Y',
						name: 'EvnStick_regEndDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: TABINDEX_ESTEF + 15,
						width: 100,
						xtype: 'swdatefield'
					}
				]
			}
		]
	});

	// 3. МСЭ
	this.panelMSE = new sw.Promed.Panel({
		autoHeight: true,
		bodyStyle: 'padding-top: 0.5em;',
		border: true,
		labelWidth: 280,
		collapsible: true,
		id: this.id + 'EStEF_MSEPanel',
		layout: 'form',
		listeners: {
			'expand': function(panel) {
				//
			}.createDelegate(this)
		},
		style: 'margin-bottom: 0.5em;',
		title: langs('3. МСЭ'),
		items: [{
			allowBlank: true,
			enableKeyEvents: true,
			fieldLabel: langs('Дата направления в бюро МСЭ'),
			format: 'd.m.Y',
			listeners: {
				'change': function(field, newValue, oldValue) {
					var base_form = this.FormPanel.getForm();
				}.createDelegate(this),
				'keydown': function(inp, e) {
					if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
						var base_form = this.FormPanel.getForm();

						e.stopEvent();

						this._keydownFocus2();

					}
				}.createDelegate(this)
			},
			name: 'EvnStick_mseDate',
			plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
			selectOnFocus: true,
			tabIndex: TABINDEX_ESTEF + 25,
			width: 100,
			xtype: 'swdatefield'
		}, {
			allowBlank: true,
			fieldLabel: langs('Дата регистрации документов в бюро МСЭ'),
			format: 'd.m.Y',
			listeners: {
				'change': function(field, newValue, oldValue) {
					var base_form = this.FormPanel.getForm();
				}.createDelegate(this)
			},
			name: 'EvnStick_mseRegDate',
			plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
			selectOnFocus: true,
			tabIndex: TABINDEX_ESTEF + 26,
			width: 100,
			xtype: 'swdatefield'
		}, {
			allowBlank: true,
			fieldLabel: langs('Дата освидетельствования в бюро МСЭ'),
			format: 'd.m.Y',
			listeners: {
				'change': function(field, newValue, oldValue) {
					var base_form = this.FormPanel.getForm();
				}.createDelegate(this)
			},
			name: 'EvnStick_mseExamDate',
			plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
			selectOnFocus: true,
			tabIndex: TABINDEX_ESTEF + 27,
			width: 100,
			xtype: 'swdatefield'
		}, {
			comboSubject: 'YesNo',
			enableKeyEvents: true,
			hidden: (getRegionNick()!='kz'),
			fieldLabel: langs('Установлена группа инвалидности'),
			hiddenName: 'EvnStick_IsDisability',
			lastQuery: '',
			listeners: {
				'keydown': function(inp, e) {
					if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
						var base_form = this.FormPanel.getForm();

						e.stopEvent();
						this._keydownFocus_noPanels_CarePersonAndRegimeAndMSE();

					}
				}.createDelegate(this)
			},
			tabIndex: TABINDEX_ESTEF + 28,
			width: 100,
			xtype: 'swcommonsprcombo'
		}, {
			comboSubject: 'InvalidGroupType',
			enableKeyEvents: true,
			hidden: (getRegionNick()=='kz'),
			fieldLabel: langs('Установлена/изменена группа инвалидности'),
			hiddenName: 'InvalidGroupType_id',
			lastQuery: '',
			listeners: {
				'keydown': function(inp, e) {
					if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
						var base_form = this.FormPanel.getForm();

						e.stopEvent();

						this._keydownFocus_noPanels_CarePersonAndRegimeAndMSE();
					}
				}.createDelegate(this)
			},
			tabIndex: TABINDEX_ESTEF + 28,
			width: 180,
			xtype: 'swcommonsprcombo'
		}]
	});

	// Меню "Действия" в блоке 4. Освобождение от работы
	this.menuActions_WorkRelease = new Ext.menu.Menu({
		items: [

			// 'Подписать (Врач)
			{
				text:'Подписать (Врач)',
				iconCls : 'signature16',
				tooltip: 'Подписать (Врач)',
				id: 'leaveActionsSign',
				handler: function(){this.doSign_WorkRelease({SignObject: 'MP'})}.createDelegate(this)
			},

			// Список версий документа (Врач)
			{
				text:'Список версий документа (Врач)',
				iconCls : 'document16',
				tooltip: 'Список версий документа (Врач)',
				id: 'leaveActionsList',
				handler: function(){this.doOpenSignHistory_WorkRelease({SignObject: 'MP'})}.createDelegate(this)
			},

			// Верификация документа (Врач)
			{
				text:'Верификация документа (Врач)',
				iconCls : 'ok16',
				tooltip: 'Верификация документа (Врач)',
				id: 'leaveActionsCheck',
				handler: function(){this.doVerifySign_WorkRelease({SignObject: 'MP'})}.createDelegate(this)
			},

			// Подписать (ВК)
			{
				text:'Подписать (ВК)',
				iconCls : 'signature16',
				tooltip: 'Подписать (ВК)',
				id: 'leaveActionsSignVK',
				handler: function(){this.doSign_WorkRelease({SignObject: 'VK'})}.createDelegate(this)
			},

			// Список версий документа (ВК)
			{
				text:'Список версий документа (ВК)',
				iconCls : 'document16',
				tooltip: 'Список версий документа (ВК)',
				id: 'leaveActionsListVK',
				handler: function(){this.doOpenSignHistory_WorkRelease({SignObject: 'VK'})}.createDelegate(this)
			},

			// Верификация документа (ВК)
			{
				text:'Верификация документа (ВК)',
				iconCls : 'ok16',
				tooltip: 'Верификация документа (ВК)',
				id: 'leaveActionsCheckVK',
				handler: function(){this.doVerifySign_WorkRelease({SignObject: 'VK'})}.createDelegate(this)
			}
		]
	});

	// 4. Освобождение от работы
	this.panelEvnStickWorkRelease = new sw.Promed.Panel({
		border: true,
		collapsible: true,
		height: 175,
		id: this.id + 'EStEF_EvnStickWorkReleasePanel',
		isLoaded: false,
		layout: 'border',
		listeners: {
			expand: function(panel){
				var me = this;
				var base_form = win.FormPanel.getForm();

				if(this._isLoaded_WorkRelease() == false) {

					var evn_stick_id = this.FormPanel.getForm().findField('EvnStick_id').getValue();
					var evn_stick_dop_pid = this.FormPanel.getForm().findField('EvnStickDop_pid').getValue();

					if ( me.action != 'add' ) {
						this._load_WorkRelease({
							EvnStick_id: evn_stick_id,
							EvnStickDop_pid: (evn_stick_dop_pid > 0) ? evn_stick_dop_pid : null,
							StickWorkType_id: base_form.findField('StickWorkType_id').getValue()
						}, function(){
							panel.isLoaded = true;

							if (Ext.isEmpty(this.FormPanel.getForm().findField('EvnStick_disDate').getValue())) {
								this.setEvnStickDisDate();
							}

							if (panel.findById(me.id + 'EStEF_EvnStickWorkReleaseGrid').getStore().getCount() > 0) {

								this.findById(me.id + 'EStEF_StickLeavePanel').expand();

							}
						}.createDelegate(this));

					}
				}

				panel.doLayout();
			}.createDelegate(this)
		},
		style: 'margin-bottom: 0.5em;',
		title: langs('4. Освобождение от работы'),

		items: [
			new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand_workrelease',
				autoExpandMin: 100,
				border: false,
				columns: [

					// С какого числа (EvnStickWorkRelease_begDate)
					{
						dataIndex: 'EvnStickWorkRelease_begDate',
						header: langs('С какого числа'),
						hidden: false,
						renderer: Ext.util.Format.dateRenderer('d.m.Y'),
						resizable: false,
						sortable: true,
						width: 100
					},

					// По какое число (EvnStickWorkRelease_endDate)
					{
						dataIndex: 'EvnStickWorkRelease_endDate',
						header: langs('По какое число'),
						hidden: false,
						renderer: Ext.util.Format.dateRenderer('d.m.Y'),
						resizable: false,
						sortable: true,
						width: 100
					},

					// МО (Org_Name)
					{
						dataIndex: 'Org_Name',
						header: langs('МО'),
						hidden: false,
						resizable: true,
						sortable: true,
						width: 200
					},

					// Врач (MedPersonal_Fio)
					{
						dataIndex: 'MedPersonal_Fio',
						header: langs('Врач'),
						hidden: false,
						id: 'autoexpand_workrelease',
						resizable: true,
						sortable: true
					},

					// Статус (EvnStickWorkRelease_IsDraft)
					{
						dataIndex: 'EvnStickWorkRelease_IsDraft',
						header: langs('Статус'),
						renderer: function(v, p, row) {
							if (row.get('StickFSSType_Name')) {
								return row.get('StickFSSType_Name');
							}

							if (!Ext.isEmpty(v) && v == 1) {
								return langs('Черновик');
							}

							if (Ext.isEmpty(v))
								return '';

							return langs('Утвержден');
						}.createDelegate(this),
						hidden: false,
						width: 100
					},

					// Подписан (Врач) (SMP_Status_Name)
					{
						dataIndex: 'SMP_Status_Name',
						header: 'Подписан (Врач)',
						hidden: getRegionNick() == 'kz',
						width: 120
					},

					// Дата подписания (Врач) (SMP_updDT)
					{
						dataIndex: 'SMP_updDT',
						header: 'Дата подписания (Врач)',
						hidden: getRegionNick() == 'kz',
						width: 100
					},

					// ФИО подписавшего (Врач) (SMP_updUser_Name)
					{
						dataIndex: 'SMP_updUser_Name',
						header: 'ФИО подписавшего (Врач)',
						hidden: getRegionNick() == 'kz',
						width: 150
					},

					// Подписан (ВК) (SVK_Status_Name)
					{
						dataIndex: 'SVK_Status_Name',
						header: 'Подписан (ВК)',
						hidden: getRegionNick() == 'kz',
						width: 120
					},

					// Дата подписания (ВК) (SVK_updDT)
					{
						dataIndex: 'SVK_updDT',
						header: 'Дата подписания (ВК)',
						hidden: getRegionNick() == 'kz',
						width: 100
					},

					// ФИО подписавшего (ВК) (SVK_updUser_Name)
					{
						dataIndex: 'SVK_updUser_Name',
						header: 'ФИО подписавшего (ВК)',
						hidden: getRegionNick() == 'kz',
						width: 150
					}
				],
				frame: false,
				id: this.id + 'EStEF_EvnStickWorkReleaseGrid',
				keys: [{
					key: [
						Ext.EventObject.DELETE,
						Ext.EventObject.END,
						Ext.EventObject.ENTER,
						Ext.EventObject.F3,
						Ext.EventObject.F4,
						Ext.EventObject.HOME,
						Ext.EventObject.INSERT,
						Ext.EventObject.PAGE_DOWN,
						Ext.EventObject.PAGE_UP,
						Ext.EventObject.TAB
					],
					fn: function(inp, e) {
						e.stopEvent();

						this._stopPagination();

						var grid = this._get_WorkReleaseGrid();

						switch ( e.getKey() ) {
							case Ext.EventObject.DELETE:
								this.deleteGridRecord('EvnStickWorkRelease');
								break;

							case Ext.EventObject.END:
								GridEnd(grid);
								break;

							case Ext.EventObject.ENTER:
							case Ext.EventObject.F3:
							case Ext.EventObject.F4:
							case Ext.EventObject.INSERT:
								if ( !grid.getSelectionModel().getSelected() ) {
									return false;
								}

								var action = 'add';

								if ( e.getKey() == Ext.EventObject.F3 ) {
									action = 'view';
								}
								else if ( e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER ) {
									action = 'edit';
								}

								this.openEvnStickWorkReleaseEditWindow(action);
								break;

							case Ext.EventObject.HOME:
								GridHome(grid);
								break;

							case Ext.EventObject.PAGE_DOWN:
								GridPageDown(grid);
								break;

							case Ext.EventObject.PAGE_UP:
								GridPageUp(grid);
								break;

							case Ext.EventObject.TAB:
								var base_form = this.FormPanel.getForm();

								grid.getSelectionModel().clearSelections();
								grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());

								if ( e.shiftKey == false ) {
									this._keydownFocus_noPanels_CarePersonAndRegimeAndMSEAndWorkRelease();
								}
								else {
									this._keydownFocus3();
								}
								break;
						}
					}.createDelegate(this),
					scope: this,
					stopEvent: true
				}],
				layout: 'fit',
				listeners: {
					'rowdblclick': function(grid, number, obj) {
						this.openEvnStickWorkReleaseEditWindow('edit');
					}.createDelegate(this)
				},
				loadMask: true,
				region: 'center',
				sm: new Ext.grid.RowSelectionModel({
					listeners: {
						'rowselect': function(sm, rowIndex, record) {
							var me = this;
							var access_type = 'view';
							var sign_access = 'view';
							var id = null;
							var selected_record = sm.getSelected();
							var toolbar = this.findById(this.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar();

							if ( selected_record ) {
								access_type = selected_record.get('accessType');
								sign_access = selected_record.get('signAccess');
								id = selected_record.get('EvnStickWorkRelease_id');
							}

							// кнопка изменить запись
							toolbar.items.items[1].disable();

							// кнопка удалить запись
							toolbar.items.items[3].disable();

							// кнопка действия для записи
							toolbar.items.items[6].disable();


							// все кнопки блокируем в подменю "действия"
							this.menuActions_WorkRelease.items.each(function(item){
								item.disable()
							});


							if ( id ) {

								// кнопка просмотр записи
								toolbar.items.items[2].enable();

								if ( this.evnStickType.inlist([1,2]) && this.action != 'view' && access_type == 'edit' ) {

									// кнопка изменить запись
									toolbar.items.items[1].enable();

									// кнопка удалить запись
									toolbar.items.items[3].enable();
								}

								if ( this.evnStickType.inlist([1,2]) && this.action != 'view' && sign_access == 'edit' ) {

									// кнопка действия для записи
									toolbar.items.items[6].enable();


									// Доступ к кнопке "Подписать (Врачом)"
									me._closeAccessToField_WorkRelease_Sign();
									if(me._checkAccessToField_WorkRelease_Sign()){
										me._openAccessToField_WorkRelease_Sign();
									}


									// Доступ к кнопке "Подписать (Врачом ВК)"
									me._closeAccessToField_WorkRelease_SignVK();
									if(me._checkAccessToField_WorkRelease_SignVK()){
										me._openAccessToField_WorkRelease_SignVK();
									}


									if (selected_record.get('SMPStatus_id') == 1) {
										this.menuActions_WorkRelease.items.get('leaveActionsList').enable();
										this.menuActions_WorkRelease.items.get('leaveActionsCheck').enable();
									}
									if (selected_record.get('SVKStatus_id') == 1) {
										this.menuActions_WorkRelease.items.get('leaveActionsListVK').enable();
										this.menuActions_WorkRelease.items.get('leaveActionsCheckVK').enable();
									}
								}
							}
							else {

								// кнопка просмотр записи
								toolbar.items.items[2].disable();

							}
						}.createDelegate(this)
					}
				}),
				stripeRows: true,
				store: new Ext.data.Store({
					autoLoad: false,
					listeners: {
						'load': function(store, records, index) {
							if ( store.getCount() == 0 ) {
								LoadEmptyRow(this.findById(this.id+'EStEF_EvnStickWorkReleaseGrid'));
							}

							this.loadMedStaffFactList();
							this.checkLastEvnStickWorkRelease();

							if(this.action != 'view') {
								this.refreshFormPartsAccess();
							}

							var paid_counter = 0;
							var update_button = Ext.getCmp('updateEvnStickWorkReleaseGrid');
							
							store.each(function(rec) {
								if (rec.get('EvnStickWorkRelease_IsPaid') == 2) {
									paid_counter ++;
								}
							});

							if(paid_counter >= 3) {
								update_button.disable();
							} else {
								update_button.enable();
							}
							

							this.checkSaveButtonEnabled();

							this.getLoadMask().hide();
						}.createDelegate(this)
					},
					reader: new Ext.data.JsonReader(
						{
							id: 'EvnStickWorkRelease_id'
						},
						[
							{
								mapping: 'accessType',
								name: 'accessType',
								type: 'string'
							},
							{
								mapping: 'signAccess',
								name: 'signAccess',
								type: 'string'
							},
							{
								mapping: 'Lpu_id',
								name: 'Lpu_id',
								type: 'int'
							},
							{
								mapping: 'EvnStickWorkRelease_id',
								name: 'EvnStickWorkRelease_id',
								type: 'int'
							},
							{
								mapping: 'EvnStickBase_id',
								name: 'EvnStickBase_id',
								type: 'int'
							},
							{
								mapping: 'Org_id',
								name: 'Org_id',
								type: 'int'
							},
							{
								mapping: 'EvnStickWorkRelease_IsDraft',
								name: 'EvnStickWorkRelease_IsDraft',
								type: 'int'
							},
							{
								mapping: 'StickFSSType_Name',
								name: 'StickFSSType_Name',
								type: 'string'
							},
							{
								mapping: 'EvnStickWorkRelease_IsSpecLpu',
								name: 'EvnStickWorkRelease_IsSpecLpu',
								type: 'int'
							},
							{
								mapping: 'Signatures_mid',
								name: 'Signatures_mid',
								type: 'int'
							},
							{
								mapping: 'SMPStatus_id',
								name: 'SMPStatus_id',
								type: 'int'
							},
							{
								mapping: 'SMP_Status_Name',
								name: 'SMP_Status_Name',
								type: 'string'
							},
							{
								mapping: 'SMP_updDT',
								name: 'SMP_updDT',
								type: 'string'
							},
							{
								mapping: 'SMP_updUser_Name',
								name: 'SMP_updUser_Name',
								type: 'string'
							},
							{
								mapping: 'Signatures_wid',
								name: 'Signatures_wid',
								type: 'int'
							},
							{
								mapping: 'SVKStatus_id',
								name: 'SVKStatus_id',
								type: 'int'
							},
							{
								mapping: 'SVK_Status_Name',
								name: 'SVK_Status_Name',
								type: 'string'
							},
							{
								mapping: 'SVK_updDT',
								name: 'SVK_updDT',
								type: 'string'
							},
							{
								mapping: 'SVK_updUser_Name',
								name: 'SVK_updUser_Name',
								type: 'string'
							},
							{
								mapping: 'EvnStickWorkRelease_IsInReg',
								name: 'EvnStickWorkRelease_IsInReg',
								type: 'int'
							},
							{
								mapping: 'EvnStickWorkRelease_IsPaid',
								name: 'EvnStickWorkRelease_IsPaid',
								type: 'int'
							},
							{
								mapping: 'LpuSection_id',
								name: 'LpuSection_id',
								type: 'int'
							},
							{
								mapping: 'LpuUnitType_SysNick',
								name: 'LpuUnitType_SysNick',
								type: 'string'
							},
							{
								mapping: 'MedPersonal_id',
								name: 'MedPersonal_id',
								type: 'int'
							},
							{
								mapping: 'MedPersonal2_id',
								name: 'MedPersonal2_id',
								type: 'int'
							},
							{
								mapping: 'MedPersonal3_id',
								name: 'MedPersonal3_id',
								type: 'int'
							},
							{
								mapping: 'MedStaffFact_id',
								name: 'MedStaffFact_id',
								type: 'int'
							},
							{
								mapping: 'MedStaffFact2_id',
								name: 'MedStaffFact2_id',
								type: 'int'
							},
							{
								mapping: 'MedStaffFact3_id',
								name: 'MedStaffFact3_id',
								type: 'int'
							},
							{
								mapping: 'RecordStatus_Code',
								name: 'RecordStatus_Code',
								type: 'int'
							},
							{
								mapping: 'Org_Name',
								name: 'Org_Name',
								type: 'string'
							},
							{
								dateFormat: 'd.m.Y',
								mapping: 'EvnStickWorkRelease_begDate',
								name: 'EvnStickWorkRelease_begDate',
								type: 'date'
							},
							{
								dateFormat: 'd.m.Y',
								mapping: 'EvnStickWorkRelease_endDate',
								name: 'EvnStickWorkRelease_endDate',
								type: 'date'
							},
							{
								mapping: 'MedPersonal_Fio',
								name: 'MedPersonal_Fio',
								type: 'string'
							},
							{
								mapping: 'EvnStickWorkRelease_IsPredVK',
								name: 'EvnStickWorkRelease_IsPredVK',
								type: 'int'
							},
							{
								mapping: 'Post_id',
								name: 'Post_id',
								type: 'int'
							},
							{
								mapping: 'EvnVK_id',
								name: 'EvnVK_id',
								type: 'int'
							},
							{
								mapping: 'EvnVK_NumProtocol',
								name: 'EvnVK_NumProtocol',
								type: 'string'
							},
							{
								mapping: 'EvnVK_descr',
								name: 'EvnVK_descr',
								type: 'string'
							},
							{
								mapping: 'EvnStickWorkRelease_updDT',
								name: 'EvnStickWorkRelease_updDT',
								type: 'string'
							}
						]
					),
					url: '/?c=Stick&m=loadEvnStickWorkReleaseGrid'
				}),
				tbar: new sw.Promed.Toolbar({
					buttons: [

						// add
						{
							handler: function() {
								this.openEvnStickWorkReleaseEditWindow('add');
							}.createDelegate(this),
							iconCls: 'add16',
							text: BTN_GRIDADD,
							tooltip: BTN_GRIDADD_TIP
						},

						// edit
						{
							handler: function() {
								this.openEvnStickWorkReleaseEditWindow('edit');
							}.createDelegate(this),
							iconCls: 'edit16',
							text: BTN_GRIDEDIT,
							tooltip: BTN_GRIDEDIT_TIP
						},

						// view
						{
							handler: function() {
								this.openEvnStickWorkReleaseEditWindow('view');
							}.createDelegate(this),
							iconCls: 'view16',
							text: BTN_GRIDVIEW,
							tooltip: BTN_GRIDVIEW_TIP
						},

						// delete
						{
							handler: function() {
								this.deleteGridRecord('EvnStickWorkRelease');
							}.createDelegate(this),
							iconCls: 'delete16',
							text: BTN_GRIDDEL,
							tooltip: BTN_GRIDDEL_TIP
						},

						// refresh
						{
							id: 'updateEvnStickWorkReleaseGrid',
							handler: function() {
								this.updateEvnStickWorkReleaseGrid();
							}.createDelegate(this),
							iconCls: 'refresh16',
							text: BTN_GRIDREFR,
							tooltip: BTN_GRIDREFR_TIP
						},

						// open Calculation Window
						{
							id: 'openEvnStickWorkReleaseCalculationWindow',
							handler: function() {
								this.openEvnStickWorkReleaseCalculationWindow();
							}.createDelegate(this),
							text: langs('Дней нетрудоспособности в году'),
							tooltip: langs('Дней нетрудоспособности в году')
						},

						// Действия
						{
							id: 'actionEvnStickWorkReleaseGrid',
							iconCls: 'actions16',
							text: 'Действия',
							tooltip: 'Действия',
							menu: this.menuActions_WorkRelease,
							hidden: getRegionNick() == 'kz'
						}
					]
				})
			})
		]
	});

	// 5. Исход ЛВН
	this.panelStickLeave = new sw.Promed.Panel({
		autoHeight: true,
		labelWidth: 200,
		bodyStyle: 'padding-top: 0.5em;',
		border: true,
		collapsible: true,
		id: this.id + 'EStEF_StickLeavePanel',
		layout: 'form',
		listeners: {
			'expand': function(panel){}.createDelegate(this)
		},
		style: 'margin-bottom: 0.5em;',
		title: langs('5. Исход ЛВН'),
		items: [
			{
				border: false,
				layout: 'column',
				items: [
					{
						border: false,
						layout: 'form',
						items: [
							{
								allowBlank: true,
								comboSubject: 'StickLeaveType',
								fieldLabel: langs('Исход ЛВН'),
								id: 'ESEW_StickLeaveType_id',
								hiddenName: 'StickLeaveType_id',
								onLoadStore: function(){}.createDelegate(this),
								listeners: {
									'change': function(combo, newValue, oldValue) {
										this.resetSignStatus();
										combo.fireEvent('select', combo, combo.getStore().getById(newValue));
									}.createDelegate(this),
									'keydown': function(inp, e) {
										var me = this;

										var base_form = this.FormPanel.getForm();

										if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
											e.stopEvent();

											this._keydownFocus4();

										}
									}.createDelegate(this),
									'beforeselect': function(combo, record) {
										// чтобы корректно работал TAB и переходил на показываемое поле в процессе выполнения события select..
										if (record && record.get('StickLeaveType_id')) {
											this.fireEvent('change', this, record.get('StickLeaveType_id'));
										}
									}.createDelegate(this),
									'select': function(combo, record) {
										var me = this;
										var base_form = this.FormPanel.getForm();

										if ( !record || !record.get('StickLeaveType_id') ) {
											base_form.findField('EvnStick_disDate').setContainerVisible(false);
											base_form.findField('EvnStick_disDate').setAllowBlank(true);
											base_form.findField('EvnStick_disDate').setRawValue('');
											base_form.findField('Lpu_oid').clearValue();
											base_form.findField('Lpu_oid').setContainerVisible(false);
											base_form.findField('EvnStick_NumNext').setContainerVisible(false);
											base_form.findField('MedStaffFact_id').clearValue();
											base_form.findField('MedStaffFact_id').setAllowBlank(true);
											base_form.findField('MedStaffFact_id').setContainerVisible(false);
											if (!getRegionNick().inlist(['kz','astra'])) {
												base_form.findField('EvnStick_sstNum').setAllowBlank(true);
											}
											this.findById('SLeaveStatus_Name').getEl().dom.innerHTML = '';
											this.findById('SLeaveStatus_Name').render();
											this.findById('swSignStickLeave').disable();
											return false;
										}

										var stick_cause_sys_nick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');

										if (getRegionNick()!='kz') {
											base_form.findField('InvalidGroupType_id').setContainerVisible(true);
											base_form.findField('EvnStick_IsDisability').setContainerVisible(false);
										} else {
											base_form.findField('InvalidGroupType_id').setContainerVisible(false);
											base_form.findField('EvnStick_IsDisability').setContainerVisible(true);
										}

										if (!getRegionNick().inlist(['kz', 'astra']) && base_form.findField('EvnStick_sstNum').isVisible()) {
											base_form.findField('EvnStick_sstNum').setAllowBlank(false);
										}

										base_form.findField('EvnStick_disDate').setContainerVisible(true);
										base_form.findField('EvnStick_disDate').setAllowBlank(false);
										base_form.findField('MedStaffFact_id').setContainerVisible(true);
										base_form.findField('MedStaffFact_id').setAllowBlank(false);

										this.checkLastEvnStickWorkRelease();
										this.loadMedStaffFactList();

										if ( record.get('StickLeaveType_Code').inlist([ '31', '32', '33', '37' ]) ) {
											base_form.findField('Lpu_oid').setContainerVisible(true);
										}
										else {
											base_form.findField('Lpu_oid').clearValue();
											base_form.findField('Lpu_oid').setContainerVisible(false);
										}

										if ( record.get('StickLeaveType_Code').inlist([ '31', '37' ]) ) {
											base_form.findField('EvnStick_NumNext').setContainerVisible(true);
										}
										else {
											base_form.findField('EvnStick_NumNext').setContainerVisible(false);
										}

										this.setEvnStickDisDate();
										this._listenerChange_EvnStick_stacBegDate();

										if (this.action != 'view') {
											this.findById('swSignStickLeave').enable();
										}

									}.createDelegate(this)
								},
								tabIndex: TABINDEX_ESTEF + 30,
								width: 500,
								xtype: 'swcommonsprcombo'
							}
						]
					},
					{
						border: false,
						layout: 'form',
						labelWidth: 120,
						items: [{
							allowBlank: true,
							fieldLabel: langs('Дата исхода ЛВН'),
							format: 'd.m.Y',
							listeners: {
								'change': function(field, newValue, oldValue) {
									// Загрузка списка врачей
									if (getRegionNick() != 'kz'){
										this.resetSignStatus();
										this.checkGetEvnStickNumButton();
									}
								}.createDelegate(this)
							},
							id: 'EStEF_EvnStick_disDate',
							name: 'EvnStick_disDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_ESTEF + 31,
							width: 100,
							xtype: 'swdatefield'
						}]
					}
				]
			},
			{
				name: 'EvnStick_NumNext',
				listWidth: 350,
				fieldLabel: 'ЛВН-продолжение',
				disabled: true,
				xtype: 'textfield'
			},
			{
				dateFieldId: 'EStEF_EvnStick_disDate',
				enableOutOfDateValidation: false,
				fieldLabel: langs('Врач'),
				hiddenName: 'MedStaffFact_id',
				id: 'EStEF_MedStaffFactCombo',
				lastQuery: '',
				listWidth: 670,
				tabIndex: TABINDEX_ESTEF + 32,
				width: 500,
				xtype: 'swmedstafffactglobalcombo',
				listeners: {
					'focus': function(cmp) {
						if(getRegionNick() == 'kareliya') {
							var time_start = (Ext.getCmp('EStEF_EvnStick_disDate').getValue() != '') ? Ext.getCmp('EStEF_EvnStick_disDate').getValue() : new Date();
							var onDate = Ext.util.Format.date(time_start, 'd.m.Y');
							cmp.baseFilterFn = setMedStaffFactGlobalStoreFilter({onDate: onDate}, cmp.store, true);
						}
					}.createDelegate(this),
					change: function() {
						this.checkSaveButtonEnabled();
						
						// определяем доступность кнопки подписания исхода
						if(this._checkAccessToField_StickLeave_Sign() == true){
							this._openAccessToField_StickLeave_Sign();
						} else {
							this._closeAccessToField_StickLeave_Sign();
						}

						this.resetSignStatus();
					}.createDelegate(this)
				}
			},
			{
				fieldLabel: langs('Направлен в другую МО'),
				hiddenName: 'Lpu_oid',
				listWidth: 600,
				tabIndex: TABINDEX_ESTEF + 33,
				width: 500,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						this.resetSignStatus();
					}.createDelegate(this)
				},
				xtype: 'swlpucombo'
			},
			{
				layout: 'column',
				labelWidth: 180,
				bodyStyle: 'margin: 10px 0',
				width: 980,
				border: false,
				hidden: getRegionNick() == 'kz',
				items: [
					{
						layout: 'form',
						border: false,
						width: 620,
						items: [{
							style: 'padding: 4px 10px 0 205px; font-size: 1.1em; text-align: right',
							autoHeight: true,
							bodyBorder: false,
							border: false,
							id: 'SLeaveStatus_Name',
							html: ''
						}]
					},
					{
						layout: 'form',
						border: false,
						width: 30,
						items: [{
							xtype: 'button',
							id: 'swSignStickLeave',
							tooltip: 'Подписать',
							iconCls: 'signature16',
							handler: function(){
								this.doSign_StickLeave();
							}.createDelegate(this)
						}]
					},
					{
						layout: 'form',
						border: false,
						width: 30,
						items: [{
							xtype: 'button',
							id: 'swSignStickLeaveList',
							tooltip: 'Список версий документа',
							iconCls: 'document16',
							handler: function() {
								this.doOpenSignHistory_WorkRelease({SignObject: 'leave'});
							}.createDelegate(this)
						}]
					},
					{
						layout: 'form',
						border: false,
						width: 30,
						items: [{
							xtype: 'button',
							id: 'swSignStickLeaveCheck',
							tooltip: 'Верификация документа',
							iconCls: 'ok16',
							handler: function() {
								this.doVerifySign_WorkRelease({SignObject: 'leave'});
							}.createDelegate(this)
						}]
					}
				]
			}
		]
	});





	this.FormPanel = new Ext.form.FormPanel({
		autoScroll: true,
		bodyBorder: false,
		bodyStyle: 'padding: 5px 5px 0',
		border: false,
		frame: false,
		id: this.id + 'EvnStickEditForm',
		labelAlign: 'right',
		labelWidth: 200,
		reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			},
			[
				{ name: 'accessType' },
				{ name: 'addWorkReleaseAccessType' },
				{ name: 'EvnStick_BirthDate' },
				{ name: 'EvnStick_disDate' },
				{ name: 'EvnStick_id' },
				{ name: 'Person_rid' },
				{ name: 'Person_Snils' },
				{ name: 'EvnStick_irrDate' },
				{ name: 'EvnStick_IsDisability' },
				{ name: 'InvalidGroupType_id' },
				{ name: 'EvnStick_StickDT' },
				{ name: 'EvnStick_IsRegPregnancy' },
				{ name: 'EvnStick_mseDate' },
				{ name: 'EvnStick_mseExamDate' },
				{ name: 'EvnStick_mseRegDate' },
				{ name: 'EvnStick_Num' },
				{ name: 'EvnStick_pid' },
				{ name: 'EvnStick_nid' },
				{ name: 'EvnStick_oid' },
				{ name: 'EvnStick_IsOriginal' },
				{ name: 'EvnStick_prid' },

				//Исход из ЛВН по основному месту работы
				{ name: 'PridStickLeaveType_Code1' },

				//Исход из предыдущего ЛВН
				{ name: 'PridStickLeaveType_Code2' },

				//Причина нетрудоспособности в предыдущем ЛВН
				{name: 'PridStickCause_SysNick' },

				//Код изменения причины нетрудоспособности в предыдущем ЛВН
				{name: 'PridStickCauseDid_SysNick' },

				{ name: 'PridEvnStickWorkRelease_endDate' },
				{ name: 'EvnStick_Ser' },
				{ name: 'EvnStick_setDate' },
				{ name: 'EvnStick_sstBegDate' },
				{ name: 'EvnStick_sstEndDate' },
				{ name: 'EvnStick_sstNum' },
				{ name: 'EvnStick_stacBegDate' },
				{ name: 'EvnStick_stacEndDate' },
				{ name: 'EvnStickDop_pid' },
				{ name: 'EvnStickFullNameText' },
				{ name: 'EvnStickLast_Title' },
				{ name: 'Lpu_oid' },
				{ name: 'Lpu_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'Org_did' },
				{ name: 'Org_id' },
				{ name: 'EvnStick_OrgNick' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'pmUser_insID' },
				{ name: 'Post_id'},
				{ name: 'Post_Name' },
				{ name: 'Server_id' },
				{ name: 'StickCause_did' },
				{ name: 'StickCause_id' },
				{ name: 'StickCauseDopType_id' },
				{ name: 'StickIrregularity_id' },
				{ name: 'StickLeaveType_id' },
				{ name: 'StickOrder_id' },
				{ name: 'StickWorkType_id' },
				{ name: 'CountDubles' },
				{ name: 'MaxDaysLimitAfterStac' },
				{ name: 'EvnSection_setDate' },
				{ name: 'EvnSection_disDate' },
				{ name: 'WorkReleaseSumm', type: 'int' },
				{ name: 'EvnStickNext_id', type: 'int' },
				{ name: 'RegistryESStorage_id', type: 'int' },
				{ name: 'EvnStick_adoptDate' },
				{ name: 'EvnStick_regBegDate' },
				{ name: 'EvnStick_regEndDate' },
				{ name: 'EvnStick_IsPaid' },
				{ name: 'EvnStick_IsInReg' },
				{ name: 'EvnStick_IsDateInReg' },
				{ name: 'EvnStick_IsDateInFSS' },
				{ name: 'StickFSSData_id' },
				{ name: 'EvnStickBase_IsFSS' },
				{ name: 'EvnStick_NumNext' },
				{ name: 'isTubDiag' },
				{ name: 'EvnStickBase_consentDT' },
				{ name: 'EvnStick_NumPar' }

			]
		),
		region: 'center',
		url: '/?c=Stick&m=saveEvnStick',

		items: [


			// -----------------------------------------------------------------------------------------------------
			// HIDDEN FIELDS
			// -----------------------------------------------------------------------------------------------------

			{
				name: 'Lpu_id',
				value: '',
				xtype: 'hidden'
			},

			{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			},

			{
				name: 'addWorkReleaseAccessType',
				xtype: 'hidden'
			},

			{
				name: 'EvnStick_NumPar',
				xtype: 'hidden'
			},

			//ссылка на следующий ЛВН
			{
				name: 'EvnStick_nid',
				xtype: 'hidden'
			},
			// Идентификатор получателя ЛВН
			{
				name: 'Person_rid',
				xtype: 'hidden'
			},
			// Идентификатор основного ЛВН
			{
				name: 'EvnStick_id',
				value: 0,
				xtype: 'hidden'
			},

			// Идентификатор продолжения ЛВН
			{
				name: 'EvnStickNext_id',
				value: null,
				xtype: 'hidden'
			},

			// Текущий номер ЭЛН в хранилище
			{
				name: 'RegistryESStorage_id',
				xtype: 'hidden'
			},

			// Признак «Дата направления в бюро МСЭ в реестре»
			{
				name: 'EvnStick_IsDateInReg',
				xtype: 'hidden'
			},

			// Признак «Дата направления в бюро МСЭ принята в ФСС»
			{
				name: 'EvnStick_IsDateInFSS',
				xtype: 'hidden'
			},

			// Количество дублей ЛВН
			{
				name: 'CountDubles',
				value: 0,
				xtype: 'hidden'
			},

			// Ограничивать ЛВН 10-ью днями, т.к. предыдущий ЛВН закрыт в стаце с прииной долечивание
			{
				name: 'MaxDaysLimitAfterStac',
				value: 1,
				xtype: 'hidden'
			},

			// Идентификатор учетного документа (ТАП, КВС)
			{
				name: 'EvnStick_mid',
				value: null,
				xtype: 'hidden'
			},

			// Идентификатор родительского события
			{
				name: 'EvnStick_pid',
				value: null,
				xtype: 'hidden'
			},

			// Идентификатор первичного ЛВН
			{
				name: 'EvnStick_prid',
				value: 0,
				xtype: 'hidden'
			},

			// Код исхода из ЛВН по основному месту работы
			{
				name: 'PridStickLeaveType_Code1',
				value: 0,
				xtype: 'hidden'
			},

			// Код исхода из предыдущего ЛВН
			{
				name: 'PridStickLeaveType_Code2',
				value: 0,
				xtype: 'hidden'
			},

			// Причина нетрудоспособности в предыдущем ЛВН
			{
				name: 'PridStickCause_SysNick',
				xtype: 'hidden'
			},

			// Код изменения нетрудоспособности в предыдущем ЛВН
			{
				name: 'PridStickCauseDid_SysNick',
				xtype: 'hidden'
			},

			// Сумма периодов освобождений для цепочки ЛВН первичный -> продолжение -> ...
			{
				name: 'WorkReleaseSumm',
				value: 0,
				xtype: 'hidden'
			},

			{
				name: 'MedPersonal_id',
				xtype: 'hidden'
			},
			{
				name: 'pmUser_insID',
				xtype: 'hidden'
			},
			{
				name: 'Person_id',
				value: -1,
				xtype: 'hidden'
			},
			{
				name: 'PersonEvn_id',
				value: -1,
				xtype: 'hidden'
			},
			{
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			},
			{
				name: 'EvnSection_setDate',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'EvnSection_disDate',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'PridEvnStickWorkRelease_endDate',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'EvnStick_IsPaid',
				value: null,
				xtype: 'hidden'
			},
			{
				name: 'EvnStick_IsInReg',
				value: null,
				xtype: 'hidden'
			},

			// Общие данные ЭЛН для отправки в ФСС
			{
				name: 'StickFSSData_id',
				value: null,
				xtype: 'hidden'
			},

			{
				xtype: 'hidden',
				name: 'UAddress_Zip'
			},
			{
				xtype: 'hidden',
				name: 'UKLCountry_id'
			},
			{
				xtype: 'hidden',
				name: 'UKLRGN_id'
			},
			{
				xtype: 'hidden',
				name: 'UKLRGNSocr_id'
			},
			{
				xtype: 'hidden',
				name: 'UKLSubRGN_id'
			},
			{
				xtype: 'hidden',
				name: 'UKLSubRGNSocr_id'
			},
			{
				xtype: 'hidden',
				name: 'UKLCity_id'
			},
			{
				xtype: 'hidden',
				name: 'UKLCitySocr_id'
			},
			{
				xtype: 'hidden',
				name: 'UPersonSprTerrDop_id'
			},
			{
				xtype: 'hidden',
				name: 'UKLTown_id'
			},
			{
				xtype: 'hidden',
				name: 'UKLTownSocr_id'
			},
			{
				xtype: 'hidden',
				name: 'UKLStreet_id'
			},
			{
				xtype: 'hidden',
				name: 'UKLStreetSocr_id'
			},
			{
				xtype: 'hidden',
				name: 'UAddress_House'
			},
			{
				xtype: 'hidden',
				name: 'UAddress_Corpus'
			},
			{
				xtype: 'hidden',
				name: 'UAddress_Flat'
			},
			{
				xtype: 'hidden',
				name: 'UAddressSpecObject_id'
			},
			{
				xtype: 'hidden',
				name: 'UAddressSpecObject_Value'
			},
			{
				xtype: 'hidden',
				name: 'UAddress_Address'
			},
			// -----------------------------------------------------------------------------------------------------




			// ЛВН из ФСС
			{
				name: 'EvnStickBase_IsFSS',
				fieldLabel: 'ЛВН из ФСС',
				disabled: true,
				xtype: 'checkbox'
			},

			// Выдан ФИО
			new sw.Promed.TripleTriggerField({
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: langs('Выдан ФИО'),
				listeners: {
					'keydown': function(inp, e) {
						if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
							e.stopEvent();
							this._focusButtonCancel();
						}
						else if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
							if ( e.F4 == e.getKey() )
								inp.onTrigger1Click();

							if ( e.F2 == e.getKey() )
								inp.onTrigger2Click();

							if ( e.DELETE == e.getKey() && e.altKey)
								inp.onTrigger3Click();

							this._stopPagination(e);

							return false;
						}
					}.createDelegate(this),
					'keyup': function( inp, e ) {
						if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
							this._stopPagination(e);

							return false;
						}
					}.createDelegate(this),
					'change': function(){
						this._setDefaultValueTo_Org_id();
						this._setDefaultValueTo_Post_Name();
						win.checkGetEvnStickNumButton();
					}.createDelegate(this),
				},
				name: 'EvnStickFullNameText',


				// кнопка "лупа" открывает окно поиска человека
				onTrigger1Click: function() {
					var me = this;
					var base_form = this.FormPanel.getForm();

					if ( base_form.findField('EvnStickFullNameText').disabled ) {
						return false;
					}

					getWnd('swPersonSearchWindow').show({
						onSelect: function(person_data) {

							//
							console.log(person_data);

							if(!Ext.isEmpty(person_data.Person_Snils)) {
								me.Person_Snils = person_data.Person_Snils;
							} else {
								me.Person_Snils = null;
							}
							
							base_form.findField('Person_id').setValue(person_data.Person_id);
							base_form.findField('PersonEvn_id').setValue(person_data.PersonEvn_id);
							base_form.findField('Server_id').setValue(person_data.Server_id);
							
							me._checkSnils();

							var newValueEvnStickFullNameText = person_data.PersonSurName_SurName + ' ' + person_data.PersonFirName_FirName + ' ' + person_data.PersonSecName_SecName;
							base_form.findField('EvnStickFullNameText').setValue(newValueEvnStickFullNameText);
							base_form.findField('EvnStickFullNameText').fireEvent('change', base_form.findField('EvnStickFullNameText'), newValueEvnStickFullNameText);


							if ( base_form.findField('EvnStickFullNameText').getValue() != '' ) {
								base_form.findField('EvnStick_IsOriginal').enable();
							} else {
								base_form.findField('EvnStick_IsOriginal').disable();
							}


							getWnd('swPersonSearchWindow').hide();

							var loadMask_Addr = new Ext.LoadMask(this.getEl(), { msg: 'Получение адреса регистрации...' });
							loadMask_Addr.show();
							Ext.Ajax.request({
								params: {Person_id: person_data.Person_id},
								url: '/?c=Person&m=getAddressByPersonId',
								success: function(response){
									loadMask_Addr.hide();
									var resp = Ext.util.JSON.decode(response.responseText);
									base_form.findField('UAddress_Zip').setValue(resp[0].UAddress_Zip);
									base_form.findField('UKLCountry_id').setValue(resp[0].UKLCountry_id);
									base_form.findField('UKLRGN_id').setValue(resp[0].UKLRGN_id);
									base_form.findField('UKLSubRGN_id').setValue(resp[0].UKLSubRGN_id);
									base_form.findField('UKLCity_id').setValue(resp[0].UKLCity_id);
									base_form.findField('UPersonSprTerrDop_id').setValue(resp[0].UPersonSprTerrDop_id);
									base_form.findField('UKLTown_id').setValue(resp[0].UKLTown_id);
									base_form.findField('UKLStreet_id').setValue(resp[0].UKLStreet_id);
									base_form.findField('UAddress_House').setValue(resp[0].UAddress_House);
									base_form.findField('UAddress_Corpus').setValue(resp[0].UAddress_Corpus);
									base_form.findField('UAddress_Flat').setValue(resp[0].UAddress_Flat);
									base_form.findField('UAddress_AddressText').setValue(resp[0].UAddress_AddressText);
									base_form.findField('UAddress_Address').setValue(resp[0].UAddress_Address);
								}
							});







						}.createDelegate(this),
						searchMode: 'all'
					});
				}.createDelegate(this),
				trigger1Class: 'x-form-search-trigger',

				// кнопка "=" подставить текущее значение
				onTrigger2Click: function() {
					var win = this;
					var advanceParams = this.advanceParams;
					var base_form = this.FormPanel.getForm();

					if ( base_form.findField('EvnStickFullNameText').disabled ) {
						return false;
					}

					base_form.findField('Person_id').setValue(advanceParams.Person_id);
					base_form.findField('PersonEvn_id').setValue(advanceParams.PersonEvn_id);
					base_form.findField('Server_id').setValue(advanceParams.Server_id);

					win.Person_Snils = advanceParams.Person_Snils;
					win._checkSnils();

					// EvnStickFullNameText
					var newValueEvnStickFullNameText = advanceParams.Person_Surname + ' ' + advanceParams.Person_Firname + ' ' + advanceParams.Person_Secname;
					base_form.findField('EvnStickFullNameText').setValue(newValueEvnStickFullNameText);
					base_form.findField('EvnStickFullNameText').fireEvent('change', base_form.findField('EvnStickFullNameText'), newValueEvnStickFullNameText);


					if ( base_form.findField('EvnStickFullNameText').getValue() != '' ) {
						base_form.findField('EvnStick_IsOriginal').enable();
					} else {
						base_form.findField('EvnStick_IsOriginal').disable();
					}



					var loadMask_Addr = new Ext.LoadMask(this.getEl(), { msg: 'Получение адреса регистрации...' });
					loadMask_Addr.show();
					Ext.Ajax.request({
						params: {Person_id: advanceParams.Person_id},
						url: '/?c=Person&m=getAddressByPersonId',
						success: function(response){
							loadMask_Addr.hide();
							var resp = Ext.util.JSON.decode(response.responseText);
							base_form.findField('UAddress_Zip').setValue(resp[0].UAddress_Zip);
							base_form.findField('UKLCountry_id').setValue(resp[0].UKLCountry_id);
							base_form.findField('UKLRGN_id').setValue(resp[0].UKLRGN_id);
							base_form.findField('UKLSubRGN_id').setValue(resp[0].UKLSubRGN_id);
							base_form.findField('UKLCity_id').setValue(resp[0].UKLCity_id);
							base_form.findField('UPersonSprTerrDop_id').setValue(resp[0].UPersonSprTerrDop_id);
							base_form.findField('UKLTown_id').setValue(resp[0].UKLTown_id);
							base_form.findField('UKLStreet_id').setValue(resp[0].UKLStreet_id);
							base_form.findField('UAddress_House').setValue(resp[0].UAddress_House);
							base_form.findField('UAddress_Corpus').setValue(resp[0].UAddress_Corpus);
							base_form.findField('UAddress_Flat').setValue(resp[0].UAddress_Flat);
							base_form.findField('UAddress_AddressText').setValue(resp[0].UAddress_AddressText);
							base_form.findField('UAddress_Address').setValue(resp[0].UAddress_Address);
						}
					});




				}.createDelegate(this),
				trigger2Class: 'x-form-equil-trigger',

				// кнопка "х" удалить
				onTrigger3Click: function() {
					var base_form = this.FormPanel.getForm();

					if ( base_form.findField('EvnStickFullNameText').disabled ) {
						return false;
					}

					base_form.findField('EvnStickFullNameText').setRawValue('');
					base_form.findField('EvnStick_IsOriginal').disable();
					base_form.findField('Person_id').setValue(0);
					base_form.findField('PersonEvn_id').setValue(0);
					base_form.findField('Server_id').setValue(-1);

					this.Person_Snils = null;
					this._checkSnils();
				}.createDelegate(this),
				trigger3Class: 'x-form-clear-trigger',


				readOnly: true,
				tabIndex: TABINDEX_ESTEF + 1,
				width: 550
			}),


			// Адрес регистрации
			new sw.Promed.TripleTriggerField({
				enableKeyEvents: true,
				fieldLabel: langs('Адрес регистрации'),
				name: 'UAddress_AddressText',
				hidden: (getRegionNick() != 'kz'),
				hideLabel: (getRegionNick() != 'kz'),
				readOnly: true,
				tabIndex: TABINDEX_PEF + 8,
				trigger1Class: 'x-form-search-trigger',

				trigger3Class: 'x-hidden',
				width: 610,

				listeners: {
					'keydown': function(inp, e) {
						if (
							e.F4 == e.getKey() ||
							e.F2 == e.getKey() ||
							(
								e.DELETE == e.getKey() && e.altKey
							)
						) {
							if ( e.F4 == e.getKey() )
								inp.onTrigger1Click();

							if ( e.F2 == e.getKey() )
								inp.onTrigger2Click();

							if ( e.DELETE == e.getKey() && e.altKey)
								inp.onTrigger3Click();

							this._stopPagination(e);

							return false;
						}
					}.createDelegate(this),
					'keyup': function( inp, e ) {
						if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
							this._stopPagination(e);

							return false;
						}
					}.createDelegate(this)
				},

				onTrigger2Click: function() {
					var base_form = this.FormPanel.getForm();
					var person_id = base_form.findField('Person_id').getValue();
					var server_id = base_form.findField('Server_id').getValue();
					var personevn_id = base_form.findField('PersonEvn_id').getValue();
					var loadMask_Addr = new Ext.LoadMask(this.getEl(), { msg: 'Получение адреса регистрации...' });
					loadMask_Addr.show();
					Ext.Ajax.request({
						params: {Person_id: person_id},
						url: '/?c=Person&m=getAddressByPersonId',
						success: function(response){
							loadMask_Addr.hide();
							var resp = Ext.util.JSON.decode(response.responseText);
							base_form.findField('UAddress_Zip').setValue(resp[0].UAddress_Zip);
							base_form.findField('UKLCountry_id').setValue(resp[0].UKLCountry_id);
							base_form.findField('UKLRGN_id').setValue(resp[0].UKLRGN_id);
							base_form.findField('UKLSubRGN_id').setValue(resp[0].UKLSubRGN_id);
							base_form.findField('UKLCity_id').setValue(resp[0].UKLCity_id);
							base_form.findField('UPersonSprTerrDop_id').setValue(resp[0].UPersonSprTerrDop_id);
							base_form.findField('UKLTown_id').setValue(resp[0].UKLTown_id);
							base_form.findField('UKLStreet_id').setValue(resp[0].UKLStreet_id);
							base_form.findField('UAddress_House').setValue(resp[0].UAddress_House);
							base_form.findField('UAddress_Corpus').setValue(resp[0].UAddress_Corpus);
							base_form.findField('UAddress_Flat').setValue(resp[0].UAddress_Flat);
							base_form.findField('UAddress_AddressText').setValue(resp[0].UAddress_AddressText);
							base_form.findField('UAddress_Address').setValue(resp[0].UAddress_Address);
						}
					});
				}.createDelegate(this),
				onTrigger1Click: function() {
					//var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
					var base_form = this.FormPanel.getForm();
					var person_id = base_form.findField('Person_id').getValue();
					var server_id = base_form.findField('Server_id').getValue();
					var personevn_id = base_form.findField('PersonEvn_id').getValue();
					if (!base_form.findField('UAddress_AddressText').disabled)
					{
						getWnd('swAddressEditWindow').show({
							fields: {
								Address_ZipEdit: 			base_form.findField('UAddress_Zip').getValue(),
								KLCountry_idEdit: 			base_form.findField('UKLCountry_id').getValue(),
								KLRgn_idEdit: 				base_form.findField('UKLRGN_id').getValue(),
								KLSubRGN_idEdit: 			base_form.findField('UKLSubRGN_id').getValue(),
								KLCity_idEdit: 				base_form.findField('UKLCity_id').getValue(),
								PersonSprTerrDop_idEdit: 	base_form.findField('UPersonSprTerrDop_id').getValue(),
								KLTown_idEdit: 				base_form.findField('UKLTown_id').getValue(),
								KLStreet_idEdit: 			base_form.findField('UKLStreet_id').getValue(),
								Address_HouseEdit: 			base_form.findField('UAddress_House').getValue(),
								Address_CorpusEdit: 		base_form.findField('UAddress_Corpus').getValue(),
								Address_FlatEdit: 			base_form.findField('UAddress_Flat').getValue(),
								Address_AddressEdit: 		base_form.findField('UAddress_Address').getValue(),
								//Address_begDateEdit: ownerForm.findById('PEW_UAddress_begDate').getValue(),
								AddressSpecObject_idEdit: 	base_form.findField('UAddressSpecObject_Value').getValue(),
								AddressSpecObject_id: 		base_form.findField('UAddressSpecObject_id').getValue(),
								//allowBlankStreet:(Ext.getCmp('PEW_Person_IsAnonym').checked),
								//allowBlankHouse:(Ext.getCmp('PEW_Person_IsAnonym').checked),
								addressType: 0,
								showDate: true
							},
							callback: function(values) {
								base_form.findField('UAddress_Zip').setValue(values.Address_ZipEdit);
								base_form.findField('UKLCountry_id').setValue(values.KLCountry_idEdit);
								base_form.findField('UKLRGN_id').setValue(values.KLRgn_idEdit);
								base_form.findField('UKLRGNSocr_id').setValue(values.KLRGN_Socr);
								base_form.findField('UKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
								base_form.findField('UKLSubRGNSocr_id').setValue(values.KLSubRGN_Socr);
								base_form.findField('UKLCity_id').setValue(values.KLCity_idEdit);
								base_form.findField('UKLCitySocr_id').setValue(values.KLCity_Socr);
								base_form.findField('UPersonSprTerrDop_id').setValue(values.PersonSprTerrDop_idEdit);
								base_form.findField('UKLTown_id').setValue(values.KLTown_idEdit);
								base_form.findField('UKLTownSocr_id').setValue(values.KLTown_Socr);
								base_form.findField('UKLStreet_id').setValue(values.KLStreet_idEdit);
								base_form.findField('UKLStreetSocr_id').setValue(values.KLStreet_Socr);
								base_form.findField('UAddress_House').setValue(values.Address_HouseEdit);
								base_form.findField('UAddress_Corpus').setValue(values.Address_CorpusEdit);
								base_form.findField('UAddress_Flat').setValue(values.Address_FlatEdit);
								base_form.findField('UAddressSpecObject_id').setValue(values.AddressSpecObject_id);
								base_form.findField('UAddressSpecObject_Value').setValue(values.AddressSpecObject_idEdit);
								base_form.findField('UAddress_Address').setValue(values.Address_AddressEdit);
								base_form.findField('UAddress_AddressText').setValue(values.Address_AddressEdit);
								//ownerForm.findById('PEW_UAddress_begDate').setValue(Ext.util.Format.date(values.Address_begDateEdit, 'd.m.Y'));
								base_form.findField('UAddress_AddressText').focus(true, 500);
								//Сохрнаяем адрес человека
								var params = new Object();
								params.Person_id = person_id;
								params.Server_id = server_id;
								params.PersonEvn_id = personevn_id;
								params.KLCountry_id = base_form.findField('UKLCountry_id').getValue();
								params.KLRgn_id = base_form.findField('UKLCountry_id').getValue();
								params.KLSubRgn_id = base_form.findField('UKLSubRGN_id').getValue();
								params.KLCity_id = base_form.findField('UKLCity_id').getValue();
								params.KLTown_id = base_form.findField('UKLTown_id').getValue();
								params.KLStreet_id = base_form.findField('UKLStreet_id').getValue();
								params.Address_Zip = base_form.findField('UAddress_Zip').getValue();
								params.Address_House = base_form.findField('UAddress_House').getValue();
								params.Address_Corpus = base_form.findField('UAddress_Corpus').getValue();
								params.Address_Flat = base_form.findField('UAddress_Flat').getValue();
								params.PersonSprTerrDop_id = base_form.findField('UPersonSprTerrDop_id').getValue();
								params.Address_Address = base_form.findField('UAddress_Address').getValue();
								var loadMask_Addr = new Ext.LoadMask(this.getEl(), { msg: 'Сохранение адреса регистрации...' });
								loadMask_Addr.show();
								Ext.Ajax.request({
									params: params,
									url: '/?c=Person&m=savePersonUAddress',
									success: function(response){
										loadMask_Addr.hide();
										var resp = Ext.util.JSON.decode(response.responseText);
									},
									failure :function()
									{
										loadMask_Addr.hide();
									}
								});
								log(values);
							},
							onClose: function() {
								base_form.findField('UAddress_AddressText').focus(true, 500);
							}
						})
					}
				}.createDelegate(this)
			}),
			{
				border: false,
				hidden: getRegionNick() == 'kz',
				layout: 'column',
				items: [{
					labelWidth: 200,
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: 'СНИЛС',
						disabled: true,
						name: 'Person_Snils',
						xtype: 'textfield'
					}]
				}, {
					labelWidth: 200,
					border: false,
					layout: 'form',
					
					items: [{
						id: win.id + 'setSnilsButton',
						hidden: true,
						xtype: 'label',
						html: '<a href="#" onClick="Ext.getCmp(\'swEvnStickEditWindow\').setSnilsButtonOnClick();return false;">Указать СНИЛС</a>',
						style: {
							'margin-left': '5px'
						}
					}]
				}] 
			},
			{
				border: false,
				layout: 'column',
				items: [
					{
						labelWidth: 200,
						border: false,
						layout: 'form',
						items: [

							// Оригинал
							{
								allowBlank: false,
								comboSubject: 'OriginType',
								fieldLabel: langs('Оригинал'),
								mode: 'local',
								store: new Ext.data.SimpleStore(
									{
										key: 'EvnStick_IsOriginal',
										fields:
											[
												{name: 'EvnStick_IsOriginal', type: 'int'},
												{name: 'OriginType_Name', type: 'string'}
											],
										data: [
											[1, langs('Оригинал')],
											[2, langs('Дубликат')]
										]
									}),
								hiddenName: 'EvnStick_IsOriginal',
								valueField: 'EvnStick_IsOriginal',
								codeField: 'EvnStick_IsOriginal',
								displayField: 'OriginType_Name',
								triggerAction: 'all',
								tpl: new Ext.XTemplate(
									'<tpl for="."><div class="x-combo-list-item">',
									'<font color="red">{EvnStick_IsOriginal}</font>&nbsp;{OriginType_Name}',
									'</div></tpl>'
								),
								tabIndex: TABINDEX_ESTEF + 2,
								width: 200,
								xtype: 'combo',
								listeners: {
									'change': this.listenerChange_EvnStick_IsOriginal.createDelegate(this)
								}
							}
						]
					},
					{
						border: false,
						layout: 'form',
						labelWidth: 200,
						items: [

							// Оригинал ЛВН
							{
								displayField: 'EvnStick_Title',
								fieldLabel: langs('Оригинал ЛВН'),
								hiddenName: 'EvnStick_oid',
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader(
										{
											id: 'EvnStick_id'
										},
										[
											{ name: 'EvnStick_BirthDate', mapping: 'EvnStick_BirthDate' },
											{ name: 'EvnStick_disDate', mapping: 'EvnStick_disDate' },
											{ name: 'EvnStick_id', mapping: 'EvnStick_id' },
											{ name: 'EvnStick_nid', mapping: 'EvnStick_nid' },
											{ name: 'EvnStick_GridId', mapping: 'EvnStick_GridId' },
											{ name: 'EvnStick_irrDate', mapping: 'EvnStick_irrDate' },
											{ name: 'EvnStick_IsDisability', mapping: 'EvnStick_IsDisability' },
											{ name: 'InvalidGroupType_id', mapping: 'InvalidGroupType_id' },
											{ name: 'EvnStick_StickDT', mapping: 'EvnStick_StickDT' },
											{ name: 'EvnStick_IsRegPregnancy', mapping: 'EvnStick_IsRegPregnancy' },
											{ name: 'EvnStick_mseDate', mapping: 'EvnStick_mseDate' },
											{ name: 'EvnStick_mseExamDate', mapping: 'EvnStick_mseExamDate' },
											{ name: 'EvnStick_mseRegDate', mapping: 'EvnStick_mseRegDate' },
											{ name: 'EvnStick_Num', mapping: 'EvnStick_Num' },
											{ name: 'EvnStickDop_pid', mapping: 'EvnStickDop_pid' },
											{ name: 'EvnStick_prid', mapping: 'EvnStick_prid' },
											{ name: 'EvnStick_Ser', mapping: 'EvnStick_Ser' },
											{ name: 'EvnStick_setDate', mapping: 'EvnStick_setDate' },
											{ name: 'EvnStick_sstBegDate', mapping: 'EvnStick_sstBegDate' },
											{ name: 'EvnStick_sstEndDate', mapping: 'EvnStick_sstEndDate' },
											{ name: 'EvnStick_sstNum', mapping: 'EvnStick_sstNum' },
											{ name: 'EvnStick_stacBegDate', mapping: 'EvnStick_stacBegDate' },
											{ name: 'EvnStick_stacEndDate', mapping: 'EvnStick_stacEndDate' },
											{ name: 'EvnStick_Title', mapping: 'EvnStick_Title' },
											{ name: 'EvnStickLast_Title', mapping: 'EvnStickLast_Title' },
											{ name: 'Lpu_oid', mapping: 'Lpu_oid' },
											{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
											{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
											{ name: 'Person_Snils', mapping: 'Person_Snils'},
											{ name: 'Org_did', mapping: 'Org_did' },
											{ name: 'Org_Nick', mapping: 'Org_Nick' },
											{ name: 'Person_id', mapping: 'Person_id' },
											{ name: 'Person_Fio', mapping: 'Person_Fio' },
											{ name: 'PersonEvn_id', mapping: 'PersonEvn_id' },
											{ name: 'Server_id', mapping: 'Server_id' },
											{ name: 'StickWorkType_id', mapping: 'StickWorkType_id' },
											{ name: 'StickCause_did', mapping: 'StickCause_did' },
											{ name: 'StickCause_id', mapping: 'StickCause_id' },
											{ name: 'StickCause_SysNick', mapping: 'StickCause_SysNick'},
											{ name: 'StickCauseDopType_id', mapping: 'StickCauseDopType_id' },
											{ name: 'StickIrregularity_id', mapping: 'StickIrregularity_id' },
											{ name: 'StickLeaveType_id', mapping: 'StickLeaveType_id' },
											{ name: 'StickOrder_id', mapping: 'StickOrder_id' },
											{ name: 'Post_Name', mapping: 'Post_Name' },
											{ name: 'EvnStick_OrgNick', mapping: 'EvnStick_OrgNick' },
											{ name: 'EvnStickBase_consentDT', mapping: 'EvnStickBase_consentDT' },
											{ name: 'Org_id', mapping: 'Org_id' },
											{ name: 'Status', mapping: 'Status' }
										]
									),
									listeners : {
										'load': function() {
											this.listenerLoad_EvnStick_oid();
											this.refreshFormPartsAccess();
										}.createDelegate(this) 
									},
									sortInfo: {
										field: 'EvnStick_Title'
									},
									url: '/?c=Stick&m=getEvnStickOriginalsList'
								}),
								listeners: {
									'change': this.listenerChange_EvnStick_oid.createDelegate(this)
								},
								tabIndex: TABINDEX_ESTEF + 3,
								tpl: (
									(getRegionNick() == 'kz')?
									new Ext.XTemplate(
										'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: center;">',
										'<td style="padding: 2px; width: 70%;">Серия, номер</td>',
										'<td style="padding: 2px; width: 30%;">Дата выдачи</td></tr>',
										'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
										'<td style="padding: 2px;">{EvnStick_Ser}&nbsp;{EvnStick_Num}</td>',
										'<td style="padding: 2px;">{EvnStick_setDate}&nbsp;</td>',
										'</tr></tpl>',
										'</table>'
									):
									new Ext.XTemplate(
										'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: center;">',
										'<td style="padding: 2px; width: 40%;">Серия, номер</td>',
										'<td style="padding: 2px; width: 30%;">Дата выдачи</td>',
										'<td style="padding: 2px; width: 30%;">Состояние</td></tr>',
										'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
										'<td style="padding: 2px;">{EvnStick_Ser}&nbsp;{EvnStick_Num}</td>',
										'<td style="padding: 2px;">{EvnStick_setDate}&nbsp;</td>',
										'<td style="padding: 2px;">{Status}&nbsp;</td>',
										'</tr></tpl>',
										'</table>'
									)
								),
								valueField: 'EvnStick_id',
								width: 200,
								listWidth: 400,
								xtype: 'swbaselocalcombo'
							}
						]
					}
				]
			},


			{
				border: false,
				layout: 'column',
				items: [
					{
						labelWidth: 200,
						border: false,
						layout: 'form',
						hidden: getRegionNick()=='kz',
						items: [

							// Тип занятости
							{
								allowBlank: getRegionNick()=='kz',
								comboSubject: 'StickWorkType',
								fieldLabel: langs('Тип занятости'),
								hiddenName: 'StickWorkType_id',
								listeners: {
									'change': this.listenerChange_StickWorkType_id.createDelegate(this)
								},
								tabIndex: TABINDEX_ESTEF + 2,
								width: 200,
								xtype: 'swcommonsprcombo'
							}
						]
					},
					{
						border: false,
						layout: 'form',
						labelWidth: 200,
						items: [

							// ЛВН по основному месту работы
							{
								displayField: 'EvnStick_Title',
								fieldLabel: langs('ЛВН по основному месту работы'),
								hiddenName: 'EvnStickDop_pid',
								listeners: {
									'select': function(combo, record, index) {
										if (record.get('EvnStick_id') < 0) {
											this.evnStickType = 1;
											sw.swMsg.show({
												buttons: {
													ok: 'Продолжить',
													cancel: 'Отмена'
												},
												fn: function(buttonId, text, obj) {
													if ( buttonId != 'ok' ) {
														combo.setValue(null);
													}
												}.createDelegate(this),
												icon: Ext.MessageBox.QUESTION,
												msg: 'Вы вводите ЛВН для мест работы по совместительству без ЛВН по основному месту работы. Перед сохранением убедитесь в корректности вводимых данных',
												title: langs('Предупреждение')
											});
										}
									}.createDelegate(this),
									'change': this.listenerChange_EvnStickDop_pid.createDelegate(this)
								},
								listWidth: 350,
								store: new Ext.data.Store({
									autoLoad: false,
									reader: new Ext.data.JsonReader({
										id: 'EvnStick_id'
									}, [
										{ name: 'EvnStick_BirthDate', mapping: 'EvnStick_BirthDate' },
										{ name: 'EvnStick_disDate', mapping: 'EvnStick_disDate' },
										{ name: 'EvnStick_id', mapping: 'EvnStick_id' },
										{ name: 'EvnStick_irrDate', mapping: 'EvnStick_irrDate' },
										{ name: 'EvnStick_IsDisability', mapping: 'EvnStick_IsDisability' },
										{ name: 'InvalidGroupType_id', mapping: 'InvalidGroupType_id' },
										{ name: 'EvnStick_IsRegPregnancy', mapping: 'EvnStick_IsRegPregnancy' },
										// { name: 'EvnStick_mid', mapping: 'EvnStick_mid' },
										{ name: 'EvnStick_mseDate', mapping: 'EvnStick_mseDate' },
										{ name: 'EvnStick_mseExamDate', mapping: 'EvnStick_mseExamDate' },
										{ name: 'EvnStick_mseRegDate', mapping: 'EvnStick_mseRegDate' },
										{ name: 'EvnStick_Num', mapping: 'EvnStick_Num' },
										{ name: 'EvnStick_prid', mapping: 'EvnStick_prid' },
										{ name: 'EvnStick_Ser', mapping: 'EvnStick_Ser' },
										{ name: 'EvnStick_setDate', mapping: 'EvnStick_setDate' },
										{ name: 'EvnStick_sstBegDate', mapping: 'EvnStick_sstBegDate' },
										{ name: 'EvnStick_sstEndDate', mapping: 'EvnStick_sstEndDate' },
										{ name: 'EvnStick_sstNum', mapping: 'EvnStick_sstNum' },
										{ name: 'EvnStick_stacBegDate', mapping: 'EvnStick_stacBegDate' },
										{ name: 'EvnStick_stacEndDate', mapping: 'EvnStick_stacEndDate' },
										{ name: 'EvnStick_Title', mapping: 'EvnStick_Title' },
										{ name: 'EvnStickLast_Title', mapping: 'EvnStickLast_Title' },
										{ name: 'PridStickLeaveType_Code', mapping: 'PridStickLeaveType_Code' },
										{ name: 'Lpu_oid', mapping: 'Lpu_oid' },
										{ name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
										{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
										// { name: 'MedPersonal_mseid', mapping: 'MedPersonal_mseid' },
										{ name: 'Org_id', mapping: 'Org_id' },
										{ name: 'Org_did', mapping: 'Org_did' },
										{ name: 'Person_id', mapping: 'Person_id' },
										{ name: 'Person_Fio', mapping: 'Person_Fio' },
										{ name: 'PersonEvn_id', mapping: 'PersonEvn_id' },
										{ name: 'Server_id', mapping: 'Server_id' },
										{ name: 'StickCause_did', mapping: 'StickCause_did' },
										{ name: 'EvnStick_StickDT', mapping: 'EvnStick_StickDT'},
										{ name: 'StickCause_id', mapping: 'StickCause_id' },
										{ name: 'StickCauseDopType_id', mapping: 'StickCauseDopType_id' },
										{ name: 'StickIrregularity_id', mapping: 'StickIrregularity_id' },
										{ name: 'StickLeaveType_id', mapping: 'StickLeaveType_id' },
										{ name: 'StickOrder_id', mapping: 'StickOrder_id' },
										{ name: 'EvnStickBase_consentDT', mapping: 'EvnStickBase_consentDT'}
									]),
									sortInfo: {
										field: 'EvnStick_Title'
									},
									url: '/?c=Stick&m=getEvnStickMainList'
								}),
								tabIndex: TABINDEX_ESTEF + 3,
								tpl: new Ext.XTemplate(
									'<table cellpadding="0" cellspacing="0" style="width: 100%;"><tr style="font-family: tahoma; font-size: 10pt; font-weight: bold; text-align: center;">',
									'<td style="padding: 2px; width: 70%;">Серия, номер</td>',
									'<td style="padding: 2px; width: 30%;">Дата выдачи</td></tr>',
									'<tpl for="."><tr class="x-combo-list-item" style="white-space: normal; overflow: auto; text-overflow: clip;">',
									'<td style="padding: 2px;">{EvnStick_Ser}&nbsp;{EvnStick_Num}</td>',
									'<td style="padding: 2px;">{EvnStick_setDate}&nbsp;</td>',
									'</tr></tpl>',
									'</table>'
								),
								valueField: 'EvnStick_id',
								width: 200,
								xtype: 'swbaselocalcombo'
							}
						]
					}
				]
			},


			// Порядок выдачи
			{
				border: false,
				layout: 'column',
				items: [{
					labelWidth: 200,
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						comboSubject: 'StickOrder',
						fieldLabel: langs('Порядок выдачи'),
						hiddenName: 'StickOrder_id',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								this.setMaxDateForSetDate();
								var base_form = this.FormPanel.getForm();

								var record = combo.getStore().getById(newValue);

								if (Ext.isEmpty(base_form.findField('RegistryESStorage_id').getValue())) {
									base_form.findField('EvnStick_Num').setRawValue('');
									base_form.findField('EvnStick_Num').fireEvent('change', base_form.findField('EvnStick_Num'), base_form.findField('EvnStick_Num').getValue());
								}
								base_form.findField('EvnStick_Ser').setRawValue('');

								if ( record && record.get('StickOrder_Code') == 2 ) {
									base_form.findField('EvnStickLast_Title').setContainerVisible(true);
									base_form.findField('EvnStickLast_Title').setAllowBlank(false);
								}
								else {
									base_form.findField('EvnStick_prid').setValue(0);
									base_form.findField('EvnStickLast_Title').setRawValue('');
									base_form.findField('EvnStickLast_Title').setContainerVisible(false);
									base_form.findField('EvnStickLast_Title').setAllowBlank(true);
								}
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_ESTEF + 4,
						width: 200,
						xtype: 'swcommonsprcombo'
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 200,
					items: [{
						allowBlank: true,
						enableKeyEvents: true,
						fieldLabel: langs('Предыдущий ЛВН'),
						listeners: {
							'keydown': function(inp, e) {
								if ( inp.disabled ) {
									return false;
								}

								switch ( e.getKey() ) {
									case Ext.EventObject.DELETE:
										e.stopEvent();
										var base_form = this.FormPanel.getForm();
										base_form.findField('EvnStick_Num').setRawValue('');
										base_form.findField('EvnStick_prid').setValue(0);
										base_form.findField('PridStickLeaveType_Code2').setValue(0);
										base_form.findField('EvnStick_Ser').setRawValue('');
										base_form.findField('EvnStickLast_Title').setRawValue('');
										//base_form.findField('FirstStickLeaveType_Code').setRawValue('');
										if (this.advanceParams && this.advanceParams.stacBegDate) {
											base_form.findField('EvnStick_stacBegDate').setValue(this.advanceParams.stacBegDate);
										}
										break;

									case Ext.EventObject.F4:
										e.stopEvent();
										this.openEvnStickListWindow();
										break;
								}
							}.createDelegate(this)
						},
						name: 'EvnStickLast_Title',
						onTriggerClick: function() {
							this.openEvnStickListWindow();
						}.createDelegate(this),
						readOnly: true,
						tabIndex: TABINDEX_ESTEF + 5,
						triggerClass: 'x-form-search-trigger',
						width: 200,
						xtype: 'trigger'
					}]
				}]
			},

			// Серия
			evnStickSerField,

			{
				autoHeight: true,
				id: this.id + 'EStEF_ESSConsent',
				userCls: 'add-cure-list-fieldset',
				//labelWidth: 200,
				style: 'padding: 2px 0px 0px 0px;',
				title: 'Согласие на получение ЭЛН',
				hidden: (getRegionNick()=='kz'),
				xtype: 'fieldset',
				items: [
					{
						layout: 'column',
						bodyStyle: 'margin: 10px 0',
						width: 980,
						border: false,
						items: [
							{
								layout: 'form',
								border: false,
								width: 320,
								items: [{
									allowBlank: true,
									enableKeyEvents: true,
									disabled: true,
									labelWidth: 200,
									fieldLabel: 'Дата согласия',
									format: 'd.m.Y',
									name: 'EvnStickBase_consentDT',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									width: 100,
									xtype: 'swdatefield'
								}]
							},
							{
								layout: 'form',
								border: false,
								width: 30,
								items: [{
									xtype: 'button',
									id: this.id + 'EStEF_ESSConsentAdd',
									tooltip: 'Добавить',
									iconCls: 'add16',
									handler: function() {
										this.openESSConsent('add');
									}.createDelegate(this)
								}]
							},
							{
								layout: 'form',
								border: false,
								width: 30,
								items: [{
									xtype: 'button',
									id: this.id + 'EStEF_ESSConsentEdit',
									tooltip: 'Редактировать',
									iconCls: 'edit16',
									handler: function() {
										this.openESSConsent('edit');
									}.createDelegate(this)
								}]
							},
							{
								layout: 'form',
								border: false,
								width: 30,
								items: [{
									xtype: 'button',
									id: this.id + 'EStEF_ESSConsentDelete',
									tooltip: 'Удалить',
									iconCls: 'delete16',
									handler: function() {
										var base_form = this.FormPanel.getForm();
										base_form.findField('EvnStickBase_consentDT').setRawValue('');

										this.checkGetEvnStickNumButton();
									}.createDelegate(this)
								}]
							},
							{
								layout: 'form',
								border: false,
								width: 30,
								items: [{
									xtype: 'button',
									id: this.id + 'EStEF_ESSConsentPrint',
									tooltip: 'Печать',
									iconCls: 'print16',
									handler: function() {
										this.doPrintESSConsent();
									}.createDelegate(this)
								}]
							}
						]
					}
				]
			},
			{
				border: false,
				layout: 'column',
				hidden: getRegionNick() == 'kz',
				style: 'margin-left: 205px; margin-bottom: 5px;',
				items: [{
					text: 'Получить номер ЭЛН',
					id: this.id + 'GetEvnStickNumButton',
					handler: function() {
						var base_form = this.FormPanel.getForm();

						if (!Ext.isEmpty(this.Person_Snils)) {
							this.getLoadMask('Получение номера ЭЛН...').show();
							Ext.Ajax.request({
								url: '/?c=RegistryESStorage&m=getEvnStickNum',
								callback: function(opt, success, response) {
									this.getLoadMask().hide();

									var responseObj = Ext.util.JSON.decode(response.responseText);
									base_form.findField('EvnStick_Num').setValue('');
									base_form.findField('RegistryESStorage_id').setValue(null);
									if (responseObj.EvnStick_Num) {
										base_form.findField('EvnStick_Num').setValue(responseObj.EvnStick_Num);
										base_form.findField('RegistryESStorage_id').setValue(responseObj.RegistryESStorage_id);
									}
									base_form.findField('EvnStick_Num').fireEvent('change', base_form.findField('EvnStick_Num'), base_form.findField('EvnStick_Num').getValue());

									if(responseObj.EvnStick_Num && getRegionNick() != 'kz' && !Ext.isEmpty(this.menuPrintActions)){
										this.menuPrintActions.items.items[1].enable();
									}

									this.refreshFormPartsAccess();
								}.createDelegate(this)
							});
						} else {

							var Person_Fio = base_form.findField('EvnStickFullNameText').getValue();
							sw.swMsg.show({
								icon: Ext.MessageBox.ERROR,
								title: 'Ошибка',
								msg: 'Для ' + Person_Fio + ' не указан СНИЛС. Электронные больничные без указания СНИЛС не принимаются ФСС и не подлежат оплате. Введите СНИЛС в реквизитах пациента или оформите ЛВН на бланке',
								buttons: Ext.Msg.OK,
								fn: function() {
									base_form.findField('EvnStick_Num').focus();
								}

							});
						}
					}.createDelegate(this),
					tooltip: 'Для создания электронного больничного получите номер ЭЛН из хранилища.',
					xtype: 'button'
				}]
			},
			{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					labelWidth: 200,
					items: [
						evnStickNumField
					]
				}, {
					border: false,
					layout: 'form',
					hidden: getRegionNick() == 'kz',
					labelWidth: 200,
					items: [{
						text: 'X',
						id: this.id + 'ClearEvnStickNumButton',
						handler: function() {
							this.clearEvnStickNum();
						}.createDelegate(this),
						xtype: 'button'
					}]
				}, 
					{
					border: false,
					layout: 'form',
					labelWidth: 100,
					items: [{
						allowBlank: false,
						fieldLabel: langs('Дата выдачи'),
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								this.filterStickCause();
								if ( blockedDateAfterPersonDeath('personpanelid', 'EStEF_PersonInformationFrame', field, newValue, oldValue) ) {
									return false;
								}
							}.createDelegate(this)
						},
						name: 'EvnStick_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						selectOnFocus: true,
						tabIndex: TABINDEX_ESTEF + 8,
						width: 100,
						xtype: 'swdatefield'
					}]
				}]
			},
			{
				border: false,
				layout: 'column',
				hidden: getRegionNick() == 'kz',
				style: 'margin: 5px 0 10px 150px; font-size: 12px;',
				items: [{
					layout: 'form',
					width: 55,
					border: false,
					items: [{
						xtype: 'label',
						text: 'Тип ЛВН:'
					}]
				}, {
					border: false,
					html: '<img src="/img/icons/UploaderIcons/loading.gif" />',
					id: 'EvnStickES_Loader'
				}, {
					xtype: 'label',
					id: 'EvnStickES_Type',
					text: 'На бланке',
					hidden: true
				}]
			},
			{
				autoHeight: true,
				id: this.id + 'EStEF_OrgFieldset',
				labelWidth: 200,
				style: 'padding: 2px 0px 0px 0px;',
				title: langs('Место работы'),
				xtype: 'fieldset',
				items: [

					// Организация
					{
						allowBlank: true,
						displayField: 'Org_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: langs('Организация'),
						hiddenName: 'Org_id',
						listeners: {
							'keydown': function( inp, e ) {
								if ( inp.disabled )
									return;

								if ( e.F4 == e.getKey() ) {
									this._stopPagination(e);

									inp.onTrigger1Click();

									return false;
								}
							}.createDelegate(this),
							'keyup': function(inp, e) {
								if ( e.F4 == e.getKey() ) {
									this._stopPagination(e);

									return false;
								}
							}.createDelegate(this)
						},
						mode: 'local',
						onTrigger1Click: function() {
							var base_form = this.FormPanel.getForm();
							var combo = base_form.findField('Org_id');

							if ( combo.disabled ) {
								return false;
							}

							getWnd('swOrgSearchWindow').show({
								object: 'org',
								onClose: function() {
									combo.focus(true, 200)
								},
								onSelect: function(org_data) {
									if ( org_data.Org_id > 0 ) {
										combo.getStore().loadData([{
											Org_id: org_data.Org_id,
											Org_Name: org_data.Org_Name,
											Org_StickNick: org_data.Org_StickNick
										}]);
										combo.setValue(org_data.Org_id);

										if ( org_data.Org_StickNick ) {
											base_form.findField('EvnStick_OrgNick').setValue(org_data.Org_StickNick);
										}
										else {
											base_form.findField('EvnStick_OrgNick').setRawValue(combo.getRawValue());
										}

										getWnd('swOrgSearchWindow').hide();
										combo.collapse();
									}
								}
							});
						}.createDelegate(this),
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'Org_id', type: 'int' },
								{ name: 'Org_Name', type: 'string' },
								{ name: 'Org_StickNick', type: 'string' }
							],
							key: 'Org_id',
							sortInfo: {
								field: 'Org_Name'
							},
							url: C_ORG_LIST
						}),
						tabIndex: TABINDEX_ESTEF + 9,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Org_Name}',
							'</div></tpl>'
						),
						trigger1Class: 'x-form-search-trigger',
						triggerAction: 'none',
						valueField: 'Org_id',
						width: 500,
						xtype: 'swbaseremotecombo'
					},

					// Наименование для печати
					{
						allowBlank: true,
						autoCreate: {
							tag: 'input',
							type: 'text',
							maxLength: '255'
						},
						fieldLabel: langs('Наименование для указания в ЛВН'),
						//autoCreate: {tag: "input", maxLength: "29", autocomplete: "off"},
						name: 'EvnStick_OrgNick',
						onTriggerClick: function() {
							var base_form = this.FormPanel.getForm();

							if ( base_form.findField('EvnStick_OrgNick').disabled ) {
								return false;
							}
							
							base_form.findField('EvnStick_OrgNick').setValue(base_form.findField('Org_id').getRawValue().substr(0,255));	
						}.createDelegate(this),
						tabIndex: TABINDEX_ESTEF + 10,
						triggerClass: 'x-form-equil-trigger',
						width: 500,
						xtype: 'trigger'
					},

					// Должность
					{
						allowBlank: true,
						fieldLabel: langs('Должность'),
						name: 'Post_Name',
						tabIndex: TABINDEX_ESTEF + 11,
						width: 500,
						xtype: 'textfield'
					}
				]
			},
			{
				allowBlank: false,
				fieldLabel: langs('Причина нетрудоспособности'),
				hiddenName: 'StickCause_id',
				listeners: {
					'change': function(combo, newValue, oldValue, ignoreCareFlag) {
						this.setMaxDateForSetDate();
						win.checkGetEvnStickNumButton();
						var base_form = this.FormPanel.getForm();
						var oldRecord = combo.getStore().getById(oldValue);
						var record = combo.getStore().getById(newValue);
						var person_age = swGetPersonAge(this.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnStick_setDate').getValue());

						var storeEvnStickCarePerson = this._getStoreEvnStickCarePerson();

						var stick_cause_sys_nick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
						var isRegPregnancy = base_form.findField('EvnStick_IsRegPregnancy');

						//поле должно быть обязательным для ввода, если причина нетрудоспособности - отпуск по беременности и родам
						if(
							stick_cause_sys_nick == 'pregn' 
							&& base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') != '020'
							&& base_form.findField('StickWorkType_id').getValue() != 2
						) {
							isRegPregnancy.setAllowBlank(false);
						} else {
							isRegPregnancy.setAllowBlank(true);
						}

						if(
							stick_cause_sys_nick == 'pregn' 
							&& this.action == 'add' && base_form.findField('EvnStick_IsOriginal').getValue() != 2
						) {
							base_form.findField('EvnStick_stacBegDate').setValue('');
							base_form.findField('EvnStick_stacEndDate').setValue('');
						}

						// Вопрос о смене причины с "Уход" на что-то другое
						if ( 
							oldRecord 
							&& (
								oldRecord.get('StickCause_SysNick').inlist([ 'uhod', 'uhodreb', 'uhodnoreb', 'rebinv', 'vich', 'zabrebmin' ])
								|| (oldRecord.get('StickCause_SysNick') == 'karantin' && person_age < 18)
							)
							&& (
								!record 
								|| !(
									record.get('StickCause_SysNick').inlist([ 'uhod', 'uhodreb', 'uhodnoreb', 'rebinv', 'vich', 'zabrebmin' ])
									|| record.get('StickCause_SysNick') == 'karantin' && person_age < 18
								)
								
							)
							&& !ignoreCareFlag && storeEvnStickCarePerson.getCount() > 0
							&& storeEvnStickCarePerson.getAt(0).get('EvnStickCarePerson_id') > 0
						) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( 'yes' == buttonId ) {
										combo.fireEvent('change', combo, newValue, oldValue, true);
									}
									else {
										combo.setValue(oldValue);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: langs('Данные из списка пациентов, нуждающихся в уходе, будут удалены. Изменить причину нетрудоспособности?'),
								title: langs('Вопрос')
							});
							return false;
						}

						// разблокируем поле Выдан ФИО при оформлении дубликата если есть список пациентов нужающихся в уходе
						if(
							win.action == 'add' 
							&& base_form.findField('EvnStick_IsOriginal').getValue() == 2
							&& !Ext.isEmpty(win.dataFromOriginal)
						) {
							if (
								record.get('StickCause_SysNick').inlist(['uhod', 'uhodnoreb', 'uhodreb', 'rebinv', 'postvaccinal', 'vich'])
								|| record.get('StickCause_SysNick') == 'karantin' && person_age < 18
							) {
								base_form.findField('EvnStickFullNameText').enable();
							} else {
								//меняем поля выдан ФИО и СНИЛС на значения из оригинала
								base_form.findField('EvnStickFullNameText').setValue(win.dataFromOriginal.get('Person_Fio'));								
								base_form.findField('Person_id').setValue(win.dataFromOriginal.get('Person_id'));
								base_form.findField('PersonEvn_id').setValue(win.dataFromOriginal.get('PersonEvn_id'));
								base_form.findField('Server_id').setValue(win.dataFromOriginal.get('Server_id'));

								win.Person_Snils = win.dataFromOriginal.get('Person_Snils');
								win._checkSnils();

								base_form.findField('EvnStickFullNameText').disable();
							}
						}

						base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
						base_form.findField('EvnStick_BirthDate').setContainerVisible(false);
						base_form.findField('EvnStick_BirthDate').setRawValue('');

						base_form.findField('EvnStick_adoptDate').setAllowBlank(true);
						base_form.findField('EvnStick_adoptDate').setContainerVisible(false);
						base_form.findField('EvnStick_adoptDate').setRawValue('');
						base_form.findField('EvnStick_sstBegDate').setAllowBlank(true);
						base_form.findField('EvnStick_sstBegDate').setContainerVisible(false);
						base_form.findField('EvnStick_sstBegDate').setRawValue('');
						base_form.findField('EvnStick_sstEndDate').setAllowBlank(true);
						base_form.findField('EvnStick_sstEndDate').setContainerVisible(false);
						base_form.findField('EvnStick_sstEndDate').setRawValue('');
						base_form.findField('EvnStick_sstNum').setAllowBlank(true);
						base_form.findField('EvnStick_sstNum').setContainerVisible(false);
						base_form.findField('EvnStick_sstNum').setRawValue('');
						base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(false);
						base_form.findField('EvnStick_IsRegPregnancy').clearValue();
						base_form.findField('Org_did').setAllowBlank(true);
						base_form.findField('Org_did').setContainerVisible(false);
						base_form.findField('Org_did').setRawValue('');

						if ( record ) {
							switch ( record.get('StickCause_SysNick') ) {
								case 'dolsan':
								case 'kurort':
									this.findById(this.id+'EStEF_EvnStickCarePersonPanel').hide();
									base_form.findField('EvnStick_sstBegDate').setAllowBlank(false);
									base_form.findField('EvnStick_sstBegDate').setContainerVisible(true);
									base_form.findField('EvnStick_sstEndDate').setContainerVisible(true);

									if (getRegionNick()=='astra') {
										if (record.get('StickCause_Code') == '08') {
											base_form.findField('EvnStick_sstNum').setAllowBlank(false);
											base_form.findField('EvnStick_sstNum').setContainerVisible(true);
										}
										base_form.findField('EvnStick_sstEndDate').setAllowBlank(false);
									} else {
										if (!Ext.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
											base_form.findField('EvnStick_sstNum').setAllowBlank(false);
										}
										base_form.findField('EvnStick_sstNum').setContainerVisible(true);
									}
									if (
										getRegionNick() == 'ufa'
										&& this.getPridStickLeaveTypeCode() == '37'
										&& ! Ext.isEmpty(base_form.findField('PridEvnStickWorkRelease_endDate').getValue())
									) {

										base_form.findField('EvnStick_sstBegDate').setValue(base_form.findField('PridEvnStickWorkRelease_endDate').getValue());


										base_form.findField('EvnStick_sstBegDate').disable();
									}
									base_form.findField('Org_did').setAllowBlank(getRegionNick()=='kz');
									base_form.findField('Org_did').setContainerVisible(true);
									break;

								// Причина нетрудоспособности «05. Отпуск по беременности и родам»
								case 'pregn':
									this.findById(this.id+'EStEF_EvnStickCarePersonPanel').hide();

									// refs #120282
									if ( getRegionNick() != 'kz' ) {
										base_form.findField('EvnStick_BirthDate').setAllowBlank(false);
									} else {
										base_form.findField('EvnStick_BirthDate').setAllowBlank(true); // согласно задаче #6269 (c) Night, 2011-09-18
									}

									base_form.findField('EvnStick_BirthDate').setContainerVisible(true);

									// ТРЭБО СДЕЛАТЬ: Учесть пол пациента!
									base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(true);
									if (base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') == '020') {
										base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
									}
									if (this.action == 'add' && base_form.findField('StickWorkType_id').getValue() == 2) {
										base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(false);
									}
									break;

								case 'adopt':
									base_form.findField('EvnStick_adoptDate').setAllowBlank(true);
									base_form.findField('EvnStick_adoptDate').setContainerVisible(true);
									break;

								case 'uhod':
								case 'uhodnoreb':
								case 'uhodreb':
								case 'rebinv':
								case 'vich':
								case 'postvaccinal':

								// Заболевание ребенка из перечня Минздрава
								case 'zabrebmin':
									this.findById(this.id+'EStEF_EvnStickCarePersonPanel').show();
									break;

								case 'karantin':
									if (person_age < 18) {
										this.findById(this.id+'EStEF_EvnStickCarePersonPanel').show();
									} else {
										this.findById(this.id+'EStEF_EvnStickCarePersonPanel').hide();
									}
									break;

								default:
									this.findById(this.id+'EStEF_EvnStickCarePersonPanel').hide();
									break;
							}
						}

						this.checkRebUhod();

						var stick_cause_d = base_form.findField('StickCause_did');
						if (!Ext.isEmpty(stick_cause_d.getValue())) {
							stick_cause_d.fireEvent('change', stick_cause_d, stick_cause_d.getValue());
						}
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_ESTEF + 12,
				width: 500,
				setValueByCode: function(code) {
					var combo = this,
						rec = false;
					if(code && combo.codeField)
						rec = combo.getStore().findRecord(combo.codeField,code);
					if(rec)
						combo.setValue(rec.get(combo.valueField));
					else
						combo.reset()
				},
				xtype: 'swstickcausecombo'
			},
			{
				allowBlank: true,
				comboSubject: 'StickCauseDopType',
				fieldLabel: langs('Доп. код нетрудоспособности'),
				hiddenName: 'StickCauseDopType_id',
				tabIndex: TABINDEX_ESTEF + 13,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'<table style="border: 0; padding: 0;"><tr>',
					'<td style="width: 30px; color: red;">{StickCauseDopType_Code}&nbsp;</td>',
					'<td style="white-space: pre-line;">{StickCauseDopType_Name}</td>',
					'</tr></table>',
					'</div></tpl>'
				),
				width: 500,
				xtype: 'swcommonsprcombo',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						this.changeStickCauseDopType(oldValue);
					}.createDelegate(this)
				}
			},
			{
				allowBlank: true,
				allowSysNick: true,
				comboSubject: 'StickCause',
				enableKeyEvents: true,
				fieldLabel: langs('Код изм. нетрудоспособности'),
				hiddenName: 'StickCause_did',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						if (getRegionNick() != 'kz') {
							if (!Ext.isEmpty(newValue)) {
								base_form.findField('EvnStick_StickDT').setContainerVisible(true);
								base_form.findField('EvnStick_StickDT').setAllowBlank(false);
							} else {
								base_form.findField('EvnStick_StickDT').setContainerVisible(false);
								base_form.findField('EvnStick_StickDT').setValue(null);
								base_form.findField('EvnStick_StickDT').setAllowBlank(true);

								if (!Ext.isEmpty(oldValue)) {
									var stick_cause = base_form.findField('StickCause_id');
									stick_cause.fireEvent('change', stick_cause, stick_cause.getValue());
								}
							}

							switch(combo.getFieldValue('StickCause_SysNick')) {
								case 'pregn':
									base_form.findField('EvnStick_BirthDate').setAllowBlank(getRegionNick()!='astra');
									base_form.findField('EvnStick_BirthDate').setContainerVisible(true);
									base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(true);
									break;
								case 'dolsan':
									base_form.findField('EvnStick_sstBegDate').setAllowBlank(false);
									base_form.findField('EvnStick_sstBegDate').setContainerVisible(true);
									base_form.findField('EvnStick_sstEndDate').setContainerVisible(true);
									base_form.findField('EvnStick_sstEndDate').setAllowBlank(getRegionNick()!='astra');
									base_form.findField('EvnStick_sstNum').setContainerVisible(true);
									if (getRegionNick()=='astra' || !Ext.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
										base_form.findField('EvnStick_sstNum').setAllowBlank(false);
									}
									base_form.findField('EvnStick_sstNum').setAllowBlank(false);
									base_form.findField('Org_did').setAllowBlank(false);
									base_form.findField('Org_did').setContainerVisible(true);
									break;
							}
						}
					}.createDelegate(this),
					'keydown': function(inp, e) {
						var base_form = this.FormPanel.getForm();

						if (
							e.getKey() == Ext.EventObject.TAB &&
							e.shiftKey == false &&
							base_form.findField('EvnStick_BirthDate').hidden &&
							base_form.findField('EvnStick_sstBegDate').hidden
						) {
							e.stopEvent();
							this._keydownFocus();
						}
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_ESTEF + 14,
				width: 500,
				xtype: 'swcommonsprcombo'
			},
			{
				allowBlank: true,
				enableKeyEvents: true,
				hidden: (getRegionNick()=='kz'),
				fieldLabel: langs('Дата изменения причины нетрудоспособности'),
				format: 'd.m.Y',
				name: 'EvnStick_StickDT',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_ESTEF + 15,
				width: 100,
				xtype: 'swdatefield'
			},
			{
				allowBlank: true,
				enableKeyEvents: true,
				fieldLabel: langs('Дата усыновления/удочерения'),
				format: 'd.m.Y',
				name: 'EvnStick_adoptDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_ESTEF + 15,
				width: 100,
				xtype: 'swdatefield'
			},
			{
				allowBlank: true,
				enableKeyEvents: true,
				fieldLabel: langs('Предполагаемая дата родов'),
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
					}.createDelegate(this),
					'keydown': function(inp, e) {
						if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
							e.stopEvent();
							this._keydownFocus();
						}
					}.createDelegate(this)
				},
				name: 'EvnStick_BirthDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_ESTEF + 15,
				width: 100,
				xtype: 'swdatefield'
			},
			{
				border: false,
				layout: 'column',
				labelWidth: 200,
				items: [
					{
						border: false,
						layout: 'form',
						labelWidth: 200,
						items: [{
							allowBlank: true,
							fieldLabel: langs('Дата начала СКЛ'),
							format: 'd.m.Y',
							listeners: {
								'change': function(field, newValue, oldValue) {
									var base_form = this.FormPanel.getForm();
								}.createDelegate(this)
							},
							name: 'EvnStick_sstBegDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_ESTEF + 16,
							width: 100,
							xtype: 'swdatefield'
						}]
					},
					{
						border: false,
						layout: 'form',
						labelWidth: 200,
						items: [{
							allowBlank: true,
							fieldLabel: langs('Дата окончания СКЛ'),
							format: 'd.m.Y',
							listeners: {
								'change': function(field, newValue, oldValue) {
									var base_form = this.FormPanel.getForm();
								}.createDelegate(this)
							},
							name: 'EvnStick_sstEndDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_ESTEF + 17,
							width: 100,
							xtype: 'swdatefield'
						}]
					}
				]
			},
			{
				allowBlank: true,
				fieldLabel: langs('Номер путевки'),
				name: 'EvnStick_sstNum',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						base_form.findField('EvnStick_sstEndDate').setAllowBlank(true);

						// refs #120282 Дата окончания СКЛ (Все, кроме Казахстана) Должно быть обязательным для заполнения, если указан номер путевки.
						// EvnStick_sstEndDate - Дата окончания СКЛ
						if (getRegionNick() != 'kz' && !Ext.isEmpty(newValue)) {
							base_form.findField('EvnStick_sstEndDate').setAllowBlank(false);
						}

					}.createDelegate(this)
				},
				tabIndex: TABINDEX_ESTEF + 18,
				width: 100,
				xtype: 'textfield'
			},
			{
				displayField: 'Org_Name',
				editable: false,
				enableKeyEvents: true,
				fieldLabel: langs('Санаторий'),
				hiddenName: 'Org_did',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						if ( getRegionNick() != 'kz' && ! Ext.isEmpty(newValue) ) {


							// refs #120282 - Номер путевки (Все, кроме Казахстана) Обязательно если редактируется Пользователем, место работы которого связано с организацией, указанной в поле «Санаторий».
							var base_form = this.FormPanel.getForm();
							if( ! base_form.findField('EvnStick_sstNum').hidden && ! base_form.findField('EvnStick_sstNum').disabled){
								base_form.findField('EvnStick_sstNum').setAllowBlank(true);
								if(getGlobalOptions().org_id == newValue){
									base_form.findField('EvnStick_sstNum').setAllowBlank(false);
								}
							}

							Ext.Ajax.request({
								url: '/?c=Org&m=getOrgOGRN',
								params: {Org_id: newValue},
								success: function(response, options){
									var responseObj = Ext.util.JSON.decode(response.responseText);
									if (Ext.isEmpty(responseObj.Org_OGRN)) {
										sw.swMsg.alert(langs('Внимание'),langs('ОГРН для данной организации не указан. Обратитесь к администратору для заполнения.'));
										return false;
									}
								}
							});
						}
					}.createDelegate(this),
					'keydown': function( inp, e ) {
						if ( inp.disabled ){
							return;
						}

						if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
							e.stopEvent();
							this._keydownFocus();
						} else if ( e.F4 == e.getKey() ) {
							this._stopPagination(e)
							inp.onTrigger1Click();
							return false;
						}
					}.createDelegate(this),
					'keyup': function(inp, e) {
						if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == false ) {
							e.stopEvent();
							this._keydownFocus();
						}
						else if ( e.F4 == e.getKey() ) {
							this._stopPagination(e)
							return false;
						}
					}.createDelegate(this)
				},
				mode: 'local',
				onTrigger1Click: function() {
					var base_form = this.FormPanel.getForm();
					var combo = base_form.findField('Org_did');

					if ( combo.disabled ) {
						return false;
					}

					getWnd('swOrgSearchWindow').show({
						object: 'org',
						onClose: function() {
							combo.focus(true, 200)
						},
						onSelect: function(org_data) {
							if ( org_data.Org_id > 0 ) {
								combo.getStore().loadData([{
									Org_id: org_data.Org_id,
									Org_Name: org_data.Org_Name
								}]);
								combo.setValue(org_data.Org_id);
								getWnd('swOrgSearchWindow').hide();
								combo.collapse();
								combo.fireEvent('change', combo, combo.getValue());
							}
						}
					});
				}.createDelegate(this),
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'Org_id', type: 'int' },
						{ name: 'Org_Name', type: 'string' }
					],
					key: 'Org_id',
					sortInfo: {
						field: 'Org_Name'
					},
					url: C_ORG_LIST
				}),
				tabIndex: TABINDEX_ESTEF + 19,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{Org_Name}',
					'</div></tpl>'
				),
				trigger1Class: 'x-form-search-trigger',
				triggerAction: 'none',
				valueField: 'Org_id',
				width: 500,
				xtype: 'swbaseremotecombo'
			},

			// 1. Список пациентов, нуждающихся в уходе
			this.panelEvnStickCarePerson,

			// 2. Режим
			this.panelStickRegime,

			// 3. МСЭ
			this.panelMSE,

			// 4. Освобождение от работы
			this.panelEvnStickWorkRelease,

			// 5. Исход ЛВН
			this.panelStickLeave
		]
	});

	this.buttonSave = {
		id: this.id + 'buttonSave',
		handler: function() {
			this.doSave();
		}.createDelegate(this),
		iconCls: 'save16',
		onShiftTabAction: function () {
			this._keydownFocus6();

		}.createDelegate(this),
		onTabAction: function () {
			this._focusButtonPrint();
		}.createDelegate(this),
		tabIndex: TABINDEX_ESTEF + 34,
		text: BTN_FRMSAVE
	};
	this.buttonPrint = {
		id: this.id + 'buttonPrint',
		handler: function() {
			this.doBeforePrintEvnStick();
		}.createDelegate(this),
		iconCls: 'print16',
		onShiftTabAction: function () {
			this._keydownFocus7();
		}.createDelegate(this),
		onTabAction: function () {
			this._focusButtonCancel();
		}.createDelegate(this),
		tabIndex: TABINDEX_ESTEF + 35,
		text: BTN_FRMPRINT
	};
	
	this.menuPrintActions = new Ext.menu.Menu({
		items: [
			this.buttonPrint,
			{
				text: 'Печать усеченного талона ЭЛН',
				iconCls : 'print16',
				tooltip: 'Печать усеченного талона ЭЛН',
				id: 'printTruncatedCouponELN',
				hidden: getRegionNick() == 'kz',
				handler: function(){
					var base_form = this.FormPanel.getForm();
					var EvnStick_id = base_form.findField('EvnStick_id').getValue();
					var Report_Params = '&paramEvnStick=' + EvnStick_id;
					printBirt({
						'Report_FileName': 'ELN_EvnStickPrint_short.rptdesign',
						'Report_Params': Report_Params,
						'Report_Format': 'pdf'
					});
				}.createDelegate(this)
			}
		]
	});
	
	if(getRegionNick() != 'kz'){
		this.buttonPrint.text = 'Печать ЛВН';
		this.pintButton = {
			id: 'sdfsdfsdfReleaseGrid',
			//iconCls: 'actions16', // actions16 дизаблит при открытии формы на прсмотр
			iconCls : 'print16',
			text: 'Печать',
			tooltip: 'Действия',
			menu: this.menuPrintActions,
			hidden: getRegionNick() == 'kz'
		}
	}else{
		this.pintButton = this.buttonPrint;
	}
	this.buttonHelp = HelpButton(this, -1);
	this.buttonCancel = {
		id: this.id + 'buttonCancel',
		handler: function() {
			this.doHideForm();
		}.createDelegate(this),
		iconCls: 'cancel16',
		onShiftTabAction: function () {
			this._focusButtonPrint();
		}.createDelegate(this),
		onTabAction: function () {
			this._keydownFocus5();
		}.createDelegate(this),
		tabIndex: TABINDEX_ESTEF + 36,
		text: BTN_FRMCANCEL
	};


	// Указывает откуда загружен исход
	// 'orig' - из оригинала
	this.fromStickLeave = null;

	sw.Promed.swEvnStickEditWindow.superclass.constructor.call(this, {
		codeRefresh: true,
		objectName: 'swEvnStickEditWindow',
		objectSrc: '/jscore/Forms/Stick/swEvnStickEditWindow.js',
		action: null,
		buttonAlign: 'left',
		callback: function(){},
		closable: true,
		closeAction: 'hide',
		collapsible: false,
		JobOrg_id: null,
		link: false,
		parentNum: null,
		Person_Post: null,
		userMedStaffFactId: null,
		CurLpuSection_id: 0,
		CurLpuUnit_id: 0,
		CurLpuBuilding_id: 0,
		IngoreMSFFilter: 0,
		StickReg: 0,
		Signatures_id: null,
		Signatures_iid: null,
		Lpu_id: null,
		isTubDiag: false,
		hasWorkReleaseIsInReg: false,
		draggable: true,


		// Тип ЛВН
		// 1 - основная работа
		// 2 - работа по совместительству
		evnStickType: 1,


		formFields: [
			'EvnStick_BirthDate',
			'EvnStick_disDate',
			'EvnStick_irrDate',
			'EvnStick_IsDisability',
			'InvalidGroupType_id',
			'EvnStick_StickDT',
			'EvnStick_IsRegPregnancy',
			'EvnStick_mseDate',
			'EvnStick_mseExamDate',
			'EvnStick_mseRegDate',
			'EvnStick_Num',
			'EvnStick_Ser',
			'EvnStick_setDate',
			'EvnStick_sstBegDate',
			'EvnStick_sstEndDate',
			'EvnStick_sstNum',
			'EvnStick_stacBegDate',
			'EvnStick_stacEndDate',
			'EvnStickDop_pid',
			'EvnStickFullNameText',
			'UAddress_AddressText',
			'EvnStickLast_Title',
			'Lpu_oid',
			'MedStaffFact_id',
			'Org_did',
			'Org_id',
			'EvnStick_OrgNick',
			'Post_Name',
			'StickCause_did',
			'StickCause_id',
			'StickCauseDopType_id',
			'StickIrregularity_id',
			'StickLeaveType_id',
			'StickOrder_id',
			'StickWorkType_id',
			'EvnStick_IsOriginal',
			'EvnStick_oid'
		],
		formMainFields: [
			'EvnStick_BirthDate',
			'EvnStick_StickDT',
			'EvnStick_Num',
			'EvnStick_Ser',
			'EvnStick_setDate',
			'EvnStick_sstBegDate',
			'EvnStick_sstEndDate',
			'EvnStick_sstNum',
			'EvnStickDop_pid',
			'EvnStickFullNameText',
			'UAddress_AddressText',
			'EvnStickLast_Title',
			'Org_did',
			'Org_id',
			'EvnStick_OrgNick',
			'Post_Name',
			'StickCause_did',
			'StickCause_id',
			'StickCauseDopType_id',
			'StickOrder_id',
			'StickWorkType_id',
			'EvnStick_IsOriginal',
			'EvnStick_oid'
		],
		formSomeMainFields: [
			'EvnStick_Num',
			'Org_id',
			'EvnStick_OrgNick'
		],
		formSomeMainFields_KodyNetrud: [
			'StickCause_did',
			'EvnStick_StickDT'
		],
		formSSTFields: [
			'EvnStick_sstBegDate',
			'EvnStick_sstEndDate',
			'EvnStick_sstNum',
			'Org_did'
		],
		formStatus: 'edit',
		height: 550,
		keys: [{
			alt: true,
			fn: function(inp, e) {


				switch ( e.getKey() ) {
					case Ext.EventObject.C:
						this.doSave();
						break;

					case Ext.EventObject.G:
						this.doBeforePrintEvnStick();
						break;

					case Ext.EventObject.J:
						this.doHide();
						break;

					case Ext.EventObject.NUM_ONE:
					case Ext.EventObject.ONE:
						this.findById(this.id+'EStEF_EvnStickCarePersonPanel').toggleCollapse();
						break;

					case Ext.EventObject.NUM_TWO:
					case Ext.EventObject.TWO:
						this.findById(this.id+'EStEF_StickRegimePanel').toggleCollapse();
						break;

					case Ext.EventObject.NUM_THREE:
					case Ext.EventObject.THREE:
						this.findById(this.id+'EStEF_MSEPanel').toggleCollapse();
						break;

					case Ext.EventObject.FOUR:
					case Ext.EventObject.NUM_FOUR:
						this._toggle_WorkReleasePanel()
						break;

					case Ext.EventObject.FIVE:
					case Ext.EventObject.NUM_FIVE:
						this.findById(this.id+'EStEF_StickLeavePanel').toggleCollapse();
						break;
				}
			}.createDelegate(this),
			key: [
				Ext.EventObject.C,
				Ext.EventObject.FIVE,
				Ext.EventObject.FOUR,
				Ext.EventObject.G,
				Ext.EventObject.J,
				Ext.EventObject.NUM_FOUR,
				Ext.EventObject.NUM_FIVE,
				Ext.EventObject.NUM_ONE,
				Ext.EventObject.NUM_TWO,
				Ext.EventObject.NUM_THREE,
				Ext.EventObject.ONE,
				Ext.EventObject.TWO,
				Ext.EventObject.THREE
			],
			scope: this,
			stopEvent: false
		}],
		layout: 'border',
		listeners: {
			'beforehide': function() {


				var base_form = this.FormPanel.getForm();
				var RegistryESStorage_id = base_form.findField('RegistryESStorage_id').getValue();
				var EvnStick_id = base_form.findField('EvnStick_id').getValue();

				if (
					getRegionNick() != 'kz' &&
					this.action == 'add' &&
					(
						Ext.isEmpty(EvnStick_id) ||
						EvnStick_id == "0"
					) &&
					! Ext.isEmpty(RegistryESStorage_id)
				) {
					// разбронировать номер.
					this._doUnbookEvnStickNum(RegistryESStorage_id)
				}

				return this;

			}.createDelegate(this),
			'hide': function() {
				this.onHide();
			}.createDelegate(this),
			'maximize': function() {
				this._panelsDoLayout();
			}.createDelegate(this),
			'restore': function() {
				this._panelsDoLayout();
			}.createDelegate(this)
		},
		loadMask: null,
		maximizable: true,
		maximized: false,
		minHeight: 550,
		minWidth: 750,
		modal: true,
		onHide: function(){},
		parentClass: null,
		plain: true,
		resizable: true,
		width: 850,
		// КВС id, если есть
		EvnPS_id: null,
		// Движения в связанной КВС (или ТАП? УТОЧНИТЬ!)
		EvnSectionList: null,
		// Даты движений в связанной КВС (или ТАП? УТОЧНИТЬ!)
		EvnSectionDates: null,
		// Игнорируем проверку на повторение организации (вынес в свойство т.к. спрашивать нужно только один раз)
		ignoreCheckEvnStickOrg: 0,
		formFirstShow: true,
		buttons: [
			// Сохранить
			this.buttonSave,
			// Печать
			this.pintButton,
			{
				text: '-'
			},
			// Помощь
			this.buttonHelp,
			// Отмена
			this.buttonCancel
		],
		items: [
			this.PersonInfo,
			this.FormPanel
		]

	});

}

Ext.extend(sw.Promed.swEvnStickEditWindow, sw.Promed.BaseForm, {

	// указываем что значение исхода было изменено при выборе оригинала
	_setFromStickLeave_Orig: function(){
		this.fromStickLeave = 'orig';

		return this;
	},

	// указываем что значение исхода было изменено при загрузке данных формы
	_setFromStickLeave_Save: function(){
		this.fromStickLeave = 'save';

		return this;
	},
	_checkSnils: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		
		if( !Ext.isEmpty(win.Person_Snils) ) {

			base_form.findField('Person_Snils').setValue(win.Person_Snils);
			Ext.getCmp(win.id + 'setSnilsButton').hide();
		} else if( 
			!Ext.isEmpty(base_form.findField('Person_id').getValue()) 
			&& base_form.findField('Person_id').getValue() != 0 
		) {
			base_form.findField('Person_Snils').setValue('Не указан СНИЛС');
			Ext.getCmp(win.id + 'setSnilsButton').show();	
		} else {
			base_form.findField('Person_Snils').setValue('')
			Ext.getCmp(win.id + 'setSnilsButton').hide();
		}
	},
	initComponent: function() {
		__l('initComponent');

		sw.Promed.swEvnStickEditWindow.superclass.initComponent.apply(this, arguments);
	},


	// =================================================================================================================
	// Информация о пациенте
	_loadPersonInfo: function(){
		__l('_loadPersonInfo');
		var me = this;
		var win = this;

		if(Ext.isEmpty(me.PersonInfo)){
			return false;
		}

		me.PersonInfo.load({
			Person_id: (me.params.Person_id ? me.params.Person_id : ''),
			Person_Birthday: (me.params.Person_Birthday ? me.params.Person_Birthday : ''),
			Person_Firname: (me.params.Person_Firname ? me.params.Person_Firname : ''),
			Person_Secname: (me.params.Person_Secname ? me.params.Person_Secname : ''),
			Person_Surname: (me.params.Person_Surname ? me.params.Person_Surname : ''),
			callback: function(response) {
				var base_form = me.FormPanel.getForm();

				win.advanceParams.Person_Snils = response[0].get('Person_Snils');

				if(win.action == 'add') {
					win.Person_Snils = response[0].get('Person_Snils');
					win._checkSnils();
				}
				clearDateAfterPersonDeath('personpanelid', 'EStEF_PersonInformationFrame', base_form.findField('EvnStick_setDate'));

				Ext.Ajax.request({
					url: '/?c=Person&m=getAddressByPersonId',
					params: {
						Person_id: base_form.findField('Person_id').getValue()
					},
					success: function(response){
						var resp = Ext.util.JSON.decode(response.responseText);
						base_form.findField('UAddress_Zip').setValue(resp[0].UAddress_Zip);
						base_form.findField('UKLCountry_id').setValue(resp[0].UKLCountry_id);
						base_form.findField('UKLRGN_id').setValue(resp[0].UKLRGN_id);
						base_form.findField('UKLSubRGN_id').setValue(resp[0].UKLSubRGN_id);
						base_form.findField('UKLCity_id').setValue(resp[0].UKLCity_id);
						base_form.findField('UPersonSprTerrDop_id').setValue(resp[0].UPersonSprTerrDop_id);
						base_form.findField('UKLTown_id').setValue(resp[0].UKLTown_id);
						base_form.findField('UKLStreet_id').setValue(resp[0].UKLStreet_id);
						base_form.findField('UAddress_House').setValue(resp[0].UAddress_House);
						base_form.findField('UAddress_Corpus').setValue(resp[0].UAddress_Corpus);
						base_form.findField('UAddress_Flat').setValue(resp[0].UAddress_Flat);
						base_form.findField('UAddress_AddressText').setValue(resp[0].UAddress_AddressText);
						base_form.findField('UAddress_Address').setValue(resp[0].UAddress_Address);
					}
				});
			}
		});
	},
	// =================================================================================================================






	// =================================================================================================================
	// ФОРМА (BASE FORM)
	// =================================================================================================================

	// Дата выдачи
	setMaxDateForSetDate: function() {
		__l('setMaxDateForSetDate');
		var me = this;
		var base_form = this.FormPanel.getForm();
		base_form.findField('EvnStick_setDate').setMaxValue(Date.parseDate((getGlobalOptions().date), 'd.m.Y').add(Date.DAY, 2));
		if (base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'pregn' && base_form.findField('StickOrder_id').getFieldValue('StickOrder_Code') == 2) {
			base_form.findField('EvnStick_setDate').setMaxValue(null);
		}
	},

	// получение информации о враче //
	getMedPersonalInfo: function(MedStaffFact_id, callback) {
		if(MedStaffFact_id) {
			Ext.Ajax.request({
				params: {
					MedStaffFact_id: MedStaffFact_id
				},
				success: function(response, options) {
					var result = Ext.util.JSON.decode(response.responseText);

					callback(result);
				},
				url: '/?c=MedPersonal&m=getMedPersonalInfo'
			});
		}

	},

	// Доп. код нетрудоспособности
	changeStickCauseDopType: function(oldValue){
		__l('changeStickCauseDopType');
		var me = this;
		var base_form = this.FormPanel.getForm(),
			combo = base_form.findField('StickCauseDopType_id');

		var stick_cause_sys_nick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');

		if (getRegionNick() != 'kz') {
			if (combo.getValue() && combo.getFieldValue('StickCauseDopType_Code').inlist(['017','018','019'])) {
				['EvnStick_sstBegDate', 'EvnStick_sstEndDate', 'Org_did'].forEach(function(el){
					base_form.findField(el).setAllowBlank(false);
					base_form.findField(el).setContainerVisible(true);
					base_form.findField(el).setValue(base_form.findField(el).getValue());
				});
				base_form.findField('EvnStick_sstEndDate').setAllowBlank(getRegionNick()!='astra');
				base_form.findField('EvnStick_sstNum').setContainerVisible(true);
				if (getRegionNick()=='astra' || !Ext.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
					base_form.findField('EvnStick_sstNum').setAllowBlank(false);
				}
				base_form.findField('EvnStick_sstNum').setValue(base_form.findField('EvnStick_sstNum').getValue());
			} else if (!Ext.isEmpty(oldValue)) {
				var stick_cause = base_form.findField('StickCause_id');
				stick_cause.fireEvent('change', stick_cause, stick_cause.getValue());
			}
		}


		var isRegPregnancy = base_form.findField('EvnStick_IsRegPregnancy');
		//поле должно быть обязательным для ввода, если причина нетрудоспособности - отпуск по беременности и родам
		if(
			stick_cause_sys_nick == 'pregn' 
			&& base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') != '020'
		) {
			isRegPregnancy.setAllowBlank(false);
		} else {
			isRegPregnancy.setAllowBlank(true);
		}

		if(
			stick_cause_sys_nick == 'pregn' 
			&& base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') == '020'
			&& this.action == 'add' && base_form.findField('EvnStick_IsOriginal').getValue() != 2
		) {
			base_form.findField('EvnStick_stacBegDate').setValue('');
			base_form.findField('EvnStick_stacEndDate').setValue('');
		}

		if (stick_cause_sys_nick == 'pregn' && combo.getFieldValue('StickCauseDopType_Code') != '020') {
			base_form.findField('EvnStick_BirthDate').setAllowBlank(false);
		} else {
			base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
		}
	},

	// Поле "Оригинал"
	listenerChange_EvnStick_IsOriginal: function(combo, newValue, oldValue){
		__l('listenerChange_EvnStick_IsOriginal');
		var me = this;


		var base_form = this.FormPanel.getForm();
		base_form.findField('EvnStick_oid').clearValue();
		base_form.findField('EvnStick_oid').fireEvent('change', base_form.findField('EvnStick_oid'));


		// Выбрано значение "Дубликат"
		if (newValue == 2) {
			// Загружаем список ЛВН - оригиналов и дизаблим все параметры.
			me._loadStore_EvnStick_oid();

			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].disable();
			this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].disable();
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[1].disable();
			this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[1].disable();
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[3].disable();
			this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[3].disable();

			this.findById(me.id+'EStEF_btnSetMinDateFromPS').setVisible(false);
			this.findById(me.id+'EStEF_btnSetMaxDateFromPS').setVisible(false);

			base_form.findField('EvnStick_oid').setAllowBlank(false);
			base_form.findField('EvnStick_oid').setContainerVisible(true);

		}
		else { // Выбрано значение "Оригинал"


			// Отменяем дизаблинг параметров
			if ( this.action != 'view' ) {
				this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].enable();
				this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].enable();
				this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[1].enable();
				this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[1].enable();
				this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[3].enable();
				this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[3].enable();
				this.enableEdit(true);

				this.findById(me.id+'EStEF_btnSetMinDateFromPS').setVisible(true);
				this.findById(me.id+'EStEF_btnSetMaxDateFromPS').setVisible(true);
			}
			
			this.findById(me.id+'EStEF_ESSConsentDelete').hide();
			base_form.findField('EvnStick_oid').getStore().removeAll();
			base_form.findField('EvnStick_oid').setAllowBlank(true);
			base_form.findField('EvnStick_oid').setContainerVisible(false);
		}
	},




	// -----------------------------------------------------------------------------------------------------------------
	// Поле "Оригинал ЛВН" (EvnStick_oid)

	_getSelectedRecord_EvnStick_oid: function(){
		var me = this;
		var base_form = this.FormPanel.getForm();

		var EvnStick_oid = base_form.findField('EvnStick_oid').getValue();

		var index = base_form.findField('EvnStick_oid').getStore().findBy(function(rec) {
			if ( rec.get('EvnStick_id') == EvnStick_oid ) {
				return true;
			}
			else {
				return false;
			}
		});

		
		return base_form.findField('EvnStick_oid').getStore().getAt(index);
	},



	// Обработка события "load" в Store!!!!
	listenerLoad_EvnStick_oid: function() {
		__l('listenerLoad_EvnStick_oid');

		var me = this;
		var field = me.FormPanel.getForm().findField('EvnStick_oid');

		// если в списке только 1 значение, то сразу его выбираем
		if (field.getStore().getCount() == 1 && me.action == 'add') {
			var newValue = field.getStore().getAt(0).get('EvnStick_id');
			field.setValue(newValue);

			// функция выбора оригинала
			me._applyValueToFields_EvnStick_oid(newValue);
		} else {
			field.setValue(field.getValue());
		}

		return true;
	},

	// Обработка события "change"
	listenerChange_EvnStick_oid: function(combo, newValue, oldValue) {
		__l('listenerChange_EvnStick_oid');

		var me = this;

		// функция выбора оригинала
		// вывел в отдельную функцию чтобы можно было вызывать данный код в другом месте не используя fireEvent
		me._applyValueToFields_EvnStick_oid(newValue);

		return true;
	},

	/**
	 * Функция выбора оригинала если выбран тип текущего ЛВН - "Дубликат"
	 * Если выбранное значение поля "EvnStick_oid", то изменяем знчения других полей которые зависят от поля EvnStick_oid
	 * 
	 * @param record
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_applyValueToFields_EvnStick_oid: function(newValue){

		__l('_applyValueToFields_EvnStick_oid');
		var me = this;
		var base_form = this.FormPanel.getForm();

		var index = base_form.findField('EvnStick_oid').getStore().findBy(function(rec) {
			if ( rec.get('EvnStick_id') == newValue ) {
				return true;
			}
			else {
				return false;
			}
		});
		var person_age = swGetPersonAge(this.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnStick_setDate').getValue());
		var record = base_form.findField('EvnStick_oid').getStore().getAt(index);

		this.dataFromOriginal = record;

		// если запись найдена и существует
		if(record){

			// Устанавливаем значения полей
			base_form.findField('EvnStick_disDate').setValue(Date.parseDate(record.get('EvnStick_disDate'), 'd.m.Y'));
			base_form.findField('EvnStick_IsDisability').setValue(record.get('EvnStick_IsDisability'));
			base_form.findField('InvalidGroupType_id').setValue(record.get('InvalidGroupType_id'));
			base_form.findField('EvnStick_StickDT').setValue(record.get('EvnStick_StickDT'));
			base_form.findField('EvnStick_mseDate').setValue(Date.parseDate(record.get('EvnStick_mseDate'), 'd.m.Y'));
			base_form.findField('EvnStick_mseExamDate').setValue(Date.parseDate(record.get('EvnStick_mseExamDate'), 'd.m.Y'));
			base_form.findField('EvnStick_mseRegDate').setValue(Date.parseDate(record.get('EvnStick_mseRegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_prid').setValue(record.get('EvnStick_prid'));
			base_form.findField('EvnStick_nid').setValue(record.get('EvnStick_nid'));
			base_form.findField('EvnStick_setDate').setValue(Date.parseDate(record.get('EvnStick_setDate'), 'd.m.Y'));
			base_form.findField('EvnStick_stacBegDate').setValue(Date.parseDate(record.get('EvnStick_stacBegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_stacEndDate').setValue(Date.parseDate(record.get('EvnStick_stacEndDate'), 'd.m.Y'));

			if (
				record.get('StickCause_SysNick').inlist(['uhod', 'uhodnoreb', 'uhodreb', 'rebinv', 'postvaccinal', 'vich'])
				|| record.get('StickCause_SysNick') == 'karantin' && person_age < 18
			) {
				base_form.findField('EvnStickFullNameText').enable();
			} else {
				base_form.findField('EvnStickFullNameText').setValue(record.get('Person_Fio'));
				base_form.findField('Person_Snils').setValue(record.get('Person_Snils'));
				base_form.findField('Person_id').setValue(record.get('Person_id'));
				base_form.findField('PersonEvn_id').setValue(record.get('PersonEvn_id'));
				base_form.findField('Server_id').setValue(record.get('Server_id'));

				me.Person_Snils = record.get('Person_Snils');
				me._checkSnils();
			}
			


			if(record.get('Person_Fio') == ''){
				base_form.findField('EvnStick_IsOriginal').disable();
			}

			base_form.findField('EvnStickLast_Title').setValue(record.get('EvnStickLast_Title'));
			base_form.findField('Lpu_oid').setValue(record.get('Lpu_oid'));
			base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
			base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
			
			base_form.findField('StickCause_did').setValue(record.get('StickCause_did'));

			if (getRegionNick() != 'penza') base_form.findField('StickCause_id').setValue(record.get('StickCause_id'));
			base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), base_form.findField('StickCause_id').getValue());

			base_form.findField('StickIrregularity_id').setValue(record.get('StickIrregularity_id'));
			base_form.findField('StickIrregularity_id').fireEvent('change', base_form.findField('StickIrregularity_id'), base_form.findField('StickIrregularity_id').getValue());




			base_form.findField('EvnStick_irrDate').setValue(Date.parseDate(record.get('EvnStick_irrDate'), 'd.m.Y'));
			base_form.findField('EvnStick_IsRegPregnancy').setValue(record.get('EvnStick_IsRegPregnancy'));
			base_form.findField('EvnStick_BirthDate').setValue(Date.parseDate(record.get('EvnStick_BirthDate'), 'd.m.Y'));
			base_form.findField('StickCauseDopType_id').setValue(record.get('StickCauseDopType_id'));

			// Исход ЛВН
			me._setFromStickLeave_Orig();
			base_form.findField('StickLeaveType_id').setValue(record.get('StickLeaveType_id'));


			base_form.findField('StickOrder_id').setValue(record.get('StickOrder_id'));
			base_form.findField('Post_Name').setValue(record.get('Post_Name'));
			base_form.findField('EvnStick_OrgNick').setValue(record.get('EvnStick_OrgNick'));
			base_form.findField('EvnStickBase_consentDT').setValue(record.get('EvnStickBase_consentDT'));
			base_form.findField('Org_id').setValue(record.get('Org_id'));
			base_form.findField('EvnStick_sstBegDate').setValue(Date.parseDate(record.get('EvnStick_sstBegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_sstEndDate').setValue(Date.parseDate(record.get('EvnStick_sstEndDate'), 'd.m.Y'));
			base_form.findField('EvnStick_sstNum').setValue(record.get('EvnStick_sstNum'));

			base_form.findField('StickWorkType_id').setValue(record.get('StickWorkType_id'));
			if (record.get('StickWorkType_id') == 2) {
				base_form.findField('EvnStickDop_pid').setValue(record.get('EvnStickDop_pid'));
				me.evnStickType = 2;
			} else {
				me.evnStickType = 1;
			}
			base_form.findField('StickWorkType_id').fireEvent('change', base_form.findField('StickWorkType_id'), base_form.findField('StickWorkType_id').getValue());

			// оригинал имеет дату согласия
			if ( record.get('EvnStickBase_consentDT') && getRegionNick() != 'kz' ) {
				this.findById(me.id+'EStEF_ESSConsentDelete').show();
				this.findById(me.id+'EStEF_ESSConsentDelete').enable();
			} else {
				this.findById(me.id+'EStEF_ESSConsentDelete').hide();
			}


			if ( record.get('Org_id') && Number(record.get('Org_id')) > 0 ) {
				me.loadField_Org_id(record.get('Org_id'));
			}

			if ( record.get('Org_did') && Number(record.get('Org_did')) > 0 ) {
				me._loadStore_Org_did(record.get('Org_did'));
			}


			// подгружаем пациентов нуждающихся в уходе
			me._loadStoreEvnStickCarePerson({
				EvnStick_id: record.get('EvnStick_GridId'),
				EvnStickBase_isFSS: base_form.findField('EvnStickBase_IsFSS').getValue()
			});


			if(me.action == 'add'){
				// подгружаем только один период освобождения (общий)
				me._load_WorkRelease({
					EvnStick_id: record.get('EvnStick_GridId'),
					LoadSummPeriod: '1'
				});
			}


			// получаем и устанавливаем номер продолжения
			// @TODO разбить на 2 отдельных функции
			me.fetchAndSetEvnStickProd(record.get('EvnStick_id'));

			me.changeStickCauseDopType();

		}
		else {
			base_form.findField('EvnStick_BirthDate').setRawValue('');
			base_form.findField('EvnStick_disDate').setRawValue('');
			base_form.findField('EvnStick_irrDate').setRawValue('');
			base_form.findField('EvnStick_IsDisability').clearValue();
			base_form.findField('InvalidGroupType_id').clearValue();
			base_form.findField('EvnStick_StickDT').setRawValue('');
			base_form.findField('EvnStick_IsRegPregnancy').clearValue();
			base_form.findField('EvnStick_mseDate').setRawValue('');
			base_form.findField('EvnStick_mseExamDate').setRawValue('');
			base_form.findField('EvnStick_mseRegDate').setRawValue('');
			base_form.findField('EvnStick_setDate').setRawValue('');
			base_form.findField('EvnStick_sstBegDate').setRawValue('');
			base_form.findField('EvnStick_sstEndDate').setRawValue('');
			base_form.findField('EvnStick_sstNum').setRawValue('');
			base_form.findField('EvnStick_stacBegDate').setRawValue('');
			base_form.findField('EvnStick_stacEndDate').setRawValue('');
			base_form.findField('UAddress_AddressText').setRawValue('');
			base_form.findField('Lpu_oid').clearValue();
			base_form.findField('MedStaffFact_id').clearValue();
			base_form.findField('Org_did').clearValue();
			base_form.findField('Org_id').clearValue();
			base_form.findField('EvnStick_OrgNick').setRawValue('');
			base_form.findField('StickCause_did').clearValue();

			base_form.findField('StickCause_id').clearValue();
			base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), base_form.findField('StickCause_id').getValue());

			base_form.findField('StickCauseDopType_id').clearValue();
			base_form.findField('StickIrregularity_id').clearValue();

			// Исход ЛВН
			me._setFromStickLeave_Orig();
			base_form.findField('StickLeaveType_id').clearValue();


			
			base_form.findField('StickOrder_id').clearValue();
			base_form.findField('EvnStick_oid').clearValue();

			// Очищаем списки пациентов, нуждающихся в уходе
			me._clearEvnStickCarePersonGrid();
			me._loadEmptyRowEvnStickCarePersonGrid();


			// Очищаем списки освобождений от работы
			me._removeAll_WorkRelease();
			me._addEmpty_WorkRelease();
		}

		// т.к. мы подтянули исход из оригинала или очистили его при отмене выбора оригинала, то вызываем событие change
		base_form.findField('StickLeaveType_id').fireEvent('change', base_form.findField('StickLeaveType_id'), base_form.findField('StickLeaveType_id').getValue());

		base_form.findField('StickOrder_id').fireEvent('change', base_form.findField('StickOrder_id'), base_form.findField('StickOrder_id').getValue());

		return me;
	},

	// Загрузка список оригиналов
	_loadStore_EvnStick_oid: function(callback){
		__l('_loadStore_EvnStick_oid');
		var me = this;

		var base_form = this.FormPanel.getForm();

		base_form.findField('EvnStick_oid').getStore().load({
			callback: function() {
				if(callback){
					callback();
				}
			},
			params: {
				'EvnStick_mid': base_form.findField('EvnStick_mid').getValue(),
				'EvnStick_id': base_form.findField('EvnStick_id').getValue(),
				'EvnStick_oid': base_form.findField('EvnStick_oid').getValue()
			}
		});
	},
	// -----------------------------------------------------------------------------------------------------------------



	// Тип занятости
	listenerChange_StickWorkType_id: function(combo, newValue, oldValue){
		var me = this;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var i = 0;

		base_form.findField('EvnStickDop_pid').clearValue();
		base_form.findField('EvnStickDop_pid').getStore().removeAll();
		base_form.findField('EvnStickDop_pid').setAllowBlank(true);
		base_form.findField('EvnStickDop_pid').setContainerVisible(false);
		base_form.findField('EvnStickDop_pid').fireEvent('change', base_form.findField('EvnStickDop_pid'));

		if (
			getRegionNick() != 'kz'
			&& (
				newValue == 1
				|| newValue == 2
			)
		) {
			base_form.findField('EvnStick_OrgNick').setAllowBlank(false);
		} else {
			base_form.findField('EvnStick_OrgNick').setAllowBlank(true);
		}

		switch(parseInt(newValue)){

			// основная работа
			case 1:
				me.evnStickType = 1;

				if ( me.action != 'view' ) {
					me.findById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].enable();
					me.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].enable();
				}

				base_form.findField('EvnStickDop_pid').clearValue();
				

				if ( this.action != 'view' ) {
					me.enableEdit(true);
					me.findById(me.id+'EStEF_btnSetMinDateFromPS').setVisible(true);
					me.findById(me.id+'EStEF_btnSetMaxDateFromPS').setVisible(true);
				}

				me.findById(me.id+'EStEF_OrgFieldset').show();


				Ext.getCmp('updateEvnStickWorkReleaseGrid').hide();

				if( ! base_form.findField('Org_id').getValue()){
					me._setDefaultValueTo_Org_id();
				}

				if( ! base_form.findField('Post_Name').getValue()){
					me._setDefaultValueTo_Post_Name();
				}
				if (base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'pregn') {
					//#192800
					base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(true);
					base_form.findField('EvnStick_IsRegPregnancy').setAllowBlank(false);
				}
				break;

			// работа по совместительству
			case 2:
				me.evnStickType = 2;

				// Загружаем список ЛВН, выданных по основному месту работы
				base_form.findField('EvnStickDop_pid').getStore().load({
					callback: function() {
						if ( base_form.findField('EvnStickDop_pid').getStore().getCount() == 1 ) {
							base_form.findField('EvnStickDop_pid').setValue(base_form.findField('EvnStickDop_pid').getStore().getAt(0).get('EvnStick_id'));
							base_form.findField('EvnStickDop_pid').fireEvent('change', base_form.findField('EvnStickDop_pid'), base_form.findField('EvnStickDop_pid').getValue());
						}
						if ( base_form.findField('EvnStickDop_pid').getStore().getCount() == 0 ) {
							base_form.findField('EvnStickDop_pid').getStore().loadData([{
								EvnStick_id: -1,
								EvnStick_Num: 'Отсутствует',
								EvnStick_Title: 'Отсутствует'
							}]);
						}

						base_form.findField('EvnStickDop_pid').setAllowBlank(false);
						base_form.findField('EvnStickDop_pid').setContainerVisible(true);
					}.createDelegate(this),
					params: {
						'EvnStick_mid': base_form.findField('EvnStick_mid').getValue()
					}
				});
				//#192800
				if (this.action == 'add') {
					base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(false);
				}
				
				base_form.findField('EvnStick_IsRegPregnancy').setAllowBlank(true);

				me.findById(me.id+'EStEF_OrgFieldset').show();

				Ext.getCmp('updateEvnStickWorkReleaseGrid').show();
				break;

			// стоит на учете в службе занятости
			case 3:
				me.evnStickType = 1;
				base_form.findField('Org_id').clearValue();
				base_form.findField('EvnStick_OrgNick').setRawValue('');
				base_form.findField('Post_Name').setRawValue('');

				me.findById(me.id+'EStEF_OrgFieldset').hide();

				Ext.getCmp('updateEvnStickWorkReleaseGrid').show();

				break;
			default:
				base_form.findField('Org_id').clearValue();
				base_form.findField('EvnStick_OrgNick').setRawValue('');
				base_form.findField('Post_Name').setRawValue('');

				me.findById(me.id+'EStEF_OrgFieldset').hide();

				Ext.getCmp('updateEvnStickWorkReleaseGrid').show();
				break;
		}


		me.checkOrgFieldDisabled();

		if (getRegionNick() != 'kz'){
			me.checkGetEvnStickNumButton();
		}
	},


	// ЛВН по основному месту работы
	listenerChange_EvnStickDop_pid: function(combo, newValue, oldValue){

		if ( newValue <= 0 || Ext.isEmpty(newValue) ) {
			return true;
		}


		var me = this;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var i = 0;


		// Получаем выбранную запись
		var index = combo.getStore().findBy(function(rec) {
			if ( rec.get('EvnStick_id') == newValue ) {
				return true;
			}
			else {
				return false;
			}
		});
		var record = combo.getStore().getAt(index);
		

		


		// блокируем кнопку добавить в списке пациентов нуждающихся в уходе
		this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].disable();

		// блокируем кнопку добавить в списке периодов освобождений
		this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].disable();

		// скрываем кнопку "=" рядом с дата начала в блоке режим в зоне "лечение в стационаре"
		this.findById(me.id+'EStEF_btnSetMinDateFromPS').setVisible(false);

		// скрываем кнопку "=" рядом с дата окончания в блоке режим в зоне "лечение в стационаре"
		this.findById(me.id+'EStEF_btnSetMaxDateFromPS').setVisible(false);


		base_form.findField('EvnStick_oid').disable();
		base_form.findField('EvnStick_BirthDate').disable();
		base_form.findField('EvnStick_irrDate').disable();
		base_form.findField('EvnStick_IsDisability').disable();
		base_form.findField('EvnStick_IsRegPregnancy').disable();
		base_form.findField('EvnStick_mseDate').disable();
		base_form.findField('EvnStick_mseExamDate').disable();
		base_form.findField('EvnStick_mseRegDate').disable();
		base_form.findField('EvnStick_sstBegDate').disable();
		base_form.findField('EvnStick_sstEndDate').disable();
		base_form.findField('EvnStick_sstNum').disable();
		base_form.findField('EvnStick_stacBegDate').disable();
		base_form.findField('EvnStick_stacEndDate').disable();
		base_form.findField('EvnStickFullNameText').disable();
		base_form.findField('InvalidGroupType_id').disable();
		base_form.findField('UAddress_AddressText').disable();
		base_form.findField('Lpu_oid').disable();
		base_form.findField('Org_did').disable();
		base_form.findField('StickIrregularity_id').disable();

		if ( record ) {

			// Устанавливаем значения полей
			base_form.findField('EvnStick_IsDisability').setValue(record.get('EvnStick_IsDisability'));
			base_form.findField('InvalidGroupType_id').setValue(record.get('InvalidGroupType_id'));
			base_form.findField('EvnStick_mseDate').setValue(Date.parseDate(record.get('EvnStick_mseDate'), 'd.m.Y'));
			base_form.findField('EvnStick_mseExamDate').setValue(Date.parseDate(record.get('EvnStick_mseExamDate'), 'd.m.Y'));
			base_form.findField('EvnStick_mseRegDate').setValue(Date.parseDate(record.get('EvnStick_mseRegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_prid').setValue(record.get('EvnStick_prid'));
			base_form.findField('EvnStick_setDate').setValue(Date.parseDate(record.get('EvnStick_setDate'), 'd.m.Y'));
			base_form.findField('EvnStick_stacBegDate').setValue(Date.parseDate(record.get('EvnStick_stacBegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_stacEndDate').setValue(Date.parseDate(record.get('EvnStick_stacEndDate'), 'd.m.Y'));

			// Предыдущий ЛВН
			base_form.findField('EvnStickLast_Title').setValue(record.get('EvnStickLast_Title'));

			base_form.findField('Lpu_oid').setValue(record.get('Lpu_oid'));
			base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
			base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
			base_form.findField('Person_id').setValue(record.get('Person_id'));
			base_form.findField('PersonEvn_id').setValue(record.get('PersonEvn_id'));
			base_form.findField('Server_id').setValue(record.get('Server_id'));
			base_form.findField('StickCause_did').setValue(record.get('StickCause_did'));
			base_form.findField('EvnStick_StickDT').setValue(record.get('EvnStick_StickDT'));
			if (getRegionNick() != 'penza') base_form.findField('StickCause_id').setValue(record.get('StickCause_id'));
			base_form.findField('StickIrregularity_id').setValue(record.get('StickIrregularity_id'));
			base_form.findField('EvnStick_IsRegPregnancy').setValue(record.get('EvnStick_IsRegPregnancy'));
			base_form.findField('EvnStick_irrDate').setValue(Date.parseDate(record.get('EvnStick_irrDate'), 'd.m.Y'));
			base_form.findField('EvnStick_BirthDate').setValue(Date.parseDate(record.get('EvnStick_BirthDate'), 'd.m.Y'));
			base_form.findField('StickCauseDopType_id').setValue(record.get('StickCauseDopType_id'));
			base_form.findField('StickLeaveType_id').setValue(record.get('StickLeaveType_id'));

			base_form.findField('StickOrder_id').setValue(record.get('StickOrder_id'));

			base_form.findField('PridStickLeaveType_Code1').setValue(record.get('PridStickLeaveType_Code'));
			base_form.findField('EvnStick_sstBegDate').setValue(Date.parseDate(record.get('EvnStick_sstBegDate'), 'd.m.Y'));
			base_form.findField('EvnStick_sstEndDate').setValue(Date.parseDate(record.get('EvnStick_sstEndDate'), 'd.m.Y'));
			base_form.findField('EvnStick_sstNum').setValue(record.get('EvnStick_sstNum'));

			base_form.findField('EvnStickFullNameText').setValue(record.get('Person_Fio'));
			if(record.get('Person_Fio') == ''){
				base_form.findField('EvnStick_IsOriginal').disable();
			}

			base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), base_form.findField('StickCause_id').getValue());
			base_form.findField('StickIrregularity_id').fireEvent('change', base_form.findField('StickIrregularity_id'), base_form.findField('StickIrregularity_id').getValue());

			me.setEvnStickDisDate();

			if ( record.get('Org_did') && Number(record.get('Org_did')) > 0 ) {
				me._loadStore_Org_did(record.get('Org_did'));
			}

			if ( record.get('Org_id') && Number(record.get('Org_id')) > 0 ) {
				me.loadField_Org_id(record.get('Org_id'));
			}

			me._loadStoreEvnStickCarePerson({
				EvnStick_id: record.get('EvnStick_id'),
				EvnStickBase_isFSS: base_form.findField('EvnStickBase_IsFSS').getValue()
			});

			me._func1GridEvnStickWorkRelease(record);

			win.changeStickCauseDopType();
		}
		else {

			base_form.findField('EvnStick_BirthDate').setRawValue('');
			base_form.findField('EvnStick_disDate').setRawValue('');
			base_form.findField('EvnStick_irrDate').setRawValue('');
			base_form.findField('EvnStick_IsDisability').clearValue();
			base_form.findField('InvalidGroupType_id').clearValue();
			base_form.findField('EvnStick_StickDT').setRawValue('');
			base_form.findField('EvnStick_IsRegPregnancy').clearValue();
			base_form.findField('EvnStick_mseDate').setRawValue('');
			base_form.findField('EvnStick_mseExamDate').setRawValue('');
			base_form.findField('EvnStick_mseRegDate').setRawValue('');
			base_form.findField('EvnStick_setDate').setRawValue('');
			base_form.findField('EvnStick_sstBegDate').setRawValue('');
			base_form.findField('EvnStick_sstEndDate').setRawValue('');
			base_form.findField('EvnStick_sstNum').setRawValue('');
			base_form.findField('EvnStick_stacBegDate').setRawValue('');
			base_form.findField('EvnStick_stacEndDate').setRawValue('');
			base_form.findField('UAddress_AddressText').setRawValue('');
			base_form.findField('Lpu_oid').clearValue();
			base_form.findField('MedStaffFact_id').clearValue();
			base_form.findField('Org_did').clearValue();
			base_form.findField('StickCause_did').clearValue();

			base_form.findField('StickCause_id').clearValue();
			base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), base_form.findField('StickCause_id').getValue());

			base_form.findField('StickCauseDopType_id').clearValue();
			base_form.findField('StickIrregularity_id').clearValue();
			base_form.findField('StickLeaveType_id').clearValue();
			base_form.findField('StickOrder_id').clearValue();
			base_form.findField('EvnStick_oid').clearValue();


			// Очищаем списки пациентов, нуждающихся в уходе, и освобождений от работы
			me._clearEvnStickCarePersonGrid();

			me._func2GridEvnStickWorkRelease();


		}

		base_form.findField('StickLeaveType_id').fireEvent('change', base_form.findField('StickLeaveType_id'), base_form.findField('StickLeaveType_id').getValue());
		base_form.findField('StickOrder_id').fireEvent('change', base_form.findField('StickOrder_id'), base_form.findField('StickOrder_id').getValue());


	},

	// -----------------------------------------------------------------------------------------------------------------
	// Организация (Место работы)
	_setDefaultValueTo_Org_id: function(){
		__l('_setDefaultValueTo_Org_id');

		var me = this;
		var base_form = me.FormPanel.getForm();
		var Person_id = base_form.findField('Person_id').getValue();

		if(getRegionNick() != 'kz'){
			if(
				Person_id &&

				// Тип занятости: основная работа
				base_form.findField('StickWorkType_id').getValue() == 1 &&

				// Оригина: оригинал
				base_form.findField('EvnStick_IsOriginal').getValue() != 2
			){
				var jobInfo = me.getPersonJobInfo(Person_id);
				if(jobInfo && ! Ext.isEmpty(jobInfo.Org_id)){
					me.loadField_Org_id(jobInfo.Org_id);
				}

			}
		} else if(getRegionNick() == 'kz'){
			if(
				Person_id &&
				// Оригина: оригинал
				base_form.findField('EvnStick_IsOriginal').getValue() != 2
			){
				var jobInfo = me.getPersonJobInfo(Person_id);
				if(jobInfo && ! Ext.isEmpty(jobInfo.Org_id)){
					me.loadField_Org_id(jobInfo.Org_id);
				}


			}
		}

		return true;
	},
	loadField_Org_id: function(Org_id){
		__l('loadField_Org_id');
		var me = this;

		if(Ext.isEmpty(Org_id) || Org_id == undefined){
			return me;
		}

		var base_form = me.FormPanel.getForm();
		base_form.findField('Org_id').getStore().load({
			callback: function(records, options, success) {
				if ( success ) {
					base_form.findField('Org_id').setValue(Org_id);
					var rec = base_form.findField('Org_id').getStore().getAt(0);
					if ( rec && rec.get('Org_StickNick') && rec.get('Org_StickNick').length > 0 ) {
						base_form.findField('EvnStick_OrgNick').setValue(rec.get('Org_StickNick'));
					} else {
						base_form.findField('EvnStick_OrgNick').setValue(base_form.findField('Org_id').getRawValue());
					}
				}
			},
			params: {
				Org_id: Org_id,
				OrgType: 'org'
			}
		});

		return me;
	},
	// -----------------------------------------------------------------------------------------------------------------


	// -----------------------------------------------------------------------------------------------------------------
	_loadStore_Org_did: function(Org_did){
		__l('_loadStore_Org_did');
		var me = this;

		if(Ext.isEmpty(Org_did) || Org_did == undefined){
			return me;
		}

		var base_form = me.FormPanel.getForm();
		base_form.findField('Org_did').getStore().load({
			callback: function(records, options, success) {
				if ( success ) {
					base_form.findField('Org_did').setValue(Org_did);
				}
			},
			params: {
				Org_id: Org_did,
				OrgType: 'org'
			}
		});
	},
	// -----------------------------------------------------------------------------------------------------------------



	// -----------------------------------------------------------------------------------------------------------------
	// Должность (Место работы)
	_setDefaultValueTo_Post_Name: function(){
		__l('_setDefaultValueTo_Post_Name');

		var me = this;
		var base_form = me.FormPanel.getForm();
		var Person_id = base_form.findField('Person_id').getValue();

		if(getRegionNick() != 'kz'){
			if(


				// Тип занятости: основная работа
				base_form.findField('StickWorkType_id').getValue() == 1 &&

				// Оригина: оригинал
				base_form.findField('EvnStick_IsOriginal').getValue() != 2
			){
				var jobInfo = me.getPersonJobInfo(Person_id);

				if(jobInfo && ! Ext.isEmpty(jobInfo.Post_Name)){
					base_form.findField('Post_Name').setValue(jobInfo.Post_Name);
				}


			}
		} else if(getRegionNick() == 'kz'){
			if(


				// Оригина: оригинал
				base_form.findField('EvnStick_IsOriginal').getValue() != 2
			){
				var jobInfo = me.getPersonJobInfo(Person_id);
				if(jobInfo && ! Ext.isEmpty(jobInfo.Post_Name)){
					base_form.findField('Post_Name').setValue(jobInfo.Post_Name);
				}


			}
		}

		return true;
	},
	// Для поля "Должность"
	getPersonJobInfo: function(Person_id){
		__l('getPersonJobInfo');
		var me = this;

		var job_info = null;

		if( ! Ext.isEmpty(Person_id)){
			$.ajax({
				method: "POST",
				url: '/?c=Person&m=getPersonJobInfo',
				data: {
					'Person_id': Person_id
				},
				async: false,
				success: function(response){
					var result = Ext.util.JSON.decode(response);
					if( ! Ext.isEmpty(result) &&  ! Ext.isEmpty(result[0])){
						job_info = result[0];
					}
				}
			});
		}

		return job_info;
	},
	// -----------------------------------------------------------------------------------------------------------------


	// =================================================================================================================











	// =================================================================================================================
	// 1. Список пациентов, нуждающихся в уходе
	// =================================================================================================================

	_hidePanelEvnStickCarePerson: function(){
		var me = this;
		me.findById(me.id+'EStEF_EvnStickCarePersonPanel').hide();
		return me;
	},
	_showPanelEvnStickCarePerson: function(){
		var me = this;
		me.findById(me.id+'EStEF_EvnStickCarePersonPanel').show();
		return me;
	},
	_clearEvnStickCarePersonGrid: function(){
		__l('_clearEvnStickCarePersonGrid');
		var me = this;

		me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().removeAll();

		return me;
	},
	_loadEmptyRowEvnStickCarePersonGrid: function(){
		__l('_loadEmptyRowEvnStickCarePersonGrid');
		var me = this;

		LoadEmptyRow(me.findById(me.id+'EStEF_EvnStickCarePersonGrid'));

		return me;
	},
	_loadStoreEvnStickCarePerson: function(params, callback){
		__l('_loadStoreEvnStickCarePerson');
		var me = this;
		this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().load({
			params: params,
			callback: callback
		});

		return me;
	},
	_getStoreEvnStickCarePerson: function(){
		__l('_getStoreEvnStickCarePerson');
		var me = this;
		return this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore();
	},
	_isFocusAccessCarePersonPanel: function(){
		if(
			! this.findById(me.id+'EStEF_EvnStickCarePersonPanel').hidden &&
			! this.findById(win.id+'EStEF_EvnStickCarePersonPanel').collapsed &&
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0
		) {
			return true;
		}

		return false;
	},
	_focusCarePersonPanel: function(){
		this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
		this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
	},
	// =================================================================================================================








	
	// =================================================================================================================
	// 2. Режим
	// =================================================================================================================


	// Флаг доступа к блоку
	isAccessToStickRegime: false,


	doSign_StickRegime: function(options){
		__l('doSign_StickRegime');

		if (typeof options != 'object') {
			options = new Object();
		}

		var me = this;
		var base_form = me.FormPanel.getForm();


		if (!options.ignoreSave) {
			// предварительно всегда сохраняем весь ЛВН.
			options.ignoreSave = true;
			me.doSave({
				ignoreSignatureCheck: true,
				callback: function () {
					me.doSign_StickRegime(options);
				}
			});
			return false;
		}

		var params = {};
		params.SignObject = 'irr';
		params.Evn_id = base_form.findField('EvnStick_id').getValue();
		params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();


		me._doSign(getOthersOptions().doc_signtype, params);
	},

	/**
	 * Закрываем доступ + ставим флаг
	 *
	 * @returns {boolean}
	 * @private
	 */
	_closeAccessToPanelStickRegime: function(){
		__l('_closeAccessToPanelStickRegime');
		var me = this;

		me.isAccessToStickRegime = false;
		me.getFields(me.id+'EStEF_StickRegimePanel').forEach(me.disableField);

		return true;
	},
	/**
	 * Открываем доступ + ставим флаг
	 *
	 * ВАЖНО!!!!!!!!!!!!!!!!!!!!!! - Не забываем что при открытии блока мы так же проверяем доступность каждого
	 * поля в методе checkFieldDisabled()
	 *
	 * @returns {boolean}
	 * @private
	 */
	_openAccessToPanelStickRegime: function(){
		__l('_openAccessToPanelStickRegime');
		var me = this;

		me.isAccessToStickRegime = true;
		me.getFields(me.id+'EStEF_StickRegimePanel').forEach(me.enableField);

		return true;
	},
	/**
	 * Проверяем доступность блока
	 * @returns {boolean} false - доступ закрыт, true - доступ открыт
	 * @private
	 */
	_checkAccessToPanelStickRegime: function(){
		__l('_checkAccessToPanelStickRegime');

		var me = this;
		var win = this;
		var base_form = me.FormPanel.getForm();

		// Флаг открытия
		var isOpen = false;

		// Оператор
		if(me.isOperator() == true){
			// refs #136152
			var checkUslovie1 = true; //false; ВРЕМЕННО!!! Так как ошибка немедленная, то пока просто откроем на редактирование.
			var checkUslovie2 = false;
			var checkUslovie3 = false;


			// 1.У ЭЛН нет признака «Принят ФСС» (IsPaid) И «В реестре» (IsInReg)
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}


			// Регион: Все, кроме Казахстана
			if(getRegionNick() != 'kz'){

				// 2. Статус ЛВН в Промед: открыт, закрыт.
				if(me.checkStatus_EvnStick() == true){
					checkUslovie2 = true;
				}

			// Если Казахстан
			} else {
				checkUslovie2 = true;
			}


			// 3. Только ЛВН, созданные в МО. Пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}


		// Статистик
		if(me.isStatistick() == true){
			// refs #136152
			var checkUslovie1 = true; //false; ВРЕМЕННО!!! Так как ошибка немедленная, то пока просто откроем на редактирование.
			var checkUslovie2 = false;
			var checkUslovie3 = false;


			// 1.У ЭЛН нет признака «Принят ФСС» (IsPaid) И «В реестре» (IsInReg)
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}


			// Регион: Все, кроме Казахстана
			if(getRegionNick() != 'kz'){

				// 2. Статус ЛВН в Промед: открыт, закрыт.
				if(me.checkStatus_EvnStick() == true){
					checkUslovie2 = true;
				}

				// Если Казахстан
			} else {
				checkUslovie2 = true;
			}


			// 3. Только ЛВН, созданные в МО. Пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}


		// Регистратор ЛВН
		if(me.isRegistratorLVN() == true){
			// refs #136152
			var checkUslovie1 = true; //false; ВРЕМЕННО!!! Так как ошибка немедленная, то пока просто откроем на редактирование.
			var checkUslovie2 = false;
			var checkUslovie3 = false;


			// 1.У ЭЛН нет признака «Принят ФСС» (IsPaid) И «В реестре» (IsInReg)
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}


			// Регион: Все, кроме Казахстана
			if(getRegionNick() != 'kz'){

				// 2. Статус ЛВН в Промед: открыт, закрыт.
				if(me.checkStatus_EvnStick() == true){
					checkUslovie2 = true;
				}

				// Если Казахстан
			} else {
				checkUslovie2 = true;
			}


			// 3. Только ЛВН, созданные в МО. Пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}


		// Регистратор
		if(me.isRegistrator() == true){
			// isOpen = false;
		}


		// Врач
		if(me.isVrach() == true){
			// refs #136152
			var checkUslovie1 = true; //false; ВРЕМЕННО!!! Так как ошибка немедленная, то пока просто откроем на редактирование.
			var checkUslovie2 = false;
			var checkUslovie3 = false;
			var checkUslovie4 = false;

			// Регион: Все, кроме Казахстана
			if(getRegionNick() != 'kz'){

				// 1. У ЭЛН нет признака «Принят ФСС» (IsPaid) И нет признака «В реестре» (IsInReg)  (Регион: Все, кроме Казахстана).
				if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
					checkUslovie1 = true;
				}

				// Если Казахстан
			} else {
				checkUslovie1 = true;
			}


			// 2. Статус ЛВН в Промед: открыт, закрыт.
			if(me.checkStatus_EvnStick() == true){
				checkUslovie2 = true;
			}

			// // 3. ЭЛН добавлен в МО пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
			// if(me.checkOwn_Lpu() == true){
			// 	checkUslovie3 = true;
			// }

			// // 4. Врач указан в качестве врача в любом периоде  «Освобождения от работы» или исходе
			// if (me.checkMedPersonalInWorkRelease() == true || me.checkMedPersonalInStickLeave() || me.action == 'add') {
			// 	checkUslovie4 = true;
			// }

			//#156721 Поле «Нарушение режима» доступно для редактирования независимо от МО создания ЛВН и присутствия врача в освобождении от работы
			checkUslovie3 = true; 
			checkUslovie4 = true;

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true && checkUslovie4 == true){
				isOpen = true;
			}
		}


		// Врач, регистратор (одновременно)
		if(me.isVrachAndRegistrator() == true){
			// refs #136152
			var checkUslovie1 = true; //false; ВРЕМЕННО!!! Так как ошибка немедленная, то пока просто откроем на редактирование.
			var checkUslovie2 = false;
			var checkUslovie3 = false;
			var checkUslovie4 = false;

			// Регион: Все, кроме Казахстана
			if(getRegionNick() != 'kz'){

				// 1. У ЭЛН нет признака «Принят ФСС» (IsPaid) И нет признака «В реестре» (IsInReg)  (Регион: Все, кроме Казахстана).
				if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
					checkUslovie1 = true;
				}

				// Если Казахстан
			} else {
				checkUslovie1 = true;
			}


			// 2. Статус ЛВН в Промед: открыт, закрыт.
			if(me.checkStatus_EvnStick() == true){
				checkUslovie2 = true;
			}

			// 3. ЭЛН добавлен в МО пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie3 = true;
			}

			// 4. Врач указан в качестве врача в любом периоде  «Освобождения от работы» или исходе
			if (me.checkMedPersonalInWorkRelease() == true || me.checkMedPersonalInStickLeave() && me.action == 'add') {
				checkUslovie4 = true;
			}


			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true && checkUslovie4 == true){
				isOpen = true;
			}
		}



		return isOpen;
	},

	// Проверка доступности кнопки "подписать режим"
	_checkAccessToField_swSignStickIrr: function() {
		var me = this;
		var isOpen = false;
		var base_form = this.FormPanel.getForm();

		// Оператор
		if(me.isOperator() == true){
			// Доступ не открываем
		}

		// Статистик
		if(me.isStatistick() == true){
			// Доступ не открываем
		}

		// Регистратор ЛВН
		if(me.isRegistratorLVN() == true){
			// Доступ не открываем
		}

		// Регистратор
		if(me.isRegistrator() == true){
			// Доступ не открываем
		}
		
		// Врач
		if(me.isVrach() == true){

			var checkUslovie1 = false;
			// Врач указан в качестве врача в любом периоде  «Освобождения от работы» или исходе
			if (me.checkMedPersonalInWorkRelease() == true || me.checkMedPersonalInStickLeave()) {
				checkUslovie1 = true;
			}
			
			var checkUslovie2 = false;
			var checkUslovie2_1 = false;
			var checkUslovie2_2 = false;

			// Режим подписан
			if ( me.Signatures_iid ) {
				checkUslovie2_1 = true;
				// Режим подписан текущим врачом
				if( me.signedRegime_MedPersonal_id == getGlobalOptions().medpersonal_id ) {
					checkUslovie2_2 = true;
				}
			}
			// режим не подписан или его пописал текущий врач
			if ( !checkUslovie2_1 || checkUslovie2_2 ) {
				checkUslovie2 = true;
			}


			var checkUslovie3 = false;
			// Поле "Нарушение режима" заполнено
			if ( base_form.findField('StickIrregularity_id').getValue() ) {
				checkUslovie3 = true;
			}
			

			if ( checkUslovie1 && checkUslovie2 && checkUslovie3 ) {
				isOpen = true;
			}
		}

		if(me.isVrachVK() == true){
			var checkUslovie1 = false;
			// Поле "Нарушение режима" заполнено
			if ( base_form.findField('StickIrregularity_id').getValue() ) {
				checkUslovie1 = true;
			}


			var checkUslovie2 = false;
			var checkUslovie2_1 = false;
			var checkUslovie2_2 = false;
			// Режим подписан
			if ( me.Signatures_iid ) {
				checkUslovie2_1 = true;
				// Режим подписан текущим врачом
				if( me.signedRegime_MedPersonal_id == getGlobalOptions().medpersonal_id ) {
					checkUslovie2_2 = true;
				}
			}
			// режим не подписан или его пописал текущий врач
			if ( !checkUslovie2_1 || checkUslovie2_2 ) {
				checkUslovie2 = true;
			}
			
			if ( checkUslovie1 && checkUslovie2 ) {
				isOpen = true;
			}
		}

		// Врач, регистратор (одновременно)
		if(me.isVrachAndRegistrator() == true){
			// Доступ не открываем
		}


		return isOpen;

	},

	// Закрываем доступ к кнопке "Подписать режим"
	_closeAccessToField_swSignStickIrr: function() {
		this.findById('swSignStickIrr').disable();
	},

	// Открываем доступ к кнопке "Подписать режим"
	_openAccessToField_swSignStickIrr: function() {
		this.findById('swSignStickIrr').enable();
	},

	// -----------------------------------------------------------------------------------------------------------------
	// Дата начала (Блок «Лечение в стационаре»)
	// EvnStick_stacBegDate
	// -----------------------------------------------------------------------------------------------------------------


	// Флаг доступа
	isAccessToField_EvnStick_stacBegDate: false, // false - доступ закрыт, true - доступ открыт

	_listenerChange_EvnStick_stacBegDate: function() {
		var base_form = this.FormPanel.getForm();
		base_form.findField('EvnStick_stacEndDate').setMinValue(base_form.findField('EvnStick_stacBegDate').getValue());

		if (!Ext.isEmpty(base_form.findField('EvnStick_stacBegDate').getValue())) {
			base_form.findField('EvnStick_stacEndDate').setAllowBlank(false);
		} else {
			base_form.findField('EvnStick_stacEndDate').setAllowBlank(true);
		}
	},

	/**
	 * Закрываем доступ к EvnStick_stacBegDate
	 *
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_closeAccessToField_EvnStick_stacBegDate: function(){
		__l('_closeAccessToField_EvnStick_stacBegDate');
		var me = this;

		me.isAccessToField_EvnStick_stacBegDate = false;
		me.FormPanel.getForm().findField('EvnStick_stacBegDate').disable();

		return me;
	},

	/**
	 * Открываем доступ к EvnStick_stacBegDate
	 *
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_openAccessToField_EvnStick_stacBegDate: function(){
		__l('_openAccessToField_EvnStick_stacBegDate');
		var me = this;

		me.isAccessToField_EvnStick_stacBegDate = true;
		me.FormPanel.getForm().findField('EvnStick_stacBegDate').enable();

		return me;
	},

	/**
	 * Проверяем доступ к полю "Дата начала" (EvnStick_stacBegDate)
	 *
	 * @returns {boolean} false - доступ закрыт, true - доступ открыт
	 * @private
	 */
	_checkAccessToField_EvnStick_stacBegDate: function(){
		__l('_checkAccessToField_EvnStick_stacBegDate');

		var me = this;
		var isOpen = false;

		if (me.checkIsLvnFromFSS() == true) { // доступ закрыт для ЛВН из ФСС
			return isOpen;
		}

		// Оператор +
		if(me.isOperator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			var checkUslovie3_1 = false;
			var checkUslovie3_2 = false;

			// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
			if(me.checkIsLvnOpenFromKVS() == true){

				if(getRegionNick() != 'kz'){
					if(me.checkHasDvijeniaInStac24() == true){
						checkUslovie3_1 = true;
					}

					// (#128729 Для Казахстана тип стационара не учитывается)
				} else {
					if(me.checkHasDvijenia() == true){
						checkUslovie3_1 = true;
					}
				}

			}

			// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
			// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
			// «дата окончания» не пустые.
			if(me.action == 'edit'){

				var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
				var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

				if( ! Ext.isEmpty(stacBegDate) || ! Ext.isEmpty(stacEndDate)){
					checkUslovie3_2 = true;
				}
			}


			if(checkUslovie3_1 == true || checkUslovie3_2 == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Статистик +
		if(me.isStatistick() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			var checkUslovie3_1 = false;
			var checkUslovie3_2 = false;

			// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
			if(me.checkIsLvnOpenFromKVS() == true){

				if(getRegionNick() != 'kz'){
					if(me.checkHasDvijeniaInStac24() == true){
						checkUslovie3_1 = true;
					}

					// (#128729 Для Казахстана тип стационара не учитывается)
				} else {
					if(me.checkHasDvijenia() == true){
						checkUslovie3_1 = true;
					}
				}

			}

			// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
			// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
			// «дата окончания» не пустые.
			if(me.action == 'edit'){

				var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
				var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

				if( ! Ext.isEmpty(stacBegDate) || ! Ext.isEmpty(stacEndDate)){
					checkUslovie3_2 = true;
				}
			}


			if(checkUslovie3_1 == true || checkUslovie3_2 == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true  && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Регистратор ЛВН +
		if(me.isRegistratorLVN() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			var checkUslovie3_1 = false;
			var checkUslovie3_2 = false;

			// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
			if(me.checkIsLvnOpenFromKVS() == true){

				if(getRegionNick() != 'kz'){
					if(me.checkHasDvijeniaInStac24() == true){
						checkUslovie3_1 = true;
					}

					// (#128729 Для Казахстана тип стационара не учитывается)
				} else {
					if(me.checkHasDvijenia() == true){
						checkUslovie3_1 = true;
					}
				}
			}

			// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
			// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
			// «дата окончания» не пустые.
			if(me.action == 'edit'){

				var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
				var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

				if( ! Ext.isEmpty(stacBegDate) || ! Ext.isEmpty(stacEndDate)){
					checkUslovie3_2 = true;
				}
			}

			if(checkUslovie3_1 == true || checkUslovie3_2 == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Регистратор
		if(me.isRegistrator() == true){
			//isOpen = false;
		}

		// Врач
		if(me.isVrach() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
			if(me.checkIsLvnOpenFromKVS() == true){
				if(getRegionNick() != 'kz'){

					if(me.checkHasDvijeniaInStac24() == true){
						checkUslovie3 = true;
					}

					// (#128729 Для Казахстана тип стационара не учитывается)
				} else {
					if(me.checkHasDvijenia() == true){
						checkUslovie3 = true;
					}
				}
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}

		}

		// Врач, регистратор (одновременно)
		if(me.isVrachAndRegistrator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
			if(me.checkIsLvnOpenFromKVS() == true){

				if(getRegionNick() != 'kz'){
					if(me.checkHasDvijeniaInStac24() == true){
						checkUslovie3 = true;
					}

					// (#128729 Для Казахстана тип стационара не учитывается)
				} else {
					if(me.checkHasDvijenia() == true){
						checkUslovie3 = true;
					}
				}
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}


		return isOpen;
	},

	// Значение по умолчанию при создании ЛВН
	_setDefaultValueTo_EvnStick_stacBegDate: function(){
		__l('_setDefaultValueTo_EvnStick_stacBegDate');
		var me = this;
		var base_form = this.FormPanel.getForm();
		if(me.parentClass == 'EvnPL'){
			return false;
		}

		var EvnSectionDates = me.getBegEndDatesInStac();

		if(
			! Ext.isEmpty(EvnSectionDates) &&
			! Ext.isEmpty(EvnSectionDates.EvnSection_setDate) &&
			! base_form.findField('EvnStickBase_IsFSS').getValue() &&
			me._checkAccessToField_EvnStick_stacBegDate() == true &&
			base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') != 'pregn' // отпуск по беременности и родам
		){
			me.FormPanel.getForm().findField('EvnStick_stacBegDate').setValue(EvnSectionDates.EvnSection_setDate);
		}

		return true;
	},

	// Обработка значения при редактировании или просмотре ЛВН
	_setProcessValueTo_EvnStick_stacBegDate: function(){
		__l('_setProcessValueTo_EvnStick_stacBegDate');
		var me = this;
		var base_form = me.FormPanel.getForm();

		var EvnSection_setDate = base_form.findField('EvnSection_setDate').getValue();

		if (EvnSection_setDate.length > 0) {
			if(me.advanceParams.stacBegDate == undefined){
				me.advanceParams.stacBegDate = EvnSection_setDate;
			} else {

				if(Date.parseDate(EvnSection_setDate, 'd.m.Y') < Date.parseDate(me.advanceParams.stacBegDate, 'd.m.Y')){
					me.advanceParams.stacBegDate = EvnSection_setDate;
				}

				base_form.findField('EvnSection_setDate').setValue(me.advanceParams.stacBegDate);

			}
		}

		return me;
	},
	// -----------------------------------------------------------------------------------------------------------------





	// -----------------------------------------------------------------------------------------------------------------
	// Минимальную дата поступления со связанных карт выбывшего из стационара (Кнопка "=" рядом с датой)
	// me.id + 'EStEF_btnSetMinDateFromPS'
	// -----------------------------------------------------------------------------------------------------------------

	// Флаг доступа
	isAccessToField_EStEF_btnSetMinDateFromPS: false, // false - доступ закрыт, true - доступ открыт

	_openAccessToField_EStEF_btnSetMinDateFromPS: function(){
		__l('_openAccessToField_EStEF_btnSetMinDateFromPS');
		var me = this;

		me.isAccessToField_EStEF_btnSetMinDateFromPS = false;
		me.findById(me.id+'EStEF_btnSetMinDateFromPS').setVisible(true);

		return me;
	},
	_closeAccessToField_EStEF_btnSetMinDateFromPS: function(){
		__l('_closeAccessToField_EStEF_btnSetMinDateFromPS');
		var me = this;

		me.isAccessToField_EStEF_btnSetMinDateFromPS = false;
		me.findById(me.id+'EStEF_btnSetMinDateFromPS').setVisible(false);

		return me;
	},
	// -----------------------------------------------------------------------------------------------------------------




	// -----------------------------------------------------------------------------------------------------------------
	// Дата окончания (Блок «Лечение в стационаре»)
	// EvnStick_stacEndDate
	// -----------------------------------------------------------------------------------------------------------------

	// Флаг доступа
	isAccessToField_EvnStick_stacEndDate: false, // false - доступ закрыт, true - доступ открыт

	/**
	 * Закрываем доступ к EvnStick_stacEndDate
	 *
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_closeAccessToField_EvnStick_stacEndDate: function(){
		__l('_closeAccessToField_EvnStick_stacEndDate');

		var me = this;

		me.isAccessToField_EvnStick_stacEndDate = false;
		me.FormPanel.getForm().findField('EvnStick_stacEndDate').disable();

		return me;
	},

	/**
	 * Открываем доступ к EvnStick_stacEndDate
	 *
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_openAccessToField_EvnStick_stacEndDate: function(){
		__l('_openAccessToField_EvnStick_stacEndDate');
		var me = this;

		me.isAccessToField_EvnStick_stacEndDate = true;
		me.FormPanel.getForm().findField('EvnStick_stacEndDate').enable();

		return me;
	},

	/**
	 * Проверяем доступ к полю "Дата окончания" (EvnStick_stacEndDate)
	 *
	 * @returns {boolean} false - доступ закрыт, true - доступ открыт
	 * @private
	 */
	_checkAccessToField_EvnStick_stacEndDate: function(){
		__l('_checkAccessToField_EvnStick_stacEndDate');

		var me = this;
		var isOpen = false;

		if (me.checkIsLvnFromFSS() == true) { // доступ закрыт для ЛВН из ФСС
			return isOpen;
		}

		// Оператор
		if(me.isOperator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			var checkUslovie3_1 = false;
			var checkUslovie3_2 = false;

			// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
			if(me.checkIsLvnOpenFromKVS() == true){

				if(getRegionNick() != 'kz'){
					if(me.checkHasDvijeniaInStac24() == true){
						checkUslovie3_1 = true;
					}

					// (#128729 Для Казахстана тип стационара не учитывается)
				} else {
					if(me.checkHasDvijenia() == true){
						checkUslovie3_1 = true;
					}
				}

			}

			// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
			// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
			// «дата окончания» не пустые.
			if(me.action == 'edit'){

				var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
				var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

				if( ! Ext.isEmpty(stacBegDate) || ! Ext.isEmpty(stacEndDate)){
					checkUslovie3_2 = true;
				}
			}


			if(checkUslovie3_1 == true || checkUslovie3_2 == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true  && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Статистик
		if(me.isStatistick() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			var checkUslovie3_1 = false;
			var checkUslovie3_2 = false;

			// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
			if(me.checkIsLvnOpenFromKVS() == true){

				if(getRegionNick() != 'kz'){
					if(me.checkHasDvijeniaInStac24() == true){
						checkUslovie3_1 = true;
					}

					// (#128729 Для Казахстана тип стационара не учитывается)
				} else {
					if(me.checkHasDvijenia() == true){
						checkUslovie3_1 = true;
					}
				}

			}

			// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
			// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
			// «дата окончания» не пустые.
			if(me.action == 'edit'){

				var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
				var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

				if( ! Ext.isEmpty(stacBegDate) || ! Ext.isEmpty(stacEndDate)){
					checkUslovie3_2 = true;
				}
			}


			if(checkUslovie3_1 == true || checkUslovie3_2 == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true  && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Регистратор ЛВН
		if(me.isRegistratorLVN() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1.	ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}

			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			var checkUslovie3_1 = false;
			var checkUslovie3_2 = false;

			// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
			if(me.checkIsLvnOpenFromKVS() == true){

				if(getRegionNick() != 'kz'){
					if(me.checkHasDvijeniaInStac24() == true){
						checkUslovie3_1 = true;
					}

					// (#128729 Для Казахстана тип стационара не учитывается)
				} else {
					if(me.checkHasDvijenia() == true){
						checkUslovie3_1 = true;
					}
				}
			}

			// для пользователей АРМ регистратора, статистика и оператора, #126943 при редактировании ЛВН
			// (вне зависимости места вызова формы) если #131544 хотя бы одно из полей «дата начала» или
			// «дата окончания» не пустые.
			if(me.action == 'edit'){

				var stacBegDate = me.FormPanel.getForm().findField('EvnStick_stacBegDate').getValue();
				var stacEndDate = me.FormPanel.getForm().findField('EvnStick_stacEndDate').getValue();

				if( ! Ext.isEmpty(stacBegDate) || ! Ext.isEmpty(stacEndDate)){
					checkUslovie3_2 = true;
				}
			}


			if(checkUslovie3_1 == true || checkUslovie3_2 == true){
				checkUslovie3 = true;
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}

		// Регистратор
		if(me.isRegistrator() == true){
			//isOpen = false;
		}

		// Врач
		if(me.isVrach() == true){

			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
			if(me.checkIsLvnOpenFromKVS() == true){

				if(getRegionNick() != 'kz'){
					if(me.checkHasDvijeniaInStac24() == true){
						checkUslovie3 = true;
					}

					// (#128729 Для Казахстана тип стационара не учитывается)
				} else {
					if(me.checkHasDvijenia() == true){
						checkUslovie3 = true;
					}
				}
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}

		}

		// Врач, регистратор (одновременно)
		if(me.isVrachAndRegistrator() == true){
			var checkUslovie1 = false;
			var checkUslovie2 = false;
			var checkUslovie3 = false;

			// 1. ЭЛН не имеет признаков «Принят в ФСС», «В реестре» (ни одного из них).
			if(me.checkExist_isPaid() == false && me.checkExist_isInReg() == false){
				checkUslovie1 = true;
			}


			// 2. ЛВН создан в МО пользователя или в поле «Санаторий» указана МО пользователя.
			if(me.checkOwn_Lpu() == true){
				checkUslovie2 = true;
			}

			// ЛВН открыт из КВС, которая содержит хотя бы 1 движение в круглосуточном стационаре
			if(me.checkIsLvnOpenFromKVS() == true){

				if(getRegionNick() != 'kz'){
					if(me.checkHasDvijeniaInStac24() == true){
						checkUslovie3 = true;
					}

					// (#128729 Для Казахстана тип стационара не учитывается)
				} else {
					if(me.checkHasDvijenia() == true){
						checkUslovie3 = true;
					}
				}
			}

			if(checkUslovie1 == true && checkUslovie2 == true && checkUslovie3 == true){
				isOpen = true;
			}
		}


		return isOpen;
	},

	// Значение по умолчанию при создании ЛВН
	_setDefaultValueTo_EvnStick_stacEndDate: function(){
		__l('_setDefaultValueTo_EvnStick_stacEndDate');
		var me = this;
		var base_form = this.FormPanel.getForm();

		if(me.parentClass == 'EvnPL'){
			return false;
		}

		var EvnSectionDates = me.getBegEndDatesInStac();

		if( 
			! Ext.isEmpty(EvnSectionDates) 
			&& ! Ext.isEmpty(EvnSectionDates.EvnSection_disDate) 
			&& ! base_form.findField('EvnStickBase_IsFSS').getValue()
			&& me._checkAccessToField_EvnStick_stacEndDate() == true
			&& base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') != 'pregn' // отпуск по беременности и родам
		){
			me.FormPanel.getForm().findField('EvnStick_stacEndDate').setValue(EvnSectionDates.EvnSection_disDate);
		}

		return true;
	},

	// Обработка значения при редактировании или просмотре ЛВН при первоначальном открытии формы
	_setProcessValueTo_EvnStick_stacEndDate: function(){
		__l('_setProcessValueTo_EvnStick_stacEndDate');
		var me = this;
		var base_form = me.FormPanel.getForm();



		// -------------------------------------------------------------------------------------------------------------
		// проверить логичность всего блока для этой функции
		var EvnSection_disDate = base_form.findField('EvnSection_disDate').getValue();
		if (EvnSection_disDate.length > 0) {
			if(me.advanceParams.stacBegDate == undefined){
				me.advanceParams.stacEndDate = EvnSection_disDate;
			} else {

				if(EvnSection_disDate.length == 0 || me.advanceParams.stacEndDate == null){
					me.advanceParams.stacEndDate = null;
				}
				else if(Date.parseDate(EvnSection_disDate, 'd.m.Y') > Date.parseDate(me.advanceParams.stacEndDate, 'd.m.Y')){
					me.advanceParams.stacEndDate = EvnSection_disDate;
				}

				// этот код логически не подходит для этой функции
				base_form.findField('EvnSection_disDate').setValue(me.advanceParams.stacEndDate);
			}
		}
		// -------------------------------------------------------------------------------------------------------------

		me._listenerChange_EvnStick_stacBegDate();

		return me;
	},
	// -----------------------------------------------------------------------------------------------------------------




	// -----------------------------------------------------------------------------------------------------------------
	// Максимальная дата поступления со связанных карт выбывшего из стационара (Кнопка "=" рядом с датой)
	// me.id + 'EStEF_btnSetMaxDateFromPS'
	// -----------------------------------------------------------------------------------------------------------------
	_openAccessToField_EStEF_btnSetMaxDateFromPS: function(){
		__l('_openAccessToField_EStEF_btnSetMaxDateFromPS');
		var me = this;

		me.EStEF_btnSetMaxDateFromPS = false;
		me.findById(me.id+'EStEF_btnSetMaxDateFromPS').setVisible(true);

		return me;
	},
	_closeAccessToField_EStEF_btnSetMaxDateFromPS: function(){
		__l('_closeAccessToField_EStEF_btnSetMaxDateFromPS');
		var me = this;

		me.EStEF_btnSetMaxDateFromPS = false;
		me.findById(me.id+'EStEF_btnSetMaxDateFromPS').setVisible(false);

		return me;
	},
	// -----------------------------------------------------------------------------------------------------------------




	_findEvnStick_stacDates: function(){
		__l('_findEvnStick_stacDates');
		var me = this;
		// -------------------------------------------------------------------------------------------------------------
		// Поиск дат (дата начала первого движения и дату окончания последнего движения) для блока "Лечение в стационаре"
		var EvnSectionDates = me.getBegEndDatesInStac();
		if(Ext.isEmpty(me.advanceParams.stacBegDate) && EvnSectionDates){
			if( ! Ext.isEmpty(EvnSectionDates) && ! Ext.isEmpty(EvnSectionDates.EvnSection_setDate)){
				me.advanceParams.stacBegDate = EvnSectionDates.EvnSection_setDate;
			}
		}

		if(Ext.isEmpty(me.advanceParams.stacEndDate) && EvnSectionDates){
			if( ! Ext.isEmpty(EvnSectionDates) && ! Ext.isEmpty(EvnSectionDates.EvnSection_disDate)){
				me.advanceParams.stacEndDate = EvnSectionDates.EvnSection_disDate;
			}
		}
		// -------------------------------------------------------------------------------------------------------------

	},

	// Удаляем номер полученный из ФСС
	clearEvnStickNum: function(callback) {
		this.getLoadMask('Отмена бронирования номера...').show();
		var base_form = this.FormPanel.getForm();
		// разбронировать номер.
		Ext.Ajax.request({
			url: '/?c=RegistryESStorage&m=unbookEvnStickNum',
			params: {
				RegistryESStorage_id: base_form.findField('RegistryESStorage_id').getValue()
			},
			callback: function(request,success,response) {
				this.getLoadMask().hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success){
						base_form.findField('RegistryESStorage_id').setValue(null);
						base_form.findField('EvnStick_Num').setRawValue('');
						base_form.findField('EvnStick_Num').fireEvent('change', base_form.findField('EvnStick_Num'), base_form.findField('EvnStick_Num').getValue());
						this.refreshFormPartsAccess();
						if ( typeof callback == 'function' ) { callback(); }
					}
				}
			}.createDelegate(this)
		});
	},

	getBegEndDatesInStac: function(){
		__l('getBegEndDatesInStac');
		var me = this;

		me.EvnSectionDates = null;


		var EvnPS_id = me.findKVC();

		// Если у ЛВН есть связанная КВС
		if( ! Ext.isEmpty(EvnPS_id)){

			$.ajax({
				method: "POST",
				url: '/?c=Stick&m=getBegEndDatesInStac',
				data: {
					'EvnPS_id': EvnPS_id
				},
				async: false,
				success: function(response){
					var result = Ext.util.JSON.decode(response);
					if( ! Ext.isEmpty(result)){
						me.EvnSectionDates = result[0];
					}
				}
			});

			// нам нужно получить движения с признаком "тип"
		}

		return me.EvnSectionDates;
	},
	// =================================================================================================================





	// =================================================================================================================
	// 3. МСЭ

	// =================================================================================================================


	

	// =================================================================================================================
	// 4. Освобождение от работы (Work Release)


	/**
	 * Разворачиваем или сворачиваем список
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_toggle_WorkReleasePanel: function(){
		var me = this;
		me.panelEvnStickWorkRelease.toggleCollapse();
		return me;
	},

	/**
	 * Выделяем Panel
	 * @private
	 */
	_focus_WorkReleasePanel: function(){
		this._focusFirst_WorkRelease();
	},

	/**
	 * Доступен ли список для пользователя (проверяем виден ли и не пуст ли)
	 * @returns {boolean}
	 * @private
	 */
	_isFocusAccess_WorkReleasePanel: function(){
		if(
			! this.findById(this.id+'EStEF_EvnStickWorkReleasePanel').collapsed &&
			this._getCount_WorkRelease() > 0
		) {
			return true;
		}

		return false;
	},



	// -----------------------------------------------------------------------------------------------------------------
	// Кнопка "Подписать Врачом" в блоке "Освобождение от работы"

	// Флаг доступа
	isAccessToField_WorkRelease_Sign: false, // false - доступ закрыт, true - доступ открыт
	_closeAccessToField_WorkRelease_Sign: function(){
		__l('_closeAccessToField_WorkRelease_Sign');

		var me = this;

		me.isAccessToField_WorkRelease_Sign = false;
		// me.findById('leaveActionsSign').disable();
		me.menuActions_WorkRelease.items.get('leaveActionsSign').disable();


		return me;
	},
	_openAccessToField_WorkRelease_Sign: function(){
		__l('_openAccessToField_WorkRelease_Sign');
		var me = this;

		me.isAccessToField_WorkRelease_Sign = true;
		// me.findById('leaveActionsSign').enable();
		me.menuActions_WorkRelease.items.get('leaveActionsSign').enable();

		return me;
	},

	/**
	 * Проверяем доступность кнопки "Подписать" для выбранного освобождения от работы
	 *
	 * ВАЖНО!!! Обязательно должно быть выбрано хотя бы одно освобождение от работы
	 *
	 * @returns {boolean} true - доступно, false - не доступно
	 * @private
	 */
	_checkAccessToField_WorkRelease_Sign: function(){
		__l('_checkAccessToField_WorkRelease_Sign');

		var me = this;
		var isOpen = false;
		var base_form = this.FormPanel.getForm();


		// Обязательно должно быть выбрано хотя бы одно освобождение от работы
		var selected_record = me._getSelected_WorkRelease();
		if( ! selected_record){
			return isOpen;
		}

		// Оператор
		if(me.isOperator() == true){
			// Доступ не открываем
		}

		// Статистик
		if(me.isStatistick() == true){
			// Доступ не открываем
		}

		// Регистратор ЛВН
		if(me.isRegistratorLVN() == true){
			// Доступ не открываем
		}

		// Регистратор
		if(me.isRegistrator() == true){
			// Доступ не открываем
		}

		// Врач
		if(me.isVrach() == true){
			// ---------------------------------------------------------------------------------------------------------
			var checkUslovie1 = false;

			// Врач указан в качестве врача 1 в периоде  «Освобождения от работы»
			if(getGlobalOptions().medpersonal_id){

				var selected_record = me._getSelected_WorkRelease();

				// Врач указан в качестве врача 1 в периоде  «Освобождения от работы»
				if(
					// Врач указан в качестве врача 1
					getGlobalOptions().medpersonal_id.inlist([
						selected_record.get('MedPersonal_id')
					]) 
				){
					checkUslovie1 = true;
				}
			}

			var checkUslovie2 = false;
			// Если ЭЛН дубликат, то дубликат оформлен текущим врачом
			if (base_form.findField('EvnStick_IsOriginal').getValue() == 2 && me.checkMedPersonalInStickLeave()) {
				checkUslovie2 = true;
			}

			if (checkUslovie1 == true || checkUslovie2 == true) {
				isOpen = true;
			}
		}

		// Врач
		if(me.isVrachVK() == true){
			// Доступ не открываем
		}

		// Врач, регистратор (одновременно)
		if(me.isVrachAndRegistrator() == true){
			// Доступ не открываем
		}


		return isOpen;
	},
	// -----------------------------------------------------------------------------------------------------------------



	// -----------------------------------------------------------------------------------------------------------------
	// Кнопка "Подписать Врачом ВК (врачебной комиссии)" в блоке "Освобождение от работы"

	// Флаг доступа
	isAccessToField_WorkRelease_SignVK: false, // false - доступ закрыт, true - доступ открыт
	_closeAccessToField_WorkRelease_SignVK: function(){
		__l('_closeAccessToField_WorkRelease_SignVK');

		var me = this;

		me.isAccessToField_WorkRelease_SignVK = false;
		// me.findById('leaveActionsSignVK').disable();
		me.menuActions_WorkRelease.items.get('leaveActionsSignVK').disable();

		return me;
	},
	_openAccessToField_WorkRelease_SignVK: function(){
		__l('_openAccessToField_WorkRelease_SignVK');
		var me = this;

		me.isAccessToField_WorkRelease_SignVK = true;
		// me.findById('leaveActionsSignVK').enable();
		me.menuActions_WorkRelease.items.get('leaveActionsSignVK').enable();

		return me;
	},
	_checkAccessToField_WorkRelease_SignVK: function(){
		__l('_checkAccessToField_WorkRelease_SignVK');

		var me = this;
		var isOpen = false;
		var base_form = this.FormPanel.getForm();

		// Обязательно должно быть выбрано хотя бы одно освобождение от работы
		var selected_record = me._getSelected_WorkRelease();
		if( ! selected_record){
			return isOpen;
		}

		// Оператор
		if(me.isOperator() == true){
			// Доступ не открываем
		}

		// Статистик
		if(me.isStatistick() == true){
			// Доступ не открываем
		}

		// Регистратор ЛВН
		if(me.isRegistratorLVN() == true){
			// Доступ не открываем
		}

		// Регистратор
		if(me.isRegistrator() == true){
			// Доступ не открываем
		}

		// Врач
		if(me.isVrach() == true){
			// Доступ не открываем
		}

		// Врач ВК
		if(me.isVrachVK() == true){
			// ---------------------------------------------------------------------------------------------------------
			var checkUslovie1 = false;

			var selected_record = me._getSelected_WorkRelease();

			// Врач указан в качестве врача 3 в периоде  «Освобождения от работы» и проставлен флаг "Председатель ВК"
			if(getGlobalOptions().medpersonal_id && selected_record.get('EvnStickWorkRelease_IsPredVK')){
				if(
					getGlobalOptions().medpersonal_id.inlist([selected_record.get('MedPersonal3_id')]) 
					&& selected_record.get('EvnStickWorkRelease_IsPredVK') == 1
				){
					checkUslovie1 = true;
				}
			}
			// ---------------------------------------------------------------------------------------------------------


			if(checkUslovie1 == true){
				isOpen = true;
			}
		}

		// Врач, регистратор (одновременно)
		if(me.isVrachAndRegistrator() == true){
			// Доступ не открываем
		}


		return isOpen;
	},
	// -----------------------------------------------------------------------------------------------------------------




	// Кнопка "Подписать (ВК)" и "Подписать (Врач)"
	doSign_WorkRelease: function (options) {
		__l('doSign_WorkRelease');

		if (typeof options != 'object') {
			options = new Object();
		}

		var me = this;
		var base_form = me.FormPanel.getForm();


		var selected_record = me._getSelected_WorkRelease();
		if( ! selected_record){
			return false;
		}


		if (!options.ignoreSave) {
			// предварительно всегда сохраняем весь ЛВН.
			options.ignoreSave = true;
			me.doSave({
				ignoreSignatureCheck: true,
				callback: function () {
					me.doSign_WorkRelease(options);
				}
			});
			return false;
		}


		var params = {};
		params.SignObject = options.SignObject; // VK или MP
		params.Evn_id = selected_record.get('EvnStickWorkRelease_id');
		params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();


		me._doSign(getOthersOptions().doc_signtype, params);
	},


	// Кнопка "Список версий документа (ВК)" и "Список версий документа (Врач)"
	doOpenSignHistory_WorkRelease: function(options) {
		__l('doOpenEvnStickSignHistoryWindow');
		var me = this;
		var form = this;
		var params = {};
		var base_form = this.FormPanel.getForm();
		var grid = this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid');
		var selected_record = grid.getSelectionModel().getSelected();
		var SignObject = options.SignObject;
		params.SignObject = options.SignObject;
		if (!selected_record && !SignObject.inlist(['leave', 'irr'])) return false;
		switch(SignObject) {
			case 'MP':
				params.Signatures_id = selected_record.get('Signatures_mid');
				break;
			case 'VK':
				params.Signatures_id = selected_record.get('Signatures_wid');
				break;
			case 'leave':
				params.Signatures_id = form.Signatures_id;
				break;
			case 'irr':
				params.Signatures_id = form.Signatures_iid;
				break;
		}
		getWnd('swStickVersionListWindow').show(params);
	},

	// Кнопка "Верификация документа (ВК)" и "Верификация документа (Врач)"
	doVerifySign_WorkRelease: function(options) {
		__l('doVerifySign_WorkRelease');

		if (typeof options != 'object') {
			options = new Object();
		}

		var me = this;
		var form = this;

		var params = {};
		var base_form = this.FormPanel.getForm();
		var selected_record = me._getSelected_WorkRelease();
		var SignObject = options.SignObject;
		params.SignObject = options.SignObject;

		// http://redmine.swan.perm.ru/issues/124678 по задаче нужно убрать сохранение перед верификацией
		// if (!options.ignoreSave) {
		// 	// предварительно всегда сохраняем весь ЛВН.
		// 	options.ignoreSave = true;
		// 	this.doSave({
		// 		callback: function() {
		// 			form.doVerifySign(options);
		// 		}
		// 	});
		// 	return false;
		// }

		if ( ! selected_record && ! SignObject.inlist(['leave', 'irr'])) return false;
		params.Evn_id = !SignObject.inlist(['leave', 'irr']) ? selected_record.get('EvnStickWorkRelease_id') : base_form.findField('EvnStick_id').getValue();
		params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();

		var doc_signtype = getOthersOptions().doc_signtype;
		if (doc_signtype && doc_signtype.inlist(['authapplet', 'authapi', 'authapitomee'])) {
			params.needVerifyOpenSSL = 1;
		}

		Ext.Ajax.request({
			url: '/?c=Stick&m=verifyEvnStickSign',
			params: params,
			success: function(response, options) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (doc_signtype && doc_signtype.inlist(['authapplet', 'authapi', 'authapitomee'])) {
					if (result.verifyStatus) {
						if (result.verifyStatus == 'valid') {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function() {
									if (!SignObject.inlist(['leave', 'irr'])) {
										me._reload_WorkRelease();
									}
									else {
										form.getEvnStickSignStatus({object: SignObject});
									}
								}.createDelegate(this),
								icon: Ext.Msg.INFO,
								msg: 'Документ подписан',
								title: 'Верификация'
							});
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function() {
									params.Signatures_id = result.Signatures_id;
									params.SignaturesStatus_id = 3;
									Ext.Ajax.request({
										url: '/?c=Stick&m=setSignStatus',
										params: params,
										success: function(response, options) {
											if (!SignObject.inlist(['leave', 'irr'])) {
												me._reload_WorkRelease();
											}
											else {
												form.getEvnStickSignStatus({object: SignObject});
											}
										}
									});
								}.createDelegate(this),
								icon: Ext.Msg.INFO,
								msg: 'Документ не актуален',
								title: 'Верификация'
							});
						}
					}
				} else {
					if (result.xml) {
						sw.Applets.CryptoPro.verifySignedXML({
							xml: result.xml,
							callback: function(success) {
								if (success) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function() {
											if (!SignObject.inlist(['leave', 'irr'])) {
												me._reload_WorkRelease();
											}
											else {
												form.getEvnStickSignStatus({object: SignObject});
											}
										}.createDelegate(this),
										icon: Ext.Msg.INFO,
										msg: 'Документ подписан',
										title: 'Верификация'
									});
								} else {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										fn: function() {
											params.Signatures_id = result.Signatures_id;
											params.SignaturesStatus_id = 3;
											Ext.Ajax.request({
												url: '/?c=Stick&m=setSignStatus',
												params: params,
												success: function(response, options) {
													if ( ! SignObject.inlist(['leave', 'irr'])) {
														me._reload_WorkRelease();
													}
													else {
														form.getEvnStickSignStatus({object: SignObject});
													}
												}
											});
										}.createDelegate(this),
										icon: Ext.Msg.INFO,
										msg: 'Документ не актуален',
										title: 'Верификация'
									});
								}
							}
						});
					}
				}
			}
		});
	},

	
	
	/**
	 * Получаем Grid списка
	 * @returns {*|Ext.Component}
	 * @private
	 */
	_get_WorkReleaseGrid: function(){
		__l('_get_WorkReleaseGrid');
		var me = this;
		return this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid');
	},

	/**
	 * Получаем количество записей в списке
	 * @returns {*}
	 * @private
	 */
	_getCount_WorkRelease: function(){
		__l('_getCount_WorkRelease');
		return this.findById(this.id+'EStEF_EvnStickWorkReleaseGrid').getStore().getCount();
	},

	/**
	 * Очищяем список
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_removeAll_WorkRelease: function(){
		__l('_removeAll_WorkRelease');
		var me = this;
		me.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getStore().removeAll();
		this.panelEvnStickWorkRelease.isLoaded = false;

		return me;
	},

	/**
	 * Добавляем в список пустую строку, не знаю зачем
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_addEmpty_WorkRelease: function(){
		__l('_addEmpty_WorkRelease');
		var me = this;
		LoadEmptyRow(me.findById(me.id+'EStEF_EvnStickWorkReleaseGrid'));

		return me;
	},

	/**
	 * Загружаем данные с параметрами по умолчанию
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_defaultLoad_WorkRelease: function(){
		__l('_defaultLoad_WorkRelease');
		var me = this;
		var base_form = me.FormPanel.getForm();

		var evn_stick_dop_pid = me.FormPanel.getForm().findField('EvnStickDop_pid').getValue();
		if(Ext.isEmpty(evn_stick_dop_pid)){
			evn_stick_dop_pid = null;
		}


		me._load_WorkRelease({
			EvnStick_id:  me.FormPanel.getForm().findField('EvnStick_id').getValue(),
			EvnStickDop_pid: evn_stick_dop_pid,
			StickWorkType_id: base_form.findField('StickWorkType_id').getValue()
		}, function(){

			me.panelEvnStickWorkRelease.isLoaded = true;

			if (Ext.isEmpty(me.FormPanel.getForm().findField('EvnStick_disDate').getValue())) {
				me.setEvnStickDisDate();
			}
		});


		return me;

	},

	/**
	 * Загружаем данные с переданными параметрами
	 * @param params
	 * @param callback
	 * @private
	 */
	_load_WorkRelease: function(params, callback){
		__l('_load_WorkRelease');
		var me = this;
		this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getStore().load({
			params: params,
			callback: callback
		})
	},

	/**
	 * Загружаем данные повторно
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_reload_WorkRelease: function(){
		__l('_reload_WorkRelease');
		this.findById(this.id+'EStEF_EvnStickWorkReleaseGrid').getStore().reload();
	},

	/**
	 * Проверяем загружены ли периоды освобождений от работы
	 * @returns bool (true - данные загружены, false - данные не загружены)
	 * @private
	 */
	_isLoaded_WorkRelease: function(){
		__l('_isLoaded_WorkRelease');

		// загружаем данные только если список пустой
		if(this.panelEvnStickWorkRelease.isLoaded == true){
			return true;
		}

		return false;
	},

	/**
	 * Фокус на первую запись списка
	 * @returns {sw.Promed.swEvnStickEditWindow}
	 * @private
	 */
	_focusFirst_WorkRelease: function(){
		__l('_focusFirst_WorkRelease');

		this.findById(this.id+'EStEF_EvnStickWorkReleaseGrid').getView().focusRow(0);
		this.findById(this.id+'EStEF_EvnStickWorkReleaseGrid').getSelectionModel().selectFirstRow();
	},

	/**
	 * Получаем выделенную запись в списке
	 * @returns {*}
	 * @private
	 */
	_getSelected_WorkRelease: function(){
		__l('_getSelected_WorkRelease');
		var me = this;
		var grid = this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid');
		return grid.getSelectionModel().getSelected();
	},

	/**
	 * хз что за функция 1, нужно разобраться
	 * @private
	 */
	_func1GridEvnStickWorkRelease: function(record){
		__l('_func1GridEvnStickWorkRelease');
		var me = this;
		var win = this;
		var base_form = this.FormPanel.getForm();

		var stick_workrelease_grid = this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid');

		var prev_records = getStoreRecords(
			stick_workrelease_grid.getStore(),
			{
				clearFilter: true
			}
		);

		var params = {
			EvnStick_id: record.get('EvnStick_id'),
			StickWorkType_id: base_form.findField('StickWorkType_id').getValue()
		}
		// если добавляется дубликат
		if(me.action == 'add' && base_form.findField('EvnStick_IsOriginal').getValue() == 2) {
			params.LoadSummPeriod = '1';
		}
		
		//если добавляется ЛВН по совместительству
		if( me.action == 'add' && base_form.findField('StickWorkType_id').getValue() == 2 ) {
			params.ignoreRegAndPaid = '1';
		}
		stick_workrelease_grid.getStore().load({
			params: params,
			callback: function() {
				me.setEvnStickDisDate();
				stick_workrelease_grid.getStore().each(function(record){

					record.set('EvnStickWorkRelease_id', -swGenTempId(stick_workrelease_grid.getStore()));

					// Устанавливаем даннные о подписании
					record.set('SMPStatus_id', 2);
					record.set('Signatures_mid', null);
					record.set('SMP_Status_Name', null);
					record.set('SMP_updDT', null);
					record.set('SMP_updUser_Name', null);
					record.set('SVKStatus_id', 2);
					record.set('Signatures_wid', null);
					record.set('SVK_Status_Name', null);
					record.set('SVK_updDT', null);
					record.set('SVK_updUser_Name', null);
					record.set('EvnStickWorkRelease_IsInReg', null);
					record.set('EvnStickWorkRelease_IsPaid', null);

					// Usually called by the Ext.data.Store which owns the Record. Commits all changes made to the Record since either creation, or the last commit operation.
					// Developers should subscribe to the Ext.data.Store.update event to have their code notified of commit operations.
					record.commit();
				});

				prev_records.forEach(function(record) {
					var index = stick_workrelease_grid.getStore().find('EvnStickWorkRelease_id', record.EvnStickWorkRelease_id);
					if (record.RecordStatus_Code != 0 && record.EvnStickBase_id == base_form.findField('EvnStick_id').getValue() && index < 0) {
						record.RecordStatus_Code = 3;
						stick_workrelease_grid.getStore().loadData([record], true);
					}
				});
				stick_workrelease_grid.getStore().filterBy(function(record) {
					return record.get('RecordStatus_Code') != 3;
				});

				win.refreshFormPartsAccess();
			}.createDelegate(this)
		});
	},

	/**
	 * хз что за функция 2, нужно разобраться
	 * @private
	 */
	_func2GridEvnStickWorkRelease: function(){
		__l('_func2GridEvnStickWorkRelease');
		var me = this;
		var win = this;
		var base_form = this.FormPanel.getForm();

		var stick_workrelease_grid = this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid');
		stick_workrelease_grid.getStore().each(function(record) {
			if (record.get('RecordStatus_Code') != 0 && record.get('EvnStickBase_id') == base_form.findField('EvnStick_id').getValue()) {
				record.set('RecordStatus_Code', 3);
				record.commit();
			} else {
				stick_workrelease_grid.getStore().remove(record);
			}
		});
		stick_workrelease_grid.getStore().filterBy(function(record) {
			return record.get('RecordStatus_Code') != 3;
		});
	},

	/**
	 * получаем сумму освобождений из предыдущих ЛВН
	 * @param EvnStick_id
	 */
	getWorkReleaseSumm: function(EvnStick_id) {
		__l('getWorkReleaseSumm');
		var me = this;
		var base_form = this.FormPanel.getForm();

		Ext.Ajax.request({
			url: '/?c=Stick&m=getWorkReleaseSumPeriod',
			params: {
				'EvnStick_id': EvnStick_id
			},
			callback: function(opt, success, response) {
				if (success && response.responseText.length > 0) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						base_form.findField('WorkReleaseSumm').setValue(result.WorkReleaseSumm);
					}
				}
			}
		});
	},
	// =================================================================================================================






	// =================================================================================================================
	// 5. Исход ЛВН

	// Флаг доступа к блоку
	isAccessToStickLeave: false,

	/**
	 * Проверяем доступность блока
	 * @returns {boolean} false - доступ закрыт, true - доступ открыт
	 * @private
	 */
	_checkAccessToPanelStickLeave: function() {
		__l('_checkAccessToPanelStickLeave');

		var me = this;
		var base_form = this.FormPanel.getForm();

		// Флаг открытия
		var isOpen = false;

		// Оператор
		if (me.isOperator() == true) {
			// ---------------------------------------------------------------------------------------------------------
			// Условие 1
			//
			// 1. ЛВН без признака «В реестре» И без признака «Принят ФСС»
			var checkUslovie1 = false;
			if (!me.isPaid && !me.isInReg) {
				checkUslovie1 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			// ---------------------------------------------------------------------------------------------------------
			// Условие 2
			//
			// 2. В зависимости от наличия исхода ЛВН:
			// 		2.1 Если ЭЛН оригинал и Исход ЛВН указан:
			// 			2.1.1 Исход ЛВН указан врачом МО пользователя, и
			// 			2.1.2 Не указан ЛВН-продолжение.
			// 		2.2 Если ЭЛН дубликат и исход ЭЛН указан (подтянулся из оригинала):
			// 			2.2.1 Дубликат оформлен в МО Пользователя
			// 		2.3 Если ЭЛН дубликат и исход ЭЛН указан (в оригинале не было данных об исходе):
			// 			2.3.1 Исход ЛВН указан в МО Пользователя
			// 		2.4 Исход ЛВН не указан (дополнительных условий нет.)
			var checkUslovie2 = false;

			// Исход ЛВН (почему определяется именно так, я хз!)
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

			// Исход ЛВН указан
			if (!Ext.isEmpty(MedStaffFact_id)) {

				// 2.1 Если ЭЛН оригинал
				if (base_form.findField('EvnStick_IsOriginal').getValue() != 2) {
					if (
						// исход ЛВН указан врачом МО пользователя
						getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id') &&

						// не указан ЛВН-продолжение
						Ext.isEmpty(base_form.findField('EvnStick_NumNext').getValue())
					) {
						checkUslovie2 = true;
					}
				}

				// 2.2 Если ЭЛН дубликат
				if (base_form.findField('EvnStick_IsOriginal').getValue() == 2) {
					var record = me._getSelectedRecord_EvnStick_oid();
					if (record) {
						var StickLeaveType_id = record.get('StickLeaveType_id');

						// исход ЭЛН подтянулся из оригинала
						if (!Ext.isEmpty(StickLeaveType_id)) {
							if (me.Lpu_id == getGlobalOptions().lpu_id) {
								checkUslovie2 = true;
							}

							// в оригинале не было данных об исходе
						} else {
							if (getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id')) {
								checkUslovie2 = true;
							}
						}
					}
				}


				// Исход ЛВН не указан
			} else if (Ext.isEmpty(MedStaffFact_id)) {
				checkUslovie2 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			if (checkUslovie1 && checkUslovie2) {
				isOpen = true;
			}


		}

		// Статистик
		if (me.isStatistick() == true) {
			// ---------------------------------------------------------------------------------------------------------
			// Условие 1
			//
			// 1. ЛВН без признака «В реестре» И без признака «Принят ФСС»
			var checkUslovie1 = false;
			if (!me.isPaid && !me.isInReg) {
				checkUslovie1 = true;
			}


			// ---------------------------------------------------------------------------------------------------------
			// Условие 2
			//
			// 2. В зависимости от наличия исхода ЛВН:
			// 		2.1 Если ЭЛН оригинал и Исход ЛВН указан:
			// 			2.1.1 Исход ЛВН указан врачом МО пользователя, и
			// 			2.1.2 Не указан ЛВН-продолжение.
			// 		2.2 Если ЭЛН дубликат и исход ЭЛН указан (подтянулся из оригинала):
			// 			2.2.1 Дубликат оформлен в МО Пользователя
			// 		2.3 Если ЭЛН дубликат и исход ЭЛН указан (в оригинале не было данных об исходе):
			// 			2.3.1 Исход ЛВН указан в МО Пользователя
			// 		2.4 Исход ЛВН не указан (дополнительных условий нет.)
			var checkUslovie2 = false;

			// Исход ЛВН (почему определяется именно так, я хз!)
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

			// Исход ЛВН указан
			if (!Ext.isEmpty(MedStaffFact_id)) {

				// 2.1 Если ЭЛН оригинал
				if (base_form.findField('EvnStick_IsOriginal').getValue() != 2) {
					if (
						// исход ЛВН указан врачом МО пользователя
						getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id') &&

						// не указан ЛВН-продолжение
						Ext.isEmpty(base_form.findField('EvnStick_NumNext').getValue())
					) {
						checkUslovie2 = true;
					}
				}

				// 2.2 Если ЭЛН дубликат
				if (base_form.findField('EvnStick_IsOriginal').getValue() == 2) {
					var record = me._getSelectedRecord_EvnStick_oid();
					if (record) {
						var StickLeaveType_id = record.get('StickLeaveType_id');

						// исход ЭЛН подтянулся из оригинала
						if (!Ext.isEmpty(StickLeaveType_id)) {
							if (me.Lpu_id == getGlobalOptions().lpu_id) {
								checkUslovie2 = true;
							}

							// в оригинале не было данных об исходе
						} else {
							if (getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id')) {
								checkUslovie2 = true;
							}
						}
					}
				}


				// Исход ЛВН не указан
			} else if (Ext.isEmpty(MedStaffFact_id)) {
				checkUslovie2 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			if (checkUslovie1 && checkUslovie2) {
				isOpen = true;
			}

		}

		// Регистратор ЛВН
		if (me.isRegistratorLVN() == true) {
			// ---------------------------------------------------------------------------------------------------------
			// Условие 1
			//
			// 1. ЛВН без признака «В реестре» И без признака «Принят ФСС»
			var checkUslovie1 = false;
			if (!me.isPaid && !me.isInReg) {
				checkUslovie1 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			// ---------------------------------------------------------------------------------------------------------
			// Условие 2
			//
			// 2. В зависимости от наличия исхода ЛВН:
			// 		2.1 Если ЭЛН оригинал и Исход ЛВН указан:
			// 			2.1.1 Исход ЛВН указан врачом МО пользователя, и
			// 			2.1.2 Не указан ЛВН-продолжение.
			// 		2.2 Если ЭЛН дубликат и исход ЭЛН указан (подтянулся из оригинала):
			// 			2.2.1 Дубликат оформлен в МО Пользователя
			// 		2.3 Если ЭЛН дубликат и исход ЭЛН указан (в оригинале не было данных об исходе):
			// 			2.3.1 Исход ЛВН указан в МО Пользователя
			// 		2.4 Исход ЛВН не указан (дополнительных условий нет.)
			var checkUslovie2 = false;

			// Исход ЛВН (почему определяется именно так, я хз!)
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

			// Исход ЛВН указан
			if (!Ext.isEmpty(MedStaffFact_id)) {

				// 2.1 Если ЭЛН оригинал
				if (base_form.findField('EvnStick_IsOriginal').getValue() != 2) {
					if (
						// исход ЛВН указан врачом МО пользователя
						getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id') &&

						// не указан ЛВН-продолжение
						Ext.isEmpty(base_form.findField('EvnStick_NumNext').getValue())
					) {
						checkUslovie2 = true;
					}
				}

				// 2.2 Если ЭЛН дубликат
				if (base_form.findField('EvnStick_IsOriginal').getValue() == 2) {
					var record = me._getSelectedRecord_EvnStick_oid();
					if (record) {
						var StickLeaveType_id = record.get('StickLeaveType_id');

						// исход ЭЛН подтянулся из оригинала
						if (!Ext.isEmpty(StickLeaveType_id)) {
							if (me.Lpu_id == getGlobalOptions().lpu_id) {
								checkUslovie2 = true;
							}

							// в оригинале не было данных об исходе
						} else {
							if (getGlobalOptions().lpu_id == base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id')) {
								checkUslovie2 = true;
							}
						}
					}
				}


				// Исход ЛВН не указан
			} else if (Ext.isEmpty(MedStaffFact_id)) {
				checkUslovie2 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			if (checkUslovie1 && checkUslovie2) {
				isOpen = true;
			}

		}


		// Регистратор
		if (me.isRegistrator() == true) {
			//
		}


		// Врач
		if (me.isVrach() == true) {
			// ---------------------------------------------------------------------------------------------------------
			// Условие 1
			//
			// 1. ЛВН без признака «В реестре» И нет признака «Принят ФСС».
			var checkUslovie1 = false;
			if (!me.isPaid && !me.isInReg) {
				checkUslovie1 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			// ---------------------------------------------------------------------------------------------------------
			// Условие 2
			//
			// 2. В зависимости от наличия исхода ЛВН
			// 		2.1 Если ЭЛН оригинал и исход ЛВН указан:
			// 			2.1.1 исход ЛВН указан этим врачом, и
			// 			2.1.2 не указан ЛВН-продолжение.
			// 		2.2 Если ЭЛН дубликат и исход ЭЛН указан (подтянулся из оригинала):
			// 			2.2.1 Дубликат оформлен текущим врачом
			// 		2.3 Если ЭЛН дубликат и исход ЭЛН указан (в оригинале не было данных об исходе):
			// 			2.3.1 Исход ЛВН указан текущим врачом
			// 		2.4 Исход ЛВН не указан:
			// 			2.4.1 дополнительных условий нет.
			var checkUslovie2 = false;

			// Исход ЛВН (почему определяется именно так, я хз!) по идее это врач указавший исход
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
			if (!Ext.isEmpty(MedStaffFact_id)) {

				// 2.1 Если ЭЛН оригинал
				if (base_form.findField('EvnStick_IsOriginal').getValue() != 2) {

					// 2.1.1 исход ЛВН указан этим врачом, и
					// 2.1.2 не указан ЛВН-продолжение.
					if (
						// исход ЛВН указан этим врачом
						getGlobalOptions().medpersonal_id == base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') &&

						// не указан ЛВН-продолжение
						Ext.isEmpty(base_form.findField('EvnStick_NumNext').getValue())
					) {
						checkUslovie2 = true;
					}

				}


				// 2.2 Если ЭЛН дубликат
				if (base_form.findField('EvnStick_IsOriginal').getValue() == 2) {

					var record = me._getSelectedRecord_EvnStick_oid();
					var StickLeaveType_id_fromOriginal = null;
					if (record) {
						StickLeaveType_id_fromOriginal = record.get('StickLeaveType_id');
					}


					// исход ЭЛН указан (подтянулся из оригинала)
					// видимо когда мы выбираем оригинал при создании ЛВН
					// я думаю что это только при создании ЛВН, проверить что будет при редактировании если выбрать
					// оригинал с исходом, что будет с сохраненным исходом, замениться ли он
					if (!Ext.isEmpty(StickLeaveType_id_fromOriginal)) {

						// 2.2.1 Дубликат оформлен текущим врачом
						if (me.action == 'add' || base_form.findField('pmUser_insID').getValue() == getGlobalOptions().pmuser_id) {
							checkUslovie2 = true;
						}
					}


					// исход ЭЛН указан (в оригинале не было данных об исходе)
					// логически данная ситуация возможна только при редактировании ЛВН т.к. при создании исход будет
					// пустым
					if (!Ext.isEmpty(StickLeaveType_id_fromOriginal)) {
						

						// 2.3.1 Исход ЛВН указан текущим врачом
						// узнать как сохраняется врач указавший исход
						// В ИТОГЕ видимо врач указавший исход указывает себя в поле врач ниже "MedStaffFact_id" (смотри поле StickLeaveType_id событие select)
						if (me.checkMedPersonalInStickLeave()) {
							checkUslovie2 = true;
						}
					}

				}


				// Исход ЛВН не указан
			} else {
				checkUslovie2 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			if (checkUslovie1 == true && checkUslovie2 == true) {
				isOpen = true;
			}

		}

		// Врач, регистратор (одновременно)
		if (me.isVrachAndRegistrator() == true) {
			// ---------------------------------------------------------------------------------------------------------
			// Условие 1
			//
			// 1. ЛВН без признака «В реестре» И нет признака «Принят ФСС».
			var checkUslovie1 = false;
			if (!me.isPaid && !me.isInReg) {
				checkUslovie1 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			// ---------------------------------------------------------------------------------------------------------
			// Условие 2
			//
			// 2. В зависимости от наличия исхода ЛВН
			// 		2.1 Если ЭЛН оригинал и исход ЛВН указан:
			// 			2.1.1 исход ЛВН указан этим врачом, и
			// 			2.1.2 не указан ЛВН-продолжение.
			// 		2.2 Если ЭЛН дубликат и исход ЭЛН указан (подтянулся из оригинала):
			// 			2.2.1 Дубликат оформлен текущим врачом
			// 		2.3 Если ЭЛН дубликат и исход ЭЛН указан (в оригинале не было данных об исходе):
			// 			2.3.1 Исход ЛВН указан текущим врачом
			// 		2.4 Исход ЛВН не указан:
			// 			2.4.1 дополнительных условий нет.
			var checkUslovie2 = false;

			// Исход ЛВН (почему определяется именно так, я хз!) по идее это врач указавший исход
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
			if (!Ext.isEmpty(MedStaffFact_id)) {

				// 2.1 Если ЭЛН оригинал
				if (base_form.findField('EvnStick_IsOriginal').getValue() != 2) {

					// 2.1.1 исход ЛВН указан этим врачом, и
					// 2.1.2 не указан ЛВН-продолжение.
					if (
						// исход ЛВН указан этим врачом
						getGlobalOptions().medpersonal_id == base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') &&

						// не указан ЛВН-продолжение
						Ext.isEmpty(base_form.findField('EvnStick_NumNext').getValue())
					) {
						checkUslovie2 = true;
					}

				}


				// 2.2 Если ЭЛН дубликат
				if (base_form.findField('EvnStick_IsOriginal').getValue() == 2) {

					var record = me._getSelectedRecord_EvnStick_oid();
					var StickLeaveType_id_fromOriginal = null;
					if (record) {
						StickLeaveType_id_fromOriginal = record.get('StickLeaveType_id');
					}


					// исход ЭЛН указан (подтянулся из оригинала)
					// видимо когда мы выбираем оригинал при создании ЛВН
					// я думаю что это только при создании ЛВН, проверить что будет при редактировании если выбрать
					// оригинал с исходом, что будет с сохраненным исходом, замениться ли он
					if (!Ext.isEmpty(StickLeaveType_id_fromOriginal)) {

						// 2.2.1 Дубликат оформлен текущим врачом (то есть если МО врача выбранного в исходе совпадает с МО Пользователя)
						// узнать как сохраняется врач создавший дубликат
						if (me.checkMedPersonalInStickLeave()) {
							checkUslovie2 = true;
						}
					}


					// исход ЭЛН указан (в оригинале не было данных об исходе)
					// логически данная ситуация возможна только при редактировании ЛВН т.к. при создании исход будет
					// пустым
					if (Ext.isEmpty(StickLeaveType_id_fromOriginal)) {

						// 2.3.1 Исход ЛВН указан текущим врачом
						// узнать как сохраняется врач указавший исход
						// В ИТОГЕ видимо врач указавший исход указывает себя в поле врач ниже "MedStaffFact_id" (смотри поле StickLeaveType_id событие select)
						if (me.checkMedPersonalInStickLeave()) {
							checkUslovie2 = true;
						}
					}

				}


				// Исход ЛВН не указан
			} else {
				checkUslovie2 = true;
			}
			// ---------------------------------------------------------------------------------------------------------


			if (checkUslovie1 == true && checkUslovie2 == true) {
				isOpen = true;
			}

		}

		return isOpen;
	},
	/**
	 * Закрываем доступ + ставим флаг
	 *
	 * @returns {boolean}
	 * @private
	 */
	_closeAccessToPanelStickLeave: function(){
		__l('_closeAccessToPanelStickLeave');
		var me = this;

		me.isAccessToStickLeave = false;
		me.getFields(me.id+'EStEF_StickLeavePanel').forEach(me.disableField);

		return true;
	},
	/**
	 * Открываем доступ + ставим флаг
	 *
	 * ВАЖНО!!!!!!!!!!!!!!!!!!!!!! - Не забываем что при открытии блока мы так же проверяем доступность каждого
	 * поля в методе checkFieldDisabled()
	 *
	 * @returns {boolean}
	 * @private
	 */
	_openAccessToPanelStickLeave: function(){
		__l('_openAccessToPanelStickLeave');
		var me = this;

		me.isAccessToStickLeave = true;
		me.getFields(me.id+'EStEF_StickLeavePanel').forEach(me.enableField);

		return true;
	},



	// -----------------------------------------------------------------------------------------------------------------
	// Кнопка "Подписать" в блоке "Исход ЛВН"

	// Флаг доступа
	isAccessToField_StickLeave_Sign: false, // false - доступ закрыт, true - доступ открыт
	_closeAccessToField_StickLeave_Sign: function(){
		__l('_closeAccessToField_StickLeave_Sign');

		var me = this;

		me.isAccessToField_StickLeave_Sign = false;
		me.findById('swSignStickLeave').disable();

		return me;
	},
	_openAccessToField_StickLeave_Sign: function(){
		__l('_openAccessToField_StickLeave_Sign');
		var me = this;

		me.isAccessToField_StickLeave_Sign = true;
		me.findById('swSignStickLeave').enable();

		return me;
	},
	_checkAccessToField_StickLeave_Sign: function(){
		__l('_checkAccessToField_StickLeave_Sign');

		var me = this;
		var isOpen = false;
		var base_form = this.FormPanel.getForm();

		// Оператор
		if(me.isOperator() == true){
			// Доступ не открываем
		}

		// Статистик
		if(me.isStatistick() == true){
			// Доступ не открываем
		}

		// Регистратор ЛВН
		if(me.isRegistratorLVN() == true){
			// Доступ не открываем
		}

		// Регистратор
		if(me.isRegistrator() == true){
			// Доступ не открываем
		}

		// Врач
		if(me.isVrach() == true){

			var checkUslovie1 = false;
			// Врач, указан в качестве врача, установившего исход
			if (me.checkMedPersonalInStickLeave()) {
				checkUslovie1 = true;
			}

			var checkUslovie2 = false;
			// Если ЭЛН дубликат, то дубликат оформлен текущим врачом
			if (base_form.findField('EvnStick_IsOriginal').getValue() == 2 && me.checkMedPersonalInStickLeave()) {
				checkUslovie2 = true;
			}

			if (checkUslovie1 == true || checkUslovie2 == true) {
				isOpen = true;
			}

		}

		if(me.isVrachVK() == true){
			// Доступ не открываем #145565
		}

		// Врач, регистратор (одновременно)
		if(me.isVrachAndRegistrator() == true){
			// Доступ не открываем
		}


		return isOpen;
	},
	// прорверка соответсвия врача в исходе текущему врачу
	checkMedPersonalInStickLeave: function() {
		var base_form = this.FormPanel.getForm();
		// врач совпадает
		if (base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id') == getGlobalOptions().medpersonal_id) {
			return true;
		}

		var Lpu_id = base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id');
		// при сравнении врача, указанного в ЛВН и врача пользователя, если человек совпадает (Person_id), но не совпадает МО (MedPersonal_id)
		// то проверяется, если МО пользователя является правопреемником МО врача указанного в ЛВН, то считается, что врач совпадает
		if (
			base_form.findField('MedStaffFact_id').getFieldValue('Person_id') == getGlobalOptions().person_id
			&& typeof getGlobalOptions().linkedLpuIdList == 'object'
			&& !Ext.isEmpty(Lpu_id)
			&& Lpu_id.inlist(getGlobalOptions().linkedLpuIdList)
		) {
			return true;
		}

		return false;
	},
	// -----------------------------------------------------------------------------------------------------------------


	// проверка, что в ЛВН есть хотя бы один добавленный период освобождения от работы
	hasWorkRelease: function() {
		var hasWorkRelease = false;
		this.findById(this.id + 'EStEF_EvnStickWorkReleaseGrid').getStore().each(function(rec) {
			if (rec && rec.get('EvnStickWorkRelease_id')) {
				hasWorkRelease = true;
			}
		});

		return hasWorkRelease;
	},
	// Устанавливаем "Дата исхода ЛВН"
	// «Дата исхода ЛВН» рассчитывается исходя из существующих в ЛВН по совместительству освобождениях от работы
	setEvnStickDisDate: function() {
		__l('setEvnStickDisDate');
		var me = this;
		var base_form = this.FormPanel.getForm();
		var StickLeaveType_Code = base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code');
		if (!StickLeaveType_Code) return false;
		StickLeaveType_Code = StickLeaveType_Code.toString();
		// Получаем дату окончания последнего освобождения от работы
		var evn_stick_work_release_end_date = null;
		var evn_stick_work_release_store = this.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getStore();
		var evn_stick_dis_date = base_form.findField('EvnStick_disDate').getValue();

		evn_stick_work_release_store.each(function(record) {
			if ( evn_stick_work_release_end_date == null || record.get('EvnStickWorkRelease_endDate') > evn_stick_work_release_end_date ) {
				evn_stick_work_release_end_date = record.get('EvnStickWorkRelease_endDate');
			}
		});

		switch ( StickLeaveType_Code ) {
			case '01':
				if ( evn_stick_work_release_end_date ) {
					base_form.findField('EvnStick_disDate').setValue(evn_stick_work_release_end_date.add(Date.DAY, 1));
				}
				break;

			case '32':
				if ( base_form.findField('EvnStick_mseExamDate').getValue() ) {
					base_form.findField('EvnStick_disDate').setValue(base_form.findField('EvnStick_mseExamDate').getValue());
				}
				else if ( evn_stick_dis_date ) {
					base_form.findField('EvnStick_disDate').setValue(evn_stick_dis_date);
				}
				break;

			case '31':
			case '35':
			case '36':
			case '37':
			case 'W.8':
				if ( evn_stick_work_release_end_date ) {
					// base_form.findField('EvnStick_disDate').setValue(evn_stick_work_release_end_date.add(Date.DAY, 1));
					base_form.findField('EvnStick_disDate').setValue(evn_stick_work_release_end_date);
				}
				else if ( evn_stick_dis_date ) {
					base_form.findField('EvnStick_disDate').setValue(evn_stick_dis_date);
				}
				break;

			case '33':
				// Изменена группа инвалидности
				break;

			case '34':
				// Дата смерти
				break;
		}
	},

	// Получаем и устанавливаем значение для поля "ЛВН-продолжение"
	fetchAndSetEvnStickProd: function(EvnStick_oid){
		__l('fetchAndSetEvnStickProd');
		var me = this;

		var base_form = this.FormPanel.getForm();

		me.getEvnStickProdValues(EvnStick_oid, function(result) {
			var EvnStick_NumNext = base_form.findField('EvnStick_NumNext').getValue();

			if(Ext.isEmpty(EvnStick_NumNext)){
				base_form.findField('EvnStick_NumNext').setValue(result.EvnStick_Title);
			}
		});

		


		return true;
	},


	doSign_StickLeave: function(options){
		__l('doSign_StickLeave');

		if (typeof options != 'object') {
			options = new Object();
		}

		var me = this;
		var base_form = me.FormPanel.getForm();


		//
		if(getRegionNick() != 'kz'){
			if( ! options.ignoreStickLeaveType){
				// StickLeaveType_id 31 or 37
				var StickLeaveType_Code = base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code');
				var EvnStick_NumNext = base_form.findField('EvnStick_NumNext').getValue();

				// поле «Исход ЛВН» имеет значение «31» («Продолжает болеть») или «37» («Долечивание»)
				if(StickLeaveType_Code == 31 || StickLeaveType_Code == 37){
					// EvnStick_NumNext is empty
					if(Ext.isEmpty(EvnStick_NumNext)){
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {

								// –	При выборе варианта «Да» выполняется подписание исхода ЛВН.
								if ( 'yes' == buttonId ) {

									options.ignoreStickLeaveType = true;
									me.doSign_StickLeave(options);
								// –	При выборе варианта «Нет», сообщение закрывается. Подписание не производится.
								} else {
									// ничего не делаем
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Для успешной сдачи в ФСС ЛВН с исходом «Продолжает болеть» и «Долечивание» рекомендуется подписывать после создания ЛВН-продолжения. Всё равно выполнить подписание?'),
							title: langs('Вопрос')
						});

						return false;
					}
				}
			}
		}




		if (!options.ignoreSave && me.action != 'view') {
			// предварительно всегда сохраняем весь ЛВН.
			options.ignoreSave = true;
			me.doSave({
				ignoreSignatureCheck: true,
				callback: function () {
					me.doSign_StickLeave(options);
				}
			});
			return false;
		}

		var params = {};
		params.SignObject = 'leave';
		params.Evn_id = base_form.findField('EvnStick_id').getValue();
		params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();

		me._doSign(getOthersOptions().doc_signtype, params);

	},




	// -----------------------------------------------------------------------------------------------------------------
	// StickLeaveType_id

	_isFocusAccess_StickLeaveType_id: function(){
		var me = this;
		var base_form = this.FormPanel.getForm();
		if(
			! this.findById(me.id+'EStEF_StickLeavePanel').collapsed &&
			! base_form.findField('StickLeaveType_id').disabled
		) {
			return true;
		}

		return false;
	},
	_focus_StickLeaveType_id: function(){
		var me = this;
		var base_form = this.FormPanel.getForm();
		base_form.findField('StickLeaveType_id').focus(true);
	},
	// -----------------------------------------------------------------------------------------------------------------

	// =================================================================================================================









	// =================================================================================================================
	// Кнопки
	// =================================================================================================================



	// -----------------------------------------------------------------------------------------------------------------
	// Кнопка "Отмена"
	// -----------------------------------------------------------------------------------------------------------------

	// Скрыть форму
	doHideForm: function(){
		__l('doHideForm');
		var me = this;

		me.hide();

		return me;
	},
	_focusButtonCancel: function(){
		var me = this;
		Ext.getCmp(me.id + 'buttonCancel').focus();
	},
	// -----------------------------------------------------------------------------------------------------------------







	// -----------------------------------------------------------------------------------------------------------------
	// Кнопка "Сохранить"
	// -----------------------------------------------------------------------------------------------------------------

	_focusButtonSave: function(){
		var me = this;

		Ext.getCmp(me.id + 'buttonSave').focus();

		return me;
	},
	_enableButtonSave: function(){
		var me = this;
		Ext.getCmp(me.id + 'buttonSave').enable();

		return me;
	},
	_disableButtonSave: function(){
		var me = this;
		Ext.getCmp(me.id + 'buttonSave').disable();

		return me;
	},
	// Сохранение
	doSave: function(options) {
		__l('doSave');
		// options @Object
		// options.ignoreSetDateError @Boolean Игнорировать проверку даты выдачи ЛВН и даты начала первого освобождения от работы
		// options.ignoreSetDateDieError @Boolean Игнорировать проверку (даты ЛВН = даты КВС и исхода, если исход = умер)
		// options.ignoreSetDateDopError @Boolean Игнорировать сравнение даты выдачи основного и дополнительного ЛВН
		// options.ignoreEvnStickContQuestion @Boolean Игнорировать вопрос на выписку продолжения ЛВН
		// options.ignorePregnancyReleasePeriodQuestion @Boolean Игнорировать вопрос о сроке освобождения от работы (для случая, когда ЛВН выдан по беременности и родам)
		// options.ignoreLeaveTypeErrors @Boolean Игнорировать ошибки, связанные с выбранным значением поля "Исход ЛВН"
		// options.print @Boolean Вызывать печать ЛВН, если true

		var me = this;
		var base_form = me.FormPanel.getForm();

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';
		var win = this;

		var advanceParams = this.advanceParams;
		var base_form = this.FormPanel.getForm();
		var form = this.FormPanel;
		var evn_stick_care_person_grid = this.findById(win.id+'EStEF_EvnStickCarePersonGrid');
		var evn_stick_work_release_grid = this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid');
		var EvnStick_Num_Field = base_form.findField('EvnStick_Num');

		if (EvnStick_Num_Field.getValue() && this.checkEvnStickNumDouble() && typeof(EvnStick_Num_Field.validator) == 'function') {
			sw.swMsg.alert(langs('Ошибка'), EvnStick_Num_Field.validator());
			this.formStatus = 'edit';
			return false;
		}

		// подсчёт количества дней освобождения от работы
		var EvnStickWorkRelease_SumDate = 0;
		evn_stick_work_release_grid.getStore().each(function(record) {
			if ( record && record.get('EvnStickWorkRelease_endDate') != '' ) {
				EvnStickWorkRelease_SumDate += Math.round((record.get('EvnStickWorkRelease_endDate') - record.get('EvnStickWorkRelease_begDate')) / 86400000) + 1;
			}
		});

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: this.FormPanel.getInvalidFieldsMessage(),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( !EvnStick_Num_Field.getValue() && !EvnStick_Num_Field.allowBlank ) {
			sw.swMsg.alert(langs('Ошибка'), 'Поле "Номер" обязательно для заполнения');
			this.formStatus = 'edit';
			return false;
		}

		if (!this.checkSaveButtonEnabled()) {
			// Сохраненеие не возможно
			sw.swMsg.alert(langs('Ошибка'), 'Должно быть заполнено хотя бы одно освобождение от работы или исход ЛВН');
			this.formStatus = 'edit';
			return false;
		}


		if (
			! Ext.isEmpty(base_form.findField('EvnStickBase_consentDT').getValue())
			&& ! Ext.isEmpty(base_form.findField('EvnStick_setDate').getValue())
			&& base_form.findField('EvnStick_setDate').getValue() >= new Date('2019-2-22') // отключаем контроль если выдан раньше 22.02.2019 
			&& base_form.findField('EvnStick_setDate').getValue() < base_form.findField('EvnStickBase_consentDT').getValue()
			&& base_form.findField('EvnStick_IsOriginal').getValue() != 2
			&& !win.checkIsLvnFromFSS()
		) {
			sw.swMsg.alert(langs('Ошибка'), 'Согласие не может быть получено после выдачи ЛВН.');
			this.formStatus = 'edit';
			return false;
		}

		var isVK = (Ext.getCmp(win.id+'EStEF_EvnStickWorkReleaseGrid').getStore().findBy(function(rec){
			return rec.get('EvnStickWorkRelease_IsPredVK');
		})) != -1;
		
		if(
			!Ext.isEmpty(base_form.findField('EvnStick_disDate').getValue())
			&& !Ext.isEmpty(base_form.findField('EvnStick_setDate').getValue())
			&& !Ext.isEmpty(base_form.findField('EvnStickBase_consentDT').getValue())
			&& !isVK 
			&& base_form.findField('EvnStick_IsOriginal').getValue() != 2
			&& base_form.findField('EvnStick_disDate').getValue() < base_form.findField('EvnStick_setDate').getValue()
		) {
			sw.swMsg.alert(langs('Ошибка'), 'Выдача ЛВН за прошедшие дни возможна только по решению врачебной комиссии.');
			this.formStatus = 'edit';
			return false;
		}

		if (
			! base_form.findField('EvnStick_sstBegDate').hidden
			&& ! base_form.findField('EvnStick_sstEndDate').hidden
			&& ! Ext.isEmpty(base_form.findField('EvnStick_sstBegDate').getValue())
			&& ! Ext.isEmpty(base_form.findField('EvnStick_sstEndDate').getValue())
			&& base_form.findField('EvnStick_sstEndDate').getValue() < base_form.findField('EvnStick_sstBegDate').getValue()
			&& getRegionNick() != 'kz'
		) {
			sw.swMsg.alert(langs('Ошибка'), 'Дата начала СКЛ должна быть не больше, чем дата окончания');
			this.formStatus = 'edit';
			return false;
		}

		if (
			getRegionNick() != 'kz'
			&& !base_form.findField('EvnStickBase_IsFSS').getValue()
			&& base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'pregn'
			&& base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') == 20 // доп. отпуск по беременности
			&& !EvnStickWorkRelease_SumDate.inlist([16, 54])
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					
				}.createDelegate(this),
				msg: 'При осложненных родах листок нетрудоспособности по беременности и родам выдается дополнительно на 16 календарных дней или 54 дня, если диагноз многоплодной беременности установлен в родах',
				title: ERR_INVFIELDS_TIT
			});
			this.formStatus = 'edit';
			return false;
		}
		if (
			!getRegionNick().inlist(['kz', 'by'])
			&& base_form.findField('EvnStick_IsOriginal').getValue() == 2 //дубликат
		) {
			var begDate, lastVK;
			Ext.getCmp(win.id+'EStEF_EvnStickWorkReleaseGrid').getStore().each(function(rec){
				if (!begDate || rec.get('EvnStickWorkRelease_begDate').getTime() > begDate) {
					begDate = rec.get('EvnStickWorkRelease_begDate').getTime();
					lastVK = rec.get('EvnStickWorkRelease_IsPredVK') == 1 && rec.get('MedStaffFact3_id');
				}
			});
			if (!lastVK) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'В дубликате ЛВН должен быть указан врач ВК. Заполните поле "Врач 3" в последнем периоде освобождения.',
					title: ERR_INVFIELDS_TIT
				});
				this.formStatus = 'edit';
				return false;
			}
		}

		
		if (!options.ignoreControlPridStickCause && base_form.findField('EvnStick_prid').getValue() && base_form.findField('EvnStick_prid').getValue() > 0) {
			var StickCause_SysNick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
			var PridStickCause_SysNick = base_form.findField('PridStickCause_SysNick').getValue();
			var PridStickCauseDid_SysNick = base_form.findField('PridStickCauseDid_SysNick').getValue();
			if (
				getRegionNick() == 'kz' && StickCause_SysNick != 'kurort' // санаторно-курортное лечение
				|| getRegionNick() != 'kz' && StickCause_SysNick != 'dolsan' // долечивания в санатории
			) {
				if ( 
					PridStickCauseDid_SysNick && PridStickCauseDid_SysNick != StickCause_SysNick
					|| !PridStickCauseDid_SysNick && PridStickCause_SysNick != StickCause_SysNick
				) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							this.formStatus = 'edit';

							if ( 'yes' == buttonId ) {
								options.ignoreControlPridStickCause = true;
								this.doSave(options);
							}
							else {
								me._focusButtonSave();
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: langs('В первичном ЛВН указана другая причина нетрудоспособности. Продолжить сохранение?'),
						title: langs('Вопрос')
					});
					return false;
				}
			}
		}

		if (getRegionNick() != 'kz') {
			var maxAge = 0;
			var ageErr = false;
			switch(base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick')) {
				case 'uhodreb':
					maxAge = 7;
					break;
				case 'rebinv':
				case 'postvaccinal':
				case 'vich':
					maxAge = 18;
					break;
			}
			if (maxAge) {
				evn_stick_care_person_grid.getStore().each(function(rec) {
					if (rec.get('Person_Age') >= maxAge) {
						ageErr = true;
					}
				});
				if (ageErr) {
					sw.swMsg.show({
						buttons: {
							ok: {text: langs('Редактировать атрибуты пациента')},
							cancel: true
						},
						fn: function (buttonId) {
							if (buttonId == 'ok') {

							}
						},
						icon: Ext.Msg.WARNING,
						msg: 'Причина нетрудоспособности должна соответствовать возрасту пациента, которому требуется уход',
						title: ERR_INVFIELDS_TIT
					});
					this.formStatus = 'edit';
					return false;
				}
			}

		}


		if (getRegionNick() == 'astra' && !options.ignoreSnilsCheck && !Ext.isEmpty(base_form.findField('Person_id').getValue()) && !Ext.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
			// проверяем СНИЛС у человека которому выдан ЛВН
			Ext.Ajax.request({
				url: '/?c=Person&m=getPersonSnils',
				params: {
					Person_id: base_form.findField('Person_id').getValue()
				},
				callback: function(opt, success, response) {
					win.formStatus = 'edit';
					if (success && response.responseText.length > 0) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (Ext.isEmpty(result.Person_Snils)) {
							var Person_Fio = base_form.findField('EvnStickFullNameText').getValue();
							sw.swMsg.show({
								buttons: {
									ok: {text: langs('Редактировать атрибуты пациента')},
									cancel: true
								},
								fn: function (buttonId) {
									if (buttonId == 'ok') {
										getWnd('swPersonEditWindow').show({
											action: 'edit',
											Person_id: base_form.findField('Person_id').getValue(),
											callback: function (data) {

											}
										});
									}
								},
								icon: Ext.Msg.QUESTION,
								msg: langs('У получателя ЛВН ') + Person_Fio + langs(' не указан СНИЛС, в связи с этим закрытие ЛВН невозможно. Редактировать персональные данные пациента?'),
								title: langs('Внимание')
							});
							return false;
						}

						options.ignoreSnilsCheck = true;
						win.doSave(options);
					}
				}
			});

			return false;
		}


		var EvnStickMain_StickDT = null, 
			EvnStickDop_StickDT = null;

		if (base_form.findField('EvnStickDop_pid').getFieldValue('EvnStick_StickDT')) {
			EvnStickMain_StickDT = Date.parseDate(base_form.findField('EvnStickDop_pid').getFieldValue('EvnStick_StickDT'), 'd.m.Y').getTime();
		}

		if (base_form.findField('EvnStick_StickDT').getValue()) {
			EvnStickDop_StickDT = base_form.findField('EvnStick_StickDT').getValue().getTime();
		}

		if (
			!options.ignoreChangeStickCauseDate
			&& getRegionNick() != 'kz' 
			&& base_form.findField('StickWorkType_id').getValue() == 2
			&& (
				base_form.findField('EvnStickDop_pid').getFieldValue('StickCause_did') != base_form.findField('StickCause_did').getValue()
				|| EvnStickMain_StickDT != EvnStickDop_StickDT
			)
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					this.formStatus = 'edit';

					if ( 'yes' == buttonId ) {
						options.ignoreChangeStickCauseDate = true;
						this.doSave(options);
					}
					else {
						me._focusButtonSave();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: langs('Код изменения нетрудоспособности или дата изменения причины нетрудоспособности не совпадает с кодом или датой в основном ЛВН. Продолжить сохранение?'),
				title: langs('Вопрос')
			});
			return false;
		}

		if (Ext.isEmpty(base_form.findField('StickFSSData_id').getValue()) && ! this.parentClass.inlist([ 'EvnPL', 'EvnPLStom' ])) {
			if ( ! options.ignoreControlEvnSectionDates ) {

				if (
					(getRegionNick() != 'kz' && this.advanceParams.LpuUnitType_SysNick == 'stac') ||
					(getRegionNick() == 'kz' && this.advanceParams.LpuUnitType_SysNick && this.advanceParams.LpuUnitType_SysNick.inlist(['stac', 'dstac', 'hstac', 'pstac']))
				) {
					// контроль на совпадение дат лечения в стационаре с датами движений (refs #7872)

					if (this.advanceParams.stacBegDate == null) {
						this.advanceParams.stacBegDate = '';
					}

					if (
						(this.advanceParams.stacBegDate - base_form.findField('EvnStick_stacBegDate').getValue() != 0) ||
						(
							! Ext.isEmpty(this.advanceParams.stacEndDate) && (this.advanceParams.stacEndDate - base_form.findField('EvnStick_stacEndDate').getValue() != 0)
						) ||
						(Ext.isEmpty(this.advanceParams.stacEndDate) && ! Ext.isEmpty(base_form.findField('EvnStick_stacEndDate').getValue()) )
					) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								this.formStatus = 'edit';

								if ( 'yes' == buttonId ) {
									options.ignoreControlEvnSectionDates = true;
									this.doSave(options);
								}
								else {
									me._focusButtonSave();
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Период лечения в стационаре в ЛВН не совпадает с данными движений связанных КВС, Продолжить?'),
							title: langs('Вопрос')
						});
						return false;
					}

				}
			}
		}

		// Получаем даты освобождения от работы
		var evn_stick_work_release_beg_date;
		var evn_stick_work_release_end_date;
		var index;
		var days_count = 0;
		var months_count = 0;
		var IsDraft = false;
		var IsSpecLpu = false;
		var hasMedPersonal2 = false;
		var Org_Nick = '', LpuUnitType_SysNick = '';
		var stac_end_date = base_form.findField('EvnStick_stacEndDate').getValue();
		var days_count_after_stac = 0;
		var person_age = swGetPersonAge(this.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnStick_setDate').getValue());

		evn_stick_work_release_grid.getStore().each(function(rec) {
			if ( typeof evn_stick_work_release_beg_date != 'object' || rec.get('EvnStickWorkRelease_begDate') < evn_stick_work_release_beg_date ) {
				evn_stick_work_release_beg_date = rec.get('EvnStickWorkRelease_begDate');
				LpuUnitType_SysNick = rec.get('LpuUnitType_SysNick');
				Org_Nick = rec.get('Org_Name');
			}

			if ( typeof evn_stick_work_release_end_date != 'object' || rec.get('EvnStickWorkRelease_endDate') > evn_stick_work_release_end_date ) {
				evn_stick_work_release_end_date = rec.get('EvnStickWorkRelease_endDate');
			}

			if (rec.get('EvnStickWorkRelease_IsDraft')) {
				IsDraft = true;
			}
			if (rec.get('EvnStickWorkRelease_IsSpecLpu')) {
				IsSpecLpu = true;
			}
			if (!Ext.isEmpty(rec.get('MedStaffFact2_id'))) {
				hasMedPersonal2 = true;
			}
		});

		var EvnStatus_Name = '';
		if (IsDraft) {
			EvnStatus_Name = langs('Черновик');
		}

		if (!Ext.isEmpty(evn_stick_work_release_beg_date) && !Ext.isEmpty(evn_stick_work_release_end_date)) {
			days_count = daysBetween(evn_stick_work_release_beg_date, evn_stick_work_release_end_date);

			months_count = evn_stick_work_release_beg_date.getMonthsBetween(evn_stick_work_release_end_date);
		}
		if (!Ext.isEmpty(stac_end_date) && !Ext.isEmpty(evn_stick_work_release_end_date)) {
			//Дней между окончанием лечения в стационаре и окончанием освобождения
			days_count_after_stac = daysBetween(stac_end_date, evn_stick_work_release_end_date);
		}


		//[gabdushev] #6146: Запретить выбирать значения с кодом, начинающимся на букву. (для ЛВН c датой выдачи начиная с 1 июля 2011г.)
		var permitBefore = '01.07.2011'; //Дата, до которой разрешается использование причин нетрудоспособности, код которых начинается на букву
		var aStickBeginDate = Date.parseDate(base_form.findField('EvnStick_setDate').value, 'd.m.Y');
		var letterBeginCauseCodePermitted = (aStickBeginDate < Date.parseDate(permitBefore,'d.m.Y'));
		var ok = true;
		if (!letterBeginCauseCodePermitted){
			function checkLetterBegin(field_id, field_code, field_name){
				var result = true;
				var causeField = base_form.findField(field_id);
				var aCauseId = causeField.getValue();
				if (!Ext.isEmpty(aCauseId)) {
					var causeCode = causeField.getFieldValue(field_code);
					if (!Ext.isEmpty(causeCode)) {
						var firstIsALetter = ((causeCode[0]>'A' && causeCode[0]<'Z') || (causeCode[0]>'a' && causeCode[0]<'z'));
						if (firstIsALetter) {
							var causeName = causeCode + '. ' + causeField.getFieldValue(field_name);
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								fn: function() {
									causeField.focus(true);
								}.createDelegate(this),
								msg: 'Согласно приказу № 347н от 26.04.2011 выбранное значение справочника <br /> "' + causeName + '" может быть использовано только для листков временной нетрудоспособности, выданных ранее ' + permitBefore,
								title: ERR_INVFIELDS_TIT
							});
							result = false;
						}
					}
				}
				return result;
			}
			ok = (ok && checkLetterBegin('StickCause_id','StickCause_Code','StickCause_Name'));
			ok = (ok && checkLetterBegin('StickCause_did','StickCause_Code','StickCause_Name'));
			ok = (ok && checkLetterBegin('StickLeaveType_id','StickLeaveType_Code','StickLeaveType_Name'));
			ok = (ok && checkLetterBegin('StickIrregularity_id','StickIrregularity_Code','StickIrregularity_Name'));
		}
		if (!ok) {
			this.formStatus = 'edit';
			return false;
		}
		//[/gabdushev] #6146

		// в поле порядок выдачи «продолжение ЛВН», исходом первичного ЛВН «37. Долечивание» и при указанной причине нетрудоспособности «08. Долечивание в санатории» снята обязательность заполнения раздела «Освобождение от работы» (refs #72473)
		var workReleaseCanBeEmpty = false;
		if (
			base_form.findField('StickOrder_id').getValue() == 2
			&& this.getPridStickLeaveTypeCode() == '37'
			&& base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'dolsan'
		) {
			workReleaseCanBeEmpty = true;
		}
		if (!Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			workReleaseCanBeEmpty = true;
		}

		// Проверка на заполнение хотя бы одной записи в таблице "Освобождение от работы"
		if ( !workReleaseCanBeEmpty && !this.hasWorkRelease() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';

					if ( this.findById(win.id+'EStEF_EvnStickWorkReleasePanel').collapsed ) {
						this.findById(win.id+'EStEF_EvnStickWorkReleasePanel').expand();
					}

					evn_stick_work_release_grid.getView().focusRow(0);
					evn_stick_work_release_grid.getSelectionModel().selectFirstRow();
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('Должно быть заполнено хотя бы одно освобождение от работы'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			// 1. Проверка на наличие периодов нетрудоспособности или исхода ЛВН для ЛВН с флагом «ЛВН из ФСС»:
			// если ЛВН имеет флаг «ЛВН из ФСС», данные ЛВН должны содержать хотя бы один период нетрудоспособности или исход ЛВН.
			if (!this.hasWorkRelease() && Ext.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';

						if ( this.findById(win.id+'EStEF_EvnStickWorkReleasePanel').collapsed ) {
							this.findById(win.id+'EStEF_EvnStickWorkReleasePanel').expand();
						}

						evn_stick_work_release_grid.getView().focusRow(0);
						evn_stick_work_release_grid.getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Должно быть заполнено хотя бы одно освобождение от работы или исход ЛВН',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		if ( this.parentClass.inlist(['EvnPL','EvnPLStom']) ) {
			// [2015-01-30] Поменял определение выписки задним числом
			// @task https://redmine.swan.perm.ru/issues/54724
			index = Ext.getCmp(win.id+'EStEF_EvnStickWorkReleaseGrid').getStore().findBy(function(rec){
				return (
					rec.get('EvnStickWorkRelease_IsDraft') != 1
					&& base_form.findField('EvnStick_setDate').getValue() > rec.get('EvnStickWorkRelease_begDate')
					&& !rec.get('EvnStickWorkRelease_IsPredVK')
					&& rec.get('Org_id') == getGlobalOptions().org_id
					&& rec.get('LpuUnitType_SysNick') == 'polka'
				);
			});

			if ( index >= 0 ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';

						if ( this.findById(win.id+'EStEF_EvnStickWorkReleasePanel').collapsed ) {
							this.findById(win.id+'EStEF_EvnStickWorkReleasePanel').expand();
						}

						evn_stick_work_release_grid.getView().focusRow(0);
						evn_stick_work_release_grid.getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: 'Вы выписываете листок нетрудоспособности за прошедшие дни. Выдача ЛВН должна осуществляться по решению врачебной комиссии. Необходимо указать членов ВК.',
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}



		if ( !Ext.isEmpty(base_form.findField('StickCause_id').getValue()) && base_form.findField('StickCause_id').getValue() == base_form.findField('StickCause_did').getValue() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('StickCause_did').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('Поля "Причина нетрудоспособности" и "Код изм. нетрудоспособности" не могут иметь одинаковые значения'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		
		
		var stick_cause_sysnick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
		
		if (getRegionNick() == 'kz') {
			var disallowSave = false;
			Ext.getCmp( win.id + 'EStEF_EvnStickWorkReleaseGrid').getStore().each(function(rec) {
				var begReleaseDate = rec.data.EvnStickWorkRelease_begDate;
				var endReleaseDate = rec.data.EvnStickWorkRelease_endDate;

				if(rec.data.LpuUnitType_SysNick == 'stac') {
					if(
						stac_end_date
						&& daysBetween(stac_end_date, endReleaseDate) > 4
						&& stick_cause_sysnick && stick_cause_sysnick.inlist(['desease','trauma','uhod']) 	
					) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								this.formStatus = 'edit';
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: 'Дата окончания освобождения от работы не может быть позднее даты окончания лечения в стационаре, более чем на 4 дня',
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}

					if (stick_cause_sysnick && stick_cause_sysnick == 'protez' && daysBetween(begReleaseDate, endReleaseDate) > 30) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								this.formStatus = 'edit';
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: 'По выбранной причине нетрудоспособности период освобождения от работы не может превышать 30 дней',
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}
				} else if(this.isTubDiag) {
					if (IsSpecLpu && begReleaseDate.getMonthsBetween(endReleaseDate) > 15) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								this.formStatus = 'edit';
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: 'По причине туберкулезного заболевания освобождения от работы не может превышать 15 месяцев',
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}

					if (!IsSpecLpu && daysBetween(begReleaseDate, endReleaseDate) > 3) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								this.formStatus = 'edit';
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: 'По причине туберкулезного заболевания освобождения от работы, выдаваемое МО общей практики, не может превышать 3-х дней',
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}
				} else {
					if (
						stick_cause_sysnick && stick_cause_sysnick.inlist(['desease','trauma']) 
						&& daysBetween(begReleaseDate, endReleaseDate) > 6 
						&& hasMedPersonal2 == false
					) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								this.formStatus = 'edit';
								//base_form.findField('StickCause_id').focus(true);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: langs('При выбранной причине нетрудоспособности, продолжительность освобождения от работы не должна превышать 6 дней'),
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}

					if (
						stick_cause_sysnick && stick_cause_sysnick == 'uhod'
						&& daysBetween(begReleaseDate, endReleaseDate) > 10) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								this.formStatus = 'edit';
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: 'По выбранной причине нетрудоспособности период освобождения от работы не может превышать 10 дней',
							title: ERR_INVFIELDS_TIT
						});
						disallowSave = true;
						return false;
					}

					if ( stick_cause_sysnick && stick_cause_sysnick == 'adopt' ) {
						var adopt_date = base_form.findField('EvnStick_adoptDate').getValue();

						if (!options.ignoreAdoptReleaseBegDate && Ext.util.Format.date(begReleaseDate, 'd.m.Y') != Ext.util.Format.date(adopt_date, 'd.m.Y')) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									this.formStatus = 'edit';

									if ( 'yes' == buttonId ) {
										options.ignoreAdoptReleaseBegDate = true;
										this.doSave(options);
									}
									else {
										me._focusButtonSave();
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: langs('Лист временной нетрудоспособности должен выдаваться со дня усыновления или удочерения. Продолжить сохранение?'),
								title: langs('Вопрос')
							});
							disallowSave = true;
							return false;
						}

						var birth_date = this.PersonInfo.getFieldValue('Person_Birthday');
						if (!options.ignoreAdoptReleaseEndDate && daysBetween(birth_date, endReleaseDate) != 56) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									this.formStatus = 'edit';

									if ( 'yes' == buttonId ) {
										options.ignoreAdoptReleaseEndDate = true;
										this.doSave(options);
									}
									else {
										me._focusButtonSave();
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: langs('Лист временной нетрудоспособности по усыновлению или удочерению должен выдаваться до истечения 56 дней со дня рождения ребенка. Продолжить сохранение?'),
								title: langs('Вопрос')
							});
							disallowSave = true;
							return false;
						}
					}
				}
			}.createDelegate(this));
			
			if(disallowSave) {
				this.formStatus = 'edit';
				return false;
			}
			
			if(base_form.findField('EvnStick_mid').getValue() != base_form.findField('EvnStick_pid').getValue() && !this.parentClass.inlist(['EvnPL','EvnPLStom']) )
				options.ignorePregnancyReleasePeriodQuestion = true;
			
			if( this.parentClass == 'EvnPS' )
				options.ignorePregnancyReleasePeriodQuestion = true;
		}

		if ( !options.ignorePregnancyReleasePeriodQuestion && stick_cause_sysnick && stick_cause_sysnick == 'pregn' ) {
			// При заполнении больничного листа у беременных по отпуску по беременности и родам добавить контроль на количество дней по ЛВН
			// не равное 140 дням. Добавить предупреждение "Лист временной нетрудоспособности по беременности и родам имеет продолжительность
			// не равную 140 дням. Продолжить сохранение?"
			var pregn_days = 140;
			if (getRegionNick() == 'kz') {
				pregn_days = 126;
			}

			if ( 
				(typeof evn_stick_work_release_beg_date == 'object') 
				&& (typeof evn_stick_work_release_end_date == 'object') 
				&& (Ext.util.Format.date(evn_stick_work_release_beg_date.add(Date.DAY, pregn_days - 1), 'd.m.Y') != Ext.util.Format.date(evn_stick_work_release_end_date, 'd.m.Y')) 
				&& base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseDopType_Code') != '020' // доп отпуск по беременности
			) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						this.formStatus = 'edit';

						if ( 'yes' == buttonId ) {
							options.ignorePregnancyReleasePeriodQuestion = true;
							this.doSave(options);
						}
						else {
							me._focusButtonSave();
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Лист временной нетрудоспособности по беременности и родам имеет продолжительность, не равную ')+pregn_days+langs('  дням. Продолжить сохранение?'),
					title: langs('Вопрос')
				});
				return false;
			}
		}
		else if (
			!base_form.findField('EvnStickBase_IsFSS').getValue()
			&& (
				stick_cause_sysnick && stick_cause_sysnick.inlist([/*'karantin', */'uhodnoreb', 'uhodreb', 'uhod', 'rebinv', 'vich', 'zabrebmin'])
				|| (stick_cause_sysnick && stick_cause_sysnick.inlist(['karantin']) && person_age < 18)
				|| (stick_cause_sysnick && stick_cause_sysnick == 'postvaccinal' && !getRegionNick().inlist(['kz']))
			)
		) {
			// Проверка правильности заполнения таблицы "Список пациентов, нуждающихся в уходе"

			// Должна быть хотя бы одна запись
			if ( evn_stick_care_person_grid.getStore().getCount() == 0 || !evn_stick_care_person_grid.getStore().getAt(0).get('EvnStickCarePerson_id') ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';

						if ( this.findById(win.id+'EStEF_EvnStickCarePersonPanel').collapsed ) {
							this.findById(win.id+'EStEF_EvnStickCarePersonPanel').expand();
						}

						//evn_stick_care_person_grid.getView().focusRow(0);
						//evn_stick_care_person_grid.getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: langs('При выбранной причине выдачи ЛВН, должен быть указан хотя бы один пациент, нуждающийся в уходе'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			// Должны быть заполнены обязательные поля Person_id, RelatedLinkType_id
			// #39804 Для казахстана RelatedLinkType_id не обязательно
			index = evn_stick_care_person_grid.getStore().findBy(function(rec) {
				if ( !rec.get('Person_id') || (!rec.get('RelatedLinkType_id') && getRegionNick()!='kz') ) {
					return true;
				}
				else {
					return false;
				}
			});

			if ( index >= 0 ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';

						if ( this.findById(win.id+'EStEF_EvnStickCarePersonPanel').collapsed ) {
							this.findById(win.id+'EStEF_EvnStickCarePersonPanel').expand();
						}

						evn_stick_care_person_grid.getView().focusRow(index);
						evn_stick_care_person_grid.getSelectionModel().selectRow(index);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: langs('Не заполнены обязательные поля в информации о пациенте, нуждающемся в уходе'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}
		// Установлена инвалидность
		// [2012-12-21] В справочнике отсутствует причина с кодом 32!!!
		else if ( stick_cause_sysnick && stick_cause_sysnick == '32' ) {
			var mseExamDate = base_form.findField('EvnStick_mseExamDate').getValue();

			if ( !mseExamDate ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';

						if ( this.findById(win.id+'EStEF_MSEPanel').collapsed ) {
							this.findById(win.id+'EStEF_MSEPanel').expand();
						}

						base_form.findField('EvnStick_mseExamDate').focus(true);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: langs('При указанном исходе ЛВН "Установлена инвалидность" поле "Дата освидетельствования в бюро МСЭ" обязательно для заполнения'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if ( mseExamDate.add(Date.DAY, -1) != evn_stick_work_release_end_date ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';

						if ( this.findById(win.id+'EStEF_EvnStickWorkReleasePanel').collapsed ) {
							this.findById(win.id+'EStEF_EvnStickWorkReleasePanel').expand();
						}

						evn_stick_work_release_grid.getView().focusRow(0);
						evn_stick_work_release_grid.getSelectionModel().selectFirstRow();
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: langs('Дата последнего освобождения от работы должна быть на 1 день меньше, чем значение поля "Дата освидетельствования в бюро МСЭ"'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		if ( !options.ignoreSetDateError ) {
			if (
				typeof evn_stick_work_release_beg_date == 'object' && evn_stick_work_release_beg_date < base_form.findField('EvnStick_setDate').getValue()
				&& !Ext.isEmpty(LpuUnitType_SysNick) && !LpuUnitType_SysNick.inlist([ 'stac', 'dstac', 'hstac', 'pstac' ])
			) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						this.formStatus = 'edit';

						if ( 'yes' == buttonId ) {
							options.ignoreSetDateError = true;
							this.doSave(options);
						}
						else {
							me._focusButtonSave();
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Вы выписываете листок нетрудоспособности задним числом. Продолжить?'),
					title: langs('Вопрос')
				});
				return false;
			}
		}

		if ( this.evnStickType == 2 && !options.ignoreSetDateDopError ) {
			if ( base_form.findField('EvnStickDop_pid').getFieldValue('EvnStick_setDate') != Ext.util.Format.date(base_form.findField('EvnStick_setDate').getValue(), 'd.m.Y') ) {
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						this.formStatus = 'edit';

						if ( 'yes' == buttonId ) {
							options.ignoreSetDateDopError = true;
							this.doSave(options);
						}
						else {
							me._focusButtonSave();
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Дата выдачи ЛВН не совпадает с датой выдачи основного ЛВН. Продолжить?'),
					title: langs('Вопрос')
				});
				return false;
			}
		}
		
		if (
			!base_form.findField('EvnStickBase_IsFSS').getValue() && 
			!!base_form.findField('RegistryESStorage_id').getValue() &&
			!options.ignoreSignatureCheck
		) {
			var sign_chk_index = evn_stick_work_release_grid.getStore().findBy(function(rec) {
				return !!rec.get('EvnStickWorkRelease_id') && rec.get('SMPStatus_id') == 2;
			});
			
			if (sign_chk_index >= 0) {
				this.formStatus = 'edit';
				sw.swMsg.confirm(
					langs('Внимание'),
					langs('Освобождение от работы не подписано, данные не будут переданы в ФСС. Хотите подписать освобождение от работы сейчас?'),
					function(btn) {
						if ( btn == 'yes' ) return;
						options.ignoreSignatureCheck = true;
						me.doSave(options);
					}
				);
				return false;
			}
		}

		var params = new Object();

		params.evnStickType = this.evnStickType;

		params.EvnStick_oid = base_form.findField('EvnStick_oid').getValue();




		params.EvnStick_IsOriginal = base_form.findField('EvnStick_IsOriginal').getValue();
		params.Signatures_id = this.Signatures_id;
		params.Signatures_iid = this.Signatures_iid;

		if ( base_form.findField('EvnStickBase_consentDT').disabled ) {
			params.EvnStickBase_consentDT = Ext.util.Format.date(base_form.findField('EvnStickBase_consentDT').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStickBase_IsFSS').disabled ) {
			params.EvnStickBase_IsFSS = base_form.findField('EvnStickBase_IsFSS').getValue();
		}

		if ( base_form.findField('EvnStick_Num').disabled ) {
			params.EvnStick_Num = base_form.findField('EvnStick_Num').getValue();
		}

		if ( base_form.findField('EvnStick_Ser').disabled ) {
			params.EvnStick_Ser = base_form.findField('EvnStick_Ser').getValue();
		}

		if ( base_form.findField('EvnStick_setDate').disabled ) {
			params.EvnStick_setDate = Ext.util.Format.date(base_form.findField('EvnStick_setDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_mseDate').disabled) {
			params.EvnStick_mseDate = Ext.util.Format.date(base_form.findField('EvnStick_mseDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_mseRegDate').disabled) {
			params.EvnStick_mseRegDate = Ext.util.Format.date(base_form.findField('EvnStick_mseRegDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_mseExamDate').disabled) {
			params.EvnStick_mseExamDate = Ext.util.Format.date(base_form.findField('EvnStick_mseExamDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('InvalidGroupType_id').disabled) {
			params.InvalidGroupType_id = base_form.findField('InvalidGroupType_id').getValue();
		}

		if ( base_form.findField('EvnStick_IsRegPregnancy').disabled ) {
			params.EvnStick_IsRegPregnancy = base_form.findField('EvnStick_IsRegPregnancy').getValue();
		}

		if ( base_form.findField('StickIrregularity_id').disabled ) {
			params.StickIrregularity_id = base_form.findField('StickIrregularity_id').getValue();
		}

		if ( base_form.findField('EvnStick_irrDate').disabled ) {
			params.EvnStick_irrDate = Ext.util.Format.date(base_form.findField('EvnStick_irrDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_stacBegDate').disabled ) {
			params.EvnStick_stacBegDate = Ext.util.Format.date(base_form.findField('EvnStick_stacBegDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_stacEndDate').disabled ) {
			params.EvnStick_stacEndDate = Ext.util.Format.date(base_form.findField('EvnStick_stacEndDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_BirthDate').disabled ) {
			params.EvnStick_BirthDate = Ext.util.Format.date(base_form.findField('EvnStick_BirthDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStickDop_pid').disabled ) {
			params.EvnStickDop_pid = base_form.findField('EvnStickDop_pid').getValue();
		}

		if ( base_form.findField('StickOrder_id').disabled ) {
			params.StickOrder_id = base_form.findField('StickOrder_id').getValue();
		}

		if ( base_form.findField('StickCause_id').disabled ) {
			params.StickCause_id = base_form.findField('StickCause_id').getValue();
		}

		if ( base_form.findField('StickWorkType_id').disabled ) {
			params.StickWorkType_id = base_form.findField('StickWorkType_id').getValue();
		}

		if ( base_form.findField('Org_id').disabled ) {
			params.Org_id = base_form.findField('Org_id').getValue();
		}

		if ( base_form.findField('EvnStick_OrgNick').disabled ) {
			params.EvnStick_OrgNick = base_form.findField('EvnStick_OrgNick').getValue();
		}

		if ( base_form.findField('Post_Name').disabled ) {
			params.Post_Name = base_form.findField('Post_Name').getValue();
		}

		if ( base_form.findField('StickCauseDopType_id').disabled ) {
			params.StickCauseDopType_id = base_form.findField('StickCauseDopType_id').getValue();
		}

		if ( base_form.findField('StickCause_did').disabled ) {
			params.StickCause_did = base_form.findField('StickCause_did').getValue();
		}

		if ( base_form.findField('EvnStick_StickDT').disabled ) {
			params.EvnStick_StickDT = Ext.util.Format.date(base_form.findField('EvnStick_StickDT').getValue());
		}

		if ( base_form.findField('EvnStick_sstBegDate').disabled ) {
			params.EvnStick_sstBegDate = Ext.util.Format.date(base_form.findField('EvnStick_sstBegDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_sstEndDate').disabled ) {
			params.EvnStick_sstEndDate = Ext.util.Format.date(base_form.findField('EvnStick_sstEndDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('EvnStick_sstNum').disabled ) {
			params.EvnStick_sstNum = base_form.findField('EvnStick_sstNum').getValue();
		}

		if ( base_form.findField('Org_did').disabled ) {
			params.Org_did = base_form.findField('Org_did').getValue();
		}

		// данные поля отправляем даже задисабленные, только если заполнен исход (регистратор не может редактировать исход)
		if (!Ext.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
			base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'));

			if ( base_form.findField('StickLeaveType_id').disabled ) {
				params.StickLeaveType_id = base_form.findField('StickLeaveType_id').getValue();
			}

			if ( base_form.findField('EvnStick_disDate').disabled ) {
				params.EvnStick_disDate = Ext.util.Format.date(base_form.findField('EvnStick_disDate').getValue(), 'd.m.Y');
			}

			if ( base_form.findField('MedStaffFact_id').disabled ) {
				params.MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
			}

			if ( base_form.findField('Lpu_oid').disabled ) {
				params.Lpu_oid = base_form.findField('Lpu_oid').getValue();
			}
		}

		params.StickParentClass = this.parentClass;

		if ( this.link == true ) {
			params.link = 1;
		}

		if ( this.evnStickType == 1 ) {
			// Собираем данные из гридов
			evn_stick_care_person_grid.getStore().clearFilter();

			if ( evn_stick_care_person_grid.getStore().getCount() > 0 && evn_stick_care_person_grid.getStore().getAt(0).get('EvnStickCarePerson_id') ) {
				var evn_stick_care_person_data = getStoreRecords(evn_stick_care_person_grid.getStore(), {
					exceptionFields: [
						'accessType',
						'Person_Fio',
						'RelatedLinkType_Name'
					]
				});

				var i = 0;

				// Если причина нетрудоспособности не подразумевает наличия пациентов, нуждающихся в уходе,
				// то записи из списка пациентов, нуждающихся в уходе, помечаются на удаление
				if ( 
					stick_cause_sysnick 
					&& !(
						stick_cause_sysnick.inlist([ 'uhodnoreb', 'uhodreb', 'uhod', 'rebinv', 'vich', 'zabrebmin' ])
						|| stick_cause_sysnick == 'karantin' && person_age < 18
					)
					&& !(stick_cause_sysnick == 'postvaccinal' && !getRegionNick().inlist(['kz']))
				) {
					for ( i in evn_stick_care_person_data ) {
						if ( evn_stick_care_person_data[i].RecordStatus_Code > 0 ) {
							evn_stick_care_person_data[i].RecordStatus_Code = 3;
						}
						else {
							delete evn_stick_care_person_data[i];
						}
					}
				}
				else {
					for ( i in evn_stick_care_person_data ) {
						if ( evn_stick_care_person_data[i].RecordStatus_Code != 3 && evn_stick_care_person_data[i].Person_id == base_form.findField('Person_id').getValue() ) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function() {
									this.formStatus = 'edit';
									base_form.findField('EvnStickFullNameText').focus(true);
								}.createDelegate(this),
								icon: Ext.Msg.WARNING,
								msg: langs('Человек не может быть указан одновременно как получивший ЛВН и как пациент, нуждающийся в уходе.'),
								title: ERR_INVFIELDS_TIT
							});
							return false;
						}
					}
				}

				params.evnStickCarePersonData = Ext.util.JSON.encode(evn_stick_care_person_data);

				evn_stick_care_person_grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
			}

			var personExists = false;

			// Проверяем, чтобы человек, на которого заведен учетный документ, был указан в качестве получателя ЛВН или присутствовал в списке
			// пациентов, нуждающихся в уходе, если указана соответствующая причина нетрудоспособности
			if ( Ext.isEmpty(base_form.findField('StickFSSData_id').getValue()) && base_form.findField('Person_id').getValue() != advanceParams.Person_id ) {
				if (
					stick_cause_sysnick 
					&& (
						stick_cause_sysnick.inlist([ 'uhodnoreb', 'uhodreb', 'uhod', 'rebinv', 'vich', 'zabrebmin' ])
						|| stick_cause_sysnick == 'karantin' && person_age < 18
					)
					|| (stick_cause_sysnick && stick_cause_sysnick == 'postvaccinal' && !getRegionNick().inlist(['kz']))
				) {
					evn_stick_care_person_grid.getStore().each(function(rec) {
						if ( rec.get('Person_id') == advanceParams.Person_id ) {
							personExists = true;
						}
					});
				}

				if ( personExists == false ) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							this.formStatus = 'edit';
							base_form.findField('EvnStickFullNameText').focus(true);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: langs('Человек, на которого ') + (this.parentClass.inlist([ 'EvnPL', 'EvnPLStom' ]) ? langs('заведен ТАП') : langs('заведена КВС')) + langs(', должен быть указан в качестве получателя ЛВН или присутствовать в списке пациентов, нуждающихся в уходе.'),
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}

			// Проверяем, чтобы человек, которому выдается ЛВН, отсутствовал в списке пациентов, нуждающихся в уходе,
			// если указана соответствующая причина нетрудоспособности
			if (
				!base_form.findField('EvnStickBase_IsFSS').getValue() 
				&& (
					stick_cause_sysnick 
					&& (
						stick_cause_sysnick.inlist([ 'uhodnoreb', 'uhodreb', 'uhod', 'rebinv', 'vich', 'zabrebmin' ])
						|| stick_cause_sysnick == 'karantin' && person_age < 18
					)
					|| (stick_cause_sysnick && stick_cause_sysnick == 'postvaccinal' && !getRegionNick().inlist(['astra', 'kz']))
				)
			) {
				personExists = false;

				evn_stick_care_person_grid.getStore().each(function(rec) {
					if ( rec.get('Person_id') == base_form.findField('Person_id').getValue() ) {
						personExists = true;
					}
				});

				if ( personExists == true ) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							this.formStatus = 'edit';
							base_form.findField('EvnStickFullNameText').focus(true);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: langs('Человек, который указан в качестве получателя ЛВН, присутствует в списке пациентов, нуждающихся в уходе.'),
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
		}

		if ( this.evnStickType.inlist([1,2]) ) {
			evn_stick_work_release_grid.getStore().clearFilter();

			if (this.hasWorkRelease()) {
				var evn_stick_work_release_data = getStoreRecords(evn_stick_work_release_grid.getStore(), {
					convertDateFields: true,
					exceptionFields: [
						'accessType',
						'Org_Name',
						'MedPersonal_Fio'
					]
				});

				if ( this.evnStickType == 2 ) {
					for(i=0; i<evn_stick_work_release_data.length; i++) {
						if (evn_stick_work_release_data[i].RecordStatus_Code.inlist([1,2]) && evn_stick_work_release_data[i].EvnStickBase_id == base_form.findField('EvnStickDop_pid').getValue()) {
							evn_stick_work_release_data[i].EvnStickWorkRelease_id = -swGenTempId(evn_stick_work_release_grid.getStore());
							evn_stick_work_release_data[i].EvnStickBase_id = base_form.findField('EvnStick_id').getValue();
							evn_stick_work_release_data[i].RecordStatus_Code = 0;
						}
					}
				}

				params.evnStickWorkReleaseData = Ext.util.JSON.encode(evn_stick_work_release_data);

				evn_stick_work_release_grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
			}
		}

		params.StickRegime_id = this.getStickRegimeId();

		if (options.ignoreStickOrderCheck) {
			params.ignoreStickOrderCheck = options.ignoreStickOrderCheck;
		}

		if (options.ignoreStickLeaveTypeCheck) {
			params.ignoreStickLeaveTypeCheck = options.ignoreStickLeaveTypeCheck;
		}

		if(me.ignoreCheckEvnStickOrg == 1){
			params.ignoreCheckEvnStickOrg = me.ignoreCheckEvnStickOrg;
		}


		if (options.doUpdateJobInfo) {
			params.doUpdateJobInfo = options.doUpdateJobInfo;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		// Необходимо, что бы ЛВН закрывался датой смерти пациента.
		// ajax запрос на проверку + калбэк.
		var checkparams = params;
		checkparams.EvnStick_pid = base_form.findField('EvnStick_pid').getValue();

		if ( base_form.findField('EvnStick_disDate').getValue() ) {
			checkparams.EvnStick_disDate = base_form.findField('EvnStick_disDate').getValue().format('d.m.Y');
		} else {
			checkparams.EvnStick_disDate = '01.01.1970';
		}
		checkparams.StickLeaveType_id = base_form.findField('StickLeaveType_id').getValue();

		checkparams.isHasDvijeniaInStac24 = this.checkHasDvijeniaInStac24();

		if(params.EvnStick_pid && params.EvnStick_id){
			if(params.EvnStick_pid == params.EvnStick_id){
				alert('params.EvnStick_pid == params.EvnStick_id')
			}
		}


		Ext.Ajax.request(
			{
				url: '/?c=Stick&m=CheckEvnStickDie',
				params: checkparams,
				callback: function(opt, scs, response)
				{
					if ( !options.ignoreSetDateDieError ) {

						if ( this.parentClass == 'EvnPS' ) {

							if (scs)
							{
								if ( response.responseText.length > 0 )
								{
									var result = Ext.util.JSON.decode(response.responseText);
									if (!result.success)
									{
										sw.swMsg.show({
											buttons: Ext.Msg.YESNO,
											fn: function(buttonId, text, obj) {
												this.formStatus = 'edit';

												if ( 'yes' == buttonId ) {
													options.ignoreSetDateDieError = true;
													this.doSave(options);
												}
												else {
													loadMask.hide();
													me._focusButtonSave();
												}
											}.createDelegate(this),
											icon: Ext.MessageBox.QUESTION,
											msg: langs('Исход госпитализации и исход ЛВН не совпадают, либо отличаются даты смерти в ЛВН и КВС, Продолжить?'),
											title: langs('Вопрос')
										});
										return false;
									}
								}
							}
						}
					}

					if (options.ignoreQuestionPrevInFSS) {
						params.ignoreQuestionPrevInFSS = options.ignoreQuestionPrevInFSS;
					}
					if (options.ignoreQuestionChangePrev) {
						params.ignoreQuestionChangePrev = options.ignoreQuestionChangePrev;
					}

					if (options.ignoreCheckFieldStickOrder) {
						params.ignoreCheckFieldStickOrder = options.ignoreCheckFieldStickOrder;
					}
					if (options.ignoreCheckFieldStickCause) {
						params.ignoreCheckFieldStickCause = options.ignoreCheckFieldStickCause;
					}

					if (options.ignoreCheckSummWorkRelease) {
						params.ignoreCheckSummWorkRelease = options.ignoreCheckSummWorkRelease;
					}
					if (options.ignoreCheckChangeJobInfo) {
						params.ignoreCheckChangeJobInfo = options.ignoreCheckChangeJobInfo;
					}

					base_form.submit({
						failure: function(result_form, action) {
							this.formStatus = 'edit';
							loadMask.hide();

							if ( action.result ) {
								if ( action.result.Alert_Msg && action.result.Error_Msg == 'YesNoCancel') {
									sw.swMsg.show({
										buttons: Ext.Msg.YESNOCANCEL,
										fn: function(buttonId, text, obj) {
											if ( action.result.Error_Code.inlist(['205','206']) ) {
												if (buttonId == 'no') {
													if (action.result.Error_Code == '205') {
														options.ignoreCheckFieldStickOrder = 1;
													}
													if (action.result.Error_Code == '206') {
														options.ignoreCheckFieldStickCause = 1;
													} 
													me.doSave(options);
												}

												if (buttonId == 'yes') {
													if (action.result.Error_Code == '205') {

														var StickOrder_id = base_form.findField('StickOrder_id');
														StickOrder_id.setValue(action.result.StickOrder_id);
														StickOrder_id.fireEvent('change', StickOrder_id, action.result.StickOrder_id);
													}
													if (action.result.Error_Code == '206') {

														var StickCause_id = base_form.findField('StickCause_id');
														StickCause_id.setValue(action.result.StickCause_id);
														StickCause_id.fireEvent('change', StickCause_id, action.result.StickCause_id);

													} 
												}
											}
										},
										msg: action.result.Alert_Msg,
										icon: Ext.Msg.QUESTION,
										title: langs('Вопрос')
									});
								} else if ( action.result.Alert_Msg && action.result.Error_Msg == 'YesNo' ) {
									var msg = action.result.Alert_Msg;

									if (action.result.Error_Code == 202) {
										sw.swMsg.show({
											buttons: Ext.Msg.YESNOCANCEL,
											fn: function(buttonId, text, obj) {
												if (buttonId == 'yes') {
													// установить исход
													base_form.findField('MedStaffFact_id').setValue(action.result.LeaveData.MedStaffFact_id);
													base_form.findField('MedPersonal_id').setValue(action.result.LeaveData.MedPersonal_id);
													base_form.findField('StickLeaveType_id').setValue(action.result.LeaveData.StickLeaveType_id);
													base_form.findField('EvnStick_disDate').setValue(action.result.LeaveData.EvnStick_disDate);
													sw.swMsg.alert(langs('Внимание'), langs('Данные исхода успешно изменены. Подпишите Исход ЛВН.'));
												} else if (buttonId == 'no') {
													options.ignoreStickLeaveTypeCheck = 1;
													me.doSave(options);
												}
											},
											msg: action.result.Alert_Msg,
											icon: Ext.Msg.WARNING,
											title: langs('Ошибка')
										});
									} else {
										sw.swMsg.show({
											buttons: Ext.Msg.YESNO,
											fn: function(buttonId, text, obj) {
												me.formStatus = 'edit';

												if (buttonId == 'yes') {
													switch (true) {
														case (101 == action.result.Error_Code):
															options.ignoreStickOrderCheck = 1;
															break;

														case (102 == action.result.Error_Code):
															options.doUpdateJobInfo = 1;
															break;

														case (103 == action.result.Error_Code):
															me.ignoreCheckEvnStickOrg = 1;
															break;

														case (104 == action.result.Error_Code):
														case (106 == action.result.Error_Code):
															options.ignoreQuestionPrevInFSS = 1;
															break;
														case (105 == action.result.Error_Code):
														case (107 == action.result.Error_Code):
															options.ignoreQuestionChangePrev = 1;
															break;
														case (108 == action.result.Error_Code):
															options.ignoreCheckSummWorkRelease = 1;
															break;
													}
													me.doSave(options);
												}

												if (buttonId == 'no') {

													if (104 == action.result.Error_Code || 105 == action.result.Error_Code) {
														base_form.findField('EvnStick_prid').setValue(action.result.EvnStick_prid);
														base_form.findField('EvnStickLast_Title').setValue(action.result.EvnStick_prNum);
													} else if (106 == action.result.Error_Code || 107 == action.result.Error_Code) {
														base_form.findField('StickOrder_id').setValue(2);
														base_form.findField('StickOrder_id').fireEvent('change', base_form.findField('StickOrder_id'), 2, 1);

														base_form.findField('EvnStick_prid').setValue(action.result.EvnStick_prid);
														base_form.findField('EvnStickLast_Title').setValue(action.result.EvnStick_prNum);
													} else if (103 == action.result.Error_Code) {
														me.ignoreCheckEvnStickOrg = 1;
													} else if (102 == action.result.Error_Code) {
														options.ignoreCheckChangeJobInfo = 1;
														me.doSave(options);
													} else if (!action.result.Error_Code.inlist(['108'])) {
														me.hide();
													}
												}
											},
											icon: Ext.MessageBox.QUESTION,
											msg: msg,
											title: langs(' Продолжить сохранение?')
										});
									}

									return false;
								} else if(action.result.Alert_Msg) {
									switch (true) {
										case (201 == action.result.Error_Code):
											sw.swMsg.show({
												buttons: Ext.Msg.OK,
												fn: function(buttonId, text, obj) {
													if(buttonId == 'ok') {
														Ext.getCmp(me.id + 'EStEF_MSEPanel').expand();
														base_form.findField('EvnStick_mseDate').focus();
														
													}
												},
												msg: action.result.Alert_Msg,
												icon: Ext.Msg.WARNING,
												title: langs('Ошибка')
											});
											break;
										case (203 == action.result.Error_Code):
											sw.swMsg.show({
												buttons: Ext.Msg.OK,
												fn: function(buttonId, text, obj) {
													if(buttonId == 'ok') {
														base_form.findField('EvnStick_prid').setValue(action.result.EvnStick_prid);
														base_form.findField('EvnStickLast_Title').setValue(action.result.EvnStick_prNum);
													}
												},
												msg: action.result.Alert_Msg,
												icon: Ext.Msg.WARNING,
												title: langs('Ошибка')
											});
											break;
										case (204 == action.result.Error_Code):
											sw.swMsg.show({
												buttons: Ext.Msg.OK,
												fn: function(buttonId, text, obj) {
													if(buttonId == 'ok') {
														base_form.findField('StickOrder_id').setValue(2);
														base_form.findField('StickOrder_id').fireEvent('change', base_form.findField('StickOrder_id'), 2, 1);

														base_form.findField('EvnStick_prid').setValue(action.result.EvnStick_prid);
														base_form.findField('EvnStickLast_Title').setValue(action.result.EvnStick_prNum);
													}
												},
												msg: action.result.Alert_Msg,
												icon: Ext.Msg.WARNING,
												title: langs('Ошибка')
											});
											break;
									}
								} else if ( action.result.Error_Msg ) {
									sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
								}

								if (action.result.Error_Code && action.result.Error_Code == '401') {
									// очищаем поле номер
									base_form.findField('RegistryESStorage_id').setValue(null);
									base_form.findField('EvnStick_Num').setRawValue('');
									base_form.findField('EvnStick_Num').fireEvent('change', base_form.findField('EvnStick_Num'), base_form.findField('EvnStick_Num').getValue());
								}
							}
						}.createDelegate(this),
						params: params,
						success: function(result_form, action) {

							loadMask.hide();

							if ( action.result.EvnStick_id > 0 ) {
								base_form.findField('EvnStick_id').setValue(action.result.EvnStick_id);
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
								return false;
							}

							this.formStatus = 'edit';


							if ( action.result ) {
								var evn_stick_id = base_form.findField('EvnStick_id').getValue();
								var stick_order_id = base_form.findField('StickOrder_id').getValue();
								var stick_order_name = base_form.findField('StickOrder_id').getFieldValue('StickOrder_Name');
								var stick_work_type_id = base_form.findField('StickWorkType_id').getValue();
								var stick_work_type_name = base_form.findField('StickWorkType_id').getFieldValue('StickWorkType_Name');

								var data = new Object();

								data.evnStickData = [{
									'accessType': 'edit',
									'delAccessType': this.delAccessType,
									'cancelAccessType': this.cancelAccessType,
									'evnStickType': this.evnStickType,
									'EvnStick_disDate': base_form.findField('EvnStick_disDate').getValue(),
									'EvnStick_id': base_form.findField('EvnStick_id').getValue(),
									'EvnStick_mid': base_form.findField('EvnStick_mid').getValue(),
									'EvnStick_Num': base_form.findField('EvnStick_Num').getValue(),
									'EvnStick_ParentNum': (base_form.findField('EvnStick_mid').getValue() != base_form.findField('EvnStick_pid').getValue() ? this.parentNum : ''),
									'EvnStick_ParentTypeName': (base_form.findField('EvnStick_mid').getValue() != base_form.findField('EvnStick_pid').getValue() ? (this.parentClass == 'EvnPL' ? langs('ТАП') : (this.parentClass == 'EvnPLStom' ? langs('Стом. ТАП') : langs('КВС'))) : langs('Текущий')),
									'EvnStick_IsOriginal' : (base_form.findField('EvnStick_IsOriginal').getValue() == 2)?langs('Дубликат'):langs('Оригинал'),
									'EvnStick_stacBegDate' : Ext.util.Format.date(base_form.findField('EvnStick_stacBegDate').getValue(), 'd.m.Y'),
									'EvnStick_stacEndDate' : Ext.util.Format.date(base_form.findField('EvnStick_stacEndDate').getValue(), 'd.m.Y'),
									'EvnSection_setDate' : base_form.findField('EvnSection_setDate').getValue(),
									'EvnSection_disDate' : base_form.findField('EvnSection_disDate').getValue(),
									'EvnStick_pid': base_form.findField('EvnStick_pid').getValue(),
									'EvnStick_Ser': base_form.findField('EvnStick_Ser').getValue(),
									'EvnStick_setDate': base_form.findField('EvnStick_setDate').getValue(),
									'EvnStickWorkRelease_begDate': evn_stick_work_release_beg_date,
									'EvnStickWorkRelease_endDate': evn_stick_work_release_end_date,
									'parentClass': this.parentClass,
									'Person_id': base_form.findField('Person_id').getValue(),
									'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
									'Server_id': base_form.findField('Server_id').getValue(),
									'StickOrder_Name': stick_order_name,
									'StickType_Name': langs('ЛВН'),
									'StickWorkType_Name': stick_work_type_name,
									'Org_Nick': Org_Nick,
									'EvnStatus_Name': EvnStatus_Name
								}];

								this.callback(data);


								if(
									getRegionNick() != 'kz' &&
									(me.checkIsLvnFromFSS() || me.checkIsLvnELN()) &&
									// если ЛВН являетс япродолжением
									base_form.findField('StickOrder_id').getFieldValue('StickOrder_Code') == 2 &&
									me.action == 'add'
								){
									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function(buttonId, text, obj) {

											if ( 'yes' == buttonId ) {

												win.params.action = 'edit';
												win.params.evnStickType = stick_work_type_id == 2 ? 2 : 1;
												win.params.formParams.EvnStick_id = base_form.findField('EvnStick_prid').getValue();
												win.params.formParams.Person_id = win.params.Person_id;

												if(win.params.PersonEvn_id) {
													win.params.formParams.PersonEvn_id = win.params.PersonEvn_id;
												} else {
													win.params.formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
												}
												
												if(win.params.Server_id) {
													win.params.formParams.Server_id = win.params.Server_id;
												}
												getWnd('swEvnStickEditWindow').show(win.params);
											}

										}.createDelegate(this),
										icon: Ext.MessageBox.QUESTION,
										msg: langs('Для успешной сдачи сведений об ЛВН в ФСС после создания ЛВН-продолжения необходимо подписывать первичный ЛВН. Открыть первичный ЛВН?».'),
										title: langs('Вопрос')
									});
									this.hide();
								}

								if ( options && (options.print || options.ignoreEvnStickContQuestion) ) {
									if ( options.print ) {
										this.CheckWorkRelease();

										// Перезагружаем списки
										// https://redmine.swan.perm.ru/issues/8568
										var evn_stick_id = base_form.findField('EvnStick_id').getValue();
										var evn_stick_dop_pid = base_form.findField('EvnStickDop_pid').getValue();

										this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getStore().load({
											params: {
												EvnStick_id: evn_stick_id,
												EvnStickDop_pid: (evn_stick_dop_pid > 0)?evn_stick_dop_pid:null
											}
										});

										if ( this.evnStickType == 2 ) {
											evn_stick_id = this.FormPanel.getForm().findField('EvnStickDop_pid').getValue();
										}
										else {
											evn_stick_id = this.FormPanel.getForm().findField('EvnStick_id').getValue();
										}

										this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getStore().load({
											params: {
												EvnStick_id: evn_stick_id,
												EvnStickBase_IsFSS: base_form.findField('EvnStickBase_IsFSS').getValue()

											}
										});

										me._focusButtonPrint();
									}
								}
								else if ( options.callback && typeof options.callback == 'function' ) {
									// надо обновить список освобождений от работы и запустить callback
									var evn_stick_id = base_form.findField('EvnStick_id').getValue();

									// после перезагрузки надо выделить ту же запись, что и сейчас выделена (если выделена).
									var EvnStickWorkReleaseGrid = this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid');
									var selectedIndex = -1;
									var record = EvnStickWorkReleaseGrid.getSelectionModel().getSelected();
									if (record) {
										selectedIndex = EvnStickWorkReleaseGrid.getStore().indexOf(record);
									}
									EvnStickWorkReleaseGrid.getStore().load({
										params: {
											EvnStick_id: evn_stick_id
										},
										callback: function() {
											if (selectedIndex >= 0) {
												EvnStickWorkReleaseGrid.getView().focusRow(selectedIndex);
												EvnStickWorkReleaseGrid.getSelectionModel().selectRow(selectedIndex);
											}
											options.callback();
										}
									});

									if ( this.evnStickType == 2 ) {
										evn_stick_id = this.FormPanel.getForm().findField('EvnStickDop_pid').getValue();
									}
									else {
										evn_stick_id = this.FormPanel.getForm().findField('EvnStick_id').getValue();
									}

									this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getStore().load({
										params: {
											EvnStick_id: evn_stick_id
										}
									});
								}
								else if (
									Ext.isEmpty(base_form.findField('EvnStickNext_id').getValue()) // если ещё нет продолжения
									&& Ext.isEmpty(base_form.findField('EvnStick_NumNext').getValue()) // если нет ЛВН-продолжение в блоке "Исход"
									&& !Ext.isEmpty(base_form.findField('EvnStick_id').getValue())
									&& base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code')
									&& base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code').inlist(['31','37']) // и исход 31 или 37
								) {
									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function(buttonId, text, obj) {
											if ( 'yes' == buttonId ) {
												win.params.EvnStick_id = null;
												win.params.EvnStick_Num = null;
												win.params.JobOrg_id = base_form.findField('Org_id').getValue();
												win.params.Person_Post = base_form.findField('Post_Name').getValue();
												win.params.EvnStick_OrgNick = base_form.findField('EvnStick_OrgNick').getValue();
												win.params.StickOrder_Code = '2';
												win.params.EvnStick_prid = base_form.findField('EvnStick_id').getValue();
												if (base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code') == '37') {
													win.params.StickCause_SysNick = 'dolsan';
													// win.params.Org_did = base_form.findField('Lpu_oid').getFieldValue('Org_id');
												} else if (getRegionNick() != 'kz') {
													if (base_form.findField('StickCause_did').getValue()) {
														win.params.StickCause_SysNick = base_form.findField('StickCause_did').getFieldValue('StickCause_SysNick');
													} else {
														win.params.StickCause_SysNick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
													}
												}
												
												win.params.action = 'add';
												win.params.formParams.EvnStick_id = null;
												win.params.formParams.EvnStick_setDate = null;
												win.params.formParams.StickFSSData_id = null;
												win.params.Person_id = base_form.findField('Person_id').getValue();
												win.params.formParams.Person_id = win.params.Person_id;
												win.params.formParams.EvnStick_pid = win.params.formParams.EvnStick_mid; // должен привязаться к текущему случаю, а не к тому, куда был привязан первичный ЛВН.

												if(win.params.PersonEvn_id) {
													win.params.formParams.PersonEvn_id = win.params.PersonEvn_id;
												} else {
													win.params.formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
												}
												
												if(win.params.Server_id) {
													win.params.formParams.Server_id = win.params.Server_id;
												}
												win.params.link = 0;

												getWnd('swEvnStickEditWindow').show(win.params);
											}
										}.createDelegate(this),
										icon: Ext.MessageBox.QUESTION,
										msg: 'Согласно правилам оформления ЛВН при закрытии ЛВН с исходом «Продолжает болеть» и «Долечивание» необходимо заполнять ЛВН-продолжение. Заполнить ЛВН-продолжение сейчас?',
										title: langs('Вопрос')
									});
									this.hide();
								}
								else {
									this.hide();
								}
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
							}
						}.createDelegate(this)
					});
				}.createDelegate(this)
			});
	},
	// -----------------------------------------------------------------------------------------------------------------




	// -----------------------------------------------------------------------------------------------------------------
	// Кнопка "Печать"
	// -----------------------------------------------------------------------------------------------------------------

	_focusButtonPrint: function(){
		var me = this;
		Ext.getCmp(me.id + 'buttonPrint').focus();
	},
	// Печать
	doBeforePrintEvnStick: function() {
		__l('doBeforePrintEvnStick');
		var me = this;
		switch ( this.action ) {
			case 'add':
			case 'edit':
				this.doSave({
					print: true
				});
				break;

			case 'view':
				this.CheckWorkRelease();
				break;
		}
	},
	// -----------------------------------------------------------------------------------------------------------------







	// =================================================================================================================








	




	// -----------------------------------------------------------------------------------------------------------------
	_applyParams: function(arguments){
		__l('_applyParams');
		var me = this;

		me.params = arguments;
		me.advanceParams = arguments;

		if(arguments.formParams.StickReg) {
			me.StickReg = arguments.formParams.StickReg;
		}
		if(arguments.formParams.CurLpuSection_id) {
			me.CurLpuSection_id = arguments.formParams.CurLpuSection_id;
		}
		if(arguments.formParams.CurLpuUnit_id) {
			me.CurLpuUnit_id = arguments.formParams.CurLpuUnit_id;
		}
		if(arguments.formParams.CurLpuBuilding_id) {
			me.CurLpuBuilding_id = arguments.formParams.CurLpuBuilding_id;
		}

		if(arguments.formParams.IngoreMSFFilter) {
			me.IngoreMSFFilter = arguments.formParams.IngoreMSFFilter;
		}

		if ( ! me.advanceParams.stacBegDate){
			me.advanceParams.stacBegDate = null;
		}
		if ( ! me.advanceParams.stacEndDate){
			me.advanceParams.stacEndDate = null;
		}

		if ( arguments.action && typeof arguments.action == 'string' ) {
			me.action = arguments.action;
		}

		if ( arguments.callback && typeof arguments.callback == 'function' ) {
			me.callback = arguments.callback;
		}

		if ( arguments.evnStickType ) {
			me.evnStickType = arguments.evnStickType;
		}

		if ( arguments.JobOrg_id ) {
			me.JobOrg_id = arguments.JobOrg_id;
		}

		if ( arguments.link ) {
			me.link = arguments.link;
		}

		if ( arguments.onHide && typeof arguments.onHide == 'function' ) {
			me.onHide = arguments.onHide;
		}

		if ( arguments.parentClass ){
			me.parentClass = arguments.parentClass;
		}

		if ( arguments.parentNum ){
			me.parentNum = arguments.parentNum;
		}

		if ( arguments.Person_Post ) {
			me.Person_Post = arguments.Person_Post;
		}

		if ( arguments.EvnStick_OrgNick ) {
			me.EvnStick_OrgNick = arguments.EvnStick_OrgNick;
		}

		if ( arguments.isTubDiag ) {
			me.isTubDiag = true;
		}


		if (arguments.formParams.EvnStick_id){
			me.EvnStick_id = arguments.formParams.EvnStick_id;
		}

		if (arguments.formParams.EvnStick_mid){
			me.EvnStick_mid = arguments.formParams.EvnStick_mid;
		}

		if (arguments.formParams.EvnStick_pid){
			me.EvnStick_pid = arguments.formParams.EvnStick_pid;
		}



		if ( arguments.UserMedStaffFact_id ) {
			me.userMedStaffFactId = arguments.UserMedStaffFact_id;
		}

		if(arguments.fromList) {
			me.fromList = true;
		}

		return me;
	},
	_toggleFormElements: function(){
		__l('_toggleFormElements');
		var me = this;

		Ext.getCmp('openEvnStickWorkReleaseCalculationWindow').hide();
		Ext.getCmp('updateEvnStickWorkReleaseGrid').hide();

		me.findById(me.id+'EStEF_MSEPanel').collapse();
		me.findById(me.id+'EStEF_StickLeavePanel').collapse();
		me.findById(me.id+'EStEF_StickRegimePanel').collapse();

		me.findById('swSignStickLeave').disable();
		me.findById('swSignStickLeaveList').disable();
		me.findById('swSignStickLeaveCheck').disable();
		me.findById('swSignStickIrr').disable();
		me.findById('swSignStickIrrList').disable();
		me.findById('swSignStickIrrCheck').disable();

		me.findById(me.id+'EStEF_EvnStickCarePersonPanel').expand();
		me.findById(me.id+'EStEF_EvnStickCarePersonPanel').isLoaded = false;

		me.findById(me.id+'EStEF_EvnStickWorkReleasePanel').expand();
		me.findById(me.id+'EStEF_EvnStickWorkReleasePanel').isLoaded = false;

		me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().removeAll();
		me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].enable();
		me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[1].disable();
		me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[2].disable();
		me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[3].disable();

		me.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getStore().removeAll();
		me.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].enable();
		me.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[1].disable();
		me.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[2].disable();
		me.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[3].disable();
		me.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[6].disable();

		me.findById(me.id+'EStEF_ESSConsentDelete').hide();
		me.findById(me.id+'EStEF_ESSConsentDelete').disable();

		me.findById('SLeaveStatus_Name').getEl().dom.innerHTML = '';
		me.findById('SLeaveStatus_Name').render();
		me.findById('SIrrStatus_Name').getEl().dom.innerHTML = '';
		me.findById('SIrrStatus_Name').render();

		if ( me.parentClass == 'EvnPS' ) {
			me.findById(me.id+'EStEF_StickRegimePanel').expand();
		}

		if(me.action == 'add'){
			me.findById(me.id+'EStEF_EvnStickCarePersonPanel').isLoaded = true;
			me.findById(me.id+'EStEF_EvnStickWorkReleasePanel').isLoaded = true;
		}

		if(me.action == 'view'){
			me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].disable();
			me.findById(me.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].disable();
		}

		return me;
	},
	_setFormFields: function(){
		__l('_setFormFields');
		var me = this;

		var base_form = me.FormPanel.getForm();

		// -------------------------------------------------------------------------------------------------------------
		base_form.setValues(me.params.formParams);
		// -------------------------------------------------------------------------------------------------------------

		base_form.findField('UAddress_AddressText').triggers[2].hide();
		base_form.findField('EvnStick_IsOriginal').setValue(1);
		base_form.findField('CountDubles').setValue(0);
		base_form.findField('EvnStick_oid').setAllowBlank(true);
		base_form.findField('EvnStick_oid').setContainerVisible(false);
		base_form.findField('EvnStick_StickDT').setAllowBlank(true);
		base_form.findField('EvnStick_StickDT').setContainerVisible(false);
		base_form.findField('EvnStick_adoptDate').setContainerVisible(false);


		if (getRegionNick() == 'kz' && me.action != 'add') {
			base_form.findField('StickCauseDopType_id').setContainerVisible(false);
			base_form.findField('StickCause_did').setContainerVisible(false);

			//определяем указано ли в освобождении рабочее место из МО пользователя #135678
			Ext.Ajax.request({
				url: '/?c=Stick&m=checkWorkReleaseMedstaffFact',
				params: {
					EvnStick_id: me.EvnStick_id
				},
				success: function(response) {
					var result = Ext.util.JSON.decode(response.responseText);
					me.MedStaffFactInUserLpu = result.MedStaffFactInUserLpu;

				}
			});
		}

		if(me.params.PersonEvn_id) {
			base_form.findField('PersonEvn_id').setValue(me.params.PersonEvn_id);
		}

		if(me.params.Person_id) {
			base_form.findField('Person_id').setValue(me.params.Person_id);
		}

		base_form.findField('EvnStickBase_IsFSS').setValue(false);
		if (!Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			base_form.findField('EvnStickBase_IsFSS').setValue(true);
		}
		base_form.findField('EvnStickDop_pid').getStore().removeAll();

		// зависит от panelStickLeave
		// base_form.findField('MedStaffFact_id').getStore().removeAll();

		if (getRegionNick() == 'kz') {
			base_form.findField('EvnStick_Ser').setAllowBlank( ! me.StickReg);
			base_form.findField('EvnStick_Num').setAllowBlank( ! me.StickReg);
		}

		if (getRegionNick() == 'kz') { // #136679 Регион: Казахстан Длина номера составляет 7 цифр
			base_form.findField('EvnStick_Num').maxLength = 7;
			base_form.findField('EvnStick_Num').minLength = 7;

		}

		base_form.findField('EvnStick_Ser').setContainerVisible(getRegionNick() == 'kz');
		base_form.findField('EvnStick_sstBegDate').enable();

		
		
		if (this.action == 'add') {
			Ext.Ajax.request({//заполняем поле "Дата направления в бюро МСЭ" значением из направления
				url: '/?c=Mse&m=getEvnMse',
				params: {
					EvnPL_id: me.EvnStick_pid 
				},
				success: function(response) {
					var result = Ext.util.JSON.decode(response.responseText);
					
					if (result[0] && result[0].EvnPrescrMse_issueDT) {
						base_form.findField('EvnStick_mseDate').setValue(result[0].EvnPrescrMse_issueDT);
						Ext.getCmp(me.id + 'EStEF_MSEPanel').expand();
					}
				}
			});
		}


		return me;
	},
	_resetConfig: function(){
		__l('_resetConfig');


		var me = this;

		me.action = null;
		me.callback = Ext.emptyFn;
		me.evnStickType = 1;
		me.formStatus = 'edit';
		me.JobOrg_id = null;
		me.link = false;
		me.onHide = Ext.emptyFn;
		me.parentClass = '';
		me.parentNum = null;
		me.Person_Post = null;
		me.EvnStick_OrgNick = null;
		me.userMedStaffFactId = null;
		me.CurLpuSection_id = 0;
		me.CurLpuUnit_id = 0;
		me.CurLpuBuilding_id = 0;
		me.IngoreMSFFilter = 0;
		me.StickReg = 0;
		me.Signatures_id = null;
		me.Signatures_iid = null;
		me.isTubDiag = false;
		me.isPaid = false;
		me.isInReg = false;
		me.hasWorkReleaseIsInReg = false;
		me.EvnStick_id = null;
		me.EvnStick_mid = null;
		me.EvnStick_pid = null;
		me.fromList = false;
		me.isHasDvijenia = null;
		me.isHasDvijeniaInStac24 = null;
		me.EvnSectionList = null;

		return me;
	},
	_resetForm: function(){
		__l('_resetForm');
		var me = this;
		me.FormPanel.getForm().reset();

		return me;
	},
	delDocsView: false,
	show: function() {
		__l('show');
		sw.Promed.swEvnStickEditWindow.superclass.show.apply(this, arguments);

		var me = this;


		me.restore();
		me.center();
		me.maximize();

		Ext.getCmp(this.id + 'buttonPrint').show();


		me.getLoadMask().show();


		me._resetConfig();
		me._resetForm();


		if ( ! arguments[0] || ! arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'), function(){
				me.doHideForm();
			});

			return false;
		}

		if (arguments[0].delDocsView) {
			this.delDocsView = arguments[0].delDocsView;
		}

		// Сохраняем полученные данные
		me._applyParams(arguments[0]);


		// hide-им, expand-им, enable-им, disable-им, collapse-им элементы формы
		me._toggleFormElements();


		// Обрабаываем поля формы в зависимости от полученных данных
		me._setFormFields();

		// Загружаем данные пациента
		me._loadPersonInfo();

		me._removeAll_WorkRelease();

		// Фильтруем причину нетрудоспособности
		me.filterStickCause();


		// Поиск движений
		me.getDvijeniaKVC();


		me._findEvnStick_stacDates();
		me._listenerChange_EvnStick_stacBegDate();

		switch(me.action){
			// При добавление действия одинаковые
			case 'add':
				this.showAsAdd();
				break;

			case 'view':
			case 'edit':
				this.showAsEditOrView();
				break;

			default:
				this.doHideForm();
				break;
		}
	},
	showAsAdd: function(){
		__l('showAsAdd');

		var me = this;
		var base_form = me.FormPanel.getForm();

		me.setTitle(WND_STICK_ESTADD);

		me.Lpu_id = getGlobalOptions().lpu_id;

		// открываем все элементы для редактирования
		me.enableEdit(true);


		if (getRegionNick() != 'kz'){
			me.checkGetEvnStickNumButton();
		}

		// Устанавливаем обязательность поля "Организация"
		me.checkOrgFieldDisabled();


		LoadEmptyRow(me.findById(me.id+'EStEF_EvnStickCarePersonGrid'));
		LoadEmptyRow(me.findById(me.id+'EStEF_EvnStickWorkReleaseGrid'));


		if ( ! Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			base_form.findField('EvnStick_IsOriginal').clearValue();
			base_form.findField('EvnStick_setDate').setValue(null);
		}

		if (base_form.findField('EvnStickBase_IsFSS').getValue()) {
			Ext.Ajax.request({
				url: '/?c=Stick&m=checkSanatorium',
				success: function(response, options) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result && result.isSanatorium === true) {
						me.isSanatorium = true;
						me.refreshFormPartsAccess();
					}
				}
			});
		}




		// -----------------------------------------------------------------------------------------------------------------
		// StickWorkType_id
		if (Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			// Устанавливаем тип занятости по умолчанию - Основная работа
			base_form.findField('StickWorkType_id').setValue(1);
		}
		base_form.findField('StickWorkType_id').fireEvent('change', base_form.findField('StickWorkType_id'), base_form.findField('StickWorkType_id').getValue());
		// -----------------------------------------------------------------------------------------------------------------



		// -----------------------------------------------------------------------------------------------------------------
		// StickCause_id
		if (Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			// Устанавливаем причину нетрудоспособности по умолчанию - Заболевание
			var StickCause_SysNick = 'desease';
			if (me.params.StickCause_SysNick) {
				StickCause_SysNick = me.params.StickCause_SysNick;
			}
			var index = base_form.findField('StickCause_id').getStore().findBy(function(rec) {
				if (rec.get('StickCause_SysNick') == StickCause_SysNick) {
					return true;
				}
				else {
					return false;
				}
			});
			var record = base_form.findField('StickCause_id').getStore().getAt(index);

			if (record && getRegionNick() != 'penza') {
				base_form.findField('StickCause_id').setValue(record.get('StickCause_id'));
			}
		}
		base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), base_form.findField('StickCause_id').getValue());
		// -----------------------------------------------------------------------------------------------------------------



		// -----------------------------------------------------------------------------------------------------------------
		// StickOrder_id
		if (Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
			// Устанавливаем порядок выдачи по умолчанию - Первичный ЛВН
			var StickOrder_Code = '1';
			if (me.params.StickOrder_Code) {
				StickOrder_Code = me.params.StickOrder_Code;
			}
			index = base_form.findField('StickOrder_id').getStore().findBy(function(rec) {
				if (rec.get('StickOrder_Code') == StickOrder_Code) {
					return true;
				}
				else {
					return false;
				}
			});
			record = base_form.findField('StickOrder_id').getStore().getAt(index);

			if (record) {
				base_form.findField('StickOrder_id').setValue(record.get('StickOrder_id'));
			}
		}
		base_form.findField('StickOrder_id').fireEvent('change', base_form.findField('StickOrder_id'), base_form.findField('StickOrder_id').getValue());
		// -----------------------------------------------------------------------------------------------------------------


		//---


		if (me.params.EvnStick_prid) {
			Ext.Ajax.request({
				url: '/?c=Stick&m=getEvnStickPridValues',
				params: {
					EvnStick_prid: me.params.EvnStick_prid
				},
				callback: function(opt, success, response) {
					if (success && response.responseText.length > 0) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success) {
							base_form.findField('EvnStick_prid').setValue(me.params.EvnStick_prid);
							base_form.findField('StickWorkType_id').setValue(result.StickWorkType_id);
							if (result.StickWorkType_id == 2) {
								base_form.findField('StickWorkType_id').fireEvent('change', base_form.findField('StickWorkType_id'), result.StickWorkType_id);
							}
							base_form.findField('PridStickLeaveType_Code2').setValue(result.PridStickLeaveType_Code2);
							base_form.findField('MaxDaysLimitAfterStac').setValue(result.MaxDaysLimitAfterStac);
							me.getWorkReleaseSumm(me.params.EvnStick_prid);
							base_form.findField('EvnStickLast_Title').setValue(result.EvnStickLast_Title);
							base_form.findField('PridEvnStickWorkRelease_endDate').setValue(result.PridEvnStickWorkRelease_endDate);
							base_form.findField('PridStickCause_SysNick').setValue(result.StickCause_SysNick);
							base_form.findField('PridStickCauseDid_SysNick').setValue(result.StickCauseDid_SysNick);

							if (!getRegionNick().inlist(['kz','penza'])) {
								var StickCause_id, index;
								if (me.getPridStickLeaveTypeCode() == '37') {
									index = base_form.findField('StickCause_id').getStore().findBy(function(rec) {
										return rec.get('StickCause_SysNick') == 'dolsan';
									});
									if (index != -1) {
										StickCause_id = base_form.findField('StickCause_id').getStore().getAt(index).get('StickCause_id');
										base_form.findField('StickCause_id').setValue(StickCause_id);
										base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), StickCause_id);
									}
								} else {
									base_form.findField('StickCause_id').setValue(result.StickCause_did || result.StickCause_id);
									base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), result.StickCause_did || result.StickCause_id);
								}
							}

							if (
								getRegionNick() == 'ufa'
								&& me.getPridStickLeaveTypeCode() == '37'
								&& ! Ext.isEmpty(base_form.findField('PridEvnStickWorkRelease_endDate').getValue())
							) {

								// Дата начала СКЛ
								base_form.findField('EvnStick_sstBegDate').setValue(base_form.findField('PridEvnStickWorkRelease_endDate').getValue());
								base_form.findField('EvnStick_sstBegDate').disable();
							}
						}
					}
				}
			});
			
			if (getRegionNick() != 'kz') {
				if (StickCause_SysNick && StickCause_SysNick.inlist(['uhod', 'uhodnoreb', 'uhodreb', 'rebinv', 'vich', 'postvaccinal', 'zabrebmin'])) {
					me._loadStoreEvnStickCarePerson({
						EvnStick_id: me.params.EvnStick_prid
					});
				}
			}
		}

		base_form.findField('StickIrregularity_id').fireEvent('change', base_form.findField('StickIrregularity_id'), base_form.findField('StickIrregularity_id').getValue());
		base_form.findField('StickLeaveType_id').fireEvent('change', base_form.findField('StickLeaveType_id'), base_form.findField('StickLeaveType_id').getValue());

		me._setDefaultValueTo_Org_id();

		if (me.params.Org_did) {
			var org_id = me.params.Org_did;

			if (org_id != null && Number(org_id) > 0) {
				base_form.findField('Org_did').getStore().load({
					callback: function(records, options, success) {
						if ( success ) {
							base_form.findField('Org_did').setValue(org_id);
						}
					},
					params: {
						Org_id: org_id,
						OrgType: 'org'
					}
				});
			}
		}

		if (me.params.JobOrg_id) {
			var org_id = me.params.JobOrg_id;

			if (org_id != null && Number(org_id) > 0) {
				base_form.findField('Org_id').getStore().load({
					callback: function(records, options, success) {
						if ( success ) {
							base_form.findField('Org_id').setValue(org_id);
						}
					},
					params: {
						Org_id: org_id,
						OrgType: 'org'
					}
				});
			}
		}

		if (me.Person_Post) {
			base_form.findField('Post_Name').setValue(me.Person_Post);
		}

		if (me.EvnStick_OrgNick) {
			base_form.findField('EvnStick_OrgNick').setValue(me.EvnStick_OrgNick);
		}


		// -----------------------------------------------------------------------------------------------------------------
		// EvnStick_setDate
		var evn_stick_set_date = base_form.findField('EvnStick_setDate').getValue();
		if ( ! Ext.isEmpty(me.advanceParams.stacEndDate) && base_form.findField('EvnStickBase_IsFSS').getValue() == false){

			evn_stick_set_date = me.advanceParams.stacEndDate;
		}
		base_form.findField('EvnStick_setDate').setValue(evn_stick_set_date);
		// -----------------------------------------------------------------------------------------------------------------



		setCurrentDateTime({
			dateField: base_form.findField('EvnStick_setDate'),
			loadMask: false,
			setDate: (Ext.isEmpty(base_form.findField('StickFSSData_id').getValue()) && Ext.isEmpty(base_form.findField('EvnStick_setDate').getValue()) ? true : false),
			windowId: me.id,
			callback: function() {
				var advanceParams = me.advanceParams;
				var person_age = swGetPersonAge(me.advanceParams.Person_Birthday, base_form.findField('EvnStick_setDate').getValue());
				var person_fio = advanceParams.Person_Surname + ' ' + advanceParams.Person_Firname + ' ' + advanceParams.Person_Secname;

				if (advanceParams.EvnStick_Num) {
					base_form.findField('EvnStick_Num').setValue(advanceParams.EvnStick_Num);
				}

				this.filterStickCause();

				if ( person_age < 18 && Ext.isEmpty(base_form.findField('StickFSSData_id').getValue()) ) {
					index = base_form.findField('StickCause_id').getStore().findBy(function(rec) {
						if ( rec.get('StickCause_SysNick') == 'uhodnoreb' ) {
							return true;
						}
						else {
							return false;
						}
					});

					record = base_form.findField('StickCause_id').getStore().getAt(index);

					if (getRegionNick() != 'penza' && record && !base_form.findField('PridStickCauseDid_SysNick').getValue() && !base_form.findField('PridStickCause_SysNick').getValue()) {
						base_form.findField('StickCause_id').setValue(record.get('StickCause_id'));
						base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), record.get('StickCause_id'));
					}

					// Добавляем строку в EvnStickCarePersonGrid

					if(base_form.findField('Person_id').getValue() == -1){
						base_form.findField('Person_id').setValue(advanceParams.Person_id);
					}

					me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().loadData([{
						accessType: 'edit',
						EvnStickCarePerson_id: -1,
						Person_id: base_form.findField('Person_id').getValue(),
						RecordStatus_Code: 0,
						Person_Age: person_age,
						Person_Fio: person_fio
					}]);
					me.checkRebUhod();

					base_form.findField('EvnStickFullNameText').onTrigger3Click();
				}
				else {
					// Установить человека в поле "Выдан ФИО"
					base_form.findField('EvnStickFullNameText').setValue(person_fio);
					base_form.findField('Person_id').setValue(advanceParams.Person_id);
					if ( base_form.findField('EvnStickFullNameText').getValue == '' ) {
						base_form.findField('EvnStick_IsOriginal').disable();
					}

					me.checkFieldDisabled('EvnStickFullNameText');
				}

				var StickCause_SysNick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
				if (
					StickCause_SysNick
					&& (
						StickCause_SysNick.inlist(['uhod', 'uhodnoreb', 'uhodreb', 'rebinv', 'postvaccinal', 'vich'])
						|| StickCause_SysNick == 'karantin' && person_age < 18
					)
				) {
					me._showPanelEvnStickCarePerson();
				} else {
					me._hidePanelEvnStickCarePerson();
				}
			}.createDelegate(this)
		});


		me.setMaxDateForSetDate();

		me._setDefaultValueTo_EvnStick_stacBegDate();
		me._setDefaultValueTo_EvnStick_stacEndDate();

		base_form.findField('EvnStickFullNameText').focus(true, 250);
	},
	showAsEditOrView: function(){
		__l('showAsEditOrView');
		var win = this;
		var me = this;
		var regionNick = getRegionNick();
		var base_form = me.FormPanel.getForm();


		// В зависимости от accessType переопределяем this.action
		if(base_form.findField('accessType').getValue() == 'view'){
			me.action = 'view';
		}


		base_form.load({
			url: ((me.evnStickType == 1)?'/?c=Stick&m=loadEvnStickEditForm':'/?c=Stick&m=loadEvnStickDopEditForm'),
			params: {
				EvnStick_id: base_form.findField('EvnStick_id').getValue(),
				EvnStick_pid: base_form.findField('EvnStick_pid').getValue(),
				archiveRecord: me.archiveRecord,
				delDocsView: me.delDocsView ? 1 : 0
			},
			failure: function() {
				me.getLoadMask().hide();
				sw.swMsg.alert(
					langs('Ошибка'),
					langs('Ошибка при загрузке данных формы'),
					me.doHideForm
				);
			},
			success: function(frm, act) {
				var response_obj = Ext.util.JSON.decode(act.response.responseText);
				me.delAccessType = response_obj[0].delAccessType;
				me.cancelAccessType = response_obj[0].cancelAccessType;

				me.Lpu_id = base_form.findField('Lpu_id').getValue();

				if(me.action != 'view'){
					me.refreshFormPartsAccess();
				} else {
					me.checkEvnStickNumDouble();
				}

				if (response_obj && response_obj[0] && response_obj[0].RegistryESErrors) {
					var errors = response_obj[0].RegistryESErrors;
					var message;
					if (errors[0].RegistryESErrorStageType_id == 1) { // ошибки ФЛК
						message = 'При отправке ЛВН найдены ошибки:<br>';
						for (var index in errors) {
							if ( !Ext.isEmpty(errors[index].RegistryESError_Descr ) ) {
								message += errors[index].RegistryESError_Descr + '<br>';
							}
						}
					} else { // не принят в ФСС
						message = 'ЛВН не принят ФСС, ' + errors[0].RegistryESError_Descr
					}
					showPopupInfoMsg(message);
				}
				me.checkEvnStickNumDouble();
				me.checkSaveButtonEnabled();

				if ( regionNick != 'kz' ) {
					var field_EvnStick_Ser = base_form.findField('EvnStick_Ser');
					if (Ext.isEmpty(field_EvnStick_Ser.getValue())){
						field_EvnStick_Ser.setContainerVisible(false);
					} else {
						field_EvnStick_Ser.setContainerVisible(true);
					}
				}


				if( !Ext.isEmpty(base_form.findField('Person_Snils').getValue()) ) {
					me.Person_Snils = base_form.findField('Person_Snils').getValue();
					me._checkSnils();
				}

				me.setTitle(WND_STICK_ESTVIEW);
				
				me.enableEdit(false);

				if (me.action == 'edit') {
					// Если есть дубликаты то открыавем в режиме просмотра
					if(parseInt(base_form.findField('CountDubles').getValue()) > 0){
						sw.swMsg.alert(
							langs('Внимание'),
							langs('Данный ЛВН нельзя редактировать, т.к. на него есть дубликат.'),
							function(){}
						);
						me.action = 'view';
					}

					me.setTitle(WND_STICK_ESTEDIT);
					me.enableEdit(true);

					setCurrentDateTime({
						dateField: base_form.findField('EvnStick_setDate'),
						loadMask: false,
						setDate: false,
						windowId: me.id,
						callback: function() {
							me.filterStickCause();
						}
					});
					me.setMaxDateForSetDate();
				}


				if (act.result.data.isTubDiag) {
					me.isTubDiag = true;
				}



				if (getRegionNick() != 'kz') {
					me.checkGetEvnStickNumButton();
				}

				if(
					base_form.findField('EvnStickBase_IsFSS').getValue() === true // из ФСС
					&& base_form.findField('StickWorkType_id').getValue() == 2 //2.Работа по совместительству
				) {
					base_form.findField('Org_id').clearValue();
				}

				if (!Ext.isEmpty(base_form.findField('StickFSSData_id').getValue()) && getRegionNick() == 'vologda') {//#198568-3
					me.menuPrintActions.items.items[1].enable();
				}
				
				me.checkFieldDisabled('StickWorkType_id');
				if (me.link == true) {
					me.checkFieldDisabled('EvnStick_Num');
					me.checkFieldDisabled('EvnStick_Ser');
					me.checkFieldDisabled('EvnStick_setDate');
					me.checkFieldDisabled('EvnStickFullNameText');
					me.checkFieldDisabled('EvnStickLast_Title');
					me.checkFieldDisabled('StickOrder_id');
				}

				me.findById(win.id+'EStEF_EvnStickCarePersonPanel').hide();



				var record;
				var i;
				var index;

				
				// --------------------------------------------------------------
				// обработка поля врач в исходе если врача нет в списке //
				var msf_field = base_form.findField('MedStaffFact_id');
				if(msf_field.getValue()) {
					index = msf_field.getStore().findBy(function(rec) { 
						return  rec.get('MedStaffFact_id') == msf_field.getValue();
					});
					if(index == -1) {
						win.getMedPersonalInfo(msf_field.getValue(), 
						function(response) {
							var fio = response[0].Person_Fio;
							var lpu_nick = response[0].Org_Nick;

							msf_field.setRawValue(fio +' (' + lpu_nick + ')');
						});
					}
				}

				// ---------------------------------------------------------------

				/// no


				// -----------------------------------------------------------------------------------------------------
				// Обработка поля "ЛВН-продолжение" (EvnStick_NumNext) в блоке "Исход ЛВН"
				var evn_stick_oid = base_form.findField('EvnStick_oid').getValue();
				if( ! Ext.isEmpty(evn_stick_oid)){
					me.fetchAndSetEvnStickProd(evn_stick_oid);
				}
				// -----------------------------------------------------------------------------------------------------



				//// no



				// -----------------------------------------------------------------------------------------------------
				// Обработка полей "EvnStick_stacBegDate" и "EvnStick_stacEndDate" при редактировании или просмотре ЛВН
				me._setProcessValueTo_EvnStick_stacBegDate();
				me._setProcessValueTo_EvnStick_stacEndDate();
				// -----------------------------------------------------------------------------------------------------




				me.findById(win.id+'EStEF_btnSetMinDateFromPS').setVisible(true);
				me.findById(win.id+'EStEF_btnSetMaxDateFromPS').setVisible(true);

				if(me.action == 'edit'){
					if(me.parentClass.inlist(['EvnPL', 'EvnPLStom'])){
						me.findById(win.id+'EStEF_btnSetMinDateFromPS').setVisible(false);
						me.findById(win.id+'EStEF_btnSetMaxDateFromPS').setVisible(false);
					}
				}



				// no

				// -----------------------------------------------------------------------------------------------------
				// Обработка значения поля StickOrder_id
				var stick_order_id = base_form.findField('StickOrder_id').getValue();
				index = base_form.findField('StickOrder_id').getStore().findBy(function(rec) {
					if(rec.get('StickOrder_id') == stick_order_id){
						return true;
					}
					else {
						return false;
					}
				});
				record = base_form.findField('StickOrder_id').getStore().getAt(index);

				if (record && record.get('StickOrder_Code') == 2) {

					// EvnStickLast_Title
					base_form.findField('EvnStickLast_Title').setContainerVisible(true);
					base_form.findField('EvnStickLast_Title').setAllowBlank(false);

				}
				else {
					base_form.findField('EvnStick_prid').setValue(0);

					// EvnStickLast_Title
					base_form.findField('EvnStickLast_Title').setRawValue('');
					base_form.findField('EvnStickLast_Title').setContainerVisible(false);
					base_form.findField('EvnStickLast_Title').setAllowBlank(true);
				}
				// -----------------------------------------------------------------------------------------------------



				/// no


				// EvnStick_BirthDate
				base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
				base_form.findField('EvnStick_BirthDate').setContainerVisible(false);


				// EvnStick_sstBegDate
				base_form.findField('EvnStick_sstBegDate').setAllowBlank(true);
				base_form.findField('EvnStick_sstBegDate').setContainerVisible(false);


				// EvnStick_sstEndDate
				base_form.findField('EvnStick_sstEndDate').setAllowBlank(true);
				base_form.findField('EvnStick_sstEndDate').setContainerVisible(false);


				// EvnStick_sstNum
				base_form.findField('EvnStick_sstNum').setAllowBlank(true);
				base_form.findField('EvnStick_sstNum').setContainerVisible(false);


				base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(false);
				
				if (base_form.findField('StickWorkType_id').getValue() == 2){//#192800
					base_form.findField('EvnStick_IsRegPregnancy').clearValue();	
				}

				// Org_did
				base_form.findField('Org_did').setAllowBlank(true);
				base_form.findField('Org_did').setContainerVisible(false);



				//// no


				// -----------------------------------------------------------------------------------------------------
				// Обработка значения поля StickCause_id
				var stick_cause_id = base_form.findField('StickCause_id').getValue();
				index = base_form.findField('StickCause_id').getStore().findBy(function(rec) {
					if(rec.get('StickCause_id') == stick_cause_id){
						return true;
					}
					else {
						return false;
					}
				});

				record = base_form.findField('StickCause_id').getStore().getAt(index);
				if (record) {

					// StickCause - Причина нетрудоспособности
					switch (record.get('StickCause_SysNick')) {

						// Санаторно-курортное лечение
						case 'kurort':

						// Долечивание в санатории
						case 'dolsan':
							base_form.findField('EvnStick_sstBegDate').setAllowBlank(false);
							base_form.findField('EvnStick_sstBegDate').setContainerVisible(true);
							base_form.findField('EvnStick_sstEndDate').setContainerVisible(true);
							base_form.findField('EvnStick_sstNum').setContainerVisible(true);

							if (regionNick == 'astra') {
								base_form.findField('EvnStick_sstEndDate').setContainerVisible(true);
								if (record.get('StickCause_SysNick') == 'dolsan') {
									base_form.findField('EvnStick_sstNum').setAllowBlank(false);
									base_form.findField('EvnStick_sstEndDate').setAllowBlank(false);
								}
							}
							else if ( ! Ext.isEmpty(base_form.findField('StickLeaveType_id').getValue())) {
								base_form.findField('EvnStick_sstNum').setAllowBlank(false);
							}

							var value_PridEvnStickWorkRelease_endDate = base_form.findField('PridEvnStickWorkRelease_endDate').getValue();
							var pridStickLeaveTypeCode = me.getPridStickLeaveTypeCode();

							if (
								regionNick == 'ufa' &&
								pridStickLeaveTypeCode == '37' &&
								! Ext.isEmpty(value_PridEvnStickWorkRelease_endDate)
							) {
								base_form.findField('EvnStick_sstBegDate').disable();
							}

							var field_Org_did = base_form.findField('Org_did');
							if(regionNick == 'kz'){
								field_Org_did.setAllowBlank(true);
							} else {
								field_Org_did.setAllowBlank(false);
							}

							base_form.findField('Org_did').setContainerVisible(true);
							break;

						// Отпуск по беременноcти и родам
						case 'pregn':
							base_form.findField('EvnStick_BirthDate').setContainerVisible(true);

							if (base_form.findField('StickCauseDopType_id').getFieldValue('StickCauseType_Code') == '020') {
								base_form.findField('EvnStick_BirthDate').setAllowBlank(true);
							} else {
								base_form.findField('EvnStick_BirthDate').setAllowBlank(false);
							}

							// ТРЭБО СДЕЛАТЬ: Учесть пол пациента!
							base_form.findField('EvnStick_IsRegPregnancy').setContainerVisible(true);
							break;

						// ?????
						case 'adopt':
							base_form.findField('EvnStick_adoptDate').setContainerVisible(true);
							break;

						// Уход за больным членом семьи
						case 'uhod':

						// Уход за больным членом семьи
						case 'uhodnoreb':

						// Уход за больным ребенком до 7 лет с диагнозом по 255-ФЗ
						case 'uhodreb':

						// Ребенок-инвалид
						case 'rebinv':

						// ВИЧ-инфицированный ребенок
						case 'vich':

						// Заболевание ребенка из перечня Минздрава
						case 'zabrebmin':

						// Поствакцинальное осложнение или злокачественное новообразование у ребенка
						case 'postvaccinal':
							this.findById(win.id+'EStEF_EvnStickCarePersonPanel').show();
							break;

						// Карантин
						case 'karantin':
							var person_age = swGetPersonAge(me.PersonInfo.getFieldValue('Person_Birthday'), base_form.findField('EvnStick_setDate').getValue());
							if (person_age < 18) {
								me._showPanelEvnStickCarePerson();
							} else {
								me._hidePanelEvnStickCarePerson();
							}
							break;
					}
				}
				// -----------------------------------------------------------------------------------------------------


				// no


				me.checkRebUhod();




				// Дубликат == 2 или Оригинал == 1
				var stick_is_original = base_form.findField('EvnStick_IsOriginal').getValue();

				// Если Дубликат
				if (stick_is_original == 2) {

					var evn_stick_oid = base_form.findField('EvnStick_oid').getValue();
					var evn_stick_dop_pid = base_form.findField('EvnStickDop_pid').getValue();

					// Подгружаем Оригинал ЛВН
					me._loadStore_EvnStick_oid(function(){

						index = base_form.findField('EvnStickDop_pid').getStore().findBy(function(rec) {
							if ( rec.get('EvnStick_id') == evn_stick_oid ) {
								return true;
							} else {
								return false;
							}
						});

						record = base_form.findField('EvnStickDop_pid').getStore().getAt(index);

						if ( record ) {
							base_form.findField('EvnStickDop_pid').setValue(evn_stick_dop_pid);
						}

					});


					this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].disable();
					this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].disable();
					this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[1].disable();
					this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[1].disable();
					this.findById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[3].disable();
					this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[3].disable();

					base_form.findField('EvnStick_oid').setAllowBlank(false);
					base_form.findField('EvnStick_oid').setContainerVisible(true);

					if( getRegionNick() != 'kz' ) {
						this.findById(win.id+'EStEF_ESSConsentDelete').show();
					}
					

					if ( this.action != 'view' ) {
						this.findById(win.id+'EStEF_btnSetMinDateFromPS').setVisible(false);
						this.findById(win.id+'EStEF_btnSetMaxDateFromPS').setVisible(false);
					}

				}




				/// yes !!!

				// -----------------------------------------------------------------------------------------------------------------
				// Обработка значения поля StickWorkType_id - Тип занятости
				var evn_stick_dop_pid = base_form.findField('EvnStickDop_pid').getValue();
				var stick_work_type_id = base_form.findField('StickWorkType_id').getValue();
				if (stick_work_type_id == 2){

					me.findById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].disable();
					me.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].disable();

					// Загружаем список ЛВН, выданных по основному месту работы
					base_form.findField('EvnStickDop_pid').getStore().load({
						params: {
							'EvnStick_mid': base_form.findField('EvnStick_mid').getValue(),
							'EvnStick_id': base_form.findField('EvnStickDop_pid').getValue()
						},
						callback: function() {
							if (base_form.findField('EvnStickDop_pid').getStore().getCount() == 0 || !Ext.isEmpty(base_form.findField('EvnStick_NumPar').getValue())) {
								base_form.findField('EvnStickDop_pid').getStore().loadData([
									{
										EvnStick_id: -1,
										EvnStick_Num: !Ext.isEmpty(base_form.findField('EvnStick_NumPar').getValue()) ? base_form.findField('EvnStick_NumPar').getValue() : 'Отсутствует',
										EvnStick_Title: !Ext.isEmpty(base_form.findField('EvnStick_NumPar').getValue()) ? base_form.findField('EvnStick_NumPar').getValue() : 'Отсутствует'
									}
								]);
								base_form.findField('EvnStickDop_pid').setValue(-1);
								me.findById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].enable();
								me.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].enable();
							}
							else {
								index = base_form.findField('EvnStickDop_pid').getStore().findBy(function(rec) {
									if(rec.get('EvnStick_id') == evn_stick_dop_pid){
										return true;
									}
									else {
										return false;
									}
								});
								record = base_form.findField('EvnStickDop_pid').getStore().getAt(index);

								if ( record ) {
									base_form.findField('EvnStickDop_pid').setValue(evn_stick_dop_pid);
								}


								if (me.action != 'view') {

									base_form.findField('EvnStick_BirthDate').disable();
									base_form.findField('EvnStick_irrDate').disable();
									base_form.findField('EvnStick_IsDisability').disable();
									base_form.findField('InvalidGroupType_id').disable();
									base_form.findField('EvnStick_IsRegPregnancy').disable();
									base_form.findField('EvnStick_mseDate').disable();
									base_form.findField('EvnStick_mseExamDate').disable();
									base_form.findField('EvnStick_mseRegDate').disable();
									base_form.findField('EvnStick_sstBegDate').disable();
									base_form.findField('EvnStick_sstEndDate').disable();
									base_form.findField('EvnStick_sstNum').disable();
									base_form.findField('EvnStick_stacBegDate').disable();
									base_form.findField('EvnStick_stacEndDate').disable();
									base_form.findField('EvnStickFullNameText').disable();
									base_form.findField('UAddress_AddressText').disable();
									base_form.findField('Lpu_oid').disable();
									base_form.findField('Org_did').disable();
									base_form.findField('StickCauseDopType_id').disable();
									base_form.findField('StickIrregularity_id').disable();
									base_form.findField('EvnStick_oid').disable();

									me.findById(win.id+'EStEF_btnSetMinDateFromPS').setVisible(false);
									me.findById(win.id+'EStEF_btnSetMaxDateFromPS').setVisible(false);
								}
							}

							me.findById(win.id+'EStEF_EvnStickCarePersonPanel').fireEvent('expand', me.findById(win.id+'EStEF_EvnStickCarePersonPanel'));
							me.findById(win.id+'EStEF_EvnStickWorkReleasePanel').fireEvent('expand', me.findById(win.id+'EStEF_EvnStickWorkReleasePanel'));
						}.createDelegate(this)
					});

					Ext.getCmp('updateEvnStickWorkReleaseGrid').show();

				}
				else {
					me.evnStickType = 1;

					me.findById(win.id+'EStEF_EvnStickWorkReleasePanel').fireEvent('expand', me.findById(win.id+'EStEF_EvnStickWorkReleasePanel'));

					if (me.action != 'view'){
						me.findById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].enable();
						me.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getTopToolbar().items.items[0].enable();
					}

					Ext.getCmp('updateEvnStickWorkReleaseGrid').hide();

				}

				// Тип занятости
				if ( ! stick_work_type_id || stick_work_type_id == 3) {
					base_form.findField('EvnStickDop_pid').setAllowBlank(true);
					base_form.findField('EvnStickDop_pid').setContainerVisible(false);

					me.findById(win.id+'EStEF_OrgFieldset').hide();
				}
				else {

					me.findById(win.id+'EStEF_OrgFieldset').show();

					if(stick_work_type_id == 2){
						base_form.findField('EvnStickDop_pid').setAllowBlank(false);
						base_form.findField('EvnStickDop_pid').setContainerVisible(true);
					}
					else {
						base_form.findField('EvnStickDop_pid').setAllowBlank(true);
						base_form.findField('EvnStickDop_pid').setContainerVisible(false);
					}
				}
				// -----------------------------------------------------------------------------------------------------------------





				win.checkOrgFieldDisabled();





				// -----------------------------------------------------------------------------------------------------------------
				// Обработка значения поля StickLeaveType_id (Исход ЛВН)
				me._setFromStickLeave_Save();
				var stick_leave_type_id = base_form.findField('StickLeaveType_id').getValue();
				index = base_form.findField('StickLeaveType_id').getStore().findBy(function(rec) {
					if (rec.get('StickLeaveType_id') == stick_leave_type_id){
						return true;
					}
					else {
						return false;
					}
				});
				record = base_form.findField('StickLeaveType_id').getStore().getAt(index);

				if ( ! record || ! record.get('StickLeaveType_id') ) {
					base_form.findField('EvnStick_disDate').setContainerVisible(false);
					base_form.findField('EvnStick_disDate').setAllowBlank(true);
					base_form.findField('EvnStick_disDate').setRawValue('');
					base_form.findField('Lpu_oid').clearValue();
					base_form.findField('Lpu_oid').setContainerVisible(false);
					base_form.findField('EvnStick_NumNext').setContainerVisible(false);
					base_form.findField('MedStaffFact_id').clearValue();
					base_form.findField('MedStaffFact_id').setAllowBlank(true);
					base_form.findField('MedStaffFact_id').setContainerVisible(false);
				}
				else {
					base_form.findField('EvnStick_disDate').setContainerVisible(true);
					base_form.findField('EvnStick_disDate').setAllowBlank(false);
					base_form.findField('MedStaffFact_id').setContainerVisible(true);
					base_form.findField('MedStaffFact_id').setAllowBlank(false);

					if ( record.get('StickLeaveType_Code').inlist([ '31', '32', '33', '37' ]) ) {
						base_form.findField('Lpu_oid').setContainerVisible(true);
					} else {
						base_form.findField('Lpu_oid').setContainerVisible(false);
					}

					if ( record.get('StickLeaveType_Code').inlist([ '31', '37' ]) ) {
						base_form.findField('EvnStick_NumNext').setContainerVisible(true);
					} else {
						base_form.findField('EvnStick_NumNext').setContainerVisible(false);
					}
				}
				// -----------------------------------------------------------------------------------------------------------------

				// установка доступности кнопок подписания при просмотре
				if ( me.action == 'view' ) {
					// Кнопка "Подписать" в блоке "Исход ЛВН"
					// Закрываем доступ
					me._closeAccessToField_StickLeave_Sign();
					// Проверяем доступность блока и открываем доступ при необходимости
					if(me._checkAccessToField_StickLeave_Sign() == true){
						me._openAccessToField_StickLeave_Sign();
					}
					// -------------------------------------------------------------------------------------------------------------

					// Кнопка "Подписать режим" 
					me._closeAccessToField_swSignStickIrr();
					if(me._checkAccessToField_swSignStickIrr() == true) {
						me._openAccessToField_swSignStickIrr();
					}
				}


				// -----------------------------------------------------------------------------------------------------
				// Org_id
				var org_id = base_form.findField('Org_id').getValue();
				if (org_id != null && Number(org_id) > 0){
					base_form.findField('Org_id').getStore().load({
						callback: function(records, options, success) {
							if(success){
								base_form.findField('Org_id').setValue(org_id);
							}
						},
						params: {
							Org_id: org_id,
							OrgType: 'org'
						}
					});
				}
				// -----------------------------------------------------------------------------------------------------




				if (regionNick != 'kz' && ! Ext.isEmpty(base_form.findField('StickCause_did').getValue())){
					base_form.findField('EvnStick_StickDT').setAllowBlank(false);
					base_form.findField('EvnStick_StickDT').setContainerVisible(true);
				}

				base_form.findField('StickCauseDopType_id').fireEvent('change', base_form.findField('StickCauseDopType_id'), base_form.findField('StickCauseDopType_id').getValue());
				base_form.findField('StickCause_did').fireEvent('change', base_form.findField('StickCause_did'), base_form.findField('StickCause_did').getValue());





				// -----------------------------------------------------------------------------------------------------
				// Org_did
				var org_did = base_form.findField('Org_did').getValue();
				if (org_did != null && Number(org_did) > 0) {
					base_form.findField('Org_did').getStore().load({
						callback: function(records, options, success) {
							if (success) {
								base_form.findField('Org_did').setValue(org_did);
							}
						},
						params: {
							Org_id: org_did,
							OrgType: 'org'
						}
					});
				}
				// -----------------------------------------------------------------------------------------------------





				if (me.evnStickType == 1) {
					me.findById(win.id+'EStEF_EvnStickCarePersonPanel').fireEvent('expand', me.findById(win.id+'EStEF_EvnStickCarePersonPanel'));
				}



				if (me.action == 'edit'){
					if(me.evnStickType == 2){
						base_form.findField('EvnStick_Ser').focus(true, 250);
					}
					else if(me.link == true){
						base_form.findField('Org_id').focus(true, 250);
					}
					else {
						base_form.findField('EvnStickFullNameText').focus(true, 250);
					}
				}
				else {
					me._focusButtonCancel();
				}



				// -----------------------------------------------------------------------------------------------------
				// StickLeaveType_id
				if (base_form.findField('StickLeaveType_id').getValue()) {

					// Статус подписи документы
					me.getEvnStickSignStatus({object: 'leave'});
				}
				// -----------------------------------------------------------------------------------------------------





				// -----------------------------------------------------------------------------------------------------
				// Обработка значения поля StickIrregularity_id
				var stick_irregularity_id = base_form.findField('StickIrregularity_id').getValue();
				base_form.findField('StickIrregularity_id').fireEvent('change', base_form.findField('StickIrregularity_id'), stick_irregularity_id);
				if (base_form.findField('StickIrregularity_id').getValue()) {
					// Статус подписи документы
					me.getEvnStickSignStatus({object: 'irr'});
				}
				// -----------------------------------------------------------------------------------------------------



				// загружаем список периодов освобождений если он не был загружен ранее
				if(me._isLoaded_WorkRelease() == false){
					me._defaultLoad_WorkRelease();
				}
				
				if (isMseDepers()) {
					base_form.findField('EvnStickFullNameText').setValue('* * *');
					Ext.getCmp(this.id + 'buttonPrint').hide();
				}

			}.createDelegate(this)

		});
	},
	// -----------------------------------------------------------------------------------------------------------------







	// -----------------------------------------------------------------------------------------------------------------
	// DO
	// -----------------------------------------------------------------------------------------------------------------





	// Печать (Почему вызов только в функции CheckWorkRelease()???)
	doPrintEvnStick: function() {
		__l('printEvnStick');
		var me = this;
		var params = new Object(),
			_this = this,
			form = this.FormPanel.getForm();

		params.EvnStick_id = form.findField('EvnStick_id').getValue();
		params.evnStickType = this.evnStickType;
		params.StickLeaveType_id = form.findField('StickLeaveType_id').getValue();
		params.StickOrder_id = form.findField('StickOrder_id').getValue();
		params.StickCause_SysNick = form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
		params.PridStickLeaveType_Code = this.getPridStickLeaveTypeCode();
		params.RegistryESStorage_id = form.findField('RegistryESStorage_id').getValue()
		params.firstEndDate = null;


		//Берём дату окончания из первого периода нетрудоспособности, заведённого орагнизацией, указанной в поле Санаторий
		if (form.findField('Org_did').getValue() == getGlobalOptions().org_id) {
			this.findById(_this.id+'EStEF_EvnStickWorkReleaseGrid').getStore().each(function(rec){
				if (Ext.isEmpty(params.firstEndDate) || params.firstEndDate > rec.get('EvnStickWorkRelease_endDate')){
					params.firstEndDate = rec.get('EvnStickWorkRelease_endDate');
				}
			});
		}

		getWnd('swEvnStickPrintWindow').show(params);
	},
	// Печать согласия
	doPrintESSConsent: function() {
		__l('doPrintESSConsent');
		var me = this;
		var bf = this.FormPanel.getForm(),
			evn_stick_id = bf.findField('EvnStick_id').getValue(),
			person_id = bf.findField('Person_id').getValue(),
			stickcause_id = bf.findField('StickCause_id').getValue(),
			consent_dt = bf.findField('EvnStickBase_consentDT').getValue();
		if (!consent_dt) return false;
		consent_dt = consent_dt.format('d.m.Y');
		printBirt({
			'Report_FileName': 'Person_Soglasie_Stick.rptdesign',
			'Report_Params': '&paramPerson=' + person_id + '&paramLpu=' + getGlobalOptions().lpu_id + '&paramStickCause=' + stickcause_id + '&paramDate=' + consent_dt,
			'Report_Format': 'pdf'
		});
	},
	// -----------------------------------------------------------------------------------------------------------------






	// -----------------------------------------------------------------------------------------------------------------
	// Открываем другие окна
	// -----------------------------------------------------------------------------------------------------------------
	// Дней нетрудоспособности в году
	openEvnStickWorkReleaseCalculationWindow: function() {
		__l('openEvnStickWorkReleaseCalculationWindow');
		var me = this;
		var base_form = this.FormPanel.getForm();

		var params = {
			Person_id: this.PersonInfo.getFieldValue('Person_id'),
			StickCause_id: base_form.findField('StickCause_id').getValue()
		};

		getWnd('swEvnStickWorkReleaseCalculationWindow').show(params);
	},



	openESSConsent: function(action) {
		__l('openESSConsent');
		var me = this;
		var win = this,
			bf = this.FormPanel.getForm(),
			base_form = bf,
			evn_stick_id = bf.findField('EvnStick_id').getValue(),
			consent_dt = bf.findField('EvnStickBase_consentDT').getValue(),
			evnstick_disdate = bf.findField('EvnStick_disDate').getValue();

		if(!Ext.isEmpty(evnstick_disdate) && bf.findField('EvnStick_IsOriginal').getValue() != 2 && bf.findField('StickWorkType_id').getValue() != 2) return false;
		if(action == 'add' && consent_dt) return false;
		if(action == 'edit' && !consent_dt) return false;

		var params = {
			EvnStickBase_consentDT: consent_dt,
			EvnStick_setDate: bf.findField('EvnStick_setDate').getValue(),
			EvnStick_disDate: bf.findField('EvnStick_disDate').getValue(),
			allowPrint: base_form.findField('EvnStickFullNameText').getValue() && base_form.findField('StickCause_id').getValue(),
			callback: function(EvnStickBase_consentDT) {
				if (!Ext.isEmpty(EvnStickBase_consentDT)) {
					bf.findField('EvnStickBase_consentDT').setValue(EvnStickBase_consentDT);

					if (base_form.findField('EvnStickFullNameText').getValue() && base_form.findField('StickCause_id').getValue()) {
						win.doPrintESSConsent();
					}
					if (getRegionNick() != 'kz' && !win.checkIsLvnELN()) {
						base_form.findField('EvnStick_Num').setValue('');// удаляем номер если он не из хранилища номеров
					}
					win.checkGetEvnStickNumButton();
				}
			}
		};

		getWnd('swEvnStickESSConfirmEditWindow').show(params);
	},
	openEvnStickListWindow: function() {
		__l('openEvnStickListWindow');
		var me = this;
		var base_form = this.FormPanel.getForm();

		if ( base_form.findField('EvnStickLast_Title').disabled ) {
			return false;
		}

		var swEvnStickListWindows = [];
		if (typeof sw.windowCounter == 'object'
			&& (!Ext.isEmpty(sw.windowCounter['swEvnStickListWindow']) || sw.windowCounter['swEvnStickListWindow'] != 0)
		) {
			var count = sw.windowCounter['swEvnStickListWindow'];
			for(var i=0;i<=count;i++){
				swEvnStickListWindows.push('swEvnStickListWindow'+i);
			}
		} else {
			swEvnStickListWindows.push('swEvnStickListWindow0');
		}

		for(var a=0;a<swEvnStickListWindows.length;a++){
			if ( Ext.get(swEvnStickListWindows[a]) && Ext.get(swEvnStickListWindows[a]).isVisible() ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Окно просмотра списка ЛВН уже открыто'));
				return false;
			}
		}

		if ( !base_form.findField('Person_id').getValue() || Number(base_form.findField('Person_id').getValue()) <= 0 ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Не выбран человек, которому выдается ЛВН'), function() {
				base_form.findField('EvnStickFullNameText').focus(true);
			});
			return false;
		}

		var params = new Object();

		params.callback = function(data) {
			if ( !data ) {
				return false;
			}

			base_form.findField('EvnStick_prid').setValue(data.EvnStick_id);
			base_form.findField('PridStickLeaveType_Code2').setValue(data.PridStickLeaveType_Code);
			base_form.findField('PridStickCause_SysNick').setValue(data.StickCause_SysNick);
			base_form.findField('PridStickCauseDid_SysNick').setValue(data.StickCauseDid_SysNick);
			base_form.findField('MaxDaysLimitAfterStac').setValue(data.MaxDaysLimitAfterStac);
			this.getWorkReleaseSumm(data.EvnStick_id);
			base_form.findField('EvnStickLast_Title').setValue(data.title);
			base_form.findField('PridEvnStickWorkRelease_endDate').setValue(Ext.util.Format.date(data.EvnStickWorkRelease_endDate, 'd.m.Y'));

			if ( typeof data.endDate == 'object' ) {
				base_form.findField('EvnStick_setDate').setValue(data.EvnStick_setDate.add(Date.DAY, 1));
				base_form.findField('EvnStick_setDate').setMinValue(data.EvnStick_disDate.add(Date.DAY, 1));
			}

			if (!getRegionNick().inlist(['kz','penza']) && me.action == 'add') {
				var StickCause_id, index;
				if (me.getPridStickLeaveTypeCode() == '37') {
					index = base_form.findField('StickCause_id').getStore().findBy(function(rec) {
						return rec.get('StickCause_SysNick') == 'dolsan';
					});
					if (index != -1) {
						StickCause_id = base_form.findField('StickCause_id').getStore().getAt(index).get('StickCause_id');
						base_form.findField('StickCause_id').setValue(StickCause_id);
						base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), StickCause_id);
					}
				} else {
					base_form.findField('StickCause_id').setValue(data.StickCause_did || data.StickCause_id);
					base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), data.StickCause_did || data.StickCause_id);
				}
			}

			if ( data.Org_id ) {
				base_form.findField('Org_id').getStore().load({
					callback: function(records, options, success) {
						if ( success ) {
							base_form.findField('Org_id').setValue(data.Org_id);
						}
					},
					params: {
						Org_id: data.Org_id,
						OrgType: 'org'
					}
				});
			}

			if ( data.EvnStick_OrgNick ) {
				base_form.findField('EvnStick_OrgNick').setValue(data.EvnStick_OrgNick);
			}
			else {
				base_form.findField('EvnStick_OrgNick').setRawValue('');
			}

			if ( data.Post_Name ) {
				base_form.findField('Post_Name').setValue(data.Post_Name);
			}
			else {
				base_form.findField('Post_Name').setRawValue('');
			}
			
			if (!getRegionNick().inlist(['kz','penza'])) {
				if (this.getPridStickLeaveTypeCode() == '37') {
					base_form.findField('StickCause_id').setValueByCode(8);
					if (!!data.Lpu_oid) {
						base_form.findField('Org_did').setValueById(data.Lpu_oid);
					}
				} else if (!!data.StickCause_did) {
					base_form.findField('StickCause_id').setValue(data.StickCause_did);
				} else {
					base_form.findField('StickCause_id').setValue(data.StickCause_id);
				}
				
				base_form.findField('StickCause_id').fireEvent('change', base_form.findField('StickCause_id'), base_form.findField('StickCause_id').getValue());
				
				var StickCause_SysNick = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
				
				if (StickCause_SysNick && StickCause_SysNick.inlist(['uhod', 'uhodnoreb', 'uhodreb', 'rebinv', 'vich', 'postvaccinal', 'zabrebmin'])) {
					me._loadStoreEvnStickCarePerson({
						EvnStick_id: data.EvnStick_id
					});
				}
			}
			
			if ( 
				this.parentClass == 'EvnPS' 
				&& me.checkHasDvijeniaInStac24()
				&& base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') != 'pregn' // отпуск по беременности и родам
			) {
				if (data.EvnStick_stacBegDate) {
					base_form.findField('EvnStick_stacBegDate').setValue(data.EvnStick_stacBegDate);
				} else if (this.advanceParams && this.advanceParams.stacBegDate) {
					base_form.findField('EvnStick_stacBegDate').setValue(this.advanceParams.stacBegDate);
				}
			} else {
				base_form.findField('EvnStick_stacBegDate').setValue(null);
			}

			if (
				getRegionNick() == 'ufa'
				&& base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'dolsan'
				&& this.getPridStickLeaveTypeCode() == '37'
				&& ! Ext.isEmpty(base_form.findField('PridEvnStickWorkRelease_endDate').getValue())
			) {
				base_form.findField('EvnStick_sstBegDate').setValue(base_form.findField('PridEvnStickWorkRelease_endDate').getValue());

				base_form.findField('EvnStick_sstBegDate').disable();
			}
		}.createDelegate(this);

		params.StickWorkType_id = base_form.findField('StickWorkType_id').getValue();
		params.EvnStick_id = base_form.findField('EvnStick_id').getValue();
		params.onHide = function() {
			base_form.findField('EvnStickLast_Title').focus();
		}.createDelegate(this);
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_id = base_form.findField('Person_id').getValue();
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');
		params.advanceParams = this.advanceParams;
		params.EvnStickOriginal_prid = base_form.findField('EvnStick_oid').getFieldValue('EvnStick_prid');

		getNewWnd('swEvnStickListWindow').show(params);
	},
	openEvnStickCarePersonEditWindow: function(action) {
		__l('openEvnStickCarePersonEditWindow');
		var me = this;
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view'])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnStickCarePersonEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования пациента, нуждающегося в уходе, уже открыто'));
			return false;
		}

		var base_form = this.FormPanel.getForm(),
			win = this;
		var grid = this.findById(win.id+'EStEF_EvnStickCarePersonGrid');
		var params = new Object();

		if ( action == 'add' ) {
			if ( grid.getStore().getCount() >= 2 ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Разрешено добавление только 2-х записей о пациентах, нуждающихся в уходе'));
				return false;
			}
		}

		params.action = action;
		params.evnStickSetDate = base_form.findField('EvnStick_setDate').getValue();
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.evnStickCarePersonData != 'object' ) {
				return false;
			}

			data.evnStickCarePersonData.RecordStatus_Code = 0;

			var record = grid.getStore().getById(data.evnStickCarePersonData.EvnStickCarePerson_id);

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.evnStickCarePersonData.RecordStatus_Code = 2;
				}

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.evnStickCarePersonData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnStickCarePerson_id') ) {
					grid.getStore().removeAll();
				}

				data.evnStickCarePersonData.EvnStickCarePerson_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.evnStickCarePersonData ], true);
			}

			this.checkRebUhod();
		}.createDelegate(this);
		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
			params.formParams.Person_pid = base_form.findField('Person_id').getValue();
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnStickCarePerson_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swEvnStickCarePersonEditWindow').show(params);
	},
	openEvnStickWorkReleaseEditWindow: function(action) {
		__l('openEvnStickWorkReleaseEditWindow');
		var win = this;
		var me = this;

		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view'])) ) {
			return false;
		}

		if ( this.action == 'view' /*|| this.evnStickType == 2*/ ) {
			if ( action == 'add') {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swEvnStickWorkReleaseEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования освобождения от работы уже открыто'));
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var grid = this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid');
		var disableBegDate = null;
		var begDate = null;
		var endDate = null;
		var maxDate = null;
		var sumDate = parseInt(base_form.findField('WorkReleaseSumm').getValue()) || 0; // подставляем сумму из предыдущих ЛВН.

		var params = new Object();
		params.StickReg = this.StickReg;
		params.CurLpuSection_id = this.CurLpuSection_id;
		params.CurLpuUnit_id = this.CurLpuUnit_id;
		params.CurLpuBuilding_id = this.CurLpuBuilding_id;
		params.IngoreMSFFilter = this.IngoreMSFFilter;
		params.isTubDiag = this.isTubDiag;
		var access_type = 'view';
		var selected_record = grid.getSelectionModel().getSelected();
		var signatures = {};
		var EvnStickWorkRelease_IsInReg = null;

		if ( selected_record ) {
			access_type = selected_record.get('accessType');
			EvnStickWorkRelease_IsInReg = selected_record.get('EvnStickWorkRelease_IsInReg');

			// Даннные о подписаниии должны сохраняться
			signatures = {
				Signatures_mid: selected_record.get('Signatures_mid'),
				SMPStatus_id: selected_record.get('SMPStatus_id'),
				SMP_Status_Name: selected_record.get('SMP_Status_Name'),
				SMP_updDT: selected_record.get('SMP_updDT'),
				SMP_updUser_Name: selected_record.get('SMP_updUser_Name'),
				Signatures_wid: selected_record.get('Signatures_wid'),
				SVKStatus_id: selected_record.get('SVKStatus_id'),
				SVK_Status_Name: selected_record.get('SVK_Status_Name'),
				SVK_updDT: selected_record.get('SVK_updDT'),
				SVK_updUser_Name: selected_record.get('SVK_updUser_Name')
			}
		}

		if (access_type != 'edit' && action == 'edit') {
			action = 'view';
		}

		if (getRegionNick() != 'kz' && EvnStickWorkRelease_IsInReg == 2 && action == 'edit') {
			action = 'view';
		}

		if (
			getRegionNick() == 'ufa'
			&& win.getPridStickLeaveTypeCode() == '37'
			&& base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'dolsan'
			&& base_form.findField('Org_did').getValue() == getGlobalOptions().org_id
			&& !Ext.isEmpty(base_form.findField('EvnStick_sstBegDate'))
		) {
			var f_s_record = null;
			grid.getStore().each(function(record){
				if (
					record.get('Org_id') == base_form.findField('Org_did').getValue()
					&& (!f_s_record || record.get('EvnStickWorkRelease_begDate') < f_s_record.get('EvnStickWorkRelease_begDate'))
				) {
					f_s_record = record;
				}
			});
			if ((action == 'add' && !f_s_record) || (action == 'edit' && f_s_record && f_s_record.id == selected_record.id)) {
				begDate = base_form.findField('EvnStick_sstBegDate').getValue();
				disableBegDate = true;
			}
		}

		if (getRegionNick() == 'kz') {
			if (this.parentClass == 'EvnPS') {
				begDate = base_form.findField('EvnStick_stacBegDate').getValue();
				endDate = base_form.findField('EvnStick_stacEndDate').getValue();
			} else {
				begDate = base_form.findField('EvnStick_setDate').getValue();
			}
		}

		if ( action == 'add' ) {
			var maxCount = getRegionNick()=='kz' ? 4 : 3;
			if ( grid.getStore().getCount() >= maxCount ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Разрешено добавление только ')+maxCount+langs('-х записей об освобождении от работы'));
				return false;
			}
			var curSumDate = 0;
			grid.getStore().each(function(record) {
				if ( record && record.get('EvnStickWorkRelease_endDate') != '' ) {
					if (!maxDate || record.get('EvnStickWorkRelease_endDate') > maxDate) {
						maxDate = record.get('EvnStickWorkRelease_endDate');
					}
					// считаем сумму периодов
					curSumDate = curSumDate + Math.round((record.get('EvnStickWorkRelease_endDate') - record.get('EvnStickWorkRelease_begDate')) / 86400000) + 1;
				}
			});
			sumDate +=  curSumDate;

		} else {

			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnStickWorkRelease_id') ) {
				return false;
			}

			var selrecord = grid.getSelectionModel().getSelected();
			var curSumDate = 0;
			grid.getStore().each(function(record) {
				if ( record && record.get('EvnStickWorkRelease_endDate') != '' && (record.get('EvnStickWorkRelease_begDate') < selrecord.get('EvnStickWorkRelease_begDate')) ) {
					if (!maxDate || record.get('EvnStickWorkRelease_endDate') > maxDate) {
						maxDate = record.get('EvnStickWorkRelease_endDate');
					}
					// считаем сумму периодов
					curSumDate = curSumDate + Math.round((record.get('EvnStickWorkRelease_endDate') - record.get('EvnStickWorkRelease_begDate')) / 86400000) + 1;
				}
			});
			sumDate += curSumDate;
		}

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.evnStickWorkReleaseData != 'object' ) {
				return false;
			}

			data.evnStickWorkReleaseData.RecordStatus_Code = 0;

			var record;
			
			var index = grid.getStore().findBy(function(rec) {
				return rec.data.EvnStickWorkRelease_id == data.evnStickWorkReleaseData.EvnStickWorkRelease_id;
			});

			if (index != -1) {
				record = grid.getStore().getAt(index);
			}

			if ( record ) {
				if ( record.get('RecordStatus_Code') == 1 ) {
					data.evnStickWorkReleaseData.RecordStatus_Code = 2;
				}

				Ext.apply(data.evnStickWorkReleaseData, signatures);

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});
				var ESWRChanged = false;
				for ( i = 0; i < grid_fields.length; i++ ) {
					if (record.get(grid_fields[i]) != data.evnStickWorkReleaseData[grid_fields[i]]) {
						ESWRChanged = true;
					}
					record.set(grid_fields[i], data.evnStickWorkReleaseData[grid_fields[i]]);
				}

				if( getRegionNick().inlist(['penza', 'buryatiya']) && ESWRChanged ) { // освобождение было изменено

					if( record.get('SMPStatus_id') == 1 || record.get('SVKStatus_id') == 1 ) {//освобождение было подписано
						sw.swMsg.alert('Предупреждение', 'В освобождение от работы были внесены изменения, необходимо подписать документ.');	
					}
					
					record.set('SMPStatus_id', 2)
					record.set('SMP_Status_Name', 'Документ не подписан');
					record.set('SMP_updDT', null);
					record.set('SMP_updUser_Name', null);
					record.set('SVKStatus_id', 2);
					record.set('SVK_Status_Name', 'Документ не подписан');
					record.set('SVK_updDT', null);
					record.set('SVK_updUser_Name', null);

					//блокируем верификацию документа
					win.menuActions_WorkRelease.items.get('leaveActionsCheck').disable();
					win.menuActions_WorkRelease.items.get('leaveActionsListVK').disable();


				}

				record.commit();
			}
			else {
				if ( 
					grid.getStore().getCount() == 1 
					&& !grid.getStore().getAt(0).get('EvnStickWorkRelease_id') 
					&& !grid.getStore().getAt(0).get('StickFSSType_Name')
				) {
					grid.getStore().removeAll();
				}

				data.evnStickWorkReleaseData.EvnStickWorkRelease_id = -swGenTempId(grid.getStore());
				data.evnStickWorkReleaseData.SMPStatus_id = 2;
				data.evnStickWorkReleaseData.SMP_Status_Name = 'Документ не подписан';
				data.evnStickWorkReleaseData.SVKStatus_id = 2;
				data.evnStickWorkReleaseData.SVK_Status_Name = 'Документ не подписан';

				grid.getStore().loadData([ data.evnStickWorkReleaseData ], true);
			}

			// Разворачиваем панель "Исход"
			this.findById(win.id+'EStEF_StickLeavePanel').expand();
			this.checkLastEvnStickWorkRelease();

			if(this.action != 'view') {
				this.refreshFormPartsAccess();
			}

			this.checkSaveButtonEnabled();
			this.loadMedStaffFactList();

			me._closeAccessToField_WorkRelease_SignVK();
			if(me._checkAccessToField_WorkRelease_SignVK()) {
				me._openAccessToField_WorkRelease_SignVK();
			}

			base_form.findField('StickLeaveType_id').fireEvent('change', base_form.findField('StickLeaveType_id'), base_form.findField('StickLeaveType_id').getValue());
		}.createDelegate(this);
		params.formParams = new Object();
		params.disableBegDate = disableBegDate;
		params.begDate = begDate;
		params.endDate = endDate;
		params.maxDate = maxDate;
		params.sumDate = sumDate;
		params.curSumDate = curSumDate;

		var recordsc = base_form.findField('StickCause_id').getStore().getById(base_form.findField('StickCause_id').getValue());
		if ( recordsc ) {
			params.StickCause_SysNick = recordsc.get('StickCause_SysNick');
		}

		var recordsoid = base_form.findField('StickOrder_id').getStore().getById(base_form.findField('StickOrder_id').getValue());
		if ( recordsoid ) {
			params.StickOrder_Code = recordsoid.get('StickOrder_Code');
		}

		params.parentClass = this.parentClass;
		// данные о том кому выдается ЛВН
		params.Person_id = this.PersonInfo.getFieldValue('Person_id');
		params.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
		params.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
		params.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
		params.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');

		// данные о больном.
		var carePersonGrid = this.findById(win.id+'EStEF_EvnStickCarePersonGrid');
		if ( carePersonGrid.getStore().getCount() > 0 && carePersonGrid.getStore().getAt(0).get('EvnStickCarePerson_id') ) {
			params.StickPerson_Birthday = carePersonGrid.getStore().getAt(0).get('Person_Birthday');
			params.StickPerson_Firname = carePersonGrid.getStore().getAt(0).get('Person_Firname');
			params.StickPerson_Secname = carePersonGrid.getStore().getAt(0).get('Person_Secname');
			params.StickPerson_Surname = carePersonGrid.getStore().getAt(0).get('Person_Surname');
		} else {
			params.StickPerson_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
			params.StickPerson_Firname = this.PersonInfo.getFieldValue('Person_Firname');
			params.StickPerson_Secname = this.PersonInfo.getFieldValue('Person_Secname');
			params.StickPerson_Surname = this.PersonInfo.getFieldValue('Person_Surname');
		}

		params.EvnStick_setDate = base_form.findField('EvnStick_setDate').getValue();
		params.EvnStick_IsOriginal = base_form.findField('EvnStick_IsOriginal').getValue();
		params.evnStickType = this.evnStickType;
		params.EvnStick_stacBegDate = base_form.findField('EvnStick_stacBegDate').getValue();
		params.EvnStick_stacEndDate = base_form.findField('EvnStick_stacEndDate').getValue();
		params.EvnStick_IsOriginal = base_form.findField('EvnStick_IsOriginal').getValue();
		params.isHasDvijeniaInStac24 = this.isHasDvijeniaInStac24;
		params.parentClass = this.parentClass;
		params.isELN = win.checkIsLvnELN();
		params.isFSS = base_form.findField('EvnStickBase_IsFSS').getValue();
		params.StickWorkType_Code = base_form.findField('StickWorkType_id').getFieldValue('StickWorkType_Code');

		var minAge, minAgePersonFio, CarePerson_id;
		win.findById(win.id+'EStEF_EvnStickCarePersonGrid').getStore().each(function(rec) {

			if (!minAge || minAge > rec.get('Person_Age')) {
				minAge = rec.get('Person_Age');
				minAgePersonFio = rec.get('Person_Fio');
				CarePerson_id = rec.get('Person_id');
			}
		});
		
		//возраст и ФИО младшего пациента нуждающегося в уходе
		params.CarePerson_Age = minAge;
		params.CarePerson_Fio = minAgePersonFio;

		params.CarePerson_id = CarePerson_id;
		params.EvnStick_prid = base_form.findField('EvnStick_prid').getValue();

		if ( action == 'add' ) {
			params.formParams.EvnStickBase_id = base_form.findField('EvnStick_id').getValue();
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnStickWorkRelease_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

				params.formParams = record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		if ( this.userMedStaffFactId ) {
			params.UserMedStaffFact_id = this.userMedStaffFactId;
		}

		if  ( this.MedStaffFactInUserLpu ) {
			params.MedStaffFactInUserLpu;
		}
		// Если полка, и предыдущий ЛВН закрыт с причиной долечивание в квс => ограничение по длительности ЛВН будет 10 дней.
		if ( this.parentClass.inlist([ 'EvnPL', 'EvnPLStom' ]) ) {
			if (base_form.findField('MaxDaysLimitAfterStac').getValue() == 2) {
				params.MaxDaysLimitAfterStac = true;
			} else {
				params.MaxDaysLimitAfterStac = false;
			}
		}

		getWnd('swEvnStickWorkReleaseEditWindow').show(params);
	},
	// -----------------------------------------------------------------------------------------------------------------




	disableField: function (field) {
		__l('disableField');
		field.disable();
	},
	
	
	onEnableEdit: function(enable) {
		__l('onEnableEdit');
		var me = this;

		if(me.action != 'view') {
			me.refreshFormPartsAccess();
		}

		me.checkSaveButtonEnabled();

		if (getRegionNick() != 'kz'){
			this.checkGetEvnStickNumButton();
		}
	},


	filterStickCause: function() {
		__l('filterStickCause');
		var wnd = this;
		var me = this;
		var base_form = wnd.FormPanel.getForm();
		var stick_cause_combo = base_form.findField('StickCause_id');

		var set_date = base_form.findField('EvnStick_setDate').getValue();
		if (Ext.isEmpty(set_date)) {
			set_date = new Date(new Date().format('Y-m-d'));
		}

		stick_cause_combo.getStore().clearFilter();
		stick_cause_combo.lastQuery = '';

		stick_cause_combo.getStore().filterBy(function(rec){
			var flag = true;
			var sysNick = rec.get('StickCause_SysNick');
			if (getRegionNick() == 'kz') {
				if (wnd.parentClass && wnd.parentClass != 'EvnPL' && sysNick.inlist(['adopt','karantin'])) {
					flag = false;
				}
				if (wnd.parentClass && wnd.parentClass != 'EvnPS' && sysNick.inlist(['protez'])) {
					flag = false;
				}
			}

			if (
				(!Ext.isEmpty(rec.get('StickCause_begDate')) && rec.get('StickCause_begDate') > set_date) ||
				(!Ext.isEmpty(rec.get('StickCause_endDate')) && rec.get('StickCause_endDate') < set_date)
			) {
				flag = false;
			}

			return flag;
		});
	},
	deleteGridRecord: function(object) {
		__l('deleteGridRecord');
		var me = this;
		if ( this.action == 'view' ) {
			return false;
		}

		if ( typeof object != 'string' || !(object.inlist([ 'EvnStickCarePerson', 'EvnStickWorkRelease' ])) ) {
			return false;
		}

		var grid = this.findById(this.id+'EStEF_' + object + 'Grid');

		if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(object + '_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		if (getRegionNick() != 'kz' && record.get('EvnStickWorkRelease_IsInReg') && record.get('EvnStickWorkRelease_IsInReg') == 2) {
			sw.swMsg.alert(langs('Ошибка'), 'Выбранный период освобождения от работы отправлен в ФСС. Удаление невозможно');
			return false;
		}

		if (object == 'EvnStickWorkRelease' && !Ext.isEmpty(record.get('EvnVK_id'))) {
			sw.swMsg.alert(langs('Ошибка'), 'Освобождение связано с протоколом заседания врачебной комиссии № '+record.get('EvnVK_NumProtocol')+'.');
			return false;
		}

		switch ( Number(record.get('RecordStatus_Code')) ) {
			case 0:
				grid.getStore().remove(record);
			break;

			case 1:
			case 2:
				record.set('RecordStatus_Code', 3);
				record.commit();

				grid.getStore().filterBy(function(rec) {
					if ( Number(rec.get('RecordStatus_Code')) == 3 ) {
						return false;
					}
					else {
						return true;
					}
				});
			break;
		}

		if ( grid.getStore().getCount() == 0 ) {
		} else {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}

		this.checkLastEvnStickWorkRelease('del');
	},



	getStickRegimeId: function() {
		__l('getStickRegimeId');
		var me = this;
		var base_form = this.FormPanel.getForm();
		var StickRegime_id = null;

		if (getRegionNick() == 'kz') {
			if (base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'kurort') {
				StickRegime_id = 4;
			} else if (this.parentClass.inlist(['EvnPL','EvnPLStom'])) {
				StickRegime_id = 1;
			} else if (this.parentClass.inlist(['EvnPS'])) {
				StickRegime_id = 2;
			}
		}
		return StickRegime_id;
	},
	getFields: function(component) {
		__l('getFields');
		var me = this;
		var base_form = this.FormPanel.getForm();
		var fields = [];
		var isString = function(s){return typeof s == 'string'};
		var fieldByName = function(name){return base_form.findField(name)};
		function getRecursiveFields(o) {
			if ((typeof o == 'object') && o.items && o.items.items) {
				o = o.items.items;
			}
			if (o && o.length && o.length>0) {
				for (var i = 0, len = o.length; i < len; i++) {
					if (o[i])
						if ((o[i].xtype && (o[i].xtype=='fieldset' || o[i].xtype=='panel' || o[i].xtype=='tabpanel')) || (o[i].layout)) {
							getRecursiveFields(o[i]);
						}
					if (o[i].isFormField) {
						fields.push(o[i]);
					}
				}
			}
		}
		if (typeof component == 'string') {
			component = this.findById(component);
		}
		if (Ext.isArray(component) && component.every(isString)) {
			component = component.map(fieldByName);
		}
		getRecursiveFields(component);
		return fields;
	},

	// Получаем Статус подписи документы, отображаем его и устанавливаем Signatures_iid или Signatures_id
	getEvnStickSignStatus: function(options) {
		__l('getEvnStickSignStatus');
		var me = this;
		var options = options || {};
		var form = this;
		var params = {};
		var base_form = this.FormPanel.getForm();
		var signobject = options.object || 'leave';
		var elname = options.object && options.object == 'irr' ? 'Irr' : 'Leave';
		params.SignObject = signobject;
		params.EvnStick_id = base_form.findField('EvnStick_id').getValue();
		form.findById('swSignStick'+elname+'List').disable();
		form.findById('swSignStick'+elname+'Check').disable();
		Ext.Ajax.request({
			url: '/?c=Stick&m=getEvnStickSignStatus',
			params: params,
			success: function(response, options) {
				var result = Ext.util.JSON.decode(response.responseText);
				if (result.SStatus_id) {
					form.findById('S'+elname+'Status_Name').getEl().dom.innerHTML = result.SStatus_Name;
					form.findById('S'+elname+'Status_Name').render();
					if (result.SStatus_id.inlist([1,3])) {
						form.findById('swSignStick'+elname+'List').enable();
						form.findById('swSignStick'+elname+'Check').enable();
						if (signobject == 'irr') {
							form.Signatures_iid = result.Signatures_id;
							form.signedRegime_MedPersonal_id = result.MedPersonal_id;
							form.refreshFormPartsAccess();
						}
						else {
							form.Signatures_id = result.Signatures_id;
						}
					}
				}
			}
		});

		return me;
	},
	getPridStickLeaveTypeCode: function() {
		__l('getPridStickLeaveTypeCode');
		var me = this;
		var base_form = this.FormPanel.getForm();
		var code1 = base_form.findField('PridStickLeaveType_Code1').getValue();
		var code2 = base_form.findField('PridStickLeaveType_Code2').getValue();

		if (!Ext.isEmpty(code2) && code2 != '0') return code2;
		if (!Ext.isEmpty(code1) && code1 != '0') return code1;
		return '0';
	},


	updateEvnStickWorkReleaseGrid: function() {
		__l('updateEvnStickWorkReleaseGrid');
		var me = this;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var stick_grid = this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid');
		var grid_update_callback = function (result, add_work_release) {
			for (var i = 0; i < result.length; i++) {
				var record = stick_grid.getStore().getAt(i);
				if ( typeof record == 'object' ) {
					if (result[i].EvnStickWorkRelease_updDT > record.get('EvnStickWorkRelease_updDT')) {
						record.set('EvnStickWorkRelease_begDate', getValidDT(result[i].EvnStickWorkRelease_begDate, ''));
						record.set('EvnStickWorkRelease_endDate', getValidDT(result[i].EvnStickWorkRelease_endDate, ''));
						record.set('RecordStatus_Code', 2);
						record.commit();
					}
				} else if ( add_work_release > 0 && add_work_release <= (result.length - stick_grid.getStore().getCount()) ) {
					add_work_release--;
					result[i].EvnStickWorkRelease_id = -swGenTempId(stick_grid.getStore());

					result[i].EvnStickWorkRelease_IsInReg = 1;
					result[i].EvnStickWorkRelease_IsPaid = 1;
					result[i].SMP_Status_Name = 'Документ не подписан';
					result[i].SMP_updDT = null;
					result[i].SMP_updUser_Name = null;
					result[i].SVK_Status_Name = 'Документ не подписан';
					result[i].SVK_updDT = null;
					result[i].SVK_updUser_Name = null;
					result[i].Signatures_mid = null;
					result[i].Signatures_wid = null;

					stick_grid.getStore().loadData([result[i]], true);
				}
			}
			win.setEvnStickDisDate();
			win.loadMedStaffFactList();
		};

		Ext.Ajax.request({
			url: '/?c=Stick&m=updateEvnStickWorkReleaseGrid',
			params: {
				'EvnStick_id': base_form.findField('EvnStick_id').getValue(),
				'EvnStick_pid': base_form.findField('EvnStickDop_pid').getValue(),
				'ignoreRegAndPaid': 1
			},
			callback: function(opt, success, response) {
				if (success && response.responseText.length > 0) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.length > stick_grid.getStore().getCount()) {
						if ((result.length - stick_grid.getStore().getCount()) == 1 ) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										grid_update_callback(result, 1);
									} else {
										grid_update_callback(result, 0);
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: langs('В ЛВН по основному месту работы найдено освобождение от работы, отсутствующее в ЛВН по совместительству. Перенести?'),
								title: langs('Вопрос')
							});
						} else if ((result.length - stick_grid.getStore().getCount()) == 2) {
							sw.swMsg.show({
									buttons: {
									yes: langs('Перенести освобождение №2 и №3'),
									no: langs('Перенести только освобождение №2'),
									cancel: langs('Отмена')
								},
								fn: function(buttonId, text, obj) {
									if ( buttonId == 'yes' ) {
										grid_update_callback(result, 2);
									} else if ( buttonId == 'no' ){
										grid_update_callback(result, 1);
									} else {
										grid_update_callback(result, 0);
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: langs('В ЛВН по основному месту работы найдены освобождения от работы, отсутствующие в ЛВН по совместительству. Перенести?'),
								title: langs('Вопрос')
							});
						}
					} else if (result.length > 0) {
						grid_update_callback(result, 0);
					}
				}
			}
		});
	},

	// проверяем доступ к элементам и блокируем или разблокируем их
	refreshFormPartsAccess: function() {
		__l('refreshFormPartsAccess');
		var me = this;
		var win = this;

		if (me.action == 'view') {
			return;
		}

		var work_release_grid = this.findById(this.id+'EStEF_EvnStickWorkReleaseGrid');
		var base_form = me.FormPanel.getForm();
		var lastWorkReleaseAccess = false;

		win.isPaid = (base_form.findField('EvnStick_IsPaid').getValue() == 2);
		win.isInReg = (base_form.findField('EvnStick_IsInReg').getValue() == 2);


		win.hasWorkReleaseIsInReg = false;
		win.hasWorkReleaseIsPaid = false;

		var person_age = swGetPersonAge(me.advanceParams.Person_Birthday, base_form.findField('EvnStick_setDate').getValue());
		var firstMedPersonal_id = null;
		var secondMedPersonal_id = null;
		var thirdMedPersonal_id = null;
		var maxDate = null;
		var minDate = null;
		work_release_grid.getStore().each(function(rec) {
			if ( rec && rec.get('EvnStickWorkRelease_begDate') != '' ) {
				if (rec.get('EvnStickWorkRelease_IsInReg') == 2) {
					win.hasWorkReleaseIsInReg = true;
				}
				if (rec.get('EvnStickWorkRelease_IsPaid') == 2) {
					win.hasWorkReleaseIsPaid = true;
				}

				if (maxDate == null || rec.get('EvnStickWorkRelease_begDate') > maxDate) {
					maxDate = rec.get('EvnStickWorkRelease_begDate');
					thirdMedPersonal_id = rec.get('MedPersonal_id');
					if (rec.get('accessType') == 'edit') {
						lastWorkReleaseAccess = true;
					} else {
						lastWorkReleaseAccess = false;
					}
				}

				if (minDate == null || rec.get('EvnStickWorkRelease_begDate') < minDate) {
					firstMedPersonal_id = rec.get('MedPersonal_id');
					minDate = rec.get('EvnStickWorkRelease_begDate');
				}

				if ( minDate < rec.get('EvnStickWorkRelease_begDate') && rec.get('EvnStickWorkRelease_begDate') < maxDate ) {
					secondMedPersonal_id = rec.get('MedPersonal_id');
				}


			}
		});

		// Основной раздел
		win.mainPanelAccess = false;

		// Основной раздел, но только поля Номер, Организация, Наименование для печати
		win.mainPanelSomeFieldsAccess = false;

		// Основной раздел, но только поля Код изм. нетрудоспособности и Дата изменения причины нетрудоспособности
		win.mainPanelSomeMainFieldsKodyNetrudAccess = false;

		//Основной раздел, поля СКЛ
		win.mainPanelSSTFieldsAccess = false;

		// Список пациентов нуждающихся в уходе, МСЭ
		win.carePersonMSEAccess = false;

		// Освобождение от работы
		win.workReleaseAccess = false;

		// isPaid - «Принят ФСС»
		// IsInReg - «В реестре»

		// если Оператор, Статистик или Регистратор ЛВН, то
		if (isOperator() || isMedStatUser() || isRegLvn()) {
			if (getRegionNick() == 'kz' || (!win.isPaid && !win.isInReg && !win.hasWorkReleaseIsInReg && !win.hasWorkReleaseIsPaid)) {
				// Все разделы ЭЛН без признака «Принят ФСС» (IsPaid) или «В реестре» (IsInReg) Регион: Все, кроме Казахстана
				if (Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
					// Основной раздел
					win.mainPanelAccess = true;

					// Основной раздел, но только поля Номер, Организация, Наименование для печати
					win.mainPanelSomeFieldsAccess = true;

					//Основной раздел, поля СКЛ
					win.mainPanelSSTFieldsAccess = true;
				}
			}

			if (getRegionNick() == 'kz' || (!win.isPaid && !win.isInReg)) {
				// У ЭЛН нет признака «Принят ФСС» (IsPaid) И «В реестре» (IsInReg) Регион: Все, кроме Казахстана
				if (Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
					// Список пациентов нуждающихся в уходе, МСЭ
					win.carePersonMSEAccess = true;

					// Основной раздел, но только поля Код изм. нетрудоспособности и Дата изменения причины нетрудоспособности
					win.mainPanelSomeMainFieldsKodyNetrudAccess = true;
				}
			}

			if (!win.isPaid && !win.isInReg) {
				// Освобождение от работы
				win.workReleaseAccess = true;
			}
		}

		win.checkEvnStickNumDouble();

		// если Регистратор, то
		if (isPolkaRegistrator()) {
			if (getRegionNick() == 'kz' || (!win.isPaid && !win.isInReg && !win.hasWorkReleaseIsInReg && !win.hasWorkReleaseIsPaid)) {
				// Все разделы ЭЛН без признака «Принят ФСС» (IsPaid) или «В реестре» (IsInReg) Регион: Все, кроме Казахстана
				// Только поля: Номер, Организация, Наименование для печати
				if (Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
					win.mainPanelSomeFieldsAccess = true;
				}
			}

			if (secondMedPersonal_id == getGlobalOptions().medpersonal_id || thirdMedPersonal_id == getGlobalOptions().medpersonal_id) {
				win.mainPanelSomeMainFieldsKodyNetrudAccess = true;
			}
		}

		// если Врач или Председатель ВК то
		if (me.isVrach() || me.isVrachVK()) {
			if (
				(getRegionNick() == 'kz' || (!win.isPaid && !win.isInReg && !win.hasWorkReleaseIsInReg && !win.hasWorkReleaseIsPaid))
			) {
				if (Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
					// Все разделы ЭЛН без признака «Принят ФСС» (IsPaid) или «В реестре» (IsInReg) Регион: Все, кроме Казахстана
					win.mainPanelSomeFieldsAccess = true;
					if (win.action == 'add' || firstMedPersonal_id == getGlobalOptions().medpersonal_id) {
						// Только если врач указан в первом освобождении от работы как врач 1 или ЛВН открыт на добавление.
						win.mainPanelAccess = true;
						win.mainPanelSSTFieldsAccess = true;
					}
				}
			}

			if (getRegionNick() == 'kz' || (!win.isPaid && !win.isInReg)) {
				// У ЭЛН нет признака «Принят ФСС» (IsPaid) И «В реестре» (IsInReg) Регион: Все, кроме Казахстана
				if (Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())) {
					win.carePersonMSEAccess = true;

					if (win.action == 'add' || firstMedPersonal_id == getGlobalOptions().medpersonal_id) {
						// Только если врач указан в первом освобождении от работы как врач 1 или ЛВН открыт на добавление.
						// Основной раздел, но только поля Код изм. нетрудоспособности и Дата изменения причины нетрудоспособности
						win.mainPanelSomeMainFieldsKodyNetrudAccess = true;
					}
				}
			}

			if (secondMedPersonal_id == getGlobalOptions().medpersonal_id || thirdMedPersonal_id == getGlobalOptions().medpersonal_id) {
				win.mainPanelSomeMainFieldsKodyNetrudAccess = true;
			}

			if (!win.isPaid && !win.isInReg) {
				win.workReleaseAccess = true;
			}


			// доступность разделов если в освобождении указано рабочее место из МО пользователя #135678
			if (getRegionNick() == 'kz' && win.MedStaffFactInUserLpu) {
				win.workReleaseAccess = true;
				win.mainPanelAccess = true;
				win.mainPanelSomeMainFieldsKodyNetrudAccess = true;
				win.carePersonMSEAccess = true;
			}
		}

		if (getRegionNick() == 'kz' || (!win.isPaid && !win.isInReg && !win.hasWorkReleaseIsInReg && !win.hasWorkReleaseIsPaid)) {
			if (base_form.findField('Org_did').getValue() == getGlobalOptions().org_id) {
				win.mainPanelSSTFieldsAccess = true;
			}
		}

		if (getRegionNick() != 'kz' && getGlobalOptions().lpu_id != win.Lpu_id) {

			win.carePersonMSEAccess = false;
			if (base_form.findField('Org_did').getValue() != getGlobalOptions().org_id) {
				win.workReleaseAccess = false;
			}
		}


		var enableField = me.enableField;
		var disableField = me.disableField;



		// основной раздел
		if(win.mainPanelAccess){
			win.getFields(this.formMainFields).forEach(enableField);
		} else {
			win.getFields(this.formMainFields).forEach(disableField);
		}

		// разблокируем поле Выдан ФИО при оформлении дубликата если есть список пациентов нужающихся в уходе
		if(
			win.action == 'add' 
			&& base_form.findField('EvnStick_IsOriginal').getValue() == 2
			&& base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick')
			&& (
				base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick').inlist(['uhod', 'uhodnoreb', 'uhodreb', 'rebinv', 'postvaccinal', 'vich'])
				|| (base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick') == 'karantin' && person_age < 18)
			)

		) {
			base_form.findField('EvnStickFullNameText').enable();
		}

		// разблокируем печать усечённого талона для электронных ЛВН
		if(getRegionNick() != 'kz'){//--
			if((me.checkIsLvnELN() || base_form.findField('EvnStickBase_IsFSS').getValue() ) && me.menuPrintActions && !Ext.isEmpty(me.menuPrintActions)){
				me.menuPrintActions.items.items[1].enable();
			}else{
				me.menuPrintActions.items.items[1].disable();
			}
		}

		// некоторые поля основного раздела: Номер, Организация, Наименование для печати
		if(win.mainPanelSomeFieldsAccess){
			win.getFields(this.formSomeMainFields).forEach(enableField);
		} else {
			win.getFields(this.formSomeMainFields).forEach(disableField);
		}


		// некоторые поля основного раздела: Код изм. нетрудоспособности и Доп. код нетрудоспособности
		if(win.mainPanelSomeMainFieldsKodyNetrudAccess){
			win.getFields(this.formSomeMainFields_KodyNetrud).forEach(enableField);
		} else {
			win.getFields(this.formSomeMainFields_KodyNetrud).forEach(disableField);
		}


		// Основной раздел, поля СКЛ
		if (win.mainPanelSSTFieldsAccess) {
			win.getFields(this.formSSTFields).forEach(enableField);
		} else {
			win.getFields(this.formSSTFields).forEach(disableField);
		}

		if (getRegionNick() != 'kz') {
			win.checkGetEvnStickNumButton();
			win.checkESSConsentDeleteButton();
		}

		// Список пациентов нуждающихся в уходе, МСЭ
		if (win.carePersonMSEAccess) {
			win.findById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].enable();
			win.getFields(win.id+'EStEF_MSEPanel').forEach(enableField);
		}
		else {
			win.findById(win.id+'EStEF_EvnStickCarePersonGrid').getTopToolbar().items.items[0].disable();
			win.getFields(win.id+'EStEF_MSEPanel').forEach(disableField);
		}

		// Освобождение от работы
		if(
			win.workReleaseAccess
			&& (
				(
					win.action == 'add'
					&& Ext.isEmpty(base_form.findField('StickFSSData_id').getValue())
				)
				|| (
					win.action == 'edit'
					&& base_form.findField('addWorkReleaseAccessType').getValue() == 'edit'
				)
				|| ( //при добавлении нового ЛВН из ФСС нет возможности определить оригинал это или дубликат
					win.action == 'add'
					&& win.checkIsLvnFromFSS()
					&& win.isSanatorium 
				)
			)
		) {
			work_release_grid.getTopToolbar().items.items[0].enable();
		}
		else {
			work_release_grid.getTopToolbar().items.items[0].disable();
		}




		// -------------------------------------------------------------------------------------------------------------
		// Исход ЛВН
		// -------------------------------------------------------------------------------------------------------------

		// Закрываем доступ
		me._closeAccessToPanelStickLeave();

		// Проверяем доступность блока и открываем доступ при необходимости
		if(me._checkAccessToPanelStickLeave() == true){
			me._openAccessToPanelStickLeave();
		}
		// -------------------------------------------------------------------------------------------------------------



		// -------------------------------------------------------------------------------------------------------------
		// Кнопка "Подписать" в блоке "Исход ЛВН"
		// -------------------------------------------------------------------------------------------------------------

		// Закрываем доступ
		me._closeAccessToField_StickLeave_Sign();

		// Проверяем доступность блока и открываем доступ при необходимости
		if(me._checkAccessToField_StickLeave_Sign() == true){
			me._openAccessToField_StickLeave_Sign();
		}
		// -------------------------------------------------------------------------------------------------------------


		// -------------------------------------------------------------------------------------------------------------
		// Блок МСЭ
		// -------------------------------------------------------------------------------------------------------------

		// поля: Дата регистрации документов в бюро МСЭ, Дата освидетельствования в бюро МСЭ, Установлена/изменена группа инвалидности
		if( !me.checkIsLvnELN() ) {
			base_form.findField('EvnStick_mseRegDate').enable();
			base_form.findField('EvnStick_mseExamDate').enable();
			base_form.findField('InvalidGroupType_id').enable();
		} else {
			base_form.findField('EvnStick_mseRegDate').disable();
			base_form.findField('EvnStick_mseExamDate').disable();
			base_form.findField('InvalidGroupType_id').disable();
		}

		// Дата направления в бюро МСЭ
		if ( 
			base_form.findField('EvnStick_IsDateInReg').getValue() == 2
			|| base_form.findField('EvnStick_IsDateInFSS').getValue() == 2
		) {
			base_form.findField('EvnStick_mseDate').disable();
		} else {
			base_form.findField('EvnStick_mseDate').enable();
		}

		// -------------------------------------------------------------------------------------------------------------
		


		// -------------------------------------------------------------------------------------------------------------
		// Блок Режим:  Дата начала, Дата окончания лечения в стационаре
		// -------------------------------------------------------------------------------------------------------------

		// Закрываем доступ
		me._closeAccessToPanelStickRegime();


		// Проверяем доступность блока и открываем доступ при необходимости
		// ВАЖНО!!!!!!!!!!!!!!!!!!!!!! - Не забываем что при открытии блока мы так же проверяем доступность каждого
		// поля в методе checkFieldDisabled()
		if(me._checkAccessToPanelStickRegime() == true){
			me._openAccessToPanelStickRegime();
		}

		// Кнопка "Подписать режим" 
		me._closeAccessToField_swSignStickIrr();
		if(me._checkAccessToField_swSignStickIrr() == true) {
			me._openAccessToField_swSignStickIrr();
		}

		// Дата начала
		me._closeAccessToField_EvnStick_stacBegDate();
		me._closeAccessToField_EStEF_btnSetMinDateFromPS();
		if(me._checkAccessToField_EvnStick_stacBegDate() == true){
			me._openAccessToField_EvnStick_stacBegDate();
			me._openAccessToField_EStEF_btnSetMinDateFromPS();
		}


		// Дата окончания
		me._closeAccessToField_EvnStick_stacEndDate();
		me._closeAccessToField_EStEF_btnSetMaxDateFromPS();
		if(me._checkAccessToField_EvnStick_stacEndDate() == true){
			me._openAccessToField_EvnStick_stacEndDate();
			me._openAccessToField_EStEF_btnSetMaxDateFromPS();
		}
		// -------------------------------------------------------------------------------------------------------------
	},
	loadMedStaffFactList: function() {
		__l('loadMedStaffFactList');
		var me = this;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var evn_stick_work_release_end_date = null;
		var evn_stick_work_release_store = this.findById(this.id+'EStEF_EvnStickWorkReleaseGrid').getStore();

		evn_stick_work_release_store.each(function(record) {
			if ( evn_stick_work_release_end_date == null || record.get('EvnStickWorkRelease_endDate') > evn_stick_work_release_end_date ) {
				evn_stick_work_release_end_date = record.get('EvnStickWorkRelease_endDate');
			}
		});

		var
			index = -1,
			MedPersonal_id = base_form.findField('MedPersonal_id').getValue(),
			MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

		if (evn_stick_work_release_end_date == null) {
			evn_stick_work_release_end_date = getValidDT(getGlobalOptions().date, '');
		}

		setMedStaffFactGlobalStoreFilter({
			onDate: Ext.util.Format.date(evn_stick_work_release_end_date, 'd.m.Y')
		});

		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		if ( !Ext.isEmpty(MedStaffFact_id) ) {
			index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
				return (rec.get('MedStaffFact_id') == MedStaffFact_id);
			});
		}

		if ( index == -1 && !Ext.isEmpty(MedPersonal_id) ) {
			index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
				return (rec.get('MedPersonal_id') == MedPersonal_id);
			});
		}

		if ( index >= 0 ) {
			base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
		}
	},



	// *****************************************************************************************************************
	// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
	// *****************************************************************************************************************

	// Признак ЭЛН - Принят ФСС
	isPaid: null,
	checkExist_isPaid: function(){
		__l('checkExist_isPaid');
		var me = this;
		var isExist = false; // ЭЛН не принят ФСС


		if(me.isPaid){

			// ФСС принял ЭЛН
			isExist == true;
		}

		return isExist;
	},

	// Признак ЭЛН - В реестре
	isInReg: null,
	checkExist_isInReg: function(){
		__l('checkExist_isInReg');
		var me = this;
		var isExist = false; // ЭЛН нет в реестре


		if(me.isInReg){

			// ЭЛН находиться в реестре
			isExist == true;
		}

		return isExist;
	},


	// -----------------------------------------------------------------------------------------------------------------
	// Роли пользователя
	// -----------------------------------------------------------------------------------------------------------------
	// Оператор
	isOperator: function(){
		__l('isOperator');
		return isOperator();
	},
	// Статистик
	isStatistick: function(){
		__l('isStatistick');
		return isMedStatUser();
	},
	// Регистратор ЛВН
	isRegistratorLVN: function(){
		__l('isRegistratorLVN');
		return isRegLvn();
	},
	// Регистратор
	isRegistrator: function(){
		__l('isRegistrator');
		return isPolkaRegistrator();
	},
	// Врач
	isVrach: function(){
		__l('isVrach');
		return userIsDoctor() || isPolkaVrach() || isStacVrach() || isStacReceptionVrach();
	},
	isVrachVK: function(){
		__l('isVrachVK');
		return haveArmType('vk');
	},
	// Председателем ВК
	isPredsedatelVK: function(){
		__l('isPredsedatelVK');
		return haveArmType('vk');
	},
	// Врач, регистратор (одновременно)
	isVrachAndRegistrator: function(){
		__l('isVrachAndRegistrator');
		var me = this;
		return me.isVrach() && me.isRegistrator();
	},
	// -----------------------------------------------------------------------------------------------------------------






	checkRebUhod: function() {
		__l('checkRebUhod');
		var me = this;
		var base_form = this.FormPanel.getForm();
		var grid = this.findById(this.id+'EStEF_EvnStickCarePersonGrid');

		var care_person_index = grid.getStore().findBy(function(rec) { return rec.get('Person_id') == this.PersonInfo.getFieldValue('Person_id'); }.createDelegate(this));
		var care_person_record = grid.getStore().getAt(care_person_index);
		var stick_cause = base_form.findField('StickCause_id').getFieldValue('StickCause_SysNick');
		var env_stick_set_date = base_form.findField('EvnStick_setDate').getValue();

		if (!care_person_record || !care_person_record.get('Person_id') || !stick_cause || !stick_cause.inlist(['uhodnoreb','uhod','uhodreb','rebinv', 'zabrebmin'])) {
			Ext.getCmp('openEvnStickWorkReleaseCalculationWindow').hide();
			return;
		}

		var params = {
			Person_id: care_person_record.get('Person_id'),
			PrivilegeType_id: 84,		//Дети-инвалиды
			Privilege_begDate: Ext.util.Format.date(env_stick_set_date, 'd.m.Y')
		};

		Ext.Ajax.request({
			url: '/?c=Privilege&m=checkPersonPrivilege',
			params: params,
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (response_obj.success) {
					var limit_age = response_obj.check ? 15 : 7;

					if (care_person_record.get('Person_Age') < limit_age) {
						Ext.getCmp('openEvnStickWorkReleaseCalculationWindow').show();
					} else {
						Ext.getCmp('openEvnStickWorkReleaseCalculationWindow').hide();
					}
				} else {
					Ext.getCmp('openEvnStickWorkReleaseCalculationWindow').hide();
				}
			},
			failure: function() {
				Ext.getCmp('openEvnStickWorkReleaseCalculationWindow').hide();
			}
		});
	},
	CheckWorkRelease: function() { //https://redmine.swan.perm.ru/issues/83780
		__l('CheckWorkRelease');
		var me = this;
		var form = this.FormPanel.getForm();
		var EvnStick_id = form.findField('EvnStick_id').getValue();
		var _this = this;
		if(getRegionNick() == 'ekb'){
			Ext.Ajax.request({
				url: '/?c=Stick&m=WorkReleaseMedStaffFactCheck',
				params: {
					EvnStickBase_id: EvnStick_id
				},
				callback: function(opt, success, response) {
					if (success && response.responseText.length > 0) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!Ext.isEmpty(result[0]) && !Ext.isEmpty(result[0].EvnStickWorkRelease_id)) {
							var b_date = result[0].evnStickWorkRelease_begDT;
							var e_date = result[0].evnStickWorkRelease_endDT;
							var msg = langs('Продление и/или выдача ЛВН осуществлена через ВК. Укажите врача №2 в освобождении от работы От ') + b_date + " " + langs('До') + " " + e_date;
							sw.swMsg.show(
								{
									buttons: Ext.Msg.OK,
									fn: function()
									{
										return 1;
									},
									icon: Ext.Msg.WARNING,
									msg: msg,
									title: langs('Ошибка')
								});
						}
						else
						{
							_this.doPrintEvnStick();
						}
					}
					else
					{
						_this.doPrintEvnStick();
					}
				}
			});
		}
		else
			_this.doPrintEvnStick();
	},
	checkConsentInAnotherLvn: function () {
		__l('checkConsentInAnotherLvn');

		var me = this;
		var win = this;
		var base_form = win.FormPanel.getForm(),
			EvnStickDop = base_form.findField('EvnStickDop_pid'),
			EvnStickDop_pid = EvnStickDop.getValue();

		if (EvnStickDop_pid < 0)
		{
			return false;
		}

		var index = EvnStickDop.getStore().find('EvnStick_id', EvnStickDop_pid);

		if (index === -1)
		{
			return false;
		}

		var record = EvnStickDop.getStore().getAt(index);

		if ( Ext.isEmpty(record.get('EvnStickBase_consentDT')) )
		{
			return false;
		}

		return true;
	},

	// Проверка доступа к кнопке удаления даты согласия
	checkESSConsentDeleteButton: function() {
		var base_form = this.FormPanel.getForm();

		if (
			this.action == 'view'
			|| this.hasWorkReleaseIsInReg 
			|| this.hasWorkReleaseIsPaid
			|| base_form.findField('EvnStick_IsInReg').getValue() == 2
			|| base_form.findField('EvnStick_IsPaid').getValue() == 2
			|| !( this.action == 'add' || base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id )
		) {
			this.findById(this.id + 'EStEF_ESSConsentDelete').disable();
		} else {
			this.findById(this.id + 'EStEF_ESSConsentDelete').enable();
		}
	},

	// Вроде как проверяем доступ к блоку "Получить номер ЭЛН" и блокируем его или открываем
	checkGetEvnStickNumButton: function() {
		__l('checkGetEvnStickNumButton');

		var win = this;
		var me = this;
		var regionNick = getRegionNick();
		var base_form = this.FormPanel.getForm();

		// вторая проверка, первая при вызове (желательно эту проверку убрать, пока оставил на всякий)
		if (regionNick != 'kz'){

			var consent_dt = base_form.findField('EvnStickBase_consentDT').getValue();
			var evnstick_disdate = base_form.findField('EvnStick_disDate').getValue();
			var StickWorkType_id = base_form.findField('StickWorkType_id').getValue();
			var EvnStick_Num = base_form.findField('EvnStick_Num').getValue();
			var EvnStickBase_consentDT = base_form.findField('EvnStickBase_consentDT').getValue()

			me.findById(win.id + 'GetEvnStickNumButton').disable();

			if (
				(getRegionNick() != 'kz' || me.action == 'add') &&
				Ext.isEmpty(EvnStick_Num) &&
				(
					!Ext.isEmpty(EvnStickBase_consentDT) ||
					(
						StickWorkType_id == 2 &&
						win.checkConsentInAnotherLvn()
					)
				)
			) {
				me.findById(win.id + 'GetEvnStickNumButton').enable();
			}

			me.findById(win.id + 'ClearEvnStickNumButton').hide();
			me.findById(win.id + 'ClearEvnStickNumButton').disable();

			if (!Ext.isEmpty(base_form.findField('RegistryESStorage_id').getValue())) {
				me.findById(win.id + 'ClearEvnStickNumButton').show();
			if (
				win.action != 'view' 
				&& !win.isPaid && !win.isInReg 
				&& !win.hasWorkReleaseIsInReg && !win.hasWorkReleaseIsPaid
				&& ( win.action == 'add' || base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id )
			) {
					me.findById(win.id + 'ClearEvnStickNumButton').enable();
				}
			}

			me.findById(win.id + 'EStEF_ESSConsentAdd').disable();
			if (
				win.action != 'view'
				&& !consent_dt
				&& (
					!evnstick_disdate || base_form.findField('EvnStick_IsOriginal').getValue() == 2 || StickWorkType_id == 2
				)
				&& ( win.action == 'add' || base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id )
				&& !me.checkIsLvnFromFSS()
			) {
				me.findById(win.id + 'EStEF_ESSConsentAdd').enable();
			}

			me.findById(win.id + 'EStEF_ESSConsentEdit').disable();
			if (
				win.action != 'view' 
				&& consent_dt 
				&& !evnstick_disdate 
				&& !me.checkIsLvnFromFSS() 
				&& ( win.action == 'add' || base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id )
			) {
				me.findById(win.id + 'EStEF_ESSConsentEdit').enable();
			}

			me.findById(win.id + 'EStEF_ESSConsentPrint').disable();
			if (
				consent_dt 
				&& base_form.findField('EvnStickFullNameText').getValue() 
				&& base_form.findField('StickCause_id').getValue()
				&& ( win.action == 'add' || base_form.findField('Lpu_id').getValue() == getGlobalOptions().lpu_id )
			) {
				me.findById(win.id + 'EStEF_ESSConsentPrint').enable();
			}

			me.checkFieldDisabled('EvnStick_Num');
		}

		return true;
	},
	/**
	 * Проверка, что текущий врач есть в одном из освобождений от работы
	 * @returns {boolean}
	 */
	checkMedPersonalInWorkRelease: function() {
		__l('checkMedPersonalInWorkRelease');

		var me = this
		var work_release_grid = me.findById(me.id + 'EStEF_EvnStickWorkReleaseGrid');

		var isInWorkRelease = false;
		work_release_grid.getStore().each(function(rec) {
			if (rec && rec.get('MedPersonal_id') && rec.get('MedPersonal_id') == getGlobalOptions().medpersonal_id) {
				isInWorkRelease = true;
			}
		});

		return isInWorkRelease;
	},

	// Статус ЛВН (не учитываем т.к. для открытия доступа статус может быть любым)
	checkStatus_EvnStick: function(){
		__l('checkStatus_EvnStick');

		// base_form.findField('StickLeaveType_id') - любое значеие подходит
		return true;
	},

	// Только ЛВН, созданные в МО. Пользователя ИЛИ в поле «Санаторий» указана МО пользователя.
	checkOwn_Lpu: function(){
		__l('checkOwn_Lpu');

		// тоже не учитываем, точно не знаю почему, но возможно: При открытии лвн его же добавляют в той мо, к которой пользователь его заносящий относится.
		return true;
	},

	checkIsLvnELN: function(){
		__l('checkIsLvnELN');
		var me = this;

		var isELN = false;




		// Общие данные ЭЛН для отправки в ФСС
		var RegistryESStorage_id = me.FormPanel.getForm().findField('RegistryESStorage_id').getValue();

		// если есть данные, значит ЛВН из ФСС
		if ( ! Ext.isEmpty(RegistryESStorage_id)){
			isELN = true;
		}

		return isELN;
	},



	// Проверяем является ли ЛВН из ФСС (EvnStickBase_IsFSS)
	checkIsLvnFromFSS: function(){
		__l('checkIsLvnFromFSS');
		var me = this;

		var isFromFSS = false;


		// Проверям по факту наличия даных!!!
		// EvnStickBase_IsFSS = true если есть данные (StickFSSData_id) !!!

		// Общие данные ЭЛН для отправки в ФСС
		var StickFSSData_id = me.FormPanel.getForm().findField('StickFSSData_id').getValue();

		// если есть данные, значит ЛВН из ФСС
		if ( ! Ext.isEmpty(StickFSSData_id)){
			isFromFSS = true;
		}

		return isFromFSS;
	},

	// Проверяем открыт ли ЛВН из КВС
	checkIsLvnOpenFromKVS: function(){
		__l('checkIsLvnOpenFromKVS');
		var me = this;

		var isFromKVS = false;

		if(me.parentClass == 'EvnPS'){
			isFromKVS = true;
		}

		return isFromKVS;
	},
	isHasDvijenia: null,
	checkHasDvijenia: function() {
		__l('checkHasDvijenia');
		var me = this;

		if (me.isHasDvijenia != null) {
			return me.isHasDvijenia;
		}

		var isHas = false;
		var EvnSectionList = me.getDvijeniaKVC();

		if (!Ext.isEmpty(EvnSectionList) && EvnSectionList.length > 0) {
			isHas = true;
		}

		me.isHasDvijenia = isHas;

		return isHas;
	},
	isHasDvijeniaInStac24: null,
	checkHasDvijeniaInStac24: function() {
		__l('checkHasDvijeniaInStac24');
		var me = this;

		if (me.isHasDvijeniaInStac24 != null) {
			return me.isHasDvijeniaInStac24;
		}

		var isHas = false;
		var EvnSectionList = me.getDvijeniaKVC();

		if (!Ext.isEmpty(EvnSectionList) && EvnSectionList.length > 0) {
			for (var i = 0; i < EvnSectionList.length; i++) {
				if (EvnSectionList[i].LpuUnitType_SysNick == 'stac') {
					isHas = true;
				}
			}
		}

		me.isHasDvijeniaInStac24 = isHas;

		return isHas;
	},


	/**
	 * При открытии блока пробегаемся по полям и проверяем нужно ли их открывать
	 * enable/disable полей в зависимости от различных условий
	 *
	 * ВАЖНО!!! Если возвращаем TRUE, то мы обязательно должны выполнить либо enable(), либо disable() поля !!!!!!!
	 *
	 * @param field - name или hiddenName поля
	 * @returns {boolean} - FALSE - если поле можно открыть, TRUE - не меняем статус (enabled/disabled) поля при открытии блока
	 *
	 */
	checkFieldDisabled: function (field) {
		__l('checkFieldDisabled');
		var win = this;
		var me = this;
		var regionNick = getRegionNick();
		var base_form = me.FormPanel.getForm();

		switch (field) {

			case 'EvnStick_Num':
				// если номер получен из хранилища номеров или есть согласие на получение ЭЛН, то блокируем поле номер от изменения
				if (
					win.link != true && win.action != 'view' 
					&& (win.mainPanelAccess || win.mainPanelSomeFieldsAccess) 
					&& Ext.isEmpty(base_form.findField('RegistryESStorage_id').getValue())
					&& (
						getRegionNick() == 'kz'
						|| Ext.isEmpty(base_form.findField('EvnStickBase_consentDT').getValue())
					)
				) {
					base_form.findField(field).enable();
				} else {
					base_form.findField(field).disable();
				}
				return true;
				break;
			case 'EvnStick_Ser':
			case 'EvnStick_setDate':
				if (win.link != true && win.action != 'view' && win.mainPanelAccess) {
					base_form.findField(field).enable();
				} else {
					base_form.findField(field).disable();
				}
				return true;
				break;
			case 'EvnStickLast_Title':
				if (win.link != true && win.action != 'view' && win.mainPanelAccess) {
					base_form.findField(field).enable();
				} else {
					base_form.findField(field).disable();
				}
				return true;
				break;
			case 'StickOrder_id':
				if (win.link != true && win.action != 'view' && win.mainPanelAccess ) {
					base_form.findField(field).enable();
				} else {
					base_form.findField(field).disable();
				}
				return true;
				break;
			case 'StickCause_id':
				if (win.action != 'view' && win.mainPanelAccess) {
					base_form.findField(field).enable();
				} else {
					base_form.findField(field).disable();
				}
				return true;
				break;
			case 'Org_id':
			case 'EvnStick_OrgNick':
				if (win.action != 'view' && (win.mainPanelAccess || win.mainPanelSomeFieldsAccess)) {
					base_form.findField(field).enable();
				} else {
					base_form.findField(field).disable();
				}
				return true;
				break;
			case 'EvnStickFullNameText':
				if (win.link != true && win.fromList != true && win.action != 'view' && win.mainPanelAccess && base_form.findField('EvnStick_IsOriginal').getValue() != 2 && Ext.isEmpty(base_form.findField('EvnStickDop_pid').getValue())) {
					base_form.findField(field).enable();
				} else {
					base_form.findField(field).disable();
				}
				return true;
				break;
			case 'StickWorkType_id':
				if (win.action == 'add') {
					base_form.findField(field).enable();
				} else {
					base_form.findField(field).disable();
				}
				return true;
				break;
			case 'EvnStick_NumNext':
				return true;
				break;
			case 'EvnStick_sstBegDate':
			case 'EvnStick_sstEndDate':
			case 'EvnStick_sstNum':
			case 'Org_did':
				if (win.action != 'view' && (win.mainPanelAccess || win.mainPanelSSTFieldsAccess)) {
					base_form.findField(field).enable();
				} else {
					base_form.findField(field).disable();
				}
				return true;
				break;
		}

		return false;
	},

	// Устанавливаем обязательность поля "Организация"
	checkOrgFieldDisabled: function() {
		__l('checkOrgFieldDisabled');
		var me = this;
		var base_form = this.FormPanel.getForm();
		if (
			getRegionNick() != 'kz' // кроме Казахстана
			&& !Ext.isEmpty(base_form.findField('RegistryESStorage_id').getValue()) // Номер ЛВН получен из хранилища номеров ЭЛН
			&& base_form.findField('StickWorkType_id').getValue()
			&& base_form.findField('StickWorkType_id').getValue().inlist([1, 2]) // Тип занятости выбрано значение: «основная работа» или  «работа по совместительству»
		) {
			base_form.findField('Org_id').setAllowBlank(false);
			base_form.findField('EvnStick_OrgNick').setAllowBlank(false);
		} else {
			base_form.findField('Org_id').setAllowBlank(true);
			base_form.findField('EvnStick_OrgNick').setAllowBlank(true);
		}
	},

	//проверяем номер ЛВН на дубли
	checkEvnStickNumDouble: function () {
		var base_form = this.FormPanel.getForm(),
			field = base_form.findField('EvnStick_Num'),
			value = base_form.findField('EvnStick_Num').getValue(),
			EvnStick_id = base_form.findField('EvnStick_id').getValue();

		if (value.length != 12 || getRegionNick() == 'kz') {
			return false;
		}

		Ext.getCmp('EvnStickES_Loader').setVisible(false);
		Ext.getCmp('EvnStickES_Type').setVisible(true);
		if (!Ext.isEmpty(base_form.findField('RegistryESStorage_id').getValue()) || base_form.findField('EvnStickBase_IsFSS').getValue()) {
			Ext.getCmp('EvnStickES_Type').setText('Электронный');
			return false; //если номер уже получен из хранилища - проверка не нужна
		}
		else {
			Ext.getCmp('EvnStickES_Type').setText('На бланке');
		}

		this.getLoadMask('Проверка номера ЛВН...').show();
		Ext.Ajax.request({
			url: '/?c=Stick&m=checkEvnStickNumDouble',
			params: {
				Lpu_id: getGlobalOptions().lpu_id,
				EvnStickNum: value,
				EvnStick_id: EvnStick_id
			},
			callback: function(opt, success, response) {
				this.getLoadMask().hide();

				var responseObj = Ext.util.JSON.decode(response.responseText);

				if (!Ext.isEmpty(responseObj.message)) {
					field.validator = function (value) {
						return responseObj.message;
					};
				} else {
					field.validator = null;
				}

				field.validate();
			}.createDelegate(this)
		});
		return true;
	},

	// устанавливаем статус подписи ЛВН "Документ не актуален"
	resetSignStatus: function() {
		var base_form = this.FormPanel.getForm();
		var status = this.findById('SLeaveStatus_Name')
		if (
			getRegionNick().inlist(['penza', 'buryatiya'])
			&& status.getEl().dom.innerHTML
		) {
			status.getEl().dom.innerHTML = 'Документ не актуален';
			status.render();

			this.findById('swSignStickLeaveCheck').disable();
		}
	},


	// блокируем или разблокируем кнопку "Сохранить"
	checkSaveButtonEnabled: function() {
		__l('checkSaveButtonEnabled');
		// При связывании ЛВН с учетным докуметом, кнопка сохранить доступна, если:
		// 1. есть хотя бы одно освобождение в своей МО
		// 2. указан исход и он в своей МО
		var me = this;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var linkedLpuIdList = [];

		if (typeof getGlobalOptions().linkedLpuIdList == 'object') {
			linkedLpuIdList = getGlobalOptions().linkedLpuIdList;
		}
		var hasOwnWorkRelease = false;
		this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid').getStore().each(function(rec) {
			if (
				rec && rec.get('Lpu_id') 
				&& (
					rec.get('Lpu_id') == getGlobalOptions().lpu_id
					|| rec.get('Lpu_id').inlist(linkedLpuIdList)
				)
			) {
				hasOwnWorkRelease = true;
			}
		});

		if (
			win.link != true
			|| hasOwnWorkRelease
			|| (
				!Ext.isEmpty(base_form.findField('MedStaffFact_id').getValue()) 
				&& !Ext.isEmpty(base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id'))
				&& (
					base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id') == getGlobalOptions().lpu_id
					|| base_form.findField('MedStaffFact_id').getFieldValue('Lpu_id').inlist(linkedLpuIdList)
				)
			)
		) {
			me._enableButtonSave()
			return true;
		} else {
			me._disableButtonSave();
			return false;
		}
	},
	checkLastEvnStickWorkRelease: function(action) {
		__l('checkLastEvnStickWorkRelease');
		// проверяем МО в последнем освобождении
		// Если МО в последнем освобождении отлична от МО пользователя, необходимо заполнить обязательные поля
		var me = this;
		var win = this;
		var base_form = this.FormPanel.getForm();
		var evn_stick_work_release_grid = this.findById(win.id+'EStEF_EvnStickWorkReleaseGrid');
		var Org_id = null;
		var maxDate = null;
		var minDate = null;
		evn_stick_work_release_grid.getStore().each(function(rec) {
			if ( rec && rec.get('EvnStickWorkRelease_begDate') != '' ) {
				if (maxDate == null || rec.get('EvnStickWorkRelease_begDate') > maxDate) {
					Org_id = rec.get('Org_id');
					maxDate = rec.get('EvnStickWorkRelease_begDate');
				}

				if (minDate == null || rec.get('EvnStickWorkRelease_begDate') < minDate) {
					minDate = rec.get('EvnStickWorkRelease_begDate');
				}
			}
		});

		var otherOrg = (!Ext.isEmpty(Org_id) && Org_id != getGlobalOptions().org_id);

		if (getRegionNick() == 'kz' && otherOrg && win.action != 'view') {
			// Исход ЛВН. Выпадающий список ТОЛЬКО из значений: 31/37
			// Дата исхода ЛВН.
			if (base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code') && !base_form.findField('StickLeaveType_id').getFieldValue('StickLeaveType_Code').inlist(['31','37'])) {
				base_form.findField('StickLeaveType_id').clearValue();
				base_form.findField('StickLeaveType_id').fireEvent('change', base_form.findField('StickLeaveType_id'), base_form.findField('StickLeaveType_id').getValue());
			}
			base_form.findField('StickLeaveType_id').getStore().clearFilter();
			base_form.findField('StickLeaveType_id').lastQuery = '';
			base_form.findField('StickLeaveType_id').getStore().filterBy(function(rec) {
				if (rec.get('StickLeaveType_Code').inlist(['31','37'])) {
					return true;
				}
				return false;
			});
			base_form.findField('StickLeaveType_id').setAllowBlank(false);
			base_form.findField('MedStaffFact_id').setAllowBlank(true);
			base_form.findField('MedStaffFact_id').setContainerVisible(false);
		} else {
			base_form.findField('StickLeaveType_id').getStore().clearFilter();
			base_form.findField('StickLeaveType_id').lastQuery = '';
			base_form.findField('StickLeaveType_id').setAllowBlank(true);

			var showMedStaffFact = !Ext.isEmpty(base_form.findField('StickLeaveType_id').getValue());
			var allowBlankMedStaffFact = (!showMedStaffFact || otherOrg);

			base_form.findField('MedStaffFact_id').setAllowBlank(allowBlankMedStaffFact);
			base_form.findField('MedStaffFact_id').setContainerVisible(showMedStaffFact);
		}

		if(this.fromList)
		{
			base_form.findField('StickLeaveType_id').getStore().clearFilter();
			base_form.findField('StickLeaveType_id').lastQuery = '';
			base_form.findField('StickLeaveType_id').getStore().filterBy(function(rec) {
				if (rec.get('StickLeaveType_Code').inlist(['31','37'])) {
					return true;
				}
				return false;
			});
			base_form.findField('StickLeaveType_id').setAllowBlank(false);
			win.findById(win.id+'EStEF_StickLeavePanel').expand();
			var index = base_form.findField('StickLeaveType_id').getStore().findBy(function(rec) {
				if(rec.get('StickLeaveType_Code') == '31')
					return true;
				else
					return false;

			});
			var record = base_form.findField('StickLeaveType_id').getStore().getAt(index);
			base_form.findField('StickLeaveType_id').setValue(record.get('StickLeaveType_id'));
		}


		if(this.action != 'view' && this.StickReg == 1)
		{
			this.findById(win.id+'EStEF_StickLeavePanel').expand();
			if (!win.isInReg && !win.isPaid) {
				base_form.findField('StickLeaveType_id').enable();
			}
		}

	},



	// запускаем процесс подписи
	_doSign: function(signType, params){
		var me = this;
		var isSign = false;
		// выбираем сертификат
		getWnd('swCertSelectWindow').show({
			signType: signType,
			callback: function (cert) {
				params.SignedToken = cert.Cert_Base64;

				if (signType && signType.inlist(['authapplet', 'authapi', 'authapitomee'])) {
					params.needHash = 1;
				}


				var sshHashData = me._getSslHash(params);

				if (sshHashData.xml) {

					switch(signType){
						case 'authapplet':
							sw.Applets.AuthApplet.signText({
								text: sshHashData.Base64ToSign,
								Cert_Thumbprint: cert.Cert_Thumbprint,
								callback: function(sSignedData){
									params.signType = signType;
									params.Hash = sshHashData.Hash;
									params.SignedData = sSignedData;
									params.xml = sshHashData.xml;

									isSign = me._sign(params);

									if(isSign){
										me._successSign(params);
									}
								}
							});
							break;
						case 'authapi':
						case 'authapitomee':
							sw.Applets.AuthApi.signText({
								win: me,
								text: sshHashData.Base64ToSign,
								Cert_Thumbprint: cert.Cert_Thumbprint,
								callback: function(sSignedData){
									params.signType = signType;
									params.Hash = sshHashData.Hash;
									params.SignedData = sSignedData;
									params.xml = sshHashData.xml;

									isSign = me._sign(params);

									if(isSign){
										me._successSign(params);
									}
								}
							});
							break;
						default:
							sw.Applets.CryptoPro.signXML({
								xml: sshHashData.xml,
								Cert_Thumbprint: cert.Cert_Thumbprint,
								callback: function(sSignedData){
									params.signType = 'cryptopro';
									params.xml = sSignedData;

									isSign = me._sign(params);

									if(isSign){
										me._successSign(params);
									}
								}
							});
							break;
					}

				}

			}
		});
	},



	// непосредственно сам запрос на подпись
	_sign: function(data){

		var me = this;
		var isSign = false;

		me.getLoadMask('Подписание').show();

		$.ajax({
			type: "POST",
			url: '/?c=Stick&m=signWorkRelease',
			data: data,
			async: false,
			success: function(response){
				var result = Ext.util.JSON.decode(response);

				if (result.success){
					isSign = true;
				} else {
					sw.swMsg.alert(langs('Ошибка'), result.Error_Msg);
				}
			}
		});

		me.getLoadMask().hide();

		return isSign;

	},

	// Выполняем обновления при успешном подписании документа
	_successSign: function(params){
		var me = this;

		// если подписывали освобождение от работы
		if ( ! params.SignObject.inlist(['leave', 'irr'])) {
			me._reload_WorkRelease();
		}
		else {
			// Если подписывали исход или режим
			me.getEvnStickSignStatus({object: params.SignObject});
		}

		return me;
	},

	// продолжение
	getEvnStickProdValues: function(EvnStick_id, callback){
		__l('getEvnStickProdValues');
		var me = this;
		var result = null;



		$.ajax({
			method: "POST",
			url: '/?c=Stick&m=getEvnStickProdValues',
			data: {
				'EvnStick_id': EvnStick_id
			},
			success: function(response){
				result = Ext.util.JSON.decode(response);
				callback(result);
			}
		});
	},

	// данные оригинала
	getEvnStickOriginInfo: function(EvnStick_id){
		__l('getEvnStickOriginInfo');
		var me = this;
		var result = null;

		$.ajax({
			method: "POST",
			url: '/?c=Stick&m=getEvnStickOriginInfo',
			data: {
				'EvnStick_id': EvnStick_id
			},
			async: false,
			success: function(response){
				result = Ext.util.JSON.decode(response);
			}
		});

		return result;
	},

	// данные лвн
	getEvnStickInfo: function(EvnStick_id){
		__l('getEvnStickInfo');
		var me = this;
		var result = null;

		$.ajax({
			method: "POST",
			url: '/?c=Stick&m=getEvnStickInfo',
			data: {
				'EvnStick_id': EvnStick_id
			},
			async: false,
			success: function(response){
				result = Ext.util.JSON.decode(response);
			}
		});

		return result;
	},


	// Поиск движений возможен:
	// 1. ЛВН заведён из КВС, тогда ЛВН по полю EvnStick_mid ссылается на EvnPS_id
	// 2. ЛВН заведён из ТАП, но связан с КВС, тогда ЛВН связан с КВС через EvnLink
	findKVC: function(){
		__l('findKVC');

		var me = this;


		// if(me.EvnPS_id != null){
		// 	return me.EvnPS_id;
		// }

		var parentClass = me.parentClass;

		// При добалении EvnStick_id равен 0
		if(me.action == 'add'){

			// ЛВН заведен из КВС
			if(parentClass == 'EvnPS'){
				me.EvnPS_id = me.EvnStick_mid;

				// ЛВН заведен из ТАП
			} else if(parentClass == 'EvnPL'){
				// никак не можем узнать КВС
			}
		}

		if(me.action == 'edit' || me.action == 'view'){

			// ЛВН заведен из КВС
			if(parentClass == 'EvnPS'){
				me.EvnPS_id = me.EvnStick_mid;

				// ЛВН заведен из ТАП
			} else if(parentClass == 'EvnPL'){
				// пробуем найти КВС через EvnLink

				$.ajax({
					method: "POST",
					url: '/?c=Stick&m=getEvnPSFromEvnLink',
					data: {
						'EvnStick_id': me.EvnStick_id
					},
					async: false,
					success: function(response){
						var result = Ext.util.JSON.decode(response);
						if (result && result[0] && result[0]['EvnPS_Id']) {
							me.EvnPS_id = result[0]['EvnPS_id'];
						}
					}
				});

			}
		}

		return me.EvnPS_id;

	},

	getLoadMask: function() {
		__l('getLoadMask');
		var me = this;
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},

	getDvijeniaKVC:  function(){
		__l('getDvijeniaKVC');
		var me = this;

		if( ! Ext.isEmpty(me.EvnSectionList)){
			return me.EvnSectionList;
		}

		me.EvnSectionList = null;

		var EvnPS_id = me.findKVC();

		// Если у ЛВН есть связанная КВС
		if( ! Ext.isEmpty(EvnPS_id)){

			$.ajax({
				method: "POST",
				url: '/?c=Stick&m=getEvnSectionList',
				data: {
					'EvnPS_id': EvnPS_id
				},
				async: false,
				success: function(response){
					var result = Ext.util.JSON.decode(response);
					if( ! Ext.isEmpty(result)){
						me.EvnSectionList = result;
					}
				}
			});

			// нам нужно получить движения с признаком "тип"
		}

		return me.EvnSectionList;
	},

	// разбронировать номер.
	_doUnbookEvnStickNum: function(RegistryESStorage_id){
		__l('_doUnbookEvnStickNum');
		Ext.Ajax.request({
			url: '/?c=RegistryESStorage&m=unbookEvnStickNum',
			params: {
				RegistryESStorage_id: RegistryESStorage_id
			}
		});
	},

	_panelsDoLayout: function(){

		__l('_panelsDoLayout');

		var me = this;

		if(me.findById(me.id+'EStEF_EvnStickCarePersonPanel')){
			me.findById(me.id+'EStEF_EvnStickCarePersonPanel').doLayout();
		}

		if(me.findById(me.id+'EStEF_EvnStickWorkReleasePanel')){
			me.findById(me.id+'EStEF_EvnStickWorkReleasePanel').doLayout();
		}

		if(me.findById(me.id+'EStEF_MSEPanel')){
			me.findById(me.id+'EStEF_MSEPanel').doLayout();
		}

		if(me.findById(me.id+'EStEF_StickLeavePanel')){
			me.findById(me.id+'EStEF_StickLeavePanel').doLayout();
		}

		if(me.findById(me.id+'EStEF_StickRegimePanel')){
			me.findById(me.id+'EStEF_StickRegimePanel').doLayout();
		}
	},


	_keydownFocus9: function(){
		var me = this;
		var base_form = me.FormPanel.getForm();
		if ( !base_form.findField('EvnStick_IsRegPregnancy').hidden && !base_form.findField('EvnStick_IsRegPregnancy').disabled ) {
			base_form.findField('EvnStick_IsRegPregnancy').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_EvnStickCarePersonPanel').hidden && !this.findById(me.id+'EStEF_EvnStickCarePersonPanel').collapsed && this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0 ) {
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
		}
		else if ( !base_form.findField('Org_did').hidden && !base_form.findField('Org_did').disabled ) {
			base_form.findField('Org_did').focus(true);
		}
		else if ( !base_form.findField('EvnStick_BirthDate').hidden && !base_form.findField('EvnStick_BirthDate').disabled ) {
			base_form.findField('EvnStick_BirthDate').focus(true);
		}
		else if ( !base_form.findField('StickCause_did').disabled ) {
			base_form.findField('StickCause_did').focus(true);
		}
		else {
			me._focusButtonCancel();
		}
	},
	_keydownFocus8: function(){
		var me = this;
		var base_form = me.FormPanel.getForm();
		if ( !this.findById(me.id+'EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_IsRegPregnancy').hidden && !base_form.findField('EvnStick_IsRegPregnancy').disabled ) {
			base_form.findField('EvnStick_IsRegPregnancy').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_StickRegimePanel').collapsed && !base_form.findField('StickIrregularity_id').disabled ) {
			base_form.findField('StickIrregularity_id').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_MSEPanel').collapsed && this.action != 'view' ) {
			base_form.findField('EvnStick_mseDate').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_EvnStickWorkReleasePanel').collapsed && me._getCount_WorkRelease() > 0 ) {
			me._focusFirst_WorkRelease();
		}
		else if ( !this.findById(me.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('StickLeaveType_id').disabled ) {
			base_form.findField('StickLeaveType_id').focus(true);
		}
		else if ( this.action != 'view' ) {
			me._focusButtonSave();
		}
		else {
			me._focusButtonPrint();
		}
	},
	_keydownFocus7: function(){
		var me = this;
		var base_form = me.FormPanel.getForm();

		if ( this.action != 'view' ) {
			me._focusButtonSave();
		}
		else if ( !this.findById(me.id+'EStEF_EvnStickWorkReleasePanel').collapsed && me._getCount_WorkRelease() > 0 ) {
			me._focusFirst_WorkRelease();
		}
		else if ( !this.findById(me.id+'EStEF_EvnStickCarePersonPanel').hidden && !this.findById(me.id+'EStEF_EvnStickCarePersonPanel').collapsed && this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0 ) {
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
		}
		else {
			me._focusButtonCancel();
		}
	},
	_keydownFocus6: function(){
		var me = this;
		var base_form = me.FormPanel.getForm();

		if ( !me.findById(me.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('Lpu_oid').hidden && !base_form.findField('Lpu_oid').disabled ) {
			base_form.findField('Lpu_oid').focus(true);
		}
		else if ( !me.findById(me.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('MedStaffFact_id').hidden && !base_form.findField('MedStaffFact_id').disabled ) {
			base_form.findField('MedStaffFact_id').focus(true);
		}
		else if ( !me.findById(me.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('EvnStick_disDate').hidden && !base_form.findField('EvnStick_disDate').disabled ) {
			base_form.findField('EvnStick_disDate').focus(true);
		}
		else if ( !me.findById(me.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('StickLeaveType_id').disabled ) {
			base_form.findField('StickLeaveType_id').focus(true);
		}
		else if ( !me.findById(me.id+'EStEF_EvnStickWorkReleasePanel').collapsed && me._getCount_WorkRelease() > 0 ) {
			me._focusFirst_WorkRelease();
		}
		else if ( !me.findById(me.id+'EStEF_MSEPanel').collapsed && !base_form.findField('InvalidGroupType_id').disabled ) {
			base_form.findField('InvalidGroupType_id').focus(true);
		}
		else if ( !me.findById(me.id+'EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_stacEndDate').disabled ) {
			base_form.findField('EvnStick_stacEndDate').focus(true);
		}
		else if ( !me.findById(me.id+'EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_irrDate').disabled ) {
			base_form.findField('EvnStick_irrDate').focus(true);
		}
		else if ( !me.findById(me.id+'EStEF_EvnStickCarePersonPanel').hidden && !me.findById(me.id+'EStEF_EvnStickCarePersonPanel').collapsed && me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0 ) {
			me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
			me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
		}
		else if ( !base_form.findField('Org_did').hidden && !base_form.findField('Org_did').disabled ) {
			base_form.findField('Org_did').focus(true);
		}
		else if ( !base_form.findField('EvnStick_BirthDate').hidden && !base_form.findField('EvnStick_BirthDate').disabled ) {
			base_form.findField('EvnStick_BirthDate').focus(true);
		}
		else if ( !base_form.findField('StickCause_did').disabled ) {
			base_form.findField('StickCause_did').focus(true);
		}
		else {
			me._focusButtonCancel();
		}
	},
	_keydownFocus5: function(){
		var me = this;
		
		var base_form = me.FormPanel.getForm();

		if ( !base_form.findField('EvnStickFullNameText').disabled ) {
			base_form.findField('EvnStickFullNameText').focus(true);
		} else if (
			! me.findById(me.id+'EStEF_EvnStickCarePersonPanel').hidden &&
			! me.findById(me.id+'EStEF_EvnStickCarePersonPanel').collapsed &&
			me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0
		) {
			me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
			me.findById(me.id+'EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
		} else if (
			! me.findById(me.id+'EStEF_EvnStickWorkReleasePanel').collapsed &&
			me._getCount_WorkRelease() > 0
		) {
			me._focusFirst_WorkRelease();
		} else if ( me.action != 'view' ) {
			me._focusButtonSave();
		} else {
			me._focusButtonPrint();
		}
	},
	_keydownFocus4: function(){
		var me = this;
		var base_form = this.FormPanel.getForm();
		if ( !this.findById(me.id+'EStEF_EvnStickWorkReleasePanel').collapsed && me._getCount_WorkRelease() > 0 ) {
			me._focusFirst_WorkRelease();
		}
		else if ( !this.findById(me.id+'EStEF_MSEPanel').collapsed && !base_form.findField('InvalidGroupType_id').disabled ) {
			base_form.findField('InvalidGroupType_id').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_stacEndDate').disabled ) {
			base_form.findField('EvnStick_stacEndDate').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_irrDate').disabled ) {
			base_form.findField('EvnStick_irrDate').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_EvnStickCarePersonPanel').hidden && !this.findById(me.id+'EStEF_EvnStickCarePersonPanel').collapsed && this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0 ) {
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
		}
		else if ( !base_form.findField('Org_did').hidden && !base_form.findField('Org_did').disabled ) {
			base_form.findField('Org_did').focus(true);
		}
		else if ( !base_form.findField('EvnStick_BirthDate').hidden && !base_form.findField('EvnStick_BirthDate').disabled ) {
			base_form.findField('EvnStick_BirthDate').focus(true);
		}
		else if ( !base_form.findField('StickCause_did').disabled ) {
			base_form.findField('StickCause_did').focus(true);
		}
		else {
			me._focusButtonCancel();
		}
	},
	_keydownFocus3: function(){
		var me = this;
		var base_form = this.FormPanel.getForm();
		if ( !this.findById(me.id+'EStEF_MSEPanel').collapsed && !base_form.findField('InvalidGroupType_id').disabled ) {
			base_form.findField('InvalidGroupType_id').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_stacEndDate').disabled ) {
			base_form.findField('EvnStick_stacEndDate').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_irrDate').disabled ) {
			base_form.findField('EvnStick_irrDate').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_EvnStickCarePersonPanel').hidden && !this.findById(me.id+'EStEF_EvnStickCarePersonPanel').collapsed && this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0 ) {
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
		}
		else if ( !base_form.findField('Org_did').hidden && !base_form.findField('Org_did').disabled ) {
			base_form.findField('Org_did').focus(true);
		}
		else if ( !base_form.findField('EvnStick_BirthDate').hidden && !base_form.findField('EvnStick_BirthDate').disabled ) {
			base_form.findField('EvnStick_BirthDate').focus(true);
		}
		else if ( !base_form.findField('StickCause_did').disabled ) {
			base_form.findField('StickCause_did').focus(true);
		}
		else {
			me._focusButtonCancel();
		}
	},
	_keydownFocus2: function(){
		var me = this;
		var base_form = this.FormPanel.getForm();
		if ( !this.findById(me.id+'EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_stacEndDate').disabled ) {
			base_form.findField('EvnStick_stacEndDate').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_StickRegimePanel').collapsed && !base_form.findField('EvnStick_irrDate').disabled ) {
			base_form.findField('EvnStick_irrDate').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_EvnStickCarePersonPanel').hidden && !this.findById(me.id+'EStEF_EvnStickCarePersonPanel').collapsed && this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getStore().getCount() > 0 ) {
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getView().focusRow(0);
			this.findById(me.id+'EStEF_EvnStickCarePersonGrid').getSelectionModel().selectFirstRow();
		}
		else if ( !base_form.findField('Org_did').hidden && !base_form.findField('Org_did').disabled ) {
			base_form.findField('Org_did').focus(true);
		}
		else if ( !base_form.findField('EvnStick_BirthDate').hidden && !base_form.findField('EvnStick_BirthDate').disabled ) {
			base_form.findField('EvnStick_BirthDate').focus(true);
		}
		else if ( !base_form.findField('StickCause_did').disabled ) {
			base_form.findField('StickCause_did').focus(true);
		}
		else {
			me._focusButtonCancel();
		}
	},
	_keydownFocus_noPanels_CarePersonAndRegimeAndMSEAndWorkRelease: function(){
		var me = this;
		var base_form = this.FormPanel.getForm();
		if ( !this.findById(me.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('StickLeaveType_id').disabled ) {
			base_form.findField('StickLeaveType_id').focus(true);
		}
		else if ( this.action != 'view' ) {
			me._focusButtonSave();
		}
		else {
			me._focusButtonPrint();
		}
	},
	_keydownFocus_noPanels_CarePersonAndRegimeAndMSE: function(){
		var me = this;
		var base_form = this.FormPanel.getForm();
		if ( !this.findById(me.id+'EStEF_EvnStickWorkReleasePanel').collapsed && me._getCount_WorkRelease() > 0 ) {
			me._focusFirst_WorkRelease()
		}
		else if ( !this.findById(me.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('StickLeaveType_id').disabled ) {
			base_form.findField('StickLeaveType_id').focus(true);
		}
		else if ( this.action != 'view' ) {
			me._focusButtonSave();
		}
		else {
			me._focusButtonPrint();
		}
	},
	_keydownFocus_noPanels_CarePersonAndRegime: function(){
		var me = this;
		var base_form = this.FormPanel.getForm();
		if ( !this.findById(me.id+'EStEF_MSEPanel').collapsed && this.action != 'view' ) {
			base_form.findField('EvnStick_mseDate').focus(true);
		}
		else if ( !this.findById(me.id+'EStEF_EvnStickWorkReleasePanel').collapsed && me._getCount_WorkRelease() > 0 ) {
			me._focusFirst_WorkRelease();
		}
		else if ( !this.findById(me.id+'EStEF_StickLeavePanel').collapsed && !base_form.findField('StickLeaveType_id').disabled ) {
			base_form.findField('StickLeaveType_id').focus(true);
		}
		else if ( this.action != 'view' ) {
			me._focusButtonSave();
		}
		else {
			me._focusButtonPrint();
		}
	},
	_keydownFocus: function(){
		var me = this;
		var base_form = this.FormPanel.getForm();



		if(me._isFocusAccessCarePersonPanel()) {
			me._focusCarePersonPanel();

		} else if(
			! this.findById(me.id+'EStEF_StickRegimePanel').collapsed &&
			this.action != 'view'
		) {

					if ( ! base_form.findField('EvnStick_IsRegPregnancy').hidden ) {
						base_form.findField('EvnStick_IsRegPregnancy').focus(true);
					} else {
						base_form.findField('StickIrregularity_id').focus(true);
					}

		} else if(
			! this.findById(me.id+'EStEF_MSEPanel').collapsed &&
			this.action != 'view'
		) {

					base_form.findField('EvnStick_mseDate').focus(true);

		} else if(me._isFocusAccess_WorkReleasePanel()) {
			me._focus_WorkReleasePanel();


		} else if(me._isFocusAccess_StickLeaveType_id()) {
			me._focus_StickLeaveType_id();

		} else if(
			this.action != 'view'
		) {

					me._focusButtonSave();

		} else {

					me._focusButtonPrint();

		}
	},




	_getSslHash: function(data){
		var me = this;

		var sshHashData = {
			xml: null,
			Base64ToSign: null,
			Hash: null
		};

		me.getLoadMask('Получение данных для подписи ЛВН').show();

		$.ajax({
			type: "POST",
			url: '/?c=Stick&m=getWorkReleaseSslHash',
			data: data,
			async: false,
			success: function(response){
				var result = Ext.util.JSON.decode(response);

				if ( ! result.success) {
					sw.swMsg.alert(langs('Ошибка'), result.Error_Msg);
				}

				if (result.xml) {

					sshHashData.xml = result.xml;
					sshHashData.Base64ToSign = result.Base64ToSign;
					sshHashData.Hash = result.Hash;
				}

			}
		});


		me.getLoadMask().hide();

		return sshHashData;
	},
	setSnilsButtonOnClick: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();

		getWnd('swPersonEditWindow').show({
			Person_id: base_form.findField('Person_id').getValue(),
			focused: 'Person_SNILS',
			callback: function(result) {
				
				if(!Ext.isEmpty(result.PersonData.Person_Snils)) {
					win.Person_Snils = result.PersonData.Person_Snils
				} else {
					win.Person_Snils = null;
				}
				win._checkSnils();
			}
		});
	},



	_stopPagination: function(e){
		var me = this;

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

		if ( Ext.isIE ) {
			e.browserEvent.keyCode = 0;
			e.browserEvent.which = 0;
		}
	}
});