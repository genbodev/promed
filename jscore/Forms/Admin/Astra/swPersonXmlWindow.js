/**
* swPersonXmlWindow - îêíî âûãðóçêè ïðèêðåïëåííîãî íàñåëåíèÿ â XML.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author
* @version      22.06.2011
* @comment      Ïðåôèêñ äëÿ id êîìïîíåíòîâ rxw (PersonXmlWindow)
*
*
* @input data: arm - èç êàêîãî ÀÐÌà âåä¸òñÿ âûãðóçêà
*/

sw.Promed.swPersonXmlWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'PersonXmlWindow',
	title: lang['vyigruzka_spiska_prikreplennogo_naseleniya'],
	width: 450,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() {
		var win = this;

		win.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryXmlTextPanel',
			html: lang['vyigruzka_spiska_prikreplennogo_naseleniya_v_formate_xml']
		});

		win.radioButtonGroup = new sw.Promed.Panel({
			items: [{
				xtype: 'radio',
				hideLabel: true,
				boxLabel: lang['skachat_fayl_s_servera'],
				inputValue: 0,
				id: 'rxw_radio_useexist',
				name: 'downloadtype',
				checked: true
			}, {
				xtype: 'radio',
				hideLabel: true,
				boxLabel: lang['sformirovat_novyiy_fayl'],
				inputValue: 1,
				id: 'rxw_radio_usenew',
				name: 'downloadtype'
			}]
		});
		
		win.exportTypeGroup = new sw.Promed.Panel({
			items: [{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: lang['period'],
						labelSeparator: '',
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'Date_upload'
					}]
				}]
			}]
		});
		
		win.Panel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'RegistryXmlPanel',
			labelAlign: 'right',
			//labelWidth: 75,
			items: [{
				allowBlank: false,
				fieldLabel: lang['mo'],
				hiddenName: 'Lpu_id',
				width: 300,
				xtype: 'swlpucombo'
			}, {
				fieldLabel: lang['smo'],
				hiddenName: 'OrgSMO_id',
				onTrigger2Click: function() {
					if ( this.disabled ) {
						return false;
					}

					var combo = this;

					getWnd('swOrgSearchWindow').show({
						KLRgn_id: getGlobalOptions().region.number,
						object: 'smo',
						onClose: function() {
							combo.focus(true, 200);
						},
						onSelect: function(orgData) {
							if ( orgData.Org_id > 0 )
							{
								combo.setValue(orgData.Org_id);
								combo.focus(true, 250);
								combo.fireEvent('change', combo);
							}
							getWnd('swOrgSearchWindow').hide();
						}
					});
				},
				width: 300,
				xtype: 'sworgsmocombo'
			},
			{
				allowBlank: false,
				fieldLabel: 'Номер пакета',
				maskRe: /[0-9]/,
				name: 'PackageNum',
				width: 100,
				xtype: 'textfield'
			},
			win.exportTypeGroup,
			win.radioButtonGroup,
			win.TextPanel
			]
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				id: 'rxfOk',
				handler: function() 
				{
					win.createXML();
				},
				iconCls: 'refresh16',
				text: lang['sformirovat']
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
				onTabElement: 'rxfOk',
				text: BTN_FRMCANCEL
			}],
			items: [
				win.Panel
			]
		});

		sw.Promed.swPersonXmlWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function() {
			if ( this.refresh ) {
				this.onHide();
			}
		}
	},
	filterOrgSMOCombo: function() {
		var OrgSMOCombo = this.Panel.getForm().findField('OrgSMO_id');
		
		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == getGlobalOptions().region.number);
		});
		OrgSMOCombo.lastQuery = lang['stroka_kotoruyu_nikto_ne_dodumaetsya_vvodit_v_kachestve_filtra_ibo_eto_bred_iskat_smo_po_takoy_stroke'];
		OrgSMOCombo.setBaseFilter(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == getGlobalOptions().region.number);
		});
	},
	createXML: function(addParams) 
	{
		var form = this;
		var base_form = form.Panel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var
			Lpu_id = base_form.findField('Lpu_id').getValue(),
			OrgSMO_id = base_form.findField('OrgSMO_id').getValue();
			PackageNum = base_form.findField('PackageNum').getValue();
		form.getLoadMask().show();
		
		var params = {
			AttachLpu_id: Lpu_id,
			OrgSMO_id: OrgSMO_id,
			OverrideExportOneMoreOrUseExist: 1,
			PackageNum: PackageNum
		};

		if ( form.Panel.findById('rxw_radio_usenew').getValue() ) {
			params.OverrideExportOneMoreOrUseExist = 2;
		}

		if (!Ext.isEmpty(base_form.findField('Date_upload').getValue()) ) {
			params.Date_upload = base_form.findField('Date_upload').getValue().format('d.m.Y');
		}
		params.Date_upload = base_form.findField('Date_upload').getValue().format('d.m.Y');
		if ( !Ext.isEmpty(addParams) ) {
			for ( var par in addParams) {
				params[par] = addParams[par];
			}
		}
		else {
			addParams = [];
		}

		Ext.getCmp('rxfOk').disable();

		Ext.Ajax.request({
			url: '/?c=PersonCard&m=loadAttachedList',
			params: params,
			timeout: 1800000,
			callback: function(options, success, response) 
			{
				form.getLoadMask().hide();
				Ext.getCmp('rxfOk').enable();
				if (success)
				{
					if (!response.responseText) {
						var newParams = addParams;
						newParams.OverrideExportOneMoreOrUseExist = 1;
						newParams.onlyLink = 1;
						form.createXML(newParams);
						return false;
					}
					var result = Ext.util.JSON.decode(response.responseText);
					
					if (result.Error_Code && result.Error_Code == '10') { // Ñòàòóñ ðååñòðà "Ïðîâåäåí êîíòðîëü ÔËÊ"
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' )
								{
									var newParams = addParams;
									newParams.OverrideControlFlkStatus = 1;
									form.createXML(newParams);
								}
							},
							msg: lang['status_reestra_proveden_kontrol_flk_vyi_uverenyi_chto_hotite_povtorono_otpravit_ego_v_tfoms'],
							title: lang['podtverjdenie']
						});
						
						return false;
					}
					
					if (result.Error_Code && result.Error_Code == '11') { // Óæå åñòü âûãðóæåííûé XML
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' )
								{
									var newParams = addParams;
									newParams.OverrideExportOneMoreOrUseExist = 2;
									form.createXML(newParams);
								} else {
									var newParams = addParams;
									newParams.OverrideExportOneMoreOrUseExist = 1;
									form.createXML(newParams);
								}
							},
							msg: lang['fayl_reestra_suschestvuet_na_servere_esli_vyi_hotite_sformirovat_novyiy_fayl_vyiberete_da_esli_hotite_skachat_fayl_s_servera_najmite_net'],
							title: lang['podtverjdenie']
						});
						
						return false;
					}
						
					var alt = '';
					var msg = '';
					form.refresh = true;
					if (result.usePrevXml)
					{
						alt = lang['izmeneniy_s_reestrom_ne_byilo_proizvedeno_ispolzuetsya_sohranennyiy_xml_predyiduschey_vyigruzki'];
						msg = lang['xml_predyiduschey_vyigruzki'];
					}
					if (result.Link) {
						form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить список</a>'+msg;
						form.radioButtonGroup.hide();
						form.syncShadow();
					}
					if (result.success === false) {
						form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
						form.radioButtonGroup.hide();
						form.syncShadow();
					}
					form.TextPanel.render();
				}
				else 
				{
					var result = Ext.util.JSON.decode(response.responseText);
					form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
					form.TextPanel.render();
				}
			}
		});
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
		}
		return this.loadMask;
	},
	show: function() 
	{
		sw.Promed.swPersonXmlWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.Panel.getForm(),
			form = this;

		base_form.reset();

		if ( isSuperAdmin() ) {
			base_form.findField('Lpu_id').clearValue();
			base_form.findField('Lpu_id').enable();
		}
		else if ( isLpuAdmin(getGlobalOptions().lpu_id) ) {
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.findField('Lpu_id').disable();
		}
		else {
			sw.swMsg.alert(lang['oshibka'], lang['funktsional_nedostupen'], function() { form.hide(); });
			return false;
		}

		form.onHide = Ext.emptyFn;
		form.buttons[0].enable();
		form.refresh = false;
		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_prikreplennogo_naseleniya_v_formate_xml'];
		form.TextPanel.render();
		if ( getRegionNick().inlist([ 'astra' ]) ) {
			this.exportTypeGroup.show();
			base_form.findField('Date_upload').setValue(getGlobalOptions().date);
		}
		else {
			this.exportTypeGroup.hide();
		}

		base_form.findField('OrgSMO_id').setAllowBlank(true);
		base_form.findField('OrgSMO_id').setContainerVisible(getRegionNick().inlist([ 'astra' ]));

		form.filterOrgSMOCombo();

		this.radioButtonGroup.hide();
		this.syncShadow();
	}
});