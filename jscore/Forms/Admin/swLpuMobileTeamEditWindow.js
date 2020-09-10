/**
* swLpuMobileTeamEditWindow - редактирование мобильной бригады
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko aka DimICE (work@dimice.ru)
* @version      22.08.2013
* @comment      Префикс для id компонентов LMTEW (LpuMobileTeamEditWindow)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swLpuMobileTeamEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swLpuMobileTeamEditWindow',
	objectSrc: '/jscore/Forms/Admin/swLpuMobileTeamEditWindow.js',
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

		var base_form = this.FormPanel.getForm();

		if (
			(!base_form.findField('TypeBrig1').checked)
			&& (!base_form.findField('TypeBrig1').checked)
			&& (!base_form.findField('TypeBrig2').checked)
			&& (!base_form.findField('TypeBrig3').checked)
			&& (!base_form.findField('TypeBrig4').checked)
			&& (!base_form.findField('TypeBrig5').checked)
			&& (!base_form.findField('TypeBrig6').checked)
			&& (!base_form.findField('TypeBrig7').checked)
			&& (!base_form.findField('TypeBrig8').checked)
			&& (!base_form.findField('TypeBrig9').checked)
			&& (!base_form.findField('TypeBrig10').checked)
		) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['doljen_byit_otmechen_hotya_byi_odin_tip_brigadyi'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
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
		
		base_form.submit({
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
				if ( action.result && action.result.success && !Ext.isEmpty(action.result.LpuMobileTeam_id) ) {
					this.callback(this.owner, action.result.LpuMobileTeam_id);
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	formStatus: 'edit',
	id: 'LpuMobileTeamEditWindow',
	initComponent: function() {
		var form = this;
		
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'LpuMobileTeamEditForm',
			labelAlign: 'right',
			labelWidth: 110,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'LpuMobileTeam_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuMobileTeam_begDate' },
				{ name: 'LpuMobileTeam_endDate' },
				{ name: 'LpuMobileTeam_Count' },
				{ name: 'TypeBrig1' },
				{ name: 'TypeBrig2' },
				{ name: 'TypeBrig3' },
				{ name: 'TypeBrig4' },
				{ name: 'TypeBrig5' },
				{ name: 'TypeBrig6' },
				{ name: 'TypeBrig7' },
				{ name: 'TypeBrig8' },
				{ name: 'TypeBrig9' },
				{ name: 'TypeBrig10' }
			]),
			url: '/?c=LpuPassport&m=saveLpuMobileTeam',
			items: [{
				name: 'LpuMobileTeam_id',
				xtype: 'hidden'
			}, {
				name: 'Lpu_id',
				xtype: 'hidden'
			}, {
				name: 'LpuMobileTeam_begDate',
				allowBlank: false,
				fieldLabel: lang['data_nachala'],
				xtype: 'swdatefield'
			}, {
				name: 'LpuMobileTeam_endDate',
				fieldLabel: lang['data_okonchaniya'],
				xtype: 'swdatefield'
			}, {
				name: 'LpuMobileTeam_Count',
				allowBlank: false,
				fieldLabel: lang['kolichestvo_brigad'],
				xtype: 'numberfield'
			}, {
				xtype: 'fieldset',
				title: lang['tip_brigadyi'],
				autoHeight: true,
				// правильнее динамические чекбоксы из справочника DispClass, но задача прям немедленная, что времени на данную реализацию нет, так что TODO
				items: [{
					xtype: 'checkbox',
					name: 'TypeBrig1',
					hideLabel: true,
					boxLabel: lang['disp-tsiya_vzr_naseleniya_1-yiy_etap']
				}, {
					xtype: 'checkbox',
					name: 'TypeBrig2',
					hideLabel: true,
					boxLabel: lang['disp-tsiya_vzr_naseleniya_2-oy_etap']
				}, {
					xtype: 'checkbox',
					name: 'TypeBrig3',
					hideLabel: true,
					boxLabel: lang['disp-tsiya_detey-sirot_statsionarnyih_1-yiy_etap']
				}, {
					xtype: 'checkbox',
					name: 'TypeBrig4',
					hideLabel: true,
					boxLabel: lang['disp-tsiya_detey-sirot_statsionarnyih_2-oy_etap']
				}, {
					xtype: 'checkbox',
					name: 'TypeBrig5',
					hideLabel: true,
					boxLabel: lang['prof_osmotryi_vzr_naseleniya']
				}, {
					xtype: 'checkbox',
					name: 'TypeBrig6',
					hideLabel: true,
					boxLabel: lang['periodicheskie_osmotryi_nesovershennoletnih']
				}, {
					xtype: 'checkbox',
					name: 'TypeBrig7',
					hideLabel: true,
					boxLabel: lang['disp-tsiya_detey-sirot_usyinovlennyih_1-oy_etap']
				}, {
					xtype: 'checkbox',
					name: 'TypeBrig8',
					hideLabel: true,
					boxLabel: lang['disp-tsiya_detey-sirot_usyinovlennyih_2-oy_etap']
				}, {
					xtype: 'checkbox',
					name: 'TypeBrig9',
					hideLabel: true,
					boxLabel: lang['predvaritelnyie_osmotryi_nesovershennoletnih']
				}, {
					xtype: 'checkbox',
					name: 'TypeBrig10',
					hideLabel: true,
					boxLabel: lang['profilakticheskie_osmotryi_nesovershennoletnih']
				}]		
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
				tabIndex: TABINDEX_LMTEW + 4,
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
					if ( !base_form.findField('LpuMobileTeam_begDate').disabled ) {
						base_form.findField('LpuMobileTeam_begDate').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_LMTEW + 5,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swLpuMobileTeamEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('LpuMobileTeamEditWindow');

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
		sw.Promed.swLpuMobileTeamEditWindow.superclass.show.apply(this, arguments);

		this.center();
		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.doLayout();
		this.center();
		
		this.action = 'add';
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.LpuMobileTeam_id = null;
		
		if ( !arguments[0] || !arguments[0].Lpu_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.Lpu_id = arguments[0].Lpu_id;
		base_form.findField('Lpu_id').setValue(this.Lpu_id);
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		
		if ( arguments[0].LpuMobileTeam_id ) {
			this.LpuMobileTeam_id = arguments[0].LpuMobileTeam_id;
		}
		
		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.getLoadMask().show();
		
		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['mobilnaya_brigada_dobavlenie']);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(lang['mobilnaya_brigada_redaktirovanie']);
					this.enableEdit(true);
				}
				else {
					this.setTitle(lang['mobilnaya_brigada_prosmotr']);
					this.enableEdit(false);
				}
				
				if (Ext.isEmpty(win.LpuMobileTeam_id)) {
					sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
					return false;
				} else {
					base_form.load({
						params: {
							LpuMobileTeam_id: win.LpuMobileTeam_id
						},
						failure: function() {
							win.getLoadMask().hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_mobilnoy_brigadyi'], function() { win.hide(); } );
						},
						success: function() {
							win.getLoadMask().hide();
							base_form.clearInvalid();
						},
						url: '/?c=LpuPassport&m=loadLpuMobileTeam'
					});			
				}
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('LpuMobileTeam_begDate').disabled ) {
			base_form.findField('LpuMobileTeam_begDate').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 600
});