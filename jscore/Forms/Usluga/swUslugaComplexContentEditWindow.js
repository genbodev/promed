/**
* swUslugaComplexContentEditWindow - редактирование состава услуги
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      15.05.2014
* @comment      Префикс для id компонентов UCCEW (UslugaComplexContentEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swUslugaComplexContentEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaComplexContentEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swUslugaComplexContentEditWindow.js',

	action: null,
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

		var data = new Object();
		
		if (base_form.findField('UslugaComplex_id').getValue() == base_form.findField('UslugaComplex_pid').getValue()) {
			sw.swMsg.alert(lang['oshibka'], lang['nelzya_dobavlyat_uslugu_v_sostav_samoy_sebya'], function() { 
				this.formStatus = 'edit';
				loadMask.hide();
			}.createDelegate(this));
			
			return false;
		}
		
		data.UslugaComplexContentData = {
			'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
			'UslugaComplex_pid': base_form.findField('UslugaComplex_pid').getValue(),
			'UslugaComplex_Code': base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code'),
			'UslugaComplex_Name': base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Name'),
			'UslugaCategory_id': base_form.findField('UslugaCategory_id').getValue(),
			'UslugaCategory_Name': base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_Name'),
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue(),
			'UslugaComplexComposition_id': base_form.findField('UslugaComplexComposition_id').getValue()
		};
		
		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();
				
				this.callback(data);
				this.hide();
			break;

			case 'remote':
                var params = {};
                if ( base_form.findField('UslugaCategory_id').disabled ) {
                    params.UslugaCategory_id = base_form.findField('UslugaCategory_id').getValue();
                }
				base_form.submit({
                    params: params,
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.UslugaComplexComposition_id > 0 ) {
							base_form.findField('UslugaComplexComposition_id').setValue(action.result.UslugaComplexComposition_id);
							data.UslugaComplexContentData.UslugaComplexComposition_id = base_form.findField('UslugaComplexComposition_id').getValue();

							this.callback(data);
							this.hide();
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
	id: 'UslugaComplexContentEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'UslugaComplexContentEditForm',
			labelAlign: 'right',
			labelWidth: 110,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'UslugaComplex_id' },
				{ name: 'UslugaComplex_pid' },
				{ name: 'UslugaCategory_id' },
				{ name: 'UslugaComplexComposition_id' }
			]),
			url: '/?c=UslugaComplex&m=saveUslugaComplexComposition',
			items: [{
				name: 'UslugaComplexComposition_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaComplex_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				hiddenName: 'UslugaCategory_id',
                comboSubject: 'UslugaCategory',
				fieldLabel: lang['kategoriya'],
				tabIndex: TABINDEX_UCCEW,
				width: 400,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						
						var uslugaCombo = base_form.findField('UslugaComplex_id');
						uslugaCombo.clearValue();
						
						if (!Ext.isEmpty(newValue)) {
							uslugaCombo.getStore().filterBy(function(record) {
								if (record.get('UslugaCategory_id') == newValue) {
									return true;
								} else {
									return false;
								}
							});
							uslugaCombo.getStore().baseParams.UslugaCategory_id = newValue;
							this.lastQuery = 'This query sample that is not will never appear';
						} else {
							uslugaCombo.getStore().clearFilter();
							delete uslugaCombo.getStore().baseParams.UslugaCategory_id;
							this.lastQuery = 'This query sample that is not will never appear';
						}
					}.createDelegate(this)
				},
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: lang['usluga'],
				hiddenName: 'UslugaComplex_id',
				allowBlank: false,
				listWidth: 600,
				tabIndex: TABINDEX_UCCEW + 1,
				width: 400,
				xtype: 'swuslugacomplexallcombo'
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

					if ( !base_form.findField('UslugaComplex_id').disabled ) {
						base_form.findField('UslugaComplex_id').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_UCCEW + 2,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
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
					if ( !base_form.findField('UslugaCategory_id').disabled ) {
						base_form.findField('UslugaCategory_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_UCCEW + 3,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swUslugaComplexContentEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaComplexContentEditWindow');

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
		sw.Promed.swUslugaComplexContentEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.doLayout();
		this.center();
		
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
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
        this.commonUslugaCategory_id = arguments[0].commonUslugaCategory_id || null;

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'local' ) {
			this.formMode = 'local';
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.getLoadMask().show();
		
		var uslugaCombo = base_form.findField('UslugaComplex_id');
        uslugaCombo.getStore().clearFilter();
        uslugaCombo.lastQuery = 'This query sample that is not will never appear';
        uslugaCombo.getStore().baseParams = {};
		uslugaCombo.getStore().baseParams.UslugaComplex_ForLpuFilter_pid = base_form.findField('UslugaComplex_pid').getValue();
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_USLUGA_CONTENT_ADD);
				this.enableEdit(true);
                if ( this.commonUslugaCategory_id > 0 ) {
                    base_form.findField('UslugaCategory_id').setValue(this.commonUslugaCategory_id);
                    uslugaCombo.getStore().baseParams.UslugaCategory_id = this.commonUslugaCategory_id;
                    base_form.findField('UslugaCategory_id').disable();
                }
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(WND_USLUGA_CONTENT_EDIT);
					this.enableEdit(true);
                    if ( this.commonUslugaCategory_id > 0 ) {
                        base_form.findField('UslugaCategory_id').setValue(this.commonUslugaCategory_id);
                        uslugaCombo.getStore().baseParams.UslugaCategory_id = this.commonUslugaCategory_id;
                        base_form.findField('UslugaCategory_id').disable();
                    }
				} else {
					this.setTitle(WND_USLUGA_CONTENT_VIEW);
					this.enableEdit(false);
				}
                uslugaCombo.getStore().load({
                    params: {
                        UslugaComplex_id: uslugaCombo.getValue()
                    },
                    callback: function() {
                        if (uslugaCombo.getStore().getById(uslugaCombo.getValue())) {
                            uslugaCombo.setValue(uslugaCombo.getValue());
                        } else {
                            uslugaCombo.setValue(null);
                        }
                    }
                });

				this.getLoadMask().hide();
				base_form.clearInvalid();
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('UslugaCategory_id').disabled ) {
			base_form.findField('UslugaCategory_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 600
});
