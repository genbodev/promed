/*
* Плагин отвечает за преобразование кода из исходного в код отображаемый в визуальном редакторе и обратно.
*
* example
* код области "только для печати" отображаемый в виз.редакторе в режиме дизайнера шаблонов
<div _cke_real_class="printonly" style="background-color: #CCC;display: block;width:100%;border: red 1px dotted;" title="только для печати" _cke_real_element_type="div" _cke_real_style="display%3A%20block%3B">тут какой-то HTML, который должен выводится только на печать</div>
* код области "только для печати" отображаемый в виз.редакторе в режиме заполнения шаблонов
<div _cke_real_class="printonly" style="display: none;" title="только для печати" _cke_real_element_type="div" _cke_real_style="display%3A%20block%3B">тут какой-то HTML, который должен выводится только на печать</div>
* исходный код области "только для печати" (так должен выглядеть код после разметки выделенной области):
<div class="printonly" style="display: block;">тут какой-то HTML, который должен выводится только на печать</div>
*/
var mode_styles = {
	// не виден при отображении шаблона 
	printonly: {
		print:'display: block;',
		input:'display: none;',
		designer:'display: block;width:100%;border:rgb(0,128,255) 1px dotted;',
		title:'только для печати'
	},
	// виден при отображении шаблона 
	hiddenuser: {
		print:'display: block;',
		input:'display: none;',
		designer:'display: block;width:100%;border:rgb(0,128,255) 1px dotted;',
		title:'текст скрытый при заполнении шаблона'
	},
	comment: {
		print:'display: none;',
		input:'background-color:rgb(210,255,210);display:inline;',
		designer:'background-color:rgb(210,255,210);display:inline;',
		title:'комментарий (на печать не выводится)'
	}
};
CKEDITOR.config.swtagStyles_data = {
		input: 'background-color:rgb(209,209,209); border: 1px dotted #000; padding-left:10px; padding-right:10px; ',
		designer: 'background-color:rgb(209,209,209); border: 1px dotted #000; padding-left:10px; padding-right:10px;',
		title:'поле ввода'
};
CKEDITOR.config.swtagStyles_metadata = {
		print:'display: none;',
		input:'display: none;',
		designer:'display:inline;border:rgb(152,0,0) 1px solid;',
		title:'служебные данные'
};
// элемент, атрибуты комментария шаблона в визуальном представлении
CKEDITOR.config.coreStyles_swcomment = { element : 'span', attributes : {style: mode_styles.comment.designer,title: mode_styles.comment.title,_cke_real_style:'display: none;', _cke_real_class:'swcomment', _cke_real_element_type:'span'} };

CKEDITOR.editor.prototype.SwTagCreateToVisual = function(realElement,setStyle,realElementType,title,remove_attributes)
{
	var className,realStyle, realName,realValue;
	className = realElement.getAttribute('class');
	realStyle = realElement.getAttribute('style');
	realName = realElement.attributes && realElement.attributes.name;
	realValue = realElement.attributes && realElement.attributes.value;

	var visual_attributes = {
		_cke_real_class : className,
		style : setStyle,
		title: title
	};
	if ( realStyle )
		visual_attributes._cke_real_style = realStyle;
	if ( realElementType )
		visual_attributes._cke_real_element_type = realElementType;
	if ( realName )
		visual_attributes._cke_real_name = realName;
	if ( realValue )
		visual_attributes._cke_real_value = realValue;

	realElement.removeAttributes(remove_attributes);
	realElement.setAttributes(visual_attributes);
	return realElement;
};

var ck_tabindex = 59000;
CKEDITOR.editor.prototype.SwTagToVisual = function(realElement,realElementType,visual_attributes,toElementType)
{
	var className,realStyle, realName,realValue;
	className = realElement.attributes && realElement.attributes['class'];
	realStyle = realElement.attributes && realElement.attributes.style;
	realName = realElement.attributes && realElement.attributes.name;
	realValue = realElement.attributes && realElement.attributes.value;
	var id = realElement.attributes && realElement.attributes.id;
	if (!className)
		return realElement;

	if (!visual_attributes)
		var visual_attributes = {_cke_real_class : className};
	else
		visual_attributes._cke_real_class = className;
	if ( realStyle )
		visual_attributes._cke_real_style = realStyle;
	if ( realName )
		visual_attributes._cke_real_name = realName;
	if ( realValue )
		visual_attributes._cke_real_value = realValue;
	if ( realElementType )
		visual_attributes._cke_real_element_type = realElementType;
	if ( id )
		visual_attributes.id = id;
	if ('data' == className)
	{
		ck_tabindex = ck_tabindex + 1;
		visual_attributes.tabindex = ck_tabindex;
	}

	realElement.attributes = visual_attributes;
	if (toElementType)
	{
		realElement.name = toElementType;
	}
	return realElement;

};

CKEDITOR.editor.prototype.SwTagToHtml = function(visualElement, visualElementType)
{
	var className = visualElement.attributes && visualElement.attributes._cke_real_class;
	var realStyle = visualElement.attributes && visualElement.attributes._cke_real_style;
	var realName = visualElement.attributes && visualElement.attributes._cke_real_name;
	var realValue = visualElement.attributes && visualElement.attributes._cke_real_value;
	var realElementType = visualElement.attributes && visualElement.attributes._cke_real_element_type;
	var id = visualElement.attributes && visualElement.attributes.id;
	var original_attributes = {};
	original_attributes['class'] = className;
	if ( id )
		original_attributes.id = id;
	if ( realStyle )
		original_attributes.style = realStyle;
	if ( realName )
		original_attributes.name = realName;
	if ( realValue )
		original_attributes.value = realValue;

	visualElement.attributes = original_attributes;
	if (realElementType != visualElementType)
	{
		visualElement.name = realElementType;
	}
	return visualElement;
};

CKEDITOR.dom.node.prototype.is_SwDataTag = null;
CKEDITOR.dom.node.prototype.is_swDataTagContains = null;
CKEDITOR.dom.node.prototype.isSwDataTag = function()
{
	if(typeof this.is_SwDataTag == 'boolean')
	{
		return this.is_SwDataTag;
	}
	if(this.type == 1 && this.getName() == 'div' && this.getAttribute('_cke_real_class') == 'data')
	{
		this.is_SwDataTag = true;
	} else {
        this.is_SwDataTag = false;
    }
	return this.is_SwDataTag;
};

/*
* Ищет ноду области ввода снизу вверх, начиная с текущей ноды
*/
CKEDITOR.dom.node.prototype.getParentSwDataTag = function()
{
	if(this.isSwDataTag())
	{
		return this;
	}
	var parents = this.getParents(),dataElement;
	for ( var i = (parents.length-1); i > 0; i-- )
	{
		if (parents[i].type != 1)
		{
			continue;
		}
		if (parents[i].getName() == 'body')
		{
			break;
		}
		if(parents[i].isSwDataTag())
		{
			dataElement = parents[i];
		}
		if(dataElement)
		{
			return dataElement;
		}
	}
	return false;
};

/*
* Ищет сверху вниз, начиная с текущей ноды, и помещает в массив CKEDITOR.editor.SwDataTag_arr ноды области ввода 
*/
CKEDITOR.dom.node.prototype.getChildSwDataTag = function()
{
	return this.searchChildSwDataTag(false);
};

/*
* Ищет сверху вниз, начиная с текущей ноды, и помещает в массив CKEDITOR.editor.SwDataTag_arr ноды области ввода 
*/
CKEDITOR.dom.node.prototype.searchChildSwDataTag = function(editor)
{
	var child_list = this.getChildren(), el, result = false;
	for( var j=0;j<child_list.count();j++)
	{
		el = child_list.getItem(j);
		if (el.type != 1) continue;
		if (el.isSwDataTag())
		{
			if (editor)
			{
				editor.SwDataTag_arr.push(el);
				this.getParent().is_swDataTagContains = true;
				this.is_swDataTagContains = true;
			}
			else
			{
				return el;
			}
		}
		else
		{
			if (editor)
			{
				el.searchChildSwDataTag(editor);
			}
			else
			{
				result = el.searchChildSwDataTag(false);
				if (result) return result;
			}
		}
	}
	if (editor == false)
	{
		return result;
	}
	/*
		if (editor == false)
		{
			log('isSwDataTag');
			log(el);
			log(el.isSwDataTag());
		}
	*/
};

CKEDITOR.dom.node.prototype.unselectableChildNotSwDataTag = function()
{
	var child_list = this.getChildren(), el;
	for( var j=0;j<child_list.count();j++)
	{
		el = child_list.getItem(j);
		//if (el.type != 1) continue;
		if (!el.isSwDataTag())
		{
			el.unselectable();
		}
	}
};

CKEDITOR.editor.prototype.SwDataTag_arr = [];
CKEDITOR.editor.prototype.findNearSwDataById = function(id, dir)
{
	if (this.SwDataTag_arr.length == 0)
		return false;

	for ( var i = 0; i < this.SwDataTag_arr.length; i++ )
	{
		if(this.SwDataTag_arr[i].getId() == id)
		{
			if(dir > 0 && this.SwDataTag_arr[i+1])
			{
				return this.SwDataTag_arr[i+1];
			}
			if(dir < 0 && this.SwDataTag_arr[i-1])
			{
				return this.SwDataTag_arr[i-1];
			}
			return this.SwDataTag_arr[i];
		}
	}
	return false;
};

/*
* Ищет ноду области ввода в соседних элементах снизу вверх, начиная с прямого родителя переданного элемента
*/
CKEDITOR.editor.prototype.findNearSwDataTagForElement = function(element, dir)
{
	//log(this);
	if (this.SwDataTag_arr.length == 0)
		return false;

	var parents = element.getParents(),dataElement;
	for ( var i = (parents.length-1); i > 0; i-- )
	{
		if (parents[i].getName() == 'body')
		{
			if(element.isSwDataTag())
			{
				return this.findNearSwDataById(element.getId(),dir);
			}
			else
			{
				return this.SwDataTag_arr[0];
			}
		}
		dataElement = parents[i].getNext(function(node){
			if(dir >= 0 && node && node.isSwDataTag())
			{
				return true;
			}
			return false;
		});
		if(!dataElement)
		{
			dataElement = parents[i].getPrevious(function(node){
				if(dir <= 0 && node && node.isSwDataTag())
				{
					return true;
				}
				return false;
			});
		}
		if(dataElement)
		{
			return dataElement;
		}
	}
	return false;
};

/**
 * Создает список CKEDITOR.dom.node областей ввода данных, если они есть в загруженном шаблоне
 */
CKEDITOR.editor.prototype.createListSwDataTag = function(isDataReady)
{
	if(isDataReady || !this.rootNode) {
		var s = this.getSelection(),
			el = s && s.getStartElement(),
			path = el && new CKEDITOR.dom.elementPath(el);
		this.rootNode = path && path.blockLimit;
	}
	this.SwDataTag_arr = [];
	// Узнаем, содержит ли шаблон теги data
	if (this.rootNode)
	{
		if(this.rootNode.isSwDataTag())
		{
			this.SwDataTag_arr.push(this.rootNode);
			this.rootNode.getParent().swDataTagContains = true;
		}
		this.rootNode.searchChildSwDataTag(this);
	}
};

/**
 * Возвращает объект выделенного элемента тега области ввода
 */
CKEDITOR.editor.prototype.getSelSwDataTag = function()
{

    var result = {},
        path = new CKEDITOR.dom.elementPath( this.getSelection().getStartElement() ),
        lastElement = path.lastElement,
        data = lastElement && lastElement.getAscendant( 'div', true );
    if (data && data.getAttribute('_cke_real_class') == 'data') {
        result.inputEl = data;
        for(var i=0; i < path.elements.length; i++) {
            if(path.elements[i].getId() == 'block_'+ data.getId()) {
                result.blockEl = path.elements[i];
                break;
            }
        }
    }
    return result;
};

/**
 * Создает список имен областей ввода в шаблоне
 */
CKEDITOR.editor.prototype.createListXmlDataSectionSysNick = function()
{
    var html = this.getData();
    var names = [
        'autoname[0-9]+',
        'complaint',
        'anamnesvitae',
        'anamnesmorbi',
        'objectivestatus',
        'localstatus',
        'diagnos',
        'recommendations',
        'resolution',
        'anamnestransfus',
        'anamnesfront'
    ];
    var re = new RegExp('('+names.toString().replace(/,/g,'|')+')', 'gi');
    names = html.match(re);
    this.listXmlDataSectionSysNick = [];
    if (names) {
        for (var i = 0; i < names.length; i++ ) {
            if (!names[i].inlist(this.listXmlDataSectionSysNick)) {
                this.listXmlDataSectionSysNick.push(names[i]);
            }
        }
    }
};

/**
 * Список имен областей ввода в шаблоне
 */
CKEDITOR.editor.prototype.listXmlDataSectionSysNick = [];

/**
 * Проверяет уникально ли имя области ввода
 */
CKEDITOR.editor.prototype.checkXmlDataSectionSysNick = function(str)
{
    for (var i = 0; i < this.listXmlDataSectionSysNick.length; i++ ) {
        if (str == this.listXmlDataSectionSysNick[i]) {
            return false;
        }
    }
    return true;
};

/**
 * Генерирует уникальное имя автоматически именнованной области ввода
 */
CKEDITOR.editor.prototype.createNewAutoName = function()
{
    var editor = this,
        getRandomName = function(first) {
            if (first) return 'autoname1';
            return 'autoname' + Math.floor(Math.random()*100);
        },
        createRecursive = function(first) {
            var str = getRandomName(first);
            log(['createRecursive', str]);
            if (editor.checkXmlDataSectionSysNick(str)) {
                return str;
            } else {
                return createRecursive(false);
            }
        };
    log(['createNewAutoName', this.listXmlDataSectionSysNick]);
    return createRecursive(true);
};

CKEDITOR.plugins.add( 'swtags',
{
	init : function( editor )
	{
		editor.addCommand('hiddenuser', CKEDITOR.plugins.hiddenuserCmd);
		editor.ui.addButton('hiddenuser', {
			label : 'Пометить выбранный фрагмент как текст, скрытый при заполнении шаблона'
			,command : 'hiddenuser'
			//,icon: '/img/icons/template-print-area.png'
		});

		editor.addCommand('printonly', CKEDITOR.plugins.printonlyCmd);
		editor.ui.addButton('printonly', {
			label : 'Пометить выбранный фрагмент как «Только для печати»'
			,command : 'printonly'
			,icon: '/img/icons/template-print-area.png'
		});
		editor.addCommand('removeprintonly', {
			exec : function( editor )
			{
				var selection = editor.getSelection(),
					ranges = selection && selection.getRanges(),
					range,
					bookmarks = selection.createBookmarks(),
					walker,
					toRemove = [];
				function findDiv( node )
				{
					var path = new CKEDITOR.dom.elementPath( node ),
						blockLimit = path.blockLimit,
						div = blockLimit.is( 'div' ) && blockLimit;
					if ( div && !div.getAttribute( '_cke_div_added' ) )
					{
						toRemove.push( div );
						div.setAttribute( '_cke_div_added' );
					}
				}
				for ( var i = 0 ; i < ranges.length ; i++ )
				{
					range = ranges[ i ];
					if ( range.collapsed )
						findDiv( selection.getStartElement() );
					else
					{
						walker = new CKEDITOR.dom.walker( range );
						walker.evaluator = findDiv;
						walker.lastForward();
					}
				}
				for ( i = 0 ; i < toRemove.length ; i++ )
					toRemove[ i ].remove( true );
				selection.selectBookmarks( bookmarks );
			}
		});
		
		var style = new CKEDITOR.style( CKEDITOR.config.coreStyles_swcomment );
		editor.attachStyleStateChange( style, function( state ){
			editor.getCommand('swcomment').setState( state );
		});
		var swcommentCmd = new CKEDITOR.styleCommand( style );
		editor.addCommand('swcomment', swcommentCmd );
		editor.ui.addButton( 'swcomment',{
			label : 'Комментарий шаблона',
			command : 'swcomment',
			icon: '/img/icons/template-note.png'
		});
		
		//editor.addCommand('swdata_add', new CKEDITOR.dialogCommand('swdata_add'));
		editor.addCommand('swdata_add', {
			exec : function( e )
			{
				getWnd('swDataValueListWindow').show({
					onSelect: function(data){
                        //log({onSelect: data});
						for (var f=0; data.length>f; f++){
                            //log({event:'beforeInsert', swDataTags: editor.listXmlDataSectionSysNick});
                            if (data[f].XmlDataSection_Code == 0) {
                                // Добавить автоматически именуемую область ввода данных
                                data[f].XmlDataSection_SysNick = editor.createNewAutoName();
                            }
                            if (!editor.checkXmlDataSectionSysNick(data[f].XmlDataSection_SysNick)) {
                                sw.swMsg.alert('Ошибка', 'Нельзя повторно вставить одинаковый тэг!');
                                return false;
                            }
                            if (!data[f].defaultValue) {
                                data[f].defaultValue = '-';
                            }
							e.insertHtml(
                                '<div class="template-block" id="block_' + data[f].XmlDataSection_SysNick + '">'
                                    + '<p class="template-block-caption" id="caption_' + data[f].XmlDataSection_SysNick + '"><span style="font-weight: bold; font-size:10px;">'+ data[f].XmlDataSection_Name+ ': </span></p>'
                                    + '<div class="template-block-data"  id="data_' + data[f].XmlDataSection_SysNick + '">'
                                    + '<div id="' + data[f].XmlDataSection_SysNick + '" style="' + CKEDITOR.config.swtagStyles_data.designer + '" _cke_real_class="data" _cke_real_element_type="data">'
                                    + data[f].defaultValue
                                    + '</div>'
                                    + '</div>'
                                + '</div><br>'
                            );
                            // обновляем список имен областей ввода
                            editor.createListXmlDataSectionSysNick();
                            log({event:'afterInsert swdata', swDataTags: editor.listXmlDataSectionSysNick});
						}
					},
					onHide: function(){
						e.focus();
					}
				});
			}
		});
		editor.addCommand('swdata_edit', new CKEDITOR.dialogCommand('swdata_edit'));
		editor.addCommand('swdata_remove', {
			exec : function( editor )
			{
				var data_els = editor.getSelSwDataTag();
				if(data_els.blockEl) {
                    data_els.blockEl.remove(false);
                    // обновляем список имен областей ввода
                    editor.createListXmlDataSectionSysNick();
                }
			}
		});
		editor.ui.addButton('swdata', {
			label : 'Создать область для ввода данных'
			,command : 'swdata_add'
			,icon: '/img/icons/template-input-area.png'
		});
		//CKEDITOR.dialog.add('swdata_add',this.path+'dialogs/data.js');
		CKEDITOR.dialog.add('swdata_edit',this.path+'dialogs/data.js');

        /*
        editor.addCommand('swmetadata_add', new CKEDITOR.dialogCommand('swmetadata_add'));
		editor.addCommand('swmetadata_edit', new CKEDITOR.dialogCommand('swmetadata_edit'));
		editor.addCommand('swmetadata_remove', {
			exec : function( editor )
			{
				var path = new CKEDITOR.dom.elementPath( editor.getSelection().getStartElement() ),
					lastElement = path.lastElement,
					data = lastElement && lastElement.getAscendant( 'data', true );
				if (data && data.getAttribute('_cke_real_class') == 'metadata')
					data.remove(false);
			}
		});
		editor.ui.addButton('swmetadata', {
			label : 'Вставить тэг для служебных данных'
			,command : 'swmetadata_add'
			//,icon: this.path + 'images/btn_metadata_add.gif'
		});
		CKEDITOR.dialog.add('swmetadata_add',this.path+'dialogs/metadata.js');
		CKEDITOR.dialog.add('swmetadata_edit',this.path+'dialogs/metadata.js');
		*/
		
		editor.addMenuGroup('cke_swtags',200);
		if(editor.addMenuItems)
		{
			editor.addMenuItems({
				removeprintonly:
				{
					label: 'Убрать отметку только для печати',
					command : 'removeprintonly',
					group : 'cke_swtags',
					order : 1
					,icon: '/img/icons/template-print-area-remove.png'
				},
				removehiddenuser:
				{
					label: 'Убрать разметку области с текстом, который скрыт при заполнении шаблона',
					command : 'removeprintonly',
					group : 'cke_swtags',
					order : 2
					//,icon: '/img/icons/template-print-area-remove.png'
				},
				swdata_edit:{
					label:'Редактировать область для ввода данных',
					command:'swdata_edit',
					group:'cke_swtags',
					order : 7
					,icon: '/img/icons/template-input-area-edit.png'
				},
				swdata_remove:{
					label:'Удалить область для ввода данных',
					command:'swdata_remove',
					group:'cke_swtags',
					order : 8
					,icon: '/img/icons/template-input-area-remove.png'
				/*},
				swmetadata_edit:{
					label:'Редактировать служебные данные',
					command:'swmetadata_edit',
					group:'cke_swtags',
					order : 9
				},
				swmetadata_remove:{
					label:'Удалить служебные данные',
					command:'swmetadata_remove',
					group:'cke_swtags',
					order : 10*/
				}
			});
			if ( editor.contextMenu && 'designer' == editor.config.toolbar)
			{
				editor.contextMenu.addListener( function( element, selection )
					{
						if ( !element )
							return null;
						var elementPath = new CKEDITOR.dom.elementPath( element ),
							blockLimit = elementPath.blockLimit,
							lastElement = elementPath.lastElement,
							add_item = {};
						// при нажатии правой кнопкой внутри элементов с атрибутом _cke_real_class выводим соответсвующий пункт меню
						var oe = blockLimit && blockLimit.getAscendant('div',true);
						if(oe&&oe.getAttribute('_cke_real_class') == 'printonly')
						{
							add_item.removeprintonly = CKEDITOR.TRISTATE_OFF;
						}
						if(oe&&oe.getAttribute('_cke_real_class') == 'hiddenuser')
						{
							add_item.removehiddenuser = CKEDITOR.TRISTATE_OFF;
						}
						var data = lastElement && lastElement.getAscendant('data',true);
						var div = lastElement && lastElement.getAscendant('div',true);
						//if(element.getAttribute('_cke_real_class') == 'data' || (data && data.getAttribute('_cke_real_class') == 'data'))
						if(element.getAttribute('_cke_real_class') == 'data' || (div && div.getAttribute('_cke_real_class') == 'data'))
						{
							add_item.swdata_edit = CKEDITOR.TRISTATE_OFF;
							add_item.swdata_remove = CKEDITOR.TRISTATE_OFF;
						}
						/*if(element.getAttribute('_cke_real_class') == 'metadata' || (data && data.getAttribute('_cke_real_class') == 'metadata'))
						{
							add_item.swmetadata_edit = CKEDITOR.TRISTATE_OFF;
							add_item.swmetadata_remove = CKEDITOR.TRISTATE_OFF;
						}*/
						return add_item;
					} );
			}
		}

        /*editor.on('key', function( event ){
			if ( event.editor.mode != 'wysiwyg' )
				return;
			var editor = event.editor,
				toolbar = editor && editor.config.toolbar,
				selection = editor && editor.getSelection(),
				element = selection && selection.getStartElement(),
				path = element && new CKEDITOR.dom.elementPath(element),
				lastElement = path && path.lastElement,
				div = lastElement && lastElement.getAscendant('div', true),
				ranges = selection.getRanges(),
				last_range = ranges[ranges.length - 1],
				key = event.data.keyCode,
				dataElement, length;
			//log(key);
			if ( key == 9 || key == ( CKEDITOR.SHIFT + 9 ) ) // TAB || SHIFT+TAB
			{
				var dir = (key == 9)?1:-1;
				// находим ближайший к element тег данных
				dataElement = editor.findNearSwDataTagForElement(element,dir);
				//log(dataElement);
				if (dataElement)
				{
					// переносим курсор и выделение на ближайший к element тег данных
					last_range.selectNodeContents(dataElement);
					last_range.moveToPosition(dataElement,CKEDITOR.POSITION_AFTER_START);// CKEDITOR.POSITION_BEFORE_END
					last_range.select();
					editor.getSelection().scrollIntoView();
					event.cancel();
				}
				return;
			}
			return true;


			if ( 'designer' == editor.config.toolbar )
				return;
			// В режиме заполнения шаблона при вводе в любое место кроме области ввода
			var is_inputData = (div && div.getAttribute('_cke_real_class') == 'data');
			if (!is_inputData)
			{
				dataElement = editor.findNearSwDataTagForElement(element,0);
				if (dataElement)
				{
					// помечаем элемент, что его нельзя выделять. 
					//element.unselectable();
					// переносим курсор и выделение на ближайший к element тег данных
					last_range.selectNodeContents(dataElement);
					last_range.moveToPosition(dataElement,CKEDITOR.POSITION_AFTER_START);// CKEDITOR.POSITION_BEFORE_END
					last_range.select();
					//editor.getSelection().scrollIntoView(); этот вызов блокирует поля ввода
					event.cancel();
				}
			}
			if (is_inputData)
			{
				var c_s = (ranges.length > 1)?ranges[0].startContainer:last_range.startContainer,
					c_e = last_range.endContainer,
					d_s,d_e;
				if (c_s.type == 1 && c_s.getName() == 'tr')
				{
					d_s = c_s.getChildSwDataTag();
				}
				else
				{
					d_s = c_s.getParentSwDataTag();
				}
				if (c_e.type == 1 && c_e.getName() == 'tr')
				{
					d_e = c_e.getChildSwDataTag();
				}
				else
				{
					d_e = c_e.getParentSwDataTag();
				}
				// это выделение захватывает несколько тегов ввода?
				if(d_s && d_e && d_s.getId() != d_e.getId())
				{
					event.cancel();
				}
			}

			switch ( key )
			{
				case 46 :	// delete
					if(ranges[0].endContainer.type == 1)
					{
						//log('ranges[0].endContainer.type==1');
						try{
							length = ranges[0].endContainer.getLength();
						}
						catch(e){
							//log(e);
							//log('ranges[0].endContainer');
							//log(ranges[0].endContainer);
							event.cancel();
							break;
						}
					}
					else
					{
						try{
							length = last_range.endContainer.getLength();
						}
						catch(e){
							//log(last_range.endContainer);
							event.cancel();
							break;
						}
						if (last_range && last_range.endOffset == length)
						{
							//log('last_range.endOffset == length');
							event.cancel();
						}
					}
				break;
				case 8 :	// backspace
					//log('backspace');
					//log(path);
					//log(ranges);
					var de = path.block;
					try{
						var sc = ranges[0].startContainer;
						if(sc.type == 1)
							length = sc.getText().length;
						else
							length = sc.getLength();
					}
					catch(e){
						//log('sc');
						//log(sc);
						event.cancel();
						break;
					}
					//log(length);
					//Область ввода пуста
					if((sc.type == 1 && sc.isSwDataTag() && sc.getText().length == 0 && ranges[0].startOffset == 0))
					{
						//log('value of data element is empty');
						event.cancel();
						break;
					}
					//Курсор находится после последнего символа области ввода, т.к. с последним символом удаляется и весь тег
					if((ranges[0].startOffset == 1 && de.getText().length == 1))
					{
						//log('is last symbol');
						event.cancel();
						break;
					}
					if((length > 0 && ranges[0].startOffset == 0))
					{
						//Левый край выделения или курсор находится вначале первой строки de.getText().length ==length && 
						//log(de.getAttribute('id') +'=='+ path.lastElement.getAttribute('id') +'&&'+ sc.getUniqueId() +'=='+ de.getFirst().getUniqueId());
						if (de.getAttribute('id') == path.lastElement.getAttribute('id') && sc.getUniqueId() == de.getFirst().getUniqueId())
						{
							//log('match id path.block==path.lastElement && sc==path.block.getFirst()');
							event.cancel(); 
						}
					}
				break;
				case 3036 :	// CTRL+SHIFT+HOME
				case 3035 :	// CTRL+SHIFT+END
				case 1065 :	// CTRL + A
					event.cancel();
				break;
			}

		});*/
		/*
		Последовательность событий при открытии шаблона
beforeGetData
getData
mode
dataReady
		*/
		editor.on('beforeGetData',function (e){
			//log('beforeGetData');
			//log(e);
		});
		editor.on('getData',function (e){
			//log('getData');
			//log(e);
		});
		editor.on('mode',function (e){
			//log('mode');
			//log(e);
			e.editor.focus();
		});

		editor.on('dataReady',function (e){
			//log('dataReady');
			//log(e);
			//e.editor.createListSwDataTag(true);
            // обновляем список имен областей ввода
            e.editor.createListXmlDataSectionSysNick();
			//log('e.editor.SwDataTag_arr');
			//log(e.editor.SwDataTag_arr);
			/*
			этот код лишает удобства перехода между полями ввода клавишами вверх-вниз
			for( var j=0;j<e.editor.SwDataTag_arr.length;j++)
			{
				el = e.editor.SwDataTag_arr[j].getParent();
				if (el.getName() != 'td')
				{
					el.unselectableChildNotSwDataTag();
				}
			}
			*/
		});

		editor.on('selectionChange', function (e){
			//log(e);
			var editor = e.editor,
				toolbar = editor && editor.config.toolbar,
				selection = editor && editor.getSelection(), // e.data.selection
				ranges = editor.getSelection().getRanges(),
				last_range = ranges[ranges.length - 1],
				element = selection && selection.getStartElement(), // e.data.element
				path = element && new CKEDITOR.dom.elementPath(element), // e.data.path
				lastElement = path && path.lastElement,
				div = lastElement && lastElement.getAscendant('div', true),
				span = lastElement && lastElement.getAscendant('span', true),
				data = lastElement && lastElement.getAscendant('data', true);
            /*
			// В режиме дизайнера шаблона нельзя в шаблоне редактировать значение в теге метаданных. 
			var is_metaData = (data && data.getAttribute('_cke_real_class') == 'metadata');
			if (toolbar && 'designer' == toolbar && is_metaData)
			{
				//element.unselectable();
				// переносим курсор и выделение за пределы тега метаданных
				last_range.moveToPosition(element,CKEDITOR.POSITION_AFTER_END);
				last_range.select(); 
			}
            */
			// внутри области ввода enterMode должен быть ENTER_BR
			//var is_inputData = (data && data.getAttribute('_cke_real_class') == 'data');
			//if(element.getName() == 'data' || data)
			var is_inputData = (div && div.getAttribute('_cke_real_class') == 'data');
			if(is_inputData)
			{
				editor.config.enterMode = CKEDITOR.ENTER_BR;
				editor.config.shiftEnterMode = CKEDITOR.ENTER_BR;
			}
			else
			{
				editor.config.enterMode = CKEDITOR.config.enterMode;
				editor.config.shiftEnterMode = CKEDITOR.config.shiftEnterMode;
			}
			//log('par debug');
			//log(editor);
			//log(is_inputData);
			//log(toolbar);
            /*
			if (toolbar && 'designer' != toolbar && is_inputData)
			{
				var c_s = (ranges.length > 1)?ranges[0].startContainer:last_range.startContainer,
					c_e = last_range.endContainer,
					d_s,d_e;
				//log(c_s);
				if (c_s.type == 1 && c_s.getName() == 'tr')
				{
					d_s = c_s.getChildSwDataTag();
				//log(d_s);
				}
				else
				{
					d_s = c_s.getParentSwDataTag();
				}
				//log(c_e);
				if (c_e.type == 1 && c_e.getName() == 'tr')
				{
					d_e = c_e.getChildSwDataTag();
				//log(d_e);
				}
				else
				{
					d_e = c_e.getParentSwDataTag();
				}
				// это выделение захватывает несколько тегов ввода?
				if(d_s && d_e && d_s.getId() != d_e.getId())
				{
					last_range.selectNodeContents(d_s);
					last_range.moveToPosition(d_s,CKEDITOR.POSITION_AFTER_START);
					last_range.select();
				}
			}
			*/
			//log(path);
			//log(selection);
			// В режиме заполнения шаблона при попадании курсора в любое место кроме области ввода
            /*
			if (toolbar && 'designer' != toolbar && !is_inputData)
			{
				// находим ближайший к element тег данных
				var dataElement = editor.findNearSwDataTagForElement(element,0);
				//log(dataElement);
				if (dataElement)
				{
					// помечаем элемент, что его нельзя выделять. 
					//element.unselectable();
					// переносим курсор и выделение на ближайший к element тег данных
					last_range.selectNodeContents(dataElement);
					last_range.moveToPosition(dataElement,CKEDITOR.POSITION_AFTER_START);// CKEDITOR.POSITION_BEFORE_END
					last_range.select(); 
					//editor.getSelection().scrollIntoView();
				}
			}
			*/
		});

		editor.on('beforeCommandExec',function (e){
			var selection = e.editor.getSelection(),
				path = selection && new CKEDITOR.dom.elementPath( selection.getStartElement() ),
				lastElement = path && path.lastElement,
				div = lastElement && lastElement.getAscendant( 'div', true ),
				span = lastElement && lastElement.getAscendant( 'span', true ),
				data = lastElement && lastElement.getAscendant( 'data', true ),
				disable = false;
			switch(e.data.name)
			{
				case 'hiddenuser':
					/*
					Нельзя создавать области для печати при выделении внутри тегов
						только для печати
						только для чтения, комментариев
						внутри тегов data, metadata
					*/
					disable = (
						(div && div.getAttribute('_cke_real_class') && div.getAttribute('_cke_real_class').inlist(['hiddenuser','printonly']))
						|| (span && span.getAttribute('_cke_real_class') == 'swcomment')
                        || (div && div.getAttribute('class') == 'template-block')
                        || (div && div.getAttribute('_cke_real_class') == 'data')
						//|| (data && data.getAttribute('_cke_real_class') == 'metadata')
					);
					if (disable)
					{
						e.cancel();
						sw.swMsg.alert('Запрещено', 'В этом месте нельзя создавать область с текстом, который скрыт при заполнении шаблона!');
					}
				break;
				
				case 'printonly':
					/*
					Нельзя создавать области для печати при выделении внутри тегов
						только для печати
						только для чтения, комментариев
						внутри тегов data, metadata
					*/
					disable = (
						(div && div.getAttribute('_cke_real_class') && div.getAttribute('_cke_real_class').inlist(['hiddenuser','printonly']))
						|| (span && span.getAttribute('_cke_real_class') == 'swcomment')
                        || (div && div.getAttribute('class') == 'template-block')
                        || (div && div.getAttribute('_cke_real_class') == 'data')
						//|| (data && data.getAttribute('_cke_real_class') == 'metadata')
					);
					if (disable)
					{
						e.cancel();
						sw.swMsg.alert('Запрещено', 'В этом месте нельзя создавать область для печати!');
						//e.data.command.disable();
						//e.data.command.uiItems[0] && e.data.command.uiItems[0].setState(CKEDITOR.TRISTATE_DISABLED);
						//e.data.command.enable();
						//e.data.command.uiItems[0] && e.data.command.uiItems[0].setState(CKEDITOR.TRISTATE_ON);
					}
				break;
				case 'swcomment':
					disable = (
						(div && div.getAttribute('_cke_real_class') && div.getAttribute('_cke_real_class').inlist(['hiddenuser','printonly']))
                        || (div && div.getAttribute('class') == 'template-block')
                        || (div && div.getAttribute('_cke_real_class') == 'data')
						//|| (data && data.getAttribute('_cke_real_class') == 'metadata')
					);
					if (disable)
					{
						e.cancel();
						sw.swMsg.alert('Запрещено', 'В этом месте нельзя создавать комментарий для пользователя!');
					}
				break;
				case 'swdata_add':
                    //log('beforeCommandExec swdata_add');
                    //log(div);
                    //log(path);
					disable = (
						(div && div.getAttribute('_cke_real_class') && div.getAttribute('_cke_real_class').inlist(['hiddenuser','printonly']))
						|| (span && span.getAttribute('_cke_real_class') == 'swcomment')
						|| (div && div.getAttribute('class') == 'template-block')
						|| (div && div.getAttribute('_cke_real_class') == 'data')
						//|| (data && data.getAttribute('_cke_real_class') == 'metadata')
					);
					if (disable)
					{
						e.cancel();
						sw.swMsg.alert('Запрещено', 'В этом месте нельзя создавать область для ввода данных!');
					}
				break;
				/*case 'swmetadata_add':
					if (data && data.getAttribute('_cke_real_class') == 'metadata')
					{
						e.cancel();
					}
				break;*/
				case 'swtagreplacement_add':
					disable = !(div && div.getAttribute('_cke_real_class') && div.getAttribute('_cke_real_class').inlist(['hiddenuser','printonly']));
					if (disable)
					{
						e.cancel();
						sw.swMsg.alert('Запрещено', 'В это место нельзя вставлять тег для подстановки данных! Вы можете вставить тег внутри области для печати или области, помеченной как текст скрытый при заполнении шаблона.');
					}
				break;
				//default:
				//	e.data.command.enable();
				//	e.data.command.uiItems[0] && e.data.command.uiItems[0].setState(CKEDITOR.TRISTATE_ON);
			}
		});
	},
	afterInit : function( editor )
	{
		// Register a filter to displaying placeholders after mode change.
		var dataProcessor = editor.dataProcessor,
			dataFilter = dataProcessor && dataProcessor.dataFilter;

		/** Логика преобразования в код отображаемый в виз.редакторе
			Отобрать элементы div с class="printonly"
				удалить атрибуты элемента
				и установить атрибуты
				стиль отображения
					style : setStyle,
				атрибут пояснение
					title: "только для печати"
				и атрибуты для восстановления реального элемента:
					_cke_real_class : className,
					_cke_real_style: realStyle,
					_cke_real_element_type: realElementType
				вернуть преобразованный элемент (дочерние элементы должны остаться без изменений!)
		*/
		if ( dataFilter )
		{
			dataFilter.addRules(
				{
					elements :
					{
						div : function( element )
						{
							var el_class = element.attributes && element.attributes['class'],
							toolbar = el_class && editor.config.toolbar,visual_attributes;
							switch(el_class)
							{
								case 'printonly':
									visual_attributes = {
										title: mode_styles.printonly.title
									};
									if (toolbar && 'designer' == toolbar)
										visual_attributes.style = mode_styles.printonly.designer;
									else
										visual_attributes.style = mode_styles.printonly.input;
									return editor.SwTagToVisual(element,'div',visual_attributes);
								break;
								case 'hiddenuser':
									visual_attributes = {
										title: mode_styles.hiddenuser.title
									};
									if (toolbar && 'designer' == toolbar)
										visual_attributes.style = mode_styles.hiddenuser.designer;
									else
										visual_attributes.style = mode_styles.hiddenuser.input;
									return editor.SwTagToVisual(element,'div',visual_attributes);
								break;
							}
						},
						data : function( element )
						{
							var el_class = element.attributes && element.attributes['class'],
							toolbar = el_class && editor.config.toolbar,visual_attributes;
							switch(el_class)
							{
								case 'data':
									visual_attributes = {
										title: CKEDITOR.config.swtagStyles_data.title
									};
									if (toolbar && 'designer' == toolbar)
										visual_attributes.style = CKEDITOR.config.swtagStyles_data.designer;
									else
										visual_attributes.style = CKEDITOR.config.swtagStyles_data.input;
									//return editor.SwTagToVisual(element,'data',visual_attributes);
									return editor.SwTagToVisual(element,'data',visual_attributes,'div');
								break;
							}
						},
						span : function( element )
						{
							var el_class = element.attributes && element.attributes['class'],
							toolbar = el_class && editor.config.toolbar,visual_attributes;
							switch(el_class)
							{
								case 'swcomment':
									visual_attributes = {
										title: mode_styles.comment.title,
										style: mode_styles.comment.designer
									};
									return editor.SwTagToVisual(element,'span',visual_attributes);
								break;
								/*
								case 'data':
									var toElementType = 'data';
									visual_attributes = {
										title: CKEDITOR.config.swtagStyles_data.title,
										style: CKEDITOR.config.swtagStyles_data.designer
									};
									return editor.SwTagToVisual(element,'span',visual_attributes,toElementType);
								break;
								*/
								case 'metadata':
									var toElementType = 'data';
									if (toolbar && 'designer' == toolbar)
										visual_attributes = {
											title: CKEDITOR.config.swtagStyles_metadata.title,
											style: CKEDITOR.config.swtagStyles_metadata.designer
										};
									else
										visual_attributes = {
											style: CKEDITOR.config.swtagStyles_metadata.input
										};
									return editor.SwTagToVisual(element,'span',visual_attributes,toElementType);
								break;
							}
						}
					}
				});
		}

		/* Логика обратного преобразования (восстановления)
			Отобрать элементы div,span с атрибутом _cke_real_class
				Получить реальные атрибуты элемента
					className = _cke_real_class;
					realStyle = _cke_real_style;
				Удалить атрибуты элемента
				и установить атрибуты
					style : realStyle,
					class : className,
				вернуть преобразованный элемент (дочерние элементы должны остаться без изменений!)
		*/
		var htmlFilter = dataProcessor && dataProcessor.htmlFilter;
		if ( htmlFilter )
		{
			htmlFilter.addRules({
				elements :
				{
					div: function( element )
					{
						var attributes = element.attributes;
						var real_class = attributes && attributes._cke_real_class;
						if ( real_class )
						{
								return editor.SwTagToHtml(element,'div');
						}
						else
							return element;
					},
					span: function( element )
					{
						var attributes = element.attributes;
						var real_class = attributes && attributes._cke_real_class;
						if ( real_class )
							return editor.SwTagToHtml(element,'span');
						else
							return element;
					},
					data: function( element )
					{
						var attributes = element.attributes;
						var real_class = attributes && attributes._cke_real_class;
						if ( real_class )
							return editor.SwTagToHtml(element,'data');
						else
							return element;
					}
				}
			});
		}
	}
	,requires : ['htmlwriter','editingblock', 'domiterator', 'styles']
});

// Definition of elements at which div operation should stopped.
var divLimitDefinition = ( function(){

	// Customzie from specialize blockLimit elements
	var definition = CKEDITOR.tools.extend( {}, CKEDITOR.dtd.$blockLimit );
	// Exclude 'div' itself.
	delete definition.div;
	// Exclude 'td' and 'th' when 'wrapping table'
	if ( CKEDITOR.config.div_wrapTable )
	{
		delete definition.td;
		delete definition.th;
	}
	return definition;
})();
/**
 * Add to collection with DUP examination.
 * @param {Object} collection
 * @param {Object} element
 * @param {Object} database
 */
function addSafely( collection, element, database )
{
	// 1. IE doesn't support customData on text nodes;
	// 2. Text nodes never get chance to appear twice;
	if ( !element.is || !element.getCustomData( 'block_processed' ) )
	{
		element.is && CKEDITOR.dom.element.setMarker( database, element, 'block_processed', true );
		collection.push( element );
	}
}
/**
 * Get the first div limit element on the element's path.
 * @param {Object} element
 */
function getDivLimitElement( element )
{
	var pathElements = new CKEDITOR.dom.elementPath( element ).elements;
	var divLimit;
	for ( var i = 0; i < pathElements.length ; i++ )
	{
		if ( pathElements[ i ].getName() in divLimitDefinition )
		{
			divLimit = pathElements[ i ];
			break;
		}
	}
	return divLimit;
}
/**
 * Divide a set of nodes to different groups by their path's blocklimit element.
 * Note: the specified nodes should be in source order naturally, which mean they are supposed to producea by following class:
 *  * CKEDITOR.dom.range.Iterator
 *  * CKEDITOR.dom.domWalker
 *  @return {Array []} the grouped nodes
 */
function groupByDivLimit( nodes )
{
	var groups = [],
		lastDivLimit = null,
		path, block;
	for ( var i = 0 ; i < nodes.length ; i++ )
	{
		block = nodes[i];
		var limit = getDivLimitElement( block );
		if ( !limit.equals( lastDivLimit ) )
		{
			lastDivLimit = limit ;
			groups.push( [] ) ;
		}
		groups[ groups.length - 1 ].push( block ) ;
	}
	return groups;
}
/**
 * Wrapping 'div' element around appropriate blocks among the selected ranges.
 * @param {Object} editor
 */
function createDiv( editor )
{
	// new adding containers OR detected pre-existed containers.
	var containers = [];
	// node markers store.
	var database = {};
	// All block level elements which contained by the ranges.
	var containedBlocks = [], block;

	// Get all ranges from the selection.
	var selection = editor.document.getSelection();
	var ranges = selection.getRanges();
	var bookmarks = selection.createBookmarks();
	var i, iterator;
	var dtd = CKEDITOR.dtd.div;

	// Calcualte a default block tag if we need to create blocks.
	//var blockTag = editor.config.enterMode == CKEDITOR.ENTER_DIV ? 'div' : 'p';
	var blockTag = editor.config.enterMode == 'div';

	// collect all included elements from dom-iterator
	for ( i = 0 ; i < ranges.length ; i++ )
	{
		iterator = ranges[ i ].createIterator();
		while ( ( block = iterator.getNextParagraph() ) )
		{
			// include contents of blockLimit elements.
			if ( block.getName() in divLimitDefinition )
			{
				var j, childNodes = block.getChildren();
				for ( j = 0 ; j < childNodes.count() ; j++ )
					addSafely( containedBlocks, childNodes.getItem( j ) , database );
			}
			else
			{
				// Bypass dtd disallowed elements.
				while ( !dtd[ block.getName() ] && block.getName() != 'body' )
					block = block.getParent();
				addSafely( containedBlocks, block, database );
			}
		}
	}

	CKEDITOR.dom.element.clearAllMarkers( database );

	var blockGroups = groupByDivLimit( containedBlocks );
	var ancestor, blockEl, divElement;

	for ( i = 0 ; i < blockGroups.length ; i++ )
	{
		var currentNode = blockGroups[ i ][ 0 ];

		// Calculate the common parent node of all contained elements.
		ancestor = currentNode.getParent();
		for ( j = 1 ; j < blockGroups[ i ].length; j++ )
			ancestor = ancestor.getCommonAncestor( blockGroups[ i ][ j ] );

		divElement = new CKEDITOR.dom.element( 'div', editor.document );

		// Normalize the blocks in each group to a common parent.
		for ( j = 0; j < blockGroups[ i ].length ; j++ )
		{
			currentNode = blockGroups[ i ][ j ];

			while ( !currentNode.getParent().equals( ancestor ) )
				currentNode = currentNode.getParent();

			// This could introduce some duplicated elements in array.
			blockGroups[ i ][ j ] = currentNode;
		}

		// Wrapped blocks counting
		var fixedBlock = null;
		for ( j = 0 ; j < blockGroups[ i ].length ; j++ )
		{
			currentNode = blockGroups[ i ][ j ];

			// Avoid DUP elements introduced by grouping.
			if ( !( currentNode.getCustomData && currentNode.getCustomData( 'block_processed' ) ) )
			{
				currentNode.is && CKEDITOR.dom.element.setMarker( database, currentNode, 'block_processed', true );

				// Establish new container, wrapping all elements in this group.
				if ( !j )
					divElement.insertBefore( currentNode );

				divElement.append( currentNode );
			}
		}

		CKEDITOR.dom.element.clearAllMarkers( database );
		containers.push( divElement );
	}

	selection.selectBookmarks( bookmarks );
	return containers;
}

CKEDITOR.plugins.printonlyCmd =
{
	exec : function( editor )
	{
		editor.fire( 'saveSnapshot' );
        var i;
        var remove_attributes = ['class','style'];
        // есть несколько способов реализации
        if (1==2) {
            // этот способ корректно работает, если выделенный фрагмент не находится внутри параграфа
            var selection = editor.getSelection(),
                enterMode = editor.config.enterMode;

            if ( !selection )
                return false;

            var bookmarks = selection.createBookmarks(),
                ranges = selection.getRanges(),
                iterator,
                block;
            for ( i = ranges.length - 1 ; i >= 0 ; i-- )
            {
                iterator = ranges[ i ].createIterator();
                iterator.enlargeBr = enterMode != CKEDITOR.ENTER_BR;
                iterator.enforceRealBlocks = true;
                while ( ( block = iterator.getNextParagraph('div') ) )
                {
                    block.setAttribute('class', 'printonly');
                    block.setAttribute('style', mode_styles.printonly.print);
                    block = editor.SwTagCreateToVisual(block, mode_styles.printonly.designer, 'div', mode_styles.printonly.title, remove_attributes);
                }
            }
            editor.focus();
            editor.forceNextSelectionCheck();
            selection.selectBookmarks( bookmarks );
        } else {
            // этот способ заключает в область для печати не только выделенный фрагмент,
            // но и все что находится рядом с выделенным фрагментом вне div-контейнера
            // эта проблема легко решается выравниванием фрагментов, которые не нужно включать в область для печати
            var containers = createDiv( editor, true );
            // Update elements attributes
            var size = containers.length;
            for ( i = 0; i < size; i++ )
            {
                containers[ i ].setAttribute('class','printonly');
                containers[ i ].setAttribute('style',mode_styles.printonly.print);
                containers[ i ] = editor.SwTagCreateToVisual(containers[ i ],mode_styles.printonly.designer,'div',mode_styles.printonly.title,remove_attributes);
            }
        }
        return true;
	}
};

CKEDITOR.plugins.hiddenuserCmd =
{
	exec : function( editor )
	{
		editor.fire( 'saveSnapshot' );
		var containers = createDiv( editor, true );
		var remove_attributes = ['class','style'];
		// Update elements attributes
		var size = containers.length;
		for ( var i = 0; i < size; i++ )
		{
			containers[ i ].setAttribute('class','hiddenuser');
			containers[ i ].setAttribute('style',mode_styles.hiddenuser.print);
			containers[ i ] = editor.SwTagCreateToVisual(containers[ i ],mode_styles.hiddenuser.designer,'div',mode_styles.hiddenuser.title,remove_attributes);
		}
		editor.fire( 'saveSnapshot' );
	}
};