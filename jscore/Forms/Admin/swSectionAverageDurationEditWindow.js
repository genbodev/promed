/**
* swSectionAverageDurationEditWindow - редактирование тарифов на услугу
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      07.04.2013
* @comment      Префикс для id компонентов SADEW (SectionAverageDurationEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swSectionAverageDurationEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swSectionAverageDurationEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swSectionAverageDurationEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

		if ( typeof options != 'object' ) {
			options = new Object();
        }
		
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var wnd = this;
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();
		
		var formBegDate = base_form.findField('SectionAverageDuration_begDate').getValue();
		var formEndDate = base_form.findField('SectionAverageDuration_endDate').getValue();
		
		data.SectionAverageDurationData = {
			'SectionAverageDuration_id': base_form.findField('SectionAverageDuration_id').getValue(),
			'LpuSection_id': base_form.findField('LpuSection_id').getValue(),
			'SectionAverageDuration_Duration': base_form.findField('SectionAverageDuration_Duration').getValue(),
			'SectionAverageDuration_begDate': formBegDate,
			'SectionAverageDuration_endDate': formEndDate,
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue()
		};

		log(data);
		
		var params = {};
		
		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();

				this.callback(data);
				if (!options.notHide) {
					this.hide();
				}
			break;

			case 'remote':
				base_form.submit({
					params: params,
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();
					}.createDelegate(this),
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.SectionAverageDuration_id > 0 ) {
							// base_form.findField('SectionAverageDuration_id').setValue(action.result.SectionAverageDuration_id);
							data.SectionAverageDurationData.SectionAverageDuration_id = action.result.SectionAverageDuration_id;
							this.callback(data);
							if (!options.notHide) {
								this.hide();
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	draggable: true,
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'SectionAverageDurationEditWindow',
	initComponent: function() {
		var wnd = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'SectionAverageDurationEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'SectionAverageDuration_id' },
				{ name: 'LpuSection_id' },
				{ name: 'SectionAverageDuration_Duration' },
				{ name: 'SectionAverageDuration_begDate' },
				{ name: 'SectionAverageDuration_endDate' }
			]),
			url: '/?c=LpuStructure&m=saveSectionAverageDuration',
			items: [{
				name: 'SectionAverageDuration_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'LpuSection_id',
				value: null,
				xtype: 'hidden'
			}, {
				xtype: 'numberfield',
				name: 'SectionAverageDuration_Duration',
				maxValue: 9999,
				minValue: 0,
				allowBlank: false,
				allowDecimals: true,
				autoCreate: {tag: "input", size:8, autocomplete: "off"},
				tabIndex: TABINDEX_SADEW + 0,
				fieldLabel: lang['srednyaya_prodoljitelnost']
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				xtype: 'swdatefield',
				fieldLabel: lang['data_nachala'],
				format: 'd.m.Y',
				allowBlank: false,
				tabIndex: TABINDEX_SADEW + 1,
				name: 'SectionAverageDuration_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			},
			{
				xtype: 'swdatefield',
				fieldLabel: lang['data_okonchaniya'],
				format: 'd.m.Y',
				tabIndex: TABINDEX_SADEW + 2,
				name: 'SectionAverageDuration_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('SectionAverageDuration_endDate').disabled ) {
						base_form.findField('SectionAverageDuration_endDate').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_SADEW + 10,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_SADEW + 11),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();
					if ( !base_form.findField('SectionAverageDuration_Duration').disabled ) {
						base_form.findField('SectionAverageDuration_Duration').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_SADEW + 12,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swSectionAverageDurationEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('SectionAverageDurationEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swSectionAverageDurationEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'local';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		var uslugaLpu_id = getGlobalOptions().lpu_id;
		
		if ( arguments[0].Lpu_id && !Ext.isEmpty(arguments[0].Lpu_id) ) {
			uslugaLpu_id = arguments[0].Lpu_id;
		}
		
		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
	
		this.getLoadMask().show();
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_SECTIONAVERAGEDURATION_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
				this.setTitle(WND_SECTIONAVERAGEDURATION_EDIT);
				this.enableEdit(true);
				this.getLoadMask().hide();
				base_form.clearInvalid();
			break;
			
			case 'view':
				this.setTitle(WND_SECTIONAVERAGEDURATION_VIEW);
				this.enableEdit(false);
				this.getLoadMask().hide();
				base_form.clearInvalid();
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('SectionAverageDuration_Duration').disabled ) {
			base_form.findField('SectionAverageDuration_Duration').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 500
});