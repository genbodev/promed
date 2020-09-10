CKEDITOR.plugins.add('swxmlmarkers', {
	init : function( editor ) {
		//пункты контекстного меню
		editor.addMenuGroup('cke_swxmlmarkers',200);
		if (editor.addMenuItems) {
			editor.addMenuItems({
				swxmlmarkeradd:{
					label:'Добавить маркер документа',
					command:'swxmlmarkeradd',
					group:'cke_swxmlmarkers',
					order : 1,
                    icon: '/img/icons/template-data-tag.png'
				}
			});
			if (editor.contextMenu  && 'designer' == editor.config.toolbar) {
				editor.contextMenu.addListener( function( element ){
					if ( !element ) {
                        return null;
                    }
					var add_item = {};
					add_item.swxmlmarkeradd = CKEDITOR.TRISTATE_OFF;
					return add_item;
				});
			}
		}
		
		//команды
		editor.addCommand('swxmlmarkeradd', {
			exec : function( e )
			{
                getWnd('swXmlMarkerEditWindow').show({
                    callback: function(marker){
                        e.insertHtml(marker+'\n');
                    },
                    onHide: function(){
                        e.focus();
                    }
                });
			}
		});
		
		//кнопки тулбара
		editor.ui.addButton('swxmlmarkeradd', {
			label : 'Добавить маркер документа'
			,command : 'swxmlmarkeradd'
			,icon: '/img/icons/template-data-tag.png'
		});
	}
});