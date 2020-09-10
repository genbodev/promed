/**
 * swEvnPrescrUslugaInputWindow - окно добавления назначений с услугой
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      EvnPrescr
 * @access       public
 * @copyright    Copyright (c) 2009-2013 Swan Ltd.
 * @version      09.2013
 */

sw.Promed.swEvnPrescrUslugaInputWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh: true,
    objectName: 'swEvnPrescrUslugaInputWindow',
    objectSrc: '/jscore/Forms/Prescription/swEvnPrescrUslugaInputWindow.js',
    collapsible: false,
    draggable: true,
    height: 550,
    id: 'EvnPrescrUslugaInputWindow',
    buttonAlign: 'left',
    closeAction: 'hide',
    maximized: true,
    minHeight: 550,
    minWidth: 800,
    modal: true,
    resizable: false,
    plain: true,
    width: 800,
    winTitle: lang['dobavlenie_naznacheniya'],
    listeners:
    {
        hide: function(win)
        {
            if (win.hasChange) {
                win.callback(win.changedType);
            }
            win.onCancel();
            win.onHide();
        }
    },

    callback: Ext.emptyFn,
    onHide: Ext.emptyFn,
    userMedStaffFact: {},
    hasChange: false,
    changedType: [],
	groupMode: 0,
	setGroupMode: function(mode) {
		var win = this;
		this.groupMode = mode;
		win.uslugaFrame.removeAll({ clearAll: true });
		if (mode == 0) {
			// по услугам
			win.uslugaFrame.setColumnHidden('location', false);
			win.uslugaFrame.getGrid().getStore().remoteSort = true;
			win.modeGroupUslugaComplex.items[0].toggle(true);
		} else {
			// по местам оказания
			win.uslugaFrame.setColumnHidden('location', true);;
			win.uslugaFrame.getGrid().getStore().remoteSort = false;
			win.modeGroupMedService.items[0].toggle(true);
		}

		win.loadUslugaFrame();
	},
	setUslugaLoadMode: function(mode) {
		var win = this;
		win.uslugaComplexLoadMode = mode;

		this.uslugaFilterCombo.setDisabled(mode != 'uc');
		this.analyzerTestNameFilterField.setDisabled(mode != 'at');

		this.uslugaFilterCombo.setContainerVisible(mode == 'uc');
		this.analyzerTestNameFilterField.setContainerVisible(mode == 'at');

		switch (mode) {
			case 'uc':
				win.analyzerTestNameFilterField.reset();
				win.uslugaComplexModeUC.items[0].toggle(true);
				win.uslugaFilterCombo.getStore().baseParams.query = "";
				win.uslugaFilterCombo.getStore().reload();
				break;
			case 'at':
				win.uslugaFilterCombo.clearValue();
				win.uslugaFilterCombo.getStore().removeAll();
				win.uslugaComplexModeAT.items[0].toggle(true);
				break;
		}
	},
	/**
	 * Устанавливает обработчики на элементы управления секции формы просмотра с идентификатором code +'_'+ id
	 */
	addHandlerForObject: function (code, id, is_readonly)
	{
		log('addHandlerForObject', code, id, is_readonly);
		// id секции должны быть в формате: EvnVizitPL_data_21374
		var section_id = code +'_rightPanel_'+ id,
			s = Ext.get(section_id);
		if (!s)
		{
			log('addHandlerForObject: Section '+ section_id +' NOT found ');
			return false;
		}
		//log('Section '+ section_id +' found');
		var section_action = this.actionListClickBySection[code];
		// log([code,section_action]);
		if (section_action)
		{
			var el;
			var params = {object:code, object_id:id, section_id: section_id, isReadOnly: is_readonly};
			for(var action in section_action)
			{
				// id элементов управления должны быть в формате: EvnVizitPL_protocol_21374_edit
				el = Ext.get(section_id +'_'+ action);
				//log(section_id +'_'+ action);
				if (action == 'addPrescription') {
					//log([action, el, is_readonly, action.inlist(this.actionNameList_View), this.isAccessViewFormDelegate(section_id,action)]);
				}
				if (el)
				{
					// content_id = идентификатор содержимого секции, которое нужно обновлять после редактирования
					if(is_readonly)
					{
						params.isAccessDelegate = this.isAccessViewFormDelegate(section_id,action);
						if (action.inlist(this.actionNameList_View) || params.isAccessDelegate )
						{
							el.on('click', section_action[action],s,params);
						}
						else
						{
							// часть элементов не надо скрывать (интерактивные поля)
							if (el.id && el.id.indexOf('_input') > 0) {
								// el.hide();
							} else {
								el.hide();
							}
						}
					}
					else
					{
						if (action.inlist(this.actionNameList_Edit))
						{
							params.content_id = section_id +'_content';
						}
						el.on('click', section_action[action],s,params);
					}
				}
				/*
				 else
				 {
				 log(Ext.get(section_id +'_'+ action));
				 log('action By Element '+ section_id +'_'+ action +' not found');
				 }
				 */
			}
			if(this.actionListDblClickBySection[code] && (!is_readonly))
			{
				s.on('dblclick', this.actionListDblClickBySection[code],s,params);
			}
			return true;
		}
		else
		{
			//log('action By Section '+ section_id +' not found');
			// log(this.actionListClickBySection);
			return false;
		}
	},

	addPrescription: function(d, section_code) {
		var formParams = this.filterPanel.getForm().getValues();

		var PrescriptionType_Code = d.object_id.replace(/.*-/, '');

		var me = this,
			evnsysnick = this.defineParentEvnClass().EvnClass_SysNick,
			option = {
				parentEvnClass_SysNick: evnsysnick,
				userMedStaffFact: this.userMedStaffFact
			};

		//todo: нужно определять ид по коду, хотя сейчас они одинаковые
		option.PrescriptionType_id = PrescriptionType_Code;
		option.PrescriptionType_Code = PrescriptionType_Code;
		option.action = 'add';
		option.data = {
			Person_id: formParams.Person_id,
			PersonEvn_id: formParams.PersonEvn_id,
			Server_id: formParams.Server_id,
			Person_Firname: this.Person_Firname,
			Person_Surname: this.Person_Surname,
			Person_Secname: this.Person_Secname,
			Person_Age: this.Person_Age,
			Diag_Code: this.Diag_Code,
			Diag_Name: this.Diag_Name,
			Diag_id: formParams.Diag_id,
			Evn_pid: formParams.Evn_pid,
			begDate: formParams.EvnPrescr_setDate,
			parentEvnClass_SysNick: evnsysnick,
			userMedStaffFact: this.userMedStaffFact,
			DopDispInfoConsent_id: formParams.DopDispInfoConsent_id,
			EvnPLDisp_id: formParams.EvnPLDisp_id
		};
		option.changedType = me.changedType;
		option.isPaidVisit = this.isPaidVisit;

		if (PrescriptionType_Code.inlist([1,2,5,10])) {
			option.callbackEditWindow = function(data){
				me.hasChange = true;
				if (!PrescriptionType_Code.inlist(me.changedType)) {
					me.changedType.push(PrescriptionType_Code);
				}
				me.reloadEvnPrescr({PrescriptionType_Code: PrescriptionType_Code});
			};
		} else {
			option.callbackEditWindow = me.callback;
		}

		if (option.PrescriptionType_Code == 5) {
			sw.Promed.EvnPrescr.openEvnCourseEditWindow(option);
		} else {
			sw.Promed.EvnPrescr.openEditWindow(option);
		}
		return true;
	},

	openEvnXmlViewWindow: function(sectionCode, el_data) {
		var formParams = this.getObjectData(el_data.object, el_data.object_id);
		if ( formParams == false ) {
			return false;
		}
		var win = getWnd('swEvnXmlViewWindow');
		if (win.isVisible()) {
			win.hide();
		}
		var params = {
			EvnXml_id: formParams.EvnXml_id,
			onBlur: function() {
				win.hide();
			},
			onHide: Ext.emptyFn
		};
		// switch(formParams.EvnClass_SysNick)
		win.show(params);
		return true;
	},
	openEvnXmlEditWindow: function(action, sectionCode, d) {
		var form = this,
			ep_data = this.getObjectData(sectionCode, d.object_id),
			params = {
			title: null,
			action: action,
			userMedStaffFact: form.userMedStaffFact,
			EvnClass_id: null,
			XmlType_id: null,
			UslugaComplex_id: null,
			EvnXml_id: null,
			Evn_id: null,
			onBlur: function() {
				//win.hide();
			},
			onHide: function() {
				// обновить панель просмотра
				form.reloadEvnPrescr({PrescriptionType_Code: ep_data.PrescriptionType_Code});
			}
		};
	
		params.title = lang['blank_napravleniya'];
		params.UslugaComplex_id = ep_data.UslugaComplex_2011id;
		params.EvnClass_id = 27;
		params.Evn_id = ep_data.EvnDirection_id;
		params.EvnXml_id = ep_data.EvnXmlDir_id;
		params.XmlType_id = ep_data.EvnXmlDirType_id || null;
		
		if (!params.Evn_id) {
			return false;
		}
		var win = getWnd('swEvnXmlEditWindow');
		if (win.isVisible()) {
			win.hide();
		}
		win.show(params);
		return true;
	},
	openEvnCourseEditWindow: function(d) {
		var form = this;
		var data = form.getObjectData(d.object, d.object_id);
		if ( data == false ) { return false; }
		var evnsysnick = this.defineParentEvnClass().EvnClass_SysNick;
		sw.Promed.EvnPrescr.openEvnCourseEditWindow({
			action: 'edit'
			,PrescriptionType_id: data.PrescriptionType_id
			,PrescriptionType_Code: data.PrescriptionType_Code
			,parentEvnClass_SysNick: evnsysnick
			,userMedStaffFact: form.userMedStaffFact
			,data: {
				Diag_id: null,
				Evn_pid: data.EvnPrescr_pid
				,EvnCourse_id: data.EvnCourse_id
				,Person_id: form.Person_id
				,PersonEvn_id: form.PersonEvn_id
				,Server_id: form.Server_id
				,Person_Firname: form.Person_Firname
				,Person_Surname: form.Person_Surname
				,Person_Secname: form.Person_Secname
				,Person_Birthday: form.Person_Birthday
			}
			,callbackEditWindow: function(fdata){
				form.reloadEvnPrescr({
					PrescriptionType_Code: data.PrescriptionType_id,
					reload: true
				});
			}
		});
		return true;
	},
	openPrescrActionMenu: function(e, d, section_code) {
		var form = this;

		$('#'+section_code+'_'+d.object_id+'_openPrescrActionMenu').addClass('click');
		$('#'+section_code+'_'+d.object_id).addClass('hover');
		$('#'+section_code+'_'+d.object_id+' > .prescriptioninfo').addClass('hover');

		var evnsysnick = this.defineParentEvnClass().EvnClass_SysNick,
			data = this.getObjectData(section_code,d.object_id);

		// можно брать данные родительского объекта откуда нибудь, но вроде как если попали в эту форму, значит имеем доступ к родительскому объекту
		var evdata = {
			'accessType': 'edit'
		};

		data.callback = function(params) {
			form.reloadEvnPrescr({
				PrescriptionType_Code: data.PrescriptionType_id,
				reload: true
			});
		}

		this.PrescrListActionMenu = sw.Promed.EvnPrescr.getPrescrActionMenu({
			data: data,
			evdata: evdata,
			evnsysnick: evnsysnick,
			ownerWindow: this,
			EvnClass_SysNick: section_code,
			d: d
		});

		this.PrescrListActionMenu.on('beforehide',function(){
			$('#'+section_code+'_'+d.object_id).removeClass('hover');
			$('#'+section_code+'_'+d.object_id+' > .prescriptioninfo').removeClass('hover');
			$('#'+section_code+'_'+d.object_id+'_openPrescrActionMenu').removeClass('click');
		});
		this.PrescrListActionMenu.showAt(e.getXY());
		return true;
	},
	getObjectData: function(object,object_id)
	{
		var record = this.viewFormDataStore.getById(object +'_'+ object_id);
		log('getObjectData', object +'_'+ object_id, record);
		if (record && record.data)
		{
			return record.data;
		}
		//log('In viewFormDataStore not found record with id: '+ object +'_'+ object_id);
		//log(this.viewFormDataStore);
		return false;
	},
	getObjectDataWithFindBy: function(search)
	{
		var index = this.viewFormDataStore.findBy(search);
		if (index == -1) {
			return false;
		}
		return this.viewFormDataStore.getAt(index).data;
	},
	/**
	 * Store данных событий, отображаемых в панели просмотра
	 * атрибуты записей:
	 * object_code string
	 * object_key string
	 * object_value int
	 * parent_object_code string
	 * parent_object_key string
	 * parent_object_value int
	 * subsection array
	 * list string
	 * id string Имеет формат: object_code +'_'+ object_value
	 * data
	 */
	viewFormDataStore: new Ext.data.SimpleStore({
		autoLoad: true,
		fields:[],
		updateFromMap: function(map, parent)
		{
			this.completeFromMap(map, parent, true);
		},
		completeFromMap: function(map, parent, remove_existing)
		{
			log({
				debug: 'completeFromMap',
				args: arguments,
				store: this
			});
			if (typeof(map) != 'object') return false;
			var object = {},list,subsection, record, item_arr, i,index;
			var value_list = [];
			for (object.code in map)
			{
				//log(object.code);
				if (typeof(map[object.code]) == 'object' && map[object.code].item && Ext.isArray(map[object.code].item))
				{
					item_arr = map[object.code].item;
					object.key = map[object.code].object_key;
					list = map[object.code].list || null;
					subsection = map[object.code].subsection || null;
					//log(item_arr.length);
					for(i=0; i < item_arr.length; i++)
					{
						//log(item_arr[i].data);
						//item_arr[i].data._is_first = (i==0);
						//item_arr[i].data._is_last = (i==(item_arr.length-1));
						item_arr[i].data._item_count = item_arr.length;
						item_arr[i].data._item_index = i;
						record = new Ext.data.Record(item_arr[i].data);
						object.value = item_arr[i].data[object.key];
						value_list.push(object.value);
						if(record)
						{
							record.object_code = object.code;
							record.object_key = object.key;
							record.object_value = object.value;
							record.parent_object_code = (parent && parent.code) || null;
							record.parent_object_key = (parent && parent.key) || null;
							record.parent_object_value = (parent && parent.value) || null;
							record.subsection = subsection;
							record.list = list;
							record.id = object.code +'_'+ object.value;
							//log(record.id);
							if (remove_existing)
							{
								index = this.indexOfId(record.id);
								if(index)
								{
									this.removeAt(index);
								}
							}
							this.add(record);
						}
						if(item_arr[i].xml_data) {
							record = new Ext.data.Record(item_arr[i].xml_data);
							if(record)
							{
								record.object_code = 'EvnXml';
								record.object_key = 'EvnXml_id';
								record.object_value = item_arr[i].EvnXml_id;
								record.parent_object_code = object.code;
								record.parent_object_key = object.key;
								record.parent_object_value = object.value;
								record.XmlType_id = item_arr[i].XmlType_id;
								record.id = 'EvnXml_'+ object.value;
								if (remove_existing)
								{
									index = this.indexOfId(record.id);
									if(index)
									{
										this.removeAt(index);
									}
								}
								this.add(record);
							}
						}
						if (item_arr[i].children)
						{
							this.completeFromMap(item_arr[i].children, object,remove_existing);
						}
					}
					this.each(function(record) {
						if (
							record.object_code == object.code
							&& record.parent_object_key == parent.key && record.parent_object_value == parent.value
							&& !record.object_value.inlist(value_list)
						) {
							index = this.indexOfId(record.id);
							if(index)
							{
								this.removeAt(index);
							}
						}
					}.createDelegate(this));
				} else {
					this.each(function(record) {
						if (record.object_code == object.code && record.parent_object_key == parent.key && record.parent_object_value == parent.value) {
							index = this.indexOfId(record.id);
							if(index)
							{
								this.removeAt(index);
							}
						}
					}.createDelegate(this));
				}
			}
		},
		data : []
	}),
	openDirActionMenu: function(e, d, section_code) {
		var form = this;

		var evnsysnick = this.defineParentEvnClass().EvnClass_SysNick,
			data = this.getObjectData(section_code,d.object_id);

		$('#'+section_code+'_'+d.object_id+'_openDirActionMenu').addClass('click');
		$('#'+section_code+'_'+data.EvnDirection_id).addClass('hover');

		// можно брать данные родительского объекта откуда нибудь, но вроде как если попали в эту форму, значит имеем доступ к родительскому объекту
		var evdata = {
			'accessType': 'edit'
		};

		data.callback = function(params) {
			form.reloadEvnPrescr({
				PrescriptionType_Code: data.PrescriptionType_id,
				reload: true
			});
		}

		var dirdata = form.getObjectDataWithFindBy(function(record,id){
			if(record.object_code == d.object && record.get(d.object +'_id') == d.object_id) {
				dirrec = record;
				return true;
			}
			return false;
		});

		log('azaza', dirdata, d.object, d.object_id);

		this.DirListActionMenu = sw.Promed.EvnPrescr.getDirActionMenu({
			data: data,
			evdata: evdata,
			dirdata: dirdata,
			evnsysnick: evnsysnick,
			ownerWindow: this,
			EvnClass_SysNick: section_code,
			d: d
		});

		this.DirListActionMenu.on('beforehide',function(){
			$('#'+section_code+'_'+data.EvnDirection_id).removeClass('hover');
			$('#'+section_code+'_'+d.object_id+'_openDirActionMenu').removeClass('click');
		});
		this.DirListActionMenu.showAt(e.getXY());
		return true;
	},
	defineParentEvnClass: function() {
		var evnClass = {
			EvnClass_SysNick: '',
			EvnClass_id: 0
		};

		var parentEvnClass_SysNick = this.filterPanel.getForm().getValues().parentEvnClass_SysNick;
		switch (true) {
			case (parentEvnClass_SysNick.inlist(['EvnPL','EvnVizitPL'])):
				evnClass.EvnClass_SysNick = 'EvnVizitPL';
				evnClass.EvnClass_id = 11;
				break;
			case (parentEvnClass_SysNick.inlist(['EvnPLStom','EvnVizitPLStom'])):
				evnClass.EvnClass_SysNick = 'EvnVizitPLStom';
				evnClass.EvnClass_id = 13;
				break;
			case (parentEvnClass_SysNick.inlist(['EvnPS','EvnSection'])):
				evnClass.EvnClass_SysNick = 'EvnSection';
				evnClass.EvnClass_id = 32;
				break;
			case (parentEvnClass_SysNick.inlist(['EvnPLDispMigrant'])):
				evnClass.EvnClass_SysNick = 'EvnPLDispMigrant';
				evnClass.EvnClass_id = 189;
				break;
			case (parentEvnClass_SysNick.inlist(['EvnPLDispDriver'])):
				evnClass.EvnClass_SysNick = 'EvnPLDispDriver';
				evnClass.EvnClass_id = 190;
				break;
		}
		return evnClass;
	},
	isAccessViewFormDelegate: function() {
		// заглушка
		return false;
	},
	/*
	 * Навешивает обработчики на элементы управления формы просмотра в соответствии со списком map
	 */
	addHandlerInTpl: function (map, pid, readonly)
	{
		log('addHandlerInTpl', map, pid, readonly);
		//log(map);
		//log(pid);
		if (typeof(map) != 'object')
		{
			return false;
		}
		var o='', b,i,j, code, id, id2, ss_arr, ro_arr = [], ro_id, item_arr, parent_id, node, data,
			is_readonly;
		for (o in map)
		{
			if (typeof(map[o]) != 'object')
			{
				continue;
			}
			is_readonly = readonly||false;
			ss_arr = null;
			if (Ext.isArray(map[o].subsection))
			{
				ss_arr = map[o].subsection;
			}
			ro_arr = [];
			if (Ext.isArray(map[o].related_objects))
			{
				ro_arr = map[o].related_objects;
			}
			if (map[o] && map[o].parent_value)
			{
				//log('For section-parent_value: '+ o +'_'+ map[o].parent_value);
				this.addHandlerForObject(o,map[o].parent_value,is_readonly);
			}

			if (map[o] && typeof(map[o].list) == 'string' && pid)
			{
				id2 = pid;
				if(map[o].first_key && map[o].parent_object) {
					data = this.getObjectData(map[o].parent_object,pid);
					if(data) {
						id2 = data[map[o].first_key] +'_'+ pid;
					}
				}
				//log('For section-list: '+ map[o].list +'_'+ id2);
				this.addHandlerForObject(map[o].list,id2,is_readonly);
			}

			if (map[o] && map[o].item && Ext.isArray(map[o].item))
			{
				item_arr = map[o].item; //parent_value
				code = o;
				for(i=0; i < item_arr.length; i++)
				{
					id = item_arr[i][map[o].object_key];

					if (id||(id==0&&o=='MorbusPregnancy'))
					{
						//получаем тип доступа из ноды
						//node = this.Tree.getNodeById(o +'_'+ id);
						//if(node && node.attributes.accessType && node.attributes.accessType == 'view')
						//получаем тип доступа из map
						data = this.getObjectData(o,id);
						is_readonly = (this.isReadOnly || readonly || (data && data.accessType && data.accessType == 'view'));
						if (
							data && item_arr[i].EvnXml_id
							&& data.EvnXml_pid
							&& this.isAccessViewFormDelegate(o+'List_'+data.EvnXml_pid, 'adddoc')
							&& data.pmUser_insID && getGlobalOptions().pmuser_id == data.pmUser_insID
						) {
							//если делегировано право добавлять документ и документ создан текущим пользователем,
							//то он должен быть доступен для редактирования
							is_readonly = false;
						}
						if (data && data[o+'_pid']
							&& this.isAccessViewFormDelegate(o+'List_'+data[o+'_pid'], 'add')
							&& data.pmUser_insID && getGlobalOptions().pmuser_id == data.pmUser_insID
						) {
							//если делегировано право добавлять файл и файл создан текущим пользователем,
							//то должны быть доступны действия редактирования файла
							is_readonly = false;
						}
						if (data && data['EvnUsluga_pid']
							&& this.isAccessViewFormDelegate(o+'List_'+data['EvnUsluga_pid'], 'add')
							&& data.accessType && 'edit' == data.accessType
						) {
							//если делегировано право добавлять услугу и есть доступ к редактированию услуги в accessType,
							//то должны быть доступны действия редактирования услуги
							is_readonly = false;
						}
						if(item_arr[i].EvnXml_id && !is_readonly) {
							//log([o,id]);
							//log(item_arr[i]);
							this.processingXmlData({
								Evn_id: item_arr[i].Evn_id || null,
								Evn_pid: item_arr[i].Evn_pid || null,
								Evn_rid: item_arr[i].Evn_rid || null,
								EvnClass_id: item_arr[i].EvnClass_id,
								EvnXml_id: item_arr[i].EvnXml_id,
								XmlType_id: item_arr[i].XmlType_id,
								xml_data: item_arr[i].xml_data
							});
						}

						id2 = id;
						if(map[o].first_key && data[map[o].first_key]) {
							id2 = data[map[o].first_key] +'_'+ id;
						}

						this.addHandlerForObject(code,id2,is_readonly);
						if (Ext.isArray(ss_arr))
						{
							for(j=0; j < ss_arr.length; j++)
							{
								//log('For subsection: '+ ss_arr[j] +'_'+ id);
								this.addHandlerForObject(ss_arr[j].code,id,is_readonly);
								if (ss_arr[j].code == 'EvnVizitPL_protocol' && item_arr[i].emptyxmltemplate)
								{
									b = Ext.get(ss_arr[j].code +'_'+ id +'_edit');
									b.setStyle({display: 'none'});
									b = Ext.get(ss_arr[j].code +'_'+ id +'_del');
									b.setStyle({display: 'none'});
									b = Ext.get(ss_arr[j].code +'_'+ id +'_print');
									b.setStyle({display: 'none'});
								}
							}
						}
					}

					for(j=0; j < ro_arr.length; j++)
					{
						ro_id = item_arr[i].data[ro_arr[j].field_code] +'_'+ item_arr[i].data[ro_arr[j].field_key];
						this.addHandlerForObject(ro_arr[j].field_code,ro_id,is_readonly);
					}

					if (item_arr[i].children && id)
					{
						this.addHandlerInTpl(item_arr[i].children, id, is_readonly);
					}
				}
			}
		}
		return true;
	},
	openEvnDirectionEditWindow: function(action, el_data) {
		var form = this;
		var parent_code = this.defineParentEvnClass().EvnClass_SysNick;
		var root_code = '';
		var section_code = 'EvnDirection';
		switch (true) {
			case (parent_code == 'EvnSection'):
				root_code = 'EvnPS';
				section_code = 'EvnDirectionStac';
				break;
			case (parent_code == 'EvnVizitPL'):
				root_code = 'EvnPL';
				break;
			case (parent_code == 'EvnVizitPLStom'):
				root_code = 'EvnPLStom';
				section_code = 'EvnDirectionStom';
				break;
		}
		var formParams = {};

		var opt = el_data.object_id.split('_');
		formParams = form.getObjectDataWithFindBy(function(record,id){
			if (record.object_code == 'EvnDirection' && record.get('timetable') == opt[0] && record.get('timetable_id') == opt[1]) {
				return true;
			}
			if (record.object_code == el_data.object && record.get(el_data.object + '_id') == el_data.object_id) {
				return true;
			}
			return false;
		});
		//formParams = form.getObjectData(el_data.object,el_data.object_id);
		if (formParams == false)
		{
			return false;
		}
		var params = new Object({
			Person_id: this.filterPanel.getForm().findField('Person_id').getValue(),
			action: action,
			EvnDirection_id: formParams.EvnDirection_id,
			callback: Ext.emptyFn,
			formParams: formParams,
			parentEvnClass_SysNick: (this.parentEvnClass_SysNick) ? this.parentEvnClass_SysNick : null
		});
		params.onHide = Ext.emptyFn;
		getWnd('swEvnDirectionEditWindow').show(params);
	},
    /**
     * Показываем окно
     * @return {Boolean}
     */
    show: function() {
        sw.Promed.swEvnPrescrUslugaInputWindow.superclass.show.apply(this, arguments);
        var thas = this;
        var base_form = thas.filterPanel.getForm();

		this.actionListDblClickBySection = {};
		this.actionListClickBySection = {};
		this.actionNameList_View = [];
		this.actionNameList_Add = [];
		this.actionNameList_Edit = [];
		this.actionNameList_Del = [];

        if (!arguments[0]
            || !arguments[0].PrescriptionType_Code
            || !arguments[0].Person_Surname
            || !arguments[0].userMedStaffFact
            || !arguments[0].formParams
            || !arguments[0].formParams.Evn_pid
            || !arguments[0].formParams.parentEvnClass_SysNick
            || !arguments[0].formParams.PrescriptionType_id
            || !arguments[0].formParams.PersonEvn_id
            ) {
            sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi'], function() {thas.hide();} );
            return false;
        }
        this.PrescriptionType_Code = arguments[0].PrescriptionType_Code;
        this.userMedStaffFact = arguments[0].userMedStaffFact;
        this.callback = Ext.emptyFn;
        this.hasChange = false;
        this.changedType = [];

		if (arguments[0].parentEvnClass_SysNick) {
			this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
		}

        if (typeof arguments[0].callback == 'function') {
            this.callback = arguments[0].callback;
        }
        this.onHide = Ext.emptyFn;
        if (typeof arguments[0].onHide == 'function') {
            this.onHide = arguments[0].onHide;
        }
		if (arguments[0].changedType) {
			this.changedType = arguments[0].changedType;
		}
	
	    this.isPaidVisit = (arguments[0].isPaidVisit)?true:false;

	    this.isPaidVisit = (arguments[0].isPaidVisit)?true:false;

        var title = this.winTitle +'. '+ arguments[0].Person_Surname;
        this.Person_Surname = arguments[0].Person_Surname;
        this.Person_Firname = null;
        if (arguments[0].Person_Firname) {
            title += ' '+ arguments[0].Person_Firname;
            this.Person_Firname = arguments[0].Person_Firname;
        }
        this.Person_Secname = null;
        if (arguments[0].Person_Secname) {
            title += ' '+ arguments[0].Person_Secname;
            this.Person_Secname = arguments[0].Person_Secname;
        }
        this.Person_Birthday = arguments[0].Person_Birthday || null;
        this.Person_Age = arguments[0].Person_Age || null;
        if (false && this.Person_Age) {
            title += ', '+ this.Person_Age;
        }
        this.Diag_Code = null;
        if (arguments[0].Diag_Code ) {
            title += '. '+ arguments[0].Diag_Code;
            this.Diag_Code = arguments[0].Diag_Code;
        }
        this.Diag_Name = null;
        if (arguments[0].Diag_Name) {
            title += '. '+ arguments[0].Diag_Name;
            this.Diag_Name = arguments[0].Diag_Name;
        }
        this.IsCito = false;
        if (arguments[0].IsCito) {
            this.IsCito = arguments[0].IsCito;
        }

		if (arguments[0].electronicQueueData) {
			this.electronicQueueData = arguments[0].electronicQueueData;
		}
		//BOB - 22.04.2019
		if (arguments[0].parentWindow_id) {
			this.parentWindow_id = arguments[0].parentWindow_id;
		}
		//BOB - 22.04.2019
	    
	    if (arguments[0].formParams['TreatmentClass_id']) {
		    this.TreatmentClass_id = arguments[0].formParams['TreatmentClass_id'];
	    }
	
	    if (arguments[0].formParams['PayTypeKAZ_id']) {
		    this.PayTypeKAZ_id = arguments[0].formParams['PayTypeKAZ_id'];
	    }


        /*var isKarelia = getGlobalOptions().region && getGlobalOptions().region.nick == "kareliya";
        if((PrescriptionType_Code==11||PrescriptionType_Code==12)&&isKarelia ){
            base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
        }*/
        var allowSetUserMoAsDefault = true;
        if (allowSetUserMoAsDefault) {
            //делаем тут, чтобы при сбросе вернулось к состоянию как при открытии
            arguments[0].formParams.Lpu_id = getGlobalOptions().lpu_id;
        }
        this.formParams = arguments[0].formParams;
        this.setTitle(title);

        if (typeof arguments[0].formParams.EvnPrescr_setDate == 'object') {
            arguments[0].formParams.EvnPrescr_setDate = arguments[0].formParams.EvnPrescr_setDate.format('d.m.Y');
        }
        this.initPrescriptionType_Code = arguments[0].formParams.PrescriptionType_id;
        this.doReset(false);
		this._loadPrescriptionListByType(this.initPrescriptionType_Code);
        this.loadDataViewStore(base_form.findField('Evn_pid').getValue());
        this.uslugaFilterCombo.showCodeField = getPrescriptionOptions().enable_show_service_code;
        this.uslugaFilterCombo.setDisallowedUslugaComplexAttributeList([ 'noprescr' ]);
		this.lpuFilterCombo.lastQuery = '';
		this.lpuFilterCombo.getStore().clearFilter();
		this.lpuFilterCombo.setBaseFilter(function(rec, id) {
			return (Ext.isEmpty(rec.get('Lpu_EndDate')) || Date.parseDate(rec.get('Lpu_EndDate'), 'Y-m-d') < Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
		});
		var isLabUfa = getRegionNick() == 'ufa' && this.PrescriptionType_Code == 11;
		this.analyzerTestNameFilterField.setContainerVisible(isLabUfa);
		this.setUslugaLoadMode(isLabUfa ? 'at' : 'uc');
		this.uslugaLoadModeToolbar.setVisible(isLabUfa);
		this.filterPanel.setHeight( isLabUfa ? 200 : 140 );
		this.doLayout();
		return true;
    },
    doReset: function(withLoad) {
        var base_form = this.filterPanel.getForm();
        base_form.reset();
        this.isOnlyPolka = (13==parseInt(this.initPrescriptionType_Code) && this.formParams.parentEvnClass_SysNick.inlist(['EvnVizitPL','EvnVizitPLStom']))?1:0;
        this.PrescriptionType_Code = this.initPrescriptionType_Code;
        base_form.findField('PrescriptionType_id').setValue(this.initPrescriptionType_Code);
        this.formParams.MedService_id = null;

        this.uslugaFilterCombo.clearValue();
        this.uslugaFilterCombo.getStore().removeAll();
        this.uslugaFilterCombo.setPrescriptionTypeCode(this.initPrescriptionType_Code);
        this.uslugaFilterCombo.getStore().baseParams.MedService_id = this.formParams.MedService_id || null;
        this.uslugaFilterCombo.getStore().baseParams.query = "";
        base_form.setValues(this.formParams);

        if (this.lpuFilterCombo.getValue()) {
            var rec = this.lpuFilterCombo.getStore().getById(this.lpuFilterCombo.getValue());
            if (!rec) {
                this.lpuFilterCombo.lastQuery = null;
                this.lpuFilterCombo.getStore().clearFilter();
                this.lpuFilterCombo.setValue(this.lpuFilterCombo.getValue());
            }
        }
        this.lpuFilterCombo.fireEvent('change', this.lpuFilterCombo, this.lpuFilterCombo.getValue(), null);
        if (withLoad) {
            this.loadUslugaFrame();
        }
        if(getRegionNick() == 'ufa') {
            this.setUslugaLoadMode('at');
        }
    },
    /**
     * Снимаем блокировку с бирок
     */
    onCancel: function()
    {
        var time_id, thas = this;
        for (var key in this.dataView.assocLockedTimetableMedServiceId) {
            time_id = this.dataView.assocLockedTimetableMedServiceId[key];
			if (time_id > 0) {
				sw.Promed.Direction.unlockTime(this, 'TimetableMedService', time_id, function(){
					delete thas.dataView.assocLockedTimetableMedServiceId[key];
				});
			} else {
				delete thas.dataView.assocLockedTimetableMedServiceId[key];
			}
        }
    },
    /**
     * Сохраняем назначение-направление в транзакции
     */
    _save: function(new_record, callback, saveOptions)
    {
		if ( typeof saveOptions != 'object' ) {
			saveOptions = new Object();
		}

        var thas = this,
			wnd = this,
            formParams = this.filterPanel.getForm().getValues(),
            key = new_record.get('EvnPrescr_key'),
            uslugaFrameRec = this.dataView.assocUslugaFrameRec[key],
            direction = this.dataView.assocDirection[key],
			electronicTalon_id = (this.electronicQueueData && this.electronicQueueData.electronicTalon_id ? this.electronicQueueData.electronicTalon_id : null),
            evnPrescrData = { //параметры для сохранения назначения
                PersonEvn_id: formParams.PersonEvn_id
                ,Server_id: formParams.Server_id
                ,parentEvnClass_SysNick: formParams.parentEvnClass_SysNick
				,DopDispInfoConsent_id: formParams.DopDispInfoConsent_id
				,StudyTarget_id: formParams.StudyTarget_id
	            ,HIVContingentTypeFRMIS_id: formParams.HIVContingentTypeFRMIS_id
	            ,HormonalPhaseType_id: formParams.HormonalPhaseType_id
	            ,CovidContingentType_id: formParams.CovidContingentType_id
            },
            params = { //параметры для функции создания направления
                person: {
                    Person_id: formParams.Person_id
                    ,PersonEvn_id: formParams.PersonEvn_id
                    ,Server_id: formParams.Server_id
	                ,HIVContingentTypeFRMIS_id: formParams.HIVContingentTypeFRMIS_id
	                ,HormonalPhaseType_id: formParams.HormonalPhaseType_id
	                ,CovidContingentType_id: formParams.CovidContingentType_id
                },
                needDirection: false,
                mode: 'nosave',
                loadMask: false,
                windowId: 'EvnPrescrUslugaInputWindow',
                onFailure: function(code){
					thas.blockAddRec = false;
                    //log(arguments);
                    thas.getLoadMask().hide();

					Ext.getCmp('EvnPrescrUslugaInputWindow').dataView.delPrescr(key, function() {
						thas.dataView.updateRec(new_record, {
							RecordStatus: ''
						});
						delete thas.dataView.assocUslugaFrameRec[key];
						delete thas.dataView.assocDirection[key];
						delete thas.dataView.assocLockedTimetableMedServiceId[key];

						thas.reloadEvnPrescr({
							reload: true
						});

						callback(new_record, null);
					});
                },
                callback: function(responseData, realResponseData){
                    thas.getLoadMask().hide();
                    //Устанавливаем признак того, что назначение сохранено в БД
                    thas.dataView.updateRec(new_record, {
                        RecordStatus: '',
						EvnDirection_id: Ext.isEmpty(responseData.EvnDirection_id)?null:responseData.EvnDirection_id,
						EvnQueue_id: Ext.isEmpty(responseData.EvnQueue_id)?null:responseData.EvnQueue_id
                    });
                    delete thas.dataView.assocUslugaFrameRec[key];
                    delete thas.dataView.assocDirection[key];
                    delete thas.dataView.assocLockedTimetableMedServiceId[key];

					// странно, почему никто никогда не использовал второй параметр кэллбэка?
					if (realResponseData && realResponseData.responseText) {
						var resp = Ext.util.JSON.decode(realResponseData.responseText);
						if (resp && resp.redirectedElectronicServiceName) {

							var electronicTalonNum = (thas.electronicQueueData && thas.electronicQueueData.electronicTalonNum ? thas.electronicQueueData.electronicTalonNum : null);
							var fullName = thas.Person_Surname + ' ' + thas.Person_Firname + ' ' + thas.Person_Secname;
							showPopupWarningMsg(
								'Пациент '
								+ fullName.toUpperCase()
								+ ' с талоном № '
								+ electronicTalonNum
								+' перенаправлен в пункт обслуживания '
								+ resp.redirectedElectronicServiceName.toUpperCase()
							);
						}
					}

					thas.reloadEvnPrescr({reload: true});
                    callback(new_record, null);
                },
                onCancel: function(){
					thas.blockAddRec = false;
                    thas.getLoadMask().hide();

					Ext.getCmp('EvnPrescrUslugaInputWindow').dataView.delPrescr(key, function(){
						thas.dataView.updateRec(new_record, {
							RecordStatus: ''
						});
						delete thas.dataView.assocUslugaFrameRec[key];
						delete thas.dataView.assocDirection[key];
						delete thas.dataView.assocLockedTimetableMedServiceId[key];

						thas.reloadEvnPrescr({
							reload: true
						});
						callback(new_record, null);
					});
                },
				onCancelQueue: function(evn_queue_id, callback){
					thas.blockAddRec = false;
					callback = callback || Ext.emptyFn;
					var dataView = Ext.getCmp('EvnPrescrUslugaInputWindow').dataView;
					var index = dataView.getStore().findBy(function(rec){
						return (rec.get('EvnQueue_id') == evn_queue_id);
					});
                    if (index >= 0 && dataView.getStore().getAt(index)) {
                        var queue_prescr_key = dataView.getStore().getAt(index).get('EvnPrescr_key');
                        dataView.delPrescr(queue_prescr_key, function() {
							delete thas.dataView.assocUslugaFrameRec[queue_prescr_key];
							delete thas.dataView.assocDirection[queue_prescr_key];
							delete thas.dataView.assocLockedTimetableMedServiceId[queue_prescr_key];

							thas.reloadEvnPrescr({
								reload: true
							});
							callback();
						});
                    } else {
						thas.reloadEvnPrescr({
							reload: true
						});
						callback();
					}
                }
            },
            checked = [],//список услуг для заказа
            save_url,
            prescr_code;


		direction.ARMType_id = this.userMedStaffFact.ARMType_id || null;
		// #183123
		direction.HIVContingentTypeFRMIS_id = formParams.HIVContingentTypeFRMIS_id || null;
		direction.CovidContingentType_id = formParams.CovidContingentType_id || null;
	    direction.HormonalPhaseType_id = formParams.HormonalPhaseType_id || null;

        switch (parseInt(new_record.get('PrescriptionType_id'))) {
            case 6:
                save_url = '/?c=EvnPrescr&m=saveEvnCourseProc';
                prescr_code = 'EvnPrescrProc';
                evnPrescrData.EvnCourseProc_id = null;
                evnPrescrData.EvnCourseProc_pid = formParams.Evn_pid;
                evnPrescrData.Morbus_id = null;
                evnPrescrData.EvnCourseProc_MinCountDay = null;
                evnPrescrData.MedPersonal_id = thas.userMedStaffFact.MedPersonal_id;
                evnPrescrData.LpuSection_id = thas.userMedStaffFact.LpuSection_id;
                evnPrescrData.EvnCourseProc_setDate = new_record.get('EvnPrescr_setDate');
                evnPrescrData.DurationType_id = new_record.get('DurationType_id');
                evnPrescrData.DurationType_recid = new_record.get('DurationType_recid');
                evnPrescrData.DurationType_intid = new_record.get('DurationType_intid');
                evnPrescrData.EvnCourseProc_MaxCountDay = new_record.get('CountInDay');
                evnPrescrData.EvnCourseProc_Duration = new_record.get('CourseDuration');
                evnPrescrData.EvnCourseProc_ContReception = new_record.get('ContReception');
                evnPrescrData.EvnCourseProc_Interval = new_record.get('Interval');
                evnPrescrData.UslugaComplex_id = uslugaFrameRec.get('UslugaComplex_id');
                checked.push(uslugaFrameRec.get('UslugaComplex_id'));
                break;
			case 7:
				save_url = '/?c=EvnPrescr&m=saveEvnPrescrOperBlock';
				prescr_code = 'EvnPrescrOperBlock';
				evnPrescrData.UslugaComplex_id = uslugaFrameRec.get('UslugaComplex_id');
				checked.push(uslugaFrameRec.get('UslugaComplex_id'));
				break;
            case 11:
                save_url = '/?c=EvnPrescr&m=saveEvnPrescrLabDiag';
                prescr_code = 'EvnPrescrLabDiag';
                if (uslugaFrameRec.compositionMenu) {
                    uslugaFrameRec.compositionMenu.items.each(function(item){
                        if (item.checked&&'checkAll'!=item.id&&item.setChecked!=undefined) {
                            checked.push(item.UslugaComplex_id);
                        }
                    });

					if (checked.length == 0) {
						callback(new_record, lang['naznachenie_nevozmojno_ne_vyibran_sostav_uslugi']);
						return false;
					}
                }
                evnPrescrData.UslugaComplex_id = uslugaFrameRec.get('UslugaComplex_id');
				evnPrescrData.EvnPrescrLimitData = thas.EvnPrescrLimitData;
				evnPrescrData.EvnPrescrLabDiag_CountComposit = checked.length;
                evnPrescrData.EvnPrescrLabDiag_uslugaList = checked.toString();
                break;
            case 12:
                save_url = '/?c=EvnPrescr&m=saveEvnPrescrFuncDiag';
                prescr_code = 'EvnPrescrFuncDiag';
                evnPrescrData.EvnPrescrFuncDiag_uslugaList = uslugaFrameRec.get('UslugaComplex_id');
                checked.push(uslugaFrameRec.get('UslugaComplex_id'));
                break;
            case 13:
                save_url = '/?c=EvnPrescr&m=saveEvnPrescrConsUsluga';
                prescr_code = 'EvnPrescrConsUsluga';
                evnPrescrData.UslugaComplex_id = uslugaFrameRec.get('UslugaComplex_id');
                checked.push(uslugaFrameRec.get('UslugaComplex_id'));
                break;
        }
        if (!save_url) {
            callback(new_record, lang['naznachenie_imeet_nepravilnyiy_tip']);
            return false;
        }

        evnPrescrData[prescr_code +'_id'] = null;
        evnPrescrData[prescr_code +'_pid'] = formParams.Evn_pid;
        evnPrescrData[prescr_code +'_IsCito'] = (new_record.get('EvnPrescr_IsCito'))?'on':'off';
        evnPrescrData[prescr_code +'_setDate'] = new_record.get('EvnPrescr_setDate') || getGlobalOptions().date;
        evnPrescrData[prescr_code +'_Descr'] = new_record.get('EvnPrescr_Descr') || '';

		// надо проверить есть ли такие направления и предложить объединить - пока только для лаборатории
		if (!saveOptions.modeDirection && !Ext.isEmpty(uslugaFrameRec.get('MedService_id')) && new_record.get('PrescriptionType_id') == 11) {
			thas.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				url: '/?c=EvnDirection&m=checkEvnDirectionExists',
				params: {
					Person_id: formParams.Person_id, // тот же чел
					MedService_id: uslugaFrameRec.get('MedService_id'), // та же служба
					UslugaComplex_id: uslugaFrameRec.get('UslugaComplex_id'), // тот же биоматериал (определим по услуге)
					EvnDirection_pid: formParams.Evn_pid // направление создано в рамках одного случая лечения/движения
				},
				callback: function(options, success, response) {
					thas.getLoadMask().hide();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.EvnDirections) {
							// если нашли направление задаём вопрос
							getWnd('swDirectionIncludeWindow').show({
								EvnDirections: response_obj.EvnDirections,
								callback: function(data) {
									if (data.include == 'yes') {
										saveOptions.modeDirection = 2;
										saveOptions.EvnDirection_id = data.EvnDirection_id;
										thas._save(new_record, callback, saveOptions);
									} else if (data.include == 'no') {
										saveOptions.modeDirection = 1;
										thas._save(new_record, callback, saveOptions);
									} else {
										params.onCancel();
									}
								}
							});
							/*sw.swMsg.show({
								buttons: {yes: "Продолжить", cancel: "Отмена"},
								fn: function (buttonId) {
									if (buttonId == 'yes') {
										saveOptions.modeDirection = 2;
										saveOptions.EvnDirection_id = response_obj.EvnDirection_id;
										thas._save(new_record, callback, saveOptions);
									} else if (buttonId == 'no') {
										saveOptions.modeDirection = 1;
										thas._save(new_record, callback, saveOptions);
									} else {
										params.onCancel();
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: lang['vklyuchit_uslugu'] + new_record.get('Usluga_List') + "в существующее направление?",
								title: lang['vopros']
							});*/
						} else {
							// иначе записываем
							saveOptions.modeDirection = 1;
							thas._save(new_record, callback, saveOptions);
						}
					} else {
						params.onCancel();
					}
				}
			});

			return;
		}
		
		// надо проверить на дублирование назначения-направления и вывести вопрос при совпадении в новом направлении услуги и места оказания с ранее созданным и активным
		if (!saveOptions.ignoreDoubleControl && !Ext.isEmpty(uslugaFrameRec.get('MedService_id'))) {
			thas.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				url: '/?c=EvnPrescr&m=checkDoubleUsluga',
				params: {
					EvnPrescr_pid: formParams.Evn_pid, // в то же посещение/движение
					PrescriptionType_id: new_record.get('PrescriptionType_id'), // тот же тип
					UslugaComplex_id: uslugaFrameRec.get('UslugaComplex_id'), // та же услуга
					MedService_id: uslugaFrameRec.get('MedService_id'), // та же служба
					checkRecordQueue: (Ext.isEmpty(uslugaFrameRec.get('TimetableMedService_id')) && Ext.isEmpty(uslugaFrameRec.get('TimetableResource_id')))
				},
				callback: function(options, success, response) {
					thas.getLoadMask().hide();

					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.EvnDirection_id && response_obj.EvnDirection_id > 0) {
							// задаём вопрос
							sw.swMsg.show({
								buttons: {yes: "Да", cancel: "Отмена"},
								fn: function (buttonId) {
									if (buttonId == 'yes') {
										saveOptions.ignoreDoubleControl = 1;
										thas._save(new_record, callback, saveOptions);
									} else {
										params.onCancel();
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: lang['suschestvuet_analogichnoe_napravlenie_prodoljit_zapis'],
								title: lang['vopros']
							});
						} else if(response_obj.checkQueue && response_obj.checkQueue ==1){
							sw.swMsg.alert(lang['oshibka'], lang['zapis_v_ochered_na_slujbu_zapreschena']);
							params.onCancel();
							
						}else {
							// иначе записываем
							saveOptions.ignoreDoubleControl = 1;
							thas._save(new_record, callback, saveOptions);
						}
					} else {
						params.onCancel();
					}
				}
			});
			return true;
		}
		
		if (getRegionNick() == 'kz') {
			var uslugacomplex_attributelist = uslugaFrameRec.get('UslugaComplex_AttributeList');
			if (uslugacomplex_attributelist && !!uslugacomplex_attributelist.split(',').find(function(el){return el == 'Kpn'})) {
				direction.PayTypeKAZ_id = 1;
			}
			else if (uslugacomplex_attributelist && uslugacomplex_attributelist.indexOf('IsNotKpn') >= 0) {
				direction.PayTypeKAZ_id = 2;
			}
			else {
				direction.PayTypeKAZ_id = null;
			}
		}
		
		saveOptions.payTypeKz = saveOptions.payTypeKz || direction.payTypeKz || null;
		
		if (getRegionNick() == 'kz' && thas.isPaidVisit) saveOptions.payTypeKz = 153;
		
		if (getRegionNick() == 'kz' && !saveOptions.payTypeKz) {
			var loadMask = new Ext.LoadMask(Ext.getBody(), { msg: "Получение источника финансирования..." });
			loadMask.show();
			Ext.Ajax.request({
				callback: function (options, success, response) {
					loadMask.hide();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						saveOptions.payTypeKz = response_obj.PayType_id;
						if (response_obj && response_obj.PayType_id) {
							thas._save(new_record, callback, saveOptions);
						} else {
							thas.blockAddRec = false;
						}
					}
					else {
						thas.blockAddRec = false;
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении источника финансирования'));
					}
				}.createDelegate(this),
				params: {
					DirType_id: sw.Promed.Direction.defineDirTypeByPrescrType(formParams.PrescriptionType_id),
					Person_id: formParams.Person_id,
					TreatmentClass_id: this.TreatmentClass_id,
					EvnDirection_setDate: new_record.get('EvnPrescr_setDate') || getGlobalOptions().date,
					Lpu_id: getGlobalOptions().lpu_id,
					Lpu_did: uslugaFrameRec.get('Lpu_id') || getGlobalOptions().lpu_id,
					LpuSectionProfile_id: direction.LpuSectionProfile_id,
					UslugaComplex_id: uslugaFrameRec.get('UslugaComplex_id'),
					Diag_id: formParams.Diag_id || null,
					PayTypeKAZ_id: direction.PayTypeKAZ_id
				},
				url: '/?c=ExchangeBL&m=getPayType'
			});
			return false;
		}
		
		
		if (getRegionNick() == 'kz') {
			if (saveOptions.payTypeKz == 152 && !saveOptions.ignoreEmptyPayType) {
				sw.swMsg.confirm("Внимание", "Источник финансирования для услуги не определился. Продолжить?", function(btn) {
					if (btn == 'yes'){
						saveOptions.ignoreEmptyPayType = 1;
						thas._save(new_record, callback, saveOptions);
					} else {
						params.onCancel();
					}
				});
				return false;
			} else {
				direction.PayType_id = saveOptions.payTypeKz;
			}
		}
		
        thas.getLoadMask(lang['pojaluysta_podojdite'] +
            lang['idet_sohranenie_naznacheniya_uslugi']+ new_record.get('Usluga_List')).show();
        Ext.Ajax.request({
            url: save_url,
            params: evnPrescrData,
            callback: function(o, s, r) {
                thas.getLoadMask().hide();
                if(s) {
                    var response_obj = Ext.util.JSON.decode(r.responseText);
                    if ( response_obj.success && response_obj.success === true) {
						//BOB - 22.04.2019
						if (thas.parentWindow_id) {
							Ext.getCmp(thas.parentWindow_id).fireEvent('success', thas.id, {EvnPrescr_id: response_obj[prescr_code +'_id']});
						}
						//BOB - 22.04.2019
                        if (6 == new_record.get('PrescriptionType_id')) {
                            direction.EvnPrescr_id = response_obj[prescr_code +'_id0'];
                        } else {
                            direction.EvnPrescr_id = response_obj[prescr_code +'_id'];
                        }
						direction.StudyTarget_id = formParams.StudyTarget_id;
                        //new_record.set('EvnPrescr_key', direction.EvnPrescr_id);
                        thas.dataView.updateRec(new_record, {
                            EvnPrescr_id: direction.EvnPrescr_id,
                            EvnXmlDir_id: response_obj['EvnXmlDir_id']||null,
                            EvnXmlDirType_id: response_obj['EvnXmlDirType_id']||null,
                            RecordStatus: 'inserted'
                        });
                        if (!direction.Lpu_did) {
                            // создаем только назначение
                            params.callback();
                            return true;
                        }
                        params.direction = direction;
                        params.order = {
                            LpuSectionProfile_id: direction.LpuSectionProfile_id
                            ,UslugaComplex_id: uslugaFrameRec.get('UslugaComplex_id')
                            ,checked: Ext.util.JSON.encode(checked)
                            ,Usluga_isCito: (new_record.get('EvnPrescr_IsCito'))?2:1
                            ,UslugaComplex_Name: uslugaFrameRec.get('UslugaComplex_Name')
                            ,UslugaComplexMedService_id: uslugaFrameRec.get('UslugaComplexMedService_id')
                            ,MedService_id: uslugaFrameRec.get('MedService_id')
                            ,Resource_id: uslugaFrameRec.get('Resource_id')
                            ,MedService_pzNick: uslugaFrameRec.get('pzm_MedService_Nick')
                            ,MedService_pzid: uslugaFrameRec.get('pzm_MedService_id')
                        };

						// если есть полезная нагрузка с формы "Параметры исследования"
						if (wnd.filterPanel.studyTargetPayloadData) {
							params.order.StudyTargetPayloadData = wnd.filterPanel.studyTargetPayloadData;
						}

                        direction['order'] = Ext.util.JSON.encode(params.order);
                        thas.getLoadMask(lang['pojaluysta_podojdite'] +
                            lang['idet_sohranenie_napravleniya_na_uslugu']+ new_record.get('Usluga_List')).show();
						if (saveOptions.modeDirection && saveOptions.modeDirection == 2) {
							// направление не создаётся, включаем в существующее
							Ext.Ajax.request({
								url: '/?c=EvnDirection&m=includeEvnPrescrInDirection',
								params: {
									EvnPrescr_id: direction.EvnPrescr_id, // назначение
									EvnDirection_id: saveOptions.EvnDirection_id, // существующее направление
									UslugaComplex_id: uslugaFrameRec.get('UslugaComplex_id'), // услуга исследования
									checked: Ext.util.JSON.encode(checked), // заказанные услуги
									order: direction['order']
								},
								callback: function(options, success, response) {
									thas.getLoadMask().hide();
									if (success) {
										if (response.responseText) {
											var response_obj = Ext.util.JSON.decode(response.responseText);
											if (response_obj.success) {
												params.callback({
													EvnDirection_id: saveOptions.EvnDirection_id
												});
											} else {
												params.onCancel();
											}
										}
									} else {
										params.onCancel();
									}
								}
							});
						} else if (uslugaFrameRec.get('TimetableMedService_id') > 0) {
                            params.Timetable_id = uslugaFrameRec.get('TimetableMedService_id');
                            params.order.TimetableMedService_id = uslugaFrameRec.get('TimetableMedService_id');
                            //sw.Promed.Direction.recordPerson(params);
                            direction['TimetableMedService_id'] = params.Timetable_id;
							direction['ElectronicTalon_id'] = electronicTalon_id;
							direction['ElectronicService_id'] = uslugaFrameRec.get('ElectronicService_id');
                            sw.Promed.Direction.requestRecord({
                                url: C_TTMS_APPLY,
                                loadMask: params.loadMask,
                                windowId: params.windowId,
                                params: direction,
                                //date: conf.date || null,
                                Timetable_id: params.Timetable_id,
                                fromEmk: false,
                                mode: 'nosave',
                                needDirection: false,
                                Unscheduled: false,
                                onHide: Ext.emptyFn,
                                onSaveRecord: function() {
                                    uslugaFrameRec.set('TimetableMedService_id', -1);
                                    uslugaFrameRec.commit();
                                },
                                onFailure: params.onFailure,
                                callback: params.callback,
								onCancel: params.onCancel,
								onCancelQueue: params.onCancelQueue
                            });
                        } else if (uslugaFrameRec.get('TimetableResource_id') > 0) {
                            params.Timetable_id = uslugaFrameRec.get('TimetableResource_id');
                            params.order.TimetableResource_id = uslugaFrameRec.get('TimetableResource_id');
                            //sw.Promed.Direction.recordPerson(params);
                            direction['TimetableResource_id'] = params.Timetable_id;
							direction['ElectronicTalon_id'] = electronicTalon_id;
							direction['ElectronicService_id'] = uslugaFrameRec.get('ElectronicService_id');
                            sw.Promed.Direction.requestRecord({
                                url: C_TTR_APPLY,
                                loadMask: params.loadMask,
                                windowId: params.windowId,
                                params: direction,
                                //date: conf.date || null,
                                Timetable_id: params.Timetable_id,
                                fromEmk: false,
                                mode: 'nosave',
                                needDirection: false,
                                Unscheduled: false,
                                onHide: Ext.emptyFn,
                                onSaveRecord: function() {
                                    uslugaFrameRec.set('TimetableResource_id', -1);
                                    uslugaFrameRec.commit();
                                },
                                onFailure: params.onFailure,
                                callback: params.callback,
								onCancel: params.onCancel,
								onCancelQueue: params.onCancelQueue
                            });
                        } else {
                            //sw.Promed.Direction.queuePerson(params);
							if (uslugaFrameRec.get('pzm_MedService_id')) {
								direction.MedService_pzid = uslugaFrameRec.get('pzm_MedService_id');
							}
							direction.UslugaComplex_did = direction.UslugaComplex_id;
							direction.MedService_did = direction.MedService_id;
							direction.Resource_did = direction.Resource_id;
                            direction.LpuSectionProfile_did = direction.LpuSectionProfile_id;
                            direction.EvnQueue_pid = direction.EvnDirection_pid;
                            direction.MedStaffFact_id = null;
							direction.Prescr = "Prescr";
                            /*
                            direction.LpuSection_did = null;
                            direction.MedService_did = null;
                            direction.LpuUnit_did = null;
                            */
                            sw.Promed.Direction.requestQueue({
                                params: direction,
                                loadMask: params.loadMask,
                                windowId: params.windowId,
                                onFailure: params.onFailure,
                                callback: params.callback
                            });
                        }
                    } else {
                        callback(new_record, response_obj.Error_Msg);
                    }
                } else {
                    callback(new_record, lang['oshibka_servera']);
                }
                return true;
            }
        });
        return true;
    },
    /**
     * Проверки перед добавлением записи в правую часть
     * @param rec
     * @return {Boolean}
     */
    _validate: function(rec)
    {
		if (rec.get('withResource')) {
			if (rec.get('ttr_Resource_id') && !rec.get('TimetableResource_id')) {
				sw.swMsg.alert(lang['oshibka'], lang['dobavlenie_nevozmojno_ne_vyibrano_vremya_priema_vyiberite_vremya_zapisi_na_priem']);
				return false;
			}
			if (rec.get('ttr_Resource_id') && rec.get('TimetableResource_id') < 0) {
				sw.swMsg.alert(lang['oshibka'], lang['dobavlenie_nevozmojno_na_eto_vremya_uje_byila_proizvedena_zapis_vyiberite_drugoe_vremya_zapisi_na_priem']);
				return false;
			}
		} else {
			if (rec.get('ttms_MedService_id') && !rec.get('TimetableMedService_id')) {
				sw.swMsg.alert(lang['oshibka'], lang['dobavlenie_nevozmojno_ne_vyibrano_vremya_priema_vyiberite_vremya_zapisi_na_priem']);
				return false;
			}
			if (rec.get('ttms_MedService_id') && rec.get('TimetableMedService_id') < 0) {
				sw.swMsg.alert(lang['oshibka'], lang['dobavlenie_nevozmojno_na_eto_vremya_uje_byila_proizvedena_zapis_vyiberite_drugoe_vremya_zapisi_na_priem']);
				return false;
			}
		}
        return true;
    },
    /**
     * Асинхронная проверка времени записи перед добавлением в правую часть
     * @param {Ext.data.Record} rec
     * @param {function} callback
     * @return {Boolean}
     */
    _checkBeforeLock: function(rec, callback)
    {
        var thas = this,
            formParams = this.filterPanel.getForm().getValues();

		if (rec.get('withResource')) {
			if (rec.get('EvnPrescr_IsCito'))
			{
				//надо создать доп.бирку и записывать на неё
				this.getLoadMask(lang['pojaluysta_podojdite_idet_sozdanie_dopolnitelnoy_birki']).show();
				Ext.Ajax.request({
					params: {
						Day: null,
						StartTime: null,
						Resource_id: rec.get('Resource_id'),
						MedService_id: rec.get('MedService_id'),
						TimetableExtend_Descr: ''
					},
					callback: function(options, success, response) {
						thas.getLoadMask().hide();
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.Error_Msg) {
								callback(false, response_obj.Error_Msg);
							} else if (response_obj.TimetableResource_id && response_obj.TimetableResource_begTime) {
								rec.set('TimetableResource_id', response_obj.TimetableResource_id);
								var dt = Date.parseDate(response_obj.TimetableResource_begTime, 'Y-m-d H:i:s');
								rec.set('TimetableResource_begTime', dt.format('d.m.Y H:i'));
								rec.commit();
								callback(true, null);
							}
							return true;
						}
						callback(false, lang['sozdanie_dopolnitelnoy_birki_ne_udalos']);
						return true;
					},
					url: '/?c=TimetableResource&m=addTTRDop'
				});
				return true;
			}

			if (!rec.get('TimetableResource_id')) {
				callback(true, null);
				return true;
			}

			this.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				params: {
					Person_id: formParams.Person_id,
					TimetableResource_id: rec.get('TimetableResource_id')
				},
				callback: function(options, success, response) {
					thas.getLoadMask().hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.Error_Msg) {
							callback(false, response_obj.Error_Msg);
						} else if (response_obj.Alert_Msg) {
							// Варианты действий: Записать/Выбрать другое время.
							sw.swMsg.show({
								buttons:Ext.Msg.YESNO,
								fn:function (buttonId) {
									if (buttonId == 'yes') {
										callback(true, null);
									} else {
										//callback(false, 'Выберите другое время!');
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: response_obj.Alert_Msg + '<br>Записать?',
								title: lang['vopros']
							});
						} else {
							callback(true, null);
						}
						return true;
					}
					callback(false, lang['proverka_vremeni_zapisi_pered_dobavleniem_ne_udalas']);
					return true;
				},
				url: '/?c=TimetableResource&m=checkBeforeLock'
			});
		} else if (rec.get('MedService_id') || rec.get('pzm_MedService_id')) {
			if (rec.get('EvnPrescr_IsCito'))
			{
				var MedService_id = rec.get('MedService_id');

				if (rec.get('pzm_MedService_id')) {
					MedService_id = rec.get('pzm_MedService_id');
				}

				//надо создать доп.бирку и записывать на неё
				this.getLoadMask(lang['pojaluysta_podojdite_idet_sozdanie_dopolnitelnoy_birki']).show();
				Ext.Ajax.request({
					params: {
						Day: null,
						StartTime: null,
						MedService_id: MedService_id,
						UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id'),
						TimetableExtend_Descr: ''
					},
					callback: function(options, success, response) {
						thas.getLoadMask().hide();
						if ( success ) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.Error_Msg) {
								callback(false, response_obj.Error_Msg);
							} else if (response_obj.TimetableMedService_id && response_obj.TimetableMedService_begTime) {
								rec.set('TimetableMedService_id', response_obj.TimetableMedService_id);
								var dt = Date.parseDate(response_obj.TimetableMedService_begTime, 'Y-m-d H:i:s');
								rec.set('TimetableMedService_begTime', dt.format('d.m.Y H:i'));
								rec.commit();
								callback(true, null);
							}
							return true;
						}
						callback(false, lang['sozdanie_dopolnitelnoy_birki_ne_udalos']);
						return true;
					},
					url: '/?c=TimetableMedService&m=addTTMSDop'
				});
				return true;
			}

			if (!rec.get('TimetableMedService_id')) {
				callback(true, null);
				return true;
			}

			this.getLoadMask(LOAD_WAIT).show();
			Ext.Ajax.request({
				params: {
					Person_id: formParams.Person_id,
					TimetableMedService_id: rec.get('TimetableMedService_id')
				},
				callback: function(options, success, response) {
					thas.getLoadMask().hide();
					if ( success ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.Error_Msg) {
							callback(false, response_obj.Error_Msg);
						} else if (response_obj.Alert_Msg) {
							// Варианты действий: Записать/Выбрать другое время.
							sw.swMsg.show({
								buttons:Ext.Msg.YESNO,
								fn:function (buttonId) {
									if (buttonId == 'yes') {
										callback(true, null);
									} else {
										//callback(false, 'Выберите другое время!');
									}
								},
								icon: Ext.MessageBox.QUESTION,
								msg: response_obj.Alert_Msg + '<br>Записать?',
								title: lang['vopros']
							});
						} else {
							callback(true, null);
						}
						return true;
					}
					callback(false, lang['proverka_vremeni_zapisi_pered_dobavleniem_ne_udalas']);
					return true;
				},
				url: '/?c=TimetableMedService&m=checkBeforeLock'
			});
		}
        return true;
    },
    /**
     * Загрузка данных в грид услуг левой части формы
     */
	loadUslugaFrame: function() {
		var win = this;
        this.uslugaFrame.removeAll();
        var base_form = this.filterPanel.getForm();
        if (!base_form.findField('UslugaComplex_id').PrescriptionType_Code) {
            sw.swMsg.alert(lang['soobschenie'], lang['vyiberite_tip_naznacheniya']);
            return false;
        }
        var ucombo = base_form.findField('UslugaComplex_id');

		if (!Ext.isEmpty(base_form.findField('SurveyTypeLink_lid').getValue()) && Ext.isEmpty(ucombo.getValue()) && ucombo.getStore().getCount() == 0) {
			// если услуга не выбрана и комбик пустой, то сперва нужно загрузить список услуг
			win.showLoadMask('Загрузка списка доступных услуг');
			ucombo.getStore().load({
				callback: function() {
					win.hideLoadMask();
					if (ucombo.getStore().getCount() > 0) {
						// выбираем первую попавшуюся услугу
						ucombo.setValue(ucombo.getStore().getAt(0).get('UslugaComplex_id'));
						// грузим заного фрейм
						win.loadUslugaFrame();
					}
				}
			});

			return false;
		}

        var lcombo = base_form.findField('Lpu_id');
        var baseParams = swCloneObject(ucombo.getStore().baseParams);
        baseParams.userLpuSection_id = this.userMedStaffFact.LpuSection_id;
        //пытаемся разрулить ситуацию когда getValue возвращает UslugaComplex_id одной услуги
        //а в комбо введено полностью или частично наименование другой услуги
        var id = ucombo.getValue();
        var str = ucombo.getRawValue()||'';
        var rec;
        if (id) {
            rec = ucombo.getStore().getById(id);
        }
        if (rec) {
            log(str);
            log(rec.get(ucombo.codeField) + '. ' + rec.get(ucombo.displayField));
            log((rec.get(ucombo.codeField) + '. ' + rec.get(ucombo.displayField)).toLowerCase().indexOf(str.toLowerCase()));
        }
        if (!rec || -1 == (rec.get(ucombo.codeField) + '. ' + rec.get(ucombo.displayField)).toLowerCase().indexOf(str.toLowerCase())) {
            ucombo.setValue(null);
            ucombo.setRawValue(str);
        }
        if (ucombo.getValue()) {
            baseParams.filterByUslugaComplex_id = ucombo.getValue();
			baseParams.filterByUslugaComplex_str = null;
        } else {
            baseParams.filterByUslugaComplex_str = ucombo.getRawValue()||null;
			baseParams.filterByUslugaComplex_id = null;
        }
        //пытаемся разрулить ситуацию когда getValue возвращает Lpu_id одной МО
        //а в комбо введено полностью или частично наименование другой МО
        id = lcombo.getValue();
        str = lcombo.getRawValue()||'';
        rec = null;
        if (id) {
            rec = lcombo.getStore().getById(id);
        }
        if (!rec || str.toLowerCase() != rec.get(lcombo.displayField).toLowerCase()) {
            lcombo.setValue(null);
            lcombo.setRawValue(str);
        }
        if (lcombo.getValue()) {
            baseParams.filterByLpu_str = null;
            baseParams.filterByLpu_id = lcombo.getValue();
        } else {
            baseParams.filterByLpu_str = lcombo.getRawValue()||null;
            baseParams.filterByLpu_id = null;
        }
        id = this.medServiceFilterCombo.getValue();
        if (id) {
            baseParams.filterByMedService_id = id;
        } else {
            baseParams.filterByMedService_id = null;
        }
        baseParams.isOnlyPolka = this.isOnlyPolka;
        baseParams.start = 0;

		if( getRegionNick() == 'ufa' ) {
			baseParams.filterByAnalyzerTestName = this.analyzerTestNameFilterField.getValue();
		}

		if (this.groupMode == 0) {
			baseParams.groupByMedService = 0;
			baseParams.limit = 100;
			this.uslugaFrame.getGrid().getBottomToolbar().pageSize = 100;
			this.uslugaFrame.pageSize = 100;
		} else {
			baseParams.groupByMedService = 1;
			baseParams.limit = 500;
			this.uslugaFrame.getGrid().getBottomToolbar().pageSize = 500;
			this.uslugaFrame.pageSize = 500;
		}

        this.uslugaFrame.loadData({
            globalFilters: baseParams
        });
        return true;
    },
    /**
     * Загрузка данных имеющихся назначений в правую часть формы
     * @param Evn_pid
     */
    loadDataViewStore:function(Evn_pid){
		var maskCfg = {
			msgCls:'hiddenMessageForLoadMask'
		};
        this.dataView.store.removeAll();
        this.dataView.resetNewIndex();
		var win = this;

		var parentEvnClass_SysNick = this.filterPanel.getForm().getValues().parentEvnClass_SysNick;
		var params = {};
		params.object =	'EvnPrescrPlan';
		params.object_id = parentEvnClass_SysNick+'_id';
		params.object_value	= Evn_pid;
		params.parent_object_id = 'EvnPrescrPlan_pid';
		params.parent_object_value = Evn_pid;
		params.forRightPanel = 1;

		win.showLoadMask(LOAD_WAIT);
		Ext.Ajax.request({
			failure: function(response, options) {
				win.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['pri_zagruzke_naznacheniy_proizoshla_oshibka']);
			},
			params: params,
			success: function(response, options) {
				win.hideLoadMask();
				if ( response.responseText ) {
					var result  = Ext.util.JSON.decode(response.responseText);
					if (result.html)
					{
						win.dataView.body.update(result.html);
						win.reloadEvnPrescr();
					}

					if (result.map) {
						win.createActionListForTpl(result.map);
						win.addHandlerInTpl(result.map, null, false);
					}
				}
			},
			url: '/?c=Template&m=getEvnForm'
		});
    },
	EvnPrescrLimitData: null, // данные с формы параметров референсных значений
    /**
     * Действия по нажатию кнопки "+" в левом гриде первой формы/по нажатию кнопки выбор
     * в форме выбора службы по известной услуге
     * В итоге в правой части в списке назначений появляется новое несохраненное направление-назначение
     */
	blockInsert: {},
	blockAddRec: false,
	doPrescr: function(key) {
		var rec = this.uslugaFrame.getGrid().getStore().getById(key);
		if (rec) {
			if (!Ext.isEmpty(rec.get('MedService_id'))) {
				this.doInsert(key);
			} else {
				this.selectLpu(key);
			}
		}
	},
	checkQueueExists: function(params) {
		Ext.Ajax.request({
			url: '/?c=Timetable&m=checkQueueExists',
			params: params,
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				log(response_obj);

				if (response_obj.warning) {
					var buttons = {
						yes: {
							text: lang['isklyuchit_iz_ocheredi'],
							iconCls: 'delete16',
							tooltip: lang['patsient_budet_isklyuchen_iz_ocheredi_i_zapisan_na_vyibrannuyu_birku']
						},
						cancel: {
							text: lang['otmena'],
							iconCls: 'close16',
							tooltip: lang['otmena_zapisi_patsienta_bez_isklyucheniya_ego_iz_ocheredi']
						}
					};
					if (getRegionNick() != 'perm') {
						buttons.no = {
							text: lang['zapisat'],
							iconCls: 'ok16',
							tooltip: lang['patsient_budet_zapisan_na_vyibrannuyu_birku_pri_etom_nahodyas_v_ocheredi']
						};
					}
					sw.swMsg.show({
						buttons: buttons,
						msg: response_obj.warning,
						title: lang['vnimanie'],
						icon: Ext.MessageBox.WARNING,
						fn: function(buttonId){
							var AnswerQueue = null;
							if (buttonId === 'yes') {
								params.callback(1);
								/*conf.queue = answer.queue;
								this.recordFromQueue(conf);*/
							} else if (buttonId === 'no') {
								params.callback(0);
								/*conf.params['AnswerQueue'] = 0;
								this.requestRecord(conf);*/
							}
						}.createDelegate(this)
					});
				}
			},
		});
	},
    doInsert: function(key, options){
		if ( typeof options != 'object' ) {
			options = new Object();
		}
		if (this.blockInsert[key] && !options.ignoreBlockInsert) {
			//showSysMsg('Повторное создание назначения в течение 5 секунд не возможно.', '', 'warning');
			return false;
		}

		this.blockInsert[key]= true; // блокируем повторные нажатия для выбранной услуги в течение короткого времени
		options.ignoreBlockInsert = true; // для игнорирования блокировки в случае вызова функции из самой себя.

		var thas = this;
		// снимаем блокировку кнопки через пару секунд
		setTimeout(function(){
			thas.blockInsert[key] = false;
		}, 5000);

		var base_form = this.filterPanel.getForm();
        var rec = this.uslugaFrame.getGrid().getStore().getById(key);
        if (!rec) {
            return false;
        }
        if (!this._validate(rec)) {
            return false;
        }
        var PrescriptionType_id = thas.filterPanel.getForm().findField('UslugaComplex_id').PrescriptionType_Code;
		if ( PrescriptionType_id==11 && !options.ignoreEvnPrescrLimitWindow ) {
			thas.EvnPrescrLimitData = null;
			// Проверяем есть ли вообще какие то лимиты и невычисляемые параметры по ним по заданной услуге
			Ext.Ajax.request({
				failure: function(response) {
				},
				params: {
					MedService_id: rec.get('MedService_id'),
					UslugaComplex_id: rec.get('UslugaComplex_id'),
					Person_id: base_form.findField('Person_id').getValue()
				},
				success: function(response) {
					// При нажатии функциональной кнопки «Назначить» перед созданием направления открывать форму «Параметры референсных значений» (refs #30185)
					if (response.responseText)
					{
						var answer = Ext.util.JSON.decode(response.responseText);
						if (answer.success && answer.IsLimits == 1) {
							getWnd('swEvnPrescrLimitEditWindow').show({
								MedService_id: rec.get('MedService_id'),
								UslugaComplex_id: rec.get('UslugaComplex_id'),
								Person_id: base_form.findField('Person_id').getValue(),
								callback: function(EvnPrescrLimitData) {
									thas.EvnPrescrLimitData = EvnPrescrLimitData;
									options.ignoreEvnPrescrLimitWindow = true;
									thas.doInsert(key, options);
								}
							});
						} else {
							options.ignoreEvnPrescrLimitWindow = true;
							thas.doInsert(key, options);
						}
					}
				},
				url: '/?c=EvnPrescrLimit&m=checkLimits'
			});

			return false;
		}

		if (!options.ignoreSelectStudyTarget) {
			this.selectStudyTarget(key, function(key) {
				options.ignoreSelectStudyTarget = true;
				thas.doInsert(key, options);
			});

			return false;
		}

        if ( PrescriptionType_id==11 && rec.get('isComposite') == 1) {
            if (!rec.compositionMenu) {
                this.loadCompositionMenu(function(){
                    thas.doInsert(key, options);
                }, rec, function() {
					sw.swMsg.alert(lang['oshibka'], lang['naznachenie_nevozmojno_ne_vyibran_sostav_uslugi']);
					return false;
				});
                return false;
            }
            var checked = [];
            rec.compositionMenu.items.each(function(item){
                if (item.checked) {
                    checked.push(item.UslugaComplex_id);
                }
            });
            if (checked.length == 0) {
                sw.swMsg.alert(lang['oshibka'], lang['naznachenie_nevozmojno_ne_vyibran_sostav_uslugi']);
                return false;
            }
        }
        if (!options.ignoreCheckBeforeLock) {
            this._checkBeforeLock(rec, function(result, msg) {
                if (result) {
					options.ignoreCheckBeforeLock = true;
                    thas.doInsert(key, options);
                } else {
                    sw.swMsg.alert(lang['soobschenie'], msg);
                }
            });
            return true;
        }

        var formParams = this.filterPanel.getForm().getValues();
        formParams.uslugaList = ''+rec.get('UslugaComplex_id');

		if ( rec.get('withResource') && rec.get('TimetableResource_id') > 0 && rec.get('TimetableResource_begTime') ) {
			formParams.EvnPrescr_setDate = Date.parseDate(rec.get('TimetableResource_begTime'), 'd.m.Y H:i').format('d.m.Y');
		} else if ( rec.get('TimetableMedService_id') > 0 && rec.get('TimetableMedService_begTime') ) {
			formParams.EvnPrescr_setDate = Date.parseDate(rec.get('TimetableMedService_begTime'), 'd.m.Y H:i').format('d.m.Y');
		}

		log('PrescriptionType_id', formParams.PrescriptionType_id);
		
		formParams.isPaidVisit = (thas.isPaidVisit)?thas.isPaidVisit:null;

		formParams.isPaidVisit = (thas.isPaidVisit)?thas.isPaidVisit:null;

        if (formParams.PrescriptionType_id == 6) {
            var win = getWnd('swPolkaEvnPrescrProcEditWindow');
            var params = {
                action: 'add',
                mode: 'nosave',
                parentEvnClass_SysNick: formParams.parentEvnClass_SysNick,
                callback: function(data) {
                    // log(data);
                    formParams.DurationTypeP_Nick = data.EvnPrescrProcData.DurationTypeP_Nick||"дн";
                    formParams.DurationType_id = data.EvnPrescrProcData.DurationType_id;
                    formParams.DurationTypeN_Nick = data.EvnPrescrProcData.DurationTypeN_Nick||"дн";
                    formParams.DurationType_recid = data.EvnPrescrProcData.DurationType_recid;
                    formParams.DurationTypeI_Nick = data.EvnPrescrProcData.DurationTypeI_Nick||"дн";
                    formParams.DurationType_intid = data.EvnPrescrProcData.DurationType_intid;
                    formParams.ContReception = data.EvnPrescrProcData.EvnCourseProc_ContReception;
                    formParams.CountInDay = data.EvnPrescrProcData.EvnCourseProc_MaxCountDay;
                    formParams.CourseDuration = data.EvnPrescrProcData.EvnCourseProc_Duration;
                    formParams.Interval = data.EvnPrescrProcData.EvnCourseProc_Interval;
                    formParams.EvnPrescr_setDate = data.EvnPrescrProcData.EvnCourseProc_setDate;
                    formParams.EvnPrescr_Descr = data.EvnPrescrProcData.EvnPrescrProc_Descr;
                    formParams.EvnPrescr_IsCito = data.EvnPrescrProcData.EvnPrescrProc_IsCito;
                    rec.set('EvnPrescr_IsCito',(data.EvnPrescrProcData.EvnPrescrProc_IsCito == 'on'));
                    rec.commit();

                    thas._createDirection(rec, formParams, function(direction){
                        thas.dataView.addRec(rec, formParams, direction);
                    });
                },
                onHide: function() {
                    //
                }
            };
            params.formParams = {
                EvnCourseProc_pid: formParams.Evn_pid
                ,EvnCourseProc_id: null
                ,Morbus_id: null
                ,MedPersonal_id: thas.userMedStaffFact.MedPersonal_id
                ,LpuSection_id: thas.userMedStaffFact.LpuSection_id
                ,EvnCourseProc_setDate: Date.parseDate((/*formParams.EvnPrescr_setDate ||*/ getGlobalOptions().date), 'd.m.Y')
                ,PersonEvn_id: formParams.PersonEvn_id
                ,Server_id: formParams.Server_id
                ,UslugaComplex_id: rec.get('UslugaComplex_id')
                ,EvnPrescrProc_IsCito: rec.get('EvnPrescr_IsCito')
            };
            if (win.isVisible()) {
                win.hide();
            }
            win.show(params);
        } else {
            this._createDirection(rec, formParams, function(direction){
                thas.dataView.addRec(rec, formParams, direction);
            });
        }
        return true;
    },
    /**
     * Открытие формы ввода данных направления
     */
    _createDirection: function(uslugaFrameRec, formParams, callback){
        var direction = {
            LpuUnitType_SysNick: 'parka'
            ,PrehospDirect_id: (getRegionNick() == 'kz')
				? (uslugaFrameRec.get('Lpu_id') == getGlobalOptions().lpu_id) ? 15 : 16
				: (uslugaFrameRec.get('Lpu_id') == getGlobalOptions().lpu_id) ? 1 : 2
            ,PrescriptionType_Code: formParams.PrescriptionType_id
            ,EvnDirection_pid: formParams.Evn_pid
            ,Evn_id: formParams.Evn_pid
			,EvnDirection_IsCito: (uslugaFrameRec.get('EvnPrescr_IsCito') === true || uslugaFrameRec.get('EvnPrescr_IsCito')=='2')?2:1
            ,DirType_id: sw.Promed.Direction.defineDirTypeByPrescrType(formParams.PrescriptionType_id)
            ,Diag_id: formParams.Diag_id || null
            ,MedPersonal_id: this.userMedStaffFact.MedPersonal_id //ид медперсонала, который направляет
            ,Lpu_id: this.userMedStaffFact.Lpu_id
            ,Lpu_sid: this.userMedStaffFact.Lpu_id
            ,LpuSection_id: this.userMedStaffFact.LpuSection_id
            ,From_MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id
            ,UslugaComplex_id: uslugaFrameRec.get('UslugaComplex_id')
            ,UslugaComplex_did: uslugaFrameRec.get('UslugaComplex_id')
            ,LpuSection_Name: uslugaFrameRec.get('LpuSection_Name')
            ,LpuSection_did: uslugaFrameRec.get('LpuSection_id')
            ,LpuSection_uid: uslugaFrameRec.get('LpuSection_id')
            ,LpuSectionProfile_id: uslugaFrameRec.get('LpuSectionProfile_id')
            ,EvnPrescr_id: null
            ,Resource_id: uslugaFrameRec.get('Resource_id')
            ,Resource_did: uslugaFrameRec.get('Resource_id')
            ,Resource_Name: uslugaFrameRec.get('Resource_Name')
            ,MedService_id: uslugaFrameRec.get('MedService_id')
            ,MedService_did: uslugaFrameRec.get('MedService_id')
            ,MedService_Nick: uslugaFrameRec.get('MedService_Nick')
            ,MedServiceType_SysNick: uslugaFrameRec.get('MedServiceType_SysNick')
            ,Lpu_did: uslugaFrameRec.get('Lpu_id')
            ,LpuUnit_did: uslugaFrameRec.get('LpuUnit_id')
            ,time: (uslugaFrameRec.get('withResource')?uslugaFrameRec.get('TimetableResource_begTime'):uslugaFrameRec.get('TimetableMedService_begTime'))||null
            ,Server_id: formParams.Server_id
            ,Person_id: formParams.Person_id
            ,PersonEvn_id: formParams.PersonEvn_id
            ,MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id //ид медперсонала, который направляет
            ,MedPersonal_did: null //ид медперсонала, куда направили
            ,timetable: 'TimetablePar'
            ,TimetableMedService_id: uslugaFrameRec.get('TimetableMedService_id')
            ,TimetableResource_id: uslugaFrameRec.get('TimetableResource_id')
            ,withResource: uslugaFrameRec.get('withResource')
            ,EvnQueue_id: null//
            ,QueueFailCause_id: null//
            ,EvnUsluga_id: null//Сохраненный заказ
            ,EvnDirection_id: null
	        ,PayType_id: (this.isPaidVisit)?'153':null
	        ,TreatmentClass_id: (this.TreatmentClass_id)?this.TreatmentClass_id:null
	        ,payTypeKz: (this.payTypeKz)?this.payTypeKz:null
	        //,PayTypeKAZ_id: (this.PayTypeKAZ_id)?this.PayTypeKAZ_id:null
        };
        // параметры для формы выписки эл.направления
        var form_params = direction;
        form_params.Person_Surname = this.Person_Surname;
        form_params.Person_Firname = this.Person_Firname;
        form_params.Person_Secname = this.Person_Secname;
        form_params.Person_Birthday = this.Person_Birthday;
        var params = {
            action: 'add',
            mode: 'nosave',
            callback: function(data){
                if (data && data.evnDirectionData) {
                    var o = data.evnDirectionData;
                    //принимаем только то, что могло измениться
                    direction.EvnDirection_Num = o.EvnDirection_Num;
                    direction.DirType_id = o.DirType_id;
                    direction.Diag_id = o.Diag_id;
                    direction.LpuSectionProfile_id = o.LpuSectionProfile_id;
                    direction.EvnDirection_Descr = o.EvnDirection_Descr;
                    direction.EvnDirection_setDate = o.EvnDirection_setDate;
                    direction.MedStaffFact_id = o.MedStaffFact_id;
                    direction.MedPersonal_id = o.MedPersonal_id;
                    direction.LpuSection_id = o.LpuSection_id;
                    direction.MedStaffFact_zid = o.MedStaffFact_zid;
                    direction.MedPersonal_zid = o.MedPersonal_zid;
                    direction.Lpu_did = o.Lpu_did;
                    direction.From_MedStaffFact_id = o.From_MedStaffFact_id;
                    direction.EvnXml_id = o.EvnXml_id;
                    direction.EvnDirection_desDT = o.EvnDirection_desDT;
                    direction.EvnDirectionOper_IsAgree = o.EvnDirectionOper_IsAgree;
                    direction.PayType_id = o.PayType_id;
	                direction.TreatmentClass_id = o.TreatmentClass_id;
	                direction.payTypeKz = o.payTypeKz;
	                //direction.PayTypeKAZ_id = o.PayTypeKAZ_id;
                    callback(direction);
                }
            },
            params: form_params
        };
        
        params.isPaidVisit = (direction.PayType_id == '153')?true:false;
        
        if (!uslugaFrameRec.get('MedService_id') && !uslugaFrameRec.get('Lpu_id')) {
            // будем сохранять только назначение
            direction.Lpu_did = null;
            callback(direction);
            return true;
        }
        if (!uslugaFrameRec.get('MedService_id') && uslugaFrameRec.get('Lpu_id')) {
            sw.Promed.Direction.openDirectionEditWindow(params);
            return true;
        }

		if (direction.PrescriptionType_Code == '7') {
			// для назначений в оперблок нужна форма направления,
			// т.к. нужна "отметка о согласии пациента/представителя пациента" и "предоперационный эпикриз"
			sw.Promed.Direction.openDirectionEditWindow(params);
		} else if (getGlobalOptions().lpu_id == uslugaFrameRec.get('Lpu_id')) {
            //возвращаем параметры автоматического направления
            direction.EvnDirection_IsAuto = 2;
            direction.EvnDirection_setDate = getGlobalOptions().date;
            direction.EvnDirection_Num = '0';
            direction.MedPersonal_zid = '0';
            callback(direction);
        } else {
            //показать форму создания электронного направления без возможности отказаться от его создания
            //params.disableClose = true;
            // Разрешить отмену создания направления при создании назначения услуги другого ЛПУ.
            // В этом случае отменять направление и назначение.
            sw.Promed.Direction.openDirectionEditWindow(params);
        }
        return true;
    },
    /**
     * Отображение состава услуги для выбора
     */
    showComposition: function(key){
        var thas = this,
            rec = this.uslugaFrame.getGrid().getStore().getById(key);
        if (!rec) {
            return false;
        }
        if (!rec.compositionMenu) {
            this.loadCompositionMenu(function(menu){
                menu.show(Ext.get('composition_'+ key),'tl-bl?');
                rec.isVisibleCompositionMenu = true;
                thas._lastRowKey = key;
            }, rec);
            return true;
        }
        if (thas._lastRowKey == key) {
            if (rec.isVisibleCompositionMenu) {
                if (!rec.compositionMenu.hidden) {
                    rec.compositionMenu.hide();
                }
                rec.isVisibleCompositionMenu = false;
                //при повторном клике меню отобразится
                return true;
            }
        } else {
            rec.isVisibleCompositionMenu = false;
        }
        rec.compositionMenu.show(Ext.get('composition_'+ key),'tl-bl?');
        rec.isVisibleCompositionMenu = true;
        thas._lastRowKey = key;
        return true;
    },
    /**
     * Открывает справочник ЛПУ для выбора.
     * Если выбрано ЛПУ, открывать создание направления,
     * если ЛПУ не указано - просто создавать назначение.
     */
    selectLpu: function(key){
        var rec = this.uslugaFrame.getGrid().getStore().getById(key);
        if (!rec) {
            return false;
        }
        var win = getWnd('swLpuSelectWindow');
        if (win.isVisible()) {
            win.hide();
        }
        var thas = this;
        win.show({
            callback: function(sel_rec) {
                //открывать создание направления
                rec.set('Lpu_id', sel_rec.get('Lpu_id'));
                rec.set('Lpu_Nick', sel_rec.get('Lpu_Nick'));
                rec.commit();
                thas.doInsert(key);
            },
            onHide: function(hasSelect) {
                if (hasSelect == false) {
                    // просто создавать назначение
                    thas.doInsert(key);
                }
            }
        });
        return true;
    },
    /**
     * Форма указания параметров исследования
     */
    selectStudyTarget: function(key, callback){

		var wnd = this;
        var record = wnd.uslugaFrame.getGrid().getStore().getById(key);
        var form = wnd.filterPanel.getForm();
        var formParams = wnd.filterPanel.getForm().getValues();

		if (!record) { return false }
		var PrescriptionType_id = form.findField('PrescriptionType_id').getValue();

		var modalWnd = getWnd('swStudyTargetSelectWindow');
        if (modalWnd.isVisible()) { modalWnd.hide() }

	    var UslugaComplex_id = (record.get('UslugaComplex_id')) ? record.get('UslugaComplex_id') : null;
        var UslugaComplex_List = null;
        if (record.compositionMenu) {
	        UslugaComplex_List = [];
	        record.compositionMenu.items.each((item) => {
		        if (!item.UslugaComplex_id) return;
		        UslugaComplex_List.push(item.UslugaComplex_id);
	        });
	        if (UslugaComplex_id) UslugaComplex_List.push(UslugaComplex_id);
        }

		modalWnd.show({
			params:{
				parentEvnClass_SysNick: (wnd.parentEvnClass_SysNick) ? wnd.parentEvnClass_SysNick : null,
				PrescriptionType_Code: (wnd.PrescriptionType_Code) ? wnd.PrescriptionType_Code : null,
				UslugaComplex_List: UslugaComplex_List,
				UslugaComplex_id: UslugaComplex_id,
				Person_id: formParams.Person_id ? formParams.Person_id : null,
				UslugaComplexMedService_pid: record.get('UslugaComplexMedService_id'),
				Lpu_id: record.get('Lpu_id')
			},
            callback: function(data) {

				form.findField('StudyTarget_id').setValue(data.StudyTarget_id);
				form.findField('HIVContingentTypeFRMIS_id').setValue(data.HIVContingentTypeFRMIS_id);
				form.findField('CovidContingentType_id').setValue(data.CovidContingentType_id);
				form.findField('HormonalPhaseType_id').setValue(data.HormonalPhaseType_id);
				
				if (data.studyTargetPayloadData) wnd.filterPanel.studyTargetPayloadData = data.studyTargetPayloadData;

                callback(key);
            }
        });

        return true;
    },
    /**
     * @param {object} params
     * linkBlock: Ext.get('uc2011_MedServiceSelectLink_'+ record.id),
     * comboBlock: Ext.get('uc2011_MedServiceSelectComboWrap_'+ record.id),
     * frame: this,
     * grid: grid,
     * record: record,
     * rowIndex: index
     */
    showMedServiceSelectCombo: function(params)
    {
        var thas = this;
        if (params.frame.medServiceSelectCombo) {
            // должен отображаться только один
            params.frame.medServiceSelectCombo.doDestroy();
        }
        var baseParams = this._getMedServiceStoreBaseParams(params.record);
        params.linkBlock.setDisplayed('none');
        params.comboBlock.setDisplayed('block');
        var initKey;
        if (params.record.get('pzm_MedService_id')) {
            initKey = params.record.get('pzm_MedService_id') +
                params.record.get('UslugaComplexMedService_id');
        } else {
            initKey = params.record.get('MedService_id') +
                params.record.get('UslugaComplexMedService_id');
        }
        params.frame.medServiceSelectCombo = new sw.Promed.SwUc2011MedServiceSelectCombo({
            id: 'uc2011_MedServiceSelectCombo_'+ params.record.id,
            initKey: initKey,
            uslugaFrameRecord: params.record,
            width: 190,
            listWidth: 500,
            hideLabel: true,
            renderTo: params.comboBlock.id,
            doDestroy: function()
            {
				// восстановить исходные данные, если есть
				if (this._dataToRestore) {
					this._dataToRestore.el.dom.innerHTML = this._dataToRestore.content;
				}

                params.comboBlock.setDisplayed('none');
                params.linkBlock.setDisplayed('block');
                this.destroy();
                params.frame.medServiceSelectCombo = null;
            },
            onTrigger2Click: function()
            {
                if (this.disabled)
                    return false;
                this.collapse();
                thas.showMedServiceAll(params.record.id);
                return true;
            },
            listeners:
            {
                render: function(f) {
                },
                blur: function(f) {
                    f.doDestroy();
                },
                select: function(f,rec) {
                    var key = params.record.get(params.frame.keyColumnName),
                        el = Ext.get('render_timetable_begtime_' + key);
                    // запомнить исходные данные
                    if (!f._dataToRestore) {
                        f._dataToRestore = {
                            el: el,
                            content: el.dom.innerHTML
                        };
                    }
					var begTime = rec.get('TimetableMedService_begTime');
					if (!Ext.isEmpty(rec.get('TimetableResource_begTime'))) {
						begTime = rec.get('TimetableResource_begTime');
					}
                    // обновить колонку "Запись"
                    el.dom.innerHTML = thas.renderTimetableBegTime(begTime, key);
                },
                change: function(f,n) {
                    // удалить исходные данные, если есть
                    if (f._dataToRestore) {
                        delete f._dataToRestore;
                    }
                    if (n && n != f.initKey) {
                        var sel_rec = f.getStore().getById(n);
                        if (sel_rec) {
							thas._onMedServiceSelect(sel_rec, f.uslugaFrameRecord, f.getStore().baseParams);
                        }
                    }
                }
            }
        });
        params.frame.medServiceSelectCombo.getStore().baseParams = baseParams;
        params.frame.medServiceSelectCombo.focus(true, 100);
        var f = params.frame.medServiceSelectCombo;
        f.getStore().loadData([{
            UslugaComplexMedService_key: f.initKey,
            UslugaComplexMedService_id: f.uslugaFrameRecord.get('UslugaComplexMedService_id'),
            UslugaComplex_2011id: f.uslugaFrameRecord.get('UslugaComplex_2011id'),
            UslugaComplex_id: f.uslugaFrameRecord.get('UslugaComplex_id'),
            UslugaComplex_Code: f.uslugaFrameRecord.get('UslugaComplex_Code'),
            UslugaComplex_Name: f.uslugaFrameRecord.get('UslugaComplex_Name'),
            MedService_id: f.uslugaFrameRecord.get('MedService_id'),
            MedServiceType_id: f.uslugaFrameRecord.get('MedServiceType_id'),
            MedServiceType_SysNick: f.uslugaFrameRecord.get('MedServiceType_SysNick'),
            MedService_Name: f.uslugaFrameRecord.get('MedService_Name'),
            MedService_Nick: f.uslugaFrameRecord.get('MedService_Nick'),
            Lpu_id: f.uslugaFrameRecord.get('Lpu_id'),
            Lpu_Nick: f.uslugaFrameRecord.get('Lpu_Nick'),
            LpuBuilding_id: f.uslugaFrameRecord.get('LpuBuilding_id'),
            LpuBuilding_Name: f.uslugaFrameRecord.get('LpuBuilding_Name'),
            LpuUnit_id: f.uslugaFrameRecord.get('LpuUnit_id'),
            LpuUnit_Name: f.uslugaFrameRecord.get('LpuUnit_Name'),
            LpuUnitType_id: f.uslugaFrameRecord.get('LpuUnitType_id'),
            LpuUnitType_SysNick: f.uslugaFrameRecord.get('LpuUnitType_SysNick'),
            LpuSection_id: f.uslugaFrameRecord.get('LpuSection_id'),
            LpuSection_Name: f.uslugaFrameRecord.get('LpuSection_Name'),
            LpuSectionProfile_id: f.uslugaFrameRecord.get('LpuSectionProfile_id'),
            ttms_MedService_id: f.uslugaFrameRecord.get('ttms_MedService_id'),
            TimetableMedService_id: f.uslugaFrameRecord.get('TimetableMedService_id'),
            TimetableMedService_begTime: f.uslugaFrameRecord.get('TimetableMedService_begTime'),

            lab_MedService_id: (f.uslugaFrameRecord.get('pzm_MedService_id')) ?
                f.uslugaFrameRecord.get('MedService_id') : null,
            pzm_Lpu_id: f.uslugaFrameRecord.get('pzm_Lpu_id')||null,
            pzm_MedService_id: f.uslugaFrameRecord.get('pzm_MedService_id')||null,
            pzm_MedServiceType_id: f.uslugaFrameRecord.get('pzm_MedServiceType_id')||null,
            pzm_MedServiceType_SysNick: f.uslugaFrameRecord.get('pzm_MedServiceType_SysNick')||'',
            pzm_MedService_Name: f.uslugaFrameRecord.get('pzm_MedService_Name')||'',
            pzm_MedService_Nick: f.uslugaFrameRecord.get('pzm_MedService_Nick')||'',

			TimetableResource_begTime: f.uslugaFrameRecord.get('TimetableResource_begTime'),
			TimetableResource_id: f.uslugaFrameRecord.get('TimetableResource_id'),
			Resource_id: f.uslugaFrameRecord.get('Resource_id'),
			Resource_Name: f.uslugaFrameRecord.get('Resource_Name'),
			ttr_Resource_id: f.uslugaFrameRecord.get('ttr_Resource_id')
        }],true);
        f.setValue(f.initKey);
        if (f.uslugaFrameRecord.get('pzm_MedService_Nick')) {
            f.setRawValue(f.uslugaFrameRecord.get('pzm_MedService_Nick'));
        } else {
            f.setRawValue(f.uslugaFrameRecord.get('MedService_Nick'));
        }
    },
    _getMedServiceStoreBaseParams: function(rec)
    {
        var base_form = this.filterPanel.getForm();
        var lcombo = base_form.findField('Lpu_id');
        var ucombo = base_form.findField('UslugaComplex_id');
        var filterByLpu_str = '';
        var filterByLpu_id = null;
        if (lcombo.getValue()) {
            filterByLpu_id = lcombo.getValue();
        } else {
            filterByLpu_str = lcombo.getRawValue()||'';
        }
        return {
            start: 0,
            limit: 100,
            userLpuSection_id: this.userMedStaffFact.LpuSection_id,
            PrescriptionType_Code: ucombo.PrescriptionType_Code || this.PrescriptionType_Code,
            isOnlyPolka: this.isOnlyPolka,
            filterByUslugaComplex_id: (rec && rec.get('UslugaComplex_id')) || ucombo.getValue(),
            filterByLpu_id: filterByLpu_id,
            filterByLpu_str: filterByLpu_str
        };
    },
	refineTimetableData: function(key) {
		var that = this,
            rec = this.uslugaFrame.getGrid().getStore().getById(key),
			params = Ext.apply({}, this.uslugaFrame.getGrid().getStore().baseParams),
			url = this.uslugaFrame.dataUrl;	
		if (!rec || !rec.get('UslugaComplex_id')) return false;
		params.filterByUslugaComplex_id = rec.get('UslugaComplex_id');
		params.filterByMedService_id = rec.get('MedService_id');		
		params.noDateLimit = 1;
        this.getLoadMask(LOAD_WAIT).show();
		Ext.Ajax.request({
            params: params,
            url: url,
            callback: function(options, success, response) {
                that.getLoadMask().hide();
                if (success) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.data && response_obj.data.length > 0) {
						var begTime = rec.get('withResource') ? response_obj.data[0].TimetableResource_begTime : response_obj.data[0].TimetableMedService_begTime;
						if (Ext.isEmpty(begTime)) {
							showSysMsg('Свободных бирок не найдено');
						} else {
							if (rec.get('withResource')) {
								rec.set('TimetableResource_begTime', begTime);
							} else {
								rec.set('TimetableMedService_begTime', begTime);
							}
							rec.commit();
							that.doApply(key);
						}
					}
                }
            }
        });
	},
    renderTimetableBegTime: function(dt_str, key)
    {
        var text = lang['v_ochered'];
        if (dt_str) {
            var dt = Date.parseDate(dt_str, 'd.m.Y H:i');
            text = '<a href="#" ' +
            'id="apply_link_'+ key +'" '+
            'onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\').doApply('+
            "'"+ key +"'"+
            ')">'+ dt.format('j M H:i').toLowerCase() +'</a>';
        } else if (!getGlobalOptions().medservice_record_day_count) {
			text += '<br><a href="#" '+
            'onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\').refineTimetableData('+
            "'"+ key +"'"+
            ')">' + 'Уточнить</a>';
		}
        return text;
    },
    _onMedServiceSelect: function(sel_rec, rec, baseParams)
    {
        if (!rec) {
            log(sel_rec);
            var base_form = this.filterPanel.getForm(),
                f = this.medServiceFilterCombo;
            f.lastQuery = '';
            f.getStore().loadData([{
                MedService_id: sel_rec.get('MedService_id'),
                Lpu_id: sel_rec.get('Lpu_id'),
                MedServiceType_id: sel_rec.get('MedServiceType_id'),
                Lpu_Nick: sel_rec.get('Lpu_Nick'),
                MedService_Nick: sel_rec.get('MedService_Nick'),
                MedService_Name: sel_rec.get('MedService_Name')
            }], true);
            f.setValue(sel_rec.get('MedService_id'));
            f.setRawValue(sel_rec.get('MedService_Nick'));
            //base_form.findField('UslugaComplex_id').setValue(sel_rec.get('UslugaComplex_id'));
            base_form.findField('Lpu_id').setValue(sel_rec.get('Lpu_id'));
            return true;
        }
        // возможно была выбрана другая служба и услуга
        if (sel_rec.get('UslugaComplexMedService_id') != rec.get('UslugaComplexMedService_id')) {
            rec.compositionMenu = null;
            rec.set('isComposite', sel_rec.get('isComposite'));
        }
        rec.set('UslugaComplexMedService_id', sel_rec.get('UslugaComplexMedService_id'));
        rec.set('pzm_UslugaComplexMedService_id', sel_rec.get('pzm_UslugaComplexMedService_id'));
        // не должна меняться? rec.set('UslugaComplex_2011id', sel_rec.get('UslugaComplex_2011id'));
        rec.set('UslugaComplex_id', sel_rec.get('UslugaComplex_id'));
        rec.set('UslugaComplex_Code', sel_rec.get('UslugaComplex_Code'));
        rec.set('UslugaComplex_Name', sel_rec.get('UslugaComplex_Name')); // при смене места оказания показывать наименование из данного места оказания
        rec.set('MedService_id', sel_rec.get('MedService_id'));
        rec.set('MedServiceType_id', sel_rec.get('MedServiceType_id'));
        rec.set('MedServiceType_SysNick', sel_rec.get('MedServiceType_SysNick'));
        rec.set('MedService_Nick', sel_rec.get('MedService_Nick'));
        rec.set('MedService_Name', sel_rec.get('MedService_Name'));
        rec.set('Lpu_id', sel_rec.get('Lpu_id'));
        rec.set('Lpu_Nick', sel_rec.get('Lpu_Nick'));
        rec.set('LpuBuilding_id', sel_rec.get('LpuBuilding_id'));
        rec.set('LpuBuilding_Name', sel_rec.get('LpuBuilding_Name'));
        rec.set('LpuUnit_id', sel_rec.get('LpuUnit_id'));
        rec.set('LpuUnit_Name', sel_rec.get('LpuUnit_Name'));
        rec.set('LpuUnitType_id', sel_rec.get('LpuUnitType_id'));
        rec.set('LpuUnitType_SysNick', sel_rec.get('LpuUnitType_SysNick'));
        rec.set('LpuSection_id', sel_rec.get('LpuSection_id'));
        rec.set('LpuSection_Name', sel_rec.get('LpuSection_Name'));
        rec.set('LpuSectionProfile_id', sel_rec.get('LpuSectionProfile_id'));
        rec.set('ttms_MedService_id', sel_rec.get('ttms_MedService_id'));
        rec.set('TimetableMedService_id', sel_rec.get('TimetableMedService_id'));
        rec.set('TimetableMedService_begTime', sel_rec.get('TimetableMedService_begTime'));
        rec.set('TimetableResource_begTime', sel_rec.get('TimetableResource_begTime'));
        rec.set('TimetableResource_id', sel_rec.get('TimetableResource_id'));
        rec.set('Resource_id', sel_rec.get('Resource_id'));
        rec.set('Resource_Name', sel_rec.get('Resource_Name'));
        rec.set('ttr_Resource_id', sel_rec.get('ttr_Resource_id'));
        if (baseParams.PrescriptionType_Code == 11) {
            // возможно была выбрана другая лаборатория или другой пункт забора
            rec.set('MedService_id', sel_rec.get('lab_MedService_id')); // лаборатория должна попасть в EvnDirection.
            rec.set('pzm_Lpu_id', sel_rec.get('pzm_Lpu_id'));
            rec.set('pzm_MedService_id', sel_rec.get('pzm_MedService_id'));
            rec.set('pzm_MedServiceType_id', sel_rec.get('pzm_MedServiceType_id'));
            rec.set('pzm_MedServiceType_SysNick', sel_rec.get('pzm_MedServiceType_SysNick'));
            rec.set('pzm_MedService_Nick', sel_rec.get('pzm_MedService_Nick'));
            rec.set('pzm_MedService_Name', sel_rec.get('pzm_MedService_Name'));
        }
        rec.commit();
        return true;
    },
    /**
     * Вызывается форма выбора службы по известной услуге, отображающая все службы,
     * оказывающие данную услугу  (связь по ГОСТ)
     */
    showMedServiceAll: function(key)
    {
        var thas = this;
        var rec = null;
        if (key) {
            rec = this.uslugaFrame.getGrid().getStore().getById(key);
        }
        if (key && !rec) {
            return false;
        }
        var win = getWnd('swMedServiceSelectWindow');
        if (win.isVisible()) {
            win.hide();
        }
        var baseParams = this._getMedServiceStoreBaseParams(rec);
        win.show({
            isOnlyPolka: baseParams.isOnlyPolka,
            PrescriptionType_Code: baseParams.PrescriptionType_Code,
            UslugaComplex_id: baseParams.filterByUslugaComplex_id,
            filterByLpu_id: baseParams.filterByLpu_id,
            filterByLpu_str: baseParams.filterByLpu_str,
            userMedStaffFact: this.userMedStaffFact,
            callback: function(sel_rec) {
                thas._onMedServiceSelect(sel_rec, rec, baseParams);
            }
        });
        return true;
    },
    /**
     * По гиперссылке открываем расписание, в котором можно выбрать другое время.
     * После закрытия формы выбора бирки, в графе "расписание" должно отобразиться новое время.
     */
    doApply: function(key){
        var thas = this,
            rec = this.uslugaFrame.getGrid().getStore().getById(key);
        if (!rec) {
            return false;
        }
		if (rec.get('withResource')) {
			this._openTimetableResource(rec, function(ttr){
				if (!ttr.TimetableResource_id) {
					rec.set('ttr_Resource_id', '');	//В очередь
				} else {
					rec.set('ttr_Resource_id', rec.get('Resource_id'));
				}
				rec.set('TimetableResource_id', ttr.TimetableResource_id);
				rec.set('TimetableResource_begTime', ttr.TimetableResource_begTime);
				rec.commit();
				thas.doInsert(key);
			});
		} else {
			this._openTimetableMedService(rec, function(ttms){
				if (!ttms.TimetableMedService_id) {
					rec.set('ttms_MedService_id', '');	//В очередь
				} else {
					rec.set('ttms_MedService_id', rec.get('MedService_id'));
				}
				rec.set('TimetableMedService_id', ttms.TimetableMedService_id);
				rec.set('TimetableMedService_begTime', ttms.TimetableMedService_begTime);
				rec.commit();
				thas.doInsert(key);
			});
		}
        return true;
    },
    changeTime:function(key){
        var thas = this,
            record = this.dataView.store.getById(key),
            rec = this.dataView.assocUslugaFrameRec[key],
            direction = this.dataView.assocDirection[key];
        if (!record || !rec) {
            return false;
        }
        this._openTimetableMedService(rec, function(ttms){
            if (!ttms || !ttms.TimetableMedService_id) {
                return false;
            }
            var old_time_id = rec.get('TimetableMedService_id');
            if (old_time_id > 0) {
                sw.Promed.Direction.unlockTime(thas, 'TimetableMedService', old_time_id, function(){
                    //delete thas.dataView.assocLockedTimetableMedServiceId[key];
                });
            }
            rec.set('TimetableMedService_id', ttms.TimetableMedService_id);
            rec.set('TimetableMedService_begTime', ttms.TimetableMedService_begTime);
            rec.commit();
            sw.Promed.Direction.lockTime(thas, 'TimetableMedService', ttms.TimetableMedService_id, function(time_id){
                thas.dataView.assocLockedTimetableMedServiceId[key] = time_id;
            });
            thas.dataView.updateRec(record,{RecDate: ttms.TimetableMedService_begTime});
            direction.time = ttms.TimetableMedService_begTime;
            direction.TimetableMedService_id = ttms.TimetableMedService_id;
            return true;
        });
        return true;
    },
    _openTimetableMedService:function(rec, callback){
		var datetime = Date.parseDate(rec.data.TimetableMedService_begTime, 'd.m.Y H:i');
        var ms_data = {
            Lpu_id: rec.data.Lpu_id,
            MedService_id: rec.data.MedService_id,
            MedServiceType_id: rec.data.MedServiceType_id,
            MedService_Nick: rec.data.MedService_Nick,
            MedService_Name: rec.data.MedService_Name,
            MedServiceType_SysNick: rec.data.MedServiceType_SysNick,
			date: datetime.format('d.m.Y')
        };
        // если это назначение лабораторной диагностики
        // и есть пункт забора
        if (rec.data.pzm_MedService_id && rec.data.pzm_MedService_id == rec.data.ttms_MedService_id) {
            //то будем записывать в пункт забора
            ms_data.Lpu_id = rec.data.pzm_Lpu_id;
            ms_data.MedService_id = rec.data.pzm_MedService_id;
            ms_data.MedServiceType_id = rec.data.pzm_MedServiceType_id;
            ms_data.MedServiceType_SysNick = rec.data.pzm_MedServiceType_SysNick;
            ms_data.MedService_Nick = rec.data.pzm_MedService_Nick;
            ms_data.MedService_Name = rec.data.pzm_MedService_Name;
        }
		if(rec.data.ttms_MedService_id==""){
        	if (rec.data.pzm_UslugaComplexMedService_id) {
				ms_data.UslugaComplexMedService_id = rec.data.pzm_UslugaComplexMedService_id;
			} else {
				ms_data.UslugaComplexMedService_id = rec.data.UslugaComplexMedService_id;
			}
		}
        sw.Promed.EvnPrescr.openTimetable({
            MedService: ms_data,
            callback: function(ttms){
                callback(ttms);
                getWnd('swTTMSScheduleRecordWindow').hide();
            }
            //,userClearTimeMS: function() {}
        });
    },
    _openTimetableResource:function(rec, callback){
		var datetime = Date.parseDate(rec.data.TimetableResource_begTime, 'd.m.Y H:i');
        var resource_data = {
            Lpu_id: rec.data.Lpu_id,
            MedService_id: rec.data.MedService_id,
            MedServiceType_id: rec.data.MedServiceType_id,
            MedService_Nick: rec.data.MedService_Nick,
            MedService_Name: rec.data.MedService_Name,
            MedServiceType_SysNick: rec.data.MedServiceType_SysNick,
			UslugaComplexMedService_id: rec.data.UslugaComplexMedService_id,
			Resource_id: rec.data.Resource_id,
			Resource_Name: rec.data.Resource_Name,
			date: datetime.format('d.m.Y')
        };
        sw.Promed.EvnPrescr.openTimetable({
            Resource: resource_data,
            callback: function(ttr){
                callback(ttr);
                getWnd('swTTRScheduleRecordWindow').hide();
            }
            //,userClearTimeMS: function() {}
        });
    },
    /**
     * Создаем меню состава услуги
     * @param callback
     * @param rec
     * @return {Boolean}
     */
    loadCompositionMenu: function(callback, rec, emptyCallback)
    {
        if (typeof callback != 'function') {
            return false;
        }
        if (!rec) {
            return false;
        }
        if (1 != rec.get('isComposite')) {
            return true;
        }
        if (rec.compositionMenu) {
            callback(rec.compositionMenu);
            return true;
        }
        var thas = this;
        this.getLoadMask(LOAD_WAIT).show();
        Ext.Ajax.request({
            params: {
                UslugaComplexMedService_pid: rec.get('UslugaComplexMedService_id'),
                UslugaComplex_pid: rec.get('UslugaComplex_id'),
                Lpu_id: rec.get('Lpu_id')
            },
            callback: function(options, success, response) {
                thas.getLoadMask().hide();
                if ( success ) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (Ext.isArray(response_obj) && response_obj.length > 0) {
                        rec.compositionMenu = new Ext.menu.Menu();
						rec.compositionMenu.addListener('beforeshow', function(m) {
							swSetMaxMenuHeight(m, 300);
						});
                        if(response_obj.length > 1){
                            rec.compositionMenu.cntAll = response_obj.length;
                            rec.compositionMenu.itemCheckAll = new Ext.menu.CheckItem({
                                id: "checkAll",
                                text: "Выбрать/Снять все",
                                iconCls: "uslugacomplex-16",
                                rec: rec,
                                checked: true,
                                hideOnClick: false,
                                handler: function(item) {
                                    rec.compositionMenu.items.each(function(rec){
                                        if(rec.id!=item.id&&rec.setChecked!=undefined){
                                            rec.setChecked(!item.checked)
                                        }
                                    });
                                    item.rec.set('compositionCntChecked', item.checked ? 0 : item.rec.compositionMenu.cntAll);
                                    item.rec.commit();
                                }
                            });
							rec.compositionMenu.add(rec.compositionMenu.itemCheckAll);
							rec.compositionMenu.add(new Ext.menu.Separator());
						}
					   for (var i=0; i < response_obj.length; i++) {
                            rec.compositionMenu.add(new Ext.menu.CheckItem({
                                id: response_obj[i].UslugaComplex_id,
                                text: getPrescriptionOptions().enable_show_service_code ? response_obj[i].UslugaComplex_Code+' '+response_obj[i].UslugaComplex_Name : response_obj[i].UslugaComplex_Name,
                                UslugaComplex_id: response_obj[i].UslugaComplex_id,
                                iconCls: "uslugacomplex-16",
                                rec: rec,
                                checked: true,
                                hideOnClick: false,
                                handler: function(item) {
                                    var cnt_checked = item.rec.get('compositionCntChecked');
                                    if (item.checked) {
                                        cnt_checked = cnt_checked - 1;
                                    } else {
                                        cnt_checked = cnt_checked + 1;
                                    }
                                    item.rec.set('compositionCntChecked', cnt_checked);
                                    item.rec.commit();
                                    var menu = this.rec.compositionMenu;
                                    if (menu.itemCheckAll) {
                                        if (cnt_checked == menu.cntAll) {
                                            menu.itemCheckAll.setChecked(true);
                                        }
                                        if (cnt_checked == 0) {
                                            menu.itemCheckAll.setChecked(false);
                                        }
                                    }
                                }
                            }));
                        }
                        rec.set('compositionCntChecked', response_obj.length);
                        rec.set('compositionCntAll', response_obj.length);
                        rec.commit();
                        callback(rec.compositionMenu);
                    } else {
						if (typeof emptyCallback == 'function') {
							emptyCallback();
						}
					}
                }
            },
            url: '/?c=MedService&m=loadCompositionMenu'
        });
        return true;
    },
	/**
	 * Загружает список услуг нужного типа
	 */
	_loadPrescriptionListByType: function(PrescriptionType_Code) {
		var base_form = this.filterPanel.getForm();
		if (jQuery.inArray(parseInt(PrescriptionType_Code), [6, 7, 11, 12, 13])<0) {
			return false;
		}
        this.isOnlyPolka = (13==parseInt(PrescriptionType_Code) && this.formParams.parentEvnClass_SysNick.inlist(['EvnVizitPL','EvnVizitPLStom']))?1:0;
        this.PrescriptionType_Code = PrescriptionType_Code;
        base_form.findField('PrescriptionType_id').setValue(PrescriptionType_Code);
        this.uslugaFilterCombo.clearValue();
        this.uslugaFilterCombo.getStore().removeAll();
        this.uslugaFilterCombo.setPrescriptionTypeCode(PrescriptionType_Code);
        this.uslugaFilterCombo.getStore().baseParams.PrescriptionType_Code = this.PrescriptionType_Code;
        this.uslugaFilterCombo.getStore().baseParams.isOnlyPolka = this.isOnlyPolka;
        this.uslugaFilterCombo.getStore().baseParams.isStac = this.formParams.parentEvnClass_SysNick.inlist(['EvnPS','EvnSection']) ? 1 : 0;
		this.uslugaFilterCombo.getStore().baseParams.SurveyTypeLink_lid = base_form.findField('SurveyTypeLink_lid').getValue();
		this.uslugaFilterCombo.getStore().baseParams.EvnPLDisp_id = base_form.findField('EvnPLDisp_id').getValue();

		// в поле «Услуга» для выбора предлагать все услуги из справочника ГОСТ-2011, а так же только фактически заведенные в местах оказания услуги
        this.uslugaFilterCombo.getStore().baseParams.to = this.id;
		var uslugacategorylist = ['gost2011'];
		if ( getRegionNick() == 'kz' ) {
			// для Казахстана фильтрация осуществляется по классификатору мед.услуг
			uslugacategorylist = ['classmedus'];
		}
        this.uslugaFilterCombo.setUslugaCategoryList(uslugacategorylist);

		this.uslugaFrame.removeAll();
		this.uslugaFrame.getGrid().getStore().removeAll();
		var colModel = this.uslugaFrame.getGrid().getColumnModel();
		colModel.setHidden(colModel.getIndexById('EPUIVF_composition'), PrescriptionType_Code!=11);

		if (getPrescriptionOptions().default_service_search_form_type && getPrescriptionOptions().default_service_search_form_type == 2) {
			this.setGroupMode(1);
		} else {
			this.setGroupMode(0);
		}
		return true;
	},
	reloadEvnPrescr: function(options) {
		var collapsedClass = 'collapsed',
			expandedClass = 'expanded';

		var win = this;
		var base_form = this.filterPanel.getForm();
		var Evn_pid = base_form.findField('Evn_pid').getValue();
		var params = {};
		params.object =	'EvnPrescrCustom';
		params.object_id = 'EvnPrescrCustom_id';
		params.object_value	= Evn_pid;
		params.is_reload_one_section = 1;
		params.parent_object_id = 'EvnPrescr_pid';
		params.parent_object_value = Evn_pid;
		params.param_name = 'section';
		params.param_value = 'EvnPrescrPlan';
		params.forRightPanel = 1;

		if (options && options.PrescriptionType_Code) {
			params.PrescriptionType_Code = options.PrescriptionType_Code;
		} else {
			params.PrescriptionType_Code = this.PrescriptionType_Code;
		}

		var section_id = 'EvnPrescrPlan_rightPanel_'+Evn_pid+'-'+params.PrescriptionType_Code;
		var gr_el = Ext.get(section_id);
		var el = Ext.get(section_id + '_items');
		var cnt_el = Ext.get(section_id +'_cnt');

		if (options && options.reload) {
			el.dom.innerHTML = "";
		}

		if (gr_el && el) {
			if (el.dom.innerHTML.length == 0) {
				win.showLoadMask(LOAD_WAIT);
				Ext.Ajax.request({
					failure: function (response, options) {
						win.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['pri_zagruzke_naznacheniy_proizoshla_oshibka']);
					},
					params: params,
					success: function (response, options) {
						win.hideLoadMask();
						if (response.responseText) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.html) {
								Ext.DomHelper.overwrite(el, result.html, false);

								var cnt = el.query('.prescriptioninfo').length;
								cnt_el.update(cnt);
							}

							if (result.map) {
								for(var section_code in result.map) {
									var items = result.map[section_code].item || [],
										object_key = result.map[section_code].object_key,
										i, data, record, index;

									win.viewFormDataStore.each(function(rec){
										if (rec.data.PrescriptionType_id
											&& rec.data.EvnPrescr_pid
											&& rec.data.PrescriptionType_id == params.object_value
											&& rec.data.EvnPrescr_pid == params.parent_object_value
											&& !rec.data.section
										) {
											win.viewFormDataStore.remove(rec);
										}
									});

									if (items) {
										for (i = 0; i < items.length; i++) {
											data = items[i].data;
											win.addHandlerForObject(params.param_value, items[i][object_key], win.isReadOnly || data.accessType == 'view');

											record = new Ext.data.Record(data);
											record.object_code = params.param_value;
											record.object_key = object_key;
											record.object_value = items[i][object_key];
											record.parent_object_code = null;
											record.parent_object_key = null;
											record.parent_object_value = null;
											record.subsection = null;
											record.list = params.param_value + 'List';
											record.id = params.param_value +'_'+ items[i][object_key];
											index = win.viewFormDataStore.indexOfId(record.id);
											win.viewFormDataStore.add(record);
										}
									}
								}
							}
						}
					},
					url: '/?c=Template&m=getEvnForm'
				});
			}

			if (!options || !options.reload) {
				if (gr_el.hasClass(expandedClass)) {
					gr_el.removeClass(expandedClass);
					gr_el.addClass(collapsedClass);
				} else {
					gr_el.removeClass(collapsedClass);
					gr_el.addClass(expandedClass);
				}
			}
		}
	},
	/**
	 * Создает массивы экшенов для элементов управления формы просмотра в соответствии со списком map и config_actions
	 */
	createActionListForTpl:function (map)
	{
		log('createActionListForTpl', map);

		if (typeof(map) != 'object')
		{
			return false;
		}
		var object_code, obj, action_name, action_obj;
		for (object_code in map)
		{
			if (typeof(this.config_actions[object_code]) != 'object' || typeof(map[object_code]) != 'object')
			{
				continue;
			}
			obj = this.config_actions[object_code];
			for (action_name in obj)
			{
				if (typeof(obj[action_name]) != 'object' || !obj[action_name].sectionCode || !obj[action_name].handler || (typeof obj[action_name].handler != 'function'))
				{
					continue;
				}
				action_obj = obj[action_name];
				if (action_obj.actionType)
				{
					switch(action_obj.actionType)
					{
						case 'view':
							if (this.readOnly || this.isReadOnly) {
								if (false == action_name.inlist(['cancelSigned'])) {
									this.actionNameList_View.push(action_name);
								}
							} else {
								this.actionNameList_View.push(action_name);
							}
							break;
						case 'add':this.actionNameList_Add.push(action_name);break;
						case 'edit':this.actionNameList_Edit.push(action_name);break;
						case 'del':this.actionNameList_Del.push(action_name);break;
					}
				}
				if (action_obj.dblClick)
				{
					this.actionListDblClickBySection[action_obj.sectionCode] = action_obj.handler;
				}
				if(typeof(this.actionListClickBySection[action_obj.sectionCode]) != 'object')
				{
					this.actionListClickBySection[action_obj.sectionCode] = {};
				}
				this.actionListClickBySection[action_obj.sectionCode][action_name] = action_obj.handler;
			}
			if (map[object_code] && map[object_code].item && Ext.isArray(map[object_code].item))
			{
				item_arr = map[object_code].item;
				for(i=0; i < item_arr.length; i++)
				{
					if (item_arr[i].children)
					{
						this.createActionListForTpl(item_arr[i].children);
					}
				}
			}
		}
		return true;
	},
	/**
	 * Переключение видимости элемента
	 * @param string id DOM
	 * @param boolean hide Принудительно скрыть или отобразить
	 */
	toggleDisplay: function(id, hide) {
		var el = Ext.get(id);
		if ( el ) {
			if ( typeof hide == 'undefined' ) {
				hide = el.isDisplayed() ? true : false;
			}
			if ( hide == true ) {
				el.setStyle({display: 'none'});
			} else {
				el.setStyle({display: 'block'});
			}
		}
	},
	/**
	 * Переключение видимости назначений курса
	 */
	toogleEvnCourse: function(d) {
		var parts, className, EvnPrescrSection;
		parts = d.object_id.split('-');
		EvnPrescrSection = d.object+'List_'+parts[0];
		className = 'EvnCourse'+parts[1];
		if (className) {
			var node_list = Ext.query("*[class*="+className+"]",Ext.getDom(EvnPrescrSection));
			var i, el;
			for(i=0; i < node_list.length; i++)
			{
				el = new Ext.Element(node_list[i]);
				this.toggleDisplay(el.id, el.isDisplayed());
			}
		}
	},
	/**
	 * Переключение видимости назначений курса
	 */
	toogleEvnCourseTreat: function(e, c, d) {
		var btnEl = new Ext.Element(c),
			viewDataEl = Ext.get(d.object+'_rightPanel_'+d.object_id+'_EvnCourseTreatViewData'),
			listEl = Ext.get(d.object+'_rightPanel_'+d.object_id+'_EvnCourseTreatItems');
		if (!btnEl || !listEl || !viewDataEl) {
			return false;
		}
		this.toggleDisplay(listEl.id, listEl.isDisplayed());
		if (btnEl.hasClass('EvnPrescrTreatCollapsed')) {
			if (viewDataEl.dom.innerHTML.length == 0) {
				var ep_data = this.getObjectData(d.object, d.object_id);
				if ( ep_data == false || !ep_data.EvnCourse_id) {
					return false;
				}
				if (!this.input_cmp_list) {
					this.input_cmp_list = {};
				}
				this.input_cmp_list[viewDataEl.id] = new sw.Promed.EvnPrescrTreatDrugDataView(
					'EvnPrescrTreatDrugDataView'+ep_data.EvnCourse_id,
					viewDataEl.id,
					ep_data,
					this,
					'EvnPrescrPlan',
					d
				);
			}
			btnEl.removeClass('EvnPrescrTreatCollapsed');
			btnEl.update(lang['cvernut']);
		} else {
			btnEl.addClass('EvnPrescrTreatCollapsed');
			btnEl.update(lang['razvernut']);
		}
		return true;
	},
    /**
     * Декларируем компоненты формы и создаем форму
     */
    initComponent: function() {
        var thas = this;

		this.config_actions =
		{
			EvnPrescrPlan: {
				loadEvnPrescr: {//ok
					actionType: 'view',
					sectionCode: 'EvnPrescrPlan',
					handler: function (e, c, d) {
						var PrescriptionType_Code = d.object_id.replace(/.*-/, '');
						thas.reloadEvnPrescr({
							PrescriptionType_Code: PrescriptionType_Code
						});
					}
				},
				toogleEvnCourse: {//ok
					actionType: 'view',
					sectionCode: 'EvnPrescrPlan',
					handler: function (e, c, d) {
						thas.toogleEvnCourse(d);
					}
				},
				toogleEvnCourseTreat: {//ok
					actionType: 'view',
					sectionCode: 'EvnPrescrPlan',
					handler: function (e, c, d) {
						thas.toogleEvnCourseTreat(e, c, d);
					}
				},
				addPrescription: {//ok
					actionType: 'add',
					sectionCode: 'EvnPrescrPlan',
					handler: function (e, c, d) {
						thas.addPrescription(d, 'EvnPrescrPlan');
					}
				},
				openPrescrListActionMenu: {//ok
					actionType: 'add',
					sectionCode: 'EvnPrescrPlanList',
					handler: function (e, c, d) {
						thas.openPrescrListActionMenu(e, d, 'EvnPrescrPlan');
					}
				},
				openPrescrActionMenu: {//ok
					actionType: 'view',
					sectionCode: 'EvnPrescrPlan',
					handler: function (e, c, d) {
						thas.openPrescrActionMenu(e, d, 'EvnPrescrPlan');
					}
				},
				openDirActionMenu: {//ok
					actionType: 'view',
					sectionCode: 'EvnPrescrPlan',
					handler: function (e, c, d) {
						thas.openDirActionMenu(e, d, 'EvnPrescrPlan');
					}
				},
				xml: {
					actionType: 'view',
					sectionCode: 'EvnPrescrPlan',
					handler: function (e, c, d) {
						thas.openEvnXmlViewWindow('EvnPrescrPlan', d);
					}
				},
				addBlankDir: {
					actionType: 'edit',
					sectionCode: 'EvnPrescrPlan',
					handler: function (e, c, d) {
						thas.openEvnXmlEditWindow('add', 'EvnPrescrPlan', d);
					}
				},
				editBlankDir: {
					actionType: 'view',
					sectionCode: 'EvnPrescrPlan',
					handler: function (e, c, d) {
						thas.openEvnXmlEditWindow('edit', 'EvnPrescrPlan', d);
					}
				},
				viewdir: {//ок
					actionType: 'view',
					sectionCode: 'EvnPrescrPlan',
					handler: function (e, c, d) {
						thas.openEvnDirectionEditWindow('view', d);
					}
				}
			}
		};


        thas.uslugaFilterCombo = new sw.Promed.SwUslugaComplexEvnPrescrCombo({
            id: thas.getId() + '_UslugaFilterCombo',
            allowBlank: true,
            fieldLabel: lang['usluga'],
            hiddenName: 'UslugaComplex_id',
            listWidth: 600,
            width: 350,
            listeners:
            {
                change: function(f,n) {
                    thas.medServiceFilterCombo.lastQuery = '';
                    thas.medServiceFilterCombo.getStore().removeAll();
                    thas.medServiceFilterCombo.getStore().baseParams = thas._getMedServiceStoreBaseParams(null);
                    thas.medServiceFilterCombo.getStore().load({
                        callback: function(){
                            var id = thas.medServiceFilterCombo.getValue();
                            thas.medServiceFilterCombo.setValue(null);
                            if (thas.medServiceFilterCombo.getStore().getById(id)) {
                                thas.medServiceFilterCombo.setValue(id);
                            }
                        }
                    });
                }
            }
        });

        thas.lpuFilterCombo = new sw.Promed.SwLpuCombo({
            id: thas.getId() + '_LpuFilterCombo',
            allowBlank: true,
            fieldLabel: lang['mo'],
            hiddenName: 'Lpu_id',
            listWidth: 400,
            width: 350,
            listeners:
            {
                change: function(f,n) {
                    thas.medServiceFilterCombo.lastQuery = '';
                    thas.medServiceFilterCombo.getStore().removeAll();
                    thas.medServiceFilterCombo.getStore().baseParams = thas._getMedServiceStoreBaseParams(null);
                    thas.medServiceFilterCombo.getStore().load({
                        callback: function(){
                            var id = thas.medServiceFilterCombo.getValue();
                            thas.medServiceFilterCombo.setValue(null);
                            if (thas.medServiceFilterCombo.getStore().getById(id)) {
                                thas.medServiceFilterCombo.setValue(id);
                            }
                        }
                    });
                    thas.uslugaFilterCombo.lastQuery = '';
                    thas.uslugaFilterCombo.getStore().removeAll();
                    thas.uslugaFilterCombo.getStore().baseParams.filterByLpu_id = n || null;
                    //thas.uslugaFilterCombo.getStore().load();
                }
            }
        });

        thas.medServiceFilterCombo = new sw.Promed.SwMedServiceFilterCombo({
            id: thas.getId() + '_MedServiceFilterCombo',
            listWidth: 500,
            width: 350,
            listeners:
            {
                change: function(f,n) {
                    thas.uslugaFilterCombo.lastQuery = '';
                    thas.uslugaFilterCombo.getStore().removeAll();
                    thas.uslugaFilterCombo.getStore().baseParams.MedService_id = n || null;
                    thas.uslugaFilterCombo.getStore().baseParams.filterByLpu_id = thas.lpuFilterCombo.getValue() || null;
                    var rec = thas.medServiceFilterCombo.getStore().getById(thas.medServiceFilterCombo.getValue());
                    if (rec) {
                        thas.lpuFilterCombo.setValue(rec.get('Lpu_id'));
                        thas.uslugaFilterCombo.getStore().baseParams.filterByLpu_id = rec.get('Lpu_id');
                    }
                    thas.uslugaFilterCombo.getStore().load({
                        callback: function(){
                            var id = thas.uslugaFilterCombo.getValue();
                            thas.uslugaFilterCombo.setValue(null);
                            if (thas.uslugaFilterCombo.getStore().getById(id)) {
                                thas.uslugaFilterCombo.setValue(id);
                            }
                        }
                    });
                }
            },
            onTrigger2Click: function()
            {
                if (this.disabled)
                    return false;
				var lpu = thas.lpuFilterCombo.getValue(),
					usl = thas.uslugaFilterCombo.getValue();
				if(Ext.isEmpty(usl) && Ext.isEmpty(lpu)){
					sw.swMsg.alert(langs('Ошибка'), langs('Не заданы минимальные параметры поиска Услуга и МО. Заполните минимум одно из полей и повторите попытку.'));
					return false;
				}
                this.collapse();
                thas.showMedServiceAll(null);
                return true;
            }
        });

		this.modeGroupUslugaComplex = new Ext.Action({
			text: lang['po_uslugam'],
			style: "",
			minWidth: 150,
			ctCls: 'newButton',
			xtype: 'button',
			toggleGroup: 'labModeToggle',
			iconCls: '',
			pressed: true,
			handler: function()
			{
				thas.setGroupMode(0);
			}
		});

		this.modeGroupMedService = new Ext.Action({
			text: lang['po_mestu_okazaniya'],
			style: "",
			ctCls: 'newButton',
			minWidth: 150,
			xtype: 'button',
			toggleGroup: 'labModeToggle',
			iconCls: '',
			handler: function()
			{
				thas.setGroupMode(1);
			}
		});

		this.modeToolbar = new Ext.Toolbar({
			cls: 'toogleToolbar',
			style: 'padding-left: 85px;',
			items: [
				this.modeGroupUslugaComplex,
				this.modeGroupMedService
			]
		});

		thas.uslugaComplexModeUC = new Ext.Action({
			text: langs('Поиск по коду услуг'),
			toggleGroup: 'uslugaLoadMode',
			iconCls: '',
			ctCls: 'newButton',
			minWidth: 150,
			xtype: 'button',
			style: "",
			handler: function() {
				thas.setUslugaLoadMode('uc');
			}
		});

		thas.uslugaComplexModeAT = new Ext.Action({
			text: langs('Поиск по наименованию'),
			toggleGroup: 'uslugaLoadMode',
			iconCls: '',
			ctCls: 'newButton',
			minWidth: 150,
			xtype: 'button',
			style: "",
			handler: function() {
				thas.setUslugaLoadMode('at');
			}
		});

		thas.uslugaLoadModeToolbar = new Ext.Toolbar({
			cls: 'toogleToolbar',
			labelSeparator: '',
			style: 'padding-left: 85px;',
			items: [
				thas.uslugaComplexModeUC,
				thas.uslugaComplexModeAT
			]
		});

		thas.analyzerTestNameFilterField = new Ext.form.TextField({
			fieldLabel: 'Исследование',
			name: 'filterByAnalyzerTestName',
			width: 350,
			disabled: true
		});

        this.filterPanel = new Ext.form.FormPanel({
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0',
			cls: 'x-panel-mc',
            border: false,
            frame: false,
            height: 140,
            id: 'EvnPrescrUslugaInputForm',
            labelAlign: 'right',
            buttonAlign: 'left',
            labelWidth: 100,
            region: 'north',
            items: [{
                border: false,
                layout: 'form',
                items: [{
                    name: 'Evn_pid',
                    xtype: 'hidden'
                }, {
                    name: 'DopDispInfoConsent_id',
                    xtype: 'hidden'
                }, {
                    name: 'SurveyTypeLink_lid',
                    xtype: 'hidden'
                }, {
                    name: 'EvnPLDisp_id',
                    xtype: 'hidden'
                }, {
                    name: 'parentEvnClass_SysNick',
                    xtype: 'hidden'
                }, {
                    name: 'PrescriptionType_id',
                    xtype: 'hidden'
                }, {
                    name: 'PersonEvn_id',
                    xtype: 'hidden'
                }, {
                    name: 'Person_id',
                    xtype: 'hidden'
                }, {
                    name: 'Server_id',
                    xtype: 'hidden'
                }, {
                    name: 'EvnPrescr_setDate',
                    xtype: 'hidden'
                }, {
                    name: 'Diag_id',
                    xtype: 'hidden'
                }, {
                    name: 'StudyTarget_id',
                    xtype: 'hidden'
                }, {
	                name: 'HIVContingentTypeFRMIS_id',
	                xtype: 'hidden'
                }, {
                    name: 'CovidContingentType_id',
                    xtype: 'hidden'
                }, {
	                name: 'HormonalPhaseType_id',
	                xtype: 'hidden'
                }, {
					layout:'column',
					border:false,
					items:[{
                        layout:'form',
                        border:false,
                        items:[
                            thas.uslugaLoadModeToolbar,
                            thas.uslugaFilterCombo,
                            thas.analyzerTestNameFilterField,
                            thas.lpuFilterCombo,
                            thas.medServiceFilterCombo,
                            thas.modeToolbar
                        ]
                    },{
                        layout:'form',
                        border:false,
                        style: 'padding-left: 5px;',
                        items:[{
                            xtype: 'button',
                            text: BTN_FRMSEARCH,
                            iconCls: 'search16',
                            style: 'padding-bottom: 3px',
                            handler: function() {
                                thas.loadUslugaFrame();
                            }
                        },{
                            xtype: 'button',
                            text: BTN_FRMRESET,
                            iconCls: 'resetsearch16',
                            handler: function() {
                                thas.doReset(true);
                            }
                        }]
                    }]
				}]

            }],
            keys: [{
                fn: function() {
                    thas.loadUslugaFrame();
                },
                key: Ext.EventObject.ENTER,
                stopEvent: true
            }]
        });

        this.dataView = new Ext.Panel({
			autoScroll: true,
			cls: 'presrcRightPanel',
			region: 'center',
			html : '',
			store:new Ext.data.Store({
				autoLoad:false,
				reader:new Ext.data.JsonReader({
					id:'EvnPrescr_key'
				}, [
					// EvnPrescr_id нельзя использовать как ключ,
					// т.к. в этом хранилище будут и несохраненные записи без EvnPrescr_id
					{name: 'EvnPrescr_key', mapping: 'EvnPrescr_key',key:true},
					{name: 'RecordStatus', mapping: 'RecordStatus'},// 'inserted', 'updated', 'new', ''
					{name: 'EvnPrescr_id', mapping: 'EvnPrescr_id'},
					{name: 'EvnPrescr_IsExec', mapping: 'EvnPrescr_IsExec'},
					{name: 'EvnPrescr_IsDir', mapping: 'EvnPrescr_IsDir'},
					{name: 'PrescriptionStatusType_id', mapping: 'PrescriptionStatusType_id'},
					{name: 'EvnDirection_id', mapping: 'EvnDirection_id'},
					{name: 'EvnQueue_id', mapping: 'EvnQueue_id'},
					{name: 'timetable', mapping: 'timetable'},
					{name: 'timetable_id', mapping: 'timetable_id'},

					// ниже данные для сохранения
					{name: 'EvnPrescr_uslugaList' ,mapping:'EvnPrescr_uslugaList'},
					{name: 'PrescriptionType_id', mapping: 'PrescriptionType_id'},
					{name: 'EvnPrescr_pid', mapping: 'EvnPrescr_pid'},
					{name: 'PersonEvn_id', mapping: 'PersonEvn_id'},
					{name: 'Server_id', mapping: 'Server_id'},
					{name: 'EvnPrescr_setDate', mapping: 'EvnPrescr_setDate'},
					{name: 'EvnPrescr_IsCito', mapping: 'EvnPrescr_IsCito'},
					{name: 'EvnPrescr_Descr', mapping: 'EvnPrescr_Descr'},
					{name: 'UslugaId_List', mapping:'UslugaId_List'},
					{name: 'DurationType_id', mapping: 'DurationType_id'},
					{name: 'DurationType_recid', mapping: 'DurationType_recid'},
					{name: 'DurationType_intid', mapping: 'DurationType_intid'},

					// ниже данные для отображения
					{name: 'Usluga_List', mapping:'Usluga_List'},
					{name: 'CountInDay', mapping: 'CountInDay'},
					{name: 'CourseDuration', mapping: 'CourseDuration'},
					{name: 'ContReception', mapping: 'ContReception'},
					{name: 'Interval', mapping: 'Interval'},
					{name: 'DurationTypeP_Nick', mapping: 'DurationTypeP_Nick'},
					{name: 'DurationTypeN_Nick', mapping: 'DurationTypeN_Nick'},
					{name: 'DurationTypeI_Nick', mapping: 'DurationTypeI_Nick'},
					{name: 'IsCito_Name', mapping: 'IsCito_Name'},
					{name: 'IsCito_Code', mapping: 'IsCito_Code'},
					{name: 'EvnDirection_Num', mapping: 'EvnDirection_Num'},
					{name: 'RecTo', mapping: 'RecTo'},
					{name: 'RecDate', mapping: 'RecDate'},
					{name: 'EvnPrescrGroup_Title', mapping: 'EvnPrescrGroup_Title'},
					{name: 'EvnPrescrGroup_Count', mapping: 'EvnPrescrGroup_Count'},
					{name: 'EvnCourse_Count', mapping: 'EvnCourse_Count'},
					{name: 'cntInPrescriptionTypeGroup', mapping: 'cntInPrescriptionTypeGroup'},

					/*1*/
					{name: 'PrescriptionRegimeType_Name', mapping: 'PrescriptionRegimeType_Name'},
					/*2*/
					{name: 'PrescriptionDietType_Name', mapping: 'PrescriptionDietType_Name'},
					/*5,6*/
					// Курс
					{name: 'EvnCourse_id', mapping: 'EvnCourse_id'},
					{name: 'EvnCourse_begDate', mapping: 'EvnCourse_begDate'},
					{name: 'EvnCourse_endDate', mapping: 'EvnCourse_endDate'},
					{name: 'MinCountInDay', mapping: 'MinCountInDay'},
					{name: 'MaxCountInDay', mapping: 'MaxCountInDay'},
					/*5*/
					{name: 'DrugListData', mapping: 'DrugListData'},
					{name: 'Duration', mapping: 'Duration'},
					{name: 'DurationType_Nick', mapping: 'DurationType_Nick'},
					{name: 'PrescriptionIntroType_Name', mapping: 'PrescriptionIntroType_Name'},
					{name: 'PerformanceType_Name', mapping: 'PerformanceType_Name'},
					/*6*/
					{name: 'MedServices', mapping: 'MedServices'},
					/*10*/
					{name: 'Params', mapping: 'Params'},
					{name: 'EvnPrescr_setTime', mapping: 'EvnPrescr_setTime'},
					/*6,12*/
					{name: 'EvnXmlDir_id', mapping: 'EvnXmlDir_id'},
					{name: 'EvnXmlDirType_id', mapping: 'EvnXmlDirType_id'},
					/*6*/
					{name: 'MedicationRegime', mapping: 'MedicationRegime'}
				]),
				url:'/?c=EvnPrescr&m=loadEvnPrescrUslugaDataView'
			}),
			bodyStyle: 'padding: 5px;',
			viewDirection:function(key){
				var store =  this.store;
				var index = store.findBy(function(record){
					return record.get('EvnPrescr_key') == key;
				});
				var record = store.getAt(index);
				getWnd('swEvnDirectionEditWindow').show({action:"view",EvnDirection_id:record.get('EvnDirection_id'),ARMType:'common',formParams:record.data});
			},
            delPrescr:function(key, callback){
				callback = callback||Ext.emptyFn;

				var params = {};
                var store =  this.store;
                var index = store.findBy(function(record){
					return record.get('EvnPrescr_key') == key;
                });

                var record = store.getAt(index);
                if (record) {
					params={
						EvnPrescr_id:record.get('EvnPrescr_id'),
						PrescriptionType_id:record.get('PrescriptionType_id'),
						parentEvnClass_SysNic:'EvnSection'
					};
                    if (record.get('RecordStatus')=='new') {
                        store.removeAt(index);
                        this.filterDS();
                    } else {
						var PrescriptionType_Code = Number(record.get('PrescriptionType_Code'));
                        var loadMask = new Ext.LoadMask(thas.getEl(), {msg: "Отмена назначения..."});
                        loadMask.show();
                        Ext.Ajax.request({
                            failure:function () {
                                loadMask.hide();
                                sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_otmene_naznacheniya']);
                            },
                            params:params,
                            success:function (response) {
                                if (response.responseText) {
                                    var answer = Ext.util.JSON.decode(response.responseText);
                                    if (answer.success) {
                                        loadMask.hide();
                                        store.removeAt(index);
                                        thas.dataView.filterDS();
                                        thas.hasChange = true;
										if (!PrescriptionType_Code.inlist(thas.changedType)) {
											thas.changedType.push(PrescriptionType_Code);
										}
										callback();
                                    } else if (answer.Error_Message) {
                                        Ext.Msg.alert(lang['oshibka'], answer.Error_Message);
                                    }
                                } else {
                                    Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_otmene_naznacheniya_otsutstvuet_otvet_servera']);
                                }
                            },
                            url:'?c=EvnPrescr&m=cancelEvnPrescr'
                        });
                    }
                }
            },
            updateRec: function(rec, data){

                var index = this.store.indexOf(rec);
                try {
                    for (var param in data) {
                        rec.set(param, data[param]);

                    }
                    rec.commit();
                   // this.refreshNode(index);
                } catch (e) {
                    //TypeError: d is undefined in d.parentNode.insertBefore(replacement, d);
                    //log([index, rec, data, e, this.store, this]);
					log("OK")
                }
            },
            filterDS: function(){
                this.store.filterBy(function (rec) {
                    return rec.get('RecordStatus') != 'delete';
                });
            },
            assocLockedTimetableMedServiceId: {},
            assocUslugaFrameRec: {},
            assocDirection: {},
            lastNewIndex: 0,
            getNewEvnPrescrKey: function(prescr_type) {
                this.lastNewIndex++;
                return 'new_'+prescr_type+'_'+this.lastNewIndex;
            },
            resetNewIndex: function() {
                this.lastNewIndex = 0;
                this.assocLockedTimetableMedServiceId = {};
                this.assocUslugaFrameRec = {};
                this.assocDirection = {};
            },
            addRec: function(uslugaFrameRec, formParams, direction){
				if (thas.blockAddRec) {
					showSysMsg(lang['pojaluysta_dojdites_zaversheniya_predyiduschey_operatsii'], '', 'warning');
					return false;
				}
				thas.blockAddRec = true;

                var base_form = thas.filterPanel.getForm();
                var prescr_type = base_form.findField('UslugaComplex_id').PrescriptionType_Code;
                var key = this.getNewEvnPrescrKey(prescr_type);
				var uslugaList='';
                //блокировку бирки осуществляем при добавлении в правую часть
				if (uslugaFrameRec.get('withResource') && uslugaFrameRec.get('TimetableResource_id')) {
					sw.Promed.Direction.lockTime(thas, 'TimetableResource', uslugaFrameRec.get('TimetableResource_id'), function(time_id){
						thas.dataView.assocLockedTimetableMedServiceId[key] = time_id;
					});
				} else if (uslugaFrameRec.get('TimetableMedService_id')) {
                    sw.Promed.Direction.lockTime(thas, 'TimetableMedService', uslugaFrameRec.get('TimetableMedService_id'), function(time_id){
                        thas.dataView.assocLockedTimetableMedServiceId[key] = time_id;
                    });
                }
                this.assocUslugaFrameRec[key] = uslugaFrameRec;
                this.assocDirection[key] = direction;
				if (uslugaFrameRec.compositionMenu) {
                    uslugaFrameRec.compositionMenu.items.each(function(item){
                        if (item.checked) {
                            uslugaList+=item.UslugaComplex_id+',';
                        }
                    });
				}
                var dds=[{
                    EvnPrescr_key: key,
                    EvnPrescr_id: null,
                    EvnPrescr_IsExec: null,
                    timetable: null,
                    timetable_id: null,
                    EvnDirection_id: null,
                    PrescriptionType_id: prescr_type,
                    PrescriptionStatusType_id: 1,
                    Usluga_List: getPrescriptionOptions().enable_show_service_code ? uslugaFrameRec.get('UslugaComplex_Code')+' '+uslugaFrameRec.get('UslugaComplex_Name') : uslugaFrameRec.get('UslugaComplex_Name'),
                    UslugaId_List: uslugaFrameRec.get('UslugaComplex_id'),
					EvnPrescr_uslugaList:uslugaList,
                    EvnPrescr_pid: base_form.findField('Evn_pid').getValue(),
                    PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
                    Server_id: base_form.findField('Server_id').getValue(),
                    EvnPrescr_setDate: formParams.EvnPrescr_setDate || base_form.findField('EvnPrescr_setDate').getValue(),
                    EvnPrescr_IsCito: uslugaFrameRec.get('EvnPrescr_IsCito'),
					EvnPrescr_Descr: formParams.EvnPrescr_Descr || '',
                    CountInDay: formParams.CountInDay || null,
                    CourseDuration: formParams.CourseDuration || null,
                    ContReception: formParams.ContReception || null,
                    Interval: formParams.Interval || null,
                    DurationTypeP_Nick: formParams.DurationTypeP_Nick || null,
                    DurationTypeN_Nick: formParams.DurationTypeN_Nick || null,
                    DurationTypeI_Nick: formParams.DurationTypeI_Nick || null,
                    DurationType_id: formParams.DurationType_id || null,
                    DurationType_recid: formParams.DurationType_recid || null,
                    DurationType_intid: formParams.DurationType_intid || null,
                    //IsCito_Name: null,
					EvnPrescr_IsDir:2,
					RecTo:uslugaFrameRec.get('MedService_Nick')+'/'+uslugaFrameRec.get('Lpu_Nick'),
					RecDate:(uslugaFrameRec.get('withResource')?uslugaFrameRec.get('TimetableResource_begTime'):uslugaFrameRec.get('TimetableMedService_begTime'))||lang['v_ochered'],
					withResource: uslugaFrameRec.get('withResource'),
                    IsCito_Code: (uslugaFrameRec.get('EvnPrescr_IsCito'))?1:0,
                    EvnDirection_Num: (direction.EvnDirection_Num && (direction.EvnDirection_Num != '0'))?lang['napravlenie_№']+direction.EvnDirection_Num:'',
                    EvnPrescrGroup_Title: null,
                    RecordStatus:'new',
					EvnPrescrGroup_Count:null,
					PrescriptionDietType_Name:null,
					DrugListData:null,
					Duration:null,
					DurationType_Nick:null,
					PrescriptionIntroType_Name:null,
					PerformanceType_Name:null,
					Params:null,
					EvnXmlDir_id: null,
					EvnXmlDirType_id: null,
					EvnPrescr_setTime:null,
					MedicationRegime:null
                }];
                this.store.loadData(dds,true);
                this.filterDS();

                var new_record;
                this.store.each(function(rec){
                    if (rec.get('EvnPrescrGroup_Count')&&(rec.get('PrescriptionType_id')==prescr_type)) {
                        rec.set('EvnPrescrGroup_Count',(rec.get('EvnPrescrGroup_Count')*1+1));
                        return false;
                    }
                    return true;
                }, this);
                this.store.each(function(rec){
                    if (rec.get('RecordStatus') == 'new' && rec.get('EvnPrescr_key') == key) {
                        new_record = rec;
                        return false;
                    }
                    return true;
                }, this);
                thas._save(new_record, function(rec, error_msg){
					thas.blockAddRec = false;
                    if (error_msg) {
                        sw.swMsg.alert('Ошибка', 'При сохранении назначения услуги <br/>"'+ rec.get('Usluga_List')
                            +'"<br/>произошла ошибка: '+ error_msg);
                    } else {
						if(!Ext.isEmpty(rec.data.EvnDirection_id)) {
							Ext.Ajax.request({
								url: '/?c=WorkList&m=getMedProductCardIsWL',
								params: {
									EvnDirection_id: rec.data.EvnDirection_id,
									MedProductCard_IsWorkList: 2 // есть признак работы с рабочим списком (ref #95987)
								},
								callback: function(options, success, response) {
									var result = Ext.util.JSON.decode(response.responseText)[0];
									if(success) {
										if(result) {
											showSysMsg(result.message, 'Рабочие списки');
										}
									} 
								}
							});
						}
                        thas.hasChange = true;
						if (!prescr_type.inlist(thas.changedType)) {
							thas.changedType.push(prescr_type);
						}
                        thas.uslugaFrame.refreshRecords(null,0);
                    }
                });
            }
        });

        this.dataViewPanel = new Ext.Panel({
            region: 'center',
            autoScroll: true,
            minSize: 400,
            id: 'EvnPrescrUslugaInputDataViewPanel',
            layout:'border',
            frame: false,
			border: false,
            bodyStyle: 'background-color: #fff;',
            width: 400,
            items: [
                this.dataView
            ]
        });

        var uslugaFrameKeyColumnName = 'Unique_id';
        this.uslugaFrame = new sw.Promed.ViewFrame({
            id: 'EvnPrescrUslugaInputViewFrame',
            keyColumnName: uslugaFrameKeyColumnName,
			grouping: true,
			groupingView: {showGroupName: false, showGroupsText: true},
			// показываем группы только, если пришёл Group_id
			startGroup: new Ext.XTemplate(
				'<div id="{groupId}" class="x-grid-group {cls}">',
				'<div id="{groupId}-hd" class="x-grid-group-hd" style="{style} {[ values.rs[0].data["Group_id"]?"":"display:none;" ]}"><div>', '<b>{[ values.rs[0].data["pzm_MedService_Nick"] ]} {[ values.rs[0].data["MedService_Nick"] ]}</b>' ,'</div></div>',
				'<div id="{groupId}-bd" class="x-grid-group-body">'
			),
			interceptMouse : function(e){
				var win = this;
				var view = this.uslugaFrame.getGrid().getView();
				var collapsed = e.getTarget('.x-grid-group-collapsed', view);
				var hd = e.getTarget('.x-grid-group-hd', view);
				var hdel = e.getTarget('.x-grid-group-hd', view, true);
				if(hd) {
					e.stopEvent();
					var id = hd.id;
					if (collapsed && id.indexOf('Group_id') > -1) { // выполняем запрос только если свёрнуто
						// Извлекаем данные по группе
						id = id.substr(id.indexOf('Group_id'));
						id = id.split('-');
						var Group_id = id[1];
						// mb - для установки скролла на прежнюю позицию после загрузке и раскрытии группы 
						var mb = view.scroller.dom.scrollTop;
						var initGroup = Group_id;
						var curGroupCount = 0;
						var groupsIds = [];
						var groups = {};
						this.uslugaFrame.getGrid().getStore().each(function(rec){
							if(rec.get('Group_id') == Group_id){
								// количество услуг в текущей группе
								curGroupCount++;
							}
							if(!rec.get('Group_id').inlist(groupsIds)){
								// запись статуса групп до загрузки (закрыты/раскрыты) 
								groupsIds.push(rec.get('Group_id'));
								var grop = view.getGroupId(rec.get('Group_id'));
								grp = Ext.getDom(grop);
								var gel = Ext.fly(grp);
								var exp = gel.hasClass('x-grid-group-collapsed');
								groups[grop] = exp;
							}
						});
						// Group_id может состоять из 2 компонентов (MedService_id и pzm_MedService_id)
						var MedService_id = Group_id;
						var pzm_MedService_id = '';
						if(Group_id.indexOf('_') > -1){
							GroupParts = Group_id.split('_');
							MedService_id = GroupParts[0];
							pzm_MedService_id = GroupParts[1];
						}
						// Общие фильтры грида
						var base_form = this.filterPanel.getForm();
				        if (!base_form.findField('UslugaComplex_id').PrescriptionType_Code) {
				            sw.swMsg.alert(lang['soobschenie'], lang['vyiberite_tip_naznacheniya']);
				            return false;
				        }
				        var ucombo = base_form.findField('UslugaComplex_id');
				        var lcombo = base_form.findField('Lpu_id');
				        var baseParams = swCloneObject(ucombo.getStore().baseParams);
				        baseParams.userLpuSection_id = this.userMedStaffFact.LpuSection_id;
				        //пытаемся разрулить ситуацию когда getValue возвращает UslugaComplex_id одной услуги
				        //а в комбо введено полностью или частично наименование другой услуги
				        var id = ucombo.getValue();
				        var str = ucombo.getRawValue()||'';
				        var rec;
				        if (id) {
				            rec = ucombo.getStore().getById(id);
				        }
				        if (!rec || -1 == (rec.get(ucombo.codeField) + '. ' + rec.get(ucombo.displayField)).toLowerCase().indexOf(str.toLowerCase())) {
				            ucombo.setValue(null);
				            ucombo.setRawValue(str);
				        }
				        if (ucombo.getValue()) {
				            baseParams.filterByUslugaComplex_id = ucombo.getValue();
							baseParams.filterByUslugaComplex_str = null;
				        } else {
				            baseParams.filterByUslugaComplex_str = ucombo.getRawValue()||null;
							baseParams.filterByUslugaComplex_id = null;
				        }
				        //пытаемся разрулить ситуацию когда getValue возвращает Lpu_id одной МО
				        //а в комбо введено полностью или частично наименование другой МО
				        id = lcombo.getValue();
				        str = lcombo.getRawValue()||'';
				        rec = null;
				        if (id) {
				            rec = lcombo.getStore().getById(id);
				        }
				        if (!rec || str.toLowerCase() != rec.get(lcombo.displayField).toLowerCase()) {
				            lcombo.setValue(null);
				            lcombo.setRawValue(str);
				        }
				        if (lcombo.getValue()) {
				            baseParams.filterByLpu_str = null;
				            baseParams.filterByLpu_id = lcombo.getValue();
				        } else {
				            baseParams.filterByLpu_str = lcombo.getRawValue()||null;
				            baseParams.filterByLpu_id = null;
				        }
				        id = this.medServiceFilterCombo.getValue();
				        if (id) {
				            baseParams.filterByMedService_id = id;
				        } else {
				            baseParams.filterByMedService_id = null;
				        }
				        baseParams.isOnlyPolka = this.isOnlyPolka;
				        baseParams.limit = this.uslugaFrame.pageSize;

				        // добавляем данные текущей группы для загрузки
				        baseParams.MedService_id = MedService_id;
				        baseParams.pzm_MedService_id = pzm_MedService_id;

						win.getLoadMask(LOAD_WAIT).show();
						Ext.Ajax.request({
                            failure:function () {
                                win.getLoadMask().hide();
                            },
                            params:baseParams,
                            success:function (response) {
                                if (response.responseText) {
                                    var answer = Ext.util.JSON.decode(response.responseText);
									if (answer.data) {
										// все полученные записи относятся к группе Group_id
										for (var k in answer.data) {
											answer.data[k]['Group_id'] = Group_id;
										}
									}

									var r = win.uslugaFrame.getGrid().getStore().reader.readRecords(answer);
									var options = {
										add: true,
										scope: win,
										callback: function(){
											// переформируем группы после подгрузки
											win.uslugaFrame.getGrid().getStore().groupBy('Group_id',true);
											// восстанавливаем состояние групп до загрузки
											for (var prop in groups) {
												var exp = groups[prop];
												if(exp) {
													exp = false;
												} else {
													exp = true;
												}
												var gel = Ext.get(prop);
												if (gel) view.toggleGroup(prop,exp);
											}
											// раскрываем текущую группу
											var grp = view.getGroupId(initGroup);
											view.toggleGroup(grp);
											view.scroller.dom.scrollTop = mb;
										}
									};
									win.uslugaFrame.getGrid().getStore().loadRecords(r,options,true);
									// очищаем фильтры грида от данных текущей группы
									win.uslugaFrame.getGrid().getStore().baseParams.MedService_id = null;
									win.uslugaFrame.getGrid().getStore().baseParams.pzm_MedService_id = null;
                                }
                                win.getLoadMask().hide();
                            },
                            url:'/?c=MedService&m=getUslugaComplexSelectList'
                        });
					} else {
						view.toggleGroup(hd.parentNode);
					}
				}
			}.createDelegate(this),
            actions: [
                {name:'action_add', hidden: true, disabled: true},
                {name:'action_edit', hidden: true, disabled: true},
                {name:'action_view', hidden: true, disabled: true},
                {name:'action_delete', hidden: true, disabled: true},
                {name:'action_refresh', hidden: true, disabled: true},
                {name:'action_print', hidden: true, disabled: true},
                {name:'action_resetfilter', hidden: true, disabled: true},
                {name:'action_save', hidden: true, disabled: true}
            ],
            stringfields: [
                {name: uslugaFrameKeyColumnName, type: 'string', header: 'ID', key: true},
                {name: 'isComposite', type: 'int', hidden: true},
                {name: 'UslugaComplex_2011id', type: 'int', hidden: true},
                {name: 'UslugaComplex_Code', type: 'string', hidden: true},
                {name: 'UslugaComplex_Name', type: 'string', hidden: true},
                {name: 'UslugaComplex_AttributeList', type: 'string', hidden: true},
                {name: 'UCUslugaComplex_Name', type: 'string', hidden: true },
                {name: 'AnalyzerTest_Name', type: 'string', hidden: true },
                {name: 'UslugaComplex_FullName', header: lang['usluga'], sortable: true, autoexpand: true, autoExpandMin: 150, renderer: function(value, cellEl, rec){
                    if(!rec.get('UslugaComplex_Name')) return '';
                    var value = '';
                    switch (true) {
                        case getRegionNick() == 'ufa' && thas.PrescriptionType_Code == 11:
                            var codeField = getPrescriptionOptions().enable_show_service_code ? "<div style='color:blue'>" + rec.get('UslugaComplex_Code') + "</div>" : '';
                            value = '<div>' + codeField;
                            value += "<div style='color:blue'>" + rec.get('UCUslugaComplex_Name') + "</div>";
                            value += "<div>" + rec.get('AnalyzerTest_Name') + "</div>";
                            value += "</div>";
                        break;

                        case getPrescriptionOptions().enable_show_service_code:
                            value += rec.get('UslugaComplex_Code') + ' ';
                        default:
                            value += rec.get('UslugaComplex_Name');
                    }
                    return value;
                }},
                {name: 'compositionCntAll', type: 'int', hidden: true},
                {name: 'compositionCntChecked', type: 'int', hidden: true},
                {name: 'composition', id: 'EPUIVF_composition', header: lang['sostav'], width: 90, sortable: true, renderer: function(value, cellEl, rec){
                    var PrescriptionType_id = thas.filterPanel.getForm().findField('UslugaComplex_id').PrescriptionType_Code;
                    if (PrescriptionType_id==11 && 1 == rec.get('isComposite')) {
                        var text = lang['izmenit'];
                        if (rec.get('compositionCntAll') > 0) {
                            text += ' ('+rec.get('compositionCntChecked')+'/'+rec.get('compositionCntAll')+')';
                        }
                        return '<a href="#" ' +
                            'id="composition_'+ rec.get(uslugaFrameKeyColumnName) +'" '+
                            'onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\').showComposition('+
                            "'"+ rec.get(uslugaFrameKeyColumnName) +"'"+
                            ')">'+ '<span class="button"><span>' +'</a>';
                    }
                    return '';
                }},
                {name: 'EvnPrescr_IsCito', header: 'Cito!', type: 'checkcolumnedit', sortable: false, width: 35},

                //ниже параметры службы, где оказывается услуга
                {name: 'MedService_cnt', type: 'int', hidden: true},
                {name: 'UslugaComplexMedService_id', type: 'int', hidden: true},
                {name: 'pzm_UslugaComplexMedService_id', type: 'int', hidden: true},
                {name: 'UslugaComplex_id', type: 'int', hidden: true},
                {name: 'Lpu_id', type: 'int', hidden: true},
                {name: 'Lpu_Nick', type: 'string', hidden: true},
                {name: 'LpuBuilding_id', type: 'int', hidden: true},
                {name: 'LpuBuilding_Name', type: 'string', hidden: true},
                {name: 'LpuUnit_id', type: 'int', hidden: true},
                {name: 'LpuUnit_Name', type: 'string', hidden: true},
                {name: 'LpuUnitType_id', type: 'int', hidden: true},
                {name: 'LpuUnitType_SysNick', type: 'string', hidden: true},
                {name: 'LpuSection_id', type: 'int', hidden: true},
                {name: 'LpuSection_Name', type: 'string', hidden: true},
                {name: 'LpuSectionProfile_id', type: 'int', hidden: true},
                {name: 'LpuUnit_Name', type: 'string', hidden: true},
                {name: 'LpuUnit_Address', type: 'string', hidden: true},
                {name: 'Group_id', type: 'string', hidden: true, group: true, sort: true, direction: 'ASC'},
                {name: 'MedService_id', type: 'int', hidden: true},
                {name: 'MedService_Nick', type: 'string', hidden: true},
                {name: 'MedService_Name', type: 'string', hidden: true},
                {name: 'MedServiceType_id', type: 'int', hidden: true},
                {name: 'MedServiceType_SysNick', type: 'string', hidden: true},
                {name: 'Resource_id', type: 'int', hidden: true},
                {name: 'Resource_Name', type: 'string', hidden: true},
                {name: 'withResource', type: 'int', hidden: true},
                {name: 'location', header: lang['mesto_okazaniya'], width: 200, sortable: true, renderer: function(value, cellEl, rec){
                    if (!rec.get('UslugaComplex_Name')) return '';
                    if (!rec.get('MedService_id')) {
						return '';
                    }

                    //если есть одна служба, то в этой колонке должен быть текст
                    var text = rec.get('MedService_Nick') +'<br>'+ rec.get('Lpu_Nick');
                    var hint = rec.get('MedService_Name') +' / '+ rec.get('Lpu_Nick') +' / '+
                        rec.get('LpuUnit_Name') +' / '+ rec.get('LpuUnit_Address');
                    // если это назначение лабораторной диагностики и есть пункт забора
                    if (rec.data.pzm_MedService_id) {
                        //то отображаем пункт забора как место оказания
                        text = rec.get('pzm_MedService_Nick') +'<br>'+ rec.get('MedService_Nick') +'<br>'+ rec.get('Lpu_Nick');
                        hint = rec.get('pzm_MedService_Name') +' / '+ rec.get('MedService_Name') +' / '+ rec.get('Lpu_Nick');
                    }

                    if (rec.get('MedService_cnt') > 1 ) {
                        // если есть несколько служб,
                        // то в этой колонке должен быть текст,
                        // при клике по которому должен отобразиться комбик выбора служб
                        return '<div id="uc2011_MedServiceSelectLink_'+
                            rec.get(uslugaFrameKeyColumnName) +
                            '" style="display: block"><a class="MedServiceSelectComboLink" href="#" title="'+
                            hint +'">'+
                            text +'</a></div>'+
                            '<div style="display: none"'+
                            'id="uc2011_MedServiceSelectComboWrap_'+ rec.get(uslugaFrameKeyColumnName) +'">'+
                            '</div>';
                    } else {
                        return '<span title="'+ hint +'">'+ text +'</span>';
                    }
                }},

                // ниже параметры для записи
                {name: 'ttms_MedService_id', type: 'int', hidden: true},
                {name: 'ttr_Resource_id', type: 'int', hidden: true},
                {name: 'pzm_Lpu_id', type: 'int', hidden: true},
                {name: 'pzm_MedService_id', type: 'int', hidden: true},
                {name: 'pzm_MedServiceType_id', type: 'int', hidden: true},
                {name: 'pzm_MedServiceType_SysNick', type: 'string', hidden: true},
                {name: 'pzm_MedService_Nick', type: 'string', hidden: true},
                {name: 'pzm_MedService_Name', type: 'string', hidden: true},
                {name: 'TimetableMedService_id', type: 'int', hidden: true},
                {name: 'TimetableMedService_begTime', type: 'string', hidden: true},
                {name: 'TimetableResource_id', type: 'int', hidden: true},
                {name: 'TimetableResource_begTime', type: 'string', hidden: true},
                {name: 'timetable', header: lang['zapis'], width: 100, sortable: true, renderer: function(value, cellEl, rec){
                    if (!rec.get('UslugaComplex_Name')) return '';
					var begTime = rec.get('withResource')?rec.get('TimetableResource_begTime'):rec.get('TimetableMedService_begTime');
                    var text = thas.renderTimetableBegTime(begTime, rec.get(uslugaFrameKeyColumnName));
                    return '<span id="render_timetable_begtime_'+ rec.get(uslugaFrameKeyColumnName) +'">'+ text +'</span>';
                }},
				{
                    name: 'doInsert', header: ' ', width: 100, sortable: false, hideable: false, renderer: function(value, cellEl, rec){
						if (!rec.get('UslugaComplex_Name')) return '';
						return '<a class="addButton"' +
							'id="insert_btn_'+ rec.get(uslugaFrameKeyColumnName) +'" '+
							'onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\').doPrescr('+
							"'"+ rec.get(uslugaFrameKeyColumnName) +"'"+
							')"><img src="/img/EvnPrescrPlan/add.png" title="Назначить" /> Назначить</a>';//<a href="#" ><img src="/img/EvnPrescrPlan/add.png" title="Добавить" /> Добавить</a>
					}
                },
				{name: 'ElectronicQueueInfo_id', type: 'int', hidden: true},
				{name: 'ElectronicService_id', type: 'int', hidden: true},
            ],
            autoLoadData: false,
            border: true,
            //dataUrl: '/?c=MedService&m=getUslugaComplexMedServiceList',
            dataUrl: '/?c=MedService&m=getUslugaComplexSelectList',
            object: 'UslugaComplex',
            layout: 'fit',
            height: 300,
            root: 'data',
            totalProperty: 'totalCount',
            paging: true,
            pageSize: 500,
            remoteSort: true,
            region: 'center',
            toolbar: false,
            editing: true,
            onAfterEditSelf: function(o) {
                o.record.commit();
            },
            onLoadData: function() {
                if (thas.groupMode == 1) {
					if (!Ext.isEmpty(thas.uslugaFrame.getGrid().getStore().baseParams.filterByUslugaComplex_id) || !Ext.isEmpty(thas.uslugaFrame.getGrid().getStore().baseParams.filterByUslugaComplex_str)) {
						thas.uslugaFrame.getGrid().getView().expandAllGroups();
					} else {
						thas.uslugaFrame.getGrid().getView().collapseAllGroups();
					}
				}

				if (thas.IsCito) {
					// проставляем галки Cito
					thas.uslugaFrame.getGrid().getStore().each(function(rec) {
						if (!Ext.isEmpty(rec.get(uslugaFrameKeyColumnName))) {
							rec.set('EvnPrescr_IsCito', true);
							rec.commit();
						}
					});
				}
            },
            onMouseDownViewBody: function(evn, domNode) {
                var el = new Ext.Element(domNode);
                var link;
                if (el.hasClass('MedServiceSelectComboLink')) {
                    link = el;
                } else {
                    link = el.child("*[class=MedServiceSelectComboLink]");
                }
                if (link) {
                    evn.stopEvent();
                    var grid = this.getGrid();
                    var index = grid.getView().findRowIndex(domNode);
                    var record = grid.getStore().getAt(index);
                    if (record) {
                        thas.showMedServiceSelectCombo({
                            linkBlock: Ext.get('uc2011_MedServiceSelectLink_'+ record.id),
                            comboBlock: Ext.get('uc2011_MedServiceSelectComboWrap_'+ record.id),
                            frame: this,
                            grid: grid,
                            record: record,
                            rowIndex: index
                        });
                    }
                }
            },
            onDblClick: function() {
                this.onEnter();
            },
            onEnter: function() {
                var rec = this.getGrid().getSelectionModel().getSelected();
                if (rec) {
                    thas.doInsert(rec.get(uslugaFrameKeyColumnName));
                }
            }
        });

        Ext.apply(this, {
//            buttonAlign: "right",
            buttons: [{
                text: '-'
            },
            {
                text: BTN_FRMHELP,
                iconCls: 'help16',
                handler: function() {
                    ShowHelp(thas.winTitle);
                }.createDelegate(self)
            },
            {
                handler: function() {
                    thas.hide();
                },
                iconCls: 'cancel16',
                text: BTN_FRMCLOSE
            }],
            border: false,
            layout: 'border',
            items: [
                new Ext.Panel({
                    region: 'west',
                    width: 400,
                    layout:'border',
                    listeners:
                    {
                        render: function(p) {
                            var body_width = Ext.getBody().getViewSize().width;
                            p.setWidth(body_width * (7/12));
                        }
                    },
                    items: [
                        this.filterPanel,
                        this.uslugaFrame
                    ]
                }),
                this.dataViewPanel
            ]
        });
        sw.Promed.swEvnPrescrUslugaInputWindow.superclass.initComponent.apply(this, arguments);

        this.uslugaFrame.getGrid().on('render', function(){
            var view = this.uslugaFrame.getGrid().getView();
            view.mainBody.on('mousedown', this.uslugaFrame.onMouseDownViewBody, this.uslugaFrame);
        }, this);
    }
});

/**
 * Комбобокс Место оказания
 */
sw.Promed.SwUc2011MedServiceSelectCombo = Ext.extend(Ext.form.ComboBox,
{
    displayField: 'MedService_Nick',
    enableKeyEvents: true,
    editable: false,
    fieldLabel: lang['mesto_okazaniya'],
    forceSelection: true,
    hiddenName: 'UslugaComplexMedService_key',
    _isLoaded: false,
    _hasProccessingLoad: false,
    _doLoad: function() {
        var combo = this;
        if (!combo._isLoaded && !combo._hasProccessingLoad) {
            combo._hasProccessingLoad = true;
            combo.getStore().load({
                callback: function()
                {
                    combo._isLoaded = true;
                    combo._hasProccessingLoad = false;
                    if (combo.getStore().getCount()>0) {
                        //combo.setValue(combo.getStore().getAt(0).get('UslugaComplexMedService_key'));
                    }
                }
            });
        }
    },
    onTrigger1Click: function()
    {
        if (this.disabled)
            return false;
        if (!this.isExpanded()) {
            this._doLoad();
            this.expand();
        } else {
            this.collapse();
        }
        return true;
    },
    beforequery: function() {
        return false;
    },
    initComponent: function() {
        Ext.form.TwinTriggerField.prototype.initComponent.apply(this, arguments);
        this.store = new Ext.data.Store({
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                totalProperty: 'totalCount',
                root: 'data',
                id: 'UslugaComplexMedService_key'
            }, [
                {name: 'UslugaComplexMedService_key', mapping: 'UslugaComplexMedService_key', type: 'string'},
                {name: 'UslugaComplexMedService_id', mapping: 'UslugaComplexMedService_id', type: 'int'},
                {name: 'pzm_UslugaComplexMedService_id', mapping: 'pzm_UslugaComplexMedService_id', type: 'int'},
                {name: 'isComposite', mapping: 'isComposite', type: 'int'},
                {name: 'MedService_id', mapping: 'MedService_id', type: 'int'},
                {name: 'UslugaComplex_id', mapping: 'UslugaComplex_id', type: 'int'},
                {name: 'LpuUnit_id', mapping: 'LpuUnit_id', type: 'int'},
                {name: 'Lpu_id', mapping: 'Lpu_id', type: 'int'},
                {name: 'LpuBuilding_id', mapping: 'LpuBuilding_id', type: 'int'},
                {name: 'LpuSection_id', mapping: 'LpuSection_id', type: 'int'},
                {name: 'LpuUnitType_id', mapping: 'LpuUnitType_id', type: 'int'},
                {name: 'LpuSectionProfile_id', mapping: 'LpuSectionProfile_id', type: 'int'},
                {name: 'MedServiceType_id', mapping: 'MedServiceType_id', type: 'int'},
                {name: 'LpuUnitType_SysNick', mapping: 'LpuUnitType_SysNick', type: 'string'},
                {name: 'MedServiceType_SysNick', mapping: 'MedServiceType_SysNick', type: 'string'},
                {name: 'UslugaComplex_Code', mapping: 'UslugaComplex_Code', type: 'string'},
                {name: 'UslugaComplex_Name', mapping: 'UslugaComplex_Name', type: 'string'},
                {name: 'MedService_Nick', mapping: 'MedService_Nick', type: 'string'},
                {name: 'MedService_Name', mapping: 'MedService_Name', type: 'string'},
                {name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string'},
                {name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name', type: 'string'},
                {name: 'LpuUnit_Name', mapping: 'LpuUnit_Name', type: 'string'},
                {name: 'LpuSection_Name', mapping: 'LpuSection_Name', type: 'string'},
                {name: 'LpuUnit_Address', mapping: 'LpuUnit_Address', type: 'string'},
                {name: 'ttms_MedService_id', mapping: 'ttms_MedService_id', type: 'int'},
                {name: 'lab_MedService_id', mapping: 'lab_MedService_id', type: 'int'},
                {name: 'pzm_MedService_id', mapping: 'pzm_MedService_id', type: 'int'},
                {name: 'pzm_Lpu_id', mapping: 'pzm_Lpu_id', type: 'int'},
                {name: 'pzm_MedServiceType_id', mapping: 'pzm_MedServiceType_id', type: 'int'},
                {name: 'pzm_MedServiceType_SysNick', mapping: 'pzm_MedServiceType_SysNick', type: 'string'},
                {name: 'pzm_MedService_Nick', mapping: 'pzm_MedService_Nick', type: 'string'},
                {name: 'pzm_MedService_Name', mapping: 'pzm_MedService_Name', type: 'string'},
                {name: 'TimetableMedService_id', mapping: 'TimetableMedService_id', type: 'int'},
                {name: 'TimetableMedService_begTime', mapping: 'TimetableMedService_begTime', type: 'string'},
                {name: 'TimetableResource_begTime', mapping: 'TimetableResource_begTime', type: 'string'},
                {name: 'TimetableResource_id', mapping: 'TimetableResource_id', type: 'int'},
                {name: 'Resource_id', mapping: 'Resource_id', type: 'int'},
                {name: 'Resource_Name', mapping: 'Resource_Name', type: 'string'},
                {name: 'ttr_Resource_id', mapping: 'ttr_Resource_id', type: 'int'},
            ]),
            url: '/?c=MedService&m=getMedServiceSelectCombo'
        });

        this.addListener('keydown', function(inp, e)
        {
            //inp.removeListener('beforequery', inp.beforequery);
            if (e.getKey() == e.F4 || e.getKey() == e.F2 || (e.getKey() == e.DOWN && !inp.isExpanded()))
            {
                e.stopEvent();
                if (e.browserEvent.stopPropagation)
                {
                    e.browserEvent.stopPropagation();
                }
                else
                {
                    e.browserEvent.cancelBubble = true;
                }
                if (e.browserEvent.preventDefault)
                {
                    e.browserEvent.preventDefault();
                }
                else
                {
                    e.browserEvent.returnValue = false;
                }

                e.returnValue = false;

                if (Ext.isIE)
                {
                    e.browserEvent.keyCode = 0;
                    e.browserEvent.which = 0;
                }
                switch (e.getKey())
                {
                    case e.F4:
                        //inp.addListener('beforequery', inp.beforequery);
                        inp.onTrigger2Click();
                        break;
                    case e.F2: case e.DOWN:
                    //inp.addListener('beforequery', inp.beforequery);
                    inp.focus();
                    inp.onTrigger1Click();
                    break;
                }
            }
        },
        this);
    },
    loadingText: lang['idet_poisk'],
    queryDelay: 500,
    minChars: 3,
    minLength: 1,
    mode: 'remote',
    minLengthText: lang['pole_doljno_byit_zapolneno'],
    resizable: true,
    selectOnFocus: true,
    tpl: new Ext.XTemplate(
        '<tpl for="."><div class="x-combo-list-item">',
        '<b>{pzm_MedService_Nick}&nbsp;{MedService_Nick}</b>&nbsp;<span style="color:#777;">{Lpu_Nick}</span>',
        //вылазит ошибка ReferenceError: pzm_MedService_Nick is not defined
        //почему? - так и не понял, в сторе оно есть
        /*
        '<tpl if="!(pzm_MedService_Nick)">',
            '<h3>{MedService_Nick}&nbsp;{Lpu_Nick}</h3>',
        '</tpl>',
        '<tpl if="(pzm_MedService_Nick)">',
            '<h3>{pzm_MedService_Nick}&nbsp;{MedService_Nick}&nbsp;{Lpu_Nick}</h3>',
        '</tpl>',
        */
        '</div></tpl>'
    ),
    triggerAction: 'all',
    valueField: 'UslugaComplexMedService_key'
});
sw.Promed.SwUc2011MedServiceSelectCombo.prototype.getTrigger = Ext.form.TwinTriggerField.prototype.getTrigger;
sw.Promed.SwUc2011MedServiceSelectCombo.prototype.initTrigger = Ext.form.TwinTriggerField.prototype.initTrigger;
sw.Promed.SwUc2011MedServiceSelectCombo.prototype.trigger2Class = 'x-form-search-trigger';
Ext.reg('swuc2011medserviceselectcombo', sw.Promed.SwUc2011MedServiceSelectCombo);

/**
 * Комбобокс Служба
 */
sw.Promed.SwMedServiceFilterCombo = Ext.extend(Ext.form.ComboBox,
    {
        displayField: 'MedService_Nick',
        enableKeyEvents: true,
        editable: true,
        lastQuery: '',
        fieldLabel: lang['slujba'],
        forceSelection: true,
        hiddenName: 'MedService_id',
        onTrigger1Click: function()
        {
            this.onTriggerClick();
        },
        beforequery: function() {
            this.lastQuery = '';
            return true;
        },
        initComponent: function() {
            Ext.form.TwinTriggerField.prototype.initComponent.apply(this, arguments);
            this.store = new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'MedService_id'
                }, [
                    {name: 'MedService_id', mapping: 'MedService_id', type: 'int'},
                    {name: 'Lpu_id', mapping: 'Lpu_id', type: 'int'},
                    {name: 'MedServiceType_id', mapping: 'MedServiceType_id', type: 'int'},
                    {name: 'MedService_Nick', mapping: 'MedService_Nick', type: 'string'},
                    {name: 'MedService_Name', mapping: 'MedService_Name', type: 'string'},
                    {name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string'}
                ]),
                url: '/?c=MedService&m=loadFilterCombo'
            });

            this.addListener('keydown', function(inp, e)
                {
                    //inp.removeListener('beforequery', inp.beforequery);
                    if (e.getKey() == e.F4 || e.getKey() == e.F2 || (e.getKey() == e.DOWN && !inp.isExpanded()))
                    {
                        e.stopEvent();
                        if (e.browserEvent.stopPropagation)
                        {
                            e.browserEvent.stopPropagation();
                        }
                        else
                        {
                            e.browserEvent.cancelBubble = true;
                        }
                        if (e.browserEvent.preventDefault)
                        {
                            e.browserEvent.preventDefault();
                        }
                        else
                        {
                            e.browserEvent.returnValue = false;
                        }

                        e.returnValue = false;

                        if (Ext.isIE)
                        {
                            e.browserEvent.keyCode = 0;
                            e.browserEvent.which = 0;
                        }
                        switch (e.getKey())
                        {
                            case e.F4:
                                //inp.addListener('beforequery', inp.beforequery);
                                inp.onTrigger2Click();
                                break;
                            case e.F2: case e.DOWN:
                            //inp.addListener('beforequery', inp.beforequery);
                            inp.focus();
                            inp.onTrigger1Click();
                            break;
                        }
                    }
                },
                this);
        },
        loadingText: lang['idet_poisk'],
        queryDelay: 500,
        minChars: 3,
        minLength: 1,
        mode: 'remote',
        minLengthText: lang['pole_doljno_byit_zapolneno'],
        resizable: true,
        selectOnFocus: true,
        tpl: new Ext.XTemplate(
            '<tpl for="."><div class="x-combo-list-item">',
            '<b>{MedService_Nick}</b>&nbsp;<span style="color:#777;">{Lpu_Nick}</span>',
            '</div></tpl>'
        ),
        triggerAction: 'all',
        valueField: 'MedService_id'
    });
sw.Promed.SwMedServiceFilterCombo.prototype.getTrigger = Ext.form.TwinTriggerField.prototype.getTrigger;
sw.Promed.SwMedServiceFilterCombo.prototype.initTrigger = Ext.form.TwinTriggerField.prototype.initTrigger;
sw.Promed.SwMedServiceFilterCombo.prototype.trigger2Class = 'x-form-search-trigger';
Ext.reg('SwMedServiceFilterCombo', sw.Promed.SwMedServiceFilterCombo);
