/**
* swListSearchWindow - универсальное окно поиска и выбора по коду / наименованию / сокращенному наименованию / описанию.
* Форма вначале инициализируется с указанием необходимых параметров, затем вызывается методом show. При вызове метода show 
* в форму передаются методы onSelect и onHide, а также произвольный набор параметров, передаваемый на сервер по адресу dataUrl
* и используемый для постоянной фильтрации поиска данных.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Common
* @access       public
* @copyright    Swan Ltd
* @author       Марков Андрей
* @version      10.11.2009
*/

sw.Promed.swListSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction : 'hide',
	title: '',
	draggable: true,
	height: 500,
	width: 700,
	layout: 'border',
	modal: true,
	plain: true,
	resizable: false,
	/**
	* @cfg {String} наименование объекта (обязательное поле). Используется для задания id формы, в случае отсутствия параметра id, 
	* а также для формирования списка найденных значений 
	*/
	object: null,
	/**
	* Передается в методе show текущей формы из вызываемой формы. Возвращает в форму вызова данные выбранной записи грида.
	* @return {params} - объект, содержащий данные выбранной записи грида.
	*/
	onSelect: Ext.emptyFn,
	/**
	* Передается в методе show текущей формы из вызываемой формы. Содержит необходимые действия, выполняемые при закрытии текущей формы.
	*/
	onHide: Ext.emptyFn,
	// Это уже свои параметры
	/**
	* @cfg {String/Object} содержит объект формы или строку с наименованием формы, которая будет открываться при 
	* на добавления/просмотр/редактирование в списке поиска. Если форма не указана, то функции добавления/просмотра/редактирования/удаления
	* не будут доступны.
	*/
	editformclassname: null,
	diableActions:true,
	/**
	* @cfg {String} префикс, добавляемый ко всем создаваемым компонентам на форме.
	*/
	prefix: 'lff', 
	/**
	* @cfg {Boolean} False - не скрывать форму после выбора элемента списка (по умолчанию true)
	*/
	autoHide: true, 
	/**
	* @cfg {String} Url- адрес, при обращении к которому возвращаются данные для списка в json-формате. Если Url не указан,
	* то при указании параметра store возьмет Url из указанного объекта. Если и store и dataUrl не указаны - при инициализации 
	* формы будет показана ошибка, информирующая о отсутствии данного свойства.
	*/
	dataUrl: null,
	/**
	* @cfg {Array} массив используется для инициализации грида и для получения полей фильтрации, если не указан параметр store 
	* (в этом случае данные берутся из параметра store). 
	* Если конкретному элементу массива принудительно указать hidden: false, то данный элемент будет выводиться в списке 
	* найденных значений.
	*/
	stringfields: null,
	/**
	* @cfg {Object} store необходимого компонента, в частном случае store поля с типом combobox, с которого открываем данную форму
	*/
	store: null,
	/**
	* @cfg {Boolean} True - использовать для фильтрации значения baseParams store комбобокса (используется если store указан, по умолчанию False)
	*/
	useBaseParams: false,
	listeners: 
	{
		hide: function() 
		{
			this.onHide();
		}
	},
	// private 
	isEmplyRecord: function(o)
	{
		var nef = true;
		if (o)
		{
			for(var key in o) 
			{
				if (o[key]!='')
				{
					nef = false;
					break;
				}
			}
		}
		return nef;
	},
	onOkButtonClick: function() 
	{
		// Возможно нужно возвращать сообщение, что нечего выбрать
		if (!this.List.getGrid().getSelectionModel().getSelected())
		{
			this.hide();
			return false;
		}
		var record = this.List.getGrid().getSelectionModel().getSelected();
		var params = record.data;
		if (!this.isEmplyRecord(params))
		{
			if (this.autoHide) 
			{
				this.hide();
			}
			this.onSelect(params);
		}
	},
	doReset: function() 
	{
		this.List.getGrid().getStore().baseParams = {};
		this.List.removeAll(true);
		this.findById(this.id+'ListForm').getForm().reset();
		Ext.getCmp(this.prefix+'ButtonOk').show();
		Ext.getCmp(this.prefix+'ButtonOk').setDisabled(true);
		// Поле на которое ставим фокус
		this.findById(this.prefix+this.field[0].name).focus(true,250);
		// Может быть так? 
		//this.List.loadData();
	},
	doSearch: function() 
	{
		var form = this;
		var grid = this.List.getGrid();
		var params = this.findById(this.id+'ListForm').getForm().getValues();
		if (form.isEmplyRecord(params))
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_zadanyi_usloviya_poiska'], function() {form.findById(form.prefix+form.field[0].name).focus(true, 250);});
			return false;
		}
		// Если используется пейджинг, то проверяем начальные значения (они могут быть заданы в show)
		if (form.paging && form.params && !form.params.start)
		{
			form.params.start = 0;
			form.params.limit = 100;
		}
		var params = Ext.apply(form.params || {}, params || {});
		
		grid.getStore().removeAll();
		this.List.loadData({globalFilters:params});
	},
	show: function() 
	{
		sw.Promed.swListSearchWindow.superclass.show.apply(this, arguments);
		this.onSelect = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka_otkryitiya_formyi']+this.id, lang['ne_ukazanyi_vhodnyie_parametryi']);
			this.hide();
		}
		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].onSelect)
		{
			this.onSelect = arguments[0].onSelect;
		}
		this.params = new Object();
		// Загоняем baseParams
		if (this.store && this.useBaseParams)
		{
			for(var key in this.store.baseParams) 
			{
				if (!key.inlist(['query']))
				{
					this.params[key] = this.store.baseParams[key];
				}
			}
		}
		// Загоняем params 
		if (arguments[0].params)
		{
			for(var key in arguments[0].params) 
			{
                if (!key.inlist(['query']))
                {
                    this.params[key] = arguments[0].params[key];
                }
			}
		}
		
		// Загоняем пришедшие в форму параметры
		for(var key in arguments[0]) 
		{
			if (!key.inlist(['onHide','onSelect','params']))
			{
				this.params[key] = arguments[0][key];
			}
		}
		if(this.editformclassname=='swOrgEditWindow'){
			this.List.ViewActions.action_delete.items[0].setDisabled(true);
			this.List.ViewActions.action_delete.items[1].setDisabled(true);
			this.List.ViewActions.action_delete.items[0].setDisabled = function() { // не даём пользоваться больше этой функцией для этого компонента.
						return false;
					}
			this.List.ViewActions.action_delete.items[1].setDisabled = function() { // не даём пользоваться больше этой функцией для этого компонента.
						return false;
					}		
		}
		if(this.id == 'Extemporal_SearchWindow'){
			this.List.ViewToolbar.items.items[2].setVisible(false);
		} else {
			this.List.ViewToolbar.items.items[2].setVisible(true);
		}
		if(this.List.id == 'CLSDRUGFORMS_SearchWindowList'){
			var fp = this.FieldPanel.getForm();
			fp.findField('CLSDRUGFORMS_NameLatin').hideContainer();
			fp.findField('CLSDRUGFORMS_NameLatinSocr').hideContainer();
		}
		if(this.List.id == 'Actmatters_SearchWindowList'){
			var fp = this.FieldPanel.getForm();
			fp.findField('LATNAME').hideContainer();
			fp.findField('Actmatters_LatNameGen').hideContainer();
		}log(this.List);
		this.doReset();
		this.doLayout();
        this.List.getAction('action_refresh').setDisabled(true);
	},
	
	initComponent: function() 
	{
		var form = this;
		// Первоначальные проверки и инициализации
		if ((!this.dataUrl) && (this.store))
		{
			this.dataUrl = this.store.url;
		}
		if ((!this.store) && (!this.stringfields))
		{
			Ext.Msg.alert(lang['oshibka_initsializatsii_formyi']+this.id, lang['ne_ukazanyi_ni_store-obyekt_ni_stringfields']);
		}
		else 
		{
			if (this.store)
			{
				var count_rec = this.store.fields.length;
				var isstore = true;
			}
			else 
			{
				var count_rec = this.stringfields.length;
				var isstore = false;
			}
		}
		if (!this.dataUrl)
		{
			Ext.Msg.alert(lang['oshibka_initsializatsii_formyi']+this.id, lang['ne_ukazan_istochnik_dannyih_dataurl']);
		}
		// Проверка на форму редактирования и акшены пришедшие извне
		var disable = true;
		if (this.editformclassname)
		{
			var disable = false;
		}
		var actions = new Array();
		if (this.actions)
		{
			actions = this.actions;
		}
		else 
			actions = 
			[
				{name:'action_add', disabled: disable},
				{name:'action_edit', disabled: disable},
				{name:'action_view', disabled: disable},
				{name:'action_delete', disabled: disable}
			];
		// Разбор store и stringfields и получение данных оттуда 
		var data = new Array();
		this.field = new Array();
		var name = '';
		var type = '';
		var fcount = -1;
		var ff = false;
		var searchpanel = [];
		for (i=0; i < count_rec; i++)
		{
			if (isstore)
			{
				name = this.store.fields.items[i].name;
				type = (this.store.fields.items[i].type)?this.store.fields.items[i].type:'';
			}
			else 
			{
				name = this.stringfields[i].name;
				type = (this.stringfields[i].type)?this.stringfields[i].type:'';
			}
			
			data[i] = new Object();
			data[i]['name'] = name;
			
			// весь выбор настроек в основном актуален для комбобоксов с указанием store
			
			if (name.toLowerCase().match(/_id/)) // это ID 
			{
				data[i]['type'] = (type!='')?type:'int';
				if (name.toLowerCase() == this.object.toLowerCase()+'_id') // Свой ID 
				{
					data[i]['key'] = true;
				}
				else 
				{
					data[i]['hidden'] = true;
				}
			}
			else 
			{
				if (name.toLowerCase() == this.object.toLowerCase()+'_code') // Code 
				{
					data[i]['type'] = (type!='')?type:'int';
					data[i]['header'] = (!isstore && this.stringfields[i]['header'])?this.stringfields[i]['header']:lang['kod'];
					data[i]['width'] = (!isstore && this.stringfields[i]['width'])?this.stringfields[i]['width']:80;
					ff = true;
				}
				else 
				{
					if ((name.toLowerCase() == this.object.toLowerCase()+'_name') || (!isstore && this.stringfields[i]['autoexpand']) || name.toLowerCase().match(/name/)) // Наименование
					{
						data[i]['id'] = 'autoexpand';
						data[i]['header'] = ((!isstore && this.stringfields[i]['header']) || (this.id == 'CLSDRUGFORMS_SearchWindow'))?this.stringfields[i]['header']:lang['naimenovanie'];
						data[i]['headerName'] = (!isstore && this.stringfields[i]['headerName'])?this.stringfields[i]['headerName']:null;
						ff = true;
					}
					else 
					{
						if (name.toLowerCase() == this.object.toLowerCase()+'_nick') // Nick 
						{
							data[i]['width'] = (!isstore && this.stringfields[i]['width'])?this.stringfields[i]['width']:200;
							data[i]['header'] = (!isstore && this.stringfields[i]['header'])?this.stringfields[i]['header']:lang['sokr_naimenovanie'];
							data[i]['headerName'] = (!isstore && this.stringfields[i]['headerName'])?this.stringfields[i]['headerName']:null;
							ff = true;
						}
						else 
						{
							if (name.toLowerCase() == this.object.toLowerCase()+'_descr') // Описание 
							{
								data[i]['width'] = (!isstore && this.stringfields[i]['width'])?this.stringfields[i]['width']:200;
								data[i]['header'] = (!isstore && this.stringfields[i]['header'])?this.stringfields[i]['header']:lang['opisanie'];
								data[i]['headerName'] = (!isstore && this.stringfields[i]['headerName'])?this.stringfields[i]['headerName']:null;
								ff = true;
							}
							else 
							{
								// Если настройки переданы через stringfields и у поля указано значение isfilter = true и заголовок есть
								if ((!isstore) && (this.stringfields[i].isfilter) && (this.stringfields[i].header))
								{
									// используем поле для фильтрации
									ff = true;
								}
								if ((!isstore) && (!this.stringfields[i].hidden) && (this.stringfields[i].header))
								{
									data[i]['hidden'] = false;
									if (this.stringfields[i].width)
									{
										data[i]['width'] = this.stringfields[i]['width'];
									}
									data[i]['header'] = this.stringfields[i]['header'];
									data[i]['headerName'] = (this.stringfields[i]['headerName'])?this.stringfields[i]['headerName']:null;
								}
								else 
								{
									data[i]['hidden'] = true;
								}
							}
						}
					}
				}
			}
			if (ff)
			{
				// Формирование fields для панели фильтров
				fcount++;
				// Само поле 
				this.field[fcount] = {};
				this.field[fcount]['fieldLabel'] =(data[i]['headerName']!=null)?data[i]['headerName']:data[i]['header'];
				this.field[fcount]['name'] = data[i]['name'];
				this.field[fcount]['id'] = this.prefix+data[i]['name'];
				this.field[fcount]['enableKeyEvents'] = true;
				this.field[fcount]['anchor'] = '100%';
				this.field[fcount]['xtype'] = 'textfield';
				
				searchpanel[fcount] = (this.columns)?{columnWidth: (!isstore && this.stringfields[i]['columnWidth'])?this.stringfields[i]['columnWidth']:'.25', border: false,layout: 'form', style: 'margin-right:10px;',items: [this.field[fcount]]}:this.field[fcount];
				ff = false;
			}
		}
		if (this.field.length==0)
		{
			Ext.Msg.alert(lang['oshibka_initsializatsii_formyi']+this.id, lang['dlya_paneli_filtrov_ne_ukazano_ni_odno_pole']);
			return false;
		}
		
		if (this.columns)
		{
			this.fields = [{border: false,layout: 'column', items:searchpanel}];
		}
		else 
		{
			this.fields = searchpanel;
		}
		
		delete searchpanel;
		// Grid
		this.List = new sw.Promed.ViewFrame(
		{
			id: (this.id)?this.id+'List':this.object+'List',
			region: 'center',
			object: this.object,
			editformclassname: this.editformclassname,
			disableActions:this.disableActions,
			dataUrl: this.dataUrl,
			autoLoadData: false,
			stringfields: data,
			actions: actions,
			/* +lang['aktivatsiya']+ +lang['postranichnogo']+ +lang['vyivoda']+ */
			paging: this.paging,
			root: (this.paging)?'data':null,
			totalProperty: (this.paging)?'totalCount':null,
			focusOn: {name:form.prefix+'ButtonSearch',type:'button'},
			focusPrev: {name:form.prefix+form.field[form.field.length-1]['name'],type:'field'},
			onLoadData: function (result)
			{
				var form = this.ownerCt;
				Ext.getCmp(form.prefix+'ButtonOk').setDisabled(!result);
                this.getAction('action_refresh').setDisabled(false);
			},
			onDblClick: function()
			{
				this.ownerCt.onOkButtonClick();
			},
			onEnter: function()
			{
				this.ownerCt.onOkButtonClick();
			}
		});
		delete(actions);
		this.FieldPanel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: this.id+'ListForm',
			items: this.fields,
			keys: 
			[{
				fn: function(e) 
				{
					//this.
					Ext.getCmp(form.id).doSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			labelAlign: 'top',
			region: 'north'
		});
		
		Ext.apply(this, 
		{
			buttons: 
			[{
				id: form.prefix+'ButtonSearch',
				handler: function() 
				{
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, 
			{
				id: form.prefix+'ButtonClear',
				handler: function() 
				{
					this.ownerCt.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, 
			{
				id: form.prefix+'ButtonOk',
				handler: function() 
				{
					this.ownerCt.onOkButtonClick();
				},
				iconCls: 'ok16',
				text: lang['vyibrat']
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				id: form.prefix+'ButtonCancel',
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: form.prefix+form.field[0]['name'],
				text: BTN_FRMCANCEL
			}],
			items: 
			[
				this.FieldPanel,
				this.List
			]
		});
		sw.Promed.swListSearchWindow.superclass.initComponent.apply(this, arguments);
		this.List.addListenersFocusOnFields();
	}
});