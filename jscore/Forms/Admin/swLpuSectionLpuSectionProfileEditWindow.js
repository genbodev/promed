/**
* swLpuSectionLpuSectionProfileEditWindow - редактирование дополнительного профиля отделения
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      LpuStructure
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stanislav Bykov (work@dimice.ru)
* @version      15.05.2014
* @comment      Префикс для id компонентов LSLSPEW (LpuSectionLpuSectionProfileEditWindow)
*/
sw.Promed.swLpuSectionLpuSectionProfileEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

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

		// Собираем данные с формы
		var data = new Object();
		
		data.lpuSectionProfileData = {
			'LpuSectionLpuSectionProfile_id': base_form.findField('LpuSectionLpuSectionProfile_id').getValue(),
			'LpuSectionProfile_id': base_form.findField('LpuSectionProfile_id').getValue(),
			'LpuSectionProfile_Code': base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code'),
			'LpuSectionProfile_Name': base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Name'),
			'LpuSectionLpuSectionProfile_begDate': base_form.findField('LpuSectionLpuSectionProfile_begDate').getValue(),
			'LpuSectionLpuSectionProfile_endDate': base_form.findField('LpuSectionLpuSectionProfile_endDate').getValue(),
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue()
		};

		this.formStatus = 'edit';
		loadMask.hide();

		var success = true;

		if ( typeof this.callback == 'function' ) {
			success = this.callback(data);
		}

		if ( success == true ) {
			this.hide();
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
	id: 'LpuSectionLpuSectionProfileEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'LpuSectionLpuSectionProfileEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'LpuSectionLpuSectionProfile_id' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'LpuSectionLpuSectionProfile_begDate' },
				{ name: 'LpuSectionLpuSectionProfile_endDate' },
				{ name: 'RecordStatus_Code' }
			]),
			url: '/?c=LpuStructure&m=saveLpuSectionLpuSectionProfile',
			items: [{
				name: 'LpuSectionLpuSectionProfile_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}/*, {
				allowBlank: false,
				comboSubject: 'LpuSectionProfile',
				hiddenName: 'LpuSectionProfile_id',
				fieldLabel: lang['profil_otdeleniya'],
				moreFields: [ {name: 'LpuSectionProfile_endDT', type: 'date', dateFormat: 'd.m.Y'} ],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						// Установить допустимые даты действия профиля
					}.createDelegate(this)
				},
				lostWidth: 600,
				//tabIndex: TABINDEX_LSLSPEW,
				width: 400,
				xtype: 'swcommonsprcombo'
			}*/, {
				allowBlank: false,
				fieldLabel: lang['profil_otdeleniya'],
				autoLoad: false,
				disabled: false,
				hiddenName: 'LpuSectionProfile_id',
				lastQuery: '',
				width: 400,
				lostWidth: 600,
				xtype: 'swlpusectionprofilecombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_nachala'],
				format: 'd.m.Y',
				name: 'LpuSectionLpuSectionProfile_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				//tabIndex: TABINDEX_LSLSPEW + 6,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['data_okonchaniya'],
				format: 'd.m.Y',
				name: 'LpuSectionLpuSectionProfile_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				//tabIndex: TABINDEX_LSLSPEW + 7,
				xtype: 'swdatefield'
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

					if ( !base_form.findField('LpuSectionLpuSectionProfile_endDate').disabled ) {
						base_form.findField('LpuSectionLpuSectionProfile_endDate').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				//tabIndex: TABINDEX_LSLSPEW + 8,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this/*, TABINDEX_LSLSPEW + 9*/),
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

					if ( !base_form.findField('LpuSectionProfile_id').disabled ) {
						base_form.findField('LpuSectionProfile_id').focus(true);
					}
				}.createDelegate(this),
				//tabIndex: TABINDEX_LSLSPEW + 10,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swLpuSectionLpuSectionProfileEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('LpuSectionLpuSectionProfileEditWindow');

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
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swLpuSectionLpuSectionProfileEditWindow.superclass.show.apply(this, arguments);

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

		if ( typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		this.getLoadMask().show();

		base_form.findField('LpuSectionLpuSectionProfile_begDate').setMaxValue(undefined);
		base_form.findField('LpuSectionLpuSectionProfile_begDate').setMinValue(undefined);
		base_form.findField('LpuSectionLpuSectionProfile_endDate').setMaxValue(undefined);
		base_form.findField('LpuSectionLpuSectionProfile_endDate').setMinValue(undefined);

		if ( !Ext.isEmpty(arguments[0].LpuSection_setDate) ) {
			base_form.findField('LpuSectionLpuSectionProfile_begDate').setMinValue(typeof arguments[0].LpuSection_setDate == 'object' ? Ext.util.Format.date(arguments[0].LpuSection_setDate, 'd.m.Y') : arguments[0].LpuSection_setDate);
			base_form.findField('LpuSectionLpuSectionProfile_endDate').setMinValue(typeof arguments[0].LpuSection_setDate == 'object' ? Ext.util.Format.date(arguments[0].LpuSection_setDate, 'd.m.Y') : arguments[0].LpuSection_setDate);
		}

		if ( !Ext.isEmpty(arguments[0].LpuSection_disDate) ) {
			base_form.findField('LpuSectionLpuSectionProfile_begDate').setMaxValue(typeof arguments[0].LpuSection_disDate == 'object' ? Ext.util.Format.date(arguments[0].LpuSection_disDate, 'd.m.Y') : arguments[0].LpuSection_disDate);
			base_form.findField('LpuSectionLpuSectionProfile_endDate').setMaxValue(typeof arguments[0].LpuSection_disDate == 'object' ? Ext.util.Format.date(arguments[0].LpuSection_disDate, 'd.m.Y') : arguments[0].LpuSection_disDate);
		}

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_LPUSECTION_LPUSECTIONPROFILE_ADD);
				this.enableEdit(true);
			break;

			case 'edit':
				this.setTitle(WND_LPUSECTION_LPUSECTIONPROFILE_EDIT);
				this.enableEdit(true);
			break;

			case 'view':
				this.setTitle(WND_LPUSECTION_LPUSECTIONPROFILE_VIEW);
				this.enableEdit(false);
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}

		this.getLoadMask().hide();

		if ( !base_form.findField('LpuSectionProfile_id').disabled ) {
			base_form.findField('LpuSectionProfile_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}

		if(getRegionNick() == 'khak'){
			base_form.findField('LpuSectionProfile_id').getStore().filterBy(function(rec){
				// NGS: #196545 WAS ADDED - || rec.get('LpuSectionProfile_Code') == 134
				return ((Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || (!Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) && rec.get('LpuSectionProfile_endDT') > new Date())) || rec.get('LpuSectionProfile_Code') == 134);
			});

			base_form.findField('LpuSectionProfile_id').setBaseFilter(function(rec){
				// NGS: #196545 WAS ADDED - || rec.get('LpuSectionProfile_Code') == 134
				return ((Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || (!Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) && rec.get('LpuSectionProfile_endDT') > new Date())) || rec.get('LpuSectionProfile_Code') == 134);
			});
		}
	},
	width: 600
});