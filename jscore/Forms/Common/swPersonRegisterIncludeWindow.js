/**
* swPersonRegisterIncludeWindow - Запись регистра: Добавление
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @version      09.2012
*/

sw.Promed.swPersonRegisterIncludeWindow = Ext.extend(sw.Promed.BaseForm, 
{
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'form',
	modal: true,
	width: 600,
	height: 200,
	doSave: function(options)
	{
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		if ( this.formStatus == 'save' ) {
			return false;
		}
		
		var win = this;
		this.formStatus = 'save';
		
		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();
		
		var MorbusType_SysNick = base_form.findField('MorbusType_SysNick').getValue();
		if((MorbusType_SysNick=='crazy' || MorbusType_SysNick=='narc' || MorbusType_SysNick=='vzn')
			&&
			(!Ext.isEmpty(win.InformationPanel.getFieldValue('Person_deadDT'))
			&& win.InformationPanel.getFieldValue('Person_deadDT').dateFormat('Y-m-d') <= base_form.findField('PersonRegister_setDate').getValue().dateFormat('Y-m-d')
			))
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: lang['data_vklyucheniya_v_registr_ne_mojet_byit_bolshe_date_smerti_patsienta'],
					title: lang['oshibka'],
					fn: function() {
						this.formStatus = 'edit';
					}.createDelegate(this)
				});
			return false;
		}
		if(MorbusType_SysNick=='vzn'){
			var diag_combo = base_form.findField('Diag_id');
			var record = diag_combo.getStore().getById(diag_combo.getValue())
			log(win.InformationPanel,333)
			params.PersonRegisterType_id=49;
			if(
				!Ext.isEmpty(base_form.findField('Direction_setDate').getValue()) 
				&& base_form.findField('Direction_setDate').getValue() > base_form.findField('PersonRegister_setDate').getValue()
			){
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: 'Дата направления не может быть позже даты включения в регистр',
					title: lang['oshibka'],
					fn: function() {
						this.formStatus = 'edit';
					}.createDelegate(this)
				});
				return false;
			}
			var morbusTypes = record.get('MorbusType_List').split(',');
			morbusTypes.forEach(function(name){
				if(name.inlist(['hypoph_nanism','onko','mucovis','haemoph','multi_scleros','Gaucher_disease','after_transplant'])){
					MorbusType_SysNick=name;
					base_form.findField('MorbusType_SysNick').setValue(name);
					//params.PersonRegisterType_id=49;
				}
			})
		} else if (MorbusType_SysNick == 'crazy') {
			base_form.findField('PersonRegisterType_SysNick').setValue('narc');
		}
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
		
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		if (options.ignoreCheckAnotherDiag) {
			params.ignoreCheckAnotherDiag = 1;
		}
		
		params.MedPersonal_iid = base_form.findField('MedPersonal_iid').getValue();

		if ( base_form.findField('Diag_id').disabled ) {
			params.Diag_id = base_form.findField('Diag_id').getValue();
		}
		
		base_form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action) 
			{
				win.formStatus = 'edit';
				loadMask.hide();
				if (!action.result) 
				{
					return false;
				}
				else if (action.result.Alert_Msg && action.result.Alert_Code && action.result.Alert_Code == 'ProfDiag')
				{
					var buttons = {
						yes: lang['vklyuchit_v_registr'],
						cancel: lang['otmena']
					};
					sw.swMsg.show({
						buttons: buttons,
						fn: function( buttonId )
						{
							var mode;
							if ( buttonId == 'yes' )
							{
								options.ignoreCheckAnotherDiag = 1;
								win.doSave(options);
							}
							else
							{
								win.hide();
							}
						},
						msg: action.result.Alert_Msg,
						title: lang['vopros']
					});
				}
				else if (action.result.Alert_Msg) 
				{
					var buttons = {
						yes:  (parseInt(action.result.PersonRegisterOutCause_id) == 3&&!MorbusType_SysNick.inlist(['crazy','narc']))? lang['novoe'] : lang['da'],
						no: (parseInt(action.result.PersonRegisterOutCause_id) == 3&&!MorbusType_SysNick.inlist(['crazy','narc'])) ? lang['predyiduschee'] : lang['net']
					};
					if (parseInt(action.result.PersonRegisterOutCause_id) == 3&&!MorbusType_SysNick.inlist(['crazy','narc'])) {
						buttons.cancel = lang['otmena'];
					}
					sw.swMsg.show(
					{
						buttons: buttons,
						fn: function( buttonId ) 
						{
							var mode;
							if ( buttonId == 'yes' && action.result.Yes_Mode) 
							{
								mode = action.result.Yes_Mode
							} 
							else if ( buttonId == 'no' && action.result.No_Mode) 
							{
								mode = action.result.No_Mode
							}
							if(mode)
							{
								if ( mode.inlist(['homecoming','relapse']) ) 
								{
									var params = {
										PersonRegister_id: action.result.PersonRegister_id
										,PersonRegister_setDate:base_form.findField('PersonRegister_setDate').getValue().dateFormat('d.m.Y')
										,Diag_id: base_form.findField('Diag_id').getValue()
										,ownerWindow: win
										,callback: function(data) {
											base_form.findField('PersonRegister_id').setValue(action.result.PersonRegister_id);
											var data = base_form.getValues();
											win.callback(data);
											win.hide();
										}
									};
										if(base_form.findField('MorbusType_SysNick').getValue()=='vzn'){
											params.PersonRegisterType_SysNick = 'nolos'
										}
									// Вернуть пациента в регистр, удалить дату закрытия заболевания
									sw.Promed.personRegister.back(params);
								}
								else
								{
									base_form.findField('Mode').setValue(mode);
									win.doSave();
								}
							}
							else
							{
								win.hide();
							}
						},
						msg: action.result.Alert_Msg,
						title: lang['vopros']
					});
				}
				else if (action.result.success) 
				{
					base_form.findField('PersonRegister_id').setValue(action.result.PersonRegister_id);
					var data = base_form.getValues();
					win.callback(data);
					win.hide();
				}
			}
		});
		
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		var base_form = this.findById('FormPanel').getForm();
		
		base_form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	isMzSpecialist: function()
	{
		return (haveArmType('minzdravdlo') || haveArmType('spec_mz') || haveArmType('mzchieffreelancer'));
	},
	loadMedPersonalCombo: function()
	{
		var base_form = this.FormPanel.getForm();
		var medpersonal_combo = base_form.findField('MedPersonal_iid');
		var medpersonal_id = medpersonal_combo.getValue();
		var lpu_id = Lpu_id = base_form.findField('Lpu_iid').getValue();
		var mpParams = {};

		if(!lpu_id && (base_form.findField('MorbusType_SysNick').getValue() == 'vzn' ||  base_form.findField('MorbusType_SysNick').getValue() == 'prof' || this.registryType == 'fmba')){
			return false;
		}
		if(this.MorbusType_SysNick == 'vzn' && this.isMzSpecialist()){
			mpParams.Lpu_id = lpu_id;
			var dirDate = base_form.findField('Direction_setDate').getValue();
			if(Ext.isEmpty(dirDate)){
				dirDate = base_form.findField('PersonRegister_setDate').getValue();
			}
			mpParams.onDate = Ext.util.Format.date(dirDate, 'Y-m-d');
		}
		
		medpersonal_combo.getStore().load({
			params: mpParams,
			callback: function()
			{
				if(medpersonal_id > 0 && medpersonal_combo.getStore().getById(medpersonal_id)){
					medpersonal_combo.setValue(medpersonal_id);
				} else {
					medpersonal_combo.clearValue();
				}
				medpersonal_combo.fireEvent('change', medpersonal_combo, medpersonal_id);
			}.createDelegate(this)
		});
	},
	show: function() 
	{
		sw.Promed.swPersonRegisterIncludeWindow.superclass.show.apply(this, arguments);
		var me = this;
		if (!arguments[0] || !arguments[0].MorbusType_SysNick || !arguments[0].Person_id)
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}
		this.focus();
		this.findById('FormPanel').getForm().reset();
		
		this.center();
		this.setTitle(langs('Запись регистра: Добавление'));

		var base_form = me.FormPanel.getForm();
        var diag_combo = base_form.findField('Diag_id');

		diag_combo.lastQuery = lang['stroka_kotoraya_nikogda_ne_smojet_okazatsya_v_lastquery'];
        diag_combo.registryType = arguments[0].registryType || null;
        diag_combo.MorbusType_SysNick = null;

		base_form.reset();

		this.action = null;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.MorbusType_SysNick = null;

		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.registryType = arguments[0].registryType || null;
		arguments[0].Lpu_iid = (arguments[0].Lpu_iid) ? arguments[0].Lpu_iid : getGlobalOptions().lpu_id;
		arguments[0].MedPersonal_iid = (arguments[0].MedPersonal_iid) ? arguments[0].MedPersonal_iid : getGlobalOptions().medpersonal_id;
		arguments[0].PersonRegister_setDate = (arguments[0].PersonRegister_setDate) ? arguments[0].PersonRegister_setDate : getGlobalOptions().date;
		
		base_form.setValues(arguments[0]);

		this.MorbusType_SysNick = base_form.findField('MorbusType_SysNick').getValue();

		if ( base_form.findField('MorbusType_SysNick').getValue() != 'fmba' )
			this.onChangeMorbusProfDiag();
		
		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		this.loadMedPersonalCombo();

        me.InformationPanel.load({
			Person_id: base_form.findField('Person_id').getValue(),
			callback:function(){
				base_form.findField('PersonRegister_setDate').setMinValue(me.InformationPanel.getFieldValue('Person_Birthday')||undefined)
			}
		});

		diag_combo.MorbusType_SysNick = base_form.findField('MorbusType_SysNick').getValue();
		switch (base_form.findField('MorbusType_SysNick').getValue()) {
			case 'vzn':
				diag_combo.filterDate = base_form.findField('PersonRegister_setDate').getValue().dateFormat('d.m.Y');
				/*diag_combo.additQueryFilter = "(MorbusType_List like '%onko%' or MorbusType_List like '%mucovis%' or MorbusType_List like '%haemoph%' "+
					"or MorbusType_List like '%multi_scleros%' or MorbusType_List like '%hypoph_nanism%' or MorbusType_List like '%other_sfingolipidozy%' or MorbusType_List like '%after_transplant%')";
                diag_combo.additClauseFilter = '(record["MorbusType_List"].search(new RegExp("hypoph_nanism|onko|mucovis|haemoph|multi_scleros|other_sfingolipidozy|after_transplant", "i"))>=0)';*/
                diag_combo.additQueryFilter = "(isVZN = 1 and Diag_Code not like 'E75.5')";
                diag_combo.additClauseFilter = '(record["MorbusType_List"].search(new RegExp("vzn", "i"))>=0)';
			break;
            /*case 'onko':
                diag_combo.additQueryFilter = "(Diag_Code like 'C%' OR Diag_Code like 'D0%')";
                diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^C|D0", "i"))>=0)';
                break;*/
            /*case 'crazy':
				diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^F0|F[2-9]", "i"))>=0)';
				diag_combo.PersonRegisterType_SysNick = 'crazyRegistry';
                break;
			case 'narc':
				diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^F1", "i"))>=0)';
				diag_combo.PersonRegisterType_SysNick = 'narkoRegistry';
				break;*/
            /*case 'hepa':
                diag_combo.additQueryFilter = "(Diag_Code like 'B15%' OR Diag_Code like 'B16%' OR Diag_Code like 'B17%' OR Diag_Code like 'B18%' OR Diag_Code like 'B19%')";
                diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^B1[5-9]", "i"))>=0)';
                break;
            case 'acs':
                diag_combo.additQueryFilter = "(Diag_Code like 'I20.0')";
                diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^I2", "i"))>=0)';
                break;
            case 'ibs':
                diag_combo.additQueryFilter = "(Diag_Code like '%I20.' or Diag_Code like '%I21.' or Diag_Code like '%I22.' or Diag_Code like '%I23.' or Diag_Code like '%I24.' or Diag_Code like '%I25.')";
                diag_combo.additClauseFilter = '(record["Diag_Code"].search(new RegExp("^I2[0-5]", "i"))>=0)';
                break;*/
            case 'fmba':
            	diag_combo.registryType = 'Fmba';
                diag_combo.MorbusType_SysNick = '';
                diag_combo.additQueryFilter = '';
                diag_combo.additClauseFilter = '';
                break;
           case 'palliat':
            	diag_combo.registryType = 'palliat';
                diag_combo.MorbusType_SysNick = '';
                diag_combo.additQueryFilter = '';
                diag_combo.additClauseFilter = '';
                break;
            case 'geriatrics':
            	diag_combo.registryType = 'geriatrics';
                diag_combo.MorbusType_SysNick = '';
                diag_combo.additQueryFilter = '';
                diag_combo.additClauseFilter = '';
                break;
            case 'gibt':
            	diag_combo.registryType = 'gibt';
                diag_combo.MorbusType_SysNick = 'gibt';
                diag_combo.additQueryFilter = '';
                diag_combo.additClauseFilter = '';
				this.setTitle(langs('Нуждаемость в ГИБТ: Добавление'));
                break;
            default:
                diag_combo.MorbusType_SysNick = base_form.findField('MorbusType_SysNick').getValue();
                diag_combo.additQueryFilter = '';
                diag_combo.additClauseFilter = '';
                break;
        }
		
		var diag_id = arguments[0].Diag_id;
		if ( diag_id != null && diag_id.toString().length > 0 ) {
			base_form.findField('Diag_id').getStore().load({
				callback: function() {
					base_form.findField('Diag_id').getStore().each(function(record) {
						if ( record.get('Diag_id') == diag_id ) {
							base_form.findField('Diag_id').setValue(diag_id);
							base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
						}
					});
				},
				params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
			});
		} else {
			base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), null, -1);
		}
		
		if ( base_form.findField('MorbusType_SysNick').getValue() == 'large family' ) {
			base_form.findField('Diag_id').hideContainer();
		} else {
			base_form.findField('Diag_id').showContainer();
		}

		base_form.findField('Diag_id').MorbusProfDiag_id = null;
		if(base_form.findField('MorbusType_SysNick').getValue() == 'vzn'){
			base_form.findField('Lpu_iid').showContainer();
			base_form.findField('Lpu_iid').setAllowBlank(false);
			base_form.findField('MorbusProfDiag_id').hideContainer();
			base_form.findField('MorbusProfDiag_id').setAllowBlank(true);
		}
		else if ( base_form.findField('MorbusType_SysNick').getValue() == 'prof' ) {
			base_form.findField('MorbusProfDiag_id').showContainer();
			base_form.findField('Lpu_iid').showContainer();
			base_form.findField('MorbusProfDiag_id').setAllowBlank(false);
			base_form.findField('Lpu_iid').setAllowBlank(false);
		} else if ( arguments[0].registryType == 'fmba' ) {
			base_form.findField('MorbusProfDiag_id').hideContainer();
			base_form.findField('Lpu_iid').showContainer();
			base_form.findField('MorbusProfDiag_id').setAllowBlank(true);
			base_form.findField('Lpu_iid').setAllowBlank(false);
			base_form.findField('Diag_id').setBaseFilter(function(rec) {
				return rec.get('Diag_Code').indexOf('Z57') >= 0;
			});
			base_form.findField('Diag_id').getStore().load({
				params: {where: "where Diag_Code like 'Z57%' "}
			});
		} else {
			base_form.findField('MorbusProfDiag_id').hideContainer();
			base_form.findField('Lpu_iid').hideContainer();
			base_form.findField('MorbusProfDiag_id').setAllowBlank(true);
			base_form.findField('Lpu_iid').setAllowBlank(true);
		}

		if ( base_form.findField('MorbusType_SysNick').getValue() == 'geriatrics' ) {
			base_form.findField('Diag_id').disable();

			base_form.findField('Diag_id').getStore().load({
				callback: function() {
					if ( base_form.findField('Diag_id').getStore().getCount() > 0 ) {
						base_form.findField('Diag_id').setValue(base_form.findField('Diag_id').getStore().getAt(0).get('Diag_id'));
					}

					loadMask.hide();
				},
				params: {
					where: "where DiagLevel_id = 4 and Diag_Code like 'R54%'"
				}
			});
		}
		else {
			base_form.findField('Diag_id').enable();
			loadMask.hide();
		}

		if ( base_form.findField('MorbusType_SysNick').getValue() == 'vzn' && this.isMzSpecialist() ) {
			base_form.findField('Direction_Num').showContainer();
			base_form.findField('Direction_setDate').showContainer();
			base_form.findField('Direction_Comment').showContainer();
			base_form.findField('MedPersonal_iid').enable();
		} else {
			base_form.findField('Direction_Num').hideContainer();
			base_form.findField('Direction_setDate').hideContainer();
			base_form.findField('Direction_Comment').hideContainer();
			if (arguments[0].registryType == 'palliat') {
				base_form.findField('MedPersonal_iid').enable();
			} else {
				base_form.findField('MedPersonal_iid').disable();
			}
		}
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible() && arguments[0].registryType != 'gibt')
		{
			base_form.findField('MedPersonal_iid').enable();
		}
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible() && arguments[0].registryType == 'gibt') {
			this.InformationPanel.enable();
		}
		this.syncShadow();
	},
	onChangeMorbusProfDiag: function()
	{
		var base_form = this.FormPanel.getForm();
		if ( base_form.findField('MorbusType_SysNick').getValue() == 'prof' ) {
			base_form.findField('Diag_id').clearValue();
			base_form.findField('Diag_id').MorbusProfDiag_id = base_form.findField('MorbusProfDiag_id').getValue();

			var diag_ids = [];
			if (!Ext.isEmpty(base_form.findField('MorbusProfDiag_id').getValue()) && !Ext.isEmpty(base_form.findField('MorbusProfDiag_id').getFieldValue('Diag_ids'))) {
				diag_ids = base_form.findField('MorbusProfDiag_id').getFieldValue('Diag_ids').split(', ');
			}

			if (diag_ids && diag_ids.length > 0) {
				base_form.findField('Diag_id').setBaseFilter(function(rec) {
					return rec.get('Diag_id').inlist(diag_ids);
				});
				base_form.findField('Diag_id').getStore().load({
					params: {where: "where Diag_id = " + diag_ids[0]},
					callback: function() {
						base_form.findField('Diag_id').setValue(diag_ids[0]);
						base_form.findField('Diag_id').getStore().each(function (rec) {
							if (rec.get('Diag_id') == diag_ids[0]) {
								base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), rec, 0);
							}
						});
					}
				});
			} else {
				base_form.findField('Diag_id').clearBaseFilter();
				base_form.findField('Diag_id').clearValue();
			}
		}
	},
	initComponent: function() 
	{
		var win = this;
		
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
		this.FormPanel = new Ext.form.FormPanel(
		{	
			frame: true,
			layout: 'form',
			region: 'center',
			id: 'FormPanel',
			bodyStyle: 'padding: 5px',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 200,
			url:'/?c=PersonRegister&m=save',
			items: 
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'PersonRegister_id',
					xtype: 'hidden',
					value: 0
				}, {
					name: 'Mode',
					xtype: 'hidden',
					value: null
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					xtype: 'hidden'
				}, {
					name: 'Person_Firname',
					xtype: 'hidden'
				}, {
					name: 'Person_Secname',
					xtype: 'hidden'
				}, {
					name: 'Person_Surname',
					xtype: 'hidden'
				}, {
					name: 'Person_Birthday',
					xtype: 'hidden'
				}, {
					name: 'MorbusType_SysNick',
					xtype: 'hidden'
				}, {
					name: 'PersonRegisterType_SysNick',
					xtype: 'hidden'
				}, {
					name: 'Morbus_id',
					xtype: 'hidden'
				}, {
					fieldLabel: 'Номер направления',
					name: 'Direction_Num',
					width: 350,
					xtype: 'textfield'
				}, {
					fieldLabel: lang['data_napravleniya'],
					name: 'Direction_setDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					maxValue: getGlobalOptions().date,
					listeners: {
						'change': function(combo,newVal){
							if(!Ext.isEmpty(newVal) && this.MorbusType_SysNick == 'vzn' && this.isMzSpecialist()){
								this.loadMedPersonalCombo();
							}
						}.createDelegate(this)
					}
				}, {
                    fieldLabel: 'Обоснование',
                    name: 'Direction_Comment',
					height: 65,
					width: 350,
                    maxLength: 1024,
                    maxLengthText: lang['maksimalnaya_dlina_etogo_polya_1024_simvolov'],
                    xtype: 'textarea'
				}, {
					allowBlank: false,
					fieldLabel: lang['data_vklyucheniya_v_registr'],
					name: 'PersonRegister_setDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					maxValue: getGlobalOptions().date,
					listeners: {
						'change': function(combo,newVal){
							if(!Ext.isEmpty(newVal) && this.MorbusType_SysNick == 'vzn' && this.isMzSpecialist()){
								this.loadMedPersonalCombo();
							}
						}.createDelegate(this)
					}
				}, {
					fieldLabel: lang['zabolevanie'],
					hiddenName: 'MorbusProfDiag_id',
					moreFields: [
						{ name: 'Diag_ids', mapping: 'Diag_ids' }
					],
					listeners: {
						'change': function() {
							win.onChangeMorbusProfDiag();
						}
					},
					editable: true,
					width: 350,
					comboSubject: 'MorbusProfDiag',
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					minChars: 0,
					triggerAction: 'all',
					fieldLabel: lang['diagnoz'],
					hiddenName: 'Diag_id',
					listWidth: 620,
					listeners: {
						'select': function(combo, record, index) {
							combo.fireEvent('change', combo, combo.getValue())
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();
							var diag_code = combo.getFieldValue('Diag_Code');

							if (diag_code >= 'B20' && diag_code <= 'B24' && getRegionNick() != 'kz') {
								base_form.findField('Morbus_confirmDate').showContainer();
								base_form.findField('Morbus_EpidemCode').showContainer();
							} else {
								base_form.findField('Morbus_confirmDate').hideContainer();
								base_form.findField('Morbus_EpidemCode').hideContainer();
							}

							this.syncShadow();
						}.createDelegate(this)
					},
					valueField: 'Diag_id',
					checkAccessRights: true,
					registryType: '',
					width: 350,
					xtype: 'swdiagcombo'
				}, {
					fieldLabel: 'Дата подтверждения диагноза',
					name: 'Morbus_confirmDate',
					xtype: 'swdatefield',
					width: 100
				}, {
					fieldLabel: 'Эпидемиологический код',
					name: 'Morbus_EpidemCode',
					xtype: 'textfield',
					width: 350
				}, {
					fieldLabel: lang['mo'],
					hiddenName: 'Lpu_iid',
					width: 350,
					xtype: 'swlpucombo',
					listeners: {
						'change': function(combo,newVal){
							if(!Ext.isEmpty(newVal) && this.MorbusType_SysNick == 'vzn' && this.isMzSpecialist()){
								this.loadMedPersonalCombo();
							}
						}.createDelegate(this)
					}
				}, {
					changeDisabled: false,
					//disabled: true,
					disabled: !getWnd('swWorkPlaceMZSpecWindow').isVisible(), //https://redmine.swan.perm.ru/issues/92555 - минздрав не привязан к врачу, поэтому нужно дать возможность выбирать
					fieldLabel: lang['vrach'],
					hiddenName: 'MedPersonal_iid',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo',
					anchor: false,
					queryDelay: 500,
					listeners: {
						'beforequery': function(evnt){// #143613 коммент 57: Надо реализовывать загрузку не всего списка, а с фильтрацией по ФИО
							if(evnt.query && evnt.query.length < 16){
								evnt.combo.getStore().load({
									params: {querystr: evnt.query}
								})
							}
						}
					}
				}]
			}]
		});
		Ext.apply(this, 
		{	
			buttons: 
			[{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items: [this.InformationPanel, this.FormPanel]
		});
		sw.Promed.swPersonRegisterIncludeWindow.superclass.initComponent.apply(this, arguments);
	},
	title: lang['zapis_registra_dobavlenie']
});