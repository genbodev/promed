/**
 * swRegistryESEditWindow - окно редактировния реестров ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.09.2014
 */

/*NO PARSE JSON*/

sw.Promed.swRegistryESEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swRegistryESEditWindow',
	layout: 'form',
	autoHeight: true,
	width: 500,
	action: 'view',

	doSave: function()
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}
		this.formStatus = 'save';

		var win = this;
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
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
			this.formStatus = 'edit';
			return false;
		}

		var params = {
			RegistryES_Num: base_form.findField('RegistryES_Num').getValue()
		};

		if (base_form.findField('RegistryES_begDate').disabled) {
			params.RegistryES_begDate = base_form.findField('RegistryES_begDate').getValue().format('d.m.Y');
		}

		win.getLoadMask("Подождите, идет формирование реестра...").show();
		base_form.submit({
			params: params,
			failure: function() {
				win.getLoadMask().hide();
				this.formStatus = 'edit';
			}.createDelegate(this),
			success: function(form, action) {
				win.getLoadMask().hide();
				this.formStatus = 'edit';
				if (action.result.success) {
					this.callback();
					this.hide();
				}
			}.createDelegate(this)
		});

		return true;
	},

	getNewNum: function(options) {
		options = options || {};
		var cb = options.callback || Ext.emptyFn;

		var base_form = this.FormPanel.getForm();
		var date = Ext.util.Format.date(base_form.findField('RegistryES_begDate').getValue(), 'd.m.Y');
		if (Ext.isEmpty(date)) {
			base_form.findField('RegistryES_Num').setValue(null);
			return false;
		}
		var params = {RegistryES_begDate: date};

		if (!Ext.isEmpty(base_form.findField('RegistryES_id').getValue())) {
			params.RegistryES_id = base_form.findField('RegistryES_id').getValue();
		}

		Ext.Ajax.request({
			params: params,
			url: '/?c=RegistryES&m=getNewRegistryESNum',
			failure: function(){},
			success: function(response){
				var responseObj = Ext.util.JSON.decode(response.responseText);
				base_form.findField('RegistryES_Num').setValue(responseObj.RegistryES_Num);
				cb();
			}
		});
		return true;
	},

	show: function(){
		sw.Promed.swRegistryESEditWindow.superclass.show.apply(this, arguments);

		this.formStatus = 'edit';
		this.enableEdit(false);

		var win = this;
		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		} else {
			this.action = 'view';
		}

		if (arguments[0] && arguments[0].RegistryES_id) {
			base_form.findField('RegistryES_id').setValue(arguments[0].RegistryES_id);
		}

		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		} else {
			this.callback = Ext.emptyFn;
		}

		this.getLoadMask(LOAD_WAIT).show();

		if ( getRegionNick() != 'astra' ) {
			base_form.findField('RegistryESType_id').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('RegistryESType_Code')) && rec.get('RegistryESType_Code').inlist([ 1, 3 ]));
			});
		}

		swLpuBuildingGlobalStore.clearFilter();
		swLpuSectionGlobalStore.clearFilter();
		base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		if (getEvnStickOptions().enable_sign_evnstick_auth_person || getRegionNick() == 'ufa' || getRegionNick() == 'krym') {
			base_form.findField('LpuBuilding_id').showContainer();
			base_form.findField('LpuSection_id').showContainer();
		} else {
			base_form.findField('LpuBuilding_id').hideContainer();
			base_form.findField('LpuSection_id').hideContainer();
		}

		switch(this.action) {
			case 'add':
				this.setTitle(lang['reestr_lvn_dobavlenie']);
				Ext.getCmp('RESEW_SaveButton').setText(lang['sohranit']);

				var date = getGlobalOptions().date;
				base_form.findField('RegistryES_insDT').setValue(date);
				base_form.findField('RegistryES_begDate').setValue(date);

				base_form.findField('RegistryESType_id').setValue(1);
				base_form.findField('RegistryESType_id').fireEvent('change', base_form.findField('RegistryESType_id'), base_form.findField('RegistryESType_id').getValue());

				this.getNewNum();
				this.enableEdit(true);
				this.getLoadMask().hide();
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					this.setTitle(lang['reestr_lvn_redaktirovanie']);
					Ext.getCmp('RESEW_SaveButton').setText(lang['pereformirovat']);
					this.enableEdit(true);
				} else {
					this.setTitle(lang['reestr_lvn_prosmotr']);
					this.enableEdit(false);
				}

				base_form.load({
					params: {RegistryES_id: base_form.findField('RegistryES_id').getValue()},
					url: '/?c=RegistryES&m=loadRegistryESForm',
					failure: function(){
						this.getLoadMask().hide();
					}.createDelegate(this),
					success: function() {
						this.getLoadMask().hide();

						base_form.findField('RegistryESType_id').fireEvent('change', base_form.findField('RegistryESType_id'), base_form.findField('RegistryESType_id').getValue());
					}.createDelegate(this)
				});
				break;
		}
	},

	initComponent: function() {
		var win = this;
		this.FormPanel = new sw.Promed.FormPanel({
			border: true,
			bodyStyle:'width:100%;background:#DFE8F6;padding:5px;',
			autoHeight: true,
			id: 'RESEW_FormPanel',
			labelWidth: 160,
			url: '/?c=RegistryES&m=saveRegistryES',
			timeout: 6000,
			items: [{
				xtype: 'hidden',
				name: 'RegistryES_id'
			},{
				xtype: 'swdatefield',
				disabled: true,
				name: 'RegistryES_insDT',
				fieldLabel: lang['data_formirovaniya'],
				width: 120
			}, {
				allowBlank: false,
				xtype: 'swcommonsprcombo',
				comboSubject: 'RegistryESType',
				lastQuery: '',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = win.FormPanel.getForm();
						if (newValue && newValue == 3) {
							// поле "Дата реестра" не активно и равно дате формирования
							base_form.findField('RegistryES_begDate').disable();
							base_form.findField('RegistryES_begDate').setValue(base_form.findField('RegistryES_insDT').getValue());
						} else {
							if (win.action != 'view') {
								base_form.findField('RegistryES_begDate').enable();
							}
							if (Ext.isEmpty(base_form.findField('RegistryES_begDate').getValue())) {
								base_form.findField('RegistryES_begDate').setValue(getGlobalOptions().date);
							}
						}
						base_form.findField('RegistryES_begDate').fireEvent('change', base_form.findField('RegistryES_begDate'), base_form.findField('RegistryES_begDate').getValue());

						if (newValue && newValue == 1) {
							base_form.findField('LpuBuilding_id').enable();
							base_form.findField('LpuSection_id').enable();
							base_form.findField('RegistryES_RegRecCount').setContainerVisible(true);
							base_form.findField('RegistryES_RegRecCount').setAllowBlank(false);

							if ( Ext.isEmpty(base_form.findField('RegistryES_RegRecCount').getValue()) ) {
								base_form.findField('RegistryES_RegRecCount').setValue((getRegionNick() == 'perm') ? 5 : 10);
							}
						} else {
							base_form.findField('RegistryES_RegRecCount').setContainerVisible(false);
							base_form.findField('RegistryES_RegRecCount').setValue(null);
							base_form.findField('RegistryES_RegRecCount').setAllowBlank(true);
							base_form.findField('LpuBuilding_id').disable();
							base_form.findField('LpuBuilding_id').clearValue();
							base_form.findField('LpuSection_id').disable();
							base_form.findField('LpuSection_id').clearValue();
						}

						win.syncShadow();
					}
				},
				hiddenName: 'RegistryESType_id',
				fieldLabel: lang['tip_reestra'],
				anchor: '100%'
			}, {
				hiddenName: 'LpuBuilding_id',
				fieldLabel: 'Подразделение',
				id: 'RESEW_LpuBuildingCombo',
				lastQuery: '',
				linkedElements: [
					'RESEW_LpuSectionCombo'
				],
				listWidth: 700,
				anchor: '100%',
				xtype: 'swlpubuildingglobalcombo'
			}, {
				hiddenName: 'LpuSection_id',
				id: 'RESEW_LpuSectionCombo',
				lastQuery: '',
				parentElementId: 'RESEW_LpuBuildingCombo',
				listWidth: 700,
				anchor: '100%',
				xtype: 'swlpusectionglobalcombo'
			}, {
				allowDecimal: false,
				allowNegative: false,
				xtype: 'numberfield',
				minValue: 1,
				maxValue: 99,
				disabled: true,
				name: 'RegistryES_Num',
				fieldLabel: lang['nomer_reestra'],
				width: 120
			}, {
				allowBlank: false,
				xtype: 'swdatefield',
				name: 'RegistryES_begDate',
				fieldLabel: lang['data_reestra'],
				width: 120,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						win.formStatus = 'save';
						win.getLoadMask(lang['poluchenie_nomera_reestra']).show();
						this.getNewNum({
							callback: function() {
								win.getLoadMask().hide();
								win.formStatus = 'edit';
							}.createDelegate(this)
						});
					}.createDelegate(this)
				}
			}, {
				allowBlank: false,
				xtype: 'numberfield',
				minValue: 1,
				maxValue: (getRegionNick() == 'perm') ? 5 : 30,
				name: 'RegistryES_RegRecCount',
				fieldLabel: langs('Кол-во записей в реестре'),
				width: 120
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//
				}
			},
			[
				{ name: 'RegistryES_id' },
				{ name: 'RegistryES_insDT' },
				{ name: 'LpuBuilding_id' },
				{ name: 'LpuSection_id' },
				{ name: 'RegistryES_Num' },
				{ name: 'RegistryES_begDate' },
				{ name: 'RegistryES_RegRecCount' },
				{ name: 'RegistryESType_id' }
			])
		});

		Ext.apply(this, {
			items: [this.FormPanel],
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					id: 'RESEW_SaveButton',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'RESEW_CancelButton',
					text: lang['zakryit']
				}]
		});

		sw.Promed.swRegistryESViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
