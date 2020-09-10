/**
* swSelectLpuWindow - окно выбора МО, в случае если человек прикреплен к нескольким МО
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      19.05.2009
*/

/**
 * swSelectLpuWindow - окно выбора МО, в случае если человек прикреплен к нескольким МО
 *
 * @class sw.Promed.swSelectLpuWindow
 * @extends Ext.Window
 */
sw.Promed.swSelectLpuWindow = Ext.extend(sw.Promed.BaseForm, {
	closable: false,
	width : 500,
	height : 140,
	modal: true,
	resizable: false,
	autoHeight: false,
	closeAction :'hide',
	border : false,
	plain : false,
	title: lang['vyibor_mo'],
	/**
	 * Входящие параметры - список Lpu_id для отображения в списке выбора
	 * @type {Array}
	 */
	params: null,
	/**
	 * Отображение окна
	 */
	show: function() {
		sw.Promed.swSelectLpuWindow.superclass.show.apply(this, arguments);

		if ( arguments[0].params ) {
			this.params = arguments[0].params;
		}
		var form = this.findById('SelectLpuForm');
		var LpuField = form.findById('SLW_Lpu_id');
		//loadComboOnce(form.findById('SLW_Lpu_id'), 'Lpu', function() {
		LpuField.getStore().clearFilter();
		// Выбираем первое МО в списке
		if (getGlobalOptions().TOUZLpuArr && getGlobalOptions().TOUZLpuArr.length > 0 && !isSuperAdmin() && !getGlobalOptions().isMinZdrav) {
			this.params = getGlobalOptions().TOUZLpuArr;
		} else if ( !getGlobalOptions().superadmin && !isUserGroup(['medpersview', 'ouzuser', 'OuzSpecMPC', 'ouzadmin', 'ouzspec', 'ouzchief', 'roszdrnadzorview']) && !(getGlobalOptions().isMinZdrav && getGlobalOptions().orgtype == 'touz' && isUserGroup(['ouzspec'])) ) {
			// Фильтруем МО, чтобы отображались только те, идентификаторы которых пришли как параметр
			this.params = (this.params)?this.params:getGlobalOptions().lpu;
		} /*
		это что-то неправильное, на момент открытия формы справочник уже загружен, зачем его загружать повторно
		else {
			if(getGlobalOptions().region.nick!='ufa')
				LpuField.getStore().load();
			
        }*/

		var i, lpu_id;

		LpuField.getStore().filterBy(function(record, id) {
			if ( record.get('Lpu_IsAccess') == 1 ) {
				return false;
			}

			var ret = true;

			if ( this.params ) {
				ret = false;

				for (i = 0; i < this.params.length; i++) {
					if ( this.params[i] == record.get('Lpu_id') ) {
						if ( Ext.isEmpty(lpu_id) ) {
							lpu_id = record.get('Lpu_id');
						}
						ret = true;
						break;
					}
				}
			}

			return ret;
		}.createDelegate(this));

		// Для непустого this.params lpu_id получили в процессе фильтрации
		if ( !this.params ) {
			// Если входных параметров нет (не пришел в форму список МО), то выбираем текущее МО
			var index = LpuField.getStore().findBy(function(rec) {
				return (rec.get('Lpu_id') == getGlobalOptions().lpu_id);
			});

			if ( index >= 0 ) {
				lpu_id = getGlobalOptions().lpu_id;
			}
		}

		if ( !Ext.isEmpty(lpu_id) ) {
			LpuField.setValue(lpu_id);
		}

		LpuField.focus(true, 100);

		var record = LpuField.getStore().getById(LpuField.getValue());
		LpuField.fireEvent("select", LpuField, record);
		this.buttons[0].enable();
		//}.createDelegate(this));
	}, //end show()


	/**
	 * Запрос к серверу после выбора МО
	 */
	submit: function() {
		var form = this.findById('SelectLpuForm').getForm();
		
		this.buttons[0].disable();
		
		if (!form.isValid()) {
			Ext.Msg.alert(lang['oshibka_zapolneniya_formyi'],
					lang['proverte_pravilnost_zapolneniya_poley_formyi']);
			this.buttons[0].enable();
			return;
		}
		form.submit({
			success : function(form, action) {
				this.hide();
				setCurrentLpu(action.result);
				
				// Открытие АРМа по умолчанию
				// TODO: Надо ли оно здесь
				// sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
				
				this.buttons[0].enable();
			}.createDelegate(this),
			failure : function(form, action) {
				
				if  ((action.result) && (action.result.Error_Code))
					Ext.Msg.alert("Ошибка", '<b>Ошибка '
									+ action.result.Error_Code
									+ ' :</b><br/> '
									+ action.result.Error_Msg);
				this.buttons[0].enable();
			}.createDelegate(this)
		});
	}, //end submit()

	/**
	 * Конструктор
	 */
	initComponent: function() {
		var form = this;


		var TextTplMark =[
			'<div style="font-size: 11px;">{text}</div>'
		];
		this.TextTpl = new Ext.Template(TextTplMark);

		this.TextPanel = new Ext.Panel({
			html: '&nbsp;',
			style: 'margin-left:55px',
			id: 'slwTextPanel',
			autoHeight: true
		});


    	Ext.apply(this, {
			items : [new Ext.form.FormPanel({
				id : 'SelectLpuForm',
				height : 75,
				layout : 'form',
				border : false,
				frame : true,
				labelWidth : 50,
				items : [{
					xtype: 'fieldset',
					style : 'padding: 10px;',
					autoHeight: true,
					items : [{
						anchor : "95%",
						editable : true,
						//editable: !(getGlobalOptions().TOUZLpuArr && getGlobalOptions().TOUZLpuArr.length > 0 && !isSuperAdmin() && getGlobalOptions().isMinZdrav),
						ctxSerach: true,
						forceSelection: true,
						hiddenName : 'Lpu_id',
						fieldLabel: lang['mo'],
						id : 'SLW_Lpu_id',
						lastQuery : '',
						listeners: {
							'blur': function(combo) {
								if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 ) {
									combo.clearValue();
									var p = {text:'&nbsp;'};
									form.TextTpl.overwrite(form.TextPanel.body, p);
									form.TextPanel.render();
								}
							},
							'select': function(combo, record, index) {
								var p = {text:'&nbsp;'};
								if(record) {
									if ( record.get('Lpu_EndDate') && record.get('Lpu_EndDate') != '' ) {
										p.text = '<span style="color: red;">МО закрыто '+record.get('Lpu_EndDate')+'</span>';
									} else {
										p.text = record.get('Lpu_Name');
									}
									/*if (isSuperAdmin()) {
										p.text = p.text + ' [ id: '+record.get('Lpu_id')+' ]';
									}*/
								}
								form.TextTpl.overwrite(form.TextPanel.body, p);
								form.TextPanel.render();

							},
							'keydown': function (inp, e) {
								if (e.shiftKey == false && e.getKey() == Ext.EventObject.ENTER)
								{
									inp.fireEvent("blur", inp);
									e.stopEvent();
									this.submit();
								}
							}.createDelegate(this)
						},
						listWidth : 500,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{[(values.Lpu_EndDate && values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыто "+ values.Lpu_EndDate /* Ext.util.Format.date(Date.parseDate(values.Lpu_EndDate.slice(0,10), "Y-m-d"), "d.m.Y")"*/ + ")" : values.Lpu_Nick ]}&nbsp;',
							'</div></tpl>'
						),
						width : 420,
						xtype : 'swlpulocalcombo'
					}, this.TextPanel
				]
			}],
			url : C_USER_SETCURLPU
			})],
			buttons : [{
				text : lang['vyibrat'],
				iconCls : 'ok16',
				handler : function(button, event) {
					this.submit();
				}.createDelegate(this)
			},
			HelpButton(this)],
			buttonAlign : "right"
		});
		sw.Promed.swSelectLpuWindow.superclass.initComponent.apply(this, arguments);
	} //end initComponent()
});