/**
 * Функции для работы с оберткой indexedDB
 */

function SqlToArray(sql) {
	//var myRe = /SELECT\s+([\w\*\)\(\,\s]+)\s+FROM\s+([\w]+)\s*(?:WHERE\s+)*(.*)(GROUP\s+BY\s+[\w\*\,\s]+)LIMIT\s+(\d+)/img;
	// TODO: Здесь надо будет сделать по-другому, потому что сейчас обрабатывается только одна ситуация  
	var myRe = '';
	var iswhere=false;
	if (/WHERE/i.test(sql)) {
		myRe = myRe + '(?:WHERE\\s+)*(.*)';
		iswhere = true;
	}
	if (/GROUP/i.test(sql)) {
		myRe = myRe + '(?:GROUP\\s+BY\\s+[\\w\*\\,\\s]+)';
	}
	if (/ORDER/i.test(sql)) {
		myRe = myRe + '(?:ORDER\\s+BY\\s+[\\w\\*\\,\\s]+)';
	}
	if (/LIMIT/i.test(sql)) {
		myRe = myRe + 'LIMIT\\s+(\\d+)';
	}
	var my = new RegExp(myRe, "img");
	var r = my.exec(sql);
	// result[1] еще разбираем на параметры
	var params = [];
	if (iswhere && r[1]) {
		var where = r[1];
		
		var data = [];
		while (where.length>0) {
			//	myRe = /\s*(\w+)\s*(=|like|in)\s*([\w\'\.\%]+)\s*(and|or)*(.*)/img;
			//	myRe = /\s*(\w+)\s*(=|like|in)\s*([\w\'\.\,\%]+)|([^\(\w\'\.\,\s\)]+)\s*(and|or)*(.*)/img;
			//	myRe = /\s*(\w+)\s*(=|like|in)\s*([\w\'\.\,\%]+|)|([^\(\w\'\.\,\s\)]+)\s*(and|or)*(.*)/img;
			//	myRe = /\s*(\w+)\s*(=|like|in)\s*([\w\'\.\,\%]+)|([^\(\w\'\.\,\s\)]+)\s*(and|or)*(.*)/img;
			my = /\s*(\w+)\s*(=|>=|<=|>|<|like|in|not in|<>|!=)\s*(([А-Яа-я\w\'\.\,\%]+)|([\(А-Яа-я\w\'\.\,\%\s\)]+))\s*(and|or)*(.*)/img;
			//log('where:',where);
			data = my.exec(where);
			if (data) {
				//log('sql params: ',data);
				params.push({name:data[1], value:data[3], type: data[2], next:data[6]});
				where = data[7];
			} else {
				where = '';
			}
		}
		//log(params);
	}
	var result = {};
	result['params'] = params;
	result['limit'] = (r[2])?r[2]:null;
	// log('SqlToArray = ', result);
	return result;
}

function getClause(params) {
	var result = [];
	var r = '';
	if (params && params.length>0) {
		for (var i=0; i<params.length; i++) {
			result[i] = false;
			//log('params[i]', params[i]);
			// Еще условие "и-или" надо обрабатывать, когда второе и третье условие берется 
			if (params[i].name && params[i].type) { // есть такое же поле
				if (params[i].value == "''") { params[i].value = ""; } // если пустое то ищем пусто "", а не "''"

				switch ( params[i].type.toLowerCase() ) {
					case '=':
						result[i] = '(record["'+params[i].name+'"]=="'+params[i].value+'") ';
						break;
					case '>': case '>=': case '<': case '<=':
						result[i] = '(record["'+params[i].name+'"]'+params[i].type+''+params[i].value+') '; // надо ли здесь экранировать значения кавычками? пока непонятно...
						break;
					case '<>': case '!=':
						result[i] = '(record["'+params[i].name+'"]!="'+params[i].value+'") ';
						break;
					case 'like':
						var v = params[i].value.replace(/'/ig, '');
						if (v && v.length>0) {
							if (v[0]!="%") {// первое не %
								v = '^'+v.replace(/%/ig, '');
							} else {
								v = v.replace(/%/ig, '');
							}
						}
						//if (data[params[i].name]==params[i].value) { // TODO: условие на вхождение надо 
						result[i] = '(record["'+params[i].name+'"].search(new RegExp("'+v+'", "i"))>=0) ';
						break;
					case 'in':
						//var v = params[i].value.replace(/'/ig, '');
						var v = params[i].value;
						if (v && v.length>0) {
							v = v.replace('(', '[');
							v = v.replace(')', ']');
						}
						result[i] = '(record["'+params[i].name+'"].inlist('+v+')) ';
						break;
					case 'not in':
						//var v = params[i].value.replace(/'/ig, '');
						var v = params[i].value;
						if (v && v.length>0) {
							v = v.replace('(', '[');
							v = v.replace(')', ']');
						}
						result[i] = '(!record["'+params[i].name+'"].inlist('+v+')) ';
						break;
					default:
						break;
				}
			}
			if (i>0 && params[i-1].next) {
				switch ( params[i-1].next.toLowerCase() ) {
					case 'or':
						r = (result[i-1]+' || '+result[i]);
						break;
					case 'and':
						r = (result[i-1]+' && '+result[i]);
						break;
					default:
						break;
				}
			} else {
				r = result[i];
			}
		}
	}
	// log(r);
	return r;
}


function getSpecificLimit(table, params, limit) {
	var limit = limit;
	if (params && params.length>0) {
		for (var i=0; i<params.length; i++) {
			if (table=='Diag') {
				if (params[i].name.toLowerCase() == "diag_code") {
					var ln = (params[i].value.indexOf('.')>=0)?8:7;
					if (params[i].value.length>=ln) {
						limit = 1;
					}
					else if (params[i].value.length>=4) {
						limit = 25;
					}
				}
				if (params[i].name.toLowerCase() == "diag_id") {
					limit = 1;
				}
			}
		}
	}
	return limit;
}