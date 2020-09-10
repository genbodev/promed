/**
* ext.ux.gridprint - класс для печати грида.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package          libs
* @access           public
* @copyright        Copyright (c) 2009 Swan Ltd.
* @original author  Ed Spencer (edward@domine.co.uk)
* @author           Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version          20.08.2009
*/

/**
* @class GetIt.GridPrinter
* @author Ed Spencer (edward@domine.co.uk)
* Helper class to easily print the contents of a grid. Will open a new window with a table where the first row
* contains the headings from your column model, and with a row for each item in your grid's store. When formatted
* with appropriate CSS it should look very similar to a default grid. If renderers are specified in your column
* model, they will be used in creating the table. Override headerTpl and bodyTpl to change how the markup is generated
*
* Usage:
*
* var grid = new Ext.grid.GridPanel({
* colModel: //some column model,
* store : //some store
* });
*
* Ext.ux.GridPrinter.print(grid);
*
*/

Ext.ux.GridPrinter = {
  /**
* Prints the passed grid. Reflects on the grid's column model to build a table, and fills it using the store
* @param {Ext.grid.GridPanel} grid The grid to print
* @param {Object} params Параметры: 'tableHeaderText' - текст в заголовке таблицы.
*									'pageTitle' - текст заголовка страницы.
*/
  print: function(grid, params) {

	if ( params && params.excel ) {

		var bT = Ext.MessageBox.buttonText;
		Ext.MessageBox.buttonText = {
			ok     : "Ok",
			cancel : "Отмена",
			yes    : "EXCEL",
			no     : "HTML"
		};
		Ext.MessageBox.show({
			scope: {
				_this: this,
				bT: bT,
				grid: grid,
				params: params,
			},
			msg: "Выберите формат формирования печатной формы:",
			buttons: Ext.Msg.YESNOCANCEL,
			buttonText: {
				ok		: "OK",
				yes		: "EXCEL",
				no		: "HTML",
				cancel	: "Отмена"
			},
			icon: Ext.MessageBox.QUESTION,
			fn: function (butn) {
				Ext.MessageBox.buttonText = this.bT;
				if ( butn == 'yes' ) {
					return this._this.print_EXCEL(this.grid, this.params);
				} else if (butn == 'no') {
					return this._this.print_HTML(this.grid, this.params);
				}
			}
		});
	} else {
		return this.print_HTML(grid, params);
	}
  },

  print_HTML: function (grid, params) {
	var table_header_text = "";
	var page_title = "";
	var notPrintEmptyRows = false;
	var rowId = null;
	var selections = null;
	var addNumberColumn = false;

	if ( params )
	{
		if (params.notPrintEmptyRows) {
			notPrintEmptyRows = params.notPrintEmptyRows
		}
		
		if ( params.tableHeaderText )
		{
			table_header_text = params.tableHeaderText;
		}
		
		if ( params.pageTitle )
		{
			page_title = params.pageTitle;
		}

		if ( params.rowId )
		{
			rowId = params.rowId;
		}
		
		if ( params.selections )
		{
			selections = params.selections;
		}

		if ( params.addNumberColumn )
		{
			addNumberColumn = true;
		}
	}
	
    //We generate an XTemplate here by using 2 intermediary XTemplates - one to create the header,
    //the other to create the body (see the escaped {} below)
    var columns = grid.getColumnModel().config;
	// колонки с удаленными скрытыми полями
	var processed_columns = Array();
    // удаляем скрытые
	Ext.each(columns, function(column) {
		if (column.hidden != true && column.hiddenPrint != true && column.dataIndex)
		{
			processed_columns.push(column);
		}
    }, this);

	if (addNumberColumn) {
		processed_columns.unshift({dataIndex: 'PrintNum', header: langs('№ п/п')});
	}
    //build a useable array of store data for the XTemplate
    var data = [];
	var num = 0;

	var processDataItem = function(item) {
		var convertedData = [];
		var isEmpty = true;
		if (addNumberColumn) {
			item.data.PrintNum = ++num;
		}

		//apply renderers from column model
		for (var key in item.data) {
			var value = item.data[key];
			if ( value == null )
				value = '';

			Ext.each(processed_columns, function(column) {
				if (column.dataIndex == key) {
					convertedData[key] = column.renderer ? column.renderer(value,null,item) : value;
					if (!Ext.isEmpty(convertedData[key]) && key!='PrintNum') { isEmpty = false; }
				}
			}, this);
		}
		if (!notPrintEmptyRows || !isEmpty) {
			data.push(convertedData);
		}
	}

	if (rowId) {
		processDataItem(grid.store.getById(rowId));
	} else if (selections) {
		for(var k in selections) {
			if (typeof selections[k] == 'object') {
				processDataItem(selections[k]);
			}
		}
	} else {
		grid.store.data.each(processDataItem);
	}

    //use the headerTpl and bodyTpl XTemplates to create the main XTemplate below
    var headings = Ext.ux.GridPrinter.headerTpl.apply(processed_columns);
    var body = Ext.ux.GridPrinter.bodyTpl.apply(processed_columns);

	if ( page_title == "" )
	{
		if ( grid.title == undefined )
		{
			page_title = "Печать списка деталей";
		}
		else
		{
			page_title = grid.title;
		}
	}
	
	// добавляем хэдр к таблице
	var table_header = "";
	if ( table_header_text != "" )
	{
		var table_header = "<th colspan='" + processed_columns.length + "'><center><b>" + table_header_text + "</b></center></th>";
	}

    var html = new Ext.XTemplate(
      '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
      '<html>',
        '<head>',
          '<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />',
          '<link href="' + Ext.ux.GridPrinter.stylesheetPath + '" rel="stylesheet" type="text/css" media="screen,print" />',
          '<title>' + page_title + '</title>',
        '</head>',
        '<body>',
          '<table>',
			table_header,
            headings,
            '<tpl for=".">',
              body,
            '</tpl>',
          '</table>',
        '</body>',
      '</html>'
    ).apply(data);

    //open up a new printing window, write to it, print it and close
    // для того, чтобы вывод шел не в уже созданое окно
	var id_salt = Math.random();
	var win_id = 'printgrid' + Math.floor(id_salt*10000);
	// собственно открываем окно и пишем в него
    var win = window.open('', win_id);
    win.document.write(html);
	win.document.close();
    //win.print();
    //win.close();
  },

  /**
* @property stylesheetPath
* @type String
* The path at which the print stylesheet can be found (defaults to '/stylesheets/print.css')
*/
  stylesheetPath: '/css/gridprint.css',

  /**
* @property headerTpl
* @type Ext.XTemplate
* The XTemplate used to create the headings row. By default this just uses <th> elements, override to provide your own
*/
  headerTpl: new Ext.XTemplate(
    '<tr>',
      '<tpl for=".">',
        '<th>{header}</th>',
      '</tpl>',
    '</tr>'
  ),

   /**
* @property bodyTpl
* @type Ext.XTemplate
* The XTemplate used to create each row. This is used inside the 'print' function to build another XTemplate, to which the data
* are then applied (see the escaped dataIndex attribute here - this ends up as "{dataIndex}")
*/
  bodyTpl: new Ext.XTemplate(
    '<tr>',
      '<tpl for=".">',
        '<td>\{{dataIndex}\}</td>',
      '</tpl>',
    '</tr>'
  ),

	print_EXCEL: function (grid, params) {

		var page_title = "";
		var notPrintEmptyRows = false;
		var rowId = null;
		var selections = null;

		if ( params ) {
			if (params.notPrintEmptyRows) {
				notPrintEmptyRows = params.notPrintEmptyRows;
			}
			if ( params.pageTitle ) {
				page_title = params.pageTitle;
			}
			if ( params.rowId ) {
				rowId = params.rowId;
			}
			if ( params.selections ) {
				selections = params.selections;
			}
		}

		var title = (!grid.title) ? page_title : grid.title;
		title = (!title) ? "untitled" : title;

		var columns = grid.getColumnModel().config;
		// колонки с удаленными скрытыми полями
		var processed_columns = Array();
		// удаляем скрытые
		Ext.each(columns, function(column) {
			if (column.hidden != true && column.hiddenPrint != true && column.dataIndex) {
				processed_columns.push(column);
			}
		}, this);

		//build a useable array of store data to EXCEL
		var converted_data = [];
		//var num = 0;

		var processDataItem = function(item) {
			var convertedData = [];
			var isEmpty = true;
			//if (addNumberColumn) {
			//	item.data.PrintNum = ++num;
			//}

			//apply renderers from column model
			for (var key in item.data) {
				var value = item.data[key];
				if ( value == null )
					value = '';

				Ext.each(processed_columns, function(column) {
					if (column.dataIndex == key) {
						convertedData[key] = column.renderer ? column.renderer(value,null,item) : value;
						if (!Ext.isEmpty(convertedData[key]) && key!='PrintNum') { isEmpty = false; }
					}
				}, this);
			}
			if (!notPrintEmptyRows || !isEmpty) {
				converted_data.push(convertedData);
			}
		}

		if (rowId) {
			processDataItem(grid.store.getById(rowId));
		} else if (selections) {
			for(var k in selections) {
				if (typeof selections[k] == 'object') {
					processDataItem(selections[k]);
				}
			}
		} else {
			grid.store.data.each(processDataItem);
		}

        var d = [],
        e = [],
        type = [],
        data = [],
        c = {};

        for (var j = 0; j<grid.colModel.config.length; j++) {
            if (!grid.colModel.config[j].hidden) {
				d.push(grid.colModel.config[j].header);
                e.push(grid.colModel.config[j].dataIndex);
                // TODO - create xlsx column type based on grid columns type
                // type.push(columns[j].filter.type);
            }
        }
        //for (var ai = 0; ai < grid.store.data.items.length; ai++) {
        for (var ai = 0; ai < converted_data.length; ai++) {
            //var raw = grid.store.data.items[ai].data;
            var raw = converted_data[ai];
            var x = [];
            var value;
            for (var ae = 0; ae < e.length; ae++) {
				value = raw[e[ae]];
				//если это дата - приводит к виду локал стринг
				//https://jira.is-mis.ru/browse/PROMEDWEB-6658 (penza)
				//https://jira.is-mis.ru/browse/PROMEDWEB-10457 (ekb)
				if (inlist(getRegionNick(), ['penza', 'ekb']) && value.toLocaleDateString) {
					value = value.toLocaleDateString();
				}
				x.push(value);
            }
            data.push(x);
        }

        c.data = data;
        c.titlu = title;
        c.metadata = {
			'text': d,
            'title': title
        }

        var mapForm = document.createElement("form");
        mapForm.target = "_blank";
        mapForm.method = "POST";
        mapForm.action = "/promed/libraries/export_xls.php";

        var mapInput = document.createElement("input");
        mapInput.type = "text";
        mapInput.name = "xlsdata";
        mapInput.value = JSON.stringify(c);

        var filename = document.createElement("input");
        filename.type = "text";
        filename.name = "filename";
        filename.value = title + ".xls";
        // Adaug input in form
        mapForm.appendChild(mapInput);
        mapForm.appendChild(filename);

        // Adaug formularul in dom
        document.body.appendChild(mapForm);

        // Submit
        mapForm.submit();
        mapForm.remove();
	}
};