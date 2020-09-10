 Ext.sql.GearsConnection = Ext.extend(Ext.sql.Connection, {
	// abstract methods
/*    open : function(db){
        this.conn = new air.SQLConnection();
		var file = air.File.applicationDirectory.resolvePath(db);
		this.conn.open(file);
        this.openState = true;
		this.fireEvent('open', this);
    },*/

	open : function(db, cb, scope){
		this.conn = google.gears.factory.create('beta.database', '1.0');
        this.conn.open(db);
        this.openState = true;
		//Ext.callback(cb, scope, [this]);
		this.fireEvent('open', this);
    },

	createTable : function(o){
		var tableName = o.name;
		var keyName = o.key;
		var fs = o.fields;
		//var cb = o.callback;
		//var scope = o.scope;
		if(!(fs instanceof Array)){ // Ext fields collection
			fs = fs.items;
		}
		var buf = [];
		for(var i = 0, len = fs.length; i < len; i++){
			var f = fs[i], s = f.name;
			switch(f.type){
	            case "int":
	            case "bool":
	            case "boolean":
	                s += ' INTEGER';
	                break;
	            case "float":
	                s += ' REAL';
	                break;
	            default:
	            	s += ' TEXT';
	        }
	        if(f.allowNull === false || f.name == keyName){
	        	s += ' NOT NULL';
	        }
	        if(f.name == keyName){
	        	s += ' PRIMARY KEY';
	        }
	        if(f.unique === true){
	        	s += ' UNIQUE';
	        }

	        buf[buf.length] = s;
	    }
	    var sql = ['CREATE TABLE IF NOT EXISTS ', tableName, ' (', buf.join(','), ')'].join('');
		this.conn.execute(sql).close();
    },

/*	close : function(){
        this.conn.close();
        this.fireEvent('close', this);
    },*/

	close : function(){
        this.conn.close();
        this.fireEvent('close', this);
    },

	/* must be rewriten createStatement : function(type){
		var stmt = new air.SQLStatement();
		stmt.sqlConnection = this.conn;
		return stmt;
	}, */

/*    exec : function(sql){
        var stmt = this.createStatement('exec');
		stmt.text = sql;
		stmt.execute();
    },*/

	exec : function(sql, cb, scope){
        this.conn.execute(sql).close();
//        Ext.callback(cb, scope, [true]);
    },

/*	execBy : function(sql, args){
		var stmt = this.createStatement('exec');
		stmt.text = sql;
		this.addParams(stmt, args);
		stmt.execute();
	},*/

	execBy : function(sql, args, cb, scope){
	    this.conn.execute(sql, args).close();
    //    Ext.callback(cb, scope, [true]);
    },


/*	query : function(sql){
		var stmt = this.createStatement('query');
		stmt.text = sql;
		stmt.execute(this.maxResults);
		return this.readResults(stmt.getResult());
	},*/

	query : function(sql, cb, scope){
        var rs = this.conn.execute(sql);
        var r = this.readResults(rs);
//        Ext.callback(cb, scope, [r]);
        return r;
    },

/*	queryBy : function(sql, args){
		var stmt = this.createStatement('query');
		stmt.text = sql;
		this.addParams(stmt, args);
		stmt.execute(this.maxResults);
		return this.readResults(stmt.getResult());
	},*/

	queryBy : function(sql, args, cb, scope){
        var rs = this.conn.execute(sql, args);
        var r = this.readResults(rs);
        Ext.callback(cb, scope, [r]);
        return r;
    },

    /* mast be added addParams : function(stmt, args){
		if(!args){ return; }
		for(var key in args){
			if(args.hasOwnProperty(key)){
				if(!isNaN(key)){
					var v = args[key];
					if(Ext.isDate(v)){
						v = v.format(Ext.sql.Proxy.DATE_FORMAT);
					}
					stmt.parameters[parseInt(key)] = v;
				}else{
					stmt.parameters[':' + key] = args[key];
				}
			}
		}
		return stmt;
	},*/

    /*readResults : function(rs){
        var r = [];
        if(rs && rs.data){
		    var len = rs.data.length;
	        for(var i = 0; i < len; i++) {
	            r[r.length] = rs.data[i];
	        }
        }
        return r;
    }*/

	readResults : function(rs){
        var r = [];
        if(rs){
            var c = rs.fieldCount();
            // precache field names
            var fs = [];
            for(var i = 0; i < c; i++){
                fs[i] = rs.fieldName(i);
            }
            // read the data
            while(rs.isValidRow()){
                var o = {};
                for(var i = 0; i < c; i++){
                    o[fs[i]] = rs.field(i);
                }
                r[r.length] = o;
                rs.next();
            }
            rs.close();
        }
        return r;
    }
});