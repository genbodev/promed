/**
 * Панель информации о пациенте
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.PersonInfoPanel', {
	extend: 'Ext6.Panel',
	//controller: 'myview',
	userMedStaffFact: null,
	additionalFields: [],
	animCollapse: false ,
	userCls: 'PersonInfoPanel',
	border: false,
	bodyStyle: 'background: #fff;',
	readOnly: false,
	layout: 'border',
	height: 170,
	narrowPanel: false,
	button1Callback: Ext6.emptyFn,
	button2Callback: Ext6.emptyFn,
	button3Callback: Ext6.emptyFn,
	button4Callback: Ext6.emptyFn,
	button5Callback: Ext6.emptyFn,
	button6Callback: Ext6.emptyFn,
	button1OnHide: Ext6.emptyFn,
	button2OnHide: Ext6.emptyFn,
	button3OnHide: Ext6.emptyFn,
	button4OnHide: Ext6.emptyFn,
	button5OnHide: Ext6.emptyFn,
	button6OnHide: Ext6.emptyFn,
	collectAdditionalParams: Ext6.emptyFn,
	personId: null,
	serverId: null,
	addToolbar: true,
	setParams: function(params) {
		if ( typeof params != 'object' ) {
			return false;
		}

		this.personId = params.Person_id;
		this.serverId = params.Server_id;
	},
	load: function(params) {
		var callback_param = Ext6.emptyFn;
		if(this.addToolbar){
			this.PToolbar.setVisible(!params.noToolbar);
		}

		// сразу скрываем, чтобы не мешалась
		this.PersonFrameContent.hide();
		this.setHeight(45);

		if ( params.callback ) {
			callback_param = params.callback;
		}

		var callback = function() {
			this.setPersonTitle();
			this.checkIsDead();
			callback_param();
		}.createDelegate(this);

		this.personId = params.Person_id;
		this.serverId = params.Server_id;
		this.PersonEvn_id = params.PersonEvn_id;
		this.userMedStaffFact = params.userMedStaffFact;
		this.DataView.getStore().removeAll();
		if (params.dataToLoad) {
			this.DataView.getStore().loadData(params.dataToLoad);
			callback();
		} else {
			this.DataView.getStore().load({
				params: {
					mode: params.mode,
					LoadShort: params.LoadShort,
					loadFromDB: params.loadFromDB,
					Person_id: params.Person_id,
					Server_id: params.Server_id,
					PersonEvn_id: params.PersonEvn_id,
					EvnDirection_id: params.EvnDirection_id,
					additionalFields: params.additionalFields
				},
				callback: callback
			});
		}

		if( this.addToolbar ) {
			this.getDispClassListAvailable();
			this.PToolbar.queryById('ScreenOnko').setVisible(false);
		}

		this.setReadOnly(false);
		if(getWnd('swWorkPlaceMZSpecWindow').isVisible()) //https://redmine.swan.perm.ru/issues/92555 Эта панель дохрена где используется, а для АРМа МЗ ее надо скрыть, поэтому чем перелопачивать туеву хучу форм, сделал такой вот финт ушами
			this.disable();
		else
			this.enable();
		// ищем родительское окно, если есть то устанавливаем возможность редактировать пользователя в зависимости от action/readOnly в родительском окне.
		/*var ownerCur = this.ownerCt;
		if( typeof ownerCur != "undefined" ) {
			while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
				ownerCur = ownerCur.ownerCt;
			}
			if (typeof ownerCur.checkRole == 'function') {
				if (ownerCur.readOnly || (ownerCur.action && ownerCur.action == 'view')) {
					this.setReadOnly(true);
				}
			}
		}*/

	},
	getFieldValue: function(field) {
		var result = '';
		if (this.DataView.getStore().getAt(0))
			result = this.DataView.getStore().getAt(0).get(field);
		return result;
	},
	panelButtonClick: function(winType) {
		var params = this.collectAdditionalParams(winType);
		var window_name = '';

		if ( typeof params != 'object' ) {
			params = new Object();
		}

		switch ( winType ) {
			case 1:
				params.callback = this.button1Callback;
				params.onHide = this.button1OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				window_name = 'swPersonCardHistoryWindow';
			break;

			case 2:
				if (!Ext6.isEmpty(this.getFieldValue('PersonEncrypHIV_Encryp'))) {
					return false;
				}
				var allow_open = 1;
				var ownerCur = this.ownerCt;
				if( typeof ownerCur != "undefined" ) {
					while (ownerCur.ownerCt && typeof ownerCur.checkRole != 'function') {
						ownerCur = ownerCur.ownerCt;
					}
					if (typeof ownerCur.checkRole == 'function') {
						if (ownerCur.readOnly || (ownerCur.action && ownerCur.action == 'view')) {
							allow_open = 0;
						}
					}
				}
				if(allow_open == 1)
				{
					params.action = 'edit';
					params.callback = this.button2Callback;
					params.onClose = this.button2OnHide;
					window_name = 'swPersonEditWindow';
				}
				else
					return false;
			break;

			case 3:
				params.callback = this.button3Callback;
				params.onHide = this.button3OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonCureHistoryWindow';
			break;

			case 4:
				params.callback = this.button4Callback;
				params.onHide = this.button4OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonPrivilegeViewWindow';
			break;

			case 5:
				params.callback = this.button5Callback;
				params.onHide = this.button5OnHide;
				params.Person_Birthday = this.getFieldValue('Person_Birthday');
				params.Person_Firname = this.getFieldValue('Person_Firname');
				params.Person_Secname = this.getFieldValue('Person_Secname');
				params.Person_Surname = this.getFieldValue('Person_Surname');
				params.Person_deadDT = this.getFieldValue('Person_deadDT');
				params.Person_closeDT = this.getFieldValue('Person_closeDT');
				params.action = this.readOnly?'view':'edit';
				window_name = 'swPersonDispHistoryWindow';
			break;

			default:
				return false;
			break;
		}

		params.Person_id = this.personId;
		params.Server_id = this.serverId;

		if ( getWnd(window_name).isVisible() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: Ext6.emptyFn,
				icon: Ext6.Msg.WARNING,
				msg: langs('Окно уже открыто'),
				title: ERR_WND_TIT
			});

			return false;
		}

		getWnd(window_name).show(params);
	},
	setReadOnly: function (is_read_only)
	{
		if (is_read_only) {
			this.readOnly = true;
			//this.ButtonPanel.items.items[1].disable();
		} else {
			this.readOnly = false;
			//this.ButtonPanel.items.items[1].enable();
		}
	},
	setPersonTitle: function()
	{
		if (Ext6.isEmpty(this.personId)) {
			this.setTitle('...', null, '', '');
			return;
		}
		var name = this.getFieldValue('Person_Surname').charAt(0).toUpperCase() + this.getFieldValue('Person_Surname').slice(1).toLowerCase()+' '+ this.getFieldValue('Person_Firname').charAt(0).toUpperCase() + this.getFieldValue('Person_Firname').slice(1).toLowerCase()+' '+this.getFieldValue('Person_Secname').charAt(0).toUpperCase() + this.getFieldValue('Person_Secname').slice(1).toLowerCase(),
			bday = Ext6.util.Format.date(this.getFieldValue('Person_Birthday'), "d.m.Y"),
			sex = this.getFieldValue('Sex_id')=='2'?'woman':'man',
			age = this.getFieldValue('personAgeText'),
			dday = Ext6.util.Format.date(this.getFieldValue('Person_deadDT'), "d.m.Y"),
			isDead = this.getFieldValue('Person_IsDead') == 2 ? true : false,
			labels = this.getFieldValue('PersLabels'),
			monitoring =
				{ 	showIcon: this.getFieldValue('RemoteMonitoring_Label_id')==1 ? (this.getFieldValue('RemoteMonitoring_Chart_id') ? true : false) : false,
					active: this.getFieldValue('RemoteMonitoring_Chart_id') ? (this.getFieldValue('RemoteMonitoring_ChartEndDT') ? '' : '-active') : '',
					group_id: this.getFieldValue('RemoteMonitoring_PersonModel_id'),
					outType: this.getFieldValue('RemoteMonitoring_OutTypeName'),
					endDT: this.getFieldValue('RemoteMonitoring_ChartEndDT'),
					tip: ( (this.getFieldValue('RemoteMonitoring_Chart_id') && this.getFieldValue('RemoteMonitoring_ChartEndDT') ) ? 
						'Исключен из регистра А/Д '+this.getFieldValue('RemoteMonitoring_ChartEndDT')+' '+this.getFieldValue('RemoteMonitoring_OutTypeName')
						:'Состоит в регистре А/Д'
						)
				},
			PersonQuarantine = false;
		if(this.getFieldValue('PersonQuarantine_IsOn') == 2){
			PersonQuarantine = 'Пациент на карантине.';
			if(this.getFieldValue('PersonQuarantine_begDT')){
				PersonQuarantine = 'Дата начала карантина COVID-19: <font color=red>' +
					Ext6.util.Format.date(this.getFieldValue('PersonQuarantine_begDT'), "d.m.Y") +
					'</font>';
			}
		}
		switch(Number(monitoring.group_id)) {
			case 1: monitoring.group = 'гр. I';break;
			case 2: monitoring.group = 'гр. II';break;
			case 3: monitoring.group = 'гр. III';break;
			default: monitoring.group = '';
		}
		this.setTitle(name, bday, sex, age, dday, isDead, monitoring, labels, PersonQuarantine);

	},
	setTitle: function(name, bday, sex, age, dday, isDead, monitoring, labels, PersonQuarantine)
	{
		// @TODO Как-то сложно получается, надо бы отрефакторить
		var classStr = 'person-frame-title-expander';
		if(PersonQuarantine){
			classStr += ' quarantined-patient-expander';
		}
		var personTpl = new Ext6.XTemplate(
			'<span class="','{classStr}','" style="cursor: pointer;" data-qtip="Подробнее">',
			'<b class="personpanel_arrow"></b>',
			'<b class="personpanel_sex person_','{sex}','"></b>',
			'{name} ',
			'<span class="personpanel_bday">{bday} ({age})</span>',
			'<tpl if="values.isDead"><span style="color: #F70707;font-size: 14px;margin-left: 4px;">Дата смерти {dday}</span></tpl>',
			'<tpl if="values.PersonQuarantine">',
			'<span style="color: #F70707;font-size: 14px;margin-left: 4px;">{PersonQuarantine}</span>',
			'</tpl>',
			'<tpl if="values.monitoring.showIcon">',//индикатор дистанц.мониторинга
			'<span data-qtip="{monitoring.tip}">',
				'<b class="dm-status-label dm-status-label-ad{monitoring.active}"></b>',
				'<span class="dm-status-name">{monitoring.group}</span>',
			'</span>',
			'</tpl></span>',
			'<tpl if="values.labels">',
			'<span>',
				'<b class="person-labels" id="'+this.id+'-person-labels" data-qtip="'+(Ext6.isEmpty(labels) ? '':labels)+'"></b>',
			'</span>',
			'</tpl>'
		);
		personTpl.overwrite(this.PersonFrameTitle.items.items[0].body, {
			name: name, 
			bday: bday, 
			sex: sex, 
			age: age, 
			dday: dday, 
			isDead: isDead, 
			monitoring: monitoring, 
			labels: labels,
			PersonQuarantine: PersonQuarantine,
			classStr: classStr
		});
	},
	clearTitle: function() {
		var personTpl = new Ext6.XTemplate();
		personTpl.overwrite(this.PersonFrameTitle.items.items[0].body);
	},
	showLabels: function(panel_id, labels) {
		if(!Ext6.isEmpty(labels)) {
			labels = labels.split('|');
			if(!this.labeltip && labels.length>0) {
				this.labeltip = Ext6.create('Ext6.tip.ToolTip', {
					html: '',
					autoHide: true,
					closable: false
				});
			}
			this.labeltip.setHtml(labels.join('<br>'));
		}
	},
	forReceptCommonAstra: false,
	initComponent: function() {
		var form = this,
			conf = form.initialConfig,
			regNick = getRegionNick(),

			// нужна ли деперсонализация данных:
			depers = isMseDepers();

		form.fields = [
			{name: 'Person_id'},
			{name: 'Server_pid'},
			{name: 'Document_begDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Document_Num'},
			{name: 'Document_Ser'},
			{name: 'KLAreaType_id'},
			{name: 'Lpu_Nick'},
			{name: 'Lpu_id'},
			{name: 'LpuRegion_Name'},
			{name: 'OrgDep_Name'},
			{name: 'OrgSmo_Name'},
			{name: 'Person_Age'},
			{name: 'Person_Birthday', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'PersonCard_begDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'PersonEvn_id'},
			{name: 'Server_id'},
			{name: 'Person_Firname'},
			{name: 'Person_Job'},
			{name: 'Person_PAddress'},
			{name: 'PAddress_Address'},
			{name: 'Person_Phone'},
			{name: 'JobOrg_id'},
			{name: 'Person_Post'},
			{name: 'Person_RAddress'},
			{name: 'RAddress_Address'},
			{name: 'Person_Secname'},
			{name: 'Person_Snils'},
			{name: 'Person_Inn'},
			{name: 'Person_Surname'},
			{name: 'Person_EdNum'},
			{name: 'SurNameLetter'},
			{name: 'Polis_begDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Polis_endDate', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Polis_Num'},
			{name: 'Polis_Ser'},
			{name: 'OmsSprTerr_id'},
			{name: 'OmsSprTerr_Code'},
			{name: 'Sex_Code'},
			{name: 'Sex_id'},
			{name: 'Sex_Name'},
			{name: 'SocStatus_id'},
			{name: 'SocStatus_Name'},
			{name: 'FamilyStatus_id'},
			{name: 'FamilyStatus_Name'},
			{name: 'Person_deadDT', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'Person_closeDT', dateFormat: 'd.m.Y', type: 'date'},
			{name: 'PersonCloseCause_id'},
			{name: 'Person_IsDead'},
			{name: 'Person_IsBDZ'},
			{name: 'PersonEncrypHIV_Encryp'},
			{name: 'Person_IsUnknown', type: 'int'},
			{name: 'Person_IsAnonym', type: 'int'},
			{name: 'RegisterAD', type: 'int'},
			{name: 'PersLabels'},
			{name: 'PersonChild_id'},
			{name: 'FeedingType_Name'},
			{name: 'personAgeText'},
			{name: 'PersonQuarantine_IsOn'},
			{name: 'PersonQuarantine_begDT', dateFormat: 'd.m.Y', type: 'date'}
		];

		form.additionalFields.forEach(function(item) {
			form.fields.push({name: item});
		});
		
		var EvnPLDispMenuHandler = function(button) {
			if(this.DispClass_id<0) { //отказ
				EvnPLDispRefuse(form.personId, -this.DispClass_id, getGlobalOptions().CurMedStaffFact_id, function() { form.ownerWin.loadTree(); });
			} else { //открыть форму ДВН
				var opts = {
					EvnVizitPL_id: false,
					Person_id: form.personId,
					dataToLoad: { evnPLDispDop13Data: {DispClass_id: this.DispClass_id, EvnClass_id: this.EvnClass_id }},
					object: this.object,
					object_id: this.object_id,
					object_value: this.object_value,
					user_MedStaffFact_id: form.userMedStaffFact ? form.userMedStaffFact.MedStaffFact_id : null
				};
				form.openEvnPLDisp(opts, button);
			}
		};

		form.EvnPLDispMenu = new Ext6.menu.Menu({
			//~ userCls: 'menuWithoutIcons',
			cls: 'menuWithoutIcons',
			checkState: function() {
				var it = this;
				var disp1 = it.queryById('EvnPLDispMenu_Disp1'),
					disp2 = it.queryById('EvnPLDispMenu_Disp2'),
					btn = form.queryById('buttonEvnPLDisp');
				btn.removeCls('dispdop-icon-warning');
				btn.removeCls('dispdop-icon-error');
				
				if(!disp1.hidden || !disp2.hidden) {//есть карта или требуется
					if(disp1.object_value || disp2.object_value) {//есть карта 1 или 2
						if((disp1.object_value && !disp1.isFinish) || (disp2.object_value && !disp2.isFinish)) //и незакончено
							btn.addCls('dispdop-icon-warning');//индикатор о том, что ДВН незакончено
					} else {
						btn.addCls('dispdop-icon-error');//индикатор о том, что требуется ДВН
					}
				}
			},
			items: [{
				text: 'Пройти Диспансеризацию',//Диспансеризация взрослого населения - 1 этап
				itemId: 'EvnPLDispMenu_Disp1',
				DispClass_id: 1,
				EvnClass_id: 101,
				object: 'EvnPLDispDop13',
				object_id: 'EvnPLDispDop13_id',
				object_value: null,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Пациент отказался от Диспансеризации',
				itemId: 'EvnPLDispMenu_Disp1Refuse',
				DispClass_id: -1,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Пройти Диспансеризацию',//Диспансеризация взрослого населения - 2 этап
				itemId: 'EvnPLDispMenu_Disp2',
				DispClass_id: 2,
				EvnClass_id: 101,
				object: 'EvnPLDispDop13',
				object_id: 'EvnPLDispDop13_id',
				object_value: null,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Пациент отказался от Диспансеризации',
				itemId: 'EvnPLDispMenu_Disp2Refuse',
				DispClass_id: -2,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Пройти Профосмотр',
				itemId: 'EvnPLDispMenu_Prof',
				object: 'EvnPLDispProf',
				object_id: 'EvnPLDispProf_id',
				object_value: null,
				DispClass_id: 5,
				EvnClass_id: 103,
				disabled: true,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Пациент отказался от профосмотра',
				itemId: 'EvnPLDispMenu_ProfRefuse',
				DispClass_id: -5,
				disabled: true,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Диспансеризация детей сирот - 1 этап',
				DispClass_id: 3,
				EvnClass_id: 3,
				disabled: true,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Диспансеризация детей сирот - 2 этап',
				DispClass_id: 4,
				disabled: true,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Периодические осмотры несовершеннолетних',
				DispClass_id: 6,
				disabled: true,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Профилактические осмотры несовершеннолетних - 1 этап',
				DispClass_id: 10,
				disabled: true,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Профилактические осмотры несовершеннолетних - 2 этап',
				DispClass_id: 12,
				disabled: true,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Предварительные осмотры несовершеннолетних - 1 этап',
				DispClass_id: 9,
				disabled: true,
				handler: EvnPLDispMenuHandler
			}, {
				text: 'Предварительные осмотры несовершеннолетних - 2 этап',
				DispClass_id: 11,
				disabled: true,
				handler: EvnPLDispMenuHandler
			}]
		});

		var cur_tpl = new Ext6.XTemplate(
			'<tpl for=".">',
				'<div class="PersonFrame'+ (form.narrowPanel ? ' PersonFrameNarrow' : '') + '">',
				'<tpl if="this.allowPersonEncrypHIV() == true">',
				'<div><i>Шифр:</i> {PersonEncrypHIV_Encryp}</div>',
				'</tpl>',
				'<tpl if="this.allowPersonEncrypHIV() == false">',
				'<div data-qtip="{Person_Surname} {Person_Firname} {Person_Secname}"><i>ФИО:</i> {Person_Surname} {Person_Firname} {Person_Secname}</div>',
				'<div data-qtip="{[Ext6.util.Format.date(values.Person_Birthday, \"d.m.Y\")]}"><i>Д/р:</i> {[Ext6.util.Format.date(values.Person_Birthday, "d.m.Y")]}</div>',
				'<div data-qtip="{Sex_Name}"><i>Пол:</i> {Sex_Name}</div>',
				'{[(String(values.Person_deadDT) != "null" ? "<div>Дата смерти: <span color=red>" + String(Ext6.util.Format.date(values.Person_deadDT, "d.m.Y")) + "</span></div>" : "")]}',
				'{[(String(values.Person_closeDT) != "null" ? "<div>Дата закрытия: <span color=red>" + String(Ext6.util.Format.date(values.Person_closeDT, "d.m.Y")) + "</span></div>" : "")]}',
				'<div data-qtip="{SocStatus_Name}"><i>Соц. статус:</i> {SocStatus_Name}</div>',
				'<div data-qtip="{[snilsRenderer(values.Person_Snils)]}"><i>СНИЛС:</i> {[snilsRenderer(values.Person_Snils)]}</div>',
				'<div data-qtip="{Person_RAddress}"><i>Регистрация:</i> {Person_RAddress}</div>',
				'<div data-qtip="{Person_PAddress}"><i>Проживает:</i> {Person_PAddress}</div>',
				'<div data-qtip="{Person_Phone}"><i>Телефон:</i> {Person_Phone}</div>',
				'<div data-qtip="{Person_Inn}"><i>' +
					(regNick == 'kz' ? 'ИИН' : 'ИНН') +
					':</i> ' +
					(depers ? '***' : '{Person_Inn}') +
					'</div>',
				"<div data-qtip='{[this.getPolisStr(values)]}'>",
					'<i>Полис:</i> {[this.getPolisStr(values)]}',
				"</div>",
				"<div data-qtip='{[this.getDocumentStr(values)]}'>",
				'<i>Документ:</i> {[this.getDocumentStr(values)]}',
				"</div>",
				'<div data-qtip="{FamilyStatus_Name}"><i>Семейное положение:</i> ' +
					(depers ? '***' : '{FamilyStatus_Name}') +
					'</div>',
				'<div data-qtip="{Person_Job}"><i>Работа:</i> {Person_Job}</div>',
				'<div data-qtip="{Person_Post}"><i>Должность:</i> {Person_Post}</div>',
				'<div data-qtip="{Lpu_Nick}" class="person-info-MO"><i>МО:</i> <span style ="overflow: hidden; text-overflow: ellipsis; width: max-content; max-width: 115px; white-space: nowrap;display: inline-block;line-height: 14px; top: 2px; position: relative;">{Lpu_Nick}</span>',
					'<tpl if="this.isNotDead() == true">',
						"<a class='change-attachment' onclick='{[this.onClickMO()]}' >Изменить прикрепление</a>",
					'</tpl>',
				'</div>',
				'<div><i>Участок:</i> {LpuRegion_Name} ({[Ext6.util.Format.date(values.PersonCard_begDate, "d.m.Y")]})</div>',
				'<div data-qtip="{NewslatterAccept}"><i>Согласие на получение уведомлений:</i> {NewslatterAccept}</div>',
				'<tpl if="this.FeedingTypeHide() == true">',
				'<div data-qtip="{FeedingType_Name}"><i>Способ вскармливания:</i> {FeedingType_Name}</div>',
				'</tpl>',
				'</tpl>',
				'</div>',
				'</tpl>',
				{
					FeedingTypeHide: function () {
						return(form.getFieldValue('Person_Age') <= 5)
					},
					allowPersonEncrypHIV: function () {
						return (!Ext6.isEmpty(form.getFieldValue('PersonEncrypHIV_Encryp')));
					},
					isNotDead: function () {
						return !form.checkIsDead();
					},
					getPolisStr: function (values) {
						var StrPolis = values.Polis_Ser+' '+values.Polis_Num;
						if(values.Polis_begDate || values.OrgSmo_Name)
							StrPolis += ' Выдан: '+Ext6.util.Format.date(values.Polis_begDate, "d.m.Y")+' '+values.OrgSmo_Name+'.';
						if(values.Polis_endDate)
							StrPolis += ' Закрыт: '+Ext6.util.Format.date(values.Polis_endDate, "d.m.Y");
						return StrPolis;
						//return StrPolis.replace(new RegExp('"','g'),"'");
					},
					getDocumentStr: function (values) {
						var StrDocument = values.Document_Ser+' '+values.Document_Num;
						if(values.Document_begDate || values.OrgDep_Name)
							StrDocument += ' Выдан: '+Ext6.util.Format.date(values.Document_begDate, "d.m.Y")+' '+values.OrgDep_Name+'.';
						return StrDocument;
					},
					onClickMO: function() {
						return 'Ext6.getCmp("' + form.getId() + '").panelButtonClick(1);'
					}
				}
		);

		if(this.forReceptCommonAstra)
		{
			cur_tpl = new Ext6.XTemplate(
				'<tpl for=".">',
				'<div class="PersonFrame">',
				'<div>&nbsp;&nbsp;&nbsp;<span style="color: blue; span-weight: bold;">{Person_Firname} {Person_Secname} {SurNameLetter} </span></div>' ,
				'<div>&nbsp;&nbsp;&nbsp;Д/р: <span style="color: blue;">{[Ext6.util.Format.date(values.Person_Birthday, "d.m.Y")]}</span> г.р. </div>' ,
				'<div>&nbsp;&nbsp;&nbsp;СНИЛС: <span style="color: blue;">{[snilsRenderer(values.Person_Snils)]}</span></div>' ,
				'<div>&nbsp;&nbsp;&nbsp;Полис: <span style="color: blue;">{Polis_Ser} {Polis_Num}</span>&nbsp; ЕНП: <span style="color: blue;">{Person_EdNum}</span></div>',
				'</div>',
				'</tpl>',
				{
					allowPersonEncrypHIV: function () {
						return (!Ext6.isEmpty(form.getFieldValue('PersonEncrypHIV_Encryp')));
					}
				}
			);
		}
		this.TimeElapsed = new Ext6.Panel({
			itemId: 'timerContainerId',
			y: 4,
			margin: '0 10 0 0',
			border: false,
			cls: 'timer-container',
			style: {
				display: 'inline-block',
				border: '1px solid #ccc',
				'border-radius': '4px',
				visibility: 'hidden'
			},
			width: 121,
			height: 36,
			items: [{
				xtype: 'button',
				iconCls: 'close-timer',
				cls: 'button-without-frame',
				tooltip: 'Закрыть таймер',
				padding: 0,
				margin: '1 2 0 0',
				style: {
					float: 'right'
				},
				listeners: {
					click: function () {
						form.ownerWin.stopTask();
					}
				}
			}]
		});
		if(this.addToolbar){
			this.PToolbar = new Ext6.Panel({
				region: 'east',
				cls: 'PToolbar',
				width:550,
				border: false,
				//noWrap: true,
				right: 0,
				items: [
					{
						xtype: 'tbspacer',
						width: 10
					}, this.TimeElapsed,
					{
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-add-new-apl',
						tooltip: langs('Создать новый случай АПЛ'),
						refId: 'create-new-apl',
						handler: function (butt) {
							form.ownerWin.addNewEvnPLAndEvnVizitPL({isStom: form.userMedStaffFact.ARMType == 'stom6'}, butt);
						}
					},
					{
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-open-personquarantine',
						tooltip: langs('Открыть контрольную карту пациента на карантине'),
						refId: 'create-new-personquarantin',
						handler: function (butt) {
							form.openQuarantineEditWindow({action: 'edit'});
						}
					},
					{
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-immunoprofil_open',
						tooltip: langs('Карта профилактических прививок'),
						refId: 'open_immunoprofil',
						hidden: (!getRegionNick().inlist(['perm', 'astra', 'krym', 'penza'])),
						handler: function (butt) {
							if(!form.personId || !form.serverId) return false;
							var params = {
								person_id: form.personId,
								Server_id: form.serverId,
								parent_id: 'swPersonEmkWindow',
								viewOnly: false,
								age: form.getFieldValue('Person_Age')
							}
							getWnd('amm_Kard063').show(params);
						}
					},
					{
						xtype: 'button',
						hidden: false, 
						padding: "13px 10px",
						userCls: 'button-without-frame dispdop-icon',
						//~ userCls: 'dispdop-icon-error',
						iconCls: 'panicon-disp',
						tooltip: langs('Диспансеризация / профосмотры'),
						itemId: 'buttonEvnPLDisp',
						//~ menu: form.EvnPLDispMenu
						handler: function(butt) {
							form.EvnPLDispMenu.showBy(butt);
						}
					}, {
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-smp',
						tooltip: langs('Вызвать СМП'),
						handler: function (butt) {
							inDevelopmentAlert();
						}
					}, {
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-new-privilege',
						tooltip: langs('Открыть льготу пациенту'),
						handler: function () {
							//следующая строка добавлена только для временного вызова формы добавления льготы в старом дизайне до завершения переноса новых функций
							form.openForm('swPrivilegeEditWindow', 'PersonPrivilege_id', {}, 'add');
							//form.openForm('swPrivilegeEditWindowExt6', 'PersonPrivilege_id', {}, 'add');
						}
					}, {
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-new-disp-reg',
						tooltip: langs('Поставить на диспансерное наблюдение'),
						handler: function () {
							form.action_New_PersonDisp();
						}
					}, {
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-med-svid-migrant',
						hidden: (!isUserGroup('MedOsvMigr')),
						tooltip: langs('Создать случай мед. освидетельствования мигранта'),
						handler: function () {
							form.addNewEvnPLDisp({
								DispClass_id: 19
							});
						}
					}, {
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-med-svid-driver',
						hidden: (!isDrivingCommission()),
						tooltip: langs('Создать случай мед. освидетельствования водителя'),
						handler: function () {
							form.addNewEvnPLDisp({
								DispClass_id: 26
							});
						}
					}, {
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-create-sms-mail',
						tooltip: langs('Создать СМС/e-mail рассылку'),
						handler: function () {
							form.addNewslatter();
						}
					}, {
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-edit-pers-info',
						name: 'editPerson',
						tooltip: langs('Редактировать данные пациента'),
						handler: function () {
							form.panelButtonClick(2);
						}
					}, {
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-screen-onko',
						name: 'ScreenOnko',
						itemId: 'ScreenOnko',
						tooltip: langs('Первичный онкологический скрининг'),
						hidden: true,
						handler: function (butt) {
							console.log('butt',butt);
							console.log('form.ownerWin',form.ownerWin);
							form.ownerWin.addNewEvnPLDispScreenOnko({}, butt);
						}
					}, {
						xtype: 'button',
						padding: "13px 10px",
						userCls: 'button-without-frame',
						iconCls: 'panicon-open-newborn',
						tooltip: langs('Сведения о новорожденном'),
						refId: 'open_personnewborn',
						hidden: (!getRegionNick().inlist(['ufa'])),
						handler: function (butt) {
							if(!form.personId) return false;
							var params = {
								action: 'view',
								Person_id: form.personId
							}
							getWnd('swPersonBirthSpecific').show(params);
						}
					}
				]
			});
		}

		this.PersonFrameTitle = new Ext6.Panel({
			border: true,
			frame: false,
			height: 45,
			region: 'north',
			layout: 'border',
			cls: 'PersonFrameTitle',
			bodyStyle: 'border-width: 0 0 1px 0',
			items: [
				{
					height: 50,
					html: '',
					width: '100%',
					minWidth: 380,
					region: 'west',
					border: false,
					padding: 0,
					bodyStyle: 'background: #fff; font-size: 16px; padding: 12px 20px;',
					listeners: {
						render: function(p) {
							/*p.getEl().on('click', function() {
								var contentPanel = this.PersonFrameContent;
								var arrow =	this.PersonFrameTitle.getEl().select('b.personpanel_arrow');
								if (!contentPanel.isHidden()) {
									contentPanel.hide();
									this.setHeight(45);
									arrow.removeCls('expanded');
								} else {
									contentPanel.show();
									this.setHeight(170);
									arrow.addCls('expanded');
								}.createDelegate(this);
							}.createDelegate(this));*/
							p.getEl().on('click',function (e) {
								if(e.target.className=='person-labels') {
									if(!Ext6.isEmpty(form.labeltip)) {
										var el_labels = document.getElementById(form.id+'-person-labels');
										if(el_labels)
											form.labeltip.showBy(el_labels, 'tr-tr');
									}
									return;
								}
								var contentPanel = form.PersonFrameContent;
								var arrow =	form.PersonFrameTitle.getEl().select('b.personpanel_arrow');
								if (!contentPanel.isHidden()) {
									contentPanel.hide();
									form.setHeight(45);
									arrow.removeCls('expanded');
								} else {
									contentPanel.show();
									form.setHeight(form.narrowPanel ? 220 : 170);
									arrow.addCls('expanded');
								}
							},null, {delegate: 'span.person-frame-title-expander'})
						}.createDelegate(this)
						}
				}
			]
		});
		if(this.addToolbar)
			this.PersonFrameTitle.add(this.PToolbar);

		this.DataView = new Ext6.DataView({
			border: false,
			frame: false,
			height: form.narrowPanel ? 182 : 132,
			itemSelector: 'div.PersonFrame',
			style: 'margin-right: 20px',
			region: 'center',
			store: {
				fields: form.fields,
				proxy: {
					type: 'ajax',
					extraParams: {
						mode: 'PersonInfoPanel',
						additionalFields: Ext6.util.JSON.encode(form.additionalFields)
					},
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=Common&m=loadPersonData',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				}
			},
			tpl: cur_tpl
		});
		var domElButt ='</span><span class="gray_sp" style="position: absolute;left: 125px; top: 8px">';
		/*this.ButtonPanel = new Ext6.Panel({
			style: 'height:105px!important; ',
			bodyStyle: 'background-color: transparent; padding-top:5px;',
			border: false,
			defaults: {
				xtype: 'button',
				minWidth: '380',
				textAlign: 'left',
			},
			items: [{
				disabled: false,
				handler: function() {
					form.panelButtonClick(4);
				},
				text: BTN_PERSPRIV+domElButt+'F12',
				userCls: 'button-without-frame micro',
				//iconCls: 'pers-priv16',
				tooltip: BTN_PERSPRIV_TIP
			}, {
				disabled: false,
				handler: function() {
					form.panelButtonClick(1);
				},
				text: BTN_PERSCARD+domElButt+'F6',
				userCls: 'button-without-frame micro',
				//iconCls: 'pers-card16',
				tooltip: BTN_PERSCARD_TIP
			}, {
				disabled: false,
				handler: function() {
					form.panelButtonClick(2);
				},
				text: BTN_PERSEDIT+domElButt+'F10',
				userCls: 'button-without-frame micro',
				//iconCls: 'edit16',
				tooltip: BTN_PERSEDIT_TIP
			}, {
				disabled: false,
				handler: function() {
					form.panelButtonClick(3);
				},
				text: BTN_PERSCUREHIST+domElButt+'F11',
				userCls: 'button-without-frame micro',
				//iconCls: 'pers-curehist16',
				tooltip: BTN_PERSCUREHIST_TIP
			},{
				disabled: false,
				handler: function() {
					form.panelButtonClick(5);
				},
				text: BTN_PERSDISP+domElButt+'сtrl+F12',
				userCls: 'button-without-frame micro',
				//iconCls: 'pers-disp16',
				tooltip: BTN_PERSDISP_TIP
			}],
			region: 'east',
			width: 180,
		});*/

		this.PersonFrameContent = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			cls: 'person-frame-content',
			border: false,
			frame: false,
			bodyStyle: 'background: #fff;',
			height: 100,
			items: [ this.DataView /*, this.ButtonPanel */]
		});
		
		Ext6.apply(this, {
			items: [ this.PersonFrameContent, this.PersonFrameTitle ]
		});

		form.callParent(arguments);
	},
	getPersonId: function(){
		return this.personId;
	},
	openEvnPLDisp: function(opts, butt) {//открыть форму двн внутри эмк, по новой или существующей карте (opts.EvnPLDispDop13_id, opts.EvnPLDispProf_id)
		this.ownerWin.openEvnPLDispDop13(opts, butt);
		/*
		var me = this;
		
		if(Ext6.isEmpty(opts.EvnPLDispDop13_id)) {
			//новая карта двн, открываем без создания узла в дереве
			//~ me.ownerWin.loadEmkViewPanel('EvnPLDispDop13','','',false,false);
			me.ownerWin.openEvnPLDispDop13(opts, butt);
		} else {
			//существующая карта двн 
			me.ownerWin.openEvnPLDispDop13({
				EvnVizitPL_id: false,
				Person_id: me.personId,
				dataToLoad: { evnPLDispData: {DispClass_id: opts.DispClass_id, EvnClass_id:opts.EvnClass_id}},
				object: "EvnPLDispDop13",
				object_id: "EvnPLDispDop13_id",
				object_value: opts.EvnPLDispDop13_id,
				user_MedStaffFact_id: me.userMedStaffFact ? me.userMedStaffFact.MedStaffFact_id : null
			}, butt);
			
		}*/
	},
	//старый метод создания карты двн
	addNewEvnPLDisp: function(opts) {return;
		var form = this;
		var emk = Ext6.ComponentQuery.query('#common')[0];
		if (!opts) {
			opts = {};
		}

		// создаём карту, встаём на неё в дереве.
		emk.getLoadMask('Создание случая диспансеризации').show();
		Ext6.Ajax.request({
			params: {
				Person_id: form.getPersonId(),
				DispClass_id: opts.DispClass_id
			},
			callback: function(options, success, response) {
				emk.getLoadMask().hide();
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (response_obj.success) {
					form.getDispClassListAvailable();
					emk.loadTree();
				}
			},
			url: '/?c=EvnPLDisp&m=createEvnPLDisp'
		});
	},
	getDispClassListAvailable: function() {
		var emk = Ext6.ComponentQuery.query('#common')[0];
		var me = this;
		//сначала скрыть все элементы
		me.EvnPLDispMenu.items.items.forEach(function(item) { item.hide(); } );

		me.queryById('buttonEvnPLDisp').disable();
		Ext6.Ajax.request({
			url: '/?c=EvnPLDisp&m=getDispClassListAvailable',
			params: {
				Person_id: me.getPersonId(),
				getAllDispInfo: true
			},
			callback: function(options, success, response) {
				me.queryById('buttonEvnPLDisp').enable();
				
				var enable = false;
				if ( success ) {
					var data = Ext6.JSON.decode(response.responseText);
					if(data && data.length>0) {
						//открыть доступные варианты диспансеризации
						data[0].avail.forEach(function (avail_id) {
							var el = me.EvnPLDispMenu.queryBy(function (item) {
								return Math.abs(item.DispClass_id) == avail_id; //отрицательные пункты - отказы
							});
							if (!Ext6.isEmpty(el) && el.length > 0) {
								el.forEach(function (elem) {
									elem.object_value = null;
									elem.show();
								});
							}
						});

						if (!Ext6.isEmpty(data[0].info.EvnPLDispDop13_id)//есть карта 1 этапа
							&& Ext6.isEmpty(data[0].info.EvnPLDispDop13_secid)//и нет 2 этапа
							&& !data[0].avail.in_array(2) //и 2 этап недоступен - чтобы была одна кнопка
						) {

							var el = me.EvnPLDispMenu.queryBy(function (item) {//берем пункт двн 1
								return Math.abs(item.DispClass_id) == 1;
							});
							if (!Ext6.isEmpty(el) && el.length > 0) {
								el.forEach(function (elem) {
									elem.object_value = elem.DispClass_id > 0 ? data[0].info.EvnPLDispDop13_id : null;
									elem.isFinish = data[0].info.EvnPLDispDop13_IsFinish == 2;
									elem.show();
								});
							}
						}
						if(!Ext6.isEmpty(data[0].info.EvnPLDispDop13_secid)) {
							//есть карта 2 этапа
							var el = me.EvnPLDispMenu.queryBy(function(item) {//берем пункт двн 2
								return Math.abs(item.DispClass_id)==2
							});

							if(!Ext6.isEmpty(el) && el.length>0) {
								el.forEach(function(elem) {
									elem.object_value = elem.DispClass_id>0 ? data[0].info.EvnPLDispDop13_secid : null;
									elem.show();
								});
							}
						}
						if (!Ext6.isEmpty(data[0].info.EvnPLDispProf_id)) {
							//есть профосмотр
							var el = me.EvnPLDispMenu.queryBy(function (item) {//берем пункт профосмотра
								return Math.abs(item.DispClass_id) == 5;
							});
							if (!Ext6.isEmpty(el) && el.length > 0) {
								el.forEach(function (elem) {
									elem.object_value = elem.DispClass_id > 0 ? data[0].info.EvnPLDispProf_id : null;
									elem.show();
								});
							}
						}

						me.EvnPLDispMenu.checkState();
					}
				}
			}
		});
		
		return false; // пока не нужно

		var emk = Ext6.ComponentQuery.query('#common')[0];
		var form = this;

		form.EvnPLDispMenu.items.items[0].hide(); // ДВН 1
		form.EvnPLDispMenu.items.items[1].hide(); // ДВН 2
		form.EvnPLDispMenu.items.items[2].hide(); // ПРОФ
		form.EvnPLDispMenu.items.items[3].hide(); // ДДС 1
		form.EvnPLDispMenu.items.items[4].hide(); // ДДС 2
		form.EvnPLDispMenu.items.items[5].hide(); // МОН период
		form.EvnPLDispMenu.items.items[6].hide(); // МОН проф 1
		form.EvnPLDispMenu.items.items[7].hide(); // МОН проф 2
		form.EvnPLDispMenu.items.items[8].hide(); // МОН пред 1
		form.EvnPLDispMenu.items.items[9].hide(); // МОН пред 2

		emk.getLoadMask('Получение доступных видов диспансеризации').show();
		Ext6.Ajax.request({
			url: '/?c=EvnPLDisp&m=getDispClassListAvailable',
			params: {
				Person_id: form.getPersonId()
			},
			callback: function(options, success, response) {
				emk.getLoadMask().hide();
				var enable = false;
				if ( success ) {
					var data = Ext6.JSON.decode(response.responseText);
					// в зависимости от доступных типов диспансеризаций делаем доступными кнопки в меню
					if ("1".inlist(data)) {
						form.EvnPLDispMenu.items.items[0].show(); // ДВН 1
						enable = true;
					}
					if ("2".inlist(data)) {
						form.EvnPLDispMenu.items.items[1].show(); // ДВН 2
					}
					if ("5".inlist(data)) {
						form.EvnPLDispMenu.items.items[2].show(); // ПРОФ
					}
					/*
					if ("3".inlist(data)) {
						form.EvnPLDispMenu.items.items[3].show(); // ДДС 1
					}
					if ("4".inlist(data)) {
						form.EvnPLDispMenu.items.items[4].show(); // ДДС 2
					}
					if ("6".inlist(data)) {
						form.EvnPLDispMenu.items.items[5].show(); // МОН период
					}
					if ("10".inlist(data)) {
						form.EvnPLDispMenu.items.items[6].show(); // МОН проф 1
					}
					if ("12".inlist(data)) {
						form.EvnPLDispMenu.items.items[7].show(); // МОН проф 2
					}
					if ("9".inlist(data)) {
						form.EvnPLDispMenu.items.items[8].show(); // МОН пред 1
					}
					if ("11".inlist(data)) {
						form.EvnPLDispMenu.items.items[9].show(); // МОН пред 2
					}
					*/
				}
				form.queryById('buttonEvnPLDisp').setDisabled(!enable);
			}
		});
	},
	action_New_PersonPrivilege: function(){
		var form = this;
		form.openForm('swPrivilegeEditWindowExt6', 'PersonPrivilege_id', {}, 'add');
	},
	action_New_PersonCard: function(){
		var form = this;
		Ext6.Ajax.request({
			url: '/?c=LpuRegion&m=getMedPersLpuRegionList',
			callback: function(options, success, response)
			{
				if (success)
				{
					var response_obj = Ext6.JSON.decode(response.responseText);
					var params = {};
					if (response_obj[0]) {
						params = response_obj[0];
						switch (parseInt(response_obj[0].LpuAttachType_id)) {
							case 1:params.attachType = 'common_region';break;
							case 2:params.attachType = 'ginecol_region';break;
							case 3:params.attachType = 'stomat_region';break;
							case 4:params.attachType = 'service_region';break;
							case 5:params.attachType = 'dms_region';break;
	}
					}
					form.openForm('swPersonCardEditWindow', 'PersonCard_id', params, 'add');
				}
			},
			params: {
				LpuSectionProfile_Code: form.userMedStaffFact.LpuSectionProfile_Code,
				MedPersonal_id: form.userMedStaffFact.MedPersonal_id,
				Lpu_id: form.userMedStaffFact.Lpu_id
			}
		});
	},
	action_New_PersonDisp: function(){
		var form = this;
		// Проверяем, может ли текущий пользователь добавлять данные по дисп. учету
		// https://redmine.swan.perm.ru/issues/110660
		// Доработал вывод сообщения об ошибке
		var errorText,
			msfFilter = {
				id: this.userMedStaffFact.MedStaffFact_id,
				isDisp: true,
				isPolka: true
			};

		if ( getGlobalOptions().allowed_disp_med_staff_fact_group == 2 ) {
			msfFilter.isDoctorOrMidMedPersonal = true;
		}

		setMedStaffFactGlobalStoreFilter(msfFilter);

		if ( swMedStaffFactGlobalStore.getCount() == 0 ) {
			errorText = 'Добавление контрольных карт диспансерного наблюдения доступно для '
				+ (getGlobalOptions().allowed_disp_med_staff_fact_group == 2 ? 'врачей и среднего мед. персонала' : 'сотрудников')
				+ ' из групп отделений с типами "Поликлиника", "Городской центр", "Травматологический пункт", "Фельдшерско-акушерский пункт"';

			Ext6.Msg.alert(langs('Ошибка'), errorText);
			return false;
		}

		var params = {
			formParams: {
				Person_id: form.personId,
				Server_id: form.serverId
			}
		}
		form.openForm('swPersonDispEditWindowExt6', 'PersonDisp_id', params, 'add');
	},
	
	addNewslatter: function() {
		var form = this;
		Ext6.Ajax.request({
			url: '/?c=NewslatterAccept&m=check',
			params: {Person_id: this.personId},
			success: function(response, options) {
				var response_obj = Ext6.JSON.decode(response.responseText);
				if (!response_obj.length || !response_obj[0].NewslatterAccept_id) { // Если согласия нет - открываем форму для добавления
					form.openNewslatterAcceptEditWindow(null, function (result) {
						form.openNewslatterEditWindow('add', result);
					});
				} else { // Если есть - открываем сразу рассылку
					form.openNewslatterEditWindow('add', response_obj[0]);
				}
			}
		});
	},
	openNewslatterAcceptEditWindow: function(NewslatterAccept_id, callback) {

		var win = this;
		var params = {};
		params.NewslatterAccept_id = NewslatterAccept_id;
		params.action = Ext6.isEmpty(NewslatterAccept_id) ? 'add' : 'edit';
		params.Person_id = this.personId;
		params.callback = function(options, success, response) {
			if (success == true && response) {
				win.askPrintNewslatterAccept(response, callback);
			}
		}
		getWnd('swNewslatterAcceptEditFormExt6').show(params);
	},
	openNewslatterEditWindow: function(action, formParams) {

		var params = new Object();
		params.action = action;
		params.formParams = formParams;

		getWnd('swNewslatterEditWindow').show(params);
	},
	askPrintNewslatterAccept: function(params, callback) {

		if (!params || !params.NewslatterAccept_id) {
			return false;
		}

		var win = this;

		if (Ext6.isEmpty(params.NewslatterAccept_endDate)) {

			Ext6.Msg.show({
				title: langs('Вопрос'),
				msg: langs('Распечатать документ?'),
				icon: Ext6.MessageBox.QUESTION,
				buttons: Ext6.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' ) {
						win.printNewslatterAccept('printAccept', params.NewslatterAccept_id);
					}
					if (typeof callback == 'function') {
						callback(params);
					}
				}
				});
		} else {

			Ext6.Msg.show({
				title: langs('Вопрос'),
				msg: langs('Распечатать документ?'),
				icon: Ext6.MessageBox.QUESTION,
				buttons: {
					yes: langs('Печать Согласия'),
					no: langs('Печать отказа'),
					cancel: langs('Отмена')
				},
				fn: function( buttonId ) {
					if ( buttonId == 'yes') {
						win.printNewslatterAccept('printAccept', params.NewslatterAccept_id);
					} else if ( buttonId == 'no') {
						win.printNewslatterAccept('printDenial', params.NewslatterAccept_id);
					}
					if (typeof callback == 'function') {
						callback(params);
					}
				}
			});
		}
	},
	printNewslatterAccept: function(method, NewslatterAccept_id) {

		if (!method || !NewslatterAccept_id) {
			return false;
		}

		window.open('/?c=NewslatterAccept&m=' + method + '&NewslatterAccept_id=' + NewslatterAccept_id, '_blank');
	},
	/**
	 * Открывает соответсвующую акшену форму
	 *
	 * @param {open_form} Название открываемой формы, такое же как название объекта формы
	 * @param {id} Наименование идентификатора таблицы, передаваемого в форму
	 */
	openForm: function (open_form, id, oparams, mode, title, callback)
	{
		// Для упрощения процесса ссылки на формы назовем также как и формы
		// Получаем Id записи
		// Открываем форму, если не открыта
		var win = this;
		//var tree = Ext6.ComponentQuery.query('#tree')[0];
		// Проверка
		if (isVisibleWnd(open_form))
		{
			if (open_form == 'swDirectionMasterWindow') {
				Ext6.Msg.alert(langs('Сообщение'), langs('Форма ')+' '+ ((title)?title:open_form) +' '+langs(' в данный момент открыта.'));
			}
			//Ext6.Msg.alert('Сообщение', 'Форма '+ ((title)?title:open_form) +' в данный момент открыта.');
			return false;
		}
		else
		{
			var object_value;
			if (!mode)
				mode = this.isReadOnly?'view':'edit';
			if (mode == 'edit' && this.isReadOnly)
				mode = 'view';
			if (mode.inlist(['view','edit']))
			{
				/*
				if (!this.Tree.getSelectionModel().selNode || this.Tree.getSelectionModel().selNode.attributes.object_id != id )
				{
					Ext6.Msg.alert(langs('Сообщение'), langs('Вы не выбрали элемент дерева или задан неверный параметр ключа элемента!'));
					return false;
				}

				if (tree.getSelectionModel().selNode) {
					object_value = tree.getSelectionModel().selNode.attributes.object_value;

					var pnode = tree.getSelectionModel().selNode;
					var readOnlyNode = false;
					if ( getGlobalOptions().archive_database_enable ) {
						while(!Ext6.isEmpty(pnode)) {
							if (pnode.attributes && pnode.attributes.archiveRecord) {
								readOnlyNode = true;
							}
							pnode = pnode.parentNode;
						}
					}

					if (readOnlyNode) {
						mode = 'view';
					}
				}*/
			}
			var params = {
				action: mode,
				onPersonChange: function(data) {
					var lastArguments = win.lastArguments;
					if (lastArguments && lastArguments.Person_id && data && data.Person_id) {
						getWnd('swPersonEmkWindow').hide();
						lastArguments.Person_id = data.Person_id;
						lastArguments.Server_id = data.Server_id;
						lastArguments.PersonEvn_id = data.PersonEvn_id;
						getWnd('swPersonEmkWindow').show(lastArguments);
					}
				},
				onHide: function() {
					if (callback){
						callback();
					}
				}.createDelegate(this),
				PersonEvn_id: this.PersonEvn_id,
				Person_id: this.personId,
				Server_id: this.serverId,
				Person_Firname: this.getFieldValue('Person_Firname'),
				Person_Surname: this.getFieldValue('Person_Surname'),
				Person_Secname: this.getFieldValue('Person_Secname'),
				Person_Birthday: this.getFieldValue('Person_Birthday'),
				Person_deadDT: this.getFieldValue('Person_deadDT'),
				UserMedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
				UserLpuSection_id: this.userMedStaffFact.LpuSection_id,
				userMedStaffFact: this.userMedStaffFact,
				from:this.mode,
				ARMType:this.userMedStaffFact.ARMType,
				TimetableGraf_id:this.TimetableGraf_id
			};
			// для новой формы записи
			params.personData = {
				PersonEvn_id: this.PersonEvn_id,
				Person_id: this.personId,
				Server_id: this.serverId,
				Person_Firname: this.getFieldValue('Person_Firname'),
				Person_Surname: this.getFieldValue('Person_Surname'),
				Person_Secname: this.getFieldValue('Person_Secname'),
				Person_Birthday: this.getFieldValue('Person_Birthday')
			}

			params = Ext6.apply(params || {}, oparams || {});
			params[id] = object_value;
			/*
			if (IS_DEBUG)
			{
				console.debug('openForm | Форма %s с параметрами: %o', open_form, params);
			}
			*/

			if(id == 'EvnReceptGeneral_id')
			{
				params[id] = oparams.EvnReceptGeneral_id;
				params.EvnReceptGeneral_pid = oparams.parent_object_value;
				if(mode == 'add')
					params[id] = null;
			}
			if (oparams && oparams.EvnPrescrMse_id) {
				params.EvnPrescrMse_id = oparams.EvnPrescrMse_id;
			}

			getWnd(open_form).show(params);
		}
	},
	defineParentEvnClass: function() {
		var evnClass = {
			EvnClass_SysNick: '',
			EvnClass_id: 0
		};
		switch (true) {
			case (this.data && this.data.Code.inlist(['EvnPL','EvnVizitPL'])):
				evnClass.EvnClass_SysNick = 'EvnVizitPL';
				evnClass.EvnClass_id = 11;
				break;
			case (this.data && this.data.Code.inlist(['EvnPLStom','EvnVizitPLStom'])):
				evnClass.EvnClass_SysNick = 'EvnVizitPLStom';
				evnClass.EvnClass_id = 13;
				break;
			case (this.data && this.data.Code.inlist(['EvnPS','EvnSection'])):
				evnClass.EvnClass_SysNick = 'EvnSection';
				evnClass.EvnClass_id = 32;
				break;
			case (this.data && this.data.Code.inlist(['EvnPLDispDop13'])):
				evnClass.EvnClass_SysNick = 'EvnPLDispDop13';
				evnClass.EvnClass_id = 101;
				break;
			case (this.data && this.data.Code.inlist(['EvnPLDispProf'])):
				evnClass.EvnClass_SysNick = 'EvnPLDispProf';
				evnClass.EvnClass_id = 103;
				break;
			case (this.data && this.data.Code.inlist(['EvnPLDispOrp'])):
				evnClass.EvnClass_SysNick = 'EvnPLDispOrp';
				evnClass.EvnClass_id = 9;
				break;
			case (this.data && this.data.Code.inlist(['EvnPLDispTeenInspection'])):
				evnClass.EvnClass_SysNick = 'EvnPLDispTeenInspection';
				evnClass.EvnClass_id = 104;
				break;
			case (this.data && this.data.Code.inlist(['EvnPLDispMigrant'])):
				evnClass.EvnClass_SysNick = 'EvnPLDispMigrant';
				evnClass.EvnClass_id = 189;
				break;
			case (this.data && this.data.Code.inlist(['EvnPLDispDriver'])):
				evnClass.EvnClass_SysNick = 'EvnPLDispDriver';
				evnClass.EvnClass_id = 190;
				break;
		}
		return evnClass;
	},
	openQuarantineEditWindow: function (params) {
		var me = this;
		var showParams = {
			MedStaffFact_id: me.userMedStaffFact.MedStaffFact_id,
			Person_id: me.personId,
			Server_id: me.serverId,
			action: 'add'
		};
		showParams = Ext.applyIf(params,showParams);
		getWnd('swPersonQuarantineEditWindow').show(showParams);
	},
	checkIsDead: function() {
		var me = this,
			isDead = me.getFieldValue('Person_IsDead') == 2 ? true : false;
		//Блокируем кнопки для умерших пациентов
		if(me.deadHistory !== isDead && me.PToolbar){ //Чтобы каждый раз по кнопкам не проходить
			var arrBtn = me.PToolbar.query('button');
			arrBtn.forEach(function(btn){
				if(!btn.name || btn.name != 'editPerson')
					btn.setDisabled(isDead);
			});
		}
		me.deadHistory = isDead;

		return isDead;
	}
});
