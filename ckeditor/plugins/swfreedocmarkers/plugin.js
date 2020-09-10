/*
 * Плагин отвечает за работу с маркерами.
 */
CKEDITOR.config.swfreedocmarkers = {
	EvnClass_id: 1
};

/*
 * Установка класса события.
 */
CKEDITOR.editor.prototype.setFDMEvnClass = function(EvnClass_id)
{
	this.config.EvnClass_id = EvnClass_id;
	CKEDITOR.config.swfreedocmarkers.EvnClass_id = EvnClass_id;
};

/*
 * Получение класса события.
 */
CKEDITOR.editor.prototype.getFDMEvnClass = function()
{
	if(this.config.EvnClass_id)
		return this.config.EvnClass_id;
	return CKEDITOR.config.swfreedocmarkers.EvnClass_id;
};

 
CKEDITOR.plugins.add('swfreedocmarkers', {
	init : function( editor ) {
		//пункты контекстного меню
		editor.addMenuGroup('cke_swfreedocmarkers',200);
		if(editor.addMenuItems) {
			editor.addMenuItems({
				fdmadd:{
					label:'Добавить спецмаркер',
					command:'fdmadd',
					group:'cke_swfreedocmarkers',
					order : 1,
                    icon: '/img/icons/template-data-tag.png'
				}
			});
			if (editor.contextMenu  && 'designer' == editor.config.toolbar) {
				editor.contextMenu.addListener( function( element, selection ){
					if ( !element )
						return null;
					add_item = {};
					add_item.fdmadd = CKEDITOR.TRISTATE_OFF;
					return add_item;
				});
			}
		}
		
		//команды
		editor.addCommand('fdmadd', {
			exec : function( e )
			{
				getWnd('swMarkerValueListWindow').show({
					EvnClass_id:editor.getFDMEvnClass(),
					onSelect: function(data){
						for(var f in data){
							e.insertText('@#@'+data[f].marker+'\n');
						}
					},
					onHide: function(){
						e.focus();
					}
				});
			}
		});
		
		//кнопки тулбара
		editor.ui.addButton('fdmadd', {
			label : 'Добавить спецмаркер'
			,command : 'fdmadd'
			,icon: '/img/icons/template-data-tag.png'
		});
	}
});