/**
 * swEvnPLDispDop13TransferWindow - окно переноса в карту проф. осмотра
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       Dmitriy Vlasenko
 */

sw.Promed.swEvnPLDispDop13TransferWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 500,
	id: 'EvnPLDispDop13TransferWindow',
	initComponent: function() {
		var win = this;

		this.EvnUslugaDispDopSuccess = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			filterByFieldEnabled: true,
			border: false,
			dataUrl: '/?c=EvnPLDispDop13&m=loadEvnUslugaDispDopTransferSuccessGrid',
			focusOn: {
				name: 'EPLDD13TW_SaveButton',
				type: 'button'
			},
			focusPrev: {
				name: 'EPLDD13TW_CloseButton',
				type: 'button'
			},
			id: 'EPLDD13TW_EvnUslugaDispDopSuccess',
			paging: false,
			region: 'center',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnUslugaDispDop_id', type: 'int', header: 'ID', key: true },
				{ name: 'SurveyType_Name', type: 'string', header: 'Наименование осмотра (исследования)', id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsEarlier', type: 'checkcolumn', header: 'Пройдено ранее', width: 100 },
				{ name: 'EvnUslugaDispDop_didDate', type: 'date', header: 'Дата выполнения', width: 100 },
				{ name: 'Lpu_Nick', type: 'string', header: 'МО выполнения', width: 150 },
				{ name: 'MedPersonal_Fio', type: 'string', header: 'Врач', width: 150 }
			],
			toolbar: true,
			title: lang['informatsiya_po_sleduyuschim_osmotrom_issledovaniyam_dvn_budet_perenesena_v_kartu_profosmotra']
		});


		this.EvnUslugaDispDopFail = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', disabled: true, hidden: true },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			height:200,
			autoLoadData: false,
			border: false,
			dataUrl: '/?c=EvnPLDispDop13&m=loadEvnUslugaDispDopTransferFailGrid',
			focusOn: {
				name: 'EPLDD13TW_SaveButton',
				type: 'button'
			},
			focusPrev: {
				name: 'EPLDD13TW_CloseButton',
				type: 'button'
			},
			id: 'EPLDD13TW_EvnUslugaDispDopFail',
			paging: false,
			region: 'south',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'EvnUslugaDispDop_id', type: 'int', header: 'ID', key: true },
				{ name: 'SurveyType_Name', type: 'string', header: 'Наименование осмотра (исследования)', id: 'autoexpand' },
				{ name: 'DopDispInfoConsent_IsEarlier', type: 'checkcolumn', header: 'Пройдено ранее', width: 100 },
				{ name: 'EvnUslugaDispDop_didDate', type: 'date', header: 'Дата выполнения', width: 100 },
				{ name: 'Lpu_Nick', type: 'string', header: 'МО выполнения', width: 150 },
				{ name: 'MedPersonal_Fio', type: 'string', header: 'Врач', width: 150 }
			],
			toolbar: true,
			title: lang['informatsiya_po_sleduyuschim_osmotrom_issledovaniyam_dvn_ne_mojet_byit_perenesena_v_kartu_profosmotra_i_budet_udalena']
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.callback();
					this.hide();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EPLDD13TW_SaveButton',
				onTabAction: function() {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				text: lang['sozdat_kartu_profosmotra']
			}, {
				text: '-'
			},
				HelpButton(this),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'EPLDD13TW_CloseButton',
					onShiftTabAction: function() {
						this.buttons[0].focus();
					}.createDelegate(this),
					text: BTN_FRMCANCEL
				}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EPLDD13TW_PersonInformationFrame',
				region: 'north'
			}),
				this.EvnUslugaDispDopSuccess,
				this.EvnUslugaDispDopFail
			]
		});
		sw.Promed.swEvnPLDispDop13TransferWindow.superclass.initComponent.apply(this, arguments);

		this.EvnUslugaDispDopSuccess.addListenersFocusOnFields();
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnPLDispDop13TransferWindow').hide();
		},
		key: [ Ext.EventObject.P ],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 800,
	modal: true,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLDispDop13TransferWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.Person_id = null;
		this.EvnPLDispDop13_id = null;

		this.action = 'edit';

		if ( arguments[0] ) {
			if ( arguments[0].callback ) {
				this.callback = arguments[0].callback;
			}

			if ( arguments[0].action ) {
				this.action = arguments[0].action;
			}

			if ( arguments[0].onHide ) {
				this.onHide = arguments[0].onHide;
			}

			if ( arguments[0].Person_id ) {
				this.Person_id = arguments[0].Person_id;
			}

			if ( arguments[0].EvnPLDispDop13_id ) {
				this.EvnPLDispDop13_id = arguments[0].EvnPLDispDop13_id;
			}
		}

		this.findById('EPLDD13TW_EvnUslugaDispDopSuccess').setReadOnly(this.action == 'view');
		this.findById('EPLDD13TW_EvnUslugaDispDopFail').setReadOnly(this.action == 'view');

		this.findById('EPLDD13TW_PersonInformationFrame').load({
			Person_id: this.Person_id,
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		this.findById('EPLDD13TW_EvnUslugaDispDopSuccess').removeAll();
		this.findById('EPLDD13TW_EvnUslugaDispDopFail').removeAll();

		this.findById('EPLDD13TW_EvnUslugaDispDopSuccess').loadData({
			globalFilters: {
				EvnPLDispDop13_id: this.EvnPLDispDop13_id
			}
		});

		this.findById('EPLDD13TW_EvnUslugaDispDopFail').loadData({
			globalFilters: {
				EvnPLDispDop13_id: this.EvnPLDispDop13_id
			}
		});
	},
	title: lang['perenos_v_kartu_profosmotra'],
	width: 800
});
