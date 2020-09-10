function getTableFields(db, table)
{    var conn = Ext.sql.Connection.getInstance();
    conn.open(db);
	try {		var ret = conn.query('select * from ' + table + ' limit 1');
	}
	catch (e) {		conn.close();
		return false;
	}
	if ( ret.length > 0 )
	{
		var list = new Array();
		var i = 0;
		for ( field_name in ret[0] )
		{
			list[i] = field_name;
			i++;
		}
		conn.close();
		return list;
	}
	else
	{
		conn.close();
		return false;
	}
}

// забираем из БД список существующих таблиц
function getDbTables(db)
{
	var tables = new Array();
	var conn = Ext.sql.Connection.getInstance();
    conn.open(db);
	try {
		var ret = conn.query("SELECT name FROM sqlite_master WHERE type='table'	ORDER BY name");
	}
	catch (e) {
		conn.close();
		return false;
	}
	if ( ret.length > 0 )
	{
		for ( var i = 0; i < ret.length; i++)
		{
			tables[i] = ret[i]['name'];
		}
		conn.close();
	}
	else
	{
		conn.close();
		return false;
	}
	return tables;
}

// функция возвращает список полей в таблицах, существующих в базе в виде массива ['имя таблицы': [filedname1, fieldname2, fieldname3 ... ]]

function getTablesFields(db)
{
	// забираем из БД список существующих таблиц
	var tables = getDbTables(db);
	if ( tables === false )
		return false;
	// формируем список запрашиваемых полей и список таблиц для запроса к БД
    var conn = Ext.sql.Connection.getInstance();
    conn.open(db);
	var tables_fields = {};
	for ( var j = 0; j < tables.length; j++ )
	{
		try {
			var ret = conn.query('select * from ' + tables[j] + ' limit 1');
		}
		catch (e) {
			conn.close();
			return false;
		}
		if ( ret.length > 0 )
		{
			var table_name = tables[j];
			tables_fields[table_name] = new Array();
			var i = 0;
			for ( field_name in ret[0] )
			{
				tables_fields[table_name].push(field_name);
				i++;
			}
		}
	}
	conn.close();
	return tables_fields;
}


function sw_select_from_local_db(db, query, callback)
{
    var conn = Ext.sql.Connection.getInstance();
    conn.open(db);

	try {
		var data_set = conn.query(query);
	}
	catch (e) {
		conn.close();
		return false;
	}
	conn.close();
	if (callback && typeof callback=='function')
		callback(data_set);
	else
		return data_set;
}

function sw_exec_query_local_db(db, query)
{
    var conn = Ext.sql.Connection.getInstance();
    conn.open(db);

	try {
		conn.exec(query);
	}
	catch (e) {
		conn.close();
		return false;
	}

	conn.close();

    return true;
}

// Реализация поддержки версионности 
Ext.sql.Version = function(config){
	Ext.apply(this, config);
	Ext.sql.Version.superclass.constructor.call(this);
	this.addEvents({
		change : true
	});
};
Ext.extend(Ext.sql.Version, Ext.util.Observable, {
	change: function (ver) {
		var tableVer = new Ext.sql.SQLiteStore({
			autoLoad: true,
			dbFile: 'Promed.db',
			fields: [
				{ name: 'Versions_ver', mapping: 'Versions_ver', type: 'float' },
				{ name: 'Versions_date', mapping: 'Versions_date', type: 'date', dateFormat: 'd.m.Y H:i:s' }
			],
			key: 'Versions_ver',
			tableName: 'Versions'
		});
		var records = new Array(new Ext.data.Record({Versions_ver: ver, Versions_date: new Date()}));
		if (tableVer && ver) {
			tableVer.add(records)
		}
	},

	get: function () {
		var select_result = sw_select_from_local_db('Promed.db', "select max(Versions_ver) as Versions_ver from Versions");
		if (select_result.length>0) {
			return select_result[0]['Versions_ver'];
		} else {
			// Первый раз ставим 1 чтобы не забирать данные для уже загруженных справочников в Промеде по старому алгоритму. 
			// Для того чтобы проверить, что данный пользователь заходит уже не первый раз, делаем запрос по первой попавшейся таблице 
			// TODO: декабрь 2011 А вообще здесь конечно просто ноль должен быть 
			var sresult = sw_select_from_local_db('Promed.db', "select max(YesNo_id) as YesNo_id from YesNo");
			if (sresult.length>0) {
				return 1;
			} else {
				return 0;
			}
		}
	}
});