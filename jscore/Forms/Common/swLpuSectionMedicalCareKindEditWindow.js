/**
* swLpuSectionMedicalCareKindEditWindow - Форма добавления/редактирования вида МП на отделении
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright	Copyright (c) 2015 Swan Ltd.
* @author		Aleksandr Chebukin
* @version		09.11.2015
*/

sw.Promed.swLpuSectionMedicalCareKindEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swLpuSectionMedicalCareKindEditWindow',
	objectSrc: '/jscore/Forms/Common/swLpuSectionMedicalCareKindEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {		
		var _this = this;
		var form = this.findById('LpuSectionMedicalCareKindEditForm');
		var base_form = form.getForm();

		if ( !form.getForm().isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();

		data.LpuSectionMedicalCareKindData = {
			'LpuSectionMedicalCareKind_id': base_form.findField('LpuSectionMedicalCareKind_id').getValue(),
			'MedicalCareKind_id': base_form.findField('MedicalCareKind_id').getValue(),
			'MedicalCareKind_Code': base_form.findField('MedicalCareKind_id').getFieldValue('MedicalCareKind_Code'),
			'MedicalCareKind_Name': base_form.findField('MedicalCareKind_id').getFieldValue('MedicalCareKind_Name'),
			'LpuSectionMedicalCareKind_begDate': base_form.findField('LpuSectionMedicalCareKind_begDate').getValue(),
			'LpuSectionMedicalCareKind_endDate': base_form.findField('LpuSectionMedicalCareKind_endDate').getValue()
		};

		this.formStatus = 'edit';
		loadMask.hide();

		if ( this.callback(data) == true ) {
			this.hide();
		}

		return true;
	},	
	draggable: true,
	formStatus: 'edit',
	height: 200,
	id: 'LpuSectionMedicalCareKindEditWindow',
	initComponent: function() {
		var form = this;

		form.formPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'LpuSectionMedicalCareKindEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'LpuSectionMedicalCareKind_id' },
				{ name: 'MedicalCareKind_id' },
				{ name: 'LpuSectionMedicalCareKind_begDate' },
				{ name: 'LpuSectionMedicalCareKind_endDate' }
			]),
			region: 'center',
			url: '/?c=UslugaComplex&m=saveUslugaComplexGroup',
			items: [{
				name: 'RecordStatus_Code',
				value: '0',
				xtype: 'hidden'
			}, {
				name: 'LpuSectionMedicalCareKind_id',
				value: '',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				comboSubject: 'MedicalCareKind',
				fieldLabel: lang['vid_okazaniya_mp'],
				hiddenName: 'MedicalCareKind_id',
				lastQuery: '',
				onLoadStore: function(store) {
					if ( getRegionNick() == 'krym' ) {
						store.filterBy(function(rec) {
							return (rec.get('MedicalCareKind_Code').toString().inlist([ '12', '13', '31' ]));
						});
					}
				},
				prefix: 'nsi_',
				width: 400,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_nachala_perioda'],
				format: 'd.m.Y',
				name: 'LpuSectionMedicalCareKind_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: true,
				fieldLabel: lang['data_okonchaniya_perioda'],
				format: 'd.m.Y',
				name: 'LpuSectionMedicalCareKind_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
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
						form.formPanel.getForm().findField('MedProductCard_id').focus(true);
					}
					else {
						form.buttons[1].focus(true);
					}
				},
				text: BTN_FRMCANCEL
			}],
			items: [
				 form.formPanel
			],
			layout: 'border'
		});

		sw.Promed.swLpuSectionMedicalCareKindEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('LpuSectionMedicalCareKindEditWindow');

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
	show: function(params) {
		sw.Promed.swLpuSectionMedicalCareKindEditWindow.superclass.show.apply(this, arguments);
		
		log(arguments);

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
		this.formMode = 'local';
        this.onHide = arguments[0].onHide || Ext.emptyFn;
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		log(arguments);
        base_form.setValues(arguments[0].formParams);		

		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['vid_okazaniya_mp_dobavlenie']);
				this.enableEdit(true);
				break;
			case 'edit':
				this.setTitle(lang['vid_okazaniya_mp_redaktirovanie']);
				this.enableEdit(true);
				break;
			case 'view':
				this.setTitle(lang['vid_okazaniya_mp_prosmotr']);
				this.enableEdit(false);
				break;
		}
		
		loadMask.hide();
		
	},
	width: 600
});