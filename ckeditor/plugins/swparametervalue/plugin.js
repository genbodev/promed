/*
 * Плагин отвечает за работу с объектами класса «Параметр и список значений»
 */
CKEDITOR.plugins.add('swparametervalue', {
	init : function( editor ) {
		//пункты контекстного меню
		editor.addMenuGroup('cke_swparametervalue',200);
		if(editor.addMenuItems) {
			editor.addMenuItems({
				pvmins:{
					label:'Добавить маркер типа «Параметр и список значений»',
					command:'pvmins',
					group:'cke_swparametervalue',
					order : 1,
					icon: '/img/icons/add16.png'
				}
			});
			if (editor.contextMenu  && 'designer' == editor.config.toolbar) {
				editor.contextMenu.addListener( function( element, selection ){
					if ( !element )
						return null;
					add_item = {};
					add_item.pvmins = CKEDITOR.TRISTATE_OFF;
					return add_item;
				});
			}
		}
		//команды
		editor.addCommand('pvmins', {
			exec : function( e )
			{
				getWnd('swParameterValueListWindow').show({
					onSelect: function(data){
						for(var f in data){
							var marker_name = data[f].marker.split(data[f].ParameterValue_id)[1],
								marker = null,
								i = 1, 
								ins = false;
							while (ins == false && i < 100) {
								marker = '@#@_' + data[f].ParameterValue_id + '_' + i + marker_name;
								if(e.getData().search(marker) < 0) {
									e.insertText(marker+'\n');
									ins = true;
								}
								i++;
							}
						}
					},
					onHide: function(){
						e.focus();
					}
				});
			}
		});
		/**
		 * Маркер объекта класса «Параметр и список значений» нельзя вставлять при выделении 
		 *     внутри области для ввода данных (template-block, тэга data),
		 *     внутри тэга только для печати (printonly),
		 *     внутри тэга комментария (swcomment),
		 *     внутри тэга текста, который скрыт при заполнении шаблона (hiddenuser),
		 *     внутри тэга metadata,
		 */
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
				case 'pvmins':
					disable = (
						(div && div.getAttribute('_cke_real_class') && div.getAttribute('_cke_real_class').inlist(['hiddenuser','printonly']))
						|| (span && span.getAttribute('_cke_real_class') == 'swcomment')
						|| (div && div.getAttribute('class') == 'template-block')
						|| (div && div.getAttribute('_cke_real_class') == 'data')
						|| (data && data.getAttribute('_cke_real_class') == 'metadata')
					);
					if (disable)
					{
						e.cancel();
						sw.swMsg.alert('Запрещено', 'В это место нельзя вставить маркер объекта класса «Параметр и список значений»!');
					}
				break;
			}
		});
		//кнопки тулбара
		editor.ui.addButton('pvmins', {
			label : 'Добавить маркер объекта класса «Параметр и список значений»'
			,command : 'pvmins'
			,icon: '/img/icons/add16.png'
		});
	}
});