/**
* swEvnUslugaOrderEditWindow - форма ввода комплексных услуг
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      декабрь 2010
* @prefix       euoew
* @comment      
*
*
* @input        params (object) 
*/
/*NO PARSE JSON*/

sw.Promed.swEvnUslugaOrderEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoHeight: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	codeRefresh: true,
	objectName: 'swEvnUslugaOrderEditWindow',
	objectSrc: '/jscore/Forms/Parka/swEvnUslugaOrderEditWindow.js',
	title: WND_EUOEW,
	split: true,
	width: 700,
	layout: 'form',
	id: 'EvnUslugaOrderEditWindow',
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		},
		beforeshow: function()
		{
			//
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	params: {
		Person_id: null,
		Sex_id: null,
		RaceType_id: null,
		PersonHeight_Height: null,
		PersonHeight_setDT: null,
		PersonWeight_WeightText: null,
		PersonWeight_setDT: null
	},
	listeners: {
		hide: function (wnd) {
			for (var i in wnd.params) wnd.params[i] = null;
		}
	},
	hiddenFields: [
		'RaceType_id',
		'PersonHeight_Height',
		'PersonHeight_setDT',
		'PersonWeight_WeightText',
		'PersonWeight_setDT'
	],
	/* Проверка которая выполняется до сохранения данных
	*/
	doSave: function()  {
		var form = this.EvnUslugaForm;
		if (!form.getForm().isValid()) 
		{
			sw.swMsg.show(
			{
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
		frm = form.getForm();
		/* проеерки, если не понадобятся то надо будет убрать 
		if ((frm.findField('EvnUslugaType_id').getValue()==3) && (frm.findField('EvnUsluga_LowerLimit').getValue()>frm.findField('EvnUsluga_UpperLimit').getValue()))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					frm.findField('EvnUsluga_LowerLimit').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['nijniy_limit_ne_mojet_byit_bolshe_verhnego'],
				title: lang['oshibka_sohraneniya']
			});
			return false;
		};
		if ((frm.findField('EvnUslugaGroup_id').getValue()>0) && (frm.findField('EvnUsluga_LowerAge').getValue()>frm.findField('EvnUsluga_UpperAge').getValue()))
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					frm.findField('EvnUsluga_LowerAge').focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['minimalnyiy_vozrast_ne_mojet_byit_bolshe_maksimalnogo'],
				title: lang['oshibka_sohraneniya']
			});
			return false;
		};
		*/
		if (this.mode == 'nosave') {
			this.returnData();
		} else {
			this.submit();
		}
		return true;
	},
	/**
	 * Возвращает список идешников выбранных услуг
	 */
	getChecked: function() {
		var nodes = this.uslugaTree.getChecked();
		var checked = [];
		for (i=0; i < nodes.length; i++)
		{
			//checked.push(nodes[i].attributes.id);
			if (nodes[i].childNodes.length == 0) {
				checked.push(nodes[i].attributes.id);
			}
		}
		return checked;
	},
	/**
	 * Возвращает данные введенные-выбранные в форме без сохранения объекта в БД
	 */
	returnData: function()
	{
		var w = this;
		var form = this.EvnUslugaForm;
		form.ownerCt.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		var params = {};
		var checked = w.getChecked();
		params = form.getForm().getValues();
		params.Lpu_uid = form.Lpu_uid;
		params.UslugaComplex_id = form.getForm().findField('UslugaComplex_id').getValue();
		params.checked = Ext.util.JSON.encode(checked);
		// пункт забора
		params.MedService_pzNick = form.getForm().findField('MedService_pzid').getRawValue();
		params.MedService_pzid = form.getForm().findField('MedService_pzid').getValue();
		// выбранная услуга
		params.UslugaComplex_Name = form.getForm().findField('UslugaComplex_id').getRawValue();
		if (w.fromRecordMaster) {
			// для мастера записи своя логика
			params.UslugaComplexMedService_id = w.UslugaComplexMedService_id;
		} else {
			params.UslugaComplexMedService_id = form.getForm().findField('UslugaComplex_id').getFieldValue('UslugaComplexMedService_id');
		}

		// #183123 PersonDetailEvnDirection
		params.HIVContingentTypeFRMIS_id = form.getForm().findField('HIVContingentTypeFRMIS_id').getValue();
		params.CovidContingentType_id = form.getForm().findField('CovidContingentType_id').getValue();
		params.HormonalPhaseType_id = form.getForm().findField('HormonalPhaseType_id').getValue();

		if (!Ext.isEmpty(params.MedService_pzid) && w.pzmUslugaComplex_MedService_id) {
			params.UslugaComplexMedService_id = w.pzmUslugaComplex_MedService_id;
		}
		// выбранную услугу в службе и профиль просто протаскиваем через форму
		params.LpuSectionProfile_id = this.LpuSectionProfile_id;
		params.Resource_id = this.Resource_id;
		w.callback(w.owner, null, params);
		w.hide();
	},
	submit: function() 
	{
		var form = this.EvnUslugaForm;
		var win = this;
		form.ownerCt.getLoadMask(lang['podojdite_sohranyaetsya_zapis']).show();
		var params = {};
		var checked = win.getChecked();
		params.UslugaComplex_id = form.getForm().findField('UslugaComplex_id').getValue();
		params.Lpu_uid = form.Lpu_uid;
		params.checked = Ext.util.JSON.encode(checked);
		form.getForm().submit(
		{
			params: params,
			failure: function(result_form, action) 
			{
				form.ownerCt.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				form.ownerCt.getLoadMask().hide();
				if (action.result) 
				{
					if (action.result.EvnUsluga_id)
					{
						var noNeedTimetableApply = false;
						
						if (action.result.TimetableApplied) {
							noNeedTimetableApply = true;
						}
						var records = form.getForm().getValues();
						records['EvnUsluga_id'] = action.result.EvnUsluga_id;
						win.callback(win.owner, action.result.EvnUsluga_id, records, (win.action=='add'), noNeedTimetableApply) //, form.getForm().getValues(), (form.ownerCt.action=='add'));
						win.hide();
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: lang['pri_vyipolnenii_operatsii_sohraneniya_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje'],
							title: lang['oshibka']
						});
					}
				}
			}
		});
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.EvnUslugaForm.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
		if (form.buttons[0].hidden && !d)
			form.buttons[0].setVisible(true);
	},
	setPanelVisible: function(panel,flag)
	{
		this.findById(panel).setVisible(flag);
		this.syncSize();
	},
	getDate: function()
	{
		this.getLoadMask(lang['opredelenie_tekuschego_vremeni']).show();
		getCurrentDateTime({
			callback: function(r) 
			{
				if (r.success) {this.loadLpuSection(r.date);}
				this.getLoadMask().hide();
			}.createDelegate(this)
		});
	},
	/** Функция относительно универсальной загрузки справочников выбор в которых осуществляется при вводе букв (цифр)
	 * Пример загрузки Usluga:
	 * loadSpr('Usluga_id', { where: "where UslugaType_id = 2 and Usluga_id = " + Usluga_id });
	 */
	loadSpr: function(field_name, params, callback)
	{
		var bf = this.EvnUslugaForm.getForm();
		var combo = bf.findField(field_name);
		var value = combo.getValue();
		combo.getStore().removeAll();
		combo.getStore().load(
		{
			callback: function() 
			{
				combo.getStore().each(function(record) 
				{
					if (record.data[field_name] == value)
					{
						combo.setValue(value);
						combo.fireEvent('select', combo, record, 0);
					}
				});
				if (callback)
				{
					callback();
				}
			},
			params: params 
		});
	},
	/** Функция устанавливает имя и код услуги из выбранных полей справочников (если поля еще на заполнены)
	 * Пример вызова:
	 * loadSpr('Usluga_id', { where: "where UslugaType_id = 2 and Usluga_id = " + Usluga_id });
	 */
	setUslugaData: function(code, name)
	{
		var bf = this.EvnUslugaForm.getForm();
		
		if (code && bf.findField('EvnUsluga_Code').getValue().length==0)
		{
			bf.findField('EvnUsluga_Code').setValue(code);
		}
		if (name && bf.findField('EvnUsluga_Name').getValue().length==0)
		{
			bf.findField('EvnUsluga_Name').setValue(name);
		}
	},
	
	show: function() 
	{
		sw.Promed.swEvnUslugaOrderEditWindow.superclass.show.apply(this, arguments);
		var form = this;
		if (!arguments[0]) 
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы "'+form.title+'".<br/>Не указаны нужные входные параметры.',
				title: lang['oshibka']
			});
		}
		var frm = form.EvnUslugaForm.getForm();
		frm.reset();
		/*
		frm.findField('EvnUsluga_isGenXml').fireEvent('check', frm.findField('EvnUsluga_isGenXml'), true);
		*/
		var rootnode = this.uslugaTree.getRootNode();
		if (rootnode.isExpanded()) {
			frm.findField('UslugaComplex_id').fireEvent('select', frm.findField('UslugaComplex_id'), null);
		}
		form.focus();
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		if (arguments[0].EvnUsluga_id) 
			form.EvnUsluga_id = arguments[0].EvnUsluga_id;
		else 
			form.EvnUsluga_id = null;
		
		if (arguments[0].LpuSection_uid) 
			form.LpuSection_uid = arguments[0].LpuSection_uid;
		else 
			form.LpuSection_uid = null;
			
		if (arguments[0].Lpu_uid) 
			form.Lpu_uid = arguments[0].Lpu_uid;
		else 
			form.Lpu_uid = null;

		if (arguments[0].fromRecordMaster)
			form.fromRecordMaster = arguments[0].fromRecordMaster;
		else
			form.fromRecordMaster = false;
		
		
		if (arguments[0].MedService_id) 
			form.MedService_id = arguments[0].MedService_id;
		else 
			form.MedService_id = null;

		if (arguments[0].Resource_id)
			form.Resource_id = arguments[0].Resource_id;
		else
			form.Resource_id = null;
		
		if (arguments[0].EvnDirection_IsReceive)
			form.EvnDirection_IsReceive = arguments[0].EvnDirection_IsReceive;
		else
			form.EvnDirection_IsReceive = null;
		
		if (arguments[0].callback) 
		{
			form.callback = arguments[0].callback;
		}
		if (arguments[0].owner) 
		{
			form.owner = arguments[0].owner;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].action) 
		{
			form.action = arguments[0].action;
		}
		if (arguments[0].time_table) 
		{
			form.time_table = arguments[0].time_table;
		}
		else 
		{
			if ((form.EvnUsluga_id) && (form.EvnUsluga_id>0))
				form.action = "edit";
			else 
				form.action = "add";
		}

		form.Person_id = arguments[0].Person_id || null;
		form.UslugaComplex_id = arguments[0].UslugaComplex_id || null;

		form.EvnPrescr_id = arguments[0].EvnPrescr_id || null;

		if (arguments[0].MedServiceType_SysNick) {
			// если служба в которую записывают - это пункт забора, то показывать компонент выбора пункта забора не надо
            //frm.findField('MedService_pzid').setContainerVisible((arguments[0].MedServiceType_SysNick != 'pzm'));
            //Комбо «Пункт забора» отображать в форме заказа только при заказе лабораторно-диагностических услуг #21653
            frm.findField('MedService_pzid').setContainerVisible((arguments[0].MedServiceType_SysNick == 'lab'));
			// если служба в которую записывают - это лаборатория, то надо будет отфильтровать пункты забора по ней
			frm.findField('MedService_pzid').params.MedService_lid = (arguments[0].MedServiceType_SysNick == 'lab')?form.MedService_id:null;
			frm.findField('MedService_pzid').getStore().removeAll();
			frm.findField('MedService_pzid').getStore().load();
			if(getRegionNick()=='ekb') {
				frm.findField('MedService_pzid').setDisabled(true);
				frm.findField('MedService_pzid').getStore().addListener( 'load', function( thisStore, records, options ) {
					Ext.pzid = records;
					Ext.tstore = thisStore;
					if(thisStore.getCount()==1) {
						frm.findField('MedService_pzid').setValue(thisStore.getAt(0).id);
					} else if(thisStore.getCount()==2 && thisStore.getAt(0).id=="") {
						frm.findField('MedService_pzid').setValue(thisStore.getAt(1).id);
					}
				});
			}
			form.syncSize();
			//frm.syncShadow();
		}
		// еще две переменные просто протаскиваем через эту форму
		this.UslugaComplexMedService_id = (arguments[0].UslugaComplexMedService_id)?arguments[0].UslugaComplexMedService_id:null;
		this.LpuSectionProfile_id = (arguments[0].LpuSectionProfile_id)?arguments[0].LpuSectionProfile_id:null;
		// назначенная услуга
		this.UslugaComplex_prescid = (arguments[0].UslugaComplex_prescid)?arguments[0].UslugaComplex_prescid:null;

		// проверяем в каком режиме открыли форму
		this.mode = (arguments[0].mode && arguments[0].mode == 'nosave')?arguments[0].mode:'';

		frm.setValues(arguments[0]);
		form.getLoadMask(LOAD_WAIT).show();
		var combo = frm.findField('UslugaComplex_id');
		combo.getStore().baseParams = {level: 0};

		var CovidContingentField = this.formPanel.getForm().findField('CovidContingentType_id');
		var HIVContingentField = this.formPanel.getForm().findField('HIVContingentTypeFRMIS_id');
		var HormonalPhaseField = this.formPanel.getForm().findField('HormonalPhaseType_id');
		CovidContingentField.hideContainer();
		CovidContingentField.disable();
		HIVContingentField.hideContainer();
		HIVContingentField.disable();
		HormonalPhaseField.hideContainer();
		HormonalPhaseField.disable();

		switch (form.action)
		{
			case 'add':
				form.setTitle(WND_EUOEW_ADD);
				var params = {Lpu_uid: form.Lpu_uid};
				params.medServiceComplexOnly = 1; // тесты не нужны.
				combo.getStore().baseParams['medServiceComplexOnly'] = 1;
				if (form.MedService_id) {
					params['MedService_id'] = form.MedService_id;
					combo.getStore().baseParams['MedService_id'] = form.MedService_id;
				} else {
					params['LpuSection_id'] = form.LpuSection_uid;
					combo.getStore().baseParams['LpuSection_id'] = form.MedService_id;
				}
				if (form.UslugaComplex_prescid && (Ext.isEmpty(combo.getValue()))) { // если есть услуга из назначения и услуга из расписания не выбрана
					params['UslugaComplex_prescid'] = form.UslugaComplex_prescid;
					combo.getStore().baseParams['UslugaComplex_prescid'] = form.UslugaComplex_prescid; // то передаем на сервер фильтр по услуге назначения
				}
				if (arguments[0].MedServiceType_SysNick == 'prock') {
					// Услуги должны иметь атрибут 'manproc'
					// Уфа: только ГОСТ-2011 
					// Пермь: только ТФОМС 
					// Остальные: ничего
					var uslugacategorylist = ['nothing'];
					switch ( getRegionNick() ) {
						case 'kz':
							uslugacategorylist.push('classmedus');
						break;

						case 'perm':
							uslugacategorylist.push('tfoms');
						break;

						case 'ufa':
							uslugacategorylist.push('gost2011');
						break;
					}
					combo.getStore().baseParams['uslugaCategoryList'] = Ext.util.JSON.encode(uslugacategorylist);
					combo.getStore().baseParams['allowedUslugaComplexAttributeList'] = Ext.util.JSON.encode(['manproc']);
				}
				form.loadSpr('UslugaComplex_id', params, function() { 
					// Прогружаем справочник и прогружаем дерево в combo.fireEvent('select', combo, record, 0) 
					if (combo.getStore().getCount()==1) {
						// если количество записей в сторе комбобокса комплексной услуги равно 1
						var record = combo.getStore().getAt(0);
						combo.setValue(record.get('UslugaComplex_id')); // то выбираем первую запись
						//combo.fireEvent('select', combo, record, 0);
					}
				}.createDelegate(form));
				 
				if (getRegionNick() == 'kz') {
					frm.findField('PrehospDirect_id').setValue((getGlobalOptions().lpu_id == form.Lpu_uid && form.EvnDirection_IsReceive != 2) ? 15 : 16);
				} else {
					frm.findField('PrehospDirect_id').setValue((getGlobalOptions().lpu_id == form.Lpu_uid && form.EvnDirection_IsReceive != 2) ? 1 : 2);
				}

				form.getLoadMask().hide();
				form.setFieldsDisabled(false);
				break;
			case 'edit':
				form.setTitle(WND_EUOEW_EDIT);
				form.setFieldsDisabled(false);
				break;
			case 'view':
				form.setTitle(WND_EUOEW_VIEW);
				form.setFieldsDisabled(true);
				break;
		}

		//debugger;
		// если выбрана именно услуга в расписании, то закрываем ее от изменения (UslugaComplex_id)

		if (!Ext.isEmpty(combo.getValue())) {
			combo.disable();
		}
		/*
		if ( form.time_table == 'TimetablePar' && !Ext.isEmpty(combo.getValue()))
			combo.disable();
		else
		 combo.focus(false, 50);*/

		form.center();
		
		if (form.action!='add')
		{
			frm.load(
			{
				params: 
				{
					EvnUsluga_id: form.EvnUsluga_id
				},
				failure: function() 
				{
					form.getLoadMask().hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: lang['pri_poluchenii_dannyih_server_vernul_oshibku_poprobuyte_povtorit_operatsiyu'],
						title: lang['oshibka']
					});
				},
				success: function() 
				{
					form.getDate();
					// Загружаем справочники
					form.loadSpr('UslugaComplex_id', {UslugaComplex_id: frm.findField('UslugaComplex_id').getValue(), LpuSection_id: form.LpuSection_uid});
					//form.loadSpr('XmlTemplate_id', { XmlTemplate_id: frm.findField('XmlTemplate_id').getValue() });
				},
				url: '/?c=Usluga&m=loadEvnUslugaView'
			});
		}

		form.UslugaComplexAttributeList = [];
		form.useTreeArray = false;
		if ( getRegionNick() == 'ufa' ) {
			this.loadUslugaComplexTree()
			.then((UslugaComplex_List) => {
				form.UslugaComplex_List = UslugaComplex_List;
			})
			.then(() => {
				return form.loadEvnDirectionPersonDetails();
			})
			.then(personDetails => {
				for (var i in personDetails)
					if (form.params.hasOwnProperty(i)) form.params[i] = personDetails[i];
				form.setValueToHidden();
			})
			.then(() => {
				var list = [];
				for (var i = 0; i < form.UslugaComplex_List.length; i++) {
					list.push(form.UslugaComplex_List[i].id);
				}
				return form.loadUslugaComplexDetails(list)
			})
			.then((attributes) => {
				form.UslugaComplexAttributeList = attributes;
				form.processPersonDetailBlock(form.UslugaComplex_List);
				form.useTreeArray = true;
			});
		}
		form.EvnDirectionDetail.hide();
		form.syncShadow();
	},
	
	initComponent: function() 
	{
		// Форма с полями 
		var form = this;

		this.uslugaTree = new Ext.tree.TreePanel({
			title: lang['sostav_kompleksnoy_uslugi'],
			height: 300,
			autoWidth: true,
			autoScroll:true,
			animate:true,
			enableDD:true,
			containerScroll: true,
			autoLoad:false,
			frame: true,
			root: new Ext.tree.AsyncTreeNode({
				text: 'Снять/выделить все',
				nodeType: 'async',
				draggable: false,
				leaf: true,
				listeners: {
					click: function(root, e) {
						var flag = true;
						var nodes = root.childNodes;
						for (var i = 0; i < nodes.length; i++) {
							if (nodes[i].attributes.checked == false) continue;
							flag = false; break;
						}

						for (var i = 0; i < nodes.length; i++) {
							form.uslugaTree.fireEvent('checkchange', nodes[i], flag);
						}
					}
				}
			}),
			cls: 'x-tree-noicon',
			loader: new Ext.tree.TreeLoader(
			{
				dataUrl:'/?c=MedService&m=loadCompositionTree',
				uiProviders: {'default': Ext.tree.TreeNodeUI, tristate: Ext.tree.TreeNodeTriStateUI},
				//clearOnLoad: true,
				listeners:
				{
					load: function(p, node)
					{
						callback:
						{
							var nodes = node.childNodes || [];
							for (var i=0; i < nodes.length; i++)
							{
								if (nodes[i].childNodes.length == 0) {
									//отмечаем выбранные услуги
									form.uslugaTree.fireEvent('checkchange', nodes[i], true);
								}
							}
						}
					},
					beforeload: function (tl, node)
					{
						//form.uslugaTree.getLoadTreeMask('Загрузка дерева услуг... ').show();
						var base_form = form.EvnUslugaForm.getForm();
						var uslugacomplex_combo = base_form.findField('UslugaComplex_id');
						var param_usluga = 'UslugaComplex_id';
						if (uslugacomplex_combo.getFieldValue('UslugaComplexMedService_id')>0) {
							param_usluga = 'UslugaComplexMedService_id';
						}
						tl.baseParams = {};
						tl.baseParams.check = 1;

						if (node.getDepth()==0) {
							if (uslugacomplex_combo.getFieldValue('UslugaComplexMedService_id')>0) {
								tl.baseParams[param_usluga] = uslugacomplex_combo.getFieldValue('UslugaComplexMedService_id');
							} else if (uslugacomplex_combo.getValue()>0) {
								tl.baseParams[param_usluga] = uslugacomplex_combo.getValue();
							} else {
								return false;
							}
						} else {
							tl.baseParams[node.attributes.object_id] = node.attributes.object_value;
						}
						return true;
					}
				}
			}),
			changing: false,
			listeners: {
				'checkchange': function (node, checked) {
					if (!this.changing) {
						this.changing = true;
						node.expand(true, false);
						if (checked)
							node.cascade( function(node){node.getUI().toggleCheck(true)} );
						else
							node.cascade( function(node){node.getUI().toggleCheck(false)} );
						node.bubble( function(node){if (node.parentNode) node.getUI().updateCheck()} );
						this.changing = false;

						var checkedList = [];
						checkedList = (form.useTreeArray) ? form.uslugaTree.getChecked() : form.UslugaComplex_List;
						if ( getRegionNick() == 'ufa' ) {
							form.processPersonDetailBlock(checkedList);
						}
					}
				}.createDelegate(this.uslugaTree), 
				expand : function (p) {
					/*
					if (!this.changing)
					{
						this.changing = true;
						node.expand(true, false);
						if (checked)
							node.cascade( function(node){node.getUI().toggleCheck(true)} );
						else
							node.cascade( function(node){node.getUI().toggleCheck(false)} );
						node.bubble( function(node){if (node.parentNode) node.getUI().updateCheck()} );
						this.changing = false;
					}
					*/
				}.createDelegate(this.uslugaTree)
			}
		});

		form.EvnDirectionDetail = new Ext.form.FieldSet({
			id: form.id + 'EvnDirectionDetail',
			xtype: 'fieldset',
			autoHeight: true,
			title: 'Дополнительные сведения о пациенте',
			style: 'padding: 2; padding-left: 5px',
			items: [
				{
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Код контингента ВИЧ'),
					comboSubject: 'HIVContingentTypeFRMIS',
					hiddenName: 'HIVContingentTypeFRMIS_id',
					allowBlank: false,
					editable: true,
					ctxSerach: true,
					loadParams: { params: { where: ' where HIVContingentTypeFRMIS_Code != 100' } },
					anchor: '95%'
				}, {
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Код контингента COVID'),
					comboSubject: 'CovidContingentType',
					hiddenName: 'CovidContingentType_id',
					allowBlank: false,
					editable: true,
					ctxSerach: true,
					anchor: '95%'
				}, {
					xtype: 'swcommonsprcombo',
					hiddenName: 'HormonalPhaseType_id',
					comboSubject: 'HormonalPhaseType',
					fieldLabel: langs('Фаза цикла'),
					anchor: '95%'
				}, {
					id: form.id + 'RaceType_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					labelWidth: 130,
					style: 'margin: 2px 0 0 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Раса: ',
							layuot: 'anchor',
							width: 150 ,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
						}, {
							xtype: 'swcommonsprcombo',
							fieldLabel: langs('Раса'),
							comboSubject: 'RaceType',
							hiddenName: 'RaceType_id',
							anchor: '95%',
							disabled: true
						}, {
							xtype: 'button',
							id: form.id + 'RaceTypeAddBtn',
							style: 'margin-left: 5px;',
							text: 'Добавить',
							handler: function () {
								getWnd('swPersonRaceEditWindow').show({
									formParams: {
										PersonRace_id: 0,
										Person_id: form.params.Person_id
									},
									action: 'add',
									onHide: Ext.emptyFn,
									callback: function(data) {
										if (!data || !data.personRaceData)
											return false;
										form.formPanel.getForm()
											.findField('RaceType_id')
											.setValue(data.personRaceData.RaceType_id);
										Ext.getCmp(form.id + 'RaceTypeAddBtn').setDisabled(true);
									}
								});
							}
						}
					]
				},
				{
					id: form.id + 'PersonHeight_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					labelWidth: 130,
					style: 'margin: 2px 0 0 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Рост (см): ',
							name: 'PersonHeight_Height_label',
							layuot: 'anchor',
							width: 150 ,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma,arial,helvetica,sans-serif;'
						}, {
							xtype: 'textfield',
							name: 'PersonHeight_Height',
							disabled: true
						}, {
							xtype: 'panel',
							html: ' на дату: ',
							layuot: 'anchor',
							bodyStyle: 'padding: 1px 5px 0 5px; border: 0px; font: normal 12px tahoma,arial,helvetica,sans-serif;'
						}, {
							fieldLabel : lang['okonchanie'],
							name: 'PersonHeight_setDT',
							xtype: 'swdatefield',
							disabled: true,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
						}, {
							xtype: 'button',
							id: form.id + 'PersonHeightAddBtn',
							text: 'Добавить',
							style: 'margin-left: 5px;',
							handler: function () {
								getWnd('swPersonHeightEditWindow').show({
									measureTypeExceptions:[1,2],
									formParams: {
										PersonHeight_id: 0,
										Person_id: form.params.Person_id
									},
									action: 'add',
									onHide: Ext.emptyFn,
									callback: function(data) {
										if (!data || !data.personHeightData)
											return false;
										form.formPanel.getForm()
											.findField('PersonHeight_Height')
											.setValue(data.personHeightData.PersonHeight_Height);
										var date = Ext.util.Format.date(new Date(data.personHeightData.PersonHeight_setDate), 'd.m.Y');
										form.formPanel.getForm()
											.findField('PersonHeight_setDT')
											.setValue(date);
									}
								});
							}
						}
					]
				}, {
					id: form.id + 'PersonWeight_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					labelWidth: 130,
					style: 'margin: 2px 0 0 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Масса: ',
							layuot: 'anchor',
							width: 150 ,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
						}, {
							xtype: 'textfield',
							name: 'PersonWeight_WeightText',
							disabled: true
						}, {
							xtype: 'panel',
							html: ' на дату: ',
							layuot: 'anchor',
							bodyStyle: 'padding: 1px 5px 0 5px; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
						}, {
							fieldLabel : lang['okonchanie'],
							name: 'PersonWeight_setDT',
							xtype: 'swdatefield',
							disabled: true,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
						}, {
							xtype: 'button',
							id: form.id + 'PersonWeightAddBtn',
							text: 'Добавить',
							style: 'margin-left: 5px;',
							handler: function () {
								getWnd('swPersonWeightEditWindow').show({
									measureTypeExceptions:[1,2],
									Okei_InterNationSymbol:"kg",
									formParams: {
										PersonWeight_id: 0,
										Person_id: form.Person_id
									},
									action: 'add',
									onHide: Ext.emptyFn,
									callback: function(data) {
										if (!data || !data.personWeightData)
											return false;
										form.formPanel.getForm()
											.findField('PersonWeight_WeightText')
											.setValue(data.personWeightData.PersonWeight_text);
										var date = Ext.util.Format.date(new Date(data.personWeightData.PersonWeight_setDate), 'd.m.Y');
										form.formPanel.getForm()
											.findField('PersonWeight_setDT')
											.setValue(date);
									}
								});
							}
						}
					]
				}
			]
		});

		this.EvnUslugaForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'EvnUslugaEditForm',
			labelAlign: 'right',
			labelWidth: 150,
			items: 
			[{
				id: 'euoewEvnUsluga_id',
				name: 'EvnUsluga_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				name: 'EvnUsluga_pid',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'euoewPrehospDirect_id',
				name: 'PrehospDirect_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'euoewLpu_did',
				name: 'Lpu_did',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'euoewLpuSection_did',
				name: 'LpuSection_did',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'euoewMedPersonal_did',
				name: 'MedPersonal_did',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'euoewOrg_did',
				name: 'Org_did',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'euoewPerson_id',
				name: 'Person_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'euoewPersonEvn_id',
				name: 'PersonEvn_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'euoewServer_id',
				name: 'Server_id',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'euoewLpu_uid',
				name: 'Lpu_uid',
				value: null,
				xtype: 'hidden'
			}, 
			{
				id: 'euoewLpuSection_uid',
				name: 'LpuSection_uid',
				value: null,
				xtype: 'hidden'
			},
			{
				id: 'euoewMedService_id',
				name: 'MedService_id',
				value: null,
				xtype: 'hidden'
			},
			{
				fieldLabel: lang['slujba'],
				name: 'MedService_Nick',
				value: '',
				changeDisabled: false,
				xtype: 'descfield'
			}, 
			{
				allowBlank: false,
				value: null,
				fieldLabel: lang['kompleksnaya_usluga'],
				id: 'euoewUslugaComplex_id',
				name: 'UslugaComplex_id',
				tabIndex: TABINDEX_EUOEW + 1,
				anchor:'100%',
				xtype: 'swuslugacomplexpidcombo',
				listeners: 
				{
					select: function(combo,record,index)
					{
						var base_form = form.EvnUslugaForm.getForm();
						//log(this.uslugaTree.getRootNode(), record);
						if (record && record.get('UslugaComplexMedService_id')) {
							if (!form.fromRecordMaster) {
								this.UslugaComplexMedService_id = record.get('UslugaComplexMedService_id');
							}
						}
						this.uslugaTree.getLoader().load(
							this.uslugaTree.getRootNode(), 
							
							function () {
								this.uslugaTree.getRootNode().expand(true);
							}.createDelegate(this)
						);
						if (record) {
							base_form.findField('StudyTarget_id').setAllowBlank(record.get('isFunc') != 1);
						}
					}.createDelegate(this)
					/*
					change: function(combo,newValue,oldValue)
					{
						this.uslugaTree.getLoader().load(
							this.uslugaTree.getRootNode(), 
							function () {this.uslugaTree.getRootNode().expand(true);}.createDelegate(this)
						);
						
					}.createDelegate(this)
					*/
				}
			}, 
			{	
				fieldLabel: 'Цель исследования',
				xtype: 'swcommonsprcombo',
				allowBlank: true,
				hiddenName: 'StudyTarget_id',
				comboSubject: 'StudyTarget',
				tabIndex: TABINDEX_EUOEW + 2,
				value: 2,
				anchor:'100%',
			},
			{
				fieldLabel: 'Cito!',
				id: 'euoewUsluga_isCito',
				name: 'Usluga_isCito',
				tabIndex: TABINDEX_EUOEW + 2,
				hiddenName: 'Usluga_isCito',
				width: 70,
				allowBlank: false,
				value: 1,
				xtype: 'swyesnocombo'
			},
			{	// todo: Надо проверить фильтр и доработать
				fieldLabel: lang['punkt_zabora'],
				allowBlank: true,
				anchor: '100%',
				id: 'EUOEW_MedService_pzid',
				xtype: 'swmedservicecombo',
				hiddenName: 'MedService_pzid',
				tabIndex: TABINDEX_EUOEW + 3,
				params:{
					MedServiceType_SysNick: 'pzm'
				},
				listeners: {
					'select': function(combo, record) {
						var base_form = form.EvnUslugaForm.getForm(),
							MedService_id = record.get('MedService_id');
						var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue(),
							load_mask = new Ext.LoadMask(Ext.get(this.id), { msg: "Пожалуйста, подождите, идет сохранение..." });
						load_mask.show();

						Ext.Ajax.request({
							params: {
								MedService_id: MedService_id,
								UslugaComplex_id: UslugaComplex_id
							},
							callback: function(opt, success, resp) {
								var response_obj = Ext.util.JSON.decode(resp.responseText);

								if (response_obj.success && !Ext.isEmpty(response_obj.data[0])) {
									if (!Ext.isEmpty(response_obj.data[0].UslugaComplexMedService_id))
										form.pzmUslugaComplex_MedService_id = response_obj.data[0].UslugaComplexMedService_id;
								}else {
									form.pzmUslugaComplex_MedService_id = null;
								}

								load_mask.hide();
							}.createDelegate(this.ownerCt.ownerCt),
							url: '/?c=MedService&m=getPzmUslugaComplexMedService'
						});
					}.createDelegate(this)
				}
			},
			{
				allowBlank: getRegionNick() == 'kz' ? false : true,
				width: 250,
				useCommonFilter: true,
				xtype: 'swpaytypecombo',
				hiddenName: 'PayType_id',
				tabIndex: TABINDEX_EUOEW + 4,
			},
			{
				id: 'euoewtime_table',
				name: 'time_table',
				xtype: 'hidden'
			},
			{
				id: 'euoewTimetableMedService_id',
				name: 'TimetableMedService_id',
				xtype: 'hidden'
			},
			{
				allowBlank: true,
				id: 'euoewXmlTemplate_id',
				name: 'XmlTemplate_id',
				xtype: 'hidden'
			},
			form.EvnDirectionDetail,
			this.uslugaTree 
			],
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					switch (e.getKey()) 
					{
						case Ext.EventObject.C:
							if (this.action != 'view') 
							{
								this.doSave(false);
							}
							break;
						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
					//
				}
			}, 
			[
				{name: 'EvnUsluga_id'},
				{name: 'EvnUsluga_pid'},
				{name: 'Lpu_id'},
				{name: 'LpuSection_uid'},
				{name: 'UslugaComplex_id'},
				{name: 'PayType_id'}
				
			]),
			timeout: 600,
			url: '/?c=EvnUsluga&m=saveEvnUslugaComplexOrder'
		});
		this.formPanel = this.EvnUslugaForm;

		Ext.apply(this, {
			buttons:  [
				{
					handler: function() {
						this.ownerCt.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE,
					tabIndex: TABINDEX_EUOEW + 91
				},
				{ text: '-' },
				HelpButton(this, TABINDEX_EUOEW + 92),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					// tabIndex: 207,
					text: BTN_FRMCANCEL,
					tabIndex: TABINDEX_EUOEW + 93
				}
			],
			items: [form.EvnUslugaForm]
		});
		sw.Promed.swEvnUslugaOrderEditWindow.superclass.initComponent.apply(this, arguments);
	},
	processPersonDetailBlock: function (uslugaComplexList) {
		var scope = this;
		var baseForm = scope.formPanel.getForm();

		var isUfa = getGlobalOptions().region.nick === 'ufa';
		var isLab = false;
		var isContingentReq = false;
		var isContingentCovid = false;
		var hiddenCount = 0;

		for (var i = 0; i < uslugaComplexList.length; i++) {
			var uslugaComplex = uslugaComplexList[i];
			for (var j = 0; j < scope.UslugaComplexAttributeList.length; j++) {
				var attribute = scope.UslugaComplexAttributeList[j];
				if (attribute.UslugaComplex_id != uslugaComplex.id) continue;
				if (attribute.UslugaComplexAttributeType_Code == 8) isLab = true;
				if (attribute.UslugaComplexAttributeType_Code == 224) isContingentReq = true;
				if (attribute.UslugaComplexAttributeType_Code == 227) isContingentCovid = true;
			}
		}

		var RaceType_FS = Ext.getCmp(scope.id + 'RaceType_FS');
		if (!isUfa || !isLab) {
			RaceType_FS.hide();
			hiddenCount++;
		} else {
			RaceType_FS.show();
			Ext.getCmp(this.id + 'RaceTypeAddBtn').setDisabled(!Ext.isEmpty(baseForm.findField('RaceType_id').getValue()));
		}
		var HIVContingentTypeFRMIS_id = baseForm.findField('HIVContingentTypeFRMIS_id');
		if (!isUfa || !isLab || !isContingentReq) {
			HIVContingentTypeFRMIS_id.setDisabled(true);
			HIVContingentTypeFRMIS_id.hideContainer();
			hiddenCount++;
		} else {
			HIVContingentTypeFRMIS_id.setDisabled(false);
			HIVContingentTypeFRMIS_id.showContainer();
		}
		isContingentCovid &= isUfa && isLab;
		var CovidContingentField = baseForm.findField('CovidContingentType_id');
		CovidContingentField.setContainerVisible(isContingentCovid);
		CovidContingentField.setDisabled(!isContingentCovid);
		var HormonalPhaseType_id = baseForm.findField('HormonalPhaseType_id');
		if (!isUfa || !isLab || !(this.params.Sex_id == 2)) {
			HormonalPhaseType_id.hideContainer();
			HormonalPhaseType_id.disable();
			hiddenCount++;
		} else {
			HormonalPhaseType_id.showContainer();
			HormonalPhaseType_id.enable();
		}
		var PersonHeight_FS = Ext.getCmp(scope.id + 'PersonHeight_FS');
		var PersonWeight_FS = Ext.getCmp(scope.id + 'PersonWeight_FS');
		if (!isUfa || !isLab) {
			PersonHeight_FS.hide();
			PersonWeight_FS.hide();
			hiddenCount += 2;
		} else {
			PersonHeight_FS.show();
			PersonWeight_FS.show();
		}
		this.EvnDirectionDetail.setVisible(hiddenCount != 5);
		this.setHeight(this.height - hiddenCount * 20);
	},
	loadEvnDirectionPersonDetails: function () {
		var scope = this;
		return new Promise(function (resolve, reject) {
			var requestParams = {
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						resolve(response_obj);
					} else {
						throw new Error('Ошибка при загрузке сигнальной информации');
					}
				},
				params: {
					Person_id: scope.Person_id
				},
				url: '/?c=PersonDetailEvnDirection&m=getOne'
			};
			Ext.Ajax.request(requestParams);
		});
	},
	loadUslugaComplexTree: function () {
		var scope = this;
		return new Promise(function (resolve, reject) {
			var requestParams = {
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						resolve(response_obj);
					} else {
						throw new Error('Ошибка при загрузке сигнальной информации');
					}
				},
				params: {
					UslugaComplexMedService_id: scope.UslugaComplexMedService_id
				},
				url: '/?c=MedService&m=loadCompositionTree'
			};
			Ext.Ajax.request(requestParams);
		});
	},
	loadUslugaComplexDetails: function (uslugaComplexList) {
		var scope = this;
		return new Promise(function (resolve, reject) {
			var requestParams = {
				callback: function (options, success, response) {
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						resolve(response_obj);
					} else {
						throw new Error('Ошибка при загрузке информации об атрибутах комплексной услиги');
					}
				},
				params: {
					uslugaComplexList: Ext.util.JSON.encode(uslugaComplexList),
					UslugaComplex_id: scope.UslugaComplex_id
				},
				url: '/?c=UslugaComplex&m=loadUslugaComplexAttributeGrid'
			};
			Ext.Ajax.request(requestParams);
		});
	},
});
