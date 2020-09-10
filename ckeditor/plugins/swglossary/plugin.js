CKEDITOR.editor.prototype.input_text = null;

CKEDITOR.editor.prototype.getToolBoxHeight = function(){
	var t = Ext.get('cke_top_'+ this.name);
	return t.getHeight();
};
/*
* Плагин для интеграции глоссария (sw.Promed.Glossary) с визуальным редактором (CKEDITOR).
*/
CKEDITOR.plugins.add( 'swglossary',
{
	init : function( editor )
	{
		function getGlossaryTagTypeSysNick(e)
		{
			if(e.SwDataTag_arr && e.SwDataTag_arr[0])
			{
				var s = e && e.getSelection(),
					el = s.getStartElement(),
					t = el && el.getParentSwDataTag();
				return t && t.getId();
			}
			else
			{
				return e.element.getNameAtt();
			}
		}

		function getSelectedText(ns)
		{
			if ( CKEDITOR.env.ie )
			{
					if(sw.Promed.Glossary.ie_selected_text && sw.Promed.Glossary.ie_selected_text.length > 0)
					{
						return sw.Promed.Glossary.ie_selected_text
					}
					else
					{
						var range = ns.createRange();
						return range.text;
					}
			}
			else
			{
				return ns.toString();
			}
		}
		
		editor.addCommand('glmenushow', {
			exec : function( e )
			{
				if(sw.Promed.Glossary.isEnableGlossary() == false)
				{
					return;
				}

				//log('glossaryMenuShowCmd');
				var s = e && e.getSelection(),
					r = s && s.getRanges(),
					ns = s && s.getNative(),
					text = getSelectedText(ns),
					range = (CKEDITOR.env.ie && ns.createRange()),
					xy_arr;
				if(!r || !r[0])
				{
					log('not selected range');
					return false;
				}

				if (e.input_text)
				{
					text = e.input_text;// это текст в юникоде в англ.раскладке и верхн.регистре
					//требуется получить введенный фрагмент текста
					if ( CKEDITOR.env.ie )
					{
						var range2 = range.duplicate();
						if(range2.moveStart('character',-(e.input_text.length)))
						{
							text = range2.text;
							//range2.collapse(false);
						}
					}
					else 
					{
						var st = r[0].startContainer.getText();
						var startInputOffset = r[0].startOffset - e.input_text.length; 
						text = st.substr(startInputOffset,(e.input_text.length));
					}
				}
				else if(text)
				{
					//log('is selected range');
					//требуется определить положение списка-меню относительно курсора мыши. Тут event не доступен(( и пока не передать его при клике по контекстному меню или кнопке(( этот момент упущен в CKEDITOR
					// требуется восстановить выделение в IE, если оно было утеряно
					if ( CKEDITOR.env.ie && (!range.text || range.text.length == 0))
					{
						range.moveEnd('character',text.length);
					}
				}
				else 
				{
					log('not text');
					return false;
				}
				
				//log(editor);
				xy_arr = sw.Promed.Glossary.getCursorOffset(editor);
				
				//console.log(getXYZ(editor));
				
				//alert(text+ ' ' +getSelectedText(ns));
				var option = {
					editor: e,
					text:text,
					xy_arr:xy_arr,
					//element_to_align_to: Ext.get(editor.element.getId()),
					GlossaryTagType_SysNick: getGlossaryTagTypeSysNick(e),
					onSelect: function(data){
						//deleteMark(e);
						//если это ввод, то имитируем пользовательское выделение
						if(e.input_text)
						{
							var content,
								startContainer,
								start,
								ck_range = new CKEDITOR.dom.range(e.document);
							if ( CKEDITOR.env.ie )
							{
								//startContainer = new CKEDITOR.dom.node(range.parentElement());
								if(range.moveStart('character',-(e.input_text.length)))
								{
									if ( ns.type == 'Control' )
										ns.clear();
									range.pasteHTML(data.Glossary_Word);
									range.select();
									range.collapse(false);
								}
								else
								{
									if ( ns.type == 'Control' )
										ns.clear();
									range2.pasteHTML(data.Glossary_Word);
									range2.select();
									range2.collapse(false);
								}
							}
							else
							{
								startContainer = r[0].startContainer;
								//content = r[0].startContainer.$.nodeValue;
								content = startContainer.getText();
								start = content.lastIndexOf(text);
								ck_range.setStart(startContainer, start);
								ck_range.setEnd(startContainer, (start + e.input_text.length));
								s.selectRanges([ck_range]);
								e.insertText(data.Glossary_Word);
							}
							e.input_text = null;
						}
						else
						{
							e.insertText(data.Glossary_Word);
						}
					},
					onHide: function(){
						//надо установить фокус в исходную позицию, если этого не произошло
						e.focus();
					}
				};

				//log(option);
				sw.Promed.Glossary.menuShowAt(option);
			}
		});

		editor.addCommand('gladd', {
			exec : function( e )
			{
				if(sw.Promed.Glossary.isEnableGlossary() == false)
				{
					return;
				}
				if(!sw.Promed.Glossary.isEnablePersGlossary() && !isAdmin)
				{
					sw.swMsg.alert('Сообщение', 'Вы не можете добавить запись в базовый глоссарий. Выберите опцию "Использовать личный глоссарий" в настройках.');
					return;
				}
				var s = e && e.getSelection(),
					selection = s && s.getNative(),
					text = getSelectedText(selection);
				if(!text)
				{
					return false;
				}

				sw.Promed.Glossary.openEditWindow({
					action: 'add',
					Glossary_id: 0,
					Glossary_Word: text,
					GlossaryTagType_SysNick: getGlossaryTagTypeSysNick(e),
					pmUser_did: (isAdmin)?null:getGlobalOptions().pmuser_id,
					onHide: function(){
						e.focus();
					}
				});
			}
		});

		editor.addCommand('gladdexpress', {
			exec : function( e )
			{
				if(sw.Promed.Glossary.isEnableGlossary() == false)
				{
					return;
				}
				if(!sw.Promed.Glossary.isEnablePersGlossary() && !isAdmin)
				{
					sw.swMsg.alert('Сообщение', 'Вы не можете добавить запись в базовый словарь. Выберите опцию "Использовать личный словарь" в настройках.');
					return;
				}
				var s = e && e.getSelection(),
					selection = s && s.getNative(),
					text = getSelectedText(selection);

				//добавляем без толкования (без открытия формы)
				sw.Promed.Glossary.addRecord({
					Glossary_Word: text,
					GlossaryTagType_SysNick: getGlossaryTagTypeSysNick(e),
					callback: function(data){
						sw.swMsg.alert('Сообщение', 'Запись успешно добавлена!',function(){e.focus();});
					}
				});
			}
		});

		editor.addCommand('glusetag', {
			exec : function( editor )
			{
				sw.Promed.Glossary.setEnableContextSearch(sw.Promed.Glossary.isEnableContextSearch() == false);
			}
		});

		editor.addCommand('gluse', {
			exec : function( editor )
			{
				sw.Promed.Glossary.setEnableGlossary(sw.Promed.Glossary.isEnableGlossary() == false);
			}
		});

		//кнопки тулбара
		editor.ui.addButton('glmenushow', {
			label : 'Глоссарий'
			,command : 'glmenushow'
			,icon: '/img/icons/glossary16.png'
		});
		editor.ui.addButton('gladd', {
			label : 'Добавить в словарь с толкованием'
			,command : 'gladd'
			,icon: '/img/icons/glossary-add16.png'
		});
		editor.ui.addButton('gladdexpress', {
			label : 'Добавить в словарь'
			,command : 'gladdexpress'
			,icon: '/img/icons/glossary-add16.png'
		});
		
		//пункты контекстного меню
		editor.addMenuGroup('cke_swglossary',200);
		if(editor.addMenuItems)
		{
			editor.addMenuItems({
				gluse:{
					label:'Использовать глоссарий',
					command:'gluse',
					group:'cke_swglossary',
					order : 1,
					icon: '/img/icons/glossary-use16.png'
				},
				glnotuse:{
					label:'Отключить глоссарий',
					command:'gluse',
					group:'cke_swglossary',
					order : 2,
					icon: '/img/icons/glossary-use16.png'//с галочкой
				},
				glusetag:{
					label:'Искать слова по контексту',
					command:'glusetag',
					group:'cke_swglossary',
					order : 11,
					icon: '/img/icons/glossary-search16.png'
				},
				glnotusetag:{
					label:'Искать слова без учета контекста',
					command:'glusetag',
					group:'cke_swglossary',
					order : 12,
					icon: '/img/icons/glossary-search16.png'//с галочкой
				},
				glmenushow:
				{
					label:'Глоссарий',
					command : 'glmenushow',
					group : 'cke_swglossary',
					order : 3,
					icon: '/img/icons/glossary16.png'
				},
				gladdexpress:
				{
					label:'Добавить в словарь',
					command : 'gladdexpress',
					group : 'cke_swglossary',
					order : 4,
					icon: '/img/icons/glossary-add16.png'
				},
				gladd:
				{
					label:'Добавить в словарь с толкованием',
					command : 'gladd',
					group : 'cke_swglossary',
					order : 5,
					icon: '/img/icons/glossary-add16.png'
				}
			});
			if (editor.contextMenu)
			{
				editor.contextMenu.addListener( function( element, selection ){
					if ( !element )
						return null;
					var ranges = selection && selection.getRanges(),
						ns = selection && selection.getNative(),
						add_item = {};

					//добавляем в меню, если включена опция использовать глосссарий
					if(sw.Promed.Glossary.isEnableGlossary())
					{
						add_item.glnotuse = CKEDITOR.TRISTATE_OFF;
						if(sw.Promed.Glossary.isEnableContextSearch())
						{
							add_item.glnotusetag = CKEDITOR.TRISTATE_OFF;
						}
						else
						{
							add_item.glusetag = CKEDITOR.TRISTATE_OFF;
						}

						//добавляем в меню, если есть выделенный фрагмент
						if (sw.Promed.Glossary.ie_selected_text || (ranges && ranges[0] && ranges[0].collapsed == false))
						{
							add_item.glmenushow = CKEDITOR.TRISTATE_OFF;
							add_item.gladdexpress = CKEDITOR.TRISTATE_OFF;
							add_item.gladd = CKEDITOR.TRISTATE_OFF;
						}
					}
					else
					{
						add_item.gluse = CKEDITOR.TRISTATE_OFF;
					}
					return add_item;
				});
			}
		}

		editor.on('key', function( event ){
			if ( editor.mode != 'wysiwyg' || sw.Promed.Glossary.isEnableGlossary() == false)
			{
				return true;
			}
			//log(event.data.keyCode);
			if (2041 > event.data.keyCode && event.data.keyCode > 2034)
			{
				// выделение клавишами shift + end home влево вверх вправо вниз (2035 2036 2037 2038 2039 2040)
				/*
				sw.Promed.Glossary.ie_selected_text = null;
				if ( CKEDITOR.env.ie )
				{
					var s = editor.getSelection(),
						ns = s && s.getNative(),
						text = (ns && ns.createRange().text) || null;
					sw.Promed.Glossary.ie_selected_text = text;
					sw.Promed.Glossary.ie_bounding_client_rect = (ns && ns.createRange().getBoundingClientRect()) || null;
				}
				*/
			}
			if (5045 == event.data.keyCode)
			{
				// Ctrl + Alt + Insert - добавить в словарь выделенный текст через форму
				editor.execCommand('gladd', editor);
			}
			
			//log([sw.Promed.Glossary.isKeyCodeAlphabet(event.data.keyCode),sw.Promed.Glossary.menu.processShow,editor.input_text, event.data.keyCode]);
			/*
			if(sw.Promed.Glossary.isKeyCodeAlphabet(event.data.keyCode))
			{
				if(sw.Promed.Glossary.menu.processShow)
				{
					event.cancel();
				}
				else if(editor.input_text && editor.input_text.length > 1)
				{
					sw.Promed.Glossary.menu.processShow = true;
				}
			}*/
			return true;
		});

		editor.on( 'contentDom', function(){

			editor.document.on('keyup', function( event ){
				var key = event.data.getKey();
				
				//log(key);
				if ( editor.mode != 'wysiwyg' || sw.Promed.Glossary.isEnableGlossary() == false)
				{
					return true;
				}
				
				switch ( true )
				{
					case (sw.Promed.Glossary.isKeyCodeAlphabet(key)):
						sw.Promed.Glossary.ie_selected_text = null;
						sw.Promed.Glossary.ie_bounding_client_rect = null;
						/*
						var c = String.fromCharCode(key);
						editor.input_text = (!editor.input_text)?c:editor.input_text + c;
						*/
						//sw.Promed.Glossary.menu.hide();
						//нужно получить слово от первого символа разделителя слева getData
						var s = editor.getSelection(),
							ns = s && s.getNative(),
							r = s && s.getRanges(),
							range = (CKEDITOR.env.ie && ns.createRange()) || null;
						//log([event.data.getTarget().getText(),event.data.getTarget().getValue(),r[0].startContainer.$.wholeText,s.getStartElement(),s,r]);
						if ( CKEDITOR.env.ie )
						{
							var range2 = range.duplicate();
							if(range2.moveStart('character',-50))
							{
								var st = range2.text || ' ',
									a = st.split(/[,:\.\(\)" ]/);
								editor.input_text = a[a.length-1];
							}
						}
						else 
						{
							var st = /*r[0].startContainer.getText(),*/r[0].startContainer.$.wholeText || ' ',
								a = st.split(/[,:\.\(\)" ]/);
							editor.input_text = a[a.length-1];
						}
						if(editor.input_text && typeof editor.input_text.trim == 'function') {
							editor.input_text = editor.input_text.trim();
						}
						if(editor.input_text && editor.input_text.length > 3)
						{
							editor.execCommand('glmenushow', editor);
						}
					break;

					case (8 == key && editor.input_text):	// backspace 
						if (editor.input_text.length > 1)
						{
							editor.input_text = editor.input_text.substring(0,(editor.input_text.length-1));
						}
						else
						{
							editor.input_text = null;
						}
					break;

					case (16 == key):	// SHIFT
						sw.Promed.Glossary.ie_selected_text = null;
						if ( CKEDITOR.env.ie )
						{
							var s = editor.getSelection(),
								ns = s && s.getNative(),
								text = (ns && ns.createRange().text) || null;
							sw.Promed.Glossary.ie_selected_text = text;
							sw.Promed.Glossary.ie_bounding_client_rect = (ns && ns.createRange().getBoundingClientRect()) || null;
						}
						sw.Promed.Glossary.menu.hide();
						//editor.input_text = null;
					break;

					case (27 == key):	// ESC 
						//по идее, после скрытия должна быть возможность продолжить подбор вариантов
						sw.Promed.Glossary.menu.hide();
						//editor.input_text = null;
					break;

					default:
						sw.Promed.Glossary.menu.hide();
						//editor.input_text = null;
					break;
				}
				return true;
			});
			
			editor.document.on('click', function( e ){
				sw.Promed.Glossary.menu.hide();
				//log(e);
				return false;
				//editor.input_text = null;
				//deleteMark(editor);
			});
			
			editor.document.on('mouseup', function( e ){
				sw.Promed.Glossary.ie_selected_text = null;
				if ( CKEDITOR.env.ie )
				{
					var s = editor.getSelection(),
						ns = s && s.getNative(),
						text = (ns && ns.createRange().text) || null;
					sw.Promed.Glossary.ie_selected_text = text;
					sw.Promed.Glossary.ie_bounding_client_rect = (ns && ns.createRange().getBoundingClientRect()) || null;
				}
			});
		});

		editor.on('selectionChange', function (e){
			//e.editor.input_text = null;
		});

		editor.on('dataReady',function (e){
			e.editor.input_text = null;
		});

	}
	,requires : ['swtags']
});