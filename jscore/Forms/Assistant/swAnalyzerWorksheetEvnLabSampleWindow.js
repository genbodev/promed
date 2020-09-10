/**
* swAnalyzerWorksheetEvnLabSampleWindow - окно редактирования проб рабочего списка
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       A. Markoff
* @version      2012/12
* @comment
*/

sw.Promed.swAnalyzerWorksheetEvnLabSampleWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	id: 'swAnalyzerWorksheetEvnLabSampleWindow',
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	layout: 'form',
	width: 980,
	titleWin: langs('Рабочий список'),
	autoHeight: true,
	maxColumns: 30,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var that = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					that.findById('AnalyzerWorksheetEvnLabSampleForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        this.submit();
		return true;
	},
	submit: function() {
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = new Object();
		params.action = that.action;
		this.form.submit({
			params: params,
			failure: function(result_form, action)
			{
				loadMask.hide();
				if (action.result)
				{
					if (action.result.Error_Code)
					{
						Ext.Msg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				loadMask.hide();
				that.callback(that.owner, action.result.MorbusCrazyDiag_id);
				that.hide();
			}
		});
	},
	samplesAdd: function () {
		var win = this;
		getWnd('swAnalyzerWorksheetEvnLabSampleEditWindow').show({
			AnalyzerWorksheet_id: win.AnalyzerWorksheet_id,
			MedService_id: win.MedService_id,
			action: 'add',
			callback: function(ct, id) {
				// после добавления обновляем матрицу
				this.grid.loadData();
			}.createDelegate(this)
		})
	},
	sampleDelete: function() {
		var grid = this.grid;
		var data = grid.getSelectedCellData(); // получаем данные по ячейке
		if (!data) return;
		var record = grid.getGrid().getSelectionModel().getSelected();
		if (!record) return;
		if (record.get(data.cellName)) {
			var params = {};
			// данные есть, можно удалять
			//params.AnalyzerWorksheetEvnLabSample_id = record.get('AnalyzerWorksheetEvnLabSample_id'+data.colNumber);
			params.id = record.get('AnalyzerWorksheetEvnLabSample_id'+data.colNumber);
			params.object = 'AnalyzerWorksheetEvnLabSample';
			params.scheme = 'lis';
			// далее удаляем эту запись и обновляем грид
			Ext.Ajax.request({
				url: C_RECORD_DEL,
				params: params,
				failure: function(response, options) {
					//Ext.Msg.alert('Ошибка', 'При удалении произошла ошибка!');
				},
				success: function(response, action) {
					if (response.responseText) {
						record.set(data.cellName, '');
						record.commit();
						// todo: правильно наверное вызывать onCellSelect для того чтобы дисаблить акшен (или даже fireEvent)
						grid.setActionDisabled('action_delete', true);
						//grid.getGrid().getStore().reload();
					} else {
						Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при удалении! Отсутствует ответ сервера.'));
					}
				}
			});
		}
	},
	showInfoPanel: function(data) {
		if (!data.AnalyzerWorksheet_setDT) {
			data.AnalyzerWorksheet_setDT = langs('Сегодня');
		}
		this.TextTpl.overwrite(this.TextPanel.body, data);
		this.TextPanel.render();
		this.syncShadow();
	},
	show: function() {
        var that = this;
		sw.Promed.swAnalyzerWorksheetEvnLabSampleWindow.superclass.show.apply(this, arguments);
		this.tabPanel.setActiveTab(0);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.AnalyzerWorksheet_id = null;
		this.arguments = null;
		
        if ( !arguments[0] || !arguments[0].MedService_id ) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { that.hide(); });
            return false;
        } else {
			this.arguments = arguments[0];
		}
		
		this.MedService_id = arguments[0].MedService_id;
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		// рабочий список
		if ( arguments[0].AnalyzerWorksheet_id ) {
			this.AnalyzerWorksheet_id = arguments[0].AnalyzerWorksheet_id;
		}
		// размерность
		if ( arguments[0].AnalyzerRack_DimensionX ) {
			this.AnalyzerRack_DimensionX = arguments[0].AnalyzerRack_DimensionX;
		}
		if ( arguments[0].AnalyzerRack_DimensionY ) {
			this.AnalyzerRack_DimensionY = arguments[0].AnalyzerRack_DimensionY;
		}
		// статус рабочего списка
		if ( arguments[0].AnalyzerWorksheetStatusType_id ) {
			this.AnalyzerWorksheetStatusType_id = arguments[0].AnalyzerWorksheetStatusType_id;
		}
		// тип рабочего списка // анализатор

		// название и код рабочего списка
		this.form = this.formpanel.getForm();

		this.showInfoPanel(arguments[0]);

		this.addMatrixActions();

		// отображаем нужное количество колонок
		this.initMatrixSamples(this.AnalyzerRack_DimensionX);

		this.form.reset();
		// режим открытия формы по умолчанию
		this.action = (arguments[0].action)?arguments[0].action:'add';
		// если статус "новый" то редактирование разрешено, если нет, то только просмотр списка
		this.action = (this.AnalyzerWorksheetStatusType_id==1)?this.action:'view';
		switch (this.action) {
			case 'add':
				this.setTitle(this.titleWin+langs(': Добавление'));
				break;
			case 'edit':
				this.setTitle(this.titleWin+langs(': Редактирование'));
				break;
			case 'view':
				this.setTitle(this.titleWin+langs(': Просмотр'));
				break;
		}
		this.grid.setReadOnly((this.action=='view')); // редактирование грида
		this.grid.setActionDisabled('action_clear', (this.action=='view'));
		this.grid.setActionDisabled('action_new', (this.AnalyzerWorksheetStatusType_id==1));
		this.grid.setActionDisabled('action_work', (this.AnalyzerWorksheetStatusType_id==2));
		this.grid.setActionDisabled('action_close', (this.AnalyzerWorksheetStatusType_id==3));
		this.getLoadMask().show();
		// загружаем данные грида всезависимости от режима формы
		this.grid.loadData({globalFilters: {AnalyzerWorksheet_id: this.AnalyzerWorksheet_id}, params: {AnalyzerWorksheet_id: this.AnalyzerWorksheet_id}});

		switch (this.action) {
			case 'add':
				that.form.setValues(arguments[0].formParams);
				that.getLoadMask().hide();
			break;
			case 'edit':
			case 'view':
				that.getLoadMask().hide();
			break;
		}
	},
	/**
	 * Очищает все ячейки матрицы штатива
	 */
	clearMatrix: function() {
		var params = {AnalyzerWorksheet_id: this.AnalyzerWorksheet_id};
		var grid = this.grid;
		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Очистить рабочий список?'),
			title: langs('Вопрос'),
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId) {
					Ext.Ajax.request({
						url: '/?c=AnalyzerWorksheetEvnLabSample&m=clearMatrix',
						params: params,
						failure: function(response, options) {
						},
						success: function(response, action) {
							if (response.responseText) {
								grid.loadData();
							} else {
								Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при удалении! Отсутствует ответ сервера.'));
							}
						}
					});
				}
			}
		});
	},
	addMatrixActions: function() {
		/*this.grid.addActions({
			name:'action_actions',
			iconCls: 'actions16',
			text:langs('Действия'),
			menu: new Ext.menu.Menu({
				id:'AnalyzerWorksheetEvnLabSampleAdditionalMenu',
				items: [{
					text: langs('Очистить все'),
					tooltip: langs('Очистить все ячейки штатива'),
					handler: function() {
						this.clearMatrix();

					}.createDelegate(this),
					iconCls: 'delete16'
				}]
			})
		});*/
		this.grid.addActions({
			name:'action_clear',
			iconCls: 'delete16',
			text: langs('Очистить все'),
			tooltip: langs('Очистить все ячейки штатива'),
			handler: function() {
				this.clearMatrix();
			}.createDelegate(this)
		});
	
		this.grid.addActions({
			name:'action_close',
			iconCls: 'archive16',
			text: langs('Закрыть'),
			tooltip: langs('Сменить статус рабочего списка на "закрыт"'),
			handler: function() {
				this.setStatus(3);
			}.createDelegate(this)
		});
		
		this.grid.addActions({
			name:'action_work',
			iconCls: 'archive16',
			text: langs('В работу'),
			tooltip: langs('Сменить статус рабочего списка на "в работе"'),
			handler: function() {
				this.setStatus(2);
			}.createDelegate(this)
		});
		
		this.grid.addActions({
			name:'action_new',
			iconCls: 'archive16',
			text: langs('Новый'),
			tooltip: langs('Сменить статус рабочего списка на "новый"'),
			handler: function() {
				this.setStatus(1);
			}.createDelegate(this)
		});
	},
	setStatus: function(status) {
		var wnd = this;
		
		var statusName = langs('Новый');
		
		switch (status) {
			case 1:
				statusName = langs('Новый');
				break
			case 2:
				statusName = langs('В работе');
				break
			case 3:
				statusName = 'закрыт';
				break
		}
		wnd.getLoadMask('Установка статуса "'+statusName+'"...').show();
		Ext.Ajax.request({
			params: {
				AnalyzerWorksheet_id: wnd.AnalyzerWorksheet_id,
				AnalyzerWorksheetStatusType_id: status
			},
			url: '/?c=AnalyzerWorksheet&m=setStatus',
			callback: function (options, success, response) {
				wnd.getLoadMask().hide();
				if (success) {
					wnd.arguments.AnalyzerWorksheetStatusType_Name = statusName;
					wnd.AnalyzerWorksheetStatusType_id = status;
					wnd.showInfoPanel(wnd.arguments);
					wnd.action = (wnd.AnalyzerWorksheetStatusType_id==1)?'edit':'view';
					wnd.grid.setReadOnly((wnd.action=='view')); // редактирование грида
					wnd.grid.setActionDisabled('action_clear', (wnd.action=='view'));
					wnd.grid.setActionDisabled('action_new', (wnd.AnalyzerWorksheetStatusType_id==1));
					wnd.grid.setActionDisabled('action_work', (wnd.AnalyzerWorksheetStatusType_id==2));
					wnd.grid.setActionDisabled('action_close', (wnd.AnalyzerWorksheetStatusType_id==3));
					wnd.setTitle(wnd.titleWin+langs(': Просмотр'));
				}
			}
		});
	},
	initMatrixSamples: function(count_x,count_y) {
		if (!this.grid) {
			return false;
		}
		if (this.grid.getColumnModel()) {
			var cm = this.grid.getColumnModel();
		} else {
			return false;
		}
		var width = this.getInnerWidth() - this.getFrameWidth()-32;

		for (i=1; i <= this.maxColumns; i++) {
			var index = cm.findColumnIndex('AnalyzerWorksheetEvnLabSample_X'+i);
			if (index>=0) {
				cm.setHidden(index, (i>count_x)); //открываем до count_x, скрываем остальные
				//cm.setColumnWidth(index, (width/count_x));
			}
		}
		this.grid.doLayout();
	},
	createMatrixSamples: function(count_x) {
		var that = this;
		var titles = [];
		var sf = [{name: 'id', type: 'int', header: 'ID', key: true}];
		sf.push({name: 'rownumberer', type:'rownumberer', header: ''});

		sf.push({name: 'AnalyzerWorksheet_id', hidden: true});
		//sf.push({name: 'y', header: '', width: 5, sortable: false});
		for (i=1; i <= count_x; i++) { // формируем колонки и заголовки
			titles[i] = i; // заголовки
			sf.push({name: 'AnalyzerWorksheetEvnLabSample_X'+i, header: i, width: 80, sortable: false, renderer: sw.Promed.Format.worksheetColumn});
			sf.push({name: 'AnalyzerWorksheetEvnLabSample_id'+i, hidden: true});
			sf.push({name: 'EvnLabSample_id'+i, hidden: true});
		}

		return new sw.Promed.ViewFrame({
			id: 'AnalyzerWorksheetEvnLabSampleGrid',
			region: 'center',
			height: 500,
			object: 'AnalyzerWorksheetEvnLabSample',
			border: true,
			dataUrl: '/?c=AnalyzerWorksheetEvnLabSample&m=loadMatrix',
			toolbar: true,
			autoLoadData: false,
			enableColumnHide: false,
			selectionModel: 'cell',
			forceFit: false,
			enableColumnMove: false,
			//hideHeaders: true,
			stringfields: sf,
			actions: [
				{name:'action_add', handler: function() {that.samplesAdd();}, text: langs('Добавить'), tooltip: langs('Добавить пробы')},
				{name:'action_edit', handler: function() {}.createDelegate(this), hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', handler: function() {this.sampleDelete();}.createDelegate(this),  text:langs('Очистить'), tooltip: langs('Очистить пробы')}
			],
			onCellDblClick: function(grid, rowIdx, colIdx, event) {
				that.samplesAdd();
			}.createDelegate(this),
            onKeyDown1: function (){
                var e = arguments[0][0];
                that.gridKeyboardInputSequence++;
                var s = that.gridKeyboardInputSequence;
                var pressed = String.fromCharCode(e.getCharCode());
                var alowed_chars = ['0','1','2','3','4','5','6','7','8','9'];
                if ((pressed != '') && (alowed_chars.indexOf(pressed) >= 0)) {
                    that.gridKeyboardInput = that.gridKeyboardInput + String.fromCharCode(e.getCharCode());
                    setTimeout(function () {
                        that.resetGridKeyboardInput(s);
                    }, 500);
                }
            },
            onEnter: function () {
                that.resetGridKeyboardInput(that.gridKeyboardInputSequence);
            },
            onKeyboardInputFinished: function (input) {
                var AnalyzerWorksheet_id = that.AnalyzerWorksheet_id;
                var grid = that.grid;
                var _this = this;
                var params = {};
                params.AnalyzerWorksheet_id = AnalyzerWorksheet_id;
                params.EvnLabSample_Num = input;
                params.MedService_id = that.MedService_id;

                if (grid.getGrid().getSelectionModel().getSelected()) {
                    var record = grid.getGrid().getSelectionModel().getSelected();
                    var data = grid.getSelectedCellData();
                    var AnalyzerWorksheetEvnLabSample_X = data.colNumber;
                    var AnalyzerWorksheetEvnLabSample_Y = data.rowNumber;
                    var EvnLabSample_id = 0;
                    var AnalyzerWorksheetEvnLabSample_id = (!Ext.isEmpty(record.get('AnalyzerWorksheetEvnLabSample_id')))?(record.get('AnalyzerWorksheetEvnLabSample_id')):0;

                    Ext.Ajax.request({
                        url: '/?c=EvnLabSample&m=loadListForCandiPicker',
                        params: params,
                        failure: function(response, options) {
                            that.getLoadMask().hide();
                            sw.swMsg.alert(langs('Не удалось загрузить данные'), langs('Не удалось загрузить данные по пробам-кадидатам. Пожалуйста повторить попытку позже.'));
                        },
                        success: function(response, action) {
                            var result = Ext.util.JSON.decode(response.responseText);
                            if (result.length < 0) {
                            //if (Ext.isEmpty(result[0])) {
                                sw.swMsg.show({
                                        icon: Ext.MessageBox.INFO,
                                        msg: langs('Проба с данным штрих-кодом не найдена в списке проб-кандидатов или уже добавлена в рабочий список.'),
                                        fn: function()
                                        {
                                            that.getLoadMask().hide();
                                            that.grid.loadData();
                                        },
                                        title: langs('Внимание'),
                                        buttons: Ext.Msg.OK
                                    });
                            } else if (!Ext.isEmpty(result[0].EvnLabSample_id)) {
                                EvnLabSample_id = result[0].EvnLabSample_id;
                                Ext.Ajax.request({
                                    url: '/?c=AnalyzerWorksheetEvnLabSample&m=save',
                                    params: {
                                        AnalyzerWorksheetEvnLabSample_id: AnalyzerWorksheetEvnLabSample_id,
                                        AnalyzerWorksheet_id: AnalyzerWorksheet_id,
                                        EvnLabSample_id: EvnLabSample_id,
                                        AnalyzerWorksheetEvnLabSample_X: AnalyzerWorksheetEvnLabSample_X,
                                        AnalyzerWorksheetEvnLabSample_Y: AnalyzerWorksheetEvnLabSample_Y,
                                        action:	'add'
                                    },
                                    failure: function(response, options) {
                                        that.getLoadMask().hide();
                                        sw.swMsg.alert(langs('Не удалось сохранить запись!'), langs('Не удалось сохранить найденную пробу-кадидата. Пожалуйста повторить попытку позже.'));
                                    },
                                    success: function(response, action) {
                                        that.getLoadMask().hide();
                                        that.grid.loadData();
                                        that.grid.getGrid().getSelectionModel().select(1, 6);
                                    }
                                });
                            }
                        }
                    });
                } else {
                    //Если рекорд пустой, то ищем максимальную заполненную ячейку
                    Ext.Ajax.request({
                        url: '/?c=EvnLabSample&m=loadListForCandiPicker',
                        params: params,
                        failure: function(response, options) {
                            that.getLoadMask().hide();
                            sw.swMsg.alert(langs('Не удалось загрузить данные'), langs('Не удалось загрузить данные по пробам-кадидатам. Пожалуйста повторить попытку позже.'));
                        },
                        success: function(response, action) {
                        var result = Ext.util.JSON.decode(response.responseText);
                            if (result.length > 0 && result[0].EvnLabSample_id) {
                                var EvnLabSample = '[' + ((result[0].EvnLabSample_id).toString()) + ']';
                                Ext.Ajax.request({
                                    url: '/?c=AnalyzerWorksheetEvnLabSample&m=saveBulk',
                                    params: {
                                        AnalyzerWorksheetEvnLabSample_id: 0,
                                        AnalyzerWorksheet_id: AnalyzerWorksheet_id,
                                        PickedEvnLabSamples: EvnLabSample,
                                        action:	'add'
                                    },
                                    failure: function(response, options) {
                                        that.getLoadMask().hide();
                                        sw.swMsg.alert(langs('Не удалось сохранить запись!'), langs('Не удалось сохранить найденную пробу-кадидата. Пожалуйста повторить попытку позже.'));
                                    },
                                    success: function(response, action) {
                                        that.getLoadMask().hide();
                                        that.grid.loadData();
                                    }
                                });
                            } else {
                                sw.swMsg.show({
                                    icon: Ext.MessageBox.INFO,
                                    msg: langs('Проба с данным штрих-кодом не найдена в списке проб-кандидатов или уже добавлена в рабочий список.'),
                                    fn: function()
                                    {
                                        that.getLoadMask().hide();
                                        that.grid.loadData();
                                    },
                                    title: langs('Внимание'),
                                    buttons: Ext.Msg.OK
                                });
                            }
                        }
                    });
                }
            },
			getSelectedCellData: function() {
				var selectedCell = this.getGrid().getSelectionModel().getSelectedCell();
				if (!selectedCell)
					return false;
				var rowNumber = selectedCell[0]+1;
				var colNumber = (selectedCell[1])/3; // на одну открытую по 2 скрытых колонки
				var cell = this.getColumnModel().getDataIndex(selectedCell[1]);
				return {rowNumber: rowNumber, colNumber: colNumber, cellName: cell};
			},
			onCellSelect: function(sm,rowIdx,colIdx) {
				var cell = this.getColumnModel().getDataIndex(colIdx);
				var record = this.getGrid().getSelectionModel().getSelected();
				if (this.ownerCt.action != 'view')
					this.setActionDisabled('action_delete', !(record && record.get(cell))); // закрываем доступ к кнопке "Очистить", если нет данных
			},
			onLoadData: function() {
				//
				/*var frm = this;
				var cm = frm.getGrid().getColumnModel();
				frm.getGrid().getStore().each(function (record)
				{

					if (record.get('id') == 1)
					{
						for (i=1; i <= 14; i++)
						{
							// setColumnHeader работает медленно
							cm.setColumnHeader(i,record.get('TimetableGraf_Day'+i));
						}
						return true;
					}
				});

				frm.getGrid().getStore().removeAt(0);

				var row = -1;
				frm.getGrid().getStore().each(function (record)
				{
					row++;
					for (col=1; col <= 14; col++)
					{
						new Ext.Element(frm.getGrid().getView().getCell(row, col)).addClass('x-grid-cell-'+record.get('TimetableType_SysNick'+col));
					}
				});
				*/

			},
			onSelectionChange: function (sm, o)
			{
				// Проверить возможны ли для данной ячейки акшены и какие
				/*var frm = sm.grid.ownerCt.ownerCt;
				if (o)
				{
					if ((o.cell[1]>0) && (o.record.get('TimetableGraf_id'+o.cell[1]))>0)
					{
						// такжe нельзя записывать на резервные бирки чужого ЛПУ
						frm.getAction('action_edit').setDisabled((o.record.get('Person_id'+o.cell[1])>0 || (this.params.Lpu_id != getGlobalOptions().lpu_id && o.record.get('TimetableType_SysNick'+o.cell[1])=='free-reserved')));
						//log([o.record.get('Person_id'+o.cell[1]),this.params.Lpu_id,getGlobalOptions().lpu_id,o.record.get('TimetableType_SysNick'+o.cell[1])]);
						if (o.record.get('Person_id'+o.cell[1])>0)
						{
							frm.getAction('action_delete').setDisabled((o.record.get('pmUser_updId'+o.cell[1]) != getGlobalOptions().pmuser_id));
						}
						else
						{
							frm.getAction('action_delete').setDisabled(true);
						}
					}
					else
					{
						frm.getAction('action_edit').setDisabled(true);
						frm.getAction('action_delete').setDisabled(true);
					}
				}*/
			}.createDelegate(this)
		});
	},
	coeffRefValues: function(rec, coeff) {
		if (!Ext.isEmpty(coeff)) {
			var UslugaTest_ResultLower = rec.get('UslugaTest_ResultLower');
			var UslugaTest_ResultUpper = rec.get('UslugaTest_ResultUpper');
			var UslugaTest_ResultLowerCrit = rec.get('UslugaTest_ResultLowerCrit');
			var UslugaTest_ResultUpperCrit = rec.get('UslugaTest_ResultUpperCrit');
			var UslugaTest_ResultValue = rec.get('UslugaTest_ResultValue');
			
			if (!Ext.isEmpty(UslugaTest_ResultLower)) {
				UslugaTest_ResultLower = UslugaTest_ResultLower * coeff;
			}
			
			if (!Ext.isEmpty(UslugaTest_ResultUpper)) {
				UslugaTest_ResultUpper = UslugaTest_ResultUpper * coeff;
			}
			
			if (!Ext.isEmpty(UslugaTest_ResultLowerCrit)) {
				UslugaTest_ResultLowerCrit = UslugaTest_ResultLowerCrit * coeff;
			}
			
			if (!Ext.isEmpty(UslugaTest_ResultUpperCrit)) {
				UslugaTest_ResultUpperCrit = UslugaTest_ResultUpperCrit * coeff;
			}
			
			if (!Ext.isEmpty(UslugaTest_ResultValue) && !isNaN(parseFloat(UslugaTest_ResultValue))) {
				UslugaTest_ResultValue = UslugaTest_ResultValue * coeff;
			}
			
			rec.set('UslugaTest_ResultNorm',UslugaTest_ResultLower + ' - ' + UslugaTest_ResultUpper);
			rec.set('UslugaTest_ResultCrit',UslugaTest_ResultLowerCrit + ' - ' + UslugaTest_ResultUpperCrit);
			rec.set('UslugaTest_ResultLower',UslugaTest_ResultLower);
			rec.set('UslugaTest_ResultUpper',UslugaTest_ResultUpper);
			rec.set('UslugaTest_ResultLowerCrit',UslugaTest_ResultLowerCrit);
			rec.set('UslugaTest_ResultUpperCrit',UslugaTest_ResultUpperCrit);
			rec.set('UslugaTest_ResultValue',UslugaTest_ResultValue);
		}
	},
	setRefValues: function(rec, refvalues) {
		if (!Ext.isEmpty(refvalues.UslugaTest_ResultQualitativeNorms)) {
			rec.set('UslugaTest_ResultQualitativeNorms', refvalues.UslugaTest_ResultQualitativeNorms);
			var resp = Ext.util.JSON.decode(refvalues.UslugaTest_ResultQualitativeNorms);
			var UslugaTest_ResultNorm = '';
			for (var k1 in resp) {
				if (typeof resp[k1] != 'function') {
					if (UslugaTest_ResultNorm.length > 0) {
						UslugaTest_ResultNorm = UslugaTest_ResultNorm + ', ';
					}
					
					UslugaTest_ResultNorm = UslugaTest_ResultNorm + resp[k1];
				}
			}
			rec.set('UslugaTest_ResultNorm',UslugaTest_ResultNorm);
			rec.set('UslugaTest_ResultCrit','');
			rec.set('UslugaTest_ResultLower','');
			rec.set('UslugaTest_ResultUpper','');
			rec.set('UslugaTest_ResultLowerCrit','');
			rec.set('UslugaTest_ResultUpperCrit','');
			rec.set('UslugaTest_ResultUnit', refvalues.UslugaTest_ResultUnit);
			rec.set('UslugaTest_Comment', refvalues.UslugaTest_Comment);
			rec.set('RefValues_Name', refvalues.RefValues_Name);
			rec.set('RefValues_id', refvalues.RefValues_id);
			rec.set('Unit_id', refvalues.Unit_id);
			rec.commit();
		} else {
			rec.set('UslugaTest_ResultQualitativeNorms', '');
			// избавляемся от null'ов:								
			if (Ext.isEmpty(refvalues.UslugaTest_ResultLower)) {
				refvalues.UslugaTest_ResultLower = '';
			}
			if (Ext.isEmpty(refvalues.UslugaTest_ResultUpper)) {
				refvalues.UslugaTest_ResultUpper = '';
			}
			if (Ext.isEmpty(refvalues.UslugaTest_ResultLowerCrit)) {
				refvalues.UslugaTest_ResultLowerCrit = '';
			}
			if (Ext.isEmpty(refvalues.UslugaTest_ResultUpperCrit)) {
				refvalues.UslugaTest_ResultUpperCrit = '';
			}
													
			rec.set('UslugaTest_ResultNorm',refvalues.UslugaTest_ResultLower + ' - ' + refvalues.UslugaTest_ResultUpper);
			rec.set('UslugaTest_ResultCrit',refvalues.UslugaTest_ResultLowerCrit + ' - ' + refvalues.UslugaTest_ResultUpperCrit);
			rec.set('UslugaTest_ResultLower',refvalues.UslugaTest_ResultLower);
			rec.set('UslugaTest_ResultUpper',refvalues.UslugaTest_ResultUpper);
			rec.set('UslugaTest_ResultLowerCrit',refvalues.UslugaTest_ResultLowerCrit);
			rec.set('UslugaTest_ResultUpperCrit',refvalues.UslugaTest_ResultUpperCrit);
			rec.set('UslugaTest_ResultUnit', refvalues.UslugaTest_ResultUnit);
			rec.set('UslugaTest_Comment', refvalues.UslugaTest_Comment);
			rec.set('RefValues_Name', refvalues.RefValues_Name);
			rec.set('RefValues_id', refvalues.RefValues_id);
			rec.set('Unit_id', refvalues.Unit_id);
			rec.commit();
		}
	},
	updateEvnLabSample: function(params) {
		var grid = this.labSampleResultsGrid;
		
		Ext.Ajax.request(
		{
			url: grid.ViewActions.action_save.initialConfig.url,
			params: params,
			failure: function(response, options) {
				// do nothing
			},
			success: function(response, action) {
				// do nothing
			}
		});
	},
	initComponent: function() {
        var that = this;
        that.gridKeyboardInput = '';
        that.gridKeyboardInputSequence = 1;
        that.resetGridKeyboardInput = function (sequence) {
            var result = false;
            if (sequence == that.gridKeyboardInputSequence) {
                if (that.gridKeyboardInput.length >= 4) {
                    that.grid.onKeyboardInputFinished(that.gridKeyboardInput);
                    result = true;
                }
                that.gridKeyboardInput = '';
            }
            return result;
        };

		var TextTplMark =[
			'<div style="font-size: 12px;">',
			'<div>Рабочий список: <b>{AnalyzerWorksheet_Name}</b>, статус: <b>{AnalyzerWorksheetStatusType_Name}</b>, создан: <b>{AnalyzerWorksheet_setDT}</b></div>',
			'<div>Размеры: <b>{AnalyzerRack_DimensionX} x {AnalyzerRack_DimensionY}</b></div>',
			'</div>'
		];
		this.TextTpl = new Ext.Template(TextTplMark);

		this.TextPanel = new Ext.Panel({
			html: '&nbsp;',
			id: 'AnalyzerWorksheetInfoTextPanel',
			autoHeight: true
		});

		this.formpanel = new Ext.form.FormPanel({
			autoHeight: true,
			id: 'AnalyzerWorksheetEvnLabSampleForm',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			frame: true,
			border: false,
			labelWidth: 200,
			labelAlign: 'right',
			region: 'west',
			items: [
				{name: 'AnalyzerWorksheet_id', xtype: 'hidden', value: null},
				this.TextPanel
			]
		});

		// расписание (предварительные даты) одно для всех
		this.grid = this.createMatrixSamples(this.maxColumns); // максимально возможное количество колонок
		
		this.labSampleGrid = new sw.Promed.ViewFrame(
		{
			title:langs('Пробы'),
			object: 'EvnLabSample',
			id: that.id + 'labSampleGrid',
			region: 'center',
			dataUrl: '/?c=EvnLabSample&m=loadEvnLabSampleListForWorksheet',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'EvnLabSample_id', type: 'int', header: 'EvnLabSample_id', key: true, hidden: true},
				{name: 'EvnLabSample_Num', type:'string', header: langs('Номер пробы'), hidden: true},
				{name: 'EvnLabSample_ShortNum', type:'string', header: langs('Номер пробы'), width: 100},
				{name: 'EvnLabSample_Position', type: 'string', header: langs('Позиция (коорд)'), width: 70},
				{name: 'EvnLabSample_Fio', type: 'string', header: langs('ФИО Пациента'), id: 'autoexpand'}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onRowSelect: function(sm,rowIdx,record)
			{
				that.labSampleResultsGrid.removeAll();
				
				if ( !record || !record.get('EvnLabSample_id') ) {
					return false;
				}
				
				that.labSampleResultsGrid.loadData({
					globalFilters: {
						EvnLabSample_id: record.get('EvnLabSample_id')
					}				
				});
			}
		});

		this.labSampleResultsGrid = new sw.Promed.ViewFrame(
		{
			title:langs('Результаты'),
			object: 'EvnLabSample',
			region: 'south',
			id: that.id + 'labSampleResultsGrid',
			height: 250,
			dataUrl: '/?c=EvnLabSample&m=getLabSampleResultGrid',
			saveAtOnce: false,
			toolbar: true,
			onAfterEdit: function(o) {
				var params = {};
				
				params.UslugaTest_id = o.record.get('UslugaTest_id');
				
				if (o.field && o.field == 'UslugaTest_ResultValue' && o.record) {
					if (o.value == '') {
						o.record.set('UslugaTest_Status', langs('новая'));
					} else {
						o.record.set('UslugaTest_Status', langs('сделана'));
					}
					o.record.set('UslugaTest_ResultApproved', 1);
					o.record.commit();
					this.setActionDisabled('action_cancel', o.record.get('UslugaTest_Status') != langs('новая'));
					this.onApproveChange();
					
					params.UslugaTest_ResultValue = o.value;
					params.updateType = 'value';
					that.updateEvnLabSample(params);
				}
				
				if (o.field && o.field == 'UslugaTest_ResultUnit' && o.record) {
					o.record.set('UslugaTest_ResultUnit', o.rawvalue);
					o.record.commit();
				}
				
				if (o.field && o.field == 'RefValues_Name' && o.record) {
					o.record.set('RefValues_Name', o.rawvalue);
					o.record.commit();
				}
			},
			autoLoadData: false,
			stringfields:
			[
				{name: 'UslugaTest_id', type: 'int', header: 'UslugaTest_id', key: true, hidden: true},
				{name: 'UslugaComplex_Code', type:'string', header: langs('Код'), width: 100},
				{name: 'UslugaComplex_Name', type: 'string', header: langs('Название теста'), id: 'autoexpand'},
				{name: 'UslugaTest_ResultValue', editor: new Ext.form.TextField(), header: langs('Результат'), renderer: function(v, p, row){
					var type = null;
					var addit = "";
					var clr = "#000";
					var UslugaTest_ResultLower = row.get('UslugaTest_ResultLower');
					var UslugaTest_ResultUpper = row.get('UslugaTest_ResultUpper');
					var UslugaTest_ResultLowerCrit = row.get('UslugaTest_ResultLowerCrit');
					var UslugaTest_ResultUpperCrit = row.get('UslugaTest_ResultUpperCrit');
					var UslugaTest_ResultQualitativeNorms = row.get('UslugaTest_ResultQualitativeNorms');
					var UslugaTest_ResultValue = row.get('UslugaTest_ResultValue');
					
					if (!Ext.isEmpty(UslugaTest_ResultValue)) {
						if (!Ext.isEmpty(UslugaTest_ResultQualitativeNorms)) {
							var resp = Ext.util.JSON.decode(UslugaTest_ResultQualitativeNorms);
							if (!UslugaTest_ResultValue.inlist(resp)) {
								clr = "#F00";
							}
						} else if (!isNaN(parseFloat(UslugaTest_ResultValue))) {
							UslugaTest_ResultValue = parseFloat(UslugaTest_ResultValue);
							UslugaTest_ResultLowerCrit = parseFloat(UslugaTest_ResultLowerCrit);
							UslugaTest_ResultUpperCrit = parseFloat(UslugaTest_ResultUpperCrit);
							UslugaTest_ResultLower = parseFloat(UslugaTest_ResultLower);
							UslugaTest_ResultUpper = parseFloat(UslugaTest_ResultUpper);
						
							if (!Ext.isEmpty(UslugaTest_ResultLowerCrit) && UslugaTest_ResultValue <= UslugaTest_ResultLowerCrit) {
								clr = "#F00";
								addit = "&#x25BC;&#x25BC;";
							} else if (!Ext.isEmpty(UslugaTest_ResultUpperCrit) && UslugaTest_ResultValue >= UslugaTest_ResultUpperCrit) {
								clr = "#F00";
								addit = "&#x25B2;&#x25B2;";
							} else if (!Ext.isEmpty(UslugaTest_ResultLower) && UslugaTest_ResultValue <= UslugaTest_ResultLower) {
								clr = "#F00";
								addit = "&#x25BC;";
							} else if (!Ext.isEmpty(UslugaTest_ResultUpper) && UslugaTest_ResultValue >= UslugaTest_ResultUpper) {
								clr = "#F00";
								addit = "&#x25B2;";
							}
						}
					}
					
					if (v == null) {
						v = "";
					}

					return "<span style='color:"+clr+"; float: left;'>"+v+"</span>" + "<span style='color:#F00; float: right;'>" + addit + "</span>";
				}, width: 110},
				{name: 'UslugaTest_ResultUnit', editor: new sw.Promed.SwTestUnitCombo({
					allowBlank: true,
					listWidth: 300,
					listeners: {
						'show': function() {
							var combo = this;
							combo.record = that.labSampleResultsGrid.getGrid().getSelectionModel().getSelected();
							this.getStore().removeAll();
							this.getStore().load({
								params: {
									UslugaTest_id: combo.record.get('UslugaTest_id')
								}
							});
						},
						'change': function(combo, newValue) {
							if (combo.record) {
								combo.record.set('Unit_id', combo.getValue());
								combo.record.set('UslugaTest_ResultUnit', combo.getFieldValue('Unit_Name'));
								combo.record.commit();
							
								that.coeffRefValues(combo.record, combo.getFieldValue('Unit_Coeff'));
								
								var refvalues = {};
								refvalues.UslugaTest_ResultQualitativeNorms = combo.record.get('UslugaTest_ResultQualitativeNorms');
								refvalues.UslugaTest_ResultNorm = combo.record.get('UslugaTest_ResultNorm');
								refvalues.UslugaTest_ResultCrit = combo.record.get('UslugaTest_ResultCrit');
								refvalues.UslugaTest_ResultLower = combo.record.get('UslugaTest_ResultLower');
								refvalues.UslugaTest_ResultUpper = combo.record.get('UslugaTest_ResultUpper');
								refvalues.UslugaTest_ResultLowerCrit = combo.record.get('UslugaTest_ResultLowerCrit');
								refvalues.UslugaTest_ResultUpperCrit = combo.record.get('UslugaTest_ResultUpperCrit');
								refvalues.UslugaTest_ResultUnit = combo.record.get('UslugaTest_ResultUnit');
								refvalues.UslugaTest_Comment = combo.record.get('UslugaTest_Comment');
								refvalues.RefValues_id = combo.record.get('RefValues_id');
								refvalues.Unit_id = combo.record.get('Unit_id');
								
								var params = {};
								params.UslugaTest_id = combo.record.get('UslugaTest_id');
								params.UslugaTest_RefValues = Ext.util.JSON.encode(refvalues);
								params.UslugaTest_ResultValue = combo.record.get('UslugaTest_ResultValue');
								params.updateType = 'value';
								that.updateEvnLabSample(params);
							}
						},
						'blur': function(combo, newValue) {
							var grid = that.labSampleResultsGrid.getGrid();
							grid.stopEditing();
						}
					}
				}), type: 'string', header: langs('Единица измерения'), width: 110},
				{name: 'RefValues_id', type:'int', hidden:true},
				{name: 'Unit_id', type:'int', hidden:true},
				{name: 'RefValues_Name', editor: new sw.Promed.SwAnalyzerTestRefValuesCombo({
					allowBlank: true,
					listWidth: 300,
					listeners: {
						'show': function() {
							var combo = this;
							combo.record = that.labSampleResultsGrid.getGrid().getSelectionModel().getSelected();
							this.getStore().removeAll();
							this.getStore().load({
								params: {
									UslugaTest_id: combo.record.get('UslugaTest_id')
								}
							});
						},
						'change': function(combo, newValue) {
							if (combo.record) {
								that.setRefValues(combo.record, {
									UslugaTest_ResultQualitativeNorms: combo.getFieldValue('UslugaTest_ResultQualitativeNorms'),
									UslugaTest_ResultUnit: combo.getFieldValue('UslugaTest_ResultUnit'),
									UslugaTest_Comment: combo.getFieldValue('UslugaTest_Comment'),
									RefValues_id: combo.getFieldValue('RefValues_id'),
									Unit_id: combo.getFieldValue('Unit_id'),
									RefValues_Name: combo.getFieldValue('RefValues_Name'),
									UslugaTest_ResultLower: combo.getFieldValue('UslugaTest_ResultLower'),
									UslugaTest_ResultUpper: combo.getFieldValue('UslugaTest_ResultUpper'),
									UslugaTest_ResultLowerCrit: combo.getFieldValue('UslugaTest_ResultLowerCrit'),
									UslugaTest_ResultUpperCrit: combo.getFieldValue('UslugaTest_ResultUpperCrit')
								});
								
								var refvalues = {};
								refvalues.UslugaTest_ResultQualitativeNorms = combo.record.get('UslugaTest_ResultQualitativeNorms');
								refvalues.UslugaTest_ResultNorm = combo.record.get('UslugaTest_ResultNorm');
								refvalues.UslugaTest_ResultCrit = combo.record.get('UslugaTest_ResultCrit');
								refvalues.UslugaTest_ResultLower = combo.record.get('UslugaTest_ResultLower');
								refvalues.UslugaTest_ResultUpper = combo.record.get('UslugaTest_ResultUpper');
								refvalues.UslugaTest_ResultLowerCrit = combo.record.get('UslugaTest_ResultLowerCrit');
								refvalues.UslugaTest_ResultUpperCrit = combo.record.get('UslugaTest_ResultUpperCrit');
								refvalues.UslugaTest_ResultUnit = combo.record.get('UslugaTest_ResultUnit');
								refvalues.UslugaTest_Comment = combo.record.get('UslugaTest_Comment');
								refvalues.RefValues_id = combo.record.get('RefValues_id');
								refvalues.Unit_id = combo.record.get('Unit_id');
								
								var params = {};
								params.UslugaTest_id = combo.record.get('UslugaTest_id');
								params.UslugaTest_RefValues = Ext.util.JSON.encode(refvalues);
								that.updateEvnLabSample(params);
							}
						},
						'blur': function(combo, newValue) {
							var grid = that.labSampleResultsGrid.getGrid();
							grid.stopEditing();
						}
					}
				}), type: 'string', header: langs('Референ. значения'), width: 110},
				{name: 'UslugaTest_ResultNorm', type: 'string', header: langs('Норм. диапазон'), width: 160},
				{name: 'UslugaTest_ResultCrit', type: 'string', header: langs('Критич. диапазон'), width: 160},
				{name: 'UslugaTest_ResultLower', type: 'string', hidden: true},
				{name: 'UslugaTest_ResultUpper', type: 'string', hidden: true},
				{name: 'UslugaTest_ResultLowerCrit', type: 'string', hidden: true},
				{name: 'UslugaTest_ResultUpperCrit', type: 'string', hidden: true},
				{name: 'UslugaTest_ResultQualitativeNorms', type: 'string', hidden: true},
				{name: 'UslugaTest_Comment', type: 'string', header: langs('Комментарий'), width: 80},
				{name: 'UslugaTest_Status', type: 'string', header: langs('Статус'), width: 100},
				{name: 'UslugaTest_setDT', type: 'timedate', header: langs('Дата'), width: 80},
				{name: 'UslugaTest_ResultApproved', hidden: true, header:langs('Признак одобрения')}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true},
				{name:'action_save', url: '/?c=EvnLabSample&m=updateResult', hidden: true }
			],
			onLoadData: function() {
				var store = this.getGrid().getStore();
				
				store.each(function(rec) {
					if (!Ext.isEmpty(rec.get('UslugaTest_ResultQualitativeNorms'))) {
						var resp = Ext.util.JSON.decode(rec.get('UslugaTest_ResultQualitativeNorms'));
						var UslugaTest_ResultNorm = '';
						for (var k1 in resp) {
							if (typeof resp[k1] != 'function') {
								if (UslugaTest_ResultNorm.length > 0) {
									UslugaTest_ResultNorm = UslugaTest_ResultNorm + ', ';
								}
								
								UslugaTest_ResultNorm = UslugaTest_ResultNorm + resp[k1];
							}
						}
						rec.set('UslugaTest_ResultNorm',UslugaTest_ResultNorm);
						rec.set('UslugaTest_ResultCrit','');
						rec.set('UslugaTest_ResultLower','');
						rec.set('UslugaTest_ResultUpper','');
						rec.set('UslugaTest_ResultLowerCrit','');
						rec.set('UslugaTest_ResultUpperCrit','');
						rec.commit();
					}
				});
			},
			onApproveChange: function() {
				var record = this.getGrid().getSelectionModel().getSelected();
				if (record && record.get('UslugaTest_ResultApproved') == 2) {
					this.setActionHidden('action_unapproveone', false);
					this.setActionHidden('action_approveone', true);
				} else {
					this.setActionHidden('action_unapproveone', true);
					this.setActionHidden('action_approveone', false);
				}
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				this.onApproveChange();
				this.setActionDisabled('action_cancel', record.get('UslugaTest_Status') != langs('новая'));
			}
		});

		this.tabPanel = new Ext.TabPanel({
			region: 'center',
			activeTab: 0,
			layoutOnTabChange: true,
			listeners:
			{
				tabchange: function(tab, panel)
				{
					// that.syncShadow();
					if (panel.id == 'tab_results') {
						that.labSampleResultsGrid.addActions({
							name:'action_cancel',
							iconCls: 'archive16',
							text: langs('Отменить'),
							tooltip: langs('Отменить тест'),
							handler: function() {
								var params = {};
								
								var record = that.labSampleGrid.getGrid().getSelectionModel().getSelected();
								if ( !record || !record.get('EvnLabSample_id') ) {
									return false;
								}
								
								params.EvnLabSample_id = record.get('EvnLabSample_id');
								
								var record = that.labSampleResultsGrid.getGrid().getSelectionModel().getSelected();
								if ( !record || !record.get('UslugaTest_id') ) {
									return false;
								}
								
								params.UslugaTest_id = record.get('UslugaTest_id');
								
								sw.swMsg.show({
									icon: Ext.MessageBox.QUESTION,
									msg: langs('Выбранный тест будет удален. Вы действительно хотите его отменить?'),
									title: langs('Вопрос'),
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ('yes' == buttonId) {
											that.getLoadMask(langs('Отмена теста')).show();
											Ext.Ajax.request({
												url: '/?c=EvnLabSample&m=cancelTest',
												params: params,
												failure: function(response, options) {
													that.getLoadMask().hide();
												},
												success: function(response, action) {
													that.getLoadMask().hide();
													that.labSampleResultsGrid.getGrid().getStore().reload();
												}
											});
										}
									}
								});
							}.createDelegate(this)
						});
						that.labSampleResultsGrid.addActions({
							name:'action_approveall',
							iconCls: 'archive16',
							text: langs('Одобрить все'),
							tooltip: langs('Одобрить все результаты'),
							handler: function() {
								var params = {};
								
								var record = that.labSampleGrid.getGrid().getSelectionModel().getSelected();
								if ( !record || !record.get('EvnLabSample_id') ) {
									return false;
								}
								
								params.EvnLabSample_id = record.get('EvnLabSample_id');
								
								sw.swMsg.show({
									icon: Ext.MessageBox.QUESTION,
									msg: langs('Одобрить все результаты'),
									title: langs('Вопрос'),
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ('yes' == buttonId) {
											that.getLoadMask(langs('Одобрение результатов')).show();
											Ext.Ajax.request({
												url: '/?c=EvnLabSample&m=approveResults',
												params: params,
												failure: function(response, options) {
													that.getLoadMask().hide();
												},
												success: function(response, action) {
													that.getLoadMask().hide();
													that.labSampleResultsGrid.getGrid().getStore().reload();
												}
											});
										}
									}
								});
							}.createDelegate(this)
						});
						that.labSampleResultsGrid.addActions({
							name:'action_unapproveone',
							iconCls: 'archive16',
							text: langs('Снять одобрение'),
							tooltip: langs('Снять одобрение результата'),
							handler: function() {
								var params = {};
								
								var record = that.labSampleGrid.getGrid().getSelectionModel().getSelected();
								if ( !record || !record.get('EvnLabSample_id') ) {
									return false;
								}
								
								params.EvnLabSample_id = record.get('EvnLabSample_id');
								
								var record = that.labSampleResultsGrid.getGrid().getSelectionModel().getSelected();
								if ( !record || !record.get('UslugaTest_id') ) {
									return false;
								}
								
								params.UslugaTest_id = record.get('UslugaTest_id');
								
								that.getLoadMask(langs('Одобрение результатов')).show();
								Ext.Ajax.request({
									url: '/?c=EvnLabSample&m=unapproveResults',
									params: params,
									failure: function(response, options) {
										that.getLoadMask().hide();
									},
									success: function(response, action) {
										that.getLoadMask().hide();
										that.labSampleResultsGrid.getGrid().getStore().reload();
									}
								});
							}.createDelegate(this)
						});
						that.labSampleResultsGrid.addActions({
							name:'action_approveone',
							iconCls: 'archive16',
							text: langs('Одобрить'),
							tooltip: langs('Одобрить результат'),
							handler: function() {
								var params = {};
								
								var record = that.labSampleGrid.getGrid().getSelectionModel().getSelected();
								if ( !record || !record.get('EvnLabSample_id') ) {
									return false;
								}
								
								params.EvnLabSample_id = record.get('EvnLabSample_id');
								
								var record = that.labSampleResultsGrid.getGrid().getSelectionModel().getSelected();
								if ( !record || !record.get('UslugaTest_id') ) {
									return false;
								}
								
								if (Ext.isEmpty(record.get('UslugaTest_ResultValue'))) {
									Ext.Msg.alert(langs('Внимание'),langs('Нужно заполнить результат, до его одобрения'));
									return false;
								}
								
								params.UslugaTest_id = record.get('UslugaTest_id');
								
								that.getLoadMask(langs('Одобрение результатов')).show();
								Ext.Ajax.request({
									url: '/?c=EvnLabSample&m=approveResults',
									params: params,
									failure: function(response, options) {
										that.getLoadMask().hide();
									},
									success: function(response, action) {
										that.getLoadMask().hide();
										that.labSampleResultsGrid.getGrid().getStore().reload();
									}
								});
							}.createDelegate(this)
						});
						
						that.labSampleGrid.addActions({
							name:'action_approve',
							text:langs('Одобрить'),
							iconCls: 'actions16',
							menu: new Ext.menu.Menu({
								items: [
									new Ext.Action({
										text: langs('Все результаты'),
										handler: function() {
											var params = {};
											
											var record = that.labSampleGrid.getGrid().getSelectionModel().getSelected();
											if ( !record || !record.get('EvnLabSample_id') ) {
												return false;
											}
											
											params.EvnLabSample_id = record.get('EvnLabSample_id');
											that.getLoadMask(langs('Одобрение результатов')).show();
											Ext.Ajax.request({
												url: '/?c=EvnLabSample&m=approveResults',
												params: params,
												failure: function(response, options) {
													that.getLoadMask().hide();
												},
												success: function(response, action) {
													that.getLoadMask().hide();
													that.labSampleResultsGrid.getGrid().getStore().reload();
												}
											});
										}
									}),
									new Ext.Action({
										text: langs('Только результаты в норме'),
										disabled: true,
										handler: function() {
											var params = {};

											var record = that.labSampleGrid.getGrid().getSelectionModel().getSelected();
											if ( !record || !record.get('EvnLabSample_id') ) {
												return false;
											}
											
											params.EvnLabSample_id = record.get('EvnLabSample_id');
											params.onlyNorm = 1;
											that.getLoadMask(langs('Одобрение результатов')).show();
											Ext.Ajax.request({
												url: '/?c=EvnLabSample&m=approveResults',
												params: params,
												failure: function(response, options) {
													that.getLoadMask().hide();
												},
												success: function(response, action) {
													that.getLoadMask().hide();
													that.labSampleResultsGrid.getGrid().getStore().reload();
												}
											});

										}
									})
								]
							})
						});
						
						// прогрузить список проб.
						that.labSampleGrid.loadData({
							globalFilters: {
								AnalyzerWorksheet_id: that.AnalyzerWorksheet_id,
								start: 0,
								limit: 100
							}
						});
					}
				}
			},
			items:
			[{
				title: langs('Штатив'),
				layout: 'fit',
				id: 'tab_worksheet',
				border:false,
				items: [that.grid]
			},
			{
				title: langs('Результаты'),
				layout: 'fit',
				id: 'tab_results',
				border:false,
				items: [{
					border: false,
					layout: 'border',
					height: 500,
					region: 'center',
					items: [
						that.labSampleGrid, 
						that.labSampleResultsGrid
					]
				}]
			}]
		});

		Ext.apply(this, {
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function()
				{
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items:[this.formpanel, this.tabPanel]
		});
		sw.Promed.swAnalyzerWorksheetEvnLabSampleWindow.superclass.initComponent.apply(this, arguments);
	}
});