/*
 MongoGrid - грид для подгрузки значений в поля(комбобоксы), указанные в editor'e
 */

Ext.define('sw.lib.MongoGrid', {
	extend: 'Ext.grid.Panel',
	alias: 'widget.MongoGrid',
	requires: ['Ext.grid.View'],
	viewConfig: {
		loadingText: 'Загрузка'
	},
//	plugins: 
//	[{ 
//		ptype: 'cellediting',
//		clicksToEdit: 1
//		//,delay: 10
//	}],

	initComponent : function(){
		var me = this;
		
		var cellEditingPlug =  Ext.create('Ext.grid.plugin.CellEditing', {
            clicksToEdit: 1})
		
		me.on('render', function(){
			var res = {},
			countMongoCombo = 0;
			
			Ext.Array.each(me.columns, function(o, value, myself)
			{
				if (o.editor)
				{
					countMongoCombo++;
					var ffields = {};
					var cfields = o.editor.store.getProxy().getReader().getFields();
					Ext.Object.each(cfields, function(key, value, myself)
					{
						if (value.type.type != 'auto')
						{
							var nn = value.name
							ffields[nn] = ""
						}
					})
					ffields.object = o.tableName;
					//собираем urls
					var curl = o.editor.store.url;
					//собираем таблицы
					var ctable = o.editor.tableName;
					//собираем в параметры

					res[ctable] = {
						url: o.editor.store.url,
						params : null,
						baseparams : ffields							
					}						
				}
			})
			
			if (countMongoCombo){
				Ext.Ajax.request({
					url: '/?c=MongoDBWork&m=getDataAll',
					callback: function(opt, success, response) {
						if (success){
							var response_obj = Ext.JSON.decode(response.responseText);
							//заполняем комбики монго
							var mongos = me.columns

							Ext.Array.each(mongos, function(o, value, myself)
							{
								if (o.editor)
								{
									o.editor.store.loadData(response_obj[o.editor.tableName], true);
									o.editor.store.commitChanges();
								}
							})
						}
					}.bind(this),
					params: {'data': Ext.JSON.encode(				
						res
					)}
				})
			}
			
		})
		
		Ext.apply(me, {
			plugins: 
			[cellEditingPlug]
		})

        me.callParent();
	}
	
})
