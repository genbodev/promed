/**
* swPersonRegisterOutWindow - Исключение записи из регистра
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

sw.Promed.swPersonRegisterOutWindow = Ext.extend(sw.Promed.BaseForm,
{
    codeRefresh: true,
    objectName: 'swPersonRegisterOutWindow',
    objectSrc: '/jscore/Forms/Common/swPersonRegisterOutWindow.js',

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
//	height: 250,
	doSave: function()
	{
		if ( this.formStatus == 'save' ) {
			return false;
		}

		var win = this;
		this.formStatus = 'save';

		var form = this.FormPanel;
		var base_form = form.getForm();
		var params = new Object();

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

		var setDate = new Date(base_form.findField('PersonRegister_setDate').getValue()).dateFormat('Y-m-d');
		var disDate = base_form.findField('PersonRegister_disDate').getValue().dateFormat('Y-m-d');

		if (String(base_form.findField('MorbusType_SysNick').getValue()).inlist(['IPRA'])) {
			if (disDate < setDate) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('PersonRegister_disDate').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['data_isklyucheniya_iz_registra_ne_mojet_byit_menshe_datyi_vklyucheniya_v_registr'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		} else {
			if (disDate <= setDate) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('PersonRegister_disDate').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['data_isklyucheniya_iz_registra_ne_mojet_byit_menshe_ili_ravno_date_vklyucheniya_v_registr'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});

		params.MedPersonal_did = base_form.findField('MedPersonal_did').getValue();
		if(base_form.findField('MorbusType_SysNick').getValue().inlist([ 'crazy', 'narc' ]) && getRegionNick() != 'ufa'){
			params.PersonRegisterOutCause_id = base_form.findField('CrazyCauseEndSurveyType_id').getValue();
		}
		if(base_form.findField('MorbusType_SysNick').getValue()=='fmba'){
			base_form.findField('Lpu_did').setValue(base_form.findField('Lpu_id').getValue());
		}
		loadMask.show();
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
				if (action.result && action.result.success)
				{
					win.callback(action.result);
					win.hide();
				}
			}
		});


	},
	doFilterPersonRegisterOutCause: function() {
		var base_form = this.FormPanel.getForm();
		base_form.findField('PersonRegisterOutCause_id').lastQuery = '';
		base_form.findField('PersonRegisterOutCause_id').getStore().clearFilter();
		var allow_outcause_list = ['1','2','5','6'];
		switch(base_form.findField('MorbusType_SysNick').getValue()) {
			case 'crazy':
			case 'narc':
				allow_outcause_list.push('3');

				if ( getRegionNick() == 'khak' ) {
					allow_outcause_list.push('7');
				}
				break;
			case 'orphan':
				allow_outcause_list.push('8');
				break;
			case 'large family':
				allow_outcause_list.push('4');
				break;
			case 'hiv':
				if (getRegionNick().inlist(['astra','kaluga'])) {
					allow_outcause_list = ['10'];
				} else {
					allow_outcause_list = ['1','5','6','15','16'];
				}
				break;
			case 'tub':
				if (!getRegionNick().inlist(['kz'])) {
					allow_outcause_list = ['1','9','10','16'];
				}
				break;
			case 'fmba':
				allow_outcause_list = ['1','2'];
				break;
			case 'palliat':
				allow_outcause_list = ['1','15','16','19'];
				break;
			case 'IPRA':
				allow_outcause_list = ['1','2','3'];
				break;
			case 'geriatrics':
				allow_outcause_list = ['1','15','16','18'];
				break;
			case 'onko':
				allow_outcause_list = ['1','3','5','6','15','16'];
				break;
			default:
				allow_outcause_list.push('3');
				break;
		}
		base_form.findField('PersonRegisterOutCause_id').getStore().filterBy(function (rec) {
			return rec.get('PersonRegisterOutCause_Code').inlist(allow_outcause_list);
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
	show: function()
	{
		sw.Promed.swPersonRegisterOutWindow.superclass.show.apply(this, arguments);


		var current_window = this;
		if (!arguments[0] || !arguments[0].PersonRegister_id || !arguments[0].Person_id || !arguments[0].Diag_Name || !arguments[0].PersonRegister_setDate || !arguments[0].MorbusType_SysNick)
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

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		arguments[0].Lpu_did = (arguments[0].Lpu_did) ? arguments[0].Lpu_did : getGlobalOptions().lpu_id;
		arguments[0].MedPersonal_did = (arguments[0].MedPersonal_did) ? arguments[0].MedPersonal_did : getGlobalOptions().medpersonal_id;
		arguments[0].PersonRegister_disDate = (arguments[0].PersonRegister_disDate) ? arguments[0].PersonRegister_disDate : getGlobalOptions().date;
		if(arguments[0].MorbusType_SysNick&&arguments[0].MorbusType_SysNick.inlist([ 'crazy', 'narc' ]) && getRegionNick() != 'ufa'){
			base_form.findField('CrazyCauseEndSurveyType_id').setDisabled(false);
			base_form.findField('PersonRegisterOutCause_id').setDisabled(true);
			base_form.findField('CrazyCauseEndSurveyType_id').showContainer();
			base_form.findField('PersonRegisterOutCause_id').hideContainer();
		}else{
			base_form.findField('PersonRegisterOutCause_id').setDisabled(false);
			base_form.findField('CrazyCauseEndSurveyType_id').setDisabled(true);
			base_form.findField('PersonRegisterOutCause_id').showContainer();
			base_form.findField('CrazyCauseEndSurveyType_id').hideContainer();
		}
		if(arguments[0].MorbusType_SysNick&&arguments[0].MorbusType_SysNick=='fmba'){
			base_form.findField('Lpu_id').setDisabled(false);
			base_form.findField('Lpu_id').showContainer();
		} else {
			base_form.findField('Lpu_id').setDisabled(true);
			base_form.findField('Lpu_id').hideContainer();
		}
		base_form.findField('PersonRegisterOutCause_id').getStore().load({});
		base_form.setValues(arguments[0]);

		var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
		loadMask.show();

		base_form.findField('PersonRegisterOutCause_id').fireEvent('change', base_form.findField('PersonRegisterOutCause_id'), base_form.findField('PersonRegisterOutCause_id').getValue());

		base_form.findField('MedPersonal_did').getStore().load({
			params: arguments[0].MorbusType_SysNick != 'gibt' ? {withPosts: "1"} : {},
			callback: function()
			{
				base_form.findField('MedPersonal_did').setValue(base_form.findField('MedPersonal_did').getValue());
				base_form.findField('MedPersonal_did').fireEvent('change', base_form.findField('MedPersonal_did'), base_form.findField('MedPersonal_did').getValue());
			}.createDelegate(this)
		});
		this.InformationPanel.load({
			Person_id: base_form.findField('Person_id').getValue()
		});
		this.doFilterPersonRegisterOutCause();
		if ( base_form.findField('MorbusType_SysNick').getValue().toLowerCase().inlist(['large family', 'ipra']) ) {
			this.findById('prDiagGroupBox').hide();
		} else {
			this.findById('prDiagGroupBox').show();
		}

		if (!getWnd('swWorkPlaceMZSpecWindow').isVisible()) {
			if (arguments[0].MorbusType_SysNick && arguments[0].MorbusType_SysNick == 'palliat') {
				base_form.findField('MedPersonal_did').enable();
			} else {
				base_form.findField('MedPersonal_did').disable();
			}
		} else {
			if (arguments[0].MorbusType_SysNick != 'gibt') {
			base_form.findField('MedPersonal_did').enable();
			} else {
				base_form.findField('MedPersonal_did').disable();
		}
		}

		if(getWnd('swWorkPlaceMZSpecWindow').isVisible() && arguments[0].MorbusType_SysNick == 'gibt') {
			this.InformationPanel.enable();
		}

		loadMask.hide();
		this.syncShadow();
	},
	initComponent: function()
	{

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
			autoHeight: false,
			labelAlign: 'right',
			labelWidth: 200,
			url:'/?c=PersonRegister&m=out',
			items:
			[{
				region: 'north',
				layout: 'form',
				xtype: 'panel',
				items: [{
					name: 'PersonRegister_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					xtype: 'hidden'
				}, {
					name: 'PersonRegister_setDate',
					xtype: 'hidden'
				}, {
					name: 'Lpu_did',
					xtype: 'hidden'
				}, {
					name: 'MorbusType_SysNick',
					xtype: 'hidden'
				}, {
					xtype: 'fieldset',
					autoHeight: true,
					id: 'prDiagGroupBox',
					style: 'padding: 0; margin: 0',
					border: false,
					items: [{
						changeDisabled: false,
						disabled: true,
						fieldLabel: lang['diagnoz'],
						hiddenName: 'Diag_Name',
						listWidth: 620,
						width: 350,
						xtype: 'swdiagcombo'
					}]
				}, {
					allowBlank: false,
					fieldLabel: lang['data_isklyucheniya_iz_registra'],
					name: 'PersonRegister_disDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					maxValue: getGlobalOptions().date
				}, {
					fieldLabel: lang['prichina_isklyucheniya'],
					hiddenName: 'CrazyCauseEndSurveyType_id',
					xtype: 'swcommonsprcombo',
					allowBlank:false,
					sortField:'CrazyCauseEndSurveyType_Code',
					comboSubject: 'CrazyCauseEndSurveyType',
					width: 350
				},{
					fieldLabel: lang['prichina_isklyucheniya'],
					hiddenName: 'PersonRegisterOutCause_id',
					xtype: 'swcommonsprcombo',
					allowBlank:false,
					sortField:'PersonRegisterOutCause_Code',
					comboSubject: 'PersonRegisterOutCause',
					onLoadStore: function() {
						this.doFilterPersonRegisterOutCause();
					}.createDelegate(this),
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.FormPanel.getForm();
							var code = base_form.findField('PersonRegisterOutCause_id').getFieldValue('PersonRegisterOutCause_Code');
							var morbusType = base_form.findField('MorbusType_SysNick').getValue();

							if (morbusType == 'hiv' && code == 1 && getRegionNick() != 'kz') {
								base_form.findField('PersonDeathCause_id').showContainer();
								base_form.findField('PersonDeathCause_id').setAllowBlank(false);
							} else {
								base_form.findField('PersonDeathCause_id').hideContainer();
								base_form.findField('PersonDeathCause_id').setAllowBlank(true);
							}
						}.createDelegate(this)
					},
					width: 350
				}, {
					fieldLabel: lang['prichina_smerti'],
					hiddenName: 'PersonDeathCause_id',
					xtype: 'swcommonsprcombo',
					comboSubject: 'PersonDeathCause',
					width: 350
				}, {
					xtype: 'swlpucombo',
					width: 350,
					fieldLabel: lang['mo'],
					name: 'Lpu_id',
					value: getGlobalOptions().lpu_id
				}, {
					changeDisabled: false,
					disabled: !getWnd('swWorkPlaceMZSpecWindow').isVisible(),//true, //https://redmine.swan.perm.ru/issues/92555 - минздрав не привязан к врачу, поэтому нужно дать возможность выбирать
					fieldLabel: lang['vrach'],
					hiddenName: 'MedPersonal_did',
					listWidth: 750,
					width: 350,
					xtype: 'swmedpersonalcombo',
					anchor: false,
					queryDelay: 500,
					listeners: {
						'beforequery': function(evnt){// #147636 фильтрация по ФИО
							if(evnt.query && evnt.query.length < 16){
								evnt.combo.getStore().load({
									params: {querystr: evnt.query, withPosts: "1"}
								})
							}
						},
						'letsloadall': function(evnt){// #147636 загрузка всего списка
							evnt.combo.getStore().load({
								params: {All_Rec: 1, withPosts: "1"}
							})
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
		sw.Promed.swPersonRegisterOutWindow.superclass.initComponent.apply(this, arguments);
	},
	title: lang['isklyuchenie_zapisi_iz_registra']
});