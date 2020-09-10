/**
* swOrgEditWindow - окно просмотра, добавления и редактирования организаций
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      14.05.2009
* @comment      Префикс для id компонентов OEF (OrgEditForm)
*/

sw.Promed.swOrgEditWindow = Ext.extend(sw.Promed.BaseForm, {
	allowDuplicateOpening: true,
	autoHeight: true,
	bodyStyle: 'padding: 2px',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closeAction: 'hide',
	draggable: false,
	id: 'OrgEditWindow',
	deleteOrgFilial: function() {
		var grid = this.OrgFilialGrid.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('OrgFilial_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_tipa_slujbyi']);
								}
								else {
									grid.getStore().remove(record);
								}

								if ( grid.getStore().getCount() > 0 ) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_filiala_voznikli_oshibki']);
							}
						},
						params: {
							OrgFilial_id: record.get('OrgFilial_id')
						},
						url: '/?c=OrgStruct&m=deleteOrgFilial'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_filial'],
			title: lang['vopros']
		});
	},
	setCode: function(){
		var win = this;
		var base_form = win.OrgEditForm.getForm();
		
		// Запрос к серверу для получения нового кода
		win.getLoadMask().show();
		Ext.Ajax.request(
		{	
			url: '/?c=Farmacy&m=generateContragentCode',
			callback: function(options, success, response) 
			{
				win.getLoadMask().hide();
				
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.findField('OrgFarmacy_ACode').setValue(result[0].Contragent_Code);
				}
			}
		});
	},
	GridAddAction:function(grid){
		var win = this;
		if(win.Org_id<=0){
			win.submit({
				callback:function(){
					var params = {
						 limit: 100
						,start: 0
						,Org_id: win.Org_id
						}
					 grid.loadData({
							params: params,
							globalFilters: params,
							callback:function(){
								grid.editRecord('add')
							}
						});
				}
			});
		}else{
			grid.editRecord('add')
		}
	},
	initComponent: function() {
		var win = this;

		win.OrgServiceTerrGrid = new sw.Promed.ViewFrame({
			id: win.id + '_' + 'OrgServiceTerrGrid',
			actions: [
				{name: 'action_add',handler:function(){win.GridAddAction(win.OrgServiceTerrGrid)}},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete'},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			object: 'OrgServiceTerr',
			editformclassname: 'swOrgServiceTerrEditWindow',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=OrgServiceTerr&m=loadOrgServiceTerrGrid',
			paging: false,
			region: 'center',
			stringfields: [
				{name: 'OrgServiceTerr_id', type: 'int', header: 'ID', key: true},
				{name: 'KLCountry_Name', type: 'string', header: lang['strana'], width: 120},
				{name: 'KLRgn_Name', type: 'string', header: lang['region'], width: 120},
				{name: 'KLSubRgn_Name', type: 'string', header: lang['rayon'], width: 120},
				{name: 'KLCity_Name', type: 'string', header: lang['gorod'], width: 120},
				{name: 'KLTown_Name', type: 'string', header: lang['naselennyiy_punkt'], width: 120},
				{name: 'KLAreaType_Name', type: 'string', header: lang['tip_naselennogo_punkta'], width: 120}
			],
			totalProperty: 'totalCount'
		});
		
		win.OrgRSchetGrid  = new sw.Promed.ViewFrame({
			id: win.id + '_' + 'OrgRSchetGrid',
			title: '',
			object: 'OrgRSchet',
			dataUrl: '/?c=OrgStruct&m=loadOrgRSchetGrid',
			editformclassname: 'swOrgRSchetEditWindow',
			autoLoadData: false,
			paging: true,
			root: 'data',
			region: 'center',
			toolbar: true,
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'OrgRSchet_id', type: 'int', header: 'OrgRSchet_id', key: true, hidden: true},
				{name: 'OrgRSchet_RSchet', header: 'Номер счёта', width: 120, id: 'autoexpand'},
				{name: 'OrgRSchetType_Name', header: 'Тип счёта', width: 120},
				{name: 'OrgBank_Name', header: lang['bank'], width: 120},
				{name: 'OrgRSchet_begDate', type:'date', header: lang['data_otkryitiya'], width: 120},
				{name: 'OrgRSchet_endDate', type:'date', header: lang['data_zakryitiya'], width: 120},
				{name: 'Okv_Nick', type:'date', header: lang['valyuta'], width: 120},
				{name: 'OrgRSchet_Name', type:'date', header: lang['primechanie'], width: 120}
			],
			actions:
			[
				{name: 'action_add',handler:function(){win.GridAddAction(win.OrgRSchetGrid)}},
				{name:'action_edit'},
				{name:'action_print'},
				{name:'action_view'},
				{name:'action_delete'}
			]
		});
		
		win.OrgHeadGrid  = new sw.Promed.ViewFrame({
			id: win.id + '_' + 'OrgHeadGrid',
			title: '',
			object: 'OrgHead',
			dataUrl: '/?c=OrgStruct&m=loadOrgHeadGrid',
			editformclassname: 'swOrgContactEditWindow',
			autoLoadData: false,
			paging: true,
			root: 'data',
			region: 'center',
			toolbar: true,
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'OrgHead_id', type: 'int', header: 'OrgHead_id', key: true, hidden: true},
				{name: 'OrgHead_Fio', header: lang['fio'], width: 120, id: 'autoexpand'},
				{name: 'OrgHeadPost_Name', header: lang['doljnost'], width: 120},
				{name: 'OrgHead_Phone', header: lang['telefon'], width: 120},
				{name: 'OrgHead_Mobile', header: lang['mobilnyiy_telefon'], width: 120}
			],
			actions:
			[
				{name: 'action_add',handler:function(){win.GridAddAction(win.OrgHeadGrid)}},
				{name:'action_edit'},
				{name:'action_print'},
				{name:'action_view'},
				{name:'action_delete'}
			]
		});
		
		win.OrgLicenceGrid  = new sw.Promed.ViewFrame(
		{
			id: win.id + '_' + 'OrgLicenceGrid',
			title: '',
			object: 'OrgLicence',
			dataUrl: '/?c=OrgStruct&m=loadOrgLicenceGrid',
			editformclassname: 'swOrgLicenceEditWindow',
			autoLoadData: false,
			paging: true,
			root: 'data',
			region: 'center',
			toolbar: true,
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'OrgLicence_id', type: 'int', header: 'OrgLicence_id', key: true, hidden: true},
				{name: 'OrgLicence_Num', header: lang['nomer_litsenzii'], width: 120, id: 'autoexpand'},
				{name: 'OrgLicence_setDate', type:'date', header: lang['data_vyidachi'], width: 120},
				{name: 'OrgLicence_RegNum', header: lang['registratsionnyiy_nomer'], width: 120},
				{name: 'OrgLicence_begDate', type:'date', header: lang['nachalo_deystviya'], width: 120},
				{name: 'OrgLicence_endDate', type:'date', header: lang['okonchanie_deystviya'], width: 120}
			],
			actions:
			[
				{name: 'action_add',handler:function(){win.GridAddAction(win.OrgLicenceGrid)}},
				{name:'action_edit'},
				{name:'action_print'},
				{name:'action_view'},
				{name:'action_delete'}
			]
		});
		
		win.OrgFilialGrid = new sw.Promed.ViewFrame(
		{
			id: win.id + '_' + 'OrgFilialGrid',
			title: '',
			object: 'OrgFilial',
			dataUrl: '/?c=OrgStruct&m=loadOrgFilialGrid',
			editformclassname: 'swOrgFilialEditWindow',
			autoLoadData: false,
			paging: true,
			root: 'data',
			region: 'center',
			toolbar: true,
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'OrgFilial_id', type: 'int', header: 'OrgFilial_id', key: true, hidden: true},
				{name: 'OrgFilial_Name', header: lang['nazvanie_filiala'], width: 120, id: 'autoexpand'}
			],
			actions:
			[
				{name: 'action_add',handler:function(){win.GridAddAction(win.OrgFilialGrid)}},
				{name:'action_edit'},
				{name:'action_print'},
				{name:'action_view'},
				{name:'action_delete', handler: function() {
					win.deleteOrgFilial();
				}}
			]
		});
		
		win.additionalFarm = new Ext.Panel({
			layout: 'form',
			items: [{
				fieldLabel: lang['kak_dobratsya'],
				name: 'OrgFarmacy_HowGo',
				width: 397,
				tabIndex: TABINDEX_OEW + 30,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['kod_apteki'],
				name: 'OrgFarmacy_ACode',
				width: 150,
				tabIndex: TABINDEX_OEW + 31,
				xtype: 'trigger',
				maskRe: /\d/,
				autoCreate: {tag: "input", size:15, maxLength: "15", autocomplete: "off"},
				triggerAction: 'all',
				triggerClass: 'x-form-plus-trigger',
				onTriggerClick: function() 
				{
					win.setCode(this);
				},
				enableKeyEvents:true,
				listeners:
				{
					keydown: function(inp, e) 
					{
						if (e.getKey() == e.F2)
						{
							this.onTriggerClick();
							if ( Ext.isIE )
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							e.stopEvent(); 
						}
					}
				}
			}, {
				allowBlank: false,
				fieldLabel: lang['otkryita'],
				hiddenName: 'OrgFarmacy_IsEnabled',
				value: 1,
				tabIndex: TABINDEX_OEW + 32,
				width: 150,
				xtype: 'swyesnocombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['fed_lgota'],
				hiddenName: 'OrgFarmacy_IsFedLgot',
				value: 1,
				tabIndex: TABINDEX_OEW + 33,
				width: 150,
				xtype: 'swyesnocombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['reg_lgota'],
				hiddenName: 'OrgFarmacy_IsRegLgot',
				value: 1,
				tabIndex: TABINDEX_OEW + 34,
				width: 150,
				xtype: 'swyesnocombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['7_nozologiy'],
				hiddenName: 'OrgFarmacy_IsNozLgot',
				value: 1,
				tabIndex: TABINDEX_OEW + 35,
				width: 150,
				xtype: 'swyesnocombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['ns_i_pv'],
				hiddenName: 'OrgFarmacy_IsNarko',
				value: 1,
				tabIndex: TABINDEX_OEW + 36,
				width: 150,
				xtype: 'swyesnocombo'
			}]
		});
		
		win.additionalBank = new Ext.Panel({
			layout: 'form',
			items: [{
				fieldLabel: 'Кор. счёт',
				name: 'OrgBank_KSchet',
				maxLength: 20,
				minLength: 20,
				autoCreate: { tag: "input", type: "text", size: "20", maxLength: "20", autocomplete: "off" },
				width: 397,
				maskRe: /\d/,
				tabIndex: TABINDEX_OEW + 40,
				xtype: 'textfield'
			}, {
				fieldLabel: lang['bik'],
				name: 'OrgBank_BIK',
				maxLength: 9,
				minLength: 9,
				autoCreate: { tag: "input", type: "text", size: "9", maxLength: "9", autocomplete: "off" },
				width: 397,
				maskRe: /\d/,
				tabIndex: TABINDEX_OEW + 41,
				xtype: 'textfield'
			}]
		});
		
		win.additionalSmo = new Ext.Panel({
			layout: 'form',
			labelWidth: 150,
			items: [{
				hiddenName: 'KLRGNSmo_id',
				minChars: 0,
				queryDelay: 1,
				listWidth: 300,
				width: 300,
				fieldLabel: lang['territoriya'],
				tabIndex: TABINDEX_OEW + 50,
				xtype: 'swregioncombo'
			}, {
				border: false,
				layout: 'column',
				labelWidth: 150,
				items: [{
					border: false,
					layout: 'form',
					items: [{
						fieldLabel: lang['kod_smo'],
						name: 'OrgSMO_RegNomC',
						maxLength: 6,
						minLength: 0,
						autoCreate: { tag: "input", type: "text", size: "6", maxLength: "6", autocomplete: "off" },
						width: 100,
						maskRe: /\d/,
						tabIndex: TABINDEX_OEW + 51,
						xtype: 'textfield'
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						hideLabel: true,
						name: 'OrgSMO_RegNomN',
						maxLength: 6,
						minLength: 0,
						autoCreate: { tag: "input", type: "text", size: "6", maxLength: "6", autocomplete: "off" },
						width: 100,
						maskRe: /\d/,
						tabIndex: TABINDEX_OEW + 52,
						xtype: 'textfield'
					}]
				}]
			}, {
				fieldLabel: lang['federalnyiy_kod_smo'],
				name: 'Orgsmo_f002smocod',
				maxLength: 5,
				minLength: 5,
				autoCreate: { tag: "input", type: "text", size: "5", maxLength: "5", autocomplete: "off" },
				width: 200,
				maskRe: /\d/,
				tabIndex: TABINDEX_OEW + 53,
				xtype: 'textfield'
			}, {
				fieldLabel: lang['dms'],
				xtype: 'swyesnocombo',
				width: 80,
				tabIndex: TABINDEX_OEW + 54,
				hiddenName: 'OrgSMO_isDMS'
			}]
		});
		
		win.cardPanel = new Ext.Panel({
			region: 'center',
			layout: 'card',
			border: false,
			activeItem: 0, 
			defaults: 
			{
				border:false
			},
			items: 
			[
				win.additionalFarm,
				win.additionalBank,
				win.additionalSmo
			]
		});
		
		this.OrgEditForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			frame: true,
			labelAlign: 'right',
			labelWidth: 145,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Org_id' },
				{ name: 'Org_Code' },
				{ name: 'Org_Nick' },
				{ name: 'Org_StickNick' },
				{ name: 'Org_begDate' },
				{ name: 'Org_endDate' },
				{ name: 'Org_rid' },
				{ name: 'Org_nid' },
				{ name: 'Org_Description' },
				{ name: 'Org_Name' },
				{ name: 'OrgType_id'},
				{ name: 'UAddress_id' },
				{ name: 'UAddress_Zip' },
				{ name: 'UKLCountry_id' },
				{ name: 'UKLRGN_id' },
				{ name: 'UKLSubRGN_id' },
				{ name: 'UKLCity_id' },
				{ name: 'UKLTown_id' },
				{ name: 'UKLStreet_id' },
				{ name: 'UAddress_House' },
				{ name: 'UPersonSprTerrDop_id' },
				{ name: 'UAddress_Corpus' },
				{ name: 'UAddress_Flat' },
				{ name: 'UAddress_Address' },
				{ name: 'UAddress_AddressText' },
				{ name: 'PAddress_id' },
				{ name: 'PAddress_Zip' },
				{ name: 'PKLCountry_id' },
				{ name: 'PKLRGN_id' },
				{ name: 'PKLSubRGN_id' },
				{ name: 'PKLCity_id' },
				{ name: 'PKLTown_id' },
				{ name: 'PKLStreet_id' },
				{ name: 'PPersonSprTerrDop_id' },
				{ name: 'PAddress_House' },
				{ name: 'PAddress_Corpus' },
				{ name: 'PAddress_Flat' },
				{ name: 'PAddress_Address' },
				{ name: 'PAddress_AddressText' },
				{ name: 'Oktmo_id' },
				{ name: 'Oktmo_Name' },
				{ name: 'Org_INN' },
				{ name: 'Org_OGRN' },
				{ name: 'Org_OKATO' },
				{ name: 'Org_KPP' },
				{ name: 'OrgType_SysNick' },
				{ name: 'Org_Email' },
				{ name: 'Org_Phone' },
				{ name: 'Okved_id' },
				{ name: 'Org_OKPO' },
				{ name: 'Okopf_id' },
				{ name: 'Okfs_id' },
				{ name: 'OrgFarmacy_ACode' },
				{ name: 'OrgFarmacy_HowGo' },
				{ name: 'OrgFarmacy_IsEnabled' },
				{ name: 'OrgFarmacy_IsFedLgot' },
				{ name: 'OrgFarmacy_IsRegLgot' },
				{ name: 'OrgFarmacy_IsNozLgot' },
				{ name: 'OrgFarmacy_IsNarko' },
				{ name: 'OrgBank_KSchet' },
				{ name: 'OrgBank_BIK' },
				{ name: 'OrgSMO_isDMS' },
				{ name: 'OrgSMO_RegNomC' },
				{ name: 'OrgSMO_RegNomN' },
				{ name: 'Orgsmo_f002smocod' },
				{ name: 'KLCountry_id' },
				{ name: 'KLRGN_id' },
				{ name: 'KLSubRGN_id' },
				{ name: 'KLCity_id' },
				{ name: 'KLTown_id' },
				{ name: 'KLRGNSmo_id' },
				{ name: 'OrgAnatom_id' },
				{ name: 'OrgFarmacy_id' },
				{ name: 'OrgDep_id' },
				{ name: 'OrgBank_id' },
				{ name: 'OrgSMO_id' },
				{ name: 'Org_IsAccess' },
				{ name: 'Org_ONMSZCode' },
				{ name: 'OrgStac_Code' },
				{ name: 'Org_IsNotForSystem' },
				{ name: 'Org_Marking' },
				{ name: 'Org_f003mcod' },
			]),
			url: '/?c=Org&m=saveOrg',

			items: [{
				name: 'Org_id',
				xtype: 'hidden'
			}, {
				name: 'PAddress_id',
				xtype: 'hidden'
			}, {
				name: 'PAddress_Zip',
				xtype: 'hidden'
			}, {
				name: 'PKLCountry_id',
				xtype: 'hidden'
			}, {
				name: 'PKLRGN_id',
				xtype: 'hidden'
			}, {
				name: 'PKLSubRGN_id',
				xtype: 'hidden'
			}, {
				name: 'PKLCity_id',
				xtype: 'hidden'
			}, {
				name: 'PPersonSprTerrDop_id',
				xtype: 'hidden'
			}, {
				name: 'PKLTown_id',
				xtype: 'hidden'
			}, {
				name: 'PKLStreet_id',
				xtype: 'hidden'
			}, {
				name: 'PAddress_House',
				xtype: 'hidden'
			}, {
				name: 'PAddress_Corpus',
				xtype: 'hidden'
			}, {
				name: 'PAddress_Flat',
				xtype: 'hidden'
			}, {
				name: 'PAddress_Address',
				xtype: 'hidden'
			}, {
				name: 'UAddress_id',
				xtype: 'hidden'
			}, {
				name: 'UAddress_Zip',
				xtype: 'hidden'
			}, {
				name: 'UKLCountry_id',
				xtype: 'hidden'
			}, {
				name: 'KLCity_id',
				xtype: 'hidden'
			}, {
				name: 'KLTown_id',
				xtype: 'hidden'
			}, {
				name: 'KLSubRGN_id',
				xtype: 'hidden'
			}, {
				name: 'KLCountry_id',
				xtype: 'hidden'
			},  {
				name: 'KLRGN_id',
				xtype: 'hidden'
			}, {
				name: 'UKLRGN_id',
				xtype: 'hidden'
			}, {
				name: 'UKLSubRGN_id',
				xtype: 'hidden'
			}, {
				name: 'UKLCity_id',
				xtype: 'hidden'
			},{
				name: 'UPersonSprTerrDop_id',
				xtype: 'hidden'
			},{
				name: 'UKLTown_id',
				xtype: 'hidden'
			}, {
				name: 'UKLStreet_id',
				xtype: 'hidden'
			}, {
				name: 'UAddress_House',
				xtype: 'hidden'
			}, {
				name: 'UAddress_Corpus',
				xtype: 'hidden'
			}, {
				name: 'UAddress_Flat',
				xtype: 'hidden'
			}, {
				name: 'UAddress_Address',
				xtype: 'hidden'
			}, {
				id: win.id + '_' + 'Oktmo_id',
				name: 'Oktmo_id',
				xtype: 'hidden'
			}, {
				name: 'OrgAnatom_id',
				xtype: 'hidden'
			}, {
				name: 'OrgFarmacy_id',
				xtype: 'hidden'
			}, {
				name: 'OrgType_SysNick',
				xtype: 'hidden'
			}, {
				name: 'OrgDep_id',
				xtype: 'hidden'
			}, {
				name: 'OrgBank_id',
				xtype: 'hidden'
			}, {
				name: 'OrgSMO_id',
				xtype: 'hidden'
			},{
				name: 'Org_IsAccess',
				xtype: 'hidden'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					labelWidth: 145,
					layout: 'form',
					items: [{
						allowBlank: false,
						allowLeadingZeroes: true,
						autoCreate: {tag: "input", size:14, maxLength: "10", autocomplete: "off"},
						minValue: 1,
						maxValue: 2147483647, // в базе поле int.
						enableKeyEvents: true,
						fieldLabel: lang['kod_organizatsii'],
						listeners: {
							keydown: function(inp, e) {
								if (e.getKey() == e.F2)
								{
									this.onTriggerClick();

									if ( Ext.isIE )
									{
										e.browserEvent.keyCode = 0;
										e.browserEvent.which = 0;
									}
									e.stopEvent();
								}
							}
						},
						maskRe: /\d/,
						name: 'Org_Code',
						onTriggerClick: function() {
							var Mask = new Ext.LoadMask(Ext.get('OrgEditWindow'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..." });
							Mask.show();

							Ext.Ajax.request({
								callback: function(opt, success, resp) {
									Mask.hide();

									var form = win.OrgEditForm.getForm();
									var response_obj = Ext.util.JSON.decode(resp.responseText);

									if (response_obj.Org_Code != '')
									{
										form.findField('Org_Code').setValue(response_obj.Org_Code);
									}
								},
								url: '/?c=Org&m=getMaxOrgCode'
							});
						},
						tabIndex: TABINDEX_OEW + 0,
						triggerAction: 'all',
						triggerClass: 'x-form-plus-trigger',
						width: 150,
						xtype: 'numberfield'
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 120,
					items: [{
						xtype: 'swdatefield',
						fieldLabel: lang['data_otkryitiya'],
						format: 'd.m.Y',
						allowBlank: true,
						name: 'Org_begDate',
						endDateField: 'Org_endDate',
						tabIndex: TABINDEX_OEW + 1,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],

						setMaxValue: function(val)
						{
							var curDate = new Date();

							if (getRegionNick() == 'vologda' && (!val || val > curDate))
								val = curDate;

							Object.getPrototypeOf(this).setMaxValue.call(this, val);
						}
					}]
				}, {
					border: false,
					layout: 'form',
					labelWidth: 100,
					items: [{
						xtype: 'swdatefield',
						fieldLabel: lang['data_zakryitiya'],
						format: 'd.m.Y',
						name: 'Org_endDate',
						begDateField: 'Org_begDate',
						listeners: {
							'change' :function() {
								win.checkOrgRid();
							}
						},
						tabIndex: TABINDEX_OEW + 2,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
					}]
				}]
			}, {
				allowBlank: false,
				fieldLabel: lang['naimenovanie'],
				listeners: {
					'keydown': function (inp, e) {
						if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
						{
							e.stopEvent();
							inp.ownerCt.getForm().findField('Org_Code').focus(true);
						}
					}
				},
				name: 'Org_Name',
				tabIndex: TABINDEX_OEW + 3,
				anchor: '95%',
				minLength: (getRegionNick() == 'vologda' ? 2 : undefined),
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: lang['kratkoe_naimenovanie'],
				listeners: {
					'change': function(field, newValue, oldValue) {
						var base_form = win.OrgEditForm.getForm();

						if ( !Ext.isEmpty(newValue) ) {
							newValue = newValue.replace(/[\"\']+/ig, '').substr(0, 38);
							base_form.findField('Org_StickNick').setValue(newValue);
						}
					}
				},
				name: 'Org_Nick',
				tabIndex: TABINDEX_OEW + 4,
				anchor: '95%',
				xtype: 'textfield'
			}, {
				allowBlank: false,
				autoCreate: {tag: "input", size: 38, maxLength: "38", autocomplete: "off"},
				fieldLabel: lang['naimenovanie_dlya_lvn'],
				name: 'Org_StickNick',
				tabIndex: TABINDEX_OEW + 5,
				anchor: '95%',
				xtype: 'textfield'
			},
			{
				allowBlank: (getRegionNick().inlist(['tambov','msk','komi','amur'])),
				comboSubject: 'OrgType',
				typeCode: 'int',
				fieldLabel: lang['tip_organizatsii'],
				allowSysNick: true,
				listeners: {
					'change' :function() {
						win.checkOrgTypeAdditional();
					}
				},
				hiddenName: 'OrgType_id',
				lastQuery: '',
				tabIndex: TABINDEX_OEW + 6,
				anchor: '95%',
				xtype: 'swcommonsprcombo'
			},
			{
				fieldLabel: lang['opisanie'],
				name: 'Org_Description',
				anchor: '95%',
				tabIndex: TABINDEX_OEW + 7,
				xtype: 'textfield'
			},
			{
				fieldLabel: lang['nasledovatel'],
				anchor: '95%',
				hiddenName: 'Org_rid',
				allowBlank: true,
				xtype: 'sworgcombo',
				tabIndex: TABINDEX_OEW + 8,
				onTrigger1Click: function() {
					var combo = this;
					if (this.disabled) {
						return false;
					}
					var form = win.OrgEditForm;
					var base_form = form.getForm();
					var searchWnd = getWnd('swOrgSearchWindow');
					
					searchWnd.show({
						OrgType_id: base_form.findField('OrgType_id').getValue(),
						onSelect: function(orgData) {
							if ( orgData.Org_id > 0 ) {
								combo.getStore().load({
									params: {
										Object:'Org',
										Org_id: orgData.Org_id,
										Org_Name:''
									},
									callback: function() {
										combo.setValue(orgData.Org_id);
										combo.focus(true, 500);
										combo.fireEvent('change', combo);
									}
								});
							}

							searchWnd.hide();
						},
						onClose: function() {combo.focus(true, 200)}
					});
				}
			}, {
				disabled: true,
				fieldLabel: lang['pravopreemnik'],
				anchor: '95%',
				hiddenName: 'Org_nid',
				allowBlank: true,
				hideTrigger: true,
				xtype: 'sworgcombo',
				tabIndex: TABINDEX_OEW + 9
			}, {
				layout: 'column',
				items: [
					{
						layout: 'form',
						items: [{
							readOnly: (getRegionNick() == 'perm'),
							disabled: (getRegionNick() == 'perm'),
							allowDecimals: false,
							allowNegative: false,
							allowLeadingZeroes: true,
							fieldLabel: langs('Код стац. учреждения'),
							name: 'OrgStac_Code',
							width: 100,
							xtype: 'numberfield'
						}]

					}, {
						layout: 'form',
						hidden: getRegionNick() === 'kz' ? true : false,
						items: [
							{
								fieldLabel: langs('Идентификатор в ИС «Маркировка»'),
								name: 'Org_Marking',
								xtype: 'textfield',
								layout: 'fieldset',
								width: 270,
								maxLength: 36,
								maxLengthText: 'Максимальное количество знаков - 36',
								validateOnBlur: true,
								allowBlank: true,
								autoCreate: {
									tag: "input",
									type: "text",
									maxlength: '36'
								}
							}
						]
					}
				]

				}, {
					layout: 'form',
					hidden: getRegionNick() === 'kz' ? true : false,
					items: [
						{
							fieldLabel: langs('Федеральный реестровый код'),
							name: 'Org_f003mcod',
							xtype: 'textfield',
							maskRe: new RegExp('[0-9]'),
							maxLength: 6,
							maxLengthText: 'Максимальное количество знаков - 6',
							disabled: !haveArmType('superadmin'),
							width: 100,
							allowBlank: true
						}
					]
				}, {
				fieldLabel: lang['ne_rabotaet_v_dannoy_sisteme'],
				name: 'Org_IsNotForSystem',
				width: 100,
				xtype: 'swcheckbox'
			},

			new Ext.TabPanel({
				activeTab: 0,
				border: false,
				enableTabScroll: true,
				id: win.id + '_' + 'OrgEditTabPanel',
				items: [{
					height: 270,
					labelWidth: 143,
					layout: 'form',
					style: 'padding: 2px',
					id: win.id + '_' + 'tab_data',
					title: lang['1_osnovnyie_atributyi'],
					items: [{
						autoHeight: true,
						title: lang['adres'],
						style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px',
						xtype: 'fieldset',
						items: [ new sw.Promed.TripleTriggerField ({
							enableKeyEvents: true,
							fieldLabel: lang['yuridicheskiy_adres'],
							listeners: {
								'keydown': function(inp, e) {
									if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
										if ( e.F4 == e.getKey() )
											inp.onTrigger1Click();
										if ( e.F2 == e.getKey() )
											inp.onTrigger2Click();
										if ( e.DELETE == e.getKey() && e.altKey)
											inp.onTrigger3Click();

										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.browserEvent.returnValue = false;
										e.returnValue = false;

										if ( Ext.isIE ) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
								},
								'keyup': function( inp, e ) {
									if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.browserEvent.returnValue = false;
										e.returnValue = false;

									if ( Ext.isIE ) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
								}
							},
							name: 'UAddress_AddressText',
							onTrigger2Click: function() {
								var base_form = this.OrgEditForm.getForm();

								if ( base_form.findField('UAddress_AddressText').disabled ) {
									return false;
								}

								base_form.findField('UAddress_Zip').setValue(base_form.findField('PAddress_Zip').getValue());
								base_form.findField('UKLCountry_id').setValue(base_form.findField('PKLCountry_id').getValue());
								base_form.findField('UKLRGN_id').setValue(base_form.findField('PKLRGN_id').getValue());
								base_form.findField('UKLSubRGN_id').setValue(base_form.findField('PKLSubRGN_id').getValue());
								base_form.findField('UKLCity_id').setValue(base_form.findField('PKLCity_id').getValue());
								base_form.findField('UKLTown_id').setValue(base_form.findField('PKLTown_id').getValue());
								base_form.findField('UKLStreet_id').setValue(base_form.findField('PKLStreet_id').getValue());
								base_form.findField('UAddress_House').setValue(base_form.findField('PAddress_House').getValue());
								base_form.findField('UAddress_Corpus').setValue(base_form.findField('PAddress_Corpus').getValue());
								base_form.findField('UAddress_Flat').setValue(base_form.findField('PAddress_Flat').getValue());
								base_form.findField('UAddress_Address').setValue(base_form.findField('PAddress_Address').getValue());
								base_form.findField('UAddress_AddressText').setValue(base_form.findField('PAddress_AddressText').getValue());
							}.createDelegate(this),
							onTrigger3Click: function() {
								var base_form = this.OrgEditForm.getForm();

								if ( base_form.findField('UAddress_AddressText').disabled ) {
									return false;
								}

								base_form.findField('UAddress_Zip').setValue('');
								base_form.findField('UKLCountry_id').setValue('');
								base_form.findField('UKLRGN_id').setValue('');
								base_form.findField('UKLSubRGN_id').setValue('');
								base_form.findField('UKLCity_id').setValue('');
								base_form.findField('UKLTown_id').setValue('');
								base_form.findField('UKLStreet_id').setValue('');
								base_form.findField('UAddress_House').setValue('');
								base_form.findField('UAddress_Corpus').setValue('');
								base_form.findField('UAddress_Flat').setValue('');
								base_form.findField('UAddress_Address').setValue('');
								base_form.findField('UAddress_AddressText').setValue('');
							}.createDelegate(this),
							onTrigger1Click: function() {
								var base_form = this.OrgEditForm.getForm();

								if ( base_form.findField('UAddress_AddressText').disabled ) {
									return false;
								}

								getWnd('swAddressEditWindow').show({
									fields: {
										Address_ZipEdit: base_form.findField('UAddress_Zip').getValue(),
										KLCountry_idEdit: base_form.findField('UKLCountry_id').getValue(),
										KLRgn_idEdit: base_form.findField('UKLRGN_id').getValue(),
										KLSubRGN_idEdit: base_form.findField('UKLSubRGN_id').getValue(),
										KLCity_idEdit: base_form.findField('UKLCity_id').getValue(),
										KLTown_idEdit: base_form.findField('UKLTown_id').getValue(),
										KLStreet_idEdit: base_form.findField('UKLStreet_id').getValue(),
										PersonSprTerrDop_idEdit: base_form.findField('UPersonSprTerrDop_id').getValue(),
										Address_HouseEdit: base_form.findField('UAddress_House').getValue(),
										Address_CorpusEdit: base_form.findField('UAddress_Corpus').getValue(),
										Address_FlatEdit: base_form.findField('UAddress_Flat').getValue(),
										Address_AddressEdit: base_form.findField('UAddress_Address').getValue()
									},
									callback: function(values) {
										base_form.findField('UAddress_Zip').setValue(values.Address_ZipEdit);
										base_form.findField('UKLCountry_id').setValue(values.KLCountry_idEdit);
										base_form.findField('UKLRGN_id').setValue(values.KLRgn_idEdit);
										base_form.findField('UKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
										base_form.findField('UKLCity_id').setValue(values.KLCity_idEdit);
										base_form.findField('UKLTown_id').setValue(values.KLTown_idEdit);
										base_form.findField('UKLStreet_id').setValue(values.KLStreet_idEdit);
										base_form.findField('UPersonSprTerrDop_id').setValue(values.PersonSprTerrDop_idEdit);
										base_form.findField('UAddress_House').setValue(values.Address_HouseEdit);
										base_form.findField('UAddress_Corpus').setValue(values.Address_CorpusEdit);
										base_form.findField('UAddress_Flat').setValue(values.Address_FlatEdit);
										base_form.findField('UAddress_Address').setValue(values.Address_AddressEdit);
										base_form.findField('UAddress_AddressText').setValue(values.Address_AddressEdit);
										base_form.findField('UAddress_AddressText').focus(true, 500);
									},
									onClose: function() {
										base_form.findField('UAddress_AddressText').focus(true, 500);
									}
								});
							}.createDelegate(this),
							readOnly: true,
							tabIndex: TABINDEX_OEW + 10,
							trigger1Class: 'x-form-search-trigger',
							trigger2Class: 'x-form-equil-trigger',
							trigger3Class: 'x-form-clear-trigger',
							width: 395
						}),
						new sw.Promed.TripleTriggerField ({
							//xtype: 'trigger',
							enableKeyEvents: true,
							fieldLabel: lang['fakticheskiy_adres'],
							listeners: {
								'keydown': function(inp, e) {
									if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
										if ( e.F4 == e.getKey() )
											inp.onTrigger1Click();
										if ( e.F2 == e.getKey() )
											inp.onTrigger2Click();
										if ( e.DELETE == e.getKey() && e.altKey)
											inp.onTrigger3Click();

										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.browserEvent.returnValue = false;
										e.returnValue = false;

										if ( Ext.isIE ) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
								},
								'keyup': function( inp, e ) {
									if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
										if ( e.browserEvent.stopPropagation )
											e.browserEvent.stopPropagation();
										else
											e.browserEvent.cancelBubble = true;

										if ( e.browserEvent.preventDefault )
											e.browserEvent.preventDefault();
										else
											e.browserEvent.returnValue = false;

										e.browserEvent.returnValue = false;
										e.returnValue = false;

										if ( Ext.isIE ) {
											e.browserEvent.keyCode = 0;
											e.browserEvent.which = 0;
										}
										return false;
									}
								}
							},
							name: 'PAddress_AddressText',
							onTrigger1Click: function() {
								var base_form = this.OrgEditForm.getForm();

								if ( base_form.findField('PAddress_AddressText').disabled ) {
									return false;
								}

								getWnd('swAddressEditWindow').show({
									fields: {
										Address_ZipEdit: base_form.findField('PAddress_Zip').getValue(),
										KLCountry_idEdit: base_form.findField('PKLCountry_id').getValue(),
										KLRgn_idEdit: base_form.findField('PKLRGN_id').getValue(),
										KLSubRGN_idEdit: base_form.findField('PKLSubRGN_id').getValue(),
										KLCity_idEdit: base_form.findField('PKLCity_id').getValue(),
										KLTown_idEdit: base_form.findField('PKLTown_id').getValue(),
										KLStreet_idEdit: base_form.findField('PKLStreet_id').getValue(),
										PersonSprTerrDop_idEdit:base_form.findField('UPersonSprTerrDop_id').getValue(),
										Address_HouseEdit: base_form.findField('PAddress_House').getValue(),
										Address_CorpusEdit: base_form.findField('PAddress_Corpus').getValue(),
										Address_FlatEdit: base_form.findField('PAddress_Flat').getValue(),
										Address_AddressEdit: base_form.findField('PAddress_Address').getValue()
									},
									callback: function(values) {
										base_form.findField('PAddress_Zip').setValue(values.Address_ZipEdit);
										base_form.findField('PKLCountry_id').setValue(values.KLCountry_idEdit);
										base_form.findField('PKLRGN_id').setValue(values.KLRgn_idEdit);
										base_form.findField('PKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
										base_form.findField('PKLCity_id').setValue(values.KLCity_idEdit);
										base_form.findField('PKLTown_id').setValue(values.KLTown_idEdit);
										base_form.findField('PPersonSprTerrDop_id').setValue(values.PersonSprTerrDop_idEdit);
										base_form.findField('PKLStreet_id').setValue(values.KLStreet_idEdit);
										base_form.findField('PAddress_House').setValue(values.Address_HouseEdit);
										base_form.findField('PAddress_Corpus').setValue(values.Address_CorpusEdit);
										base_form.findField('PAddress_Flat').setValue(values.Address_FlatEdit);
										base_form.findField('PAddress_Address').setValue(values.Address_AddressEdit);
										base_form.findField('PAddress_AddressText').setValue(values.Address_AddressEdit);
										base_form.findField('PAddress_AddressText').focus(true, 500);
									},
									onClose: function() {
										base_form.findField('PAddress_AddressText').focus(true, 500);
									}
								})
							}.createDelegate(this),
							onTrigger2Click: function() {
								var base_form = this.OrgEditForm.getForm();

								if ( base_form.findField('PAddress_AddressText').disabled ) {
									return false;
								}

								base_form.findField('PAddress_Zip').setValue(base_form.findField('UAddress_Zip').getValue());
								base_form.findField('PKLCountry_id').setValue(base_form.findField('UKLCountry_id').getValue());
								base_form.findField('PKLRGN_id').setValue(base_form.findField('UKLRGN_id').getValue());
								base_form.findField('PKLSubRGN_id').setValue(base_form.findField('UKLSubRGN_id').getValue());
								base_form.findField('PKLCity_id').setValue(base_form.findField('UKLCity_id').getValue());
								base_form.findField('PKLTown_id').setValue(base_form.findField('UKLTown_id').getValue());
								base_form.findField('PKLStreet_id').setValue(base_form.findField('UKLStreet_id').getValue());
								base_form.findField('PAddress_House').setValue(base_form.findField('UAddress_House').getValue());
								base_form.findField('PAddress_Corpus').setValue(base_form.findField('UAddress_Corpus').getValue());
								base_form.findField('PAddress_Flat').setValue(base_form.findField('UAddress_Flat').getValue());
								base_form.findField('PAddress_Address').setValue(base_form.findField('UAddress_Address').getValue());
								base_form.findField('PAddress_AddressText').setValue(base_form.findField('UAddress_AddressText').getValue());
							}.createDelegate(this),
							onTrigger3Click: function() {
								var base_form = this.OrgEditForm.getForm();

								if ( base_form.findField('PAddress_AddressText').disabled ) {
									return false;
								}

								base_form.findField('PAddress_Zip').setValue('');
								base_form.findField('PKLCountry_id').setValue('');
								base_form.findField('PKLRGN_id').setValue('');
								base_form.findField('PKLSubRGN_id').setValue('');
								base_form.findField('PKLCity_id').setValue('');
								base_form.findField('PKLTown_id').setValue('');
								base_form.findField('PKLStreet_id').setValue('');
								base_form.findField('PAddress_House').setValue('');
								base_form.findField('PAddress_Corpus').setValue('');
								base_form.findField('PAddress_Flat').setValue('');
								base_form.findField('PAddress_Address').setValue('');
								base_form.findField('PAddress_AddressText').setValue('');
							}.createDelegate(this),
							readOnly: true,
							tabIndex: TABINDEX_OEW + 11,
							trigger1Class: 'x-form-search-trigger',
							trigger2Class: 'x-form-equil-trigger',
							trigger3Class: 'x-form-clear-trigger',
							allowBlank: (getRegionNick() != 'vologda'),
							width: 395
						})]
					}, {
						border: false,
						layout: 'column',
						labelWidth: 60,
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['inn'],
								name: 'Org_INN',
								minLength: "10",
								tabIndex: TABINDEX_OEW + 12,
								width: 120,
								maskRe: new RegExp('[0-9]'),
								xtype: 'textfield'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['kpp'],
								name: 'Org_KPP',
								tabIndex: TABINDEX_OEW + 13,
								width: 120,
								maskRe: new RegExp('[0-9]'),
								xtype: 'textfield'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								allowLeadingZeroes: true,
								fieldLabel: lang['ogrn'],
								name: 'Org_OGRN',
								minLength: "13",
								tabIndex: TABINDEX_OEW + 14,
								width: 120,
								xtype: 'numberfield'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								allowLeadingZeroes: true,
								fieldLabel: lang['okato'],
								name: 'Org_OKATO',
								tabIndex: TABINDEX_OEW + 15,
								width: 120,
								xtype: 'numberfield'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						labelWidth: 60,
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['okfs'],
								hiddenName: 'Okfs_id',
								tabIndex: TABINDEX_OEW + 16,
								width: 305,
								xtype: 'swokfscombo'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['okopf'],
								hiddenName: 'Okopf_id',
								tabIndex: TABINDEX_OEW + 17,
								width: 305,
								xtype: 'swokopfcombo'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						labelWidth: 60,
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['okved'],
								hiddenName: 'Okved_id',
								tabIndex: TABINDEX_OEW + 18,
								width: 397,
								xtype: 'swokvedcombo'
							}, {
								allowBlank: true,
								allowLowLevelRecordsOnly: false,
								fieldLabel: lang['oktmo'],
								name: 'Oktmo_Name',
								object: 'Oktmo',
								selectionWindowParams: {
									height: 500,
									title: lang['kod_oktmo'],
									width: 600
								},
								showCodeMode: 2,
								tabIndex: TABINDEX_OEW + 19,
								useCodeOnly: true,
								useNameWithPath: false,
								valueFieldId: win.id + '_' + 'Oktmo_id',
								width: 397,
								xtype: 'swtreeselectionfield'
							}]
						},{	border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['okpo'],
								name: 'Org_OKPO',
								tabIndex: TABINDEX_OEW + 20,
								width: 213,
								xtype: 'textfield'

							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px',
						title: lang['kontaktyi'],
						xtype: 'fieldset',
						items: [{
							fieldLabel: lang['telefon'],
							name: 'Org_Phone',
							tabIndex: TABINDEX_OEW + 21,
							width: 397,
							xtype: 'textfield'
						}, {
							fieldLabel: 'E-mail',
							name: 'Org_Email',
							tabIndex: TABINDEX_OEW + 22,
							width: 397,
							xtype: 'textfield'
						}]
					}]
				}, {
					height: 250,
					labelWidth: 143,
					layout: 'fit',
					id: win.id + '_' + 'tab_serveterr',
					title: lang['2_territoriya_obslujivaniya'],
					items: [ win.OrgServiceTerrGrid ]
				}, {
					height: 250,
					labelWidth: 143,
					layout: 'fit',
					id: win.id + '_' + 'tab_OrgRSchet',
					title: '<u>3</u>. Расчётные счета',
					items: [ win.OrgRSchetGrid ]
				}, {
					height: 250,
					labelWidth: 143,
					layout: 'fit',
					id: win.id + '_' + 'tab_OrgHead',
					title: lang['4_kontaktnyie_litsa'],
					items: [ win.OrgHeadGrid ]
				}, {
					height: 250,
					labelWidth: 143,
					layout: 'fit',
					id: win.id + '_' + 'tab_OrgLicence',
					title: lang['5_litsenzii'],
					items: [ win.OrgLicenceGrid ]
				}, {
					height: 250,
					labelWidth: 143,
					layout: 'fit',
					id: win.id + '_' + 'tab_OrgFilial',
					title: lang['6_filialyi'],
					items: [ win.OrgFilialGrid ]
				}, {
					height: 250,
					labelWidth: 143,
					layout: 'form',
					id: win.id + '_' + 'tab_additional',
					style: 'padding: 2px',
					title: lang['7_dannyie'],
					items: [ win.cardPanel ]
				}, {
					height: 250,
					labelWidth: 143,
					layout: 'form',
					id: win.id + '_' + 'tab_outerISCodes',
					style: 'padding: 2px',
					labelWidth: 100,
					title: langs('Коды внешних ИС'),
					items: [{
						layout: 'form',
						id: win.id + '_' + 'Org_ONMSZCodeContainer',
						items: [{
							fieldLabel: langs('Код ОНМСЗ'),
							disabled: !haveArmType('superadmin'),
							name: 'Org_ONMSZCode',
							xtype: 'textfield'
						}]
					}]
				}],
				layoutOnTabChange: true,
				listeners: {
					'tabchange': function(panel, tab) {
						var els = tab.findByType('textfield', false);

						if (els == undefined || els == null)
							els = tab.findByType('combo', false);

						var el = els[0];

						if (el != undefined && el.focus)
							el.focus(true, 200);
							
						var grid = null;
						var params = {
							 limit: 100
							,start: 0
							,Org_id: win.Org_id
						}
						
						// прогружаем грид.
						switch (tab.id)
						{
							case win.id + '_' + 'tab_serveterr':
								grid = win.OrgServiceTerrGrid;
								break;
								
							case win.id + '_' + 'tab_OrgRSchet':
								grid = win.OrgRSchetGrid;
								break;

							case win.id + '_' + 'tab_OrgHead':
								grid = win.OrgHeadGrid;
								break;
								
							case win.id + '_' + 'tab_OrgLicence':
								grid = win.OrgLicenceGrid;
								break;
								
							case win.id + '_' + 'tab_OrgFilial':
								grid = win.OrgFilialGrid;
								break;
								
							default:
								return false;
								break;
						}

						if (tab.id != win.id + '_' + 'tab_serveterr' || win.action != 'add') {
							grid.loadData({
								params: params,
								globalFilters: params
							});
						}

						win.syncSize();
						win.syncShadow();
					}
				}
			})]
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'save16',
				id: win.id + '_' + 'OEF_SaveButton',
				tabIndex: TABINDEX_OEW + 60,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_OEW + 61),
			{
				iconCls: 'cancel16',
				id: win.id + '_' + 'OEF_CancelButton',
				handler: function() {
					this.ownerCt.hide();
				},
				tabIndex: TABINDEX_OEW + 62,
				onTabAction: function() {
					var base_form = win.OrgEditForm.getForm();
					base_form.findField('Org_Code').focus();
				},
				text: BTN_FRMCANCEL
			}],
			items: [ win.OrgEditForm ],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.J) {
						this.hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.C) {
						this.submit();
						return false;
					}
				},
				key: [ Ext.EventObject.J, Ext.EventObject.C ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swOrgEditWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'fit',
	listeners: {
		'hide': function() {
			this.onHide();

			if ( this.isWindowCopy == true ) {
				this.destroy();
			}
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	Org_id: null,
	checkFieldsEditAvailable: function(){

		var win = this,
			form = win.OrgEditForm,
			base_form = form.getForm(),
			OrgType_SysNick = base_form.findField('OrgType_id').getFieldValue('OrgType_SysNick'),
			Org_IsAccess = base_form.findField('Org_IsAccess').getValue(),
			fields_to_check =  [
				'Org_Code',
				'Org_begDate',
				'Org_Name',
				'Org_Nick',
				'Org_StickNick',
				'Org_rid',
				'Org_nid',
				'UAddress_AddressText',
				'PAddress_AddressText',
				'Okfs_id',
				'Okopf_id',
				'Org_INN',
				'Org_KPP',
				'Org_OGRN'
			];
		//Если у МО проставлен признак "Доступ в систему" и пользователь не суперадмин то не даём редактировать ряд полей
		fields_to_check.forEach(function(el){
			if (base_form.findField(el)){
				base_form.findField(el).setDisabled(Org_IsAccess === '2' && OrgType_SysNick === 'lpu' && !isSuperAdmin());
			}
		});
		if(OrgType_SysNick === 'lpu') {
			base_form.findField('Org_endDate').setDisabled(true);
			base_form.findField('Org_IsNotForSystem').setDisabled(false);
		} else {
			base_form.findField('Org_IsNotForSystem').setDisabled(true);
			base_form.findField('Org_IsNotForSystem').setValue(false);
		}
	},
	show: function() {
		sw.Promed.swOrgEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var form = win.OrgEditForm;
		var base_form = form.getForm();
		
		base_form.reset();
		win.OrgFilialGrid.removeAll({clearAll: true});
		win.OrgLicenceGrid.removeAll({clearAll: true});
		win.OrgHeadGrid.removeAll({clearAll: true});
		win.OrgRSchetGrid.removeAll({clearAll: true});
		win.OrgServiceTerrGrid.removeAll({clearAll: true});
		win.action = null;
		win.callback = Ext.emptyFn;
		win.onHide = Ext.emptyFn;
		win.mode = 'orgedit';
		win.allowEmptyUAddress = (getRegionNick() == 'vologda' ? '0' : '1');
		win.org_add = false;

		if ( arguments[0] ) {
			if ( arguments[0].action )
				win.action = arguments[0].action;

			if ( arguments[0].callback )
				win.callback = arguments[0].callback;

			if ( arguments[0].onHide )
				win.onHide = arguments[0].onHide;

			if ( arguments[0].Org_id ) {
				win.Org_id = arguments[0].Org_id;
			} else {
				win.Org_id = null;
			}

			if ( arguments[0].mode )
				win.mode = arguments[0].mode;

			if ( arguments[0].allowEmptyUAddress ) {
				win.allowEmptyUAddress = arguments[0].allowEmptyUAddress;
			}
			if ( arguments[0].org_add && getRegionNick() == 'vologda') {
				base_form.findField('Org_INN').setAllowBlank(false);
				base_form.findField('Org_OGRN').setAllowBlank(false);
			}
		}
		
		if ( isSuperAdmin() ) {
			win.mode = 'passport';
		}
		base_form.findField('UAddress_AddressText').setAllowBlank(true);
		base_form.findField('Org_begDate').setMinValue(undefined);
		base_form.findField('Org_begDate').setMaxValue(undefined);
		base_form.findField('Org_endDate').setMinValue(undefined);
		base_form.findField('Org_endDate').setMaxValue(undefined);

		base_form.setValues({
			Org_id: win.Org_id
		})

		//form.enable();
		win.buttons[0].enable();

		var tabPanel = win.findById(win.id + '_' + 'OrgEditTabPanel');
		
		// скрываем вкладку с дополнительными данными
		tabPanel.hideTabStripItem(win.id + '_' + 'tab_additional');
		
		// и вкладки "паспорта организации"
		tabPanel.hideTabStripItem(win.id + '_' + 'tab_OrgFilial');
		tabPanel.hideTabStripItem(win.id + '_' + 'tab_OrgLicence');
		tabPanel.hideTabStripItem(win.id + '_' + 'tab_OrgHead');
		tabPanel.hideTabStripItem(win.id + '_' + 'tab_OrgRSchet');
			
		tabPanel.setActiveTab(win.id + '_' + 'tab_additional');
		tabPanel.setActiveTab(win.id + '_' + 'tab_serveterr');
		tabPanel.setActiveTab(win.id + '_' + 'tab_data');

		if (win.mode == 'passport') {
			tabPanel.unhideTabStripItem(win.id + '_' + 'tab_OrgFilial');
			tabPanel.unhideTabStripItem(win.id + '_' + 'tab_OrgLicence');
			tabPanel.unhideTabStripItem(win.id + '_' + 'tab_OrgHead');
			tabPanel.unhideTabStripItem(win.id + '_' + 'tab_OrgRSchet');
		}
		
		if ( win.action ) {
			switch ( win.action ) {
				case 'add':
					win.setTitle(lang['organizatsiya_dobavlenie']);
					win.enableEdit(true);
					win.checkOrgTypeAdditional();
					win.checkOrgRid();
					
					//Фокусируем на поле Наименование
					base_form.findField('Org_Name').focus(100, true);

					var Mask = new Ext.LoadMask(Ext.get('OrgEditWindow'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..." });
					Mask.show();

					base_form.findField('OrgType_id').getStore().clearFilter();
					base_form.findField('OrgType_id').clearBaseFilter();

					// исключить значение МО для не админов Екб
					if(
						getRegionNick() == 'ekb' 
						&& !(isLpuAdmin() || isSuperAdmin() || isUserGroup('LpuAdmin')) 
						&& base_form.findField('OrgType_id').getStore().getById(11)
					) {
						base_form.findField('OrgType_id').getStore().remove(base_form.findField('OrgType_id').getStore().getById(11));
					}

					Ext.Ajax.request({
						callback: function(opt, success, resp) {
							Mask.hide();

							var response_obj = Ext.util.JSON.decode(resp.responseText);

							if (response_obj.Org_Code != '') {
								base_form.findField('Org_Code').setValue(response_obj.Org_Code);
							}
						},
						url: '/?c=Org&m=getMaxOrgCode'
					});
				break;

				case 'edit':
				case 'view':
					win.enableEdit(win.action == 'edit');

					if (win.action == 'edit') {
						win.setTitle(lang['organizatsiya_redaktirovanie']);
						//Фокусируем на поле Код
						base_form.findField('Org_Code').focus(100, true);
					} else {
						win.setTitle(lang['organizatsiya_prosmotr']);
					}

					win.OrgServiceTerrGrid.setReadOnly(win.action == 'view');
					win.OrgRSchetGrid.setReadOnly(win.action == 'view');
					win.OrgHeadGrid.setReadOnly(win.action == 'view');
					win.OrgLicenceGrid.setReadOnly(win.action == 'view');
					win.OrgFilialGrid.setReadOnly(win.action == 'view');
					
					var Mask = new Ext.LoadMask(Ext.get('OrgEditWindow'), { msg: "Пожалуйста, подождите, идет загрузка данных формы..."} );
					Mask.show();

					base_form.load({
						failure: function() {
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() { win.hide(); } );
						},
						params: {
							Org_id: win.Org_id
						},
						success: function() {
							var tabPanel = win.findById(win.id + '_' + 'OrgEditTabPanel');

							Mask.hide();
							if (Ext.isEmpty(base_form.findField('OrgType_id').getValue())) {
								/*base_form.findField('OrgType_id').setBaseFilter(function(rec){
									return rec.get('OrgType_Code').inlist(['7', '8', '9', '10']);
								});*/
							} else {
								base_form.findField('OrgType_id').disable();
								base_form.findField('OrgType_id').getStore().clearFilter();
								base_form.findField('OrgType_id').clearBaseFilter();
							}
							base_form.findField('OrgType_id').setValue(base_form.findField('OrgType_id').getValue());

							win.checkFieldsEditAvailable();
							
							var orgType = base_form.findField('OrgType_SysNick').getValue();
							if( orgType.inlist(['touz', 'farm', 'reg_dlo', 'lpu', 'contractor']) ) {
								tabPanel.unhideTabStripItem( win.id + '_' + 'tab_outerISCodes');

								if(getRegionNick() != 'kz' && orgType.inlist(['touz', 'lpu'])) {
									Ext.getCmp(win.id + '_' + 'Org_ONMSZCodeContainer').show();
								} else {
									Ext.getCmp(win.id + '_' + 'Org_ONMSZCodeContainer').hide();
								}
							} else {
								tabPanel.hideTabStripItem( win.id + '_' + 'tab_outerISCodes');
							}

							if ( base_form.findField('Org_rid').getValue() ) {
								var Org_rid = base_form.findField('Org_rid').getValue();
								base_form.findField('Org_rid').getStore().load({
									params: {
										Object:'Org',
										Org_id: Org_rid,
										Org_Name:''
									},
									callback: function()
									{
										base_form.findField('Org_rid').setValue(Org_rid);
									}
								});
							}
							if ( base_form.findField('Org_nid').getValue() ) {
								var Org_nid = base_form.findField('Org_nid').getValue();
								base_form.findField('Org_nid').getStore().load({
									params: {
										Object:'Org',
										Org_id: Org_nid,
										Org_Name:''
									},
									callback: function()
									{
										base_form.findField('Org_nid').setValue(Org_nid);
									}
								});
							}
							win.checkOrgTypeAdditional();
							win.checkOrgRid();

							if ( !Ext.isEmpty(base_form.findField('Oktmo_id').getValue()) && base_form.findField('Oktmo_Name').useNameWithPath == true ) {
								// Тянем полное наименование ОКТМО
								base_form.findField('Oktmo_Name').setNameWithPath();
							}

							//win.setAddress();
						},
						url: '/?c=Org&m=getOrgData'
					});
				break;
			}
		}
	},

	submit: function(options)
	{
		var form = this.OrgEditForm.getForm();

		if ( !form.isValid() ) {
			let invalidEl = this.OrgEditForm.getFirstInvalidEl();

			// sw.swMsg.alert('Ошибка заполнения формы', 'Проверьте правильность заполнения полей формы.');
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					invalidEl.focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,

				msg: (invalidEl.name == 'Org_Name' ?
					lang['org_name_too_short'] :
					'Не верно заполнено поле "'+invalidEl.fieldLabel+'"'),

				title: lang['oshibka_zapolneniya_formyi']
			});
			return false;
		}

// Для Вологды проверяем дубликат организации по названию и типу:
		if (getRegionNick() == 'vologda')
		{
			Ext.Ajax.request({
				url: '/?c=Org&m=getOrgView',

				params:
				{
					Name: form.findField('Org_Name').getValue(),
					Type: form.findField('OrgType_id').getValue()
				},

				callback: _onLoad_orgCheck,
				scope: this
			});

			return (false);
		}

		return (this._doSubmit(options));

		function _onLoad_orgCheck(opt, success, resp)
		{
			if (success && resp.status == 200)  // OK
			{
				let data;

				try
				{
					data = JSON.parse(resp.responseText).data;
				}
				catch (e)
				{
					data = null;
				}

				if (data && data.length > 0 &&
					(data.length > 1 ||
						data[0].Org_id != form.findField('Org_id').getValue()))
				{
					sw.swMsg.show({
						title: lang['oshibka'],
						msg: lang['org_duplicate'],
						icon: Ext.Msg.WARNING,
						buttons: Ext.Msg.OK
					});

					return;
				}
			}

			this._doSubmit(options);
		}
	},

	_doSubmit: function(options)
	{
		var win = this;
		var form = this.OrgEditForm.getForm();

		var params = {},
			fields_to_check =  [
				'Org_Code',
				'Org_begDate',
				'Org_endDate',
				'Org_Name',
				'Org_Nick',
				'Org_StickNick',
				'OrgType_id',
				'Org_rid',
				'Org_nid',
				'UAddress_AddressText',
				'PAddress_AddressText',
				'Okfs_id',
				'Okopf_id',
				'Org_INN',
				'Org_KPP',
				'Org_OGRN'
			];
		if(getGlobalOptions().isMinZdrav
			||getGlobalOptions().CurMedServiceType_SysNick=="mekllo"
			||getGlobalOptions().CurMedServiceType_SysNick=="minzdravdlo"){
			params.isminzdrav = true;
		}

		//обработка задизейбленых в checkFieldsEditAvailable полей
		fields_to_check.forEach(function(el){
			if (form.findField(el) && form.findField(el).disabled && form.findField(el).getValue()){
				if (el.inlist(['Org_endDate', 'Org_begDate'])){
					params[el] = Ext.util.Format.date(form.findField(el).getValue(), 'd.m.Y');
				} else {
					params[el] = form.findField(el).getValue();
				}
			}
		});

		if (form.findField('OrgType_id').disabled) {
			params.OrgType_id = form.findField('OrgType_id').getValue();
		}
		
		if (form.findField('KLCountry_id').disabled) {
			params.KLCountry_id = form.findField('KLCountry_id').getValue();
		}
		if (form.findField('KLRGN_id').disabled) {
			params.KLRGN_id = form.findField('KLRGN_id').getValue();
		}
		if (form.findField('KLSubRGN_id').disabled) {
			params.KLSubRGN_id = form.findField('KLSubRGN_id').getValue();
		}
		if (form.findField('KLCity_id').disabled) {
			params.KLCity_id = form.findField('KLCity_id').getValue();
		}
		if (form.findField('KLTown_id').disabled) {
			params.KLTown_id = form.findField('KLTown_id').getValue();
		}
		if (form.findField('OrgStac_Code').disabled) {
			params.OrgStac_Code = form.findField('OrgStac_Code').getValue();
		}
		
		if ( options&&options.check_double_cancel && options.check_double_cancel == true )
		{
			if ( options.check_code == '777' )
				params.check_double_inn_cancel = 2;
			if ( options.check_code == '888' )
				params.check_double_ogrn_cancel = 2;
			if ( ( options.check_code == '889' ) || ( options.check_code == '778' ) )
			{
				params.check_double_inn_cancel = 2;
				params.check_double_ogrn_cancel = 2;
			}
		}
		
		form.submit({
			failure: function (form, action) {
				if (action.result.Error_Code)
				{					
					if ( action.result['Error_Code'] && ( action.result.Error_Code == '777' || action.result.Error_Code == '888' || action.result.Error_Code == '778' || action.result.Error_Code == '889' ) )
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if (buttonId == 'yes')
								{
									Ext.getCmp('OrgEditWindow').submit({check_double_cancel:true, check_code:action.result.Error_Code});
								}
								else
								{
									form.findField('Org_Code').focus(true, 200);
								}
							},
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Error_Msg + lang['prodoljit_sohranenie'],
							title: lang['vopros']
						});
					else
					{
						sw.swMsg.alert("Ошибка", action.result.Error_Msg);
						form.findField('Org_Code').focus(true, 200);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(form, action) {
				var OrgType_SysNick = form.findField('OrgType_id').getFieldValue('OrgType_SysNick');
				
				if (
					(!action.result.Org_id)
					|| ( OrgType_SysNick == 'anatom' && !action.result.OrgAnatom_id )
					|| ( OrgType_SysNick == 'dep' && !action.result.OrgDep_id )
					|| ( OrgType_SysNick == 'farm' && !action.result.OrgFarmacy_id )
					|| ( OrgType_SysNick == 'smo' && !action.result.OrgSMO_id )
				) {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_organizatsii_proizoshla_oshibka']);
					return false;
				}
				
				form.findField('Org_id').setValue(action.result.Org_id);
				win.Org_id = action.result.Org_id;
				win.action = 'edit';

				switch (OrgType_SysNick) {
					case 'anatom':
						form.findField('OrgAnatom_id').setValue(action.result.OrgAnatom_id);
						break;

					case 'dep':
						form.findField('OrgDep_id').setValue(action.result.OrgDep_id);
						break;

					case 'farm':
						form.findField('OrgFarmacy_id').setValue(action.result.OrgFarmacy_id);
						break;

					case 'smo':
						form.findField('OrgSMO_id').setValue(action.result.OrgSMO_id);
						break;
				}

				if (options&&options.callback) {
					options.callback();
					return true;
				}

				this.hide();
				
				var data = {
					OrgData: {
						Org_id: action.result.Org_id
					}
				};

				switch ( OrgType_SysNick ) {
					case 'anatom':
						data.OrgData.OrgAnatom_id = action.result.OrgAnatom_id;
						break;

					case 'dep':
						data.OrgData.OrgDep_id = action.result.OrgDep_id;
						break;

					case 'farm':
						data.OrgData.OrgFarmacy_ACode = form.findField('OrgFarmacy_ACode').getRawValue();
						data.OrgData.OrgFarmacy_Address = form.findField('PAddress_AddressText').getRawValue();
						data.OrgData.OrgFarmacy_HowGo = form.findField('OrgFarmacy_HowGo').getRawValue();
						data.OrgData.OrgFarmacy_id = action.result.OrgFarmacy_id;
						data.OrgData.OrgFarmacy_IsDisabled = (form.findField('OrgFarmacy_IsEnabled').getValue() == 1 ? 'true' : 'false');
						data.OrgData.OrgFarmacy_IsFedLgot = (form.findField('OrgFarmacy_IsFedLgot').getValue() == 1 ? 'false' : 'true');
						data.OrgData.OrgFarmacy_IsNozLgot = (form.findField('OrgFarmacy_IsNozLgot').getValue() == 1 ? 'false' : 'true');
						data.OrgData.OrgFarmacy_IsNarko = (form.findField('OrgFarmacy_IsNarko').getValue() == 1 ? 'false' : 'true');
						data.OrgData.OrgFarmacy_IsRegLgot = (form.findField('OrgFarmacy_IsRegLgot').getValue() == 1 ? 'false' : 'true');
						data.OrgData.OrgFarmacy_Name = form.findField('Org_Name').getRawValue();
						data.OrgData.OrgFarmacy_Nick = form.findField('Org_Nick').getRawValue();
						data.OrgData.OrgFarmacy_Phone = form.findField('Org_Phone').getRawValue();
						break;

					case 'smo':
						data.OrgData.OrgSMO_id = action.result.OrgSMO_id;
						break;
				}

				this.callback(data);
			}.createDelegate(this)
		});
	},
	enablePanelEdit: function(o, bool) {
		if ((typeof o == 'object') && o.items && o.items.items) {
			o = o.items.items;
		}
		if (o && o.length && o.length>0) {
			for (var i = 0, len = o.length; i < len; i++) {
				if (o[i])
					if ((o[i].xtype && (o[i].xtype=='fieldset' || o[i].xtype=='panel' || o[i].xtype=='tabpanel')) || (o[i].layout/* && (o[i].layout=='form')*/)) { // TO-DO: Скорее всего здесь надо будет поправить
						// уровень ниже
						this.enablePanelEdit(o[i],bool);
					}
					else if (typeof o[i].getTopToolbar != 'function') {
						if (bool) {
							o[i].enable();
						} else {
							o[i].disable();
						}
					}
			};
		}
	},
	checkOrgTypeAdditional: function() {
		var win = this;
		var base_form = win.OrgEditForm.getForm();
		var OrgType_SysNick = base_form.findField('OrgType_id').getFieldValue('OrgType_SysNick');
		var OrgType_Name = base_form.findField('OrgType_id').getFieldValue('OrgType_Name');
		var tabPanel = win.findById(win.id + '_' + 'OrgEditTabPanel');
		var tabTitle = '';

		tabPanel.hideTabStripItem(win.id + '_' + 'tab_additional');
		
		if (win.mode == 'passport') {
			tabTitle = lang['7_dannyie'] + OrgType_Name;
		} else {
			tabTitle = lang['2_dannyie'] + OrgType_Name;
		}
		
		win.findById(win.id + '_' + 'OrgEditTabPanel').getItem(win.id + '_' + 'tab_additional').setTitle(tabTitle);
		win.enablePanelEdit(win.additionalFarm, false);
		win.enablePanelEdit(win.additionalBank, false);
		win.enablePanelEdit(win.additionalSmo, false);

		if(getRegionNick() != 'vologda') base_form.findField('Org_OGRN').setAllowBlank(true);

		switch (OrgType_SysNick) {
			case 'farm':
				tabPanel.unhideTabStripItem(win.id + '_' + 'tab_additional');
				var isMekllo = (sw.Promed.MedStaffFactByUser.store.findBy(function(rec) { return rec.get('ARMType') == 'mekllo'; }) > -1);
				var isMerch = (sw.Promed.MedStaffFactByUser.store.findBy(function(rec) { return rec.get('ARMType') == 'merch'; }) > -1);
				if (win.action != 'view' && ((isOrgAdmin() && getGlobalOptions().org_id == win.Org_id) || isSuperAdmin() || getGlobalOptions().isMinZdrav) || isMekllo || isMerch) {
					win.enablePanelEdit(win.additionalFarm, true);
				}
				base_form.findField('Org_OGRN').setAllowBlank(false);
				win.cardPanel.layout.setActiveItem(0);
			break;
			
			case 'bank':
				tabPanel.unhideTabStripItem(win.id + '_' + 'tab_additional');
				if (win.action != 'view') {
					win.enablePanelEdit(win.additionalBank, true);
				}
				win.cardPanel.layout.setActiveItem(1);
			break;
			
			case 'smo':
				tabPanel.unhideTabStripItem(win.id + '_' + 'tab_additional');
				if (win.action != 'view') {
					win.enablePanelEdit(win.additionalSmo, true);
				}
				win.cardPanel.layout.setActiveItem(2);
			break;
		}

		if (getRegionNick() == 'ufa') {
			base_form.findField('UAddress_AddressText').setAllowBlank(true);
			base_form.findField('PAddress_AddressText').setAllowBlank(getRegionNick() != 'vologda');
			base_form.findField('Org_INN').setAllowBlank(true);
			base_form.findField('Org_KPP').setAllowBlank(true);
			if (OrgType_SysNick && OrgType_SysNick.inlist(['preschool', 'secschool', 'proschool', 'highschool'])) {
				base_form.findField('UAddress_AddressText').setAllowBlank(false);
				base_form.findField('PAddress_AddressText').setAllowBlank(false);
				base_form.findField('Org_INN').setAllowBlank(false);
				base_form.findField('Org_KPP').setAllowBlank(false);
			}
		}

		if(OrgType_SysNick === 'lpu') {
			base_form.findField('Org_IsNotForSystem').setDisabled(false);


		} else {
			base_form.findField('Org_IsNotForSystem').setDisabled(true);
			base_form.findField('Org_IsNotForSystem').setValue(false);
		}
		base_form.findField('Org_f003mcod').setContainerVisible(OrgType_SysNick === 'lpu');

		if(win.allowEmptyUAddress == '0')
			base_form.findField('UAddress_AddressText').setAllowBlank(false);
	},
	checkOrgRid: function() {
		var win = this;
		var base_form = win.OrgEditForm.getForm();
		var Org_endDate = base_form.findField('Org_endDate').getValue();
		
		base_form.findField('Org_rid').setDisabled(Ext.isEmpty(Org_endDate));
	},
	title: lang['redaktirovanie_organizatsiya'],
	width: 800
});
