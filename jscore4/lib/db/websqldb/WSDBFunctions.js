function getTableFields(db, table)
{	var conn = Ext.db.indexedDBDriver.getInstance();
	//conn.open(db);
	try {		var ret = conn.query('select * from ' + table + ' limit 1');
	}
	catch (e) {		//conn.close();
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
	var conn = Ext.db.indexedDBDriver.getInstance();
	//conn.open(db);
	try {
		var ret = conn.query("SELECT name FROM sqlite_master WHERE type='table'	ORDER BY name");
	}
	catch (e) {
		//conn.close();
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
	var conn = Ext.db.indexedDBDriver.getInstance();
	//conn.open(db);
	var tables_fields = {};
	for ( var j = 0; j < tables.length; j++ )
	{
		try {
			var ret = conn.query('select * from ' + tables[j] + ' limit 1');
		}
		catch (e) {
			//conn.close();
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
	//conn.close();
	return tables_fields;
}


function sw_select_from_local_db(db, query, callback)
{
	var conn = Ext.db.indexedDBDriver.getInstance();
	//conn.open(db);
	try {
		var data_set = conn.query(query);
	}
	catch (e) {
		//conn.close();
		return false;
	}

	//conn.close();
	if (callback && typeof callback=='function')
		callback(data_set);
	else
		return data_set;
}

function sw_exec_query_local_db(db, query)
{
	var conn = Ext.db.indexedDBDriver.getInstance();
	//conn.open(db);

	try {
		conn.exec(query);
	}
	catch (e) {
		//conn.close();
		return false;
	}

	//conn.close();
	return true;
}
