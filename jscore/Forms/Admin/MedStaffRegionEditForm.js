/**
* swMedStaffRegionEditForm - окно просмотра и редактирования участков
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright © 2009 Swan Ltd.
* @author       Быдлокодер ©
* @version      18.06.2009
*/

sw.Promed.swMedStaffRegionEditForm = Ext.extend(sw.Promed.BaseForm, {
	title:lang['otdelenie'],
	id: 'MedStaffRegionEditForm',
	layout: 'border',
	maximizable: false,
	shim: false,
	width: 550,
	//autoHeight: true,
	height: 180,
	modal: true,
	buttons:
	[{
		text: BTN_FRMSAVE,
		id: 'lrOk',
		tabIndex: 1005,
		iconCls: 'save16',
		handler: function()
		{
			this.ownerCt.doSave();
		}
	},
	{
		text:'-'
	},
		HelpButton(this),
	{
		text: BTN_FRMCANCEL,
		id: 'lrCancel',
		tabIndex: 1006,
		iconCls: 'cancel16',
		handler: function()
		{
			this.ownerCt.hide();
			this.ownerCt.callback(this.ownerCt.owner, -1);
		}
	}
	],
	listeners:
	{
		hide: function()
		{
			this.callback(this.owner, -1);
		}
	},
	callback: function(owner, kid) {},
	show: function()
	{
		sw.Promed.swMedStaffRegionEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('MedStaffRegionEditForm'), { msg: "Подождите, идет загрузка..." });
		loadMask.show();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.checkStavka = true;
		this.checkLpuSection = true;
		this.checkPost = true;
		this.Lpu_id = null;
		this.LpuRegion_begDate = null;
		this.LpuRegion_endDate = null;
		this.LpuRegionType_SysNick = null;
		this.LpuSection_id = null;
		this.MedPersonal_id = null;
		this.MedStaffFact_id = null;
		this.MedStaffRegion_id = null;
		this.MedStaffRegionData = new Array();

		if (!arguments[0]){
			sw.swMsg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}

		if ( typeof arguments[0].callback == 'function' )
			this.callback = arguments[0].callback;

		if (arguments[0].owner)
			this.owner = arguments[0].owner;

		if (arguments[0].action)
			this.action = arguments[0].action;

		if (arguments[0].MedStaffRegion_id)
			this.MedStaffRegion_id = arguments[0].MedStaffRegion_id;

		if ( typeof arguments[0].MedStaffRegionData == 'object' )
			this.MedStaffRegionData = arguments[0].MedStaffRegionData;

		if (arguments[0].LpuRegionType_SysNick)
			this.LpuRegionType_SysNick = arguments[0].LpuRegionType_SysNick;

		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;

		if (arguments[0].MedPersonal_id)
			this.MedPersonal_id = arguments[0].MedPersonal_id;

		if (arguments[0].MedStaffFact_id)
			this.MedStaffFact_id = arguments[0].MedStaffFact_id;

		if (arguments[0].LpuRegion_endDate)
			this.LpuRegion_endDate = arguments[0].LpuRegion_endDate;

		if (arguments[0].LpuRegion_begDate)
			this.LpuRegion_begDate = arguments[0].LpuRegion_begDate;

		if (arguments[0].LpuSection_id)
			this.LpuSection_id = arguments[0].LpuSection_id;

		var form = this,
			params = {},
			base_form = form.findById('MedStaffRegionEditFormPanel').getForm();

		base_form.reset();

		loadMask.hide();

		if (!Ext.isEmpty(this.LpuRegion_begDate)) {
			base_form.findField('MedStaffRegion_begDate').setMinValue(this.LpuRegion_begDate);
		}

		switch (this.action){
			case 'add':
				form.setTitle(lang['vrach_na_uchastke_dobavlenie']);
				this.enableEdit(true);
				base_form.clearInvalid();
			break;
			case 'edit':
				form.setTitle(lang['vrach_na_uchastke_redaktirovanie']);
				this.enableEdit(true);
				base_form.findField('MedStaffFact_id').disable();
			break;
			case 'view':
				form.setTitle(lang['vrach_na_uchastke_prosmotr']);
				this.enableEdit(false);
			break;
		}

		//Основной врач на участке может быть только один
		base_form.findField('Lpu_id').setValue(base_form.findField('Lpu_id').getValue() || form.Lpu_id);

		base_form.setValues(arguments[0].formParams);

		base_form.findField('MedStaffFact_id').getStore().load({
			params: {
				Lpu_id: form.Lpu_id,
				LpuSection_id: getRegionNick().inlist([ 'khak', 'perm', 'pskov' ]) ? form.LpuSection_id : null,
				mode: 'combo'
			},
			callback: function(r,o,s) {
				var index = -1, MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

				params.isDoctor = (!getRegionNick().inlist([ 'khak', 'buryatiya', 'perm', 'pskov'  ]) ? 1 : 0); // только врачи
				params.Lpu_id = base_form.findField('Lpu_id').getValue();
				params.LpuSection_id = getRegionNick().inlist([ 'khak', 'perm', 'pskov' ]) ? form.LpuSection_id : null;
				params.dateFrom = form.LpuRegion_begDate;
				params.dateTo = form.LpuRegion_endDate;
				params.LpuRegionType_SysNick = form.LpuRegionType_SysNick;

				var comboMedStaffFact = base_form.findField('MedStaffFact_id');

				comboMedStaffFact.lastQuery = '';
				// setMedStaffFactGlobalStoreFilter(params, base_form.findField('MedStaffFact_id').getStore());
				comboMedStaffFact.baseFilterFn = setMedStaffFactGlobalStoreFilter(params, comboMedStaffFact.store, true);

				if ( !Ext.isEmpty(MedStaffFact_id) ) {
					index = comboMedStaffFact.getStore().findBy(function(rec) {
						return (rec.get('MedStaffFact_id') == MedStaffFact_id);
					});
				}

				if (index >= 0) {
					comboMedStaffFact.setValue(MedStaffFact_id);
				} else {
					comboMedStaffFact.clearValue();
				}

				comboMedStaffFact.focus(true, 400);
				loadMask.hide();
			}
		});
	},
    doSave: function() {
        var _this = this,
			form = this.findById('MedStaffRegionEditFormPanel'),
        	base_form = form.getForm(),
			endDate = base_form.findField('MedStaffRegion_endDate').getValue(),
			begDate = base_form.findField('MedStaffRegion_begDate').getValue(),
        	data = {};

		if ( !form.getForm().isValid() ){
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

		if (!Ext.isEmpty(endDate) && endDate < begDate) {
			sw.swMsg.alert(lang['oshibka'], lang['data_okonchaniya_ne_mojet_byit_bolshe_datyi_nachala']);
			return false;
		}

		if (Ext.util.Format.date(begDate, 'd.m.Y').split('.').reverse().join('.') < _this.LpuRegion_begDate.split('.').reverse().join('.')) {
			sw.swMsg.alert(lang['oshibka'], lang['data_nachala_rabotyi_vracha_ne_mojet_byit_ranshe_datyi_otkryitiya_uchastka']);
			return false;
		}

		var i, mainRecordExists = false;

		if ( base_form.findField('MedStaffRegion_isMain').getValue() ) {
			for ( i in this.MedStaffRegionData ) {
				if (
					this.MedStaffRegionData[i].MedStaffRegion_id != base_form.findField('MedStaffRegion_id').getValue()
					&& (
						this.MedStaffRegionData[i].MedStaffRegion_isMain === true
						|| this.MedStaffRegionData[i].MedStaffRegion_isMain === 'true'
						|| this.MedStaffRegionData[i].MedStaffRegion_isMain === 2
						|| this.MedStaffRegionData[i].MedStaffRegion_isMain === '2'
					)
				) {
					sw.swMsg.alert(lang['oshibka'], lang['na_uchastke_uje_imeetsya_osnovnoy_vrach']);
					return false;
				}
			}

			var LpuRegion_endDate = this.LpuRegion_endDate || getValidDT(getGlobalOptions().date, '');

			if ( !Ext.isEmpty(endDate) && endDate < LpuRegion_endDate ) {
				sw.swMsg.alert('Ошибка', 'Период работы основного врача на участке закрыт. Основным врачом участка может быть врач с действующим периодом работы на участке.');
				return false;
			}
		}

		for ( i in this.MedStaffRegionData ) {
			if (
				this.MedStaffRegionData[i].MedStaffRegion_id != base_form.findField('MedStaffRegion_id').getValue()
				&& this.MedStaffRegionData[i].MedPersonal_id == base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id')
				&& (Ext.isEmpty(endDate) || endDate >= this.MedStaffRegionData[i].MedStaffRegion_begDate)
				&& (Ext.isEmpty(this.MedStaffRegionData[i].MedStaffRegion_endDate) || this.MedStaffRegionData[i].MedStaffRegion_endDate >= begDate)
			) {
				sw.swMsg.alert(lang['oshibka'], lang['ukazannyiy_vrach_uje_dobavlen_na_uchastok_proverte_datyi_nachala_i_okonchaniya_rabotyi_vracha_na_uchastke']);
				return false;
			}
		}

		var comboMedStaffFact = base_form.findField('MedStaffFact_id');
		var WorkData_begDate = Date.parseDate(comboMedStaffFact.getFieldValue('WorkData_begDate'), 'd.m.Y');
		var endDateRegion = (endDate) ? endDate : new Date();
		var WorkData_endDate = (comboMedStaffFact.getFieldValue('WorkData_endDate')) ? Date.parseDate(comboMedStaffFact.getFieldValue('WorkData_endDate'), 'd.m.Y') : endDateRegion;
		var flagMedStaffFact = (comboMedStaffFact.getValue() && begDate >= WorkData_begDate && endDateRegion <= WorkData_endDate) ? false : true;

		if(flagMedStaffFact){
			sw.swMsg.alert(langs('Ошибка'), langs('Указанный период работы врача на участке превышает период работы врача.'));
			return false;
		}

		if (_this.checkStavka && base_form.findField('MedStaffFact_id').getFieldValue('MedStaffFact_Stavka') == 0){
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						_this.checkStavka = false;
						_this.doSave();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['u_vyibrannogo_vracha_nulevaya_stavka_prodoljit_sohranenie'],
				title: lang['preduprejdenie']
			});
			return false;
		}

        data.MedStaffRegionData = {
            'MedStaffRegion_id': base_form.findField('MedStaffRegion_id').getValue(),
            'MedStaffFact_id': base_form.findField('MedStaffFact_id').getValue(),
            'MedPersonal_id': base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id'),
            'MedStaffRegion_isMain': base_form.findField('MedStaffRegion_isMain').getValue(),
            'MedPersonal_FIO': base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_Fio'),
            'PostMed_Name': base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Name'),
            'MedStaffRegion_endDate': base_form.findField('MedStaffRegion_endDate').getValue(),
            'MedStaffRegion_begDate': base_form.findField('MedStaffRegion_begDate').getValue()
        };

        this.callback(data);
        this.hide();
		return true;
	},
	initComponent: function()
	{
		var _this = this;
		this.MainPanel = new sw.Promed.FormPanel(
		{
			id:'MedStaffRegionEditFormPanel',
			height:this.height,
			width: this.width,
			frame: true,
			autoWidth: false,
			autoHeight: false,
			labelWidth: 150,
			region: 'center',
			items:
			[{
				name: 'MedStaffRegion_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'msrMedStaffRegion_id'
			},{
				name: 'Lpu_id',
				tabIndex: -1,
				xtype: 'hidden',
				id: 'msrLpu_id'
			},{
				tabIndex: 1002,
				fieldLabel: lang['mesto_rabotyi'],
				hiddenName: 'MedStaffFact_id',
				allowBlank: false,
				id: 'msrMedStaffFact_id',
				listWidth: 600,
				width: 350,
				listeners: {
					'select': function(combo, record, index){
							var WorkData_begDate = Date.parseDate(combo.getFieldValue('WorkData_begDate'), 'd.m.Y');
							var LpuRegion_begDate = Date.parseDate(_this.LpuRegion_begDate, 'd.m.Y');
							if (LpuRegion_begDate < WorkData_begDate){
								_this.findById('msrMedStaffRegion_begDate').setValue(combo.getFieldValue('WorkData_begDate'));
							} else {
								_this.findById('msrMedStaffRegion_begDate').setValue(_this.LpuRegion_begDate);
							}
					}
				},
				xtype: 'swmedstafffactglobalcombo'
			},{
				xtype:'swcheckbox',
				checked: false,
				tabIndex: TABINDEX_SPEF + 35,
				name: 'MedStaffRegion_isMain',
				fieldLabel:lang['osnovnoy_vrach_uchastka'],
				id: 'msrMedStaffRegion_isMain'
			},{
				fieldLabel: lang['data_nachala'],
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'MedStaffRegion_begDate',
				id: 'msrMedStaffRegion_begDate',
				allowBlank:false
			},{
				fieldLabel : lang['data_okonchaniya'],
				xtype: 'swdatefield',
				format: 'd.m.Y',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'MedStaffRegion_endDate',
				id: 'msrMedStaffRegion_endDate',
				allowBlank:true
			}]
		});

		Ext.apply(this,
		{
			xtype: 'panel',
			border: false,
			items: [this.MainPanel]
		});
		sw.Promed.swMedStaffRegionEditForm.superclass.initComponent.apply(this, arguments);
	}
});