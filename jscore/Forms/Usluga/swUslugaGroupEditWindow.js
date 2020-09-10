/**
* swUslugaGroupEditWindow - Форма добавления/редактирования группы услуг
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Usluga
* @access		public
* @copyright	Copyright (c) 2014 Swan Ltd.
* @author		Dmitriy Vlasenko
* @version		29.09.2014
* @comment		Префикс для id компонентов UGEF (UslugaGroupEditForm)
*/

sw.Promed.swUslugaGroupEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaGroupEditWindow',
	objectSrc: '/jscore/Forms/Usluga/swUslugaGroupEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.formPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение услуги..." });
		loadMask.show();
		
		base_form.submit({
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
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.UslugaComplex_id > 0 ) {
						base_form.findField('UslugaComplex_id').setValue(action.result.UslugaComplex_id);

						var data = {
							'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
							'UslugaComplex_pid': base_form.findField('UslugaComplex_pid').getValue(),
							'accessType': 'edit'
						};

						this.callback(data);
						this.hide();
					}
					else {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	formStatus: 'edit',
	height: 200,
	id: 'UslugaGroupEditWindow',
	initComponent: function() {
		var form = this;

		form.formPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'UslugaGroupEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'UslugaCategory_id' },
				{ name: 'UslugaCategory_SysNick' },
				{ name: 'Lpu_id' },
				{ name: 'UslugaComplex_id' },
				{ name: 'UslugaComplex_begDate' },
				{ name: 'UslugaComplex_Code' },
				{ name: 'UslugaComplex_endDate' },
				{ name: 'UslugaComplex_Name' },
				{ name: 'UslugaComplex_pid' }
			]),
			region: 'center',
			url: '/?c=UslugaComplex&m=saveUslugaComplexGroup',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'UslugaComplex_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaCategory_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'UslugaCategory_SysNick',
				value: '',
				xtype: 'hidden'
			}, {
				hiddenName: 'UslugaComplex_pid',
				fieldLabel: lang['verhniy_uroven'],
				width: 300,
				xtype: 'swuslugacomplexgroupcombo'
			}, {
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: lang['kod'],
				listeners: {
					'keydown': function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.TAB:
								if ( e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							break;
						}
					}.createDelegate(this)
				},
				name: 'UslugaComplex_Code',
				// tabIndex: TABINDEX_UGEF + 1,
				width: 300,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: lang['naimenovanie'],
				name: 'UslugaComplex_Name',
				// tabIndex: TABINDEX_UGEF + 1,
				width: 300,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_nachala'],
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = this.formPanel.getForm();
					}.createDelegate(this)
				},
				name: 'UslugaComplex_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				// tabIndex: TABINDEX_UGEF + 4,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: true,
				fieldLabel: lang['data_okonchaniya'],
				format: 'd.m.Y',
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = this.formPanel.getForm();
					}.createDelegate(this)
				},
				name: 'UslugaComplex_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				// tabIndex: TABINDEX_UGEF + 4,
				width: 100,
				xtype: 'swdatefield'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSave();
				},
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = form.formPanel.getForm();

					if ( form.action == 'view' ) {
						form.buttons[form.buttons.length - 1].focus(true);
					}
				},
				onTabAction: function () {
					form.buttons[1].focus(true);
				},
				// tabIndex: TABINDEX_UGEF + 44,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(form, -1),
			{
				handler: function() {
					form.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					form.buttons[1].focus();
				},
				onTabAction: function () {
					if ( form.action == 'edit' ) {
						form.formPanel.getForm().findField('UslugaComplex_Code').focus(true);
					}
					else {
						form.buttons[1].focus(true);
					}
				},
				// tabIndex: TABINDEX_UGEF + 46,
				text: BTN_FRMCANCEL
			}],
			items: [
				 form.formPanel
			],
			layout: 'border'
		});

		sw.Promed.swUslugaGroupEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaGroupEditWindow');

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
		stopEvent: true
	}],
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	onChangeUslugaComplexPid: function() {
		var base_form = this.formPanel.getForm();
		var UslugaComplex_pid = base_form.findField('UslugaComplex_pid').getValue();
		if (Ext.isEmpty(UslugaComplex_pid)) {
			base_form.findField('UslugaComplex_pid').hideContainer();
			base_form.findField('UslugaComplex_pid').setAllowBlank(true);
		} else {
			base_form.findField('UslugaComplex_pid').showContainer();
			base_form.findField('UslugaComplex_pid').setAllowBlank(false);
			base_form.findField('UslugaComplex_pid').getStore().baseParams.filterByUslugaComplex_id = UslugaComplex_pid;
			base_form.findField('UslugaComplex_pid').getStore().load({
				callback: function() {
					base_form.findField('UslugaComplex_pid').setValue(UslugaComplex_pid);
				},
				params: {
					filterByUslugaComplex_id: UslugaComplex_pid
				}
			})
		}
	},
    /**
     * @param {Object} params
     * @param {String} params.action inlist([ 'add', 'edit', 'view' ])
     * @param {Object} params.formParams
     * @param {Integer} params.formParams.UslugaCategory_id Обязательный параметр при добавлении услуги
     * @param {Integer} params.formParams.UslugaCategory_SysNick Обязательный параметр при добавлении услуги
     * @param {Integer} params.formParams.Lpu_id Обязательный параметр при добавлении услуги с категорий "Услуги ЛПУ"
     * @param {Integer} params.formParams.UslugaComplex_pid Обязательный параметр при добавлении услуги в состав другой услуги
     * @param {Integer} params.formParams.UslugaComplexLevel_id Параметр при добавлении услуги, непонятно для чего
     * @param {Integer} params.formParams.UslugaComplex_id Обязательный параметр при просмотре/редактировании услуги
     * @param {Function} params.callback
     * @param {Function} params.onHide
     * @return {Boolean}
     */
	show: function(params) {
		sw.Promed.swUslugaGroupEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.formPanel.getForm();
		base_form.reset();

		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

        this.action = arguments[0].action || null;
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.onHide = arguments[0].onHide || Ext.emptyFn;
        if ( this.action == 'add' && !arguments[0].formParams.UslugaCategory_SysNick ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi_2'], function() { this.hide(); }.createDelegate(this) );
            return false;
        }
        base_form.setValues(arguments[0].formParams);
		this.onChangeUslugaComplexPid();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_USLUGA_UGEFADD);
				this.enableEdit(true);

				loadMask.hide();

				base_form.findField('UslugaComplex_Code').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				var usluga_complex_id = base_form.findField('UslugaComplex_id').getValue();

				if ( !usluga_complex_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'UslugaComplex_id': usluga_complex_id
					},
					success: function() {
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						this.onChangeUslugaComplexPid();
						
						if ( this.action == 'edit' ) {
							this.setTitle(WND_USLUGA_UGEFEDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_USLUGA_UGEFEDIT);
							this.enableEdit(false);
						}

						loadMask.hide();

						if ( this.action == 'edit' ) {
							base_form.findField('UslugaComplex_Code').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=UslugaComplex&m=loadUslugaComplexGroupEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 550
});