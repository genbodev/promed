/**
* Ext6.ux.gridprint - класс для печати грида.
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
* var grid = new Ext6.grid.GridPanel({
* colModel: //some column model,
* store : //some store
* });
*
* Ext6.ux.GridPrinter.print(grid);
*
*/

Ext6.ux.GridPrinter = {
  /**
* Prints the passed grid. Reflects on the grid's column model to build a table, and fills it using the store
* @param {Ext6.grid.GridPanel} grid The grid to print
* @param {Object} params Параметры: 'tableHeaderText' - текст в заголовке таблицы.
*									'pageTitle' - текст заголовка страницы.
*/
  print: function(grid, params) {	
	
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
    var columns = grid.getView().getGridColumns();
	// колонки с удаленными скрытыми полями
	var processed_columns = Array();
    // удаляем скрытые
	Ext6.each(columns, function(column) {
		if (column.hidden != true && column.hiddenPrint != true && column.dataIndex)
		{
			processed_columns.push(column);
		}
    }, this);

	if (addNumberColumn) {
		processed_columns.unshift({dataIndex: 'PrintNum', header: '№ п/п'});
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

			Ext6.each(processed_columns, function(column) {
				if (column.dataIndex == key) {
					convertedData[key] = column.renderer ? column.renderer(value,null,item) : value;
					if (!Ext6.isEmpty(convertedData[key]) && key!='PrintNum') { isEmpty = false; }
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
    var headings = Ext6.ux.GridPrinter.headerTpl.apply(processed_columns);
    var body = Ext6.ux.GridPrinter.bodyTpl.apply(processed_columns);

	if ( page_title == "" )
	{
		if ( grid.title == undefined )
		{
			page_title = "Печать списка";
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

    var html = new Ext6.XTemplate(
      '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
      '<html>',
        '<head>',
          '<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />',
          '<link href="' + Ext6.ux.GridPrinter.stylesheetPath + '" rel="stylesheet" type="text/css" media="screen,print" />',
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
* @type Ext6.XTemplate
* The XTemplate used to create the headings row. By default this just uses <th> elements, override to provide your own
*/
  headerTpl: new Ext6.XTemplate(
    '<tr>',
      '<tpl for=".">',
        '<th>{text}</th>',
      '</tpl>',
    '</tr>'
  ),

   /**
* @property bodyTpl
* @type Ext6.XTemplate
* The XTemplate used to create each row. This is used inside the 'print' function to build another XTemplate, to which the data
* are then applied (see the escaped dataIndex attribute here - this ends up as "{dataIndex}")
*/
  bodyTpl: new Ext6.XTemplate(
    '<tr>',
      '<tpl for=".">',
        '<td>\{{dataIndex}\}</td>',
      '</tpl>',
    '</tr>'
  )
};