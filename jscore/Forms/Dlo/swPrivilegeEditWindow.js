/**
* swPrivilegeEditWindow - окно редактирования/добавления/просмотра льготы.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.002-14.08.2009
* @comment      Префикс для id компонентов PrivEF (PrivilegeEditForm)
*               tabIndex: 451
*
*
* @input data: action - действие (add, edit, view)
*              Person_id - ID человека
*              PersonPrivilege_id - ID льготы
*              PrivilegeType_id - тип льготы (при редактировании)
*              Server_id - ID сервера
*/

sw.Promed.swPrivilegeEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	doSave: function(ignore_age) {
		var current_window = this;
		var form = current_window.findById('PrivilegeEditForm');
        var base_form = form.getForm();
		var person_information = current_window.findById('PrivEF_PersonInformationFrame');
		var checking_for_regional_benefits = (ignore_age && ignore_age.checking_for_regional_benefits) ? ignore_age.checking_for_regional_benefits : null;

		if ( !form.getForm().isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var privilege_type_code = form.findById('PrivEF_PrivilegeTypeCombo').getFieldValue('PrivilegeType_Code');
		var privilege_type_sysnick = form.findById('PrivEF_PrivilegeTypeCombo').getFieldValue('PrivilegeType_SysNick');
		var privilege_type_name = form.findById('PrivEF_PrivilegeTypeCombo').getFieldValue('PrivilegeType_Name');

		var lgotEndDate = form.findById('PrivEF_Privilege_endDate').getValue();
		var lgotStartDate = form.findById('PrivEF_Privilege_begDate').getValue();
		var birth_date = person_information.getFieldValue('Person_Birthday');
		var death_date = person_information.getFieldValue('Person_deadDT');

		var third_birth_date = birth_date.add('Y', 3).add('D', -1);
		var sixth_birth_date = birth_date.add('Y', 6).add('D', -1);

		if (getRegionNick() != 'kz') {
			if ( Ext.isEmpty(birth_date) || typeof birth_date != 'object' ) {
				sw.swMsg.show({
					buttons: sw.swMsg.OK,
					icon: Ext.MessageBox.WARNING,
					msg: langs('У пациента не указана дата рождения'),
					title: langs('Ошибка')
				});
				return false;
			}

			// проверка даты рождения < даты начала льготы
			if ( birth_date > lgotStartDate ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Дата начала льготы не может быть раньше даты рождения.'), function() {
					form.findById('PrivEF_Privilege_begDate').focus();
				});
				return false;
			}

			// проверка даты начала льготы < даты окончания льготы
			if ( !Ext.isEmpty(lgotEndDate) && lgotStartDate >= lgotEndDate ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Дата окончания льготы не может быть раньше даты начала.'), function() {
					form.findById('PrivEF_Privilege_begDate').focus();
				});
				return false;
			}
			var dt = new Date();

			if (
				!isUserGroup(['SuperAdmin','ChiefLLO','minzdravdlo']) &&
				privilege_type_sysnick && !privilege_type_sysnick.inlist(['child_und_three_year','deti_6_mnogod','infarkt']) &&
				!Ext.isEmpty(lgotEndDate) && Ext.isEmpty(death_date) && lgotEndDate < new Date(dt.getFullYear(), dt.getMonth(), dt.getDate()) // для того чтобы не учитывать минуты
			) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						form.findById('PrivEF_Privilege_endDate').focus();
					},
					icon: Ext.Msg.WARNING,
					msg: langs('Дата закрытия льготы не может быть меньше текущей даты'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if (privilege_type_sysnick == 'child_und_three_year' && (Ext.isEmpty(lgotEndDate) || !lgotEndDate.equals(third_birth_date))) {
				var privName = getRegionNick() == 'perm'?'253':'«Дети первых 3 лет»';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId) {
						if ('yes' == buttonId) {
							form.findById('PrivEF_Privilege_endDate').setValue(third_birth_date);
						}
					},
					icon: Ext.Msg.WARNING,
					msg: langs('Для добавления льготы '+privName+' необходимо указать дату окончания. Установить дату наступления трехлетнего возраста?'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if (privilege_type_sysnick == 'deti_6_mnogod' && (Ext.isEmpty(lgotEndDate) || !lgotEndDate.equals(sixth_birth_date))) {
				var privName = getRegionNick() == 'perm'? '258' : '«Дети из многодетных семей в возрасте до 6 лет»';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId) {
						if ('yes' == buttonId) {
							form.findById('PrivEF_Privilege_endDate').setValue(sixth_birth_date);
						}
					},
					icon: Ext.Msg.WARNING,
					msg: langs('Для добавления льготы '+privName+' необходимо указать дату окончания. Установить дату наступления шестилетнего возраста?'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if (privilege_type_sysnick.inlist(['infarkt','infarkt_miok'])) {
				var infarktEndDate = infarktEndDate = new Date()
					.add(Date.MONTH, 6)
					.add(Date.DAY, 7*(getRegionNick()=='khak'?3:0))
					.add(Date.DAY, -1);

				if (Ext.isEmpty(lgotEndDate)) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId) {
							if ('yes' == buttonId) {
								form.findById('PrivEF_Privilege_endDate').setValue(infarktEndDate);
							}
						},
						icon: Ext.Msg.WARNING,
						msg: langs('Для льготной категории «Инфаркт миокарда (первые шесть месяцев)» должна быть указана дата окончания. Установить дату окончания  льготы  '+infarktEndDate.format('d.m.Y')+'?'),
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
		} else {
			
			var age = swGetPersonAgeDay(birth_date, new Date());
			var privMinAgeDay = form.findById('PrivEF_SubCategoryPrivTypeCombo').getFieldValue('SubCategoryPrivType_minAgeDay');
			var privMaxAgeDay = form.findById('PrivEF_SubCategoryPrivTypeCombo').getFieldValue('SubCategoryPrivType_maxAgeDay');
			
			var minDate = birth_date;
			if (!!privMinAgeDay) {
				minDate.add(Date.DAY, privMinAgeDay);
			}
			
			// проверка даты рождения/минимальной даты < даты начала льготы
			if ( minDate > lgotStartDate ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Дата начала льготы указана не верно. Дата начала льготы не может быть меньше ' + minDate.format('d.m.Y')), function() {
					form.findById('PrivEF_Privilege_begDate').focus();
				});
				return false;
			}
			
			// проверка даты начала льготы < даты окончания льготы
			if ( !Ext.isEmpty(lgotEndDate) && lgotStartDate >= lgotEndDate ) {
				sw.swMsg.alert(langs('Ошибка'), langs('Дата окончания льготы не может быть раньше даты начала.'), function() {
					form.findById('PrivEF_Privilege_begDate').focus();
				});
				return false;
			}
			
			// проверка даты смерти > даты окончания льготы
			if (
				!Ext.isEmpty(lgotEndDate) && !Ext.isEmpty(death_date) && lgotEndDate < death_date
			) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						form.findById('PrivEF_Privilege_endDate').focus();
					},
					icon: Ext.Msg.WARNING,
					msg: langs('Дата окончания льготы не может быть позже даты смерти пациента'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var post_data = new Object();

		if((getRegionNick() == 'msk')){
			post_data.ReceptFinance_id = base_form.findField('PrivilegeType_id').getFieldValue('ReceptFinance_id');
			if(ignore_age && ignore_age.checking_for_regional_benefits){
				post_data.checking_for_regional_benefits = 1;
			}else{
				post_data.checking_for_regional_benefits = (post_data.ReceptFinance_id != 1) ? 1 : null;
			}
		}else{
			post_data.checking_for_regional_benefits = 1;
		}

		if (base_form.findField('PrivilegeType_id').disabled) {
			post_data.PrivilegeType_id = base_form.findField('PrivilegeType_id').getValue();
		}
		if (base_form.findField('Privilege_begDate').disabled) {
			var begDate = base_form.findField('Privilege_begDate').getValue();
			post_data.Privilege_begDate = Ext.util.Format.date(begDate ? begDate : new Date(), 'd.m.Y');
		}
        if (base_form.findField('PrivilegeCloseType_id').disabled) {
            post_data.PrivilegeCloseType_id = base_form.findField('PrivilegeCloseType_id').getValue();
        }
		
		var loadMask = new Ext.LoadMask(Ext.get('PrivilegeEditWindow'), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		form.getForm().submit({
			failure: function(form_temp, action) {
				loadMask.hide();

				if (action.result && action.result.Error_Msg == 'YesNo') {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function (buttonId) {
							if ('yes' == buttonId) {
								if (action.result.Error_Code == 201 && action.result.maxEvnRecept_setDate) {
									form.findById('PrivEF_Privilege_endDate').setValue(action.result.maxEvnRecept_setDate);
								}
							}
						},
						icon: Ext.Msg.WARNING,
						msg: action.result.Alert_Msg,
						title: ERR_INVFIELDS_TIT
					});
					return false;
				} else if(getRegionNick() != 'kz' && action.result && action.result.nosnils) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId) {
							if ('yes' == buttonId) {
								//открыть форму «Человек: Редактирование» с установленным фокусом в поле СНИЛС
								getWnd("swPersonEditWindow").show({ action: "edit", Person_id: "" + form.findById('PrivEF_Person_id').getValue() + "", focused: 'Person_SNILS'});
							}
						},
						icon: Ext.Msg.WARNING,
						msg: langs('Создание льготы невозможно. У пациента отсутствует СНИЛС. Добавить СНИЛС?'),
						title: ERR_INVFIELDS_TIT
					});
				} else if (action.result && action.result.Error_Msg) {
					sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
				} else if (getRegionNick() == 'msk' && action.result && action.result.PrivilegeRegion_Count) {
					var win = current_window;
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId) {
							if ('yes' == buttonId) {
								win.doSave({checking_for_regional_benefits: 1});
							}
						},
						icon: Ext.Msg.WARNING,
						msg: langs('Добавить федеральную льготу и закрыть имеющиеся региональные льготы?'),
						title: ERR_INVFIELDS_TIT
					});
				} else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
				}
			},
			params: post_data,
			success: function(form_temp, action) {
				loadMask.hide();

				if (action.result)
				{
					if (action.result.PersonPrivilege_id && action.result.PersonPrivilege_id > 0)
					{
						var lpu_id = Ext.globalOptions.globals.lpu_id;
						var lpu_name = '';
						var person_privilege_id = action.result.PersonPrivilege_id;
						var privilege_type_code = null;
						var privilege_type_vcode = null;
						var privilege_type_name = null;
						var response = new Object();
						var server_id = form.findById('PrivEF_Server_id').getValue();

						var privilege_type_record = form.findById('PrivEF_PrivilegeTypeCombo').getStore().getById(form.findById('PrivEF_PrivilegeTypeCombo').getValue());
						if (privilege_type_record) {
							privilege_type_code = privilege_type_record.get('PrivilegeType_Code');
							privilege_type_vcode = privilege_type_record.get('PrivilegeType_VCode');
							privilege_type_name = privilege_type_record.get('PrivilegeType_Name');
						}

						var lpu_store = new Ext.db.AdapterStore({
							autoLoad: false,
							dbFile: 'Promed.db',
							fields: [
								{ name: 'Lpu_id', type: 'int' },
								{ name: 'Lpu_Nick', type: 'string' }
							], 
							key: 'Lpu_id',
							tableName: 'Lpu'
						});

						lpu_store.load({
							callback: function(records/*, options, success*/) {
								for ( var i = 0; i < records.length; i++ ) {
									if ( records[i].get('Lpu_id') == lpu_id ) {
										lpu_name = records[i].get('Lpu_Nick');
									}
								}
							},
							params: {
								where: 'where Lpu_id = ' + lpu_id
							}
						});

						response.Lpu_id = lpu_id;
						response.Lpu_Name = lpu_name;
						response.Person_Birthday = person_information.getFieldValue('Person_Birthday');
						response.Person_Firname = person_information.getFieldValue('Person_Firname');
						response.Person_id = form.findById('PrivEF_Person_id').getValue();
						response.PersonEvn_id = person_information.getFieldValue('PersonEvn_id');
						response.Person_Secname = person_information.getFieldValue('Person_Secname');
						response.Person_Surname = person_information.getFieldValue('Person_Surname');
						response.PersonPrivilege_id = person_privilege_id;
						response.Privilege_begDate = form.findById('PrivEF_Privilege_begDate').getValue();
						response.Privilege_endDate = form.findById('PrivEF_Privilege_endDate').getValue();
						response.Privilege_Refuse = '';
						response.PrivilegeType_Code = privilege_type_code;
						response.PrivilegeType_VCode = privilege_type_vcode;
						response.PrivilegeType_id = form.findById('PrivEF_PrivilegeTypeCombo').getValue();
						response.PrivilegeType_Name = privilege_type_name;
						response.Server_id = server_id;
						response.ReceptFinance_id = form.findById('PrivEF_PrivilegeTypeCombo').getFieldValue('ReceptFinance_id');

						current_window.callback({ PersonPrivilegeData: response });
						current_window.hide();
					}
					else
					{
						if (action.result.Error_Msg)
						{
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else
						{
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
						}
					}
				}
				else
				{
					if (action.result.Error_Msg)
					{
						sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else
					{
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
					}
				}
			}
		});
	},
	deletRefuse: function(){
		var form = this;
		Ext.Ajax.request({
			callback: function(options, success, response) {
				if (success) {
					showSysMsg('',"Отказ от льгот убран.", null, {closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
					form.buttons[1].disable();
					form.callback({ PersonPrivilegeData: null });
				}
			},
			params: {
				PersonRefuse_id: form.findById('PersonRefuse_id').getValue()
			},
			url: "/?c=PersonRefuse&m=deletePersonRefuse"
		});
	},
	setDisabled: function(disable) {
		var current_window = this;
        var form = current_window.findById('PrivilegeEditForm');
        var base_form = form.getForm();

        var field_arr = new Array(
            'PrivilegeType_id',
            'SubCategoryPrivType_id',
            'Privilege_begDate',
            'Privilege_endDate',
            'Diag_id',
            'DocumentPrivilegeType_id',
            'DocumentPrivilege_Ser',
            'DocumentPrivilege_Num',
            'DocumentPrivilege_begDate',
            'DocumentPrivilege_Org',
            'PrivilegeCloseType_id',
			'WhsDocumentCostItemType_id'
        );

        for (var i in field_arr) {
            if (base_form.findField(field_arr[i])) {
                var field = base_form.findField(field_arr[i]);
                if (disable || field.enable_blocked) {
                    field.disable();
                } else {
                    field.enable();
                }
            }
        }

		if (disable) {
			current_window.buttons[0].hide();
            current_window.buttons[0].disable();
		} else {
			current_window.buttons[0].show();
            current_window.buttons[0].enable();
		}
	},
	krymWhsDocumentCostItemTypeFilter: function() {
		var current_window = this;
		var form = current_window.findById('PrivilegeEditForm');
		var base_form = form.getForm();
		var person_id = base_form.findField('Person_id').getValue();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);
					base_form.findField('WhsDocumentCostItemType_id').getStore().filterBy(function (rec) {
						var nowDate = new Date();
						var isDlo = (rec.get('WhsDocumentCostItemType_IsDlo') == 2);
						var isValidDate = (
							(Ext.isEmpty(rec.get('PrivilegeType_begDate')) || rec.get('PrivilegeType_begDate') <= nowDate)
							&& (Ext.isEmpty(rec.get('PrivilegeType_endDate')) || rec.get('PrivilegeType_endDate') >= nowDate)
						);
						//есть прикрепление к МО пользователя и пользователь включен в группу «Руководитель ЛЛО МО»
						if (result[0] && result[0].isAttachment && isUserGroup('ChiefLLO')) {
							return (isValidDate && isDlo);
						} else {
							return ((rec.get('WhsDocumentCostItemType_Nick') != 'fl') && isValidDate && isDlo);
						}
					});
				}
			},
			params: {
				Person_id: person_id
			},
			url: "/?c=Privilege&m=checkPrivilegeMainOrServiceAttachment"
		});
	},
	whsDocumentCostItemTypeFilter: function() {
		var current_window = this;
		var form = current_window.findById('PrivilegeEditForm');
		var base_form = form.getForm();

		//блок для Саратова оставлен на всякий случай, на 01.06.2020 работа с Саратовом не ведется
		var globalOptions = getGlobalOptions();
		var cond = (
			globalOptions.region.nick == 'saratov'
			&& isUserGroup('OrgUser') && globalOptions.CurMedServiceType_SysNick
			&& globalOptions.CurMedServiceType_SysNick.inlist(['mekllo','minzdravdlo'])
		);

		base_form.findField('WhsDocumentCostItemType_id').getStore().clearFilter();

		if (!(isSuperAdmin() || isUserGroup('ChiefLLO')) && !cond && getRegionNick() != 'kz') { //ChiefLLO - Руководитель ЛЛО МО
			if (getRegionNick() == 'krym'){
				current_window.krymWhsDocumentCostItemTypeFilter();
			} else {
				base_form.findField('WhsDocumentCostItemType_id').getStore().filterBy(function (rec) {
					var nowDate = new Date();
					var isDlo = (rec.get('WhsDocumentCostItemType_IsDlo') == 2);
					var isValidDate = (
						(Ext.isEmpty(rec.get('PrivilegeType_begDate')) || rec.get('PrivilegeType_begDate') <= nowDate)
						&& (Ext.isEmpty(rec.get('PrivilegeType_endDate')) || rec.get('PrivilegeType_endDate') >= nowDate)
					);

					if (getRegionNick() == 'perm') {
						return ((!rec.get('WhsDocumentCostItemType_Nick').inlist(['kardio', 'fl'])) && isValidDate && isDlo)
					} else if (!getRegionNick().inlist(['perm','krym'])) {
						return ((rec.get('WhsDocumentCostItemType_Nick') != 'fl') && isValidDate && isDlo)
					}
				});
			}
		} else {
			//оставляем только программы с признаком ДЛО
			base_form.findField('WhsDocumentCostItemType_id').getStore().filterBy(function (rec){
				return (rec.get('WhsDocumentCostItemType_IsDlo') == 2);
			});
		}
	},
	id: 'PrivilegeEditWindow',
	initComponent: function() {
		var win = this;

		this.privilege_type_combo = new sw.Promed.SwBaseLocalCombo({
            fieldLabel: langs('Категория'),
            hiddenName: 'PrivilegeType_id',
            id: 'PrivEF_PrivilegeTypeCombo',
            codeField: 'PrivilegeType_VCode',
            valueField: 'PrivilegeType_id',
            displayField: 'PrivilegeType_Name',
            allowBlank: false,
            editable: false,
            anchor: '100%',
            lastQuery: '',
            tabIndex: 445,
            tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="border: 0;"><tr><td><font color="red">{PrivilegeType_VCode}</font></td><td>{PrivilegeType_Name}<td style="font: {[ Ext.isEmpty(values.PrivilegeType_endDate) ? "normal" : "normal; color: red;" ]};">{[Ext.isEmpty(values.PrivilegeType_endDate) ? "&nbsp;" : " (закрыта)" ]}</td></tr></table>',
				'</div></tpl>'),
            store: new Ext.db.AdapterStore({
                autoLoad: false,
                dbFile: 'Promed.db',
                fields: [
                    { name: 'PrivilegeType_id', type: 'int'},
                    { name: 'PrivilegeType_Code', type: 'int'},
                    { name: 'PrivilegeType_VCode', type: 'string'},
                    { name: 'PrivilegeType_Name', type: 'string'},
                    { name: 'PrivilegeType_SysNick', type: 'string'},
                    { name: 'ReceptDiscount_id', type: 'int'},
                    { name: 'ReceptFinance_id', type: 'int'},
                    { name: 'PrivilegeType_begDate', type: 'date', dateFormat: 'd.m.Y'},
                    { name: 'PrivilegeType_endDate', type: 'date', dateFormat: 'd.m.Y'},
                    { name: 'PrivilegeType_IsDoc', type: 'int'},
                    { name: 'PrivilegeType_IsNoz', type: 'int'},
                    { name: 'WhsDocumentCostItemType_Nick', type: 'string'}
                ],
                key: 'PrivilegeType_id',
                sortInfo: { field: 'PrivilegeType_VCode' },
                tableName: 'PrivilegeType',
                listeners: {
                    load: function(s) {
                        s.sortData('RlsClsntfr_Name');
                    }
                },
                sortData: function() {
                    var f = 'PrivilegeType_VCode';
                    var direction = 'ASC';
                    var f_type = 'int';

                    this.each(function(r) {
                        var val = r.get(f);
                        if (!Ext.isEmpty(val) && val != val*1) {
                            f_type = 'string';
                            return false;
                        }
                    });

                    var fn = function(r1, r2){
                        var v1 = r1.data[f], v2 = r2.data[f];

                        if (f_type == 'int') {
                            v1 = v1*1;
                            v2 = v2*1;
                        } else {
                            v1 = v1.toLowerCase();
                            v2 = v2.toLowerCase();
                        }

                        var ret = v1 > v2 ? 1 : (v1 < v2 ? -1 : 0);
                        return ret;
                    };
                    this.data.sort(direction, fn);
                    if(this.snapshot && this.snapshot != this.data){
                        this.snapshot.sort(direction, fn);
                    }
                }
            }),
            listeners:{
            	'change': function (combo, newValue, oldValue) {
                    this.setLinkedFields();
                },
                'select': function (combo, record, id) {
                    this.setLinkedFields();
                }
            },
            getSelectedRecordData: function() {
                var combo = this;
                var value = combo.getValue();
                var data = new Object();
                if (value > 0) {
                    var idx = this.getStore().findBy(function(record) {
                        return (record.get(combo.valueField) == value);
                    });
                    if (idx > -1) {
                        Ext.apply(data, this.getStore().getAt(idx).data);
                    }
                }
                return data;
            },
            setLinkedFields: function(event_name) {
                var base_form = win.findById('PrivilegeEditForm').getForm();

                base_form.findField('Privilege_begDate').setMaxValue(undefined);
                base_form.findField('Privilege_endDate').setMaxValue(undefined);
                base_form.findField('Privilege_begDate').setMinValue(undefined);
                base_form.findField('Privilege_endDate').setMinValue(undefined);

                var privilege_data = new Object();
                if (this.getValue() > 0) {
                    privilege_data = this.getSelectedRecordData();
                }

                if (!Ext.isEmpty(privilege_data.PrivilegeType_id)) {
                    if (!Ext.isEmpty(privilege_data.PrivilegeType_begDate)) {
                        base_form.findField('Privilege_begDate').setMinValue(privilege_data.PrivilegeType_begDate);
                        base_form.findField('Privilege_endDate').setMinValue(privilege_data.PrivilegeType_begDate);
                    }

                    if (!Ext.isEmpty(privilege_data.PrivilegeType_endDate)) {
                        base_form.findField('Privilege_begDate').setMaxValue(privilege_data.PrivilegeType_endDate);
                        base_form.findField('Privilege_endDate').setMaxValue(privilege_data.PrivilegeType_endDate);
                    }
                }

                if (getRegionNick() == 'kz' && (privilege_data.PrivilegeType_Code == '18' || privilege_data.ReceptDiscount_id != '3')) {
                    var age = swGetPersonAgeDay(win.findById('PrivEF_PersonInformationFrame').getFieldValue('Person_Birthday'), new Date());
                    base_form.findField('SubCategoryPrivType_id').showContainer();
                    base_form.findField('SubCategoryPrivType_id').setAllowBlank(false);
                    base_form.findField('SubCategoryPrivType_id').lastQuery = '';
                    base_form.findField('SubCategoryPrivType_id').getStore().clearFilter();
                    base_form.findField('SubCategoryPrivType_id').getStore().filterBy(function(rec) {
                        var test = true;

                        test = test && rec.get('SubCategoryPrivType_IsSocVulnGroup') == (privilege_data.PrivilegeType_Code == '18' ? 2 : 1);

                        if(!!rec.get('SubCategoryPrivType_minAgeDay')) {
                            test = test && rec.get('SubCategoryPrivType_minAgeDay') <= age;
                        }

                        if(!!rec.get('SubCategoryPrivType_maxAgeDay')) {
                            test = test && rec.get('SubCategoryPrivType_maxAgeDay') >= age;
                        }

                        return test;
                    });
                } else {
                    base_form.findField('SubCategoryPrivType_id').hideContainer();
                    base_form.findField('SubCategoryPrivType_id').setAllowBlank(true);
                }

                if (getRegionNick() != 'kz') {
					base_form.findField('Privilege_endDate').setDefaultValue();
				}

                var doc_visible = false;
                var diag_visible = false;

                if (!Ext.isEmpty(privilege_data.PrivilegeType_id)) {
                    //льгота явдялется федеральной или льгота является региональной и имеет признак «Документ на льготу»; настройка "Льготы социальные. Контроль на наличие данных документа, подтверждающего наличие льгот" включена
                    doc_visible = ((privilege_data && privilege_data.WhsDocumentCostItemType_Nick == 'fl') || (privilege_data && privilege_data.WhsDocumentCostItemType_Nick != 'fl' && privilege_data.PrivilegeType_IsDoc == 2 && getGlobalOptions().social_privilege_document_available_checking));

                    //льготная категория не связанна с программой ОНЛС и имеет признак «Нозология»; настройка "Льготы по нозологиям. Контроль на наличие диагноза" включена
                    diag_visible = (privilege_data && privilege_data.WhsDocumentCostItemType_Nick != 'fl' && privilege_data.PrivilegeType_IsNoz == 2 && getGlobalOptions().vzn_privilege_diag_available_checking);
                }

                base_form.findField('DocumentPrivilegeType_id').enable_blocked = !doc_visible;
                base_form.findField('DocumentPrivilege_Ser').enable_blocked = !doc_visible;
                base_form.findField('DocumentPrivilege_Num').enable_blocked = !doc_visible;
                base_form.findField('DocumentPrivilege_begDate').enable_blocked = !doc_visible;
                base_form.findField('DocumentPrivilege_Org').enable_blocked = !doc_visible;
                base_form.findField('DocumentPrivilegeType_id').allowBlank = !doc_visible;
                base_form.findField('DocumentPrivilege_Ser').allowBlank = !doc_visible;
                base_form.findField('DocumentPrivilege_Num').allowBlank = !doc_visible;
                base_form.findField('DocumentPrivilege_begDate').allowBlank = !doc_visible;
                base_form.findField('DocumentPrivilege_Org').allowBlank = !doc_visible;
                if (doc_visible) {
                    base_form.findField('DocumentPrivilegeType_id').ownerCt.show();
                } else {
                    base_form.findField('DocumentPrivilegeType_id').ownerCt.hide();
                }

                if (base_form.findField('Diag_id').getStore().baseParams.PrivilegeType_id != this.getValue()) {
                    if (event_name != 'set_by_id') {
                        base_form.findField('Diag_id').fullReset();
                    }
                    base_form.findField('Diag_id').getStore().baseParams.PrivilegeType_id = this.getValue();
                }

                base_form.findField('Diag_id').enable_blocked = !diag_visible;
                base_form.findField('Diag_id').allowBlank = !diag_visible;
                if (diag_visible) {
                    base_form.findField('Diag_id').showContainer();
                } else {
                    base_form.findField('Diag_id').hideContainer();
                }

				base_form.findField('PrivilegeCloseType_id').setDefaultValue();
				//устанавливаем в поле Программа ЛЛО программу редактируемой льготы
                if (win.action == 'edit' && getRegionNick() != 'kz') {
                	var PrivilegeType_id = base_form.findField('PrivilegeType_id').getValue();
					var pt_idx = base_form.findField('PrivilegeType_id').getStore().findBy(function(rec){
						return (rec.get('PrivilegeType_id') == PrivilegeType_id);
					});
					var WhsDocumentCostItemType_Nick = '';
					if (pt_idx >= 0) {
						WhsDocumentCostItemType_Nick = base_form.findField('PrivilegeType_id').getStore().getAt(pt_idx).get('WhsDocumentCostItemType_Nick');
					}

					var wdcit_idx = base_form.findField('WhsDocumentCostItemType_id').getStore().findBy(function (rec) {
						return (rec.get('WhsDocumentCostItemType_Nick') == WhsDocumentCostItemType_Nick);
					});

					var WhsDocumentCostItemType_id = '';
					if (wdcit_idx >= 0) {
						WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getStore().getAt(wdcit_idx).get('WhsDocumentCostItemType_id');
					}
					if (!Ext.isEmpty(WhsDocumentCostItemType_id)) {
						base_form.findField('WhsDocumentCostItemType_id').setValue(WhsDocumentCostItemType_id);
						base_form.findField('WhsDocumentCostItemType_id').fireEvent('change', base_form.findField('WhsDocumentCostItemType_id'), base_form.findField('WhsDocumentCostItemType_id').getValue());
					} else {
						base_form.findField('PrivilegeType_id').clearValue();
						base_form.findField('PrivilegeType_id').getStore().filterBy(function (rec) {
							return false;
						});
					}
				}
				//неиспользуемые поля нужно заблокировать, таким образом они не будут переданы при сохранении и будут автоматически очищены в данных запроса
                win.setDisabled(win.action == 'view');
                win.syncShadow();
            }
        });

        this.diag_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Диагноз'),
            hiddenName: 'Diag_id',
            displayField: 'Diag_Name',
            valueField: 'Diag_id',
            editable: true,
            allowBlank: false,
            width: 300,
            listWidth: 300,
            triggerAction: 'all',
			tabIndex: 446,
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'Diag_id'
                }, [
                    {name: 'Diag_id', mapping: 'Diag_id'},
                    {name: 'Diag_Code', mapping: 'Diag_Code'},
                    {name: 'Diag_Name', mapping: 'Diag_Name'}
                ]),
                url: '/?c=Privilege&m=loadDiagByPrivilegeTypeCombo'
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table><tr><td style="width: 40px;"><font color="red">{Diag_Code}</font>&nbsp;</td><td>{Diag_Name}&nbsp;</td></tr></table>',
                '</div></tpl>'
            )
        });

        this.document_type_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Вид документа'),
            hiddenName: 'DocumentPrivilegeType_id',
            displayField: 'DocumentPrivilegeType_Name',
            valueField: 'DocumentPrivilegeType_id',
            editable: true,
            allowBlank: false,
            width: 300,
            listWidth: 300,
            triggerAction: 'all',
			tabIndex: 447,
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'DocumentPrivilegeType_id'
                }, [
                    {name: 'DocumentPrivilegeType_id', mapping: 'DocumentPrivilegeType_id'},
                    {name: 'DocumentPrivilegeType_Code', mapping: 'DocumentPrivilegeType_Code'},
                    {name: 'DocumentPrivilegeType_Name', mapping: 'DocumentPrivilegeType_Name'}
                ]),
                url: '/?c=Privilege&m=loadDocumentPrivilegeTypeCombo'
            }),
            trigger2Class: 'x-form-plus-trigger',
            onTrigger2Click: function() {
                var combo = this;
                if (!combo.disabled) {
                    getWnd('swDocumentPrivilegeTypeAddWindow').show({
                        callback: function(data) {
                            if (!Ext.isEmpty(data.DocumentPrivilegeType_id)) {
                                combo.setValueById(data.DocumentPrivilegeType_id);
                            }
                        }
                    });
                }
            }
        });

        this.WhsDocumentCostItemTypeCombo = new sw.Promed.SwBaseLocalCombo({
			allowBlank: false,
			codeField: 'WhsDocumentCostItemType_Code',
			displayField: 'WhsDocumentCostItemType_Name',
			id: 'PrivEF_WhsDocumentCostItemTypeCombo',
			editable: false,
			fieldLabel: 'Программа ЛЛО',
			hiddenName: 'WhsDocumentCostItemType_id',
			anchor: '100%',
			lastQuery: '',
			tabIndex: 444,
			listeners: {
			'change': function(combo, newValue, oldValue) {
				var base_form = win.findById('PrivilegeEditForm').getForm();
				var PTCombo = base_form.findField('PrivilegeType_id');
				PTCombo.clearFilter();
				var wdcit_index = combo.getStore().findBy(function(rec){
					return (rec.get('WhsDocumentCostItemType_id') == newValue);
				});
				var wdcit_nick = '';
				if (wdcit_index >= 0) {
						wdcit_nick = combo.getStore().getAt(wdcit_index).get('WhsDocumentCostItemType_Nick');
				}
				if (getRegionNick() != 'kz') {
					PTCombo.getStore().filterBy(function (rec) {
						return (rec.get('WhsDocumentCostItemType_Nick') == wdcit_nick)
					});
				}
				if (Ext.isEmpty(newValue)){
					if (getRegionNick() != 'kz') {
						base_form.findField('PrivilegeType_id').disable();
					}
					base_form.findField('WhsDocumentCostItemType_id').fireEvent('select', base_form.findField('WhsDocumentCostItemType_id'), base_form.findField('WhsDocumentCostItemType_id').getValue());
				} else {
					base_form.findField('PrivilegeType_id').enable();
				}
			}.createDelegate(this),
			'select': function (combo, record, index){
				var base_form = win.findById('PrivilegeEditForm').getForm();
				base_form.findField('PrivilegeType_id').clearValue();
				win.findById('PrivEF_Privilege_endDate').setValue('');
				base_form.findField('PrivilegeCloseType_id').clearValue();
			}.createDelegate(this),

		},
			store: new Ext.db.AdapterStore({
				autoLoad: false,
				dbFile: 'Promed.db',
				key: 'WhsDocumentCostItemType_id',
				fields: [
					{ name: 'WhsDocumentCostItemType_id', mapping: 'WhsDocumentCostItemType_id', type: 'int' },
					{ name: 'WhsDocumentCostItemType_Code', mapping: 'WhsDocumentCostItemType_Code', type: 'int' },
					{ name: 'WhsDocumentCostItemType_Name', mapping: 'WhsDocumentCostItemType_Name', type: 'string' },
					{ name: 'WhsDocumentCostItemType_Nick', mapping: 'WhsDocumentCostItemType_Nick', type: 'string' },
					{ name: 'WhsDocumentCostItemType_begDate', mapping: 'WhsDocumentCostItemType_begDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'WhsDocumentCostItemType_endDate', mapping: 'WhsDocumentCostItemType_endDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'WhsDocumentCostItemType_IsDlo', mapping: 'WhsDocumentCostItemType_IsDlo', type: 'int' },
					{ name: 'DrugFinance_id', mapping: 'DrugFinance_id', type: 'int' },
					{ name: 'PersonRegisterType_id', mapping: 'PersonRegisterType_id', type: 'int' }
				],
				listeners: {
					'load': function(store, records, index) {
						store.filterBy(function (rec){
							return (rec.get('WhsDocumentCostItemType_IsDlo') == 2);
						});
					}.createDelegate(this)
				},
				sortInfo: {
					field: 'WhsDocumentCostItemType_id'
				},
				tableName: 'WhsDocumentCostItemType'
			}),
			//tabIndex: ...,
			tpl: new Ext.XTemplate(
			'<tpl for="."><div class="x-combo-list-item">',
			'<font color="red">{WhsDocumentCostItemType_Code}</font>&nbsp;{WhsDocumentCostItemType_Name}',
			'</div></tpl>'
		),
			validateOnBlur: true,
			valueField: 'WhsDocumentCostItemType_id',
		});


		Ext.apply(this, {
			buttons: [
			{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: 452,
				text: BTN_FRMSAVE
			},
			{
				handler: function() {
					win.deletRefuse();
				},
				iconCls: 'delete16',
				tabIndex: 457,
				hidden:true,
				text: "Отказ от льгот"
			},{
				text: '-'
			},
			HelpButton(this, 453),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: 454,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanel({
				button2Callback: function(callback_data) {
					var current_window = Ext.getCmp('PrivilegeEditWindow');

					current_window.findById('PrivEF_Server_id').setValue(callback_data.Server_id);
					current_window.findById('PrivEF_PersonInformationFrame').load({ Person_id: callback_data.Person_id, Server_id: callback_data.Server_id });
				},
				button2OnHide: function() {
					var current_window = Ext.getCmp('PrivilegeEditWindow');

					if ( !current_window.findById('PrivEF_PrivilegeTypeCombo').disabled )
					{
						current_window.findById('PrivEF_PrivilegeTypeCombo').focus(false);
					}
					else if ( !current_window.findById('PrivEF_Privilege_begDate').disabled )
					{
						current_window.findById('PrivEF_Privilege_begDate').focus(true);
					}
					else
					{
						current_window.buttons[4].focus();
					}
				},
				button3OnHide: function() {
					var current_window = Ext.getCmp('PrivilegeEditWindow');

					if ( !current_window.findById('PrivEF_PrivilegeTypeCombo').disabled )
					{
						current_window.findById('PrivEF_PrivilegeTypeCombo').focus(false);
					}
					else if ( !current_window.findById('PrivEF_Privilege_begDate').disabled )
					{
						current_window.findById('PrivEF_Privilege_begDate').focus(true);
					}
					else
					{
						current_window.buttons[4].focus();
					}
				},
				id: 'PrivEF_PersonInformationFrame'
			}),
			new Ext.form.FormPanel({
				autoHeight: true,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'PrivilegeEditForm',
				labelAlign: 'right',
                labelWidth: 120,
				items: [{
					id: 'PrivEF_Server_id',
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				}, {
					id: 'PrivEF_Person_id',
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					id: 'PersonRefuse_id',
					name: 'PersonRefuse_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					id: 'PersonPrivilege_IsAddMZ',
					name: 'PersonPrivilege_IsAddMZ',
					value: 0,
					xtype: 'hidden'
				},
				{
					id: 'PrivEF_PersonPrivilege_id',
					name: 'PersonPrivilege_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					id: 'PrivEF_hasRecepts',
					name: 'hasRecepts',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'DocumentPrivilege_id',
					xtype: 'hidden'
				},
				this.WhsDocumentCostItemTypeCombo,
				this.privilege_type_combo,
				{
					allowBlank: true,
					anchor: '100%',
					fieldLabel: 'Подкатегория',
					id: 'PrivEF_SubCategoryPrivTypeCombo',
					hiddenName: 'SubCategoryPrivType_id',
					comboSubject: 'SubCategoryPrivType',
					moreFields: [
						{ name: 'SubCategoryPrivType_IsSocVulnGroup', mapping: 'SubCategoryPrivType_IsSocVulnGroup' },
						{ name: 'SubCategoryPrivType_minAgeDay', mapping: 'SubCategoryPrivType_minAgeDay' },
						{ name: 'SubCategoryPrivType_maxAgeDay', mapping: 'SubCategoryPrivType_maxAgeDay' }
					],
					tabIndex: 445,
					xtype: 'swcommonsprcombo',
					listeners:{
						'change':function (combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function (rec) {
								return (rec.get(combo.valueField) == newValue);
							});
							if (index >= 0) {
								var record = combo.getStore().getAt(index);
								combo.fireEvent('select', combo, record);
							}
						}.createDelegate(this),
						'select': function (combo, record, id) {
							var Person_Birthday = this.findById('PrivEF_PersonInformationFrame').getFieldValue('Person_Birthday');
							var Person_deadDT = this.findById('PrivEF_PersonInformationFrame').getFieldValue('Person_deadDT');
							if (this.action == 'add' && Ext.isEmpty(Person_deadDT)) {
								if(record.get('SubCategoryPrivType_maxAgeDay') > 0 && record.get('SubCategoryPrivType_maxAgeDay') < 1000000) {
									var endDate = Person_Birthday.add(Date.DAY, record.get('SubCategoryPrivType_maxAgeDay') + 1);
									this.findById('PrivEF_Privilege_endDate').setValue(endDate);
								} else {
									this.findById('PrivEF_Privilege_endDate').setValue('');
								}
							}
						}.createDelegate(this)
					},
				},
				this.diag_combo,
				{
					xtype: 'fieldset',
					title: langs('Документ о праве на льготу'),
					autoHeight: true,
					style: 'padding: 3px; margin-bottom: 7px; display: block;',
					labelWidth: 210,
					items: [
						this.document_type_combo,
						{
							xtype: 'textfield',
							fieldLabel: 'Серия документа',
							name: 'DocumentPrivilege_Ser',
							tabIndex: 447,
							width: 300
						}, {
							xtype: 'textfield',
							fieldLabel: 'Номер документа',
							name: 'DocumentPrivilege_Num',
							tabIndex: 447,
							width: 300
						}, {
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							fieldLabel: 'Дата выдачи документа',
							name: 'DocumentPrivilege_begDate',
							tabIndex: 447
						}, {
							xtype: 'textfield',
							fieldLabel: 'Организация, выдавшая документ',
							name: 'DocumentPrivilege_Org',
							tabIndex: 447,
							width: 300
						}]
				}, {
					allowBlank: false,
					fieldLabel: langs('Начало'),
					format: 'd.m.Y',
					id: 'PrivEF_Privilege_begDate',
					listeners: {
						'keydown': function (inp, e) {
							if (!e.shiftKey && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								inp.ownerCt.findById('PrivEF_Privilege_endDate').focus(true);
							}
						}, 
						'change': function(combo, newValue, oldValue) {
							blockedDateAfterPersonDeath('personpanelid', 'PrivEF_PersonInformationFrame', combo, newValue, oldValue);

							var base_form = win.findById('PrivilegeEditForm').getForm();
							var end_date_field = base_form.findField('Privilege_endDate');
							end_date_field.setDefaultValue();
						}.createDelegate(this)
					},
					name: 'Privilege_begDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: 451,
					validateOnBlur: true,
					xtype: 'swdatefield'
				}, {
					fieldLabel: langs('Окончание'),
					format: 'd.m.Y',
					id: 'PrivEF_Privilege_endDate',
					listeners: {
						'keydown': function (inp, e) {
							if (e.shiftKey && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								inp.ownerCt.findById('PrivEF_Privilege_begDate').focus(true);
							}
						},
						'change': function() {
                            var base_form = win.findById('PrivilegeEditForm').getForm();
							var close_type_combo = base_form.findField('PrivilegeCloseType_id');
							close_type_combo.setAllowBlank(Ext.isEmpty(this.getValue()) || win.action == 'view');
							close_type_combo.setDefaultValue();
						}
					},
					name: 'Privilege_endDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: 451,
					xtype: 'swdatefield',
					setDefaultValue: function() {
						if (win.action != 'view' && !this.disabled) {
							var base_form = win.findById('PrivilegeEditForm').getForm();
							var beg_date = base_form.findField('Privilege_begDate').getValue();
							var pt_data = win.privilege_type_combo.getSelectedRecordData();

							if (pt_data && pt_data.WhsDocumentCostItemType_Nick == 'acs' && !Ext.isEmpty(beg_date)) { //asc - ССЗ
								var end_date = beg_date;
								end_date.setFullYear(beg_date.getFullYear() + 1);

								// новое значение не должно быть больше максимального
								if (!Ext.isEmpty(this.maxValue) && end_date > this.maxValue) {
									end_date = this.maxValue;
								}

								this.setValue(end_date);
							}
						}
					}
				}, {
					xtype: 'swcommonsprcombo',
					comboSubject: 'PrivilegeCloseType',
					fieldLabel: 'Причина закрытия',
					hiddenName: 'PrivilegeCloseType_id',
					tabIndex: 451,
					width: 300,
					setDefaultValue: function() {
						var base_form = win.findById('PrivilegeEditForm').getForm();
						var end_date = base_form.findField('Privilege_endDate').getValue();
						var person_dead_dt = Ext.getCmp('PrivEF_PersonInformationFrame').getFieldValue('Person_deadDT');
						var close_type_code = null;
						var close_type_id = -1;
						this.enable_blocked = false;

						if (win.action != 'view') {
							if (!Ext.isEmpty(person_dead_dt)) { //если для человека указана дата смерти
								close_type_code = 1; //1 - Смерть
								this.enable_blocked = true;
							} else if (!Ext.isEmpty(end_date)) {
								var pt_data = win.privilege_type_combo.getSelectedRecordData();

								if (pt_data && pt_data.WhsDocumentCostItemType_Nick == 'acs') {
									close_type_code = 6; //6 - Ограниченный период действия льготы
									this.enable_blocked = true;
								} else {
									close_type_code = 4; //4 - Прочее
								}
							} else {
								close_type_id = null; //пустое значение
							}

							if (!Ext.isEmpty(close_type_code)) { //опредежение id по коду
								var index = this.getStore().findBy(function(record) {
									return (record.get('PrivilegeCloseType_Code') == close_type_code);
								});
								if (index >= 0) {
									close_type_id = this.getStore().getAt(index).get('PrivilegeCloseType_id');
								}
							}

							if (close_type_id != -1) {
								this.setValue(close_type_id);
							}
							win.setDisabled(false);
						}
					}
				}],
				keys: [{
					fn: function(/*inp, e*/) {
						this.doSave();
					},
					key: Ext.EventObject.ENTER,
					scope: this,
					stopEvent: true
				}],
				reader: new Ext.data.JsonReader({
					success: function() { alert('All Right!'); }
				}, [
					{ name: 'PersonPrivilege_id' },
					{ name: 'Privilege_begDate' },
					{ name: 'Privilege_endDate' },
					{ name: 'PrivilegeType_id' },
					{ name: 'SubCategoryPrivType_id' },
					{ name: 'PersonPrivilege_IsAddMZ' },
                    { name: 'Diag_id' },
                    { name: 'DocumentPrivilege_id' },
                    { name: 'DocumentPrivilegeType_id' },
                    { name: 'DocumentPrivilege_Ser' },
                    { name: 'DocumentPrivilege_Num' },
                    { name: 'DocumentPrivilege_begDate' },
                    { name: 'DocumentPrivilege_Org' },
                    { name: 'PrivilegeCloseType_id' }
				]),
				url: C_PRIV_SAVE
			})]
		});
		sw.Promed.swPrivilegeEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			e.stopEvent();

			if (e.browserEvent.stopPropagation)
				e.browserEvent.stopPropagation();
			else
				e.browserEvent.cancelBubble = true;

			if (e.browserEvent.preventDefault)
				e.browserEvent.preventDefault();
			else
				e.browserEvent.returnValue = false;

			e.returnValue = false;

			if (Ext.isIE)
			{
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			if (e.getKey() == Ext.EventObject.F6)
			{
				Ext.getCmp('PrivEF_PersonInformationFrame').panelButtonClick(1);
				return false;
			}

			if (e.getKey() == Ext.EventObject.F10)
			{
				Ext.getCmp('PrivEF_PersonInformationFrame').panelButtonClick(2);
				return false;
			}

			if (e.getKey() == Ext.EventObject.F11)
			{
				Ext.getCmp('PrivEF_PersonInformationFrame').panelButtonClick(3);
				return false;
			}

			if (e.getKey() == Ext.EventObject.F12)
			{
				if (e.ctrlKey)
				{
					Ext.getCmp('PrivEF_PersonInformationFrame').panelButtonClick(5);
				}
				else
				{
					Ext.getCmp('PrivEF_PersonInformationFrame').panelButtonClick(4);
				}
				return false;
			}
		},
		key: [
			Ext.EventObject.F6,
			Ext.EventObject.F10,
			Ext.EventObject.F11,
			Ext.EventObject.F12
		],
		scope: this,
		stopEvent: false
	}, {
		alt: true,
		fn: function(inp, e) {
			//var copy = false;
			//var print = false;

			e.stopEvent();

			if (e.browserEvent.stopPropagation)
				e.browserEvent.stopPropagation();
			else
				e.browserEvent.cancelBubble = true;

			if (e.browserEvent.preventDefault)
				e.browserEvent.preventDefault();
			else
				e.browserEvent.returnValue = false;

			e.returnValue = false;

			if (Ext.isIE)
			{
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			var current_window = Ext.getCmp('PrivilegeEditWindow');

			if (e.getKey() == Ext.EventObject.J)
			{
				current_window.hide();
				return false;
			}

			if ('view' != current_window.action)
			{
				current_window.doSave();
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPrivilegeEditWindow.superclass.show.apply(this, arguments);		

		var current_window = this;
		var form = current_window.findById('PrivilegeEditForm');
		var base_form = form.getForm();
		
		current_window.action = null;
		current_window.callback = Ext.emptyFn;
		current_window.onHide = Ext.emptyFn;
		current_window.ARMType = '';

		var loadMask = new Ext.LoadMask(Ext.get('PrivilegeEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

		form.getForm().reset();
		form.findById('PrivEF_PrivilegeTypeCombo').getStore().clearFilter();

		if ( !arguments[0] )
		{
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'));
			return false;
		}

		if (arguments[0].action)
		{
			current_window.action = arguments[0].action;
		}

		if (arguments[0].callback)
		{
			current_window.callback = arguments[0].callback;
		}

		if (arguments[0].onHide)
		{
			current_window.onHide = arguments[0].onHide;
		}

		if (arguments[0].ARMType)
		{
			current_window.ARMType = arguments[0].ARMType;
		}

		form.getForm().setValues(arguments[0]);

		//сброс блокировок
        base_form.findField('PrivilegeType_id').enable_blocked = false;
        base_form.findField('Privilege_begDate').enable_blocked = false;
        base_form.findField('Privilege_endDate').enable_blocked = false;
        base_form.findField('PrivilegeCloseType_id').enable_blocked = false;

        base_form.findField('PrivilegeCloseType_id').setAllowBlank(true);

		var person_id = base_form.findField('Person_id').getValue();
        var server_id = base_form.findField('Server_id').getValue();
		var person_privilege_id = base_form.findField('PersonPrivilege_id').getValue();
		var privilege_type_id = base_form.findField('PrivilegeType_id').getValue();

		current_window.buttons[1].disable();

		current_window.findById('PrivEF_PersonInformationFrame').load({
			Person_id: person_id,
			Server_id: server_id,
			callback: function() {
				var birth_date = Ext.getCmp('PrivEF_PersonInformationFrame').getFieldValue('Person_Birthday');
				this.findById('PrivEF_Privilege_begDate').setMinValue(birth_date.add(Date.DAY, -1));

				var Person_deadDT = Ext.getCmp('PrivEF_PersonInformationFrame').getFieldValue('Person_deadDT');
				if (!Ext.isEmpty(Person_deadDT)) {
					if (current_window.action == 'add') {
						base_form.findField('Privilege_endDate').setValue(Person_deadDT);
						base_form.findField('Privilege_endDate').enable_blocked = !isUserGroup(['SuperAdmin', 'ChiefLLO', 'minzdravdlo']);

						if (base_form.findField('Privilege_begDate').getValue() > Person_deadDT) {
							base_form.findField('Privilege_begDate').setValue(Person_deadDT);
						}
					}

                    base_form.findField('PrivilegeCloseType_id').setDefaultValue();

					current_window.setDisabled(false);
				}
			}.createDelegate(this)
		});
		current_window.findById('PrivEF_PersonInformationFrame').setDisabled(false);
		base_form.findField('SubCategoryPrivType_id').hideContainer();
		base_form.findField('SubCategoryPrivType_id').setAllowBlank(true);

		if (getRegionNick() == 'kz') {
			this.WhsDocumentCostItemTypeCombo.hideContainer();
			this.WhsDocumentCostItemTypeCombo.setAllowBlank(true);
		} else {
			this.WhsDocumentCostItemTypeCombo.showContainer();
			this.WhsDocumentCostItemTypeCombo.setAllowBlank(false);
		}

		this.syncShadow();

		switch (current_window.action) {
			case 'add':
				current_window.setTitle(WND_DLO_LGOTADD);
				current_window.whsDocumentCostItemTypeFilter();

                base_form.findField('Privilege_begDate').setValue(new Date());
                base_form.findField('Privilege_begDate').enable_blocked = (getRegionNick() == 'perm' && !isUserGroup(['SuperAdmin','ChiefLLO','minzdravdlo']));

				loadMask.hide();
				if (!Ext.isEmpty(base_form.findField('PrivilegeType_id').getValue())) {
					base_form.findField('PrivilegeType_id').fireEvent('change', base_form.findField('PrivilegeType_id'), base_form.findField('PrivilegeType_id').getValue());
				} else {
                    base_form.findField('PersonPrivilege_id').setValue(0);
				}
				if (getGlobalOptions().isMinZdrav || current_window.ARMType.inlist(['superadmin','minzdravdlo'])){
					base_form.findField('PersonPrivilege_IsAddMZ').setValue(2);
				}
                base_form.findField('WhsDocumentCostItemType_id').focus(true, 250);
				//form.getForm().clearInvalid();
				current_window.setDisabled(false);
				if (getRegionNick() != 'kz') {
					base_form.findField('PrivilegeType_id').disable();
				}
				break;

			case 'edit':
				current_window.setTitle(WND_DLO_LGOTEDIT);

				if(getRegionNick() == 'perm' && isSuperAdmin()){
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								if ( response.responseText ) {
									var result  = Ext.util.JSON.decode(response.responseText);
									if(result) {
										if(result[0] && result[0].PersonRefuse_id) {
                                            base_form.findField('PersonRefuse_id').setValue(result[0].PersonRefuse_id);
											current_window.buttons[1].show();
											current_window.buttons[1].enable();
										}
									}
								}
							}
						},
						params: {
							Person_id: person_id
						},
						url: "/?c=PersonRefuse&m=getPersonRefuseId"
					});
				}

				form.getForm().load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы с сервера'));
					},
					params: {
						Person_id: person_id,
						PersonPrivilege_id: person_privilege_id,
						PrivilegeType_id: privilege_type_id,
						Server_id: server_id
					},
					success: function() {
						current_window.whsDocumentCostItemTypeFilter();

						loadMask.hide();

						var hasRecepts = (form.findById('PrivEF_hasRecepts').getValue() == 1);

                        base_form.findField('PrivilegeType_id').enable_blocked = hasRecepts;
                        base_form.findField('Privilege_begDate').enable_blocked = (hasRecepts || !isUserGroup(['SuperAdmin','ChiefLLO','minzdravdlo']));
                        base_form.findField('Privilege_endDate').enable_blocked = (!Ext.isEmpty(form.findById('PrivEF_Privilege_endDate').getValue()) && !isUserGroup(['SuperAdmin','ChiefLLO','minzdravdlo']));

                        base_form.findField('Diag_id').setValueById(base_form.findField('Diag_id').getValue());

						base_form.findField('PrivilegeType_id').setLinkedFields('set_by_id');
                        base_form.clearInvalid();

                        current_window.setDisabled(false);
						base_form.findField('WhsDocumentCostItemType_id').disable();
					},
					url: C_PRIV_LOAD_EDIT
				});
				break;

			case 'view':
				current_window.setTitle(WND_DLO_LGOTVIEW);
				current_window.setDisabled(true);

				if(getRegionNick() == 'perm' && isSuperAdmin()){
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success)
							{
								if ( response.responseText )
								{

									var result  = Ext.util.JSON.decode(response.responseText);
									if(result[0] && result[0].PersonRefuse_id){
										form.findById('PersonRefuse_id').setValue(result[0].PersonRefuse_id);
										current_window.buttons[1].show();
										current_window.buttons[1].enable();
									}
								}
							}
						},
						params: {
							Person_id:person_id
						},
						url: "/?c=PersonRefuse&m=getPersonRefuseId"
					});
				}

				form.getForm().load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы с сервера'));
					},
					params: {
						Person_id: person_id,
						PersonPrivilege_id: person_privilege_id,
						PrivilegeType_id: privilege_type_id,
						Server_id: server_id
					},
					success: function() {
						loadMask.hide();
						current_window.buttons[4].focus();
						form.getForm().clearInvalid();

						base_form.findField('Diag_id').setValueById(base_form.findField('Diag_id').getValue());

                        base_form.findField('PrivilegeType_id').setLinkedFields('set_by_id');

						current_window.findById('PrivEF_PersonInformationFrame').setDisabled(true);

						base_form.findField('WhsDocumentCostItemType_id').getStore().clearFilter();
						var PrivilegeTypeCombo = base_form.findField('PrivilegeType_id');
						var PrivilegeType_id = PrivilegeTypeCombo.getValue();
						var PT_index = PrivilegeTypeCombo.getStore().findBy(function(rec){
							return (rec.get('PrivilegeType_id') == PrivilegeType_id);
						});
						var WhsDocumentCostItemType_Nick = '';
						if(PT_index >= 0) {
							 WhsDocumentCostItemType_Nick = base_form.findField('PrivilegeType_id').getStore().getAt(PT_index).get('WhsDocumentCostItemType_Nick');
						}
						var WDCIT_index = base_form.findField('WhsDocumentCostItemType_id').getStore().findBy(function(rec){
							return (rec.get('WhsDocumentCostItemType_Nick') == WhsDocumentCostItemType_Nick);
						});
						var WhsDocumentCostItemType_id = '';
						if (WDCIT_index >= 0) {
							var WhsDocumentCostItemType_id = base_form.findField('WhsDocumentCostItemType_id').getStore().getAt(WDCIT_index).get('WhsDocumentCostItemType_id');
						}
						if (!Ext.isEmpty(WhsDocumentCostItemType_id)) {
							base_form.findField('WhsDocumentCostItemType_id').setValue(WhsDocumentCostItemType_id);
						}
					},
					url: C_PRIV_LOAD_EDIT
				});
				break;
		}
	},
	width: 700
});