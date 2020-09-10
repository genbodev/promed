/**
* swTreatmentReportWindow - форма просмотра списка и заполнения фильтров для печати отчетов по обращениям
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swTreatmentReportWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      15.07.2010
* @comment      Префикс для id компонентов TRW (TreatmentReportWindow). TABINDEX_TRW = 10400
*/

sw.Promed.swTreatmentReportWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'TRW',
	layout: 'border',
	maximized: true,
	modal: true,
	resizable: false,
	draggable: false,
	closeAction:'hide',
	plain: true,
	title: lang['registratsiya_obrascheniy_otchetnost'],
	initComponent: function() 
	{
		Ext.apply(this, 
		{
			items: [
				new Ext.tree.TreePanel(
				{
					split: true,
					region: 'west',
					height: 500,
					width: 230,
					useArrows: true,
					animate:true,
					id: 'TRW_Tree',
					enableDD: false,
					rootVisible: false,
					title: lang['vyiberite_otchetyi'],
					//header: true,
					//headerAsText: true,
					autoScroll: true,
					border: true,
					root:
					{
						text: lang['otchetyi'],
						draggable: false,
						expandable: true,
						expanded: true,
						id: 'TRW_root'
					},
					loader: new Ext.tree.TreeLoader(
					{
						dataUrl: '/?c=Treatment&m=getTreatmentReportTree'
					}),
					listeners: 
					{
						'click': function(node) 
						{
							switch ( node.id ) {
								case 'TRW_root':
									node.expandChildNodes();
									return;
									break;
								/*case 'TRW_subjectLpu':
									sw.swMsg.alert(lang['soobschenie'], lang['otchet_v_razrabotke']);
									
									var header_form = this.findById('TRW_FormHeader');
									header_form.setTitle(tree.getSelectionModel().getSelectedNode().text);
									var form = this.findById('TRW_Form');
									form.removeAll();
									Ext.getCmp('TRW_HelpButton').handler = function(button, event) 
									{
										ShowHelp(lang['otchet']+header_form.title);
									};
									break;
								case 'TRW_subjectMedpersonal':
									sw.swMsg.alert(lang['soobschenie'], lang['otchet_v_razrabotke']);
									break;*/
								default:
									var wnd = this.ownerCt;
									controlStoreRequest = Ext.Ajax.request(
									{
										url: '/?c=Treatment&m=getTreatmentReportForm',
										params: {node: node.id},
										success: function(result)
										{
											formData = Ext.util.JSON.decode(result.responseText);
											wnd.setFormData( formData );
											wnd.setControlFormData( wnd, node.id );
										},
										failure: function(result)
										{
											
										},
										method: 'POST',
										timeout: 120000
									});
									break;
							}
						}
					}
				}),
				new Ext.Panel(
				{
					id: 'TRW_FormHeader',
					title: lang['otchetyi'],
					region: 'center',
					layout: 'border',
					items: 
					[
						new Ext.form.FormPanel(
						{
							frame: true,
							url: '/?c=Treatment&m=getTreatmentReport',
							autoHeight: true,
							region: 'center',
							id: 'TRW_Form',
							autoLoad: false,
							border: false,
							buttons: [{
								handler: function() {
									this.print();
								}.createDelegate(this),
								iconCls: 'print16',
								tabIndex: TABINDEX_TRW + 49,
								text: BTN_FRMPRINT
							}, {
								text: '-'
							}],
							items: []
						})
					]
				})
			],
			buttons: [
			{
				text:'-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'TRW_HelpButton',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			}, 
			{
				text: BTN_FRMCANCEL,
				iconCls: 'cancel16',
				tabIndex: TABINDEX_TRW + 50,
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			enableKeyEvents: true,
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					if (e.brTRWserEvent.stopPropagation)
						e.brTRWserEvent.stopPropagation();
					else
						e.brTRWserEvent.cancelBubble = true;
					if (e.brTRWserEvent.preventDefault)
						e.brTRWserEvent.preventDefault();
					else
						e.brTRWserEvent.returnValue = false;
					e.brTRWserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.brTRWserEvent.keyCode = 0;
						e.brTRWserEvent.which = 0;
					}

					if (e.getKey() == Ext.EventObject.ESC)
					{
						Ext.getCmp('TRW').hide();
						return false;
					}

					if (e.getKey() == Ext.EventObject.ENTER)
					{
						Ext.getCmp('TRW').print();
						return false;
					}
				},
				key: [ Ext.EventObject.ENTER, Ext.EventObject.ESC ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swTreatmentReportWindow.superclass.initComponent.apply(this, arguments);
	},
	setFormData: function( formdata ) 
	{
		function processField(field, cont)
		{
			if (field.xtype==undefined)
				return;
			var config = {xtype: field.xtype};
			for (property in field)
			{
				if ( property != 'xtype' )
				{
					config[property] = field[property];
					if ( property = 'maskRe' )
						config[property] = /\d/;
				}
			}
			switch (field.xtype)
			{
				case 'daterangefield':
					var fd = new Ext.form.DateRangeField(config);
					break;
				case 'swdatefield':
					config['plugins'] = [ new Ext.ux.InputTextMask('99.99.9999', true) ] ; 
					var fd = new sw.Promed.SwDateField(config);
					break;
				case 'swtreatmentcombo':
					var fd = new sw.Promed.SwTreatmentCombo(config);
					fd.getStore().load({
						callback: function() {
							fd.setValue(config.value);
						}
					});
					break;
				case 'swcommonsprcombo':
					var fd = new sw.Promed.SwCommonSprCombo(config);
					fd.getStore().load({
						callback: function() {
							fd.setValue(config.value);
						}
					});
					break;
				case 'swlpulocalcombo':
					var fd = new sw.Promed.SwLpuLocalCombo(config);
					break;
				case 'textarea':
					var fd = new Ext.form.TextArea(config);
					break;
			}
			cont.add(fd);
			cont.doLayout();
		}
		// меняем заголовок окна и вызов кнопки помощи
		var tree = this.findById('TRW_Tree');
		var header_form = this.findById('TRW_FormHeader');
		header_form.setTitle(tree.getSelectionModel().getSelectedNode().text);
		Ext.getCmp('TRW_HelpButton').handler = function(button, event) 
		{
			ShowHelp(lang['otchet']+header_form.title);
		};
		var form = this.findById('TRW_Form');
		form.removeAll();
		form.doLayout();
		var node='';
		for (menu in formdata)
		{
			node = menu;
			for(fields in formdata[menu])
			{
				if (formdata[menu][fields].type != undefined && formdata[menu][fields].type == 'fieldset')
				{
					var fset = new Ext.form.FieldSet({
						title: formdata[menu][fields].label,
						labelWidth: formdata[menu][fields].labelWidth,
						autoHeight: true
					});
					form.add(fset);
					for (fld in formdata[menu][fields].items)
						processField(formdata[menu][fields].items[fld], fset);
				}
				else
				{
					processField(formdata[menu][fields], form);
				}
			}
		}
		if (node=='TRW_subjectMedpersonal') {
			var panel = this.findById('TRW_Form'); // получаем панель, на которой находятся комбики
			var lists = this.getComboLists(panel); // получаем список комбиков
			this.loadDataLists({}, lists, true); // прогружаем все справочники (третий параметр noclose - без операций над формой)
		}
		form.add(new Ext.form.Hidden({id: 'TRW_node', name: 'node', value: node}));
		form.doLayout();
		Ext.getCmp('TRW_Treatment_DateReg_Start').focus(true, 100);
	},
	setControlFormData: function( current_window, node_id ) 
	{
		switch ( node_id ) {
			case 'TRW_subjectMedpersonal':
				// В список выбора ЛПУ отчета «Субъекты обращения Врачи» добавляем значение ВСЕ
				/*var data = {};
				data['Lpu_id'] = "";
				data['Lpu_Nick'] = "ВСЕ";
				var record = new Ext.data.Record(data);
				Ext.getCmp('TRW_Lpu_sid').getStore().insert(0,[record]);
				//log(record);
				this.findById('TRW_Form').doLayout();*/
			// Для отчетов «Субъекты обращения ЛПУ», «Субъекты обращения Врачи» устанавливаем зависимость полей дат рассмотрения и результат рассмотрения
			case 'TRW_subjectLpu':
				Ext.getCmp('TRW_TreatmentReview_id').addListener('beforeselect', function(combo, record) {
					if (record.get(combo.valueField)) {
						var rectype = record.get(combo.valueField);
						if ( rectype  == 2) // Рассмотрено
						{
							current_window.enableField('TRW_Treatment_DateReview_Start', true);
							current_window.enableField('TRW_Treatment_DateReview_End', true);
							Ext.getCmp('TRW_Treatment_DateReview_Start').focus(true, 100);
							return true;
						}
					}
					current_window.disableField('TRW_Treatment_DateReview_Start', true);
					current_window.disableField('TRW_Treatment_DateReview_End', true);
				});
				/*Ext.getCmp('TRW_Treatment_DateReview_Start').addListener('select', function(field, data) {
					Ext.getCmp('TRW_TreatmentReview_id').setValue(2);
				});*/
				break;
			default:
				break;
		}
	},
	enableField: function(id, allowBlank = false) {
		var cmp = Ext.getCmp(id);
		cmp.enable();
		cmp.setVisible(true);
		cmp.allowBlank = allowBlank;
	},
	disableField: function(id, visible) {
		var cmp = Ext.getCmp(id);
		cmp.disable();
		cmp.setVisible(visible);
		cmp.setValue('');
		cmp.allowBlank = true;
	},
	print: function() {
		var Treatment_DateReg_Start = Ext.util.Format.date(Ext.getCmp('TRW_Treatment_DateReg_Start').getValue(), 'd.m.Y');
		var Treatment_DateReg_End = Ext.util.Format.date(Ext.getCmp('TRW_Treatment_DateReg_End').getValue(), 'd.m.Y');
		var node = Ext.getCmp('TRW_node').getValue();
		if (!Treatment_DateReg_Start || !Treatment_DateReg_End || !node)
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					if (!Treatment_DateReg_Start) 
						Ext.getCmp('TRW_Treatment_DateReg_Start').focus(true);
					else if (!Treatment_DateReg_End) 
						Ext.getCmp('TRW_Treatment_DateReg_End').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var query_string = '/?c=Treatment&m=getTreatmentReport&node=' + node + '&Treatment_DateReg_Start=' + Treatment_DateReg_Start + '&Treatment_DateReg_End=' + Treatment_DateReg_End;
		var values = Ext.getCmp('TRW_Form').getForm().getValues();
		//log( values );
		switch ( node ) {
			case 'TRW_subjectMedpersonal':
				var Lpu_sid = values['Lpu_sid'];
				if ( !Lpu_sid )
				{
					Lpu_sid = '';
/*
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
								Ext.getCmp('TRW_Lpu_sid').focus(true);
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: ERR_INVFIELDS_MSG,
						title: ERR_INVFIELDS_TIT
					});
					return false;
*/
				}
				query_string = query_string + '&Lpu_sid=' + Lpu_sid;
			case 'TRW_subjectLpu': 
				var TreatmentMethodDispatch_id = values['TreatmentMethodDispatch_id'];
				var TreatmentMultiplicity_id = values['TreatmentMultiplicity_id'];
				var TreatmentType_id = values['TreatmentType_id'];
				var TreatmentCat_id = values['TreatmentCat_id'];
				var TreatmentRecipientType_id = values['TreatmentRecipientType_id'];
				var TreatmentReview_id = values['TreatmentReview_id'];
				var Treatment_DateReview_Start = values['Treatment_DateReview_Start'];
				var Treatment_DateReview_End = values['Treatment_DateReview_End'];
				if ( TreatmentMethodDispatch_id )
					query_string = query_string + '&TreatmentMethodDispatch_id=' + TreatmentMethodDispatch_id;
				if ( TreatmentMultiplicity_id )
					query_string = query_string + '&TreatmentMultiplicity_id=' + TreatmentMultiplicity_id;
				if ( TreatmentType_id )
					query_string = query_string + '&TreatmentType_id=' + TreatmentType_id;
				if ( TreatmentCat_id )
					query_string = query_string + '&TreatmentCat_id=' + TreatmentCat_id;
				if ( TreatmentRecipientType_id )
					query_string = query_string + '&TreatmentRecipientType_id=' + TreatmentRecipientType_id;
				if ( TreatmentReview_id )
					query_string = query_string + '&TreatmentReview_id=' + TreatmentReview_id;
				if ( Treatment_DateReview_Start )
					query_string = query_string + '&Treatment_DateReview_Start=' + Treatment_DateReview_Start;
				if ( Treatment_DateReview_End )
					query_string = query_string + '&Treatment_DateReview_End=' + Treatment_DateReview_End;
				break;
		}
		window.open(query_string, '_blank');
	},
	show: function() 
	{
		sw.Promed.swTreatmentReportWindow.superclass.show.apply(this, arguments);
		this.center();
		var tree = this.findById('TRW_Tree');
		tree.root.expand(true, true, function() 
		{
			if (tree.root.hasChildNodes())
			{
				tree.getSelectionModel().select(tree.root.item(0));
				tree.fireEvent('click', tree.root.item(0));
			}
		});
	}
});
