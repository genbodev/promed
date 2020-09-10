/**
* swRegistryXmlWindow - окно выгрузки реестра в XML.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Пшеницын Иван
* @version      22.06.2011
* @comment      Префикс для id компонентов rxw (RegistryXmlWindow)
*
*
* @input data: Registry_id - ID регистра
*/

sw.Promed.swRegistryXmlWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'RegistryXmlWindow',
	title: lang['formirovanie_xml'],
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		var win = this;

		this.TextPanel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryXmlTextPanel',
			html: langs('Выгрузка данных реестра в формате Xml'),
			style: 'padding-bottom: 0.25em;'
		});
		
		this.radioButtonGroup = new sw.Promed.Panel({
			items: [{
				xtype: 'radio',
				hideLabel: true,
				boxLabel: lang['skachat_fayl_s_servera'],
				inputValue: 0,
				id: 'rxw_radio_useexist',
				name: 'exporttype',
				checked: true,
				listeners: {
					'check': function(radio, checked, c){
						var base_form = win.Panel.getForm();

						if ( checked ) {
							base_form.findField('RegistryRecipient_id').setAllowBlank(false);
							base_form.findField('RegistryRecipient_id').setContainerVisible(false);
							base_form.findField('RegistryRecipient_id').clearValue();
						}
					}
				}
			}, {
				xtype: 'radio',
				hideLabel: true,
				boxLabel: lang['sformirovat_novyiy_fayl'],
				inputValue: 1,
				id: 'rxw_radio_usenew',
				name: 'exporttype',
				listeners: {
					'check': function(radio, checked, c){
						var base_form = win.Panel.getForm();

						if ( checked && getRegionNick() == 'pskov' && win.KatNasel_SysNick == 'oblast' ) {
							base_form.findField('RegistryRecipient_id').setAllowBlank(false);
							base_form.findField('RegistryRecipient_id').setContainerVisible(true);
							base_form.findField('RegistryRecipient_id').setValue('S');
						}
						else {
							base_form.findField('RegistryRecipient_id').setAllowBlank(true);
							base_form.findField('RegistryRecipient_id').setContainerVisible(false);
							base_form.findField('RegistryRecipient_id').clearValue();
						}
					}
				}
			}],
			style: 'padding-bottom: 0.25em;'
		});
		
		this.Panel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'RegistryXmlPanel',
			labelAlign: 'right',
			labelWidth: 70,
			items: [
				this.TextPanel,
				this.radioButtonGroup,
				{
					displayField: 'RegistryRecipient_Name',
					editable: false,
					fieldLabel: 'Получатель',
					hiddenName: 'RegistryRecipient_id',
					mode:'local',
					store: new Ext.data.SimpleStore(  {
						data: [
							[ 'S', langs('СМО') ],
							[ 'T', langs('ТФОМС') ]
						],
						fields: [
							'RegistryRecipient_id',
							'RegistryRecipient_Name'
						]
					}),
					tpl: '<tpl for="."><div class="x-combo-list-item">'+
						'<span style="color: red;">{RegistryRecipient_id}</span> {RegistryRecipient_Name} '+ '&nbsp;' +
						'</div></tpl>',
					triggerAction: 'all',
					valueField: 'RegistryRecipient_id',
					width: 150,
					xtype: 'combo'
				}
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
					this.ownerCt.createXML();
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
			items: [this.Panel]
		});
		sw.Promed.swRegistryXmlWindow.superclass.initComponent.apply(this, arguments);
	},
	
	listeners: 
	{
		'hide': function() 
		{
			if (this.refresh)
				this.onHide();
		}
	},
	createXML: function(addParams) 
	{
		var Registry_id = this.Registry_id;
		var RegistryType_id = this.RegistryType_id;
		var KatNasel_id = this.KatNasel_id;
		var form = this;
		var base_form = this.Panel.getForm();
		form.getLoadMask().show();
		
		var params = {
			Registry_id: Registry_id,
			RegistryRecipient_id: base_form.findField('RegistryRecipient_id').getValue(),
			RegistryType_id: RegistryType_id,
			Registry_IsNew: this.Registry_IsNew,
			KatNasel_id: KatNasel_id,
			OverrideExportOneMoreOrUseExist: 1
		};

		if (form.Panel.findById('rxw_radio_usenew').getValue()) {
			params.OverrideExportOneMoreOrUseExist = 2;
		}
		
		if (addParams != undefined) {
			for(var par in addParams) {
				params[par] = addParams[par];
			}
		} else {
			addParams = [];
		}

		if (form.withSign) {
			params.withSign = 1;
		}
		
		Ext.Ajax.request(
		{
			url: form.formUrl,
			params: params,
			timeout: 1800000,
			failure: function(response, options) {
				form.getLoadMask().hide();

				var msg = '<div>Ошибка при выполнении запроса к серверу</div>';

				if ( !Ext.isEmpty(response.responseText) ) {
					msg = msg + '<div>' + response.responseText.substr(0, 200) + '</div>';
				}
				else if ( !Ext.isEmpty(response.statusText) ) {
					msg = msg + '<div>' + response.statusText + '</div>';
				}

				form.TextPanel.getEl().dom.innerHTML = msg;
				form.TextPanel.render();
			},
			success: function(response, options) 
			{
				form.getLoadMask().hide();

				if (!response.responseText) {
					var newParams = addParams;
					newParams.OverrideExportOneMoreOrUseExist = 1;
					newParams.onlyLink = 1;
					form.createXML(newParams);
					return false;
				}

				var result = Ext.util.JSON.decode(response.responseText);

				if ( result.success )
				{
					if ( !Ext.isEmpty(result.Alert_Msg) ) {
						sw.swMsg.alert('Предупреждение', result.Alert_Msg, function() {
							var newParams = addParams;
							newParams.OverrideExportOneMoreOrUseExist = 1;
							newParams.onlyLink = 1;
							form.createXML(newParams);
							return false;
						});
						return false;
					}
					
					if (result.Error_Code && result.Error_Code == '10') { // Статус реестра "Проведен контроль ФЛК"
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
					
					if (result.Error_Code && result.Error_Code == '11') { // Уже есть выгруженный XML
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

					if (form.withSign) {
						// выполняем подпись
						if (result.filehash) {
							params.filehash = result.filehash;
							getWnd('swCertSelectWindow').show({
								callback: function(cert) {
									sw.Applets.CryptoPro.signText({
										text: params.filehash,
										Cert_Thumbprint: cert.Cert_Thumbprint,
										callback: function (sSignedData) {
											// сохраняем подпись в файл, помечаем реестр как готов к отправке в ТФОМС.
											params.documentSigned = sSignedData;
											form.getLoadMask(lang['podpisanie_reestra']).show();
											Ext.Ajax.request({
												url: '/?c=RegistryUfa&m=signRegistry',
												params: params,
												callback: function (options, success, response) {
													form.getLoadMask().hide();
													if (success) {
														var result = Ext.util.JSON.decode(response.responseText);
														if (result.success && result.Link) {
															form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="' + alt + '" href="' + result.Link + '">Скачать и сохранить подписанный реестр</a>' + msg;
															form.radioButtonGroup.hide();
															form.syncShadow();
															Ext.getCmp('rxfOk').disable();
														}
													}
												}
											});
										}
									});
								}
							});
						} else {
							log(lang['oshibka_pri_poluchenii_hesha_fayla'], result);
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_poluchenii_hesha_fayla']);
						}
					}

					if (result.Link) {
						form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="' + alt + '" href="' + result.Link + '">Скачать и сохранить реестр</a>' + msg;
						form.radioButtonGroup.hide();
						base_form.findField('RegistryRecipient_id').setContainerVisible(false);
						form.syncShadow();
						Ext.getCmp('rxfOk').disable();
					}
					if (result.ErrorLink) {
						form.TextPanel.getEl().dom.innerHTML += '<br><a target="_blank" title="' + alt + '" href="' + result.ErrorLink + '">Скачать файл с ошибками (Реестр не прошёл ФЛК)</a>' + msg;
					}

					form.TextPanel.render();
					form.callback();
				}
				else 
				{
					form.radioButtonGroup.hide();
					Ext.getCmp('rxfOk').disable();
					form.TextPanel.getEl().dom.innerHTML = (!Ext.isEmpty(result.Error_Msg) ? result.Error_Msg : 'Ошибка при выполнении запроса к серверу');
					form.TextPanel.render();
					form.syncShadow();
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
		sw.Promed.swRegistryXmlWindow.superclass.show.apply(this, arguments);
		var form = this;
		form.Panel.getForm().reset();
		form.withSign = false;
		form.Registry_id = null;
		form.RegistryType_id = null;
		form.KatNasel_id = null;
		form.KatNasel_SysNick = null;
		form.RegistryCheckStatus_id = null;
		form.Registry_IsNew = null;
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		Ext.getCmp('rxfOk').enable();
		form.refresh = false;

		form.Panel.getForm().findField('RegistryRecipient_id').setContainerVisible(false);
		form.Panel.getForm().findField('RegistryRecipient_id').setAllowBlank(true);

		if (arguments[0].withSign)
		{
			form.withSign = arguments[0].withSign;
		}

		if (form.withSign) {
			form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_reestra_v_formate_xml_s_podpisaniem'];
		} else {
			form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_reestra_v_formate_xml'];
		}
		form.TextPanel.render();
		
		if (!arguments[0] || !arguments[0].Registry_id) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi'] + form.id + lang['ne_ukazanyi_neobhodimyie_vhodnyie_parametryi'],
				title: lang['oshibka']
			});
			this.hide();
		}

		if (arguments[0].Registry_id) 
		{
			form.Registry_id = arguments[0].Registry_id;
		}
		if (arguments[0].RegistryType_id) 
		{
			form.RegistryType_id = arguments[0].RegistryType_id;
		}
		if (arguments[0].KatNasel_id) 
		{
			form.KatNasel_id = arguments[0].KatNasel_id;
		}
		if (arguments[0].KatNasel_SysNick) 
		{
			form.KatNasel_SysNick = arguments[0].KatNasel_SysNick;
		}
		if (typeof arguments[0].callback == 'function') 
		{
			form.callback = arguments[0].callback;
		}
		if (typeof arguments[0].onHide == 'function') 
		{
			form.onHide = arguments[0].onHide;
		}
		if(arguments[0].RegistryCheckStatus_id){
			form.RegistryCheckStatus_id =arguments[0].RegistryCheckStatus_id; 
		}
		if(arguments[0].Registry_IsNew){
			form.Registry_IsNew = arguments[0].Registry_IsNew;
		}
		if (arguments[0].url)
		{
			form.formUrl = arguments[0].url;
		}
		else 
		{
			form.formUrl = '/?c=Registry&m=exportRegistryToXml';
		}
		
		this.radioButtonGroup.hide();
		this.syncShadow();
		form.getLoadMask(lang['poluchenie_dannyih_po_reestru']).show();
		Ext.Ajax.request(
		{
			url: form.formUrl + 'CheckExist',
			params: {
				Registry_id: form.Registry_id,
				RegistryType_id: form.RegistryType_id
			},
			callback: function(options, success, response) 
			{
				form.getLoadMask().hide();
				if (success && response.responseText)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.exportfile == 'inprogress') {
						sw.swMsg.alert(lang['soobschenie'], lang['reestr_uje_eksportiruetsya_pojaluysta_dojdites_okonchaniya_eksporta_v_srednem_1-10_min'], function() {
							form.hide();
						});
					}
					if (result.exportfile == 'exists' || result.exportfile == 'onlyexists') {
						// показываем выбор новый или старый файл
						form.radioButtonGroup.show();
						if (result.exportfile == 'onlyexists') {
							form.Panel.findById('rxw_radio_usenew').hide();
						} else {
							form.Panel.findById('rxw_radio_usenew').show();
						}
						form.syncShadow();
					}
					else if ( getRegionNick() == 'pskov' && form.KatNasel_SysNick == 'oblast' ) {
						form.Panel.getForm().findField('RegistryRecipient_id').setAllowBlank(false);
						form.Panel.getForm().findField('RegistryRecipient_id').setContainerVisible(true);
						form.Panel.getForm().findField('RegistryRecipient_id').setValue('S');
				}
			}
			}
		});
	}
});