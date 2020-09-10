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

Ext.ux.GridPrinterVac = {
  /**
* Prints the passed grid. Reflects on the grid's column model to build a table, and fills it using the store
* @param {Ext.grid.GridPanel} grid The grid to print
* @param {Object} params Параметры: 'tableHeaderText' - текст в заголовке таблицы.
*									'pageTitle' - текст заголовка страницы.
*/
  print: function(grid, params) {	
	
	var table_header_text = "";
	var page_title = "";
	var table_footer_text = "";
	var notPrintEmptyRows = false;
	
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
		
		if ( params.tableFooterText )
		{
			table_footer_text = params.tableFooterText;
		}
	}
	
    //We generate an XTemplate here by using 2 intermediary XTemplates - one to create the header,
    //the other to create the body (see the escaped {} below)
    var columns = grid.getColumnModel().config;
	// колонки с удаленными скрытыми полями
	var processed_columns = Array();
    // удаляем скрытые
	Ext.each(columns, function(column) {
		if (column.hidden != true)
		{
			processed_columns.push(column);
		}
    }, this);

    //build a useable array of store data for the XTemplate
    var data = [];

    grid.store.data.each(function(item) {
      var convertedData = [];
	  var isEmpty = true;
	  
      //apply renderers from column model
      for (var key in item.data) {
        var value = item.data[key];
		if ( value == null )
			value = '';

        Ext.each(processed_columns, function(column) {
          if (column.dataIndex == key) {
            convertedData[key] = column.renderer ? column.renderer(value,null,item) : value;
			if (!Ext.isEmpty(convertedData[key])) { isEmpty = false; }
          }
        }, this);
      }
	  if (!notPrintEmptyRows || !isEmpty) {
		data.push(convertedData);
	  }
    });

    //use the headerTpl and bodyTpl XTemplates to create the main XTemplate below
    var headings = Ext.ux.GridPrinterVac.headerTpl.apply(processed_columns);
//		var footer = Ext.ux.GridPrinterVac.footerTpl.apply(processed_columns);
    var body = Ext.ux.GridPrinterVac.bodyTpl.apply(processed_columns);
	
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
//		var table_header = "<th colspan='" + processed_columns.length + "'><center><b>" + table_header_text + "</b></center></th>";
		table_header = "<center>" + table_header_text + "</center>";
	}
	
	// добавляем футер к таблице
	var table_footer = "";
	if ( table_footer_text != "" )
	{
//		var table_footer = "<th colspan='" + processed_columns.length + "'><center><b>" + table_footer_text + "</b></center></th>";
		table_footer = "<center>" + table_footer_text + "</center>";
	}

    var html = new Ext.XTemplate(
      '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
      '<html>',
        '<head>',
          '<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />',
          '<link href="' + Ext.ux.GridPrinterVac.stylesheetPath + '" rel="stylesheet" type="text/css" media="screen,print" />',
          '<title>' + page_title + '</title>',
        '</head>',
        '<body>',
				table_header,
          '<table>',
//						table_header,
            headings,
            '<tpl for=".">',
              body,
            '</tpl>',
//						table_footer,
          '</table>',
			table_footer,
//			footer,
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
  stylesheetPath: '/css/gridprintvac.css',

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
		
//  /**
//* @property footerTpl
//* @type Ext.XTemplate
//* The XTemplate used to create the headings row. By default this just uses <th> elements, override to provide your own
//*/
//  footerTpl: new Ext.XTemplate(
//    '<tr>',
//      '<tpl for=".">',
//        '<th>{header}</th>',
//      '</tpl>',
//    '</tr>'
//  ),

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
  )
};