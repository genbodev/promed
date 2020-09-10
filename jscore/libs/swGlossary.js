/**
 * sw.Promed.glossaryMenu. Класс меню глоссария
 *
 * @author		Permyakov Alexander
 * @class		sw.Promed.glossaryMenu
 * @extends		Ext.menu.Menu
 */
sw.Promed.glossaryMenu = function(config)
{
	Ext.apply(this, config);
	sw.Promed.glossaryMenu.superclass.constructor.call(this);
};
Ext.extend(sw.Promed.glossaryMenu, Ext.menu.Menu, {
	processShow: false,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	ignoreParentClicks: true,
	listeners: {
		'hide': function(menu) {
			menu.processShow = false;
			menu.onHide();
		},
		'mouseover': function(menu,e,item) {
			if(!item) {
				return false;
			}
			item.getEl().on('contextmenu',function (ev){
				// Cancel the browser context menu.
				ev.preventDefault();
			});
		},
		'mouseout': function(menu,e,item) {
			if(!item) {
				return false;
			}
			item.getEl().un('contextmenu',function (ev){
				// Cancel the browser context menu.
				ev.preventDefault();
			});
		},
		'itemclick': function(item,e) {
			//log('itemclick');
			//log(e);
			if (e.type == 'click')
			{
				//надо отменить т.к. это мог быть клик по гипер ссылке
				//e.stopEvent();
				return false;
			}
			var s = sw.Promed.Glossary.store,
				r = s.getAt(s.find('Glossary_id',item.Glossary_id)),
				data = r.data;
			this.onSelect(data);
		}
	},
	minWidth: 180
});

/** 
 * swGlossary Класс для работы с глоссарием на клиенте
 *
 * функции:
 * 1) Синхронизация локального справочника с БД на сервере - необходимо при каждом изменении!
 * удаление записи из локального справочника после удаления с сервера
 * добавление записи в локальный справочник после добавления в БД на сервере
 * обновление записи в локальном справочнике после обновления в БД на сервере
 * 2) Отображение меню
 * 3) Изменение настроек
 *
 * @author		Permyakov Alexander
 * @class		sw.Promed.Glossary
 */
sw.Promed.Glossary = {
	isEnableGlossary: function(){
		return Ext.globalOptions.glossary.enable_glossary;
	},
	isEnableBaseGlossary: function(){
		return Ext.globalOptions.glossary.enable_base_glossary;
	},
	isEnablePersGlossary: function(){
		return Ext.globalOptions.glossary.enable_pers_glossary;
	},
	isEnableContextSearch: function(){
		return (Ext.globalOptions.glossary.use_glossary_tag == 2);
	},
	setEnableContextSearch: function(enabled){
		var s = {
			node: 'glossary',
			use_glossary_tag: (enabled)?2:1
		};
		if (this.isEnableBaseGlossary())
		{
			s.enable_base_glossary = 'on';
		}
		if (this.isEnablePersGlossary())
		{
			s.enable_pers_glossary = 'on';
		}
		this._saveSettings(s);
	},
	setEnableGlossary: function(enabled){
		var s = {
			node: 'glossary',
			use_glossary_tag: (this.isEnableContextSearch())?2:1
		};
		if(enabled)
		{
			s.enable_base_glossary = 'on';
			if(!isAdmin)
			{
				s.enable_pers_glossary = 'on';
			}
		}
		this._saveSettings(s);
	},
	_saveSettings: function(settings){
		Ext.Ajax.request({
			failure: function(response, options) {
				sw.swMsg.alert(langs('Ошибка'), langs('При сохранении настройки возникли ошибки'));
			},
			params: settings,
			success: function(response, options) {
				// To-Do достаточно обновить одну опцию в Ext.globalOptions.glossary
				Ext.loadOptions();
			},
			url: '/?c=Options&m=saveOptionsForm'
		});
	},
	/*
	store: new Ext.db.AdapterStore({
		autoLoad: false,
		dbFile: 'Promed.db',
		fields: [
			{name: 'Glossary_id', type:'int'},
			{name: 'GlossaryTagType_id', type:'int'},
			{name: 'GlossarySynonym_id', type:'int'},
			{name: 'GlossaryTagType_SysNick', type: 'string'},
			{name: 'Glossary_Descr', type: 'string'},
			{name: 'pmUser_did', type:'int'},
			{name: 'Glossary_Word', type:'string'}
		],
		key: 'Glossary_id',
		sortInfo: {field: 'Glossary_Word'},
		tableName: 'Glossary',
		// TODO: IndexedDB: В рамках IndexedDB удаление, вставку и выборки здесь надо сделать по другому
		deleteFromLocal: function(id){
			return sw_exec_query_local_db('Promed.db', "DELETE FROM Glossary where Glossary_id = " + id);
		},
		insertLocal: function(data){
			var sql = "INSERT INTO Glossary (Glossary_id,GlossarySynonym_id,GlossaryTagType_id,GlossaryTagType_SysNick,Glossary_Descr,Glossary_Word,pmUser_did) VALUES ('"+ data.Glossary_id +"','"+ data.GlossarySynonym_id +"','" +data.GlossaryTagType_id +"','" +data.GlossaryTagType_SysNick+ "','" +data.Glossary_Descr+ "','"+ data.Glossary_Word.toLowerCase() +"','" +data.pmUser_did +"');";
			return sw_exec_query_local_db('Promed.db', sql);
		},
		updateLocal: function(data){
			return sw_exec_query_local_db('Promed.db', "UPDATE Glossary SET GlossarySynonym_id = '"+ data.GlossarySynonym_id +"', GlossaryTagType_id = '" +data.GlossaryTagType_id +"', GlossaryTagType_SysNick = '" +data.GlossaryTagType_SysNick+ "', Glossary_Descr = '" +data.Glossary_Descr+ "', Glossary_Word = '"+ data.Glossary_Word.toLowerCase() +"' where Glossary_id = "+ data.Glossary_id);
		}
	}),
	*/
	store: new Ext.data.JsonStore({
		autoLoad: false,
		url: '/?c=Glossary&m=loadRecordStore',
		key: 'Glossary_id',
		sortInfo: {field: 'Glossary_Word'},
		tableName: 'Glossary',
		fields:
		[
			{name: 'Glossary_id', type:'int'},
			{name: 'GlossaryTagType_id', type:'int'},
			{name: 'GlossarySynonym_id', type:'int'},
			{name: 'GlossaryTagType_SysNick', type: 'string'},
			{name: 'Glossary_Descr', type: 'string'},
			{name: 'pmUser_did', type:'int'},
			{name: 'Glossary_Word', type:'string'}
		]
	}),
	getGlossaryTagTypeBySysNick: function(s, callback){
		if (typeof sw_select_from_local_db == 'function') {
			sw_select_from_local_db('Promed.db', "SELECT * FROM GlossaryTagType where GlossaryTagType_SysNick = '" + s +"'",callback);
		} else {
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert(langs('Ошибка'), langs('При получении записи GlossaryTagType возникли ошибки'));
				},
				params: {GlossaryTagType_SysNick: s},
				success: function(response, options) {
					if ( response.responseText )
					{
						var result  = Ext.util.JSON.decode(response.responseText);
						if ( Ext.isArray(result) )
						{
							if(typeof callback == 'function')
							{
								callback(result);
							}
							return;
						}
						sw.swMsg.alert(langs('Ошибка'), langs('При получении GlossaryTagType возникли ошибки!'));
						return;
					}
					sw.swMsg.alert(langs('Ошибка'), langs('При получении записи GlossaryTagType не был получен ответ сервера!'));
				},
				url: '/?c=Glossary&m=getGlossaryTagTypeBySysNick'
			});
		}
	},
	addRecord: function(option){
		if(!option || !option.Glossary_Word)
		{
			return false;
		}
		var params = {Glossary_Word: option.Glossary_Word};
		var request = function(glossary_tagtype){
			if(Ext.isArray(glossary_tagtype) && glossary_tagtype.length > 0) {
				glossary_tagtype = glossary_tagtype[0];
				params.GlossaryTagType_id = glossary_tagtype.GlossaryTagType_id;
			} else {
				glossary_tagtype = false;
			}
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert(langs('Ошибка'), langs('При добавлении записи возникли ошибки'));
				},
				params: params,
				success: function(response, options) {
					if ( response.responseText )
					{
						var result  = Ext.util.JSON.decode(response.responseText);
						if ( result.success )
						{
							var data = {};
							data.Glossary_id = result.Glossary_id;
							data.GlossaryTagType_Name = (glossary_tagtype && glossary_tagtype.GlossaryTagType_Name) || '';
							data.GlossaryTagType_id = (glossary_tagtype && glossary_tagtype.GlossaryTagType_id) || '';
							data.GlossaryTagType_SysNick = (glossary_tagtype && glossary_tagtype.GlossaryTagType_SysNick) || '';
							data.Glossary_Word = option.Glossary_Word;
							data.GlossarySynonym_id = result.GlossarySynonym_id;
							data.pmUser_did = (isAdmin)?'':getGlobalOptions().pmuser_id;
							data.Glossary_Descr = '';
							//sw.Promed.Glossary.store.insertLocal(data);
							if(typeof option.callback == 'function')
							{
								option.callback(data);
							}
						}
						else
						{
							sw.swMsg.alert(langs('Ошибка'), (result.Error_Msg)?result.Error_Msg:langs('При добавлении записи возникли ошибки'));
						}
						return;
					}
					sw.swMsg.alert(langs('Ошибка'), langs('При добавлении записи не был получен ответ сервера!'));
				},
				url: '/?c=Glossary&m=saveRecord'
			});
		};
		
		if(option.GlossaryTagType_SysNick){
			this.getGlossaryTagTypeBySysNick(option.GlossaryTagType_SysNick,request);
		} else {
			request(false);
		}
	},
	viewRecord: function(id){
		this.openEditWindow({action: 'view', Glossary_id: id});
	},
	editRecord: function(id){
		this.openEditWindow({action: 'edit', Glossary_id: id});
	},
	deleteRecord: function(id){
		//var glossary = this.store;
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Удаление записи..." });
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при удалении записи'));
						},
						params: {id: id,object: 'Glossary'},
						success: function(response, options) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							//log(response_obj);
							if ( response_obj.success == false )
							{
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Message ? response_obj.Error_Message : langs('Ошибка при удалении записи'));
							}
							else
							{
								//glossary.deleteFromLocal(id);
								sw.swMsg.alert(langs('Сообщение'), langs('Запись удалена!'));
							}
						},
						url: '/?c=Utils&m=ObjectRecordDelete'
					});
				}
			}.createDelegate(this.menu),
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить запись?'),
			title: langs('Вопрос')
		});
	},
	selRecord: function(id){
		var r = sw.Promed.Glossary.store.getById(id),
			data = r.data;
		this.menu.onSelect(data);
		this.menu.hide();
	},
	/*
	getSynonymList: function(id, synonym_id){
		var res = false, i, l = [], r;
		if(synonym_id && synonym_id > 0)
		{
			var filter = '';
			if (this.isEnableBaseGlossary() && this.isEnablePersGlossary())
			{
				filter = " and (pmUser_did = '' or pmUser_did = '"+ getGlobalOptions().pmuser_id +"')";
			}
			else if (this.isEnableBaseGlossary())
			{
				filter = " and pmUser_did = ''";
			}
			else if (this.isEnablePersGlossary())
			{
				filter = " and pmUser_did = '"+ getGlobalOptions().pmuser_id +"'";
			}
			res = sw_select_from_local_db('Promed.db', "SELECT * FROM Glossary where GlossarySynonym_id = " + synonym_id +" and Glossary_id != " + id + filter);
			if(res)
			{
				for(i=0;i<res.length;i++)
				{
					r = new Ext.data.Record(res[i]);
					r.id = res[i].Glossary_id;
					l.push(r);
				}
			}
		}
		if(l.length > 0)
		{
			this.store.add(l);
			var menu = new sw.Promed.glossaryMenu({
				onSelect: this.menu.onSelect,
				onHide: this.menu.onHide
			});
			for(i=0;i<l.length;i++)
			{
				menuItem = this.getMenuItem({r: l[i]});
				menu.addItem(menuItem);
			}
			return menu;
		}
		return false;
	},
	*/
	getMenuItem: function(option){
		var record = option.r,
			id = record.get('Glossary_id'),
			word = record.get('Glossary_Word'),
			descr = record.get('Glossary_Descr'),
			pmUser_did = record.get('pmUser_did'),
			synonym_id = record.get('GlossarySynonym_id'),
			caption, config,
			synonym_list = false;
		if(word.length > 50)
		{
			word = word.substring(0,47) +'...';
		}
		caption = '<span onclick="sw.Promed.Glossary.selRecord('+record.id+');" style="font-weight: bolder;"><span>'+ word +'</span>';
		if (descr && descr.length > 0)
		{
			caption = caption + '<span><img src="/img/icons/view16.png" title="'+descr+'" /></span>';
		}
		caption = caption + '</span><span id="GlossaryItem'+id+'Tools"  style="visibility: hidden;">';
		if (isAdmin || pmUser_did == getGlobalOptions().pmuser_id)
		{
			caption = caption + ' <span onclick="sw.Promed.Glossary.editRecord('+id+')" title="Редактировать"><img src="/img/icons/edit16.png" /></span> <span onclick="sw.Promed.Glossary.deleteRecord('+id+')" title="Удалить"><img src="/img/icons/delete16.png" /></span>';
		}
		caption = caption + ' </span>';
		config = {
			text: caption,
			Glossary_id: id,
			style: 'padding-left: 5px; padding-right: 5px; padding-top: 0px; padding-bottom: 0px; margin: 0px; font-size: 11px;',
			id: 'GlossaryItem'+id,
			listeners: {
				'activate': function(item){ Ext.get('GlossaryItem'+item.Glossary_id+'Tools').show();},
				'deactivate': function(item){ Ext.get('GlossaryItem'+item.Glossary_id+'Tools').hide();}
			}
		};
		if(option.sl && option.sl.length > 0)
		{
			var res = option.sl,r,menuItem;
			var menu = new sw.Promed.glossaryMenu({
				onSelect: this.menu.onSelect,
				onHide: this.menu.onHide
			});
			for(i=0;i<res.length;i++) {
				r = new Ext.data.Record(res[i]);
				this.store.add([r]);
				menuItem = this.getMenuItem({r: r});
				menu.addItem(menuItem);
			}
			config.menu = menu;
		}
		/*
		synonym_list = this.getSynonymList(id, synonym_id);
		}
		if(synonym_list)
		{
			config.menu = synonym_list;
		}
		*/
		return new Ext.menu.Item(config);
	},
	openEditWindow: function(p){
		getWnd('swGlossaryEditWindow').show(p);
	},
	menu: new sw.Promed.glossaryMenu({
		ignoreParentClicks: false
	}),
	relay: function(e){
		var k = e.getKey();
		if(this.isKeyCodeAlphabet(k))
		{
			this.menu.hide();
		}
		if(8 == k)// backspace
		{
			e.stopEvent();
			this.menu.hide();
		}
		return true;
	},
	menuShowAt: function(option){
		this.menu.destroy();
		this.menu = new sw.Promed.glossaryMenu({
			ignoreParentClicks: false
		});
		if(this.menu.getEl())
		{
			this.menu.getEl().un('keydown', this.relay, this);
			this.menu.getEl().on('keydown', this.relay, this);
		}
		this.menu.onSelect = option.onSelect || Ext.emptyFn;
		this.menu.onHide = option.onHide || Ext.emptyFn;
		var first_id = null;
		/*
		var filter = '';
		if (this.isEnableBaseGlossary() && this.isEnablePersGlossary())
		{
			filter = " and (pmUser_did = '' or pmUser_did = '"+ getGlobalOptions().pmuser_id +"')";
		}
		else if (this.isEnableBaseGlossary())
		{
			filter = " and pmUser_did = ''";
		}
		else if (this.isEnablePersGlossary())
		{
			filter = " and pmUser_did = '"+ getGlobalOptions().pmuser_id +"'";
		}
		if(option.GlossaryTagType_SysNick && this.isEnableContextSearch())
		{
			filter = " and (GlossaryTagType_SysNick = '' or GlossaryTagType_SysNick = '"+ option.GlossaryTagType_SysNick +"')";
		}*/
		//log([option,this.store.getCount()]);
		if(this.synLoadId) {
			Ext.Ajax.abort(this.synLoadId);
		}
		if(this.storeLoadId) {
			Ext.Ajax.abort(this.storeLoadId);
		}
		this.storeLoadId = Ext.Ajax.request({
			url: '/?c=Glossary&m=loadRecordStore',
			failure: function(response, options) {
				this.storeLoadId = null;
			}.createDelegate(this),
			params: {
				text: option.text.toLowerCase(),
				GlossaryTagType_SysNick: option.GlossaryTagType_SysNick || '',
				isEnableContextSearch: (this.isEnableContextSearch())?1:0,
				isEnableBaseGlossary: (this.isEnableBaseGlossary())?1:0,
				isEnablePersGlossary: (this.isEnablePersGlossary())?1:0
			},
			success: function(response, options) {
				this.storeLoadId = null;
				var l = [],j;
				if ( response.responseText )
				{
					var res  = Ext.util.JSON.decode(response.responseText);
					//this.store.loadData(res);
					for(j=0;j<res.length;j++) {
						l.push(new Ext.data.Record(res[j]));
					}
					this.store.removeAll();
					this.store.add(l);
				}
		/*this.processLoad = true;
		this.store.load({
			//params: {where: "where Glossary_Word like '"+ option.text.toLowerCase() +"%'"+ filter +' ORDER BY Glossary_Word LIMIT 20 OFFSET 0'},
			params: {
				text: option.text.toLowerCase(),
				GlossaryTagType_SysNick: option.GlossaryTagType_SysNick || '',
				isEnableContextSearch: (this.isEnableContextSearch())?1:0,
				isEnableBaseGlossary: (this.isEnableBaseGlossary())?1:0,
				isEnablePersGlossary: (this.isEnablePersGlossary())?1:0
			},
			callback: function(l,o,s){
				this.processLoad = false;
				//log([l,o,s]);*/
				var i,menuItem,sl = [],l_max=0,ln;
				//log(l);
				for(i=0;i<l.length;i++)
				{
					if(!first_id)
					{
						first_id = 'GlossaryItem'+ l[i].get('Glossary_id');
					}
					// формируем меню после получения синонимов
					//menuItem = this.getMenuItem(l[i], true);
					//this.menu.addItem(menuItem);
					ln = l[i].get('Glossary_Word').length + (l[i].get('Glossary_Descr')?3:0);
					if(l_max < ln)
						l_max = ln;
					sl.push(l[i].get('Glossary_id') + '-'+ l[i].get('GlossarySynonym_id'));
				}
				//log(this.store);
				//показываем, если есть варианты
				if(l.length > 0)
				{
					this.synLoadId = Ext.Ajax.request({
						failure: function(response, options) {
							this.synLoadId = null;
							//sw.swMsg.alert('Ошибка', 'При получении списка синонимов возникли ошибки');
						}.createDelegate(this),
						params: {
							Synonym_list: sl.toString(),
							isEnableBaseGlossary: (this.isEnableBaseGlossary())?1:0,
							isEnablePersGlossary: (this.isEnablePersGlossary())?1:0
						},
						success: function(response, options) {
							this.synLoadId = null;
							if ( response.responseText )
							{
								var res  = Ext.util.JSON.decode(response.responseText);
								for(i=0;i<l.length;i++)
								{
									menuItem = this.getMenuItem({r: l[i], sl: res[l[i].get('Glossary_id')]});
									this.menu.addItem(menuItem);
								}

								/*
								log(l.length);
								log(this.menu.getEl().getHeight());
								log(l_max);
								log(this.menu.getEl().getWidth()); //minWidth
								*/
								return;
							}
							//sw.swMsg.alert('Ошибка', 'При получении списка синонимов  не был получен ответ сервера!');
						}.createDelegate(this),
						url: '/?c=Glossary&m=loadSynonymMenu'
					});
					if(option.element_to_align_to)
					{
						this.menu.show(option.element_to_align_to);
					}
					else if(option.xy_arr)
					{
						// на основании количества символов в самом длинном термине вычисляю примерную ширину меню
						var menu_w = l_max * 6 + 78;
						var o = Ext.get(option.editor.container.getId()).getWidth()-option.xy_arr[0];
						if(o < menu_w)
							option.xy_arr[0] = option.xy_arr[0] - (menu_w - o);
						this.menu.showAt(option.xy_arr);
					}
				}
			}.createDelegate(this)
		});
	}, 
	/** 
	 * Определяет является ли данный код клавишы с алфавитным символом
	 */
	isKeyCodeAlphabet: function(key)
	{
		return (key > 64 && key < 91) || key.inlist([59,186,188,190,192,219,221,222]);
	}, 
	/** 
	 * Определяет является ли данный код клавишы с символом-разделителем
	isKeyCodeSplit: function(key)
	{
		//пробел,(,),:,точка на рус.раскладке или запятая на рус.раскладке
		//188,190 на англ. раскладке точка с запятой, на русской - буквы.
		return key.inlist([32,57,48,54,191]);
	}, 
	 */
	/** Получает примерное положение курсора ввода в текущем окне редактора
	 * для отображения в этом положении меню.
	 *
	 */
	getCursorOffset: function(editor)
	{
		var x = 0,
			y = 0;
		var h = editor.getToolBoxHeight();
		// это ветка для IE в котором есть selection документа
		if (document.selection && document.selection.createRange) {
			var cursorPos = document.selection.createRange();
			if (sw.Promed.Glossary.ie_bounding_client_rect)
			{
				x = sw.Promed.Glossary.ie_bounding_client_rect.left+15; 
				y = editor.container.getDocumentPosition().y + sw.Promed.Glossary.ie_bounding_client_rect.top+h+16;
			} 
			else if (cursorPos.getBoundingClientRect)
			{
				x = cursorPos.getBoundingClientRect().left-5; 
				y = editor.container.getDocumentPosition().y + cursorPos.getBoundingClientRect().top+h+16;
			}
		} else {
			// судя по описаниям - FF поддерживает getBoundingClientRect для range, но начиная с версии 4. 
			// поэтому мы в этом месте создаем спан  и по нему определяем где находится курсор
			var span = editor.document.createElement( 'span', {
				attributes : {
					position : 'absolute',
					wrap : 'hard',
					whiteSpace : 'pre',
					zIndex : '-10'
				}
			});
			//editor.insertElement( span  );
			var s = editor.getSelection(),
				r = s && s.getRanges();
			r[0].insertNode(span);
			r[0].selectNodeContents(span);
			x = span.getDocumentPosition().x-6;
			y = editor.container.getDocumentPosition().y+span.getDocumentPosition().y+h+3;
			// TODO: Здесь надо сделать удаление этого спана, не нашел как - времени нет разбираться пока
			// пустой span редактор сам удаляет,
			//editor.removeElement( span  );
		}
		return [x, y];
	}
};