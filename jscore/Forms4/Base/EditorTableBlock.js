Ext6.define('base.EditorTableBlock', {
	extend: 'Ext6.Component',
	xtype: 'editortableblock',

	editor: null,
	isReadOnly: false,

	baseCls: 'editor-table-block',

	tableTpl: [
		'<table class="mce-item-table" style="',
			'box-sizing: border-box; min-width: {minWidth}px; min-height: {minHeight}px; max-width: {maxWidth}px; border-collapse: collapse;',
		'">',
			'<tbody><tpl for="rows">{.}</tpl></tbody>',
		'</table>',
		'<div id="{id}-column-resizer" style="position: absolute; display: none; cursor: col-resize; padding: 0 {resizerOffset}px;">',
			'<div id="{id}-column-resizer-inner" style="opacity: 0; height: 100%; border-right: solid 1px rgb(33, 150, 243);"></div>',
		'</div>'
	],

	rowTpl: [
		'<tr><tpl for="cells">{.}</tpl></tr>'
	],

	cellTpl: [
		'<td class="{classes}" x="{x}" y="{y}" colspan="{colspan}" rowspan="{rowspan}" style="',
			'width: {widthPercents}%; padding: 5px; vertical-align: top',
		'">{content}</td>'
	],

	containerTpl: [
		'<div id="{id}-container" class="{baseCls}-container editor-block-container"></div>'
	],

	resizeColumnIndex: -1,

	inheritableStatics: {
		insertToEditor: function(editor, size) {
			var blockClass = this;

			var block = new blockClass({
				editor: editor,
				size: size
			});

			editor.undoManager.transact(function() {
				editor.mce.selection.setContent(block.getContainerHtml());
				block.renderToContainer();
			});

			editor.setCursorLocation(block.getCell(1,1).dom);

			return [block];
		},

		factory: function(editor, template) {
			var blockClass = this;
			var blocks = [];
			template = template || editor.getTemplate();

			var dom = document.createElement('div');
			dom.insertAdjacentHTML('afterbegin', template);
			editor.forEachNode(dom.querySelectorAll('table'), function(el){
				var block = new blockClass({
					editor: editor,
					html: el.outerHTML
				});

				blocks.push(block);
			});

			return blocks;
		}
	},

	getPlace: function() {
		var me = this;
		return me.html;
	},

	getContainerHtml: function() {
		var me = this;
		return me.containerTpl.apply({
			id: me.id, baseCls: me.baseCls
		});
	},

	getContainerEl: function() {
		var me = this;
		return me.el.up('#'+me.getId()+'-container');
	},

	getTable: function() {
		var me = this;
		if (!me.rendered) return null;
		return me.el.down('table');
	},

	getColumnResizer: function() {
		var me = this;
		if (!me.rendered) return null;
		return me.el.down('#'+me.id+'-column-resizer');
	},

	getCell: function(x, y) {
		var me = this;
		if (!me.rendered) return null;
		var cell = me.el.down('td[x="'+x+'"][y="'+y+'"]');
		if (!cell) {
			cell = me.el.down('tr:nth-child('+y+') td:nth-child('+x+')');
		}
		return cell;
	},

	getCellContent: function(x, y) {
		var me = this;
		var cell = null;
		if (x instanceof HTMLElement) {
			cell = Ext6.get(x);
		} else if (x instanceof Ext6.Element) {
			cell = x;
		} else {
			cell = me.getCell(x, y);
		}
		return cell?cell.getHtml().trim():null;
	},

	setCellContent: function(x, y, content) {
		var me = this;
		if (x <= me.size.x && y <= me.size.y) {
			me.getCell(x, y).setHtml(content);
			me.editor.undoManager.add();
			me.editor.onContentChange();
		}
	},

	getColumnCells: function(x) {
		var me = this;
		var cells = [];
		for (var y = 1; y <= me.size.y; y++) {
			cells.push(me.getCell(x, y));
		}
		return cells;
	},

	getRowCells: function(y) {
		var me = this;
		var cells = [];
		for (var x = 1; x <= me.size.x; x++) {
			var cell = me.getCell(x, y);
			if (cell) cells.push(me.getCell(x, y));
		}
		return cells;
	},

	getCurrentCell: function() {
		var me = this;
		var selection = me.editor.mce.selection;
		var cell = Ext6.fly(selection.getNode());

		if (!me.el.contains(cell)) {
			return null;
		}
		if (cell.dom.tagName == 'TR') {
			cell = cell.down('td');
		}
		if (cell.dom.tagName != 'TD' && cell.up('td')) {
			cell = cell.up('td');
		}

		return cell;
	},

	getCurrentRowNumber: function() {
		var me = this;
		var cell = me.getCurrentCell();
		return cell?cell.getAttribute('y'):null;
	},

	getCurrentColumnNumber: function() {
		var me = this;
		var cell = me.getCurrentCell();
		return cell?cell.getAttribute('x'):null;
	},

	getTableProperties: function() {
		var me = this;
		var table = me.getTable();
		var currentCell = me.getCurrentCell();
		var tableWidth = me.getWidth();
		var x, y;

		var percents = function(width) {
			return (width/tableWidth) * 100;
		};

		var columns = table.query('tr:first-of-type > td', false).reduce(function(count, cell) {
			return count + Number(cell.getAttribute('colspan') || 1);
		}, 0);

		var rows = table.query('tbody:first-of-type > tr > td:first-of-type', false).reduce(function(count, cell) {
			if (cell.getAttribute('x') > 1) return count;
			return count + Number(cell.getAttribute('rowspan') || 1);
		}, 0);

		var properties = {
			columns: columns,
			rows: rows,
			width: table.getWidth(),
			height: table.getHeight(),
			align: table.getAttribute('align') || 'left',
			selected: null,
			cells: []
		};

		var spanList = [];

		for (y = 1; y <= properties.rows; y++) {
			var cellsByRow = [];

			for (x = 1; x <= properties.columns; x++) {
				var cellEl = me.getCell(x, y);

				var isSpanned = spanList.some(function(span) {
					return (!cellEl || (
						x >= span[0] && x <= span[2] &&
						y >= span[1] && y <= span[3]
					));
				});

				if (isSpanned) {
					cellsByRow.push({spanned: true});
					continue;
				}

				cellEl.set({x: x, y: y});

				var colspan = Number(cellEl.getAttribute('colspan')) || 1;
				var rowspan = Number(cellEl.getAttribute('rowspan')) || 1;

				if (colspan > 1 || rowspan > 1) {
					spanList.push([x, y, x + colspan - 1, y + rowspan - 1]);
				}

				cellsByRow.push({
					content: cellEl.getHtml().trim(),
					width: percents(cellEl.getWidth()),
					colspan: colspan,
					rowspan: rowspan
				});
			}
			properties.cells.push(cellsByRow);
		}

		if (currentCell) properties.selected = {
			x: currentCell.getAttribute('x'),
			y: currentCell.getAttribute('y')
		};

		return properties;
	},

	openProperites: function() {
		var me = this;
		var params = {};
		var properties = me.getTableProperties();

		params.formParams = {
			columns: properties.columns,
			rows: properties.rows,
			align: properties.align
		};

		params.callback = function(data) {
			me.updateTable(Ext6.apply(properties, data));
		};

		me.toolsPanel.hide();
		getWnd('swTablePropertiesWindow').show(params);
	},

	renderTable: function(cellsProperties) {
		var me = this;

		var defaultWidthCell = 190;

		me.minWidthCell = 45;
		me.minHeightCell = 16;

		var minWidth = me.size.x * defaultWidthCell;
		if (minWidth >= 765) {
			minWidth = 765;
		}
		var minHeight = me.minHeightCell;
		var maxHeight = me.size.y * me.minHeightCell;

		var maxWidth = 765;

		var widthPercents = 100 / me.size.x;

		me.resizerOffset = 5;

		var getCellProperties = function(x, y) {
			var cellProperties = {content: '<br>', width: widthPercents};
			if (cellsProperties && cellsProperties[y-1] && cellsProperties[y-1][x-1]) {
				cellProperties = cellsProperties[y-1][x-1];
			}
			return cellProperties;
		};

		var x, y;
		var rows = [];

		for(y = 1; y <= me.size.y; y++) {
			var cells = [];

			for(x = 1; x <= me.size.x; x++) {
				cellProperties = getCellProperties(x, y);
				if (cellProperties.spanned) {
					continue;
				}

				var colspan = cellProperties.colspan || 1;
				var rowspan = cellProperties.rowspan || 1;

				var classes = [];
				if (x == 1) {
					classes.push('table-border-left');
				}
				if (y == 1) {
					classes.push('table-border-top');
				}
				if (x + cellProperties.colspan - 1 == me.size.x) {
					classes.push('table-border-right');
				}
				if (y + cellProperties.rowspan - 1 == me.size.y) {
					classes.push('table-border-bottom');
				}

				cells.push(me.cellTpl.apply({
					id: me.id, x: x, y: y,
					classes: classes.join(' '),
					widthPercents: cellProperties.width,
					content: cellProperties.content,
					colspan: colspan,
					rowspan: rowspan
				}));
			}

			rows.push(me.rowTpl.apply({
				id: me.id, y: y,
				cells: cells
			}));
		}

		return me.tableTpl.apply({
			id: me.id,
			minHeight: minHeight,
			maxHeight: maxHeight,
			minWidth: minWidth,
			maxWidth: maxWidth,
			resizerOffset: me.resizerOffset,
			rows: rows
		});
	},

	renderInnerBlocks: function() {
		var me = this;
		var blockClasses = [
			common.XmlTemplate.EditorSpecMarkerBlock
		];
		var renderBlock = function(blocks, block) {
			try {
				blocks = blocks.concat(block.renderToContainer());
			} catch(e) {
				log('Block not rendered: '+e.message, block);
			}
			return blocks;
		};

		var cells = me.getTable().query('tbody:first-of-type > tr > td', false);

		var createBlocksByCell = function(cell) {
			var content = cell.getHtml().trim();

			var blocks = blockClasses.reduce(function(blocks, blockClass) {
				return blocks.concat(blockClass.factory(me.editor, content));
			}, []);

			cell.setHtml(blocks.reduce(function(content, block) {
				return content.replace(block.getPlace(), block.getContainerHtml());
			}, content));

			return blocks;
		};

		var blocks = [].concat.apply([], cells.map(createBlocksByCell));
		return blocks.reduce(renderBlock, []);
	},

	renderToContainer: function() {
		var me = this;
		var container = Ext6.get(me.id+'-container');

		if (me.html) {
			me.render(container);
			me.updateTable(null, true);
		} else {
			me.html = me.renderTable();
			me.render(container);
		}

		return [me].concat(me.renderInnerBlocks());
	},

	addRow: function(place) {
		var me = this;
		var properties = me.getTableProperties();

		if (!properties.selected) return;

		var currentCell = me.getCell(properties.selected.x, properties.selected.y);
		var currentIndex = properties.selected.y - 1;
		var cells = properties.cells;
		var rowCells = cells[currentIndex];

		var newRowCells = rowCells.map(function(cell) {
			return {content: '<br/>', width: cell.width};
		});

		if (place == 'before') {
			cells.splice(currentIndex, 0, newRowCells);
		} else {
			cells.splice(currentIndex+1, 0, newRowCells);
			properties.selected.y++;
		}

		properties.rows++;
		properties.height += currentCell.getHeight();

		me.updateTable(properties);
		me.showToolsPanel();
	},

	deleteRow: function() {
		var me = this;
		var properties = me.getTableProperties();

		if (!properties.selected) return;

		var currentCell = me.getCell(properties.selected.x, properties.selected.y);
		var currentIndex = properties.selected.y - 1;
		var cells = properties.cells;

		cells.splice(currentIndex, 1);
		properties.rows--;
		properties.selected.y--;
		properties.height -= currentCell.getHeight();

		me.updateTable(properties);
		me.showToolsPanel();
	},

	addColumn: function(place) {
		var me = this;
		var properties = me.getTableProperties();

		if (!properties.selected) return;

		var currentCell = me.getCell(properties.selected.x, properties.selected.y);
		var currentIndex = properties.selected.x - 1;
		var cells = properties.cells;
		var newCell = {content: '<br/>', width: cells[0][properties.selected.x]};

		if (place == 'before') {
			cells.forEach(function(rowCells) {
				rowCells.splice(currentIndex, 0, newCell);
			});
		} else {
			cells.forEach(function(rowCells) {
				rowCells.splice(currentIndex+1, 0, newCell);
			});
			properties.selected.x++;
		}

		properties.columns++;
		properties.width += newCell.width;

		me.updateTable(properties);
		me.showToolsPanel();
	},

	deleteColumn: function() {
		var me = this;
		var properties = me.getTableProperties();

		if (!properties.selected) return;

		var currentCell = me.getCell(properties.selected.x, properties.selected.y);
		var currentIndex = properties.selected.x - 1;
		var cells = properties.cells;

		cells.forEach(function(rowCells) {
			rowCells.splice(currentIndex, 1);
		});
		properties.columns--;
		//properties.selected.x--;
		properties.width -= currentCell.getWidth();

		me.updateTable(properties);
		me.showToolsPanel();
	},

	mergeCells: function() {
		var me = this;

		var selectedCells = me.el.query('td.focus', false);

		var coordsList = selectedCells.map(function(cell) {
			return {
				startX: Number(cell.getAttribute('x')),
				startY: Number(cell.getAttribute('y')),
				endX: Number(cell.getAttribute('x')) + Number(cell.getAttribute('colspan')) - 1,
				endY: Number(cell.getAttribute('y')) + Number(cell.getAttribute('rowspan')) - 1
			};
		}).sort(function(a, b) {
			var aIndex = [a.endX,a.endY].join(',');
			var bIndex = [b.endX,b.endY].join(',');
			return (aIndex < bIndex)?-1:1;
		});

		var firstCoords = coordsList[0];
		var lastCoords = coordsList[coordsList.length - 1];

		var firstCell = me.getCell(firstCoords.startX, firstCoords.startY);
		var lastCell = me.getCell(lastCoords.startX, lastCoords.startY);

		var startX = firstCoords.startX;
		var startY = firstCoords.startY;
		var endX = lastCoords.endX;
		var endY = lastCoords.endY;

		var colspan = endX - startX + 1;
		var rowspan = endY - startY + 1;

		var contentArr = selectedCells.map(function(cell) {
			var content = me.getCellContent(cell);
			return (!Ext6.isEmpty(content) && content != '<br>')?content:'';
		});

		me.editor.undoManager.transact(function() {
			for (var y = startY; y <= endY; y++) {
				for(var x = startX; x <= endX; x++) {
					var cell = me.getCell(x, y);
					if (cell && cell != firstCell) {
						cell.remove();
					}
				}
			}

			me.setCellContent(startX, startY, contentArr.join(''));

			if (startX + colspan - 1 == me.size.x) {
				firstCell.addCls('table-border-right');
			}
			if (startY + rowspan - 1 == me.size.y) {
				firstCell.addCls('table-border-bottom');
			}

			firstCell.set({
				colspan: colspan,
				rowspan: rowspan
			});

			firstCell.addCls('focus');
		});

		me.editor.onContentChange();
	},

	updateTable: function(properties, force) {
		var me = this;
		var table = me.getTable();
		var x, y;

		if (!properties) {
			properties = me.getTableProperties();
		}

		if (!me.size) me.size = {
			x: properties.columns,
			y: properties.rows
		};

		if (force ||
			(properties.columns && properties.columns != me.size.x) ||
			(properties.rows && properties.rows != me.size.y)
		) {
			me.size.x = properties.columns;
			me.size.y = properties.rows;
			me.setHtml(me.renderTable(properties.cells));
			table = me.getTable();

			var cell = null;
			if (properties.selected) {
				cell = me.getCell(properties.selected.x, properties.selected.y);
				if (!cell) cell = me.getCell(properties.selected.x, 1);
				if (!cell) cell = me.getCell(1, properties.selected.y);
			}
			if (!cell) cell = me.getCell(1, 1);

			if (!force && cell) {
				me.editor.setCursorLocation(cell.dom);
			}

			me.initListeners();
		}

		if (properties.width) {
			table.setWidth(properties.width);
		}
		if (properties.height) {
			table.setHeight(properties.height);
		}

		if (properties.align) {
			table.set({align: properties.align});
		}

		me.calculateTableColumns();
		me.editor.undoManager.add();
		me.editor.onContentChange();
	},

	trackColumnResizer: function(e) {
		var me = this;
		var coords = {x: e.clientX, y: e.clientY};
		var table = me.getTable();
		var resizer = me.getColumnResizer();

		if (me.isReadOnly) {
			resizer.setStyle('display', 'none');
			return;
		}

		var offset = {
			x: me.editor.getEditorEl().getLeft(),
			y: me.editor.getEditorEl().getTop()
		};

		var tableTop = table.getTop();
		var tableBottom = table.getBottom();
		var cells = table.query('* > tbody > tr:first-of-type > td:not(:last-of-type)', false);

		var rectList = cells.map(function(cell) {
			var cellRight = cell.getRight();
			return {
				top: tableTop,
				bottom: tableBottom,
				left: cellRight - me.resizerOffset,
				right: cellRight + me.resizerOffset
			};
		});

		var coordsIsInRect = function(rect){
			return (
				coords.x >= rect.left &&
				coords.x <= rect.right &&
				coords.y >= rect.top &&
				coords.y <= rect.bottom
			);
		};

		me.resizeColumnIndex = rectList.findIndex(coordsIsInRect);

		if (me.resizeColumnIndex >= 0) {
			var rect = rectList[me.resizeColumnIndex];
			resizer.setStyle('display', 'block');
			resizer.setLeft(rect.left - offset.x - 1);
			resizer.setTop(rect.top - offset.y);
			resizer.setHeight(rect.bottom - rect.top);
		} else {
			resizer.cells = null;
			resizer.setStyle('display', 'none');
			resizer.setLeft(0);
			resizer.setTop(0);
			resizer.setHeight(0);
		}
	},

	setIsColumnResizing: function(flag) {
		this.isColumnResizing = flag;
	},

	startColumnResizing: function(e) {
		var me = this;

		if (me.isReadOnly) {
			return;
		}

		var resizer = me.getColumnResizer();
		var columnNumber = me.resizeColumnIndex + 1;
		var x, y;

		me.setIsColumnResizing(true);

		resizer.down('div').setStyle('opacity', 1);
	},

	processColumnResizing: function(e) {
		var me = this;
		var table = me.getTable();
		var resizer = me.getColumnResizer();

		if (!me.isColumnResizing || me.resizeColumnIndex < 0) {
			return;
		}

		var columnNumber = me.resizeColumnIndex + 1;

		var offset = {
			x: me.editor.getEditorEl().getLeft(),
			y: me.editor.getEditorEl().getTop()
		};

		var currCell = me.getCell(columnNumber, 1);
		var nextCell = me.getCell(columnNumber + 1, 1);

		var left = currCell.getLeft();
		var widthCells = currCell.getWidth() + nextCell.getWidth();

		var width = Math.round(e.clientX - left);

		if (width < me.minWidthCell) {
			width = me.minWidthCell;
		}
		if (width > Math.round(widthCells - me.minWidthCell)) {
			width = Math.round(widthCells - me.minWidthCell);
		}

		me.resizeWidth = width;

		resizer.setLeft(Math.round(width + left - offset.x - me.resizerOffset));
	},

	stopColumnResizing: function(e) {
		var me = this;
		var table = me.getTable();
		var resizer = me.getColumnResizer();

		if (!me.isColumnResizing || me.resizeColumnIndex < 0) {
			return;
		}

		var columnIndex = me.resizeColumnIndex;
		var columnNumber = me.resizeColumnIndex + 1;
		var width = me.resizeWidth;
		var tableWidth = table.getWidth();

		me.setIsColumnResizing(false);
		me.resizeWidth = null;
		//me.resizeColumnIndex = -1;
		resizer.down('div').setStyle('opacity', 0);

		var currCell = me.getCell(columnNumber, 1);
		var nextCell = me.getCell(columnNumber + 1, 1);

		var widthCells = currCell.getWidth() + nextCell.getWidth();

		var width1 = widthCells - width;

		var widthPercents = (width/tableWidth) * 100;
		var widthPercents1 = (width1/tableWidth) * 100;

		if (widthPercents > 0) {
			table.query('* > tbody > tr', false).forEach(function(row) {
				row.query('* > td', false).forEach(function(cell, index) {
					if (index == columnIndex) {
						cell.setWidth(widthPercents + '%');
					}
					if (index == columnIndex + 1) {
						cell.setWidth(widthPercents1 + '%');
					}
				});
			});
		}
	},

	calculateTableColumns: function() {
		var me = this;
		var cells = me.getRowCells(1);
		var tableWidth = me.getTable().getWidth();

		var percents = function(width) {
			return (width/tableWidth) * 100;
		};

		var minWidthPercents = percents(me.minWidthCell);

		var columnsWidth = [];

		cells.forEach(function(curr, index) {
			var next = cells[index + 1];

			var currWidth = curr.getWidth();
			var nextWidth = next?next.getWidth():0;

			var sumWidthPercents = percents(currWidth) + percents(nextWidth);

			if (currWidth < me.minWidthCell) {
				curr.setWidth(minWidthPercents+'%');
				if (next) {
					next.setWidth((sumWidthPercents-minWidthPercents)+'%');
				}
			}

			columnsWidth[index] = percents(curr.getWidth());
		});

		for (var y = 2; y <= me.size.y; y++) {
			me.getRowCells(y).forEach(function(cell, index) {
				cell.setWidth(columnsWidth[index]+'%');
			});
		}
	},

	onTableResized: function(e) {
		var me = this;
		me.calculateTableColumns();
		me.showToolsPanel();
	},

	showToolsPanel: function(e) {
		var me = this;
		var table = me.getTable();

		if (e)e.stopEvent();

		if (me.isReadOnly) {
			return false;
		}

		me.toolsPanel.show({
			target: table, align: 'tr-br', offset: [0, 0]
		});
	},

	hideToolsPanel: function() {
		var me = this;
		me.toolsPanel.hide();
	},

	onRender: function() {
		var me = this;
		me.callParent(arguments);
		me.setReadOnly(me.isReadOnly);
		me.initListeners();
	},

	onDestroy: function() {
		var me = this;
		me.toolsPanel.hide();
		me.callParent(arguments);

		if (me.getContainerEl()) {
			me.getContainerEl().remove();
		}

		var index = me.editor.blocks.indexOf(me);

		if (index >= 0) {
			me.editor.blocks.splice(index, 1);
		}

		me.editor.getUndoManager().add();
		me.editor.onContentChange();
	},

	onMouseDown: function(e) {
		var me = this;
		if (e.target.tagName == 'TD') {
			var currentCell = e.target;

			me.startSelectCell = {
				x: Number(currentCell.getAttribute('x')),
				y: Number(currentCell.getAttribute('y')),
				colspan: Number(currentCell.getAttribute('colspan')),
				rowspan: Number(currentCell.getAttribute('rowspan'))
			};
			me.lastSelectCell = null;

			me.el.query('td.focus').forEach(function(cell) {
				cell.classList.remove('focus');
			});

			currentCell.classList.add('focus');

			me.editor.setCursorLocation(currentCell);
		}
	},

	onMouseUp: function(e) {
		var me = this;
		if (me.startSelectCell) {
			me.trackSelectCells();
			me.startSelectCell = null;
			me.lastSelectCell = null;
			me.showToolsPanel();
		}
	},

	trackSelectCells: function(e) {
		var me = this;

		if (e) me.lastSelectCell = {
			x: Number(e.target.getAttribute('x')),
			y: Number(e.target.getAttribute('y')),
			colspan: Number(e.target.getAttribute('colspan')),
			rowspan: Number(e.target.getAttribute('rowspan'))
		};

		if (me.startSelectCell && me.lastSelectCell) {
			var arr = [{
				x: me.startSelectCell.x,
				y: me.startSelectCell.y
			}, {
				x: me.startSelectCell.x + me.startSelectCell.colspan - 1,
				y: me.startSelectCell.y + me.startSelectCell.rowspan - 1
			}, {
				x: me.lastSelectCell.x,
				y: me.lastSelectCell.y
			}, {
				x: me.lastSelectCell.x + me.lastSelectCell.colspan - 1,
				y: me.lastSelectCell.y + me.lastSelectCell.rowspan - 1
			}];

			var getCoords = function() {
				return {
					startX: Math.min.apply(null, arr.map(a => a.x)),
					startY: Math.min.apply(null, arr.map(a => a.y)),
					endX: Math.max.apply(null, arr.map(a => a.x)),
					endY: Math.max.apply(null, arr.map(a => a.y))
				};
			};

			me.el.query('td', false).forEach(function(cell) {
				var x = Number(cell.getAttribute('x'));
				var y = Number(cell.getAttribute('y'));
				var colspan = Number(cell.getAttribute('colspan'));
				var rowspan = Number(cell.getAttribute('rowspan'));

				var x1 = x + colspan - 1;
				var y1 = y + rowspan - 1;

				var coords = getCoords();

				if (coords.startX <= x1 && coords.endX >= x) {
					if (coords.startY <= y1 && coords.endY >= y) {
						arr.push({x: x, y: y});
						arr.push({x: x1, y: y1});
					}
				}
			});

			var coords = getCoords();

			me.el.query('td', false).forEach(function(cell) {
				var x = Number(cell.getAttribute('x'));
				var y = Number(cell.getAttribute('y'));
				var colspan = Number(cell.getAttribute('colspan'));
				var rowspan = Number(cell.getAttribute('rowspan'));

				var x1 = x + colspan - 1;
				var y1 = y + rowspan - 1;

				var selected = false;

				for (var _y = y; _y <= y1; _y++) {
					for (var _x = x; _x <= x1; _x++) {
						if (_x >= coords.startX && _x <= coords.endX &&
							_y >= coords.startY && _y <= coords.endY
						) {
							selected = true;
							break;
						}
					}
					if (selected) {
						break;
					}
				}

				cell.dom.classList.toggle('focus', selected);
			});
		}
	},

	setReadOnly: function(isReadOnly) {
		var me = this;
		me.isReadOnly = isReadOnly;

		if (me.isReadOnly) {
			me.getEl().addCls('readonly');
		} else {
			me.getEl().removeCls('readonly');
		}

		if (me.isReadOnly) {
			me.hideToolsPanel();
		}
	},

	initListeners: function() {
		var me = this;

		if (!me.getTable() || !me.getColumnResizer()) {
			return;
		}

		me.getTable().on({
			mousemove: me.trackColumnResizer,
			mousedown: me.onMouseDown,
			mouseup: me.onMouseUp,
			scope: me
		});

		me.getTable().on({
			mouseenter: me.trackSelectCells,
			delegate: 'td',
			scope: me
		});

		me.getColumnResizer().on({
			mousedown: me.startColumnResizing,
			mousemove: me.processColumnResizing,
			mouseup: me.stopColumnResizing,
			scope: me
		});
	},

	initComponent: function() {
		var me = this;

		if (!(me.editor instanceof base.EditorPanel)) {
			throw new Error('Not defined editor');
		}

		me.tableTpl = new Ext6.XTemplate(me.tableTpl);
		me.rowTpl = new Ext6.XTemplate(me.rowTpl);
		me.cellTpl = new Ext6.XTemplate(me.cellTpl);
		me.containerTpl = new Ext6.XTemplate(me.containerTpl);

		var ParamsToolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			cls: 'editor-table-block-toolbar',
			defaults: {
				width: 16,
				height: 16
			},
			items: [{
				iconCls: 'icon-table-row-add-top',
				tooltip: 'Добавить строку сверху',
				margin: '4 2 4 4',
				style:{
					top: '4px !important'
				},
				handler: function() {
					me.addRow('before');
				}
			}, {
				iconCls: 'icon-table-row-add-bottom',
				tooltip: 'Добавить строку снизу',
				margin: '4 2',
				handler: function() {
					me.addRow('after');
				}
			}, {
				iconCls: 'icon-table-row-delete',
				tooltip: 'Удалить строку',
				handler: function() {
					me.deleteRow();
				},
				margin: '4 4 4 2',
			}, {
				xtype: 'tbseparator',
				margin: 0,
				height: 24,
				width: 1
			}, {
				iconCls: 'icon-table-column-add-right',
				tooltip: 'Добавить столбец справа',
				handler: function() {
					me.addColumn('after');
				},
				margin: '4 2 4 4'
			}, {
				iconCls: 'icon-table-column-add-left',
				tooltip: 'Добавить столбец слева',
				handler: function() {
					me.addColumn('before');
				},
				margin: '4 2 4 2'
			}, {
				iconCls: 'icon-table-column-delete',
				tooltip: 'Удалить столбец',
				handler: function() {
					me.deleteColumn();
				},
				margin: '4 4 4 2'
			}, {
				xtype: 'tbseparator',
				margin: 0,
				height: 24,
				width: 1
			}, 	{
				iconCls: 'icon-table-combine-cell',
				tooltip: 'Объеденить ячейки',
				handler: function () {
					me.mergeCells();
				},
				margin: '4 4 4 4'
			}, {
				xtype: 'tbseparator',
				margin: 0,
				height: 24,
				width: 1
			}, {
				iconCls: 'icon-table-up',
				tooltip: 'Перместить выше',
				disabled: true,
				handler: function () {},
				margin: '4 2 4 4'
			}, {
				iconCls: 'icon-table-down',
				tooltip: 'Перместить ниже',
				disabled: true,
				handler: function () {},
				margin: '4 2 4 2'
			}, 	{
				iconCls: 'icon-table-properties',
				tooltip: 'Свойства таблицы',
				handler: function() {
					me.openProperites();
				},
				margin: '4 2 4 2'
			}, {
				iconCls: 'icon-table-delete',
				tooltip: 'Удалить таблицу',
				handler: function() {
					me.toolsPanel.hide();
					me.destroy();
				},
				margin: '4 4 4 2'
			}]
		});

		me.toolsPanel = Ext6.create('base.DropdownPanel', {
			autoSize: true,
			resizable: false,
			shadow: false,
			minWidth: 16,
			panel: ParamsToolbar,
		});

		me.callParent(arguments);

		me.editor.blocks.push(me);
	}
});