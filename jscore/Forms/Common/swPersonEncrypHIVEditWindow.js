/**
 * swPersonEncrypHIVEditWindow - окно редактирования шифрование ВИЧ-инфицированных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			04.06.2015
 */
/*NO PARSE JSON*/

sw.Promed.swPersonEncrypHIVEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonEncrypHIVEditWindow',
	width: 560,
	autoHeight: true,
	modal: true,

	doSave: function() {
		var wnd = this;

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {};
		if (base_form.findField('EncrypHIVTerr_id').disabled) {
			params.EncrypHIVTerr_id = base_form.findField('EncrypHIVTerr_id').getValue();
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		base_form.submit({
			params: params,
			failure: function(result_form, action)
			{
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action)
			{
				loadMask.hide();
				if (action.result){
					if (!Ext.isEmpty(action.result.PersonEncrypHIV_id)){
						base_form.findField('PersonEncrypHIV_id').setValue(action.result.PersonEncrypHIV_id);
						this.callback(action.result.PersonEncrypHIV_id);
						this.hide();
					}
				}
			}.createDelegate(this)
		});
	},

	doPersonEncrypHIVTransfer: function(person_type) {
		var wnd = this;
		var base_form = this.FormPanel.getForm();
		var encryp_id = base_form.findField('PersonEncrypHIV_id').getValue();
		if (Ext.isEmpty(encryp_id)) {
			return false;
		}

		var doTransfer = function(PersonEncrypHIV_id, Person_nid, PersonType) {
			var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите, выполняется смена пациента..." });
			loadMask.show();

			Ext.Ajax.request({
				params: {
					PersonEncrypHIV_id: PersonEncrypHIV_id,
					Person_nid:  Person_nid
				},
				url: '/?c=PersonEncrypHIV&m=changePersonInPersonEncrypHIV',
				success: function(response) {
					loadMask.hide();
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success) {
						wnd.setPersonType(PersonType);
						base_form.setValues(response_obj.encryp_data);
					}
				},
				failure: function() {
					loadMask.hide();
				}
			});
		};

		switch(person_type) {
			case 'anonym':
				doTransfer(encryp_id, null);
				break;

			case 'real':
				getWnd('swPersonSearchWindow').show({
					onClose: Ext.emptyFn,
					onSelect: function(person_data) {
						getWnd('swPersonSearchWindow').hide();
						doTransfer(encryp_id, person_data.Person_id, person_type);
					}.createDelegate(this),
					searchMode: 'all'
				});
				break;
		}

		return true;
	},

	setPersonType: function(type) {
		var base_form = this.FormPanel.getForm();
		var terr_combo = base_form.findField('EncrypHIVTerr_id');

		if (this.action != 'view') {
			terr_combo.enable();
		}
		terr_combo.clearFilter();
		terr_combo.lastQuery = '';

		Ext.getCmp('PEHEW_ViewPersonButton').hide();
		Ext.getCmp('PEHEW_ChangeToAnonymPerson').disable();

		switch(type) {
			case 'anonym':
				terr_combo.getStore().filterBy(function(rec){
					return (rec.get('EncrypHIVTerr_Code') == 20);
				});
				terr_combo.disable();
				break;
			case 'real':
				terr_combo.getStore().filterBy(function(rec){
					return (rec.get('EncrypHIVTerr_Code') != 20);
				});
				if (this.action != 'view') {
					terr_combo.enable();
					Ext.getCmp('PEHEW_ViewPersonButton').show();
					Ext.getCmp('PEHEW_ChangeToAnonymPerson').enable();
				}
				break;
		}
	},

	getPersonEncrypHIVEncryp: function(callback) {
		var base_form = this.FormPanel.getForm();
		var encryp_field = base_form.findField('PersonEncrypHIV_Encryp');
		var terr_combo = base_form.findField('EncrypHIVTerr_id');

		Ext.Ajax.request({
			params: {
				Person_id: base_form.findField('Person_id').getValue(),
				PersonEncrypHIV_setDT: base_form.findField('PersonEncrypHIV_setDT').getValue()
			},
			url: '/?c=PersonEncrypHIV&m=getPersonEncrypHIVEncryp',
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success && response_obj.PersonEncrypHIV_Encryp) {
					encryp_field.setValue(response_obj.PersonEncrypHIV_Encryp);
					terr_combo.setValue(response_obj.EncrypHIVTerr_id);
					if (typeof callback == 'function') {
						callback();
					}
				}
			}.createDelegate(this)
		});
	},

	openPersonViewWindow: function() {
		var base_form = this.FormPanel.getForm();
		var person_id = base_form.findField('Person_id').getValue();

		if (Ext.isEmpty(person_id)) {
			return false;
		}

		getWnd('swPersonEditWindow').show({
			onClose: Ext.emptyFn,
			action: 'view',
			Person_id: person_id
		});
		return true;
	},

	show: function() {
		sw.Promed.swPersonEncrypHIVEditWindow.superclass.show.apply(this, arguments);

		this.action = 'view';
		this.callback = Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		Ext.getCmp('PEHEW_ViewPersonButton').hide();
		Ext.getCmp('PEHEW_ChangePersonMenu').hide();
		this.setPersonType();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0] && arguments[0].Person_id) {
			base_form.findField('Person_id').setValue(arguments[0].Person_id);
		}

		if (arguments[0] && arguments[0].PersonEncrypHIV_id) {
			base_form.findField('PersonEncrypHIV_id').setValue(arguments[0].PersonEncrypHIV_id);
		}

		base_form.items.each(function(f){f.validate()});

		var terr_combo = base_form.findField('EncrypHIVTerr_id');
		Ext.getCmp('PEHEW_ChangePersonMenu').hide();

		if (getRegionNick() == 'kaluga') {
			terr_combo.setAllowBlank(true);
			terr_combo.hideContainer();
			this.syncShadow();
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		switch(this.action) {
			case 'add':
				this.setTitle(lang['shifrovanie_vich-infitsirovannyih_dobavlenie']);
				this.enableEdit(true);
				Ext.getCmp('PEHEW_ViewPersonButton').hide();
				Ext.getCmp('PEHEW_ChangePersonMenu').hide();

				getCurrentDateTime({
					callback: function(result) {
						if (result.date) {
							base_form.findField('PersonEncrypHIV_setDT').setValue(result.date);

							this.getPersonEncrypHIVEncryp(function(){
								this.setPersonType(terr_combo.getFieldValue('EncrypHIVTerr_Code')==20?'anonym':'real');
							}.createDelegate(this));
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								fn: function(){
									this.hide();
								}.createDelegate(this),
								icon: Ext.Msg.ERROR,
								msg: lang['ne_udalos_poluchit_tekuschuyuyu_datu_s_servera'],
								title: lang['oshibka']
							});
						}
					}.createDelegate(this)
				});

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['shifrovanie_vich-infitsirovannyih_redaktirvanie']);
					this.enableEdit(true);
					Ext.getCmp('PEHEW_ViewPersonButton').hide();
					Ext.getCmp('PEHEW_ChangePersonMenu').show();
				} else {
					this.setTitle(lang['shifrovanie_vich-infitsirovannyih_prosmotr']);
					this.enableEdit(false);
					Ext.getCmp('PEHEW_ViewPersonButton').hide();
					Ext.getCmp('PEHEW_ChangePersonMenu').hide();
				}

				base_form.load({
					params: {
						PersonEncrypHIV_id: base_form.findField('PersonEncrypHIV_id').getValue()
					},
					url: '/?c=PersonEncrypHIV&m=loadPersonEncrypHIVForm',
					success: function() {
						this.setPersonType(terr_combo.getFieldValue('EncrypHIVTerr_Code')==20?'anonym':'real');

						loadMask.hide();
					}.createDelegate(this),
					failure: function() {
						loadMask.hide();
					}
				});

				break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'PEHEW_FilterPanel',
			region: 'north',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 160,
			url: '/?c=PersonEncrypHIV&m=savePersonEncrypHIV',
			items: [{
				xtype: 'hidden',
				name: 'PersonEncrypHIV_id'
			}, {
				xtype: 'hidden',
				name: 'Person_id'
			}, {
				xtype: 'hidden',
				name: 'PersonEncrypHIV_setDT'
			}, {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'EncrypHIVTerr',
				hiddenName: 'EncrypHIVTerr_id',
				typeCode: 'int',
				fieldLabel: lang['territoriya_projivaniya'],
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						var encryp_field = base_form.findField('PersonEncrypHIV_Encryp');
						var encryp = encryp_field.getValue();

						if (!Ext.isEmpty(newValue) && !Ext.isEmpty(combo.getFieldValue('EncrypHIVTerr_Code'))) {
							var code = combo.getFieldValue('EncrypHIVTerr_Code').toString();
							encryp_field.setValue((code.length==2?code:'0'+code)+encryp.substring(2));
						}
					}.createDelegate(this),
					'select': function(combo, record, index) {
						combo.fireEvent('change', combo, record.get('EncrypHIVTerr_id'));
					}.createDelegate(this)
				},
				width: 240
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'PersonEncrypHIV_Encryp',
				fieldLabel: lang['shifr'],
				validationEvent: 'blur',
				stripCharsRe: new RegExp('__-__-_-____'),
				plugins: [ new Ext.ux.InputTextMask('99-99-X[А-Я-]X-9999', true) ],
				width: 240
			}],
			reader: new Ext.data.JsonReader({
				success: function(){
					//
				}
			}, [
				{name: 'PersonEncrypHIV_id'},
				{name: 'Person_id'},
				{name: 'PersonEncrypHIV_setDT'},
				{name: 'EncrypHIVTerr_id'},
				{name: 'PersonEncrypHIV_Encryp'}
			])
		});

		Ext.apply(this,
		{
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'PEHEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					handler: function () {
						this.openPersonViewWindow();
					}.createDelegate(this),
					id: 'PEHEW_ViewPersonButton',
					text: lang['prosmotret_patsienta']
				},
				{
					menu: new Ext.menu.Menu({
						items: [{
							text: lang['privyazat_k_anonimu'],
							id: 'PEHEW_ChangeToAnonymPerson',
							handler: function() {
								this.doPersonEncrypHIVTransfer('anonym');
							}.createDelegate(this)
						}, {
							text: lang['privyazat_k_realnomu_patsientu'],
							id: 'PEHEW_ChangeToRealPerson',
							handler: function() {
								this.doPersonEncrypHIVTransfer('real');
							}.createDelegate(this)
						}]
					}),
					id: 'PEHEW_ChangePersonMenu',
					text: lang['smenit_patsienta']
				},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function()
					{
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE
				}
			],
			items: [this.FormPanel]
		});

		sw.Promed.swPersonEncrypHIVEditWindow.superclass.initComponent.apply(this, arguments);
	}
});